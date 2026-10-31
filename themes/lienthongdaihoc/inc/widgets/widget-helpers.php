<?php
/**
 * Shared Widget Helpers: Context Detection & Normalization.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper: Format phone number or URL into a valid Zalo chat URL.
 *
 * @param string $zalo_or_phone
 * @return string
 */
function ltdh_format_zalo_url( $zalo_or_phone ) {
	$val = trim( (string) $zalo_or_phone );
	if ( empty( $val ) ) {
		return function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
	}
	if ( 0 === strpos( $val, 'http://' ) || 0 === strpos( $val, 'https://' ) ) {
		return $val;
	}
	$digits = preg_replace( '/\D/', '', $val );
	if ( ! empty( $digits ) ) {
		return 'https://zalo.me/' . $digits;
	}
	return 'https://zalo.me';
}

/**
 * Helper: Retrieve all School IDs offering programs for a given Major.
 *
 * @param int $major_id
 * @return int[]
 */
function ltdh_get_schools_for_major( $major_id ) {
	$major_id = absint( $major_id );
	if ( ! $major_id ) {
		return [];
	}

	$cpt_prog = defined( 'LTDH_CPT_PROGRAM' ) ? LTDH_CPT_PROGRAM : 'program';
	$key_rel  = defined( 'LTDH_META_MAJOR_REL' ) ? LTDH_META_MAJOR_REL : 'major_relationship';

	$progs = get_posts( [
		'post_type'      => $cpt_prog,
		'post_status'    => 'publish',
		'posts_per_page' => 50,
		'fields'         => 'ids',
		'meta_query'     => [
			'relation' => 'OR',
			[
				'key'     => $key_rel,
				'value'   => $major_id,
				'compare' => '=',
			],
			[
				'key'     => $key_rel,
				'value'   => '"' . $major_id . '"',
				'compare' => 'LIKE',
			],
			[
				'key'     => $key_rel,
				'value'   => 's:' . strlen( (string) $major_id ) . ':"' . $major_id . '"',
				'compare' => 'LIKE',
			],
			[
				'key'     => $key_rel,
				'value'   => 'i:' . $major_id . ';',
				'compare' => 'LIKE',
			],
		],
	] );

	$school_ids = [];
	foreach ( $progs as $pid ) {
		$sid = (int) get_post_meta( $pid, 'school_relationship', true );
		if ( $sid > 0 ) {
			$school_ids[] = $sid;
		}
	}

	return array_values( array_unique( $school_ids ) );
}

/**
 * Helper: Detect the current School post ID from various contexts.
 *
 * @return int
 */
function ltdh_detect_current_school_id() {
	$cur_id    = get_the_ID();
	$post_type = $cur_id ? get_post_type( $cur_id ) : '';

	// 1. Direct school singular
	if ( 'school' === $post_type || is_singular( 'school' ) ) {
		return (int) $cur_id;
	}

	// 2. Query parameters (?truong=..., ?school=..., ?from_school=...)
	$param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );
	if ( ! empty( $param ) ) {
		if ( is_numeric( $param ) ) {
			return (int) $param;
		}
		$cpt_school = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
		$s_post     = get_page_by_path( sanitize_title( $param ), OBJECT, $cpt_school );
		if ( $s_post && 'publish' === $s_post->post_status ) {
			return (int) $s_post->ID;
		}
	}

	// 3. Theme helper if present
	if ( function_exists( 'ltdh_get_current_context_school_id' ) ) {
		$sid = ltdh_get_current_context_school_id();
		if ( $sid > 0 ) {
			return (int) $sid;
		}
	}

	// 4. Program singular -> school_relationship
	if ( 'program' === $post_type || is_singular( 'program' ) ) {
		$s_rel = function_exists( 'get_field' ) ? get_field( 'school_relationship', $cur_id ) : get_post_meta( $cur_id, 'school_relationship', true );
		if ( ! empty( $s_rel ) ) {
			if ( is_array( $s_rel ) ) {
				$first = reset( $s_rel );
				return is_object( $first ) ? (int) $first->ID : (int) $first;
			}
			return is_object( $s_rel ) ? (int) $s_rel->ID : (int) $s_rel;
		}
	}

	// 5. Post or guide singular -> school_relationship
	if ( in_array( $post_type, [ 'post', 'guide' ], true ) || is_singular( [ 'post', 'guide' ] ) ) {
		$s_rel = function_exists( 'get_field' ) ? get_field( 'school_relationship', $cur_id ) : get_post_meta( $cur_id, 'school_relationship', true );
		if ( ! empty( $s_rel ) ) {
			if ( is_array( $s_rel ) ) {
				$first = reset( $s_rel );
				return is_object( $first ) ? (int) $first->ID : (int) $first;
			}
			return is_object( $s_rel ) ? (int) $s_rel->ID : (int) $s_rel;
		}
	}

	return 0;
}

/**
 * Helper: Detect the current Major post ID from various contexts.
 *
 * @return int
 */
function ltdh_detect_current_major_id() {
	$cur_id    = get_the_ID();
	$post_type = $cur_id ? get_post_type( $cur_id ) : '';

	// 1. Single Major
	if ( 'major' === $post_type || is_singular( 'major' ) ) {
		return (int) $cur_id;
	}

	// 2. Single Program -> check major_relationship
	if ( 'program' === $post_type || is_singular( 'program' ) ) {
		$rel = function_exists( 'get_field' ) ? get_field( 'major_relationship', $cur_id ) : get_post_meta( $cur_id, 'major_relationship', true );
		if ( is_array( $rel ) && ! empty( $rel ) ) {
			$first = is_object( $rel[0] ) ? $rel[0]->ID : $rel[0];
			if ( (int) $first > 0 ) {
				return (int) $first;
			}
		} elseif ( is_object( $rel ) && ! empty( $rel->ID ) ) {
			return (int) $rel->ID;
		} elseif ( is_numeric( $rel ) && (int) $rel > 0 ) {
			return (int) $rel;
		}
	}

	// 3. Single Post or Guide -> check related_majors
	if ( in_array( $post_type, [ 'post', 'guide' ], true ) || is_singular( [ 'post', 'guide' ] ) ) {
		$related = function_exists( 'get_field' ) ? get_field( 'related_majors', $cur_id ) : get_post_meta( $cur_id, 'related_majors', true );
		if ( is_array( $related ) && ! empty( $related ) ) {
			$first = is_object( $related[0] ) ? $related[0]->ID : $related[0];
			if ( (int) $first > 0 ) {
				return (int) $first;
			}
		} elseif ( is_numeric( $related ) && (int) $related > 0 ) {
			return (int) $related;
		}
	}

	// 4. URL parameters (?nganh=..., ?major=..., ?major_id=...)
	$param = isset( $_GET['nganh'] ) ? sanitize_text_field( wp_unslash( $_GET['nganh'] ) ) : ( isset( $_GET['major'] ) ? sanitize_text_field( wp_unslash( $_GET['major'] ) ) : ( isset( $_GET['major_id'] ) ? sanitize_text_field( wp_unslash( $_GET['major_id'] ) ) : '' ) );
	if ( ! empty( $param ) ) {
		if ( is_numeric( $param ) ) {
			return (int) $param;
		}
		$cpt_major = defined( 'LTDH_CPT_MAJOR' ) ? LTDH_CPT_MAJOR : 'major';
		$m_post    = get_page_by_path( sanitize_title( $param ), OBJECT, $cpt_major );
		if ( $m_post && 'publish' === $m_post->post_status ) {
			return (int) $m_post->ID;
		}
	}

	return 0;
}

/**
 * Helper: Detect the current Program post ID from various contexts.
 *
 * @return int
 */
function ltdh_detect_current_program_id() {
	$cur_id    = get_the_ID();
	$post_type = $cur_id ? get_post_type( $cur_id ) : '';

	// 1. Single Program
	if ( 'program' === $post_type || is_singular( 'program' ) ) {
		return (int) $cur_id;
	}

	// 2. Query parameter (?chuong-trinh=..., ?program=..., ?program_id=...)
	$param = isset( $_GET['chuong-trinh'] ) ? sanitize_text_field( wp_unslash( $_GET['chuong-trinh'] ) ) : ( isset( $_GET['program'] ) ? sanitize_text_field( wp_unslash( $_GET['program'] ) ) : ( isset( $_GET['program_id'] ) ? sanitize_text_field( wp_unslash( $_GET['program_id'] ) ) : '' ) );
	if ( ! empty( $param ) ) {
		if ( is_numeric( $param ) ) {
			return (int) $param;
		}
		$cpt_prog = defined( 'LTDH_CPT_PROGRAM' ) ? LTDH_CPT_PROGRAM : 'program';
		$p_post   = get_page_by_path( sanitize_title( $param ), OBJECT, $cpt_prog );
		if ( $p_post && 'publish' === $p_post->post_status ) {
			return (int) $p_post->ID;
		}
	}

	return 0;
}
