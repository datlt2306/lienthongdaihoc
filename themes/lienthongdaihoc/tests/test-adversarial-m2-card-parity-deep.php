<?php
/**
 * Adversarial Deep Stress Harness: Milestone 2 Program Card Parity
 *
 * Authored by: challenger_m2_2_rep
 * Tests:
 * 1. Byte-for-byte exact equality between SSR and AJAX card rendering
 * 2. Deep stress testing of unusual/adversarial program fixtures:
 *    - Orphan programs (no school relation)
 *    - Missing major relations (fallback parsing)
 *    - Array of post objects vs array of IDs vs single IDs for ACF relations
 *    - HTML characters, quotes, XSS injections in title
 *    - Empty training types, prefixes ('Hệ ', 'Ngành ')
 *    - All compare attributes present on container and button
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class M2DeepCardTester {
	public static int $passed = 0;
	public static int $failed = 0;
	public static array $errors = [];

	public static function assert( bool $condition, string $description, array $context = [] ): void {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] {$description}\n";
		} else {
			self::$failed++;
			self::$errors[] = [ 'desc' => $description, 'context' => $context ];
			echo "  [FAIL] {$description}\n";
			if ( ! empty( $context ) ) {
				echo "         Context: " . json_encode( $context, JSON_UNESCAPED_UNICODE ) . "\n";
			}
		}
	}
}

class CardMockDeepEnv {
	public static array $posts = [];
	public static array $meta = [];
	public static array $terms = [];
	public static int $current_post_id = 0;

	public static function reset() {
		self::$posts = [];
		self::$meta = [];
		self::$terms = [];
		self::$current_post_id = 0;
	}

	public static function add_post( int $id, string $title, string $type = 'program', string $slug = '' ) {
		self::$posts[ $id ] = (object) [
			'ID'         => $id,
			'post_title' => $title,
			'post_name'  => $slug ?: strtolower( trim( preg_replace( '/[^A-Za-z0-9-]+/', '-', $title ) ) ),
			'post_type'  => $type,
		];
	}

	public static function set_field( int $id, string $key, $value ) {
		self::$meta[ $id ][ $key ] = $value;
	}

	public static function add_term( int $id, string $taxonomy, string $slug, string $name ) {
		self::$terms[ $id ][ $taxonomy ][] = (object) [
			'slug' => $slug,
			'name' => $name,
		];
	}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() { return CardMockDeepEnv::$current_post_id; }
}
if ( ! function_exists( 'get_the_title' ) ) {
	function get_the_title( $id = 0 ) {
		$pid = $id ?: CardMockDeepEnv::$current_post_id;
		return CardMockDeepEnv::$posts[ $pid ]->post_title ?? '';
	}
}
if ( ! function_exists( 'get_post_field' ) ) {
	function get_post_field( $field, $id = 0 ) {
		$pid = $id ?: CardMockDeepEnv::$current_post_id;
		return CardMockDeepEnv::$posts[ $pid ]->$field ?? '';
	}
}
if ( ! function_exists( 'get_field' ) ) {
	function get_field( $key, $id = 0 ) {
		$pid = $id ?: CardMockDeepEnv::$current_post_id;
		return CardMockDeepEnv::$meta[ $pid ][ $key ] ?? null;
	}
}
if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( $id, $key, $single = false ) {
		return CardMockDeepEnv::$meta[ $id ][ $key ] ?? ( $single ? '' : [] );
	}
}
if ( ! function_exists( 'wp_get_post_terms' ) ) {
	function wp_get_post_terms( $id, $taxonomy ) {
		return CardMockDeepEnv::$terms[ $id ][ $taxonomy ] ?? [];
	}
}
if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) { return false; }
}
if ( ! function_exists( 'get_permalink' ) ) {
	function get_permalink( $id = 0 ) {
		$slug = CardMockDeepEnv::$posts[ $id ]->post_name ?? $id;
		return 'https://lienthongdaihoc.com/' . $slug . '/';
	}
}
if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
}
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
}
if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) { return htmlspecialchars( (string) $url, ENT_QUOTES, 'UTF-8' ); }
}
if ( ! function_exists( 'get_stylesheet_directory_uri' ) ) {
	function get_stylesheet_directory_uri() { return 'https://lienthongdaihoc.com/wp-content/themes/lienthongdaihoc'; }
}
if ( ! function_exists( 'wp_get_attachment_image' ) ) {
	function wp_get_attachment_image( $id, $size = 'thumbnail', $icon = false, $attr = [] ) {
		return '<img src="https://lienthongdaihoc.com/logo-' . $id . '.webp" alt="" />';
	}
}
if ( ! function_exists( 'ltdh_icon' ) ) {
	function ltdh_icon( $name, $class = '' ) {
		echo '<svg class="ltdh-icon icon-' . esc_attr( $name ) . ' ' . esc_attr( $class ) . '"><use href="#icon-' . esc_attr( $name ) . '"></use></svg>';
	}
}

function render_card_deep( int $prog_id, bool $is_ajax ): string {
	ob_start();
	if ( $is_ajax ) {
		$args = [ 'prog_id' => $prog_id ];
		CardMockDeepEnv::$current_post_id = 0;
	} else {
		$args = [];
		CardMockDeepEnv::$current_post_id = $prog_id;
	}
	include ABSPATH . 'template-parts/cards/card-program.php';
	return ob_get_clean();
}

$stress_fixtures = [
	[
		'id'     => 601,
		'title'  => 'Cử nhân Ngôn ngữ Nhật',
		'slug'   => 'cu-nhan-ngon-ngu-nhat',
		'school' => null,
		'major'  => 20,
		'type'   => [ 'tu-xa', 'Từ xa' ],
		'status' => null,
	],
	[
		'id'     => 602,
		'title'  => 'Kỹ sư Cơ điện tử',
		'slug'   => 'ky-su-co-dien-tu',
		'school' => [ 10 ],
		'major'  => [ 21 ],
		'type'   => [ 'vua-hoc-vua-lam', 'Hệ Vừa học vừa làm' ],
		'status' => 'sap-mo',
	],
	[
		'id'     => 603,
		'title'  => 'Đại học Luật Kinh tế',
		'slug'   => 'dai-hoc-luat-kinh-te',
		'school' => [ (object) [ 'ID' => 10 ] ],
		'major'  => [ (object) [ 'ID' => 22 ] ],
		'type'   => [ 'tu-xa', 'Hệ Đào tạo từ xa' ],
		'status' => 'tam-ngung',
	],
	[
		'id'     => 604,
		'title'  => 'Cử nhân Tài chính - Ngân hàng',
		'slug'   => 'cu-nhan-tai-chinh-ngan-hang',
		'school' => (object) [ 'ID' => 10 ],
		'major'  => (object) [ 'ID' => 23 ],
		'type'   => [ 'chinh-quy', 'Chính quy' ],
		'status' => 'tuyen-sinh',
	],
	[
		'id'     => 605,
		'title'  => 'Chương trình "Đặc Biệt" & <Hot> CNTT - 2026',
		'slug'   => 'chuong-trinh-dac-biet-cntt-2026',
		'school' => 10,
		'major'  => 0,
		'type'   => [ 'tu-xa', 'Từ xa' ],
		'status' => 'tuyen-sinh',
	],
	[
		'id'     => 606,
		'title'  => 'Liên thông Sư phạm Âm nhạc',
		'slug'   => 'lien-thong-su-pham-am-nhac',
		'school' => 10,
		'major'  => 20,
		'type'   => null,
		'status' => '',
	],
	[
		'id'     => 607,
		'title'  => 'Liên thông ngành Kỹ thuật Điện & Điện tử',
		'slug'   => 'lien-thong-nganh-ky-thuat-dien',
		'school' => 10,
		'major'  => 24,
		'type'   => [ 'tu-xa', 'Hệ Từ xa' ],
		'status' => 'tuyen-sinh',
	],
];

// Seed school 10 and majors
CardMockDeepEnv::reset();
CardMockDeepEnv::add_post( 10, 'Trường Đại học Bách Khoa', 'school', 'bkhn' );
CardMockDeepEnv::set_field( 10, 'school_code', 'BKHN' );
CardMockDeepEnv::add_post( 20, 'Ngôn ngữ Nhật', 'major', 'ngon-ngu-nhat' );
CardMockDeepEnv::add_post( 21, 'Cơ điện tử', 'major', 'co-dien-tu' );
CardMockDeepEnv::add_post( 22, 'Luật Kinh tế', 'major', 'luat-kinh-te' );
CardMockDeepEnv::add_post( 23, 'Tài chính - Ngân hàng', 'major', 'tai-chinh-ngan-hang' );
CardMockDeepEnv::add_post( 24, 'Ngành Kỹ thuật Điện & Điện tử', 'major', 'ky-thuat-dien' );

foreach ( $stress_fixtures as $f ) {
	CardMockDeepEnv::add_post( $f['id'], $f['title'], 'program', $f['slug'] );
	if ( $f['school'] !== null ) {
		CardMockDeepEnv::set_field( $f['id'], 'school_relationship', $f['school'] );
	}
	if ( $f['major'] !== null ) {
		CardMockDeepEnv::set_field( $f['id'], 'major_relationship', $f['major'] );
	}
	if ( $f['type'] !== null ) {
		CardMockDeepEnv::add_term( $f['id'], 'training_type', $f['type'][0], $f['type'][1] );
	}
	if ( $f['status'] !== null ) {
		CardMockDeepEnv::set_field( $f['id'], 'admission_status', $f['status'] );
	}
}

echo "===================================================================\n";
echo "ADVERSARIAL DEEP STRESS HARNESS: CARD PROGRAM PARITY\n";
echo "===================================================================\n";

foreach ( $stress_fixtures as $idx => $f ) {
	$pid = $f['id'];
	$ssr = render_card_deep( $pid, false );
	$ajax = render_card_deep( $pid, true );

	// 1. Exact byte-for-byte parity
	M2DeepCardTester::assert(
		$ssr === $ajax,
		"Fixture #{$idx} [{$f['title']}]: Exact 100% byte-for-byte HTML parity between SSR and AJAX"
	);

	// 2. Container Attributes
	$expected_container_attrs = [
		'data-compare-btn',
		'data-compare-type="program"',
		'data-compare-id="' . $pid . '"',
		'data-compare-slug="' . $f['slug'] . '"',
		'data-compare-thumb=',
		'data-compare-he=',
		'data-compare-nganh=',
		'data-compare-title=',
	];
	foreach ( $expected_container_attrs as $attr ) {
		M2DeepCardTester::assert(
			strpos( $ssr, $attr ) !== false,
			"Fixture #{$idx} [{$f['title']}]: Container includes attribute {$attr}"
		);
	}

	// 3. Button Attributes
	$expected_btn_attrs = [
		'class="rounded-full ltdh-compare-toggle',
		'data-compare-type="program"',
		'data-compare-id="' . $pid . '"',
		'data-compare-slug="' . $f['slug'] . '"',
		'data-compare-he=',
		'data-compare-nganh=',
		'data-compare-title=',
	];
	foreach ( $expected_btn_attrs as $attr ) {
		M2DeepCardTester::assert(
			strpos( $ssr, $attr ) !== false,
			"Fixture #{$idx} [{$f['title']}]: Button includes attribute {$attr}"
		);
	}

	// 4. XSS Escaping
	M2DeepCardTester::assert(
		strpos( $ssr, '<script>' ) === false,
		"Fixture #{$idx} [{$f['title']}]: Contains zero unescaped <script> tags"
	);

	// 5. Zero raw 'Hệ ' in badge
	if ( ! empty( $f['type'] ) ) {
		$clean_type = preg_replace( '/^hệ\s+/iu', '', trim( $f['type'][1] ) );
		M2DeepCardTester::assert(
			strpos( $ssr, '>Hệ ' . $clean_type . '<' ) === false,
			"Fixture #{$idx} [{$f['title']}]: Zero 'Hệ ' prefix in badge"
		);
	}
}

echo "\n===================================================================\n";
echo "SUMMARY: " . M2DeepCardTester::$passed . " PASSED, " . M2DeepCardTester::$failed . " FAILED\n";
echo "===================================================================\n";

if ( M2DeepCardTester::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
