<?php

error_reporting(E_ALL | E_STRICT);

class HDStoreTest extends PHPUnit_Framework_TestCase {

	var $testData = array(
			'roses' => 'red',
			'fish' => 'blue',
			'sugar' => 'sweet',
			'number' => 4
		);

	// Writes to store & cache
	function testReadWrite() {
		$key = 'storekey'.time();
		$store = HandsetDetection\HDStore::getInstance();
		$store->write($key, $this->testData);

		$data = $store->read($key);
		$this->assertEquals($this->testData, $data);

		$cache = new HandsetDetection\HDCache();
		$data = $cache->read($key);
		$this->assertEquals($this->testData, $data);

		$exists = is_file($store->directory . DIRECTORY_SEPARATOR . "$key.json");
		$this->assertTrue($exists);
	}

	// Writes to store & not cache
	function testStoreFetch() {
		$key = 'storekey2'.time();
		$store = HandsetDetection\HDStore::getInstance();
		$store->store($key, $this->testData);

		$cache = new HandsetDetection\HDCache();
		$data = $cache->read($key);
		$this->assertEquals(false, $data);

		$data = $store->fetch($key);
		$this->assertEquals($this->testData, $data);

		$exists = is_file($store->directory . DIRECTORY_SEPARATOR . "$key.json");
		$this->assertTrue($exists);
	}

	// Test purge
	function testPurge() {
		$store = HandsetDetection\HDStore::getInstance();
		$files = glob($store->directory . DIRECTORY_SEPARATOR . '*.json');
		$this->assertNotEmpty($files);
		
		$store->purge();

		$files = glob($store->directory . DIRECTORY_SEPARATOR . '*.json');
		$this->assertEmpty($files);
	}

	// Reads all devices from Disk (Keys need to be in Device*json format)
	function testFetchDevices() {
		$key = 'Device'.time();
		$store = HandsetDetection\HDStore::getInstance();
		$store->store($key, $this->testData);

		$devices = $store->fetchDevices();
		$this->assertEquals($devices['devices'][0], $this->testData);
		$store->purge();
	}

	// Moves a file from disk into store (vanishes from previous location).
	function testMoveIn() {
		$store = HandsetDetection\HDStore::getInstance();
		$jsonstr = json_encode($this->testData);
		file_put_contents('TmpDevice.json', $jsonstr);

		$store->moveIn('TmpDevice.json',  'TmpDevice.json');

		$this->assertFileNotExists('TmpDevice.json');
		$this->assertFileExists($store->directory . DIRECTORY_SEPARATOR . 'TmpDevice.json');
	}

	// Test singleton'ship
	function testSingleton() {
		$store = HandsetDetection\HDStore::getInstance();
		$store2 = HandsetDetection\HDStore::getInstance();

		$store->setPath('/tmp', true);
		$this->assertEquals($store2->directory, '/tmp/hd40store');
	}
}


