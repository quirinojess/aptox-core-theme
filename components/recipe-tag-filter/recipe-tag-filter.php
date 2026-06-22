<?php
/**
 * Component: Recipe tag filter labels
 *
 * @context Receitas sticky filter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$group = isset( $args['group'] ) ? sanitize_key( (string) $args['group'] ) : 'all';

$meal_filters = array(
	array(
		'label' => __( 'café da manhã', 'aptox' ),
		'slugs' => array( 'cafe-da-manha', 'café-da-manhã' ),
	),
	array(
		'label' => __( 'almoço', 'aptox' ),
		'slugs' => array( 'almoco', 'almoço' ),
	),
	array(
		'label' => __( 'café da tarde', 'aptox' ),
		'slugs' => array( 'cafe-da-tarde', 'café-da-tarde' ),
	),
	array(
		'label' => __( 'jantar', 'aptox' ),
		'slugs' => array( 'jantar' ),
	),
	array(
		'label' => __( 'sobremesa', 'aptox' ),
		'slugs' => array( 'sobremesa' ),
	),
	array(
		'label' => __( 'entradas', 'aptox' ),
		'slugs' => array( 'entradas' ),
	),
	array(
		'label' => __( 'acompanhamentos', 'aptox' ),
		'slugs' => array( 'acompanhamentos', 'acompanamentos' ),
	),
);

$culinary_filters = array(
	array(
		'label' => __( 'vegana', 'aptox' ),
		'slugs' => array( 'vegana' ),
	),
	array(
		'label' => __( 'inglesa', 'aptox' ),
		'slugs' => array( 'inglesa' ),
	),
	array(
		'label' => __( 'argentina', 'aptox' ),
		'slugs' => array( 'argentina' ),
	),
	array(
		'label' => __( 'chinesa', 'aptox' ),
		'slugs' => array( 'chinesa' ),
	),
	array(
		'label' => __( 'japonesa', 'aptox' ),
		'slugs' => array( 'japonesa' ),
	),
	array(
		'label' => __( 'árabe', 'aptox' ),
		'slugs' => array( 'arabe', 'árabe' ),
	),
	array(
		'label' => __( 'brasileira', 'aptox' ),
		'slugs' => array( 'brasileira' ),
	),
	array(
		'label' => __( 'francesa', 'aptox' ),
		'slugs' => array( 'francesa' ),
	),
	array(
		'label' => __( 'mexicana', 'aptox' ),
		'slugs' => array( 'mexicana' ),
	),
	array(
		'label' => __( 'italiana', 'aptox' ),
		'slugs' => array( 'italiana' ),
	),
	array(
		'label' => __( 'indiana', 'aptox' ),
		'slugs' => array( 'indiana' ),
	),
	array(
		'label' => __( 'americana', 'aptox' ),
		'slugs' => array( 'americana' ),
	),
);

$active_slug = aptox_get_receita_tag_query_slug();

$build_tag_links = static function ( array $filters ) use ( $active_slug ) {
	$links = array();

	foreach ( $filters as $tag_filter ) {
		$url = aptox_get_receita_tag_link( $tag_filter['slugs'] );

		if ( '' === $url ) {
			continue;
		}

		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query_args );
		$resolved_slug = isset( $query_args['tag'] ) ? sanitize_title( (string) $query_args['tag'] ) : '';

		$links[] = array(
			'label'     => $tag_filter['label'],
			'url'       => $url,
			'is_active' => ( '' !== $active_slug && $active_slug === $resolved_slug ),
		);
	}

	return $links;
};

$filters = array();

if ( 'meals' === $group ) {
	$filters = $meal_filters;
} elseif ( 'culinary' === $group ) {
	$filters = $culinary_filters;
} else {
	$filters = array_merge( $meal_filters, $culinary_filters );
}

$tag_links = $build_tag_links( $filters );

if ( empty( $tag_links ) ) {
	return;
}
?>

<div class="recipe-tag-filter recipe-tag-filter--<?php echo esc_attr( $group ); ?>">
	<div class="recipe-tag-filter__track-wrap">
		<div class="recipe-tag-filter__tags">
			<?php foreach ( $tag_links as $tag_link ) : ?>
				<a
					class="recipe-tag-filter__tag<?php echo ! empty( $tag_link['is_active'] ) ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( $tag_link['url'] ); ?>"
					<?php echo ! empty( $tag_link['is_active'] ) ? ' aria-current="page"' : ''; ?>
				>
					<?php echo esc_html( $tag_link['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</div>
