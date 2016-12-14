<?php

class NoneTest extends PHPUnit_Framework_TestCase {

	var $volumeTest = 10000;
	var $testData = array(
			'roses' => 'red',
			'fish' => 'blue',
			'sugar' => 'sweet',
			'number' => 4
		);

    public function setUp() {
    }

	function testBasic() {
		$config = array(
			'cache' => array(
				'none' => true
			)
		);

		$cache = new HandsetDetection\HDCache($config);
		$now = time();

		// Test Write & Read
		$cache->write($now, $this->testData);
		$reply = $cache->read($now);
		$this->assertFalse($reply);

		// Test Flush
		$reply = $cache->purge();
		$this->assertFalse($reply);
		$reply = $cache->read($now);
		$this->assertFalse($reply);
	}

	function testVolume() {
		$config = array(
			'cache' => array(
				'none' => true
			)
		);
		
		$cache = new HandsetDetection\HDCache($config);
		$now = time();

		for($i=0; $i < $this->volumeTest; $i++) {
			$key = 'test'.$now.$i;

			// Write
			$reply = $cache->write($key, $this->testData);
			$this->assertFalse($reply);

			// Read
			$reply = $cache->read($key);
			$this->assertFalse($reply);

			// Delete
			$reply = $cache->delete($key);
			$this->assertFalse($reply);

			// Read
			$reply = $cache->read($key);
			$this->assertFalse($reply);
		}
		$end = time();
		$cache->purge();
	}

	function testGetName() {
		$config = array(
			'cache' => array(
				'none' => true
			)
		);

		$cache = new HandsetDetection\HDCache($config);
		$this->assertEquals('none', $cache->getName());
	}
}