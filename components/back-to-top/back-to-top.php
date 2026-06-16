<?php
/**
 * Component: Back to Top
 * Theme: Aptox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_singular() ) {
	return;
}

$is_recipe = 'receitas' === get_post_type();
$icon_url  = $is_recipe
	? get_template_directory_uri() . '/assets/img/ico-recipe.png'
	: get_template_directory_uri() . '/assets/icons/ui/actions/ui-action-topo.png';
$label     = $is_recipe
	? __( 'Ir para a receita', 'aptox' )
	: __( 'Voltar ao topo', 'aptox' );
$arc_text  = $is_recipe
	? __( 'ir para a RECEITA', 'aptox' )
	: __( 'voltar ao TOPO', 'aptox' );
$circle_text_length = $is_recipe ? 124 : 108;
$classes   = $is_recipe ? 'back-to-top back-to-top--recipe' : 'back-to-top';
?>

<button
	type="button"
	class="<?php echo esc_attr( $classes ); ?>"
	aria-label="<?php echo esc_attr( $label ); ?>"
>
	<span class="back-to-top__arc" aria-hidden="true">
		<svg class="back-to-top__svg back-to-top__svg--arc" viewBox="0 0 120 36" role="presentation">
			<defs>
				<path
					id="back-to-top-arc"
					d="M 20,30 A 40,40 0 0,1 100,30"
				/>
			</defs>
			<text class="back-to-top__text" text-anchor="start" textLength="126" lengthAdjust="spacing">
				<textPath class="back-to-top__text-path" href="#back-to-top-arc" startOffset="0%">
					<?php echo esc_html( $arc_text ); ?>
				</textPath>
			</text>
		</svg>

		<svg class="back-to-top__svg back-to-top__svg--circle" viewBox="0 0 60 60" role="presentation">
			<defs>
				<path
					id="back-to-top-circle"
					d="M 30,7 A 23,23 0 1,1 29.99,7"
				/>
			</defs>
			<text
				class="back-to-top__text back-to-top__text--circle"
				text-anchor="start"
				textLength="<?php echo esc_attr( (string) $circle_text_length ); ?>"
				lengthAdjust="spacing"
			>
				<textPath
					class="back-to-top__text-path back-to-top__text-path--circle"
					href="#back-to-top-circle"
					startOffset="0%"
				>
					<?php echo esc_html( $arc_text ); ?>
				</textPath>
			</text>
		</svg>
	</span>

	<span class="back-to-top__icon-wrap">
		<img
			class="back-to-top__icon"
			src="<?php echo esc_url( $icon_url ); ?>"
			alt=""
			width="42"
			height="42"
			decoding="async"
		>
	</span>
</button>
