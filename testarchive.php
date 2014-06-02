<?php
/*
** Handset Detection - sites.php - http://www.handsetdetection.com
** Examples of all the sites methods
*/

ini_set('display_errors', 1);
ini_set('max_execution_time', 1200);
ini_set('memory_limit', "512M");
error_reporting(E_ALL);

require_once('hd3.php');
$hd3 = new HD3();

// If your site has access to download our device specs database only.
// Note  - Increase default timeout
echo "</p><h1>Archive Information</h1><p>";
$hd3->setTimeout(500);
$time_start = getmicrotime();
if ($hd3->siteFetchArchive()) {
	$data = $hd3->getRawReply();
	echo "Downloaded ".strlen($data)." bytes";
} else {
	print $hd3->getError();
}
$time_elapsed = getmicrotime() - $time_start;
echo "<br/>Time elapsed " . $time_elapsed . "ms";
echo "</p>"; 

function getmicrotime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

?>