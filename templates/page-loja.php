<?php
/**
 * Template Name: Loja
 */

get_header();
?>

<section class="loja-cta-section">
	<?php get_template_part( 'components/loja-cta/loja-cta' ); ?>
</section>

<main class="container loja-page">
	<?php
	get_template_part(
		'components/grid-loja/grid-loja',
		null,
		array(
			'posts_per_page' => 12,
		)
	);
	?>
</main>

<?php get_template_part( 'components/filter-nav/filter-nav-loja' ); ?>

<?php get_footer(); ?>
