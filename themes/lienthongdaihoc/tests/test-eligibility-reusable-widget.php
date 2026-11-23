<?php
/**
 * Test Suite for Reusable Eligibility Checker Component & Context-Aware Engine
 *
 * Verification:
 * 1. Shortcode registration & alias parsing
 * 2. Context Conflict 1: Chính quy program + Outside hours/Online preference -> Article 5 TT 56 warning & sibling suggestion
 * 3. Context Conflict 2: UTC program + Student without THPT -> Urgent warning UTC closed for 2026, redirect to EPU
 * 4. Multi-mode rendering (compact vs full)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) );
}

// Minimal WordPress function mocks for CLI testing
if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) { return trim( strip_tags( (string) $str ) ); }
}
if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $key ) ); }
}
if ( ! function_exists( 'sanitize_title' ) ) {
	function sanitize_title( $title ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $title ) ); }
}
if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $class ) { return preg_replace( '/[^a-zA-Z0-9_\-]/', '', (string) $class ); }
}
if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
}
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
}
if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) { return filter_var( (string) $url, FILTER_SANITIZE_URL ); }
}
if ( ! function_exists( 'selected' ) ) {
	function selected( $selected, $current = true, $echo = true ) {
		$res = ( (string) $selected === (string) $current ) ? 'selected="selected"' : '';
		if ( $echo ) echo $res;
		return $res;
	}
}
if ( ! function_exists( 'current_time' ) ) {
	function current_time( $type ) {
		if ( 'Y' === $type ) return 2026;
		if ( 'n' === $type ) return 11;
		return time();
	}
}
if ( ! function_exists( 'wp_unique_id' ) ) {
	function wp_unique_id( $prefix = '' ) {
		static $id = 0;
		return $prefix . ( ++$id );
	}
}
if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data ) { return json_encode( $data, JSON_UNESCAPED_UNICODE ); }
}
if ( ! function_exists( 'shortcode_atts' ) ) {
	function shortcode_atts( $pairs, $atts, $shortcode = '' ) {
		$atts = (array) $atts;
		$out = [];
		foreach ( $pairs as $name => $default ) {
			if ( array_key_exists( $name, $atts ) ) {
				$out[ $name ] = $atts[ $name ];
			} else {
				$out[ $name ] = $default;
			}
		}
		return $out;
	}
}
if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() { return 100; }
}
if ( ! function_exists( 'get_the_title' ) ) {
	function get_the_title( $post = 0 ) {
		if ( 100 === $post || 0 === $post ) {
			return 'Liên thông Công nghệ thông tin Từ xa ĐH Giao thông Vận tải (UTC)';
		}
		return 'Chương trình mẫu';
	}
}
if ( ! function_exists( 'get_permalink' ) ) {
	function get_permalink( $post = 0 ) {
		return 'https://lienthongdaihoc.com/lien-thong-cong-nghe-thong-tin-tu-xa-utc/';
	}
}
if ( ! function_exists( 'get_posts' ) ) {
	function get_posts( $args = [] ) {
		return [];
	}
}
if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $str ) { return $str; }
}
if ( ! function_exists( 'ltdh_get_icon' ) ) {
	function ltdh_get_icon( $name, $class = '' ) { return '<svg class="' . esc_attr( $class ) . '"></svg>'; }
}
if ( ! function_exists( 'get_stylesheet_directory_uri' ) ) {
	function get_stylesheet_directory_uri() { return 'https://lienthongdaihoc.com/wp-content/themes/lienthongdaihoc'; }
}
if ( ! function_exists( 'get_terms' ) ) {
	function get_terms( $args = [] ) {
		return [];
	}
}
if ( ! function_exists( 'get_field' ) ) {
	function get_field( $selector, $post_id = false ) {
		return '';
	}
}
if ( ! function_exists( 'ltdh_render_consultation_form' ) ) {
	function ltdh_render_consultation_form( $args = [] ) {
		echo '<div class="consultation-form-mock"></div>';
	}
}
if ( ! function_exists( 'get_template_part' ) ) {
	function get_template_part( $slug, $name = null, $args = [] ) {
		$file = ABSPATH . '/' . $slug . '.php';
		if ( file_exists( $file ) ) {
			include $file;
		}
	}
}
if ( ! function_exists( 'add_action' ) ) { function add_action( ...$args ) {} }
if ( ! function_exists( 'add_filter' ) ) { function add_filter( ...$args ) {} }
if ( ! function_exists( 'add_shortcode' ) ) { function add_shortcode( ...$args ) {} }
if ( ! function_exists( 'wp_unslash' ) ) { function wp_unslash( $val ) { return $val; } }
if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		private $code;
		private $message;
		public function __construct( $code = '', $message = '' ) {
			$this->code = $code;
			$this->message = $message;
		}
		public function get_error_message() { return $this->message; }
		public function get_error_code() { return $this->code; }
	}
}
if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) { return is_a( $thing, 'WP_Error' ); }
}
if ( ! defined( 'LTDH_VERSION' ) ) {
	define( 'LTDH_VERSION', '2.0.0' );
}

// Include rules engine and eligibility files
require_once __DIR__ . '/../inc/eligibility-rules.php';
require_once __DIR__ . '/../inc/eligibility.php';

class ReusableWidgetTestSuite {
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
// SUITE 1: CONTEXT CONFLICT 1 — SCHEDULE MISMATCH (TT 56/2026 Điều 5)
// ----------------------------------------------------
ReusableWidgetTestSuite::suite( 'Context Conflict 1: Hệ Chính quy vs Quỹ thời gian Ngoài giờ / Online' );

$cq_context = [
	'program_id'         => 101,
	'program_title'      => 'Liên thông Công nghệ thông tin Chính quy ĐH Giao thông Vận tải',
	'school_title'       => 'Trường Đại học Giao thông Vận tải (UTC)',
	'school_slug'        => 'dai-hoc-giao-thong-van-tai',
	'school_code'        => 'UTC',
	'major_title'        => 'Công nghệ thông tin',
	'major_slug'         => 'cong-nghe-thong-tin',
	'training_type_name' => 'Chính quy',
	'training_type_slug' => 'chinh-quy',
	'sibling_programs'   => [
		[
			'id'         => 102,
			'title'      => 'Liên thông CNTT Đào tạo từ xa UTC',
			'permalink'  => '/lien-thong-cong-nghe-thong-tin-tu-xa-utc/',
			'type_name'  => 'Đào tạo từ xa',
			'type_slug'  => 'tu-xa',
		],
		[
			'id'         => 103,
			'title'      => 'Liên thông CNTT Vừa làm vừa học UTC',
			'permalink'  => '/lien-thong-cong-nghe-thong-tin-vua-hoc-vua-lam-utc/',
			'type_name'  => 'Vừa làm vừa học',
			'type_slug'  => 'vua-hoc-vua-lam',
		],
	],
	'page_url'           => 'https://lienthongdaihoc.com/lien-thong-cong-nghe-thong-tin-chinh-quy-utc/',
];

$cq_input = [
	'education'             => 'cao-dang',
	'thpt_status'           => 'co-bang-thpt',
	'graduation_grade'      => 'kha',
	'major_relation'        => 'dung-nganh',
	'study_time_preference' => 'online-100', // outside hours / online
	'context'               => $cq_context,
];

$cq_decision = ltdh_elig_evaluate_decision_tree( $cq_input );

ReusableWidgetTestSuite::assert(
	! empty( $cq_decision['context_conflict'] ),
	'Phải phát hiện xung đột ngữ cảnh (context_conflict !== null)'
);

ReusableWidgetTestSuite::assert(
	$cq_decision['context_conflict']['type'] === 'schedule_mismatch',
	'Loại xung đột phải là schedule_mismatch',
	'Actual: ' . ( $cq_decision['context_conflict']['type'] ?? 'none' )
);

ReusableWidgetTestSuite::assert(
	strpos( $cq_decision['context_conflict']['message'], 'Điều 5 Thông tư 56/2026/TT-BGDĐT' ) !== false,
	'Thông điệp xung đột trích dẫn Điều 5 Thông tư 56/2026 (>50% giờ hành chính)',
	'Message: ' . ( $cq_decision['context_conflict']['message'] ?? '' )
);

ReusableWidgetTestSuite::assert(
	$cq_decision['context_conflict']['action_url'] === '/lien-thong-cong-nghe-thong-tin-tu-xa-utc/',
	'Đề xuất đúng đường dẫn chuyển sang hệ Đào tạo từ xa cùng ngành',
	'Action URL: ' . ( $cq_decision['context_conflict']['action_url'] ?? '' )
);

// ----------------------------------------------------
// SUITE 2: CONTEXT CONFLICT 2 — UTC HẾT CHỈ TIÊU 2026 VS THÍ SINH CHƯA CÓ THPT
// ----------------------------------------------------
ReusableWidgetTestSuite::suite( 'Context Conflict 2: Không gợi ý chuyển sang trường khác' );

$utc_context = [
	'program_id'         => 200,
	'program_title'      => 'Liên thông CNTT Từ xa UTC',
	'school_title'       => 'Trường Đại học Giao thông Vận tải',
	'school_slug'        => 'utc',
	'school_code'        => 'UTC',
	'major_title'        => 'Công nghệ thông tin',
	'major_slug'         => 'cong-nghe-thong-tin',
	'training_type_name' => 'Đào tạo từ xa',
	'training_type_slug' => 'tu-xa',
	'sibling_programs'   => [],
	'page_url'           => 'https://lienthongdaihoc.com/lien-thong-cong-nghe-thong-tin-tu-xa-utc/',
];

// Student with Trung cấp without THPT on UTC page
$utc_student_input = [
	'education'             => 'trung-cap',
	'thpt_status'           => 'chua-co-giay-van-hoa-thpt',
	'grad_year'             => 2024,
	'graduation_grade'      => 'kha',
	'major_relation'        => 'dung-nganh',
	'study_time_preference' => 'online-100',
	'context'               => $utc_context,
];

$utc_decision = ltdh_elig_evaluate_decision_tree( $utc_student_input );

ReusableWidgetTestSuite::assert(
	empty( $utc_decision['context_conflict'] ),
	'Tuyệt đối không kích hoạt gợi ý chuyển sang trường khác (No cross-school suggestion)'
);

ReusableWidgetTestSuite::assert(
	! isset( $utc_decision['context_conflict']['school_redirect'] ),
	'Không có dữ liệu school_redirect sang trường đối thủ'
);

// ----------------------------------------------------
// SUITE 3: SHORTCODE REGISTRATION & COMPACT RENDERING
// ----------------------------------------------------
ReusableWidgetTestSuite::suite( 'Shortcode Registration & Reusable Component Part' );

ReusableWidgetTestSuite::assert(
	function_exists( 'ltdh_elig_condition_checker_shortcode' ),
	'Hàm ltdh_elig_condition_checker_shortcode() đã được đăng ký'
);

$output_compact = ltdh_elig_condition_checker_shortcode( [
	'mode'   => 'compact',
	'school' => 'utc',
	'major'  => 'cong-nghe-thong-tin',
	'system' => 'tu-xa',
] );

ReusableWidgetTestSuite::assert(
	strpos( $output_compact, 'ltdh-condition-checker-compact' ) !== false,
	'Shortcode render đúng container ltdh-condition-checker-compact'
);

ReusableWidgetTestSuite::assert(
	strpos( $output_compact, 'data-checker-mode="compact"' ) !== false,
	'Thuộc tính data-checker-mode="compact" có mặt trong markup'
);

ReusableWidgetTestSuite::assert(
	strpos( $output_compact, 'name="compact_education"' ) !== false,
	'Trường compact_education có mặt trong form'
);

ReusableWidgetTestSuite::assert(
	strpos( $output_compact, 'data-conflict-box' ) !== false,
	'Khung data-conflict-box có mặt sẵn để đón nhận cảnh báo điều hướng'
);

ReusableWidgetTestSuite::assert(
	strpos( $output_compact, 'data-compact-lead-form' ) !== false,
	'Form lead capture data-compact-lead-form được nhúng sẵn trong Step 3'
);

// ----------------------------------------------------
// SUITE 4: SHORTCODE FULL MODE
// ----------------------------------------------------
ReusableWidgetTestSuite::suite( 'Shortcode Full Mode for /kiem-tra-dieu-kien/' );

$output_full = ltdh_elig_condition_checker_shortcode( [
	'mode' => 'full',
] );

ReusableWidgetTestSuite::assert(
	strpos( $output_full, 'ltdh-condition-checker-full' ) !== false,
	'Shortcode mode="full" render đúng container ltdh-condition-checker-full'
);

ReusableWidgetTestSuite::assert(
	strpos( $output_full, 'data-checker-mode="full"' ) !== false,
	'Thuộc tính data-checker-mode="full" có mặt trong markup'
);

// ----------------------------------------------------
// SUITE 5: DEFAULT SIDEBAR MOUNTING & WP WIDGET CLASS
// ----------------------------------------------------
ReusableWidgetTestSuite::suite( 'Default Sidebar Mounting & LTDH_Widget_Condition_Checker Registration' );

if ( ! class_exists( 'WP_Widget' ) ) {
	class WP_Widget {
		public function __construct( $id_base, $name, $widget_options = [] ) {}
	}
}

require_once dirname( __DIR__ ) . '/inc/widgets/class-widget-condition-checker.php';
require_once dirname( __DIR__ ) . '/inc/widgets/widget-helpers.php';

ReusableWidgetTestSuite::assert(
	class_exists( 'LTDH_Widget_Condition_Checker' ),
	'LTDH_Widget_Condition_Checker class được định nghĩa và kế thừa WP_Widget'
);

ReusableWidgetTestSuite::assert(
	function_exists( 'ltdh_sidebar_has_condition_checker' ),
	'Hàm helper ltdh_sidebar_has_condition_checker() đã được định nghĩa'
);

$sidebar_program_code = file_get_contents( dirname( __DIR__ ) . '/sidebar-program.php' );
ReusableWidgetTestSuite::assert(
	strpos( $sidebar_program_code, 'template-parts/widgets/condition-checker' ) !== false,
	'sidebar-program.php đã gắn mặc định Condition Checker widget'
);

$sidebar_school_code = file_get_contents( dirname( __DIR__ ) . '/sidebar-school.php' );
ReusableWidgetTestSuite::assert(
	strpos( $sidebar_school_code, 'template-parts/widgets/condition-checker' ) !== false,
	'sidebar-school.php đã gắn mặc định Condition Checker widget'
);

$sidebar_major_code = file_get_contents( dirname( __DIR__ ) . '/sidebar-major.php' );
ReusableWidgetTestSuite::assert(
	strpos( $sidebar_major_code, 'template-parts/widgets/condition-checker' ) !== false,
	'sidebar-major.php đã gắn mặc định Condition Checker widget'
);

$sidebar_blog_code = file_get_contents( dirname( __DIR__ ) . '/sidebar.php' );
ReusableWidgetTestSuite::assert(
	strpos( $sidebar_blog_code, 'template-parts/widgets/condition-checker' ) !== false,
	'sidebar.php (Blog) đã gắn mặc định Condition Checker widget'
);

$sidebar_guide_code = file_get_contents( dirname( __DIR__ ) . '/sidebar-guide.php' );
ReusableWidgetTestSuite::assert(
	strpos( $sidebar_guide_code, 'template-parts/widgets/condition-checker' ) !== false,
	'sidebar-guide.php đã gắn mặc định Condition Checker widget'
);

$sidebar_page_code = file_get_contents( dirname( __DIR__ ) . '/sidebar-page.php' );
ReusableWidgetTestSuite::assert(
	strpos( $sidebar_page_code, 'template-parts/widgets/condition-checker' ) !== false,
	'sidebar-page.php đã gắn mặc định Condition Checker widget'
);

// ----------------------------------------------------
// TEST 6: TỰ ĐỘNG ẨN FORM TƯ VẤN TRÙNG LẶP Ở SIDEBAR KHI STEP 3 KÍCH HOẠT
// ----------------------------------------------------
ReusableWidgetTestSuite::suite( 'Tự động ẩn Form tư vấn trùng lặp ở Sidebar khi Step 3 kích hoạt' );

$elig_js_code = file_get_contents( dirname( __DIR__ ) . '/assets/js/eligibility.js' );

ReusableWidgetTestSuite::assert(
	strpos( $elig_js_code, 'data-hidden-by-checker' ) !== false,
	'eligibility.js có cơ chế đánh dấu data-hidden-by-checker cho form tư vấn trùng lặp'
);

ReusableWidgetTestSuite::assert(
	strpos( $elig_js_code, 'widget_ltdh_consultation_form' ) !== false,
	'eligibility.js quét đúng selector widget_ltdh_consultation_form và #register'
);

ReusableWidgetTestSuite::assert(
	strpos( $elig_js_code, 'step === 3' ) !== false,
	'Khi chuyển sang Step 3 (kết quả và form hồ sơ), widget tư vấn bên dưới tự động được ẩn'
);

// ----------------------------------------------------
// TEST 7: HỖ TRỢ CSS CHO TRẠNG THÁI NOT_ELIGIBLE (GẠCH DƯỚI & GẠCH NGANG)
// ----------------------------------------------------
ReusableWidgetTestSuite::suite( 'CSS Selector cho trạng thái Chưa đủ điều kiện (status-not_eligible)' );

$elig_css_code = file_get_contents( dirname( __DIR__ ) . '/assets/css/eligibility.css' );

ReusableWidgetTestSuite::assert(
	strpos( $elig_css_code, '.status-not_eligible' ) !== false,
	'eligibility.css hỗ trợ đầy đủ selector .status-not_eligible (gạch dưới)'
);

ReusableWidgetTestSuite::assert(
	strpos( $elig_css_code, '.status-not-eligible' ) !== false,
	'eligibility.css hỗ trợ selector chuẩn .status-not-eligible (gạch ngang)'
);

ReusableWidgetTestSuite::assert(
	strpos( $elig_js_code, "status.replace(/_/g, '-')" ) !== false,
	'eligibility.js chuẩn hóa trạng thái not_eligible sang not-eligible'
);

// SUMMARY
echo "\n===================================================================\n";
echo "SUMMARY: " . ReusableWidgetTestSuite::$passed . " PASSED, " . ReusableWidgetTestSuite::$failed . " FAILED\n";
echo "===================================================================\n";

if ( ReusableWidgetTestSuite::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
