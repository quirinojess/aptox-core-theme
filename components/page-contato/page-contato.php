<?php
/**
 * Component: Page Contato
 *
 * @context Page Contato
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$contact_email = 'emailaptox@gmail.com';
?>

<section class="page-contato" aria-labelledby="page-contato-title">
	<header class="page-contato-header">
		<h1 id="page-contato-title" class="page-contato-title">
			<?php esc_html_e( 'Contato', 'aptox' ); ?>
		</h1>
	</header>

	<div class="page-contato-body">
		<div class="page-contato-inner">
			<p class="page-contato-intro">
				<?php esc_html_e( 'Para qualquer solicitação, dúvida, ou sugestão, você pode nos escrever no endereço eletrônico:', 'aptox' ); ?>
				<a href="<?php echo esc_url( 'mailto:' . $contact_email ); ?>">
					<?php echo esc_html( $contact_email ); ?>
				</a>
				<?php esc_html_e( 'ou se preferir, preencha o formulário:', 'aptox' ); ?>
			</p>

			<div class="page-contato-form">
				<?php echo do_shortcode( '[forminator_form id="8052"]' ); ?>
			</div>
		</div>
	</div>
</section>
