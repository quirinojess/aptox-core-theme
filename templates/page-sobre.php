<?php
/**
 * Template Name: Manifesto
 */

get_header();
?>

<main class="page-sobre-page">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'components/page-sobre/page-sobre' );
	endwhile;
	?>

	<section class="page-sobre-pillars" aria-labelledby="page-sobre-pillars-title">
		<div class="page-sobre-pillars-layout">
			<div class="page-sobre-pillars-intro">
				<h2 id="page-sobre-pillars-title" class="page-sobre-pillars__title">
					<?php esc_html_e( 'Nossos 3 pilares', 'aptox' ); ?>
				</h2>
			</div>
			<div class="page-sobre-pillars-carousel__controls">
				<button
					type="button"
					class="page-sobre-pillars-nav page-sobre-pillars-nav--prev"
					aria-label="<?php echo esc_attr__( 'Ver pilar anterior', 'aptox' ); ?>"
					disabled
					hidden
				>
					<span class="page-sobre-pillars-nav__icon"><?php echo aptox_chevron_icon( 'left' ); ?></span>
				</button>
				<button
					type="button"
					class="page-sobre-pillars-nav page-sobre-pillars-nav--next"
					aria-label="<?php echo esc_attr__( 'Ver próximo pilar', 'aptox' ); ?>"
					hidden
				>
					<span class="page-sobre-pillars-nav__icon"><?php echo aptox_chevron_icon( 'right' ); ?></span>
				</button>
			</div>
			<div class="page-sobre-pillars-grid">
				<div class="page-sobre-pillars-carousel">
					<?php get_template_part( 'components/info-grid/info-grid' ); ?>
				</div>
			</div>
		</div>
	</section>
	<?php get_template_part( 'components/page-sobre/page-sobre-seasonal' ); ?>
	<?php get_template_part( 'components/page-editorial/page-editorial-benefits' ); ?>
	<?php get_template_part( 'components/page-sobre-timeline/page-sobre-timeline' ); ?>
	<?php get_template_part( 'components/page-sobre-clipping/page-sobre-clipping' ); ?>
</main>

<?php get_footer(); ?>
