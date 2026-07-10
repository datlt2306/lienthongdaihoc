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
