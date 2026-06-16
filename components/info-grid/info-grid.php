<?php
/**
 * Component: Info Grid
 *
 * Three-column grid with icon, display title and base text.
 *
 * @context index
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon_ui = get_template_directory_uri() . '/assets/icons/ui/sections/';

$items = array(
	array(
		'icon'  => $icon_ui . 'ui-section-receitas-176.png',
		'title' => 'Cozinhar é',
		'text'  => 'Um ato de amor e cuidado consigo e com o próximo. Por isso fazemos com atenção e presença para garantir o respeito com o alimento que nos nutre e a melhor utilização dos recursos que a natureza nos dá.',
	),
	array(
		'icon'  => $icon_ui . 'ui-section-decoracao-176.png',
		'title' => 'Decorar é',
		'text'  => 'Uma forma de cultivar valor nas coisas que conquistamos. Por isso cuidamos com carinho e sempre buscando melhorias para fazer crescer e multiplicar aquilo que conquistamos em nossas vidas.',
	),
	array(
		'icon'  => $icon_ui . 'ui-section-festas-176.png',
		'title' => 'Celebrar é',
		'text'  => 'O meio pelo qual expressamos gratidão por estarmos vivos. Por isso fazemos sempre que possível, exaltando as culturas e honrando nossos valores, tornando datas especiais em momentos de fazer valer os nossos dias.',
	),
);
?>

<section
	class="info-grid"
	aria-label="<?php esc_attr_e( 'Informações', 'aptox' ); ?>"
>
	<div
		class="info-grid-inner"
		aria-label="<?php esc_attr_e( 'Destaques', 'aptox' ); ?>"
	>

		<?php foreach ( $items as $index => $item ) : ?>
			<article class="info-grid-item">

				<figure class="info-grid-icon" aria-hidden="true">
					<img
						src="<?php echo esc_url( $item['icon'] ); ?>"
						alt=""
						loading="lazy"
						width="176"
						height="176"
						decoding="async"
					>
				</figure>

				<div class="info-grid-content">
					<h2 class="info-grid-title">
						<?php echo esc_html( $item['title'] ); ?>
					</h2>

					<p class="info-grid-text">
						<?php echo esc_html( $item['text'] ); ?>
					</p>
				</div>

			</article>
		<?php endforeach; ?>

	</div>
</section>
