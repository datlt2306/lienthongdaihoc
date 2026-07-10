<?php
/**
 * SEO Architecture & Rank Math Integration
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Dynamic Title Tag Override
// ----------------------------------------------------
add_filter( 'rank_math/frontend/title', 'ltdh_seo_dynamic_title' );

function ltdh_seo_dynamic_title( $title ) {
	if ( is_singular( 'program' ) ) {
		$program_id = get_the_ID();
		$school_id  = get_field( 'school_relationship', $program_id );
		$school     = $school_id ? get_the_title( $school_id ) : '';
		
		// Get training type term name
		$training_type = '';
		$terms = wp_get_post_terms( $program_id, 'training_type' );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$training_type = $terms[0]->name;
		}

		$year = date( 'Y' );
		return sprintf( 'Học %s (%s) - %s | Tuyển sinh %d', get_the_title(), $training_type ?: 'Từ xa', $school, $year );
	}
	return $title;
}

// ----------------------------------------------------
// 2. Dynamic Meta Description Override
// ----------------------------------------------------
add_filter( 'rank_math/frontend/description', 'ltdh_seo_dynamic_description' );

function ltdh_seo_dynamic_description( $desc ) {
	if ( is_singular( 'program' ) ) {
		$program_id = get_the_ID();
		$school_id  = get_field( 'school_relationship', $program_id );
		$school     = $school_id ? get_the_title( $school_id ) : '';
		$tuition    = get_field( 'tuition_fee', $program_id ) ?: 'Liên hệ';
		$duration   = get_field( 'duration', $program_id ) ?: '1.5 - 2 năm';
		
		$training_type = '';
		$terms = wp_get_post_terms( $program_id, 'training_type' );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$training_type = $terms[0]->name;
		}

		return sprintf( 
			'Đăng ký tuyển sinh lớp %s (%s) tại trường %s. Thời gian đào tạo %s, học phí chỉ %s. Nhận tư vấn lộ trình và hướng dẫn làm hồ sơ miễn phí.',
			get_the_title(),
			$training_type ?: 'Từ xa',
			$school,
			$duration,
			$tuition
		);
	}
	return $desc;
}

// ----------------------------------------------------
// 3. Schema JSON-LD Injection
// ----------------------------------------------------
add_filter( 'rank_math/json_ld', 'ltdh_seo_inject_custom_schema', 99, 2 );

function ltdh_seo_inject_custom_schema( $data, $json_ld ) {
	if ( is_singular( 'program' ) ) {
		$program_id = get_the_ID();
		$school_id  = get_field( 'school_relationship', $program_id );
		$school     = $school_id ? get_the_title( $school_id ) : 'Liên kết';
		
		// Course Schema
		$data['ltdh_course'] = [
			'@context'    => 'https://schema.org',
			'@type'       => 'Course',
			'name'        => get_the_title(),
			'description' => get_the_excerpt() ?: 'Chương trình đào tạo đại học tuyển sinh chất lượng cao.',
			'provider'    => [
				'@type' => 'CollegeOrUniversity',
				'name'  => $school,
				'url'   => $school_id ? get_field( 'website', $school_id ) : home_url( '/' ),
			]
		];

		// FAQ Schema if repeaters are populated
		$faqs = get_field( 'faq', $program_id );
		if ( ! empty( $faqs ) && is_array( $faqs ) ) {
			$faq_elements = [];
			foreach ( $faqs as $item ) {
				$faq_elements[] = [
					'@type'          => 'Question',
					'name'           => $item['question'],
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => $item['answer'],
					],
				];
			}
			$data['ltdh_faq'] = [
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => $faq_elements,
			];
		}
	}

	if ( is_singular( 'school' ) ) {
		$school_id = get_the_ID();
		$data['ltdh_university'] = [
			'@context' => 'https://schema.org',
			'@type'    => 'CollegeOrUniversity',
			'name'     => get_the_title(),
			'url'      => get_field( 'website', $school_id ) ?: get_permalink(),
			'address'  => [
				'@type'           => 'PostalAddress',
				'streetAddress'   => get_field( 'address', $school_id ) ?: '',
				'addressCountry'  => 'VN',
			],
			'telephone' => get_field( 'hotline', $school_id ) ?: '',
		];
	}

	return $data;
}

// ----------------------------------------------------
// 4. Override Breadcrumb Items for School CPT
// ----------------------------------------------------
add_filter( 'rank_math/frontend/breadcrumb/items', 'ltdh_seo_override_breadcrumb_items', 10, 2 );

function ltdh_seo_override_breadcrumb_items( $crumbs, $class ) {
	foreach ( $crumbs as $key => $crumb ) {
		$lower_label = mb_strtolower( $crumb[0], 'UTF-8' );
		if ( $lower_label === 'trường học' || $lower_label === 'truong' || $lower_label === 'truong-lien-ket' || $lower_label === 'schools' || $lower_label === 'school' ) {
			$crumbs[$key][0] = 'Trường liên kết';
		}
	}
	return $crumbs;
}

// ----------------------------------------------------
// 5. Dynamic Title & Description for Comparison Pages
// ----------------------------------------------------
add_filter( 'rank_math/frontend/title', 'ltdh_seo_compare_title' );
add_filter( 'rank_math/frontend/description', 'ltdh_seo_compare_description' );

function ltdh_seo_compare_title( $title ) {
	$type = get_query_var( 'ltdh_compare' );
	if ( ! $type ) {
		return $title;
	}

	$items = ltdh_compare_get_items();
	if ( count( $items ) < 2 ) {
		return $title;
	}

	$names = array_map( function( $item ) { return $item['title']; }, $items );
	$type_labels = [ 'program' => 'chương trình', 'school' => 'trường' ];
	$type_label  = $type_labels[ $type ] ?? 'chương trình';

	return sprintf( 'So sánh %s %s | LTDH', implode( ' vs ', $names ), $type_label );
}

function ltdh_seo_compare_description( $desc ) {
	$type = get_query_var( 'ltdh_compare' );
	if ( ! $type ) {
		return $desc;
	}

	$items = ltdh_compare_get_items();
	if ( count( $items ) < 2 ) {
		return $desc;
	}

	$names = array_map( function( $item ) { return $item['title']; }, $items );
	return sprintf(
		'So sánh chi tiết %s. Xem học phí, thời gian đào tạo, điều kiện tuyển sinh và cơ hội việc làm.',
		implode( ', ', $names )
	);
}

// ----------------------------------------------------
// 6. JSON-LD Schema for Comparison Pages
// ----------------------------------------------------
add_filter( 'rank_math/json_ld', 'ltdh_seo_compare_schema', 99, 2 );

function ltdh_seo_compare_schema( $data, $json_ld ) {
	$type = get_query_var( 'ltdh_compare' );
	if ( ! $type || $type !== 'program' ) {
		return $data;
	}

	$items = ltdh_compare_get_items();
	if ( count( $items ) < 2 ) {
		return $data;
	}

	// Add Course schema for each compared program
	foreach ( $items as $index => $item ) {
		$school_name = $item['school'] ? $item['school']['title'] : 'Liên kết';
		$school_url  = $item['school'] && $item['school']['website'] ? $item['school']['website'] : home_url( '/' );

		$data[ 'ltdh_compare_course_' . $index ] = [
			'@context'    => 'https://schema.org',
			'@type'       => 'Course',
			'name'        => $item['title'],
			'description' => $item['excerpt'] ?: 'Chương trình đào tạo đại học.',
			'provider'    => [
				'@type' => 'CollegeOrUniversity',
				'name'  => $school_name,
				'url'   => $school_url,
			],
		];
	}

	return $data;
}
