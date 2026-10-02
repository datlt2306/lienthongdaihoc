<?php
/**
 * Empirical Test Harness for Milestone M4: Navigation, Homepage & Filters
 *
 * Verifies:
 * 1. Header Navigation Menu & Fallback Structure (Menu ID 3, primary, mobile, submenu injection)
 * 2. Footer Navigation Column 3 & Policy Links (in-scope links, zero dead '#' links, zero out-of-scope items)
 * 3. Homepage Alignment (hidden H1, search form action, eligibility entry levels, testimonial & news fallbacks, hero badge)
 * 4. Filter Harmonization (all filter forms submit to /he-dao-tao/, zero redundant filters)
 * 5. PHP Syntax integrity
 *
 * Run via: php tests/test-m4-navigation-homepage.php
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

// Mock WordPress functions if not loaded in CLI
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
if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
}
if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $transient ) {
		return [
			(object) [ 'ID' => 101, 'post_title' => 'Công nghệ thông tin' ],
			(object) [ 'ID' => 102, 'post_title' => 'Quản trị kinh doanh' ],
		];
	}
}
if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( $transient, $value, $expiration = 0 ) {
		return true;
	}
}
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}
if ( ! function_exists( 'get_permalink' ) ) {
	function get_permalink( $id = 0 ) {
		return home_url( '/nganh-hoc/post-' . $id . '/' );
	}
}
if ( ! defined( 'LTDH_TAX_TRAINING_TYPE' ) ) {
	define( 'LTDH_TAX_TRAINING_TYPE', 'training_type' );
}
if ( ! defined( 'LTDH_CPT_MAJOR' ) ) {
	define( 'LTDH_CPT_MAJOR', 'major' );
}

// Load theme files
require_once __DIR__ . '/../inc/config/class-defaults.php';
require_once __DIR__ . '/../inc/core/class-menus.php';

class M4Tester {
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
echo "EMPIRICAL TEST SUITE: M4 NAVIGATION, HOMEPAGE & FILTERS\n";
echo "===================================================================\n\n";

// -------------------------------------------------------------
// SECTION 1: HEADER NAVIGATION MENU & FALLBACK STRUCTURE
// -------------------------------------------------------------
echo "SECTION 1: Header Navigation Menu & Fallbacks\n";
echo "-------------------------------------------------------------------\n";

$primary_defaults = ltdh_default( 'navigation', 'primary', [] );
$mobile_defaults  = ltdh_default( 'navigation', 'mobile', [] );

// 1.1: Standard 6 items in primary navigation
M4Tester::assert(
	count( $primary_defaults ) === 6,
	"1.1: Primary navigation defaults have exactly 6 top-level items",
	[ 'count' => count( $primary_defaults ) ]
);

// 1.2: Top-level item labels and URLs match specification
$expected_items = [
	[ 'url' => '/',                    'label' => 'Trang chủ' ],
	[ 'url' => '/he-dao-tao/',        'label' => 'Liên thông đại học' ],
	[ 'url' => '/nganh-hoc/',          'label' => 'Ngành học' ],
	[ 'url' => '/truong-doi-tac/',     'label' => 'Trường đại học' ],
	[ 'url' => '/tin-tuc/',            'label' => 'Kiến thức liên thông' ],
	[ 'url' => '/kiem-tra-dieu-kien/',  'label' => 'Kiểm tra điều kiện' ],
];

$all_matched = true;
foreach ( $expected_items as $idx => $exp ) {
	if ( ( $primary_defaults[ $idx ]['url'] ?? '' ) !== $exp['url'] || ( $primary_defaults[ $idx ]['label'] ?? '' ) !== $exp['label'] ) {
		$all_matched = false;
		break;
	}
}
M4Tester::assert(
	$all_matched,
	"1.2: Primary navigation default items follow exact order: Trang chủ, Liên thông đại học, Ngành học, Trường đại học, Kiến thức liên thông, Kiểm tra điều kiện",
	[ 'actual' => $primary_defaults, 'expected' => $expected_items ]
);

// 1.3: Sub-items under 'Liên thông đại học' contain 'Từ xa' and 'Vừa học vừa làm'
$he_dao_tao_item = $primary_defaults[1] ?? [];
$sub_items = $he_dao_tao_item['sub'] ?? [];
$has_tu_xa = false;
$has_vhvl  = false;
foreach ( $sub_items as $sub ) {
	if ( $sub['url'] === '/he-dao-tao/tu-xa/' && strpos( $sub['label'], 'Từ xa' ) !== false ) {
		$has_tu_xa = true;
	}
	if ( $sub['url'] === '/he-dao-tao/vua-hoc-vua-lam/' && strpos( $sub['label'], 'Vừa học vừa làm' ) !== false ) {
		$has_vhvl = true;
	}
}
M4Tester::assert(
	$has_tu_xa && $has_vhvl,
	"1.3: Fallback primary menu item 'Liên thông đại học' has sub-items for '/he-dao-tao/tu-xa/' and '/he-dao-tao/vua-hoc-vua-lam/'",
	[ 'sub_items' => $sub_items ]
);

// 1.4: Mobile fallback navigation points to '/he-dao-tao/' and has zero rogue '/he-dao-tao/tu-xa/' labeled 'Chương trình'
$mobile_has_rogue_link = false;
foreach ( $mobile_defaults as $m_item ) {
	if ( $m_item['url'] === '/he-dao-tao/tu-xa/' && ( $m_item['label'] ?? '' ) === 'Chương trình' ) {
		$mobile_has_rogue_link = true;
	}
}
M4Tester::assert(
	! $mobile_has_rogue_link,
	"1.4: Mobile fallback menu does NOT contain obsolete '/he-dao-tao/tu-xa/' labeled 'Chương trình'",
	[ 'mobile_defaults' => $mobile_defaults ]
);

// 1.5: Render fallback menu outputs valid HTML with sub-menu
ob_start();
ltdh_render_fallback_menu( 'primary', 'nav-primary-menu' );
$rendered_fallback = ob_get_clean();

M4Tester::assert(
	strpos( $rendered_fallback, 'nav-primary-menu' ) !== false &&
	strpos( $rendered_fallback, 'Liên thông đại học' ) !== false &&
	strpos( $rendered_fallback, 'sub-menu' ) !== false &&
	strpos( $rendered_fallback, '/he-dao-tao/tu-xa/' ) !== false,
	"1.5: ltdh_render_fallback_menu('primary') produces valid markup with nested sub-menus",
	[ 'rendered_preview' => substr( $rendered_fallback, 0, 300 ) ]
);

// 1.6: Dynamic submenu injection test on 'Liên thông đại học'
$mock_nav_items = [
	(object) [
		'ID' => 1,
		'title' => 'Liên thông đại học',
		'url' => home_url( '/he-dao-tao/' ),
		'menu_item_parent' => 0,
		'classes' => [],
	],
	(object) [
		'ID' => 2,
		'title' => 'Ngành học',
		'url' => home_url( '/nganh-hoc/' ),
		'menu_item_parent' => 0,
		'classes' => [],
	],
];

$injected = ltdh_dynamic_menu_submenu_injection( $mock_nav_items, (object) [ 'theme_location' => 'primary-menu' ] );
$found_injected_tu_xa = false;
$found_injected_vhvl  = false;
$parent_has_child_class = false;

foreach ( $injected as $item ) {
	if ( $item->ID === 1 && in_array( 'menu-item-has-children', $item->classes, true ) ) {
		$parent_has_child_class = true;
	}
	if ( (int) ( $item->menu_item_parent ?? 0 ) === 1 ) {
		if ( strpos( $item->url, '/he-dao-tao/tu-xa/' ) !== false ) {
			$found_injected_tu_xa = true;
		}
		if ( strpos( $item->url, '/he-dao-tao/vua-hoc-vua-lam/' ) !== false ) {
			$found_injected_vhvl = true;
		}
	}
}

M4Tester::assert(
	$found_injected_tu_xa && $found_injected_vhvl && $parent_has_child_class,
	"1.6: Dynamic submenu injection successfully injects training types under 'Liên thông đại học' and sets menu-item-has-children",
	[
		'found_tu_xa' => $found_injected_tu_xa,
		'found_vhvl'  => $found_injected_vhvl,
		'parent_class' => $parent_has_child_class,
	]
);

// -------------------------------------------------------------
// SECTION 2: FOOTER NAVIGATION (footer.php)
// -------------------------------------------------------------
echo "\nSECTION 2: Footer Navigation Column 3 & Policy Links\n";
echo "-------------------------------------------------------------------\n";

$footer_content = file_get_contents( __DIR__ . '/../footer.php' );

// 2.1: Column 3 has zero dead '#' links
preg_match( '/<!-- Column 3:.*?<\/ul>/s', $footer_content, $col3_match );
$col3_html = $col3_match[0] ?? '';

$has_hash_link_in_col3 = strpos( $col3_html, 'href="#"' ) !== false;
M4Tester::assert(
	! $has_hash_link_in_col3 && ! empty( $col3_html ),
	"2.1: Footer Column 3 contains zero dead '#' links",
	[ 'col3_preview' => $col3_html ]
);

// 2.2: Column 3 has zero out-of-scope items
$out_of_scope_patterns = [
	'Cao đẳng online',
	'VB2',
	'Liên thông Đại Học chính quy',
	'Trung Cấp lên Đại học',
	'Đại học tại chức',
	'VLVH',
];
$found_out_of_scope = [];
foreach ( $out_of_scope_patterns as $pattern ) {
	if ( stripos( $col3_html, $pattern ) !== false ) {
		$found_out_of_scope[] = $pattern;
	}
}
M4Tester::assert(
	empty( $found_out_of_scope ),
	"2.2: Footer Column 3 contains zero out-of-scope items (VB2, Cao đẳng online, Chính quy, v.v.)",
	[ 'found' => $found_out_of_scope ]
);

// 2.3: Column 3 contains all 5 required in-scope links
$required_footer_links = [
	'/he-dao-tao/tu-xa/'       => 'Liên thông Đại học Từ xa',
	'/he-dao-tao/vua-hoc-vua-lam/' => 'Liên thông Vừa học vừa làm',
	'/truong-doi-tac/'         => 'Trường đại học tuyển sinh',
	'/nganh-hoc/'              => 'Ngành học liên thông',
	'/kiem-tra-dieu-kien/'     => 'Kiểm tra điều kiện',
];

$all_footer_links_present = true;
$missing_footer_links = [];
foreach ( $required_footer_links as $url => $label ) {
	if ( strpos( $col3_html, $url ) === false || strpos( $col3_html, $label ) === false ) {
		$all_footer_links_present = false;
		$missing_footer_links[] = [ 'url' => $url, 'label' => $label ];
	}
}
M4Tester::assert(
	$all_footer_links_present,
	"2.3: Footer Column 3 contains all 5 required valid in-scope links and labels",
	[ 'missing' => $missing_footer_links ]
);

// 2.4: Footer policy links point to real URLs
$has_policy_link = strpos( $footer_content, '/chinh-sach-bao-mat/' ) !== false;
$has_terms_link  = strpos( $footer_content, '/dieu-khoan/' ) !== false;
M4Tester::assert(
	$has_policy_link && $has_terms_link,
	"2.4: Footer policy links point to /chinh-sach-bao-mat/ and /dieu-khoan/ (zero '#' links in footer bottom)",
	[ 'policy' => $has_policy_link, 'terms' => $has_terms_link ]
);

// -------------------------------------------------------------
// SECTION 3: HOMEPAGE ALIGNMENT (front-page.php & class-defaults.php)
// -------------------------------------------------------------
echo "\nSECTION 3: Homepage Alignment & Fallbacks\n";
echo "-------------------------------------------------------------------\n";

$front_page_content = file_get_contents( __DIR__ . '/../front-page.php' );

// 3.1: Hidden semantic H1
$expected_h1 = '<h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hình Thức Từ Xa & Vừa Học Vừa Làm</h1>';
M4Tester::assert(
	strpos( $front_page_content, $expected_h1 ) !== false,
	"3.1: front-page.php hidden H1 is 'Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hình Thức Từ Xa & Vừa Học Vừa Làm'",
	[ 'found' => strpos( $front_page_content, 'Cổng Thông Tin Tuyển Sinh' ) !== false ]
);

// 3.2: Homepage search form action
preg_match( '/<form\s+action="([^"]+)"[^>]*method="GET"/i', $front_page_content, $form_match );
$form_action_raw = $form_match[1] ?? '';
M4Tester::assert(
	strpos( $form_action_raw, "home_url( '/he-dao-tao/' )" ) !== false || strpos( $form_action_raw, "home_url('/he-dao-tao/')" ) !== false,
	"3.2: front-page.php search form action submits to home_url('/he-dao-tao/') (NOT /he-dao-tao/tu-xa/)",
	[ 'form_action' => $form_action_raw ]
);

// 3.3: Homepage search form reset link
M4Tester::assert(
	preg_match( '/<a\s+href="<\?php\s+echo\s+esc_url\(\s*home_url\(\s*[\'"]\/he-dao-tao\/[\'"]\s*\)\s*\);\s*\?>"[^>]*title="Reset bộ lọc"/i', $front_page_content ) === 1,
	"3.3: front-page.php search reset button points to home_url('/he-dao-tao/')",
	[]
);

// 3.4: Eligibility checker default entry levels
M4Tester::assert(
	strpos( $front_page_content, 'Tốt nghiệp Trung cấp' ) !== false &&
	strpos( $front_page_content, 'Tốt nghiệp Cao đẳng' ) !== false &&
	strpos( $front_page_content, 'Đã có bằng Đại học' ) !== false &&
	stripos( $front_page_content, 'Học sinh tốt nghiệp THPT' ) === false,
	"3.4: front-page.php eligibility section contains zero THPT options; entry levels are Trung cấp, Cao đẳng, Đại học",
	[]
);

// 3.5: Testimonial fallback
M4Tester::assert(
	strpos( $front_page_content, "'role' => 'Liên thông Công nghệ thông tin'" ) !== false &&
	strpos( $front_page_content, "'role' => 'VB2 Công nghệ thông tin'" ) === false,
	"3.5: front-page.php testimonial fallback role is 'Liên thông Công nghệ thông tin' (zero VB2)",
	[]
);

// 3.6: News fallback
M4Tester::assert(
	strpos( $front_page_content, 'Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026' ) !== false &&
	strpos( $front_page_content, 'Điều kiện học Văn bằng 2 đại học năm 2026' ) === false,
	"3.6: front-page.php news fallback is 'Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026' (zero VB2)",
	[]
);

// 3.7: Defaults hero badge
$hero_badge_2 = ltdh_default( 'homepage', 'hero_badge_2', '' );
$hero_badges  = ltdh_default( 'homepage', 'hero_badges', [] );
$first_badge_subtext = $hero_badges[0]['subtext'] ?? '';

M4Tester::assert(
	$hero_badge_2 === '50+ chương trình Liên thông Đại học: Từ xa & Vừa học vừa làm' &&
	$first_badge_subtext === 'Liên thông Đại học: Từ xa & Vừa học vừa làm',
	"3.7: class-defaults.php hero_badge_2 is '50+ chương trình Liên thông Đại học: Từ xa & Vừa học vừa làm'",
	[ 'hero_badge_2' => $hero_badge_2, 'subtext' => $first_badge_subtext ]
);

// -------------------------------------------------------------
// SECTION 4: FILTER HARMONIZATION & REDUNDANCY CHECK
// -------------------------------------------------------------
echo "\nSECTION 4: Filter Harmonization & Redundancy Check\n";
echo "-------------------------------------------------------------------\n";

// 4.1: Verify all catalog/search filter forms submit to /he-dao-tao/
$archive_program_content = file_get_contents( __DIR__ . '/../archive-program.php' );
$tax_training_content   = file_get_contents( __DIR__ . '/../taxonomy-training_type.php' );

$archive_submits_to_hdt = strpos( $archive_program_content, "home_url( '/he-dao-tao/' )" ) !== false;
$tax_submits_to_hdt     = strpos( $tax_training_content, "home_url( '/he-dao-tao/'" ) !== false;

M4Tester::assert(
	$archive_submits_to_hdt && $tax_submits_to_hdt,
	"4.1: All program catalog and taxonomy filter forms submit to /he-dao-tao/ canonical route",
	[ 'archive_program' => $archive_submits_to_hdt, 'taxonomy_training_type' => $tax_submits_to_hdt ]
);

// 4.2: Zero redundant 'Loại tuyển sinh' or 'loai_tuyen_sinh' in templates
$files_to_check = [
	__DIR__ . '/../front-page.php',
	__DIR__ . '/../header.php',
	__DIR__ . '/../footer.php',
	__DIR__ . '/../inc/config/class-defaults.php',
	__DIR__ . '/../inc/core/class-menus.php',
];

$has_redundant_filter = false;
foreach ( $files_to_check as $file ) {
	$content = file_get_contents( $file );
	if ( stripos( $content, 'loai_tuyen_sinh' ) !== false || stripos( $content, 'loại tuyển sinh' ) !== false ) {
		$has_redundant_filter = true;
		break;
	}
}

M4Tester::assert(
	! $has_redundant_filter,
	"4.2: Zero redundant 'Loại tuyển sinh' or 'loai_tuyen_sinh' filters exist across M4 scope files",
	[ 'has_redundant' => $has_redundant_filter ]
);

// -------------------------------------------------------------
// SECTION 5: LINT & SYNTAX INTEGRITY
// -------------------------------------------------------------
echo "\nSECTION 5: PHP Syntax Integrity\n";
echo "-------------------------------------------------------------------\n";

$php_files = [
	'header.php',
	'footer.php',
	'front-page.php',
	'inc/config/class-defaults.php',
	'inc/core/class-menus.php',
];

$all_syntax_pass = true;
foreach ( $php_files as $f ) {
	$full_path = dirname( __DIR__ ) . '/' . $f;
	$output = [];
	$return_var = 0;
	exec( 'php -l ' . escapeshellarg( $full_path ), $output, $return_var );
	$pass = ( $return_var === 0 );
	if ( ! $pass ) {
		$all_syntax_pass = false;
	}
	M4Tester::assert(
		$pass,
		"5.1: Syntax check passes for {$f}",
		[ 'output' => implode( "\n", $output ) ]
	);
}

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n===================================================================\n";
echo sprintf( "SUMMARY: %d PASSED, %d FAILED\n", M4Tester::$passed, M4Tester::$failed );
echo "===================================================================\n";

if ( M4Tester::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
