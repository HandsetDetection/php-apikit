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

class File implements CacheInterface {

	/** @var string file system directory */
	protected $dir;

	/**
	 * Construct a new File cache layer object.
	 *
	 * @param  string $dir
	 * @throws \RuntimeException
	 */
	public function __construct($config = array()) {
		$dir = empty($config['cachedir']) ? sys_get_temp_dir() : $config['cachedir'];

		if (substr($dir, -1) !== DIRECTORY_SEPARATOR)
			$dir .= DIRECTORY_SEPARATOR;

		if (!file_exists($dir) || !is_dir($dir))
			throw new \RuntimeException('Directory does not exist.');

		if (!is_writable($dir))
			throw new \RuntimeException('Directory is not writable.');

		$this->dir = $dir;
	}

	/** Get key */
	public function get($key) {
		$fname = $this->getFilePath($key);
		if (!file_exists($fname))
			return null;

		$data = file($fname);
		if (time() > $data[0] && $data[0] != -1)
			return null;

		return $data[1];
	}

	/** Set key */
	public function set($key, $data, $ttl) {
		$fname = $this->getFilePath($key);
		$tempName = tempname(); // $fname . '-' . mt_rand(10000, 99999);
		file_put_contents($tempName, time() + $ttl . "\n" . $data);
		return rename($tempName, $fname);
	}

	/** Delete key */
	public function del($key) {
		$fname = $this->getFilePath($key);
		if (!file_exists($fname))
			return true;
		return @unlink($fname);
	}
	
	/** Flush cache */
	public function flush() {
		$files = glob($this->dir . '*');
		foreach($files as $file) {
		  if (is_file($file))
			@unlink($file);
		}
	}

	/** Get fully qualified path to file */
	protected function getFilePath($key) {
		return $this->dir . $key;
	}
}