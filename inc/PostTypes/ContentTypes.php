<?php
/**
 * Register content types, taxonomies and meta.
 *
 * @package Aptox
 */

namespace Aptox\PostTypes;

class ContentTypes {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'register_post_type_args', array( $this, 'filter_post_type_admin_menu' ), 10, 2 );
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( $this, 'reorder_admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'remove_default_posts_menu' ), 999 );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'init', array( $this, 'register_global_tags' ), 11 );
		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'template_redirect', array( $this, 'resolve_loja_category_request' ), 0 );
		add_action( 'template_redirect', array( $this, 'redirect_legacy_loja_single_urls' ), 1 );
		add_filter( 'pre_handle_404', array( $this, 'pre_handle_loja_category_404' ), 10, 2 );
		add_filter( 'template_include', array( $this, 'include_loja_category_template' ), 99 );
		add_action( 'after_switch_theme', array( $this, 'flush_rewrites_on_switch' ) );
		add_action( 'init', array( $this, 'maybe_flush_loja_rewrites' ), 99 );
	}

	/**
	 * Register canonical post types used by templates.
	 *
	 * @return void
	 */
	public function register_post_types() {
		$this->register_post_type_if_missing(
			'casas',
			array(
				'singular'     => 'Casa',
				'plural'       => 'Casa',
				'single_slug'  => 'casa',
				'archive_slug' => 'casas',
				'menu_icon'    => $this->get_post_type_menu_icon( 'casas' ),
			)
		);

		$this->register_post_type_if_missing(
			'receitas',
			array(
				'singular'     => 'Receita',
				'plural'       => 'Receitas',
				'single_slug'  => 'receita',
				'archive_slug' => 'receitas',
				'menu_icon'    => $this->get_post_type_menu_icon( 'receitas' ),
			)
		);

		$this->register_post_type_if_missing(
			'celebracoes',
			array(
				'singular'     => 'Celebração',
				'plural'       => 'Celebrações',
				'single_slug'  => 'celebre',
				'archive_slug' => 'celebracoes',
				'menu_icon'    => $this->get_post_type_menu_icon( 'celebracoes' ),
			)
		);

		$this->register_post_type_if_missing(
			'loja',
			array(
				'singular'     => 'Produto',
				'plural'       => 'Loja',
				'single_slug'  => 'produto',
				'archive_slug' => 'loja',
				'menu_icon'    => $this->get_post_type_menu_icon( 'loja' ),
			)
		);
	}

	/**
	 * Register dedicated taxonomies used by templates/components.
	 *
	 * @return void
	 */
	public function register_taxonomies() {
		$this->register_taxonomy_if_missing(
			'casa_categoria',
			array( 'casas' ),
			'Categoria de Casa',
			'Categorias de Casa',
			array(
				'slug' => 'casas',
			)
		);
		$this->register_taxonomy_if_missing(
			'receita_categoria',
			array( 'receitas' ),
			'Categoria de Receita',
			'Categorias de Receita',
			array(
				'slug' => 'receitas',
			)
		);
		$this->register_taxonomy_if_missing(
			'receita_tag',
			array( 'receitas' ),
			'Tag de Receita',
			'Tags de Receita',
			array(
				'slug'         => 'receitas/tag',
				'hierarchical' => false,
			)
		);
		$this->register_taxonomy_if_missing(
			'celebracao_categoria',
			array( 'celebracoes' ),
			'Categoria de Celebração',
			'Categorias de Celebração',
			array(
				'slug' => 'celebracoes',
			)
		);
		$this->register_taxonomy_if_missing(
			'loja_categoria',
			array( 'loja' ),
			'Categoria de Loja',
			'Categorias de Loja',
			array(
				'slug' => 'loja',
			)
		);
	}

	/**
	 * Ensure global post tags are available in all custom post types.
	 *
	 * @return void
	 */
	public function register_global_tags() {
		$post_types = array( 'casas', 'receitas', 'celebracoes', 'loja' );

		foreach ( $post_types as $post_type ) {
			register_taxonomy_for_object_type( 'post_tag', $post_type );
		}
	}

	/**
	 * Register post and user meta keys.
	 *
	 * @return void
	 */
	public function register_meta() {
		register_post_meta(
			'',
			'_post_likes',
			array(
				'type'              => 'integer',
				'single'            => true,
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'auth_callback'     => '__return_true',
				'show_in_rest'      => true,
			)
		);

		register_post_meta(
			'receitas',
			'codigo_receita',
			array(
				'type'              => 'integer',
				'single'            => true,
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			)
		);

		register_post_meta(
			'loja',
			'link_compra',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			)
		);

		register_meta(
			'user',
			'_aptox_liked_posts',
			array(
				'type'              => 'array',
				'single'            => true,
				'default'           => array(),
				'sanitize_callback' => array( $this, 'sanitize_liked_posts' ),
				'auth_callback'     => '__return_true',
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Sanitize liked posts user meta.
	 *
	 * @param mixed $value Meta value.
	 * @return array<int>
	 */
	public function sanitize_liked_posts( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		return array_values( array_unique( array_map( 'absint', $value ) ) );
	}

	/**
	 * Conditionally register a post type.
	 *
	 * @param string              $post_type Post type key.
	 * @param array<string,mixed> $config Type labels and slugs.
	 * @return void
	 */
	private function register_post_type_if_missing( $post_type, $config ) {
		if ( post_type_exists( $post_type ) ) {
			return;
		}

		$args = array(
			'labels'       => array(
				'name'          => $config['plural'],
				'singular_name' => $config['singular'],
			),
			'public'       => true,
			'has_archive'  => $config['archive_slug'],
			'show_in_rest' => true,
			'rewrite'      => array(
				'slug'       => $config['single_slug'],
				'with_front' => false,
			),
			'taxonomies'   => array( 'post_tag' ),
			'supports'     => array(
				'title',
				'editor',
				'thumbnail',
				'excerpt',
			),
		);

		if ( ! empty( $config['menu_icon'] ) ) {
			$args['menu_icon'] = $config['menu_icon'];
		}

		$menu_position = $this->get_post_type_menu_position( $post_type );

		if ( null !== $menu_position ) {
			$args['menu_position'] = $menu_position;
		}

		register_post_type( $post_type, $args );
	}

	/**
	 * Ensure section icons and menu order in the admin for theme post types.
	 *
	 * @param array<string,mixed> $args Post type registration args.
	 * @param string              $post_type Post type key.
	 * @return array<string,mixed>
	 */
	public function filter_post_type_admin_menu( $args, $post_type ) {
		$icon = $this->get_post_type_menu_icon( $post_type );

		if ( '' !== $icon ) {
			$args['menu_icon'] = $icon;
		}

		$menu_position = $this->get_post_type_menu_position( $post_type );

		if ( null !== $menu_position ) {
			$args['menu_position'] = $menu_position;
		}

		return $args;
	}

	/**
	 * Hide the default WordPress Posts menu when the site uses custom post types only.
	 *
	 * @return void
	 */
	public function remove_default_posts_menu() {
		remove_menu_page( 'edit.php' );
	}

	/**
	 * Keep theme content menus above default Posts in wp-admin.
	 *
	 * @param array<int,string> $menu_order Current menu order.
	 * @return array<int,string>
	 */
	public function reorder_admin_menu( $menu_order ) {
		if ( ! is_array( $menu_order ) ) {
			return $menu_order;
		}

		$theme_menus = array(
			'edit.php?post_type=casas',
			'edit.php?post_type=receitas',
			'edit.php?post_type=celebracoes',
			'edit.php?post_type=loja',
		);

		$menu_order = array_values(
			array_diff( $menu_order, $theme_menus )
		);

		$posts_index = array_search( 'edit.php', $menu_order, true );

		if ( false === $posts_index ) {
			return array_merge( $theme_menus, $menu_order );
		}

		return array_merge(
			array_slice( $menu_order, 0, $posts_index ),
			$theme_menus,
			array_slice( $menu_order, $posts_index )
		);
	}

	/**
	 * Resolve admin menu position for a theme post type.
	 *
	 * Posts uses position 5 in wp-admin.
	 *
	 * @param string $post_type Post type key.
	 * @return float|null
	 */
	private function get_post_type_menu_position( $post_type ) {
		$positions = array(
			'casas'       => 4,
			'receitas'    => 4.1,
			'celebracoes' => 4.2,
			'loja'        => 4.3,
		);

		return $positions[ $post_type ] ?? null;
	}

	/**
	 * Resolve dashicon for a theme post type admin menu.
	 *
	 * @param string $post_type Post type key.
	 * @return string
	 */
	private function get_post_type_menu_icon( $post_type ) {
		$icons = array(
			'casas'       => 'dashicons-admin-home',
			'receitas'    => 'dashicons-carrot',
			'celebracoes' => 'dashicons-calendar-alt',
			'loja'        => 'dashicons-cart',
		);

		return $icons[ $post_type ] ?? '';
	}

	/**
	 * Conditionally register a taxonomy.
	 *
	 * @param string   $taxonomy Taxonomy key.
	 * @param string[] $post_types Post types.
	 * @param string   $singular Singular label.
	 * @param string   $plural Plural label.
	 * @param array<string,mixed> $config Optional taxonomy config.
	 * @return void
	 */
	private function register_taxonomy_if_missing( $taxonomy, array $post_types, $singular, $plural, array $config = array() ) {
		if ( taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$hierarchical = array_key_exists( 'hierarchical', $config )
			? (bool) $config['hierarchical']
			: true;

		register_taxonomy(
			$taxonomy,
			$post_types,
			array(
				'labels'            => array(
					'name'          => $plural,
					'singular_name' => $singular,
				),
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'       => isset( $config['slug'] ) ? $config['slug'] : $taxonomy,
					'with_front' => false,
				),
				'hierarchical'      => $hierarchical,
			)
		);
	}

	/**
	 * Resolve /loja/{categoria}/ when taxonomy rewrites are stale.
	 *
	 * @return void
	 */
	public function resolve_loja_category_request() {
		if ( is_admin() || is_tax( 'loja_categoria' ) ) {
			return;
		}

		$slug = $this->get_loja_path_slug();

		if ( '' === $slug ) {
			return;
		}

		$term = get_term_by( 'slug', $slug, 'loja_categoria' );

		if ( ! $term || is_wp_error( $term ) ) {
			return;
		}

		global $wp_query, $wp_the_query;

		$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

		$wp_the_query = new \WP_Query(
			array(
				'post_type'              => 'loja',
				'post_status'            => 'publish',
				'posts_per_page'         => 12,
				'paged'                  => $paged,
				'ignore_sticky_posts'    => true,
				'tax_query'              => array(
					array(
						'taxonomy' => 'loja_categoria',
						'field'    => 'term_id',
						'terms'    => array( (int) $term->term_id ),
					),
				),
			)
		);

		$wp_query = $wp_the_query;

		$wp_the_query->is_tax            = true;
		$wp_the_query->is_archive        = true;
		$wp_the_query->is_home           = false;
		$wp_the_query->is_singular       = false;
		$wp_the_query->is_404            = false;
		$wp_the_query->queried_object    = $term;
		$wp_the_query->queried_object_id = (int) $term->term_id;
		$wp_the_query->set( 'taxonomy', 'loja_categoria' );
		$wp_the_query->set( 'term', $term->slug );
		$wp_the_query->set( 'loja_categoria', $term->slug );

		status_header( 200 );
		nocache_headers();
	}

	/**
	 * Prevent WordPress from treating valid Loja category URLs as 404.
	 *
	 * @param bool      $preempt  Whether to short-circuit default 404 handling.
	 * @param \WP_Query $wp_query Main query instance.
	 * @return bool
	 */
	public function pre_handle_loja_category_404( $preempt, $wp_query ) {
		if ( is_admin() || ! $wp_query->is_main_query() ) {
			return $preempt;
		}

		$slug = $this->get_loja_path_slug();

		if ( '' === $slug ) {
			return $preempt;
		}

		$term = get_term_by( 'slug', $slug, 'loja_categoria' );

		if ( $term && ! is_wp_error( $term ) ) {
			return true;
		}

		return $preempt;
	}

	/**
	 * Force taxonomy template for resolved Loja category requests.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public function include_loja_category_template( $template ) {
		if ( ! is_tax( 'loja_categoria' ) ) {
			return $template;
		}

		$found = locate_template( 'taxonomy-loja_categoria.php' );

		return $found ? $found : $template;
	}

	/**
	 * Extract the second segment from /loja/{slug}/ requests.
	 *
	 * @return string
	 */
	private function get_loja_path_slug() {
		$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );

		if ( ! is_string( $path ) ) {
			return '';
		}

		$home_path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );

		if ( is_string( $home_path ) && '/' !== $home_path && 0 === strpos( $path, $home_path ) ) {
			$path = substr( $path, strlen( $home_path ) );
		}

		$path = trim( $path, '/' );

		if ( ! preg_match( '#^loja/([^/]+)/?$#', $path, $matches ) ) {
			return '';
		}

		return sanitize_title( $matches[1] );
	}

	/**
	 * Redirect legacy single product URLs that used the archive slug.
	 *
	 * @return void
	 */
	public function redirect_legacy_loja_single_urls() {
		if ( is_admin() ) {
			return;
		}

		$slug = $this->get_loja_path_slug();

		if ( '' === $slug ) {
			return;
		}

		$term = get_term_by( 'slug', $slug, 'loja_categoria' );

		if ( $term && ! is_wp_error( $term ) ) {
			return;
		}

		$posts = get_posts(
			array(
				'name'                   => $slug,
				'post_type'              => 'loja',
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( empty( $posts ) ) {
			return;
		}

		wp_safe_redirect( get_permalink( $posts[0] ), 301 );
		exit;
	}

	/**
	 * Flush rewrite rules once after Loja taxonomy routing changes.
	 *
	 * @return void
	 */
	public function maybe_flush_loja_rewrites() {
		if ( 'v2' === get_option( 'aptox_loja_rewrites', '' ) ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( 'aptox_loja_rewrites', 'v2', false );
	}

	/**
	 * Flush rewrite rules after theme switch.
	 *
	 * @return void
	 */
	public function flush_rewrites_on_switch() {
		$this->register_post_types();
		$this->register_taxonomies();
		$this->register_global_tags();
		flush_rewrite_rules();
	}
}
