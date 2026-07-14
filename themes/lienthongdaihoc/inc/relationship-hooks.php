<?php
/**
 * Bi-directional relationship hooks for Schools, Majors, and Programs
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/save_post', 'ltdh_sync_program_relationships', 20 );

function ltdh_sync_program_relationships( $post_id ) {
	// Only run on program post type
	if ( get_post_type( $post_id ) !== LTDH_CPT_PROGRAM ) {
		return;
	}

	// Prevent infinite loops
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// 1. Sync with School
	$school_id = get_field( LTDH_META_SCHOOL_REL, $post_id );
	$old_school_id = get_post_meta( $post_id, LTDH_META_LAST_SCHOOL, true );

	if ( $school_id ) {
		$school_programs = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
		if ( ! is_array( $school_programs ) ) {
			$school_programs = [];
		}
		if ( ! in_array( $post_id, $school_programs ) ) {
			$school_programs[] = $post_id;
			update_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, $school_programs );
		}
		update_post_meta( $post_id, LTDH_META_LAST_SCHOOL, $school_id );
	}

	// Remove from old school if changed
	if ( $old_school_id && $old_school_id != $school_id ) {
		$old_school_programs = get_post_meta( $old_school_id, LTDH_META_OFFERED_PROGRAMS, true );
		if ( is_array( $old_school_programs ) ) {
			$old_school_programs = array_diff( $old_school_programs, [ $post_id ] );
			update_post_meta( $old_school_id, '_offered_programs', $old_school_programs );
		}
	}

	// 2. Sync with Major
	$major_id = get_field( LTDH_META_MAJOR_REL, $post_id );
	$old_major_id = get_post_meta( $post_id, LTDH_META_LAST_MAJOR, true );

	if ( $major_id ) {
		$major_programs = get_post_meta( $major_id, LTDH_META_OFFERED_PROGRAMS, true );
		if ( ! is_array( $major_programs ) ) {
			$major_programs = [];
		}
		if ( ! in_array( $post_id, $major_programs ) ) {
			$major_programs[] = $post_id;
			update_post_meta( $major_id, LTDH_META_OFFERED_PROGRAMS, $major_programs );
		}
		update_post_meta( $post_id, LTDH_META_LAST_MAJOR, $major_id );
	}

	// Remove from old major if changed
	if ( $old_major_id && $old_major_id != $major_id ) {
		$old_major_programs = get_post_meta( $old_major_id, LTDH_META_OFFERED_PROGRAMS, true );
		if ( is_array( $old_major_programs ) ) {
			$old_major_programs = array_diff( $old_major_programs, [ $post_id ] );
			update_post_meta( $old_major_id, '_offered_programs', $old_major_programs );
		}
	}
}
