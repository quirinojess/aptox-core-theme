<?php
/**
 * Taxonomy template for Receita categories.
 */

get_header();
?>

<div id="recipe-sticky">
	<div class="recipe-sticky-header">
		<span class="recipe-sticky-title">BUSQUE POR TIPO</span>

		<button
			id="recipe-toggle"
			class="recipe-toggle"
			aria-expanded="false"
			aria-controls="recipe-sticky-content"
			aria-label="Abrir ou fechar filtros"
		>
			<span class="icon icon-open">⌵</span>
			<span class="icon icon-close">✕</span>
		</button>
	</div>

	<section id="recipe-sticky-content">
		<?php get_template_part( 'components/recipe-carousel/recipe-carousel' ); ?>
	</section>
</div>

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
			<div class="archive-load-more">
				<button
					type="button"
					class="next page-numbers"
					data-load-more-global
					data-grid-selector=".archive-grid"
					data-next-url="<?php echo esc_url( get_pagenum_link( $paged + 1 ) ); ?>"
				>
					Leia mais
				</button>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<p>Nenhuma receita encontrada.</p>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
