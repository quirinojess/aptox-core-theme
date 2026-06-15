<?php
/**
 * Template Name: Sobre
 */

get_header();
?>

<main class="page-sobre-page">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'components/page-sobre/page-sobre' );
	endwhile;
	?>

	<?php get_template_part( 'components/info-grid/info-grid' ); ?>
	<?php get_template_part( 'components/page-sobre-timeline/page-sobre-timeline' ); ?>
	<?php get_template_part( 'components/page-sobre-clipping/page-sobre-clipping' ); ?>
</main>

<?php get_footer(); ?>
