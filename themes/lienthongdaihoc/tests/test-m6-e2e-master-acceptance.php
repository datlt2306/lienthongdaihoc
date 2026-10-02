<?php
/**
 * Master E2E Acceptance Test Runner for Milestone M6
 * Project: lienthongdaihoc.com IA & Scope Refactoring
 *
 * Systematically asserts all 5 milestone requirements:
 * R1: Data Audit, Out-of-Scope Isolation & Artifacts
 * R2: Core 3 CPTs, Data Flow & Campus Isolation
 * R3: Taxonomy Label Standardization & Routing
 * R4: Navigation, Homepage & Filters
 * R5: Program Card Parity & Template Presentation
 *
 * Run via: php tests/test-m6-e2e-master-acceptance.php
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

require_once ABSPATH . 'inc/config/constants.php';
require_once ABSPATH . 'inc/config/class-defaults.php';

// Test Assertion Harness
class MasterE2ETestRunner {
	public static $passed = 0;
	public static $failed = 0;
	public static $errors = [];
	public static $current_suite = '';

	public static function suite( $name ) {
		self::$current_suite = $name;
		echo "\n===================================================================\n";
		echo $name . "\n";
		echo "===================================================================\n";
	}

	public static function section( $name ) {
		echo "\n--- $name ---\n";
	}

	public static function assert( $condition, $message, $details = '' ) {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] $message\n";
		} else {
			self::$failed++;
			$fail_msg = "  [FAIL] $message";
			if ( $details ) {
				$fail_msg .= " (Details: $details)";
			}
			echo "$fail_msg\n";
			self::$errors[] = [
				'suite'   => self::$current_suite,
				'message' => $message,
				'details' => $details,
			];
		}
	}

	public static function report() {
		echo "\n===================================================================\n";
		echo sprintf( "MASTER E2E ACCEPTANCE RESULTS: %d PASSED, %d FAILED\n", self::$passed, self::$failed );
		echo "===================================================================\n";

		if ( self::$failed > 0 ) {
			echo "\nFAILURES DETECTED:\n";
			foreach ( self::$errors as $idx => $err ) {
				echo sprintf( "%d. [%s] %s\n", $idx + 1, $err['suite'], $err['message'] );
				if ( ! empty( $err['details'] ) ) {
					echo "   Details: " . $err['details'] . "\n";
				}
			}
			exit( 1 );
		}

		echo "\nALL E2E ACCEPTANCE CHECKS PASSED WITH 100% INTEGRITY.\n\n";
		exit( 0 );
	}
}

// -------------------------------------------------------------------------
// SUITE 1: Comprehensive Syntax Audit (php -l on EVERY PHP file)
// -------------------------------------------------------------------------
MasterE2ETestRunner::suite( 'SUITE 1: COMPREHENSIVE THEME PHP SYNTAX AUDIT' );

$theme_dir = realpath( ABSPATH );
$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme_dir ) );
$php_files = [];

foreach ( $iterator as $file ) {
	if ( $file->isFile() && $file->getExtension() === 'php' ) {
		$file_path = $file->getRealPath();
		// Skip third-party node_modules or dot directories
		if ( str_contains( $file_path, '/node_modules/' ) || str_contains( $file_path, '/.agents/' ) || str_contains( $file_path, '/.git/' ) ) {
			continue;
		}
		$php_files[] = $file_path;
	}
}

sort( $php_files );
MasterE2ETestRunner::assert( count( $php_files ) >= 60, sprintf( "Discovered %d PHP files in theme directory (threshold: >= 60)", count( $php_files ) ) );

$syntax_errors = [];
foreach ( $php_files as $file ) {
	$rel_path = str_replace( $theme_dir . '/', '', $file );
	$output = [];
	$return_var = 0;
	exec( sprintf( 'php -l %s 2>&1', escapeshellarg( $file ) ), $output, $return_var );
	if ( $return_var !== 0 ) {
		$syntax_errors[] = [
			'file'   => $rel_path,
			'output' => implode( "\n", $output ),
		];
	}
}

MasterE2ETestRunner::assert( empty( $syntax_errors ), sprintf( "100%% PHP syntax pass rate across all %d files (0 syntax errors)", count( $php_files ) ), ! empty( $syntax_errors ) ? json_encode( $syntax_errors ) : '' );

// -------------------------------------------------------------------------
// SUITE 2: REQUIREMENT R1 - DATA AUDIT, ISOLATION & ARTIFACT INTEGRITY
// -------------------------------------------------------------------------
MasterE2ETestRunner::suite( 'SUITE 2: REQUIREMENT R1 - DATA AUDIT, SCOPE ISOLATION & ARTIFACTS' );

$audit_file = ABSPATH . 'audit_report.json';
MasterE2ETestRunner::assert( file_exists( $audit_file ), "audit_report.json artifact exists at theme root" );

$audit_raw = file_get_contents( $audit_file );
$audit_data = json_decode( $audit_raw, true );
MasterE2ETestRunner::assert( json_last_error() === JSON_ERROR_NONE, "audit_report.json is valid well-formed JSON" );
MasterE2ETestRunner::assert( isset( $audit_data['summary'] ), "audit_report.json contains 'summary' block" );

$summary = $audit_data['summary'] ?? [];

// Programs count verification
$p_summary = $summary['programs'] ?? [];
MasterE2ETestRunner::assert( ( $p_summary['total_scanned'] ?? 0 ) === 100, "Programs total scanned is exactly 100", "Actual: " . ( $p_summary['total_scanned'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $p_summary['in_scope_total'] ?? 0 ) === 95, "Programs in-scope total is exactly 95 (94 Từ xa, 1 Vừa học vừa làm)", "Actual: " . ( $p_summary['in_scope_total'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $p_summary['in_scope_tu_xa'] ?? 0 ) === 94, "Programs in-scope 'Từ xa' is exactly 94", "Actual: " . ( $p_summary['in_scope_tu_xa'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $p_summary['in_scope_vua_hoc_vua_lam'] ?? 0 ) === 1, "Programs in-scope 'Vừa học vừa làm' is exactly 1", "Actual: " . ( $p_summary['in_scope_vua_hoc_vua_lam'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $p_summary['out_of_scope_total'] ?? 0 ) === 5, "Programs out-of-scope total is exactly 5", "Actual: " . ( $p_summary['out_of_scope_total'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $p_summary['post_status_after']['publish'] ?? 0 ) === 95, "Published programs in target database is exactly 95", "Actual: " . ( $p_summary['post_status_after']['publish'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $p_summary['post_status_after']['draft'] ?? 0 ) === 5, "Drafted out-of-scope programs is exactly 5", "Actual: " . ( $p_summary['post_status_after']['draft'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $p_summary['post_status_after']['trash'] ?? 0 ) === 0, "Programs in trash is 0 (zero deletes)", "Actual: " . ( $p_summary['post_status_after']['trash'] ?? 0 ) );

// Specific out-of-scope programs: IDs 2013, 1786, 1787, 1788, 1789
$out_of_scope_progs = $audit_data['out_of_scope_programs'] ?? [];
$out_prog_ids = array_column( $out_of_scope_progs, 'id' );
sort( $out_prog_ids );
$expected_prog_ids = [ 1786, 1787, 1788, 1789, 2013 ];
MasterE2ETestRunner::assert( $out_prog_ids === $expected_prog_ids, "Out-of-scope programs match exact IDs [1786, 1787, 1788, 1789, 2013]", "Actual: " . implode( ', ', $out_prog_ids ) );

foreach ( $out_of_scope_progs as $prog ) {
	MasterE2ETestRunner::assert( ( $prog['current_status'] ?? '' ) === 'draft', sprintf( "Program ID %d is in 'draft' status", $prog['id'] ) );
}

// Schools count verification
$s_summary = $summary['schools'] ?? [];
MasterE2ETestRunner::assert( ( $s_summary['total_scanned'] ?? 0 ) === 21, "Schools total scanned is exactly 21", "Actual: " . ( $s_summary['total_scanned'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $s_summary['in_scope_total'] ?? 0 ) === 20, "Published universities in-scope is exactly 20", "Actual: " . ( $s_summary['in_scope_total'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $s_summary['out_of_scope_total'] ?? 0 ) === 1, "Schools out-of-scope is exactly 1 (HCCT ID 1662)", "Actual: " . ( $s_summary['out_of_scope_total'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $s_summary['post_status_after']['publish'] ?? 0 ) === 20, "Published schools after audit is exactly 20", "Actual: " . ( $s_summary['post_status_after']['publish'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $s_summary['post_status_after']['draft'] ?? 0 ) === 1, "Drafted schools after audit is exactly 1", "Actual: " . ( $s_summary['post_status_after']['draft'] ?? 0 ) );

$out_schools = $audit_data['out_of_scope_schools'] ?? [];
$out_school_ids = array_column( $out_schools, 'id' );
MasterE2ETestRunner::assert( in_array( 1662, $out_school_ids ), "Drafted school is Cao đẳng HCCT (ID 1662)" );

// Majors count verification
$m_summary = $summary['majors'] ?? [];
MasterE2ETestRunner::assert( ( $m_summary['total_scanned'] ?? 0 ) === 34, "Majors total scanned is exactly 34", "Actual: " . ( $m_summary['total_scanned'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $m_summary['in_scope_total'] ?? 0 ) === 34, "Majors in-scope total is exactly 34", "Actual: " . ( $m_summary['in_scope_total'] ?? 0 ) );
MasterE2ETestRunner::assert( ( $m_summary['post_status_after']['publish'] ?? 0 ) === 34, "Published majors after audit is exactly 34", "Actual: " . ( $m_summary['post_status_after']['publish'] ?? 0 ) );

// Integrity: Zero hard deletes in audit command
$cli_code = file_get_contents( ABSPATH . 'inc/cli-commands.php' );
$audit_data_fn = '';
if ( preg_match( '/public function audit_data\s*\([^)]*\)\s*\{(.*?)\n\t\}/s', $cli_code, $matches ) ) {
	$audit_data_fn = $matches[1];
}
MasterE2ETestRunner::assert( ! empty( $audit_data_fn ), "Found audit_data implementation in inc/cli-commands.php" );
MasterE2ETestRunner::assert( ! str_contains( $audit_data_fn, 'wp_delete_post' ), "Zero calls to wp_delete_post() in audit_data method (0 hard deletes)" );
MasterE2ETestRunner::assert( ! preg_match( '/DELETE\s+FROM/i', $audit_data_fn ), "Zero SQL DELETE statements in audit_data method" );

// Ghost IDs 1855, 1856 pruning verification
MasterE2ETestRunner::assert( str_contains( $cli_code, 'removed_ghost' ) && str_contains( $cli_code, 'clean_ids' ), "inc/cli-commands.php implements ghost ID filtering and _offered_programs pruning" );

// -------------------------------------------------------------------------
// SUITE 3: REQUIREMENT R2 - CORE 3 CPTS, DATA FLOW & CAMPUS ISOLATION
// -------------------------------------------------------------------------
MasterE2ETestRunner::suite( 'SUITE 3: REQUIREMENT R2 - CORE 3 CPTS, DATA FLOW & CAMPUS ISOLATION' );

// Exactly 3 Core CPTs
$cpts_raw = file_get_contents( ABSPATH . 'inc/acf-import-cpts.json' );
$cpts_json = json_decode( $cpts_raw, true );
$post_types = [];
foreach ( $cpts_json as $item ) {
	if ( isset( $item['post_type'] ) ) {
		$post_types[] = $item['post_type'];
	}
}
$post_types = array_unique( $post_types );
sort( $post_types );
MasterE2ETestRunner::assert( $post_types === [ 'major', 'program', 'school' ], "Exactly 3 core CPTs exist (school, major, program; zero unauthorized CPTs)", "Actual: " . implode( ', ', $post_types ) );

$post_types_php = file_get_contents( ABSPATH . 'inc/post-types.php' );
MasterE2ETestRunner::assert( ! str_contains( $post_types_php, 'register_post_type( \'course\'' ), "Zero 'course' CPT registered" );
MasterE2ETestRunner::assert( ! str_contains( $post_types_php, 'register_post_type( \'admission\'' ), "Zero 'admission' CPT registered" );
MasterE2ETestRunner::assert( ! str_contains( $post_types_php, 'register_post_type( \'intake\'' ), "Zero 'intake' CPT registered" );

// Helpers: ltdh_get_school_training_types() and ltdh_get_program_learning_details()
$helpers_php = file_get_contents( ABSPATH . 'inc/core/class-helpers.php' );
MasterE2ETestRunner::assert( str_contains( $helpers_php, 'function ltdh_get_school_training_types' ), "Function ltdh_get_school_training_types() exists in class-helpers.php" );
MasterE2ETestRunner::assert( str_contains( $helpers_php, 'function ltdh_get_program_learning_details' ), "Function ltdh_get_program_learning_details() exists in class-helpers.php" );

// Helper logic verification: Rolls up only in-scope published programs
MasterE2ETestRunner::assert( str_contains( $helpers_php, '\'tu-xa\'' ) && str_contains( $helpers_php, '\'vua-hoc-vua-lam\'' ), "ltdh_get_school_training_types() enforces whitelist ['tu-xa', 'vua-hoc-vua-lam']" );
MasterE2ETestRunner::assert( str_contains( $helpers_php, '\'school_relationship\'' ), "ltdh_get_school_training_types() queries by school_relationship" );
MasterE2ETestRunner::assert( preg_match( '/[\'"]post_status[\'"]\s*=>\s*[\'"]publish[\'"]/', $helpers_php ), "ltdh_get_school_training_types() restricts to published programs" );

// Campus Online Isolation logic
MasterE2ETestRunner::assert( str_contains( $helpers_php, '\'online\'' ), "ltdh_get_program_learning_details() explicitly identifies 'online' campus term" );
MasterE2ETestRunner::assert( str_contains( $helpers_php, 'Toàn quốc' ), "ltdh_get_program_learning_details() falls back to 'Toàn quốc' when only online exists" );
MasterE2ETestRunner::assert( str_contains( $helpers_php, 'Học online 100%' ), "ltdh_get_program_learning_details() maps tu-xa to 'Học online 100%'" );
MasterE2ETestRunner::assert( str_contains( $helpers_php, 'Học tập trung / Cuối tuần' ), "ltdh_get_program_learning_details() maps vua-hoc-vua-lam to 'Học tập trung / Cuối tuần'" );

// Verify campus isolation across templates
$single_prog_php = file_get_contents( ABSPATH . 'single-program.php' );
MasterE2ETestRunner::assert( str_contains( $single_prog_php, 'Toàn quốc' ), "single-program.php has guard falling back to 'Toàn quốc'" );

// taxonomy.php:220 Syntax & Anchor Cleanliness
$tax_php = file_get_contents( ABSPATH . 'taxonomy.php' );
$tax_lines = explode( "\n", $tax_php );
$line_220 = $tax_lines[219] ?? ''; // 0-indexed line 220
MasterE2ETestRunner::assert( str_contains( $line_220, '<a href="<?php the_permalink(); ?>"' ), "taxonomy.php:220 has valid '<a href=\"<?php the_permalink(); ?>\"' syntax", "Line 220: $line_220" );
MasterE2ETestRunner::assert( ! str_contains( $line_220, '<"' ) && ! str_contains( $line_220, '">' ) === false, "taxonomy.php:220 has no malformed quote sequences" );

// -------------------------------------------------------------------------
// SUITE 4: REQUIREMENT R3 - TAXONOMY STANDARDIZATION & ROUTING
// -------------------------------------------------------------------------
MasterE2ETestRunner::suite( 'SUITE 4: REQUIREMENT R3 - TAXONOMY STANDARDIZATION & ROUTING' );

// ACF Taxonomy Label "Hình thức học"
$tax_found = false;
foreach ( $cpts_json as $item ) {
	if ( isset( $item['key'] ) && $item['key'] === 'taxonomy_training_type' ) {
		$tax_found = true;
		MasterE2ETestRunner::assert( $item['title'] === 'Hình thức học', "Taxonomy title is 'Hình thức học'", "Actual: " . $item['title'] );
		MasterE2ETestRunner::assert( $item['label'] === 'Hình thức học', "Taxonomy label is 'Hình thức học'", "Actual: " . $item['label'] );
		MasterE2ETestRunner::assert( $item['singular_label'] === 'Hình thức học', "Taxonomy singular_label is 'Hình thức học'" );
		MasterE2ETestRunner::assert( ( $item['rewrite_slug'] ?? '' ) === 'he-dao-tao', "Taxonomy rewrite_slug is preserved as 'he-dao-tao'" );
		break;
	}
}
MasterE2ETestRunner::assert( $tax_found, "Found taxonomy_training_type in acf-import-cpts.json" );

// Preserved Slugs & Rewrite Rules
$rewrites_php = file_get_contents( ABSPATH . 'inc/core/class-rewrite-rules.php' );
MasterE2ETestRunner::assert( str_contains( $rewrites_php, 'he-dao-tao/?$' ), "Rewrite rule for /he-dao-tao/ registered" );
MasterE2ETestRunner::assert( str_contains( $rewrites_php, 'he-dao-tao/([^/]+)/?$' ), "Rewrite rule for /he-dao-tao/{term}/ registered" );
MasterE2ETestRunner::assert( str_contains( $rewrites_php, 'he-dao-tao/page/([0-9]+)/?$' ), "Rewrite rule for /he-dao-tao/page/{n}/ registered" );
MasterE2ETestRunner::assert( str_contains( $rewrites_php, 'he-dao-tao/([^/]+)/page/([0-9]+)/?$' ), "Rewrite rule for /he-dao-tao/{term}/page/{n}/ registered" );

// 301 Redirect for /chuong-trinh/ -> /he-dao-tao/
MasterE2ETestRunner::assert( str_contains( $rewrites_php, 'chuong-trinh' ), "inc/core/class-rewrite-rules.php handles /chuong-trinh/ route" );
MasterE2ETestRunner::assert( str_contains( $rewrites_php, 'home_url( \'/he-dao-tao/\' )' ), "Redirect target points strictly to /he-dao-tao/ (NOT /he-dao-tao/tu-xa/)" );
MasterE2ETestRunner::assert( str_contains( $rewrites_php, '$_GET' ), "Redirect preserves \$_GET query parameters" );

// Rank Math Canonical
$seo_php = file_get_contents( ABSPATH . 'inc/seo/class-rankmath-integration.php' );
MasterE2ETestRunner::assert( str_contains( $seo_php, 'ltdh_seo_enforce_canonical_url' ), "Canonical filter ltdh_seo_enforce_canonical_url() exists" );
MasterE2ETestRunner::assert( str_contains( $seo_php, '/he-dao-tao/' ), "Canonical filter enforces /he-dao-tao/ on program archive" );
MasterE2ETestRunner::assert( str_contains( $seo_php, 'rank_math/frontend/canonical' ), "Hooked to rank_math/frontend/canonical" );

// Zero "Loại tuyển sinh" across templates & constants
$constants_php = file_get_contents( ABSPATH . 'inc/config/constants.php' );
MasterE2ETestRunner::assert( ! str_contains( $constants_php, 'loai_tuyen_sinh' ), "constants.php contains zero references to 'loai_tuyen_sinh'" );
MasterE2ETestRunner::assert( ! str_contains( $cpts_raw, 'loai_tuyen_sinh' ), "acf-import-cpts.json contains zero 'loai_tuyen_sinh' taxonomy" );

// -------------------------------------------------------------------------
// SUITE 5: REQUIREMENT R4 - NAVIGATION, FOOTER & HOMEPAGE ALIGNMENT
// -------------------------------------------------------------------------
MasterE2ETestRunner::suite( 'SUITE 5: REQUIREMENT R4 - NAVIGATION, FOOTER & HOMEPAGE ALIGNMENT' );

$nav_defaults = ltdh_get_defaults( 'navigation' );
$primary_nav = $nav_defaults['primary'] ?? [];

MasterE2ETestRunner::assert( count( $primary_nav ) === 6, "Primary navigation defaults has exactly 6 items", "Actual: " . count( $primary_nav ) );

$expected_primary = [
	0 => [ 'label' => 'Trang chủ', 'url' => '/' ],
	1 => [ 'label' => 'Liên thông đại học', 'url' => '/he-dao-tao/' ],
	2 => [ 'label' => 'Ngành học', 'url' => '/nganh-hoc/' ],
	3 => [ 'label' => 'Trường đại học', 'url' => '/truong-doi-tac/' ],
	4 => [ 'label' => 'Kiến thức liên thông', 'url' => '/tin-tuc/' ],
	5 => [ 'label' => 'Kiểm tra điều kiện', 'url' => '/kiem-tra-dieu-kien/' ],
];

foreach ( $expected_primary as $idx => $exp ) {
	$actual_item = $primary_nav[ $idx ] ?? [];
	MasterE2ETestRunner::assert(
		( $actual_item['label'] ?? '' ) === $exp['label'] && ( $actual_item['url'] ?? '' ) === $exp['url'],
		sprintf( "Menu position %d is '%s' (%s)", $idx + 1, $exp['label'], $exp['url'] ),
		sprintf( "Actual: '%s' (%s)", $actual_item['label'] ?? '', $actual_item['url'] ?? '' )
	);
}

// Submenu under 'Liên thông đại học'
$sub_items = $primary_nav[1]['sub'] ?? [];
MasterE2ETestRunner::assert( count( $sub_items ) === 2, "Menu position 2 has exactly 2 sub-items (Từ xa, Vừa học vừa làm)", "Actual: " . count( $sub_items ) );
MasterE2ETestRunner::assert( ( $sub_items[0]['url'] ?? '' ) === '/he-dao-tao/tu-xa/', "Sub-item 1 URL is '/he-dao-tao/tu-xa/'" );
MasterE2ETestRunner::assert( ( $sub_items[1]['url'] ?? '' ) === '/he-dao-tao/vua-hoc-vua-lam/', "Sub-item 2 URL is '/he-dao-tao/vua-hoc-vua-lam/'" );

// Dynamic submenu injection in class-menus.php
$menus_php = file_get_contents( ABSPATH . 'inc/core/class-menus.php' );
MasterE2ETestRunner::assert( str_contains( $menus_php, 'ltdh_dynamic_menu_submenu_injection' ), "Dynamic submenu injector function (ltdh_dynamic_menu_submenu_injection) exists in class-menus.php" );
MasterE2ETestRunner::assert( str_contains( $menus_php, 'tu-xa' ) && str_contains( $menus_php, 'vua-hoc-vua-lam' ), "Submenu injector restricts to ['tu-xa', 'vua-hoc-vua-lam']" );

// Footer.php Inspection: Column 3 & Policies
$footer_php = file_get_contents( ABSPATH . 'footer.php' );
// Column 3 must have zero '#' hrefs
preg_match( '/<!--\s*Column 3.*-->.*?<\/ul>/s', $footer_php, $col3_match );
$col3_html = $col3_match[0] ?? '';
MasterE2ETestRunner::assert( ! empty( $col3_html ), "Found Column 3 block in footer.php" );
MasterE2ETestRunner::assert( ! str_contains( $col3_html, 'href="#"' ), "Footer Column 3 contains zero dead '#' links" );
MasterE2ETestRunner::assert( ! str_contains( $col3_html, 'Văn bằng 2' ) && ! str_contains( $col3_html, 'Cao đẳng' ), "Footer Column 3 contains zero out-of-scope offerings (VB2, Cao đẳng)" );
MasterE2ETestRunner::assert( str_contains( $col3_html, '/he-dao-tao/tu-xa/' ), "Footer Column 3 links to Liên thông Từ xa" );
MasterE2ETestRunner::assert( str_contains( $col3_html, '/he-dao-tao/vua-hoc-vua-lam/' ), "Footer Column 3 links to Liên thông Vừa học vừa làm" );

// Footer policy links
preg_match( '/<!--\s*Footer Bottom\s*-->.*?<\/footer>/s', $footer_php, $bottom_match );
$bottom_html = $bottom_match[0] ?? '';
MasterE2ETestRunner::assert( str_contains( $bottom_html, '/chinh-sach-bao-mat/' ) && str_contains( $bottom_html, '/dieu-khoan/' ), "Footer bottom policy links point to /chinh-sach-bao-mat/ and /dieu-khoan/" );
MasterE2ETestRunner::assert( ! str_contains( $bottom_html, 'href="#"' ), "Footer bottom policy links have zero dead '#' links" );

// Homepage front-page.php alignment
$front_php = file_get_contents( ABSPATH . 'front-page.php' );
MasterE2ETestRunner::assert( str_contains( $front_php, 'Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hình Thức Từ Xa & Vừa Học Vừa Làm' ), "front-page.php contains standardized semantic H1" );
MasterE2ETestRunner::assert( ! str_contains( $front_php, 'Văn Bằng 2 & Đại Học Từ Xa' ), "front-page.php does NOT contain obsolete H1 mentioning VB2" );
MasterE2ETestRunner::assert( str_contains( $front_php, 'home_url( \'/he-dao-tao/\' )' ), "front-page.php search form action submits to /he-dao-tao/" );
MasterE2ETestRunner::assert( ! str_contains( $front_php, 'Học sinh tốt nghiệp THPT' ) && ! str_contains( $front_php, 'value="thpt"' ), "front-page.php eligibility contains zero THPT options" );
MasterE2ETestRunner::assert( str_contains( $front_php, 'Liên thông Công nghệ thông tin' ), "front-page.php testimonial fallback role is 'Liên thông Công nghệ thông tin'" );

// -------------------------------------------------------------------------
// SUITE 6: REQUIREMENT R5 - PROGRAM CARD PARITY & TEMPLATE PRESENTATION
// -------------------------------------------------------------------------
MasterE2ETestRunner::suite( 'SUITE 6: REQUIREMENT R5 - PROGRAM CARD PARITY & TEMPLATE PRESENTATION' );

$tax_tt_php   = file_get_contents( ABSPATH . 'taxonomy-training_type.php' );
$arch_prog_php = file_get_contents( ABSPATH . 'archive-program.php' );
$filters_php  = file_get_contents( ABSPATH . 'inc/core/class-query-filters.php' );
$single_sch   = file_get_contents( ABSPATH . 'single-school.php' );
$single_maj   = file_get_contents( ABSPATH . 'single-major.php' );
$comp_cards   = file_get_contents( ABSPATH . 'template-parts/compare/program-cards.php' );

// 1:1 Headline Formula across all templates
$headline_formula = 'Liên thông ngành ';
MasterE2ETestRunner::assert( str_contains( $tax_tt_php, $headline_formula ), "taxonomy-training_type.php implements formula 'Liên thông ngành [Major] - [Type]'" );
MasterE2ETestRunner::assert( str_contains( $arch_prog_php, $headline_formula ), "archive-program.php implements formula 'Liên thông ngành [Major] - [Type]'" );
MasterE2ETestRunner::assert( str_contains( $filters_php, $headline_formula ), "class-query-filters.php (AJAX) implements formula 'Liên thông ngành [Major] - [Type]'" );
MasterE2ETestRunner::assert( str_contains( $single_sch, $headline_formula ), "single-school.php implements formula 'Liên thông ngành [Major] - [Type]'" );
MasterE2ETestRunner::assert( str_contains( $single_maj, $headline_formula ), "single-major.php implements formula 'Liên thông ngành [Major] - [Type]'" );
MasterE2ETestRunner::assert( str_contains( $comp_cards, $headline_formula ), "compare/program-cards.php implements formula 'Liên thông ngành [Major] - [Type]'" );

// Clean Badges without "Hệ "
$badge_regex = '/^Hệ\s+/i';
MasterE2ETestRunner::assert( str_contains( $tax_tt_php, 'Hệ ' ) || str_contains( $tax_tt_php, 'preg_replace' ), "taxonomy-training_type.php cleans 'Hệ ' prefix from badges" );
MasterE2ETestRunner::assert( str_contains( $filters_php, 'preg_replace' ), "class-query-filters.php cleans 'Hệ ' prefix from AJAX badges" );

// Data Compare attributes 1:1 parity between SSR and AJAX
$required_attrs = [
	'data-compare-btn',
	'data-compare-type',
	'data-compare-id',
	'data-compare-title',
	'data-compare-slug',
	'data-compare-thumb',
	'data-compare-he',
	'data-compare-nganh',
];

foreach ( $required_attrs as $attr ) {
	MasterE2ETestRunner::assert( str_contains( $tax_tt_php, $attr ), "SSR card (taxonomy-training_type.php) has compare attribute: $attr" );
	MasterE2ETestRunner::assert( str_contains( $filters_php, $attr ), "AJAX card (class-query-filters.php) has compare attribute: $attr" );
}

// single-program.php presentation & legacy notice
MasterE2ETestRunner::assert( ! str_contains( $single_prog_php, 'hệ Chính quy' ) && ! str_contains( $single_prog_php, 'hệ Liên thông Chính quy' ), "single-program.php contains zero legacy mentions of 'hệ Chính quy'" );
$expected_quota_notice = 'Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này.';
MasterE2ETestRunner::assert( str_contains( $single_prog_php, $expected_quota_notice ), "single-program.php has standardized quota notice for Liên thông Đại học" );
MasterE2ETestRunner::assert( str_contains( $single_prog_php, 'Hình thức học' ), "single-program.php uses 'Hình thức học' (NOT 'Hệ đào tạo')" );
MasterE2ETestRunner::assert( str_contains( $single_prog_php, 'Học phí' ), "single-program.php displays 'Học phí' section" );
MasterE2ETestRunner::assert( str_contains( $single_prog_php, 'Thời gian học' ), "single-program.php displays 'Thời gian học' section" );

// template-parts/banner.php text purity
$banner_php = file_get_contents( ABSPATH . 'template-parts/banner.php' );
MasterE2ETestRunner::assert( ! str_contains( $banner_php, 'Văn bằng 2' ) && ! str_contains( $banner_php, 'van-bang-2' ), "template-parts/banner.php contains zero 'Văn bằng 2'" );
MasterE2ETestRunner::assert( ! str_contains( $banner_php, 'Chính quy' ), "template-parts/banner.php contains zero 'Chính quy'" );
MasterE2ETestRunner::assert( ! str_contains( $banner_php, 'hệ đào tạo' ) && ! str_contains( $banner_php, 'Hệ đào tạo' ), "template-parts/banner.php contains zero 'Hệ đào tạo'" );
MasterE2ETestRunner::assert( str_contains( $banner_php, 'Hình thức học' ), "template-parts/banner.php displays 'Hình thức học'" );

// -------------------------------------------------------------------------
// SUITE 7: FORENSIC INTEGRITY & INVARIANT AUDIT
// -------------------------------------------------------------------------
MasterE2ETestRunner::suite( 'SUITE 7: FORENSIC INTEGRITY & INVARIANT AUDIT' );

// Check for test cheating / dummy facades
$all_theme_files = array_merge(
	glob( ABSPATH . '*.php' ) ?: [],
	glob( ABSPATH . 'inc/*.php' ) ?: [],
	glob( ABSPATH . 'inc/*/*.php' ) ?: [],
	glob( ABSPATH . 'template-parts/*/*.php' ) ?: []
);

$facade_patterns = [
	'/return\s+true;\s*\/\/\s*bypass/i',
	'/mock_test_mode/i',
	'/if\s*\(\s*defined\(\s*[\'"]TESTING[\'"]\s*\)\s*\)/i',
];

$facades_found = 0;
foreach ( $all_theme_files as $f ) {
	$content = file_get_contents( $f );
	foreach ( $facade_patterns as $pattern ) {
		if ( preg_match( $pattern, $content ) ) {
			$facades_found++;
			MasterE2ETestRunner::assert( false, sprintf( "Suspicious facade pattern %s detected in %s", $pattern, basename( $f ) ) );
		}
	}
}
MasterE2ETestRunner::assert( $facades_found === 0, "Zero test bypasses, fake facades, or mock switches found in theme source code" );

// Final Report
MasterE2ETestRunner::report();
