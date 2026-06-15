<?php
/**
 * Global footer template.
 */

$social_links = array(
	array(
		'url'   => 'https://www.instagram.com/aptox/',
		'icon'  => 'ico-instagram.svg',
		'label' => 'Instagram',
	),
	array(
		'url'   => 'https://br.pinterest.com/aptoxblog/',
		'icon'  => 'ico-pinterest.svg',
		'label' => 'Pinterest',
	),
	array(
		'url'   => 'https://www.youtube.com/@aptoxblog',
		'icon'  => 'ico-youtube.svg',
		'label' => 'YouTube',
	),
	array(
		'url'   => 'https://www.tiktok.com/@aptoxblog',
		'icon'  => 'ico-tiktok.svg',
		'label' => 'TikTok',
	),
	array(
		'url'   => 'https://www.facebook.com/aptox',
		'icon'  => 'ico-facebook.svg',
		'label' => 'Facebook',
	),
);
?>

	</div>

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
						src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/' . $social_link['icon'] ); ?>"
						alt="<?php echo esc_attr( $social_link['label'] ); ?>"
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
				<strong>QRNO</strong>
			</p>
		</div>
	</div>
</footer>

<?php get_template_part( 'components/edit-post/edit-post' ); ?>

<?php wp_footer(); ?>
</body>
</html>
