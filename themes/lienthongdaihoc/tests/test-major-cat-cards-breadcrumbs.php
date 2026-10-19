<?php
/**
 * Test Suite: Major Category Archive, Major Card Design & Breadcrumbs
 */

require_once __DIR__ . '/../../../../wp-load.php';

echo "===================================================================" . PHP_EOL;
echo "TEST SUITE: MAJOR_CAT ARCHIVE, CARD DESIGN & BREADCRUMBS" . PHP_EOL;
echo "===================================================================" . PHP_EOL . PHP_EOL;

$pass_count = 0;
$fail_count = 0;

function assert_test( $condition, $message ) {
	global $pass_count, $fail_count;
	if ( $condition ) {
		echo "  [PASS] " . $message . PHP_EOL;
		$pass_count++;
	} else {
		echo "  [FAIL] " . $message . PHP_EOL;
		$fail_count++;
	}
}

// -------------------------------------------------------------------
// TEST 1: Template Hierarchy & Files
// -------------------------------------------------------------------
echo "TEST 1: Template Hierarchy & Files" . PHP_EOL;
$theme_dir = get_template_directory();
assert_test( file_exists( $theme_dir . '/taxonomy-major_cat.php' ), 'taxonomy-major_cat.php template exists' );
assert_test( file_exists( $theme_dir . '/template-parts/cards/card-major.php' ), 'template-parts/cards/card-major.php exists' );

// -------------------------------------------------------------------
// TEST 2: ACF Field Group for major_cat
// -------------------------------------------------------------------
echo PHP_EOL . "TEST 2: ACF Field Group for major_cat" . PHP_EOL;
$fg = acf_get_field_group( 'group_major_cat_settings' );
assert_test( ! empty( $fg ), 'ACF field group group_major_cat_settings is registered' );
assert_test( isset( $fg['location'][0][0]['param'] ) && $fg['location'][0][0]['param'] === 'taxonomy', 'Field group targets taxonomy' );
assert_test( isset( $fg['location'][0][0]['value'] ) && $fg['location'][0][0]['value'] === 'major_cat', 'Field group targets major_cat taxonomy' );
$fields = acf_get_fields( 'group_major_cat_settings' );
$found_thumb_field = false;
if ( ! empty( $fields ) ) {
	foreach ( $fields as $f ) {
		if ( $f['name'] === 'category_thumbnail' && $f['type'] === 'image' ) {
			$found_thumb_field = true;
			break;
		}
	}
}
assert_test( $found_thumb_field, 'ACF field category_thumbnail (image) is present' );

// -------------------------------------------------------------------
// TEST 3: Card Major Output Verification (Image #2 Specification)
// -------------------------------------------------------------------
echo PHP_EOL . "TEST 3: Card Major Output Verification (Image #2 Specification)" . PHP_EOL;

// Test on Logistics (ID 1692)
$logistics = get_post( 1692 );
assert_test( ! empty( $logistics ), 'Logistics post exists' );

global $post;
$post = $logistics;
setup_postdata( $post );

ob_start();
get_template_part( 'template-parts/cards/card-major' );
$card_html = ob_get_clean();
wp_reset_postdata();

// Strict absence of unneeded elements
assert_test( false === strpos( $card_html, 'Đại học đối tác' ), 'Card does NOT contain "Đại học đối tác"' );
assert_test( false === strpos( $card_html, 'Tổ hợp:' ), 'Card does NOT contain "Tổ hợp:"' );
assert_test( false === strpos( $card_html, 'So sánh' ), 'Card does NOT contain "So sánh"' );
assert_test( false === strpos( $card_html, 'Đăng ký học' ), 'Card does NOT contain "Đăng ký học"' );
assert_test( false === strpos( $card_html, 'trường tuyển sinh' ), 'Card does NOT contain "trường tuyển sinh" overlay badge' );

// Presence of Image #2 elements
assert_test( false !== strpos( $card_html, 'Xã hội - Dịch vụ' ), 'Card contains category badge: "Xã hội - Dịch vụ"' );
assert_test( false !== strpos( $card_html, 'Ngành Logistics' ), 'Card contains formatted title: "Ngành Logistics"' );
assert_test( false !== strpos( $card_html, 'Khám phá ngành học' ), 'Card contains CTA: "Khám phá ngành học"' );
assert_test( false !== strpos( $card_html, 'nganh-logistics.webp' ), 'Card uses uploaded featured image: nganh-logistics.webp' );

// -------------------------------------------------------------------
// TEST 4: Breadcrumb Hierarchy (major_cat & major)
// -------------------------------------------------------------------
echo PHP_EOL . "TEST 4: Breadcrumb Hierarchy" . PHP_EOL;

// 4.1 major_cat: Trang chủ > Kinh tế - Quản lý
$_SERVER['REQUEST_URI'] = '/nhom-nganh/kinh-te-quan-ly/';
wp();

$crumbs_cat = apply_filters( 'rank_math/frontend/breadcrumb/items', [], null );
assert_test( count( $crumbs_cat ) === 2, 'major_cat breadcrumbs have exactly 2 items' );
assert_test( $crumbs_cat[0][0] === 'Trang chủ', 'Crumb 1 is "Trang chủ"' );
assert_test( $crumbs_cat[1][0] === 'Kinh tế - Quản lý' && empty( $crumbs_cat[1][1] ), 'Crumb 2 is "Kinh tế - Quản lý" (active last crumb)' );

// 4.2 major single: Trang chủ > Kinh tế - Quản lý > Luật kinh tế
$_SERVER['REQUEST_URI'] = '/nganh-luat-kinh-te/';
wp();

$crumbs_major = apply_filters( 'rank_math/frontend/breadcrumb/items', [], null );
assert_test( count( $crumbs_major ) === 3, 'major single breadcrumbs have exactly 3 items' );
assert_test( $crumbs_major[0][0] === 'Trang chủ', 'Crumb 1 is "Trang chủ"' );
assert_test( $crumbs_major[1][0] === 'Kinh tế - Quản lý' && false !== strpos( $crumbs_major[1][1], '/nhom-nganh/kinh-te-quan-ly/' ), 'Crumb 2 is "Kinh tế - Quản lý" linking to /nhom-nganh/kinh-te-quan-ly/' );
assert_test( $crumbs_major[2][0] === 'Luật kinh tế' && empty( $crumbs_major[2][1] ), 'Crumb 3 is "Luật kinh tế" (active last crumb)' );

echo PHP_EOL . "===================================================================" . PHP_EOL;
echo "RESULTS: " . $pass_count . " PASSED, " . $fail_count . " FAILED" . PHP_EOL;
echo "===================================================================" . PHP_EOL;

if ( $fail_count > 0 ) {
	exit( 1 );
}
exit( 0 );
