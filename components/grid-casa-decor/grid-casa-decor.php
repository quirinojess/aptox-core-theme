<?php
/**
 * Component: Seasonal decoration carousel (Casa archive/page)
 *
 * @context Archive Casas / Page Casa
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

if ( empty( $season_slug ) ) {
	return;
}

$tag_slug = 'decoracao-de-' . $season_slug;

$cache_key   = 'aptox_grid_casa_decor_v1_' . md5( $season_slug );
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$resolved_house_taxonomy = 'casa_categoria';

foreach ( array( 'casa_categoria', 'casa' ) as $candidate_taxonomy ) {
	if ( ! taxonomy_exists( $candidate_taxonomy ) ) {
		continue;
	}

	$candidate_term = get_term_by( 'slug', 'decoracao', $candidate_taxonomy );
	if ( $candidate_term && ! is_wp_error( $candidate_term ) ) {
		$resolved_house_taxonomy = $candidate_taxonomy;
		break;
	}
}

$query = new WP_Query(
	array(
		'post_type'              => 'casas',
		'posts_per_page'         => 8,
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'tax_query'              => array(
			'relation' => 'AND',
			array(
				'taxonomy' => $resolved_house_taxonomy,
				'field'    => 'slug',
				'terms'    => 'decoracao',
			),
			array(
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => $tag_slug,
			),
		),
		'orderby'                => 'rand',
	)
);

if ( ! $query->have_posts() ) {
	return;
}

ob_start();
?>

<section class="grid-casa-decor" aria-labelledby="grid-casa-decor-title">
	<div class="grid-recipe-home">
		<div class="grid-recipe-top">
			<header class="grid-recipe-header">
				<div class="grid-recipe-header-inner">
					<figure class="grid-recipe-icon" aria-hidden="true">
						<img
							src="<?php echo esc_url( aptox_decor_season_icon( $season_slug ) ); ?>"
							alt=""
							loading="eager"
						>
					</figure>

					<h2 id="grid-casa-decor-title" class="grid-recipe-title">
						Decore sua casa para
						<span class="grid-recipe-season">
							<?php echo esc_html( $season_label ); ?>
						</span>
					</h2>
				</div>
			</header>

			<div class="grid-recipe-carousel__controls">
				<button
					type="button"
					class="grid-recipe-nav grid-recipe-nav--prev"
					aria-label="<?php echo esc_attr__( 'Ver decorações anteriores', 'aptox' ); ?>"
					disabled
					hidden
				>
					<span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>
				</button>

				<button
					type="button"
					class="grid-recipe-nav grid-recipe-nav--next"
					aria-label="<?php echo esc_attr__( 'Ver próximas decorações', 'aptox' ); ?>"
					hidden
				>
					<span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
				</button>
			</div>
		</div>

		<div class="grid-recipe-carousel">
			<div class="grid-recipe-track">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					?>
					<article <?php post_class( 'archive-card' ); ?>>
						<a
							href="<?php the_permalink(); ?>"
							class="archive-thumb"
							aria-hidden="true"
							tabindex="-1"
						>
							<?php if ( has_post_thumbnail() ) : ?>
								<figure class="archive-image">
									<?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?>
								</figure>
							<?php endif; ?>
						</a>

						<h3 class="archive-title">
							<a href="<?php the_permalink(); ?>">
								<?php the_title(); ?>
							</a>
						</h3>
					</article>
				<?php endwhile; ?>
			</div>
		</div>
	</div>
</section>

<?php
$html = ob_get_clean();

wp_reset_postdata();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
