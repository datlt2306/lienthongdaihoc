<?php
if ( php_sapi_name() !== 'cli' ) {
	http_response_code( 403 );
	die( 'Forbidden: CLI access only.' );
}

/**
 * Test Suite: Google reCAPTCHA Integration Verification
 *
 * Verifies server-side helpers, template markup injection, AJAX interceptors,
 * verification logic, and backward compatibility fallbacks.
 *
 * Usage:
 *   php -d mysqli.default_socket="/Users/ken/Library/Application Support/Local/run/_nsV9ehOj/mysql/mysqld.sock" tests/test-recaptcha.php
 */

require_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-load.php';

echo "==================================================\n";
echo "STARTING TEST SUITE: GOOGLE RECAPTCHA INTEGRATION\n";
echo "==================================================\n\n";

$tests_passed = 0;
$tests_failed = 0;

function run_test( string $description, callable $test_fn ) {
	global $tests_passed, $tests_failed;
	echo "Running: {$description} ... ";
	try {
		$result = $test_fn();
		if ( true === $result ) {
			echo "✅ PASS\n";
			$tests_passed++;
		} else {
			echo "❌ FAIL: Expected true, got " . var_export( $result, true ) . "\n";
			$tests_failed++;
		}
	} catch ( Throwable $e ) {
		echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
		$tests_failed++;
	}
}

// ----------------------------------------------------
// Suite 1: Function Existence & Contract
// ----------------------------------------------------
echo "--- SUITE 1: FUNCTION CONTRACTS ---\n";

run_test( 'Function ltdh_get_recaptcha_site_key exists', function() {
	return function_exists( 'ltdh_get_recaptcha_site_key' );
} );

run_test( 'Function ltdh_get_recaptcha_secret_key exists', function() {
	return function_exists( 'ltdh_get_recaptcha_secret_key' );
} );

run_test( 'Function ltdh_get_recaptcha_version exists', function() {
	return function_exists( 'ltdh_get_recaptcha_version' );
} );

run_test( 'Function ltdh_get_recaptcha_min_score exists', function() {
	return function_exists( 'ltdh_get_recaptcha_min_score' );
} );

run_test( 'Function ltdh_is_recaptcha_enabled exists', function() {
	return function_exists( 'ltdh_is_recaptcha_enabled' );
} );

run_test( 'Function ltdh_verify_recaptcha exists', function() {
	return function_exists( 'ltdh_verify_recaptcha' );
} );

run_test( 'Function ltdh_render_recaptcha_field exists', function() {
	return function_exists( 'ltdh_render_recaptcha_field' );
} );

// ----------------------------------------------------
// Suite 2: Default Unconfigured / Disabled State
// ----------------------------------------------------
echo "\n--- SUITE 2: GRACEFUL DEGRADATION WHEN UNCONFIGURED ---\n";

run_test( 'Unconfigured state returns empty keys without errors', function() {
	$site_key = ltdh_get_recaptcha_site_key();
	return is_string( $site_key );
} );

run_test( 'Unconfigured state renders empty field output', function() {
	ob_start();
	ltdh_render_recaptcha_field();
	$output = ob_get_clean();
	// If keys are empty, output must be empty so forms are not broken
	if ( ! ltdh_is_recaptcha_enabled() ) {
		return empty( $output );
	}
	return true;
} );

run_test( 'Verification passes through gracefully when reCAPTCHA is not enabled', function() {
	if ( ! ltdh_is_recaptcha_enabled() ) {
		// When disabled/unconfigured, normal users should never be blocked
		return ( true === ltdh_verify_recaptcha( '' ) );
	}
	return true;
} );

// ----------------------------------------------------
// Suite 3: Simulated Verification & Scoring Logic
// ----------------------------------------------------
echo "\n--- SUITE 3: VERIFICATION LOGIC & SCORE HANDLING ---\n";

run_test( 'Empty token fails when verification is forced with simulated enabled state', function() {
	// Temporarily simulate enabled via option
	$original_site = get_option( 'options_recaptcha_site_key' );
	$original_sec  = get_option( 'options_recaptcha_secret_key' );
	$original_en   = get_option( 'options_recaptcha_enabled' );

	update_option( 'options_recaptcha_site_key', 'test_site_key_123' );
	update_option( 'options_recaptcha_secret_key', 'test_secret_key_456' );
	update_option( 'options_recaptcha_enabled', 1 );

	$is_enabled = ltdh_is_recaptcha_enabled();
	$empty_fail = ( false === ltdh_verify_recaptcha( '', 'submit' ) );

	// Restore original state
	if ( false === $original_site ) delete_option( 'options_recaptcha_site_key' ); else update_option( 'options_recaptcha_site_key', $original_site );
	if ( false === $original_sec ) delete_option( 'options_recaptcha_secret_key' ); else update_option( 'options_recaptcha_secret_key', $original_sec );
	if ( false === $original_en ) delete_option( 'options_recaptcha_enabled' ); else update_option( 'options_recaptcha_enabled', $original_en );

	return $is_enabled && $empty_fail;
} );

run_test( 'reCAPTCHA v3 verify passes on score >= min_score and fails on low score', function() {
	// Backup current settings
	$cur_site = get_option( 'options_recaptcha_site_key' );
	$cur_sec  = get_option( 'options_recaptcha_secret_key' );
	$cur_en   = get_option( 'options_recaptcha_enabled' );
	$cur_ver  = get_option( 'options_recaptcha_version' );
	$cur_sc   = get_option( 'options_recaptcha_min_score' );

	// Setup test keys
	update_option( 'options_recaptcha_site_key', 'test_site_key_123' );
	update_option( 'options_recaptcha_secret_key', 'test_secret_key_456' );
	update_option( 'options_recaptcha_enabled', 1 );
	update_option( 'options_recaptcha_version', 'v3' );
	update_option( 'options_recaptcha_min_score', 0.5 );

	// Mock wp_remote_post using pre_http_request filter
	$mock_score = 0.9;
	$mock_success = true;

	$filter_cb = function( $pre, $args, $url ) use ( &$mock_score, &$mock_success ) {
		if ( strpos( $url, 'recaptcha/api/siteverify' ) !== false ) {
			return [
				'response' => [ 'code' => 200 ],
				'body'     => json_encode( [
					'success' => $mock_success,
					'score'   => $mock_score,
					'action'  => 'test_action',
				] ),
			];
		}
		return $pre;
	};

	add_filter( 'pre_http_request', $filter_cb, 10, 3 );

	// Test 1: Good score (0.9 >= 0.5) -> should return true
	$mock_score = 0.9;
	$res_pass = ltdh_verify_recaptcha( 'mock_valid_token', 'test_action' );

	// Test 2: Low score (0.2 < 0.5) -> should return false (bot blocked)
	$mock_score = 0.2;
	$res_fail_score = ltdh_verify_recaptcha( 'mock_bot_token', 'test_action' );

	// Test 3: Success false -> should return false
	$mock_success = false;
	$res_fail_success = ltdh_verify_recaptcha( 'mock_invalid_token', 'test_action' );

	remove_filter( 'pre_http_request', $filter_cb );

	// Restore original settings
	if ( false === $cur_site ) delete_option( 'options_recaptcha_site_key' ); else update_option( 'options_recaptcha_site_key', $cur_site );
	if ( false === $cur_sec ) delete_option( 'options_recaptcha_secret_key' ); else update_option( 'options_recaptcha_secret_key', $cur_sec );
	if ( false === $cur_en ) delete_option( 'options_recaptcha_enabled' ); else update_option( 'options_recaptcha_enabled', $cur_en );
	if ( false === $cur_ver ) delete_option( 'options_recaptcha_version' ); else update_option( 'options_recaptcha_version', $cur_ver );
	if ( false === $cur_sc ) delete_option( 'options_recaptcha_min_score' ); else update_option( 'options_recaptcha_min_score', $cur_sc );

	return ( true === $res_pass && false === $res_fail_score && false === $res_fail_success );
} );

// ----------------------------------------------------
// Suite 4: Markup & Template Inspection
// ----------------------------------------------------
echo "\n--- SUITE 4: TEMPLATE RECAPTCHA INTEGRATION ---\n";

run_test( 'Native form template includes ltdh_render_recaptcha_field', function() {
	$helpers_code = file_get_contents( get_template_directory() . '/inc/core/class-helpers.php' );
	return strpos( $helpers_code, "ltdh_render_recaptcha_field( 'native_lead' )" ) !== false;
} );

run_test( 'Widget consultation form includes ltdh_render_recaptcha_field', function() {
	$widgets_code = file_get_contents( get_template_directory() . '/inc/core/class-widgets.php' );
	return strpos( $widgets_code, "ltdh_render_recaptcha_field( 'sidebar_lead' )" ) !== false;
} );

run_test( 'Lead Magnet modal form includes ltdh_render_recaptcha_field', function() {
	$program_code = file_get_contents( get_template_directory() . '/single-program.php' );
	$has_render   = strpos( $program_code, "ltdh_render_recaptcha_field( 'lead_magnet' )" ) !== false;
	$has_token_js = strpos( $program_code, "ltdhGetRecaptchaToken('lead_magnet'" ) !== false;
	return $has_render && $has_token_js;
} );

run_test( 'Eligibility wizard is kept lightweight without reCAPTCHA friction', function() {
	$wizard_code = file_get_contents( get_template_directory() . '/template-parts/eligibility/wizard.php' );
	return strpos( $wizard_code, 'ltdh_render_recaptcha_field' ) === false;
} );

run_test( 'Eligibility results unified form includes ltdh_render_recaptcha_field and profile fields', function() {
	$results_code = file_get_contents( get_template_directory() . '/template-parts/eligibility/results.php' );
	$has_lead     = strpos( $results_code, "ltdh_render_recaptcha_field( 'elig_lead' )" ) !== false;
	$has_degree   = strpos( $results_code, 'name="degree_file"' ) !== false;
	$has_trans    = strpos( $results_code, 'name="transcript_file"' ) !== false;
	$has_school   = strpos( $results_code, 'name="previous_school"' ) !== false;
	return $has_lead && $has_degree && $has_trans && $has_school;
} );

run_test( 'Backend lead-capture handlers include ltdh_verify_recaptcha', function() {
	$lc_code      = file_get_contents( get_template_directory() . '/inc/lead-capture.php' );
	$has_native   = strpos( $lc_code, "ltdh_verify_recaptcha( \$recaptcha_token, 'native_form' )" ) !== false;
	$has_magnet   = strpos( $lc_code, "ltdh_verify_recaptcha( \$recaptcha_token, 'lead_magnet' )" ) !== false;
	return $has_native && $has_magnet;
} );

run_test( 'Backend eligibility lead capture handler includes ltdh_verify_recaptcha', function() {
	$elig_code    = file_get_contents( get_template_directory() . '/inc/eligibility.php' );
	$has_lead     = strpos( $elig_code, "ltdh_verify_recaptcha( \$recaptcha_token, 'elig_lead' )" ) !== false;
	$has_verify   = strpos( $elig_code, "ltdh_verify_recaptcha( \$recaptcha_token, 'elig_verify' )" ) !== false;
	return $has_lead && $has_verify;
} );

run_test( 'Frontend script assets/js/recaptcha.js exists and is valid JS', function() {
	$js_file = get_template_directory() . '/assets/js/recaptcha.js';
	if ( ! file_exists( $js_file ) ) {
		return false;
	}
	$content = file_get_contents( $js_file );
	return strpos( $content, 'window.ltdhGetRecaptchaToken' ) !== false
		&& strpos( $content, 'window.ltdhOnRecaptchaLoad' ) !== false
		&& strpos( $content, 'ltdh-lead-magnet-form' ) !== false;
} );

run_test( 'ACF JSON includes reCAPTCHA settings tab and fields', function() {
	$acf_json = file_get_contents( get_template_directory() . '/inc/acf-import-fields.json' );
	return strpos( $acf_json, 'field_tab_recaptcha' ) !== false
		&& strpos( $acf_json, 'field_recaptcha_site_key' ) !== false
		&& strpos( $acf_json, 'field_recaptcha_secret_key' ) !== false
		&& strpos( $acf_json, 'field_recaptcha_min_score' ) !== false;
} );

echo "\n==================================================\n";
echo sprintf( "TOTAL TESTS: %d | PASSED: %d | FAILED: %d\n", $tests_passed + $tests_failed, $tests_passed, $tests_failed );
echo "==================================================\n";

if ( $tests_failed > 0 ) {
	exit( 1 );
}
exit( 0 );
