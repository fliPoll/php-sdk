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

namespace fliPoll;

use fliPoll\Http\fliPollRequest;
use fliPoll\Http\fliPollResponse;
use fliPoll\Http\fliPollCurlHttpClient;
use fliPoll\Authentication\fliPollAccessToken;
use fliPoll\Authentication\fliPollOAuth2Client;
use fliPoll\Handlers\fliPollRedirectLoginHandler;
use fliPoll\Handlers\fliPollJavascriptLoginHandler;
use fliPoll\Exceptions\fliPollSdkException;

/**
 * Class fliPoll
 *
 * @package fliPoll
 */
class fliPoll {
    /**
     * @const string The fliPoll PHP SDK version.
     */
    const SDK_VERSION = '1.0';
    
    /**
     * @const string The default fliPoll REST API version.
     */
    const DEFAULT_REST_API_VERSION = 'v2.1';
    
    /**
     * @var string The app id.
     */
    private $_appId;
    
    /**
     * @var string The app secret.
     */
    private $_appSecret;
    
    /**
     * @var string The access token.
     */
    private $_accessToken;
    
    /**
     * @var bool Version of the REST API to access.
     */
    private $_apiVersion;
    
    /**
     * @var array The OAuth 2.0 client object.
     */
    private $_oAuth2Client;

    /**
     * Instantiates a new fliPoll class object.
     *
     * @param array $config
     */
    function __construct(array $config) {
        $config = array_merge(
            array(
                'status' => false
            ),
            $config
        );
        
        if (!isset($config['app_id'])) {
            throw new fliPollSdkException('Required "app_id" key not supplied in config.');
        }
        
        if (!isset($config['app_secret'])) {
            throw new fliPollSdkException('Required "app_secret" key not supplied in config.');
        }
        
        if (!is_string($config['app_id'])) {
            throw new fliPollSdkException('The "app_id" key is required to be a string.');
        }
        
        if (!is_string($config['app_secret'])) {
            throw new fliPollSdkException('The "app_secret" key is required to be a string.');
        }
        
        if (!is_bool($config['status'])) {
            throw new fliPollSdkException('The "status" key is required to be a bool.');
        }
        
        $this->_appId = $config['app_id'];
        
        $this->_appSecret = $config['app_secret'];
        
        if (isset($config['api_version'])) {
            if (!preg_match('/\b(v[1-9]\.[0-9])\b/', $config['api_version'])) {
                throw new fliPollSdkException('The "api_version" key must be in a vX.X format.');
            }
            
            $this->_apiVersion = $config['api_version'];
        }
        
        if (isset($config['access_token'])) {
            $this->setAccessToken($config['access_token']);
        }
        
        $this->_oAuth2Client = new fliPollOAuth2Client($this);
    }
    
    /**
     * Returns the app id.
     *
     * @return string
     */
    public function getAppId() {
        return $this->_appId;
    }
    
    /**
     * Returns the app secret.
     *
     * @return string
     */
    public function getAppSecret() {
        return $this->_appSecret;
    }
    
    /**
     * Returns the access token if it's been set.
     *
     * @return \fliPoll\Authentication\fliPollAccessToken|null
     */
    public function getAccessToken() {
        return $this->_accessToken;
    }
    
    /**
     * Returns the requested API version.
     *
     * @return string
     */
    public function getApiVersion() {
        return ($this->_apiVersion) ?: self::DEFAULT_REST_API_VERSION;
    }
    
    /**
     * Returns the OAuth 2.0 client object.
     *
     * @return string|null
     */
    public function getOAuth2Client() {
        return $this->_oAuth2Client;
    }
    
    /**
     * Returns a new instance of a redirect login handler.
     *
     * @return \fliPoll\Handlers\fliPollRedirectLoginHandler
     */
    public function getRedirectLoginHandler() {
        return new fliPollRedirectLoginHandler($this);
    }
    
    /**
     * Returns a new instance of a javascript login handler.
     *
     * @return \fliPoll\Handlers\fliPollJavascriptLoginHandler
     */
    public function getJavascriptLoginHandler() {
        return new fliPollJavascriptLoginHandler($this);
    }
    
    /**
     * Assigns the access token value either from a passed value or OAuth2 request to the server.
     *
     * @param string|null $accessToken
     */
    public function setAccessToken($accessToken) {
        $this->_accessToken = ($accessToken instanceof fliPollAccessToken)
            ? $accessToken
            : new fliPollAccessToken($accessToken);
    }
    
    /**
     * Performs an API request to the server.
     *
     * @param mixed $endPoint, $method, $params, $accessToken
     *
     * @return \fliPoll\Http\fliPollResponse
     */
    public function api() {
        $args = func_get_args();
    
        if (!sizeof($args)) {
            return false;
        }
        
        $endPoint
            = $method
            = $params
            = $accessToken
            = null;
        
        foreach ($args as $index => $arg) {
            if (!$index) {
                if ( gettype($arg) !== 'string'
                    or strpos($arg, '/') !== 0 ) {
                    return false;
                }
                
                $endPoint = $arg;
                
                continue;
            }
            
            switch(gettype($arg)) {
                case 'string': {
                    if (in_array($arg, array('GET', 'POST', 'DELETE'))) {
                        if ( $method
                            or $params
                            or $accessToken ) {
                            return false;
                        }
                        
                        $method = $arg;
                        
                        continue;
                    }
                    
                    if ($accessToken) {
                        return false;
                    }
                    
                    $accessToken = $arg;
                    
                    continue;
                }
                case 'array': {
                    if ( $params
                        or $accessToken ) {
                        return false;
                    }
                    
                    $params = $arg;
                
                    continue;
                }
                case 'object': {
                    if ( !($arg instanceof fliPollAccessToken)
                        or $accessToken ) {
                        return false;
                    }
                    
                    $accessToken = $arg;
                    
                    continue;
                }
            }
        }
        
        return (new fliPollCurlHttpClient(
            new fliPollRequest(
                $this,
                ($method) ?: 'GET',
                $endPoint,
                ($params) ?: [],
                [],
                $accessToken
            )
        ))->send();
    }
}
