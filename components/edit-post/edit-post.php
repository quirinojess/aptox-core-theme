<?php
/**
 * Component: Fixed edit post link (logged-in editors)
 * Theme: Aptox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_singular() ) {
	return;
}

$post_id   = get_queried_object_id();
$edit_link = $post_id ? get_edit_post_link( $post_id ) : '';

if ( ! $edit_link ) {
	return;
}
?>

<aside class="post-edit-fixed" aria-label="<?php esc_attr_e( 'Editar post', 'aptox' ); ?>">
	<a class="post-edit-fixed__link" href="<?php echo esc_url( $edit_link ); ?>">
		<?php esc_html_e( 'Editar post', 'aptox' ); ?>
	</a>
</aside>
