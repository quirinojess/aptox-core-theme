<?php
/**
 * Component: Archive load more with crawlable pagination fallback.
 *
 * @context Archive grids
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$paged         = isset( $args['paged'] ) ? max( 1, (int) $args['paged'] ) : max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$max_pages     = isset( $args['max_pages'] ) ? (int) $args['max_pages'] : 0;
$next_url      = isset( $args['next_url'] ) ? (string) $args['next_url'] : '';
$grid_selector  = isset( $args['grid_selector'] ) ? (string) $args['grid_selector'] : '.archive-grid';
$card_selector  = isset( $args['card_selector'] ) ? (string) $args['card_selector'] : '.archive-card';
$label          = isset( $args['label'] ) ? (string) $args['label'] : __( 'Leia mais', 'aptox' );

if ( $max_pages <= $paged ) {
	return;
}

if ( '' === $next_url ) {
	$next_url = get_pagenum_link( $paged + 1 );
}

$next_label = sprintf(
	/* translators: %d: page number */
	__( 'Página %d', 'aptox' ),
	$paged + 1
);
?>

<div class="archive-load-more">
	<button
		type="button"
		class="next page-numbers"
		data-load-more-global
		data-grid-selector="<?php echo esc_attr( $grid_selector ); ?>"
		data-card-selector="<?php echo esc_attr( $card_selector ); ?>"
		data-next-url="<?php echo esc_url( $next_url ); ?>"
	>
		<?php echo esc_html( $label ); ?>
	</button>

	<a
		class="screen-reader-text"
		href="<?php echo esc_url( $next_url ); ?>"
		rel="next"
	>
		<?php echo esc_html( $next_label ); ?>
	</a>
</div>
