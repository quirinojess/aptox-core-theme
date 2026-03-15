<?php
/**
 * Component: Recipe Categories Carousel
 * @Context Home ARCHIVE RECIPE
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$cache_key   = 'aptox_recipe_carousel';
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$terms = get_terms(
	array(
		'taxonomy'   => 'receita_categoria',
		'hide_empty' => true,
	)
);

if ( empty( $terms ) || is_wp_error( $terms ) ) {
  return;
}

ob_start();
?>

<section
  class="recipe-tags-carousel"
  aria-labelledby="recipe-tags-carousel-title"
>

  <h2
    id="recipe-tags-carousel-title"
    class="screen-reader-text"
  >
    Categorias de Receitas
  </h2>

  <div class="tags-track">

    <?php foreach ( $terms as $term ) :

      $query = new WP_Query( array(
        'post_type'           => 'receitas',
        'posts_per_page'      => 1,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'tax_query'           => array(
          array(
            'taxonomy' => 'receita_categoria',
            'field'    => 'term_id',
            'terms'    => $term->term_id,
          ),
        ),
      ) );

      if ( ! $query->have_posts() ) {
        wp_reset_postdata();
        continue;
      }

      $query->the_post();
    ?>

      <article class="tag-item-wrapper">

        <a
          href="<?php echo esc_url( get_term_link( $term ) ); ?>"
          class="tag-item"
          aria-label="<?php echo esc_attr( $term->name ); ?>"
        >

          <?php if ( has_post_thumbnail() ) : ?>
            <figure class="tag-image">
              <?php the_post_thumbnail( 'medium' ); ?>
            </figure>
          <?php endif; ?>

          <span class="tag-label">
            <?php echo esc_html( $term->name ); ?>
          </span>

        </a>

      </article>

    <?php
      wp_reset_postdata();
    endforeach;
    ?>

  </div>

</section>
<?php
$html = ob_get_clean();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
?>
