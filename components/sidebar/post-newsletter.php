<?php
/**
 * Post sidebar newsletter signup.
 *
 * @context Single Post (Celebre / Decor)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section
	class="post-side-newsletter"
	aria-labelledby="post-side-newsletter-title"
>
	<header class="post-side-newsletter__header">
		<div class="post-side-newsletter__header-inner">
			<figure class="post-side-newsletter__icon" aria-hidden="true">
				<img
					src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/ico-mailing.png' ); ?>"
					alt=""
					loading="lazy"
					width="36"
					height="36"
				>
			</figure>

			<h3 id="post-side-newsletter-title" class="post-side-newsletter__title-hand">
				<?php esc_html_e( 'Gostou desse post?', 'aptox' ); ?>
			</h3>

			<p class="post-side-newsletter__title-display">
				<?php esc_html_e( 'inscreva-se na nossa lista para receber novidades', 'aptox' ); ?>
			</p>
		</div>
	</header>

	<div class="post-side-newsletter__form">
		<?php echo do_shortcode( '[forminator_form id="7369"]' ); ?>
	</div>
</section>
