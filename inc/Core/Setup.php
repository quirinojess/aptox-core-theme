<?php
/**
 * Theme setup hooks.
 *
 * @package Aptox
 */

namespace Aptox\Core;

class Setup {
	/**
	 * Register setup hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'after_setup_theme', array( $this, 'setup_theme' ) );
		add_filter( 'show_admin_bar', '__return_false' );
	}

	/**
	 * Configure core theme supports.
	 *
	 * @return void
	 */
	public function setup_theme() {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );

		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);
	}
}
