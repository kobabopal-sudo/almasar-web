<?php
get_header();
$image_base = get_template_directory_uri() . '/assets/images/';
$published_products = wp_count_posts( 'product' );
$product_count = $published_products && isset( $published_products->publish ) ? absint( $published_products->publish ) : 0;
$bag_categories = array(
	array( 'shoulder-bags', 'Shoulder Bags', 'حقائب الكتف' ),
	array( 'crossbody-bags', 'Crossbody Bags', 'حقائب كروس' ),
	array( 'tote-bags', 'Tote Bags', 'حقائب توت' ),
	array( 'small-leather-goods', 'Small Leather Goods', 'منتجات جلدية صغيرة' ),
	array( 'atelier-edit', 'The Atelier Edit', 'اختيارات الأتيليه' ),
);
$object_categories = array(
	array( 'kitchen-and-dining', 'Kitchen & Dining', 'للمطبخ والمائدة' ),
	array( 'camp-and-outdoors', 'Camp & Outdoors', 'للتخييم والرحلات' ),
	array( 'bottles-and-hydration', 'Bottles & Hydration', 'القوارير والترطيب' ),
	array( 'travel-essentials', 'Travel Essentials', 'مستلزمات السفر' ),
	array( 'tech-and-utility', 'Tech & Utility', 'التقنية والأدوات' ),
);
?>
<section class="uabb-entry-intro">
	<p class="uabb-eyebrow"><?php echo esc_html( uabb_t( 'Almasar Al Raed · Dubai', 'المسار الرائد · دبي' ) ); ?></p>
	<h1><?php echo esc_html( uabb_t( 'Two worlds. One point of view.', 'عالمَان، ورؤية واحدة.' ) ); ?></h1>
	<p><?php echo esc_html( uabb_t( 'Choose a collection to begin.', 'اختر التشكيلة التي تناسبك.' ) ); ?></p>
</section>

<section class="uabb-department-grid" aria-label="<?php echo esc_attr( uabb_t( 'Choose a department', 'اختر قسماً' ) ); ?>">
	<a class="uabb-department-card uabb-department-card-bags" href="<?php echo esc_url( uabb_category_url( 'bags' ) ); ?>">
		<img src="<?php echo esc_url( $image_base . 'hero-atelier.jpg' ); ?>" alt="<?php echo esc_attr( uabb_t( 'A sculptural leather bag in a sunlit atelier', 'حقيبة جلدية بتصميم أنيق في أتيليه مضاء بالشمس' ) ); ?>" fetchpriority="high">
		<span class="uabb-department-shade" aria-hidden="true"></span>
		<span class="uabb-department-meta"><span>01 / <?php echo esc_html( uabb_t( 'THE CARRY ARCHIVE', 'أرشيف الحقائب' ) ); ?></span><span><?php echo esc_html( uabb_t( 'THE ATELIER', 'الأتيليه' ) ); ?></span></span>
		<span class="uabb-department-content"><span class="uabb-department-kicker"><?php echo esc_html( uabb_t( 'Form · material · movement', 'الشكل · الخامة · الحركة' ) ); ?></span><strong><?php echo esc_html( uabb_t( 'Bags', 'الحقائب' ) ); ?></strong><span class="uabb-department-description"><?php echo esc_html( uabb_t( 'Carry what matters. Keep the rest simple.', 'احمل ما يهمك، واترك الباقي ببساطة.' ) ); ?></span><span class="uabb-department-cta"><?php echo esc_html( uabb_t( 'Enter the bag collection', 'اكتشف مجموعة الحقائب' ) ); ?><span aria-hidden="true">↗</span></span></span>
	</a>
	<a class="uabb-department-card uabb-department-card-objects" href="<?php echo esc_url( uabb_category_url( 'everyday-objects' ) ); ?>">
		<img src="<?php echo esc_url( $image_base . 'hero-everyday-objects.jpg' ); ?>" alt="<?php echo esc_attr( uabb_t( 'Modern objects for travel, hydration and the outdoors', 'أغراض عصرية للسفر والترطيب والرحلات' ) ); ?>" loading="lazy">
		<span class="uabb-department-shade" aria-hidden="true"></span>
		<span class="uabb-department-meta"><span>02 / <?php echo esc_html( uabb_t( 'FIELD OBJECTS', 'أغراض ميدانية' ) ); ?></span><span><?php echo esc_html( uabb_t( 'OBJECTS STUDIO', 'استوديو الأغراض' ) ); ?></span></span>
		<span class="uabb-department-content"><span class="uabb-department-kicker"><?php echo esc_html( uabb_t( 'Designed for the way you move', 'مصممة لترافق إيقاع يومك' ) ); ?></span><strong><?php echo esc_html( uabb_t( 'Everyday Objects', 'أغراض يومية' ) ); ?></strong><span class="uabb-department-description"><?php echo esc_html( uabb_t( 'Useful things for kitchen, camp, hydration and travel.', 'أغراض عملية للمطبخ والرحلات والسفر.' ) ); ?></span><span class="uabb-department-cta"><?php echo esc_html( uabb_t( 'Explore everyday objects', 'اكتشف الأغراض اليومية' ) ); ?><span aria-hidden="true">↗</span></span></span>
	</a>
</section>

<section class="uabb-worlds-section">
	<div class="uabb-world-panel uabb-world-panel-bags">
		<div class="uabb-world-heading">
			<p class="uabb-eyebrow"><?php echo esc_html( uabb_t( '01 · The carry archive', '٠١ · أرشيف الحقائب' ) ); ?></p>
			<h2><?php echo esc_html( uabb_t( 'A study in the everyday.', 'تأمل في تفاصيل كل يوم.' ) ); ?></h2>
			<p><?php echo esc_html( uabb_t( 'Thoughtful shapes, selected for a life in motion.', 'تصاميم مدروسة ترافق حياة متحركة.' ) ); ?></p>
		</div>
		<nav class="uabb-category-links" aria-label="<?php echo esc_attr( uabb_t( 'Bag categories', 'فئات الحقائب' ) ); ?>">
			<?php foreach ( $bag_categories as $index => $category ) : ?>
				<a href="<?php echo esc_url( uabb_category_url( $category[0] ) ); ?>"><span><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span><?php echo esc_html( uabb_t( $category[1], $category[2] ) ); ?><span class="uabb-category-arrow" aria-hidden="true">↗</span></a>
			<?php endforeach; ?>
		</nav>
	</div>
	<div class="uabb-world-panel uabb-world-panel-objects">
		<div class="uabb-world-heading">
			<p class="uabb-eyebrow"><?php echo esc_html( uabb_t( '02 · Field objects', '٠٢ · أغراض ميدانية' ) ); ?></p>
			<h2><?php echo esc_html( uabb_t( 'Utility, reimagined.', 'وظائف يومية برؤية جديدة.' ) ); ?></h2>
			<p><?php echo esc_html( uabb_t( 'Small discoveries for the kitchen, the open air and the road.', 'اختيارات صغيرة للمطبخ والهواء الطلق والطريق.' ) ); ?></p>
		</div>
		<nav class="uabb-category-links" aria-label="<?php echo esc_attr( uabb_t( 'Everyday object categories', 'فئات الأغراض اليومية' ) ); ?>">
			<?php foreach ( $object_categories as $index => $category ) : ?>
				<a href="<?php echo esc_url( uabb_category_url( $category[0] ) ); ?>"><span><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span><?php echo esc_html( uabb_t( $category[1], $category[2] ) ); ?><span class="uabb-category-arrow" aria-hidden="true">↗</span></a>
			<?php endforeach; ?>
		</nav>
	</div>
</section>

<section class="uabb-featured-section uabb-home-featured">
	<div class="uabb-section-heading">
		<div>
			<p class="uabb-eyebrow"><?php echo esc_html( uabb_t( 'A closer look', 'نظرة أقرب' ) ); ?></p>
			<h2><?php echo esc_html( uabb_t( 'The latest from Almasar', 'أحدث اختيارات المسار' ) ); ?></h2>
		</div>
		<a class="uabb-text-link" href="<?php echo esc_url( uabb_shop_url() ); ?>"><?php echo esc_html( uabb_t( 'View the collection', 'عرض التشكيلة' ) ); ?><span aria-hidden="true">↗</span></a>
	</div>
	<?php if ( $product_count > 0 ) : ?>
		<?php echo do_shortcode( '[products limit="4" columns="4" orderby="date" order="DESC"]' ); ?>
	<?php else : ?>
		<div class="uabb-empty-collection">
			<p class="uabb-empty-index">01 / 02</p>
			<div>
				<h3><?php echo esc_html( uabb_t( 'The first collection is taking shape.', 'التشكيلة الأولى قيد التجهيز.' ) ); ?></h3>
				<p><?php echo esc_html( uabb_t( 'Your product stories will appear here when the collection is added.', 'ستظهر منتجاتك هنا عند إضافة التشكيلة.' ) ); ?></p>
			</div>
			<a class="uabb-text-link" href="<?php echo esc_url( uabb_shop_url() ); ?>"><?php echo esc_html( uabb_t( 'Browse both departments', 'استكشف القسمين' ) ); ?><span aria-hidden="true">↗</span></a>
		</div>
	<?php endif; ?>
</section>

<section class="uabb-service-strip" aria-label="<?php echo esc_attr( uabb_t( 'Store services', 'خدمات المتجر' ) ); ?>">
	<div><span>01</span><p><?php echo esc_html( uabb_t( 'Curated in the UAE', 'اختيارات من الإمارات' ) ); ?></p></div>
	<div><span>02</span><p><?php echo esc_html( uabb_t( 'Delivery across all seven emirates', 'توصيل إلى الإمارات السبع' ) ); ?></p></div>
	<div><span>03</span><p><?php echo esc_html( uabb_t( 'Personal assistance on WhatsApp', 'مساعدة شخصية عبر واتساب' ) ); ?></p></div>
</section>
<?php get_footer(); ?>
