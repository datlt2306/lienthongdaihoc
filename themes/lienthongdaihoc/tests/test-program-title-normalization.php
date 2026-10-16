<?php
/**
 * Test Suite: Program Title Normalization, H1 Banner, SEO & URL Redirection
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class ProgramTitleTestSuite {
	public static int $passed = 0;
	public static int $failed = 0;

	public static function assert( bool $condition, string $description ): void {
		if ( $condition ) {
			self::$passed++;
			echo "  [PASS] " . $description . "\n";
		} else {
			self::$failed++;
			echo "  [FAIL] " . $description . "\n";
		}
	}
}

echo "===================================================================\n";
echo "TEST SUITE: PROGRAM TITLE, H1 BANNER, SEO & URL NORMALIZATION\n";
echo "===================================================================\n\n";

$banner_content = file_get_contents( ABSPATH . 'template-parts/banner.php' );
$rankmath_content = file_get_contents( ABSPATH . 'inc/seo/class-rankmath-integration.php' );
$rewrite_content = file_get_contents( ABSPATH . 'inc/core/class-rewrite-rules.php' );
$cli_content = file_get_contents( ABSPATH . 'inc/cli-commands.php' );
$prog_content = file_get_contents( ABSPATH . 'single-program.php' );

// 1. Banner Title & Subtitle
ProgramTitleTestSuite::assert(
	false === strpos( $banner_content, "'Tuyển sinh Liên thông ' . \$clean_prog" ),
	"banner.php: Eliminated redundant 'Tuyển sinh Liên thông ' prepending"
);

ProgramTitleTestSuite::assert(
	false !== strpos( $banner_content, "'Liên thông ' . \$clean_major . \$type_part" )
	&& false !== strpos( $banner_content, "\$banner_eyebrow" ),
	"banner.php: Formats concise H1 'Liên thông [Major] ([Type])' with School in Eyebrow Badge"
);

ProgramTitleTestSuite::assert(
	false !== strpos( $banner_content, "Chương trình đào tạo Liên thông Đại học (%s)" ),
	"banner.php: Subtitle uses clean training description without redundant 'Tuyển sinh'"
);

// 2. RankMath SEO Integration
ProgramTitleTestSuite::assert(
	false !== strpos( $rankmath_content, "sprintf( 'Liên thông %s%s%s | Tuyển sinh %d'" ),
	"class-rankmath-integration.php: Document title uses standardized formula with Year"
);

ProgramTitleTestSuite::assert(
	false === strpos( $rankmath_content, "get_the_title(), \$type_label" ),
	"class-rankmath-integration.php: Eliminated duplicate training type appending in SEO title"
);

ProgramTitleTestSuite::assert(
	false !== strpos( $rankmath_content, "\$schema_program_name = 'Liên thông ' . \$clean_major" ),
	"class-rankmath-integration.php: Course and EducationalOccupationalProgram schema uses standardized name"
);

// 3. 301 URL Redirection
ProgramTitleTestSuite::assert(
	false !== strpos( $rewrite_content, "cu-nhan-cong-nghe-thong-tin-lien-thong-chinh-quy-chinh-quy-utc" )
	&& false !== strpos( $rewrite_content, "lien-thong-cong-nghe-thong-tin-chinh-quy-utc" ),
	"class-rewrite-rules.php: 301 redirects legacy double slug to clean slug"
);

// 4. CLI / Seed Standardization
ProgramTitleTestSuite::assert(
	false !== strpos( $cli_content, "Công nghệ thông tin (Chính quy) - ĐH GTVT" )
	&& false !== strpos( $cli_content, "lien-thong-cong-nghe-thong-tin-chinh-quy-utc" ),
	"cli-commands.php: Seeds clean admin titles and non-repeating slugs"
);

// 5. Mobile Sticky Bar
ProgramTitleTestSuite::assert(
	false !== strpos( $prog_content, "\$mob_title" ),
	"single-program.php: Mobile sticky bar prevents truncation by formatting concise title"
);

echo "\n===================================================================\n";
echo sprintf( "SUMMARY: %d PASSED, %d FAILED\n", ProgramTitleTestSuite::$passed, ProgramTitleTestSuite::$failed );
echo "===================================================================\n";

if ( ProgramTitleTestSuite::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
