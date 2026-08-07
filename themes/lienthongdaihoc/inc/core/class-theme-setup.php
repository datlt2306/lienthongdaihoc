<?php
/**
 * Theme Setup — Supports, menus, asset enqueuing, and core initializations.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme supports, menus, and core features.
 */
function ltdh_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	] );
	add_theme_support( 'custom-logo', [
		'height'      => 80,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	] );
	add_theme_support( 'rank-math-breadcrumbs' );
	add_theme_support( 'customize-selective-refresh-widgets' );

	register_nav_menus( [
		'primary-menu' => 'Header Navigation Menu',
		'footer-menu'  => 'Footer Navigation Menu',
	] );
}
add_action( 'after_setup_theme', 'ltdh_theme_setup' );

/**
 * Enqueue frontend styles and scripts.
 */
function ltdh_enqueue_assets() {
	$main_css_path = get_template_directory() . '/style.css';
	$main_version = file_exists( $main_css_path ) ? filemtime( $main_css_path ) : LTDH_VERSION;
	wp_enqueue_style( 'ltdh-main-style', get_template_directory_uri() . '/style.css', [], $main_version );

	if ( is_front_page() ) {
		if ( file_exists( get_template_directory() . '/assets/css/swiper-bundle.min.css' ) ) {
			wp_enqueue_style( 'swiper-css', get_template_directory_uri() . '/assets/css/swiper-bundle.min.css', [], '11.0.0' );
		}
		if ( file_exists( get_template_directory() . '/assets/js/swiper-bundle.min.js' ) ) {
			wp_enqueue_script( 'swiper-js', get_template_directory_uri() . '/assets/js/swiper-bundle.min.js', [], '11.0.0', true );
		}
	}

	$theme_css_path = get_template_directory() . '/assets/css/main.min.css';
	if ( file_exists( $theme_css_path ) ) {
		$theme_version = filemtime( $theme_css_path );
		wp_enqueue_style( 'ltdh-theme-styles', get_template_directory_uri() . '/assets/css/main.min.css', [], $theme_version );
	}

	$script_handle = 'ltdh-fallback-js';
	if ( file_exists( get_template_directory() . '/assets/js/main.bundle.js' ) ) {
		wp_enqueue_script( 'ltdh-theme-js', get_template_directory_uri() . '/assets/js/main.bundle.js', [], LTDH_VERSION, true );
		$script_handle = 'ltdh-theme-js';
	} else {
		wp_enqueue_script( 'ltdh-fallback-js', get_template_directory_uri() . '/assets/js/main.js', [], LTDH_VERSION, true );
	}

	wp_localize_script( $script_handle, 'ltdh_ajax', [
		'ajax_url'      => admin_url( 'admin-ajax.php' ),
		'compare_nonce' => wp_create_nonce( LTDH_NONCE_COMPARE ),
		'home_url'      => esc_url( home_url( '/' ) ),
	] );

	if ( ltdh_compare_should_load() ) {
		wp_enqueue_script( 'ltdh-compare-js', get_template_directory_uri() . '/assets/js/compare.js', [], LTDH_VERSION, true );
	}
}
add_action( 'wp_enqueue_scripts', 'ltdh_enqueue_assets' );

/**
 * Check if the current page should load program comparison logic.
 */
function ltdh_compare_should_load() {
	if ( is_admin() ) {
		return false;
	}
	$request_uri = $_SERVER['REQUEST_URI'] ?? '';
	if ( strpos( $request_uri, '/he-dao-tao' ) !== false || strpos( $request_uri, '/so-sanh' ) !== false ) {
		return true;
	}
	return is_post_type_archive( 'program' ) || is_singular( 'program' ) || is_tax( 'training_type' ) || is_tax( 'major' ) || is_search() || is_page_template( 'template-search.php' ) || is_page( 'he-dao-tao' );
}

/**
 * Output the floating compare tray on all frontend pages.
 */
function ltdh_compare_output_tray() {
	if ( is_admin() || ! ltdh_compare_should_load() ) {
		return;
	}
	get_template_part( 'template-parts/compare/tray' );
}
add_action( 'wp_footer', 'ltdh_compare_output_tray' );

/**
 * Enable WebP upload support.
 */
function ltdh_enable_webp_upload( $mimes ) {
	$mimes['webp'] = 'image/webp';
	return $mimes;
}
add_filter( 'upload_mimes', 'ltdh_enable_webp_upload' );

function ltdh_allow_webp_upload_check( $data, $file, $filename, $mimes ) {
	$filetype = wp_check_filetype( $filename, $mimes );
	$ext      = $filetype['ext'];
	if ( 'webp' === $ext ) {
		$data['ext']  = 'webp';
		$data['type'] = 'image/webp';
	}
	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'ltdh_allow_webp_upload_check', 10, 4 );

/**
 * Flush rewrite rules on theme activation.
 */
function ltdh_flush_rewrite_rules_on_switch() {
	flush_rewrite_rules();
	update_option( LTDH_OPT_REWRITE_FLUSHED, time() );
}
add_action( 'after_switch_theme', 'ltdh_flush_rewrite_rules_on_switch' );

/**
 * One-time rewrite flush after deploy — runs inside 'init' at priority 99
 * so all CPTs and taxonomies are already registered before flushing.
 *
 * NEVER call flush_rewrite_rules() at global scope; it fires during
 * WordPress's fatal-error sandbox scrape on theme activation and can
 * cause the theme to be paused / "Cannot Activate".
 */
add_action( 'init', function() {
	$flushed = (int) get_option( LTDH_OPT_REWRITE_FLUSHED );
	if ( ! $flushed || $flushed < 1770460000 ) {
		flush_rewrite_rules();
		update_option( LTDH_OPT_REWRITE_FLUSHED, time() );
	}
}, 99 );

/**
 * Let WordPress Customizer handle logo and favicon natively.
 * ACF fields (global_logo, global_favicon) are kept only as fallback
 * when Customizer values are not set.
 */
