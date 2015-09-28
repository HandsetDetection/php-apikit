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

define('DETECTIONV4_STANDARD', 0);
define('DETECTIONV4_GENERIC', 1);

class HDBase {

	var $config = array();
	var $apiBase = '/apiv4/';
	var $detectedRuleKey = array();
	var $deviceUAFilter = " _\\#-,./:\"'";
	var $extraUAFilter = " ";
	var $apikit = 'PHP 4.0.0';
	var $loggerHost = 'logger.handsetdetection.com';
	var $loggerPort = 80;
	
	var $detectionConfig = array(
		'device-ua-order' => array('x-operamini-phone-ua', 'x-mobile-ua', 'device-stock-ua', 'user-agent', 'agent'),
		'platform-ua-order' => array('x-operamini-phone-ua', 'x-mobile-ua', 'device-stock-ua', 'user-agent', 'agent'),
		'browser-ua-order' => array('user-agent', 'agent', 'device-stock-ua'),
		'app-ua-order' => array('user-agent', 'agent', 'device-stock-ua'),
		'language-ua-order' => array('user-agent', 'agent', 'device-stock-ua'),
		'device-bi-order' => array(
								'android' => array(
									array('ro.product.brand','ro.product.model'),
									array('ro.product.manufacturer','ro.product.model'),
									array('ro-product-brand','ro-product-model'),
									array('ro-product-manufacturer','ro-product-model'),
								),
								'ios' => array(
									array('utsname.brand','utsname.machine')
								),
								'windows phone' => array(
									array('devicemanufacturer','devicename')
								)
							),
		'platform-bi-order' => array(
								'android' => array(
									array('ro.build.id', 'ro.build.version.release'),
									array('ro-build-id', 'ro-build-version-release'),
								),
								'ios' => array(
									array('uidevice.systemName','uidevice.systemversion')
								),
								'windows phone' => array(
									array('osname','osversion')
								)
							),
		'browser-bi-order' => array(),
		'app-bi-order' => array()
	);

	var $detectionLanguages = array(
		'af' => 'Afrikaans',
		'sq' => 'Albanian',
		'ar-dz' => 'Arabic (Algeria)',
		'ar-bh' => 'Arabic (Bahrain)',
		'ar-eg' => 'Arabic (Egypt)',
		'ar-iq' => 'Arabic (Iraq)',
		'ar-jo' => 'Arabic (Jordan)',
		'ar-kw' => 'Arabic (Kuwait)',
		'ar-lb' => 'Arabic (Lebanon)',
		'ar-ly' => 'Arabic (libya)',
		'ar-ma' => 'Arabic (Morocco)',
		'ar-om' => 'Arabic (Oman)',
		'ar-qa' => 'Arabic (Qatar)',
		'ar-sa' => 'Arabic (Saudi Arabia)',
		'ar-sy' => 'Arabic (Syria)',
		'ar-tn' => 'Arabic (Tunisia)',
		'ar-ae' => 'Arabic (U.A.E.)',
		'ar-ye' => 'Arabic (Yemen)',
		'ar' => 'Arabic',
		'hy' => 'Armenian',
		'as' => 'Assamese',
		'az' => 'Azeri',
		'eu' => 'Basque',
		'be' => 'Belarusian',
		'bn' => 'Bengali',
		'bg' => 'Bulgarian',
		'ca' => 'Catalan',
		'zh-cn' => 'Chinese (China)',
		'zh-hk' => 'Chinese (Hong Kong SAR)',
		'zh-mo' => 'Chinese (Macau SAR)',
		'zh-sg' => 'Chinese (Singapore)',
		'zh-tw' => 'Chinese (Taiwan)',
		'zh' => 'Chinese',
		'hr' => 'Croatian',
		'cs' => 'Czech',
		'da' => 'Danish',
		'da-dk' => 'Danish',
		'div' => 'Divehi',
		'nl-be' => 'Dutch (Belgium)',
		'nl' => 'Dutch (Netherlands)',
		'en-au' => 'English (Australia)',
		'en-bz' => 'English (Belize)',
		'en-ca' => 'English (Canada)',
		'en-ie' => 'English (Ireland)',
		'en-jm' => 'English (Jamaica)',
		'en-nz' => 'English (New Zealand)',
		'en-ph' => 'English (Philippines)',
		'en-za' => 'English (South Africa)',
		'en-tt' => 'English (Trinidad)',
		'en-gb' => 'English (United Kingdom)',
		'en-us' => 'English (United States)',
		'en-zw' => 'English (Zimbabwe)',
		'en' => 'English',
		'us' => 'English (United States)',
		'et' => 'Estonian',
		'fo' => 'Faeroese',
		'fa' => 'Farsi',
		'fi' => 'Finnish',
		'fr-be' => 'French (Belgium)',
		'fr-ca' => 'French (Canada)',
		'fr-lu' => 'French (Luxembourg)',
		'fr-mc' => 'French (Monaco)',
		'fr-ch' => 'French (Switzerland)',
		'fr' => 'French (France)',
		'mk' => 'FYRO Macedonian',
		'gd' => 'Gaelic',
		'ka' => 'Georgian',
		'de-at' => 'German (Austria)',
		'de-li' => 'German (Liechtenstein)',
		'de-lu' => 'German (Luxembourg)',
		'de-ch' => 'German (Switzerland)',
		'de-de' => 'German (Germany)',
		'de' => 'German (Germany)',
		'el' => 'Greek',
		'gu' => 'Gujarati',
		'he' => 'Hebrew',
		'hi' => 'Hindi',
		'hu' => 'Hungarian',
		'is' => 'Icelandic',
		'id' => 'Indonesian',
		'it-ch' => 'Italian (Switzerland)',
		'it' => 'Italian (Italy)',
		'it-it' => 'Italian (Italy)',
		'ja' => 'Japanese',
		'kn' => 'Kannada',
		'kk' => 'Kazakh',
		'kok' => 'Konkani',
		'ko' => 'Korean',
		'kz' => 'Kyrgyz',
		'lv' => 'Latvian',
		'lt' => 'Lithuanian',
		'ms' => 'Malay',
		'ml' => 'Malayalam',
		'mt' => 'Maltese',
		'mr' => 'Marathi',
		'mn' => 'Mongolian (Cyrillic)',
		'ne' => 'Nepali (India)',
		'nb-no' => 'Norwegian (Bokmal)',
		'nn-no' => 'Norwegian (Nynorsk)',
		'no' => 'Norwegian (Bokmal)',
		'or' => 'Oriya',
		'pl' => 'Polish',
		'pt-br' => 'Portuguese (Brazil)',
		'pt' => 'Portuguese (Portugal)',
		'pa' => 'Punjabi',
		'rm' => 'Rhaeto-Romanic',
		'ro-md' => 'Romanian (Moldova)',
		'ro' => 'Romanian',
		'ru-md' => 'Russian (Moldova)',
		'ru' => 'Russian',
		'sa' => 'Sanskrit',
		'sr' => 'Serbian',
		'sk' => 'Slovak',
		'ls' => 'Slovenian',
		'sb' => 'Sorbian',
		'es-ar' => 'Spanish (Argentina)',
		'es-bo' => 'Spanish (Bolivia)',
		'es-cl' => 'Spanish (Chile)',
		'es-co' => 'Spanish (Colombia)',
		'es-cr' => 'Spanish (Costa Rica)',
		'es-do' => 'Spanish (Dominican Republic)',
		'es-ec' => 'Spanish (Ecuador)',
		'es-sv' => 'Spanish (El Salvador)',
		'es-gt' => 'Spanish (Guatemala)',
		'es-hn' => 'Spanish (Honduras)',
		'es-mx' => 'Spanish (Mexico)',
		'es-ni' => 'Spanish (Nicaragua)',
		'es-pa' => 'Spanish (Panama)',
		'es-py' => 'Spanish (Paraguay)',
		'es-pe' => 'Spanish (Peru)',
		'es-pr' => 'Spanish (Puerto Rico)',
		'es-us' => 'Spanish (United States)',
		'es-uy' => 'Spanish (Uruguay)',
		'es-ve' => 'Spanish (Venezuela)',
		'es' => 'Spanish (Traditional Sort)',
		'es-es' => 'Spanish (Traditional Sort)',
		'sx' => 'Sutu',
		'sw' => 'Swahili',
		'sv-fi' => 'Swedish (Finland)',
		'sv' => 'Swedish',
		'syr' => 'Syriac',
		'ta' => 'Tamil',
		'tt' => 'Tatar',
		'te' => 'Telugu',
		'th' => 'Thai',
		'ts' => 'Tsonga',
		'tn' => 'Tswana',
		'tr' => 'Turkish',
		'uk' => 'Ukrainian',
		'ur' => 'Urdu',
		'uz' => 'Uzbek',
		'vi' => 'Vietnamese',
		'xh' => 'Xhosa',
		'yi' => 'Yiddish',
		'zu' => 'Zulu'
	);
	
	function __construct() {
		$this->deviceUAFilterList = preg_split('//', $this->deviceUAFilter, null, PREG_SPLIT_NO_EMPTY);
		$this->extraUAFilterList = preg_split('//', $this->extraUAFilter, null, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Get reply status
	 *
	 * @param void
	 * @return int error status, 0 is Ok, anything else is probably not Ok
	 **/
	function getStatus() {
		return $this->reply['status'];
	}

	/**
	 * Get reply message
	 *
	 * @param void
	 * @return string A message
	 **/
	function getMessage() {
		return $this->reply['message'];
	}

	/**
	 * Get reply payload in array assoc format
	 *
	 * @param void
	 * @return array
	 **/
	function getReply() {
		return $this->reply;
	}

	/**
	 * Set a reply payload
	 *
	 * @param array $reply
	 * @return void
	 **/
	function setReply($reply) {
		$this->reply = $reply;
	}

	/**
	 * Error handling helper. Sets a message and an error code.
	 *
	 * @param int $status
	 * @param string $msg
	 * @return true if no error, or false otherwise.
	 **/
	function setError($status, $msg) {
		$this->error = $msg;
		$this->reply['status'] = $status;
		$this->reply['message'] = $msg;
		return ($status > 0) ? false : true;
	}
	
	/**
	 * String cleanse for extras matching.
	 *
	 * @param string $str
	 * @return string Cleansed string
	 **/
	function extraCleanStr($str) {
		$str = str_replace($this->extraUAFilterList,'', strtolower($str));
		$str = preg_replace('/[^(\x20-\x7F)]*/','', $str);
		return trim($str);
	}

	/**
	 * Standard string cleanse for device matching
	 *
	 * @param string $str
	 * @return string cleansed string
	 **/
	function cleanStr($str) {
		$str = str_replace($this->deviceUAFilterList, '', strtolower($str));
		$str = preg_replace('/[^(\x20-\x7F)]*/','', $str);
		return trim($str);
	}
	
	// Log function - User defined functions can be supplied in the 'logger' config variable.
	function log($msg) {
		syslog(LOG_NOTICE, microtime()." ".$msg);
		if (isset($this->config['logger']) && is_callable($this->config['logger'])) {
			call_user_func($this->config['logger'], $msg);
		}
	}
	
	/**
	 * Makes requests to the various web services of Handset Detection.
	 *
	 * Note : $suburl - the url fragment of the web service eg site/detect/${site_id}
	 *
	 * @param string $suburl
	 * @param string $data
	 * @param string $filetype
	 * @param boolean $authRequired - Is authentication required ?
	 * @return bool true on success, false otherwise
	 */
	function remote($suburl, $data, $filetype='json', $authRequired=true) {
		$this->reply = array();
		$this->rawreply = array();
		$this->setError(0, '');

		if (empty($data))
			$data = array();

		$url = $this->apiBase.$suburl;
		$attempts = $this->config['retries'] + 1;
		$trys = 0;

		$requestdata = json_encode($data);

		$success = false;
		while($trys++ < $attempts && $success === false) {
			$this->rawreply = $this->post($this->config['api_server'], $url, $requestdata, $authRequired);
			if ($this->rawreply === false) {
				$this->setError(299, "Error : Connection to $url Failed");
			} else {
				if ($filetype == 'json') {
					$this->reply = json_decode($this->rawreply, true);

					if (empty($this->reply)) {
						$this->setError(299, "Error : Empty Reply.");
					} elseif (! isset($this->reply['status'])) {
						$this->setError(299, "Error : No status set in reply");
					} elseif ((int) $this->reply['status'] != 0) {
						$this->setError(@$this->reply['status'], @$this->reply['message']);
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

	/**
	 * Post data to remote server
	 *
	 * Modified version of PHP Post from From http://www.enyem.com/wiki/index.php/Send_POST_request_(PHP)
	 * Thanks dude !
	 *
	 * @param string $server Server name
	 * @param string $url URL name
	 * @param string $jsondata Data in json format
	 * @param boolean $authRequired Is suthentication reguired ?
	 * @return false on failue (sets error), or string on success.
	 **/
	private function post($server, $url, $jsondata, $authRequired=true) {
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
		$errno = '';
		$errstr = '';
		$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
		if (! $fp)
			return $this->setError(299, "Error : Cannot connect to $host, port $port timeout $timeout : ($errno) $errstr");

		//* * connection successful, write headers */
		// Use HTTP/1.0 (to disable content chunking on large replies).
		$out = "POST $url HTTP/1.0\r\n";
		$out .= "Host: $server\r\n";
		if ($this->config['use_proxy'] && ! empty($this->config['proxy_user']) && ! empty($this->config['proxy_pass'])) {
			$out .= "Proxy-Authorization:Basic ".base64_encode("{$this->config['proxy_user']}:{$this->config['proxy_pass']}")."\r\n";
		}
		$out .= "Content-Type: application/json\r\n";
		// Pre-computed auth credentials, saves waiting for the auth challenge hence makes things round trip time 50% faster.
		if ($authRequired) {
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
		}
		$out .= "Content-length: " . strlen($jsondata) . "\r\n\r\n";
		$out .= "$jsondata\r\n\r\n";

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

		if (strlen($body))
			return $body;
		return $this->setError(299, "Error : Reply body is empty.");
	}

	/**
	 * Helper for determining if a header has BiKeys
	 *
	 * @param array $header
	 * @return platform name on success, false otherwise
	 **/
	function hasBiKeys($headers) {
		$biKeys = $this->detectionConfig['device-bi-order'];

		$dataKeys = array_keys($headers);
		array_change_key_case($dataKeys);

		// Fast check
		if (isset($headers['agent']))
			return false;
		if (isset($headers['user-agent']))
			return false;

		foreach($biKeys as $platform => $set) {
			foreach($set as $tuple) {
				$count = 0;
				$total = count($tuple);
				foreach($tuple as $item) {
					if (in_array($item, $dataKeys, true))
						$count++;
					if ($count == $total)
						return $platform;
				}
			}
		}
		return false;
	}

	/**
	 * The heart of the detection process
	 *
	 * @param string $header The type of header we're matching against - user-agent type headers use a sieve matching, all others are hash matching.
	 * @param string $newvalue The http header's value (could be a user-agent or some other x- header value)
	 * @param string $treetag The branch name eg : user-agent0, user-agent1, user-agentplatform, user-agentbrowser
	 * @return int node (which is an id) on success, false otherwise
	 */
	function getMatch($header, $value, $subtree="0", $actualHeader='', $class='device') {
		$f = 0;
		$r = 0;
		if ($class == 'device') {
			$value = $this->cleanStr($value);
			$treetag = $header.$subtree;
		} else {
			$value = $this->extraCleanStr($value);
			$treetag = $header.$subtree;
		}

		if ($value == "") {
			return false;
		}

		if (strlen($value) < 4) {
			return false;
		}

		$branch = $this->getBranch($treetag);
		if (empty($branch)) {
			return false;
		}

		if ($header == 'user-agent') {
			// Sieve matching strategy
			foreach((array) $branch as $order => $filters) {
				foreach((array) $filters as $filter => $matches) {
					++$f;
					if (strpos($value, (string) $filter) !== false) {
						foreach((array) $matches as $match => $node) {
							++$r;
							if (strpos($value, (string) $match) !== false) {
								$this->detectedRuleKey[$class] = $this->cleanStr(@$header).':'.$this->cleanStr(@$filter).':'.$this->cleanStr(@$match);
								return $node;
							}
						}
					}
				}
			}
		} else {
			// Hash matching strategy
			if (! empty($branch[$value])) {
				$node = $branch[$value];
				return $node;
			}
		}
		return false;
	}

	/**
	 * Find a branch for the matching process
 	 *
	 * @param string $branch The name of the branch to find
	 * @return an assoc array on success, false otherwise.
	 */
	function getBranch($branch) {
		if (! empty($this->tree[$branch])) {
			return $this->tree[$branch];
		}
		$tmp = $this->Store->read($branch);
		if ($tmp !== false) {
			$this->tree[$branch] = $tmp;
			return $tmp;
		}
		return false;
	}

	/**
	 * UDP Syslog via https://gist.github.com/troy/2220679 - Thanks Troy
	 *
	 * Send a message via UDP, used if log_unknown is set in config && running in Ultimate (local) mode.
	 *
	 * @param array $headers
	 * @return void
	 **/
	function send_remote_syslog($headers) {
		$headers['version'] = phpversion();
		$headers['apikit'] = $this->apikit;
		$sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		$message = json_encode($headers);
		@socket_sendto($sock, '<22> '.$message, strlen($message), 0, $this->loggerHost, $this->loggerPort);
		@socket_close($sock);
	}
}