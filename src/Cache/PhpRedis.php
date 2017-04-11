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

class PhpRedis implements CacheInterface {

	protected $phpredis;
	private $name = 'phpredis';

	public function __construct($config=array()) {
		if (! extension_loaded("redis")) {
			throw new \RuntimeException('redis.so module not loaded. Is the extension installed and enabled ?');
		}

		$options = @$config['cache']['phpredis'];

		// Create handle
		$this->phpredis = new \Redis();

		$connectMethod = isset($config['connect_method']) ? $config['connect_method'] : 'connect';
		$host = isset($options['host']) ? $options['host'] : '127.0.0.1';
		$port = isset($options['port']) ? $options['port'] : 6379;
		$timeout = isset($options['timeout']) ? $options['timeout'] : null;
		$persistent_id = isset($options['persistent_id']) ? $options['persistent_id'] : null;
		if ($connectMethod == 'connect') {
			$this->phpredis->connect($host, $port, $timeout);
		} elseif ($connect_method == 'pconnect') {
			$this->phpredis->connect($host, $port, $timeout, $persistent_id);
		}

		if (! empty($options['auth']))
			$this->phpredis->auth($options['auth']);

		if (! empty($options['select']))
			$this->phpredis->select($options['select']);
	}

	/** Get key */
	public function get($key) {
		$data = $this->phpredis->get($key);
		if ($data === false)
			return null;
		if (empty($data))
			return null;
		return unserialize($data);
	}

	/** Set key */
	public function set($key, $data, $ttl) {
		$code = $this->phpredis->set($key, serialize($data));
		if (! $code)
			return null;
		if ($ttl) {
			return ($this->phpredis->expire($key, $ttl) == 1) ? true : null;
		}
		return true;
	}

	/** Delete key */
	public function del($key) {
		return ($this->phpredis->delete($key) == 1) ? true : null;
	}

	/** Flush Cache */
	public function flush() {
		if (! $this->phpredis->flushDb())
			return null;
		return true;
	}

	/** Return cache name **/
	public function getName() {
		return $this->name;
	}
}