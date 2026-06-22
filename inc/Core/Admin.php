<?php
/**
 * WordPress admin branding.
 *
 * @package Aptox
 */

namespace Aptox\Core;

class Admin {
	/**
	 * Admin color scheme slug.
	 *
	 * @var string
	 */
	private const COLOR_SCHEME = 'aptox';

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_init', array( $this, 'register_color_scheme' ) );
		add_filter( 'get_user_option_admin_color', array( $this, 'default_admin_color' ), 10, 2 );
	}

	/**
	 * Register the Aptox admin palette.
	 *
	 * @return void
	 */
	public function register_color_scheme() {
		$path = get_template_directory() . '/assets/css/admin/aptox-admin.css';
		$url  = get_template_directory_uri() . '/assets/css/admin/aptox-admin.css';
		$ver  = file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme()->get( 'Version' );

		wp_admin_css_color(
			self::COLOR_SCHEME,
			__( 'Aptox', 'aptox' ),
			add_query_arg( 'ver', $ver, $url ),
			array( '#F3F1F0', '#050505', '#111111', '#050505' ),
			array(
				'base'    => '#050505',
				'focus'   => '#111111',
				'current' => '#FBFBFB',
			)
		);
	}

	/**
	 * Use the Aptox palette as the default admin color scheme.
	 *
	 * @param string $color   Stored color scheme.
	 * @param int    $user_id User ID.
	 * @return string
	 */
	public function default_admin_color( $color, $user_id ) {
		unset( $user_id );

		return self::COLOR_SCHEME;
	}
}
