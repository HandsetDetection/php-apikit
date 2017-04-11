<?php

class PhpRedisTest extends PHPUnit_Framework_TestCase {
	var $volumeTest = 10000;
	var $testData = array(
			'roses' => 'red',
			'fish' => 'blue',
			'sugar' => 'sweet',
			'number' => 4
		);

	function setup() {
		if (! extension_loaded('redis')) {
			$this->markTestSkipped('redis extension not loaded or available. Please install extension.');
		}
	}

	function testConnectBasic() {
		$config = array(
			'cache' => array(
				'phpredis' => array(
					'connect_method' => 'connect',
					'host'   => '127.0.0.1',
					'port'   => 6379
				)
			)
		);

		$cache = new HandsetDetection\HDCache($config);
		$now = time();

		// Test Write & Read
		$cache->write($now, $this->testData);
		$reply = $cache->read($now);
		$this->assertEquals($this->testData, $reply);

		// Test Flush
		$reply = $cache->purge();
		$this->assertTrue($reply);
		$reply = $cache->read($now);
		$this->assertNull($reply);
	}

	function testPConnectBasic() {
		$config = array(
			'cache' => array(
				'phpredis' => array(
					'connect_method' => 'pconnect',
					'host'   => '127.0.0.1',
					'port'   => 6379
				)
			)
		);

		$cache = new HandsetDetection\HDCache($config);
		$now = time();

		// Test Write & Read
		$cache->write($now, $this->testData);
		$reply = $cache->read($now);
		$this->assertEquals($this->testData, $reply);

		// Test Flush
		$reply = $cache->purge();
		$this->assertTrue($reply);
		$reply = $cache->read($now);
		$this->assertNull($reply);
	}

	function testVolume() {
		$config = array(
			'cache' => array(
				'phpredis' => array(
					'connect_method' => 'connect',
					'host'   => '127.0.0.1',
					'port'   => 6379
				)
			)
		);

		$cache = new HandsetDetection\HDCache($config);
		$now = time();

		for($i=0; $i < $this->volumeTest; $i++) {
			$key = 'test'.$now.$i;

			// Write
			$reply = $cache->write($key, $this->testData);
			$this->assertTrue($reply);

			// Read
			$reply = $cache->read($key);
			$this->assertEquals($this->testData, $reply);

			// Delete
			$reply = $cache->delete($key);
			$this->assertTrue($reply);

			// Read
			$reply = $cache->read($key);
			$this->assertNull($reply);
		}
		$end = time();
		$cache->purge();
	}

	function testPConnectVolume() {
		$config = array(
			'cache' => array(
				'phpredis' => array(
					'connect_method' => 'pconnect',
					'host'   => '127.0.0.1',
					'port'   => 6379
				)
			)
		);

		$cache = new HandsetDetection\HDCache($config);
		$now = time();

		for($i=0; $i < $this->volumeTest; $i++) {
			$key = 'test'.$now.$i;

			// Write
			$reply = $cache->write($key, $this->testData);
			$this->assertTrue($reply);

			// Read
			$reply = $cache->read($key);
			$this->assertEquals($this->testData, $reply);

			// Delete
			$reply = $cache->delete($key);
			$this->assertTrue($reply);

			// Read
			$reply = $cache->read($key);
			$this->assertNull($reply);
		}
		$end = time();
		$cache->purge();
	}

	function testGetName() {
		$config = array(
			'cache' => array(
				'phpredis' => array(
					'connect_method' => 'pconnect',
					'host'   => '127.0.0.1',
					'port'   => 6379
				)
			)
		);
		$cache = new HandsetDetection\HDCache($config);
		$this->assertEquals('phpredis', $cache->getName());
	}
}