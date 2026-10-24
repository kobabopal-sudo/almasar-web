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
	wp_enqueue_style( 'uabb-local-fonts', get_template_directory_uri() . '/assets/fonts/fonts.css', array(), '1.0.0' );
	wp_enqueue_style( 'uabb-style', get_stylesheet_uri(), array( 'uabb-local-fonts' ), '1.8.0' );
	wp_enqueue_style( 'uabb-storefront', get_template_directory_uri() . '/assets/storefront.css', array( 'uabb-style' ), '1.8.0' );
	wp_enqueue_style( 'uabb-experience', get_template_directory_uri() . '/assets/experience.css', array( 'uabb-storefront' ), '1.8.0' );
	wp_enqueue_style( 'uabb-polish', get_template_directory_uri() . '/assets/polish.css', array( 'uabb-experience' ), '1.8.1' );
	wp_enqueue_script( 'uabb-experience', get_template_directory_uri() . '/assets/experience.js', array(), '1.7.0', true );
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

function uabb_account_url() {
	$url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '';
	return is_string( $url ) && $url ? $url : home_url( '/my-account/' );
}

function uabb_myaccount_intro() {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || is_user_logged_in() ) {
		return;
	}
	$image = get_template_directory_uri() . '/assets/images/category-bags-editorial.jpg';
	echo '<section class="uabb-account-intro"><img src="' . esc_url( $image ) . '" alt="" loading="eager"><div><p class="uabb-eyebrow">' . esc_html( uabb_t( 'ALMASAR / PRIVATE CLIENT', 'المسار / تجربة خاصة' ) ) . '</p><h1>' . esc_html( uabb_t( 'Your collection, kept close.', 'مجموعتك، أقرب إليك.' ) ) . '</h1><p>' . esc_html( uabb_t( 'Sign in or create an account to keep track of orders and delivery details.', 'سجّل الدخول أو أنشئ حساباً لمتابعة طلباتك وتفاصيل التوصيل.' ) ) . '</p></div></section>';
}
add_action( 'woocommerce_before_customer_login_form', 'uabb_myaccount_intro', 5 );

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

function uabb_department_for_term( $term ) {
	if ( ! $term instanceof WP_Term || 'product_cat' !== $term->taxonomy ) {
		return '';
	}
	$term_ids = array_merge( array( absint( $term->term_id ) ), array_map( 'absint', get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) ) );
	foreach ( $term_ids as $term_id ) {
		$ancestor = get_term( $term_id, 'product_cat' );
		if ( ! $ancestor instanceof WP_Term ) {
			continue;
		}
		if ( 'bags' === $ancestor->slug ) {
			return 'bags';
		}
		if ( in_array( $ancestor->slug, array( 'everyday-objects', 'gadgets' ), true ) ) {
			return 'objects';
		}
	}
	return '';
}

function uabb_department_product_count( $root_slug ) {
	$root = get_term_by( 'slug', sanitize_title( $root_slug ), 'product_cat' );
	if ( ! $root instanceof WP_Term ) {
		return 0;
	}
	$products = get_posts( array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => 1,
		'fields' => 'ids',
		'no_found_rows' => true,
		'tax_query' => array( array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => array( absint( $root->term_id ) ), 'include_children' => true ) ),
	) );
	return count( $products );
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
			$term = get_queried_object();
			$parts['title'] = $term instanceof WP_Term && in_array( $term->slug, array( 'everyday-objects', 'gadgets' ), true )
				? uabb_t( 'Everyday Gadgets', 'الأجهزة اليومية' )
				: single_term_title( '', false );
		}
	}
	return $parts;
}
add_filter( 'document_title_parts', 'uabb_document_title_parts' );

/** Add useful search descriptions without competing with a dedicated SEO plugin. */
function uabb_meta_description() {
	if ( is_admin() || defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) ) {
		return;
	}
	$description = '';
	if ( is_front_page() ) {
		$description = uabb_t( 'Discover Almasar Al Raed: a considered edit of bags and distinctive everyday gadgets, curated in the UAE with delivery across all seven emirates.', 'اكتشف المسار الرائد: تشكيلة منتقاة من الحقائب والأجهزة اليومية المميزة، من الإمارات مع التوصيل إلى الإمارات السبع.' );
	} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$department = uabb_department_for_term( $term );
			$label = is_rtl() ? get_term_meta( $term->term_id, 'uabs_ar_name', true ) : '';
			$label = $label ? $label : $term->name;
			if ( in_array( $term->slug, array( 'everyday-objects', 'gadgets' ), true ) ) {
				$label = uabb_t( 'Everyday Gadgets', 'الأجهزة اليومية' );
			}
			if ( 'bags' === $department ) {
				$description = sprintf( uabb_t( 'Explore %1$s in The Carry Archive by Almasar Al Raed. Thoughtful shapes and considered details, curated in the UAE.', 'اكتشف %1$s من أرشيف الحقائب لدى المسار الرائد. تصاميم مدروسة وتفاصيل مختارة بعناية من الإمارات.' ), $label );
			} elseif ( 'objects' === $department ) {
				$description = sprintf( uabb_t( 'Explore %1$s from The Gadget Studio by Almasar Al Raed: thoughtful finds for home, sound, travel and everyday rituals.', 'اكتشف %1$s من استوديو الأجهزة لدى المسار الرائد: اختيارات مدروسة للمنزل والصوت والسفر وتفاصيل الحياة اليومية.' ), $label );
			}
		}
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product ) {
			$description = wp_strip_all_tags( $product->get_short_description() ?: $product->get_name() );
		}
	}
	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( wp_trim_words( $description, 32, '' ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'uabb_meta_description', 2 );

function uabb_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

function uabb_remove_default_canonical() {
	if ( ! uabb_has_seo_plugin() ) {
		remove_action( 'wp_head', 'rel_canonical' );
	}
}
add_action( 'after_setup_theme', 'uabb_remove_default_canonical', 30 );

/** Emit language-specific canonical and hreflang links for the query-based Arabic/English switch. */
function uabb_language_canonical_links() {
	if ( is_admin() || uabb_has_seo_plugin() || is_search() ) {
		return;
	}
	if ( ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || ( function_exists( 'is_account_page' ) && is_account_page() ) ) ) ) {
		return;
	}
	$base_url = '';
	if ( is_front_page() || is_home() ) {
		$base_url = home_url( '/' );
	} elseif ( function_exists( 'is_shop' ) && is_shop() && function_exists( 'wc_get_page_permalink' ) ) {
		$base_url = wc_get_page_permalink( 'shop' );
	} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$term = get_queried_object();
		$base_url = $term instanceof WP_Term ? get_term_link( $term ) : '';
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		$base_url = get_permalink( get_queried_object_id() );
	} elseif ( is_singular() ) {
		$base_url = get_permalink();
	}
	if ( ! is_string( $base_url ) || ! $base_url || is_wp_error( $base_url ) ) {
		return;
	}
	$english = remove_query_arg( 'uabs_lang', $base_url );
	$arabic  = add_query_arg( 'uabs_lang', 'ar', $english );
	$current = isset( $_GET['uabs_lang'] ) && is_scalar( $_GET['uabs_lang'] ) ? sanitize_key( wp_unslash( $_GET['uabs_lang'] ) ) : ( is_rtl() ? 'ar' : 'en' );
	$canonical = 'ar' === $current ? $arabic : $english;
	echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
	echo '<link rel="alternate" hreflang="en-AE" href="' . esc_url( $english ) . '">' . "\n";
	echo '<link rel="alternate" hreflang="ar-AE" href="' . esc_url( $arabic ) . '">' . "\n";
	echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $english ) . '">' . "\n";
}
add_action( 'wp_head', 'uabb_language_canonical_links', 1 );

/** Publish the store identity and supported storefront languages as structured data. */
function uabb_store_structured_data() {
	if ( is_admin() || ( ! is_front_page() && ! is_home() ) || uabb_has_seo_plugin() ) {
		return;
	}
	$site_url = home_url( '/' );
	$logo_url = get_stylesheet_directory_uri() . '/assets/images/almasar-logo.png';
	$data     = array(
		'@context' => 'https://schema.org',
		'@graph'   => array(
			array(
				'@type'         => 'Organization',
				'@id'           => $site_url . '#organization',
				'name'          => 'Almasar Al Raed',
				'alternateName' => 'المسار الرائد',
				'url'           => $site_url,
				'logo'          => array( '@type' => 'ImageObject', 'url' => $logo_url ),
				'sameAs'        => array( 'https://www.instagram.com/almasar_alraed/' ),
				'areaServed'    => array( '@type' => 'Country', 'name' => 'United Arab Emirates' ),
			),
			array(
				'@type'     => 'WebSite',
				'@id'       => $site_url . '#website',
				'url'       => $site_url,
				'name'      => 'Almasar Al Raed',
				'publisher' => array( '@id' => $site_url . '#organization' ),
				'inLanguage' => array( 'en-AE', 'ar-AE' ),
			),
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'uabb_store_structured_data', 30 );

/** Keep transactional and empty catalog pages out of search results. */
function uabb_search_robots( $robots ) {
	$is_private_page = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || ( function_exists( 'is_account_page' ) && is_account_page() ) );
	$is_search_page = is_search();
	$is_author_page = is_author();
	$is_empty_category = function_exists( 'is_product_category' ) && is_product_category() && ! have_posts();
	if ( $is_private_page || $is_search_page || $is_author_page || $is_empty_category ) {
		$robots['noindex'] = true;
		$robots['follow'] = true;
		unset( $robots['index'], $robots['nofollow'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'uabb_search_robots' );

/** Keep customer-only and transactional pages out of the native WordPress sitemap. */
function uabb_sitemap_exclude_store_pages( $args, $post_type ) {
	if ( 'page' !== $post_type || ! function_exists( 'wc_get_page_id' ) ) {
		return $args;
	}
	$private_ids = array_filter( array_map( 'absint', array( wc_get_page_id( 'myaccount' ), wc_get_page_id( 'cart' ), wc_get_page_id( 'checkout' ) ) ) );
	$args['post__not_in'] = array_values( array_unique( array_merge( isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(), $private_ids ) ) );
	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'uabb_sitemap_exclude_store_pages', 10, 2 );

/** Do not advertise empty product categories in the native sitemap. */
function uabb_sitemap_hide_empty_product_categories( $args, $taxonomy ) {
	if ( 'product_cat' === $taxonomy ) {
		$args['hide_empty'] = true;
	}
	return $args;
}
add_filter( 'wp_sitemaps_taxonomies_query_args', 'uabb_sitemap_hide_empty_product_categories', 10, 2 );

/** Baseline response headers; TLS/HSTS belongs at the host or Cloudflare edge. */
function uabb_security_headers() {
	if ( headers_sent() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
}
add_action( 'send_headers', 'uabb_security_headers' );

/** This store does not use XML-RPC integrations or pingbacks. Keep WooCommerce REST available. */
add_filter( 'xmlrpc_enabled', '__return_false' );
function uabb_remove_pingback_method( $methods ) {
	unset( $methods['pingback.ping'] );
	return $methods;
}
add_filter( 'xmlrpc_methods', 'uabb_remove_pingback_method' );

/** Avoid public WordPress user-directory enumeration while keeping authenticated admin use. */
function uabb_private_user_directory( $endpoints ) {
	if ( ! current_user_can( 'list_users' ) ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\\d]+)'] );
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'uabb_private_user_directory' );

function uabb_department_body_class( $classes ) {
	$term = null;
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$term = get_queried_object();
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		$terms = get_the_terms( get_queried_object_id(), 'product_cat' );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$term = reset( $terms );
		}
	}
	$department = uabb_department_for_term( $term );
	if ( 'bags' === $department ) {
		$classes[] = 'uabb-department-bags';
	} elseif ( 'objects' === $department ) {
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
	if ( ! $term instanceof WP_Term || ! in_array( $term->slug, array( 'bags', 'everyday-objects', 'gadgets' ), true ) ) {
		return;
	}
	$is_objects = in_array( $term->slug, array( 'everyday-objects', 'gadgets' ), true );
	$image = get_template_directory_uri() . '/assets/images/' . ( $is_objects ? 'category-gadgets-studio.jpg' : 'category-bags-editorial.jpg' );
	$heading = $is_objects ? uabb_t( 'The Gadget Studio', 'استوديو الأجهزة' ) : uabb_t( 'The Carry Archive', 'أرشيف الحقائب' );
	$description = $is_objects ? uabb_t( 'A considered edit for sound, gaming, coffee, care, light and home.', 'اختيارات مدروسة للصوت والألعاب والقهوة والعناية والإضاءة والمنزل.' ) : uabb_t( 'A study in form, material and the everyday ritual of carrying.', 'تأمل في الشكل والخامة وتفاصيل ما نحمله كل يوم.' );
	$eyebrow = $is_objects ? uabb_t( 'Gadgets & home · 02', 'الأجهزة والمنزل · ٠٢' ) : uabb_t( 'Bags · 01', 'الحقائب · ٠١' );
	echo '<section class="uabb-archive-hero ' . ( $is_objects ? 'uabb-archive-hero-objects' : 'uabb-archive-hero-bags' ) . '"><div class="uabb-archive-hero-copy"><p class="uabb-eyebrow">' . esc_html( $eyebrow ) . '</p><h1>' . esc_html( $heading ) . '</h1><p>' . esc_html( $description ) . '</p></div><img src="' . esc_url( $image ) . '" alt="" fetchpriority="high"></section>';
	}
	add_action( 'woocommerce_before_shop_loop', 'uabb_department_archive_intro', 2 );

function uabb_department_archive_navigation() {
	if ( ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
		return;
	}
	$term = get_queried_object();
	if ( ! $term instanceof WP_Term ) {
		return;
	}
	$department = uabb_department_for_term( $term );
	if ( ! $department ) {
		return;
	}
	$root = 'bags' === $department ? 'bags' : 'everyday-objects';
	$root_term = get_term_by( 'slug', $root, 'product_cat' );
	if ( ! $root_term ) {
		return;
	}
	$children = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $term->term_id, 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
	if ( $term->term_id !== $root_term->term_id ) {
		$children = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $root_term->term_id, 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
	}
	if ( is_wp_error( $children ) || empty( $children ) ) {
		return;
	}
	echo '<nav class="uabb-archive-categories" aria-label="' . esc_attr( uabb_t( 'Explore categories', 'استكشف الفئات' ) ) . '">';
	foreach ( $children as $child ) {
		$url = get_term_link( $child );
		if ( is_wp_error( $url ) ) {
			continue;
		}
		$name = $child->name;
		if ( is_rtl() ) {
			$arabic_name = get_term_meta( $child->term_id, 'uabs_ar_name', true );
			if ( $arabic_name ) {
				$name = $arabic_name;
			}
		}
		$current = absint( $term->term_id ) === absint( $child->term_id ) ? ' aria-current="page"' : '';
		echo '<a href="' . esc_url( $url ) . '"' . $current . '>' . esc_html( $name ) . '<span aria-hidden="true">↗</span></a>';
	}
	echo '</nav>';
}
add_action( 'woocommerce_before_shop_loop', 'uabb_department_archive_navigation', 4 );
