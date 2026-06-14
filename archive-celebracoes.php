<?php
/**
 * Archive template for Celebracoes.
 */

get_header();
?>

<section class="celebre-cta-section">
	<?php get_template_part( 'components/celebre-cta/celebre-cta' ); ?>
</section>

<?php get_template_part( 'components/cta-celebration/cta-celebration' ); ?>

<section class="container">
	<h5 class="center">veja todos os posts</h5>
	<?php get_template_part( 'components/grid-celebration/grid-celebration' ); ?>
</section>

<?php get_template_part( 'components/filter-nav/filter-nav-celebration' ); ?>

<?php get_footer(); ?>
