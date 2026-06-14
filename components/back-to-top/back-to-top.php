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

$icon_url = get_template_directory_uri() . '/assets/icons/ui/ico-top.png';
$label    = __( 'Voltar ao topo', 'aptox' );
$arc_text = __( 'voltar ao TOPO', 'aptox' );
?>

<button
	type="button"
	class="back-to-top"
	aria-label="<?php echo esc_attr( $label ); ?>"
>
	<span class="back-to-top__arc" aria-hidden="true">
		<svg class="back-to-top__svg" viewBox="0 0 120 36" role="presentation">
			<defs>
				<path
					id="back-to-top-arc"
					d="M 20,30 A 40,40 0 0,1 100,30"
				/>
			</defs>
			<text class="back-to-top__text" text-anchor="middle">
				<textPath href="#back-to-top-arc" startOffset="50%">
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
