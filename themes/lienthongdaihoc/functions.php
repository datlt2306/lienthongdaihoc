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
require_once __DIR__ . '/inc/core/class-icons.php';
require_once __DIR__ . '/inc/core/class-helpers.php';
require_once __DIR__ . '/inc/core/class-menus.php';
require_once __DIR__ . '/inc/core/class-rewrite-rules.php';
require_once __DIR__ . '/inc/core/class-query-filters.php';
require_once __DIR__ . '/inc/core/class-performance.php';
require_once __DIR__ . '/inc/core/class-widgets.php';


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
require_once __DIR__ . '/inc/recaptcha.php';
require_once __DIR__ . '/inc/referral-tracking.php';
require_once __DIR__ . '/inc/lead-capture.php';
require_once __DIR__ . '/inc/crm-adapters.php';
require_once __DIR__ . '/inc/email-notifications.php';
require_once __DIR__ . '/inc/search-engine.php';
require_once __DIR__ . '/inc/comparison.php';
require_once __DIR__ . '/inc/eligibility.php';
require_once __DIR__ . '/inc/eligibility-rules.php';
require_once __DIR__ . '/inc/program-reviews.php';

if ( file_exists( __DIR__ . '/inc/seo/class-rankmath-integration.php' ) ) {
	require_once __DIR__ . '/inc/seo/class-rankmath-integration.php';
}

// ----------------------------------------------------
// 6. Integrations: Easy Table of Contents
// ----------------------------------------------------
require_once __DIR__ . '/inc/easy-table-of-contents.php';

// ----------------------------------------------------
// 7. CLI: WP-CLI Commands
// ----------------------------------------------------
require_once __DIR__ . '/inc/cli-commands.php';

// Exclude .git and non-public dev/test directories from All-in-One WP Migration export
add_filter( 'ai1wm_exclude_content_from_export', function ( $exclude_filters ) {
	$exclude_filters[] = '.git';
	$exclude_filters[] = 'themes/lienthongdaihoc/.agent';
	$exclude_filters[] = 'themes/lienthongdaihoc/.agents';
	$exclude_filters[] = 'themes/lienthongdaihoc/tests';
	$exclude_filters[] = 'themes/lienthongdaihoc/scripts';
	$exclude_filters[] = 'themes/lienthongdaihoc/node_modules';
	return $exclude_filters;
} );

// Chỉ mở giới hạn dung lượng upload 1GB cho Quản trị viên (phục vụ All-in-One WP Migration)
add_filter( 'upload_size_limit', function ( $size ) {
	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return 1024 * 1024 * 1024; // 1 GB
	}
	return $size;
} );

// Chỉ cho phép namespace REST API tùy chỉnh ltdh đi qua tường lửa AIOS (không mở trắng toàn bộ 'wp')
add_filter( 'aios_whitelisted_rest_routes', function ( $routes ) {
	if ( ! is_array( $routes ) ) {
		$routes = [];
	}
	$routes[] = 'ltdh';
	return array_values( array_unique( $routes ) );
} );

// Chặn khách ẩn danh dò danh sách tài khoản quản trị qua /wp-json/wp/v2/users
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( ! is_user_logged_in() || ! current_user_can( 'list_users' ) ) {
		unset( $endpoints['/wp/v2/users'] );
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
} );



