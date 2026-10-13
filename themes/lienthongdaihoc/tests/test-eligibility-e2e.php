<?php
/**
 * End-to-End (E2E) Live Integration Test Runner for Eligibility System
 * lienthongdaihoc Theme
 *
 * Black-box HTTP tests against running web server:
 * 1. Wizard Page Delivery & DOM Form Structure
 * 2. Cache-busting Asset Enqueues & CSS/JS rules
 * 3. Inlined Script Localization & Dynamic Nonce Extraction
 * 4. Real AJAX Eligibility Check (Cao đẳng -> CNTT Từ xa Hà Nội -> UTC #1)
 * 5. Dual-Slug Taxonomy Parity ('tu-xa' vs 'dao-tao-tu-xa')
 * 6. Lead Capture Submission & DB Linkage
 * 7. REST API Endpoint (/wp-json/ltdh/v1/eligibility/check)
 *
 * Run via:
 *   php tests/test-eligibility-e2e.php
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

class EligibilityE2ETestRunner {
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

	public static function http_request( $url, $method = 'GET', $data = [], $headers = [] ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 15 );

		if ( $method === 'POST' ) {
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		}

		if ( ! empty( $headers ) ) {
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		}

		$response = curl_exec( $ch );
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$error = curl_error( $ch );
		curl_close( $ch );

		return [
			'code'     => $http_code,
			'body'     => $response,
			'error'    => $error,
		];
	}

	public static function report() {
		echo "\n===================================================================\n";
		echo sprintf( "ELIGIBILITY E2E TEST RESULTS: %d PASSED, %d FAILED\n", self::$passed, self::$failed );
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

		echo "\nALL ELIGIBILITY E2E CHECKS PASSED WITH 100% INTEGRITY.\n\n";
		exit( 0 );
	}
}

$base_url = 'http://localhost:10028';

echo "==================================================\n";
echo "STARTING ELIGIBILITY E2E INTEGRATION TEST SUITE\n";
echo "Target: $base_url\n";
echo "==================================================\n";

// -----------------------------------------------------------------
// SUITE 1: Wizard Page Delivery & DOM Elements
// -----------------------------------------------------------------
EligibilityE2ETestRunner::suite( 'SUITE 1: WIZARD PAGE DELIVERY & DOM STRUCTURE' );

$page_url = "$base_url/kiem-tra-dieu-kien/";
$res_page = EligibilityE2ETestRunner::http_request( $page_url );

EligibilityE2ETestRunner::assert(
	$res_page['code'] === 200,
	"GET $page_url returns HTTP 200 OK",
	"Got HTTP {$res_page['code']}"
);

$html = $res_page['body'];

EligibilityE2ETestRunner::assert(
	strpos( $html, 'id="elig-unified-form"' ) !== false,
	"Page contains #elig-unified-form"
);

EligibilityE2ETestRunner::assert(
	strpos( $html, 'name="education"' ) !== false,
	"Page contains education selector (name='education')"
);

EligibilityE2ETestRunner::assert(
	strpos( $html, 'name="desired_major"' ) !== false,
	"Page contains desired major hidden input (name='desired_major')"
);

EligibilityE2ETestRunner::assert(
	strpos( $html, 'name="training_type"' ) !== false,
	"Page contains training type selector (name='training_type')"
);

EligibilityE2ETestRunner::assert(
	strpos( $html, 'name="campus"' ) !== false,
	"Page contains campus selector (name='campus')"
);

EligibilityE2ETestRunner::assert(
	strpos( $html, 'id="elig-unified-submit"' ) !== false,
	"Page contains unified submit button (#elig-unified-submit)"
);

EligibilityE2ETestRunner::assert(
	strpos( $html, 'id="elig-input-summary"' ) !== false && strpos( $html, 'id="elig-summary-tags"' ) !== false,
	"Page contains #elig-input-summary and #elig-summary-tags containers"
);

EligibilityE2ETestRunner::assert(
	strpos( $html, 'id="elig-results"' ) !== false,
	"Page contains #elig-results container"
);

// Check CSS and JS asset enqueue URLs
EligibilityE2ETestRunner::assert(
	strpos( $html, 'assets/css/eligibility.css?ver=' ) !== false,
	"Stylesheet eligibility.css is enqueued with cache-busting version query string"
);

EligibilityE2ETestRunner::assert(
	strpos( $html, 'assets/js/eligibility.js?ver=' ) !== false,
	"Script eligibility.js is enqueued with cache-busting version query string"
);

// -----------------------------------------------------------------
// SUITE 2: Script Localization & Nonce Extraction
// -----------------------------------------------------------------
EligibilityE2ETestRunner::suite( 'SUITE 2: SCRIPT LOCALIZATION & NONCE EXTRACTION' );

$matched = preg_match( '/var ltdh_elig = (\{.*?\});/s', $html, $matches );

EligibilityE2ETestRunner::assert(
	$matched && ! empty( $matches[1] ),
	"Page contains localized ltdh_elig configuration object"
);

$ltdh_config = json_decode( $matches[1] ?? '{}', true );

EligibilityE2ETestRunner::assert(
	! empty( $ltdh_config['ajax_url'] ),
	"ltdh_elig.ajax_url is populated",
	'URL: ' . ( $ltdh_config['ajax_url'] ?? 'NONE' )
);

EligibilityE2ETestRunner::assert(
	! empty( $ltdh_config['nonce'] ),
	"ltdh_elig.nonce is populated with valid token",
	'Nonce: ' . ( $ltdh_config['nonce'] ?? 'NONE' )
);

$ajax_url = $ltdh_config['ajax_url'] ?? "$base_url/wp-admin/admin-ajax.php";
$nonce    = $ltdh_config['nonce'] ?? '';

// -----------------------------------------------------------------
// SUITE 3: Real AJAX Check: Cao đẳng -> CNTT Từ xa Hà Nội -> UTC #1
// -----------------------------------------------------------------
EligibilityE2ETestRunner::suite( 'SUITE 3: REAL AJAX CHECK (CAO ĐẲNG -> CNTT TỪ XA HÀ NỘI)' );

$ajax_payload = [
	'action'        => 'ltdh_elig_check',
	'nonce'         => $nonce,
	'education'     => 'cao-dang',
	'desired_major' => 1677, // CNTT
	'training_type' => 'tu-xa',
	'campus'        => 'ha-noi',
];

$res_ajax = EligibilityE2ETestRunner::http_request( $ajax_url, 'POST', $ajax_payload );

EligibilityE2ETestRunner::assert(
	$res_ajax['code'] === 200,
	"POST to admin-ajax.php returns HTTP 200 OK",
	"Got HTTP {$res_ajax['code']}"
);

$json = json_decode( $res_ajax['body'], true );

EligibilityE2ETestRunner::assert(
	isset( $json['success'] ) && $json['success'] === true,
	"AJAX response returns success: true",
	'Body: ' . substr( $res_ajax['body'], 0, 200 )
);

$data = $json['data'] ?? [];

EligibilityE2ETestRunner::assert(
	( $data['eligible'] ?? false ) === true,
	"Eligibility outcome is eligible: true"
);

EligibilityE2ETestRunner::assert(
	( $data['eligible_count'] ?? 0 ) >= 1,
	"Eligible count is >= 1",
	'Count: ' . ( $data['eligible_count'] ?? 0 )
);

EligibilityE2ETestRunner::assert(
	( $data['input']['desired_major_title'] ?? '' ) === 'Công nghệ thông tin',
	"Response input includes serialized desired_major_title ('Công nghệ thông tin')"
);

$programs = $data['programs'] ?? [];
$top_pick = $programs[0] ?? null;

EligibilityE2ETestRunner::assert(
	$top_pick !== null,
	"Programs array contains at least 1 program"
);

EligibilityE2ETestRunner::assert(
	( $top_pick['program_id'] ?? 0 ) === 2014,
	"Top #1 match is UTC Distance Learning program (ID 2014)",
	'Top: ' . ( $top_pick['title'] ?? 'NONE' ) . ' (ID: ' . ( $top_pick['program_id'] ?? 0 ) . ')'
);

EligibilityE2ETestRunner::assert(
	( $top_pick['school']['code'] ?? '' ) === 'UTC',
	"Top #1 program school code is 'UTC'"
);

EligibilityE2ETestRunner::assert(
	( $top_pick['school']['title'] ?? '' ) === 'Trường Đại học Giao thông Vận tải',
	"Top #1 program school title is 'Trường Đại học Giao thông Vận tải'"
);

EligibilityE2ETestRunner::assert(
	! empty( $top_pick['school']['logo'] ),
	"Top #1 program has valid university logo image URL"
);

EligibilityE2ETestRunner::assert(
	( $data['check_id'] ?? 0 ) > 0,
	"Check transaction ID is created in database (> 0)",
	'check_id: ' . ( $data['check_id'] ?? 0 )
);

$check_id = $data['check_id'] ?? 0;

// -----------------------------------------------------------------
// SUITE 4: Dual-Slug Taxonomy Parity ('dao-tao-tu-xa')
// -----------------------------------------------------------------
EligibilityE2ETestRunner::suite( 'SUITE 4: DUAL-SLUG PARITY (DAO-TAO-TU-XA)' );

$ajax_payload_canonical = [
	'action'        => 'ltdh_elig_check',
	'nonce'         => $nonce,
	'education'     => 'cao-dang',
	'desired_major' => 1677,
	'training_type' => 'dao-tao-tu-xa',
	'campus'        => 'ha-noi',
];

$res_canonical = EligibilityE2ETestRunner::http_request( $ajax_url, 'POST', $ajax_payload_canonical );
$json_canonical = json_decode( $res_canonical['body'], true );

EligibilityE2ETestRunner::assert(
	( $json_canonical['success'] ?? false ) === true,
	"POST with canonical slug 'dao-tao-tu-xa' returns success: true"
);

$canonical_top = $json_canonical['data']['programs'][0] ?? null;
EligibilityE2ETestRunner::assert(
	( $canonical_top['program_id'] ?? 0 ) === 2014,
	"Canonical slug query also returns UTC program 2014 as #1 TOP PICK"
);

// -----------------------------------------------------------------
// SUITE 5: Real Consultation Lead Capture Flow
// -----------------------------------------------------------------
EligibilityE2ETestRunner::suite( 'SUITE 5: CONSULTATION LEAD CAPTURE FLOW' );

if ( $check_id > 0 ) {
	$lead_payload = [
		'action'          => 'ltdh_elig_lead',
		'nonce'           => $nonce,
		'elig_check_id'   => $check_id,
		'elig_program_id' => 2014,
		'cf_name'         => 'Nguyễn Văn Test E2E',
		'cf_phone'        => '0987654321',
		'cf_email'        => 'test.e2e@lienthongdaihoc.com',
	];

	$res_lead = EligibilityE2ETestRunner::http_request( $ajax_url, 'POST', $lead_payload );
	$json_lead = json_decode( $res_lead['body'], true );

	EligibilityE2ETestRunner::assert(
		$res_lead['code'] === 200,
		"POST ltdh_elig_lead returns HTTP 200 OK"
	);

	EligibilityE2ETestRunner::assert(
		( $json_lead['success'] ?? false ) === true,
		"Lead submission returns success: true",
		'Lead Body: ' . substr( $res_lead['body'], 0, 200 )
	);

	EligibilityE2ETestRunner::assert(
		( $json_lead['data']['lead_id'] ?? 0 ) > 0,
		"Lead record is created with positive lead_id",
		'lead_id: ' . ( $json_lead['data']['lead_id'] ?? 0 )
	);
} else {
	EligibilityE2ETestRunner::assert( false, "Cannot test lead capture without check_id" );
}

// -----------------------------------------------------------------
// SUITE 6: REST API Endpoint E2E Flow
// -----------------------------------------------------------------
EligibilityE2ETestRunner::suite( 'SUITE 6: REST API ENDPOINT E2E FLOW' );

$rest_url = "$base_url/wp-json/ltdh/v1/eligibility/check";
$rest_payload = json_encode( [
	'education'     => 'cao-dang',
	'desired_major' => 1677,
	'training_type' => 'tu-xa',
	'campus'        => 'ha-noi',
] );

$res_rest = EligibilityE2ETestRunner::http_request(
	$rest_url,
	'POST',
	$rest_payload,
	[ 'Content-Type: application/json' ]
);

EligibilityE2ETestRunner::assert(
	$res_rest['code'] === 200,
	"POST to $rest_url returns HTTP 200 OK",
	"Got HTTP {$res_rest['code']}"
);

$json_rest = json_decode( $res_rest['body'], true );

EligibilityE2ETestRunner::assert(
	( $json_rest['eligible'] ?? false ) === true,
	"REST response confirms eligible: true"
);

EligibilityE2ETestRunner::assert(
	( $json_rest['programs'][0]['program_id'] ?? 0 ) === 2014,
	"REST response ranks UTC program 2014 as #1"
);

// Output Final Report
EligibilityE2ETestRunner::report();
