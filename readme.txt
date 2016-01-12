=== WP101 ===
Contributors: shawndh, markjaquith, mordauk, JustinSainton, wpsmith, bhwebworks
Tags: wp101, tutorials, video, help, learn, screencast
Requires at least: 3.2
Tested up to: 4.4
Stable tag: 3.2.3

Delivers a complete set of WordPress tutorial videos directly within the dashboard. Choose which videos to show, or add your own!

== Description ==

The WP101 Plugin is simply the easiest way to teach your clients how to use WordPress, cutting your support costs while providing an invaluable resource to your clients. It delivers a complete set of professionally-produced “WordPress 101” and WordPress SEO Plugin tutorial videos directly within your clients' dashboard.

Simply enter your [WP101Plugin.com](https://wp101plugin.com/) API key to deliver WordPress tutorial videos within the WordPress administration panel. Perfect for teaching your clients the basics of how to use WordPress!

Selectively choose which tutorial videos are shown, and even embed your own custom videos!

Plus, the WP101 Plugin also includes a complete set of tutorial videos for the Yoast SEO plugin, provided that plugin is also installed on the site. Videos for other popular plugins coming soon!

== Installation ==

1. Go to [WP101Plugin.com](https://wp101plugin.com/) to get your API key.
2. Copy your API key from your [WP101Plugin.com](https://wp101plugin.com/) account page.
3. Install and activate the WP101 Plugin in the 'Plugins' panel.
5. Go to WP101 &rarr; Settings, and enter your API key.
6. Selectively hide/show individual videos from the list.
7. Add your own custom videos to the list using simple embed fields.

== Frequently Asked Questions ==

= How do I get an API key? =

Simply go to: [WP101Plugin.com](https://wp101plugin.com/) and follow the instructions to set up an API key in less than a minute.

= Can I choose which video topics are displayed? =

Yes! You can selectively hide or show individual tutorial videos. Simply go to the Settings panel to choose which videos you'd like to include.

= Can I add my own custom videos? =

Yes! You can add your own custom videos, and they'll appear at the bottom of the list of tutorial videos, along with the WP101 videos. Visit the Settings panel to add new videos by simply pasting the video embed code from your video hosting provider.

= Why aren’t the Yoast SEO videos showing up? =

The tutorial videos for the Yoast SEO plugin will only appear in the list if that plugin is also installed on the same site. No sense showing videos that don’t apply to a particular site, now is there?

= The plugin was installed by my developer, but their API key has expired. What do I do? =

You can ask your developer to renew their subscription, or you can go to [WP101Plugin.com](https://wp101plugin.com/) to start your own subscription and get access to updated content.

= Can I hardcode my API key into the plugin for use across multiple installations?  =

Yes! Simply enter your API key into the `wp101.php` file and then install your customized version of the plugin across your clients' sites.

Or, if you prefer, define the `$_wp101_api_key` variable within your `wp-config` file:

`define('WP101_API_KEY', 'XXXXXXXXXXXX');`

Either way you choose, your API key will be preserved when you upgrade to future versions of the plugin.

= Can I limit access to the settings panel? =

Yes! By default, all administrators have access to the settings panel. Optionally, you may choose a specific administrator who alone will have access to the settings panel.

We've also added a series of filters to allow for a couple helpful scenarios:

* `wp101_is_user_authorized` - allows a developer to override the authorization routine. A great use case would be if someone's client has their user set to be the only admin, but the developer also needs to access the settings. Filtering this conditionally would allow for a whitelist of sorts.
* `wp101_default_settings_role` - When counting admins, we default to counting the administrators. This filter can be used in conjunction with the `wp101_settings_management_user_args` filter to change the actual role that we're allowing for. A good example might be a site that actually has no administrator roles, but a custom role, like a store manager or something.
* `wp101_too_many_admins` - This provides a sane default for what we consider to be too many admins for this UX. Drop-downs are pretty crappy when you're dealing with a bunch of options, so we have a super high limit of 100. This can be changed to whatever one desires.
* `wp101_settings_management_user_args` - Used in conjunction with `wp101_default_settings_role`, this filters the array of arguments passed to `get_users()` to populate the drop-down.

= Can I filter the list of videos, or add my own programmatically? =

Absolutely! The WP101 Plugin comes with a number of helpful filters for adding, removing, or modifying existing videos from a number of functions.  We'll walk through some of them, showing what you can do with them…

= wp101_get_help_topics =

The `wp101_get_help_topics` filter is applied to the output of the `get_help_topics()` method.  This supplies all of the default videos for the WP101 plugin.  This filter, and indeed all of the filters, is passed an array of videos that looks something very much like the following:

    php
    array(
	1 => array(
		'id'      => 1,
		'title'   => 'The Dashboard',
		'content' => '<iframe src="//player.vimeo.com/video/104639801" width="1280" height="720" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>'
	),
	2 => array(
		'id'      => 2,
		'title'   => 'Posts vs. Pages',
		'content' => '<iframe src="//player.vimeo.com/video/81744178" width="1280" height="720" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>'
	),
	3 => array(
		'id'      => 3,
		'title'   => 'The Editor',
		'content' => '<iframe src="//player.vimeo.com/video/81743148" width="1280" height="720" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>'
	)
    );

Suppose you have a site where you aren't using any posts or pages.  Not inconceivable, as you might be entirely dependent upon custom post types for a specific build.  It would make great sense in this situation to remove the _Posts vs. Pages_ video, as it would be irrelevant.  Here's how you might do that:

    php
    add_filter( 'wp101_get_help_topics', function( $videos ) {

	unset( $videos[2] );
	return $videos;

    } );

And voila!  No more _Posts vs. Pages_ video in the core help topics.  Cool, right?

= wp101_get_custom_help_topics =

Maybe you have a really great plugin that you've made some instructional videos for, or someone else has made some tutorials that you'd like to include in the WP101 interface.  That's awesome! The `wp101_get_custom_help_topics` filter is applied to the output of the `get_custom_help_topics()` method, which outputs custom videos directly after the core videos, if any exist. Here's an example of how you might add a custom help topic.

    php
    add_filter( 'wp101_get_custom_help_topics', function( $custom_videos ) {

	$custom_videos['myplugin.1'] => array(
		'id'      => 'myplugin.1',
		'title'   => 'General Helpful Stuff',
		'content' => '<iframe src="//player.vimeo.com/video/12345678" width="1280" height="720" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>'
	);

	return $custom_videos;
    } );

And just like that, you have your own custom video inside WP101.

= wp101_get_hidden_topics =

Say you want to hide a video - not necessarily remove it completely from WP101, but have it hidden by default, rather than shown.  There's a filter for that:

    php
    add_filter( 'wp101_get_hidden_topics', function( $hidden_videos ) {
	// As in the first example, we might want to hide the Posts vs. Pages video.  Instead of the whole array, we add the topic ID.
	$topic_id = 2;

	if ( ! in_array( $topic_id, $hidden_videos ) ) {
		$hidden_videos[] = $topic_id;
	}

	return $hidden_videos;
    } );

And there we go, we've added a video to the hidden topics. Pretty sweet, right?

_Note: All code examples are using anonymous functions, which work in PHP 5.3+.  If you're using anything less than PHP 5.3, you have our condolences.  Change the examples to use declared functions instead, unless you have a penchant for white._


== Screenshots ==

1. The video tutorial selection and viewing interface.
2. The configuration interface, where you can enter your API key, hide videos from the list, or even add your own custom videos.

== Changelog ==

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
* Bug fixes for hiding and showing all the SEO videos. Thanks, Justin Sainton!

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
