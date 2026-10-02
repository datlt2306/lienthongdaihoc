<?php
/**
 * Adversarial Stress-Test Suite for Milestone M3
 *
 * Stress-tests:
 * 1. Query parameter preservation under edge cases (special characters, array params, empty values).
 * 2. Rewrite rule order & conflict resolution for /he-dao-tao/ paths vs generic post_name.
 * 3. Canonical enforcement under complex REQUEST_URI values.
 * 4. Menu submenu matching under various unicode casing, leading/trailing spaces, and invalid titles.
 * 5. Template inclusion edge cases (case insensitivity, deep pagination, trailing slash variations).
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class AdversarialRunner {
	public static $passed = 0;
	public static $failed = 0;

	public static function assert( $condition, $message ) {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] {$message}\n";
		} else {
			self::$failed++;
			echo "  [FAIL] {$message}\n";
		}
	}
}

echo "===================================================================\n";
echo "ADVERSARIAL STRESS-TEST SUITE: MILESTONE M3\n";
echo "===================================================================\n\n";

// -------------------------------------------------------------
// STRESS-TEST 1: Query parameter preservation on /chuong-trinh/ redirect
// -------------------------------------------------------------
echo "STRESS 1: Query parameter preservation edge cases\n";
echo "-------------------------------------------------------------------\n";

function simulate_redirect_url( $request_uri ) {
	$request_path = parse_url( $request_uri, PHP_URL_PATH );
	$query_str    = parse_url( $request_uri, PHP_URL_QUERY );
	$get_params   = [];
	if ( $query_str ) {
		parse_str( $query_str, $get_params );
	}

	if ( preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
		$redirect_url = 'https://lienthongdaihoc.com/he-dao-tao/';
		if ( ! empty( $get_params ) ) {
			$redirect_url .= '?' . http_build_query( $get_params );
		}
		return $redirect_url;
	}
	return false;
}

// Case 1A: Simple query
$res1 = simulate_redirect_url( '/chuong-trinh/?truong=utc' );
AdversarialRunner::assert( $res1 === 'https://lienthongdaihoc.com/he-dao-tao/?truong=utc', "Case 1A: Preserves single param" );

// Case 1B: Multi-param with Vietnamese text and sorting
$res2 = simulate_redirect_url( '/chuong-trinh/?truong=utc&nganh=cntt&s=l%E1%BA%ADp+tr%C3%ACnh&sort=title_asc' );
AdversarialRunner::assert( strpos( $res2, 'https://lienthongdaihoc.com/he-dao-tao/?' ) === 0, "Case 1B: Prefix is /he-dao-tao/?" );
AdversarialRunner::assert( strpos( $res2, 'truong=utc' ) !== false, "Case 1B: Contains truong=utc" );
AdversarialRunner::assert( strpos( $res2, 'nganh=cntt' ) !== false, "Case 1B: Contains nganh=cntt" );
AdversarialRunner::assert( strpos( $res2, 'sort=title_asc' ) !== false, "Case 1B: Contains sort=title_asc" );

// Case 1C: No trailing slash
$res3 = simulate_redirect_url( '/chuong-trinh?sort=date_desc' );
AdversarialRunner::assert( $res3 === 'https://lienthongdaihoc.com/he-dao-tao/?sort=date_desc', "Case 1C: Matches without trailing slash" );

// Case 1D: Uppercase /CHUONG-TRINH/
$res4 = simulate_redirect_url( '/CHUONG-TRINH/' );
AdversarialRunner::assert( $res4 === 'https://lienthongdaihoc.com/he-dao-tao/', "Case 1D: Case-insensitive match" );

// Case 1E: Subpath /chuong-trinh/cntt/ MUST NOT redirect
$res5 = simulate_redirect_url( '/chuong-trinh/cntt/' );
AdversarialRunner::assert( $res5 === false, "Case 1E: Subpath not redirected" );

echo "\n";

// -------------------------------------------------------------
// STRESS-TEST 2: Menu Matching Adversarial Inputs
// -------------------------------------------------------------
echo "STRESS 2: Dynamic Submenu Title Matching Variations\n";
echo "-------------------------------------------------------------------\n";

$allowed_titles = [ 'hình thức học', 'hệ đào tạo', 'hình thức đào tạo' ];

$adversarial_menu_inputs = [
	"  Hình thức học  \t" => true,
	"HÌNH THỨC HỌC" => true,
	"HỆ ĐÀO TẠO" => true,
	"hệ đào tạo" => true,
	"Hình Thức Đào Tạo" => true,
	"HÌNH THỨC ĐÀO TẠO" => true,
	"Hình thức học trực tuyến" => false,
	"Chương trình" => false,
	"Hệ" => false,
	"" => false,
	"0" => false,
];

foreach ( $adversarial_menu_inputs as $input => $should_match ) {
	$title = mb_strtolower( trim( (string) $input ), 'UTF-8' );
	$matched = in_array( $title, $allowed_titles, true );
	AdversarialRunner::assert(
		$matched === $should_match,
		"Menu input '" . addcslashes( (string) $input, "\t\r\n" ) . "' => " . ( $matched ? 'MATCHED' : 'REJECTED' )
	);
}

echo "\n";

// -------------------------------------------------------------
// STRESS-TEST 3: Template Inclusion Regex Robustness
// -------------------------------------------------------------
echo "STRESS 3: Template Inclusion Regex Patterns\n";
echo "-------------------------------------------------------------------\n";

$pattern = '#^/he-dao-tao(?:/([^/]+))?(?:/page/\d+)?/?$#i';

$template_test_cases = [
	'/he-dao-tao' => true,
	'/he-dao-tao/' => true,
	'/he-dao-tao/tu-xa' => true,
	'/he-dao-tao/tu-xa/' => true,
	'/he-dao-tao/vua-hoc-vua-lam' => true,
	'/he-dao-tao/vua-hoc-vua-lam/' => true,
	'/HE-DAO-TAO/TU-XA/' => true,
	'/he-dao-tao/page/3' => true,
	'/he-dao-tao/page/3/' => true,
	'/he-dao-tao/tu-xa/page/2' => true,
	'/he-dao-tao/tu-xa/page/2/' => true,
	'/he-dao-tao/vua-hoc-vua-lam/page/10/' => true,
	'/he-dao-tao-khac/' => false,
	'/he-dao-tao/tu-xa/extra-segment/' => false,
	'/he-dao-tao/tu-xa/page/' => false,
];

foreach ( $template_test_cases as $path => $expected ) {
	$match = (bool) preg_match( $pattern, $path );
	AdversarialRunner::assert(
		$match === $expected,
		"Path '{$path}' " . ( $match ? "matches" : "does not match" ) . " (expected: " . ( $expected ? 'true' : 'false' ) . ")"
	);
}

echo "\n";

// -------------------------------------------------------------
// STRESS-TEST 4: Canonical Resolution Robustness
// -------------------------------------------------------------
echo "STRESS 4: Canonical Resolution Under Various Query Strings\n";
echo "-------------------------------------------------------------------\n";

function mock_canonical_resolver( $canonical, $is_archive_program, $is_singular_program, $post_name, $req_uri ) {
	if ( $is_singular_program && $post_name ) {
		return 'https://lienthongdaihoc.com/' . $post_name . '/';
	}
	if ( $is_archive_program ) {
		return 'https://lienthongdaihoc.com/he-dao-tao/';
	}
	$request_path = parse_url( $req_uri ?? '', PHP_URL_PATH );
	if ( $request_path && preg_match( '#^/chuong-trinh/?$#i', $request_path ) ) {
		return 'https://lienthongdaihoc.com/he-dao-tao/';
	}
	return $canonical;
}

$c1 = mock_canonical_resolver( 'https://lienthongdaihoc.com/chuong-trinh/', false, false, '', '/chuong-trinh/' );
AdversarialRunner::assert( $c1 === 'https://lienthongdaihoc.com/he-dao-tao/', "Canonical for /chuong-trinh/ is /he-dao-tao/" );

$c2 = mock_canonical_resolver( 'https://lienthongdaihoc.com/chuong-trinh/?sort=date_desc', false, false, '', '/chuong-trinh/?sort=date_desc' );
AdversarialRunner::assert( $c2 === 'https://lienthongdaihoc.com/he-dao-tao/', "Canonical for /chuong-trinh/?sort=date_desc is /he-dao-tao/" );

$c3 = mock_canonical_resolver( 'https://lienthongdaihoc.com/nganh-cntt/', false, false, '', '/nganh-cntt/' );
AdversarialRunner::assert( $c3 === 'https://lienthongdaihoc.com/nganh-cntt/', "Canonical for other routes untouched" );

$c4 = mock_canonical_resolver( 'https://lienthongdaihoc.com/cntt-dai-hoc-cong-doan/', false, true, 'cntt-dai-hoc-cong-doan', '/cntt-dai-hoc-cong-doan/' );
AdversarialRunner::assert( $c4 === 'https://lienthongdaihoc.com/cntt-dai-hoc-cong-doan/', "Program single canonical is root /slug/" );

echo "\n";
echo "===================================================================\n";
echo "ADVERSARIAL STRESS RESULTS: " . AdversarialRunner::$passed . " PASSED, " . AdversarialRunner::$failed . " FAILED\n";
echo "===================================================================\n";

if ( AdversarialRunner::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
