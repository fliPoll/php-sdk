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

namespace fliPoll\Authentication;

use fliPoll\fliPoll;
use fliPoll\fliPollApp;
use fliPoll\Http\fliPollRequest;
use fliPoll\Http\fliPollResponse;
use fliPoll\Http\fliPollCurlHttpClient;
use fliPoll\Exceptions\fliPollAuthenticationException;
use fliPoll\Exceptions\fliPollSdkException;

/**
 * Class fliPollOAuth2Client
 *
 * @package fliPoll\Authentication
 */
class fliPollOAuth2Client {
	/**
	 * @var array The fliPoll class object.
	 */
	private $_fliPoll;
	
	/**
     * Instantiates a new fliPoll OAuth 2.0 client class object.
	 *
	 * @param array $config
     */
	function __construct(fliPoll $fliPoll) {
		$this->_fliPoll = $fliPoll;
	}
	
	/**
     * Performs an OAuth 2.0 API request to retrieve an app access token.
	 *
	 * @return \fliPoll\Authentication\fliPollAccessToken
     */
	public function getAppAccessToken() {
		$response = $this->_fliPoll->api(
			'/oauth/token',
			'POST',
			array(
				'client_id' => $this->_fliPoll->getAppId(),
				'client_secret' => $this->_fliPoll->getAppSecret(),
				'grant_type' => 'client_credentials'
			)
		);
		
		if ( !$results = $response->getResults()
			or !isset($results['access_token']) ) {
			throw new fliPollAuthenticationException('An app access token was not able to be retrieved.');
		}
		
		return new fliPollAccessToken($results['access_token']);
	}
	
	/**
     * Performs an OAuth 2.0 API request to exchange an authorization code for a user access token.
	 *
	 * @param string $code
	 * @param string $redirectUri
	 *
	 * @return \fliPoll\Authentication\fliPollAccessToken
     */
	public function getUserAccessToken($code, $redirectUri = null) {
		$response = $this->_fliPoll->api(
			'/oauth/token',
			'POST',
			array(
				'client_id' => $this->_fliPoll->getAppId(),
				'client_secret' => $this->_fliPoll->getAppSecret(),
				'code' => $code,
				'redirect_uri' => ($redirectUri) ?: $this->_getCurrentUrl(),
				'grant_type' => 'authorization_code'
			)
		);
		
		if ( !$results = $response->getResults()
			or !isset($results['access_token']) ) {
			throw new fliPollAuthenticationException('A user access token was not able to be retrieved.');
		}
		
		return new fliPollAccessToken($results['access_token']);
	}
	
	/**
     * Performs an OAuth 2.0 API request to retrieve an access token with metadata.
	 *
	 * @param string $accessToken
	 *
	 * @return \fliPoll\Authentication\fliPollAccessToken
     */
	public function getAccessTokenMetadata($accessToken = null) {
		if (!in_array(gettype($accessToken), array('string', 'object', 'NULL'))) {
			throw new fliPollSdkException('Only string and object access tokens can be passed.');
		}
		
		if ( gettype($accessToken) === 'object'
			and !( $accessToken instanceof fliPollAccessToken) ) {
			throw new fliPollSdkException('Only \fliPoll\Authentication\fliPollAccessToken access token objects can be passed.');
		}
		
		if ( !$accessToken
			and !$this->_fliPoll->getAccessToken() ) {
			throw new fliPollSdkException('An access token was not found.');
		}
		
		$response = $this->_fliPoll->api(
			'/oauth/token',
			array(
				'input_token' => ( ($accessToken) ? (string) $accessToken : $this->_fliPoll->getAccessToken() )
			)
		);
		
		if (!$results = $response->getResults()) {
			throw new fliPollAuthenticationException('A user access token was not able to be retrieved.');
		}
		
		return new fliPollAccessToken($results);
	}
	
	/**
     * Returns the current url of the script being executed to be used for redirect url requests.
	 *
	 * @return string
     */
	private function _getCurrentUrl() {
		return 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER["REQUEST_URI"], '?');
	}
}
?>