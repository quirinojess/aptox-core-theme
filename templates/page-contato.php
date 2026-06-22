<?php
/**
 * Template Name: Contato
 */

get_header();
?>

<main class="page-contato-page">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'components/page-contato/page-contato' );
	endwhile;
	?>
</main>

<?php get_footer(); ?>
