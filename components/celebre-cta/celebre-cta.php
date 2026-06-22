<?php
/**
 * Component: Celebre CTA
 *
 * @context Archive Celebracoes / Page Celebration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cta_image = aptox_theme_image_uri( 'celebre-cta' );
$cta_meta  = aptox_theme_image_meta( 'celebre-cta' );
?>

<section
	class="celebre-cta"
	aria-labelledby="celebre-cta-title"
>
	<?php if ( $cta_image ) : ?>
		<img
			class="celebre-cta__image"
			src="<?php echo esc_url( $cta_image ); ?>"
			alt=""
			width="<?php echo esc_attr( (string) $cta_meta['width'] ); ?>"
			height="<?php echo esc_attr( (string) $cta_meta['height'] ); ?>"
			fetchpriority="high"
			decoding="async"
		>
	<?php endif; ?>

	<div class="celebre-cta-inner">
		<div class="celebre-cta-content">
			<h2 id="celebre-cta-title" class="celebre-cta-title">
				<?php esc_html_e( 'Celebrando a vida com mais', 'aptox' ); ?>
				<span class="celebre-cta-title-hand"><?php esc_html_e( 'amor', 'aptox' ); ?></span>
			</h2>

			<p class="celebre-cta-text">
				<?php esc_html_e( 'Encontre inspirações para todas as celebrações dessa estação. Dicas de como planejar, ideias de decoração e receitas típicas para cada festividade.', 'aptox' ); ?>
			</p>
		</div>
	</div>
</section>
