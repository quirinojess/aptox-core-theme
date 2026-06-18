<?php
/**
 * Component: YouTube Feed
 *
 * @Context Index
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$featured_url = class_exists( '\Aptox\Widgets\YouTubeFeaturedWidget' )
	? \Aptox\Widgets\YouTubeFeaturedWidget::get_configured_video_url()
	: '';
$cache_key    = 'aptox_youtube_feed_v10_' . md5( $featured_url );
$cached_html  = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$feed           = aptox_get_youtube_home_feed();
$featured_video = ! empty( $feed['featured_video'] ) ? $feed['featured_video'] : null;
$long_videos    = ! empty( $feed['long_videos'] ) ? $feed['long_videos'] : array();

if ( ! $featured_video && empty( $long_videos ) ) {
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
					src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/social/ui-social-youtube-section.png' ); ?>"
					alt=""
					loading="lazy"
				>
			</figure>

			<h2 id="youtube-feed-title" class="youtube-feed-title">
				<?php esc_html_e( 'Conheça nosso canal no', 'aptox' ); ?>
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
		<?php if ( $featured_video ) : ?>
			<article class="youtube-feed-featured">
				<a
					href="<?php echo esc_url( $featured_video['url'] ); ?>"
					class="youtube-feed-thumb"
					target="_blank"
					rel="noopener noreferrer"
					aria-label="<?php echo esc_attr( $featured_video['title'] ); ?>"
				>
					<figure class="youtube-feed-image">
						<img
							src="<?php echo esc_url( $featured_video['thumbnail'] ); ?>"
							alt=""
							loading="lazy"
						>
					</figure>

					<span class="youtube-feed-play" aria-hidden="true"></span>
				</a>

				<h3 class="youtube-feed-featured-title">
					<a
						href="<?php echo esc_url( $featured_video['url'] ); ?>"
						target="_blank"
						rel="noopener noreferrer"
					>
						<?php echo esc_html( $featured_video['title'] ); ?>
					</a>
				</h3>
			</article>
		<?php endif; ?>

		<?php if ( ! empty( $long_videos ) ) : ?>
			<aside
				class="youtube-feed-recent"
				aria-label="<?php esc_attr_e( 'Vídeos recentes', 'aptox' ); ?>"
			>
				<ul class="youtube-feed-recent-list">
					<?php foreach ( $long_videos as $video ) : ?>
						<li class="youtube-feed-recent-item">
							<a
								href="<?php echo esc_url( $video['url'] ); ?>"
								target="_blank"
								rel="noopener noreferrer"
							>
								<?php echo esc_html( $video['title'] ); ?>
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
