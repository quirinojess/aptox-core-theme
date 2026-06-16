<?php
/**
 * Loja Filter Navigation
 *
 * @context Archive Loja / Page Loja / Taxonomy Loja
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$taxonomy   = 'loja_categoria';
$archive_url = function_exists( 'aptox_get_loja_archive_url' )
	? aptox_get_loja_archive_url()
	: home_url( '/loja/' );

$current_term_id = 0;
$is_archive_view = is_post_type_archive( 'loja' ) || is_page_template( 'templates/page-loja.php' );

if ( is_tax( $taxonomy ) ) {
	$queried = get_queried_object();

	if ( $queried instanceof WP_Term ) {
		$current_term_id = (int) $queried->term_id;
	}
}

$get_term_thumbnail = static function ( $term_id ) {
	$query = new WP_Query(
		array(
			'post_type'              => 'loja',
			'posts_per_page'         => 1,
			'post_status'            => 'publish',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'loja_categoria',
					'field'    => 'term_id',
					'terms'    => array( (int) $term_id ),
				),
			),
		)
	);

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return '';
	}

	$query->the_post();
	$image = has_post_thumbnail() ? get_the_post_thumbnail( null, 'thumbnail' ) : '';
	wp_reset_postdata();

	return $image;
};

$get_archive_thumbnail = static function () {
	$query = new WP_Query(
		array(
			'post_type'              => 'loja',
			'posts_per_page'         => 1,
			'post_status'            => 'publish',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return '';
	}

	$query->the_post();
	$image = has_post_thumbnail() ? get_the_post_thumbnail( null, 'thumbnail' ) : '';
	wp_reset_postdata();

	return $image;
};

$terms = get_terms(
	array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

if ( is_wp_error( $terms ) ) {
	$terms = array();
}
?>

<nav class="filter-nav filter-nav--loja" aria-label="<?php esc_attr_e( 'Filtros da Loja', 'aptox' ); ?>">
	<ul class="filter-list">
		<li class="filter-item<?php echo $is_archive_view && 0 === $current_term_id ? ' is-active' : ''; ?>">
			<a href="<?php echo esc_url( $archive_url ); ?>">
				<?php
				$all_thumb = $get_archive_thumbnail();

				if ( $all_thumb ) {
					echo $all_thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
				<span><?php esc_html_e( 'Todos', 'aptox' ); ?></span>
			</a>
		</li>

		<?php foreach ( $terms as $term ) : ?>
			<?php
			$term_link = get_term_link( $term, $taxonomy );

			if ( is_wp_error( $term_link ) ) {
				continue;
			}

			$is_active = $current_term_id === (int) $term->term_id;
			$thumb     = $get_term_thumbnail( $term->term_id );
			?>
			<li class="filter-item<?php echo $is_active ? ' is-active' : ''; ?>">
				<a href="<?php echo esc_url( $term_link ); ?>">
					<?php
					if ( $thumb ) {
						echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
					<span><?php echo esc_html( $term->name ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
