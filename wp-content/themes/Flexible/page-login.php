<?php
/*
Template Name: Login Page
*/
?>
<?php
	$et_ptemplate_settings = array();
	$et_ptemplate_settings = maybe_unserialize( get_post_meta(get_the_ID(),'et_ptemplate_settings',true) );

	$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : false;
?>

<?php get_header(); ?>

<?php get_template_part('includes/breadcrumbs', 'page'); ?>

<div id="content-area" class="clearfix<?php if ( $fullwidth ) echo ' fullwidth'; ?>">
	<div id="left-area">
		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class('entry clearfix'); ?>>
				<h1 class="page_title"><?php the_title(); ?></h1>

				<?php
					$thumb = '';
					$width = (int) apply_filters('et_blog_image_width',640);
					$height = (int) apply_filters('et_blog_image_height',320);
					$classtext = '';
					$titletext = get_the_title();
					$thumbnail = get_thumbnail($width,$height,$classtext,$titletext,$titletext,false,'Blogimage');
					$thumb = $thumbnail["thumb"];
				?>
				<?php if ( '' != $thumb && 'on' == et_get_option('flexible_page_thumbnails') ) { ?>
					<div class="post-thumbnail">
						<?php print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext); ?>
					</div> 	<!-- end .post-thumbnail -->
				<?php } ?>

				<div class="post-content">
					<?php the_content(); ?>

					<div id="et-login" class="responsive">
						<div class='et-protected'>
							<div class='et-protected-form'>
								<?php $scheme = apply_filters( 'et_forms_scheme', null ); ?>

								<form action='<?php echo esc_url( home_url( '', $scheme ) ); ?>/wp-login.php' method='post'>
									<p><label><span><?php esc_html_e('Username','Flexible'); ?>: </span><input type='text' name='log' id='log' value='<?php echo esc_attr($user_login); ?>' size='20' /><span class='et_protected_icon'></span></label></p>
									<p><label><span><?php esc_html_e('Password','Flexible'); ?>: </span><input type='password' name='pwd' id='pwd' size='20' /><span class='et_protected_icon et_protected_password'></span></label></p>
									<input type='submit' name='submit' value='Login' class='etlogin-button' />
								</form>
							</div> <!-- .et-protected-form -->
						</div> <!-- .et-protected -->
					</div> <!-- end #et-login -->

					<div class="clear"></div>

					<?php wp_link_pages(array('before' => '<p><strong>'.esc_attr__('Pages','Flexible').':</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
					<?php edit_post_link(esc_attr__('Edit this page','Flexible')); ?>
				</div> 	<!-- end .post-content -->
			</article> <!-- end .entry -->
		<?php endwhile; // end of the loop. ?>
	</div> <!-- end #left_area -->

	<?php if ( ! $fullwidth ) get_sidebar(); ?>
</div> 	<!-- end #content-area -->

<?php get_footer(); ?>