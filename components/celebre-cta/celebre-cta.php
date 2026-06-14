<?php
/**
 * Component: Celebre CTA
 *
 * @context Archive Celebracoes / Page Celebration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cta_image = get_template_directory_uri() . '/assets/img/celebre-cta.png';
?>

<section
	class="celebre-cta"
	aria-labelledby="celebre-cta-title"
	style="--celebre-cta-image: url('<?php echo esc_url( $cta_image ); ?>');"
>
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
