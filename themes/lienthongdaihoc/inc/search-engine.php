<?php
/**
 * Program Search Engine Filters and Logic
 * lienthongdaihoc Theme
 */

add_filter( 'pre_get_posts_args_ltdh', 'ltdh_filter_program_search_query' );

function ltdh_filter_program_search_query( $args ) {
	// 1. Check if keyword search "s" is present
	if ( ! isset( $args['s'] ) || empty( $args['s'] ) ) {
		return $args;
	}

	$original_s = trim( $args['s'] );
	if ( empty( $original_s ) ) {
		return $args;
	}

	// 2. Expand synonyms
	$keyword = ltdh_expand_search_synonyms( $original_s );

	// Sanitize the search keyword safely for safe search queries
	$keyword = sanitize_text_field( $keyword );

	// 3. Query matching school IDs by keyword
	$school_ids = get_posts( [
		'post_type'      => 'school',
		's'              => $keyword,
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'post_status'    => 'publish',
	] );

	// 4. Query matching major IDs by keyword
	$major_ids = get_posts( [
		'post_type'      => 'major',
		's'              => $keyword,
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'post_status'    => 'publish',
	] );

	// Define status exclusion filter
	$meta_status_filter = [
		'relation' => 'OR',
		[
			'key'     => 'admission_status',
			'value'   => 'tam-ngung',
			'compare' => '!=',
		],
		[
			'key'     => 'admission_status',
			'compare' => 'NOT EXISTS',
		],
	];

	// 5. Query matching program IDs by keyword directly
	$program_ids_direct = get_posts( [
		'post_type'      => 'program',
		's'              => $keyword,
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'post_status'    => 'publish',
		'meta_query'     => [
			'relation' => 'AND',
			$meta_status_filter,
		],
	] );

	// 6. Query program IDs linked to matched schools
	$program_ids_schools = [];
	if ( ! empty( $school_ids ) ) {
		$program_ids_schools = get_posts( [
			'post_type'      => 'program',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => 'publish',
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => 'school_relationship',
					'value'   => $school_ids,
					'compare' => 'IN',
				],
				$meta_status_filter,
			],
		] );
	}

	// 7. Query program IDs linked to matched majors
	$program_ids_majors = [];
	if ( ! empty( $major_ids ) ) {
		$program_ids_majors = get_posts( [
			'post_type'      => 'program',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => 'publish',
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => 'major_relationship',
					'value'   => $major_ids,
					'compare' => 'IN',
				],
				$meta_status_filter,
			],
		] );
	}

	// Merge all matched program IDs
	$matched_ids = array_unique( array_merge( $program_ids_direct, $program_ids_schools, $program_ids_majors ) );

	// If no matches found, force empty result by passing post__in = [0]
	if ( empty( $matched_ids ) ) {
		$args['post__in'] = [ 0 ];
	} else {
		$args['post__in'] = $matched_ids;
	}

	// Unset keyword search "s" to avoid double search matching of program CPT's title only
	unset( $args['s'] );

	return $args;
}

/**
 * Expand search synonyms
 */
function ltdh_expand_search_synonyms( $s ) {
	$s_lower = mb_strtolower( trim( $s ), 'UTF-8' );

	$synonyms = [
		'cntt' => 'công nghệ thông tin',
		'qtkd' => 'quản trị kinh doanh',
		'kdtm' => 'kinh doanh thương mại',
		'nna'  => 'ngôn ngữ anh',
	];

	if ( isset( $synonyms[ $s_lower ] ) ) {
		return $synonyms[ $s_lower ];
	}

	return $s;
}
