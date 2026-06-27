<?php
/**
 * Minify and cache theme CSS/JS assets on the front end.
 *
 * @package Aptox
 */

namespace Aptox\Services;

use Aptox\Helpers\AssetMinifier;

class AssetMinifyService {
	/**
	 * Cache folder name inside uploads.
	 */
	private const CACHE_DIRNAME = 'aptox-minify';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'switch_theme', array( $this, 'clear_cache' ) );

		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'style_loader_src', array( $this, 'filter_style_src' ), 999, 2 );
		add_filter( 'script_loader_src', array( $this, 'filter_script_src' ), 999, 2 );
	}

	/**
	 * Whether asset minification should run.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return false;
		}

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			return false;
		}

		if ( defined( 'APTOX_MINIFY_ASSETS' ) ) {
			return (bool) APTOX_MINIFY_ASSETS;
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return false;
		}

		if ( $this->is_external_minify_active() ) {
			return false;
		}

		return (bool) apply_filters( 'aptox_asset_minify_enabled', true );
	}

	/**
	 * Detect common optimization plugins already minifying assets.
	 *
	 * @return bool
	 */
	private function is_external_minify_active() {
		if ( defined( 'LSCWP_V' ) ) {
			$css_min = (bool) get_option( 'litespeed.conf.optm-css_min', false );
			$js_min  = (bool) get_option( 'litespeed.conf.optm-js_min', false );

			if ( $css_min || $js_min ) {
				return true;
			}
		}

		if ( defined( 'WP_ROCKET_VERSION' ) ) {
			$rocket_options = get_option( 'wp_rocket_settings', array() );

			if (
				( is_array( $rocket_options ) && ! empty( $rocket_options['minify_css'] ) )
				|| ( is_array( $rocket_options ) && ! empty( $rocket_options['minify_js'] ) )
			) {
				return true;
			}
		}

		return (bool) apply_filters( 'aptox_asset_minify_external_active', false );
	}

	/**
	 * @param string $src    Asset URL.
	 * @param string $handle Style handle.
	 * @return string
	 */
	public function filter_style_src( $src, $handle ) {
		unset( $handle );

		return $this->maybe_minify_src( (string) $src, 'css' );
	}

	/**
	 * @param string $src    Asset URL.
	 * @param string $handle Script handle.
	 * @return string
	 */
	public function filter_script_src( $src, $handle ) {
		unset( $handle );

		return $this->maybe_minify_src( (string) $src, 'js' );
	}

	/**
	 * @param string $src  Asset URL.
	 * @param string $type css|js.
	 * @return string
	 */
	private function maybe_minify_src( $src, $type ) {
		if ( '' === $src ) {
			return $src;
		}

		$source_path = $this->resolve_theme_file_path( $src );

		if ( null === $source_path || ! is_readable( $source_path ) ) {
			return $src;
		}

		if ( $this->is_already_minified( $source_path, $type ) ) {
			return $src;
		}

		$cache = $this->get_cached_asset( $source_path, $type );

		if ( null === $cache ) {
			return $src;
		}

		return $this->append_version_query( $cache['url'], $cache['version'] );
	}

	/**
	 * @param string $src Asset URL.
	 * @return string|null
	 */
	private function resolve_theme_file_path( $src ) {
		$theme_uri = trailingslashit( get_template_directory_uri() );
		$theme_dir = wp_normalize_path( get_template_directory() );

		$clean_src = wp_parse_url( $src, PHP_URL_PATH );

		if ( ! is_string( $clean_src ) || '' === $clean_src ) {
			return null;
		}

		$theme_path = wp_parse_url( $theme_uri, PHP_URL_PATH );

		if ( ! is_string( $theme_path ) || '' === $theme_path ) {
			return null;
		}

		$theme_path = trailingslashit( $theme_path );

		if ( 0 !== strpos( $clean_src, $theme_path ) ) {
			return null;
		}

		$relative = ltrim( substr( $clean_src, strlen( $theme_path ) ), '/' );
		$path     = wp_normalize_path( $theme_dir . '/' . $relative );
		$real     = realpath( $path );

		if ( false === $real || 0 !== strpos( $real, $theme_dir ) ) {
			return null;
		}

		return $real;
	}

	/**
	 * @param string $path File path.
	 * @param string $type css|js.
	 * @return bool
	 */
	private function is_already_minified( $path, $type ) {
		$basename = basename( $path );

		return (bool) preg_match( '/\.min\.' . preg_quote( $type, '/' ) . '$/i', $basename );
	}

	/**
	 * @param string $source_path Source file path.
	 * @param string $type        css|js.
	 * @return array{url: string, version: string}|null
	 */
	private function get_cached_asset( $source_path, $type ) {
		$modified = (int) filemtime( $source_path );

		if ( $modified < 1 ) {
			return null;
		}

		$cache_dir = $this->get_cache_dir();

		if ( null === $cache_dir ) {
			return null;
		}

		$cache_name = md5( $source_path ) . '-' . $modified . '.min.' . $type;
		$cache_path = $cache_dir['path'] . '/' . $cache_name;
		$cache_url  = $cache_dir['url'] . '/' . $cache_name;

		if ( is_readable( $cache_path ) && filesize( $cache_path ) > 0 ) {
			return array(
				'url'     => $cache_url,
				'version' => (string) filemtime( $cache_path ),
			);
		}

		$source = file_get_contents( $source_path );

		if ( false === $source || '' === $source ) {
			return null;
		}

		$minified = 'css' === $type
			? AssetMinifier::minify_css( $source )
			: AssetMinifier::minify_js( $source );

		if ( '' === $minified ) {
			return null;
		}

		if ( false === file_put_contents( $cache_path, $minified, LOCK_EX ) ) {
			return null;
		}

		return array(
			'url'     => $cache_url,
			'version' => (string) filemtime( $cache_path ),
		);
	}

	/**
	 * @return array{path: string, url: string}|null
	 */
	private function get_cache_dir() {
		$upload = wp_upload_dir();

		if ( ! empty( $upload['error'] ) ) {
			return null;
		}

		$path = trailingslashit( $upload['basedir'] ) . self::CACHE_DIRNAME;
		$url  = trailingslashit( $upload['baseurl'] ) . self::CACHE_DIRNAME;

		if ( ! wp_mkdir_p( $path ) ) {
			return null;
		}

		$this->maybe_write_cache_guards( $path );

		return array(
			'path' => $path,
			'url'  => $url,
		);
	}

	/**
	 * @param string $path Cache directory.
	 * @return void
	 */
	private function maybe_write_cache_guards( $path ) {
		$index = $path . '/index.php';

		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php\n// Silence is golden.\n" );
		}
	}

	/**
	 * @param string $url     Asset URL.
	 * @param string $version Cache version.
	 * @return string
	 */
	private function append_version_query( $url, $version ) {
		return add_query_arg( 'ver', rawurlencode( $version ), $url );
	}

	/**
	 * Remove generated minified files.
	 *
	 * @return void
	 */
	public function clear_cache() {
		$upload = wp_upload_dir();

		if ( ! empty( $upload['error'] ) ) {
			return;
		}

		$path = trailingslashit( $upload['basedir'] ) . self::CACHE_DIRNAME;

		if ( ! is_dir( $path ) ) {
			return;
		}

		$files = glob( $path . '/*' );

		if ( ! is_array( $files ) ) {
			return;
		}

		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				wp_delete_file( $file );
			}
		}
	}
}
