<?php

// testHasBiKeys - key case, android, ios, http, windows phone.

error_reporting(E_ALL | E_STRICT);

class HD4Test extends PHPUnit_Framework_TestCase {

	var $cloudConfig = 'hd4CloudConfig.php';
	var $ultimateConfig = 'hd4UltimateConfig.php';

	var $devices = array(
		'NokiaN95' => array(
      		'general_vendor' => 'Nokia',
			'general_model' => 'N95',
			'general_platform' => 'Symbian',
			'general_platform_version' => '9.2',
			'general_platform_version_max' => '',
			'general_browser' => '',
			'general_browser_version' => '',
			'general_image' => 'nokian95-1403496370-0.gif',
			'general_aliases' => array(),
			'general_app' => '',
			'general_app_category' => '',
			'general_app_version' => '',
			'general_language' => '',
			'general_eusar' => '0.50',
			'general_battery' => array('Li-Ion 950 mAh','BL-5F'),
			'general_type' => 'Mobile',
			'general_cpu' => array('Dual ARM 11','332Mhz'),
			'general_virtual' => 0,
			'design_formfactor' => 'Dual Slide',
			'design_dimensions' => '99 x 53 x 21',
			'design_weight' => '120',
			'design_antenna' => 'Internal',
			'design_keyboard' => 'Numeric',
			'design_softkeys' => '2',
			'design_sidekeys' => array('Volume','Camera'),
			'display_type' => 'TFT',
			'display_color' => 'Yes',
			'display_colors' => '16M',
			'display_css_screen_sizes' => array('240x320'),
			'display_size' => '2.6"',
			'display_x' => '240',
			'display_y' => '320',
			'display_other' => array(),
			'display_pixel_ratio' => '1.0',
			'display_ppi' => 154,
			'memory_internal' => array('160MB','64MB RAM','256MB ROM'),
			'memory_slot' => array('microSD', '8GB', '128MB'),
			'network' => array('GSM850','GSM900','GSM1800','GSM1900','UMTS2100','HSDPA2100','Infrared','Bluetooth 2.0','802.11b','802.11g','GPRS Class 10','EDGE Class 32'),
			'media_camera' => array('5MP','2592x1944'),
			'media_secondcamera' => array('QVGA'),
			'media_videocapture' => array('VGA@30fps'),
			'media_videoplayback' => array('MPEG4','H.263','H.264','3GPP','RealVideo 8','RealVideo 9','RealVideo 10'),
			'media_audio' => array('MP3','AAC','AAC+','eAAC+','WMA'),
			'media_other' => array('Auto focus','Video stabilizer','Video calling','Carl Zeiss optics','LED Flash'),
			'features' => array(
							  'Unlimited entries','Multiple numbers per contact','Picture ID','Ring ID','Calendar','Alarm','To-Do','Document viewer',
							  'Calculator','Notes','UPnP','Computer sync','VoIP','Music ringtones (MP3)','Vibration','Phone profiles','Speakerphone',
							  'Accelerometer','Voice dialing','Voice commands','Voice recording','Push-to-Talk','SMS','MMS','Email','Instant Messaging',
					'Stereo FM radio','Visual radio','Dual slide design','Organizer','Word viewer','Excel viewer','PowerPoint viewer','PDF viewer',
					'Predictive text input','Push to talk','Voice memo','Games'
						),
			'connectors' => array('USB','miniUSB','3.5mm AUdio','TV Out'),
			'benchmark_max' => 0,
			'benchmark_min' => 0
        )
	);

	/**
	 * test for config file .. required for all cloud tests
	 * @group cloud
	 **/
	function test_cloudConfigExists() {
		$this->assertEquals(true, true);
	}

	/**
	 * device vendors test
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceVendors() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$result = $hd->deviceVendors();
		$reply = $hd->getReply();
		//print_r(json_encode($reply));

		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertContains('Nokia', $reply['vendor']);
		$this->assertContains('Samsung', $reply['vendor']);
	}

	/**
	 * device models test
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	public function test_deviceModels() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$reply = $hd->deviceModels('Nokia');
		$data = $hd->getReply();
		
		$this->assertEquals($reply, true);
		$this->assertGreaterThan(700, count($data['model']));
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
	}

	/**
	 * device view test
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	public function test_deviceView() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$reply = $hd->deviceView('Nokia', 'N95');
		$data = $hd->getReply();
		//print_r(json_encode($data));
		
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		ksort($data['device']);
		ksort($this->devices['NokiaN95']);

		//print_r(json_encode($data['device']));
		//print_r(json_encode($this->devices['NokiaN95']));
		$this->assertEquals(strtolower(json_encode($this->devices['NokiaN95'])), strtolower(json_encode($data['device'])));
	}

	/**
	 * device whatHas test
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/		
	public function test_deviceWhatHas() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$reply = $hd->deviceWhatHas('design_dimensions', '101 x 44 x 16');
		$data = $hd->getReply();
		//print_r(json_encode($data));
		
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		$jsonString = json_encode($data['devices']);
		$this->assertEquals(true, preg_match('/Asus/', $jsonString));
		$this->assertEquals(true, preg_match('/V80/', $jsonString));
		$this->assertEquals(true, preg_match('/Spice/', $jsonString));
		$this->assertEquals(true, preg_match('/S900/', $jsonString));
		$this->assertEquals(true, preg_match('/Voxtel/', $jsonString));
		$this->assertEquals(true, preg_match('/RX800/', $jsonString));
	}

	/**
	 * Detection test Windows PC running Chrome
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/		
	function test_deviceDetectHTTPDesktop() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$headers = array(
			'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();

		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertEquals('Computer', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test Junk user-agent
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/		
	function test_deviceDetectHTTPDesktopJunk() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$headers = array(
			'User-Agent' => 'aksjakdjkjdaiwdidjkjdkawjdijwidawjdiajwdkawdjiwjdiawjdwidjwakdjajdkad'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertFalse($result);
		$this->assertEquals(301, $reply['status']);
		$this->assertEquals('Not Found', $reply['message']);
	}

	/**
	 * Detection test Wii
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectHTTPWii() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$headers = array(
			'User-Agent' => 'Opera/9.30 (Nintendo Wii; U; ; 2047-7; es-Es)'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertEquals('Console', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test iPhone
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectHTTP() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$headers = array(
			'User-Agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.3', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * Detection test iPhone in weird headers
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectHTTPOtherHeader() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$headers = array(
			'user-agent' => 'blahblahblah',
			'x-fish-header' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.3', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * Detection test iPhone 3GS (same UA as iPhone 3G, different x-local-hardwareinfo header)
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectHTTPHardwareInfo() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)',
			'x-local-hardwareinfo' => '320:480:100:100'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 3GS', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.2.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * Detection test iPhone 3G (same UA as iPhone 3GS, different x-local-hardwareinfo header)
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectHTTPHardwareInfoB() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)',
			'x-local-hardwareinfo' => '320:480:100:72'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 3G', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.2.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * Detection test iPhone - Crazy benchmark (eg from emulated desktop) with outdated OS
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectHTTPHardwareInfoC() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_0 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)',
			'x-local-hardwareinfo' => '320:480:200:1200',
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 3G', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('2.0', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * Detection test user-agent has been encoded with plus for space.
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectHTTPPlusForSpace() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0+(Linux;+Android+5.1.1;+SM-J110M+Build/LMY48B;+wv)+AppleWebKit/537.36+(KHTML,+like+Gecko)+Version/4.0+Chrome/47.0.2526.100+Mobile+Safari/537.36',
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		$this->assertTrue($result);
		$this->assertEquals('Samsung', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('SM-J110M', $reply['hd_specs']['general_model']);
		$this->assertEquals('Android', $reply['hd_specs']['general_platform']);
		$this->assertEquals('5.1.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test iPhone 5s running Facebook 9.0 app (hence no general_browser set).
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectHTTPFBiOS() {
		
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_1 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Mobile/11D201 [FBAN/FBIOS;FBAV/9.0.0.25.31;FBBV/2102024;FBDV/iPhone6,2;FBMD/iPhone;FBSN/iPhone OS;FBSV/7.1.1;FBSS/2; FBCR/vodafoneIE;FBID/phone;FBLC/en_US;FBOP/5]',
			'Accept-Language' => 'da, en-gb;q=0.8, en;q=0.7'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 5S', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('7.1.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('da', $reply['hd_specs']['general_language']);
		$this->assertEquals('Danish', $reply['hd_specs']['general_language_full']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertEquals('Facebook', $reply['hd_specs']['general_app']);
		$this->assertEquals('9.0', $reply['hd_specs']['general_app_version']);
		$this->assertEquals('', $reply['hd_specs']['general_browser']);
		$this->assertEquals('', $reply['hd_specs']['general_browser_version']);
		
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * Android version is not supplied in UA & device base profile has more info than detected platform result
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectNoPlatformOverlay() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (Linux; U; Android; en-ca; GT-I9500 Build) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1'
		);
		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Samsung', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('GT-I9500', $reply['hd_specs']['general_model']);
		$this->assertEquals('Android', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.2.2', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test Samsung GT-I9500 Native - Note : Device shipped with Android 4.2.2, so this device has been updated.
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectBIAndroid() {
		$buildInfo = array (
			'ro.build.PDA' => 'I9500XXUFNE7',
			'ro.build.changelist' => '699287',
			'ro.build.characteristics' => 'phone',
			'ro.build.date.utc' => '1401287026',
			'ro.build.date' => 'Wed May 28 23:23:46 KST 2014',
			'ro.build.description' => 'ja3gxx-user 4.4.2 KOT49H I9500XXUFNE7 release-keys',
			'ro.build.display.id' => 'KOT49H.I9500XXUFNE7',
			'ro.build.fingerprint' => 'samsung/ja3gxx/ja3g:4.4.2/KOT49H/I9500XXUFNE7:user/release-keys',
			'ro.build.hidden_ver' => 'I9500XXUFNE7',
			'ro.build.host' => 'SWDD5723',
			'ro.build.id' => 'KOT49H',
			'ro.build.product' => 'ja3g',
			'ro.build.tags' => 'release-keys',
			'ro.build.type' => 'user',
			'ro.build.user' => 'dpi',
			'ro.build.version.codename' => 'REL',
			'ro.build.version.incremental' => 'I9500XXUFNE7',
			'ro.build.version.release' => '4.4.2',
			'ro.build.version.sdk' => '19',
			'ro.product.board' => 'universal5410',
			'ro.product.brand' => 'samsung',
			'ro.product.cpu.abi2' => 'armeabi',
			'ro.product.cpu.abi' => 'armeabi-v7a',
			'ro.product.device' => 'ja3g',
			'ro.product.locale.language' => 'en',
			'ro.product.locale.region' => 'GB',
			'ro.product.manufacturer' => 'samsung',
			'ro.product.model' => 'GT-I9500',
			'ro.product.name' => 'ja3gxx',
			'ro.product_ship' => 'true'
		);

		//print_r(json_encode($buildInfo));
		
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		//print_r(json_encode($reply));
		
		$this->assertEquals('Samsung', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('GT-I9500', $reply['hd_specs']['general_model']);
		$this->assertEquals('Android', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.4.2', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Samsung Galaxy S4', $reply['hd_specs']['general_aliases'][0]);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test Samsung GT-I9500 Native - Note : Device shipped with Android 4.2.2, so this device has been updated.
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectBIAndroidUpdatedOs() {
		$buildInfo = array (
			'ro.build.id' => 'KOT49H',
			'ro.build.version.release' => '5.2',
			'ro.build.version.sdk' => '19',
			'ro.product.brand' => 'samsung',
			'ro.product.model' => 'GT-I9500',
		);

		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		$this->assertEquals('Samsung', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('GT-I9500', $reply['hd_specs']['general_model']);
		$this->assertEquals('Android', $reply['hd_specs']['general_platform']);
		$this->assertEquals('5.2', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Samsung Galaxy S4', $reply['hd_specs']['general_aliases'][0]);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test Samsung GT-I9500 Native - Note : Device shipped with Android 4.2.2, so this device has been updated.
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectBIAndroidDefaultOs() {
		$buildInfo = array (
			'ro.product.brand' => 'samsung',
			'ro.product.model' => 'GT-I9500',
		);

		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		$this->assertEquals('Samsung', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('GT-I9500', $reply['hd_specs']['general_model']);
		$this->assertEquals('Android', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.2.2', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Samsung Galaxy S4', $reply['hd_specs']['general_aliases'][0]);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test iPhone 4S Native
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectBIiOS() {
		$buildInfo = array (
			'utsname.machine' => 'iphone4,1',
			'utsname.brand' => 'Apple'
		);

		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		//print_r(json_encode($reply));

		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 4S', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		// Note : Default shipped version in the absence of any version information
		$this->assertEquals('5.0', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test iPhone 4S Native
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectBIiOSUpdatedOs() {
		$buildInfo = array (
			'utsname.machine' => 'iphone4,1',
			'utsname.brand' => 'Apple',
			'uidevice.systemVersion' => '5.1',
			'UIDEVICE.SYSTEMNAME' => 'iphone os' 
		);

		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		//print_r(json_encode($reply));
		
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 4S', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		// Note : Default shipped version is 5.0
		$this->assertEquals('5.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test iPhone 4S Native
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectBIiOSOverlayPlatform() {
		$buildInfo = array (
			'utsname.machine' => 'iphone4,1',
			'utsname.brand' => 'Apple',
			'uidevice.systemversion' => '5.1',
			'uidevice.systemname' => 'iphone os'
		);

		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		//print_r(json_encode($reply));

		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 4S', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('5.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test Windows Phone Native Nokia Lumia 1020
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectWindowsPhone() {
		$buildInfo = array (
			'devicemanufacturer' => 'nokia',
			'devicename' => 'RM-875'
		);
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		//print_r(json_encode($reply));
		
		$this->assertEquals('Nokia', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('RM-875', $reply['hd_specs']['general_model']);
		$this->assertEquals('Windows Phone', $reply['hd_specs']['general_platform']);
		$this->assertEquals('8.0', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertEquals('326', $reply['hd_specs']['display_ppi']);
	}

	/**
	 * Detection test Windows Phone Native Nokia Lumia 1020
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectWindowsPhoneB() {
		$buildInfo = array (
			'devicemanufacturer' => 'nokia',
			'devicename' => 'RM-875',
			'osname' => 'windows phone',
			'osversion' => '8.1'
		);
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		//print_r(json_encode($reply));
		
		$this->assertEquals('Nokia', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('RM-875', $reply['hd_specs']['general_model']);
		$this->assertEquals('Windows Phone', $reply['hd_specs']['general_platform']);
		$this->assertEquals('8.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertEquals('326', $reply['hd_specs']['display_ppi']);
	}

	/**
	 * Detection test Windows Phone Native Nokia Lumia 1020
	 * @depends test_cloudConfigExists
	 * @group cloud
	 **/
	function test_deviceDetectBIiPhoneOverlay() {
		$buildInfo = array (
			'utsname.brand' => 'apple',
			'utsname.machine' => 'iPhone7,2',
			'UIDevice.systemVersion' => '9.2',
			'UIDevice.systemName' => 'iPhone OS'
		);
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		//print_r(json_encode($reply));
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 6', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('9.2', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}
	// ***************************************************************************************************
	// ***************************************** Ultimate Tests ******************************************
	// ***************************************************************************************************

	/**
	 * Broken Archive Test
	 * @group ultimate
	 **/
	function test_unzipBogusArchive() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$hd->setTimeout(500);

		file_put_contents('/tmp/test.zip', 'testy mc testery fish fish fish');
		$result = $hd->installArchive('/tmp/test.zip');
		$data = $hd->getReply();
		$this->assertFalse($result);
		$this->assertEquals(299, $data['status']);
	}

	/**
	 * Empty Archive Test
	 * @group ultimate
	 **/
	function test_detectionOnEmptyArchive() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);

		$store = HandsetDetection\HDStore::getInstance();
		$store->purge();

		$headers = array(
			'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		$this->assertEquals(299, $reply['status']);
		$this->assertEquals('Branch not found. Is it installed ?', $reply['message']);
	}
	
	/**
	 * Empty Archive Test
	 * @group ultimate
	 **/
	function test_detectionOnEmptyArchiveStillNotSolved() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);

		$store = HandsetDetection\HDStore::getInstance();
		$store->purge();

		$headers = array(
			'User-Agent' => '...'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		$this->assertEquals(299, $reply['status']);
		$this->assertEquals('Branch not found. Is it installed ?', $reply['message']);
	}
	
	/**
	 * Fetch Archive Test
	 * @group ultimate
	 **/
	function test_fetchArchive() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$hd->setTimeout(500);

		$store = HandsetDetection\HDStore::getInstance();
		$store->purge();

		$result = $hd->deviceFetchArchive();
		$data = $hd->getRawReply();
		$size = strlen($data);
		echo "Downloaded $size bytes ";
		if (! $result) {
			$data = $hd->getReply();
			print_r($data);
		}
		if ($size < 1000) {
			$this->markTestSkipped($data);
		} else {
			$this->assertTrue($result);
			$this->assertGreaterThan(19000000, strlen($data));		// Filesize greater than 19Mb (currently 28Mb).
		}
	}

	/**
	 * device vendors test
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceVendors() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$result = $hd->deviceVendors();
		$reply = $hd->getReply();

		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertContains('Nokia', $reply['vendor']);
		$this->assertContains('Samsung', $reply['vendor']);
	}

	/**
	 * device models test
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	public function test_ultimate_deviceModels() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$reply = $hd->deviceModels('Nokia');
		$data = $hd->getReply();

		$this->assertEquals($reply, true);
		$this->assertGreaterThan(700, count($data['model']));
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
	}

	/**
	 * device view test
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	public function test_ultimate_deviceView() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$reply = $hd->deviceView('Nokia', 'N95');
		$data = $hd->getReply();
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		ksort($data['device']);
		ksort($this->devices['NokiaN95']);

		$this->assertEquals(strtolower(json_encode($this->devices['NokiaN95'])), strtolower(json_encode($data['device'])));
	}

	/**
	 * device whatHas test
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	public function test_ultimate_deviceWhatHas() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$reply = $hd->deviceWhatHas('design_dimensions', '101 x 44 x 16');
		$data = $hd->getReply();
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		$jsonString = json_encode($data['devices']);
		$this->assertEquals(true, preg_match('/Asus/', $jsonString));
		$this->assertEquals(true, preg_match('/V80/', $jsonString));
		$this->assertEquals(true, preg_match('/Spice/', $jsonString));
		$this->assertEquals(true, preg_match('/S900/', $jsonString));
		$this->assertEquals(true, preg_match('/Voxtel/', $jsonString));
		$this->assertEquals(true, preg_match('/RX800/', $jsonString));
	}

	/**
	 * Windows PC running Chrome
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectHTTPDesktop() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertEquals('Computer', $reply['hd_specs']['general_type']);
	}

	/**
	 * Junk user-agent
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectHTTPDesktopJunk() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'User-Agent' => 'aksjakdjkjdaiwdidjkjdkawjdijwidawjdiajwdkawdjiwjdiawjdwidjwakdjajdkad'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertFalse($result);
		$this->assertEquals(301, $reply['status']);
		$this->assertEquals('Not Found', $reply['message']);
	}

	/**
	 * Wii
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectHTTPWii() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'User-Agent' => 'Opera/9.30 (Nintendo Wii; U; ; 2047-7; es-Es)'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertEquals('Console', $reply['hd_specs']['general_type']);
	}

	/**
	 * iPhone
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectHTTP() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'User-Agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.3', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * iPhone - user-agent in random other header
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectHTTPOtherHeader() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'blahblahblah',
			'x-fish-header' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.3', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * iPhone 3GS (same UA as iPhone 3G, different x-local-hardwareinfo header)
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectHTTPHardwareInfo() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)',
			'x-local-hardwareinfo' => '320:480:100:100'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 3GS', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.2.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * iPhone 3G (same UA as iPhone 3GS, different x-local-hardwareinfo header)
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectHTTPHardwareInfoB() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)',
			'x-local-hardwareinfo' => '320:480:100:72'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 3G', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.2.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * iPhone - Crazy benchmark (eg from emulated desktop) with outdated OS
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectHTTPHardwareInfoC() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_0 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)',
			'x-local-hardwareinfo' => '320:480:200:1200',
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 3G', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('2.0', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * Detection test user-agent has been encoded with plus for space.
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectHTTPPlusForSpace() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0+(Linux;+Android+5.1.1;+SM-J110M+Build/LMY48B;+wv)+AppleWebKit/537.36+(KHTML,+like+Gecko)+Version/4.0+Chrome/47.0.2526.100+Mobile+Safari/537.36',
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		$this->assertTrue($result);
		$this->assertEquals('Samsung', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('SM-J110M', $reply['hd_specs']['general_model']);
		$this->assertEquals('Android', $reply['hd_specs']['general_platform']);
		$this->assertEquals('5.1.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}

	/**
	 * iPhone 5s running Facebook 9.0 app (hence no general_browser set).
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectHTTPFBiOS() {

		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_1 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Mobile/11D201 [FBAN/FBIOS;FBAV/9.0.0.25.31;FBBV/2102024;FBDV/iPhone6,2;FBMD/iPhone;FBSN/iPhone OS;FBSV/7.1.1;FBSS/2; FBCR/vodafoneIE;FBID/phone;FBLC/en_US;FBOP/5]',
			'Accept-Language' => 'da, en-gb;q=0.8, en;q=0.7'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 5S', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('7.1.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('da', $reply['hd_specs']['general_language']);
		$this->assertEquals('Danish', $reply['hd_specs']['general_language_full']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertEquals('Facebook', $reply['hd_specs']['general_app']);
		$this->assertEquals('9.0', $reply['hd_specs']['general_app_version']);
		$this->assertEquals('', $reply['hd_specs']['general_browser']);
		$this->assertEquals('', $reply['hd_specs']['general_browser_version']);

		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * Android version is not supplied in UA & device base profile has more info than detected platform result
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectNoPlatformOverlay() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (Linux; U; Android; en-ca; GT-I9500 Build) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1'
		);
		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Samsung', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('GT-I9500', $reply['hd_specs']['general_model']);
		$this->assertEquals('Android', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.2.2', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}
	
	/**
	 * Samsung GT-I9500 Native - Note : Device shipped with Android 4.2.2, so this device has been updated.
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectBIAndroid() {
		$buildInfo = array (
			'ro.build.PDA' => 'I9500XXUFNE7',
			'ro.build.changelist' => '699287',
			'ro.build.characteristics' => 'phone',
			'ro.build.date.utc' => '1401287026',
			'ro.build.date' => 'Wed May 28 23:23:46 KST 2014',
			'ro.build.description' => 'ja3gxx-user 4.4.2 KOT49H I9500XXUFNE7 release-keys',
			'ro.build.display.id' => 'KOT49H.I9500XXUFNE7',
			'ro.build.fingerprint' => 'samsung/ja3gxx/ja3g:4.4.2/KOT49H/I9500XXUFNE7:user/release-keys',
			'ro.build.hidden_ver' => 'I9500XXUFNE7',
			'ro.build.host' => 'SWDD5723',
			'ro.build.id' => 'KOT49H',
			'ro.build.product' => 'ja3g',
			'ro.build.tags' => 'release-keys',
			'ro.build.type' => 'user',
			'ro.build.user' => 'dpi',
			'ro.build.version.codename' => 'REL',
			'ro.build.version.incremental' => 'I9500XXUFNE7',
			'ro.build.version.release' => '4.4.2',
			'ro.build.version.sdk' => '19',
			'ro.product.board' => 'universal5410',
			'ro.product.brand' => 'samsung',
			'ro.product.cpu.abi2' => 'armeabi',
			'ro.product.cpu.abi' => 'armeabi-v7a',
			'ro.product.device' => 'ja3g',
			'ro.product.locale.language' => 'en',
			'ro.product.locale.region' => 'GB',
			'ro.product.manufacturer' => 'samsung',
			'ro.product.model' => 'GT-I9500',
			'ro.product.name' => 'ja3gxx',
			'ro.product_ship' => 'true'
		);

		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		$this->assertEquals('Samsung', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('GT-I9500', $reply['hd_specs']['general_model']);
		$this->assertEquals('Android', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.4.2', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Samsung Galaxy S4', $reply['hd_specs']['general_aliases'][0]);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test Samsung GT-I9500 Native - Note : Device shipped with Android 4.2.2, so this device has been updated.
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectBIAndroidUpdatedOs() {
		$buildInfo = array (
			'ro.build.id' => 'KOT49H',
			'ro.build.version.release' => '5.2',
			'ro.build.version.sdk' => '19',
			'ro.product.brand' => 'samsung',
			'ro.product.model' => 'GT-I9500',
		);

		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		$this->assertEquals('Samsung', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('GT-I9500', $reply['hd_specs']['general_model']);
		$this->assertEquals('Android', $reply['hd_specs']['general_platform']);
		$this->assertEquals('5.2', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Samsung Galaxy S4', $reply['hd_specs']['general_aliases'][0]);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test Samsung GT-I9500 Native - Note : Device shipped with Android 4.2.2
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectBIAndroidDefaultOs() {
		$buildInfo = array (
			'ro.product.brand' => 'samsung',
			'ro.product.model' => 'GT-I9500',
		);

		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		$this->assertEquals('Samsung', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('GT-I9500', $reply['hd_specs']['general_model']);
		$this->assertEquals('Android', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.2.2', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Samsung Galaxy S4', $reply['hd_specs']['general_aliases'][0]);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}
	
	/**
	 * iPhone 4S Native
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectBIiOS() {
		$buildInfo = array (
			'utsname.machine' => 'iphone4,1',
			'utsname.brand' => 'Apple',
		);

		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 4S', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		// Note : Default shipped version in the absence of any version information
		$this->assertEquals('5.0', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}

	/**
	 * Detection test iPhone 4S Native
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectBIiOSOverlayPlatform() {
		$buildInfo = array (
			'utsname.machine' => 'iphone4,1',
			'utsname.brand' => 'Apple',
			'uidevice.systemversion' => '5.1',
			'uidevice.systemname' => 'iphone os'
		);

		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 4S', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('5.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}
	//
	/**
	 * Windows Phone Native Nokia Lumia 1020
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectWindowsPhone() {
		$buildInfo = array (
			'devicemanufacturer' => 'nokia',
			'devicename' => 'RM-875'
		);

		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		$this->assertEquals('Nokia', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('RM-875', $reply['hd_specs']['general_model']);
		$this->assertEquals('Windows Phone', $reply['hd_specs']['general_platform']);
		$this->assertEquals('8.0', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertEquals('326', $reply['hd_specs']['display_ppi']);
	}

	/**
	 * Windows Phone Native Nokia Lumia 1020
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/
	function test_ultimate_deviceDetectWindowsPhoneB() {
		$buildInfo = array (
			'devicemanufacturer' => 'nokia',
			'devicename' => 'RM-875',
			'osname' => 'windows phone',
			'osversion' => '8.1'
		);

		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		$this->assertEquals('Nokia', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('RM-875', $reply['hd_specs']['general_model']);
		$this->assertEquals('Windows Phone', $reply['hd_specs']['general_platform']);
		$this->assertEquals('8.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
		$this->assertEquals('326', $reply['hd_specs']['display_ppi']);
	}

	/**
	 * Detection test Windows Phone Native Nokia Lumia 1020
	 * @depends test_fetchArchive
	 * @group ultimate
	 **/	
	function test_ultimate_deviceDetectBIiPhoneOverlay() {
		$buildInfo = array (
			'utsname.brand' => 'apple',
			'utsname.machine' => 'iPhone7,2',
			'UIDevice.systemVersion' => '9.2',
			'UIDevice.systemName' => 'iPhone OS'
		);
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		//print_r(json_encode($reply));
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 6', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('9.2', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('Mobile', $reply['hd_specs']['general_type']);
	}
	
	// ***************************************************************************************************
	// *********************************** Ultimate Community Tests **************************************
	// ***************************************************************************************************

	/**
	 * Fetch Archive Test
	 *
	 * The community fetchArchive version contains a cut down version of the device specs.
	 * It has general_vendor, general_model, display_x, display_y, general_platform, general_platform_version,
	 * general_browser, general_browser_version, general_app, general_app_version, general_language,
	 * general_language_full, benahmark_min & benchmark_max
	 *
	 * @group community
	 **/
	function test_ultimate_community_fetchArchive() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$hd->setTimeout(500);

		// Purge store
		$hd->Store->purge();

		// Fetch new device specs into store.
		$result = $hd->communityFetchArchive();
		
		$data = $hd->getRawReply();
		$size = strlen($data);
		echo "Downloaded $size bytes ";
		if ($size < 1000) {
			$this->markTestSkipped($data);
		} else {
			$this->assertTrue($result);
			$this->assertGreaterThan(9000000, strlen($data));		// Filesize greater than 9Mb
		}
	}


	/**
	 * Windows PC running Chrome
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectHTTPDesktop() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
	}

	/**
	 * Junk user-agent
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectHTTPDesktopJunk() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'User-Agent' => 'aksjakdjkjdaiwdidjkjdkawjdijwidawjdiajwdkawdjiwjdiawjdwidjwakdjajdkad'
		);
		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertFalse($result);
		$this->assertEquals(301, $reply['status']);
		$this->assertEquals('Not Found', $reply['message']);
	}

	/**
	 * Wii
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectHTTPWii() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'User-Agent' => 'Opera/9.30 (Nintendo Wii; U; ; 2047-7; es-Es)'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
	}

	/**
	 * iPhone
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectHTTP() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'User-Agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.3', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * iPhone - user-agent in random other header
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectHTTPOtherHeader() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'blahblahblah',
			'x-fish-header' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals(0, $reply['status']);
		$this->assertEquals('OK', $reply['message']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.3', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * iPhone 3GS (same UA as iPhone 3G, different x-local-hardwareinfo header)
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectHTTPHardwareInfo() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)',
			'x-local-hardwareinfo' => '320:480:100:100'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 3GS', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.2.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * iPhone 3G (same UA as iPhone 3GS, different x-local-hardwareinfo header)
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectHTTPHardwareInfoB() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)',
			'x-local-hardwareinfo' => '320:480:100:72'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 3G', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('4.2.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * iPhone - Crazy benchmark (eg from emulated desktop) with outdated OS
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectHTTPHardwareInfoC() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_0 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko)',
			'x-local-hardwareinfo' => '320:480:200:1200',
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 3G', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('2.0', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('en-gb', $reply['hd_specs']['general_language']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * Detection test user-agent has been encoded with plus for space.
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectHTTPPlusForSpace() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0+(Linux;+Android+5.1.1;+SM-J110M+Build/LMY48B;+wv)+AppleWebKit/537.36+(KHTML,+like+Gecko)+Version/4.0+Chrome/47.0.2526.100+Mobile+Safari/537.36',
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		$this->assertTrue($result);
		$this->assertEquals('Samsung', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('SM-J110M', $reply['hd_specs']['general_model']);
		$this->assertEquals('Android', $reply['hd_specs']['general_platform']);
		$this->assertEquals('5.1.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
	}
	
	/**
	 * iPhone 5s running Facebook 9.0 app (hence no general_browser set).
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectHTTPFBiOS() {

		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_1 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Mobile/11D201 [FBAN/FBIOS;FBAV/9.0.0.25.31;FBBV/2102024;FBDV/iPhone6,2;FBMD/iPhone;FBSN/iPhone OS;FBSV/7.1.1;FBSS/2; FBCR/vodafoneIE;FBID/phone;FBLC/en_US;FBOP/5]',
			'Accept-Language' => 'da, en-gb;q=0.8, en;q=0.7'
		);

		$result = $hd->deviceDetect($headers);
		$reply = $hd->getReply();
		//print_r($reply);
		$this->assertTrue($result);
		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 5S', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		$this->assertEquals('7.1.1', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('da', $reply['hd_specs']['general_language']);
		$this->assertEquals('Danish', $reply['hd_specs']['general_language_full']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
		$this->assertEquals('Facebook', $reply['hd_specs']['general_app']);
		$this->assertEquals('9.0', $reply['hd_specs']['general_app_version']);
		$this->assertEquals('', $reply['hd_specs']['general_browser']);
		$this->assertEquals('', $reply['hd_specs']['general_browser_version']);

		$this->assertArrayHasKey('display_pixel_ratio', $reply['hd_specs']);
		$this->assertArrayHasKey('display_ppi', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_min', $reply['hd_specs']);
		$this->assertArrayHasKey('benchmark_max', $reply['hd_specs']);
	}

	/**
	 * Samsung GT-I9500 Native - Note : Device shipped with Android 4.2.2, so this device has been updated.
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectBIAndroid() {
		$buildInfo = array (
			'ro.build.PDA' => 'I9500XXUFNE7',
			'ro.build.changelist' => '699287',
			'ro.build.characteristics' => 'phone',
			'ro.build.date.utc' => '1401287026',
			'ro.build.date' => 'Wed May 28 23:23:46 KST 2014',
			'ro.build.description' => 'ja3gxx-user 4.4.2 KOT49H I9500XXUFNE7 release-keys',
			'ro.build.display.id' => 'KOT49H.I9500XXUFNE7',
			'ro.build.fingerprint' => 'samsung/ja3gxx/ja3g:4.4.2/KOT49H/I9500XXUFNE7:user/release-keys',
			'ro.build.hidden_ver' => 'I9500XXUFNE7',
			'ro.build.host' => 'SWDD5723',
			'ro.build.id' => 'KOT49H',
			'ro.build.product' => 'ja3g',
			'ro.build.tags' => 'release-keys',
			'ro.build.type' => 'user',
			'ro.build.user' => 'dpi',
			'ro.build.version.codename' => 'REL',
			'ro.build.version.incremental' => 'I9500XXUFNE7',
			'ro.build.version.release' => '4.4.2',
			'ro.build.version.sdk' => '19',
			'ro.product.board' => 'universal5410',
			'ro.product.brand' => 'samsung',
			'ro.product.cpu.abi2' => 'armeabi',
			'ro.product.cpu.abi' => 'armeabi-v7a',
			'ro.product.device' => 'ja3g',
			'ro.product.locale.language' => 'en',
			'ro.product.locale.region' => 'GB',
			'ro.product.manufacturer' => 'samsung',
			'ro.product.model' => 'GT-I9500',
			'ro.product.name' => 'ja3gxx',
			'ro.product_ship' => 'true'
		);

		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		$this->assertEquals('Samsung', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('GT-I9500', $reply['hd_specs']['general_model']);
		$this->assertEquals('Android', $reply['hd_specs']['general_platform']);
		//$this->assertEquals('4.4.2', $reply['hd_specs']['general_platform_version']);
		$this->assertEmpty('', @$reply['hd_specs']['general_aliases'][0]);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
	}

	/**
	 * iPhone 4S Native
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectBIiOS() {
		$buildInfo = array (
			'utsname.machine' => 'iphone4,1',
			'utsname.brand' => 'Apple'
		);

		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		$this->assertEquals('Apple', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('iPhone 4S', $reply['hd_specs']['general_model']);
		$this->assertEquals('iOS', $reply['hd_specs']['general_platform']);
		// Note : Default shipped version in the absence of any version information
		$this->assertEquals('5.0', $reply['hd_specs']['general_platform_version']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
	}

	/**
	 * Windows Phone Native Nokia Lumia 1020
	 * @depends test_ultimate_community_fetchArchive
	 * @group community
	 **/
	function test_ultimate_community_deviceDetectWindowsPhone() {
		$buildInfo = array (
			'devicemanufacturer' => 'nokia',
			'devicename' => 'RM-875'
		);

		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$result = $hd->deviceDetect($buildInfo);
		$reply = $hd->getReply();

		$this->assertEquals('Nokia', $reply['hd_specs']['general_vendor']);
		$this->assertEquals('RM-875', $reply['hd_specs']['general_model']);
		$this->assertEquals('Windows Phone', $reply['hd_specs']['general_platform']);
		$this->assertEquals('', $reply['hd_specs']['general_type']);
		$this->assertEquals(0, $reply['hd_specs']['display_ppi']);
	}	
}
