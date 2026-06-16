<?php
/**
 * Component: Index CTA
 *
 * @context index
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$manifesto_url = function_exists( 'aptox_get_manifesto_url' )
	? aptox_get_manifesto_url()
	: home_url( '/manifesto/' );

$cta_image = aptox_theme_image_uri( 'index-cta' );
$cta_meta  = aptox_theme_image_meta( 'index-cta' );
?>

<section
	class="index-cta"
	aria-labelledby="index-cta-title"
>
	<?php if ( $cta_image ) : ?>
		<img
			class="index-cta__image"
			src="<?php echo esc_url( $cta_image ); ?>"
			alt=""
			width="<?php echo esc_attr( (string) $cta_meta['width'] ); ?>"
			height="<?php echo esc_attr( (string) $cta_meta['height'] ); ?>"
			fetchpriority="high"
			decoding="async"
		>
	<?php endif; ?>

	<div class="index-cta-inner">

		<div class="index-cta-content">

			<h1
				id="index-cta-title"
				class="index-cta-title"
			>
				A vida feita com mais <span class="index-cta-title-hand">amor</span>
			</h1>

			<p class="index-cta-text">
				Esse é um espaço para quem ama viver as estações, cozinhar experiências, celebrar momentos e cuidar da casa de forma intencional e afetiva.
			</p>

			<a
				href="<?php echo esc_url( $manifesto_url ); ?>"
				class="index-cta-btn"
			>
				leia nosso manifesto
			</a>

		</div>

	</div>

</section>
