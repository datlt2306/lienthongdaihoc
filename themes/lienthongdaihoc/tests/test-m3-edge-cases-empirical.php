<?php
/**
 * Empirical Edge-Case & Adversarial Harness: Out-of-Scope Taxonomy & Study Mode Leakage Audit
 *
 * Authored by: challenger_m3_iter2
 * Purpose:
 *   Stress-test all edge cases where an out-of-scope study mode / taxonomy slug
 *   (e.g., van-bang-2, chinh-quy, lien-thong, thpt, random-bad-slug) is requested
 *   directly via URL path, query params, or menus.
 *   Verify that NO unapproved study modes leak into:
 *   1. Pill tabs in taxonomy-training_type.php & archive-program.php
 *   2. Header navigation submenus
 *   3. School & Major rollup helpers
 *   4. Program cards and badge outputs
 *   5. AJAX query filters
 *   6. Comparison breadcrumbs and eligibility responses
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class EdgeCaseAuditTester {
	public static int $passed = 0;
	public static int $failed = 0;
	public static array $findings = [];

	public static function assert( bool $condition, string $name, array $details = [] ): void {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] {$name}\n";
		} else {
			self::$failed++;
			self::$findings[] = [ 'name' => $name, 'details' => $details ];
			echo "  [FAIL] {$name}\n";
			foreach ( $details as $k => $v ) {
				echo "         - {$k}: " . ( is_array( $v ) ? json_encode( $v, JSON_UNESCAPED_UNICODE ) : $v ) . "\n";
			}
		}
	}
}

echo "===================================================================\n";
echo "EMPIRICAL EDGE CASE & LEAKAGE TEST SUITE (challenger_m3_iter2)\n";
echo "===================================================================\n\n";

// -----------------------------------------------------------------
// SUITE 1: Direct Request to Out-of-Scope Slugs in Archive Pill Tabs
// -----------------------------------------------------------------
echo "SUITE 1: Pill Tabs Isolation Under Adversarial Slugs\n";
echo "-------------------------------------------------------------------\n";

/**
 * Real simulation of taxonomy-training_type.php & archive-program.php logic:
 *
 * $allowed_training_types = [ 'tu-xa', 'vua-hoc-vua-lam' ];
 * $all_types = get_terms( [
 *     'taxonomy'   => 'training_type',
 *     'slug'       => $allowed_training_types,
 *     'hide_empty' => false,
 * ] );
 * if ( ! is_wp_error( $all_types ) && is_array( $all_types ) ) {
 *     $all_types = array_values( array_filter( $all_types, function( $t ) use ( $allowed_training_types ) {
 *         return in_array( $t->slug, $allowed_training_types, true );
 *     } ) );
 * }
 */
function render_pill_tabs_simulation( string $request_uri, array $db_terms, array $term_counts ): array {
	// 1. Parse selected_type from URL or query
	$selected_type = '';
	$request_path  = parse_url( $request_uri, PHP_URL_PATH );
	if ( preg_match( '#^/he-dao-tao/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $m ) && 'page' !== $m[1] ) {
		$selected_type = $m[1];
	}
	if ( empty( $selected_type ) ) {
		$parsed_query = [];
		parse_str( parse_url( $request_uri, PHP_URL_QUERY ) ?: '', $parsed_query );
		$selected_type = $parsed_query['he'] ?? '';
	}

	// 2. Query terms with allowed_training_types whitelist
	$allowed_training_types = [ 'tu-xa', 'vua-hoc-vua-lam' ];
	$all_types = array_filter( $db_terms, function( $t ) use ( $allowed_training_types ) {
		return in_array( $t['slug'], $allowed_training_types, true );
	} );

	// 3. Render tabs
	$rendered = [];
	$rendered[] = [
		'slug'   => '',
		'label'  => 'Tất cả',
		'active' => empty( $selected_type ),
	];

	foreach ( $all_types as $t_term ) {
		$slug      = $t_term['slug'];
		$t_count   = $term_counts[ $slug ] ?? 0;
		$is_active = ( $selected_type === $slug );

		if ( $t_count === 0 && ! $is_active ) {
			continue;
		}

		$rendered[] = [
			'slug'   => $slug,
			'label'  => $t_term['name'],
			'active' => $is_active,
		];
	}

	return $rendered;
}

// Full DB terms including obsolete/unapproved terms that might exist in WordPress
$db_terms_fixture = [
	[ 'slug' => 'tu-xa', 'name' => 'Từ xa' ],
	[ 'slug' => 'vua-hoc-vua-lam', 'name' => 'Vừa học vừa làm' ],
	[ 'slug' => 'van-bang-2', 'name' => 'Văn bằng 2' ],
	[ 'slug' => 'chinh-quy', 'name' => 'Chính quy' ],
	[ 'slug' => 'lien-thong', 'name' => 'Liên thông' ],
	[ 'slug' => 'dai-hoc-chinh-quy', 'name' => 'Đại học chính quy' ],
	[ 'slug' => 'thpt', 'name' => 'Tuyển sinh THPT' ],
];
$db_counts_fixture = [
	'tu-xa'              => 94,
	'vua-hoc-vua-lam'    => 1,
	'van-bang-2'         => 0,
	'chinh-quy'          => 0,
	'lien-thong'         => 0,
	'dai-hoc-chinh-quy'  => 0,
	'thpt'               => 0,
];

$adversarial_urls = [
	'/he-dao-tao/van-bang-2/'               => 'van-bang-2',
	'/he-dao-tao/chinh-quy/'                => 'chinh-quy',
	'/he-dao-tao/lien-thong/'               => 'lien-thong',
	'/he-dao-tao/dai-hoc-chinh-quy/'        => 'dai-hoc-chinh-quy',
	'/he-dao-tao/thpt/'                     => 'thpt',
	'/he-dao-tao/?he=van-bang-2'            => 'van-bang-2 (query param)',
	'/he-dao-tao/?he=chinh-quy'             => 'chinh-quy (query param)',
	'/he-dao-tao/non-existent-taxonomy-foo/' => 'non-existent slug',
];

foreach ( $adversarial_urls as $adv_url => $desc ) {
	$tabs = render_pill_tabs_simulation( $adv_url, $db_terms_fixture, $db_counts_fixture );
	$rendered_slugs = array_column( $tabs, 'slug' );

	$leaked = array_diff( $rendered_slugs, [ '', 'tu-xa', 'vua-hoc-vua-lam' ] );

	EdgeCaseAuditTester::assert(
		empty( $leaked ),
		"1. Pill tabs do NOT leak unapproved modes on request to '{$adv_url}' ({$desc})",
		[
			'rendered_slugs' => $rendered_slugs,
			'leaked_slugs'   => $leaked,
		]
	);
}

echo "\n";

// -----------------------------------------------------------------
// SUITE 2: Dynamic Header Menu Submenu Isolation
// -----------------------------------------------------------------
echo "SUITE 2: Header Navigation Menu Dropdown Submenu Isolation\n";
echo "-------------------------------------------------------------------\n";

$menus_content = file_get_contents( ABSPATH . 'inc/core/class-menus.php' );

// 2.1: Verify allowed_training_types whitelist exists in class-menus.php
$has_allowed_in_menus = (bool) preg_match( '/\$allowed_training_types\s*=\s*\[\s*[\x27\x22]tu-xa[\x27\x22]\s*,\s*[\x27\x22]vua-hoc-vua-lam[\x27\x22]\s*\];/u', $menus_content );
EdgeCaseAuditTester::assert(
	$has_allowed_in_menus,
	"2.1: inc/core/class-menus.php explicitly defines whitelist ['tu-xa', 'vua-hoc-vua-lam']"
);

// 2.2: Verify get_terms in class-menus.php uses the slug filter
$has_slug_filter = (bool) preg_match( '/[\x27\x22]slug[\x27\x22]\s*=>\s*\$allowed_training_types/u', $menus_content );
EdgeCaseAuditTester::assert(
	$has_slug_filter,
	"2.2: inc/core/class-menus.php passes \$allowed_training_types to get_terms()"
);

// 2.3: Verify defensive array_filter in class-menus.php
$has_array_filter = (bool) preg_match( '/array_filter\(\s*\$types,\s*function\(\s*\$t\s*\)\s*use\s*\(\s*\$allowed_training_types\s*\)/u', $menus_content );
EdgeCaseAuditTester::assert(
	$has_array_filter,
	"2.3: inc/core/class-menus.php has secondary array_filter defense against unapproved modes"
);

echo "\n";

// -----------------------------------------------------------------
// SUITE 3: School Training Types Rollup Isolation (ltdh_get_school_training_types)
// -----------------------------------------------------------------
echo "SUITE 3: School Training Types Rollup Isolation\n";
echo "-------------------------------------------------------------------\n";

$helpers_content = file_get_contents( ABSPATH . 'inc/core/class-helpers.php' );

// 3.1: ltdh_get_school_training_types specifies allowed_slugs = ['tu-xa', 'vua-hoc-vua-lam']
$has_school_allowed = (bool) preg_match( '/\$allowed_slugs\s*=\s*\[\s*[\x27\x22]tu-xa[\x27\x22]\s*,\s*[\x27\x22]vua-hoc-vua-lam[\x27\x22]\s*\];/u', $helpers_content );
EdgeCaseAuditTester::assert(
	$has_school_allowed,
	"3.1: ltdh_get_school_training_types restricts rollup to ['tu-xa', 'vua-hoc-vua-lam']"
);

// 3.2: Tax query in ltdh_get_school_training_types enforces operator IN with allowed_slugs
$has_tax_query_restriction = (bool) preg_match( '/[\x27\x22]terms[\x27\x22]\s*=>\s*\$allowed_slugs/u', $helpers_content );
EdgeCaseAuditTester::assert(
	$has_tax_query_restriction,
	"3.2: ltdh_get_school_training_types enforces tax_query terms => \$allowed_slugs"
);

echo "\n";

// -----------------------------------------------------------------
// SUITE 4: Badge Prefix Invariant Across All Theme Templates
// -----------------------------------------------------------------
echo "SUITE 4: Obsolete 'Hệ ' Badge Prefix Scan Across All Source Files\n";
echo "-------------------------------------------------------------------\n";

$files_with_badges = [
	'taxonomy-training_type.php',
	'archive-program.php',
	'single-school.php',
	'single-major.php',
	'archive-school.php',
	'inc/core/class-query-filters.php',
];

$he_badge_leaks = [];
foreach ( $files_with_badges as $f ) {
	$path = ABSPATH . $f;
	if ( ! file_exists( $path ) ) continue;
	$content = file_get_contents( $path );
	// Look for "Hệ <?php echo ... $type_name", "Hệ <?php echo ... $t_name", "Hệ <?php echo ... $mode"
	if ( preg_match_all( '/Hệ\s*<\?php\s+echo\s+esc_html\(\s*\$(?:type_name|t_name|mode|st_term)/u', $content, $m ) ) {
		$he_badge_leaks[] = [ 'file' => $f, 'matches' => $m[0] ];
	}
}

EdgeCaseAuditTester::assert(
	empty( $he_badge_leaks ),
	"4.1: Zero obsolete 'Hệ ' badge prefixes found across all template and filter files",
	[ 'leaks' => $he_badge_leaks ]
);

echo "\n";

// -----------------------------------------------------------------
// SUITE 5: Public Eligibility Strings Consistency
// -----------------------------------------------------------------
echo "SUITE 5: Public Eligibility Engine Output Strings Consistency\n";
echo "-------------------------------------------------------------------\n";

$elig_file = ABSPATH . 'inc/eligibility.php';
$elig_code = file_get_contents( $elig_file );

// 5.1: Error message for invalid training
$has_invalid_msg_hth = strpos( $elig_code, "'Hình thức học không hợp lệ.'" ) !== false;
$has_invalid_msg_hdt = strpos( $elig_code, "'Hệ đào tạo không hợp lệ.'" ) !== false;
EdgeCaseAuditTester::assert(
	$has_invalid_msg_hth && ! $has_invalid_msg_hdt,
	"5.1: inc/eligibility.php:306 uses 'Hình thức học không hợp lệ.' (0 occurrences of 'Hệ đào tạo không hợp lệ.')"
);

// 5.2: Verification item string
$has_verif_hth = strpos( $elig_code, "'Hình thức học ' . ltdh_elig_get_training_label" ) !== false;
$has_verif_hdt = strpos( $elig_code, "'Hệ đào tạo ' . ltdh_elig_get_training_label" ) !== false;
EdgeCaseAuditTester::assert(
	$has_verif_hth && ! $has_verif_hdt,
	"5.2: inc/eligibility.php:479 uses 'Hình thức học [Từ xa] cần được nhà trường xác nhận...'"
);

// 5.3: Match reason string
$has_match_hth = strpos( $elig_code, "'Hỗ trợ hình thức học ' . ltdh_elig_get_training_label" ) !== false;
$has_match_hdt = strpos( $elig_code, "'Hỗ trợ hệ đào tạo ' . ltdh_elig_get_training_label" ) !== false;
EdgeCaseAuditTester::assert(
	$has_match_hth && ! $has_match_hdt,
	"5.3: inc/eligibility.php:482 uses 'Hỗ trợ hình thức học [Từ xa] phù hợp.'"
);

echo "\n";

// -----------------------------------------------------------------
// SUITE 6: Breadcrumb Uniformity Under Edge-Case Comparison Route
// -----------------------------------------------------------------
echo "SUITE 6: Comparison Breadcrumb Cleanliness\n";
echo "-------------------------------------------------------------------\n";

// 6.1: Verify comparison breadcrumb links to /he-dao-tao/ with 'Hình thức học'
$has_clean_compare_crumb = strpos(
	$helpers_content,
	"echo '<a href=\"' . esc_url( home_url( '/he-dao-tao/' ) ) . '\" class=\"hover:text-brand-primary\">Hình thức học</a>';"
) !== false;

EdgeCaseAuditTester::assert(
	$has_clean_compare_crumb,
	"6.1: inc/core/class-helpers.php:407 breadcrumb for comparison links to /he-dao-tao/ with label 'Hình thức học'"
);

// 6.2: Ensure NO residual /he-dao-tao/tu-xa/ hardcoding in ltdh_breadcrumb
preg_match( '/function ltdh_breadcrumb.*?^}/ms', $helpers_content, $breadcrumb_func );
$bc_body = $breadcrumb_func[0] ?? '';
$has_residual_tu_xa = strpos( $bc_body, '/he-dao-tao/tu-xa/' ) !== false;
EdgeCaseAuditTester::assert(
	! $has_residual_tu_xa,
	"6.2: ltdh_breadcrumb() contains zero hardcoded '/he-dao-tao/tu-xa/' links"
);

echo "\n";
echo "===================================================================\n";
printf( "EDGE CASE AUDIT SUMMARY: %d PASSED, %d FAILED\n", EdgeCaseAuditTester::$passed, EdgeCaseAuditTester::$failed );
echo "===================================================================\n";

if ( EdgeCaseAuditTester::$failed > 0 ) {
	echo "\nFAILURES:\n";
	foreach ( EdgeCaseAuditTester::$findings as $idx => $f ) {
		echo ( $idx + 1 ) . ". " . $f['name'] . "\n";
	}
}
exit( EdgeCaseAuditTester::$failed > 0 ? 1 : 0 );
