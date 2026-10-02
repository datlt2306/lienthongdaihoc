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
// 1. Training Type Archive (/hinh-thuc-dao-tao/)
// ----------------------------------------------------
function ltdh_register_training_type_rewrite() {
	// Base archive pagination
	add_rewrite_rule( 'hinh-thuc-dao-tao/page/([0-9]+)/?$', 'index.php?post_type=' . LTDH_CPT_PROGRAM . '&paged=$matches[1]', 'top' );
	// Term archive pagination
	add_rewrite_rule( 'hinh-thuc-dao-tao/([^/]+)/page/([0-9]+)/?$', 'index.php?' . LTDH_TAX_TRAINING_TYPE . '=$matches[1]&paged=$matches[2]', 'top' );
	// Base archive
	add_rewrite_rule( 'hinh-thuc-dao-tao/?$', 'index.php?post_type=' . LTDH_CPT_PROGRAM, 'top' );
	// Term archive
	add_rewrite_rule( 'hinh-thuc-dao-tao/([^/]+)/?$', 'index.php?' . LTDH_TAX_TRAINING_TYPE . '=$matches[1]', 'top' );

	// Legacy /he-dao-tao/ fallback rewrite rules
	add_rewrite_rule( 'he-dao-tao/page/([0-9]+)/?$', 'index.php?post_type=' . LTDH_CPT_PROGRAM . '&paged=$matches[1]', 'top' );
	add_rewrite_rule( 'he-dao-tao/([^/]+)/page/([0-9]+)/?$', 'index.php?' . LTDH_TAX_TRAINING_TYPE . '=$matches[1]&paged=$matches[2]', 'top' );
	add_rewrite_rule( 'he-dao-tao/?$', 'index.php?post_type=' . LTDH_CPT_PROGRAM, 'top' );
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
	add_rewrite_rule( '^nganh-([^/]+)/?$', 'index.php?post_type=' . LTDH_CPT_MAJOR . '&name=$matches[1]', 'top' );
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

	$raw_slug = sanitize_title( $query_vars['program'] );

	// Check if raw_slug is a CPT archive base slug
	if ( 'nganh-hoc' === $raw_slug ) {
		unset( $query_vars['program'] );
		unset( $query_vars['name'] );
		$query_vars['post_type'] = 'major';
		return $query_vars;
	}

	if ( in_array( $raw_slug, [ 'truong-da-hoc', 'truong-doi-tac' ], true ) ) {
		unset( $query_vars['program'] );
		unset( $query_vars['name'] );
		$query_vars['post_type'] = 'school';
		return $query_vars;
	}

	if ( in_array( $raw_slug, [ 'hinh-thuc-dao-tao', 'he-dao-tao', 'chuong-trinh' ], true ) ) {
		unset( $query_vars['program'] );
		unset( $query_vars['name'] );
		$query_vars['post_type'] = 'program';
		return $query_vars;
	}

	$slug     = $raw_slug;

	// Check if URL has /nganh-{slug}/ prefix
	$is_nganh_prefix = false;
	if ( 0 === strpos( $raw_slug, 'nganh-' ) ) {
		$slug            = substr( $raw_slug, 6 ); // strip 'nganh-'
		$is_nganh_prefix = true;
	}

	$cache_key = 'ltdh_slug_type_' . $raw_slug;
	$slug_type = wp_cache_get( $cache_key, 'ltdh_rewrites' );

	if ( false === $slug_type ) {
		global $wpdb;

		// 1. If prefix is 'nganh-', check if major post exists with stripped slug or raw slug
		if ( $is_nganh_prefix ) {
			$major_id = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_name IN (%s, %s) AND post_type = 'major' AND post_status = 'publish' LIMIT 1",
				$slug,
				$raw_slug
			) );
			if ( $major_id > 0 ) {
				$slug_type = 'major';
			}
		}

		// 2. Check program post
		if ( ! $slug_type ) {
			$program_id = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'program' AND post_status = 'publish' LIMIT 1",
				$raw_slug
			) );

			if ( $program_id > 0 ) {
				$slug_type = 'program';
			} else {
				// 3. Check school post
				$school_id = (int) $wpdb->get_var( $wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'school' AND post_status = 'publish' LIMIT 1",
					$raw_slug
				) );

				if ( $school_id > 0 ) {
					$slug_type = 'school';
				} else {
					// 4. Check major post directly without prefix
					$major_id = (int) $wpdb->get_var( $wpdb->prepare(
						"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'major' AND post_status = 'publish' LIMIT 1",
						$raw_slug
					) );
					if ( $major_id > 0 ) {
						$slug_type = 'major';
					} else {
						// 5. Check regular post
						$post_id = (int) $wpdb->get_var( $wpdb->prepare(
							"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'post' AND post_status = 'publish' LIMIT 1",
							$raw_slug
						) );
						$slug_type = ( $post_id > 0 ) ? 'post' : 'page';
					}
				}
			}
		}
		wp_cache_set( $cache_key, $slug_type, 'ltdh_rewrites', 3600 );
	}

	if ( 'program' === $slug_type ) {
		return $query_vars;
	}

	// Clean up generic program query vars
	unset( $query_vars['program'] );
	unset( $query_vars['post_type'] );
	unset( $query_vars['name'] );

	if ( 'major' === $slug_type ) {
		global $wpdb;
		$real_post_name = $wpdb->get_var( $wpdb->prepare(
			"SELECT post_name FROM {$wpdb->posts} WHERE post_name IN (%s, %s) AND post_type = 'major' AND post_status = 'publish' LIMIT 1",
			$slug,
			$raw_slug
		) );
		$target_slug = $real_post_name ?: $slug;

		$query_vars['major']     = $target_slug;
		$query_vars['name']      = $target_slug;
		$query_vars['post_type'] = 'major';
		return $query_vars;
	}

	if ( 'school' === $slug_type ) {
		$query_vars['school']    = $raw_slug;
		$query_vars['name']      = $raw_slug;
		$query_vars['post_type'] = 'school';
		return $query_vars;
	}

	if ( 'post' === $slug_type ) {
		$query_vars['name']      = $raw_slug;
		$query_vars['post_type'] = 'post';
		return $query_vars;
	}

	// Fall back to pagename so WordPress resolves pages, guides, etc.
	$query_vars['pagename'] = $raw_slug;

	return $query_vars;
}
add_filter( 'request', 'ltdh_program_request_guard' );

/**
 * Override get_permalink() for program and school posts → /slug/ (no /program/ or /truong-doi-tac/ prefix).
 *
 * @param string  $url  Original URL.
 * @param WP_Post $post Post object.
 * @return string Modified URL.
 */
function ltdh_program_permalink( $url, $post ) {
	if ( $post instanceof WP_Post ) {
		if ( in_array( $post->post_type, [ 'program', 'school' ], true ) ) {
			return home_url( '/' . $post->post_name . '/' );
		}
		if ( 'major' === $post->post_type ) {
			return home_url( '/nganh-' . $post->post_name . '/' );
		}
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
// 3. Redirect Empty Taxonomy Base URLs & Legacy CPT Prefixes
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

	// 301 redirect: /truong-doi-tac/slug/ → /slug/ (except archive base /truong-doi-tac/ and paginated /truong-doi-tac/page/X/)
	if ( preg_match( '#^/truong-doi-tac/([^/]+)/?$#i', $request_path, $matches ) ) {
		$sub_slug = $matches[1];
		if ( 'page' !== $sub_slug && ! empty( $sub_slug ) ) {
			wp_redirect( home_url( '/' . $sub_slug . '/' ), 301 );
			exit;
		}
	}

	// 301 redirect: /nganh-hoc/slug/ → /nganh-slug/ (except archive base /nganh-hoc/ and paginated /nganh-hoc/page/X/)
	if ( preg_match( '#^/nganh-hoc/([^/]+)/?$#i', $request_path, $matches ) ) {
		$sub_slug = $matches[1];
		if ( 'page' !== $sub_slug && ! empty( $sub_slug ) ) {
			wp_redirect( home_url( '/nganh-' . $sub_slug . '/' ), 301 );
			exit;
		}
	}


	// 301 redirect: /he-dao-tao/... → /hinh-thuc-dao-tao/...
	if ( preg_match( '#^/he-dao-tao(?:/([^/]+))?/?$#i', $request_path, $matches ) ) {
		$sub = $matches[1] ?? '';
		if ( 'tu-xa' === $sub ) {
			$sub = 'dao-tao-tu-xa';
		}
		$redirect_url = home_url( '/hinh-thuc-dao-tao/' . ( $sub ? $sub . '/' : '' ) );
		if ( ! empty( $_GET ) ) {
			$redirect_url = add_query_arg( $_GET, $redirect_url );
		}
		wp_redirect( $redirect_url, 301 );
		exit;
	}

	// 301 redirect: Clean empty query parameters (e.g. ?s=&truong=abc&nganh=&sort= -> ?truong=abc)
	if ( ! empty( $_GET ) ) {
		$cleaned_get = [];
		$has_dirty   = false;
		foreach ( $_GET as $key => $val ) {
			if ( is_string( $val ) && '' === trim( $val ) ) {
				$has_dirty = true;
			} else {
				$cleaned_get[ $key ] = $val;
			}
		}
		if ( $has_dirty ) {
			$base_url     = strtok( $_SERVER['REQUEST_URI'] ?? '/', '?' );
			$redirect_url = home_url( $base_url );
			if ( ! empty( $cleaned_get ) ) {
				$redirect_url = add_query_arg( $cleaned_get, $redirect_url );
			}
			wp_redirect( $redirect_url, 301 );
			exit;
		}
	}

	if ( preg_match( '#^/co-so/?$#i', $request_path ) ) {
		wp_redirect( home_url( '/hinh-thuc-dao-tao/dao-tao-tu-xa/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
		$redirect_url = home_url( '/hinh-thuc-dao-tao/' );
		if ( ! empty( $_GET ) ) {
			$redirect_url = add_query_arg( $_GET, $redirect_url );
		}
		wp_redirect( $redirect_url, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'ltdh_redirect_taxonomy_base' );

// ----------------------------------------------------
// 4. Custom Archive Templates Loader for /hinh-thuc-dao-tao/, /nganh-hoc/, /truong-da-hoc/
// ----------------------------------------------------
function ltdh_template_include_he_dao_tao( $template ) {
	$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );

	if ( preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)(?:/([^/]+))?(?:/page/\d+)?/?$#i', $request_path ) ) {
		global $wp_query;
		if ( $wp_query ) {
			$wp_query->is_404 = false;
			status_header( 200 );
		}
		$archive_template = locate_template( 'taxonomy-training_type.php' ) ?: locate_template( 'archive-program.php' );
		if ( $archive_template ) {
			return $archive_template;
		}
	}

	if ( preg_match( '#^/nganh-hoc(?:/page/\d+)?/?$#i', $request_path ) ) {
		global $wp_query;
		if ( $wp_query ) {
			$wp_query->is_404 = false;
			status_header( 200 );
		}
		$archive_template = locate_template( 'archive-major.php' );
		if ( $archive_template ) {
			return $archive_template;
		}
	}

	if ( preg_match( '#^/(?:truong-da-hoc|truong-doi-tac)(?:/page/\d+)?/?$#i', $request_path ) ) {
		global $wp_query;
		if ( $wp_query ) {
			$wp_query->is_404 = false;
			status_header( 200 );
		}
		$archive_template = locate_template( 'archive-school.php' );
		if ( $archive_template ) {
			return $archive_template;
		}
	}

	return $template;
}
add_filter( 'template_include', 'ltdh_template_include_he_dao_tao' );
