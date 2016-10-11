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
use HandsetDetection\HDStore;
use HandsetDetection\HDExtra;

class HDDevice extends HDBase {
	var $device = null;
	var $platform = null;
	var $browser = null;
	var $app = null;
	var $ratingResult = null;
	var $Store = null;
	var $Extra = null;
	var $config = null;
	
	function __construct($config=array()) {
		parent::__construct();
		$this->setConfig($config);
	}

	/**
	 * Set Config sets config vars
	 *
	 * @param array $config A config assoc array.
	 * @return true on success, false otherwise
	 **/
	function setConfig($config) {
		foreach((array) $config as $key => $value)
			$this->config[$key] = $value;

		$this->Store = HDStore::getInstance();
		$this->Store->setConfig($this->config);
		$this->Extra = new HDExtra($this->config);
	}
	/**
	 * Find all device vendors
	 *
	 * @param void
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */
	function localVendors() {
		$this->reply = array();
		$data = $this->fetchDevices();
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
	function localModels($vendor) {
		$this->reply = array();
		$data = $this->fetchDevices();
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
	function localView($vendor, $model) {
		$this->reply = array();
		$data = $this->fetchDevices();
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

		return $this->setError(301, 'Nothing found');
	}

	/**
	 * Finds all devices that have a specific property
	 *
	 * @param string $key
	 * @param string $value
	 * @return bool true on success, false otherwise. Use getReply to inspect results on success.
	 */
	function localWhatHas($key, $value) {
		$data = $this->fetchDevices();
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
	function localDetect($headers) {
		// lowercase headers on the way in.
		$headers = array_change_key_case($headers);
		$hardwareInfo = @$headers['x-local-hardwareinfo'];
		unset($headers['x-local-hardwareinfo']);

		// Is this a native detection or a HTTP detection ?
		if ($this->hasBiKeys($headers))
			return $this->v4MatchBuildInfo($headers);
		return $this->v4MatchHttpHeaders($headers, $hardwareInfo);
	}

	/**
	 * Returns the rating score for a device based on the passed values
	 *
	 * @param string $deviceId : The ID of the device to check.
	 * @param array $props Properties extracted from the device (display_x, display_y etc .. )
	 * @return array of rating information. (which includes 'score' which is an int value that is a percentage.)
	 */
	function findRating($deviceId, $props) {
		$device = $this->findById($deviceId);
		if (empty($device['Device']['hd_specs']))
			return null;

		$specs = $device['Device']['hd_specs'];

		$total = 70;
		$result = array();

		// Display Resolution - Worth 40 points if correct
		$result['resolution'] = 0;
		if (! empty($props['display_x']) && ! empty($props['display_y'])) {
			$pMaxRes = (int) max($props['display_x'], $props['display_y']);
			$pMinRes = (int) min($props['display_x'], $props['display_y']);
			$sMaxRes = (int) max($specs['display_x'], $specs['display_y']);
			$sMinRes = (int) min($specs['display_x'], $specs['display_y']);
			if ($pMaxRes == $sMaxRes && $pMinRes == $sMinRes) {
				// Check for native match first
				$result['resolution'] = 40;
			} else {
				// Check for css dimensions match.
				// css dimensions should be display_[xy] / display_pixel_ratio or others in other modes.
				// Devices can have multiple css display modes (eg. iPhone 6, iPhone 6+ Zoom mode)
				$cssScreenSizes = empty($specs['display_css_screen_sizes']) ? array() : $specs['display_css_screen_sizes'];
				foreach($cssScreenSizes as $size) {
					$dimensions = explode('x', $size);
					$tmpMaxRes = (int) max($dimensions);
					$tmpMinRes = (int) min($dimensions);
					if ($pMaxRes == $tmpMaxRes && $pMinRes == $tmpMinRes) {
						$result['resolution'] = 40;
						break;
					}
				}
			}
		}

		// Display pixel ratio - 20 points
		$result['display_pixel_ratio'] = 20;
		if (! empty($props['display_pixel_ratio'])) {
			// Note : display_pixel_ratio will be a string stored as 1.33 or 1.5 or 2, perhaps 2.0 ..
			if (@$specs['display_pixel_ratio'] == (string) round($props['display_pixel_ratio']/100, 2)) {
				$result['display_pixel_ratio'] = 40;
			}
		}

		// Benchmark - 10 points - Enough to tie break but not enough to overrule display or pixel ratio.
		$result['benchmark'] = 0;
		if (! empty($props['benchmark'])) {
			if (! empty($specs['benchmark_min']) && ! empty($specs['benchmark_max'])) {
				if ((int) $props['benchmark'] >= (int) @$specs['benchmark_min'] && (int) $props['benchmark'] <= (int) @$specs['benchmark_max']) {
					// Inside range
					$result['benchmark'] = 10;
				}
			}
		}

		$result['score'] = (int) array_sum($result);
		$result['possible'] = $total;
		$result['_id'] = $deviceId;

		// Distance from mean used in tie breaking situations if two devices have the same score.
		$result['distance'] = 100000;
		if (! empty($specs['benchmark_min']) && ! empty($specs['benchmark_max']) && ! empty($props['benchmark'])) {
			$result['distance'] = (int) abs((($specs['benchmark_min'] + $specs['benchmark_max'])/2) - $props['benchmark']);
		}
		return $result;
	}

	/**
	 * Overlays specs onto a device
	 *
	 * @param string specsField : Either 'platform', 'browser', 'language'
	 * @return void
	 **/
	function specsOverlay($specsField, &$device, $specs) {
		switch ($specsField) {
			case 'platform' : {
				if (! empty($specs['hd_specs']['general_platform']) && ! empty($specs['hd_specs']['general_platform_version'])) {
					$device['Device']['hd_specs']['general_platform'] = $specs['hd_specs']['general_platform'];
					$device['Device']['hd_specs']['general_platform_version'] = $specs['hd_specs']['general_platform_version'];
				} elseif (! empty($specs['hd_specs']['general_platform']) && $specs['hd_specs']['general_platform'] != $device['Device']['hd_specs']['general_platform']) {
					$device['Device']['hd_specs']['general_platform'] = $specs['hd_specs']['general_platform'];
					$device['Device']['hd_specs']['general_platform_version'] = '';
				}
			} break;

			case 'browser' : {
				if (! empty($specs['hd_specs']['general_browser'])) {
					$device['Device']['hd_specs']['general_browser'] = $specs['hd_specs']['general_browser'];
					$device['Device']['hd_specs']['general_browser_version'] = $specs['hd_specs']['general_browser_version'];
				}

			} break;

			case 'app' : {
				if (! empty($specs['hd_specs']['general_app'])) {
					$device['Device']['hd_specs']['general_app'] = $specs['hd_specs']['general_app'];
					$device['Device']['hd_specs']['general_app_version'] = $specs['hd_specs']['general_app_version'];
					$device['Device']['hd_specs']['general_app_category'] = $specs['hd_specs']['general_app_category'];
				}

			} break;

			case 'language' : {
				if (! empty($specs['hd_specs']['general_language'])) {
					$device['Device']['hd_specs']['general_language'] = $specs['hd_specs']['general_language'];
					$device['Device']['hd_specs']['general_language_full'] = $specs['hd_specs']['general_language_full'];
				}
			} break;
		}
	}

	/**
	 * Takes a string of onDeviceInformation and turns it into something that can be used for high accuracy checking.
	 *
	 * Strings a usually generated from cookies, but may also be supplied in headers.
	 * The format is $w;$h;$r;$b where w is the display width, h is the display height, r is the pixel ratio and b is the benchmark.
	 * display_x, display_y, display_pixel_ratio, general_benchmark
	 *
	 * @param string $hardwareInfo String of light weight device property information, separated by ':'
	 * @return array partial specs array of information we can use to improve detection accuracy
	 **/
	function infoStringToArray($hardwareInfo) {
		// Remove the header or cookie name from the string 'x-specs1a='
		if (strpos($hardwareInfo, '=') !== false) {
			$tmp = explode('=', $hardwareInfo);
			if (empty($tmp[1])) {
				return array();
			} else {
				$hardwareInfo = $tmp[1];
			}
		}
		$reply = array();
		$info = explode(':', $hardwareInfo);
		if (count($info) != 4) {
			return array();
		}
		$reply['display_x'] = (int) trim($info[0]);
		$reply['display_y'] = (int) trim($info[1]);
		$reply['display_pixel_ratio'] = (int) trim($info[2]);
		$reply['benchmark'] = (int) trim($info[3]);
		return $reply;
	}

	/**
	 * Overlays hardware info onto a device - Used in generic replys
	 *
	 * @param array $device
	 * @param hardwareInfo
	 * @return void
	 **/
	function hardwareInfoOverlay(&$device, $infoArray) {
		if (! empty($infoArray['display_x']))
			$device['Device']['hd_specs']['display_x'] = $infoArray['display_x'];
		if (! empty($infoArray['display_y']))
			$device['Device']['hd_specs']['display_y'] = $infoArray['display_y'];
		if (! empty($infoArray['display_pixel_ratio']))
			$device['Device']['hd_specs']['display_pixel_ratio'] = $infoArray['display_pixel_ratio'];
	}

	/**
	 * Device matching
	 *
	 * Plan of attack :
	 *  1) Look for opera headers first - as they're definitive
	 *  2) Try profile match - only devices which have unique profiles will match.
	 *  3) Try user-agent match
	 *  4) Try other x-headers
	 *  5) Try all remaining headers
	 *
	 * @param void
	 * @return array The matched device or null if not found
	 **/
	function matchDevice($headers) {
		$agent = "";											// Remember the agent for generic matching later.
		// Opera mini sometimes puts the vendor # model in the header - nice! ... sometimes it puts ? # ? in as well
		if (! empty($headers['x-operamini-phone']) && trim($headers['x-operamini-phone']) != "? # ?") {
			$_id = $this->getMatch('x-operamini-phone', $headers['x-operamini-phone'], DETECTIONV4_STANDARD, 'x-operamini-phone', 'device');
			if ($_id) {
				return $this->findById($_id);
			}
			$agent = $headers['x-operamini-phone'];
			unset($headers['x-operamini-phone']);
		}

		// Profile header matching
		if (! empty($headers['profile'])) {
			$_id = $this->getMatch('profile', $headers['profile'], DETECTIONV4_STANDARD, 'profile', 'device');
			if ($_id) {
				return $this->findById($_id);
			}
			unset($headers['profile']);
		}

		// Profile header matching
		if (! empty($headers['x-wap-profile'])) {
			$_id = $this->getMatch('profile', $headers['x-wap-profile'], DETECTIONV4_STANDARD, 'x-wap-profile', 'device');
			if ($_id) {
				return $this->findById($_id);
			}
			unset($headers['x-wap-profile']);
		}

		// Match nominated headers ahead of x- headers
		$order = $this->detectionConfig['device-ua-order'];
		foreach((array)$headers as $key => $value) {
			if (! in_array($key, $order) && preg_match("/^x-/i",$key))
				$order[] = $key;
		}

		foreach($order as $item) {
			if (! empty($headers[$item])) {
				//$this->log("Trying user-agent match on header $item");
				$_id = $this->getMatch('user-agent', $headers[$item], DETECTIONV4_STANDARD, $item, 'device');
				if ($_id) {
					return $this->findById($_id);
				}
			}
		}

		// Generic matching - Match of last resort
		//$this->log('Trying Generic Match');

		if (isset($headers['x-operamini-phone-ua'])) {
			$_id = $this->getMatch('user-agent', $headers['x-operamini-phone-ua'], DETECTIONV4_GENERIC, 'agent', 'device');
		}
		if (empty($_id) && isset($headers['agent'])) {
			$_id = $this->getMatch('user-agent', $headers['agent'], DETECTIONV4_GENERIC, 'agent', 'device');
		}
		if (empty($_id) && isset($headers['user-agent'])) {
			$_id = $this->getMatch('user-agent', $headers['user-agent'], DETECTIONV4_GENERIC, 'agent', 'device');
		}

		if (! empty($_id))
			return $this->findById($_id);

		return false;
	}

	/**
	 * Find a device by its id
	 *
	 * @param string $_id
	 * @return array device on success, false otherwise
	 **/
	function findById($_id) {
		return $this->Store->read("Device_{$_id}");
	}

	/**
	 * Internal helper for building a list of all devices.
	 *
	 * @param void
	 * @return array List of all devices.
	 */
	function fetchDevices() {
		$result = $this->Store->fetchDevices();
		if (! $result)
			return $this->setError(299, "Error : fetchDevices cannot read files from store.");
		return $result;
	}

	/**
	 * BuildInfo Matching
	 *
	 * Takes a set of buildInfo key/value pairs & works out what the device is
	 *
	 * @param array $buildInfo - Buildinfo key/value array
	 * @return mixed device array on success, false otherwise
	 */
	function v4MatchBuildInfo($buildInfo) {
		$this->device = null;
		$this->platform = null;
		$this->browser = null;
		$this->app = null;
		$this->detectedRuleKey = null;
		$this->ratingResult = null;
		$this->reply = null;

		// Nothing to check		
		if (empty($buildInfo))
			return false;

		$this->buildInfo = $buildInfo;
		
		// Device Detection
		$this->device = $this->v4MatchBIHelper($buildInfo, 'device');
		if (empty($this->device))
			return false;
		
		// Platform Detection
		$this->platform = $this->v4MatchBIHelper($buildInfo, 'platform');
		if (! empty($this->platform))
			$this->specsOverlay('platform', $this->device, $this->platform['Extra']);

		$this->reply['hd_specs'] = $this->device['Device']['hd_specs'];
		return $this->setError(0, "OK");
	}
	
	/**
	 * buildInfo Match helper - Does the build info match heavy lifting
	 *
	 * @param array $buildInfo A buildInfo key/value array
	 * @param string $category - 'device' or 'platform' (cant match browser or app with buildinfo)
	 * @return $device or $extra on success, false otherwise
	 **/
	function v4MatchBIHelper($buildInfo, $category='device') {
		// ***** Device Detection *****
		$confBIKeys = $this->detectionConfig["{$category}-bi-order"];
		if (empty($confBIKeys) || empty($buildInfo))
			return null;

		$hints = array();
		foreach($confBIKeys as $platform => $set) {
			foreach($set as $tuple) {
				$checking = true;
				$value = '';
				foreach($tuple as $item) {
					if ($item == 'hd-platform') {
						$value .= '|'.$platform;
					} elseif (! isset($buildInfo[$item])) {
						$checking = false;
						break;
					} else {
						$value .= '|'.$buildInfo[$item];
					}
				}
				if ($checking) {
					$value = trim($value, "| \t\n\r\0\x0B");
					$hints[] = $value;
					$subtree = ($category == 'device') ? DETECTIONV4_STANDARD : $category;
					$_id = $this->getMatch('buildinfo', $value, $subtree, 'buildinfo', $category);
					if (! empty($_id)) {
						return ($category == 'device') ? $this->findById($_id) : $this->Extra->findById($_id);
					}
				}
			}
		}
		
		// If we get this far then not found, so try generic.
		$platform = $this->hasBiKeys($buildInfo);
		if (! empty($platform)) {
			$try = array("generic|{$platform}", "{$platform}|generic");
			foreach($try as $value) {
				$subtree = ($category == 'device') ? DETECTIONV4_GENERIC : $category;
				$_id = $this->getMatch('buildinfo', $value, $subtree, 'buildinfo', $category);
				if (! empty($_id)) {
					return ($category == 'device') ? $this->findById($_id) : $this->Extra->findById($_id);
				}
			}
		}		
		return null;
	}
	
	/**
	 * Find the best device match for a given set of headers and optional device properties.
	 *
	 * In 'all' mode all conflicted devces will be returned as a list.
	 * In 'default' mode if there is a conflict then the detected device is returned only (backwards compatible with v3).
	 * 
	 * @param array $headers Set of sanitized http headers
	 * @param string $hardwareInfo Information about the hardware
	 * @return array device specs. (device.hd_specs)
	 **/
	function v4MatchHttpHeaders($headers, $hardwareInfo=null) {
		$this->device = null;
		$this->platform = null;
		$this->browser = null;
		$this->app = null;
		$this->ratingResult = null;
		$this->detectedRuleKey = array();
		$this->reply = null;
		$hwProps = null;
		
		// Nothing to check		
		if (empty($headers))
			return false;

		unset($headers['ip']);
		unset($headers['host']);

		// Sanitize headers & cleanup language
		foreach($headers as $key => $value) {
			$key = strtolower($key);

			if ($key == 'accept-language' || $key == 'content-language') {
				$key = 'language';
				$tmp = preg_split("/[,;]/", str_replace(" ","", strtolower($value)));
				if (! empty($tmp[0]))
					$value = $tmp[0];
				else
					continue;
			} elseif ($key != 'profile' && $key != 'x-wap-profile') {
				// Handle strings that have had + substituted for a space ie. badly (semi) url encoded..
				$charCounts = count_chars($value, 0);
				$stringLen = strlen($value);
				if ($charCounts[ord(' ')] == 0 && $charCounts[ord('+')] > 5 && $stringLen > 20) {
					$value = str_replace('+', ' ', $value);
				}
			}

			$this->deviceHeaders[$key] = $this->cleanStr($value);
			$this->extraHeaders[$key] = $this->Extra->extraCleanStr($value);
		}

		$this->device = $this->matchDevice($this->deviceHeaders);
		if (empty($this->device))
			return $this->setError(301, "Not Found");

		if (! empty($hardwareInfo))
			$hwProps = $this->infoStringToArray($hardwareInfo);

		// Stop on detect set - Tidy up and return
		if (! empty($this->device['Device']['hd_ops']['stop_on_detect'])) {
			// Check for hardwareInfo overlay
			if (! empty($this->device['Device']['hd_ops']['overlay_result_specs'])) {
				$this->hardwareInfoOverlay($this->device, $hwProps);
			}
			$this->reply['hd_specs'] = $this->device['Device']['hd_specs'];
			return $this->setError(0, "OK");
		}

		// Get extra info
		$this->platform = $this->Extra->matchExtra('platform', $this->extraHeaders);
		$this->browser = $this->Extra->matchExtra('browser', $this->extraHeaders);
		$this->app = $this->Extra->matchExtra('app', $this->extraHeaders);
		$this->language = $this->Extra->matchLanguage($this->extraHeaders);

		// Find out if there is any contention on the detected rule.
		$deviceList = $this->getHighAccuracyCandidates();
		if (! empty($deviceList)) {

			// Resolve contention with OS check
			$this->Extra->set($this->platform);
			$pass1List = array();
			foreach($deviceList as $_id) {
				$tryDevice = $this->findById($_id);
				if ($this->Extra->verifyPlatform($tryDevice['Device']['hd_specs'])) {
					$pass1List[] = $_id;
				}
			}

			// Contention still not resolved .. check hardware
			if (count($pass1List) >= 2 && ! empty($hwProps)) {

				// Score the list based on hardware
				$result = array();
				foreach($pass1List as $_id) {
					$tmp = $this->findRating($_id, $hwProps);
					if (! empty($tmp)) {
						$tmp['_id'] = $_id;
						$result[] = $tmp;
					}
				}

				// Sort the results
				usort($result, array($this, 'hd_sortByScore'));
				$this->ratingResult = $result;

				// Take the first one
				if ($this->ratingResult[0]['score'] != 0) {
					$device = $this->findById($result[0]['_id']);
					if (! empty($device)) {
						$this->device = $device;
					}
				}
			}
		}

		// Overlay specs
		$this->specsOverlay('platform', $this->device, $this->platform['Extra']);
		$this->specsOverlay('browser', $this->device, $this->browser['Extra']);
		$this->specsOverlay('app', $this->device, $this->app['Extra']);
		$this->specsOverlay('language', $this->device, $this->language['Extra']);

		// Overlay hardware info result if required
		if (! empty($this->device['Device']['hd_ops']['overlay_result_specs']) && ! empty($hardwareInfo))
			$this->hardwareInfoOverlay($this->device, $hwProps);

		$this->reply['hd_specs'] = $this->device['Device']['hd_specs'];
		return $this->setError(0, "OK");
	}

	/**
	 * Determines if high accuracy checks are available on the device which was just detected
	 *
	 * @param void
	 * @returns array, a list of candidate devices which have this detection rule or false otherwise.
	 */
	function getHighAccuracyCandidates() {
		$branch = $this->getBranch('hachecks');
		$ruleKey = @$this->detectedRuleKey['device'];
		if (! empty($branch[$ruleKey])) {
			return $branch[$ruleKey];
		}
		return false;
	}
	
	/**
	 * Determines if hd4Helper would provide more accurate results.
	 *
	 * @param array $headers HTTP Headers
	 * @return true if required, false otherwise
	 **/
	function isHelperUseful($headers) {
		if (empty($headers))
			return false;

		unset($headers['ip']);
		unset($headers['host']);

		$tmp = $this->localDetect($headers);
		if (empty($tmp))
			return false;

		$tmp = $this->getHighAccuracyCandidates();
		if (empty($tmp))
			return false;

		return true;
	}

	/**
	 * Custom sort function for sorting results.
	 *
	 * Includes a tie-breaker for results which score out the same
	 *
	 * @param array $result1
	 * @param array $result2
	 * @return -1 ($result1 < $result2), 0 ($result1 === $result2) , 1 ($result1 > $result2)
	 **/
	function hd_sortByScore($d1, $d2) {
		if ((@$d2['score'] - (int) @$d1['score']) != 0)
			return (int) @$d2['score'] - (int) @$d1['score'];
		return (int) @$d1['distance'] - (int) @$d2['distance'];
	}	
}
