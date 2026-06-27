<?php
/**
 * XML sitemap configuration aligned with Aptox content architecture.
 *
 * @package Aptox
 */

namespace Aptox\Services;

class SitemapService {
	/**
	 * Post types exposed in the XML sitemap.
	 */
	private const POST_TYPES = array(
		'page',
		'casas',
		'receitas',
		'celebracoes',
		'loja',
	);

	/**
	 * Taxonomies exposed in the XML sitemap.
	 */
	private const TAXONOMIES = array(
		'casa_categoria',
		'casa',
		'receita_categoria',
		'receita_tag',
		'receita',
		'celebracao_categoria',
		'celebrar',
		'loja_categoria',
		'post_tag',
	);

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		if ( $this->is_external_sitemap_active() ) {
			return;
		}

		add_filter( 'wp_sitemaps_enabled', '__return_true' );
		add_filter( 'wp_sitemaps_post_types', array( $this, 'filter_post_types' ) );
		add_filter( 'wp_sitemaps_taxonomies', array( $this, 'filter_taxonomies' ) );
		add_filter( 'wp_sitemaps_users', '__return_empty_array' );
		add_filter( 'wp_sitemaps_posts_query_args', array( $this, 'filter_posts_query_args' ), 10, 2 );
		add_filter( 'wp_sitemaps_taxonomies_query_args', array( $this, 'filter_taxonomies_query_args' ), 10, 2 );
		add_action( 'wp_sitemaps_init', array( $this, 'register_providers' ) );
	}

	/**
	 * Whether a popular SEO plugin is actively generating its own sitemap.
	 *
	 * @return bool
	 */
	private function is_external_sitemap_active() {
		return $this->is_yoast_sitemap_active() || $this->is_rank_math_sitemap_active();
	}

	/**
	 * @return bool
	 */
	private function is_yoast_sitemap_active() {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			return false;
		}

		if ( class_exists( '\WPSEO_Options' ) ) {
			return (bool) \WPSEO_Options::get( 'enable_xml_sitemap' );
		}

		$options = get_option( 'wpseo' );

		return is_array( $options ) && ! empty( $options['enable_xml_sitemap'] );
	}

	/**
	 * @return bool
	 */
	private function is_rank_math_sitemap_active() {
		if ( ! defined( 'RANK_MATH_VERSION' ) ) {
			return false;
		}

		if ( class_exists( '\RankMath\Helper' ) ) {
			return (bool) \RankMath\Helper::is_module_active( 'sitemap' );
		}

		$modules = get_option( 'rank_math_modules', array() );

		return is_array( $modules ) && in_array( 'sitemap', $modules, true );
	}

	/**
	 * Limit sitemap post types to theme content.
	 *
	 * @param array<string, \WP_Post_Type> $post_types Registered post types.
	 * @return array<string, \WP_Post_Type>
	 */
	public function filter_post_types( $post_types ) {
		$allowed = array();

		foreach ( self::POST_TYPES as $post_type ) {
			if ( isset( $post_types[ $post_type ] ) ) {
				$allowed[ $post_type ] = $post_types[ $post_type ];
			}
		}

		return $allowed;
	}

	/**
	 * Limit sitemap taxonomies to theme navigation taxonomies.
	 *
	 * @param array<string, \WP_Taxonomy> $taxonomies Registered taxonomies.
	 * @return array<string, \WP_Taxonomy>
	 */
	public function filter_taxonomies( $taxonomies ) {
		$allowed = array();

		foreach ( self::TAXONOMIES as $taxonomy ) {
			if ( taxonomy_exists( $taxonomy ) && isset( $taxonomies[ $taxonomy ] ) ) {
				$allowed[ $taxonomy ] = $taxonomies[ $taxonomy ];
			}
		}

		return $allowed;
	}

	/**
	 * Keep sitemap entries to public, indexable content.
	 *
	 * @param array<string, mixed> $args      Query args.
	 * @param string               $post_type Post type slug.
	 * @return array<string, mixed>
	 */
	public function filter_posts_query_args( $args, $post_type ) {
		$args['post_status'] = 'publish';

		if ( 'page' === $post_type ) {
			$args['post__not_in'] = array_merge(
				array_map( 'intval', (array) ( $args['post__not_in'] ?? array() ) ),
				$this->get_excluded_page_ids()
			);
		}

		return $args;
	}

	/**
	 * Only expose taxonomy terms that currently have content.
	 *
	 * @param array<string, mixed> $args     Query args.
	 * @param string               $taxonomy Taxonomy slug.
	 * @return array<string, mixed>
	 */
	public function filter_taxonomies_query_args( $args, $taxonomy ) {
		unset( $taxonomy );

		$args['hide_empty'] = true;

		return $args;
	}

	/**
	 * Register supplemental sitemap providers.
	 *
	 * @param \WP_Sitemaps $sitemaps Sitemaps server.
	 * @return void
	 */
	public function register_providers( $sitemaps ) {
		if ( ! class_exists( '\WP_Sitemaps_Provider', false ) ) {
			return;
		}

		require_once __DIR__ . '/SitemapHubsProvider.php';

		$sitemaps->registry->add_provider( 'aptoxhubs', new SitemapHubsProvider() );
	}

	/**
	 * Resolve page IDs that should stay out of the sitemap.
	 *
	 * @return array<int>
	 */
	private function get_excluded_page_ids() {
		$excluded = array();

		foreach ( array( 'privacy-policy' ) as $slug ) {
			$page = get_page_by_path( $slug );

			if ( $page instanceof \WP_Post ) {
				$excluded[] = (int) $page->ID;
			}
		}

		return array_values( array_unique( array_filter( $excluded ) ) );
	}

	/**
	 * Resolve supplemental hub URLs not already listed as pages.
	 *
	 * @return array<int, array{loc: string, lastmod?: string}>
	 */
	public static function get_hub_urls() {
		$entries = array();

		if ( 'posts' === get_option( 'show_on_front' ) ) {
			$entries[] = array(
				'loc' => home_url( '/' ),
			);
		}

		$page_urls = self::get_published_page_url_lookup();

		foreach ( self::get_post_type_archive_candidates() as $archive_url ) {
			if ( self::is_url_covered_by_pages( $archive_url, $page_urls ) ) {
				continue;
			}

			$entries[] = array(
				'loc' => $archive_url,
			);
		}

		return self::dedupe_entries( $entries );
	}

	/**
	 * @return array<string, bool>
	 */
	private static function get_published_page_url_lookup() {
		$pages = get_posts(
			array(
				'post_type'              => 'page',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		$lookup = array();

		foreach ( $pages as $page_id ) {
			$url = get_permalink( (int) $page_id );

			if ( ! $url ) {
				continue;
			}

			$lookup[ self::normalize_sitemap_url( $url ) ] = true;
		}

		return $lookup;
	}

	/**
	 * @return array<int, string>
	 */
	private static function get_post_type_archive_candidates() {
		$candidates = array();

		foreach ( array( 'casas', 'receitas', 'celebracoes', 'loja' ) as $post_type ) {
			$archive_url = get_post_type_archive_link( $post_type );

			if ( $archive_url ) {
				$candidates[] = (string) $archive_url;
			}
		}

		return $candidates;
	}

	/**
	 * @param string               $url       Candidate URL.
	 * @param array<string, bool>  $page_urls Published page lookup.
	 * @return bool
	 */
	private static function is_url_covered_by_pages( $url, array $page_urls ) {
		return isset( $page_urls[ self::normalize_sitemap_url( $url ) ] );
	}

	/**
	 * @param string $url URL.
	 * @return string
	 */
	private static function normalize_sitemap_url( $url ) {
		return untrailingslashit( strtolower( (string) $url ) );
	}

	/**
	 * @param array<int, array{loc: string, lastmod?: string}> $entries Sitemap entries.
	 * @return array<int, array{loc: string, lastmod?: string}>
	 */
	private static function dedupe_entries( array $entries ) {
		$seen    = array();
		$unique  = array();

		foreach ( $entries as $entry ) {
			$loc = isset( $entry['loc'] ) ? esc_url_raw( (string) $entry['loc'] ) : '';

			if ( '' === $loc || isset( $seen[ $loc ] ) ) {
				continue;
			}

			$seen[ $loc ] = true;
			$unique[]     = $entry;
		}

		return $unique;
	}
}
