<?php
/**
 * Eligibility Checker — Rule Definitions & Decision Tree Engine (2026–2027 Regulations)
 *
 * Fully compliant with Ministry of Education & Training (MOET) circulars:
 * - Circular 06/2026/TT-BGDĐT: College & University Admissions Regulations
 * - Circular 95/2026/TT-BGDĐT: Entry Standards & Cultural Knowledge Requirements for Vocational Education
 * - Circular 56/2026/TT-BGDĐT: Articulation Education & Training Form Delivery Regulations (Article 5)
 * - Circular 52/2026/TT-BGDĐT: Credit Transfer, Course Exemption & Degree Recognition
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ----------------------------------------------------
// 1. TRAINING TYPE COMPATIBILITY MATRIX
// ----------------------------------------------------
function ltdh_elig_get_training_type_compatibility() {
	return [
		'trung-cap'      => [ 'chinh-quy', 'vua-hoc-vua-lam', 'tu-xa', 'dao-tao-tu-xa' ],
		'cao-dang'       => [ 'chinh-quy', 'vua-hoc-vua-lam', 'tu-xa', 'dao-tao-tu-xa' ],
	];
}

// ----------------------------------------------------
// 2. EDUCATION LEVEL HIERARCHY
// ----------------------------------------------------
function ltdh_elig_get_education_hierarchy() {
	return [
		'trung-cap' => 1,
		'cao-dang'  => 2,
	];
}

// ----------------------------------------------------
// 3. BUDGET RANGES
// ----------------------------------------------------
function ltdh_elig_get_budget_ranges() {
	return [
		'duoi-20-trieu'   => [ 'min' => 0,        'max' => 20000000 ],
		'20-30-trieu'     => [ 'min' => 20000000, 'max' => 30000000 ],
		'30-50-trieu'     => [ 'min' => 30000000, 'max' => 50000000 ],
		'tren-50-trieu'   => [ 'min' => 50000000, 'max' => PHP_INT_MAX ],
	];
}

// ----------------------------------------------------
// 4. PROHIBITED DISTANCE LEARNING MAJORS (TT 28/2023 & TT 06/2026)
// ----------------------------------------------------
function ltdh_elig_get_prohibited_distance_learning_categories() {
	return [
		// Khối ngành Sức khỏe (Y Dược)
		'y-khoa', 'y-da-khoa', 'duoc-hoc', 'dieu-duong', 'rang-ham-mat', 'y-hoc-co-truyen', 'ky-thuat-xet-nghiem-y-hoc', 'ky-thuat-hinh-anh-y-hoc',
		// Khối ngành Sư phạm (Đào tạo giáo viên)
		'su-pham-toan', 'su-pham-van', 'su-pham-tieng-anh', 'giao-duc-mam-non', 'giao-duc-tieu-hoc', 'giao-duc-the-chat'
	];
}

// ----------------------------------------------------
// 5. SCHEDULE PREFERENCE MAP
// ----------------------------------------------------
function ltdh_elig_get_schedule_keywords() {
	return [
		'toi'         => [ 'tối', 'toi', 'evening', 'đêm' ],
		'cuoi-tuan'   => [ 'thứ 7', 'thu 7', 'CN', 'cuối tuần', 'weekend', 'chủ nhật' ],
		'online'      => [ 'online', 'trực tuyến', 'e-learning', 'từ xa' ],
		'linh-hoat'   => [ 'linh hoạt', 'linh hoat', 'flexible', 'tự chọn' ],
	];
}

// ----------------------------------------------------
// 6. SCORING WEIGHTS
// ----------------------------------------------------
function ltdh_elig_get_scoring_weights() {
	return [
		'major_match'        => 30,  // Max 30 points
		'major_related'      => 15,  // If related major
		'graduation_recent'  => 10,  // Max 10 points
		'budget_match'       => 20,  // Max 20 points
		'campus_match'       => 10,  // Max 10 points
		'schedule_match'     => 5,   // Max 5 points
	];
}

// ----------------------------------------------------
// 7. MAJOR RELATIONSHIPS
// ----------------------------------------------------
function ltdh_elig_get_major_relationships() {
	return [
		'ke-toan'               => [ 'kiem-toan', 'tai-chinh', 'ngan-hang', 'tai-chinh-ngan-hang' ],
		'quan-tri-kinh-doanh'   => [ 'marketing', 'kinh-doanh-quoc-te', 'thuong-mai', 'quan-tri-nhan-luc', 'logistics' ],
		'cong-nghe-thong-tin'   => [ 'ky-thuat-phan-mem', 'an-toan-thong-tin', 'he-thong-thong-tin', 'khoa-hoc-may-tinh' ],
		'ngon-ngu-anh'          => [ 'ngon-ngu-nhat', 'ngon-ngu-trung', 'ngon-ngu-han', 'dich-thuat' ],
		'marketing'             => [ 'quan-tri-kinh-doanh', 'quang-cao', 'pr', 'truyen-thong', 'thuong-mai-dien-tu' ],
		'kinh-doanh-thuong-mai' => [ 'quan-tri-kinh-doanh', 'marketing', 'logistics', 'thuong-mai-dien-tu' ],
		'luat'                  => [ 'luat-kinh-te', 'quan-ly-nha-nuoc' ],
		'luat-kinh-te'          => [ 'luat', 'quan-tri-kinh-doanh' ],
		'ky-thuat-dien'         => [ 'ky-thuat-dien-tu', 'tu-dong-hoa', 'co-dien-tu' ],
		'xay-dung'              => [ 'ky-thuat-xay-dung', 'quan-ly-xay-dung', 'kien-truc' ],
	];
}

function ltdh_elig_are_majors_related( $major_id_1, $major_id_2 ) {
	if ( ! $major_id_1 || ! $major_id_2 ) {
		return false;
	}
	if ( (int) $major_id_1 === (int) $major_id_2 ) {
		return true;
	}

	if ( function_exists( 'get_field' ) ) {
		$related_posts = get_field( 'major_related', $major_id_1 );
		if ( ! empty( $related_posts ) ) {
			foreach ( $related_posts as $p ) {
				$related_id = is_object( $p ) ? $p->ID : ( is_array( $p ) ? $p['ID'] : intval( $p ) );
				if ( $related_id === (int) $major_id_2 ) {
					return true;
				}
			}
		}

		$related_posts_2 = get_field( 'major_related', $major_id_2 );
		if ( ! empty( $related_posts_2 ) ) {
			foreach ( $related_posts_2 as $p ) {
				$related_id = is_object( $p ) ? $p->ID : ( is_array( $p ) ? $p['ID'] : intval( $p ) );
				if ( $related_id === (int) $major_id_1 ) {
					return true;
				}
			}
		}
	}

	$slug_1 = get_post_field( 'post_name', $major_id_1 );
	$slug_2 = get_post_field( 'post_name', $major_id_2 );
	if ( $slug_1 && $slug_2 ) {
		$relationships = ltdh_elig_get_major_relationships();
		if ( isset( $relationships[ $slug_1 ] ) && in_array( $slug_2, $relationships[ $slug_1 ], true ) ) {
			return true;
		}
		if ( isset( $relationships[ $slug_2 ] ) && in_array( $slug_1, $relationships[ $slug_2 ], true ) ) {
			return true;
		}
	}

	return false;
}

// ----------------------------------------------------
// 8. MONTHS CALCULATION HELPER
// ----------------------------------------------------
/**
 * Calculate months elapsed from graduation date to reference date (current date or admission deadline).
 *
 * @param int|null $grad_month Month (1-12).
 * @param int|null $grad_year  Year (e.g. 2024).
 * @param int|null $ref_month  Reference month (default 11 for late 2026).
 * @param int|null $ref_year   Reference year (default 2026).
 * @return int Number of months elapsed.
 */
function ltdh_elig_calculate_months_since_graduation( $grad_month = null, $grad_year = null, $ref_month = null, $ref_year = null ) {
	if ( empty( $grad_year ) ) {
		return 0;
	}
	$g_year  = intval( $grad_year );
	$g_month = ! empty( $grad_month ) ? max( 1, min( 12, intval( $grad_month ) ) ) : 6; // default mid-year (June)

	if ( null === $ref_year ) {
		$ref_year = intval( current_time( 'Y' ) );
	}
	if ( null === $ref_month ) {
		$ref_month = intval( current_time( 'n' ) );
	}

	$months = ( $ref_year - $g_year ) * 12 + ( $ref_month - $g_month );
	return max( 0, $months );
}

// ----------------------------------------------------
// 8.1 PAGE CONTEXT RESOLUTION & CONFLICT RESOLVER
// ----------------------------------------------------
/**
 * Automatically resolve page context for Condition Checker Widget.
 *
 * @param int $program_id Optional program ID override.
 * @return array Page context data.
 */
function ltdh_elig_get_page_context( int $program_id = 0 ): array {
	$program_id = $program_id > 0 ? $program_id : ( function_exists( 'is_singular' ) && is_singular( 'program' ) ? (int) get_the_ID() : 0 );

	$context = [
		'is_single_program'  => false,
		'program_id'         => 0,
		'program_title'      => '',
		'program_slug'       => '',
		'program_permalink'  => '',
		'school_id'          => 0,
		'school_title'       => '',
		'school_slug'        => '',
		'school_code'        => '',
		'major_id'           => 0,
		'major_title'        => '',
		'major_slug'         => '',
		'training_type_slug' => '',
		'training_type_name' => '',
		'sibling_programs'   => [],
		'page_url'           => function_exists( 'home_url' ) && isset( $_SERVER['REQUEST_URI'] ) ? home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '',
	];

	if ( $program_id > 0 ) {
		$context['is_single_program'] = true;
		$context['program_id']        = $program_id;
		$context['program_title']     = function_exists( 'get_the_title' ) ? html_entity_decode( get_the_title( $program_id ), ENT_QUOTES, 'UTF-8' ) : '';
		$context['program_slug']      = function_exists( 'get_post_field' ) ? get_post_field( 'post_name', $program_id ) : '';
		$context['program_permalink'] = function_exists( 'get_permalink' ) ? get_permalink( $program_id ) : '';

		// School relationship
		$school_id = function_exists( 'get_field' ) ? (int) get_field( 'school_relationship', $program_id ) : 0;
		if ( ! $school_id && function_exists( 'get_post_meta' ) ) {
			$school_id = (int) get_post_meta( $program_id, 'school_relationship', true );
		}
		if ( $school_id > 0 ) {
			$context['school_id']    = $school_id;
			$context['school_title'] = function_exists( 'get_the_title' ) ? html_entity_decode( get_the_title( $school_id ), ENT_QUOTES, 'UTF-8' ) : '';
			$context['school_slug']  = function_exists( 'get_post_field' ) ? get_post_field( 'post_name', $school_id ) : '';
			$context['school_code']  = function_exists( 'get_post_meta' ) ? (string) ( get_post_meta( $school_id, 'school_code', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'school_code', $school_id ) : '' ) ) : '';
		}

		// Major relationship
		$major_id = function_exists( 'get_field' ) ? (int) get_field( 'major_relationship', $program_id ) : 0;
		if ( ! $major_id && function_exists( 'get_post_meta' ) ) {
			$major_id = (int) get_post_meta( $program_id, 'major_relationship', true );
		}
		if ( $major_id > 0 ) {
			$context['major_id']    = $major_id;
			$context['major_title'] = function_exists( 'get_the_title' ) ? html_entity_decode( get_the_title( $major_id ), ENT_QUOTES, 'UTF-8' ) : '';
			$context['major_slug']  = function_exists( 'get_post_field' ) ? get_post_field( 'post_name', $major_id ) : '';
		}

		// Training Type taxonomy
		if ( function_exists( 'wp_get_post_terms' ) ) {
			$tt_terms = wp_get_post_terms( $program_id, 'training_type' );
			if ( ! empty( $tt_terms ) && ! is_wp_error( $tt_terms ) ) {
				$context['training_type_slug'] = $tt_terms[0]->slug;
				$context['training_type_name'] = $tt_terms[0]->name;
			}
		}

		// Sibling programs of the same school & major
		if ( function_exists( 'ltdh_get_sibling_training_programs' ) ) {
			$context['sibling_programs'] = ltdh_get_sibling_training_programs( $program_id );
		}
	} elseif ( function_exists( 'is_singular' ) && is_singular( 'school' ) ) {
		$school_id = (int) get_the_ID();
		$context['school_id']    = $school_id;
		$context['school_title'] = function_exists( 'get_the_title' ) ? html_entity_decode( get_the_title( $school_id ), ENT_QUOTES, 'UTF-8' ) : '';
		$context['school_slug']  = function_exists( 'get_post_field' ) ? get_post_field( 'post_name', $school_id ) : '';
		$context['school_code']  = function_exists( 'get_post_meta' ) ? (string) ( get_post_meta( $school_id, 'school_code', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'school_code', $school_id ) : '' ) ) : '';
	} elseif ( function_exists( 'is_singular' ) && is_singular( 'major' ) ) {
		$major_id = (int) get_the_ID();
		$context['major_id']    = $major_id;
		$context['major_title'] = function_exists( 'get_the_title' ) ? html_entity_decode( get_the_title( $major_id ), ENT_QUOTES, 'UTF-8' ) : '';
		$context['major_slug']  = function_exists( 'get_post_field' ) ? get_post_field( 'post_name', $major_id ) : '';
	}

	return $context;
}

/**
 * Apply context-aware resolution and conflict handling to the evaluated decision.
 *
 * @param array $decision Evaluated decision array.
 * @param array $input    Input parameters including optional 'context'.
 * @return array Final decision array with context conflicts attached.
 */
function ltdh_elig_finalize_decision( array $decision, array $input ): array {
	$context = $input['context'] ?? ( $input['_page_context'] ?? [] );
	if ( empty( $context ) && ! empty( $input['context_program_id'] ) ) {
		$context = ltdh_elig_get_page_context( (int) $input['context_program_id'] );
	}
	$decision['context_conflict'] = null;

	if ( ! empty( $context ) ) {
		$ctx_school_slug  = strtolower( (string) ( $context['school_slug'] ?? '' ) );
		$ctx_school_code  = strtoupper( (string) ( $context['school_code'] ?? '' ) );
		$ctx_school_title = (string) ( $context['school_title'] ?? '' );
		$ctx_system_slug  = strtolower( (string) ( $context['training_type_slug'] ?? '' ) );
		$ctx_siblings     = (array) ( $context['sibling_programs'] ?? [] );

		$time_preference   = $input['study_time_preference'] ?? 'online-100';
		$thpt_status       = $input['thpt_status'] ?? '';
		$education         = $input['education'] ?? '';
		$months_since_grad = $decision['months_since_grad'] ?? 0;
		$is_arts_specialty = ! empty( $decision['is_arts_specialty'] );

		$is_utc_school = (
			false !== strpos( $ctx_school_slug, 'utc' ) ||
			false !== strpos( $ctx_school_slug, 'giao-thong-van-tai' ) ||
			in_array( $ctx_school_code, [ 'UTC', 'GHA' ], true ) ||
			( function_exists( 'mb_strtolower' ) && false !== strpos( mb_strtolower( $ctx_school_title ), 'giao thông vận tải' ) )
		);

		// Context Conflict 1: Trang Chính quy nhưng học viên chọn học ngoài giờ / Online 100% (Điều 5 TT 56/2026)
		if ( 'chinh-quy' === $ctx_system_slug && ( 'ngoai-gio-cuoi-tuan' === $time_preference || 'online-100' === $time_preference ) ) {
			$suggested_link = '';
			$suggested_name = '';
			foreach ( $ctx_siblings as $sib ) {
				$sib_slug = $sib['type_slug'] ?? '';
				if ( false !== strpos( $sib_slug, 'tu-xa' ) || false !== strpos( $sib_slug, 'vua-hoc-vua-lam' ) ) {
					$suggested_link = $sib['permalink'] ?? '';
					$suggested_name = $sib['type_name'] ?? ( false !== strpos( $sib_slug, 'tu-xa' ) ? 'Đào tạo từ xa' : 'Vừa làm vừa học' );
					if ( 'online-100' === $time_preference && false !== strpos( $sib_slug, 'tu-xa' ) ) {
						break;
					}
				}
			}

			$decision['context_conflict'] = [
				'type'           => 'schedule_mismatch',
				'severity'       => 'warning',
				'title'          => 'Lưu ý về hình thức đào tạo của trang bạn đang xem',
				'message'        => 'Chương trình bạn đang xem thuộc <b>Hệ Chính quy</b>. Theo Điều 5 Thông tư 56/2026/TT-BGDĐT, hệ Chính quy liên thông bắt buộc học trực tiếp tại trường trong giờ hành chính (>50% khối lượng chương trình), không phù hợp với quỹ thời gian ngoài giờ/online của bạn.',
				'action_text'    => $suggested_name ? "Chuyển sang xem hệ {$suggested_name} cùng ngành" : 'Xem các hệ đào tạo linh hoạt',
				'action_url'     => $suggested_link ?: '#training-modes',
				'recommendation' => 'Bạn hoàn toàn đủ điều kiện học đại học nhưng nên chuyển sang hệ Vừa làm vừa học hoặc Đào tạo từ xa để phù hợp với thời gian đi làm.',
			];
		}
	}

	return $decision;
}

// ----------------------------------------------------
// 9. CORE DECISION TREE ENGINE (2026–2027 Regulations)
// ----------------------------------------------------
/**
 * Evaluates admission eligibility across 2 key timelines:
 * - Urgent year-end 2026 intake before 15/12/2026
 * - New academic cycle from 01/01/2027 (Circulars 06, 95, 56, 52/2026/TT-BGDĐT)
 *
 * Returns 3 clear decision states:
 * 1. 'eligible' (Xanh lá / Emerald): Đủ điều kiện dự tuyển.
 * 2. 'warning_urgent' (Vàng/Cam / Amber-Orange): Đủ điều kiện có lưu ý / Cần chốt gấp trước 15/12/2026.
 * 3. 'not_eligible_2027' (Đỏ / Rose-Red): Chưa đủ điều kiện theo quy chế 2027 + Giải pháp khắc phục.
 *
 * @param array $input User input data.
 * @return array Decision tree assessment result.
 */
function ltdh_elig_evaluate_decision_tree( array $input ): array {
	$education       = sanitize_text_field( $input['education'] ?? '' );
	$thpt_status     = sanitize_text_field( $input['thpt_status'] ?? '' );
	$grad_month      = ! empty( $input['grad_month'] ) ? intval( $input['grad_month'] ) : null;
	$grad_year       = ! empty( $input['grad_year'] ) ? intval( $input['grad_year'] ) : ( ! empty( $input['graduation'] ) ? intval( $input['graduation'] ) : null );
	$grad_grade      = sanitize_text_field( $input['graduation_grade'] ?? 'kha' );
	$major_relation  = sanitize_text_field( $input['major_relation'] ?? 'dung-nganh' );
	$time_preference = sanitize_text_field( $input['study_time_preference'] ?? 'online-100' );
	$desired_major   = intval( $input['desired_major'] ?? 0 );

	// Calculate elapsed months since graduation
	$months_since_grad = ltdh_elig_calculate_months_since_graduation( $grad_month, $grad_year );

	// Determine if desired major is arts/music (exempted from 24-month rule under specific guidelines)
	$is_arts_specialty = false;
	if ( $desired_major > 0 ) {
		$major_slug = get_post_field( 'post_name', $desired_major );
		if ( in_array( $major_slug, [ 'am-nhac', 'thanh-nhac', 'bieu-dien-nhac-cu', 'nghe-thuat-bieu-dien', 'hoi-hoa', 'dien-vien' ], true ) ) {
			$is_arts_specialty = true;
		}
	}

	// ----------------------------------------------------
	// RULE 4: Filter Suitable Training Types based on Study Time Preference (TT 56/2026)
	// ----------------------------------------------------
	$suitable_training_types = [];
	$time_filter_note        = '';
	$exclude_chinh_quy       = false;

	if ( 'ngoai-gio-cuoi-tuan' === $time_preference || 'online-100' === $time_preference ) {
		$exclude_chinh_quy       = true;
		$suitable_training_types = [ 'vua-hoc-vua-lam', 'tu-xa' ];
		$time_filter_note        = 'Theo Điều 5 Thông tư 56/2026/TT-BGDĐT, hệ Chính quy liên thông bắt buộc học trực tiếp tại trường trong giờ hành chính trên 50% khối lượng chương trình. Do bạn có quỹ thời gian ngoài giờ/online, hệ thống tự động loại bỏ hệ Chính quy và gợi ý tối ưu hệ Vừa làm vừa học hoặc Đào tạo từ xa.';
	} else {
		// 'gio-hanh-chinh'
		$suitable_training_types = [ 'chinh-quy', 'vua-hoc-vua-lam', 'tu-xa' ];
		$time_filter_note        = 'Quỹ thời gian của bạn phù hợp với cả 3 hệ: Chính quy tập trung ban ngày tại trường, Vừa làm vừa học (buổi tối/cuối tuần) và Đào tạo từ xa (100% online).';
	}

	$credit_transfer_note = 'Theo Thông tư 52/2026/TT-BGDĐT & Thông tư 56/2026/TT-BGDĐT, tất cả các hệ đào tạo (Chính quy, Vừa học vừa làm, Đào tạo từ xa) đều học chung khung chương trình chuẩn, giá trị văn bằng và đơn giá học phí tương đương; sinh viên được Hội đồng nhà trường xét miễn trừ tín chỉ cá nhân hóa theo bảng điểm gốc.';

	$legal_basis = [
		'Thông tư 06/2026/TT-BGDĐT (Quy chế tuyển sinh đại học)',
		'Thông tư 95/2026/TT-BGDĐT (Chuẩn đầu vào khối lượng văn hóa THPT & Trung học nghề)',
		'Thông tư 56/2026/TT-BGDĐT (Quy chế đào tạo liên thông & hình thức đào tạo)',
		'Thông tư 52/2026/TT-BGDĐT (Công nhận văn bằng & miễn trừ tín chỉ cá nhân hóa)',
	];

	// Initialize result blueprint
	$decision = [
		'education'               => $education,
		'thpt_status'             => $thpt_status,
		'grad_month'              => $grad_month,
		'grad_year'               => $grad_year,
		'months_since_grad'       => $months_since_grad,
		'graduation_grade'        => $grad_grade,
		'major_relation'          => $major_relation,
		'study_time_preference'   => $time_preference,
		'suitable_training_types' => $suitable_training_types,
		'exclude_chinh_quy'       => $exclude_chinh_quy,
		'time_filter_note'        => $time_filter_note,
		'credit_transfer_note'    => $credit_transfer_note,
		'legal_basis'             => $legal_basis,
		'decision_status'         => 'eligible', // 'eligible' | 'warning_urgent' | 'not_eligible_2027'
		'badge_color'             => 'emerald',
		'badge_text'              => 'Đủ điều kiện xét tuyển',
		'status_label'            => 'Đủ điều kiện',
		'decision_title'          => 'Hồ sơ đủ điều kiện tuyển sinh liên thông',
		'decision_summary'        => '',
		'urgent_deadline'         => null,
		'remedy_solution'         => '',
		'priority_level'          => 'standard', // 'urgent_dec2026' | 'standard' | 'remedy_needed'
		'is_2027_blocked'         => false,
		'urgent_2026_available'   => false,
	];

	// ----------------------------------------------------
	// RULE 1: Nhóm CHƯA có bằng THPT & CHƯA có Giấy hoàn thành văn hóa THPT
	// ----------------------------------------------------
	if ( ( 'trung-cap' === $education || 'cao-dang' === $education ) && 'chua-co-giay-van-hoa-thpt' === $thpt_status ) {
		if ( 'trung-cap' === $education ) {
			// TRUNG CẤP CHƯA CÓ BẰNG THPT & CHƯA CÓ GIẤY HOÀN THÀNH VĂN HÓA THPT
			$decision['decision_status']       = 'not_eligible';
			$decision['badge_color']           = 'rose';
			$decision['badge_text']            = 'Chưa đủ điều kiện xét tuyển';
			$decision['status_label']          = 'Chưa đủ điều kiện';
			$decision['priority_level']        = 'standard';
			$decision['urgent_deadline']       = null;
			$decision['is_2027_blocked']       = true;
			$decision['urgent_2026_available'] = false;
			$decision['decision_title']        = 'Chưa đủ điều kiện xét tuyển liên thông Đại học';
			$decision['decision_summary']      = 'Theo quy định chuẩn đầu vào của Bộ GD&ĐT (Thông tư 95/2026/TT-BGDĐT), người tốt nghiệp Trung cấp bắt buộc phải có bằng tốt nghiệp THPT hoặc Giấy chứng nhận hoàn thành đủ khối lượng kiến thức văn hóa THPT mới đủ điều kiện đăng ký xét tuyển liên thông lên đại học.';
			$decision['remedy_solution']       = '';
			return ltdh_elig_finalize_decision( $decision, $input );
		}

		if ( 'cao-dang' === $education ) {
			// CAO ĐẲNG CHƯA CÓ BẰNG THPT & CHƯA CÓ GIẤY HOÀN THÀNH VĂN HÓA THPT
			if ( $months_since_grad >= 24 || $is_arts_specialty ) {
				// ĐỦ >= 24 THÁNG (hoặc ngành Nghệ thuật/Âm nhạc) -> ĐỦ ĐIỀU KIỆN
				$decision['decision_status']  = 'eligible';
				$decision['badge_color']      = 'emerald';
				$decision['badge_text']       = 'Đủ điều kiện xét tuyển (Đã tốt nghiệp ≥ 24 tháng)';
				$decision['status_label']     = 'Đủ điều kiện';
				$decision['priority_level']   = 'standard';
				$decision['urgent_deadline']  = null;
				$decision['decision_title']   = 'Đủ điều kiện dự tuyển liên thông Đại học';
				$decision['decision_summary'] = 'Bằng Cao đẳng của bạn đã được cấp ' . ( $months_since_grad > 0 ? ( 'khoảng ' . $months_since_grad . ' tháng' ) : 'trên 24 tháng' ) . ' (đạt mốc ≥ 24 tháng theo quy chế tuyển sinh của Bộ GD&ĐT)' . ( $is_arts_specialty ? ' hoặc thuộc nhóm ngành Âm nhạc/Nghệ thuật' : '' ) . '. Bạn đủ điều kiện đăng ký xét tuyển liên thông đại học bình thường.';
				$decision['remedy_solution']  = '';
				return ltdh_elig_finalize_decision( $decision, $input );
			} else {
				// CHƯA ĐỦ 24 THÁNG
				$decision['decision_status']       = 'not_eligible';
				$decision['badge_color']           = 'rose';
				$decision['badge_text']            = 'Chưa đủ mốc 24 tháng theo quy chế';
				$decision['status_label']          = 'Chưa đủ điều kiện';
				$decision['priority_level']        = 'standard';
				$decision['urgent_deadline']       = null;
				$decision['is_2027_blocked']       = true;
				$decision['urgent_2026_available'] = false;
				$decision['decision_title']        = 'Chưa đủ điều kiện xét tuyển (Chưa đủ mốc 24 tháng)';
				$decision['decision_summary']      = 'Theo quy chế tuyển sinh của Bộ GD&ĐT, người tốt nghiệp Cao đẳng nhưng chưa có bằng tốt nghiệp THPT bắt buộc phải có thời gian từ khi công nhận tốt nghiệp đến khi dự tuyển từ đủ 24 tháng trở lên (bằng của bạn hiện mới cấp khoảng ' . $months_since_grad . ' tháng). Bạn cần chờ đủ thời gian 24 tháng hoặc bổ sung Giấy chứng nhận hoàn thành đủ khối lượng kiến thức văn hóa THPT để đủ điều kiện xét tuyển.';
				$decision['remedy_solution']       = '';
				return ltdh_elig_finalize_decision( $decision, $input );
			}
		}
	}

	// ----------------------------------------------------
	// RULE 2: Nhóm tốt nghiệp Trung học nghề (THN) (TT 95/2026 & 52/2026)
	// ----------------------------------------------------
	if ( 'trung-hoc-nghe' === $education ) {
		$is_same_major  = ( 'dung-nganh' === $major_relation );
		$is_close_major = ( 'nganh-gan' === $major_relation );
		$is_good_grade  = in_array( $grad_grade, [ 'xuat-sac-gioi', 'kha' ], true );

		if ( $is_same_major && $is_good_grade ) {
			// ĐỦ ĐIỀU KIỆN: Đúng ngành + Khá/Giỏi
			$decision['decision_status']  = 'eligible';
			$decision['badge_color']      = 'emerald';
			$decision['badge_text']       = 'Đủ điều kiện liên thông THN → ĐH (Thông tư 95/2026)';
			$decision['status_label']     = 'Đủ điều kiện';
			$decision['priority_level']   = 'standard';
			$decision['decision_title']   = 'Đủ điều kiện liên thông trực tiếp từ Trung học nghề lên Đại học';
			$decision['decision_summary'] = 'Từ 01/01/2027, căn cứ Thông tư 95/2026/TT-BGDĐT và Thông tư 52/2026/TT-BGDĐT, người tốt nghiệp Trung học nghề (THN) hệ 3 năm ĐỦ ĐIỀU KIỆN dự tuyển liên thông trực tiếp lên Đại học khi đăng ký cùng nhóm ngành và có xếp loại tốt nghiệp từ Khá trở lên. Bảng điểm THN sẽ được thẩm định để miễn trừ các học phần cơ sở theo quy định.';
			return ltdh_elig_finalize_decision( $decision, $input );
		}

		if ( $is_close_major && $is_good_grade ) {
			// NGÀNH GẦN + KHÁ/GIỎI: Đủ điều kiện nhưng học bổ sung
			$decision['decision_status']  = 'warning_urgent';
			$decision['badge_color']      = 'amber';
			$decision['badge_text']       = 'Đủ điều kiện ngành gần • Cần học bổ sung kiến thức';
			$decision['status_label']     = 'Đủ điều kiện có lưu ý';
			$decision['priority_level']   = 'standard';
			$decision['decision_title']   = 'Đủ điều kiện dự tuyển ngành gần (Học bổ sung kiến thức)';
			$decision['decision_summary'] = 'Hồ sơ Trung học nghề của bạn có xếp loại tốt nghiệp từ Khá trở lên nên đáp ứng ngưỡng năng lực của Thông tư 95/2026/TT-BGDĐT. Do đăng ký ngành gần, nhà trường sẽ yêu cầu học bổ sung từ 2–4 học phần kiến thức cơ sở ngành trước khi vào học chương trình chính khóa.';
			$decision['remedy_solution']  = 'Bạn có thể đăng ký thẩm định bảng điểm ngay để nhà trường xếp lịch học bổ sung trực tuyến song song, không làm kéo dài thời gian nhận bằng đại học.';
			return ltdh_elig_finalize_decision( $decision, $input );
		}

		// TRÁI NGÀNH hoặc HỌC LỰC TRUNG BÌNH -> KHÔNG ĐỦ ĐIỀU KIỆN 2027
		$decision['decision_status']  = 'not_eligible_2027';
		$decision['badge_color']      = 'rose';
		$decision['badge_text']       = 'Chưa đạt chuẩn đầu vào THN (Thông tư 95/2026)';
		$decision['status_label']     = 'Chưa đủ điều kiện';
		$decision['priority_level']   = 'remedy_needed';
		$decision['is_2027_blocked']  = true;
		$decision['decision_title']   = 'Chưa đạt ngưỡng đầu vào chuẩn liên thông Đại học từ Trung học nghề';

		$reason_details = [];
		if ( 'trai-nganh' === $major_relation ) {
			$reason_details[] = 'đăng ký trái nhóm ngành';
		}
		if ( ! $is_good_grade ) {
			$reason_details[] = 'xếp loại tốt nghiệp ở mức Trung bình / Trung bình khá';
		}
		$reasons_str = implode( ' và ', $reason_details );

		$decision['decision_summary'] = 'Theo quy định tại Thông tư 95/2026/TT-BGDĐT, thí sinh tốt nghiệp Trung học nghề (THN) muốn liên thông trực tiếp lên Đại học từ năm 2027 bắt buộc phải đáp ứng đồng thời 2 điều kiện: Cùng nhóm ngành đào tạo VÀ Xếp loại tốt nghiệp từ Khá trở lên. Hồ sơ của bạn hiện ' . $reasons_str . ' nên chưa đạt ngưỡng tuyển sinh chuẩn.';
		$decision['remedy_solution']  = 'Giải pháp khắc phục tối ưu:<br>1. <b>Chọn học đúng nhóm ngành:</b> Chuyển nguyện vọng sang ngành đúng với chuyên môn Trung học nghề để đủ điều kiện xét tuyển thẳng.<br>2. <b>Lộ trình qua Cao đẳng:</b> Học liên thông lấy bằng Cao đẳng (thời gian khoảng 1 năm) trước khi liên thông Đại học để được mở rộng ngành nghề và không bị giới hạn xếp loại.<br>3. <b>Bổ sung kiến thức văn hóa THPT:</b> Học lấy Giấy chứng nhận hoàn thành đủ khối lượng văn hóa THPT để xét tuyển diện thí sinh tự do.';
		return ltdh_elig_finalize_decision( $decision, $input );
	}

	// ----------------------------------------------------
	// RULE 3: Nhóm ĐÃ CÓ bằng THPT (hoặc Giấy hoàn thành văn hóa THPT) + Trung cấp / Cao đẳng
	// ----------------------------------------------------
	if ( in_array( $thpt_status, [ 'co-bang-thpt', 'co-giay-van-hoa-thpt' ], true ) ) {
		$is_unrelated_or_low_grade = ( 'trai-nganh' === $major_relation || 'trung-binh' === $grad_grade );

		if ( $is_unrelated_or_low_grade ) {
			// ĐỦ ĐIỀU KIỆN CÓ LƯU Ý
			$decision['decision_status']  = 'warning_urgent';
			$decision['badge_color']      = 'amber';
			$decision['badge_text']       = 'Đủ điều kiện • Cần kiểm tra điểm sàn & học bổ sung';
			$decision['status_label']     = 'Đủ điều kiện có lưu ý';
			$decision['priority_level']   = 'standard';
			$decision['decision_title']   = 'Đủ điều kiện dự tuyển liên thông (Có lưu ý xét tuyển)';

			$reasons = [];
			if ( 'trai-nganh' === $major_relation ) {
				$reasons[] = 'đăng ký học khác nhóm ngành (trái ngành)';
			}
			if ( 'trung-binh' === $grad_grade ) {
				$reasons[] = 'bằng xếp loại Trung bình / Trung bình khá';
			}
			$reasons_text = implode( ' và ', $reasons );

			$decision['decision_summary'] = 'Về mặt pháp lý văn bằng, bạn HOÀN TOÀN ĐỦ ĐIỀU KIỆN đăng ký xét tuyển liên thông đại học. Tuy nhiên do hồ sơ của bạn ' . $reasons_text . ', cần kiểm tra ngưỡng điểm sàn đầu vào của từng trường và có thể phải học bổ sung kiến thức nền tảng (theo Thông tư 95/2026 & Thông tư 52/2026/TT-BGDĐT).';
			$decision['remedy_solution']  = 'Gợi ý: Hãy gửi kèm bảng điểm gốc khi đăng ký tư vấn để Ban tuyển sinh thẩm định số môn cần học bổ sung và hướng dẫn chọn trường có chính sách miễn giảm môn học ưu đãi nhất.';
			return ltdh_elig_finalize_decision( $decision, $input );
		}

		// ĐỦ ĐIỀU KIỆN HOÀN TOÀN (Đúng ngành/Ngành gần + Khá/Giỏi)
		$decision['decision_status']  = 'eligible';
		$decision['badge_color']      = 'emerald';
		$decision['badge_text']       = 'Đủ điều kiện xét tuyển ưu tiên 100%';
		$decision['status_label']     = 'Đủ điều kiện';
		$decision['priority_level']   = 'standard';
		$decision['decision_title']   = 'Hồ sơ đáp ứng hoàn hảo tiêu chuẩn tuyển sinh liên thông';
		$decision['decision_summary'] = 'Hồ sơ học vấn của bạn hoàn toàn đủ điều kiện dự tuyển vào tất cả các chương trình liên thông đại học theo quy chế mới của Bộ GD&ĐT. Với trình độ hiện tại cùng bằng THPT/chứng nhận văn hóa và học lực tốt, bạn thuộc diện được ưu tiên xét tuyển hồ sơ sớm và tối đa hóa số tín chỉ được xét miễn giảm.';
		return ltdh_elig_finalize_decision( $decision, $input );
	}

	// Fallback general check
	$decision['decision_summary'] = 'Hệ thống đã ghi nhận hồ sơ của bạn. Vui lòng đối chiếu thêm thông tin văn bằng và bảng điểm cụ thể với ban tư vấn tuyển sinh.';
	return ltdh_elig_finalize_decision( $decision, $input );
}

// ----------------------------------------------------
// 10. LABELS FOR FORM OPTIONS
// ----------------------------------------------------
function ltdh_elig_get_thpt_status_label( string $status ): string {
	$labels = [
		'co-bang-thpt'              => 'Đã có bằng tốt nghiệp THPT',
		'co-giay-van-hoa-thpt'       => 'Chưa có bằng THPT, nhưng đã có Giấy chứng nhận hoàn thành đủ khối lượng kiến thức văn hóa THPT',
		'chua-co-giay-van-hoa-thpt' => 'Chưa có bằng THPT và chưa có Giấy chứng nhận hoàn thành kiến thức văn hóa THPT',
	];
	return $labels[ $status ] ?? $status;
}

function ltdh_elig_get_graduation_grade_label( string $grade ): string {
	$labels = [
		'xuat-sac-gioi' => 'Xuất sắc / Giỏi',
		'kha'           => 'Khá',
		'trung-binh'    => 'Trung bình khá / Trung bình',
	];
	return $labels[ $grade ] ?? $grade;
}

function ltdh_elig_get_major_relation_label( string $relation ): string {
	$labels = [
		'dung-nganh' => 'Đúng ngành / Cùng nhóm ngành',
		'nganh-gan'  => 'Ngành gần',
		'trai-nganh' => 'Khác nhóm ngành (Trái ngành)',
	];
	return $labels[ $relation ] ?? $relation;
}

function ltdh_elig_get_time_preference_label( string $pref ): string {
	$labels = [
		'gio-hanh-chinh'      => 'Học tập trung giờ hành chính ban ngày tại trường',
		'ngoai-gio-cuoi-tuan' => 'Chỉ học được ngoài giờ hành chính / Cuối tuần',
		'online-100'          => 'Học trực tuyến (Online 100% qua LMS, thi tập trung)',
	];
	return $labels[ $pref ] ?? $pref;
}
