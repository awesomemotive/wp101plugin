/**
 * Scripts for the WP101 settings screen within WP Admin.
 *
 * @package WP101
 */

(function () {
	'use strict';

	var settingsForm = document.getElementById('wp101-settings-api-key-form'),
		settingsDisplay = document.getElementById('wp101-settings-api-key-display');

	// Abort if we're not on the WP101 Settings screen.
	if (! settingsForm) {
		return;
	}

	/*
	 * If an API key has already been set, we'll display a masked version.
	 *
	 * Clicking the button within settingsDisplay will replace the display with the previously-
	 * hidden form.
	 */
	if (settingsDisplay && settingsForm.classList.contains('hide-if-js')) {
		settingsForm.setAttribute('hidden', '');
		settingsForm.classList.remove('hide-if-js');

		settingsDisplay.addEventListener('click', function (e) {
			if ('BUTTON' !== e.target.tagName) {
				return;
			}

			settingsDisplay.setAttribute('hidden', '');
			settingsForm.removeAttribute('hidden');
		});
	}
}());
