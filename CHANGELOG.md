# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html) as of version 5.0.0.

## [5.0.0]
* Complete rewrite of the plugin and backing APIs to bring even more content to the WP101 plugin.
* Custom videos, course visibility, and permissions are now controlled via [the WP101 Plugin app](https://app.wp101plugin.com).

## 4.2.1
* In addition to whether or not the Classic Editor plugin is installed and activated, this minor fix also checks to see if filters are being used to disable Gutenberg. If so, display the previous version of our WordPress 101 videos instead of the new videos for Gutenberg and 5.0. Thanks, Cliff Seal!

## 4.2
* Re-added the old WordPress 101 videos for the Classic Editor, provided that plugin is also installed and activated.
* Added function `get_wpclassic_topics`

## 4.1
* Brand new WordPress 101 video tutorial series, completely rewritten for the all-new Gutenberg Block Editor in WordPress 5.0!

## 4.0.2
* Return the ‘plugin_action_links_’ filter argument in all cases. Previously, it was only returned if the authorization check succeeded, causing errors in some edge-cases.

## 4.0.1
* Transient for get_help_topics was shortened for testing, but left in the last release. It's now good for a day. Nothing to see here. Move along.

## 4.0
* Jetpack and WooCommerce videos are now included, for a total of 90 tutorial videos!
* Collapsible sections to make the long list of videos more manageable.
* Added a Settings link on the Plugins page, if user is authorized.
* Minor CSS revisions and bug fixes.

## 3.2.3
* Updated for new translation system on WordPress.org.

## 3.2.2
* Minor changes to description verbiage and fixed a tiny typo.
* Tested and verified for WordPress 4.3!

## 3.2.1
* Changed title to reflect the new name of the Yoast SEO plugin.

## 3.2
* Updated the Yoast SEO plugin videos for version 2.0.
* Tested and verified for WordPress 4.2!

## 3.1
* By popular request, we’ve now added the ability to limit access to the settings panel to a specific administrator.
* We've also added several new filters to facilitate overrides for this new feature. See the FAQ for documentation on these new filters. Thanks, Justin Sainton!
* Last, we’ve assigned the plugin instance to a (global) variable, to make it accessible outside the plugin for modifications. Thanks, John Sundberg!

## 3.0.4
* Bug fixes for hiding and showing all the Yoast SEO videos. Thanks, Justin Sainton!

## 3.0.3
* Added more detailed docs on the built-in hooks to filter the list of videos, or even add your own. Thanks, Justin Sainton!

## 3.0.2
* CSS bug fix for Firefox.

## 3.0.1
* Bug fix for unexpected T_PAAMAYIM_NEKUDOTAYIM error on PHP 5.2 and older.

## 3.0
* We’ve added videos for the Yoast SEO plugin, provided that plugin is installed.
* Added new filters for developers. You can now filter the topics and videos returned on wp101_get_help_topics and wp101_get_custom_help_topics.
* Increased the default size of the video player, plus added responsive support for all your devices!
* Minor coding standards cleanup.

## 2.1.1
* Bug fix for missing wp101_icon_url error.

## 2.1
* Updated for WordPress 3.8, including new menu icon.

## 2.0.6
* Bug fix for missing api_key_notset_message.

## 2.0.5
* Fixed issue with hiding the first video.

## 2.0.4
* Replaced mentions of "WP101" with "Video Tutorials"
* Replaced icons with a more generic icon.
* Removed "Part 1," "Part 2," etc. from video titles.
* Updated screenshots.

## 2.0.3
* Fix to ensure hardcoded API keys are not lost on upgrade.

## 2.0.2
* Bug fix to address "API key not valid" error on multisite installations.
* Removed redundant notification when API key is not set.

## 2.0.1
* Minor fix to ensure the actively-playing video title is bold.

## 2.0
* Added the ability to selectively choose which videos appear in the list.
* Added the ability to add your own custom videos to the list.

## 1.1.1
* Minor change to ensure hardcoded API keys are written to the database.
* Added a small icon to the menu item to help it be more easily visible.

## 1.1
* Moved WP101 to its own separate menu item at the bottom of the navigation menu.
* Changed the API Key input field to a password field, instead of a regular text field.
* Granted permissions for logged-in Subscribers to view the videos.

## 1.0.1
* Minor bug fix for multisite installations.

## 1.0
* First version!

[5.0.0]: https://github.com/leftlane/wp101plugin/releases/tag/v5.0.0
