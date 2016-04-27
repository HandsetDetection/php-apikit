# PHP API Kit v4.0, implementing v4.0 of the HandsetDetection API. #

API Kits can use our web service or resolve detections locally 
depending on your configuration.


## Installation ##

Download the package directly from github or use composer.

{
    "require": {
      "handsetdetection/php-apikit": "4.0.*"
    }
}


## Configuration ##

API Kit configuration files can be downloaded directly from Handset Detection.

1. Login to your dashboard
2. Click 'Add a Site'
3. Configure your new site
4. Grab the config file variables for your API Kit (from the site settings)
5. Place the variables into the hdconfig.php file


## Examples ##

### Instantiate the HD4 object ###

    // Using the default config file
    require_once('HD4.php');
    $hd = new HandsetDetection\HD4();

OR

    // Using a custom config file
    require_once('HD4.php');
    $hd = new HandsetDetection\HD4('/tmp/myCustomConfigFile.php');

### List all vendors ###

    if ($hd->deviceVendors()) {
        $data = $hd->getReply();
        print_r($data);
    } else {
        print $hd->getError();
    }

### List all device models for a vendor (Nokia) ###

    if ($hd->deviceModels('Nokia')) {
        $data = $hd->getReply();
        print_r($data);
    } else {
        print $hd->getError();
    }

### View information for a specific device (Nokia N95) ###

    if ($hd->deviceView('Nokia','N95')) {
        $data = $hd->getReply();
        print_r($data);
    } else {
        print $hd->getError();
    }

### What devices have this attribute ? ###

    if ($hd->deviceWhatHas('network','CDMA')) {
        $data = $hd->getReply();
        print_r($data);
    } else {
        print $hd->getError();
    }

### Basic device detection ###

This is the most simple detection call - http headers are picked up automatically.

    if ($hd->deviceDetect()) {
        $tmp = $hd->getReply();
        print_r($tmp);
    } else {
        print $hd->getError();
    }

### Manually set the http headers (user-agent etc..) ###

    $hd->setDetectVar('user-agent','Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 NokiaN95-3/20.2.011 Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413');
    $hd->setDetectVar('x-wap-profile','http://nds1.nds.nokia.com/uaprof/NN95-1r100.xml');
    if ($hd->deviceDetect()) {
        $tmp = $hd->getReply();
        print_r($tmp);
    } else {
        print $hd->getError();
    }

### Download the Full Ultimate Edition ###

Note : Increase the default timeout before downloading the archive.

    $hd->setTimeout(500);
    if ($hd->deviceFetchArchive()) {
        $data = $hd->getRawReply();
        echo "Downloaded ".strlen($data)." bytes";
    } else {
        print $hd->getError();
        print $hd->getRawReply();
    }

### Download the Community Ultimate Edition ###

    $hd->setTimeout(500);
    if ($hd->communityFetchArchive()) {
        $data = $hd->getRawReply();
        echo "Downloaded ".strlen($data)." bytes";
    } else {
        print $hd->getError();
        print $hd->getRawReply();
    }


## Extra Examples ##

Additional examples can be found in the examples.php file.


## Getting Started with the Free usage tier and Community Edition ##

After signing up with our service you'll be on a free usage tier which entitles you to 20,000 Cloud detections (web service)
per month, and access to our Community Edition for Ultimate (stand alone) detection. The archive for stand alone detection
can be downloaded manually however its easiest to configure the API kit with your credentials and let the API kit do the
heavy lifting for you. See examples above for how to do this.

Instructions for manually installing the archive are available at [v4 API Ultimate Community Edition, Getting Started](https://handsetdetection.readme.io/docs/getting-started-with-ultimate-community-full-editions)


## Unit testing ##

Unit tests use phpUnit and can be found in the tests directory.


## API Documentation ##

See the [v4 API Documentation](https://handsetdetection.readme.io).


## API Kits ##

See the [Handset Detection GitHub Repo](https://github.com/HandsetDetection).


## Support ##

Let us know if you have any hassles (hello@handsetdetection.com).


Thanks.

Cheers
Richard Uren
richard@handsetdetection.com