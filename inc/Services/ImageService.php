<?php
/**
 * Image optimization: WebP conversion and compression.
 *
 * @package Aptox
 */

namespace Aptox\Services;

class ImageService {
	/**
	 * JPEG compression quality (visually lossless for web).
	 */
	private const JPEG_QUALITY = 85;

	/**
	 * WebP compression quality.
	 */
	private const WEBP_QUALITY = 85;

	/**
	 * Post meta key for the full-size WebP companion file.
	 */
	private const WEBP_META_KEY = '_aptox_webp_file';

	/**
	 * Guard against recursive attachment URL filters.
	 *
	 * @var array<int, bool>
	 */
	private static $resolving_attachment_url = array();

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		if ( ! self::supports_webp() ) {
			add_action( 'admin_notices', array( $this, 'render_webp_unavailable_notice' ) );
			return;
		}

		add_filter( 'image_editor_output_format', array( $this, 'use_webp_for_subsizes' ) );
		add_filter( 'jpeg_quality', array( $this, 'set_jpeg_quality' ) );
		add_filter( 'wp_editor_set_quality', array( $this, 'set_editor_quality' ), 10, 2 );
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'ensure_webp_companion' ), 20, 2 );
		add_filter( 'wp_get_attachment_url', array( $this, 'prefer_webp_attachment_url' ), 10, 2 );
		add_filter( 'wp_get_attachment_image_src', array( $this, 'prefer_webp_image_src' ), 10, 4 );
		add_filter( 'wp_calculate_image_srcset', array( $this, 'prefer_webp_image_srcset' ), 10, 5 );
		add_filter( 'wp_content_img_tag', array( $this, 'prefer_webp_in_content_image' ), 16, 3 );
		add_filter( 'the_content', array( $this, 'prefer_webp_in_content_links' ), 31 );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
			add_action( 'wp_ajax_aptox_convert_images_webp', array( $this, 'ajax_convert_batch' ) );
		}
	}

	/**
	 * Whether the server can create WebP images.
	 *
	 * @return bool
	 */
	public static function supports_webp() {
		if ( ! function_exists( 'wp_image_editor_supports' ) ) {
			return false;
		}

		return (bool) wp_image_editor_supports(
			array(
				'mime_type' => 'image/webp',
			)
		);
	}

	/**
	 * Generate theme subsizes as WebP instead of JPEG/PNG.
	 *
	 * @param array<string, string> $formats Mime type map.
	 * @return array<string, string>
	 */
	public function use_webp_for_subsizes( $formats ) {
		if ( ! is_array( $formats ) ) {
			$formats = array();
		}

		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';

		return $formats;
	}

	/**
	 * Set JPEG export quality.
	 *
	 * @param int $quality Current quality.
	 * @return int
	 */
	public function set_jpeg_quality( $quality ) {
		return self::JPEG_QUALITY;
	}

	/**
	 * Set editor export quality per mime type.
	 *
	 * @param int    $quality   Current quality.
	 * @param string $mime_type Target mime type.
	 * @return int
	 */
	public function set_editor_quality( $quality, $mime_type = '' ) {
		unset( $quality );

		if ( 'image/webp' === $mime_type ) {
			return self::WEBP_QUALITY;
		}

		if ( in_array( $mime_type, array( 'image/jpeg', 'image/jpg' ), true ) ) {
			return self::JPEG_QUALITY;
		}

		return self::JPEG_QUALITY;
	}

	/**
	 * Create a WebP companion for the full-size upload.
	 *
	 * @param array<string, mixed> $metadata      Attachment metadata.
	 * @param int                  $attachment_id Attachment ID.
	 * @return array<string, mixed>
	 */
	public function ensure_webp_companion( $metadata, $attachment_id ) {
		if ( ! is_array( $metadata ) ) {
			return $metadata;
		}

		$this->create_webp_companion( (int) $attachment_id );

		return $metadata;
	}

	/**
	 * Build or refresh the full-size WebP file for an attachment.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string|false WebP path on success.
	 */
	public function create_webp_companion( $attachment_id ) {
		$attachment_id = (int) $attachment_id;

		if ( $attachment_id <= 0 ) {
			return false;
		}

		$mime_type = (string) get_post_mime_type( $attachment_id );

		if ( ! in_array( $mime_type, array( 'image/jpeg', 'image/png' ), true ) ) {
			return false;
		}

		$source = get_attached_file( $attachment_id );

		if ( ! $source || ! file_exists( $source ) ) {
			return false;
		}

		$webp_path = $this->get_webp_path_for_file( $source );

		if ( ! $webp_path ) {
			return false;
		}

		if ( ! file_exists( $webp_path ) || filemtime( $webp_path ) < filemtime( $source ) ) {
			$editor = wp_get_image_editor( $source );

			if ( is_wp_error( $editor ) ) {
				return false;
			}

			$editor->set_quality( self::WEBP_QUALITY );
			$saved = $editor->save( $webp_path, 'image/webp' );

			if ( is_wp_error( $saved ) ) {
				return false;
			}
		}

		update_post_meta( $attachment_id, self::WEBP_META_KEY, wp_basename( $webp_path ) );

		return $webp_path;
	}

	/**
	 * Regenerate subsizes and WebP companions for an attachment.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	public function regenerate_attachment( $attachment_id ) {
		$attachment_id = (int) $attachment_id;
		$file          = get_attached_file( $attachment_id );

		if ( $attachment_id <= 0 || ! $file || ! file_exists( $file ) ) {
			return false;
		}

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$metadata = wp_generate_attachment_metadata( $attachment_id, $file );

		if ( is_wp_error( $metadata ) || ! is_array( $metadata ) ) {
			return false;
		}

		wp_update_attachment_metadata( $attachment_id, $metadata );
		$this->create_webp_companion( $attachment_id );

		return true;
	}

	/**
	 * Prefer WebP for attachment URLs when a companion exists.
	 *
	 * @param string $url           Attachment URL.
	 * @param int    $attachment_id Attachment ID.
	 * @return string
	 */
	public function prefer_webp_attachment_url( $url, $attachment_id ) {
		$attachment_id = (int) $attachment_id;

		if ( $attachment_id <= 0 || isset( self::$resolving_attachment_url[ $attachment_id ] ) ) {
			return $url;
		}

		self::$resolving_attachment_url[ $attachment_id ] = true;

		$webp_url = $this->get_attachment_webp_url( $attachment_id );

		unset( self::$resolving_attachment_url[ $attachment_id ] );

		return $webp_url ? $webp_url : $url;
	}

	/**
	 * Prefer WebP in rendered attachment image src.
	 *
	 * @param array<int|string>|false $image         Image data.
	 * @param int                     $attachment_id Attachment ID.
	 * @param string|int[]            $size          Requested size.
	 * @param bool                    $icon          Whether icon src is requested.
	 * @return array<int|string>|false
	 */
	public function prefer_webp_image_src( $image, $attachment_id, $size, $icon ) {
		unset( $size, $icon, $attachment_id );

		if ( ! is_array( $image ) || empty( $image[0] ) || preg_match( '/\.webp$/i', $image[0] ) ) {
			return $image;
		}

		$webp_url = $this->replace_url_with_webp_if_exists( $image[0] );

		if ( $webp_url !== $image[0] ) {
			$image[0] = $webp_url;
		}

		return $image;
	}

	/**
	 * Prefer WebP sources inside responsive srcset maps.
	 *
	 * @param array<int, array<string, int|string>>|false $sources       Source list.
	 * @param array<int, int>                             $size_array    Requested size.
	 * @param string                                      $image_src     Image src URL.
	 * @param array<string, mixed>                        $image_meta    Attachment metadata.
	 * @param int                                         $attachment_id Attachment ID.
	 * @return array<int, array<string, int|string>>|false
	 */
	public function prefer_webp_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		unset( $size_array, $image_src, $image_meta, $attachment_id );

		if ( ! is_array( $sources ) ) {
			return $sources;
		}

		foreach ( $sources as $width => $source ) {
			if ( empty( $source['url'] ) || ! is_string( $source['url'] ) ) {
				continue;
			}

			$webp_url = $this->replace_url_with_webp_if_exists( $source['url'] );

			if ( $webp_url !== $source['url'] ) {
				$sources[ $width ]['url'] = $webp_url;
			}
		}

		return $sources;
	}

	/**
	 * Swap content image src and srcset to WebP when companions exist.
	 *
	 * @param string $filtered_image Full img tag.
	 * @param string $context        Context.
	 * @param int    $attachment_id  Attachment ID.
	 * @return string
	 */
	public function prefer_webp_in_content_image( $filtered_image, $context, $attachment_id ) {
		unset( $context, $attachment_id );

		$updated = $filtered_image;

		if ( preg_match( '/\bsrc=(["\'])([^"\']+)\1/i', $updated, $matches ) ) {
			$src      = html_entity_decode( $matches[2], ENT_QUOTES, 'UTF-8' );
			$webp_url = $this->replace_url_with_webp_if_exists( $src );

			if ( $webp_url !== $src ) {
				$replaced = preg_replace(
					'/\bsrc=(["\'])[^"\']+\1/i',
					'src=$1' . esc_url( $webp_url ) . '$1',
					$updated,
					1
				);

				if ( is_string( $replaced ) ) {
					$updated = $replaced;
				}
			}
		}

		if ( preg_match( '/\bsrcset=(["\'])([^"\']+)\1/i', $updated, $matches ) ) {
			$srcset      = html_entity_decode( $matches[2], ENT_QUOTES, 'UTF-8' );
			$webp_srcset = $this->replace_srcset_with_webp( $srcset );

			if ( $webp_srcset !== $srcset ) {
				$replaced = preg_replace(
					'/\bsrcset=(["\'])[^"\']+\1/i',
					'srcset=$1' . esc_attr( $webp_srcset ) . '$1',
					$updated,
					1
				);

				if ( is_string( $replaced ) ) {
					$updated = $replaced;
				}
			}
		}

		return $updated;
	}

	/**
	 * Point linked media files in post content to WebP companions.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function prefer_webp_in_content_links( $content ) {
		if ( ! is_string( $content ) || is_admin() || ! is_singular() || '' === trim( $content ) ) {
			return $content;
		}

		if ( false === stripos( $content, '<a' ) ) {
			return $content;
		}

		$replaced = preg_replace_callback(
			'/<a\b([^>]*?)\bhref=(["\'])([^"\']+)\2/i',
			function ( $matches ) {
				$href = html_entity_decode( $matches[3], ENT_QUOTES, 'UTF-8' );

				if ( ! preg_match( '/\.(jpe?g|png)$/i', $href ) ) {
					return $matches[0];
				}

				$webp_href = $this->replace_url_with_webp_if_exists( $href );

				if ( $webp_href === $href ) {
					return $matches[0];
				}

				return '<a' . $matches[1] . 'href=' . $matches[2] . esc_url( $webp_href ) . $matches[2];
			},
			$content
		);

		return is_string( $replaced ) ? $replaced : $content;
	}

	/**
	 * @param string $url Public uploads URL.
	 * @return string
	 */
	private function replace_url_with_webp_if_exists( $url ) {
		if ( ! is_string( $url ) || '' === $url || preg_match( '/\.webp$/i', $url ) ) {
			return (string) $url;
		}

		$webp_url = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $url );

		if ( ! is_string( $webp_url ) || $webp_url === $url || ! $this->upload_url_exists( $webp_url ) ) {
			return $url;
		}

		return $webp_url;
	}

	/**
	 * @param string $srcset srcset attribute value.
	 * @return string
	 */
	private function replace_srcset_with_webp( $srcset ) {
		$candidates = array_map( 'trim', explode( ',', $srcset ) );
		$converted  = array();

		foreach ( $candidates as $candidate ) {
			if ( '' === $candidate ) {
				continue;
			}

			if ( preg_match( '/^(\S+)\s+(.+)$/', $candidate, $matches ) ) {
				$url        = $this->replace_url_with_webp_if_exists( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ) );
				$converted[] = esc_url( $url ) . ' ' . trim( $matches[2] );
				continue;
			}

			$converted[] = $candidate;
		}

		return implode( ', ', $converted );
	}

	/**
	 * Resolve the public WebP URL for an attachment.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string
	 */
	public function get_attachment_webp_url( $attachment_id ) {
		$attachment_id = (int) $attachment_id;

		if ( $attachment_id <= 0 ) {
			return '';
		}

		$file = get_attached_file( $attachment_id );

		if ( ! $file ) {
			return '';
		}

		$stored     = (string) get_post_meta( $attachment_id, self::WEBP_META_KEY, true );
		$webp_path  = '' !== $stored
			? trailingslashit( dirname( $file ) ) . $stored
			: $this->get_webp_path_for_file( $file );

		if ( ! $webp_path || ! file_exists( $webp_path ) ) {
			return '';
		}

		$source_url = $this->build_upload_url_from_path( $file );

		if ( ! $source_url ) {
			return '';
		}

		$webp_name = wp_basename( $webp_path );

		return trailingslashit( dirname( $source_url ) ) . $webp_name;
	}

	/**
	 * Build a public uploads URL from an absolute file path.
	 *
	 * @param string $file Absolute attachment file path.
	 * @return string
	 */
	private function build_upload_url_from_path( $file ) {
		$upload_dir = wp_get_upload_dir();

		if ( empty( $upload_dir['baseurl'] ) || empty( $upload_dir['basedir'] ) || ! $file ) {
			return '';
		}

		$basedir = wp_normalize_path( $upload_dir['basedir'] );
		$path    = wp_normalize_path( $file );

		if ( 0 !== strpos( $path, trailingslashit( $basedir ) ) ) {
			return '';
		}

		$relative = ltrim( substr( $path, strlen( $basedir ) ), '/' );

		return trailingslashit( $upload_dir['baseurl'] ) . $relative;
	}

	/**
	 * Build a WebP path from a JPEG/PNG file path.
	 *
	 * @param string $file Source file path.
	 * @return string
	 */
	private function get_webp_path_for_file( $file ) {
		$webp_path = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file );

		return is_string( $webp_path ) ? $webp_path : '';
	}

	/**
	 * Check whether a file exists for a public uploads URL.
	 *
	 * @param string $url Public uploads URL.
	 * @return bool
	 */
	private function upload_url_exists( $url ) {
		$upload = wp_get_upload_dir();

		if ( empty( $upload['baseurl'] ) || empty( $upload['basedir'] ) ) {
			return false;
		}

		if ( 0 !== strpos( $url, $upload['baseurl'] ) ) {
			return false;
		}

		$path = str_replace( $upload['baseurl'], $upload['basedir'], $url );

		return file_exists( $path );
	}

	/**
	 * Register bulk conversion page under Tools.
	 *
	 * @return void
	 */
	public function register_admin_page() {
		add_management_page(
			__( 'Otimizar imagens', 'aptox' ),
			__( 'Otimizar imagens', 'aptox' ),
			'manage_options',
			'aptox-image-optimizer',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render bulk conversion admin page.
	 *
	 * @return void
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$total = $this->count_convertible_attachments();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Otimizar imagens', 'aptox' ); ?></h1>
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of images */
						__( 'Converte JPEG e PNG da biblioteca para WebP e regenera os tamanhos do tema. %d imagens encontradas.', 'aptox' ),
						$total
					)
				);
				?>
			</p>
			<p><?php esc_html_e( 'Novos uploads já são convertidos automaticamente. Use esta ferramenta uma vez para o acervo existente.', 'aptox' ); ?></p>
			<p>
				<button type="button" class="button button-primary" id="aptox-image-optimizer-start" <?php disabled( $total <= 0 ); ?>>
					<?php esc_html_e( 'Converter biblioteca para WebP', 'aptox' ); ?>
				</button>
			</p>
			<div id="aptox-image-optimizer-progress" hidden>
				<p><strong id="aptox-image-optimizer-status"></strong></p>
				<progress id="aptox-image-optimizer-bar" max="100" value="0" style="width:100%;max-width:480px;"></progress>
			</div>
		</div>
		<script>
		(function () {
			const startButton = document.getElementById('aptox-image-optimizer-start');
			const progressWrap = document.getElementById('aptox-image-optimizer-progress');
			const statusEl = document.getElementById('aptox-image-optimizer-status');
			const barEl = document.getElementById('aptox-image-optimizer-bar');
			const total = <?php echo (int) $total; ?>;
			let offset = 0;
			let processed = 0;

			if (!startButton) {
				return;
			}

			startButton.addEventListener('click', function () {
				startButton.disabled = true;
				progressWrap.hidden = false;
				statusEl.textContent = '<?php echo esc_js( __( 'Iniciando…', 'aptox' ) ); ?>';
				runBatch();
			});

			function runBatch() {
				const formData = new FormData();
				formData.append('action', 'aptox_convert_images_webp');
				formData.append('nonce', '<?php echo esc_js( wp_create_nonce( 'aptox_convert_images_webp' ) ); ?>');
				formData.append('offset', String(offset));

				fetch(ajaxurl, {
					method: 'POST',
					credentials: 'same-origin',
					body: formData
				})
					.then(function (response) { return response.json(); })
					.then(function (payload) {
						if (!payload || !payload.success) {
							throw new Error((payload && payload.data && payload.data.message) || 'Erro ao converter imagens.');
						}

						const data = payload.data;
						processed += data.processed || 0;
						offset = data.next_offset || 0;

						const percent = total > 0 ? Math.min(100, Math.round((processed / total) * 100)) : 100;
						barEl.value = percent;
						statusEl.textContent = processed + ' / ' + total + ' <?php echo esc_js( __( 'imagens processadas', 'aptox' ) ); ?>';

						if (data.done) {
							statusEl.textContent = '<?php echo esc_js( __( 'Concluído. Todas as imagens foram otimizadas.', 'aptox' ) ); ?>';
							return;
						}

						runBatch();
					})
					.catch(function (error) {
						statusEl.textContent = error.message;
						startButton.disabled = false;
					});
			}
		}());
		</script>
		<?php
	}

	/**
	 * AJAX handler for batched library conversion.
	 *
	 * @return void
	 */
	public function ajax_convert_batch() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Permissão negada.', 'aptox' ) ),
				403
			);
		}

		check_ajax_referer( 'aptox_convert_images_webp', 'nonce' );

		$offset = isset( $_POST['offset'] ) ? max( 0, (int) wp_unslash( $_POST['offset'] ) ) : 0;
		$batch  = 10;
		$ids    = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => array( 'image/jpeg', 'image/png' ),
				'post_status'    => 'inherit',
				'posts_per_page' => $batch,
				'offset'         => $offset,
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);

		$processed = 0;

		foreach ( $ids as $attachment_id ) {
			if ( $this->regenerate_attachment( (int) $attachment_id ) ) {
				$processed++;
			}
		}

		$next_offset = $offset + count( $ids );

		wp_send_json_success(
			array(
				'processed'   => $processed,
				'next_offset' => $next_offset,
				'done'        => count( $ids ) < $batch,
			)
		);
	}

	/**
	 * Count JPEG/PNG attachments in the media library.
	 *
	 * @return int
	 */
	private function count_convertible_attachments() {
		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => array( 'image/jpeg', 'image/png' ),
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return (int) $query->found_posts;
	}

	/**
	 * Warn admins when the server cannot generate WebP files.
	 *
	 * @return void
	 */
	public function render_webp_unavailable_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>';
		esc_html_e( 'O tema Aptox não consegue gerar WebP neste servidor. Ative suporte a WebP no GD ou Imagick do PHP.', 'aptox' );
		echo '</p></div>';
	}
}
