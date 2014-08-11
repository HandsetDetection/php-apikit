<?php
/*
** Copyright (c) Richard Uren 2012 - 2013 <richard@teleport.com.au>
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

//
// NOTE NOTE NOTE : The system requires about 30M of free APC cache. If the cache fills up 
// and stuff gets purged then we go to file and you know how that works - Slowly. 
// The api.ini config file setting is apc.shm_size = xxM
// where xx is 32 by default, make it 64 or greater to be extra happy :)
if (! function_exists('json_encode')) {
	require_once('json.php');
}

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

// From http://php.net/manual/en/function.apache-request-headers.php
if (! function_exists('apache_request_headers')) {
	function apache_request_headers() {
		$arh = array();
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
				$arh[$arh_key] = $val;
			}
		}
		return( $arh );
	}
}

/**
 * Caching class with optional persistance to disk
 * Primary role is to persist objects for Ultimate detection
 *
 * Notes :
 *  - Cache objects may be > 1Mb when serialized which makes memcache a bad choice (1Mb limit).
 *  - Consider php-igbinary to improve serialization performance in time critical situations.
 *  - Uses PHP's apc module (use uAPC in php 5.5 or greater)
 */
if (! class_exists('HD3Cache')) {
	class HD3Cache {
		var $prefix = 'hd34';
		var $dirpath = "";
		public $dirname = "hd34cache";
		var $duration = 7200;

		function getCacheDir() { return $this->dirpath . DS . $this->dirname; }
		function setDirectory($dir) {
			$this->dirpath = $dir;
			if (! is_dir($this->dirpath. DS . $this->dirname)) {
				if (! mkdir($this->dirpath. DS . $this->dirname)) {
					return false;
				}
			}			
			return true;
		}
		
		/**
		 * Read the key from memory cache
		 *
		 * @param string $key
		 * @return $data if found, false otherwise
		 */
		function read($key) {									
			return apc_fetch($this->prefix.$key);			
		}
	
		/**
		 * Write data to memory cache accessed by $key
		 *
		 * @param string $key
		 * @param array $data
		 * @return bool true on success, false otherwise 
		 */
		function write($key, $data) {
			if (empty($data))
				return false;
			
			return apc_store($this->prefix.$key, $data, $this->duration);
		}
	
		/**
		 * Trys memory and filesystem to find data.
		 *
		 * If not found in memory and found on disk then save to memory for faster access later.
		 *
		 * @param sting $key Key to search for
		 * @return bool true on success, false otherwise
		 */
		function readStore($key) {
			$reply = $this->read($key);
			if ($reply)
				return $reply;

			$jsonstr = @file_get_contents($this->dirpath . DS . $this->dirname . DS . $key . '.json');					
			if ($jsonstr === false || empty($jsonstr)) {
				return false;
			}
			$reply = $this->__decode($jsonstr);
			
			// Save into memory cache for later
			$this->write($key, $reply);
			return $reply;
		}
		
		/**
		 * Persists data to disk (does not save in memory, readStore will load it onto memory if required)
		 *
		 * @param string $key The search key
		 * @param array $data Data to persist (will be persisted in json format)
		 * @return bool true on success, false otherwise
		 */
		function writeStore($key, $data) {
			$jsonstr = $this->__encode($data);
			if (! @file_put_contents($this->dirpath . DS . $this->dirname . DS . $key . '.json', $jsonstr))
				return false;
			return true;
		}

		/**
		 * Writes a file into the disk store
		 *
		 * Note : File data must be a json string if you ever intend to read it back later with readStore()
		 * 
		 * @param string $srcAbsName The fully qualified path and file name eg /tmp/sjjhas778hsjhh
		 * @param string $destName The key name inside the cache eg Device_19.json 
		 * @return bool true on success, false otherwise
		 */
		function writeFile($srcAbsName, $destName) {
			return @rename($srcAbsName, $this->dirpath . DS . $this->dirname . DS . $destName);
		}
		
		 /**
		  * Used by the local functions. Reads the Device specs into one large array.
		  *
		  * @param null
		  * @return $data
		  */
		function readSpecs() {
			$dir = $this->dirpath . DS . $this->dirname;			
			foreach(glob($this->dirpath . DS . $this->dirname . DS . 'Device*.json') as $file) {				
				$jsonstr = @file_get_contents($file);
				if ($jsonstr === false || empty($jsonstr)) {
					return false;
				}
				$data['devices'][] = $this->__decode($jsonstr);
			}			
			return $data;
		}

		/**
		 * Encodes php assoc array to json string
		 *
		 * @param json $data
		 * @return $data
		 */
		function __encode($data) {
			if (function_exists('json_encode')) {
				$jsondata = json_encode($data);
			} else {
				$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
				$jsondata = $json->encode($data);
			}
			return $jsondata;
		}
				
		/**
		 * Decode turns json string into php assoc array
		 *
		 * @param json $jsonstr
		 * @return $data
		 */
		function __decode($jsonstr) {
			$data = array();
			if (function_exists('json_decode')) {
				$data = json_decode($jsonstr, true);
			} else {
				$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
				$data = $json->decode($jsonstr);			
			}
			return $data;
		}
	}
}

/**
 *
 * HD3 class 
 *
 */
class HD3 {
	var $realm = 'APIv3';
	var $reply = null;
	var $rawreply = null;
	var $detectRequest = array();
	var $error = '';
	var $logger = null;
	var $debug = false;
	var $configFile = 'hdconfig.php';				// hdconfig.php is the v3x PHP config file name.
	
	var $config = array (
		'username' => '',
		'secret' => '',
		'site_id' => '',
		'mobile_site' => '',
		'use_proxy' => 0,
		'proxy_server' => '',
		'proxy_port' => '',
		'proxy_user' => '',
		'proxy_pass' => '',
		'use_local' => 0,
		'non_mobile' => "/^Feedfetcher|^FAST|^gsa_crawler|^Crawler|^goroam|^GameTracker|^http:\/\/|^Lynx|^Link|^LegalX|libwww|^LWP::Simple|FunWebProducts|^Nambu|^WordPress|^yacybot|^YahooFeedSeeker|^Yandex|^MovableType|^Baiduspider|SpamBlockerUtility|AOLBuild|Link Checker|Media Center|Creative ZENcast|GoogleToolbar|MEGAUPLOAD|Alexa Toolbar|^User-Agent|SIMBAR|Wazzup|PeoplePal|GTB5|Dealio Toolbar|Zango|MathPlayer|Hotbar|Comcast Install|WebMoney Advisor|OfficeLiveConnector|IEMB3|GTB6|Avant Browser|America Online Browser|SearchSystem|WinTSI|FBSMTWB|NET_lghpset/",
		'match_filter' => " _\\#-,./:\"'",
		'api_server' => 'api.handsetdetection.com',
		'timeout' => 10,
		'debug' => false,
		'filesdir' => '',
		'retries' => 3
	);	
	
	var $tree = array();
	
	/**
	 * This is the main constructor for the class HD3
	 *
	 * @param array $config Optional config info will override the config file.
	 * @return void
	 */
	function HD3($config = null) {				
		if (! empty($config)) {
			$this->config = array_merge($this->config, $config);
		} elseif (! file_exists($this->configFile)) {
			throw new Exception ('Error : Invalid config file and no config passed to constructor');
		} else {
			$hdconfig = array();
			// Note : require not require_once as multiple invocations will require config file again.
			require($this->configFile);
			$this->config = array_merge($this->config, (array) $hdconfig);
		}
							
		if (empty($this->config['username'])) {
			throw new Exception('Error : API username not set. Download a premade config from your Site Settings.');
		} elseif (empty($this->config['secret'])) {
			throw new Exception('Error : API secret not set. Download a premade config from your Site Settings.');
		}		
		if (empty($this->config['filesdir'])) $this->config['filesdir'] = dirname(__FILE__);
		$this->match_filter = preg_split('//', $this->config['match_filter'], null, PREG_SPLIT_NO_EMPTY);
		$this->debug = $this->config['debug'];
		if ($this->debug) $this->__log('Config '.print_r($this->config, true));

		$this->Cache = new HD3Cache();
		$this->setFilesDir($this->config['filesdir']);
		$this->setup();
	}
	
	function setLocalDetection($enable){ $this->config['use_local'] = $enable;}	
	function setProxyUser($user){ $this->config['proxy_user'] = $user; }
	function setProxyPass($pass){ $this->config['proxy_pass'] = $pass; }
	function setUseProxy($proxy){ $this->config['use_proxy'] = $proxy; }
	function setProxyServer($name) { $this->config['proxy_server'] = $name; }
	function setProxyPort($number) {$this->config['proxy_port'] = $number; }
	function setMobileSite($mobile_site) { $this->config['mobile_site'] = $mobile_site; }
	function setSecret($secret) { $this->config['secret'] = $secret; }
	function setUsername($user) { $this->config['username'] = $user; }
	function setTimeout($timeout) { $this->config['timeout'] = $timeout; }
	function setDetectVar($key, $value) { $this->detectRequest[strtolower($key)] = $value; }
	function setSiteId($siteid) { $this->config['site_id'] = (int) $siteid; }
	function setUseLocal($value) { $this->config['use_local'] = $value; }
	function setApiServer($value) { $this->config['api_server'] = $value; }
	function setReply($reply) { $this->reply = $reply; }
	function setLogger($function) { $this->config['logger'] = $function; }
	function setFilesDir($directory) {
		$this->config['filesdir'] = $directory;
		if (! $this->Cache->setDirectory($directory)) {				
			throw new InvalidCacheDirectoryException('Error : Failed to create cache directory in ('.$directory.'). Set your filesdir config setting or check directory permissions.');
		}
	}
	
	function setError($status, $msg, $class=null) { 
		$this->error = $msg; 
		if ($this->debug) $this->__log($msg); 
		$this->reply['status'] = $status;
		$this->reply['message'] = $msg; 
		if (! empty($class)) $this->reply['class'] = $class;
		if ($status > 0) return false;
		return true;
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
	
	// Log function - User defined functions can be supplied in the 'logger' config variable.
	private function __log($msg) {
		//syslog(LOG_NOTICE, microtime()." ".$msg);
		if (isset($this->config['logger']) && is_callable($this->config['logger'])) {
			call_user_func($this->config['logger'], $msg);
		}
	}	
	
	/**
	 * Helper for encoding JSON	  	  
	 *
	 * @param string $data
	 * @return $jsondata 
	 */
	private function __encode($data) {
		if (function_exists('json_encode')) {
			$jsondata = json_encode($data);
		} else {
			$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
			$jsondata = $json->encode($data);
		}
		return $jsondata;
	}
	
	/**
	 * Helper for Decoding JSON	  
	 *
	 * @param string $jsonstr
	 * @return $data 
	 */
	private function __decode($jsonstr) {
		$data = array();
		if (function_exists('json_decode')) {
			$data = json_decode($jsonstr, true);
		} else {
			$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
			$data = $json->decode($jsonstr);			
		}
		return $data;
	}
	
	/**
	 * Redirect you to the mobile site : LEGACY FUNCTION, DEPRECATED in APIKit 4.0
	 *
	 * @param void
	 * @return void 
	 */	
	function redirectToMobileSite(){
		if ($this->mobile_site != '') {
			header('Location: '.$this->mobile_site);
			exit;
		} 
	} 
		
	/**
	 * Setup the api kit with the current HTTP headers.
	 * 
	 * This is likely what you want to send if you're invoking this class for each new website visitor.
	 * You can override or add to these with setDetectVar($key, $value)
	 * 
	 * @param 
	 * @return void 
	 */	
	function setup() {
		$this->reply = array();
		$this->rawreply = array();
		$this->detectRequest = apache_request_headers();
		if (! $this->getUseLocal()) {
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
		return ($this->config['use_local'] ? $this->_localDeviceVendors() : $this->_remote('device/vendors', null));
	}
	
	/**
	 * List all models for a given vendor
	 *
	 * @param string $vendor The device vendor eg Apple
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success. 
	 */	
	function deviceModels($vendor) {
		return ($this->config['use_local'] ? $this->_localDeviceModels($vendor) : $this->_remote("device/models/$vendor", null));
	}
	
	/**
	 * Find properties for a specific device
	 *
	 * @param string $vendor The device vendor eg. Nokia 
	 * @param string $model The deviec model eg. N95
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success. 
	 */	
	function deviceView($vendor, $model) {
		return ($this->config['use_local'] ? $this->_localDeviceView($vendor, $model) : $this->_remote("device/view/$vendor/$model", null));
	}
	
	/**
	 * Find which devices have property 'X'.
	 *
	 * @param string $key Property to inquire about eg 'network', 'connectors' etc...
	 * @param string $value Value to inquire about eg 'CDMA', 'USB' etc ... 
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success. 
	 */	
	function deviceWhatHas($key, $value) {
		return ($this->config['use_local'] ? $this->_localDeviceWhatHas($key, $value) : $this->_remote("device/whathas/$key/$value", null));
	}
	
	/**
	 * Add a site : Stub for forthcoming feature
	 *
	 * @param array $data
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */	
	function siteAdd($data) {
		return $this->_remote("site/add", $data);
	}
	
	/**
	 * Site Edit : Stub for forthcoming feature
	 *
	 * @param array $data
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */	
	function siteEdit($data) {
		$id = (int) (empty($data['id']) ? $this->config['site_id'] : $data['id']);
		return $this->_remote("site/edit/$id", $data);
	}
	
	/**
	 * Site View : Stub for forthcoming feature
	 *
	 * @param int $id Site Id
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */	
	function siteView($id=null) {
		$id = (int) (empty($id) ? $this->config['site_id'] : $id);
		return $this->_remote("site/view/$id", null);
	}
	
	/**
	 * Site Delete : Stub for forthcoming feature
	 *
	 * @param int $id Site Id.
	 * @return bool true on success, false otherwise.
	 */	
	function siteDelete($id=null) {
		$id = (int) (empty($id) ? $this->config['site_id'] : $id);
		return $this->_remote("site/delete/$id", null);
	}
	
	/**
	 * Site Detect
	 *
	 * @param array $data : Data for device detection : HTTP Headers usually
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */		
	function siteDetect($data=null) {		
		$id = (int) (empty($data['id']) ? $this->config['site_id'] : $data['id']);
		$requestBody = array_merge($this->detectRequest, (array) $data);
		
		// Dont send detection requests if non_mobile matches
		// Prevent bots & spiders (search engines) chalking up high detection counts.
		if (! empty($requestBody['user-agent']) && preg_match($this->config['non_mobile'], $requestBody['user-agent'])) {
			return $this->setError(301, 'Notice : FastFail, Probable bot, spider or script');
		}				
		if ($this->config['use_local']) {						
			if ($this->debug) $this->__log("Starting Local Detection");						
			$result = $this->_localSiteDetect($requestBody);													
			if ($this->debug) $this->__log("Finishing Local Detection : result is ($result)");			
			return $result;
		} else {			
			$result = $this->_remote("site/detect/$id", $requestBody);			
			if (! $result) {
				return false;
			}
			$reply = $this->getReply();			
			if (isset($reply['status']) && (int) $reply['status'] == 0 || $reply['status'] == "0") {
				return true;
			}			
			return false;
		}
	}
		
	/**
	 * Convenience Function to download trees and specs in one go : DEPRECATED in 4.0 : Replace by siteFetchArchive
	 *
	 * Note : Ultimate only
	 * 
	 * @param int $id Site Id
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */		
	function siteFetchAll($id=null) {
		$status = $this->siteFetchSpecs($id);
		if (! $status)
			return false;
		$status = $this->siteFetchTrees($id);		
		if (! $status)
			return false;
		return true;
	}

	/**
	 * Download detection trees. DEPRECATED in 4.0 : Replace by siteFetchArchive
	 *
	 * @param int $id Site Id
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */		
	function siteFetchTrees($id=null) {
		$id = (int) (empty($id) ? $this->config['site_id'] : $id);

		$status = $this->_remote("site/fetchtrees/$id", null);
		if (! $status) {
			// Error will be set in _remote
			return false;
		}

		$status = file_put_contents($this->config['filesdir'] . DS . "hd3trees.json", $this->getRawReply());
		if ($status === false)
			return $this->setError(299, 'Error : Unable to write trees file hd3trees.json to '.$this->config['filesdir']);

		// Write objects to disk cache
		$str = @file_get_contents($this->config['filesdir'] . DS . "hd3trees.json");
		if ($str === false || empty($str)) {
			return $this->setError(299, 'Error : Unable to open trees file hd3trees.json. Is it there ? Are premissions OK ?');
		}					
		$data = $this->__decode($str);
		foreach($data['trees'] as $key => $branch) {
			if ($this->debug) $this->__log("Caching ".$key);
			if (! $this->Cache->writeStore($key, $branch))
				return $this->setError(299, "Error : Could not write cache fragment. Key : $key, Size: ".strlen($branch));
		}
		return true;
	}
	
	/**
	 * Download device specs. DEPRECATED in 4.0 : Replace by siteFetchArchive
	 *
	 * @param int $id Site Id
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success. 
	 */		
	function siteFetchSpecs($id=null) {		
		$id = (int) (empty($id) ? $this->config['site_id'] : $id);

		$status = $this->_remote("site/fetchspecs/$id", null);
		// Error will be set in _remote
		if (! $status)
			return false;
			
		$status = file_put_contents($this->config['filesdir'] . DS . "hd3specs.json", $this->getRawReply());
		if ($status === false)
			return $this->setError(299, "Error : Unable to write specs file hd3specs.json to ".$this->config['filesdir']);

		// Write objects to disk cache
		$str = @file_get_contents($this->config['filesdir'] . DS . "hd3specs.json");
		if ($str === false || empty($str))
			return $this->setError(299, 'Error : Unable to open specs file hd3specs.json. Is it there ? Are premissions OK ?');
		
		$data = $this->__decode($str);

		if (! empty($data['devices'])) {
			foreach($data['devices'] as $device) {
				$device_id = $device['Device']['_id'];
				if (! $this->Cache->writeStore('Device_'.$device_id, $device))	
					return $this->setError(299, 'Error : Could not write cache fragment.');
			}
		}

		if (! empty($data['extras'])) {
			foreach($data['extras'] as $extra) {
				$extra_id = $extra['Extra']['_id'];
				if (! $this->Cache->writeStore('Extra_'.$extra_id, $extra))
					return $this->setError(299, 'Error : Could not write cache fragment.');
			}		
		}
					
		return true;
	}
	
	/**
	 * Fetch specs form the cache
	 *
	 * @param int $id ID of key to fetch. 16 or 23
	 * @param string $type Type of key to fetch 'Device' or 'Extra'
	 * @returns hd_specs data on success, false otherwise  
	 */		
	function _getCacheSpecs($id, $type) {					
		$data = $this->Cache->readStore($type.'_'.$id);
		if (isset($data[$type]['hd_specs']))
			return $data[$type]['hd_specs'];
		return false;
	}
	
	/**
	 * Fetch an archive from handset detection which contains all the device specs and matching trees as individual json files.
	 *
	 * @param int $id Site Id
	 * @returns hd_specs data on success, false otherwise  
	 */		
	function siteFetchArchive($id=null) {		
		$id = (int) (empty($id) ? $this->config['site_id'] : $id);

		$status = $this->_remote("site/fetcharchive/$id", "", 'zip');		
		
		if (! $status)
			return false;

		$data = $this->getRawReply();		
			
		if (empty($data)) 
			return $this->setError(299, 'Error : siteFetchArchive failed. Bad Download. File is zero length');
		elseif (strlen($data) < 9000000) {
			$trythis = $this->__decode($data);
			if (! empty($trythis) && isset($trythis['status']) && isset($trythis['message']))
				return $this->setError((int) $trythis['status'], "Error : ".@$trythis['status'].", Message : ".@$trythis['message']);
			return $this->setError(299, 'Error : siteFetchArchive failed. Bad Download. File too short.');
		}

		$status = file_put_contents($this->config['filesdir'] . DS . "ultimate.zip", $this->getRawReply());
		if ($status === false)
			return $this->setError(299, "Error : siteFetchArchive failed. Could not write ". $this->config['filesdir'] . DS . "ultimate.zip"); 

		// Unzip the archive and cache the individual files
		if (class_exists('ZipArchive')) {
			$zip = new ZipArchive();			
			if ($zip->open($this->config['filesdir'] . DS . "ultimate.zip") === TRUE) {
				for ($i = 0; $i < $zip->numFiles; $i++) {
					$filename = $zip->getNameIndex($i);					
					$filename = str_replace(":","_", $filename);				
					if (strpos($filename, "_")) 
						$zip->renameIndex($i, $filename);	
					$zip->extractTo(sys_get_temp_dir(), $filename);
					$this->Cache->writeFile(sys_get_temp_dir() . DS . $filename, $filename);
				}
    			$zip->close();
				return true;
			} else {
				return $this->setError(299, "Error : Failed to open ". $this->config['filesdir'] . DS . "ultimate.zip");
			}
		} else {
			return $this->setError(299, "Error : Failed to open ". $this->config['filesdir'] . DS . "ultimate.zip, is the ZIP module installed ?");
		}
		return false;
	}
	
	/**
	 * User actions. Always remote. Stub function for user actions implemented in a forthcoming release.
	 *
	 * @param string $suburl
	 * @param string $data
	 * @return bool true on success, false otherwise.
	 */		
	function user($suburl, $data=null) {
		return $this->_remote($suburl, $data); 
	}
		 	
	/**
	 * Makes requests to the various web services of Handset Detection.
	 *
	 * Note : $suburl - the url fragment of the web service eg site/detect/${site_id}
	 * 
	 * @param string $suburl
	 * @param string $data
	 * @param string $filetype
	 * @return bool true on success, false otherwise 
	 */		
	function _remote($suburl, $data, $filetype='json') {
		$this->reply = array();
		$this->rawreply = array();
		$this->setError(0, '');
			
		if (empty($data)) $data = array();
		$url = "/apiv3/$suburl.json";
		
		$attempts = $this->config['retries'] + 1;		
		$trys = 0;
		
		$requestdata = $this->__encode($data);			
		
		$success = false;
		while($trys++ < $attempts && $success === false) {			
			if ($this->debug) $this->__log("Connection attempt $trys");			
			$this->rawreply = $this->_post($this->config['api_server'], $url, $requestdata);			
			if ($this->rawreply === false) {
				$this->setError(299, "Error : Connection to $url Failed");
			} else {
				if ($filetype == 'json') {	
					$this->reply = $this->__decode($this->rawreply);
	
					if (empty($this->reply)) {
						$this->setError(299, "Error : Empty Reply.");
					} elseif (! isset($this->reply['status'])) {
						$this->setError(299, "Error : No status set in reply");
					} elseif ((int) $this->reply['status'] != 0) { 
						$this->setError(@$this->reply['status'], "Error : ".@$this->reply['status'].", Message : ".@$this->reply['message']);
						$trys = $attempts + 1;
					} else {
						$success = true;
					} 
				} else {
					$success = true;
				}
			}
		}
					
		return $success;
	}
		
	//************ Private Functions ***********//
	// From http://www.enyem.com/wiki/index.php/Send_POST_request_(PHP)
	// PHP 4/5 http post function
	// And modified to fit	
	function _post($server, $url, $jsondata) {				
		$host = $server;
		$port = 80;
		$timeout = $this->config['timeout'];
		$uri = parse_url($url);
		$realm = $this->realm;		
		$username = $this->config['username'];
		$nc = "00000001";
		$snonce = $this->realm;
		$cnonce = md5(time().$this->config['secret']);
		$qop = 'auth';
		
		if ($this->config['use_proxy']) {
			$host = $this->config['proxy_server'];
			$port = $this->config['proxy_port'];
		}

		// AuthDigest Components
		// http://en.wikipedia.org/wiki/Digest_access_authentication
		$ha1 = md5($username.':'.$realm.':'.$this->config['secret']);
		$ha2 = md5('POST:'.$uri['path']);
		$response = md5($ha1.':'.$snonce.':'.$nc.':'.$cnonce.':'.$qop.':'.$ha2);
		
		// * Connect *
		//echo "Connecting to $host, port $port, url $url<br/>";
		$errno = ""; 
		$errstr="";
		$fp = @fsockopen($host, $port, $errno, $errstr, $timeout); 		
		if (! $fp)
			return $this->setError(299, "Error : Cannot connect to $host, port $port timeout $timeout : ($errno) $errstr");

		if ($this->debug) $this->__log("Socket open to $host, port $port  timeout $timeout : ($errno) $errstr");

		//* * connection successful, write headers */
		// Use HTTP/1.0 (to disable content chunking on large replies).
		$out = "POST $url HTTP/1.0\r\n";  
		$out .= "Host: $server\r\n";
		if ($this->config['use_proxy'] && ! empty($this->config['proxy_user']) && ! empty($this->config['proxy_pass'])) {
			$out .= "Proxy-Authorization:Basic ".base64_encode("{$this->config['proxy_user']}:{$this->config['proxy_pass']}")."\r\n";
		}
		$out .= "Content-Type: application/json\r\n";
		$out .= 'Authorization: Digest '.
			'username="'.$username.'", '.
			'realm="'.$realm.'", '.
			'nonce="'.$snonce.'", '.
			'uri="'.$uri['path'].'", '.
			'qop='.$qop.', '.
            'nc='.$nc.', '.
            'cnonce="'.$cnonce.'", '.
            'response="'.$response.'", '.
            'opaque="'.$realm.'"'."\r\n";
		$out .= "Content-length: " . strlen($jsondata) . "\r\n\r\n";
		$out .= "$jsondata\r\n\r\n";
		
		if ($this->debug) $this->__log("Sending : $out");		
		fputs($fp, $out);
		
		$reply = "";
		$time = time();

		/*
		 * Get response. Badly behaving servers might not maintain or close the stream properly, 
		 * we need to check for a timeout if the server doesn't send anything.
		 */
		$timeout_status = FALSE;
		
		stream_set_blocking ( $fp, 0 );
		while ( ! feof( $fp )  and ! $timeout_status) {
			$r = fgets($fp, 1024*25);
			if ( $r ) {
				$reply .= $r;
				$time = time();
			}
			if ((time() - $time) > $timeout)
				$timeout_status = TRUE;
		}
		
		if ($this->debug) $this->__log($reply);
		
		if ($timeout_status == TRUE)
			return $this->setError(299, "Error : Timeout when reading the stream."); 	

		if (!feof($fp))
			return $this->setError(299, "Error : Socket not closed properly.");	

		fclose($fp); 

   		$hunks = explode("\r\n\r\n",$reply);
		
   		if (!is_array($hunks) or count($hunks) < 2)
			return $this->setError(299, "Error : Reply is too short.");

   		$header = $hunks[count($hunks) - 2];
   		$body = $hunks[count($hunks) - 1];
   		$headers = explode("\n",$header);

		if (strlen($body)) return $body;
		return $this->setError(299, "Error : Reply body is empty.");
	}
	
	/**
	 * Internal helper for building a list of all devices.
	 *
	 * @param void
	 * @return array List of all devices.
	 */		
	function _localGetSpecs() {
		$result = $this->Cache->readSpecs();						
		if (! $result)
			return $this->setError(299, "Error : _localGetSpecs cannot read files from cache.");
		return $result;
	}
	
	/**
	 * Find all device vendors
	 *
	 * @param void
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success. 
	 */		
	function _localDeviceVendors() {
		$this->reply = array();
		$data = $this->_localGetSpecs();
		if (empty($data))
			return false;

		$tmp = array();
		foreach($data['devices'] as $item) {
			$tmp[] = $item['Device']['hd_specs']['general_vendor'];
		}		
		$this->reply['vendor'] = array_unique($tmp);
		sort($this->reply['vendor']);
		return $this->setError(0, 'OK');
	}
	
	/**
	 * Find all models for the sepecified vendor
	 *
	 * @param string $vendor The device vendor
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */		
	function _localDeviceModels($vendor) {
		$this->reply = array();
		$data = $this->_localGetSpecs();
		if (empty($data))
			return false;

		$vendor = strtolower($vendor);
		$tmp = array();
		$trim = '';
		foreach($data['devices'] as $item) {
			if ($vendor === strtolower($item['Device']['hd_specs']['general_vendor'])) {
				$tmp[] = $item['Device']['hd_specs']['general_model'];
			}
			$key = $vendor." ";
			if (! empty($item['Device']['hd_specs']['general_aliases'])) {
				foreach($item['Device']['hd_specs']['general_aliases'] as $alias_item) {
					// Note : Position is 0, at the start of the string, NOT False.
					$result = stripos($alias_item, $key);
					if ($result == 0 && $result !== false) {
						$tmp[] = str_replace($key, '', $alias_item);
					}
				}
			}
		}
		sort($tmp);
		$this->reply['model'] = array_unique($tmp);
		return $this->setError(0, 'OK');
	}
	
	
	/**
	 * Finds all the specs for a specific device
	 *
	 * @param string $vendor The device vendor
	 * @param string $model The device model
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */		
	function _localDeviceView($vendor, $model) {
		$this->reply = array();
		$data = $this->_localGetSpecs();
		if (empty($data))
			return false;

		$vendor = strtolower($vendor);
		$model = strtolower($model);
		foreach($data['devices'] as $item) {
			if ($vendor === strtolower($item['Device']['hd_specs']['general_vendor']) && $model === strtolower($item['Device']['hd_specs']['general_model'])) {
				$this->reply['device'] = $item['Device']['hd_specs'];
				return $this->setError(0, 'OK');
			}
		}

		if ($this->debug) $this->__log("_localDeviceView finds no matching device with vendor ($vendor) and model ($model)");
		return $this->setError(301, 'Nothing found');
	}
	
	/**
	 * Finds all devices that have a specific property
	 *
	 * @param string $key
	 * @param string $value
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */		
	function _localDeviceWhatHas($key, $value) {
		$data = $this->_localGetSpecs();
		if (empty($data))
			return false;

		$tmp = array();
		$value = strtolower($value);
		foreach($data['devices'] as $item) {
			if (empty($item['Device']['hd_specs'][$key])) {
				continue;
			}
			
			$match = false;			
			if (is_array($item['Device']['hd_specs'][$key])) {
				foreach($item['Device']['hd_specs'][$key] as $check) {
					if (stristr($check, $value)) {
						$match = true;
					}
				}
			} elseif (stristr($item['Device']['hd_specs'][$key], $value)) {
				$match = true;
			}
			
			if ($match == true) {
				$tmp[] = array('id' => $item['Device']['_id'], 
					'general_vendor' => $item['Device']['hd_specs']['general_vendor'],
					'general_model' => $item['Device']['hd_specs']['general_model']);
			}
		}
		$this->reply['devices'] = $tmp;
		return $this->setError(0, 'OK');
	}
	
	/**
 	 * Perform a local detection 
	 *
	 * @param array $headers HTTP headers as an assoc array. keys are standard http header names eg user-agent, x-wap-profile 
	 * @return bool true on success, false otherwise
	 */			
	function _localSiteDetect($headers) {		
		$this->reply = array();
		$this->rawreply = array();
		$this->setError(0, '');
		$device = null;
		
		// Fast Cache Check
		ksort($headers);
		$fastKey = preg_replace('/ /','', $this->__encode($headers));
		if ($device = $this->Cache->read($fastKey)) {
			$this->reply['hd_specs'] = $device;
			$this->reply['class'] = (empty($device['general_type']) ? "Unknown" : $device['general_type']);
			return $this->setError(0, "OK");			
		}
		
		$id = $this->_getDevice($headers);							
		if ($id) {
			if ($this->debug) $this->__log("Looking to read $id from cache");		
			$device = $this->_getCacheSpecs($id, 'Device');			
			if ($device === false) {
				if ($this->debug) $this->__log("Cache problem : $id not found");
				return $this->setError(255, "$id not found in cache. Are files downloaded ?", 'Unknown');
			}
				
			if ($this->debug) $this->__log("$id fetched from cache");

			// Browser & OS (platform) detection
			$platform = array();
			$browser = array();
			
			$platform_id = $this->_getExtra('platform', $headers);
			$browser_id = $this->_getExtra('browser', $headers);
			if ($platform_id)
				$platform = $this->_getCacheSpecs($platform_id, 'Extra');
			if ($browser_id)
				$browser = $this->_getCacheSpecs($browser_id, 'Extra');

			if ($this->debug) $this->__log("platform ".print_r($platform, true));
			if ($this->debug) $this->__log("browser".print_r($browser, true));

			// Selective merge
			if (! empty($browser['general_browser'])) {
				$platform['general_browser'] = $browser['general_browser'];
				$platform['general_browser_version'] = $browser['general_browser_version'];
			}

			if (! empty($platform['general_platform'])) {
				$device['general_platform'] = $platform['general_platform'];
				$device['general_platform_version'] = $platform['general_platform_version'];
			}
			
			if (! empty($platform['general_browser'])) {
				$device['general_browser'] = $platform['general_browser'];
				$device['general_browser_version'] = $platform['general_browser_version'];
			}

			$this->Cache->write($fastKey, $device);			
			$this->reply['hd_specs'] = $device;
			$this->reply['class'] = (empty($device['general_type']) ? "Unknown" : $device['general_type']);
			return $this->setError(0, "OK");			
		}
		return $this->setError(301, 'Nothing Found', 'Unknown');
	}

	/**
	 * Hunts through the headers in a specific order looking for likely device matches 
	 *
	 * @param array $headers An assoc array or http headers
	 * @return bool int a device id on success, false otherwise
	 */		
	function _getDevice($headers) {				
		// Remember the agent for generic matching later.
		$agent = "";

		// Convert all headers to lowercase 
		$headers = array_change_key_case($headers);

		if ($this->debug) $this->__log('Working with headers of '.print_r($headers, true));
		if ($this->debug) $this->__log('Start Checking Opera Special headers');
		// Opera mini puts the vendor # model in the header - nice! ... sometimes it puts ? # ? in as well :(
		if (! empty($headers['x-operamini-phone']) && trim($headers['x-operamini-phone']) != "? # ?") {
			$_id = $this->_matchDevice('x-operamini-phone', $headers['x-operamini-phone']);
			if ($_id) {
				if ($this->debug) $this->__log('End x-operamini-phone check - x-operamini-phone found');
				return $_id;
			}
			unset($headers['x-operamini-phone']);
		}
		if ($this->debug) $this->__log('Finish Checking Opera Special headers');

		// Profile header matching
		if ($this->debug) $this->__log('Start Profile Check');
		if (! empty($headers['profile'])) {
			$_id = $this->_matchDevice('profile', $headers['profile']);			
			if ($_id) {
				if ($this->debug) $this->__log('End profile check - profile found');
				return $_id;
			}
			unset($headers['profile']);
		}
		if ($this->debug) $this->__log('End profile check - no profile found');
		if ($this->debug) $this->__log('Start x-wap-profile check');
		if (! empty($headers['x-wap-profile'])) {
			$_id = $this->_matchDevice('profile', $headers['x-wap-profile']);
			if ($_id) {
				if ($this->debug) $this->__log('End profile check - profile found');
				return $_id;
			}
			unset($headers['x-wap-profile']);
		}
		
		if ($this->debug) $this->__log('End x-wap-profile check - no profile found');
		
		// Various types of user-agent x-header matching, order is important here (for the first 3).
		// Choose any x- headers .. skip the others.
		$order = array('x-operamini-phone-ua', 'x-mobile-ua', 'user-agent');		
		foreach($headers as $key => $value) {
			if (! in_array($key, $order) && @$key[0] == 'x' && @$key[1] == '-')
				$order[] = $key;
		}

		if (! empty($headers['user-agent']) && empty($agent))
			$agent = $headers['user-agent'];
		
		foreach($order as $item) {
			if (! empty($headers[$item])) {
				if ($this->debug) $this->__log("Trying user-agent match on header $item");
				$_id = $this->_matchDevice('user-agent', $headers[$item]);
				if ($_id) {
					return $_id;
				}
				unset($headers[$item]);
			}
		}

		// Generic matching - Match of last resort.
		if ($this->debug) $this->__log('Trying Generic Match');		
		return $this->_matchDevice('user-agent', $agent, true);
	}
	
	/**
	 * Helper function for examining a HTTP header
	 *
	 * @param string $header matching ruleset to use (eg user-agent Note : Can use other headers like 'x-mobile-ua' against the user-agent rules)
	 * @param string $value The header value
	 * @param int $generic True is this is a generic match (The match of last resort)
	 * @return int a node id on success, false (0) otherwise.
	 */		
	function _matchDevice($header, $value, $generic=0) {
		// Strip unwanted chars from lower case version of value
		$value = str_replace($this->match_filter, "", strtolower($value));				
		$treetag = $header.$generic;		
		return $this->_match($header, $value, $treetag);
	}
	
	/**
	 * Hunts through HTTP headers in a specific order to find info.
	 *
	 * @param class $class
	 * @param array $valuearr
	 * @return boot true 
	 */		
	function _getExtra($class, $valuearr) {
		if ($class == 'platform') {
			$checkOrder = array_merge(array('x-operamini-phone-ua','user-agent'), array_keys($valuearr)); 
		} elseif ($class == 'browser') {
			$checkOrder = array_merge(array('agent'), array_keys($valuearr)); 			
		}

		foreach($checkOrder as $field) {
			if (! empty($valuearr[$field]) && ($field == 'user-agent' || strstr($field, 'x-') !== false)) {
				$_id = $this->_matchExtra('user-agent', $valuearr[$field], $class);
				if ($_id) {
					return $_id;
				}
			}
		}
		return false;
	}
	
	/**
	 * Helper function for examining a HTTP header
	 *
	 * @param string $header Always 'user-agent'
	 * @param string $value The HTTP header value
	 * @param string $class 'platform' or 'browser' or ????
	 * @return int a node id on success, false (0) otherwise.
	 */		
	function _matchExtra($header, $value, $class) {
		// Note : Extra manipulations less onerous than for devices.	
		$value = strtolower(str_replace(" ","", trim($value)));
		$treetag = $header.$class;
		return $this->_match($header, $value, $treetag);
	}
	
	
	/**
	 * The heart of the detection process
	 *
	 * @param string $header The type of header we're matching against - user-agent type headers use a sieve matching, all others are hash matching.
	 * @param string $newvalue The http header's value (could be a user-agent or some other x- header value)
	 * @param string $treetag The branch name eg : user-agent0, user-agent1, user-agentplatform, user-agentbrowser
	 * @return int node (which is an id) on success, false otherwise
	 */		
	function _match($header, $newvalue, $treetag) {
		$f = 0;
		$r = 0;
								
		if ($this->debug) $this->__log("Looking for $treetag $newvalue"); 

		if ($newvalue == "") {
			if ($this->debug) $this->__log("Value empty - returning false");
			return false;
		}
		
		if (strlen($newvalue) < 4) {
			if ($this->debug) $this->__log("Value ($newvalue) too small - returning false");
			return false;
		}			
		if ($this->debug) $this->__log("Loading match branch"); 		
		$branch = $this->_getBranch($treetag);							
		if (empty($branch)) {
			if ($this->debug) $this->__log("Match branch ($treetag) empty - returning false");
			return false;
		}						
		if ($this->debug) $this->__log("Match branch loaded");		
		
		if ($header == 'user-agent') {		
			// Sieve matching strategy
			foreach((array) $branch as $order => $filters) {
				foreach((array) $filters as $filter => $matches) {
					$f++;
					if (strpos($newvalue, (string) $filter) !== false) {
						foreach((array) $matches as $match => $node) {
							$r++;
							if (strpos($newvalue, (string) $match) !== false) {
								if ($this->debug) $this->__log("Match Found : $filter $match wins on $newvalue ($f/$r)");
								return $node;
							}
						}
					}
				}
			}
		} else {
			// Direct matching strategy
			if (! empty($branch[$newvalue])) {
				$node = $branch[$newvalue];
				if ($this->debug) $this->__log("Match found : $treetag $newvalue ($f/$r)");				
				return $node;
			}
		}
		
		if ($this->debug) $this->__log("No Match Found for $treetag $newvalue ($f/$r)");
		return false;
	}

	/**
	 * Find a branch for the matching process
 	 *
	 * @param string $branch The name of the branch to find
	 * @return an assoc array on success, false otherwise.
	 */		
	function _getBranch($branch) {					
		if (! empty($this->tree[$branch])) {
			if ($this->debug) $this->__log("$branch fetched from memory");
			return $this->tree[$branch];
		}		
		$tmp = $this->Cache->readStore($branch);			
		if ($tmp !== false) {
			if ($this->debug) $this->__log("$branch fetched from cache");
			$this->tree[$branch] = $tmp;			
			return $tmp;
		}			
		if ($this->debug) $this->__log("$branch not found");
		return false;
	}
}
?>