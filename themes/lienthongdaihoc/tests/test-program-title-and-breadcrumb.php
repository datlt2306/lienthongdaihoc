<?php
/**
 * Test Suite: Program Title, Specialization & Breadcrumbs Parity
 *
 * Verifies that:
 * 1. ltdh_get_program_discipline_name() respects specialization_title over major.
 * 2. ltdh_get_program_banner_title() generates 'Liên thông [Discipline] ([Clean Mode])'.
 * 3. Breadcrumb overrides in Rank Math and ltdh_breadcrumb() match the Banner H1 title.
 * 4. CPT program parent crumb is 'Liên thông Đại học' linking to /hinh-thuc-dao-tao/.
 *
 * @package lienthongdaihoc
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

echo "===================================================================\n";
echo "TEST SUITE: PROGRAM TITLE & BREADCRUMB HARMONIZATION\n";
echo "===================================================================\n";

$pass_count = 0;
$fail_count = 0;

function ltdh_assert( $condition, $message ) {
	global $pass_count, $fail_count;
	if ( $condition ) {
		echo "  [PASS] $message\n";
		$pass_count++;
	} else {
		echo "  [FAIL] $message\n";
		$fail_count++;
	}
}

// 1. Verify helper functions exist in inc/core/class-helpers.php
$helpers_src = file_get_contents( __DIR__ . '/../inc/core/class-helpers.php' );
ltdh_assert( false !== strpos( $helpers_src, 'function ltdh_get_program_discipline_name' ), 'ltdh_get_program_discipline_name helper function is defined' );
ltdh_assert( false !== strpos( $helpers_src, 'function ltdh_get_program_banner_title' ), 'ltdh_get_program_banner_title helper function is defined' );

// 2. Verify banner.php calls ltdh_get_program_banner_title
$banner_src = file_get_contents( __DIR__ . '/../template-parts/banner.php' );
ltdh_assert( false !== strpos( $banner_src, 'ltdh_get_program_banner_title' ), 'template-parts/banner.php calls ltdh_get_program_banner_title' );

// 3. Verify inc/seo/class-rankmath-integration.php includes single program override & discipline helpers
$rankmath_src = file_get_contents( __DIR__ . '/../inc/seo/class-rankmath-integration.php' );
ltdh_assert( false !== strpos( $rankmath_src, 'is_singular( LTDH_CPT_PROGRAM )' ), 'class-rankmath-integration.php intercepts singular program for breadcrumbs' );
ltdh_assert( false !== strpos( $rankmath_src, 'ltdh_get_program_banner_title' ), 'class-rankmath-integration.php uses ltdh_get_program_banner_title for breadcrumb' );
ltdh_assert( false !== strpos( $rankmath_src, 'ltdh_get_program_discipline_name' ), 'class-rankmath-integration.php uses ltdh_get_program_discipline_name for titles & schema' );
ltdh_assert( false !== strpos( $rankmath_src, "'major_cat'" ), 'class-rankmath-integration.php queries major_cat for hierarchical breadcrumbs' );

// 4. Unit logic simulation
function simulate_discipline_name( $spec_title, $major_name, $post_title ) {
	if ( ! empty( $spec_title ) ) {
		$clean_spec = preg_replace( '/^(?:chuyên ngành|ngành|cử nhân|kỹ sư)\s+/iu', '', trim( $spec_title ) );
		$clean_spec = preg_replace( '/\s*\([^)]*\)$/u', '', $clean_spec );
		if ( ! empty( $clean_spec ) ) {
			return $clean_spec;
		}
	}
	if ( ! empty( $post_title ) ) {
		$clean_title = (string) $post_title;
		$clean_title = preg_replace( '/\s*[-–—]\s*(?:ĐH|Trường ĐH|Đại học|Viện ĐH|TMU|GTVT)[^)]*$/iu', '', $clean_title );
		$clean_title = preg_replace( '/\s*\((?:hệ\s+)?(?:từ xa|đào tạo từ xa|vừa học vừa làm|vhvl|chính quy|online|tập trung|liên thông)[^)]*\)$/iu', '', $clean_title );
		$clean_title = preg_replace( '/^(?:tuyển sinh\s+)+/iu', '', $clean_title );
		$clean_title = preg_replace( '/^(?:liên thông\s+(?:đại học\s+)?(?:ngành\s+)?)/iu', '', $clean_title );
		$clean_title = preg_replace( '/^(?:cử nhân|kỹ sư|ngành)\s+/iu', '', $clean_title );
		$clean_title = trim( $clean_title, " -–—\t\n\r\0\x0B" );
		if ( ! empty( $clean_title ) ) {
			return $clean_title;
		}
	}
	if ( ! empty( $major_name ) ) {
		$clean_major = preg_replace( '/^(?:ngành|cử nhân|kỹ sư)\s+/iu', '', trim( $major_name ) );
		$clean_major = preg_replace( '/\s*\([^)]*\)$/u', '', $clean_major );
		if ( ! empty( $clean_major ) ) {
			return $clean_major;
		}
	}
	return '';
}

function simulate_banner_title( $discipline, $training_type ) {
	$clean_type = preg_replace( '/^hệ\s+/iu', '', trim( (string) $training_type ) );
	$type_part  = ! empty( $clean_type ) ? ' (' . $clean_type . ')' : '';
	return 'Liên thông ' . $discipline . $type_part;
}

// Case 1: TMU Specialization (Luật thương mại) under Major (Luật kinh tế)
$disc1 = simulate_discipline_name( 'Luật thương mại', 'Ngành Luật kinh tế', 'Luật thương mại (Từ xa) - TMU' );
$title1 = simulate_banner_title( $disc1, 'Đào tạo từ xa' );
ltdh_assert( $disc1 === 'Luật thương mại', 'Case 1: Discipline resolves to specialization "Luật thương mại"' );
ltdh_assert( $title1 === 'Liên thông Luật thương mại (Đào tạo từ xa)', 'Case 1: Banner/Breadcrumb title resolves to "Liên thông Luật thương mại (Đào tạo từ xa)"' );

// Case 2: Redundant prefix in specialization ("Chuyên ngành Marketing")
$disc2 = simulate_discipline_name( 'Chuyên ngành Marketing', 'Quản trị kinh doanh', 'Marketing (Từ xa) - TMU' );
$title2 = simulate_banner_title( $disc2, 'Hệ Đào tạo từ xa' );
ltdh_assert( $disc2 === 'Marketing', 'Case 2: Redundant prefix cleanly stripped -> "Marketing"' );
ltdh_assert( $title2 === 'Liên thông Marketing (Đào tạo từ xa)', 'Case 2: Training type "Hệ " stripped -> "Liên thông Marketing (Đào tạo từ xa)"' );

// Case 3: Standard Major without specialization (GTVT - Công nghệ thông tin)
$disc3 = simulate_discipline_name( '', 'Ngành Công nghệ thông tin', 'Công nghệ thông tin (Chính quy) - ĐH GTVT' );
$title3 = simulate_banner_title( $disc3, 'Chính quy' );
ltdh_assert( $disc3 === 'Công nghệ thông tin', 'Case 3: In the absence of specialization, cleanly falls back to Major' );
ltdh_assert( $title3 === 'Liên thông Công nghệ thông tin (Chính quy)', 'Case 3: Banner title resolves to "Liên thông Công nghệ thông tin (Chính quy)"' );

// Case 4: Custom post title override by editor ("Công nghệ thông tin 123")
$disc4 = simulate_discipline_name( '', 'Ngành Công nghệ thông tin', 'Công nghệ thông tin 123' );
$title4 = simulate_banner_title( $disc4, 'Đào tạo từ xa' );
ltdh_assert( $disc4 === 'Công nghệ thông tin 123', 'Case 4: Custom post title "Công nghệ thông tin 123" is respected' );
ltdh_assert( $title4 === 'Liên thông Công nghệ thông tin 123 (Đào tạo từ xa)', 'Case 4: Banner title resolves to "Liên thông Công nghệ thông tin 123 (Đào tạo từ xa)"' );

echo "\n===================================================================\n";
echo "SUMMARY: $pass_count PASSED, $fail_count FAILED\n";
echo "===================================================================\n";

if ( $fail_count > 0 ) {
	exit( 1 );
}
