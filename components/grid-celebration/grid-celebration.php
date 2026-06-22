<?php
/**
 * Seasonal Archive Grid — Celebration
 *
 * @context Archive Celebration
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

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$queried = get_queried_object();

$active_taxonomy = '';
$active_term_slug = '';
$allowed_category_taxonomies = array( 'celebracao_categoria', 'celebracao' );

if ( $queried instanceof WP_Term && in_array( $queried->taxonomy, $allowed_category_taxonomies, true ) ) {
	$active_taxonomy  = $queried->taxonomy;
	$active_term_slug = $queried->slug;
}

$tax_query = array();

if ( ! empty( $active_taxonomy ) && ! empty( $active_term_slug ) ) {
	$tax_query[] = array(
		'taxonomy' => $active_taxonomy,
		'field'    => 'slug',
		'terms'    => array( $active_term_slug ),
	);
} elseif ( ! empty( $season_slug ) ) {
	$tax_query[] = array(
		'taxonomy' => 'post_tag',
		'field'    => 'slug',
		'terms'    => array( $tag_slug ),
	);
}

$cache_context = implode(
	'|',
	array(
		$season_slug,
		$active_taxonomy,
		$active_term_slug,
		(string) $posts_per_page,
		(string) $paged,
	)
);
$cache_key   = 'aptox_grid_celebration_' . md5( $cache_context );
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$query_args = array(
	'post_type'              => 'celebracoes',
	'posts_per_page'         => $posts_per_page,
	'ignore_sticky_posts'    => true,
	'no_found_rows'          => false,
	'update_post_term_cache' => false,
	'update_post_meta_cache' => false,
	'paged'                  => $paged,
	'orderby'                => 'date',
	'order'                  => 'DESC',
);

if ( ! empty( $tax_query ) ) {
	$query_args['tax_query'] = $tax_query;
}

$query = new WP_Query( $query_args );



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
