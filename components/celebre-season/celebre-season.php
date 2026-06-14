<?php
/**
 * Component: Celebre seasonal festivity blocks
 *
 * @context Archive Celebracoes / Page Celebration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Aptox\Services\CelebreSeasonService;

$cache_key   = 'aptox_celebre_season_v10_' . CelebreSeasonService::get_cache_suffix();
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$blocks = CelebreSeasonService::get_season_blocks();

if ( empty( $blocks ) ) {
	return;
}

ob_start();

echo '<div class="celebre-season">';

foreach ( $blocks as $block ) {
	get_template_part(
		'components/celebre-block/celebre-block',
		null,
		array(
			'block' => $block,
		)
	);
}

echo '</div>';

$html = ob_get_clean();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
