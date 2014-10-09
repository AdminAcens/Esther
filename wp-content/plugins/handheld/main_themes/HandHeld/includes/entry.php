<section id="recent_work">
	<?php
		global $et_mobile_theme_options;
		
		$et_cat_gallery = is_category() && isset( $et_mobile_theme_options['home_project_categories'] ) && !empty( $et_mobile_theme_options['home_project_categories'] ) && in_array( $cat, $et_mobile_theme_options['home_project_categories'] ) ? true : false;
		
		$et_is_ajax_request = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	?>
	<?php if ( $et_cat_gallery && ! $et_is_ajax_request ) echo '<div class="text_block et_handheld_gallery clearfix">'; ?>
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<?php if ( ! $et_cat_gallery ) { ?> 
			<?php et_mobile_regular_post(); ?>
		<?php } else { ?>
			<?php et_mobile_gallery_post(); ?>
		<?php } ?>
	<?php 
	endwhile; 
		get_template_part('includes/navigation','entry');
	else:
		get_template_part('includes/no-results','entry');
	endif; ?>
	<?php if ( $et_cat_gallery && ! $et_is_ajax_request ) echo '</div>'; ?>

</section>