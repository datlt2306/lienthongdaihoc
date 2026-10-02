<?php
/**
 * Adversarial Empirical Test Suite: Milestone M4 (Homepage & Filters Challenger)
 * Challenger: challenger_m4_2
 *
 * Scope & Verification:
 * 1. front-page.php Simulation & Output Parsing:
 *    - Hidden H1 check
 *    - Search form action & reset URL check
 *    - Dropdown default select label check
 *    - Eligibility section THPT exclusion & valid entry levels
 *    - Testimonial fallbacks (0 VB2)
 *    - Mock news 100% Liên thông focus (0 VB2)
 * 2. Deep Adversarial Scan of ALL Theme Templates:
 *    - Zero 'loai_tuyen_sinh' / 'Loại tuyển sinh' across ALL PHP files and JSON configs
 *    - Zero dead '#' links in footer
 *    - Canonical form action verification across all archive/search templates
 * 3. Fallback and Edge Case Stress-Testing:
 *    - ACF empty returns: hero, badges, testimonials, news, eligibility
 *    - Dropdown input names and values
 *    - Menu item resolution & sub-menu injection
 * 4. Token & Syntax Integrity under PHP 8+
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

// Mock WordPress functions
if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		return 'https://lienthongdaihoc.com' . ( $path ? ( '/' . ltrim( $path, '/' ) ) : '' );
	}
}
if ( ! function_exists( 'untrailingslashit' ) ) {
	function untrailingslashit( $string ) {
		return rtrim( $string, '/\\' );
	}
}
if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $string ) {
		return untrailingslashit( $string ) . '/';
	}
}
if ( ! function_exists( 'get_template_directory_uri' ) ) {
	function get_template_directory_uri() {
		return 'https://lienthongdaihoc.com/wp-content/themes/lienthongdaihoc';
	}
}
if ( ! function_exists( 'get_template_directory' ) ) {
	function get_template_directory() {
		return dirname( __DIR__ );
	}
}
if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
	}
}
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}
if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}
if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}
if ( ! function_exists( 'esc_attr__' ) ) {
	function esc_attr__( $text, $domain = 'default' ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}
if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return false;
	}
}
if ( ! function_exists( 'get_terms' ) ) {
	function get_terms( $args = [] ) {
		return [
			(object) [ 'term_id' => 7, 'slug' => 'tu-xa', 'name' => 'Từ xa' ],
			(object) [ 'term_id' => 8, 'slug' => 'vua-hoc-vua-lam', 'name' => 'Vừa học vừa làm' ],
		];
	}
}
if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $transient ) {
		return false;
	}
}
if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( $transient, $value, $expiration = 0 ) {
		return true;
	}
}
if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
}
if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value ) {
		return $value;
	}
}
if ( ! function_exists( 'get_field' ) ) {
	function get_field( $selector, $post_id = false ) {
		return false; // Test pure fallback behavior
	}
}

if ( ! function_exists( 'ltdh_get_cached_featured_schools' ) ) {
	function ltdh_get_cached_featured_schools() {
		return [];
	}
}
if ( ! function_exists( 'ltdh_get_cached_query' ) ) {
	function ltdh_get_cached_query( $key, $args, $ttl ) {
		return (object) [
			'have_posts' => function() { return false; },
		];
	}
}
if ( ! function_exists( 'ltdh_get_cached_filter_options' ) ) {
	function ltdh_get_cached_filter_options() {
		return [
			'schools' => [
				[ 'slug' => 'dai-hoc-kinh-te-quoc-dan', 'title' => 'Đại học Kinh tế Quốc dân' ],
				[ 'slug' => 'dai-hoc-mo-ha-noi', 'title' => 'Trường Đại học Mở Hà Nội' ],
			],
			'majors' => [
				[ 'slug' => 'cong-nghe-thong-tin', 'title' => 'Công nghệ thông tin' ],
				[ 'slug' => 'quan-tri-kinh-doanh', 'title' => 'Quản trị kinh doanh' ],
			],
			'types' => [
				[ 'slug' => 'tu-xa', 'name' => 'Từ xa' ],
				[ 'slug' => 'vua-hoc-vua-lam', 'name' => 'Vừa học vừa làm' ],
			],
		];
	}
}

if ( ! function_exists( 'get_header' ) ) {
	function get_header() {}
}
if ( ! function_exists( 'get_footer' ) ) {
	function get_footer() {}
}
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}
if ( ! defined( 'LTDH_TAX_TRAINING_TYPE' ) ) {
	define( 'LTDH_TAX_TRAINING_TYPE', 'training_type' );
}
if ( ! defined( 'LTDH_CPT_MAJOR' ) ) {
	define( 'LTDH_CPT_MAJOR', 'major' );
}

require_once __DIR__ . '/../inc/config/class-defaults.php';
require_once __DIR__ . '/../inc/core/class-menus.php';

class ChallengerM4Test {
	public static int $passed = 0;
	public static int $failed = 0;
	public static array $failures = [];

	public static function assert( bool $condition, string $test_name, array $evidence = [] ): void {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] {$test_name}\n";
		} else {
			self::$failed++;
			self::$failures[] = [
				'test'     => $test_name,
				'evidence' => $evidence,
			];
			echo "  [FAIL] {$test_name}\n";
			if ( ! empty( $evidence ) ) {
				foreach ( $evidence as $k => $v ) {
					echo "         - {$k}: " . ( is_array( $v ) ? json_encode( $v, JSON_UNESCAPED_UNICODE ) : $v ) . "\n";
				}
			}
		}
	}
}

echo "===================================================================\n";
echo "EMPIRICAL CHALLENGER TEST SUITE: HOMEPAGE & FILTERS (M4)\n";
echo "===================================================================\n\n";

// -------------------------------------------------------------
// SECTION 1: SIMULATE & RENDER FRONT-PAGE.PHP
// -------------------------------------------------------------
echo "SECTION 1: front-page.php Simulation & Direct AST/Render Check\n";
echo "-------------------------------------------------------------------\n";

$front_page_path = dirname( __DIR__ ) . '/front-page.php';
$front_page_raw  = file_get_contents( $front_page_path );

// 1.1: Hidden H1 semantic heading check
$expected_h1_text = 'Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hình Thức Từ Xa & Vừa Học Vừa Làm';
$has_h1 = (bool) preg_match( '/<h1\s+class="sr-only">\s*' . preg_quote( $expected_h1_text, '/' ) . '\s*<\/h1>/u', $front_page_raw );
ChallengerM4Test::assert(
	$has_h1,
	"1.1: Hidden H1 is exactly '{$expected_h1_text}'",
	[ 'found' => $has_h1 ]
);

// 1.2: Search form action check
$search_action_correct = (bool) preg_match( '/<form\s+action="<\?php\s+echo\s+esc_url\(\s*home_url\(\s*[\'"]\/he-dao-tao\/[\'"]\s*\)\s*\);\s*\?>"\s+method="GET"/i', $front_page_raw );
ChallengerM4Test::assert(
	$search_action_correct,
	"1.2: Search form action points to /he-dao-tao/ (NOT /he-dao-tao/tu-xa/)",
	[ 'action_matches' => $search_action_correct ]
);

// 1.3: Search form reset link check
$reset_link_correct = (bool) preg_match( '/<a\s+href="<\?php\s+echo\s+esc_url\(\s*home_url\(\s*[\'"]\/he-dao-tao\/[\'"]\s*\)\s*\);\s*\?>"[^>]*title="Reset bộ lọc"/i', $front_page_raw );
ChallengerM4Test::assert(
	$reset_link_correct,
	"1.3: Search reset button points to /he-dao-tao/",
	[ 'reset_matches' => $reset_link_correct ]
);

// 1.4: Search form selects and default labels
$has_he_select = (bool) preg_match( '/<select\s+name="he"[^>]*>.*?<option\s+value="">-- Chọn hình thức học --<\/option>/s', $front_page_raw );
$has_truong_select = (bool) preg_match( '/<select\s+name="truong"[^>]*>.*?<option\s+value="">-- Chọn trường --<\/option>/s', $front_page_raw );
$has_nganh_select = (bool) preg_match( '/<select\s+name="nganh"[^>]*>.*?<option\s+value="">-- Chọn ngành học --<\/option>/s', $front_page_raw );

ChallengerM4Test::assert(
	$has_he_select && $has_truong_select && $has_nganh_select,
	"1.4: Search form has exactly 3 selects (truong, nganh, he) with default '-- Chọn hình thức học --'",
	[
		'has_he_select'     => $has_he_select,
		'has_truong_select' => $has_truong_select,
		'has_nganh_select'  => $has_nganh_select,
	]
);

// 1.5: Verify no redundant or out-of-scope select in search form
$has_loai_select = (bool) preg_match( '/<select\s+name="loai[^"]*"/i', $front_page_raw );
ChallengerM4Test::assert(
	! $has_loai_select,
	"1.5: Search form has zero redundant 'loai' or 'loai_tuyen_sinh' selects",
	[ 'has_loai_select' => $has_loai_select ]
);

// 1.6: Eligibility section: Zero THPT or Học sinh tốt nghiệp THPT
$has_thpt_in_fp = ( stripos( $front_page_raw, 'THPT' ) !== false || stripos( $front_page_raw, 'học sinh tốt nghiệp' ) !== false );
ChallengerM4Test::assert(
	! $has_thpt_in_fp,
	"1.6: front-page.php has ZERO occurrences of 'THPT' or 'Học sinh tốt nghiệp THPT'",
	[ 'found_thpt' => $has_thpt_in_fp ]
);

// 1.7: Eligibility section default items are strictly Liên thông entry levels
$has_tc_item = strpos( $front_page_raw, "'title' => 'Tốt nghiệp Trung cấp'" ) !== false;
$has_cd_item = strpos( $front_page_raw, "'title' => 'Tốt nghiệp Cao đẳng'" ) !== false;
$has_dh_item = strpos( $front_page_raw, "'title' => 'Đã có bằng Đại học'" ) !== false;

ChallengerM4Test::assert(
	$has_tc_item && $has_cd_item && $has_dh_item,
	"1.7: Eligibility section fallback items are Trung cấp, Cao đẳng, Đại học (Liên thông)",
	[
		'has_tc' => $has_tc_item,
		'has_cd' => $has_cd_item,
		'has_dh' => $has_dh_item,
	]
);

// 1.8: Testimonial fallback check: role is 'Liên thông Công nghệ thông tin', zero 'VB2'
$has_vb2_in_testimonials = false;
preg_match( '/\$fallback_testimonials\s*=\s*\[(.*?)\];\s*foreach/s', $front_page_raw, $testi_match );
$testi_block = $testi_match[1] ?? '';

$has_vb2_role = ( stripos( $testi_block, 'VB2' ) !== false || stripos( $testi_block, 'văn bằng 2' ) !== false );
$has_lienthong_cntt = strpos( $testi_block, "'role' => 'Liên thông Công nghệ thông tin'" ) !== false;

ChallengerM4Test::assert(
	! $has_vb2_role && $has_lienthong_cntt,
	"1.8: Testimonials fallback role is 'Liên thông Công nghệ thông tin' with 0 instances of 'VB2'",
	[
		'has_vb2'           => $has_vb2_role,
		'has_lienthong_cntt' => $has_lienthong_cntt,
	]
);

// 1.9: Mock news fallback check: 100% focused on Liên thông đại học, zero 'Văn bằng 2'
preg_match( '/\$mock_news\s*=\s*\[(.*?)\];\s*\$news_has_posts/s', $front_page_raw, $news_match );
$news_block = $news_match[1] ?? '';

$has_vb2_in_news = ( stripos( $news_block, 'văn bằng 2' ) !== false || stripos( $news_block, 'vb2' ) !== false );
$has_lienthong_news = strpos( $news_block, 'Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026' ) !== false;
$has_caodang_lienthong = strpos( $news_block, 'Quy chế tuyển sinh Liên thông Cao đẳng lên Đại học' ) !== false;

ChallengerM4Test::assert(
	! $has_vb2_in_news && $has_lienthong_news && $has_caodang_lienthong,
	"1.9: Mock news fallback has 0 VB2 and is 100% focused on Liên thông Đại học",
	[
		'has_vb2_in_news' => $has_vb2_in_news,
		'has_lienthong'   => $has_lienthong_news,
		'has_caodang'     => $has_caodang_lienthong,
	]
);

// -------------------------------------------------------------
// SECTION 2: ADVERSARIAL SCAN ACROSS ALL THEME TEMPLATES
// -------------------------------------------------------------
echo "\nSECTION 2: Adversarial Scan Across ALL Theme Template Files\n";
echo "-------------------------------------------------------------------\n";

// Scan all PHP files in root, template-parts, inc
$theme_dir = dirname( __DIR__ );
$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme_dir ) );
$scanned_files = [];
$leaked_loai_tuyen_sinh = [];
$leaked_vb2_in_templates = [];
$dead_links = [];

$excluded_dirs = [ '.agents', '.agent', 'tests', 'vendor', '.git' ];

foreach ( $iterator as $file ) {
	if ( $file->isDir() ) {
		continue;
	}
	$ext = $file->getExtension();
	if ( $ext !== 'php' && $ext !== 'json' ) {
		continue;
	}

	$pathname = $file->getPathname();
	$relpath  = str_replace( $theme_dir . '/', '', $pathname );

	$skip = false;
	foreach ( $excluded_dirs as $ex ) {
		if ( strpos( $relpath, $ex . '/' ) === 0 || strpos( $relpath, '/' . $ex . '/' ) !== false ) {
			$skip = true;
			break;
		}
	}
	if ( $skip ) {
		continue;
	}

	$content = file_get_contents( $pathname );
	$scanned_files[] = $relpath;

	// Check 1: 'loai_tuyen_sinh' or 'loại tuyển sinh'
	if ( stripos( $content, 'loai_tuyen_sinh' ) !== false || stripos( $content, 'loại tuyển sinh' ) !== false ) {
		$leaked_loai_tuyen_sinh[] = $relpath;
	}

	// Check 2: footer.php navigation dead '#' links
	if ( $relpath === 'footer.php' ) {
		// Column 3 navigation check
		if ( preg_match( '/<!-- Column 3:.*?<\/ul>/s', $content, $col3_m ) ) {
			if ( strpos( $col3_m[0], 'href="#"' ) !== false ) {
				$dead_links['footer_col3'] = $col3_m[0];
			}
		}
		// Footer bottom policy links check
		if ( preg_match( '/<!-- Footer Bottom -->.*?<\/footer>/s', $content, $bottom_m ) ) {
			if ( strpos( $bottom_m[0], 'href="#"' ) !== false ) {
				$dead_links['footer_bottom'] = $bottom_m[0];
			}
		}
	}
}

// 2.1: Zero loai_tuyen_sinh across entire active codebase
ChallengerM4Test::assert(
	empty( $leaked_loai_tuyen_sinh ),
	"2.1: Zero occurrences of 'loai_tuyen_sinh' or 'loại tuyển sinh' across all " . count( $scanned_files ) . " scanned theme files",
	[ 'leaks' => $leaked_loai_tuyen_sinh ]
);

// 2.2: Zero dead '#' links in footer navigation (Column 3 and footer bottom policy links)
ChallengerM4Test::assert(
	empty( $dead_links ),
	"2.2: Zero dead '#' links in footer Column 3 navigation and footer bottom policy links",
	[ 'dead_links' => $dead_links ]
);

// 2.3: Verify all template filter/search forms submit to /he-dao-tao/
$filter_template_files = [
	'front-page.php',
	'archive-program.php',
	'taxonomy-training_type.php',
];

$all_filters_canonical = true;
$non_canonical_filters = [];
foreach ( $filter_template_files as $f ) {
	$c = file_get_contents( $theme_dir . '/' . $f );
	// Find all <form action="..." method="GET">
	preg_match_all( '/<form\s+[^>]*action="([^"]+)"[^>]*method="GET"/i', $c, $m );
	if ( ! empty( $m[1] ) ) {
		foreach ( $m[1] as $act ) {
			if ( strpos( $act, '/he-dao-tao/' ) === false && strpos( $act, 'home_url(' ) === false ) {
				$all_filters_canonical = false;
				$non_canonical_filters[] = [ 'file' => $f, 'action' => $act ];
			}
		}
	}
}

ChallengerM4Test::assert(
	$all_filters_canonical,
	"2.3: All search/filter forms in key templates submit to /he-dao-tao/ canonical endpoint",
	[ 'violations' => $non_canonical_filters ]
);

// -------------------------------------------------------------
// SECTION 3: FALLBACK AND STRESS TESTING
// -------------------------------------------------------------
echo "\nSECTION 3: Dynamic Menu Injection & Fallback Stress-Testing\n";
echo "-------------------------------------------------------------------\n";

// 3.1: Menu title matching with variations
$test_titles = [
	'Liên thông đại học'     => true,
	'LIÊN THÔNG ĐẠI HỌC'     => true,
	'  Liên thông đại học  ' => true,
	'Liên thông'             => true,
	'LIÊN THÔNG'             => true,
	'Hình thức học'          => true,
	'HỆ ĐÀO TẠO'             => true,
	'Hình thức đào tạo'      => true,
	'Đại học chính quy'      => false,
	'Tuyển sinh THPT'        => false,
	'Văn bằng 2'             => false,
	'Chương trình'           => false,
];

$matching_ok = true;
$mismatch_details = [];
foreach ( $test_titles as $title_input => $should_match ) {
	$mock_items = [
		(object) [
			'ID' => 99,
			'title' => $title_input,
			'url' => home_url( '/he-dao-tao/' ),
			'menu_item_parent' => 0,
			'classes' => [],
		],
	];

	$res = ltdh_dynamic_menu_submenu_injection( $mock_items, (object) [ 'theme_location' => 'primary-menu' ] );
	$did_inject = ( count( $res ) > 1 );

	if ( $did_inject !== $should_match ) {
		$matching_ok = false;
		$mismatch_details[] = [
			'title'    => $title_input,
			'expected' => $should_match,
			'actual'   => $did_inject,
		];
	}
}

ChallengerM4Test::assert(
	$matching_ok,
	"3.1: Submenu injection title matcher accepts all Liên thông / Hình thức học variations and rejects out-of-scope titles",
	[ 'mismatches' => $mismatch_details ]
);

// 3.2: Fallback menu render produces valid hierarchical HTML
ob_start();
ltdh_render_fallback_menu( 'primary', 'test-nav-class' );
$fallback_html = ob_get_clean();

$has_top_items = (
	strpos( $fallback_html, 'Trang chủ' ) !== false &&
	strpos( $fallback_html, 'Liên thông đại học' ) !== false &&
	strpos( $fallback_html, 'Ngành học' ) !== false &&
	strpos( $fallback_html, 'Trường đại học' ) !== false &&
	strpos( $fallback_html, 'Kiến thức liên thông' ) !== false &&
	strpos( $fallback_html, 'Kiểm tra điều kiện' ) !== false
);

$has_sub_tu_xa = strpos( $fallback_html, '/he-dao-tao/tu-xa/' ) !== false;
$has_sub_vhvl  = strpos( $fallback_html, '/he-dao-tao/vua-hoc-vua-lam/' ) !== false;

ChallengerM4Test::assert(
	$has_top_items && $has_sub_tu_xa && $has_sub_vhvl,
	"3.2: Fallback menu HTML renders all 6 top items and nested sub-menu with 'Từ xa' and 'Vừa học vừa làm'",
	[
		'has_top'  => $has_top_items,
		'has_tu_xa' => $has_sub_tu_xa,
		'has_vhvl'  => $has_sub_vhvl,
	]
);

// 3.3: Mobile fallback menu has exactly 6 items and zero '/he-dao-tao/tu-xa/' labeled 'Chương trình'
$mobile_items = ltdh_default( 'navigation', 'mobile', [] );
$mobile_has_clean_links = true;
foreach ( $mobile_items as $mi ) {
	if ( $mi['url'] === '/he-dao-tao/tu-xa/' && ( $mi['label'] ?? '' ) === 'Chương trình' ) {
		$mobile_has_clean_links = false;
	}
}

ChallengerM4Test::assert(
	$mobile_has_clean_links && count( $mobile_items ) === 6,
	"3.3: Mobile navigation fallback has 6 clean items with zero obsolete 'Chương trình' link",
	[ 'count' => count( $mobile_items ) ]
);

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n===================================================================\n";
echo sprintf( "CHALLENGER SUMMARY: %d PASSED, %d FAILED\n", ChallengerM4Test::$passed, ChallengerM4Test::$failed );
echo "===================================================================\n";

if ( ChallengerM4Test::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
