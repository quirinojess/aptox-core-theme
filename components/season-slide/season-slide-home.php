<?php
/**
 * Component: Season slide (home decor variant)
 *
 * @context Index
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

$cache_key   = 'aptox_season_slide_home_v2_' . sanitize_key( $season_slug );
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$list_args = array(
	'post_type'      => 'casas',
	'posts_per_page' => 4,
	'tax_query'      => array(
		array(
			'taxonomy' => 'post_tag',
			'field'    => 'slug',
			'terms'    => $tag_slug,
		),
	),
);

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

$season_prep       = 'primavera' === $season_slug ? __( 'a', 'aptox' ) : __( 'o', 'aptox' );
$season_title_main = sprintf(
	/* translators: %s: article (a/o) */
	__( 'Decore para %s', 'aptox' ),
	$season_prep
);
$season_title_name = aptox_get_season_label( $season_slug );

$season_label = '';

if ( is_array( $season ) && ! empty( $season['label'] ) ) {
	$season_label = $season['label'];
} elseif ( $season_slug ) {
	$season_label = aptox_get_season_label( $season_slug );
}

$badge = aptox_get_season_badge_data( $season_label );
?>

<?php ob_start(); ?>

<section
  class="season-slide"
  aria-labelledby="season-slide-title"
>

  <div class="season-slide-wrapper">

    <article
      class="season-slide-post"
      id="season-slide-post"
    >

      <?php if ( $initial['image'] ) : ?>
        <figure class="season-slide-image">
          <img
            src="<?php echo esc_url( $initial['image'] ); ?>"
            alt="<?php echo esc_attr( $initial['title'] ); ?>"
            loading="lazy"
          >

          <?php if ( ! empty( $badge['text'] ) ) : ?>
            <div
              class="season-slide-badge"
              style="--season-slide-badge-font-size: <?php echo esc_attr( $badge['font_size'] ); ?>;"
              aria-hidden="true"
            >
              <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <defs>
                  <path
                    id="season-slide-badge-path"
                    d="M 50,50 m -37,0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0"
                  />
                </defs>
                <text textLength="232" lengthAdjust="spacing">
                  <textPath href="#season-slide-badge-path" startOffset="0%">
                    <?php echo esc_html( $badge['text'] ); ?>
                  </textPath>
                </text>
              </svg>
            </div>
          <?php endif; ?>
        </figure>
      <?php endif; ?>

      <div class="season-slide-content">

        <h2
          id="season-slide-title"
          class="season-slide-title"
        >
          <?php echo esc_html( $initial['title'] ); ?>
        </h2>

        <p class="season-slide-excerpt">
          <?php echo esc_html( $initial['excerpt'] ); ?>
        </p>

        <a
          href="<?php echo esc_url( $initial['link'] ); ?>"
          class="season-slide-cta"
        >
          Ler mais →
        </a>

      </div>

    </article>

    <aside
      class="season-slide-aside"
      aria-labelledby="season-slide-aside-title"
    >

      <h3
        id="season-slide-aside-title"
        class="season-slide-aside-title"
      >
        <span class="season-slide-aside-title-main"><?php echo esc_html( $season_title_main ); ?></span>
        <span class="season-slide-aside-title-season"><?php echo esc_html( aptox_hand_text( $season_title_name, false ) ); ?></span>
      </h3>

      <ul class="season-slide-list">

        <?php
        $list_count = 0;

        while ( $list->have_posts() ) :
          $list->the_post();

          if ( get_the_ID() === $first_post_id ) {
            continue;
          }

          $list_count++;

          if ( $list_count > 3 ) {
            break;
          }

          $item_excerpt = wp_trim_words( get_the_excerpt(), 18 );
        ?>
          <li
            class="season-slide-item"
            data-title="<?php echo esc_attr( get_the_title() ); ?>"
            data-excerpt="<?php echo esc_attr( wp_trim_words( get_the_excerpt(), 26 ) ); ?>"
            data-image="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ); ?>"
            data-link="<?php the_permalink(); ?>"
          >
            <div class="season-slide-item-content">
              <span class="season-slide-item-title"><?php the_title(); ?></span>
              <p class="season-slide-item-excerpt"><?php echo esc_html( $item_excerpt ); ?></p>
            </div>
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
