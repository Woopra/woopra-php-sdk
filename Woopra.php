<?php

/**
 * Woopra PHP SDK
 * This class represents the PHP equivalent of the JavaScript Woopra Object.
 * @version 1.0
 * @author Antoine Chkaiban
 */
class Woopra {

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
											"hide_campaign" => false
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
	* Events array stack
	* Each item of the stack is an array(2)
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
	function __construct() {

		//Tracker is not ready yet
		$this->tracker_ready = false;

		//Domain has not been set yet
		$this->domain_was_set = false;

		//Current configuration is Default
		$this->current_config = Woopra::$default_config;
		
	}

	/**
	 * Echoes JS code to configure the tracker
	 * @return none
	 */
	private function print_javascript_configuration() {

		$woopra_js_config = "woopra.config(".json_encode($this->custom_config).");";
		echo $woopra_js_config;

		//Configuration has been printed, reset the custom_configuration as an empty array
		unset( $this->custom_config );

	}

	/**
	 * Echoes JS code to identify the user with the tracker
	 * @return none
	 */
	private function print_javascript_identification() {

		$woopra_js_identify = "woopra.identify(".json_encode($this->user).");";
		echo $woopra_js_identify;

	}

	/**
	 * Echoes JS code to track custom events
	 * @param none
	 * @return none
	 */
	private function print_javascript_events() {

		$woopra_js_events = "";
		foreach ($this->events as $event) {
			$woopra_js_events .= "woopra.track(".json_encode($event[0]).", ".json_encode($event[1]).");\n	";
		}
		echo $woopra_js_events;

		//Events have been printed, reset the events as an empty array
		unset( $this->events );

	}

	/**
	 * Echoes Woopra Widget JS code, and checks if there is any stored Configuration, Identification, or Custom events awaiting process and echoes it too.
	 * @param none
	 * @return none
	 */
	public function woopra_widget() {

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
				if ( isset($this->custom_config) ) {
					$this->print_javascript_configuration();
					echo "\n\n";
				}

				//Print JavaScript Identification Code
				if ( isset($this->user) ) {
					$this->print_javascript_identification();
					echo "\n\n";
				}
				
				//Print stored events
				if ( isset($this->events) ) {
					$this->print_javascript_events();
				}

			?>

			woopra.track();

		</script>

		<!-- Woopra code ends here -->

		<?php

	}

	/**
	* Configures Woopra
	* @param array
	* @return Woopra object
	*/
	public function config($args) {

		$this->custom_config = array();
		foreach( $args as $option => $value) {

			if ( array_key_exists($option, Woopra::$default_config) ) {

				if ( gettype($value) == gettype( Woopra::$default_config[$option] ) ) {
					$this->custom_config[$option] = $value;
					$this->current_config[$option] = $value;

					//If it's the domain also update the cookie_domain
					if ( $option == "domain" ) {
						if ($this->current_config["cookie_domain"] == "") {
							$this->current_config["cookie_domain"] = $value;
						}
					}

				}
				else {
					unset( $custom_config );
					//Throw Exception
				}
			}
			else {
				unset( $custom_config );
				//Throw Exception
			}
		}
		if ( $this->tracker_ready ) {
			echo "<script>\n";
			$this->print_javascript_configuration();
			echo "\n</script>\n";
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

		if ( $this->tracker_ready ) {
			echo "<script>\n";
			$this->print_javascript_identification();
			echo "\n</script>\n";
		}
		return $this;
	}

	/**
	* Tracks Custom Event
	* @param string
	* @param array
	* @return Woopra object
	*/
	public function track($event, $args) {

		$this->events = array();
		array_push( $this->events, array($event, $args) );

		if ( $this->tracker_ready ) {
			echo "<script>\n";
			$this->print_javascript_events();
			echo "</script>\n";
		}
		return $this;
	}

	/**
	* Pushes unprocessed actions
	* @param none
	* @return none
	*/
	public function push() {
		?>

		<script>

			woopra.push();

		</script>

		<?php
	}
}

?>