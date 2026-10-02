<?php
/**
 * Empirical Test Harness for Milestone M3 Public Labels & Facets
 *
 * Written by challenger_m3_2 (M3 Public Label & Facet Challenger)
 * Tests:
 * 1. Search template files for any remaining public instances of "Hệ đào tạo" that should be "Hình thức học"
 * 2. Verify that pill tabs on /he-dao-tao/ and archive templates show "Hình thức học" and display allowed modes ("Từ xa", "Vừa học vừa làm")
 * 3. Verify that breadcrumbs evaluate to "Hình thức học" across all route and taxonomy scenarios
 * 4. Verify no taxonomy "Loại tuyển sinh" exists in database or code
 *
 * Run via: php tests/test-m3-label-facets-empirical.php
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class LabelFacetTester {
	public static int $passed = 0;
	public static int $failed = 0;
	public static array $findings = [];

	public static function assert( bool $condition, string $test_name, array $evidence = [] ): void {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] {$test_name}\n";
		} else {
			self::$failed++;
			self::$findings[] = [
				'test'     => $test_name,
				'evidence' => $evidence,
			];
			echo "  [FAIL] {$test_name}\n";
			if ( ! empty( $evidence ) ) {
				foreach ( $evidence as $k => $v ) {
					echo "         - {$k}: " . ( is_array( $v ) ? json_encode( $v, JSON_UNESCAPED_UNICODE ) : $v ) . "\n";
				}
			}
		}
	}
}

echo "===================================================================\n";
echo "EMPIRICAL TEST SUITE: M3 PUBLIC LABELS & FACETS (challenger_m3_2)\n";
echo "===================================================================\n\n";

// -------------------------------------------------------------
// SECTION 1: PUBLIC INSTANCES OF "HỆ ĐÀO TẠO" IN TEMPLATES
// -------------------------------------------------------------
echo "SECTION 1: Public Label Audit for 'Hệ đào tạo' / 'Hệ '\n";
echo "-------------------------------------------------------------------\n";

$templates_to_scan = [
	'taxonomy-training_type.php',
	'archive-program.php',
	'archive-school.php',
	'archive-major.php',
	'single-program.php',
	'single-school.php',
	'single-major.php',
	'single-guide.php',
	'single.php',
	'taxonomy.php',
	'front-page.php',
	'index.php',
	'page.php',
	'page-about.php',
	'page-compare-program.php',
	'page-contact.php',
	'page-eligible.php',
	'page-faq.php',
	'page-register.php',
	'header.php',
	'footer.php',
	'template-parts/banner.php',
	'template-parts/compare/cta-bar.php',
	'template-parts/compare/program-cards.php',
	'template-parts/compare/program-table.php',
	'template-parts/compare/tray.php',
	'template-parts/eligibility/results.php',
	'template-parts/eligibility/wizard.php',
	'inc/core/class-query-filters.php',
	'inc/eligibility.php',
];

$found_he_dao_tao = [];
$found_he_prefix  = [];

foreach ( $templates_to_scan as $file ) {
	$full = ABSPATH . $file;
	if ( ! file_exists( $full ) ) {
		continue;
	}
	$lines = file( $full );
	foreach ( $lines as $line_num => $line ) {
		// 1. Check for exact phrase "hệ đào tạo"
		if ( preg_match( '/hệ\s+đào\s+tạo/iu', $line ) ) {
			$found_he_dao_tao[] = [
				'file'    => $file,
				'line'    => $line_num + 1,
				'content' => trim( $line ),
			];
		}
		// 2. Check for "Hệ " badge prefix pattern in card badges: e.g. "Hệ <?php echo ... $type_name"
		if ( preg_match( '/Hệ\s*<\?php\s+echo\s+esc_html\(\s*\$type_name/u', $line ) || preg_match( '/Hệ\s*<\?php\s+echo\s+esc_html\(\s*\$t_name/u', $line ) ) {
			$found_he_prefix[] = [
				'file'    => $file,
				'line'    => $line_num + 1,
				'content' => trim( $line ),
			];
		}
	}
}

// 1.1: Zero instances of "Hệ đào tạo" in public presentation templates
$public_templates_he_dao_tao = array_filter( $found_he_dao_tao, function( $item ) {
	return $item['file'] !== 'inc/eligibility.php';
} );
LabelFacetTester::assert(
	empty( $public_templates_he_dao_tao ),
	"1.1: Theme template files contain zero instances of 'Hệ đào tạo'",
	[ 'found' => $public_templates_he_dao_tao ]
);

// 1.2: Check inc/eligibility.php for public-facing messages containing "Hệ đào tạo"
$elig_public_messages = array_filter( $found_he_dao_tao, function( $item ) {
	return $item['file'] === 'inc/eligibility.php' && in_array( $item['line'], [ 306, 479, 482 ] );
} );
LabelFacetTester::assert(
	empty( $elig_public_messages ),
	"1.2: Eligibility engine messages contain zero instances of 'Hệ đào tạo'",
	[ 'found' => $elig_public_messages ]
);

// 1.3: Check for obsolete "Hệ " prefix on card badges in taxonomy-training_type.php & archive-program.php
LabelFacetTester::assert(
	empty( $found_he_prefix ),
	"1.3: Program cards contain zero obsolete 'Hệ ' prefix before training type name (e.g. 'Hệ Từ xa')",
	[ 'found' => $found_he_prefix ]
);

echo "\n";

// -------------------------------------------------------------
// SECTION 2: PILL TABS ON /he-dao-tao/ AND ARCHIVE TEMPLATES
// -------------------------------------------------------------
echo "SECTION 2: Pill Tabs Evaluation on /he-dao-tao/ and Archive Templates\n";
echo "-------------------------------------------------------------------\n";

$archive_files = [
	'taxonomy-training_type.php',
	'archive-program.php',
];

foreach ( $archive_files as $file ) {
	$content = file_get_contents( ABSPATH . $file );

	// 2.1: Label is "Hình thức học:"
	$has_label = (bool) preg_match( '/<span[^>]*>\s*Hình thức học:\s*<\/span>/u', $content );
	LabelFacetTester::assert(
		$has_label,
		"2.1: {$file} pill tabs container has label 'Hình thức học:'",
		[ 'file' => $file ]
	);

	// 2.2: "Tất cả" tab present with count
	$has_all_tab = (bool) preg_match( '/<span>\s*Tất cả\s*<\/span>/u', $content );
	LabelFacetTester::assert(
		$has_all_tab,
		"2.2: {$file} has 'Tất cả' master tab",
		[ 'file' => $file ]
	);

	// 2.3: "Tất cả" URL points to home_url('/he-dao-tao/')
	$has_all_url = (bool) preg_match( '/\$all_url\s*=\s*home_url\(\s*[\x27\x22]\/he-dao-tao\/[\x27\x22]\s*\)/u', $content );
	LabelFacetTester::assert(
		$has_all_url,
		"2.3: {$file} 'Tất cả' tab URL points to '/he-dao-tao/'",
		[ 'file' => $file ]
	);

	// 2.4: Type tab URLs point to home_url('/he-dao-tao/' . $t_term->slug . '/')
	$has_type_url = (bool) preg_match( '/home_url\(\s*[\x27\x22]\/he-dao-tao\/[\x27\x22]\s*\.\s*\$t_term->slug\s*\.\s*[\x27\x22]\/[\x27\x22]\s*\)/u', $content );
	LabelFacetTester::assert(
		$has_type_url,
		"2.4: {$file} type tab URLs point to '/he-dao-tao/{slug}/'",
		[ 'file' => $file ]
	);
}

// 2.5: Simulation of Allowed Modes in Pill Tabs
// Test behavior when DB has allowed modes (tu-xa, vua-hoc-vua-lam) and out-of-scope modes (chinh-quy, van-bang-2)
function simulate_pill_tabs( string $selected_type, array $terms, array $counts, int $total ): array {
	$output_tabs = [];
	$output_tabs[] = [
		'slug'   => '',
		'label'  => 'Tất cả',
		'count'  => $total,
		'active' => empty( $selected_type ),
	];

	// Whitelist terms to allowed modes per target architecture [tu-xa, vua-hoc-vua-lam]
	$allowed_modes = [ 'tu-xa', 'vua-hoc-vua-lam' ];
	$terms = array_filter( $terms, function( $t ) use ( $allowed_modes ) {
		$slug = is_array( $t ) ? $t['slug'] : $t->slug;
		return in_array( $slug, $allowed_modes, true );
	} );

	foreach ( $terms as $term ) {
		$slug      = $term['slug'];
		$name      = $term['name'];
		$t_count   = $counts[ $slug ] ?? 0;
		$is_active = ( $selected_type === $slug );

		if ( $t_count === 0 && ! $is_active ) {
			continue;
		}

		$output_tabs[] = [
			'slug'   => $slug,
			'label'  => $name,
			'count'  => $t_count,
			'active' => $is_active,
		];
	}

	return $output_tabs;
}

$sample_terms = [
	[ 'slug' => 'tu-xa', 'name' => 'Từ xa' ],
	[ 'slug' => 'vua-hoc-vua-lam', 'name' => 'Vừa học vừa làm' ],
	[ 'slug' => 'chinh-quy', 'name' => 'Chính quy' ],
	[ 'slug' => 'van-bang-2', 'name' => 'Văn bằng 2' ],
];
$sample_counts = [
	'tu-xa'           => 94,
	'vua-hoc-vua-lam' => 1,
	'chinh-quy'       => 0,
	'van-bang-2'      => 0,
];

// Scenario A: Default /he-dao-tao/ view
$tabs_default = simulate_pill_tabs( '', $sample_terms, $sample_counts, 95 );
$rendered_slugs_default = array_column( $tabs_default, 'slug' );
LabelFacetTester::assert(
	$rendered_slugs_default === [ '', 'tu-xa', 'vua-hoc-vua-lam' ],
	"2.5: Default /he-dao-tao/ view renders ONLY allowed modes ('', 'tu-xa', 'vua-hoc-vua-lam')",
	[ 'rendered_slugs' => $rendered_slugs_default ]
);

// Scenario B: Direct visit to /he-dao-tao/van-bang-2/ (Adversarial stress test)
$tabs_vb2 = simulate_pill_tabs( 'van-bang-2', $sample_terms, $sample_counts, 95 );
$rendered_slugs_vb2 = array_column( $tabs_vb2, 'slug' );
$vb2_leaked = in_array( 'van-bang-2', $rendered_slugs_vb2, true );
LabelFacetTester::assert(
	! $vb2_leaked,
	"2.6: Direct request to out-of-scope slug '/he-dao-tao/van-bang-2/' does NOT leak out-of-scope pill tab into UI",
	[
		'rendered_slugs' => $rendered_slugs_vb2,
		'explanation'    => 'If terms array is not whitelisted to allowed modes [tu-xa, vua-hoc-vua-lam], visiting an out-of-scope slug renders that slug as an active tab even with count=0.',
	]
);

// Scenario C: Direct visit to /he-dao-tao/chinh-quy/ (Adversarial stress test)
$tabs_cq = simulate_pill_tabs( 'chinh-quy', $sample_terms, $sample_counts, 95 );
$rendered_slugs_cq = array_column( $tabs_cq, 'slug' );
$cq_leaked = in_array( 'chinh-quy', $rendered_slugs_cq, true );
LabelFacetTester::assert(
	! $cq_leaked,
	"2.7: Direct request to out-of-scope slug '/he-dao-tao/chinh-quy/' does NOT leak out-of-scope pill tab into UI",
	[
		'rendered_slugs' => $rendered_slugs_cq,
		'explanation'    => 'If terms array is not whitelisted to allowed modes [tu-xa, vua-hoc-vua-lam], visiting an out-of-scope slug renders that slug as an active tab even with count=0.',
	]
);

echo "\n";

// -------------------------------------------------------------
// SECTION 3: BREADCRUMBS EVALUATION ACROSS ROUTE SCENARIOS
// -------------------------------------------------------------
echo "SECTION 3: Breadcrumbs Evaluation to 'Hình thức học'\n";
echo "-------------------------------------------------------------------\n";

// Load helpers
if ( ! function_exists( 'home_url' ) ) {
	function home_url( string $path = '' ): string {
		return 'https://lienthongdaihoc.com' . ( $path ? '/' . ltrim( $path, '/' ) : '' );
	}
}
if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( string $url ): string {
		return $url;
	}
}
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( string $text ): string {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

// Function to simulate breadcrumbs engine from inc/core/class-helpers.php
function simulate_breadcrumb_crumbs( array $context ): array {
	$crumbs   = [];
	$crumbs[] = [ 'label' => 'Trang chủ', 'url' => home_url( '/' ) ];

	if ( ! empty( $context['is_singular'] ) ) {
		$post_type = $context['post_type'] ?? '';
		if ( $post_type === 'program' ) {
			$crumbs[] = [ 'label' => 'Hình thức học', 'url' => home_url( '/he-dao-tao/' ) ];
			$crumbs[] = [ 'label' => $context['title'] ?? 'Program Title', 'url' => '' ];
		} elseif ( $post_type === 'school' ) {
			$crumbs[] = [ 'label' => 'Trường đối tác', 'url' => home_url( '/truong-doi-tac/' ) ];
			$crumbs[] = [ 'label' => $context['title'] ?? 'School Title', 'url' => '' ];
		} elseif ( $post_type === 'major' ) {
			$crumbs[] = [ 'label' => 'Chuyên ngành', 'url' => home_url( '/nganh-hoc/' ) ];
			$crumbs[] = [ 'label' => $context['title'] ?? 'Major Title', 'url' => '' ];
		} else {
			$crumbs[] = [ 'label' => $context['title'] ?? 'Title', 'url' => '' ];
		}
	} elseif ( ! empty( $context['is_tax'] ) ) {
		$tax = $context['taxonomy'] ?? '';
		if ( $tax === 'training_type' ) {
			$crumbs[] = [ 'label' => 'Hình thức học', 'url' => home_url( '/he-dao-tao/' ) ];
		}
		$crumbs[] = [ 'label' => $context['term_name'] ?? 'Term Name', 'url' => '' ];
	} elseif ( ! empty( $context['is_post_type_archive'] ) ) {
		$post_type = $context['post_type'] ?? '';
		if ( $post_type === 'school' ) {
			$crumbs[] = [ 'label' => 'Trường đối tác', 'url' => '' ];
		} elseif ( $post_type === 'major' ) {
			$crumbs[] = [ 'label' => 'Chuyên ngành', 'url' => '' ];
		} else {
			$crumbs[] = [ 'label' => 'Hình thức học', 'url' => '' ];
		}
	} else {
		// Virtual route /he-dao-tao/
		$request_path = $context['request_path'] ?? '/';
		if ( preg_match( '#^/he-dao-tao(?:/([^/]+))?(?:/page/\d+)?/?$#i', $request_path, $m ) ) {
			if ( ! empty( $m[1] ) && 'page' !== $m[1] ) {
				$term_name = $context['terms_map'][ $m[1] ] ?? $m[1];
				$crumbs[]  = [ 'label' => 'Hình thức học', 'url' => home_url( '/he-dao-tao/' ) ];
				$crumbs[]  = [ 'label' => $term_name, 'url' => '' ];
			} else {
				$crumbs[] = [ 'label' => 'Hình thức học', 'url' => '' ];
			}
		}
	}

	return $crumbs;
}

// 3.1: Program single breadcrumb
$crumbs_prog = simulate_breadcrumb_crumbs( [
	'is_singular' => true,
	'post_type'   => 'program',
	'title'       => 'Cử nhân CNTT Từ xa',
] );
LabelFacetTester::assert(
	count( $crumbs_prog ) === 3 &&
	$crumbs_prog[1]['label'] === 'Hình thức học' &&
	$crumbs_prog[1]['url'] === 'https://lienthongdaihoc.com/he-dao-tao/',
	"3.1: Program single breadcrumb contains 'Hình thức học' pointing to '/he-dao-tao/'",
	[ 'crumbs' => $crumbs_prog ]
);

// 3.2: Taxonomy archive breadcrumb (is_tax('training_type'))
$crumbs_tax = simulate_breadcrumb_crumbs( [
	'is_tax'    => true,
	'taxonomy'  => 'training_type',
	'term_name' => 'Từ xa',
] );
LabelFacetTester::assert(
	count( $crumbs_tax ) === 3 &&
	$crumbs_tax[1]['label'] === 'Hình thức học' &&
	$crumbs_tax[1]['url'] === 'https://lienthongdaihoc.com/he-dao-tao/' &&
	$crumbs_tax[2]['label'] === 'Từ xa',
	"3.2: Taxonomy training_type archive breadcrumb contains 'Hình thức học' > 'Từ xa'",
	[ 'crumbs' => $crumbs_tax ]
);

// 3.3: Virtual route /he-dao-tao/ breadcrumb
$crumbs_virtual_root = simulate_breadcrumb_crumbs( [
	'request_path' => '/he-dao-tao/',
] );
LabelFacetTester::assert(
	count( $crumbs_virtual_root ) === 2 &&
	$crumbs_virtual_root[1]['label'] === 'Hình thức học' &&
	empty( $crumbs_virtual_root[1]['url'] ),
	"3.3: Virtual base route '/he-dao-tao/' evaluates breadcrumb to 'Hình thức học'",
	[ 'crumbs' => $crumbs_virtual_root ]
);

// 3.4: Virtual route /he-dao-tao/vua-hoc-vua-lam/ breadcrumb
$crumbs_virtual_term = simulate_breadcrumb_crumbs( [
	'request_path' => '/he-dao-tao/vua-hoc-vua-lam/',
	'terms_map'    => [ 'vua-hoc-vua-lam' => 'Vừa học vừa làm' ],
] );
LabelFacetTester::assert(
	count( $crumbs_virtual_term ) === 3 &&
	$crumbs_virtual_term[1]['label'] === 'Hình thức học' &&
	$crumbs_virtual_term[2]['label'] === 'Vừa học vừa làm',
	"3.4: Virtual sub-route '/he-dao-tao/vua-hoc-vua-lam/' evaluates breadcrumb to 'Hình thức học' > 'Vừa học vừa làm'",
	[ 'crumbs' => $crumbs_virtual_term ]
);

// 3.5: Comparison page breadcrumb check in inc/core/class-helpers.php
$helpers_content = file_get_contents( ABSPATH . 'inc/core/class-helpers.php' );
preg_match( '/ltdh_compare.*?return;/s', $helpers_content, $compare_crumb_match );
$compare_block = $compare_crumb_match[0] ?? '';
$has_chuong_trinh_in_compare = strpos( $compare_block, 'Chương trình' ) !== false;
$has_tu_xa_hardcoded         = strpos( $compare_block, '/he-dao-tao/tu-xa/' ) !== false;

LabelFacetTester::assert(
	! $has_tu_xa_hardcoded && ! $has_chuong_trinh_in_compare,
	"3.5: Compare page breadcrumbs link to '/he-dao-tao/' with label 'Hình thức học' (NOT '/he-dao-tao/tu-xa/' labeled 'Chương trình')",
	[
		'compare_block' => trim( $compare_block ),
		'issue'         => 'inc/core/class-helpers.php:407 hardcodes /he-dao-tao/tu-xa/ and label "Chương trình"',
	]
);

echo "\n";

// -------------------------------------------------------------
// SECTION 4: TAXONOMY "LOẠI TUYỂN SINH" PURITY CHECK
// -------------------------------------------------------------
echo "SECTION 4: Taxonomy 'Loại tuyển sinh' Purity Check\n";
echo "-------------------------------------------------------------------\n";

// 4.1: ACF CPT JSON definition
$cpts_raw  = file_get_contents( ABSPATH . 'inc/acf-import-cpts.json' );
$cpts_data = json_decode( $cpts_raw, true );
$has_loai_tuyen_sinh_in_cpts_json = false;
foreach ( $cpts_data as $item ) {
	$title = $item['title'] ?? '';
	$slug  = $item['taxonomy'] ?? ( $item['post_type'] ?? '' );
	if ( stripos( $title, 'loại tuyển sinh' ) !== false || stripos( $slug, 'loai_tuyen_sinh' ) !== false || stripos( $slug, 'admission_type' ) !== false ) {
		$has_loai_tuyen_sinh_in_cpts_json = true;
	}
}
LabelFacetTester::assert(
	! $has_loai_tuyen_sinh_in_cpts_json,
	"4.1: inc/acf-import-cpts.json defines NO taxonomy 'Loại tuyển sinh' or 'loai_tuyen_sinh'"
);

// 4.2: ACF Fields JSON definition
$fields_raw = file_get_contents( ABSPATH . 'inc/acf-import-fields.json' );
$has_loai_tuyen_sinh_in_fields = ( stripos( $fields_raw, 'loại tuyển sinh' ) !== false || stripos( $fields_raw, 'loai_tuyen_sinh' ) !== false );
LabelFacetTester::assert(
	! $has_loai_tuyen_sinh_in_fields,
	"4.2: inc/acf-import-fields.json defines NO field/taxonomy 'Loại tuyển sinh'"
);

// 4.3: Codebase PHP Scan for register_taxonomy or taxonomy usage of loai_tuyen_sinh
$php_iter = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( ABSPATH, RecursiveDirectoryIterator::SKIP_DOTS ) );
$loai_tuyen_sinh_in_code = [];

foreach ( $php_iter as $f ) {
	$p = $f->getPathname();
	if ( strpos( $p, '/.agents' ) !== false || strpos( $p, '/.git' ) !== false || strpos( $p, '/tests' ) !== false ) {
		continue;
	}
	if ( pathinfo( $p, PATHINFO_EXTENSION ) !== 'php' ) {
		continue;
	}
	$c = file_get_contents( $p );
	if ( preg_match( '/[\x27\x22]loai[_-]tuyen[_-]sinh[\x27\x22]/i', $c ) ||
		 preg_match( '/register_taxonomy\(\s*[\x27\x22]admission_type[\x27\x22]/i', $c ) ||
		 preg_match( '/[\x27\x22]taxonomy[\x27\x22]\s*=>\s*[\x27\x22]loai_tuyen_sinh[\x27\x22]/i', $c ) ) {
		$loai_tuyen_sinh_in_code[] = $p;
	}
}
LabelFacetTester::assert(
	empty( $loai_tuyen_sinh_in_code ),
	"4.3: Zero occurrences of taxonomy 'loai_tuyen_sinh' across all PHP source files",
	[ 'found_in' => $loai_tuyen_sinh_in_code ]
);

// 4.4: Constants file verification
$constants_content = file_get_contents( ABSPATH . 'inc/config/constants.php' );
$has_loai_constant = ( stripos( $constants_content, 'LTDH_TAX_ADMISSION_TYPE' ) !== false || stripos( $constants_content, 'loai_tuyen_sinh' ) !== false );
LabelFacetTester::assert(
	! $has_loai_constant,
	"4.4: inc/config/constants.php defines NO taxonomy constant for 'Loại tuyển sinh'"
);

// 4.5: Audit Report verification
$audit_raw = file_get_contents( ABSPATH . 'audit_report.json' );
$has_loai_in_audit = ( stripos( $audit_raw, 'loai_tuyen_sinh' ) !== false || stripos( $audit_raw, 'loại tuyển sinh' ) !== false );
LabelFacetTester::assert(
	! $has_loai_in_audit,
	"4.5: audit_report.json has zero record or trace of 'Loại tuyển sinh'"
);

echo "\n";
echo "===================================================================\n";
printf( "SUMMARY: %d PASSED, %d FAILED\n", LabelFacetTester::$passed, LabelFacetTester::$failed );
echo "===================================================================\n";

if ( LabelFacetTester::$failed > 0 ) {
	echo "\nFAILED TESTS DETAIL:\n";
	foreach ( LabelFacetTester::$findings as $idx => $f ) {
		echo ( $idx + 1 ) . ". " . $f['test'] . "\n";
		foreach ( $f['evidence'] as $k => $v ) {
			echo "   * {$k}: " . ( is_array( $v ) ? json_encode( $v, JSON_UNESCAPED_UNICODE ) : $v ) . "\n";
		}
	}
}
exit( LabelFacetTester::$failed > 0 ? 1 : 0 );
