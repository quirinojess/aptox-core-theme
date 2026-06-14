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
					</div>
				</section>
			<?php endwhile; ?>
		<?php endif; ?>

		<?php get_template_part( 'components/sidebar/sidebar' ); ?>
	</section>

	<?php get_template_part( 'components/post-share-stack/post-share-stack' ); ?>
	<?php get_template_part( 'components/related-posts/related-posts' ); ?>
	<?php get_template_part( 'components/post-taxonomies/post-taxonomies' ); ?>
	<?php get_template_part( 'components/post-nav/post-nav' ); ?>
</main>

<?php get_footer(); ?>
