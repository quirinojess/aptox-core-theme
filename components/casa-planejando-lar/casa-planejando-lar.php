<?php
/**
 * Component: Casa Planejando um lar section
 *
 * @context Archive Casas / Page Casa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cache_key   = 'aptox_casa_planejando_lar_v10';
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$house_taxonomy = 'casa_categoria';

foreach ( array( 'casa_categoria', 'casa' ) as $candidate_taxonomy ) {
	if ( ! taxonomy_exists( $candidate_taxonomy ) ) {
		continue;
	}

	$planejando_term = get_term_by( 'slug', 'planejando-um-lar', $candidate_taxonomy );

	if ( $planejando_term && ! is_wp_error( $planejando_term ) ) {
		$house_taxonomy = $candidate_taxonomy;
		break;
	}
}

$query = new WP_Query(
	array(
		'post_type'              => 'casas',
		'posts_per_page'         => 5,
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => true,
		'tax_query'              => array(
			array(
				'taxonomy' => $house_taxonomy,
				'field'    => 'slug',
				'terms'    => 'planejando-um-lar',
			),
		),
	)
);

if ( ! $query->have_posts() ) {
	wp_reset_postdata();
	return;
}

$posts          = $query->posts;
$featured_posts = array_slice( $posts, 0, 2 );
$list_posts     = array_slice( $posts, 2, 3 );

wp_reset_postdata();

$featured_posts = array_values(
	array_filter(
		$featured_posts,
		static function ( $post ) {
			return has_post_thumbnail( $post );
		}
	)
);

if ( empty( $featured_posts ) ) {
	return;
}

$badge_text = 'planejando um novo lar ♥';

ob_start();
?>

<section class="decoracao-section casa-planejando-lar" aria-labelledby="casa-planejando-lar-menu-title">
	<div class="decoracao-posts">
		<?php foreach ( $featured_posts as $post ) : ?>
			<article class="decoracao-card">
				<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="decoracao-link">
					<figure class="decoracao-image">
						<?php
						echo aptox_render_post_thumbnail(
							$post,
							'aptox-card',
							array(
								'alt'   => get_the_title( $post ),
								'sizes' => '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 400px',
							)
						);
						?>
					</figure>

					<h3 class="decoracao-title">
						<?php echo esc_html( get_the_title( $post ) ); ?>
					</h3>
				</a>
			</article>
		<?php endforeach; ?>
	</div>

	<aside class="decoracao-menu casa-planejando-lar__aside" aria-labelledby="casa-planejando-lar-menu-title">
		<div class="casa-planejando-lar-badge" aria-hidden="true">
			<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
				<defs>
					<path
						id="casa-planejando-lar-badge-path"
						d="M 50,50 m -37,0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0"
					/>
				</defs>
				<text>
					<textPath
						href="#casa-planejando-lar-badge-path"
						startOffset="3%"
						textLength="215"
						lengthAdjust="spacing"
					>
						<?php echo esc_html( $badge_text ); ?>
					</textPath>
				</text>
			</svg>
		</div>

		<h3 id="casa-planejando-lar-menu-title" class="decoracao-menu-title">
			<?php esc_html_e( 'Planejando um novo lar', 'aptox' ); ?>
		</h3>

		<p class="casa-planejando-lar-description">
			<?php esc_html_e( 'Dicas e ideias para você que está na fase de planejamento de uma nova casinha.', 'aptox' ); ?>
		</p>

		<?php if ( ! empty( $list_posts ) ) : ?>
			<nav class="casa-planejando-lar-list" aria-label="<?php esc_attr_e( 'Planejando um lar', 'aptox' ); ?>">
				<?php foreach ( $list_posts as $post ) : ?>
					<a class="casa-planejando-lar-list__link" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
						<?php echo esc_html( get_the_title( $post ) ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</aside>
</section>

<?php
$html = ob_get_clean();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
