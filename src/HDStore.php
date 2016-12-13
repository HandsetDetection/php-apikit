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
 * A file backed storage class
 **/

namespace HandsetDetection;

use HandsetDetection\HDCache;

class HDStore implements \Iterator {
	public $dirname = "hd40store";
	var $path = "";
	var $directory = "";
	private $Cache = null;
	private static $_instance = null;
	private $config = array();
	private $indexPosition = 0;
	private $indexArray = array();
	
	/**
	 * Constructor
	 *
	 **/
	private function __construct() {
		$this->indexPosition = 0;
		$this->indexArray = array();
	}

	/**
	 * Get the Singleton
	 *
	 * @param void
	 * @return Object $_instance
	 **/
	public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

	/**
	 * Sets the storage config options, optionally creating the storage directory.
	 *
	 * @param array $config An assoc array of config info.
	 * @param boolean $createDirectory
	 * @return void
	 **/
	public function setConfig($config, $createDirectory=false) {
		foreach((array) $config as $key => $value)
			$this->config[$key] = $value;

		$this->path = empty($this->config['filesdir']) ? dirname(__FILE__) : $this->config['filesdir'];
		$this->directory = $this->path . DIRECTORY_SEPARATOR . $this->dirname;
		$this->Cache = new HDCache($this->config);

		if ($createDirectory) {
			if (! is_dir($this->directory)) {
				if (! mkdir($this->directory)) {
					throw new \Exception('Error : Failed to create storage directory at ('.$this->directory.'). Check permissions.');
				}
			}
		}
	}

	/**
	 * Write data to cache & disk
	 *
	 * @param string $key
	 * @param array $data
	 * @return boolean true on success, false otherwise
	 */
	function write($key, $data) {
		if (empty($data))
			return false;

		if (! $this->store($key, $data))
			return false;

		return $this->Cache->write($key, $data);
	}

	/**
	 * Store data to disk
	 *
	 * @param string $key The search key (becomes the filename .. so keep it alphanumeric)
	 * @param array $data Data to persist (will be persisted in json format)
	 * @return boolean true on success, false otherwise
	 */
	function store($key, $data) {
		$jsonstr = json_encode($data);
		if (! @file_put_contents($this->directory . DIRECTORY_SEPARATOR . $key . '.json', $jsonstr))
			return false;
		return true;
	}

	/**
	 * Read $data, try cache first
	 *
	 * @param sting $key Key to search for
	 * @return boolean true on success, false
	 */
	function read($key) {
		$reply = $this->Cache->read($key);
		if ($reply)
			return $reply;

		if (! $reply = $this->fetch($key))
			return false;

		if (! $this->Cache->write($key, $reply))
			return false;

		return $reply;
	}

	/**
	 * Fetch data from disk
	 *
	 * @param string $key.
	 * @reply mixed
	 **/
	function fetch($key) {
		$jsonstr = @file_get_contents($this->directory . DIRECTORY_SEPARATOR . $key . '.json');
		if ($jsonstr === false || empty($jsonstr)) {
			return false;
		}
		return json_decode($jsonstr, true);
	}
	
	/**
	 * Returns all devices inside one giant array
	 *
	 * Used by localDevice* functions to iterate over all devies
	 *
	 * @param void
	 * @return array All devices in one giant assoc array
	 **/
	function fetchDevices() {
		$data = array();
		foreach(glob($this->directory . DIRECTORY_SEPARATOR . 'Device*.json') as $file) {
			$jsonstr = @file_get_contents($file);
			if ($jsonstr === false || empty($jsonstr)) {
				return false;
			}
			$data['devices'][] = json_decode($jsonstr, true);
		}
		return $data;
	}

	/**
	 * Moves a json file into storage.
	 *
	 * @param string $srcAbsName The fully qualified path and file name eg /tmp/sjjhas778hsjhh
	 * @param string $destName The key name inside the cache eg Device_19.json
	 * @return boolean true on success, false otherwise
	 */
	function moveIn($srcAbsName, $destName) {
		return rename($srcAbsName, $this->directory . DIRECTORY_SEPARATOR . $destName);
	}

	/**
	 * Cleans out the store - Use with caution
	 *
	 * @param void
	 * @return true on success, false otherwise
	 **/
	function purge() {
		$files = glob($this->directory . DIRECTORY_SEPARATOR . '*.json');
		foreach($files as $file) {
			if (is_file($file)) {
				if (! unlink($file)) {
					return false;
				}
			}
		}
		return $this->Cache->purge();
	}

	/**
	 * Rewind - iterator for looping over the device list
	 *
	 * @param void
	 * @return void
	 **/
    function rewind() {
        $this->indexPosition = 0;
		// Build the device list.
		$this->indexArray = array();
		foreach(glob($this->directory . DIRECTORY_SEPARATOR . 'Device*.json') as $file) {
			$this->indexArray[] = preg_replace('/\.json$/', '', basename($file));
		}
    }

	/**
	 * Current - return current device pointer
	 *
	 * @param void
	 * @return array device
	 **/
    function current() {
		$file = $this->indexArray[$this->indexPosition];
        return $this->fetch($file);
    }

	/**
	 * Current - return current device key
	 *
	 * @param void
	 * @return string device
	 **/
    function key() {
        return $this->indexArray[$this->indexPosition];
    }

	/**
	 * Next - move to the next device
	 *
	 * @param void
	 * @return array device
	 **/
    function next() {
        ++$this->indexPosition;
    }

	/**
	 * Valid - Is the current entry valid
	 *
	 * @param void
	 * @return array device
	 **/	
    function valid() {
        return isset($this->indexArray[$this->indexPosition]);
    }
}