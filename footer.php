<?php
/**
 * Global footer template.
 */
?>

	</div>

<section class="site-footer-midia">
	<ul>
		<li>
			<h3> acompanhe </h3>
		</li>

		<li>
			<a href="https://br.pinterest.com/aptoxblog/" target="_blank" rel="noopener">
				<img
					src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/ico-pinterest.svg' ); ?>"
					alt="Pinterest"
				/>
				Pinterest
			</a>
		</li>

		<li>
			<a href="https://www.instagram.com/aptox/" target="_blank" rel="noopener">
				<img
					src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/ico-instagram.svg' ); ?>"
					alt="Instagram"
				/>
				Instagram
			</a>
		</li>

		<li>
			<a href="https://www.tiktok.com/@aptoxblog" target="_blank" rel="noopener">
				<img
					src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/ico-tiktok.svg' ); ?>"
					alt="TikTok"
				/>
				TikTok
			</a>
		</li>

		<li>
			<a href="https://www.facebook.com/aptox" target="_blank" rel="noopener">
				<img
					src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/ico-facebook.svg' ); ?>"
					alt="Facebook"
				/>
				Facebook
			</a>
		</li>
	</ul>
</section>

<footer class="site-footer-simple">
	<div class="footer-simple-inner">
		<nav class="footer-simple-nav">
			<ul>
				<li><a href="/sobre">Sobre</a></li>
				<li><a href="/na-midia">Onde aparecemos</a></li>
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

<?php wp_footer(); ?>
</body>
</html>
