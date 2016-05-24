<?php

error_reporting(E_ALL | E_STRICT);

echo phpversion();

class HDCacheTest extends PHPUnit_Framework_TestCase {
	var $volumeTest = 10000;
	var $testData = array(
			'roses' => 'red',
			'fish' => 'blue',
			'sugar' => 'sweet',
			'number' => 4
		);

	function testAPCBasic() {
		$cache = new HandsetDetection\HDCache();
		$now = time();
		$cache->write($now, $this->testData);
		$reply = $cache->read($now);
		$this->assertEquals($this->testData, $reply);
	}

	function testAPCVolume() {
		$cache = new HandsetDetection\HDCache();
		$keys = array();
		$now = time();
		for($i=0; $i < $this->volumeTest; $i++) {
			$key = 'test'.$now.$i;
			$cache->write($key, $this->testData);
		}
		for($i=0; $i < $this->volumeTest; $i++) {
			$key = 'test'.$now.$i;
			$reply = $cache->read($key);
			$this->assertEquals($this->testData, $reply);
		}
	}

	function testMemcacheBasic() {
		$config = array(
			'cache' => array(
				'memcache' => array(
					'options' => 0,
					'servers' => array(
						'localhost' => '11211'
					)
				)
			)
		);

		$cache = new HandsetDetection\HDCache($config);
		$now = time();
		$cache->write($now, $this->testData);
		$reply = $cache->read($now);
		$this->assertEquals($this->testData, $reply);
	}

	function testMemcacheVolume() {
		$config = array(
			'cache' => array(
				'memcache' => array(
					'options' => 0,
					'servers' => array(
						'localhost' => '11211'
					)
				)
			)
		);

		$cache = new HandsetDetection\HDCache($config);
		$keys = array();
		$now = time();
		for($i=0; $i < $this->volumeTest; $i++) {
			$key = 'test'.$now.$i;
			$cache->write($key, $this->testData);
		}
		for($i=0; $i < $this->volumeTest; $i++) {
			$key = 'test'.$now.$i;
			$reply = $cache->read($key);
			$this->assertEquals($this->testData, $reply);
		}
		$end = time();
	}

	function testMemcachedBasic() {
		$config = array(
			'cache' => array(
				'memcached' => array(
					'options' => '',
					'servers' => array(
						array('localhost', '11211'),
					)
				)
			)
		);

		$cache = new HandsetDetection\HDCache($config);
		$now = time();
		$cache->write($now, $this->testData);
		$reply = $cache->read($now);
		$this->assertEquals($this->testData, $reply);
	}

	function testMemcachedVolume() {
		$config = array(
			'cache' => array(
				'memcached' => array(
					'options' => '',
					'servers' => array(
						array('localhost', '11211'),
					)
				)
			)
		);
		$cache = new HandsetDetection\HDCache($config);
		$keys = array();
		$now = time();
		for($i=0; $i < $this->volumeTest; $i++) {
			$key = 'test'.$now.$i;
			$cache->write($key, $this->testData);
		}
		for($i=0; $i < $this->volumeTest; $i++) {
			$key = 'test'.$now.$i;
			$reply = $cache->read($key);
			$this->assertEquals($this->testData, $reply);
		}		
	}

	function testMemcachedPoolBasic() {
		$config = array(
			'cache' => array(
				'memcached' => array(
					'pool' => 'mypool',
					'options' => '',
					'servers' => array(
						array('localhost', '11211'),
					)
				)
			)
		);

		$cache = new HandsetDetection\HDCache($config);
		$now = time();
		$cache->write($now, $this->testData);
		$reply = $cache->read($now);
		$this->assertEquals($this->testData, $reply);
	}

	function testMemcachedPoolVolume() {
		$config = array(
			'cache' => array(
				'memcached' => array(
					'pool' => 'mypool',
					'options' => '',
					'servers' => array(
						array('localhost', '11211'),
					)
				)
			)
		);
		$cache = new HandsetDetection\HDCache($config);
		$keys = array();
		$now = time();
		for($i=0; $i < $this->volumeTest; $i++) {
			$key = 'test'.$now.$i;
			$cache->write($key, $this->testData);
		}
		for($i=0; $i < $this->volumeTest; $i++) {
			$key = 'test'.$now.$i;
			$reply = $cache->read($key);
			$this->assertEquals($this->testData, $reply);
		}
	}
}

