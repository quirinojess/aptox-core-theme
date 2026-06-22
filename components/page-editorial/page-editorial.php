<?php
/**
 * Component: Page Editorial
 *
 * @context Page Editorial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon_uri      = get_template_directory_uri() . '/assets/icons/';
$icon_sections = $icon_uri . 'ui/sections/';
$icon_casa     = $icon_uri . 'casa/';

$newsletter  = aptox_get_season_newsletter_data();
$season       = aptox_get_season_context();
$season_slug  = ! empty( $season['slug'] ) ? sanitize_title( $season['slug'] ) : '';
$season_label = ! empty( $season['label'] ) ? $season['label'] : aptox_get_season_label( $season_slug );
$hero_image  = ! empty( $newsletter['image'] ) ? $newsletter['image'] : '';
$hero_alt     = ! empty( $newsletter['title'] ) ? $newsletter['title'] : sprintf(
	/* translators: %s: season name */
	__( 'Editorial de %s', 'aptox' ),
	$season_label
);
$season_icon = function_exists( 'aptox_filter_home_season_icon' )
	? aptox_filter_home_season_icon()
	: $icon_casa . 'sazonal/casa-sazonal-verao.png';
$recipe_season_icon = function_exists( 'aptox_recipe_season_icon' )
	? aptox_recipe_season_icon( $season_slug )
	: '';
$rituals       = aptox_get_season_editorial_rituals();
$rituals_intro = aptox_get_season_editorial_rituals_intro();
$page_id       = get_queried_object_id();
$cover_image   = aptox_get_editorial_cover_image( $page_id, $season_slug );

$cycles = array(
	__( 'Há momentos de expansão, de receber pessoas, iniciar projetos e experimentar o novo.', 'aptox' ),
	__( 'Há momentos de recolhimento, descanso, organização e silêncio.', 'aptox' ),
	__( 'Há tempo para celebrar e tempo para desacelerar.', 'aptox' ),
	__( 'Tempo para cultivar e tempo para colher.', 'aptox' ),
);

/**
 * Render an editorial section heading with icon.
 *
 * @param string $icon      Icon URL.
 * @param string $title     Section title.
 * @param string $id        Optional heading id.
 * @param string $modifier  Optional BEM modifier class.
 * @return void
 */
$render_section_heading = static function ( $icon, $title, $id = '', $modifier = '' ) {
	$id_attr       = '' !== $id ? ' id="' . esc_attr( $id ) . '"' : '';
	$heading_class = 'page-editorial-section-heading';

	if ( '' !== $modifier ) {
		$heading_class .= ' ' . sanitize_html_class( $modifier );
	}
	?>
	<header class="<?php echo esc_attr( $heading_class ); ?>"<?php echo $id_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="page-editorial-section-heading__inner">
			<figure class="page-editorial-section-heading__icon" aria-hidden="true">
				<img src="<?php echo esc_url( $icon ); ?>" alt="" loading="lazy" decoding="async">
			</figure>
			<h2 class="page-editorial-section-heading__title">
				<?php echo esc_html( $title ); ?>
			</h2>
		</div>
	</header>
	<?php
};
?>

<section class="page-editorial" aria-labelledby="page-editorial-title">
	<div class="page-editorial-hero">
		<div class="page-editorial-hero-inner<?php echo '' === $hero_image ? ' page-editorial-hero-inner--no-image' : ''; ?>">
			<?php if ( '' !== $hero_image ) : ?>
				<figure class="page-editorial-hero-photo">
					<img
						src="<?php echo esc_url( $hero_image ); ?>"
						alt="<?php echo esc_attr( $hero_alt ); ?>"
						loading="eager"
						decoding="async"
					>
				</figure>
			<?php endif; ?>

			<div class="page-editorial-hero-content">
				<h1 id="page-editorial-title" class="page-editorial-title"><?php esc_html_e( 'Editorial de', 'aptox' ); ?> <span class="page-editorial-title-hand"><?php echo esc_html( $season_label ); ?></span></h1>

				<div class="page-editorial-prose">
					<p>
						<?php
						esc_html_e(
							'Vivemos em um país de dimensões continentais, onde o inverno pode ser rigoroso em algumas regiões e praticamente inexistente em outras. Há lugares onde as folhas nunca mudam de cor, a neve nunca cai e a diferença entre as estações é quase imperceptível.',
							'aptox'
						);
						?>
					</p>
					<p>
						<?php
						esc_html_e(
							'Ainda assim, acreditamos que viver as estações vai muito além do clima: é uma forma de viver o tempo com mais intenção. A natureza nos lembra de que nada permanece igual por muito tempo — ela floresce, amadurece, recolhe-se e recomeça. E nós também podemos viver assim.',
							'aptox'
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="page-editorial-section page-editorial-section--beige">
		<div class="page-editorial-inner">
			<header class="page-editorial-lead-wrap">
				<?php if ( '' !== $recipe_season_icon ) : ?>
					<figure class="page-editorial-lead__icon" aria-hidden="true">
						<img
							src="<?php echo esc_url( $recipe_season_icon ); ?>"
							alt=""
							loading="lazy"
							decoding="async"
						>
					</figure>
				<?php endif; ?>
				<h2 class="page-editorial-lead">
					<?php esc_html_e( 'Uma vida sazonal é uma vida que aceita os ciclos.', 'aptox' ); ?>
				</h2>
			</header>

			<div class="page-editorial-cycles">
				<?php foreach ( $cycles as $cycle ) : ?>
					<article class="page-editorial-cycle">
						<p class="page-editorial-cycle__text">
							<?php echo esc_html( $cycle ); ?>
						</p>
					</article>
				<?php endforeach; ?>
			</div>

			<div class="page-editorial-prose">
				<p>
					<?php
					esc_html_e(
						'Quando passamos a perceber esses ritmos, deixamos de viver todos os dias da mesma forma e começamos a construir um cotidiano mais consciente.',
						'aptox'
					);
					?>
				</p>
			</div>
		</div>
	</div>

	<?php if ( is_array( $cover_image ) && ! empty( $cover_image['url'] ) ) : ?>
		<div class="page-editorial-cover-band">
			<figure class="page-editorial-cover">
				<img
					src="<?php echo esc_url( $cover_image['url'] ); ?>"
					alt="<?php echo esc_attr( $cover_image['alt'] ); ?>"
					<?php if ( ! empty( $cover_image['width'] ) ) : ?>
						width="<?php echo esc_attr( (string) $cover_image['width'] ); ?>"
					<?php endif; ?>
					<?php if ( ! empty( $cover_image['height'] ) ) : ?>
						height="<?php echo esc_attr( (string) $cover_image['height'] ); ?>"
					<?php endif; ?>
					loading="lazy"
					decoding="async"
				>
			</figure>
		</div>
	<?php endif; ?>

	<div class="page-editorial-section page-editorial-section--beige">
		<div class="page-editorial-inner">
			<?php
			$render_section_heading(
				$season_icon,
				__( 'Os pequenos rituais que transformam o cotidiano', 'aptox' ),
				'page-editorial-rituais',
				'page-editorial-section-heading--season'
			);
			?>

			<div class="page-editorial-prose">
				<p>
					<?php echo esc_html( $rituals_intro ); ?>
				</p>
			</div>

			<ul class="page-editorial-list">
				<?php foreach ( $rituals as $ritual ) : ?>
					<li><?php echo esc_html( $ritual ); ?></li>
				<?php endforeach; ?>
			</ul>

			<div class="page-editorial-prose">
				<p>
					<?php
					esc_html_e(
						'Esses rituais marcam a passagem do tempo e criam memórias que voltam todos os anos, carregadas de afeto.',
						'aptox'
					);
					?>
				</p>
			</div>
		</div>
	</div>

	<?php
	get_template_part(
		'components/page-editorial/page-editorial-celebre',
		null,
		array(
			'season_slug' => $season_slug,
		)
	);
	?>
</section>
