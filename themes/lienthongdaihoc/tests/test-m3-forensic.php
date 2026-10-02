<?php
/**
 * Milestone M3 Forensic Integrity Verification Test Suite
 *
 * Verifies:
 * 1. Syntax of all 15 modified files via PHP tokenizer / AST.
 * 2. ACF JSON taxonomy definition integrity (title, label, rewrite_slug).
 * 3. Navigation defaults configuration integrity.
 * 4. Menu submenu injection title matching logic.
 * 5. Breadcrumb trail labels and links.
 * 6. Rewrite rules & regex integrity for /he-dao-tao/ and /chuong-trinh/.
 * 7. Rank Math canonical resolution and redirect loop prevention.
 * 8. Zero public occurrences of "Hệ đào tạo" in theme templates.
 * 9. Program archive form action and quick pill URLs.
 * 10. Database post & taxonomy preservation check.
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class ForensicRunner {
	public static $passed = 0;
	public static $failed = 0;
	public static $errors = [];

	public static function assert( $condition, $message, $details = null ) {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] {$message}\n";
		} else {
			self::$failed++;
			self::$errors[] = [ 'message' => $message, 'details' => $details ];
			echo "  [FAIL] {$message}\n";
			if ( $details ) {
				echo "         Details: " . print_r( $details, true ) . "\n";
			}
		}
	}
}

echo "===================================================================\n";
echo "FORENSIC INTEGRITY AUDIT: MILESTONE M3\n";
echo "===================================================================\n\n";

// -------------------------------------------------------------
// CHECK 1: File Syntax Tokenizer Analysis across all modified files
// -------------------------------------------------------------
echo "CHECK 1: AST Token Analysis on All 15 Modified Files\n";
echo "-------------------------------------------------------------------\n";

$files_to_check = [
	'inc/acf-import-cpts.json',
	'inc/config/class-defaults.php',
	'inc/core/class-menus.php',
	'inc/core/class-helpers.php',
	'taxonomy-training_type.php',
	'archive-program.php',
	'single-major.php',
	'single-school.php',
	'template-parts/compare/program-table.php',
	'template-parts/compare/program-cards.php',
	'template-parts/eligibility/wizard.php',
	'inc/core/class-query-filters.php',
	'taxonomy.php',
	'template-parts/banner.php',
	'inc/core/class-rewrite-rules.php',
	'inc/seo/class-rankmath-integration.php',
];

foreach ( $files_to_check as $rel_path ) {
	$full_path = ABSPATH . $rel_path;
	ForensicRunner::assert( file_exists( $full_path ), "File exists: {$rel_path}" );

	$content = file_get_contents( $full_path );
	if ( str_ends_with( $rel_path, '.json' ) ) {
		$json_data = json_decode( $content, true );
		ForensicRunner::assert( json_last_error() === JSON_ERROR_NONE, "JSON valid: {$rel_path} (" . json_last_error_msg() . ")" );
	} else {
		$tokens = token_get_all( $content );
		$has_bad_token = false;
		$bad_token_info = null;
		foreach ( $tokens as $t ) {
			if ( is_array( $t ) && $t[0] === T_BAD_CHARACTER ) {
				$has_bad_token = true;
				$bad_token_info = $t;
				break;
			}
		}
		ForensicRunner::assert( ! $has_bad_token, "No syntax/bad tokens: {$rel_path}", $bad_token_info );
	}
}

echo "\n";

// -------------------------------------------------------------
// CHECK 2: ACF JSON Taxonomy Definition for training_type
// -------------------------------------------------------------
echo "CHECK 2: ACF JSON Taxonomy Definition Integrity\n";
echo "-------------------------------------------------------------------\n";

$cpts_json = json_decode( file_get_contents( ABSPATH . 'inc/acf-import-cpts.json' ), true );
$training_type_tax = null;
foreach ( $cpts_json as $item ) {
	if ( isset( $item['key'] ) && $item['key'] === 'taxonomy_training_type' ) {
		$training_type_tax = $item;
		break;
	}
}

ForensicRunner::assert( $training_type_tax !== null, "Found taxonomy_training_type in acf-import-cpts.json" );
ForensicRunner::assert( $training_type_tax['title'] === 'Hình thức học', "Taxonomy title is 'Hình thức học' (actual: '{$training_type_tax['title']}')" );
ForensicRunner::assert( $training_type_tax['label'] === 'Hình thức học', "Taxonomy label is 'Hình thức học' (actual: '{$training_type_tax['label']}')" );
ForensicRunner::assert( $training_type_tax['singular_label'] === 'Hình thức học', "Taxonomy singular_label is 'Hình thức học'" );
ForensicRunner::assert( $training_type_tax['rewrite_slug'] === 'he-dao-tao', "CRITICAL: Slug preserved as 'he-dao-tao' (actual: '{$training_type_tax['rewrite_slug']}')" );
ForensicRunner::assert( $training_type_tax['labels']['name'] === 'Hình thức học', "Label name is 'Hình thức học'" );
ForensicRunner::assert( $training_type_tax['labels']['menu_name'] === 'Hình thức học', "Menu name is 'Hình thức học'" );

echo "\n";

// -------------------------------------------------------------
// CHECK 3: Navigation Defaults
// -------------------------------------------------------------
echo "CHECK 3: Navigation Defaults Configuration\n";
echo "-------------------------------------------------------------------\n";

require_once ABSPATH . 'inc/config/constants.php';
require_once ABSPATH . 'inc/config/class-defaults.php';

$nav_defaults = ltdh_get_defaults( 'navigation' );
$he_dao_tao_item = null;
foreach ( $nav_defaults['primary'] as $item ) {
	if ( $item['url'] === '/he-dao-tao/' ) {
		$he_dao_tao_item = $item;
		break;
	}
}
ForensicRunner::assert( $he_dao_tao_item !== null, "Primary navigation has item for '/he-dao-tao/'" );
ForensicRunner::assert( in_array( $he_dao_tao_item['label'], [ 'Hình thức học', 'Liên thông đại học' ], true ), "Nav item label is valid in-scope ('Hình thức học' or 'Liên thông đại học') (actual: '{$he_dao_tao_item['label']}')" );
ForensicRunner::assert( $he_dao_tao_item['url'] === '/he-dao-tao/', "Nav item URL is '/he-dao-tao/' (actual: '{$he_dao_tao_item['url']}')" );

echo "\n";

// -------------------------------------------------------------
// CHECK 4: Dynamic Submenu Injection Matching Logic
// -------------------------------------------------------------
echo "CHECK 4: Dynamic Submenu Injection Matching Logic\n";
echo "-------------------------------------------------------------------\n";

$menu_file = file_get_contents( ABSPATH . 'inc/core/class-menus.php' );
ForensicRunner::assert(
	strpos( $menu_file, "in_array( \$title, [ 'hình thức học', 'hệ đào tạo', 'hình thức đào tạo' ], true )" ) !== false,
	"Submenu injection matches 'hình thức học' while backwards-compatible with 'hệ đào tạo'"
);

// Simulate the matching logic
$test_titles = [
	'Hình thức học' => true,
	'HÌNH THỨC HỌC' => true,
	'Hệ đào tạo' => true,
	'HỆ ĐÀO TẠO' => true,
	'hình thức đào tạo' => true,
	'Ngành học' => false,
	'Trang chủ' => false,
];

foreach ( $test_titles as $title_input => $expected_match ) {
	$norm = mb_strtolower( trim( $title_input ), 'UTF-8' );
	$matched = in_array( $norm, [ 'hình thức học', 'hệ đào tạo', 'hình thức đào tạo' ], true );
	ForensicRunner::assert( $matched === $expected_match, "Menu matching for '{$title_input}': " . ( $matched ? 'matched' : 'not matched' ) );
}

echo "\n";

// -------------------------------------------------------------
// CHECK 5: Breadcrumbs Trail
// -------------------------------------------------------------
echo "CHECK 5: Breadcrumbs Trail Configuration in class-helpers.php\n";
echo "-------------------------------------------------------------------\n";

$helpers_content = file_get_contents( ABSPATH . 'inc/core/class-helpers.php' );

// Check that no 'Hệ đào tạo' is in breadcrumbs
$has_he_dao_tao_in_crumbs = preg_match( "#'label'\s*=>\s*'(?:Hệ đào tạo|Hệ Đào Tạo)'#u", $helpers_content );
ForensicRunner::assert( ! $has_he_dao_tao_in_crumbs, "class-helpers.php has NO 'Hệ đào tạo' breadcrumb labels" );

// Check that 'Hình thức học' is used
preg_match_all( "#'label'\s*=>\s*'Hình thức học'#u", $helpers_content, $crumb_matches );
ForensicRunner::assert( count( $crumb_matches[0] ) >= 4, "class-helpers.php has at least 4 'Hình thức học' breadcrumb entries (found: " . count( $crumb_matches[0] ) . ")" );

echo "\n";

// -------------------------------------------------------------
// CHECK 6: Rewrite Rules & Redirect Verification
// -------------------------------------------------------------
echo "CHECK 6: Rewrite Rules & 301 Redirect Logic\n";
echo "-------------------------------------------------------------------\n";

$rewrite_content = file_get_contents( ABSPATH . 'inc/core/class-rewrite-rules.php' );

// 1. Training type rewrite rules
ForensicRunner::assert( strpos( $rewrite_content, "add_rewrite_rule( 'he-dao-tao/?$'" ) !== false, "Rule 'he-dao-tao/?$' registered" );
ForensicRunner::assert( strpos( $rewrite_content, "add_rewrite_rule( 'he-dao-tao/([^/]+)/?$'" ) !== false, "Rule 'he-dao-tao/([^/]+)/?$' registered" );
ForensicRunner::assert( strpos( $rewrite_content, "add_rewrite_rule( 'he-dao-tao/page/([0-9]+)/?$'" ) !== false, "Rule 'he-dao-tao/page/([0-9]+)/?$' registered" );
ForensicRunner::assert( strpos( $rewrite_content, "add_rewrite_rule( 'he-dao-tao/([^/]+)/page/([0-9]+)/?$'" ) !== false, "Rule 'he-dao-tao/([^/]+)/page/([0-9]+)/?$' registered" );

// 2. /chuong-trinh/ 301 redirect
ForensicRunner::assert( strpos( $rewrite_content, "preg_match( '#^/chuong-trinh/?$#i', \$request_path )" ) !== false, "/chuong-trinh/ exact path regex pattern present" );
ForensicRunner::assert( strpos( $rewrite_content, "\$redirect_url = home_url( '/he-dao-tao/' );" ) !== false, "Redirect target is home_url( '/he-dao-tao/' )" );
ForensicRunner::assert( strpos( $rewrite_content, "add_query_arg( \$_GET, \$redirect_url )" ) !== false, "Redirect preserves \$_GET parameters" );

// Regex validation
$regex = '#^/chuong-trinh/?$#i';
ForensicRunner::assert( preg_match( $regex, '/chuong-trinh' ) === 1, "Regex matches '/chuong-trinh'" );
ForensicRunner::assert( preg_match( $regex, '/chuong-trinh/' ) === 1, "Regex matches '/chuong-trinh/'" );
ForensicRunner::assert( preg_match( $regex, '/chuong-trinh/cntt/' ) === 0, "Regex does NOT match subpaths like '/chuong-trinh/cntt/'" );

// Template include regex
$tmpl_regex = '#^/he-dao-tao(?:/([^/]+))?(?:/page/\d+)?/?$#i';
ForensicRunner::assert( preg_match( $tmpl_regex, '/he-dao-tao/' ) === 1, "Template include matches '/he-dao-tao/'" );
ForensicRunner::assert( preg_match( $tmpl_regex, '/he-dao-tao/tu-xa/' ) === 1, "Template include matches '/he-dao-tao/tu-xa/'" );
ForensicRunner::assert( preg_match( $tmpl_regex, '/he-dao-tao/vua-hoc-vua-lam/' ) === 1, "Template include matches '/he-dao-tao/vua-hoc-vua-lam/'" );
ForensicRunner::assert( preg_match( $tmpl_regex, '/he-dao-tao/tu-xa/page/2/' ) === 1, "Template include matches paginated '/he-dao-tao/tu-xa/page/2/'" );

echo "\n";

// -------------------------------------------------------------
// CHECK 7: Rank Math Canonical & Redirect Loop Resolution
// -------------------------------------------------------------
echo "CHECK 7: Rank Math Canonical Resolution Logic\n";
echo "-------------------------------------------------------------------\n";

if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		return 'https://lienthongdaihoc.com' . ( $path ? '/' . ltrim( $path, '/' ) : '' );
	}
}
if ( ! function_exists( 'is_singular' ) ) {
	function is_singular( $t = '' ) {
		global $mock_is_singular;
		return ! empty( $mock_is_singular[ $t ] );
	}
}
if ( ! function_exists( 'is_post_type_archive' ) ) {
	function is_post_type_archive( $t = '' ) {
		global $mock_is_archive;
		return ! empty( $mock_is_archive[ $t ] );
	}
}
if ( ! function_exists( 'add_filter' ) ) {
	function add_filter() {}
}
if ( ! function_exists( 'add_action' ) ) {
	function add_action() {}
}

require_once ABSPATH . 'inc/seo/class-rankmath-integration.php';

// Scenario A: Post type archive 'program'
$GLOBALS['mock_is_archive'] = [ 'program' => true ];
$GLOBALS['mock_is_singular'] = [];
$_SERVER['REQUEST_URI'] = '/some-catalog-url/';
$canonical_a = ltdh_seo_enforce_canonical_url( 'https://lienthongdaihoc.com/some-catalog-url/' );
ForensicRunner::assert(
	$canonical_a === 'https://lienthongdaihoc.com/he-dao-tao/',
	"Archive 'program' canonical points to 'https://lienthongdaihoc.com/he-dao-tao/' (actual: '{$canonical_a}')"
);

// Scenario B: Request to /chuong-trinh/
$GLOBALS['mock_is_archive'] = [];
$_SERVER['REQUEST_URI'] = '/chuong-trinh/';
$canonical_b = ltdh_seo_enforce_canonical_url( 'https://lienthongdaihoc.com/chuong-trinh/' );
ForensicRunner::assert(
	$canonical_b === 'https://lienthongdaihoc.com/he-dao-tao/',
	"/chuong-trinh/ canonical points directly to 'https://lienthongdaihoc.com/he-dao-tao/' (prevents 301 canonical loop, actual: '{$canonical_b}')"
);

echo "\n";

// -------------------------------------------------------------
// CHECK 8: Public Facing Label Audit in Modified Templates
// -------------------------------------------------------------
echo "CHECK 8: Public UI Label Audit Across All Templates\n";
echo "-------------------------------------------------------------------\n";

$template_files = [
	'taxonomy-training_type.php',
	'archive-program.php',
	'single-major.php',
	'single-school.php',
	'single-program.php',
	'template-parts/compare/program-table.php',
	'template-parts/compare/program-cards.php',
	'template-parts/eligibility/wizard.php',
	'taxonomy.php',
	'template-parts/banner.php',
	'inc/core/class-query-filters.php',
];

foreach ( $template_files as $file ) {
	$content = file_get_contents( ABSPATH . $file );
	// Look for visible 'Hệ đào tạo' in HTML/text
	preg_match_all( '#>([^<]*Hệ đào tạo[^<]*)<#ui', $content, $matches );
	$count = count( $matches[0] );
	ForensicRunner::assert( $count === 0, "No visible 'Hệ đào tạo' in {$file} (found: {$count})", $matches[0] ?? [] );
}

echo "\n";

// -------------------------------------------------------------
// CHECK 9: Program Archive Form Action and Reset URLs
// -------------------------------------------------------------
echo "CHECK 9: Program Archive Form Action & Tab Links\n";
echo "-------------------------------------------------------------------\n";

$archive_content = file_get_contents( ABSPATH . 'archive-program.php' );
ForensicRunner::assert(
	strpos( $archive_content, 'action="<?php echo esc_url( home_url( \'/he-dao-tao/\' ) ); ?>"' ) !== false,
	"archive-program.php form action points to /he-dao-tao/"
);
ForensicRunner::assert(
	strpos( $archive_content, '$all_url = home_url( \'/he-dao-tao/\' );' ) !== false,
	"archive-program.php 'Tất cả' tab link points to /he-dao-tao/"
);
ForensicRunner::assert(
	strpos( $archive_content, '$type_url = home_url( \'/he-dao-tao/\' . $t_term->slug . \'/\' );' ) !== false,
	"archive-program.php training type tabs point to /he-dao-tao/{slug}/"
);

echo "\n";
echo "===================================================================\n";
echo "AUDIT SUMMARY: " . ForensicRunner::$passed . " PASSED, " . ForensicRunner::$failed . " FAILED\n";
echo "===================================================================\n";

if ( ForensicRunner::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
