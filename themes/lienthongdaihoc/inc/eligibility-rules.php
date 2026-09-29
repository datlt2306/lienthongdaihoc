<?php
/**
 * Eligibility Checker — Rule Definitions
 *
 * Hard filters, soft scoring, and conversion hooks.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ----------------------------------------------------
// TRAINING TYPE COMPATIBILITY MATRIX
// Which education levels can access which training types
// ----------------------------------------------------
function ltdh_elig_get_training_type_compatibility() {
	return [
		'thap-phan'    => [ 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],
		'trung-cap'    => [ 'lien-thong', 'van-bang-2', 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],
		'cao-dang'     => [ 'lien-thong', 'van-bang-2', 'tu-xa', 'vua-hoc-vua-lam', 'chinh-quy' ],
		'dai-hoc'      => [ 'van-bang-2', 'tu-xa', 'vua-hoc-vua-lam' ],
		'thac-si'      => [ 'van-bang-2', 'tu-xa' ],
	];

	/*
	 * Liên thông: requires same-level or lower degree (Trung cấp/Cao đẳng)
	 * VB2: requires existing degree (Cao đẳng+)
	 * Từ xa/Vừa học vừa làm: compatible with all levels
	 * Chính quy: THPT/Trung cấp/Cao đẳng only
	 */
}

// ----------------------------------------------------
// MAJOR RELATIONSHIP MAP
// Which majors are considered "related" for scoring
// ----------------------------------------------------
function ltdh_elig_get_major_relationships() {
	/**
	 * Returns array of major_id => [related_major_ids]
	 *
	 * This is populated dynamically from ACF relationship field
	 * `field_major_related` on each major CPT.
	 *
	 * Fallback: use keyword-based matching if ACF data unavailable.
	 */
	return [
		'ke-toan'       => [ 'kiem-toan', 'tai-chinh', 'ngan-hang' ],
		'quan-tri-kinh-doanh' => [ 'marketing', 'kinh-doanh-quoc-te', 'thuong-mai' ],
		'cong-nghe-thong-tin' => [ 'ky-thuat-phan-mem', 'an-toan-thong-tin', 'he-thong-thong-tin' ],
		'ngon-ngu-anh'  => [ 'ngon-ngu-nhat', 'ngon-ngu-trung', 'dich-thuat' ],
		'marketing'     => [ 'quang-cao', 'pr', 'truyen-thong' ],
		'kinh-doanh-thuong-mai' => [ 'quan-tri-kinh-doanh', 'marketing', 'logistics' ],
	];
}

// ----------------------------------------------------
// BUDGET RANGES
// Maps user budget options to numeric ranges
// ----------------------------------------------------
function ltdh_elig_get_budget_ranges() {
	return [
		'duoi-20-trieu'   => [ 'min' => 0,        'max' => 20000000 ],
		'20-30-trieu'     => [ 'min' => 20000000, 'max' => 30000000 ],
		'30-50-trieu'     => [ 'min' => 30000000, 'max' => 50000000 ],
		'tren-50-trieu'   => [ 'min' => 50000000, 'max' => PHP_INT_MAX ],
	];
}

// ----------------------------------------------------
// EDUCATION LEVEL HIERARCHY
// For comparison in hard filters
// ----------------------------------------------------
function ltdh_elig_get_education_hierarchy() {
	return [
		'thap-phan'  => 1,
		'trung-cap'  => 2,
		'cao-dang'   => 3,
		'dai-hoc'    => 4,
		'thac-si'    => 5,
	];
}

// ----------------------------------------------------
// SCHEDULE PREFERENCE MAP
// Maps user preference to schedule keywords
// ----------------------------------------------------
function ltdh_elig_get_schedule_keywords() {
	return [
		'toi'         => [ 'tối', 'toi', 'evening', 'đêm' ],
		'cuoi-tuan'   => [ 'thứ 7', 'thu 7', 'CN', 'cuối tuần', 'weekend', 'chủ nhật' ],
		'online'      => [ 'online', 'trực tuyến', 'e-learning', 'từ xa' ],
		'linh-hoat'   => [ 'linh hoạt', 'linh hoat', 'flexible', 'tự chọn' ],
	];
}

// ----------------------------------------------------
// SCORING WEIGHTS
// ----------------------------------------------------
function ltdh_elig_get_scoring_weights() {
	return [
		'major_match'        => 30,  // Max 30 points
		'major_related'      => 15,  // If related major
		'graduation_recent'  => 10,  // Max 10 points
		'budget_match'       => 20,  // Max 20 points
		'campus_match'       => 10,  // Max 10 points
		'schedule_match'     => 5,   // Max 5 points
	];
}

// ----------------------------------------------------
// CHECK IF MAJORS ARE RELATED
// ----------------------------------------------------
function ltdh_elig_are_majors_related( $major_id_1, $major_id_2 ) {
	if ( ! $major_id_1 || ! $major_id_2 ) {
		return false;
	}
	if ( (int) $major_id_1 === (int) $major_id_2 ) {
		return true;
	}

	// 1. Check ACF relationship field `major_related` on major_id_1
	if ( function_exists( 'get_field' ) ) {
		$related_posts = get_field( 'major_related', $major_id_1 );
		if ( ! empty( $related_posts ) ) {
			foreach ( $related_posts as $p ) {
				$related_id = is_object( $p ) ? $p->ID : ( is_array( $p ) ? $p['ID'] : intval( $p ) );
				if ( $related_id === (int) $major_id_2 ) {
					return true;
				}
			}
		}

		// Also check the other way around
		$related_posts_2 = get_field( 'major_related', $major_id_2 );
		if ( ! empty( $related_posts_2 ) ) {
			foreach ( $related_posts_2 as $p ) {
				$related_id = is_object( $p ) ? $p->ID : ( is_array( $p ) ? $p['ID'] : intval( $p ) );
				if ( $related_id === (int) $major_id_1 ) {
					return true;
				}
			}
		}
	}

	// 2. Fallback: Slug-based check using ltdh_elig_get_major_relationships()
	$slug_1 = get_post_field( 'post_name', $major_id_1 );
	$slug_2 = get_post_field( 'post_name', $major_id_2 );
	if ( $slug_1 && $slug_2 ) {
		$relationships = ltdh_elig_get_major_relationships();
		if ( isset( $relationships[ $slug_1 ] ) && in_array( $slug_2, $relationships[ $slug_1 ], true ) ) {
			return true;
		}
		if ( isset( $relationships[ $slug_2 ] ) && in_array( $slug_1, $relationships[ $slug_2 ], true ) ) {
			return true;
		}
	}

	return false;
}

