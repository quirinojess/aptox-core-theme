<?php
/**
 * Structured data and crawlability helpers.
 *
 * @package Aptox
 */

namespace Aptox\Services;

class SeoService {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_head', array( $this, 'render_structured_data' ), 1 );
		add_action( 'wp_head', array( $this, 'render_pagination_links' ), 2 );
		add_action( 'wp_head', array( $this, 'render_meta_tags' ), 3 );
		add_filter( 'document_title_parts', array( $this, 'filter_document_title_parts' ), 20 );
		add_filter( 'wp_robots', array( $this, 'filter_robots' ) );
	}

	/**
	 * Prevent thin search result pages from being indexed.
	 *
	 * @param array<string, bool|string> $robots Robots directives.
	 * @return array<string, bool|string>
	 */
	public function filter_robots( $robots ) {
		if ( is_search() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}

		return $robots;
	}

	/**
	 * Output rel prev/next links for paginated archives.
	 *
	 * @return void
	 */
	public function render_pagination_links() {
		if ( is_admin() ) {
			return;
		}

		if ( ! is_archive() && ! is_search() && ! is_home() && ! $this->is_loja_listing_context() ) {
			return;
		}

		$paged     = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$max_pages = $this->is_loja_listing_context()
			? $this->get_loja_listing_max_pages()
			: (int) $GLOBALS['wp_query']->max_num_pages;

		if ( $max_pages < 2 ) {
			return;
		}

		if ( $paged > 1 ) {
			printf(
				'<link rel="prev" href="%s">' . "\n",
				esc_url( $this->get_pagination_url( $paged - 1 ) )
			);
		}

		if ( $paged < $max_pages ) {
			printf(
				'<link rel="next" href="%s">' . "\n",
				esc_url( $this->get_pagination_url( $paged + 1 ) )
			);
		}
	}

	/**
	 * Output JSON-LD graph for Organization, WebSite and BreadcrumbList.
	 *
	 * @return void
	 */
	public function render_structured_data() {
		$graph = array(
			$this->get_organization_schema(),
			$this->get_website_schema(),
		);

		$breadcrumb = $this->get_breadcrumb_schema();

		if ( null !== $breadcrumb ) {
			$graph[] = $breadcrumb;
		}

		foreach ( $this->get_loja_structured_data() as $schema ) {
			$graph[] = $schema;
		}

		$graph = array_values(
			array_filter(
				$graph,
				static function ( $item ) {
					return is_array( $item ) && ! empty( $item );
				}
			)
		);

		if ( empty( $graph ) ) {
			return;
		}

		$payload = array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		);

		echo '<script type="application/ld+json">';
		echo wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		echo '</script>' . "\n";
	}

	/**
	 * @return array<string, mixed>
	 */
	private function get_organization_schema() {
		return array(
			'@type' => 'Organization',
			'@id'   => home_url( '/#organization' ),
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
			'logo'  => array(
				'@type' => 'ImageObject',
				'url'   => get_template_directory_uri() . '/assets/icons/ui/logo.svg',
			),
			'sameAs' => array(
				'https://www.instagram.com/aptox/',
				'https://br.pinterest.com/aptoxblog/',
				'https://www.youtube.com/@aptoxblog',
				'https://www.tiktok.com/@aptoxblog',
				'https://www.facebook.com/aptox',
			),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function get_website_schema() {
		return array(
			'@type'     => 'WebSite',
			'@id'       => home_url( '/#website' ),
			'url'       => home_url( '/' ),
			'name'      => get_bloginfo( 'name' ),
			'publisher' => array(
				'@id' => home_url( '/#organization' ),
			),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function get_breadcrumb_schema() {
		$items = $this->get_breadcrumb_items();

		if ( count( $items ) < 2 ) {
			return null;
		}

		$list_items = array();

		foreach ( $items as $index => $item ) {
			$list_items[] = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => $item['name'],
				'item'     => $item['url'],
			);
		}

		return array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $list_items,
		);
	}

	/**
	 * @return array<int, array{name: string, url: string}>
	 */
	private function get_breadcrumb_items() {
		$items = array(
			array(
				'name' => __( 'Home', 'aptox' ),
				'url'  => home_url( '/' ),
			),
		);

		if ( is_singular( 'loja' ) ) {
			return $this->append_single_loja_breadcrumb_items( $items );
		}

		if ( is_page_template( 'templates/page-loja.php' ) ) {
			return $this->append_loja_landing_breadcrumb_items( $items );
		}

		if ( is_tax() ) {
			return $this->append_taxonomy_breadcrumb_items( $items );
		}

		if ( is_post_type_archive() ) {
			return $this->append_post_type_archive_breadcrumb_items( $items );
		}

		if ( is_category() || is_tag() ) {
			return $this->append_default_taxonomy_breadcrumb_items( $items );
		}

		if ( is_archive() && ! is_search() ) {
			return $this->append_generic_archive_breadcrumb_items( $items );
		}

		return array();
	}

	/**
	 * @param array<int, array{name: string, url: string}> $items Existing items.
	 * @return array<int, array{name: string, url: string}>
	 */
	private function append_taxonomy_breadcrumb_items( array $items ) {
		$term = get_queried_object();

		if ( ! $term instanceof \WP_Term ) {
			return array();
		}

		$section = $this->get_section_for_taxonomy( $term->taxonomy );

		if ( null === $section ) {
			return array();
		}

		$items[] = array(
			'name' => $section['name'],
			'url'  => $section['url'],
		);

		$term_link = get_term_link( $term );

		if ( is_wp_error( $term_link ) ) {
			return array();
		}

		$items[] = array(
			'name' => $term->name,
			'url'  => $term_link,
		);

		return $items;
	}

	/**
	 * @param array<int, array{name: string, url: string}> $items Existing items.
	 * @return array<int, array{name: string, url: string}>
	 */
	private function append_post_type_archive_breadcrumb_items( array $items ) {
		$post_type = get_query_var( 'post_type' );

		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}

		$section = $this->get_section_for_post_type( (string) $post_type );

		if ( null === $section ) {
			return array();
		}

		$items[] = array(
			'name' => $section['name'],
			'url'  => $section['url'],
		);

		return $items;
	}

	/**
	 * @param array<int, array{name: string, url: string}> $items Existing items.
	 * @return array<int, array{name: string, url: string}>
	 */
	private function append_default_taxonomy_breadcrumb_items( array $items ) {
		$term = get_queried_object();

		if ( ! $term instanceof \WP_Term ) {
			return array();
		}

		$term_link = get_term_link( $term );

		if ( is_wp_error( $term_link ) ) {
			return array();
		}

		$items[] = array(
			'name' => $term->name,
			'url'  => $term_link,
		);

		return $items;
	}

	/**
	 * @param array<int, array{name: string, url: string}> $items Existing items.
	 * @return array<int, array{name: string, url: string}>
	 */
	private function append_generic_archive_breadcrumb_items( array $items ) {
		$title = wp_strip_all_tags( get_the_archive_title() );

		if ( '' === $title ) {
			return array();
		}

		$items[] = array(
			'name' => $title,
			'url'  => $this->get_current_url(),
		);

		return $items;
	}

	/**
	 * @param string $taxonomy Taxonomy slug.
	 * @return array{name: string, url: string}|null
	 */
	private function get_section_for_taxonomy( $taxonomy ) {
		switch ( $taxonomy ) {
			case 'casa_categoria':
				return array(
					'name' => __( 'Casa', 'aptox' ),
					'url'  => home_url( '/casas/' ),
				);

			case 'receita_categoria':
			case 'receita_tag':
				return array(
					'name' => __( 'Receitas', 'aptox' ),
					'url'  => function_exists( 'aptox_get_receitas_archive_url' )
						? aptox_get_receitas_archive_url()
						: home_url( '/receitas/' ),
				);

			case 'celebracao_categoria':
				return array(
					'name' => __( 'Celebrações', 'aptox' ),
					'url'  => home_url( '/celebracoes/' ),
				);

			case 'loja_categoria':
				return array(
					'name' => __( 'Loja', 'aptox' ),
					'url'  => function_exists( 'aptox_get_loja_archive_url' )
						? aptox_get_loja_archive_url()
						: home_url( '/loja/' ),
				);
		}

		return null;
	}

	/**
	 * @param string $post_type Post type slug.
	 * @return array{name: string, url: string}|null
	 */
	private function get_section_for_post_type( $post_type ) {
		switch ( $post_type ) {
			case 'casas':
				return array(
					'name' => __( 'Casa', 'aptox' ),
					'url'  => get_post_type_archive_link( 'casas' ) ?: home_url( '/casas/' ),
				);

			case 'receitas':
				return array(
					'name' => __( 'Receitas', 'aptox' ),
					'url'  => function_exists( 'aptox_get_receitas_archive_url' )
						? aptox_get_receitas_archive_url()
						: home_url( '/receitas/' ),
				);

			case 'celebracoes':
				return array(
					'name' => __( 'Celebrações', 'aptox' ),
					'url'  => get_post_type_archive_link( 'celebracoes' ) ?: home_url( '/celebracoes/' ),
				);

			case 'loja':
				return array(
					'name' => __( 'Loja', 'aptox' ),
					'url'  => function_exists( 'aptox_get_loja_archive_url' )
						? aptox_get_loja_archive_url()
						: home_url( '/loja/' ),
				);
		}

		return null;
	}

	/**
	 * @param int $page Page number.
	 * @return string
	 */
	private function get_pagination_url( $page ) {
		if ( $this->is_loja_listing_context() ) {
			return $this->get_loja_pagination_url( $page );
		}

		$tag_slug = function_exists( 'aptox_get_receita_tag_query_slug' )
			? aptox_get_receita_tag_query_slug()
			: '';

		$url = get_pagenum_link( $page );

		if ( '' !== $tag_slug ) {
			$url = add_query_arg( 'tag', $tag_slug, $url );
		}

		return $url;
	}

	/**
	 * @return string
	 */
	private function get_current_url() {
		global $wp;

		$paged = max( 0, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$path  = is_object( $wp ) ? (string) $wp->request : '';

		if ( '' === $path ) {
			return home_url( '/' );
		}

		$url = home_url( user_trailingslashit( $path ) );

		if ( $paged > 1 ) {
			$url = user_trailingslashit( trailingslashit( $url ) . 'page/' . $paged );
		}

		$tag_slug = function_exists( 'aptox_get_receita_tag_query_slug' )
			? aptox_get_receita_tag_query_slug()
			: '';

		if ( '' !== $tag_slug ) {
			$url = add_query_arg( 'tag', $tag_slug, $url );
		}

		return $url;
	}

	/**
	 * Customize document titles for Loja templates.
	 *
	 * @param array<string, string> $parts Title parts.
	 * @return array<string, string>
	 */
	public function filter_document_title_parts( $parts ) {
		if ( is_tax( 'loja_categoria' ) ) {
			$term = get_queried_object();

			if ( $term instanceof \WP_Term ) {
				$parts['title'] = $term->name . ' | ' . __( 'Loja', 'aptox' );
			}

			return $parts;
		}

		if ( is_singular( 'loja' ) ) {
			$parts['title'] = get_the_title() . ' | ' . __( 'Loja', 'aptox' );
			return $parts;
		}

		if ( is_page_template( 'templates/page-loja.php' ) ) {
			$parts['title'] = get_the_title() ?: __( 'Loja', 'aptox' );
			return $parts;
		}

		if ( is_post_type_archive( 'loja' ) ) {
			$parts['title'] = __( 'Loja', 'aptox' );
		}

		return $parts;
	}

	/**
	 * Output meta description, canonical and Open Graph tags for Loja pages.
	 *
	 * @return void
	 */
	public function render_meta_tags() {
		if ( is_admin() || ! $this->should_render_theme_meta_tags() || ! $this->is_loja_seo_context() ) {
			return;
		}

		$description = $this->get_loja_meta_description();
		$canonical   = $this->get_loja_canonical_url();
		$title       = wp_get_document_title();
		$image       = $this->get_loja_social_image();
		$og_type     = is_singular( 'loja' ) ? 'product' : 'website';

		if ( '' !== $description ) {
			printf(
				'<meta name="description" content="%s">' . "\n",
				esc_attr( $description )
			);
		}

		if ( '' !== $canonical ) {
			printf(
				'<link rel="canonical" href="%s">' . "\n",
				esc_url( $canonical )
			);
		}

		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $og_type ) );

		if ( '' !== $canonical ) {
			printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $canonical ) );
		}

		if ( '' !== $description ) {
			printf(
				'<meta property="og:description" content="%s">' . "\n",
				esc_attr( $description )
			);
		}

		if ( '' !== $image ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		}

		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	}

	/**
	 * @return bool
	 */
	private function should_render_theme_meta_tags() {
		return ! defined( 'WPSEO_VERSION' ) && ! defined( 'RANK_MATH_VERSION' );
	}

	/**
	 * @return bool
	 */
	private function is_loja_seo_context() {
		return is_singular( 'loja' )
			|| is_tax( 'loja_categoria' )
			|| is_post_type_archive( 'loja' )
			|| is_page_template( 'templates/page-loja.php' );
	}

	/**
	 * @return bool
	 */
	private function is_loja_listing_context() {
		return is_tax( 'loja_categoria' )
			|| is_post_type_archive( 'loja' )
			|| is_page_template( 'templates/page-loja.php' );
	}

	/**
	 * @param array<int, array{name: string, url: string}> $items Existing items.
	 * @return array<int, array{name: string, url: string}>
	 */
	private function append_single_loja_breadcrumb_items( array $items ) {
		$section = $this->get_section_for_post_type( 'loja' );

		if ( null === $section ) {
			return array();
		}

		$items[] = $section;
		$items[] = array(
			'name' => get_the_title(),
			'url'  => get_permalink(),
		);

		return $items;
	}

	/**
	 * @param array<int, array{name: string, url: string}> $items Existing items.
	 * @return array<int, array{name: string, url: string}>
	 */
	private function append_loja_landing_breadcrumb_items( array $items ) {
		$section = $this->get_section_for_post_type( 'loja' );

		if ( null === $section ) {
			return array();
		}

		$items[] = array(
			'name' => get_the_title() ?: $section['name'],
			'url'  => $section['url'],
		);

		return $items;
	}

	/**
	 * @return string
	 */
	private function get_loja_meta_description() {
		if ( is_tax( 'loja_categoria' ) ) {
			$term = get_queried_object();

			if ( $term instanceof \WP_Term && ! empty( $term->description ) ) {
				return $this->truncate_description( wp_strip_all_tags( $term->description ) );
			}

			if ( $term instanceof \WP_Term ) {
				return $this->truncate_description(
					sprintf(
						/* translators: %s: category name */
						__( 'Confira nossa seleção de %s na Loja Aptox. Produtos de afiliadas que amamos para casa, mesa e celebrações.', 'aptox' ),
						$term->name
					)
				);
			}
		}

		if ( is_singular( 'loja' ) ) {
			$excerpt = get_the_excerpt();

			if ( '' !== $excerpt ) {
				return $this->truncate_description( wp_strip_all_tags( $excerpt ) );
			}

			return $this->truncate_description( wp_strip_all_tags( get_the_content() ) );
		}

		if ( is_page_template( 'templates/page-loja.php' ) ) {
			$excerpt = get_the_excerpt( get_queried_object_id() );

			if ( '' !== $excerpt ) {
				return $this->truncate_description( wp_strip_all_tags( $excerpt ) );
			}
		}

		return $this->truncate_description(
			__( 'Nossa seleção de produtos especiais na Loja Aptox. Indicações de afiliadas que amamos e usamos no dia a dia — para tornar casa, mesa e celebrações ainda mais bonitas.', 'aptox' )
		);
	}

	/**
	 * @param string $text Description text.
	 * @return string
	 */
	private function truncate_description( $text ) {
		$text = trim( preg_replace( '/\s+/u', ' ', (string) $text ) );

		if ( '' === $text ) {
			return '';
		}

		return wp_html_excerpt( $text, 160, '' );
	}

	/**
	 * @return string
	 */
	private function get_loja_canonical_url() {
		if ( is_singular( 'loja' ) ) {
			return (string) get_permalink();
		}

		if ( is_tax( 'loja_categoria' ) ) {
			$term = get_queried_object();

			if ( $term instanceof \WP_Term ) {
				$term_link = get_term_link( $term );

				if ( ! is_wp_error( $term_link ) ) {
					return $this->append_pagination_to_url( (string) $term_link );
				}
			}
		}

		if ( is_page_template( 'templates/page-loja.php' ) ) {
			$page_id = get_queried_object_id();

			if ( $page_id > 0 ) {
				return $this->append_pagination_to_url( (string) get_permalink( $page_id ) );
			}
		}

		if ( is_post_type_archive( 'loja' ) ) {
			$archive_url = function_exists( 'aptox_get_loja_archive_url' )
				? aptox_get_loja_archive_url()
				: get_post_type_archive_link( 'loja' );

			if ( $archive_url ) {
				return $this->append_pagination_to_url( (string) $archive_url );
			}
		}

		return '';
	}

	/**
	 * @param string $url Base URL.
	 * @return string
	 */
	private function append_pagination_to_url( $url ) {
		$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

		if ( $paged < 2 ) {
			return $url;
		}

		return user_trailingslashit( trailingslashit( $url ) . 'page/' . $paged );
	}

	/**
	 * @return string
	 */
	private function get_loja_social_image() {
		if ( is_singular( 'loja' ) && has_post_thumbnail() ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );

			if ( is_array( $image ) && ! empty( $image[0] ) ) {
				return (string) $image[0];
			}
		}

		$posts = $this->get_loja_listing_posts();

		foreach ( $posts as $post ) {
			if ( ! $post instanceof \WP_Post || ! has_post_thumbnail( $post ) ) {
				continue;
			}

			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'large' );

			if ( is_array( $image ) && ! empty( $image[0] ) ) {
				return (string) $image[0];
			}
		}

		return get_template_directory_uri() . '/assets/icons/ui/logo.svg';
	}

	/**
	 * @return int
	 */
	private function get_loja_listing_max_pages() {
		$query = $this->get_loja_listing_query();

		return (int) $query->max_num_pages;
	}

	/**
	 * @param int $page Page number.
	 * @return string
	 */
	private function get_loja_pagination_url( $page ) {
		$page = max( 1, (int) $page );

		if ( is_page_template( 'templates/page-loja.php' ) ) {
			$page_id = get_queried_object_id();

			if ( $page_id > 0 ) {
				$base = trailingslashit( get_permalink( $page_id ) );

				return 1 === $page ? untrailingslashit( $base ) : user_trailingslashit( $base . 'page/' . $page );
			}
		}

		if ( is_tax( 'loja_categoria' ) ) {
			$term = get_queried_object();

			if ( $term instanceof \WP_Term ) {
				$term_link = get_term_link( $term );

				if ( ! is_wp_error( $term_link ) ) {
					$base = trailingslashit( (string) $term_link );

					return 1 === $page ? untrailingslashit( $base ) : user_trailingslashit( $base . 'page/' . $page );
				}
			}
		}

		if ( is_post_type_archive( 'loja' ) ) {
			$archive_url = get_post_type_archive_link( 'loja' );

			if ( $archive_url ) {
				$base = trailingslashit( (string) $archive_url );

				return 1 === $page ? untrailingslashit( $base ) : user_trailingslashit( $base . 'page/' . $page );
			}
		}

		return get_pagenum_link( $page );
	}

	/**
	 * @return \WP_Query
	 */
	private function get_loja_listing_query() {
		$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$args  = array(
			'post_type'              => 'loja',
			'post_status'            => 'publish',
			'posts_per_page'         => 12,
			'paged'                  => $paged,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		if ( is_tax( 'loja_categoria' ) ) {
			$term = get_queried_object();

			if ( $term instanceof \WP_Term ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'loja_categoria',
						'field'    => 'term_id',
						'terms'    => array( (int) $term->term_id ),
					),
				);
			}
		}

		return new \WP_Query( $args );
	}

	/**
	 * @return array<int, \WP_Post>
	 */
	private function get_loja_listing_posts() {
		if ( is_singular( 'loja' ) ) {
			$post = get_post();

			return $post instanceof \WP_Post ? array( $post ) : array();
		}

		if ( ! $this->is_loja_listing_context() ) {
			return array();
		}

		if ( ( is_tax( 'loja_categoria' ) || is_post_type_archive( 'loja' ) ) && have_posts() ) {
			global $wp_query;

			return is_array( $wp_query->posts ) ? $wp_query->posts : array();
		}

		$query = $this->get_loja_listing_query();
		$posts = is_array( $query->posts ) ? $query->posts : array();

		wp_reset_postdata();

		return $posts;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function get_loja_structured_data() {
		if ( is_singular( 'loja' ) ) {
			$product = $this->get_loja_product_schema();

			return $product ? array( $product ) : array();
		}

		if ( ! $this->is_loja_listing_context() ) {
			return array();
		}

		$item_list = $this->get_loja_item_list_schema();
		$schemas   = array();

		if ( $item_list ) {
			$schemas[] = $item_list;
		}

		$collection = $this->get_loja_collection_page_schema();

		if ( $collection ) {
			$schemas[] = $collection;
		}

		return $schemas;
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function get_loja_product_schema() {
		$post = get_post();

		if ( ! $post instanceof \WP_Post || 'loja' !== $post->post_type ) {
			return null;
		}

		$permalink   = get_permalink( $post );
		$description = $this->get_loja_meta_description();
		$schema      = array(
			'@type'       => 'Product',
			'@id'         => $permalink . '#product',
			'name'        => get_the_title( $post ),
			'url'         => $permalink,
			'description' => $description,
			'brand'       => array(
				'@type' => 'Brand',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		if ( has_post_thumbnail( $post ) ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'large' );

			if ( is_array( $image ) && ! empty( $image[0] ) ) {
				$schema['image'] = (string) $image[0];
			}
		}

		$terms = get_the_terms( $post, 'loja_categoria' );

		if ( is_array( $terms ) && ! empty( $terms ) ) {
			$schema['category'] = $terms[0]->name;
		}

		$link_compra = get_post_meta( $post->ID, 'link_compra', true );

		if ( $link_compra ) {
			$schema['offers'] = array(
				'@type'         => 'Offer',
				'url'           => esc_url_raw( $link_compra ),
				'availability'  => 'https://schema.org/InStock',
				'itemCondition' => 'https://schema.org/NewCondition',
			);
		}

		return $schema;
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function get_loja_item_list_schema() {
		$posts = $this->get_loja_listing_posts();

		if ( empty( $posts ) ) {
			return null;
		}

		$list_items = array();

		foreach ( $posts as $index => $post ) {
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}

			$list_items[] = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'url'      => get_permalink( $post ),
				'name'     => get_the_title( $post ),
			);
		}

		if ( empty( $list_items ) ) {
			return null;
		}

		return array(
			'@type'           => 'ItemList',
			'itemListElement' => $list_items,
		);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function get_loja_collection_page_schema() {
		$url = $this->get_loja_canonical_url();

		if ( '' === $url ) {
			return null;
		}

		$name = __( 'Loja', 'aptox' );

		if ( is_tax( 'loja_categoria' ) ) {
			$term = get_queried_object();

			if ( $term instanceof \WP_Term ) {
				$name = $term->name;
			}
		} elseif ( is_page_template( 'templates/page-loja.php' ) ) {
			$name = get_the_title() ?: $name;
		}

		return array(
			'@type'       => 'CollectionPage',
			'@id'         => $url . '#collection',
			'url'         => $url,
			'name'        => $name,
			'description' => $this->get_loja_meta_description(),
			'isPartOf'    => array(
				'@id' => home_url( '/#website' ),
			),
		);
	}
}
