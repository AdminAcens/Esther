<?php
	global $paged, $wp_query;
	if ( !$paged ) $paged = 1;
	
	$et_is_ajax_request = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	
	if ( $et_is_ajax_request || ( 1 == $paged && !$et_is_ajax_request ) ) {
		
		if ( $paged != $wp_query->max_num_pages ) { ?>
			<div class="more_posts">
				<a href="<?php echo esc_url( next_posts( $wp_query->max_num_pages, false ) ); ?>" class="load_more"><span><?php esc_html_e('Load More Posts','HandHeld'); ?></span></a>
			</div> <!-- end .more_posts -->
	<?php 
		}
	} else { ?>
		<div class="pagination clearfix">
			<div class="alignleft"><?php next_posts_link(esc_html__('&laquo; Older Entries','HandHeld')) ?></div>
			<div class="alignright"><?php previous_posts_link(esc_html__('Next Entries &raquo;', 'HandHeld')) ?></div>
		</div>
<?php } ?>