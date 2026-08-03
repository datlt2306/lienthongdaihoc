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

$type = get_query_var( 'ltdh_compare' );
$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );

if ( preg_match( '#^/he-dao-tao/?$#i', $request_path ) ) {
	$banner_title    = 'Hệ Đào Tạo';
	$banner_subtitle = 'Tổng hợp các chương trình đào tạo từ xa, liên thông, văn bằng 2';
} elseif ( $type === 'program' ) {
	$banner_title    = 'So sánh chương trình đào tạo';
	$banner_subtitle = 'So sánh chi tiết học phí, thời gian học, điều kiện tuyển sinh của các chương trình học.';
} elseif ( is_page() ) {
	// Pages: ACF banner field → featured image → default
	$banner_image    = get_field( 'page_banner' ) ?: '';
	$banner_subtitle = get_field( 'page_banner_subtitle' ) ?: '';
	$banner_title    = get_the_title();
} elseif ( is_singular( 'school' ) ) {
	$banner_title    = get_the_title();
	$banner_subtitle = 'Thông tin chi tiết về trường đào tạo đối tác';
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
	$banner_title    = 'Trường Đại học Đối tác';
	$banner_subtitle = 'Hệ thống các trường đại học uy tín hàng đầu Việt Nam';
} elseif ( is_post_type_archive( 'major' ) ) {
	$banner_title    = 'Chuyên Ngành';
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

	<section class="relative bg-gradient-to-tr from-[#0E2038] to-brand-primary text-white py-14 md:py-20 overflow-hidden">
	<?php if ( ! empty( $banner_image ) ) : ?>
		<!-- Banner Background Image with Overlay -->
		<div class="absolute inset-0 z-0">
			<img src="<?php echo esc_url( $banner_image ); ?>" class="w-full h-full object-cover object-center" alt="<?php echo esc_attr( $banner_title ); ?>">
			<div class="absolute inset-0 bg-gradient-to-r from-[#0c1b30]/90 to-brand-primary/85 mix-blend-multiply"></div>
		</div>
	<?php endif; ?>

	<!-- Dot Grid Pattern -->
	<div class="absolute inset-0 opacity-10 pointer-events-none z-0" style="background-image: radial-gradient(white 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
	<div class="absolute -right-32 -bottom-32 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>

	<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
		<h1 class="text-2xl sm:text-3xl md:text-4xl font-black font-display tracking-tight leading-tight">
			<?php echo esc_html( $banner_title ); ?>
		</h1>
		<?php if ( ! empty( $banner_subtitle ) ) : ?>
			<p class="text-blue-100 text-sm md:text-base font-semibold max-w-2xl mt-2">
				<?php echo esc_html( $banner_subtitle ); ?>
			</p>
		<?php endif; ?>
	</div>
</section>
