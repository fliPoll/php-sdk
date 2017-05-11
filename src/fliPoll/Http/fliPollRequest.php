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

use fliPoll\fliPoll;
use fliPoll\Exceptions\fliPollSDKException;

/**
 * Class fliPollRequest
 *
 * @package fliPoll\Http
 */
class fliPollRequest {
    /**
     * @const string The fliPoll REST API url.
     */
    const REST_API_URL = 'https://flipoll.com/api';
    
    /**
     * @var \fliPoll\fliPoll The fliPoll object.
     */
    private $_fliPoll;
    
    /**
     * @var string The request method.
     */
    private $_method;
    
    /**
     * @var string The API end point.
     */
    private $_endPoint;
    
    /**
     * @var array The API parameters.
     */
    private $_params;
    
    /**
     * @var array The request headers.
     */
    private $_headers;
    
    /**
     * @var string|null The access token to use for the request.
     */
    private $_accessToken;
    
    /**
     * @var string|null The API version to request.
     */
    private $_apiVersion;
    
    /**
     * Instantiates a new fliPollRequest class object.
     *
     * @param \fliPoll\fliPoll $fliPoll
     * @param string $method
     * @param string $endPoint
     * @param array $params
     * @param string $accessToken
     * @param string $apiVersion
     *
     * @throws \fliPoll\Exceptions\fliPollSDKException
     */
    function __construct(fliPoll $fliPoll, $method, $endPoint, array $params = [], array $headers = [], $accessToken = null) {
        $this->_fliPoll = $fliPoll;
        $this->_method = strtoupper($method);
        $this->_endPoint = $endPoint;
        $this->_params = $params;
        $this->_headers = $headers;
        $this->_accessToken = $accessToken;
        
        if (!in_array($this->_method, array('GET', 'POST', 'DELETE'))) {
            throw new fliPollSDKException('Unsupported API request method.');
        }
        
        if (!is_string($this->_endPoint) or strpos($this->_endPoint, '/') !== 0) {
            throw new fliPollSDKException('Invalid API request method.');
        }
    }
    
    /**
     * Returns the fliPoll object.
     *
     * @return \fliPoll\fliPoll
     */
    public function getfliPoll() {
        return $this->_fliPoll;
    }
    
    /**
     * Returns the request method.
     *
     * @return string
     */
    public function getMethod() {
        return $this->_method;
    }
    
    /**
     * Returns the request headers.
     *
     * @return array
     */
    public function getHeaders() {
        $headers = array_merge(
            array(
                'Accept-Encoding' => '*',
                'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
                'fliPoll-PHP-SDK-Version' => fliPoll::SDK_VERSION
            ),
            $this->_headers
        );
        
        if ( !isset($headers['Authorization'])
            and ( $this->_accessToken 
                or $accessToken = $this->_fliPoll->getAccessToken() ) ) {
            $headers['Authorization'] = 'Bearer '
                . ( ($this->_accessToken)
                    ? (string) $this->_accessToken
                    : $accessToken 
                );
        }
        
        return $headers;
    }
    
    /**
     * Returns the API url.
     *
     * @return string
     */
    public function getUrl() {
        $url = self::REST_API_URL . '/' . $this->_fliPoll->getApiVersion().$this->_endPoint;
        
        if ( $this->_method === 'GET'
            and $params = $this->getParams() ) {
            return $url . '?' . $params;
        }
        
        return $url;
    }
    
    /**
     * Returns the API parameters.
     *
     * @return string
     */
    public function getParams() {
        if (!$this->_params) {
            return;
        }
        
        $params = '';
        
        foreach ($this->_params as $key => $value) {
            if ($params !== '') {
                $params .= '&';
            }
            
            $params .= $key . '=' . (string) $value;
        }
        
        return $params;
    }
}
?>
