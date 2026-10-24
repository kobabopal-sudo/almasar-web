<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="uabb-announcement"><?php echo esc_html( uabb_t( 'Two worlds, one point of view · Curated in the UAE', 'عالمَان برؤية واحدة · اختيارات من الإمارات' ) ); ?></div>
<header class="uabb-header">
	<div class="uabb-header-main">
		<a class="uabb-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( uabb_t( 'Almasar Al Raed home', 'الرئيسية — المسار الرائد' ) ); ?>">
			<?php echo uabb_brand_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
		<form class="uabb-search" role="search" method="get" action="<?php echo esc_url( uabb_shop_url() ); ?>">
			<label class="screen-reader-text" for="uabb-product-search"><?php echo esc_html( uabb_t( 'Search products', 'ابحثي عن المنتجات' ) ); ?></label>
			<input id="uabb-product-search" type="search" name="s" placeholder="<?php echo esc_attr( uabb_t( 'Search the collection', 'ابحثي في التشكيلة' ) ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
			<input type="hidden" name="post_type" value="product">
			<button type="submit" aria-label="<?php echo esc_attr( uabb_t( 'Search', 'بحث' ) ); ?>"><?php echo esc_html( uabb_t( 'Search', 'بحث' ) ); ?></button>
		</form>
		<div class="uabb-header-actions">
			<?php if ( shortcode_exists( 'uabs_language_switcher' ) ) { echo do_shortcode( '[uabs_language_switcher]' ); } ?>
			<a href="<?php echo esc_url( uabb_support_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( uabb_t( 'WhatsApp', 'واتساب' ) ); ?></a>
			<a href="<?php echo esc_url( uabb_instagram_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( uabb_t( 'Instagram', 'إنستغرام' ) ); ?></a>
			<?php echo uabb_cart_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
	<nav class="uabb-nav" aria-label="<?php echo esc_attr( uabb_t( 'Main navigation', 'التنقل الرئيسي' ) ); ?>">
		<a class="uabb-nav-department" href="<?php echo esc_url( uabb_category_url( 'bags' ) ); ?>"><?php echo esc_html( uabb_t( 'Bags', 'الحقائب' ) ); ?></a>
		<a class="uabb-nav-department uabb-nav-objects" href="<?php echo esc_url( uabb_category_url( 'everyday-objects' ) ); ?>"><?php echo esc_html( uabb_t( 'Everyday Objects', 'أغراض يومية' ) ); ?></a>
		<a href="<?php echo esc_url( add_query_arg( 'orderby', 'date', uabb_shop_url() ) ); ?>"><?php echo esc_html( uabb_t( 'New in', 'وصل حديثاً' ) ); ?></a>
		<a href="<?php echo esc_url( uabb_category_url( 'atelier-edit' ) ); ?>"><?php echo esc_html( uabb_t( 'The Atelier Edit', 'اختيارات الأتيليه' ) ); ?></a>
		<a href="<?php echo esc_url( uabb_shop_url() ); ?>"><?php echo esc_html( uabb_t( 'Shop all', 'تسوق الكل' ) ); ?></a>
	</nav>
</header>
<main class="uabb-main">
