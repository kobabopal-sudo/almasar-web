<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function uabb_setup() {
	load_theme_textdomain( 'uae-bag-boutique', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 100, 'width' => 300, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	register_nav_menus( array( 'primary' => 'Main menu' ) );
}
add_action( 'after_setup_theme', 'uabb_setup' );

function uabb_assets() {
	wp_enqueue_style(
		'uabb-google-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Noto+Sans+Arabic:wght@300;400;500;600&family=Raleway:wght@300;400;500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'uabb-style', get_stylesheet_uri(), array( 'uabb-google-fonts' ), '1.3.0' );
	wp_enqueue_style( 'uabb-storefront', get_template_directory_uri() . '/assets/storefront.css', array( 'uabb-style' ), '1.3.0' );
	if ( function_exists( 'WC' ) ) {
		wp_enqueue_script( 'wc-cart-fragments' );
	}
}
add_action( 'wp_enqueue_scripts', 'uabb_assets' );

function uabb_cart_count() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '0';
	}
	return (string) WC()->cart->get_cart_contents_count();
}

function uabb_t( $english, $arabic ) {
	return function_exists( 'uabs_t' ) ? uabs_t( $english, $arabic ) : ( is_rtl() ? $arabic : $english );
}

function uabb_brand_markup( $class = 'uabb-brand-lockup' ) {
	$logo = get_template_directory_uri() . '/assets/images/almasar-logo.png';
	return '<span class="' . esc_attr( $class ) . '" aria-label="' . esc_attr( uabb_t( 'Almasar Al Raed', 'المسار الرائد' ) ) . '"><img class="uabb-brand-mark" src="' . esc_url( $logo ) . '" alt="" aria-hidden="true"><span class="uabb-brand-wordmark"><span class="uabb-brand-primary">' . esc_html( uabb_t( 'Almasar', 'المسار' ) ) . '</span><span class="uabb-brand-secondary">' . esc_html( uabb_t( 'Al Raed', 'الرائد' ) ) . '</span></span></span>';
}

function uabb_cart_markup() {
	$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
	$label = uabb_t( 'Bag', 'السلة' );
	return '<a class="uabb-cart" href="' . esc_url( $cart_url ) . '"><span>' . esc_html( $label ) . '</span><b>' . esc_html( uabb_cart_count() ) . '</b></a>';
}

function uabb_cart_fragments( $fragments ) {
	$fragments['a.uabb-cart'] = uabb_cart_markup();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'uabb_cart_fragments' );

function uabb_shop_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

function uabb_category_url( $slug ) {
	$term = get_term_by( 'slug', sanitize_title( $slug ), 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		$url = get_term_link( $term );
		if ( ! is_wp_error( $url ) ) {
			return $url;
		}
	}
	return uabb_shop_url();
}

function uabb_support_url() {
	$settings = get_option( 'uabs_settings', array() );
	$business_url = is_array( $settings ) && isset( $settings['whatsapp_business_url'] ) && is_string( $settings['whatsapp_business_url'] ) ? esc_url_raw( $settings['whatsapp_business_url'], array( 'https' ) ) : '';
	if ( $business_url && preg_match( '~^https://wa\.me/message/[A-Za-z0-9]+/?$~i', $business_url ) ) {
		return $business_url;
	}
	$raw_number = is_array( $settings ) && isset( $settings['whatsapp_number'] ) && is_scalar( $settings['whatsapp_number'] ) ? (string) $settings['whatsapp_number'] : '';
	$number = preg_replace( '/\D+/', '', $raw_number );
	return $number ? 'https://wa.me/' . $number : 'https://wa.me/message/GZQV4BN3HXM3M1';
}

function uabb_instagram_url() {
	return 'https://www.instagram.com/almasar_alraed/?hl=en';
}

function uabb_document_title_parts( $parts ) {
	if ( ! is_admin() ) {
		$parts['site'] = uabb_t( 'Almasar Al Raed', 'المسار الرائد' );
		if ( is_front_page() ) {
			$parts['title'] = '';
		} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
			$parts['title'] = uabb_t( 'Shop all', 'تسوق الكل' );
		} elseif ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
			$parts['title'] = single_term_title( '', false );
		}
	}
	return $parts;
}
add_filter( 'document_title_parts', 'uabb_document_title_parts' );

function uabb_department_body_class( $classes ) {
	if ( ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
		return $classes;
	}
	$term = get_queried_object();
	if ( $term instanceof WP_Term && in_array( $term->slug, array( 'bags', 'handbags', 'shoulder-bags', 'crossbody-bags', 'tote-bags', 'mini-bags', 'small-leather-goods', 'accessories', 'atelier-edit' ), true ) ) {
		$classes[] = 'uabb-department-bags';
	} elseif ( $term instanceof WP_Term && in_array( $term->slug, array( 'everyday-objects', 'kitchen-and-dining', 'camp-and-outdoors', 'bottles-and-hydration', 'travel-essentials', 'tech-and-utility' ), true ) ) {
		$classes[] = 'uabb-department-objects';
	}
	return $classes;
}
add_filter( 'body_class', 'uabb_department_body_class' );

function uabb_department_archive_intro() {
	if ( ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
		return;
	}
	$term = get_queried_object();
	if ( ! $term instanceof WP_Term || ! in_array( $term->slug, array( 'bags', 'everyday-objects' ), true ) ) {
		return;
	}
	$is_objects = 'everyday-objects' === $term->slug;
	$image = get_template_directory_uri() . '/assets/images/' . ( $is_objects ? 'hero-everyday-objects.jpg' : 'hero-atelier.jpg' );
	$heading = $is_objects ? uabb_t( 'Useful things, beautifully considered.', 'أغراض نافعة بتفاصيل مدروسة.' ) : uabb_t( 'The Carry Archive', 'أرشيف الحقائب' );
	$description = $is_objects ? uabb_t( 'Modern essentials for kitchen, camp, hydration and travel.', 'أساسيات عصرية للمطبخ والرحلات والترطيب والسفر.' ) : uabb_t( 'A study in form, material and the everyday ritual of carrying.', 'تأمل في الشكل والخامة وتفاصيل ما نحمله كل يوم.' );
	$eyebrow = $is_objects ? uabb_t( 'Field objects · 02', 'أغراض ميدانية · ٠٢' ) : uabb_t( 'The atelier · 01', 'الأتيليه · ٠١' );
	echo '<section class="uabb-archive-hero ' . ( $is_objects ? 'uabb-archive-hero-objects' : 'uabb-archive-hero-bags' ) . '"><div class="uabb-archive-hero-copy"><p class="uabb-eyebrow">' . esc_html( $eyebrow ) . '</p><h1>' . esc_html( $heading ) . '</h1><p>' . esc_html( $description ) . '</p></div><img src="' . esc_url( $image ) . '" alt="" fetchpriority="high"></section>';
	$children = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $term->term_id, 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
	if ( is_wp_error( $children ) || empty( $children ) ) {
		return;
	}
	echo '<nav class="uabb-archive-categories" aria-label="' . esc_attr( uabb_t( 'Explore categories', 'استكشف الفئات' ) ) . '">';
	foreach ( $children as $child ) {
		$url = get_term_link( $child );
		if ( is_wp_error( $url ) ) {
			continue;
		}
		echo '<a href="' . esc_url( $url ) . '">' . esc_html( $child->name ) . '<span aria-hidden="true">↗</span></a>';
	}
	echo '</nav>';
}
add_action( 'woocommerce_before_shop_loop', 'uabb_department_archive_intro', 2 );
