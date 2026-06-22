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
		add_action( 'init', array( $this, 'handle_season_preference' ), 0 );
		add_action( 'wp_head', array( $this, 'render_season_storage_sync' ), 0 );
		add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_filter( 'show_admin_bar', '__return_false' );
	}

	/**
	 * Persist season preference from query string.
	 *
	 * @return void
	 */
	public function handle_season_preference() {
		\Aptox\Services\SeasonService::bootstrap_season_cookies();
		\Aptox\Services\SeasonService::handle_season_switch();
	}

	/**
	 * Reset browser session state when the natural season changes.
	 *
	 * @return void
	 */
	public function render_season_storage_sync() {
		if ( function_exists( 'aptox_is_links_page' ) && aptox_is_links_page() ) {
			return;
		}

		\Aptox\Services\SeasonService::render_client_storage_sync_script();
	}

	/**
	 * Configure core theme supports.
	 *
	 * @return void
	 */
	public function setup_theme() {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );

		add_image_size( 'aptox-card', 600, 0, false );
		add_image_size( 'aptox-feature', 960, 0, false );
		add_image_size( 'aptox-hero', 1400, 0, false );

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

		register_sidebar(
			array(
				'name'          => __( 'Rodapé publicidade', 'aptox' ),
				'id'            => 'footer-ad-sidebar',
				'description'   => __( 'Bloco sticky no rodapé. Slot fixo: 90px no desktop e 50px no mobile. No widget HTML, use um banner AdSense horizontal nesse tamanho — o anúncio se adapta ao layout, não o contrário.', 'aptox' ),
				'before_widget' => '<div id="%1$s" class="footer-ad__widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<span class="screen-reader-text">',
				'after_title'   => '</span>',
			)
		);

		register_sidebar(
			array(
				'name'          => __( 'Seção YouTube (Home)', 'aptox' ),
				'id'            => 'youtube-home-featured',
				'description'   => __( 'Adicione o widget "Vídeo YouTube em destaque" para escolher o vídeo exibido à esquerda na home.', 'aptox' ),
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '',
				'after_title'   => '',
			)
		);
	}

	/**
	 * Register custom widgets.
	 *
	 * @return void
	 */
	public function register_widgets() {
		register_widget( \Aptox\Widgets\YouTubeFeaturedWidget::class );
	}
}
