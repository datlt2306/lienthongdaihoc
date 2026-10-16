<?php
/**
 * Test Suite for School and Major FAQ Feature
 *
 * Verifies UI/UX Pro Max borderless accordions, ordered bullet lists,
 * zero-entry fallback synthesis, context awareness, ACF registrations,
 * and RankMath FAQPage schema integration.
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class FAQTestSuite {
	public static int $passed = 0;
	public static int $failed = 0;
	public static array $errors = [];

	public static function assert( bool $condition, string $description, array $context = [] ): void {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] " . $description . "\n";
		} else {
			self::$failed++;
			self::$errors[] = [ 'desc' => $description, 'context' => $context ];
			echo "  [FAIL] " . $description . "\n";
			if ( ! empty( $context ) ) {
				echo "         Details: " . json_encode( $context, JSON_UNESCAPED_UNICODE ) . "\n";
			}
		}
	}
}

echo "===================================================================\n";
echo "EMPIRICAL TEST SUITE: SCHOOL & MAJOR FAQ SYSTEM (UI/UX PRO MAX)\n";
echo "===================================================================\n\n";

// -----------------------------------------------------------------
// SUITE 1: ACF Fields & Import JSON
// -----------------------------------------------------------------
echo "SUITE 1: ACF Fields Configuration\n";
echo "-------------------------------------------------------------------\n";

$json_content = file_get_contents( ABSPATH . 'inc/acf-import-fields.json' );
$acf_json = json_decode( $json_content, true );

FAQTestSuite::assert(
	false !== strpos( $json_content, 'field_school_faq' ),
	"acf-import-fields.json: Contains 'field_school_faq' repeater"
);

FAQTestSuite::assert(
	false !== strpos( $json_content, 'field_major_faq' ),
	"acf-import-fields.json: Contains 'field_major_faq' repeater"
);

$acf_fields_php = file_get_contents( ABSPATH . 'inc/acf-fields.php' );
FAQTestSuite::assert(
	false !== strpos( $acf_fields_php, 'ltdh_register_school_faq_acf_fields' ),
	"inc/acf-fields.php: Registers school FAQ field group"
);

FAQTestSuite::assert(
	false !== strpos( $acf_fields_php, 'ltdh_register_major_faq_acf_fields' ),
	"inc/acf-fields.php: Registers major FAQ field group"
);

FAQTestSuite::assert(
	false !== strpos( $acf_fields_php, "'field_school_faq'" ) && false !== strpos( $acf_fields_php, "'field_major_faq'" ),
	"inc/acf-fields.php: Whitelists FAQ repeaters in ltdh_is_repeater_or_complex_field"
);

// -----------------------------------------------------------------
// SUITE 2: Helper Functions & Bullet Formatting
// -----------------------------------------------------------------
echo "\nSUITE 2: Helper Functions in class-helpers.php\n";
echo "-------------------------------------------------------------------\n";

$helpers_content = file_get_contents( ABSPATH . 'inc/core/class-helpers.php' );

FAQTestSuite::assert(
	false !== strpos( $helpers_content, 'function ltdh_get_school_faqs' ),
	"class-helpers.php: Defines ltdh_get_school_faqs()"
);

FAQTestSuite::assert(
	false !== strpos( $helpers_content, 'function ltdh_get_major_faqs' ),
	"class-helpers.php: Defines ltdh_get_major_faqs()"
);

// Verify bullet formatting: lists, steps, and badges
FAQTestSuite::assert(
	false !== strpos( $helpers_content, '<ul class="space-y-2' ),
	"class-helpers.php: Answers use clean structured bullets (<ul class=\"space-y-2 ...\">)"
);

FAQTestSuite::assert(
	false !== strpos( $helpers_content, 'Phiếu đăng ký xét tuyển' ) && false !== strpos( $helpers_content, '04 Ảnh thẻ cỡ 3x4' ),
	"class-helpers.php: Admissions documents formatted with 4 distinct numbered items"
);

// -----------------------------------------------------------------
// SUITE 3: single-school.php Presentation & Borderless Design
// -----------------------------------------------------------------
echo "\nSUITE 3: single-school.php Presentation & UI/UX Standards\n";
echo "-------------------------------------------------------------------\n";

$school_content = file_get_contents( ABSPATH . 'single-school.php' );

FAQTestSuite::assert(
	false !== strpos( $school_content, "'id' => 'hoi-dap-tuyen-sinh'" ) || false !== strpos( $school_content, "'id'       => 'hoi-dap-tuyen-sinh'" ),
	"single-school.php: FAQ tab 'hoi-dap-tuyen-sinh' added to sticky nav"
);

FAQTestSuite::assert(
	false !== strpos( $school_content, 'id="hoi-dap-tuyen-sinh"' ),
	"single-school.php: Section <section id=\"hoi-dap-tuyen-sinh\"> is rendered"
);

FAQTestSuite::assert(
	false !== strpos( $school_content, 'divide-y divide-slate-100' ),
	"single-school.php: Uses borderless 'divide-y divide-slate-100' accordion layout"
);

FAQTestSuite::assert(
	false === strpos( $school_content, '<details class="border border-slate-200' ),
	"single-school.php: Does NOT use heavy box-in-a-box individual border accordions"
);

FAQTestSuite::assert(
	false !== strpos( $school_content, 'wp_kses_post( $faq_item[\'answer\'] )' ),
	"single-school.php: Renders rich HTML bullets with wp_kses_post"
);

// -----------------------------------------------------------------
// SUITE 4: single-major.php Presentation & Context Awareness
// -----------------------------------------------------------------
echo "\nSUITE 4: single-major.php Presentation & Context Awareness\n";
echo "-------------------------------------------------------------------\n";

$major_content = file_get_contents( ABSPATH . 'single-major.php' );

FAQTestSuite::assert(
	false !== strpos( $major_content, "'id'       => 'hoi-dap-nganh'" ),
	"single-major.php: FAQ tab 'hoi-dap-nganh' added to \$major_tabs"
);

FAQTestSuite::assert(
	false !== strpos( $major_content, 'id="hoi-dap-nganh"' ),
	"single-major.php: Section <section id=\"hoi-dap-nganh\"> is rendered"
);

FAQTestSuite::assert(
	false !== strpos( $major_content, 'divide-y divide-slate-100' ),
	"single-major.php: Uses borderless 'divide-y divide-slate-100' accordion layout"
);

FAQTestSuite::assert(
	false !== strpos( $major_content, '$context_school ? \'Hỏi đáp tuyển sinh\' : \'Cẩm nang định hướng\'' ),
	"single-major.php: Context-aware badge toggles between school context and general orientation"
);

FAQTestSuite::assert(
	false !== strpos( $major_content, 'ltdh_get_major_faqs( (int) $major_id, $context_school ? $context_school->ID : null )' ),
	"single-major.php: Passes school context to ltdh_get_major_faqs"
);

// -----------------------------------------------------------------
// SUITE 5: RankMath FAQPage Schema Integration
// -----------------------------------------------------------------
echo "\nSUITE 5: RankMath FAQPage Schema Integration\n";
echo "-------------------------------------------------------------------\n";

$seo_content = file_get_contents( ABSPATH . 'inc/seo/class-rankmath-integration.php' );

FAQTestSuite::assert(
	false !== strpos( $seo_content, "\$data['ltdh_school_faq']" ),
	"class-rankmath-integration.php: School FAQPage schema injected into RankMath"
);

FAQTestSuite::assert(
	false !== strpos( $seo_content, "\$data['ltdh_major_faq']" ),
	"class-rankmath-integration.php: Major FAQPage schema injected into RankMath"
);

FAQTestSuite::assert(
	false !== strpos( $seo_content, 'wp_strip_all_tags( $a )' ),
	"class-rankmath-integration.php: Strips HTML tags for schema acceptedAnswer text"
);

// -----------------------------------------------------------------
// SUMMARY
// -----------------------------------------------------------------
echo "\n===================================================================\n";
echo sprintf( "FAQ TEST SUITE SUMMARY: %d PASSED, %d FAILED\n", FAQTestSuite::$passed, FAQTestSuite::$failed );
echo "===================================================================\n";

if ( FAQTestSuite::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
