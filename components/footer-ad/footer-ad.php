<?php
/**
 * Component: Footer Ad (sticky widget area)
 *
 * Widget area "Rodapé publicidade" renders inside the sticky bar center slot.
 *
 * @package Aptox
 */

if ( ! function_exists( 'aptox_show_footer_ad' ) || ! aptox_show_footer_ad() ) {
	return;
}

$slot_html = function_exists( 'aptox_get_footer_ad_slot_html' )
	? aptox_get_footer_ad_slot_html()
	: '';

$has_widget = function_exists( 'aptox_footer_ad_slot_has_content' )
	? aptox_footer_ad_slot_has_content( $slot_html )
	: ( '' !== trim( $slot_html ) );

if ( ! $has_widget ) {
	return;
}
?>

<div
	id="aptoxStickyChrome"
	class="aptox-sticky-chrome is-visible"
	data-has-widget="true"
	role="region"
	aria-label="<?php esc_attr_e( 'Publicidade', 'aptox' ); ?>"
>
	<div class="aptox-sticky-chrome__inner">
		<p class="aptox-sticky-chrome__label"><?php esc_html_e( 'Publicidade', 'aptox' ); ?></p>

		<div id="aptoxStickySlot" class="aptox-sticky-chrome__slot">
			<?php echo $slot_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Widget output. ?>
		</div>

		<button
			class="close"
			type="button"
			aria-label="<?php esc_attr_e( 'Fechar publicidade', 'aptox' ); ?>"
		>
			×
		</button>
	</div>
</div>
<script>
document.documentElement.setAttribute('data-aptox-sticky', 'active');
document.documentElement.classList.add('has-aptox-sticky');
</script>
