<?php
/**
 * Global header template.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php if ( ! function_exists( 'aptox_is_links_page' ) || ! aptox_is_links_page() ) : ?>
<script>
document.documentElement.removeAttribute('data-footer-ad');
document.documentElement.classList.remove('has-footer-ad', 'footer-ad-dismissed');
document.documentElement.style.removeProperty('--footer-ad-lift');
document.documentElement.style.removeProperty('--footer-ad-bar-height');
</script>
<?php endif; ?>

<div id="align">

	<?php if ( ! function_exists( 'aptox_is_links_page' ) || ! aptox_is_links_page() ) : ?>
	<header id="header">
		<div class="container-header">
			<?php get_template_part( 'components/menu/menu' ); ?>
		</div>
	</header>
	<?php endif; ?>
