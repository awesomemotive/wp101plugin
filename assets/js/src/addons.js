/**
 * Scripting to save dismissed WP101 add-on notifications.
 *
 * @package WP101
 */
/* global ajaxurl, wp101Addons */

(function ($) {
	'use strict';

	document.addEventListener('click', function (e) {
		if ('BUTTON' !== e.target.tagName || ! e.target.classList.contains('notice-dismiss')) {
			return;
		}

		$.post(ajaxurl, {
			action: 'wp101_dismiss_notice',
			addons: e.target.parentElement.dataset.wp101AddonSlug.split(','),
			nonce: wp101Addons.nonce
		});
	});
}(jQuery));
