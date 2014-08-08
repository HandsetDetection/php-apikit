<?php
require_once('hdconfig.php');
require_once('hd3.php');
/*
** run: phpunit --bootstrap test/autoload.php HD3Test
**
*/
class Hd3Test extends PHPUnit_Framework_TestCase {

	protected $hd3;

	protected $cloud_HD3;

	protected $ultimate_HD3;

	var $config = array ( 
		'username' => '203a2c5495',
		'secret' => '4Mcy7r7wDFdCDbg2',
		'site_id' => '50538',
		'use_local' => true
	);

	var $header1 = "Mozilla/5.0 (iPad; CPU OS 7_0_6 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B141 Safari/8536.25";

	var $header2 = "Opera/9.80 (J2ME/MIDP; Opera Mini/4.2.24721/26.984; U; en) Presto/2.8.119 Version/10.54";

	/**	  
	  *
	  * Setup HD3 class first
	  *	  
	  */
	protected function setUp() {
		$this->hd3 = new HD3($this->config);

		// set-up cloud set to false
		$hdconfig['username'] = $this->config["username"];
		$hdconfig['secret'] = $this->config["secret"];
		$hdconfig['site_id'] = $this->config["site_id"];
		$hdconfig['use_local'] = false;			// set this to false for cloud detection test
		$this->cloud_HD3 = new HD3($hdconfig);

		// set-up ultimate to true, local detection
		$hdconfig['username'] = $this->config["username"];
		$hdconfig['secret'] = $this->config["secret"];
		$hdconfig['site_id'] = $this->config["site_id"];
		$hdconfig['use_local'] = true;			// set this to true for ultimate local detection test
		$this->ultimate_HD3 = new HD3($hdconfig);

		$this->cloud_HD3->setDetectVar('User-Agent', $this->header1);
		$this->ultimate_HD3->setDetectVar('User-Agent', $this->header1);

		$this->cloud_HD3->siteDetect();		
		$this->ultimate_HD3->siteDetect();
	}	

	protected function tearDown() { }

	/**
	 *
	 * Pass Test Cloud and Ultimate detection Vendor
	 *
	 */
	public function testCompareCloudUltimateVendorPass() {										
		$this->assertEquals($this->cloud_HD3->getReply()["hd_specs"]["general_vendor"], $this->ultimate_HD3->getReply()["hd_specs"]["general_vendor"]);												
	}

	/**
	 *
	 * Pass Test Cloud and Ultimate detection Model
	 *
	 */
	public function testCompareCloudUltimateModelPass() {	
		$this->assertEquals($this->cloud_HD3->getReply()["hd_specs"]["general_model"], $this->ultimate_HD3->getReply()["hd_specs"]["general_model"]);		
	}

	/**
	 *
	 * Pass Test Cloud and Ultimate detection Platform
	 *
	 */
	public function testCompareCloudUltimatePlatformPass() {	
		$this->assertEquals($this->cloud_HD3->getReply()["hd_specs"]["general_platform"], $this->ultimate_HD3->getReply()["hd_specs"]["general_platform"]);		
	}
	
	/**
	 *
	 * Pass Test Cloud and Ultimate detection Platform Version
	 *
	 */
	public function testCompareCloudUltimatePlatformVersionPass() {	
		$this->assertEquals($this->cloud_HD3->getReply()["hd_specs"]["general_platform_version"], $this->ultimate_HD3->getReply()["hd_specs"]["general_platform_version"]);		
	}

	/**
	 *
	 * Pass Test Cloud and Ultimate detection Browser
	 *
	 */
	public function testCompareCloudUltimateBrowserPass() {	
		$this->assertEquals($this->cloud_HD3->getReply()["hd_specs"]["general_browser"], $this->ultimate_HD3->getReply()["hd_specs"]["general_browser"]);		

	}

	/**
	 *
	 * Pass Test Cloud and Ultimate detection Browser Version
	 *
	 */
	public function testCompareCloudUltimateBrowserVersionPass() {	
		$this->assertEquals($this->cloud_HD3->getReply()["hd_specs"]["general_browser_version"], $this->ultimate_HD3->getReply()["hd_specs"]["general_browser_version"]);		
	}

	/**
	 *
	 * Fail Test Cloud and Ultimate detection; putting all together
	 * You can change it to assertNotEquals to pass.
	 *
	 */
	public function testCompareCloudUltimateFail() {
		$this->ultimate_HD3->setDetectVar('User-Agent', $this->header2);
		$this->ultimate_HD3->siteDetect();
		$this->assertEquals($this->cloud_HD3->getReply()["hd_specs"]["general_vendor"], $this->ultimate_HD3->getReply()["hd_specs"]["general_vendor"]);												
		$this->assertEquals($this->cloud_HD3->getReply()["hd_specs"]["general_model"], $this->ultimate_HD3->getReply()["hd_specs"]["general_model"]);		
		$this->assertEquals($this->cloud_HD3->getReply()["hd_specs"]["general_platform"], $this->ultimate_HD3->getReply()["hd_specs"]["general_platform"]);		
		$this->assertEquals($this->cloud_HD3->getReply()["hd_specs"]["general_platform_version"], $this->ultimate_HD3->getReply()["hd_specs"]["general_platform_version"]);		
		$this->assertEquals($this->cloud_HD3->getReply()["hd_specs"]["general_browser"], $this->ultimate_HD3->getReply()["hd_specs"]["general_browser"]);		
		$this->assertEquals($this->cloud_HD3->getReply()["hd_specs"]["general_browser_version"], $this->ultimate_HD3->getReply()["hd_specs"]["general_browser_version"]);		
	}

	/**	  
	  *
	  * Test HD3 instances
	  *	  
	  */
	public function testHD3instanceOf() {
		$this->assertInstanceOf('HD3', $this->hd3);		
		$this->assertInternalType('object', $this->hd3);		
		$this->assertContainsOnlyInstancesOf('HD3', array(new HD3()));				
	}
	
	/**	  
	  *
	  * Test HD3 attributes
	  *	  
	  */
	public function testHD3Attributes() {
		$this->assertObjectHasAttribute('realm', new HD3);
		$this->assertClassHasAttribute('configFile', 'HD3');
		$this->assertClassHasStaticAttribute('rawreply', 'HD3');
	}
	
	/**	  
	  *
	  * Test default site detect
	  *	  
	  */
	public function testsiteDetect() {
		$this->assertFalse($this->hd3->siteDetect());
	} 
	
	/**	  
	  *
	  * Test device nokia site detect
	  *	  
	  */
	public function testnokiasiteDetect() {
		$this->hd3->setDetectVar('user-agent','Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 NokiaN95-3/20.2.011 Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413');
		$this->hd3->setDetectVar('x-wap-profile','http://nds1.nds.nokia.com/uaprof/NN95-1r100.xml');
		$this->hd3->siteDetect();
		$data = $this->hd3->getReply();
		$this->assertEquals("Nokia", $data['hd_specs']['general_vendor']);
		$this->assertEquals("Symbian", $data['hd_specs']['general_platform']);
		$features = $data["hd_specs"]["features"];
		$this->assertTrue(in_array("Email", $features));
		$this->assertTrue(in_array("Push to talk", $features));
		$this->assertTrue(in_array("PDF viewer", $features));
		$this->assertFalse(in_array("Gun", $features));
		$connectors = $data["hd_specs"]["connectors"];
		$this->assertTrue(in_array("TV Out", $connectors));
		$this->assertTrue(in_array("miniUSB", $connectors));
		$this->assertArrayHasKey('design_keyboard', $data['hd_specs']);
		$this->assertArrayHasKey('display_colors', $data['hd_specs']);
		$this->assertArrayHasKey('memory_slot', $data['hd_specs']);
		$this->assertContains('GSM1900', $data['hd_specs']['network']);				
		foreach(array('RealVideo 9', 'H.264', 'MPEG4', '3GPP') as $media) {
			$this->assertContains($media, $data['hd_specs']['media_videoplayback']);
		}		
	} 
	
	/**	  
	  *
	  * Test GEOIP site detect
	  *	  
	  */
	public function testgeoipsiteDetect() {
		$this->hd3->setDetectVar('ipaddress','64.34.165.180');
		$this->hd3->siteDetect(array('options' => 'geoip,hd_specs'));
		$data = $this->hd3->getReply();
		$this->assertEquals("38.9266", $data['geoip']['latitude']);
		$this->assertEquals("-77.3936", $data['geoip']['longitude']);
		$this->assertEquals("Virginia", $data['geoip']['region']);
		$this->assertEquals("ServerBeach", $data['geoip']['company']);
		$this->assertEquals("Herndon", $data['geoip']['city']);
		$this->assertEquals("US", $data['geoip']['countrycode']);
	}
	
	/**	  
	  *
	  * Test device vendors
	  *	  
	  */
	public function testdeviceVendors() {
		$this->hd3->deviceVendors();
		$data = $this->hd3->getReply();		
		$vendor = $data['vendor'];
		$this->assertContains("Apple", $vendor);
		$this->assertContains("Acer", $vendor);
		$this->assertContains("BlackBerry", $vendor);
		$this->assertContains("Cherry Mobile", $vendor);
		$this->assertEquals(0, $data['status']);
		$vendors = current($data);
		foreach(array('Just5', 'Nokia', 'Asus', 'Acer', 'AOC', 'Tecno', 'Optimus', 'MyPhone', 'Kyocera', 'Gateway') as $vendor) {
			$this->assertContains($vendor, $vendors);
		}
	}
	
	/**	  
	  *
	  * Test count the device vendor
	  *	  
	  */
	public function testcountdeviceVendors() {		
		$this->hd3->deviceVendors();
		$data = $this->hd3->getReply();		
		$this->assertGreaterThan(0, count($data['vendor']));
	}
	
	/**	  
	  *
	  * Test device model Samsung 
	  *	  
	  */
	public function testsamsungdeviceVendor() {
		$this->hd3->deviceVendors();
		$data = $this->hd3->getReply();
		$this->assertContains("Samsung", $data['vendor']);
	}
	
	/**	  
	  *
	  * Test device vendor Nokia 
	  *	  
	  */
	public function testsonydeviceVendor() {
		$this->hd3->deviceVendors();
		$data = $this->hd3->getReply();
		$this->assertContains("Sony", $data['vendor']);
	}
	
	/**	  
	  *
	  * Test device model Nokia 
	  *	  
	  */
	public function testnokiadeviceModel() {
		$this->hd3->deviceModels('Nokia');
		$data = $this->hd3->getReply();
		$model = $data['model'];
		$this->assertContains("Supernova", $model);
		$this->assertContains("Lumia 610 NFC", $model);
		$this->assertContains("3310i", $model);
		$this->assertContains("Asha 300", $model);
		$this->assertContains("Evolve", $model);		
	}
	
	/**	  
	  *
	  * Test device model Apple 
	  *	  
	  */
	public function testappledeviceModel() {
		$this->hd3->deviceModels('Apple');
		$models = current($this->hd3->getReply());
		foreach(array('iPhone 5S', 'iPod touch 3rd generation', 'iPad 3', 'iPad Air') as $model) {
			$this->assertContains($model, $models);
		}
	}
	
	/**	  
	  *
	  * Test Nokia N95 device view
	  *	  
	  */
	public function testnokiadeviceView() {
		$this->hd3->deviceView('Nokia','N95');
		$data = $this->hd3->getReply();
		$this->assertEquals("N95", $data['device']['general_model']);
		$this->assertEquals("9.2", $data['device']['general_platform_version']);
		$this->assertEquals("99 x 53 x 21", $data['device']['design_dimensions']);		
		$this->assertEquals("Symbian", $data['device']['general_platform']);
		$network = $data['device']['network'];
		$this->assertContains("Bluetooth 2.0", $network);
		$this->assertContains("HSDPA2100", $network);
		$this->assertContains("GPRS Class 10", $network);
		$this->assertContains("802.11g", $network);
		$this->assertContains("EDGE Class 32", $network);		
		$features = $data['device']['features'];
		$this->assertContains("Dual slide design", $features);
		$this->assertContains("PDF viewer", $features);
		$this->assertContains("Picture ID", $features);
		$this->assertContains("Multiple numbers per contact", $features);
		$this->assertContains("Accelerometer", $features);		
		$this->assertArrayHasKey('memory_internal', $data['device']);
		$this->assertArrayHasKey('connectors', $data['device']);
		$this->assertArrayHasKey('general_cpu', $data['device']);
		$this->assertArrayHasKey('display_colors', $data['device']);
		$this->assertArrayHasKey('memory_slot', $data['device']);
	}
	
	/**
	  * 	  
	  * Test network cdma devices
	  *	  
	  */
	public function testsanyodeviceWhatHas() {
		$this->hd3->deviceWhatHas('network','CDMA');
		$data = $this->hd3->getReply();
		$this->assertEquals("Sanyo", $data['devices'][5]['general_vendor']);
		$this->assertEquals("SCP-550CN", $data['devices'][5]['general_model']);
		$this->assertEquals("Pantech", $data['devices'][1896]['general_vendor']);
		$this->assertEquals("IM-A730S", $data['devices'][1896]['general_model']);
		$this->assertEquals("DoCoMo", $data['devices'][301]['general_vendor']);
		$this->assertEquals("N701iECO", $data['devices'][301]['general_model']);
		$this->assertEquals("Kyocera", $data['devices'][322]['general_vendor']);
		$this->assertEquals("K483JLC", $data['devices'][322]['general_model']);
		$dataVendors = current($data);
		$vendors = array();
		foreach($dataVendors as $vendor) {
			$vendors[] = $vendor['general_vendor'];
		}
		$vendors = array_unique($vendors);
		foreach(array('Huawei', 'UTStarcom', 'SmartQ', 'Cherry Mobile', 'Motorola',
			'HTC', 'WellcoM', 'ZTE') as $vendor) {
			$this->assertContains($vendor, $vendors);
		}
	}	
} 
