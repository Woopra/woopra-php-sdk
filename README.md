Track customers directly in PHP using Woopra's PHP SDK

The SDK can be used both for front-end and back-end tracking. In either cases, you should setup the tracker SDK first. To do so, configure the tracker instance as follows (replace mybusiness.com with your website as registered on Woopra):
``` php
$woopra = new WoopraTracker(array(&quot;domain&quot; => &quot;mybusiness.com&quot;));
```
You can update your idle timeout (default: 30 seconds) by updating the timeout property in your WoopraTracker instance (NB: this could also have been done in the step above, by adding all the properties you wish to configure to the array):
``` php
$woopra->config(array(&quot;idle_timeout&quot; => 15000)); // in milliseconds
```
If you don&#8217;t want to keep the user online on Woopra when they don&#8217;t commit any event between the last event and the idle_timeout, you can disable auto pings (auto ping only matters for front-end tracking).
``` php
$woopra->config(array(&quot;ping&quot; => false)); // default is true
```
To add custom visitor properties, you should use the identify($user) function:
``` php
$woopra->identify(array(
   &quot;name&quot; => &quot;User Name&quot;,
   &quot;email&quot; => &quot;user@company.com&quot;,
   &quot;company&quot; => &quot;User Business&quot;
));
```
If you wish to track page views, first call track(), and finally calling woopra_code() in your page header will insert the woopra javascript tracker:
``` php
&lt;head&gt;
   ...
   &lt;?php $woopra->track()->woopra_code(); ?&gt;
&lt;/head&gt;

```
You can always track events through front-end later in the page. With all the previous steps done at once, it should look like:
``` php
&lt;html&gt;
   &lt;head&gt;
      ...
      &lt;?php
         $woopra = new WoopraTracker($config);
         $woopra->identify($user)->track()->woopra_code();
      ?&gt;
   &lt;/head&gt;
   &lt;body&gt;
      ...
      &lt;?php
         $woopra->track(&quot;play&quot;, array(
            &quot;artist&quot; => &quot;Dave Brubeck&quot;,
            &quot;song&quot; => &quot;Take Five&quot;,
            &quot;genre&quot; => &quot;Jazz&quot;
         ));
      ?&gt;
      ...

   &lt;/body&gt;
&lt;/html&gt;
```
To track a custom event through back-end, just specify the additional parameter TRUE in the track() functions.
``` php
$woopra->track($event_name, $event_properties, TRUE);
```
If you identify the user after the last tracking event, don&#8217;t forget to push() the update to Woopra:
``` php
$woopra->identify($user)->push();
//or, to push through back-end:
$woopra->identify($user)->push(TRUE);
```
If you&#8217;re only going to be tracking through the back-end, set the cookie (before the headers are sent):
``` php
$woopra->set_woopra_cookie();
```
and to make sure you&#8217;re sending you&#8217;re user&#8217;s IP, set it manually doing (if none if specified, the value of $_SERVER['REMOTE_ADDR'] will be used):
``` php
$woopra->config(array(&quot;ip_address&quot; => 74.125.224.72));
```