<?php
/**
 * Main index template.
 */

get_header();
?>

<main>
	<section class="index-cta-section">
		<?php get_template_part( 'components/index-cta/index-cta' ); ?>
	</section>

	<section>
		<?php get_template_part( 'components/info-grid/info-grid' ); ?>
	</section>

	<section>
		<?php get_template_part( 'components/cta-season/cta-season' ); ?>
	</section>

	<section class="container-lg">
		<?php get_template_part( 'components/grid-recipe/grid-recipe' ); ?>
	</section>

	<section>
		<?php get_template_part( 'components/cta-editorial/cta-editorial' ); ?>
	</section>

	<section class="container">
		<?php get_template_part( 'components/home-decor-slide/home-decor-slide' ); ?>
	</section>
</main>

<?php get_footer(); ?>
