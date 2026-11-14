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
	if ( is_array( $banner_image ) ) {
		$banner_image = $banner_image['url'] ?? '';
	} elseif ( is_numeric( $banner_image ) ) {
		$banner_image = wp_get_attachment_image_url( (int) $banner_image, 'full' ) ?: '';
	}
} elseif ( is_page( 'truong-doi-tac' ) || preg_match( '#^/truong-doi-tac(?:/page/\d+)?/?$#i', $request_path ) || is_post_type_archive( 'school' ) ) {
	$banner_title    = 'Trường Đại học Đối tác';
	$banner_subtitle = ( function_exists( 'get_field' ) && get_field( 'page_banner_subtitle' ) )
		? get_field( 'page_banner_subtitle' )
		: 'Hệ thống các trường đại học uy tín hàng đầu Việt Nam tuyển sinh liên thông chính quy, vừa học vừa làm & từ xa';
	$banner_eyebrow  = [
		'text' => 'Mạng lưới đại học đối tác ' . date( 'Y' ),
	];
	if ( function_exists( 'get_field' ) && get_field( 'page_banner' ) ) {
		$raw_img = get_field( 'page_banner' );
		$banner_image = is_array( $raw_img ) ? ( $raw_img['url'] ?? '' ) : ( is_numeric( $raw_img ) ? wp_get_attachment_image_url( (int) $raw_img, 'full' ) : (string) $raw_img );
	}
} elseif ( is_page( 'nhom-nganh' ) || preg_match( '#^/(?:nhom-nganh|nganh-hoc)(?:/page/\d+)?/?$#i', $request_path ) || is_post_type_archive( 'major' ) ) {
	$banner_title    = 'Nhóm Ngành Đào Tạo';
	$banner_subtitle = ( function_exists( 'get_field' ) && get_field( 'page_banner_subtitle' ) )
		? get_field( 'page_banner_subtitle' )
		: 'Khám phá các nhóm ngành đào tạo đa dạng với cơ hội nghề nghiệp rộng mở';
	$banner_eyebrow  = [
		'text' => 'Danh mục ngành đào tạo ' . date( 'Y' ),
	];
	if ( function_exists( 'get_field' ) && get_field( 'page_banner' ) ) {
		$raw_img = get_field( 'page_banner' );
		$banner_image = is_array( $raw_img ) ? ( $raw_img['url'] ?? '' ) : ( is_numeric( $raw_img ) ? wp_get_attachment_image_url( (int) $raw_img, 'full' ) : (string) $raw_img );
	}
} elseif ( is_page() ) {
	// Pages: ACF banner field → featured image → default
	$banner_image    = get_field( 'page_banner' ) ?: '';
	if ( is_array( $banner_image ) ) {
		$banner_image = $banner_image['url'] ?? '';
	} elseif ( is_numeric( $banner_image ) ) {
		$banner_image = wp_get_attachment_image_url( (int) $banner_image, 'full' ) ?: '';
	}
	if ( empty( $banner_image ) ) {
		$banner_image = get_the_post_thumbnail_url( get_the_ID(), 'full' ) ?: '';
	}
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
	$banner_title    = 'Liên thông ' . $clean_term_name;
	$banner_subtitle = 'Danh sách chương trình tuyển sinh Liên thông Đại học ' . $clean_term_name . ' mới nhất ' . date( 'Y' );
	$banner_eyebrow  = [
		'text' => 'Tuyển sinh Liên thông ' . date( 'Y' ),
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

	// Inherit banner image from the news / posts page
	$posts_page_id = (int) get_option( 'page_for_posts' );
	if ( ! $posts_page_id ) {
		$posts_page = get_page_by_path( 'tin-tuc' );
		if ( $posts_page ) {
			$posts_page_id = (int) $posts_page->ID;
		}
	}
	if ( $posts_page_id > 0 ) {
		$raw_img = function_exists( 'get_field' ) ? get_field( 'page_banner', $posts_page_id ) : get_post_meta( $posts_page_id, 'page_banner', true );
		if ( empty( $raw_img ) ) {
			$raw_img = get_the_post_thumbnail_url( $posts_page_id, 'full' );
		}
		if ( ! empty( $raw_img ) ) {
			$banner_image = is_array( $raw_img ) ? ( $raw_img['url'] ?? '' ) : ( is_numeric( $raw_img ) ? wp_get_attachment_image_url( (int) $raw_img, 'full' ) : (string) $raw_img );
		}
	}
} elseif ( is_tag() ) {
	$banner_title    = 'Chủ đề: ' . single_tag_title( '', false );
	$banner_subtitle = 'Tổng hợp các bài viết liên quan đến chủ đề ' . single_tag_title( '', false );
	$banner_eyebrow  = [
		'text' => 'Thẻ bài viết',
		'url'  => home_url( '/tin-tuc/' ),
	];

	// Inherit banner image from the news / posts page
	$posts_page_id = (int) get_option( 'page_for_posts' );
	if ( ! $posts_page_id ) {
		$posts_page = get_page_by_path( 'tin-tuc' );
		if ( $posts_page ) {
			$posts_page_id = (int) $posts_page->ID;
		}
	}
	if ( $posts_page_id > 0 ) {
		$raw_img = function_exists( 'get_field' ) ? get_field( 'page_banner', $posts_page_id ) : get_post_meta( $posts_page_id, 'page_banner', true );
		if ( empty( $raw_img ) ) {
			$raw_img = get_the_post_thumbnail_url( $posts_page_id, 'full' );
		}
		if ( ! empty( $raw_img ) ) {
			$banner_image = is_array( $raw_img ) ? ( $raw_img['url'] ?? '' ) : ( is_numeric( $raw_img ) ? wp_get_attachment_image_url( (int) $raw_img, 'full' ) : (string) $raw_img );
		}
	}
} elseif ( is_home() ) {
	$banner_title    = 'Cẩm Nang Tuyển Sinh & Tin Tức';
	$banner_subtitle = 'Cập nhật nhanh nhất và chính xác các quy chế thi, đề án tuyển sinh, học bổng từ các trường đại học đối tác.';
	$banner_eyebrow  = [
		'text' => 'Bản tin giáo dục & tuyển sinh ' . date( 'Y' ),
	];

	// Resolve Posts Page ID (Settings > Reading or slug /tin-tuc/ or queried object)
	$posts_page_id = (int) get_option( 'page_for_posts' );
	if ( ! $posts_page_id ) {
		$posts_page = get_page_by_path( 'tin-tuc' );
		if ( $posts_page ) {
			$posts_page_id = (int) $posts_page->ID;
		}
	}
	if ( ! $posts_page_id ) {
		$posts_page_id = (int) get_queried_object_id();
	}

	if ( $posts_page_id > 0 ) {
		// 1. Banner Image: ACF page_banner -> Featured Image -> First slide image
		$raw_page_banner = function_exists( 'get_field' ) ? get_field( 'page_banner', $posts_page_id ) : get_post_meta( $posts_page_id, 'page_banner', true );
		if ( empty( $raw_page_banner ) ) {
			$raw_page_banner = get_the_post_thumbnail_url( $posts_page_id, 'full' );
		}
		if ( empty( $raw_page_banner ) && function_exists( 'get_field' ) ) {
			$slides = get_field( 'banner_slides', $posts_page_id ) ?: get_field( 'hero_slides', $posts_page_id ) ?: get_field( 'slides', $posts_page_id );
			if ( ! empty( $slides ) && is_array( $slides ) && ! empty( $slides[0]['image'] ) ) {
				$raw_page_banner = $slides[0]['image'];
			}
		}

		if ( ! empty( $raw_page_banner ) ) {
			if ( is_array( $raw_page_banner ) ) {
				$banner_image = $raw_page_banner['url'] ?? '';
			} elseif ( is_numeric( $raw_page_banner ) ) {
				$banner_image = wp_get_attachment_image_url( (int) $raw_page_banner, 'full' ) ?: '';
			} else {
				$banner_image = (string) $raw_page_banner;
			}
		}

		// 2. Custom Subtitle if entered on the page
		$custom_sub = function_exists( 'get_field' ) ? get_field( 'page_banner_subtitle', $posts_page_id ) : get_post_meta( $posts_page_id, 'page_banner_subtitle', true );
		if ( ! empty( $custom_sub ) ) {
			$banner_subtitle = $custom_sub;
		}

		// 3. Custom Banner Link
		$custom_link = function_exists( 'get_field' ) ? get_field( 'page_banner_link', $posts_page_id ) : get_post_meta( $posts_page_id, 'page_banner_link', true );
		if ( ! empty( $custom_link ) ) {
			$banner_link = $custom_link;
		} else {
			$school_ref = function_exists( 'get_field' ) ? get_field( 'page_banner_school', $posts_page_id ) : get_post_meta( $posts_page_id, 'page_banner_school', true );
			if ( is_array( $school_ref ) ) {
				$school_ref = reset( $school_ref );
			}
			if ( is_object( $school_ref ) ) {
				$school_ref = $school_ref->ID;
			}
			if ( ! empty( $school_ref ) && is_numeric( $school_ref ) ) {
				$banner_link = get_permalink( (int) $school_ref ) ?: '';
			}
		}
	}
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
		$banner_title    = $he_term ? 'Liên thông ' . $clean_he_name : 'Chương Trình Tuyển Sinh Liên Thông Đại Học';
		$banner_subtitle = $he_term ? ( 'Danh sách chương trình tuyển sinh Liên thông Đại học ' . $clean_he_name . ' mới nhất ' . date( 'Y' ) ) : 'Tổng hợp các chương trình tuyển sinh Liên thông Đại học hệ Chính quy, Vừa học vừa làm và Đào tạo từ xa';
		$banner_eyebrow  = [
			'text' => 'Tuyển sinh Liên thông ' . date( 'Y' ),
		];
	} else {
		$banner_title    = get_the_title() ?: wp_title( '', false );
		$banner_subtitle = '';
	}
}

// "Tao không muốn có ảnh mặc định, nếu không có ảnh thì thôi."
// Strict zero-fallback policy: Only use explicit images/slides provided in ACF or post thumbnails.

// -------------------------------------------------------------------------
// Banner Image & Verification Meta Resolution
// -------------------------------------------------------------------------
if ( empty( $banner_image ) ) {
	$page_img = function_exists( 'get_field' ) ? get_field( 'page_banner' ) : '';
	if ( empty( $page_img ) && is_page() ) {
		$page_img = get_the_post_thumbnail_url( get_the_ID(), 'full' );
	}
	if ( is_array( $page_img ) ) {
		$page_img = $page_img['url'] ?? '';
	} elseif ( is_numeric( $page_img ) ) {
		$page_img = wp_get_attachment_image_url( (int) $page_img, 'full' ) ?: '';
	}
	if ( ! empty( $page_img ) ) {
		$banner_image = $page_img;
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

<!-- ============================================================== -->
<!-- FULL-WIDTH GRADIENT HERO BANNER (CLASSIC BRAND BLUE STRIP)     -->
<!-- ============================================================== -->
<section class="relative w-full bg-gradient-to-tr from-[#0E2038] via-[#0F284E] to-brand-primary text-white py-8 sm:py-10 md:py-14 overflow-hidden shadow-sm">
	<?php if ( ! empty( $banner_image ) ) : ?>
		<!-- Subtle Banner Background Image with Gradient Overlay -->
		<div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
			<img src="<?php echo esc_url( $banner_image ); ?>" 
			     class="hero-banner skip-lazy no-lazy w-full h-full object-cover object-center scale-105 opacity-15 blur-[1px]" 
			     alt="<?php echo esc_attr( $banner_title ); ?>" 
			     loading="eager" fetchpriority="high" decoding="async" data-no-lazy="1">
			<div class="absolute inset-0 bg-gradient-to-r from-[#0E2038]/95 via-[#0F284E]/90 to-brand-primary/85"></div>
		</div>
	<?php endif; ?>

	<!-- Ambient Modern Background: Soft Aura Glow & Subtle Fine Grid (Clean, Eye-friendly, High-legibility) -->
	<div class="absolute inset-0 pointer-events-none z-0 overflow-hidden select-none" aria-hidden="true">
		<!-- Fine Blueprint Grid Pattern (Subtle 4% opacity, zero glare) -->
		<svg class="absolute inset-0 w-full h-full opacity-[0.04]" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
			<defs>
				<pattern id="ltdh-fine-grid" width="36" height="36" patternUnits="userSpaceOnUse">
					<path d="M 36 0 L 0 0 0 36" fill="none" stroke="#ffffff" stroke-width="1"/>
				</pattern>
				<radialGradient id="ltdh-grid-mask-grad" cx="50%" cy="50%" r="55%">
					<stop offset="0%" stop-color="#ffffff" stop-opacity="0.2"/>
					<stop offset="60%" stop-color="#ffffff" stop-opacity="0.8"/>
					<stop offset="100%" stop-color="#ffffff" stop-opacity="1"/>
				</radialGradient>
				<mask id="ltdh-grid-mask">
					<rect width="100%" height="100%" fill="url(#ltdh-grid-mask-grad)"/>
				</mask>
			</defs>
			<rect width="100%" height="100%" fill="url(#ltdh-fine-grid)" mask="url(#ltdh-grid-mask)"/>
		</svg>

		<!-- Elegant Ambient Glow Orbs (Soft Diffusion, Pure Depth) -->
		<div class="absolute -right-24 -top-24 w-96 h-96 bg-sky-400/15 rounded-full blur-3xl pointer-events-none"></div>
		<div class="absolute right-1/4 -bottom-32 w-80 h-80 bg-brand-primary/25 rounded-full blur-3xl pointer-events-none"></div>
		<div class="absolute -left-20 -bottom-20 w-72 h-72 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>

		<!-- Subtle Right-side Academic Geometric Concentric Rings (Only 5% opacity, non-intrusive) -->
		<svg class="absolute -right-16 top-1/2 -translate-y-1/2 w-[480px] h-[480px] opacity-[0.05] pointer-events-none hidden md:block" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
			<circle cx="200" cy="200" r="185" stroke="#ffffff" stroke-width="1" stroke-dasharray="4 6" />
			<circle cx="200" cy="200" r="145" stroke="#ffffff" stroke-width="1" />
			<circle cx="200" cy="200" r="105" stroke="#ffffff" stroke-width="1" stroke-dasharray="2 4" />
			<circle cx="200" cy="200" r="65" stroke="#ffffff" stroke-width="1" />
		</svg>
	</div>

	<!-- Subtle Top Accent Line -->
	<div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#00308b] via-blue-400 to-amber-400 z-10"></div>

		<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
			<!-- Eyebrow Badges Row -->
			<?php if ( is_singular( 'post' ) ) : ?>
				<?php if ( ! empty( $post_cat ) || ! empty( $school_name ) ) : ?>
					<div class="flex flex-wrap items-center gap-2 mb-2.5 sm:mb-3.5">
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
				<div class="flex flex-wrap items-center gap-2 sm:gap-2.5 mb-2.5 sm:mb-3.5">
					<?php if ( ! empty( $banner_eyebrow ) ) : ?>
						<?php if ( ! empty( $banner_eyebrow['url'] ) ) : ?>
							<a href="<?php echo esc_url( $banner_eyebrow['url'] ); ?>" 
							   title="<?php echo esc_attr( $banner_eyebrow['text'] ); ?>"
							   class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-white/10 hover:bg-white/20 text-white/95 border border-white/15 text-xs sm:text-sm font-semibold backdrop-blur-md transition-all duration-200 no-underline shadow-xs hover:border-white/30 group">
								<svg class="w-3.5 h-3.5 text-amber-400 shrink-0 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM3.45 9L12 4.34 20.55 9 12 13.66 3.45 9zM5 12.2v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1z"/></svg>
								<span class="truncate max-w-[210px] sm:max-w-md"><?php echo esc_html( $banner_eyebrow['text'] ); ?></span>
								<span class="text-white/60 group-hover:text-white transition-colors text-xs font-normal shrink-0">→</span>
							</a>
						<?php else : ?>
							<span class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-white/10 text-white/95 border border-white/15 text-xs sm:text-sm font-semibold backdrop-blur-md shadow-xs">
								<svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM3.45 9L12 4.34 20.55 9 12 13.66 3.45 9zM5 12.2v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1z"/></svg>
								<span class="truncate max-w-[210px] sm:max-w-md"><?php echo esc_html( $banner_eyebrow['text'] ); ?></span>
							</span>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( is_singular( 'program' ) && ! empty( $banner_verif['is_verified'] ) ) : ?>
						<!-- Verified Pill -->
						<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 text-xs font-semibold backdrop-blur-xs select-none">
							<span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-emerald-500 text-white text-[9px] font-black shrink-0">✓</span>
							<span>Thông tin đã xác minh</span>
							<?php if ( ! empty( $banner_verif['source_url'] ) ) : ?>
								<a href="<?php echo esc_url( $banner_verif['source_url'] ); ?>" target="_blank" rel="noopener noreferrer nofollow" class="text-emerald-200 hover:text-white underline inline-flex items-center ml-0.5" title="<?php echo esc_attr( $source_label ); ?>">
									<svg class="w-2.5 h-2.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
								</a>
							<?php endif; ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
				<!-- Brand Logo Container (Only displayed when official logo is present) -->
				<?php if ( ! empty( $banner_logo_url ) ) : ?>
					<div class="w-16 h-16 sm:w-20 sm:h-20 md:w-22 md:h-22 rounded-2xl bg-white p-2 shadow-xl ring-2 ring-white/20 shrink-0 flex items-center justify-center overflow-hidden">
						<img src="<?php echo esc_url( $banner_logo_url ); ?>" alt="<?php echo esc_attr( $banner_logo_alt ?: $banner_title ); ?>" class="max-w-full max-h-full object-contain">
					</div>
				<?php endif; ?>

				<!-- Title & Description Details -->
				<div class="min-w-0 flex-1">
					<h1 class="font-black text-white text-2xl sm:text-3xl md:text-4xl leading-tight tracking-tight drop-shadow-sm">
						<?php echo esc_html( $banner_title ); ?>
					</h1>

					<?php if ( ! empty( $banner_subtitle ) ) : ?>
						<p class="text-blue-100/95 text-xs sm:text-sm md:text-base font-normal max-w-3xl mt-2 leading-relaxed">
							<?php echo esc_html( $banner_subtitle ); ?>
						</p>
					<?php endif; ?>

					<!-- Address & Website Metadata for School -->
					<?php if ( ! empty( $banner_address ) || ! empty( $banner_website ) ) : ?>
						<div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs sm:text-sm text-slate-100">
							<?php if ( ! empty( $banner_address ) ) : ?>
								<div class="inline-flex items-center gap-1.5">
									<svg class="w-4 h-4 text-blue-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
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
									<svg class="w-4 h-4 text-blue-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
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

					<!-- Verification Details for Program (Full line) -->
					<?php if ( is_singular( 'program' ) && ! empty( $banner_verif['is_verified'] ) ) : ?>
						<div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-blue-100/90">
							<span class="inline-flex items-center gap-1.5 text-emerald-300 font-semibold">
								<span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-emerald-500 text-white text-[9px] font-black shrink-0">✓</span>
								<span>Thông tin tuyển sinh đã được xác minh</span>
							</span>
							<?php if ( ! empty( $banner_verif['last_verified_at'] ) ) : ?>
								<span class="text-blue-200/80">• <?php echo esc_html( $banner_verif['last_verified_at'] ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $banner_verif['source_url'] ) ) : ?>
								<span class="text-blue-200/60">•</span>
								<a href="<?php echo esc_url( $banner_verif['source_url'] ); ?>" target="_blank" rel="noopener noreferrer nofollow" class="inline-flex items-center gap-1 text-blue-200 hover:text-white underline underline-offset-2 transition-colors font-medium">
									<span><?php echo esc_html( $source_label ); ?></span>
									<svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<!-- Post Reading Metadata -->
					<?php if ( is_singular( 'post' ) ) : ?>
						<div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs sm:text-sm text-slate-100">
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
		</div>
	</section>
