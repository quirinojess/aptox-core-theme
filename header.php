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

<div id="align">

	<header id="header">
		<div class="container-header">
			<?php get_template_part( 'components/menu/menu' ); ?>
		</div>
	</header>
