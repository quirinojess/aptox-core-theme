<?php
/**
 * YouTube channel feed helpers.
 *
 * @package Aptox
 */

namespace Aptox\Services;

class YouTubeService {
	/**
	 * Public channel URL.
	 */
	public const CHANNEL_URL = 'https://www.youtube.com/@aptoxblog';

	/**
	 * Channel handle without @.
	 */
	public const CHANNEL_HANDLE = 'aptoxblog';

	/**
	 * Known channel ID fallback when handle resolution fails.
	 */
	public const CHANNEL_ID = 'UCOZ2jItSeVMPsgObRSQkecw';

	/**
	 * Default number of videos to fetch.
	 */
	public const DEFAULT_LIMIT = 4;

	/**
	 * Cache duration for parsed video lists.
	 */
	private const VIDEOS_CACHE_TTL = 6 * HOUR_IN_SECONDS;

	/**
	 * Cache duration for resolved channel IDs.
	 */
	private const CHANNEL_ID_CACHE_TTL = WEEK_IN_SECONDS;

	/**
	 * Get latest videos from the configured channel.
	 *
	 * @param int $limit Number of videos.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_latest_videos( $limit = self::DEFAULT_LIMIT ) {
		$limit   = max( 1, min( 12, (int) $limit ) );
		$entries = self::get_channel_entries();

		return array_slice( $entries, 0, $limit );
	}

	/**
	 * Get home layout data: latest long video + latest shorts.
	 *
	 * @return array{long_video: array<string, mixed>|null, shorts: array<int, array<string, mixed>>}
	 */
	public static function get_home_feed() {
		$cache_key = 'aptox_youtube_home_feed_v4';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$entries    = self::get_channel_entries();
		$long_video = null;
		$shorts     = array();

		foreach ( $entries as $entry ) {
			if ( $long_video || ! empty( $entry['is_short'] ) ) {
				continue;
			}

			$long_video = $entry;
		}

		foreach ( $entries as $entry ) {
			if ( empty( $entry['is_short'] ) || count( $shorts ) >= 4 ) {
				continue;
			}

			$shorts[] = $entry;
		}

		$data = array(
			'long_video' => $long_video,
			'shorts'     => $shorts,
		);

		set_transient( $cache_key, $data, self::VIDEOS_CACHE_TTL );

		return $data;
	}

	/**
	 * Fetch and cache all recent channel entries from the RSS feed.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function get_channel_entries() {
		$cache_key = 'aptox_youtube_channel_entries_v3';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$channel_id = self::get_channel_id();

		if ( ! $channel_id ) {
			return array();
		}

		$feed_url = sprintf(
			'https://www.youtube.com/feeds/videos.xml?channel_id=%s',
			rawurlencode( $channel_id )
		);

		$response = wp_remote_get(
			$feed_url,
			array(
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$body = wp_remote_retrieve_body( $response );

		if ( '' === $body ) {
			return array();
		}

		$xml = simplexml_load_string( $body );

		if ( false === $xml || empty( $xml->entry ) ) {
			return array();
		}

		$entries = array();

		foreach ( $xml->entry as $entry ) {
			$parsed = self::parse_feed_entry( $entry );

			if ( null !== $parsed ) {
				$entries[] = $parsed;
			}
		}

		set_transient( $cache_key, $entries, self::VIDEOS_CACHE_TTL );

		return $entries;
	}

	/**
	 * Parse a single RSS entry into a normalized video array.
	 *
	 * @param \SimpleXMLElement $entry RSS entry node.
	 * @return array<string, mixed>|null
	 */
	private static function parse_feed_entry( $entry ) {
		$namespaces = $entry->getNameSpaces( true );
		$yt         = isset( $namespaces['yt'] )
			? $entry->children( $namespaces['yt'] )
			: null;
		$media      = isset( $namespaces['media'] )
			? $entry->children( $namespaces['media'] )
			: null;

		$video_id = $yt ? (string) $yt->videoId : '';

		if ( '' === $video_id ) {
			return null;
		}

		$alternate_url = self::get_entry_alternate_url( $entry );
		$entry_xml     = $entry->asXML();
		$is_short      = self::is_short_video_url( $alternate_url )
			|| ( is_string( $entry_xml ) && false !== strpos( $entry_xml, '/shorts/' ) );

		$url = $is_short
			? 'https://www.youtube.com/shorts/' . $video_id
			: 'https://www.youtube.com/watch?v=' . $video_id;

		$thumbnail = sprintf(
			'https://i.ytimg.com/vi/%s/hqdefault.jpg',
			rawurlencode( $video_id )
		);

		if ( $media && isset( $media->group->thumbnail ) ) {
			$thumb_attrs = $media->group->thumbnail->attributes();
			if ( ! empty( $thumb_attrs['url'] ) ) {
				$thumbnail = (string) $thumb_attrs['url'];
			}
		}

		return array(
			'id'        => sanitize_text_field( $video_id ),
			'title'     => sanitize_text_field( html_entity_decode( (string) $entry->title, ENT_QUOTES, 'UTF-8' ) ),
			'url'       => esc_url_raw( $url ),
			'thumbnail' => esc_url_raw( $thumbnail ),
			'published' => sanitize_text_field( (string) $entry->published ),
			'is_short'  => $is_short,
		);
	}

	/**
	 * Get the alternate link URL from an RSS entry.
	 *
	 * @param \SimpleXMLElement $entry RSS entry node.
	 * @return string
	 */
	private static function get_entry_alternate_url( $entry ) {
		if ( empty( $entry->link ) ) {
			return '';
		}

		foreach ( $entry->link as $link ) {
			$href = '';
			$rel  = '';

			if ( isset( $link['href'] ) ) {
				$href = (string) $link['href'];
			}

			if ( isset( $link['rel'] ) ) {
				$rel = (string) $link['rel'];
			}

			if ( '' === $href ) {
				$attrs = $link->attributes();

				if ( isset( $attrs['href'] ) ) {
					$href = (string) $attrs['href'];
				}

				if ( isset( $attrs['rel'] ) ) {
					$rel = (string) $attrs['rel'];
				}
			}

			if ( '' === $href || false !== strpos( $href, '/channel/' ) ) {
				continue;
			}

			if ( '' === $rel || 'alternate' === $rel ) {
				return $href;
			}
		}

		return '';
	}

	/**
	 * Detect whether a YouTube URL points to a Short.
	 *
	 * @param string $url Video URL.
	 * @return bool
	 */
	private static function is_short_video_url( $url ) {
		return is_string( $url ) && false !== strpos( $url, '/shorts/' );
	}

	/**
	 * Resolve and cache the channel ID for the configured handle.
	 *
	 * @return string
	 */
	public static function get_channel_id() {
		if ( defined( 'APTOX_YOUTUBE_CHANNEL_ID' ) && APTOX_YOUTUBE_CHANNEL_ID ) {
			return sanitize_text_field( APTOX_YOUTUBE_CHANNEL_ID );
		}

		$cache_key = 'aptox_youtube_channel_id_v1';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_string( $cached ) && '' !== $cached ) {
			return $cached;
		}

		$resolved = self::resolve_channel_id_from_handle( self::CHANNEL_HANDLE );

		if ( ! $resolved ) {
			$resolved = self::CHANNEL_ID;
		}

		set_transient( $cache_key, $resolved, self::CHANNEL_ID_CACHE_TTL );

		return $resolved;
	}

	/**
	 * Resolve a channel ID from a public @handle page.
	 *
	 * @param string $handle Channel handle without @.
	 * @return string
	 */
	private static function resolve_channel_id_from_handle( $handle ) {
		$handle = sanitize_title( $handle );

		if ( '' === $handle ) {
			return '';
		}

		$response = wp_remote_get(
			'https://www.youtube.com/@' . rawurlencode( $handle ),
			array(
				'timeout'    => 15,
				'user-agent' => 'Mozilla/5.0 (compatible; AptoxTheme/1.0)',
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$body = wp_remote_retrieve_body( $response );

		if ( '' === $body ) {
			return '';
		}

		$patterns = array(
			'/"(?:channelId|browseId|externalId)":"(UC[a-zA-Z0-9_-]+)"/',
			'/itemprop="channelId" content="(UC[a-zA-Z0-9_-]+)"/',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $body, $matches ) && ! empty( $matches[1] ) ) {
				return sanitize_text_field( $matches[1] );
			}
		}

		return '';
	}
}
