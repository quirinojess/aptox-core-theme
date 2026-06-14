<?php
/**
 * Component: Casa CTA
 *
 * @context Archive Casas / Page Casa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cta_image = get_template_directory_uri() . '/assets/img/home-cta.jpg';
?>

<section
	class="casa-cta"
	aria-labelledby="casa-cta-title"
	style="--casa-cta-image: url('<?php echo esc_url( $cta_image ); ?>');"
>
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
