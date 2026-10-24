<?php
get_header();
$image_base = get_template_directory_uri() . '/assets/images/';
?>
<section class="uabb-entry-intro">
	<p class="uabb-eyebrow"><?php echo esc_html( uabb_t( 'Almasar Al Raed · Dubai', 'المسار الرائد · دبي' ) ); ?></p>
	<h1><?php echo esc_html( uabb_t( 'Two worlds. One point of view.', 'عالمَان، ورؤية واحدة.' ) ); ?></h1>
</section>

<section class="uabb-department-grid" aria-label="<?php echo esc_attr( uabb_t( 'Choose a department', 'اختر قسماً' ) ); ?>">
	<a class="uabb-department-card uabb-department-card-bags" href="<?php echo esc_url( uabb_category_url( 'bags' ) ); ?>">
		<img src="<?php echo esc_url( $image_base . 'category-bags-editorial.jpg' ); ?>" alt="<?php echo esc_attr( uabb_t( 'A sculptural leather bag in a sunlit atelier', 'حقيبة جلدية بتصميم أنيق في أتيليه مضاء بالشمس' ) ); ?>" fetchpriority="high">
		<span class="uabb-department-shade" aria-hidden="true"></span>
		<span class="uabb-department-meta"><span>01 / <?php echo esc_html( uabb_t( 'THE CARRY ARCHIVE', 'أرشيف الحقائب' ) ); ?></span><span><?php echo esc_html( uabb_t( 'FORM & MOVEMENT', 'الشكل والحركة' ) ); ?></span></span>
		<span class="uabb-department-content"><span class="uabb-department-kicker"><?php echo esc_html( uabb_t( 'Form · material · movement', 'الشكل · الخامة · الحركة' ) ); ?></span><strong><?php echo esc_html( uabb_t( 'Bags', 'الحقائب' ) ); ?></strong><span class="uabb-department-description"><?php echo esc_html( uabb_t( 'Carry what matters. Keep the rest simple.', 'احمل ما يهمك، واترك الباقي ببساطة.' ) ); ?></span><span class="uabb-department-cta"><?php echo esc_html( uabb_t( 'Enter the bag collection', 'اكتشف مجموعة الحقائب' ) ); ?><span aria-hidden="true">↗</span></span></span>
	</a>
	<a class="uabb-department-card uabb-department-card-objects" href="<?php echo esc_url( uabb_category_url( 'everyday-objects' ) ); ?>">
		<img src="<?php echo esc_url( $image_base . 'category-gadgets-studio.jpg' ); ?>" alt="<?php echo esc_attr( uabb_t( 'Modern gadgets for sound, home and everyday rituals', 'أدوات عصرية للصوت والمنزل وتفاصيل الحياة اليومية' ) ); ?>" loading="lazy">
		<span class="uabb-department-shade" aria-hidden="true"></span>
		<span class="uabb-department-meta"><span>02 / <?php echo esc_html( uabb_t( 'THE GADGET STUDIO', 'استوديو الأجهزة' ) ); ?></span><span><?php echo esc_html( uabb_t( 'GADGETS & HOME', 'الأجهزة والمنزل' ) ); ?></span></span>
		<span class="uabb-department-content"><span class="uabb-department-kicker"><?php echo esc_html( uabb_t( 'Designed for the way you live', 'مصممة لترافق تفاصيل يومك' ) ); ?></span><strong><?php echo esc_html( uabb_t( 'Gadgets', 'الأجهزة والإكسسوارات' ) ); ?></strong><span class="uabb-department-description"><?php echo esc_html( uabb_t( 'Useful discoveries for home, travel, sound and everyday rituals.', 'اكتشافات عملية للمنزل والسفر والصوت وتفاصيل الحياة اليومية.' ) ); ?></span><span class="uabb-department-cta"><?php echo esc_html( uabb_t( 'Enter the gadgets collection', 'اكتشف مجموعة الأجهزة' ) ); ?><span aria-hidden="true">↗</span></span></span>
	</a>
</section>

<section class="uabb-entry-note">
	<p class="uabb-eyebrow"><?php echo esc_html( uabb_t( 'A house of two distinct worlds', 'دار لعالمين مختلفين' ) ); ?></p>
	<h2><?php echo esc_html( uabb_t( 'Choose your point of view.', 'اختر عالمك.' ) ); ?></h2>
	<p><?php echo esc_html( uabb_t( 'The Carry Archive for considered bags. The Gadget Studio for thoughtful finds across home, sound and travel.', 'أرشيف الحقائب لتصاميم منتقاة، واستوديو الأجهزة لاكتشافات مدروسة للمنزل والصوت والسفر.' ) ); ?></p>
</section>

<section class="uabb-service-strip" aria-label="<?php echo esc_attr( uabb_t( 'Store services', 'خدمات المتجر' ) ); ?>">
	<div><span>01</span><p><?php echo esc_html( uabb_t( 'Curated in the UAE', 'اختيارات من الإمارات' ) ); ?></p></div>
	<div><span>02</span><p><?php echo esc_html( uabb_t( 'Delivery across all seven emirates', 'توصيل إلى الإمارات السبع' ) ); ?></p></div>
	<div><span>03</span><p><?php echo esc_html( uabb_t( 'Personal assistance on WhatsApp', 'مساعدة شخصية عبر واتساب' ) ); ?></p></div>
</section>
<?php get_footer(); ?>
