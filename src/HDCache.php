<?php
/*
** Copyright (c) Teleport corp 2012 - 2015 <richard@teleport.com.au>
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
** USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
** DAMAGE.
*/

/**
 * Cache class for HandsetDetection
 *
 * Notes :
 *  - Cache objects may be > 1Mb when serialized so ensure memcache or memcached can handle it.
 *  - Consider php-igbinary to improve serialization performance.
 *  - 48Mb of APC cache is optimal if available.
 **/

namespace HandsetDetection;

use HandsetDetection\Cache\APC;
use HandsetDetection\Cache\APCu;
use HandsetDetection\Cache\Memcache;
use HandsetDetection\Cache\Memcached;
use HandsetDetection\Cache\File;
use HandsetDetection\Cache\None;

class HDCache {
	var $prefix;
	var $ttl;
	protected $cache = null;

	function __construct($config = array()) {
		$this->setConfig($config);
	}

	/**
	 * Set config file
	 *
	 * @param array $config An assoc array of config data
	 * @return true on success, false otherwise
	 **/
	function setConfig($config) {
		foreach((array) $config as $key => $value)
			$this->config[$key] = $value;

		$this->prefix = isset($config['cache']['prefix']) ? $config['cache']['prefix'] : 'hd40';
		$this->duration = isset($config['cache']['ttl']) ? $config['cache']['ttl'] : 7200;

		if (isset($config['cache']['memcached']))
			$this->cache = new Memcached($config);
		elseif (isset($config['cache']['memcache']))
			$this->cache = new Memcache($config);
		elseif (isset($config['cache']['file']))
			$this->cache = new File($config);
		elseif (isset($config['cache']['none']))
			$this->cache = new None($config);
		elseif (isset($config['cache']['apc']))
			$this->cache = new APC($config);
		elseif (isset($config['cache']['apcu']))
			$this->cache = new APCu($config);
		elseif (! isset($config['cache'])) {
			// The legacy option was to use apc/apc by default - so use apcu/apc if 'cache' is not set
			if (function_exists('apcu_store'))
				$this->cache = new APCu($config);
			elseif (function_exists('apc_store'))
				$this->cache = new APC($config);
		}

		if (empty($this->cache))
			$this->cache = new None($config);

		return true;
	}
	/**
	 * Fetch a cache key
	 *
	 * @param string $key
	 * @return value on success, null otherwise
	 **/
	function read($key) {
		return $this->cache->get($this->prefix.$key);
	}

	/**
	 * Store a data at $key
	 *
	 * @param string $key
	 * @param mixed $data
	 * @return true on success, false otherwise
	 **/
	function write($key, $data) {
		return $this->cache->set($this->prefix.$key, $data, $this->duration);
	}

	/**
	 * Remove a cache key (and its data)
	 *
	 * @param string $key
	 * @return true on success, false otherwise
	 **/
	function delete($key) {
		return $this->cache->del($this->prefix.$key);
	}

	/**
	 * Flush the whole cache
	 *
	 * @param void
	 * @return true on success, false otherwise
	 **/
	function purge() {
		return $this->cache->flush();
	}
}