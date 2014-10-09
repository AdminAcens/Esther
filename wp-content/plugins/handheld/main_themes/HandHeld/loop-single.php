<?php global $et_mobile_theme_options; ?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	<?php 
		$et_is_gallery_post = false;
		if ( isset( $et_mobile_theme_options['home_project_categories'] ) ){
			$et_post_categories = wp_get_post_categories( $post->ID );
			foreach ( $et_post_categories as $et_post_category ){
				if ( in_array( $et_post_category, $et_mobile_theme_options['home_project_categories'] ) ) $et_is_gallery_post = true;
			}
		}
	?>
	<article class="post text_block clearfix<?php if ( $et_is_gallery_post ) echo ' gallery_post'; ?>">
		<?php
			$thumb = '';
			$width = 72;
			$height = 72;
			$classtext = '';
			$titletext = get_the_title();
			$thumbnail = et_get_thumbnail($width,$height,$classtext,$titletext,$titletext,false,'Entry');
			$thumb = $thumbnail["thumb"];
		?>
		<?php if ( ! $et_is_gallery_post ){ ?>
			<?php if( $thumb <> '' ){ ?>
				<div class="post-thumb">
					<?php et_print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext); ?>
					<span class="overlay"></span>
					<span class="comment_count"><?php comments_popup_link( 0, 1, '%' ); ?></span>
				</div> <!-- end .post-thumb -->
			<?php } ?>
		<?php } ?>
		<div class="post-content">
			<h1><?php the_title(); ?></h1>
			<p class="meta-info"><?php esc_html_e('Posted on','HandHeld'); ?> <time datetime="<?php the_time( 'Y-m-d' ); ?>" pubdate><?php the_time( 'F jS' ); ?></time></p>
		</div> <!-- end .post-content -->
		
		<?php if ( $et_is_gallery_post ){ ?>
			<div class="gallery_item">
				<img src="<?php echo esc_attr( $thumb ); ?>" alt="<?php the_title(); ?>" />
				<span class="overlay"></span>
			</div>
		<?php } ?>
		
		<div class="main_post_text">
			<?php the_content(); ?>
			<?php wp_link_pages(array('before' => '<p><strong>'.esc_attr__('Pages','HandHeld').':</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
			<?php edit_post_link(esc_attr__('Edit this page','HandHeld')); ?>
		</div> <!-- end .main_post_text -->
	</article> <!-- end .post -->

	<?php
		$et_show_comments = isset( $et_mobile_theme_options['single_post_comments'] ) ? (bool) $et_mobile_theme_options['single_post_comments'] : true;
		if ( $et_show_comments ) comments_template('', true);
	?>
<?php endwhile; // end of the loop. ?>