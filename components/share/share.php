<?php
/**
 * Component: Share Bar
 * Theme: Aptox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section
	class="share-bar"
	aria-label="<?php esc_attr_e( 'Curtir e compartilhar', 'aptox' ); ?>"
>
	<div class="share-bar-inner">
		<div class="share-like-group">
			<span class="share-label share-label--like">
				<?php esc_html_e( 'Curta', 'aptox' ); ?>
			</span>

			<button
				class="like-btn"
				type="button"
				data-post-id="<?php echo esc_attr( (string) get_the_ID() ); ?>"
				aria-label="<?php esc_attr_e( 'Curtir este conteúdo', 'aptox' ); ?>"
			>
				<img
					class="like-icon"
					src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/actions/ui-action-favorite-outline.svg' ); ?>"
					alt=""
					aria-hidden="true"
				>

				<span class="like-count" aria-live="polite">
					<?php echo (int) get_post_meta( get_the_ID(), '_post_likes', true ); ?>
				</span>
			</button>
		</div>

		<div class="share-icons-group">
			<span class="share-label share-label--share">
				<?php esc_html_e( 'Compartilhe', 'aptox' ); ?>
			</span>

			<nav class="share-icons" aria-label="<?php esc_attr_e( 'Compartilhar este conteúdo', 'aptox' ); ?>">
				<ul>
					<li>
						<a
							href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( get_permalink() ) ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php esc_attr_e( 'Compartilhar no Facebook', 'aptox' ); ?>"
						>
							<img
								src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/social/ui-social-facebook.svg' ); ?>"
								alt=""
								aria-hidden="true"
							>
						</a>
					</li>

					<li>
						<a
							href="<?php echo esc_url( 'https://pinterest.com/pin/create/button/?url=' . rawurlencode( get_permalink() ) . '&media=' . rawurlencode( (string) get_the_post_thumbnail_url() ) . '&description=' . rawurlencode( get_the_title() ) ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php esc_attr_e( 'Salvar no Pinterest', 'aptox' ); ?>"
						>
							<img
								src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/social/ui-social-pinterest.svg' ); ?>"
								alt=""
								aria-hidden="true"
							>
						</a>
					</li>

					<li>
						<a
							href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( get_the_title() . ' - ' . get_permalink() ) ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php esc_attr_e( 'Compartilhar no WhatsApp', 'aptox' ); ?>"
						>
							<img
								src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/social/ui-social-whatsapp.svg' ); ?>"
								alt=""
								aria-hidden="true"
							>
						</a>
					</li>

					<li>
						<button
							type="button"
							class="share-copy-link"
							data-url="<?php echo esc_url( get_permalink() ); ?>"
							aria-label="<?php esc_attr_e( 'Copiar link do post', 'aptox' ); ?>"
						>
							<img
								src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/actions/ui-action-link.svg' ); ?>"
								alt=""
								aria-hidden="true"
							>
						</button>
					</li>
				</ul>
			</nav>
		</div>
	</div>
</section>
