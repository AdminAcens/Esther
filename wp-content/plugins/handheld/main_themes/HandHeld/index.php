<?php
	$et_is_ajax_request = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	if ( $et_is_ajax_request ) {
		get_template_part('includes/entry','index_async');
		die();
	}
?>
<?php get_header(); ?>

	<?php get_template_part('includes/breadcrumbs','index'); ?>

	<?php get_template_part('includes/entry','index'); ?>

<?php get_footer(); ?>