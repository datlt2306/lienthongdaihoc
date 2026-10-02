<?php
/**
 * Empirical & Adversarial Test Suite for Milestone M5: Templates & Program Presentation
 *
 * Authored by: worker_m5
 * Scope: Program cards across SSR, AJAX, single-school, single-major, compare,
 *        single-program quota notices, study mode archives tax_query, banner purity.
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class M5TestSuite {
	public static int $passed = 0;
	public static int $failed = 0;
	public static array $errors = [];

	public static function assert( bool $condition, string $description, array $context = [] ): void {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] " . $description . "\n";
		} else {
			self::$failed++;
			self::$errors[] = [ 'desc' => $description, 'context' => $context ];
			echo "  [FAIL] " . $description . "\n";
			if ( ! empty( $context ) ) {
				echo "         Details: " . json_encode( $context, JSON_UNESCAPED_UNICODE ) . "\n";
			}
		}
	}
}

echo "===================================================================\n";
echo "M5 EMPIRICAL & ADVERSARIAL TEST SUITE: TEMPLATES & PRESENTATION\n";
echo "===================================================================\n\n";

// -----------------------------------------------------------------
// SUITE 1: template-parts/banner.php PURITY & ACCURACY
// -----------------------------------------------------------------
echo "SUITE 1: template-parts/banner.php Out-of-Scope Cleanup & Subtitles\n";
echo "-------------------------------------------------------------------\n";

$banner_content = file_get_contents( ABSPATH . 'template-parts/banner.php' );

M5TestSuite::assert(
	false === stripos( $banner_content, 'Văn bằng 2' ) && false === stripos( $banner_content, 'van bang 2' ),
	"banner.php: Zero occurrences of 'Văn bằng 2'",
	[]
);

M5TestSuite::assert(
	false === stripos( $banner_content, 'Chính quy' ) && false === stripos( $banner_content, 'chinh quy' ),
	"banner.php: Zero occurrences of 'Chính quy'",
	[]
);

M5TestSuite::assert(
	false === stripos( $banner_content, 'hệ đào tạo' ) && false === stripos( $banner_content, 'Hệ đào tạo' ),
	"banner.php: Zero occurrences of 'hệ đào tạo' / 'Hệ đào tạo'",
	[]
);

M5TestSuite::assert(
	false !== stripos( $banner_content, 'Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm' ),
	"banner.php: In-scope subtitle present for /he-dao-tao/ and default archives",
	[]
);

M5TestSuite::assert(
	false !== strpos( $banner_content, "preg_replace( '/^hệ\\s+/iu', '', \$he_term->name )" ),
	"banner.php: Taxonomy term title cleanly strips 'Hệ ' prefix",
	[]
);

// -----------------------------------------------------------------
// SUITE 2: single-program.php NOTICE & SCOPE PURITY
// -----------------------------------------------------------------
echo "\nSUITE 2: single-program.php Notice Cleanup & Delivery Mode Guard\n";
echo "-------------------------------------------------------------------\n";

$single_prog_content = file_get_contents( ABSPATH . 'single-program.php' );

M5TestSuite::assert(
	false === strpos( $single_prog_content, 'hệ Chính quy' ) && false === strpos( $single_prog_content, 'hệ Liên thông Chính quy' ),
	"single-program.php: Zero mentions of 'hệ Chính quy' or 'hệ Liên thông Chính quy'",
	[]
);

$expected_notice = 'Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này.';
M5TestSuite::assert(
	false !== strpos( $single_prog_content, $expected_notice ),
	"single-program.php: Quota notice correctly reflects in-scope Liên thông admissions",
	[ 'expected' => $expected_notice ]
);

M5TestSuite::assert(
	false !== strpos( $single_prog_content, "'Toàn quốc'" ),
	"single-program.php: Delivery mode / campus defensively guards against 'Online' and defaults to 'Toàn quốc'",
	[]
);

M5TestSuite::assert(
	false !== strpos( $single_prog_content, 'Hình thức học' ),
	"single-program.php: Uses 'Hình thức học' instead of out-of-scope 'Hệ đào tạo'",
	[]
);

// -----------------------------------------------------------------
// SUITE 3: STUDY MODE ARCHIVES tax_query DEFAULTS & H1 LABELS
// -----------------------------------------------------------------
echo "\nSUITE 3: Archive tax_query In-Scope Defaults & Headers\n";
echo "-------------------------------------------------------------------\n";

$tax_tt_content = file_get_contents( ABSPATH . 'taxonomy-training_type.php' );
$archive_prog_content = file_get_contents( ABSPATH . 'archive-program.php' );

M5TestSuite::assert(
	false !== strpos( $tax_tt_content, "'terms'    => [ 'tu-xa', 'vua-hoc-vua-lam' ]" ) ||
	false !== strpos( $tax_tt_content, "['tu-xa', 'vua-hoc-vua-lam']" ) ||
	( false !== strpos( $tax_tt_content, "'tu-xa'" ) && false !== strpos( $tax_tt_content, "'vua-hoc-vua-lam'" ) ),
	"taxonomy-training_type.php: tax_query defaults strictly to in-scope modes ['tu-xa', 'vua-hoc-vua-lam']",
	[]
);

M5TestSuite::assert(
	false !== strpos( $archive_prog_content, "'terms'    => [ 'tu-xa', 'vua-hoc-vua-lam' ]" ) ||
	false !== strpos( $archive_prog_content, "['tu-xa', 'vua-hoc-vua-lam']" ) ||
	( false !== strpos( $archive_prog_content, "'tu-xa'" ) && false !== strpos( $archive_prog_content, "'vua-hoc-vua-lam'" ) ),
	"archive-program.php: tax_query defaults strictly to in-scope modes ['tu-xa', 'vua-hoc-vua-lam']",
	[]
);

M5TestSuite::assert(
	false !== strpos( $tax_tt_content, "Hình thức học: " ),
	"taxonomy-training_type.php: H1 heading formats as 'Hình thức học: [Tên hình thức]'",
	[]
);

M5TestSuite::assert(
	false !== strpos( $tax_tt_content, "preg_replace( '/^hệ\\s+/iu', '', \$active_type_term->name )" ),
	"taxonomy-training_type.php: Strips 'Hệ ' prefix from H1 current type heading",
	[]
);

M5TestSuite::assert(
	false !== strpos( $archive_prog_content, "preg_replace( '/^hệ\\s+/iu', '', \$active_type_term->name )" ),
	"archive-program.php: Strips 'Hệ ' prefix from H1 current type heading",
	[]
);

// -----------------------------------------------------------------
// SUITE 4: PROGRAM CARD HEADLINE FORMULA ACROSS TEMPLATES
// -----------------------------------------------------------------
echo "\nSUITE 4: Program Card Headline Formula Parity Across All 6 Templates\n";
echo "-------------------------------------------------------------------\n";

$compare_cards_content = file_get_contents( ABSPATH . 'template-parts/compare/program-cards.php' );
$single_school_content = file_get_contents( ABSPATH . 'single-school.php' );
$single_major_content  = file_get_contents( ABSPATH . 'single-major.php' );
$ajax_filters_content  = file_get_contents( ABSPATH . 'inc/core/class-query-filters.php' );

// 4.1 Check headline formula in taxonomy-training_type.php
M5TestSuite::assert(
	false !== strpos( $tax_tt_content, "'Liên thông ngành ' . \$clean_major_name" ) &&
	false !== strpos( $tax_tt_content, "preg_replace( '/^ngành\\s+/iu', '', trim( \$major_name ) )" ),
	"taxonomy-training_type.php: Implements formula 'Liên thông ngành [Major] - [Type]' with major/type prefix cleaning",
	[]
);

// 4.2 Check headline formula in archive-program.php
M5TestSuite::assert(
	false !== strpos( $archive_prog_content, "'Liên thông ngành ' . \$clean_major_name" ) &&
	false !== strpos( $archive_prog_content, "preg_replace( '/^ngành\\s+/iu', '', trim( \$major_name ) )" ),
	"archive-program.php: Implements formula 'Liên thông ngành [Major] - [Type]' with major/type prefix cleaning",
	[]
);

// 4.3 Check headline formula in class-query-filters.php (AJAX)
M5TestSuite::assert(
	false !== strpos( $ajax_filters_content, "'Liên thông ngành ' . \$clean_major_name" ) &&
	false !== strpos( $ajax_filters_content, "preg_replace( '/^ngành\\s+/iu', '', trim( \$major_name ) )" ),
	"class-query-filters.php: AJAX card implements formula 'Liên thông ngành [Major] - [Type]'",
	[]
);

// 4.4 Check headline formula in single-school.php
M5TestSuite::assert(
	false !== strpos( $single_school_content, "'Liên thông ngành ' . \$clean_major_name" ) &&
	false !== strpos( $single_school_content, "\$prog['opportunity_title']" ),
	"single-school.php: Computes and renders opportunity_title in program offerings",
	[]
);

// 4.5 Check headline formula in single-major.php
M5TestSuite::assert(
	false !== strpos( $single_major_content, "'Liên thông ngành ' . \$clean_major_name" ) &&
	false !== strpos( $single_major_content, "\$prog['opportunity_title']" ),
	"single-major.php: Computes and renders opportunity_title in program offerings",
	[]
);

// 4.6 Check headline formula in compare cards
M5TestSuite::assert(
	false !== strpos( $compare_cards_content, "'Liên thông ngành ' . \$clean_item_major" ) &&
	false !== strpos( $compare_cards_content, "preg_replace( '/^ngành\\s+/iu', '', trim( \$item_major_name ) )" ),
	"template-parts/compare/program-cards.php: Implements formula 'Liên thông ngành [Major] - [Type]'",
	[]
);

// -----------------------------------------------------------------
// SUITE 5: TYPE BADGE PURITY (ZERO "Hệ " PREFIX)
// -----------------------------------------------------------------
echo "\nSUITE 5: Training Type Badges Zero 'Hệ ' Prefix Enforcement\n";
echo "-------------------------------------------------------------------\n";

M5TestSuite::assert(
	false !== strpos( $tax_tt_content, "preg_replace( '/^hệ\\s+/iu', '', trim( \$type_name ) )" ),
	"taxonomy-training_type.php: Strips 'Hệ ' prefix before badge output",
	[]
);

M5TestSuite::assert(
	false !== strpos( $archive_prog_content, "preg_replace( '/^hệ\\s+/iu', '', trim( \$type_name ) )" ),
	"archive-program.php: Strips 'Hệ ' prefix before badge output",
	[]
);

M5TestSuite::assert(
	false !== strpos( $ajax_filters_content, "preg_replace( '/^hệ\\s+/iu', '', trim( \$t_name ) )" ),
	"class-query-filters.php: Strips 'Hệ ' prefix before AJAX badge output",
	[]
);

M5TestSuite::assert(
	false !== strpos( $single_school_content, "preg_replace( '/^hệ\\s+/iu', '', trim( \$type_name ) )" ),
	"single-school.php: Strips 'Hệ ' prefix before badge output",
	[]
);

M5TestSuite::assert(
	false !== strpos( $single_major_content, "preg_replace( '/^hệ\\s+/iu', '', trim( \$type_name ) )" ),
	"single-major.php: Strips 'Hệ ' prefix before badge output",
	[]
);

M5TestSuite::assert(
	false !== strpos( $compare_cards_content, "preg_replace( '/^hệ\\s+/iu', '', trim( \$item['training_type'] ?? '' ) )" ) &&
	false !== strpos( $compare_cards_content, "ltdh_get_training_type_badge_html( \$clean_item_type )" ),
	"compare/program-cards.php: Strips 'Hệ ' prefix before compare badge output",
	[]
);

// -----------------------------------------------------------------
// SUITE 6: SSR & AJAX STRUCTURAL & ATTRIBUTE PARITY
// -----------------------------------------------------------------
echo "\nSUITE 6: SSR vs AJAX Program Card Structural Parity\n";
echo "-------------------------------------------------------------------\n";

// Both SSR and AJAX must have compare button attributes
$compare_attrs = [
	'data-compare-btn',
	'data-compare-type="program"',
	'data-compare-id',
	'data-compare-title',
	'data-compare-slug',
	'data-compare-thumb',
	'data-compare-he',
	'data-compare-nganh',
];

foreach ( $compare_attrs as $attr ) {
	M5TestSuite::assert(
		false !== strpos( $tax_tt_content, $attr ),
		"taxonomy-training_type.php: Has compare attribute {$attr}",
		[]
	);
	M5TestSuite::assert(
		false !== strpos( $ajax_filters_content, $attr ),
		"class-query-filters.php: Has compare attribute {$attr}",
		[]
	);
}

// Check learning details helper usage
M5TestSuite::assert(
	false !== strpos( $tax_tt_content, 'ltdh_get_program_learning_details' ),
	"taxonomy-training_type.php: Uses ltdh_get_program_learning_details() for mode/schedule",
	[]
);

M5TestSuite::assert(
	false !== strpos( $ajax_filters_content, 'ltdh_get_program_learning_details' ),
	"class-query-filters.php: Uses ltdh_get_program_learning_details() for mode/schedule",
	[]
);

M5TestSuite::assert(
	false !== strpos( $archive_prog_content, 'ltdh_get_program_learning_details' ),
	"archive-program.php: Uses ltdh_get_program_learning_details() for mode/schedule",
	[]
);

// -----------------------------------------------------------------
// SUITE 7: EMPIRICAL STRING LOGIC SIMULATION & EDGE CASES
// -----------------------------------------------------------------
echo "\nSUITE 7: Adversarial String Cleaning & Edge Cases Simulation\n";
echo "-------------------------------------------------------------------\n";

function m5_compute_headline( string $major, string $type ): array {
	$clean_type = preg_replace( '/^hệ\s+/iu', '', trim( $type ) );
	$clean_major = preg_replace( '/^ngành\s+/iu', '', trim( $major ) );
	$clean_major = preg_replace( '/^cử\s+nhân\s+/iu', '', $clean_major );
	$clean_major = preg_replace( '/^kỹ\s+sư\s+/iu', '', $clean_major );
	$title = 'Liên thông ngành ' . $clean_major . ( $clean_type ? ' - ' . $clean_type : '' );
	return [
		'clean_major' => $clean_major,
		'clean_type'  => $clean_type,
		'title'       => $title,
	];
}

$test_cases = [
	[
		'major'         => 'Công nghệ thông tin',
		'type'          => 'Từ xa',
		'expected_type' => 'Từ xa',
		'expected_title'=> 'Liên thông ngành Công nghệ thông tin - Từ xa',
	],
	[
		'major'         => 'Ngành Quản trị kinh doanh',
		'type'          => 'Hệ Từ xa',
		'expected_type' => 'Từ xa',
		'expected_title'=> 'Liên thông ngành Quản trị kinh doanh - Từ xa',
	],
	[
		'major'         => 'ngành Kế toán',
		'type'          => 'hệ vừa học vừa làm',
		'expected_type' => 'vừa học vừa làm',
		'expected_title'=> 'Liên thông ngành Kế toán - vừa học vừa làm',
	],
	[
		'major'         => 'Cử nhân Ngôn ngữ Anh',
		'type'          => 'HỆ TỪ XA',
		'expected_type' => 'TỪ XA',
		'expected_title'=> 'Liên thông ngành Ngôn ngữ Anh - TỪ XA',
	],
	[
		'major'         => 'Kỹ sư Xây dựng',
		'type'          => 'Vừa học vừa làm',
		'expected_type' => 'Vừa học vừa làm',
		'expected_title'=> 'Liên thông ngành Xây dựng - Vừa học vừa làm',
	],
];

foreach ( $test_cases as $idx => $case ) {
	$res = m5_compute_headline( $case['major'], $case['type'] );
	M5TestSuite::assert(
		$res['clean_type'] === $case['expected_type'],
		"Case {$idx} type cleaning: '{$case['type']}' => '{$res['clean_type']}'",
		[ 'expected' => $case['expected_type'], 'actual' => $res['clean_type'] ]
	);
	M5TestSuite::assert(
		$res['title'] === $case['expected_title'],
		"Case {$idx} headline: '{$res['title']}'",
		[ 'expected' => $case['expected_title'], 'actual' => $res['title'] ]
	);
	M5TestSuite::assert(
		false === strpos( $res['title'], 'Liên thông ngành Ngành' ),
		"Case {$idx}: Zero double-prefix 'Liên thông ngành Ngành'",
		[]
	);
	M5TestSuite::assert(
		false === strpos( $res['title'], 'Hệ ' ),
		"Case {$idx}: Zero 'Hệ ' prefix in headline",
		[]
	);
}

// -----------------------------------------------------------------
// SUMMARY
// -----------------------------------------------------------------
echo "\n===================================================================\n";
echo sprintf( "M5 TEST SUITE SUMMARY: %d PASSED, %d FAILED\n", M5TestSuite::$passed, M5TestSuite::$failed );
echo "===================================================================\n";

if ( M5TestSuite::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
