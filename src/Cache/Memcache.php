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

class Memcache implements CacheInterface {

	protected $memcache;
	protected $options;
	private $name = 'memcache';

	public function __construct($config=array()) {
		$this->options = isset($config['cache']['memcache']['options']) ? $config['cache']['memcache']['options'] : 0;
		$servers = @$config['cache']['memcache']['servers'];

		$this->memcache = new \Memcache();

		// Add servers
		foreach($servers as $host => $port)
			$this->memcache->addServer($host, $port);
	}

	/** Get key */
	public function get($key) {
		$data = $this->memcache->get($key);
		if ($data === false)
			return null;
		return $data;
	}

	/** Set key */
	public function set($key, $data, $ttl) {
		return $this->memcache->set($key, $data, $this->options, $ttl);
	}

	/** Delete key */
	public function del($key) {
		return $this->memcache->delete($key);
	}

	/** Flush cache */
	public function flush() {
		return $this->memcache->flush();
	}
	
	/** Return cache name **/
	public function getName() {
		return $this->name;
	}	
}