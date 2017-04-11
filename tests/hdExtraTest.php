<?php

error_reporting(E_ALL | E_STRICT);

// phpunit 6.0 backward compatibility with phpunit 4.0
if (!class_exists('\PHPUnit\Framework\TestCase') && class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class HDExtraTest extends \PHPUnit\Framework\TestCase {

	public function test_comparePlatformVersionsA() {
		$extra = new HandsetDetection\HDExtra();
		$result = $extra->comparePlatformVersions('9.0.1', '9.1');
		$this->assertLessThanOrEqual(-1, $result);
	}

	public function test_comparePlatformVersionsB() {
		$extra = new HandsetDetection\HDExtra();
		$result = $extra->comparePlatformVersions('9.0.1', '9.0.1');
		$this->assertEquals($result, 0);
	}

	public function test_comparePlatformVersionsC() {
		$extra = new HandsetDetection\HDExtra();
		$result = $extra->comparePlatformVersions('9.1', '9.0.1');
		$this->assertGreaterThanOrEqual(1, $result);
	}

	public function test_comparePlatformVersionsD() {
		$extra = new HandsetDetection\HDExtra();
		$result = $extra->comparePlatformVersions('4.2.1', '9.1');
		$this->assertLessThanOrEqual(-1, $result);
	}

	public function test_comparePlatformVersionsE() {
		$extra = new HandsetDetection\HDExtra();
		$result = $extra->comparePlatformVersions('4.2.1', '4.2.2');
		$this->assertLessThanOrEqual(-1, $result);
	}

	public function test_comparePlatformVersionsF() {
		$extra = new HandsetDetection\HDExtra();
		$result = $extra->comparePlatformVersions('4.2.1', '4.2.12');
		$this->assertLessThanOrEqual(-1, $result);
	}

	public function test_comparePlatformVersionsG() {
		$extra = new HandsetDetection\HDExtra();
		$result = $extra->comparePlatformVersions('4.1.1', '4.2.1');
		$this->assertLessThanOrEqual(-1, $result);
	}

	public function test_comparePlatformVersionsH() {
		$extra = new HandsetDetection\HDExtra();
		$result = $extra->comparePlatformVersions('4.0.21', '40.21');
		$this->assertLessThanOrEqual(-1, $result);
	}

	public function test_comparePlatformVersionsI() {
		$extra = new HandsetDetection\HDExtra();
		$result = $extra->comparePlatformVersions('4.1.1', '411');
		$this->assertLessThanOrEqual(-1, $result);
	}

	public function test_comparePlatformVersionsJ() {
		$extra = new HandsetDetection\HDExtra();
		$result = $extra->comparePlatformVersions('411', '4.1.1');
		$this->assertGreaterThanOrEqual(1, $result);
	}

	public function test_comparePlatformVersionsK() {
		$extra = new HandsetDetection\HDExtra();
		$result = $extra->comparePlatformVersions('Q7.1', 'Q7.2');
		$this->assertLessThanOrEqual(1, $result);
	}

	public function test_comparePlatformVersionsL() {
		$extra = new HandsetDetection\HDExtra();
		$result = $extra->comparePlatformVersions('Q5SK', 'Q7SK');
		$this->assertLessThanOrEqual(1, $result);
	}
}
