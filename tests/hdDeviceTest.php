<?php

error_reporting(E_ALL | E_STRICT);

// The device class performs the same functions as our Cloud API, but locally.
// It is only used when use_local is set to true in the config file.
// To perform tests we need to setup the environment by populating the the Storage layer with device specs.
// So install the latest community edition so there is something to work with.

class HDDeviceTest extends PHPUnit_Framework_TestCase {

	// Setup community edition for tests. Takes 60s or so to download and install.
	static function setUpBeforeClass() {
		$dir = sys_get_temp_dir();
		$file = $dir . DIRECTORY_SEPARATOR . "communityTest.zip";
		$hd4 = new HandsetDetection\HD4();

		$store = HandsetDetection\HDStore::getInstance();
		$store->setPath($dir, true);

		$hd4->remote("community/fetcharchive", '', 'zip', false);
		$status = file_put_contents($dir . DIRECTORY_SEPARATOR . "communityTest.zip", $hd4->getRawReply());

		$zip = new \ZipArchive();
		if ($zip->open($file) === false)
			return false;

		for ($i = 0; $i < $zip->numFiles; $i++) {
			$filename = $zip->getNameIndex($i);
			$zip->extractTo($dir, $filename);
			$store->moveIn($dir . DIRECTORY_SEPARATOR . $filename, $filename);
		}
		$zip->close();
		return true;
	}

	// Remove community edition
	static function tearDownAfterClass() {
		$store = HandsetDetection\HDStore::getInstance();
		$store->purge();
	}

	function testIsHelperUsefulTrue() {
		$headers = array(
			'User-Agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)'
		);

		$hdDevice = new HandsetDetection\HDDevice();
		$result = $hdDevice->isHelperUseful($headers);
		$this->assertTrue($result);
	}

	function testIsHelperUsefulFalse() {
		$headers = array(
			'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
		);

		$hdDevice = new HandsetDetection\HDDevice();
		$result = $hdDevice->isHelperUseful($headers);
		$this->assertFalse($result);
	}
}

