<?php
/**
 * Component: Page Editorial seasonal celebration post
 *
 * @context Page Editorial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$season_slug = isset( $args['season_slug'] ) ? sanitize_title( (string) $args['season_slug'] ) : '';

if ( '' === $season_slug ) {
	$season = aptox_get_season_context();

	if ( is_array( $season ) && ! empty( $season['slug'] ) ) {
		$season_slug = sanitize_title( $season['slug'] );
	}
}

$celebration_post = isset( $args['post'] ) && $args['post'] instanceof WP_Post
	? $args['post']
	: aptox_get_season_celebration_post( $season_slug );

if ( ! $celebration_post instanceof WP_Post ) {
	return;
}

$post_content = function_exists( 'aptox_render_celebration_post_content' )
	? aptox_render_celebration_post_content( $celebration_post )
	: apply_filters( 'the_content', $celebration_post->post_content );

if ( '' === trim( wp_strip_all_tags( $post_content ) ) ) {
	return;
}

$season_label = aptox_get_season_label( $season_slug );
$signoff_meta = sprintf(
	'%s/%s',
	$season_label,
	wp_date( 'Y' )
);

$marquee_text = $season_label;
$marquee_label = sprintf(
	/* translators: %s: season name */
	__( 'Estação: %s', 'aptox' ),
	$season_label
);

$render_marquee_track = static function ( $text, $hidden = false ) {
	$hidden_attr = $hidden ? ' aria-hidden="true"' : '';

	echo '<div class="page-editorial-inspiracoes-marquee__track"' . $hidden_attr . '>';

	for ( $repeat = 0; $repeat < 32; $repeat++ ) {
		echo '<span class="page-editorial-inspiracoes-marquee__text">';
		echo esc_html( $text );
		echo '</span>';
		echo '<span class="page-editorial-inspiracoes-marquee__dot" aria-hidden="true"></span>';
	}

	echo '</div>';
};
?>

<div class="page-editorial-inspiracoes-marquee" aria-label="<?php echo esc_attr( $marquee_label ); ?>">
	<div class="page-editorial-inspiracoes-marquee__viewport">
		<div class="page-editorial-inspiracoes-marquee__inner">
			<?php
			$render_marquee_track( $marquee_text );
			$render_marquee_track( $marquee_text, true );
			?>
		</div>
	</div>
</div>

<section
	class="page-editorial-section page-editorial-section--light page-editorial-section--closing page-editorial-celebre"
	aria-labelledby="page-editorial-celebre-title"
>
	<div class="page-editorial-inner">
		<article
			id="post-<?php echo esc_attr( (string) $celebration_post->ID ); ?>"
			<?php post_class( 'page-editorial-celebre__article', $celebration_post ); ?>
		>
			<p class="page-editorial-celebre__intro">
				<?php esc_html_e( 'E para esse ano, veja o que preparamos para você:', 'aptox' ); ?>
			</p>

			<h2 id="page-editorial-celebre-title" class="page-editorial-celebre__title">
				<?php echo esc_html( get_the_title( $celebration_post ) ); ?>
			</h2>

			<div id="content" class="page-editorial-celebre__content">
				<?php echo $post_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<footer class="page-editorial-celebre__signoff">
				<p class="page-editorial-celebre__signoff-hand">
					<?php esc_html_e( 'Com amor, Jess', 'aptox' ); ?>
				</p>
				<p class="page-editorial-celebre__signoff-meta">
					<?php echo esc_html( $signoff_meta ); ?>
				</p>
			</footer>
		</article>
	</div>
</section>
