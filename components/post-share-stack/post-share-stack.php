<?php
/**
 * Component: Post share stack (back to top + share bar)
 * Theme: Aptox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_singular() ) {
	return;
}
?>

<div class="post-share-stack">
	<?php get_template_part( 'components/back-to-top/back-to-top' ); ?>
	<?php get_template_part( 'components/share/share' ); ?>
</div>
