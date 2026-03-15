<?php
/**
 * Template Name: Receitas
 */

get_header();
?>

<section class="container">
	<?php
	get_template_part(
		'components/grid-recipe/grid-recipe',
		null,
		array(
			'posts_per_page' => 8,
		)
	);
	?>
</section>

<section class="container">
	<h5 class="center">busque por tipo</h5>
</section>

<?php get_template_part( 'components/recipe-carousel/recipe-carousel' ); ?>

<?php get_footer(); ?>
