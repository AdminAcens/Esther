(function($){
	$(document).ready(function(){
		et_skip_iphone_bar();
		
		$('body.home #recent_posts a.load_more, body.home #recent_work a.load_more').live('click', function(){
			var $et_more_button = $(this).addClass('load_more_ajax'),
				$et_gallery = $et_more_button.parents('#recent_work').find('.text_block');
			$.ajax({
				type: "POST",
				url: etmobile.ajaxurl,
				data:
				{
					action : 'et_show_ajax_posts',
					et_load_nonce : etmobile.et_load_nonce,
					et_gallery: $et_gallery.length,
					et_posts_num : $(this).attr('data-et-posts-per-page'),
					et_posts_offset : $(this).attr('data-et-offset')
				},
				success: function(data){
					var response = JSON.parse(data),
						$response_elements = $(response.posts).css({'opacity':'0','position':'relative','top':-30});
					
					if ( $et_gallery.length ) $et_more_button.parent().siblings('.text_block').append($response_elements);
					else $et_more_button.parent().before($response_elements);
					
					$response_elements.animate({opacity:1,top:0}, 700);
					
					if ( response.last_query ) $et_more_button.hide();
					
					$et_more_button.attr('data-et-offset', parseInt($et_more_button.attr('data-et-offset'))+parseInt($et_more_button.attr('data-et-posts-per-page')));
					
					$et_more_button.removeClass('load_more_ajax');
				}
			});
			
			return false;
		});
		
		$('body.archive a.load_more, body.search a.load_more').live('click', function(){
			var $et_more_index_button = $(this).addClass('load_more_ajax')
			$.post( $(this).attr('href'), {}, function( data ) {
					$et_more_index_button.parent().remove();
					var $et_append_to = $( ".et_handheld_gallery" ).length ? $( ".et_handheld_gallery" ) : $( "#main-top-shadow" );
					$et_append_to.append( $( data ) );
				}
			);
			return false;
		});
		
		$('#main_menu_link').click(function(){
			var $et_menu_arrow = $(this).find('span'),
				menu_open_class = 'menu_open';
			$(this).siblings('.main_nav').slideToggle(700);
			if ( $et_menu_arrow.hasClass( menu_open_class ) ) $et_menu_arrow.removeClass( menu_open_class );
			else $et_menu_arrow.addClass( menu_open_class );
			return false;
		});
		
		
		var $searchform = $('#nav_bar #search-form'),
			$searchinput = $searchform.find("input#searchinput"),
			searchvalue = $searchinput.val();
				
		$searchinput.focus(function(){
			if ($(this).val() === searchvalue) $(this).val("");
		}).blur(function(){
			if ($(this).val() === "") $(this).val(searchvalue);
		});
		
		var $comment_form = jQuery('form#commentform');

		$comment_form.find('input, textarea').each(function(index,domEle){
			var $et_current_input = jQuery(domEle),
				$et_comment_label = $et_current_input.siblings('label'),
				et_comment_label_value = $et_current_input.siblings('label').text();
			if ( $et_comment_label.length ) {
				$et_comment_label.hide();
				if ( $et_current_input.siblings('span.required') ) { 
					et_comment_label_value += $et_current_input.siblings('span.required').text();
					$et_current_input.siblings('span.required').hide();
				}
				$et_current_input.val(et_comment_label_value);
			}
		}).live('focus',function(){
			var et_label_text = jQuery(this).siblings('label').text();
			if ( jQuery(this).siblings('span.required').length ) et_label_text += jQuery(this).siblings('span.required').text();
			if (jQuery(this).val() === et_label_text) jQuery(this).val("");
		}).live('blur',function(){
			var et_label_text = jQuery(this).siblings('label').text();
			if ( jQuery(this).siblings('span.required').length ) et_label_text += jQuery(this).siblings('span.required').text();
			if (jQuery(this).val() === "") jQuery(this).val( et_label_text );
		});

		$comment_form.find('input#submit').click(function(){
			if (jQuery("input#url").val() === jQuery("input#url").siblings('label').text()) jQuery("input#url").val("");
		});
		
		// http://remysharp.com/2010/08/05/doing-it-right-skipping-the-iphone-url-bar/
		function et_skip_iphone_bar(){
			/iPhone/i.test(navigator.userAgent) && !location.hash && setTimeout(function () {
				if (!pageYOffset) window.scrollTo(0, 1);
			}, 1000);
		}
	});
})(jQuery)