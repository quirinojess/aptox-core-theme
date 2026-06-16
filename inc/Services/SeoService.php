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

		if ( ! is_archive() && ! is_search() && ! is_home() ) {
			return;
		}

		global $wp_query;

		$paged     = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$max_pages = (int) $wp_query->max_num_pages;

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
					'url'  => home_url( '/loja/' ),
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
					'url'  => get_post_type_archive_link( 'loja' ) ?: home_url( '/loja/' ),
				);
		}

		return null;
	}

	/**
	 * @param int $page Page number.
	 * @return string
	 */
	private function get_pagination_url( $page ) {
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
}
