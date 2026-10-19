<?php
/**
 * Test Suite: Mega Menu for Majors & Major Categories
 * Tests dynamic injection, grouping, walker formatting, and fallback rendering.
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

require_once __DIR__ . '/../inc/config/constants.php';

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public $errors = [];
		public function __construct( $code = '', $message = '' ) {
			if ( $code ) {
				$this->errors[ $code ] = [ $message ];
			}
		}
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
}

if ( ! function_exists( 'untrailingslashit' ) ) {
	function untrailingslashit( $string ) {
		return rtrim( (string) $string, '/\\' );
	}
}

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $string ) {
		return rtrim( (string) $string, '/\\' ) . '/';
	}
}

if ( ! function_exists( 'wp_parse_url' ) ) {
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( $url, $component );
	}
}

if ( ! function_exists( 'get_template_directory_uri' ) ) {
	function get_template_directory_uri() {
		return 'http://example.com/wp-content/themes/lienthongdaihoc';
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}

if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		return 'http://example.com' . $path;
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'get_terms' ) ) {
	function get_terms( $args = [] ) {
		if ( isset( $GLOBALS['mock_terms_return'] ) ) {
			return $GLOBALS['mock_terms_return'];
		}
		if ( isset( $args['taxonomy'] ) && $args['taxonomy'] === 'major_cat' ) {
			return [
				(object) [
					'term_id' => 10,
					'name'    => 'Kinh tế & Quản lý',
					'slug'    => 'kinh-te-quan-ly',
				],
				(object) [
					'term_id' => 20,
					'name'    => 'Kỹ thuật & Công nghệ',
					'slug'    => 'ky-thuat-cong-nghe',
				],
			];
		}
		return [];
	}
}

if ( ! function_exists( 'get_term_link' ) ) {
	function get_term_link( $term, $taxonomy = '' ) {
		$slug = is_object( $term ) ? $term->slug : $term;
		return home_url( '/nhom-nganh/' . $slug . '/' );
	}
}

if ( ! function_exists( 'get_page_by_path' ) ) {
	function get_page_by_path( $slug, $output = OBJECT, $post_type = 'page' ) {
		if ( $slug === 'cong-nghe-thong-tin' ) {
			return (object) [
				'ID'          => 201,
				'post_title'  => 'Công nghệ thông tin',
				'post_name'   => 'cong-nghe-thong-tin',
				'post_status' => 'publish',
			];
		}
		return null;
	}
}

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $class, $fallback = '' ) {
		$sanitized = preg_replace( '|[^a-zA-Z0-9_-]|', '', $class );
		return $sanitized ?: $fallback;
	}
}

if ( ! function_exists( 'get_posts' ) ) {
	function get_posts( $args = [] ) {
		if ( isset( $GLOBALS['mock_posts_return'] ) ) {
			return $GLOBALS['mock_posts_return'];
		}
		$tax_query = $args['tax_query'] ?? [];
		$terms = $tax_query[0]['terms'] ?? [];

		if ( in_array( 10, (array) $terms, true ) ) {
			return [
				(object) [
					'ID'         => 101,
					'post_title' => 'Quản trị kinh doanh',
					'post_name'  => 'quan-tri-kinh-doanh',
				],
				(object) [
					'ID'         => 102,
					'post_title' => 'Kế toán',
					'post_name'  => 'ke-toan',
				],
			];
		}

		if ( in_array( 20, (array) $terms, true ) ) {
			return [
				(object) [
					'ID'         => 201,
					'post_title' => 'Công nghệ thông tin',
					'post_name'  => 'cong-nghe-thong-tin',
				],
			];
		}

		return [];
	}
}

if ( ! function_exists( 'get_permalink' ) ) {
	function get_permalink( $id = 0 ) {
		return home_url( '/nganh/' . $id . '/' );
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( $id, $key, $single = true ) {
		if ( $key === '_is_hot' && $id === 201 ) {
			return '1';
		}
		return '';
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $key ) {
		return false; // Force recompute for testing
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( $key, $val, $exp ) {
		return true;
	}
}

// Load class-defaults.php and class-menus.php
require_once __DIR__ . '/../inc/config/class-defaults.php';
require_once __DIR__ . '/../inc/core/class-menus.php';

$all_passed = true;
function test_assert( $cond, $name ) {
	global $all_passed;
	if ( $cond ) {
		echo "  [PASS] {$name}\n";
	} else {
		echo "  [FAIL] {$name}\n";
		$all_passed = false;
	}
}

echo "===================================================================\n";
echo "TEST SUITE: MEGA MENU VERIFICATION\n";
echo "===================================================================\n";

// Test 1: ltdh_get_mega_menu_data
echo "\nTEST 1: Mega Menu Data Extraction\n";
$mega_data = ltdh_get_mega_menu_data();
test_assert( is_array( $mega_data ) && count( $mega_data ) === 2, 'Returns 2 categories of majors' );
$cats = array_values( $mega_data );
test_assert( isset( $cats[0]['majors'] ) && count( $cats[0]['majors'] ) === 2, 'Category 1 has 2 majors' );
test_assert( isset( $cats[1]['majors'] ) && count( $cats[1]['majors'] ) === 1, 'Category 2 has 1 major' );
test_assert( isset( $cats[1]['majors'][0]['slug'] ) && $cats[1]['majors'][0]['slug'] === 'cong-nghe-thong-tin', 'IT major slug is correct' );

// Test 2: Icon retrieval
echo "\nTEST 2: Category Icon Retrieval\n";
$icon_kt = ltdh_get_major_cat_icon( 'kinh-te-quan-ly' );
$icon_cntt = ltdh_get_major_cat_icon( 'ky-thuat-cong-nghe' );
test_assert( strpos( $icon_kt, '<svg' ) !== false, 'Kinh te icon returns valid SVG markup' );
test_assert( strpos( $icon_cntt, '<svg' ) !== false, 'Ky thuat icon returns valid SVG markup' );

// Test 3: Submenu injection for "Chuyên ngành"
echo "\nTEST 3: Dynamic Menu Injection for 'Chuyên ngành'\n";
$mock_nav_items = [
	(object) [
		'ID'               => 1,
		'title'            => 'Trang chủ',
		'url'              => home_url( '/' ),
		'menu_item_parent' => 0,
		'classes'          => [],
	],
	(object) [
		'ID'               => 2,
		'title'            => 'Chuyên ngành',
		'url'              => home_url( '/nganh-hoc/' ),
		'menu_item_parent' => 0,
		'classes'          => [],
	],
];

$injected = ltdh_dynamic_menu_submenu_injection( $mock_nav_items, (object) [ 'theme_location' => 'primary-menu' ] );

// Original: 2 items.
// Injected:
// - Item 2 gets class 'menu-item-mega-menu'
// - Depth 1 Category 1: ID > 1000
// - Depth 2 Major 101 (child of Cat 1)
// - Depth 2 Major 102 (child of Cat 1)
// - Depth 1 Category 2: ID > 1000
// - Depth 2 Major 201 (child of Cat 2)
// - Depth 1 "Xem tất cả ngành →"
// Total: 2 + 2 cats + 3 majors + 1 view-all = 8 items
test_assert( count( $injected ) === 8, 'Total injected items count is exactly 8 (2 roots + 2 cats + 3 majors + 1 cta)' );
test_assert( in_array( 'menu-item-mega-menu', $injected[1]->classes, true ), 'Parent "Chuyên ngành" has "menu-item-mega-menu" class' );

// Check parenting
$cat1_id = $injected[2]->ID;
test_assert( $injected[2]->menu_item_parent === 2, 'Category 1 has parent ID 2 (Chuyên ngành)' );
test_assert( $injected[3]->menu_item_parent === $cat1_id, 'Major 101 has parent Category 1' );
test_assert( $injected[4]->menu_item_parent === $cat1_id, 'Major 102 has parent Category 1' );

$cat2_id = $injected[5]->ID;
test_assert( $injected[5]->menu_item_parent === 2, 'Category 2 has parent ID 2 (Chuyên ngành)' );
test_assert( $injected[6]->menu_item_parent === $cat2_id, 'Major 201 has parent Category 2' );

$view_all = $injected[7];
test_assert( $view_all->menu_item_parent === 2, 'View all link has parent ID 2' );
test_assert( in_array( 'ltdh-view-all-link', $view_all->classes, true ), 'View all link has "ltdh-view-all-link" class' );

// Test 4: Nav menu start_el filter formatting
echo "\nTEST 4: Start Element HTML Enrichment (walker_nav_menu_start_el)\n";
$cat_item = $injected[2];
$cat_html = ltdh_mega_menu_nav_menu_start_el(
	'<a href="' . $cat_item->url . '">' . $cat_item->title . '</a>',
	$cat_item,
	1,
	(object) [ 'theme_location' => 'primary-menu' ]
);
test_assert( strpos( $cat_html, 'class="mega-cat-header-link"' ) !== false, 'Category link gets mega-cat-header-link' );
test_assert( strpos( $cat_html, 'mega-cat-icon' ) !== false, 'Category link contains mega-cat-icon' );
test_assert( strpos( $cat_html, 'mega-cat-badge' ) !== false, 'Category link contains major count badge' );

$major_item = $injected[6]; // IT major (is_hot)
$major_html = ltdh_mega_menu_nav_menu_start_el(
	'<a href="' . $major_item->url . '">' . $major_item->title . '</a>',
	$major_item,
	2,
	(object) [ 'theme_location' => 'primary-menu' ]
);
test_assert( strpos( $major_html, 'class="mega-major-link"' ) !== false, 'Major link gets mega-major-link' );

// Test 5: Fallback Menu Rendering
echo "\nTEST 5: Fallback Mega Menu Output\n";
ob_start();
ltdh_render_fallback_menu( 'primary', 'nav-primary-menu' );
$fallback_desktop = ob_get_clean();

test_assert( strpos( $fallback_desktop, 'menu-item-mega-menu' ) !== false, 'Fallback desktop renders menu-item-mega-menu' );
test_assert( strpos( $fallback_desktop, 'mega-cat-header-link' ) !== false, 'Fallback desktop renders category headers' );
test_assert( strpos( $fallback_desktop, 'mega-footer-bar' ) !== false, 'Fallback desktop renders mega-footer-bar' );

ob_start();
ltdh_render_fallback_menu( 'mobile', 'nav-mobile-menu' );
$fallback_mobile = ob_get_clean();
test_assert( strpos( $fallback_mobile, 'mobile-submenu-toggle' ) !== false, 'Fallback mobile renders accordion toggle buttons' );

// Test 6: Admin-managed Mode (When user saves children in wp-admin)
echo "\nTEST 6: Admin-managed Mode in Dynamic Injection\n";
$admin_saved_items = [
	(object) [
		'ID'               => 10,
		'title'            => 'Chuyên ngành',
		'url'              => home_url( '/nganh-hoc/' ),
		'menu_item_parent' => 0,
		'classes'          => [],
	],
	(object) [
		'ID'               => 11,
		'title'            => 'Khối ngành Kinh tế Đặc biệt',
		'url'              => home_url( '/nhom-nganh/kinh-te/' ),
		'menu_item_parent' => 10,
		'classes'          => [],
		'object'           => 'custom',
		'object_id'        => 0,
	],
	(object) [
		'ID'               => 12,
		'title'            => 'Ngành Quản lý Doanh nghiệp',
		'url'              => home_url( '/nganh-quan-ly/' ),
		'menu_item_parent' => 11,
		'classes'          => [],
		'object'           => 'custom',
		'object_id'        => 0,
	],
	(object) [
		'ID'               => 13,
		'title'            => 'Khám phá tất cả các ngành →',
		'url'              => home_url( '/nganh-hoc/' ),
		'menu_item_parent' => 10,
		'classes'          => [],
		'object'           => 'custom',
		'object_id'        => 0,
	],
];

$admin_res = ltdh_dynamic_menu_submenu_injection( $admin_saved_items, (object) [ 'theme_location' => 'primary-menu' ] );
test_assert( count( $admin_res ) === 4, 'Admin-saved items are NOT wiped (exactly 4 items preserved)' );
test_assert( in_array( 'menu-item-mega-menu', $admin_res[0]->classes, true ), 'Parent item 10 gets menu-item-mega-menu' );
test_assert( in_array( 'menu-item-category', $admin_res[1]->classes, true ), 'Custom category 11 gets menu-item-category' );
test_assert( in_array( 'menu-item-object-major', $admin_res[2]->classes, true ), 'Custom sub-major 12 gets menu-item-object-major' );
test_assert( in_array( 'ltdh-view-all-link', $admin_res[3]->classes, true ), 'Custom CTA 13 gets ltdh-view-all-link' );

echo "\n===================================================================\n";
if ( $all_passed ) {
	echo "ALL MEGA MENU TESTS PASSED!\n";
	exit( 0 );
} else {
	echo "SOME TESTS FAILED!\n";
	exit( 1 );
}
