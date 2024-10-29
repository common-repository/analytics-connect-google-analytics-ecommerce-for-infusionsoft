<?php

/*
Plugin Name: AnalyticsConnect.io - Google Analytics Ecommerce for Infusionsoft
Plugin URI: http://analyticsconnect.io/kb/wordpress.php
Description: The official AnalyticsConnect.io plugin for WordPress.
Version: 2.4.1
Requires at least: 3.5.1
Author: AnalyticsConnect.io
Author URI: http://analyticsconnect.io
License: GPL v3

Copyright (C) 2011-2017, AnalyticsConnect.io - admin@analyticsconnect.io

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

$analyticsconnectio_wp_options = get_option('analyticsconnectio_options');
define('ANALYTICS_CONNECT_IO_COOKIE_ROOT_DOMAIN', $analyticsconnectio_wp_options['cookierootdomain']);

define('ANALYTICS_CONNECT_IO_SOFTWARE_VERSION', '2.4.1');  //  Use same as listed above & inside readme
define('ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME', 'AnalyticsConnect.io');  //  Used for display to users
define('ANALYTICS_CONNECT_IO_POST_URL', 'https://api.analyticsconnect.io/v2/request/index');  //  Main Servers: Processing URL
define('ANALYTICS_CONNECT_IO_CALLBACK_URL', 'https://api.analyticsconnect.io/v2/callback/wordpress');  //  Main Servers: Callback URL
define('ANALYTICS_CONNECT_IO_COOKIE_EXPIRATION', (86400 * 30));  //  Default is (86400 * 30) which is 30 days
define('ANALYTICS_CONNECT_IO_POST_URL_LEAD', 'https://api.analyticsconnect.io/v2/request/lead');  //  Main Servers: Processing URL



include( plugin_dir_path( __FILE__ ) . 'analyticsconnect-io-admin.php');



//  Cookie System
if ($analyticsconnectio_wp_options['cookiesenable'] == 'true') {  //  Is the cookie system turned on?
	add_action( 'init', 'analyticsconnectio_cookie_system' );
	function analyticsconnectio_cookie_system() {
	  $cookieData = analyticsconnectio_CookieBuild();
		if ($cookieData) {
			setcookie('_acio', json_encode($cookieData), time() + (86400 * 30), '/', '.' . ANALYTICS_CONNECT_IO_COOKIE_ROOT_DOMAIN);
		}
	}
}



//  Push Lead (Used on webform lead thank-you page)
add_shortcode('analyticsconnect-io-lead', 'analyticsconnectio_lead_shortcode');
function analyticsconnectio_lead_shortcode() {
	
	$contactId = FALSE;
	
	//  Let's see if we can pull an ContactID
	
	if (isset($_POST)) {  //  Look for ContactID as a POST var (used by developers of other plugins)
		foreach ($_POST as $var => $value) {
			if (strtolower($var)=='contactid') {
				$contactId = $value;
			}
		}
	}
	if (isset($_GET)) {  //  Look for ContactID as a GET var
		foreach ($_GET as $var => $value) {
			if (strtolower($var)=='contactid') {
				$contactId = $value;
			}
		}
	}
	if ($contactId == FALSE) {  //  No ContactID found
		return '<!-- ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' - ERROR (local): No ContactID available! -->';  //  Just give up
	} else {  //  We have a ContactID
		
		if (isset($_COOKIE['_acio'])) {
			
			$cookie = stripslashes($_COOKIE['_acio']);  //  The below is differnt for WP version because of how WordPress processes this
			if ((is_string($cookie) && (is_object(json_decode($cookie)) || is_array(json_decode($cookie))))) {  //  Cookie syntax okay
			
				$options = get_option('analyticsconnectio_options');  //  Pull info from WP database
			
				if (preg_match('/^[a-z0-9]{24}$/i', $options['secret_key'])) {  //  Only run if Secret Key has a valid format
					
					//  Get the user's Google Cookie ID, if not available generate a UUID we can use
					$cid = analyticsconnectio_get_user_ga_cookie_id();
					if ($cid == FALSE) { $cid = analyticsconnectio_gen_uuid(); }
					
					$curlPostData = array(
						'secretkey' => $options['secret_key'],
						'cid' => $cid,
						'contactid' => $contactId,
						'sentfromurl' => analyticsconnectio_GetPageUrl(),
						'cookie' => $cookie,
					);
					
					$curlPostBody = http_build_query($curlPostData);
					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_URL => ANALYTICS_CONNECT_IO_POST_URL_LEAD,
						CURLOPT_USERAGENT => ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' PHP Plugin v' . ANALYTICS_CONNECT_IO_SOFTWARE_VERSION,
						CURLOPT_POST => 1,
						CURLOPT_CONNECTTIMEOUT => 10,
						CURLOPT_POSTFIELDS => $curlPostBody
					));
					$result = curl_exec($curl);
					curl_close($curl);
					$data = json_decode($result, true);
					
					//  Process the result data
					
					if ($data['error'] == '') {  //  No errors reported back from the servers
						return '<!-- ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' - SUCCESS: Lead sent to Cookie Vault -->';
					} else {  //  Something went wrong
						return $data['error'];
					}
					
				} else {  //  Invalid Secret Key format
					return '<!-- ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' - ERROR (local): Your Secret Key is invalid! -->';
				}
				
			} else {  //  Cookie has been retarded
				return '<!-- ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' - ERROR (local): Cookie failed JSON syntax check! -->';
			}
			
		} else {  //  No cookie to send
			return '<!-- ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' - ERROR (local): No cookie available! -->';
		}
	}
}



//  Push Transaction (Used on online sale thank-you page)
add_shortcode('analyticsconnect-io', 'analyticsconnectio_shortcode');
function analyticsconnectio_shortcode($atts) {
	
	$orderId = FALSE;
	
	//  Let's see if we can pull an OrderID
	
	if (isset($_POST)) {  //  Look for OrderID as a POST var (used by developers of other plugins)
		foreach ($_POST as $var => $value) {
			if (strtolower($var)=='orderid') {
				$orderId = $value;
			}
		}
	}
	if (isset($_GET)) {  //  Look for OrderID as a GET var
		foreach ($_GET as $var => $value) {
			if (strtolower($var)=='orderid') {
				$orderId = $value;
			}
		}
	}
	if (isset($_GET)) {  //  Compatibility for PlusThis
		foreach ($_GET as $var => $value) {
			if (strtolower($var)=='ptlatestorderid') {
				$orderId = $value;
			}
		}
	}
	
	if ($orderId == FALSE) {  //  No OrderID found
		return '<!-- ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' - ERROR (local): No OrderID available! -->';  //  Just give up
	} else {  //  We have an OrderID
		
		$options = get_option('analyticsconnectio_options');  //  Pull info from WP database
		
		if (preg_match('/^[a-z0-9]{24}$/i', $options['secret_key'])) {  //  Only run if Secret Key has a valid format
		
			//  Get the user's Google Cookie ID, if not available generate a UUID we can use
			$cid = analyticsconnectio_get_user_ga_cookie_id();
			if ($cid == FALSE) { $cid = analyticsconnectio_gen_uuid(); }
			
			$curlPostData = array(
				'secretkey' => $options['secret_key'],
				'orderid' => $orderId,
				'cid' => $cid,
				'sentfromurl' => analyticsconnectio_GetPageUrl()
			);
			
			//  Add AnalyticsConnect.io traffic source cookie if available
			if (isset($_COOKIE['_acio'])) {
				$cookie = stripslashes($_COOKIE['_acio']);  //  The below is different for WP version because of how WordPress processes this
				if ((is_string($cookie) && (is_object(json_decode($cookie)) || is_array(json_decode($cookie))))) {  //  Cookie syntax okay
					$curlPostData['cookie'] = $cookie;
				}
			}
			
			// API
			if ($atts != '') {  //  Attributes have been added to the shortcode
				$curlPostData['api'] = 'true';
				if (isset($atts['gaua'])) { $curlPostData['gaua'] = $atts['gaua']; }
				if (isset($atts['awconid'])) { $curlPostData['awconid'] = $atts['awconid']; }
				if (isset($atts['awconlabel'])) { $curlPostData['awconlabel'] = $atts['awconlabel']; }
				if (isset($atts['fbconpixelid'])) { $curlPostData['fbconpixelid'] = $atts['fbconpixelid']; }
				if (isset($atts['bingtagid'])) { $curlPostData['bingtagid'] = $atts['bingtagid']; }
			}
			
			$curlPostBody = http_build_query($curlPostData);
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => ANALYTICS_CONNECT_IO_POST_URL,
				CURLOPT_USERAGENT => ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' WordPress Plugin v' . ANALYTICS_CONNECT_IO_SOFTWARE_VERSION,
				CURLOPT_POST => 1,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_POSTFIELDS => $curlPostBody
			));
			$result = curl_exec($curl);
			curl_close($curl);
			$data = json_decode($result, true);
			
			//  Process the result data
			
			if ($data['error'] == '') {  //  No errors reported back from the servers
				
				$htmlCode = '
				
	<!-- ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' WordPress Plugin v' . ANALYTICS_CONNECT_IO_SOFTWARE_VERSION . ' -->
	' . $data['googleanalytics'] . '
	' . $data['adwords'] . '
	' . $data['facebook'] . '
	' . $data['bing'] . '
	';
				return $htmlCode;
				
			} else {  //  Something went wrong
			
				return $data['error'];
				
			}
			
		} else {  //  Invalid Secret Key format
		
			return '<!-- ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' - ERROR (local): Your Secret Key is invalid! -->';
			
		}

	}

}



//  Get the user's Google Cookie ID
function analyticsconnectio_get_user_ga_cookie_id() {
	if (isset($_COOKIE['_ga'])){  //  Get the GA cookie
		$cookiePieces = explode('.', $_COOKIE['_ga']);
		$version = $cookiePieces[0];
		$domainDepth = $cookiePieces[1];
		$cid1 = $cookiePieces[2];
		$cid2 = $cookiePieces[3];
		$cid = $cid1 . '.' . $cid2;
		return $cid;
	} else {
		return FALSE;
	}
}



//  Generate UUID v4 (If the user doesn't have a Google Cookie we need to create something to send with the data to GA Measurement Protocol)
function analyticsconnectio_gen_uuid() {

	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
	
		// 16 bits for "time_mid"
		mt_rand( 0, 0xffff ),
	
		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		mt_rand( 0, 0x0fff ) | 0x4000,
	
		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand( 0, 0x3fff ) | 0x8000,
	
		// 48 bits for "node"
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	);
	
}



//  Gets URL of current page
function analyticsconnectio_GetPageUrl() {
	
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;

}



//  Creates Traffic Source Cookie
function analyticsconnectio_CookieBuild() {
	
	if (isset($_COOKIE['_acio'])) {  //  Don't overwrite existing cookies
		return false;
	} else {
		
		//  Sanitize Input
		foreach ($_GET as $var => $value) {
			$input[$var] = filter_var($value, FILTER_SANITIZE_URL);
		}
		
		//  Set Controller Data
		$acioCookieData['controller']['soft_ver'] = ANALYTICS_CONNECT_IO_SOFTWARE_VERSION;
		$acioCookieData['controller']['set_at_url'] = analyticsconnectio_GetPageUrl();

		if (isset($_GET['utm_source'])) {  // Traffic source has been hard-coded
			
			$data['utm_source'] = $input['utm_source'];
			if (isset($_GET['utm_medium'])) { $data['utm_medium'] = $input['utm_medium']; }
			if (isset($_GET['utm_campaign'])) { $data['utm_campaign'] = $input['utm_campaign']; }
			if (isset($_GET['utm_term'])) { $data['utm_term'] = $input['utm_term']; }
			if (isset($_GET['utm_content'])) { $data['utm_content'] = $input['utm_content']; }
			$acioCookieData['traffic_source'] = $data;
			return $acioCookieData;
			
		} else if (isset($_GET['gclid'])) {  //  Google AdWords detected
			
			$data['gclid'] = $input['gclid'];
			$acioCookieData['traffic_source'] = $data;
			return $acioCookieData;
			
		} else if (isset($_GET['dclid'])) {  //  Google Display Ads detected
			
			$data['dclid'] = $input['dclid'];
			$acioCookieData['traffic_source'] = $data;
			return $acioCookieData;
			
		} else if (isset($_SERVER['HTTP_REFERER'])) {  //  Referer data detected on server
			
			if (strpos($_SERVER['HTTP_REFERER'], ANALYTICS_CONNECT_IO_COOKIE_ROOT_DOMAIN) === FALSE) {  //  Ignore self-refered traffic
				
				//  Give just the hostname for the referer
				$referer = substr($_SERVER['HTTP_REFERER'], strpos($_SERVER['HTTP_REFERER'], '://') + 3);  //  Cut off protocol
				if (strpos($referer, 'www.') === 0) {  //  Cut off begining www
					$referer = substr($_SERVER['HTTP_REFERER'], strpos($_SERVER['HTTP_REFERER'], 'www.') + 4);
				}
				$parts = explode('/', $referer);  //  Cut off file path
				$referer = $parts[0];
				
				$data['utm_source'] = $referer;
				$data['utm_source_url'] = $_SERVER['HTTP_REFERER'];
				$data['utm_medium'] = 'referral';
				
				$acioCookieData['traffic_source'] = $data;
				return $acioCookieData;
				
			} else {  //  Do this for self-referred traffic
				
				$data['utm_source'] = '(direct)';
				$data['utm_medium'] = '(none)';
				
				$acioCookieData['traffic_source'] = $data;
				return $acioCookieData;
			
			}
			
		} else {  //  Default

			$data['utm_source'] = '(direct)';
			$data['utm_medium'] = '(none)';
			
			$acioCookieData['traffic_source'] = $data;
			return $acioCookieData;
			
		}
		
	}
	
}



//  If the user asked this plugin for the Google Analytics code to be loaded onto their site
add_action('wp_head', 'analyticsconnectio_add_to_header');
function analyticsconnectio_add_to_header() {
	$options = get_option('analyticsconnectio_options');
	if ($options['gacode'] == 'true') { ?>
<!-- The following has been added in by the <?php echo ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME; ?> WordPress Plugin v<?php echo ANALYTICS_CONNECT_IO_SOFTWARE_VERSION; ?> -->
<!-- START Google Universal Analytics Code -->
<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	ga('create', '<?php echo $options['gaua']; ?>', 'auto', {'allowLinker': true});
	ga('require', 'linker');
	ga('linker:autoLink', ['<?php echo $options['infappname']; ?>.infusionsoft.com', '<?php echo $options['infappname']; ?>.infusionsoft.app'], false, true);
	ga('require', 'displayfeatures');
	ga('send', 'pageview');
</script>
<!-- END Google Universal Analytics Code -->
	<?php }
}



?>