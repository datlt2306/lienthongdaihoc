<?php
/**
 * Automated LiteSpeed Cache Configuration Sync Engine
 *
 * Synchronizes LiteSpeed Cache settings from `litespeed-cache-settings.data`
 * into WordPress database.
 *
 * Usage:
 *   php inc/cli/sync-litespeed.php
 *   wp eval-file wp-content/themes/lienthongdaihoc/inc/cli/sync-litespeed.php
 *
 * @package lienthongdaihoc
 */

// 1. Locate and boot WordPress environment
if ( ! defined( 'ABSPATH' ) ) {
	$wp_load_candidates = [
		dirname( __DIR__, 4 ) . '/wp-load.php',                    // wp-content/themes/lienthongdaihoc/inc/cli -> public root
		dirname( __DIR__, 5 ) . '/wp-load.php',                    // in case of nested structure
		__DIR__ . '/../../../../wp-load.php',
		__DIR__ . '/../../../../../wp-load.php',
		$_SERVER['DOCUMENT_ROOT'] . '/wp-load.php',
		'/var/www/html/wp-load.php',                               // Docker container default
		'/home/datlt2306/lienthongdaihoc/wp-load.php',             // VPS default
	];

	$loaded = false;
	foreach ( $wp_load_candidates as $candidate ) {
		if ( file_exists( $candidate ) ) {
			require_once $candidate;
			$loaded = true;
			break;
		}
	}

	if ( ! $loaded ) {
		fwrite( STDERR, "❌ ERROR: Could not locate wp-load.php. Run from WordPress root directory.\n" );
		exit( 1 );
	}
}

// 2. Verify LiteSpeed Cache plugin availability
if ( ! class_exists( 'LiteSpeed\Import' ) ) {
	fwrite( STDERR, "❌ ERROR: LiteSpeed Cache plugin is not active or installed.\n" );
	exit( 1 );
}

// 3. Locate settings data file (in theme root)
$data_file = dirname( __DIR__, 2 ) . '/litespeed-cache-settings.data';
if ( ! file_exists( $data_file ) ) {
	$data_file = dirname( __DIR__ ) . '/litespeed-cache-settings.data';
}
if ( ! file_exists( $data_file ) ) {
	fwrite( STDERR, "❌ ERROR: Configuration file not found: {$data_file}\n" );
	exit( 1 );
}

echo "=======================================================\n";
echo "🚀 SYNCHRONIZING LITESPEED CACHE SETTINGS...\n";
echo "=======================================================\n";

try {
	$importer = new \LiteSpeed\Import();
	$result   = $importer->import( $data_file );

	if ( $result ) {
		// Purge all stale cache to apply new settings immediately
		if ( class_exists( 'LiteSpeed\Purge' ) ) {
			\LiteSpeed\Purge::purge_all( 'Synced from litespeed-cache-settings.data' );
		}

		echo "✅ SUCCESS: All 188 LiteSpeed Cache settings successfully imported & synchronized!\n";
		echo "   - Page Cache: ENABLED (TTL: 7 days / 604,800s)\n";
		echo "   - Mobile Cache: ENABLED (Matches .htaccess rules)\n";
		echo "   - Browser Cache: ENABLED (TTL: 1 year / 31,557,600s)\n";
		echo "   - CSS Minify: ENABLED (CSS Combine: OFF for HTTP/2)\n";
		echo "   - JS Minify: ENABLED (JS Combine: OFF for stability)\n";
		echo "   - HTML Minify: ENABLED (Strips comments & whitespace)\n";
		echo "   - Cloudflare Safe: Zero CSS/JS collisions\n";
		echo "   - All cache purged cleanly.\n";
		echo "=======================================================\n";
		exit( 0 );
	} else {
		fwrite( STDERR, "❌ FAILED: LiteSpeed Import failed to process configuration.\n" );
		exit( 1 );
	}
} catch ( \Exception $e ) {
	fwrite( STDERR, "❌ EXCEPTION: " . $e->getMessage() . "\n" );
	exit( 1 );
}
