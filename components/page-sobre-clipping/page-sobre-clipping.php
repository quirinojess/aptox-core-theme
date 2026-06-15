<?php
/**
 * Component: Page Sobre clipping logos marquee
 *
 * @context Page Sobre
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$logo_dir = get_template_directory() . '/assets/img/logo-clipping';
$logo_uri = get_template_directory_uri() . '/assets/img/logo-clipping';
$logos    = array();

if ( is_dir( $logo_dir ) ) {
	$files = scandir( $logo_dir );

	if ( is_array( $files ) ) {
		sort( $files );

		foreach ( $files as $file ) {
			if ( ! preg_match( '/\.(png|svg|jpe?g|webp)$/i', $file ) ) {
				continue;
			}

			$name = pathinfo( $file, PATHINFO_FILENAME );
			$name = str_replace( array( '-', '_' ), ' ', $name );

			$logos[] = array(
				'src'  => $logo_uri . '/' . $file,
				'alt'  => ucwords( $name ),
				'slug' => sanitize_title( $name ),
			);
		}
	}
}

if ( empty( $logos ) ) {
	return;
}

$render_track = static function ( array $logos_list, bool $hidden = false ) {
	$hidden_attr = $hidden ? ' aria-hidden="true"' : '';

	echo '<div class="page-sobre-clipping__track"' . $hidden_attr . '>';

	foreach ( $logos_list as $logo ) {
		printf(
			'<img class="page-sobre-clipping__logo page-sobre-clipping__logo--%1$s" src="%2$s" alt="%3$s" loading="lazy" decoding="async" width="160" height="48">',
			esc_attr( $logo['slug'] ),
			esc_url( $logo['src'] ),
			esc_attr( $logo['alt'] )
		);
	}

	echo '</div>';
};
?>

<section
	class="page-sobre-clipping"
	aria-label="<?php esc_attr_e( 'Onde o blog já foi destaque', 'aptox' ); ?>"
>
	<div class="page-sobre-clipping__heading">
		<span class="page-sobre-clipping__title-main">
			<?php esc_html_e( 'Onde o blog', 'aptox' ); ?>
		</span>
		<span class="page-sobre-clipping__title-sub">
			<?php esc_html_e( 'já foi destaque', 'aptox' ); ?>
		</span>
	</div>

	<div class="page-sobre-clipping__viewport">
		<div class="page-sobre-clipping__inner">
			<?php
			$render_track( $logos );
			$render_track( $logos, true );
			?>
		</div>
	</div>
</section>
