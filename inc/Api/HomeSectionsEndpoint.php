<?php
/**
 * Lazy home section endpoint.
 *
 * @package Aptox
 */

namespace Aptox\Api;

use WP_Error;
use WP_REST_Request;

class HomeSectionsEndpoint {
	/**
	 * Register endpoint hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Allowed lazy-loaded home sections.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_allowed_sections() {
		return array(
			'info-grid'        => array(
				'slug' => 'components/info-grid/info-grid',
			),
			'cta-season'       => array(
				'slug' => 'components/cta-season/cta-season',
			),
			'grid-recipe'      => array(
				'slug' => 'components/grid-recipe/grid-recipe',
				'args' => array(
					'show_season_title' => true,
				),
			),
			'season-slide' => array(
				'slug' => 'components/season-slide/season-slide-home',
			),
			'grid-festivity'   => array(
				'slug' => 'components/grid-festivity/grid-festivity',
			),
			'youtube-feed'     => array(
				'slug' => 'components/youtube-feed/youtube-feed',
			),
		);
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			'aptox/v1',
			'/home-section/(?P<section>[a-z0-9-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_section_request' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Render and return a home section markup.
	 *
	 * @param WP_REST_Request $request Request instance.
	 * @return mixed
	 */
	public function handle_section_request( WP_REST_Request $request ) {
		$section = sanitize_key( (string) $request['section'] );
		$allowed = $this->get_allowed_sections();

		if ( ! isset( $allowed[ $section ] ) ) {
			return new WP_Error(
				'aptox_invalid_section',
				__( 'Seção inválida.', 'aptox' ),
				array( 'status' => 404 )
			);
		}

		ob_start();

		$template = $allowed[ $section ];
		$args     = isset( $template['args'] ) ? $template['args'] : array();

		get_template_part( $template['slug'], null, $args );
		$html = (string) ob_get_clean();

		return rest_ensure_response(
			array(
				'html' => trim( $html ),
			)
		);
	}
}
