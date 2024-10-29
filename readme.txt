=== AnalyticsConnect.io - Google Analytics Ecommerce for Infusionsoft ===
Contributors: dkadmin233
Tags: analytics, google analytics, ecommerce, infusionsoft, adwords conversion tracking, facebook conversion tracking
Requires at least: 3.5.1
Tested up to: 6.2
Stable tag: trunk

Easily adds AnalyticsConnect.io functionality to your WordPress site.

== Description ==

AnalyticsConnect.io is the easy solution for getting your Infusionsoft sales data into Google Analytics Ecommerce, Google AdWords Conversion Tracking, Facebook Ads Conversion Tracking, and Bing Ads Conversion Tracking.  This plugin makes installing AnalyticsConnect.io to your WordPress site fast and easy.

== Installation ==

You can either install it automatically from the WordPress admin, or do it manually:

1. Unzip the archive and upload the "analytics-connect-google-analytics-ecommerce-for-infusionsoft" folder to the "/wp-content/plugins/" directory.
2. Activate the plugin from the Plugins menu.
3. Go to the "Settings" menu and type in your AnalyticsConnect.io Key.

= Usage =

1. Place the "[analyticsconnect-io]" shortcode on all sales transaction thank-you pages.

== Screenshots ==

1. Settings Page

== Frequently Asked Questions ==

= What version of Google Analytics is this for? =

This is built for Google's newest system: Google Universal Analytics running Enhanced Ecommerce. If you're running an older version, you'll need to upgrade before using our software.

== Changelog ==

= 2.4.1 (2023-01-20) =
* Changed API endpoint URLs to new system.

= 2.4.0 (2018-10-10) =
* Added cross-domain tracking support for infusionsoft.app domains.

= 2.3.0 (2017-04-23) =
* Added support for Bing Ads Conversion Tracking.
* Added support for PlusThis One-Click Upsell.
* Send AnalyticsConnect.io traffic source cookie with online sale data if available.

= 2.2.1 (2016-05-06) =
* Bug fix (for future compatibility).

= 2.2.0 (2016-01-14) =
* Added support for the Cookie Vault.
* Added shortcode detection on the status dashboard.

= 2.1.1 (2015-02-26) =
* Replaced deprecated PHP function split() with explode().

= 2.1.0 (2014-12-01) =
* Enabled API.  Attributes can now be added to the shortcode to overwrite default settings on a page by page basis.

= 2.0.3 (2014-11-02) =
* Updated the look of the status dashboard.

= 2.0.2 (2014-10-31) =
* Reading POST and GET vars for OrderID is now case insensitive.

= 2.0.1 (2014-10-29) =
* Fixed small bug with CSS on WP Admin page.

= 2.0.0 (2014-10-29) =
* Plugin code rebuilt from the ground up for the new AnalyticsConnect.io system (version 2 of our software).
* All PHP solution (no Javascript) for better reliability.
* Using OAuth2 for the connection to Infusionsoft (no need to update our settings if you change your Infusionsoft password).
* Infusionsoft Promo Code written to Google Analytics as part of the order data.
* Added support for Facebook Conversion Tracking.

= 1.0.3 (2014-05-12) =
* Added link for Premium Support

= 1.0.2 (2014-04-07) =
* Removed unused public function new_key_from_file()

= 1.0.1 (2014-03-30) =
* Removed optional "key.php" file from install package

= 1.0.0 (2014-03-30) =
* First release
