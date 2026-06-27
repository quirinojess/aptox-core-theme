<?php
/**
 * Supplemental sitemap provider for primary section hubs.
 *
 * @package Aptox
 */

namespace Aptox\Services;

/**
 * Loaded lazily after WordPress sitemap classes are available.
 */
class SitemapHubsProvider extends \WP_Sitemaps_Provider {
	/**
	 * @return void
	 */
	public function __construct() {
		$this->name        = 'aptoxhubs';
		$this->object_type = 'aptoxhubs';
	}

	/**
	 * @return array<string, \stdClass>
	 */
	public function get_object_subtypes() {
		return array();
	}

	/**
	 * @param int    $page_num       Page number.
	 * @param string $object_subtype Object subtype.
	 * @return array<int, array<string, string>>
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {
		unset( $object_subtype );

		if ( $page_num > 1 ) {
			return array();
		}

		return SitemapService::get_hub_urls();
	}

	/**
	 * @param string $object_subtype Object subtype.
	 * @return int
	 */
	public function get_max_num_pages( $object_subtype = '' ) {
		unset( $object_subtype );

		return empty( SitemapService::get_hub_urls() ) ? 0 : 1;
	}
}
