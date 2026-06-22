<?php
/**
 * Archive template for Celebracoes.
 */

get_header();
?>

<section class="celebre-cta-section">
	<?php get_template_part( 'components/celebre-cta/celebre-cta' ); ?>
</section>

<section class="home-lazy-section container" data-celebre-section="season-slide">
	<div class="home-lazy-section__placeholder" aria-hidden="true"></div>
	<div class="home-lazy-section__content"></div>
</section>

<section class="home-lazy-section" data-celebre-section="celebre-season">
	<div class="home-lazy-section__placeholder" aria-hidden="true"></div>
	<div class="home-lazy-section__content"></div>
</section>

<section class="home-lazy-section" data-celebre-section="info-grid">
	<div class="home-lazy-section__placeholder" aria-hidden="true"></div>
	<div class="home-lazy-section__content"></div>
</section>

<?php get_template_part( 'components/filter-nav/filter-nav-celebration' ); ?>

<?php get_footer(); ?>
