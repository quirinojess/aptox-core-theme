<?php
/**
 * Component: Grid Loja
 *
 * @context Archive Loja / Taxonomy Loja / Page Loja
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = wp_parse_args(
	$args ?? array(),
	array(
		'use_main_query' => false,
		'posts_per_page' => 12,
		'term_id'        => 0,
	)
);

$use_main_query = (bool) $args['use_main_query'];
$posts_per_page = (int) $args['posts_per_page'];
$term_id        = (int) $args['term_id'];
$paged          = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$max_pages      = 1;
$has_posts      = false;

if ( $use_main_query ) {
	$has_posts = have_posts();
	$max_pages = (int) $GLOBALS['wp_query']->max_num_pages;
} else {
	$query_args = array(
		'post_type'              => 'loja',
		'post_status'            => 'publish',
		'posts_per_page'         => $posts_per_page > 0 ? $posts_per_page : -1,
		'paged'                  => $paged,
		'ignore_sticky_posts'    => true,
		'update_post_meta_cache' => true,
	);

	if ( $term_id > 0 ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'loja_categoria',
				'field'    => 'term_id',
				'terms'    => array( $term_id ),
			),
		);
	}

	$query = new WP_Query( $query_args );

	$has_posts = $query->have_posts();
	$max_pages = (int) $query->max_num_pages;
}

if ( ! $has_posts ) {
	if ( ! $use_main_query && isset( $query ) ) {
		wp_reset_postdata();
	}

	echo '<p class="loja-grid__empty">' . esc_html__( 'Nenhum produto encontrado.', 'aptox' ) . '</p>';
	return;
}

$render_card = static function () {
	$link_compra = get_post_meta( get_the_ID(), 'link_compra', true );
	$url         = $link_compra ? $link_compra : get_permalink();
	$image_alt   = get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true );

	if ( ! is_string( $image_alt ) || '' === $image_alt ) {
		$image_alt = get_the_title();
	}
	?>
	<article <?php post_class( 'loja-grid__item' ); ?>>
		<a
			href="<?php echo esc_url( $url ); ?>"
			class="loja-grid__card"
			target="_blank"
			rel="noopener noreferrer sponsored nofollow"
			aria-label="<?php echo esc_attr( sprintf( __( 'Comprar %s', 'aptox' ), get_the_title() ) ); ?>"
		>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="loja-grid__media">
					<?php
					if ( function_exists( 'aptox_render_loja_thumbnail' ) ) {
						echo aptox_render_loja_thumbnail( get_the_ID(), 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						the_post_thumbnail(
							'medium_large',
							array(
								'loading' => 'eager',
								'alt'     => $image_alt,
							)
						);
					}
					?>
				</figure>
			<?php endif; ?>

			<span class="loja-grid__overlay">
				<span class="loja-grid__name">
					<?php the_title(); ?>
				</span>

				<span class="loja-grid__cta">
					<?php esc_html_e( 'Compre', 'aptox' ); ?>
					<span class="loja-grid__arrow" aria-hidden="true">→</span>
				</span>
			</span>
		</a>
	</article>
	<?php
};
?>

<section class="loja-grid" aria-label="<?php esc_attr_e( 'Produtos da loja', 'aptox' ); ?>">
	<div class="loja-grid__items">
		<?php
		if ( $use_main_query ) {
			while ( have_posts() ) {
				the_post();
				$render_card();
			}
		} else {
			while ( $query->have_posts() ) {
				$query->the_post();
				$render_card();
			}

			wp_reset_postdata();
		}
		?>
	</div>

	<?php if ( $max_pages > $paged ) : ?>
		<?php
		$next_page = $paged + 1;
		$next_url  = '';

		if ( is_page() ) {
			$page_id = get_queried_object_id();

			if ( $page_id > 0 ) {
				$next_url = trailingslashit( get_permalink( $page_id ) ) . user_trailingslashit( 'page/' . $next_page );
			}
		} elseif ( is_tax( 'loja_categoria' ) ) {
			$term = get_queried_object();

			if ( $term instanceof WP_Term ) {
				$term_link = get_term_link( $term );

				if ( ! is_wp_error( $term_link ) ) {
					$next_url = trailingslashit( $term_link ) . user_trailingslashit( 'page/' . $next_page );
				}
			}
		} elseif ( is_post_type_archive( 'loja' ) ) {
			$archive_url = get_post_type_archive_link( 'loja' );

			if ( $archive_url ) {
				$next_url = trailingslashit( $archive_url ) . user_trailingslashit( 'page/' . $next_page );
			}
		}

		if ( '' === $next_url ) {
			$next_url = get_pagenum_link( $next_page );
		}

		aptox_render_archive_load_more(
			array(
				'paged'          => $paged,
				'max_pages'      => $max_pages,
				'next_url'       => $next_url,
				'grid_selector'  => '.loja-grid__items',
				'card_selector'  => '.loja-grid__item',
				'label'          => __( 'Ver mais', 'aptox' ),
			)
		);
		?>
	<?php endif; ?>
</section>
