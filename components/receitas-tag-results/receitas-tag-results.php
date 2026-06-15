<?php
/**
 * Component: Receitas filtered by ?tag= query param.
 *
 * @context Receitas archive / page template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tag_slug = aptox_get_receita_tag_query_slug();

if ( '' === $tag_slug ) {
	return;
}

$use_main_query = ! empty( $args['use_main_query'] );
$query          = null;

if ( $use_main_query ) {
	global $wp_query;
	$query = $wp_query;
} else {
	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

	$query = new WP_Query(
		array(
			'post_type'      => 'receitas',
			'posts_per_page' => (int) get_option( 'posts_per_page' ),
			'paged'          => $paged,
			'tax_query'      => array(
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'slug',
					'terms'    => $tag_slug,
				),
			),
		)
	);
}

if ( ! $query instanceof WP_Query ) {
	return;
}

$paged     = max( 1, (int) $query->get( 'paged' ), (int) get_query_var( 'page' ) );
$max_pages = (int) $query->max_num_pages;
$tag_term  = get_term_by( 'slug', $tag_slug, 'post_tag' );
$tag_label = ( $tag_term && ! is_wp_error( $tag_term ) ) ? $tag_term->name : $tag_slug;
?>

<main class="container-lg">
	<h1 class="taxonomy-title">
		<?php echo esc_html( $tag_label ); ?>
	</h1>

	<?php if ( $query->have_posts() ) : ?>
		<section class="archive-grid">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				?>
				<article class="archive-card">
					<a href="<?php the_permalink(); ?>" class="archive-thumb">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'large' ); ?>
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

		<?php if ( $max_pages > $paged ) : ?>
			<?php
			aptox_render_archive_load_more(
				array(
					'paged'     => $paged,
					'max_pages' => $max_pages,
					'next_url'  => add_query_arg( 'tag', $tag_slug, get_pagenum_link( $paged + 1 ) ),
				)
			);
			?>
		<?php endif; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nenhuma receita encontrada.', 'aptox' ); ?></p>
	<?php endif; ?>
</main>

<?php
if ( ! $use_main_query ) {
	wp_reset_postdata();
}
