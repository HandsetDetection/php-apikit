<?php

class MemcachedTest extends PHPUnit_Framework_TestCase {

	var $volumeTest = 10000;
	var $testData = array(
			'roses' => 'red',
			'fish' => 'blue',
			'sugar' => 'sweet',
			'number' => 4
		);

	function setup() {
        if (! extension_loaded('memcached') || ! class_exists('\Memcached'))
            $this->markTestSkipped('Memcached extension is not available.');
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
}