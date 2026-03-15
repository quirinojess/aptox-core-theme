<?php
/**
 * Seasonal Archive Grid — Celebration
 *
@Context Archive Celebration 
 */

$season = aptox_get_season_context();

$season_slug = '';

if ( is_array( $season ) && ! empty( $season['slug'] ) ) {
	$season_slug = sanitize_title( $season['slug'] );
}

$tag_slug = 'celebrando-no-' . $season_slug;

$posts_per_page = isset( $args['posts_per_page'] )
	? (int) $args['posts_per_page']
	: 4;

$cache_key   = 'aptox_grid_celebration_' . md5( $season_slug . '|' . $posts_per_page );
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$query = null;

if ( ! empty( $season_slug ) ) {

	$query = new WP_Query(
		[
			'post_type'              => 'celebracoes',
			'posts_per_page'         => $posts_per_page,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'tax_query'              => [
				[
					'taxonomy' => 'post_tag',
					'field'    => 'slug',
					'terms'    => $tag_slug,
				],
			],
			'orderby'                => 'date',
			'order'                  => 'DESC',
		]
	);
}



if ( $query instanceof WP_Query && $query->have_posts() ) :

	ob_start();
	?>
	<section
	  class="archive-grid"
	  aria-labelledby="archive-grid-title"
	>

	  <?php while ( $query->have_posts() ) : $query->the_post(); ?>
		<article <?php post_class( 'archive-card' ); ?>>

		  <a
			href="<?php the_permalink(); ?>"
			class="archive-thumb"
			aria-hidden="true"
			tabindex="-1"
		  >
			<?php if ( has_post_thumbnail() ) : ?>
			  <figure class="archive-image">
				<?php the_post_thumbnail( 'large' ); ?>
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

	</section>
	<?php
	$html = ob_get_clean();

	wp_reset_postdata();

	set_transient( $cache_key, $html, HOUR_IN_SECONDS );

	echo $html;
endif;
