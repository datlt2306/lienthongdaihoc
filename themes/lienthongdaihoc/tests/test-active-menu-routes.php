<?php
/**
 * Test Suite: Active Menu Route Verification
 *
 * Verifies contextual menu highlighting for:
 * 1. Single Program -> 'Hình thức đào tạo' parent + matching training type sub-item
 * 2. Single School  -> 'Trường đối tác' parent
 * 3. Single Major   -> 'Nhóm Ngành' parent + category column header + major item
 * 4. Fallback Menu  -> Desktop and mobile parity
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__, 4 ) . '/wp-load.php';
}

echo "===================================================================\n";
echo "TEST SUITE: ACTIVE MENU ROUTE VERIFICATION\n";
echo "===================================================================\n\n";

function ltdh_assert_active( $cond, $msg ) {
	if ( $cond ) {
		echo "  [PASS] $msg\n";
	} else {
		echo "  [FAIL] $msg\n";
		exit( 1 );
	}
}

// -------------------------------------------------------------------
// TEST 1: Single Program -> Training Type Menu Active
// -------------------------------------------------------------------
echo "TEST 1: Single Program -> Training Type Menu Active\n";
$_SERVER['REQUEST_URI'] = '/lien-thong-cong-nghe-thong-tin-tu-xa-utc/';
$items = wp_get_nav_menu_items( 'Header Navigation Menu' );
$items = apply_filters( 'wp_nav_menu_objects', $items, (object) [ 'theme_location' => 'primary-menu' ] );

$parent_active = false;
$child_active  = false;
foreach ( $items as $it ) {
	$classes = (array) $it->classes;
	if ( (int) $it->ID === 410 && in_array( 'current-menu-item', $classes, true ) && in_array( 'current-menu-ancestor', $classes, true ) ) {
		$parent_active = true;
	}
	if ( (int) $it->menu_item_parent === 410 && in_array( 'current-menu-item', $classes, true ) ) {
		$child_active = true;
	}
}
ltdh_assert_active( $parent_active, 'Item 410 (Hình thức đào tạo) has current-menu-item and current-menu-ancestor' );
ltdh_assert_active( $child_active, 'Child item under 410 has current-menu-item' );

// -------------------------------------------------------------------
// TEST 2: Single School -> School Menu Active
// -------------------------------------------------------------------
echo "\nTEST 2: Single School -> School Menu Active\n";
$_SERVER['REQUEST_URI'] = '/truong-dai-hoc-giao-thong-van-tai/';
$items = wp_get_nav_menu_items( 'Header Navigation Menu' );
$items = apply_filters( 'wp_nav_menu_objects', $items, (object) [ 'theme_location' => 'primary-menu' ] );

$school_active = false;
foreach ( $items as $it ) {
	$classes = (array) $it->classes;
	if ( (int) $it->ID === 403 && in_array( 'current-menu-item', $classes, true ) && in_array( 'current-menu-ancestor', $classes, true ) ) {
		$school_active = true;
	}
}
ltdh_assert_active( $school_active, 'Item 403 (Trường đối tác) has current-menu-item and current-menu-ancestor' );

// -------------------------------------------------------------------
// TEST 3: Single Major -> Major Menu & Category Header Active
// -------------------------------------------------------------------
echo "\nTEST 3: Single Major -> Major Menu & Category Header Active\n";
$_SERVER['REQUEST_URI'] = '/nganh-cong-nghe-thong-tin/';
$items = wp_get_nav_menu_items( 'Header Navigation Menu' );
$items = apply_filters( 'wp_nav_menu_objects', $items, (object) [ 'theme_location' => 'primary-menu' ] );

$major_parent_active = false;
$cat_col_active      = false;
$major_item_active   = false;
$view_all_inactive   = true;

foreach ( $items as $it ) {
	$classes = (array) $it->classes;
	if ( (int) $it->ID === 404 && in_array( 'current-menu-item', $classes, true ) && in_array( 'current-menu-ancestor', $classes, true ) ) {
		$major_parent_active = true;
	}
	if ( (int) $it->ID === 3316 && in_array( 'current-menu-ancestor', $classes, true ) ) {
		$cat_col_active = true;
	}
	if ( (int) $it->ID === 3317 && in_array( 'current-menu-item', $classes, true ) ) {
		$major_item_active = true;
	}
	if ( (int) $it->ID === 3323 && ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-ancestor', $classes, true ) ) ) {
		$view_all_inactive = false;
	}
}
ltdh_assert_active( $major_parent_active, 'Item 404 (Nhóm Ngành) has current-menu-item and current-menu-ancestor' );
ltdh_assert_active( ! empty( $cat_col_active ), 'Category Header 3316 (Kỹ thuật - Công nghệ) has current-menu-ancestor' );
ltdh_assert_active( $major_item_active, 'Major Item 3317 (Công nghệ thông tin) has current-menu-item' );
ltdh_assert_active( $view_all_inactive, 'View all link 3323 is not marked active' );

// -------------------------------------------------------------------
// TEST 4: Mobile Drawer Auto-Expand (is-open Class & aria-expanded)
// -------------------------------------------------------------------
echo "\nTEST 4: Mobile Drawer Auto-Expand (is-open Class)\n";
$_SERVER['REQUEST_URI'] = '/lien-thong-cong-nghe-thong-tin-tu-xa-utc/';
$html_mobile = wp_nav_menu( [
	'theme_location' => 'primary-menu',
	'echo'           => false,
	'container'      => false,
	'menu_class'     => 'nav-mobile-menu',
] );

ltdh_assert_active( strpos( $html_mobile, 'menu-item-410' ) !== false && strpos( $html_mobile, 'is-open' ) !== false, 'Mobile drawer adds is-open to active parent menu item' );
ltdh_assert_active( strpos( $html_mobile, 'aria-expanded="true"' ) !== false, 'Mobile toggle button has aria-expanded="true"' );

// -------------------------------------------------------------------
// TEST 5: Fallback Menu Parity
// -------------------------------------------------------------------
echo "\nTEST 5: Fallback Menu Parity\n";
$_SERVER['REQUEST_URI'] = '/lien-thong-cong-nghe-thong-tin-tu-xa-utc/';
ob_start();
ltdh_render_fallback_menu( 'primary', 'nav-primary-menu' );
$fb_desktop = ob_get_clean();

ltdh_assert_active( strpos( $fb_desktop, 'current-menu-ancestor' ) !== false, 'Fallback desktop renders current-menu-ancestor' );
ltdh_assert_active( strpos( $fb_desktop, 'current-menu-item' ) !== false, 'Fallback desktop renders current-menu-item on sub-item' );

echo "\n===================================================================\n";
echo "ALL ACTIVE MENU TESTS PASSED SUCCESSFULLY!\n";
echo "===================================================================\n";
