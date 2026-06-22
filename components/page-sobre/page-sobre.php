<?php
/**
 * Component: Page Sobre
 *
 * @context Page Sobre
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$photo_url = get_template_directory_uri() . '/assets/img/foto-sobre.jpg';
?>

<section class="page-sobre" aria-labelledby="page-sobre-title">
	<header class="page-sobre-header">
		<h1 id="page-sobre-title" class="page-sobre-title">
			<?php esc_html_e( 'A vida feita com mais amor', 'aptox' ); ?>
		</h1>
	</header>

	<div class="page-sobre-body">
		<div class="page-sobre-inner">
			<figure class="page-sobre-photo">
				<img
					src="<?php echo esc_url( $photo_url ); ?>"
					alt="<?php esc_attr_e( 'Jess', 'aptox' ); ?>"
					loading="eager"
				>
			</figure>

			<div class="page-sobre-prose">
				<p>
					<?php
					esc_html_e(
						'Olá, seja bem vindo! Esse é um espaço onde você vai encontrar uma visão sobre o amor manifesto em pequenas coisas cotidianas. A princípio pode parecer que se trata de um blog de casa e decoração. E de fato é a ideia principal. Mas nas entrelinhas de cada postagem, você vai encontrar um pouco do que eu acredito sobre a vida: que quando fazemos as coisas com mais amor e capricho, tornamos nosso dias mais significativos e a vida passa a ter mais significado.',
						'aptox'
					);
					?>
				</p>

				<p>
					<?php
					esc_html_e(
						'Nesse sentido, esse blog é sobre fazer a vida mais bonita. Não em termos de uma estética em si, mas no sentido emocional, de projetar cuidado e carinho naquilo que é o central: o nosso lar, nossas refeições e datas queridas. O contexto onde a vida começa e termina todos os dias e cria as memórias mais importantes que vão contar a nossa história até o fim de nossas jornadas.',
						'aptox'
					);
					?>
				</p>

				<p>
					<?php
					esc_html_e(
						'Por isso espere que aproveite o conteúdo que com tanto carinho selecionei para você. A maioria do que verá aqui foi criado/produzido por mim ao longo de quase uma década em diferentes fases da minha vida e gostaria que você notasse que o que é feito com amor, não tem prazo de validade e é como um legado que deixamos através do tempo.',
						'aptox'
					);
					?>
				</p>
			</div>
		</div>
	</div>
</section>
