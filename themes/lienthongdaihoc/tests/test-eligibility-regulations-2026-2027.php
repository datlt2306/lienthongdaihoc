<?php
/**
 * Standalone Unit Test Suite for Eligibility Checker Engine (2026–2027 Regulations)
 * Testing Circulars: TT 06/2026, TT 95/2026, TT 56/2026, TT 52/2026/TT-BGDĐT
 *
 * Edge cases covered:
 * 1. Edge Case 1: Trung cấp không THPT & chưa có văn hóa THPT (Khẩn cấp trước 15/12/2026, đóng từ 2027)
 * 2. Edge Case 2: Cao đẳng không THPT, tốt nghiệp < 24 tháng (Chưa đủ 24 tháng, chốt gấp trước 15/12/2026)
 * 3. Edge Case 3: Cao đẳng không THPT, tốt nghiệp >= 24 tháng (Đủ điều kiện xét tuyển bình thường cả 2026 & 2027)
 * 4. Edge Case 4: Trung học nghề (THN) (Đúng ngành + Khá -> Đạt; Trái ngành / Trung bình -> Chưa đạt ngưỡng chuẩn)
 * 5. Edge Case 5: Cao đẳng có THPT: Quỹ thời gian ban ngày vs Ngoài giờ/Online (TT 56 loại bỏ Chính quy khi ngoài giờ)
 * 6. Edge Case 6: Input Validation & Graduation Month/Year logic
 */

// Define ABSPATH and mocks if running directly via CLI without WordPress
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		return trim( strip_tags( (string) $str ) );
	}
}

if ( ! function_exists( 'sanitize_email' ) ) {
	function sanitize_email( $email ) {
		return filter_var( trim( (string) $email ), FILTER_SANITIZE_EMAIL );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return filter_var( (string) $url, FILTER_SANITIZE_URL );
	}
}

if ( ! function_exists( 'current_time' ) ) {
	function current_time( $type ) {
		if ( 'Y' === $type ) return 2026;
		if ( 'n' === $type ) return 11;
		if ( 'mysql' === $type ) return date( 'Y-m-d H:i:s' );
		return time();
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data ) {
		return json_encode( $data, JSON_UNESCAPED_UNICODE );
	}
}

if ( ! function_exists( 'get_post_field' ) ) {
	function get_post_field( $field, $post_id ) {
		return '';
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		private $code;
		private $message;
		public function __construct( $code = '', $message = '' ) {
			$this->code = $code;
			$this->message = $message;
		}
		public function get_error_message() {
			return $this->message;
		}
		public function get_error_code() {
			return $this->code;
		}
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return is_a( $thing, 'WP_Error' );
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( ...$args ) {}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( ...$args ) {}
}

if ( ! function_exists( 'add_shortcode' ) ) {
	function add_shortcode( ...$args ) {}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default = false ) { return $default; }
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $name, $value ) { return true; }
}

if ( ! function_exists( 'get_terms' ) ) {
	function get_terms( ...$args ) { return [ 'ha-noi', 'ho-chi-minh', 'da-nang', 'thai-nguyen', 'online' ]; }
}

if ( ! function_exists( 'check_ajax_referer' ) ) {
	function check_ajax_referer( ...$args ) { return 1; }
}

if ( ! defined( 'LTDH_TABLE_ELIGIBILITY' ) ) {
	define( 'LTDH_TABLE_ELIGIBILITY', 'ltdh_eligibility_checks' );
}

if ( ! defined( 'LTDH_VERSION' ) ) {
	define( 'LTDH_VERSION', '2.0.0' );
}

// Load Eligibility Rules and Core Engine
require_once __DIR__ . '/../inc/eligibility-rules.php';
require_once __DIR__ . '/../inc/eligibility.php';

class EligibilityRegulations2027Test {
	public static $passed = 0;
	public static $failed = 0;

	public static function assert( $condition, $title, $details = '' ) {
		if ( $condition ) {
			self::$passed++;
			echo "  \033[32m[PASS]\033[0m {$title}\n";
		} else {
			self::$failed++;
			echo "  \033[31m[FAIL]\033[0m {$title}\n";
			if ( $details ) {
				echo "         \033[33mDetails: {$details}\033[0m\n";
			}
		}
	}

	public static function suite( $name ) {
		echo "\n===================================================================\n";
		echo "TEST SUITE: {$name}\n";
		echo "===================================================================\n";
	}
}

// ----------------------------------------------------
// TEST 1: TRUNG CẤP KHÔNG THPT & CHƯA CÓ VĂN HÓA THPT
// ----------------------------------------------------
EligibilityRegulations2027Test::suite( 'Edge Case 1: Trung cấp không THPT & chưa có văn hóa THPT' );

$tc_input = [
	'education'             => 'trung-cap',
	'thpt_status'           => 'chua-co-giay-van-hoa-thpt',
	'grad_year'             => 2024,
	'graduation_grade'      => 'kha',
	'major_relation'        => 'dung-nganh',
	'study_time_preference' => 'online-100',
];
$tc_decision = ltdh_elig_evaluate_decision_tree( $tc_input );

EligibilityRegulations2027Test::assert(
	$tc_decision['decision_status'] === 'not_eligible',
	'Trạng thái quyết định phải là not_eligible (Chưa đủ điều kiện theo Thông tư 95/2026)',
	'Actual: ' . $tc_decision['decision_status']
);

EligibilityRegulations2027Test::assert(
	$tc_decision['urgent_deadline'] === null,
	'Không tạo áp lực deadline tạm thời 15/12/2026 (urgent_deadline === null)',
	'Actual: ' . var_export( $tc_decision['urgent_deadline'], true )
);

EligibilityRegulations2027Test::assert(
	$tc_decision['is_2027_blocked'] === true,
	'Kỳ tuyển sinh mới phải bị khoá (is_2027_blocked === true)',
	'Actual: ' . var_export( $tc_decision['is_2027_blocked'], true )
);

EligibilityRegulations2027Test::assert(
	strpos( $tc_decision['decision_summary'], 'Thông tư 95/2026/TT-BGDĐT' ) !== false,
	'Tóm tắt quyết định có viện dẫn chính xác Thông tư 95/2026/TT-BGDĐT',
	'Summary excerpt: ' . substr( $tc_decision['decision_summary'], 0, 100 )
);

EligibilityRegulations2027Test::assert(
	empty( $tc_decision['remedy_solution'] ),
	'Không hiển thị hộp phương án giải quyết tối ưu (remedy_solution trống)',
	'Remedy: ' . $tc_decision['remedy_solution']
);

// ----------------------------------------------------
// TEST 2: CAO ĐẲNG KHÔNG THPT, TỐT NGHIỆP < 24 THÁNG
// ----------------------------------------------------
EligibilityRegulations2027Test::suite( 'Edge Case 2: Cao đẳng không THPT, tốt nghiệp < 24 tháng' );

// Reference date is 11/2026, grad_year: 2025, grad_month: 6 -> 17 months
$cd_recent_input = [
	'education'             => 'cao-dang',
	'thpt_status'           => 'chua-co-giay-van-hoa-thpt',
	'grad_month'            => 6,
	'grad_year'             => 2025,
	'graduation_grade'      => 'kha',
	'major_relation'        => 'dung-nganh',
	'study_time_preference' => 'online-100',
];
$cd_recent_decision = ltdh_elig_evaluate_decision_tree( $cd_recent_input );

EligibilityRegulations2027Test::assert(
	$cd_recent_decision['months_since_grad'] < 24,
	'Số tháng tốt nghiệp phải < 24 tháng',
	'Actual months: ' . $cd_recent_decision['months_since_grad']
);

EligibilityRegulations2027Test::assert(
	$cd_recent_decision['decision_status'] === 'not_eligible',
	'Trạng thái quyết định phải là not_eligible vì chưa đủ 24 tháng',
	'Actual: ' . $cd_recent_decision['decision_status']
);

EligibilityRegulations2027Test::assert(
	$cd_recent_decision['urgent_deadline'] === null,
	'Không tạo áp lực hạn chót 15/12/2026 (urgent_deadline === null)',
	'Actual: ' . var_export( $cd_recent_decision['urgent_deadline'], true )
);

EligibilityRegulations2027Test::assert(
	strpos( $cd_recent_decision['decision_summary'], '24 tháng' ) !== false,
	'Tóm tắt nêu rõ yêu cầu đạt mốc từ đủ 24 tháng trở lên theo quy chế',
	'Summary: ' . $cd_recent_decision['decision_summary']
);

// ----------------------------------------------------
// TEST 3: CAO ĐẲNG KHÔNG THPT, TỐT NGHIỆP >= 24 THÁNG
// ----------------------------------------------------
EligibilityRegulations2027Test::suite( 'Edge Case 3: Cao đẳng không THPT, tốt nghiệp >= 24 tháng' );

// Reference date is 11/2026, grad_year: 2024, grad_month: 6 -> 29 months >= 24
$cd_mature_input = [
	'education'             => 'cao-dang',
	'thpt_status'           => 'chua-co-giay-van-hoa-thpt',
	'grad_month'            => 6,
	'grad_year'             => 2024,
	'graduation_grade'      => 'kha',
	'major_relation'        => 'dung-nganh',
	'study_time_preference' => 'online-100',
];
$cd_mature_decision = ltdh_elig_evaluate_decision_tree( $cd_mature_input );

EligibilityRegulations2027Test::assert(
	$cd_mature_decision['months_since_grad'] >= 24,
	'Số tháng tốt nghiệp phải >= 24 tháng',
	'Actual months: ' . $cd_mature_decision['months_since_grad']
);

EligibilityRegulations2027Test::assert(
	$cd_mature_decision['decision_status'] === 'eligible',
	'Trạng thái quyết định phải là eligible (Đủ điều kiện cả 2026 & 2027)',
	'Actual: ' . $cd_mature_decision['decision_status']
);

EligibilityRegulations2027Test::assert(
	$cd_mature_decision['badge_color'] === 'emerald',
	'Badge color phải là emerald (xanh lá)',
	'Actual: ' . $cd_mature_decision['badge_color']
);

EligibilityRegulations2027Test::assert(
	$cd_mature_decision['is_2027_blocked'] === false,
	'Kỳ 2027 không bị chặn (is_2027_blocked === false)',
	'Actual: ' . var_export( $cd_mature_decision['is_2027_blocked'], true )
);

// ----------------------------------------------------
// TEST 4: TRUNG HỌC NGHỀ (THN) THEO THÔNG TƯ 95/2026
// ----------------------------------------------------
EligibilityRegulations2027Test::suite( 'Edge Case 4: Trung học nghề (THN) theo Thông tư 95/2026' );

// 4a. Cùng nhóm ngành + Khá/Giỏi -> ĐỦ ĐIỀU KIỆN
$thn_pass_input = [
	'education'             => 'trung-hoc-nghe',
	'graduation_grade'      => 'kha',
	'major_relation'        => 'dung-nganh',
	'study_time_preference' => 'online-100',
];
$thn_pass_decision = ltdh_elig_evaluate_decision_tree( $thn_pass_input );

EligibilityRegulations2027Test::assert(
	$thn_pass_decision['decision_status'] === 'eligible',
	'THN cùng nhóm ngành + Khá: ĐỦ ĐIỀU KIỆN liên thông từ 01/01/2027',
	'Actual: ' . $thn_pass_decision['decision_status']
);

EligibilityRegulations2027Test::assert(
	strpos( $thn_pass_decision['decision_summary'], '95/2026' ) !== false,
	'Trích dẫn đúng Thông tư 95/2026 trong tóm tắt quyết định',
	'Summary: ' . $thn_pass_decision['decision_summary']
);

// 4b. Trái ngành -> CHƯA ĐỦ ĐIỀU KIỆN 2027 (Cảnh báo không đạt chuẩn)
$thn_fail_major_input = [
	'education'             => 'trung-hoc-nghe',
	'graduation_grade'      => 'kha',
	'major_relation'        => 'trai-nganh',
	'study_time_preference' => 'online-100',
];
$thn_fail_major_decision = ltdh_elig_evaluate_decision_tree( $thn_fail_major_input );

EligibilityRegulations2027Test::assert(
	$thn_fail_major_decision['decision_status'] === 'not_eligible_2027',
	'THN trái ngành: Phải rơi vào trạng thái not_eligible_2027',
	'Actual: ' . $thn_fail_major_decision['decision_status']
);

EligibilityRegulations2027Test::assert(
	$thn_fail_major_decision['badge_color'] === 'rose',
	'Badge color phải là rose (đỏ)',
	'Actual: ' . $thn_fail_major_decision['badge_color']
);

// 4c. Đúng ngành nhưng học lực Trung bình -> CHƯA ĐỦ ĐIỀU KIỆN 2027
$thn_fail_grade_input = [
	'education'             => 'trung-hoc-nghe',
	'graduation_grade'      => 'trung-binh',
	'major_relation'        => 'dung-nganh',
	'study_time_preference' => 'online-100',
];
$thn_fail_grade_decision = ltdh_elig_evaluate_decision_tree( $thn_fail_grade_input );

EligibilityRegulations2027Test::assert(
	$thn_fail_grade_decision['decision_status'] === 'not_eligible_2027',
	'THN xếp loại trung bình: Phải rơi vào trạng thái not_eligible_2027',
	'Actual: ' . $thn_fail_grade_decision['decision_status']
);

EligibilityRegulations2027Test::assert(
	strpos( $thn_fail_grade_decision['remedy_solution'], 'Cao đẳng' ) !== false,
	'Đưa ra giải pháp liên thông qua Cao đẳng để mở rộng cơ hội',
	'Remedy: ' . $thn_fail_grade_decision['remedy_solution']
);

// ----------------------------------------------------
// TEST 5: CAO ĐẲNG CÓ THPT & QUỸ THỜI GIAN THEO THÔNG TƯ 56/2026
// ----------------------------------------------------
EligibilityRegulations2027Test::suite( 'Edge Case 5: Cao đẳng có THPT & Quỹ thời gian Thông tư 56/2026' );

// 5a. Học ngoài giờ/Online -> Loại bỏ Chính quy, trích dẫn Điều 5 Thông tư 56
$online_input = [
	'education'             => 'cao-dang',
	'thpt_status'           => 'co-bang-thpt',
	'graduation_grade'      => 'kha',
	'major_relation'        => 'dung-nganh',
	'study_time_preference' => 'online-100',
];
$online_decision = ltdh_elig_evaluate_decision_tree( $online_input );

EligibilityRegulations2027Test::assert(
	$online_decision['exclude_chinh_quy'] === true,
	'exclude_chinh_quy phải là true khi chọn online-100',
	'Actual: ' . var_export( $online_decision['exclude_chinh_quy'], true )
);

EligibilityRegulations2027Test::assert(
	! in_array( 'chinh-quy', $online_decision['suitable_training_types'], true ),
	'Hệ Chính quy không được xuất hiện trong suitable_training_types',
	'Types: ' . implode( ',', $online_decision['suitable_training_types'] )
);

EligibilityRegulations2027Test::assert(
	in_array( 'tu-xa', $online_decision['suitable_training_types'], true ) && in_array( 'vua-hoc-vua-lam', $online_decision['suitable_training_types'], true ),
	'Chỉ gợi ý Vừa làm vừa học và Đào tạo từ xa',
	'Types: ' . implode( ',', $online_decision['suitable_training_types'] )
);

EligibilityRegulations2027Test::assert(
	strpos( $online_decision['time_filter_note'], 'Điều 5 Thông tư 56/2026/TT-BGDĐT' ) !== false,
	'Ghi chú trích dẫn chính xác Điều 5 Thông tư 56/2026/TT-BGDĐT (>50% trực tiếp)',
	'Note: ' . $online_decision['time_filter_note']
);

EligibilityRegulations2027Test::assert(
	strpos( $online_decision['credit_transfer_note'], 'Thông tư 52/2026/TT-BGDĐT' ) !== false,
	'Ghi chú nhấn mạnh miễn trừ tín chỉ theo Thông tư 52 & 56',
	'Credit note: ' . $online_decision['credit_transfer_note']
);

// 5b. Học ban ngày giờ hành chính -> Đủ cả 3 hệ
$daytime_input = [
	'education'             => 'cao-dang',
	'thpt_status'           => 'co-bang-thpt',
	'graduation_grade'      => 'kha',
	'major_relation'        => 'dung-nganh',
	'study_time_preference' => 'gio-hanh-chinh',
];
$daytime_decision = ltdh_elig_evaluate_decision_tree( $daytime_input );

EligibilityRegulations2027Test::assert(
	$daytime_decision['exclude_chinh_quy'] === false,
	'exclude_chinh_quy phải là false khi học ban ngày',
	'Actual: ' . var_export( $daytime_decision['exclude_chinh_quy'], true )
);

EligibilityRegulations2027Test::assert(
	count( $daytime_decision['suitable_training_types'] ) === 3 && in_array( 'chinh-quy', $daytime_decision['suitable_training_types'], true ),
	'Gợi ý đầy đủ cả 3 hệ (Chính quy, Vừa học vừa làm, Đào tạo từ xa)',
	'Types: ' . implode( ',', $daytime_decision['suitable_training_types'] )
);

// 5c. Trái ngành hoặc Xếp loại trung bình -> ĐỦ ĐIỀU KIỆN CÓ LƯU Ý
$recheck_input = [
	'education'             => 'cao-dang',
	'thpt_status'           => 'co-bang-thpt',
	'graduation_grade'      => 'trung-binh',
	'major_relation'        => 'trai-nganh',
	'study_time_preference' => 'online-100',
];
$recheck_decision = ltdh_elig_evaluate_decision_tree( $recheck_input );

EligibilityRegulations2027Test::assert(
	$recheck_decision['decision_status'] === 'warning_urgent',
	'Có THPT nhưng trái ngành + xếp loại TB: Đủ điều kiện có lưu ý (warning_urgent)',
	'Actual: ' . $recheck_decision['decision_status']
);

EligibilityRegulations2027Test::assert(
	$recheck_decision['badge_color'] === 'amber',
	'Badge color phải là amber (vàng)',
	'Actual: ' . $recheck_decision['badge_color']
);

// ----------------------------------------------------
// TEST 6: INPUT VALIDATION & EDUCATION LABELS
// ----------------------------------------------------
EligibilityRegulations2027Test::suite( 'Edge Case 6: Input Validation & Helper Functions' );

// 6a. Validation: Chỉ chấp nhận cao-dang và trung-cap, từ chối trung-hoc-nghe và dai-hoc
$val_thn = ltdh_elig_validate_input( [
	'education'             => 'trung-hoc-nghe',
	'graduation_grade'      => 'kha',
	'major_relation'        => 'dung-nganh',
	'study_time_preference' => 'online-100',
] );
EligibilityRegulations2027Test::assert(
	is_wp_error( $val_thn ) && $val_thn->get_error_code() === 'invalid_education',
	'Validation từ chối trình độ ngoài phạm vi tuyển sinh liên thông (trung-hoc-nghe)',
	'Result: ' . ( is_wp_error( $val_thn ) ? $val_thn->get_error_code() : 'ACCEPTED' )
);

$val_cd = ltdh_elig_validate_input( [
	'education'             => 'cao-dang',
	'thpt_status'           => 'co-bang-thpt',
	'graduation_grade'      => 'kha',
	'major_relation'        => 'dung-nganh',
	'study_time_preference' => 'online-100',
] );
EligibilityRegulations2027Test::assert(
	$val_cd === true,
	'Validation chấp nhận trình độ Cao đẳng',
	'Error: ' . ( is_wp_error( $val_cd ) ? $val_cd->get_error_message() : '' )
);

$val_tc = ltdh_elig_validate_input( [
	'education'             => 'trung-cap',
	'thpt_status'           => 'co-bang-thpt',
	'graduation_grade'      => 'kha',
	'major_relation'        => 'dung-nganh',
	'study_time_preference' => 'online-100',
] );
EligibilityRegulations2027Test::assert(
	$val_tc === true,
	'Validation chấp nhận trình độ Trung cấp',
	'Error: ' . ( is_wp_error( $val_tc ) ? $val_tc->get_error_message() : '' )
);

// 6b. Bắt buộc năm tốt nghiệp khi CĐ chưa có THPT
$val_cd_no_grad = ltdh_elig_validate_input( [
	'education'   => 'cao-dang',
	'thpt_status' => 'chua-co-giay-van-hoa-thpt',
	'grad_year'   => 0,
] );
EligibilityRegulations2027Test::assert(
	is_wp_error( $val_cd_no_grad ) && $val_cd_no_grad->get_error_code() === 'invalid_grad_year',
	'Validation bắt buộc năm tốt nghiệp khi Cao đẳng chưa có THPT',
	'Code: ' . ( is_wp_error( $val_cd_no_grad ) ? $val_cd_no_grad->get_error_code() : 'NONE' )
);

// 6c. Education labels: Chỉ Cao đẳng và Trung cấp (chuẩn không suffix nghề / VB2)
EligibilityRegulations2027Test::assert(
	ltdh_elig_get_education_label( 'cao-dang' ) === 'Cao đẳng',
	'ltdh_elig_get_education_label trả về chuẩn "Cao đẳng" (không gắn suffix nghề)'
);

EligibilityRegulations2027Test::assert(
	ltdh_elig_get_education_label( 'trung-cap' ) === 'Trung cấp',
	'ltdh_elig_get_education_label trả về chuẩn "Trung cấp" (không gắn suffix nghề)'
);

// SUMMARY
echo "\n===================================================================\n";
echo "SUMMARY: " . EligibilityRegulations2027Test::$passed . " PASSED, " . EligibilityRegulations2027Test::$failed . " FAILED\n";
echo "===================================================================\n";

if ( EligibilityRegulations2027Test::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
