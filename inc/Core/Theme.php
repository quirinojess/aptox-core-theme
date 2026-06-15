<?php
/**
 * Theme bootstrap orchestrator.
 *
 * @package Aptox
 */

namespace Aptox\Core;

use Aptox\Api\CelebreSectionsEndpoint;
use Aptox\Api\HomeSectionsEndpoint;
use Aptox\Api\LikesEndpoint;
use Aptox\Helpers\ContentFilters;
use Aptox\PostTypes\ContentTypes;
use Aptox\Services\LikesService;
use Aptox\Services\SeoService;

class Theme {
	/**
	 * Prevent duplicate boot execution.
	 *
	 * @var bool
	 */
	private static $booted = false;

	/**
	 * Bootstrap theme services.
	 *
	 * @return void
	 */
	public static function boot() {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		self::load_dependencies();

		$likes_service = new LikesService();

		( new Setup() )->register();
		( new Assets() )->register();
		( new ContentFilters( $likes_service ) )->register();
		( new LikesEndpoint( $likes_service ) )->register();
		( new HomeSectionsEndpoint() )->register();
		( new CelebreSectionsEndpoint() )->register();
		( new ContentTypes() )->register();
		( new SeoService() )->register();
	}

	/**
	 * Manual fallback loader when Composer autoload is unavailable.
	 *
	 * @return void
	 */
	private static function load_dependencies() {
		$base = dirname( __DIR__ );

		$files = array(
			$base . '/Core/Setup.php',
			$base . '/Core/Assets.php',
			$base . '/Services/SeasonService.php',
			$base . '/Services/CelebreSeasonService.php',
			$base . '/Services/NotFoundService.php',
			$base . '/Services/YouTubeService.php',
			$base . '/Services/RelatedPostsService.php',
			$base . '/Services/LikesService.php',
			$base . '/Services/SeoService.php',
			$base . '/Api/HomeSectionsEndpoint.php',
			$base . '/Api/CelebreSectionsEndpoint.php',
			$base . '/Api/LikesEndpoint.php',
			$base . '/Helpers/ContentFilters.php',
			$base . '/PostTypes/ContentTypes.php',
			$base . '/compat.php',
		);

		foreach ( $files as $file ) {
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}
}
