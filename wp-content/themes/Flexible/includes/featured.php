<?php
	$featured_slider_class = '';
	if ( 'on' == et_get_option('flexible_slider_auto') ) $featured_slider_class .= ' et_slider_auto et_slider_speed_' . et_get_option('flexible_slider_autospeed');
	if ( 'slide' == et_get_option('flexible_slider_effect') ) $featured_slider_class .= ' et_slider_effect_slide';
?>
<div id="featured" class="flexslider<?php echo esc_attr( $featured_slider_class ); ?>">
	<ul class="slides">
	<?php
		$featured_cat = et_get_option('flexible_feat_cat');
		$featured_cat_term = get_term_by( 'name', $featured_cat, 'project_category' );

		$featured_num = et_get_option('flexible_featured_num');

		if (et_get_option('flexible_use_pages','false') == 'false') {
			if ( 'on' == et_get_option('flexible_use_posts','false') )
				$featured_query = new WP_Query( array(
					'posts_per_page' => (int) $featured_num,
					'cat' => (int) get_catId( et_get_option('flexible_feat_posts_cat') ),
				) );
			else
				$featured_query = new WP_Query( array(
					'post_type' => 'project',
					'posts_per_page' => (int) $featured_num,
					'tax_query' => array(
						array(
							'taxonomy' => 'project_category',
							'field' => 'id',
							'terms' => (array) $featured_cat_term->term_id,
							'operator' => 'IN'
						)
					),
				) );
		} else {
			global $pages_number;

			if (et_get_option('flexible_feat_pages') <> '') $featured_num = count(et_get_option('flexible_feat_pages'));
			else $featured_num = $pages_number;

			$et_featured_pages_args = array(
				'post_type' => 'page',
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'posts_per_page' => (int) $featured_num,
			);

			if ( is_array( et_get_option( 'flexible_feat_pages', '', 'page' ) ) )
				$et_featured_pages_args['post__in'] = (array) array_map( 'intval', et_get_option( 'flexible_feat_pages', '', 'page' ) );

			$featured_query = new WP_Query( $et_featured_pages_args );
		}

		while ($featured_query->have_posts()) : $featured_query->the_post(); ?>
			<li class="slide">
				<a href="<?php echo esc_url( get_permalink() ); ?>">
					<?php
						$width = (int) apply_filters( 'slider_image_width', 960 );
						$height = (int) apply_filters( 'slider_image_height', 360 );
						$title = get_the_title();
						$thumbnail = get_thumbnail($width,$height,'',$title,$title,false,'Featured');
						$thumb = $thumbnail["thumb"];

						if ( '' != $thumb ) {
							print_thumbnail($thumb, $thumbnail["use_timthumb"], $title, $width, $height, '');
						} else {
							$media = get_post_meta( get_the_ID(), '_et_used_images', true );
							if ( $media ){
								foreach( (array) $media as $et_media ){
									if ( is_numeric( $et_media ) ) {
										$et_fullimage_array = wp_get_attachment_image_src( $et_media, 'full' );
										if ( $et_fullimage_array ){
											$et_fullimage = $et_fullimage_array[0];
											echo '<img src="' . esc_attr( et_new_thumb_resize( et_multisite_thumbnail($et_fullimage ), $width, $height, '', true ) ) . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" alt="' . esc_attr( $title ) . '" />';
										}
										break;
									} else {
										continue;
									}
								}
							}
						}
					?>
				</a>
			</li>
	<?php
		endwhile; wp_reset_postdata();
	?>
	</ul>
</div> <!-- end #featured -->