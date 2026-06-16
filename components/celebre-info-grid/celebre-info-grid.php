<?php
/**
 * Component: Celebre info grid
 *
 * @context Archive Celebracoes / Page Celebration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$celebration_taxonomy = 'celebracao_categoria';
$archive_url          = home_url( '/celebracoes/' );
$icon_base            = get_template_directory_uri() . '/assets/icons/celebre/ocasioes/';

$term_url = static function ( array $slugs ) use ( $celebration_taxonomy, $archive_url ) {
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, $celebration_taxonomy );

		if ( $term && ! is_wp_error( $term ) ) {
			$url = get_term_link( $term );

			if ( ! is_wp_error( $url ) ) {
				return $url;
			}
		}
	}

	$fallback_slug = reset( $slugs );

	return trailingslashit( $archive_url ) . $fallback_slug . '/';
};

$items = array(
	array(
		'icon'  => $icon_base . 'celebre-ocasiao-aniversario.png',
		'title' => __( 'Aniversários', 'aptox' ),
		'text'  => __( 'Inspirações para celebrar o dia único daquela pessoa mais que especial.', 'aptox' ),
		'url'   => $term_url( array( 'aniversario', 'aniversarios' ) ),
	),
	array(
		'icon'  => $icon_base . 'celebre-ocasiao-casamento.png',
		'title' => __( 'Casamento', 'aptox' ),
		'text'  => __( 'Ideias, dicas de planejamento e registros desse momento único na vida.', 'aptox' ),
		'url'   => $term_url( array( 'casamento', 'casamentos' ) ),
	),
	array(
		'icon'  => $icon_base . 'celebre-ocasiao-namorados.png',
		'title' => __( 'Namorados', 'aptox' ),
		'text'  => __( 'Dicas e inspirações para manter o romance em qualquer época do ano.', 'aptox' ),
		'url'   => $term_url( array( 'dia-dos-namorados', 'namorados' ) ),
	),
);
?>

<section
	class="celebre-info-grid info-grid"
	aria-label="<?php esc_attr_e( 'Categorias de celebração', 'aptox' ); ?>"
>
	<div
		class="info-grid-inner celebre-info-grid__inner"
	>
		<?php foreach ( $items as $item ) : ?>
			<article class="info-grid-item celebre-info-grid__item">
				<figure class="info-grid-icon celebre-info-grid__icon" aria-hidden="true">
					<img
						src="<?php echo esc_url( $item['icon'] ); ?>"
						alt=""
						loading="lazy"
					>
				</figure>

				<div class="info-grid-content celebre-info-grid__content">
					<h2 class="info-grid-title">
						<?php echo esc_html( $item['title'] ); ?>
					</h2>

					<p class="info-grid-text">
						<?php echo esc_html( $item['text'] ); ?>
					</p>

					<a class="celebre-info-grid__link" href="<?php echo esc_url( $item['url'] ); ?>">
						<?php esc_html_e( 'Leia mais', 'aptox' ); ?>
					</a>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
