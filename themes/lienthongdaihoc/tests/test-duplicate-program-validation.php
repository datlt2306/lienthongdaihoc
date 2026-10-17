<?php
/**
 * Test Suite: Duplicate Program Title Validation
 *
 * Usage: wp eval-file tests/test-duplicate-program-validation.php
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Direct execution not allowed.\n" );
}

echo "===================================================================\n";
echo "TEST SUITE: DUPLICATE PROGRAM TITLE VALIDATION\n";
echo "===================================================================\n\n";

global $pass_count, $fail_count;
$pass_count = 0;
$fail_count = 0;

function assert_test( bool $condition, string $description, string $details = '' ): void {
	global $pass_count, $fail_count;
	if ( $condition ) {
		$pass_count++;
		echo "  [PASS] " . $description . "\n";
	} else {
		$fail_count++;
		echo "  [FAIL] " . $description . "\n";
		if ( $details ) {
			echo "         Details: " . $details . "\n";
		}
	}
}

// 1. Kiểm tra hàm ltdh_is_program_title_duplicate tồn tại
assert_test(
	function_exists( 'ltdh_is_program_title_duplicate' ),
	"Hàm ltdh_is_program_title_duplicate() đã được định nghĩa"
);

// 2. Tạo một chương trình mẫu với tên duy nhất để test
$unique_title = 'Chương trình Thử nghiệm Kiểm tra Trùng ' . time() . '_' . wp_rand( 1000, 9999 );

$first_post_id = wp_insert_post( [
	'post_title'   => $unique_title,
	'post_type'    => 'program',
	'post_status'  => 'publish',
	'post_content' => 'Nội dung test',
] );

assert_test(
	$first_post_id && ! is_wp_error( $first_post_id ),
	"Tạo bài viết chương trình gốc thành công (ID: #{$first_post_id})"
);

// 3. Test: Kiểm tra với tên hoàn toàn mới -> Phải trả về false
$new_title = 'Chương trình Hoàn Toàn Chưa Tồn Tại ' . ( time() + 100 );
$is_dup = ltdh_is_program_title_duplicate( $new_title );
assert_test(
	false === $is_dup,
	"Tên mới chưa tồn tại trả về false (Không trùng lặp)"
);

// 4. Test: Kiểm tra với tên giống hệt chương trình gốc -> Phải phát hiện trùng và trả về ID gốc
$dup_id = ltdh_is_program_title_duplicate( $unique_title );
assert_test(
	$dup_id === (int) $first_post_id,
	"Phát hiện trùng lặp chính xác với tên giống hệt",
	"Expected: {$first_post_id}, Got: " . var_export( $dup_id, true )
);

// 5. Test: Kiểm tra không phân biệt chữ hoa, chữ thường (Case-insensitive)
$lower_title = mb_strtolower( $unique_title );
$dup_lower_id = ltdh_is_program_title_duplicate( $lower_title );
assert_test(
	$dup_lower_id === (int) $first_post_id,
	"Phát hiện trùng lặp khi viết thường (case-insensitive: '{$lower_title}')",
	"Expected: {$first_post_id}, Got: " . var_export( $dup_lower_id, true )
);

// 6. Test: Kiểm tra khoảng trắng thừa đầu cuối và ở giữa (Whitespace trim/normalize)
$spaced_title = '   ' . $unique_title . '   ';
$dup_spaced_id = ltdh_is_program_title_duplicate( $spaced_title );
assert_test(
	$dup_spaced_id === (int) $first_post_id,
	"Phát hiện trùng lặp khi có khoảng trắng đầu/cuối",
	"Expected: {$first_post_id}, Got: " . var_export( $dup_spaced_id, true )
);

// 7. Test: Loại trừ chính bài viết khi cập nhật (exclude_post_id)
$dup_self = ltdh_is_program_title_duplicate( $unique_title, $first_post_id );
assert_test(
	false === $dup_self,
	"Khi cập nhật chính nó (truyền exclude_post_id = {$first_post_id}), không bị coi là trùng lặp"
);

// 8. Test: Kiểm tra với bài viết khác (exclude_post_id khác) -> Vẫn phải phát hiện trùng
$dup_other = ltdh_is_program_title_duplicate( $unique_title, 99999999 );
assert_test(
	$dup_other === (int) $first_post_id,
	"Khi tạo mới hoặc sửa bài viết khác có cùng tên, phát hiện trùng với bài viết gốc"
);

// 9. Test: Tạo bài viết nháp (draft) trùng tên -> Cũng phải phát hiện trùng
$draft_title = 'Chương trình Nháp Test ' . time();
$draft_id = wp_insert_post( [
	'post_title'   => $draft_title,
	'post_type'    => 'program',
	'post_status'  => 'draft',
	'post_content' => 'Nội dung nháp',
] );
$dup_draft_id = ltdh_is_program_title_duplicate( $draft_title );
assert_test(
	$dup_draft_id === (int) $draft_id,
	"Phát hiện trùng lặp ngay cả khi bài viết ở trạng thái Bản nháp (draft)"
);

// 10. Test: Khi bài viết bị đưa vào thùng rác (trash) -> Không chặn tạo mới
wp_trash_post( $draft_id );
$dup_after_trash = ltdh_is_program_title_duplicate( $draft_title );
assert_test(
	false === $dup_after_trash,
	"Bài viết trong Thùng rác (trash) không ngăn cản tạo chương trình mới cùng tên"
);

// 11. Test: Mô phỏng ACF Validation khi submit bài viết trùng tên
$_POST['post_type']   = 'program';
$_POST['post_title']  = $unique_title;
$_POST['post_ID']     = 0; // Bài viết mới
$_POST['post_status'] = 'draft'; // Lưu nháp

// Reset ACF errors nếu hàm tồn tại
if ( function_exists( 'acf_reset_validation_errors' ) ) {
	acf_reset_validation_errors();
}

ltdh_validate_program_acf_publish_fields();
$acf_errors = function_exists( 'acf_get_validation_errors' ) ? acf_get_validation_errors() : [];

$found_error = false;
if ( ! empty( $acf_errors ) ) {
	foreach ( $acf_errors as $err ) {
		if ( false !== strpos( $err['message'], 'đã tồn tại trên hệ thống' ) ) {
			$found_error = true;
			break;
		}
	}
}

assert_test(
	$found_error,
	"ACF validation chặn lưu và ném thông báo lỗi trùng tên khi tạo chương trình"
);

// Cleanup bài viết test
wp_delete_post( $first_post_id, true );
wp_delete_post( $draft_id, true );

echo "\n===================================================================\n";
echo sprintf( "SUMMARY: %d PASSED, %d FAILED\n", $pass_count, $fail_count );
echo "===================================================================\n";

if ( $fail_count > 0 ) {
	exit( 1 );
}
exit( 0 );
