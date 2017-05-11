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
use fliPoll\Data\fliPollData;
use fliPoll\Exceptions\fliPollAuthenticationException;

class fliPollAccessToken {
    /**
     * @var string|array The access token.
     */
    private $_accessToken;
    
    /**
     * Instantiates a new fliPoll Access Token class object.
     *
     * @param string|array $accessToken
     */
    function __construct($accessToken) {
        if (!in_array(gettype($accessToken), array('string', 'array'))) {
            throw new fliPollAuthenticationException('Unsupported access token type.');
        }
        
        if ( is_array($accessToken)
            and !isset($accessToken['access_token']) ) {
            throw new fliPollAuthenticationException('Invalid access token format.');
        }
        
        $this->_accessToken = $accessToken;
    }
    
    /**
     * Returns the access token metadata if it exists.
     *
     * @return array|null
     */
    public function getMetadata() {
        return (is_array($this->_accessToken)) ? $this->_accessToken : null;
    }
    
    /**
     * Returns the access token string.
     *
     * @return string
     */
    public function __toString() {
        return ($metaData = $this->getMetadata())
            ? $metaData['access_token']
            : $this->_accessToken;
    }
    
    /**
     * Returns specific information about an access token's metadata.
     * 
     * @param string $name
     * @param array|null $arguments
     *
     * @return string
     */
    public function __call($name, $arguments) {
        if (strpos($name, 'get') !== 0) {
            trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);
        }
        
        return ( $metaData = $this->getMetadata()
            and isset($metaData[fliPollData::getFromCamelCase(substr($name, 3))]) )
            ? $metaData[fliPollData::getFromCamelCase(substr($name, 3))]
            : null;
    }
}
?>
