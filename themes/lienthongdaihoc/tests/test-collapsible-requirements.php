<?php
/**
 * Test Suite for Single Program Collapsible Requirements & Documents
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
echo "TEST SUITE: SINGLE PROGRAM COLLAPSIBLE REQUIREMENTS & DOCUMENTS\n";
echo "===================================================================\n\n";

$prog_content = file_get_contents( ABSPATH . 'single-program.php' );

CollapsibleTestSuite::assert(
	false !== strpos( $prog_content, 'id="dieu-kien-ho-so"' ),
	"single-program.php: Contains section id 'dieu-kien-ho-so'"
);

CollapsibleTestSuite::assert(
	false !== strpos( $prog_content, 'id="details-dieu-kien"' ),
	"single-program.php: Admission requirements wrapped in collapsible <details id=\"details-dieu-kien\">"
);

CollapsibleTestSuite::assert(
	false !== strpos( $prog_content, 'id="details-ho-so"' ),
	"single-program.php: Admission documents wrapped in collapsible <details id=\"details-ho-so\">"
);

CollapsibleTestSuite::assert(
	false !== strpos( $prog_content, 'id="details-lien-he"' ),
	"single-program.php: Contact & admission office wrapped in collapsible <details id=\"details-lien-he\">"
);

CollapsibleTestSuite::assert(
	false !== strpos( $prog_content, 'id="ltdh-toggle-req-docs-btn"' ),
	"single-program.php: Has 'Mở tất cả' / 'Thu gọn' bulk toggle button"
);

CollapsibleTestSuite::assert(
	false !== strpos( $prog_content, 'divide-y divide-slate-100' ),
	"single-program.php: Uses borderless 'divide-y divide-slate-100' layout"
);

CollapsibleTestSuite::assert(
	false !== strpos( $prog_content, 'id="dieu-kien-xet-tuyen"' ) && false !== strpos( $prog_content, 'id="ho-so-can-nop"' ) && false !== strpos( $prog_content, 'id="thong-tin-lien-he"' ),
	"single-program.php: Preserves backward-compatible anchor spans (including thong-tin-lien-he)"
);

CollapsibleTestSuite::assert(
	false === strpos( $prog_content, '<section id="thong-tin-lien-he"' ),
	"single-program.php: Standalone section #thong-tin-lien-he is removed in favor of accordion item"
);

CollapsibleTestSuite::assert(
	false !== strpos( $prog_content, 'checkReqDocsHash' ) && false !== strpos( $prog_content, '#details-lien-he' ),
	"single-program.php: Auto-opens target accordion when URL hash points to specific subsection"
);

echo "\n===================================================================\n";
echo sprintf( "SUMMARY: %d PASSED, %d FAILED\n", CollapsibleTestSuite::$passed, CollapsibleTestSuite::$failed );
echo "===================================================================\n";

if ( CollapsibleTestSuite::$failed > 0 ) {
	exit( 1 );
}
exit( 0 );
