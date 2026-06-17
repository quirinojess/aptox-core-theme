<?php
/**
 * Asset registration and enqueue strategy.
 *
 * @package Aptox
 */

namespace Aptox\Core;

use Aptox\Services\NotFoundService;
use Aptox\Services\SeasonService;

class Assets {
	/**
	 * Register asset hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_dequeue_jquery' ), 100 );
		add_action( 'wp_head', array( $this, 'preload_lcp_resources' ), 1 );
	}

	/**
	 * Enqueue fonts, styles and scripts.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$this->enqueue_styles();
		$this->enqueue_scripts();
	}

	/**
	 * Preload LCP resources on the home page.
	 *
	 * @return void
	 */
	public function preload_lcp_resources() {
		if ( ! function_exists( 'aptox_is_lazy_home' ) || ! aptox_is_lazy_home() ) {
			return;
		}

		$font_path = get_template_directory() . '/assets/fonts/bth-primary-regular.woff2';
		$image_uri = function_exists( 'aptox_theme_image_uri' ) ? aptox_theme_image_uri( 'index-cta' ) : '';

		if ( file_exists( $font_path ) ) {
			printf(
				'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
				esc_url( get_template_directory_uri() . '/assets/fonts/bth-primary-regular.woff2' )
			);
		}

		if ( $image_uri ) {
			printf(
				'<link rel="preload" href="%s" as="image" fetchpriority="high">' . "\n",
				esc_url( $image_uri )
			);
		}
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

			$this->enqueue_filter_nav_styles();
			$this->enqueue_casa_page_styles();
			$this->enqueue_celebre_page_styles();
			$this->enqueue_loja_page_styles();
			$this->enqueue_footer_loja_styles();
			$this->enqueue_footer_ad_styles();
			return;
		}

		$this->enqueue_non_import_styles();
	}

	/**
	 * Style handles that must stay render-blocking.
	 *
	 * @return array<int, string>
	 */
	private function get_critical_style_handles() {
		$handles = array(
			'aptox-base',
			'aptox-layout-containers',
			'aptox-layout-menu',
			'aptox-layout-footer',
			'aptox-layout-buttons',
		);

		if ( function_exists( 'aptox_is_lazy_home' ) && aptox_is_lazy_home() ) {
			$handles[] = 'aptox-index-cta';
		}

		if ( function_exists( 'aptox_is_lazy_celebre' ) && aptox_is_lazy_celebre() ) {
			$handles[] = 'aptox-celebre-cta';
		}

		if ( is_post_type_archive( 'casas' ) || is_page_template( 'templates/page-casa.php' ) ) {
			$handles[] = 'aptox-casa-cta';
		}

		if ( $this->is_loja_context() ) {
			$handles[] = 'aptox-loja-cta';
		}

		return $handles;
	}

	/**
	 * Default dependency chain for component styles when no build bundle exists.
	 *
	 * @return array<int, string>
	 */
	private function get_component_style_deps() {
		if ( file_exists( get_template_directory() . '/assets/build/main.css' ) ) {
			return array( 'aptox-main' );
		}

		return array( 'aptox-layout-buttons' );
	}

	/**
	 * Enqueue non-import CSS files directly to avoid request chaining.
	 *
	 * @return void
	 */
	private function enqueue_non_import_styles() {
		$this->enqueue_theme_style(
			'aptox-base',
			'/assets/css/global/base.css'
		);

		$layout_handles = array(
			'aptox-layout-containers' => '/assets/css/global/layout/containers.css',
			'aptox-layout-menu'       => '/assets/css/global/layout/menu.css',
			'aptox-layout-footer'     => '/assets/css/global/layout/footer.css',
			'aptox-layout-blocks'     => '/assets/css/global/layout/blocks.css',
			'aptox-layout-buttons'    => '/assets/css/global/layout/buttons.css',
		);

		$this->enqueue_theme_styles( $layout_handles, array( 'aptox-base' ) );

		$component_handles = array(
			'aptox-archive-grid'          => '/components/archive-grid/archive-grid.css',
			'aptox-grid-casa-decor'       => '/components/grid-casa-decor/grid-casa-decor.css',
			'aptox-casa-reforma'          => '/components/casa-reforma/casa-reforma.css',
			'aptox-casa-cta'              => '/components/casa-cta/casa-cta.css',
			'aptox-celebre-cta'           => '/components/celebre-cta/celebre-cta.css',
			'aptox-celebre-block'         => '/components/celebre-block/celebre-block.css',
			'aptox-casa-diy-marquee'      => '/components/casa-diy-marquee/casa-diy-marquee.css',
			'aptox-casa-organizacao'      => '/components/casa-organizacao/casa-organizacao.css',
			'aptox-grid-festivity'        => '/components/grid-festivity/grid-festivity.css',
			'aptox-casa-jardinagem'       => '/components/casa-jardinagem/casa-jardinagem.css',
			'aptox-casa-rooms-carousel'   => '/components/casa-rooms-carousel/casa-rooms-carousel.css',
			'aptox-casa-planejando-lar'   => '/components/casa-planejando-lar/casa-planejando-lar.css',
			'aptox-grid-recipe'           => '/components/grid-recipe/grid-recipe.css',
			'aptox-recipe-carousel'       => '/components/recipe-carousel/recipe-carousel.css',
			'aptox-recipe-tag-filter'     => '/components/recipe-tag-filter/recipe-tag-filter.css',
			'aptox-modal-search'          => '/components/modal-search/modal-search.css',
			'aptox-modal-season'          => '/components/modal-season/modal-season.css',
			'aptox-home-decor'            => '/components/home-decor/home-decor.css',
			'aptox-related-posts'         => '/components/related-posts/related-posts.css',
			'aptox-taxonomy-layout'       => '/assets/css/global/layout/taxonomy.css',
			'aptox-filter-nav'            => '/components/filter-nav/filter-nav.css',
			'aptox-cta-celebration'       => '/components/cta-celebration/cta-celebration.css',
			'aptox-author'                => '/components/author/author.css',
			'aptox-sidebar'               => '/components/sidebar/sidebar.css',
			'aptox-share'                 => '/components/share/share.css',
			'aptox-post-pin-it'           => '/components/post-pin-it/post-pin-it.css',
			'aptox-post-share-stack'      => '/components/post-share-stack/post-share-stack.css',
			'aptox-post-taxonomies'       => '/components/post-taxonomies/post-taxonomies.css',
			'aptox-post-nav'              => '/components/post-nav/post-nav.css',
			'aptox-back-to-top'           => '/components/back-to-top/back-to-top.css',
			'aptox-edit-post'             => '/components/edit-post/edit-post.css',
			'aptox-season-slide'          => '/components/season-slide/season-slide.css',
			'aptox-celebre-season-slide'  => '/components/celebre-season-slide/celebre-season-slide.css',
			'aptox-index-cta'             => '/components/index-cta/index-cta.css',
			'aptox-page-sobre'            => '/components/page-sobre/page-sobre.css',
			'aptox-page-contato'          => '/components/page-contato/page-contato.css',
			'aptox-page-editorial'        => '/components/page-editorial/page-editorial.css',
			'aptox-page-not-found'        => '/components/page-not-found/page-not-found.css',
			'aptox-page-sobre-timeline'   => '/components/page-sobre-timeline/page-sobre-timeline.css',
			'aptox-page-sobre-clipping'   => '/components/page-sobre-clipping/page-sobre-clipping.css',
			'aptox-info-grid'             => '/components/info-grid/info-grid.css',
			'aptox-celebre-info-grid'     => '/components/celebre-info-grid/celebre-info-grid.css',
			'aptox-cta-season'            => '/components/cta-season/cta-season.css',
			'aptox-page-loja'             => '/components/page-loja/page-loja.css',
			'aptox-grid-loja'             => '/components/grid-loja/grid-loja.css',
			'aptox-loja-cta'              => '/components/loja-cta/loja-cta.css',
			'aptox-footer-loja'           => '/components/footer-loja/footer-loja.css',
			'aptox-youtube-feed'          => '/components/youtube-feed/youtube-feed.css',
			'aptox-home-lazy-sections'    => '/components/home-lazy-sections/home-lazy-sections.css',
			'aptox-components-overrides'  => '/assets/css/components-overrides.css',
		);

		$this->enqueue_theme_styles( $component_handles, array( 'aptox-layout-buttons' ) );
		$this->enqueue_footer_ad_styles();
	}

	/**
	 * Enqueue a theme stylesheet when the file exists.
	 *
	 * @param string               $handle        Style handle.
	 * @param string               $relative_path Path relative to theme root.
	 * @param array<int, string>   $deps          Dependencies.
	 * @return void
	 */
	private function enqueue_theme_style( $handle, $relative_path, array $deps = array() ) {
		$file_path = get_template_directory() . $relative_path;

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		wp_enqueue_style(
			$handle,
			get_template_directory_uri() . $relative_path,
			$deps,
			(string) filemtime( $file_path )
		);
	}

	/**
	 * Enqueue multiple theme stylesheets.
	 *
	 * @param array<string, string> $styles Map of handle => relative path.
	 * @param array<int, string>    $deps   Dependencies.
	 * @return void
	 */
	private function enqueue_theme_styles( array $styles, array $deps = array() ) {
		if ( empty( $deps ) ) {
			$deps = $this->get_component_style_deps();
		}

		foreach ( $styles as $handle => $relative_path ) {
			$this->enqueue_theme_style( $handle, $relative_path, $deps );
		}
	}

	/**
	 * Enqueue global layout styles directly (avoids @import chains).
	 *
	 * @return void
	 */
	private function enqueue_layout_styles() {
		$base_deps = array( 'aptox-base' );
		$layouts   = array(
			'aptox-layout-containers' => '/assets/css/global/layout/containers.css',
			'aptox-layout-menu'       => '/assets/css/global/layout/menu.css',
			'aptox-layout-footer'     => '/assets/css/global/layout/footer.css',
			'aptox-layout-buttons'    => '/assets/css/global/layout/buttons.css',
		);

		foreach ( $layouts as $handle => $relative_path ) {
			$this->enqueue_theme_style( $handle, $relative_path, $base_deps );
		}

		if ( is_singular() || is_page() ) {
			$this->enqueue_theme_style(
				'aptox-layout-blocks',
				'/assets/css/global/layout/blocks.css',
				$base_deps
			);
		}
	}

	/**
	 * Enqueue styles shared across all public pages.
	 *
	 * @return void
	 */
	private function enqueue_shared_component_styles() {
		$this->enqueue_theme_styles(
			array(
				'aptox-modal-search'        => '/components/modal-search/modal-search.css',
				'aptox-edit-post'           => '/components/edit-post/edit-post.css',
				'aptox-components-overrides' => '/assets/css/components-overrides.css',
			)
		);
	}

	/**
	 * Enqueue home page section styles.
	 *
	 * @return void
	 */
	private function enqueue_home_styles() {
		if ( ! function_exists( 'aptox_is_lazy_home' ) || ! aptox_is_lazy_home() ) {
			return;
		}

		$this->enqueue_theme_styles(
			array(
				'aptox-index-cta'          => '/components/index-cta/index-cta.css',
				'aptox-home-lazy-sections' => '/components/home-lazy-sections/home-lazy-sections.css',
				'aptox-info-grid'          => '/components/info-grid/info-grid.css',
				'aptox-cta-season'         => '/components/cta-season/cta-season.css',
				'aptox-grid-recipe'        => '/components/grid-recipe/grid-recipe.css',
				'aptox-season-slide'       => '/components/season-slide/season-slide.css',
				'aptox-grid-festivity'     => '/components/grid-festivity/grid-festivity.css',
				'aptox-youtube-feed'       => '/components/youtube-feed/youtube-feed.css',
			)
		);
	}

	/**
	 * Enqueue single post/page component styles.
	 *
	 * @return void
	 */
	private function enqueue_singular_styles() {
		if ( ! is_singular() ) {
			return;
		}

		$this->enqueue_theme_styles(
			array(
				'aptox-author'            => '/components/author/author.css',
				'aptox-sidebar'           => '/components/sidebar/sidebar.css',
				'aptox-share'             => '/components/share/share.css',
				'aptox-post-pin-it'       => '/components/post-pin-it/post-pin-it.css',
				'aptox-post-share-stack'  => '/components/post-share-stack/post-share-stack.css',
				'aptox-post-taxonomies'   => '/components/post-taxonomies/post-taxonomies.css',
				'aptox-post-nav'          => '/components/post-nav/post-nav.css',
				'aptox-back-to-top'       => '/components/back-to-top/back-to-top.css',
				'aptox-related-posts'     => '/components/related-posts/related-posts.css',
			)
		);
	}

	/**
	 * Enqueue archive and taxonomy listing styles.
	 *
	 * @return void
	 */
	private function enqueue_archive_styles() {
		if ( ! is_archive() && ! is_tax() && ! is_search() && ! is_home() ) {
			return;
		}

		$this->enqueue_theme_style(
			'aptox-archive-grid',
			'/components/archive-grid/archive-grid.css',
			$this->get_component_style_deps()
		);

		if ( is_tax() || is_category() || is_tag() ) {
			$this->enqueue_theme_style(
				'aptox-taxonomy-layout',
				'/assets/css/global/layout/taxonomy.css',
				$this->get_component_style_deps()
			);
		}
	}

	/**
	 * Enqueue Receitas section styles.
	 *
	 * @return void
	 */
	private function enqueue_receitas_styles() {
		if (
			! is_home()
			&& ! is_post_type_archive( 'receitas' )
			&& ! is_tax( 'receita_categoria' )
			&& ! is_tax( 'receita_tag' )
			&& ! is_tax( 'receita' )
			&& ! is_page_template( 'templates/page-receitas.php' )
		) {
			return;
		}

		$this->enqueue_theme_styles(
			array(
				'aptox-recipe-carousel'  => '/components/recipe-carousel/recipe-carousel.css',
				'aptox-recipe-tag-filter' => '/components/recipe-tag-filter/recipe-tag-filter.css',
			)
		);
	}

	/**
	 * Enqueue static page template styles.
	 *
	 * @return void
	 */
	private function enqueue_static_page_styles() {
		if ( is_page( 'sobre' ) || is_page( 'manifesto' ) || is_page_template( 'templates/page-sobre.php' ) ) {
			$this->enqueue_theme_styles(
				array(
					'aptox-page-sobre'           => '/components/page-sobre/page-sobre.css',
					'aptox-page-sobre-timeline'  => '/components/page-sobre-timeline/page-sobre-timeline.css',
					'aptox-page-sobre-clipping'  => '/components/page-sobre-clipping/page-sobre-clipping.css',
					'aptox-page-editorial'       => '/components/page-editorial/page-editorial.css',
				)
			);
		}

		if ( is_page( 'contato' ) ) {
			$this->enqueue_theme_style(
				'aptox-page-contato',
				'/components/page-contato/page-contato.css',
				$this->get_component_style_deps()
			);
		}

		if (
			is_page( 'editorial' )
			|| is_page( 'manifesto' )
			|| is_page_template( 'templates/page-editorial.php' )
			|| is_page_template( 'templates/page-manifesto.php' )
		) {
			$this->enqueue_theme_style(
				'aptox-page-editorial',
				'/components/page-editorial/page-editorial.css',
				$this->get_component_style_deps()
			);
		}

		if ( is_404() ) {
			$this->enqueue_theme_style(
				'aptox-page-not-found',
				'/components/page-not-found/page-not-found.css',
				$this->get_component_style_deps()
			);
		}
	}

	/**
	 * Load non-critical theme CSS asynchronously.
	 *
	 * @param string $html   Link tag HTML.
	 * @param string $handle Style handle.
	 * @param string $href   Style URL.
	 * @param string $media  Media attribute.
	 * @return string
	 */
	public function async_non_critical_styles( $html, $handle, $href, $media ) {
		unset( $href, $media );

		if ( 0 !== strpos( (string) $handle, 'aptox-' ) ) {
			return $html;
		}

		if ( in_array( $handle, $this->get_critical_style_handles(), true ) ) {
			return $html;
		}

		if ( false === stripos( $html, 'stylesheet' ) ) {
			return $html;
		}

		$async = preg_replace(
			'/\smedia=(["\'])all\1/i',
			' media=$1print$1 onload="this.media=\'all\'"',
			$html,
			1
		);

		if ( ! is_string( $async ) || $async === $html ) {
			return $html;
		}

		return $async . '<noscript>' . $html . '</noscript>';
	}

	/**
	 * Remove jQuery when no queued script depends on it.
	 *
	 * @return void
	 */
	public function maybe_dequeue_jquery() {
		if ( is_admin() ) {
			return;
		}

		global $wp_scripts;

		if ( ! $wp_scripts instanceof \WP_Scripts ) {
			return;
		}

		$needs_jquery = false;

		foreach ( (array) $wp_scripts->queue as $handle ) {
			if ( empty( $wp_scripts->registered[ $handle ] ) ) {
				continue;
			}

			$deps = (array) $wp_scripts->registered[ $handle ]->deps;

			if ( in_array( 'jquery', $deps, true ) || in_array( 'jquery-core', $deps, true ) ) {
				$needs_jquery = true;
				break;
			}
		}

		if ( $needs_jquery ) {
			return;
		}

		wp_dequeue_script( 'jquery' );
		wp_dequeue_script( 'jquery-migrate' );
	}

	/**
	 * Enqueue filter nav styles with cache busting (PNG icons need explicit sizing).
	 *
	 * @return void
	 */
	private function enqueue_filter_nav_styles() {
		if (
			! is_post_type_archive( 'casas' )
			&& ! is_tax( 'casa_categoria' )
			&& ! is_tax( 'casa' )
			&& ! is_page_template( 'templates/page-casa.php' )
			&& ! is_post_type_archive( 'celebracoes' )
			&& ! is_tax( 'celebracao_categoria' )
			&& ! is_tax( 'celebracao' )
			&& ! is_page_template( 'templates/page-celebration.php' )
			&& ! $this->is_loja_context()
		) {
			return;
		}

		$file_path = get_template_directory() . '/components/filter-nav/filter-nav.css';

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		$deps = $this->get_component_style_deps();

		wp_enqueue_style(
			'aptox-filter-nav',
			get_template_directory_uri() . '/components/filter-nav/filter-nav.css',
			$deps,
			(string) filemtime( $file_path )
		);
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

		$deps = $this->get_component_style_deps();

		$components = array(
			'aptox-archive-grid'     => '/components/archive-grid/archive-grid.css',
			'aptox-grid-recipe'      => '/components/grid-recipe/grid-recipe.css',
			'aptox-grid-casa-decor'  => '/components/grid-casa-decor/grid-casa-decor.css',
			'aptox-casa-reforma'     => '/components/casa-reforma/casa-reforma.css',
			'aptox-casa-cta'         => '/components/casa-cta/casa-cta.css',
			'aptox-casa-diy-marquee' => '/components/casa-diy-marquee/casa-diy-marquee.css',
			'aptox-casa-organizacao' => '/components/casa-organizacao/casa-organizacao.css',
			'aptox-grid-festivity'   => '/components/grid-festivity/grid-festivity.css',
			'aptox-casa-jardinagem'  => '/components/casa-jardinagem/casa-jardinagem.css',
			'aptox-recipe-carousel'  => '/components/recipe-carousel/recipe-carousel.css',
			'aptox-casa-rooms-carousel' => '/components/casa-rooms-carousel/casa-rooms-carousel.css',
			'aptox-home-decor'          => '/components/home-decor/home-decor.css',
			'aptox-casa-planejando-lar' => '/components/casa-planejando-lar/casa-planejando-lar.css',
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
	 * Enqueue Celebre page component styles (not always in stale build bundles).
	 *
	 * @return void
	 */
	private function enqueue_celebre_page_styles() {
		if ( ! is_post_type_archive( 'celebracoes' ) && ! is_page_template( 'templates/page-celebration.php' ) ) {
			return;
		}

		$deps = $this->get_component_style_deps();

		$components = array(
			'aptox-celebre-cta'         => '/components/celebre-cta/celebre-cta.css',
			'aptox-home-lazy-sections'  => '/components/home-lazy-sections/home-lazy-sections.css',
			'aptox-season-slide'         => '/components/season-slide/season-slide.css',
			'aptox-celebre-season-slide' => '/components/celebre-season-slide/celebre-season-slide.css',
			'aptox-info-grid'           => '/components/info-grid/info-grid.css',
			'aptox-celebre-info-grid'   => '/components/celebre-info-grid/celebre-info-grid.css',
			'aptox-archive-grid'        => '/components/archive-grid/archive-grid.css',
			'aptox-celebre-block'       => '/components/celebre-block/celebre-block.css',
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
	 * Enqueue Loja page component styles (not always in stale build bundles).
	 *
	 * @return void
	 */
	private function enqueue_loja_page_styles() {
		if ( ! $this->is_loja_context() ) {
			return;
		}

		$deps = $this->get_component_style_deps();

		$components = array(
			'aptox-loja-cta'   => '/components/loja-cta/loja-cta.css',
			'aptox-grid-loja'  => '/components/grid-loja/grid-loja.css',
			'aptox-page-loja'  => '/components/page-loja/page-loja.css',
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
	 * Whether the current request should load Loja page styles.
	 *
	 * @return bool
	 */
	private function is_loja_context() {
		if (
			is_post_type_archive( 'loja' )
			|| is_tax( 'loja_categoria' )
			|| is_page_template( 'templates/page-loja.php' )
		) {
			return true;
		}

		if ( ! is_404() ) {
			return false;
		}

		$context = NotFoundService::get_context();
		$term    = $context['term'] ?? null;

		return $term instanceof \WP_Term && 'loja_categoria' === $term->taxonomy;
	}

	/**
	 * Enqueue footer Loja carousel assets on every page.
	 *
	 * @return void
	 */
	private function enqueue_footer_loja_styles() {
		$file_path = get_template_directory() . '/components/footer-loja/footer-loja.css';

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		$deps = $this->get_component_style_deps();

		wp_enqueue_style(
			'aptox-footer-loja',
			get_template_directory_uri() . '/components/footer-loja/footer-loja.css',
			$deps,
			(string) filemtime( $file_path )
		);
	}

	/**
	 * Enqueue sticky footer ad styles on every page.
	 *
	 * @return void
	 */
	private function enqueue_footer_ad_styles() {
		if ( ! function_exists( 'aptox_show_footer_ad' ) || ! aptox_show_footer_ad() ) {
			return;
		}

		$this->enqueue_theme_style(
			'aptox-footer-ad',
			'/components/footer-ad/footer-ad.css'
		);
	}

	/**
	 * Enqueue sticky footer ad assets when the widget area is active.
	 *
	 * @return void
	 */
	private function enqueue_footer_ad_assets() {
		if ( is_admin() || ! function_exists( 'aptox_show_footer_ad' ) || ! aptox_show_footer_ad() ) {
			return;
		}

		$script_path = get_template_directory() . '/components/footer-ad/footer-ad.js';

		wp_enqueue_script(
			'aptox-footer-ad',
			get_template_directory_uri() . '/components/footer-ad/footer-ad.js',
			array(),
			file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0',
			true
		);
	}

	/**
	 * Enqueue footer Loja carousel script when the footer carousel is present.
	 *
	 * @return void
	 */
	private function enqueue_footer_loja_script() {
		if ( ! function_exists( 'aptox_show_footer_loja' ) || ! aptox_show_footer_loja() ) {
			return;
		}

		$this->enqueue_dynamic_loader_script();
	}

	/**
	 * Enqueue scripts with conditional loading.
	 *
	 * @return void
	 */
	private function enqueue_scripts() {
		$should_enqueue_global_archive_load_more = is_archive() || is_tax() || is_search() || $this->is_loja_context();

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
			$this->localize_menu_script( 'aptox-main' );
			if ( $should_enqueue_global_archive_load_more ) {
				$this->enqueue_archive_load_more_script( array( 'aptox-main' ) );
			}

			$this->enqueue_footer_loja_script();
			$this->enqueue_footer_ad_assets();
		} else {
			$modal_search_path = get_template_directory() . '/components/modal-search/modal-search.js';
			$menu_path         = get_template_directory() . '/components/menu/menu.js';

			wp_enqueue_script(
				'aptox-search-modal',
				get_template_directory_uri() . '/components/modal-search/modal-search.js',
				array(),
				file_exists( $modal_search_path ) ? (string) filemtime( $modal_search_path ) : '1.0',
				true
			);

			$modal_season_path = get_template_directory() . '/components/modal-season/modal-season.js';

			wp_enqueue_script(
				'aptox-season-modal',
				get_template_directory_uri() . '/components/modal-season/modal-season.js',
				array(),
				file_exists( $modal_season_path ) ? (string) filemtime( $modal_season_path ) : '1.0',
				true
			);

			wp_enqueue_script(
				'aptox-menu',
				get_template_directory_uri() . '/components/menu/menu.js',
				array(),
				file_exists( $menu_path ) ? (string) filemtime( $menu_path ) : '1.0',
				true
			);
			$this->localize_menu_script( 'aptox-menu' );

			$this->enqueue_footer_loja_script();
			$this->enqueue_footer_ad_assets();

			if ( is_singular() ) {
				wp_enqueue_script(
					'aptox-like',
					get_template_directory_uri() . '/components/share/share.js',
					array(),
					(string) filemtime( get_template_directory() . '/components/share/share.js' ),
					true
				);
				$this->localize_like_script( 'aptox-like' );

				$back_to_top_path = get_template_directory() . '/components/back-to-top/back-to-top.js';

				wp_enqueue_script(
					'aptox-back-to-top',
					get_template_directory_uri() . '/components/back-to-top/back-to-top.js',
					array(),
					file_exists( $back_to_top_path ) ? (string) filemtime( $back_to_top_path ) : '1.0',
					true
				);

				$post_pin_it_path = get_template_directory() . '/components/post-pin-it/post-pin-it.js';

				wp_enqueue_script(
					'aptox-post-pin-it',
					get_template_directory_uri() . '/components/post-pin-it/post-pin-it.js',
					array(),
					file_exists( $post_pin_it_path ) ? (string) filemtime( $post_pin_it_path ) : '1.0',
					true
				);
			}

			if ( $should_enqueue_global_archive_load_more ) {
				$this->enqueue_archive_load_more_script( array() );
			}
		}

		$this->enqueue_recipe_carousel_script();
		$this->enqueue_grid_recipe_script();
		$this->enqueue_grid_festivity_script();
		$this->enqueue_celebre_block_script();
		$this->enqueue_casa_organizacao_script();
		$this->enqueue_season_slide_script();
		$this->enqueue_home_lazy_sections_script();
		$this->enqueue_page_sobre_pillars_script();
		$this->apply_defer_strategy();
	}

	/**
	 * Enqueue Casa organização carousel on Casa pages.
	 *
	 * @return void
	 */
	private function enqueue_casa_organizacao_script() {
		if ( ! is_post_type_archive( 'casas' ) && ! is_page_template( 'templates/page-casa.php' ) ) {
			return;
		}

		$script_path = get_template_directory() . '/components/casa-organizacao/casa-organizacao.js';

		wp_enqueue_script(
			'aptox-casa-organizacao',
			get_template_directory_uri() . '/components/casa-organizacao/casa-organizacao.js',
			array(),
			file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0',
			true
		);
	}

	/**
	 * Enqueue progressive home section loader.
	 *
	 * @return void
	 */
	private function enqueue_home_lazy_sections_script() {
		$is_home    = function_exists( 'aptox_is_lazy_home' ) && aptox_is_lazy_home();
		$is_celebre = function_exists( 'aptox_is_lazy_celebre' ) && aptox_is_lazy_celebre();

		if ( ! $is_home && ! $is_celebre ) {
			return;
		}

		$this->enqueue_dynamic_loader_script();

		$script_path = get_template_directory() . '/components/home-lazy-sections/home-lazy-sections.js';

		wp_enqueue_script(
			'aptox-home-lazy-sections',
			get_template_directory_uri() . '/components/home-lazy-sections/home-lazy-sections.js',
			array( 'aptox-load-script' ),
			file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0',
			true
		);

		if ( $is_home ) {
			$season_cookie = SeasonService::get_client_cookie_config();

			wp_localize_script(
				'aptox-home-lazy-sections',
				'aptoxHomeLazy',
				array(
					'restUrl'        => rest_url( 'aptox/v1/home-section/' ),
					'seasonSlug'     => sanitize_title( (string) ( aptox_get_season_context()['slug'] ?? '' ) ),
					'cookieName'     => $season_cookie['name'],
					'sectionScripts' => $this->get_lazy_home_section_scripts(),
				)
			);
		}

		if ( $is_celebre ) {
			$season_cookie = SeasonService::get_client_cookie_config();

			wp_localize_script(
				'aptox-home-lazy-sections',
				'aptoxCelebreLazy',
				array(
					'restUrl'        => rest_url( 'aptox/v1/celebre-section/' ),
					'seasonSlug'     => sanitize_title( (string) ( aptox_get_season_context()['slug'] ?? '' ) ),
					'cookieName'     => $season_cookie['name'],
					'sectionScripts' => $this->get_lazy_celebre_section_scripts(),
				)
			);
		}
	}

	/**
	 * Whether carousel scripts are loaded on demand for lazy sections.
	 *
	 * @return bool
	 */
	private function uses_lazy_section_scripts() {
		return ( function_exists( 'aptox_is_lazy_home' ) && aptox_is_lazy_home() )
			|| ( function_exists( 'aptox_is_lazy_celebre' ) && aptox_is_lazy_celebre() );
	}

	/**
	 * Script URLs for lazy-loaded home sections.
	 *
	 * @return array<string, string>
	 */
	private function get_lazy_home_section_scripts() {
		return array(
			'grid-recipe'    => $this->get_theme_script_uri( '/components/grid-recipe/grid-recipe.js' ),
			'grid-festivity' => $this->get_theme_script_uri( '/components/grid-festivity/grid-festivity.js' ),
			'season-slide'   => $this->get_theme_script_uri( '/components/season-slide/season-slide.js' ),
		);
	}

	/**
	 * Script URLs for lazy-loaded Celebre sections.
	 *
	 * @return array<string, string>
	 */
	private function get_lazy_celebre_section_scripts() {
		return array(
			'season-slide'   => $this->get_theme_script_uri( '/components/season-slide/season-slide.js' ),
			'celebre-season' => $this->get_theme_script_uri( '/components/celebre-block/celebre-block.js' ),
		);
	}

	/**
	 * Enqueue shared dynamic script loader.
	 *
	 * @return void
	 */
	private function enqueue_dynamic_loader_script() {
		$file_path = get_template_directory() . '/assets/js/aptox-load-script.js';

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		wp_enqueue_script(
			'aptox-load-script',
			get_template_directory_uri() . '/assets/js/aptox-load-script.js',
			array(),
			(string) filemtime( $file_path ),
			true
		);

		$footer_loja_path = get_template_directory() . '/components/footer-loja/footer-loja.js';

		if ( file_exists( $footer_loja_path ) ) {
			wp_add_inline_script(
				'aptox-load-script',
				'window.aptoxFooterLojaScript=' . wp_json_encode( $this->get_theme_script_uri( '/components/footer-loja/footer-loja.js' ) ) . ';',
				'before'
			);
		}
	}

	/**
	 * Build a versioned theme script URI.
	 *
	 * @param string $relative_path Path relative to theme root.
	 * @return string
	 */
	private function get_theme_script_uri( $relative_path ) {
		$file_path = get_template_directory() . $relative_path;
		$version   = file_exists( $file_path ) ? (string) filemtime( $file_path ) : '1.0';

		return add_query_arg( 'ver', $version, get_template_directory_uri() . $relative_path );
	}

	/**
	 * Apply defer loading strategy to theme scripts.
	 *
	 * @return void
	 */
	private function apply_defer_strategy() {
		$handles = array(
			'aptox-main',
			'aptox-search-modal',
			'aptox-menu',
			'aptox-like',
			'aptox-back-to-top',
			'aptox-post-pin-it',
			'aptox-archive-load-more',
			'aptox-carousel',
			'aptox-grid-recipe',
			'aptox-grid-festivity',
			'aptox-celebre-block',
			'aptox-casa-organizacao',
			'aptox-season-slide',
			'aptox-home-lazy-sections',
			'aptox-load-script',
			'aptox-footer-ad',
		);

		foreach ( $handles as $handle ) {
			if ( wp_script_is( $handle, 'enqueued' ) || wp_script_is( $handle, 'registered' ) ) {
				wp_script_add_data( $handle, 'strategy', 'defer' );
			}
		}
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
	 * Enqueue recipe category carousel (loaded separately from main bundle).
	 *
	 * @return void
	 */
	private function enqueue_recipe_carousel_script() {
		if ( is_front_page() ) {
			return;
		}

		if (
			! is_home()
			&& ! is_tax( 'receita_categoria' )
			&& ! is_tax( 'receita_tag' )
			&& ! is_tax( 'receita' )
			&& ! is_post_type_archive( 'receitas' )
			&& ! is_page_template( 'templates/page-receitas.php' )
			&& ! is_post_type_archive( 'casas' )
			&& ! is_page_template( 'templates/page-casa.php' )
		) {
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
		if ( $this->uses_lazy_section_scripts() ) {
			return;
		}

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
		if ( $this->uses_lazy_section_scripts() ) {
			return;
		}

		if (
			( function_exists( 'aptox_is_lazy_home' ) && aptox_is_lazy_home() )
			|| is_post_type_archive( 'casas' )
			|| is_page_template( 'templates/page-casa.php' )
		) {
			// Continue below.
		} else {
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
	 * Enqueue Celebre festivity block carousel on celebration pages.
	 *
	 * @return void
	 */
	private function enqueue_celebre_block_script() {
		if ( $this->uses_lazy_section_scripts() ) {
			return;
		}

		if ( ! is_post_type_archive( 'celebracoes' ) && ! is_page_template( 'templates/page-celebration.php' ) ) {
			return;
		}

		$script_path = get_template_directory() . '/components/celebre-block/celebre-block.js';

		wp_enqueue_script(
			'aptox-celebre-block',
			get_template_directory_uri() . '/components/celebre-block/celebre-block.js',
			array(),
			file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0',
			true
		);
	}

	/**
	 * Enqueue Manifesto pillars carousel script.
	 *
	 * @return void
	 */
	private function enqueue_page_sobre_pillars_script() {
		if ( $this->uses_lazy_section_scripts() ) {
			return;
		}

		if ( ! is_page( 'sobre' ) && ! is_page( 'manifesto' ) && ! is_page_template( 'templates/page-sobre.php' ) ) {
			return;
		}

		$script_path = get_template_directory() . '/components/page-sobre/page-sobre-pillars.js';

		wp_enqueue_script(
			'aptox-page-sobre-pillars',
			get_template_directory_uri() . '/components/page-sobre/page-sobre-pillars.js',
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
	private function enqueue_season_slide_script() {
		if ( $this->uses_lazy_section_scripts() ) {
			return;
		}

		$is_celebre_page = is_post_type_archive( 'celebracoes' ) || is_page_template( 'templates/page-celebration.php' );

		if (
			( ! function_exists( 'aptox_is_lazy_home' ) || ! aptox_is_lazy_home() )
			&& ! is_post_type_archive( 'casas' )
			&& ! is_page_template( 'templates/page-casa.php' )
			&& ! is_tax( 'casa_categoria' )
			&& ! $is_celebre_page
		) {
			return;
		}

		$slide_js_path = get_template_directory() . '/components/season-slide/season-slide.js';
		wp_enqueue_script(
			'aptox-season-slide',
			get_template_directory_uri() . '/components/season-slide/season-slide.js',
			array(),
			file_exists( $slide_js_path ) ? (string) filemtime( $slide_js_path ) : '1.0',
			true
		);
	}

	/**
	 * Add season switcher data to menu script.
	 *
	 * @param string $handle Script handle.
	 * @return void
	 */
	private function localize_menu_script( $handle ) {
		$cookie = SeasonService::get_client_cookie_config();

		wp_localize_script(
			$handle,
			'aptoxSeason',
			array(
				'cookieName' => $cookie['name'],
				'cookiePath' => $cookie['path'],
			)
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
				'iconOutline' => get_template_directory_uri() . '/assets/icons/ui/actions/ui-action-favorite-outline.svg',
				'iconFilled'  => get_template_directory_uri() . '/assets/icons/ui/actions/ui-action-favorite-filled.svg',
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
