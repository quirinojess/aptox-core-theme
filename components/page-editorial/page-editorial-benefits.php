<?php
/**
 * Component: Page Editorial benefits section
 *
 * @context Page Editorial / Page Sobre
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon_uri      = get_template_directory_uri() . '/assets/icons/';
$icon_sections = $icon_uri . 'ui/sections/';
$icon_casa     = $icon_uri . 'casa/';

$season      = aptox_get_season_context();
$season_slug = ! empty( $season['slug'] ) ? sanitize_title( $season['slug'] ) : '';

$season_icon = function_exists( 'aptox_recipe_season_icon' )
	? aptox_recipe_season_icon( $season_slug )
	: $icon_sections . 'ui-section-receitas-verao.png';

$decor_season_icon = function_exists( 'aptox_decor_season_icon' )
	? aptox_decor_season_icon( $season_slug )
	: $icon_sections . 'ui-section-decoracao.png';

$home_season_icon = function_exists( 'aptox_filter_home_season_icon' )
	? aptox_filter_home_season_icon( $season_slug )
	: $icon_casa . 'sazonal/casa-sazonal-verao.png';

$festivity_season_icon = function_exists( 'aptox_season_festivity_icon' )
	? aptox_season_festivity_icon( $season_slug )
	: '';

if ( '' === $festivity_season_icon ) {
	$festivity_season_icon = function_exists( 'aptox_party_season_icon' )
		? aptox_party_season_icon( $season_slug )
		: $icon_sections . 'ui-section-festas-176.png';
}

$is_closing = ! empty( $args['closing'] );

$editorial_url = function_exists( 'aptox_get_editorial_url' )
	? aptox_get_editorial_url()
	: home_url( '/editorial/' );

$benefits = array(
	array(
		'icon'  => $decor_season_icon,
		'title' => __( 'Renovação constante', 'aptox' ),
		'text'  => __( 'Em vez de buscar mudanças radicais, fazemos pequenas transformações ao longo do ano. A casa muda, a decoração muda, os aromas mudam, os sabores mudam e a rotina ganha novos estímulos. A sensação é de recomeço constante.', 'aptox' ),
	),
	array(
		'icon'  => $home_season_icon,
		'title' => __( 'Um guarda-roupa que faz sentido', 'aptox' ),
		'text'  => __( 'Em vez de manter tudo visível o tempo inteiro, damos protagonismo às peças adequadas para cada estação. Guardar os vestidos leves para receber os tricôs no inverno, reorganizar acessórios ou redescobrir aquela jaqueta esquecida faz com que o próprio armário pareça novo várias vezes ao longo do ano. Consumimos com mais consciência e valorizamos aquilo que já temos.', 'aptox' ),
	),
	array(
		'icon'  => $festivity_season_icon,
		'title' => __( 'Dias mais leves e divertidos', 'aptox' ),
		'text'  => __( 'Quando criamos experiências ao longo do ano, deixamos de esperar apenas férias ou grandes datas para viver algo especial. Cada estação traz uma nova lista de filmes, receitas, passeios, flores, músicas, projetos e pequenas tradições. Sempre existe algo novo para experimentar. A rotina deixa de ser repetitiva e passa a acompanhar o movimento natural do tempo.', 'aptox' ),
	),
	array(
		'icon'  => $season_icon,
		'title' => __( 'Uma mesa que celebra o tempo', 'aptox' ),
		'text'  => __( 'Cada estação traz ingredientes, receitas e tradições que esperamos reviver. O primeiro chocolate quente do inverno. A torta de maçã do outono. As frutas frescas do verão. Os almoços floridos da primavera. Esses sabores deixam de ser apenas comida e passam a marcar momentos do ano.', 'aptox' ),
	),
);

$section_class = 'page-editorial-section page-editorial-section--light';

if ( $is_closing ) {
	$section_class .= ' page-editorial-section--closing';
}
?>

<div class="<?php echo esc_attr( $section_class ); ?>">
	<div class="page-editorial-inner">
		<header class="page-editorial-section-heading page-editorial-section-heading--benefits" id="page-editorial-beneficios">
			<h2 class="page-editorial-section-heading__title">
				<?php esc_html_e( 'Os benefícios de viver uma vida sazonal', 'aptox' ); ?>
			</h2>
		</header>

		<div class="page-editorial-benefits">
			<?php foreach ( $benefits as $benefit ) : ?>
				<article class="page-editorial-benefit<?php echo ! empty( $benefit['wide'] ) ? ' page-editorial-benefit--wide' : ''; ?>">
					<figure class="page-editorial-benefit__icon" aria-hidden="true">
						<img
							src="<?php echo esc_url( $benefit['icon'] ); ?>"
							alt=""
							loading="lazy"
							decoding="async"
						>
					</figure>
					<div class="page-editorial-benefit__content">
						<h3 class="page-editorial-benefit__title">
							<?php echo esc_html( $benefit['title'] ); ?>
						</h3>
						<p class="page-editorial-benefit__text">
							<?php echo esc_html( $benefit['text'] ); ?>
						</p>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<div class="page-editorial-benefits-cta">
			<a
				class="cta-season-btn"
				href="<?php echo esc_url( $editorial_url ); ?>"
				target="_blank"
				rel="noopener noreferrer"
			>
				<?php esc_html_e( 'leia o editorial dessa estação', 'aptox' ); ?>
			</a>
		</div>

		<h2 class="page-editorial-benefits-history-title">
			<?php echo esc_html( aptox_hand_text( __( 'A história do blog', 'aptox' ), false ) ); ?>
		</h2>
	</div>
</div>
