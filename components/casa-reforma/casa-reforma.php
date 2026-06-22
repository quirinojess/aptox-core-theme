<?php
/**
 * Component: Casa Reforma section
 *
 * @context Archive Casas / Page Casa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cache_key   = 'aptox_casa_reforma_v6';
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

	$reforma_term = get_term_by( 'slug', 'reforma', $candidate_taxonomy );

	if ( $reforma_term && ! is_wp_error( $reforma_term ) ) {
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
				'terms'    => 'reforma',
			),
		),
	)
);

if ( ! $query->have_posts() ) {
	return;
}

$posts = $query->posts;

wp_reset_postdata();

$featured_post = $posts[0];
$featured_id   = $featured_post->ID;
$featured_url  = get_permalink( $featured_id );
$featured_img  = get_the_post_thumbnail_url( $featured_id, 'large' );

if ( ! $featured_img ) {
	return;
}

$divider_phrase = 'DICAS DE REFORMA · ';
$divider_text   = str_repeat( $divider_phrase, 12 );

ob_start();
?>

<section class="casa-reforma" aria-labelledby="casa-reforma-title">
	<h2 id="casa-reforma-title" class="screen-reader-text">
		<?php esc_html_e( 'Reforma', 'aptox' ); ?>
	</h2>

	<div class="casa-reforma-layout">
		<figure class="casa-reforma-featured">
			<a href="<?php echo esc_url( $featured_url ); ?>" tabindex="-1" aria-hidden="true">
				<img
					src="<?php echo esc_url( $featured_img ); ?>"
					alt="<?php echo esc_attr( get_the_title( $featured_id ) ); ?>"
					loading="lazy"
				>
			</a>
		</figure>

		<div class="casa-reforma-divider" aria-hidden="true">
			<div class="casa-reforma-divider__viewport">
				<div class="casa-reforma-divider__marquee">
					<span class="casa-reforma-divider__track"><?php echo esc_html( $divider_text ); ?></span>
					<span class="casa-reforma-divider__track" aria-hidden="true"><?php echo esc_html( $divider_text ); ?></span>
				</div>
			</div>
		</div>

		<div class="casa-reforma-list">
			<?php
			foreach ( $posts as $post ) :
				setup_postdata( $post );
				?>
				<article class="casa-reforma-item">
					<h3 class="casa-reforma-item-title">
						<a href="<?php echo esc_url( get_permalink( $post ) ); ?>">
							<?php echo esc_html( get_the_title( $post ) ); ?>
						</a>
					</h3>

					<?php if ( has_excerpt( $post ) ) : ?>
						<p class="casa-reforma-item-excerpt">
							<?php echo esc_html( get_post_field( 'post_excerpt', $post->ID ) ); ?>
						</p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
$html = ob_get_clean();

wp_reset_postdata();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
