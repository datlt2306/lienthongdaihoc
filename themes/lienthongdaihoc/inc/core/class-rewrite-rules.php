<?php
/**
 * Rewrite Rules — Custom URL routing for training types, programs, and comparison.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Training Type Archive (/he-dao-tao/)
// ----------------------------------------------------
function ltdh_register_training_type_rewrite() {
	add_rewrite_rule( 'he-dao-tao/?$', 'index.php?' . LTDH_TAX_TRAINING_TYPE, 'top' );
	add_rewrite_rule( 'he-dao-tao/([^/]+)/?$', 'index.php?' . LTDH_TAX_TRAINING_TYPE . '=$matches[1]', 'top' );
}
add_action( 'init', 'ltdh_register_training_type_rewrite' );

// ----------------------------------------------------
// 3. Redirect Empty Taxonomy Base URLs
// ----------------------------------------------------
function ltdh_redirect_taxonomy_base() {
	if ( is_tax( LTDH_TAX_CAMPUS ) ) {
		return;
	}
	$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
	if ( preg_match( '#^/co-so/?$#i', $request_path ) ) {
		wp_redirect( home_url( '/he-dao-tao/tu-xa/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
		$redirect_url = home_url( '/he-dao-tao/tu-xa/' );
		if ( ! empty( $_GET ) ) {
			$redirect_url = add_query_arg( $_GET, $redirect_url );
		}
		wp_redirect( $redirect_url, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'ltdh_redirect_taxonomy_base' );

// ----------------------------------------------------
// 4. Use archive-program.php for /he-dao-tao/
// ----------------------------------------------------
function ltdh_template_include_he_dao_tao( $template ) {
	$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
	if ( preg_match( '#^/he-dao-tao(?:/[^/]+)?/?$#i', $request_path ) ) {
		$archive_template = locate_template( 'archive-program.php' );
		if ( $archive_template ) {
			return $archive_template;
		}
	}
	return $template;
}
add_filter( 'template_include', 'ltdh_template_include_he_dao_tao' );
