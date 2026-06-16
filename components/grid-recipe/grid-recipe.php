<?php
/**
 * Component: Grid Recipe
 *
 * Context: Archive Recipes / Home seasonal carousel
 */

$season = aptox_get_season_context();

$season_slug  = '';
$season_label = '';

if ( is_array( $season ) && ! empty( $season['slug'] ) ) {
	$season_slug  = sanitize_title( $season['slug'] );
	$season_label = ! empty( $season['label'] ) ? $season['label'] : aptox_get_season_label( $season_slug );
}

$show_season_title = isset( $args['show_season_title'] )
	? (bool) $args['show_season_title']
	: is_front_page();

$posts_per_page = isset( $args['posts_per_page'] )
	? (int) $args['posts_per_page']
	: ( $show_season_title ? 8 : 4 );

$tag_slug = 'receitas-de-' . $season_slug;

$cache_context = implode(
	'|',
	array(
		$season_slug,
		(string) $posts_per_page,
		$show_season_title ? 'home' : 'archive',
	)
);
$cache_key   = 'aptox_grid_recipe_v9_' . md5( $cache_context );
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$query = new WP_Query();

if ( ! empty( $season_slug ) ) {
	$term = get_term_by( 'slug', $tag_slug, 'post_tag' );

	if ( $term && ! is_wp_error( $term ) ) {
		$query = new WP_Query(
			array(
				'post_type'              => 'receitas',
				'posts_per_page'         => $posts_per_page,
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'tax_query'              => array(
					array(
						'taxonomy' => 'post_tag',
						'field'    => 'term_id',
						'terms'    => array( $term->term_id ),
					),
				),
				'orderby'                => 'rand',
			)
		);
	}
}

if ( ! $query->have_posts() ) {
	return;
}

ob_start();

if ( $show_season_title ) :
	?>
	<div class="grid-recipe-home">
		<div class="grid-recipe-top">
			<header class="grid-recipe-header">
				<div class="grid-recipe-header-inner">
					<figure class="grid-recipe-icon" aria-hidden="true">
						<img
							src="<?php echo esc_url( aptox_recipe_season_icon( $season_slug ) ); ?>"
							alt=""
							loading="lazy"
						>
					</figure>

					<h2 id="recipes-grid-title" class="grid-recipe-title">
						Receitas de
						<span class="grid-recipe-season">
							<?php echo esc_html( aptox_hand_text( $season_label ) ); ?>
						</span>
					</h2>
				</div>
			</header>

			<div class="grid-recipe-carousel__controls">
				<button
					type="button"
					class="grid-recipe-nav grid-recipe-nav--prev"
					aria-label="<?php echo esc_attr__( 'Ver receitas anteriores', 'aptox' ); ?>"
					disabled
					hidden
				>
					<span class="grid-recipe-nav__icon"><?php echo aptox_chevron_icon( 'left' ); ?></span>
				</button>

				<button
					type="button"
					class="grid-recipe-nav grid-recipe-nav--next"
					aria-label="<?php echo esc_attr__( 'Ver próximas receitas', 'aptox' ); ?>"
					hidden
				>
					<span class="grid-recipe-nav__icon"><?php echo aptox_chevron_icon( 'right' ); ?></span>
				</button>
			</div>
		</div>

		<div
			class="grid-recipe-carousel"
			aria-labelledby="recipes-grid-title"
		>
			<div class="grid-recipe-track">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					?>
					<article <?php post_class( 'archive-card' ); ?>>
						<a
							href="<?php the_permalink(); ?>"
							class="archive-thumb"
							aria-hidden="true"
							tabindex="-1"
						>
							<?php if ( has_post_thumbnail() ) : ?>
								<figure class="archive-image">
									<?php echo aptox_render_post_thumbnail( null, 'aptox-card', array( 'loading' => 'eager' ) ); ?>
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
			</div>
		</div>
	</div>
	<?php
else :
	?>
	<section
		class="archive-grid"
		aria-label="<?php echo esc_attr__( 'Receitas', 'aptox' ); ?>"
	>
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			?>
			<article <?php post_class( 'archive-card' ); ?>>
				<a
					href="<?php the_permalink(); ?>"
					class="archive-thumb"
					aria-hidden="true"
					tabindex="-1"
				>
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="archive-image">
							<?php echo aptox_render_post_thumbnail( null, 'aptox-card' ); ?>
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
endif;

$html = ob_get_clean();

wp_reset_postdata();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
