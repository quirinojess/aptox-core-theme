<?php
/**
 * Component: Post Navigation
 * Theme: Aptox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_singular() ) {
	return;
}

$previous_post = get_previous_post();
$next_post     = get_next_post();

if ( ! $previous_post && ! $next_post ) {
	return;
}
?>

<nav
	class="post-nav"
	aria-label="<?php esc_attr_e( 'Navegação entre posts', 'aptox' ); ?>"
>
	<div class="post-nav-inner">
		<?php if ( $previous_post ) : ?>
			<a
				class="post-nav-link post-nav-link--prev"
				href="<?php echo esc_url( get_permalink( $previous_post ) ); ?>"
				rel="prev"
			>
				<span class="post-nav-icon" aria-hidden="true">&lsaquo;</span>
				<?php esc_html_e( 'Post anterior', 'aptox' ); ?>
			</a>
		<?php endif; ?>

		<?php if ( $next_post ) : ?>
			<a
				class="post-nav-link post-nav-link--next"
				href="<?php echo esc_url( get_permalink( $next_post ) ); ?>"
				rel="next"
			>
				<?php esc_html_e( 'Próximo post', 'aptox' ); ?>
				<span class="post-nav-icon" aria-hidden="true">&rsaquo;</span>
			</a>
		<?php endif; ?>
	</div>
</nav>
