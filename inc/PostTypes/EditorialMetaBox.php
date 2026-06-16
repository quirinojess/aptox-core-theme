<?php
/**
 * Admin meta box for editorial seasonal cover images.
 *
 * @package Aptox
 */

namespace Aptox\PostTypes;

use Aptox\Services\SeasonService;

class EditorialMetaBox {
	/**
	 * Meta key prefix for seasonal cover attachment IDs.
	 *
	 * @var string
	 */
	public const META_PREFIX = '_aptox_editorial_cover_';

	/**
	 * Legacy meta key prefix kept for backward compatibility.
	 *
	 * @var string
	 */
	public const LEGACY_META_PREFIX = '_aptox_manifesto_cover_';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post_page', array( $this, 'save_meta' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue media library assets on editorial page edit screen.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || 'page' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();
	}

	/**
	 * Register the seasonal cover meta box.
	 *
	 * @return void
	 */
	public function register_meta_box() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( $screen && 'page' !== $screen->post_type ) {
			return;
		}

		add_meta_box(
			'aptox_editorial_covers',
			__( 'Covers sazonais do editorial', 'aptox' ),
			array( $this, 'render_meta_box' ),
			'page',
			'normal',
			'high'
		);
	}

	/**
	 * Build meta key for a season slug.
	 *
	 * @param string $season_slug Season slug.
	 * @return string
	 */
	public static function meta_key_for_season( $season_slug ) {
		return self::META_PREFIX . sanitize_key( (string) $season_slug );
	}

	/**
	 * Resolve a seasonal cover attachment ID from current or legacy meta keys.
	 *
	 * @param int    $page_id     Page ID.
	 * @param string $season_slug Season slug.
	 * @return int
	 */
	public static function get_cover_attachment_id( $page_id, $season_slug ) {
		$page_id = absint( $page_id );

		if ( $page_id <= 0 ) {
			return 0;
		}

		$season_slug   = sanitize_key( (string) $season_slug );
		$attachment_id = (int) get_post_meta( $page_id, self::meta_key_for_season( $season_slug ), true );

		if ( $attachment_id > 0 ) {
			return $attachment_id;
		}

		return (int) get_post_meta( $page_id, self::LEGACY_META_PREFIX . $season_slug, true );
	}

	/**
	 * Render seasonal cover fields.
	 *
	 * @param \WP_Post $post Current post.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'aptox_editorial_covers', 'aptox_editorial_covers_nonce' );

		$template = get_page_template_slug( $post );

		if ( 'templates/page-editorial.php' !== $template && 'templates/page-manifesto.php' !== $template ) {
			echo '<p class="description">';
			esc_html_e( 'Selecione o template "Editorial" para configurar as covers sazonais desta página.', 'aptox' );
			echo '</p>';
		}
		?>
		<p class="description">
			<?php esc_html_e( 'Adicione uma imagem de cover para cada estação. A cover exibida no site muda automaticamente conforme a estação ativa.', 'aptox' ); ?>
		</p>

		<div class="aptox-editorial-covers">
			<?php foreach ( SeasonService::get_available_season_slugs() as $season_slug ) : ?>
				<?php
				$field_id      = 'aptox_editorial_cover_' . $season_slug;
				$attachment_id = self::get_cover_attachment_id( $post->ID, $season_slug );
				$image_url     = $attachment_id > 0 ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
				$season_label  = SeasonService::get_season_label( $season_slug );
				?>
				<div class="aptox-editorial-cover-field" data-season="<?php echo esc_attr( $season_slug ); ?>">
					<p>
						<strong><?php echo esc_html( $season_label ); ?></strong>
					</p>

					<div class="aptox-editorial-cover-field__preview">
						<?php if ( $image_url ) : ?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="">
						<?php endif; ?>
					</div>

					<input
						type="hidden"
						id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( $field_id ); ?>"
						value="<?php echo esc_attr( (string) $attachment_id ); ?>"
					>

					<p>
						<button type="button" class="button aptox-editorial-cover-select" data-target="<?php echo esc_attr( $field_id ); ?>">
							<?php esc_html_e( 'Selecionar imagem', 'aptox' ); ?>
						</button>
						<button type="button" class="button-link-delete aptox-editorial-cover-remove" data-target="<?php echo esc_attr( $field_id ); ?>"<?php echo $attachment_id > 0 ? '' : ' hidden'; ?>>
							<?php esc_html_e( 'Remover', 'aptox' ); ?>
						</button>
					</p>
				</div>
			<?php endforeach; ?>
		</div>

		<style>
			.aptox-editorial-covers {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
				gap: 16px;
				margin-top: 12px;
			}

			.aptox-editorial-cover-field {
				padding: 12px;
				border: 1px solid #dcdcde;
				background: #fff;
			}

			.aptox-editorial-cover-field__preview {
				display: flex;
				align-items: center;
				justify-content: center;
				min-height: 120px;
				margin-bottom: 10px;
				background: #f6f7f7;
			}

			.aptox-editorial-cover-field__preview img {
				display: block;
				max-width: 100%;
				height: auto;
			}
		</style>

		<script>
			( function ( $ ) {
				$( function () {
					var frame;

					function getFieldElements( targetId ) {
						return {
							input: $( '#' + targetId ),
							field: $( '#' + targetId ).closest( '.aptox-editorial-cover-field' ),
							preview: $( '#' + targetId ).closest( '.aptox-editorial-cover-field' ).find( '.aptox-editorial-cover-field__preview' ),
							remove: $( '.aptox-editorial-cover-remove[data-target="' + targetId + '"]' )
						};
					}

					$( document ).on( 'click', '.aptox-editorial-cover-select', function ( event ) {
						event.preventDefault();

						var targetId = $( this ).data( 'target' );
						var elements = getFieldElements( targetId );

						if ( frame ) {
							frame.close();
						}

						frame = wp.media( {
							title: '<?php echo esc_js( __( 'Selecionar cover', 'aptox' ) ); ?>',
							button: {
								text: '<?php echo esc_js( __( 'Usar imagem', 'aptox' ) ); ?>'
							},
							multiple: false,
							library: {
								type: 'image'
							}
						} );

						frame.on( 'select', function () {
							var attachment = frame.state().get( 'selection' ).first().toJSON();
							var previewUrl = attachment.sizes && attachment.sizes.medium
								? attachment.sizes.medium.url
								: attachment.url;

							elements.input.val( attachment.id );
							elements.preview.html( '<img src="' + previewUrl + '" alt="">' );
							elements.remove.prop( 'hidden', false );
						} );

						frame.open();
					} );

					$( document ).on( 'click', '.aptox-editorial-cover-remove', function ( event ) {
						event.preventDefault();

						var targetId = $( this ).data( 'target' );
						var elements = getFieldElements( targetId );

						elements.input.val( '' );
						elements.preview.empty();
						elements.remove.prop( 'hidden', true );
					} );
				} );
			} )( jQuery );
		</script>
		<?php
	}

	/**
	 * Persist seasonal cover attachment IDs.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function save_meta( $post_id, $post ) {
		unset( $post );

		if (
			! isset( $_POST['aptox_editorial_covers_nonce'] )
			|| ! wp_verify_nonce(
				sanitize_text_field( wp_unslash( (string) $_POST['aptox_editorial_covers_nonce'] ) ),
				'aptox_editorial_covers'
			)
		) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( SeasonService::get_available_season_slugs() as $season_slug ) {
			$field_name = 'aptox_editorial_cover_' . $season_slug;
			$meta_key   = self::meta_key_for_season( $season_slug );
			$legacy_key = self::LEGACY_META_PREFIX . $season_slug;

			if ( ! isset( $_POST[ $field_name ] ) ) {
				continue;
			}

			$attachment_id = absint( wp_unslash( (string) $_POST[ $field_name ] ) );

			if ( $attachment_id > 0 && wp_attachment_is_image( $attachment_id ) ) {
				update_post_meta( $post_id, $meta_key, $attachment_id );
				delete_post_meta( $post_id, $legacy_key );
				continue;
			}

			delete_post_meta( $post_id, $meta_key );
			delete_post_meta( $post_id, $legacy_key );
		}
	}
}
