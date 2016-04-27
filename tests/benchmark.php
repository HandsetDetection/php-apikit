<?php

ini_set('display_errors', 1);
ini_set('max_execution_time', 120);
ini_set('memory_limit', "128M");
error_reporting(E_ALL | E_STRICT);

//$configFile = 'hdconfig.php';
$configFile = "../hd4UltimateConfig.php";
$dataFile = "benchmarkData.txt";

// Ensure config file present.
if (! file_exists($configFile))
	die('Config file not found');

// Ensure data file present
if (! file_exists($dataFile))
	die('Data file not found');

include($configFile);
if (@$hdconfig['username'] == "your_api_username")
	die('Please configure your username, secret and site_id');

require_once('../HD4.php');
$hd = new HandsetDetection\HD4($configFile);
//$hd->deviceFetchArchive();

class FileException extends Exception {};

class Benchmark {

	private $file;
	private $time_start;
	private $time;
	private $headers;
	var $count = 0;
	private $verbose = false;

	function __construct($filename) {
		try {
			$this->file = fopen($filename, "r");
		} catch(FileException $e) {
			echo "File error: ".$e->getMessage();
			exit(1);
		}

		while (!feof($this->file)) {
			$line_of_text = fgets($this->file);
			$this->headers[] = explode("|", $line_of_text);
		}
	}

	private function getmicrotime() {
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}

	function flyThrough($hd) {
		$hd->setup();
		$this->time_start = $this->getmicrotime();

		foreach($this->headers as $key => $device) {
			$hd->setDetectVar("User-Agent", $device[0]);
			$hd->setDetectVar("x-wap-profile", $device[1]);
			$result = $hd->deviceDetect();

			if ($this->verbose) {
				echo "<tr>";
				echo "<td>$this->count</td>";
				if ($result) {
					$reply = $hd->getReply();
					echo "<td>".@$reply['hd_specs']['general_vendor']."</td>";
					echo "<td>".@$reply['hd_specs']['general_model']."</td>";
					echo "<td>".@$reply['hd_specs']['general_platform']."</td>";
					echo "<td>".@$reply['hd_specs']['general_platform_version']."</td>";
					echo "<td>".@$reply['hd_specs']['general_browser']."</td>";
					echo "<td>".@$reply['hd_specs']['general_browser_version']."</td>";
					echo "<td>".print_r($device, true)."</td>";
				} else {
					echo "<td colspan='7'> Got nothing for : ".print_r($device, true)."</td>";
				}
				echo "</tr>";
			}
			$this->count++;
		}
		$this->time = $this->getmicrotime() - $this->time_start;
	}

	function header() {
		if ($this->verbose) {
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
	}

	function footer() {
		if ($this->verbose) {
			echo "</body></table>";
			echo "<div style=\"color:#f00;\">Test Complete</div>";
		}
		$dps = $this->count / round($this->time,4);
		$dps = round($dps, 0);
		echo "<h3>Elapsed time: ".round($this->time,4)."s, Total detections: $this->count, Detections per second: $dps</h3>\n\n";
	}

}
$test = new Benchmark($dataFile);
$test->header();
$test->flyThrough($hd);
$test->footer();
?>