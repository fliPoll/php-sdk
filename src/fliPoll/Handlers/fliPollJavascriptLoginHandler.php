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

namespace fliPoll\Handlers;

use fliPoll\Authentication\fliPollAccessToken;
use fliPoll\Authentication\fliPollSignedRequest;
use fliPoll\Exceptions\fliPollSdkException;
use fliPoll\Exceptions\fliPollAuthenticationException;

class fliPollJavascriptLoginHandler extends fliPollLoginHandler {
	/**
	 * @const string The signed request prefix.
	 */
	const SIGNED_REQUEST_PREFIX = 'fplsr_';
	
	/**
     * Returns the access token associated with a signed request cookie.
	 *
	 * @return \fliPoll\Authentication\fliPollAccessToken
     */
	public function getAccessToken() {
		$getSignedRequest = 'get' . self::SIGNED_REQUEST_PREFIX . $this->fliPoll->getAppId();
		
		if (!$signedRequest = $this->cookieData->$getSignedRequest()) {
			throw new fliPollAuthenticationException('No Javascript login detected.');
		}
		
		$signedRequest = new fliPollSignedRequest($this->fliPoll, $signedRequest);
		
		if ($signedRequest->getAppId() != $this->fliPoll->getAppId()) {
			throw new fliPollAuthenticationException('The signed request\'s app id does not match the app id used to initialize the SDK.');
		}
		
		if ($code = $signedRequest->getCode()) {
			$oauth2Client = $this->fliPoll->getOAuth2Client();
			
			return $oauth2Client->getUserAccessToken($code);
		}
		
		if (!$signedRequest->getAccessToken()) {
			throw new fliPollAuthenticationException('No OAuth data was found in the signed request.');
		}
		
		return new fliPollAccessToken($signedRequest->getMetadata());
	}
}
?>