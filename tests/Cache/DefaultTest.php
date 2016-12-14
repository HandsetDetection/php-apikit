<?php

class DefaultTest extends PHPUnit_Framework_TestCase {

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
		$cache = new HandsetDetection\HDCache();
		$now = time();

		// Skip tests if no cache installed
		if ($cache->getName() == 'none') {
            $this->markTestSkipped('No cache configured/installed');
			return;
		}
		
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
		$cache = new HandsetDetection\HDCache();
		$now = time();

		// Skip tests if no cache installed
		if ($cache->getName() == 'none') {
            $this->markTestSkipped('No cache configured/installed');
			return;
		}

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
		$cache = new HandsetDetection\HDCache();
		$cacheNames = array('apc', 'apcu', 'memcache', 'memcached', 'file', 'none');
		$this->assertContains($cache->getName(), $cacheNames);
	}
}