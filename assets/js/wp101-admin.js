(function ($) {
	'use strict';

	var $playlist = $('.wp101-playlist'),
		title = document.getElementById('wp101-player-title'),
		player = document.getElementById('wp101-player');

	/**
	 * Read window.location.hash and return the HTMLElement representing that topic
	 * in the playlist.
	 *
	 * If no match is found, return the first playlist item.
	 */
	function getCurrentTopic() {
		var hash = window.location.hash.substring(1),
			topic = document.querySelector('a[data-media-slug="' + hash + '"]');

		return topic || document.querySelector('.wp101-topics-list a');
	}

	/**
	 * Load a topic based on its playlist node.
	 *
	 * @param HTMLElement el - The playlist node.
	 */
	function loadTopic(el) {
		el = el || getCurrentTopic();

		if (! el) {
			return;
		}

		$playlist.find('a.active').removeClass('active');
		el.classList.add('active');

		player.src = el.dataset.mediaSrc;
		title.innerText = el.dataset.mediaTitle;
	}

	// Detect changes to window.location.hash.
	window.addEventListener('hashchange', function () {
		loadTopic();
	});

	// Enable jQuery accordion for list of series.
	$playlist.accordion({
		collapsible: true,
		header: '.wp101-series h2',
		heightStyle: 'content',
		activate: function () {
			sessionStorage.setItem('wp101ListState', $playlist.accordion('option', 'active'));
		},
		active: parseInt(sessionStorage.getItem('wp101ListState'), 10)
	});

	// Load the default topic.
	loadTopic();
}(jQuery));
