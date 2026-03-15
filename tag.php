<?php
/**
 * Tag archive router template.
 */

get_header();

$post_type = null;

if ( have_posts() ) {
	the_post();
	$post_type = get_post_type();
	rewind_posts();
}

$template = locate_template( 'taxonomy-casa_categoria.php' );
$archive  = locate_template( 'archive.php' );

if ( 'casas' === $post_type && $template ) {
	include $template;
} else {
	if ( $archive ) {
		include $archive;
	} else {
		include locate_template( 'index.php' );
	}
}

get_footer();
