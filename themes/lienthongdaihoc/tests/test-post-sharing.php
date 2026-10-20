<?php
/**
 * Test Suite: Post Social Sharing & Clipboard Functionality
 */

require_once __DIR__ . '/../../../../wp-load.php';

echo "==================================================\n";
echo "STARTING TEST: POST SOCIAL SHARING & CLIPBOARD\n";
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
$template_file = get_template_directory() . '/template-parts/post-share.php';
assert_test( file_exists( $template_file ), 'Template part template-parts/post-share.php exists' );

// 2. Fetch or create a test post
$posts = get_posts( [ 'numberposts' => 1, 'post_type' => 'post' ] );
if ( empty( $posts ) ) {
	$test_post_id = wp_insert_post( [
		'post_title'   => 'Test Bài Viết Chia Sẻ',
		'post_content' => 'Nội dung bài viết để kiểm tra chia sẻ.',
		'post_status'  => 'publish',
		'post_type'    => 'post',
	] );
} else {
	$test_post_id = $posts[0]->ID;
}

// Set global post to test post
global $post;
$post = get_post( $test_post_id );
setup_postdata( $post );

// 3. Render template and capture output
ob_start();
get_template_part( 'template-parts/post-share' );
$html = ob_get_clean();

// 4. Test Zalo URL format (Zalo share removed per user requirement)
assert_test( strpos( $html, 'zalo.me/share' ) === false, 'Zalo share button is removed from post-share template' );

// 5. Test Facebook URL format
assert_test( strpos( $html, 'https://www.facebook.com/sharer/sharer.php?u=' ) !== false, 'Facebook URL uses official sharer endpoint' );

// 6. Test Copy Button & Data Attributes
assert_test( strpos( $html, 'id="ltdh-copy-post-btn"' ) !== false, 'Copy button exists with id="ltdh-copy-post-btn"' );
assert_test( strpos( $html, 'data-url=' ) !== false, 'Copy button has data-url attribute' );
assert_test( strpos( $html, 'ltdhCopyPostLink' ) !== false, 'Copy button has inline safety handler ltdhCopyPostLink' );

// 7. Test Toast Container
assert_test( strpos( $html, 'id="ltdh-share-toast"' ) !== false, 'Toast container exists with id="ltdh-share-toast"' );

// 8. Test Native Share Button
assert_test( strpos( $html, 'id="ltdh-native-share-btn"' ) !== false, 'Native share button exists with id="ltdh-native-share-btn"' );

// 9. Test single.php contains template part call
$single_content = file_get_contents( get_template_directory() . '/single.php' );
assert_test( strpos( $single_content, "get_template_part( 'template-parts/post-share' )" ) !== false, 'single.php includes post-share template part' );
assert_test( strpos( $single_content, 'zalo.me/share/url' ) === false, 'single.php no longer has dead hardcoded Zalo URL' );

// 10. Test single-guide.php contains template part call
$guide_content = file_get_contents( get_template_directory() . '/single-guide.php' );
assert_test( strpos( $guide_content, "get_template_part( 'template-parts/post-share' )" ) !== false, 'single-guide.php includes post-share template part' );

// 11. Test main.js has all required utilities
$main_js = file_get_contents( get_template_directory() . '/assets/js/main.js' );
assert_test( strpos( $main_js, 'window.ltdhCopyToClipboard' ) !== false, 'main.js defines window.ltdhCopyToClipboard' );
assert_test( strpos( $main_js, 'window.ltdhOpenSharePopup' ) !== false, 'main.js defines window.ltdhOpenSharePopup' );
assert_test( strpos( $main_js, 'window.ltdhCopyPostLink' ) !== false, 'main.js defines window.ltdhCopyPostLink' );
assert_test( strpos( $main_js, 'fallbackExecCopy' ) !== false, 'main.js implements document.execCommand copy fallback for insecure contexts' );
assert_test( strpos( $main_js, 'initPostSharing' ) !== false, 'main.js initializes post sharing module' );

echo "\n==================================================\n";
echo "SUMMARY: {$pass_count} PASSED, {$fail_count} FAILED\n";
echo "==================================================\n";

if ( $fail_count > 0 ) {
	exit( 1 );
}
exit( 0 );
