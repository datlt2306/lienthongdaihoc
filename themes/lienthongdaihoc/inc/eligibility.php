<?php
/**
 * Eligibility Checker — Core Engine
 *
 * Handles input validation, program matching, scoring, ranking,
 * lead capture, and REST API endpoints.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ----------------------------------------------------
// 1. Database Table
// ----------------------------------------------------

/**
 * Create eligibility_checks table on theme activation.
 */
function ltdh_elig_create_table() {
	global $wpdb;
	$table   = $wpdb->prefix . 'ltdh_eligibility_checks';
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		session_token varchar(64) NOT NULL,
		input_education varchar(50) NOT NULL DEFAULT '',
		input_major_id bigint(20) UNSIGNED DEFAULT 0,
		input_graduation year DEFAULT NULL,
		input_desired_major bigint(20) UNSIGNED DEFAULT 0,
		input_training_type varchar(50) NOT NULL DEFAULT '',
		input_campus varchar(50) NOT NULL DEFAULT '',
		input_budget varchar(50) NOT NULL DEFAULT '',
		total_candidates int(11) DEFAULT 0,
		eligible_count int(11) DEFAULT 0,
		top_score int(11) DEFAULT 0,
		top_program_id bigint(20) UNSIGNED DEFAULT 0,
		phone varchar(50) DEFAULT '',
		email varchar(100) DEFAULT '',
		lead_captured tinyint(1) DEFAULT 0,
		lead_id bigint(20) UNSIGNED DEFAULT NULL,
		created_at datetime NOT NULL,
		completed_at datetime DEFAULT NULL,
		referrer_url text,
		PRIMARY KEY  (id),
		KEY idx_session (session_token),
		KEY idx_created (created_at),
		KEY idx_lead (lead_captured, created_at)
	) $charset;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
add_action( 'after_switch_theme', 'ltdh_elig_create_table' );

// Run on module load as well (safe — dbDelta is idempotent)
add_action( 'init', function() {
	if ( get_option( 'ltdh_elig_table_version' ) !== '1.0' ) {
		ltdh_elig_create_table();
		update_option( 'ltdh_elig_table_version', '1.0' );
	}
});

// ----------------------------------------------------
// 2. Eligibility Page Template
// ----------------------------------------------------

/**
 * Register eligibility page template.
 */
function ltdh_elig_register_page_template( $templates ) {
	$templates['page-eligible.php'] = 'Kiểm tra điều kiện';
	return $templates;
}
add_filter( 'theme_page_templates', 'ltdh_elig_register_page_template' );

/**
 * Redirect to eligibility template.
 */
function ltdh_elig_template_redirect( $template ) {
	if ( is_page() ) {
		$page_template = get_page_template_slug();
		if ( $page_template === 'page-eligible.php' ) {
			$custom = get_template_directory() . '/page-eligible.php';
			if ( file_exists( $custom ) ) {
				return $custom;
			}
		}
	}
	return $template;
}
add_filter( 'template_include', 'ltdh_elig_template_redirect' );

// ----------------------------------------------------
// 3. Asset Enqueue
// ----------------------------------------------------

function ltdh_elig_enqueue_assets() {
	if ( is_page_template( 'page-eligible.php' ) ) {
		wp_enqueue_style(
			'ltdh-eligibility-css',
			get_template_directory_uri() . '/assets/css/eligibility.css',
			[],
			'1.0.0'
		);

		wp_enqueue_script(
			'ltdh-eligibility-js',
			get_template_directory_uri() . '/assets/js/eligibility.js',
			[],
			'1.0.0',
			true
		);

		wp_localize_script( 'ltdh-eligibility-js', 'ltdh_elig', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'ltdh_elig_nonce' ),
			'rest_url' => esc_url_raw( rest_url( 'ltdh/v1/eligibility/' ) ),
			'i18n'     => [
				'checking'   => 'Đang kiểm tra...',
				'results'    => 'Kết quả',
				'step'       => 'Bước',
				'of'         => 'trên',
				'next'       => 'Tiếp theo',
				'back'       => 'Quay lại',
				'submit'     => 'Kiểm tra ngay',
				'perfect'    => 'Phù hợp hoàn hảo',
				'very_good'  => 'Phù hợp tốt',
				'possible'   => 'Có thể phù hợp',
				'not_match'  => 'Chưa phù hợp',
				'no_results' => 'Chưa có chương trình phù hợp',
				'register'   => 'Đăng ký ngay',
				'consult'    => 'Tư vấn miễn phí',
				'compare'    => 'Thêm vào so sánh',
			],
		]);
	}
}
add_action( 'wp_enqueue_scripts', 'ltdh_elig_enqueue_assets' );

// ----------------------------------------------------
// 4. AJAX Handler — Check Eligibility
// ----------------------------------------------------

add_action( 'wp_ajax_ltdh_elig_check', 'ltdh_elig_ajax_check' );
add_action( 'wp_ajax_nopriv_ltdh_elig_check', 'ltdh_elig_ajax_check' );

add_action( 'wp_ajax_ltdh_elig_lead', 'ltdh_elig_ajax_lead' );
add_action( 'wp_ajax_nopriv_ltdh_elig_lead', 'ltdh_elig_ajax_lead' );

function ltdh_elig_ajax_check() {
	check_ajax_referer( 'ltdh_elig_nonce', 'nonce' );

	$input = [
		'education'     => sanitize_text_field( $_POST['education'] ?? '' ),
		'major_id'      => intval( $_POST['major_id'] ?? 0 ),
		'graduation'    => intval( $_POST['graduation'] ?? 0 ),
		'desired_major' => intval( $_POST['desired_major'] ?? 0 ),
		'training_type' => sanitize_text_field( $_POST['training_type'] ?? '' ),
		'campus'        => sanitize_text_field( $_POST['campus'] ?? '' ),
		'budget'        => sanitize_text_field( $_POST['budget'] ?? '' ),
		'phone'         => sanitize_text_field( $_POST['phone'] ?? '' ),
		'email'         => sanitize_email( $_POST['email'] ?? '' ),
	];

	// Validate required fields
	$validation = ltdh_elig_validate_input( $input );
	if ( is_wp_error( $validation ) ) {
		wp_send_json_error( [ 'message' => $validation->get_error_message() ] );
	}

	// Run eligibility engine
	$results = ltdh_elig_run_check( $input );

	// Store check in database
	$check_id = ltdh_elig_store_check( $input, $results );

	// Capture lead if phone/email provided
	$lead_id = 0;
	if ( ! empty( $input['phone'] ) || ! empty( $input['email'] ) ) {
		$lead_id = ltdh_elig_capture_lead( $input, $results, $check_id );
	}

	wp_send_json_success( [
		'check_id'      => $check_id,
		'input'         => $input,
		'eligible'      => $results['eligible'],
		'programs'      => $results['programs'],
		'total'         => $results['total_candidates'],
		'eligible_count'=> $results['eligible_count'],
		'top_score'     => $results['top_score'],
		'alternatives'  => $results['alternatives'],
		'lead_id'       => $lead_id,
	] );
}

// ----------------------------------------------------
// 5. Input Validation
// ----------------------------------------------------

function ltdh_elig_validate_input( $input ) {
	$valid_education = [ 'thap-phan', 'trung-cap', 'cao-dang', 'dai-hoc', 'thac-si' ];
	$valid_training  = [ 'lien-thong', 'van-bang-2', 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ];
	$valid_campus    = [ 'ha-noi', 'ho-chi-minh', 'da-nang', 'thai-nguyen', 'online' ];
	$valid_budget    = [ 'duoi-20-trieu', '20-30-trieu', '30-50-trieu', 'tren-50-trieu' ];

	if ( empty( $input['education'] ) || ! in_array( $input['education'], $valid_education, true ) ) {
		return new WP_Error( 'invalid_education', 'Vui lòng chọn trình độ học vấn.' );
	}

	if ( ! empty( $input['training_type'] ) && ! in_array( $input['training_type'], $valid_training, true ) ) {
		return new WP_Error( 'invalid_training', 'Hệ đào tạo không hợp lệ.' );
	}

	if ( ! empty( $input['campus'] ) && ! in_array( $input['campus'], $valid_campus, true ) ) {
		return new WP_Error( 'invalid_campus', 'Cơ sở không hợp lệ.' );
	}

	if ( ! empty( $input['budget'] ) && ! in_array( $input['budget'], $valid_budget, true ) ) {
		return new WP_Error( 'invalid_budget', 'Ngân sách không hợp lệ.' );
	}

	return true;
}

// ----------------------------------------------------
// 6. Core Eligibility Engine
// ----------------------------------------------------

function ltdh_elig_run_check( $input ) {
	// Load rule definitions
	$compatibility = ltdh_elig_get_training_type_compatibility();
	$hierarchy     = ltdh_elig_get_education_hierarchy();
	$weights       = ltdh_elig_get_scoring_weights();
	$budget_ranges = ltdh_elig_get_budget_ranges();
	$major_rels    = ltdh_elig_get_major_relationships();

	// Load user's major slug for relationship lookup
	$user_major_slug = '';
	if ( $input['major_id'] ) {
		$user_major_slug = get_post_field( 'post_name', $input['major_id'] );
	}

	// Step 1: Load candidate programs
	$query_args = [
		'post_type'      => 'program',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'     => [
			[
				'key'     => 'admission_status',
				'value'   => 'tuyen-sinh',
				'compare' => '=',
			],
		],
	];

	// Pre-filter by training type if specified
	if ( ! empty( $input['training_type'] ) ) {
		$query_args['tax_query'] = [
			[
				'taxonomy' => 'training_type',
				'field'    => 'slug',
				'terms'    => $input['training_type'],
			],
		];
	}

	$candidates = get_posts( $query_args );
	$total_candidates = count( $candidates );

	// Step 2: Apply hard filters and score
	$eligible = [];
	$rejected = [];

	foreach ( $candidates as $program ) {
		$program_id = $program->ID;

		// --- HARD FILTERS ---
		$hard_fail = false;
		$fail_reason = '';

		// H01: Education level minimum
		$min_edu = get_field( 'elig_min_education', $program_id ) ?: '';
		if ( $min_edu ) {
			$user_level = $hierarchy[ $input['education'] ] ?? 0;
			$min_level  = $hierarchy[ $min_edu ] ?? 0;
			if ( $user_level < $min_level ) {
				$hard_fail = true;
				$fail_reason = 'Trình độ tối thiểu yêu cầu: ' . ltdh_elig_get_education_label( $min_edu );
			}
		}

		// H02: Training type compatibility
		if ( ! $hard_fail && ! empty( $input['training_type'] ) ) {
			$user_edu = $input['education'];
			$allowed_types = $compatibility[ $user_edu ] ?? [];
			if ( ! in_array( $input['training_type'], $allowed_types, true ) ) {
				// Check if program explicitly accepts this type
				$prog_types = get_field( 'elig_training_types', $program_id );
				$prog_types = is_array( $prog_types ) ? $prog_types : [];
				$program_taxes = wp_get_post_terms( $program_id, 'training_type', [ 'fields' => 'slugs' ] );
				$all_types = array_unique( array_merge( $prog_types, $program_taxes ) );

				if ( ! in_array( $input['training_type'], $all_types, true ) ) {
					$hard_fail = true;
					$fail_reason = 'Hệ ' . ltdh_elig_get_training_label( $input['training_type'] ) . ' không áp dụng cho trình độ này.';
				}
			}
		}

		// H03: Campus availability
		if ( ! $hard_fail && ! empty( $input['campus'] ) && $input['campus'] !== 'online' ) {
			$all_campuses = wp_get_post_terms( $program_id, 'campus', [ 'fields' => 'slugs' ] );

			if ( ! in_array( $input['campus'], $all_campuses, true ) ) {
				// Check if online is available (online works everywhere)
				if ( ! in_array( 'online', $all_campuses, true ) ) {
					$hard_fail = true;
					$fail_reason = 'Chương trình chưa có tại ' . ltdh_elig_get_campus_label( $input['campus'] );
				}
			}
		}

		// H05: University + Liên thông block
		if ( ! $hard_fail && $input['education'] === 'dai-hoc' && $input['training_type'] === 'lien-thong' ) {
			$hard_fail = true;
			$fail_reason = 'Người đã có bằng Đại học không áp dụng hệ Liên thông. Hãy xem hệ Văn bằng 2.';
		}

		// H05: Thạc sĩ + Liên thông block
		if ( ! $hard_fail && $input['education'] === 'thac-si' && $input['training_type'] === 'lien-thong' ) {
			$hard_fail = true;
			$fail_reason = 'Người đã có bằng Thạc sĩ không áp dụng hệ Liên thông.';
		}

		if ( $hard_fail ) {
			$rejected[] = [
				'program_id' => $program_id,
				'reason'     => $fail_reason,
			];
			continue;
		}

		// --- SOFT SCORING ---
		$score = 0;
		$breakdown = [];

		// S01: Major match
		$prog_major_id = get_field( 'major_relationship', $program_id );
		if ( is_array( $prog_major_id ) ) {
			$prog_major_id = ! empty( $prog_major_id ) ? $prog_major_id[0] : 0;
		}
		if ( is_object( $prog_major_id ) ) {
			$prog_major_id = $prog_major_id->ID;
		}
		$prog_major_id = intval( $prog_major_id );
		$prog_major_slug = $prog_major_id ? get_post_field( 'post_name', $prog_major_id ) : '';

		if ( $input['desired_major'] && $prog_major_id ) {
			if ( (int) $input['desired_major'] === $prog_major_id ) {
				$score += $weights['major_match'];
				$breakdown['major'] = [ 'score' => $weights['major_match'], 'label' => 'Ngành trùng khớp' ];
			} else {
				$related_ids = get_field( 'major_related', $input['desired_major'] );
				if ( ! is_array( $related_ids ) ) {
					$related_ids = $related_ids ? [ $related_ids ] : [];
				}
				if ( $input['major_id'] ) {
					$current_related = get_field( 'major_related', $input['major_id'] );
					if ( is_array( $current_related ) ) {
						$related_ids = array_merge( $related_ids, $current_related );
					} elseif ( $current_related ) {
						$related_ids[] = $current_related;
					}
				}
				$related_ids = array_map( 'intval', $related_ids );

				if ( in_array( $prog_major_id, $related_ids, true ) ) {
					$score += $weights['major_related'];
					$breakdown['major'] = [ 'score' => $weights['major_related'], 'label' => 'Ngành liên quan' ];
				} elseif ( isset( $major_rels[ $user_major_slug ] ) && in_array( $prog_major_slug, $major_rels[ $user_major_slug ], true ) ) {
					$score += $weights['major_related'];
					$breakdown['major'] = [ 'score' => $weights['major_related'], 'label' => 'Ngành liên quan' ];
				} else {
					$breakdown['major'] = [ 'score' => 0, 'label' => 'Ngành khác' ];
				}
			}
		}

		// S02: Graduation recency (estimated from Birth Year + 18)
		if ( $input['graduation'] ) {
			$estimated_graduation = $input['graduation'] + 18;
			$years_since = date( 'Y' ) - $estimated_graduation;
			if ( $years_since <= 3 ) {
				$pts = $weights['graduation_recent'];
				$breakdown['graduation'] = [ 'score' => $pts, 'label' => 'Tốt nghiệp gần đây' ];
			} elseif ( $years_since <= 5 ) {
				$pts = (int) ( $weights['graduation_recent'] * 0.7 );
				$breakdown['graduation'] = [ 'score' => $pts, 'label' => 'Tốt nghiệp 3-5 năm' ];
			} elseif ( $years_since <= 10 ) {
				$pts = (int) ( $weights['graduation_recent'] * 0.5 );
				$breakdown['graduation'] = [ 'score' => $pts, 'label' => 'Tốt nghiệp 5-10 năm' ];
			} else {
				$pts = (int) ( $weights['graduation_recent'] * 0.2 );
				$breakdown['graduation'] = [ 'score' => $pts, 'label' => 'Tốt nghiệp > 10 năm' ];
			}
			$score += $pts;
		}

		// S03: Budget fit
		if ( $input['budget'] && isset( $budget_ranges[ $input['budget'] ] ) ) {
			$budget = $budget_ranges[ $input['budget'] ];
			$tuition_str = get_post_meta( $program_id, 'tuition_fee', true ) ?: '';
			$tuition_num = ltdh_elig_parse_tuition( $tuition_str );
			$duration_num = ltdh_elig_parse_duration( get_post_meta( $program_id, 'duration', true ) ?: '' );
			$total_cost = $tuition_num * 120 * $duration_num; // rough estimate: 120 credits

			if ( $total_cost > 0 && $budget['max'] < PHP_INT_MAX ) {
				if ( $total_cost <= $budget['max'] ) {
					$score += $weights['budget_match'];
					$breakdown['budget'] = [ 'score' => $weights['budget_match'], 'label' => 'Phù hợp ngân sách' ];
				} elseif ( $total_cost <= $budget['max'] * 1.2 ) {
					$pts = (int) ( $weights['budget_match'] * 0.5 );
					$score += $pts;
					$breakdown['budget'] = [ 'score' => $pts, 'label' => 'Vượt ngân sách nhẹ' ];
				} else {
					$breakdown['budget'] = [ 'score' => 0, 'label' => 'Vượt ngân sách' ];
				}
			} elseif ( $total_cost > 0 && $budget['max'] === PHP_INT_MAX ) {
				$score += $weights['budget_match'];
				$breakdown['budget'] = [ 'score' => $weights['budget_match'], 'label' => 'Phù hợp ngân sách' ];
			}
		}

		// S04: Campus match
		if ( $input['campus'] ) {
			$all_campuses2 = wp_get_post_terms( $program_id, 'campus', [ 'fields' => 'slugs' ] );

			if ( in_array( $input['campus'], $all_campuses2, true ) ) {
				$score += $weights['campus_match'];
				$breakdown['campus'] = [ 'score' => $weights['campus_match'], 'label' => 'Cơ sở phù hợp' ];
			} elseif ( in_array( 'online', $all_campuses2, true ) ) {
				$pts = (int) ( $weights['campus_match'] * 0.5 );
				$score += $pts;
				$breakdown['campus'] = [ 'score' => $pts, 'label' => 'Có Online' ];
			}
		}

		// S05: Schedule match
		$schedule = get_post_meta( $program_id, 'schedule', true ) ?: '';
		if ( $schedule ) {
			$score += $weights['schedule_match'];
			$breakdown['schedule'] = [ 'score' => $weights['schedule_match'], 'label' => 'Có lịch học' ];
		}

		// Cap score at 100
		$score = min( $score, 100 );

		// Get school data
		$school_id = get_field( 'school_relationship', $program_id );
		if ( is_array( $school_id ) ) {
			$school_id = ! empty( $school_id ) ? $school_id[0] : 0;
		}
		if ( is_object( $school_id ) ) {
			$school_id = $school_id->ID;
		}
		$school_id = intval( $school_id );
		$school_logo_id = $school_id ? ltdh_get_school_image_id( $school_id ) : 0;

		$eligible[] = [
			'program_id'   => $program_id,
			'title'        => get_the_title( $program_id ),
			'permalink'    => get_permalink( $program_id ),
			'thumbnail'    => get_the_post_thumbnail_url( $program_id, 'medium' ) ?: get_stylesheet_directory_uri() . '/assets/images/banner-default.jpg',
			'score'        => $score,
			'breakdown'    => $breakdown,
			'school'       => $school_id ? [
				'id'     => $school_id,
				'title'  => get_the_title( $school_id ),
				'logo'   => $school_logo_id ? wp_get_attachment_image_url( $school_logo_id, 'thumbnail' ) : '',
			] : null,
			'major'        => $prog_major_id ? get_the_title( $prog_major_id ) : '',
			'training_type'=> wp_get_post_terms( $program_id, 'training_type', [ 'fields' => 'names' ] )[0] ?? '',
			'tuition_fee'  => get_post_meta( $program_id, 'tuition_fee', true ) ?: '',
			'duration'     => get_post_meta( $program_id, 'duration', true ) ?: '',
			'schedule'     => $schedule,
			'campus_info'  => implode( ', ', wp_get_post_terms( $program_id, 'campus', [ 'fields' => 'names' ] ) ) ?: '—',
		];
	}

	// Sort by score descending
	usort( $eligible, function( $a, $b ) {
		return $b['score'] <=> $a['score'];
	});

	// Limit to top 10
	$eligible = array_slice( $eligible, 0, 10 );

	// Find alternatives (rejected programs with reasons)
	$alternatives = [];
	$shown_reasons = [];
	foreach ( $rejected as $r ) {
		$reason = $r['reason'];
		if ( ! in_array( $reason, $shown_reasons, true ) ) {
			$shown_reasons[] = $reason;
			$alternatives[] = [
				'program_id' => $r['program_id'],
				'title'      => get_the_title( $r['program_id'] ),
				'reason'     => $reason,
			];
		}
	}

	return [
		'eligible'         => ! empty( $eligible ),
		'programs'         => $eligible,
		'total_candidates' => $total_candidates,
		'eligible_count'   => count( $eligible ),
		'top_score'        => $eligible[0]['score'] ?? 0,
		'alternatives'     => array_slice( $alternatives, 0, 5 ),
	];
}

// ----------------------------------------------------
// 7. Helpers
// ----------------------------------------------------

function ltdh_elig_parse_tuition( $str ) {
	$str = str_replace( [ '.', ',', 'đ', '₫', '/tín chỉ', '/hoc ky', '/học kỳ' ], '', $str );
	$str = trim( $str );
	return intval( $str );
}

function ltdh_elig_parse_duration( $str ) {
	preg_match( '/[\d.]+/', $str, $m );
	return $m ? floatval( $m[0] ) : 1;
}

function ltdh_elig_get_education_label( $slug ) {
	$map = [
		'thap-phan'  => 'THPT',
		'trung-cap'  => 'Trung cấp',
		'cao-dang'   => 'Cao đẳng',
		'dai-hoc'    => 'Đại học',
		'thac-si'    => 'Thạc sĩ',
	];
	return $map[ $slug ] ?? $slug;
}

function ltdh_elig_get_training_label( $slug ) {
	$map = [
		'lien-thong'       => 'Liên thông',
		'van-bang-2'       => 'Văn bằng 2',
		'tu-xa'            => 'Từ xa',
		'vua-hoc-vua-lam'  => 'Vừa học vừa làm',
		'chinh-quy'        => 'Chính quy',
	];
	return $map[ $slug ] ?? $slug;
}

function ltdh_elig_get_campus_label( $slug ) {
	$map = [
		'ha-noi'       => 'Hà Nội',
		'ho-chi-minh'  => 'TP. Hồ Chí Minh',
		'da-nang'      => 'Đà Nẵng',
		'thai-nguyen'  => 'Thái Nguyên',
		'online'       => 'Online',
	];
	return $map[ $slug ] ?? $slug;
}

function ltdh_elig_get_budget_label( $slug ) {
	$map = [
		'duoi-20-trieu' => 'Dưới 20 triệu',
		'20-30-trieu'   => '20 - 30 triệu',
		'30-50-trieu'   => '30 - 50 triệu',
		'tren-50-trieu' => 'Trên 50 triệu',
	];
	return $map[ $slug ] ?? $slug;
}

// ----------------------------------------------------
// 8. Database Storage
// ----------------------------------------------------

function ltdh_elig_store_check( $input, $results ) {
	global $wpdb;
	$table = $wpdb->prefix . 'ltdh_eligibility_checks';

	$token = wp_generate_password( 32, false );

	$wpdb->insert( $table, [
		'session_token'    => $token,
		'input_education'  => $input['education'],
		'input_major_id'   => $input['major_id'],
		'input_graduation' => $input['graduation'] ?: null,
		'input_desired_major' => $input['desired_major'],
		'input_training_type' => $input['training_type'],
		'input_campus'     => $input['campus'],
		'input_budget'     => $input['budget'],
		'total_candidates' => $results['total_candidates'],
		'eligible_count'   => $results['eligible_count'],
		'top_score'        => $results['top_score'],
		'top_program_id'   => $results['programs'][0]['program_id'] ?? 0,
		'phone'            => $input['phone'],
		'email'            => $input['email'],
		'created_at'       => current_time( 'mysql' ),
		'referrer_url'     => wp_get_referer() ?: '',
	], [ '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s' ] );

	return $wpdb->insert_id;
}

// ----------------------------------------------------
// 9. Lead Capture Integration
// ----------------------------------------------------

function ltdh_elig_capture_lead( $input, $results, $check_id ) {
	if ( empty( $input['phone'] ) && empty( $input['email'] ) ) {
		return 0;
	}

	$top_program = $results['programs'][0] ?? null;
	$program_id  = $top_program['program_id'] ?? 0;
	$school_id   = $top_program['school']['id'] ?? 0;
	$major_id    = $input['desired_major'] ?: $input['major_id'];

	// Use existing lead capture system
	if ( function_exists( 'ltdh_insert_lead' ) ) {
		$lead_id = ltdh_insert_lead( [
			'name'            => 'Eligibility Checker User',
			'phone'           => $input['phone'],
			'email'           => $input['email'],
			'program_id'      => $program_id,
			'school_id'       => $school_id,
			'major_id'        => $major_id,
			'training_type'   => $input['training_type'],
			'campus'          => $input['campus'],
			'referral_source' => 'eligibility_checker',
		] );

		if ( $lead_id ) {
			global $wpdb;
			$wpdb->update(
				$wpdb->prefix . 'ltdh_eligibility_checks',
				[ 'lead_captured' => 1, 'lead_id' => $lead_id ],
				[ 'id' => $check_id ]
			);
		}

		return $lead_id;
	}

	// Fallback: direct DB insert
	global $wpdb;
	$wpdb->insert( $wpdb->prefix . 'ltdh_leads', [
		'name'          => 'Eligibility Checker User',
		'phone'         => $input['phone'],
		'email'         => $input['email'],
		'program_id'    => $program_id,
		'school_id'     => $school_id,
		'major_id'      => $major_id,
		'training_type' => $input['training_type'],
		'campus'        => $input['campus'],
		'referral_source' => 'eligibility_checker',
		'created_at'    => current_time( 'mysql' ),
	] );

	return $wpdb->insert_id;
}

// ----------------------------------------------------
// 10. AJAX Handler — Lead Capture
// ----------------------------------------------------

function ltdh_elig_ajax_lead() {
	check_ajax_referer( 'ltdh_elig_nonce', 'nonce' );

	$name  = sanitize_text_field( $_POST['cf_name'] ?? '' );
	$phone = sanitize_text_field( $_POST['cf_phone'] ?? '' );
	$email = sanitize_email( $_POST['cf_email'] ?? '' );
	$check_id = intval( $_POST['elig_check_id'] ?? 0 );
	$program_id = intval( $_POST['elig_program_id'] ?? 0 );

	if ( empty( $name ) || empty( $phone ) ) {
		wp_send_json_error( [ 'message' => 'Vui lòng nhập họ tên và số điện thoại.' ] );
	}

	$school_id = $program_id ? intval( get_post_meta( $program_id, 'school_relationship', true ) ) : 0;
	$major_id  = $program_id ? intval( get_post_meta( $program_id, 'major_relationship', true ) ) : 0;

	if ( function_exists( 'ltdh_insert_lead' ) ) {
		$lead_id = ltdh_insert_lead( [
			'name'            => $name,
			'phone'           => $phone,
			'email'           => $email,
			'program_id'      => $program_id,
			'school_id'       => $school_id,
			'major_id'        => $major_id,
			'training_type'   => '',
			'campus'          => '',
			'referral_source' => 'eligibility_checker_form',
		] );
	} else {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'ltdh_leads', [
			'name'            => $name,
			'phone'           => $phone,
			'email'           => $email,
			'program_id'      => $program_id,
			'school_id'       => $school_id,
			'major_id'        => $major_id,
			'referral_source' => 'eligibility_checker_form',
			'created_at'      => current_time( 'mysql' ),
		] );
		$lead_id = $wpdb->insert_id;
	}

	if ( $lead_id && $check_id ) {
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'ltdh_eligibility_checks',
			[ 'lead_captured' => 1, 'lead_id' => $lead_id ],
			[ 'id' => $check_id ]
		);
	}

	wp_send_json_success( [ 'lead_id' => $lead_id ] );
}

// ----------------------------------------------------
// 11. REST API Endpoints
// ----------------------------------------------------

add_action( 'rest_api_init', function() {
	register_rest_route( 'ltdh/v1', '/eligibility/check', [
		'methods'  => 'POST',
		'callback' => 'ltdh_elig_rest_check',
		'permission_callback' => '__return_true',
	] );
} );

function ltdh_elig_rest_check( $request ) {
	$input = [
		'education'     => sanitize_text_field( $request->get_param( 'education' ) ?? '' ),
		'major_id'      => intval( $request->get_param( 'major_id' ) ?? 0 ),
		'graduation'    => intval( $request->get_param( 'graduation' ) ?? 0 ),
		'desired_major' => intval( $request->get_param( 'desired_major' ) ?? 0 ),
		'training_type' => sanitize_text_field( $request->get_param( 'training_type' ) ?? '' ),
		'campus'        => sanitize_text_field( $request->get_param( 'campus' ) ?? '' ),
		'budget'        => sanitize_text_field( $request->get_param( 'budget' ) ?? '' ),
		'phone'         => sanitize_text_field( $request->get_param( 'phone' ) ?? '' ),
		'email'         => sanitize_email( $request->get_param( 'email' ) ?? '' ),
	];

	$validation = ltdh_elig_validate_input( $input );
	if ( is_wp_error( $validation ) ) {
		return new WP_REST_Response( [ 'error' => $validation->get_error_message() ], 400 );
	}

	$results = ltdh_elig_run_check( $input );
	$check_id = ltdh_elig_store_check( $input, $results );

	return rest_ensure_response( [
		'check_id'       => $check_id,
		'input'          => $input,
		'eligible'       => $results['eligible'],
		'programs'       => $results['programs'],
		'total'          => $results['total_candidates'],
		'eligible_count' => $results['eligible_count'],
		'top_score'      => $results['top_score'],
		'alternatives'   => $results['alternatives'],
	] );
}

// ----------------------------------------------------
// 11. Admin Columns (optional — for debug)
// ----------------------------------------------------

add_filter( 'manage_edit-program_columns', function( $columns ) {
	$columns['elig_status'] = 'Eligibility';
	return $columns;
} );

add_action( 'manage_program_posts_custom_column', function( $column, $post_id ) {
	if ( $column === 'elig_status' ) {
		$min_edu = get_field( 'elig_min_education', $post_id );
		echo $min_edu ? esc_html( ltdh_elig_get_education_label( $min_edu ) . '+' ) : '<span style="color:#999">—</span>';
	}
}, 10, 2 );
