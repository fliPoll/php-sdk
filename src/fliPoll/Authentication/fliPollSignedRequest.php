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

class fliPollSignedRequest {
    /**
     * @const string The aglorithm for hashing signed requests.
     */
    const HASH_ALGORITHM = 'sha256';
    
    /**
     * @var \fliPoll\fliPoll The fliPoll class object.
     */
    private $_fliPoll;
    
    /**
     * @var string The signed request string.
     */
    private $_signedRequest;
    
    /**
     * @var array The metadata data associated with the signed request.
     */
    private $_metadata;
    
    /**
     * Instantiates a new fliPollSignedRequest class object.
     * 
     * @param \fliPoll\fliPoll $fliPoll
     * @param string $signedRequest
     *
     * @throws \fliPoll\Exceptions\fliPollAuthenticationException
     */
    function __construct(fliPoll $fliPoll, $signedRequest = null) {
        $this->_fliPoll = $fliPoll;
        
        if (gettype($signedRequest) != 'string') {
            return;
        }
        
        $this->_signedRequest = $signedRequest;
        
        $this->_parse();
    }
    
    /**
     * Returns the metadata associated with the signed request.
     *
     * @return mixed
     */
    public function getMetadata() {
        return $this->_metadata;
    }
    
    /**
     * Returns the signed request either for the current object or passed metadata data.
     *
     * @param array|null $metadata
     * 
     * @return string
     */
    public function getSignedRequest(array $metadata = null) {
        if (!$metadata) {
            return $this->_signedRequest;
        }
        
        if ( !$encodedMetadata = $this->_base64Encode(json_encode($metadata))
            or !$signature = $this->_getSignature($encodedMetadata)
            or !$encodedSignature = $this->_base64Encode($signature) ) {
            throw new fliPollAuthenticationException('Invalid metadata.');
        }
        
        return $encodedSignature . '|' . $encodedMetadata;
    }

    /**
     * Returns the signed request string.
     *
     * @return string
     */
    public function __toString() {
        return $this->_signedRequest;
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
        if ( !$metadata = $this->getMetadata()
            or strpos($name, 'get') !== 0 ) {
            trigger_error('Call to undefined method '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
        }
        
        return ( (isset($metadata[fliPollData::getFromCamelCase(substr($name, 3))]))
            ? $metadata[fliPollData::getFromCamelCase(substr($name, 3))]
            : null
        );
    }
    
    /**
     * Parses a signed request into metadata data.
     */
    private function _parse() {
        $explodedSignedRequest = explode('|', $this->_signedRequest);
        
        if (sizeof($explodedSignedRequest) !== 2) {
            throw new fliPollAuthenticationException('Invalid signed request.');
        }
        
        list($encodedSignature, $encodedMetadata) = $explodedSignedRequest;
        
        if ( !$signature = $this->_base64Decode($encodedSignature)
            or !$hashedMetadata = $this->_getSignature($encodedMetadata)
            or !\hash_equals($signature, $hashedMetadata)
            or !$decodedMetadata = json_decode($this->_base64Decode($encodedMetadata), true) ) {
            throw new fliPollAuthenticationException('Invalid signed request.');
        }
        
        $this->_metadata = $decodedMetadata;
    }
    
    /**
     * Returns a signed request specific hash.
     *
     * @param string $text
     *
     * @return string
     */
    private function _getSignature($data) {
        return hash_hmac(
            self::HASH_ALGORITHM,
            $data,
            $this->_fliPoll->getAppSecret(),
            true
        );
    }
    
    /**
     * Returns a formatted base 64 encoded string.
     *
     * @param string $text
     *
     * @return string
     */
    private function _base64Encode($text) {
        return str_replace(
            array('+', '/', '='),
            array('-', '_', ''),
            base64_encode($text)
        );
    }
    
    /**
     * Returns a formatted base 64 decoded string.
     *
     * @param string $text
     *
     * @return string
     */
    private function _base64Decode($text) {
        return base64_decode(
            str_replace(
                array('-', '_'),
                array('+', '/'),
                $text
            )
        );
    }
}
?>
