<?php
/**
 * Component: Related Posts
 */

if ( ! is_singular() ) {
  return;
}

$context = aptox_get_related_tax_context();

if ( ! $context ) {
  return;
}

$post_id   = $context['post_id'];
$post_type = $context['post_type'];
$taxonomy  = $context['taxonomy'];
$term_id   = $context['term_id'];
$limit     = $context['posts_per_page'];

$season_tag_slug = null;
$season          = aptox_get_season_context();

if ( ! empty( $season['slug'] ) ) {

  $tags = get_the_terms( $post_id, 'post_tag' );

  if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
    $tag_slugs = wp_list_pluck( $tags, 'slug' );
    $base_slug = sanitize_title( $season['slug'] );
		$candidates = array(
			$base_slug,
			'decoracao-de-' . $base_slug,
			'receitas-de-' . $base_slug,
			'celebrando-no-' . $base_slug,
			'post-de-' . $base_slug,
			'posts-de-' . $base_slug,
		);

		foreach ( $candidates as $candidate ) {
			if ( in_array( $candidate, $tag_slugs, true ) ) {
				$season_tag_slug = $candidate;
				break;
			}
		}
  }
}

$base_query = new WP_Query(
	[
		'post_type'              => $post_type,
		'posts_per_page'         => $limit,
		'post__not_in'           => [ $post_id ],
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'tax_query'              => [
			[
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => [ $term_id ],
			],
		],
		'orderby'                => 'rand',
	]
);

if ( ! $base_query->have_posts() ) {
  return;
}

$related_posts = $base_query->posts;

if ( $season_tag_slug ) {

	$season_query = new WP_Query(
		[
		'post_type'              => $post_type,
			'posts_per_page'         => $limit,
			'post__not_in'           => [ $post_id ],
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'tax_query'              => [
				'relation' => 'AND',
				[
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => [ $term_id ],
				],
				[
					'taxonomy' => 'post_tag',
					'field'    => 'slug',
					'terms'    => [ $season_tag_slug ],
				],
			],
			'orderby'                => 'rand',
		]
	);

	/**
	 * Only replace if season has posts
	 */
	if ( $season_query->have_posts() ) {
		$related_posts = $season_query->posts;
	}
}

if ( empty( $related_posts ) ) {
  return;
}
?>

<section
  class="related-container"
  aria-labelledby="related-title"
>

  <div class="related-inner">

    <header class="related-header">

      <h2
        id="related-title"
        class="related-heading"
      >
        <span class="related-leia">Leia também</span>
        <span class="related-mais">esses outros posts</span>
      </h2>

    </header>

    <div class="related-grid">

      <?php foreach ( $related_posts as $post ) : setup_postdata( $post ); ?>

        <article <?php post_class( 'related-card' ); ?>>

          <a
            href="<?php the_permalink(); ?>"
            class="related-thumb"
            aria-hidden="true"
            tabindex="-1"
          >
            <?php if ( has_post_thumbnail() ) : ?>
              <figure class="related-image">
                <?php the_post_thumbnail( 'blog' ); ?>
              </figure>
            <?php endif; ?>
          </a>

          <h3 class="related-title">
            <a href="<?php the_permalink(); ?>">
              <?php the_title(); ?>
            </a>
          </h3>

        </article>

      <?php endforeach; wp_reset_postdata(); ?>

    </div>

  </div>

</section>
