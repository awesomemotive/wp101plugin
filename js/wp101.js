(function($){
	$(document).ready(function(){

		var maybeShowAll = function() {
			$.each( $( '.wp101-show-all' ), function() {
				var $this       = $( this ),
					$hide_link  = $( '.wp101-hide-all', $this.parent() ),
					$maybe_hide = $this.parent().next().find( '.wp101-show:visible' );

					if ( $maybe_hide.length < 1 ) {
						$this.hide();
						$hide_link.show();
					} else {
						$this.show();
						$hide_link.hide();
					}
			} );
		};

		maybeShowAll();

		$('ul.wp101-topic-ul li small.wp101-hide a').click(function(e){
			e.preventDefault();
			$(this).parents('li.wp101-shown').removeClass('wp101-shown').addClass('wp101-hidden');
			$.post( ajaxurl, {
				_wpnonce: $(this).data('nonce'),
				action: 'wp101-showhide-topic',
				direction: 'hide',
				topic_id: $(this).data('topic-id')
			}, maybeShowAll );
		});

		$('small.wp101-hide-all a').click(function(e){
			e.preventDefault();

			var $this       = $( this ),
				$show_link  = $( '.wp101-show-all', $this.parent().parent() ),
				$ul         = $this.parent().parent().next();

			$this.parent().hide();
			$show_link.show();

			$ul.find( '.wp101-show' ).show().parents('li.wp101-shown').removeClass('wp101-shown').addClass('wp101-hidden');
			$ul.find( '.wp101-hide' ).hide();

			$.post( ajaxurl, {
				_wpnonce : $this.data('nonce'),
				action   : 'wp101-showhide-topic',
				direction: 'hide-all',
				topic    : $this.data('topic')
			} );
		});

		$('ul.wp101-topic-ul li small.wp101-show a').click(function(e){
			e.preventDefault();
			$(this).parents('li.wp101-hidden').removeClass('wp101-hidden').addClass('wp101-shown');
			$.post( ajaxurl, {
				_wpnonce: $(this).data('nonce'),
				action: 'wp101-showhide-topic',
				direction: 'show',
				topic_id: $(this).data('topic-id')
			}, maybeShowAll);
		});

		$( 'small.wp101-show-all a' ).click(function(e){
			e.preventDefault();

			var $this       = $( this ),
				$hide_link  = $( '.wp101-hide-all', $this.parent().parent() ),
				$ul         = $this.parent().parent().next();

			$this.parent().hide();
			$hide_link.show();

			$ul.find( '.wp101-hide' ).show().parents('li.wp101-hidden').removeClass('wp101-hidden').addClass('wp101-shown');
			$ul.find( '.wp101-show' ).hide();

			$.post( ajaxurl, {
				_wpnonce: $(this).data('nonce'),
				action: 'wp101-showhide-topic',
				direction: 'show-all',
				topic : $(this).data('topic')
			} );
		});

		$('ul.wp101-topic-ul li small.wp101-delete a').click(function(e){
			e.preventDefault();
			$.post( ajaxurl, {
				_wpnonce: $(this).data('nonce'),
				action: 'wp101-delete-topic',
				topic_id: $(this).data('topic-id')
			});
			$(this).parents('li').remove();
		});



	});
})(jQuery);
