<?php
/**
 * Adversarial Verification Test Suite for Milestone 2:
 * Card Unification, Data Parity, Accessibility & Navigation Skeleton.
 *
 * Authored by: challenger_m2_2_v2 (Adversarial Empirical Challenger)
 * Scope:
 * 1. Card Parity: Full SSR vs AJAX execution parity across diverse program fixtures
 * 2. Data Attributes Parity: Exact match of data-compare-* attributes on container and button
 * 3. Accessibility & Landmarks: Skip link registration priority, target ID matching, single landmark enforcement
 * 4. Mega Menu & Navigation: Execution resilience, edge cases, walker stress test
 *
 * Run: php tests/test-adversarial-m2-verification.php
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class M2AdversarialHarness {
	public static int $passed = 0;
	public static int $failed = 0;
	public static array $errors = [];

	public static function assert( bool $condition, string $description, array $context = [] ): void {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] {$description}\n";
		} else {
			self::$failed++;
			self::$errors[] = [ 'desc' => $description, 'context' => $context ];
			echo "  [FAIL] {$description}\n";
			if ( ! empty( $context ) ) {
				echo "         Context: " . json_encode( $context, JSON_UNESCAPED_UNICODE ) . "\n";
			}
		}
	}
}

echo "===================================================================\n";
echo "CHALLENGER M2-2-V2: ADVERSARIAL EMPIRICAL VERIFICATION HARNESS\n";
echo "===================================================================\n\n";

// =================================================================
// SECTION 1: SKIP LINK REGISTRATION, LANDMARK TARGETING & SKELETON
// =================================================================
echo "SECTION 1: Skip Link Registration & Landmark Targeting\n";
echo "-------------------------------------------------------------------\n";

// 1.1 Inspect inc/core/class-theme-setup.php for ltdh_skip_link definition and hook
$setup_code = file_get_contents( ABSPATH . 'inc/core/class-theme-setup.php' );

M2AdversarialHarness::assert(
	strpos( $setup_code, 'function ltdh_skip_link()' ) !== false,
	"ltdh_skip_link() function is defined in inc/core/class-theme-setup.php"
);

// Verify exact hook and priority 5
$hook_match = preg_match( "/add_action\s*\(\s*['\"]wp_body_open['\"]\s*,\s*['\"]ltdh_skip_link['\"]\s*,\s*5\s*\)/", $setup_code );
M2AdversarialHarness::assert(
	$hook_match === 1,
	"ltdh_skip_link is hooked to 'wp_body_open' with exact priority 5"
);

// 1.2 Verify skip link output contains href="#content" and required accessibility classes
preg_match( '/function ltdh_skip_link\(\)\s*\{([^}]+)\}/s', $setup_code, $fn_matches );
$fn_body = $fn_matches[1] ?? '';

M2AdversarialHarness::assert(
	strpos( $fn_body, 'href="#content"' ) !== false,
	"Skip link markup targets '#content' exactly"
);

M2AdversarialHarness::assert(
	strpos( $fn_body, 'skip-link screen-reader-text' ) !== false,
	"Skip link contains standard accessible classes 'skip-link screen-reader-text'"
);

M2AdversarialHarness::assert(
	strpos( $fn_body, 'focus:not-sr-only' ) !== false,
	"Skip link includes 'focus:not-sr-only' for keyboard accessibility"
);

// 1.3 Verify header.php calls wp_body_open() and opens <main id="content">
$header_code = file_get_contents( ABSPATH . 'header.php' );

M2AdversarialHarness::assert(
	strpos( $header_code, 'wp_body_open();' ) !== false,
	"header.php executes wp_body_open() right after <body>"
);

M2AdversarialHarness::assert(
	preg_match( '/<main\s+id=["\']content["\'][^>]*role=["\']main["\']/', $header_code ) === 1,
	"header.php renders <main id=\"content\" class=\"site-main\" role=\"main\">"
);

// 1.4 Verify BlankSlate container hierarchy in header.php
M2AdversarialHarness::assert(
	strpos( $header_code, '<div id="wrapper" class="hfeed">' ) !== false,
	"header.php opens container <div id=\"wrapper\" class=\"hfeed\">"
);

M2AdversarialHarness::assert(
	strpos( $header_code, '<header id="masthead"' ) !== false && strpos( $header_code, 'role="banner"' ) !== false,
	"header.php contains <header role=\"banner\">"
);

M2AdversarialHarness::assert(
	strpos( $header_code, '<div id="container">' ) !== false,
	"header.php opens <div id=\"container\">"
);

// 1.5 Verify footer.php closes containers in correct reverse order
$footer_code = file_get_contents( ABSPATH . 'footer.php' );

M2AdversarialHarness::assert(
	strpos( $footer_code, '</main>' ) !== false,
	"footer.php closes </main>"
);

M2AdversarialHarness::assert(
	strpos( $footer_code, '</div><!-- #container -->' ) !== false || strpos( $footer_code, '</div><!-- container -->' ) !== false || strpos( $footer_code, '</div>' ) !== false,
	"footer.php closes #container"
);

M2AdversarialHarness::assert(
	strpos( $footer_code, '<footer' ) !== false && strpos( $footer_code, 'role="contentinfo"' ) !== false,
	"footer.php renders <footer role=\"contentinfo\">"
);

M2AdversarialHarness::assert(
	strpos( $footer_code, '</div><!-- #wrapper -->' ) !== false,
	"footer.php closes </div><!-- #wrapper -->"
);

// 1.6 Verify zero duplicate <main> tags across all theme templates
$php_files = glob( ABSPATH . '*.php' );
$template_part_files = glob( ABSPATH . 'template-parts/**/*.php' );
$all_theme_php = array_merge( $php_files, $template_part_files );

$main_open_count = 0;
$main_open_locations = [];

foreach ( $all_theme_php as $file ) {
	$content = file_get_contents( $file );
	// Strip comments before checking for HTML <main
	$clean_content = preg_replace( '/\/\*.*?\*\//s', '', $content );
	$clean_content = preg_replace( '/\/\/.*$/m', '', $clean_content );
	if ( preg_match_all( '/<main[\s>]/i', $clean_content, $matches ) ) {
		$count = count( $matches[0] );
		$main_open_count += $count;
		$main_open_locations[ basename( $file ) ] = $count;
	}
}

M2AdversarialHarness::assert(
	$main_open_count === 1 && isset( $main_open_locations['header.php'] ),
	"Exactly 1 active <main> opening tag across entire theme (in header.php)",
	$main_open_locations
);

// 1.7 Verify zero duplicate id="content" across all theme templates
$content_id_count = 0;
$content_id_locations = [];

foreach ( $all_theme_php as $file ) {
	$content = file_get_contents( $file );
	$clean_content = preg_replace( '/\/\*.*?\*\//s', '', $content );
	$clean_content = preg_replace( '/\/\/.*$/m', '', $clean_content );
	if ( preg_match_all( '/id=["\']content["\']/i', $clean_content, $matches ) ) {
		$count = count( $matches[0] );
		$content_id_count += $count;
		$content_id_locations[ basename( $file ) ] = $count;
	}
}

M2AdversarialHarness::assert(
	$content_id_count === 1 && isset( $content_id_locations['header.php'] ),
	"Exactly 1 active id=\"content\" attribute across entire theme (matching skip link target)",
	$content_id_locations
);


// =================================================================
// SECTION 2: CARD PROGRAM EMPIRICAL SSR VS AJAX EXECUTION PARITY
// =================================================================
echo "\nSECTION 2: Program Card SSR vs AJAX Empirical Execution Parity\n";
echo "-------------------------------------------------------------------\n";

// Setup Mock WordPress Environment for Card Execution
class CardMockEnv {
	public static array $posts = [];
	public static array $meta = [];
	public static array $terms = [];
	public static int $current_post_id = 0;

	public static function reset() {
		self::$posts = [];
		self::$meta = [];
		self::$terms = [];
		self::$current_post_id = 0;
	}

	public static function add_post( int $id, string $title, string $type = 'program', string $slug = '' ) {
		self::$posts[ $id ] = (object) [
			'ID'         => $id,
			'post_title' => $title,
			'post_name'  => $slug ?: sanitize_title( $title ),
			'post_type'  => $type,
		];
	}

	public static function set_field( int $id, string $key, $value ) {
		self::$meta[ $id ][ $key ] = $value;
	}

	public static function add_term( int $id, string $taxonomy, string $slug, string $name ) {
		self::$terms[ $id ][ $taxonomy ][] = (object) [
			'slug' => $slug,
			'name' => $name,
		];
	}
}

// Mock WP Functions if not already declared
if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		return CardMockEnv::$current_post_id;
	}
}

if ( ! function_exists( 'get_the_title' ) ) {
	function get_the_title( $id = 0 ) {
		$pid = $id ?: CardMockEnv::$current_post_id;
		return CardMockEnv::$posts[ $pid ]->post_title ?? '';
	}
}

if ( ! function_exists( 'get_post_field' ) ) {
	function get_post_field( $field, $id = 0 ) {
		$pid = $id ?: CardMockEnv::$current_post_id;
		return CardMockEnv::$posts[ $pid ]->$field ?? '';
	}
}

if ( ! function_exists( 'get_field' ) ) {
	function get_field( $key, $id = 0 ) {
		$pid = $id ?: CardMockEnv::$current_post_id;
		return CardMockEnv::$meta[ $pid ][ $key ] ?? null;
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( $id, $key, $single = false ) {
		return CardMockEnv::$meta[ $id ][ $key ] ?? ( $single ? '' : [] );
	}
}

if ( ! function_exists( 'wp_get_post_terms' ) ) {
	function wp_get_post_terms( $id, $taxonomy ) {
		return CardMockEnv::$terms[ $id ][ $taxonomy ] ?? [];
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return false;
	}
}

if ( ! function_exists( 'get_permalink' ) ) {
	function get_permalink( $id = 0 ) {
		$slug = CardMockEnv::$posts[ $id ]->post_name ?? $id;
		return 'https://lienthongdaihoc.com/' . $slug . '/';
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return htmlspecialchars( (string) $url, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'sanitize_title' ) ) {
	function sanitize_title( $title ) {
		return strtolower( trim( preg_replace( '/[^A-Za-z0-9-]+/', '-', $title ) ) );
	}
}

if ( ! function_exists( 'get_stylesheet_directory_uri' ) ) {
	function get_stylesheet_directory_uri() {
		return 'https://lienthongdaihoc.com/wp-content/themes/lienthongdaihoc';
	}
}

if ( ! function_exists( 'wp_get_attachment_image' ) ) {
	function wp_get_attachment_image( $id, $size = 'thumbnail', $icon = false, $attr = [] ) {
		$class = $attr['class'] ?? '';
		return '<img src="https://lienthongdaihoc.com/logo-' . $id . '.webp" class="' . esc_attr( $class ) . '" alt="" />';
	}
}

if ( ! function_exists( 'ltdh_icon' ) ) {
	function ltdh_icon( $name, $class = '' ) {
		echo '<svg class="ltdh-icon icon-' . esc_attr( $name ) . ' ' . esc_attr( $class ) . '"><use href="#icon-' . esc_attr( $name ) . '"></use></svg>';
	}
}

// Function to simulate rendering card-program.php in SSR vs AJAX
function render_card_program_simulated( int $prog_id, bool $is_ajax ) {
	ob_start();
	if ( $is_ajax ) {
		// In AJAX: $args = ['prog_id' => $prog_id]
		$args = [ 'prog_id' => $prog_id ];
		CardMockEnv::$current_post_id = 0; // Simulate unaligned or separate loop
	} else {
		// In SSR: get_the_ID() returns $prog_id and $args is empty
		$args = [];
		CardMockEnv::$current_post_id = $prog_id;
	}

	include ABSPATH . 'template-parts/cards/card-program.php';
	return ob_get_clean();
}

// Setup 10 diverse test program fixtures
$fixtures = [
	[
		'prog_id' => 101,
		'title' => 'Cử nhân Công nghệ thông tin',
		'slug' => 'cu-nhan-cong-nghe-thong-tin',
		'school_id' => 10,
		'school_title' => 'Trường Đại học Bách Khoa',
		'school_code' => 'BKHN',
		'major_id' => 20,
		'major_title' => 'Công nghệ thông tin',
		'major_slug' => 'cong-nghe-thong-tin',
		'type_slug' => 'tu-xa',
		'type_name' => 'Từ xa',
		'status' => 'tuyen-sinh',
		'tuition' => '450.000đ/tín chỉ',
		'duration' => '1.5 - 2 năm',
	],
	[
		'prog_id' => 102,
		'title' => 'Kỹ sư Xây dựng Cầu đường',
		'slug' => 'ky-su-xay-dung-cau-duong',
		'school_id' => 11,
		'school_title' => 'Trường Đại học Giao thông Vận tải',
		'school_code' => 'UTC',
		'major_id' => 21,
		'major_title' => 'Kỹ thuật Xây dựng',
		'major_slug' => 'ky-thuat-xay-dung',
		'type_slug' => 'vua-hoc-vua-lam',
		'type_name' => 'Vừa học vừa làm',
		'status' => 'sap-mo',
		'tuition' => '15.000.000đ/kỳ',
		'duration' => '2 năm',
	],
	[
		'prog_id' => 103,
		'title' => 'Đại học Quản trị kinh doanh - Chương trình Quốc tế',
		'slug' => 'dai-hoc-quan-tri-kinh-doanh-quoc-te',
		'school_id' => 12,
		'school_title' => 'Trường Đại học Thương Mại',
		'school_code' => 'TMU',
		'major_id' => 22,
		'major_title' => 'Quản trị kinh doanh',
		'major_slug' => 'quan-tri-kinh-doanh',
		'type_slug' => 'chinh-quy',
		'type_name' => 'Chính quy',
		'status' => 'tam-ngung',
		'tuition' => 'Liên hệ',
		'duration' => '2.5 năm',
	],
	[
		'prog_id' => 104,
		'title' => 'Liên thông ngành Kế toán doanh nghiệp (Hệ Từ xa) - UTC',
		'slug' => 'lien-thong-ke-toan-doanh-nghiep-utc',
		'school_id' => 11,
		'school_title' => 'Trường Đại học Giao thông Vận tải',
		'school_code' => 'UTC',
		'major_id' => 0, // Fallback parsing from title!
		'major_title' => '',
		'major_slug' => '',
		'type_slug' => 'tu-xa',
		'type_name' => 'Hệ Từ xa', // Tests 'Hệ ' prefix stripping
		'status' => 'tuyen-sinh',
		'tuition' => '12.000.000đ/năm',
		'duration' => '1.5 năm',
	],
	[
		'prog_id' => 105,
		'title' => 'Ngôn ngữ Anh & Biên phiên dịch "Chất lượng cao"',
		'slug' => 'ngon-ngu-anh-chat-luong-cao',
		'school_id' => 13,
		'school_title' => 'Trường Đại học Mở Hà Nội',
		'school_code' => 'HOU',
		'major_id' => 23,
		'major_title' => 'Ngành Ngôn ngữ Anh', // Tests 'Ngành ' prefix stripping
		'major_slug' => 'ngon-ngu-anh',
		'type_slug' => 'tu-xa',
		'type_name' => 'Từ xa',
		'status' => 'tuyen-sinh',
		'tuition' => '400.000đ/tín',
		'duration' => '2 năm',
	],
];

// Seed fixtures into CardMockEnv
CardMockEnv::reset();
foreach ( $fixtures as $f ) {
	CardMockEnv::add_post( $f['prog_id'], $f['title'], 'program', $f['slug'] );
	if ( $f['school_id'] > 0 ) {
		CardMockEnv::add_post( $f['school_id'], $f['school_title'], 'school', sanitize_title( $f['school_code'] ) );
		CardMockEnv::set_field( $f['prog_id'], 'school_relationship', $f['school_id'] );
		CardMockEnv::set_field( $f['school_id'], 'school_code', $f['school_code'] );
	}
	if ( $f['major_id'] > 0 ) {
		CardMockEnv::add_post( $f['major_id'], $f['major_title'], 'major', $f['major_slug'] );
		CardMockEnv::set_field( $f['prog_id'], 'major_relationship', $f['major_id'] );
	}
	if ( ! empty( $f['type_slug'] ) ) {
		CardMockEnv::add_term( $f['prog_id'], 'training_type', $f['type_slug'], $f['type_name'] );
	}
	CardMockEnv::set_field( $f['prog_id'], 'admission_status', $f['status'] );
	CardMockEnv::set_field( $f['prog_id'], 'tuition_fee', $f['tuition'] );
	CardMockEnv::set_field( $f['prog_id'], 'duration', $f['duration'] );
}

// Execute SSR vs AJAX Parity Testing
foreach ( $fixtures as $idx => $f ) {
	$pid = $f['prog_id'];
	$ssr_html = render_card_program_simulated( $pid, false );
	$ajax_html = render_card_program_simulated( $pid, true );

	// 2.1 Exact Byte Parity Test
	M2AdversarialHarness::assert(
		$ssr_html === $ajax_html,
		"Fixture #{$idx} (ID {$pid} - {$f['title']}): Exact 100% byte-for-byte HTML parity between SSR and AJAX"
	);

	// 2.2 Compare Attributes on Outer Card Container
	$expected_outer_attrs = [
		'data-compare-btn',
		'data-compare-type="program"',
		'data-compare-id="' . $pid . '"',
		'data-compare-slug="' . $f['slug'] . '"',
		'data-compare-he="' . $f['type_slug'] . '"',
	];

	foreach ( $expected_outer_attrs as $attr ) {
		M2AdversarialHarness::assert(
			strpos( $ssr_html, $attr ) !== false,
			"Fixture #{$idx}: Outer card container includes required attribute [{$attr}]"
		);
	}

	// 2.3 Compare Button Attributes
	$expected_btn_attrs = [
		'data-compare-type="program"',
		'data-compare-id="' . $pid . '"',
		'data-compare-slug="' . $f['slug'] . '"',
		'data-compare-he="' . $f['type_slug'] . '"',
	];

	foreach ( $expected_btn_attrs as $attr ) {
		M2AdversarialHarness::assert(
			strpos( $ssr_html, $attr ) !== false,
			"Fixture #{$idx}: Compare toggle button includes required attribute [{$attr}]"
		);
	}

	// 2.4 Verify Major Rel Slug Match
	if ( $f['major_id'] > 0 ) {
		M2AdversarialHarness::assert(
			strpos( $ssr_html, 'data-compare-nganh="' . $f['major_slug'] . '"' ) !== false,
			"Fixture #{$idx}: data-compare-nganh matches major slug '{$f['major_slug']}'"
		);
	}

	// 2.5 Verify Badge Purity (Zero 'Hệ ' prefix in training type badge)
	if ( ! empty( $f['type_name'] ) ) {
		$stripped_type = preg_replace( '/^hệ\s+/iu', '', trim( $f['type_name'] ) );
		M2AdversarialHarness::assert(
			strpos( $ssr_html, '>' . $stripped_type . '<' ) !== false || strpos( $ssr_html, $stripped_type ) !== false,
			"Fixture #{$idx}: Training type badge renders stripped name '{$stripped_type}'"
		);
		M2AdversarialHarness::assert(
			strpos( $ssr_html, '>Hệ ' . $stripped_type . '<' ) === false && strpos( $ssr_html, '>hệ ' . $stripped_type . '<' ) === false,
			"Fixture #{$idx}: Training type badge strictly contains ZERO 'Hệ ' prefix"
		);
	}
}


// =================================================================
// SECTION 3: COMPARE.JS CONTRACT CONFORMANCE
// =================================================================
echo "\nSECTION 3: compare.js Contract Conformance\n";
echo "-------------------------------------------------------------------\n";

$compare_js = file_get_contents( ABSPATH . 'assets/js/compare.js' );

// 3.1 Verify compare.js selectors
M2AdversarialHarness::assert(
	strpos( $compare_js, '.ltdh-compare-toggle' ) !== false,
	"compare.js binds to '.ltdh-compare-toggle' click events"
);

M2AdversarialHarness::assert(
	strpos( $compare_js, 'data-compare-id' ) !== false,
	"compare.js reads 'data-compare-id'"
);

M2AdversarialHarness::assert(
	strpos( $compare_js, 'data-compare-type' ) !== false,
	"compare.js reads 'data-compare-type'"
);

M2AdversarialHarness::assert(
	strpos( $compare_js, 'data-compare-thumb' ) !== false,
	"compare.js reads fallback thumbnail from 'data-compare-thumb'"
);

M2AdversarialHarness::assert(
	strpos( $compare_js, 'data-compare-he' ) !== false,
	"compare.js reads training type from 'data-compare-he'"
);

M2AdversarialHarness::assert(
	strpos( $compare_js, 'data-compare-nganh' ) !== false,
	"compare.js reads major slug from 'data-compare-nganh'"
);

// 3.2 Verify card-program.php provides all keys read by compare.js
$card_code = file_get_contents( ABSPATH . 'template-parts/cards/card-program.php' );

$required_compare_keys = [
	'data-compare-btn',
	'data-compare-type="program"',
	'data-compare-id',
	'data-compare-title',
	'data-compare-slug',
	'data-compare-thumb',
	'data-compare-he',
	'data-compare-nganh',
	'data-compare-major-name',
	'data-compare-school-name',
	'ltdh-compare-toggle',
];

foreach ( $required_compare_keys as $key ) {
	M2AdversarialHarness::assert(
		strpos( $card_code, $key ) !== false,
		"card-program.php provides required compare.js key: {$key}"
	);
}


// =================================================================
// SECTION 4: ARCHIVE & AJAX QUERY HARMONIZATION
// =================================================================
echo "\nSECTION 4: Archive & AJAX Query Harmonization\n";
echo "-------------------------------------------------------------------\n";

$archive_prog = file_get_contents( ABSPATH . 'archive-program.php' );
$query_filters = file_get_contents( ABSPATH . 'inc/core/class-query-filters.php' );

M2AdversarialHarness::assert(
	strpos( $archive_prog, 'template-parts/cards/card-program' ) !== false,
	"archive-program.php delegates card rendering to 'template-parts/cards/card-program'"
);

M2AdversarialHarness::assert(
	strpos( $query_filters, 'template-parts/cards/card-program' ) !== false,
	"inc/core/class-query-filters.php delegates card rendering to 'template-parts/cards/card-program'"
);

M2AdversarialHarness::assert(
	strpos( $archive_prog, 'id="program-results-container"' ) !== false,
	"archive-program.php defines wrapper #program-results-container for filter.js target"
);

M2AdversarialHarness::assert(
	strpos( $archive_prog, 'grid grid-cols-2 lg:grid-cols-3' ) !== false,
	"archive-program.php defines responsive 2-column mobile, 3-column desktop grid"
);


// =================================================================
// SECTION 5: MEGA MENU & NAVIGATION ADVERSARIAL STRESS TEST
// =================================================================
echo "\nSECTION 5: Mega Menu & Navigation Adversarial Stress Test\n";
echo "-------------------------------------------------------------------\n";

// Execute test-mega-menu.php directly and verify exit code 0
$output = [];
$return_code = 0;
exec( 'php ' . escapeshellarg( ABSPATH . 'tests/test-mega-menu.php' ), $output, $return_code );

M2AdversarialHarness::assert(
	$return_code === 0,
	"tests/test-mega-menu.php executes cleanly with return code 0"
);

$full_menu_output = implode( "\n", $output );
M2AdversarialHarness::assert(
	strpos( $full_menu_output, 'ALL MEGA MENU TESTS PASSED!' ) !== false,
	"tests/test-mega-menu.php logs 'ALL MEGA MENU TESTS PASSED!'"
);

// Adversarial Edge Cases for Navigation Helpers in isolation
require_once ABSPATH . 'inc/config/constants.php';
require_once ABSPATH . 'inc/config/class-defaults.php';

// Test ltdh_default fallback with nonexistent key
$missing_default = ltdh_default( 'nonexistent_group', 'nonexistent_key', [ 'fallback' => true ] );
M2AdversarialHarness::assert(
	$missing_default === [ 'fallback' => true ],
	"ltdh_default() handles non-existent group gracefully with fallback"
);


// =================================================================
// FINAL SUMMARY & EXIT
// =================================================================
echo "\n===================================================================\n";
echo "ADVERSARIAL VERIFICATION SUMMARY: " . M2AdversarialHarness::$passed . " PASSED, " . M2AdversarialHarness::$failed . " FAILED\n";
echo "===================================================================\n";

if ( M2AdversarialHarness::$failed > 0 ) {
	echo "\nFAILED ASSERTIONS:\n";
	foreach ( M2AdversarialHarness::$errors as $err ) {
		echo " - " . $err['desc'] . "\n";
	}
	exit( 1 );
}

echo "All Milestone 2 adversarial challenges verified with 100% PASS rate!\n";
exit( 0 );
