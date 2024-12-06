=== WP101 Video Tutorial Plugin ===
Contributors: shawndh, markjaquith, mordauk, JustinSainton, wpsmith, bhwebworks, liquidweb
Tags: wp101, tutorials, video, help, learn
Requires at least: 5.1
Requires PHP: 7.4
Tested up to: 6.7.1
Stable tag: 5.4.1
License: GPLv2 or later

Professional video tutorials for WordPress, WooCommerce, Elementor, and more, right in the dashboard of your WordPress site. Perfect for beginners.

== Description ==

The WP101® Video Tutorial Plugin is simply the easiest way to teach your clients WordPress basics, cutting your support costs while providing an invaluable resource for your clients. It delivers a library of professionally-produced, WordPress 101 tutorial videos directly within your client’s own dashboard.

In addition to video tutorials for WordPress (both Gutenberg and Classic Editor), we're continually expanding our library with video tutorials for the most popular WordPress plugins, including WooCommerce, Elementor, Beaver Builder, Ninja Forms, and WPForms.

Simply enter your [WP101Plugin.com](https://wp101plugin.com/) API key to display our WordPress tutorial videos within your client’s WordPress administration panel.

You can choose which tutorial videos to show in the list, or even embed your own custom videos.

Stop wasting your valuable time teaching WordPress to your clients. Let the WP101 Plugin free your time to do what you do best!

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

Yes! You can add your own custom videos, and they'll appear at the bottom of the list of tutorial videos. Visit the “Custom Videos” page in the [WP101 Plugin app](https://app.wp101plugin.com/custom-topics).

= What if I have the Classic Editor installed? =

If the Classic Editor plugin is also installed and activated on your site, the previous version of our WordPress 101 videos for the Classic Editor in WordPress 4.9 and older will also appear in the list. You can hide or show these videos in the Settings.

= Why aren’t the videos for WooCommerce, WPForms, etc. showing up? =

The tutorial videos for WooCommerce, Ninja Forms, WPForms and other plugins will only appear in the list if the plugin in question is also installed and activated on the same site. No sense showing videos that don’t apply to a particular site, right?

= The plugin was installed by my developer, but their API key has expired. What do I do? =

You can ask your developer to renew their subscription, or you can go to [WP101Plugin.com](https://wp101plugin.com/) to start your own Personal subscription and get access to all of our videos.

= Can I hardcode my API key into the plugin for use across multiple installations?  =

Yes! Simply define the `WP101_API_KEY` constant within your `wp-config` file:

    /**
     * API key for the WP101 plugin.
     *
     * @link https://wp101plugin.com
     */
    define( 'WP101_API_KEY', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' );

== Screenshots ==

1. The video tutorial selection and viewing interface.
2. Add your own custom videos and deploy them to one or more sites.
3. Choose to hide or show individual videos—or an entire series—on a per-domain basis.
4. Manage all your client domains remotely, from one dashboard.
5. With an Agency Plan, you can also embed the 31-part WordPress 101 video series on the front-end of your membership site.
6. Use one API key across all your sites, or generate unique API keys as needed.

== Changelog ==

For a complete list of changes, please see [the plugin's GitHub repository](https://github.com/leftlane/wp101plugin/blob/master/CHANGELOG.md).

= 5.4.1 =
* Fix: Deprecated notice in PHP 8.1+

= 5.4.0 =
* Tested and verified for WordPress 6.7.1
* Fix: Major performance issues when the plugin is activated. 👈
* Fix: The custom videos not showing up in the list of videos.
* Replaced the old API endpoints with the new ones.
* Code cleanup and other minor bug fixes.

= 5.3.2 =
* Tested and verified for WordPress 6.7.0
* Changing the source of videos (internal)

= 5.3.1 =
* Tested and verified for WordPress 6.0.1
* Resolved warning in PHP 8

= 5.3 =
* Tested and verified for WordPress 5.7.
* Updated name for better discoverability.

= 5.2 =
* Tested and verified for WordPress 5.3.
* Updated screenshots and WP101® branding.

= 5.1.0 =
* Run migrations across a multisite network via a background task.
* Store public API keys based on the site URL, enabling better handling of domain changes.
* Add the `wp101_excluded_topics` filter.

= 5.0.1 =
* Ensure that legacy API keys are exchanged before making any other API requests.

= 5.0.0 =
* Complete rewrite of the plugin and backing APIs to bring even more content to the WP101 plugin.
* Custom videos, course visibility, and permissions are now controlled via [the WP101 Plugin app](https://app.wp101plugin.com).

== Upgrade Notice ==

= 5.3.1 =
* Tested and verified for WordPress 6.0.1
* Resolved warning in PHP 8

= 5.3 =
* Tested and verified for WordPress 5.7, plus some minor housekeeping.

= 5.2 =
* Tested and verified for WordPress 5.3, plus some minor housekeeping.

= 5.1.0 =
* Improves migration behavior on WordPress multisite instances.

= 5.0.1 =
* Resolves an issue some subscribers were seeing during API key migration.

= 5.0.0 =
* We’ve completely redesigned the WP101 Plugin from the ground-up, adding brand new features and improving the entire experience.

= 4.2.1 =
* In addition to whether or not the Classic Editor plugin is installed and activated, this minor fix also checks to see if filters are being used to disable Gutenberg. If so, display the previous version of our WordPress 101 videos instead of the new videos for Gutenberg and 5.0. Thanks, Cliff Seal!

= 4.2 =
* Re-added the previous WordPress 101 videos for the Classic Editor in WordPress 4.9 or older, provided the Classic Editor plugin is also installed and activated.

= 4.1 =
* Brand new WordPress 101 video tutorial series, completely rewritten for the all-new Gutenberg Block Editor in WordPress 5.0!

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
