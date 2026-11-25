<?php
/**
 * Test Program Quota Status (Đang tuyển sinh / Ngừng tuyển sinh)
 *
 * Verifies that:
 * 1. ltdh_get_program_quota_display returns 'Đang tuyển sinh' or 'Ngừng tuyển sinh' without requiring numbers.
 * 2. ltdh_is_program_quota_open works reliably.
 * 3. Two-way sync between quota and admission_status works seamlessly.
 * 4. Program overview template renders status cleanly without numbers.
 *
 * @package lienthongdaihoc
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

class TestProgramQuotaStatus {
	private static $passed = 0;
	private static $failed = 0;

	public static function assert( $condition, $message ) {
		if ( $condition ) {
			echo "  [PASS] " . $message . "\n";
			self::$passed++;
		} else {
			echo "  [FAIL] " . $message . "\n";
			self::$failed++;
		}
	}

	public static function run() {
		echo "===================================================================\n";
		echo "TEST SUITE: PROGRAM QUOTA STATUS (ĐANG TUYỂN / NGỪNG TUYỂN SINH)\n";
		echo "===================================================================\n\n";

		// Test 1: Helper functions exist
		self::assert( function_exists( 'ltdh_get_program_quota_display' ), "ltdh_get_program_quota_display function exists" );
		self::assert( function_exists( 'ltdh_is_program_quota_open' ), "ltdh_is_program_quota_open function exists" );
		self::assert( method_exists( 'LTDH_Program_Helpers', 'get_quota_display' ), "LTDH_Program_Helpers::get_quota_display method exists" );

		// Create a test program post
		$test_post_id = wp_insert_post( [
			'post_title'   => 'Chương trình Test Quota Status',
			'post_type'    => 'program',
			'post_status'  => 'publish',
		] );

		self::assert( $test_post_id > 0, "Test Program created successfully (ID: {$test_post_id})" );

		// Test 2: Default status
		update_post_meta( $test_post_id, 'quota', 'dang-tuyen' );
		update_post_meta( $test_post_id, 'admission_status', 'tuyen-sinh' );
		self::assert( 'Đang tuyển sinh' === ltdh_get_program_quota_display( $test_post_id ), "Active program displays 'Đang tuyển sinh'" );
		self::assert( true === ltdh_is_program_quota_open( $test_post_id ), "Active program is_quota_open is true" );

		// Test 3: Stopped/closed status
		update_post_meta( $test_post_id, 'quota', 'ngung-tuyen' );
		update_post_meta( $test_post_id, 'admission_status', 'tam-ngung' );
		self::assert( 'Ngừng tuyển sinh' === ltdh_get_program_quota_display( $test_post_id ), "Stopped program displays 'Ngừng tuyển sinh'" );
		self::assert( false === ltdh_is_program_quota_open( $test_post_id ), "Stopped program is_quota_open is false" );

		// Test 4: Sync when setting quota to ngung-tuyen
		update_post_meta( $test_post_id, 'admission_status', 'tuyen-sinh' );
		update_post_meta( $test_post_id, 'quota', 'ngung-tuyen' );
		ltdh_sync_program_admission_status_batches( $test_post_id );
		$synced_status = get_post_meta( $test_post_id, 'admission_status', true );
		self::assert( 'tam-ngung' === $synced_status, "Sync hook updates admission_status to 'tam-ngung' when quota is 'ngung-tuyen'" );

		// Test 5: Sync when setting quota to dang-tuyen
		update_post_meta( $test_post_id, 'admission_status', 'tam-ngung' );
		update_post_meta( $test_post_id, 'quota', 'dang-tuyen' );
		ltdh_sync_program_admission_status_batches( $test_post_id );
		$synced_status = get_post_meta( $test_post_id, 'admission_status', true );
		self::assert( 'tuyen-sinh' === $synced_status, "Sync hook updates admission_status to 'tuyen-sinh' when quota is 'dang-tuyen'" );

		// Test 6: Check overview template syntax
		$overview_file = dirname( __DIR__ ) . '/template-parts/program/overview.php';
		$content = file_get_contents( $overview_file );
		self::assert( false !== strpos( $content, 'ltdh_get_program_quota_display' ), "template-parts/program/overview.php calls ltdh_get_program_quota_display" );
		self::assert( false !== strpos( $content, 'Ngừng tuyển sinh' ), "template-parts/program/overview.php contains 'Ngừng tuyển sinh' status branch" );
		self::assert( false !== strpos( $content, 'Đang tuyển sinh' ), "template-parts/program/overview.php contains 'Đang tuyển sinh' status branch" );
		self::assert( false === strpos( $content, "\$quota . ' chỉ tiêu'" ), "template-parts/program/overview.php no longer outputs numeric quota formula" );

		// Test 7: Check ACF field definition in JSON
		$json_file = dirname( __DIR__ ) . '/inc/acf-import-fields.json';
		$json_content = file_get_contents( $json_file );
		self::assert( false !== strpos( $json_content, '"dang-tuyen": "Đang tuyển sinh"' ), "acf-import-fields.json contains dang-tuyen choice" );
		self::assert( false !== strpos( $json_content, '"ngung-tuyen": "Ngừng tuyển sinh"' ), "acf-import-fields.json contains ngung-tuyen choice" );

		// Clean up test post
		wp_delete_post( $test_post_id, true );
		echo "\n===================================================================\n";
		echo "SUMMARY: " . self::$passed . " PASSED, " . self::$failed . " FAILED\n";
		echo "===================================================================\n";

		if ( self::$failed > 0 ) {
			exit( 1 );
		}
	}
}

TestProgramQuotaStatus::run();
