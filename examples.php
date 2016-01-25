<?php
/*
** Handset Detection - API call examples
*/

//
ini_set('display_errors', 1);
ini_set('max_execution_time', 120);
ini_set('memory_limit', "512M");
error_reporting(E_ALL);

//$configFile = 'hdconfig.php';
$configFile = 'hd4UltimateConfig.php';

// Ensure config file is setup.
if (! file_exists($configFile)) {
	die('Config file not found');
}


include($configFile);
if (@$hdconfig['username'] == "your_api_username") {
	die('Please configure your username, secret and site_id');
}

require_once('HD4.php');
$hd = new HandsetDetection\HD4($configFile);

/// Vendors example : Get a list of all vendors
echo "<h1>Vendors</h1><p>";
if ($hd->deviceVendors()) {
	$data = $hd->getReply();
	print_r($data);
} else {
	print $hd->getError();
}
echo "</p>";

// Models example : Get a list of all models for a specific vendor
echo "<h1>Nokia Models</h1><p>";
if ($hd->deviceModels('Nokia')) {
	$data = $hd->getReply();
	print_r($data);
} else {
	print $hd->getError();
}
echo "</p>";

// View information for a specific handset
echo "<h1>Nokia N95 Properties</h1><p>";
if ($hd->deviceView('Nokia','N95')) {
	$data = $hd->getReply();
	print_r($data);
} else {
	print $hd->getError();
}
echo "</p>";

// What handset have this attribute ?
echo "<h1>Handsets with Network CDMA</h1><p>";
if ($hd->deviceWhatHas('network','CDMA')) {
	$data = $hd->getReply();
	print_r($data);
} else {
	print $hd->getError();
}
echo "</p>";

// ***************************** Detection Examples ********************************
// This is the most simple detection call - http headers are picked up automatically.
// You're probably using a normal browser so expect this to reply with NOTFOUND
echo "<h1>Simple Detection - Using your web browser standard headers (expect NotFound)</h1><p>";
if ($hd->deviceDetect()) {
	$tmp = $hd->getReply();
	print_r($tmp);
} else {
	print $hd->getError();
}
echo "</p>";

// This manually sets the headers that a Nokia N95 would set.
// Other agents you also might like to try
// Mozilla/5.0 (BlackBerry; U; BlackBerry 9300; es) AppleWebKit/534.8+ (KHTML, like Gecko) Version/6.0.0.534 Mobile Safari/534.8+
// Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 NokiaN95-3/20.2.011 Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413
// Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5
echo "<h1>Simple Detection - Setting Headers for an N95</h1><p>";
$hd->setDetectVar('user-agent','Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 NokiaN95-3/20.2.011 Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413');
$hd->setDetectVar('x-wap-profile','http://nds1.nds.nokia.com/uaprof/NN95-1r100.xml');
if ($hd->deviceDetect()) {
	$tmp = $hd->getReply();
	print_r($tmp);
} else {
	print $hd->getError();
}
echo "</p>";

// Query for some other information (remember the N95 headers are still set).
// Add detection options to query for additional reply information such as geoip information
// Note : We use the ipaddress to get the geoip location.
echo "<h1>Simple Detection - Passing a different ip address</h1><p>";
$hd->setDetectVar('ipaddress','64.34.165.180');
if ($hd->deviceDetect(array('options' => 'geoip,hd_specs'))) {
	$tmp = $hd->getReply();
	print_r($tmp);
} else {
	print $hd->getError();
}
echo "</p>";

// Ultimate customers can also download the ultimate database.
// Note  - Increase default timeout
echo "<h1>Archive Information</h1><p>";
$hd->setTimeout(500);
$time_start = _getmicrotime();
if ($hd->deviceFetchArchive()) {
	$data = $hd->getRawReply();
	echo "Downloaded ".strlen($data)." bytes";
} else {
	print $hd->getError();
	print $hd->getRawReply();
	print "\n";
}
$time_elapsed = _getmicrotime() - $time_start;
echo "<br/>Time elapsed " . $time_elapsed . "ms";
echo "</p>"; 

function _getmicrotime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}