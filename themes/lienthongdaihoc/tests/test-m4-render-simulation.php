<?php
/**
 * Direct Runtime Rendering Simulation of front-page.php
 * Challenger: challenger_m4_2
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

require_once dirname( __DIR__ ) . '/inc/config/class-defaults.php';

// Setup mocks
function home_url( $path = '' ) {
	return 'https://lienthongdaihoc.com' . ( $path ? ( '/' . ltrim( $path, '/' ) ) : '' );
}
function untrailingslashit( $string ) {
	return rtrim( $string, '/\\' );
}
function trailingslashit( $string ) {
	return untrailingslashit( $string ) . '/';
}
function get_template_directory_uri() {
	return 'https://lienthongdaihoc.com/wp-content/themes/lienthongdaihoc';
}
function get_template_directory() {
	return dirname( __DIR__ );
}
function esc_url( $url ) {
	return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
}
function esc_html( $text ) {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}
function esc_attr( $text ) {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}
function esc_html__( $text, $domain = 'default' ) {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}
function esc_attr__( $text, $domain = 'default' ) {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}
function get_field( $selector, $post_id = false ) {
	return false; // Test default fallbacks
}
function ltdh_get_hotline() {
	return '0912345678';
}
function ltdh_get_zalo_url() {
	return 'https://zalo.me/0912345678';
}
function ltdh_get_fallback_image( $type = '' ) {
	return 'https://lienthongdaihoc.com/assets/images/default-' . $type . '.jpg';
}
function ltdh_get_optimized_image_url( $url, $is_mobile = false ) {
	return $url;
}
function ltdh_get_cached_featured_schools() {
	return [];
}
class MockQuery {
	public function have_posts() { return false; }
	public function the_post() {}
}
function ltdh_get_cached_query( $key, $args, $ttl ) {
	return new MockQuery();
}
function ltdh_get_cached_filter_options() {
	return [
		'schools' => [
			[ 'slug' => 'neu', 'title' => 'Đại học Kinh tế Quốc dân' ],
		],
		'majors' => [
			[ 'slug' => 'cntt', 'title' => 'Công nghệ thông tin' ],
		],
		'types' => [
			[ 'slug' => 'tu-xa', 'name' => 'Từ xa' ],
			[ 'slug' => 'vua-hoc-vua-lam', 'name' => 'Vừa học vừa làm' ],
		],
	];
}
function ltdh_get_hot_majors() {
	return [];
}
function ltdh_render_consultation_form( $hidden = [] ) {
	echo '<div class="rendered-consultation-form">Consultation Form Rendered</div>';
}
function get_header() {
	echo "<!-- MOCK HEADER -->\n";
}
function get_footer() {
	echo "<!-- MOCK FOOTER -->\n";
}
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

echo "Executing runtime render of front-page.php...\n";

ob_start();
require dirname( __DIR__ ) . '/front-page.php';
$rendered_html = ob_get_clean();

echo "Render complete! Output length: " . strlen( $rendered_html ) . " bytes.\n\n";

$tests = [];

// 1. Hidden H1
$tests['Hidden H1 exact match'] = strpos( $rendered_html, '<h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hình Thức Từ Xa & Vừa Học Vừa Làm</h1>' ) !== false;

// 2. Search form action
$tests['Search form action is /he-dao-tao/'] = strpos( $rendered_html, '<form action="https://lienthongdaihoc.com/he-dao-tao/" method="GET"' ) !== false;

// 3. Search form reset link
$tests['Search form reset link is /he-dao-tao/'] = strpos( $rendered_html, 'href="https://lienthongdaihoc.com/he-dao-tao/"' ) !== false;

// 4. Dropdown default select
$tests['Default select label is -- Chọn hình thức học --'] = strpos( $rendered_html, '<option value="">-- Chọn hình thức học --</option>' ) !== false;

// 5. Zero THPT in rendered homepage
$tests['Zero THPT in rendered HTML'] = ( stripos( $rendered_html, 'THPT' ) === false && stripos( $rendered_html, 'học sinh tốt nghiệp' ) === false );

// 6. Testimonial fallback role is Liên thông CNTT
$tests['Testimonial fallback is Liên thông Công nghệ thông tin'] = strpos( $rendered_html, '<p class="text-sm text-slate-400">Liên thông Công nghệ thông tin</p>' ) !== false;

// 7. Testimonials have ZERO VB2
$tests['Testimonials have 0 VB2'] = ( stripos( $rendered_html, 'VB2' ) === false && stripos( $rendered_html, 'Văn bằng 2' ) === false );

// 8. Sample news fallback is 100% focused on Liên thông
$tests['Sample news fallback contains Liên thông đại học 2026'] = strpos( $rendered_html, 'Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026' ) !== false;
$tests['Sample news fallback contains Liên thông CĐ lên ĐH'] = strpos( $rendered_html, 'Quy chế tuyển sinh Liên thông Cao đẳng lên Đại học' ) !== false;

$passed = 0;
$failed = 0;
foreach ( $tests as $name => $result ) {
	if ( $result ) {
		$passed++;
		echo "  [PASS] {$name}\n";
	} else {
		$failed++;
		echo "  [FAIL] {$name}\n";
	}
}

echo "\nRender simulation: {$passed} PASSED, {$failed} FAILED\n";

if ( $failed > 0 ) {
	exit( 1 );
}
exit( 0 );
