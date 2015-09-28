<?php

error_reporting(E_ALL | E_STRICT);
require_once('HDCache.php');

echo phpversion();

class HDCacheTest extends PHPUnit_Framework_TestCase {
	var $testData = array(
			'roses' => 'red',
			'fish' => 'blue',
			'sugar' => 'sweet',
			'number' => 4
		);

	function testA() {
		$cache = new HandsetDetection\HDCache();
		$now = time();
		$cache->write($now, $this->testData);
		$reply = $cache->read($now);
		$this->assertEquals($this->testData, $reply);
	}

	function testVolume() {
		$cache = new HandsetDetection\HDCache();
		$keys = array();
		$now = time();
		for($i=0; $i < 10000; $i++) {
			$key = 'test'.$now.$i;
			$cache->write($key, $this->testData);
		}
		for($i=0; $i < 10000; $i++) {
			$key = 'test'.$now.$i;
			$reply = $cache->read($key);
			$this->assertEquals($this->testData, $reply);
		}
	}
}

