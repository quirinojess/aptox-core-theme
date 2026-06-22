<?php
/**
 * Taxonomy template for Loja categories.
 */

get_header();

$term = get_queried_object();

if ( ! $term instanceof WP_Term ) {
	$term = null;
}
?>

<section class="hero-container">
	<?php if ( $term ) : ?>
		<nav class="taxonomy-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'aptox' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'aptox' ); ?></a>
			<span aria-hidden="true">›</span>
			<a href="<?php echo esc_url( function_exists( 'aptox_get_loja_archive_url' ) ? aptox_get_loja_archive_url() : home_url( '/loja/' ) ); ?>">
				<?php esc_html_e( 'Loja', 'aptox' ); ?>
			</a>
			<span aria-hidden="true">›</span>
			<span><?php echo esc_html( $term->name ); ?></span>
		</nav>

		<h1 class="taxonomy-title">
			<?php echo esc_html( $term->name ); ?>
		</h1>

		<?php if ( ! empty( $term->description ) ) : ?>
			<div class="taxonomy-description">
				<?php echo wp_kses_post( wpautop( $term->description ) ); ?>
			</div>
		<?php else : ?>
			<p class="taxonomy-description">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: category name */
						__( 'Confira nossa seleção de %s na Loja Aptox. Produtos de afiliadas que amamos para casa, mesa e celebrações.', 'aptox' ),
						$term->name
					)
				);
				?>
			</p>
		<?php endif; ?>
	<?php endif; ?>
</section>

<main class="container loja-page">
	<?php
	get_template_part(
		'components/grid-loja/grid-loja',
		null,
		array(
			'use_main_query' => true,
		)
	);
	?>
</main>

<?php get_template_part( 'components/filter-nav/filter-nav-loja' ); ?>

<?php get_footer(); ?>
