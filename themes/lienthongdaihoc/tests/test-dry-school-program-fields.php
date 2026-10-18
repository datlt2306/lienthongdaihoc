<?php
/**
 * Test Suite: DRY School-Program Fields Architecture
 *
 * Verifies that the 5 fields/clusters requested by the user are properly:
 * 1. Added/standardized in School (group_school_details).
 * 2. Removed from Program (group_program_details) and hidden in WP Admin.
 * 3. Inherited seamlessly on frontend (helpers & single-program.php).
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class DryFieldsTestSuite {
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
echo "TEST SUITE: DRY SCHOOL-PROGRAM FIELDS ARCHITECTURE\n";
echo "===================================================================\n\n";

$json_raw = file_get_contents( ABSPATH . 'inc/acf-import-fields.json' );
$json_data = json_decode( $json_raw, true );

DryFieldsTestSuite::assert(
	is_array( $json_data ),
	"acf-import-fields.json: Valid JSON syntax"
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

// -------------------------------------------------------------
// 1. SCHOOL GROUP TESTS
// -------------------------------------------------------------
echo "\n--- 1. SCHOOL GROUP (group_school_details) ---\n";

$school_fields_by_name = [];
foreach ( $school_group['fields'] ?? [] as $f ) {
	if ( ! empty( $f['name'] ) ) {
		$school_fields_by_name[ $f['name'] ] = $f;
	}
}

// 1.1 Contact info
DryFieldsTestSuite::assert(
	isset( $school_fields_by_name['contact_info'] ),
	"School: Has field 'contact_info'"
);
DryFieldsTestSuite::assert(
	( $school_fields_by_name['contact_info']['label'] ?? '' ) === 'Thông tin liên hệ & Tiếp nhận hồ sơ',
	"School: 'contact_info' label is 'Thông tin liên hệ & Tiếp nhận hồ sơ'"
);

// 1.2 Required documents
DryFieldsTestSuite::assert(
	isset( $school_fields_by_name['default_required_documents'] ),
	"School: Has field 'default_required_documents'"
);
DryFieldsTestSuite::assert(
	( $school_fields_by_name['default_required_documents']['label'] ?? '' ) === 'Hồ sơ xét tuyển cần thiết',
	"School: Required documents label is 'Hồ sơ xét tuyển cần thiết'"
);

// 1.3 Tuition increase roadmap
DryFieldsTestSuite::assert(
	isset( $school_fields_by_name['tuition_increase_roadmap'] ),
	"School: Has field 'tuition_increase_roadmap'"
);
DryFieldsTestSuite::assert(
	( $school_fields_by_name['tuition_increase_roadmap']['label'] ?? '' ) === 'Lộ trình tăng học phí / Ghi chú học phí',
	"School: 'tuition_increase_roadmap' label is 'Lộ trình tăng học phí / Ghi chú học phí'"
);

// 1.4 Duration
DryFieldsTestSuite::assert(
	isset( $school_fields_by_name['duration'] ),
	"School: Has field 'duration'"
);
DryFieldsTestSuite::assert(
	( $school_fields_by_name['duration']['label'] ?? '' ) === 'Thời gian học',
	"School: 'duration' label is 'Thời gian học'"
);

// 1.5 Exemptions (title, intro, items)
DryFieldsTestSuite::assert(
	isset( $school_fields_by_name['exemption_title'] ),
	"School: Has field 'exemption_title'"
);
DryFieldsTestSuite::assert(
	( $school_fields_by_name['exemption_title']['label'] ?? '' ) === 'Tiêu đề khối quy định miễn môn',
	"School: 'exemption_title' label is 'Tiêu đề khối quy định miễn môn'"
);

DryFieldsTestSuite::assert(
	isset( $school_fields_by_name['exemption_intro'] ),
	"School: Has field 'exemption_intro'"
);
DryFieldsTestSuite::assert(
	( $school_fields_by_name['exemption_intro']['label'] ?? '' ) === 'Mô tả / Lời mở đầu quy định miễn môn',
	"School: 'exemption_intro' label is 'Mô tả / Lời mở đầu quy định miễn môn'"
);

DryFieldsTestSuite::assert(
	isset( $school_fields_by_name['exemption_items'] ),
	"School: Has field 'exemption_items'"
);
DryFieldsTestSuite::assert(
	( $school_fields_by_name['exemption_items']['label'] ?? '' ) === 'Danh sách các môn được xét miễn giảm',
	"School: 'exemption_items' label is 'Danh sách các môn được xét miễn giảm'"
);

// -------------------------------------------------------------
// 2. PROGRAM GROUP TESTS (Removed fields)
// -------------------------------------------------------------
echo "\n--- 2. PROGRAM GROUP (group_program_details) ---\n";

$program_fields_by_name = [];
foreach ( $program_group['fields'] ?? [] as $f ) {
	if ( ! empty( $f['name'] ) ) {
		$program_fields_by_name[ $f['name'] ] = $f;
	}
}

DryFieldsTestSuite::assert(
	! isset( $program_fields_by_name['contact_info'] ),
	"Program: Field 'contact_info' is removed"
);
DryFieldsTestSuite::assert(
	! isset( $program_fields_by_name['required_documents'] ),
	"Program: Field 'required_documents' is removed"
);
DryFieldsTestSuite::assert(
	! isset( $program_fields_by_name['tuition_increase_roadmap'] ),
	"Program: Field 'tuition_increase_roadmap' is removed"
);
DryFieldsTestSuite::assert(
	! isset( $program_fields_by_name['duration'] ),
	"Program: Field 'duration' is removed"
);
DryFieldsTestSuite::assert(
	! isset( $program_fields_by_name['exemption_title'] ),
	"Program: Field 'exemption_title' is removed"
);
DryFieldsTestSuite::assert(
	! isset( $program_fields_by_name['exemption_intro'] ),
	"Program: Field 'exemption_intro' is removed"
);
DryFieldsTestSuite::assert(
	! isset( $program_fields_by_name['exemption_items'] ),
	"Program: Field 'exemption_items' is removed"
);

// -------------------------------------------------------------
// 3. PHP ADMIN HIDING & VALIDATION TESTS
// -------------------------------------------------------------
echo "\n--- 3. PHP ADMIN HIDING & VALIDATION (inc/acf-fields.php) ---\n";

$acf_fields_php = file_get_contents( ABSPATH . 'inc/acf-fields.php' );

$hidden_keys = [
	'field_program_contact_info',
	'field_program_documents',
	'field_program_tuition_increase_roadmap',
	'field_program_duration',
	'field_program_exemption_title',
	'field_program_exemption_intro',
	'field_program_exemption_items',
];

foreach ( $hidden_keys as $k ) {
	DryFieldsTestSuite::assert(
		false !== strpos( $acf_fields_php, "add_filter( 'acf/prepare_field/key={$k}', '__return_false' );" ),
		"inc/acf-fields.php: Explicitly hides {$k} via acf/prepare_field"
	);
}

// Ensure duration & documents are NOT required in ltdh_validate_program_acf_publish_fields
DryFieldsTestSuite::assert(
	false === strpos( $acf_fields_php, "acf_add_validation_error( 'acf[field_program_duration]'" ),
	"inc/acf-fields.php: Program publish validation does NOT require duration"
);
DryFieldsTestSuite::assert(
	false === strpos( $acf_fields_php, "acf_add_validation_error( 'acf[field_program_documents]'" ),
	"inc/acf-fields.php: Program publish validation does NOT require required_documents"
);

// -------------------------------------------------------------
// 4. HELPER FUNCTIONS & FRONTEND INHERITANCE
// -------------------------------------------------------------
echo "\n--- 4. HELPER FUNCTIONS & FRONTEND (class-helpers.php & single-program.php) ---\n";

$helpers_php = file_get_contents( ABSPATH . 'inc/core/class-helpers.php' );

DryFieldsTestSuite::assert(
	false !== strpos( $helpers_php, 'function ltdh_get_program_duration' ),
	"class-helpers.php: Defines ltdh_get_program_duration"
);
DryFieldsTestSuite::assert(
	false !== strpos( $helpers_php, 'function ltdh_get_program_tuition_roadmap' ),
	"class-helpers.php: Defines ltdh_get_program_tuition_roadmap"
);
DryFieldsTestSuite::assert(
	false !== strpos( $helpers_php, 'function ltdh_get_program_required_documents' ),
	"class-helpers.php: Defines ltdh_get_program_required_documents"
);
DryFieldsTestSuite::assert(
	false !== strpos( $helpers_php, 'function ltdh_get_program_exemptions' ),
	"class-helpers.php: Defines ltdh_get_program_exemptions"
);

$single_prog_php = file_get_contents( ABSPATH . 'single-program.php' );

DryFieldsTestSuite::assert(
	false !== strpos( $single_prog_php, 'ltdh_get_program_duration' ),
	"single-program.php: Uses ltdh_get_program_duration for duration"
);
DryFieldsTestSuite::assert(
	false !== strpos( $single_prog_php, 'ltdh_get_program_tuition_roadmap' ),
	"single-program.php: Uses ltdh_get_program_tuition_roadmap for tuition roadmap"
);
DryFieldsTestSuite::assert(
	false !== strpos( $single_prog_php, '$school_contact_raw' ),
	"single-program.php: Inherits school contact_info"
);
DryFieldsTestSuite::assert(
	false !== strpos( $single_prog_php, 'ltdh_get_program_exemptions' ),
	"single-program.php: Uses ltdh_get_program_exemptions for exemptions"
);

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n===================================================================\n";
echo sprintf( "SUMMARY: %d PASSED, %d FAILED\n", DryFieldsTestSuite::$passed, DryFieldsTestSuite::$failed );
echo "===================================================================\n";

if ( DryFieldsTestSuite::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
