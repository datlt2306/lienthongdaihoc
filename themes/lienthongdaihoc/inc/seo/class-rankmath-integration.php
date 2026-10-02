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

/**
 * Dynamic Document Title for Archive & Virtual Route Listings
 */
function ltdh_seo_archive_document_title( $title ) {
	$request_uri  = $_SERVER['REQUEST_URI'] ?? '';
	$request_path = parse_url( $request_uri, PHP_URL_PATH );
	$site_name    = get_bloginfo( 'name' );

	if (
		is_post_type_archive( 'program' ) ||
		is_tax( 'training_type' ) ||
		is_tax( 'major_cat' ) ||
		is_tax( 'region' ) ||
		( ! empty( $request_path ) && preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao|chuong-trinh)/?#i', $request_path ) )
	) {
		$page_title = 'Hình thức đào tạo';

		if ( is_tax( 'training_type' ) ) {
			$term = get_queried_object();
			if ( $term && isset( $term->name ) ) {
				$clean_term_name = preg_replace( '/^hệ\s+/iu', '', $term->name );
				$page_title = 'Hình thức đào tạo: ' . $clean_term_name;
			}
		} elseif ( ! empty( $request_path ) && preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $m ) && 'page' !== $m[1] ) {
			$slug = sanitize_text_field( $m[1] );
			$term = get_term_by( 'slug', $slug, 'training_type' );
			if ( $term && ! is_wp_error( $term ) && isset( $term->name ) ) {
				$clean_term_name = preg_replace( '/^hệ\s+/iu', '', $term->name );
				$page_title = 'Hình thức đào tạo: ' . $clean_term_name;
			}
		} elseif ( is_tax( 'major_cat' ) ) {
			$term = get_queried_object();
			if ( $term && isset( $term->name ) ) {
				$page_title = 'Ngành học: ' . $term->name;
			}
		} elseif ( is_tax( 'region' ) ) {
			$term = get_queried_object();
			if ( $term && isset( $term->name ) ) {
				$page_title = 'Tuyển sinh Liên thông Đại học ' . $term->name;
			}
		}

		return $page_title . ' | ' . $site_name;
	}

	if ( is_post_type_archive( 'major' ) || ( ! empty( $request_path ) && preg_match( '#^/nganh-hoc/?#i', $request_path ) ) ) {
		return 'Ngành học | ' . $site_name;
	}

	if ( is_post_type_archive( 'school' ) || ( ! empty( $request_path ) && preg_match( '#^/truong-da-hoc/?#i', $request_path ) ) ) {
		return 'Trường đối tác | ' . $site_name;
	}

	return $title;
}
add_filter( 'rank_math/frontend/title', 'ltdh_seo_archive_document_title', 20 );
add_filter( 'pre_get_document_title', 'ltdh_seo_archive_document_title', 20 );
add_filter( 'wp_title', 'ltdh_seo_archive_document_title', 20 );

// ----------------------------------------------------
// 2. Dynamic Meta Description for Programs
// ----------------------------------------------------
function ltdh_seo_dynamic_description( $desc ) {
	if ( is_singular( LTDH_CPT_PROGRAM ) ) {
		$program_id = get_the_ID();
		$school_id  = intval( get_field( LTDH_META_SCHOOL_REL, $program_id ) ?: 0 );
		$school     = $school_id ? get_the_title( $school_id ) : '';
		$tuition    = ltdh_get_program_tuition_display( $program_id );
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

/**
 * Enforce flat Canonical URL for program and school posts (/%slug%/),
 * and point program archive canonical cleanly to /hinh-thuc-dao-tao/ to eliminate redirect loops.
 */
function ltdh_seo_enforce_canonical_url( $canonical ) {
	if ( is_singular( 'program' ) || is_singular( 'school' ) ) {
		$post_id = get_the_ID();
		if ( $post_id ) {
			return home_url( '/' . get_post_field( 'post_name', $post_id ) . '/' );
		}
	}

	$request_uri  = $_SERVER['REQUEST_URI'] ?? '';
	$request_path = parse_url( $request_uri, PHP_URL_PATH );

	if ( is_post_type_archive( 'program' ) || is_tax( 'training_type' ) || preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao|chuong-trinh)/?#i', $request_path ) ) {
		// Clean parameter mapping
		$truong = isset( $_GET['truong'] ) ? sanitize_text_field( $_GET['truong'] ) : '';
		$nganh  = isset( $_GET['nganh'] ) ? sanitize_text_field( $_GET['nganh'] ) : '';
		$sort   = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : '';
		$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

		// If single school filter only, point canonical to clean school page
		if ( ! empty( $truong ) && empty( $nganh ) && empty( $search ) && empty( $sort ) ) {
			$school_post = get_page_by_path( $truong, OBJECT, 'school' );
			if ( $school_post ) {
				return home_url( '/' . $school_post->post_name . '/' );
			}
		}

		// If single major filter only, point canonical to clean major page
		if ( ! empty( $nganh ) && empty( $truong ) && empty( $search ) && empty( $sort ) ) {
			$major_post = get_page_by_path( $nganh, OBJECT, 'major' );
			if ( $major_post ) {
				return home_url( '/nganh-' . $major_post->post_name . '/' );
			}
		}

		// Default clean base canonical for filter combinations
		return home_url( '/hinh-thuc-dao-tao/' );
	}

	return $canonical;
}
add_filter( 'rank_math/frontend/canonical', 'ltdh_seo_enforce_canonical_url' );
add_filter( 'rank_math/paper/canonical_url', 'ltdh_seo_enforce_canonical_url' );

/**
 * Set noindex, follow on dynamic sorting and search filter combinations to prevent crawl bloat
 */
function ltdh_seo_robots_filter( $robots ) {
	if ( ! is_array( $robots ) ) {
		$robots = [];
	}
	if ( ! empty( $_GET['s'] ) || ! empty( $_GET['sort'] ) || ( ! empty( $_GET['truong'] ) && ! empty( $_GET['nganh'] ) ) ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}
	return $robots;
}
add_filter( 'rank_math/frontend/robots', 'ltdh_seo_robots_filter' );

/**
 * Ensure Rank Math XML Sitemaps use flat URLs for school posts
 */
function ltdh_seo_rankmath_sitemap_school_url( $url, $post ) {
	if ( isset( $post->post_type ) && 'school' === $post->post_type ) {
		return home_url( '/' . $post->post_name . '/' );
	}
	return $url;
}
add_filter( 'rank_math/sitemap/entry', 'ltdh_seo_rankmath_sitemap_school_url', 10, 2 );

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
	if ( count( $items ) < 1 ) {
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
	if ( count( $items ) < 1 ) {
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
	if ( count( $items ) < 1 ) {
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
	if ( function_exists( 'get_field' ) ) {
		$custom_share_image = get_field( 'global_share_image', 'options' );
		if ( ! empty( $custom_share_image ) ) {
			return $custom_share_image;
		}
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
	
	$thumbnail_url = '';
	
	if ( is_singular() && has_post_thumbnail() ) {
		$thumbnail_url = get_the_post_thumbnail_url( null, 'large' );
	}
	
	if ( empty( $thumbnail_url ) && function_exists( 'get_field' ) ) {
		$thumbnail_url = get_field( 'global_share_image', 'options' );
	}
	
	if ( empty( $thumbnail_url ) ) {
		$thumbnail_url = get_template_directory_uri() . '/assets/images/banner-program.jpg';
	}
	
	echo "\n" . '<!-- Social Share Meta Tags -->' . "\n";
	echo '<meta property="og:image" content="' . esc_url( $thumbnail_url ) . '" />' . "\n";
	echo '<meta property="og:image:width" content="1200" />' . "\n";
	echo '<meta property="og:image:height" content="630" />' . "\n";
	echo '<meta name="twitter:image" content="' . esc_url( $thumbnail_url ) . '" />' . "\n";
}, 5 );

/**
 * Output native JSON-LD Schema fallback if Rank Math is not installed or inactive.
 */
add_action( 'wp_head', 'ltdh_output_native_schema_fallback', 2 );
function ltdh_output_native_schema_fallback() {
	if ( class_exists( 'RankMath' ) ) {
		return;
	}

	$schemas = [];

	// 1. Schema EducationalOrganization cho toàn website
	$contact_defaults = function_exists( 'ltdh_get_defaults' ) ? ltdh_get_defaults( 'contact' ) : [];
	$schemas[] = [
		'@context'    => 'https://schema.org',
		'@type'       => 'EducationalOrganization',
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'logo'        => function_exists( 'ltdh_get_logo_url' ) ? ltdh_get_logo_url() : '',
		'description' => get_bloginfo( 'description' ),
		'telephone'   => $contact_defaults['hotline'] ?? '',
		'email'       => $contact_defaults['email'] ?? '',
		'address'     => [
			'@type'          => 'PostalAddress',
			'streetAddress'  => $contact_defaults['address'] ?? '',
			'addressCountry' => 'VN',
		],
	];

	// 2. Schema Course cho chi tiết chương trình
	if ( defined( 'LTDH_CPT_PROGRAM' ) && is_singular( LTDH_CPT_PROGRAM ) ) {
		$program_id  = get_the_ID();
		$school_id   = intval( function_exists( 'get_field' ) && defined( 'LTDH_META_SCHOOL_REL' ) ? get_field( LTDH_META_SCHOOL_REL, $program_id ) : 0 );
		$tuition_raw = function_exists( 'get_field' ) && defined( 'LTDH_META_TUITION' ) ? get_field( LTDH_META_TUITION, $program_id ) : '';
		$duration    = ( function_exists( 'get_field' ) && defined( 'LTDH_META_DURATION' ) ? get_field( LTDH_META_DURATION, $program_id ) : '' ) ?: '1.5 - 2.5 năm';
		$mode_terms  = defined( 'LTDH_TAX_TRAINING_TYPE' ) ? wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE ) : [];
		$mode_name   = ( ! empty( $mode_terms ) && ! is_wp_error( $mode_terms ) ) ? $mode_terms[0]->name : 'Đại học từ xa';

		$schemas[] = [
			'@context'                     => 'https://schema.org',
			'@type'                        => 'Course',
			'name'                         => get_the_title( $program_id ),
			'description'                  => get_the_excerpt( $program_id ) ?: get_the_title( $program_id ),
			'provider'                     => [
				'@type' => 'CollegeOrUniversity',
				'name'  => $school_id ? get_the_title( $school_id ) : get_bloginfo( 'name' ),
				'url'   => $school_id ? get_permalink( $school_id ) : home_url( '/' ),
			],
			'educationalCredentialAwarded' => 'Bằng Cử nhân / Kỹ sư',
			'hasCourseInstance'            => [
				'@type'          => 'CourseInstance',
				'courseMode'     => $mode_name,
				'courseWorkload' => $duration,
			],
			'offers'                       => [
				'@type'         => 'Offer',
				'price'         => is_numeric( $tuition_raw ) ? $tuition_raw : '0',
				'priceCurrency' => 'VND',
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink( $program_id ),
			],
		];
	}

	// 3. Schema BreadcrumbList
	$crumb_list = [
		[
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => 'Trang chủ',
			'item'     => home_url( '/' ),
		]
	];
	if ( is_singular() ) {
		$crumb_list[] = [
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		];
	} elseif ( is_tax() || is_category() ) {
		$term = get_queried_object();
		if ( $term ) {
			$crumb_list[] = [
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => $term->name,
				'item'     => get_term_link( $term ),
			];
		}
	}
	if ( count( $crumb_list ) > 1 ) {
		$schemas[] = [
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $crumb_list,
		];
	}

	if ( ! empty( $schemas ) ) {
		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schemas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}
}


