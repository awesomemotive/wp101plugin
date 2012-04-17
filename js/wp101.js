(function($){
	$('#wp101-topic-listing').ready(function(){
		$('#wp101-topic-listing li small.wp101-hide a').click(function(){
			$(this).parents('li.wp101-shown').removeClass('wp101-shown').addClass('wp101-hidden');
			$.post( ajaxurl, {
				_wpnonce: $(this).data('nonce'),
				action: 'wp101-showhide-topic',
				direction: 'hide',
				topic_id: $(this).data('topic-id')
			});
		});
		$('#wp101-topic-listing li small.wp101-show a').click(function(){
			$(this).parents('li.wp101-hidden').removeClass('wp101-hidden').addClass('wp101-shown');
			$.post( ajaxurl, {
				_wpnonce: $(this).data('nonce'),
				action: 'wp101-showhide-topic',
				direction: 'show',
				topic_id: $(this).data('topic-id')
			});
		});
	});
})(jQuery);
