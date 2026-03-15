<?php
/**
 * Single template for recipes.
 */

get_header();
?>

<main>
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<section class="container">
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="content-recipe">
						<?php the_content(); ?>
					</div>
				</article>

				<div class="content-footer">
					<h5>Com amor,</h5>
					<div class="navigation">
						<?php edit_post_link( 'Editar este artigo', '<p>', '</p>' ); ?>
					</div>
				</div>
			</section>
		<?php endwhile; ?>
	<?php endif; ?>

	<?php get_template_part( 'components/author/author' ); ?>
	<?php get_template_part( 'components/share/share' ); ?>
	<?php get_template_part( 'components/related-posts/related-posts' ); ?>
</main>

<button class="btn-pular-receita" type="button" aria-label="Pular para a receita">
	VER RECEITA
</button>

<?php get_footer(); ?>
