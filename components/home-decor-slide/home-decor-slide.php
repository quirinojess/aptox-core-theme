<?php
/**
 * Component: Season Decoration Slide
 * @Context Index
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$season = aptox_get_season_context();

$season_slug = '';

if ( is_array( $season ) && ! empty( $season['slug'] ) ) {
    $season_slug = sanitize_title( $season['slug'] );
}

$tag_slug = 'decoracao-de-' . $season_slug;

$cache_key   = 'aptox_home_decor_slide_' . sanitize_key( $season_slug );
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$list_args = [
  'post_type'      => 'casas',
  'posts_per_page' => 5,
  'tax_query'      => [
    [
      'taxonomy' => 'post_tag',
      'field'    => 'slug',
      'terms'    => $tag_slug,
    ],
  ],
];

$list = new WP_Query(
	array_merge(
		$list_args,
		[
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		]
	)
);

if ( ! $list->have_posts() ) {
  return;
}

$list->the_post();

$first_post_id = get_the_ID();

$initial = array(
  'title'   => get_the_title(),
  'excerpt' => wp_trim_words( get_the_excerpt(), 26 ),
  'image'   => get_the_post_thumbnail_url( $first_post_id, 'large' ),
  'link'    => get_permalink(),
);
?>

<?php ob_start(); ?>

<section
  class="decoracao-slide"
  aria-labelledby="decoracao-slide-title"
>

  <div class="decoracao-slide-wrapper">

    <article
      class="decoracao-slide-post"
      id="decoracao-slide-post"
    >

      <?php if ( $initial['image'] ) : ?>
        <figure class="decoracao-slide-image">
          <img
            src="<?php echo esc_url( $initial['image'] ); ?>"
            alt="<?php echo esc_attr( $initial['title'] ); ?>"
            loading="lazy"
          >
        </figure>
      <?php endif; ?>

      <div class="decoracao-slide-content">

        <h2
          id="decoracao-slide-title"
          class="decoracao-slide-title"
        >
          <?php echo esc_html( $initial['title'] ); ?>
        </h2>

        <p class="decoracao-slide-excerpt">
          <?php echo esc_html( $initial['excerpt'] ); ?>
        </p>

        <a
          href="<?php echo esc_url( $initial['link'] ); ?>"
          class="decoracao-slide-cta"
        >
          Ler mais →
        </a>

      </div>

    </article>

    <aside
      class="decoracao-slide-aside"
      aria-labelledby="decoracao-slide-aside-title"
    >

      <h3
        id="decoracao-slide-aside-title"
        class="decoracao-slide-aside-title"
      >
        Decore para o <?php echo esc_html( aptox_get_season_label( $season['slug'] ) ); ?>
      </h3>

      <ul class="decoracao-slide-list">

        <li
          class="decoracao-slide-item is-active"
          data-title="<?php echo esc_attr( $initial['title'] ); ?>"
          data-excerpt="<?php echo esc_attr( $initial['excerpt'] ); ?>"
          data-image="<?php echo esc_url( $initial['image'] ); ?>"
          data-link="<?php echo esc_url( $initial['link'] ); ?>"
        >
          <span><?php echo esc_html( $initial['title'] ); ?></span>
        </li>

        <?php
        while ( $list->have_posts() ) :
          $list->the_post();

          if ( get_the_ID() === $first_post_id ) {
            continue;
          }
        ?>
          <li
            class="decoracao-slide-item"
            data-title="<?php echo esc_attr( get_the_title() ); ?>"
            data-excerpt="<?php echo esc_attr( wp_trim_words( get_the_excerpt(), 26 ) ); ?>"
            data-image="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ); ?>"
            data-link="<?php the_permalink(); ?>"
          >
            <span><?php the_title(); ?></span>
          </li>
        <?php endwhile; ?>

      </ul>

    </aside>

  </div>

</section>

<?php
$html = ob_get_clean();

wp_reset_postdata();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
?>
