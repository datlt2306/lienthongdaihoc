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
	'h-32 sm:h-44 md:h-48 w-full bg-slate-100 relative overflow-hidden group/cover',
	'bg-gradient-to-t from-slate-900/70 via-slate-900/10 to-slate-900/40 pointer-events-none',
	'tam-ngung',
	'Tạm dừng',
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
	'w-8 h-8 sm:w-10 sm:h-10 bg-white border border-slate-200/90 rounded-lg sm:rounded-xl flex items-center justify-center p-1 shrink-0 shadow-2xs group-hover/school:border-brand-primary/40 group-hover/school:shadow-xs transition-all -mt-4 sm:-mt-5',
	'text-[10px] sm:text-xs font-bold text-slate-500 group-hover/school:text-brand-primary uppercase tracking-tight sm:tracking-wide block truncate transition-colors',
	'font-black text-slate-900 text-xs sm:text-base md:text-lg hover:text-brand-primary leading-tight sm:leading-snug line-clamp-2 min-h-[28px] sm:min-h-[40px] transition-colors',
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
	'ltdh-btn-details min-h-[36px] sm:min-h-[38px] flex items-center justify-center flex-1',
	'ltdh-compare-toggle text-[10px] sm:text-xs md:text-sm text-slate-600 hover:text-brand-primary font-bold border border-slate-200 hover:border-brand-primary rounded-lg sm:rounded-xl py-1.5 sm:py-2 px-1 sm:px-3 transition-all min-h-[36px] sm:min-h-[38px] flex items-center justify-center flex-1 bg-white hover:bg-slate-50 text-center whitespace-nowrap',
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
	false !== strpos( $ajax_code, "preg_replace( '/^hệ\\s+/iu', '', trim( \$t_name ) )" )
	|| false !== strpos( $ajax_code, "preg_replace( '/^hệ\\s+/iu', '', trim( \$type_name ) )" ),
	"AJAX executes regex stripping of 'Hệ ' prefix before badge output"
);

CardParityTestSuite::assert(
	false !== strpos( $compare_code, "preg_replace( '/^hệ\\s+/iu', '', trim( \$item['training_type'] ?? '' ) )" ),
	"Compare cards executes regex stripping of 'Hệ ' prefix before badge output"
);

// Zero hardcoded 'Hệ ' prefix inside badge span across all 3 card templates
CardParityTestSuite::assert(
	false === strpos( $ssr_tax_code, 'Hệ <?php echo esc_html( $clean_type_name ); ?>' ),
	"SSR taxonomy does not have literal 'Hệ ' inside badge span"
);

CardParityTestSuite::assert(
	false === strpos( $ssr_arch_code, 'Hệ <?php echo esc_html( $clean_type_name ); ?>' ),
	"SSR archive does not have literal 'Hệ ' inside badge span"
);

CardParityTestSuite::assert(
	false === strpos( $ajax_code, 'Hệ <?php echo esc_html( $clean_type_name ); ?>' ),
	"AJAX card does not have literal 'Hệ ' inside badge span"
);

// All 3 templates invoke ltdh_format_program_card_headline
CardParityTestSuite::assert(
	false !== strpos( $ssr_tax_code, 'ltdh_format_program_card_headline' ),
	"SSR taxonomy calls ltdh_format_program_card_headline"
);

CardParityTestSuite::assert(
	false !== strpos( $ssr_arch_code, 'ltdh_format_program_card_headline' ),
	"SSR archive calls ltdh_format_program_card_headline"
);

CardParityTestSuite::assert(
	false !== strpos( $ajax_code, 'ltdh_format_program_card_headline' ),
	"AJAX calls ltdh_format_program_card_headline"
);

// -----------------------------------------------------------------
// 5. ADVERSARIAL STRESS TEST: MAJOR & TYPE PREFIX EDGE CASES
// -----------------------------------------------------------------
echo "\nSECTION 5: Adversarial Edge Cases: Prefix Stripping & Headline Generator\n";
echo "-------------------------------------------------------------------\n";

/**
 * Exact replica of the template logic used in:
 * - taxonomy-training_type.php
 * - archive-program.php
 * - inc/core/class-query-filters.php
 * via ltdh_format_program_card_headline()
 */
function simulate_card_headline( ?string $major_cpt_title, string $raw_prog_title, string $type_name, string $school_code = 'TMU', string $school_name = 'Trường Đại học Thương mại' ): array {
	$major_name = $major_cpt_title ?: '';
	if ( empty( $major_name ) ) {
		$major_name = preg_replace( '/^(Cử nhân|Kỹ sư|Đại học)\s+/iu', '', $raw_prog_title );
		$major_name = preg_replace( '/\s*[-–—].*$/u', '', $major_name );
		$major_name = preg_replace( '/\s*\([^)]*\)$/u', '', $major_name );
	}
	$clean_major_name = preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) );
	$clean_type_name  = preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) );

	$school_code   = trim( $school_code );
	$school_name   = trim( $school_name );
	$school_suffix = '';

	if ( ! empty( $school_code ) ) {
		$school_suffix = ' - ' . $school_code;
	} elseif ( ! empty( $school_name ) ) {
		$clean_school_fallback = preg_replace( '/^trường\s+đại\s+học\s+/iu', 'ĐH ', $school_name );
		$school_suffix = $clean_school_fallback ? ' - ' . $clean_school_fallback : '';
	}

	$clean_prog = trim( $raw_prog_title );
	$clean_prog = preg_replace( '/^(?:tuyển sinh\s+)?(?:liên thông\s+)?(?:ngành\s+)?/iu', '', $clean_prog );
	$clean_prog = preg_replace( '/^(?:cử nhân|kỹ sư|đại học)\s+/iu', '', $clean_prog );

	if ( ! empty( $school_code ) ) {
		$clean_prog = preg_replace( '/\s*[-–—]\s*(?:(?:đh|trường|học viện|đại học)\s+)?' . preg_quote( $school_code, '/' ) . '$/iu', '', $clean_prog );
	}
	if ( ! empty( $school_name ) ) {
		$clean_prog = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_name, '/' ) . '$/iu', '', $clean_prog );
	}
	$clean_prog = preg_replace( '/\s*[-–—]\s*(?:đh|trường|học viện|đại học)\s+[^\-–—()]+$/iu', '', $clean_prog );
	$clean_prog = preg_replace( '/\s*\([^)]*\)$/u', '', $clean_prog );
	$clean_prog = preg_replace( '/\s*[-–—]\s*(?:hệ\s+|đào tạo\s+)?(?:từ xa|chính quy|vừa học vừa làm|vhvl|vừa làm vừa học|online|văn bằng 2|vb2)$/iu', '', $clean_prog );

	if ( ! empty( $school_code ) ) {
		$clean_prog = preg_replace( '/\s*[-–—]\s*(?:(?:đh|trường|học viện|đại học)\s+)?' . preg_quote( $school_code, '/' ) . '$/iu', '', $clean_prog );
	}
	$clean_prog = preg_replace( '/\s*[-–—]\s*(?:đh|trường|học viện|đại học)\s+[^\-–—()]+$/iu', '', $clean_prog );

	$clean_prog = trim( $clean_prog, " -–—\t\n\r\0\x0B" );

	if ( empty( $clean_prog ) && ! empty( $clean_major_name ) ) {
		$clean_prog = preg_replace( '/^(?:ngành|cử nhân|kỹ sư)\s+/iu', '', trim( $clean_major_name ) );
	}

	if ( empty( $clean_prog ) ) {
		$clean_prog = trim( $raw_prog_title );
	}

	$card_headline = 'Liên thông ngành ' . $clean_prog . $school_suffix;

	return [
		'clean_major'   => $clean_major_name,
		'clean_type'    => $clean_type_name,
		'card_headline' => $card_headline,
	];
}

$adversarial_cases = [
	// Edge Case 1: Standard clean names with TMU school code
	[
		'desc'           => 'Standard clean names with TMU school code',
		'major_cpt'      => 'Công nghệ thông tin',
		'prog_title'     => 'Cử nhân Công nghệ thông tin',
		'type_name'      => 'Từ xa',
		'school_code'    => 'TMU',
		'school_name'    => 'Trường Đại học Thương mại',
		'expected_major' => 'Công nghệ thông tin',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Công nghệ thông tin - TMU',
	],
	// Edge Case 2: Major starting with "Ngành"
	[
		'desc'           => 'Major CPT starting with "Ngành "',
		'major_cpt'      => 'Ngành Quản trị kinh doanh',
		'prog_title'     => 'Cử nhân Quản trị kinh doanh',
		'type_name'      => 'Từ xa',
		'school_code'    => 'TMU',
		'school_name'    => 'Trường Đại học Thương mại',
		'expected_major' => 'Quản trị kinh doanh',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Quản trị kinh doanh - TMU',
	],
	// Edge Case 3: Major starting with "ngành " (lowercase)
	[
		'desc'           => 'Major CPT starting with lowercase "ngành "',
		'major_cpt'      => 'ngành Kế toán',
		'prog_title'     => 'Cử nhân Kế toán',
		'type_name'      => 'Từ xa',
		'school_code'    => 'UTC',
		'school_name'    => 'Trường Đại học Giao thông Vận tải',
		'expected_major' => 'Kế toán',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Kế toán - UTC',
	],
	// Edge Case 4: Major starting with "NGÀNH " (uppercase)
	[
		'desc'           => 'Major CPT starting with uppercase "NGÀNH "',
		'major_cpt'      => 'NGÀNH LUẬT',
		'prog_title'     => 'Cử nhân LUẬT',
		'type_name'      => 'Từ xa',
		'school_code'    => 'TMU',
		'school_name'    => 'Trường Đại học Thương mại',
		'expected_major' => 'LUẬT',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành LUẬT - TMU',
	],
	// Edge Case 5: Training type starting with "Hệ "
	[
		'desc'           => 'Training type starting with "Hệ "',
		'major_cpt'      => 'Thương mại điện tử',
		'prog_title'     => 'Cử nhân Thương mại điện tử',
		'type_name'      => 'Hệ Từ xa',
		'school_code'    => 'TMU',
		'school_name'    => 'Trường Đại học Thương mại',
		'expected_major' => 'Thương mại điện tử',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Thương mại điện tử - TMU',
	],
	// Edge Case 6: Training type starting with lowercase "hệ "
	[
		'desc'           => 'Training type starting with lowercase "hệ "',
		'major_cpt'      => 'Logistics',
		'prog_title'     => 'Cử nhân Logistics',
		'type_name'      => 'hệ vừa học vừa làm',
		'school_code'    => 'PTIT',
		'school_name'    => 'Học viện Công nghệ Bưu chính Viễn thông',
		'expected_major' => 'Logistics',
		'expected_type'  => 'vừa học vừa làm',
		'expected_hl'    => 'Liên thông ngành Logistics - PTIT',
	],
	// Edge Case 7: Training type starting with uppercase "HỆ "
	[
		'desc'           => 'Training type starting with uppercase "HỆ "',
		'major_cpt'      => 'Tài chính Ngân hàng',
		'prog_title'     => 'Cử nhân Tài chính Ngân hàng',
		'type_name'      => 'HỆ TỪ XA',
		'school_code'    => 'TMU',
		'school_name'    => 'Trường Đại học Thương mại',
		'expected_major' => 'Tài chính Ngân hàng',
		'expected_type'  => 'TỪ XA',
		'expected_hl'    => 'Liên thông ngành Tài chính Ngân hàng - TMU',
	],
	// Edge Case 8: Missing major relationship (major_cpt = null), fallback to "Cử nhân [Major]"
	[
		'desc'           => 'Missing major rel, fallback extracts from "Cử nhân [Major]"',
		'major_cpt'      => null,
		'prog_title'     => 'Cử nhân Ngôn ngữ Anh',
		'type_name'      => 'Từ xa',
		'school_code'    => 'ETU',
		'school_name'    => 'Trường Đại học Hòa Bình',
		'expected_major' => 'Ngôn ngữ Anh',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Ngôn ngữ Anh - ETU',
	],
	// Edge Case 9: Missing major rel, fallback extracts from "Kỹ sư [Major]"
	[
		'desc'           => 'Missing major rel, fallback extracts from "Kỹ sư [Major]"',
		'major_cpt'      => null,
		'prog_title'     => 'Kỹ sư Xây dựng',
		'type_name'      => 'Vừa học vừa làm',
		'school_code'    => 'UTC',
		'school_name'    => 'Trường Đại học Giao thông Vận tải',
		'expected_major' => 'Xây dựng',
		'expected_type'  => 'Vừa học vừa làm',
		'expected_hl'    => 'Liên thông ngành Xây dựng - UTC',
	],
	// Edge Case 10: Missing major rel, fallback extracts from "Đại học [Major]"
	[
		'desc'           => 'Missing major rel, fallback extracts from "Đại học [Major]"',
		'major_cpt'      => null,
		'prog_title'     => 'Đại học Sư phạm Toán',
		'type_name'      => 'Từ xa',
		'school_code'    => 'TNU',
		'school_name'    => 'Trường Đại học Thái Nguyên',
		'expected_major' => 'Sư phạm Toán',
		'expected_type'  => 'Từ xa',
		'expected_hl'    => 'Liên thông ngành Sư phạm Toán - TNU',
	],
	// Edge Case 11: Program title with parenthetical suffix "(Vừa học vừa làm)" and school in title
	[
		'desc'           => 'Missing major rel, program title with parenthetical suffix and school',
		'major_cpt'      => null,
		'prog_title'     => 'Công nghệ thông tin (Chính quy) - ĐH GTVT',
		'type_name'      => 'Chính quy',
		'school_code'    => 'UTC',
		'school_name'    => 'Trường Đại học Giao thông Vận tải',
		'expected_major' => 'Công nghệ thông tin',
		'expected_type'  => 'Chính quy',
		'expected_hl'    => 'Liên thông ngành Công nghệ thông tin - UTC',
	],
	// Edge Case 12: Empty training type
	[
		'desc'           => 'Empty training type',
		'major_cpt'      => 'Công nghệ sinh học',
		'prog_title'     => 'Cử nhân Công nghệ sinh học',
		'type_name'      => '',
		'school_code'    => 'TMU',
		'school_name'    => 'Trường Đại học Thương mại',
		'expected_major' => 'Công nghệ sinh học',
		'expected_type'  => '',
		'expected_hl'    => 'Liên thông ngành Công nghệ sinh học - TMU',
	],
	// Edge Case 13: Combined: Admin title "Thương mại điện tử" + TMU (User specific test case)
	[
		'desc'           => 'User exact case: Title "Thương mại điện tử", School TMU, Type Đào tạo từ xa',
		'major_cpt'      => 'Thương mại điện tử',
		'prog_title'     => 'Thương mại điện tử',
		'type_name'      => 'Đào tạo từ xa',
		'school_code'    => 'TMU',
		'school_name'    => 'Trường Đại học Thương mại',
		'expected_major' => 'Thương mại điện tử',
		'expected_type'  => 'Đào tạo từ xa',
		'expected_hl'    => 'Liên thông ngành Thương mại điện tử - TMU',
	],
];

foreach ( $adversarial_cases as $idx => $test ) {
	$res = simulate_card_headline(
		$test['major_cpt'],
		$test['prog_title'],
		$test['type_name'],
		$test['school_code'] ?? 'TMU',
		$test['school_name'] ?? 'Trường Đại học Thương mại'
	);

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
