<?php



// Add the admin options page
add_action('admin_menu', 'analyticsconnectio_admin_add_page');
function analyticsconnectio_admin_add_page() {
	add_options_page(ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME, ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME, 'manage_options', 'analyticsconnectio', 'analyticsconnectio_options_page');
}



//  CSS for the Admin
add_action( 'admin_enqueue_scripts', 'analyticsconnectio_add_stylesheet_to_admin' );
function analyticsconnectio_add_stylesheet_to_admin() {
	wp_enqueue_style( 'analyticsconnectio-admin-style', plugins_url() . '/analytics-connect-google-analytics-ecommerce-for-infusionsoft/analyticsconnect-io-admin-style.css');
}



//  Display the admin options page
function analyticsconnectio_options_page() {

	$displayUserData = analyticsconnectio_get_displayable_user_data();

?>
<div class="wrap">
<h2><?php echo ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME; ?></h2>
<hr><p> &nbsp; </p>
<h3>Instructions</h3>
<p>Authenticate with the <?php echo ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME; ?> servers by entering your secret key below.</p>
<p>If you need a key, please visit our <a href="https://analyticsconnect.io?utm_source=ac-wordpress-plugin&utm_medium=text-link&utm_campaign=internal-wordpress-link">website</a> to sign up.</p>
<p>Insert the shortcode <b>[analyticsconnect-io]</b> on your sales thank-you page.</p>
<p>Make sure that the "orderid" variable is being passed to the thank-you page containing the shortcode.</p>
<p>See our <a href="https://analyticsconnect.io/app/user/installation-troubleshooting-guide.php?utm_source=ac-wordpress-plugin&utm_medium=text-link&utm_campaign=internal-wordpress-link">Installation and Troubleshooting Guide</a> for additional information.</p>
<p>For advanced configuration options, see our <a href="https://analyticsconnect.io/kb/api.php?utm_source=ac-wordpress-plugin&utm_medium=text-link&utm_campaign=internal-wordpress-link">API Documentation</a>.</p>
<p> &nbsp; </p>
<h3>Status</h3>
<table>
	<tr>
		<td><p><b>Connection to <?php echo ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME; ?>:</b></p></td>
		<td><p><?php echo $displayUserData['status']; ?></p></td>
	</tr>
	<tr>
		<td><p><b>Connection to Infusionsoft:</b></p></td>
		<td><p><?php echo $displayUserData['infappname']; ?></p></td>
	</tr>
	<tr>
		<td><p><b>Google Analytics Tracking ID:</b></p></td>
		<td><p><?php echo $displayUserData['gaua']; ?></p></td>
	</tr>
	<tr>
		<td><p><b>Plugin Shortcode:</b></p></td>
		<td><p><?php echo $displayUserData['shortcodestatus']; ?></p></td>
	</tr>
</table>
<p> &nbsp; </p>
<form action="options.php" method="post">
<?php settings_fields('analyticsconnectio_options'); ?>
<?php do_settings_sections('analyticsconnectio'); ?>
<input name="Submit" type="submit" class="button button-primary" value="<?php esc_attr_e('Save Settings'); ?>" />
</form>
</div>
<?php
}



//  Add the admin settings and such
add_action('admin_init', 'analyticsconnectio_admin_init');
function analyticsconnectio_admin_init(){
	register_setting( 'analyticsconnectio_options', 'analyticsconnectio_options', 'analyticsconnectio_options_validate' );
	add_settings_section('analyticsconnectio_key', 'Authentication', 'analyticsconnectio_section_authentication_settings_text', 'analyticsconnectio');
	add_settings_field('analyticsconnectio_secret_key', 'Secret Key', 'analyticsconnectio_key_setting_string', 'analyticsconnectio', 'analyticsconnectio_key');
	add_settings_section('analyticsconnectio_show_gacode', 'Google Analytics Tracking Code (Optional)', 'analyticsconnectio_section_gacode_settings_text', 'analyticsconnectio');
	add_settings_field('analyticsconnectio_gacode', 'Write Google Analytics Tracking Code', 'analyticsconnectio_gacode_setting_string', 'analyticsconnectio', 'analyticsconnectio_show_gacode');
	add_settings_section('analyticsconnectio_show_cookies', 'Cookie System (Optional)', 'analyticsconnectio_section_cookiesenable_settings_text', 'analyticsconnectio');
	add_settings_field('analyticsconnectio_cookiesenable', 'Enable Cookie System', 'analyticsconnectio_cookiesenable_setting_string', 'analyticsconnectio', 'analyticsconnectio_show_cookies');
	add_settings_field('analyticsconnectio_cookierootdomain', 'Cookie Root Domain', 'analyticsconnectio_cookierootdomain_setting_string', 'analyticsconnectio', 'analyticsconnectio_show_cookies');

}



//  The instructions appearing above the Authentication Section
function analyticsconnectio_section_authentication_settings_text() {
	echo '<p>Enter your ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' secret key below to authenticate with the ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' servers.</p>';
}



//  Display of the Authentication form element
function analyticsconnectio_key_setting_string() {
	$options = get_option('analyticsconnectio_options');
	echo "<input id='analyticsconnectio_secret_key' name='analyticsconnectio_options[secret_key]' size='40' type='text' value='{$options['secret_key']}' />";
}



//  The instructions appearing above the Google Analytics Section
function analyticsconnectio_section_gacode_settings_text() {
	echo '<p>If you already have a plugin that is injecting the Google Analytics tracking code into your WordPress install, leave this option turned off.  If you need the Google Analytics tracking code installed, the below option will do it.  It uses the information from your ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' account to automatically create the Google Analytics code with <a href="https://analyticsconnect.io/kb/google-analytics-cross-domain-tracking-infusionsoft.php?utm_source=ac-wordpress-plugin&utm_medium=text-link&utm_campaign=internal-wordpress-link">cross-domain tracking</a> and <a href="https://analyticsconnect.io/lc/google-analytics-remarketing-demographics-interest-reporting-infusionsoft.php?utm_source=ac-wordpress-plugin&utm_medium=text-link&utm_campaign=internal-wordpress-link">display features</a> enabled.</p>';
}



//  Display of the Google Analytics form element
function analyticsconnectio_gacode_setting_string() {
	$options = get_option('analyticsconnectio_options');
	if ($options['gacode'] == 'true') { $isChecked = 'checked'; } else { $isChecked = ''; }
	echo "<input id='analyticsconnectio_gacode' name='analyticsconnectio_options[gacode]' type='checkbox' value='true' " . $isChecked . " />";
}



//  The instructions appearing above the Cookie System Section
function analyticsconnectio_section_cookiesenable_settings_text() {
	echo '<p>If you have the <a href="https://analyticsconnect.io/kb/analytics-connect-cookie-vault-cookie-system.php?utm_source=ac-wordpress-plugin&utm_medium=text-link&utm_campaign=internal-wordpress-link">Cookie Vault</a> enabled from inside your ' . ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' account settings, you\'ll need to turn this on.  For the Cookie Root Domain, unless you have reason to do otherwise, use "example.com" rather than "www.example.com", or "subdomain.example.com" so that the cookie is accessible to your entire domain.</p>';
}



//  Display of the Cookie System form element
function analyticsconnectio_cookiesenable_setting_string() {
	$options = get_option('analyticsconnectio_options');
	if ($options['cookiesenable'] == 'true') { $isChecked = 'checked'; } else { $isChecked = ''; }
	echo "<input id='analyticsconnectio_cookiesenable' name='analyticsconnectio_options[cookiesenable]' type='checkbox' value='true' " . $isChecked . " />";
}



//  Display of the Cookie Root Domain form element
function analyticsconnectio_cookierootdomain_setting_string() {
	$options = get_option('analyticsconnectio_options');
	echo "<input id='analyticsconnectio_cookierootdomain' name='analyticsconnectio_options[cookierootdomain]' size='40' type='text' value='{$options['cookierootdomain']}' />";
}



//  Sanitize input
function analyticsconnectio_options_validate($input) {

	$options = get_option('analyticsconnectio_options');
	
	//  Secret Key
	$newinput['secret_key'] = trim($input['secret_key']);
	if (!preg_match('/^[a-z0-9]{24}$/i', $newinput['secret_key'])) {  //  Letters and numbers only, 24 characters long
		$newinput['secret_key'] = 'INVALID KEY FORMAT';
	}
	
	//  GA Code
	if ($input['gacode'] == 'true'){
		$newinput['gacode'] = $input['gacode'];
	} else {
		$newinput['gacode'] = 'N';
	}
	
	//  Infusionsoft Application Name
	if (ctype_alnum($input['infappname'])) {
		$newinput['infappname'] = $input['infappname'];
	} else {
		$newinput['infappname'] = $options['infappname'];
	}
	
	//  Google Analytics Code
	if (preg_match('/^([a-z0-9]+-)*[a-z0-9]+$/i', $input['gaua'])) {  //  Letters, numbers, and hyphens only (hyphen not repeated twice-in-a-row and not the begining/ending of the string)
		$newinput['gaua'] = $input['gaua'];
	} else {
		$newinput['gaua'] = $options['gaua'];
	}
	
	//  Enable Cookie System
	if ($input['cookiesenable'] == 'true'){
		$newinput['cookiesenable'] = $input['cookiesenable'];
	} else {
		$newinput['cookiesenable'] = 'N';
	}
	
	//  Cookie Root Domain
	if (preg_match('/^[a-z0-9.\-]+$/i', $input['cookierootdomain'])) {  //  Letters, numbers, ".", and "-"
		$newinput['cookierootdomain'] = strtolower($input['cookierootdomain']);
	} else {
		$newinput['cookierootdomain'] = $options['cookierootdomain'];
	}
	
	return $newinput;
}



//  Add links on plugins page
add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( __FILE__ ) . 'analyticsconnect-io.php'), 'analyticsconnectio_action_links' );
function analyticsconnectio_action_links( $links ) {
	return array_merge(
		array(
			'support' => '<a href="http://analyticsconnect.io/app/user/premium-support.php?utm_source=ac-wordpress-plugin&utm_medium=text-link&utm_campaign=premium-support-link">Premium Support</a>',
			'settings' => '<a href="options-general.php?page=analyticsconnectio">Settings</a>'
		),
		$links
	);
}



//  Pull user data from server
function analyticsconnectio_get_user_data(){
	
	$options = get_option('analyticsconnectio_options');  //  Pull info from WP database
		
	if (preg_match('/^[a-z0-9]{24}$/i', $options['secret_key'])) {  //  Only run if Secret Key has a valid format
	
		$curlPostData = array(
			'secretkey' => $options['secret_key']
		);
		$curlPostBody = http_build_query($curlPostData);
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => ANALYTICS_CONNECT_IO_CALLBACK_URL,
			CURLOPT_USERAGENT => ANALYTICS_CONNECT_IO_APP_DISPLAY_NAME . ' WordPress Plugin v' . ANALYTICS_CONNECT_IO_SOFTWARE_VERSION,
			CURLOPT_POST => 1,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_POSTFIELDS => $curlPostBody
		));
		$result = curl_exec($curl);
		curl_close($curl);
		$data = json_decode($result, true);
		
		return $data;
		
	} else {  //  No key or invalid key format
		
		return FALSE;
		
	}

}



//  Detect if user has inserted any of our shortcodes on any posts or pages
function analyticsconnectio_shortcodes_detected() {
	
	$found = FALSE;
	$query_args = array('s' => '[analyticsconnect-io', 'post_type' => 'page');
	$query = new WP_Query($query_args);
	if ($query->have_posts()) { $found = TRUE; }
	$query_args = array('s' => '[analyticsconnect-io', 'post_type' => 'post');
	$query = new WP_Query($query_args);
	if ($query->have_posts()) { $found = TRUE; }
	return $found;
}


	
//  Tweak user data into what we display inside the WordPress Admin and update the WordPress database
function analyticsconnectio_get_displayable_user_data() {

	$options = get_option('analyticsconnectio_options');  //  Pull info from WP database
	
	$rawData = analyticsconnectio_get_user_data();
	
	if ($rawData == FALSE) {  //  We don't have any data because WordPress either doesn't have a key or the key has an invalid format
	
		$showData['status'] = '<span class="analyticsconnectio-warning">(Connection Error) Status Unknown</span>';
		
		if (strlen($options['infappname']) > 2) {
			$showData['infappname'] = '<span class="analyticsconnectio-warning">(Connection Error) Application ' . $options['infappname'] . '</span>';
		} else {
			$showData['infappname'] = '<span class="analyticsconnectio-warning">(Connection Error) Application Unknown</span>';
		}
		
		if (strlen($options['gaua']) > 2) {
			$showData['gaua'] = '<span class="analyticsconnectio-warning">(Connection Error) Currently set to ' . $options['gaua'] . '</span>';
		} else {
			$showData['gaua'] = '<span class="analyticsconnectio-warning">(Connection Error) No Tracking ID currently set</span>';
		}
			
	}  else {  //  We got data back
		
		if ($rawData['status'] == 'error') {  //  Key lookup failed on the server
			
			$showData['status'] = '<span class="analyticsconnectio-failure">Secret Key Error</span>';
			
			if (strlen($options['infappname']) > 2) {
				$showData['infappname'] = '<span class="analyticsconnectio-warning">(Connection Error) Application ' . $options['infappname'] . '</span>';
			} else {
				$showData['infappname'] = '<span class="analyticsconnectio-warning">(Connection Error) Application Unknown</span>';
			}
			
			if (strlen($options['gaua']) > 2) {
				$showData['gaua'] = '<span class="analyticsconnectio-warning">(Connection Error) Currently set to ' . $options['gaua'] . '</span>';
			} else {
				$showData['gaua'] = '<span class="analyticsconnectio-warning">(Connection Error) No Tracking ID currently set</span>';
			}

		} else {  //  We got good data back
		
			if ($rawData['status'] == 'active') { $showData['status'] = '<span class="analyticsconnectio-okay">Active</span>'; } else
			if ($rawData['status'] == 'trialing') { $showData['status'] = '<span class="analyticsconnectio-okay">Active (Free Trial)</span>'; } else
			if ($rawData['status'] == 'past_due') { $showData['status'] = '<span class="analyticsconnectio-warning">Active (Your payment is past due!)</span>'; } else
			if ($rawData['status'] == 'canceled') { $showData['status'] = '<span class="analyticsconnectio-failure">Account Canceled</span>'; }
			
			if ($rawData['infappname'] == NULL) {
				if (strlen($options['infappname']) > 2) {
					$showData['infappname'] = '<span class="analyticsconnectio-warning">(Connection Error) Application ' . $options['infappname'] . '</span>';
				} else {
					$showData['infappname'] = '<span class="analyticsconnectio-warning">(Connection Error) Application Unknown</span>';
				}
			} else {
				$showData['infappname'] = '<span class="analyticsconnectio-okay">Application ' . $rawData['infappname'] . '</span>';
				//  SAVE $rawData['infappname'] to WP DB
				$options = get_option('analyticsconnectio_options');  //  Pull info from WP database
				$options['infappname'] = $rawData['infappname'];
				update_option('analyticsconnectio_options', $options);
			}
			
			if ($rawData['gaua'] == NULL) {
				if (strlen($options['gaua']) > 2) {
					$showData['gaua'] = '<span class="analyticsconnectio-warning">(Connection Error) Currently set to ' . $options['gaua'] . '</span>';
				} else {
					$showData['gaua'] = '<span class="analyticsconnectio-warning">(Connection Error) No Tracking ID currently set</span>';
				}
			} else {
				$showData['gaua'] = '<span class="analyticsconnectio-okay">' . $rawData['gaua'] . '</span>';
				//  SAVE $rawData['gaua'] to WP DB
				$options = get_option('analyticsconnectio_options');  //  Pull info from WP database
				$options['gaua'] = $rawData['gaua'];
				update_option('analyticsconnectio_options', $options);
			}
			
		}
	}
	
	if (analyticsconnectio_shortcodes_detected()) {
		$showData['shortcodestatus'] = '<span class="analyticsconnectio-okay">Detected</span>';
	} else {
		$showData['shortcodestatus'] = '<span class="analyticsconnectio-warning">(WordPress Warning) No shortcodes detected</span>';
	}
	
	return $showData;
	
}



?>