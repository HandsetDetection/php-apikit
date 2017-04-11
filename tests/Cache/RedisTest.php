<?php

// phpunit 6.0 backward compatibility with phpunit 4.0
if (!class_exists('\PHPUnit\Framework\TestCase') && class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class RedisTest extends \PHPUnit\Framework\TestCase {
	var $volumeTest = 10000;
	var $testData = array(
			'roses' => 'red',
			'fish' => 'blue',
			'sugar' => 'sweet',
			'number' => 4
		);

	function setup() {
		if (! class_exists('\Predis\Client')) {
			$this->markTestSkipped('Predis class not available. Please install via composer.');
		}
	}

	function testBasic() {
		$config = array(
			'cache' => array(
				'redis' => array(
					'scheme' => 'tcp',
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
				'redis' => array(
					'scheme' => 'tcp',
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
				'redis' => array(
					'scheme' => 'tcp',
					'host'   => '127.0.0.1',
					'port'   => 6379
				)
			)
		);

		$cache = new HandsetDetection\HDCache($config);
		$this->assertEquals('redis', $cache->getName());
	}
}
