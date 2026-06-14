<?php
/**
 * Legacy compatibility layer.
 *
 * Keeps existing template calls working while the internal architecture
 * moves to namespaced services.
 *
 * @package Aptox
 */

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
