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
<?php
$uabb_current_department = '';
if ( function_exists( 'is_product_category' ) && is_product_category() ) {
	$uabb_current_department = uabb_department_for_term( get_queried_object() );
} elseif ( isset( $_GET['uabs_department'] ) && is_scalar( $_GET['uabs_department'] ) ) {
	$uabb_current_department = 'bags' === sanitize_key( wp_unslash( $_GET['uabs_department'] ) ) ? 'bags' : 'objects';
}
?>
<div class="uabb-announcement"><?php echo esc_html( uabb_t( 'Two worlds, one point of view · Curated in the UAE', 'عالمَان برؤية واحدة · اختيارات من الإمارات' ) ); ?></div>
<header class="uabb-header">
	<div class="uabb-header-main">
		<a class="uabb-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( uabb_t( 'Almasar Al Raed home', 'الرئيسية — المسار الرائد' ) ); ?>">
			<?php echo uabb_brand_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
		<form class="uabb-search" role="search" method="get" action="<?php echo esc_url( uabb_shop_url() ); ?>">
			<label class="screen-reader-text" for="uabb-product-search"><?php echo esc_html( uabb_t( 'Search products', 'ابحثي عن المنتجات' ) ); ?></label>
			<?php if ( $uabb_current_department ) : ?><input type="hidden" name="uabs_department" value="<?php echo esc_attr( 'bags' === $uabb_current_department ? 'bags' : 'gadgets' ); ?>"><?php else : ?>
				<label class="screen-reader-text" for="uabb-search-department"><?php echo esc_html( uabb_t( 'Choose a collection', 'اختر التشكيلة' ) ); ?></label>
				<select id="uabb-search-department" class="uabb-search-department" name="uabs_department"><option value="bags"><?php echo esc_html( uabb_t( 'Bags', 'الحقائب' ) ); ?></option><option value="gadgets"><?php echo esc_html( uabb_t( 'Gadgets', 'الأجهزة' ) ); ?></option></select>
			<?php endif; ?>
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
	<?php
	$uabb_bag_menu = array(
		array( 'shoulder-bags', 'Shoulder bags', 'حقائب كتف' ),
		array( 'crossbody-bags', 'Crossbody bags', 'حقائب كروس' ),
		array( 'tote-bags', 'Tote bags', 'حقائب توت' ),
		array( 'mini-bags', 'Mini bags', 'حقائب صغيرة' ),
		array( 'clutches', 'Evening & clutches', 'حقائب سهرة وكلتش' ),
		array( 'small-leather-goods', 'Small leather goods', 'منتجات جلدية صغيرة' ),
	);
	$uabb_object_menu = array(
		array( 'speakers-and-audio', 'Speakers & audio', 'السماعات والصوتيات' ),
		array( 'bottles-and-hydration', 'Drinkware', 'الأكواب والقوارير' ),
		array( 'gaming', 'Gaming', 'الألعاب' ),
		array( 'kitchen-and-coffee', 'Kitchen & coffee', 'المطبخ والقهوة' ),
		array( 'lighting', 'Lighting', 'الإضاءة' ),
		array( 'personal-care', 'Personal care', 'العناية الشخصية' ),
		array( 'projectors', 'Projectors', 'أجهزة العرض' ),
		array( 'home-fragrance', 'Home fragrance', 'العطور المنزلية' ),
		array( 'air-care', 'Air care', 'تنقية الهواء' ),
		array( 'outdoor-travel', 'Outdoor & travel', 'الرحلات والسفر' ),
	);
	$uabb_images = get_template_directory_uri() . '/assets/images/';
	?>
	<nav class="uabb-nav" aria-label="<?php echo esc_attr( uabb_t( 'Explore collections', 'استكشف التشكيلات' ) ); ?>">
		<details class="uabb-nav-mega uabb-nav-mega-bags">
			<summary><?php echo esc_html( uabb_t( 'Bags', 'الحقائب' ) ); ?><span aria-hidden="true">⌄</span></summary>
			<div class="uabb-mega-panel">
				<div class="uabb-mega-side">
					<p class="uabb-eyebrow"><?php echo esc_html( uabb_t( 'The Carry Archive', 'أرشيف الحقائب' ) ); ?></p>
					<?php foreach ( $uabb_bag_menu as $item ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'uabs_category', $item[0], uabb_category_url( 'bags' ) ) ); ?>"><?php echo esc_html( uabb_t( $item[1], $item[2] ) ); ?><span aria-hidden="true">↗</span></a>
					<?php endforeach; ?>
					<a class="uabb-mega-all" href="<?php echo esc_url( uabb_category_url( 'bags' ) ); ?>"><?php echo esc_html( uabb_t( 'Explore all bags', 'استكشف كل الحقائب' ) ); ?><span aria-hidden="true">↗</span></a>
				</div>
				<div class="uabb-mega-tiles uabb-mega-tiles-bags">
					<a href="<?php echo esc_url( uabb_category_url( 'bags' ) ); ?>"><img src="<?php echo esc_url( $uabb_images . 'category-handbags.jpg' ); ?>" alt=""><span><?php echo esc_html( uabb_t( 'The Carry Archive', 'أرشيف الحقائب' ) ); ?></span></a>
					<a href="<?php echo esc_url( uabb_category_url( 'bags' ) ); ?>"><img src="<?php echo esc_url( $uabb_images . 'category-leather-goods.jpg' ); ?>" alt=""><span><?php echo esc_html( uabb_t( 'Form & material', 'الشكل والخامة' ) ); ?></span></a>
					<a class="uabb-mega-note" href="<?php echo esc_url( uabb_category_url( 'bags' ) ); ?>"><span>ALMASAR / 01</span><strong><?php echo esc_html( uabb_t( 'A quieter kind of statement.', 'حضور هادئ بتفاصيل واثقة.' ) ); ?></strong><small><?php echo esc_html( uabb_t( 'Shapes made for the everyday.', 'تصاميم ترافق تفاصيل اليوم.' ) ); ?></small></a>
				</div>
			</div>
		</details>
		<details class="uabb-nav-mega uabb-nav-mega-objects">
			<summary><?php echo esc_html( uabb_t( 'Gadgets & home', 'الأجهزة والمنزل' ) ); ?><span aria-hidden="true">⌄</span></summary>
			<div class="uabb-mega-panel">
				<div class="uabb-mega-side">
				<p class="uabb-eyebrow"><?php echo esc_html( uabb_t( 'The Gadget Studio', 'استوديو الأجهزة' ) ); ?></p>
					<?php foreach ( $uabb_object_menu as $item ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'uabs_category', $item[0], uabb_category_url( 'everyday-objects' ) ) ); ?>"><?php echo esc_html( uabb_t( $item[1], $item[2] ) ); ?><span aria-hidden="true">↗</span></a>
					<?php endforeach; ?>
				<a class="uabb-mega-all" href="<?php echo esc_url( uabb_category_url( 'everyday-objects' ) ); ?>"><?php echo esc_html( uabb_t( 'Explore all gadgets', 'استكشف كل الأجهزة' ) ); ?><span aria-hidden="true">↗</span></a>
				</div>
				<div class="uabb-mega-tiles uabb-mega-tiles-objects">
					<a href="<?php echo esc_url( uabb_category_url( 'everyday-objects' ) ); ?>"><img src="<?php echo esc_url( $uabb_images . 'hero-everyday-objects.jpg' ); ?>" alt=""><span><?php echo esc_html( uabb_t( 'Field objects', 'مقتنيات الميدان' ) ); ?></span></a>
					<a class="uabb-mega-note" href="<?php echo esc_url( uabb_category_url( 'everyday-objects' ) ); ?>"><span>ALMASAR / 02</span><strong><?php echo esc_html( uabb_t( 'Useful, with a point of view.', 'منفعة بتصميم له حضوره.' ) ); ?></strong><small><?php echo esc_html( uabb_t( 'Sound · ritual · home · travel', 'صوت · طقوس · منزل · سفر' ) ); ?></small></a>
					<a class="uabb-mega-note uabb-mega-note-blue" href="<?php echo esc_url( uabb_category_url( 'everyday-objects' ) ); ?>"><span>OBJECTS STUDIO</span><strong><?php echo esc_html( uabb_t( 'Everyday, reconsidered.', 'تفاصيل يومية برؤية جديدة.' ) ); ?></strong><small><?php echo esc_html( uabb_t( 'Discover the objects collection.', 'اكتشف مجموعة المقتنيات.' ) ); ?></small></a>
				</div>
			</div>
		</details>
		<a class="uabb-nav-story" href="<?php echo esc_url( uabb_category_url( 'bags' ) ); ?>"><?php echo esc_html( uabb_t( 'The Carry Archive', 'أرشيف الحقائب' ) ); ?></a>
		<a class="uabb-nav-story uabb-nav-story-new-gadgets" href="<?php echo esc_url( add_query_arg( 'orderby', 'date', uabb_category_url( 'everyday-objects' ) ) ); ?>"><?php echo esc_html( uabb_t( 'New Gadgets', 'أحدث الأجهزة' ) ); ?></a>
	</nav>
	<nav class="uabb-collection-switch" aria-label="<?php echo esc_attr( uabb_t( 'Choose a collection', 'اختر التشكيلة' ) ); ?>">
		<a class="uabb-collection-switch-bags<?php echo 'bags' === $uabb_current_department ? ' is-active' : ''; ?>" href="<?php echo esc_url( uabb_category_url( 'bags' ) ); ?>"<?php echo 'bags' === $uabb_current_department ? ' aria-current="page"' : ''; ?>><span>01</span><strong><?php echo esc_html( uabb_t( 'The Carry Archive', 'أرشيف الحقائب' ) ); ?></strong><small><?php echo esc_html( uabb_t( 'Bags', 'الحقائب' ) ); ?></small><b aria-hidden="true">↗</b></a>
		<span class="uabb-collection-switch-mark"><img src="<?php echo esc_url( $uabb_images . 'almasar-logo.png' ); ?>" alt="<?php echo esc_attr( uabb_t( 'Almasar Al Raed', 'المسار الرائد' ) ); ?>"></span>
		<a class="uabb-collection-switch-gadgets<?php echo 'objects' === $uabb_current_department ? ' is-active' : ''; ?>" href="<?php echo esc_url( uabb_category_url( 'everyday-objects' ) ); ?>"<?php echo 'objects' === $uabb_current_department ? ' aria-current="page"' : ''; ?>><span>02</span><strong><?php echo esc_html( uabb_t( 'The Gadget Studio', 'استوديو الأجهزة' ) ); ?></strong><small><?php echo esc_html( uabb_t( 'Gadgets & home', 'الأجهزة والمنزل' ) ); ?></small><b aria-hidden="true">↗</b></a>
	</nav>
</header>
<main class="uabb-main">
