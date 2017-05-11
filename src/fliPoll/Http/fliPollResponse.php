<?php
/**
 * The MIT License
 * 
 * Copyright (c) 2015 fliPoll, LLC
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

namespace fliPoll\Http;

use fliPoll\Exceptions\fliPollApiException;
use fliPoll\Exceptions\fliPollAuthenticationException;

/**
 * Class fliPollResponse
 *
 * @package fliPoll\Http
 */
class fliPollResponse {
    /**
     * @const int Http OK status code.
     */ 
    const HTTP_OK_STATUS_CODE = 200;
    
    /**
     * @const int Http bad request status code.
     */ 
    const HTTP_BAD_REQUEST_STATUS_CODE = 400;
    
    /**
     * @const int Http unauthorized status code.
     */ 
    const HTTP_UNAUTHORIZED_STATUS_CODE = 401;
    
    /**
     * @const int Http forbidden status code.
     */ 
    const HTTP_FORBIDDEN_STATUS_CODE = 404;
    
    /**
     * @const int Http internal server error status code.
     */ 
    const HTTP_INTERNAL_SERVER_ERROR_STATUS_CODE = 500;
    
    /**
     * @var array The API response headers.
     */
    private $_headers;
    
    /**
     * @var string The API response body.
     */
    private $_body;
    
    /**
     * @var int The API response HTTP status code.
     */
    private $_httpStatusCode;
    
    /**
     * @var string The API OAuth header error.
     */
    private $_oauthError;
    
    /**
     * Instantiates a new fliPollResponse class object.
     *
     * @param \fliPoll\Http\fliPollRequest $request
     * @param array|string $headers
     * @param string $body
     * @param int|null $httpStatusCode
     *
     * @throws \fliPoll\Exceptions\fliPollAuthenticationException|\fliPoll\Exceptions\fliPollApiException
     */
    function __construct($headers, $body, $httpStatusCode = null) {
        // Set this before parsing the raw headers to avoid overwriting raw http status codes
        if (is_numeric($httpStatusCode)) {
            $this->_httpStatusCode = (int)$httpStatusCode;
        }
        
        if (is_array($headers)) {
            $this->_headers = $headers;
        } else {
            $this->_setHeadersFromString($headers);
        }
        
        $this->_body = $body;
        
        if ($this->_httpStatusCode !== fliPollResponse::HTTP_OK_STATUS_CODE) {
            throw new fliPollAuthenticationException(
                ($this->_oauthError) ?: 'Invalid OAuth 2.0 request.', 
                $this->_httpStatusCode
            );
        }
        
        if ($error = $this->_getError()) {
            throw new fliPollApiException($error['message'], $error['code']);
        }
    }
    
    /**
     * Returns the API headers.
     *
     * @return array
     */
    public function getHeaders() {
        $this->_headers;
    }
    
    /**
     * Returns the API response HTTP status code.
     *
     * @return int
     */
    public function getHttpStatusCode() {
        return $this->_httpStatusCode;
    }
    
    /**
     * Returns the raw API response body.
     *
     * @return string
     */
    public function getRawResponse() {
        return $this->_body;
    }
    
    /**
     * Returns the API response body formatted as an array.
     *
     * @return array
     */
    public function getFormattedResponse() {
        return json_decode($this->_body, true);
    }
    
    /**
     * Returns the API response results if they exist.
     *
     * @return array|null
     */
    public function getResults() {
        $formattedResponse = $this->getFormattedResponse();
        
        return (isset($formattedResponse['results'])) ? $formattedResponse['results'] : null;
    }
    
    /**
     * Returns the API response error if it exists.
     *
     * @return array|null
     */
    private function _getError() {
        $formattedResponse = $this->getFormattedResponse();
        
        return (isset($formattedResponse['error'])) ? $formattedResponse['error'] : null;
    }
    
    /**
     * Store the headers from the raw API response HTTP headers.
     */
    private function _setHeadersFromString($headers) {
        // Normalize line breaks
        $rawHeaders = str_replace("\r\n", "\n", $headers);
        
        // There will be multiple headers if a 301 was followed
        // or a proxy was followed, etc
        $headerCollection = explode("\n\n", trim($rawHeaders));
        
        // We just want the last response (at the end)
        $rawHeader = array_pop($headerCollection);
        $headerComponents = explode("\n", $rawHeader);
        
        foreach($headerComponents as $line) {
            if (strpos($line, 'HTTP/') === 0) {
                $this->_setHttpStatusCodeFromHeader($line);
            } else {
                list($key, $value) = array_pad(explode(': ', $line), 2, '');
                $this->_headers[$key] = $value;
            }
        }
    }
    
    /**
     * Store the HTTP status code from the raw API response HTTP headers.
     */
    private function _setHttpStatusCodeFromHeader($rawResponseHeader) {
        if (preg_match('|HTTP/\d\.\d\s+(\d+)\s+.*|', $rawResponseHeader, $httpStatusCodeMatch)) {
            if (!$this->_httpStatusCode) {
                $this->_httpStatusCode = (int)$match[1];
            }
            
            if (preg_match('/{(.*?)}/', $rawResponseHeader, $oauthErrorMatch)) {
                if ($oathError = json_decode($oauthErrorMatch[0], true)) {
                    $this->_oauthError = $oathError['Error'];
                }
            }
        }
    }
}
?>
