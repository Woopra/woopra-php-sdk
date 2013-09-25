Track customers directly in PHP using Woopra's PHP SDK

The SDK can be used both for front-end and back-end tracking. In either cases, you should setup the tracker SDK first. To do so, configure the tracker instance as follows (replace mybusiness.com with your website as registered on Woopra):
``` php
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
If you wish to track page views, first call track(), and finally calling woopra_code() in your page header will insert the woopra javascript tracker:
``` php
<head>
   ...
   <?php $woopra->track()->woopra_code(); ?>
</head>

```
You can always track events through front-end later in the page. With all the previous steps done at once, it should look like:
``` php
<html>
   <head>
      ...
      <?php
         $woopra = new WoopraTracker($config);
         $woopra->identify($user)->track()->woopra_code();
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
and to make sure you're sending you're user's IP, set it manually doing (if none if specified, the value of $_SERVER['REMOTE_ADDR'] will be used):
``` php
$woopra->config(array("ip_address" => 74.125.224.72));
```