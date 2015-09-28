<?php

error_reporting(E_ALL | E_STRICT);
require_once('hdconfig.php');
require_once('HD4.php');


/*
** run: phpunit --bootstrap test/autoload.php HD4Test
**
*/
class HD4Test extends PHPUnit_Framework_TestCase {
	// Config files for the different test groups
	var $cloudConfig = 'hd4CloudConfig.php';
	var $cloudProxyConfig = 'hd4CloudProxyConfig.php';
	var $ultimateConfig = 'hd4UltimateConfig.php';
	
	var $headers = array(
		'h1' => array(
			'user-agent' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; GTB7.1; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; InfoPath.2; .NET CLR 3.5.30729; .NET4.0C; .NET CLR 3.0.30729; AskTbFWV5/5.12.2.16749; 978803803',
			'device' => 'GenericWindows PC',
			'platform' => 'WindowsVista',
			'browser' => 'Internet Explorer8.0',
		),
		'h2' => array(
			'user-agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22 ( .NET CLR 3.5.30729) Swapper 1.0.4',
			'device' => 'GenericWindows PC',
			'platform' => 'WindowsXP',
			'browser' => 'Firefox3.6',
		),
		'h3' => array(
			'user-agent' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; Sky Broadband; GTB7.1; SeekmoToolbar 4.8.4; Sky Broadband; Sky Broadband; AskTbBLPV5/5.9.1.14019)',
			'device' => 'GenericWindows PC',
			'platform' => 'WindowsXP',
			'browser' => 'Internet Explorer8.0',
		),
		'h4' => array(
			'user-agent' => 'Mozilla/5.0 (Linux; U; Android 2.2.2; en-us; SCH-M828C[3373773858] Build/FROYO) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
			'x-wap-profile' => 'http://www-ccpp.tcl-ta.com/files/ALCATEL_one_touch_908.xml',
			'device' => 'AlcatelOT-908',
			'platform' => 'Android2.2.2',
			'browser' => 'Android Webkit4.0'
		),
		'h5' => array(
			'user-agent' => 'Mozilla/5.0 (Linux; U; Android 2.2.2; en-us; SCH-M828C[3373773858] Build/FROYO) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
			'device' => 'SamsungSCH-M828C',
			'platform' => 'Android2.2.2',
			'browser' => 'Android Webkit4.0'
		),
		'h6' => array(
			'x-wap-profile' => 'http://www-ccpp.tcl-ta.com/files/ALCATEL_one_touch_908.xml',
			'device' => 'AlcatelOT-908',
			'platform' => 'Android2.2',
			'browser' => ''
		),
		'h7' => array(
			'user-agent' => 'Mozilla/5.0 (Linux; U; Android 2.3.3; es-es; GT-P1000N Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
			'x-wap-profile' => 'http://wap.samsungmobile.com/uaprof/GT-P1000.xml',
			'device' => 'SamsungGT-P1000',
			'platform' => 'Android2.3.3',
			'browser' => 'Android Webkit4.0'
		),
		'h8' => array(
			'user-agent' => 'Opera/9.80 (J2ME/MIDP; Opera Mini/5.21076/26.984; U; en) Presto/2.8.119 Version/10.54',
			'device' => 'GenericOpera Mini 5',
			'platform' => '',
			'browser' => 'Opera Mini5.2'
		),
		'h9' => array(
			'user-agent' => 'Opera/9.80 (iPhone; Opera Mini/6.1.15738/26.984; U; tr) Presto/2.8.119 Version/10.54',
			'device' => 'AppleiPhone',
			'platform' => 'iOS',
			'browser' => 'Opera Mini6.1'
		),
		'h10' => array(
			'user-agent' => 'Mozilla/5.0 (Linux; U; Android 2.1-update1; cs-cz; SonyEricssonX10i Build/2.1.B.0.1) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17',
			'device' => 'SonyEricssonX10I',
			'platform' => 'Android2.1.1',
			'browser' => 'Android Webkit4.0'
		)
	);

	var $fileDevice10 = '{"Device":{"_id":"10","hd_ops":{"is_generic":0,"stop_on_detect":0,"overlay_result_specs":0},"hd_specs":{"general_vendor":"Samsung","general_model":"SPH-A680","general_platform":"","general_platform_version":"","general_platform_version_max":"","general_browser":"","general_browser_version":"","general_image":"samsungsph-a680-1403617961-1.jpg","general_aliases":["Samsung VM-A680"],"general_eusar":"","general_battery":["Li-Ion 900 mAh"],"general_type":"Mobile","general_cpu":[],"design_formfactor":"Clamshell","design_dimensions":"83 x 46 x 24","design_weight":"96","design_antenna":"Internal","design_keyboard":"Numeric","design_softkeys":"2","design_sidekeys":[],"display_type":"TFT","display_color":"Yes","display_colors":"65K","display_size":"","display_x":"128","display_y":"160","display_ppi":"0","display_pixel_ratio":"","display_other":["Second External TFT"],"memory_internal":[],"memory_slot":[],"network":["CDMA800","CDMA1900","AMPS800"],"media_camera":["VGA","640x480"],"media_secondcamera":[],"media_videocapture":["Yes"],"media_videoplayback":[],"media_audio":[],"media_other":["Exposure control","White balance","Multi shot","Self-timer","LED Flash"],"features":["300 entries","Multiple numbers per contact","Picture ID","Ring ID","Calendar","Alarm","To-Do","Calculator","Stopwatch","SMS","T9","Computer sync","Polyphonic ringtones (32 voices)","Vibration","Voice dialing (Speaker independent)","Voice recording","TTY\/TDD","Games"],"connectors":["USB"],"benchmark_min":"0","benchmark_max":"0","general_app":"","general_app_version":"","general_app_category":"","general_language":""}}}';
	protected function setUp() {}	
	protected function tearDown() {}

	/**
	 * Test for runtime exception
     * @expectedException Exception
     */
	public function testUsernameRequired() {
		$config = array('username' => '');
		$hd4 = new HandsetDetection\HD4($config);
	}

	/**
	 * Test for runtime exception
     * @expectedException Exception
     */
	public function testSecretRequired() {
		$config = array('secret' => '');
		$hd4 = new HandsetDetection\HD4($config);
	}

	//
	public function testPassedConfig() {
		$config = array(
			'username' => 'jones',
			'secret' => 'jango',
			'site_id' => 78,
			'use_proxy' => true,
			'proxy_server' => '127.0.0.1',
			'proxy_port' => 8080,
			'proxy_user' => 'bob',
			'proxy_pass' => '123abc',
			'filesdir' => '/tmp'
		);
		$hd4 = new HandsetDetection\HD4($config);
		$this->assertEquals($hd4->getUsername(), 'jones');
		$this->assertEquals($hd4->getSecret(), 'jango');
		$this->assertEquals($hd4->getSiteId(), 78);
		$this->assertEquals($hd4->getUseProxy(), true);
		$this->assertEquals($hd4->getProxyServer(), '127.0.0.1');
		$this->assertEquals($hd4->getProxyPort(), 8080);
		$this->assertEquals($hd4->getProxyUser(), 'bob');
		$this->assertEquals($hd4->getProxyPass(), '123abc');
		$this->assertEquals($hd4->getFilesDir(), '/tmp');
	}

	//
	public function testDefaultFileConfig() {
		$hd4 = new HandsetDetection\HD4();
		$hd4->setUseProxy(false);
		$hd4->setUseLocal(false);

		$this->assertNotEmpty($hd4->getUsername());
		$this->assertNotEmpty($hd4->getSecret());
		$this->assertNotEmpty($hd4->getSiteId());
		$this->assertNotEmpty($hd4->getApiServer());
	}

	//
	public function testDefaultSetup() {
		$userAgent = 'Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 NokiaN95-3/20.2.011 Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413';
		$xWapProfile = 'http://nds1.nds.nokia.com/uaprof/NN95-1r100.xml';
		$ipAddress = '127.0.0.1';
		$cookie = 'yum';
		
		// Mockup for $_SERVER in PHP CLI
		$_SERVER['HTTP_USER_AGENT'] = $userAgent;
		$_SERVER['HTTP_X_WAP_PROFILE'] = $xWapProfile;
		$_SERVER['REMOTE_ADDR'] = $ipAddress;
		$_SERVER['Cookie'] = $cookie;

		$hd4 = new HandsetDetection\HD4();
		$hd4->setUseLocal(false);
		$vars = $hd4->getDetectRequest();
		$this->assertEquals($userAgent, $vars['USER-AGENT']);
		$this->assertEquals($xWapProfile, $vars['X-WAP-PROFILE']);
		$this->assertEquals($ipAddress, $vars['ipaddress']);
		$this->assertEquals(isset($vars['Cookie']), false);
	}
	
	//
	public function testManualSetup() {
		$userAgent = 'Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 NokiaN95-3/20.2.011 Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413';
		$xWapProfile = 'http://nds1.nds.nokia.com/uaprof/NN95-1r100.xml';

		$hd4 = new HandsetDetection\HD4();
		$hd4->setDetectVar('user-agent', $userAgent);
		$hd4->setDetectVar('x-wap-profile', $xWapProfile);
		$vars = $hd4->getDetectRequest();
		$this->assertEquals($userAgent, $vars['user-agent']);
		$this->assertEquals($xWapProfile, $vars['x-wap-profile']);
	}

	//
	public function testInvalidCredentials() {
		$config = array('username' => 'jones', 'secret' => 'jipple', 'use_local' => false, 'site_id' => 57);
		$hd4 = new HandsetDetection\HD4($config);
		
		$reply = $hd4->deviceVendors();
		$data = $hd4->getReply();
		$this->assertEquals($reply, false);
	}

	//
	public function testCloudDeviceVendors() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$reply = $hd->deviceVendors();
		$data = $hd->getReply();
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		$this->assertGreaterThan(1000, count($data['vendor']));
		$this->assertContains('Apple', $data['vendor']);
		$this->assertContains('Sony', $data['vendor']);
		$this->assertContains('Samsung', $data['vendor']);
	}

	//
	public function testCloudDeviceModels() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$reply = $hd->deviceModels('Nokia');
		$data = $hd->getReply();
		$this->assertEquals($reply, true);
		$this->assertGreaterThan(700, count($data['model']));
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
	}

	//
	public function testDeviceView() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
		$reply = $hd->deviceView('Nokia', 'N95');
		$data = $hd->getReply();
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		ksort($data['device']);
		ksort($this->devices['NokiaN95']);
		
		$this->assertEquals(strtolower(json_encode($this->devices['NokiaN95'])), strtolower(json_encode($data['device'])));
	}

	//
	public function testCloudDeviceWhatHas() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);
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

	//
	public function testCloudSiteDetect() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);

		foreach ($this->headers as $header) {
			$device = @$header['device'];
			$platform = @$header['platform'];
			$browser = @$header['browser'];
			$app = @$header['app'];
			unset($header['device']);
			unset($header['platform']);
			unset($header['browser']);
			unset($header['app']);

			$reply = $hd->siteDetect($header);
			$data = $hd->getReply();

			$deviceReply = $data['hd_specs']['general_vendor'].$data['hd_specs']['general_model'];
			$platformReply = $data['hd_specs']['general_platform'].$data['hd_specs']['general_platform_version'];
			$browserReply = $data['hd_specs']['general_browser'].$data['hd_specs']['general_browser_version'];
			$appReply = $data['hd_specs']['general_app'].$data['hd_specs']['general_app_version'];

			$this->assertEquals($reply, true);
			$this->assertEquals(0, $data['status']);
			$this->assertEquals('OK', $data['message']);
			$this->assertEquals($device, $deviceReply);
			$this->assertEquals($platform, $platformReply);
			$this->assertEquals($browser, $browserReply);
			$this->assertEquals($app, $appReply);
		}
	}

	// ********* Test Cloud Detection via Proxy *************
	

	//  Tests API Kit setup with proxy
	public function testCloudProxyConfig() {
		$hd4 = new HandsetDetection\HD4($this->cloudProxyConfig);

		$this->assertNotEmpty($hd4->getUseProxy());
		$this->assertNotEmpty($hd4->getProxyServer());
		$this->assertNotEmpty($hd4->getProxyPort());
		$this->assertNotEmpty($hd4->getProxyUser());
		$this->assertNotEmpty($hd4->getProxyPass());
	}
/**
	//
	public function testCloudProxyDeviceVendors() {
		$hd = new HandsetDetection\HD4($this->cloudProxyConfig);
		$reply = $hd->deviceVendors();
		$data = $hd->getReply();
		print_r($data);
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		$this->assertGreaterThan(1000, count($data['vendor']));
		$this->assertContains('Apple', $data['vendor']);
		$this->assertContains('Sony', $data['vendor']);
		$this->assertContains('Samsung', $data['vendor']);
	}

	//
	public function testCloudProxyDeviceModels() {
		$hd = new HandsetDetection\HD4($this->cloudProxyConfig);
		$reply = $hd->deviceModels('Nokia');
		$data = $hd->getReply();
		$this->assertEquals($reply, true);
		$this->assertGreaterThan(700, count($data['model']));
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
	}

	//
	public function testDeviceProxyView() {
		$hd = new HandsetDetection\HD4($this->cloudProxyConfig);
		$reply = $hd->deviceView('Nokia', 'N95');
		$data = $hd->getReply();
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		$this->assertEquals($this->devices['NokiaN95'], $data['device']);
	}

	//
	public function testCloudProxyDeviceWhatHas() {
		$hd = new HandsetDetection\HD4($this->cloudProxyConfig);
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

	//
	public function testCloudProxySiteDetect() {
		$hd = new HandsetDetection\HD4($this->cloudConfig);

		foreach ($this->headers as $header) {
			$match = $header['match'];
			unset($header['match']);
			$reply = $hd->siteDetect($header);
			$data = $hd->getReply();
			$this->assertEquals($reply, true);
			$this->assertEquals(0, $data['status']);
			$this->assertEquals('OK', $data['message']);
			$this->assertEquals($data['hd_specs']['general_type'], $data['class'], "hd_specs.general_type not matching class in reply ".json_encode($data));
			$this->_compareDevices($this->devices[$match], $data['hd_specs'], "Comparing ".json_encode($this->devices[$match])." with ".json_encode($data['hd_specs']));
		}
	}
**/	

	// Download the archive files - required for testUltimate* tests.
	public function testUltimateFetchArchive() {
		$hd4 = new HandsetDetection\HD4($this->ultimateConfig);

		$hd4->setTimeout(120);	
		$reply = $hd4->siteFetchArchive();
		$this->assertEquals($reply, true);

		$dir = $hd4->getFilesDir();
		$this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'hd40cache'. DIRECTORY_SEPARATOR . 'Device_10.json');
		$this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'hd40cache'. DIRECTORY_SEPARATOR . 'Extra_546.json');
		$this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'hd40cache'. DIRECTORY_SEPARATOR . 'Device_46142.json');
		$this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'hd40cache'. DIRECTORY_SEPARATOR . 'Extra_9.json');
		$this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'hd40cache'. DIRECTORY_SEPARATOR . 'Extra_102.json');
		$fileDevice10 = file_get_contents($dir . DIRECTORY_SEPARATOR . 'hd40cache'. DIRECTORY_SEPARATOR . 'Device_10.json');
		$this->assertEquals($fileDevice10, $this->fileDevice10);
		$this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'hd40cache'. DIRECTORY_SEPARATOR . 'user-agent0.json');
		$this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'hd40cache'. DIRECTORY_SEPARATOR . 'user-agent1.json');
		$this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'hd40cache'. DIRECTORY_SEPARATOR . 'user-agentplatform.json');
		$this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'hd40cache'. DIRECTORY_SEPARATOR . 'user-agentbrowser.json');
		$this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'hd40cache'. DIRECTORY_SEPARATOR . 'profile0.json');
	}

	//
	public function testUltimateDeviceVendors() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$reply = $hd->deviceVendors();
		$data = $hd->getReply();
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		$this->assertGreaterThan(1000, count($data['vendor']));
		$this->assertContains('Apple', $data['vendor']);
		$this->assertContains('Sony', $data['vendor']);
		$this->assertContains('Samsung', $data['vendor']);
	}
	
	//
	public function testUltimateDeviceModels() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$reply = $hd->deviceModels('Nokia');
		$data = $hd->getReply();
		$this->assertEquals($reply, true);
		$this->assertGreaterThan(700, count($data['model']));
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
	}

	//
	public function testUltimateDeviceView() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);
		$reply = $hd->deviceView('Nokia', 'N95');
		$data = $hd->getReply();
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		$this->assertEquals($this->devices['NokiaN95'], $data['device']);
	}

	//
	public function testUltimateDeviceWhatHas() {
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

	//
	public function testUltimateSiteDetect() {
		$hd = new HandsetDetection\HD4($this->ultimateConfig);

		foreach ($this->headers as $header) {
			$match = $header['match'];
			unset($header['match']);
			$reply = $hd->siteDetect($header);
			$data = $hd->getReply();
			$this->assertEquals($reply, true);
			$this->assertEquals(0, $data['status']);
			$this->assertEquals('OK', $data['message']);
			$this->assertEquals($data['hd_specs']['general_type'], $data['class'], "hd_specs.general_type not matching class in reply ".json_encode($data));
			$this->_compareDevices($this->devices[$match], $data['hd_specs'], "Comparing ".json_encode($this->devices[$match])." with ".json_encode($data['hd_specs']));
		}
	}
} 
