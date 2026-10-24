<?php
get_header();
$image_base = get_template_directory_uri() . '/assets/images/';
$categories = array(
	array( 'slug' => 'handbags', 'title' => 'Handbags', 'arabic' => 'حقائب اليد', 'image' => 'category-handbags.jpg', 'alt' => 'A sculptural leather shoulder bag on limestone', 'alt_ar' => 'حقيبة كتف جلدية بتصميم أنيق على الحجر الجيري' ),
	array( 'slug' => 'small-leather-goods', 'title' => 'Small Leather Goods', 'arabic' => 'منتجات جلدية صغيرة', 'image' => 'category-leather-goods.jpg', 'alt' => 'Small leather goods in olive and clay', 'alt_ar' => 'محفظة وحافظة بطاقات جلدية بدرجات الزيتوني والطيني' ),
	array( 'slug' => 'accessories', 'title' => 'Accessories', 'arabic' => 'الإكسسوارات', 'image' => 'category-accessories.jpg', 'alt' => 'A silk scarf and leather pouch', 'alt_ar' => 'وشاح حريري وحافظة جلدية صغيرة' ),
	array( 'slug' => 'atelier-edit', 'title' => 'Atelier Edit', 'arabic' => 'اختيارات الأتيليه', 'image' => 'category-atelier.jpg', 'alt' => 'A woven leather bag in an atelier', 'alt_ar' => 'حقيبة جلدية منسوجة في أتيليه' ),
);
$published_products = wp_count_posts( 'product' );
$product_count = $published_products && isset( $published_products->publish ) ? absint( $published_products->publish ) : 0;
?>
<section class="uabb-hero">
	<div class="uabb-hero-media">
		<img src="<?php echo esc_url( $image_base . 'hero-atelier.jpg' ); ?>" alt="<?php echo esc_attr( uabb_t( 'A sculptural leather bag in a sunlit atelier', 'حقيبة جلدية بتصميم أنيق في أتيليه مضاء بالشمس' ) ); ?>" fetchpriority="high">
	</div>
	<div class="uabb-hero-copy">
		<p class="uabb-eyebrow"><?php echo esc_html( uabb_t( 'The atelier archive', 'أرشيف الأتيليه' ) ); ?></p>
		<h1><?php echo esc_html( uabb_t( 'Objects for a more intentional tomorrow', 'قطعٌ ليومٍ أكثر عناية' ) ); ?></h1>
		<p class="uabb-hero-description"><?php echo esc_html( uabb_t( 'Timeless handbags, reimagined for modern life.', 'حقائب خالدة برؤية جديدة لحياة اليوم.' ) ); ?></p>
		<a class="uabb-button" href="<?php echo esc_url( uabb_shop_url() ); ?>"><?php echo esc_html( uabb_t( 'Shop the edit', 'اكتشفي التشكيلة' ) ); ?><span aria-hidden="true">→</span></a>
		<p class="uabb-hero-note"><?php echo esc_html( uabb_t( 'Curated in the UAE · Made to move with you', 'اختيارات من الإمارات · ترافقك أينما ذهبتِ' ) ); ?></p>
	</div>
</section>

<section class="uabb-category-section">
	<div class="uabb-section-heading">
		<div>
			<p class="uabb-eyebrow"><?php echo esc_html( uabb_t( 'Explore by collection', 'اكتشفي حسب التشكيلة' ) ); ?></p>
			<h2><?php echo esc_html( uabb_t( 'The considered edit', 'اختيارات بعناية' ) ); ?></h2>
		</div>
		<a class="uabb-text-link" href="<?php echo esc_url( uabb_shop_url() ); ?>"><?php echo esc_html( uabb_t( 'View all', 'عرض الكل' ) ); ?><span aria-hidden="true">→</span></a>
	</div>
	<div class="uabb-category-grid">
		<?php foreach ( $categories as $index => $category ) : ?>
			<a class="uabb-category-card" href="<?php echo esc_url( uabb_category_url( $category['slug'] ) ); ?>">
				<img src="<?php echo esc_url( $image_base . $category['image'] ); ?>" alt="<?php echo esc_attr( uabb_t( $category['alt'], $category['alt_ar'] ) ); ?>" loading="lazy">
				<span class="uabb-category-index"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
				<span class="uabb-category-label"><?php echo esc_html( uabb_t( $category['title'], $category['arabic'] ) ); ?><span aria-hidden="true">→</span></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<section class="uabb-featured-section">
	<div class="uabb-section-heading">
		<div>
			<p class="uabb-eyebrow"><?php echo esc_html( uabb_t( 'A closer look', 'نظرة أقرب' ) ); ?></p>
			<h2><?php echo esc_html( uabb_t( 'The latest from Almasar', 'أحدث اختيارات المسار' ) ); ?></h2>
		</div>
		<a class="uabb-text-link" href="<?php echo esc_url( uabb_shop_url() ); ?>"><?php echo esc_html( uabb_t( 'Explore the archive', 'اكتشفي الأرشيف' ) ); ?><span aria-hidden="true">→</span></a>
	</div>
	<?php if ( $product_count > 0 ) : ?>
		<?php echo do_shortcode( '[products limit="4" columns="4" orderby="date" order="DESC"]' ); ?>
	<?php else : ?>
		<div class="uabb-empty-collection">
			<p class="uabb-empty-index">01 / 04</p>
			<div>
				<h3><?php echo esc_html( uabb_t( 'A new collection is being prepared.', 'تشكيلة جديدة قيد الإعداد.' ) ); ?></h3>
				<p><?php echo esc_html( uabb_t( 'The atelier archive will appear here as the collection is added.', 'ستظهر قطع الأتيليه هنا عند إضافة التشكيلة.' ) ); ?></p>
			</div>
			<a class="uabb-text-link" href="<?php echo esc_url( uabb_shop_url() ); ?>"><?php echo esc_html( uabb_t( 'Browse the archive', 'تصفحي الأرشيف' ) ); ?><span aria-hidden="true">→</span></a>
		</div>
	<?php endif; ?>
</section>

<section class="uabb-service-strip" aria-label="<?php echo esc_attr( uabb_t( 'Store services', 'خدمات المتجر' ) ); ?>">
	<div><span>01</span><p><?php echo esc_html( uabb_t( 'A considered edit, curated in the UAE', 'اختيارات بعناية من الإمارات' ) ); ?></p></div>
	<div><span>02</span><p><?php echo esc_html( uabb_t( 'Delivery across all seven emirates', 'توصيل إلى الإمارات السبع' ) ); ?></p></div>
	<div><span>03</span><p><?php echo esc_html( uabb_t( 'Personal assistance on WhatsApp', 'مساعدة شخصية عبر واتساب' ) ); ?></p></div>
</section>
<?php get_footer(); ?>
