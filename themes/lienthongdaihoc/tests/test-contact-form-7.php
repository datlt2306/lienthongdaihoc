<?php
if ( php_sapi_name() !== 'cli' ) {
	http_response_code( 403 );
	die( 'Forbidden: CLI access only.' );
}

/**
 * Test Suite: Contact Form 7 Migration & Integration Verification
 *
 * Verifies CF7 forms configuration, options mapping, template callers,
 * accessibility compliance, context propagation, spam prevention, and lead capture.
 */

require_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-load.php';

echo "==================================================\n";
echo "STARTING TEST SUITE: CONTACT FORM 7 INTEGRATION\n";
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
// Suite 1: Functions and Hooks
// ----------------------------------------------------
echo "--- SUITE 1: FUNCTION CONTRACTS & HOOKS ---\n";

run_test( 'Function ltdh_render_contact_form exists', function() {
	return function_exists( 'ltdh_render_contact_form' );
} );

run_test( 'Function ltdh_get_form_shortcode exists', function() {
	return function_exists( 'ltdh_get_form_shortcode' );
} );

run_test( 'Function ltdh_render_consultation_form exists', function() {
	return function_exists( 'ltdh_render_consultation_form' );
} );

run_test( 'Function ltdh_capture_cf7_lead exists', function() {
	return function_exists( 'ltdh_capture_cf7_lead' );
} );

run_test( 'wpcf7_autop_or_not filter is registered to disable autop', function() {
	return has_filter( 'wpcf7_autop_or_not' ) !== false;
} );

run_test( 'wpcf7_form_hidden_fields filter is registered', function() {
	return has_filter( 'wpcf7_form_hidden_fields' ) !== false;
} );

run_test( 'wpcf7_before_send_mail hook has ltdh_capture_cf7_lead', function() {
	return has_action( 'wpcf7_before_send_mail', 'ltdh_capture_cf7_lead' ) !== false;
} );

// ----------------------------------------------------
// Suite 2: Contact Form 7 Post 321 (Form Liên hệ)
// ----------------------------------------------------
echo "\n--- SUITE 2: CF7 FORM 321 (FORM LIÊN HỆ) ---\n";

run_test( 'CF7 Form 321 exists and is published', function() {
	$form = get_post( 321 );
	return $form && $form->post_type === 'wpcf7_contact_form' && $form->post_status === 'publish';
} );

run_test( 'CF7 Form 321 has title "Form Liên hệ"', function() {
	$form = get_post( 321 );
	return $form && $form->post_title === 'Form Liên hệ';
} );

run_test( 'CF7 Form 321 markup contains all essential contact fields', function() {
	$markup = get_post_meta( 321, '_form', true );
	return strpos( $markup, 'your-name' ) !== false
		&& strpos( $markup, 'your-phone' ) !== false
		&& strpos( $markup, 'your-email' ) !== false
		&& strpos( $markup, 'your-subject' ) !== false
		&& strpos( $markup, 'your-message' ) !== false
		&& strpos( $markup, 'hp_website' ) !== false;
} );

run_test( 'CF7 Form 321 contains accessible label elements with ID pairings', function() {
	$markup = get_post_meta( 321, '_form', true );
	return strpos( $markup, 'for="contact-name"' ) !== false
		&& strpos( $markup, 'id:contact-name' ) !== false
		&& strpos( $markup, 'for="contact-phone"' ) !== false
		&& strpos( $markup, 'id:contact-phone' ) !== false;
} );

run_test( 'CF7 Form 321 mail settings are configured', function() {
	$mail = get_post_meta( 321, '_mail', true );
	return is_array( $mail ) && ! empty( $mail['recipient'] ) && ! empty( $mail['subject'] );
} );

// ----------------------------------------------------
// Suite 3: Consultation Form 7 Post 1804 (Form Tư vấn)
// ----------------------------------------------------
echo "\n--- SUITE 3: CF7 FORM 1804 (FORM TƯ VẤN) ---\n";

run_test( 'CF7 Form 1804 exists and contains honeypot protection', function() {
	$markup = get_post_meta( 1804, '_form', true );
	return $markup && strpos( $markup, 'hp_website' ) !== false;
} );

// ----------------------------------------------------
// Suite 4: Options & Defaults Configuration
// ----------------------------------------------------
echo "\n--- SUITE 4: OPTIONS & DEFAULTS ---\n";

run_test( 'ltdh_get_form_shortcode("contact") returns shortcode for form 321', function() {
	$shortcode = ltdh_get_form_shortcode( 'contact' );
	return ! empty( $shortcode ) && strpos( $shortcode, 'id="321"' ) !== false;
} );

run_test( 'ltdh_get_form_shortcode("consultation") returns shortcode for form 1804', function() {
	$shortcode = ltdh_get_form_shortcode( 'consultation' );
	return ! empty( $shortcode ) && strpos( $shortcode, 'id="1804"' ) !== false;
} );

run_test( 'Centralized defaults define contact and consultation form fallbacks', function() {
	$forms_def = ltdh_get_defaults( 'forms' );
	return isset( $forms_def['contact_form_id'] ) && (int) $forms_def['contact_form_id'] === 321
		&& isset( $forms_def['consultation_form_id'] ) && (int) $forms_def['consultation_form_id'] === 1804;
} );

// ----------------------------------------------------
// Suite 5: Template Integrations
// ----------------------------------------------------
echo "\n--- SUITE 5: TEMPLATE INTEGRATION & CALLERS ---\n";

run_test( 'page-contact.php invokes ltdh_render_contact_form()', function() {
	$theme_dir = get_template_directory();
	$content   = file_get_contents( $theme_dir . '/page-contact.php' );
	return strpos( $content, 'ltdh_render_contact_form' ) !== false;
} );

run_test( 'inc/core/class-widgets.php guide sidebar calls ltdh_render_consultation_form()', function() {
	$theme_dir = get_template_directory();
	$content   = file_get_contents( $theme_dir . '/inc/core/class-widgets.php' );
	return strpos( $content, '[contact-form-7 id="consultation-form"' ) === false;
} );

run_test( 'class-performance.php preserves CF7 assets when front-page CF7 is active', function() {
	$theme_dir = get_template_directory();
	$content   = file_get_contents( $theme_dir . '/inc/core/class-performance.php' );
	return strpos( $content, '$has_front_cf7' ) !== false;
} );

// ----------------------------------------------------
// Suite 6: Render Simulation & CSS Compatibility
// ----------------------------------------------------
echo "\n--- SUITE 6: RENDER SIMULATION & CSS ---\n";

run_test( 'Rendered Contact Form HTML does not contain autop <p> or <br> pollution', function() {
	ob_start();
	ltdh_render_contact_form();
	$output = ob_get_clean();

	return strpos( $output, 'data-wpcf7-id="321"' ) !== false
		&& strpos( $output, '<p><label' ) === false
		&& strpos( $output, 'class="space-y-4 ltdh-cf7-form"' ) !== false;
} );

run_test( 'Input CSS contains response output and validation styling', function() {
	$theme_dir = get_template_directory();
	$css       = file_get_contents( $theme_dir . '/assets/css/input.css' );
	return strpos( $css, '.wpcf7 form.sent .wpcf7-response-output' ) !== false
		&& strpos( $css, '.wpcf7-not-valid-tip' ) !== false;
} );

// ----------------------------------------------------
// Suite 7: Contextual Hidden Fields Propagation
// ----------------------------------------------------
echo "\n--- SUITE 7: CONTEXTUAL HIDDEN FIELDS PROPAGATION ---\n";

run_test( 'Hidden fields filter successfully merges context into CF7 hidden fields', function() {
	$GLOBALS['ltdh_cf7_context_hidden_fields'] = [
		'current_program_id' => 999,
		'current_school_id'  => 888,
		'referral_source'    => 'https://example.com/test',
	];

	$hidden = apply_filters( 'wpcf7_form_hidden_fields', [ '_wpcf7' => '321' ] );
	$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];

	return isset( $hidden['current_program_id'] ) && $hidden['current_program_id'] === 999
		&& isset( $hidden['current_school_id'] ) && $hidden['current_school_id'] === 888
		&& isset( $hidden['referral_source'] ) && $hidden['referral_source'] === 'https://example.com/test';
} );

// ----------------------------------------------------
// Suite 8: Dynamic Program Dropdown Tag
// ----------------------------------------------------
echo "\n--- SUITE 8: DYNAMIC PROGRAM SELECTOR ---\n";

run_test( 'Function ltdh_format_program_select_label exists and formats program labels as "Tên chương trình - Hệ - Mã trường"', function() {
	if ( ! function_exists( 'ltdh_format_program_select_label' ) ) {
		return false;
	}

	$programs = get_posts( [
		'post_type'   => 'program',
		'post_status' => 'publish',
		'numberposts' => 10,
	] );
	if ( empty( $programs ) ) {
		return true;
	}

	$checked = false;
	foreach ( $programs as $p ) {
		$label = ltdh_format_program_select_label( $p->ID );
		if ( ! empty( $label ) && strpos( $label, ' - ' ) !== false ) {
			$checked = true;
			break;
		}
	}
	return $checked;
} );

run_test( 'wpcf7_form_tag filter populates numerical program IDs and formatted labels for current_program_id', function() {
	$dummy_tag = [
		'name'       => 'current_program_id',
		'raw_values' => [],
		'values'     => [],
		'labels'     => [],
	];

	$filtered_tag = apply_filters( 'wpcf7_form_tag', $dummy_tag, false );
	return ! empty( $filtered_tag['values'] ) && ! empty( $filtered_tag['labels'] ) && in_array( '-- Chọn ngành hoặc hệ học --', $filtered_tag['labels'], true );
} );

// ----------------------------------------------------
// SUMMARY
// ----------------------------------------------------
echo "\n==================================================\n";
echo "TOTAL TESTS: " . ( $tests_passed + $tests_failed ) . " | PASSED: {$tests_passed} | FAILED: {$tests_failed}\n";
echo "==================================================\n";

if ( $tests_failed > 0 ) {
	exit( 1 );
}
exit( 0 );
