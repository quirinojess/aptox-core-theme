<?php
/**
 * Related posts context helpers.
 *
 * @package Aptox
 */

namespace Aptox\Services;

class RelatedPostsService {
	/**
	 * Detect the first taxonomy with terms for a post.
	 *
	 * @param int|null $post_id Post ID.
	 * @return string|null
	 */
	public static function detect_post_taxonomy( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_queried_object_id();
		}

		if ( ! $post_id ) {
			return null;
		}

		$post_type = get_post_type( $post_id );
		if ( ! $post_type ) {
			return null;
		}

		$preferred_taxonomies = array(
			'casas'       => 'casa_categoria',
			'receitas'    => 'receita_categoria',
			'celebracoes' => 'celebracao_categoria',
			'post'        => 'category',
		);

		if ( isset( $preferred_taxonomies[ $post_type ] ) ) {
			$preferred_taxonomy = $preferred_taxonomies[ $post_type ];
			$preferred_terms    = get_the_terms( $post_id, $preferred_taxonomy );
			if ( ! empty( $preferred_terms ) && ! is_wp_error( $preferred_terms ) ) {
				return $preferred_taxonomy;
			}
		}

		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		if ( empty( $taxonomies ) ) {
			return null;
		}

		foreach ( $taxonomies as $taxonomy ) {
			if ( 'post_tag' === $taxonomy->name ) {
				continue;
			}

			$terms = get_the_terms( $post_id, $taxonomy->name );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				return $taxonomy->name;
			}
		}

		return null;
	}

	/**
	 * Build context array for related queries.
	 *
	 * @return array<string, int|string>|null
	 */
	public static function get_related_tax_context() {
		if ( ! is_singular() ) {
			return null;
		}

		$post_id   = get_queried_object_id();
		$post_type = get_post_type( $post_id );

		if ( ! $post_id || ! $post_type ) {
			return null;
		}

		$taxonomy = self::detect_post_taxonomy( $post_id );
		if ( ! $taxonomy ) {
			return null;
		}

		$terms = get_the_terms( $post_id, $taxonomy );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return null;
		}

		return array(
			'post_id'        => $post_id,
			'post_type'      => $post_type,
			'taxonomy'       => $taxonomy,
			'term_id'        => $terms[0]->term_id,
			'posts_per_page' => 4,
		);
	}
}
