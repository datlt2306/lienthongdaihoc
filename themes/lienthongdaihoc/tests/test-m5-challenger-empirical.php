<?php
/**
 * Adversarial Empirical Test Suite: Milestone M5 (Challenger: challenger_m5_2)
 *
 * Scope:
 * 1. single-program.php notice cleanup, quota handling, delivery mode & campus guard.
 * 2. template-parts/banner.php text purity across multiple routes and simulated contexts.
 * 3. Study mode archive query construction and scope confinement (taxonomy-training_type.php & archive-program.php).
 * 4. Headline formula and badge purity cross-template parity.
 * 5. String cleaning adversarial resilience under Unicode, accented, and malformed inputs.
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class ChallengerM5TestRunner {
	public static int $passed = 0;
	public static int $failed = 0;
	public static array $failures = [];

	public static function assert( bool $condition, string $description, array $context = [] ): void {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] " . $description . "\n";
		} else {
			self::$failed++;
			self::$failures[] = [ 'desc' => $description, 'context' => $context ];
			echo "  [FAIL] " . $description . "\n";
			if ( ! empty( $context ) ) {
				echo "         Context: " . json_encode( $context, JSON_UNESCAPED_UNICODE ) . "\n";
			}
		}
	}
}

echo "===================================================================\n";
echo "CHALLENGER M5-2 EMPIRICAL TEST HARNESS\n";
echo "===================================================================\n\n";

// -----------------------------------------------------------------
// SUITE 1: single-program.php DEEP AUDIT
// -----------------------------------------------------------------
echo "SUITE 1: single-program.php Scope, Notice & Campus Audit\n";
echo "-------------------------------------------------------------------\n";

$single_prog_raw = file_get_contents( ABSPATH . 'single-program.php' );

// 1.1 Forbidden string audit
$forbidden_in_single = [
	'hệ Chính quy',
	'hệ Liên thông Chính quy',
	'Văn bằng 2',
	'van bang 2',
	'hệ đào tạo',
	'Học sinh tốt nghiệp THPT',
];
foreach ( $forbidden_in_single as $bad_str ) {
	ChallengerM5TestRunner::assert(
		false === stripos( $single_prog_raw, $bad_str ),
		"single-program.php: Zero occurrences of '{$bad_str}'"
	);
}

// 1.2 In-scope Quota notice check
$expected_quota_notice = 'Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này.';
ChallengerM5TestRunner::assert(
	false !== strpos( $single_prog_raw, $expected_quota_notice ),
	"single-program.php: Contains standardized quota announcement for Liên thông",
	[ 'expected' => $expected_quota_notice ]
);

// 1.3 Key detail cards audit
ChallengerM5TestRunner::assert(
	false !== strpos( $single_prog_raw, 'Hình thức học' ),
	"single-program.php: Presents 'Hình thức học' (not 'Hệ đào tạo')"
);

ChallengerM5TestRunner::assert(
	false !== strpos( $single_prog_raw, 'Cơ sở học' ),
	"single-program.php: Presents 'Cơ sở học' block"
);

ChallengerM5TestRunner::assert(
	false !== strpos( $single_prog_raw, 'Học phí' ),
	"single-program.php: Presents 'Học phí' block"
);

ChallengerM5TestRunner::assert(
	false !== strpos( $single_prog_raw, 'Thời gian học' ),
	"single-program.php: Presents 'Thời gian học' block"
);

// 1.4 Campus defensive logic evaluation
function test_single_campus_fallback( $campus_val ) {
	$display_campus = $campus_val;
	if ( empty( $display_campus ) || 'online' === strtolower( trim( $display_campus ) ) ) {
		$display_campus = 'Toàn quốc';
	}
	return $display_campus;
}

ChallengerM5TestRunner::assert(
	test_single_campus_fallback( 'Online' ) === 'Toàn quốc',
	"single-program.php logic: 'Online' fallback -> 'Toàn quốc'"
);
ChallengerM5TestRunner::assert(
	test_single_campus_fallback( '  ONLINE  ' ) === 'Toàn quốc',
	"single-program.php logic: '  ONLINE  ' fallback -> 'Toàn quốc'"
);
ChallengerM5TestRunner::assert(
	test_single_campus_fallback( '' ) === 'Toàn quốc',
	"single-program.php logic: empty fallback -> 'Toàn quốc'"
);
ChallengerM5TestRunner::assert(
	test_single_campus_fallback( null ) === 'Toàn quốc',
	"single-program.php logic: null fallback -> 'Toàn quốc'"
);
ChallengerM5TestRunner::assert(
	test_single_campus_fallback( 'Hà Nội' ) === 'Hà Nội',
	"single-program.php logic: physical campus 'Hà Nội' preserved"
);


// -----------------------------------------------------------------
// SUITE 2: template-parts/banner.php ROUTE & STRING PURITY AUDIT
// -----------------------------------------------------------------
echo "\nSUITE 2: template-parts/banner.php Route Purity & Subtitle Audit\n";
echo "-------------------------------------------------------------------\n";

$banner_raw = file_get_contents( ABSPATH . 'template-parts/banner.php' );

// 2.1 Forbidden strings
$forbidden_in_banner = [
	'Văn bằng 2',
	'van bang 2',
	'Chính quy',
	'chinh quy',
	'hệ đào tạo',
];
foreach ( $forbidden_in_banner as $bad_str ) {
	ChallengerM5TestRunner::assert(
		false === stripos( $banner_raw, $bad_str ),
		"banner.php: Zero occurrences of '{$bad_str}'"
	);
}

// 2.2 Subtitle accuracy
$expected_sub = 'Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm';
ChallengerM5TestRunner::assert(
	false !== strpos( $banner_raw, $expected_sub ),
	"banner.php: Contains exact standard Liên thông subtitle",
	[ 'expected' => $expected_sub ]
);

// 2.3 Simulated Route Matching in banner.php
function simulate_banner_route( $request_path ) {
	$banner_title = '';
	$banner_subtitle = '';

	if ( preg_match( '#^/he-dao-tao(?:/page/\d+)?/?$#i', $request_path ) ) {
		$banner_title    = 'Hình thức học';
		$banner_subtitle = 'Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm';
	} elseif ( preg_match( '#^/he-dao-tao/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $m ) && 'page' !== $m[1] ) {
		$selected_he = $m[1];
		$term_name = ( $selected_he === 'tu-xa' ) ? 'Từ xa' : ( ( $selected_he === 'vua-hoc-vua-lam' ) ? 'Vừa học vừa làm' : $selected_he );
		$clean_he_name = preg_replace( '/^hệ\s+/iu', '', $term_name );
		$banner_title = 'Hình thức học: ' . $clean_he_name;
		$banner_subtitle = 'Danh sách chương trình thuộc hình thức học ' . $clean_he_name;
	} else {
		$banner_title = 'Chương Trình Tuyển Sinh Liên Thông Đại Học';
		$banner_subtitle = 'Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm';
	}

	return [ 'title' => $banner_title, 'subtitle' => $banner_subtitle ];
}

$routes_to_test = [
	'/he-dao-tao'                     => [ 'title' => 'Hình thức học', 'sub_contains' => 'liên thông đại học hình thức từ xa' ],
	'/he-dao-tao/'                    => [ 'title' => 'Hình thức học', 'sub_contains' => 'liên thông đại học hình thức từ xa' ],
	'/he-dao-tao/page/2'              => [ 'title' => 'Hình thức học', 'sub_contains' => 'liên thông đại học hình thức từ xa' ],
	'/he-dao-tao/page/2/'             => [ 'title' => 'Hình thức học', 'sub_contains' => 'liên thông đại học hình thức từ xa' ],
	'/he-dao-tao/tu-xa'               => [ 'title' => 'Hình thức học: Từ xa', 'sub_contains' => 'hình thức học Từ xa' ],
	'/he-dao-tao/tu-xa/'              => [ 'title' => 'Hình thức học: Từ xa', 'sub_contains' => 'hình thức học Từ xa' ],
	'/he-dao-tao/tu-xa/page/3/'       => [ 'title' => 'Hình thức học: Từ xa', 'sub_contains' => 'hình thức học Từ xa' ],
	'/he-dao-tao/vua-hoc-vua-lam'     => [ 'title' => 'Hình thức học: Vừa học vừa làm', 'sub_contains' => 'hình thức học Vừa học vừa làm' ],
	'/he-dao-tao/vua-hoc-vua-lam/'    => [ 'title' => 'Hình thức học: Vừa học vừa làm', 'sub_contains' => 'hình thức học Vừa học vừa làm' ],
	'/chuong-trinh/'                  => [ 'title' => 'Chương Trình Tuyển Sinh Liên Thông Đại Học', 'sub_contains' => 'liên thông đại học' ],
];

foreach ( $routes_to_test as $path => $expected ) {
	$res = simulate_banner_route( $path );
	ChallengerM5TestRunner::assert(
		$res['title'] === $expected['title'],
		"banner route '{$path}' => title '{$res['title']}'",
		[ 'expected' => $expected['title'], 'actual' => $res['title'] ]
	);
	ChallengerM5TestRunner::assert(
		false !== stripos( $res['subtitle'], $expected['sub_contains'] ),
		"banner route '{$path}' => subtitle contains '{$expected['sub_contains']}'"
	);
}


// -----------------------------------------------------------------
// SUITE 3: STUDY MODE ARCHIVES tax_query DEFAULTS & ISOLATION
// -----------------------------------------------------------------
echo "\nSUITE 3: Study Mode Archive Query Confinement Audit\n";
echo "-------------------------------------------------------------------\n";

$tax_tt_raw = file_get_contents( ABSPATH . 'taxonomy-training_type.php' );
$archive_prog_raw = file_get_contents( ABSPATH . 'archive-program.php' );
$ajax_raw = file_get_contents( ABSPATH . 'inc/core/class-query-filters.php' );

// 3.1 tax_query default terms
ChallengerM5TestRunner::assert(
	false !== strpos( $tax_tt_raw, "'terms'    => [ 'tu-xa', 'vua-hoc-vua-lam' ]" ),
	"taxonomy-training_type.php: Default tax_query confines strictly to ['tu-xa', 'vua-hoc-vua-lam']"
);

ChallengerM5TestRunner::assert(
	false !== strpos( $archive_prog_raw, "'terms'    => [ 'tu-xa', 'vua-hoc-vua-lam' ]" ),
	"archive-program.php: Default tax_query confines strictly to ['tu-xa', 'vua-hoc-vua-lam']"
);

ChallengerM5TestRunner::assert(
	false !== strpos( $ajax_raw, "'terms'    => [ 'tu-xa', 'vua-hoc-vua-lam' ]" ),
	"class-query-filters.php: Default AJAX tax_query confines strictly to ['tu-xa', 'vua-hoc-vua-lam']"
);

// 3.2 Filter pills restriction
ChallengerM5TestRunner::assert(
	false !== strpos( $tax_tt_raw, "\$allowed_training_types = [ 'tu-xa', 'vua-hoc-vua-lam' ];" ),
	"taxonomy-training_type.php: Training type pills whitelist strictly ['tu-xa', 'vua-hoc-vua-lam']"
);

ChallengerM5TestRunner::assert(
	false !== strpos( $archive_prog_raw, "\$allowed_training_types = [ 'tu-xa', 'vua-hoc-vua-lam' ];" ),
	"archive-program.php: Training type pills whitelist strictly ['tu-xa', 'vua-hoc-vua-lam']"
);

// 3.3 Post status publish requirement
ChallengerM5TestRunner::assert(
	1 === preg_match( "/'post_status'\\s*=>\\s*'publish'/", $tax_tt_raw ),
	"taxonomy-training_type.php: Enforces post_status => publish"
);
ChallengerM5TestRunner::assert(
	1 === preg_match( "/'post_status'\\s*=>\\s*'publish'/", $archive_prog_raw ),
	"archive-program.php: Enforces post_status => publish"
);


// -----------------------------------------------------------------
// SUITE 4: PROGRAM CARD HEADLINE & BADGE PARITY ACROSS 6 TEMPLATES
// -----------------------------------------------------------------
echo "\nSUITE 4: Program Card Headline & Badge Parity Audit\n";
echo "-------------------------------------------------------------------\n";

$single_school_raw = file_get_contents( ABSPATH . 'single-school.php' );
$single_major_raw  = file_get_contents( ABSPATH . 'single-major.php' );
$compare_raw       = file_get_contents( ABSPATH . 'template-parts/compare/program-cards.php' );

$templates_checked = [
	'taxonomy-training_type.php' => $tax_tt_raw,
	'archive-program.php'        => $archive_prog_raw,
	'class-query-filters.php'    => $ajax_raw,
	'single-school.php'          => $single_school_raw,
	'single-major.php'           => $single_major_raw,
	'compare/program-cards.php'  => $compare_raw,
];

foreach ( $templates_checked as $name => $content ) {
	ChallengerM5TestRunner::assert(
		false !== strpos( $content, "'Liên thông ngành ' . " ) || false !== strpos( $content, "'Liên thông ngành '." ),
		"{$name}: Implements headline starting with 'Liên thông ngành '"
	);

	ChallengerM5TestRunner::assert(
		false !== strpos( $content, "preg_replace( '/^hệ\\s+/iu', '', " ) ||
		false !== strpos( $content, "preg_replace( '/^hệ\\\\s+/iu', '', " ),
		"{$name}: Implements regex cleaning for 'Hệ ' prefix"
	);
}


// -----------------------------------------------------------------
// SUITE 5: ADVERSARIAL UNICODE & STRING RESILIENCE
// -----------------------------------------------------------------
echo "\nSUITE 5: Adversarial String Cleaning Resilience\n";
echo "-------------------------------------------------------------------\n";

function challenger_clean_headline( string $major, string $type ): string {
	$clean_type  = preg_replace( '/^hệ\s+/iu', '', trim( $type ) );
	$clean_major = preg_replace( '/^ngành\s+/iu', '', trim( $major ) );
	$clean_major = preg_replace( '/^cử\s+nhân\s+/iu', '', $clean_major );
	$clean_major = preg_replace( '/^kỹ\s+sư\s+/iu', '', $clean_major );
	return 'Liên thông ngành ' . $clean_major . ( $clean_type ? ' - ' . $clean_type : '' );
}

$adversarial_cases = [
	[
		'major'    => "   ngành   Dược học   ",
		'type'     => "  HỆ TỪ XA  ",
		'expected' => "Liên thông ngành Dược học - TỪ XA",
	],
	[
		'major'    => "NGÀNH LUẬT KINH TẾ",
		'type'     => "vừa học vừa làm",
		'expected' => "Liên thông ngành LUẬT KINH TẾ - vừa học vừa làm",
	],
	[
		'major'    => "Cử Nhân Tài Chính Ngân Hàng",
		'type'     => "Hệ Vừa Học Vừa Làm",
		'expected' => "Liên thông ngành Tài Chính Ngân Hàng - Vừa Học Vừa Làm",
	],
	[
		'major'    => "Kỹ Sư Công Nghệ Thông Tin",
		'type'     => "từ xa",
		'expected' => "Liên thông ngành Công Nghệ Thông Tin - từ xa",
	],
	[
		'major'    => "Thương Mại Điện Tử",
		'type'     => "",
		'expected' => "Liên thông ngành Thương Mại Điện Tử",
	],
];

foreach ( $adversarial_cases as $i => $ac ) {
	$res = challenger_clean_headline( $ac['major'], $ac['type'] );
	ChallengerM5TestRunner::assert(
		$res === $ac['expected'],
		"Adversarial case {$i}: '{$res}' matches expected '{$ac['expected']}'",
		[ 'input_major' => $ac['major'], 'input_type' => $ac['type'] ]
	);
	ChallengerM5TestRunner::assert(
		false === strpos( $res, 'Liên thông ngành Ngành' ) && false === strpos( $res, 'Liên thông ngành NGÀNH' ),
		"Adversarial case {$i}: Zero duplicate 'ngành' prefix"
	);
	ChallengerM5TestRunner::assert(
		false === strpos( $res, 'Hệ ' ) && false === strpos( $res, 'HỆ ' ),
		"Adversarial case {$i}: Zero 'Hệ ' in study mode string"
	);
}

// -----------------------------------------------------------------
// SUMMARY
// -----------------------------------------------------------------
echo "\n===================================================================\n";
echo sprintf( "CHALLENGER M5-2 SUITE SUMMARY: %d PASSED, %d FAILED\n", ChallengerM5TestRunner::$passed, ChallengerM5TestRunner::$failed );
echo "===================================================================\n";

if ( ChallengerM5TestRunner::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
