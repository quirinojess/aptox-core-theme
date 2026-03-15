<?php
/**
 * Likes endpoint handlers.
 *
 * @package Aptox
 */

namespace Aptox\Api;

use Aptox\Services\LikesService;
use WP_Error;
use WP_REST_Request;

class LikesEndpoint {
	/**
	 * Likes domain service.
	 *
	 * @var LikesService
	 */
	private $service;

	/**
	 * Constructor.
	 *
	 * @param LikesService $service Likes service.
	 */
	public function __construct( LikesService $service ) {
		$this->service = $service;
	}

	/**
	 * Register endpoint hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'wp_ajax_toggle_post_like', array( $this, 'handle_ajax_toggle' ) );
		add_action( 'wp_ajax_nopriv_toggle_post_like', array( $this, 'handle_ajax_toggle' ) );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			'aptox/v1',
			'/posts/(?P<id>\d+)/like',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_rest_toggle' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Handle REST toggle request.
	 *
	 * @param WP_REST_Request $request Request instance.
	 * @return mixed
	 */
	public function handle_rest_toggle( WP_REST_Request $request ) {
		$post_id = (int) $request['id'];
		$action  = $request->get_param( 'action_type' );
		$action  = $action ? $action : 'like';

		$result = $this->service->toggle_like( get_current_user_id(), $post_id, $action );
		if ( isset( $result['error'] ) ) {
			return new WP_Error(
				'aptox_like_error',
				$result['error'],
				array(
					'status' => isset( $result['code'] ) ? (int) $result['code'] : 400,
				)
			);
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Handle AJAX toggle request.
	 *
	 * @return void
	 */
	public function handle_ajax_toggle() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'aptox_like_nonce' ) ) {
			wp_send_json_error( 'invalid_nonce' );
		}

		if ( empty( $_POST['post_id'] ) || ! isset( $_POST['action_type'] ) ) {
			wp_send_json_error( 'invalid_request' );
		}

		$post_id = absint( $_POST['post_id'] );
		$action  = sanitize_text_field( wp_unslash( $_POST['action_type'] ) );

		if ( ! $post_id || ! in_array( $action, array( 'like', 'unlike' ), true ) ) {
			wp_send_json_error( 'invalid_data' );
		}

		$result = $this->service->toggle_like( get_current_user_id(), $post_id, $action );
		if ( isset( $result['error'] ) ) {
			$status = isset( $result['code'] ) ? (int) $result['code'] : 400;
			wp_send_json_error(
				array(
					'error' => $result['error'],
				),
				$status
			);
		}

		wp_send_json_success(
			array(
				'likes' => isset( $result['likes'] ) ? (int) $result['likes'] : 0,
				'state' => isset( $result['state'] ) ? $result['state'] : 'unchanged',
			)
		);
	}
}
