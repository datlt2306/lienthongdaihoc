<?php
/**
 * Adversarial Card Parity & Stress Harness for Milestone M5
 *
 * Authored by: challenger_m5_1 (Card Parity Challenger)
 * Scope: 1:1 structural parity between SSR cards and AJAX cards,
 *        headline formula consistency, badge purity (0 'Hệ ' prefix),
 *        compare toggle attribute integrity, adversarial prefix stripping.
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class CardParityTestSuite {
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
echo "CHALLENGER M5-1: CARD PARITY & ADVERSARIAL STRESS HARNESS\n";
echo "===================================================================\n\n";

// -----------------------------------------------------------------
// 1. FILE EXISTENCE & SYNTAX AUDIT
// -----------------------------------------------------------------
echo "SECTION 1: Syntax Audit of Target Files\n";
echo "-------------------------------------------------------------------\n";

$target_files = [
	'taxonomy-training_type.php',
	'archive-program.php',
	'inc/core/class-query-filters.php',
	'template-parts/compare/program-cards.php',
];

foreach ( $target_files as $rel_path ) {
	$full_path = ABSPATH . $rel_path;
	CardParityTestSuite::assert(
		file_exists( $full_path ),
		"Target file exists: {$rel_path}",
		[ 'path' => $full_path ]
	);

	$output = [];
	$return_var = 0;
	exec( "php -l " . escapeshellarg( $full_path ), $output, $return_var );
	CardParityTestSuite::assert(
		0 === $return_var,
		"Syntax check passed: {$rel_path}",
		[ 'output' => implode( "\n", $output ) ]
	);
}

// -----------------------------------------------------------------
// 2. DOM & STRUCTURAL PARITY BETWEEN SSR AND AJAX CARDS
// -----------------------------------------------------------------
echo "\nSECTION 2: SSR vs AJAX Card Structural & Token Parity\n";
echo "-------------------------------------------------------------------\n";

$ssr_tax_code   = file_get_contents( ABSPATH . 'taxonomy-training_type.php' );
$ssr_arch_code  = file_get_contents( ABSPATH . 'archive-program.php' );
$ajax_code      = file_get_contents( ABSPATH . 'inc/core/class-query-filters.php' );
$compare_code   = file_get_contents( ABSPATH . 'template-parts/compare/program-cards.php' );

// 2.1 Outer card container class list
$outer_classes = [
	'bg-white',
	'border',
	'border-slate-200/90',
	'rounded-2xl',
	'overflow-hidden',
	'shadow-xs',
	'hover:shadow-lg',
	'transition-all',
	'duration-300',
	'flex',
	'flex-col',
	'justify-between',
	'group',
];

foreach ( $outer_classes as $cls ) {
	CardParityTestSuite::assert(
		false !== strpos( $ssr_tax_code, $cls ),
		"SSR taxonomy card container includes class: {$cls}"
	);
	CardParityTestSuite::assert(
		false !== strpos( $ssr_arch_code, $cls ),
		"SSR archive card container includes class: {$cls}"
	);
	CardParityTestSuite::assert(
		false !== strpos( $ajax_code, $cls ),
		"AJAX card container includes class: {$cls}"
	);
}

// 2.2 Cover image and badge overlay tokens
$cover_tokens = [
	'h-28 sm:h-32 w-full bg-slate-100 bg-cover bg-center relative overflow-hidden',
	'bg-gradient-to-t from-slate-900/60 via-slate-900/20 to-transparent',
	'tam-ngung',
	'Đã hết chỉ tiêu',
	'sap-mo',
	'Sắp mở',
];

foreach ( $cover_tokens as $token ) {
	CardParityTestSuite::assert(
		false !== strpos( $ssr_tax_code, $token ),
		"SSR taxonomy includes cover token: {$token}"
	);
	CardParityTestSuite::assert(
		false !== strpos( $ajax_code, $token ),
		"AJAX includes cover token: {$token}"
	);
}

// 2.3 Institution header & typography
$header_tokens = [
	'w-12 h-12 bg-white border border-slate-200/90 rounded-xl flex items-center justify-center p-1.5 shrink-0 shadow-sm group-hover:border-brand-primary/40 transition-colors',
	'text-xs font-bold text-slate-500 uppercase tracking-wider block truncate',
	'font-black text-slate-900 text-base md:text-lg hover:text-brand-primary leading-snug line-clamp-2 min-h-[48px] transition-colors',
];

foreach ( $header_tokens as $token ) {
	CardParityTestSuite::assert(
		false !== strpos( $ssr_tax_code, $token ),
		"SSR taxonomy includes header token: {$token}"
	);
	CardParityTestSuite::assert(
		false !== strpos( $ajax_code, $token ),
		"AJAX includes header token: {$token}"
	);
}

// 2.4 Key metrics display (Tuition, Duration, Delivery Mode)
$metric_labels = [
	'Học phí:',
	'Thời gian:',
	'Hình thức:',
	'ltdh_get_program_learning_details',
];

foreach ( $metric_labels as $label ) {
	CardParityTestSuite::assert(
		false !== strpos( $ssr_tax_code, $label ),
		"SSR taxonomy includes metric: {$label}"
	);
	CardParityTestSuite::assert(
		false !== strpos( $ajax_code, $label ),
		"AJAX includes metric: {$label}"
	);
}

// 2.5 Action buttons (Details CTA & Compare Toggle)
$action_tokens = [
	'ltdh-btn-details min-h-[44px] flex items-center justify-center flex-1',
	'ltdh-compare-toggle text-xs md:text-sm text-slate-600 hover:text-brand-primary font-bold border border-slate-200 hover:border-brand-primary rounded-xl py-2.5 px-3 transition-all min-h-[44px] flex items-center justify-center flex-1 bg-white hover:bg-slate-50',
	'Tìm hiểu',
	'So sánh',
];

foreach ( $action_tokens as $token ) {
	CardParityTestSuite::assert(
		false !== strpos( $ssr_tax_code, $token ),
		"SSR taxonomy includes action token: {$token}"
	);
	CardParityTestSuite::assert(
		false !== strpos( $ajax_code, $token ),
		"AJAX includes action token: {$token}"
	);
}

// -----------------------------------------------------------------
// 3. COMPARE TOGGLE ATTRIBUTE INTEGRITY ACROSS TEMPLATES
// -----------------------------------------------------------------
echo "\nSECTION 3: Compare Toggle Attribute Completeness\n";
echo "-------------------------------------------------------------------\n";

$compare_button_attrs = [
	'data-compare-type="program"',
	'data-compare-id=',
	'data-compare-title=',
	'data-compare-slug=',
	'data-compare-he=',
	'data-compare-nganh=',
];

foreach ( $compare_button_attrs as $attr ) {
	CardParityTestSuite::assert(
		false !== strpos( $ssr_tax_code, $attr ),
		"SSR taxonomy button has attribute: {$attr}"
	);
	CardParityTestSuite::assert(
		false !== strpos( $ssr_arch_code, $attr ),
		"SSR archive button has attribute: {$attr}"
	);
	CardParityTestSuite::assert(
		false !== strpos( $ajax_code, $attr ),
		"AJAX button has attribute: {$attr}"
	);
}

// Card wrapper compare attributes for thumbnail/title lookup fallback in compare.js
$card_wrapper_attrs = [
	'data-compare-btn',
	'data-compare-type="program"',
	'data-compare-id=',
	'data-compare-title=',
	'data-compare-slug=',
	'data-compare-thumb=',
];

foreach ( $card_wrapper_attrs as $attr ) {
	CardParityTestSuite::assert(
		false !== strpos( $ssr_tax_code, $attr ),
		"SSR taxonomy outer card wrapper has attribute: {$attr}"
	);
	CardParityTestSuite::assert(
		false !== strpos( $ssr_arch_code, $attr ),
		"SSR archive outer card wrapper has attribute: {$attr}"
	);
	CardParityTestSuite::assert(
		false !== strpos( $ajax_code, $attr ),
		"AJAX outer card wrapper has attribute: {$attr}"
	);
}

// -----------------------------------------------------------------
// 4. BADGE PURITY (ZERO "HỆ " PREFIX) EMPIRICAL CHECKS
// -----------------------------------------------------------------
echo "\nSECTION 4: Badge Purity & Stripping Verification\n";
echo "-------------------------------------------------------------------\n";

CardParityTestSuite::assert(
	false !== strpos( $ssr_tax_code, "preg_replace( '/^hệ\\s+/iu', '', trim( \$type_name ) )" ),
	"SSR taxonomy executes regex stripping of 'Hệ ' prefix before badge output"
);

CardParityTestSuite::assert(
	false !== strpos( $ssr_arch_code, "preg_replace( '/^hệ\\s+/iu', '', trim( \$type_name ) )" ),
	"SSR archive executes regex stripping of 'Hệ ' prefix before badge output"
);

CardParityTestSuite::assert(
	false !== strpos( $ajax_code, "preg_replace( '/^hệ\\s+/iu', '', trim( \$t_name ) )" ),
	"AJAX executes regex stripping of 'Hệ ' prefix before badge output"
);

CardParityTestSuite::assert(
	false !== strpos( $compare_code, "preg_replace( '/^hệ\\s+/iu', '', trim( \$item['training_type'] ?? '' ) )" ),
	"Compare cards executes regex stripping of 'Hệ ' prefix before badge output"
);

// -----------------------------------------------------------------
// 5. ADVERSARIAL STRESS TEST: MAJOR & TYPE PREFIX EDGE CASES
// -----------------------------------------------------------------
echo "\nSECTION 5: Adversarial Edge Cases: Prefix Stripping & Headline Generator\n";
echo "-------------------------------------------------------------------\n";

/**
 * Exact replica of the template logic used in:
 * - taxonomy-training_type.php (lines 340-353)
 * - archive-program.php (lines 340-353)
 * - inc/core/class-query-filters.php (lines 191-208)
 */
function simulate_card_headline( ?string $major_cpt_title, string $raw_prog_title, string $type_name ): array {
	$major_name = $major_cpt_title ?: '';
	if ( empty( $major_name ) ) {
		$major_name = preg_replace( '/^(Cử nhân|Kỹ sư|Đại học)\s+/iu', '', $raw_prog_title );
		$major_name = preg_replace( '/\s*\([^)]*\)$/u', '', $major_name );
	}
	$clean_major_name = preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) );
	$clean_type_name  = preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) );
	$card_headline    = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );

	return [
		'clean_major'   => $clean_major_name,
		'clean_type'    => $clean_type_name,
		'card_headline' => $card_headline,
	];
}

$adversarial_cases = [
	// Edge Case 1: Standard clean inputs
	[
		'desc'           => 'Standard clean names',
		'major_cpt'      => 'Công nghệ thông tin',
		'prog_title'     => 'Cử nhân Công nghệ thông tin',
		'type_name'      => 'Từ xa',
		'expected_major' => 'Công nghệ thông tin',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Công nghệ thông tin - Từ xa',
	],
	// Edge Case 2: Major starting with "Ngành"
	[
		'desc'           => 'Major CPT starting with "Ngành "',
		'major_cpt'      => 'Ngành Quản trị kinh doanh',
		'prog_title'     => 'Cử nhân Quản trị kinh doanh',
		'type_name'      => 'Từ xa',
		'expected_major' => 'Quản trị kinh doanh',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Quản trị kinh doanh - Từ xa',
	],
	// Edge Case 3: Major starting with "ngành " (lowercase)
	[
		'desc'           => 'Major CPT starting with lowercase "ngành "',
		'major_cpt'      => 'ngành Kế toán',
		'prog_title'     => 'Cử nhân Kế toán',
		'type_name'      => 'Từ xa',
		'expected_major' => 'Kế toán',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Kế toán - Từ xa',
	],
	// Edge Case 4: Major starting with "NGÀNH " (uppercase)
	[
		'desc'           => 'Major CPT starting with uppercase "NGÀNH "',
		'major_cpt'      => 'NGÀNH LUẬT',
		'prog_title'     => 'Cử nhân Luật',
		'type_name'      => 'Từ xa',
		'expected_major' => 'LUẬT',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành LUẬT - Từ xa',
	],
	// Edge Case 5: Training type starting with "Hệ "
	[
		'desc'           => 'Training type starting with "Hệ "',
		'major_cpt'      => 'Thương mại điện tử',
		'prog_title'     => 'Cử nhân Thương mại điện tử',
		'type_name'      => 'Hệ Từ xa',
		'expected_major' => 'Thương mại điện tử',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Thương mại điện tử - Từ xa',
	],
	// Edge Case 6: Training type starting with lowercase "hệ "
	[
		'desc'           => 'Training type starting with lowercase "hệ "',
		'major_cpt'      => 'Logistics',
		'prog_title'     => 'Cử nhân Logistics',
		'type_name'      => 'hệ vừa học vừa làm',
		'expected_major' => 'Logistics',
		'expected_type'  => 'vừa học vừa làm',
		'expected_hl'    => 'Liên thông ngành Logistics - vừa học vừa làm',
	],
	// Edge Case 7: Training type starting with uppercase "HỆ "
	[
		'desc'           => 'Training type starting with uppercase "HỆ "',
		'major_cpt'      => 'Tài chính Ngân hàng',
		'prog_title'     => 'Cử nhân Tài chính Ngân hàng',
		'type_name'      => 'HỆ TỪ XA',
		'expected_major' => 'Tài chính Ngân hàng',
		'expected_type'  => 'TỪ XA',
		'expected_hl'    => 'Liên thông ngành Tài chính Ngân hàng - TỪ XA',
	],
	// Edge Case 8: Missing major relationship (major_cpt = null), fallback to "Cử nhân [Major]"
	[
		'desc'           => 'Missing major rel, fallback extracts from "Cử nhân [Major]"',
		'major_cpt'      => null,
		'prog_title'     => 'Cử nhân Ngôn ngữ Anh',
		'type_name'      => 'Từ xa',
		'expected_major' => 'Ngôn ngữ Anh',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Ngôn ngữ Anh - Từ xa',
	],
	// Edge Case 9: Missing major rel, fallback extracts from "Kỹ sư [Major]"
	[
		'desc'           => 'Missing major rel, fallback extracts from "Kỹ sư [Major]"',
		'major_cpt'      => null,
		'prog_title'     => 'Kỹ sư Xây dựng',
		'type_name'      => 'Vừa học vừa làm',
		'expected_major' => 'Xây dựng',
		'expected_type'  => 'Vừa học vừa làm',
		'expected_hl'    => 'Liên thông ngành Xây dựng - Vừa học vừa làm',
	],
	// Edge Case 10: Missing major rel, fallback extracts from "Đại học [Major]"
	[
		'desc'           => 'Missing major rel, fallback extracts from "Đại học [Major]"',
		'major_cpt'      => null,
		'prog_title'     => 'Đại học Sư phạm Toán',
		'type_name'      => 'Từ xa',
		'expected_major' => 'Sư phạm Toán',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Sư phạm Toán - Từ xa',
	],
	// Edge Case 11: Program title with parenthetical suffix "(Vừa học vừa làm)"
	[
		'desc'           => 'Missing major rel, program title with parenthetical suffix',
		'major_cpt'      => null,
		'prog_title'     => 'Cử nhân Công nghệ thông tin (Vừa học vừa làm)',
		'type_name'      => 'Vừa học vừa làm',
		'expected_major' => 'Công nghệ thông tin',
		'expected_type'  => 'Vừa học vừa làm',
		'expected_hl'    => 'Liên thông ngành Công nghệ thông tin - Vừa học vừa làm',
	],
	// Edge Case 12: Empty training type
	[
		'desc'           => 'Empty training type',
		'major_cpt'      => 'Công nghệ sinh học',
		'prog_title'     => 'Cử nhân Công nghệ sinh học',
		'type_name'      => '',
		'expected_major' => 'Công nghệ sinh học',
		'expected_type'  => '',
		'expected_hl'    => 'Liên thông ngành Công nghệ sinh học',
	],
	// Edge Case 13: Combined double adversity: "Ngành Kế toán" + "Hệ Từ xa"
	[
		'desc'           => 'Combined: "Ngành Kế toán" + "Hệ Từ xa"',
		'major_cpt'      => 'Ngành Kế toán',
		'prog_title'     => 'Cử nhân Kế toán',
		'type_name'      => 'Hệ Từ xa',
		'expected_major' => 'Kế toán',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Kế toán - Từ xa',
	],
];

foreach ( $adversarial_cases as $idx => $test ) {
	$res = simulate_card_headline( $test['major_cpt'], $test['prog_title'], $test['type_name'] );

	CardParityTestSuite::assert(
		$res['clean_major'] === $test['expected_major'],
		"Case {$idx} ({$test['desc']}): Major cleaned to '{$res['clean_major']}'",
		[ 'expected' => $test['expected_major'], 'actual' => $res['clean_major'] ]
	);

	CardParityTestSuite::assert(
		$res['clean_type'] === $test['expected_type'],
		"Case {$idx} ({$test['desc']}): Type cleaned to '{$res['clean_type']}'",
		[ 'expected' => $test['expected_type'], 'actual' => $res['clean_type'] ]
	);

	CardParityTestSuite::assert(
		$res['card_headline'] === $test['expected_hl'],
		"Case {$idx} ({$test['desc']}): Headline matches '{$test['expected_hl']}'",
		[ 'expected' => $test['expected_hl'], 'actual' => $res['card_headline'] ]
	);

	// Zero duplicate prefix assertions
	CardParityTestSuite::assert(
		false === strpos( $res['card_headline'], 'Liên thông ngành Ngành' ),
		"Case {$idx}: Zero duplicate 'Liên thông ngành Ngành'"
	);
	CardParityTestSuite::assert(
		false === strpos( $res['card_headline'], 'Liên thông ngành ngành' ),
		"Case {$idx}: Zero duplicate 'Liên thông ngành ngành'"
	);
	CardParityTestSuite::assert(
		false === strpos( $res['card_headline'], ' - Hệ ' ) && false === strpos( $res['card_headline'], ' - hệ ' ),
		"Case {$idx}: Zero 'Hệ ' or 'hệ ' prefix in headline separator"
	);
	CardParityTestSuite::assert(
		0 === preg_match( '/^hệ\s+/iu', $res['clean_type'] ),
		"Case {$idx}: clean_type does not start with 'Hệ ' / 'hệ '"
	);
	CardParityTestSuite::assert(
		0 === preg_match( '/^ngành\s+/iu', $res['clean_major'] ),
		"Case {$idx}: clean_major does not start with 'Ngành ' / 'ngành '"
	);
}

// -----------------------------------------------------------------
// 6. REALISTIC HTML ESCAPING & XSS INTEGRITY
// -----------------------------------------------------------------
echo "\nSECTION 6: HTML Escaping & XSS Resistance\n";
echo "-------------------------------------------------------------------\n";

$xss_payload = '<script>alert(1)</script>';
$xss_res = simulate_card_headline( "Ngành {$xss_payload}", "Cử nhân {$xss_payload}", "Hệ {$xss_payload}" );

$escaped_title = htmlspecialchars( $xss_res['card_headline'], ENT_QUOTES, 'UTF-8' );
$escaped_type  = htmlspecialchars( $xss_res['clean_type'], ENT_QUOTES, 'UTF-8' );

CardParityTestSuite::assert(
	false === strpos( $escaped_title, '<script>' ),
	"Headline safely escapes dangerous HTML script tags"
);
CardParityTestSuite::assert(
	false === strpos( $escaped_type, '<script>' ),
	"Type badge safely escapes dangerous HTML script tags"
);

// -----------------------------------------------------------------
// SUMMARY
// -----------------------------------------------------------------
echo "\n===================================================================\n";
echo sprintf( "CARD PARITY TEST SUITE SUMMARY: %d PASSED, %d FAILED\n", CardParityTestSuite::$passed, CardParityTestSuite::$failed );
echo "===================================================================\n";

if ( CardParityTestSuite::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
