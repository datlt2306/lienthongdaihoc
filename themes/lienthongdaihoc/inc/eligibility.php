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
	$table   = $wpdb->prefix . LTDH_TABLE_ELIGIBILITY;
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		session_token varchar(64) NOT NULL,
		input_education varchar(50) NOT NULL DEFAULT '',
		input_major_id bigint(20) UNSIGNED DEFAULT 0,
		input_previous_school varchar(255) NOT NULL DEFAULT '',
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
add_action( 'after_switch_theme', 'ltdh_elig_ensure_page' );

// Run on module load as well (safe — dbDelta is idempotent)
add_action( 'init', function() {
	if ( get_option( 'ltdh_elig_table_version' ) !== '1.1' ) {
		ltdh_elig_create_table();
		update_option( 'ltdh_elig_table_version', '1.1' );
	}
	if ( get_option( 'ltdh_elig_page_created' ) !== '1' ) {
		ltdh_elig_ensure_page();
	}
});

/**
 * Auto-create the eligibility checker page if missing.
 */
function ltdh_elig_ensure_page() {
	$slug = 'kiem-tra-dieu-kien';
	$page = get_page_by_path( $slug );
	if ( $page ) {
		update_option( 'ltdh_elig_page_created', '1' );
		return;
	}

	$post_id = wp_insert_post( [
		'post_title'  => 'Kiểm tra điều kiện',
		'post_name'   => $slug,
		'post_status' => 'publish',
		'post_type'   => 'page',
	] );

	if ( ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_wp_page_template', 'page-eligible.php' );
		update_option( 'ltdh_elig_page_created', '1' );
		flush_rewrite_rules();
	}
}

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
		$theme_dir = get_template_directory();
		$css_path  = $theme_dir . '/assets/css/eligibility.css';
		$js_path   = $theme_dir . '/assets/js/eligibility.js';
		
		$css_ver = file_exists( $css_path ) ? filemtime( $css_path ) : LTDH_VERSION;
		$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : LTDH_VERSION;

		wp_enqueue_style(
			'ltdh-eligibility-css',
			get_template_directory_uri() . '/assets/css/eligibility.css',
			[],
			$css_ver
		);

		wp_enqueue_script(
			'ltdh-eligibility-js',
			get_template_directory_uri() . '/assets/js/eligibility.js',
			[],
			$js_ver,
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

	// Honeypot check
	if ( ! empty( $_POST['website_confirm'] ) ) {
		wp_send_json_error( [ 'message' => 'Spam detected.' ], 400 );
	}

	// Rate limiting check
	if ( ltdh_elig_is_rate_limited() ) {
		wp_send_json_error( [ 'message' => 'Yêu cầu quá nhanh. Vui lòng thử lại sau.' ], 429 );
	}

	// Process file upload if any
	$degree_file_url = '';
	if ( ! empty( $_FILES['degree_file'] ) && ! empty( $_FILES['degree_file']['name'] ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		$uploadedfile = $_FILES['degree_file'];
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

		if ( $movefile && ! isset( $movefile['error'] ) ) {
			$degree_file_url = $movefile['url'];
		}
	}

	$degree_link = sanitize_text_field( $_POST['degree_link'] ?? '' );
	if ( ! empty( $degree_file_url ) ) {
		$degree_link = $degree_file_url;
	}

	$input = [
		'name'            => sanitize_text_field( $_POST['name'] ?? '' ),
		'education'       => sanitize_text_field( $_POST['education'] ?? '' ),
		'major_id'        => intval( $_POST['major_id'] ?? 0 ),
		'previous_school' => sanitize_text_field( $_POST['previous_school'] ?? '' ),
		'graduation'      => intval( $_POST['graduation'] ?? 0 ),
		'desired_major'   => intval( $_POST['desired_major'] ?? 0 ),
		'training_type'   => sanitize_text_field( $_POST['training_type'] ?? '' ),
		'campus'          => sanitize_text_field( $_POST['campus'] ?? '' ),
		'budget'          => sanitize_text_field( $_POST['budget'] ?? '' ),
		'phone'           => sanitize_text_field( $_POST['phone'] ?? '' ),
		'email'           => sanitize_email( $_POST['email'] ?? '' ),
		'degree_link'     => $degree_link,
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

	// Step 1: Load candidate programs using safe pre-filters
	$query_args = [
		'post_type'      => LTDH_CPT_PROGRAM,
		'post_status'    => 'publish',
		'posts_per_page' => 100, // Limit to prevent memory exhaustion
		'fields'         => 'ids', // Get IDs first for optimization
		'meta_query'     => [
			[
				'key'     => LTDH_META_ADMISSION_STATUS,
				'value'   => LTDH_STATUS_OPEN,
				'compare' => '=',
			],
		],
	];

	// Pre-filter by training type if specified (Safe Filter)
	if ( ! empty( $input['training_type'] ) ) {
		$query_args['tax_query'] = [
			[
				'taxonomy' => 'training_type',
				'field'    => 'slug',
				'terms'    => $input['training_type'],
			],
		];
	}

	// Pre-filter by campus (Safe Filter - matches selected campus exactly)
	if ( ! empty( $input['campus'] ) ) {
		if ( ! isset( $query_args['tax_query'] ) ) {
			$query_args['tax_query'] = [ 'relation' => 'AND' ];
		}
		$query_args['tax_query'][] = [
			'taxonomy' => 'campus',
			'field'    => 'slug',
			'terms'    => $input['campus'],
		];
	}

	$candidate_ids = get_posts( $query_args );
	$total_candidates = count( $candidate_ids );

	if ( ! empty( $candidate_ids ) ) {
		update_meta_cache( 'post', $candidate_ids );
		update_object_term_cache( $candidate_ids, 'program' );
	}

	// Step 2: Apply safe compatibility filters, score, and evaluate status
	$eligible = [];
	$rejected = [];

	foreach ( $candidate_ids as $program_id ) {
		$preliminary_status = 'compatible';
		$match_reasons = [];
		$verification_items = [];
		$mismatch_reasons = [];
		$hard_fail = false;
		$match_score = 0;

		// 1. Safe Filter: Education level minimum
		$min_edu = get_field( 'elig_min_education', $program_id ) ?: '';
		if ( $min_edu ) {
			$user_level = $hierarchy[ $input['education'] ] ?? 0;
			$min_level  = $hierarchy[ $min_edu ] ?? 0;
			if ( $user_level < $min_level ) {
				$preliminary_status = 'not_compatible';
				$mismatch_reasons[] = 'Trình độ học vấn của bạn chưa đáp ứng yêu cầu tối thiểu của chương trình (Yêu cầu: ' . ltdh_elig_get_education_label( $min_edu ) . ').';
				$hard_fail = true;
			} else {
				$match_reasons[] = 'Đáp ứng yêu cầu học vấn tối thiểu (' . ltdh_elig_get_education_label( $min_edu ) . ').';
			}
		} else {
			// Unknown minimum, needs verification
			$preliminary_status = 'needs_verification';
			$verification_items[] = 'Chương trình chưa cấu hình điều kiện học vấn tối thiểu chính thức (Cần xác minh thêm).';
		}

		// 2. Desired Major Match
		$prog_major_id = get_field( 'major_relationship', $program_id );
		if ( is_array( $prog_major_id ) ) {
			$prog_major_id = ! empty( $prog_major_id ) ? $prog_major_id[0] : 0;
		}
		if ( is_object( $prog_major_id ) ) {
			$prog_major_id = $prog_major_id->ID;
		}
		$prog_major_id = intval( $prog_major_id );

		if ( ! empty( $input['desired_major'] ) && $prog_major_id ) {
			if ( (int) $input['desired_major'] === $prog_major_id ) {
				$match_score += $weights['major_match'];
				$match_reasons[] = 'Đúng ngành bạn mong muốn học.';
			} else {
				$preliminary_status = 'not_compatible';
				$mismatch_reasons[] = 'Không khớp ngành đào tạo mong muốn.';
				$hard_fail = true;
			}
		}

		// 3. User Current Major vs Desired Program Major
		if ( ! $hard_fail ) {
			if ( empty( $input['major_id'] ) ) {
				if ( $preliminary_status !== 'not_compatible' ) {
					$preliminary_status = 'needs_verification';
				}
				$verification_items[] = 'Chưa có thông tin chuyên ngành hiện tại/đã tốt nghiệp (Cần xác minh tính hợp lệ của văn bằng).';
			} else {
				if ( $prog_major_id && (int) $input['major_id'] === $prog_major_id ) {
					$match_score += $weights['major_related'];
					$match_reasons[] = 'Chuyên ngành muốn học trùng khớp với ngành bạn đã tốt nghiệp.';
				} else {
					if ( $preliminary_status !== 'not_compatible' ) {
						$preliminary_status = 'needs_verification';
					}
					$verification_items[] = 'Ngành bạn muốn học khác với ngành đã tốt nghiệp (Cần kiểm tra quy chế tiếp nhận ngành chéo của trường).';
				}
			}
		}

		// 4. Training Type Compatibility (Safe checking)
		if ( ! $hard_fail && ! empty( $input['training_type'] ) ) {
			$user_edu = $input['education'];
			$allowed_types = $compatibility[ $user_edu ] ?? [];
			if ( ! in_array( $input['training_type'], $allowed_types, true ) ) {
				if ( $preliminary_status !== 'not_compatible' ) {
					$preliminary_status = 'needs_verification';
				}
				$verification_items[] = 'Hệ đào tạo ' . ltdh_elig_get_training_label( $input['training_type'] ) . ' cần được nhà trường xác nhận với trình độ hiện tại.';
			} else {
				$match_score += $weights['schedule_match'];
				$match_reasons[] = 'Hỗ trợ hệ đào tạo ' . ltdh_elig_get_training_label( $input['training_type'] ) . ' phù hợp.';
			}
		}

		// 5. Campus Availability
		if ( ! $hard_fail && ! empty( $input['campus'] ) ) {
			$all_campuses = wp_get_post_terms( $program_id, 'campus', [ 'fields' => 'slugs' ] );
			if ( in_array( $input['campus'], $all_campuses, true ) ) {
				$match_score += $weights['campus_match'];
				$match_reasons[] = 'Có địa điểm học trực tiếp tại ' . ltdh_elig_get_campus_label( $input['campus'] ) . '.';
			} elseif ( in_array( 'online', $all_campuses, true ) ) {
				$match_score += (int) ( $weights['campus_match'] * 0.5 );
				$match_reasons[] = 'Hỗ trợ học từ xa / Online.';
			} else {
				$preliminary_status = 'not_compatible';
				$mismatch_reasons[] = 'Không có cơ sở đào tạo tại ' . ltdh_elig_get_campus_label( $input['campus'] ) . '.';
				$hard_fail = true;
			}
		}

		// 6. Budget
		if ( ! $hard_fail && ! empty( $input['budget'] ) && isset( $budget_ranges[ $input['budget'] ] ) ) {
			$budget = $budget_ranges[ $input['budget'] ];
			$tuition_str = get_post_meta( $program_id, 'tuition_fee', true ) ?: '';
			$tuition_num = ltdh_elig_parse_tuition( $tuition_str );
			$duration_num = ltdh_elig_parse_duration( get_post_meta( $program_id, 'duration', true ) ?: '' );
			$total_cost = $tuition_num * 120 * $duration_num;

			if ( $total_cost > 0 && $budget['max'] < PHP_INT_MAX ) {
				if ( $total_cost <= $budget['max'] ) {
					$match_score += $weights['budget_match'];
					$match_reasons[] = 'Học phí phù hợp với ngân sách dự kiến của bạn.';
				} elseif ( $total_cost <= $budget['max'] * 1.2 ) {
					$match_score += (int) ( $weights['budget_match'] * 0.5 );
					$match_reasons[] = 'Học phí vượt nhẹ so với ngân sách của bạn.';
				} else {
					if ( $preliminary_status !== 'not_compatible' ) {
						$preliminary_status = 'needs_verification';
					}
					$verification_items[] = 'Mức học phí ước tính cao hơn ngân sách dự kiến của bạn.';
				}
			} else {
				$match_score += $weights['budget_match'];
				$match_reasons[] = 'Chi phí phù hợp.';
			}
		}

		$match_score = min( $match_score, 100 );

		// Get school information
		$school_id = get_field( 'school_relationship', $program_id );
		if ( is_array( $school_id ) ) {
			$school_id = ! empty( $school_id ) ? $school_id[0] : 0;
		}
		if ( is_object( $school_id ) ) {
			$school_id = $school_id->ID;
		}
		$school_id = intval( $school_id );
		$school_logo_id = $school_id ? ltdh_get_school_image_id( $school_id ) : 0;

		$program_data = [
			'program_id'         => $program_id,
			'title'              => get_the_title( $program_id ),
			'permalink'          => get_permalink( $program_id ),
			'thumbnail'          => get_the_post_thumbnail_url( $program_id, 'medium' ) ?: get_stylesheet_directory_uri() . '/assets/images/banner-default.jpg',
			'score'              => $match_score,
			'preliminary_status' => $preliminary_status,
			'match_reasons'      => $match_reasons,
			'verification_items' => $verification_items,
			'mismatch_reasons'   => $mismatch_reasons,
			'school'             => $school_id ? [
				'id'     => $school_id,
				'title'  => get_the_title( $school_id ),
				'logo'   => $school_logo_id ? wp_get_attachment_image_url( $school_logo_id, 'thumbnail' ) : '',
			] : null,
			'major'              => $prog_major_id ? get_the_title( $prog_major_id ) : '',
			'training_type'      => wp_get_post_terms( $program_id, 'training_type', [ 'fields' => 'names' ] )[0] ?? '',
			'tuition_fee'        => get_post_meta( $program_id, 'tuition_fee', true ) ?: '',
			'duration'           => get_post_meta( $program_id, 'duration', true ) ?: '',
			'schedule'           => get_post_meta( $program_id, 'schedule', true ) ?: '',
			'campus_info'        => implode( ', ', wp_get_post_terms( $program_id, 'campus', [ 'fields' => 'names' ] ) ) ?: '—',
		];

		if ( $preliminary_status === 'not_compatible' || $hard_fail ) {
			$rejected[] = $program_data;
		} else {
			$eligible[] = $program_data;
		}
	}

	// Sort eligible programs by match score descending
	usort( $eligible, function( $a, $b ) {
		return $b['score'] <=> $a['score'];
	});

	$eligible_slice = array_slice( $eligible, 0, 10 );

	// Build alternatives (programs that are not fully compatible but might interest the user)
	$alternatives = [];
	foreach ( $rejected as $r ) {
		if ( count( $alternatives ) >= 5 ) {
			break;
		}
		$alternatives[] = [
			'program_id' => $r['program_id'],
			'title'      => $r['title'],
			'reason'     => ! empty( $r['mismatch_reasons'] ) ? $r['mismatch_reasons'][0] : 'Chưa phù hợp điều kiện',
		];
	}

	return [
		'eligible'         => ! empty( $eligible_slice ),
		'programs'         => $eligible_slice,
		'total_candidates' => $total_candidates,
		'eligible_count'   => count( $eligible ),
		'top_score'        => $eligible_slice[0]['score'] ?? 0,
		'alternatives'     => $alternatives,
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
		'session_token'         => $token,
		'input_education'       => $input['education'],
		'input_major_id'        => $input['major_id'],
		'input_previous_school' => $input['previous_school'],
		'input_graduation'      => $input['graduation'] ?: null,
		'input_desired_major'   => $input['desired_major'],
		'input_training_type'   => $input['training_type'],
		'input_campus'          => $input['campus'],
		'input_budget'          => $input['budget'],
		'total_candidates'      => $results['total_candidates'],
		'eligible_count'        => $results['eligible_count'],
		'top_score'             => $results['top_score'],
		'top_program_id'        => $results['programs'][0]['program_id'] ?? 0,
		'phone'                 => $input['phone'],
		'email'                 => $input['email'],
		'created_at'            => current_time( 'mysql' ),
		'referrer_url'          => wp_get_referer() ?: '',
	], [ '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s' ] );

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

	$is_verification_required = ($top_program && $top_program['preliminary_status'] === 'needs_verification') ? '1' : '0';
	$ref_source = 'eligibility_checker?education_level=' . urlencode($input['education']) . 
		'&current_major=' . urlencode($input['major_id']) . 
		'&previous_school=' . urlencode($input['previous_school']) .
		'&desired_major=' . urlencode($input['desired_major']) . 
		'&birth_year=' . urlencode($input['graduation']) . 
		'&verification_required=' . $is_verification_required;

	if ( ! empty( $input['degree_link'] ) ) {
		$ref_source .= '&degree_link=' . urlencode( $input['degree_link'] );
	}

	// Use existing lead capture system
	if ( function_exists( 'ltdh_insert_lead' ) ) {
		$lead_id = ltdh_insert_lead( [
			'name'            => ! empty( $input['name'] ) ? $input['name'] : 'Eligibility Checker User',
			'phone'           => $input['phone'],
			'email'           => $input['email'],
			'program_id'      => $program_id,
			'school_id'       => $school_id,
			'major_id'        => $major_id,
			'training_type'   => $input['training_type'],
			'campus'          => $input['campus'],
			'referral_source' => $ref_source,
			'message'         => ! empty( $input['degree_link'] ) ? 'Ảnh bằng cấp đính kèm: ' . $input['degree_link'] : '',
		] );

		if ( $lead_id ) {
			global $wpdb;
			$wpdb->update(
				$wpdb->prefix . LTDH_TABLE_ELIGIBILITY,
				[ 'lead_captured' => 1, 'lead_id' => $lead_id ],
				[ 'id' => $check_id ]
			);
		}

		return $lead_id;
	}

	// Fallback: direct DB insert
	global $wpdb;
	$wpdb->insert( $wpdb->prefix . LTDH_TABLE_LEADS, [
		'name'          => ! empty( $input['name'] ) ? $input['name'] : 'Eligibility Checker User',
		'phone'         => $input['phone'],
		'email'         => $input['email'],
		'program_id'    => $program_id,
		'school_id'     => $school_id,
		'major_id'      => $major_id,
		'training_type' => $input['training_type'],
		'campus'        => $input['campus'],
		'referral_source' => $ref_source,
		'error_message' => ! empty( $input['degree_link'] ) ? 'Ảnh bằng cấp đính kèm: ' . $input['degree_link'] : '',
		'created_at'    => current_time( 'mysql' ),
	] );

	return $wpdb->insert_id;
}

// ----------------------------------------------------
// 10. AJAX Handler — Lead Capture
// ----------------------------------------------------

function ltdh_elig_ajax_lead() {
	check_ajax_referer( 'ltdh_elig_nonce', 'nonce' );

	// Honeypot check
	if ( ! empty( $_POST['website_confirm'] ) ) {
		wp_send_json_error( [ 'message' => 'Spam detected.' ], 400 );
	}

	// Rate limiting check
	if ( ltdh_elig_is_rate_limited() ) {
		wp_send_json_error( [ 'message' => 'Yêu cầu quá nhanh. Vui lòng thử lại sau.' ], 429 );
	}

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
		$wpdb->insert( $wpdb->prefix . LTDH_TABLE_LEADS, [
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

add_action( 'wp_ajax_ltdh_elig_advanced_verify', 'ltdh_elig_ajax_advanced_verify' );
add_action( 'wp_ajax_nopriv_ltdh_elig_advanced_verify', 'ltdh_elig_ajax_advanced_verify' );

function ltdh_elig_ajax_advanced_verify() {
	check_ajax_referer( 'ltdh_elig_nonce', 'nonce' );

	$lead_id = intval( $_POST['lead_id'] ?? 0 );
	if ( ! $lead_id ) {
		wp_send_json_error( [ 'message' => 'Yêu cầu không hợp lệ.' ] );
	}

	$previous_school = sanitize_text_field( $_POST['previous_school'] ?? '' );
	$graduation      = intval( $_POST['graduation'] ?? 0 );
	$degree_link     = sanitize_text_field( $_POST['degree_link'] ?? '' );

	// Handle file upload if any
	$degree_file_url = '';
	if ( ! empty( $_FILES['degree_file'] ) && ! empty( $_FILES['degree_file']['name'] ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		$uploadedfile = $_FILES['degree_file'];
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

		if ( $movefile && ! isset( $movefile['error'] ) ) {
			$degree_file_url = $movefile['url'];
		}
	}

	if ( ! empty( $degree_file_url ) ) {
		$degree_link = $degree_file_url;
	}

	global $wpdb;
	
	// Get existing lead details to update
	$lead = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ltdh_leads WHERE id = %d", $lead_id ) );

	if ( $lead ) {
		$ref_source = $lead->referral_source;
		if ( ! empty( $previous_school ) ) {
			$ref_source .= (strpos($ref_source, '?') !== false ? '&' : '?') . 'previous_school=' . urlencode( $previous_school );
		}
		if ( ! empty( $graduation ) ) {
			$ref_source .= (strpos($ref_source, '?') !== false ? '&' : '?') . 'birth_year=' . $graduation;
		}
		if ( ! empty( $degree_link ) ) {
			$ref_source .= (strpos($ref_source, '?') !== false ? '&' : '?') . 'degree_link=' . urlencode( $degree_link );
		}

		$current_msg = $lead->error_message;
		$notes = [];
		if ( ! empty( $previous_school ) ) {
			$notes[] = "Trường cũ: " . $previous_school;
		}
		if ( ! empty( $graduation ) ) {
			$notes[] = "Năm sinh: " . $graduation;
		}
		if ( ! empty( $degree_link ) ) {
			$notes[] = "Ảnh bằng cấp: " . $degree_link;
		}

		if ( ! empty( $notes ) ) {
			$current_msg = trim( ($current_msg ? $current_msg . ' | ' : '') . implode(' | ', $notes) );
		}

		$wpdb->update(
			$wpdb->prefix . 'ltdh_leads',
			[
				'referral_source' => $ref_source,
				'error_message'   => $current_msg,
			],
			[ 'id' => $lead_id ]
		);

		// Send updated Telegram Notification
		$telegram_data = [
			'name'            => $lead->name,
			'phone'           => $lead->phone,
			'email'           => $lead->email,
			'program_id'      => $lead->program_id,
			'school_id'       => $lead->school_id,
			'major_id'        => $lead->major_id,
			'referral_source' => $ref_source,
			'message'         => '📎 Gửi bổ sung hồ sơ xác minh nâng cao. ' . (!empty($previous_school) ? 'Trường cũ: ' . $previous_school . '. ' : '') . (!empty($graduation) ? 'Năm sinh: ' . $graduation . '. ' : ''),
		];
		if ( function_exists( 'ltdh_trigger_telegram_notification' ) ) {
			ltdh_trigger_telegram_notification( $telegram_data );
		}

		wp_send_json_success( [ 'message' => 'Hồ sơ đã được bổ sung thành công.' ] );
	}

	wp_send_json_error( [ 'message' => 'Không tìm thấy lead tương ứng.' ] );
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
	// Honeypot check
	if ( ! empty( $request->get_param( 'website_confirm' ) ) ) {
		return new WP_REST_Response( [ 'error' => 'Spam detected.' ], 400 );
	}

	// Rate limiting check
	if ( ltdh_elig_is_rate_limited() ) {
		return new WP_REST_Response( [ 'error' => 'Too many requests. Please try again later.' ], 429 );
	}

	$input = [
		'name'            => sanitize_text_field( $request->get_param( 'name' ) ?? '' ),
		'education'       => sanitize_text_field( $request->get_param( 'education' ) ?? '' ),
		'major_id'        => intval( $request->get_param( 'major_id' ) ?? 0 ),
		'previous_school' => sanitize_text_field( $request->get_param( 'previous_school' ) ?? '' ),
		'graduation'      => intval( $request->get_param( 'graduation' ) ?? 0 ),
		'desired_major'   => intval( $request->get_param( 'desired_major' ) ?? 0 ),
		'training_type'   => sanitize_text_field( $request->get_param( 'training_type' ) ?? '' ),
		'campus'          => sanitize_text_field( $request->get_param( 'campus' ) ?? '' ),
		'budget'          => sanitize_text_field( $request->get_param( 'budget' ) ?? '' ),
		'phone'           => sanitize_text_field( $request->get_param( 'phone' ) ?? '' ),
		'email'           => sanitize_email( $request->get_param( 'email' ) ?? '' ),
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

/**
 * Get client IP address safely.
 */
function ltdh_get_client_ip() {
	if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
	}
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
	}
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ips = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
		return sanitize_text_field( wp_unslash( trim( $ips[0] ) ) );
	}
	return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
}

/**
 * Check if the current client is rate limited.
 * Allow 10 check requests per 5 minutes.
 */
function ltdh_elig_is_rate_limited() {
	$ip = ltdh_get_client_ip();
	if ( empty( $ip ) ) {
		return false;
	}
	$key = 'ltdh_elig_rate_' . md5( $ip );
	$count = intval( get_transient( $key ) );
	if ( $count >= 10 ) {
		return true;
	}
	set_transient( $key, $count + 1, 300 );
	return false;
}

// ----------------------------------------------------
// 12. WordPress Admin Dashboard Panels
// ----------------------------------------------------

add_action( 'admin_menu', 'ltdh_elig_register_admin_menu' );

function ltdh_elig_register_admin_menu() {
	add_menu_page(
		'Quản lý Tuyển sinh',
		'Tuyển sinh & Leads',
		'manage_options',
		'ltdh_leads_menu',
		'ltdh_elig_admin_leads_page',
		'dashicons-groups',
		30
	);

	add_submenu_page(
		'ltdh_leads_menu',
		'Danh sách Leads',
		'Danh sách Leads',
		'manage_options',
		'ltdh_leads_menu',
		'ltdh_elig_admin_leads_page'
	);

	add_submenu_page(
		'ltdh_leads_menu',
		'Lượt kiểm tra điều kiện',
		'Nhật ký kiểm tra',
		'manage_options',
		'ltdh_elig_checks_menu',
		'ltdh_elig_admin_checks_page'
	);
}

function ltdh_elig_admin_leads_page() {
	global $wpdb;
	$table = $wpdb->prefix . LTDH_TABLE_LEADS;
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Bạn không có quyền truy cập trang này.' );
	}
	
	// Handle delete lead
	if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['lead_id'] ) ) {
		check_admin_referer( 'ltdh_delete_lead_nonce' );
		$wpdb->delete( $table, [ 'id' => intval( $_GET['lead_id'] ) ] );
		echo '<div class="updated"><p>Đã xóa lead thành công.</p></div>';
	}

	// Handle bulk delete
	$bulk_action = isset( $_POST['action'] ) && $_POST['action'] !== '-1' ? sanitize_text_field( $_POST['action'] ) : '';
	if ( empty( $bulk_action ) ) {
		$bulk_action = isset( $_POST['action2'] ) && $_POST['action2'] !== '-1' ? sanitize_text_field( $_POST['action2'] ) : '';
	}

	if ( $bulk_action === 'bulk-delete' && isset( $_POST['lead_ids'] ) && is_array( $_POST['lead_ids'] ) ) {
		check_admin_referer( 'ltdh_bulk_leads_nonce' );
		$lead_ids = array_map( 'intval', $_POST['lead_ids'] );
		if ( ! empty( $lead_ids ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $lead_ids ), '%d' ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE id IN ($placeholders)", $lead_ids ) );
			echo '<div class="updated"><p>Đã xóa ' . count( $lead_ids ) . ' leads thành công.</p></div>';
		}
	}

	// Filters and Search params
	$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$sync_status = isset( $_GET['sync_status'] ) ? sanitize_text_field( wp_unslash( $_GET['sync_status'] ) ) : '';
	$school_id = isset( $_GET['school_id'] ) ? intval( $_GET['school_id'] ) : 0;
	$major_id = isset( $_GET['major_id'] ) ? intval( $_GET['major_id'] ) : 0;

	// Build WHERE query
	$where_clauses = array();
	$where_params = array();

	if ( ! empty( $search ) ) {
		$where_clauses[] = '(name LIKE %s OR phone LIKE %s OR email LIKE %s)';
		$like_search = '%' . $wpdb->esc_like( $search ) . '%';
		$where_params[] = $like_search;
		$where_params[] = $like_search;
		$where_params[] = $like_search;
	}

	if ( ! empty( $sync_status ) ) {
		$where_clauses[] = 'sync_status = %s';
		$where_params[] = $sync_status;
	}

	if ( $school_id > 0 ) {
		$where_clauses[] = 'school_id = %d';
		$where_params[] = $school_id;
	}

	if ( $major_id > 0 ) {
		$where_clauses[] = 'major_id = %d';
		$where_params[] = $major_id;
	}

	$where_sql = '';
	if ( ! empty( $where_clauses ) ) {
		$where_sql = ' WHERE ' . implode( ' AND ', $where_clauses );
	}

	// Count matching items
	$count_query = "SELECT COUNT(*) FROM $table $where_sql";
	if ( ! empty( $where_params ) ) {
		$count_query = $wpdb->prepare( $count_query, $where_params );
	}
	$total_items = intval( $wpdb->get_var( $count_query ) );

	// Pagination setup
	$per_page = 20;
	$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
	$offset = ( $paged - 1 ) * $per_page;
	$total_pages = ceil( $total_items / $per_page );

	// Fetch items
	$query = "SELECT * FROM $table $where_sql ORDER BY id DESC LIMIT %d OFFSET %d";
	$query_params = $where_params;
	$query_params[] = $per_page;
	$query_params[] = $offset;

	$leads = $wpdb->get_results( $wpdb->prepare( $query, $query_params ) );
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">Danh sách Leads Đăng ký học</h1>
		<hr class="wp-header-end">

		<!-- Search and Filters Form -->
		<form method="get" action="" style="margin-top: 15px; margin-bottom: 15px; background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 4px;">
			<input type="hidden" name="page" value="ltdh_leads_menu">
			
			<div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
				<!-- Search -->
				<div>
					<label for="lead-search" style="font-weight: 600; display: block; margin-bottom: 5px;">Tìm kiếm:</label>
					<input type="search" id="lead-search" name="s" placeholder="Số điện thoại, Họ tên, Email..." value="<?php echo esc_attr( $search ); ?>" style="width: 250px;">
				</div>
				
				<!-- Filter Sync Status -->
				<div>
					<label for="lead-sync-status" style="font-weight: 600; display: block; margin-bottom: 5px;">Đồng bộ:</label>
					<select id="lead-sync-status" name="sync_status">
						<option value="">Tất cả trạng thái</option>
						<option value="synced" <?php selected( $sync_status, 'synced' ); ?>>Synced</option>
						<option value="pending" <?php selected( $sync_status, 'pending' ); ?>>Pending</option>
						<option value="failed" <?php selected( $sync_status, 'failed' ); ?>>Failed</option>
					</select>
				</div>
				
				<!-- Filter School -->
				<div>
					<label for="lead-school" style="font-weight: 600; display: block; margin-bottom: 5px;">Trường đối tác:</label>
					<select id="lead-school" name="school_id" style="max-width: 250px;">
						<option value="">Tất cả các trường</option>
						<?php
						$all_schools = get_posts( [ 'post_type' => 'school', 'numberposts' => -1, 'post_status' => 'publish' ] );
						foreach ( $all_schools as $school ) {
							printf( '<option value="%d" %s>%s</option>', $school->ID, selected( $school_id, $school->ID, false ), esc_html( $school->post_title ) );
						}
						?>
					</select>
				</div>
				
				<!-- Filter Major -->
				<div>
					<label for="lead-major" style="font-weight: 600; display: block; margin-bottom: 5px;">Chuyên ngành đăng ký:</label>
					<select id="lead-major" name="major_id" style="max-width: 250px;">
						<option value="">Tất cả các ngành</option>
						<?php
						$all_majors = get_posts( [ 'post_type' => 'major', 'numberposts' => -1, 'post_status' => 'publish' ] );
						foreach ( $all_majors as $major ) {
							printf( '<option value="%d" %s>%s</option>', $major->ID, selected( $major_id, $major->ID, false ), esc_html( $major->post_title ) );
						}
						?>
					</select>
				</div>

				<div style="padding-top: 20px;">
					<input type="submit" class="button button-primary" value="Tìm kiếm & Lọc">
					<?php if ( ! empty( $search ) || ! empty( $sync_status ) || $school_id > 0 || $major_id > 0 ) : ?>
						<a href="admin.php?page=ltdh_leads_menu" class="button button-secondary" style="margin-left: 5px;">Xóa bộ lọc</a>
					<?php endif; ?>
				</div>
			</div>
		</form>

		<!-- Bulk Action and Table Form -->
		<form method="post" action="<?php echo esc_url( add_query_arg( array() ) ); ?>">
			<?php wp_nonce_field( 'ltdh_bulk_leads_nonce' ); ?>
			
			<div class="tablenav top" style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
				<div class="alignleft actions bulkactions" style="display: flex; gap: 5px;">
					<select name="action">
						<option value="-1">Hành động hàng loạt</option>
						<option value="bulk-delete">Xóa hàng loạt</option>
					</select>
					<input type="submit" id="doaction" class="button action" value="Áp dụng" onclick="return confirm('Bạn có chắc chắn muốn xóa những lead đã chọn?');">
				</div>
				
				<div class="tablenav-pages">
					<span class="displaying-num" style="margin-right: 10px;"><?php echo number_format_i18n( $total_items ); ?> mục</span>
					<?php
					echo paginate_links( array(
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'total'     => $total_pages,
						'current'   => $paged,
					) );
					?>
				</div>
			</div>

			<table class="wp-list-table widefat fixed striped table-view-list posts">
				<thead>
					<tr>
						<td id="cb" class="manage-column column-cb check-column" style="width: 30px; padding: 8px 10px;"><input id="cb-select-all-1" type="checkbox"></td>
						<th class="manage-column" style="width: 50px;">ID</th>
						<th class="manage-column">Họ tên</th>
						<th class="manage-column">Số điện thoại</th>
						<th class="manage-column">Email</th>
						<th class="manage-column">Ngành đăng ký</th>
						<th class="manage-column">Trường đối tác</th>
						<th class="manage-column">Cơ sở / Hệ học</th>
						<th class="manage-column" style="width: 250px;">Chi tiết khảo sát</th>
						<th class="manage-column">Trạng thái đồng bộ</th>
						<th class="manage-column">Ngày tạo</th>
						<th class="manage-column" style="width: 80px;">Hành động</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $leads ) ) : ?>
						<tr><td colspan="12" style="text-align: center;">Chưa có lượt đăng ký nào phù hợp với tìm kiếm/bộ lọc.</td></tr>
					<?php else : ?>
						<?php foreach ( $leads as $lead ) : 
							$school_title = $lead->school_id ? get_the_title( $lead->school_id ) : '—';
							$major_title = $lead->major_id ? get_the_title( $lead->major_id ) : '—';
							
							// Decode survey metadata
							$metadata_html = '—';
							if ( ! empty( $lead->referral_source ) && strpos( $lead->referral_source, 'eligibility_checker' ) !== false ) {
								$parts = parse_url( $lead->referral_source );
								if ( isset( $parts['query'] ) ) {
									parse_str( $parts['query'], $query_data );
									$metadata_html = '<div style="font-size: 11px; line-height: 1.4;">';
									if ( isset( $query_data['education_level'] ) ) {
										$metadata_html .= '<strong>Học vấn:</strong> ' . esc_html( ltdh_elig_get_education_label( $query_data['education_level'] ) ) . '<br>';
									}
									if ( ! empty( $query_data['current_major'] ) && intval( $query_data['current_major'] ) > 0 ) {
										$metadata_html .= '<strong>Ngành cũ:</strong> ' . esc_html( get_the_title( intval( $query_data['current_major'] ) ) ) . '<br>';
									}
									if ( ! empty( $query_data['previous_school'] ) ) {
										$metadata_html .= '<strong>Trường cũ:</strong> ' . esc_html( $query_data['previous_school'] ) . '<br>';
									}
									if ( isset( $query_data['birth_year'] ) ) {
										$metadata_html .= '<strong>Năm sinh:</strong> ' . esc_html( $query_data['birth_year'] ) . '<br>';
									}
									$metadata_html .= '</div>';
								}
							} else {
								$metadata_html = esc_html( $lead->referral_source );
							}
							?>
							<tr>
								<th scope="row" class="check-column" style="padding: 8px 10px;"><input id="cb-select-<?php echo esc_attr( $lead->id ); ?>" type="checkbox" name="lead_ids[]" value="<?php echo esc_attr( $lead->id ); ?>"></th>
								<td><?php echo esc_html( $lead->id ); ?></td>
								<td><strong><?php echo esc_html( $lead->name ); ?></strong></td>
								<td><a href="tel:<?php echo esc_attr( $lead->phone ); ?>"><?php echo esc_html( $lead->phone ); ?></a></td>
								<td><?php echo esc_html( $lead->email ?: '—' ); ?></td>
								<td><?php echo esc_html( $major_title ); ?></td>
								<td><?php echo esc_html( $school_title ); ?></td>
								<td>
									<?php echo esc_html( $lead->campus ? ltdh_elig_get_campus_label( $lead->campus ) : '—' ); ?> / 
									<?php echo esc_html( $lead->training_type ? ltdh_elig_get_training_label( $lead->training_type ) : '—' ); ?>
								</td>
								<td><?php echo $metadata_html; ?></td>
								<td>
									<span class="badge" style="padding: 3px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; background: <?php echo $lead->sync_status === 'synced' ? '#d1e7dd' : '#f8d7da'; ?>; color: <?php echo $lead->sync_status === 'synced' ? '#0f5132' : '#842029'; ?>;">
										<?php echo esc_html( strtoupper( $lead->sync_status ) ); ?>
									</span>
								</td>
								<td><?php echo esc_html( $lead->created_at ); ?></td>
								<td>
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ltdh_leads_menu&action=delete&lead_id=' . $lead->id ), 'ltdh_delete_lead_nonce' ) ); ?>" class="button button-link-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa lead này?');">Xóa</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
			
			<div class="tablenav bottom" style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
				<div class="alignleft actions bulkactions" style="display: flex; gap: 5px;">
					<select name="action2">
						<option value="-1">Hành động hàng loạt</option>
						<option value="bulk-delete">Xóa hàng loạt</option>
					</select>
					<input type="submit" id="doaction2" class="button action" value="Áp dụng" onclick="return confirm('Bạn có chắc chắn muốn xóa những lead đã chọn?');">
				</div>
				
				<div class="tablenav-pages">
					<span class="displaying-num" style="margin-right: 10px;"><?php echo number_format_i18n( $total_items ); ?> mục</span>
					<?php
					echo paginate_links( array(
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'total'     => $total_pages,
						'current'   => $paged,
					) );
					?>
				</div>
			</div>
		</form>
		
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const selectAll1 = document.getElementById('cb-select-all-1');
			const checkboxes = document.querySelectorAll('input[name="lead_ids[]"]');

			function toggleAll(checked) {
				checkboxes.forEach(cb => cb.checked = checked);
				if (selectAll1) selectAll1.checked = checked;
			}

			if (selectAll1) {
				selectAll1.addEventListener('change', function() {
					toggleAll(this.checked);
				});
			}
		});
		</script>
	</div>
	<?php
}

function ltdh_elig_admin_checks_page() {
	global $wpdb;
	$table = $wpdb->prefix . 'ltdh_eligibility_checks';

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Bạn không có quyền truy cập trang này.' );
	}

	// Handle clear log
	if ( isset( $_GET['action'] ) && $_GET['action'] === 'clear' ) {
		check_admin_referer( 'ltdh_clear_checks_nonce' );
		$wpdb->query( "TRUNCATE TABLE $table" );
		echo '<div class="updated"><p>Đã xóa toàn bộ nhật ký kiểm tra.</p></div>';
	}

	// Handle bulk delete
	$bulk_action = isset( $_POST['action'] ) && $_POST['action'] !== '-1' ? sanitize_text_field( $_POST['action'] ) : '';
	if ( empty( $bulk_action ) ) {
		$bulk_action = isset( $_POST['action2'] ) && $_POST['action2'] !== '-1' ? sanitize_text_field( $_POST['action2'] ) : '';
	}

	if ( $bulk_action === 'bulk-delete' && isset( $_POST['check_ids'] ) && is_array( $_POST['check_ids'] ) ) {
		check_admin_referer( 'ltdh_bulk_checks_nonce' );
		$check_ids = array_map( 'intval', $_POST['check_ids'] );
		if ( ! empty( $check_ids ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $check_ids ), '%d' ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE id IN ($placeholders)", $check_ids ) );
			echo '<div class="updated"><p>Đã xóa ' . count( $check_ids ) . ' nhật ký thành công.</p></div>';
		}
	}

	// Filters and Search params
	$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$input_education = isset( $_GET['input_education'] ) ? sanitize_text_field( wp_unslash( $_GET['input_education'] ) ) : '';
	$input_desired_major = isset( $_GET['input_desired_major'] ) ? intval( $_GET['input_desired_major'] ) : 0;
	$input_training_type = isset( $_GET['input_training_type'] ) ? sanitize_text_field( wp_unslash( $_GET['input_training_type'] ) ) : '';
	$input_campus = isset( $_GET['input_campus'] ) ? sanitize_text_field( wp_unslash( $_GET['input_campus'] ) ) : '';

	// Build WHERE query
	$where_clauses = array();
	$where_params = array();

	if ( ! empty( $search ) ) {
		$where_clauses[] = '(phone LIKE %s OR email LIKE %s OR input_previous_school LIKE %s)';
		$like_search = '%' . $wpdb->esc_like( $search ) . '%';
		$where_params[] = $like_search;
		$where_params[] = $like_search;
		$where_params[] = $like_search;
	}

	if ( ! empty( $input_education ) ) {
		$where_clauses[] = 'input_education = %s';
		$where_params[] = $input_education;
	}

	if ( $input_desired_major > 0 ) {
		$where_clauses[] = 'input_desired_major = %d';
		$where_params[] = $input_desired_major;
	}

	if ( ! empty( $input_training_type ) ) {
		$where_clauses[] = 'input_training_type = %s';
		$where_params[] = $input_training_type;
	}

	if ( ! empty( $input_campus ) ) {
		$where_clauses[] = 'input_campus = %s';
		$where_params[] = $input_campus;
	}

	$where_sql = '';
	if ( ! empty( $where_clauses ) ) {
		$where_sql = ' WHERE ' . implode( ' AND ', $where_clauses );
	}

	// Count matching items
	$count_query = "SELECT COUNT(*) FROM $table $where_sql";
	if ( ! empty( $where_params ) ) {
		$count_query = $wpdb->prepare( $count_query, $where_params );
	}
	$total_items = intval( $wpdb->get_var( $count_query ) );

	// Pagination setup
	$per_page = 20;
	$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
	$offset = ( $paged - 1 ) * $per_page;
	$total_pages = ceil( $total_items / $per_page );

	// Fetch items
	$query = "SELECT * FROM $table $where_sql ORDER BY id DESC LIMIT %d OFFSET %d";
	$query_params = $where_params;
	$query_params[] = $per_page;
	$query_params[] = $offset;

	$checks = $wpdb->get_results( $wpdb->prepare( $query, $query_params ) );
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">Nhật ký Lượt kiểm tra Điều kiện</h1>
		<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ltdh_elig_checks_menu&action=clear' ), 'ltdh_clear_checks_nonce' ) ); ?>" class="page-title-action" onclick="return confirm('Bạn có chắc chắn muốn xóa toàn bộ lịch sử log?');" style="color: #d63638; border-color: #d63638;">Xóa toàn bộ Log</a>
		<hr class="wp-header-end">

		<!-- Search and Filters Form -->
		<form method="get" action="" style="margin-top: 15px; margin-bottom: 15px; background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 4px;">
			<input type="hidden" name="page" value="ltdh_elig_checks_menu">
			
			<div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
				<!-- Search -->
				<div>
					<label for="check-search" style="font-weight: 600; display: block; margin-bottom: 5px;">Tìm kiếm:</label>
					<input type="search" id="check-search" name="s" placeholder="Số điện thoại, Email, Trường cũ..." value="<?php echo esc_attr( $search ); ?>" style="width: 250px;">
				</div>
				
				<!-- Filter Education -->
				<div>
					<label for="check-education" style="font-weight: 600; display: block; margin-bottom: 5px;">Trình độ:</label>
					<select id="check-education" name="input_education">
						<option value="">Tất cả trình độ</option>
						<option value="thap-phan" <?php selected( $input_education, 'thap-phan' ); ?>>THPT</option>
						<option value="trung-cap" <?php selected( $input_education, 'trung-cap' ); ?>>Trung cấp</option>
						<option value="cao-dang" <?php selected( $input_education, 'cao-dang' ); ?>>Cao đẳng</option>
						<option value="dai-hoc" <?php selected( $input_education, 'dai-hoc' ); ?>>Đại học</option>
						<option value="thac-si" <?php selected( $input_education, 'thac-si' ); ?>>Thạc sĩ</option>
					</select>
				</div>
				
				<!-- Filter Desired Major -->
				<div>
					<label for="check-desired-major" style="font-weight: 600; display: block; margin-bottom: 5px;">Ngành mong muốn:</label>
					<select id="check-desired-major" name="input_desired_major" style="max-width: 250px;">
						<option value="">Tất cả ngành mong muốn</option>
						<?php
						$all_majors = get_posts( [ 'post_type' => 'major', 'numberposts' => -1, 'post_status' => 'publish' ] );
						foreach ( $all_majors as $major ) {
							printf( '<option value="%d" %s>%s</option>', $major->ID, selected( $input_desired_major, $major->ID, false ), esc_html( $major->post_title ) );
						}
						?>
					</select>
				</div>
				
				<!-- Filter Training Type -->
				<div>
					<label for="check-training-type" style="font-weight: 600; display: block; margin-bottom: 5px;">Hệ học:</label>
					<select id="check-training-type" name="input_training_type">
						<option value="">Tất cả hệ học</option>
						<option value="lien-thong" <?php selected( $input_training_type, 'lien-thong' ); ?>>Liên thông</option>
						<option value="van-bang-2" <?php selected( $input_training_type, 'van-bang-2' ); ?>>Văn bằng 2</option>
						<option value="tu-xa" <?php selected( $input_training_type, 'tu-xa' ); ?>>Từ xa</option>
						<option value="vua-hoc-vua-lam" <?php selected( $input_training_type, 'vua-hoc-vua-lam' ); ?>>Vừa học vừa làm</option>
						<option value="chinh-quy" <?php selected( $input_training_type, 'chinh-quy' ); ?>>Chính quy</option>
					</select>
				</div>

				<!-- Filter Campus -->
				<div>
					<label for="check-campus" style="font-weight: 600; display: block; margin-bottom: 5px;">Cơ sở:</label>
					<select id="check-campus" name="input_campus">
						<option value="">Tất cả cơ sở</option>
						<option value="ha-noi" <?php selected( $input_campus, 'ha-noi' ); ?>>Hà Nội</option>
						<option value="ho-chi-minh" <?php selected( $input_campus, 'ho-chi-minh' ); ?>>TP. Hồ Chí Minh</option>
						<option value="da-nang" <?php selected( $input_campus, 'da-nang' ); ?>>Đà Nẵng</option>
						<option value="thai-nguyen" <?php selected( $input_campus, 'thai-nguyen' ); ?>>Thái Nguyên</option>
						<option value="online" <?php selected( $input_campus, 'online' ); ?>>Online</option>
					</select>
				</div>

				<div style="padding-top: 20px;">
					<input type="submit" class="button button-primary" value="Tìm kiếm & Lọc">
					<?php if ( ! empty( $search ) || ! empty( $input_education ) || $input_desired_major > 0 || ! empty( $input_training_type ) || ! empty( $input_campus ) ) : ?>
						<a href="admin.php?page=ltdh_elig_checks_menu" class="button button-secondary" style="margin-left: 5px;">Xóa bộ lọc</a>
					<?php endif; ?>
				</div>
			</div>
		</form>

		<!-- Bulk Action and Table Form -->
		<form method="post" action="<?php echo esc_url( add_query_arg( array() ) ); ?>">
			<?php wp_nonce_field( 'ltdh_bulk_checks_nonce' ); ?>
			
			<div class="tablenav top" style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
				<div class="alignleft actions bulkactions" style="display: flex; gap: 5px;">
					<select name="action">
						<option value="-1">Hành động hàng loạt</option>
						<option value="bulk-delete">Xóa hàng loạt</option>
					</select>
					<input type="submit" id="doaction" class="button action" value="Áp dụng" onclick="return confirm('Bạn có chắc chắn muốn xóa những nhật ký đã chọn?');">
				</div>
				
				<div class="tablenav-pages">
					<span class="displaying-num" style="margin-right: 10px;"><?php echo number_format_i18n( $total_items ); ?> mục</span>
					<?php
					echo paginate_links( array(
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'total'     => $total_pages,
						'current'   => $paged,
					) );
					?>
				</div>
			</div>

			<table class="wp-list-table widefat fixed striped table-view-list posts">
				<thead>
					<tr>
						<td id="cb" class="manage-column column-cb check-column" style="width: 30px; padding: 8px 10px;"><input id="cb-select-all-1" type="checkbox"></td>
						<th style="width: 50px;">ID</th>
						<th>Trình độ hiện tại</th>
						<th>Ngành tốt nghiệp</th>
						<th>Trường đã học</th>
						<th>Năm sinh</th>
						<th>Ngành mong muốn</th>
						<th>Hệ / Cơ sở học / Ngân sách</th>
						<th>Kết quả đối chiếu</th>
						<th>Lượt liên hệ</th>
						<th>Thời gian</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $checks ) ) : ?>
						<tr><td colspan="11" style="text-align: center;">Chưa có lượt kiểm tra nào được ghi nhận phù hợp với tìm kiếm/bộ lọc.</td></tr>
					<?php else : ?>
						<?php foreach ( $checks as $check ) : 
							$curr_major = $check->input_major_id ? get_the_title( $check->input_major_id ) : '—';
							$desired_major = $check->input_desired_major ? get_the_title( $check->input_desired_major ) : '—';
							?>
							<tr>
								<th scope="row" class="check-column" style="padding: 8px 10px;"><input id="cb-select-<?php echo esc_attr( $check->id ); ?>" type="checkbox" name="check_ids[]" value="<?php echo esc_attr( $check->id ); ?>"></th>
								<td><?php echo esc_html( $check->id ); ?></td>
								<td><strong><?php echo esc_html( ltdh_elig_get_education_label( $check->input_education ) ); ?></strong></td>
								<td><?php echo esc_html( $curr_major ); ?></td>
								<td><?php echo esc_html( $check->input_previous_school ?: '—' ); ?></td>
								<td><?php echo esc_html( $check->input_graduation ?: '—' ); ?></td>
								<td><strong><?php echo esc_html( $desired_major ); ?></strong></td>
								<td>
									Hệ: <?php echo esc_html( ltdh_elig_get_training_label( $check->input_training_type ) ); ?><br>
									Cơ sở: <?php echo esc_html( ltdh_elig_get_campus_label( $check->input_campus ) ); ?><br>
									Ngân sách: <?php echo esc_html( ltdh_elig_get_budget_label( $check->input_budget ) ); ?>
								</td>
								<td>
									Tìm thấy: <?php echo esc_html( $check->total_candidates ); ?> ngành<br>
									Khớp: <?php echo esc_html( $check->eligible_count ); ?> ngành<br>
									Match tốt nhất: <?php echo esc_html( $check->top_score ); ?>%
								</td>
								<td>
									<?php if ( ! empty( $check->phone ) ) : ?>
										📞 <strong><?php echo esc_html( $check->phone ); ?></strong><br>
									<?php endif; ?>
									<?php if ( ! empty( $check->email ) ) : ?>
										✉️ <?php echo esc_html( $check->email ); ?><br>
									<?php endif; ?>
									<?php if ( empty( $check->phone ) && empty( $check->email ) ) : ?>
										<span style="color: #94a3b8;">—</span>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $check->created_at ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
			
			<div class="tablenav bottom" style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
				<div class="alignleft actions bulkactions" style="display: flex; gap: 5px;">
					<select name="action2">
						<option value="-1">Hành động hàng loạt</option>
						<option value="bulk-delete">Xóa hàng loạt</option>
					</select>
					<input type="submit" id="doaction2" class="button action" value="Áp dụng" onclick="return confirm('Bạn có chắc chắn muốn xóa những nhật ký đã chọn?');">
				</div>
				
				<div class="tablenav-pages">
					<span class="displaying-num" style="margin-right: 10px;"><?php echo number_format_i18n( $total_items ); ?> mục</span>
					<?php
					echo paginate_links( array(
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'total'     => $total_pages,
						'current'   => $paged,
					) );
					?>
				</div>
			</div>
		</form>
		
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const selectAll1 = document.getElementById('cb-select-all-1');
			const checkboxes = document.querySelectorAll('input[name="check_ids[]"]');

			function toggleAll(checked) {
				checkboxes.forEach(cb => cb.checked = checked);
				if (selectAll1) selectAll1.checked = checked;
			}

			if (selectAll1) {
				selectAll1.addEventListener('change', function() {
					toggleAll(this.checked);
				});
			}
		});
		</script>
	</div>
	<?php
}
