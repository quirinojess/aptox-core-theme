<?php
/**
 * Mobile bottom navigation (viewport-fixed; rendered outside #align).
 *
 * @package Aptox
 */
?>

<nav
	id="menu-mob"
	class="menu-mobile"
	aria-label="<?php esc_attr_e( 'Menu mobile', 'aptox' ); ?>"
>
	<a href="<?php echo esc_url( home_url( '/em-casa' ) ); ?>"><?php esc_html_e( 'Casa', 'aptox' ); ?></a>
	<a href="<?php echo esc_url( home_url( '/na-cozinha' ) ); ?>"><?php esc_html_e( 'Receitas', 'aptox' ); ?></a>
	<a href="<?php echo esc_url( home_url( '/celebrando' ) ); ?>"><?php esc_html_e( 'Celebre', 'aptox' ); ?></a>
</nav>
