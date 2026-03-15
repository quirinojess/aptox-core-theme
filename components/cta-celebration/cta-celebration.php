<?php
/**
 * CTA - Celebration Season
 *
 * @context Celebration Page
 */

$season = aptox_get_season_context();

$season_slug = '';

if ( is_array( $season ) && ! empty( $season['slug'] ) ) {
	$season_slug = sanitize_title( $season['slug'] );
}

$tag_slug = 'celebrando-no-' . $season_slug;

$posts_per_page = isset( $args['posts_per_page'] )
	? (int) $args['posts_per_page']
	: 2;

$cache_key   = 'aptox_cta_celebration_' . md5( $season_slug . '|' . $posts_per_page );
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$query = null;

if ( ! empty( $season_slug ) ) {

    $query = new WP_Query( [
        'post_type'           => 'celebracoes',
        'posts_per_page'      => $posts_per_page,
        'ignore_sticky_posts' => true,
        'tax_query'           => [
            [
                'taxonomy' => 'post_tag',
                'field'    => 'slug',
                'terms'    => $tag_slug,
            ],
        ],
    ] );

}

if ( $query->have_posts() ) :
?>
<section
  class="season-highlight"
  aria-labelledby="season-highlight-title"
>

  <div class="season-grid">

    <div class="season-posts">
      <?php while ( $query->have_posts() ) : $query->the_post(); ?>
        <article
          <?php post_class( 'season-card' ); ?>
        >
          <a
            href="<?php the_permalink(); ?>"
            class="season-card-link"
          >
            <figure class="season-card-image">
              <?php the_post_thumbnail( 'large' ); ?>
            </figure>
          </a>
        </article>
      <?php endwhile; ?>
      <?php wp_reset_postdata(); ?>
    </div>

    <header class="season-editorial">

      <h2
        id="season-highlight-title"
        class="season-title"
      >
        Chegou o <?php echo esc_html( $season['label'] ); ?>!
      </h2>

      <p class="season-description">
        Ideias, inspirações e detalhes pensados para celebrar
        os momentos mais especiais da estação.
      </p>

    </header>

  </div>

</section>
<?php endif; ?>
