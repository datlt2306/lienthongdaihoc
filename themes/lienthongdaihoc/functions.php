<?php
/**
 * Theme functions and definitions — Bootstrap file.
 *
 * This file only loads modules. All logic lives in inc/.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Constants & Configuration
// ----------------------------------------------------
require_once __DIR__ . '/inc/config/constants.php';
require_once __DIR__ . '/inc/config/class-defaults.php';

// ----------------------------------------------------
// 2. Core: Setup, Helpers, Rewrites, Queries
// ----------------------------------------------------
require_once __DIR__ . '/inc/core/class-theme-setup.php';
require_once __DIR__ . '/inc/core/class-helpers.php';
require_once __DIR__ . '/inc/core/class-menus.php';
require_once __DIR__ . '/inc/core/class-rewrite-rules.php';
require_once __DIR__ . '/inc/core/class-query-filters.php';

// ----------------------------------------------------
// 3. ACF: Field Groups
// ----------------------------------------------------
require_once __DIR__ . '/inc/acf-fields.php';

// ----------------------------------------------------
// 4. Content: CPTs & Taxonomies
// ----------------------------------------------------
require_once __DIR__ . '/inc/post-types.php';
require_once __DIR__ . '/inc/relationship-hooks.php';

// ----------------------------------------------------
// 5. Modules: Business Logic
// ----------------------------------------------------
require_once __DIR__ . '/inc/lead-capture.php';
require_once __DIR__ . '/inc/crm-adapters.php';
require_once __DIR__ . '/inc/search-engine.php';
require_once __DIR__ . '/inc/comparison.php';
require_once __DIR__ . '/inc/eligibility.php';
require_once __DIR__ . '/inc/eligibility-rules.php';

// ----------------------------------------------------
// 6. SEO: Rank Math Integration
// ----------------------------------------------------
require_once __DIR__ . '/inc/seo/class-rankmath-integration.php';

// ----------------------------------------------------
// 7. CLI: WP-CLI Commands
// ----------------------------------------------------
require_once __DIR__ . '/inc/cli-commands.php';

// Exclude .git directory from All-in-One WP Migration export to prevent size ballooning
add_filter( 'ai1wm_exclude_content_from_export', function ( $exclude_filters ) {
	$exclude_filters[] = '.git';
	return $exclude_filters;
} );

