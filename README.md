Track customers directly in PHP using Woopra's PHP SDK

The purpose of this SDK is to allow our customers who have servers running PHP to track their users by writing only PHP code. Tracking directly in PHP will allow you to decide whether you want to track your users:
- through the front-end: after configuring the tracker, identifying the user, and tracking page views and events in PHP, the SDK will generate the corresponding JavaScript code, and you will be able to print that code in your pages' headers.
- through the back-end: after configuring the tracker & identifying the user, add the optional parameter TRUE to the methods <code>track</code> or <code>push</code>, and the PHP tracker will handle sending the data to Woopra by making HTTP Requests. By doing that, the client is never involved in the tracking process.

The best way to install Woopra/Woopra-php-sdk is using [Composer](getcomposer.org)

``` sh
$ composer require woopra/woopra-php-sdk
```

The first step is to setup the tracker SDK. To do so, import the woopra_tracker.php file then configure the tracker instance as follows (replace mybusiness.com with your website as registered on Woopra):
``` php
require_once('woopra_tracker.php');
// require_once('vendor/autoload.php'); // for composer installation
$woopra = new WoopraTracker(array("domain" => "mybusiness.com"));
```
You can update your idle timeout (default: 30 seconds) by updating the timeout property in your WoopraTracker instance (NB: this could also have been done in the step above, by adding all the properties you wish to configure to the array):
``` php
$woopra->config(array("idle_timeout" => 15000)); // in milliseconds
```
If you don't want to keep the user online on Woopra when they don't commit any event between the last event and the idle_timeout, you can disable auto pings (auto ping only matters for front-end tracking).
``` php
$woopra->config(array("ping" => false)); // default is true
```
To add custom visitor properties, you should use the identify($user) function:
``` php
$woopra->identify(array(
   "name" => "User Name",
   "email" => "user@company.com",
   "company" => "User Business"
));
```
If you wish to track page views, first call track(), and finally calling js_code() in your page header will insert the woopra javascript tracker:
``` php
<head>
   ...
   <?php $woopra->track()->js_code(); ?>
</head>

```
You can always track events through front-end later in the page. With all the previous steps done at once, it should look like:
``` php
<html>
   <head>
      ...
      <?php
         $woopra = new WoopraTracker($config);
         $woopra->identify($user)->track()->js_code();
      ?>
   </head>
   <body>
      ...
      <?php
         $woopra->track("play", array(
            "artist" => "Dave Brubeck",
            "song" => "Take Five",
            "genre" => "Jazz"
         ));
      ?>
      ...

   </body>
</html>
```
To track a custom event through back-end, just specify the additional parameter TRUE in the track() functions.
``` php
$woopra->track($event_name, $event_properties, TRUE);
```
If you identify the user after the last tracking event, don't forget to push() the update to Woopra:
``` php
$woopra->identify($user)->push();
//or, to push through back-end:
$woopra->identify($user)->push(TRUE);
```
If you're only going to be tracking through the back-end, set the cookie (before the headers are sent):
``` php
$woopra->set_woopra_cookie();
```
