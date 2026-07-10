<?php
/**
 * Comparison System — Core Engine
 *
 * Handles rewrite rules, data resolution, highlight logic,
 * REST API, and AJAX endpoints for the comparison feature.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Rewrite Rules & Query Vars
// ----------------------------------------------------
add_action( 'init', 'ltdh_compare_register_rewrite_rules' );
add_filter( 'query_vars', 'ltdh_compare_register_query_vars' );
add_action( 'template_redirect', 'ltdh_compare_template_redirect' );

function ltdh_compare_register_rewrite_rules() {
	// SEO-friendly: /so-sanh/chuong-trinh/slug-vs-slug/
	add_rewrite_rule(
		'so-sanh/chuong-trinh/(.+?)/?$',
		'index.php?ltdh_compare=program&ltdh_compare_slug=$matches[1]',
		'top'
	);
}

function ltdh_compare_register_query_vars( $vars ) {
	if ( ! is_array( $vars ) ) {
		$vars = [];
	}
	$vars[] = 'ltdh_compare';
	$vars[] = 'ltdh_compare_slug';
	return $vars;
}

function ltdh_compare_template_redirect() {
	$type = get_query_var( 'ltdh_compare' );
	if ( ! $type || $type !== 'program' ) {
		return;
	}

	$slug = get_query_var( 'ltdh_compare_slug' );
	if ( ! $slug ) {
		return;
	}

	$ids = ltdh_compare_resolve_ids_from_slug( $type, $slug );
	if ( empty( $ids ) || count( $ids ) < 2 ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		return;
	}

	// Store resolved IDs for templates
	global $ltdh_compare_ids;
	$ltdh_compare_ids = $ids;

	$template = get_template_directory() . '/page-compare-' . $type . '.php';
	if ( file_exists( $template ) ) {
		include $template;
		exit;
	}
}

/**
 * Resolve SEO slug like "ptit-cntt-vs-tnu-cntt" → [post_id, post_id]
 */
function ltdh_compare_resolve_ids_from_slug( $type, $slug ) {
	// Split on -vs- separator
	$parts = preg_split( '/-vs-/', $slug, -1, PREG_SPLIT_NO_EMPTY );
	if ( count( $parts ) < 2 ) {
		return [];
	}

	$post_type_map = [
		'program' => 'program',
	];
	$post_type = $post_type_map[ $type ];

	$ids = [];
	foreach ( $parts as $part ) {
		$part = sanitize_text_field( trim( $part ) );
		if ( empty( $part ) ) {
			continue;
		}

		// Try to find by slug first
		$post = get_page_by_path( $part, OBJECT, $post_type );
		if ( $post ) {
			$ids[] = $post->ID;
			continue;
		}

		// Fallback: search by title similarity
		$found = get_posts( [
			'post_type'      => $post_type,
			's'              => $part,
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		] );
		if ( ! empty( $found ) ) {
			$ids[] = $found[0]->ID;
		}
	}

	return $ids;
}

/**
 * Generate SEO-friendly slug from IDs and post data.
 */
function ltdh_compare_generate_slug( $type, $ids ) {
	$slugs = [];
	foreach ( $ids as $id ) {
		$slugs[] = get_post_field( 'post_name', $id );
	}
	return implode( '-vs-', $slugs );
}

// ----------------------------------------------------
// 2. Data Resolver
// ----------------------------------------------------

/**
 * Resolve a program's comparison data from all sources.
 */
function ltdh_compare_resolve_program( $program_id ) {
	$school_id  = get_field( 'school_relationship', $program_id );
	$major_id   = get_field( 'major_relationship', $program_id );

	// Resolve taxonomy terms
	$training_terms = wp_get_post_terms( $program_id, 'training_type' );
	$training_type  = ( ! is_wp_error( $training_terms ) && ! empty( $training_terms ) ) ? $training_terms[0]->name : '';

	$campus_terms = wp_get_post_terms( $program_id, 'campus' );
	$campus_names = [];
	if ( ! is_wp_error( $campus_terms ) && ! empty( $campus_terms ) ) {
		foreach ( $campus_terms as $term ) {
			$campus_names[] = $term->name;
		}
	}
	$campus_name = ! empty( $campus_names ) ? implode( ', ', $campus_names ) : '';

	$learning_details = ltdh_get_program_learning_details( $program_id );

	$tuition_str  = get_field( 'tuition_fee', $program_id ) ?: '';
	$duration_str = get_field( 'duration', $program_id ) ?: '';
	$enrollment   = get_field( 'enrollment_period', $program_id ) ?: '';

	$school_logo = '';
	if ( $school_id ) {
		$image_id = ltdh_get_school_image_id( $school_id );
		if ( $image_id ) {
			$school_logo = wp_get_attachment_image_url( $image_id, 'thumbnail' );
		}
		if ( ! $school_logo ) {
			$school_logo = get_the_post_thumbnail_url( $school_id, 'thumbnail' ) ?: '';
		}
	}

	return [
		'id'                    => $program_id,
		'title'                 => get_the_title( $program_id ),
		'permalink'             => get_permalink( $program_id ),
		'thumbnail'             => get_the_post_thumbnail_url( $program_id, 'medium' ) ?: get_stylesheet_directory_uri() . '/assets/images/banner-default.jpg',
		'excerpt'               => get_the_excerpt( $program_id ),
		'school'                => $school_id ? [
			'id'        => $school_id,
			'title'     => get_the_title( $school_id ),
			'logo'      => $school_logo,
			'permalink' => get_permalink( $school_id ),
			'hotline'   => get_field( 'hotline', $school_id ) ?: '',
			'address'   => get_field( 'address', $school_id ) ?: '',
			'website'   => get_field( 'website', $school_id ) ?: '',
		] : null,
		'major'                 => $major_id ? [
			'id'        => $major_id,
			'title'     => get_the_title( $major_id ),
			'code'      => get_field( 'major_code', $major_id ) ?: '',
			'permalink' => get_permalink( $major_id ),
		] : null,
		'training_type'         => $training_type,
		'campus'                => $campus_name,
		'campus_info'           => $campus_name,
		'learning_mode'         => $learning_details['mode'],
		'tuition_fee'           => $tuition_str,
		'tuition_numeric'       => ltdh_compare_parse_tuition( $tuition_str ),
		'duration'              => $duration_str,
		'duration_numeric'      => ltdh_compare_parse_duration( $duration_str ),
		'degree_type'           => get_field( 'degree_type', $program_id ) ?: '',
		'schedule'              => get_field( 'schedule', $program_id ) ?: '',
		'enrollment_period'     => $enrollment,
		'enrollment_deadline'   => ltdh_compare_parse_enrollment_date( $enrollment ),
		'admission_requirements' => get_field( 'admission_requirements', $program_id ) ?: '',
		'required_documents'    => get_field( 'required_documents', $program_id ) ?: '',
		'target_students'       => get_field( 'target_students', $program_id ) ?: '',
		'career_opportunities'  => get_field( 'career_opportunities', $program_id ) ?: '',
		'advantages'            => get_field( 'program_benefits', $program_id ) ?: '',
		'disadvantages'         => get_field( 'disadvantages', $program_id ) ?: '',
		'diploma_value'         => get_field( 'diploma_value', $program_id ) ?: '',
		'why_choose_us'         => get_field( 'why_choose_us', $program_id ) ?: '',
		'hotline'               => get_field( 'hotline_override', $program_id ) ?: ( $school_id ? get_field( 'hotline', $school_id ) : '' ) ?: get_field( 'global_hotline', 'options' ),
	];
}

// ----------------------------------------------------
// 3. Numeric Parsing Helpers
// ----------------------------------------------------

/**
 * Parse tuition string to numeric value for comparison.
 * Handles: "450,000đ/tín chỉ", "1.200.000đ", "900,000 - 1,500,000đ/tc"
 */
function ltdh_compare_parse_tuition( $str ) {
	if ( empty( $str ) ) {
		return 0;
	}

	// Remove non-numeric chars except dots, commas, hyphens
	$clean = preg_replace( '/[^\d.,\-]/', '', $str );

	// If it's a range like "900,000 - 1,500,000", take the min
	if ( preg_match( '/(\d[\d.,]*)\s*-\s*(\d[\d.,]*)/', $clean, $m ) ) {
		$clean = $m[1];
	}

	// Handle Vietnamese number format: 1.200.000 (dots as thousands separator)
	// Remove dots if there are multiple (thousands separator)
	if ( preg_match_all( '/\./', $clean ) > 1 ) {
		$clean = str_replace( '.', '', $clean );
	}

	// Remove commas
	$clean = str_replace( ',', '', $clean );
	$clean = trim( $clean );

	if ( is_numeric( $clean ) ) {
		return (float) $clean;
	}

	return 0;
}

/**
 * Parse duration string to numeric years for comparison.
 * Handles: "1.5 - 2 năm", "2 năm", "18 tháng"
 */
function ltdh_compare_parse_duration( $str ) {
	if ( empty( $str ) ) {
		return 0;
	}

	$lower = mb_strtolower( $str, 'UTF-8' );

	// Check for months
	if ( preg_match( '/(\d+(?:\.\d+)?)\s*tháng/', $lower, $m ) ) {
		$months = (float) $m[1];
		if ( preg_match( '/(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)\s*tháng/', $lower, $r ) ) {
			$months = (float) $r[1];
		}
		return round( $months / 12, 1 );
	}

	// Check for range "1.5 - 2 năm"
	if ( preg_match( '/(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)\s*năm/', $lower, $m ) ) {
		return (float) $m[1];
	}

	// Single year value
	if ( preg_match( '/(\d+(?:\.\d+)?)\s*năm/', $lower, $m ) ) {
		return (float) $m[1];
	}

	// Plain number
	if ( preg_match( '/(\d+(?:\.\d+)?)/', $str, $m ) ) {
		return (float) $m[1];
	}

	return 0;
}

/**
 * Parse enrollment period string to a date for comparison.
 * Returns YYYY-MM-DD or empty string.
 */
function ltdh_compare_parse_enrollment_date( $str ) {
	if ( empty( $str ) ) {
		return '';
	}

	// Try to find a date like 30/11/2026 or 30-11-2026
	if ( preg_match( '/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $str, $m ) ) {
		return sprintf( '%d-%02d-%02d', $m[3], $m[2], $m[1] );
	}

	// Try Vietnamese format: "hết 30/11/2026"
	if ( preg_match( '/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $str, $m ) ) {
		$year = strlen( $m[3] ) === 2 ? '20' . $m[3] : $m[3];
		return sprintf( '%d-%02d-%02d', $year, $m[2], $m[1] );
	}

	return '';
}

// ----------------------------------------------------
// 4. Highlight Logic
// ----------------------------------------------------

/**
 * Compute which items are "best" for each highlightable attribute.
 * Returns array of attribute => [ best_ids => [], ... ]
 */
function ltdh_compare_compute_highlights( $type, $items ) {
	$highlights = [];

	if ( $type === 'program' ) {
		// Lowest tuition
		$tuitions = [];
		foreach ( $items as $item ) {
			if ( $item['tuition_numeric'] > 0 ) {
				$tuitions[ $item['id'] ] = $item['tuition_numeric'];
			}
		}
		if ( count( $tuitions ) >= 2 ) {
			$min = min( $tuitions );
			$best = array_keys( $tuitions, $min, true );
			$highlights['tuition_fee'] = [
				'best_ids' => $best,
				'value'    => $min,
				'label'    => 'Học phí thấp nhất',
			];
		}

		// Shortest duration
		$durations = [];
		foreach ( $items as $item ) {
			if ( $item['duration_numeric'] > 0 ) {
				$durations[ $item['id'] ] = $item['duration_numeric'];
			}
		}
		if ( count( $durations ) >= 2 ) {
			$min = min( $durations );
			$best = array_keys( $durations, $min, true );
			$highlights['duration'] = [
				'best_ids' => $best,
				'value'    => $min,
				'label'    => 'Thời gian ngắn nhất',
			];
		}

		// Latest enrollment deadline
		$deadlines = [];
		foreach ( $items as $item ) {
			if ( ! empty( $item['enrollment_deadline'] ) ) {
				$deadlines[ $item['id'] ] = $item['enrollment_deadline'];
			}
		}
		if ( count( $deadlines ) >= 2 ) {
			$max = max( $deadlines );
			$best = array_keys( $deadlines, $max, true );
			$highlights['enrollment_period'] = [
				'best_ids' => $best,
				'value'    => $max,
				'label'    => 'Hạn muộn nhất',
			];
		}
	}

	if ( $type === 'school' ) {
		// Highest rating
		$ratings = [];
		foreach ( $items as $item ) {
			$r = (float) $item['rating'];
			if ( $r > 0 ) {
				$ratings[ $item['id'] ] = $r;
			}
		}
		if ( count( $ratings ) >= 2 ) {
			$max = max( $ratings );
			$best = array_keys( $ratings, $max, true );
			$highlights['rating'] = [
				'best_ids' => $best,
				'value'    => $max,
				'label'    => 'Đánh giá cao nhất',
			];
		}

		// Most programs
		$counts = [];
		foreach ( $items as $item ) {
			if ( $item['programs_count'] > 0 ) {
				$counts[ $item['id'] ] = $item['programs_count'];
			}
		}
		if ( count( $counts ) >= 2 ) {
			$max = max( $counts );
			$best = array_keys( $counts, $max, true );
			$highlights['programs_count'] = [
				'best_ids' => $best,
				'value'    => $max,
				'label'    => 'Nhiều chương trình nhất',
			];
		}

		// Lowest starting tuition
		$tuitions = [];
		foreach ( $items as $item ) {
			if ( $item['tuition_min'] > 0 ) {
				$tuitions[ $item['id'] ] = $item['tuition_min'];
			}
		}
		if ( count( $tuitions ) >= 2 ) {
			$min = min( $tuitions );
			$best = array_keys( $tuitions, $min, true );
			$highlights['tuition_min'] = [
				'best_ids' => $best,
				'value'    => $min,
				'label'    => 'Học phí thấp nhất',
			];
		}
	}

	return $highlights;
}

/**
 * Check if an item is the best for a given attribute.
 */
function ltdh_compare_is_best( $highlights, $attribute, $item_id ) {
	return isset( $highlights[ $attribute ] ) && in_array( $item_id, $highlights[ $attribute ]['best_ids'], true );
}

/**
 * Get best badge HTML.
 */
function ltdh_compare_badge( $label ) {
	return '<span class="inline-flex items-center gap-0.5 text-xs font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded-lg ml-1.5 whitespace-nowrap">🏆 ' . esc_html( $label ) . '</span>';
}

// ----------------------------------------------------
// 5. Helper: Render comparison field value with fallback
// ----------------------------------------------------
function ltdh_compare_field( $value, $is_html = false ) {
	if ( empty( $value ) || $value === '<p></p>' || $value === '<p>\n</p>' ) {
		return '<span class="text-slate-300 italic">Chưa cập nhật</span>';
	}
	if ( $is_html ) {
		return '<div class="prose prose-sm prose-slate max-w-none">' . wp_kses_post( $value ) . '</div>';
	}
	return esc_html( $value );
}

// ----------------------------------------------------
// 6. REST API Endpoints
// ----------------------------------------------------
add_action( 'rest_api_init', 'ltdh_compare_register_rest_routes' );

function ltdh_compare_register_rest_routes() {
	register_rest_route( 'ltdh/v1', '/compare/(?P<type>program)', [
		'methods'             => 'GET',
		'callback'            => 'ltdh_compare_rest_get_items',
		'permission_callback' => '__return_true',
		'args' => [
			'ids' => [
				'required'    => true,
				'validate_callback' => function( $param ) {
					return ! empty( $param ) && is_string( $param );
				},
			],
		],
	] );
}

function ltdh_compare_rest_get_items( $request ) {
	$type = $request->get_param( 'type' );
	$ids_raw = $request->get_param( 'ids' );
	$ids = array_map( 'intval', explode( ',', $ids_raw ) );
	$ids = array_filter( $ids );
	$ids = array_slice( $ids, 0, 4 );

	if ( count( $ids ) < 2 ) {
		return new WP_Error( 'insufficient_items', 'At least 2 items required', [ 'status' => 400 ] );
	}

	$valid_post_types = [
		'program' => 'program',
	];

	if ( ! isset( $valid_post_types[ $type ] ) ) {
		return new WP_Error( 'invalid_type', 'Invalid comparison type', [ 'status' => 400 ] );
	}

	$items = [];
	foreach ( $ids as $id ) {
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== $valid_post_types[ $type ] || $post->post_status !== 'publish' ) {
			continue;
		}

		switch ( $type ) {
			case 'program':
				$items[] = ltdh_compare_resolve_program( $id );
				break;
		}
	}

	if ( count( $items ) < 2 ) {
		return new WP_Error( 'not_found', 'Could not find enough valid items', [ 'status' => 404 ] );
	}

	$highlights = ltdh_compare_compute_highlights( $type, $items );

	return rest_ensure_response( [
		'type'       => $type,
		'items'      => $items,
		'highlights' => $highlights,
	] );
}

// ----------------------------------------------------
// 7. AJAX Endpoints (for tray add/remove)
// ----------------------------------------------------
add_action( 'wp_ajax_ltdh_compare_add', 'ltdh_compare_ajax_add' );
add_action( 'wp_ajax_ltdh_compare_remove', 'ltdh_compare_ajax_remove' );
add_action( 'wp_ajax_nopriv_ltdh_compare_add', 'ltdh_compare_ajax_add' );
add_action( 'wp_ajax_nopriv_ltdh_compare_remove', 'ltdh_compare_ajax_remove' );

function ltdh_compare_ajax_add() {
	check_ajax_referer( 'ltdh_compare_nonce', 'nonce' );

	$type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
	$id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

	$valid_types = [ 'program', 'school' ];
	if ( ! in_array( $type, $valid_types, true ) || $id <= 0 ) {
		wp_send_json_error( [ 'message' => 'Dữ liệu không hợp lệ.' ] );
	}

	$post = get_post( $id );
	$post_type_map = [ 'program' => 'program', 'school' => 'school' ];
	if ( ! $post || $post->post_type !== $post_type_map[ $type ] || $post->post_status !== 'publish' ) {
		wp_send_json_error( [ 'message' => 'Bài viết không tồn tại.' ] );
	}

	$thumbnail = get_the_post_thumbnail_url( $id, 'thumbnail' ) ?: get_stylesheet_directory_uri() . '/assets/images/banner-default.jpg';

	wp_send_json_success( [
		'id'        => $id,
		'type'      => $type,
		'title'     => get_the_title( $id ),
		'thumbnail' => $thumbnail,
		'permalink' => get_permalink( $id ),
	] );
}

function ltdh_compare_ajax_remove() {
	check_ajax_referer( 'ltdh_compare_nonce', 'nonce' );
	wp_send_json_success();
}

// ----------------------------------------------------
// 8. Compare Page Helper Functions
// ----------------------------------------------------

/**
 * Get the global hotline for CTA sections.
 */
function ltdh_compare_get_global_hotline() {
	return get_field( 'global_hotline', 'options' ) ?: '0389198653';
}

/**
 * Get the global Zalo URL.
 */
function ltdh_compare_get_zalo_url() {
	return get_field( 'global_zalo_url', 'options' ) ?: 'https://zalo.me';
}

/**
 * Get current compare type from query.
 */
function ltdh_compare_get_type() {
	return get_query_var( 'ltdh_compare' ) ?: '';
}

/**
 * Get resolved compare IDs from the global.
 */
function ltdh_compare_get_ids() {
	global $ltdh_compare_ids;
	return ! empty( $ltdh_compare_ids ) ? $ltdh_compare_ids : [];
}

/**
 * Get resolved compare items.
 */
function ltdh_compare_get_items( $type = '', $ids = [] ) {
	if ( empty( $type ) ) {
		$type = ltdh_compare_get_type();
	}
	if ( empty( $ids ) ) {
		$ids = ltdh_compare_get_ids();
	}

	$items = [];
	foreach ( $ids as $id ) {
		switch ( $type ) {
			case 'program':
				$items[] = ltdh_compare_resolve_program( $id );
				break;
		}
	}
	return $items;
}

/**
 * Get highlights for items.
 */
function ltdh_compare_get_highlights( $type = '', $items = [] ) {
	if ( empty( $type ) ) {
		$type = ltdh_compare_get_type();
	}
	if ( empty( $items ) ) {
		$items = ltdh_compare_get_items( $type );
	}
	return ltdh_compare_compute_highlights( $type, $items );
}
