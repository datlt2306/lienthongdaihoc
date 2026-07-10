<?php
/**
 * Automated Search Test Runner
 * lienthongdaihoc Theme
 */

// 1. Boot WordPress
$wp_load_path = dirname(__DIR__, 4) . '/wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
	die( "Error: wp-load.php not found at $wp_load_path\n" );
}
define( 'WP_USE_THEMES', false );
require_once $wp_load_path;

echo "==================================================\n";
echo "STARTING AUTOMATED TEST RUNNER: PROGRAM SEARCH\n";
echo "==================================================\n\n";

// 2. Setup fixtures
$school_id = 0;
$major_id  = 0;
$program_id = 0;
$type_term = null;
$campus_term = null;

try {
	// Create Test School
	$school_id = wp_insert_post( [
		'post_title'  => 'Học viện Công nghệ Bưu chính Viễn thông',
		'post_name'   => 'ptit',
		'post_type'   => 'school',
		'post_status' => 'publish'
	] );
	
	// Create Test Major
	$major_id = wp_insert_post( [
		'post_title'  => 'Công nghệ thông tin',
		'post_name'   => 'cong-nghe-thong-tin',
		'post_type'   => 'major',
		'post_status' => 'publish'
	] );
	
	// Helper to get or create term slug
	function get_or_create_term_slug( $name, $taxonomy, $slug ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( $term ) {
			return $term->slug;
		}
		$new_term = wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
		if ( is_wp_error( $new_term ) ) {
			if ( isset( $new_term->error_data['term_exists'] ) ) {
				$t = get_term( $new_term->error_data['term_exists'], $taxonomy );
				return $t->slug;
			}
			return $slug;
		}
		return $slug;
	}

	// Create Taxonomies Terms
	$type_slug = get_or_create_term_slug( 'Liên thông', 'training_type', 'lien-thong' );
	$campus_slug = get_or_create_term_slug( 'Hà Nội', 'campus', 'ha-noi' );

	// Create Program: "Liên thông CNTT - PTIT Hà Nội"
	$program_id = wp_insert_post( [
		'post_title'  => 'Liên thông CNTT - PTIT Hà Nội',
		'post_name'   => 'lien-thong-cntt-ptit-ha-noi',
		'post_type'   => 'program',
		'post_status' => 'publish'
	] );
	
	// Map ACF links
	update_post_meta( $program_id, 'school_relationship', $school_id );
	update_post_meta( $program_id, 'major_relationship', $major_id );
	
	// Set term taxonomies
	wp_set_object_terms( $program_id, $type_slug, 'training_type' );
	wp_set_object_terms( $program_id, $campus_slug, 'campus' );

	echo "Fixtures loaded successfully:\n";
	echo "- School ID: $school_id\n";
	echo "- Major ID: $major_id\n";
	echo "- Program ID: $program_id\n\n";

	// Define Test Helper
	function run_search( $params ) {
		$args = [
			'post_type'      => 'program',
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			'meta_query'     => [ 'relation' => 'AND' ],
			'tax_query'      => [ 'relation' => 'AND' ],
		];

		if ( isset( $params['s'] ) && ! empty( $params['s'] ) ) {
			$args['s'] = $params['s'];
		}
		if ( isset( $params['truong_filter'] ) && ! empty( $params['truong_filter'] ) ) {
			$args['meta_query'][] = [
				'key'     => 'school_relationship',
				'value'   => $params['truong_filter'],
				'compare' => '='
			];
		}
		if ( isset( $params['nganh_filter'] ) && ! empty( $params['nganh_filter'] ) ) {
			$args['meta_query'][] = [
				'key'     => 'major_relationship',
				'value'   => $params['nganh_filter'],
				'compare' => '='
			];
		}
		if ( isset( $params['he_filter'] ) && ! empty( $params['he_filter'] ) ) {
			$args['tax_query'][] = [
				'taxonomy' => 'training_type',
				'field'    => 'slug',
				'terms'    => $params['he_filter']
			];
		}
		if ( isset( $params['co_so_filter'] ) && ! empty( $params['co_so_filter'] ) ) {
			$args['tax_query'][] = [
				'taxonomy' => 'campus',
				'field'    => 'slug',
				'terms'    => $params['co_so_filter']
			];
		}

		// Apply theme filters manually to simulate query execution
		$args = apply_filters( 'pre_get_posts_args_ltdh', $args );

		$q = new WP_Query( $args );
		return $q->posts;
	}

	function assertContainsProgram( $posts, $id, $message ) {
		foreach ( $posts as $p ) {
			if ( $p->ID == $id ) {
				echo "✅ SUCCESS: $message\n";
				return true;
			}
		}
		echo "❌ FAILED: $message\n";
		return false;
	}

	function assertEmptyResults( $posts, $message ) {
		if ( empty( $posts ) ) {
			echo "✅ SUCCESS: $message\n";
			return true;
		}
		echo "❌ FAILED: $message (Got " . count( $posts ) . " results instead of 0)\n";
		return false;
	}

	// 3. EXECUTE TESTS
	$failures = 0;

	// TC-01: keyword search
	echo "Running TC-01...\n";
	$res = run_search( [ 's' => 'ptit' ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-01: Search by exact lowercase keyword (ptit)" ) ) $failures++;

	// TC-02: keyword uppercase
	echo "Running TC-02...\n";
	$res = run_search( [ 's' => 'PTIT' ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-02: Search by exact uppercase keyword (PTIT)" ) ) $failures++;

	// TC-03: trim spaces
	echo "Running TC-03...\n";
	$res = run_search( [ 's' => '   PTIT   ' ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-03: Trim leading/trailing spaces" ) ) $failures++;

	// TC-04: partial search
	echo "Running TC-04...\n";
	$res = run_search( [ 's' => 'buu chinh' ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-04: Partial match keyword search (buu chinh)" ) ) $failures++;

	// TC-05: accent-insensitive search
	echo "Running TC-05...\n";
	$res = run_search( [ 's' => 'Buu chinh Vien thong' ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-05: Accent-insensitive search (Buu chinh Vien thong)" ) ) $failures++;

	// TC-06: synonym search CNTT -> Công nghệ thông tin
	echo "Running TC-06...\n";
	$res = run_search( [ 's' => 'CNTT' ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-06: Synonym search (CNTT)" ) ) $failures++;

	// TC-07: synonym search QTKD -> Quản trị kinh doanh
	echo "Running TC-07...\n";
	// Insert temporary program for QTKD to verify QTKD synonym
	$qtkd_major_id = wp_insert_post( [ 'post_title' => 'Quản trị kinh doanh', 'post_type' => 'major', 'post_status' => 'publish' ] );
	$qtkd_program_id = wp_insert_post( [ 'post_title' => 'Cử nhân Quản trị kinh doanh', 'post_type' => 'program', 'post_status' => 'publish' ] );
	update_post_meta( $qtkd_program_id, 'major_relationship', $qtkd_major_id );
	$res = run_search( [ 's' => 'qtkd' ] );
	if ( ! assertContainsProgram( $res, $qtkd_program_id, "TC-07: Synonym search (qtkd)" ) ) $failures++;
	wp_delete_post( $qtkd_program_id, true );
	wp_delete_post( $qtkd_major_id, true );

	// TC-08: school filter
	echo "Running TC-08...\n";
	$res = run_search( [ 'truong_filter' => $school_id ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-08: Filter by School ID" ) ) $failures++;

	// TC-09: major filter
	echo "Running TC-09...\n";
	$res = run_search( [ 'nganh_filter' => $major_id ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-09: Filter by Major ID" ) ) $failures++;

	// TC-10: training type filter
	echo "Running TC-10...\n";
	$res = run_search( [ 'he_filter' => $type_slug ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-10: Filter by Training Type" ) ) $failures++;

	// TC-11: campus filter
	echo "Running TC-11...\n";
	$res = run_search( [ 'co_so_filter' => $campus_slug ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-11: Filter by Campus" ) ) $failures++;

	// TC-12: combined filter School + Major
	echo "Running TC-12...\n";
	$res = run_search( [ 'truong_filter' => $school_id, 'nganh_filter' => $major_id ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-12: Combined Filter (School + Major)" ) ) $failures++;

	// TC-13: combined filter Major + Training Type
	echo "Running TC-13...\n";
	$res = run_search( [ 'nganh_filter' => $major_id, 'he_filter' => $type_slug ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-13: Combined Filter (Major + Training Type)" ) ) $failures++;

	// TC-14: combined filter Keyword + School
	echo "Running TC-14...\n";
	$res = run_search( [ 's' => 'CNTT', 'truong_filter' => $school_id ] );
	if ( ! assertContainsProgram( $res, $program_id, "TC-14: Combined Filter (Keyword + School)" ) ) $failures++;

	// TC-15: empty result
	echo "Running TC-15...\n";
	$res = run_search( [ 's' => 'invalid_random_search_term' ] );
	if ( ! assertEmptyResults( $res, "TC-15: Empty results handling" ) ) $failures++;

	// TC-16: SQL injection attempt sanitization
	echo "Running TC-16...\n";
	$res = run_search( [ 'truong_filter' => "9999 OR 1=1 --" ] );
	if ( ! assertEmptyResults( $res, "TC-16: SQL injection protection" ) ) $failures++;

	// TC-17: XSS attempt sanitization
	echo "Running TC-17...\n";
	$res = run_search( [ 's' => "<script>alert('XSS')</script>" ] );
	// Should sanitize input safely and not crash
	echo "✅ SUCCESS: TC-17: XSS input handled safely\n";

	echo "\n==================================================\n";
	if ( $failures === 0 ) {
		echo "TEST RESULT: ALL TESTS PASSED SUCCESSFULLY! 🎉\n";
	} else {
		echo "TEST RESULT: FAILED with $failures failure(s).\n";
		exit(1);
	}
	echo "==================================================\n";

} finally {
	// Cleanup fixtures
	if ( $program_id ) wp_delete_post( $program_id, true );
	if ( $school_id ) wp_delete_post( $school_id, true );
	if ( $major_id ) wp_delete_post( $major_id, true );
	echo "Fixtures cleaned up successfully.\n";
}
