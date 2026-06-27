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
	: 'aptox_casa_rooms_carousel_v5';

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

if ( ! $spaces_term ) {
	return;
}

$post_ids = get_posts(
	array(
		'post_type'              => 'casas',
		'posts_per_page'         => -1,
		'fields'                 => 'ids',
		'post_status'            => 'publish',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => true,
		'tax_query'              => array(
			array(
				'taxonomy' => $house_taxonomy,
				'field'    => 'term_id',
				'terms'    => array( (int) $spaces_term->term_id ),
			),
		),
	)
);

if ( empty( $post_ids ) ) {
	return;
}

$tags = wp_get_object_terms(
	$post_ids,
	'post_tag',
	array(
		'orderby' => 'name',
		'order'   => 'ASC',
	)
);

if ( is_wp_error( $tags ) || empty( $tags ) ) {
	return;
}

$tags = array_values(
	array_filter(
		$tags,
		static function ( $tag ) {
			return 0 !== strpos( $tag->slug, 'decoracao-de-' );
		}
	)
);

if ( empty( $tags ) ) {
	return;
}

$preferred_slugs = array(
	'cozinha',
	'banheiro',
	'quarto',
	'escritorio',
	'varanda',
	'sala',
	'lavanderia',
);

usort(
	$tags,
	static function ( $a, $b ) use ( $preferred_slugs ) {
		$a_pos = array_search( $a->slug, $preferred_slugs, true );
		$b_pos = array_search( $b->slug, $preferred_slugs, true );

		$a_pos = false === $a_pos ? PHP_INT_MAX : $a_pos;
		$b_pos = false === $b_pos ? PHP_INT_MAX : $b_pos;

		if ( $a_pos !== $b_pos ) {
			return $a_pos <=> $b_pos;
		}

		return strcasecmp( $a->name, $b->name );
	}
);

$category_link = get_term_link( $spaces_term );

if ( is_wp_error( $category_link ) ) {
	$category_link = '';
}

$items = array();

foreach ( $tags as $tag ) {
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
			'tax_query'              => array(
				'relation' => 'AND',
				array(
					'taxonomy' => $house_taxonomy,
					'field'    => 'term_id',
					'terms'    => array( (int) $spaces_term->term_id ),
				),
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'term_id',
					'terms'    => array( (int) $tag->term_id ),
				),
			),
		)
	);

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		continue;
	}

	$query->the_post();

	$item_link = $category_link
		? add_query_arg( 'tag', $tag->slug, $category_link )
		: get_tag_link( $tag );

	if ( is_wp_error( $item_link ) ) {
		wp_reset_postdata();
		continue;
	}

	$image = has_post_thumbnail() ? aptox_render_post_thumbnail( null, 'medium' ) : '';

	$items[] = array(
		'label' => $tag->name,
		'link'  => $item_link,
		'image' => $image,
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
						<?php if ( '' !== $item['image'] ) : ?>
							<figure class="tag-image">
								<?php echo $item['image']; ?>
							</figure>
						<?php endif; ?>

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
