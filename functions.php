<?php
/**
 * Theme bootstrap for Aptox.
 */

$autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
}

require_once __DIR__ . '/inc/Core/Theme.php';

\Aptox\Core\Theme::boot();
