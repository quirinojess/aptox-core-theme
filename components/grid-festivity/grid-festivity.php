<?php
/**
 * Component: Season Festivities Grid
 *
 * @Context Index
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$season = aptox_get_season_context();

$season_slug  = '';
$season_label = '';

if ( is_array( $season ) && ! empty( $season['slug'] ) ) {
	$season_slug  = sanitize_title( $season['slug'] );
	$season_label = ! empty( $season['label'] ) ? $season['label'] : aptox_get_season_label( $season_slug );
}

$cache_key   = 'aptox_grid_festivity_v7_' . sanitize_key( $season_slug );
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$festivities = aptox_get_season_festivities( $season_slug );

if ( empty( $festivities ) ) {
	return;
}

$use_carousel = count( $festivities ) >= 3;

$render_festivity_card = static function ( array $festivity ) {
	?>
	<article class="archive-card">
		<a
			href="<?php echo esc_url( $festivity['url'] ); ?>"
			class="archive-thumb"
			aria-label="<?php echo esc_attr( $festivity['label'] ); ?>"
		>
			<figure class="archive-image">
				<img
					src="<?php echo esc_url( $festivity['image'] ); ?>"
					alt=""
					loading="lazy"
				>
			</figure>
		</a>

		<h3 class="archive-title">
			<a href="<?php echo esc_url( $festivity['url'] ); ?>">
				<?php echo esc_html( $festivity['label'] ); ?>
			</a>
		</h3>
	</article>
	<?php
};

$render_festivity_title = static function () use ( $season_slug, $season_label ) {
	?>
	<div class="grid-festivity-title">
		<div class="grid-festivity-title-inner">
			<figure class="grid-festivity-icon" aria-hidden="true">
				<img
					src="<?php echo esc_url( aptox_party_season_icon( $season_slug ) ); ?>"
					alt=""
					loading="lazy"
				>
			</figure>

			<h2 id="grid-festivity-title" class="grid-festivity-heading">
				<?php esc_html_e( 'Festividades de', 'aptox' ); ?>
				<span class="grid-festivity-season">
					<?php echo esc_html( aptox_hand_text( $season_label ) ); ?>
				</span>
			</h2>
		</div>
	</div>
	<?php
};

ob_start();
?>

<section
	class="grid-festivity<?php echo $use_carousel ? ' grid-festivity--carousel' : ''; ?>"
	aria-labelledby="grid-festivity-title"
>
	<div class="container-lg">
		<?php if ( $use_carousel ) : ?>
			<div class="grid-festivity-layout grid-festivity-layout--carousel">
				<?php $render_festivity_title(); ?>

				<div class="grid-festivity-carousel">
					<div class="grid-festivity-carousel__controls">
						<button
							type="button"
							class="grid-festivity-nav grid-festivity-nav--prev"
							aria-label="<?php echo esc_attr__( 'Ver festividades anteriores', 'aptox' ); ?>"
							disabled
							hidden
						>
							<span class="grid-festivity-nav__icon"><?php echo aptox_chevron_icon( 'left' ); ?></span>
						</button>

						<button
							type="button"
							class="grid-festivity-nav grid-festivity-nav--next"
							aria-label="<?php echo esc_attr__( 'Ver próximas festividades', 'aptox' ); ?>"
							hidden
						>
							<span class="grid-festivity-nav__icon"><?php echo aptox_chevron_icon( 'right' ); ?></span>
						</button>
					</div>

					<div class="grid-festivity-track">
						<?php foreach ( $festivities as $festivity ) : ?>
							<?php $render_festivity_card( $festivity ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php else : ?>
			<div class="archive-grid grid-festivity-grid">
				<?php $render_festivity_title(); ?>

				<?php foreach ( $festivities as $festivity ) : ?>
					<?php $render_festivity_card( $festivity ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
$html = ob_get_clean();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
