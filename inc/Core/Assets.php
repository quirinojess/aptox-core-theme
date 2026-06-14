<?php
/**
 * Asset registration and enqueue strategy.
 *
 * @package Aptox
 */

namespace Aptox\Core;

class Assets {
	/**
	 * Register asset hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue fonts, styles and scripts.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$this->enqueue_fonts();
		$this->enqueue_styles();
		$this->enqueue_scripts();
	}

	/**
	 * Enqueue Google Fonts.
	 *
	 * @return void
	 */
	private function enqueue_fonts() {
		wp_enqueue_style(
			'aptox-fonts',
			'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300..700;1,300..700&display=swap',
			array(),
			null
		);
	}

	/**
	 * Enqueue styles, preferring built bundle.
	 *
	 * @return void
	 */
	private function enqueue_styles() {
		$build_css_path = get_template_directory() . '/assets/build/main.css';
		if ( file_exists( $build_css_path ) ) {
			wp_enqueue_style(
				'aptox-main',
				get_template_directory_uri() . '/assets/build/main.css',
				array(),
				(string) filemtime( $build_css_path )
			);
		} else {
			wp_enqueue_style(
				'aptox-base',
				get_template_directory_uri() . '/assets/css/global/base.css',
				array(),
				(string) filemtime( get_template_directory() . '/assets/css/global/base.css' )
			);

			wp_enqueue_style(
				'aptox-layout',
				get_template_directory_uri() . '/assets/css/layout.css',
				array( 'aptox-base' ),
				(string) filemtime( get_template_directory() . '/assets/css/layout.css' )
			);

			wp_enqueue_style(
				'aptox-components',
				get_template_directory_uri() . '/assets/css/components.css',
				array( 'aptox-layout' ),
				(string) filemtime( get_template_directory() . '/assets/css/components.css' )
			);
		}

		$this->enqueue_casa_page_styles();
	}

	/**
	 * Enqueue Casa page component styles (not always in stale build bundles).
	 *
	 * @return void
	 */
	private function enqueue_casa_page_styles() {
		if ( ! is_post_type_archive( 'casas' ) && ! is_page_template( 'templates/page-casa.php' ) ) {
			return;
		}

		$deps = file_exists( get_template_directory() . '/assets/build/main.css' )
			? array( 'aptox-main' )
			: array( 'aptox-components' );

		$components = array(
			'aptox-archive-grid'    => '/components/archive-grid/archive-grid.css',
			'aptox-grid-recipe'     => '/components/grid-recipe/grid-recipe.css',
			'aptox-grid-casa-decor' => '/components/grid-casa-decor/grid-casa-decor.css',
			'aptox-casa-reforma'    => '/components/casa-reforma/casa-reforma.css',
			'aptox-casa-cta'        => '/components/casa-cta/casa-cta.css',
			'aptox-casa-diy-marquee' => '/components/casa-diy-marquee/casa-diy-marquee.css',
		);

		foreach ( $components as $handle => $relative_path ) {
			$file_path = get_template_directory() . $relative_path;

			if ( ! file_exists( $file_path ) ) {
				continue;
			}

			wp_enqueue_style(
				$handle,
				get_template_directory_uri() . $relative_path,
				$deps,
				(string) filemtime( $file_path )
			);
		}
	}

	/**
	 * Enqueue scripts with conditional loading.
	 *
	 * @return void
	 */
	private function enqueue_scripts() {
		$should_enqueue_global_archive_load_more = is_archive() || is_tax() || is_search();

		$build_js_path = get_template_directory() . '/assets/build/main.js';
		if ( file_exists( $build_js_path ) ) {
			$asset_file = get_template_directory() . '/assets/build/main.asset.php';
			$asset_data = file_exists( $asset_file ) ? require $asset_file : array();
			$deps       = isset( $asset_data['dependencies'] ) ? $asset_data['dependencies'] : array();
			$version    = isset( $asset_data['version'] ) ? (string) $asset_data['version'] : (string) filemtime( $build_js_path );

			wp_enqueue_script(
				'aptox-main',
				get_template_directory_uri() . '/assets/build/main.js',
				$deps,
				$version,
				true
			);

			$this->localize_like_script( 'aptox-main' );
			if ( $should_enqueue_global_archive_load_more ) {
				$this->enqueue_archive_load_more_script( array( 'aptox-main' ) );
			}
		} else {
			wp_enqueue_script(
				'aptox-search-modal',
				get_template_directory_uri() . '/components/modal-search/modal-search.js',
				array(),
				'1.0',
				true
			);

			wp_enqueue_script(
				'aptox-menu',
				get_template_directory_uri() . '/components/menu/menu.js',
				array(),
				'1.0',
				true
			);

			if ( is_singular() ) {
				wp_enqueue_script(
					'aptox-like',
					get_template_directory_uri() . '/components/share/share.js',
					array(),
					'1.0',
					true
				);
				$this->localize_like_script( 'aptox-like' );
			}

			if ( $should_enqueue_global_archive_load_more ) {
				$this->enqueue_archive_load_more_script( array() );
			}
		}

		$this->enqueue_material_symbols();
		$this->enqueue_recipe_carousel_script();
		$this->enqueue_grid_recipe_script();
		$this->enqueue_grid_festivity_script();
		$this->enqueue_home_decor_slide_script();
		$this->enqueue_home_lazy_sections_script();
	}

	/**
	 * Enqueue progressive home section loader.
	 *
	 * @return void
	 */
	private function enqueue_home_lazy_sections_script() {
		if ( ! function_exists( 'aptox_is_lazy_home' ) || ! aptox_is_lazy_home() ) {
			return;
		}

		$script_path = get_template_directory() . '/components/home-lazy-sections/home-lazy-sections.js';

		wp_enqueue_script(
			'aptox-home-lazy-sections',
			get_template_directory_uri() . '/components/home-lazy-sections/home-lazy-sections.js',
			array(),
			file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0',
			true
		);

		wp_localize_script(
			'aptox-home-lazy-sections',
			'aptoxHomeLazy',
			array(
				'restUrl' => rest_url( 'aptox/v1/home-section/' ),
			)
		);
	}

	/**
	 * Whether seasonal carousels (recipe/decor grids) should load assets.
	 *
	 * @return bool
	 */
	private function should_enqueue_season_carousels() {
		if ( function_exists( 'aptox_is_lazy_home' ) && aptox_is_lazy_home() ) {
			return true;
		}

		return is_post_type_archive( 'casas' ) || is_page_template( 'templates/page-casa.php' );
	}

	/**
	 * Enqueue Material Symbols used on the home page.
	 *
	 * @return void
	 */
	private function enqueue_material_symbols() {
		if ( ! $this->should_enqueue_season_carousels() ) {
			return;
		}

		wp_enqueue_style(
			'aptox-material-symbols',
			'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300,0,0&icon_names=chevron_left,chevron_right,play_arrow',
			array(),
			null
		);
	}

	/**
	 * Enqueue recipe category carousel (loaded separately from main bundle).
	 *
	 * @return void
	 */
	private function enqueue_recipe_carousel_script() {
		if ( is_front_page() ) {
			return;
		}

		if ( ! is_home() && ! is_tax( 'receita_categoria' ) && ! is_tax( 'receita' ) && ! is_post_type_archive( 'receitas' ) && ! is_page_template( 'templates/page-receitas.php' ) ) {
			return;
		}

		$carousel_js_path = get_template_directory() . '/components/recipe-carousel/recipe-carousel.js';
		wp_enqueue_script(
			'aptox-carousel',
			get_template_directory_uri() . '/components/recipe-carousel/recipe-carousel.js',
			array(),
			file_exists( $carousel_js_path ) ? (string) filemtime( $carousel_js_path ) : '1.1',
			true
		);
	}

	/**
	 * Enqueue seasonal recipe carousel on home.
	 *
	 * @return void
	 */
	private function enqueue_grid_recipe_script() {
		if ( ! $this->should_enqueue_season_carousels() ) {
			return;
		}

		$script_path = get_template_directory() . '/components/grid-recipe/grid-recipe.js';

		wp_enqueue_script(
			'aptox-grid-recipe',
			get_template_directory_uri() . '/components/grid-recipe/grid-recipe.js',
			array(),
			file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0',
			true
		);
	}

	/**
	 * Enqueue seasonal festivities carousel on home.
	 *
	 * @return void
	 */
	private function enqueue_grid_festivity_script() {
		if ( ! function_exists( 'aptox_is_lazy_home' ) || ! aptox_is_lazy_home() ) {
			return;
		}

		$script_path = get_template_directory() . '/components/grid-festivity/grid-festivity.js';

		wp_enqueue_script(
			'aptox-grid-festivity',
			get_template_directory_uri() . '/components/grid-festivity/grid-festivity.js',
			array(),
			file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0',
			true
		);
	}

	/**
	 * Enqueue home decor slide script (loaded separately from main bundle).
	 *
	 * @return void
	 */
	private function enqueue_home_decor_slide_script() {
		if ( ( ! function_exists( 'aptox_is_lazy_home' ) || ! aptox_is_lazy_home() ) && ! is_post_type_archive( 'casas' ) && ! is_page_template( 'templates/page-casa.php' ) && ! is_tax( 'casa_categoria' ) ) {
			return;
		}

		$slide_js_path = get_template_directory() . '/components/home-decor-slide/home-decor-slide.js';
		wp_enqueue_script(
			'aptox-home-slide',
			get_template_directory_uri() . '/components/home-decor-slide/home-decor-slide.js',
			array(),
			file_exists( $slide_js_path ) ? (string) filemtime( $slide_js_path ) : '1.0',
			true
		);
	}

	/**
	 * Add likes AJAX data to script.
	 *
	 * @param string $handle Script handle.
	 * @return void
	 */
	private function localize_like_script( $handle ) {
		wp_localize_script(
			$handle,
			'aptoxLike',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'aptox_like_nonce' ),
				'iconOutline' => get_template_directory_uri() . '/assets/icons/ui/favorite-outline.svg',
				'iconFilled'  => get_template_directory_uri() . '/assets/icons/ui/favorite-filled.svg',
			)
		);
	}

	/**
	 * Enqueue generic archive/search load more script.
	 *
	 * @param array<int, string> $deps Script dependencies.
	 * @return void
	 */
	private function enqueue_archive_load_more_script( array $deps ) {
		wp_enqueue_script(
			'aptox-archive-load-more',
			get_template_directory_uri() . '/components/archive-grid/archive-load-more.js',
			$deps,
			(string) filemtime( get_template_directory() . '/components/archive-grid/archive-load-more.js' ),
			true
		);
	}
}
