<?php
/**
 * Component: YouTube Feed
 *
 * @Context Index
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cache_key   = 'aptox_youtube_feed_v9';
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$feed       = aptox_get_youtube_home_feed();
$long_video = ! empty( $feed['long_video'] ) ? $feed['long_video'] : null;
$shorts     = ! empty( $feed['shorts'] ) ? $feed['shorts'] : array();

if ( ! $long_video && empty( $shorts ) ) {
	return;
}

ob_start();
?>

<section
	class="youtube-feed"
	aria-labelledby="youtube-feed-title"
>
	<header class="youtube-feed-header">
		<div class="youtube-feed-header-inner">
			<figure class="youtube-feed-icon" aria-hidden="true">
				<img
					src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/youtube-section.png' ); ?>"
					alt=""
					loading="lazy"
				>
			</figure>

			<h2 id="youtube-feed-title" class="youtube-feed-title">
				<?php esc_html_e( 'conheça nosso canal no', 'aptox' ); ?>
				<a
					class="youtube-feed-brand"
					href="<?php echo esc_url( aptox_get_youtube_channel_url() ); ?>"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php esc_html_e( 'YouTube', 'aptox' ); ?>
				</a>
			</h2>
		</div>
	</header>

	<div class="youtube-feed-layout">
		<?php if ( $long_video ) : ?>
			<article class="youtube-feed-featured">
				<a
					href="<?php echo esc_url( $long_video['url'] ); ?>"
					class="youtube-feed-thumb"
					target="_blank"
					rel="noopener noreferrer"
					aria-label="<?php echo esc_attr( $long_video['title'] ); ?>"
				>
					<figure class="youtube-feed-image">
						<img
							src="<?php echo esc_url( $long_video['thumbnail'] ); ?>"
							alt=""
							loading="lazy"
						>
					</figure>

					<span class="youtube-feed-play" aria-hidden="true"></span>
				</a>

				<h3 class="youtube-feed-featured-title">
					<a
						href="<?php echo esc_url( $long_video['url'] ); ?>"
						target="_blank"
						rel="noopener noreferrer"
					>
						<?php echo esc_html( $long_video['title'] ); ?>
					</a>
				</h3>
			</article>
		<?php endif; ?>

		<?php if ( ! empty( $shorts ) ) : ?>
			<aside
				class="youtube-feed-shorts"
				aria-label="<?php esc_attr_e( 'Vídeos recentes', 'aptox' ); ?>"
			>
				<ul class="youtube-feed-shorts-list">
					<?php foreach ( $shorts as $short ) : ?>
						<li class="youtube-feed-shorts-item">
							<a
								href="<?php echo esc_url( $short['url'] ); ?>"
								target="_blank"
								rel="noopener noreferrer"
							>
								<?php echo esc_html( $short['title'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</aside>
		<?php endif; ?>
	</div>
</section>

<?php
$html = ob_get_clean();

set_transient( $cache_key, $html, 6 * HOUR_IN_SECONDS );

echo $html;
