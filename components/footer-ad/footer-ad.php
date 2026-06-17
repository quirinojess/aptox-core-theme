<?php
/**
 * Component: Footer Ad (sticky widget area)
 *
 * @package Aptox
 */

if ( ! function_exists( 'aptox_show_footer_ad' ) || ! aptox_show_footer_ad() ) {
	return;
}
?>

<aside
	id="footerAd"
	class="footer-ad"
	aria-label="<?php esc_attr_e( 'Publicidade', 'aptox' ); ?>"
	aria-hidden="false"
>
	<div class="footer-ad__inner">
		<p class="footer-ad__label"><?php esc_html_e( 'Publicidade', 'aptox' ); ?></p>

		<div class="footer-ad__slot">
			<?php dynamic_sidebar( 'footer-ad-sidebar' ); ?>
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

<script>
(function () {
	var storageKey = 'aptox-footer-ad-dismissed';
	var footerAd = document.getElementById('footerAd');
	var navEntry = performance.getEntriesByType('navigation')[0];
	var isReload = navEntry && navEntry.type === 'reload';
	var isDismissed = false;

	try {
		isDismissed = sessionStorage.getItem(storageKey) === 'true';
	} catch (error) {
		/* Ignore storage errors. */
	}

	if (isReload) {
		try {
			sessionStorage.removeItem(storageKey);
		} catch (error) {
			/* Ignore storage errors. */
		}

		isDismissed = false;
	}

	if (!footerAd) {
		return;
	}

	if (isDismissed) {
		footerAd.classList.add('is-hidden');
		footerAd.setAttribute('aria-hidden', 'true');
		document.documentElement.classList.add('footer-ad-dismissed');
		document.documentElement.removeAttribute('data-footer-ad');
		document.documentElement.classList.remove('has-footer-ad');
		return;
	}

	footerAd.classList.add('is-visible');
	footerAd.setAttribute('aria-hidden', 'false');
	document.documentElement.classList.remove('footer-ad-dismissed');
	document.documentElement.setAttribute('data-footer-ad', 'active');
	document.documentElement.classList.add('has-footer-ad');
})();
</script>
