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
// 0. Rank Math Admin Synchronization Helpers
// ----------------------------------------------------

/**
 * Check if the given post has a custom SEO Title set in Rank Math metabox.
 */
function ltdh_seo_has_custom_title( $post_id ): bool {
	if ( ! $post_id ) {
		return false;
	}
	$custom = get_post_meta( $post_id, 'rank_math_title', true );
	return ! empty( trim( (string) $custom ) );
}

/**
 * Check if the given post has a custom SEO Description set in Rank Math metabox.
 */
function ltdh_seo_has_custom_description( $post_id ): bool {
	if ( ! $post_id ) {
		return false;
	}
	$custom = get_post_meta( $post_id, 'rank_math_description', true );
	return ! empty( trim( (string) $custom ) );
}

/**
 * Check if the given term has a custom SEO Title set in Rank Math.
 */
function ltdh_seo_has_custom_term_title( $term_id ): bool {
	if ( ! $term_id ) {
		return false;
	}
	$custom = get_term_meta( $term_id, 'rank_math_title', true );
	return ! empty( trim( (string) $custom ) );
}

/**
 * Check if the given term has a custom SEO Description set in Rank Math.
 */
function ltdh_seo_has_custom_term_description( $term_id ): bool {
	if ( ! $term_id ) {
		return false;
	}
	$custom = get_term_meta( $term_id, 'rank_math_description', true );
	return ! empty( trim( (string) $custom ) );
}

/**
 * Check if a schema of a given @type was already added by the user in Rank Math Schema Builder.
 */
function ltdh_seo_has_user_schema( array $data, string $type ): bool {
	foreach ( $data as $key => $schema ) {
		if ( ! is_array( $schema ) ) {
			continue;
		}
		if ( is_string( $key ) && str_starts_with( $key, 'ltdh_' ) ) {
			continue;
		}
		$schema_type = $schema['@type'] ?? '';
		if ( is_array( $schema_type ) && in_array( $type, $schema_type, true ) ) {
			return true;
		}
		if ( $schema_type === $type ) {
			return true;
		}
	}
	return false;
}

/**
 * Build enhanced, high-authority FAQPage schema elements adhering strictly to Google Search Central guidelines.
 *
 * @param array       $raw_faqs     Array of array['question' => ..., 'answer' => ...]
 * @param string      $context_url  Canonical or permalink URL of the page hosting these FAQs.
 * @param int|null    $post_id      Optional post ID to derive publication / modification timestamps.
 * @return array                    FAQPage schema structure or empty array if no valid questions found.
 */
function ltdh_build_enhanced_faq_schema( array $raw_faqs, string $context_url, ?int $post_id = null ): array {
	if ( empty( $raw_faqs ) ) {
		return [];
	}

	$site_name = get_bloginfo( 'name' ) ?: 'Liên Thông Đại Học';
	$site_url  = home_url( '/' );

	// Determine publication & modification dates (ISO 8601)
	$date_published = '';
	$date_modified  = '';
	if ( $post_id && function_exists( 'get_the_date' ) ) {
		$date_published = get_the_date( 'c', $post_id );
		$date_modified  = get_the_modified_date( 'c', $post_id );
	}
	if ( empty( $date_published ) ) {
		$date_published = gmdate( 'c' );
	}
	if ( empty( $date_modified ) ) {
		$date_modified = $date_published;
	}

	$faq_elements = [];
	$idx          = 0;

	foreach ( $raw_faqs as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$q = trim( (string) ( $item['question'] ?? '' ) );
		$a = trim( (string) ( $item['answer'] ?? '' ) );

		if ( empty( $q ) || empty( $a ) ) {
			continue;
		}

		$idx++;
		$clean_q = wp_strip_all_tags( html_entity_decode( $q, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		$clean_a = wp_strip_all_tags( $a );
		$clean_a = html_entity_decode( $clean_a, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		// Clean up extra whitespace/newlines
		$clean_q = preg_replace( '/\s+/u', ' ', $clean_q );
		$clean_a = preg_replace( '/\n{3,}/', "\n\n", $clean_a );

		$q_anchor = '#faq-' . $idx;
		$a_anchor = '#faq-' . $idx . '-answer';

		if ( ! empty( $context_url ) ) {
			$base_url = explode( '#', $context_url )[0];
			if ( str_contains( $base_url, '?' ) ) {
				$parts    = explode( '?', $base_url, 2 );
				$item_url = trailingslashit( $parts[0] ) . '?' . $parts[1] . $q_anchor;
				$ans_url  = trailingslashit( $parts[0] ) . '?' . $parts[1] . $a_anchor;
			} else {
				$item_url = trailingslashit( $base_url ) . $q_anchor;
				$ans_url  = trailingslashit( $base_url ) . $a_anchor;
			}
		} else {
			$item_url = $q_anchor;
			$ans_url  = $a_anchor;
		}

		$question_schema = [
			'@type'          => 'Question',
			'name'           => $clean_q,
			'text'           => $clean_q,
			'url'            => $item_url,
			'datePublished'  => $date_published,
			'dateModified'   => $date_modified,
			'author'         => [
				'@type' => 'Organization',
				'name'  => $site_name,
				'url'   => $site_url,
			],
			'acceptedAnswer' => [
				'@type'         => 'Answer',
				'text'          => $clean_a,
				'url'           => $ans_url,
				'datePublished' => $date_published,
				'dateModified'  => $date_modified,
				'author'        => [
					'@type' => 'Organization',
					'name'  => 'Ban Tuyển Sinh ' . $site_name,
					'url'   => $site_url,
				],
			],
		];

		$faq_elements[] = $question_schema;
	}

	if ( empty( $faq_elements ) ) {
		return [];
	}

	$schema_id = '#faq';
	if ( ! empty( $context_url ) ) {
		$base_url  = explode( '#', $context_url )[0];
		$schema_id = trailingslashit( explode( '?', $base_url, 2 )[0] ) . '#faq';
	}

	return [
		'@type'      => 'FAQPage',
		'@id'        => $schema_id,
		'mainEntity' => $faq_elements,
	];
}

// ----------------------------------------------------
// 1. Dynamic Title for Programs (Fallback only when Rank Math custom title is empty)
// ----------------------------------------------------
function ltdh_seo_dynamic_title( $title ) {
	$year         = (int) date( 'Y' );
	$request_uri  = $_SERVER['REQUEST_URI'] ?? '';
	$request_path = parse_url( $request_uri, PHP_URL_PATH );

	// 1. Homepage: Check if user customized in Rank Math metabox or Titles & Meta settings
	if ( is_front_page() || ( is_home() && is_front_page() ) ) {
		$front_id = (int) get_option( 'page_on_front' );
		if ( $front_id && ltdh_seo_has_custom_title( $front_id ) ) {
			return $title; // Respect custom title from Rank Math metabox on front page!
		}
		$rm_titles = get_option( 'rank-math-options-titles', [] );
		$custom_rm_home_title = trim( (string) ( $rm_titles['homepage_title'] ?? '' ) );
		// Rank Math default placeholders: '%sitename% %page% %sep% %sitedesc%', '%sitename% %sep% %sitedesc%', '%title% %sep% %sitename%'
		if ( ! empty( $custom_rm_home_title ) && ! in_array( $custom_rm_home_title, [ '%sitename% %page% %sep% %sitedesc%', '%sitename% %sep% %sitedesc%', '%title% %sep% %sitename%' ], true ) ) {
			return $title; // Respect custom non-default setting in Rank Math -> Titles & Meta -> Homepage!
		}
		return sprintf( 'Liên Thông Đại Học %d: Đào Tạo Từ Xa & Vừa Học Vừa Làm', $year );
	}

	// 2. Singular Pages: Check if user customized in Rank Math metabox
	if ( is_singular() ) {
		$post_id = get_the_ID();
		if ( $post_id && ltdh_seo_has_custom_title( $post_id ) ) {
			return $title; // Respect user custom title in Rank Math metabox!
		}
	}

	if ( is_page( 'cau-hoi-thuong-gap' ) || ( ! empty( $request_path ) && preg_match( '#^/cau-hoi-thuong-gap/?#i', $request_path ) ) ) {
		return sprintf( 'Hỏi Đáp Tuyển Sinh Liên Thông Đại Học %d | Câu Hỏi Thường Gặp', $year );
	}

	if ( is_page( 'kiem-tra-dieu-kien' ) || ( ! empty( $request_path ) && preg_match( '#^/kiem-tra-dieu-kien/?#i', $request_path ) ) ) {
		return sprintf( 'Kiểm Tra Điều Kiện Liên Thông Đại Học %d | Xét Tuyển Nhanh', $year );
	}

	if ( is_singular( LTDH_CPT_PROGRAM ) ) {
		$program_id = $post_id ?: get_the_ID();
		$school_id  = intval( get_field( LTDH_META_SCHOOL_REL, $program_id ) ?: 0 );
		$major_id   = intval( get_field( LTDH_META_MAJOR_REL, $program_id ) ?: 0 );
		$school     = $school_id ? get_the_title( $school_id ) : '';
		$major      = $major_id ? get_the_title( $major_id ) : '';

		$training_type = '';
		$terms = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$training_type = $terms[0]->name;
		}
		$clean_type = preg_replace( '/^hệ\s+/iu', '', trim( (string) $training_type ) );

		$clean_major = function_exists( 'ltdh_get_program_discipline_name' )
			? ltdh_get_program_discipline_name( $program_id )
			: ( $major ? preg_replace( '/^(?:ngành|cử nhân|kỹ sư)\s+/iu', '', trim( $major ) ) : '' );
		if ( ! empty( $clean_major ) && $clean_type ) {
			$clean_major = preg_replace( '/\s*[-–—(]\s*' . preg_quote( $clean_type, '/' ) . '\s*[)]?/iu', '', $clean_major );
			$clean_major = trim( $clean_major, " -–—\t\n\r\0\x0B" );
		}
		if ( empty( $clean_major ) ) {
			$raw_title   = get_the_title( $program_id );
			$clean_major = preg_replace( '/^(?:tuyển sinh\s+)?liên thông\s+(?:đại học\s+)?(?:ngành\s+)?/iu', '', $raw_title );
			if ( $clean_type ) {
				$clean_major = preg_replace( '/\s*[-–—(]\s*' . preg_quote( $clean_type, '/' ) . '\s*[)]?/iu', '', $clean_major );
			}
			$clean_major = trim( $clean_major, " -–—\t\n\r\0\x0B" );
		}

		$type_part    = $clean_type ? " ($clean_type)" : '';
		$clean_school = $school ? preg_replace( '/^trường\s+đại\s+học\s+/iu', 'ĐH ', $school ) : '';
		$school_code  = $school_id ? get_post_meta( $school_id, 'school_code', true ) : '';
		$school_part  = $school ? " – $school" : '';

		// Standardized formula with Year (aiming under 65 chars for optimal SERP visibility)
		$full_candidate = sprintf( 'Liên thông %s%s%s | Tuyển sinh %d', $clean_major, $type_part, $school_part, $year );
		if ( mb_strlen( $full_candidate ) <= 65 ) {
			return $full_candidate;
		}

		$short_school_candidate = sprintf( 'Liên thông %s%s - %s | %d', $clean_major, $type_part, $clean_school, $year );
		if ( mb_strlen( $short_school_candidate ) <= 65 ) {
			return $short_school_candidate;
		}

		if ( ! empty( $school_code ) ) {
			$code_candidate = sprintf( 'Liên thông %s%s - %s | Tuyển sinh %d', $clean_major, $type_part, $school_code, $year );
			if ( mb_strlen( $code_candidate ) <= 65 ) {
				return $code_candidate;
			}
			return sprintf( 'Liên thông %s%s - %s | %d', $clean_major, $type_part, $school_code, $year );
		}

		return $short_school_candidate;
	}

	if ( is_singular( 'school' ) ) {
		$clean_title = preg_replace( '/(?:trường\s+)?đại\s+học\s+/iu', 'ĐH ', $title );
		if ( mb_strlen( $clean_title ) <= 65 ) {
			return $clean_title;
		}
	}

	if ( is_singular( 'major' ) ) {
		$clean_title = str_replace( 'Lộ Trình Học Liên Thông Đại Học', 'Lộ Trình Liên Thông ĐH', $title );
		if ( mb_strlen( $clean_title ) <= 65 ) {
			return $clean_title;
		}
	}

	return $title;
}
add_filter( 'rank_math/frontend/title', 'ltdh_seo_dynamic_title' );
add_filter( 'rank_math/opengraph/facebook/title', 'ltdh_seo_dynamic_title' );
add_filter( 'rank_math/opengraph/twitter/title', 'ltdh_seo_dynamic_title' );
add_filter( 'pre_get_document_title', 'ltdh_seo_dynamic_title', 15 );

/**
 * Dynamic Document Title for Archive & Virtual Route Listings
 * (Respects user custom title on terms if entered in Rank Math taxonomy metabox)
 */
function ltdh_seo_archive_document_title( $title ) {
	$request_uri  = $_SERVER['REQUEST_URI'] ?? '';
	$request_path = parse_url( $request_uri, PHP_URL_PATH );
	$site_name    = get_bloginfo( 'name' );
	$year         = (int) date( 'Y' );

	// Check if queried term has custom title set in Rank Math
	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->term_id ) && ltdh_seo_has_custom_term_title( $term->term_id ) ) {
			return $title; // Respect user custom term title in Rank Math!
		}
	}

	if (
		is_post_type_archive( 'program' ) ||
		is_tax( 'training_type' ) ||
		is_tax( 'major_cat' ) ||
		is_tax( 'region' ) ||
		( ! empty( $request_path ) && preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao|chuong-trinh)/?#i', $request_path ) )
	) {
		if ( is_tax( 'training_type' ) ) {
			$term = get_queried_object();
			if ( $term && isset( $term->name ) ) {
				$clean_term_name = preg_replace( '/^hệ\s+/iu', '', $term->name );
				return sprintf( 'Liên Thông %s %d | Danh Sách Trường & Học Phí', $clean_term_name, $year );
			}
		} elseif ( ! empty( $request_path ) && preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $m ) && 'page' !== $m[1] ) {
			$slug = sanitize_text_field( $m[1] );
			$term = get_term_by( 'slug', $slug, 'training_type' );
			if ( $term && ! is_wp_error( $term ) && isset( $term->name ) ) {
				if ( ltdh_seo_has_custom_term_title( $term->term_id ) ) {
					return $title; // Respect user custom term title!
				}
				$clean_term_name = preg_replace( '/^hệ\s+/iu', '', $term->name );
				return sprintf( 'Liên Thông %s %d | Danh Sách Trường & Học Phí', $clean_term_name, $year );
			}
		} elseif ( is_tax( 'major_cat' ) ) {
			$term = get_queried_object();
			if ( $term && isset( $term->name ) ) {
				$clean_cat = preg_replace( '/^nhóm\s+ngành\s+/iu', '', $term->name );
				$cat_title = sprintf( 'Liên Thông Ngành %s %d | Chỉ Tiêu & Học Phí', $clean_cat, $year );
				if ( mb_strlen( $cat_title ) <= 65 ) {
					return $cat_title;
				}
				return sprintf( 'Liên Thông %s %d | Danh Sách Trường', $clean_cat, $year );
			}
		} elseif ( is_tax( 'region' ) ) {
			$term = get_queried_object();
			if ( $term && isset( $term->name ) ) {
				return sprintf( 'Tuyển Sinh Liên Thông Đại Học %s %d | Các Trường Đào Tạo', $term->name, $year );
			}
		}

		return sprintf( 'Các Hình Thức Tuyển Sinh Liên Thông Đại Học %d: Từ Xa, VHVL, CQ', $year );
	}

	if ( is_post_type_archive( 'major' ) || ( ! empty( $request_path ) && preg_match( '#^/(?:nhom-nganh|nganh-hoc)/?#i', $request_path ) ) ) {
		return sprintf( 'Các Ngành Tuyển Sinh Liên Thông Đại Học %d | Danh Sách Mới Nhất', $year );
	}

	if ( is_post_type_archive( 'school' ) || ( ! empty( $request_path ) && preg_match( '#^/(?:truong-da-hoc|truong-doi-tac)/?#i', $request_path ) ) ) {
		return sprintf( 'Các Trường Tuyển Sinh Liên Thông Đại Học %d | Danh Sách Mới', $year );
	}

	if ( is_home() || ( ! empty( $request_path ) && preg_match( '#^/tin-tuc/?#i', $request_path ) ) ) {
		$posts_page_id = (int) get_option( 'page_for_posts' );
		if ( $posts_page_id && ltdh_seo_has_custom_title( $posts_page_id ) ) {
			return $title;
		}
		return sprintf( 'Tin Tuyển Sinh Liên Thông Đại Học %d | Thông Báo Mới Nhất', $year );
	}

	return $title;
}
add_filter( 'rank_math/frontend/title', 'ltdh_seo_archive_document_title', 20 );
add_filter( 'rank_math/opengraph/facebook/title', 'ltdh_seo_archive_document_title', 20 );
add_filter( 'rank_math/opengraph/twitter/title', 'ltdh_seo_archive_document_title', 20 );
add_filter( 'pre_get_document_title', 'ltdh_seo_archive_document_title', 20 );
add_filter( 'wp_title', 'ltdh_seo_archive_document_title', 20 );

// ----------------------------------------------------
// 2. Dynamic Meta Description (Fallback only when Rank Math custom description is empty)
// ----------------------------------------------------
function ltdh_seo_dynamic_description( $desc ) {
	$year         = (int) date( 'Y' );
	$request_uri  = $_SERVER['REQUEST_URI'] ?? '';
	$request_path = parse_url( $request_uri, PHP_URL_PATH );

	// 1. Homepage: Check if user customized in Rank Math metabox or Titles & Meta settings
	if ( is_front_page() || ( is_home() && is_front_page() ) ) {
		$front_id = (int) get_option( 'page_on_front' );
		if ( $front_id && ltdh_seo_has_custom_description( $front_id ) ) {
			return $desc; // Respect custom description from Rank Math metabox on front page!
		}
		$rm_titles = get_option( 'rank-math-options-titles', [] );
		if ( ! empty( $rm_titles['homepage_description'] ) ) {
			return $desc; // Respect custom setting in Rank Math -> Titles & Meta -> Homepage!
		}
		return sprintf(
			'Cổng thông tin tuyển sinh Liên thông Đại học %d. Đào tạo từ xa online, vừa học vừa làm, chính quy cho người đi làm. Nhận bằng Cử nhân, Kỹ sư chuẩn Bộ GD&ĐT.',
			$year
		);
	}

	// 2. Singular Posts & Pages: Check if user customized in Rank Math metabox
	if ( is_singular() ) {
		$post_id = get_the_ID();
		if ( $post_id && ltdh_seo_has_custom_description( $post_id ) ) {
			return $desc; // Respect user custom description in Rank Math metabox!
		}
	}

	if ( is_home() || ( ! empty( $request_path ) && preg_match( '#^/tin-tuc/?#i', $request_path ) ) ) {
		$posts_page_id = (int) get_option( 'page_for_posts' );
		if ( $posts_page_id && ltdh_seo_has_custom_description( $posts_page_id ) ) {
			return $desc;
		}
		return sprintf(
			'Tin tức tuyển sinh liên thông đại học năm %d: thông báo xét tuyển, lịch khai giảng, học phí và hướng dẫn nộp hồ sơ từ các trường đại học hàng đầu.',
			$year
		);
	}

	if ( is_page( 'cau-hoi-thuong-gap' ) || ( ! empty( $request_path ) && preg_match( '#^/cau-hoi-thuong-gap/?#i', $request_path ) ) ) {
		return sprintf(
			'Giải đáp chi tiết các thắc mắc về tuyển sinh liên thông đại học năm %d: điều kiện xét tuyển, thời gian đào tạo từ xa, mức học phí và giá trị bằng cấp tốt nghiệp.',
			$year
		);
	}

	if ( is_page( 'kiem-tra-dieu-kien' ) || ( ! empty( $request_path ) && preg_match( '#^/kiem-tra-dieu-kien/?#i', $request_path ) ) ) {
		return sprintf(
			'Công cụ tra cứu và đối chiếu điều kiện tuyển sinh liên thông đại học năm %d. Hướng dẫn lộ trình học cho người tốt nghiệp Trung cấp, Cao đẳng nhận bằng nhanh.',
			$year
		);
	}

	if ( is_singular( 'school' ) ) {
		$school_id   = get_the_ID();
		$school_name = get_the_title( $school_id );
		$school_code = get_post_meta( $school_id, 'school_code', true );
		$code_str    = $school_code ? " ($school_code)" : '';
		return sprintf(
			'Thông tin tuyển sinh liên thông đại học tại %s%s năm %d: chỉ tiêu, ngành đào tạo, hình thức đào tạo từ xa, vừa học vừa làm và chính sách học phí.',
			$school_name,
			$code_str,
			$year
		);
	}

	if ( is_singular( 'major' ) ) {
		$major_id   = get_the_ID();
		$major_name = get_the_title( $major_id );
		return sprintf(
			'Thông tin tuyển sinh liên thông đại học ngành %s năm %d: lộ trình đào tạo, điều kiện xét tuyển văn bằng, danh sách trường đào tạo và cơ hội nghề nghiệp.',
			$major_name,
			$year
		);
	}

	if ( is_singular( LTDH_CPT_PROGRAM ) ) {
		$program_id = $post_id ?: get_the_ID();
		$school_id  = intval( get_field( LTDH_META_SCHOOL_REL, $program_id ) ?: 0 );
		$major_id   = intval( get_field( LTDH_META_MAJOR_REL, $program_id ) ?: 0 );
		$school     = $school_id ? get_the_title( $school_id ) : '';
		$major      = $major_id ? get_the_title( $major_id ) : '';
		$tuition    = ltdh_get_program_tuition_display( $program_id );
		$duration   = function_exists( 'ltdh_get_program_duration' ) ? ltdh_get_program_duration( $program_id ) : ( get_field( LTDH_META_DURATION, $program_id ) ?: '1.5 - 2 năm' );

		$training_type = '';
		$terms = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$training_type = $terms[0]->name;
		}
		$clean_type = preg_replace( '/^hệ\s+/iu', '', trim( (string) $training_type ) );

		$clean_major = function_exists( 'ltdh_get_program_discipline_name' )
			? ltdh_get_program_discipline_name( $program_id )
			: ( $major ? preg_replace( '/^(?:ngành|cử nhân|kỹ sư)\s+/iu', '', trim( $major ) ) : '' );
		if ( ! empty( $clean_major ) && $clean_type ) {
			$clean_major = preg_replace( '/\s*[-–—(]\s*' . preg_quote( $clean_type, '/' ) . '\s*[)]?/iu', '', $clean_major );
			$clean_major = trim( $clean_major, " -–—\t\n\r\0\x0B" );
		}
		if ( empty( $clean_major ) ) {
			$raw_title   = get_the_title( $program_id );
			$clean_major = preg_replace( '/^(?:tuyển sinh\s+)?liên thông\s+(?:đại học\s+)?(?:ngành\s+)?/iu', '', $raw_title );
			if ( $clean_type ) {
				$clean_major = preg_replace( '/\s*[-–—(]\s*' . preg_quote( $clean_type, '/' ) . '\s*[)]?/iu', '', $clean_major );
			}
			$clean_major = trim( $clean_major, " -–—\t\n\r\0\x0B" );
		}

		$school_code       = $school_id ? get_post_meta( $school_id, 'school_code', true ) : '';
		$clean_school_desc = $school_code ?: ( $school ? preg_replace( '/^trường\s+đại\s+học\s+/iu', 'ĐH ', $school ) : 'đối tác' );

		$candidate = sprintf(
			'Tuyển sinh Liên thông %s (%s) tại %s. Thời gian %s, học phí %s. Hướng dẫn hồ sơ xét tuyển và miễn giảm tín chỉ.',
			$clean_major,
			$clean_type ?: 'Liên thông',
			$clean_school_desc,
			$duration,
			$tuition
		);

		if ( mb_strlen( $candidate ) > 165 ) {
			$candidate = sprintf(
				'Tuyển sinh Liên thông %s (%s) tại %s. Thời gian %s, học phí %s. Xét tuyển nhanh & miễn giảm tín chỉ.',
				$clean_major,
				$clean_type ?: 'Liên thông',
				$clean_school_desc,
				$duration,
				$tuition
			);
		}

		if ( mb_strlen( $candidate ) > 165 ) {
			$candidate = sprintf(
				'Tuyển sinh Liên thông %s (%s) tại %s. Thời gian %s, học phí %s. Đăng ký xét tuyển ngay.',
				$clean_major,
				$clean_type ?: 'Liên thông',
				$clean_school_desc,
				$duration,
				$tuition
			);
		}

		return $candidate;
	}

	// 3. Taxonomies: Check if user customized in Rank Math term metabox
	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->term_id ) && ltdh_seo_has_custom_term_description( $term->term_id ) ) {
			return $desc; // Respect user custom term description!
		}
	}

	// 4. Archive Major (/nhom-nganh/, /nganh-hoc/)
	if ( is_post_type_archive( 'major' ) || ( ! empty( $request_path ) && preg_match( '#^/(?:nhom-nganh|nganh-hoc)/?#i', $request_path ) ) ) {
		return sprintf(
			'Danh sách ngành tuyển sinh liên thông đại học năm %d: CNTT, Quản trị kinh doanh, Kế toán, Ngôn ngữ Anh... Xem chỉ tiêu, điều kiện và học phí chi tiết.',
			$year
		);
	}

	// 5. Archive School (/truong-da-hoc/, /truong-doi-tac/)
	if ( is_post_type_archive( 'school' ) || ( ! empty( $request_path ) && preg_match( '#^/(?:truong-da-hoc|truong-doi-tac)/?#i', $request_path ) ) ) {
		return sprintf(
			'Tổng hợp danh sách các trường đại học công lập và trường đào tạo liên thông chính quy, vừa học vừa làm, đào tạo từ xa uy tín hàng đầu toàn quốc năm %d.',
			$year
		);
	}

	// 6. Training Type Archive (/hinh-thuc-dao-tao/) & Taxonomies
	if (
		is_post_type_archive( 'program' ) ||
		is_tax( 'training_type' ) ||
		is_tax( 'major_cat' ) ||
		is_tax( 'region' ) ||
		( ! empty( $request_path ) && preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao|chuong-trinh)/?#i', $request_path ) )
	) {
		if ( is_tax( 'training_type' ) ) {
			$term = get_queried_object();
			if ( $term && isset( $term->name ) ) {
				$clean_term_name = preg_replace( '/^hệ\s+/iu', '', $term->name );
				return sprintf(
					'Danh sách tuyển sinh Liên thông Đại học %s %d. Học phí chi tiết, lộ trình công nhận tín chỉ và nộp hồ sơ xét tuyển online.',
					$clean_term_name,
					$year
				);
			}
		} elseif ( ! empty( $request_path ) && preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $m ) && 'page' !== $m[1] ) {
			$slug = sanitize_text_field( $m[1] );
			$term = get_term_by( 'slug', $slug, 'training_type' );
			if ( $term && ! is_wp_error( $term ) && isset( $term->name ) ) {
				if ( ltdh_seo_has_custom_term_description( $term->term_id ) ) {
					return $desc; // Respect user custom term description!
				}
				$clean_term_name = preg_replace( '/^hệ\s+/iu', '', $term->name );
				return sprintf(
					'Danh sách tuyển sinh Liên thông Đại học %s %d. Học phí chi tiết, lộ trình công nhận tín chỉ và nộp hồ sơ xét tuyển online.',
					$clean_term_name,
					$year
				);
			}
		} elseif ( is_tax( 'major_cat' ) || ( ! empty( $request_path ) && preg_match( '#^/nhom-nganh/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $m_cat ) && 'page' !== $m_cat[1] ) ) {
			$term = get_queried_object();
			if ( ( ! $term || ! isset( $term->name ) ) && ! empty( $m_cat[1] ) ) {
				$term = get_term_by( 'slug', sanitize_title( $m_cat[1] ), 'major_cat' );
			}
			if ( $term && isset( $term->name ) ) {
				if ( isset( $term->term_id ) && ltdh_seo_has_custom_term_description( $term->term_id ) ) {
					return $desc; // Respect user custom term description!
				}
				return sprintf(
					'Danh sách các ngành đào tạo thuộc nhóm %s tuyển sinh liên thông đại học năm %d. Thông tin chỉ tiêu, trường đào tạo và thời gian nhận hồ sơ.',
					$term->name,
					$year
				);
			}
		} elseif ( is_tax( 'region' ) ) {
			$term = get_queried_object();
			if ( $term && isset( $term->name ) ) {
				return sprintf(
					'Tổng hợp các trường và chương trình tuyển sinh liên thông đại học tại khu vực %s năm %d. Danh sách địa điểm học, học phí và lịch xét tuyển.',
					$term->name,
					$year
				);
			}
		}

		return sprintf(
			'So sánh học phí, thời gian và văn bằng các hình thức liên thông đại học %d: Đào tạo từ xa online, Vừa học vừa làm, Chính quy chuẩn Bộ GD&ĐT.',
			$year
		);
	}

	return $desc;
}
add_filter( 'rank_math/frontend/description', 'ltdh_seo_dynamic_description' );
add_filter( 'rank_math/opengraph/facebook/description', 'ltdh_seo_dynamic_description' );
add_filter( 'rank_math/opengraph/twitter/description', 'ltdh_seo_dynamic_description' );

/**
 * Enforce flat Canonical URL for program and school posts (/%slug%/),
 * and point program archive canonical cleanly to /hinh-thuc-dao-tao/ to eliminate redirect loops.
 * (Respects user custom canonical URL set in Rank Math metabox)
 */
function ltdh_seo_enforce_canonical_url( $canonical ) {
	$post_id = get_the_ID();
	if ( $post_id ) {
		$custom_canonical = get_post_meta( $post_id, 'rank_math_canonical_url', true );
		if ( ! empty( $custom_canonical ) ) {
			return $custom_canonical; // Respect user override in Rank Math Advanced tab!
		}
	}

	if ( is_singular( 'program' ) || is_singular( 'school' ) ) {
		if ( $post_id ) {
			return home_url( '/' . get_post_field( 'post_name', $post_id ) . '/' );
		}
	}

	$request_uri  = $_SERVER['REQUEST_URI'] ?? '';
	$request_path = parse_url( $request_uri, PHP_URL_PATH );

	// Bảo vệ URL Canonical chuẩn cho các Taxonomy Landing Pages
	if ( is_tax( 'training_type' ) ) {
		$current_term = get_queried_object();
		if ( $current_term instanceof WP_Term ) {
			$term_link = get_term_link( $current_term );
			if ( ! is_wp_error( $term_link ) && ! empty( $term_link ) ) {
				return $term_link;
			}
		}
		return $canonical;
	}

	if ( is_post_type_archive( 'program' ) || preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao|chuong-trinh)/?#i', $request_path ) ) {
		// Clean parameter mapping
		$truong = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : '';
		$nganh  = isset( $_GET['nganh'] ) ? sanitize_text_field( wp_unslash( $_GET['nganh'] ) ) : '';
		$sort   = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : '';
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

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

	// Protect Comparison routes with clean self-referencing canonical
	if ( get_query_var( 'ltdh_compare' ) || ( ! empty( $request_path ) && preg_match( '#^/so-sanh#i', $request_path ) ) ) {
		return home_url( trailingslashit( $request_path ) );
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

/**
 * Recursively decode HTML entities and strip unwanted HTML tags in schema strings.
 */
function ltdh_seo_clean_schema_strings( $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $k => $v ) {
			$value[ $k ] = ltdh_seo_clean_schema_strings( $v );
		}
		return $value;
	}
	if ( is_string( $value ) ) {
		return html_entity_decode( wp_strip_all_tags( $value ), ENT_QUOTES, 'UTF-8' );
	}
	return $value;
}

function ltdh_seo_inject_custom_schema( $data, $json_ld ) {
	if ( ! is_array( $data ) ) {
		$data = [];
	}

	// 0. Remove inappropriate Article / BlogPosting and author Person from non-article pages
	$request_uri  = $_SERVER['REQUEST_URI'] ?? '';
	$request_path = parse_url( $request_uri, PHP_URL_PATH );
	$is_faq_page  = is_page( 'cau-hoi-thuong-gap' ) || ( ! empty( $request_path ) && preg_match( '#^/cau-hoi-thuong-gap/?#i', $request_path ) );
	$is_chk_page  = is_page( 'kiem-tra-dieu-kien' ) || ( ! empty( $request_path ) && preg_match( '#^/kiem-tra-dieu-kien/?#i', $request_path ) );
	$is_front     = is_front_page() || ( is_home() && is_front_page() );

	if ( $is_front || $is_faq_page || $is_chk_page ) {
		unset( $data['richSnippet'], $data['author'] );
		foreach ( $data as $k => $v ) {
			if ( is_string( $k ) && ( str_starts_with( $k, 'schema-' ) || $k === 'Article' || $k === 'BlogPosting' ) ) {
				unset( $data[ $k ] );
			}
		}
	}

	if ( is_singular( LTDH_CPT_PROGRAM ) ) {
		$program_id = get_the_ID();
		$school_id  = intval( get_field( LTDH_META_SCHOOL_REL, $program_id ) ?: 0 );
		$major_id   = intval( get_field( LTDH_META_MAJOR_REL, $program_id ) ?: 0 );
		$school     = $school_id ? get_the_title( $school_id ) : 'Đối tác';
		$major      = $major_id ? get_the_title( $major_id ) : '';

		$training_type = '';
		$terms         = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$training_type = $terms[0]->name;
		}
		$clean_type  = preg_replace( '/^hệ\s+/iu', '', trim( (string) $training_type ) );
		$clean_major = function_exists( 'ltdh_get_program_discipline_name' )
			? ltdh_get_program_discipline_name( $program_id )
			: ( $major ? preg_replace( '/^(?:ngành|cử nhân|kỹ sư)\s+/iu', '', trim( $major ) ) : '' );
		if ( ! empty( $clean_major ) && $clean_type ) {
			$clean_major = preg_replace( '/\s*[-–—(]\s*' . preg_quote( $clean_type, '/' ) . '\s*[)]?/iu', '', $clean_major );
			$clean_major = trim( $clean_major, " -–—\t\n\r\0\x0B" );
		}
		if ( empty( $clean_major ) ) {
			$raw_title   = get_the_title( $program_id );
			$clean_major = preg_replace( '/^(?:tuyển sinh\s+)?liên thông\s+(?:đại học\s+)?(?:ngành\s+)?/iu', '', $raw_title );
			if ( $clean_type ) {
				$clean_major = preg_replace( '/\s*[-–—(]\s*' . preg_quote( $clean_type, '/' ) . '\s*[)]?/iu', '', $clean_major );
			}
			$clean_major = trim( $clean_major, " -–—\t\n\r\0\x0B" );
		}
		$type_part   = $clean_type ? " ($clean_type)" : '';
		$school_part = ( $school && 'Đối tác' !== $school ) ? " – $school" : '';
		$schema_program_name = 'Liên thông ' . $clean_major . $type_part . $school_part;

		// Convert duration to ISO 8601 duration format for timeToComplete
		$duration_text = function_exists( 'ltdh_get_program_duration' )
			? ltdh_get_program_duration( $program_id )
			: ( get_field( LTDH_META_DURATION, $program_id ) ?: '1.5 - 2 năm' );

		$iso_duration = 'P1Y6M';
		if ( preg_match( '/2\.5\s*năm/iu', $duration_text ) || preg_match( '/30\s*tháng/iu', $duration_text ) ) {
			$iso_duration = 'P2Y6M';
		} elseif ( preg_match( '/2\s*năm/iu', $duration_text ) || preg_match( '/24\s*tháng/iu', $duration_text ) ) {
			$iso_duration = 'P2Y';
		} elseif ( preg_match( '/1\.5\s*năm/iu', $duration_text ) || preg_match( '/18\s*tháng/iu', $duration_text ) ) {
			$iso_duration = 'P1Y6M';
		} elseif ( preg_match( '/1\s*năm/iu', $duration_text ) || preg_match( '/12\s*tháng/iu', $duration_text ) ) {
			$iso_duration = 'P1Y';
		} elseif ( preg_match( '/3\s*năm/iu', $duration_text ) || preg_match( '/36\s*tháng/iu', $duration_text ) ) {
			$iso_duration = 'P3Y';
		}

		$tuition_num   = (float) get_field( 'tuition_amount', $program_id );
		$tuition_unit  = get_field( 'tuition_unit', $program_id );
		$total_credits = (int) get_field( 'tuition_total_credits', $program_id );

		$calculated_price = 0;
		$price_is_total   = false;
		if ( $tuition_num > 0 ) {
			if ( 'tin-chi' === $tuition_unit && $total_credits > 0 ) {
				$calculated_price = $tuition_num * $total_credits;
				$price_is_total   = true;
			} elseif ( 'toan-khoa' === $tuition_unit ) {
				$calculated_price = $tuition_num;
				$price_is_total   = true;
			}
		}

		$offers_data = [
			'@type'        => 'Offer',
			'category'     => 'Paid',
			'availability' => 'https://schema.org/InStock',
			'url'          => get_permalink( $program_id ),
		];
		if ( $price_is_total && $calculated_price > 0 ) {
			$offers_data['price']         = (string) $calculated_price;
			$offers_data['priceCurrency'] = 'VND';
		}

		$course_mode  = $clean_type ?: ( $training_type ?: 'Liên thông Đại học' );
		$provider_url = $school_id ? ( get_field( 'website', $school_id ) ?: get_permalink( $school_id ) ) : home_url( '/' );

		// Course Schema (with hasCourseInstance and offers for rich snippet eligibility)
		// Injected only if user has not defined a custom Course schema in Rank Math Schema Builder
		if ( ! ltdh_seo_has_user_schema( $data, 'Course' ) ) {
			$data['ltdh_course'] = [
				'@type'                        => 'Course',
				'name'                         => $schema_program_name,
				'description'                  => get_the_excerpt( $program_id ) ?: ( 'Chương trình tuyển sinh Liên thông Đại học ngành ' . $clean_major ),
				'provider'                     => [
					'@type' => 'CollegeOrUniversity',
					'name'  => $school,
					'url'   => $provider_url,
				],
				'educationalCredentialAwarded' => 'Bằng Cử nhân / Bằng Kỹ sư',
				'hasCourseInstance'            => [
					'@type'          => 'CourseInstance',
					'courseMode'     => $course_mode,
					'courseWorkload' => $duration_text,
				],
			];
			if ( $offers_data ) {
				$data['ltdh_course']['offers'] = $offers_data;
			}
		}

		// Schema EducationalOccupationalProgram chuẩn hóa cho Liên thông Đại học
		// Injected only if user has not defined one in Rank Math Schema Builder
		if ( ! ltdh_seo_has_user_schema( $data, 'EducationalOccupationalProgram' ) ) {
			$data['ltdh_program'] = [
				'@type'                        => 'EducationalOccupationalProgram',
				'name'                         => $schema_program_name,
				'description'                  => get_the_excerpt( $program_id ) ?: ( 'Chương trình tuyển sinh Liên thông Đại học ngành ' . $clean_major . ' chuẩn Bộ GD&ĐT.' ),
				'provider'                     => [
					'@type' => 'CollegeOrUniversity',
					'name'  => $school,
					'url'   => $provider_url,
				],
				'educationalProgramMode'       => $course_mode,
				'programPrerequisites'         => 'Tốt nghiệp trình độ Trung cấp hoặc Cao đẳng theo quy chế tuyển sinh hiện hành của Bộ GD&ĐT',
				'educationalCredentialAwarded' => 'Bằng Cử nhân / Bằng Kỹ sư',
				'timeToComplete'               => $iso_duration,
				'url'                          => get_permalink( $program_id ),
			];
			if ( $offers_data ) {
				$data['ltdh_program']['offers'] = $offers_data;
			}
		}

		// Synthesized or Custom FAQs (only if not already created in Rank Math Schema Builder)
		if ( ! ltdh_seo_has_user_schema( $data, 'FAQPage' ) ) {
			$faqs = function_exists( 'ltdh_get_program_faqs' )
				? ltdh_get_program_faqs( $program_id )
				: ( get_field( 'faq', $program_id ) ?: [] );

			if ( ! empty( $faqs ) && is_array( $faqs ) ) {
				$faq_schema = ltdh_build_enhanced_faq_schema( $faqs, (string) get_permalink( $program_id ), (int) $program_id );
				if ( ! empty( $faq_schema ) ) {
					$data['ltdh_faq'] = $faq_schema;
				}
			}
		}
	}

	if ( is_singular( LTDH_CPT_SCHOOL ) ) {
		$school_id = get_the_ID();
		if ( ! ltdh_seo_has_user_schema( $data, 'CollegeOrUniversity' ) && ! ltdh_seo_has_user_schema( $data, 'EducationalOrganization' ) ) {
			$school_logo = get_the_post_thumbnail_url( $school_id, 'full' );
			if ( empty( $school_logo ) && function_exists( 'get_field' ) ) {
				$custom_logo = get_field( 'logo', $school_id ) ?: get_field( 'school_logo', $school_id );
				if ( is_array( $custom_logo ) && ! empty( $custom_logo['url'] ) ) {
					$school_logo = $custom_logo['url'];
				} elseif ( is_string( $custom_logo ) && filter_var( $custom_logo, FILTER_VALIDATE_URL ) ) {
					$school_logo = $custom_logo;
				}
			}

			$official_website = get_field( 'website', $school_id );
			$school_permalink = get_permalink( $school_id );
			$school_title     = get_the_title( $school_id );
			$univ_id          = $official_website ? ( rtrim( $official_website, '/' ) . '/#organization' ) : ( $school_permalink . '#organization' );

			$data['ltdh_university'] = [
				'@type' => 'CollegeOrUniversity',
				'@id'   => $univ_id,
				'name'  => $school_title,
				'url'   => $official_website ?: $school_permalink,
			];

			$address_val = get_field( 'address', $school_id );
			if ( ! empty( $address_val ) ) {
				$data['ltdh_university']['address'] = [
					'@type'          => 'PostalAddress',
					'streetAddress'  => $address_val,
					'addressCountry' => 'VN',
				];
			}
			$hotline_val = get_field( 'hotline', $school_id );
			if ( ! empty( $hotline_val ) ) {
				$data['ltdh_university']['telephone'] = $hotline_val;
			}

			if ( ! empty( $school_logo ) ) {
				$data['ltdh_university']['logo']  = $school_logo;
				$data['ltdh_university']['image'] = $school_logo;
			}

			if ( ! empty( $official_website ) ) {
				$data['ltdh_university']['sameAs'] = [ $official_website ];
			}

			$data['ltdh_school_about'] = [
				'@type'      => 'AboutPage',
				'@id'        => $school_permalink . '#webpage',
				'url'        => $school_permalink,
				'name'       => $school_title . ' - Tuyển sinh Liên thông Đại học',
				'isPartOf'   => [
					'@id' => home_url( '/#website' ),
				],
				'about'      => [
					'@id' => $univ_id,
				],
				'inLanguage' => 'vi-VN',
			];
		}

		if ( ! ltdh_seo_has_user_schema( $data, 'FAQPage' ) && function_exists( 'ltdh_get_school_faqs' ) ) {
			$school_faqs = ltdh_get_school_faqs( $school_id );
			if ( ! empty( $school_faqs ) && is_array( $school_faqs ) ) {
				$faq_schema = ltdh_build_enhanced_faq_schema( $school_faqs, (string) get_permalink( $school_id ), (int) $school_id );
				if ( ! empty( $faq_schema ) ) {
					$data['ltdh_school_faq'] = $faq_schema;
				}
			}
		}
	}

	if ( defined( 'LTDH_CPT_MAJOR' ) && is_singular( LTDH_CPT_MAJOR ) ) {
		$major_id  = get_the_ID();
		$school_id = null;
		if ( isset( $_GET['truong'] ) && ! empty( $_GET['truong'] ) ) {
			$school_slug = sanitize_title( wp_unslash( $_GET['truong'] ) );
			$matched_school = get_page_by_path( $school_slug, OBJECT, defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' );
			if ( $matched_school ) {
				$school_id = $matched_school->ID;
			}
		}

		// Discipline / Program Schema for Major (if not added via Schema Builder)
		if ( ! ltdh_seo_has_user_schema( $data, 'EducationalOccupationalProgram' ) ) {
			$data['ltdh_major_program'] = [
				'@type'                        => 'EducationalOccupationalProgram',
				'name'                         => 'Chương trình đào tạo ngành ' . get_the_title( $major_id ),
				'description'                  => get_the_excerpt( $major_id ) ?: ( 'Thông tin tuyển sinh và lộ trình liên thông đại học ngành ' . get_the_title( $major_id ) ),
				'educationalCredentialAwarded' => 'Bằng Cử nhân / Kỹ sư',
				'educationalProgramMode'       => 'Liên thông Đại học',
				'programPrerequisites'         => 'Tốt nghiệp Trung cấp hoặc Cao đẳng theo quy chế của Bộ GD&ĐT',
				'url'                          => get_permalink( $major_id ),
			];
		}

		if ( ! ltdh_seo_has_user_schema( $data, 'FAQPage' ) && function_exists( 'ltdh_get_major_faqs' ) ) {
			$major_faqs = ltdh_get_major_faqs( $major_id, $school_id );
			if ( ! empty( $major_faqs ) && is_array( $major_faqs ) ) {
				$major_url = (string) get_permalink( $major_id );
				if ( ! empty( $school_slug ) ) {
					$major_url = add_query_arg( 'truong', $school_slug, $major_url );
				}
				$faq_schema = ltdh_build_enhanced_faq_schema( $major_faqs, $major_url, (int) $major_id );
				if ( ! empty( $faq_schema ) ) {
					$data['ltdh_major_faq'] = $faq_schema;
				}
			}
		}
	}

	if ( is_page( 'cau-hoi-thuong-gap' ) || is_page_template( 'page-faq.php' ) || is_page_template( 'page-cau-hoi-thuong-gap.php' ) ) {
		if ( ! ltdh_seo_has_user_schema( $data, 'FAQPage' ) ) {
			$faq_items = [];
			if ( function_exists( 'get_field' ) ) {
				$faq_items = get_field( 'faq_items', 'options' ) ?: get_field( 'faq_items', 'option' ) ?: [];
			}
			if ( empty( $faq_items ) || ! is_array( $faq_items ) ) {
				$faq_items = [
					[
						'question' => 'Bằng tốt nghiệp Đại học Liên thông / Từ xa có ghi hình thức đào tạo không?',
						'answer'   => 'Theo Thông tư 27/2019/TT-BGDĐT của Bộ Giáo dục và Đào tạo, từ ngày 01/03/2020 trên bằng tốt nghiệp Đại học không còn ghi hình thức đào tạo (như Từ xa hay Vừa học vừa làm). Tấm bằng do các trường đại học đối tác cấp có giá trị pháp lý tương đương bằng cử nhân/kỹ sư chính quy, được thi cao học, công chức và nâng bậc lương.',
					],
					[
						'question' => 'Thời gian đào tạo Liên thông Đại học mất bao lâu?',
						'answer'   => 'Thời gian đào tạo trung bình từ 1.5 đến 2 năm tùy theo khối ngành. Đặc biệt, học viên đã có bằng Trung cấp hoặc Cao đẳng đúng ngành sẽ được xét miễn giảm các học phần tương đương để rút ngắn thời gian hoàn thành chương trình.',
					],
					[
						'question' => 'Hình thức học trực tuyến (Online/Từ xa) diễn ra như thế nào?',
						'answer'   => 'Học viên học hoàn toàn qua hệ thống E-Learning trực tuyến của nhà trường. Bạn có thể tự chủ thời gian học mọi lúc mọi nơi qua bài giảng video, slide và tham gia trao đổi trực tiếp với giảng viên qua các buổi ôn tập online cuối tuần.',
					],
					[
						'question' => 'Hồ sơ xét tuyển Liên thông Đại học bao gồm những gì?',
						'answer'   => 'Hồ sơ xét tuyển cơ bản gồm: 01 Phiếu đăng ký theo mẫu của trường, 02 bản sao công chứng Bằng + Bảng điểm tốt nghiệp Cao đẳng/Trung cấp, 01 bản sao CCCD và 02 ảnh 3x4. Chuyên viên tuyển sinh sẽ hỗ trợ gửi mẫu và hướng dẫn chuẩn bị chi tiết.',
					],
					[
						'question' => 'Bằng Đại học Liên thông có đủ điều kiện đăng ký học Cao học / Thạc sĩ không?',
						'answer'   => 'Hoàn toàn đủ điều kiện. Tấm bằng đại học sau khi tốt nghiệp có đầy đủ tư cách pháp lý để bạn tiếp tục đăng ký thi tuyển Thạc sĩ, Cao học tại tất cả các trường Đại học trên toàn quốc hoặc du học nước ngoài.',
					],
				];
			}
			$faq_schema = ltdh_build_enhanced_faq_schema( $faq_items, (string) get_permalink(), (int) get_the_ID() );
			if ( ! empty( $faq_schema ) ) {
				$data['ltdh_faq_page'] = $faq_schema;
			}
		}
	}

	// 3.6 Training Type Archive FAQs (/hinh-thuc-dao-tao/)
	if ( is_tax( LTDH_TAX_TRAINING_TYPE ) || ( ! empty( $request_path ) && preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)/?#i', $request_path, $m ) && 'page' !== $m[1] ) ) {
		if ( ! ltdh_seo_has_user_schema( $data, 'FAQPage' ) && function_exists( 'ltdh_get_training_type_faqs' ) ) {
			$t_slug = is_tax( LTDH_TAX_TRAINING_TYPE ) ? ( get_queried_object()->slug ?? '' ) : ( $m[1] ?? '' );
			$t_term = $t_slug ? get_term_by( 'slug', $t_slug, LTDH_TAX_TRAINING_TYPE ) : null;
			if ( ! $t_term && ( 'tu-xa' === $t_slug || 'dao-tao-tu-xa' === $t_slug ) ) {
				$t_term = get_term_by( 'slug', 'dao-tao-tu-xa', LTDH_TAX_TRAINING_TYPE );
			}
			if ( $t_term ) {
				$t_faqs = ltdh_get_training_type_faqs( $t_term );
				if ( ! empty( $t_faqs ) && is_array( $t_faqs ) ) {
					$t_term_url = get_term_link( $t_term, LTDH_TAX_TRAINING_TYPE );
					$t_url      = ( ! is_wp_error( $t_term_url ) && ! empty( $t_term_url ) ) ? $t_term_url : home_url( $request_path );
					$faq_schema = ltdh_build_enhanced_faq_schema( $t_faqs, (string) $t_url, null );
					if ( ! empty( $faq_schema ) ) {
						$data['ltdh_training_type_faq'] = $faq_schema;
					}
				}

				// 3.7 Add ItemList schema of programs for this training type
				$progs = get_posts( [
					'post_type'      => LTDH_CPT_PROGRAM,
					'posts_per_page' => 12,
					'post_status'    => 'publish',
					'tax_query'      => [
						[
							'taxonomy' => LTDH_TAX_TRAINING_TYPE,
							'field'    => 'slug',
							'terms'    => ( 'tu-xa' === $t_term->slug || 'dao-tao-tu-xa' === $t_term->slug ) ? [ 'dao-tao-tu-xa', 'tu-xa' ] : $t_term->slug,
						],
					],
				] );
				if ( ! empty( $progs ) ) {
					$item_elements = [];
					$pos = 1;
					foreach ( $progs as $prog ) {
						$item_elements[] = [
							'@type'    => 'ListItem',
							'position' => $pos++,
							'name'     => get_the_title( $prog->ID ),
							'url'      => get_permalink( $prog->ID ),
						];
					}
					$t_link = get_term_link( $t_term );
					$clean_type_title = preg_replace( '/^(hệ|liên\s+thông)\s+/iu', '', $t_term->name );
					$data['ltdh_training_type_item_list'] = [
						'@type'           => 'ItemList',
						'@id'             => ( ! is_wp_error( $t_link ) ? $t_link : '' ) . '#itemlist',
						'name'            => sprintf( 'Chương trình tuyển sinh Liên thông %s', $clean_type_title ),
						'itemListElement' => $item_elements,
					];
				}
			}
		}
	}

	// 4. Enhance Organization / Publisher with Admin Settings (Rank Math + ACF Options)
	$rm_titles = get_option( 'rank-math-options-titles', [] );
	$kg_name   = ! empty( $rm_titles['knowledgegraph_name'] )
		? $rm_titles['knowledgegraph_name']
		: ( function_exists( 'get_field' ) ? ( get_field( 'global_company_name', 'option' ) ?: get_bloginfo( 'name' ) ) : get_bloginfo( 'name' ) );
	$kg_type   = ( isset( $rm_titles['knowledgegraph_type'] ) && 'person' === $rm_titles['knowledgegraph_type'] ) ? 'Person' : 'Organization';

	$social_links = [];
	$fb_url = function_exists( 'get_field' ) ? get_field( 'facebook_url', 'option' ) : '';
	if ( empty( $fb_url ) && ! empty( $rm_titles['social_url_facebook'] ) ) {
		$fb_url = $rm_titles['social_url_facebook'];
	}
	if ( $fb_url ) {
		$social_links[] = $fb_url;
	}

	$zalo_url = function_exists( 'get_field' ) ? ( get_field( 'global_zalo_url', 'option' ) ?: get_field( 'zalo_url', 'option' ) ) : '';
	if ( $zalo_url ) {
		$social_links[] = $zalo_url;
	}

	$yt_url = function_exists( 'get_field' ) ? get_field( 'youtube_url', 'option' ) : '';
	if ( empty( $yt_url ) && ! empty( $rm_titles['social_url_youtube'] ) ) {
		$yt_url = $rm_titles['social_url_youtube'];
	}
	if ( $yt_url ) {
		$social_links[] = $yt_url;
	}

	if ( empty( $social_links ) ) {
		$social_links = [
			'https://facebook.com/lienthongdaihoc.edu.vn',
			'https://zalo.me/0988991496',
			'https://youtube.com/@lienthongdaihoc',
		];
	}

	if ( isset( $data['publisher'] ) && is_array( $data['publisher'] ) ) {
		$data['publisher']['@type'] = $kg_type;
		$data['publisher']['name']  = $kg_name;
		$data['publisher']['url']   = home_url( '/' );
		if ( empty( $data['publisher']['sameAs'] ) ) {
			$data['publisher']['sameAs'] = $social_links;
		}
	}
	if ( isset( $data['Organization'] ) && is_array( $data['Organization'] ) ) {
		$data['Organization']['name'] = $kg_name;
		if ( empty( $data['Organization']['sameAs'] ) ) {
			$data['Organization']['sameAs'] = $social_links;
		}
	}

	// 5. Enforce Vietnamese language (vi-VN) across all nodes recursively
	$data = ltdh_seo_enforce_vietnamese_language( $data );

	// 6. Clean all HTML entities (&amp;, &#039;, etc.) across all strings
	return ltdh_seo_clean_schema_strings( $data );
}
add_filter( 'rank_math/json_ld', 'ltdh_seo_inject_custom_schema', 99, 2 );

/**
 * Filter Rank Math's default schema language so entities like Article / BlogPosting use vi-VN.
 */
add_filter( 'rank_math/schema/language', function() {
	return 'vi-VN';
}, 99 );

add_filter( 'rank_math/schema/validated_data', function( $data ) {
	return ltdh_seo_enforce_vietnamese_language( $data );
}, 99 );

/**
 * Recursively enforce Vietnamese language attribute across all schema nodes.
 */
function ltdh_seo_enforce_vietnamese_language( $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $k => $v ) {
			if ( 'inLanguage' === $k && is_string( $v ) ) {
				$value[ $k ] = 'vi-VN';
			} else {
				$value[ $k ] = ltdh_seo_enforce_vietnamese_language( $v );
			}
		}
		return $value;
	}
	return $value;
}

/**
 * Ensure HTML lang attribute is consistently set to Vietnamese (vi)
 */
add_filter( 'language_attributes', function( $output ) {
	if ( is_admin() ) {
		return $output;
	}
	return 'lang="vi"';
}, 20 );

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
	$home_label = ( ! empty( $crumbs[0][0] ) ) ? $crumbs[0][0] : 'Trang chủ';
	$home_url   = ( ! empty( $crumbs[0][1] ) ) ? $crumbs[0][1] : home_url( '/' );

	// ---- Comparison Route: Trang chủ > Liên thông Đại học > So sánh chương trình ----
	if ( get_query_var( 'ltdh_compare' ) ) {
		return [
			ltdh_seo_crumb( $home_label, $home_url ),
			ltdh_seo_crumb( 'Liên thông Đại học', home_url( '/hinh-thuc-dao-tao/' ) ),
			ltdh_seo_crumb( 'So sánh chương trình', home_url( '/so-sanh/' ) ),
		];
	}

	// ---- Blog archive: is_home() ----
	if ( is_home() ) {
		$tin_tuc_url = get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/tin-tuc/' );
		return [
			ltdh_seo_crumb( $home_label, $home_url ),
			ltdh_seo_crumb( 'Tin tức', $tin_tuc_url ),
		];
	}

	// ---- Category archive: is_category() ----
	if ( is_category() ) {
		$tin_tuc_url = get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/tin-tuc/' );
		$cat         = get_queried_object();
		$cat_url     = ( $cat && ! is_wp_error( get_category_link( $cat->term_id ) ) ) ? get_category_link( $cat->term_id ) : '';
		return [
			ltdh_seo_crumb( $home_label, $home_url ),
			ltdh_seo_crumb( 'Tin tức', $tin_tuc_url ),
			ltdh_seo_crumb( $cat ? $cat->name : 'Danh mục', $cat_url ),
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

		$new_crumbs[] = ltdh_seo_crumb( get_the_title( $post_id ), get_permalink( $post_id ) );

		return $new_crumbs;
	}

	// ---- Taxonomy training_type: Trang chủ > Liên thông Đại học > [Tên hình thức] ----
	if ( is_tax( LTDH_TAX_TRAINING_TYPE ) ) {
		$term     = get_queried_object();
		$term_url = ( $term && ! is_wp_error( get_term_link( $term ) ) ) ? get_term_link( $term ) : '';
		return [
			ltdh_seo_crumb( $home_label, $home_url ),
			ltdh_seo_crumb( 'Liên thông Đại học', home_url( '/hinh-thuc-dao-tao/' ) ),
			ltdh_seo_crumb( $term ? $term->name : 'Hình thức đào tạo', $term_url ),
		];
	}

	// ---- Single program: Trang chủ > [Nhóm ngành] > [Ngành học] > [Tên chuẩn chương trình] ----
	if ( is_singular( LTDH_CPT_PROGRAM ) ) {
		$program_id   = get_the_ID();
		$program_name = function_exists( 'ltdh_get_program_banner_title' )
			? ltdh_get_program_banner_title( $program_id )
			: get_the_title( $program_id );

		$crumbs = [
			ltdh_seo_crumb( $home_label, $home_url ),
		];

		$major_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $program_id ) : 0;
		if ( $major_id > 0 ) {
			$terms = get_the_terms( $major_id, 'major_cat' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$cat_term = $terms[0];
				$cat_link = get_term_link( $cat_term );
				if ( ! is_wp_error( $cat_link ) ) {
					$crumbs[] = ltdh_seo_crumb( $cat_term->name, $cat_link );
				}
			}
			$crumbs[] = ltdh_seo_crumb( get_the_title( $major_id ), get_permalink( $major_id ) );
		} else {
			$crumbs[] = ltdh_seo_crumb( 'Liên thông Đại học', home_url( '/hinh-thuc-dao-tao/' ) );
		}

		$crumbs[] = ltdh_seo_crumb( $program_name, get_permalink( $program_id ) );

		return $crumbs;
	}

	// ---- Taxonomy major_cat: Trang chủ > [Tên nhóm ngành] ----
	if ( is_tax( 'major_cat' ) ) {
		$term     = get_queried_object();
		$term_url = ( $term && ! is_wp_error( get_term_link( $term ) ) ) ? get_term_link( $term ) : '';
		return [
			ltdh_seo_crumb( $home_label, $home_url ),
			ltdh_seo_crumb( $term ? $term->name : 'Nhóm ngành', $term_url ),
		];
	}

	// ---- Single school: Trang chủ > Trường đối tác > [Tên trường] ----
	if ( is_singular( defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' ) ) {
		$school_id = get_the_ID();
		return [
			ltdh_seo_crumb( $home_label, $home_url ),
			ltdh_seo_crumb( 'Trường đối tác', home_url( '/truong-doi-tac/' ) ),
			ltdh_seo_crumb( get_the_title( $school_id ), get_permalink( $school_id ) ),
		];
	}

	// ---- Single guide: Trang chủ > Cẩm nang > [Tiêu đề cẩm nang] ----
	if ( is_singular( 'guide' ) ) {
		$guide_id = get_the_ID();
		return [
			ltdh_seo_crumb( $home_label, $home_url ),
			ltdh_seo_crumb( 'Cẩm nang', home_url( '/cam-nang/' ) ),
			ltdh_seo_crumb( get_the_title( $guide_id ), get_permalink( $guide_id ) ),
		];
	}

	// ---- Single major: Trang chủ > [Tên nhóm ngành] > [Tên ngành] ----
	if ( is_singular( 'major' ) ) {
		$post_id    = get_the_ID();
		$terms      = get_the_terms( $post_id, 'major_cat' );
		$major_term = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0] : null;

		$crumbs = [
			ltdh_seo_crumb( $home_label, $home_url ),
		];

		if ( $major_term ) {
			$crumbs[] = ltdh_seo_crumb( $major_term->name, get_term_link( $major_term ) );
		} else {
			$crumbs[] = ltdh_seo_crumb( 'Nhóm ngành', home_url( '/nhom-nganh/' ) );
		}

		$title = get_the_title( $post_id );
		$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );
		if ( ! empty( $school_param ) ) {
			$school_obj = is_numeric( $school_param ) ? get_post( intval( $school_param ) ) : get_page_by_path( sanitize_title( $school_param ), OBJECT, 'school' );
			if ( ! $school_obj ) {
				$by_code = get_posts( [ 'post_type' => 'school', 'meta_key' => 'school_code', 'meta_value' => sanitize_text_field( $school_param ), 'numberposts' => 1 ] );
				if ( ! empty( $by_code ) ) {
					$school_obj = $by_code[0];
				}
			}
			if ( $school_obj ) {
				$s_code = get_post_meta( $school_obj->ID, 'school_code', true );
				$s_name = $s_code ? $s_code : preg_replace( '/^trường\s+đại\s+học\s+/iu', 'ĐH ', get_the_title( $school_obj->ID ) );
				$title .= ' – ' . $s_name;
			}
		}

		$crumbs[] = ltdh_seo_crumb( $title, get_permalink( $post_id ) );
		return $crumbs;
	}

	// ---- Default: fix CPT archive labels & ensure valid URLs ----
	$last_index = count( $crumbs ) - 1;
	foreach ( $crumbs as $key => $crumb ) {
		$lower_label = mb_strtolower( $crumb[0], 'UTF-8' );
		if ( in_array( $lower_label, [ 'trường học', 'truong', 'truong-lien-ket', 'truong-doi-tac', 'trường đối tác', 'schools', 'school' ], true ) ) {
			$crumbs[ $key ][0] = 'Trường đối tác';
			if ( empty( $crumbs[ $key ][1] ) || $key !== $last_index ) {
				$crumbs[ $key ][1] = home_url( '/truong-doi-tac/' );
			}
		}
		if ( in_array( $lower_label, [ 'chương trình đào tạo', 'chuong-trinh', 'chương trình', 'programs', 'program' ], true ) ) {
			$crumbs[ $key ][0] = 'Liên thông Đại học';
			$crumbs[ $key ][1] = home_url( '/hinh-thuc-dao-tao/' );
		}
		if ( in_array( $lower_label, [ 'ngành học', 'nganh-hoc', 'nganh', 'majors', 'major', 'nhóm ngành' ], true ) ) {
			$crumbs[ $key ][0] = 'Nhóm ngành';
			if ( empty( $crumbs[ $key ][1] ) || $key !== $last_index ) {
				$crumbs[ $key ][1] = home_url( '/nhom-nganh/' );
			}
		}
		// If terminal item has empty URL and we are on a singular post, fill with permalink for Schema.org
		if ( $key === $last_index && empty( $crumbs[ $key ][1] ) && is_singular() ) {
			$crumbs[ $key ][1] = get_permalink();
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
		return 'So Sánh Các Chương Trình Liên Thông Đại Học | LTDH';
	}

	$names = array_map( fn( $item ) => $item['title'], $items );
	$candidate = sprintf( 'So sánh %s | LTDH', implode( ' vs ', $names ) );
	if ( mb_strlen( $candidate ) <= 65 ) {
		return $candidate;
	}

	// Shorten names for SERP
	$short_names = array_map( function( $item ) {
		$t = $item['title'] ?? '';
		$t = preg_replace( '/\s*–\s*.*$/iu', '', $t );
		return trim( $t );
	}, $items );

	$candidate = sprintf( 'So sánh %s | LTDH', implode( ' vs ', $short_names ) );
	if ( mb_strlen( $candidate ) <= 65 ) {
		return $candidate;
	}

	return mb_substr( $candidate, 0, 62 ) . '...';
}
add_filter( 'rank_math/frontend/title', 'ltdh_seo_compare_title', 25 );
add_filter( 'rank_math/opengraph/facebook/title', 'ltdh_seo_compare_title', 25 );
add_filter( 'rank_math/opengraph/twitter/title', 'ltdh_seo_compare_title', 25 );
add_filter( 'pre_get_document_title', 'ltdh_seo_compare_title', 25 );

function ltdh_seo_compare_description( $desc ) {
	$type = get_query_var( LTDH_QV_COMPARE );
	if ( ! $type ) {
		return $desc;
	}

	$items = function_exists( 'ltdh_compare_get_items' ) ? ltdh_compare_get_items() : [];
	if ( count( $items ) < 1 ) {
		return sprintf( 'Công cụ so sánh chi tiết các chương trình liên thông đại học chính quy, từ xa và vừa học vừa làm năm %d. Đối chiếu học phí, thời gian học và điều kiện xét tuyển.', (int) date( 'Y' ) );
	}

	$names = array_map( fn( $item ) => $item['title'], $items );
	$desc_candidate = sprintf(
		'So sánh chi tiết %s. Xem học phí, thời gian đào tạo, điều kiện tuyển sinh và cơ hội việc làm.',
		implode( ' và ', $names )
	);
	if ( mb_strlen( $desc_candidate ) <= 165 ) {
		return $desc_candidate;
	}

	$short_names = array_map( function( $item ) {
		$t = $item['title'] ?? '';
		$t = preg_replace( '/\s*–\s*.*$/iu', '', $t );
		return trim( $t );
	}, $items );

	$fallback = sprintf(
		'So sánh chi tiết %s. Học phí, thời gian đào tạo và điều kiện tuyển sinh liên thông 2026.',
		implode( ' và ', $short_names )
	);
	if ( mb_strlen( $fallback ) <= 165 ) {
		return $fallback;
	}

	return mb_substr( $fallback, 0, 160 ) . '...';
}
add_filter( 'rank_math/frontend/description', 'ltdh_seo_compare_description', 25 );
add_filter( 'rank_math/opengraph/facebook/description', 'ltdh_seo_compare_description', 25 );
add_filter( 'rank_math/opengraph/twitter/description', 'ltdh_seo_compare_description', 25 );

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
add_filter( 'rank_math/opengraph/facebook/image', 'ltdh_seo_fallback_og_image', 20 );
add_filter( 'rank_math/opengraph/twitter/image', 'ltdh_seo_fallback_og_image', 20 );

function ltdh_seo_resolve_global_share_image() {
	$custom_share_image = null;
	if ( function_exists( 'get_field' ) ) {
		$custom_share_image = get_field( 'global_share_image', 'options' ) ?: get_field( 'global_share_image', 'option' );
	}
	if ( empty( $custom_share_image ) ) {
		$raw_opt = get_option( 'options_global_share_image' ) ?: get_option( 'global_share_image' );
		if ( ! empty( $raw_opt ) ) {
			if ( is_numeric( $raw_opt ) ) {
				$custom_share_image = wp_get_attachment_image_url( (int) $raw_opt, 'full' );
			} elseif ( is_string( $raw_opt ) && filter_var( $raw_opt, FILTER_VALIDATE_URL ) ) {
				$custom_share_image = $raw_opt;
			}
		}
	}
	if ( ! empty( $custom_share_image ) ) {
		if ( is_array( $custom_share_image ) && ! empty( $custom_share_image['url'] ) ) {
			return $custom_share_image['url'];
		} elseif ( is_numeric( $custom_share_image ) ) {
			$url = wp_get_attachment_image_url( (int) $custom_share_image, 'full' );
			if ( ! empty( $url ) ) {
				return $url;
			}
		} elseif ( is_string( $custom_share_image ) ) {
			return $custom_share_image;
		}
	}

	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();
	if ( file_exists( $theme_dir . '/assets/images/banner-fallback.png' ) ) {
		return $theme_uri . '/assets/images/banner-fallback.png';
	}
	if ( file_exists( $theme_dir . '/assets/images/banner-fallback.webp' ) ) {
		return $theme_uri . '/assets/images/banner-fallback.webp';
	}
	return $theme_uri . '/assets/images/banner-default.jpg';
}

function ltdh_seo_fallback_og_image( $image_url ) {
	if ( ! empty( $image_url ) ) {
		return $image_url;
	}
	return ltdh_seo_resolve_global_share_image();
}

/**
 * Ensure og:image width, height, and mime type are set for optimal social card rendering (Zalo, Facebook).
 */
add_filter( 'rank_math/opengraph/facebook/image_array', 'ltdh_seo_enrich_og_image_meta', 20 );
function ltdh_seo_enrich_og_image_meta( $attachment ) {
	if ( ! is_array( $attachment ) || empty( $attachment['url'] ) ) {
		return $attachment;
	}
	if ( empty( $attachment['width'] ) || empty( $attachment['height'] ) ) {
		$attachment['width']  = 1200;
		$attachment['height'] = 630;
	}
	if ( empty( $attachment['type'] ) ) {
		$path = parse_url( $attachment['url'], PHP_URL_PATH );
		$ext  = strtolower( pathinfo( (string) $path, PATHINFO_EXTENSION ) );
		if ( $ext === 'webp' ) {
			$attachment['type'] = 'image/webp';
		} elseif ( $ext === 'png' ) {
			$attachment['type'] = 'image/png';
		} elseif ( $ext === 'jpg' || $ext === 'jpeg' ) {
			$attachment['type'] = 'image/jpeg';
		}
	}
	return $attachment;
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
	
	if ( empty( $thumbnail_url ) ) {
		$thumbnail_url = ltdh_seo_resolve_global_share_image();
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
		$duration    = ( function_exists( 'ltdh_get_program_duration' ) ? ltdh_get_program_duration( $program_id ) : ( function_exists( 'get_field' ) && defined( 'LTDH_META_DURATION' ) ? get_field( LTDH_META_DURATION, $program_id ) : '' ) ) ?: '1.5 - 2.5 năm';
		$mode_terms  = defined( 'LTDH_TAX_TRAINING_TYPE' ) ? wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE ) : [];
		$mode_name   = ( ! empty( $mode_terms ) && ! is_wp_error( $mode_terms ) ) ? $mode_terms[0]->name : 'Đại học từ xa';

		$tuition_num   = function_exists( 'get_field' ) ? (float) get_field( 'tuition_amount', $program_id ) : 0;
		$tuition_unit  = function_exists( 'get_field' ) ? get_field( 'tuition_unit', $program_id ) : '';
		$total_credits = function_exists( 'get_field' ) ? (int) get_field( 'tuition_total_credits', $program_id ) : 0;

		$calc_price     = 0;
		$has_calc_price = false;
		if ( $tuition_num > 0 ) {
			if ( 'tin-chi' === $tuition_unit && $total_credits > 0 ) {
				$calc_price     = $tuition_num * $total_credits;
				$has_calc_price = true;
			} elseif ( 'toan-khoa' === $tuition_unit ) {
				$calc_price     = $tuition_num;
				$has_calc_price = true;
			}
		}

		$fallback_offer = [
			'@type'        => 'Offer',
			'category'     => 'Paid',
			'availability' => 'https://schema.org/InStock',
			'url'          => get_permalink( $program_id ),
		];
		if ( $has_calc_price && $calc_price > 0 ) {
			$fallback_offer['price']         = (string) $calc_price;
			$fallback_offer['priceCurrency'] = 'VND';
		}

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
			'offers'                       => $fallback_offer,
		];

		$schemas[] = [
			'@context'                     => 'https://schema.org',
			'@type'                        => 'EducationalOccupationalProgram',
			'name'                         => 'Tuyển sinh Liên thông: ' . get_the_title( $program_id ),
			'description'                  => get_the_excerpt( $program_id ) ?: get_the_title( $program_id ),
			'provider'                     => [
				'@type' => 'CollegeOrUniversity',
				'name'  => $school_id ? get_the_title( $school_id ) : get_bloginfo( 'name' ),
				'url'   => $school_id ? get_permalink( $school_id ) : home_url( '/' ),
			],
			'educationalProgramMode'       => $mode_name,
			'programPrerequisites'         => 'Tốt nghiệp trình độ Trung cấp hoặc Cao đẳng theo quy chế tuyển sinh của Bộ GD&ĐT',
			'educationalCredentialAwarded' => 'Bằng Cử nhân / Bằng Kỹ sư',
			'url'                          => get_permalink( $program_id ),
			'offers'                       => $fallback_offer,
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

/**
 * 8. Fallback Alt Attribute for Attachments
 * Ensures all images rendered via wp_get_attachment_image have valid descriptive alt text.
 */
add_filter( 'wp_get_attachment_image_attributes', function( $attr, $attachment ) {
	if ( empty( trim( $attr['alt'] ?? '' ) ) ) {
		$parent_id = wp_get_post_parent_id( $attachment->ID );
		if ( $parent_id ) {
			$attr['alt'] = get_the_title( $parent_id );
		} elseif ( ! empty( $attachment->post_title ) ) {
			$attr['alt'] = $attachment->post_title;
		} else {
			$attr['alt'] = get_bloginfo( 'name' );
		}
	}
	return $attr;
}, 10, 2 );

/**
 * 9. Force HTML Language & OpenGraph Locale to Vietnamese (vi / vi_VN)
 */
add_filter( 'language_attributes', function( $output ) {
	return 'lang="vi"';
}, 99 );

add_filter( 'rank_math/opengraph/facebook/og_locale', function() {
	return 'vi_VN';
}, 99 );



