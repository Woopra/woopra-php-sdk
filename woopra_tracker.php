<?php

/**
 * Woopra PHP SDK
 * This class represents the PHP equivalent of the JavaScript Woopra Object.
 * @version 1.0
 * @author Antoine Chkaiban
 */
class WoopraTracker {

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
	private static $default_config = array(
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
		"cookie_value" => ""
	);

	/**
	* Custom configuration stack.
	* If the user has set up custom configuration, store it in this array. It will be sent when the tracker is ready.
	* @var array
	*/
	private $custom_config;

	/**
	* Current configuration
	* Default configuration array, updated by Manual configurations.
	* @var array
	*/
	private $current_config;

	/**
	* User array.
	* If the user has been identified, store his information in this array
	* KEYS:
	* email (string) – Which displays the visitor’s email address and it will be used as a unique identifier instead of cookies.
	* name (string) – Which displays the visitor’s full name
	* company (string) – Which displays the company name or account of your customer
	* avatar (string) – Which is a URL link to a visitor avatar
	* other (string) - You can define any attribute you like and have that detail passed from within the visitor live stream data when viewing Woopra
	* @var array
	*/
	private $user;

	/**
	* Has the latest information on the user been sent to woopra?
	* @var boolean
	*/
	private $user_up_to_date;

	/**
	* Events array stack
	* Each item of the stack is either:
	* - an empty array (if pv event)
	* - an array(2) (if custom event)
	* O (string) - the name of the event
	* 1 (array) - properties associated with that action
	* @var array
	*/
	private $events;

	/**
	* Is JavaScript Tracker Ready?
	* @var boolean
	*/
	private $tracker_ready;
	
	/**
	 * Woopra Analytics
	 * @param none
	 * @return none
	 * @constructor
	 */
	function __construct($config_params = null) {

		//Tracker is not ready yet
		$this->tracker_ready = false;

		//Domain has not been set yet
		$this->domain_was_set = false;

		//Current configuration is Default
		$this->current_config = WoopraTracker::$default_config;

		//Set the default IP
		$this->current_config["ip_address"] = $_SERVER["REMOTE_ADDR"];

		//Get cookie or generate a random one
		$this->current_config["cookie_value"] = isset($_COOKIE["wooTracker"]) ? $_COOKIE["wooTracker"] : WoopraTracker::RandomString();

		//We don't have any info on the user yet, so he is up to date by default.
		$this->user_up_to_date = true;

		//If configuration array was passed, configure Woopra
		if (isset($config_params)) {
			$this->config($config_params);
		}
	}

	/**
	 * Echoes JS code to configure the tracker
	 * @return none
	 */
	private function print_javascript_configuration() {
		if (isset($this->custom_config)) {

?>
		woopra.config(<?php echo json_encode($this->custom_config); ?>);
<?php
			//Configuration has been printed, reset the custom_configuration as an empty array
			unset( $this->custom_config );
		}
	}

	/**
	 * Echoes JS code to identify the user with the tracker
	 * @return none
	 */
	private function print_javascript_identification() {

		if ( ! $this->user_up_to_date ) {
?>
		woopra.identify(<?php echo json_encode($this->user); ?>);
<?php
			$this->user_up_to_date = true;
		}
	}

	/**
	 * Echoes JS code to track custom events
	 * @param none
	 * @return none
	 */
	private function print_javascript_events() {

		if (isset($this->events)) {

			foreach ($this->events as $event) {
				if(empty($event)) {
?>
		woopra.track();
<?php
				} else {
?>
		woopra.track(<?php echo json_encode($event[0]); ?>, <?php echo json_encode($event[1]); ?>); 
<?php
				}
			}
			//Events have been printed, reset the events as an empty array
			unset( $this->events );
		}
	}

	/**
	 * Random Cookie generator in case the user doesn't have a cookie yet. Better to use a hash of the email.
	 * @param none
	 * @return string
	 */
	private static function RandomString() {
	    $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	    $randstring = "";
	    for ($i = 0; $i < 12; $i++) {
	        $randstring .= $characters[rand(0, strlen($characters)-1)];
	    }
	    return $randstring;
	}



	/**
	 * Prepares the http request and sends it.
	 * @param boolean Is this a tracking event or are we just identifying a user?
	 * @param (optional) array
	 * @return none
	 */
	private function woopra_http_request($is_tracking, $event = null) {

		$base_url = "http://www.woopra.com/track/";

		//Config params
		$config_params = "?host=" . urlencode($this->current_config["domain"]);
		$config_params .= "&cookie=" . urlencode($this->current_config["cookie_value"]);
		$config_params .= "&ip=" . urlencode($this->current_config["ip_address"]);
		$config_params .= "&timeout=" . urlencode($this->current_config["idle_timeout"]);

		//User params
		$user_params = "";
		if ( isset($this->user) ) {
			foreach($this->user as $option => $value) {
				$user_params .= "&cv_" . urlencode($option) . "=" . urlencode($value);
			}
		}

		//Just identifying
		if ( ! $is_tracking ) {
			$url = $base_url . "identify/" . $config_params . $user_params;

		//Tracking
		} else {

			//Event params
			$event_params = "";
			if ( $event != null ) {
				$event_params .= "&ce_name=" . urlencode($event[0]);
				foreach($event[1] as $option => $value) {
					$event_params .= "&ce_" . urlencode($option) . "=" . urlencode($value);
				}
			} else {
				$event_params .= "&ce_name=pv&ce_url=" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			}
			$url = $base_url . "ce/" . $config_params . $user_params . $event_params;
		}

		$opts = array(
			'http'=>array(
				'method'=>"GET",
				'header'=>"User-Agent: ".$_SERVER['HTTP_USER_AGENT']
		    )
		);
		$context = stream_context_create($opts);

		//Send the request
		file_get_contents( $url, false, $context);
	}

	/**
	 * Echoes Woopra Widget JS code, and checks if there is any stored Configuration, Identification, or Custom events awaiting process and echoes it too.
	 * @param none
	 * @return Woopra object
	 */
	public function woopra_code() {

?>
	
	<!-- Woopra code starts here -->
	<script>
		(function(){
		var t,i,e,n=window,o=document,a=arguments,s="script",r=["config","track","identify","visit","push","call"],c=function(){var t,i=this;for(i._e=[],t=0;r.length>t;t++)(function(t){i[t]=function(){return i._e.push([t].concat(Array.prototype.slice.call(arguments,0))),i}})(r[t])};for(n._w=n._w||{},t=0;a.length>t;t++)n._w[a[t]]=n[a[t]]=n[a[t]]||new c;i=o.createElement(s),i.async=1,i.src="//static.woopra.com/js/w.js",e=o.getElementsByTagName(s)[0],e.parentNode.insertBefore(i,e)
		})("woopra");
<?php

		//The Tracker is now ready
		$this->tracker_ready = true;

		//Print Custom JavaScript Configuration Code
		$this->print_javascript_configuration();
		
		//Print JavaScript Identification Code
		$this->print_javascript_identification();
		
		//Print stored events
		$this->print_javascript_events();

?>
	</script>
	<!-- Woopra code ends here -->
	
<?php
		return $this;

	}

	/**
	* Configures Woopra
	* @param array
	* @return Woopra object
	*/
	public function config($args) {

		$this->custom_config = array();
		foreach( $args as $option => $value) {

			if ( array_key_exists($option, WoopraTracker::$default_config) ) {

				if ( gettype($value) == gettype( WoopraTracker::$default_config[$option] ) ) {
					$this->custom_config[$option] = $value;
					$this->current_config[$option] = $value;

					//If it's the domain also update the cookie_domain
					if ( $option == "domain" ) {
						if ($this->current_config["cookie_domain"] == "") {
							$this->custom_config["cookie_domain"] = $value;
							$this->current_config["cookie_domain"] = $value;
						}
					}

				}
				else {
					trigger_error("Wrong value type in configuration array for parameter ".$option.". Recieved ".gettype($value).", expected ".gettype( WoopraTracker::$default_config[$option] ).".");
				}
			}
			else {
				trigger_error("Unexpected parameter in configuration array: ".$option.".");
			}
		}
		return $this;
	}

	/**
	* Identifies User
	* @param array
	* @return Woopra object
	*/
	public function identify($identified_user) {

		$this->user = $identified_user;
		$this->user_up_to_date = false;
		return $this;
	}

	/**
	* Tracks Custom Event. If no parameters are specified, will simply track pageview.
	* @param string
	* @param array
	* @param (optional) boolean
	* @return Woopra object
	*/
	public function track($event = null, $args = array(), $back_end_processing = false) {

		if ( $back_end_processing ) {
			$http_event = null;
			if ( $event != null ) {
				$http_event = array($event, $args);
			}
			$this->woopra_http_request(true, $http_event);
			return $this;
		}

		if ($event == null) {
			if ( $this->tracker_ready ) {
?>
	<script>
<?php
				$this->print_javascript_configuration();
				$this->print_javascript_identification();
?>
		woopra.track();
	</script>
<?php
			} else {
				if (! isset($this->events) ) {
					$this->events = array();
				}
				array_push( $this->events, array());
			}
			return $this;
		}

		if (! isset($this->events) ) {
			$this->events = array();
		}
		array_push( $this->events, array($event, $args) );

		if ( $this->tracker_ready ) {
?>
	<script>
<?php
			$this->print_javascript_configuration();
			$this->print_javascript_identification();
			$this->print_javascript_events();
?>
	</script>
<?php
		}
		return $this;
	}

	/**
	* Pushes unprocessed actions
	* @param none
	* @param (optional) boolean
	* @return none
	*/
	public function push($back_end_processing = false) {

		if ( $back_end_processing ) {
			$this->woopra_http_request(false);
			$this->user_up_to_date = true;
		} else {

?>
	<script>
<?php
			$this->print_javascript_configuration();
			$this->print_javascript_identification();
?>
		woopra.push();
	</script>
<?php
		}
	}

	/**
	* Sets the Woopra cookie from the back-end. Call this function before any headers are sent (HTTP restrictions).
	* @param none
	* @return none
	*/
	public function set_woopra_cookie() {
		setcookie( $this->current_config["cookie_name"], $this->current_config["cookie_value"], time()+(60*60*24*365*2), $this->current_config["cookie_path"], $this->current_config["cookie_domain"] );
	}
}

?>
