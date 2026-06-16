<?php
/**
 * Global footer template.
 */

$social_links = array(
	array(
		'url'   => 'https://www.instagram.com/aptox/',
		'icon'  => 'ui-social-instagram.svg',
		'label' => 'Instagram',
	),
	array(
		'url'   => 'https://br.pinterest.com/aptoxblog/',
		'icon'  => 'ui-social-pinterest.svg',
		'label' => 'Pinterest',
	),
	array(
		'url'   => 'https://www.youtube.com/@aptoxblog',
		'icon'  => 'ui-social-youtube.svg',
		'label' => 'YouTube',
	),
	array(
		'url'   => 'https://www.tiktok.com/@aptoxblog',
		'icon'  => 'ui-social-tiktok.svg',
		'label' => 'TikTok',
	),
	array(
		'url'   => 'https://www.facebook.com/aptox',
		'icon'  => 'ui-social-facebook.svg',
		'label' => 'Facebook',
	),
);
?>

	</div>

<?php if ( function_exists( 'aptox_show_footer_loja' ) && aptox_show_footer_loja() ) : ?>
	<?php if ( function_exists( 'aptox_is_lazy_home' ) && aptox_is_lazy_home() ) : ?>
<div class="footer-loja-band" aria-hidden="true"></div>
	<?php endif; ?>

	<?php get_template_part( 'components/footer-loja/footer-loja' ); ?>
<?php endif; ?>

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

<footer class="site-footer-simple">
	<div class="footer-simple-inner">
		<nav class="footer-simple-nav">
			<ul>
				<li><a href="/sobre">Sobre</a></li>
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

<?php get_template_part( 'components/edit-post/edit-post' ); ?>

<?php wp_footer(); ?>
</body>
</html>
