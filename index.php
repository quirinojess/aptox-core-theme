<?php
/**
 * Main index template.
 */

get_header();
?>

<main>
	<section class="container-lg">
		<?php get_template_part( 'components/grid-recipe/grid-recipe' ); ?>
	</section>

	<section class="container">
		<h5>e muitas receitas</h5>
		<?php get_template_part( 'components/recipe-carousel/recipe-carousel' ); ?>
	</section>

	<section>
		<?php get_template_part( 'components/cta-editorial/cta-editorial' ); ?>
	</section>

	<section class="container">
		<?php get_template_part( 'components/home-decor-slide/home-decor-slide' ); ?>
	</section>
</main>

<?php get_footer(); ?>
