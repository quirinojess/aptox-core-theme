<?php
/**
 * Component: Casa Organização carousel
 *
 * @context Archive Casas / Page Casa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cache_key   = 'aptox_casa_organizacao_v3';
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$house_taxonomy   = 'casa_categoria';
$organizacao_term = null;

foreach ( array( 'casa_categoria', 'casa' ) as $candidate_taxonomy ) {
	if ( ! taxonomy_exists( $candidate_taxonomy ) ) {
		continue;
	}

	$candidate_term = get_term_by( 'slug', 'organizacao', $candidate_taxonomy );

	if ( $candidate_term && ! is_wp_error( $candidate_term ) ) {
		$house_taxonomy   = $candidate_taxonomy;
		$organizacao_term = $candidate_term;
		break;
	}
}

if ( ! $organizacao_term ) {
	return;
}

$filter_tags = array(
	'rotinas' => __( 'Rotinas', 'aptox' ),
	'espacos' => __( 'Espaços', 'aptox' ),
);

$fetch_all_posts = static function () use ( $house_taxonomy ) {
	$query = new WP_Query(
		array(
			'post_type'              => 'casas',
			'posts_per_page'         => 8,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => true,
			'tax_query'              => array(
				array(
					'taxonomy' => $house_taxonomy,
					'field'    => 'slug',
					'terms'    => 'organizacao',
				),
			),
		)
	);

	$posts = array_values(
		array_filter(
			$query->posts,
			static function ( $post ) {
				return has_post_thumbnail( $post );
			}
		)
	);

	wp_reset_postdata();

	return $posts;
};

$fetch_posts = static function ( $tag_slug ) use ( $house_taxonomy ) {
	$query = new WP_Query(
		array(
			'post_type'              => 'casas',
			'posts_per_page'         => 8,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => true,
			'tax_query'              => array(
				'relation' => 'AND',
				array(
					'taxonomy' => $house_taxonomy,
					'field'    => 'slug',
					'terms'    => 'organizacao',
				),
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'slug',
					'terms'    => $tag_slug,
				),
			),
		)
	);

	$posts = array_values(
		array_filter(
			$query->posts,
			static function ( $post ) {
				return has_post_thumbnail( $post );
			}
		)
	);

	wp_reset_postdata();

	return $posts;
};

$posts_by_filter = array(
	'all' => $fetch_all_posts(),
);

foreach ( $filter_tags as $tag_slug => $label ) {
	$posts_by_filter[ $tag_slug ] = $fetch_posts( $tag_slug );
}

if ( empty( $posts_by_filter['all'] ) ) {
	return;
}

$default_filter = 'all';
$organize_icon  = get_template_directory_uri() . '/assets/icons/ui/icon-home-organize.png';

$render_post_card = static function ( $post ) {
	$post_id = $post->ID;
	?>
	<article class="archive-card">
		<a
			href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"
			class="archive-thumb"
			aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"
		>
			<figure class="archive-image">
				<img
					src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'large' ) ); ?>"
					alt=""
					loading="lazy"
				>
			</figure>
		</a>

		<h3 class="archive-title">
			<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
				<?php echo esc_html( get_the_title( $post_id ) ); ?>
			</a>
		</h3>
	</article>
	<?php
};

ob_start();
?>

<section
	class="casa-organizacao casa-organizacao--carousel"
	aria-labelledby="casa-organizacao-title"
	data-default-filter="<?php echo esc_attr( $default_filter ); ?>"
>
	<div class="container-lg">
		<div class="casa-organizacao-top">
			<header class="casa-organizacao-header">
				<div class="casa-organizacao-header-inner">
					<figure class="casa-organizacao-icon" aria-hidden="true">
						<img
							src="<?php echo esc_url( $organize_icon ); ?>"
							alt=""
							loading="lazy"
						>
					</figure>

					<h2 id="casa-organizacao-title" class="casa-organizacao-title">
						<span class="casa-organizacao-title-hand">
							<?php esc_html_e( 'organize', 'aptox' ); ?>
						</span>
						<span class="casa-organizacao-title-display">
							<?php esc_html_e( 'sua casa', 'aptox' ); ?>
						</span>
					</h2>
				</div>
			</header>

			<div class="casa-organizacao-filter" role="tablist" aria-label="<?php esc_attr_e( 'Filtrar organização', 'aptox' ); ?>">
				<span class="casa-organizacao-filter-label">
					<?php esc_html_e( 'filtre por', 'aptox' ); ?>
				</span>

				<div class="casa-organizacao-tags">
					<?php foreach ( $filter_tags as $tag_slug => $label ) : ?>
						<?php if ( empty( $posts_by_filter[ $tag_slug ] ) ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<button
							type="button"
							class="casa-organizacao-tag"
							role="tab"
							aria-selected="false"
							data-filter-target="<?php echo esc_attr( $tag_slug ); ?>"
						>
							<?php echo esc_html( $label ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="casa-organizacao-carousel__controls">
				<button
					type="button"
					class="grid-recipe-nav grid-recipe-nav--prev"
					aria-label="<?php echo esc_attr__( 'Ver posts anteriores', 'aptox' ); ?>"
					disabled
					hidden
				>
					<span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>
				</button>

				<button
					type="button"
					class="grid-recipe-nav grid-recipe-nav--next"
					aria-label="<?php echo esc_attr__( 'Ver próximos posts', 'aptox' ); ?>"
					hidden
				>
					<span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
				</button>
			</div>
		</div>

		<div class="casa-organizacao-carousel">
			<div
				class="grid-recipe-track casa-organizacao-panel"
				data-filter-panel="all"
				role="tabpanel"
			>
				<?php foreach ( $posts_by_filter['all'] as $post ) : ?>
					<?php $render_post_card( $post ); ?>
				<?php endforeach; ?>
			</div>

			<?php foreach ( $filter_tags as $tag_slug => $label ) : ?>
				<?php if ( empty( $posts_by_filter[ $tag_slug ] ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<div
					class="grid-recipe-track casa-organizacao-panel"
					data-filter-panel="<?php echo esc_attr( $tag_slug ); ?>"
					role="tabpanel"
					hidden
				>
					<?php foreach ( $posts_by_filter[ $tag_slug ] as $post ) : ?>
						<?php $render_post_card( $post ); ?>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
$html = ob_get_clean();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
