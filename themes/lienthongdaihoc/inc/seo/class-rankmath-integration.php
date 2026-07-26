<?php
/**
 * Rank Math SEO Integration — Dynamic titles, descriptions, schema, and breadcrumbs.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Dynamic Title for Programs
// ----------------------------------------------------
function ltdh_seo_dynamic_title( $title ) {
	if ( is_singular( LTDH_CPT_PROGRAM ) ) {
		$program_id = get_the_ID();
		$school_id  = intval( get_field( LTDH_META_SCHOOL_REL, $program_id ) ?: 0 );
		$school     = $school_id ? get_the_title( $school_id ) : '';

		$training_type = '';
		$terms = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$training_type = $terms[0]->name;
		}

		$year = (int) date( 'Y' );
		return sprintf( 'Học %s (%s) - %s | Tuyển sinh %d', get_the_title(), $training_type ?: 'Từ xa', $school, $year );
	}
	return $title;
}
add_filter( 'rank_math/frontend/title', 'ltdh_seo_dynamic_title' );

// ----------------------------------------------------
// 2. Dynamic Meta Description for Programs
// ----------------------------------------------------
function ltdh_seo_dynamic_description( $desc ) {
	if ( is_singular( LTDH_CPT_PROGRAM ) ) {
		$program_id = get_the_ID();
		$school_id  = intval( get_field( LTDH_META_SCHOOL_REL, $program_id ) ?: 0 );
		$school     = $school_id ? get_the_title( $school_id ) : '';
		$tuition    = get_field( LTDH_META_TUITION, $program_id ) ?: 'Liên hệ';
		$duration   = get_field( LTDH_META_DURATION, $program_id ) ?: '1.5 - 2 năm';

		$training_type = '';
		$terms = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
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
add_filter( 'rank_math/frontend/description', 'ltdh_seo_dynamic_description' );

// ----------------------------------------------------
// 3. Schema JSON-LD Injection
// ----------------------------------------------------
function ltdh_seo_inject_custom_schema( $data, $json_ld ) {
	if ( is_singular( LTDH_CPT_PROGRAM ) ) {
		$program_id = get_the_ID();
		$school_id  = intval( get_field( LTDH_META_SCHOOL_REL, $program_id ) ?: 0 );
		$school     = $school_id ? get_the_title( $school_id ) : 'Đối tác';

		$data['ltdh_course'] = [
			'@context'    => 'https://schema.org',
			'@type'       => 'Course',
			'name'        => get_the_title(),
			'description' => get_the_excerpt() ?: 'Chương trình đào tạo đại học tuyển sinh chất lượng cao.',
			'provider'    => [
				'@type' => 'CollegeOrUniversity',
				'name'  => $school,
				'url'   => $school_id ? get_field( 'website', $school_id ) : home_url( '/' ),
			],
		];

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

	if ( is_singular( LTDH_CPT_SCHOOL ) ) {
		$school_id = get_the_ID();
		$data['ltdh_university'] = [
			'@context' => 'https://schema.org',
			'@type'    => 'CollegeOrUniversity',
			'name'     => get_the_title(),
			'url'      => get_field( 'website', $school_id ) ?: get_permalink(),
			'address'  => [
				'@type'          => 'PostalAddress',
				'streetAddress'  => get_field( 'address', $school_id ) ?: '',
				'addressCountry' => 'VN',
			],
			'telephone' => get_field( 'hotline', $school_id ) ?: '',
		];
	}

	return $data;
}
add_filter( 'rank_math/json_ld', 'ltdh_seo_inject_custom_schema', 99, 2 );

// ----------------------------------------------------
// 4. Override Breadcrumb Labels & Tin tức Structure
// ----------------------------------------------------

/**
 * Build breadcrumb crumb array helper.
 *
 * @param string $label Display label.
 * @param string $url   URL (empty string for last item).
 * @return array
 */
function ltdh_seo_crumb( $label, $url = '' ) {
	return [ $label, $url ];
}

/**
 * Override breadcrumb items for:
 * - CPT archive labels (Trường đối tác, etc.)
 * - Blog archive (is_home): Trang chủ > Tin tức
 * - Category archive (is_category): Trang chủ > Tin tức > [Tên danh mục]
 * - Single post (is_single + post): Trang chủ > Tin tức > [Tên danh mục] > [Tiêu đề bài]
 */
function ltdh_seo_override_breadcrumb_items( $crumbs, $class ) {
	$home_url   = home_url( '/' );
	$home_label = 'Trang chủ';

	// ---- Blog archive: is_home() ----
	if ( is_home() ) {
		return [
			ltdh_seo_crumb( $home_label, $home_url ),
			ltdh_seo_crumb( 'Tin tức' ),
		];
	}

	// ---- Category archive: is_category() ----
	if ( is_category() ) {
		$tin_tuc_url = get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/tin-tuc/' );
		$cat         = get_queried_object();
		return [
			ltdh_seo_crumb( $home_label, $home_url ),
			ltdh_seo_crumb( 'Tin tức', $tin_tuc_url ),
			ltdh_seo_crumb( $cat ? $cat->name : 'Danh mục' ),
		];
	}

	// ---- Single post (post type = post): Trang chủ > Tin tức > [Cat] > [Title] ----
	if ( is_singular( 'post' ) ) {
		$tin_tuc_url = get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/tin-tuc/' );
		$post_id     = get_the_ID();
		$post_cats   = get_the_category( $post_id );

		// Pick first non-uncategorized category
		$post_cat = null;
		foreach ( $post_cats as $pc ) {
			if ( 'uncategorized' !== $pc->slug ) {
				$post_cat = $pc;
				break;
			}
		}

		$new_crumbs = [
			ltdh_seo_crumb( $home_label, $home_url ),
			ltdh_seo_crumb( 'Tin tức', $tin_tuc_url ),
		];

		if ( $post_cat ) {
			$new_crumbs[] = ltdh_seo_crumb( $post_cat->name, get_category_link( $post_cat->term_id ) );
		}

		$new_crumbs[] = ltdh_seo_crumb( get_the_title() );

		return $new_crumbs;
	}

	// ---- Default: fix CPT archive labels ----
	foreach ( $crumbs as $key => $crumb ) {
		$lower_label = mb_strtolower( $crumb[0], 'UTF-8' );
		if ( in_array( $lower_label, [ 'trường học', 'truong', 'truong-lien-ket', 'truong-doi-tac', 'trường đối tác', 'schools', 'school' ], true ) ) {
			$crumbs[ $key ][0] = 'Trường đối tác';
		}
	}

	return $crumbs;
}
add_filter( 'rank_math/frontend/breadcrumb/items', 'ltdh_seo_override_breadcrumb_items', 10, 2 );

// ----------------------------------------------------
// 5. Comparison Page SEO
// ----------------------------------------------------
function ltdh_seo_compare_title( $title ) {
	$type = get_query_var( LTDH_QV_COMPARE );
	if ( ! $type ) {
		return $title;
	}

	$items = function_exists( 'ltdh_compare_get_items' ) ? ltdh_compare_get_items() : [];
	if ( count( $items ) < 2 ) {
		return $title;
	}

	$names      = array_map( fn( $item ) => $item['title'], $items );
	$type_labels = [ 'program' => 'chương trình', 'school' => 'trường' ];
	$type_label  = $type_labels[ $type ] ?? 'chương trình';

	return sprintf( 'So sánh %s %s | LTDH', implode( ' vs ', $names ), $type_label );
}
add_filter( 'rank_math/frontend/title', 'ltdh_seo_compare_title' );

function ltdh_seo_compare_description( $desc ) {
	$type = get_query_var( LTDH_QV_COMPARE );
	if ( ! $type ) {
		return $desc;
	}

	$items = function_exists( 'ltdh_compare_get_items' ) ? ltdh_compare_get_items() : [];
	if ( count( $items ) < 2 ) {
		return $desc;
	}

	$names = array_map( fn( $item ) => $item['title'], $items );
	return sprintf(
		'So sánh chi tiết %s. Xem học phí, thời gian đào tạo, điều kiện tuyển sinh và cơ hội việc làm.',
		implode( ', ', $names )
	);
}
add_filter( 'rank_math/frontend/description', 'ltdh_seo_compare_description' );

// ----------------------------------------------------
// 6. Comparison Schema
// ----------------------------------------------------
function ltdh_seo_compare_schema( $data, $json_ld ) {
	$type = get_query_var( LTDH_QV_COMPARE );
	if ( ! $type || $type !== 'program' ) {
		return $data;
	}

	$items = function_exists( 'ltdh_compare_get_items' ) ? ltdh_compare_get_items() : [];
	if ( count( $items ) < 2 ) {
		return $data;
	}

	foreach ( $items as $index => $item ) {
		$school_name = $item['school'] ? $item['school']['title'] : 'Đối tác';
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
add_filter( 'rank_math/json_ld', 'ltdh_seo_compare_schema', 99, 2 );

// ----------------------------------------------------
// 7. Open Graph & Social Share Image
// ----------------------------------------------------

/**
 * Filter Rank Math OpenGraph Image.
 */
add_filter( 'rank_math/opengraph/facebook/image', 'ltdh_seo_fallback_og_image' );
add_filter( 'rank_math/opengraph/twitter/image', 'ltdh_seo_fallback_og_image' );

function ltdh_seo_fallback_og_image( $image_url ) {
	if ( ! empty( $image_url ) ) {
		return $image_url;
	}
	return get_template_directory_uri() . '/assets/images/banner-program.jpg';
}

/**
 * Output og:image tag in wp_head as a robust fallback for general sharing.
 */
add_action( 'wp_head', function() {
	if ( class_exists( 'RankMath' ) ) {
		return;
	}
	
	$thumbnail_url = get_template_directory_uri() . '/assets/images/banner-program.jpg';
	
	if ( is_singular() && has_post_thumbnail() ) {
		$thumbnail_url = get_the_post_thumbnail_url( null, 'large' );
	}
	
	echo "\n" . '<!-- Social Share Meta Tags -->' . "\n";
	echo '<meta property="og:image" content="' . esc_url( $thumbnail_url ) . '" />' . "\n";
	echo '<meta property="og:image:width" content="1200" />' . "\n";
	echo '<meta property="og:image:height" content="630" />' . "\n";
	echo '<meta name="twitter:image" content="' . esc_url( $thumbnail_url ) . '" />' . "\n";
}, 5 );

