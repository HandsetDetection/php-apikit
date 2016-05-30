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
** USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
** DAMAGE.
**
** --
**
** This is a reference implementation for interfacing with www.handsetdetection.com
**
*/

namespace HandsetDetection;

use HandsetDetection\HDBase;
use HandsetDetection\HDDevice;

/**
 * HD4 Class
 */
class HD4 extends HDBase {
	var $realm = 'APIv4';
	var $reply = null;
	var $rawreply = null;
	var $detectRequest = array();
	var $error = '';
	var $logger = null;
	var $debug = false;
	var $configFile = 'hdconfig.php';
	
	var $config = array (
		'username' => '',
		'secret' => '',
		'site_id' => '',
		'use_proxy' => 0,
		'proxy_server' => '',
		'proxy_port' => '',
		'proxy_user' => '',
		'proxy_pass' => '',
		'use_local' => 0,
		'api_server' => 'api.handsetdetection.com',
		'timeout' => 10,
		'debug' => false,
		'filesdir' => '',
		'retries' => 3,
		'cache_requests' => false,
		'geoip' => false,
		'log_unknown' => true
	);	
	
	var $tree = array();
	
	/**
	 * This is the main constructor for the class HD4
	 *
	 * @param mixed $config can be an array of config options or a fully qualified path to an alternate config file.
	 * @return void
	 */
	function __construct($config = null) {
		parent::__construct();
		$this->setConfig($config);
			
		if (empty($this->config['username'])) {
			throw new \Exception('Error : API username not set. Download a premade config from your Site Settings.');
		} elseif (empty($this->config['secret'])) {
			throw new \Exception('Error : API secret not set. Download a premade config from your Site Settings.');
		}

		if (! empty($this->config['use_local']) && ! class_exists('ZipArchive')) {
			throw new \Exception('Ultimate detection needs ZipArchive to unzip archive files. Please install this php module.');
		}
		
		$this->setup();
	}

	function setLocalDetection($enable){ $this->config['use_local'] = $enable;}
	function setProxyUser($user){ $this->config['proxy_user'] = $user; }
	function setProxyPass($pass){ $this->config['proxy_pass'] = $pass; }
	function setUseProxy($proxy){ $this->config['use_proxy'] = $proxy; }
	function setProxyServer($name) { $this->config['proxy_server'] = $name; }
	function setProxyPort($number) {$this->config['proxy_port'] = $number; }
	function setSecret($secret) { $this->config['secret'] = $secret; }
	function setUsername($user) { $this->config['username'] = $user; }
	function setTimeout($timeout) { $this->config['timeout'] = $timeout; }
	function setDetectVar($key, $value) { $this->detectRequest[strtolower($key)] = $value; }
	function setSiteId($siteid) { $this->config['site_id'] = (int) $siteid; }
	function setUseLocal($value) { $this->config['use_local'] = $value; }
	function setApiServer($value) { $this->config['api_server'] = $value; }
	function setLogger($function) { $this->config['logger'] = $function; }
	function setFilesDir($directory) {
		$this->config['filesdir'] = $directory;
		if (! $this->Store->setDirectory($directory)) {
			throw new InvalidCacheDirectoryException('Error : Failed to create cache directory in ('.$directory.'). Set your filesdir config setting or check directory permissions.');
		}
	}

	function getLocalDetection() { return $this->config['use_local']; }
	function getProxyUser(){ return $this->config['proxy_user']; }
	function getProxyPass(){ return $this->config['proxy_pass']; }
	function getUseProxy(){ return $this->config['use_proxy']; }
	function getProxyServer(){ return $this->config['proxy_server']; }
	function getProxyPort(){ return $this->config['proxy_port']; }
	function getError() { return $this->error; }	// Backwards compatibility with previous PHP api kits.
	function getErrorMsg() { return $this->error; }
	function getSecret() { return $this->config['secret']; }
	function getUsername() { return $this->config['username']; }
	function getTimeout() { return $this->config['timeout']; }
	function getReply() { return $this->reply; }
	function getRawReply() { return $this->rawreply; }
	function getSiteId() { return $this->config['site_id']; }
	function getUseLocal() { return $this->config['use_local']; }
	function getApiServer() { return $this->config['api_server']; }
	function getDetectRequest() { return $this->detectRequest; }
	function getFilesDir() { return $this->config['filesdir']; }
	
	/**
	 * Set config file
	 *
	 * @param array $config An assoc array of config data
	 * @return true on success, false otherwise
	 **/
	function setConfig($cfg) {
		$config = array();
		if (is_array($cfg)) {
			$config = $cfg;
		} elseif (is_string($cfg)) {
			// Sets $hdconfig
			require($cfg);
			$config = $hdconfig;
		}
		
		foreach($config as $key => $value)
			$this->config[$key] = $value;

		if (empty($this->config['filesdir']))
			$this->config['filesdir'] = dirname(__FILE__);

		$this->Store = HDStore::getInstance();
		$this->Store->setConfig($this->config, true);
		$this->Cache = new HDCache($this->config);
		$this->Device = new HDDevice($this->config);
		return true;
	}
	
	/**
	 * Setup the api kit with the current HTTP headers.
	 * 
	 * This is likely what you want to send if you're invoking this class for each new website visitor.
	 * You can override or add to these with setDetectVar($key, $value)
	 * 
	 * @param void
	 * @return void
	 */	
	function setup() {
		$this->reply = array();
		$this->rawreply = array();
		$this->detectRequest = array();

		if (function_exists('apache_request_headers')) {
			$this->detectRequest = apache_request_headers();
		} else {
			// From http://php.net/manual/en/function.apache-request-headers.php
			$rx_http = '/\AHTTP_/';
			foreach($_SERVER as $key => $val) {
				if (preg_match($rx_http, $key) ) {
					$arh_key = preg_replace($rx_http, '', $key);
					$rx_matches = array();
					// do some nasty string manipulations to restore the original letter case
					// this should work in most cases
					$rx_matches = explode('_', $arh_key);
					if (count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
						foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
						$arh_key = implode('-', $rx_matches);
					}
					$this->detectRequest[$arh_key] = $val;
				}
			}
		}

		if (! $this->getUseLocal() && @$this->config['geoip']) {
			// Ip address only used in cloud mode
			$this->detectRequest['ipaddress'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
		}
		unset($this->detectRequest['Cookie']);
	}

	/**
	 * List all known vendors
	 *
	 * @param void
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */
	function deviceVendors() {
		if (empty($this->config['use_local']))
			return $this->remote("device/vendors", null);
		
		if ($this->Device->localVendors())
			$this->reply = $this->Device->getReply();

		return $this->setError($this->Device->getStatus(), $this->Device->getMessage());
	}
	
	/**
	 * List all models for a given vendor
	 *
	 * @param string $vendor The device vendor eg Apple
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */	
	function deviceModels($vendor) {
		if (empty($this->config['use_local']))
			return $this->remote("device/models/$vendor", null);

		if ($this->Device->localModels($vendor))
			$this->reply = $this->Device->getReply();

		return $this->setError($this->Device->getStatus(), $this->Device->getMessage());
	}
	
	/**
	 * Find properties for a specific device
	 *
	 * @param string $vendor The device vendor eg. Nokia
	 * @param string $model The deviec model eg. N95
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */
	function deviceView($vendor, $model) {
		if (empty($this->config['use_local']))
			return $this->remote("device/view/$vendor/$model", null);
		
		if ($this->Device->localView($vendor, $model))
			$this->reply = $this->Device->getReply();

		return $this->setError($this->Device->getStatus(), $this->Device->getMessage());
	}
	
	/**
	 * Find which devices have property 'X'.
	 *
	 * @param string $key Property to inquire about eg 'network', 'connectors' etc...
	 * @param string $value Value to inquire about eg 'CDMA', 'USB' etc ...
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success. 
	 */	
	function deviceWhatHas($key, $value) {
		if (empty($this->config['use_local']))
			return $this->remote("device/whathas/$key/$value", null);
		
		if ($this->Device->localWhatHas($key, $value))
			$this->reply = $this->Device->getReply();
			
		return $this->setError($this->Device->getStatus(), $this->Device->getMessage());
	}
		
	/**
	 * Device Detect
	 *
	 * @param array $data : Data for device detection : HTTP Headers usually
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */		
	function deviceDetect($data=null) {
		$id = (int) (empty($data['id']) ? $this->config['site_id'] : $data['id']);
		$requestBody = array_merge($this->detectRequest, (array) $data);
		$fastKey = '';
		
		// If caching enabled then check cache
		if (! empty($this->config['cache_requests'])) {
			$headers = array_change_key_case($requestBody);
			ksort($headers);
			$fastKey = preg_replace('/ /','', json_encode($headers));
			if ($reply = $this->Cache->read($fastKey)) {
				$this->reply = $reply;
				$this->rawReply = '';
				return $this->setError(0, "OK");
			}
		}
		
		if ($this->config['use_local']) {						
			//$this->log("Starting Local Detection");
			$result = $this->Device->localDetect($requestBody);
			$this->setError($this->Device->getStatus(), $this->Device->getMessage());
			$this->setReply($this->Device->getReply());
			//$this->log("Finishing Local Detection : result is ($result)");
			// Log unknown headers if enabled
			if ($this->config['log_unknown'] == true && ! $result) {
				$this->send_remote_syslog($requestBody);
			}
		} else {
			//$this->log("Starting API Detection");
			$result = $this->remote("device/detect/$id", $requestBody);
			//$this->log("Finishing API Detection : result is ($result)");
		}

		// If we got a result then cache it
		if ($result && ! empty($this->config['cache_requests']))
			$this->Cache->write($fastKey, $this->reply);

		return $result;
	}

	/**
	 * Fetch an archive from handset detection which contains all the device specs and matching trees as individual json files.
	 *
	 * @param void
	 * @returns hd_specs data on success, false otherwise
	 */
	function deviceFetchArchive() {
		if (! $this->remote("device/fetcharchive", '', 'zip'))
			return false;

		$data = $this->getRawReply();

		if (empty($data))
			return $this->setError(299, 'Error : FetchArchive failed. Bad Download. File is zero length');
		elseif (strlen($data) < 9000000) {
			$trythis = json_decode($data, true);
			if (! empty($trythis) && isset($trythis['status']) && isset($trythis['message']))
				return $this->setError((int) $trythis['status'], @$trythis['message']);
			return $this->setError(299, 'Error : FetchArchive failed. Bad Download. File too short at '.strlen($data).' bytes.');
		}

		$status = file_put_contents($this->config['filesdir'] . DIRECTORY_SEPARATOR . "ultimate.zip", $this->getRawReply());
		if ($status === false)
			return $this->setError(299, "Error : FetchArchive failed. Could not write ". $this->config['filesdir'] . DIRECTORY_SEPARATOR . "ultimate.zip");

		return $this->installArchive($this->config['filesdir'] . DIRECTORY_SEPARATOR . "ultimate.zip");
	}

	/**
	 * Community Fetch Archive - Fetch the community archive version
	 *
	 * @param void
	 * @returns hd_specs data on success, false otherwise
	 */
	function communityFetchArchive() {
		if (! $this->remote("community/fetcharchive", '', 'zip', false))
			return false;

		$data = $this->getRawReply();

		if (empty($data))
			return $this->setError(299, 'Error : FetchArchive failed. Bad Download. File is zero length');
		elseif (strlen($data) < 900000) {
			$trythis = json_decode($data, true);
			if (! empty($trythis) && isset($trythis['status']) && isset($trythis['message']))
				return $this->setError((int) $trythis['status'], @$trythis['message']);
			return $this->setError(299, 'Error : FetchArchive failed. Bad Download. File too short at '.strlen($data).' bytes.');
		}

		$status = file_put_contents($this->config['filesdir'] . DIRECTORY_SEPARATOR . "ultimate.zip", $this->getRawReply());
		if ($status === false)
			return $this->setError(299, "Error : FetchArchive failed. Could not write ". $this->config['filesdir'] . DIRECTORY_SEPARATOR . "ultimate.zip");

		return $this->installArchive($this->config['filesdir'] . DIRECTORY_SEPARATOR . "ultimate.zip");
	}	
	/**
	 * Install an ultimate archive file
	 *
	 * @param string $file Fully qualified path to file
	 * @return boolean true on success, false otherwise
	 **/
	function installArchive($file) {
		// Unzip the archive and cache the individual files
		if (! class_exists('ZipArchive'))
			return $this->setError(299, "Error : Failed to open ". $this->config['filesdir'] . DIRECTORY_SEPARATOR . "ultimate.zip, is the ZIP module installed ?");

		$zip = new \ZipArchive();
		if ($zip->open($file) === false)
			return $this->setError(299, "Error : Failed to open ". $this->config['filesdir'] . DIRECTORY_SEPARATOR . "ultimate.zip");

		for ($i = 0; $i < $zip->numFiles; $i++) {
			$filename = $zip->getNameIndex($i);
			$zip->extractTo(sys_get_temp_dir(), $filename);
			$this->Store->moveIn(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename, $filename);
		}
		$zip->close();
		return true;
	}

	/**
	 * This method can indicate if using the js Helper would yeild more accurate results.
	 *
	 * @param array $headers
	 * @return true if helpful, false otherwise.
	 **/
	function isHelperUseful($headers) {
		return $this->Device->isHelperUseful($headers);
	}
}