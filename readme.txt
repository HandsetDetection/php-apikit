Hi, 

Thanks for checking out Handset Detection. This is a Readme file 
for the Handset Detection HD3 API Kit, which implements version 3
of our API.

There are 3 ways you can use our device detection service, they are
by Javascript (Express Detection) by API Kit in web service mode
(Cloud Detection) or API Kit in local mode (Express Detection).

Javascript involves pasting a small javascript code snippet into the 
<head> section of your web pages. Its simple, painless, quick and easy 
to do. It doesnt need any technical skills apart from cut and paste. 
If you're using this method then you dont need this API Kit at all. 
Simply login, add a new 'Site Profile' and follow the prompts.

API Kits are more technical and are targeted at folks that have high 
traffic volumes or their own developers. Generally when a new visitor hits
your site you'll perform a detection to see if the visitor is using a Mobile, 
Tablet, Console etc … Then either redirect them to a mobile url or optimize 
your site for their device.

API Kits can make requests to our detection servers or they can run stand
alone on your own servers depending on your configuration and which plan 
you've purchased.

API Kit configuration files should be downloaded directly from Handset Detection.
Simply login to your user area, click 'Add a new Site Profile' configure
up your new site then download the appropriate config file for your API Kit.
(There are different config files for each API Kit). 

I hope you enjoy using Handset Detection and if we can help in any way then 
please let us know at hello@handsetdetection.com

Thanks.

Cheers
Richard Uren
richard@handsetdetection.com

-------

Handset Detection API Kit v3

The purpose of this project is to develop an API Kit for Handset Detection's
v3 API release. The release builds on concepts in the v2.0 API Kit however 
most of the actual web service calls are different.

The most significant change in v3 is that device/* and site/detect calls can 
now happen locally or remotely. Customers with appropriate permission can 
download a device database and detection rules and perform device inquiry 
and detections on a local server. This is especially awesome for super high 
volume customers.


Authentication


API v3 uses a modified form of HTTP digest for authentication. 
http://en.wikipedia.org/wiki/Digest_access_authentication

The usual HTTP digest is a challenge response, which involves a
request, a challenge then a response. This is 2 http requests
to detect one handset, a high overhead. To reduce the network
overhead we've fixed the server side realm, nonce and opaque to
the string literal 'APIv3'. You can now pre-compute the full
HTTP Digest without needing the challenge and send that with
the initial request.

Configuration

The v3 config file is different to the v2 config file.
Key differences are auth credentials no longer use an email 
address and now use a username which is used for API access only.
There are also additional config options.

Web Service Endpoints

Device : Use the device endpoint to lookup information about 
a device or query for specific device information. All device
calls are available in v3. Additionally all device calls can be local or remote.

/apiv3/device/vendors
/apiv3/device/models/<vendor>
/apiv3/device/view/<vendor>/<model>
/apiv3/device/whathas/<property>/<value>

User : No user calls are available in v3. This is marked for 
expansion in 4.0

/apiv3/user/add
/apiv3/user/delete
/apiv3/user/view
/apiv3/user/index
/apiv3/user/edit

Site : All detection requests now happen at the site level. 
v3.0 implements detect, fetcharchive (for Ultimate folks).
Add, delete, edit, view and index are marked for 4.0

/apiv3/site/detect/<site id>
/apiv3/site/fetcharchive/<site id>

/apiv3/site/add
/spiv3/site/delete
/apiv3/site/edit
/apiv3/site/view
/apiv3/site/index

A PHP reference implementation is available for local functions that 
perform detection and the device/* calls.
