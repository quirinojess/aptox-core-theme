<?php
/**
 * Resolve context and related posts for 404 pages.
 *
 * @package Aptox
 */

namespace Aptox\Services;

class NotFoundService {
	/**
	 * @return string
	 */
	public static function get_request_path() {
		$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );

		if ( ! is_string( $path ) ) {
			return '';
		}

		$home_path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );

		if ( is_string( $home_path ) && '/' !== $home_path && 0 === strpos( $path, $home_path ) ) {
			$path = substr( $path, strlen( $home_path ) );
		}

		return trim( $path, '/' );
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_context() {
		$segments = array_values(
			array_filter(
				explode( '/', self::get_request_path() ),
				static function ( $segment ) {
					return '' !== $segment;
				}
			)
		);

		$first = sanitize_key( $segments[0] ?? '' );

		$archive_sections = array(
			'celebracoes' => array(
				'post_type' => 'celebracoes',
				'taxonomy'  => 'celebracao_categoria',
			),
			'casas'       => array(
				'post_type' => 'casas',
				'taxonomy'  => 'casa_categoria',
			),
			'receitas'    => array(
				'post_type' => 'receitas',
				'taxonomy'  => 'receita_categoria',
			),
		);

		$single_sections = array(
			'celebre' => 'celebracoes',
			'casa'    => 'casas',
			'receita' => 'receitas',
		);

		if ( isset( $archive_sections[ $first ] ) ) {
			return self::build_section_context( $archive_sections[ $first ], $segments );
		}

		if ( isset( $single_sections[ $first ] ) ) {
			return array(
				'mode'      => 'section',
				'post_type' => $single_sections[ $first ],
				'term'      => null,
			);
		}

		return array(
			'mode' => 'mixed',
		);
	}

	/**
	 * @param array<string, string> $config  Section config.
	 * @param array<int, string>    $segments Request path segments.
	 * @return array<string, mixed>
	 */
	private static function build_section_context( array $config, array $segments ) {
		$term = null;

		if ( 'receitas' === ( $segments[0] ?? '' ) && 'tag' === ( $segments[1] ?? '' ) ) {
			$term_slug = sanitize_title( $segments[2] ?? '' );

			if ( '' !== $term_slug && taxonomy_exists( 'receita_tag' ) ) {
				$term = get_term_by( 'slug', $term_slug, 'receita_tag' );
			}
		} elseif ( ! empty( $segments[1] ) && 'tag' !== $segments[1] ) {
			$term_slug = sanitize_title( $segments[1] );

			if ( '' !== $term_slug && taxonomy_exists( $config['taxonomy'] ) ) {
				$term = get_term_by( 'slug', $term_slug, $config['taxonomy'] );
			}
		}

		if ( $term instanceof \WP_Term ) {
			return array(
				'mode'      => 'section',
				'post_type' => $config['post_type'],
				'term'      => $term,
			);
		}

		return array(
			'mode'      => 'section',
			'post_type' => $config['post_type'],
			'term'      => null,
		);
	}

	/**
	 * @param array<string, mixed> $context Context from get_context().
	 * @return array<int, \WP_Post>
	 */
	public static function get_posts( array $context ) {
		if ( 'mixed' === ( $context['mode'] ?? '' ) ) {
			return self::get_mixed_posts();
		}

		return self::get_section_posts( $context );
	}

	/**
	 * @param array<string, mixed> $context Context from get_context().
	 * @return array<int, \WP_Post>
	 */
	private static function get_section_posts( array $context ) {
		$post_type = ! empty( $context['post_type'] ) ? sanitize_key( $context['post_type'] ) : '';

		if ( '' === $post_type || ! post_type_exists( $post_type ) ) {
			return array();
		}

		$query_args = array(
			'post_type'              => $post_type,
			'posts_per_page'         => 8,
			'post_status'            => 'publish',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => true,
			'orderby'                => array(
				'date' => 'DESC',
				'ID'   => 'DESC',
			),
		);

		if ( ! empty( $context['term'] ) && $context['term'] instanceof \WP_Term ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => $context['term']->taxonomy,
					'field'    => 'term_id',
					'terms'    => array( (int) $context['term']->term_id ),
				),
			);
		}

		$query = new \WP_Query( $query_args );

		if ( ! $query->have_posts() && ! empty( $context['term'] ) ) {
			unset( $query_args['tax_query'] );
			$query = new \WP_Query( $query_args );
		}

		if ( ! $query->have_posts() ) {
			return array();
		}

		$posts = is_array( $query->posts ) ? $query->posts : array();

		wp_reset_postdata();

		return $posts;
	}

	/**
	 * @return array<int, \WP_Post>
	 */
	private static function get_mixed_posts() {
		$post_types = array( 'receitas', 'casas', 'celebracoes' );
		$posts      = array();

		foreach ( $post_types as $post_type ) {
			if ( ! post_type_exists( $post_type ) ) {
				continue;
			}

			$query = new \WP_Query(
				array(
					'post_type'              => $post_type,
					'posts_per_page'         => 1,
					'post_status'            => 'publish',
					'ignore_sticky_posts'    => true,
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => true,
					'orderby'                => array(
						'date' => 'DESC',
						'ID'   => 'DESC',
					),
				)
			);

			if ( $query->have_posts() ) {
				$posts[] = $query->posts[0];
			}

			wp_reset_postdata();
		}

		return $posts;
	}
}
