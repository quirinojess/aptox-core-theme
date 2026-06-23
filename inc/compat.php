<?php
/**
 * Legacy compatibility layer.
 *
 * Keeps existing template calls working while the internal architecture
 * moves to namespaced services.
 *
 * @package Aptox
 */

use Aptox\Services\CelebreSeasonService;
use Aptox\Services\LikesService;
use Aptox\Services\RelatedPostsService;
use Aptox\Services\SeasonService;
use Aptox\Services\YouTubeService;

if ( class_exists( LikesService::class ) && ! class_exists( 'Aptox_Likes_Service' ) ) {
	class_alias( LikesService::class, 'Aptox_Likes_Service' );
}

if ( ! function_exists( 'aptox_get_season_context' ) ) {
	function aptox_get_season_context() {
		return SeasonService::get_season_context();
	}
}

if ( ! function_exists( 'aptox_get_all_seasons' ) ) {
	function aptox_get_all_seasons() {
		return SeasonService::get_all_seasons();
	}
}

if ( ! function_exists( 'aptox_hand_text' ) ) {
	/**
	 * Normalize copy rendered with the handwriting font.
	 *
	 * @param string $text      Text to normalize.
	 * @param bool   $lowercase Whether to lowercase the text.
	 * @return string
	 */
	function aptox_hand_text( $text, $lowercase = true ) {
		$text = (string) $text;

		if ( $lowercase ) {
			$text = mb_strtolower( $text, 'UTF-8' );
		}

		return $text;
	}
}

if ( ! function_exists( 'aptox_get_season_badge_data' ) ) {
	/**
	 * Build circular badge marquee data for decor/celebre slides.
	 *
	 * @param string $label Season label.
	 * @return array{text: string, font_size: string}
	 */
	function aptox_get_season_badge_data( $label ) {
		$text = aptox_hand_text( $label );

		if ( '' === $text ) {
			return array(
				'text'      => '',
				'font_size' => 'var(--font-size-xxs)',
			);
		}

		$unit     = $text . ' · ';
		$unit_len = max( 1, mb_strlen( $unit ) );
		$repeats  = max( 4, min( 10, (int) round( 40 / $unit_len ) ) );
		$char_len = mb_strlen( $text );

		if ( $char_len >= 11 ) {
			$font_size = '7.5px';
		} elseif ( $char_len >= 8 ) {
			$font_size = '9px';
		} else {
			$font_size = 'var(--font-size-xxs)';
		}

		return array(
			'text'      => trim( str_repeat( $unit, $repeats ) ),
			'font_size' => $font_size,
		);
	}
}

if ( ! function_exists( 'aptox_season_icon' ) ) {
	function aptox_season_icon( $icon ) {
		return SeasonService::season_icon( $icon );
	}
}

if ( ! function_exists( 'aptox_recipe_season_icon' ) ) {
	function aptox_recipe_season_icon( $season_slug = null ) {
		return SeasonService::recipe_season_icon( $season_slug );
	}
}

if ( ! function_exists( 'aptox_get_receitas_archive_url' ) ) {
	/**
	 * Resolve the public Receitas landing URL.
	 *
	 * @return string
	 */
	function aptox_get_receitas_archive_url() {
		$pages = get_pages(
			array(
				'meta_key'   => '_wp_page_template',
				'meta_value' => 'templates/page-receitas.php',
				'number'     => 1,
			)
		);

		if ( ! empty( $pages ) ) {
			return get_permalink( $pages[0] );
		}

		$archive = get_post_type_archive_link( 'receitas' );

		if ( $archive ) {
			return $archive;
		}

		return home_url( '/receitas/' );
	}
}

if ( ! function_exists( 'aptox_get_loja_archive_url' ) ) {
	/**
	 * Resolve the public Loja landing URL.
	 *
	 * @return string
	 */
	function aptox_get_loja_archive_url() {
		$pages = get_pages(
			array(
				'meta_key'   => '_wp_page_template',
				'meta_value' => 'templates/page-loja.php',
				'number'     => 1,
			)
		);

		if ( ! empty( $pages ) ) {
			return get_permalink( $pages[0] );
		}

		$archive = get_post_type_archive_link( 'loja' );

		if ( $archive ) {
			return $archive;
		}

		return home_url( '/loja/' );
	}
}

if ( ! function_exists( 'aptox_get_editorial_url' ) ) {
	/**
	 * Resolve the public Editorial page URL.
	 *
	 * @return string
	 */
	function aptox_get_editorial_url() {
		$editorial_page = get_page_by_path( 'editorial' );

		if ( $editorial_page ) {
			return get_permalink( $editorial_page->ID );
		}

		$pages = get_pages(
			array(
				'meta_key'   => '_wp_page_template',
				'meta_value' => 'templates/page-editorial.php',
				'number'     => 1,
			)
		);

		if ( ! empty( $pages ) ) {
			return get_permalink( $pages[0] );
		}

		return home_url( '/editorial/' );
	}
}

if ( ! function_exists( 'aptox_get_manifesto_url' ) ) {
	/**
	 * Resolve the public Manifesto page URL (former Sobre page).
	 *
	 * @return string
	 */
	function aptox_get_manifesto_url() {
		$manifesto_page = get_page_by_path( 'manifesto' );

		if ( $manifesto_page ) {
			return get_permalink( $manifesto_page->ID );
		}

		$legacy_sobre_page = get_page_by_path( 'sobre' );

		if ( $legacy_sobre_page ) {
			return get_permalink( $legacy_sobre_page->ID );
		}

		$pages = get_pages(
			array(
				'meta_key'   => '_wp_page_template',
				'meta_value' => 'templates/page-sobre.php',
				'number'     => 1,
			)
		);

		if ( ! empty( $pages ) ) {
			return get_permalink( $pages[0] );
		}

		return home_url( '/manifesto/' );
	}
}

if ( ! function_exists( 'aptox_get_receita_tag_taxonomies' ) ) {
	/**
	 * Tag taxonomies used by receita filters, in priority order.
	 *
	 * @return array<int, string>
	 */
	function aptox_get_receita_tag_taxonomies() {
		$taxonomies = array( 'post_tag' );

		if ( taxonomy_exists( 'receita_tag' ) ) {
			$taxonomies[] = 'receita_tag';
		}

		return $taxonomies;
	}
}

if ( ! function_exists( 'aptox_resolve_receita_tag_term' ) ) {
	/**
	 * Resolve a receita tag slug against supported taxonomies.
	 *
	 * @param string|array<int, string> $slug_or_slugs Tag slug or slug candidates.
	 * @return array{taxonomy: string, term: \WP_Term}|null
	 */
	function aptox_resolve_receita_tag_term( $slug_or_slugs ) {
		$slugs = is_array( $slug_or_slugs ) ? $slug_or_slugs : array( (string) $slug_or_slugs );

		foreach ( $slugs as $slug ) {
			$candidate = sanitize_title( (string) $slug );

			if ( '' === $candidate ) {
				continue;
			}

			foreach ( aptox_get_receita_tag_taxonomies() as $taxonomy ) {
				$term = get_term_by( 'slug', $candidate, $taxonomy );

				if ( $term && ! is_wp_error( $term ) ) {
					return array(
						'taxonomy' => $taxonomy,
						'term'     => $term,
					);
				}
			}
		}

		return null;
	}
}

if ( ! function_exists( 'aptox_get_receita_tag_query_slug' ) ) {
	/**
	 * Read the active receita tag filter from the query string.
	 *
	 * @return string
	 */
	function aptox_get_receita_tag_query_slug() {
		if ( is_tax( aptox_get_receita_tag_taxonomies() ) ) {
			$term = get_queried_object();

			if ( $term instanceof WP_Term ) {
				return $term->slug;
			}
		}

		$query_slug = get_query_var( 'tag' );

		if ( is_string( $query_slug ) && '' !== $query_slug ) {
			return sanitize_title( $query_slug );
		}

		if ( ! isset( $_GET['tag'] ) ) {
			return '';
		}

		return sanitize_title( wp_unslash( (string) $_GET['tag'] ) );
	}
}

if ( ! function_exists( 'aptox_get_receita_tag_link' ) ) {
	/**
	 * Build a receita tag filter URL using ?tag=slug on the receitas archive.
	 *
	 * @param array<int, string> $slugs Tag slug candidates.
	 * @return string
	 */
	function aptox_get_receita_tag_link( array $slugs ) {
		$resolved_slug = '';
		$resolved      = aptox_resolve_receita_tag_term( $slugs );

		if ( null !== $resolved ) {
			$resolved_slug = $resolved['term']->slug;
		} else {
			foreach ( $slugs as $slug ) {
				$candidate = sanitize_title( (string) $slug );

				if ( '' === $candidate ) {
					continue;
				}

				$resolved_slug = $candidate;
				break;
			}
		}

		if ( '' === $resolved_slug ) {
			return '';
		}

		$base_url = aptox_get_receitas_archive_url();

		if ( is_tax( array( 'receita_categoria', 'receita' ) ) ) {
			$term_link = get_term_link( get_queried_object() );

			if ( ! is_wp_error( $term_link ) ) {
				$base_url = $term_link;
			}
		}

		return add_query_arg( 'tag', $resolved_slug, $base_url );
	}
}

if ( ! function_exists( 'aptox_get_post_tag_link' ) ) {
	/**
	 * @deprecated Use aptox_get_receita_tag_link().
	 *
	 * @param array<int, string> $slugs Tag slug candidates.
	 * @return string
	 */
	function aptox_get_post_tag_link( array $slugs ) {
		return aptox_get_receita_tag_link( $slugs );
	}
}

if ( ! function_exists( 'aptox_party_season_icon' ) ) {
	function aptox_party_season_icon( $season_slug = null ) {
		return SeasonService::party_season_icon( $season_slug );
	}
}

if ( ! function_exists( 'aptox_decor_season_icon' ) ) {
	function aptox_decor_season_icon( $season_slug = null ) {
		return SeasonService::decor_season_icon( $season_slug );
	}
}

if ( ! function_exists( 'aptox_season_festivity_icon' ) ) {
	function aptox_season_festivity_icon( $season_slug = null ) {
		return CelebreSeasonService::get_season_festivity_icon_url( $season_slug );
	}
}

if ( ! function_exists( 'aptox_filter_home_season_icon' ) ) {
	function aptox_filter_home_season_icon( $season_slug = null ) {
		return SeasonService::filter_home_season_icon( $season_slug );
	}
}

if ( ! function_exists( 'aptox_get_season_newsletter_data' ) ) {
	function aptox_get_season_newsletter_data() {
		return SeasonService::get_season_newsletter_data();
	}
}

if ( ! function_exists( 'aptox_get_season_recipe_term' ) ) {
	function aptox_get_season_recipe_term( $season_slug ) {
		return SeasonService::get_season_recipe_term( $season_slug );
	}
}

if ( ! function_exists( 'aptox_get_season_label' ) ) {
	function aptox_get_season_label( $slug ) {
		return SeasonService::get_season_label( $slug );
	}
}

if ( ! function_exists( 'aptox_get_season_home_cta_text' ) ) {
	function aptox_get_season_home_cta_text( $season_slug = null ) {
		return SeasonService::get_season_home_cta_text( $season_slug );
	}
}

if ( ! function_exists( 'aptox_get_season_editorial_rituals' ) ) {
	function aptox_get_season_editorial_rituals( $season_slug = null ) {
		return SeasonService::get_season_editorial_rituals( $season_slug );
	}
}

if ( ! function_exists( 'aptox_get_season_editorial_rituals_intro' ) ) {
	function aptox_get_season_editorial_rituals_intro( $season_slug = null ) {
		return SeasonService::get_season_editorial_rituals_intro( $season_slug );
	}
}

if ( ! function_exists( 'aptox_get_editorial_cover_image' ) ) {
	/**
	 * @param int         $page_id     Page ID.
	 * @param string|null $season_slug Optional season slug.
	 * @return array{url: string, alt: string, width: int, height: int}|null
	 */
	function aptox_get_editorial_cover_image( $page_id, $season_slug = null ) {
		return SeasonService::get_editorial_cover_image( $page_id, $season_slug );
	}
}

if ( ! function_exists( 'aptox_get_season_celebration_post' ) ) {
	/**
	 * @param string|null $season_slug Optional season slug.
	 * @return \WP_Post|null
	 */
	function aptox_get_season_celebration_post( $season_slug = null ) {
		return SeasonService::get_latest_season_celebration_post( $season_slug );
	}
}

if ( ! function_exists( 'aptox_render_celebration_post_content' ) ) {
	/**
	 * Render full celebration post content with theme filters.
	 *
	 * @param \WP_Post $post Celebration post.
	 * @return string
	 */
	function aptox_render_celebration_post_content( $post ) {
		if ( ! $post instanceof WP_Post ) {
			return '';
		}

		$content = apply_filters( 'the_content', $post->post_content );

		if ( function_exists( 'aptox_add_h2_anchors_to_content' ) ) {
			$content = aptox_add_h2_anchors_to_content( $content );
		}

		return $content;
	}
}

if ( ! function_exists( 'aptox_get_season_festivities' ) ) {
	function aptox_get_season_festivities( $season_slug = null ) {
		return SeasonService::get_season_festivities( $season_slug );
	}
}

if ( ! function_exists( 'aptox_get_youtube_videos' ) ) {
	function aptox_get_youtube_videos( $limit = 4 ) {
		return YouTubeService::get_latest_videos( $limit );
	}
}

if ( ! function_exists( 'aptox_get_youtube_home_feed' ) ) {
	function aptox_get_youtube_home_feed() {
		return YouTubeService::get_home_feed();
	}
}

if ( ! function_exists( 'aptox_get_youtube_channel_url' ) ) {
	function aptox_get_youtube_channel_url() {
		return YouTubeService::CHANNEL_URL;
	}
}

if ( ! function_exists( 'aptox_detect_post_taxonomy' ) ) {
	function aptox_detect_post_taxonomy( $post_id = null ) {
		return RelatedPostsService::detect_post_taxonomy( $post_id );
	}
}

if ( ! function_exists( 'aptox_get_related_tax_context' ) ) {
	function aptox_get_related_tax_context() {
		return RelatedPostsService::get_related_tax_context();
	}
}

if ( ! function_exists( 'aptox_is_lazy_home' ) ) {
	/**
	 * Whether the current view uses the lazy-loaded home layout.
	 *
	 * @return bool
	 */
	function aptox_is_lazy_home() {
		return ( is_front_page() || is_home() ) && ! is_paged();
	}
}

if ( ! function_exists( 'aptox_is_links_page' ) ) {
	/**
	 * Whether the current view uses the Linktree-style links landing page.
	 *
	 * @return bool
	 */
	function aptox_is_links_page() {
		return is_page_template( 'templates/page-links.php' );
	}
}

if ( ! function_exists( 'aptox_get_social_links' ) ) {
	/**
	 * Social profile URLs used in the footer and links landing page.
	 *
	 * @return array<int, array{url: string, icon: string, label: string}>
	 */
	function aptox_get_social_links() {
		return array(
			array(
				'url'   => 'https://www.instagram.com/aptox/',
				'icon'  => 'ui-social-instagram.svg',
				'label' => 'Instagram',
			),
			array(
				'url'   => 'https://br.pinterest.com/aptoxblog/',
				'icon'  => 'ui-social-pinterest.svg',
				'label' => 'Pinterest',
			),
			array(
				'url'   => 'https://www.youtube.com/@aptoxblog',
				'icon'  => 'ui-social-youtube.svg',
				'label' => 'YouTube',
			),
			array(
				'url'   => 'https://www.tiktok.com/@aptoxblog',
				'icon'  => 'ui-social-tiktok.svg',
				'label' => 'TikTok',
			),
			array(
				'url'   => 'https://www.facebook.com/aptox',
				'icon'  => 'ui-social-facebook.svg',
				'label' => 'Facebook',
			),
		);
	}
}

if ( ! function_exists( 'aptox_page_links_get_casa_posts' ) ) {
	/**
	 * Resolve Casa posts for the links landing page.
	 *
	 * First item: latest seasonal decoration post (decoracao + decoracao-de-{season} tag).
	 * Next items: latest Casa posts excluding seasonal decoration posts.
	 *
	 * @param string $season_slug Season slug.
	 * @return array<int, \WP_Post>
	 */
	function aptox_page_links_get_casa_posts( $season_slug ) {
		if ( ! post_type_exists( 'casas' ) ) {
			return array();
		}

		$season_slug = sanitize_title( $season_slug );
		$tag_slug    = 'decoracao-de-' . $season_slug;

		$house_taxonomy = 'casa_categoria';

		foreach ( array( 'casa_categoria', 'casa' ) as $candidate_taxonomy ) {
			if ( ! taxonomy_exists( $candidate_taxonomy ) ) {
				continue;
			}

			$decor_term = get_term_by( 'slug', 'decoracao', $candidate_taxonomy );

			if ( $decor_term && ! is_wp_error( $decor_term ) ) {
				$house_taxonomy = $candidate_taxonomy;
				break;
			}
		}

		$season_decor_tax_query = array(
			'relation' => 'AND',
			array(
				'taxonomy' => $house_taxonomy,
				'field'    => 'slug',
				'terms'    => 'decoracao',
			),
			array(
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => $tag_slug,
			),
		);

		$featured_query = new WP_Query(
			array(
				'post_type'              => 'casas',
				'posts_per_page'         => 1,
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'tax_query'              => $season_decor_tax_query,
				'orderby'                => 'date',
				'order'                  => 'DESC',
			)
		);

		$featured_post = $featured_query->have_posts() ? $featured_query->posts[0] : null;
		wp_reset_postdata();

		if ( ! $featured_post instanceof WP_Post ) {
			$fallback_query = new WP_Query(
				array(
					'post_type'              => 'casas',
					'posts_per_page'         => 3,
					'ignore_sticky_posts'    => true,
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'orderby'                => 'date',
					'order'                  => 'DESC',
				)
			);

			$fallback_posts = $fallback_query->have_posts() ? $fallback_query->posts : array();
			wp_reset_postdata();

			return $fallback_posts;
		}

		$exclude_ids = get_posts(
			array(
				'post_type'              => 'casas',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'tax_query'              => $season_decor_tax_query,
			)
		);

		$others_query = new WP_Query(
			array(
				'post_type'              => 'casas',
				'posts_per_page'         => 2,
				'post__not_in'           => array_map( 'intval', $exclude_ids ),
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'orderby'                => 'date',
				'order'                  => 'DESC',
			)
		);

		$posts = array();

		if ( $featured_post instanceof WP_Post ) {
			$posts[] = $featured_post;
		}

		if ( $others_query->have_posts() ) {
			$posts = array_merge( $posts, $others_query->posts );
		}

		wp_reset_postdata();

		return $posts;
	}
}

if ( ! function_exists( 'aptox_page_links_get_latest_youtube_video' ) ) {
	/**
	 * Resolve the latest YouTube video for the links landing page.
	 *
	 * @return array<string, mixed>|null
	 */
	function aptox_page_links_get_latest_youtube_video() {
		if ( ! function_exists( 'aptox_get_youtube_videos' ) ) {
			return null;
		}

		$videos = aptox_get_youtube_videos( 12 );

		foreach ( $videos as $video ) {
			if ( is_array( $video ) && empty( $video['is_short'] ) ) {
				return $video;
			}
		}

		if ( ! empty( $videos[0] ) && is_array( $videos[0] ) ) {
			return $videos[0];
		}

		return null;
	}
}

if ( ! function_exists( 'aptox_page_links_get_posts_by_season_tag' ) ) {
	/**
	 * Resolve posts for the links landing page by seasonal post tag.
	 *
	 * @param string $post_type   Post type slug.
	 * @param string $season_slug Season slug.
	 * @param string $tag_prefix  Tag prefix before the season slug.
	 * @param int    $limit       Number of posts to return.
	 * @return array<int, \WP_Post>
	 */
	function aptox_page_links_get_posts_by_season_tag( $post_type, $season_slug, $tag_prefix, $limit = 3 ) {
		if ( ! post_type_exists( $post_type ) ) {
			return array();
		}

		$season_slug = sanitize_title( $season_slug );
		$tag_slug    = $tag_prefix . $season_slug;
		$term        = get_term_by( 'slug', $tag_slug, 'post_tag' );

		if ( ! $term || is_wp_error( $term ) ) {
			return array();
		}

		$query = new WP_Query(
			array(
				'post_type'              => $post_type,
				'posts_per_page'         => max( 1, (int) $limit ),
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'tax_query'              => array(
					array(
						'taxonomy' => 'post_tag',
						'field'    => 'term_id',
						'terms'    => array( (int) $term->term_id ),
					),
				),
				'orderby'                => 'date',
				'order'                  => 'DESC',
			)
		);

		$posts = $query->have_posts() ? $query->posts : array();
		wp_reset_postdata();

		return $posts;
	}
}

add_filter(
	'body_class',
	static function ( $classes ) {
		if ( function_exists( 'aptox_is_links_page' ) && aptox_is_links_page() ) {
			$classes[] = 'aptox-links-page';
		}

		return $classes;
	}
);

if ( ! function_exists( 'aptox_is_receita_context' ) ) {
	/**
	 * Whether the current view belongs to the Receitas section.
	 *
	 * @return bool
	 */
	function aptox_is_receita_context() {
		return is_singular( 'receitas' )
			|| is_tax( 'receita_categoria' )
			|| is_tax( 'receita_tag' )
			|| is_post_type_archive( 'receitas' )
			|| is_page_template( 'templates/page-receitas.php' );
	}
}

if ( ! function_exists( 'aptox_footer_ad_slot_has_content' ) ) {
	/**
	 * Whether rendered footer ad markup contains a visible ad unit.
	 *
	 * @param string $html Sidebar output HTML.
	 * @return bool
	 */
	function aptox_footer_ad_slot_has_content( $html ) {
		$html = trim( (string) $html );

		if ( '' === $html ) {
			return false;
		}

		if ( '' !== trim( wp_strip_all_tags( $html ) ) ) {
			return true;
		}

		return (bool) preg_match( '/<(iframe|img|ins|picture|video|object|embed)\b/i', $html );
	}
}

if ( ! function_exists( 'aptox_normalize_footer_ad_slot_html' ) ) {
	/**
	 * Normalize footer AdSense markup for the horizontal sticky slot.
	 *
	 * @param string $html Widget output HTML.
	 * @return string
	 */
	function aptox_normalize_footer_ad_slot_html( $html ) {
		$html = (string) $html;

		if ( '' === trim( $html ) ) {
			return $html;
		}

		$html = preg_replace( '/\sdata-full-width-responsive=(["\'])true\1/i', '', $html );
		$html = preg_replace( '/\sdata-ad-format=(["\'])auto\1/i', ' data-ad-format="horizontal"', $html );

		if ( preg_match( '/<ins\b[^>]*class=(["\'])adsbygoogle\1/i', $html ) ) {
			$html = preg_replace_callback(
				'/<ins\b[^>]*class=(["\'])adsbygoogle\1[^>]*>/i',
				static function ( $matches ) {
					$tag = $matches[0];

					if ( preg_match( '/\sstyle=(["\'])/i', $tag ) ) {
						return preg_replace(
							'/\sstyle=(["\'])([^"\']*)\1/i',
							' style="display:block;width:100%;height:90px;max-height:90px"',
							$tag,
							1
						);
					}

					return rtrim( $tag, '>' ) . ' style="display:block;width:100%;height:90px;max-height:90px">';
				},
				$html,
				1
			);
		}

		return $html;
	}
}

if ( ! function_exists( 'aptox_get_footer_ad_slot_html' ) ) {
	/**
	 * Render and return footer ad sidebar markup.
	 *
	 * @return string
	 */
	function aptox_get_footer_ad_slot_html() {
		static $cached_html = null;
		static $resolved    = false;

		if ( $resolved ) {
			return $cached_html;
		}

		$resolved = true;

		if ( ! is_active_sidebar( 'footer-ad-sidebar' ) ) {
			$cached_html = '';
			return $cached_html;
		}

		ob_start();
		dynamic_sidebar( 'footer-ad-sidebar' );
		$html = ob_get_clean();

		$cached_html = is_string( $html ) ? $html : '';

		if ( function_exists( 'aptox_normalize_footer_ad_slot_html' ) ) {
			$cached_html = aptox_normalize_footer_ad_slot_html( $cached_html );
		}

		return $cached_html;
	}
}

if ( ! function_exists( 'aptox_show_footer_ad' ) ) {
	/**
	 * Whether the sticky footer ad bar should render.
	 *
	 * @return bool
	 */
	function aptox_show_footer_ad() {
		if ( ! is_active_sidebar( 'footer-ad-sidebar' ) ) {
			return false;
		}

		return aptox_footer_ad_slot_has_content( aptox_get_footer_ad_slot_html() );
	}
}

if ( ! function_exists( 'aptox_show_footer_loja' ) ) {
	/**
	 * Whether the footer Loja carousel should render.
	 *
	 * @return bool
	 */
	function aptox_show_footer_loja() {
		if ( function_exists( 'aptox_is_links_page' ) && aptox_is_links_page() ) {
			return false;
		}

		if ( ! post_type_exists( 'loja' ) || aptox_is_receita_context() ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'aptox_footer_loja_cache_key' ) ) {
	/**
	 * Transient key for cached footer Loja carousel data.
	 *
	 * @return string
	 */
	function aptox_footer_loja_cache_key() {
		return 'aptox_footer_loja_v8';
	}
}

if ( ! function_exists( 'aptox_clear_footer_loja_cache' ) ) {
	/**
	 * Clear cached footer Loja carousel data.
	 *
	 * @return void
	 */
	function aptox_clear_footer_loja_cache() {
		delete_transient( aptox_footer_loja_cache_key() );

		// Legacy HTML caches generated while lazy-load plugins were active.
		foreach ( array( 'v1', 'v2', 'v3', 'v4', 'v5', 'v6', 'v7' ) as $version ) {
			delete_transient( 'aptox_footer_loja_' . $version );
		}
	}
}

if ( ! function_exists( 'aptox_render_loja_thumbnail' ) ) {
	/**
	 * Render a Loja product thumbnail without plugin-dependent lazy markup.
	 *
	 * @param int          $post_id Post ID.
	 * @param string|int[] $size    Image size.
	 * @param array        $args    Extra attributes.
	 * @return string
	 */
	function aptox_render_loja_thumbnail( $post_id, $size = 'medium', $args = array() ) {
		$post_id = (int) $post_id;

		if ( $post_id <= 0 || ! has_post_thumbnail( $post_id ) ) {
			return '';
		}

		$image_alt = get_post_meta( get_post_thumbnail_id( $post_id ), '_wp_attachment_image_alt', true );

		if ( ! is_string( $image_alt ) || '' === $image_alt ) {
			$image_alt = get_the_title( $post_id );
		}

		$args = wp_parse_args(
			$args,
			array(
				'loading'  => 'eager',
				'decoding' => 'async',
				'alt'      => $image_alt,
			)
		);

		return get_the_post_thumbnail( $post_id, $size, $args );
	}
}

if ( ! function_exists( 'aptox_is_lazy_celebre' ) ) {
	/**
	 * Whether the current view uses the lazy-loaded Celebre layout.
	 *
	 * @return bool
	 */
	function aptox_is_lazy_celebre() {
		return (
			is_post_type_archive( 'celebracoes' )
			|| is_page_template( 'templates/page-celebration.php' )
		) && ! is_paged();
	}
}

if ( ! function_exists( 'aptox_generate_h2_anchor_id' ) ) {
	/**
	 * Build a unique slug for an h2 anchor.
	 *
	 * @param string        $text     Heading text.
	 * @param array<string> $used_ids Existing ids in the document.
	 * @return string
	 */
	function aptox_generate_h2_anchor_id( $text, array &$used_ids ) {
		$base = sanitize_title( $text );

		if ( '' === $base ) {
			$base = 'topico';
		}

		$id     = $base;
		$suffix = 2;

		while ( in_array( $id, $used_ids, true ) ) {
			$id = $base . '-' . $suffix;
			++$suffix;
		}

		$used_ids[] = $id;

		return $id;
	}
}

if ( ! function_exists( 'aptox_extract_h2_section_summary' ) ) {
	/**
	 * Extract a short summary from HTML that follows an h2.
	 *
	 * @param string $html Content after the heading until the next h2.
	 * @return string
	 */
	function aptox_extract_h2_section_summary( $html ) {
		$html = trim( (string) $html );

		if ( '' === $html ) {
			return '';
		}

		if ( preg_match( '/<p[^>]*>(.*?)<\/p>/is', $html, $paragraph_match ) ) {
			$text = trim( wp_strip_all_tags( $paragraph_match[1] ) );
		} else {
			$text = trim( wp_strip_all_tags( $html ) );
		}

		if ( '' === $text ) {
			return '';
		}

		return wp_trim_words( $text, 14, '...' );
	}
}

if ( ! function_exists( 'aptox_get_post_h2_topics' ) ) {
	/**
	 * Extract h2 headings, anchor ids and section summaries from post content.
	 *
	 * @param string $content Raw or rendered post content.
	 * @return array<int, array{text: string, id: string, summary: string}>
	 */
	function aptox_get_post_h2_topics( $content ) {
		$topics   = array();
		$used_ids = array();

		if ( ! preg_match_all(
			'/<h2\b([^>]*)>(.*?)<\/h2>(.*?)(?=<h2\b|$)/is',
			$content,
			$matches,
			PREG_SET_ORDER
		) ) {
			return $topics;
		}

		foreach ( $matches as $match ) {
			$text = trim( wp_strip_all_tags( $match[2] ) );

			if ( '' === $text ) {
				continue;
			}

			if ( preg_match( '/\bid=(["\'])([^"\']+)\1/i', $match[1], $id_match ) ) {
				$id = sanitize_title( $id_match[2] );

				if ( '' === $id ) {
					$id = aptox_generate_h2_anchor_id( $text, $used_ids );
				} elseif ( ! in_array( $id, $used_ids, true ) ) {
					$used_ids[] = $id;
				}
			} else {
				$id = aptox_generate_h2_anchor_id( $text, $used_ids );
			}

			$topics[] = array(
				'text'    => $text,
				'id'      => $id,
				'summary' => aptox_extract_h2_section_summary( $match[3] ),
			);
		}

		return $topics;
	}
}

if ( ! function_exists( 'aptox_add_h2_anchors_to_content' ) ) {
	/**
	 * Ensure each h2 in content has an id for in-page navigation.
	 *
	 * @param string $content Rendered post content.
	 * @return string
	 */
	function aptox_add_h2_anchors_to_content( $content ) {
		if ( false === strpos( $content, '<h2' ) ) {
			return $content;
		}

		$used_ids = array();

		return preg_replace_callback(
			'/<h2\b([^>]*)>(.*?)<\/h2>/is',
			static function ( $match ) use ( &$used_ids ) {
				if ( preg_match( '/\bid=(["\'])([^"\']+)\1/i', $match[1], $id_match ) ) {
					$id = sanitize_title( $id_match[2] );

					if ( '' !== $id && ! in_array( $id, $used_ids, true ) ) {
						$used_ids[] = $id;
					}

					return $match[0];
				}

				$text = trim( wp_strip_all_tags( $match[2] ) );
				$id   = aptox_generate_h2_anchor_id( $text, $used_ids );
				$attrs = trim( $match[1] );

				if ( '' !== $attrs ) {
					return sprintf(
						'<h2 id="%s" %s>%s</h2>',
						esc_attr( $id ),
						$attrs,
						$match[2]
					);
				}

				return sprintf(
					'<h2 id="%s">%s</h2>',
					esc_attr( $id ),
					$match[2]
				);
			},
			$content
		);
	}
}

if ( ! function_exists( 'aptox_render_archive_load_more' ) ) {
	/**
	 * Render archive pagination with a crawlable next-page link.
	 *
	 * @param array<string, mixed> $args Pagination args.
	 * @return void
	 */
	function aptox_render_archive_load_more( array $args = array() ) {
		get_template_part( 'components/archive-grid/archive-load-more', null, $args );
	}
}

if ( ! function_exists( 'aptox_chevron_icon' ) ) {
	/**
	 * Render a carousel chevron icon.
	 *
	 * @param string $direction Icon direction. Accepts `left` or `right`.
	 * @return string
	 */
	function aptox_chevron_icon( $direction = 'right' ) {
		$paths = array(
			'left'  => 'M13 4l-6 6 6 6',
			'right' => 'M7 16l6-6-6-6',
		);

		$path = isset( $paths[ $direction ] ) ? $paths[ $direction ] : $paths['right'];

		return sprintf(
			'<svg class="aptox-chevron-icon" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="none" aria-hidden="true"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="%s"></path></svg>',
			esc_attr( $path )
		);
	}
}

if ( ! function_exists( 'aptox_theme_image_uri' ) ) {
	/**
	 * Resolve a theme image URI, preferring lighter optimized formats.
	 *
	 * @param string $basename File basename without extension.
	 * @return string
	 */
	function aptox_theme_image_uri( $basename ) {
		$basename = sanitize_file_name( (string) $basename );

		if ( '' === $basename ) {
			return '';
		}

		$directory = get_template_directory() . '/assets/img/';
		$base_uri  = get_template_directory_uri() . '/assets/img/';
		$formats   = array( 'webp', 'jpg', 'jpeg', 'png' );

		foreach ( $formats as $format ) {
			if ( file_exists( $directory . $basename . '.' . $format ) ) {
				return $base_uri . $basename . '.' . $format;
			}
		}

		return '';
	}
}

if ( ! function_exists( 'aptox_theme_image_meta' ) ) {
	/**
	 * Read width and height for a theme image.
	 *
	 * @param string $basename File basename without extension.
	 * @return array{width:int,height:int}
	 */
	function aptox_theme_image_meta( $basename ) {
		$basename = sanitize_file_name( (string) $basename );
		$path     = '';

		foreach ( array( 'webp', 'jpg', 'jpeg', 'png' ) as $format ) {
			$candidate = get_template_directory() . '/assets/img/' . $basename . '.' . $format;

			if ( file_exists( $candidate ) ) {
				$path = $candidate;
				break;
			}
		}

		if ( '' === $path ) {
			return array(
				'width'  => 0,
				'height' => 0,
			);
		}

		$size = function_exists( 'wp_getimagesize' ) ? wp_getimagesize( $path ) : getimagesize( $path );

		return array(
			'width'  => isset( $size[0] ) ? (int) $size[0] : 0,
			'height' => isset( $size[1] ) ? (int) $size[1] : 0,
		);
	}
}

if ( ! function_exists( 'aptox_render_post_thumbnail' ) ) {
	/**
	 * Render a post thumbnail with theme defaults.
	 *
	 * @param int|\WP_Post|null $post  Post object, ID, or current loop item.
	 * @param string|int[] $size  Image size.
	 * @param array        $attrs Extra attributes.
	 * @return string
	 */
	function aptox_render_post_thumbnail( $post, $size = 'aptox-card', $attrs = array() ) {
		$post_id = $post instanceof \WP_Post ? (int) $post->ID : (int) $post;

		if ( $post_id <= 0 ) {
			$post_id = get_the_ID() ? (int) get_the_ID() : 0;
		}

		if ( $post_id <= 0 || ! has_post_thumbnail( $post_id ) ) {
			return '';
		}

		$defaults = array(
			'loading'  => 'lazy',
			'decoding' => 'async',
		);

		return get_the_post_thumbnail( $post_id, $size, wp_parse_args( $attrs, $defaults ) );
	}
}

if ( ! function_exists( 'aptox_get_post_thumbnail_src' ) ) {
	/**
	 * Resolve a post thumbnail URL for a theme image size.
	 *
	 * @param int|\WP_Post $post Post object or ID.
	 * @param string|int[] $size Image size.
	 * @return string
	 */
	function aptox_get_post_thumbnail_src( $post, $size = 'aptox-feature' ) {
		$url = get_the_post_thumbnail_url( $post, $size );

		if ( $url ) {
			return (string) $url;
		}

		return (string) get_the_post_thumbnail_url( $post, 'medium_large' );
	}
}
