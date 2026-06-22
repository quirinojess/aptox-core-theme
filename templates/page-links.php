<?php
/**
 * Template Name: Links
 *
 * Landing page estilo Linktree, sem header/footer globais do tema.
 */

get_header();
?>

<main class="page-links-page">
	<?php get_template_part( 'components/page-links/page-links' ); ?>
</main>

<?php
get_footer();
