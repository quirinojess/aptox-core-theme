<?php
/**
 * Archive template for Receitas.
 */

get_header();
?>

<?php get_template_part( 'components/recipe-sticky/recipe-sticky' ); ?>

<?php if ( aptox_get_receita_tag_query_slug() ) : ?>
	<?php
	get_template_part(
		'components/receitas-tag-results/receitas-tag-results',
		null,
		array(
			'use_main_query' => true,
		)
	);
	?>
<?php else : ?>
	<section class="container">
		<?php
		get_template_part(
			'components/grid-recipe/grid-recipe',
			null,
			array(
				'posts_per_page' => 8,
			)
		);
		?>
	</section>
<?php endif; ?>

<?php get_footer(); ?>
