<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 120);
ini_set('memory_limit', "128M");
error_reporting(E_ALL);
require_once('hd3.php');

class FileException extends Exception {};

class TestDevices {
	
	private $file;
		
	private $time_start;
	
	private $time;
	
	var $count = 0;
			
	function __construct($filename) {
		try {
			$this->file = fopen($filename, "r");
		} catch(FileException $e) {
			echo "File error: ".$e->getMessage();
			exit(1);
		}
	}
	
	private function getmicrotime(){
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}
	
	function multipleInstance() {		
		$this->header();
		$this->time_start = $this->getmicrotime();		
		while(!feof($this->file)) {			
			$line_of_text = fgets($this->file);
			$headers = explode("|", $line_of_text);
			for($i = 0; $i<10; $i++) {
				$hd3 = new HD3();
				$hd3->setup();
				$hd3->setDetectVar("User-Agent", $headers[0]);
				$hd3->setDetectVar('x-wap-profile', $headers[1]);	
				if($hd3->siteDetect()) {					
					$reply = $hd3->getReply();
					echo "<tr><td>$this->count</td><td>".@$reply['hd_specs']['general_vendor']."</td><td>".@$reply['hd_specs']['general_model']."</td><td>".
							@$reply['hd_specs']['general_platform']."</td><td>".@$reply['hd_specs']['general_platform_version']."</td><td>".
							@$reply['hd_specs']['general_browser']."</td><td>".@$reply['hd_specs']['general_browser_version']."</td><td>".print_r($headers, true)."</td></tr>";
				} else {
					$reply = $hd3->getReply();
					echo "<tr><td>$this->count</td><td colspan='7'> Got nothing for $this->count headers : ".print_r($headers, true)."</td></tr>";					
				}
				$this->count++;				
			}
		}
		$this->time = $this->getmicrotime() - $this->time_start;
		$this->footer();
	}
	
	function singleInstance() {
		$hd3 = new HD3();
		$this->header();
		$this->time_start = $this->getmicrotime();		
		while(!feof($this->file)) {			
			$line_of_text = fgets($this->file);
			$headers = explode("|", $line_of_text);
			for($i = 0; $i<10; $i++) {
				$hd3->setup();
				$hd3->setDetectVar("User-Agent", $headers[0]);
				$hd3->setDetectVar('x-wap-profile', $headers[1]);	
				if($hd3->siteDetect()) {					
					$reply = $hd3->getReply();
					echo "<tr><td>$this->count</td><td>".@$reply['hd_specs']['general_vendor']."</td><td>".@$reply['hd_specs']['general_model']."</td><td>".
							@$reply['hd_specs']['general_platform']."</td><td>".@$reply['hd_specs']['general_platform_version']."</td><td>".
							@$reply['hd_specs']['general_browser']."</td><td>".@$reply['hd_specs']['general_browser_version']."</td><td>".print_r($headers, true)."</td></tr>";
				} else {
					$reply = $hd3->getReply();
					echo "<tr><td>$this->count</td><td colspan='7'> Got nothing for : ".print_r($headers, true)."</td></tr>";					
				}
				$this->count++;				
			}
		}
		$this->time = $this->getmicrotime() - $this->time_start;
		$this->footer();
	}
	
	function header() {
		$str = <<<TAG
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Test headers</title>
	<style type="text/css">
		body { background-color: lemonchiffon; }
		table { background-color: #fff; }
		tr:nth-child(even) {background: #f1f1f1; }
		tr:nth-child(odd) {background: #FFF; }		
	</style>
</head>
<body>
TAG;
		echo $str;
		echo "<table style='font-size:12px'><tr><th>Count</th><th>Vendor</th><th>Model</th><th>Platform</th><th>Platform Version</th><th>Browser</th><th>Browser Version</th><th>HTTP Headers</th></tr>";
	}
	
	function footer() { 
		echo "</body></table>"; 					
		echo "<div style=\"color:#f00;\">Test Complete</div>";
		$dps = $this->count / round($this->time,4);
		$dps = round($dps, 0);
		echo "<h3>Elapsed time: ".round($this->time,4)."s, Total detections: $this->count, Detections per second: $dps</h3>";		
	}
	
}
$test = new TestDevices("headers.txt");
$test->singleInstance();

?>