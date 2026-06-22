<?php
/**
 * Component: Links landing page (Linktree-style)
 *
 * @context Page template Links
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$season       = function_exists( 'aptox_get_season_context' ) ? aptox_get_season_context() : array();
$season_slug  = ! empty( $season['slug'] ) ? sanitize_title( $season['slug'] ) : 'verao';
$season_label = ! empty( $season['label'] ) ? $season['label'] : (
	function_exists( 'aptox_get_season_label' ) ? aptox_get_season_label( $season_slug ) : 'Verão'
);
$season_icon = function_exists( 'aptox_filter_home_season_icon' )
	? aptox_filter_home_season_icon( $season_slug )
	: get_template_directory_uri() . '/assets/icons/casa/sazonal/casa-sazonal-verao.png';
$editorial_url = function_exists( 'aptox_get_editorial_url' )
	? aptox_get_editorial_url()
	: home_url( '/editorial/' );
$latest_youtube_video = function_exists( 'aptox_page_links_get_latest_youtube_video' )
	? aptox_page_links_get_latest_youtube_video()
	: null;
$youtube_video_id     = is_array( $latest_youtube_video ) && ! empty( $latest_youtube_video['id'] )
	? sanitize_key( (string) $latest_youtube_video['id'] )
	: 'none';

$cache_key   = 'aptox_page_links_v10_' . sanitize_key( $season_slug ) . '_' . $youtube_video_id;
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$banner_image = function_exists( 'aptox_theme_image_uri' ) ? aptox_theme_image_uri( 'index-cta' ) : '';
$banner_meta  = function_exists( 'aptox_theme_image_meta' ) ? aptox_theme_image_meta( 'index-cta' ) : array(
	'width'  => 768,
	'height' => 512,
);

$logo_uri = get_template_directory_uri() . '/assets/icons/ui/brand/ui-brand-logo.svg';

$sections = array(
	array(
		'label'     => 'CASA',
		'post_type' => 'casas',
	),
	array(
		'label'     => 'RECEITAS',
		'post_type' => 'receitas',
	),
	array(
		'label'     => 'CELEBRE',
		'post_type' => 'celebracoes',
	),
);

$section_posts = array();

foreach ( $sections as $section ) {
	if ( ! post_type_exists( $section['post_type'] ) ) {
		$section_posts[] = array();
		continue;
	}

	if ( 'casas' === $section['post_type'] ) {
		$section_posts[] = function_exists( 'aptox_page_links_get_casa_posts' )
			? aptox_page_links_get_casa_posts( $season_slug )
			: array();
		continue;
	}

	if ( 'receitas' === $section['post_type'] ) {
		$section_posts[] = function_exists( 'aptox_page_links_get_posts_by_season_tag' )
			? aptox_page_links_get_posts_by_season_tag( 'receitas', $season_slug, 'receitas-de-', 3 )
			: array();
		continue;
	}

	if ( 'celebracoes' === $section['post_type'] ) {
		$section_posts[] = function_exists( 'aptox_page_links_get_posts_by_season_tag' )
			? aptox_page_links_get_posts_by_season_tag( 'celebracoes', $season_slug, 'celebrando-no-', 3 )
			: array();
		continue;
	}

	$section_posts[] = array();
}

$social_links = function_exists( 'aptox_get_social_links' ) ? aptox_get_social_links() : array();
$icon_base    = get_template_directory_uri() . '/assets/icons/ui/social/';

ob_start();
?>

<article class="page-links" aria-labelledby="page-links-title">
	<?php if ( $banner_image ) : ?>
		<div class="page-links__banner">
			<img
				class="page-links__banner-image"
				src="<?php echo esc_url( $banner_image ); ?>"
				alt=""
				width="<?php echo esc_attr( (string) $banner_meta['width'] ); ?>"
				height="<?php echo esc_attr( (string) $banner_meta['height'] ); ?>"
				fetchpriority="high"
				decoding="async"
			>
		</div>
	<?php endif; ?>

	<div class="page-links__inner">
		<header class="page-links__header">
			<figure class="page-links__avatar-wrap">
				<img
					class="page-links__avatar"
					src="<?php echo esc_url( $logo_uri ); ?>"
					alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
					width="96"
					height="96"
					decoding="async"
				>
			</figure>

			<h1 id="page-links-title" class="page-links__title">
				<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
			</h1>

			<p class="page-links__tagline">
				<?php esc_html_e( 'Casa · Receitas · Celebrações', 'aptox' ); ?>
			</p>
		</header>

		<?php if ( is_array( $latest_youtube_video ) && ! empty( $latest_youtube_video['url'] ) ) : ?>
			<section class="page-links-youtube" aria-labelledby="page-links-youtube-title">
				<h2 id="page-links-youtube-title" class="page-links-youtube__title">
					<?php
					echo esc_html(
						function_exists( 'aptox_hand_text' )
							? aptox_hand_text( __( 'Assista nosso último vídeo', 'aptox' ), false )
							: __( 'Assista nosso último vídeo', 'aptox' )
					);
					?>
				</h2>

				<article class="page-links-youtube__video">
					<a
						href="<?php echo esc_url( $latest_youtube_video['url'] ); ?>"
						class="youtube-feed-thumb page-links-youtube__thumb"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="<?php echo esc_attr( $latest_youtube_video['title'] ?? __( 'Assistir no YouTube', 'aptox' ) ); ?>"
					>
						<?php if ( ! empty( $latest_youtube_video['thumbnail'] ) ) : ?>
							<figure class="youtube-feed-image">
								<img
									src="<?php echo esc_url( $latest_youtube_video['thumbnail'] ); ?>"
									alt=""
									loading="lazy"
									decoding="async"
								>
							</figure>
						<?php endif; ?>

						<span class="youtube-feed-play" aria-hidden="true"></span>
					</a>
				</article>
			</section>
		<?php endif; ?>

		<a
			class="page-links-editorial"
			href="<?php echo esc_url( $editorial_url ); ?>"
		>
			<img
				class="page-links-editorial__icon"
				src="<?php echo esc_url( $season_icon ); ?>"
				alt=""
				width="36"
				height="36"
				decoding="async"
				aria-hidden="true"
			>
			<span class="page-links-editorial__text">
				<?php
				printf(
					/* translators: %s: season name */
					esc_html__( 'Leia nosso editorial de %s', 'aptox' ),
					esc_html( $season_label )
				);
				?>
			</span>
		</a>

		<div class="page-links__sections">
			<?php foreach ( $sections as $index => $section ) : ?>
				<?php
				$posts = $section_posts[ $index ] ?? array();

				if ( empty( $posts ) ) {
					continue;
				}
				?>
				<section class="page-links__section" aria-labelledby="page-links-section-<?php echo esc_attr( (string) $index ); ?>">
					<h2 id="page-links-section-<?php echo esc_attr( (string) $index ); ?>" class="page-links__section-label">
						<?php echo esc_html( $section['label'] ); ?>
					</h2>

					<nav class="page-links-list" aria-label="<?php echo esc_attr( $section['label'] ); ?>">
						<?php foreach ( $posts as $post ) : ?>
							<a class="page-links-list__link" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
								<?php echo esc_html( get_the_title( $post ) ); ?>
							</a>
						<?php endforeach; ?>
					</nav>
				</section>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $social_links ) ) : ?>
			<nav class="page-links__social" aria-label="<?php esc_attr_e( 'Redes sociais', 'aptox' ); ?>">
				<ul class="page-links__social-list">
					<?php foreach ( $social_links as $social_link ) : ?>
						<li>
							<a
								href="<?php echo esc_url( $social_link['url'] ); ?>"
								target="_blank"
								rel="noopener noreferrer"
								aria-label="<?php echo esc_attr( $social_link['label'] ); ?>"
							>
								<img
									src="<?php echo esc_url( $icon_base . $social_link['icon'] ); ?>"
									alt=""
									width="18"
									height="18"
									decoding="async"
									aria-hidden="true"
								>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
	</div>
</article>

<?php
$html = ob_get_clean();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
