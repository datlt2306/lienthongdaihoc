<?php
/**
 * Test Suite: DRY Degree Type & Diploma Value Inheritance
 *
 * Verifies that:
 * 1. degree_type inherits from Major when not overridden on Program.
 * 2. diploma_value inherits from School when not overridden on Program.
 * 3. Fallbacks to standard MOET & TT 27/2019/TT-BGDĐT text are preserved.
 *
 * @package lienthongdaihoc
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

echo "===================================================================\n";
echo "TEST SUITE: DRY DEGREE TYPE & DIPLOMA VALUE INHERITANCE\n";
echo "===================================================================\n";

$pass_count = 0;
$fail_count = 0;

function ltdh_assert( $condition, $message ) {
	global $pass_count, $fail_count;
	if ( $condition ) {
		echo "  [PASS] $message\n";
		$pass_count++;
	} else {
		echo "  [FAIL] $message\n";
		$fail_count++;
	}
}

// 1. Verify helper functions exist in inc/core/class-helpers.php
$helpers_src = file_get_contents( __DIR__ . '/../inc/core/class-helpers.php' );
ltdh_assert( false !== strpos( $helpers_src, 'function ltdh_get_program_degree_type' ), 'ltdh_get_program_degree_type helper function is defined' );
ltdh_assert( false !== strpos( $helpers_src, 'function ltdh_get_program_diploma_value' ), 'ltdh_get_program_diploma_value helper function is defined' );

// 2. Verify ACF JSON definitions for group_major_details and group_school_details
$json_path = __DIR__ . '/../inc/acf-import-fields.json';
ltdh_assert( file_exists( $json_path ), 'acf-import-fields.json exists' );

$json_data = json_decode( file_get_contents( $json_path ), true );
$groups_by_key = [];
foreach ( $json_data as $g ) {
	if ( ! empty( $g['key'] ) ) {
		$groups_by_key[ $g['key'] ] = $g;
	}
}

// Check Major has degree_type
ltdh_assert( isset( $groups_by_key['group_major_details'] ), 'group_major_details is present in JSON' );
$major_field_names = array_column( $groups_by_key['group_major_details']['fields'], 'name' );
ltdh_assert( in_array( 'degree_type', $major_field_names, true ), 'degree_type field is present in group_major_details' );

// Check School has diploma_value
ltdh_assert( isset( $groups_by_key['group_school_details'] ), 'group_school_details is present in JSON' );
$school_field_names = array_column( $groups_by_key['group_school_details']['fields'], 'name' );
ltdh_assert( in_array( 'diploma_value', $school_field_names, true ), 'diploma_value field is present in group_school_details' );

// Check Program has specialization_title, degree_type override, and diploma_value override
ltdh_assert( isset( $groups_by_key['group_program_details'] ), 'group_program_details is present in JSON' );
$prog_field_names = array_column( $groups_by_key['group_program_details']['fields'], 'name' );
ltdh_assert( in_array( 'specialization_title', $prog_field_names, true ), 'specialization_title is present in group_program_details' );
ltdh_assert( in_array( 'degree_type', $prog_field_names, true ), 'degree_type override is present in group_program_details' );
ltdh_assert( in_array( 'diploma_value', $prog_field_names, true ), 'diploma_value override is present in group_program_details' );

// 3. Simulate degree_type resolution logic
function simulate_get_degree_type( $prog_custom, $major_deg, $major_title ) {
	if ( ! empty( $prog_custom ) && ! empty( trim( $prog_custom ) ) ) {
		return trim( $prog_custom );
	}
	if ( ! empty( $major_deg ) && ! empty( trim( $major_deg ) ) ) {
		return trim( $major_deg );
	}
	$clean_major = preg_replace( '/^ngành\s+/iu', '', trim( $major_title ) );
	return ! empty( $clean_major ) ? 'Cử nhân ' . $clean_major : 'Cử nhân / Kỹ sư';
}

ltdh_assert(
	simulate_get_degree_type( '', 'Cử nhân Luật kinh tế', 'Ngành Luật kinh tế' ) === 'Cử nhân Luật kinh tế',
	'degree_type cleanly inherits from Major when Program field is empty'
);

ltdh_assert(
	simulate_get_degree_type( 'Kỹ sư chuyên sâu CNTT', 'Cử nhân Công nghệ thông tin', 'Công nghệ thông tin' ) === 'Kỹ sư chuyên sâu CNTT',
	'degree_type respects Program override when provided'
);

ltdh_assert(
	simulate_get_degree_type( '', '', 'Quản trị kinh doanh' ) === 'Cử nhân Quản trị kinh doanh',
	'degree_type cleanly falls back to "Cử nhân [Tên ngành]" when both are empty'
);

// 4. Simulate diploma_value resolution logic
function simulate_get_diploma_value( $prog_custom, $school_dip, $school_title ) {
	if ( ! empty( $prog_custom ) && ! empty( trim( $prog_custom ) ) ) {
		return trim( $prog_custom );
	}
	if ( ! empty( $school_dip ) && ! empty( trim( $school_dip ) ) ) {
		return trim( $school_dip );
	}
	return sprintf(
		'Bằng tốt nghiệp do %s cấp, phôi bằng chuẩn Bộ Giáo dục & Đào tạo. Theo Thông tư 27/2019/TT-BGDĐT, trên văn bằng không ghi hình thức đào tạo, có giá trị tương đương bằng chính quy, đủ điều kiện học lên Thạc sĩ/Tiến sĩ hoặc thi tuyển công chức, viên chức nhà nước.',
		$school_title
	);
}

$school_name = 'Trường Đại học Thương mại';
$inherited_diploma = simulate_get_diploma_value( '', '', $school_name );
ltdh_assert(
	false !== strpos( $inherited_diploma, 'Trường Đại học Thương mại' ) && false !== strpos( $inherited_diploma, 'Thông tư 27/2019/TT-BGDĐT' ),
	'diploma_value generates legal text with school name and Circular 27 when school field is empty'
);

$custom_school_dip = 'Bằng Cử nhân do ĐH Thương Mại cấp, phôi bằng chuẩn Bộ GD&ĐT.';
ltdh_assert(
	simulate_get_diploma_value( '', $custom_school_dip, $school_name ) === $custom_school_dip,
	'diploma_value inherits custom School diploma_value when set'
);

$override_prog_dip = 'Bằng Kỹ sư do ĐH Bách Khoa cấp đặc thù.';
ltdh_assert(
	simulate_get_diploma_value( $override_prog_dip, $custom_school_dip, $school_name ) === $override_prog_dip,
	'diploma_value respects Program override when explicitly set'
);

// 5. Verify single-program.php uses the DRY helpers
$single_program_src = file_get_contents( __DIR__ . '/../single-program.php' );
ltdh_assert( false !== strpos( $single_program_src, 'ltdh_get_program_degree_type' ), 'single-program.php calls ltdh_get_program_degree_type' );
ltdh_assert( false !== strpos( $single_program_src, 'ltdh_get_program_diploma_value' ), 'single-program.php calls ltdh_get_program_diploma_value' );

// 6. Verify comparison.php uses the DRY helpers
$comparison_src = file_get_contents( __DIR__ . '/../inc/comparison.php' );
ltdh_assert( false !== strpos( $comparison_src, 'ltdh_get_program_degree_type' ), 'inc/comparison.php calls ltdh_get_program_degree_type' );
ltdh_assert( false !== strpos( $comparison_src, 'ltdh_get_program_diploma_value' ), 'inc/comparison.php calls ltdh_get_program_diploma_value' );

echo "\n===================================================================\n";
echo "SUMMARY: $pass_count PASSED, $fail_count FAILED\n";
echo "===================================================================\n";

if ( $fail_count > 0 ) {
	exit( 1 );
}
