<?php
/**
 * Taxonomy template for Receita tags.
 *
 * Redirects to the Receitas archive/page with ?tag= so the sticky filter
 * and category-style layout are always used.
 */

$term = get_queried_object();

if ( $term instanceof WP_Term && function_exists( 'aptox_get_receitas_archive_url' ) ) {
	wp_safe_redirect(
		add_query_arg( 'tag', $term->slug, aptox_get_receitas_archive_url() ),
		301
	);
	exit;
}

get_header();
?>

<?php get_template_part( 'components/recipe-sticky/recipe-sticky' ); ?>

<main class="container-lg">
	<?php if ( have_posts() ) : ?>
		<?php
		$paged     = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$max_pages = (int) $wp_query->max_num_pages;
		?>
		<section class="archive-grid">
			<?php while ( have_posts() ) : the_post(); ?>
				<article class="archive-card">
					<a href="<?php the_permalink(); ?>" class="archive-thumb">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php echo aptox_render_post_thumbnail( null, 'aptox-card' ); ?>
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
					'next_url'  => get_pagenum_link( $paged + 1 ),
				)
			);
			?>
		<?php endif; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nenhuma receita encontrada.', 'aptox' ); ?></p>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
