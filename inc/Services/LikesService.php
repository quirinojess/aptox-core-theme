<?php
/**
 * Like system service.
 *
 * @package Aptox
 */

namespace Aptox\Services;

class LikesService {
	/**
	 * Toggle like status for a given post and actor.
	 *
	 * @param int|null $user_id Logged-in user ID or null for anonymous.
	 * @param int      $post_id Target post ID.
	 * @param string   $action  Action type.
	 * @return array<string, mixed>
	 */
	public function toggle_like( $user_id, $post_id, $action ) {
		$post_id = absint( $post_id );
		$action  = sanitize_key( $action );

		if ( ! $post_id || ! in_array( $action, array( 'like', 'unlike' ), true ) ) {
			return array(
				'error' => 'invalid_data',
				'code'  => 400,
			);
		}

		$post_validation = $this->validate_post( $post_id );
		if ( isset( $post_validation['error'] ) ) {
			return $post_validation;
		}

		$rate_check = $this->check_rate_limit();
		if ( isset( $rate_check['error'] ) ) {
			return $rate_check;
		}

		$likes = (int) get_post_meta( $post_id, '_post_likes', true );

		if ( $user_id ) {
			$result = $this->handle_logged_in_user( $user_id, $post_id, $action, $likes );
		} else {
			$result = $this->handle_anonymous_user( $post_id, $action, $likes );
		}

		$likes = (int) $result['likes'];
		$state = (string) $result['state'];

		update_post_meta( $post_id, '_post_likes', $likes );

		return array(
			'likes' => $likes,
			'state' => $state,
		);
	}

	/**
	 * Ensure the post can be liked.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string, mixed>
	 */
	protected function validate_post( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return array(
				'error' => 'post_not_found',
				'code'  => 404,
			);
		}

		if ( 'publish' !== $post->post_status || post_password_required( $post ) ) {
			return array(
				'error' => 'post_not_public',
				'code'  => 403,
			);
		}

		$allowed_types = array( 'post', 'casas', 'receitas', 'celebracoes' );
		if ( ! in_array( $post->post_type, $allowed_types, true ) ) {
			return array(
				'error' => 'post_type_not_likable',
				'code'  => 403,
			);
		}

		return array();
	}

	/**
	 * Simple IP-based rate limit.
	 *
	 * @return array<string, mixed>
	 */
	protected function check_rate_limit() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		if ( '' === $ip ) {
			return array();
		}

		$key    = 'aptox_like_rate_' . md5( $ip );
		$data   = get_transient( $key );
		$now    = time();
		$limit  = 30;
		$window = 10 * MINUTE_IN_SECONDS;

		if ( ! is_array( $data ) ) {
			$data = array(
				'count' => 0,
				'start' => $now,
			);
		}

		if ( $now - (int) $data['start'] > $window ) {
			$data = array(
				'count' => 0,
				'start' => $now,
			);
		}

		$data['count']++;
		if ( $data['count'] > $limit ) {
			set_transient( $key, $data, $window );
			return array(
				'error' => 'rate_limit_exceeded',
				'code'  => 429,
			);
		}

		set_transient( $key, $data, $window );
		return array();
	}

	/**
	 * Handle logged-in likes through user meta.
	 *
	 * @param int    $user_id User ID.
	 * @param int    $post_id Post ID.
	 * @param string $action Action type.
	 * @param int    $likes Current likes.
	 * @return array<string, mixed>
	 */
	protected function handle_logged_in_user( $user_id, $post_id, $action, $likes ) {
		$meta_key    = '_aptox_liked_posts';
		$liked_posts = get_user_meta( $user_id, $meta_key, true );

		if ( ! is_array( $liked_posts ) ) {
			$liked_posts = array();
		}

		$already_liked = in_array( $post_id, $liked_posts, true );

		if ( 'like' === $action && ! $already_liked ) {
			$liked_posts[] = $post_id;
			update_user_meta( $user_id, $meta_key, $liked_posts );
			return array(
				'likes' => $likes + 1,
				'state' => 'liked',
			);
		}

		if ( 'unlike' === $action && $already_liked ) {
			$liked_posts = array_diff( $liked_posts, array( $post_id ) );
			update_user_meta( $user_id, $meta_key, $liked_posts );
			return array(
				'likes' => max( 0, $likes - 1 ),
				'state' => 'unliked',
			);
		}

		return array(
			'likes' => $likes,
			'state' => 'unchanged',
		);
	}

	/**
	 * Handle anonymous likes through IP transients.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $action Action type.
	 * @param int    $likes Current likes.
	 * @return array<string, mixed>
	 */
	protected function handle_anonymous_user( $post_id, $action, $likes ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		if ( '' === $ip ) {
			if ( 'like' === $action ) {
				return array(
					'likes' => $likes + 1,
					'state' => 'liked',
				);
			}

			if ( 'unlike' === $action ) {
				return array(
					'likes' => max( 0, $likes - 1 ),
					'state' => 'unliked',
				);
			}

			return array(
				'likes' => $likes,
				'state' => 'unchanged',
			);
		}

		$key           = 'aptox_like_' . $post_id . '_' . md5( $ip );
		$already_liked = (bool) get_transient( $key );

		if ( 'like' === $action && ! $already_liked ) {
			set_transient( $key, 1, WEEK_IN_SECONDS );
			return array(
				'likes' => $likes + 1,
				'state' => 'liked',
			);
		}

		if ( 'unlike' === $action && $already_liked ) {
			delete_transient( $key );
			return array(
				'likes' => max( 0, $likes - 1 ),
				'state' => 'unliked',
			);
		}

		return array(
			'likes' => $likes,
			'state' => 'unchanged',
		);
	}
}
