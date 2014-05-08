<?php
require_once('hd3.php');
class Hd3Test extends PHPUnit_Framework_TestCase {
	
	protected $hd3;

	var $config = array ( 
		'username' => 'your_api_username',
		'secret' => 'your_api_secret',
		'site_id' => 'your_site_id',
		'use_local' => false
	);

	protected function setUp() {
		$this->hd3 = new HD3($this->config);	
	}	

	protected function tearDown() { }
	
	public function testsiteDetect() {
		$this->assertFalse($this->hd3->siteDetect());
	} 

	public function testnokiasiteDetect() {
		$this->hd3->setDetectVar('user-agent','Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 NokiaN95-3/20.2.011 Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413');
		$this->hd3->setDetectVar('x-wap-profile','http://nds1.nds.nokia.com/uaprof/NN95-1r100.xml');
		$this->hd3->siteDetect();
		$data = $this->hd3->getReply();
		$this->assertEquals("Nokia", $data['hd_specs']['general_vendor']);
		$this->assertEquals("Symbian", $data['hd_specs']['general_platform']);
	}

	public function testgeoipsiteDetect() {
		$this->hd3->setDetectVar('ipaddress','64.34.165.180');
		$this->hd3->siteDetect(array('options' => 'geoip,hd_specs'));
		$data = $this->hd3->getReply();
		$this->assertEquals("38.9266", $data['geoip']['latitude']);
		$this->assertEquals("US", $data['geoip']['countrycode']);
	}

	public function testcountdeviceVendors() {		
		$this->hd3->deviceVendors();
		$data = $this->hd3->getReply();		
		$this->assertGreaterThan(0, count($data['vendor']));
	}

	public function testsamsungdeviceVendors() {
		$this->hd3->deviceVendors();
		$data = $this->hd3->getReply();
		$this->assertContains("Samsung", $data['vendor']);
	}

	public function testburgerdeviceVendors() {
		$this->hd3->deviceVendors();
		$data = $this->hd3->getReply();
		$this->assertContains("Sony", $data['vendor']);
	}

	public function testnokiasupernovadeviceModels() {
		$this->hd3->deviceModels('Nokia');
		$data = $this->hd3->getReply();
		$this->assertContains("Supernova", $data['model']);
	}

	public function testnokiamodeldeviceView() {
		$this->hd3->deviceView('Nokia','N95');
		$data = $this->hd3->getReply();
		$this->assertEquals("N95", $data['device']['general_model']);
	}

	public function testnokiaplatformdeviceView() {
		$this->hd3->deviceView('Nokia','N95');
		$data = $this->hd3->getReply();
		$this->assertEquals("Symbian", $data['device']['general_platform']);
	}
	
	public function testnokiafeaturesdeviceView() {
		$this->hd3->deviceView('Nokia','N95');
		$data = $this->hd3->getReply();
		$this->assertContains("Email", $data['device']['features']);
	}
	
	public function testsanyodeviceWhatHas() {
		$this->hd3->deviceWhatHas('network','CDMA');
		$data = $this->hd3->getReply();
		$this->assertEquals("Sanyo", $data['devices'][5]['general_vendor']);
		$this->assertEquals("SCP-550CN", $data['devices'][5]['general_model']);
	}	
} 
?>
