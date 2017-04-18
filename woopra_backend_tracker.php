<?php

/**
 * Woopra PHP SDK
 * This class represents the PHP equivalent of the JavaScript Woopra Object.
 * @version 1.0
 * @author Antoine Chkaiban
 */
class WoopraTracker {

        private static $SDK_ID = "php";
        /**
        * Default configuration.
        * KEYS:
        *
        * domain (string) - Website hostname as added to Woopra
        * cookie_name (string) - Name of the cookie used to identify the visitor
        * cookie_domain (string) - Domain scope of the Woopra cookie
        * cookie_path (string) - Directory scope of the Woopra cookie
        * ping (boolean) - Ping woopra servers to ensure that the visitor is still on the webpage?
        * ping_interval (integer) - Time interval in milliseconds between each ping
        * idle_timeout (integer) - Idle time after which the user is considered offline
        * download_tracking (boolean) - Track downloads on the web page
        * outgoing_tracking (boolean) - Track external links clicks on the web page
        * download_pause (integer) - Time in millisecond to pause the browser to ensure that the event is tracked when visitor clicks on a download url
        * outgoing_pause (integer) - Time in millisecond to pause the browser to ensure that the event is tracked when visitor clicks on an outgoing url
        * ignore_query_url (boolean) - Ignores the query part of the url when the standard pageviews tracking function track()
        * hide_campaign (boolean) - Enabling this option will remove campaign properties from the URL when they’re captured (using HTML5 pushState)
        * ip_address (string) - the IP address of the user viewing the page. If back-end processing, always set this manually.
        * cookie_value (string) - the value of $_COOKIE["wooTracker"] if it has been set.
        * @var array
        */
	private $config = array(
                "domain" => "",
                "cookie_name" => "wooTracker",
                "cookie_domain" => "",
                "cookie_path" => "/",
                "ping" => true,
                "ping_interval" => 12000,
                "idle_timeout" => 300000,
                "download_tracking" => true,
                "outgoing_tracking" => true,
                "download_pause" => 200,
                "outgoing_pause" => 400,
                "ignore_query_url" => true,
                "hide_campaign" => false,
                "ip_address" => "",
                "cookie_value" => "",
                "app" => ""
        );

	private $visitor_data = array(
	);

	private $event_data = array(
	);


	function __construct($domain) {
		$this->config["domain"]=$domain;
        }

	public function addVisitorProperty($key, $value){
		$this->visitor_data[$key]=$value;
	}

	public function push(){
		$url="http://www.woopra.com/track/identify";
		$url=$url."?website=".urlencode($this->config["domain"]);
		foreach($this->visitor_data as $key => $value) {
			$url.="&cv_".urlencode($key)."=".urlencode($value);
		}
		echo $url;
		echo "\n";
	}

	public function track($eventName, $eventData, $metaData){
                $url="http://www.woopra.com/track/ce";
                $url=$url."?website=".urlencode($this->config["domain"]);

                foreach($this->visitor_data as $key => $value) {
                        $url.="&cv_".$key."=".$value;
                }

                $url=$url."&event=".urlencode($eventName);
		foreach($eventData as $key => $value) {
                        $url.="&ce_".urlencode($key)."=".urlencode($value);
                }

                if(isset($metaData)){
			if(isset($metaData["timestamp"])){
				$url.="&timestamp=".$metaData["timestamp"];
			}
                        if(isset($metaData["referer"])){
				$url.="&referer=".$metaData["referer"];
			}
                        if(isset($metaData["referrer"])){
				$url.="&referer=".$metaData["referrer"];
			}
		}
                echo $url;
		echo "\n";
        }

	public function test(){
		print("test "+"A\n");
	}

}


$tracker = new WoopraTracker("ralphsamuel.io");
$tracker->addVisitorProperty("name", "tigi");
$tracker->push();
$tracker->track("play", array("title"=>"TITLE"), array("timestamp"=>123, "referer"=>"docs.woopra.com"));

?>
