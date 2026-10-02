<?php
/**
 * Empirical Test Harness for Milestone M3: Taxonomy Label & Routing Verification
 *
 * Specifically verifies:
 * 1. Simulate request to `/chuong-trinh/` and verify 301 target is `/he-dao-tao/` (NOT `/he-dao-tao/tu-xa/`).
 * 2. Simulate request to `/chuong-trinh/?truong=utc&nganh=cntt` and verify query args are preserved in the redirect URL.
 * 3. Verify Rank Math canonical URL function for program archive produces `home_url('/he-dao-tao/')`.
 * 4. Verify that `/he-dao-tao/` returns HTTP 200 without redirect loop.
 *
 * Additional Adversarial Stress Tests:
 * - Trailing slash and non-trailing slash variations
 * - Case insensitivity (/CHUONG-TRINH/)
 * - Complex, array, encoded Vietnamese, and marketing UTM query args
 * - Paginated /he-dao-tao/ and term routes
 * - Full HTTP roundtrip simulation ensuring zero redirect loop
 *
 * Run via: php tests/test-m3-empirical.php
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

// Load constants
require_once __DIR__ . '/../inc/config/constants.php';

// Global simulation state
class WP_Simulation_State {
	public static $redirect_url = null;
	public static $redirect_status = null;
	public static $status_header = null;
	public static $is_post_type_archive = false;
	public static $is_singular = false;
	public static $singular_type = '';
	public static $current_post_id = 0;
	public static $is_tax = false;
	public static $filter_callbacks = [];
	public static $action_callbacks = [];
	public static $posts = [];

	public static function reset() {
		self::$redirect_url = null;
		self::$redirect_status = null;
		self::$status_header = null;
		self::$is_post_type_archive = false;
		self::$is_singular = false;
		self::$singular_type = '';
		self::$current_post_id = 0;
		self::$is_tax = false;
		$_SERVER['REQUEST_URI'] = '/';
		$_GET = [];
	}
}

// Exception to simulate exit after wp_redirect
class WPRedirectExitException extends Exception {
	public $url;
	public $status;
	public function __construct( $url, $status ) {
		parent::__construct( "Redirected to $url with status $status" );
		$this->url = $url;
		$this->status = $status;
	}
}

// WordPress Stub Functions
function home_url( $path = '' ) {
	return 'https://lienthongdaihoc.com' . ( $path ? '/' . ltrim( $path, '/' ) : '' );
}

function add_query_arg( ...$args ) {
	if ( is_array( $args[0] ) ) {
		$new_args = $args[0];
		$url = $args[1] ?? ( $_SERVER['REQUEST_URI'] ?? '' );
	} else {
		$new_args = [ $args[0] => $args[1] ];
		$url = $args[2] ?? ( $_SERVER['REQUEST_URI'] ?? '' );
	}

	$parts = parse_url( $url );
	$query = [];
	if ( isset( $parts['query'] ) ) {
		parse_str( $parts['query'], $query );
	}
	$query = array_merge( $query, $new_args );

	$scheme   = isset( $parts['scheme'] ) ? $parts['scheme'] . '://' : '';
	$host     = $parts['host'] ?? '';
	$port     = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
	$path     = $parts['path'] ?? '';
	$qs       = ! empty( $query ) ? '?' . http_build_query( $query ) : '';
	$fragment = isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '';

	return "$scheme$host$port$path$qs$fragment";
}

function wp_redirect( $location, $status = 302 ) {
	WP_Simulation_State::$redirect_url = $location;
	WP_Simulation_State::$redirect_status = $status;
	throw new WPRedirectExitException( $location, $status );
}

function status_header( $code, $description = '' ) {
	WP_Simulation_State::$status_header = (int) $code;
}

function is_post_type_archive( $post_types = '' ) {
	if ( empty( $post_types ) ) {
		return WP_Simulation_State::$is_post_type_archive;
	}
	if ( is_array( $post_types ) ) {
		return WP_Simulation_State::$is_post_type_archive && in_array( 'program', $post_types, true );
	}
	return WP_Simulation_State::$is_post_type_archive && ( 'program' === $post_types );
}

function is_singular( $post_types = '' ) {
	if ( ! WP_Simulation_State::$is_singular ) {
		return false;
	}
	if ( empty( $post_types ) ) {
		return true;
	}
	$types = (array) $post_types;
	return in_array( WP_Simulation_State::$singular_type, $types, true );
}

function get_the_ID() {
	return WP_Simulation_State::$current_post_id;
}

function get_post_field( $field, $post_id ) {
	return WP_Simulation_State::$posts[ $post_id ]->$field ?? '';
}

function is_tax( $taxonomy = '', $term = '' ) {
	return WP_Simulation_State::$is_tax;
}

function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	WP_Simulation_State::$action_callbacks[ $tag ][] = $callback;
	return true;
}

function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	WP_Simulation_State::$filter_callbacks[ $tag ][] = $callback;
	return true;
}

function add_rewrite_rule( $regex, $query, $after = 'bottom' ) {
	return true;
}

function locate_template( $template_names, $load = false, $require_once = true, $args = [] ) {
	foreach ( (array) $template_names as $template_name ) {
		if ( file_exists( ABSPATH . $template_name ) ) {
			return ABSPATH . $template_name;
		}
	}
	return '';
}

function sanitize_title( $title ) {
	return strtolower( trim( preg_replace( '/[^A-Za-z0-9-]+/', '-', $title ) ) );
}

function wp_cache_get( $key, $group = '' ) {
	return false;
}

function wp_cache_set( $key, $data, $group = '', $expire = 0 ) {
	return true;
}

// Global wp_query mock
class MockWPQuery {
	public $is_404 = false;
}
$wp_query = new MockWPQuery();

// Load target implementation files under test
require_once ABSPATH . 'inc/core/class-rewrite-rules.php';
require_once ABSPATH . 'inc/seo/class-rankmath-integration.php';

// Test Framework
class TestRunner {
	public static $passed = 0;
	public static $failed = 0;
	public static $results = [];

	public static function assert( $condition, $message, $details = null ) {
		if ( $condition ) {
			self::$passed++;
			self::$results[] = [ 'status' => 'PASS', 'message' => $message, 'details' => $details ];
			echo "  [PASS] $message\n";
		} else {
			self::$failed++;
			self::$results[] = [ 'status' => 'FAIL', 'message' => $message, 'details' => $details ];
			echo "  [FAIL] $message\n";
			if ( $details !== null ) {
				echo "         Details: " . print_r( $details, true ) . "\n";
			}
		}
	}
}

echo "===================================================================\n";
echo "EMPIRICAL TEST SUITE: M3 ROUTING & CANONICAL VERIFICATION\n";
echo "===================================================================\n\n";

// =================================================================
// SUITE 1: Simulate request to /chuong-trinh/ -> 301 Target is /he-dao-tao/
// =================================================================
echo "SUITE 1: Request to /chuong-trinh/ Redirect Target Verification\n";
echo "-------------------------------------------------------------------\n";

function simulate_template_redirect( $uri, $get_params = [] ) {
	WP_Simulation_State::reset();
	$_SERVER['REQUEST_URI'] = $uri;
	$_GET = $get_params;

	try {
		ltdh_redirect_taxonomy_base();
		return [ 'redirected' => false, 'url' => null, 'status' => null ];
	} catch ( WPRedirectExitException $e ) {
		return [ 'redirected' => true, 'url' => $e->url, 'status' => $e->status ];
	}
}

// 1.1: Standard /chuong-trinh/
$r1_1 = simulate_template_redirect( '/chuong-trinh/' );
TestRunner::assert(
	$r1_1['redirected'] === true,
	"1.1: /chuong-trinh/ triggers redirect",
	$r1_1
);
TestRunner::assert(
	$r1_1['status'] === 301,
	"1.1: /chuong-trinh/ redirect status is HTTP 301 (actual: {$r1_1['status']})",
	$r1_1
);
TestRunner::assert(
	$r1_1['url'] === 'https://lienthongdaihoc.com/he-dao-tao/',
	"1.1: /chuong-trinh/ target is https://lienthongdaihoc.com/he-dao-tao/ (actual: {$r1_1['url']})",
	$r1_1
);
TestRunner::assert(
	strpos( (string) $r1_1['url'], '/he-dao-tao/tu-xa/' ) === false,
	"1.1: /chuong-trinh/ target is NOT /he-dao-tao/tu-xa/",
	$r1_1
);

// 1.2: Without trailing slash: /chuong-trinh
$r1_2 = simulate_template_redirect( '/chuong-trinh' );
TestRunner::assert(
	$r1_2['redirected'] === true && $r1_2['status'] === 301 && $r1_2['url'] === 'https://lienthongdaihoc.com/he-dao-tao/',
	"1.2: /chuong-trinh (no trailing slash) 301 redirects to /he-dao-tao/",
	$r1_2
);

// 1.3: Case Insensitivity: /CHUONG-TRINH/ and /Chuong-Trinh
$r1_3a = simulate_template_redirect( '/CHUONG-TRINH/' );
TestRunner::assert(
	$r1_3a['redirected'] === true && $r1_3a['status'] === 301 && $r1_3a['url'] === 'https://lienthongdaihoc.com/he-dao-tao/',
	"1.3a: /CHUONG-TRINH/ (uppercase) 301 redirects to /he-dao-tao/",
	$r1_3a
);

$r1_3b = simulate_template_redirect( '/Chuong-Trinh/' );
TestRunner::assert(
	$r1_3b['redirected'] === true && $r1_3b['status'] === 301 && $r1_3b['url'] === 'https://lienthongdaihoc.com/he-dao-tao/',
	"1.3b: /Chuong-Trinh/ (mixed case) 301 redirects to /he-dao-tao/",
	$r1_3b
);

// 1.4: Adversarial Guard: Subpaths like /chuong-trinh/cntt/ should NOT be caught by base redirect
$r1_4 = simulate_template_redirect( '/chuong-trinh/cntt/' );
TestRunner::assert(
	$r1_4['redirected'] === false,
	"1.4: Subpath /chuong-trinh/cntt/ is NOT redirected by ltdh_redirect_taxonomy_base",
	$r1_4
);

echo "\n";


// =================================================================
// SUITE 2: Query Args Preservation on /chuong-trinh/?...
// =================================================================
echo "SUITE 2: Query Args Preservation Verification\n";
echo "-------------------------------------------------------------------\n";

// 2.1: The prompt requirement: /chuong-trinh/?truong=utc&nganh=cntt
$query_2_1 = [ 'truong' => 'utc', 'nganh' => 'cntt' ];
$r2_1 = simulate_template_redirect( '/chuong-trinh/?truong=utc&nganh=cntt', $query_2_1 );
TestRunner::assert(
	$r2_1['redirected'] === true && $r2_1['status'] === 301,
	"2.1: /chuong-trinh/?truong=utc&nganh=cntt returns 301",
	$r2_1
);
TestRunner::assert(
	$r2_1['url'] === 'https://lienthongdaihoc.com/he-dao-tao/?truong=utc&nganh=cntt',
	"2.1: Query args preserved exactly in target URL (actual: {$r2_1['url']})",
	$r2_1
);
TestRunner::assert(
	strpos( (string) $r2_1['url'], 'truong=utc' ) !== false && strpos( (string) $r2_1['url'], 'nganh=cntt' ) !== false,
	"2.1: Both truong=utc and nganh=cntt present in destination",
	$r2_1
);

// 2.2: Without trailing slash with query args: /chuong-trinh?truong=utc&nganh=cntt
$r2_2 = simulate_template_redirect( '/chuong-trinh?truong=utc&nganh=cntt', $query_2_1 );
TestRunner::assert(
	$r2_2['url'] === 'https://lienthongdaihoc.com/he-dao-tao/?truong=utc&nganh=cntt',
	"2.2: /chuong-trinh?truong=utc&nganh=cntt (no slash) preserves query args and adds trailing slash to path",
	$r2_2
);

// 2.3: Multiple query args including sorting and pagination
$query_2_3 = [ 'truong' => 'neu', 'nganh' => 'ke-toan', 'sort' => 'tuition_asc', 'paged' => '2' ];
$r2_3 = simulate_template_redirect( '/chuong-trinh/?' . http_build_query( $query_2_3 ), $query_2_3 );
TestRunner::assert(
	$r2_3['url'] === 'https://lienthongdaihoc.com/he-dao-tao/?truong=neu&nganh=ke-toan&sort=tuition_asc&paged=2',
	"2.3: Multi-param query (truong, nganh, sort, paged) preserved cleanly",
	$r2_3
);

// 2.4: Array query parameters: ?truong[]=utc&truong[]=neu
$query_2_4 = [ 'truong' => [ 'utc', 'neu' ], 'nganh' => 'cntt' ];
$r2_4 = simulate_template_redirect( '/chuong-trinh/?' . http_build_query( $query_2_4 ), $query_2_4 );
TestRunner::assert(
	strpos( (string) $r2_4['url'], 'truong%5B0%5D=utc' ) !== false && strpos( (string) $r2_4['url'], 'truong%5B1%5D=neu' ) !== false,
	"2.4: Array parameters truong[] preserved accurately in redirect",
	$r2_4
);

// 2.5: URL-encoded Vietnamese characters: ?s=công nghệ thông tin
$query_2_5 = [ 's' => 'công nghệ thông tin' ];
$r2_5 = simulate_template_redirect( '/chuong-trinh/?s=' . urlencode( 'công nghệ thông tin' ), $query_2_5 );
TestRunner::assert(
	strpos( (string) $r2_5['url'], 's=c%C3%B4ng+ngh%E1%BB%87+th%C3%B4ng+tin' ) !== false || strpos( (string) $r2_5['url'], 'c%C3%B4ng' ) !== false,
	"2.5: Vietnamese accented search query preserved in redirect",
	$r2_5
);

// 2.6: Marketing UTM parameters
$query_2_6 = [ 'utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'tuyensinh' ];
$r2_6 = simulate_template_redirect( '/chuong-trinh/?' . http_build_query( $query_2_6 ), $query_2_6 );
TestRunner::assert(
	strpos( (string) $r2_6['url'], 'utm_source=google' ) !== false && strpos( (string) $r2_6['url'], 'utm_campaign=tuyensinh' ) !== false,
	"2.6: Marketing UTM parameters preserved on redirect",
	$r2_6
);

echo "\n";


// =================================================================
// SUITE 3: Rank Math Canonical URL Filter Verification
// =================================================================
echo "SUITE 3: Rank Math Canonical URL Filter Verification\n";
echo "-------------------------------------------------------------------\n";

// 3.1: Check that hooks are registered
TestRunner::assert(
	in_array( 'ltdh_seo_enforce_canonical_url', WP_Simulation_State::$filter_callbacks['rank_math/frontend/canonical'] ?? [], true ),
	"3.1a: ltdh_seo_enforce_canonical_url is hooked to rank_math/frontend/canonical"
);
TestRunner::assert(
	in_array( 'ltdh_seo_enforce_canonical_url', WP_Simulation_State::$filter_callbacks['rank_math/paper/canonical_url'] ?? [], true ),
	"3.1b: ltdh_seo_enforce_canonical_url is hooked to rank_math/paper/canonical_url"
);

// 3.2: When is_post_type_archive('program') is true
WP_Simulation_State::reset();
WP_Simulation_State::$is_post_type_archive = true;
$_SERVER['REQUEST_URI'] = '/he-dao-tao/';
$canon_3_2 = ltdh_seo_enforce_canonical_url( 'https://lienthongdaihoc.com/chuong-trinh/' );
TestRunner::assert(
	$canon_3_2 === 'https://lienthongdaihoc.com/he-dao-tao/',
	"3.2: On is_post_type_archive('program'), canonical is home_url('/he-dao-tao/') (actual: $canon_3_2)",
	$canon_3_2
);

// 3.3: When REQUEST_URI is /chuong-trinh/ (even if is_post_type_archive isn't set yet)
WP_Simulation_State::reset();
WP_Simulation_State::$is_post_type_archive = false;
$_SERVER['REQUEST_URI'] = '/chuong-trinh/';
$canon_3_3 = ltdh_seo_enforce_canonical_url( 'https://lienthongdaihoc.com/chuong-trinh/' );
TestRunner::assert(
	$canon_3_3 === 'https://lienthongdaihoc.com/he-dao-tao/',
	"3.3: When REQUEST_URI is /chuong-trinh/, canonical points directly to /he-dao-tao/ (actual: $canon_3_3)",
	$canon_3_3
);

// 3.4: When REQUEST_URI is /chuong-trinh/?truong=utc&nganh=cntt
WP_Simulation_State::reset();
$_SERVER['REQUEST_URI'] = '/chuong-trinh/?truong=utc&nganh=cntt';
$canon_3_4 = ltdh_seo_enforce_canonical_url( 'https://lienthongdaihoc.com/chuong-trinh/?truong=utc&nganh=cntt' );
TestRunner::assert(
	$canon_3_4 === 'https://lienthongdaihoc.com/he-dao-tao/',
	"3.4: With query args, canonical points to clean canonical base /he-dao-tao/ without query args",
	$canon_3_4
);

// 3.5: Singular program post preservation check
WP_Simulation_State::reset();
WP_Simulation_State::$is_singular = true;
WP_Simulation_State::$singular_type = 'program';
WP_Simulation_State::$current_post_id = 1800;
WP_Simulation_State::$posts[1800] = (object) [ 'post_name' => 'cu-nhan-cong-tac-xa-hoi' ];
$canon_3_5 = ltdh_seo_enforce_canonical_url( 'https://lienthongdaihoc.com/program/cu-nhan-cong-tac-xa-hoi/' );
TestRunner::assert(
	$canon_3_5 === 'https://lienthongdaihoc.com/cu-nhan-cong-tac-xa-hoi/',
	"3.5: Singular program canonical enforces flat URL /cu-nhan-cong-tac-xa-hoi/",
	$canon_3_5
);

// 3.6: Non-matching page pass-through
WP_Simulation_State::reset();
$_SERVER['REQUEST_URI'] = '/tin-tuc/';
$canon_3_6 = ltdh_seo_enforce_canonical_url( 'https://lienthongdaihoc.com/tin-tuc/' );
TestRunner::assert(
	$canon_3_6 === 'https://lienthongdaihoc.com/tin-tuc/',
	"3.6: Unrelated page canonical is untouched ($canon_3_6)",
	$canon_3_6
);

echo "\n";


// =================================================================
// SUITE 4: Verify /he-dao-tao/ Returns HTTP 200 without Redirect Loop
// =================================================================
echo "SUITE 4: Verify /he-dao-tao/ Returns HTTP 200 without Redirect Loop\n";
echo "-------------------------------------------------------------------\n";

// 4.1: Test ltdh_redirect_taxonomy_base() on /he-dao-tao/
$r4_1 = simulate_template_redirect( '/he-dao-tao/' );
TestRunner::assert(
	$r4_1['redirected'] === false,
	"4.1: /he-dao-tao/ does NOT redirect in ltdh_redirect_taxonomy_base()",
	$r4_1
);

// 4.2: Test ltdh_template_include_he_dao_tao() on /he-dao-tao/
WP_Simulation_State::reset();
$_SERVER['REQUEST_URI'] = '/he-dao-tao/';
$global_wp_query = new MockWPQuery();
$global_wp_query->is_404 = true;
$GLOBALS['wp_query'] = $global_wp_query;

$template_result = ltdh_template_include_he_dao_tao( ABSPATH . 'index.php' );

TestRunner::assert(
	WP_Simulation_State::$status_header === 200,
	"4.2: status_header(200) was called for /he-dao-tao/ (actual: " . WP_Simulation_State::$status_header . ")",
	WP_Simulation_State::$status_header
);
TestRunner::assert(
	$global_wp_query->is_404 === false,
	"4.2: wp_query->is_404 is cleared to false",
	$global_wp_query->is_404
);
TestRunner::assert(
	$template_result === ABSPATH . 'taxonomy-training_type.php' || $template_result === ABSPATH . 'archive-program.php',
	"4.2: Valid catalog template returned (" . basename( $template_result ) . ")",
	$template_result
);

// 4.3: Test term archives /he-dao-tao/tu-xa/ and /he-dao-tao/vua-hoc-vua-lam/
$r4_3a = simulate_template_redirect( '/he-dao-tao/tu-xa/' );
TestRunner::assert(
	$r4_3a['redirected'] === false,
	"4.3a: Term archive /he-dao-tao/tu-xa/ does NOT redirect",
	$r4_3a
);

WP_Simulation_State::reset();
$_SERVER['REQUEST_URI'] = '/he-dao-tao/tu-xa/';
$global_wp_query->is_404 = true;
$tmpl_tu_xa = ltdh_template_include_he_dao_tao( ABSPATH . 'index.php' );
TestRunner::assert(
	WP_Simulation_State::$status_header === 200 && $global_wp_query->is_404 === false,
	"4.3a: /he-dao-tao/tu-xa/ sets HTTP 200 and is_404=false",
	WP_Simulation_State::$status_header
);

$r4_3b = simulate_template_redirect( '/he-dao-tao/vua-hoc-vua-lam/' );
TestRunner::assert(
	$r4_3b['redirected'] === false,
	"4.3b: Term archive /he-dao-tao/vua-hoc-vua-lam/ does NOT redirect",
	$r4_3b
);

WP_Simulation_State::reset();
$_SERVER['REQUEST_URI'] = '/he-dao-tao/vua-hoc-vua-lam/';
$global_wp_query->is_404 = true;
$tmpl_vhvl = ltdh_template_include_he_dao_tao( ABSPATH . 'index.php' );
TestRunner::assert(
	WP_Simulation_State::$status_header === 200 && $global_wp_query->is_404 === false,
	"4.3b: /he-dao-tao/vua-hoc-vua-lam/ sets HTTP 200 and is_404=false",
	WP_Simulation_State::$status_header
);

// 4.4: Paginated /he-dao-tao/page/2/
$r4_4 = simulate_template_redirect( '/he-dao-tao/page/2/' );
TestRunner::assert(
	$r4_4['redirected'] === false,
	"4.4: /he-dao-tao/page/2/ does NOT redirect",
	$r4_4
);

WP_Simulation_State::reset();
$_SERVER['REQUEST_URI'] = '/he-dao-tao/page/2/';
$global_wp_query->is_404 = true;
$tmpl_paged = ltdh_template_include_he_dao_tao( ABSPATH . 'index.php' );
TestRunner::assert(
	WP_Simulation_State::$status_header === 200 && $global_wp_query->is_404 === false,
	"4.4: /he-dao-tao/page/2/ sets HTTP 200 and is_404=false",
	WP_Simulation_State::$status_header
);

// 4.5: Paginated term /he-dao-tao/tu-xa/page/2/
$r4_5 = simulate_template_redirect( '/he-dao-tao/tu-xa/page/2/' );
TestRunner::assert(
	$r4_5['redirected'] === false,
	"4.5: /he-dao-tao/tu-xa/page/2/ does NOT redirect",
	$r4_5
);

// 4.6: End-to-End Simulation: Roundtrip Redirect Loop Test
// Step 1: User/Bot visits /chuong-trinh/
$hop1 = simulate_template_redirect( '/chuong-trinh/' );
TestRunner::assert(
	$hop1['redirected'] === true && $hop1['status'] === 301,
	"4.6: Hop 1: /chuong-trinh/ returns 301"
);
$dest_path = parse_url( $hop1['url'], PHP_URL_PATH );

// Step 2: User/Bot follows Location header to $dest_path (/he-dao-tao/)
$hop2 = simulate_template_redirect( $dest_path );
TestRunner::assert(
	$hop2['redirected'] === false,
	"4.6: Hop 2: Destination {$dest_path} terminates cleanly (NO redirect, NOT looping back to /chuong-trinh/)"
);

// Step 3: Template inclusion on destination sets HTTP 200
WP_Simulation_State::reset();
$_SERVER['REQUEST_URI'] = $dest_path;
$global_wp_query->is_404 = true;
$final_template = ltdh_template_include_he_dao_tao( ABSPATH . 'index.php' );
TestRunner::assert(
	WP_Simulation_State::$status_header === 200 && ! empty( $final_template ),
	"4.6: Hop 2: Destination {$dest_path} serves HTTP 200 with catalog template"
);

// Step 4: Canonical verification on destination
WP_Simulation_State::$is_post_type_archive = true;
$final_canonical = ltdh_seo_enforce_canonical_url( 'https://lienthongdaihoc.com' . $dest_path );
TestRunner::assert(
	$final_canonical === 'https://lienthongdaihoc.com/he-dao-tao/',
	"4.6: Hop 2: Canonical tag on {$dest_path} is self-referential /he-dao-tao/ (Zero Canonical Loop)"
);

echo "\n";
echo "===================================================================\n";
echo "TEST RESULTS: " . TestRunner::$passed . " PASSED, " . TestRunner::$failed . " FAILED\n";
echo "===================================================================\n";

if ( TestRunner::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
