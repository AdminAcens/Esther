<?php
add_action( 'after_setup_theme', 'handheld_setup' );
if ( ! function_exists( 'handheld_setup' ) ){
	function handheld_setup(){
		global $et_mobile_theme_options;
		
		load_theme_textdomain( 'HandHeld', TEMPLATEPATH . '/languages' );
		
		add_action( 'wp_ajax_nopriv_et_show_ajax_posts', 'et_show_ajax_posts' );
		add_action( 'wp_ajax_et_show_ajax_posts', 'et_show_ajax_posts' );
		
		if ( isset( $et_mobile_theme_options['bg_color'] ) && '' != $et_mobile_theme_options['bg_color'] ) add_action( 'wp_head','et_add_bgcolor' );
		
		add_filter( 'template_include', 'et_check_homepage_static' );
		
		add_action( 'wp_head', 'et_add_apple_touch_images', 7 );
	}
}

function et_add_apple_touch_images(){
	global $et_mobile_theme_options;
	
	$webpage_icon_small = isset( $et_mobile_theme_options['webpage_icon_small'] ) && '' != $et_mobile_theme_options['webpage_icon_small'] ? $et_mobile_theme_options['webpage_icon_small'] : get_template_directory_uri() . '/images/ios_icons/apple-touch-icon-precomposed.png';
	$webpage_icon_big = isset( $et_mobile_theme_options['webpage_icon_big'] ) && '' != $et_mobile_theme_options['webpage_icon_big'] ? $et_mobile_theme_options['webpage_icon_big'] : get_template_directory_uri() . '/images/ios_icons/apple-touch-icon.png';
	$splash_image = isset( $et_mobile_theme_options['splash_image'] ) && '' != $et_mobile_theme_options['splash_image'] ? $et_mobile_theme_options['splash_image'] : get_template_directory_uri() . '/images/ios_icons/splash.png';

	echo '<link rel="apple-touch-icon-precomposed" href="' . esc_url( $webpage_icon_small ) . '" />';
	echo '<link rel="apple-touch-icon-precomposed" sizes="114x114" href="' . esc_url( $webpage_icon_big ) . '" />';
	echo '<link rel="apple-touch-startup-image" href="' . esc_url( $splash_image ) . '" />';
}

function et_check_homepage_static( $template ){
	# if static homepage is set ( WP-Admin / Settings / Reading ) and we're on the homepage, load home.php
	if ( is_front_page() && ! is_home() ) $template = get_home_template();
	
	return $template;
}

function et_add_bgcolor(){
	global $et_mobile_theme_options;
	
	echo '<style>body{ background-color: #'. esc_html( str_replace( '#', '', $et_mobile_theme_options['bg_color'] ) ) .'; }</style>';
}

if ( ! function_exists( 'et_mobile_custom_comments_display' ) ) :
function et_mobile_custom_comments_display($comment, $args, $depth) {
   $GLOBALS['comment'] = $comment; ?>
   <li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
		<article id="comment-<?php comment_ID(); ?>" class="text_block comment clearfix">
			<div class="avatar-box">
				<?php echo get_avatar($comment,$size='37'); ?>
				<span class="avatar-overlay"></span>
			</div> <!-- end .avatar-box -->
			<?php printf('<span class="fn">%s</span>', get_comment_author_link()) ?>
				
			<div class="comment-content clearfix">
				<?php if ($comment->comment_approved == '0') : ?>
					<em class="moderation"><?php esc_html_e('Your comment is awaiting moderation.','HandHeld') ?></em>
					<br />
				<?php endif; ?>

				<?php comment_text() ?>
			</div> <!-- end comment-content-->
			
			<div class="comment-meta clearfix">
				<span class="comment-date"><?php if ( 1 == $depth ) printf( __( 'Posted on %1$s', 'HandHeld' ), get_comment_date() ); else echo get_comment_date(); ?></span>
				<?php 
					$et_comment_reply_link = get_comment_reply_link( array_merge( $args, array('reply_text' => esc_attr__('Reply','HandHeld'),'depth' => $depth, 'max_depth' => $args['max_depth'])) );
					if ( $et_comment_reply_link ) echo '<div class="reply-container">' . $et_comment_reply_link . '</div>';
				?>
			</div> <!-- end .comment-meta -->
		</article>
<?php }
endif;

if ( ! function_exists( 'et_list_pings' ) ){
	function et_list_pings($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment; ?>
		<li id="comment-<?php comment_ID(); ?>"><?php comment_author_link(); ?> - <?php comment_excerpt(); ?>
	<?php }
}

if ( ! function_exists( 'et_mobile_regular_post' ) ){
	function et_mobile_regular_post(){ 
		global $post; ?>
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
					<a href="<?php the_permalink(); ?>">
						<?php et_print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext); ?>
						<span class="overlay"></span>
					</a>
					<span class="comment_count"><?php comments_popup_link( 0, 1, '%' ); ?></span>
				</div> <!-- end .post-thumb -->
			<?php } ?>
			<div class="post-content">
				<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<p class="meta-info"><?php esc_html_e('Posted on','HandHeld'); ?> <time datetime="<?php the_time( 'Y-m-d' ); ?>" pubdate><?php the_time( 'F jS' ); ?></time></p>
			</div> <!-- end .post-content -->
			<a href="<?php the_permalink(); ?>" class="readmore"><?php esc_html_e('Read more','HandHeld'); ?></a>
		</article> <!-- end .post -->
	<?php } 
}

if ( ! function_exists( 'et_mobile_gallery_post' ) ){
	function et_mobile_gallery_post(){
		global $post; ?>
		<a href="<?php the_permalink(); ?>" class="project">
			<?php
				$thumb = '';
				$width = 70;
				$height = 70;
				$classtext = '';
				$titletext = get_the_title();
				$thumbnail = et_get_thumbnail($width,$height,$classtext,$titletext,$titletext,false,'Project');
				$thumb = $thumbnail["thumb"];
			?>
			<?php et_print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext); ?>
			<span></span>
		</a>
	<?php } 
}

add_action( 'template_redirect', 'et_mobile_load_ajax_scripts' );
function et_mobile_load_ajax_scripts(){
	wp_enqueue_script( 'et_home_load_more', get_template_directory_uri() . '/js/custom.js', array( 'jquery' ) );
	wp_localize_script( 'et_home_load_more', 'etmobile', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'et_load_nonce' => wp_create_nonce( 'et_load_nonce' ) ) );
}

function et_show_ajax_posts() {
	global $et_mobile_theme_options;
	if ( ! wp_verify_nonce( $_POST['et_load_nonce'], 'et_load_nonce' ) ) die(-1);
	
	$posts_num = (int) $_POST['et_posts_num'];
	$posts_offset = (int) $_POST['et_posts_offset'];
	$gallery = (int) $_POST['et_gallery'];
	
	$args = array(
		'posts_per_page' => $posts_num,
		'offset' => $posts_offset,
		'post_status' => 'publish'
	);
	
	if ( isset( $et_mobile_theme_options['home_blog_categories'] ) && !empty( $et_mobile_theme_options['home_blog_categories'] ) && 0 == $gallery )
		$args['category__in'] = $et_mobile_theme_options['home_blog_categories'];
		
	if ( 0 != $gallery && isset( $et_mobile_theme_options['home_project_categories'] ) && !empty( $et_mobile_theme_options['home_project_categories'] ) ) 
		$args['category__in'] = $et_mobile_theme_options['home_project_categories'];
	
	ob_start();
	$the_query = new WP_Query( $args );
	while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
		<?php if ( 0 == $gallery ) { ?>
			<?php et_mobile_regular_post(); ?>
		<?php } else { ?>
			<?php et_mobile_gallery_post(); ?>
		<?php } ?>
	<?php endwhile;
	wp_reset_postdata();
	$posts = ob_get_clean();
	$last_query = ( $the_query->found_posts - $posts_offset ) > $posts_num ? false : true;
	echo json_encode( array( 'posts' => $posts, 'last_query' => $last_query ) );
	die();
} ?>