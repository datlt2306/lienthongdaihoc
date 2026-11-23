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
		input_thpt_status varchar(50) NOT NULL DEFAULT '',
		input_grad_month tinyint(2) UNSIGNED DEFAULT NULL,
		input_graduation year DEFAULT NULL,
		input_grad_grade varchar(50) NOT NULL DEFAULT '',
		input_major_relation varchar(50) NOT NULL DEFAULT '',
		input_time_preference varchar(50) NOT NULL DEFAULT '',
		decision_status varchar(50) NOT NULL DEFAULT '',
		decision_data longtext DEFAULT NULL,
		input_major_id bigint(20) UNSIGNED DEFAULT 0,
		input_previous_school varchar(255) NOT NULL DEFAULT '',
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
	if ( get_option( 'ltdh_elig_table_version' ) !== '2.0' ) {
		ltdh_elig_create_table();
		update_option( 'ltdh_elig_table_version', '2.0' );
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
// 2.1 Shortcode & Widget Support
// ----------------------------------------------------

/**
 * Shortcode [ltdh_condition_checker]
 * Also aliases: [kiem_tra_dieu_kien], [ltdh_eligibility_checker]
 *
 * Supports attributes:
 * - mode: 'compact' (default) or 'full'
 * - school: School slug or ID
 * - major: Major slug or ID
 * - system: Training type slug ('tu-xa', 'vua-hoc-vua-lam', 'chinh-quy')
 * - title: Custom widget title
 * - subtitle: Custom widget subtitle
 */
function ltdh_elig_condition_checker_shortcode( $atts = [] ) {
	$parsed_atts = shortcode_atts( [
		'mode'          => 'compact',
		'school'        => '',
		'major'         => '',
		'system'        => '',
		'title'         => '',
		'subtitle'      => '',
		'wrapper_class' => '',
	], $atts, 'ltdh_condition_checker' );

	ob_start();
	get_template_part( 'template-parts/widgets/condition-checker', null, $parsed_atts );
	return ob_get_clean();
}
add_shortcode( 'ltdh_condition_checker', 'ltdh_elig_condition_checker_shortcode' );
add_shortcode( 'kiem_tra_dieu_kien', 'ltdh_elig_condition_checker_shortcode' );
add_shortcode( 'ltdh_eligibility_checker', 'ltdh_elig_condition_checker_shortcode' );

// ----------------------------------------------------
// 3. Asset Enqueue
// ----------------------------------------------------

function ltdh_elig_enqueue_assets() {
	if ( is_admin() ) {
		return;
	}

	$theme_dir = get_template_directory();
	$css_path  = $theme_dir . '/assets/css/eligibility.css';
	$eng_path  = $theme_dir . '/assets/js/eligibility-engine.js';
	$js_path   = $theme_dir . '/assets/js/eligibility.js';
	
	$css_ver = file_exists( $css_path ) ? filemtime( $css_path ) : LTDH_VERSION;
	$eng_ver = file_exists( $eng_path ) ? filemtime( $eng_path ) : LTDH_VERSION;
	$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : LTDH_VERSION;

	wp_enqueue_style(
		'ltdh-eligibility-css',
		get_template_directory_uri() . '/assets/css/eligibility.css',
		[],
		$css_ver
	);

	wp_enqueue_script(
		'ltdh-eligibility-engine-js',
		get_template_directory_uri() . '/assets/js/eligibility-engine.js',
		[],
		$eng_ver,
		true
	);

	wp_enqueue_script(
		'ltdh-eligibility-js',
		get_template_directory_uri() . '/assets/js/eligibility.js',
		[ 'ltdh-eligibility-engine-js' ],
		$js_ver,
		true
	);

	$shared_check = null;
	$param = sanitize_text_field( wp_unslash( $_GET['kq'] ?? ( $_GET['token'] ?? ( $_GET['res'] ?? ( $_GET['check_id'] ?? '' ) ) ) ) );
	if ( ! empty( $param ) && function_exists( 'ltdh_elig_get_check_results' ) ) {
		$shared_check = ltdh_elig_get_check_results( $param );
	}

	$current_context = function_exists( 'ltdh_elig_get_page_context' ) ? ltdh_elig_get_page_context() : [];

	wp_localize_script( 'ltdh-eligibility-js', 'ltdh_elig', [
		'ajax_url'        => admin_url( 'admin-ajax.php' ),
		'nonce'           => wp_create_nonce( 'ltdh_elig_nonce' ),
		'rest_url'        => esc_url_raw( rest_url( 'ltdh/v1/eligibility/' ) ),
		'initial_results' => $shared_check,
		'page_context'    => $current_context,
		'i18n'            => [
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
add_action( 'wp_enqueue_scripts', 'ltdh_elig_enqueue_assets' );

/**
 * Prevent page caches (LiteSpeed / CDN) from caching personalized eligibility share URLs.
 */
add_action( 'template_redirect', function () {
	if ( is_page_template( 'page-eligible.php' ) && ( ! empty( $_GET['kq'] ) || ! empty( $_GET['token'] ) || ! empty( $_GET['res'] ) || ! empty( $_GET['check_id'] ) ) ) {
		do_action( 'litespeed_disable_all' );
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();
	}
}, 1 );

// ----------------------------------------------------
// 4. AJAX Handler — Check Eligibility
// ----------------------------------------------------

add_action( 'wp_ajax_ltdh_elig_check', 'ltdh_elig_ajax_check' );
add_action( 'wp_ajax_nopriv_ltdh_elig_check', 'ltdh_elig_ajax_check' );

add_action( 'wp_ajax_ltdh_elig_get_check', 'ltdh_elig_ajax_get_check' );
add_action( 'wp_ajax_nopriv_ltdh_elig_get_check', 'ltdh_elig_ajax_get_check' );

add_action( 'wp_ajax_ltdh_elig_lead', 'ltdh_elig_ajax_lead' );
add_action( 'wp_ajax_nopriv_ltdh_elig_lead', 'ltdh_elig_ajax_lead' );

function ltdh_elig_ajax_get_check() {
	nocache_headers();
	if ( ltdh_elig_is_rate_limited() ) {
		wp_send_json_error( [ 'message' => 'Yêu cầu quá nhanh. Vui lòng thử lại sau.' ], 429 );
	}
	$check_param = sanitize_text_field( wp_unslash( $_GET['kq'] ?? ( $_POST['kq'] ?? ( $_GET['check_id'] ?? ( $_POST['check_id'] ?? '' ) ) ) ) );
	if ( empty( $check_param ) ) {
		wp_send_json_error( [ 'message' => 'Check token required' ], 400 );
	}
	$result = ltdh_elig_get_check_results( $check_param );
	if ( ! $result ) {
		wp_send_json_error( [ 'message' => 'Không tìm thấy kết quả đối chiếu' ], 404 );
	}
	wp_send_json_success( $result );
}

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

	$phone_raw        = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$email_raw        = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$degree_file_path = '';

	try {
		// Only accept ephemeral document upload when contact info is provided (never store permanently)
		if ( ( ! empty( $phone_raw ) || ! empty( $email_raw ) ) && ! empty( $_FILES['degree_file'] ) && ! empty( $_FILES['degree_file']['name'] ) ) {
			if ( ! function_exists( 'ltdh_handle_ephemeral_document_upload' ) ) {
				wp_send_json_error( [ 'message' => 'Chức năng tải tệp tạm thời không khả dụng.' ] );
			}
			$upload_res = ltdh_handle_ephemeral_document_upload( $_FILES['degree_file'] );
			if ( is_wp_error( $upload_res ) ) {
				wp_send_json_error( [ 'message' => $upload_res->get_error_message() ] );
			}
			$degree_file_path = $upload_res['file'] ?? '';
		}

		$degree_link = esc_url_raw( trim( wp_unslash( $_POST['degree_link'] ?? '' ) ) );

		$input = [
			'name'                  => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'education'             => sanitize_text_field( wp_unslash( $_POST['education'] ?? '' ) ),
			'thpt_status'           => sanitize_text_field( wp_unslash( $_POST['thpt_status'] ?? '' ) ),
			'grad_month'            => intval( $_POST['grad_month'] ?? 0 ),
			'grad_year'             => intval( $_POST['grad_year'] ?? ( $_POST['graduation'] ?? 0 ) ),
			'graduation'            => intval( $_POST['grad_year'] ?? ( $_POST['graduation'] ?? 0 ) ),
			'graduation_grade'      => sanitize_text_field( wp_unslash( $_POST['graduation_grade'] ?? 'kha' ) ),
			'major_relation'        => sanitize_text_field( wp_unslash( $_POST['major_relation'] ?? 'dung-nganh' ) ),
			'study_time_preference' => sanitize_text_field( wp_unslash( $_POST['study_time_preference'] ?? 'online-100' ) ),
			'major_id'              => intval( $_POST['major_id'] ?? 0 ),
			'previous_school'       => sanitize_text_field( wp_unslash( $_POST['previous_school'] ?? '' ) ),
			'desired_major'         => intval( $_POST['desired_major'] ?? 0 ),
			'training_type'         => sanitize_text_field( wp_unslash( $_POST['training_type'] ?? '' ) ),
			'campus'                => sanitize_text_field( wp_unslash( $_POST['campus'] ?? '' ) ),
			'budget'                => sanitize_text_field( wp_unslash( $_POST['budget'] ?? '' ) ),
			'phone'                 => $phone_raw,
			'email'                 => $email_raw,
			'degree_link'           => $degree_link,
			'degree_file_path'      => $degree_file_path,
		];

		// Validate required fields
		$validation = ltdh_elig_validate_input( $input );
		if ( is_wp_error( $validation ) ) {
			wp_send_json_error( [ 'message' => $validation->get_error_message() ] );
		}

		// Run eligibility engine
		$results = ltdh_elig_run_check( $input );

		// Store check in database (returns integer check_id and populates CSPRNG $share_token)
		$share_token = '';
		$check_id    = ltdh_elig_store_check( $input, $results, $share_token );

		// Attach desired major title for frontend summary
		if ( ! empty( $input['desired_major'] ) ) {
			$input['desired_major_title'] = html_entity_decode( get_the_title( intval( $input['desired_major'] ) ), ENT_QUOTES, 'UTF-8' );
		} else {
			$input['desired_major_title'] = '';
		}

		// Capture lead if phone/email provided
		$lead_id = 0;
		if ( ! empty( $input['phone'] ) || ! empty( $input['email'] ) ) {
			$lead_id = ltdh_elig_capture_lead( $input, $results, $check_id );
		}

		$public_input = $input;
		unset( $public_input['degree_file_path'] );

		wp_send_json_success( [
			'check_id'       => $check_id,
			'share_token'    => $share_token,
			'input'          => $public_input,
			'decision'       => $results['decision'] ?? null,
			'eligible'       => $results['eligible'],
			'programs'       => $results['programs'],
			'total'          => $results['total_candidates'],
			'eligible_count' => $results['eligible_count'],
			'top_score'      => $results['top_score'],
			'alternatives'   => $results['alternatives'],
			'lead_id'        => $lead_id,
		] );
	} finally {
		if ( function_exists( 'ltdh_cleanup_ephemeral_files' ) ) {
			ltdh_cleanup_ephemeral_files( [ $degree_file_path ] );
		}
	}
}

// ----------------------------------------------------
// 5. Input Validation
// ----------------------------------------------------

function ltdh_elig_validate_input( $input ) {
	$valid_education = [ 'cao-dang', 'trung-cap' ];
	$valid_training  = [ 'tu-xa', 'dao-tao-tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ];
	$valid_thpt      = [ '', 'co-bang-thpt', 'co-giay-van-hoa-thpt', 'chua-co-giay-van-hoa-thpt' ];
	$valid_grades    = [ '', 'xuat-sac-gioi', 'kha', 'trung-binh' ];
	$valid_relations = [ '', 'dung-nganh', 'nganh-gan', 'trai-nganh' ];
	$valid_time_pref = [ '', 'gio-hanh-chinh', 'ngoai-gio-cuoi-tuan', 'online-100' ];

	// Dynamic fetch campuses
	$campus_terms = get_terms( [ 'taxonomy' => 'campus', 'hide_empty' => false, 'fields' => 'slugs' ] );
	$valid_campus = ! is_wp_error( $campus_terms ) && ! empty( $campus_terms ) ? $campus_terms : [ 'ha-noi', 'ho-chi-minh', 'da-nang', 'thai-nguyen', 'online' ];
	$valid_budget = [ 'duoi-20-trieu', '20-30-trieu', '30-50-trieu', 'tren-50-trieu' ];

	if ( empty( $input['education'] ) || ! in_array( $input['education'], $valid_education, true ) ) {
		return new WP_Error( 'invalid_education', 'Vui lòng chọn trình độ học vấn hợp lệ: Cao đẳng hoặc Trung cấp.' );
	}

	if ( in_array( $input['education'], [ 'trung-cap', 'cao-dang' ], true ) ) {
		if ( empty( $input['thpt_status'] ) || ! in_array( $input['thpt_status'], $valid_thpt, true ) ) {
			return new WP_Error( 'invalid_thpt_status', 'Vui lòng chọn tình trạng bằng tốt nghiệp THPT.' );
		}
	}

	if ( 'cao-dang' === $input['education'] && 'chua-co-giay-van-hoa-thpt' === ( $input['thpt_status'] ?? '' ) ) {
		$grad_year = intval( $input['grad_year'] ?? ( $input['graduation'] ?? 0 ) );
		if ( $grad_year <= 0 ) {
			return new WP_Error( 'invalid_grad_year', 'Trường hợp Cao đẳng chưa có bằng THPT bắt buộc phải nhập năm tốt nghiệp để đối chiếu mốc 24 tháng theo quy chế.' );
		}
	}

	if ( ! empty( $input['graduation_grade'] ) && ! in_array( $input['graduation_grade'], $valid_grades, true ) ) {
		return new WP_Error( 'invalid_grade', 'Xếp loại học lực không hợp lệ.' );
	}

	if ( ! empty( $input['major_relation'] ) && ! in_array( $input['major_relation'], $valid_relations, true ) ) {
		return new WP_Error( 'invalid_major_relation', 'Mức độ liên quan ngành không hợp lệ.' );
	}

	if ( ! empty( $input['study_time_preference'] ) && ! in_array( $input['study_time_preference'], $valid_time_pref, true ) ) {
		return new WP_Error( 'invalid_time_pref', 'Quỹ thời gian học tập không hợp lệ.' );
	}

	if ( ! empty( $input['training_type'] ) && ! in_array( $input['training_type'], $valid_training, true ) ) {
		return new WP_Error( 'invalid_training', 'Hình thức học không hợp lệ.' );
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
	// Evaluate decision tree based on 2026–2027 regulations (Circulars 06/2026, 95/2026, 56/2026, 52/2026/TT-BGDĐT)
	$decision = function_exists( 'ltdh_elig_evaluate_decision_tree' )
		? ltdh_elig_evaluate_decision_tree( $input )
		: null;

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
		$type_terms = in_array( $input['training_type'], [ 'tu-xa', 'dao-tao-tu-xa' ], true )
			? [ 'dao-tao-tu-xa', 'tu-xa' ]
			: [ $input['training_type'] ];

		$query_args['tax_query'] = [
			[
				'taxonomy' => 'training_type',
				'field'    => 'slug',
				'terms'    => $type_terms,
			],
		];
	}

	// Pre-filter by campus (Safe Filter - matches selected campus or online)
	if ( ! empty( $input['campus'] ) ) {
		if ( ! isset( $query_args['tax_query'] ) ) {
			$query_args['tax_query'] = [ 'relation' => 'AND' ];
		}
		$campus_terms = [ $input['campus'] ];
		if ( 'online' !== $input['campus'] ) {
			$campus_terms[] = 'online';
		}
		$query_args['tax_query'][] = [
			'taxonomy' => 'campus',
			'field'    => 'slug',
			'terms'    => $campus_terms,
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

		// 2.1. Lệnh cấm Đào tạo từ xa ngành Y Dược & Sư phạm (Khoản 3 Điều 5 Thông tư 28/2023/TT-BGDĐT)
		if ( ! $hard_fail ) {
			$prog_training_types = wp_get_post_terms( $program_id, 'training_type', [ 'fields' => 'slugs' ] );
			$is_distance_program = in_array( 'tu-xa', $prog_training_types, true )
				|| in_array( 'dao-tao-tu-xa', $prog_training_types, true )
				|| in_array( $input['training_type'], [ 'tu-xa', 'dao-tao-tu-xa' ], true );

			if ( $is_distance_program ) {
				$prohibited_slugs = function_exists( 'ltdh_elig_get_prohibited_distance_learning_categories' ) 
					? ltdh_elig_get_prohibited_distance_learning_categories() 
					: [ 'y-khoa', 'y-da-khoa', 'duoc-hoc', 'dieu-duong', 'su-pham-toan', 'su-pham-van', 'su-pham-tieng-anh', 'giao-duc-mam-non', 'giao-duc-tieu-hoc' ];
				$prog_major_slug    = $prog_major_id ? get_post_field( 'post_name', $prog_major_id ) : '';
				$desired_major_slug = ! empty( $input['desired_major'] ) ? get_post_field( 'post_name', intval( $input['desired_major'] ) ) : '';

				if ( in_array( $prog_major_slug, $prohibited_slugs, true ) || in_array( $desired_major_slug, $prohibited_slugs, true ) ) {
					$hard_fail = true;
					$preliminary_status = 'not_compatible';
					$mismatch_reasons[] = 'Theo Thông tư 28/2023/TT-BGDĐT, ngành Y Dược và Sư phạm KHÔNG ĐƯỢC PHÉP đào tạo từ xa. Vui lòng chọn hệ Vừa làm vừa học hoặc Chính quy để đảm bảo điều kiện thực hành lâm sàng và cấp Giấy phép hành nghề.';
				}
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
				} elseif ( $prog_major_id && ltdh_elig_are_majors_related( $input['major_id'], $prog_major_id ) ) {
					$match_score += $weights['major_related'];
					$match_reasons[] = 'Ngành bạn đã tốt nghiệp có liên quan mật thiết với ngành muốn học.';
				} else {
					if ( $preliminary_status !== 'not_compatible' ) {
						$preliminary_status = 'needs_verification';
					}
					$verification_items[] = 'Ngành bạn muốn học khác với ngành đã tốt nghiệp (Cần kiểm tra quy chế tiếp nhận ngành chéo của trường).';
				}
			}

			// 3.1 Mức độ liên quan ngành tự khai báo (Thông tư 95/2026 & Thông tư 52/2026/TT-BGDĐT)
			$major_rel = $input['major_relation'] ?? '';
			if ( 'dung-nganh' === $major_rel ) {
				$match_score += 15;
			} elseif ( 'nganh-gan' === $major_rel ) {
				$match_score += 10;
			} elseif ( 'trai-nganh' === $major_rel ) {
				$verification_items[] = 'Đăng ký trái ngành: Cần học bổ sung kiến thức khối ngành/cơ sở trước khi vào học chính thức theo quy định.';
			}
		}

		// 4. Training Type Compatibility (Safe checking) & Quỹ thời gian Thông tư 56/2026/TT-BGDĐT
		if ( ! $hard_fail && ! empty( $decision['exclude_chinh_quy'] ) ) {
			$prog_training_types = wp_get_post_terms( $program_id, 'training_type', [ 'fields' => 'slugs' ] );
			if ( in_array( 'chinh-quy', $prog_training_types, true ) && count( $prog_training_types ) === 1 ) {
				$hard_fail = true;
				$preliminary_status = 'not_compatible';
				$mismatch_reasons[] = 'Theo Điều 5 Thông tư 56/2026/TT-BGDĐT, hệ Chính quy liên thông bắt buộc học trực tiếp tại trường trong giờ hành chính (>50% khối lượng chương trình), không phù hợp với quỹ thời gian bạn đã chọn.';
			}
		}

		// 4. Training Type Compatibility (Safe checking)
		if ( ! $hard_fail && ! empty( $input['training_type'] ) ) {
			$user_edu = $input['education'];
			$allowed_types = $compatibility[ $user_edu ] ?? [];
			$is_compat = in_array( $input['training_type'], $allowed_types, true )
				|| ( in_array( $input['training_type'], [ 'tu-xa', 'dao-tao-tu-xa' ], true ) && ( in_array( 'tu-xa', $allowed_types, true ) || in_array( 'dao-tao-tu-xa', $allowed_types, true ) ) );
			if ( ! $is_compat ) {
				if ( $preliminary_status !== 'not_compatible' ) {
					$preliminary_status = 'needs_verification';
				}
				$verification_items[] = 'Hình thức đào tạo ' . ltdh_elig_get_training_label( $input['training_type'] ) . ' cần được nhà trường xác nhận với trình độ hiện tại.';
			} else {
				$match_score += $weights['schedule_match'];
				$match_reasons[] = 'Hỗ trợ hình thức học ' . ltdh_elig_get_training_label( $input['training_type'] ) . ' phù hợp.';
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

			// Chuẩn hóa tính toán tổng học phí thực tế:
			if ( $tuition_num > 0 ) {
				if ( $tuition_num < 2000000 ) {
					// Học phí theo tín chỉ (khoảng 400.000 - 800.000 VNĐ / tín chỉ x ~75 tín chỉ)
					$total_cost = $tuition_num * 75;
				} elseif ( $tuition_num <= 30000000 ) {
					// Học phí theo học kỳ (khoảng 10.000.000 - 20.000.000 VNĐ / kỳ)
					$semesters  = max( 2, (int) round( $duration_num * 2 ) );
					$total_cost = $tuition_num * $semesters;
				} else {
					// Học phí trọn khóa
					$total_cost = $tuition_num;
				}
			} else {
				$total_cost = 0;
			}

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
		$school_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $program_id ) : 0;
		if ( ! $school_id ) {
			$raw_school = get_field( 'school_relationship', $program_id );
			if ( is_array( $raw_school ) ) {
				$raw_school = ! empty( $raw_school ) ? $raw_school[0] : 0;
			}
			if ( is_object( $raw_school ) ) {
				$raw_school = $raw_school->ID;
			}
			$school_id = intval( $raw_school );
		}

		if ( ! $school_id || 'publish' !== get_post_status( $school_id ) ) {
			continue;
		}

		$school_logo_id = ltdh_get_school_image_id( $school_id );

		$program_data = [
			'program_id'         => $program_id,
			'title'              => html_entity_decode( get_the_title( $program_id ), ENT_QUOTES, 'UTF-8' ),
			'permalink'          => get_permalink( $program_id ),
			'thumbnail'          => get_the_post_thumbnail_url( $program_id, 'medium' ) ?: get_stylesheet_directory_uri() . '/assets/images/banner-default.jpg',
			'score'              => $match_score,
			'preliminary_status' => $preliminary_status,
			'match_reasons'      => $match_reasons,
			'verification_items' => $verification_items,
			'mismatch_reasons'   => $mismatch_reasons,
			'school'             => $school_id ? [
				'id'     => $school_id,
				'title'  => html_entity_decode( get_the_title( $school_id ), ENT_QUOTES, 'UTF-8' ),
				'code'   => get_post_meta( $school_id, 'school_code', true ) ?: '',
				'logo'   => $school_logo_id ? wp_get_attachment_image_url( $school_logo_id, 'thumbnail' ) : '',
			] : null,
			'major'              => $prog_major_id ? html_entity_decode( get_the_title( $prog_major_id ), ENT_QUOTES, 'UTF-8' ) : '',
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

	// Build curated, intelligent alternatives
	$alternatives = ltdh_elig_build_smart_alternatives( $input, $eligible_slice, $hierarchy );

	return [
		'decision'         => $decision,
		'eligible'         => ! empty( $eligible_slice ),
		'programs'         => $eligible_slice,
		'total_candidates' => $total_candidates,
		'eligible_count'   => count( $eligible ),
		'top_score'        => $eligible_slice[0]['score'] ?? 0,
		'alternatives'     => $alternatives,
	];
}

/**
 * Build smart, relevant alternatives when primary search needs backup options.
 *
 * @param array $input User input data.
 * @param array $eligible_slice Currently matched eligible programs.
 * @param array $hierarchy Education hierarchy map.
 * @return array List of curated alternative programs.
 */
function ltdh_elig_build_smart_alternatives( $input, $eligible_slice, $hierarchy ) {
	$eligible_ids = ! empty( $eligible_slice ) ? wp_list_pluck( $eligible_slice, 'program_id' ) : [];
	$target_major = ! empty( $input['desired_major'] ) ? intval( $input['desired_major'] ) : intval( $input['major_id'] ?? 0 );
	$user_level   = $hierarchy[ $input['education'] ?? '' ] ?? 0;

	$args = [
		'post_type'      => LTDH_CPT_PROGRAM,
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'fields'         => 'ids',
		'post__not_in'   => ! empty( $eligible_ids ) ? $eligible_ids : [ 0 ],
		'meta_query'     => [
			[
				'key'     => LTDH_META_ADMISSION_STATUS,
				'value'   => LTDH_STATUS_OPEN,
				'compare' => '=',
			],
		],
	];

	$candidate_ids = get_posts( $args );
	if ( empty( $candidate_ids ) ) {
		return [];
	}

	$prohibited_slugs = function_exists( 'ltdh_elig_get_prohibited_distance_learning_categories' ) 
		? ltdh_elig_get_prohibited_distance_learning_categories() 
		: [ 'y-khoa', 'y-da-khoa', 'duoc-hoc', 'dieu-duong', 'su-pham-toan', 'su-pham-van', 'su-pham-tieng-anh', 'giao-duc-mam-non', 'giao-duc-tieu-hoc' ];

	$alternatives = [];

	foreach ( $candidate_ids as $prog_id ) {
		if ( count( $alternatives ) >= 3 ) {
			break;
		}

		$school_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $prog_id ) : 0;
		if ( ! $school_id ) {
			$raw_school = get_field( 'school_relationship', $prog_id );
			if ( is_array( $raw_school ) ) {
				$raw_school = ! empty( $raw_school ) ? $raw_school[0] : 0;
			}
			if ( is_object( $raw_school ) ) {
				$raw_school = $raw_school->ID;
			}
			$school_id = intval( $raw_school );
		}
		if ( ! $school_id || 'publish' !== get_post_status( $school_id ) ) {
			continue;
		}

		$prog_major_id = get_field( 'major_relationship', $prog_id );
		if ( is_array( $prog_major_id ) ) {
			$prog_major_id = ! empty( $prog_major_id ) ? $prog_major_id[0] : 0;
		}
		if ( is_object( $prog_major_id ) ) {
			$prog_major_id = $prog_major_id->ID;
		}
		$prog_major_id = intval( $prog_major_id );

		$min_edu = get_field( 'elig_min_education', $prog_id ) ?: '';
		if ( $min_edu && $user_level ) {
			$min_level = $hierarchy[ $min_edu ] ?? 0;
			if ( $user_level < $min_level ) {
				continue;
			}
		}

		$prog_tt_slugs = wp_get_post_terms( $prog_id, 'training_type', [ 'fields' => 'slugs' ] );
		$is_distance   = in_array( 'tu-xa', $prog_tt_slugs, true ) || in_array( 'dao-tao-tu-xa', $prog_tt_slugs, true );

		if ( $is_distance ) {
			$prog_major_slug = $prog_major_id ? get_post_field( 'post_name', $prog_major_id ) : '';
			if ( in_array( $prog_major_slug, $prohibited_slugs, true ) ) {
				continue;
			}
		}

		$is_same_major    = false;
		$is_related_major = false;

		if ( $target_major > 0 ) {
			if ( $prog_major_id === $target_major ) {
				$is_same_major = true;
			} elseif ( $prog_major_id && function_exists( 'ltdh_elig_are_majors_related' ) && ltdh_elig_are_majors_related( $target_major, $prog_major_id ) ) {
				$is_related_major = true;
			} else {
				// Strictly skip any program with unrelated major
				continue;
			}
		}

		$major_title = $prog_major_id ? html_entity_decode( get_the_title( $prog_major_id ), ENT_QUOTES, 'UTF-8' ) : '';
		$school_name = html_entity_decode( get_the_title( $school_id ), ENT_QUOTES, 'UTF-8' );

		if ( $is_same_major ) {
			if ( $is_distance ) {
				$reason = 'Chương trình cùng ngành với hình thức Đào tạo từ xa linh hoạt (học online 100%, chủ động thời gian, văn bằng giá trị tương đương chính quy).';
				$badge  = 'Cùng ngành • Đào tạo từ xa';
			} elseif ( in_array( 'vua-hoc-vua-lam', $prog_tt_slugs, true ) ) {
				$reason = 'Chương trình cùng ngành hệ Vừa học vừa làm (học buổi tối & cuối tuần, thuận tiện cho người đi làm).';
				$badge  = 'Cùng ngành • Vừa học vừa làm';
			} else {
				$reason = 'Phương án tuyển sinh mở rộng cùng ngành ' . ( $major_title ? ' (' . $major_title . ')' : '' ) . ' tại ' . $school_name . '.';
				$badge  = 'Cùng ngành đào tạo';
			}
		} elseif ( $is_related_major ) {
			$reason = 'Ngành gần với định hướng của bạn' . ( $major_title ? ' (' . $major_title . ')' : '' ) . ' — cơ hội việc làm và chương trình đào tạo tương đương.';
			$badge  = 'Ngành liên quan gần';
		} else {
			$reason = 'Chương trình đào tạo uy tín đang mở đợt tuyển sinh phù hợp với hồ sơ văn bằng của bạn.';
			$badge  = 'Chương trình nổi bật';
		}

		$school_logo_id = ltdh_get_school_image_id( $school_id );

		$alternatives[] = [
			'program_id'         => $prog_id,
			'title'              => html_entity_decode( get_the_title( $prog_id ), ENT_QUOTES, 'UTF-8' ),
			'permalink'          => get_permalink( $prog_id ),
			'thumbnail'          => get_the_post_thumbnail_url( $prog_id, 'medium' ) ?: get_stylesheet_directory_uri() . '/assets/images/banner-default.jpg',
			'score'              => 75,
			'preliminary_status' => 'alternative',
			'alt_badge'          => $badge,
			'reason'             => $reason,
			'suggestion_reason'  => $reason,
			'match_reasons'      => [ $reason ],
			'verification_items' => [],
			'mismatch_reasons'   => [],
			'school'             => [
				'id'    => $school_id,
				'title' => $school_name,
				'code'  => get_post_meta( $school_id, 'school_code', true ) ?: '',
				'logo'  => $school_logo_id ? wp_get_attachment_image_url( $school_logo_id, 'thumbnail' ) : '',
			],
			'major'              => $major_title,
			'training_type'      => wp_get_post_terms( $prog_id, 'training_type', [ 'fields' => 'names' ] )[0] ?? 'Linh hoạt',
			'tuition_fee'        => get_post_meta( $prog_id, 'tuition_fee', true ) ?: 'Theo quy định nhà trường',
			'duration'           => function_exists( 'ltdh_get_program_duration' ) ? ltdh_get_program_duration( $prog_id ) : ( get_post_meta( $prog_id, 'duration', true ) ?: '1.5 – 2 năm' ),
			'schedule'           => get_post_meta( $prog_id, 'schedule', true ) ?: 'Linh hoạt (Online/Tối/Cuối tuần)',
			'campus_info'        => implode( ', ', wp_get_post_terms( $prog_id, 'campus', [ 'fields' => 'names' ] ) ) ?: 'Toàn quốc (Online)',
			'is_alternative'     => true,
		];
	}

	return $alternatives;
}

// ----------------------------------------------------
// 7. Helpers
// ----------------------------------------------------

function ltdh_elig_parse_tuition( $str ) {
	if ( function_exists( 'ltdh_compare_parse_tuition' ) ) {
		return (int) ltdh_compare_parse_tuition( $str );
	}
	$str = str_replace( [ '.', ',', 'đ', '₫', '/tín chỉ', '/hoc ky', '/học kỳ' ], '', $str );
	$str = trim( $str );
	return intval( $str );
}

function ltdh_elig_parse_duration( $str ) {
	if ( function_exists( 'ltdh_compare_parse_duration' ) ) {
		return (float) ltdh_compare_parse_duration( $str );
	}
	preg_match( '/[\d.]+/', $str, $m );
	return $m ? floatval( $m[0] ) : 1;
}

function ltdh_elig_get_education_label( $slug ) {
	$map = [
		'cao-dang'  => 'Cao đẳng',
		'trung-cap' => 'Trung cấp',
	];
	return $map[ $slug ] ?? $slug;
}

function ltdh_elig_get_training_label( $slug ) {
	$map = [
		'tu-xa'            => 'Hệ đào tạo từ xa',
		'dao-tao-tu-xa'    => 'Hệ đào tạo từ xa',
		'vua-hoc-vua-lam'  => 'Hệ vừa học vừa làm',
		'chinh-quy'        => 'Hệ chính quy',
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

function ltdh_elig_store_check( $input, $results, &$out_token = '' ) {
	global $wpdb;
	$table = $wpdb->prefix . LTDH_TABLE_ELIGIBILITY;

	$token     = bin2hex( random_bytes( 16 ) );
	$out_token = $token;

	$grad_year = ! empty( $input['grad_year'] ) ? intval( $input['grad_year'] ) : ( ! empty( $input['graduation'] ) ? intval( $input['graduation'] ) : null );

	$data = [
		'session_token'         => $token,
		'input_education'       => sanitize_text_field( $input['education'] ?? '' ),
		'input_thpt_status'     => sanitize_text_field( $input['thpt_status'] ?? '' ),
		'input_grad_month'      => ! empty( $input['grad_month'] ) ? intval( $input['grad_month'] ) : null,
		'input_graduation'      => $grad_year,
		'input_grad_grade'      => sanitize_text_field( $input['graduation_grade'] ?? '' ),
		'input_major_relation'  => sanitize_text_field( $input['major_relation'] ?? '' ),
		'input_time_preference' => sanitize_text_field( $input['study_time_preference'] ?? '' ),
		'decision_status'       => sanitize_text_field( $results['decision']['status'] ?? '' ),
		'decision_data'         => ! empty( $results['decision'] ) ? wp_json_encode( $results['decision'] ) : null,
		'input_major_id'        => intval( $input['major_id'] ?? 0 ),
		'input_previous_school' => sanitize_text_field( $input['previous_school'] ?? '' ),
		'input_desired_major'   => intval( $input['desired_major'] ?? 0 ),
		'input_training_type'   => sanitize_text_field( $input['training_type'] ?? '' ),
		'input_campus'          => sanitize_text_field( $input['campus'] ?? '' ),
		'input_budget'          => sanitize_text_field( $input['budget'] ?? '' ),
		'total_candidates'      => intval( $results['total_candidates'] ?? 0 ),
		'eligible_count'        => intval( $results['eligible_count'] ?? 0 ),
		'top_score'             => intval( $results['top_score'] ?? 0 ),
		'top_program_id'        => intval( $results['programs'][0]['program_id'] ?? 0 ),
		'phone'                 => sanitize_text_field( $input['phone'] ?? '' ),
		'email'                 => sanitize_email( $input['email'] ?? '' ),
		'created_at'            => current_time( 'mysql' ),
		'referrer_url'          => wp_get_referer() ?: '',
	];

	$wpdb->insert( $table, $data );

	return $wpdb->insert_id;
}

/**
 * Retrieve saved check data and evaluate results for permalink sharing.
 *
 * Security (CRIT-04): Unauthenticated callers may ONLY query via cryptographic
 * session_token (never sequential integer IDs), and all PII fields (phone, email,
 * previous_school, graduation) are redacted from shared results.
 *
 * @param int|string $check_identifier Session token (or integer ID for admins only).
 * @return array|null Check result object or null if not found.
 */
function ltdh_elig_get_check_results( $check_identifier ) {
	global $wpdb;
	$table    = $wpdb->prefix . 'ltdh_eligibility_checks';
	$is_admin = is_user_logged_in() && current_user_can( 'manage_options' );
	$raw_id   = trim( (string) $check_identifier );

	if ( '' === $raw_id ) {
		return null;
	}

	if ( ctype_digit( $raw_id ) ) {
		if ( ! $is_admin ) {
			// Block sequential IDOR enumeration for public callers
			return null;
		}
		$check_id = intval( $raw_id );
		if ( $check_id <= 0 ) {
			return null;
		}
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $check_id ), ARRAY_A );
	} else {
		$token = sanitize_text_field( $raw_id );
		if ( ! preg_match( '/^[a-zA-Z0-9]{24,64}$/', $token ) ) {
			return null;
		}
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE session_token = %s", $token ), ARRAY_A );
	}

	if ( ! $row ) {
		return null;
	}

	// Re-run eligibility engine using academic criteria while stripping PII from public payload
	$engine_input = [
		'name'                  => '',
		'education'             => sanitize_text_field( $row['input_education'] ?? '' ),
		'thpt_status'           => sanitize_text_field( $row['input_thpt_status'] ?? '' ),
		'grad_month'            => ! empty( $row['input_grad_month'] ) ? intval( $row['input_grad_month'] ) : 0,
		'grad_year'             => ! empty( $row['input_graduation'] ) ? intval( $row['input_graduation'] ) : 0,
		'graduation'            => ! empty( $row['input_graduation'] ) ? intval( $row['input_graduation'] ) : 0,
		'graduation_grade'      => sanitize_text_field( $row['input_grad_grade'] ?? 'kha' ),
		'major_relation'        => sanitize_text_field( $row['input_major_relation'] ?? 'dung-nganh' ),
		'study_time_preference' => sanitize_text_field( $row['input_time_preference'] ?? 'online-100' ),
		'major_id'              => intval( $row['input_major_id'] ?? 0 ),
		'previous_school'       => '',
		'desired_major'         => intval( $row['input_desired_major'] ?? 0 ),
		'training_type'         => sanitize_text_field( $row['input_training_type'] ?? '' ),
		'campus'                => sanitize_text_field( $row['input_campus'] ?? '' ),
		'budget'                => sanitize_text_field( $row['input_budget'] ?? '' ),
		'phone'                 => '',
		'email'                 => '',
		'degree_link'      => '',
		'degree_file_path' => '',
	];

	$results = ltdh_elig_run_check( $engine_input );

	$public_input = $engine_input;
	$public_input['graduation'] = 0; // Do not expose birth/graduation year on shared links
	unset( $public_input['degree_file_path'] );

	if ( ! empty( $public_input['desired_major'] ) ) {
		$public_input['desired_major_title'] = html_entity_decode( get_the_title( intval( $public_input['desired_major'] ) ), ENT_QUOTES, 'UTF-8' );
	} else {
		$public_input['desired_major_title'] = '';
	}

	$decision_output = $results['decision'] ?? null;
	if ( empty( $decision_output ) && ! empty( $row['decision_data'] ) ) {
		$decision_output = json_decode( $row['decision_data'], true );
	}

	return [
		'check_id'       => $is_admin ? intval( $row['id'] ) : 0,
		'share_token'    => sanitize_text_field( $row['session_token'] ?? '' ),
		'input'          => $public_input,
		'decision'       => $decision_output,
		'eligible'       => $results['eligible'],
		'programs'       => $results['programs'],
		'total'          => $results['total_candidates'],
		'eligible_count' => $results['eligible_count'],
		'top_score'      => $results['top_score'],
		'alternatives'   => $results['alternatives'],
		'is_shared'      => true,
	];
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

	$decision    = $results['decision'] ?? null;
	$thpt_status = $input['thpt_status'] ?? '';
	$grad_year   = ! empty( $input['grad_year'] ) ? intval( $input['grad_year'] ) : ( ! empty( $input['graduation'] ) ? intval( $input['graduation'] ) : 0 );
	$grad_month  = ! empty( $input['grad_month'] ) ? intval( $input['grad_month'] ) : 0;
	$months_grad = $decision['months_since_graduation'] ?? '';
	$status_val  = $decision['status'] ?? '';
	$priority    = $decision['priority_level'] ?? 'medium';
	$suit_types  = ! empty( $decision['suitable_training_types'] ) ? implode( ',', $decision['suitable_training_types'] ) : '';

	$ref_source = 'eligibility_checker?education_level=' . urlencode( (string) ( $input['education'] ?? '' ) ) . 
		'&thpt_status=' . urlencode( (string) $thpt_status ) .
		'&grad_year=' . urlencode( (string) $grad_year ) .
		'&grad_month=' . urlencode( (string) $grad_month ) .
		'&months_since_grad=' . urlencode( (string) $months_grad ) .
		'&grad_grade=' . urlencode( (string) ( $input['graduation_grade'] ?? '' ) ) .
		'&major_relation=' . urlencode( (string) ( $input['major_relation'] ?? '' ) ) .
		'&time_preference=' . urlencode( (string) ( $input['study_time_preference'] ?? '' ) ) .
		'&decision_status=' . urlencode( (string) $status_val ) .
		'&priority_level=' . urlencode( (string) $priority ) .
		'&suit_types=' . urlencode( (string) $suit_types ) .
		'&current_major=' . urlencode( (string) ( $input['major_id'] ?? '' ) ) . 
		'&previous_school=' . urlencode( (string) ( $input['previous_school'] ?? '' ) ) .
		'&desired_major=' . urlencode( (string) ( $input['desired_major'] ?? '' ) ) . 
		'&verification_required=' . $is_verification_required;

	if ( ! empty( $input['degree_link'] ) ) {
		$ref_source .= '&degree_link=' . urlencode( $input['degree_link'] );
	}

	$lead_notes = [];
	if ( $status_val ) {
		$status_label = 'ĐỦ ĐIỀU KIỆN';
		if ( 'warning_urgent' === $status_val ) {
			$status_label = 'ĐỦ ĐIỀU KIỆN CÓ LƯU Ý';
		} elseif ( in_array( $status_val, [ 'not_eligible', 'not_eligible_2027' ], true ) ) {
			$status_label = 'CHƯA ĐỦ ĐIỀU KIỆN';
		}
		$lead_notes[] = "Kết quả: " . $status_label;
	}
	if ( ! empty( $input['education'] ) ) {
		$lead_notes[] = "Học vấn: " . ltdh_elig_get_education_label( $input['education'] );
	}
	if ( $thpt_status && function_exists( 'ltdh_elig_get_thpt_status_label' ) ) {
		$lead_notes[] = "THPT: " . ltdh_elig_get_thpt_status_label( $thpt_status );
	}
	if ( $suit_types ) {
		$lead_notes[] = "Hệ đề xuất (TT 56): " . $suit_types;
	}
	if ( ! empty( $input['degree_link'] ) ) {
		$lead_notes[] = "Ảnh bằng cấp: " . $input['degree_link'];
	}
	$lead_msg = implode( " | ", $lead_notes );

	// Use existing lead capture system
	if ( function_exists( 'ltdh_insert_lead' ) ) {
		$lead_id = ltdh_insert_lead( [
			'name'            => ! empty( $input['name'] ) ? $input['name'] : 'Eligibility Checker User',
			'phone'           => $input['phone'],
			'email'           => $input['email'],
			'program_id'      => $program_id,
			'school_id'       => $school_id,
			'major_id'        => $major_id,
			'training_type'    => $input['training_type'],
			'campus'           => $input['campus'],
			'referral_source'  => $ref_source,
			'message'          => $lead_msg,
			'degree_file_path' => $input['degree_file_path'] ?? '',
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
		'error_message' => $lead_msg,
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

	// Google reCAPTCHA check
	if ( function_exists( 'ltdh_is_recaptcha_enabled' ) && ltdh_is_recaptcha_enabled() ) {
		$recaptcha_token = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ?? '' ) );
		if ( ! ltdh_verify_recaptcha( $recaptcha_token, 'elig_lead' ) ) {
			wp_send_json_error( [ 'message' => 'Xác thực reCAPTCHA không thành công hoặc nghi ngờ tự động.' ], 403 );
		}
	}

	$name          = sanitize_text_field( wp_unslash( $_POST['cf_name'] ?? '' ) );
	$phone         = sanitize_text_field( wp_unslash( $_POST['cf_phone'] ?? '' ) );
	$email         = sanitize_email( wp_unslash( $_POST['cf_email'] ?? '' ) );
	$raw_check_ref = sanitize_text_field( wp_unslash( $_POST['elig_check_id'] ?? '' ) );
	$check_id      = ctype_digit( $raw_check_ref ) ? intval( $raw_check_ref ) : 0;
	$program_id    = intval( $_POST['elig_program_id'] ?? 0 );

	if ( empty( $name ) || empty( $phone ) ) {
		wp_send_json_error( [ 'message' => 'Vui lòng nhập họ tên và số điện thoại.' ] );
	}

	$previous_school      = sanitize_text_field( wp_unslash( $_POST['previous_school'] ?? '' ) );
	$graduation           = intval( $_POST['graduation'] ?? 0 );
	$user_message         = sanitize_textarea_field( wp_unslash( $_POST['cf_message'] ?? '' ) );
	$degree_link          = esc_url_raw( trim( wp_unslash( $_POST['degree_link'] ?? '' ) ) );
	$transcript_link      = esc_url_raw( trim( wp_unslash( $_POST['transcript_link'] ?? '' ) ) );
	$degree_file_path     = '';
	$transcript_file_path = '';

	try {
		// Process degree file upload into protected ephemeral storage
		if ( ! empty( $_FILES['degree_file'] ) && ! empty( $_FILES['degree_file']['name'] ) ) {
			if ( ! function_exists( 'ltdh_handle_ephemeral_document_upload' ) ) {
				wp_send_json_error( [ 'message' => 'Chức năng tải tệp tạm thời không khả dụng.' ] );
			}
			$upload_res = ltdh_handle_ephemeral_document_upload( $_FILES['degree_file'] );
			if ( is_wp_error( $upload_res ) ) {
				wp_send_json_error( [ 'message' => 'Tệp bằng cấp: ' . $upload_res->get_error_message() ] );
			}
			$degree_file_path = $upload_res['file'] ?? '';
		}

		// Process transcript file upload into protected ephemeral storage
		if ( ! empty( $_FILES['transcript_file'] ) && ! empty( $_FILES['transcript_file']['name'] ) ) {
			if ( ! function_exists( 'ltdh_handle_ephemeral_document_upload' ) ) {
				wp_send_json_error( [ 'message' => 'Chức năng tải tệp tạm thời không khả dụng.' ] );
			}
			$upload_res = ltdh_handle_ephemeral_document_upload( $_FILES['transcript_file'] );
			if ( is_wp_error( $upload_res ) ) {
				wp_send_json_error( [ 'message' => 'Tệp bảng điểm: ' . $upload_res->get_error_message() ] );
			}
			$transcript_file_path = $upload_res['file'] ?? '';
		}

		$school_id = $program_id ? intval( get_post_meta( $program_id, 'school_relationship', true ) ) : 0;
		$major_id  = $program_id ? intval( get_post_meta( $program_id, 'major_relationship', true ) ) : 0;

		$context_prog_id   = intval( $_POST['context_program_id'] ?? 0 );
		$context_school_id = intval( $_POST['context_school_id'] ?? 0 );
		$context_major_id  = intval( $_POST['context_major_id'] ?? 0 );
		$page_url          = esc_url_raw( trim( wp_unslash( $_POST['page_url'] ?? '' ) ) );

		if ( ! $program_id && $context_prog_id > 0 ) {
			$program_id = $context_prog_id;
		}
		if ( ! $school_id && $context_school_id > 0 ) {
			$school_id = $context_school_id;
		}
		if ( ! $major_id && $context_major_id > 0 ) {
			$major_id = $context_major_id;
		}

		// Query original eligibility checker survey responses (supports integer check_id or session_token)
		$campus_val   = '';
		$training_val = '';
		$ref_source   = 'eligibility_checker_form';
		$check_row    = null;

		if ( $check_id > 0 ) {
			global $wpdb;
			$check_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ltdh_eligibility_checks WHERE id = %d", $check_id ) );
		} elseif ( '' !== $raw_check_ref && preg_match( '/^[a-zA-Z0-9]{24,64}$/', $raw_check_ref ) ) {
			global $wpdb;
			$check_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ltdh_eligibility_checks WHERE session_token = %s", $raw_check_ref ) );
			if ( $check_row ) {
				$check_id = (int) $check_row->id;
			}
		}

		// Evaluated criteria from wizard/results or compact widget
		$eval_education     = sanitize_text_field( wp_unslash( $_POST['eval_education'] ?? ( $_POST['cf_education'] ?? ( $check_row->input_education ?? '' ) ) ) );
		$eval_thpt_status   = sanitize_text_field( wp_unslash( $_POST['eval_thpt_status'] ?? ( $_POST['cf_thpt_status'] ?? ( $check_row->input_thpt_status ?? '' ) ) ) );
		$eval_grad_month    = intval( $_POST['eval_grad_month'] ?? ( $_POST['cf_grad_month'] ?? ( $check_row->input_grad_month ?? 0 ) ) );
		$eval_grad_year     = intval( $_POST['eval_grad_year'] ?? ( $_POST['cf_grad_year'] ?? ( $check_row->input_graduation ?? ( $graduation ?: 0 ) ) ) );
		$eval_months        = intval( $_POST['eval_months_since_grad'] ?? ( $_POST['cf_months_since_grad'] ?? 0 ) );
		$eval_grade         = sanitize_text_field( wp_unslash( $_POST['eval_graduation_grade'] ?? ( $_POST['cf_grad_grade'] ?? ( $check_row->input_grad_grade ?? '' ) ) ) );
		$eval_relation      = sanitize_text_field( wp_unslash( $_POST['eval_major_relation'] ?? ( $_POST['cf_major_relation'] ?? ( $check_row->input_major_relation ?? '' ) ) ) );
		$eval_time_pref     = sanitize_text_field( wp_unslash( $_POST['eval_study_time_preference'] ?? ( $_POST['cf_time_pref'] ?? ( $check_row->input_time_preference ?? '' ) ) ) );
		$eval_decision      = sanitize_text_field( wp_unslash( $_POST['eval_decision_status'] ?? ( $_POST['cf_decision_status'] ?? ( $check_row->decision_status ?? '' ) ) ) );
		$eval_priority      = sanitize_text_field( wp_unslash( $_POST['eval_priority_level'] ?? ( $_POST['cf_priority_level'] ?? '' ) ) );
		$eval_suit_types    = sanitize_text_field( wp_unslash( $_POST['eval_suitable_training_types'] ?? ( $_POST['cf_suitable_training_types'] ?? '' ) ) );

		if ( $check_row ) {
			$campus_val = $check_row->input_campus;
			$training_val = $check_row->input_training_type;
			$is_verification_required = ( $program_id && get_field( 'elig_min_education', $program_id ) ) ? '1' : '0';
			$ref_source = 'eligibility_checker?education_level=' . urlencode( (string) ( $eval_education ?: $check_row->input_education ) ) . 
				'&thpt_status=' . urlencode( (string) ( $eval_thpt_status ?: $check_row->input_thpt_status ) ) .
				'&grad_year=' . urlencode( (string) $eval_grad_year ) .
				'&grad_month=' . urlencode( (string) $eval_grad_month ) .
				'&months_since_grad=' . urlencode( (string) $eval_months ) .
				'&grad_grade=' . urlencode( (string) ( $eval_grade ?: $check_row->input_grad_grade ) ) .
				'&major_relation=' . urlencode( (string) ( $eval_relation ?: $check_row->input_major_relation ) ) .
				'&time_preference=' . urlencode( (string) ( $eval_time_pref ?: $check_row->input_time_preference ) ) .
				'&decision_status=' . urlencode( (string) ( $eval_decision ?: $check_row->decision_status ) ) .
				'&priority_level=' . urlencode( (string) $eval_priority ) .
				'&suit_types=' . urlencode( (string) $eval_suit_types ) .
				'&current_major=' . urlencode( (string) $check_row->input_major_id ) . 
				'&previous_school=' . urlencode( (string) ( $previous_school ?: $check_row->input_previous_school ) ) .
				'&desired_major=' . urlencode( (string) $check_row->input_desired_major ) . 
				'&birth_year=' . urlencode( (string) ( $eval_grad_year ?: $graduation ) ) . 
				'&verification_required=' . $is_verification_required;
		} else {
			$ref_source = 'eligibility_checker?education_level=' . urlencode( (string) $eval_education ) .
				'&thpt_status=' . urlencode( (string) $eval_thpt_status ) .
				'&grad_year=' . urlencode( (string) $eval_grad_year ) .
				'&grad_month=' . urlencode( (string) $eval_grad_month ) .
				'&months_since_grad=' . urlencode( (string) $eval_months ) .
				'&grad_grade=' . urlencode( (string) $eval_grade ) .
				'&major_relation=' . urlencode( (string) $eval_relation ) .
				'&time_preference=' . urlencode( (string) $eval_time_pref ) .
				'&decision_status=' . urlencode( (string) $eval_decision ) .
				'&priority_level=' . urlencode( (string) $eval_priority ) .
				'&suit_types=' . urlencode( (string) $eval_suit_types );
		}

	// Append additional verification query parameters to referral_source
	if ( ! empty( $previous_school ) && strpos( $ref_source, 'previous_school=' ) === false ) {
		$ref_source .= ( strpos( $ref_source, '?' ) !== false ? '&' : '?' ) . 'previous_school=' . urlencode( $previous_school );
	}
	if ( ! empty( $graduation ) && strpos( $ref_source, 'birth_year=' ) === false ) {
		$ref_source .= ( strpos( $ref_source, '?' ) !== false ? '&' : '?' ) . 'birth_year=' . $graduation;
	}
	if ( ! empty( $degree_link ) ) {
		$ref_source .= ( strpos( $ref_source, '?' ) !== false ? '&' : '?' ) . 'degree_link=' . urlencode( $degree_link );
	}
	if ( ! empty( $transcript_link ) ) {
		$ref_source .= ( strpos( $ref_source, '?' ) !== false ? '&' : '?' ) . 'transcript_link=' . urlencode( $transcript_link );
	}
	if ( ! empty( $page_url ) ) {
		$ref_source .= ( strpos( $ref_source, '?' ) !== false ? '&' : '?' ) . 'page_url=' . urlencode( $page_url );
	}

	// Construct comprehensive notes
	$is_group_reg = ! empty( $_POST['is_group_reg'] );
	$notes = [];
	if ( $is_group_reg ) {
		$notes[] = "Hình thức: ĐĂNG KÝ THEO NHÓM (Học bổng đồng hành)";
	}
	if ( ! empty( $page_url ) ) {
		$notes[] = "Trang đăng ký: " . $page_url;
	}

	// Quy chế 2026–2027 decision breakdown
	if ( ! empty( $eval_decision ) ) {
		$status_name = 'ĐỦ ĐIỀU KIỆN';
		if ( 'warning_urgent' === $eval_decision ) {
			$status_name = 'ĐỦ ĐIỀU KIỆN CÓ LƯU Ý';
		} elseif ( in_array( $eval_decision, [ 'not_eligible', 'not_eligible_2027' ], true ) ) {
			$status_name = 'CHƯA ĐỦ ĐIỀU KIỆN (Quy chế Bộ GD&ĐT)';
		}
		$notes[] = "KẾT QUẢ ĐỐI CHIẾU: " . $status_name;
	}
	if ( ! empty( $eval_education ) ) {
		$notes[] = "Học vấn: " . ltdh_elig_get_education_label( $eval_education );
	}
	if ( ! empty( $eval_thpt_status ) && function_exists( 'ltdh_elig_get_thpt_status_label' ) ) {
		$notes[] = "THPT: " . ltdh_elig_get_thpt_status_label( $eval_thpt_status );
	}
	if ( ! empty( $eval_months ) && $eval_months > 0 ) {
		$notes[] = "Đã tốt nghiệp: " . $eval_months . " tháng";
	}
	if ( ! empty( $eval_grade ) && function_exists( 'ltdh_elig_get_graduation_grade_label' ) ) {
		$notes[] = "Học lực: " . ltdh_elig_get_graduation_grade_label( $eval_grade );
	}
	if ( ! empty( $eval_relation ) && function_exists( 'ltdh_elig_get_major_relation_label' ) ) {
		$notes[] = "Liên quan ngành: " . ltdh_elig_get_major_relation_label( $eval_relation );
	}
	if ( ! empty( $eval_time_pref ) && function_exists( 'ltdh_elig_get_time_preference_label' ) ) {
		$notes[] = "Quỹ thời gian: " . ltdh_elig_get_time_preference_label( $eval_time_pref );
	}
	if ( ! empty( $eval_suit_types ) ) {
		$notes[] = "Hệ đào tạo đề xuất (TT 56): " . $eval_suit_types;
	}

	if ( ! empty( $previous_school ) ) {
		$notes[] = "Trường cũ: " . $previous_school;
	}
	if ( ! empty( $graduation ) ) {
		$notes[] = "Năm tốt nghiệp: " . $graduation;
	}
	if ( ! empty( $degree_link ) ) {
		$notes[] = "Có gửi kèm bằng cấp";
	}
	if ( ! empty( $transcript_link ) ) {
		$notes[] = "Có gửi kèm bảng điểm";
	}
	if ( ! empty( $user_message ) ) {
		$notes[] = "Ghi chú: " . $user_message;
	}
	$full_message = implode( " | ", $notes );

	$lead_args = [
		'name'                 => $name,
		'phone'                => $phone,
		'email'                => $email,
		'program_id'           => $program_id,
		'school_id'            => $school_id,
		'major_id'             => $major_id,
		'training_type'        => $training_val,
		'campus'               => $campus_val,
		'referral_source'      => $ref_source,
		'message'              => $full_message,
		'is_group_reg'         => $is_group_reg,
		'degree_file_path'     => $degree_file_path,
		'degree_link'          => $degree_link,
		'transcript_file_path' => $transcript_file_path,
		'transcript_link'      => $transcript_link,
	];

	if ( function_exists( 'ltdh_insert_lead' ) ) {
		$lead_id = ltdh_insert_lead( $lead_args );
	} else {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . LTDH_TABLE_LEADS, [
			'name'            => $name,
			'phone'           => $phone,
			'email'           => $email,
			'program_id'      => $program_id,
			'school_id'       => $school_id,
			'major_id'        => $major_id,
			'training_type'   => $training_val,
			'campus'          => $campus_val,
			'referral_source' => $ref_source,
			'message'         => $full_message,
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

	$lead_token = hash_hmac( 'sha256', (string) $lead_id, wp_salt( 'auth' ) );
	wp_send_json_success( [ 'lead_id' => $lead_id, 'lead_token' => $lead_token ] );
	} finally {
		if ( function_exists( 'ltdh_cleanup_ephemeral_files' ) ) {
			ltdh_cleanup_ephemeral_files( [ $degree_file_path, $transcript_file_path ] );
		}
	}
}

add_action( 'wp_ajax_ltdh_elig_advanced_verify', 'ltdh_elig_ajax_advanced_verify' );
add_action( 'wp_ajax_nopriv_ltdh_elig_advanced_verify', 'ltdh_elig_ajax_advanced_verify' );

function ltdh_elig_ajax_advanced_verify() {
	check_ajax_referer( 'ltdh_elig_nonce', 'nonce' );

	if ( ltdh_elig_is_rate_limited() ) {
		wp_send_json_error( [ 'message' => 'Yêu cầu quá nhanh. Vui lòng thử lại sau.' ], 429 );
	}

	$lead_id    = intval( $_POST['lead_id'] ?? 0 );
	$lead_token = sanitize_text_field( wp_unslash( $_POST['lead_token'] ?? '' ) );
	if ( ! $lead_id || empty( $lead_token ) ) {
		wp_send_json_error( [ 'message' => 'Yêu cầu không hợp lệ hoặc thiếu mã xác thực phiên.' ] );
	}

	$expected_token = hash_hmac( 'sha256', (string) $lead_id, wp_salt( 'auth' ) );
	if ( ! hash_equals( $expected_token, $lead_token ) ) {
		wp_send_json_error( [ 'message' => 'Bạn không có quyền cập nhật hồ sơ này.' ] );
	}

	// Google reCAPTCHA check if enabled (Fail-Closed)
	if ( function_exists( 'ltdh_is_recaptcha_enabled' ) && ltdh_is_recaptcha_enabled() ) {
		$recaptcha_token = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ?? '' ) );
		if ( ! ltdh_verify_recaptcha( $recaptcha_token, 'elig_verify' ) ) {
			wp_send_json_error( [ 'message' => 'Xác thực reCAPTCHA không thành công.' ], 403 );
		}
	}

	$previous_school  = sanitize_text_field( wp_unslash( $_POST['previous_school'] ?? '' ) );
	$graduation       = intval( $_POST['graduation'] ?? 0 );
	$degree_link      = esc_url_raw( trim( wp_unslash( $_POST['degree_link'] ?? '' ) ) );
	$degree_file_path = '';

	try {
		// Handle ephemeral file upload (never stored in public uploads)
		if ( ! empty( $_FILES['degree_file'] ) && ! empty( $_FILES['degree_file']['name'] ) ) {
			if ( ! function_exists( 'ltdh_handle_ephemeral_document_upload' ) ) {
				wp_send_json_error( [ 'message' => 'Chức năng tải tệp tạm thời không khả dụng.' ] );
			}
			$upload_res = ltdh_handle_ephemeral_document_upload( $_FILES['degree_file'] );
			if ( is_wp_error( $upload_res ) ) {
				wp_send_json_error( [ 'message' => $upload_res->get_error_message() ] );
			}
			$degree_file_path = $upload_res['file'] ?? '';
		}

		global $wpdb;
		
		// Get existing lead details to update
		$lead = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ltdh_leads WHERE id = %d", $lead_id ) );

		if ( $lead ) {
			$ref_source = $lead->referral_source;
			if ( ! empty( $previous_school ) ) {
				$ref_source .= ( strpos( $ref_source, '?' ) !== false ? '&' : '?' ) . 'previous_school=' . urlencode( $previous_school );
			}
			if ( ! empty( $graduation ) ) {
				$ref_source .= ( strpos( $ref_source, '?' ) !== false ? '&' : '?' ) . 'birth_year=' . $graduation;
			}
			if ( ! empty( $degree_link ) ) {
				$ref_source .= ( strpos( $ref_source, '?' ) !== false ? '&' : '?' ) . 'degree_link=' . urlencode( $degree_link );
			}

			$current_msg = $lead->error_message;
			$notes = [];
			if ( ! empty( $previous_school ) ) {
				$notes[] = 'Trường cũ: ' . $previous_school;
			}
			if ( ! empty( $graduation ) ) {
				$notes[] = 'Năm sinh: ' . $graduation;
			}
			if ( ! empty( $degree_link ) ) {
				$notes[] = 'Link bằng cấp: ' . $degree_link;
			} elseif ( ! empty( $degree_file_path ) ) {
				$notes[] = 'Đã gửi kèm tệp bằng cấp xác minh';
			}

			if ( ! empty( $notes ) ) {
				$current_msg = trim( ( $current_msg ? $current_msg . ' | ' : '' ) . implode( ' | ', $notes ) );
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
				'name'             => $lead->name,
				'phone'            => $lead->phone,
				'email'            => $lead->email,
				'program_id'       => $lead->program_id,
				'school_id'        => $lead->school_id,
				'major_id'         => $lead->major_id,
				'referral_source'  => $ref_source,
				'message'          => '📎 Gửi bổ sung hồ sơ xác minh nâng cao. ' . ( ! empty( $previous_school ) ? 'Trường cũ: ' . $previous_school . '. ' : '' ) . ( ! empty( $graduation ) ? 'Năm sinh: ' . $graduation . '. ' : '' ),
				'degree_file_path' => $degree_file_path,
			];
			if ( function_exists( 'ltdh_trigger_telegram_notification' ) ) {
				ltdh_trigger_telegram_notification( $telegram_data );
			}

			if ( function_exists( 'ltdh_trigger_admin_supplement_email' ) ) {
				try {
					ltdh_trigger_admin_supplement_email( (array) $lead, [
						'previous_school' => $previous_school,
						'graduation'      => $graduation,
						'degree_link'     => $degree_link,
					] );
				} catch ( \Throwable $e ) {
					error_log( '[LTDH Email Error] ' . $e->getMessage() );
				}
			}

			wp_send_json_success( [ 'message' => 'Hồ sơ đã được bổ sung thành công.' ] );
		}

		wp_send_json_error( [ 'message' => 'Không tìm thấy lead tương ứng.' ] );
	} finally {
		if ( function_exists( 'ltdh_cleanup_ephemeral_files' ) ) {
			ltdh_cleanup_ephemeral_files( [ $degree_file_path ] );
		}
	}
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

	$results     = ltdh_elig_run_check( $input );
	$share_token = '';
	$check_id    = ltdh_elig_store_check( $input, $results, $share_token );

	return rest_ensure_response( [
		'check_id'       => $check_id,
		'share_token'    => $share_token,
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
 * Fallback client IP resolver if inc/lead-capture.php was not loaded first.
 * Never trusts spoofable HTTP_CLIENT_IP or unverified HTTP_X_FORWARDED_FOR.
 */
if ( ! function_exists( 'ltdh_get_client_ip' ) ) {
	function ltdh_get_client_ip() {
		$remote_addr = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		return filter_var( $remote_addr, FILTER_VALIDATE_IP ) ? $remote_addr : '';
	}
}

/**
 * Check if the current client is rate limited.
 * Allow 10 check requests per 5 minutes for public Internet visitors.
 */
function ltdh_elig_is_rate_limited() {
	if ( PHP_SAPI === 'cli' || defined( 'WP_CLI' ) ) {
		return false;
	}
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return false;
	}
	$ip = ltdh_get_client_ip();
	if ( empty( $ip ) ) {
		return false;
	}
	if ( function_exists( 'ltdh_is_local_dev_site' ) && ltdh_is_local_dev_site() && ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
		return false;
	}
	$key   = 'ltdh_elig_rate_' . md5( $ip );
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
								<td>
									<?php echo $metadata_html; ?>
									<?php if ( ! empty( $lead->sales_partner ) ) : ?>
										<div style="margin-top: 5px;">
											<span style="display: inline-block; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600;">🤝 Ref: <?php echo esc_html( $lead->sales_partner ); ?></span>
										</div>
									<?php endif; ?>
								</td>
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
						<option value="trung-cap" <?php selected( $input_education, 'trung-cap' ); ?>>Trung cấp</option>
						<option value="cao-dang" <?php selected( $input_education, 'cao-dang' ); ?>>Cao đẳng</option>
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
						<option value="tu-xa" <?php selected( $input_training_type, 'tu-xa' ); ?>>Hệ đào tạo từ xa</option>
						<option value="vua-hoc-vua-lam" <?php selected( $input_training_type, 'vua-hoc-vua-lam' ); ?>>Hệ vừa học vừa làm</option>
						<option value="chinh-quy" <?php selected( $input_training_type, 'chinh-quy' ); ?>>Hệ chính quy</option>
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
