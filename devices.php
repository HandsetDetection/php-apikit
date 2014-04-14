<?php
/*
** Handset Detection - devices.php - http://www.handsetdetection.com
** Examples of all the devices methods
*/

ini_set('display_errors', 1);
ini_set('max_execution_time', 120);
ini_set('memory_limit', "512M");
error_reporting(E_ALL);

require_once('hd3.php');
$hd = new HD3();	

echo "<h1>Vendors</h1><p>";

// Vendors example : Get a list of all vendors
if ($hd->deviceVendors()) {
	$data = $hd->getReply();
	print_r($data);
} else {
	print $hd->getError();
}

echo "</p><h1>Nokia Models</h1><p>";
// Models example : Get a list of all models for a specific vendor
if ($hd->deviceModels('Nokia')) {
	$data = $hd->getReply();
	print_r($data);
} else {
	print $hd->getError();
}

echo "</p><h1>Nokia N95 Properties</h1><p>";
// View information for a specific handset
if ($hd->deviceView('Nokia','N95')) {
	$data = $hd->getReply();
	print_r($data);
} else {
	print $hd->getError();
}

// What handset have this attribute ?
// Queryable fields are any hd_specs fields.
// NOTE: does not operate on legacy schema.
echo "</p><h1>Handsets with Network CDMA</h1><p>";
if ($hd->deviceWhatHas('network','CDMA')) {
	$data = $hd->getReply();
	print_r($data);
} else {
	print $hd->getError();
}


echo "</p>";

?>
