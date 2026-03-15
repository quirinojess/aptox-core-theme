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

if ( ! function_exists( 'aptox_get_season_cta_data' ) ) {
	function aptox_get_season_cta_data() {
		return SeasonService::get_season_cta_data();
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
