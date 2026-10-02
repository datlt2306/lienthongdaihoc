<?php
/**
 * Adversarial Critic & Stress-Testing Suite for Milestone M4 (Homepage & Filters)
 * Agent: reviewer_m4_2
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class M4AdversarialTester {
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
echo "ADVERSARIAL STRESS-TEST & INTEGRITY AUDIT: HOMEPAGE & FILTERS (M4)\n";
echo "===================================================================\n\n";

$front_page_path = dirname( __DIR__ ) . '/front-page.php';
$defaults_path   = dirname( __DIR__ ) . '/inc/config/class-defaults.php';

$fp_content = file_get_contents( $front_page_path );
$def_content = file_get_contents( $defaults_path );

// -------------------------------------------------------------
// SUITE 1: INTEGRITY & FACADE AUDIT
// -------------------------------------------------------------
echo "SUITE 1: Integrity & Facade Audit\n";
echo "-------------------------------------------------------------------\n";

// Check if any hardcoded test flags or cheats are in front-page.php
$cheats_found = (
	stripos( $fp_content, 'test_override' ) !== false ||
	stripos( $fp_content, 'mock_test_mode' ) !== false ||
	stripos( $fp_content, 'UNIT_TESTING' ) !== false
);
M4AdversarialTester::assert(
	! $cheats_found,
	"1.1: front-page.php contains zero test bypasses or fake hardcoded flags",
	[ 'cheats_found' => $cheats_found ]
);

// Check if defaults has real logic returning an array
require_once $defaults_path;
$homepage_defs = ltdh_get_defaults( 'homepage' );
M4AdversarialTester::assert(
	is_array( $homepage_defs ) && ! empty( $homepage_defs['hero_badges'] ),
	"1.2: ltdh_get_defaults('homepage') executes genuine array configuration",
	[ 'hero_badges_count' => count( $homepage_defs['hero_badges'] ?? [] ) ]
);


// -------------------------------------------------------------
// SUITE 2: SEMANTIC H1 RIGOROUS AUDIT
// -------------------------------------------------------------
echo "\nSUITE 2: Semantic H1 Rigorous Audit\n";
echo "-------------------------------------------------------------------\n";

// Exactly ONE <h1> tag on the homepage
preg_match_all( '/<h1\b[^>]*>(.*?)<\/h1>/is', $fp_content, $h1_matches );
$h1_count = count( $h1_matches[0] ?? [] );
M4AdversarialTester::assert(
	$h1_count === 1,
	"2.1: Exactly one <h1> tag exists in front-page.php for optimal SEO hierarchy",
	[ 'count' => $h1_count, 'tags' => $h1_matches[0] ?? [] ]
);

$h1_tag = $h1_matches[0][0] ?? '';
$h1_inner = trim( strip_tags( $h1_matches[1][0] ?? '' ) );

M4AdversarialTester::assert(
	strpos( $h1_tag, 'class="sr-only"' ) !== false,
	"2.2: <h1> tag contains 'sr-only' class for screen reader and crawler discovery",
	[ 'tag' => $h1_tag ]
);

$banned_terms_in_h1 = [ 'văn bằng 2', 'vb2', 'cao đẳng', 'chính quy', 'thpt', 'sau đại học', 'thạc sĩ' ];
$found_banned_in_h1 = [];
foreach ( $banned_terms_in_h1 as $term ) {
	if ( mb_stripos( $h1_inner, $term ) !== false ) {
		$found_banned_in_h1[] = $term;
	}
}
M4AdversarialTester::assert(
	empty( $found_banned_in_h1 ),
	"2.3: <h1> contains zero out-of-scope terms (VB2, Cao đẳng, Chính quy, THPT)",
	[ 'banned_found' => $found_banned_in_h1, 'h1_inner' => $h1_inner ]
);

M4AdversarialTester::assert(
	$h1_inner === 'Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hình Thức Từ Xa & Vừa Học Vừa Làm',
	"2.4: <h1> matches exact canonical string",
	[ 'actual' => $h1_inner ]
);


// -------------------------------------------------------------
// SUITE 3: HOMEPAGE SEARCH FORM & RESET ACTION AUDIT
// -------------------------------------------------------------
echo "\nSUITE 3: Search Form & Reset Action Audit\n";
echo "-------------------------------------------------------------------\n";

// Form action check
preg_match( '/<form\s+action="([^"]+)"[^>]*method="GET"/i', $fp_content, $form_match );
$form_action = $form_match[1] ?? '';

M4AdversarialTester::assert(
	preg_match( '/home_url\(\s*[\'"]\/he-dao-tao\/[\'"]\s*\)/', $form_action ) === 1,
	"3.1: Form action evaluates strictly to home_url('/he-dao-tao/')",
	[ 'action' => $form_action ]
);

M4AdversarialTester::assert(
	strpos( $form_action, 'tu-xa' ) === false,
	"3.2: Form action does NOT point to /tu-xa/ or /he-dao-tao/tu-xa/",
	[ 'action' => $form_action ]
);

// Reset button check
preg_match( '/<a\s+href="([^"]+)"[^>]*title="Reset bộ lọc"/i', $fp_content, $reset_match );
$reset_href = $reset_match[1] ?? '';

M4AdversarialTester::assert(
	preg_match( '/home_url\(\s*[\'"]\/he-dao-tao\/[\'"]\s*\)/', $reset_href ) === 1,
	"3.3: Reset button href evaluates strictly to home_url('/he-dao-tao/')",
	[ 'reset_href' => $reset_href ]
);

// Dropdown 'he' default text check
preg_match( '/<select\s+name="he"[^>]*>(.*?)<\/select>/is', $fp_content, $he_select_match );
$he_select_inner = $he_select_match[1] ?? '';
M4AdversarialTester::assert(
	strpos( $he_select_inner, '-- Chọn hình thức học --' ) !== false,
	"3.4: 'he' select dropdown default option is '-- Chọn hình thức học --'",
	[ 'he_select_inner' => $he_select_inner ]
);


// -------------------------------------------------------------
// SUITE 4: ELIGIBILITY SECTION AUDIT
// -------------------------------------------------------------
echo "\nSUITE 4: Eligibility Section Audit\n";
echo "-------------------------------------------------------------------\n";

// Heading check
preg_match( '/<h2[^>]*font-display[^>]*>(.*?)<\/h2>/is', $fp_content, $elig_h2_match );
$elig_h2 = trim( $elig_h2_match[1] ?? '' );
M4AdversarialTester::assert(
	strpos( $elig_h2, 'Bạn có đủ điều kiện học<br>Liên thông Đại học?' ) !== false,
	"4.1: Eligibility heading default asks specifically about Liên thông Đại học",
	[ 'h2' => $elig_h2 ]
);

// THPT check
M4AdversarialTester::assert(
	stripos( $fp_content, 'Học sinh tốt nghiệp THPT' ) === false &&
	stripos( $fp_content, 'tốt nghiệp THPT' ) === false,
	"4.2: Zero occurrences of 'THPT' or 'tốt nghiệp THPT' in front-page.php",
	[]
);

// 3 Default items check
$has_tc = strpos( $fp_content, "['title' => 'Tốt nghiệp Trung cấp', 'desc' => 'Liên thông lên Đại học']" ) !== false;
$has_cd = strpos( $fp_content, "['title' => 'Tốt nghiệp Cao đẳng', 'desc' => 'Liên thông miễn giảm tín chỉ']" ) !== false;
$has_dh = strpos( $fp_content, "['title' => 'Đã có bằng Đại học', 'desc' => 'Liên thông văn bằng thứ hai']" ) !== false;

M4AdversarialTester::assert(
	$has_tc && $has_cd && $has_dh,
	"4.3: Eligibility section default levels are exclusively Trung cấp, Cao đẳng, Đại học",
	[ 'has_tc' => $has_tc, 'has_cd' => $has_cd, 'has_dh' => $has_dh ]
);


// -------------------------------------------------------------
// SUITE 5: TESTIMONIALS & NEWS FALLBACKS
// -------------------------------------------------------------
echo "\nSUITE 5: Testimonials & News Fallbacks\n";
echo "-------------------------------------------------------------------\n";

// Testimonial check
M4AdversarialTester::assert(
	strpos( $fp_content, "'role' => 'Liên thông Công nghệ thông tin'" ) !== false,
	"5.1: Testimonial 1 role is 'Liên thông Công nghệ thông tin'",
	[]
);
M4AdversarialTester::assert(
	stripos( $fp_content, 'VB2 Công nghệ thông tin' ) === false &&
	stripos( $fp_content, 'Văn bằng 2 Công nghệ thông tin' ) === false,
	"5.2: Testimonial contains zero VB2 references",
	[]
);

// News mock array check
M4AdversarialTester::assert(
	strpos( $fp_content, 'Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026' ) !== false,
	"5.3: News fallback contains 'Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026'",
	[]
);
M4AdversarialTester::assert(
	stripos( $fp_content, 'Điều kiện học Văn bằng 2 đại học năm 2026' ) === false,
	"5.4: News fallback contains zero out-of-scope articles",
	[]
);


// -------------------------------------------------------------
// SUITE 6: CLASS-DEFAULTS.PHP HERO BADGES & LABELS
// -------------------------------------------------------------
echo "\nSUITE 6: Hero Badges & Defaults Audit\n";
echo "-------------------------------------------------------------------\n";

$hero_b2 = $homepage_defs['hero_badge_2'] ?? '';
M4AdversarialTester::assert(
	$hero_b2 === '50+ chương trình Liên thông Đại học: Từ xa & Vừa học vừa làm',
	"6.1: hero_badge_2 in class-defaults.php is '50+ chương trình Liên thông Đại học: Từ xa & Vừa học vừa làm'",
	[ 'hero_badge_2' => $hero_b2 ]
);

$first_badge = $homepage_defs['hero_badges'][0] ?? [];
M4AdversarialTester::assert(
	( $first_badge['subtext'] ?? '' ) === 'Liên thông Đại học: Từ xa & Vừa học vừa làm',
	"6.2: hero_badges[0].subtext is 'Liên thông Đại học: Từ xa & Vừa học vừa làm' (zero VB2)",
	[ 'badge_0' => $first_badge ]
);


// -------------------------------------------------------------
// SUITE 7: REDUNDANT FILTERS & TAXONOMY HARMONIZATION
// -------------------------------------------------------------
echo "\nSUITE 7: Redundant Filters & Taxonomy Harmonization\n";
echo "-------------------------------------------------------------------\n";

$templates_to_audit = [
	dirname( __DIR__ ) . '/front-page.php',
	dirname( __DIR__ ) . '/archive-program.php',
	dirname( __DIR__ ) . '/taxonomy-training_type.php',
	dirname( __DIR__ ) . '/single-school.php',
	dirname( __DIR__ ) . '/single-major.php',
	dirname( __DIR__ ) . '/inc/core/class-query-filters.php',
];

$redundant_tags_found = [];
foreach ( $templates_to_audit as $tmpl ) {
	$c = file_get_contents( $tmpl );
	if ( stripos( $c, 'loai_tuyen_sinh' ) !== false || stripos( $c, 'loại tuyển sinh' ) !== false ) {
		$redundant_tags_found[] = basename( $tmpl );
	}
}

M4AdversarialTester::assert(
	empty( $redundant_tags_found ),
	"7.1: Zero redundant 'loai_tuyen_sinh' or 'loại tuyển sinh' found across all theme templates",
	[ 'found_in' => $redundant_tags_found ]
);

// Check that no redundant "Liên thông" select option exists inside training_type filters
$redundant_option = false;
foreach ( [ dirname( __DIR__ ) . '/front-page.php', dirname( __DIR__ ) . '/archive-program.php' ] as $f ) {
	$c = file_get_contents( $f );
	// If there's an <option value="...">Liên thông</option> inside a filter
	if ( preg_match( '/<option[^>]*>\s*Liên thông\s*<\/option>/iu', $c ) ) {
		$redundant_option = true;
	}
}
M4AdversarialTester::assert(
	! $redundant_option,
	"7.2: Zero redundant 'Liên thông' filter options exist (since entire site is Liên thông)",
	[]
);

echo "\n===================================================================\n";
echo "TOTAL: " . M4AdversarialTester::$passed . " PASSED, " . M4AdversarialTester::$failed . " FAILED\n";
echo "===================================================================\n";

if ( M4AdversarialTester::$failed > 0 ) {
	exit( 1 );
}
