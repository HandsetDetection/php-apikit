<?php
/*
** Copyright (c) Richard Uren 2012 - 2016 <richard@teleport.com.au>
** All Rights Reserved
**
** --
**
** LICENSE: Redistribution and use in source and binary forms, with or
** without modification, are permitted provided that the following
** conditions are met: Redistributions of source code must retain the
** above copyright notice, this list of conditions and the following
** disclaimer. Redistributions in binary form must reproduce the above
** copyright notice, this list of conditions and the following disclaimer
** in the documentation and/or other materials provided with the
** distribution.
**
** THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED
** WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
** MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN
** NO EVENT SHALL CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
** INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
** BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
** OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
** ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
** TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
** USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

namespace HandsetDetection\Cache;

use HandsetDetection\Cache\CacheInterface;

class APC implements CacheInterface {

	private $name = 'apc';

	public function __construct($config = array()) {
		if (! function_exists('apc_store'))
			throw new \RuntimeException('APC functions not available. Is the APC module installed ?');
	}

	/** Get key */
	public function get($key) {
		$data = apc_fetch($key);
		return ($data === false) ? null : $data;
	}

	/** Set key */
	public function set($key, $data, $ttl) {
		return apc_store($key, $data, $ttl);
	}

	/** Delete key */
	public function del($key) {
		return apc_delete($key);
	}

	/** Flush Cache */
	public function flush() {
		return apc_clear_cache('user');
	}
	
	/** Return cache name **/
	public function getName() {
		return $this->name;
	}
}