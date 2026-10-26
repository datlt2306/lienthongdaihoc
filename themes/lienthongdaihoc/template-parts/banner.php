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

// Determine banner image & presentation metadata
$banner_image    = '';
$banner_title    = '';
$banner_subtitle = '';
$banner_eyebrow  = null;
$banner_logo_url = '';
$banner_logo_alt = '';
$banner_initials = '';
$banner_address  = '';
$banner_website  = '';
$banner_map_url  = '';
$banner_link     = '';

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
	$school_id       = get_the_ID();
	$banner_title    = get_the_title( $school_id );
	$banner_subtitle = 'Thông tin chi tiết về trường đào tạo đối tác';
	$banner_image    = get_field( 'school_banner' ) ?: get_the_post_thumbnail_url( $school_id, 'full' ) ?: '';
	$banner_eyebrow  = [
		'text' => 'Trường đại học đối tác ' . date( 'Y' ),
	];
	$banner_logo_url = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'medium' ) : ( get_the_post_thumbnail_url( $school_id, 'thumbnail' ) ?: '' );
	$banner_logo_alt = $banner_title;
	$banner_address  = get_field( 'address', $school_id ) ?: '';
	$banner_website  = get_field( 'website', $school_id ) ?: '';
	$banner_map_url  = get_field( 'google_map_url', $school_id ) ?: get_field( 'map_url', $school_id );
	$banner_link     = get_field( 'school_banner_link', $school_id ) ?: ( get_post_meta( $school_id, 'school_banner_link', true ) ?: '' );
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

		$banner_image = ( get_field( 'school_banner', $school->ID ) ?: '' ) ?: ( get_the_post_thumbnail_url( $school->ID, 'full' ) ?: ( function_exists( 'ltdh_get_major_banner_url' ) ? ltdh_get_major_banner_url( $major_id, 'full' ) : ( get_the_post_thumbnail_url( $major_id, 'full' ) ?: '' ) ) );

		$banner_logo_url = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school->ID, 'medium' ) : ( get_the_post_thumbnail_url( $school->ID, 'thumbnail' ) ?: '' );
		$banner_logo_alt = $school_name;
		$banner_address  = get_field( 'address', $school->ID ) ?: '';
		$banner_website  = get_field( 'website', $school->ID ) ?: '';
		$banner_map_url  = get_field( 'google_map_url', $school->ID ) ?: get_field( 'map_url', $school->ID );

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

		$banner_image    = function_exists( 'ltdh_get_major_banner_url' ) ? ltdh_get_major_banner_url( $major_id, 'full' ) : ( ( get_field( 'major_banner', $major_id ) ?: '' ) ?: ( get_the_post_thumbnail_url( $major_id, 'full' ) ?: '' ) );
		$banner_logo_url = ( get_field( 'major_icon', $major_id ) ?: '' ) ?: ( get_field( 'major_logo', $major_id ) ?: '' );
		$banner_logo_alt = $clean_major;

		$words = preg_split( '/\s+/u', $clean_major );
		$banner_initials = '';
		foreach ( $words as $w ) {
			$banner_initials .= mb_substr( $w, 0, 1 );
		}
		$banner_initials = mb_strtoupper( $banner_initials );
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
	// Banner Image: Lấy ảnh riêng của chương trình, nếu không có thì mặc định lấy ảnh của ngành thuộc chương trình đó
	$banner_image    = function_exists( 'ltdh_get_program_image_url' )
		? ltdh_get_program_image_url( $program_id, 'full' )
		: ( ( get_the_post_thumbnail_url( $program_id, 'full' ) ?: '' ) ?: ( $major_id ? ( get_the_post_thumbnail_url( $major_id, 'full' ) ?: '' ) : ( ( $school_id ? get_field( 'school_banner', $school_id ) : '' ) ?: '' ) ) );

	if ( ! empty( $school_id ) ) {
		$banner_logo_url = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'medium' ) : ( get_the_post_thumbnail_url( $school_id, 'thumbnail' ) ?: '' );
		$banner_logo_alt = $school_name ?: get_the_title( $school_id );
		$banner_address  = get_field( 'address', $school_id ) ?: '';
		$banner_website  = get_field( 'website', $school_id ) ?: '';
		$banner_map_url  = get_field( 'google_map_url', $school_id ) ?: get_field( 'map_url', $school_id );

		$words = explode( ' ', $banner_logo_alt );
		foreach ( array_slice( $words, -3 ) as $w ) {
			$banner_initials .= mb_substr( $w, 0, 1 );
		}
		$banner_initials = mb_strtoupper( $banner_initials );
	}
} elseif ( is_post_type_archive( 'school' ) ) {
	$banner_title    = 'Trường Đại học Đối tác';
	$banner_subtitle = 'Hệ thống các trường đại học uy tín hàng đầu Việt Nam tuyển sinh liên thông chính quy, vừa học vừa làm & từ xa';
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

// "Tao không muốn có ảnh mặc định, nếu không có ảnh thì thôi."
// Strict zero-fallback policy: Only use explicit images/slides provided in ACF or post thumbnails.

// -------------------------------------------------------------------------
// Pure Banner & Hidden H1 Detection: Pages and Hub Archives
// Applicable to: /truong-doi-tac/, /nhom-nganh/, /hinh-thuc-dao-tao/, and standard Pages
// -------------------------------------------------------------------------
$is_pure_banner_page = false;
$hub_slug            = '';
$hub_slides          = [];

if ( ! is_singular( 'school' ) && ! is_singular( 'major' ) && ! is_singular( 'program' ) && ! is_singular( 'post' ) ) {
	if ( is_post_type_archive( 'school' ) || preg_match( '#^/truong-doi-tac(?:/page/\d+)?/?$#i', $request_path ) || ( is_page() && 'truong-doi-tac' === get_post_field( 'post_name', get_the_ID() ) ) ) {
		$is_pure_banner_page = true;
		$hub_slug            = 'truong-doi-tac';
	} elseif ( is_post_type_archive( 'major' ) || is_tax( 'major_cat' ) || preg_match( '#^/(?:nhom-nganh|nganh-hoc)(?:/.*)?$#i', $request_path ) || ( is_page() && in_array( get_post_field( 'post_name', get_the_ID() ), [ 'nhom-nganh', 'nganh-hoc' ], true ) ) ) {
		$is_pure_banner_page = true;
		$hub_slug            = 'nhom-nganh';
	} elseif ( is_post_type_archive( 'program' ) || is_tax( 'training_type' ) || preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)(?:/.*)?$#i', $request_path ) || ( is_page() && in_array( get_post_field( 'post_name', get_the_ID() ), [ 'hinh-thuc-dao-tao', 'he-dao-tao' ], true ) ) ) {
		$is_pure_banner_page = true;
		$hub_slug            = 'hinh-thuc-dao-tao';
	} elseif ( is_page() ) {
		$is_pure_banner_page = true;
		$hub_slug            = (string) get_post_field( 'post_name', get_the_ID() );
	}
}

if ( $is_pure_banner_page ) {
	$page_obj = ! empty( $hub_slug ) ? get_page_by_path( $hub_slug ) : ( is_page() ? get_post() : null );
	$page_id  = $page_obj ? $page_obj->ID : ( is_page() ? get_the_ID() : 0 );

	if ( $page_id && function_exists( 'get_field' ) ) {
		$hub_slides = get_field( 'banner_slides', $page_id ) 
			?: get_field( 'hero_slides', $page_id ) 
			?: get_field( 'slides', $page_id ) 
			?: [];

		if ( empty( $hub_slides ) ) {
			$page_img = get_field( 'page_banner', $page_id ) ?: get_the_post_thumbnail_url( $page_id, 'full' );
			if ( is_array( $page_img ) ) {
				$page_img = $page_img['url'] ?? '';
			}
			if ( ! empty( $page_img ) ) {
				$banner_image = $page_img;
			}
		}

		// Resolve banner link: Custom link OR Selected School link
		$custom_link       = get_field( 'page_banner_link', $page_id ) ?: ( get_post_meta( $page_id, 'page_banner_link', true ) ?: '' );
		$banner_school_raw = get_field( 'page_banner_school', $page_id ) ?: ( get_post_meta( $page_id, 'page_banner_school', true ) ?: 0 );
		if ( is_array( $banner_school_raw ) ) {
			$banner_school_raw = ! empty( $banner_school_raw ) ? reset( $banner_school_raw ) : 0;
		}
		if ( is_object( $banner_school_raw ) ) {
			$banner_school_raw = $banner_school_raw->ID;
		}
		$banner_school_id = intval( $banner_school_raw );

		if ( ! empty( $custom_link ) ) {
			$banner_link = $custom_link;
		} elseif ( $banner_school_id > 0 ) {
			$banner_link = get_permalink( $banner_school_id ) ?: '';
		}
	}
	// Strictly NO default image fallback if user has not uploaded any image or slides
}

if ( ! empty( $banner_image ) && function_exists( 'ltdh_get_optimized_image_url' ) ) {
	$banner_image = ltdh_get_optimized_image_url( $banner_image );
}

$has_banner_content = ( ! empty( $hub_slides ) && count( $hub_slides ) > 0 ) || ! empty( $banner_image );

$banner_verif = ( is_singular( 'program' ) && function_exists( 'ltdh_get_program_verification' ) ) 
	? ltdh_get_program_verification( get_the_ID() ) 
	: null;
$source_label = ! empty( $banner_verif['source_title'] ) ? $banner_verif['source_title'] : 'Nguồn chính thức';
?>

<?php if ( $is_pure_banner_page ) : ?>
	<!-- Semantic H1 for Screen Readers & SEO without Visual Text Display (Always rendered for page SEO) -->
	<h1 class="sr-only"><?php echo esc_html( $banner_title ); ?></h1>

	<?php if ( $has_banner_content ) : ?>
		<!-- ============================================================== -->
		<!-- PURE IMAGE HERO BANNER (CONTAINERIZED, ZERO OVERLAY TEXT, SWIPER-READY) -->
		<!-- ============================================================== -->
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6">
			<div class="relative w-full rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg border border-slate-200/90 bg-[#f8fafc]">
				<?php if ( ! empty( $hub_slides ) && count( $hub_slides ) > 1 ) : ?>
					<!-- SwiperJS Hero Slider (Performance-Optimized: LCP Eager, Lazy Non-Active, Zero CLS) -->
					<div class="swiper hero-swiper w-full min-h-[180px] sm:min-h-[280px] md:min-h-[380px] max-h-[700px]">
						<div class="swiper-wrapper">
							<?php 
							$s_idx = 0;
							foreach ( $hub_slides as $slide ) : 
								$s_desktop = '';
								if ( ! empty( $slide['image'] ) ) {
									if ( is_array( $slide['image'] ) ) {
										$s_desktop = $slide['image']['url'] ?? '';
									} elseif ( is_numeric( $slide['image'] ) ) {
										$s_desktop = wp_get_attachment_image_url( (int) $slide['image'], 'full' ) ?: '';
									} else {
										$s_desktop = (string) $slide['image'];
									}
								}
								if ( empty( $s_desktop ) ) continue;
								if ( function_exists( 'ltdh_get_optimized_image_url' ) ) {
									$s_desktop = ltdh_get_optimized_image_url( $s_desktop );
								}

								$s_mobile = '';
								if ( ! empty( $slide['image_mobile'] ) ) {
									if ( is_array( $slide['image_mobile'] ) ) {
										$s_mobile = $slide['image_mobile']['url'] ?? '';
									} elseif ( is_numeric( $slide['image_mobile'] ) ) {
										$s_mobile = wp_get_attachment_image_url( (int) $slide['image_mobile'], 'full' ) ?: '';
									} else {
										$s_mobile = (string) $slide['image_mobile'];
									}
									if ( function_exists( 'ltdh_get_optimized_image_url' ) ) {
										$s_mobile = ltdh_get_optimized_image_url( $s_mobile );
									}
								}
								$s_link = $slide['link'] ?? '';
								if ( empty( $s_link ) && ! empty( $slide['school'] ) ) {
									$sl_school_id = is_object( $slide['school'] ) ? $slide['school']->ID : intval( is_array( $slide['school'] ) ? reset( $slide['school'] ) : $slide['school'] );
									if ( $sl_school_id > 0 ) {
										$s_link = get_permalink( $sl_school_id ) ?: '';
									}
								}
								$s_alt    = ! empty( $slide['title'] ) ? $slide['title'] : $banner_title;
								$is_lcp   = ( 0 === $s_idx );
								$loading  = $is_lcp ? 'loading="eager" fetchpriority="high" decoding="async" data-no-lazy="1"' : 'loading="lazy" fetchpriority="low" decoding="async"';
								$hero_cls = $is_lcp ? 'hero-banner hero-img skip-lazy no-lazy ' : '';
							?>
								<div class="swiper-slide w-full">
									<?php if ( ! empty( $s_link ) ) : ?><a href="<?php echo esc_url( $s_link ); ?>" class="block w-full h-full"><?php endif; ?>
									<div class="relative w-full h-auto min-h-[180px] sm:min-h-[280px] md:min-h-[380px] max-h-[700px] overflow-hidden bg-[#f8fafc]">
										<picture class="relative z-10 block w-full h-auto aspect-[1942/809]" style="aspect-ratio: 1942/809;">
											<?php if ( ! empty( $s_mobile ) && $s_mobile !== $s_desktop ) : ?>
												<source media="(max-width: 768px)" srcset="<?php echo esc_url( $s_mobile ); ?>">
											<?php endif; ?>
											<img src="<?php echo esc_url( $s_desktop ); ?>" alt="<?php echo esc_attr( $s_alt ); ?>" width="1942" height="809" class="<?php echo $hero_cls; ?>w-full h-auto aspect-[1942/809] object-cover object-center pointer-events-none" style="aspect-ratio: 1942/809;" <?php echo $loading; ?>>
										</picture>
									</div>
									<?php if ( ! empty( $s_link ) ) : ?></a><?php endif; ?>
								</div>
							<?php 
								$s_idx++;
							endforeach; 
							?>
						</div>
						<!-- Swiper Pagination -->
						<div class="swiper-pagination"></div>
						<!-- Swiper Navigation Arrows -->
						<div class="swiper-button-next !text-white after:!text-base bg-black/30 hover:bg-black/50 !w-8 md:!w-10 !h-8 md:!h-10 rounded-full transition-all flex items-center justify-center"></div>
						<div class="swiper-button-prev !text-white after:!text-base bg-black/30 hover:bg-black/50 !w-8 md:!w-10 !h-8 md:!h-10 rounded-full transition-all flex items-center justify-center"></div>
					</div>
					<style>
						.hero-swiper { position: relative; overflow: hidden; list-style: none; padding: 0; z-index: 1; display: block; }
						.hero-swiper .swiper-wrapper { position: relative; width: 100%; height: 100%; z-index: 1; display: flex; transition-property: transform; box-sizing: content-box; }
						.hero-swiper .swiper-slide { flex-shrink: 0; width: 100%; height: 100%; position: relative; transition-property: transform; display: block; }
						.hero-swiper .swiper-pagination-bullet-active { background: #f5bf23 !important; width: 24px; border-radius: 4px; }
						.hero-swiper .swiper-pagination-bullet { transition: all 0.3s ease; }
						.hero-swiper .swiper-button-next, .hero-swiper .swiper-button-prev { opacity: 0; margin-top: -16px; transition: all 0.3s ease; }
						.hero-swiper:hover .swiper-button-next, .hero-swiper:hover .swiper-button-prev { opacity: 1; }
						.hero-swiper .swiper-button-next { right: 20px; }
						.hero-swiper .swiper-button-prev { left: 20px; }
					</style>
				<?php else : ?>
					<!-- Static High-Performance Single Banner Image (Zero CLS, Instant LCP) -->
					<?php 
					$single_img = '';
					if ( ! empty( $hub_slides[0]['image'] ) ) {
						$raw = $hub_slides[0]['image'];
						$single_img = is_array( $raw ) ? ( $raw['url'] ?? '' ) : ( is_numeric( $raw ) ? wp_get_attachment_image_url( (int) $raw, 'full' ) : (string) $raw );
					}
					if ( empty( $single_img ) ) {
						$single_img = ! empty( $banner_image ) ? $banner_image : '';
					}
					if ( function_exists( 'ltdh_get_optimized_image_url' ) && ! empty( $single_img ) ) {
						$single_img = ltdh_get_optimized_image_url( $single_img );
					}
					$single_link = ! empty( $banner_link ) ? $banner_link : '';
					if ( empty( $single_link ) && ! empty( $hub_slides[0] ) ) {
						$first_slide = $hub_slides[0];
						$single_link = ! empty( $first_slide['link'] ) ? $first_slide['link'] : '';
						if ( empty( $single_link ) && ! empty( $first_slide['school'] ) ) {
							$f_school_id = is_object( $first_slide['school'] ) ? $first_slide['school']->ID : intval( is_array( $first_slide['school'] ) ? reset( $first_slide['school'] ) : $first_slide['school'] );
							if ( $f_school_id > 0 ) {
								$single_link = get_permalink( $f_school_id ) ?: '';
							}
						}
					}
					?>
					<?php if ( ! empty( $single_img ) ) : ?>
						<div class="relative w-full h-auto min-h-[180px] sm:min-h-[280px] md:min-h-[380px] max-h-[700px] overflow-hidden bg-[#f8fafc]">
							<?php if ( ! empty( $single_link ) ) : ?>
								<a href="<?php echo esc_url( $single_link ); ?>" class="block w-full h-full cursor-pointer group" title="<?php echo esc_attr( $banner_title ); ?>">
							<?php endif; ?>
							<picture class="relative z-10 block w-full h-auto aspect-[1942/809]" style="aspect-ratio: 1942/809;">
								<img src="<?php echo esc_url( $single_img ); ?>" alt="<?php echo esc_attr( $banner_title ); ?>" width="1942" height="809" class="hero-banner hero-img skip-lazy no-lazy w-full h-auto aspect-[1942/809] object-cover object-center pointer-events-none group-hover:scale-[1.01] transition-transform duration-300" style="aspect-ratio: 1942/809;" loading="eager" fetchpriority="high" decoding="async" data-no-lazy="1">
							</picture>
							<?php if ( ! empty( $single_link ) ) : ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
<?php else : ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6 pb-2 sm:pb-3">
		<!-- GRAND HERO BANNER (CONTAINERIZED, SOFT SCRIM OVERLAY, BOTTOM-ANCHORED CONTENT) -->
		<section class="relative bg-slate-100 border border-slate-200/90 rounded-2xl sm:rounded-3xl overflow-hidden mb-6 shadow-lg min-h-[320px] sm:min-h-[380px] md:min-h-[440px] lg:min-h-[530px] flex flex-col justify-end">
			<?php if ( ! empty( $banner_image ) ) : ?>
				<?php if ( ! empty( $banner_link ) ) : ?><a href="<?php echo esc_url( $banner_link ); ?>" class="absolute inset-0 z-0 block cursor-pointer group/bg" title="<?php echo esc_attr( $banner_title ); ?>"><?php endif; ?>
				<!-- Background Banner Image (Bright, natural display) -->
				<img src="<?php echo esc_url( $banner_image ); ?>" 
				     class="hero-banner skip-lazy no-lazy absolute inset-0 w-full h-full object-cover object-center pointer-events-none group-hover/bg:scale-[1.01] transition-transform duration-300" 
				     alt="<?php echo esc_attr( $banner_title ); ?>" 
				     loading="eager" fetchpriority="high" decoding="async" data-no-lazy="1">
				<?php if ( ! empty( $banner_link ) ) : ?></a><?php endif; ?>
				
				<!-- Ultra-Soft Scrim Gradient: Upper 60% is 100% clear & bright; bottom has gentle fade only for text contrast -->
				<div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 via-35% to-transparent pointer-events-none"></div>
			<?php else : ?>
				<!-- Ambient Fallback Background -->
				<div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-[#00308b] to-slate-800 z-0 pointer-events-none"></div>
			<?php endif; ?>

			<!-- Subtle Ambient Top Accent Line -->
			<div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#00308b] via-blue-500 to-amber-500 z-10"></div>

			<!-- Main Brand Info Content (Anchored to Bottom of Grand Banner) -->
			<div class="relative z-10 p-5 sm:p-7 md:p-8 lg:p-10 flex flex-col sm:flex-row sm:items-end gap-4 sm:gap-6">
				<!-- School Logo Container (Only displayed when official logo is present) -->
				<?php if ( ! empty( $banner_logo_url ) ) : ?>
					<div class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 rounded-2xl sm:rounded-3xl bg-white p-2 sm:p-2.5 shadow-2xl ring-4 ring-white/15 flex items-center justify-center shrink-0 overflow-hidden">
						<img src="<?php echo esc_url( $banner_logo_url ); ?>" alt="<?php echo esc_attr( $banner_logo_alt ?: $banner_title ); ?>" class="max-w-full max-h-full object-contain">
					</div>
				<?php endif; ?>

				<!-- Title & Meta Details -->
				<div class="min-w-0 flex-1">
					<!-- School / Program / Major Title -->
					<h1 class="font-black text-white text-xl sm:text-2xl md:text-3xl lg:text-4xl leading-tight tracking-tight drop-shadow-md">
						<?php echo esc_html( $banner_title ); ?>
					</h1>

					<!-- Subtitle -->
					<?php if ( ! empty( $banner_subtitle ) ) : ?>
						<p class="hidden md:block text-white text-xs sm:text-sm md:text-base font-medium max-w-3xl mt-1.5 sm:mt-2 leading-relaxed line-clamp-2 sm:line-clamp-none drop-shadow-[0_1px_2px_rgba(0,0,0,0.85)]">
							<?php echo esc_html( $banner_subtitle ); ?>
						</p>
					<?php endif; ?>

					<!-- Address & Website Metadata (matching the school banner style) -->
					<?php if ( ! empty( $banner_address ) || ! empty( $banner_website ) ) : ?>
						<div class="mt-2.5 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs sm:text-sm text-slate-100 drop-shadow-[0_1px_2px_rgba(0,0,0,0.85)]">
							<?php if ( ! empty( $banner_address ) ) : ?>
								<div class="inline-flex items-center gap-1.5">
									<svg class="w-4 h-4 text-blue-300 shrink-0 drop-shadow-[0_1px_2px_rgba(0,0,0,0.85)]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
										<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
									</svg>
									<span><?php echo esc_html( $banner_address ); ?></span>
									<?php if ( ! empty( $banner_map_url ) ) : ?>
										<a href="<?php echo esc_url( $banner_map_url ); ?>" target="_blank" rel="noopener noreferrer" class="text-blue-200 font-bold hover:text-white hover:underline cursor-pointer transition-colors">(Bản đồ)</a>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $banner_website ) ) : ?>
								<div class="inline-flex items-center gap-1.5">
									<svg class="w-4 h-4 text-blue-300 shrink-0 drop-shadow-[0_1px_2px_rgba(0,0,0,0.85)]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
									</svg>
									<a href="<?php echo esc_url( $banner_website ); ?>" target="_blank" rel="noopener noreferrer" class="group inline-flex items-center text-blue-200 font-semibold hover:text-white hover:underline cursor-pointer transition-colors">
										<span><?php echo esc_html( preg_replace( '#^https?://#i', '', rtrim( $banner_website, '/' ) ) ); ?></span>
										<svg class="w-3.5 h-3.5 text-blue-300 group-hover:text-white transition-colors ml-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
											<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
										</svg>
									</a>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<!-- Verification Row for Programs -->
					<?php if ( is_singular( 'program' ) && ! empty( $banner_verif['is_verified'] ) ) : ?>
						<div class="mt-2.5 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-100 drop-shadow-[0_1px_2px_rgba(0,0,0,0.85)]">
							<span class="inline-flex items-center gap-1.5 text-emerald-300 font-semibold">
								<span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-emerald-500 text-white text-[9px] font-black shrink-0">✓</span>
								<span>Thông tin tuyển sinh đã được xác minh</span>
							</span>
							<?php if ( ! empty( $banner_verif['last_verified_at'] ) ) : ?>
								<span class="text-slate-300">• <?php echo esc_html( $banner_verif['last_verified_at'] ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $banner_verif['source_url'] ) ) : ?>
								<span class="text-slate-400">•</span>
								<a href="<?php echo esc_url( $banner_verif['source_url'] ); ?>" target="_blank" rel="noopener noreferrer nofollow" class="inline-flex items-center gap-1 text-blue-200 hover:text-white underline underline-offset-2 transition-colors font-medium">
									<span><?php echo esc_html( $source_label ); ?></span>
									<svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<!-- Post Reading Metadata -->
					<?php if ( is_singular( 'post' ) ) : ?>
						<div class="mt-2.5 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs sm:text-sm text-slate-100 drop-shadow-[0_1px_2px_rgba(0,0,0,0.85)]">
							<span class="inline-flex items-center gap-1.5">
								<svg class="w-3.5 h-3.5 text-blue-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
							<span><?php echo get_the_date( 'd/m/Y' ); ?></span>
						</span>
						<span class="inline-flex items-center gap-1.5">
							<svg class="w-3.5 h-3.5 text-blue-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
							<span><?php echo esc_html( $post_reading_time ); ?> phút đọc</span>
						</span>
						<?php if ( get_the_author() ) : ?>
						<span class="inline-flex items-center gap-1.5">
							<svg class="w-3.5 h-3.5 text-blue-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
							<span><?php the_author(); ?></span>
						</span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
</div>
<?php endif; ?>
