<?php
/**
 * Component: Recipe Categories Carousel
 * @Context Home ARCHIVE RECIPE
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Global cache for the recipe carousel.
 *
 */
$cache_key   = 'aptox_recipe_carousel_v6';
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$resolved_recipe_taxonomy = 'receita_categoria';

$recipe_terms = get_terms(
	array(
		'taxonomy'   => 'receita_categoria',
		'hide_empty' => true,
	)
);

if ( empty( $recipe_terms ) || is_wp_error( $recipe_terms ) ) {
	$legacy_terms = get_terms(
		array(
			'taxonomy'   => 'receita',
			'hide_empty' => true,
		)
	);

	if ( ! empty( $legacy_terms ) && ! is_wp_error( $legacy_terms ) ) {
		$resolved_recipe_taxonomy = 'receita';
		$recipe_terms             = $legacy_terms;
	}
}

$terms = $recipe_terms;

if ( empty( $terms ) || is_wp_error( $terms ) ) {
  return;
}

ob_start();
?>

<section
  class="recipe-sticky-categories"
  aria-label="<?php esc_attr_e( 'Categorias de receitas', 'aptox' ); ?>"
>
  <div class="recipe-tags-carousel">
  <div class="recipe-tags-carousel__viewport">

    <button
      type="button"
      class="recipe-tags-nav recipe-tags-nav--prev"
      aria-label="<?php echo esc_attr__( 'Ver categorias anteriores', 'aptox' ); ?>"
      disabled
    >
      <span class="recipe-tags-nav__icon"><?php echo aptox_chevron_icon( 'left' ); ?></span>
    </button>

    <div class="tags-track">

    <?php foreach ( $terms as $term ) :

      $query = new WP_Query( array(
        'post_type'           => 'receitas',
        'posts_per_page'      => 1,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'tax_query'           => array(
          array(
            'taxonomy' => $resolved_recipe_taxonomy,
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

      $term_link = get_term_link( $term, $resolved_recipe_taxonomy );
      if ( is_wp_error( $term_link ) ) {
        $recipe_archive = get_post_type_archive_link( 'receitas' );
        if ( ! $recipe_archive ) {
          $recipe_archive = home_url( '/receitas/' );
        }
        $term_link = trailingslashit( untrailingslashit( $recipe_archive ) ) . 'categoria/' . $term->slug . '/';
      }
    ?>

      <article class="tag-item-wrapper">

        <a
          href="<?php echo esc_url( $term_link ); ?>"
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

    <button
      type="button"
      class="recipe-tags-nav recipe-tags-nav--next"
      aria-label="<?php echo esc_attr__( 'Ver próximas categorias', 'aptox' ); ?>"
    >
      <span class="recipe-tags-nav__icon"><?php echo aptox_chevron_icon( 'right' ); ?></span>
    </button>

  </div>

  </div>
</section>
<?php
$html = ob_get_clean();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
?>
