=== WP101 ===
Contributors: shawndh, markjaquith, mordauk, JustinSainton, wpsmith, bhwebworks, liquidweb
Tags: wp101, tutorials, video, help, learn, screencast
Requires at least: 3.2
Requires PHP: 5.4
Tested up to: 4.9.5
Stable tag: 4.1

A complete set of WordPress, Jetpack, WooCommerce, and Yoast SEO tutorial videos directly within the dashboard. Choose which videos to show, or add your own!

== Description ==

The WP101 Plugin is simply the easiest way to teach your clients WordPress basics, cutting your support costs while providing an invaluable resource for your clients. It delivers a complete set of professionally-produced “WordPress 101” tutorial videos directly within your client’s dashboard!

The WP101 Plugin also includes a complete set of tutorial videos for WooCommerce, Jetpack, Yoast SEO, and MailPoet, provided those plugins are also installed on the site. Videos for other popular plugins are in the works.

Simply enter your [WP101Plugin.com](https://wp101plugin.com/) API key to display our WordPress tutorial videos within your client’s WordPress administration panel.

You can choose which tutorial videos are shown, and even embed your own custom videos!

Stop wasting your valuable time teaching WordPress to your clients. Let the WP101 Plugin free your time to focus on what you do best!

== Installation ==

1. Go to [WP101Plugin.com](https://wp101plugin.com/) to get your API key.
2. Copy your API key from your [WP101Plugin.com](https://app.wp101plugin.com/) dashboard.
3. Install and activate the WP101 Plugin in the 'Plugins' panel.
4. Go to the Video Tutorials menu item and click the Settings button to enter your API key.

== Frequently Asked Questions ==

= How do I get an API key? =

Simply go to: [WP101Plugin.com](https://wp101plugin.com/) and follow the instructions to set up an API key in less than a minute.

= Can I choose which video topics are displayed? =

Yes! You can selectively hide or show individual tutorial videos (or entire courses) through the app at [WP101Plugin.com](https://app.wp101plugin.com).

= Can I add my own custom videos? =

Yes! You can add your own custom videos, and they'll appear at the bottom of the list of tutorial videos. Visit the ["Custom Videos" page in the [WP101Plugin.com app](https://app.wp101plugin.com/custom-topics).

= Where are the Jetpack, WooCommerce, Yoast SEO, or MailPoet videos? =

The tutorial videos for Jetpack, WooCommerce, Yoast SEO, and MailPoet will only appear in the list if the plugin in question is also installed and activated on the same site. No sense showing videos that don’t apply to a particular site, now is there?

= The plugin was installed by my developer, but their API key has expired. What do I do? =

No sweat! Just go to [WP101Plugin.com](https://wp101plugin.com/) to start your own subscription and get access to all of our videos.

= Can I hardcode my API key into the plugin for use across multiple installations?  =

Yes! Simply define the `WP101_API_KEY` constant within your `wp-config` file:

	/**
	 * API key for the WP101 plugin.
	 *
	 * @link https://wp101plugin.com
	 */
	define( 'WP101_API_KEY', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' );

= I've been using the WP101 Plugin for some time now — will upgrading to 5.x break my site? =

We've made every effort to ensure a smooth transition to version 5.x of the WP101 plugin from earlier versions:

* Upon upgrading, your existing API key will be exchanged for a new API key automatically.
	- If you've defined the API key via the `WP101_API_KEY` constant, you'll be given instructions for updating the value.
* Hidden courses and videos will automatically be passed to the WP101 Plugin app as part of the exchange, and will be reflected automatically. No more having to filter out videos!

== Screenshots ==

1. The video tutorial selection and viewing interface.

== Changelog ==
= 5.0.0 =
* Complete rewrite of the plugin and backing APIs to bring even more content to the WP101 plugin.
* Custom videos, course visibility, and permissions are now controlled via [the WP101 Plugin app](https://app.wp101plugin.com).

= 4.1 =
* We’ve added videos for the MailPoet plugin, provided that plugin is installed and activated.

= 4.0.2 =
* Return the ‘plugin_action_links_’ filter argument in all cases. Previously, it was only returned if the authorization check succeeded, causing errors in some edge-cases.

= 4.0.1 =
* Transient for get_help_topics was shortened for testing, but left in the last release. It's now good for a day. Nothing to see here. Move along.

= 4.0 =
* Jetpack and WooCommerce videos are now included, for a total of 90 tutorial videos!
* Collapsible sections to make the long list of videos more manageable.
* Added a Settings link on the Plugins page, if user is authorized.
* Minor CSS revisions and bug fixes.

= 3.2.3 =
* Updated for new translation system on WordPress.org.

= 3.2.2 =
* Minor changes to description verbiage and fixed a tiny typo.
* Tested and verified for WordPress 4.3!

= 3.2.1 =
* Changed title to reflect the new name of the Yoast SEO plugin.

= 3.2 =
* Updated the Yoast SEO plugin videos for version 2.0.
* Tested and verified for WordPress 4.2!

= 3.1 =
* By popular request, we’ve now added the ability to limit access to the settings panel to a specific administrator.
* We've also added several new filters to facilitate overrides for this new feature. See the FAQ for documentation on these new filters. Thanks, Justin Sainton!
* Last, we’ve assigned the plugin instance to a (global) variable, to make it accessible outside the plugin for modifications. Thanks, John Sundberg!

= 3.0.4 =
* Bug fixes for hiding and showing all the Yoast SEO videos. Thanks, Justin Sainton!

= 3.0.3 =
* Added more detailed docs on the built-in hooks to filter the list of videos, or even add your own. Thanks, Justin Sainton!

= 3.0.2 =
* CSS bug fix for Firefox.

= 3.0.1 =
* Bug fix for unexpected T_PAAMAYIM_NEKUDOTAYIM error on PHP 5.2 and older.

= 3.0 =
* We’ve added videos for the Yoast SEO plugin, provided that plugin is installed.
* Added new filters for developers. You can now filter the topics and videos returned on wp101_get_help_topics and wp101_get_custom_help_topics.
* Increased the default size of the video player, plus added responsive support for all your devices!
* Minor coding standards cleanup.

= 2.1.1 =
* Bug fix for missing wp101_icon_url error.

= 2.1 =
* Updated for WordPress 3.8, including new menu icon.

= 2.0.6 =
* Bug fix for missing api_key_notset_message.

= 2.0.5 =
* Fixed issue with hiding the first video.

= 2.0.4 =
* Replaced mentions of "WP101" with "Video Tutorials"
* Replaced icons with a more generic icon.
* Removed "Part 1," "Part 2," etc. from video titles.
* Updated screenshots.

= 2.0.3 =
* Fix to ensure hardcoded API keys are not lost on upgrade.

= 2.0.2 =
* Bug fix to address "API key not valid" error on multisite installations.
* Removed redundant notification when API key is not set.

= 2.0.1 =
* Minor fix to ensure the actively-playing video title is bold.

= 2.0 =
* Added the ability to selectively choose which videos appear in the list.
* Added the ability to add your own custom videos to the list.

= 1.1.1 =
* Minor change to ensure hardcoded API keys are written to the database.
* Added a small icon to the menu item to help it be more easily visible.

= 1.1 =
* Moved WP101 to its own separate menu item at the bottom of the navigation menu.
* Changed the API Key input field to a password field, instead of a regular text field.
* Granted permissions for logged-in Subscribers to view the videos.

= 1.0.1 =
* Minor bug fix for multisite installations.

= 1.0 =
* First version!

== Upgrade Notice ==
= 4.1 =
* We’ve added videos for the MailPoet plugin, provided that plugin is installed and activated.

= 4.0.2 =
* Minor bug fix.

= 4.0.1 =
* Minor fix.

= 4.0 =
* This is a big one! The WP101 Plugin now includes videos for WooCommerce and Jetpack, provided those plugins are also installed. Plus a few more goodies.

= 3.2.2 =
* Minor changes to description verbiage and fixed a tiny typo.
* Tested and verified for WordPress 4.3!

= 3.2.1 =
* Changed title to reflect the new name of the Yoast SEO plugin.

= 3.2 =
* We’ve updated the Yoast SEO plugin videos for version 2.0.
* Tested and verified for WordPress 4.2!

= 3.1 =
* This important update adds the ability to limit access to the settings panel to a specific administrator, plus adds several new filters for this new feature. Thanks, Justin Sainton!
* We’ve also assigned the plugin instance to a (global) variable, to make it accessible outside the plugin for modifications. Thanks, John Sundberg!

= 3.0.4 =
* Bug fixes for hiding and showing all the SEO videos. Thanks, Justin Sainton!

= 3.0.3 =
* Added more detailed docs on the built-in hooks to filter the list of videos, or even add your own!

= 3.0 =
* We’ve added videos for the Yoast SEO plugin, provided that plugin is installed.
* Added new filters for developers. You can now filter the topics and videos returned on wp101_get_help_topics and wp101_get_custom_help_topics.
* Increased the default size of the video player, plus added responsive support for all your devices!
* Minor coding standards cleanup.

= 2.1.1 =
* Bug fix for missing wp101_icon_url error.

= 2.1 =
* Updated for WordPress 3.8, including new menu icon.

= 2.0.6 =
* Bug fix for missing api_key_notset_message.

= 2.0.5 =
* Bug fix to address issue when hiding the first video in the list.

= 2.0.4 =
* Minor changes to improve the white-labeled experience.

= 2.0.3 =
* Fix to ensure hardcoded API keys are not lost on upgrade.

= 2.0.2 =
* Bug fix to address "API key not valid" error on multisite installations.
* Removed redundant notification when API key is not set.

= 2.0.1 =
* Minor fix to ensure the actively-playing video title is bold.

= 2.0 =
* Includes the ability to hide individual videos.
* Includes he ability to add your own custom videos.
* Compatibility with WordPress 3.4.

= 1.1.1 =
* Minor fix related to hardcoded API keys. Added a custom menu icon.

= 1.1 =
* Made API Key input field more secure, and a couple of widely-requested minor changes.

= 1.0.1 =
* Minor bug fix for multisite installations.

= 1.0 =
First version!
