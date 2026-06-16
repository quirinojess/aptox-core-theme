<?php
/**
 * Post Sidebar
 * Theme: Aptox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<aside class="post-side" aria-label="<?php esc_attr_e( 'Barra lateral do post', 'aptox' ); ?>">
	<section class="post-side-author">
		<?php
		get_template_part(
			'components/author/author',
			null,
			array(
				'layout' => 'vertical',
			)
		);
		?>
	</section>

	<?php get_template_part( 'components/sidebar/post-summary' ); ?>

	<?php get_template_part( 'components/sidebar/post-published-date' ); ?>

	<?php get_template_part( 'components/sidebar/post-newsletter' ); ?>

	<?php if ( is_active_sidebar( 'post-sidebar' ) ) : ?>
		<div class="post-side-widgets">
			<?php dynamic_sidebar( 'post-sidebar' ); ?>
		</div>
	<?php endif; ?>
</aside>
