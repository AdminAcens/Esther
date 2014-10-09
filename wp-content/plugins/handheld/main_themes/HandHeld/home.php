<?php get_header(); ?>
<?php global $et_mobile_theme_options; ?>

<?php if ( isset( $et_mobile_theme_options['home_front_page'] ) && 0 != $et_mobile_theme_options['home_front_page'] ){ ?>
	<?php 
		query_posts( 'page_id=' . $et_mobile_theme_options['home_front_page'] );
		get_template_part('loop','page');
		wp_reset_query();
	?>
<?php } else { ?>
	<?php
		$et_show_home_welcome_section = isset( $et_mobile_theme_options['home_welcome_section'] ) ? (bool) $et_mobile_theme_options['home_welcome_section'] : true;
		if ( isset( $et_mobile_theme_options['home_intro_page'] ) && $et_show_home_welcome_section ){
			$welcome_query = new WP_Query( 'page_id=' . $et_mobile_theme_options['home_intro_page'] );
			while ( $welcome_query->have_posts() ) : $welcome_query->the_post(); ?>
				<section id="welcome" class="text_block">
					<h1><?php the_title(); ?></h1>
					<?php the_content(''); ?>
				</section> <!-- end #main-top-shadow -->
	<?php	
			endwhile;
			wp_reset_postdata();
		}
	?>

	<?php $et_show_home_recentposts_section = isset( $et_mobile_theme_options['home_recentposts_section'] ) ? (bool) $et_mobile_theme_options['home_recentposts_section'] : true;  ?>
	<?php if ( $et_show_home_recentposts_section ){ ?>
		<section id="recent_posts">
			<h1 class="small_title"><?php esc_html_e('Recent Posts','HandHeld'); ?></h1>
			
			<?php
				$posts_query_args = array( 'posts_per_page' => '3' );
				
				if ( isset( $et_mobile_theme_options['home_blog_posts_num'] ) && '' != $et_mobile_theme_options['home_blog_posts_num'] ) $posts_query_args['posts_per_page'] = (int) $et_mobile_theme_options['home_blog_posts_num'];
				
				if ( isset( $et_mobile_theme_options['home_blog_categories'] ) && !empty( $et_mobile_theme_options['home_blog_categories'] ) )
					$posts_query_args['category__in'] = $et_mobile_theme_options['home_blog_categories'];
				
				$posts_query = new WP_Query( $posts_query_args );
				while ( $posts_query->have_posts() ) : $posts_query->the_post(); ?>
					<?php et_mobile_regular_post(); ?>
			<?php
				endwhile;
				wp_reset_postdata();
			?>
			
			<?php if ( $posts_query->post_count < $posts_query->found_posts ){ ?>
				<?php $et_posts_per_page = isset( $et_mobile_theme_options['home_blog_posts_add_num'] ) && '' != $et_mobile_theme_options['home_blog_posts_add_num'] ? $et_mobile_theme_options['home_blog_posts_add_num'] : 5; ?>
				<div class="more_posts">
					<a href="#" class="load_more" data-et-offset="<?php echo esc_attr( $posts_query->post_count ); ?>" data-et-posts-per-page="<?php echo esc_attr( $et_posts_per_page ); ?>"><span><?php esc_html_e('Load More Posts','HandHeld'); ?></span></a>
				</div> <!-- end .more_posts -->
			<?php } ?>
		</section> <!-- end #recent_posts -->
	<?php } ?>

	<?php $et_show_home_recentwork_section = isset( $et_mobile_theme_options['home_recentwork_section'] ) ? (bool) $et_mobile_theme_options['home_recentwork_section'] : true;  ?>
	<?php if ( $et_show_home_recentwork_section ){ ?>
		<section id="recent_work">
			<h1 class="small_title"><?php esc_html_e('Recent Work','HandHeld'); ?></h1>
			<div class="text_block clearfix">
				<?php
					$projects_query_args = array( 'posts_per_page' => '6' );
					
					if ( isset( $et_mobile_theme_options['home_projects_posts_num'] ) && '' != $et_mobile_theme_options['home_projects_posts_num'] ) $projects_query_args['posts_per_page'] = (int) $et_mobile_theme_options['home_projects_posts_num'];
					
					if ( isset( $et_mobile_theme_options['home_project_categories'] ) && !empty( $et_mobile_theme_options['home_project_categories'] ) )
						$projects_query_args['category__in'] = $et_mobile_theme_options['home_project_categories'];
					
					$projects_query = new WP_Query( $projects_query_args );
					
					while ( $projects_query->have_posts() ) : $projects_query->the_post(); ?>
						<?php et_mobile_gallery_post(); ?>
				<?php
					endwhile;
					wp_reset_postdata(); 
				?>	
			</div> <!-- end .text_block -->
			
			<?php if ( $projects_query->post_count < $projects_query->found_posts ){ ?>
				<?php $et_projects_per_page = isset( $et_mobile_theme_options['home_projects_posts_add_num'] ) && '' != $et_mobile_theme_options['home_projects_posts_add_num'] ? $et_mobile_theme_options['home_projects_posts_add_num'] : 6; ?>
				<div class="more_posts">
					<a href="#" class="load_more" data-et-offset="<?php echo esc_attr( $projects_query->post_count ); ?>" data-et-posts-per-page="<?php echo esc_attr( $et_projects_per_page ); ?>"><span><?php esc_html_e('View More Gallery Entries','HandHeld'); ?></span></a>
				</div> <!-- end .more_posts -->
			<?php } ?>
		</section>
	<?php } ?>
<?php } ?>
<?php get_footer(); ?>