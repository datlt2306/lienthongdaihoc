<?php
/**
 * Test Suite: Contact Info Admin Fields & Fallback Integration
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class ContactInfoAdminTestSuite {
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
echo "TEST SUITE: CONTACT INFO ADMIN FIELDS & FALLBACK\n";
echo "===================================================================\n\n";

$json_raw = file_get_contents( ABSPATH . 'inc/acf-import-fields.json' );
$json_data = json_decode( $json_raw, true );

ContactInfoAdminTestSuite::assert(
	is_array( $json_data ),
	"acf-import-fields.json: Valid JSON syntax"
);

// 1. Check School group
$school_group = null;
$program_group = null;

foreach ( $json_data as $group ) {
	if ( ( $group['key'] ?? '' ) === 'group_school_details' ) {
		$school_group = $group;
	} elseif ( ( $group['key'] ?? '' ) === 'group_program_details' ) {
		$program_group = $group;
	}
}

ContactInfoAdminTestSuite::assert(
	$school_group !== null,
	"acf-import-fields.json: group_school_details exists"
);

ContactInfoAdminTestSuite::assert(
	$program_group !== null,
	"acf-import-fields.json: group_program_details exists"
);

// Check School Contact Info field (removed per request)
$school_contact = null;
foreach ( $school_group['fields'] ?? [] as $f ) {
	if ( ( $f['name'] ?? '' ) === 'contact_info' ) {
		$school_contact = $f;
		break;
	}
}

ContactInfoAdminTestSuite::assert(
	$school_contact === null,
	"group_school_details: 'contact_info' field removed from School"
);

// Check Program Contact Info field
$program_contact = null;
foreach ( $program_group['fields'] ?? [] as $f ) {
	if ( ( $f['name'] ?? '' ) === 'contact_info' ) {
		$program_contact = $f;
		break;
	}
}

ContactInfoAdminTestSuite::assert(
	$program_contact !== null,
	"group_program_details: Has 'contact_info' field"
);

ContactInfoAdminTestSuite::assert(
	( $program_contact['label'] ?? '' ) === 'Thông tin liên hệ & Tiếp nhận hồ sơ',
	"group_program_details: Field label is 'Thông tin liên hệ & Tiếp nhận hồ sơ'"
);

ContactInfoAdminTestSuite::assert(
	( $program_contact['key'] ?? '' ) === 'field_program_contact_info',
	"group_program_details: Key is 'field_program_contact_info'"
);

// 2. Check inc/acf-fields.php label overrides
$acf_fields_php = file_get_contents( ABSPATH . 'inc/acf-fields.php' );

ContactInfoAdminTestSuite::assert(
	false !== strpos( $acf_fields_php, 'field_program_contact_info' ),
	"inc/acf-fields.php: Hooks field_program_contact_info"
);

ContactInfoAdminTestSuite::assert(
	false !== strpos( $acf_fields_php, 'field_school_contact_info' ),
	"inc/acf-fields.php: Hooks field_school_contact_info"
);

// 3. Check single-program.php fallback logic
$prog_php = file_get_contents( ABSPATH . 'single-program.php' );

ContactInfoAdminTestSuite::assert(
	false !== strpos( $prog_php, '$prog_contact_custom = get_field( \'contact_info\', $program_id );' ),
	"single-program.php: Checks program contact_info first"
);

ContactInfoAdminTestSuite::assert(
	false !== strpos( $prog_php, '$school_contact_raw  = $school_id ? get_field( \'contact_info\', $school_id ) : \'\';' ),
	"single-program.php: Falls back to school contact_info if program empty"
);

echo "\n===================================================================\n";
echo sprintf( "SUMMARY: %d PASSED, %d FAILED\n", ContactInfoAdminTestSuite::$passed, ContactInfoAdminTestSuite::$failed );
echo "===================================================================\n";

if ( ContactInfoAdminTestSuite::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
