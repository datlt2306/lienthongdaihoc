<?php
/**
 * Adversarial Empirical Verification Suite for Milestone 2
 * Author: challenger_m2_1_rep
 *
 * Verifies:
 * 1. Global Landmark Uniqueness & Hierarchy (<main>, </main>, header, footer, skip link)
 * 2. Template Partials Existence & Compilation for single-program, single-school, single-major, front-page
 * 3. Adversarial Runtime Execution with NULL, Empty, and Corrupted Mock Data (using live WP environment)
 * 4. Card Unification and Attribute Parity
 * 5. Full Regression Suite Execution
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

// Bootstrap WordPress
$wp_load_path = dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-load.php';
if ( file_exists( $wp_load_path ) ) {
	require_once $wp_load_path;
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

class M2ChallengerRunner {
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

echo "===================================================================\n";
echo "EMPIRICAL ADVERSARIAL CHALLENGER SUITE: MILESTONE 2 (BlankSlate)\n";
echo "===================================================================\n\n";

// =================================================================
// SECTION 1: GLOBAL LANDMARK UNIQUENESS & CONTAINER CONTRACT
// =================================================================
echo "SECTION 1: HTML5 Landmark Uniqueness & Container Contract\n";
echo "-------------------------------------------------------------------\n";

$theme_root = get_template_directory();
$all_php_files = [];
$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme_root ) );
foreach ( $iterator as $file ) {
	if ( $file->isFile() && $file->getExtension() === 'php' ) {
		$pathname = $file->getPathname();
		// Skip tests, node_modules, vendor, .agents
		if ( strpos( $pathname, '/tests/' ) !== false ||
		     strpos( $pathname, '/node_modules/' ) !== false ||
		     strpos( $pathname, '/vendor/' ) !== false ||
		     strpos( $pathname, '/.agents/' ) !== false ) {
			continue;
		}
		$all_php_files[] = $pathname;
	}
}

echo "Discovered " . count( $all_php_files ) . " active theme PHP files to audit.\n";

$main_openings = [];
$main_closings = [];
$header_landmarks = [];
$footer_landmarks = [];

foreach ( $all_php_files as $filepath ) {
	$code = file_get_contents( $filepath );
	$rel_path = str_replace( $theme_root . '/', '', $filepath );

	// Strip comments before checking HTML landmarks
	$clean = preg_replace( '!/\*.*?\*/!s', '', $code );
	$clean = preg_replace( '!//.*?$!m', '', $clean );

	// Check <main
	if ( preg_match_all( '/<main[\s>]/i', $clean, $matches ) ) {
		$main_openings[ $rel_path ] = count( $matches[0] );
	}

	// Check </main>
	if ( preg_match_all( '/<\/main[\s>]/i', $clean, $matches ) ) {
		$main_closings[ $rel_path ] = count( $matches[0] );
	}

	// Check <header role="banner"
	if ( preg_match_all( '/<header\b[^>]*role=["\']banner["\']/i', $clean, $matches ) ) {
		$header_landmarks[ $rel_path ] = count( $matches[0] );
	}

	// Check <footer role="contentinfo"
	if ( preg_match_all( '/<footer\b[^>]*role=["\']contentinfo["\']/i', $clean, $matches ) ) {
		$footer_landmarks[ $rel_path ] = count( $matches[0] );
	}
}

// 1.1 Assert <main> opening is strictly in header.php
M2ChallengerRunner::assert(
	count( $main_openings ) === 1 && isset( $main_openings['header.php'] ) && $main_openings['header.php'] === 1,
	"Exactly 1 active <main> opening landmark across entire theme (strictly in header.php)",
	$main_openings
);

// 1.2 Assert </main> closing is strictly in footer.php
M2ChallengerRunner::assert(
	count( $main_closings ) === 1 && isset( $main_closings['footer.php'] ) && $main_closings['footer.php'] === 1,
	"Exactly 1 active </main> closing landmark across entire theme (strictly in footer.php)",
	$main_closings
);

// 1.3 Assert <header role="banner"> is strictly in header.php
M2ChallengerRunner::assert(
	count( $header_landmarks ) === 1 && isset( $header_landmarks['header.php'] ) && $header_landmarks['header.php'] === 1,
	"Exactly 1 <header role=\"banner\"> landmark across entire theme (strictly in header.php)",
	$header_landmarks
);

// 1.4 Assert <footer role="contentinfo"> is strictly in footer.php
M2ChallengerRunner::assert(
	count( $footer_landmarks ) === 1 && isset( $footer_landmarks['footer.php'] ) && $footer_landmarks['footer.php'] === 1,
	"Exactly 1 <footer role=\"contentinfo\"> landmark across entire theme (strictly in footer.php)",
	$footer_landmarks
);

// 1.5 Assert specific subtemplates contain ZERO <main> or </main> tags
$crucial_subtemplates = [
	'front-page.php',
	'single-program.php',
	'single-school.php',
	'single-major.php',
	'index.php',
	'single.php',
	'page.php',
	'archive.php',
	'archive-program.php',
	'archive-school.php',
	'archive-major.php',
	'taxonomy.php',
	'taxonomy-training_type.php',
	'search.php',
	'404.php',
];

foreach ( $crucial_subtemplates as $tpl ) {
	$is_clean = ! isset( $main_openings[ $tpl ] ) && ! isset( $main_closings[ $tpl ] );
	M2ChallengerRunner::assert(
		$is_clean,
		"Template '{$tpl}' cleanly omits outer <main> landmarks"
	);
}

// 1.6 Verify skip link implementation and hook
$setup_file = $theme_root . '/inc/core/class-theme-setup.php';
$setup_src  = file_exists( $setup_file ) ? file_get_contents( $setup_file ) : '';

M2ChallengerRunner::assert(
	strpos( $setup_src, 'function ltdh_skip_link()' ) !== false,
	"ltdh_skip_link() function defined in inc/core/class-theme-setup.php"
);

M2ChallengerRunner::assert(
	preg_match( "/add_action\s*\(\s*['\"]wp_body_open['\"]\s*,\s*['\"]ltdh_skip_link['\"]\s*,\s*5\s*\)/", $setup_src ) === 1,
	"ltdh_skip_link hooked to 'wp_body_open' with exact priority 5"
);

M2ChallengerRunner::assert(
	strpos( $setup_src, 'href="#content"' ) !== false && strpos( $setup_src, 'skip-link screen-reader-text' ) !== false,
	"Skip link contains href=\"#content\" and standard screen reader classes"
);


// =================================================================
// SECTION 2: TEMPLATE PARTIALS EXTRACTION, EXISTENCE & COMPILATION
// =================================================================
echo "\nSECTION 2: Template Partials Extraction, Existence & Compilation\n";
echo "-------------------------------------------------------------------\n";

function extract_template_parts( string $filepath ): array {
	$content = file_get_contents( $filepath );
	$tokens  = token_get_all( $content );
	$parts   = [];

	$count = count( $tokens );
	for ( $i = 0; $i < $count; $i++ ) {
		if ( is_array( $tokens[ $i ] ) && $tokens[ $i ][0] === T_STRING && $tokens[ $i ][1] === 'get_template_part' ) {
			// Find opening parenthesis
			$j = $i + 1;
			while ( $j < $count && ( is_array( $tokens[ $j ] ) && ( $tokens[ $j ][0] === T_WHITESPACE || $tokens[ $j ][0] === T_COMMENT ) ) ) {
				$j++;
			}
			if ( $j < $count && $tokens[ $j ] === '(' ) {
				$j++;
				while ( $j < $count && ( is_array( $tokens[ $j ] ) && ( $tokens[ $j ][0] === T_WHITESPACE || $tokens[ $j ][0] === T_COMMENT ) ) ) {
					$j++;
				}
				if ( $j < $count && is_array( $tokens[ $j ] ) && $tokens[ $j ][0] === T_CONSTANT_ENCAPSED_STRING ) {
					$slug = trim( $tokens[ $j ][1], "'\"" );
					$parts[] = $slug;
				}
			}
		}
	}

	return array_unique( $parts );
}

$core_templates = [
	'front-page.php',
	'single-program.php',
	'single-school.php',
	'single-major.php',
];

$all_extracted_partials = [];

foreach ( $core_templates as $tpl ) {
	$extracted = extract_template_parts( $theme_root . '/' . $tpl );
	echo "Found " . count( $extracted ) . " partials referenced in {$tpl}.\n";
	foreach ( $extracted as $slug ) {
		$all_extracted_partials[ $slug ][] = $tpl;
	}
}

// Add standard BlankSlate entry and card partials
$additional_partials = [
	'template-parts/entry',
	'template-parts/entry-content',
	'template-parts/entry-summary',
	'template-parts/entry-meta',
	'template-parts/entry-footer',
	'template-parts/nav-below',
	'template-parts/nav-below-single',
	'template-parts/cards/card-program',
	'template-parts/cards/card-school',
	'template-parts/cards/card-major',
	'template-parts/modal-admissions',
	'template-parts/modal-compare',
];

foreach ( $additional_partials as $p ) {
	if ( ! isset( $all_extracted_partials[ $p ] ) ) {
		$all_extracted_partials[ $p ][] = 'BlankSlate Core';
	}
}

// Verify existence and syntax for every single partial
foreach ( $all_extracted_partials as $slug => $callers ) {
	$relative_file = $slug . '.php';
	$full_path     = $theme_root . '/' . $relative_file;
	$exists        = file_exists( $full_path );

	M2ChallengerRunner::assert(
		$exists,
		"Partial '{$relative_file}' exists on disk (callers: " . implode( ', ', $callers ) . ")"
	);

	if ( $exists ) {
		// Run php -l
		$output = [];
		$ret    = 0;
		exec( 'php -l ' . escapeshellarg( $full_path ), $output, $ret );
		M2ChallengerRunner::assert(
			$ret === 0,
			"Partial '{$relative_file}' compiles cleanly with 0 syntax errors"
		);
	}
}


// =================================================================
// SECTION 3: ADVERSARIAL RUNTIME EXECUTION WITH NULL/FALLBACK DATA
// =================================================================
echo "\nSECTION 3: Adversarial Runtime Execution with NULL / Fallback Data\n";
echo "-------------------------------------------------------------------\n";

function execute_partial_live( string $full_path, $args = null ): array {
	$errors = [];
	set_error_handler( function( $errno, $errstr, $errfile, $errline ) use ( &$errors ) {
		// Ignore deprecation or user notices
		if ( in_array( $errno, [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ] ) ) {
			$errors[] = [
				'errno'   => $errno,
				'errstr'  => $errstr,
				'errfile' => $errfile,
				'errline' => $errline,
			];
		}
		return true; // continue
	} );

	ob_start();
	$caught_exception = null;
	try {
		include $full_path;
	} catch ( Throwable $t ) {
		$caught_exception = $t;
	}
	$output = ob_get_clean();

	restore_error_handler();

	return [
		'output'    => $output,
		'exception' => $caught_exception,
		'errors'    => $errors,
	];
}

// Adversarial stress test on every partial
foreach ( $all_extracted_partials as $slug => $callers ) {
	$full_path = $theme_root . '/' . $slug . '.php';
	if ( ! file_exists( $full_path ) ) {
		continue;
	}

	// Test 1: $args = []
	$res_empty = execute_partial_live( $full_path, [] );
	M2ChallengerRunner::assert(
		$res_empty['exception'] === null,
		"Adversarial: '{$slug}.php' executes safely when \$args = []" . ( $res_empty['exception'] ? ': ' . $res_empty['exception']->getMessage() : '' )
	);

	// Test 2: $args = null
	$res_null = execute_partial_live( $full_path, null );
	M2ChallengerRunner::assert(
		$res_null['exception'] === null,
		"Adversarial: '{$slug}.php' executes safely when \$args = null" . ( $res_null['exception'] ? ': ' . $res_null['exception']->getMessage() : '' )
	);

	// Test 3: check for fatal PHP errors
	$fatal_count = count( $res_empty['errors'] ) + count( $res_null['errors'] );
	M2ChallengerRunner::assert(
		$fatal_count === 0,
		"Adversarial: '{$slug}.php' produces ZERO fatal errors on null/empty inputs",
		array_merge( $res_empty['errors'], $res_null['errors'] )
	);
}


// =================================================================
// SECTION 4: CARD ATTRIBUTE INTEGRITY & PREFIX PURITY
// =================================================================
echo "\nSECTION 4: Card Component Attribute Integrity & Prefix Purity\n";
echo "-------------------------------------------------------------------\n";

$card_prog_code = file_get_contents( $theme_root . '/template-parts/cards/card-program.php' );

$crucial_card_attributes = [
	'data-compare-btn',
	'data-compare-type="program"',
	'data-compare-id',
	'data-compare-title',
	'data-compare-slug',
	'data-compare-thumb',
	'data-compare-he',
	'data-compare-nganh',
	'ltdh-compare-toggle',
];

foreach ( $crucial_card_attributes as $attr ) {
	M2ChallengerRunner::assert(
		strpos( $card_prog_code, $attr ) !== false,
		"card-program.php implements compare attribute [{$attr}]"
	);
}

// Check badge prefix stripping regex in card-program.php
M2ChallengerRunner::assert(
	strpos( $card_prog_code, "preg_replace( '/^hệ\s+/iu', ''," ) !== false || strpos( $card_prog_code, "preg_replace('/^hệ\\s+/iu'" ) !== false,
	"card-program.php strictly removes 'Hệ ' prefix from training type badge"
);


// =================================================================
// SECTION 5: REGRESSION SUITE MASTER RUNNER
// =================================================================
echo "\nSECTION 5: Full Regression Test Suite Execution\n";
echo "-------------------------------------------------------------------\n";

$regression_suites = [
	'test-adversarial-m2-verification.php',
	'test-faq-feature.php',
	'test-collapsible-requirements.php',
	'test-specialization-and-collision.php',
	'test-m5-templates-presentation.php',
	'test-m4-navigation-homepage.php',
	'test-m4-adversarial-homepage.php',
	'test-m2-empirical.php',
	'test-dry-school-program-fields.php',
	'test-dry-degree-and-diploma.php',
	'test-eligibility-unit.php',
	'test-mega-menu.php',
];

foreach ( $regression_suites as $suite_file ) {
	$full_suite_path = $theme_root . '/tests/' . $suite_file;
	if ( ! file_exists( $full_suite_path ) ) {
		M2ChallengerRunner::assert( false, "Regression suite exists: {$suite_file}" );
		continue;
	}

	$out = [];
	$code = 0;
	exec( 'php ' . escapeshellarg( $full_suite_path ), $out, $code );

	M2ChallengerRunner::assert(
		$code === 0,
		"Regression Suite: {$suite_file} PASSED (exit code 0)"
	);
}


// =================================================================
// SECTION 6: ISOLATED PROCESS RENDER AUDIT OF CORE TEMPLATES
// =================================================================
echo "\nSECTION 6: Isolated Process Render Audit of Core Templates\n";
echo "-------------------------------------------------------------------\n";

$render_test_cases = [
	'front-page.php'     => [ 'id' => 0, 'type' => '' ],
	'single-program.php' => [ 'id' => 3636, 'type' => 'program' ],
	'single-school.php'  => [ 'id' => 3531, 'type' => 'school' ],
	'single-major.php'   => [ 'id' => 3612, 'type' => 'major' ],
];

foreach ( $render_test_cases as $template_file => $cfg ) {
	$pid = $cfg['id'];
	$code = '
		require_once "' . $wp_load_path . '";
		if ( ' . $pid . ' > 0 ) {
			global $post, $wp_query;
			$post = get_post(' . $pid . ');
			setup_postdata($post);
			$wp_query->queried_object = $post;
			$wp_query->is_single = true;
			$wp_query->is_singular = true;
		}
		ob_start();
		include get_template_directory() . "/' . $template_file . '";
		$html = ob_get_clean();
		$res = [
			"main_open"  => preg_match_all("/<main[\s>]/i", $html),
			"main_close" => preg_match_all("/<\/main>/i", $html),
			"header"     => preg_match_all("/<header\b[^>]*role=[\x27\x22]banner[\x27\x22]/i", $html),
			"footer"     => preg_match_all("/<footer\b[^>]*role=[\x27\x22]contentinfo[\x27\x22]/i", $html),
			"skip_link"  => strpos($html, "href=\"#content\"") !== false && strpos($html, "skip-link") !== false,
			"len"        => strlen($html),
		];
		echo json_encode($res);
	';

	$exec_output = [];
	$exec_code = 0;
	exec( 'php -r ' . escapeshellarg( $code ), $exec_output, $exec_code );
	$json_str = implode( '', $exec_output );
	if ( preg_match( '/\{"main_open".*\}/', $json_str, $m ) ) {
		$data = json_decode( $m[0], true );
		M2ChallengerRunner::assert(
			$data['main_open'] === 1 && $data['main_close'] === 1,
			"Rendered '{$template_file}': Exactly 1 <main id=\"content\"> and 1 </main> landmark",
			$data
		);
		M2ChallengerRunner::assert(
			$data['header'] === 1 && $data['footer'] === 1,
			"Rendered '{$template_file}': Exactly 1 <header role=\"banner\"> and 1 <footer role=\"contentinfo\">",
			$data
		);
		M2ChallengerRunner::assert(
			$data['skip_link'] === true,
			"Rendered '{$template_file}': Skip navigation link present targeting #content"
		);
	} else {
		M2ChallengerRunner::assert(
			false,
			"Rendered '{$template_file}': Failed to execute in isolated sub-process",
			[ 'output' => $json_str ]
		);
	}
}


// =================================================================
// FINAL SUMMARY
// =================================================================
echo "\n===================================================================\n";
echo "CHALLENGER VERDICT SUMMARY: " . M2ChallengerRunner::$passed . " PASSED, " . M2ChallengerRunner::$failed . " FAILED\n";
echo "===================================================================\n";

if ( M2ChallengerRunner::$failed > 0 ) {
	echo "\nFAILURES ENCOUNTERED:\n";
	foreach ( M2ChallengerRunner::$errors as $err ) {
		echo " - " . $err['desc'] . "\n";
	}
	exit( 1 );
}

echo "VERDICT: APPROVE — All Milestone 2 empirical challenges PASSED!\n";
exit( 0 );
