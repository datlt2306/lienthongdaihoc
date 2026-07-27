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
	// Base archive pagination
	add_rewrite_rule( 'he-dao-tao/page/([0-9]+)/?$', 'index.php?taxonomy=' . LTDH_TAX_TRAINING_TYPE . '&paged=$matches[1]', 'top' );
	// Term archive pagination
	add_rewrite_rule( 'he-dao-tao/([^/]+)/page/([0-9]+)/?$', 'index.php?' . LTDH_TAX_TRAINING_TYPE . '=$matches[1]&paged=$matches[2]', 'top' );
	// Base archive
	add_rewrite_rule( 'he-dao-tao/?$', 'index.php?taxonomy=' . LTDH_TAX_TRAINING_TYPE, 'top' );
	// Term archive
	add_rewrite_rule( 'he-dao-tao/([^/]+)/?$', 'index.php?' . LTDH_TAX_TRAINING_TYPE . '=$matches[1]', 'top' );
}
add_action( 'init', 'ltdh_register_training_type_rewrite' );

// ----------------------------------------------------
// 2. Program Single Posts — No prefix (/%postname%/)
//
// Strategy:
//   a) Register a generic top-priority rule: /slug/ → ?program=slug
//   b) Guard via `request` filter: if slug is not a real program, fall back to pagename
//      so pages, posts, schools, majors etc. continue to work.
//   c) Override post_type_link so get_permalink() outputs /slug/ (not /program/slug/).
//   d) Serve single-program.php via template_include when is_singular('program').
// ----------------------------------------------------

/**
 * Register rewrite rule: /slug/ → index.php?program=slug (top priority).
 */
function ltdh_register_program_rewrite() {
	add_rewrite_rule( '([^/]+)/?$', 'index.php?program=$matches[1]', 'top' );
}
add_action( 'init', 'ltdh_register_program_rewrite' );

/**
 * Guard filter: when the generic rule sets ?program=slug, verify the slug
 * belongs to an actual published program post. If not, fall back to pagename
 * so WordPress pages (/lien-he/, /tin-tuc/, etc.) continue to work.
 *
 * @param array $query_vars Parsed query vars from the rewrite rules.
 * @return array Modified query vars.
 */
function ltdh_program_request_guard( $query_vars ) {
	if ( empty( $query_vars['program'] ) ) {
		return $query_vars;
	}

	$slug = $query_vars['program'];

	// Check if a published program post with this slug exists.
	$program_post = get_posts( [
		'name'           => $slug,
		'post_type'      => 'program',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	] );

	if ( ! empty( $program_post ) ) {
		// Valid program slug — keep query var as-is.
		return $query_vars;
	}

	// Not a program slug. Clean up all CPT-injected vars so WordPress
	// falls through to pagename-based resolution for pages, posts, etc.
	unset( $query_vars['program'] );
	unset( $query_vars['post_type'] );
	unset( $query_vars['name'] );

	// Check if it's a regular post (post type = post).
	$regular_post = get_posts( [
		'name'           => $slug,
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	] );

	if ( ! empty( $regular_post ) ) {
		$query_vars['name'] = $slug;
		$query_vars['post_type'] = 'post';
		return $query_vars;
	}

	// Fall back to pagename so WordPress resolves pages, guides, etc.
	$query_vars['pagename'] = $slug;

	return $query_vars;
}
add_filter( 'request', 'ltdh_program_request_guard' );

/**
 * Override get_permalink() for program posts → /slug/ (no /program/ prefix).
 *
 * @param string  $url  Original URL.
 * @param WP_Post $post Post object.
 * @return string Modified URL.
 */
function ltdh_program_permalink( $url, $post ) {
	if ( $post instanceof WP_Post && 'program' === $post->post_type ) {
		return home_url( '/' . $post->post_name . '/' );
	}
	return $url;
}
add_filter( 'post_type_link', 'ltdh_program_permalink', 10, 2 );

/**
 * Serve single-program.php when WordPress resolves a program post.
 *
 * @param string $template Current template path.
 * @return string Modified template path.
 */
function ltdh_template_include_program( $template ) {
	if ( is_singular( 'program' ) ) {
		$program_template = locate_template( 'single-program.php' );
		if ( $program_template ) {
			return $program_template;
		}
	}
	return $template;
}
add_filter( 'template_include', 'ltdh_template_include_program', 5 );

// ----------------------------------------------------
// 3. Redirect Empty Taxonomy Base URLs
// ----------------------------------------------------
function ltdh_redirect_taxonomy_base() {
	if ( is_tax( LTDH_TAX_CAMPUS ) ) {
		return;
	}
	$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );

	// 301 redirect: /program/slug/ → /slug/
	if ( preg_match( '#^/program/([^/]+)/?$#i', $request_path, $matches ) ) {
		wp_redirect( home_url( '/' . $matches[1] . '/' ), 301 );
		exit;
	}

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
// 4. Use archive-program.php / taxonomy-training_type.php for /he-dao-tao/
// ----------------------------------------------------
function ltdh_template_include_he_dao_tao( $template ) {
	$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
	if ( preg_match( '#^/he-dao-tao(?:/([^/]+))?(?:/page/\d+)?/?$#i', $request_path ) ) {
		$archive_template = locate_template( 'taxonomy-training_type.php' ) ?: locate_template( 'archive-program.php' );
		if ( $archive_template ) {
			return $archive_template;
		}
	}
	return $template;
}
add_filter( 'template_include', 'ltdh_template_include_he_dao_tao' );
