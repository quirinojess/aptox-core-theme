<?php
/**
 * Global footer template.
 */

$is_links_page = function_exists( 'aptox_is_links_page' ) && aptox_is_links_page();
$social_links  = function_exists( 'aptox_get_social_links' ) ? aptox_get_social_links() : array();

$manifesto_url = function_exists( 'aptox_get_manifesto_url' )
	? aptox_get_manifesto_url()
	: home_url( '/manifesto/' );
?>

	</div>

<?php if ( ! $is_links_page && function_exists( 'aptox_show_footer_loja' ) && aptox_show_footer_loja() ) : ?>
	<?php if ( function_exists( 'aptox_is_lazy_home' ) && aptox_is_lazy_home() ) : ?>
<div class="footer-loja-band" aria-hidden="true"></div>
	<?php endif; ?>

	<?php get_template_part( 'components/footer-loja/footer-loja' ); ?>
<?php endif; ?>

<?php if ( ! $is_links_page ) : ?>
<section
	class="site-footer-midia"
	aria-label="<?php esc_attr_e( 'Redes sociais', 'aptox' ); ?>"
>
	<ul class="site-footer-midia__list">
		<li>
			<h3>acompanhe nas redes sociais</h3>
		</li>

		<?php foreach ( $social_links as $social_link ) : ?>
			<li>
				<a href="<?php echo esc_url( $social_link['url'] ); ?>" target="_blank" rel="noopener">
					<img
						src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/social/' . $social_link['icon'] ); ?>"
						alt=""
						aria-hidden="true"
						width="12"
						height="12"
						decoding="async"
					/>
					<?php echo esc_html( $social_link['label'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
<?php endif; ?>

<?php if ( ! $is_links_page ) : ?>
<footer class="site-footer-simple">
	<div class="footer-simple-inner">
		<nav class="footer-simple-nav">
			<ul>
				<li><a href="<?php echo esc_url( $manifesto_url ); ?>"><?php esc_html_e( 'Manifesto', 'aptox' ); ?></a></li>
				<li><a href="/termos-de-uso">Termos de uso</a></li>
				<li><a href="/contato">Contato</a></li>
			</ul>
		</nav>

		<div class="footer-simple-credits">
			<p>
				Desenvolvido por
				<a href="https://www.qrno.com.br/" target="_blank" rel="noopener noreferrer">
					<strong>QRNO</strong>
				</a>
			</p>
		</div>
	</div>
</footer>
<?php endif; ?>

<?php if ( ! $is_links_page ) : ?>
	<?php get_template_part( 'components/edit-post/edit-post' ); ?>
<?php endif; ?>

<?php if ( ! $is_links_page ) : ?>
	<?php get_template_part( 'components/menu/menu-mob' ); ?>
<?php endif; ?>

<?php if ( ! $is_links_page && function_exists( 'aptox_show_footer_ad' ) && aptox_show_footer_ad() ) : ?>
	<?php get_template_part( 'components/footer-ad/footer-ad' ); ?>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
