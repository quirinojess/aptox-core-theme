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
		add_action( 'init', array( $this, 'handle_season_preference' ), 1 );
		add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
		add_filter( 'show_admin_bar', '__return_false' );
	}

	/**
	 * Persist season preference from query string.
	 *
	 * @return void
	 */
	public function handle_season_preference() {
		\Aptox\Services\SeasonService::handle_season_switch();
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

	/**
	 * Register widget areas.
	 *
	 * @return void
	 */
	public function register_sidebars() {
		register_sidebar(
			array(
				'name'          => __( 'Sidebar de posts', 'aptox' ),
				'id'            => 'post-sidebar',
				'description'   => __( 'Banners publicitários abaixo do autor em posts de Celebre e Decoração.', 'aptox' ),
				'before_widget' => '<div id="%1$s" class="post-side-widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<span class="screen-reader-text">',
				'after_title'   => '</span>',
			)
		);
	}
}
