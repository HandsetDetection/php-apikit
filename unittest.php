<?php

error_reporting(E_ALL | E_STRICT);

require_once('hdconfig.php');
require_once('hd3.php');
/*
** run: phpunit --bootstrap test/autoload.php HD3Test
**
*/
class Hd3Test extends PHPUnit_Framework_TestCase {
	
	var $devices = array(
		'NokiaN95' => array(
			'general_vendor' => 'Nokia',
            'general_model' => 'N95',
            'general_platform' => 'Symbian',
            'general_platform_version' => '9.2',
            'general_browser' => '',
            'general_browser_version' => '',
            'general_image' => 'nokian95-1403496370-0.gif',
            'general_aliases' => array(),
            'general_eusar' => '0.50',
            'general_battery' => array('Li-Ion 950 mAh','BL-5F'),
            'general_type' => 'Mobile',
            'general_cpu' => array('Dual ARM 11','332Mhz'),
            'design_formfactor' => 'Dual Slide',
            'design_dimensions' => '99 x 53 x 21',
            'design_weight' => '120',
            'design_antenna' => 'Internal',
            'design_keyboard' => 'Numeric',
            'design_softkeys' => 2,
            'design_sidekeys' => array('Volume','Camera'),
            'display_type' => 'TFT',
            'display_color' => 'Yes',
            'display_colors' => '16M',
            'display_size' => '2.6"',
            'display_x' => '240',
            'display_y' => '320',
            'display_other' => array(),
            'memory_internal' => array('160MB','64MB RAM','256MB ROM'),
            'memory_slot' => array('microSD', '8GB', '128MB'),
            'network' => array('GSM850','GSM900','GSM1800','GSM1900','UMTS2100','HSDPA2100','Infrared port','Bluetooth 2.0','802.11b','802.11g','GPRS Class 10','EDGE Class 32'),
            'media_camera' => array('5MP','2592x1944'),
            'media_secondcamera' => array('QVGA'),
            'media_videocapture' => array('VGA@30fps'),
            'media_videoplayback' => array('MPEG4','H.263','H.264','3GPP','RealVideo 8','RealVideo 9','RealVideo 10'),
            'media_audio' => array('MP3','AAC','AAC+','eAAC+','WMA'),
            'media_other' => array('Auto focus','Video stabilizer','Video calling','Carl Zeiss optics','LED Flash'),
            'features' => array('Unlimited entries','Multiple numbers per contact','Picture ID','Ring ID','Calendar','Alarm','To-Do','Document viewer',
                    'Calculator','Notes','UPnP','Computer sync','VoIP','Music ringtones (MP3)','Vibration','Phone profiles','Speakerphone',
                    'Accelerometer','Voice dialing','Voice commands','Voice recording','Push-to-Talk','SMS','MMS','Email','Instant Messaging',
					'Stereo FM radio','Visual radio','Dual slide design','Organizer','Word viewer','Excel viewer','PowerPoint viewer','PDF viewer',
					'Predictive text input','Push to talk','Voice memo','Games'),
            'connectors' => array('USB','miniUSB','3.5mm Headphone','TV Out')
        ),
		
		'AlcatelOT-908222' => array(
            'general_vendor' => 'Alcatel',
            'general_model' => 'OT-908',
            'general_platform' => 'Android',
            'general_platform_version' => '2.2.2',
            'general_browser' => 'Android Webkit',
            'general_browser_version' => '4.0',
            'general_image' => '',
            'general_aliases' => array('Alcatel One Touch 908'),
            'general_eusar' => '',
            'general_battery' => array('Li-Ion 1300 mAh'),
            'general_type' => 'Mobile',
            'general_cpu' => array('600Mhz'),
            'design_formfactor' => 'Bar',
            'design_dimensions' => '110 x 57.4 x 12.4',
            'design_weight' => '120',
            'design_antenna' => 'Internal',
            'design_keyboard' => 'Screen',
            'design_softkeys' => '',
            'design_sidekeys' => array('Lock/Unlock', 'Volume'),
            'display_type' => 'TFT',
            'display_color' => 'Yes',
            'display_colors' => '262K',
            'display_size' => '2.8"',
            'display_x' => '240',
            'display_y' => '320',
            'display_other' => array('Capacitive','Touch','Multitouch'),
            'memory_internal' => array('150MB'),
            'memory_slot' => array('microSD','microSDHC','32GB','2GB'),
            'network' => array('GSM850','GSM900','GSM1800','GSM1900','UMTS900','UMTS2100','HSDPA900','HSDPA2100','Bluetooth 3.0','802.11b','802.11g','802.11n','GPRS Class 12','EDGE Class 12'),
            'media_camera' => array('2MP','1600x1200'),
            'media_secondcamera' => array(),
			'media_videocapture' => array('Yes'),
            'media_videoplayback' => array('MPEG4','H.263','H.264'),
            'media_audio' => array('MP3','AAC','AAC+','WMA'),
            'media_other' => array('Geo-tagging'),
            'features' => array(
                    'Unlimited entries','Caller groups','Multiple numbers per contact','Search by both first and last name','Picture ID',
                    'Ring ID','Calendar','Alarm','Calculator','Computer sync','OTA sync','Music ringtones (MP3)','Polyphonic ringtones (64 voices)',
                    'Vibration','Flight mode','Silent mode','Speakerphone','Accelerometer','Compass','Voice recording','SMS',
                    'MMS','Email','Push Email','IM','Stereo FM radio with RDS','SNS integration','Google Search','Maps','Gmail','YouTube',
					'Google Talk','Picasa integration','Organizer','Document viewer','Voice memo','Voice dialing','Predictive text input','Games'),
            'connectors' => array('USB 2.0','microUSB','3.5mm Headphone')
        ),

		'AlcatelOT-90822' => array(
            'general_vendor' => 'Alcatel',
            'general_model' => 'OT-908',
            'general_platform' => 'Android',
            'general_platform_version' => '2.2',
            'general_browser' => '',
            'general_browser_version' => '',
            'general_image' => '',
            'general_aliases' => array('Alcatel One Touch 908'),
            'general_eusar' => '',
            'general_battery' => array('Li-Ion 1300 mAh'),
            'general_type' => 'Mobile',
            'general_cpu' => array('600Mhz'),
            'design_formfactor' => 'Bar',
            'design_dimensions' => '110 x 57.4 x 12.4',
            'design_weight' => '120',
            'design_antenna' => 'Internal',
            'design_keyboard' => 'Screen',
            'design_softkeys' => '',
            'design_sidekeys' => array('Lock/Unlock', 'Volume'),
            'display_type' => 'TFT',
            'display_color' => 'Yes',
            'display_colors' => '262K',
            'display_size' => '2.8"',
            'display_x' => '240',
            'display_y' => '320',
            'display_other' => array('Capacitive','Touch','Multitouch'),
            'memory_internal' => array('150MB'),
            'memory_slot' => array('microSD','microSDHC','32GB','2GB'),
            'network' => array('GSM850','GSM900','GSM1800','GSM1900','UMTS900','UMTS2100','HSDPA900','HSDPA2100','Bluetooth 3.0','802.11b','802.11g','802.11n','GPRS Class 12','EDGE Class 12'),
            'media_camera' => array('2MP','1600x1200'),
            'media_secondcamera' => array(),
			'media_videocapture' => array('Yes'),
            'media_videoplayback' => array('MPEG4','H.263','H.264'),
            'media_audio' => array('MP3','AAC','AAC+','WMA'),
            'media_other' => array('Geo-tagging'),
            'features' => array(
                    'Unlimited entries','Caller groups','Multiple numbers per contact','Search by both first and last name','Picture ID',
                    'Ring ID','Calendar','Alarm','Calculator','Computer sync','OTA sync','Music ringtones (MP3)','Polyphonic ringtones (64 voices)',
                    'Vibration','Flight mode','Silent mode','Speakerphone','Accelerometer','Compass','Voice recording','SMS',
                    'MMS','Email','Push Email','IM','Stereo FM radio with RDS','SNS integration','Google Search','Maps','Gmail','YouTube',
					'Google Talk','Picasa integration','Organizer','Document viewer','Voice memo','Voice dialing','Predictive text input','Games'),
            'connectors' => array('USB 2.0','microUSB','3.5mm Headphone')
        ),
		
		'SamsungSCH-M828C' => array(
            'general_vendor' => 'Samsung',
            'general_model' => 'SCH-M828C',
            'general_platform' => 'Android',
            'general_platform_version' => '2.2.2',
            'general_browser' => 'Android Webkit',
            'general_browser_version' => '4.0',
            'general_image' => 'samsungsch-m828c-1355919519-0.jpg',
            'general_aliases' => array('Samsung Galaxy Prevail', 'Samsung Galaxy Precedent'),
            'general_eusar' => '',
            'general_battery' => array('Li-Ion 1500 mAh'),
            'general_type' => 'Mobile',
            'general_cpu' => array('800Mhz'),
            'design_formfactor' => 'Bar',
            'design_dimensions' => '113 x 57 x 12',
            'design_weight' => '108',
            'design_antenna' => 'Internal',
            'design_keyboard' => 'Screen',
            'design_softkeys' => '',
            'design_sidekeys' => array(),
            'display_type' => 'TFT',
            'display_color' => 'Yes',
            'display_colors' => '262K',
            'display_size' => '3.2"',
            'display_x' => '320',
            'display_y' => '480',
            'display_other' => array('Capacitive', 'Touch', 'Multitouch', 'Touch Buttons'),
            'memory_internal' => array('117MB'),
            'memory_slot' => array('microSD', 'microSDHC', '32GB', '2GB'),
            'network' => array('CDMA800', 'CDMA1900', 'Bluetooth 3.0'),
            'media_camera' => array('2MP', '1600x1200'),
            'media_secondcamera' => array(),
            'media_videocapture' => array('QVGA'),
            'media_videoplayback' => array('MP3', 'WAV', 'eAAC+'),
            'media_audio' => array('MP4', 'H.264', 'H.263'),
            'media_other' => array('Geo-tagging'),
            'features' => array('Unlimited entries', 'Caller groups', 'Multiple numbers per contact', 'Search by both first and last name', 'Picture ID',
                 'Ring ID', 'Calendar', 'Alarm', 'Document viewer', 'Calculator', 'Computer sync', 'OTA sync', 'Music ringtones (MP3)', 'Polyphonic ringtones',
                 'Vibration', 'Flight mode', 'Silent mode', 'Speakerphone', 'Accelerometer', 'Voice dialing', 'Voice recording', 'SMS', 'Threaded viewer',
                 'MMS', 'Email', 'Push Email', 'IM', 'Organizer', 'Google Search', 'Maps', 'Gmail', 'YouTube', 'Google Talk',
                 'Picasa integration', 'Voice memo', 'Predictive text input (Swype)', 'Games'),
            'connectors' => array('USB', 'microUSB', '3.5mm Headphone')
        ),
		
		'SamsungGT-P1000' => array(
            'general_vendor' => 'Samsung',
            'general_model' => 'GT-P1000',
            'general_platform' => 'Android',
            'general_platform_version' => '2.3.3',
            'general_browser' => 'Android Webkit',
            'general_browser_version' => '4.0',
            'general_image' => 'samsunggt-p1000-1368755043-0.jpg',
            'general_aliases' => array('Samsung Galaxy Tab'),
            'general_eusar' => '1.07',
            'general_battery' => array('Li-Ion 4000 mAh'),
            'general_type' => 'Tablet',
            'general_cpu' => array('1000Mhz'),
            'design_formfactor' => 'Bar',
            'design_dimensions' => '190.1 x 120.45 x 11.98',
            'design_weight' => '380',
            'design_antenna' => 'Internal',
            'design_keyboard' => 'Screen',
            'design_softkeys' => '',
            'design_sidekeys' => array(),
            'display_type' => 'TFT',
            'display_color' => 'Yes',
            'display_colors' => '16M',
            'display_size' => '7"',
            'display_x' => '1024',
            'display_y' => '600',
            'display_other' => array('Capacitive', 'Touch', 'Multitouch', 'Touch Buttons', 'Gorilla Glass', 'TouchWiz'),
            'memory_internal' => array('16GB', '32GB', '512MB RAM'),
            'memory_slot' => array('microSD', 'microSDHC', '32GB'),
			'network' => array('GSM850', 'GSM900', 'GSM1800', 'GSM1900', 'UMTS900', 'UMTS1900', 'UMTS2100', 'HSDPA900', 'HSDPA1900', 'HSDPA2100', 'Bluetooth 3.0', '802.11b', '802.11g', '802.11n', 'GPRS', 'EDGE'),
            'media_camera' => array('3.15MP', '2048x1536'),
            'media_secondcamera' => array('1.3MP'),
            'media_videocapture' => array('720x480@30fps'),
            'media_videoplayback' => array('MPEG4', 'H.264', 'DivX', 'XviD'),
            'media_audio' => array('MP3', 'AAC', 'FLAC', 'WMA', 'WAV', 'AMR', 'OGG', 'MIDI'),
            'media_other' => array('Auto focus', 'Video calling', 'Geo-tagging', 'LED Flash'),
            'features' => array('Unlimited entries', 'Caller groups', 'Multiple numbers per contact', 'Search by both first and last name', 'Picture ID', 'Ring ID',
                     'Calendar', 'Alarm', 'Document viewer', 'Calculator', 'DLNA', 'Computer sync', 'OTA sync', 'Music ringtones (MP3)', 'Flight mode', 'Silent mode',
                     'Speakerphone', 'Accelerometer', 'Voice commands', 'Voice recording', 'SMS', 'Threaded viewer', 'MMS', 'Email', 'Push Mail', 'IM', 'RSS', 'Social networking integration',
                     'Full HD video playback', 'Up to 7h movie playback', 'Organizer', 'Image/video editor', 'Thinkfree Office', 'Word viewer', 'Excel viewer', 'PowerPoint viewer',
                     'PDF viewer', 'Google Search', 'Maps', 'Gmail', 'YouTube', 'Google Talk', 'Picasa integration', 'Readers/Media/Music Hub', 'Voice memo',
                     'Voice dialing', 'Predictive text input (Swype)', 'Games'),
            'connectors' => array('USB', '3.5mm Headphone', 'TV Out', 'MHL')
		),
		
		'GenericOperaMini' => array(
			'general_vendor' => 'Generic',
            'general_model' => 'Opera Mini 5',
            'general_platform' => '',
            'general_platform_version' => '',
            'general_browser' => 'Opera Mini',
            'general_browser_version' => '5.2',
            'general_image' => '',
            'general_aliases' => array(),
            'general_eusar' => '',
            'general_battery' => array(),
            'general_type' => 'Mobile',
            'general_cpu' => array() ,
            'design_formfactor' => '',
            'design_dimensions' => '',
            'design_weight' => '',
            'design_antenna' => '',
            'design_keyboard' => '',
            'design_softkeys' => '',
            'design_sidekeys' => array(),
            'display_type' => '',
            'display_color' => '',
            'display_colors' => '',
            'display_size' => '',
            'display_x' => '176',
            'display_y' => '160',
            'display_other' => array(),
            'memory_internal' => array(),
            'memory_slot' => array(),
            'network' => array(),
            'media_camera' => array(),
            'media_secondcamera' => array(),
            'media_videocapture' => array(),
            'media_videoplayback' => array(),
            'media_audio' => array(),
            'media_other' => array(),
            'features' => array(),
            'connectors' => array()
		),
		
		'AppleiPhone' => array(
			'general_vendor' => 'Apple',
            'general_model' => 'iPhone',
            'general_platform' => 'iOS',
            'general_image' => 'apple^iphone.jpg',
            'general_aliases' => array(),
            'general_eusar' => '0.97',
            'general_battery' => array('Li-Ion 1400 mAh'),
            'general_type' => 'Mobile',
            'general_cpu' => array('ARM 11', '412Mhz'),
            'design_formfactor' => 'Bar',
            'design_dimensions' => '115 x 61 x 11.6',
            'design_weight' => '135',
            'design_antenna' => 'Internal',
            'design_keyboard' => 'Screen',
            'design_softkeys' => '',
            'design_sidekeys' => array('Volume'),
            'display_type' => 'TFT',
            'display_color' => 'Yes',
            'display_colors' => '16M',
            'display_size' => '3.5"',
            'display_x' => '320',
            'display_y' => '480',
            'display_other' => array('Capacitive', 'Touch', 'Multitouch', 'Gorilla Glass'),
            'memory_internal' => array('4GB', '8GB', '16GB RAM'),
            'memory_slot' => array(),
            'network' => array('GSM850', 'GSM900', 'GSM1800', 'GSM1900', 'Bluetooth 2.0', '802.11b', '802.11g', 'GPRS', 'EDGE'),
            'media_camera' => array('2MP', '1600x1200'),
            'media_secondcamera' => array(),
            'media_videocapture' => array(),
            'media_videoplayback' => array('MPEG4', 'H.264'),
            'media_audio' => array('MP3', 'AAC', 'WAV'),
            'media_other' => array(),
            'features' => array('Unlimited entries', 'Multiple numbers per contact', 'Picture ID', 'Ring ID', 'Calendar', 'Alarm', 'Document viewer', 'Calculator',
                'Timer', 'Stopwatch', 'Computer sync', 'OTA sync', 'Polyphonic ringtones', 'Vibration', 'Phone profiles', 'Flight mode', 'Silent mode', 'Speakerphone',
                'Accelerometer', 'Voice recording', 'Light sensor', 'Proximity sensor', 'SMS', 'Threaded viewer', 'Email', 'Google Maps', 'Audio/video player', 'Games'),
            'connectors' => array('USB', '3.5mm Headphone', 'TV Out'),
            'general_platform_version' => '',
            'general_browser' => 'Opera Mini',
            'general_browser_version' => '6.1'
		),
		
		'SonyEricssonX10I' => array(
			'general_vendor' => 'SonyEricsson',
            'general_model' => 'X10I',
            'general_platform' => 'Android',
            'general_platform_version' => '2.1.1',
            'general_browser' => 'Android Webkit',
            'general_browser_version' => '4.0',
            'general_image' => '',
            'general_aliases' => array('SonyEricsson Xperia X10', 'SonyEricsson X10'),
			'general_eusar' => '',
            'general_battery' => array('Li-Po 1500 mAh', 'BST-41'),
            'general_type' => 'Mobile',
            'general_cpu' => array('1000Mhz'), 
            'design_formfactor' => 'Bar',
            'design_dimensions' => '119 x 63 x 13',
            'design_weight' => '135',
            'design_antenna' => 'Internal',
            'design_keyboard' => 'Screen',
            'design_softkeys' => '',
            'design_sidekeys' => array('Volume', 'Camera'),
            'display_type' => 'TFT', 
            'display_color' => 'Yes',
            'display_colors' => '65K',
            'display_size' => '4"',
            'display_x' => '480',
            'display_y' => '854',
            'display_other' => array('Capacitive', 'Touch', 'Multitouch'),
            'memory_internal' => array('1GB', '384MB RAM'),
            'memory_slot' => array('microSD', 'microSDHC', '32GB', '8GB'),
			'network' => array('GSM850', 'GSM900', 'GSM1800', 'GSM1900', 'UMTS900', 'UMTS1700', 'UMTS2100', 'HSDPA900', 'HSDPA1700', 'HSDPA2100', 'Bluetooth 2.1', '802.11b', '802.11g', 'GPRS Class 10', 'EDGE Class 10'),
            'media_camera' => array('8MP', '3264x2448'),
            'media_secondcamera' => array(),
            'media_videocapture' => array('WVGA@30fps'), 
            'media_videoplayback' => array('MPEG4'), 
            'media_audio' => array('MP3', 'AAC', 'AAC+', 'WMA', 'WAV'),
            'media_other' => array('Auto focus', 'Image stabilizer', 'Video stabilizer', 'Face detection', 'Smile detection', 'Digital zoom', 'Geo-tagging', 'Touch focus', 'LED Flash'),
            'features' => array('Unlimited entries', 'Caller groups', 'Multiple numbers per contact', 'Search by both first and last name', 'Picture ID', 'Ring ID', 'Calendar',
				'Alarm', 'Document viewer', 'Calculator', 'World clock', 'Stopwatch', 'Notes', 'Computer sync', 'OTA sync', 'Music ringtones (MP3)', 'Polyphonic ringtones', 'Vibration',
				'Flight mode', 'Silent mode', 'Speakerphone', 'Voice recording', 'Accelerometer', 'Compass', 'Timescape/Mediascape UI', 'SMS', 'Threaded viewer', 'MMS',
				'Email', 'Push email', 'IM', 'Google Search', 'Maps', 'Gmail', 'YouTube', 'Google Talk', 'Facebook and Twitter integration', 'Voice memo', 'Games'),
            'connectors' => array('USB 2.0', 'microUSB', '3.5mm Headphone')
		)
	);

	var $notFoundHeaders = array(
		'h1' => array(
			'user-agent' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; GTB7.1; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; InfoPath.2; .NET CLR 3.5.30729; .NET4.0C; .NET CLR 3.0.30729; AskTbFWV5/5.12.2.16749; 978803803'
		),
		'h2' => array(
			'user-agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22 ( .NET CLR 3.5.30729) Swapper 1.0.4'
		),
		'h3' => array(
			'user-agent' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; Sky Broadband; GTB7.1; SeekmoToolbar 4.8.4; Sky Broadband; Sky Broadband; AskTbBLPV5/5.9.1.14019)'
		)
	);
	
	var $deviceHeaders = array(
		'h1' => array(
			'user-agent' => 'Mozilla/5.0 (Linux; U; Android 2.2.2; en-us; SCH-M828C[3373773858] Build/FROYO) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
			'x-wap-profile' => 'http://www-ccpp.tcl-ta.com/files/ALCATEL_one_touch_908.xml',
			'match' => 'AlcatelOT-908222'
		),
		'h2' => array(
			'user-agent' => 'Mozilla/5.0 (Linux; U; Android 2.2.2; en-us; SCH-M828C[3373773858] Build/FROYO) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
			'match' => 'SamsungSCH-M828C'
		),
		'h3' => array(
			'x-wap-profile' => 'http://www-ccpp.tcl-ta.com/files/ALCATEL_one_touch_908.xml',
			'match' => 'AlcatelOT-90822'
		),
		'h4' => array(
			'user-agent' => 'Mozilla/5.0 (Linux; U; Android 2.3.3; es-es; GT-P1000N Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
			'x-wap-profile' => 'http://wap.samsungmobile.com/uaprof/GT-P1000.xml',
			'match' => 'SamsungGT-P1000'
		),
		'h5' => array(
			'user-agent' => 'Opera/9.80 (J2ME/MIDP; Opera Mini/5.21076/26.984; U; en) Presto/2.8.119 Version/10.54',
			'match' => 'GenericOperaMini'
		),
		'h6' => array(
			'user-agent' => 'Opera/9.80 (iPhone; Opera Mini/6.1.15738/26.984; U; tr) Presto/2.8.119 Version/10.54',
			'match' => 'AppleiPhone'
		),
		'h7' => array(
			'user-agent' => 'Mozilla/5.0 (Linux; U; Android 2.1-update1; cs-cz; SonyEricssonX10i Build/2.1.B.0.1) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17',
			'match' => 'SonyEricssonX10I'
		)
	);
	
	var $fileDevice10 = '{"Device":{"_id":"10","hd_specs":{"general_vendor":"Samsung","general_model":"SPH-A680","general_platform":"","general_platform_version":"","general_browser":"","general_browser_version":"","general_image":"samsungsph-a680-1403617960-0.jpg","general_aliases":["Samsung VM-A680"],"general_eusar":"","general_battery":["Li-Ion 900 mAh"],"general_type":"Mobile","general_cpu":[],"design_formfactor":"Clamshell","design_dimensions":"83 x 46 x 24","design_weight":"96","design_antenna":"Internal","design_keyboard":"Numeric","design_softkeys":"2","design_sidekeys":[],"display_type":"TFT","display_color":"Yes","display_colors":"65K","display_size":"","display_x":"128","display_y":"160","display_other":["Second External TFT"],"memory_internal":[],"memory_slot":[],"network":["CDMA800","CDMA1900","AMPS800"],"media_camera":["VGA","640x480"],"media_secondcamera":[],"media_videocapture":["Yes"],"media_videoplayback":[],"media_audio":[],"media_other":["Exposure control","White balance","Multi shot","Self-timer","LED Flash"],"features":["300 entries","Multiple numbers per contact","Picture ID","Ring ID","Calendar","Alarm","To-Do","Calculator","Stopwatch","SMS","T9","Computer sync","Polyphonic ringtones (32 voices)","Vibration","Voice dialing (Speaker independent)","Voice recording","TTY\/TDD","Games"],"connectors":["USB"]}}}';
	
	protected function setUp() {}	
	protected function tearDown() { }

	/**
	 * Compare a device to our static boilerplate
	 *
	 * @param array $check The reference boilerplate device (see $this->devices)
	 * @param array $fetch The fetched device
	 * @param string A note to print along with any assertion failures
	 * @return void
	 */
	private function _compareDevices($check, $fetch, $string='') {
		foreach ($check as $key => $value) {
			if (is_array($value)) {
				foreach($value as $subvalue) {
					$this->assertContains($subvalue, $fetch[$key]); 
				}
			} else {
				$this->assertEquals($value, $fetch[$key], $string);
			}
		}
	}
	
	/**
	 * Helper function to remove all files from a directory
	 * http://php.net/manual/en/function.rmdir.php
	 *
	 * @param string $dir Directory name
	 * @return void
	 */
	private function _rmDirFiles($dir) {
		foreach (glob($dir . '/*') as $file) {
	        if (is_dir($file))
	            $this->_rmDirFiles($file);
	        else
	            unlink($file);
	    }
	    rmdir($dir);
	}
	
	/**
	 * Test for runtime exception
     * @expectedException Exception
     */
	public function testUsernameRequired() {
		$config = array('username' => '');
		$hd3 = new HD3($config);
	}

	/**
	 * Test for runtime exception
     * @expectedException Exception
     */
	public function testSecretRequired() {
		$config = array('secret' => '');
		$hd3 = new HD3($config);
	}

	/**
	 * Test for a config passed to the constructor
     */
	public function testPassedConfig() {
		$config = array(
			'username' => 'jones',
			'secret' => 'jango',
			'site_id' => 78,
			'use_proxy' => true,
			'proxy_server' => '127.0.0.1',
			'proxy_port' => 8080,
			'proxy_user' => 'bob',
			'proxy_pass' => '123abc',
			'filesdir' => '/tmp'
		);
		$hd3 = new HD3($config);
		$this->assertEquals($hd3->getUsername(), 'jones');
		$this->assertEquals($hd3->getSecret(), 'jango');
		$this->assertEquals($hd3->getSiteId(), 78);
		$this->assertEquals($hd3->getUseProxy(), true);
		$this->assertEquals($hd3->getProxyServer(), '127.0.0.1');
		$this->assertEquals($hd3->getProxyPort(), 8080);
		$this->assertEquals($hd3->getProxyUser(), 'bob');
		$this->assertEquals($hd3->getProxyPass(), '123abc');
		$this->assertEquals($hd3->getFilesDir(), '/tmp');
	}

	/**
	 * Test for default config readon from config file
	 */
	public function testDefaultFileConfig() {
		$hd3 = new HD3();
		$hd3->setUseProxy(false);
		$hd3->setUseLocal(false);

		$this->assertNotEmpty($hd3->getUsername());
		$this->assertNotEmpty($hd3->getSecret());
		$this->assertNotEmpty($hd3->getSiteId());
		$this->assertNotEmpty($hd3->getApiServer());
	}

	/**
	 * Test for default http headers read when a new object is instantiated
	 */
	public function testDefaultSetup() {
		$userAgent = 'Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 NokiaN95-3/20.2.011 Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413';
		$xWapProfile = 'http://nds1.nds.nokia.com/uaprof/NN95-1r100.xml';
		$ipAddress = '127.0.0.1';
		$cookie = 'yum';
		
		// Mockup for $_SERVER in PHP CLI
		$_SERVER['HTTP_USER_AGENT'] = $userAgent;
		$_SERVER['HTTP_X_WAP_PROFILE'] = $xWapProfile;
		$_SERVER['REMOTE_ADDR'] = $ipAddress;
		$_SERVER['Cookie'] = $cookie;
		
		$hd3 = new HD3();
		$hd3->setUseLocal(false);
		$vars = $hd3->getDetectRequest();
		$this->assertEquals($userAgent, $vars['USER-AGENT']);
		$this->assertEquals($xWapProfile, $vars['X-WAP-PROFILE']);
		$this->assertEquals($ipAddress, $vars['ipaddress']);
		$this->assertEquals(isset($vars['Cookie']), false);
	}
	
	/**
	 * Test for manual setting of http headers
	 */
	public function testManualSetup() {
		$userAgent = 'Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 NokiaN95-3/20.2.011 Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413';
		$xWapProfile = 'http://nds1.nds.nokia.com/uaprof/NN95-1r100.xml';

		$hd3 = new HD3();
		$hd3->setDetectVar('user-agent', $userAgent);
		$hd3->setDetectVar('x-wap-profile', $xWapProfile);
		$vars = $hd3->getDetectRequest();
		$this->assertEquals($userAgent, $vars['user-agent']);
		$this->assertEquals($xWapProfile, $vars['x-wap-profile']);
	}
	
	/**
	 * Test for invalis API credentials
	 */
	public function testInvalidCredentials() {
		$config = array('username' => 'jones', 'secret' => 'jipple', 'use_local' => false, 'site_id' => 57);
		$hd3 = new HD3($config);
		
		$reply = $hd3->deviceVendors();
		$data = $hd3->getReply();
		$this->assertEquals($reply, false);
	}

	/**
	 * Test for deviceVendors
	 *
	 * The list is continually growing so ensure its a min length and common vendors are present
	 *
	 * @param bool $local : True if running in local mode, false otherwise
	 * @param bool $proxy : True if using proxy for API queries
	 * @return void
	 */
	public function deviceVendors($local=false, $proxy=false) {
		$hd3 = new HD3();
		$hd3->setUseLocal($local);
		$hd3->setUseProxy($proxy);
		
		$reply = $hd3->deviceVendors();
		$data = $hd3->getReply();
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		$this->assertGreaterThan(1000, count($data['vendor']));
		$this->assertContains('Apple', $data['vendor']);
		$this->assertContains('Sony', $data['vendor']);
		$this->assertContains('Samsung', $data['vendor']);
		$this->assertContains('Nokia', $data['vendor']);
		$this->assertContains('LG', $data['vendor']);
		$this->assertContains('HTC', $data['vendor']);
		$this->assertContains('Karbonn', $data['vendor']);
		$this->vendorCount = count($data['vendor']);
	}
	
	/**
	 * Test for deviceModels
	 *
	 * This list is also continually growing so ensure its a minimum length
	 * 
 	 * @param bool $local : True if running in local mode, false otherwise
	 * @param bool $proxy : True if using proxy for API queries
	 * @return void
	 */
	public function deviceModels($local=false, $proxy=false) {
		$hd3 = new HD3();
		$hd3->setUseLocal($local);
		$hd3->setUseProxy($proxy);
		
		$reply = $hd3->deviceModels('Nokia');
		$data = $hd3->getReply();
		$this->assertEquals($reply, true);
		$this->assertGreaterThan(700, count($data['model']));
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		$this->modelCount = count($data['model']);
	}

	/**
	 * Test for deviceView
	 *
	 * View detailed information about one device
	 * 
	 * @param bool $local : True if running in local mode, false otherwise
	 * @param bool $proxy : True if using proxy for API queries
	 * @return void
	 */
	public function deviceView($local=false, $proxy=false) {
		$hd3 = new HD3();
		$hd3->setUseLocal($local);
		$hd3->setUseProxy($proxy);

		$reply = $hd3->deviceView('Nokia', 'N95');
		$data = $hd3->getReply();
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		$this->_compareDevices($this->devices['NokiaN95'], $data['device']);
	}

	/**
	 * Test for deviceWhatHas
	 *
	 * Find which devices have a specific property
	 *
	 * @param bool $local : True if running in local mode, false otherwise
	 * @param bool $proxy : True if using proxy for API queries
	 * @return void
	 */
	public function deviceWhatHas($local=false, $proxy=false) {
		$hd3 = new HD3();
		$hd3->setUseLocal($local);
		$hd3->setUseProxy($proxy);
		
		$reply = $hd3->deviceWhatHas('design_dimensions', '101 x 44 x 16');
		$data = $hd3->getReply();
		$this->assertEquals($reply, true);
		$this->assertEquals(0, $data['status']);
		$this->assertEquals('OK', $data['message']);
		$jsonString = json_encode($data['devices']);
		$this->assertEquals(true, preg_match('/Asus/', $jsonString));
		$this->assertEquals(true, preg_match('/V80/', $jsonString));
		$this->assertEquals(true, preg_match('/Spice/', $jsonString));
		$this->assertEquals(true, preg_match('/S900/', $jsonString));
		$this->assertEquals(true, preg_match('/Voxtel/', $jsonString));
		$this->assertEquals(true, preg_match('/RX800/', $jsonString));
	}

	/**
	 * Perform a battery of detection tests
	 *
	 * Uses the headers in $this->notFoundHeaders and $this->deviceHeaders to test for common detection replys
	 *
	 * @param bool $local : True if running in local mode, false otherwise
	 * @param bool $proxy : True if using proxy for API queries
	 * @return void
	 */
	public function siteDetect($local=false, $proxy=false) {
		$hd3 = new HD3();
		$hd3->setUseLocal($local);
		$hd3->setUseProxy($proxy);
		
		// All of these are normal browser user-agents : not mobile devices
		foreach ($this->notFoundHeaders as $headers) {	
			$reply = $hd3->siteDetect($headers);
			$data = $hd3->getReply();
			$this->assertEquals($reply, false);
			$this->assertEquals(301, $data['status']);
		}
		
		// All of these are valid devices
		foreach ($this->deviceHeaders as $headers) {
			$match = $headers['match'];
			unset($headers['match']);
			$reply = $hd3->siteDetect($headers);
			$data = $hd3->getReply();
			$this->assertEquals($reply, true);
			$this->assertEquals(0, $data['status']);
			$this->assertEquals('OK', $data['message']);
			$this->assertEquals($data['hd_specs']['general_type'], $data['class'], "hd_specs.general_type not matching class in reply ".json_encode($data));
			$this->_compareDevices($this->devices[$match], $data['hd_specs'], "Comparing ".json_encode($this->devices[$match])." with ".json_encode($data['hd_specs']));
		}
	}
	
	/**
	 * Runs the Api tests against the Cloud web service
	 *
	 * @param void
	 * @return void
	 */
	public function testCloudApiCalls() {
		$this->deviceVendors(false, false);
		$this->deviceModels(false, false);
		$this->deviceView(false, false);
		$this->deviceWhatHas(false, false);
		$this->siteDetect(false, false);
	}

	/**
	 * Runs the same tests as testCloudApiCalls() but through a proxy
	 * 
	 * @param void
	 * @return void
	 */
	public function testCloudProxyApiCalls() {
		$hd3 = new HD3();
		$hd3->setUseLocal(false);
		$hd3->setUseProxy(true);

		$this->assertNotEmpty($hd3->getProxyServer());
		$this->assertNotEmpty($hd3->getProxyPort());
		$this->assertNotEmpty($hd3->getProxyUser());
		$this->assertNotEmpty($hd3->getProxyPass());
				
		$this->deviceVendors(false, true);
		$this->deviceModels(false, true);
		$this->deviceView(false, true);
		$this->deviceWhatHas(false, true);
		$this->siteDetect(false, true);
	}

		/**
	 * Test fetching the detection trees
	 *
	 * Deprecated in 4.0 : Replaced by testUltimateFetchArchive
	 *
	 * @param void
	 * @return void
	 */
	public function testUltimateFetchTrees() {
		$hd3 = new HD3();
		$hd3->setUseLocal(true);
		$hd3->setUseProxy(false);
		$hd3->setTimeout(120);
		$dir = "/tmp";
		$hd3->setFilesDir($dir);
		
		$reply = $hd3->siteFetchTrees();
		$this->assertEquals($reply, true);
		$this->assertFileExists($dir . DS . 'hd3trees.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'user-agent0.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'user-agent1.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'user-agentplatform.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'user-agentbrowser.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'profile0.json');
		$this->_rmDirFiles($dir . DS . 'hd34cache');
		unlink($dir . DS . 'hd3trees.json');
	}

	/**
	 * Test invalid credentials for accessing fetchTrees 
	 *
	 * Deprecated in 4.0 : Replaced by testUltimateFetchArchive
	 * 
	 * @param void
	 * @return void
	 */
	public function testUltimateFetchTreesFail() {
		$hd3 = new HD3(array('username' => 'bob', 'secret' => 'cowcowcow', 'site_id' => '76', 'filesdir' => '/tmp', 'timeout' => 120));
		$hd3->setUseLocal(true);
		$hd3->setUseProxy(false);
		
		$reply = $hd3->siteFetchTrees();
		$this->assertEquals($reply, false);
	}
	
	/**
	 * Fetch the device specs
	 *
	 * Deprecated in 4.0 : Replaced by testUltimateFetchArchive
	 * 
	 * @param void
	 * @return void
	 */
	public function testUltimateFetchSpecs() {
		ini_set('memory_limit', '768M');
		$hd3 = new HD3();
		$hd3->setUseLocal(true);
		$hd3->setUseProxy(false);
		$hd3->setTimeout(120);
		$dir = "/tmp";
		
		$hd3->setFilesDir($dir);

		$reply = $hd3->siteFetchSpecs();
		$this->assertEquals($reply, true);
		$this->assertFileExists($dir . DS . 'hd3specs.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'Device_10.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'Extra_546.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'Device_46142.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'Extra_9.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'Extra_102.json');
		$fileDevice10 = file_get_contents($dir . DS . 'hd34cache'. DS . 'Device_10.json');
		$this->assertEquals($fileDevice10, $this->fileDevice10);
		$this->_rmDirFiles($dir . DS . 'hd34cache');
		unlink($dir . DS . 'hd3specs.json');
	}

	/**
	 * Test invalid credentials for accessing fetchSpecs 
	 *
	 * Deprecated in 4.0 : Replaced by testUltimateFetchArchive
	 * 
	 * @param void
	 * @return void
	 */	
	public function testUltimateFetchSpecsFail() {
		$hd3 = new HD3(array('username' => 'bob', 'secret' => 'cowcowcow', 'site_id' => '76', 'filesdir' => '/tmp', 'timeout' => 120));
		$hd3->setUseLocal(true);
		$hd3->setUseProxy(false);
		
		$reply = $hd3->siteFetchSpecs();
		$this->assertEquals($reply, false);
	}

	/**
	 * Test fetchArchive
	 *
	 * @param void
	 * @return void
	 */
	public function testUltimateFetchArchive() {
		$hd3 = new HD3();
		$hd3->setUseLocal(true);
		$hd3->setUseProxy(false);
		$hd3->setTimeout(120);
	
		$dir = $hd3->getFilesDir();		
		$reply = $hd3->siteFetchArchive();
		$this->assertEquals($reply, true);
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'Device_10.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'Extra_546.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'Device_46142.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'Extra_9.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'Extra_102.json');
		$fileDevice10 = file_get_contents($dir . DS . 'hd34cache'. DS . 'Device_10.json');
		$this->assertEquals($fileDevice10, $this->fileDevice10);
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'user-agent0.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'user-agent1.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'user-agentplatform.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'user-agentbrowser.json');
		$this->assertFileExists($dir . DS . 'hd34cache'. DS . 'profile0.json');		
	}

	/**
	 * Test Ultimate mode for API calls
	 * 
     * @depends testUltimateFetchArchive
     */
	public function testUltimateApiCalls() {
		$this->deviceVendors(true, false);
		$this->deviceModels(true, false);
		$this->deviceView(true, false);
		$this->deviceWhatHas(true, false);
		$this->siteDetect(true, false);
	}
} 
