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
		$store->setConfig(array('filesdir' => '/tmp'), true);
		$store->write($key, $this->testData);

		$data = $store->read($key);
		$this->assertEquals($this->testData, $data);

		$cache = new HandsetDetection\HDCache();
		$data = $cache->read($key);

		if ($cache->getName() == 'none') {
			$this->assertFalse($data);
		} else {
			$this->assertEquals($this->testData, $data);
		}

		$exists = is_file($store->directory . DIRECTORY_SEPARATOR . "$key.json");
		$this->assertTrue($exists);
	}

	// Writes to store & not cache
	function testStoreFetch() {
		$key = 'storekey2'.time();
		$store = HandsetDetection\HDStore::getInstance();
		$store->setConfig(array('filesdir' => '/tmp'), true);
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
		$store->setConfig(array('filesdir' => '/tmp'), true);
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
		$store->setConfig(array('filesdir' => '/tmp'), true);
		$store->store($key, $this->testData);

		$devices = $store->fetchDevices();
		$this->assertEquals($devices['devices'][0], $this->testData);
		$store->purge();
	}

	// Moves a file from disk into store (vanishes from previous location).
	function testMoveInFetch() {
		$store = HandsetDetection\HDStore::getInstance();
		$store->setConfig(array('filesdir' => '/tmp'), true);
		$store->purge();

		$tmpData = '{"Device":{"_id":"3454","hd_ops":{"is_generic":0,"stop_on_detect":0,"overlay_result_specs":0},"hd_specs":{"general_vendor":"Sagem","general_model":"MyX5-2","general_platform":"","general_image":"","general_aliases":"","general_eusar":"","general_battery":"","general_type":"","general_cpu":"","design_formfactor":"","design_dimensions":"","design_weight":0,"design_antenna":"","design_keyboard":"","design_softkeys":"","design_sidekeys":"","display_type":"","display_color":"","display_colors":"","display_size":"","display_x":"128","display_y":"160","display_other":"","memory_internal":"","memory_slot":"","network":"","media_camera":"","media_secondcamera":"","media_videocapture":"","media_videoplayback":"","media_audio":"","media_other":"","features":"","connectors":"","general_platform_version":"","general_browser":"","general_browser_version":"","general_language":"","general_platform_version_max":"","general_app":"","general_app_version":"","display_ppi":0,"display_pixel_ratio":0,"benchmark_min":0,"benchmark_max":0,"general_app_category":"","general_virtual":0,"display_css_screen_sizes":""}}}';
		file_put_contents('Device_3454.json', $tmpData);
		$store->moveIn('Device_3454.json',  'Device_3454.json');
		$this->assertFileNotExists('Device_3454.json');
		$this->assertFileExists($store->directory . DIRECTORY_SEPARATOR . 'Device_3454.json');

		$tmpData = '{"Device":{"_id":"3455","hd_ops":{"is_generic":0,"stop_on_detect":0,"overlay_result_specs":0},"hd_specs":{"general_aliases":"","display_x":"120","display_y":"120","general_vendor":"Sagem","general_model":"MY X55","general_platform":"","general_image":"","network":"","general_type":"","general_eusar":"","general_battery":"","general_cpu":"","design_formfactor":"","design_dimensions":"","design_weight":0,"design_antenna":"","design_keyboard":"","design_softkeys":"","design_sidekeys":"","display_type":"","display_color":"","display_colors":"","display_size":"","display_other":"","memory_internal":"","memory_slot":"","media_camera":"","media_secondcamera":"","media_videocapture":"","media_videoplayback":"","media_audio":"","media_other":"","features":"","connectors":"","general_platform_version":"","general_browser":"","general_browser_version":"","general_language":"","general_platform_version_max":"","general_app":"","general_app_version":"","display_ppi":0,"display_pixel_ratio":0,"benchmark_min":0,"benchmark_max":0,"general_app_category":"","general_virtual":0,"display_css_screen_sizes":""}}}';
		file_put_contents('Device_3455.json', $tmpData);
		$store->moveIn('Device_3455.json',  'Device_3455.json');

		$tmpData = '{"Device":{"_id":"3456","hd_ops":{"is_generic":0,"stop_on_detect":0,"overlay_result_specs":0},"hd_specs":{"general_vendor":"Sagem","general_model":"myX5-2v","general_platform":"","general_image":"","general_aliases":"","general_eusar":"","general_battery":"","general_type":"","general_cpu":"","design_formfactor":"","design_dimensions":"","design_weight":0,"design_antenna":"","design_keyboard":"","design_softkeys":"","design_sidekeys":"","display_type":"","display_color":"","display_colors":"","display_size":"","display_x":"128","display_y":"160","display_other":"","memory_internal":"","memory_slot":"","network":"","media_camera":"","media_secondcamera":"","media_videocapture":"","media_videoplayback":"","media_audio":"","media_other":"","features":"","connectors":"","general_platform_version":"","general_browser":"","general_browser_version":"","general_language":"","general_platform_version_max":"","general_app":"","general_app_version":"","display_ppi":0,"display_pixel_ratio":0,"benchmark_min":0,"benchmark_max":0,"general_app_category":"","general_virtual":0,"display_css_screen_sizes":""}}}';
		file_put_contents('Device_3456.json', $tmpData);
		$store->moveIn('Device_3456.json',  'Device_3456.json');

		$devices = $store->fetchDevices();
		$this->assertEquals(3, count($devices['devices']));
		$store->purge();
	}


	// Test singleton'ship
	function testSingleton() {
		$store = HandsetDetection\HDStore::getInstance();
		$store->setConfig(array('filesdir' => '/tmp'), true);
		$store2 = HandsetDetection\HDStore::getInstance();

		$store->setConfig(array('filesdir' => '/tmp/storetest'));
		$this->assertEquals($store2->directory, '/tmp/storetest/hd40store');
	}

	// Test iterability
	function testIterability() {
		$store = HandsetDetection\HDStore::getInstance();
		$store->setConfig(array('filesdir' => '/tmp'), true);
		$store->purge();

		$tmpData = '{"Device":{"_id":"3454","hd_ops":{"is_generic":0,"stop_on_detect":0,"overlay_result_specs":0},"hd_specs":{"general_vendor":"Sagem","general_model":"MyX5-2","general_platform":"","general_image":"","general_aliases":"","general_eusar":"","general_battery":"","general_type":"","general_cpu":"","design_formfactor":"","design_dimensions":"","design_weight":0,"design_antenna":"","design_keyboard":"","design_softkeys":"","design_sidekeys":"","display_type":"","display_color":"","display_colors":"","display_size":"","display_x":"128","display_y":"160","display_other":"","memory_internal":"","memory_slot":"","network":"","media_camera":"","media_secondcamera":"","media_videocapture":"","media_videoplayback":"","media_audio":"","media_other":"","features":"","connectors":"","general_platform_version":"","general_browser":"","general_browser_version":"","general_language":"","general_platform_version_max":"","general_app":"","general_app_version":"","display_ppi":0,"display_pixel_ratio":0,"benchmark_min":0,"benchmark_max":0,"general_app_category":"","general_virtual":0,"display_css_screen_sizes":""}}}';
		file_put_contents('Device_3454.json', $tmpData);
		$store->moveIn('Device_3454.json',  'Device_3454.json');
		$this->assertFileNotExists('Device_3454.json');
		$this->assertFileExists($store->directory . DIRECTORY_SEPARATOR . 'Device_3454.json');

		$tmpData = '{"Device":{"_id":"3455","hd_ops":{"is_generic":0,"stop_on_detect":0,"overlay_result_specs":0},"hd_specs":{"general_aliases":"","display_x":"120","display_y":"120","general_vendor":"Sagem","general_model":"MY X55","general_platform":"","general_image":"","network":"","general_type":"","general_eusar":"","general_battery":"","general_cpu":"","design_formfactor":"","design_dimensions":"","design_weight":0,"design_antenna":"","design_keyboard":"","design_softkeys":"","design_sidekeys":"","display_type":"","display_color":"","display_colors":"","display_size":"","display_other":"","memory_internal":"","memory_slot":"","media_camera":"","media_secondcamera":"","media_videocapture":"","media_videoplayback":"","media_audio":"","media_other":"","features":"","connectors":"","general_platform_version":"","general_browser":"","general_browser_version":"","general_language":"","general_platform_version_max":"","general_app":"","general_app_version":"","display_ppi":0,"display_pixel_ratio":0,"benchmark_min":0,"benchmark_max":0,"general_app_category":"","general_virtual":0,"display_css_screen_sizes":""}}}';
		file_put_contents('Device_3455.json', $tmpData);
		$store->moveIn('Device_3455.json',  'Device_3455.json');

		$tmpData = '{"Device":{"_id":"3456","hd_ops":{"is_generic":0,"stop_on_detect":0,"overlay_result_specs":0},"hd_specs":{"general_vendor":"Sagem","general_model":"myX5-2v","general_platform":"","general_image":"","general_aliases":"","general_eusar":"","general_battery":"","general_type":"","general_cpu":"","design_formfactor":"","design_dimensions":"","design_weight":0,"design_antenna":"","design_keyboard":"","design_softkeys":"","design_sidekeys":"","display_type":"","display_color":"","display_colors":"","display_size":"","display_x":"128","display_y":"160","display_other":"","memory_internal":"","memory_slot":"","network":"","media_camera":"","media_secondcamera":"","media_videocapture":"","media_videoplayback":"","media_audio":"","media_other":"","features":"","connectors":"","general_platform_version":"","general_browser":"","general_browser_version":"","general_language":"","general_platform_version_max":"","general_app":"","general_app_version":"","display_ppi":0,"display_pixel_ratio":0,"benchmark_min":0,"benchmark_max":0,"general_app_category":"","general_virtual":0,"display_css_screen_sizes":""}}}';
		file_put_contents('Device_3456.json', $tmpData);
		$store->moveIn('Device_3456.json',  'Device_3456.json');

		$tmp = array();
		foreach($store as $key => $value) {
			$tmp[$key] = $value;
		}
		$this->assertArrayHasKey('Device_3454', $tmp);
		$this->assertArrayHasKey('Device_3455', $tmp);
		$this->assertArrayHasKey('Device_3456', $tmp);

		$this->assertArrayHasKey('Device', $tmp['Device_3454']);
		$this->assertArrayHasKey('Device', $tmp['Device_3455']);
		$this->assertArrayHasKey('Device', $tmp['Device_3456']);

		$store->purge();
	}
}


