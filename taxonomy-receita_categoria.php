<?php
/**
 * Taxonomy template for Receita categories.
 */

get_header();
?>

<?php get_template_part( 'components/recipe-sticky/recipe-sticky' ); ?>

<main class="container-lg">
	<?php
	$tag_slug = aptox_get_receita_tag_query_slug();

	if ( $tag_slug ) :
		$resolved  = aptox_resolve_receita_tag_term( $tag_slug );
		$tag_label = null !== $resolved ? $resolved['term']->name : $tag_slug;
		?>
		<h1 class="taxonomy-title">
			<?php echo esc_html( $tag_label ); ?>
		</h1>
	<?php endif; ?>

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
					'next_url'  => $tag_slug ? add_query_arg( 'tag', $tag_slug, get_pagenum_link( $paged + 1 ) ) : get_pagenum_link( $paged + 1 ),
				)
			);
			?>
		<?php endif; ?>
	<?php else : ?>
		<p>Nenhuma receita encontrada.</p>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
