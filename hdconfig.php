<?php
/**
* Handset Detection v3.2 API Kit configuration file
* This file configures your Handset Detection PHP API Kit with your details.
*
* 1) Copy and paste this text into a file called hdconfig.php in your apikit directory.
* 2) Alter any other option sfor your local setup
*
* See http://www.handsetdetection.com/resources for a full list of api web service calls
*
* config file options 
*
* username : your api username * Required
* secret : your api secret * Required
* site_id : the site_id to be used for these queries * Required
* filesdir : defaults to the installation directory - when set any downloaded files and any file caches will be setup here eg /tmp
* use_local : set to true if you're using an Ultimate licence (download the data set and perform detections locally)
* api_server : defaults to api.handsetdetection.com - Use a different hostname to force connections to a different cluster
* debug : defaults to false - Set to true to log interesting messages to syslog
* retries : default 3 - number of times a connection is retried
* use_proxy : set to true to connect through a proxy server
* proxy_server : address of proxy server
* proxy_port : port of proxy server
* proxy_user : if proxy server requires a username
* proxy_pass : if proxy server requires a password
* non_mobile : a regular expressions of useragent fragments that indicate the device is definetly not mobile (mostly search engines & toolbars)
* - the default is "/^Feedfetcher|^FAST|^gsa_crawler|^Crawler|^goroam|^GameTracker|^http:\/\/|^Lynx|^Link|^LegalX|libwww|^LWP::Simple|FunWebProducts|^Nambu|^WordPress|^yacybot|^YahooFeedSeeker|^Yandex|^MovableType|^Baiduspider|SpamBlockerUtility|AOLBuild|Link Checker|Media Center|Creative ZENcast|GoogleToolbar|MEGAUPLOAD|Alexa Toolbar|^User-Agent|SIMBAR|Wazzup|PeoplePal|GTB5|Dealio Toolbar|Zango|MathPlayer|Hotbar|Comcast Install|WebMoney Advisor|OfficeLiveConnector|IEMB3|GTB6|Avant Browser|America Online Browser|SearchSystem|WinTSI|FBSMTWB|NET_lghpset/" 
**/

$hdconfig['username']="**your api username**";					# Your API Username
$hdconfig['secret']="**your api secret**";				# Your API Secret
$hdconfig['site_id']="**your api siteId**";						# Your Site ID
$hdconfig['use_local']=true;
$hdconfig['filesdir']="";							# Ultimate customer cache directory & downloaded files go here.
$hdconfig['debug']=false;							# Set to true to log debug messages to syslog.
$hdconfig['api_server'] = 'localhost';
?>