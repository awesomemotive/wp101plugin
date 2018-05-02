/**
 * Scripting to save dismissed WP101 add-on notifications.
 */

(function ($) {
	'use strict';

	document.addEventListener('click', function (e) {
		if ('BUTTON' !== e.target.tagName || ! e.target.classList.contains('notice-dismiss')) {
			return;
		}

		var notice = e.target.parentElement,
			data = {
				action: 'wp101_dismiss_notice',
				addons: notice.dataset.wp101AddonSlug.split(','),
				nonce: wp101Addons.nonce
			};

		$.post(ajaxurl, data);
	});
}(jQuery));
