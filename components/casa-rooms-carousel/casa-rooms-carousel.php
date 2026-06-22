<?php
/**
 * Component: Casa room tags carousel
 *
 * @context Archive Casas / Page Casa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cache_key   = 'aptox_casa_rooms_carousel_v3';
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$room_tags = array(
	'cozinha'    => __( 'Cozinha', 'aptox' ),
	'banheiro'   => __( 'Banheiro', 'aptox' ),
	'quarto'     => __( 'Quarto', 'aptox' ),
	'escritorio' => __( 'Escritório', 'aptox' ),
	'varanda'    => __( 'Varanda', 'aptox' ),
	'sala'       => __( 'Sala', 'aptox' ),
	'lavanderia' => __( 'Lavanderia', 'aptox' ),
);

$items = array();

foreach ( $room_tags as $tag_slug => $label ) {
	$tag = get_term_by( 'slug', $tag_slug, 'post_tag' );

	if ( ! $tag || is_wp_error( $tag ) ) {
		continue;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'casas',
			'posts_per_page'         => 1,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => true,
			'tax_query'              => array(
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'slug',
					'terms'    => $tag_slug,
				),
			),
		)
	);

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		continue;
	}

	$query->the_post();

	if ( ! has_post_thumbnail() ) {
		wp_reset_postdata();
		continue;
	}

	$tag_link = get_tag_link( $tag );

	if ( is_wp_error( $tag_link ) ) {
		wp_reset_postdata();
		continue;
	}

	$items[] = array(
		'label' => $label,
		'link'  => $tag_link,
		'image' => get_the_post_thumbnail( null, 'medium' ),
	);

	wp_reset_postdata();
}

if ( empty( $items ) ) {
	return;
}

ob_start();
?>

<section class="casa-rooms-carousel" aria-labelledby="casa-rooms-carousel-title">
	<div class="casa-rooms-carousel__inner container-lg">
		<h2 id="casa-rooms-carousel-title" class="casa-rooms-carousel__title">
			<?php esc_html_e( 'Inspiração por espaços', 'aptox' ); ?>
		</h2>

		<div class="casa-rooms-carousel__carousel recipe-tags-carousel">
			<div class="recipe-tags-carousel__viewport">
		<button
			type="button"
			class="recipe-tags-nav recipe-tags-nav--prev"
			aria-label="<?php echo esc_attr__( 'Ver ambientes anteriores', 'aptox' ); ?>"
			disabled
		>
			<span class="recipe-tags-nav__icon"><?php echo aptox_chevron_icon( 'left' ); ?></span>
		</button>

		<div class="tags-track">
			<?php foreach ( $items as $item ) : ?>
				<article class="tag-item-wrapper">
					<a
						href="<?php echo esc_url( $item['link'] ); ?>"
						class="tag-item"
						aria-label="<?php echo esc_attr( $item['label'] ); ?>"
					>
						<figure class="tag-image">
							<?php echo $item['image']; ?>
						</figure>

						<span class="tag-label">
							<?php echo esc_html( $item['label'] ); ?>
						</span>
					</a>
				</article>
			<?php endforeach; ?>
		</div>

		<button
			type="button"
			class="recipe-tags-nav recipe-tags-nav--next"
			aria-label="<?php echo esc_attr__( 'Ver próximos ambientes', 'aptox' ); ?>"
		>
			<span class="recipe-tags-nav__icon"><?php echo aptox_chevron_icon( 'right' ); ?></span>
		</button>
			</div>
		</div>
	</div>
</section>

<?php
$html = ob_get_clean();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
