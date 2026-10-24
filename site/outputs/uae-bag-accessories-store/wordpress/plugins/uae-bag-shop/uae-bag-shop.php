<?php
/**
 * Plugin Name: UAE Bag Shop
 * Description: Bilingual English/Arabic storefront helpers for a WooCommerce bags and accessories shop.
 * Version: 1.3.4
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Text Domain: uae-bag-shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UABS_VERSION', '1.3.4' );
define( 'UABS_FILE', __FILE__ );
define( 'UABS_URL', plugin_dir_url( __FILE__ ) );

final class UABS_Plugin {
	const OPTION = 'uabs_settings';
	const LANG_COOKIE = 'uabs_shop_lang';
	const META_AR_TITLE = 'uabs_ar_title';
	const META_AR_SHORT = 'uabs_ar_short_description';
	const META_AR_DESCRIPTION = 'uabs_ar_description';
	const META_AR_VARIATIONS = 'uabs_ar_variation_map';
	const META_AR_VARIATION_LABEL = 'uabs_ar_variation_label';
	const META_AR_CATEGORY = 'uabs_ar_name';
	const CRON_HOOK = 'uabs_expire_bank_transfer_order';
	const CRON_GROUP = 'uae-bag-shop';

	private static $instance;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'remember_language' ), 1 );
		add_filter( 'locale', array( $this, 'locale' ), 20 );
		add_filter( 'language_attributes', array( $this, 'language_attributes' ), 20 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'gettext', array( $this, 'translate_store_strings' ), 20, 3 );
		add_shortcode( 'uabs_language_switcher', array( $this, 'language_switcher' ) );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'init', array( $this, 'enable_myaccount_registration_once' ), 40 );
		add_filter( 'option_page_capability_uabs_settings_group', array( $this, 'settings_capability' ) );
		add_action( 'admin_notices', array( $this, 'woocommerce_notice' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_fields' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_product_fields' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variation_fields' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_variation_fields' ), 10, 2 );
		add_action( 'product_cat_add_form_fields', array( $this, 'category_add_field' ) );
		add_action( 'product_cat_edit_form_fields', array( $this, 'category_edit_field' ) );
		add_action( 'created_product_cat', array( $this, 'save_category_field' ) );
		add_action( 'edited_product_cat', array( $this, 'save_category_field' ) );

		add_filter( 'woocommerce_product_get_name', array( $this, 'product_name' ), 20, 2 );
		add_filter( 'woocommerce_product_variation_get_name', array( $this, 'variation_name' ), 20, 2 );
		add_filter( 'woocommerce_product_get_description', array( $this, 'product_description' ), 20, 2 );
		add_filter( 'woocommerce_product_get_short_description', array( $this, 'product_short_description' ), 20, 2 );
		add_filter( 'get_term', array( $this, 'category_name' ), 20, 2 );
		add_filter( 'woocommerce_attribute_label', array( $this, 'attribute_label' ), 20, 3 );
		add_filter( 'woocommerce_variation_option_name', array( $this, 'variation_option_name' ), 20, 2 );

		add_filter( 'woocommerce_currency', array( $this, 'currency' ) );
		add_filter( 'woocommerce_currency_symbol', array( $this, 'currency_symbol' ), 20, 2 );
		add_filter( 'default_checkout_billing_country', array( $this, 'default_country' ) );
		add_filter( 'default_checkout_shipping_country', array( $this, 'default_country' ) );
		add_filter( 'woocommerce_allowed_countries', array( $this, 'allowed_countries' ) );
		add_filter( 'woocommerce_states', array( $this, 'emirate_names' ) );
		add_filter( 'woocommerce_checkout_registration_enabled', '__return_false' );
		add_filter( 'woocommerce_enable_guest_checkout', '__return_true' );
		add_filter( 'woocommerce_checkout_fields', array( $this, 'checkout_fields' ) );
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'offline_gateways_only' ), 100 );
		add_filter( 'woocommerce_gateway_title', array( $this, 'gateway_title' ), 20, 2 );
		add_filter( 'woocommerce_package_rates', array( $this, 'emirate_shipping_only' ), 100, 2 );
		add_filter( 'woocommerce_shipping_methods', array( $this, 'register_shipping_method' ) );
		add_action( 'woocommerce_shipping_init', array( $this, 'load_shipping_method' ) );
		add_action( 'init', array( $this, 'seed_store_categories' ), 30 );
		add_action( 'admin_init', array( $this, 'ensure_store_attributes' ), 30 );
		add_action( 'woocommerce_before_shop_loop', array( $this, 'render_catalog_filters' ), 5 );
		add_action( 'woocommerce_no_products_found', array( $this, 'render_catalog_filters' ), 5 );
		add_action( 'pre_get_posts', array( $this, 'filter_catalog_query' ), 20 );
		add_filter( 'woocommerce_page_title', array( $this, 'store_page_title' ), 20 );
		add_filter( 'the_title', array( $this, 'woocommerce_page_title' ), 20, 2 );
		add_filter( 'the_content', array( $this, 'woocommerce_page_content' ), 8 );
		add_filter( 'document_title_parts', array( $this, 'woocommerce_document_title_parts' ), 20 );

		add_action( 'woocommerce_order_status_on-hold', array( $this, 'schedule_bank_order_expiry' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'unschedule_closed_order' ), 10, 4 );
		add_action( self::CRON_HOOK, array( $this, 'expire_bank_order' ), 10, 1 );
		add_action( 'woocommerce_thankyou', array( $this, 'whatsapp_order_link' ), 25 );
	}

	public function remember_language() {
		if ( is_admin() || empty( $_GET['uabs_lang'] ) ) {
			return;
		}
		$lang = sanitize_key( wp_unslash( $_GET['uabs_lang'] ) );
		if ( ! in_array( $lang, array( 'en', 'ar' ), true ) ) {
			return;
		}
		$secure = is_ssl();
		if ( PHP_VERSION_ID >= 70300 ) {
			setcookie( self::LANG_COOKIE, $lang, array(
				'expires'  => time() + YEAR_IN_SECONDS,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => $secure,
				'httponly' => true,
				'samesite' => 'Lax',
			) );
		} else {
			setcookie( self::LANG_COOKIE, $lang, time() + YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure, true );
		}
		$_COOKIE[ self::LANG_COOKIE ] = $lang;
	}

	public function current_language() {
		if ( ! empty( $_GET['uabs_lang'] ) ) {
			$lang = sanitize_key( wp_unslash( $_GET['uabs_lang'] ) );
			if ( in_array( $lang, array( 'en', 'ar' ), true ) ) {
				return $lang;
			}
		}
		if ( ! empty( $_COOKIE[ self::LANG_COOKIE ] ) ) {
			$lang = sanitize_key( wp_unslash( $_COOKIE[ self::LANG_COOKIE ] ) );
			if ( in_array( $lang, array( 'en', 'ar' ), true ) ) {
				return $lang;
			}
		}
		return 'en';
	}

	public function is_arabic() {
		return ! is_admin() && 'ar' === $this->current_language();
	}

	public function locale( $locale ) {
		if ( is_admin() ) {
			return $locale;
		}
		return $this->is_arabic() ? 'ar' : 'en_US';
	}

	public function language_attributes( $attributes ) {
		if ( is_admin() ) {
			return $attributes;
		}
		$lang = $this->is_arabic() ? 'ar' : 'en';
		$dir  = $this->is_arabic() ? 'rtl' : 'ltr';
		$attributes = preg_replace( '/\s*(?:lang|dir)="[^"]*"/i', '', $attributes );
		return trim( $attributes . ' lang="' . esc_attr( $lang ) . '" dir="' . esc_attr( $dir ) . '"' );
	}

	public function body_class( $classes ) {
		$classes[] = 'uabs-shop';
		$classes[] = $this->is_arabic() ? 'uabs-lang-ar' : 'uabs-lang-en';
		return $classes;
	}

	public function translate_store_strings( $translated, $text, $domain ) {
		if ( ! $this->is_arabic() ) {
			return $translated;
		}
		$dictionary = array(
			'Shop' => 'المتجر', 'Search' => 'بحث', 'Search products…' => 'ابحث عن المنتجات…',
			'My account' => 'حسابي', 'Login' => 'تسجيل الدخول', 'Register' => 'إنشاء حساب',
			'Username or email address' => 'اسم المستخدم أو البريد الإلكتروني', 'Email address' => 'البريد الإلكتروني',
			'Password' => 'كلمة المرور', 'Remember me' => 'تذكرني', 'Lost your password?' => 'هل نسيت كلمة المرور؟',
			'Create an account' => 'إنشاء حساب', 'A link to set a new password will be sent to your email address.' => 'سيتم إرسال رابط تعيين كلمة المرور إلى بريدك الإلكتروني.',
			'Add to cart' => 'أضف إلى السلة', 'View cart' => 'عرض السلة', 'Checkout' => 'إتمام الطلب',
			'Cart' => 'السلة', 'Continue shopping' => 'متابعة التسوق', 'Place order' => 'تأكيد الطلب',
			'Your order' => 'طلبك', 'Product' => 'المنتج', 'Price' => 'السعر', 'Quantity' => 'الكمية',
			'Total' => 'الإجمالي', 'Subtotal' => 'المجموع الفرعي', 'Shipping' => 'التوصيل',
			'Billing details' => 'بيانات العميل', 'Your cart is currently empty.' => 'سلتك فارغة حالياً.',
			'Proceed to checkout' => 'الانتقال إلى إتمام الطلب', 'Apply coupon' => 'تطبيق القسيمة',
			'Order received' => 'تم استلام الطلب', 'Thank you. Your order has been received.' => 'شكراً لك. تم استلام طلبك.',
			'No products were found matching your selection.' => 'لم يتم العثور على منتجات تطابق اختيارك.',
		);
		return isset( $dictionary[ $text ] ) ? $dictionary[ $text ] : $translated;
	}

	public function language_switcher() {
		$current = $this->current_language();
		$url     = remove_query_arg( 'uabs_lang' );
		$english = add_query_arg( 'uabs_lang', 'en', $url );
		$arabic  = add_query_arg( 'uabs_lang', 'ar', $url );
		return '<nav class="uabs-language-switcher" aria-label="' . esc_attr__( 'Language', 'uae-bag-shop' ) . '"><a href="' . esc_url( $english ) . '" lang="en"' . ( 'en' === $current ? ' aria-current="page"' : '' ) . '>English</a><span aria-hidden="true"> / </span><a href="' . esc_url( $arabic ) . '" lang="ar" dir="rtl"' . ( 'ar' === $current ? ' aria-current="page"' : '' ) . '>العربية</a></nav>';
	}

	public function enqueue_assets() {
		if ( is_admin() ) {
			return;
		}
		wp_enqueue_style( 'uabs-shop', UABS_URL . 'assets/store.css', array(), UABS_VERSION );
	}

	/** Enable account creation on the WooCommerce My Account page once. */
	public function enable_myaccount_registration_once() {
		if ( get_option( 'uabs_myaccount_registration_initialized' ) ) {
			return;
		}
		update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
		update_option( 'uabs_myaccount_registration_initialized', 'yes', false );
	}

	public function admin_menu() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_submenu_page( 'woocommerce', 'UAE Bag Shop', 'UAE Bag Shop', 'manage_woocommerce', 'uabs-settings', array( $this, 'settings_page' ) );
	}

	public function register_settings() {
		register_setting( 'uabs_settings_group', self::OPTION, array( 'sanitize_callback' => array( $this, 'sanitize_settings' ) ) );
	}

	public function settings_capability() {
		return 'manage_woocommerce';
	}

	public function seed_store_categories() {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return;
		}
		$bags_root = get_term_by( 'slug', 'bags', 'product_cat' );
		$legacy_edit = get_term_by( 'slug', 'atelier-edit', 'product_cat' );
		if ( $legacy_edit && ! get_term_by( 'slug', 'travel-organizers', 'product_cat' ) ) {
			wp_update_term( $legacy_edit->term_id, 'product_cat', array( 'name' => 'Travel Organisers', 'slug' => 'travel-organizers', 'parent' => $bags_root ? absint( $bags_root->term_id ) : 0 ) );
			update_term_meta( $legacy_edit->term_id, self::META_AR_CATEGORY, 'منظمات السفر' );
		}
		$categories = array(
			array( 'bags', 'Bags', 'الحقائب', 0 ),
			array( 'handbags', 'Handbags', 'حقائب اليد', 'bags' ),
			array( 'shoulder-bags', 'Shoulder Bags', 'حقائب الكتف', 'bags' ),
			array( 'crossbody-bags', 'Crossbody Bags', 'حقائب كروس', 'bags' ),
			array( 'tote-bags', 'Tote Bags', 'حقائب توت', 'bags' ),
			array( 'mini-bags', 'Mini Bags', 'حقائب صغيرة', 'bags' ),
			array( 'clutches', 'Clutches', 'حقائب يد صغيرة', 'bags' ),
			array( 'small-leather-goods', 'Small Leather Goods', 'منتجات جلدية صغيرة', 'bags' ),
			array( 'accessories', 'Bag Accessories', 'إكسسوارات الحقائب', 'bags' ),
			array( 'travel-organizers', 'Travel Organisers', 'منظمات السفر', 'bags' ),
			array( 'everyday-objects', 'Gadgets', 'الأجهزة والإكسسوارات', 0 ),
			array( 'speakers-and-audio', 'Speakers & Audio', 'السماعات والصوتيات', 'everyday-objects' ),
			array( 'bottles-and-hydration', 'Drinkware', 'الأكواب والقوارير', 'everyday-objects' ),
			array( 'gaming', 'Gaming', 'الألعاب', 'everyday-objects' ),
			array( 'kitchen-and-dining', 'Kitchen & Coffee', 'المطبخ والقهوة', 'everyday-objects' ),
			array( 'lighting', 'Lighting', 'الإضاءة', 'everyday-objects' ),
			array( 'personal-care', 'Personal Care', 'العناية الشخصية', 'everyday-objects' ),
			array( 'projectors', 'Projectors', 'أجهزة العرض', 'everyday-objects' ),
			array( 'home-fragrance', 'Home Fragrance', 'العطور المنزلية', 'everyday-objects' ),
			array( 'air-care', 'Air Care', 'تنقية الهواء', 'everyday-objects' ),
			array( 'camp-and-outdoors', 'Outdoor Gear', 'مستلزمات الرحلات', 'everyday-objects' ),
			array( 'travel-essentials', 'Travel & Utility', 'السفر والأدوات اليومية', 'everyday-objects' ),
			array( 'tech-and-utility', 'Tech & Utility', 'التقنية والأدوات', 'everyday-objects' ),
		);
		foreach ( $categories as $category ) {
			$term = get_term_by( 'slug', $category[0], 'product_cat' );
			if ( ! $term ) {
				$term = wp_insert_term( $category[1], 'product_cat', array( 'slug' => $category[0] ) );
			}
			$term_id = is_object( $term ) ? absint( $term->term_id ) : ( is_array( $term ) && isset( $term['term_id'] ) ? absint( $term['term_id'] ) : 0 );
			if ( $term_id && ! is_wp_error( $term ) ) {
				$parent_id = 0;
				if ( ! empty( $category[3] ) ) {
					$parent = get_term_by( 'slug', $category[3], 'product_cat' );
					$parent_id = $parent ? absint( $parent->term_id ) : 0;
				}
				$term_parent = is_object( $term ) ? absint( $term->parent ) : ( isset( $term['term_id'] ) ? absint( get_term_field( 'parent', $term_id, 'product_cat' ) ) : 0 );
				if ( $term_parent !== $parent_id ) {
					wp_update_term( $term_id, 'product_cat', array( 'parent' => $parent_id ) );
				}
				update_term_meta( $term_id, self::META_AR_CATEGORY, $category[2] );
			}
		}
		update_option( 'uabs_default_categories_seeded', 1, false );
	}

	public function ensure_store_attributes() {
		if ( get_option( 'uabs_store_attribute_terms_seeded_1_3' ) || ! current_user_can( 'manage_woocommerce' ) || ! function_exists( 'wc_create_attribute' ) || ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
			return;
		}
		$definitions = array(
			'color' => array( 'Color', array( 'Black' => 'أسود', 'Beige' => 'بيج', 'Brown' => 'بني', 'Olive' => 'زيتوني', 'Cream' => 'كريمي', 'White' => 'أبيض' ) ),
			'size' => array( 'Size', array( 'Mini' => 'ميني', 'Small' => 'صغير', 'Medium' => 'متوسط', 'Large' => 'كبير' ) ),
			'brand' => array( 'Brand', array() ),
		);
		$attributes = wc_get_attribute_taxonomies();
		$created_attribute = false;
		foreach ( $definitions as $slug => $definition ) {
			$exists = false;
			foreach ( $attributes as $attribute ) {
				if ( isset( $attribute->attribute_name ) && $slug === $attribute->attribute_name ) {
					$exists = true;
					break;
				}
			}
			if ( ! $exists ) {
				$result = wc_create_attribute( array(
					'name' => $definition[0],
					'slug' => $slug,
					'type' => 'select',
					'order_by' => 'menu_order',
					'has_archives' => false,
				) );
				if ( ! is_wp_error( $result ) ) {
					$created_attribute = true;
				}
			}
		}
		if ( $created_attribute ) {
			return;
		}
		foreach ( $definitions as $slug => $definition ) {
			$taxonomy = wc_attribute_taxonomy_name( $slug );
			if ( ! taxonomy_exists( $taxonomy ) ) {
				return;
			}
			foreach ( $definition[1] as $name => $arabic ) {
				$term = get_term_by( 'slug', sanitize_title( $name ), $taxonomy );
				if ( ! $term ) {
					$term = wp_insert_term( $name, $taxonomy, array( 'slug' => sanitize_title( $name ) ) );
				}
				$term_id = is_object( $term ) ? absint( $term->term_id ) : ( is_array( $term ) && isset( $term['term_id'] ) ? absint( $term['term_id'] ) : 0 );
				if ( $term_id && ! is_wp_error( $term ) && ! get_term_meta( $term_id, self::META_AR_CATEGORY, true ) ) {
					update_term_meta( $term_id, self::META_AR_CATEGORY, $arabic );
				}
			}
		}
		update_option( 'uabs_store_attribute_terms_seeded_1_3', 1, false );
	}

	private function catalog_filter_taxonomies() {
		$color = '';
		foreach ( array( 'pa_color', 'pa_colour' ) as $candidate ) {
			if ( taxonomy_exists( $candidate ) ) {
				$color = $candidate;
				break;
			}
		}
		$size = '';
		foreach ( array( 'pa_size', 'pa_bag_size' ) as $candidate ) {
			if ( taxonomy_exists( $candidate ) ) {
				$size = $candidate;
				break;
			}
		}
		$brand = '';
		foreach ( array( 'product_brand', 'pwb-brand', 'yith_product_brand', 'brand', 'pa_brand' ) as $candidate ) {
			if ( taxonomy_exists( $candidate ) ) {
				$brand = $candidate;
				break;
			}
		}
		return array(
			'category' => taxonomy_exists( 'product_cat' ) ? 'product_cat' : '',
			'color' => $color,
			'size' => $size,
			'brand' => $brand,
		);
	}

	private function catalog_department_root() {
		if ( isset( $_GET['uabs_department'] ) && is_scalar( $_GET['uabs_department'] ) ) {
			$requested = sanitize_key( wp_unslash( $_GET['uabs_department'] ) );
			$root_slug = 'bags' === $requested ? 'bags' : ( 'gadgets' === $requested ? 'everyday-objects' : '' );
			if ( $root_slug ) {
				$root = get_term_by( 'slug', $root_slug, 'product_cat' );
				if ( $root instanceof WP_Term ) {
					return $root;
				}
			}
		}
		if ( ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
			return null;
		}
		$term = get_queried_object();
		if ( ! $term instanceof WP_Term ) {
			return null;
		}
		$term_ids = array_merge( array( absint( $term->term_id ) ), array_map( 'absint', get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) ) );
		foreach ( $term_ids as $term_id ) {
			$ancestor = get_term( $term_id, 'product_cat' );
			if ( $ancestor instanceof WP_Term && in_array( $ancestor->slug, array( 'bags', 'everyday-objects' ), true ) ) {
				return $ancestor;
			}
		}
		return null;
	}

	private function catalog_department_facet_term_ids( $root, $taxonomy ) {
		global $wpdb;
		if ( ! $root instanceof WP_Term || ! is_string( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}
		$root_id      = absint( $root->term_id );
		$child_ids    = get_terms( array(
			'taxonomy'   => 'product_cat',
			'child_of'   => $root_id,
			'hide_empty' => false,
			'fields'     => 'ids',
		) );
		if ( is_wp_error( $child_ids ) ) {
			return array();
		}
		$category_ids = array_values( array_unique( array_map( 'absint', array_merge( array( $root_id ), (array) $child_ids ) ) ) );
		if ( empty( $category_ids ) ) {
			return array();
		}
		$category_placeholders = implode( ', ', array_fill( 0, count( $category_ids ), '%d' ) );
		$sql = "SELECT DISTINCT facet_tt.term_id
			FROM {$wpdb->term_relationships} AS facet_rel
			INNER JOIN {$wpdb->term_taxonomy} AS facet_tt ON facet_tt.term_taxonomy_id = facet_rel.term_taxonomy_id
			INNER JOIN {$wpdb->posts} AS product ON product.ID = facet_rel.object_id
			INNER JOIN {$wpdb->term_relationships} AS category_rel ON category_rel.object_id = product.ID
			INNER JOIN {$wpdb->term_taxonomy} AS category_tt ON category_tt.term_taxonomy_id = category_rel.term_taxonomy_id
			WHERE facet_tt.taxonomy = %s
				AND product.post_type = %s
				AND product.post_status = %s
				AND category_tt.taxonomy = %s
				AND category_tt.term_id IN ({$category_placeholders})";
		$args = array_merge( array( $taxonomy, 'product', 'publish', 'product_cat' ), $category_ids );
		$term_ids = $wpdb->get_col( $wpdb->prepare( $sql, $args ) );
		return array_map( 'absint', $term_ids );
	}

	public function render_catalog_filters() {
		if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() && ! is_search() ) ) {
			return;
		}
		$department_root = $this->catalog_department_root();
		if ( ! $department_root instanceof WP_Term ) {
			if ( is_shop() || is_search() ) {
				$bags_url = get_term_by( 'slug', 'bags', 'product_cat' );
				$objects_url = get_term_by( 'slug', 'everyday-objects', 'product_cat' );
				$bags_url = $bags_url instanceof WP_Term ? get_term_link( $bags_url ) : home_url( '/' );
				$objects_url = $objects_url instanceof WP_Term ? get_term_link( $objects_url ) : home_url( '/' );
				if ( is_wp_error( $bags_url ) ) { $bags_url = home_url( '/' ); }
				if ( is_wp_error( $objects_url ) ) { $objects_url = home_url( '/' ); }
				?>
				<section class="uabb-shop-gate" aria-label="<?php echo esc_attr( uabs_t( 'Choose a collection', 'اختر التشكيلة' ) ); ?>">
					<p class="uabb-eyebrow"><?php echo esc_html( uabs_t( 'Two distinct collections', 'تشكيلتان مستقلتان' ) ); ?></p>
					<h2><?php echo esc_html( uabs_t( 'Choose your world to browse.', 'اختر عالمك للتسوق.' ) ); ?></h2>
					<a href="<?php echo esc_url( $bags_url ); ?>"><?php echo esc_html( uabs_t( 'The Carry Archive · Bags', 'أرشيف الحقائب · حقائب' ) ); ?><span aria-hidden="true">↗</span></a>
					<a href="<?php echo esc_url( $objects_url ); ?>"><?php echo esc_html( uabs_t( 'Objects for living · Gadgets', 'مقتنيات للحياة · أجهزة' ) ); ?><span aria-hidden="true">↗</span></a>
				</section>
				<?php
			}
			return;
		}
		$taxonomies = $this->catalog_filter_taxonomies();
		$is_bags = 'bags' === $department_root->slug;
		$labels = array(
			'category' => array( 'Category', 'الفئة', 'All categories', 'كل الفئات' ),
			'color' => array( 'Color', 'اللون', 'All colors', 'كل الألوان' ),
		);
		if ( $is_bags ) {
			$labels['size'] = array( 'Size', 'المقاس', 'All sizes', 'كل المقاسات' );
		}
		$labels['brand'] = array( 'Brand', 'العلامة التجارية', 'All brands', 'كل العلامات التجارية' );
		$values = array();
		foreach ( array_keys( $labels ) as $key ) {
			$filter_key = 'uabs_' . $key;
			$raw_value = isset( $_GET[ $filter_key ] ) && is_scalar( $_GET[ $filter_key ] ) ? wp_unslash( $_GET[ $filter_key ] ) : '';
			$values[ $key ] = sanitize_title( $raw_value );
		}
		$action = get_term_link( $department_root );
		if ( is_wp_error( $action ) ) {
			$action = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
		}
		$minimum = isset( $_GET['uabs_price_min'] ) && is_scalar( $_GET['uabs_price_min'] ) ? sanitize_text_field( wp_unslash( $_GET['uabs_price_min'] ) ) : '';
		$maximum = isset( $_GET['uabs_price_max'] ) && is_scalar( $_GET['uabs_price_max'] ) ? sanitize_text_field( wp_unslash( $_GET['uabs_price_max'] ) ) : '';
		?>
		<form class="uabb-catalog-filters" method="get" action="<?php echo esc_url( $action ); ?>" data-store-department="<?php echo esc_attr( $is_bags ? 'bags' : 'objects' ); ?>">
			<?php if ( is_search() && get_search_query() ) : ?><input type="hidden" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"><?php endif; ?>
			<input type="hidden" name="post_type" value="product">
			<input type="hidden" name="uabs_department" value="<?php echo esc_attr( $is_bags ? 'bags' : 'gadgets' ); ?>">
			<button class="uabb-filter-toggle" type="button" aria-expanded="true" aria-controls="uabs-catalog-filter-fields">
				<span><?php echo esc_html( uabs_t( 'Refine your selection', 'تصفّح حسب تفضيلاتك' ) ); ?></span>
				<span class="uabb-filter-toggle-icon" aria-hidden="true">+</span>
			</button>
			<div class="uabb-filter-content" id="uabs-catalog-filter-fields" aria-hidden="false">
			<div class="uabb-filter-fields">
			<?php foreach ( $labels as $key => $label ) : ?>
				<div class="uabb-filter-field">
					<label for="uabs-filter-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( uabs_t( $label[0], $label[1] ) ); ?></label>
					<select id="uabs-filter-<?php echo esc_attr( $key ); ?>" name="uabs_<?php echo esc_attr( $key ); ?>"<?php echo ( 'brand' === $key && empty( $taxonomies[ $key ] ) ) ? ' disabled' : ''; ?>>
						<option value=""><?php echo esc_html( uabs_t( $label[2], $label[3] ) ); ?></option>
						<?php if ( ! empty( $taxonomies[ $key ] ) ) : ?>
							<?php
							$term_args = array( 'taxonomy' => $taxonomies[ $key ], 'hide_empty' => 'category' !== $key, 'orderby' => 'name', 'order' => 'ASC' );
							if ( 'category' === $key && $department_root instanceof WP_Term ) {
								$term_args['parent'] = absint( $department_root->term_id );
							} elseif ( $department_root instanceof WP_Term ) {
								$term_args['include'] = $this->catalog_department_facet_term_ids( $department_root, $taxonomies[ $key ] );
							}
							$terms = ( isset( $term_args['include'] ) && empty( $term_args['include'] ) ) ? array() : get_terms( $term_args );
							if ( ! is_wp_error( $terms ) ) :
								foreach ( $terms as $term ) :
									$default_category = 'category' === $key && (
										absint( get_option( 'default_product_cat' ) ) === absint( $term->term_id )
						|| 'uncategorized' === $term->slug
									);
									if ( $default_category ) {
										continue;
									}
									$name = $term->name;
									if ( $this->is_arabic() ) {
										$arabic_name = get_term_meta( $term->term_id, self::META_AR_CATEGORY, true );
										if ( $arabic_name ) {
											$name = $arabic_name;
										}
									}
									?>
									<option value="<?php echo esc_attr( $term->slug ); ?>"<?php selected( $values[ $key ], $term->slug ); ?>><?php echo esc_html( $name ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						<?php elseif ( 'brand' === $key ) : ?>
							<option value="" disabled><?php echo esc_html( uabs_t( 'Brand options appear with your products', 'ستظهر العلامات التجارية عند إضافة المنتجات' ) ); ?></option>
						<?php endif; ?>
					</select>
				</div>
			<?php endforeach; ?>
			<?php if ( ! $is_bags ) : ?>
				<div class="uabb-filter-field uabb-filter-price">
					<label for="uabs-filter-price-min"><?php echo esc_html( uabs_t( 'Price from · AED', 'السعر من · درهم' ) ); ?></label>
					<input id="uabs-filter-price-min" name="uabs_price_min" type="number" min="0" step="0.01" inputmode="decimal" value="<?php echo esc_attr( $minimum ); ?>">
				</div>
				<div class="uabb-filter-field uabb-filter-price">
					<label for="uabs-filter-price-max"><?php echo esc_html( uabs_t( 'Price to · AED', 'السعر إلى · درهم' ) ); ?></label>
					<input id="uabs-filter-price-max" name="uabs_price_max" type="number" min="0" step="0.01" inputmode="decimal" value="<?php echo esc_attr( $maximum ); ?>">
				</div>
			<?php endif; ?>
			<?php if ( isset( $_GET['orderby'] ) && is_scalar( $_GET['orderby'] ) ) : ?><input type="hidden" name="orderby" value="<?php echo esc_attr( sanitize_key( wp_unslash( $_GET['orderby'] ) ) ); ?>"><?php endif; ?>
			<div class="uabb-filter-actions">
				<button class="uabb-filter-submit" type="submit"><?php echo esc_html( uabs_t( 'Apply', 'تطبيق' ) ); ?></button>
				<a class="uabb-filter-clear" href="<?php echo esc_url( $action ); ?>"><?php echo esc_html( uabs_t( 'Clear', 'مسح' ) ); ?></a>
			</div>
			</div>
			</div>
		</form>
		<?php
	}

	public function filter_catalog_query( $query ) {
		$search_post_type = $query instanceof WP_Query ? $query->get( 'post_type' ) : null;
		$search_is_any_post_type = 'any' === $search_post_type || ( is_array( $search_post_type ) && in_array( 'any', $search_post_type, true ) );
		$is_product_search = $query instanceof WP_Query && $query->is_search() && ( 'product' === $search_post_type || $search_is_any_post_type || ( is_array( $search_post_type ) && in_array( 'product', $search_post_type, true ) ) );
		$product_taxonomies = get_object_taxonomies( 'product', 'names' );
		$is_product_taxonomy_archive = $query instanceof WP_Query && $query->is_tax( $product_taxonomies );
		if ( is_admin() || ! $query instanceof WP_Query || ! $query->is_main_query() || ( ! $query->is_post_type_archive( 'product' ) && ! $is_product_taxonomy_archive && ! $is_product_search ) ) {
			return;
		}
		$taxonomies = $this->catalog_filter_taxonomies();
		$tax_query = (array) $query->get( 'tax_query' );
		$department_root = $this->catalog_department_root();
		if ( $is_product_search && ! ( $department_root instanceof WP_Term ) ) {
			if ( $search_is_any_post_type ) {
				$searchable_post_types = get_post_types( array( 'publicly_queryable' => true, 'exclude_from_search' => false ), 'names' );
				$searchable_post_types = array_values( array_diff( $searchable_post_types, array( 'product' ) ) );
				if ( empty( $searchable_post_types ) ) {
					$query->set( 'post__in', array( 0 ) );
				} else {
					$query->set( 'post_type', $searchable_post_types );
				}
			} elseif ( is_array( $search_post_type ) ) {
				$remaining_post_types = array_values( array_diff( $search_post_type, array( 'product' ) ) );
				if ( empty( $remaining_post_types ) ) {
					$query->set( 'post__in', array( 0 ) );
				} else {
					$query->set( 'post_type', $remaining_post_types );
				}
			} else {
				$query->set( 'post__in', array( 0 ) );
			}
			return;
		}
		if ( $is_product_search && $department_root instanceof WP_Term && $search_post_type !== 'product' ) {
			$query->set( 'post_type', 'product' );
		}
		$requires_department = $query->is_post_type_archive( 'product' ) || $is_product_search || $is_product_taxonomy_archive;
		if ( $requires_department && ! ( $department_root instanceof WP_Term ) ) {
			$query->set( 'post__in', array( 0 ) );
			return;
		}
		if ( $department_root instanceof WP_Term && ( $query->is_post_type_archive( 'product' ) || $is_product_search || $is_product_taxonomy_archive ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => array( absint( $department_root->term_id ) ),
				'include_children' => true,
			);
		}
		$filter_keys = $department_root instanceof WP_Term && 'bags' === $department_root->slug
			? array( 'category', 'color', 'size', 'brand' )
			: array( 'category', 'color', 'brand' );
		foreach ( $filter_keys as $key ) {
			$filter_key = 'uabs_' . $key;
			if ( empty( $taxonomies[ $key ] ) || ! isset( $_GET[ $filter_key ] ) || ! is_scalar( $_GET[ $filter_key ] ) || '' === (string) $_GET[ $filter_key ] ) {
				continue;
			}
			$slug = sanitize_title( wp_unslash( $_GET[ $filter_key ] ) );
			$term = get_term_by( 'slug', $slug, $taxonomies[ $key ] );
			$term_in_department = true;
			if ( 'category' === $key && $department_root instanceof WP_Term && $term instanceof WP_Term ) {
				$term_ancestors = array_map( 'absint', get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) );
				$term_in_department = absint( $term->term_id ) !== absint( $department_root->term_id ) && in_array( absint( $department_root->term_id ), $term_ancestors, true );
			}
			if ( $slug && $term && ! is_wp_error( $term ) && $term_in_department ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomies[ $key ],
					'field' => 'slug',
					'terms' => array( $slug ),
					'include_children' => 'category' === $key,
				);
			}
		}
		if ( $department_root instanceof WP_Term && 'bags' !== $department_root->slug ) {
			$minimum = isset( $_GET['uabs_price_min'] ) && is_scalar( $_GET['uabs_price_min'] ) ? wp_unslash( $_GET['uabs_price_min'] ) : '';
			$maximum = isset( $_GET['uabs_price_max'] ) && is_scalar( $_GET['uabs_price_max'] ) ? wp_unslash( $_GET['uabs_price_max'] ) : '';
			$minimum = '' !== (string) $minimum && is_numeric( $minimum ) ? max( 0, (float) $minimum ) : null;
			$maximum = '' !== (string) $maximum && is_numeric( $maximum ) ? max( 0, (float) $maximum ) : null;
			if ( null !== $minimum || null !== $maximum ) {
				$price_clause = array( 'key' => '_price', 'type' => 'NUMERIC' );
				if ( null !== $minimum && null !== $maximum && $minimum > $maximum ) {
					$swap = $minimum;
					$minimum = $maximum;
					$maximum = $swap;
				}
				if ( null !== $minimum && null !== $maximum ) {
					$price_clause['value'] = array( $minimum, $maximum );
					$price_clause['compare'] = 'BETWEEN';
				} elseif ( null !== $minimum ) {
					$price_clause['value'] = $minimum;
					$price_clause['compare'] = '>=';
				} else {
					$price_clause['value'] = $maximum;
					$price_clause['compare'] = '<=';
				}
				$meta_query = (array) $query->get( 'meta_query' );
				$meta_query[] = $price_clause;
				$query->set( 'meta_query', $meta_query );
			}
		}
		if ( count( $tax_query ) > 0 ) {
			$tax_query['relation'] = 'AND';
			$query->set( 'tax_query', $tax_query );
		}
	}

	public function store_page_title( $title ) {
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			return uabs_t( 'Shop all products', 'كل المنتجات' );
		}
		return $title;
	}

	public function woocommerce_page_title( $title, $post_id ) {
		if ( is_admin() || ! $post_id ) {
			return $title;
		}
		$pages = array(
			'woocommerce_cart_page_id' => array( 'Your bag', 'سلتك' ),
			'woocommerce_checkout_page_id' => array( 'Checkout', 'إتمام الطلب' ),
			'woocommerce_myaccount_page_id' => array( 'My account', 'حسابي' ),
		);
		foreach ( $pages as $option => $labels ) {
			if ( absint( get_option( $option ) ) === absint( $post_id ) ) {
				return uabs_t( $labels[0], $labels[1] );
			}
		}
		return $title;
	}

	public function woocommerce_page_content( $content ) {
		if ( is_admin() || ! is_singular( 'page' ) ) {
			return $content;
		}
		$shortcodes = array(
			'woocommerce_cart_page_id' => '[woocommerce_cart]',
			'woocommerce_checkout_page_id' => '[woocommerce_checkout]',
			'woocommerce_myaccount_page_id' => '[woocommerce_my_account]',
		);
		foreach ( $shortcodes as $option => $shortcode ) {
			if ( absint( get_option( $option ) ) === get_queried_object_id() ) {
				return $shortcode;
			}
		}
		return $content;
	}

	public function woocommerce_document_title_parts( $parts ) {
		if ( is_admin() || ! is_singular( 'page' ) ) {
			return $parts;
		}
		$pages = array(
			'woocommerce_cart_page_id' => array( 'Your Bag', 'سلتك' ),
			'woocommerce_checkout_page_id' => array( 'Checkout', 'إتمام الطلب' ),
			'woocommerce_myaccount_page_id' => array( 'My Account', 'حسابي' ),
		);
		foreach ( $pages as $option => $labels ) {
			if ( absint( get_option( $option ) ) === get_queried_object_id() ) {
				$parts['title'] = uabs_t( $labels[0], $labels[1] );
				break;
			}
		}
		return $parts;
	}

	public function sanitize_settings( $input ) {
		$input = is_array( $input ) ? $input : array();
		$clean = $this->settings();
		$number = isset( $input['whatsapp_number'] ) && is_scalar( $input['whatsapp_number'] ) ? (string) $input['whatsapp_number'] : '';
		$clean['whatsapp_number'] = preg_replace( '/\D+/', '', $number );
		$raw_business_url = isset( $input['whatsapp_business_url'] ) && is_scalar( $input['whatsapp_business_url'] ) ? trim( (string) $input['whatsapp_business_url'] ) : '';
		$business_url = esc_url_raw( $raw_business_url, array( 'https' ) );
		$clean['whatsapp_business_url'] = preg_match( '~^https://wa\.me/message/[A-Za-z0-9]+/?$~i', $business_url ) ? $business_url : '';
		$hold_hours = isset( $input['hold_hours'] ) && is_scalar( $input['hold_hours'] ) ? absint( $input['hold_hours'] ) : 24;
		$clean['hold_hours'] = max( 1, min( 168, $hold_hours ) );
		$emirates = $this->emirates();
		foreach ( $emirates as $code => $name ) {
			$key = 'rate_' . strtolower( $code );
			$value = isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ? trim( (string) $input[ $key ] ) : '';
			$clean[ $key ] = '' === $value ? '' : wc_format_decimal( max( 0, (float) wc_format_decimal( $value ) ) );
		}
		return $clean;
	}

	public function settings() {
		$defaults = array(
			'whatsapp_number' => '',
			'whatsapp_business_url' => 'https://wa.me/message/GZQV4BN3HXM3M1',
			'hold_hours' => 24,
			'rate_az' => '', 'rate_aj' => '', 'rate_du' => '', 'rate_fu' => '', 'rate_rk' => '', 'rate_sh' => '', 'rate_uq' => '',
		);
		$saved = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
	}

	private function emirates() {
		return array(
			'AZ' => 'Abu Dhabi', 'AJ' => 'Ajman', 'DU' => 'Dubai', 'FU' => 'Fujairah',
			'RK' => 'Ras Al Khaimah', 'SH' => 'Sharjah', 'UQ' => 'Umm Al Quwain',
		);
	}

	public function settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$settings = $this->settings();
		?>
		<div class="wrap uabs-admin-wrap">
			<h1><?php echo esc_html__( 'UAE Bag Shop setup', 'uae-bag-shop' ); ?></h1>
			<p><?php echo esc_html__( 'Set the WhatsApp contact, emirate delivery charges, and the payment reservation window.', 'uae-bag-shop' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'uabs_settings_group' ); ?>
				<table class="form-table" role="presentation">
					<tr><th scope="row"><label for="uabs_whatsapp_business_url">WhatsApp Business short link</label></th><td><input id="uabs_whatsapp_business_url" name="<?php echo esc_attr( self::OPTION ); ?>[whatsapp_business_url]" value="<?php echo esc_attr( $settings['whatsapp_business_url'] ); ?>" class="regular-text" type="url" inputmode="url"><p class="description">Used for the storefront and order follow-up. The order number is shown on the confirmation page so the customer can include it in the chat.</p></td></tr>
					<tr><th scope="row"><label for="uabs_whatsapp_number">WhatsApp phone number (optional)</label></th><td><input id="uabs_whatsapp_number" name="<?php echo esc_attr( self::OPTION ); ?>[whatsapp_number]" value="<?php echo esc_attr( $settings['whatsapp_number'] ); ?>" class="regular-text" inputmode="tel"><p class="description">Optional. A full international number enables an order summary prefilled in the chat. The business short link works without it.</p></td></tr>
					<tr><th scope="row">Delivery charge by emirate (AED)</th><td>
						<?php foreach ( $this->emirates() as $code => $name ) : $key = 'rate_' . strtolower( $code ); ?>
							<label class="uabs-rate-row" for="uabs_<?php echo esc_attr( $key ); ?>"><span><?php echo esc_html( $name ); ?></span><input id="uabs_<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $settings[ $key ] ); ?>" type="number" min="0" step="0.01" placeholder="Set fee"></label>
						<?php endforeach; ?>
						<p class="description">Enter 0 for free delivery. Leave blank until a charge is decided; checkout will not offer delivery to an emirate without a configured rate.</p>
					</td></tr>
					<tr><th scope="row"><label for="uabs_hold_hours">Bank transfer reservation (hours)</label></th><td><input id="uabs_hold_hours" name="<?php echo esc_attr( self::OPTION ); ?>[hold_hours]" type="number" min="1" max="168" value="<?php echo esc_attr( $settings['hold_hours'] ); ?>"><p class="description">Default is 24 hours. Unpaid bank transfer orders are cancelled after this period and their stock is returned.</p></td></tr>
				</table>
			<?php submit_button(); ?>
			</form>
			<hr><h2>One-time WooCommerce setup</h2>
			<ol><li>Set the store country to United Arab Emirates and currency to AED.</li><li>Enable Direct Bank Transfer and Cash on Delivery under WooCommerce → Settings → Payments. Add bank details to Direct Bank Transfer.</li><li>Add “Delivery by Emirate” to the UAE shipping zone under WooCommerce → Settings → Shipping, then enter all delivery charges above. This method is the only delivery rate shown to UAE shoppers.</li><li>Set the shipping destination to the customer billing address to collect one delivery address.</li><li>Use the classic WooCommerce Cart and Checkout shortcodes on their pages so the guest checkout fields and payment choices use the standard WooCommerce flow.</li><li>Set the store administrator email under Settings → General to receive WooCommerce order notices.</li></ol>
		</div>
		<?php
	}

	public function woocommerce_notice() {
		if ( class_exists( 'WooCommerce' ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p><strong>UAE Bag Shop</strong> needs WooCommerce to be installed and active before store features can run.</p></div>';
	}

	public function product_fields() {
		global $post;
		$product = $post ? wc_get_product( $post->ID ) : false;
		if ( ! $product ) {
			return;
		}
		echo '<div class="options_group">';
		woocommerce_wp_text_input( array( 'id' => self::META_AR_TITLE, 'label' => 'Arabic product name', 'value' => $product->get_meta( self::META_AR_TITLE ), 'desc_tip' => true, 'description' => 'Arabic name shown when the storefront is switched to Arabic.' ) );
		woocommerce_wp_textarea_input( array( 'id' => self::META_AR_SHORT, 'label' => 'Arabic short description', 'value' => $product->get_meta( self::META_AR_SHORT ), 'rows' => 3 ) );
		woocommerce_wp_textarea_input( array( 'id' => self::META_AR_DESCRIPTION, 'label' => 'Arabic description', 'value' => $product->get_meta( self::META_AR_DESCRIPTION ), 'rows' => 5 ) );
		woocommerce_wp_textarea_input( array( 'id' => self::META_AR_VARIATIONS, 'label' => 'Arabic variation labels (JSON)', 'value' => $product->get_meta( self::META_AR_VARIATIONS ), 'rows' => 5, 'desc_tip' => true, 'description' => 'Optional JSON map: {"labels":{"Color":"اللون"},"values":{"pa_color":{"black":"أسود"}}}. Use attribute taxonomy slugs and option slugs.' ) );
		echo '</div>';
	}

	public function save_product_fields( $product ) {
		$keys = array( self::META_AR_TITLE, self::META_AR_SHORT, self::META_AR_DESCRIPTION, self::META_AR_VARIATIONS );
		foreach ( $keys as $key ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$value = wp_unslash( $_POST[ $key ] );
			if ( self::META_AR_VARIATIONS === $key ) {
				$value = sanitize_textarea_field( $value );
				$decoded = json_decode( $value, true );
				if ( '' !== $value && ! is_array( $decoded ) ) {
					continue;
				}
			} elseif ( self::META_AR_TITLE === $key ) {
				$value = sanitize_text_field( $value );
			} else {
				$value = wp_kses_post( $value );
			}
			$product->update_meta_data( $key, $value );
		}
	}

	public function variation_fields( $loop, $variation_data, $variation ) {
		$value = get_post_meta( $variation->ID, self::META_AR_VARIATION_LABEL, true );
		woocommerce_wp_text_input( array(
			'id' => self::META_AR_VARIATION_LABEL . '[' . absint( $loop ) . ']',
			'name' => self::META_AR_VARIATION_LABEL . '[' . absint( $loop ) . ']',
			'value' => $value,
			'label' => 'Arabic option label',
			'wrapper_class' => 'form-row form-row-full',
		) );
	}

	public function save_variation_fields( $variation_id, $index ) {
		if ( isset( $_POST[ self::META_AR_VARIATION_LABEL ][ $index ] ) ) {
			update_post_meta( $variation_id, self::META_AR_VARIATION_LABEL, sanitize_text_field( wp_unslash( $_POST[ self::META_AR_VARIATION_LABEL ][ $index ] ) ) );
		}
	}

	public function category_add_field() {
		wp_nonce_field( 'uabs_save_category', 'uabs_category_nonce' );
		?>
		<div class="form-field"><label for="uabs_ar_name">Arabic category name</label><input name="uabs_ar_name" id="uabs_ar_name" type="text" value=""><p>Shown when the storefront is switched to Arabic.</p></div>
		<?php
	}

	public function category_edit_field( $term ) {
		wp_nonce_field( 'uabs_save_category', 'uabs_category_nonce' );
		$value = get_term_meta( $term->term_id, self::META_AR_CATEGORY, true );
		?>
		<tr class="form-field"><th scope="row"><label for="uabs_ar_name">Arabic category name</label></th><td><input name="uabs_ar_name" id="uabs_ar_name" type="text" value="<?php echo esc_attr( $value ); ?>"><p class="description">Shown when the storefront is switched to Arabic.</p></td></tr>
		<?php
	}

	public function save_category_field( $term_id ) {
		if ( ! isset( $_POST['uabs_category_nonce'], $_POST['uabs_ar_name'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['uabs_category_nonce'] ) ), 'uabs_save_category' ) || ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}
		update_term_meta( $term_id, self::META_AR_CATEGORY, sanitize_text_field( wp_unslash( $_POST['uabs_ar_name'] ) ) );
	}

	public function product_name( $name, $product ) {
		if ( ! $this->is_arabic() || ! $product instanceof WC_Product ) {
			return $name;
		}
		$arabic = $product->get_meta( self::META_AR_TITLE );
		return $arabic ? $arabic : $name;
	}

	public function variation_name( $name, $variation ) {
		if ( ! $this->is_arabic() || ! $variation instanceof WC_Product_Variation ) {
			return $name;
		}
		$custom = $variation->get_meta( self::META_AR_VARIATION_LABEL );
		$parent_id = $variation->get_parent_id();
		$parent_ar = get_post_meta( $parent_id, self::META_AR_TITLE, true );
		if ( $parent_ar && $custom ) {
			return $parent_ar . ' — ' . $custom;
		}
		return $parent_ar ? $parent_ar : ( $custom ? $custom : $name );
	}

	public function product_description( $description, $product ) {
		if ( $this->is_arabic() && $product instanceof WC_Product ) {
			$arabic = $product->get_meta( self::META_AR_DESCRIPTION );
			if ( $arabic ) {
				return $arabic;
			}
		}
		return $description;
	}

	public function product_short_description( $description, $product ) {
		if ( $this->is_arabic() && $product instanceof WC_Product ) {
			$arabic = $product->get_meta( self::META_AR_SHORT );
			if ( $arabic ) {
				return $arabic;
			}
		}
		return $description;
	}

	public function category_name( $term, $taxonomy ) {
		if ( is_admin() || ! $this->is_arabic() || 'product_cat' !== $taxonomy || ! $term instanceof WP_Term ) {
			return $term;
		}
		$arabic = get_term_meta( $term->term_id, self::META_AR_CATEGORY, true );
		if ( $arabic ) {
			$term = clone $term;
			$term->name = $arabic;
		}
		return $term;
	}

	private function product_variation_map( $product ) {
		if ( $product instanceof WC_Product_Variation ) {
			$product = wc_get_product( $product->get_parent_id() );
		}
		if ( ! $product instanceof WC_Product ) {
			return array();
		}
		$decoded = json_decode( (string) $product->get_meta( self::META_AR_VARIATIONS ), true );
		return is_array( $decoded ) ? $decoded : array();
	}

	public function attribute_label( $label, $name, $product ) {
		if ( ! $this->is_arabic() ) {
			return $label;
		}
		$map = $this->product_variation_map( $product );
		$key = isset( $map['labels'][ $name ] ) ? $name : sanitize_title( $name );
		return isset( $map['labels'][ $key ] ) ? $map['labels'][ $key ] : $label;
	}

	public function variation_option_name( $name, $term ) {
		if ( ! $this->is_arabic() ) {
			return $name;
		}
		global $product;
		$map = $this->product_variation_map( $product );
		if ( ! isset( $map['values'] ) || ! is_array( $map['values'] ) ) {
			return $name;
		}
		$slug = $term instanceof WP_Term ? $term->slug : sanitize_title( (string) $name );
		if ( $term instanceof WP_Term ) {
			$attribute_map = isset( $map['values'][ $term->taxonomy ] ) ? $map['values'][ $term->taxonomy ] : array();
			return isset( $attribute_map[ $slug ] ) ? $attribute_map[ $slug ] : $name;
		}
		// For product-level (non-global) attributes WooCommerce passes the option
		// text instead of a term. Match the normalized option slug in each map.
		foreach ( $map['values'] as $attribute_map ) {
			if ( is_array( $attribute_map ) && isset( $attribute_map[ $slug ] ) ) {
				return $attribute_map[ $slug ];
			}
		}
		return $name;
	}

	public function currency( $currency ) {
		return 'AED';
	}

	public function currency_symbol( $symbol, $currency ) {
		if ( 'AED' !== $currency ) {
			return $symbol;
		}
		return $this->is_arabic() ? 'د.إ' : 'AED';
	}

	public function default_country( $country ) {
		return 'AE';
	}

	public function allowed_countries( $countries ) {
		return isset( $countries['AE'] ) ? array( 'AE' => $countries['AE'] ) : $countries;
	}

	public function emirate_names( $states ) {
		if ( ! $this->is_arabic() || empty( $states['AE'] ) ) {
			return $states;
		}
		$states['AE'] = array(
			'AZ' => 'أبو ظبي', 'AJ' => 'عجمان', 'DU' => 'دبي', 'FU' => 'الفجيرة',
			'RK' => 'رأس الخيمة', 'SH' => 'الشارقة', 'UQ' => 'أم القيوين',
		);
		return $states;
	}

	public function offline_gateways_only( $gateways ) {
		if ( is_admin() ) {
			return $gateways;
		}
		foreach ( array_keys( $gateways ) as $id ) {
			if ( ! in_array( $id, array( 'cod', 'bacs' ), true ) ) {
				unset( $gateways[ $id ] );
			}
		}
		return $gateways;
	}

	public function gateway_title( $title, $gateway_id ) {
		if ( ! $this->is_arabic() ) {
			return $title;
		}
		if ( 'cod' === $gateway_id ) {
			return 'الدفع عند الاستلام';
		}
		if ( 'bacs' === $gateway_id ) {
			return 'التحويل البنكي المباشر';
		}
		return $title;
	}

	public function emirate_shipping_only( $rates, $package ) {
		$country = isset( $package['destination']['country'] ) ? strtoupper( $package['destination']['country'] ) : '';
		if ( 'AE' !== $country ) {
			return $rates;
		}
		foreach ( $rates as $rate_id => $rate ) {
			if ( 0 !== strpos( $rate_id, 'uabs_emirate_shipping' ) ) {
				unset( $rates[ $rate_id ] );
			}
		}
		return $rates;
	}

	public function checkout_fields( $fields ) {
		if ( empty( $fields['billing'] ) ) {
			return $fields;
		}
		if ( isset( $fields['billing']['billing_email'] ) ) {
			$fields['billing']['billing_email']['required'] = false;
		}
		if ( ! $this->is_arabic() ) {
			return $fields;
		}
		$labels = array(
			'billing_first_name' => 'الاسم الأول', 'billing_last_name' => 'اسم العائلة',
			'billing_company' => 'الشركة (اختياري)', 'billing_country' => 'الدولة',
			'billing_address_1' => 'العنوان', 'billing_address_2' => 'تفاصيل إضافية (اختياري)',
			'billing_city' => 'المدينة / المنطقة', 'billing_state' => 'الإمارة',
			'billing_postcode' => 'الرمز البريدي', 'billing_phone' => 'رقم الهاتف',
			'billing_email' => 'البريد الإلكتروني (اختياري)',
		);
		foreach ( $labels as $key => $label ) {
			if ( isset( $fields['billing'][ $key ] ) ) {
				$fields['billing'][ $key ]['label'] = $label;
				$fields['billing'][ $key ]['placeholder'] = $label;
			}
		}
		return $fields;
	}

	public function load_shipping_method() {
		if ( ! class_exists( 'WC_Shipping_Method' ) || class_exists( 'UABS_Emirate_Shipping_Method' ) ) {
			return;
		}
		require_once plugin_dir_path( UABS_FILE ) . 'includes/class-uabs-emirate-shipping-method.php';
	}

	public function register_shipping_method( $methods ) {
		$methods['uabs_emirate_shipping'] = 'UABS_Emirate_Shipping_Method';
		return $methods;
	}

	public function schedule_bank_order_expiry( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || 'bacs' !== $order->get_payment_method() ) {
			return;
		}
		$settings = $this->settings();
		$hours = max( 1, absint( $settings['hold_hours'] ) );
		$timestamp = time() + ( $hours * HOUR_IN_SECONDS );
		$order->update_meta_data( '_uabs_bacs_hold_until', $timestamp );
		$order->save_meta_data();
		if ( function_exists( 'as_schedule_single_action' ) ) {
			as_schedule_single_action( $timestamp, self::CRON_HOOK, array( absint( $order_id ) ), self::CRON_GROUP );
		} else {
			wp_schedule_single_event( $timestamp, self::CRON_HOOK, array( absint( $order_id ) ) );
		}
	}

	private function schedule_expiry_at( $order_id, $timestamp ) {
		$args = array( absint( $order_id ) );
		if ( function_exists( 'as_schedule_single_action' ) ) {
			as_schedule_single_action( absint( $timestamp ), self::CRON_HOOK, $args, self::CRON_GROUP );
		} else {
			wp_schedule_single_event( absint( $timestamp ), self::CRON_HOOK, $args );
		}
	}

	public function unschedule_closed_order( $order_id, $from, $to, $order ) {
		if ( 'on-hold' === $to ) {
			return;
		}
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( self::CRON_HOOK, array( absint( $order_id ) ), self::CRON_GROUP );
		}
		wp_clear_scheduled_hook( self::CRON_HOOK, array( absint( $order_id ) ) );
	}

	public function expire_bank_order( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || ! $order->has_status( 'on-hold' ) || 'bacs' !== $order->get_payment_method() ) {
			return;
		}
		$settings = $this->settings();
		$hours = max( 1, absint( $settings['hold_hours'] ) );
		$hold_until = absint( $order->get_meta( '_uabs_bacs_hold_until', true ) );
		if ( ! $hold_until ) {
			$hold_until = time() + ( $hours * HOUR_IN_SECONDS );
			$order->update_meta_data( '_uabs_bacs_hold_until', $hold_until );
			$order->save_meta_data();
		}
		if ( time() < $hold_until ) {
			$this->schedule_expiry_at( $order_id, $hold_until );
			return;
		}
		$order->update_status( 'cancelled', sprintf( 'Automatically cancelled after %d hours without bank transfer confirmation.', $hours ) );
	}

	public function whatsapp_order_link( $order_id ) {
		$order = wc_get_order( $order_id );
		$settings = $this->settings();
		$raw_number = isset( $settings['whatsapp_number'] ) && is_scalar( $settings['whatsapp_number'] ) ? (string) $settings['whatsapp_number'] : '';
		$number = preg_replace( '/\D+/', '', $raw_number );
		$business_url = isset( $settings['whatsapp_business_url'] ) && is_string( $settings['whatsapp_business_url'] ) ? esc_url_raw( $settings['whatsapp_business_url'], array( 'https' ) ) : '';
		if ( ! $order || ( ! $number && ! preg_match( '~^https://wa\.me/message/[A-Za-z0-9]+/?$~i', $business_url ) ) || $order->has_status( array( 'failed', 'cancelled' ) ) ) {
			return;
		}
		if ( $number ) {
			$parts = array();
			foreach ( $order->get_items() as $item ) {
				$parts[] = $item->get_name() . ' × ' . $item->get_quantity();
			}
			$prefix = $this->is_arabic() ? 'مرحباً، أود متابعة الطلب رقم' : 'Hello, I would like to follow up on order';
			$message = $prefix . ' #' . $order->get_order_number() . "\n" . implode( "\n", $parts ) . "\n" . $order->get_formatted_order_total();
			$url = 'https://wa.me/' . $number . '?text=' . rawurlencode( $message );
			$body = $this->is_arabic() ? 'تم حفظ طلبك. يمكنك إرسال ملخصه إلينا عبر واتساب.' : 'Your order is saved. You can send its summary to us on WhatsApp.';
		} else {
			$url = $business_url;
			$body = $this->is_arabic()
				? 'تم حفظ طلبك رقم ' . $order->get_order_number() . '. أرسلي رقم الطلب في رسالتك عبر واتساب.'
				: 'Your order #' . $order->get_order_number() . ' is saved. Include this order number in your WhatsApp message.';
		}
		$label = $this->is_arabic() ? 'أرسل تفاصيل الطلب عبر واتساب' : 'Send order details on WhatsApp';
		echo '<section class="uabs-whatsapp-order"><p>' . esc_html( $body ) . '</p><a class="button uabs-whatsapp-button" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label ) . '</a></section>';
	}
}

UABS_Plugin::instance();

/** Small translation helper used by the bundled storefront theme. */
function uabs_t( $english, $arabic ) {
	return UABS_Plugin::instance()->is_arabic() ? $arabic : $english;
}
