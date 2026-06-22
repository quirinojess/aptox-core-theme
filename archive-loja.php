<?php
/**
 * Archive template for Loja products.
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
			'use_main_query' => true,
		)
	);
	?>
</main>

<?php get_template_part( 'components/filter-nav/filter-nav-loja' ); ?>

<?php get_footer(); ?>
