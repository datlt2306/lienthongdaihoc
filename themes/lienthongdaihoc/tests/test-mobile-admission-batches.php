<?php
/**
 * Test Suite: Mobile Admission Batches Optimization
 * 
 * Verifies that in single-program.php:
 * 1. Only 1 active admission batch is shown on mobile; closed/other batches have 'hidden md:block'.
 * 2. The batch title ('Đợt X') is hidden on mobile via 'hidden md:flex'.
 * 3. The card layout is a single horizontal row: 'flex flex-row items-center justify-between'.
 * 4. Milestones adapt with 1 key date on mobile and 3 columns on desktop (md:grid md:grid-cols-3).
 * 5. Status badge is styled and positioned cleanly at the end.
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__, 4 ) . '/wp-load.php';
}

$GLOBALS['passed'] = 0;
$GLOBALS['failed'] = 0;

function ltdh_batch_assert( bool $cond, string $msg ): void {
	if ( $cond ) {
		echo "✅ PASS: {$msg}\n";
		$GLOBALS['passed']++;
	} else {
		echo "❌ FAIL: {$msg}\n";
		$GLOBALS['failed']++;
	}
}

echo "\n==================================================\n";
echo "TEST SUITE: MOBILE ADMISSION BATCHES OPTIMIZATION\n";
echo "==================================================\n\n";

$single_prog_file = dirname( __DIR__ ) . '/single-program.php';
ltdh_batch_assert( file_exists( $single_prog_file ), 'single-program.php exists' );

$code = file_get_contents( $single_prog_file );

// 1. Mobile active batch selection logic exists
ltdh_batch_assert(
	false !== strpos( $code, '$active_mobile_batch_idx' ),
	'single-program.php defines $active_mobile_batch_idx'
);

// 2. Closed/inactive batches hidden on mobile via hidden md:block
ltdh_batch_assert(
	false !== strpos( $code, '$mobile_vis_class     = $is_active_for_mobile ? \'\' : \'hidden md:block\';' ),
	'Non-active batches have hidden md:block applied on mobile'
);

// 3. Batch title hidden on mobile
ltdh_batch_assert(
	false !== strpos( $code, 'hidden md:flex w-24 shrink-0 flex-col' ),
	'Batch title has hidden md:flex to hide on mobile'
);

// 4. Card layout is horizontal flex-row on mobile and desktop
ltdh_batch_assert(
	false !== strpos( $code, 'flex flex-row items-center justify-between gap-2.5 sm:gap-3' ),
	'Row container uses flex flex-row items-center justify-between'
);

// 5. Milestones container has 1-row layout on mobile and 3 columns on md
ltdh_batch_assert(
	false !== strpos( $code, 'flex-1 min-w-0 md:grid md:grid-cols-3 md:gap-6 lg:gap-8' ),
	'Milestones use flex-1 min-w-0 with md:grid md:grid-cols-3'
);

// 6. Secondary milestones hidden on mobile when primary deadline is present
ltdh_batch_assert(
	false !== strpos( $code, '$primary_col      = $has_app_deadline ? \'col1\' : ( ! empty( $b_data[\'enrol_val\'] ) ? \'col3\' : \'col2\' );' ),
	'Adaptive primary column logic exists for mobile single-row'
);

// 7. Status badge aligned to right
ltdh_batch_assert(
	false !== strpos( $code, 'w-auto md:w-44 lg:w-48 shrink-0 flex items-center justify-end' ),
	'Status badge container has shrink-0 flex items-center justify-end'
);

// 8. Render check on real page (post 2013)
$program_id = 2013;
$page_html = file_get_contents( get_permalink( $program_id ) );
if ( ! empty( $page_html ) ) {
	// Must contain lich-tuyen-sinh
	ltdh_batch_assert( false !== strpos( $page_html, 'id="lich-tuyen-sinh"' ), 'Live program page renders #lich-tuyen-sinh section' );
	
	// Must contain hidden md:block for inactive batches
	ltdh_batch_assert( false !== strpos( $page_html, 'hidden md:block' ), 'Live HTML contains hidden md:block for non-active batches' );
	
	// Must contain hidden md:flex for batch title
	ltdh_batch_assert( false !== strpos( $page_html, 'hidden md:flex w-24' ), 'Live HTML contains hidden md:flex on batch titles' );
}

echo "\n==================================================\n";
echo "SUMMARY: Passed: {$GLOBALS['passed']} | Failed: {$GLOBALS['failed']}\n";
echo "==================================================\n\n";

if ( $GLOBALS['failed'] > 0 ) {
	exit( 1 );
}
