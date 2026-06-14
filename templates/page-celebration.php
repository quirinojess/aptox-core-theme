<?php
/**
 * Template Name: Celebration
 */

get_header();
?>

<section class="celebre-cta-section">
	<?php get_template_part( 'components/celebre-cta/celebre-cta' ); ?>
</section>

<section class="container">
	<?php get_template_part( 'components/celebre-season-slide/celebre-season-slide' ); ?>
</section>

<?php get_template_part( 'components/celebre-season/celebre-season' ); ?>

<?php get_template_part( 'components/celebre-info-grid/celebre-info-grid' ); ?>

<?php get_template_part( 'components/filter-nav/filter-nav-celebration' ); ?>

<?php get_footer(); ?>
