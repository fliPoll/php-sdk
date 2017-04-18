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

use fliPoll\fliPoll;
use fliPoll\Data\fliPollQueryData;
use fliPoll\Data\fliPollSessionData;
use fliPoll\Data\fliPollCookieData;
use fliPoll\Authentication\fliPollAccessToken;
use fliPoll\Authentication\fliPollSignedRequest;

class fliPollLoginHandler {
	/**
	 * @var \fliPoll\fliPoll The fliPoll class object.
	 */
	protected $fliPoll;
	
	/**
	 * @var \fliPoll\Data\fliPollQueryData The fliPollQueryData class object.
	 */
	protected $queryData;
	
	/**
	 * @var \fliPoll\Data\fliPollSessionData The fliPollSessionData class object.
	 */
	protected $sessionData;
	
	/**
	 * @var \fliPoll\Data\fliPollCookieData The fliPollCookieData class object.
	 */
	protected $cookieData;
	
	/**
     * Instantiates a new fliPollLoginHandler class object.
     * 
     * @param \fliPoll\fliPoll $fliPoll
     */
	function __construct(fliPoll $fliPoll) {
		$this->fliPoll = $fliPoll;
		
		$this->queryData = new fliPollQueryData();
		
		$this->sessionData = new fliPollSessionData();
		
		$this->cookieData = new fliPollCookieData();
	}
}
?>