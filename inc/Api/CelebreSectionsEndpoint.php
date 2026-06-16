<?php
/**
 * Lazy Celebre page section endpoint.
 *
 * @package Aptox
 */

namespace Aptox\Api;

use Aptox\Services\SeasonService;
use WP_Error;
use WP_REST_Request;

class CelebreSectionsEndpoint {
	/**
	 * Register endpoint hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Allowed lazy-loaded Celebre sections.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_allowed_sections() {
		return array(
			'season-slide'   => array(
				'slug' => 'components/celebre-season-slide/celebre-season-slide',
			),
			'celebre-season' => array(
				'slug' => 'components/celebre-season/celebre-season',
			),
			'info-grid'      => array(
				'slug' => 'components/celebre-info-grid/celebre-info-grid',
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
			'/celebre-section/(?P<section>[a-z0-9-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_section_request' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'estacao' => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_title',
					),
				),
			)
		);
	}

	/**
	 * Render and return a Celebre section markup.
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

		SeasonService::bootstrap_request_season( $request->get_param( 'estacao' ) );

		if ( null === SeasonService::get_override_season_slug() && isset( $_COOKIE[ SeasonService::SEASON_COOKIE_NAME ] ) ) {
			SeasonService::bootstrap_request_season( wp_unslash( $_COOKIE[ SeasonService::SEASON_COOKIE_NAME ] ) );
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
