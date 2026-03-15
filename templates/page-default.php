<?php
/**
 * Template Name: Default
 */

get_header();
?>

<main>
	<?php while ( have_posts() ) : the_post(); ?>

		<section class="hero-container">
			<h1 class="taxonomy-title">
				<?php the_title(); ?>
			</h1>
		</section>

		<section class="container">
			<?php the_content(); ?>
		</section>

	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
