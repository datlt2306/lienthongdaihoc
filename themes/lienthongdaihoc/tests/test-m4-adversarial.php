<?php
/**
 * Adversarial Stress Harness for Milestone M4: Navigation, Menus & Homepage
 *
 * Authored by: challenger_m4_1
 * Purpose: Empirically stress-test navigation menus, fallback rendering,
 *          dynamic injection filters, edge cases, ID collision, idempotency,
 *          footer sanitation, and previous milestone regressions.
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

// Mock WordPress environment
if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		return 'https://lienthongdaihoc.com' . ( $path ? ( '/' . ltrim( $path, '/' ) ) : '' );
	}
}
if ( ! function_exists( 'untrailingslashit' ) ) {
	function untrailingslashit( $string ) {
		return rtrim( (string) $string, '/\\' );
	}
}
if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $string ) {
		return untrailingslashit( $string ) . '/';
	}
}
if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return htmlspecialchars( (string) $url, ENT_QUOTES, 'UTF-8' );
	}
}
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}
if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}
if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return ( is_object( $thing ) && get_class( $thing ) === 'WP_Error' );
	}
}
if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public $message;
		public function __construct( $message = 'Error' ) {
			$this->message = $message;
		}
	}
}

// Global terms store to simulate standard vs error vs empty conditions
$GLOBALS['mock_terms_return'] = [
	(object) [ 'term_id' => 7, 'slug' => 'tu-xa', 'name' => 'Từ xa' ],
	(object) [ 'term_id' => 8, 'slug' => 'vua-hoc-vua-lam', 'name' => 'Vừa học vừa làm' ],
];

if ( ! function_exists( 'get_terms' ) ) {
	function get_terms( $args = [] ) {
		return $GLOBALS['mock_terms_return'];
	}
}
if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
}

$GLOBALS['mock_majors_return'] = [
	(object) [ 'ID' => 101, 'post_title' => 'Công nghệ thông tin' ],
	(object) [ 'ID' => 102, 'post_title' => 'Quản trị kinh doanh' ],
	(object) [ 'ID' => 103, 'post_title' => 'Kế toán' ],
];

if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $transient ) {
		return $GLOBALS['mock_majors_return'];
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
if ( ! function_exists( 'wp_parse_url' ) ) {
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( $url, $component );
	}
}
if ( ! defined( 'LTDH_TAX_TRAINING_TYPE' ) ) {
	define( 'LTDH_TAX_TRAINING_TYPE', 'training_type' );
}
if ( ! defined( 'LTDH_CPT_MAJOR' ) ) {
	define( 'LTDH_CPT_MAJOR', 'major' );
}

// Load theme files under test
require_once __DIR__ . '/../inc/config/class-defaults.php';
require_once __DIR__ . '/../inc/core/class-menus.php';

class AdversarialHarness {
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
echo "ADVERSARIAL STRESS TEST SUITE: M4 NAVIGATION & MENUS\n";
echo "===================================================================\n\n";

// -------------------------------------------------------------
// CHALLENGE 1: DYNAMIC SUBMENU INJECTION TITLE VARIATIONS & CASE SENSITIVITY
// -------------------------------------------------------------
echo "CHALLENGE 1: Title Variations & Case Insensitivity in Submenu Injection\n";
echo "-------------------------------------------------------------------\n";

$title_variations = [
	'Liên thông đại học',
	'liên thông đại học',
	'LIÊN THÔNG ĐẠI HỌC',
	'  Liên thông đại học  ',
	'Liên thông',
	'LIÊN THÔNG',
	'hệ đào tạo',
	'HỆ ĐÀO TẠO',
	'hình thức học',
	'HÌNH THỨC HỌC',
	'Hình thức đào tạo',
];

$all_titles_injected = true;
foreach ( $title_variations as $title ) {
	$mock_items = [
		(object) [
			'ID' => 10,
			'title' => $title,
			'url' => home_url( '/he-dao-tao/' ),
			'menu_item_parent' => 0,
			'classes' => [],
		],
	];
	$res = ltdh_dynamic_menu_submenu_injection( $mock_items, (object) [ 'theme_location' => 'primary-menu' ] );
	$has_children_class = in_array( 'menu-item-has-children', $res[0]->classes, true );
	$has_sub_items = ( count( $res ) === 3 ); // 1 parent + 2 injected sub-items
	if ( ! $has_children_class || ! $has_sub_items ) {
		$all_titles_injected = false;
		break;
	}
}

AdversarialHarness::assert(
	$all_titles_injected,
	"C1.1: Dynamic submenu injection correctly matches all unicode/accented/cased variations of 'Liên thông đại học', 'Liên thông', 'Hình thức học', 'Hệ đào tạo'",
	[ 'all_passed' => $all_titles_injected ]
);

// Negative control: Unrelated titles must NOT trigger injection
$negative_titles = [ 'Trang chủ', 'Kiến thức liên thông', 'Kiểm tra điều kiện', 'Tư vấn', 'Giới thiệu', 'Blog' ];
$all_negatives_clean = true;
foreach ( $negative_titles as $title ) {
	$mock_items = [
		(object) [
			'ID' => 20,
			'title' => $title,
			'url' => home_url( '/some-path/' ),
			'menu_item_parent' => 0,
			'classes' => [],
		],
	];
	$res = ltdh_dynamic_menu_submenu_injection( $mock_items, (object) [ 'theme_location' => 'primary-menu' ] );
	if ( count( $res ) !== 1 || in_array( 'menu-item-has-children', $res[0]->classes, true ) ) {
		$all_negatives_clean = false;
		break;
	}
}

AdversarialHarness::assert(
	$all_negatives_clean,
	"C1.2: Unrelated menu titles (Trang chủ, Tin tức, Tư vấn, v.v.) do NOT trigger submenu injection",
	[ 'all_negatives_clean' => $all_negatives_clean ]
);

// -------------------------------------------------------------
// CHALLENGE 2: THEME LOCATION DISCRIMINATION
// -------------------------------------------------------------
echo "\nCHALLENGE 2: Theme Location Discrimination & Behavior on 'primary' vs 'primary-menu'\n";
echo "-------------------------------------------------------------------\n";

$mock_items = [
	(object) [
		'ID' => 1,
		'title' => 'Liên thông đại học',
		'url' => home_url( '/he-dao-tao/' ),
		'menu_item_parent' => 0,
		'classes' => [],
	],
];

// Test footer-menu: should NOT inject
$res_footer = ltdh_dynamic_menu_submenu_injection( $mock_items, (object) [ 'theme_location' => 'footer-menu' ] );
AdversarialHarness::assert(
	count( $res_footer ) === 1,
	"C2.1: theme_location 'footer-menu' does NOT trigger dynamic injection",
	[ 'count' => count( $res_footer ) ]
);

// Test empty theme_location: should NOT inject
$res_empty = ltdh_dynamic_menu_submenu_injection( $mock_items, (object) [ 'theme_location' => '' ] );
AdversarialHarness::assert(
	count( $res_empty ) === 1,
	"C2.2: Empty theme_location does NOT trigger dynamic injection",
	[ 'count' => count( $res_empty ) ]
);

// Test theme_location 'primary-menu': MUST inject
$res_primary_menu = ltdh_dynamic_menu_submenu_injection( $mock_items, (object) [ 'theme_location' => 'primary-menu' ] );
AdversarialHarness::assert(
	count( $res_primary_menu ) === 3,
	"C2.3: theme_location 'primary-menu' successfully injects 2 training type sub-items",
	[ 'count' => count( $res_primary_menu ) ]
);

// Test theme_location 'primary' (behavior verification)
$res_primary_alias = ltdh_dynamic_menu_submenu_injection( $mock_items, (object) [ 'theme_location' => 'primary' ] );
$primary_alias_injected = ( count( $res_primary_alias ) === 3 );
AdversarialHarness::assert(
	! $primary_alias_injected,
	"C2.4: Verifies current implementation strictly checks 'primary-menu' (and ignores unregistered 'primary')",
	[ 'injected_with_primary' => $primary_alias_injected ]
);

// -------------------------------------------------------------
// CHALLENGE 3: ERROR HANDLING & EMPTY TERMS RESILIENCE
// -------------------------------------------------------------
echo "\nCHALLENGE 3: Error Handling & Empty Terms Resilience\n";
echo "-------------------------------------------------------------------\n";

// Scenario A: get_terms returns WP_Error
$GLOBALS['mock_terms_return'] = new WP_Error( 'terms_failed' );

$mock_items_err = [
	(object) [
		'ID' => 50,
		'title' => 'Liên thông đại học',
		'url' => home_url( '/he-dao-tao/' ),
		'menu_item_parent' => 0,
		'classes' => [],
	],
];

$res_err = ltdh_dynamic_menu_submenu_injection( $mock_items_err, (object) [ 'theme_location' => 'primary-menu' ] );
AdversarialHarness::assert(
	count( $res_err ) === 1 && $res_err[0]->ID === 50,
	"C3.1: When get_terms returns WP_Error, function degrades gracefully without throwing fatal errors",
	[ 'count' => count( $res_err ) ]
);

// Scenario B: get_terms returns empty array
$GLOBALS['mock_terms_return'] = [];
$res_empty_terms = ltdh_dynamic_menu_submenu_injection( $mock_items_err, (object) [ 'theme_location' => 'primary-menu' ] );
AdversarialHarness::assert(
	count( $res_empty_terms ) === 1 && $res_empty_terms[0]->ID === 50,
	"C3.2: When get_terms returns empty array, parent item is preserved without ghost sub-items",
	[ 'count' => count( $res_empty_terms ) ]
);

// Restore standard terms
$GLOBALS['mock_terms_return'] = [
	(object) [ 'term_id' => 7, 'slug' => 'tu-xa', 'name' => 'Từ xa' ],
	(object) [ 'term_id' => 8, 'slug' => 'vua-hoc-vua-lam', 'name' => 'Vừa học vừa làm' ],
];

// -------------------------------------------------------------
// CHALLENGE 4: ID COLLISION & IDEMPOTENCY
// -------------------------------------------------------------
echo "\nCHALLENGE 4: ID Collision & Idempotency\n";
echo "-------------------------------------------------------------------\n";

// Test High DB IDs (e.g. 99999) to verify no numeric overflow or collision
$high_id_items = [
	(object) [
		'ID' => 99999,
		'title' => 'Liên thông đại học',
		'url' => home_url( '/he-dao-tao/' ),
		'menu_item_parent' => 0,
		'classes' => [],
	],
	(object) [
		'ID' => 100000,
		'title' => 'Ngành học',
		'url' => home_url( '/nganh-hoc/' ),
		'menu_item_parent' => 0,
		'classes' => [],
	],
];

$res_high = ltdh_dynamic_menu_submenu_injection( $high_id_items, (object) [ 'theme_location' => 'primary-menu' ] );
$injected_ids = [];
$has_collision = false;
foreach ( $res_high as $item ) {
	if ( isset( $injected_ids[ $item->ID ] ) ) {
		$has_collision = true;
	}
	$injected_ids[ $item->ID ] = true;
}

AdversarialHarness::assert(
	! $has_collision && count( $res_high ) > 2,
	"C4.1: Submenu injection assigns unique, non-colliding IDs even with large pre-existing IDs (>= 100000)",
	[ 'total_items' => count( $res_high ), 'has_collision' => $has_collision ]
);

// -------------------------------------------------------------
// CHALLENGE 5: FALLBACK MENU RENDERING UNDER VARIOUS REQUEST_URI
// -------------------------------------------------------------
echo "\nCHALLENGE 5: Fallback Menu Rendering Under Various REQUEST_URI\n";
echo "-------------------------------------------------------------------\n";

$test_uris = [
	'/' => [ 'active_expected' => 'Trang chủ' ],
	'/he-dao-tao/' => [ 'active_expected' => 'Liên thông đại học' ],
	'/he-dao-tao/tu-xa/' => [ 'active_expected' => 'Từ xa' ],
	'/he-dao-tao/vua-hoc-vua-lam/' => [ 'active_expected' => 'Vừa học vừa làm' ],
	'/nganh-hoc/' => [ 'active_expected' => 'Ngành học' ],
	'/truong-doi-tac/' => [ 'active_expected' => 'Trường đại học' ],
	'/tin-tuc/' => [ 'active_expected' => 'Kiến thức liên thông' ],
	'/kiem-tra-dieu-kien/' => [ 'active_expected' => 'Kiểm tra điều kiện' ],
];

$all_uris_valid = true;
foreach ( $test_uris as $uri => $meta ) {
	$_SERVER['REQUEST_URI'] = $uri;
	ob_start();
	ltdh_render_fallback_menu( 'primary', 'test-ul-class' );
	$html = ob_get_clean();

	if ( empty( $html ) || strpos( $html, $meta['active_expected'] ) === false ) {
		$all_uris_valid = false;
		break;
	}
}

AdversarialHarness::assert(
	$all_uris_valid,
	"C5.1: Fallback menu renders correctly across all 8 standard routes with expected labels",
	[ 'all_uris_valid' => $all_uris_valid ]
);

// Test null/empty REQUEST_URI
$_SERVER['REQUEST_URI'] = '';
ob_start();
ltdh_render_fallback_menu( 'primary', 'test-ul-class' );
$null_uri_html = ob_get_clean();
AdversarialHarness::assert(
	! empty( $null_uri_html ) && strpos( $null_uri_html, 'Liên thông đại học' ) !== false,
	"C5.2: Fallback menu handles empty/unset REQUEST_URI without throwing PHP notices",
	[]
);

// -------------------------------------------------------------
// CHALLENGE 6: FOOTER.PHP COLUMN 3 FORENSIC ADVERSARIAL INSPECTION
// -------------------------------------------------------------
echo "\nCHALLENGE 6: Footer.php Column 3 Forensic Adversarial Inspection\n";
echo "-------------------------------------------------------------------\n";

$footer_raw = file_get_contents( __DIR__ . '/../footer.php' );

// Isolate Column 3 exactly between comments or container
preg_match( '/<!-- Column 3:.*?<\/ul>\s*<\/div>/s', $footer_raw, $c3_match );
$c3_block = $c3_match[0] ?? '';

AdversarialHarness::assert(
	! empty( $c3_block ),
	"C6.1: Found Column 3 block in footer.php",
	[ 'found' => ! empty( $c3_block ) ]
);

// Count total links in Column 3
preg_match_all( '/<a\s+[^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/s', $c3_block, $all_c3_links, PREG_SET_ORDER );
$c3_link_count = count( $all_c3_links );

AdversarialHarness::assert(
	$c3_link_count === 5,
	"C6.2: Footer Column 3 contains exactly 5 navigation links",
	[ 'count' => $c3_link_count ]
);

// Verify NO '#' href in Column 3
$c3_has_hash = false;
foreach ( $all_c3_links as $link ) {
	if ( trim( $link[1] ) === '#' ) {
		$c3_has_hash = true;
	}
}
AdversarialHarness::assert(
	! $c3_has_hash,
	"C6.3: Zero '#' href links in Column 3",
	[ 'c3_has_hash' => $c3_has_hash ]
);

// Banned words stress test in Column 3
$banned_terms = [
	'VB2',
	'Văn bằng 2',
	'Cao đẳng online',
	'Chính quy',
	'Đại học chính quy',
	'Tại chức',
	'Đại học tại chức',
	'Trung Cấp lên Đại học',
	'VLVH',
];
$found_banned = [];
foreach ( $banned_terms as $term ) {
	if ( stripos( $c3_block, $term ) !== false ) {
		$found_banned[] = $term;
	}
}
AdversarialHarness::assert(
	empty( $found_banned ),
	"C6.4: Zero out-of-scope educational terms (VB2, Cao đẳng online, Chính quy, v.v.) in Column 3",
	[ 'found_banned' => $found_banned ]
);

// -------------------------------------------------------------
// CHALLENGE 7: HOMEPAGE EXHAUSTIVE INTEGRITY AUDIT
// -------------------------------------------------------------
echo "\nCHALLENGE 7: Homepage Exhaustive Scope & Presentation Audit\n";
echo "-------------------------------------------------------------------\n";

$front_page = file_get_contents( __DIR__ . '/../front-page.php' );

// Verify no VB2 in H1
AdversarialHarness::assert(
	stripos( $front_page, 'Văn Bằng 2 & Đại Học Từ Xa' ) === false,
	"C7.1: front-page.php does NOT contain obsolete H1 'Văn Bằng 2 & Đại Học Từ Xa'",
	[]
);

// Verify search action is /he-dao-tao/
AdversarialHarness::assert(
	strpos( $front_page, "home_url('/he-dao-tao/tu-xa/')" ) === false,
	"C7.2: front-page.php search form does NOT hardcode action to /he-dao-tao/tu-xa/",
	[]
);

// Verify THPT removal
AdversarialHarness::assert(
	stripos( $front_page, 'Học sinh tốt nghiệp THPT' ) === false,
	"C7.3: front-page.php does NOT contain 'Học sinh tốt nghiệp THPT'",
	[]
);

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n===================================================================\n";
echo sprintf( "ADVERSARIAL SUITE SUMMARY: %d PASSED, %d FAILED\n", AdversarialHarness::$passed, AdversarialHarness::$failed );
echo "===================================================================\n";

if ( AdversarialHarness::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
