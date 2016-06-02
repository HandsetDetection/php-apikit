<?php
/*
** Copyright (c) Richard Uren 2012 - 2015 <richard@teleport.com.au>
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
*/

namespace HandsetDetection;

use HandsetDetection\HDBase;

class HDExtra extends HDBase {
	var $data = null;
	var $Store = null;

	function __construct($config=array()) {
		parent::__construct();
		$this->setConfig($config);
	}

	/**
	 * Set Config variables
	 *
	 * @param array $config A config array
	 * @return boolean true on success, false otherwise
	 **/
	function setConfig($config) {
		$this->Store = HDStore::getInstance();
		$this->Store->setConfig($config);
		return true;
	}

	function set($data) { $this->data = $data; }

	/**
	 * Matches all HTTP header extras - platform, browser and app
	 *
	 * @param string $class Is 'platform','browser' or 'app'
	 * @return an Extra on success, false otherwise
	 **/
	function matchExtra($class, $headers) {
		unset($headers['profile']);

		$order = $this->detectionConfig["{$class}-ua-order"];
		$keys = array_keys($headers);

		foreach($keys as $key) {
			// Append any x- headers to the list of headers to check
			if (! in_array($key, $order) && preg_match("/^x-/i",$key))
				$order[] = $key;
		}

		foreach($order as $field) {
			if (! empty($headers[$field])) {
				$_id = $this->getMatch('user-agent', $headers[$field], $class, $field, $class);
				if ($_id) {
					$extra = $this->findById($_id);

					return $extra;
				}
			}
		}
		return false;
	}

	/**
	 * Find a device by its id
	 *
	 * @param string $_id
	 * @return array device on success, false otherwise
	 **/
	function findById($_id) {
		return $this->Store->read("Extra_{$_id}");
	}

	/**
	 * Can learn language from language header or agent
	 *
	 * @param array $headers A key => value array of sanitized http headers
	 * @return array Extra on success, false otherwise
	 **/
	function matchLanguage($headers) {

		$extra = array();

		// Mock up a fake Extra for merge into detection reply.
		$extra['_id'] = (int) 0;
		$extra['Extra']['hd_specs']['general_language'] = '';
		$extra['Extra']['hd_specs']['general_language_full'] = '';

		// Try directly from http header first
		if (! empty($headers['language'])) {
			$candidate = $headers['language'];
			if ($this->detectionLanguages[$candidate]) {
				$extra['Extra']['hd_specs']['general_language'] =  $candidate;
				$extra['Extra']['hd_specs']['general_language_full'] = $this->detectionLanguages[$candidate];
				return $extra;
			}
		}

		$checkOrder = array_merge($this->detectionConfig['language-ua-order'], array_keys($headers));
		$languageList = $this->detectionLanguages;
		foreach($checkOrder as $header) {
			$agent = @$headers[$header];

			if (! empty($agent)) {
				foreach((array) $languageList as $code => $full) {
					if (preg_match("/[; \(]${code}[; \)]/i", $agent)) {
						$extra['Extra']['hd_specs']['general_language'] =  $code;
						$extra['Extra']['hd_specs']['general_language_full'] =  $full;
						return $extra;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Returns false if this device definitively cannot run this platform and platform version.
	 * Returns true if its possible of if there is any doubt.
	 *
	 * Note : The detected platform must match the device platform. This is the stock OS as shipped
	 * on the device. If someone is running a variant (eg CyanogenMod) then all bets are off.
	 *
	 * @param string $specs The specs we want to check.
	 * @return boolean false if these specs can not run the detected OS, true otherwise.
	 **/
	function verifyPlatform($specs=null) {
		$platform = $this->data;

		$platformName = trim(strtolower(@$platform['Extra']['hd_specs']['general_platform']));
		$platformVersion = trim(strtolower(@$platform['Extra']['hd_specs']['general_platform_version']));
		$devicePlatformName = trim(strtolower(@$specs['general_platform']));
		$devicePlatformVersionMin = trim(strtolower(@$specs['general_platform_version']));
		$devicePlatformVersionMax = trim(strtolower(@$specs['general_platform_version_max']));

		// Its possible that we didnt pickup the platform correctly or the device has no platform info
		// Return true in this case because we cant give a concrete false (it might run this version).
		if (empty($platform) || empty($platformName) || empty($devicePlatformName))
			return true;

		// Make sure device is running stock OS / Platform
		// Return true in this case because its possible the device can run a different OS (mods / hacks etc..)
		if ($platformName != $devicePlatformName)
			return true;

		// Detected version is lower than the min version - so definetly false.
		if (! empty($platformVersion) && ! empty($devicePlatformVersionMin) && $this->comparePlatformVersions($platformVersion, $devicePlatformVersionMin) <= -1)
			return false;

		// Detected version is greater than the max version - so definetly false.
		if (! empty($platformVersion) && ! empty($devicePlatformVersionMax) && $this->comparePlatformVersions($platformVersion, $devicePlatformVersionMax) >= 1)
			return false;

		// Maybe Ok ..
		return true;
	}

	/**
	 * Breaks a version number apart into its Major, minor and point release numbers for comparison.
	 *
	 * Big Assumption : That version numbers separate their release bits by '.' !!!
	 * might need to do some analysis on the string to rip it up right.
	 *
	 * @param string $versionNumber
	 * @return array of ('major' => x, 'minor' => y and 'point' => z) on success, null otherwise
	 **/
	function breakVersionApart($versionNumber) {
		$tmp = explode('.', $versionNumber.'.0.0.0', 4);
		$reply = array();
		$reply['major'] = ! empty($tmp[0]) ? $tmp[0] : '0';
		$reply['minor'] = ! empty($tmp[1]) ? $tmp[1] : '0';
		$reply['point'] = ! empty($tmp[2]) ? $tmp[2] : '0';
		return $reply;
	}

	/**
	 * Helper for comparing two strings (numerically if possible)
	 *
	 * @param string $a Generally a number, but might be a string
	 * @param string $b Generally a number, but might be a string
	 * @return int
	 **/
	function compareSmartly($a, $b) {
		return (is_numeric($a) && is_numeric($b)) ? (int) $a - $b : strcmp($a, $b);
	}

	/**
	 * Compares two platform version numbers
	 *
	 * @param string $va Version A
	 * @param string $vb Version B
	 * @return < 0 if a < b, 0 if a == b and > 0 if a > b : Also returns 0 if data is absent from either.
	 */
	function comparePlatformVersions($va, $vb) {
		if (empty($va) || empty($vb))
			return 0;
		$versionA = $this->breakVersionApart($va);
		$versionB = $this->breakVersionApart($vb);

		$major = $this->compareSmartly($versionA['major'], $versionB['major']);
		$minor = $this->compareSmartly($versionA['minor'], $versionB['minor']);
		$point = $this->compareSmartly($versionA['point'], $versionB['point']);

		if ($major) return $major;
		if ($minor) return $minor;
		if ($point) return $point;
		return 0;
	}
}