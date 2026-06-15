<?php
/**
 * Component: Recipe sticky filter (categories + tags)
 *
 * @context Receitas archive / taxonomy / page template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_slug   = aptox_get_receita_tag_query_slug();
$initial_panel = 'categories';

$meal_slugs = array(
	'cafe-da-manha',
	'almoco',
	'cafe-da-tarde',
	'jantar',
	'sobremesa',
	'entradas',
	'acompanhamentos',
	'acompanamentos',
);

$culinary_slugs = array(
	'vegana',
	'oriental',
	'brasileira',
	'francesa',
	'mexicana',
	'italiana',
	'indiana',
	'americana',
);

if ( '' !== $active_slug ) {
	if ( in_array( $active_slug, $culinary_slugs, true ) ) {
		$initial_panel = 'culinary';
	} elseif ( in_array( $active_slug, $meal_slugs, true ) ) {
		$initial_panel = 'meals';
	}
}

$sticky_open_class = '' !== $active_slug ? ' is-open' : '';

$panels = array(
	'categories' => __( 'Categorias', 'aptox' ),
	'meals'      => __( 'Refeições', 'aptox' ),
	'culinary'   => __( 'Culinária', 'aptox' ),
);
?>

<div id="recipe-sticky" class="<?php echo esc_attr( trim( $sticky_open_class ) ); ?>" data-initial-panel="<?php echo esc_attr( $initial_panel ); ?>">
	<div class="recipe-sticky-header">
		<div class="recipe-sticky-nav">
			<span class="recipe-sticky-title"><?php esc_html_e( 'filtre por', 'aptox' ); ?>:</span>

			<nav
				class="recipe-sticky-tabs"
				aria-label="<?php esc_attr_e( 'Filtrar receitas', 'aptox' ); ?>"
				role="tablist"
			>
				<?php
				$panel_keys = array_keys( $panels );
				$last_key     = end( $panel_keys );

				foreach ( $panels as $panel_id => $panel_label ) :
					$is_active = ( $initial_panel === $panel_id );
					?>
					<a
						href="#"
						class="recipe-sticky-tab<?php echo $is_active ? ' is-active' : ''; ?>"
						id="recipe-sticky-tab-<?php echo esc_attr( $panel_id ); ?>"
						role="tab"
						data-recipe-tab="<?php echo esc_attr( $panel_id ); ?>"
						aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
						aria-controls="recipe-sticky-panel-<?php echo esc_attr( $panel_id ); ?>"
						tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
					>
						<?php echo esc_html( $panel_label ); ?>
					</a>
					<?php if ( $panel_id !== $last_key ) : ?>
						<span class="recipe-sticky-tabs__sep" aria-hidden="true">·</span>
					<?php endif; ?>
				<?php endforeach; ?>
			</nav>
		</div>

		<button
			id="recipe-toggle"
			class="recipe-toggle aptox-btn--plain"
			type="button"
			aria-expanded="<?php echo '' !== $active_slug ? 'true' : 'false'; ?>"
			aria-controls="recipe-sticky-content"
			aria-label="<?php esc_attr_e( 'Abrir ou fechar filtros', 'aptox' ); ?>"
		>
			<span class="icon icon-open">⌵</span>
			<span class="icon icon-close">✕</span>
		</button>
	</div>

	<section id="recipe-sticky-content">
		<div
			id="recipe-sticky-panel-categories"
			class="recipe-sticky-panel<?php echo 'categories' === $initial_panel ? ' is-active' : ''; ?>"
			data-recipe-panel="categories"
			role="tabpanel"
			aria-labelledby="recipe-sticky-tab-categories"
			<?php echo 'categories' === $initial_panel ? '' : ' hidden'; ?>
		>
			<?php get_template_part( 'components/recipe-carousel/recipe-carousel' ); ?>
		</div>

		<div
			id="recipe-sticky-panel-meals"
			class="recipe-sticky-panel<?php echo 'meals' === $initial_panel ? ' is-active' : ''; ?>"
			data-recipe-panel="meals"
			role="tabpanel"
			aria-labelledby="recipe-sticky-tab-meals"
			<?php echo 'meals' === $initial_panel ? '' : ' hidden'; ?>
		>
			<?php
			get_template_part(
				'components/recipe-tag-filter/recipe-tag-filter',
				null,
				array(
					'group' => 'meals',
				)
			);
			?>
		</div>

		<div
			id="recipe-sticky-panel-culinary"
			class="recipe-sticky-panel<?php echo 'culinary' === $initial_panel ? ' is-active' : ''; ?>"
			data-recipe-panel="culinary"
			role="tabpanel"
			aria-labelledby="recipe-sticky-tab-culinary"
			<?php echo 'culinary' === $initial_panel ? '' : ' hidden'; ?>
		>
			<?php
			get_template_part(
				'components/recipe-tag-filter/recipe-tag-filter',
				null,
				array(
					'group' => 'culinary',
				)
			);
			?>
		</div>
	</section>
</div>
