<?php
/**
 * Single template for Celebracoes.
 */

get_header();
?>

<main>
	<section class="align-posts">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<section class="container-blog">
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h1><?php the_title(); ?></h1>
						<div id="content">
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

		<?php get_template_part( 'components/sidebar/sidebar' ); ?>
	</section>

	<?php get_template_part( 'components/share/share' ); ?>
	<?php get_template_part( 'components/related-posts/related-posts' ); ?>
</main>

<?php get_footer(); ?>
