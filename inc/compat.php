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
