<?php
/**
 * Test Suite: Mobile Sticky Footer & Desktop Section Requirements Refactoring
 */

$total_tests  = 0;
$passed_tests = 0;

function assert_test( $condition, $message ) {
	global $total_tests, $passed_tests;
	$total_tests++;
	if ( $condition ) {
		$passed_tests++;
		echo "  [PASS] {$message}\n";
	} else {
		echo "  [FAIL] {$message}\n";
	}
}

echo "\n===================================================================\n";
echo "TEST SUITE: Desktop Section Requirements (requirements.php)\n";
echo "===================================================================\n";

$req_file = dirname( __DIR__ ) . '/template-parts/program/requirements.php';
$req_content = file_get_contents( $req_file );

assert_test( false === strpos( $req_content, 'widgets/condition-checker' ), 'Section requirements.php KHÔNG CÒN nhúng widget condition-checker (tránh trùng lặp trên desktop)' );
assert_test( false === strpos( $req_content, 'INTERACTIVE CONDITION CHECKER WIDGET' ), 'Khối INTERACTIVE CONDITION CHECKER WIDGET đã được gỡ bỏ khỏi requirements.php' );
assert_test( false !== strpos( $req_content, 'details-dieu-kien' ), 'Accordion 1 Tiêu chuẩn xét tuyển vẫn hoạt động nguyên vẹn' );
assert_test( false !== strpos( $req_content, 'details-ho-so' ), 'Accordion 2 Hồ sơ xét tuyển vẫn hoạt động nguyên vẹn' );
assert_test( false !== strpos( $req_content, 'details-lien-he' ), 'Accordion 3 Thông tin liên hệ vẫn hoạt động nguyên vẹn' );

echo "\n===================================================================\n";
echo "TEST SUITE: Mobile Sticky Footer Bar (footer.php)\n";
echo "===================================================================\n";

$footer_file = dirname( __DIR__ ) . '/footer.php';
$footer_content = file_get_contents( $footer_file );

assert_test( false !== strpos( $footer_content, 'ltdh-global-mobile-bar' ), 'Thanh sticky footer mobile ltdh-global-mobile-bar tồn tại trong footer.php' );
assert_test( false !== strpos( $footer_content, 'is_singular' ), 'Có điều kiện rẽ nhánh is_singular cho thanh mobile bar' );
assert_test( false !== strpos( $footer_content, 'js-open-condition-checker-modal' ), 'Nút trung tâm trên trang program kích hoạt js-open-condition-checker-modal' );
assert_test( false !== strpos( $footer_content, 'Kiểm tra điều kiện ngay' ), 'Nhãn nút trung tâm trên program hiển thị rõ "Kiểm tra điều kiện ngay"' );
assert_test( false !== strpos( $footer_content, 'modal-condition-checker' ), 'footer.php đã nhúng template-parts/modal-condition-checker' );

echo "\n===================================================================\n";
echo "TEST SUITE: Modal Bottom Sheet (modal-condition-checker.php)\n";
echo "===================================================================\n";

$modal_file = dirname( __DIR__ ) . '/template-parts/modal-condition-checker.php';
assert_test( file_exists( $modal_file ), 'File template-parts/modal-condition-checker.php đã được tạo' );

$modal_content = file_get_contents( $modal_file );
assert_test( false !== strpos( $modal_content, 'id="ltdh-condition-checker-modal"' ), 'Container modal mang id ltdh-condition-checker-modal' );
assert_test( false !== strpos( $modal_content, 'id="ltdh-condition-modal-sheet"' ), 'Container sheet trượt từ dưới lên id ltdh-condition-modal-sheet' );
assert_test( false !== strpos( $modal_content, 'translate-y-full' ), 'Hỗ trợ hiệu ứng transition slide-up translate-y-full -> translate-y-0' );
assert_test( false !== strpos( $modal_content, 'widgets/condition-checker' ), 'Nhúng widget condition-checker mode compact bên trong modal' );
assert_test( false !== strpos( $modal_content, 'ltdhOpenConditionCheckerModal' ), 'Định nghĩa window.ltdhOpenConditionCheckerModal toàn cục' );
assert_test( false !== strpos( $modal_content, 'ltdhCloseConditionCheckerModal' ), 'Định nghĩa window.ltdhCloseConditionCheckerModal toàn cục' );
assert_test( false !== strpos( $modal_content, 'ltdhOpenConsultationModal' ), 'Có switch chuyển sang đăng ký nhận tư vấn nhanh qua điện thoại' );

echo "\n===================================================================\n";
echo "SUMMARY: {$passed_tests} PASSED, " . ( $total_tests - $passed_tests ) . " FAILED\n";
echo "===================================================================\n";

exit( $passed_tests === $total_tests ? 0 : 1 );
