<?php
/**
 * Component: Casa CTA
 *
 * @context Archive Casas / Page Casa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cta_image = aptox_theme_image_uri( 'home-cta' );
$cta_meta  = aptox_theme_image_meta( 'home-cta' );
?>

<section
	class="casa-cta"
	aria-labelledby="casa-cta-title"
>
	<?php if ( $cta_image ) : ?>
		<img
			class="casa-cta__image"
			src="<?php echo esc_url( $cta_image ); ?>"
			alt=""
			width="<?php echo esc_attr( (string) $cta_meta['width'] ); ?>"
			height="<?php echo esc_attr( (string) $cta_meta['height'] ); ?>"
			fetchpriority="high"
			decoding="async"
		>
	<?php endif; ?>
	<div class="casa-cta-inner">
		<div class="casa-cta-content">
			<h2 id="casa-cta-title" class="casa-cta-title">
				<?php esc_html_e( 'Cuidando no nosso lar com', 'aptox' ); ?>
				<span class="casa-cta-title-hand"><?php esc_html_e( 'amor', 'aptox' ); ?></span>
			</h2>

			<p class="casa-cta-text">
				<?php esc_html_e( 'Dicas para transformar sua casa em um verdadeiro lar: ideias para planejar um novo lar, reformas e pequenos projetos, inspiração para decorar cada ambiente, decoração sazonal, jardinagem, organização e rotinas.', 'aptox' ); ?>
			</p>
		</div>
	</div>
</section>
