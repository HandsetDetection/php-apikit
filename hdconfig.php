<?php
/**
* Handset Detection v4.0 API Kit configuration file
*
* Config file options
*
* username : your api username * Required : From 'Dashboard (Manage Sites Section) > Site Settings > Cloud ' Page
* secret : your api secret * Required : From 'Dashboard (Manage Sites Section) > Site Settings > Cloud ' Page
* site_id : the site_id to be used for these queries * Required : From 'Dashboard (Manage Sites Section) > Site Settings > Cloud ' Page
* filesdir : defaults to the installation directory - when set any downloaded files and any file caches will be setup here eg /tmp
* use_local : set to true if you're using an Ultimate licence (download the data set and perform detections locally)
* api_server : defaults to api.handsetdetection.com - Use a different hostname to force connections to a different serevr pool
* debug : defaults to false - Set to true to log interesting messages to syslog
* retries : default 3 - number of times a connection is retried
* use_proxy : set to true to connect through a proxy server
* proxy_server : address of proxy server
* proxy_port : port of proxy server
* proxy_user : if proxy server requires a username
* proxy_pass : if proxy server requires a password
* log_unknown : Anonymously log unclassified http headers - Fast (uses UDP) places no strain on the system.
* log_generics : Anonymously log generic replies (so we can do a better job picking them up) - Fast (uses UDP) places no strain on the system.
**/

$hdconfig['username'] = "your_api_username";
$hdconfig['secret'] = "your_api_secret";
$hdconfig['site_id'] = "your_api_siteId";
$hdconfig['use_local'] = false;
$hdconfig['filesdir'] = '/tmp';
$hdconfig['debug'] = false;
$hdconfig['api_server'] = 'api.handsetdetection.com';
$hdconfig['cache_requests'] = false;
$hdconfig['geoip'] = true;
$hdconfig['timeout'] = 10;
$hdconfig['use_proxy'] = false;
$hdconfig['proxy_server'] = '';
$hdconfig['proxy_port'] = '';
$hdconfig['proxy_user'] = '';
$hdconfig['proxy_pass'] = '';
$hdconfig['retries'] = 3;
$hdconfig['log_unknown'] = true;