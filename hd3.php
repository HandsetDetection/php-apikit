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

// Note : Cache objects may be > 1Mb when serialized
// Consider php-igbinary to improve serialization performance in time critical situations.
if (! class_exists('HD3Cache')) {
	class HD3Cache {
		var $prefix = 'hd34';
		var $dirpath = "";
		var $dirname = "hd34cache";
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
		  * Read the key stored from the cache
		  *
		  * @param string $key
		  *
		  * @return $data
		  */
		function read($key) {
			$data = apc_fetch($this->prefix.$key);
	
			// Try file cache
			if (empty($data)) {
				$jsonstr = @file_get_contents($this->dirpath . DS . $this->dirname . DS . $key . '.json');
				if ($jsonstr === false || empty($jsonstr)) {
					return false;
				}
				$data = $this->__decode($jsonstr);
				// Write to APC as well (for next time).
				if (! empty($data))
					apc_add($this->prefix.$key, $data, $this->duration);
			}
			return $data;
		}
	
		 /**
		  * Write new key and store inside the cache
		  *
		  * @param string $key
		  *
		  * @return $data
		  */
		function write($key, $data) {
			if (empty($data))
				return false;
			
			if (! apc_store($this->prefix.$key, $data, $this->duration))
				return false;
	
			$jsonstr = $this->__encode($data);			
			if (! @file_put_contents($this->dirpath . DS . $this->dirname . DS . $key . '.json', $jsonstr))
				return false;

			return true;
		}
		
		 /**
		  * Used by the local functions. Reads the Device specs into one large array.
		  *
		  * @param null
		  *
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
		  *
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
		  *
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
	
	 /**
	  * This is the main constructor for the class HD3
	  *
	  * setup everything the config
	  *
	  * @param array $config
	  *
	  * @return void
	  */
	function HD3($config = null) {
		if (! empty($config)) {
			$this->config = array_merge($this->config, $config);
		} elseif (! file_exists($this->configFile)) {
			echo 'Error : Invalid config file and missing config array. Either pass a config array to the consutictor or create a hdconfig.php file.';
			exit(1);
		} else {
			$hdconfig = array();
			// Note : require not require_once as multiple invocations will require config file again.
			require($this->configFile);
			$this->config = array_merge($this->config, (array) $hdconfig);
		}
							
		if (empty($this->config['username']) || empty($this->config['secret'])) {
			echo 'Error : Please set your username and secret in the hdconfig.php file or in your hd3 constructor config array.<br/>';
			echo 'Error : Download a premade config file for this site from your "My Sites" section on your <a href="http://www.handsetdetection.com/users/index">My Profile</a> page';
			exit(1);
		}

		if (empty($this->config['filesdir'])) $this->config['filesdir'] = dirname(__FILE__);
		$this->match_filter = preg_split('//', $this->config['match_filter'], null, PREG_SPLIT_NO_EMPTY);
		$this->debug = $this->config['debug'];
		if ($this->debug) $this->__log('Config '.print_r($this->config, true));
		$this->Cache = null;
		$this->setup();
	}
	
	/**
	  * load the cache
	  *
	  * @param 
	  *
	  * @return true if it succeed
	  */
	function lazyLoadCache() {
		if ($this->debug) $this->__log('lazyLoading Cache Class ');
		if (empty($this->Cache)) {
			$this->Cache = new HD3Cache();
			if (! $this->Cache->setDirectory($this->config['filesdir'])) {
				if ($this->debug) $this->__log('Failed to create cache directory');
				return false;
			}
		}
		if ($this->debug) $this->__log('Cache ready');
		return true;
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
	function setReply($reply) { $this->reply = $reply; }
	function setLogger($function) { $this->config['logger'] = $function; }
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
		
	// Log function - User defined functions can be supplied in the 'logger' config variable.
	private function __log($msg) {
		//syslog(LOG_NOTICE, microtime()." ".$msg);
		if (isset($this->config['logger']) && is_callable($this->config['logger'])) {
			call_user_func($this->config['logger'], $msg);
		}
	}	
	
	/**
	  *  Encodes php assoc array to json string	  	  
	  *
	  * @param string $data
	  *
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
	  * Decode turns json string into php assoc array 	  
	  *
	  * @param string $jsonstr
	  *
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
	  * Redirect you to the mobile site 
	  *
	  * @param 
	  *
	  * @return void 
	  */	
	function redirectToMobileSite(){
		if ($this->mobile_site != '') {
			header('Location: '.$this->mobile_site);
			exit;
		} 
	} 
		
	/** Public Functions **/
	// Read http headers from the server - likely what you want to send to HD for detection.
	// You can override or add to these with setDetectVar($key, $value)
	
	/**
	  * Main setup 
	  *
	  * @param 
	  *
	  * @return void 
	  */	
	function setup() {
		$this->reply = array();
		$this->rawreply = array();
		$this->detectRequest = apache_request_headers();
		$this->detectRequest['ipaddress'] = $_SERVER['REMOTE_ADDR'];
		unset($this->detectRequest['Cookie']);
	}
	
	/**
	  * Device Vendors
	  *
	  * @param 
	  *
	  * @return true 
	  */	
	function deviceVendors() {		
		return ($this->config['use_local'] ? $this->_localDeviceVendors() : $this->_remote('device/vendors', null));
	}
	
	/**
	  * Device Models
	  *
	  * @param string $vendor
	  *
	  * @return true 
	  */	
	function deviceModels($vendor) {
		return ($this->config['use_local'] ? $this->_localDeviceModels($vendor) : $this->_remote("device/models/$vendor", null));
	}
	
	/**
	  * Device View
	  *
	  * @param string $vendor
	  * @param string $model
	  *
	  * @return true 
	  */	
	function deviceView($vendor, $model) {
		return ($this->config['use_local'] ? $this->_localDeviceView($vendor, $model) : $this->_remote("device/view/$vendor/$model", null));
	}
	
	/**
	  * Device WhatHas
	  *
	  * @param string $key
	  * @param string $value
	  *
	  * @return true 
	  */	
	function deviceWhatHas($key, $value) {
		return ($this->config['use_local'] ? $this->_localDeviceWhatHas($key, $value) : $this->_remote("device/whathas/$key/$value", null));
	}

	
	/**
	  * Device Vendors
	  *
	  * @param string $data
	  *
	  * @return true 
	  */	
	function siteAdd($data) {
		return $this->_remote("site/add", $data);
	}
	
	/**
	  * Site Edit
	  *
	  * @param string $data
	  *
	  * @return true 
	  */	
	function siteEdit($data) {
		$id = (int) (empty($data['id']) ? $this->config['site_id'] : $data['id']);
		return $this->_remote("site/edit/$id", $data);
	}
	
	/**
	  * Site View
	  *
	  * @param int $id
	  *
	  * @return true 
	  */	
	function siteView($id=null) {
		$id = (int) (empty($id) ? $this->config['site_id'] : $id);
		return $this->_remote("site/view/$id", null);
	}
	
	/**
	  * Site Delete
	  *
	  * @param int $id
	  *
	  * @return true 
	  */	
	function siteDelete($id=null) {
		$id = (int) (empty($id) ? $this->config['site_id'] : $id);
		return $this->_remote("site/delete/$id", null);
	}
	
	/**
	  * Site Detect
	  *
	  * @param string $data
	  *
	  * @return true 
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
	  * Convenience Function to download everything
	  *
	  * @param int $id
	  *
	  * @return true 
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
	  * Fetch from the tree
	  *
	  * @param int $id
	  *
	  * @return true 
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

		// Immediately write objects to disk/memory cache
		return $this->_setCacheTrees();
	}
	
	/**
	  * Set the cache tree
	  *
	  * @param 
	  *
	  * @return true 
	  */		
	function _setCacheTrees() {
		$str = @file_get_contents($this->config['filesdir'] . DS . "hd3trees.json");
		if ($str === false || empty($str)) {
			return $this->setError(299, 'Error : Unable to open trees file hd3trees.json. Is it there ? Are premissions OK ?');
		}			
		
		$data = $this->__decode($str);

		$this->lazyLoadCache();
		foreach($data['trees'] as $key => $branch) {
			if ($this->debug) $this->__log("Caching ".$key);
			if (! $this->Cache->write($key, $branch))
				return $this->setError(299, "Error : Could not write cache fragment. Key : $key, Size: ".strlen($branch));
		}
		return true;
	}
	
	/**
	  * Fetch the site specs
	  *
	  * @param int $id
	  *
	  * @return true 
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

		return $this->_setCacheSpecs();
	}

	/**
	  * Set the cache specs
	  *
	  * @param 
	  *
	  * @return true 
	  */		
	function _setCacheSpecs() {
		$str = @file_get_contents($this->config['filesdir'] . DS . "hd3specs.json");
		if ($str === false || empty($str))
			return $this->setError(299, 'Error : Unable to open specs file hd3specs.json. Is it there ? Are premissions OK ?');
		
		$data = $this->__decode($str);

		$this->lazyLoadCache();
		if (! empty($data['devices'])) {
			foreach($data['devices'] as $device) {
				$device_id = $device['Device']['_id'];
				$device_specs = $device['Device']['hd_specs'];
				if (! $this->Cache->write('Device:'.$device_id, $device_specs))	
					return $this->setError(299, 'Error : Could not write cache fragment.');
			}
		}

		if (! empty($data['extras'])) {
			foreach($data['extras'] as $extra) {
				$extra_id = $extra['Extra']['_id'];
				$extra_specs = $extra['Extra']['hd_specs'];
				if (! $this->Cache->write('Extra:'.$extra_id, $extra_specs))
					return $this->setError(299, 'Error : Could not write cache fragment.');
			}		
		}
					
		return true;
	}
	
	/**
	  * Fetch the cache specs
	  *
	  * @param int $id
	  * @param string $type
	  *
	  * @return $result 
	  */		
	function _getCacheSpecs($id, $type) {
		$this->lazyLoadCache();
		if (! $result = $this->Cache->read($type.':'.$id)) {
			if ($this->debug) $this->__log("Id $id for $type not found");
		}		
		return $result;
	}
	
	/**
	  * Fetch the archive list
	  *
	  * @param int $id
	  *
	  * @return true 
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

		if (class_exists('ZipArchive')) {
			$this->lazyLoadCache();
			$zip = new ZipArchive();
			if ($zip->open($this->config['filesdir'] . DS . "ultimate.zip") === TRUE) {
				$zip->extractTo($this->Cache->getCacheDir());
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
	
	// User Functions	
	/**
	  * User actions. Always remote.
	  *
	  * @param string $suburl
	  * @param string $data
	  *
	  * @return true 
	  */		
	function user($suburl, $data=null) {
		return $this->_remote($suburl, $data); 
	}
		 	
	/**
	  * Makes requests to the various web services of Handset Detection.
	  *
	  * Note : $suburl - the url fragment of the web service eg site/detect/${site_id}
	  * @param string $suburl
	  * @param string $data
	  * @param string $filetype
	  *
	  * @return true 
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
			$out .= "Proxy-Authorization:Basic ".base64_encode("$this->proxy_user:$this->proxy_pass")."\r\n";
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

   		$hunks = explode("\r\n\r\n",trim($reply));
   		if (!is_array($hunks) or count($hunks) < 2)
			return $this->setError(299, "Error : Reply is too short.");

   		$header = $hunks[count($hunks) - 2];
   		$body = $hunks[count($hunks) - 1];
   		$headers = explode("\n",$header);

		if (strlen($body)) return $body;
		return $this->setError(299, "Error : Reply body is empty.");
	}
	
	/**
	  * Local get specs needs to read the whole cache into array.
	  *
	  * @param 
	  *
	  * @return $result 
	  */		
	function _localGetSpecs() {
		$this->lazyLoadCache();
		$result = $this->Cache->readSpecs();		
		if (! $result)
			return $this->setError(299, "Error : _localGetSpecs cannot read files from cache.");
		return $result;
	}
	
	/**
	  * Local get device vendors.
	  *
	  * @param 
	  *
	  * @return true 
	  */		
	function _localDeviceVendors() {
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
	  * Local get device models
	  *
	  * @param string $vendor
	  *
	  * @return true 
	  */		
	function _localDeviceModels($vendor) {
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
	  * Local get device view 
	  *
	  * @param string $vendor
	  * @param string $model
	  *
	  * @return true 
	  */		
	function _localDeviceView($vendor, $model) {
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
	  * Local get device what has
	  *
	  * @param string $key
	  * @param string $value
	  *
	  * @return true 
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
	  * Local site detect
	  *
	  * @param string $headers	  
	  *
	  * @return true 
	  */			
	function _localSiteDetect($headers) {
		$this->reply = array();
		$this->rawreply = array();
		$this->setError(0, '');
		$device = null;
		$id = $this->_getDevice($headers);
		if ($id) {
			if ($this->debug) $this->__log("Looking to read $id from cache");
		
			$device = $this->_getCacheSpecs($id, 'Device');
			if ($device === false) {
				if ($this->debug) $this->__log("Cache problem : $id not found");
				return $this->setError(255, "$id not found in cache", 'Unknown');
			}
			
			if ($this->debug) $this->__log("$id fetched from cache");

			// Perform Browser & OS (platform) detection
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
												
			$this->reply['hd_specs'] = $device;
			$this->reply['class'] = (empty($device['general_type']) ? "Unknown" : $device['general_type']);
			$this->devices[$id] = $device;
			return $this->setError(0, "OK", 'Unknown');
		}
		return $this->setError(301, 'Nothing Found', 'Unknown');
	}
	
	/**
	  * Local get the device
	  *
	  * @param string $headers	  
	  *
	  * @return true 
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
	  * match device
	  *
	  * @param string $header
	  * @param string $value
	  * @param int $generic
	  *
	  * @return true 
	  */		
	function _matchDevice($header, $value, $generic=0) {
		// Strip unwanted chars from lower case version of value
		$value = str_replace($this->match_filter, "", strtolower($value));
		$treetag = $header.$generic;
		
		return $this->_match($header, $value, $treetag);
	}
	
	/**
	  * Tries headers in diffferent orders depending on the extra $class.
	  *
	  * @param class $class
	  * @param array $valuearr
	  *
	  * @return true 
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
	  * match extra
	  *
	  * @param string $header
	  * @param string $value
	  * @param Class $class
	  *
	  * @return true 
	  */		
	function _matchExtra($header, $value, $class) {
		// Note : Extra manipulations less onerous than for devices.	
		$value = strtolower(str_replace(" ","", trim($value)));
		$treetag = $header.$class;
		
		return $this->_match($header, $value, $treetag);
	}
	
	
	/**
	  * match
	  *
	  * @param string $header
	  * @param string $newvalue
	  * @param string $treetag
	  *
	  * @return true 
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
	  * get branch
	  *
	  * @param string $branch
	  *
	  * @return true 
	  */		
	function _getBranch($branch) {
		if (! empty($this->tree[$branch])) {
			if ($this->debug) $this->__log("$branch fetched from memory");
			return $this->tree[$branch];
		}
		
		$this->lazyLoadCache();
		$tmp = $this->Cache->read($branch);
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