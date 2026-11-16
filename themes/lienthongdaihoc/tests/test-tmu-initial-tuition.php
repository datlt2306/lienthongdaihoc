<?php
/**
 * Test Suite: TMU Initial Tuition (Tạm thu học phí kỳ 1)
 *
 * Verifies that:
 * 1. ACF fields for initial_tuition and default_initial_tuition exist.
 * 2. Helper methods format_initial_tuition and get_initial_tuition work accurately.
 * 3. TMU programs properly resolve "9.000.000 đ" for first semester provisional tuition.
 * 4. DRY inheritance cascades from School to Program.
 * 5. Program detail templates (tuition.php, overview.php) render the section cleanly.
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

$theme_dir = dirname( __DIR__ ) . '/';

require_once $theme_dir . 'inc/helpers/helper-pricing.php';
require_once $theme_dir . 'inc/core/class-helpers.php';

class TMUInitialTuitionTestSuite {
	public static int $passed = 0;
	public static int $failed = 0;

	public static function assert( bool $condition, string $description ): void {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] " . $description . "\n";
		} else {
			self::$failed++;
			echo "  [FAIL] " . $description . "\n";
		}
	}
}

echo "===================================================================\n";
echo "TEST SUITE: TMU INITIAL TUITION (TẠM THU HỌC PHÍ KỲ 1 - 9TR)\n";
echo "===================================================================\n\n";

// -------------------------------------------------------------
// 1. JSON DEFINITIONS TEST
// -------------------------------------------------------------
echo "--- 1. ACF JSON SCHEMA DEFINITIONS ---\n";

$json_raw = file_get_contents( $theme_dir . 'inc/acf-import-fields.json' );
$json_data = json_decode( $json_raw, true );

TMUInitialTuitionTestSuite::assert(
	is_array( $json_data ),
	"acf-import-fields.json is valid JSON"
);

$school_group = null;
$program_group = null;
foreach ( $json_data as $group ) {
	if ( ( $group['key'] ?? '' ) === 'group_school_details' ) {
		$school_group = $group;
	} elseif ( ( $group['key'] ?? '' ) === 'group_program_details' ) {
		$program_group = $group;
	}
}

$school_fields = array_column( $school_group['fields'] ?? [], null, 'name' );
$program_fields = array_column( $program_group['fields'] ?? [], null, 'name' );

TMUInitialTuitionTestSuite::assert(
	isset( $school_fields['default_initial_tuition'] ),
	"School group has 'default_initial_tuition' field"
);
TMUInitialTuitionTestSuite::assert(
	( $school_fields['default_initial_tuition']['key'] ?? '' ) === 'field_school_default_initial_tuition',
	"School 'default_initial_tuition' key matches expected"
);
TMUInitialTuitionTestSuite::assert(
	isset( $program_fields['initial_tuition'] ),
	"Program group has 'initial_tuition' field"
);
TMUInitialTuitionTestSuite::assert(
	( $program_fields['initial_tuition']['key'] ?? '' ) === 'field_program_initial_tuition',
	"Program 'initial_tuition' key matches expected"
);

// -------------------------------------------------------------
// 2. HELPER FORMATTING TESTS
// -------------------------------------------------------------
echo "\n--- 2. FORMATTING HELPER TESTS ---\n";

$f1 = LTDH_Pricing_Helpers::format_initial_tuition( '9tr' );
TMUInitialTuitionTestSuite::assert(
	$f1['display'] === '9.000.000 đ' && (float) $f1['raw'] === 9000000.0,
	"format_initial_tuition('9tr') converts to '9.000.000 đ'"
);

$f2 = LTDH_Pricing_Helpers::format_initial_tuition( '9 triệu' );
TMUInitialTuitionTestSuite::assert(
	$f2['display'] === '9.000.000 đ',
	"format_initial_tuition('9 triệu') converts to '9.000.000 đ'"
);

$f3 = LTDH_Pricing_Helpers::format_initial_tuition( 9000000 );
TMUInitialTuitionTestSuite::assert(
	$f3['display'] === '9.000.000 đ',
	"format_initial_tuition(9000000) converts to '9.000.000 đ'"
);

$f4 = LTDH_Pricing_Helpers::format_initial_tuition( '9.000.000 đ' );
TMUInitialTuitionTestSuite::assert(
	$f4['display'] === '9.000.000 đ',
	"format_initial_tuition('9.000.000 đ') preserves format"
);

$f5 = LTDH_Pricing_Helpers::format_initial_tuition( '9.000.000 đ (15 tín chỉ)' );
TMUInitialTuitionTestSuite::assert(
	strpos( $f5['display'], '9.000.000 đ (15 tín chỉ)' ) !== false,
	"format_initial_tuition handles descriptive parenthesis note"
);

// -------------------------------------------------------------
// 3. DATABASE & RESOLUTION TESTS (IF WP LOADED)
// -------------------------------------------------------------
echo "\n--- 3. DATABASE ENTITIES & RESOLUTION TESTS ---\n";

if ( function_exists( 'get_posts' ) ) {
	$tmu_school_id = 2364;
	$tmu_default = get_post_meta( $tmu_school_id, 'default_initial_tuition', true );
	TMUInitialTuitionTestSuite::assert(
		! empty( $tmu_default ) && strpos( $tmu_default, '9.000.000' ) !== false,
		"TMU School (ID 2364) has default_initial_tuition = '9.000.000 đ'"
	);

	$tmu_programs = [ 3453, 3296, 3293, 3276, 3258 ];
	foreach ( $tmu_programs as $pid ) {
		$res = ltdh_get_program_initial_tuition( $pid );
		$title = get_the_title( $pid );
		TMUInitialTuitionTestSuite::assert(
			! empty( $res ) && strpos( $res, '9.000.000' ) !== false,
			"TMU Program {$pid} ({$title}) resolves initial_tuition: '{$res}'"
		);
	}

	// -------------------------------------------------------------
	// 4. TEMPLATE RENDERING TESTS
	// -------------------------------------------------------------
	echo "\n--- 4. TEMPLATE RENDERING VERIFICATION ---\n";

	// Test tuition.php rendering
	ob_start();
	$test_pid = 3258; // TMU E-Commerce
	$args = [
		'program_id'      => $test_pid,
		'school_id'       => $tmu_school_id,
		'tuition'         => ltdh_get_program_tuition_display( $test_pid ),
		'initial_tuition' => ltdh_get_program_initial_tuition( $test_pid ),
		'duration'        => '2.5 - 3 năm',
	];
	include $theme_dir . 'template-parts/program/tuition.php';
	$tuition_html = ob_get_clean();

	TMUInitialTuitionTestSuite::assert(
		strpos( $tuition_html, 'Tạm thu kỳ 1:' ) !== false,
		"template-parts/program/tuition.php renders 'Tạm thu kỳ 1:'"
	);
	TMUInitialTuitionTestSuite::assert(
		strpos( $tuition_html, '9.000.000 đ' ) !== false,
		"template-parts/program/tuition.php displays '9.000.000 đ'"
	);
	TMUInitialTuitionTestSuite::assert(
		strpos( $tuition_html, 'Tạm thu học phí kỳ 1:' ) !== false,
		"template-parts/program/tuition.php includes highlight in tuition note box"
	);

	// Test overview.php rendering
	ob_start();
	$args = [
		'program_id'      => $test_pid,
		'school_id'       => $tmu_school_id,
		'tuition'         => ltdh_get_program_tuition_display( $test_pid ),
		'initial_tuition' => ltdh_get_program_initial_tuition( $test_pid ),
		'duration'        => '2.5 - 3 năm',
	];
	include $theme_dir . 'template-parts/program/overview.php';
	$overview_html = ob_get_clean();

	TMUInitialTuitionTestSuite::assert(
		strpos( $overview_html, 'Tạm thu kỳ 1:' ) !== false,
		"template-parts/program/overview.php renders 'Tạm thu kỳ 1:'"
	);
	TMUInitialTuitionTestSuite::assert(
		strpos( $overview_html, '9.000.000 đ' ) !== false,
		"template-parts/program/overview.php displays '9.000.000 đ'"
	);
	TMUInitialTuitionTestSuite::assert(
		strpos( $overview_html, 'Tạm thu học phí kỳ 1' ) !== false,
		"template-parts/program/overview.php includes semantic table row for search engines"
	);
} else {
	echo "  [SKIP] WordPress environment not loaded directly; run via wp eval-file for full DB tests.\n";
}

echo "\n===================================================================\n";
echo "SUMMARY: " . TMUInitialTuitionTestSuite::$passed . " PASSED, " . TMUInitialTuitionTestSuite::$failed . " FAILED\n";
echo "===================================================================\n";

if ( TMUInitialTuitionTestSuite::$failed > 0 ) {
	exit( 1 );
}
