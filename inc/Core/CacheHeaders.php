<?php
/**
 * HTTP cache headers for theme assets and lazy section APIs.
 *
 * @package Aptox
 */

namespace Aptox\Core;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class CacheHeaders {
	/**
	 * Cache lifetime for lazy section REST responses.
	 */
	private const SECTION_MAX_AGE = HOUR_IN_SECONDS;

	/**
	 * Register cache header hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'rest_post_dispatch', array( $this, 'add_section_cache_headers' ), 10, 3 );
	}

	/**
	 * Allow browsers and CDNs to cache lazy section HTML briefly.
	 *
	 * @param \WP_REST_Response|\WP_HTTP_Response|\WP_Error|mixed $response Response object.
	 * @param WP_REST_Server                                     $server REST server instance.
	 * @param WP_REST_Request                                    $request Request instance.
	 * @return mixed
	 */
	public function add_section_cache_headers( $response, $server, $request ) {
		unset( $server );

		if ( ! $response instanceof WP_REST_Response || ! $request instanceof WP_REST_Request ) {
			return $response;
		}

		if ( $response->is_error() ) {
			return $response;
		}

		$route = (string) $request->get_route();

		if (
			0 !== strpos( $route, '/aptox/v1/home-section/' )
			&& 0 !== strpos( $route, '/aptox/v1/celebre-section/' )
		) {
			return $response;
		}

		$response->header(
			'Cache-Control',
			sprintf(
				'public, max-age=%d, stale-while-revalidate=86400',
				self::SECTION_MAX_AGE
			)
		);

		return $response;
	}
}
