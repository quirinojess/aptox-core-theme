<?php
/**
 * Template Name: Editorial
 */

get_header();
?>

<main class="page-editorial-page">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'components/page-editorial/page-editorial' );
	endwhile;
	?>
</main>

<?php get_footer(); ?>
