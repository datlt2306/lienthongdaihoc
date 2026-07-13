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

if ( is_page() ) {
	// Pages: ACF banner field → featured image → default
	$banner_image    = get_field( 'page_banner' ) ?: '';
	$banner_subtitle = get_field( 'page_banner_subtitle' ) ?: '';
	$banner_title    = get_the_title();
} elseif ( is_singular( 'school' ) ) {
	$banner_title    = get_the_title();
	$banner_subtitle = 'Thông tin chi tiết về trường đào tạo liên kết';
	$banner_image    = get_field( 'school_banner' ) ?: get_the_post_thumbnail_url( get_the_ID(), 'full' ) ?: '';
} elseif ( is_singular( 'major' ) ) {
	$banner_title    = 'Ngành ' . get_the_title();
	$banner_subtitle = 'Tìm hiểu chương trình đào tạo, cơ hội nghề nghiệp và thông tin tuyển sinh';
	$banner_image    = get_the_post_thumbnail_url( get_the_ID(), 'full' ) ?: '';
} elseif ( is_singular( 'program' ) ) {
	$banner_title    = get_the_title();
	$school_id       = get_field( 'school_relationship' );
	if ( is_array( $school_id ) ) {
		$school_id = ! empty( $school_id ) ? $school_id[0] : 0;
	}
	if ( is_object( $school_id ) ) {
		$school_id = $school_id->ID;
	}
	$school_id = intval( $school_id );

	$banner_subtitle = $school_id ? get_the_title( $school_id ) : 'Chương trình đào tạo chất lượng cao';
	$banner_image    = get_the_post_thumbnail_url( get_the_ID(), 'full' ) ?: ( $school_id ? get_field( 'school_banner', $school_id ) : '' ) ?: '';
} elseif ( is_post_type_archive( 'school' ) ) {
	$banner_title    = 'Trường Đại học Liên kết';
	$banner_subtitle = 'Hệ thống các trường đại học uy tín hàng đầu Việt Nam';
} elseif ( is_post_type_archive( 'major' ) ) {
	$banner_title    = 'Ngành Học';
	$banner_subtitle = 'Khám phá các ngành đào tạo đa dạng với cơ hội nghề nghiệp rộng mở';
} elseif ( is_post_type_archive( 'program' ) ) {
	$selected_he = '';
	$request_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	if ( preg_match( '#^/he-dao-tao/([^/]+)/?$#i', $request_path, $m ) ) {
		$selected_he = sanitize_text_field( $m[1] );
	}
	if ( empty( $selected_he ) ) {
		$selected_he = isset( $_GET['he'] ) ? sanitize_text_field( $_GET['he'] ) : '';
	}
	if ( $selected_he ) {
		$he_term = get_term_by( 'slug', $selected_he, 'training_type' );
		if ( $he_term ) {
			$banner_title    = 'Hệ ' . $he_term->name;
			$banner_subtitle = $he_term->description ?: 'Các chương trình đào tạo thuộc hệ ' . $he_term->name;
		} else {
			$banner_title    = 'Chương Trình Đào Tạo';
			$banner_subtitle = 'Tìm kiếm chương trình phù hợp với lộ trình học tập của bạn';
		}
	} else {
		$banner_title    = 'Chương Trình Đào Tạo';
		$banner_subtitle = 'Tìm kiếm chương trình phù hợp với lộ trình học tập của bạn';
	}
} elseif ( is_tax( 'training_type' ) ) {
	$term = get_queried_object();
	$banner_title    = 'Hệ đào tạo: ' . $term->name;
	$banner_subtitle = $term->description ?: 'Danh sách chương trình thuộc hệ đào tạo ' . $term->name;
} elseif ( is_tax( 'campus' ) ) {
	$term = get_queried_object();
	$banner_title    = 'Cơ sở: ' . $term->name;
	$banner_subtitle = $term->description ?: 'Các chương trình đào tạo tại cơ sở ' . $term->name;
} elseif ( is_home() || is_category() ) {
	$banner_title    = 'Tin Tức Tuyển Sinh';
	$banner_subtitle = 'Cập nhật thông tin tuyển sinh, hướng dẫn nhập học và tin tức giáo dục mới nhất';
} elseif ( is_singular( 'post' ) ) {
	$banner_title    = get_the_title();
	$banner_subtitle = '';
	$banner_image    = get_the_post_thumbnail_url( get_the_ID(), 'full' ) ?: '';
} else {
	$banner_title    = get_the_title() ?: wp_title( '', false );
	$banner_subtitle = '';
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
?>

<section class="ltdh-banner relative w-full overflow-hidden" style="min-height: 220px;">
	<!-- Background Image -->
	<div class="absolute inset-0">
		<img src="<?php echo esc_url( $banner_image ); ?>" 
			 alt="<?php echo esc_attr( $banner_title ); ?>" 
			 class="w-full h-full object-cover" 
			 loading="eager" />
		<!-- Gradient Overlay -->
		<div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(15,23,42,0.85) 0%, rgba(37,99,235,0.7) 100%);"></div>
	</div>
	
	<!-- Content -->
	<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
		<h1 class="text-2xl md:text-4xl font-black text-white leading-tight mb-2 drop-shadow-lg">
			<?php echo esc_html( $banner_title ); ?>
		</h1>
		<?php if ( ! empty( $banner_subtitle ) ) : ?>
			<p class="text-base md:text-lg text-blue-100 font-medium max-w-2xl leading-relaxed">
				<?php echo esc_html( $banner_subtitle ); ?>
			</p>
		<?php endif; ?>
	</div>
</section>
