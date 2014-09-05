=== WP101 ===
Contributors: shawndh, markjaquith, mordauk, JustinSainton, wpsmith
Tags: wp101, tutorials, video, help, learn, screencast
Requires at least: 3.2
Tested up to: 4.0
Stable tag: 3.0

Delivers a complete set of WordPress tutorial videos directly within the dashboard. Choose which videos to show, or add your own!

== Description ==

The WP101 Plugin is simply the easiest way to teach out clients how to use WordPress. It delivers a complete set of professionally-produced “WordPress 101” and WordPress SEO Plugin tutorial videos directly within your clients' dashboard.

Simply enter your [WP101Plugin.com](http://wp101plugin.com/) API key to deliver WordPress tutorial videos within the WordPress administration panel. Perfect for teaching your clients the basics of how to use WordPress!

Selectively choose which tutorial videos are shown, and even embed your own custom videos!

NEW! Now includes a complete set of tutorial videos for the WordPress SEO Plugin by Yoast, provided that plugin is also installed on the site. Videos for other popular plugins coming soon!

== Installation ==

1. Go to [WP101Plugin.com](http://wp101plugin.com/) and subscribe.
2. Copy your API key from your [WP101Plugin.com](http://wp101plugin.com/) account page.
3. Upload the `wp101` directory to the `wp-content/plugins/` directory.
4. Activate the plugin through the 'Plugins' menu in WordPress.
5. Go to WP101 &rarr; Settings, and enter your API key.
6. Selectively hide/show individual videos from the list.
7. Add your own custom videos to the list using simple embed fields.

== Frequently Asked Questions ==

= How do I get an API key? =

Simply go to: [WP101Plugin.com](http://wp101plugin.com/) and follow the instructions to set up an API key in less than a minute.

= Can I choose which video topics are displayed? =

Yes! You can selectively hide or show individual tutorial videos. Simply go to the Settings panel to choose which videos you'd like to include.

= Can I add my own custom videos? =

Yes! You can add your own custom videos, and they'll appear at the bottom of the list of tutorial videos, along with the WP101 videos. Visit the Settings panel to add new videos by simply pasting the video embed code from your video hosting provider.

= The plugin was installed by my developer, but their API key has expired. What do I do? =

You can ask your developer to renew their subscription, or you can go to [WP101Plugin.com](http://wp101plugin.com/) to start your own subscription and get access to updated content.

= Can I hardcode my API key into the plugin for use across multiple installations?  =

Yes! Simply enter your API key into the wp101.php file and then install your customized version of the plugin across your clients' sites. Or, if you prefer, define $_wp101_api_key within your wp-config file. Either way, your API key will be preserved when you upgrade to future versions of the plugin.

= Can I filter the list of videos, or add my own programmatically? =

Absolutely! We've added the following filters for developers to add (or even remove) videos from the plugin.

 * wp101_get_help_topics
 * wp101_get_custom_help_topics
 * wp101_get_hidden_topics

== Screenshots ==

1. The video tutorial selection and viewing interface.
2. The configuration interface, where you can enter your API key, hide videos from the list, or even add your own custom videos.

== Changelog ==

= 3.0 =
* We’ve added videos for the WordPress SEO Plugin by Yoast, provided that plugin is installed.
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

= 3.0 =
* We’ve added videos for the WordPress SEO Plugin by Yoast, provided that plugin is installed.
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
