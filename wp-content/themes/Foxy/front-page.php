<?php if ( is_front_page() && is_page() ) { include( get_page_template() ); return; } ?>

<?php get_header(); ?>

<?php if ( 'on' == et_get_option( 'foxy_featured', 'on' ) ) get_template_part( 'includes/featured' ); ?>

<?php if ( 'on' == et_get_option( 'foxy_display_services', 'on' ) || 'on' == et_get_option( 'foxy_display_callout', 'on' ) ) : ?>

<div id="section-area">

<?php if ( 'on' == et_get_option( 'foxy_display_services', 'on' ) ) { ?>

	</div> <!-- #services -->
<?php } // 'on' == et_get_option( 'foxy_display_services', 'on' ) ?>

<?php if ( 'on' == et_get_option( 'foxy_display_callout', 'false' ) ) { ?>
	<a id="callout" href="<?php echo esc_url( et_get_option( 'foxy_callout_url', '#' ) ); ?>">
		<strong><?php echo et_get_option( 'foxy_callout_text' ); ?></strong>
		<span><?php echo esc_html( et_get_option( 'foxy_callout_button_text' ) ); ?></span>
	</a>
<?php } // 'on' == et_get_option( 'foxy_display_callout', 'on' ) ?>

</div> <!-- #section-area -->

<?php endif; // 'on' == et_get_option( 'foxy_display_services', 'on' ) || 'on' == et_get_option( 'foxy_display_callout', 'on' ) ?>

<?php if ( 'on' == et_get_option( 'foxy_home_products_featured', 'on' ) && class_exists( 'woocommerce' ) ) : ?>
<?php
	global $woocommerce;

	$product_ids_on_sale = et_woocommerce_get_product_on_sale_ids();

	$query_args = array(
		'post_type'	=> 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts' => 1,
		'posts_per_page' => (int) et_get_option( 'foxy_sale_products_number', 8 ),
		'orderby' => 'date',
		'order' => 'DESC',
		'meta_query' => array(
			array(
				'key' => '_visibility',
				'value' => array('catalog', 'visible'),
				'compare' => 'IN'
			),
			array(
				'key' => '_featured',
				'value' => 'yes'
			)
		)
	);

	$et_featured_products_query = new WP_Query( apply_filters( 'et_featured_products_args', $query_args ) );

	if ( $et_featured_products_query->have_posts() ) :
?>
		<div id="et-product-slider">
			<div class="et-carousel-wrapper">
				<ul class="clearfix">
		<?php while ( $et_featured_products_query->have_posts() ) : $et_featured_products_query->the_post(); ?>
		<?php
					global $post;

					if ( function_exists( 'get_product' ) )
						$product = get_product( $et_featured_products_query->post->ID );
					else
						$product = new WC_Product( $et_featured_products_query->post->ID );

					$thumb = '';
					$width = 220;
					$height = 9999;
					$classtext = '';
					$titletext = get_the_title();
					$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'ProductImage' );
					$thumb = $thumbnail["thumb"];

					$et_price_before = 'variable' == $product->product_type ? $product->min_variation_regular_price : $product->regular_price;
		?>
					<li class="et-product">
						<a href="<?php the_permalink(); ?>"><?php print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext ); ?></a>

						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

					<?php if ( ! in_array( get_the_ID(), array_map( 'intval', $product_ids_on_sale ) ) ) { ?>
						<?php if ( '' != $product->get_price_html() ) : ?>
						<div class="et-price-button">
							<span class="et-price-sale"><?php echo $product->get_price_html(); ?></span>
						</div>
						<?php endif; ?>
					<?php } else { ?>
						<div class="et-price-button et-product-on-sale">
							<span class="et-price-before"><del><?php echo woocommerce_price( $et_price_before ); ?></del></span>
							<span class="et-price-sale"><?php echo woocommerce_price( $product->get_price() ); ?></span>
						</div>
					<?php } ?>

						<?php woocommerce_show_product_sale_flash( $post, $product ); ?>
					</li>
		<?php endwhile; ?>
				</ul>
			</div> <!-- .et-carousel-wrapper -->
		</div> <!-- #et-product-slider -->
<?php
	endif;
	wp_reset_postdata();
?>
<?php endif; // 'on' == et_get_option( 'foxy_home_products_onsale', 'on' ) ?>




<?php if ( 'on' == et_get_option( 'foxy_show_homepage_tabs', 'false' ) ) { ?>
<?php
	$i = 1;

	$home_pages_num = count( et_get_option( 'foxy_home_tabs_pages' ) );

	$home_pages_args = array(
		'post_type' 		=> 'page',
		'orderby' 			=> 'menu_order',
		'order' 			=> 'ASC',
		'posts_per_page' 	=> (int) $home_pages_num,
	);

	if ( is_array( et_get_option( 'foxy_home_tabs_pages', '', 'page' ) ) )
		$home_pages_args['post__in'] = (array) array_map( 'intval', et_get_option( 'foxy_home_tabs_pages', '', 'page' ) );

	$home_pages = new WP_Query( apply_filters( 'et_home_tabpages_args', $home_pages_args ) );
?>

<?php wp_reset_postdata(); ?>
<?php } // 'on' == et_get_option( 'foxy_show_homepage_tabs', 'false' ) ?>

<?php
if ( 'on' == et_get_option( 'foxy_show_testimonials', 'false' ) ) {

	$args = array(
		'orderby' 			=> 'rand',
		'post_type'			=> 'testimonial',
		'posts_per_page' 	=> 1,
	);
	$et_testimonials_query = new WP_Query( apply_filters( 'et_home_testimonials_query_args', $args ) );
	if ( $et_testimonials_query->have_posts() ) :
?>
	<div id="testimonials">
		<?php while ( $et_testimonials_query->have_posts() ) : $et_testimonials_query->the_post(); ?>
		<?php
			$thumb = '';
			$width = 75;
			$height = 75;
			$classtext = 'author-img';
			$titletext = get_the_title();
			$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Testimonial' );
			$thumb = $thumbnail["thumb"];
			$company_name = get_post_meta( get_the_ID(), '_et_testimonial_company', true );
		?>
		<div class="testimonial">
			<?php the_content(); ?>
			<div class="testimonial-author">
			<?php if ( '' != $thumb ) { ?>
				<span class="et-avatar">
					<?php print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext ); ?>
				</span>
			<?php } ?>
				<strong><?php the_title(); ?></strong>
				<p><?php echo esc_html( $company_name ); ?></p>
			</div>
		</div>
		<?php endwhile; ?>
	</div> <!-- #testimonials -->
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>

<?php } // 'on' == et_get_option( 'foxy_show_testimonials', 'false' ) ?>

</div> <!-- #home-info -->

<?php get_footer(); ?>