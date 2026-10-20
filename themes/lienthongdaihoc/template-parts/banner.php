<?php
/**
 * Reusable Banner Partial
 * 
 * Usage: get_template_part( 'template-parts/banner' );
 * 
 * Displays a full-width hero banner with overlay text.
 * Supports: ACF page_banner field, post thumbnails, or contextual defaults.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine banner image
$banner_image    = '';
$banner_title    = '';
$banner_subtitle = '';
$banner_eyebrow  = null;

$type = get_query_var( 'ltdh_compare' );
$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );

if ( preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)(?:/page/\d+)?/?$#i', $request_path ) ) {
	$banner_title    = 'Hình thức đào tạo';
	$banner_subtitle = 'Tổng hợp các chương trình tuyển sinh Liên thông Đại học hệ Chính quy, Vừa học vừa làm và Đào tạo từ xa';
	$banner_eyebrow  = [
		'text' => 'Hình thức đào tạo liên thông ' . date( 'Y' ),
	];
} elseif ( $type === 'program' ) {
	$banner_title    = 'So sánh chương trình đào tạo';
	$banner_subtitle = 'So sánh chi tiết học phí, thời gian học, điều kiện tuyển sinh của các chương trình học.';
} elseif ( is_page( 'kiem-tra-dieu-kien' ) || preg_match( '#^/kiem-tra-dieu-kien/?#i', $request_path ) ) {
	$banner_title    = 'Kiểm tra điều kiện Liên thông Đại học';
	$banner_subtitle = 'Chỉ với 60 giây đối chiếu thông tin để tìm đúng lộ trình liên thông từ Trung cấp hoặc Cao đẳng lên Đại học phù hợp nhất cho bạn.';
	$banner_eyebrow  = [
		'text' => 'Công cụ đối chiếu điều kiện tuyển sinh 2026',
		'url'  => '#eligibility-app',
	];
} elseif ( is_page_template( 'page-faq.php' ) || is_page( 'cau-hoi-thuong-gap' ) || is_page( 'faq' ) ) {
	$banner_title    = 'Câu Hỏi Thường Gặp';
	$banner_subtitle = get_field( 'page_banner_subtitle' ) ?: 'Giải đáp tất cả thắc mắc về điều kiện tuyển sinh, quy trình nộp hồ sơ, học phí, hình thức học trực tuyến và giá trị văn bằng tốt nghiệp.';
	$banner_eyebrow  = [
		'text' => 'Giải đáp thắc mắc tuyển sinh',
	];
	$banner_image    = get_field( 'page_banner' ) ?: '';
} elseif ( is_page() ) {
	// Pages: ACF banner field → featured image → default
	$banner_image    = get_field( 'page_banner' ) ?: '';
	$banner_subtitle = get_field( 'page_banner_subtitle' ) ?: '';
	$banner_title    = get_the_title();
} elseif ( is_singular( 'school' ) ) {
	$banner_title    = get_the_title();
	$banner_subtitle = 'Thông tin chi tiết về trường đào tạo đối tác';
	$banner_image    = get_field( 'school_banner' ) ?: get_the_post_thumbnail_url( get_the_ID(), 'full' ) ?: '';
	$banner_eyebrow  = [
		'text' => 'Trường đại học đối tác ' . date( 'Y' ),
	];
} elseif ( is_singular( 'major' ) ) {
	$major_id    = get_the_ID();
	$raw_title   = get_the_title( $major_id );
	$clean_major = preg_replace( '/^(?:ngành|cử nhân|kỹ sư)\s+/iu', '', trim( (string) $raw_title ) );
	$major_code  = get_field( 'major_code', $major_id ) ?: get_post_meta( $major_id, 'major_code', true );
	$year        = (int) date( 'Y' );

	// 1. Resolve Context School
	$school = $args['school'] ?? null;
	if ( ! $school ) {
		$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );

		if ( ! empty( $school_param ) ) {
			if ( is_numeric( $school_param ) ) {
				$school = get_post( intval( $school_param ) );
			} else {
				$school = get_page_by_path( sanitize_title( $school_param ), OBJECT, defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' );
				if ( ! $school ) {
					$school_by_code = get_posts( [
						'post_type'   => defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school',
						'post_status' => 'publish',
						'numberposts' => 1,
						'meta_query'  => [
							[
								'key'     => 'school_code',
								'value'   => sanitize_text_field( $school_param ),
								'compare' => '=',
							],
						],
					] );
					if ( ! empty( $school_by_code ) ) {
						$school = $school_by_code[0];
					}
				}
			}
		}
	}

	// If no query param, check if all programs of this major belong to a SINGLE school
	if ( ! $school ) {
		$offered_pids = get_post_meta( $major_id, defined( 'LTDH_META_OFFERED_PROGRAMS' ) ? LTDH_META_OFFERED_PROGRAMS : 'offered_programs', true );
		$direct_pids  = get_posts( [
			'post_type'   => defined( 'LTDH_CPT_PROGRAM' ) ? LTDH_CPT_PROGRAM : 'program',
			'post_status' => 'publish',
			'numberposts' => -1,
			'meta_query'  => [
				[
					'key'     => defined( 'LTDH_META_MAJOR_REL' ) ? LTDH_META_MAJOR_REL : 'major_relationship',
					'value'   => $major_id,
					'compare' => '=',
				],
			],
			'fields'      => 'ids',
		] );
		$all_pids = array_unique( array_filter( array_merge(
			is_array( $offered_pids ) ? $offered_pids : [],
			is_array( $direct_pids ) ? $direct_pids : []
		) ) );

		if ( ! empty( $all_pids ) ) {
			$distinct_schools = [];
			foreach ( $all_pids as $pid ) {
				$sid = get_field( defined( 'LTDH_META_SCHOOL_REL' ) ? LTDH_META_SCHOOL_REL : 'school_relationship', $pid ) ?: get_post_meta( $pid, 'school_relationship', true );
				if ( is_array( $sid ) ) $sid = $sid[0] ?? 0;
				if ( is_object( $sid ) ) $sid = $sid->ID;
				$sid = intval( $sid );
				if ( $sid > 0 ) {
					$distinct_schools[ $sid ] = $sid;
				}
			}
			if ( count( $distinct_schools ) === 1 ) {
				$school = get_post( reset( $distinct_schools ) );
			}
		}
	}

	// 2. Training type context (e.g. ?he=tu-xa or from $args)
	$selected_he = $args['selected_he'] ?? ( isset( $_GET['he'] ) ? sanitize_text_field( wp_unslash( $_GET['he'] ) ) : '' );
	$clean_type  = '';
	if ( ! empty( $selected_he ) ) {
		$slug_lookup = ( 'tu-xa' === $selected_he ) ? 'dao-tao-tu-xa' : $selected_he;
		$term = get_term_by( 'slug', $slug_lookup, 'training_type' ) ?: get_term_by( 'slug', $selected_he, 'training_type' );
		if ( $term ) {
			$clean_type = preg_replace( '/^hệ\s+/iu', '', trim( (string) $term->name ) );
		}
	}
	$type_part = ! empty( $clean_type ) ? ' (' . $clean_type . ')' : '';

	// 3. Eyebrow Badge Pill, Subtitle & Banner Image
	if ( $school && 'publish' === $school->post_status ) {
		$school_name      = get_the_title( $school->ID );
		$school_code      = get_field( 'school_code', $school->ID ) ?: get_post_meta( $school->ID, 'school_code', true );
		$school_permalink = get_permalink( $school->ID );
		$school_label     = $school_name . ( $school_code ? ' (' . $school_code . ')' : '' );

		$banner_eyebrow = [
			'text' => $school_label,
			'url'  => $school_permalink,
		];

		if ( ! empty( $clean_type ) ) {
			$banner_subtitle = sprintf(
				'Chương trình đào tạo Liên thông Đại học (%s) ngành %s tại %s. Chuẩn quy chế tuyển sinh & công nhận tín chỉ của Bộ GD&ĐT.',
				$clean_type,
				$clean_major,
				$school_label
			);
		} else {
			$banner_subtitle = sprintf(
				'Chương trình đào tạo Liên thông Đại học ngành %s tại %s. Chuẩn quy chế tuyển sinh & công nhận tín chỉ của Bộ GD&ĐT.',
				$clean_major,
				$school_label
			);
		}

		$banner_image = ( get_field( 'school_banner', $school->ID ) ?: '' ) ?: ( get_the_post_thumbnail_url( $school->ID, 'full' ) ?: ( get_the_post_thumbnail_url( $major_id, 'full' ) ?: '' ) );

	} else {
		// General Major Eyebrow Pill
		$banner_eyebrow = [
			'text' => 'Ngành đào tạo chuẩn Bộ GD&ĐT',
			'url'  => '#truong-tuyen-sinh',
		];

		if ( ! empty( $clean_type ) ) {
			$banner_subtitle = sprintf(
				'Chương trình đào tạo Liên thông Đại học (%s) ngành %s. Danh sách trường đào tạo, lộ trình công nhận tín chỉ và học phí năm %d.',
				$clean_type,
				$clean_major,
				$year
			);
		} else {
			$banner_subtitle = 'Danh sách các trường đại học tuyển sinh, lộ trình công nhận tín chỉ và học phí năm ' . $year;
		}

		$banner_image = get_the_post_thumbnail_url( $major_id, 'full' ) ?: '';
	}

	// 4. Banner Title
	$banner_title = 'Liên thông ' . $clean_major . $type_part;
} elseif ( is_singular( 'program' ) ) {
	$program_id      = get_the_ID();
	$school_id       = get_field( 'school_relationship', $program_id ) ?: get_post_meta( $program_id, 'school_relationship', true );
	if ( is_array( $school_id ) ) {
		$school_id = ! empty( $school_id ) ? $school_id[0] : 0;
	}
	if ( is_object( $school_id ) ) {
		$school_id = $school_id->ID;
	}
	$school_id = intval( $school_id );

	$major_id        = get_field( 'major_relationship', $program_id ) ?: get_post_meta( $program_id, 'major_relationship', true );
	if ( is_array( $major_id ) ) {
		$major_id = ! empty( $major_id ) ? $major_id[0] : 0;
	}
	if ( is_object( $major_id ) ) {
		$major_id = $major_id->ID;
	}
	$major_id = intval( $major_id );

	$training_type = '';
	$terms = wp_get_post_terms( $program_id, 'training_type' );
	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		$training_type = $terms[0]->name;
	}
	$clean_type = preg_replace( '/^hệ\s+/iu', '', trim( (string) $training_type ) );

	$school_name      = $school_id ? get_the_title( $school_id ) : '';
	$school_code      = $school_id ? ( get_field( 'school_code', $school_id ) ?: get_post_meta( $school_id, 'school_code', true ) ) : '';
	$school_permalink = $school_id ? get_permalink( $school_id ) : '';

	// UI/UX Eyebrow Badge: Highlight School entity with clean link
	if ( ! empty( $school_name ) ) {
		$school_label   = $school_name . ( $school_code ? ' (' . $school_code . ')' : '' );
		$banner_eyebrow = [
			'text' => $school_label,
			'url'  => $school_permalink,
		];
	}

	$major_name  = $major_id ? get_the_title( $major_id ) : '';
	$clean_major = $major_name ? preg_replace( '/^(?:ngành|cử nhân|kỹ sư)\s+/iu', '', trim( $major_name ) ) : '';

	$type_part = ! empty( $clean_type ) ? ' (' . $clean_type . ')' : '';

	// Concise H1 formula for single program (harmonized with specialization & breadcrumbs)
	$banner_title = function_exists( 'ltdh_get_program_banner_title' )
		? ltdh_get_program_banner_title( $program_id )
		: ( 'Liên thông ' . ( $clean_major ?: get_the_title( $program_id ) ) . $type_part );

	// Clean subtitle without repeating school name
	$banner_subtitle = sprintf(
		'Chương trình đào tạo Liên thông Đại học (%s). Chuẩn quy chế tuyển sinh & công nhận tín chỉ của Bộ GD&ĐT.',
		$clean_type ?: 'Chính quy & Từ xa'
	);
	$banner_image    = get_the_post_thumbnail_url( $program_id, 'full' ) ?: ( $school_id ? get_field( 'school_banner', $school_id ) : '' ) ?: '';
} elseif ( is_post_type_archive( 'school' ) ) {
	$banner_title    = 'Trường Đại học Đối tác';
	$banner_subtitle = 'Hệ thống các trường đại học uy tín hàng đầu Việt Nam';
	$banner_eyebrow  = [
		'text' => 'Mạng lưới đại học đối tác ' . date( 'Y' ),
	];
} elseif ( is_tax( 'major_cat' ) || ( ! empty( $request_path ) && preg_match( '#^/nhom-nganh/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $m_cat ) && 'page' !== $m_cat[1] ) ) {
	$term            = is_tax( 'major_cat' ) ? get_queried_object() : ( ! empty( $m_cat[1] ) ? get_term_by( 'slug', sanitize_title( $m_cat[1] ), 'major_cat' ) : null );
	$banner_title    = ( $term && ! empty( $term->name ) ) ? 'Nhóm ngành: ' . $term->name : 'Nhóm Ngành Đào Tạo';
	$banner_subtitle = ( $term && ! empty( $term->description ) ) ? $term->description : 'Khám phá các ngành đào tạo đa dạng với cơ hội nghề nghiệp rộng mở';
	$banner_eyebrow  = [
		'text' => 'Danh mục ngành đào tạo ' . date( 'Y' ),
	];
	if ( $term && function_exists( 'ltdh_get_major_cat_thumbnail_url' ) ) {
		$banner_image = ltdh_get_major_cat_thumbnail_url( $term->term_id, 'full' );
	}
} elseif ( is_post_type_archive( 'major' ) || ( ! empty( $request_path ) && preg_match( '#^/(?:nhom-nganh|nganh-hoc)(?:/page/\d+)?/?$#i', $request_path ) ) ) {
	$banner_title    = 'Nhóm Ngành Đào Tạo';
	$banner_subtitle = 'Khám phá các nhóm ngành đào tạo đa dạng với cơ hội nghề nghiệp rộng mở';
	$banner_eyebrow  = [
		'text' => 'Danh mục ngành đào tạo ' . date( 'Y' ),
	];
} elseif ( is_post_type_archive( 'program' ) ) {
	$selected_he = '';
	$request_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	if ( preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $m ) && 'page' !== $m[1] ) {
		$selected_he = sanitize_text_field( $m[1] );
	}
	if ( empty( $selected_he ) ) {
		$selected_he = isset( $_GET['he'] ) ? sanitize_text_field( $_GET['he'] ) : '';
	}
	if ( $selected_he ) {
		$slug_lookup = ( 'tu-xa' === $selected_he ) ? 'dao-tao-tu-xa' : $selected_he;
		$he_term     = get_term_by( 'slug', $slug_lookup, 'training_type' ) ?: get_term_by( 'slug', $selected_he, 'training_type' );
		if ( $he_term ) {
			$clean_he_name   = preg_replace( '/^hệ\s+/iu', '', $he_term->name );
			$banner_title    = 'Hình thức đào tạo: ' . $clean_he_name;
			$banner_subtitle = $he_term->description ?: 'Danh sách chương trình thuộc hình thức đào tạo ' . $clean_he_name;
		} else {
			$banner_title    = 'Chương Trình Tuyển Sinh Liên Thông Đại Học';
			$banner_subtitle = 'Tổng hợp các chương trình tuyển sinh Liên thông Đại học hệ Chính quy, Vừa học vừa làm và Đào tạo từ xa';
		}
		$banner_eyebrow  = [
			'text' => 'Hình thức đào tạo liên thông ' . date( 'Y' ),
		];
	} else {
		$banner_title    = 'Chương Trình Tuyển Sinh Liên Thông Đại Học';
		$banner_subtitle = 'Tổng hợp các chương trình tuyển sinh Liên thông Đại học hệ Chính quy, Vừa học vừa làm và Đào tạo từ xa';
		$banner_eyebrow  = [
			'text' => 'Chương trình tuyển sinh liên thông ' . date( 'Y' ),
		];
	}
} elseif ( is_tax( 'training_type' ) ) {
	$term            = get_queried_object();
	$clean_term_name = ( $term && ! empty( $term->name ) ) ? preg_replace( '/^hệ\s+/iu', '', $term->name ) : '';
	$banner_title    = 'Hình thức đào tạo: ' . $clean_term_name;
	$banner_subtitle = ( $term && ! empty( $term->description ) ) ? $term->description : ( 'Danh sách chương trình thuộc hình thức đào tạo ' . $clean_term_name );
	$banner_eyebrow  = [
		'text' => 'Hình thức đào tạo liên thông ' . date( 'Y' ),
	];
} elseif ( is_tax( 'campus' ) ) {
	$term = get_queried_object();
	$banner_title    = 'Cơ sở: ' . $term->name;
	$banner_subtitle = $term->description ?: 'Các chương trình đào tạo tại cơ sở ' . $term->name;
} elseif ( is_category() ) {
	$banner_title    = single_cat_title( '', false );
	$cat_desc        = category_description();
	$banner_subtitle = ! empty( $cat_desc ) ? wp_strip_all_tags( $cat_desc ) : 'Cập nhật thông tin tuyển sinh, hướng dẫn nhập học và tin tức giáo dục mới nhất';
	$banner_eyebrow  = [
		'text' => 'Chuyên mục tin tức',
		'url'  => home_url( '/tin-tuc/' ),
	];
} elseif ( is_tag() ) {
	$banner_title    = 'Chủ đề: ' . single_tag_title( '', false );
	$banner_subtitle = 'Tổng hợp các bài viết liên quan đến chủ đề ' . single_tag_title( '', false );
	$banner_eyebrow  = [
		'text' => 'Thẻ bài viết',
		'url'  => home_url( '/tin-tuc/' ),
	];
} elseif ( is_home() ) {
	$banner_title    = 'Cẩm Nang Tuyển Sinh & Tin Tức';
	$banner_subtitle = 'Cập nhật nhanh nhất và chính xác các quy chế thi, đề án tuyển sinh, học bổng từ các trường đại học đối tác.';
	$banner_eyebrow  = [
		'text' => 'Bản tin giáo dục & tuyển sinh ' . date( 'Y' ),
	];
} elseif ( is_singular( 'post' ) ) {
	$post_id         = get_the_ID();
	$banner_title    = get_the_title( $post_id );
	$banner_subtitle = '';
	$banner_image    = function_exists( 'ltdh_get_post_thumbnail_url' ) 
		? ltdh_get_post_thumbnail_url( $post_id, 'full' ) 
		: ( get_the_post_thumbnail_url( $post_id, 'full' ) ?: '' );

	// Badges: Category & School
	$post_cats = get_the_category( $post_id );
	$post_cat  = null;
	if ( ! empty( $post_cats ) ) {
		foreach ( $post_cats as $pc ) {
			if ( $pc->slug !== 'uncategorized' ) {
				$post_cat = $pc;
				break;
			}
		}
		if ( ! $post_cat && ! empty( $post_cats[0] ) ) {
			$post_cat = $post_cats[0];
		}
	}

	$school_id = get_field( 'school_relationship', $post_id );
	if ( is_array( $school_id ) ) {
		$school_id = ! empty( $school_id ) ? $school_id[0] : 0;
	}
	if ( is_object( $school_id ) ) {
		$school_id = $school_id->ID;
	}
	$school_id = intval( $school_id );
	$school_name = $school_id ? get_the_title( $school_id ) : '';
	$school_url  = $school_id ? get_permalink( $school_id ) : '';

	// Reading time & word count
	$post_content      = get_post_field( 'post_content', $post_id );
	$post_word_count   = str_word_count( strip_tags( (string) $post_content ) );
	$post_reading_time = max( 1, ceil( $post_word_count / 200 ) );
} else {
	if ( preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)(?:/page/\d+)?/?$#i', $request_path ) ) {
		$banner_title    = 'Hình thức đào tạo';
		$banner_subtitle = 'Tổng hợp các chương trình tuyển sinh Liên thông Đại học hệ Chính quy, Vừa học vừa làm và Đào tạo từ xa';
		$banner_eyebrow  = [
			'text' => 'Hình thức đào tạo liên thông ' . date( 'Y' ),
		];
	} elseif ( preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $m ) && 'page' !== $m[1] ) {
		$slug_lookup     = ( 'tu-xa' === $m[1] ) ? 'dao-tao-tu-xa' : $m[1];
		$he_term         = get_term_by( 'slug', $slug_lookup, 'training_type' ) ?: get_term_by( 'slug', $m[1], 'training_type' );
		$clean_he_name   = $he_term ? preg_replace( '/^hệ\s+/iu', '', $he_term->name ) : '';
		$banner_title    = $he_term ? 'Hình thức đào tạo: ' . $clean_he_name : 'Chương Trình Tuyển Sinh Liên Thông Đại Học';
		$banner_subtitle = $he_term ? ( $he_term->description ?: 'Danh sách chương trình thuộc hình thức đào tạo ' . $clean_he_name ) : 'Tổng hợp các chương trình tuyển sinh Liên thông Đại học hệ Chính quy, Vừa học vừa làm và Đào tạo từ xa';
		$banner_eyebrow  = [
			'text' => 'Hình thức đào tạo liên thông ' . date( 'Y' ),
		];
	} else {
		$banner_title    = get_the_title() ?: wp_title( '', false );
		$banner_subtitle = '';
	}
}

// Fallback banner images by context
if ( empty( $banner_image ) ) {
	$theme_uri = get_template_directory_uri();
	if ( is_singular( 'school' ) || is_post_type_archive( 'school' ) ) {
		$banner_image = $theme_uri . '/assets/images/banner-school.jpg';
	} elseif ( is_singular( 'program' ) || is_post_type_archive( 'program' ) || is_tax() ) {
		$banner_image = $theme_uri . '/assets/images/banner-program.jpg';
	} else {
		$banner_image = $theme_uri . '/assets/images/banner-default.jpg';
	}
}

if ( ! empty( $banner_image ) && function_exists( 'ltdh_get_optimized_image_url' ) ) {
	$banner_image = ltdh_get_optimized_image_url( $banner_image );
}

$banner_verif = ( is_singular( 'program' ) && function_exists( 'ltdh_get_program_verification' ) ) 
	? ltdh_get_program_verification( get_the_ID() ) 
	: null;
$source_label = ! empty( $banner_verif['source_title'] ) ? $banner_verif['source_title'] : 'Nguồn chính thức';
?>

	<section class="relative w-full bg-gradient-to-tr from-[#0F172A] via-[#1E293B] to-brand-primary text-white py-6 sm:py-8 md:py-14 overflow-hidden">
	<!-- Ambient Grid Pattern & Glow -->
	<div class="absolute inset-0 opacity-15 pointer-events-none z-0" style="background-image: radial-gradient(rgba(255,255,255,0.4) 1px, transparent 1px); background-size: 24px 24px;"></div>
	<div class="absolute -right-32 -bottom-32 w-96 h-96 bg-brand-primary/20 rounded-full blur-3xl pointer-events-none"></div>

	<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
		<?php if ( is_singular( 'post' ) ) : ?>
			<?php if ( ! empty( $post_cat ) || ! empty( $school_name ) ) : ?>
				<div class="flex flex-wrap items-center gap-2 mb-2 sm:mb-4">
					<?php if ( ! empty( $post_cat ) ) : ?>
						<a href="<?php echo esc_url( get_category_link( $post_cat->term_id ) ); ?>" 
						   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 hover:bg-white/30 text-white font-bold text-xs sm:text-sm uppercase tracking-wider backdrop-blur-md border border-white/20 transition-all duration-200 no-underline shadow-xs">
							<span><?php echo esc_html( $post_cat->name ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $school_name ) && ! empty( $school_url ) ) : ?>
						<a href="<?php echo esc_url( $school_url ); ?>" 
						   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 hover:bg-white/20 text-white/95 font-medium text-xs sm:text-sm backdrop-blur-md border border-white/15 transition-all duration-200 no-underline shadow-xs">
							<svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM3.45 9L12 4.34 20.55 9 12 13.66 3.45 9z"/></svg>
							<span><?php echo esc_html( $school_name ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php elseif ( ! empty( $banner_eyebrow ) || ( is_singular( 'program' ) && ! empty( $banner_verif['is_verified'] ) ) ) : ?>
			<div class="flex flex-wrap items-center gap-2 sm:gap-2.5 mb-2.5 sm:mb-4">
				<?php if ( ! empty( $banner_eyebrow ) ) : ?>
					<?php if ( ! empty( $banner_eyebrow['url'] ) ) : ?>
						<a href="<?php echo esc_url( $banner_eyebrow['url'] ); ?>" 
						   title="<?php echo esc_attr( $banner_eyebrow['text'] ); ?>"
						   class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-1.5 sm:px-3.5 sm:py-1.5 rounded-full bg-white/10 hover:bg-white/20 text-white/95 border border-white/15 text-xs sm:text-sm font-semibold backdrop-blur-md transition-all duration-200 no-underline shadow-xs hover:border-white/30 group">
							<svg class="w-3.5 h-3.5 text-amber-400 shrink-0 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM3.45 9L12 4.34 20.55 9 12 13.66 3.45 9zM5 12.2v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1z"/></svg>
							<span class="truncate max-w-[210px] sm:max-w-md"><?php echo esc_html( $banner_eyebrow['text'] ); ?></span>
							<span class="text-white/60 group-hover:text-white transition-colors text-xs font-normal shrink-0">→</span>
						</a>
					<?php else : ?>
						<span class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-1.5 sm:px-3.5 sm:py-1.5 rounded-full bg-white/10 text-white/95 border border-white/15 text-xs sm:text-sm font-semibold backdrop-blur-md shadow-xs">
							<svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM3.45 9L12 4.34 20.55 9 12 13.66 3.45 9zM5 12.2v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1z"/></svg>
							<span class="truncate max-w-[210px] sm:max-w-md"><?php echo esc_html( $banner_eyebrow['text'] ); ?></span>
						</span>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( is_singular( 'program' ) && ! empty( $banner_verif['is_verified'] ) ) : ?>
					<!-- Compact Verified Pill on Mobile -->
					<span class="inline-flex sm:hidden items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[11px] font-semibold backdrop-blur-xs select-none">
						<span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-emerald-500 text-white text-[9px] font-black shrink-0">✓</span>
						<span>Đã xác minh</span>
						<?php if ( ! empty( $banner_verif['source_url'] ) ) : ?>
							<a href="<?php echo esc_url( $banner_verif['source_url'] ); ?>" target="_blank" rel="noopener noreferrer nofollow" class="text-emerald-200 hover:text-white underline inline-flex items-center ml-0.5" title="<?php echo esc_attr( $source_label ); ?>">
								<svg class="w-2.5 h-2.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
							</a>
						<?php endif; ?>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight leading-snug sm:leading-tight text-white drop-shadow-sm">
			<?php echo esc_html( $banner_title ); ?>
		</h1>
		<?php if ( ! empty( $banner_subtitle ) ) : ?>
			<p class="<?php echo is_singular( 'program' ) ? 'hidden sm:block ' : ''; ?>text-slate-200/90 text-xs sm:text-sm md:text-base font-medium max-w-2xl mt-1.5 sm:mt-2.5 leading-relaxed line-clamp-2 sm:line-clamp-none">
				<?php echo esc_html( $banner_subtitle ); ?>
			</p>
		<?php endif; ?>
		<?php if ( is_singular( 'post' ) ) : ?>
			<!-- Meta Row for Single Post -->
			<div class="mt-4 flex flex-wrap items-center gap-3 sm:gap-4 text-xs sm:text-sm text-slate-200/90 font-medium">
				<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 backdrop-blur-xs border border-white/10">
					<svg class="w-3.5 h-3.5 text-blue-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
					<span><?php echo get_the_date( 'd/m/Y' ); ?></span>
				</span>
				<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 backdrop-blur-xs border border-white/10">
					<svg class="w-3.5 h-3.5 text-blue-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
					<span><?php echo esc_html( $post_reading_time ); ?> phút đọc</span>
				</span>
				<?php if ( get_the_author() ) : ?>
				<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 backdrop-blur-xs border border-white/10">
					<svg class="w-3.5 h-3.5 text-blue-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
					<span><?php the_author(); ?></span>
				</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( is_singular( 'program' ) && ! empty( $banner_verif['is_verified'] ) ) : ?>
			<!-- Rich Verification Row for Tablet & Desktop -->
			<div class="mt-3 hidden sm:inline-flex flex-wrap items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 text-xs font-semibold backdrop-blur-xs select-none">
				<span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-emerald-500 text-white text-[10px] font-black shrink-0">✓</span>
				<span>Thông tin tuyển sinh đã được xác minh</span>
				<?php if ( ! empty( $banner_verif['last_verified_at'] ) ) : ?>
					<span class="text-emerald-300/80">• <?php echo esc_html( $banner_verif['last_verified_at'] ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $banner_verif['source_url'] ) ) : ?>
					<span class="text-emerald-300/60 hidden sm:inline">•</span>
					<a href="<?php echo esc_url( $banner_verif['source_url'] ); ?>" target="_blank" rel="noopener noreferrer nofollow" class="inline-flex items-center gap-1 text-emerald-200 hover:text-white underline underline-offset-2 transition-colors font-medium">
						<span><?php echo esc_html( $source_label ); ?></span>
						<svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
