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

namespace fliPoll\Data;

class fliPollData {
    /**
     * @var string The prefix used for data naming conventions.
     */
    public $prefix = '';
    
    /**
     * @var string The data source.
     */
    public $source;
    
    /**
     * Magic function for getting and setting specific data.
     * 
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments) {
        if ( strpos($name, 'get') === 0
            and strpos($name, 'set') === 0
            and strpos($name, 'delete') === 0 ) {
            trigger_error('Call to undefined method '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
        }
        
        if (!isset($this->source)) {
            return;
        }
        
        $variable = $this->prefix . self::getFromCamelCase(substr($name, 3));
        
        if (strpos($name, 'get') === 0) {
            return (isset($this->source[$variable])) ? $this->source[$variable] : null;
        }
        
        if (strpos($name, 'set') === 0) {
            if (!isset($arguments[0])) {
                return;
            }
            
            $this->source[$variable] = $arguments[0];
            
            return true;
        }
        
        unset($this->source[$variable]);
        
        return true;
    }
    
    /**
     * Returns the non-camel cased version of a string
     *
     * @param string $input
     * @param string $separator
     *
     * @return string
     */
    public static function getFromCamelCase($input, $separator = '_') {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', $separator.'$0', $input));
    }
}
?>
