<?php
/**
 * Theme functions and definitions
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Theme Configuration & Core Initializations
// ----------------------------------------------------

// Setup theme support
add_action( 'after_setup_theme', 'ltdh_theme_setup' );
function ltdh_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	
	// Rank Math breadcrumbs
	add_theme_support( 'rank-math-breadcrumbs' );

	// Register navigation menus
	register_nav_menus( [
		'primary-menu' => 'Header Navigation Menu',
		'footer-menu'  => 'Footer Navigation Menu',
	] );
}

/**
 * Output Rank Math breadcrumb with consistent styling
 */
function ltdh_breadcrumb() {
	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		echo '<div class="ltdh-breadcrumb max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-sm text-slate-400">';
		rank_math_the_breadcrumbs();
		echo '</div>';
	}
}

// Enqueue styles and scripts
add_action( 'wp_enqueue_scripts', 'ltdh_enqueue_assets' );
function ltdh_enqueue_assets() {
	// Standard Stylesheets
	wp_enqueue_style( 'ltdh-main-style', get_template_directory_uri() . '/style.css', [], '1.0.0' );
	
	if ( file_exists( get_template_directory() . '/assets/css/main.min.css' ) ) {
		wp_enqueue_style( 'ltdh-theme-styles', get_template_directory_uri() . '/assets/css/main.min.css', [], '1.0.0' );
	}
	
	// Vanilla JS Bundle
	if ( file_exists( get_template_directory() . '/assets/js/main.bundle.js' ) ) {
		wp_enqueue_script( 'ltdh-theme-js', get_template_directory_uri() . '/assets/js/main.bundle.js', [], '1.0.0', true );
	} else {
		// Fallback vanilla script for dev
		wp_enqueue_script( 'ltdh-fallback-js', get_template_directory_uri() . '/assets/js/main.js', [], '1.0.0', true );
	}
}

// ----------------------------------------------------
// 2. Performance Query Caching Helpers
// ----------------------------------------------------
/**
 * Cached WP_Query wrapper using WordPress Transients
 */
function ltdh_get_cached_query( $transient_key, $query_args, $expiration = HOUR_IN_SECONDS ) {
	$cached_results = get_transient( $transient_key );
	if ( false !== $cached_results ) {
		return $cached_results;
	}

	$query = new WP_Query( $query_args );
	set_transient( $transient_key, $query, $expiration );

	return $query;
}

// Clear transients on publish/edit to ensure freshness
add_action( 'save_post', 'ltdh_clear_transients_on_save' );
function ltdh_clear_transients_on_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	delete_transient( 'ltdh_featured_schools' );
}

/**
 * Resolve school image attachment ID (ACF logo → featured image).
 */
function ltdh_get_school_image_id( $school_id ) {
	$logo_id = get_field( 'logo', $school_id );
	if ( $logo_id ) {
		return (int) $logo_id;
	}

	if ( has_post_thumbnail( $school_id ) ) {
		return (int) get_post_thumbnail_id( $school_id );
	}

	return 0;
}

/**
 * Render school thumbnail with UNI fallback.
 */
function ltdh_render_school_thumbnail( $school_id, $size = 'thumbnail', $classes = 'h-14 w-14 object-cover border border-slate-100 bg-white rounded-lg' ) {
	$image_id = ltdh_get_school_image_id( $school_id );

	if ( $image_id ) {
		echo wp_get_attachment_image( $image_id, $size, false, [
			'class'   => $classes,
			'loading' => 'lazy',
			'alt'     => sprintf( 'Logo %s', get_the_title( $school_id ) ),
		] );
		return;
	}

	$fallback_classes = preg_replace( '/\bobject-(cover|contain)\b/', '', $classes );
	$fallback_classes = trim( preg_replace( '/\s+/', ' ', $fallback_classes ) );

	printf(
		'<div class="%s bg-blue-50 text-[#2563EB] font-display font-black text-sm flex items-center justify-center" aria-hidden="true">UNI</div>',
		esc_attr( $fallback_classes )
	);
}

// ----------------------------------------------------
// 3. Module Loader
// ----------------------------------------------------
$ltdh_modules = [
	'inc/post-types.php',
	'inc/acf-fields.php',
	'inc/relationship-hooks.php',
	'inc/lead-capture.php',
	'inc/crm-adapters.php',
	'inc/seo.php',
	'inc/cli-commands.php',
];

foreach ( $ltdh_modules as $module ) {
	$file_path = get_template_directory() . '/' . $module;
	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
}
