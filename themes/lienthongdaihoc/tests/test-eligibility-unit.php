<?php
if ( php_sapi_name() !== 'cli' ) {
	http_response_code( 403 );
	die( 'Forbidden: CLI access only.' );
}

/**
 * Unit Test Suite for Eligibility Engine & Distance Learning Matching
 * lienthongdaihoc Theme
 *
 * Verifies:
 * 1. Taxonomy Dual-Slug Normalization ('tu-xa' vs 'dao-tao-tu-xa')
 * 2. UTC Program Matching & #1 Ranking (ID 2014, CNTT, Đào tạo từ xa)
 * 3. Campus Normalization & Online Fallback
 * 4. Ghost University / Orphan Program Exclusion
 * 5. Education Hierarchy & Postmeta Data Integrity (Zero 'thap-phan' corruption)
 * 6. Ministry Circular 28/2023 Distance Learning Prohibitions
 * 7. Frontend Data Contract & Desired Major Title Serialization
 * 8. Database Logging & Check-Lead Linkage
 *
 * Run via:
 *   php tests/test-eligibility-unit.php
 *   or: wp eval-file tests/test-eligibility-unit.php --path="/Users/ken/Local Sites/lienthongdaihoc/app/public"
 */

// If executed via direct php CLI without WordPress loaded, delegate to wp eval-file
if ( ! defined( 'ABSPATH' ) ) {
	$wp_path = dirname( __DIR__, 4 );
	$cmd = 'wp eval-file ' . escapeshellarg( __FILE__ ) . ' --path=' . escapeshellarg( $wp_path );
	passthru( $cmd, $return_code );
	exit( $return_code );
}

// Ensure theme eligibility files are loaded
if ( ! function_exists( 'ltdh_elig_run_check' ) ) {
	require_once get_stylesheet_directory() . '/inc/eligibility.php';
}
if ( ! function_exists( 'ltdh_elig_get_scoring_weights' ) ) {
	require_once get_stylesheet_directory() . '/inc/eligibility-rules.php';
}

class EligibilityUnitTestRunner {
	public static $passed = 0;
	public static $failed = 0;
	public static $errors = [];
	public static $current_suite = '';

	public static function suite( $name ) {
		self::$current_suite = $name;
		echo "\n===================================================================\n";
		echo $name . "\n";
		echo "===================================================================\n";
	}

	public static function assert( $condition, $message, $details = '' ) {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] $message\n";
		} else {
			self::$failed++;
			$fail_msg = "  [FAIL] $message";
			if ( $details ) {
				$fail_msg .= " (Details: $details)";
			}
			echo "$fail_msg\n";
			self::$errors[] = [
				'suite'   => self::$current_suite,
				'message' => $message,
				'details' => $details,
			];
		}
	}

	public static function report() {
		echo "\n===================================================================\n";
		echo sprintf( "ELIGIBILITY UNIT TEST RESULTS: %d PASSED, %d FAILED\n", self::$passed, self::$failed );
		echo "===================================================================\n";

		if ( self::$failed > 0 ) {
			echo "\nFAILURES DETECTED:\n";
			foreach ( self::$errors as $idx => $err ) {
				echo sprintf( "%d. [%s] %s\n", $idx + 1, $err['suite'], $err['message'] );
				if ( ! empty( $err['details'] ) ) {
					echo "   Details: " . $err['details'] . "\n";
				}
			}
			exit( 1 );
		}

		echo "\nALL ELIGIBILITY UNIT CHECKS PASSED WITH 100% INTEGRITY.\n\n";
		exit( 0 );
	}
}

echo "==================================================\n";
echo "STARTING ELIGIBILITY UNIT TEST SUITE\n";
echo "==================================================\n";

// -----------------------------------------------------------------
// SUITE 1: Taxonomy Dual-Slug Normalization ('tu-xa' vs 'dao-tao-tu-xa')
// -----------------------------------------------------------------
EligibilityUnitTestRunner::suite( 'SUITE 1: TAXONOMY DUAL-SLUG NORMALIZATION' );

$input_tu_xa = [
	'education'     => 'cao-dang',
	'desired_major' => 1677, // CNTT
	'training_type' => 'tu-xa',
	'campus'        => 'ha-noi',
];

$input_dao_tao_tu_xa = [
	'education'     => 'cao-dang',
	'desired_major' => 1677, // CNTT
	'training_type' => 'dao-tao-tu-xa',
	'campus'        => 'ha-noi',
];

$res_tu_xa = ltdh_elig_run_check( $input_tu_xa );
$res_dao_tao = ltdh_elig_run_check( $input_dao_tao_tu_xa );

EligibilityUnitTestRunner::assert(
	! empty( $res_tu_xa['programs'] ),
	"Query with training_type 'tu-xa' returns non-empty eligible programs",
	'Count: ' . count( $res_tu_xa['programs'] ?? [] )
);

EligibilityUnitTestRunner::assert(
	! empty( $res_dao_tao['programs'] ),
	"Query with training_type 'dao-tao-tu-xa' returns non-empty eligible programs",
	'Count: ' . count( $res_dao_tao['programs'] ?? [] )
);

$ids_tu_xa = array_column( $res_tu_xa['programs'], 'program_id' );
$ids_dao_tao = array_column( $res_dao_tao['programs'], 'program_id' );

EligibilityUnitTestRunner::assert(
	$ids_tu_xa === $ids_dao_tao,
	"Both 'tu-xa' and 'dao-tao-tu-xa' queries return identical program sets",
	'tu-xa IDs: ' . implode( ',', $ids_tu_xa ) . ' vs dao-tao IDs: ' . implode( ',', $ids_dao_tao )
);

EligibilityUnitTestRunner::assert(
	in_array( 2014, $ids_tu_xa, true ),
	"UTC program 2014 is present in 'tu-xa' query results"
);

EligibilityUnitTestRunner::assert(
	in_array( 2014, $ids_dao_tao, true ),
	"UTC program 2014 is present in 'dao-tao-tu-xa' query results"
);

// -----------------------------------------------------------------
// SUITE 2: Program Matching Accuracy & UTC Rank #1 Verification
// -----------------------------------------------------------------
EligibilityUnitTestRunner::suite( 'SUITE 2: PROGRAM MATCHING ACCURACY & UTC RANK #1' );

$top_program = $res_tu_xa['programs'][0] ?? null;

EligibilityUnitTestRunner::assert(
	$top_program !== null && $top_program['program_id'] === 2014,
	"Top #1 recommended program is UTC (ID 2014)",
	'Top Program: ' . ( $top_program['title'] ?? 'NONE' ) . ' (ID: ' . ( $top_program['program_id'] ?? 0 ) . ')'
);

EligibilityUnitTestRunner::assert(
	isset( $top_program['school'] ) && ! empty( $top_program['school']['id'] ),
	"Top program has attached school object",
	json_encode( $top_program['school'] ?? [] )
);

EligibilityUnitTestRunner::assert(
	( $top_program['school']['id'] ?? 0 ) === 1853,
	"School ID is 1853 (Trường Đại học Giao thông Vận tải)"
);

EligibilityUnitTestRunner::assert(
	( $top_program['school']['code'] ?? '' ) === 'UTC',
	"School code is 'UTC'"
);

EligibilityUnitTestRunner::assert(
	( $top_program['school']['title'] ?? '' ) === 'Trường Đại học Giao thông Vận tải',
	"School title is 'Trường Đại học Giao thông Vận tải'"
);

EligibilityUnitTestRunner::assert(
	! empty( $top_program['school']['logo'] ),
	"School logo URL is non-empty",
	'Logo: ' . ( $top_program['school']['logo'] ?? '' )
);

EligibilityUnitTestRunner::assert(
	$top_program['score'] >= 40,
	"Match score is >= 40 points",
	'Score: ' . $top_program['score']
);

EligibilityUnitTestRunner::assert(
	$top_program['preliminary_status'] !== 'not_compatible',
	"Preliminary status is not 'not_compatible'",
	'Status: ' . $top_program['preliminary_status']
);

$match_reasons = $top_program['match_reasons'] ?? [];
EligibilityUnitTestRunner::assert(
	in_array( 'Đáp ứng yêu cầu học vấn tối thiểu (Trung cấp).', $match_reasons, true ),
	"Match reasons include minimum education fulfillment"
);

EligibilityUnitTestRunner::assert(
	in_array( 'Đúng ngành bạn mong muốn học.', $match_reasons, true ),
	"Match reasons include desired major match"
);

EligibilityUnitTestRunner::assert(
	in_array( 'Có địa điểm học trực tiếp tại Hà Nội.', $match_reasons, true ),
	"Match reasons include campus location match"
);

// -----------------------------------------------------------------
// SUITE 3: Campus Query Normalization & Physical Campus Matching
// -----------------------------------------------------------------
EligibilityUnitTestRunner::suite( 'SUITE 3: CAMPUS QUERY NORMALIZATION & PHYSICAL MATCHING' );

$input_hanoi = [
	'education'     => 'cao-dang',
	'desired_major' => 1677,
	'training_type' => 'tu-xa',
	'campus'        => 'ha-noi',
];
$res_hanoi = ltdh_elig_run_check( $input_hanoi );

EligibilityUnitTestRunner::assert(
	! empty( $res_hanoi['programs'] ),
	"Physical campus search for 'ha-noi' returns valid distance programs"
);

// Every program in Hanoi search must support either Hanoi or Online
$campus_check_passed = true;
foreach ( $res_hanoi['programs'] as $p ) {
	$campuses = wp_get_post_terms( $p['program_id'], 'campus', [ 'fields' => 'slugs' ] );
	if ( ! in_array( 'ha-noi', $campuses, true ) && ! in_array( 'online', $campuses, true ) ) {
		$campus_check_passed = false;
		break;
	}
}
EligibilityUnitTestRunner::assert(
	$campus_check_passed,
	"All programs returned in 'ha-noi' search support either 'ha-noi' campus or 'online'"
);

// -----------------------------------------------------------------
// SUITE 4: Ghost School & Orphan Program Exclusion
// -----------------------------------------------------------------
EligibilityUnitTestRunner::suite( 'SUITE 4: GHOST UNIVERSITY & ORPHAN PROGRAM EXCLUSION' );

$all_programs_have_published_schools = true;
$orphan_program_found = false;

foreach ( $res_tu_xa['programs'] as $p ) {
	if ( empty( $p['school'] ) || empty( $p['school']['id'] ) ) {
		$all_programs_have_published_schools = false;
		$orphan_program_found = true;
		break;
	}
	$status = get_post_status( $p['school']['id'] );
	if ( 'publish' !== $status ) {
		$all_programs_have_published_schools = false;
		break;
	}
}

EligibilityUnitTestRunner::assert(
	$all_programs_have_published_schools,
	"100% of returned programs have a valid, published school post",
	"Orphan found: " . ( $orphan_program_found ? 'YES' : 'NO' )
);

// Specifically verify ghost programs (1757, 1705, 1678, 1724, 1718) are excluded
$ghost_ids = [ 1757, 1705, 1678, 1724, 1718 ];
$leaked_ghosts = array_intersect( $ids_tu_xa, $ghost_ids );

EligibilityUnitTestRunner::assert(
	empty( $leaked_ghosts ),
	"Unpublished/ghost school programs (1757, 1705, etc.) are strictly excluded",
	'Leaked IDs: ' . implode( ',', $leaked_ghosts )
);

// -----------------------------------------------------------------
// SUITE 5: Education Hierarchy & Corrupted Data Resilience
// -----------------------------------------------------------------
EligibilityUnitTestRunner::suite( 'SUITE 5: EDUCATION HIERARCHY & DATA INTEGRITY' );

global $wpdb;
$corrupted_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'elig_min_education' AND meta_value = 'thap-phan'" );

EligibilityUnitTestRunner::assert(
	(int) $corrupted_count === 0,
	"Zero 'thap-phan' corrupted records exist in wp_postmeta",
	"Corrupted count: $corrupted_count"
);

$hierarchy = ltdh_elig_get_education_hierarchy();
EligibilityUnitTestRunner::assert(
	isset( $hierarchy['trung-cap'] ) && isset( $hierarchy['cao-dang'] ),
	"Education hierarchy defines trung-cap and cao-dang levels"
);

EligibilityUnitTestRunner::assert(
	$hierarchy['trung-cap'] < $hierarchy['cao-dang'],
	"Hierarchy ordering strictly follows: trung-cap (1) < cao-dang (2)"
);

// -----------------------------------------------------------------
// SUITE 6: Circular 28/2023 Distance Learning Prohibition Enforcer
// -----------------------------------------------------------------
EligibilityUnitTestRunner::suite( 'SUITE 6: CIRCULAR 28/2023 DISTANCE LEARNING PROHIBITIONS' );

$prohibited_slugs = function_exists( 'ltdh_elig_get_prohibited_distance_learning_categories' )
	? ltdh_elig_get_prohibited_distance_learning_categories()
	: [];

EligibilityUnitTestRunner::assert(
	in_array( 'duoc-hoc', $prohibited_slugs, true ) || in_array( 'dieu-duong', $prohibited_slugs, true ),
	"Prohibited distance learning categories define Health / Medical majors (duoc-hoc, dieu-duong)"
);

EligibilityUnitTestRunner::assert(
	in_array( 'su-pham-toan', $prohibited_slugs, true ) || in_array( 'giao-duc-mam-non', $prohibited_slugs, true ),
	"Prohibited distance learning categories define Pedagogy / Education majors (su-pham-toan, giao-duc-mam-non)"
);

// -----------------------------------------------------------------
// SUITE 7: Frontend Data Contract & Desired Major Title Serialization
// -----------------------------------------------------------------
EligibilityUnitTestRunner::suite( 'SUITE 7: FRONTEND DATA CONTRACT & VALIDATION' );

// Test validation
$valid_input = [
	'education'     => 'cao-dang',
	'desired_major' => 1677,
	'training_type' => 'tu-xa',
	'campus'        => 'ha-noi',
];
$validation_res = ltdh_elig_validate_input( $valid_input );
EligibilityUnitTestRunner::assert(
	$validation_res === true,
	"ltdh_elig_validate_input() accepts valid input with true"
);

$invalid_input = [
	'education'     => '',
	'desired_major' => 0,
];
$invalid_res = ltdh_elig_validate_input( $invalid_input );
EligibilityUnitTestRunner::assert(
	is_wp_error( $invalid_res ),
	"ltdh_elig_validate_input() rejects missing education with WP_Error instance"
);

// Test desired_major_title resolution
$test_major_title = get_the_title( 1677 );
EligibilityUnitTestRunner::assert(
	$test_major_title === 'Công nghệ thông tin',
	"Major ID 1677 title resolves to 'Công nghệ thông tin'",
	"Got: '$test_major_title'"
);

// -----------------------------------------------------------------
// SUITE 8: Database Logging & Traceability
// -----------------------------------------------------------------
EligibilityUnitTestRunner::suite( 'SUITE 8: DATABASE LOGGING & TRACEABILITY' );

$test_check_id = ltdh_elig_store_check( $input_tu_xa, $res_tu_xa );
EligibilityUnitTestRunner::assert(
	$test_check_id > 0,
	"ltdh_elig_store_check() stores check record and returns ID > 0",
	"Check ID: $test_check_id"
);

$table_name = $wpdb->prefix . LTDH_TABLE_ELIGIBILITY;
$saved_check = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $test_check_id ) );

EligibilityUnitTestRunner::assert(
	! empty( $saved_check ),
	"Record successfully retrieved from {$table_name}"
);

EligibilityUnitTestRunner::assert(
	$saved_check->input_education === 'cao-dang' && $saved_check->input_training_type === 'tu-xa',
	"Database row contains correct input values (cao-dang, tu-xa)"
);

EligibilityUnitTestRunner::assert(
	(int) $saved_check->top_program_id === 2014,
	"Database row records UTC program 2014 as top_program_id",
	"Top Program ID in DB: {$saved_check->top_program_id}"
);

// Clean up test check row
$wpdb->delete( $table_name, [ 'id' => $test_check_id ], [ '%d' ] );

// Output Final Report
EligibilityUnitTestRunner::report();
