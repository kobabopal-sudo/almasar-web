</main>
<footer class="uabb-footer">
	<div class="uabb-footer-inner">
		<div class="uabb-footer-intro">
			<a class="uabb-footer-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php echo uabb_brand_markup( 'uabb-brand-lockup' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<p><?php echo esc_html( uabb_t( 'Thoughtful finds for everyday and occasion.', 'اختيارات أنيقة لكل يوم ومناسبة.' ) ); ?></p>
		</div>
		<div class="uabb-footer-links">
			<a href="<?php echo esc_url( uabb_category_url( 'bags' ) ); ?>"><?php echo esc_html( uabb_t( 'Bags', 'الحقائب' ) ); ?></a>
			<a href="<?php echo esc_url( uabb_category_url( 'everyday-objects' ) ); ?>"><?php echo esc_html( uabb_t( 'Gadgets', 'الأجهزة والإكسسوارات' ) ); ?></a>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ?: home_url( '/' ) ); ?>"><?php echo esc_html( uabb_t( 'Your bag', 'سلتك' ) ); ?></a>
			<a href="<?php echo esc_url( uabb_support_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( uabb_t( 'WhatsApp', 'واتساب' ) ); ?></a>
			<a href="<?php echo esc_url( uabb_instagram_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( uabb_t( 'Instagram', 'إنستغرام' ) ); ?></a>
		</div>
		<p class="uabb-copyright">© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( uabb_t( 'Almasar Al Raed', 'المسار الرائد' ) ); ?></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
