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
use fliPoll\Exceptions\fliPollSdkException;
use fliPoll\Exceptions\fliPollAuthenticationException;

class fliPollRedirectLoginHandler extends fliPollLoginHandler {
    /**
     * @const string The hashing aglorithm for generating an OAuth 2.0 login state.
     */
    const HASH_ALGORITHM = 'md5';
    
    /**
     * @const string The fliPoll intent URL.
     */
    const INTENT_URL = 'https://flipoll.com/intent';
    
    /**
     * Returns the url to be used for a login redirect.
     *
     * @param string $redirectUri
     * @param array $params
     *
     * @return string
     */
    public function getLoginUrl($redirectUri, array $scope = array()) {
        if (filter_var($redirectUri, FILTER_VALIDATE_URL) === false) {
            throw new fliPollSdkException('A valid redirect URI must be supplied.');
        }
        
        $loginUrl = self::INTENT_URL . '/oauth?client_id=' . $this->fliPoll->getAppId() . '&redirect_uri=' . rawurlencode($redirectUri);
        
        if ($scope) {
            $loginUrl .= '&scope=' . implode(',', $scope);
        }
        
        $state = $this->_getState();
        
        $this->sessionData->setState($state);
        
        return $loginUrl . '&state=' . $state;
    }
    
    /**
     * Returns the access token associated with a login redirect.
     *
     * @param string $redirectUri
     *
     * @return \fliPoll\Authentication\fliPollAccessToken
     */
    public function getAccessToken($redirectUri = null) {
        if ( !$token = $this->queryData->getToken()
            and !$code = $this->queryData->getCode() ) {
            throw new fliPollAuthenticationException('No OAuth data was found.');
        }
        
        if ( !$queryState = $this->queryData->getState()
            or !$sessionState = $this->sessionData->getState()
            or !\hash_equals($queryState, $sessionState) ) {
            throw new fliPollAuthenticationException('The login source could not be validated.');
        }
        
        if ($token) {
            return new fliPollAccessToken($token);
        }
        
        $oauth2Client = $this->fliPoll->getOAuth2Client();
        
        $accessToken = $oauth2Client->getUserAccessToken($code, $redirectUri);
        
        $this->sessionData->deleteState();
        
        return $accessToken;
    }
    
    /**
     * Returns the url to be used for a logout redirect.
     *
     * @param string $redirectUri
     *
     * @return string
     */
    public function getLogoutUrl($redirectUri) {
        if (!$accessToken = $this->fliPoll->getAccessToken()) {
            throw new fliPollSdkException('No access token is available to logout.');
        }
        
        if ( $tokenType = $accessToken->getTokenType()
            and $tokenType != 'user' ) {
            throw new fliPollSdkException('Only user access tokens can be used to logout.');
        }
        
        if (filter_var($redirectUri, FILTER_VALIDATE_URL) === false) {
            throw new fliPollSdkException('A valid redirect URI must be supplied.');
        }
        
        return self::INTENT_URL . '/logout?client_id=' . $this->fliPoll->getAppId() . '&redirect_uri=' . rawurlencode($redirectUri);
    }
    
    /**
     * Returns a hashed OAuth 2.0 login state.
     *
     * @return string
     */
    private function _getState() {
        return hash_hmac(
            self::HASH_ALGORITHM,
            ( microtime(TRUE) . rand() ), 
            $_SERVER['REMOTE_ADDR']
        );
    }
}
?>
