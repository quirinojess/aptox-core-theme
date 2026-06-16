<?php
/**
 * Component: Celebre season slide
 *
 * Season slide variant for Celebre; posts from celebracoes/{season}/ category.
 *
 * @context Archive Celebracoes / Page Celebration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$season = aptox_get_season_context();

$season_slug = '';

if ( is_array( $season ) && ! empty( $season['slug'] ) ) {
	$season_slug = sanitize_title( $season['slug'] );
}

if ( empty( $season_slug ) ) {
	return;
}

$celebration_taxonomy = 'celebracao_categoria';
$archive_url          = home_url( '/celebracoes/' );

$resolve_season_term = static function ( $slug ) {
	$taxonomies = array( 'celebracao_categoria', 'celebracao' );

	foreach ( $taxonomies as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$term = get_term_by( 'slug', sanitize_title( $slug ), $taxonomy );

		if ( $term && ! is_wp_error( $term ) ) {
			return array(
				'term'     => $term,
				'taxonomy' => $taxonomy,
			);
		}
	}

	return null;
};

$season_term_data = $resolve_season_term( $season_slug );

if ( null === $season_term_data ) {
	return;
}

$season_category_url = get_term_link( $season_term_data['term'] );

if ( is_wp_error( $season_category_url ) ) {
	$season_category_url = trailingslashit( $archive_url ) . $season_slug . '/';
} else {
	$season_category_url = esc_url_raw( $season_category_url );
}

$cache_key   = 'aptox_celebre_season_slide_v7_' . sanitize_key( $season_slug );
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$list = new WP_Query(
	array(
		'post_type'              => 'celebracoes',
		'posts_per_page'         => 4,
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => true,
		'tax_query'              => array(
			array(
				'taxonomy' => $season_term_data['taxonomy'],
				'field'    => 'term_id',
				'terms'    => array( (int) $season_term_data['term']->term_id ),
			),
		),
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

$season_label_for_title = aptox_get_season_label( $season_slug );

if ( 'fim-de-ano' === $season_slug ) {
	$title_main   = sprintf(
		/* translators: %s: article "o" */
		__( 'Celebre %s', 'aptox' ),
		__( 'o', 'aptox' )
	);
	$title_season = __( 'fim de ano', 'aptox' );
} else {
	$season_prep = 'primavera' === $season_slug ? __( 'na', 'aptox' ) : __( 'no', 'aptox' );
	$title_main  = sprintf(
		/* translators: %s: season preposition (na/no) */
		__( 'Celebre %s', 'aptox' ),
		$season_prep
	);
	$title_season = $season_label_for_title;
}

$season_label = '';

if ( is_array( $season ) && ! empty( $season['label'] ) ) {
	$season_label = $season['label'];
} else {
	$season_label = aptox_get_season_label( $season_slug );
}

$badge = aptox_get_season_badge_data( $season_label );
?>

<?php ob_start(); ?>

<section
	class="season-slide celebre-season-slide"
	aria-labelledby="celebre-season-slide-title"
>
	<div class="season-slide-wrapper">
		<article class="season-slide-post">
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
										id="celebre-season-slide-badge-path"
										d="M 50,50 m -37,0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0"
									/>
								</defs>
								<text textLength="232" lengthAdjust="spacing">
									<textPath href="#celebre-season-slide-badge-path" startOffset="0%">
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
					id="celebre-season-slide-title"
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
					<?php esc_html_e( 'Ler mais →', 'aptox' ); ?>
				</a>
			</div>
		</article>

		<aside
			class="season-slide-aside"
			aria-labelledby="celebre-season-slide-aside-title"
		>
			<h3
				id="celebre-season-slide-aside-title"
				class="season-slide-aside-title"
			>
				<a href="<?php echo esc_url( $season_category_url ); ?>">
					<span class="celebre-season-slide-title-main"><?php echo esc_html( $title_main ); ?></span>
					<span class="celebre-season-slide-title-season">
						<?php echo esc_html( aptox_hand_text( $title_season, false ) ); ?>
					</span>
				</a>
			</h3>

			<ul class="season-slide-list">
				<?php
				$list->rewind_posts();

				while ( $list->have_posts() ) :
					$list->the_post();

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
