<?php
/**
 * Section: Decoration in Home
 *
 @Context Home Page
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
$season = function_exists( 'aptox_get_season_context' )
  ? aptox_get_season_context()
  : array( 'slug' => 'null' );

$season_slug = '';

if ( is_array( $season ) && ! empty( $season['slug'] ) ) {
  $season_slug = sanitize_title( $season['slug'] );
}

$tag_slug = 'decoracao-de-' . $season_slug;

$resolved_house_taxonomy = 'casa_categoria';
$decoracao_term          = null;

foreach ( array( 'casa_categoria', 'casa' ) as $candidate_taxonomy ) {
  if ( ! taxonomy_exists( $candidate_taxonomy ) ) {
    continue;
  }

  $candidate_term = get_term_by( 'slug', 'decoracao-sazonal', $candidate_taxonomy );
  if ( $candidate_term && ! is_wp_error( $candidate_term ) ) {
    $resolved_house_taxonomy = $candidate_taxonomy;
    $decoracao_term          = $candidate_term;
    break;
  }
}

if ( ! $decoracao_term && taxonomy_exists( 'casa' ) ) {
  $resolved_house_taxonomy = 'casa';
}


$posts = new WP_Query( array(
  'post_type'           => 'casas',
  'posts_per_page'      => 2,
  'orderby'             => 'date',
  'order'               => 'DESC',
  'ignore_sticky_posts' => true,
  'no_found_rows'       => true,
  'tax_query'           => array(
    'relation' => 'AND',

    array(
      'taxonomy' => $resolved_house_taxonomy,
      'field'    => 'slug',
      'terms'    => 'decoracao-sazonal',
    ),

    array(
      'taxonomy' => 'post_tag',
      'field'    => 'slug',
      'terms'    => $tag_slug,
    ),
  ),
) );


$terms = get_terms( array(
  'taxonomy'   => $resolved_house_taxonomy,
  'hide_empty' => true,
) );

if ( is_wp_error( $terms ) ) {
  $terms = array();
}

?>

<section
  class="decoracao-section"
  aria-labelledby="decoracao-section-title"
>

  <?php if ( $posts->have_posts() ) : ?>
    <div class="decoracao-posts">

      <?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
        <article <?php post_class( 'decoracao-card' ); ?>>

          <a href="<?php the_permalink(); ?>" class="decoracao-link">

            <?php if ( has_post_thumbnail() ) : ?>
              <figure class="decoracao-image">
                <?php
                echo aptox_render_post_thumbnail(
                  null,
                  'aptox-card',
                  array(
                    'sizes' => '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 400px',
                  )
                );
                ?>
              </figure>
            <?php endif; ?>

            <h3 class="decoracao-title">
              <?php the_title(); ?>
            </h3>

          </a>

        </article>
      <?php endwhile; wp_reset_postdata(); ?>

    </div>
  <?php endif; ?>

  <?php if ( ! empty( $terms ) ) : ?>
    <aside
      class="decoracao-menu"
      aria-labelledby="decoracao-menu-title"
    >

      <h3
        id="decoracao-menu-title"
        class="decoracao-menu-title"
      >
        O que você busca hoje?
      </h3>

<nav
  class="decoracao-categories"
  aria-label="Categorias de Decoração"
>
  <?php foreach ( $terms as $term ) : ?>

    <?php
      $term_url = get_term_link( $term );

      if (
        ! is_wp_error( $term_url ) &&
        $term->slug === 'decoracao-sazonal' &&
        ! empty( $season['slug'] )
      ) {
        $term_url = add_query_arg(
           'tag',
    $tag_slug,
    $term_url
        );
      }
    ?>

    <a
      href="<?php echo esc_url( $term_url ); ?>"
      class="decoracao-category"
    >
      <?php echo esc_html( $term->name ); ?>
    </a>

  <?php endforeach; ?>
</nav>


    </aside>
  <?php endif; ?>

</section>
