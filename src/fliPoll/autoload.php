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

/**
 * This file is only required for non-composer usage.
 * To use composer, visit https://getcomposer.org
 */

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    throw new Exception('The Test PHP SDK requires PHP version 5.4 or higher.');
}

require_once __DIR__ . '/polyfills.php';

/**
 * Registers the autoloader for the Test classes.
 *
 * @param string $className The class name.
 *
 * @return void
 */
spl_autoload_register(function($className) {
    $file = __DIR__ . '/' . str_replace(array('fliPoll\\', '\\'), array('', '/'), $className) . '.php';
    
    if (file_exists($file)) {
        require($file);
    }
});
?>
