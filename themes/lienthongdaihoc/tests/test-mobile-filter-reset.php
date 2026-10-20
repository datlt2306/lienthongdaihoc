<?php
/**
 * Test Suite: Mobile Filter Reset on Program and Major Archive Pages
 */

require_once __DIR__ . '/../../../../wp-load.php';

echo "\n==================================================\n";
echo "STARTING TEST: MOBILE FILTER RESET (PROGRAM & MAJOR)\n";
echo "==================================================\n\n";

$passed = 0;
$failed = 0;

function assert_test( $condition, $message ) {
	global $passed, $failed;
	if ( $condition ) {
		echo "✅ PASS: $message\n";
		$passed++;
	} else {
		echo "❌ FAIL: $message\n";
		$failed++;
	}
}

// ----------------------------------------------------
// 1. PHP Query Filters Verification
// ----------------------------------------------------
$query_filters_file = file_get_contents( __DIR__ . '/../inc/core/class-query-filters.php' );
assert_test(
	strpos( $query_filters_file, '$card_type_slug' ) !== false,
	'class-query-filters.php uses $card_type_slug in the card loop to prevent overwriting $type_slug'
);

// Simulate AJAX filter programs with empty he
$_POST['he'] = '';
$_POST['s'] = '';
$_POST['khu_vuc'] = '';
$_POST['truong'] = '';
$_POST['nganh'] = '';
$_POST['sort'] = '';
$_POST['paged'] = 1;

try {
	// Call AJAX directly by capturing wp_send_json_success
	add_filter( 'wp_die_ajax_handler', function() {
		return function( $message ) { throw new Exception( $message ); };
	} );

	ob_start();
	try {
		ltdh_ajax_filter_programs();
	} catch ( Exception $e ) {
		// Output is printed before exit
	}
	$ajax_raw = ob_get_clean();
	$ajax_res = json_decode( $ajax_raw, true );

	if ( $ajax_res && isset( $ajax_res['data'] ) ) {
		assert_test(
			$ajax_res['data']['has_active_filters'] === false,
			'AJAX filter programs returns has_active_filters = false when no filters selected'
		);
		assert_test(
			$ajax_res['data']['active_filter_count'] === 0,
			'AJAX filter programs returns active_filter_count = 0 when no filters selected'
		);
		assert_test(
			$ajax_res['data']['chips_html'] === '',
			'AJAX filter programs returns empty chips_html when no filters selected'
		);
	} else {
		assert_test( false, 'AJAX filter programs response parseable' );
	}
} catch ( Exception $e ) {
	echo "Notice: " . $e->getMessage() . "\n";
}

// ----------------------------------------------------
// 2. archive-program.php verification
// ----------------------------------------------------
$prog_template = file_get_contents( __DIR__ . '/../archive-program.php' );

assert_test(
	strpos( $prog_template, 'touch-action: manipulation;' ) !== false,
	'archive-program.php drawer sticky footer uses touch-action: manipulation'
);

assert_test(
	strpos( $prog_template, 'onTouchMoveLock(e) {' ) !== false &&
	strpos( $prog_template, "e.target.closest('button, a, input, select, label')" ) !== false,
	'archive-program.php onTouchMoveLock bypasses interactive elements'
);

assert_test(
	strpos( $prog_template, 'resetBtn.addEventListener(\'click\', closeDrawer);' ) !== false,
	'archive-program.php binds resetBtn to closeDrawer'
);

assert_test(
	strpos( $prog_template, 'js-ltdh-reset-filter cursor-pointer bg-brand-primary' ) !== false,
	'archive-program.php empty state reset link includes js-ltdh-reset-filter'
);

// ----------------------------------------------------
// 3. archive-major.php verification
// ----------------------------------------------------
$major_template = file_get_contents( __DIR__ . '/../archive-major.php' );

assert_test(
	strpos( $major_template, 'touch-action: manipulation;' ) !== false,
	'archive-major.php drawer sticky footer uses touch-action: manipulation'
);

assert_test(
	strpos( $major_template, 'onTouchMoveLock(e) {' ) !== false &&
	strpos( $major_template, "e.target.closest('button, a, input, select, label')" ) !== false,
	'archive-major.php onTouchMoveLock bypasses interactive elements'
);

assert_test(
	strpos( $major_template, 'resetBtn.onclick = closeDrawer;' ) !== false,
	'archive-major.php binds resetBtn to closeDrawer'
);

// ----------------------------------------------------
// 4. assets/js/main.js verification
// ----------------------------------------------------
$main_js = file_get_contents( __DIR__ . '/../assets/js/main.js' );

assert_test(
	strpos( $main_js, 'ltdhDrawerTouchMoveLock(e) {' ) !== false &&
	strpos( $main_js, "if (e.target.closest('button, a, input, select, label')) return;" ) !== false,
	'main.js ltdhDrawerTouchMoveLock bypasses interactive elements'
);

assert_test(
	strpos( $main_js, 'drawerResetBtn._boundProgramReset' ) !== false,
	'main.js binds ltdh-drawer-reset-btn with auto-close in initProgramArchiveFilter'
);

assert_test(
	strpos( $main_js, 'drawerResetBtn._boundMajorReset' ) !== false,
	'main.js binds ltdh-major-drawer-reset-btn with auto-close in initMajorArchiveFilter'
);

assert_test(
	strpos( $main_js, 'closeMajorFilterDrawer();' ) !== false,
	'main.js calls closeMajorFilterDrawer() on delegated major filter reset'
);

echo "\n==================================================\n";
echo "SUMMARY: $passed PASSED, $failed FAILED\n";
echo "==================================================\n";

if ( $failed > 0 ) {
	exit( 1 );
}
