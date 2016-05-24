<?php

class APCTest extends PHPUnit_Framework_TestCase {

	var $volumeTest = 10000;
	var $testData = array(
			'roses' => 'red',
			'fish' => 'blue',
			'sugar' => 'sweet',
			'number' => 4
		);

    public function setUp() {
        if (! extension_loaded('apc'))
            $this->markTestSkipped('APC(u) extension not available.');

        if (! ini_get('apc.enable_cli'))
            $this->markTestSkipped('Enable apc.enable_cli');
    }

	function testAPCBasic() {
		$cache = new HandsetDetection\HDCache();
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

	function testAPCVolume() {
		$cache = new HandsetDetection\HDCache();
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