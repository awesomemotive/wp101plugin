(function ($) {
	'use strict';

	var playlist = $('.wp101-playlist');

	// Enable jQuery accordion for list of series.
	playlist.accordion({
		collapsible: true,
		header: 'h2',
		heightStyle: 'content',
		activate: function () {
			localStorage.setItem('wp101ListState', playlist.accordion('option', 'active'));
		},
		active: parseInt(localStorage.getItem('wp101ListState'), 10)
	});
}(jQuery));
