<?php
/**
 * Component: Page Sobre seasonal living section
 *
 * @context Page Manifesto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section
	class="page-sobre-seasonal"
	aria-labelledby="page-sobre-seasonal-title"
>
	<div class="page-sobre-seasonal-inner">
		<h2 id="page-sobre-seasonal-title" class="page-sobre-seasonal__title">
			<?php esc_html_e( 'Por uma vida sazonal', 'aptox' ); ?>
		</h2>

		<div class="page-sobre-seasonal__prose">
			<p>
				<?php
				esc_html_e(
					'Viver de forma sazonal no Brasil é um ato de intenção, já que o nosso clima muitas vezes ignora o calendário. Mesmo onde as folhas não mudam de cor e o inverno é apenas um detalhe, é possível cultivar as estações no coração. Na prática, isso significa adaptar a rotina ao que cada fase do ano nos pede: no verão, priorizamos o frescor, a leveza e a vida ao ar livre. Quando o outono e o inverno chegam, trazemos o aconchego para dentro de casa, com experiências interiores. Na primavera, renovamos o ambiente com cores e novos ciclos, enquanto o fim de ano é o momento de desacelerar, celebrar e guardar as memórias do que vivemos. Viver sazonalmente não depende da temperatura lá fora, mas da vontade de harmonizar o nosso ritmo interno com a beleza de cada ciclo, vivendo cada momento com propósito.',
					'aptox'
				);
				?>
			</p>
		</div>
	</div>
</section>
