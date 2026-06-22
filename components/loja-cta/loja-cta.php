<?php
/**
 * Component: Loja CTA
 *
 * @context Archive Loja / Page Loja
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading = __( 'Loja', 'aptox' );
$page_id = get_queried_object_id();

if ( is_page() && $page_id > 0 && 'templates/page-loja.php' === get_page_template_slug( $page_id ) ) {
	$page_title = get_the_title( $page_id );

	if ( '' !== $page_title ) {
		$heading = $page_title;
	}
}
?>

<section class="loja-cta" aria-labelledby="loja-cta-title">
	<div class="loja-cta-inner">
		<div class="loja-cta-content">
			<h1 id="loja-cta-title" class="loja-cta-title">
				<?php echo esc_html( $heading ); ?>
			</h1>

			<p class="loja-cta-text">
				<?php esc_html_e( 'Nossa seleção de produtos especiais. Indicações de afiliadas que amamos e usamos no dia a dia — para tornar casa, mesa e celebrações ainda mais bonitas.', 'aptox' ); ?>
			</p>
		</div>
	</div>
</section>
