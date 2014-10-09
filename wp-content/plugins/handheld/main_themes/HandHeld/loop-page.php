<?php global $et_mobile_theme_options; ?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	<article class="post text_block clearfix">
		<?php
			$thumb = '';
			$width = 72;
			$height = 72;
			$classtext = '';
			$titletext = get_the_title();
			$thumbnail = et_get_thumbnail($width,$height,$classtext,$titletext,$titletext,false,'Entry');
			$thumb = $thumbnail["thumb"];
		?>
		<?php if( $thumb <> '' ){ ?>
			<div class="post-thumb">
				<?php et_print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext); ?>
				<span class="overlay"></span>
			</div> <!-- end .post-thumb -->
		<?php } ?>
		
		<h1><?php the_title(); ?></h1>
		
		<div class="main_post_text">
			<?php the_content(); ?>
			<?php wp_link_pages(array('before' => '<p><strong>'.esc_attr__('Pages','HandHeld').':</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
			<?php edit_post_link(esc_attr__('Edit this page','HandHeld')); ?>
		</div> <!-- end .main_post_text -->
	</article> <!-- end .post -->

	<?php
		$et_show_comments = isset( $et_mobile_theme_options['single_page_comments'] ) ? (bool) $et_mobile_theme_options['single_page_comments'] : true;
		if ( $et_show_comments ) comments_template('', true);
	?>
<?php endwhile; // end of the loop. ?>