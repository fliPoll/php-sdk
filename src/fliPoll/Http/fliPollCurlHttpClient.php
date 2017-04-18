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

use fliPoll\Exceptions\fliPollClientException;

/**
 * Class fliPollCurlHttpClient
 *
 * @package fliPoll\Http
 */
class fliPollCurlHttpClient {
	/**
	 * @var \fliPoll\Http\fliPollRequest The request object.
	 */
	private $_request;
	
	/**
	 * @var resource The cURL resource instance.
	 */
	private $_curl;
	
	/**
	 * @var string The raw cURL HTTP response.
	 */
	private $_rawResponse;
	
	/**
     * Instantiates a new fliPollCurlHttpClient class object.
	 *
	 * @param \fliPoll\Http\fliPollRequest $request
	 *
	 * @throws \fliPoll\Exceptions\fliPollClientException
     */
	function __construct(fliPollRequest $request) {
		$this->_request = $request;
	}
	
	/**
     * Returns the response of cURL HTTP request.
	 *
	 * @return \fliPoll\Http\fliPollResponse
     */
    public function send() {
		$this->_openConnection();
		
		$this->_sendRequest();
		
		if ($curlErrorCode = curl_errno($this->_curl)) {
			// echo curl_error($this->_curl).'|'.$curlErrorCode;
			
            throw new fliPollClientException(curl_error($this->_curl), $curlErrorCode);
        }
        
		// Separate the raw headers from the raw body
        list($headers, $body, $httpStatusCode) = $this->_extractResponseParts();
        
		$this->_closeConnection();
        
		return new fliPollResponse($headers, $body, $httpStatusCode);
    }
	
    /**
     * Opens a new cURL connection.
     */
    public function _openConnection() {
		/*  
	    $options = [
            CURLOPT_CUSTOMREQUEST => $this->_request->getMethod(),
            CURLOPT_HTTPHEADER => $this->_compileRequestHeaders($this->_request->getHeaders()),
            CURLOPT_URL => $this->_request->getUrl(),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_RETURNTRANSFER => true, // Follow 301 redirects
            CURLOPT_HEADER => true, // Enable header processing
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAINFO => __DIR__ . '/certs/DigiCertHighAssuranceEVRootCA.pem', // @TODO: Update cert
        ];
		*/
		
		$options = array(
            CURLOPT_CUSTOMREQUEST => $this->_request->getMethod(),
			CURLOPT_HTTPHEADER => $this->_compileRequestHeaders($this->_request->getHeaders()),
			CURLOPT_URL => $this->_request->getUrl(),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_RETURNTRANSFER => true, // Follow 301 redirects
            CURLOPT_HEADER => true, // Enable header processing
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => false
        );
		
		if ($this->_request->getMethod() !== 'GET') {
            $options[CURLOPT_POSTFIELDS] = $this->_request->getParams();
        }
		
		$this->_curl = curl_init();
		
        curl_setopt_array($this->_curl, $options);
    }
	
	/**
     * Sends the request and retrieves the raw response from cURL.
     */
    public function _sendRequest() {
		$this->_rawResponse = curl_exec($this->_curl);
    }
	
    /**
     * Closes an existing cURL connection.
     */
    public function _closeConnection() {
		curl_close($this->_curl);
    }    
	
    /**
     * Compiles the request headers into a cURL-friendly format.
     *
     * @param array $headers The request headers.
     *
     * @return array
     */
    public function _compileRequestHeaders(array $headers = array()) {
        $return = array();
		
        foreach($headers as $key => $value) {
            $return[] = $key . ': ' . $value;
        }
		
        return $return;
    }
	
    /**
     * Returns the extracted headers, body, and HTTP status code in a three-part array.
     *
     * @return array
     */
    public function _extractResponseParts() {
        $parts = explode("\r\n\r\n", $this->_rawResponse);
        $body = array_pop($parts);
        $headers = implode("\r\n\r\n", $parts);
		
        return [trim($headers), trim($body), curl_getinfo($this->_curl, CURLINFO_HTTP_CODE)];
    }
}
?>