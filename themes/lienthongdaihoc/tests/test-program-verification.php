<?php
/**
 * Test Suite: Program Verification Status and Public Display
 * 
 * Verifies all 10 acceptance tests and security constraints.
 * Run with: wp eval-file tests/test-program-verification.php
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script execution not allowed.' );
}

echo "\n======================================================\n";
echo "RUNNING PROGRAM VERIFICATION ACCEPTANCE TESTS\n";
echo "======================================================\n\n";

global $pass_count, $fail_count;
$pass_count = 0;
$fail_count = 0;

wp_set_current_user( 1 );

function run_test( string $description, bool $condition, string $details = '' ): void {
	global $pass_count, $fail_count;
	if ( $condition ) {
		$pass_count++;
		echo " [PASS] {$description}\n";
	} else {
		$fail_count++;
		echo " [FAIL] {$description}\n";
		if ( $details ) {
			echo "        Details: {$details}\n";
		}
	}
}

// Create a temporary test program post
$test_program_id = wp_insert_post( [
	'post_title'   => 'Test Program Verification ' . time(),
	'post_type'    => 'program',
	'post_status'  => 'publish',
	'post_content' => '<p>Test program content for verification testing.</p>',
] );

if ( ! $test_program_id || is_wp_error( $test_program_id ) ) {
	exit( "Failed to create test program post.\n" );
}

try {
	// ====================================================
	// Test 9: Existing program records have no verification metadata
	// ====================================================
	$verif_initial = ltdh_get_program_verification( $test_program_id );
	run_test(
		'Test 9: Unset metadata defaults safely to unverified with empty date and source',
		$verif_initial['status'] === 'unverified'
		&& $verif_initial['is_verified'] === false
		&& $verif_initial['is_partial'] === false
		&& $verif_initial['is_unverified'] === true
		&& $verif_initial['last_verified_at'] === ''
		&& $verif_initial['source_url'] === '',
		'Status: ' . $verif_initial['status'] . ', Date: ' . $verif_initial['last_verified_at']
	);

	// ====================================================
	// Test 4: A program is unverified
	// ====================================================
	update_post_meta( $test_program_id, 'verification_status', 'unverified' );
	$html_unverified = ltdh_get_program_verification_badge_html( $test_program_id, 'detail' );
	$card_unverified = ltdh_get_program_verification_badge_html( $test_program_id, 'card' );

	run_test(
		'Test 4: Unverified program outputs nothing on detail page and listing card',
		$html_unverified === '' && $card_unverified === '',
		'HTML: ' . $html_unverified
	);

	// ====================================================
	// Test 1: A program is verified and has a date and source URL
	// ====================================================
	update_post_meta( $test_program_id, 'verification_status', 'verified' );
	update_post_meta( $test_program_id, 'last_verified_at', '2026-05-15' );
	update_post_meta( $test_program_id, 'verification_source', 'https://utc.edu.vn/tuyen-sinh-2026' );
	update_post_meta( $test_program_id, 'verification_source_title', 'Đề án tuyển sinh UTC 2026' );
	update_post_meta( $test_program_id, 'verification_notes', 'Ghi chú nội bộ không được hiển thị' );

	$verif_t1 = ltdh_get_program_verification( $test_program_id );
	$html_t1  = ltdh_get_program_verification_badge_html( $test_program_id, 'detail' );
	$card_t1  = ltdh_get_program_verification_badge_html( $test_program_id, 'card' );

	run_test(
		'Test 1a: Verification data parsed with Vietnamese date 15/05/2026 and valid source URL',
		$verif_t1['status'] === 'verified'
		&& $verif_t1['last_verified_at'] === '15/05/2026'
		&& $verif_t1['source_url'] === 'https://utc.edu.vn/tuyen-sinh-2026'
		&& $verif_t1['source_title'] === 'Đề án tuyển sinh UTC 2026',
		'Parsed date: ' . $verif_t1['last_verified_at']
	);

	run_test(
		'Test 1b: Detail page displays verified badge, date, and source link',
		strpos( $html_t1, 'Thông tin chương trình đã được xác minh' ) !== false
		&& strpos( $html_t1, '✓' ) !== false
		&& strpos( $html_t1, 'Cập nhật xác minh:' ) !== false
		&& strpos( $html_t1, '15/05/2026' ) !== false
		&& strpos( $html_t1, 'Nguồn thông tin:' ) !== false
		&& strpos( $html_t1, 'https://utc.edu.vn/tuyen-sinh-2026' ) !== false
		&& strpos( $html_t1, 'Đề án tuyển sinh UTC 2026' ) !== false
		&& strpos( $html_t1, 'target="_blank"' ) !== false
		&& strpos( $html_t1, 'rel="noopener noreferrer nofollow"' ) !== false,
		'HTML: ' . $html_t1
	);

	run_test(
		'Test 1c: Internal verification notes are NEVER exposed in public output',
		strpos( $html_t1, 'Ghi chú nội bộ không được hiển thị' ) === false
		&& strpos( $card_t1, 'Ghi chú nội bộ không được hiển thị' ) === false,
		'Found private note in public HTML!'
	);

	run_test(
		'Test 1d: Listing card displays restrained verified indicator',
		strpos( $card_t1, 'Đã xác minh' ) !== false,
		'Card HTML: ' . $card_t1
	);

	// ====================================================
	// Test 2: A program is verified but has no verification date
	// ====================================================
	update_post_meta( $test_program_id, 'last_verified_at', '' );
	$html_t2 = ltdh_get_program_verification_badge_html( $test_program_id, 'detail' );

	run_test(
		'Test 2: Verified program with empty date displays badge without date or empty element',
		strpos( $html_t2, 'Thông tin chương trình đã được xác minh' ) !== false
		&& strpos( $html_t2, 'Cập nhật xác minh:' ) === false,
		'HTML: ' . $html_t2
	);

	// ====================================================
	// Test 7: A program has no source URL
	// ====================================================
	update_post_meta( $test_program_id, 'verification_source', '' );
	update_post_meta( $test_program_id, 'last_verified_at', '2026-05-15' );
	$html_t7 = ltdh_get_program_verification_badge_html( $test_program_id, 'detail' );

	run_test(
		'Test 7: Program with empty source renders normally without empty source link or empty label',
		strpos( $html_t7, 'Thông tin chương trình đã được xác minh' ) !== false
		&& strpos( $html_t7, '15/05/2026' ) !== false
		&& strpos( $html_t7, 'Nguồn thông tin:' ) === false
		&& strpos( $html_t7, 'href=""' ) === false,
		'HTML: ' . $html_t7
	);

	// Test 7b: Source URL with no title uses fallback "Xem nguồn chính thức"
	update_post_meta( $test_program_id, 'verification_source', 'https://daihoc.edu.vn' );
	update_post_meta( $test_program_id, 'verification_source_title', '' );
	$html_t7b = ltdh_get_program_verification_badge_html( $test_program_id, 'detail' );
	run_test(
		'Test 7b: Source URL with empty title falls back to "Xem nguồn chính thức"',
		strpos( $html_t7b, 'Xem nguồn chính thức' ) !== false,
		'HTML: ' . $html_t7b
	);

	// ====================================================
	// Test 3: A program is partially verified
	// ====================================================
	update_post_meta( $test_program_id, 'verification_status', 'partially_verified' );
	$html_t3 = ltdh_get_program_verification_badge_html( $test_program_id, 'detail' );
	$card_t3 = ltdh_get_program_verification_badge_html( $test_program_id, 'card' );

	run_test(
		'Test 3a: Partially verified program displays restrained notice and NOT full verified badge',
		strpos( $html_t3, 'Một số thông tin chương trình chưa được xác minh đầy đủ.' ) !== false
		&& strpos( $html_t3, 'Thông tin chương trình đã được xác minh' ) === false,
		'HTML: ' . $html_t3
	);

	run_test(
		'Test 3b: Partially verified program outputs nothing on listing card',
		$card_t3 === '',
		'Card HTML: ' . $card_t3
	);

	// ====================================================
	// Test 5: Administrator edits program description - date does NOT change automatically
	// ====================================================
	update_post_meta( $test_program_id, 'verification_status', 'verified' );
	update_post_meta( $test_program_id, 'last_verified_at', '2025-01-10' );

	// Simulate post update of description/content
	wp_update_post( [
		'ID'           => $test_program_id,
		'post_content' => '<p>Updated description by editor.</p>',
	] );

	$meta_date_after = get_post_meta( $test_program_id, 'last_verified_at', true );
	run_test(
		'Test 5: Editing program content does not automatically change or overwrite verification date',
		$meta_date_after === '2025-01-10',
		'Date after save: ' . $meta_date_after
	);

	// ====================================================
	// Test 6: Administrator explicitly confirms a new verification
	// ====================================================
	$explicit_date = '2026-10-13';
	$_POST['acf'] = [
		'field_program_verification_status'    => 'verified',
		'field_program_last_verified_at'       => $explicit_date,
		'field_program_verification_source'    => 'https://utc.edu.vn/ts-2026',
		'field_program_verification_source_title' => 'Đề án 2026',
		'field_program_verification_notes'     => 'Đã kiểm tra kỹ',
	];

	// Run fail-safe save handler simulating form submission
	$post_obj = get_post( $test_program_id );
	ltdh_save_custom_fields_fail_safe( $test_program_id, $post_obj );

	$saved_status = get_post_meta( $test_program_id, 'verification_status', true );
	$saved_date   = get_post_meta( $test_program_id, 'last_verified_at', true );
	$saved_source = get_post_meta( $test_program_id, 'verification_source', true );

	run_test(
		'Test 6: Explicit administrator submission correctly updates verification status and date',
		$saved_status === 'verified' && $saved_date === '2026-10-13' && $saved_source === 'https://utc.edu.vn/ts-2026',
		"Status: {$saved_status}, Date: {$saved_date}, Source: {$saved_source}"
	);
	unset( $_POST['acf'] );

	// ====================================================
	// Test 8: Verified program with unknown admission status
	// ====================================================
	// Set admission status to 'tam-ngung' (closed) or non-standard, verification to 'verified'
	update_post_meta( $test_program_id, 'admission_status', 'tam-ngung' );
	update_post_meta( $test_program_id, 'verification_status', 'verified' );

	$current_admission_status = get_post_meta( $test_program_id, 'admission_status', true );
	$verif_t8 = ltdh_get_program_verification( $test_program_id );

	run_test(
		'Test 8: Verification status does not infer or alter admission status (tam-ngung remains tam-ngung)',
		$verif_t8['status'] === 'verified' && $current_admission_status === 'tam-ngung',
		"Verification: {$verif_t8['status']}, Admission: {$current_admission_status}"
	);

	// ====================================================
	// Security & Validation Edge Cases
	// ====================================================
	// Test invalid status fallback
	$sanitized_status = ltdh_sanitize_verification_meta( 'verification_status', 'hacked_status' );
	run_test(
		'Security: Invalid verification status falls back to unverified',
		$sanitized_status === 'unverified',
		'Got: ' . $sanitized_status
	);

	// Test javascript: or invalid URL rejection
	$sanitized_bad_url = ltdh_sanitize_verification_meta( 'verification_source', 'javascript:alert(1)' );
	run_test(
		'Security: Dangerous URL scheme (javascript:) is rejected',
		$sanitized_bad_url === '',
		'Got: ' . $sanitized_bad_url
	);

	$sanitized_file_url = ltdh_sanitize_verification_meta( 'verification_source', 'file:///etc/passwd' );
	run_test(
		'Security: Non-HTTP URL scheme (file://) is rejected',
		$sanitized_file_url === '',
		'Got: ' . $sanitized_file_url
	);

	// ====================================================
	// Test 10: Check desktop and mobile rendering classes
	// ====================================================
	$html_render = ltdh_get_program_verification_badge_html( $test_program_id, 'detail' );
	run_test(
		'Test 10: HTML contains clean full-width layout with responsive flex meta items',
		strpos( $html_render, 'ltdh-verification-badge' ) !== false
		&& strpos( $html_render, 'border-t border-emerald-200/70' ) !== false
		&& strpos( $html_render, 'flex flex-wrap items-center' ) !== false,
		'Responsive classes missing in badge HTML'
	);

} finally {
	// Clean up temporary post
	wp_delete_post( $test_program_id, true );
}

echo "\n======================================================\n";
echo "SUMMARY: {$pass_count} PASSED, {$fail_count} FAILED\n";
echo "======================================================\n\n";

if ( $fail_count > 0 ) {
	exit( 1 );
}
exit( 0 );
