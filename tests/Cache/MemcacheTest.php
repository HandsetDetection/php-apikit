<?php

// phpunit 6.0 backward compatibility with phpunit 4.0
if (!class_exists('\PHPUnit\Framework\TestCase') && class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class MemcacheTest extends \PHPUnit\Framework\TestCase {
	var $volumeTest = 10000;
	var $testData = array(
			'roses' => 'red',
			'fish' => 'blue',
			'sugar' => 'sweet',
			'number' => 4
		);

	function setup() {
        if (! extension_loaded('memcache') || ! class_exists('\Memcache'))
            $this->markTestSkipped('Memcache extension is not available.');
	}

	function testBasic() {
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
				'memcache' => array(
					'options' => 0,
					'servers' => array(
						'localhost' => '11211'
					)
				)
			)
		);

		$cache = new HandsetDetection\HDCache($config);
		$this->assertEquals('memcache', $cache->getName());
	}
}
