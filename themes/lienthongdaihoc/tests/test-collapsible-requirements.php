<?php
/**
 * Test Suite for Single School & Major Collapsible Requirements & Documents
 */

if ( php_sapi_name() !== 'cli' ) {
	die( "CLI only.\n" );
}

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class CollapsibleTestSuite {
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
echo "TEST SUITE: COLLAPSIBLE REQUIREMENTS & DOCUMENTS (SCHOOL & MAJOR)\n";
echo "===================================================================\n\n";

$school_content  = file_get_contents( ABSPATH . 'single-school.php' );
$major_content   = file_get_contents( ABSPATH . 'single-major.php' );
$program_content = file_get_contents( ABSPATH . 'single-program.php' );

CollapsibleTestSuite::assert(
	false !== strpos( $school_content, 'id="dieu-kien-ho-so"' ),
	"single-school.php: Contains section id 'dieu-kien-ho-so'"
);

CollapsibleTestSuite::assert(
	false !== strpos( $program_content, 'id="dieu-kien-ho-so"' ),
	"single-program.php: Contains section id 'dieu-kien-ho-so'"
);

CollapsibleTestSuite::assert(
	false !== strpos( $program_content, 'id="details-dieu-kien"' ) && false === strpos( $program_content, '<details id="details-dieu-kien" open' ),
	"single-program.php: Admission requirements wrapped in collapsible <details id=\"details-dieu-kien\"> and closed by default"
);

CollapsibleTestSuite::assert(
	false !== strpos( $program_content, 'id="details-ho-so"' ) && false === strpos( $program_content, '<details id="details-ho-so" open' ),
	"single-program.php: Admission documents wrapped in collapsible <details id=\"details-ho-so\"> and closed by default"
);

CollapsibleTestSuite::assert(
	false !== strpos( $program_content, 'id="details-lien-he"' ) && false === strpos( $program_content, '<details id="details-lien-he" open' ),
	"single-program.php: Contact & admission office wrapped in collapsible <details id=\"details-lien-he\"> and closed by default"
);

CollapsibleTestSuite::assert(
	false !== strpos( $program_content, 'id="ltdh-toggle-program-req-docs-btn"' ),
	"single-program.php: Has 'Mở tất cả' / 'Thu gọn' bulk toggle button"
);

CollapsibleTestSuite::assert(
	false !== strpos( $school_content, 'id="details-dieu-kien"' ) && false === strpos( $school_content, '<details id="details-dieu-kien" open' ),
	"single-school.php: Admission requirements wrapped in collapsible <details id=\"details-dieu-kien\"> and closed by default"
);

CollapsibleTestSuite::assert(
	false !== strpos( $school_content, 'id="details-ho-so"' ) && false === strpos( $school_content, '<details id="details-ho-so" open' ),
	"single-school.php: Admission documents wrapped in collapsible <details id=\"details-ho-so\"> and closed by default"
);

CollapsibleTestSuite::assert(
	false !== strpos( $school_content, 'id="details-lien-he"' ) && false === strpos( $school_content, '<details id="details-lien-he" open' ),
	"single-school.php: Contact & admission office wrapped in collapsible <details id=\"details-lien-he\"> and closed by default"
);

CollapsibleTestSuite::assert(
	false !== strpos( $school_content, 'id="ltdh-toggle-school-req-docs-btn"' ),
	"single-school.php: Has 'Mở tất cả' / 'Thu gọn' bulk toggle button"
);

CollapsibleTestSuite::assert(
	false !== strpos( $major_content, 'id="details-doi-tuong"' ) && false === strpos( $major_content, '<details id="details-doi-tuong" open' ),
	"single-major.php: Target applicants wrapped in collapsible <details id=\"details-doi-tuong\"> and closed by default"
);

CollapsibleTestSuite::assert(
	false !== strpos( $school_content, 'id="dieu-kien-xet-tuyen"' ) && false !== strpos( $school_content, 'id="ho-so-can-nop"' ) && false !== strpos( $school_content, 'id="thong-tin-lien-he"' ),
	"single-school.php: Preserves backward-compatible anchor spans"
);

CollapsibleTestSuite::assert(
	false !== strpos( $school_content, 'checkSchoolReqDocsHash' ) && false !== strpos( $school_content, '#details-lien-he' ),
	"single-school.php: Auto-opens target accordion when URL hash points to specific subsection"
);

echo "\n===================================================================\n";
echo sprintf( "SUMMARY: %d PASSED, %d FAILED\n", CollapsibleTestSuite::$passed, CollapsibleTestSuite::$failed );
echo "===================================================================\n";

if ( CollapsibleTestSuite::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
