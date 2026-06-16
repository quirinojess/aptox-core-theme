<?php
/**
 * Component: CTA Season
 *
 * Seasonal celebration block for the home page.
 *
 * @context index
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$season = aptox_get_season_context();

$season_slug  = '';
$season_label = '';
$season_text    = '';

if ( is_array( $season ) && ! empty( $season['slug'] ) ) {
	$season_slug  = sanitize_title( $season['slug'] );
	$season_label = ! empty( $season['label'] ) ? $season['label'] : aptox_get_season_label( $season_slug );
	$season_text  = aptox_get_season_home_cta_text( $season_slug );
}

$cache_key   = 'aptox_cta_season_v12_' . sanitize_key( $season_slug );
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$featured_post  = null;
$featured_image = '';
$featured_link  = home_url( '/celebrando/' );

if ( ! empty( $season_slug ) && post_type_exists( 'celebracoes' ) ) {
	$category_taxonomies = array( 'celebracao_categoria', 'celebracao' );

	foreach ( $category_taxonomies as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$term = get_term_by( 'slug', $season_slug, $taxonomy );

		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}

		$query = new WP_Query(
			array(
				'post_type'              => 'celebracoes',
				'posts_per_page'         => 1,
				'post_status'            => 'publish',
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => true,
				'orderby'                => array(
					'date' => 'DESC',
					'ID'   => 'DESC',
				),
				'tax_query'              => array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'slug',
						'terms'    => array( $season_slug ),
					),
				),
			)
		);

		if ( $query->have_posts() ) {
			$query->the_post();
			$featured_post  = get_post();
			$featured_image = aptox_get_post_thumbnail_src( get_the_ID(), 'aptox-feature' );
			$featured_link  = get_permalink();
			wp_reset_postdata();
			break;
		}
	}
}

ob_start();

$inner_class = $featured_image
	? 'cta-season-inner has-image'
	: 'cta-season-inner no-image';
?>

<section
	class="cta-season"
	aria-labelledby="cta-season-title"
>
	<div class="<?php echo esc_attr( $inner_class ); ?>">

		<?php if ( $featured_image ) : ?>
			<a
				href="<?php echo esc_url( $featured_link ); ?>"
				class="cta-season-media"
				aria-hidden="true"
				tabindex="-1"
			>
				<figure class="cta-season-image">
					<img
						src="<?php echo esc_url( $featured_image ); ?>"
						alt="<?php echo esc_attr( $featured_post ? get_the_title( $featured_post ) : '' ); ?>"
						loading="lazy"
					>
				</figure>
			</a>
		<?php endif; ?>

		<div class="cta-season-content">
			<h2
				id="cta-season-title"
				class="cta-season-title"
			>
				<a href="<?php echo esc_url( home_url( '/celebrando/' ) ); ?>">
					estamos no
					<span class="cta-season-name">
						<?php echo esc_html( aptox_hand_text( $season_label ) ); ?>
					</span>
				</a>
			</h2>

			<?php if ( ! empty( $season_text ) ) : ?>
				<p class="cta-season-text">
					<?php echo esc_html( $season_text ); ?>
				</p>
			<?php endif; ?>

			<?php if ( $featured_post instanceof WP_Post ) : ?>
				<a
					href="<?php echo esc_url( $featured_link ); ?>"
					class="cta-season-btn"
				>
					leia nosso editorial
				</a>
			<?php endif; ?>
		</div>

	</div>
</section>

<?php
$html = ob_get_clean();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
