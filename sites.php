<?php
/*
** Handset Detection - sites.php - http://www.handsetdetection.com
** Examples of all the sites methods
*/

ini_set('display_errors', 1);
ini_set('max_execution_time', 1200);
ini_set('memory_limit', "768M");
error_reporting(E_ALL);

require_once('hd3.php');
$hd3 = new HD3();

echo "<h1>Simple Detection - Using your web browser standard headers (expect NotFound)</h1><p>";
// This is the most simple detection call - http headers are picked up automatically.
// You're probably using a normal browser so expect this to reply with NOTFOUND
if ($hd3->siteDetect()) {
	$tmp = $hd3->getReply();
	print_r($tmp);
} else {
	print $hd3->getError();	
}
echo "</p>";

echo "<h1>Simple Detection - Setting Headers for an N95</h1><p>";
// This manually sets the headers that a Nokia N95 would set.
// Other agents you also might like to try 
// Mozilla/5.0 (BlackBerry; U; BlackBerry 9300; es) AppleWebKit/534.8+ (KHTML, like Gecko) Version/6.0.0.534 Mobile Safari/534.8+
// Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 NokiaN95-3/20.2.011 Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413
// Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5
$hd3->setDetectVar('user-agent','Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 NokiaN95-3/20.2.011 Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413');
$hd3->setDetectVar('x-wap-profile','http://nds1.nds.nokia.com/uaprof/NN95-1r100.xml');
if ($hd3->siteDetect()) {
	$tmp = $hd3->getReply();
	print_r($tmp);
} else {
	print $hd3->getError();	
}
echo "</p>";

echo "<h1>Simple Detection - Passing a different ip address</h1><p>";
// Query for some other information (remember the N95 headers are still set).
// Add detection options to query for additional reply information such as geoip information
// Note : We use the ipaddress to get the geoip location.
$hd3->setDetectVar('ipaddress','64.34.165.180');
if ($hd3->siteDetect(array('options' => 'geoip,hd_specs'))) {
	$tmp = $hd3->getReply();
	print_r($tmp);
} else {
	print $hd3->getError();	
}
echo "</p>";

echo "<h1>Simple Detection - Getting legacy options</h1><p>";
// Query for some other information (remember the N95 headers are still set).
// Add the legacy option to get legacy information from the old schema.
if ($hd3->siteDetect(array('options' => 'geoip,legacy,product_info,display'))) {
	$tmp = $hd3->getReply();
	print_r($tmp);
} else {
	print $hd3->getError();	
}
echo "</p>";

echo "<h1>Simple Detection - Getting legacy options and current information</h1><p>";
// Query for some other information (remember the N95 headers are still set).
// Add the legacy option to get legacy information from the old schema.
if ($hd3->siteDetect(array('options' => 'hd_specs,legacy,product_info,display'))) {
	$tmp = $hd3->getReply();
	print_r($tmp);
} else {
	print $hd3->getError();	
}
echo "</p>";

// **** Examples for other Site calls ****
// Note : you can also setup your own config array and pass it to the constructor
// Follow the format of the hdconfig.php file & call $hd = new HD3($cfg);
$hd = new HD3();	

$site_id = $hd->getSiteId();
$new_site_id = 0;

echo "<h1>Add Site</h1><p>";
$data['name'] = 'My Site';			// Site name - a convenience thing.
$data['url'] = 'www.mydomain.com';	// The standard domain name of your website
$data['active'] = 1;				// Is this site profile active & detecting ? (1 for yes, 0 for no).
$data['user_id'] = 0; 				// Your user_id or the id of a user your account has created.
$data['smartredir'] = 1;			// Smart redirection - You probably want this on.
$data['rules'] = array(
	array('title' => 'Redirect All Mobiles',
		  'conditionsall' => array(
			array('name' => 'width', 'test' => 'ltequals', 'value' => '480'),
			array('name' => 'height', 'test' => 'lt', 'value' => '320')
		  ),
		  'conditionsany' => array(),
		  'action' => 'www.mydomain.com/mobi'),
	array('title' => 'A Better All Mobiles rule',
		  'conditionsany' => array(
			array('name' => 'ismobile', 'test' => 'is', 'value' => 'true'),
		  ),
		  'conditionsall' => array(),
		  'action' => 'www.mydomain.mobi'),
	array('title' => 'Redirect All Tablets',
		  'conditionsall' => array(),
		  'conditionsany' => array(
			array('name' => 'istablet', 'test' => 'is', 'value' => 'true'),
		   ),
		  'action' => 'www.mydomain.com/tablet'
		 )
	);

if ($hd->siteAdd($data)) {
	$new = $hd->getReply();
	if (isset($new['id']))
		$new_site_id = $new['id'];
} else {
	print $hd->getError();
}

echo "</p><h1>View Site</h1><p>";
if ($hd->siteView()) {
	$tmp = $hd->getReply();
	print_r($tmp);
} else {
	print $hd->getError();	
}

echo "</p><h1>Edit Site</h1><p>";
$data['active'] = 0;				// Pause site
$data['name'] = 'New Site Name';	// Change name
if ($hd->siteEdit($data)) {
	$tmp = $hd->getReply();
	print_r($tmp);
} else {
	print $hd->getError();	
}

// If your site has access to download our device specs database only.
echo "</p><h1>Detection Information</h1><p>";
$hd3->setTimeout(120);
if ($hd3->siteFetchTrees()) {
	$data = $hd3->getRawReply();
	echo "Downloaded ".strlen($data)." bytes";
} else {
	print $hd3->getError();
}
echo "</p>";

// If your site has access to download our device specs database only.
echo "</p><h1>Handset Information</h1><p>";
if ($hd3->siteFetchSpecs()) {
	$data = $hd3->getRawReply();
	echo "Downloaded ".strlen($data)." bytes";
} else {
	print $hd3->getError();
}
echo "</p>";

// If your site has access to download our device specs database only.
// Note  - Increase default timeout
echo "</p><h1>Archive Information</h1><p>";
$hd3->setTimeout(120);
if ($hd3->siteFetchArchive()) {
	$data = $hd3->getRawReply();
	echo "Downloaded ".strlen($data)." bytes";
} else {
	print $hd3->getError();
}
$hd3->setTimeout(60);
echo "</p>";


echo "</p><h1>Delete Site</h1><p>";
if ($hd->siteDelete(21)) {
	$tmp = $hd->getReply();
	print_r($tmp);
} else {
	print $hd->getError();	
}

echo "</p>";


?>