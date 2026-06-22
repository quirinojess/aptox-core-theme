<?php
/**
 * Component: Footer Ad (sticky widget area)
 *
 * @package Aptox
 */

if ( ! function_exists( 'aptox_show_footer_ad' ) || ! aptox_show_footer_ad() ) {
	return;
}

$slot_html = function_exists( 'aptox_get_footer_ad_slot_html' )
	? aptox_get_footer_ad_slot_html()
	: '';
?>

<aside
	id="footerAd"
	class="footer-ad"
	aria-label="<?php esc_attr_e( 'Publicidade', 'aptox' ); ?>"
	aria-hidden="true"
>
	<div class="footer-ad__inner">
		<p class="footer-ad__label"><?php esc_html_e( 'Publicidade', 'aptox' ); ?></p>

		<div class="footer-ad__slot">
			<?php echo $slot_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Widget output. ?>
		</div>

		<button
			class="close footer-ad__close"
			type="button"
			aria-label="<?php esc_attr_e( 'Fechar publicidade', 'aptox' ); ?>"
		>
			×
		</button>
	</div>
</aside>
