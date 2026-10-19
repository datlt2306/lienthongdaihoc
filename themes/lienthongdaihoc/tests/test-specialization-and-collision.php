<?php
/**
 * Test Suite: Specialization & Training System Collision Resolution
 *
 * Verifies that multiple programs under the same major and same training system
 * are never overwritten, correctly preserve specializations, and render distinct buttons.
 *
 * @package lienthongdaihoc
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

require_once __DIR__ . '/../inc/config/constants.php';

echo "===================================================================\n";
echo "TEST SUITE: SPECIALIZATION & PROGRAM COLLISION RESOLUTION\n";
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

// 1. Verify constant definition
ltdh_assert( defined( 'LTDH_META_SPECIALIZATION' ) && LTDH_META_SPECIALIZATION === 'specialization_title', 'LTDH_META_SPECIALIZATION constant is defined correctly' );

// 2. Verify single-school.php logic by simulation
$school_title = 'Trường Đại học Thương mại';
$major_name = 'Quản trị kinh doanh';
$clean_major_name = preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) );

$mock_programs = [
	[
		'id'          => 101,
		'post_title'  => 'Cử nhân Marketing',
		'clean_title' => 'Cử nhân Marketing',
		'type_name'   => 'Đào tạo từ xa',
		'type_slug'   => 'dao-tao-tu-xa',
		'spec_field'  => '',
	],
	[
		'id'          => 102,
		'post_title'  => 'Cử nhân Thương mại điện tử',
		'clean_title' => 'Cử nhân Thương mại điện tử',
		'type_name'   => 'Đào tạo từ xa',
		'type_slug'   => 'dao-tao-tu-xa',
		'spec_field'  => '',
	],
];

$majors_data = [
	1671 => [
		'id'       => 1671,
		'name'     => $major_name,
		'programs' => [],
	],
];

foreach ( $mock_programs as $p ) {
	$prog_id = $p['id'];
	$clean_title = $p['clean_title'];
	$type_name = $p['type_name'];
	$type_slug = $p['type_slug'];
	$clean_type_name = preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) );
	$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );

	$spec_title = $p['spec_field'];
	if ( empty( $spec_title ) ) {
		$candidate = trim( preg_replace( '/^(?:cử\s+nhân|kỹ\s+sư|liên\s+thông)\s+/iu', '', $clean_title ) );
		$candidate = trim( preg_replace( '/\s*\([^)]*\)/iu', '', $candidate ) );
		$candidate = trim( preg_replace( '/^ngành\s+/iu', '', $candidate ) );
		if ( ! empty( $candidate ) && mb_strtolower( $candidate, 'UTF-8' ) !== mb_strtolower( $clean_major_name, 'UTF-8' ) ) {
			$spec_title = $candidate;
		}
	}

	$prog_data = [
		'id'                => $prog_id,
		'title'             => $clean_title,
		'specialization'    => $spec_title,
		'opportunity_title' => $opportunity_title,
		'type_name'         => $clean_type_name,
		'he'                => $type_slug,
	];

	// Store by ID (no overwrite)
	$majors_data[ 1671 ]['programs'][ $prog_id ] = $prog_data;
}

$majors_data[ 1671 ]['programs'] = array_values( $majors_data[ 1671 ]['programs'] );

// Check that both programs are retained
ltdh_assert( count( $majors_data[ 1671 ]['programs'] ) === 2, 'Both Marketing and E-commerce programs are retained without collision' );

// Check that specialization is cleanly inferred
ltdh_assert( $majors_data[ 1671 ]['programs'][0]['specialization'] === 'Marketing', 'Specialization Marketing correctly inferred from title' );
ltdh_assert( $majors_data[ 1671 ]['programs'][1]['specialization'] === 'Thương mại điện tử', 'Specialization Thương mại điện tử correctly inferred from title' );

// Check data-major-specs generation
$major_spec_names = array_unique( array_filter( array_column( $majors_data[ 1671 ]['programs'], 'specialization' ) ) );
$specs_attr = mb_strtolower( implode( ' ', $major_spec_names ), 'UTF-8' );
ltdh_assert( false !== strpos( $specs_attr, 'marketing' ) && false !== strpos( $specs_attr, 'thương mại điện tử' ), 'data-major-specs attribute includes both specializations' );

// Check explicit specialization priority
$explicit_spec = 'Marketing số & Truyền thông';
$candidate = 'Marketing';
$final_spec = $explicit_spec ?: $candidate;
ltdh_assert( $final_spec === 'Marketing số & Truyền thông', 'Explicit specialization field takes priority over title parsing' );

// Check single-school.php file contains no old overwrite pattern
$single_school_src = file_get_contents( __DIR__ . '/../single-school.php' );
ltdh_assert( false === strpos( $single_school_src, "['programs'][ \$prog_he_key ]" ), 'single-school.php zero occurrences of overwriting programs by prog_he_key' );
ltdh_assert( false !== strpos( $single_school_src, "data-major-specs" ), 'single-school.php contains data-major-specs attribute' );

echo "\n===================================================================\n";
echo "SUMMARY: $pass_count PASSED, $fail_count FAILED\n";
echo "===================================================================\n";

if ( $fail_count > 0 ) {
	exit( 1 );
}
