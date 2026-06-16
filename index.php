<?php
/**
 * Main index template.
 */

get_header();
?>

<main>
	<section class="index-cta-section">
		<?php get_template_part( 'components/index-cta/index-cta' ); ?>
	</section>

	<section class="home-lazy-section" data-home-section="info-grid">
		<div class="home-lazy-section__placeholder" aria-hidden="true"></div>
		<div class="home-lazy-section__content"></div>
	</section>

	<section class="home-lazy-section" data-home-section="cta-season">
		<div class="home-lazy-section__placeholder" aria-hidden="true"></div>
		<div class="home-lazy-section__content"></div>
	</section>

	<section class="home-lazy-section container-lg" data-home-section="grid-recipe">
		<div class="home-lazy-section__placeholder" aria-hidden="true"></div>
		<div class="home-lazy-section__content"></div>
	</section>

	<section class="home-lazy-section container" data-home-section="season-slide">
		<div class="home-lazy-section__placeholder" aria-hidden="true"></div>
		<div class="home-lazy-section__content"></div>
	</section>

	<section class="home-lazy-section" data-home-section="grid-festivity">
		<div class="home-lazy-section__placeholder" aria-hidden="true"></div>
		<div class="home-lazy-section__content"></div>
	</section>

	<section class="home-lazy-section container-lg" data-home-section="youtube-feed">
		<div class="home-lazy-section__placeholder" aria-hidden="true"></div>
		<div class="home-lazy-section__content"></div>
	</section>
</main>

<?php get_footer(); ?>
