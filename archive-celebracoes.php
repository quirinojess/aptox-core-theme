<?php
/**
 * Archive template for Celebracoes.
 */

get_header();
?>

<section class="celebre-cta-section">
	<?php get_template_part( 'components/celebre-cta/celebre-cta' ); ?>
</section>

<?php get_template_part( 'components/grid-festivity/grid-festivity' ); ?>

<?php get_template_part( 'components/filter-nav/filter-nav-celebration' ); ?>

<?php get_footer(); ?>
