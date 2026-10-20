<?php
/**
 * Test Suite: Contact Form 7 Evaluation Form ("Đăng ký thẩm định hồ sơ & xét miễn giảm tín chỉ")
 *
 * Verifies:
 * 1. CF7 Form 3632 exists and is published with slug 'form-tham-dinh-ho-so'.
 * 2. Form content has removed file attachment fields (degree_file, transcript_file).
 * 3. All essential fields (name, phone, email, previous_school, graduation, message, group reg, consent) are present.
 * 4. ltdh_get_form_shortcode('evaluation') returns shortcode for form 3632.
 * 5. ltdh_render_evaluation_form() exists and outputs the CF7 form.
 * 6. template-parts/eligibility/results.php invokes ltdh_render_evaluation_form().
 * 7. Lead capture interceptor ltdh_capture_cf7_lead() captures evaluation fields correctly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__, 4 ) . '/wp-load.php';
}

global $tests_passed, $tests_failed;
$tests_passed = 0;
$tests_failed = 0;

function run_test( string $name, callable $fn ) {
	global $tests_passed, $tests_failed;
	echo "Running: {$name} ... ";
	try {
		$result = $fn();
		if ( true === $result ) {
			echo "✅ PASS\n";
			$tests_passed++;
		} else {
			echo "❌ FAIL (Returned false or non-true)\n";
			$tests_failed++;
		}
	} catch ( Throwable $e ) {
		echo "❌ FAIL (Exception: " . $e->getMessage() . ")\n";
		$tests_failed++;
	}
}

echo "==================================================\n";
echo "TEST SUITE: CF7 EVALUATION FORM CONVERSION\n";
echo "==================================================\n\n";

// --- SUITE 1: CF7 Form 3632 Existence & Configuration ---
echo "--- SUITE 1: CF7 FORM EXISTENCE & FIELDS ---\n";

run_test( 'CF7 Form 3632 exists and is published', function() {
	$form = get_post( 3632 );
	return ( $form && 'wpcf7_contact_form' === $form->post_type && 'publish' === $form->post_status );
} );

run_test( 'CF7 Form 3632 has slug form-tham-dinh-ho-so', function() {
	$form = get_post( 3632 );
	return ( $form && 'form-tham-dinh-ho-so' === $form->post_name );
} );

run_test( 'CF7 Form 3632 content has NO file upload inputs (degree_file, transcript_file)', function() {
	$form = get_post( 3632 );
	$content = $form ? $form->post_content : '';
	return ( false === strpos( $content, 'degree_file' ) && false === strpos( $content, 'transcript_file' ) && false === strpos( $content, 'type="file"' ) );
} );

run_test( 'CF7 Form 3632 content contains all required contact & evaluation fields', function() {
	$form = get_post( 3632 );
	$content = $form ? $form->post_content : '';
	$checks = [
		'your-name',
		'your-phone',
		'your-email',
		'previous_school',
		'graduation',
		'your-message',
		'is_group_reg',
		'consent_data',
		'elig_check_id',
		'elig_program_id',
		'form_context',
	];
	foreach ( $checks as $field ) {
		if ( false === strpos( $content, $field ) ) {
			return false;
		}
	}
	return true;
} );

// --- SUITE 2: HELPER & RENDERING CONTRACTS ---
echo "\n--- SUITE 2: HELPER & RENDERING CONTRACTS ---\n";

run_test( 'Function ltdh_render_evaluation_form exists', function() {
	return function_exists( 'ltdh_render_evaluation_form' );
} );

run_test( 'ltdh_get_form_shortcode("evaluation") returns shortcode for form 3632', function() {
	$shortcode = ltdh_get_form_shortcode( 'evaluation' );
	return ( false !== strpos( $shortcode, 'wpcf7' ) || false !== strpos( $shortcode, '3632' ) );
} );

run_test( 'ltdh_render_evaluation_form() outputs CF7 evaluation form without file uploads', function() {
	ob_start();
	ltdh_render_evaluation_form();
	$output = ob_get_clean();
	return ( ! empty( $output ) && false === strpos( $output, 'type="file"' ) && false !== strpos( $output, 'elig-lead-name' ) );
} );

run_test( 'results.php invokes ltdh_render_evaluation_form()', function() {
	$file = get_template_directory() . '/template-parts/eligibility/results.php';
	$content = file_get_contents( $file );
	return ( false !== strpos( $content, 'ltdh_render_evaluation_form' ) && false === strpos( $content, 'id="degree-file-input"' ) );
} );

// --- SUITE 3: CF7 LEAD CAPTURE & CONTEXT ENRICHMENT ---
echo "\n--- SUITE 3: CF7 LEAD CAPTURE INTERCEPTOR ---\n";

run_test( 'ltdh_capture_cf7_lead() captures previous_school, graduation, and group_reg', function() {
	global $wpdb;
	$initial_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}" . LTDH_TABLE_LEADS );

	$_POST = [
		'_wpcf7'          => '3632',
		'_wpcf7_version'  => '5.9.8',
		'_wpcf7_unit_tag' => 'wpcf7-f3632-p1-o1',
		'your-name'       => 'Test Evaluation Student',
		'your-phone'      => '0911223344',
		'your-email'      => 'evaluation.student@example.com',
		'previous_school' => 'Cao đẳng Kinh tế Đối ngoại',
		'graduation'      => '2024',
		'your-message'    => 'Mong muốn thẩm định bảng điểm miễn giảm môn Marketing',
		'is_group_reg'    => [ 'Đăng ký nhóm' ],
		'elig_program_id' => '100',
		'form_context'    => 'eligibility_evaluation',
	];

	$cf = WPCF7_ContactForm::get_instance( 3632 );
	$submission = WPCF7_Submission::get_instance( $cf, [ 'skip_mail' => true ] );

	$abort = false;
	ltdh_capture_cf7_lead( $cf, $abort, $submission );

	$new_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}" . LTDH_TABLE_LEADS );
	if ( $new_count !== $initial_count + 1 ) {
		return false;
	}

	$lead = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}" . LTDH_TABLE_LEADS . " ORDER BY id DESC LIMIT 1" );
	$has_name = ( 'Test Evaluation Student' === $lead->name );
	$has_school = ( false !== strpos( $lead->message, 'Cao đẳng Kinh tế Đối ngoại' ) );
	$has_grad = ( false !== strpos( $lead->message, '2024' ) );
	$has_group = ( false !== strpos( $lead->message, 'ĐĂNG KÝ THEO NHÓM' ) );

	// Clean up test record
	if ( $lead && 'Test Evaluation Student' === $lead->name ) {
		$wpdb->delete( $wpdb->prefix . LTDH_TABLE_LEADS, [ 'id' => $lead->id ] );
	}

	return ( $has_name && $has_school && $has_grad && $has_group );
} );

echo "\n==================================================\n";
echo "TEST RESULTS: {$tests_passed} PASSED | {$tests_failed} FAILED\n";
echo "==================================================\n";

if ( $tests_failed > 0 ) {
	exit( 1 );
}
exit( 0 );
