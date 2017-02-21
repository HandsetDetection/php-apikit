<?php
/*
** Copyright (c) Richard Uren 2017 - 2017 <richard@teleport.com.au>
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

class Redis implements CacheInterface {

	protected $redis;
	private $name = 'redis';

	public function __construct($config=array()) {
		if (! class_exists("\Predis\Client")) {
			throw new \RuntimeException('Redis functions not available. Is the Predis module installed ?');
		}
		
		$options = @$config['cache']['redis'];

		// Create handle
		$this->redis = empty($options) ? new \Predis\Client() : new \Predis\Client($options);
	}

	/** Get key */
	public function get($key) {
		$data = $this->redis->get($key);
		if ($data === false)
			return null;
		if (empty($data))
			return null;
		return unserialize($data);
	}

	/** Set key */
	public function set($key, $data, $ttl) {
		$code = $this->redis->set($key, serialize($data));
		if (! $code)
			return null;
		if ($ttl) {
			return ($this->redis->expire($key, $ttl) == 1) ? true : null;
		}
		return true;
	}

	/** Delete key */
	public function del($key) {
		return ($this->redis->del($key) == 1) ? true : null;
	}

	/** Flush Cache */
	public function flush() {
		if (! $this->redis->flushdb())
			return null;
		return true;
	}

	/** Return cache name **/
	public function getName() {
		return $this->name;
	}
}