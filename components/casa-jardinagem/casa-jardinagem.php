<?php
/**
 * Component: Casa Jardinagem grid
 *
 * @context Archive Casas / Page Casa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cache_key   = 'aptox_casa_jardinagem_v1';
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$house_taxonomy = 'casa_categoria';

foreach ( array( 'casa_categoria', 'casa' ) as $candidate_taxonomy ) {
	if ( ! taxonomy_exists( $candidate_taxonomy ) ) {
		continue;
	}

	$garden_term = get_term_by( 'slug', 'jardinagem', $candidate_taxonomy );

	if ( $garden_term && ! is_wp_error( $garden_term ) ) {
		$house_taxonomy = $candidate_taxonomy;
		break;
	}
}

$query = new WP_Query(
	array(
		'post_type'              => 'casas',
		'posts_per_page'         => 12,
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => true,
		'tax_query'              => array(
			array(
				'taxonomy' => $house_taxonomy,
				'field'    => 'slug',
				'terms'    => 'jardinagem',
			),
		),
	)
);

if ( ! $query->have_posts() ) {
	return;
}

$posts = array_values(
	array_filter(
		$query->posts,
		static function ( $post ) {
			return has_post_thumbnail( $post );
		}
	)
);

wp_reset_postdata();

if ( empty( $posts ) ) {
	return;
}

$garden_icon   = get_template_directory_uri() . '/assets/icons/ui/icon-decor-garden.png';
$use_carousel  = count( $posts ) > 3;

$render_post_card = static function ( $post ) {
	$post_id = $post->ID;
	$url     = get_permalink( $post_id );
	$image   = get_the_post_thumbnail_url( $post_id, 'large' );

	if ( ! $image ) {
		return;
	}
	?>
	<article class="archive-card">
		<a
			href="<?php echo esc_url( $url ); ?>"
			class="archive-thumb"
			aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"
		>
			<figure class="archive-image">
				<img
					src="<?php echo esc_url( $image ); ?>"
					alt=""
					loading="lazy"
				>
			</figure>
		</a>

		<h3 class="archive-title">
			<a href="<?php echo esc_url( $url ); ?>">
				<?php echo esc_html( get_the_title( $post_id ) ); ?>
			</a>
		</h3>
	</article>
	<?php
};

$render_section_title = static function () use ( $garden_icon ) {
	?>
	<div class="grid-festivity-title">
		<div class="grid-festivity-title-inner">
			<figure class="grid-festivity-icon" aria-hidden="true">
				<img
					src="<?php echo esc_url( $garden_icon ); ?>"
					alt=""
					loading="lazy"
				>
			</figure>

			<h2 id="grid-casa-jardinagem-title" class="grid-festivity-heading">
				<?php esc_html_e( 'dicas de', 'aptox' ); ?>
				<span class="grid-festivity-season">
					<?php esc_html_e( 'jardinagem', 'aptox' ); ?>
				</span>
			</h2>
		</div>
	</div>
	<?php
};

ob_start();
?>

<section
	class="grid-festivity grid-casa-jardinagem<?php echo $use_carousel ? ' grid-festivity--carousel' : ''; ?>"
	aria-labelledby="grid-casa-jardinagem-title"
>
	<div class="container-lg">
		<?php if ( $use_carousel ) : ?>
			<div class="grid-festivity-layout grid-festivity-layout--carousel">
				<?php $render_section_title(); ?>

				<div class="grid-festivity-carousel">
					<div class="grid-festivity-carousel__controls">
						<button
							type="button"
							class="grid-festivity-nav grid-festivity-nav--prev"
							aria-label="<?php echo esc_attr__( 'Ver posts anteriores', 'aptox' ); ?>"
							disabled
							hidden
						>
							<span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>
						</button>

						<button
							type="button"
							class="grid-festivity-nav grid-festivity-nav--next"
							aria-label="<?php echo esc_attr__( 'Ver próximos posts', 'aptox' ); ?>"
							hidden
						>
							<span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
						</button>
					</div>

					<div class="grid-festivity-track">
						<?php foreach ( $posts as $post ) : ?>
							<?php $render_post_card( $post ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php else : ?>
			<div class="archive-grid grid-festivity-grid">
				<?php $render_section_title(); ?>

				<?php foreach ( $posts as $post ) : ?>
					<?php $render_post_card( $post ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
$html = ob_get_clean();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
