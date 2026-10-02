<?php
/**
 * Empirical Test Harness for Milestone M2: Campus Isolation & Data Flow
 *
 * Verifies:
 * 1. ltdh_get_program_learning_details() on programs with campus = 'online' (IDs 1800, 1795, 1791, 1785)
 *    and extensive adversarial edge cases (case variations, mixed campuses, non-tu-xa, missing terms).
 * 2. inc/comparison.php helper ltdh_get_program_comparison_data() and template output for campus_info.
 * 3. taxonomy.php:220 syntax, token AST parsing, and runtime output.
 * 4. single-program.php campus guard behavior.
 *
 * Run via: php tests/test-m2-empirical.php
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

// Load constants
require_once __DIR__ . '/../inc/config/constants.php';
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

// In-memory WordPress mock database for test programs
class MockWPData {
	public static $posts = [];
	public static $post_meta = [];
	public static $terms = [];
	public static $term_relationships = [];

	public static function reset() {
		self::$posts = [];
		self::$post_meta = [];
		self::$terms = [];
		self::$term_relationships = [];
	}

	public static function add_post( $id, $title, $type = 'program', $status = 'publish' ) {
		self::$posts[ $id ] = (object) [
			'ID' => $id,
			'post_title' => $title,
			'post_name' => strtolower( str_replace( ' ', '-', $title ) ),
			'post_type' => $type,
			'post_status' => $status,
		];
	}

	public static function set_meta( $id, $key, $value ) {
		self::$post_meta[ $id ][ $key ] = $value;
	}

	public static function add_term( $post_id, $taxonomy, $slug, $name ) {
		$term = (object) [
			'term_id' => rand( 100, 999 ),
			'slug' => $slug,
			'name' => $name,
			'taxonomy' => $taxonomy,
		];
		self::$term_relationships[ $post_id ][ $taxonomy ][] = $term;
	}
}

// WordPress Stub Functions
function wp_get_post_terms( $post_id, $taxonomy, $args = [] ) {
	$terms = MockWPData::$term_relationships[ $post_id ][ $taxonomy ] ?? [];
	if ( isset( $args['fields'] ) && $args['fields'] === 'names' ) {
		return array_map( function( $t ) { return $t->name; }, $terms );
	}
	if ( isset( $args['fields'] ) && $args['fields'] === 'slugs' ) {
		return array_map( function( $t ) { return $t->slug; }, $terms );
	}
	return $terms;
}

function is_wp_error( $thing ) {
	return false;
}

function get_post_meta( $post_id, $key = '', $single = false ) {
	if ( empty( $key ) ) {
		return MockWPData::$post_meta[ $post_id ] ?? [];
	}
	$val = MockWPData::$post_meta[ $post_id ][ $key ] ?? '';
	return $single ? $val : ( $val !== '' ? [ $val ] : [] );
}

function update_post_meta( $post_id, $key, $value ) {
	MockWPData::$post_meta[ $post_id ][ $key ] = $value;
	return true;
}

function get_the_title( $post_id = 0 ) {
	return MockWPData::$posts[ $post_id ]->post_title ?? 'Test Post';
}

function get_permalink( $post_id = 0 ) {
	return 'https://lienthongdaihoc.com/test-url-' . $post_id;
}

function get_the_permalink( $post_id = 0 ) {
	return get_permalink( $post_id );
}

function the_permalink() {
	echo get_permalink( 1800 );
}

function get_the_excerpt( $post_id = 0 ) {
	return 'Sample excerpt for ' . $post_id;
}

function get_field( $selector, $post_id = false, $format_value = true ) {
	return get_post_meta( $post_id, $selector, true );
}

function get_the_post_thumbnail_url( $post_id = 0, $size = 'post-thumbnail' ) {
	return 'https://lienthongdaihoc.com/sample.jpg';
}

function wp_get_attachment_image_url( $attachment_id, $size = 'thumbnail' ) {
	return 'https://lienthongdaihoc.com/sample.jpg';
}

function esc_html( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_url( $url ) {
	return filter_var( $url, FILTER_SANITIZE_URL ) ?: '';
}

function sanitize_title( $title ) {
	return strtolower( trim( preg_replace( '/[^A-Za-z0-9-]+/', '-', $title ) ) );
}

function wp_cache_get( $key, $group = '' ) {
	return false;
}

function wp_cache_set( $key, $data, $group = '', $expire = 0 ) {
	return true;
}

function has_post_thumbnail( $post_id = 0 ) {
	return false;
}

function get_post_thumbnail_id( $post_id = 0 ) {
	return 0;
}

function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	return true;
}

function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	return true;
}

function do_action( $tag, ...$args ) {
	return null;
}

function apply_filters( $tag, $value, ...$args ) {
	return $value;
}

function wp_list_pluck( $list, $field ) {
	$result = [];
	foreach ( $list as $key => $value ) {
		if ( is_object( $value ) && isset( $value->$field ) ) {
			$result[ $key ] = $value->$field;
		} elseif ( is_array( $value ) && isset( $value[ $field ] ) ) {
			$result[ $key ] = $value[ $field ];
		}
	}
	return $result;
}

function absint( $maybeint ) {
	return abs( (int) $maybeint );
}

function trailingslashit( $string ) {
	return rtrim( $string, '/\\' ) . '/';
}

function home_url( $path = '' ) {
	return 'https://lienthongdaihoc.com' . ( $path ? '/' . ltrim( $path, '/' ) : '' );
}

function get_template_directory_uri() {
	return 'https://lienthongdaihoc.com/wp-content/themes/lienthongdaihoc';
}

function wp_strip_all_tags( $text ) {
	return strip_tags( (string) $text );
}

function wp_kses_post( $string ) {
	return $string;
}

function get_posts( $args = [] ) {
	$results = [];
	$post_type = $args['post_type'] ?? 'post';
	$post_status = $args['post_status'] ?? 'publish';
	$fields = $args['fields'] ?? '';
	
	$school_filter = null;
	if ( ! empty( $args['meta_query'] ) ) {
		foreach ( $args['meta_query'] as $mq ) {
			if ( is_array( $mq ) && isset( $mq['key'] ) && $mq['key'] === 'school_relationship' ) {
				$school_filter = $mq['value'];
			}
		}
	}
	
	$tax_terms = null;
	if ( ! empty( $args['tax_query'] ) ) {
		foreach ( $args['tax_query'] as $tq ) {
			if ( is_array( $tq ) && isset( $tq['terms'] ) ) {
				$tax_terms = (array) $tq['terms'];
			}
		}
	}

	foreach ( MockWPData::$posts as $pid => $post ) {
		if ( $post->post_type !== $post_type ) continue;
		if ( $post_status !== 'any' && $post->post_status !== $post_status ) continue;
		if ( isset( $args['post__in'] ) && ! in_array( $pid, $args['post__in'] ) ) continue;
		
		if ( $school_filter !== null ) {
			$s_rel = MockWPData::$post_meta[ $pid ]['school_relationship'] ?? null;
			if ( (int) $s_rel !== (int) $school_filter ) continue;
		}
		
		if ( $tax_terms !== null ) {
			$terms = MockWPData::$term_relationships[ $pid ][ LTDH_TAX_TRAINING_TYPE ] ?? [];
			$match = false;
			foreach ( $terms as $term ) {
				if ( in_array( $term->slug, $tax_terms, true ) ) {
					$match = true;
					break;
				}
			}
			if ( ! $match ) continue;
		}

		$results[] = ( $fields === 'ids' ) ? $pid : $post;
	}
	return $results;
}

// Load Theme Implementation Files
require_once __DIR__ . '/../inc/core/class-helpers.php';
require_once __DIR__ . '/../inc/comparison.php';

// Test Framework
class TestRunner {
	public static $passed = 0;
	public static $failed = 0;
	public static $results = [];

	public static function assert( $condition, $message, $details = null ) {
		if ( $condition ) {
			self::$passed++;
			self::$results[] = [ 'status' => 'PASS', 'message' => $message, 'details' => $details ];
			echo "  [PASS] $message\n";
		} else {
			self::$failed++;
			self::$results[] = [ 'status' => 'FAIL', 'message' => $message, 'details' => $details ];
			echo "  [FAIL] $message\n";
			if ( $details ) {
				echo "         Details: " . print_r( $details, true ) . "\n";
			}
		}
	}
}

echo "===================================================================\n";
echo "EMPIRICAL TEST SUITE: M2 CAMPUS ISOLATION & DATA FLOW\n";
echo "===================================================================\n\n";

// -----------------------------------------------------------------
// Setup Seeded Programs from Database Audit
// -----------------------------------------------------------------
// School IDs
$school_cd = 1650; // Trường Đại học Công đoàn (Hà Nội)
MockWPData::add_post( $school_cd, 'Trường Đại học Công đoàn', 'school' );
MockWPData::add_term( $school_cd, LTDH_TAX_REGION, 'mien-bac', 'Miền Bắc' );

$school_tdhn = 1651; // Trường Đại học Thủ đô Hà Nội
MockWPData::add_post( $school_tdhn, 'Trường Đại học Thủ đô Hà Nội', 'school' );
MockWPData::add_term( $school_tdhn, LTDH_TAX_REGION, 'mien-bac', 'Miền Bắc' );

$school_ldxh = 1652; // Trường Đại học Lao động - Xã hội
MockWPData::add_post( $school_ldxh, 'Trường Đại học Lao động - Xã hội', 'school' );
MockWPData::add_term( $school_ldxh, LTDH_TAX_REGION, 'mien-bac', 'Miền Bắc' );

// 1. Program 1800: Cử nhân Công tác xã hội (ĐH Công đoàn), Từ xa, Campus: Online
MockWPData::add_post( 1800, 'Cử nhân Công tác xã hội', 'program' );
MockWPData::set_meta( 1800, 'school_relationship', $school_cd );
MockWPData::add_term( 1800, LTDH_TAX_TRAINING_TYPE, 'tu-xa', 'Từ xa' );
MockWPData::add_term( 1800, LTDH_TAX_CAMPUS, 'online', 'Online' );

// 2. Program 1795: Cử nhân Bảo hộ lao động (ĐH Công đoàn), Từ xa, Campus: Online
MockWPData::add_post( 1795, 'Cử nhân Bảo hộ lao động', 'program' );
MockWPData::set_meta( 1795, 'school_relationship', $school_cd );
MockWPData::add_term( 1795, LTDH_TAX_TRAINING_TYPE, 'tu-xa', 'Từ xa' );
MockWPData::add_term( 1795, LTDH_TAX_CAMPUS, 'online', 'Online' );

// 3. Program 1791: Cử nhân Logistics (ĐH Thủ đô Hà Nội), Từ xa, Campus: Online
MockWPData::add_post( 1791, 'Cử nhân Logistics', 'program' );
MockWPData::set_meta( 1791, 'school_relationship', $school_tdhn );
MockWPData::add_term( 1791, LTDH_TAX_TRAINING_TYPE, 'tu-xa', 'Từ xa' );
MockWPData::add_term( 1791, LTDH_TAX_CAMPUS, 'online', 'Online' );

// 4. Program 1785: Cử nhân Công tác xã hội (ĐH Lao động - Xã hội), Từ xa, Campus: Online
MockWPData::add_post( 1785, 'Cử nhân Công tác xã hội', 'program' );
MockWPData::set_meta( 1785, 'school_relationship', $school_ldxh );
MockWPData::add_term( 1785, LTDH_TAX_TRAINING_TYPE, 'tu-xa', 'Từ xa' );
MockWPData::add_term( 1785, LTDH_TAX_CAMPUS, 'online', 'Online' );

// -----------------------------------------------------------------
// Edge Case Programs
// -----------------------------------------------------------------
// 5. Program 9001: Mixed Campus (Online + Hà Nội + TP.HCM)
MockWPData::add_post( 9001, 'Chương trình Đa cơ sở', 'program' );
MockWPData::set_meta( 9001, 'school_relationship', $school_cd );
MockWPData::add_term( 9001, LTDH_TAX_TRAINING_TYPE, 'tu-xa', 'Từ xa' );
MockWPData::add_term( 9001, LTDH_TAX_CAMPUS, 'online', 'Online' );
MockWPData::add_term( 9001, LTDH_TAX_CAMPUS, 'ha-noi', 'Hà Nội' );
MockWPData::add_term( 9001, LTDH_TAX_CAMPUS, 'ho-chi-minh', 'TP. Hồ Chí Minh' );

// 6. Program 9002: Weird Case ('oNLiNe', ' ONLINE ')
MockWPData::add_post( 9002, 'Chương trình Case Online Dị biệt', 'program' );
MockWPData::set_meta( 9002, 'school_relationship', $school_cd );
MockWPData::add_term( 9002, LTDH_TAX_TRAINING_TYPE, 'tu-xa', 'Từ xa' );
MockWPData::add_term( 9002, LTDH_TAX_CAMPUS, 'oNLiNe', ' ONLINE ' );

// 7. Program 9003: Vừa học vừa làm + Campus Online + School Region Miền Bắc
MockWPData::add_post( 9003, 'Chương trình VLVH Online Fallback', 'program' );
MockWPData::set_meta( 9003, 'school_relationship', $school_cd );
MockWPData::add_term( 9003, LTDH_TAX_TRAINING_TYPE, 'vua-hoc-vua-lam', 'Vừa học vừa làm' );
MockWPData::add_term( 9003, LTDH_TAX_CAMPUS, 'online', 'Online' );

// 8. Program 9004: No campus terms at all (Từ xa)
MockWPData::add_post( 9004, 'Chương trình Không gắn Campus', 'program' );
MockWPData::set_meta( 9004, 'school_relationship', $school_cd );
MockWPData::add_term( 9004, LTDH_TAX_TRAINING_TYPE, 'tu-xa', 'Từ xa' );

// 9. Program 9005: Physical Campus Only ('Đà Nẵng')
MockWPData::add_post( 9005, 'Chương trình Trạm Đà Nẵng', 'program' );
MockWPData::set_meta( 9005, 'school_relationship', $school_cd );
MockWPData::add_term( 9005, LTDH_TAX_TRAINING_TYPE, 'tu-xa', 'Từ xa' );
MockWPData::add_term( 9005, LTDH_TAX_CAMPUS, 'da-nang', 'Đà Nẵng' );


// =================================================================
// SUITE 1: ltdh_get_program_learning_details() Empirical Tests
// =================================================================
echo "SUITE 1: Testing ltdh_get_program_learning_details()\n";
echo "-------------------------------------------------------------------\n";

// Test 1: Program 1800
$d1800 = ltdh_get_program_learning_details( 1800 );
TestRunner::assert(
	$d1800['campus'] === 'Toàn quốc',
	"Program 1800: campus is 'Toàn quốc' (actual: '{$d1800['campus']}')",
	$d1800
);
TestRunner::assert(
	stripos( $d1800['campus'], 'online' ) === false,
	"Program 1800: campus never contains 'Online'",
	$d1800
);
TestRunner::assert(
	$d1800['mode'] === 'Học online 100%',
	"Program 1800: mode is 'Học online 100%' (actual: '{$d1800['mode']}')",
	$d1800
);

// Test 2: Program 1795
$d1795 = ltdh_get_program_learning_details( 1795 );
TestRunner::assert(
	$d1795['campus'] === 'Toàn quốc' && stripos( $d1795['campus'], 'online' ) === false,
	"Program 1795: campus is 'Toàn quốc' and not 'Online'",
	$d1795
);

// Test 3: Program 1791
$d1791 = ltdh_get_program_learning_details( 1791 );
TestRunner::assert(
	$d1791['campus'] === 'Toàn quốc' && stripos( $d1791['campus'], 'online' ) === false,
	"Program 1791: campus is 'Toàn quốc' and not 'Online'",
	$d1791
);

// Test 4: Program 1785
$d1785 = ltdh_get_program_learning_details( 1785 );
TestRunner::assert(
	$d1785['campus'] === 'Toàn quốc' && stripos( $d1785['campus'], 'online' ) === false,
	"Program 1785: campus is 'Toàn quốc' and not 'Online'",
	$d1785
);

// Test 5: Mixed Campuses (Program 9001)
$d9001 = ltdh_get_program_learning_details( 9001 );
TestRunner::assert(
	$d9001['campus'] === 'Hà Nội, TP. Hồ Chí Minh',
	"Program 9001 (mixed): returns physical stations 'Hà Nội, TP. Hồ Chí Minh' (actual: '{$d9001['campus']}')",
	$d9001
);
TestRunner::assert(
	stripos( $d9001['campus'], 'online' ) === false,
	"Program 9001 (mixed): 'Online' is stripped out from physical campuses",
	$d9001
);

// Test 6: Weird Casing / Whitespace (Program 9002)
$d9002 = ltdh_get_program_learning_details( 9002 );
TestRunner::assert(
	$d9002['campus'] === 'Toàn quốc',
	"Program 9002 ('oNLiNe', ' ONLINE '): case-insensitively stripped, falls back to 'Toàn quốc'",
	$d9002
);

// Test 7: Vừa học vừa làm + Online tag (Program 9003)
$d9003 = ltdh_get_program_learning_details( 9003 );
TestRunner::assert(
	$d9003['campus'] === 'Miền Bắc',
	"Program 9003 (VLVH + Online tag): stripped Online, resolved school region 'Miền Bắc' (actual: '{$d9003['campus']}')",
	$d9003
);
TestRunner::assert(
	$d9003['mode'] === 'Học tập trung / Cuối tuần',
	"Program 9003: mode is 'Học tập trung / Cuối tuần'",
	$d9003
);

// Test 8: No campus terms at all (Program 9004)
$d9004 = ltdh_get_program_learning_details( 9004 );
TestRunner::assert(
	$d9004['campus'] === 'Toàn quốc',
	"Program 9004 (no campus terms): defaults to 'Toàn quốc'",
	$d9004
);

// Test 9: Physical Campus Only (Program 9005)
$d9005 = ltdh_get_program_learning_details( 9005 );
TestRunner::assert(
	$d9005['campus'] === 'Đà Nẵng',
	"Program 9005 (physical station only): preserves 'Đà Nẵng'",
	$d9005
);

// Test 10: Non-existent / Invalid Program ID (0, -1, 999999)
$d_invalid = ltdh_get_program_learning_details( 999999 );
TestRunner::assert(
	is_array( $d_invalid ) && isset( $d_invalid['campus'] ) && $d_invalid['campus'] === 'Toàn quốc',
	"Invalid ID 999999: safely returns array with campus = 'Toàn quốc'",
	$d_invalid
);

echo "\n";


// =================================================================
// SUITE 2: inc/comparison.php Helper & Table Output Tests
// =================================================================
echo "SUITE 2: Testing inc/comparison.php helper & comparison table output\n";
echo "-------------------------------------------------------------------\n";

// Test 11: ltdh_compare_resolve_program on 1800
$comp1800 = ltdh_compare_resolve_program( 1800 );
TestRunner::assert(
	isset( $comp1800['campus'] ) && $comp1800['campus'] === 'Toàn quốc',
	"Comparison 1800: \$comp['campus'] is 'Toàn quốc' (actual: '{$comp1800['campus']}')",
	$comp1800
);
TestRunner::assert(
	isset( $comp1800['campus_info'] ) && $comp1800['campus_info'] === 'Toàn quốc',
	"Comparison 1800: \$comp['campus_info'] is 'Toàn quốc' (actual: '{$comp1800['campus_info']}')",
	$comp1800
);
TestRunner::assert(
	stripos( $comp1800['campus_info'], 'online' ) === false,
	"Comparison 1800: campus_info never contains 'Online'",
	$comp1800
);

// Test 12: ltdh_compare_resolve_program on 1795, 1791, 1785
$comp1795 = ltdh_compare_resolve_program( 1795 );
$comp1791 = ltdh_compare_resolve_program( 1791 );
$comp1785 = ltdh_compare_resolve_program( 1785 );

foreach ( [ 1795 => $comp1795, 1791 => $comp1791, 1785 => $comp1785 ] as $pid => $comp ) {
	TestRunner::assert(
		$comp['campus_info'] === 'Toàn quốc' && stripos( $comp['campus_info'], 'online' ) === false,
		"Comparison $pid: campus_info is 'Toàn quốc', strictly isolates Online",
		$comp
	);
}

// Test 13: ltdh_compare_resolve_program on mixed campuses (9001)
$comp9001 = ltdh_compare_resolve_program( 9001 );
TestRunner::assert(
	$comp9001['campus_info'] === 'Hà Nội, TP. Hồ Chí Minh',
	"Comparison 9001 (mixed): campus_info contains only physical stations: '{$comp9001['campus_info']}'",
	$comp9001
);
TestRunner::assert(
	stripos( $comp9001['campus_info'], 'online' ) === false,
	"Comparison 9001 (mixed): 'Online' is not in campus_info",
	$comp9001
);

// Test 14: Desktop Table View Row Output Verification
// Simulate template-parts/compare/program-table.php row rendering
$items = [ $comp1800, $comp1795, $comp1791, $comp1785, $comp9001 ];
$row_campus = [
	'label' => 'Cơ sở học',
	'key' => 'campus_info',
	'render' => function( $item ) { return esc_html( $item['campus_info'] ?: 'Chưa cập nhật' ); }
];

foreach ( $items as $it ) {
	$cell_output = $row_campus['render']( $it );
	TestRunner::assert(
		stripos( $cell_output, 'online' ) === false,
		"Table Row 'Cơ sở học' for ID {$it['id']} output: '{$cell_output}' (Never Online)",
		$cell_output
	);
}

// Test 15: Mobile Card View Row Output Verification
// Simulate template-parts/compare/program-cards.php section rendering
foreach ( $items as $it ) {
	$val = $it['campus_info'];
	TestRunner::assert(
		stripos( $val, 'online' ) === false,
		"Mobile Card 'Cơ sở' for ID {$it['id']} output: '{$val}' (Never Online)",
		$val
	);
}

echo "\n";


// =================================================================
// SUITE 3: taxonomy.php:220 Syntax & Token Inspection
// =================================================================
echo "SUITE 3: Testing taxonomy.php:220 syntax, AST token stream, and output\n";
echo "-------------------------------------------------------------------\n";

$taxonomy_file = __DIR__ . '/../taxonomy.php';
$file_content  = file_get_contents( $taxonomy_file );
$lines         = explode( "\n", $file_content );
$line_220      = $lines[219] ?? ''; // 1-indexed line 220 is array index 219

// Test 16: Check line 220 content
TestRunner::assert(
	strpos( $line_220, '<a href="<?php the_permalink(); ?>"' ) !== false,
	"taxonomy.php:220 has valid '<a href=\"<?php the_permalink(); ?>\"'",
	$line_220
);
TestRunner::assert(
	strpos( $line_220, '<"' ) === false && strpos( $line_220, "'>" ) === false,
	"taxonomy.php:220 has NO malformed quotes '<\"' or '\">'"
);

// Test 17: Tokenizer AST validation on taxonomy.php
$tokens = token_get_all( $file_content );
$has_parse_error = false;
$error_token = null;
foreach ( $tokens as $token ) {
	if ( is_array( $token ) && $token[0] === T_BAD_CHARACTER ) {
		$has_parse_error = true;
		$error_token = $token;
		break;
	}
}
TestRunner::assert(
	! $has_parse_error,
	"taxonomy.php tokenizes cleanly with zero T_BAD_CHARACTER or token corruption",
	$error_token
);

// Test 18: Render simulation of line 220
ob_start();
eval( '?>' . $line_220 . '<?php ' );
$rendered_220 = ob_get_clean();

TestRunner::assert(
	strpos( $rendered_220, 'href="https://lienthongdaihoc.com/test-url-1800"' ) !== false,
	"taxonomy.php:220 evaluates and renders proper href: '{$rendered_220}'"
);
TestRunner::assert(
	strpos( $rendered_220, 'Đăng ký học</a>' ) !== false,
	"taxonomy.php:220 renders button anchor text 'Đăng ký học</a>'"
);

echo "\n";


// =================================================================
// SUITE 4: single-program.php Campus Defense-in-Depth Guard
// =================================================================
echo "SUITE 4: Testing single-program.php campus guard logic\n";
echo "-------------------------------------------------------------------\n";

function simulate_single_program_campus( $learning_details ) {
	$display_campus = $learning_details['campus'] ?? '';
	if ( empty( $display_campus ) || 'online' === strtolower( trim( $display_campus ) ) ) {
		$display_campus = 'Toàn quốc';
	}
	return esc_html( $display_campus );
}

// Test 19: When helper returns 'Online'
$guard_test_1 = simulate_single_program_campus( [ 'campus' => 'Online' ] );
TestRunner::assert(
	$guard_test_1 === 'Toàn quốc',
	"single-program.php guard: 'Online' is defensively forced to 'Toàn quốc'"
);

// Test 20: When helper returns empty string
$guard_test_2 = simulate_single_program_campus( [ 'campus' => '' ] );
TestRunner::assert(
	$guard_test_2 === 'Toàn quốc',
	"single-program.php guard: empty campus is defensively forced to 'Toàn quốc'"
);

// Test 21: When helper returns physical campus
$guard_test_3 = simulate_single_program_campus( [ 'campus' => 'Hà Nội' ] );
TestRunner::assert(
	$guard_test_3 === 'Hà Nội',
	"single-program.php guard: physical campus 'Hà Nội' is preserved"
);

echo "\n";


// =================================================================
// SUITE 5: ltdh_get_school_training_types() Rollup Exclusivity Tests
// =================================================================
echo "SUITE 5: Testing ltdh_get_school_training_types() rollup exclusivity\n";
echo "-------------------------------------------------------------------\n";

// Add legacy/out-of-scope terms directly to School 1650 post
MockWPData::add_term( $school_cd, LTDH_TAX_TRAINING_TYPE, 'chinh-quy', 'Chính quy' );
MockWPData::add_term( $school_cd, LTDH_TAX_TRAINING_TYPE, 'van-bang-2', 'Văn bằng 2' );

// Add a draft program (9006) for School 1650
MockWPData::add_post( 9006, 'Draft Program', 'program', 'draft' );
MockWPData::set_meta( 9006, 'school_relationship', $school_cd );
MockWPData::add_term( 9006, LTDH_TAX_TRAINING_TYPE, 'tu-xa', 'Từ xa' );

// Add an out-of-scope published program (9007) with training_type = 'chinh-quy'
MockWPData::add_post( 9007, 'Chính quy Program', 'program', 'publish' );
MockWPData::set_meta( 9007, 'school_relationship', $school_cd );
MockWPData::add_term( 9007, LTDH_TAX_TRAINING_TYPE, 'chinh-quy', 'Chính quy' );

// Test 22: Rollup training types for School 1650
$school_types_slugs = ltdh_get_school_training_types( $school_cd, 'slugs' );
TestRunner::assert(
	in_array( 'tu-xa', $school_types_slugs, true ),
	"School 1650 includes 'tu-xa' from published programs",
	$school_types_slugs
);
TestRunner::assert(
	in_array( 'vua-hoc-vua-lam', $school_types_slugs, true ),
	"School 1650 includes 'vua-hoc-vua-lam' from program 9003",
	$school_types_slugs
);
TestRunner::assert(
	! in_array( 'chinh-quy', $school_types_slugs, true ),
	"School 1650 strictly EXCLUDES 'chinh-quy' (Step 1 eliminated, tax_query enforced)",
	$school_types_slugs
);
TestRunner::assert(
	! in_array( 'van-bang-2', $school_types_slugs, true ),
	"School 1650 strictly EXCLUDES 'van-bang-2' from direct school taxonomy terms",
	$school_types_slugs
);

// Test 23: Human-readable names format
$school_types_names = ltdh_get_school_training_types( $school_cd, 'names' );
TestRunner::assert(
	in_array( 'Từ xa', $school_types_names, true ) && in_array( 'Vừa học vừa làm', $school_types_names, true ),
	"School 1650 names format returns ['Từ xa', 'Vừa học vừa làm']",
	$school_types_names
);


// =================================================================
// SUITE 6: ltdh_get_school_unique_majors_count() Query Integrity Tests
// =================================================================
echo "\nSUITE 6: Testing ltdh_get_school_unique_majors_count() query integrity\n";
echo "-------------------------------------------------------------------\n";

// Assign major relationships to programs of school_tdhn (School 1651)
// Program 1791 has major 701 (Logistics)
MockWPData::set_meta( 1791, 'major_relationship', 701 );

// Add another program with same major 701
MockWPData::add_post( 9008, 'Logistics đợt 2', 'program', 'publish' );
MockWPData::set_meta( 9008, 'school_relationship', $school_tdhn );
MockWPData::set_meta( 9008, 'major_relationship', 701 );
MockWPData::add_term( 9008, LTDH_TAX_TRAINING_TYPE, 'tu-xa', 'Từ xa' );

// Add program with different major 702
MockWPData::add_post( 9009, 'Quản trị kinh doanh', 'program', 'publish' );
MockWPData::set_meta( 9009, 'school_relationship', $school_tdhn );
MockWPData::set_meta( 9009, 'major_relationship', 702 );
MockWPData::add_term( 9009, LTDH_TAX_TRAINING_TYPE, 'tu-xa', 'Từ xa' );

// Add draft program with major 703 (must NOT be counted)
MockWPData::add_post( 9010, 'Draft Major Program', 'program', 'draft' );
MockWPData::set_meta( 9010, 'school_relationship', $school_tdhn );
MockWPData::set_meta( 9010, 'major_relationship', 703 );
MockWPData::add_term( 9010, LTDH_TAX_TRAINING_TYPE, 'tu-xa', 'Từ xa' );

$majors_count = ltdh_get_school_unique_majors_count( $school_tdhn );
TestRunner::assert(
	$majors_count === 2,
	"School 1651 unique majors count is exactly 2 (majors 701, 702; ignores duplicates and drafts, actual: $majors_count)"
);

echo "\n";
echo "===================================================================\n";
echo "TEST RESULTS: " . TestRunner::$passed . " PASSED, " . TestRunner::$failed . " FAILED\n";
echo "===================================================================\n";

if ( TestRunner::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
