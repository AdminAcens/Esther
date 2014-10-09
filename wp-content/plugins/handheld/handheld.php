<?php /*

**************************************************************************

Plugin Name:  HandHeld
Plugin URI:   http://elegantthemes.com
Description:  Adds mobile version of the site
Version:      1.3
Author:       ElegantThemes
Author URI:   http://elegantthemes.com

**************************************************************************

Copyright 2011 Elegant Themes, Inc.

This plugin is released under the GNU Public License 2.0. Read license.txt for more details.

**************************************************************************/

class Et_Mobile {
	var $main_themes_folder_name;
	var $child_themes_folder_name;
	var $use_child_themes;
	var $main_theme_name;
	var $child_theme_name;
	var $real_site_name;
	var $real_site_shortname;
	var $is_mobile;
	var $show_full_version;
	var $menu_page;
	var $_options_pagename = 'et_mobile_options';
	var $_options_user_can = 'manage_options';
	var $et_mobile_plugin_options;
	var $et_mobile_theme_options;
	var $theme_options_name;
	var $plugin_options_name;
	var $all_options;
	var $update_name = 'handheld/handheld.php';
	var $theme_changed = false;

	function Et_Mobile(){
		global $et_mobile_theme_options;
		
		/* Load the translation of the plugin. */
		load_plugin_textdomain( 'et_mobile', false, '/handheld/languages/' );
				
		$this->plugin_options_name = 'et_mobile_plugin_options';
		
		$this->et_mobile_plugin_options = get_option( $this->plugin_options_name );
		if ( false === $this->et_mobile_plugin_options ){
			$this->et_mobile_plugin_options = array(
				'main_theme' => 'HandHeld',
				'child_theme' => '0'
			);
			add_option( $this->plugin_options_name, $this->et_mobile_plugin_options );
		}
		
		$this->main_themes_folder_name = 'main_themes';
		$this->main_theme_name = $this->et_mobile_plugin_options['main_theme'];
		
		# store the main theme settings, using this option name
		$this->theme_options_name = 'et_mobile_' . strtolower( $this->main_theme_name ) . '_options';
		
		$this->et_mobile_theme_options = get_option( $this->theme_options_name );
		
		# this variable should be used in main/child theme files
		$et_mobile_theme_options = $this->et_mobile_theme_options;
		
		$this->child_themes_folder_name = 'child_themes';
		$this->child_theme_name = $this->et_mobile_plugin_options['child_theme'];
		
		$this->real_site_name = get_template();
		$this->real_site_shortname = strtolower( $this->real_site_name );
		
		$this->is_mobile = false;
		$this->show_full_version = false;
		
		$this->use_child_themes = ( '0' == $this->child_theme_name ) ? false : true;
		
		$this->check_mobile_view();
		
		define( 'ET_MOBILE_PARENT_THEMES_DIR', trailingslashit( dirname(__FILE__) ) . trailingslashit( $this->main_themes_folder_name ) );
		define( 'ET_MOBILE_CHILD_THEMES_DIR', trailingslashit( dirname(__FILE__) ) . trailingslashit( $this->child_themes_folder_name ) );
		
		if ( ( !is_admin() || ( is_admin() && defined('DOING_AJAX') && DOING_AJAX ) ) && $this->is_mobile && !$this->show_full_version ) {
			add_filter( 'template_directory', array( &$this, 'change_get_parent_directory' ) );
			add_filter( 'template_directory_uri', array( &$this, 'change_get_parent_directory_uri' ) );
			add_filter( 'stylesheet_directory', array( &$this, 'change_get_children_directory' ) );
			add_filter( 'stylesheet_directory_uri', array( &$this, 'change_get_children_directory_uri' ) );
			$this->remove_all_shortcodes();
			
			add_theme_support( 'post-thumbnails' );
		}
		
		if ( is_admin() ){
			add_action( 'init', array( &$this, 'register_mobile_menu' ) );
			add_action( 'admin_menu', array(&$this, 'create_menu_link') );
		}

		add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'check_plugin_updates' ) );
		add_filter( 'site_transient_update_plugins', array( &$this, 'add_plugin_to_update_notification' ) );
		add_action( 'admin_init', array( &$this, 'remove_plugin_update_info' ), 11 );
	}
	
	function create_menu_link()
    {
        $this->menu_page = add_options_page( __('ET Mobile Plugin Options','et_mobile'), __('ET Mobile Plugin','et_mobile'), $this->_options_user_can, $this->_options_pagename, array(&$this, 'build_settings_page'));
        add_action( "admin_print_scripts-{$this->menu_page}", array(&$this, 'settings_page_js') );
        add_action( "admin_print_styles-{$this->menu_page}", array(&$this, 'settings_page_css') );
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'), 10, 2 );
    }
	
	function settings_page_js(){
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'et_mobile_settings', plugins_url( 'js', __FILE__ ) . '/settings_page.js' );
	}
	
	function settings_page_css(){
		wp_enqueue_style( 'thickbox' );
	}
	
	function add_settings_link($links){
		$settings = '<a href="'.admin_url('options-general.php?page='.$this->_options_pagename).'">' . __('Settings') . '</a>';
		array_unshift( $links, $settings );
		return $links;
	}
	 
	function build_settings_page(){
        if ( !current_user_can( $this->_options_user_can ) ) {
            wp_die( __('You do not have sufficient permissions to access this page.', 'et_mobile') );
        }
		
		$this->save_plugin_options();
		
		$plugin_options = apply_filters( 'et_mobile_plugin_options', array(
			'et_section1' => array(
				'type' => 'heading',
				'title' => __('General settings','et_mobile')
			),
			'main_theme' => array(
				'type' => 'select',
				'title' => __('Main theme','et_mobile')
			),
			'child_theme' => array(
				'type' => 'select',
				'title' => __('Child theme','et_mobile'),
				'description' => sprintf( __('If you want to customize the theme, create a <a href="%1$s">child theme</a> in %2$s. Then you\'ll be able to select it.','et_mobile'),'http://codex.wordpress.org/Child_Themes', '<code>handheld/child_themes</code>' )
			)
		) );
		
		# main theme options are located in main_theme_name/includes/et_theme_options.php file
		$theme_options_filename = trailingslashit( dirname(__FILE__) ) . trailingslashit( $this->main_themes_folder_name ) . $this->main_theme_name . '/includes/et_theme_options.php';
		if ( file_exists( $theme_options_filename ) ) include( $theme_options_filename );
		else $theme_options = array();
		
		$this->all_options = array_merge( $plugin_options, $theme_options );
		
		$categories_option = array();
		$categories = get_categories();
		foreach ( $categories as $category ){
			$categories_option[$category->term_id] = $category->name;
		}
		
		$pages_option = array();
		$pages = get_pages();
		foreach ( $pages as $page ){
			$pages_option[$page->ID] = $page->post_title;
		}
		
		$this->save_options();
		
		$output = '<form method="post" action="' . admin_url('options-general.php?page='.$this->_options_pagename) . '">';
		$output .= wp_nonce_field( 'et-mobile-save_options', '_wpnonce', true, false );
		$output .= '<table class="form-table"><tbody>';
		
		foreach ( $this->all_options as $key => $option ){
			$settings = '';
			$option_element = ( ! in_array( $option['type'], array('heading', 'enable_disable') ) ) ? $this->wrap_option_title( $option['title'], $key ) : '';
			
			switch ( $option['type'] ){
				case 'heading' :
					$option_element .= '<tr><td><h3>' . esc_html( $option['title'] ) . '</h3></td></tr>';
					# we don't need to save heading as an option, so delete it
					unset( $this->all_options[$key] );
					break;
				case 'enable_disable' :
					$option_available = isset( $this->et_mobile_theme_options[$key] );
					
					$value = $option_available ? (int) $this->et_mobile_theme_options[$key] : 1;
					if ( !$option_available && isset( $option['std'] ) && 'disable' == $option['std'] ) $value = 0;
					
					$option_element .= '<tr><td colspan="2">' . '<label for="' . esc_attr( $key ) . '"><input type="checkbox" name="'. esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="'. esc_attr( $value ) . '" '. checked( $value, 1, false ) . ' /> ' . esc_html( $option['title'] ) . '</label></td></tr>';
					break;
				case 'select' :
					$settings = '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '">';
					
					if ( isset( $option['what_to_select'] ) ){
						if ( 'categories' == $option['what_to_select'] ) $values = $categories_option;
						if ( 'pages' == $option['what_to_select'] ) $values = $pages_option;
						
						$option_available = isset( $this->et_mobile_theme_options[$key] );
						
						if ( isset( $option['can_be_empty'] ) && $option['can_be_empty'] ) $settings .= '<option value="0">-- Select --</option>';
						
						foreach ( $values as $id_key => $value ){
							$selected = $option_available && $this->et_mobile_theme_options[$key] == $id_key ? ' selected="selected"' : '';
							$settings .= '<option value="'. esc_attr( $id_key ) . '"' . $selected . '>'.esc_html( $value ).'</option>';
						}
					} elseif ( 'main_theme' == $key ) {
						$themes = $this->get_theme_folders( ET_MOBILE_PARENT_THEMES_DIR );
						$active_theme = isset( $this->et_mobile_theme_options[$key] ) ? $this->et_mobile_theme_options[$key] : 'HandHeld';
						
						foreach ( $themes as $themename ){
							$settings .= '<option value="'.esc_attr( $themename ).'"' . selected( $active_theme, $themename, false ) . '>' . esc_html( $themename ) . '</option>';
						}
					} elseif ( 'child_theme' == $key ) {
						$themes = $this->get_theme_folders( ET_MOBILE_CHILD_THEMES_DIR );
						
						$active_theme = isset( $this->et_mobile_theme_options[$key] ) ? $this->et_mobile_theme_options[$key] : '0';
						
						$settings .= '<option value="0"' . selected( $active_theme, $themename, false ) . '>' . '-- Select --' . '</option>';
						
						foreach ( $themes as $themename ){
							$settings .= '<option value="'.esc_attr( $themename ).'"' . selected( $active_theme, $themename, false ) . '>' . esc_html( $themename ) . '</option>';
						}
					}
					
					$settings .= '</select>';
					
					if ( isset( $option['description'] ) ) $settings .= ' <span class="description">' . $option['description'] . '</span>';
					
					$option_element .= $this->wrap_option_settings( $settings );
					break;
				case 'checkboxes' :
					if ( isset( $option['what_to_select'] ) ){
						if ( 'categories' == $option['what_to_select'] ) $values = $categories_option;
						if ( 'pages' == $option['what_to_select'] ) $values = $pages_option;
						
						$option_available = isset( $this->et_mobile_theme_options[$key] );
						$checked = '';
						
						foreach ( $values as $id_key => $value ){
							$hash = esc_attr( $key . '_' . $id_key );
							$checked = ( $option_available && in_array( $id_key, $this->et_mobile_theme_options[$key] ) ) ? ' checked="checked"' : '';
							
							$settings .= '
								<label for="' . $hash . '">
									<input name="'.esc_attr( $key . '[]' ).'" type="checkbox" id="'.$hash.'" value="'.esc_attr($id_key). '"' . $checked . ' >' . esc_html( $value ) . 
								'</label>';
						}
					}
					$option_element .= $this->wrap_option_settings( $settings );
				
					break;
				case 'text':
					$class = isset( $option['small_input'] ) && $option['small_input'] ? 'small-text' : 'regular-text';
					
					$option_available = isset( $this->et_mobile_theme_options[$key] );
					$text_value = '';
					if ( $option_available && '' != $this->et_mobile_theme_options[$key] ) $text_value = $this->et_mobile_theme_options[$key];
					elseif ( isset( $option['std'] ) ) $text_value = $option['std'];
					
					$settings = '<input name="' . esc_attr( $key ) . '" type="text" id="' . esc_attr( $key ) . '" value="'.esc_attr( $text_value ).'" class="'.$class.'" />';
					
					if ( isset( $option['description'] ) ) $settings .= ' <span class="description">' . $option['description'] . '</span>';
					
					$option_element .= $this->wrap_option_settings( $settings );
					
					break;
				case 'upload' :
					$upload_value = isset( $this->et_mobile_theme_options[$key] ) ? $this->et_mobile_theme_options[$key] : '';
					$settings = '<input id="'.esc_attr( $key ).'" class="uploadfield regular-text" type="text" name="'.esc_attr( $key ).'" value="'.esc_url( $upload_value ).'">';
					$settings .= '<a class="upload_image_button button" href="#">' . __('Upload Image','et_mobile') . '</a>';
					
					if ( isset( $option['description'] ) ) $settings .= ' <span class="description">' . $option['description'] . '</span>';
					
					$option_element .= $this->wrap_option_settings( $settings );
					break;
			}
			
			$output .= ( ! in_array( $option['type'], array('heading', 'enable_disable') ) ) ? '<tr valign="top">' . $option_element . '</tr>' : $option_element;
		}
		
		$output .= '</tbody></table>' . get_submit_button() . '</form>';
		
		echo '<div class="wrap">' . $output . '</div>';
	}
	
	function save_options(){
		if ( isset( $_REQUEST['submit'] ) ){
			check_admin_referer( 'et-mobile-save_options' );
			
			$options_to_save = array();
			foreach( $this->all_options as $key_option => $main_option ){
				if ( isset( $_REQUEST[ $key_option ] ) ) {
					$options_to_save[ $key_option ] = ( is_array( $_REQUEST[ $key_option ] ) ) ? stripslashes_deep( array_map('esc_html', $_REQUEST[ $key_option ] ) ) : stripslashes( esc_html( $_REQUEST[ $key_option ] ) );
					if ( 'enable_disable' == $main_option['type'] ) $options_to_save[ $key_option ] = 1;
				}
				elseif ( 'checkboxes' == $main_option['type'] ) $options_to_save[ $key_option ] = array();
				elseif ( 'enable_disable' == $main_option['type'] ) $options_to_save[ $key_option ] = 0;
			}
			
			if ( ! $this->theme_changed ) {
				$this->et_mobile_theme_options = $options_to_save;
				update_option( $this->theme_options_name, $this->et_mobile_theme_options );
			}
			
			echo '<div id="message" class="updated fade"><p><strong>' . __('Settings saved.','et_mobile') . '</strong></p></div>';
		}
	}
	
	function save_plugin_options(){
		if ( isset( $_REQUEST['submit'] ) ){
			check_admin_referer( 'et-mobile-save_options' );
			
			$this->et_mobile_plugin_options = array(
				'main_theme' => esc_html( $_REQUEST['main_theme'] ),
				'child_theme' => esc_html( $_REQUEST['child_theme'] )
			);
			if ( $_REQUEST['main_theme'] != $this->main_theme_name ) $this->theme_changed = true;
			
			update_option( $this->plugin_options_name, $this->et_mobile_plugin_options );
			
			$this->main_theme_name = $this->et_mobile_plugin_options['main_theme'];
			$this->child_theme_name = $this->et_mobile_plugin_options['child_theme'];
			
			# store the main theme settings, using this option name
			$this->theme_options_name = 'et_mobile_' . strtolower( $this->main_theme_name ) . '_options';
			
			$this->et_mobile_theme_options = get_option( $this->theme_options_name );
			$this->et_mobile_theme_options['main_theme'] = $this->main_theme_name;
		}
	}
	
	function remove_all_shortcodes(){ 
		$et_shortcodes = array( 'digg', 'stumble', 'facebook', 'buzz', 'twitter', 'feedburner', 'retweet', 'protected', 'box', 'tooltip', 'learn_more', 'button', 'slide', 'tabs', 'tabcontainer', 'imagetabcontainer', 'imagetabtext', 'tabtext', 'tabcontent','tab','imagetab','author', 'author_image','author_info','pricing_table','custom_list','pricing','feature','dropcap','testimonial','quote','one_half','one_half_last','one_third','one_third_last','one_fourth','one_fourth_last','two_third','two_third_last','three_fourth','three_fourth_last' );
		
		foreach( $et_shortcodes as $shortcode ){
			add_shortcode( $shortcode, array( &$this, 'create_empty_shortcode' ) );
		};
	}
	
	function create_empty_shortcode( $atts, $content = null, $shortcode_name ){
		$output = '';
		if ( 'dropcap' == $shortcode_name ) $output = $content;
		
		return apply_filters( 'et_empty_shortcode_result', $output, $shortcode_name, $content, $atts );
	}
	
	function get_theme_folders( $scan_dir ){
		$folders = array();
		$directory = @ opendir( $scan_dir );
		
		if ( $directory ) {
			while ( ( $file = readdir( $directory ) ) !== false ) {
				if ( substr($file, 0, 1) == '.' )
					continue;
				if ( is_dir( trailingslashit( $scan_dir ) . $file ) )
					$folders[] = $file;
			}
			closedir( $directory );
		}
		
		return $folders;
	}

	function wrap_option_title( $setting_title, $key ){
		return '<th scope="row"><label for="' . esc_attr( $key ) . '">' . esc_html( $setting_title ) . '</label></th>';
	}
	
	function wrap_option_settings( $settings ){
		return '<td>' . $settings . '</td>';
	}
	
	function change_get_parent_directory( $directory ){
		return ET_MOBILE_PARENT_THEMES_DIR . $this->main_theme_name;
	}

	function change_get_parent_directory_uri( $directory_uri ){
		return plugins_url( trailingslashit( $this->main_themes_folder_name ) . $this->main_theme_name, __FILE__ );
	}

	function change_get_children_directory( $directory ){
		$new_directory = $this->use_child_themes ? ET_MOBILE_CHILD_THEMES_DIR . $this->child_theme_name : ET_MOBILE_PARENT_THEMES_DIR . $this->main_theme_name;
		
		return $new_directory;
	}

	function change_get_children_directory_uri( $directory_uri ){
		$new_directory = $this->use_child_themes ? plugins_url( trailingslashit( $this->child_themes_folder_name ) . $this->child_theme_name, __FILE__ ) : plugins_url( trailingslashit( $this->main_themes_folder_name ) . $this->main_theme_name, __FILE__ );
		
		return $new_directory;
	}
	
	function get_real_theme_shortname(){
		return $this->real_site_shortname;
	}
	
	# Exit Mobile mode / Mobile version
	function mobile_mode_link(){
		echo '<div id="mobile_options"><a id="exit_mobile" href="?et_no_mobile=1">' . __('Exit Mobile Mode','et_mobile') . '</a></div><!-- end #mobile_options -->';
	}
	
	# check if the site is viewed on a mobile device
	function check_mobile_view(){
		$this->is_mobile = preg_match( '/' . apply_filters( 'et_mobile_regex','android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino|htc' ) . '/i', $_SERVER['HTTP_USER_AGENT'] ) ? true : false;
		#$this->is_mobile = true; // temp

		if ( ( isset( $_COOKIE['et_no_mobile'] ) && '1' == $_COOKIE['et_no_mobile'] && ! ( isset( $_GET['et_no_mobile'] ) && '0' == $_GET['et_no_mobile'] ) ) || ( isset( $_GET['et_no_mobile'] ) && '1' == $_GET['et_no_mobile'] ) )
			$this->show_full_version = true;
		
		if ( isset( $_GET['et_no_mobile'] ) )
			setcookie( 'et_no_mobile', (int) $_GET['et_no_mobile'], time()+60*60*24*14, COOKIEPATH, COOKIE_DOMAIN, false );
			
		if ( $this->is_mobile && $this->show_full_version )
			add_action( 'wp_footer', array( &$this, 'view_mobile_version_bar' ) );
			
		if ( $this->is_mobile && !$this->show_full_version )
			add_action( 'et_mobile_footer', array( &$this, 'mobile_mode_link' ) );
	}
	
	# add view mobile version bar for mobile users that disabled mobile mode
	function view_mobile_version_bar(){
		echo '<div style="position: relative; top: 20px; left: 0; clear: both; background: blue; background: rgba( 53, 103, 229, 0.7 ); text-align: center;  border-bottom: 1px solid #fff;"><a href="?et_no_mobile=0" style="font-size: 16px; color: #fff; display: block; padding: 20px 0; text-shadow: none;">' . __('Visit Mobile Version','et_mobile') . '</a></div>';
	}
		
	function register_mobile_menu() {
		register_nav_menus(
			array(
				'handheld-menu' => __( 'Handheld Menu', 'et_mobile' )
			)
		);
	}
	
	function remove_plugin_update_info(){
		$et_update_handheld = get_site_transient( 'et_update_handheld_plugin' );
		
		if ( isset( $et_update_handheld->response[$this->update_name] ) ){ 
			remove_action( "after_plugin_row_" . $this->update_name, 'wp_plugin_update_row', 10, 2 );
			add_action( "after_plugin_row_" . $this->update_name, array( &$this, 'update_plugin_information' ), 10, 2 );
		}
	}
	
	function update_plugin_information( $file, $plugin_data ){
		# based on wp-admin/includes/update.php
		
		$current = get_site_transient( 'update_plugins' );
		if ( !isset( $current->response[ $file ] ) )
			return false;

		$r = $current->response[ $file ];

		$plugins_allowedtags = array('a' => array('href' => array(),'title' => array()),'abbr' => array('title' => array()),'acronym' => array('title' => array()),'code' => array(),'em' => array(),'strong' => array());
		$plugin_name = wp_kses( $plugin_data['Name'], $plugins_allowedtags );

		$et_update_handheld = get_site_transient( 'et_update_handheld_plugin' );
		# open handheld changelog file in TB
		$details_url = add_query_arg( array('TB_iframe' => 'true', 'width' => 1024, 'height' => 800), $et_update_handheld->response[$this->update_name]->url );

		$wp_list_table = _get_list_table('WP_Plugins_List_Table');

		if ( is_network_admin() || !is_multisite() ) {
			echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';

			if ( ! current_user_can('update_plugins') )
				printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>.'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $r->new_version );
			else if ( empty($r->package) )
				printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>. <em>Automatic update is unavailable for this plugin.</em>'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $r->new_version );
			else
				printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s">update automatically</a>.'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $r->new_version, wp_nonce_url( self_admin_url('update.php?action=upgrade-plugin&plugin=') . $file, 'upgrade-plugin_' . $file) );

			do_action( "in_plugin_update_message-$file", $plugin_data, $r );

			echo '</div></td></tr>';
		}
	}
	
	function check_plugin_updates( $update_transient ){
		global $wp_version;
		
		if ( !isset($update_transient->checked) ) return $update_transient;
		else $plugins = $update_transient->checked;
		
		$all_plugins_info = apply_filters( 'all_plugins', get_plugins() );
		$plugin_version = $all_plugins_info[$this->update_name]['Version'];
		
		$send_to_api = array(
			'action' => 'check_plugin_updates',
			'single_plugin_version' => $plugin_version,
			'check_single_plugin_name' => $this->update_name
		);
		
		$options = array(
			'timeout' => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 3),
			'body'			=> $send_to_api,
			'user-agent'	=> 'WordPress/' . $wp_version . '; ' . home_url()
		);
		
		$plugin_request = wp_remote_post( 'http://www.elegantthemes.com/api/api.php', $options );
		if ( !is_wp_error($plugin_request) && wp_remote_retrieve_response_code($plugin_request) == 200 ){
			$plugin_response = unserialize( wp_remote_retrieve_body( $plugin_request ) );
			if ( !empty($plugin_response) ) {
				$update_transient->response = array_merge(!empty($update_transient->response) ? $update_transient->response : array(),$plugin_response);
				$last_update->checked = $plugins;
				$last_update->response = $plugin_response;
			}
		}
		
		$last_update->last_checked = time();
		set_site_transient( 'et_update_handheld_plugin', $last_update );
		
		return $update_transient;
	}
	
	function add_plugin_to_update_notification( $update_transient ){
		$et_update_handheld = get_site_transient( 'et_update_handheld_plugin' );
		if ( !is_object($et_update_handheld) || !isset($et_update_handheld->response) ) return $update_transient;
		$update_transient->response = array_merge(!empty($update_transient->response) ? $update_transient->response : array(), $et_update_handheld->response);
		
		return $update_transient;
	}
}

add_action( 'setup_theme', 'et_mobile_init' );
function et_mobile_init(){
	global $et_mobile;

	add_filter( 'et_mobile_regex', 'et_mobile_ipad_detection' );
	function et_mobile_ipad_detection( $reg_expr ){
		global $et_mobile_theme_options;
		$et_show_on_ipad = isset( $et_mobile_theme_options['activate_ipad'] ) ? (bool) $et_mobile_theme_options['activate_ipad'] : false;
		
		if ( $et_show_on_ipad ) $reg_expr .= '|ipad';
		
		return $reg_expr;
	}
	
	# you can create plugin_init.php within the plugin folder to run filters that can be applied to ET_Mobile hooks
	$plugin_init_file = trailingslashit( dirname(__FILE__) ) . 'plugin_init.php';
	if ( file_exists( $plugin_init_file ) ) include( $plugin_init_file );
	
	$et_mobile = new Et_Mobile();
	
	if ( $et_mobile->is_mobile && ! $et_mobile->show_full_version ){

		if ( ! function_exists( 'et_get_thumbnail' ) ){
			function et_get_thumbnail($width=100, $height=100, $class='', $alttext='', $titletext='', $fullpath=false, $custom_field='', $post='')
			{
				if ( $post == '' ) global $post;
				global $shortname;
				
				$thumb_array['thumb'] = '';
				$thumb_array['use_timthumb'] = true;
				if ($fullpath) $thumb_array['fullpath'] = ''; //full image url for lightbox
				
				$new_method = true;
				
				if ( has_post_thumbnail( $post->ID ) && !( '' != $custom_field && get_post_meta( $post->ID, $custom_field, true ) ) ) {
					$thumb_array['use_timthumb'] = false;
					
					$et_fullpath =  wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
					$thumb_array['fullpath'] =  $et_fullpath[0];
					$thumb_array['thumb'] = $thumb_array['fullpath'];
				}

				if ($thumb_array['thumb'] == '') {
					if ($custom_field == '') $thumb_array['thumb'] = esc_attr( get_post_meta($post->ID, 'Thumbnail', $single = true) );
					else {
						$thumb_array['thumb'] = esc_attr( get_post_meta($post->ID, $custom_field, $single = true) );
						if ($thumb_array['thumb'] == '') $thumb_array['thumb'] = esc_attr( get_post_meta($post->ID, 'Thumbnail', $single = true) );
					}
					
					if (($thumb_array['thumb'] == '') && ((get_option($shortname.'_grab_image')) == 'on')) { 
						$thumb_array['thumb'] = esc_attr( et_first_image() );
						if ( $fullpath ) $thumb_array['fullpath'] = $thumb_array['thumb'];
					}
					
					#if custom field used for small pre-cropped image, open Thumbnail custom field image in lightbox
					if ($fullpath) {
						$thumb_array['fullpath'] = $thumb_array['thumb'];
						if ($custom_field == '') $thumb_array['fullpath'] = apply_filters('et_fullpath', et_path_reltoabs(esc_attr($thumb_array['thumb'])));
						elseif ( $custom_field <> '' && get_post_meta($post->ID, 'Thumbnail', $single = true) ) $thumb_array['fullpath'] = apply_filters( 'et_fullpath', et_path_reltoabs(esc_attr(get_post_meta($post->ID, 'Thumbnail', $single = true))) );
					}
				}
				
				return $thumb_array;
			}
		}

		/* this function prints thumbnail from Post Thumbnail or Custom field or First post image */
		if ( ! function_exists( 'et_print_thumbnail' ) ){
			function et_print_thumbnail($thumbnail = '', $use_timthumb = true, $alttext = '', $width = 100, $height = 100, $class = '', $echoout = true, $forstyle = false, $resize = true, $post='') {
				global $shortname;
				if ( $post == '' ) global $post;
				
				$output = '';
				$thumbnail_orig = $thumbnail;
				
				$thumbnail = et_multisite_thumbnail( $thumbnail );
				
				$cropPosition = '';
				
				$allow_new_thumb_method = false;
				
				$new_method = true;
				$new_method_thumb = '';
				$external_source = false;
					
				$allow_new_thumb_method = !$external_source && $new_method && $cropPosition == '';
				
				if ( $allow_new_thumb_method && $thumbnail <> '' ){
					$et_crop = get_post_meta( $post->ID, 'et_nocrop', true ) == '' ? true : false; 
					$new_method_thumb =  et_resize_image( et_path_reltoabs($thumbnail), $width, $height, $et_crop );
					if ( is_wp_error( $new_method_thumb ) ) $new_method_thumb = '';
				}
				
				if ($forstyle === false) {
					$output = '<img src="' . $new_method_thumb . '"';
					
					if ($class <> '') $output .= " class='$class' ";

					$output .= " alt='$alttext' />";
					
					if (!$resize) $output = $thumbnail;
				} else {
					$output = $new_method_thumb;
				}
				
				if ($echoout) echo $output;
				else return $output;
			}
		}

		if ( ! function_exists( 'et_new_thumb_resize' ) ){
			function et_new_thumb_resize( $thumbnail, $width, $height, $alt='', $forstyle = false ){
				global $shortname;
					
				$new_method = true;
				$new_method_thumb = '';
				$external_source = false;
					
				$allow_new_thumb_method = !$external_source && $new_method;
				
				if ( $allow_new_thumb_method && $thumbnail <> '' ){
					$et_crop = true;
					$new_method_thumb = et_resize_image( $thumbnail, $width, $height, $et_crop );
					if ( is_wp_error( $new_method_thumb ) ) $new_method_thumb = '';
				}
				
				$thumb = esc_attr( $new_method_thumb );
				
				$output = '<img src="' . $thumb . '" alt="' . $alt . '" width =' . $width . ' height=' . $height . ' />';
				
				return ( !$forstyle ) ? $output : $thumb;
			}
		}

		if ( ! function_exists( 'et_multisite_thumbnail' ) ){
			function et_multisite_thumbnail($thumbnail='') {
				global $post;
				if ( isset( $post ) && get_post_meta($post->ID, 'blogid', true) ) $blog_id = get_post_meta($post->ID, 'blogid', true);
				else global $blog_id;

				if (isset($blog_id) && $blog_id > 0) {
					$imagePath = explode('/files/', esc_attr($thumbnail));
					if (isset($imagePath[1])) {
						$thumbnail = apply_filters( 'et_multisite_thumbs_absolute_path', get_home_url( 1 ) . '/wp-content/' ); // retrieves wp-content url for a main site ( with blog_id = 1 )
						$thumbnail .= 'blogs.dir/' . $blog_id . '/files/' . $imagePath[1];
					}
				}

				return $thumbnail;
			}
		}

		if ( ! function_exists( 'et_path_reltoabs' ) ){
			function et_path_reltoabs( $imageurl ){
				if ( strpos(strtolower($imageurl), 'http://') !== false || strpos(strtolower($imageurl), 'https://') !== false ) return $imageurl;
				
				if ( strpos( strtolower($imageurl), $_SERVER['HTTP_HOST'] ) !== false )
					return $imageurl;
				else {
					$imageurl = apply_filters( 'et_path_relative_image', site_url() . '/' ) . $imageurl;
				}
				
				return $imageurl;
			}
		}

		add_action( 'init', 'et_create_images_temp_folder2' );
		function et_create_images_temp_folder2(){
			#clean et_temp folder once per week
			if ( false !== $last_time = get_option( 'et_schedule_clean_images_last_time'  ) ){
				$timeout = 86400 * 7;
				if ( ( $timeout < ( time() - $last_time ) ) && '' != get_option( 'et_images_temp_folder' ) ) et_clean_temp_images( get_option( 'et_images_temp_folder' ) );
			}
			
			if ( false !== get_option( 'et_images_temp_folder' ) ) return;
			
			$uploads_dir = wp_upload_dir();
			$destination_dir = ( false === $uploads_dir['error'] ) ? path_join( $uploads_dir['basedir'], 'et_temp' ) : null;
				
			if ( ! wp_mkdir_p( $destination_dir ) ) update_option( 'et_images_temp_folder', '' );
			else {
				update_option( 'et_images_temp_folder', preg_replace( '#\/\/#', '/', $destination_dir ) );
				update_option( 'et_schedule_clean_images_last_time', time() );
			}
		}

		if ( ! function_exists( 'et_clean_temp_images' ) ){
			function et_clean_temp_images( $directory ){
				$dir_to_clean = @ opendir( $directory );
				
				if ( $dir_to_clean ) {
					while (($file = readdir( $dir_to_clean ) ) !== false ) {
						if ( substr($file, 0, 1) == '.' )
							continue;
						if ( is_dir( $directory.'/'.$file ) )
							et_clean_temp_images( path_join( $directory, $file ) );
						else
							@ unlink( path_join( $directory, $file ) );
					}
					closedir( $dir_to_clean );
				}
				
				#set last time cleaning was performed
				update_option( 'et_schedule_clean_images_last_time', time() );
			}
		}

		if ( ! function_exists( 'et_resize_image' ) ){
			function et_resize_image( $thumb, $new_width, $new_height, $crop ){
				if ( is_ssl() ) $thumb = preg_replace( '#^http://#', 'https://', $thumb );
				$info = pathinfo($thumb);
				$ext = $info['extension'];
				$name = wp_basename($thumb, ".$ext");
				$is_jpeg = false;
				$site_uri = apply_filters( 'et_resize_image_site_uri', site_url() );
				$site_dir = apply_filters( 'et_resize_image_site_dir', ABSPATH );
				
				#get main site url on multisite installation 
				if ( is_multisite() ){
					switch_to_blog(1);
					$site_uri = site_url();
					restore_current_blog();
				}
				
				if ( 'jpeg' == $ext ) {
					$ext = 'jpg';
					$name = preg_replace( '#.jpeg$#', '', $name );
					$is_jpeg = true;
				}
				
				$suffix = "{$new_width}x{$new_height}";
				
				$destination_dir = '' != get_option( 'et_images_temp_folder' ) ? preg_replace( '#\/\/#', '/', get_option( 'et_images_temp_folder' ) ) : null;
				
				$matches = apply_filters( 'et_resize_image_site_dir', array(), $site_dir );
				if ( !empty($matches) ){
					preg_match( '#'.$matches[1].'$#', $site_uri, $site_uri_matches );
					if ( !empty($site_uri_matches) ){
						$site_uri = str_replace( $matches[1], '', $site_uri );
						$site_uri = preg_replace( '#/$#', '', $site_uri );
						$site_dir = str_replace( $matches[1], '', $site_dir );
						$site_dir = preg_replace( '#\\\/$#', '', $site_dir );
					}
				}
				
				#get local name for use in file_exists() and get_imagesize() functions
				$localfile = str_replace( apply_filters( 'et_resize_image_localfile', $site_uri, $site_dir, et_multisite_thumbnail($thumb) ), $site_dir, et_multisite_thumbnail($thumb) );
				
				$add_to_suffix = '';
				if ( file_exists( $localfile ) ) $add_to_suffix = filesize( $localfile ) . '_';
				
				#prepend image filesize to be able to use images with the same filename
				$suffix = $add_to_suffix . $suffix;
				$destfilename_attributes = '-' . $suffix . '.' . $ext;
				
				$checkfilename = ( '' != $destination_dir && null !== $destination_dir ) ? path_join( $destination_dir, $name ) : path_join( dirname( $localfile ), $name );
				$checkfilename .= $destfilename_attributes;
				
				if ( $is_jpeg ) $checkfilename = preg_replace( '#.jpeg$#', '.jpg', $checkfilename );
				
				$uploads_dir = wp_upload_dir();
				$uploads_dir['basedir'] = preg_replace( '#\/\/#', '/', $uploads_dir['basedir'] );
				
				if ( null !== $destination_dir && '' != $destination_dir && apply_filters('et_enable_uploads_detection', true) ){
					$site_dir = trailingslashit( preg_replace( '#\/\/#', '/', $uploads_dir['basedir'] ) );
					$site_uri = trailingslashit( $uploads_dir['baseurl'] );
				}
				
				#check if we have an image with specified width and height
				
				if ( file_exists( $checkfilename ) ) return str_replace( $site_dir, trailingslashit( $site_uri ), $checkfilename );

				$size = @getimagesize( $localfile );
				
				if ( !$size ) return new WP_Error('invalid_image_path', __('Image doesn\'t exist'), $thumb);
				list($orig_width, $orig_height, $orig_type) = $size;
				
				#check if we're resizing the image to smaller dimensions
				if ( $orig_width > $new_width || $orig_height > $new_height ){
					if ( $orig_width < $new_width || $orig_height < $new_height ){
						#don't resize image if new dimensions > than its original ones
						if ( $orig_width < $new_width ) $new_width = $orig_width;
						if ( $orig_height < $new_height ) $new_height = $orig_height;
						
						#regenerate suffix and appended attributes in case we changed new width or new height dimensions
						$suffix = "{$add_to_suffix}{$new_width}x{$new_height}";
						$destfilename_attributes = '-' . $suffix . '.' . $ext;
						
						$checkfilename = ( '' != $destination_dir && null !== $destination_dir ) ? path_join( $destination_dir, $name ) : path_join( dirname( $localfile ), $name );
						$checkfilename .= $destfilename_attributes;
						
						#check if we have an image with new calculated width and height parameters
						if ( file_exists($checkfilename) ) return str_replace( $site_dir, trailingslashit( $site_uri ), $checkfilename );
					}
					
					#we didn't find the image in cache, resizing is done here
					$result = image_resize( $localfile, $new_width, $new_height, $crop, $suffix, $destination_dir );

					if ( !is_wp_error( $result ) ) {
						#transform local image path into URI
						
						if ( $is_jpeg ) $thumb = preg_replace( '#.jpeg$#', '.jpg', $thumb);
						
						$site_dir = str_replace( '\\', '/', $site_dir );
						$result = str_replace( '\\', '/', $result );
						$result = str_replace( $site_dir, trailingslashit( $site_uri ), $result );
					}
					
					#returns resized image path or WP_Error ( if something went wrong during resizing )
					return $result;
				}
				
				#returns unmodified image, for example in case if the user is trying to resize 800x600px to 1920x1080px image
				return $thumb;
			}
		}
	}
}