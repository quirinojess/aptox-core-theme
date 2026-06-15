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
				</div>
			</section>
		<?php endwhile; ?>
	<?php endif; ?>

	<?php get_template_part( 'components/author/author' ); ?>
	<?php get_template_part( 'components/post-share-stack/post-share-stack' ); ?>
	<?php get_template_part( 'components/related-posts/related-posts' ); ?>
	<?php get_template_part( 'components/post-taxonomies/post-taxonomies' ); ?>
	<?php get_template_part( 'components/post-nav/post-nav' ); ?>
</main>

<?php get_footer(); ?>
