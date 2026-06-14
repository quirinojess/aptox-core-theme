<?php
/**
 * Component: Index CTA
 *
 * @context index
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sobre_page = get_page_by_path( 'sobre' );
$sobre_url  = $sobre_page ? get_permalink( $sobre_page->ID ) : home_url( '/sobre/' );

$cta_image = get_template_directory_uri() . '/assets/img/index-cta.png';
?>

<section
	class="index-cta"
	aria-labelledby="index-cta-title"
	style="--index-cta-image: url('<?php echo esc_url( $cta_image ); ?>');"
>

	<div class="index-cta-inner">

		<div class="index-cta-content">

			<h2
				id="index-cta-title"
				class="index-cta-title"
			>
				a vida feita com mais <span class="index-cta-title-hand">amor</span>
			</h2>

			<p class="index-cta-text">
				Esse é um espaço para quem ama viver as estações, cozinhar experiências, celebrar momentos e cuidar da casa de forma intencional e afetiva.
			</p>

			<a
				href="<?php echo esc_url( $sobre_url ); ?>"
				class="index-cta-btn"
			>
				leia nosso manifesto
			</a>

		</div>

	</div>

</section>
