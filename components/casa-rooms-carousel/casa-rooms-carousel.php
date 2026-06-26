<?php
/**
 * Component: Casa room tags carousel
 *
 * @context Archive Casas / Page Casa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cache_key = function_exists( 'aptox_casa_rooms_carousel_cache_key' )
	? aptox_casa_rooms_carousel_cache_key()
	: 'aptox_casa_rooms_carousel_v4';

$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$house_taxonomy = 'casa_categoria';
$spaces_term    = null;

foreach ( array( 'casa_categoria', 'casa' ) as $candidate_taxonomy ) {
	if ( ! taxonomy_exists( $candidate_taxonomy ) ) {
		continue;
	}

	$candidate_term = get_term_by( 'slug', 'decoracao-por-espacos', $candidate_taxonomy );

	if ( $candidate_term && ! is_wp_error( $candidate_term ) ) {
		$house_taxonomy = $candidate_taxonomy;
		$spaces_term    = $candidate_term;
		break;
	}
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
		$tag = get_term_by( 'name', $label, 'post_tag' );
	}

	if ( ! $tag || is_wp_error( $tag ) ) {
		continue;
	}

	$tax_query = array(
		'relation' => 'AND',
		array(
			'taxonomy' => 'post_tag',
			'field'    => 'slug',
			'terms'    => $tag->slug,
		),
	);

	if ( $spaces_term ) {
		$tax_query[] = array(
			'taxonomy' => $house_taxonomy,
			'field'    => 'slug',
			'terms'    => 'decoracao-por-espacos',
		);
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'casas',
			'posts_per_page'         => 1,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => true,
			'tax_query'              => $tax_query,
			'meta_query'             => array(
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			),
		)
	);

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		continue;
	}

	$query->the_post();

	if ( $spaces_term ) {
		$item_link = get_term_link( $spaces_term );

		if ( ! is_wp_error( $item_link ) ) {
			$item_link = add_query_arg( 'tag', $tag->slug, $item_link );
		} else {
			$item_link = get_tag_link( $tag );
		}
	} else {
		$item_link = get_tag_link( $tag );
	}

	if ( is_wp_error( $item_link ) ) {
		wp_reset_postdata();
		continue;
	}

	$items[] = array(
		'label' => $label,
		'link'  => $item_link,
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
