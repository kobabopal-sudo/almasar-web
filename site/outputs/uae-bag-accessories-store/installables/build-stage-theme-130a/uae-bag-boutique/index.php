<?php get_header(); ?>
<div class="uabb-content-wrap">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'uabb-article' ); ?>><h1><?php the_title(); ?></h1><?php the_content(); ?></article>
	<?php endwhile; the_posts_pagination(); else : ?>
		<p><?php echo esc_html( uabb_t( 'Nothing to show yet.', 'لا توجد محتويات حالياً.' ) ); ?></p>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
