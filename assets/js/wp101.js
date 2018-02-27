(function (wp101) {
	'use strict';

	var topics = document.querySelectorAll('.wp101-video'),

	/**
	 * Render a WP101 media object into its player.
	 */
	renderVideo = function () {
		if (200 !== this.status) {
			return;
		}

		this.wp101.player.src = URL.createObjectURL(this.response);
	},

	/**
	 * Load a WP101 video into its player.
	 *
	 * @param HTMLElement el - The figure.wp101-video node.
	 */
	loadTopic = function (el) {
		var player = el.querySelector('.wp101-video-player'),
			xhr = new XMLHttpRequest();

		if (! player || ! player.dataset.mediaSrc) {
			return;
		}

		xhr.open('GET', player.dataset.mediaSrc);
		xhr.onload = renderVideo;
		xhr.responseType = 'blob';
		xhr.setRequestHeader('Authorization', 'Bearer ' + wp101.apiKey);
		xhr.wp101 = {
			player: player
		};
		xhr.send();
	};

	topics.forEach(loadTopic);
}(window.wp101));
