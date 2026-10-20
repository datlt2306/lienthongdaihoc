<?php
/**
 * Test Suite: Program Social & Direct Sharing Functionality
 */

require_once __DIR__ . '/../../../../wp-load.php';

echo "==================================================\n";
echo "STARTING TEST: PROGRAM SOCIAL SHARING & CLIPBOARD\n";
echo "==================================================\n\n";

$pass_count = 0;
$fail_count = 0;

function assert_test( $condition, $message ) {
	global $pass_count, $fail_count;
	if ( $condition ) {
		echo "✅ SUCCESS: $message\n";
		$pass_count++;
	} else {
		echo "❌ FAILED: $message\n";
		$fail_count++;
	}
}

// 1. Check template file exists
$template_file = get_template_directory() . '/template-parts/program-share.php';
assert_test( file_exists( $template_file ), 'Template part template-parts/program-share.php exists' );

// 2. Fetch or create a test program post
$programs = get_posts( [ 'numberposts' => 1, 'post_type' => 'program' ] );
if ( empty( $programs ) ) {
	$test_program_id = wp_insert_post( [
		'post_title'   => 'Liên thông Đại học Ngành Kỹ thuật Phần mềm',
		'post_content' => 'Nội dung chi tiết chương trình tuyển sinh liên thông.',
		'post_status'  => 'publish',
		'post_type'    => 'program',
	] );
} else {
	$test_program_id = $programs[0]->ID;
}

// Set global post to test program post
global $post;
$post = get_post( $test_program_id );
setup_postdata( $post );

// 3. Render template and capture output
ob_start();
get_template_part( 'template-parts/program-share', null, [ 'program_id' => $test_program_id ] );
$html = ob_get_clean();

// 4. Test heading and contextual labels
assert_test( strpos( $html, 'Chia sẻ chương trình đào tạo' ) !== false, 'Share component has correct program heading' );
assert_test( strpos( $html, 'ltdh-program-share-section' ) !== false, 'Section has class ltdh-program-share-section' );

// 5. Test Facebook URL format
assert_test( strpos( $html, 'https://www.facebook.com/sharer/sharer.php?u=' ) !== false, 'Facebook URL uses official sharer endpoint' );

// 6. Test Copy Button & Data Attributes
assert_test( strpos( $html, 'id="ltdh-copy-program-btn"' ) !== false, 'Copy button exists with id="ltdh-copy-program-btn"' );
assert_test( strpos( $html, 'data-url=' ) !== false, 'Copy button has data-url attribute' );
assert_test( strpos( $html, 'data-type="program"' ) !== false, 'Copy button has data-type="program" attribute' );
assert_test( strpos( $html, 'ltdhCopyProgramLink' ) !== false, 'Copy button has inline safety handler ltdhCopyProgramLink' );

// 7. Test Toast Container
assert_test( strpos( $html, 'id="ltdh-share-toast"' ) !== false, 'Toast container exists with id="ltdh-share-toast"' );

// 8. Test Native Share Button
assert_test( strpos( $html, 'id="ltdh-native-program-share-btn"' ) !== false, 'Native share button exists with id="ltdh-native-program-share-btn"' );
assert_test( strpos( $html, 'data-title=' ) !== false, 'Native share button has data-title attribute' );

// 9. Test single-program.php contains template part call
$single_program_content = file_get_contents( get_template_directory() . '/single-program.php' );
assert_test( strpos( $single_program_content, "get_template_part( 'template-parts/program-share'" ) !== false, 'single-program.php includes program-share template part' );
assert_test( strpos( $single_program_content, 'ltdh-quick-share-btn' ) !== false, 'single-program.php includes sticky nav quick share button' );

// 10. Test inc/core/class-widgets.php contains sidebar program share button
$widgets_content = file_get_contents( get_template_directory() . '/inc/core/class-widgets.php' );
assert_test( strpos( $widgets_content, 'ltdh-program-sidebar-share-btn' ) !== false, 'class-widgets.php includes program sidebar share button' );

// 11. Test archive-program.php contains card share button
$archive_program_content = file_get_contents( get_template_directory() . '/archive-program.php' );
assert_test( strpos( $archive_program_content, 'ltdh-card-share-btn' ) !== false, 'archive-program.php includes card share button' );

// 12. Test main.js has all required utilities and bindings for programs
$main_js = file_get_contents( get_template_directory() . '/assets/js/main.js' );
assert_test( strpos( $main_js, 'window.ltdhCopyProgramLink' ) !== false, 'main.js defines window.ltdhCopyProgramLink' );
assert_test( strpos( $main_js, 'ltdh-copy-program-btn' ) !== false, 'main.js binds ltdh-copy-program-btn' );
assert_test( strpos( $main_js, 'ltdh-native-program-share-btn' ) !== false, 'main.js binds ltdh-native-program-share-btn' );
assert_test( strpos( $main_js, 'ltdh-quick-share-btn' ) !== false, 'main.js handles sticky quick share button' );
assert_test( strpos( $main_js, 'ltdh-program-sidebar-share-btn' ) !== false, 'main.js handles sidebar share button' );
assert_test( strpos( $main_js, 'ltdh-card-share-btn' ) !== false, 'main.js handles card share button' );
assert_test( strpos( $main_js, 'Đã sao chép liên kết chương trình vào bộ nhớ tạm' ) !== false, 'main.js includes contextual program toast copy text' );

echo "\n==================================================\n";
echo "SUMMARY: {$pass_count} PASSED, {$fail_count} FAILED\n";
echo "==================================================\n";

if ( $fail_count > 0 ) {
	exit( 1 );
}
exit( 0 );
