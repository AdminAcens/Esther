<!doctype html>
<html>
<head>
	<meta charset="utf-8">

	<title><?php wp_title( '|', true, 'right' );
	bloginfo( 'name' );
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description"; ?></title>

	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320"/>
	
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />

	<meta http-equiv="cleartype" content="on">

	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
	
	<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php
		global $et_mobile;
	?>
	<div id="container">
		<header id="main_header" role="banner">
			<?php do_action( 'handheld_header' ); ?>
			<div id="logo-area">
				<a href="<?php echo home_url(); ?>">
					<?php 
						global $et_mobile_theme_options;
						if ( isset( $et_mobile_theme_options['logo'] ) && '' != $et_mobile_theme_options['logo'] ) $logo = $et_mobile_theme_options['logo'];
						else
							$logo = (get_option($et_mobile->get_real_theme_shortname().'_logo') <> '') ? esc_attr(get_option($et_mobile->get_real_theme_shortname().'_logo')) : get_template_directory_uri() . '/images/logo.png'; 
					?>
					<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" id="logo"/>
				</a>
			</div> <!-- end #logo-area -->
			<div id="nav_bar">
				<div id="nav_bottom_shadow">
					<div id="nav_bar_top_bg">
						<a id="main_menu_link" href="#"><?php esc_html_e('Menu','HandHeld'); ?><span></span></a>
						<?php $menuClass = 'main_nav';
						$primaryNav = '';
									
						$primaryNav = wp_nav_menu( array( 'theme_location' => 'handheld-menu', 'container' => '', 'fallback_cb' => 'wp_page_menu', 'menu_class' => $menuClass, 'echo' => false ) );
						
						if ($primaryNav == '') { ?>
							<ul class="<?php echo $menuClass; ?>">
								<?php if (get_option($et_mobile->get_real_theme_shortname().'_home_link') == 'on') { ?>
									<li <?php if (is_front_page()) echo('class="current_page_item"') ?>><a href="<?php bloginfo('url'); ?>"><?php esc_html_e('Home','HandHeld'); ?></a></li>
								<?php }; ?>

								<?php show_categories_menu($menuClass,false); ?>
								
								<?php show_page_menu($menuClass,false,false); ?>
							</ul> <!-- end ul.nav -->
						<?php }
						else echo($primaryNav); ?>
						
						<div id="search-form">
							<form method="get" id="searchform" action="<?php echo home_url(); ?>/">
								<input type="text" value="<?php esc_attr_e('Search this site...', 'HandHeld'); ?>" name="s" id="searchinput" />
								<input type="image" src="<?php bloginfo('template_directory'); ?>/images/search_btn.png" id="searchsubmit" />
							</form>
						</div> <!-- end #search-form -->
					</div> <!-- end #nav_bar_top_bg -->
				</div> <!-- end #nav_bottom_shadow -->
			</div> <!-- end #nav_bar -->
		</header> <!-- end #main-header -->
		<div id="main" role="main">
			<div id="main-top-shadow">
			<?php do_action( 'handheld_main_area' ); ?>