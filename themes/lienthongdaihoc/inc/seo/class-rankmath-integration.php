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
	$year         = (int) date( 'Y' );
	$request_uri  = $_SERVER['REQUEST_URI'] ?? '';
	$request_path = parse_url( $request_uri, PHP_URL_PATH );

	if ( is_front_page() || ( is_home() && is_front_page() ) ) {
		return sprintf( 'Liên Thông Đại Học %d: Hệ Từ Xa, Vừa Học Vừa Làm, Chính Quy', $year );
	}

	if ( is_page( 'cau-hoi-thuong-gap' ) || ( ! empty( $request_path ) && preg_match( '#^/cau-hoi-thuong-gap/?#i', $request_path ) ) ) {
		return sprintf( 'Hỏi Đáp Tuyển Sinh Liên Thông Đại Học %d | Câu Hỏi Thường Gặp', $year );
	}

	if ( is_page( 'kiem-tra-dieu-kien' ) || ( ! empty( $request_path ) && preg_match( '#^/kiem-tra-dieu-kien/?#i', $request_path ) ) ) {
		return sprintf( 'Kiểm Tra Điều Kiện Liên Thông Đại Học %d | Xét Tuyển Nhanh', $year );
	}

	if ( is_singular( LTDH_CPT_PROGRAM ) ) {
		$program_id = get_the_ID();
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
		if ( empty( $clean_major ) ) {
			$raw_title   = get_the_title( $program_id );
			$clean_major = preg_replace( '/^(?:tuyển sinh\s+)?liên thông\s+(?:đại học\s+)?(?:ngành\s+)?/iu', '', $raw_title );
			if ( $clean_type ) {
				$clean_major = preg_replace( '/\s*[-–(]\s*' . preg_quote( $clean_type, '/' ) . '\s*[)]?/iu', '', $clean_major );
			}
			$clean_major = trim( $clean_major, " -–\t\n\r\0\x0B" );
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
 */
function ltdh_seo_archive_document_title( $title ) {
	$request_uri  = $_SERVER['REQUEST_URI'] ?? '';
	$request_path = parse_url( $request_uri, PHP_URL_PATH );
	$site_name    = get_bloginfo( 'name' );
	$year         = (int) date( 'Y' );

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
				$clean_term_name = preg_replace( '/^hệ\s+/iu', '', $term->name );
				return sprintf( 'Liên Thông %s %d | Danh Sách Trường & Học Phí', $clean_term_name, $year );
			}
		} elseif ( is_tax( 'major_cat' ) ) {
			$term = get_queried_object();
			if ( $term && isset( $term->name ) ) {
				return sprintf( 'Nhóm Ngành %s – Tuyển Sinh Liên Thông Đại Học %d | %s', $term->name, $year, $site_name );
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

	return $title;
}
add_filter( 'rank_math/frontend/title', 'ltdh_seo_archive_document_title', 20 );
add_filter( 'rank_math/opengraph/facebook/title', 'ltdh_seo_archive_document_title', 20 );
add_filter( 'rank_math/opengraph/twitter/title', 'ltdh_seo_archive_document_title', 20 );
add_filter( 'pre_get_document_title', 'ltdh_seo_archive_document_title', 20 );
add_filter( 'wp_title', 'ltdh_seo_archive_document_title', 20 );

// ----------------------------------------------------
// 2. Dynamic Meta Description for Programs & Archives
// ----------------------------------------------------
function ltdh_seo_dynamic_description( $desc ) {
	$year         = (int) date( 'Y' );
	$request_uri  = $_SERVER['REQUEST_URI'] ?? '';
	$request_path = parse_url( $request_uri, PHP_URL_PATH );

	if ( is_front_page() || ( is_home() && is_front_page() ) ) {
		return sprintf(
			'Cổng thông tin tuyển sinh Liên thông Đại học %d. Đào tạo từ xa online, vừa học vừa làm, chính quy cho người đi làm. Nhận bằng Cử nhân, Kỹ sư chuẩn Bộ GD&ĐT.',
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

	if ( is_singular( LTDH_CPT_PROGRAM ) ) {
		$program_id = get_the_ID();
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
		if ( empty( $clean_major ) ) {
			$raw_title   = get_the_title( $program_id );
			$clean_major = preg_replace( '/^(?:tuyển sinh\s+)?liên thông\s+(?:đại học\s+)?(?:ngành\s+)?/iu', '', $raw_title );
			if ( $clean_type ) {
				$clean_major = preg_replace( '/\s*[-–(]\s*' . preg_quote( $clean_type, '/' ) . '\s*[)]?/iu', '', $clean_major );
			}
			$clean_major = trim( $clean_major, " -–\t\n\r\0\x0B" );
		}

		$school_code       = $school_id ? get_post_meta( $school_id, 'school_code', true ) : '';
		$clean_school_desc = $school_code ?: ( $school ? preg_replace( '/^trường\s+đại\s+học\s+/iu', 'ĐH ', $school ) : 'đối tác' );

		return sprintf(
			'Tuyển sinh Liên thông %s (%s) tại %s. Thời gian %s, học phí %s. Hướng dẫn hồ sơ xét tuyển và miễn giảm tín chỉ.',
			$clean_major,
			$clean_type ?: 'Liên thông',
			$clean_school_desc,
			$duration,
			$tuition
		);
	}

	// 1. Archive Major (/nhom-nganh/, /nganh-hoc/)
	if ( is_post_type_archive( 'major' ) || ( ! empty( $request_path ) && preg_match( '#^/(?:nhom-nganh|nganh-hoc)/?#i', $request_path ) ) ) {
		return sprintf(
			'Danh sách ngành tuyển sinh liên thông đại học năm %d: CNTT, Quản trị kinh doanh, Kế toán, Ngôn ngữ Anh... Xem chỉ tiêu, điều kiện và học phí chi tiết.',
			$year
		);
	}

	// 2. Archive School (/truong-da-hoc/, /truong-doi-tac/)
	if ( is_post_type_archive( 'school' ) || ( ! empty( $request_path ) && preg_match( '#^/(?:truong-da-hoc|truong-doi-tac)/?#i', $request_path ) ) ) {
		return sprintf(
			'Tổng hợp danh sách các trường đại học công lập và trường đào tạo liên thông chính quy, vừa học vừa làm, đào tạo từ xa uy tín hàng đầu toàn quốc năm %d.',
			$year
		);
	}

	// 3. Training Type Archive (/hinh-thuc-dao-tao/) & Taxonomies
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
				$clean_term_name = preg_replace( '/^hệ\s+/iu', '', $term->name );
				return sprintf(
					'Danh sách tuyển sinh Liên thông Đại học %s %d. Học phí chi tiết, lộ trình công nhận tín chỉ và nộp hồ sơ xét tuyển online.',
					$clean_term_name,
					$year
				);
			}
		} elseif ( is_tax( 'major_cat' ) ) {
			$term = get_queried_object();
			if ( $term && isset( $term->name ) ) {
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
function ltdh_seo_inject_custom_schema( $data, $json_ld ) {
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
		if ( empty( $clean_major ) ) {
			$raw_title   = get_the_title( $program_id );
			$clean_major = preg_replace( '/^(?:tuyển sinh\s+)?liên thông\s+(?:đại học\s+)?(?:ngành\s+)?/iu', '', $raw_title );
			if ( $clean_type ) {
				$clean_major = preg_replace( '/\s*[-–(]\s*' . preg_quote( $clean_type, '/' ) . '\s*[)]?/iu', '', $clean_major );
			}
			$clean_major = trim( $clean_major, " -–\t\n\r\0\x0B" );
		}
		$type_part   = $clean_type ? " ($clean_type)" : '';
		$school_part = ( $school && 'Đối tác' !== $school ) ? " – $school" : '';
		$schema_program_name = 'Liên thông ' . $clean_major . $type_part . $school_part;

		$data['ltdh_course'] = [
			'@context'    => 'https://schema.org',
			'@type'       => 'Course',
			'name'        => $schema_program_name,
			'description' => get_the_excerpt() ?: 'Chương trình đào tạo đại học tuyển sinh chất lượng cao.',
			'provider'    => [
				'@type' => 'CollegeOrUniversity',
				'name'  => $school,
				'url'   => $school_id ? get_field( 'website', $school_id ) : home_url( '/' ),
			],
		];

		// Schema EducationalOccupationalProgram chuẩn hóa cho Liên thông Đại học
		$data['ltdh_program'] = [
			'@context'                     => 'https://schema.org',
			'@type'                        => 'EducationalOccupationalProgram',
			'name'                         => $schema_program_name,
			'description'                  => get_the_excerpt() ?: 'Chương trình tuyển sinh Liên thông Đại học chuẩn Bộ GD&ĐT.',
			'provider'                     => [
				'@type' => 'CollegeOrUniversity',
				'name'  => $school,
				'url'   => $school_id ? get_field( 'website', $school_id ) : home_url( '/' ),
			],
			'educationalProgramMode'       => $training_type ?: 'Liên thông Đại học',
			'programPrerequisites'         => 'Tốt nghiệp trình độ Trung cấp hoặc Cao đẳng theo quy chế tuyển sinh hiện hành của Bộ GD&ĐT',
			'educationalCredentialAwarded' => 'Bằng Cử nhân / Bằng Kỹ sư',
			'timeToComplete'               => function_exists( 'ltdh_get_program_duration' ) ? ltdh_get_program_duration( $program_id ) : ( get_field( LTDH_META_DURATION, $program_id ) ?: 'P1Y6M' ),
			'url'                          => get_permalink( $program_id ),
		];

		$tuition_num = (float) get_field( 'tuition_amount', $program_id );
		if ( $tuition_num > 0 ) {
			$data['ltdh_program']['offers'] = [
				'@type'         => 'Offer',
				'price'         => $tuition_num,
				'priceCurrency' => 'VND',
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink( $program_id ),
			];
		}

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

		if ( function_exists( 'ltdh_get_school_faqs' ) ) {
			$school_faqs = ltdh_get_school_faqs( $school_id );
			if ( ! empty( $school_faqs ) && is_array( $school_faqs ) ) {
				$faq_elements = [];
				foreach ( $school_faqs as $item ) {
					$q = trim( $item['question'] ?? '' );
					$a = trim( $item['answer'] ?? '' );
					if ( empty( $q ) || empty( $a ) ) {
						continue;
					}
					$faq_elements[] = [
						'@type'          => 'Question',
						'name'           => $q,
						'acceptedAnswer' => [
							'@type' => 'Answer',
							'text'  => wp_strip_all_tags( $a ),
						],
					];
				}
				if ( ! empty( $faq_elements ) ) {
					$data['ltdh_school_faq'] = [
						'@context'   => 'https://schema.org',
						'@type'      => 'FAQPage',
						'mainEntity' => $faq_elements,
					];
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

		if ( function_exists( 'ltdh_get_major_faqs' ) ) {
			$major_faqs = ltdh_get_major_faqs( $major_id, $school_id );
			if ( ! empty( $major_faqs ) && is_array( $major_faqs ) ) {
				$faq_elements = [];
				foreach ( $major_faqs as $item ) {
					$q = trim( $item['question'] ?? '' );
					$a = trim( $item['answer'] ?? '' );
					if ( empty( $q ) || empty( $a ) ) {
						continue;
					}
					$faq_elements[] = [
						'@type'          => 'Question',
						'name'           => $q,
						'acceptedAnswer' => [
							'@type' => 'Answer',
							'text'  => wp_strip_all_tags( $a ),
						],
					];
				}
				if ( ! empty( $faq_elements ) ) {
					$data['ltdh_major_faq'] = [
						'@context'   => 'https://schema.org',
						'@type'      => 'FAQPage',
						'mainEntity' => $faq_elements,
					];
				}
			}
		}
	}

	if ( is_page( 'cau-hoi-thuong-gap' ) || is_page_template( 'page-faq.php' ) || is_page_template( 'page-cau-hoi-thuong-gap.php' ) ) {
		$faq_items = [];
		if ( function_exists( 'get_field' ) ) {
			$faq_items = get_field( 'faq_items', 'options' ) ?: get_field( 'faq_items', 'option' ) ?: [];
		}
		if ( ! empty( $faq_items ) && is_array( $faq_items ) ) {
			$faq_elements = [];
			foreach ( $faq_items as $item ) {
				$q = trim( $item['question'] ?? '' );
				$a = trim( $item['answer'] ?? '' );
				if ( empty( $q ) || empty( $a ) ) {
					continue;
				}
				$faq_elements[] = [
					'@type'          => 'Question',
					'name'           => $q,
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => $a,
					],
				];
			}
			if ( ! empty( $faq_elements ) ) {
				$data['ltdh_faq_page'] = [
					'@context'   => 'https://schema.org',
					'@type'      => 'FAQPage',
					'mainEntity' => $faq_elements,
				];
			}
		}
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

		$crumbs[] = ltdh_seo_crumb( $program_name );

		return $crumbs;
	}

	// ---- Taxonomy major_cat: Trang chủ > [Tên nhóm ngành] ----
	if ( is_tax( 'major_cat' ) ) {
		$term = get_queried_object();
		return [
			ltdh_seo_crumb( $home_label, $home_url ),
			ltdh_seo_crumb( $term ? $term->name : 'Nhóm ngành' ),
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

		$crumbs[] = ltdh_seo_crumb( $title );
		return $crumbs;
	}

	// ---- Default: fix CPT archive labels ----
	foreach ( $crumbs as $key => $crumb ) {
		$lower_label = mb_strtolower( $crumb[0], 'UTF-8' );
		if ( in_array( $lower_label, [ 'trường học', 'truong', 'truong-lien-ket', 'truong-doi-tac', 'trường đối tác', 'schools', 'school' ], true ) ) {
			$crumbs[ $key ][0] = 'Trường đối tác';
		}
		if ( in_array( $lower_label, [ 'chương trình đào tạo', 'chuong-trinh', 'chương trình', 'programs', 'program' ], true ) ) {
			$crumbs[ $key ][0] = 'Liên thông Đại học';
			$crumbs[ $key ][1] = home_url( '/hinh-thuc-dao-tao/' );
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
		return 'Công cụ so sánh chi tiết các chương trình đào tạo liên thông đại học chính quy, từ xa và vừa học vừa làm năm 2026.';
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
		];

		$tuition_num = function_exists( 'get_field' ) ? (float) get_field( 'tuition_amount', $program_id ) : 0;
		if ( $tuition_num > 0 ) {
			$schemas[ count( $schemas ) - 1 ]['offers'] = [
				'@type'         => 'Offer',
				'price'         => $tuition_num,
				'priceCurrency' => 'VND',
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink( $program_id ),
			];
		}
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



