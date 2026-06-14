<?php
/**
 * Template Name: Casa
 */

get_header();
?>

<section class="casa-cta-section">
	<?php get_template_part( 'components/casa-cta/casa-cta' ); ?>
</section>

<section class="container container-casa-top">
	<?php get_template_part( 'components/grid-casa-decor/grid-casa-decor' ); ?>
</section>

<section class="container">
	<?php get_template_part( 'components/casa-reforma/casa-reforma' ); ?>
</section>

<?php get_template_part( 'components/casa-diy-marquee/casa-diy-marquee' ); ?>

<?php get_template_part( 'components/casa-organizacao/casa-organizacao' ); ?>

<?php get_template_part( 'components/casa-jardinagem/casa-jardinagem' ); ?>

<section class="container">
	<?php get_template_part( 'components/home-decor/home-decor' ); ?>
</section>

<?php get_footer(); ?>
