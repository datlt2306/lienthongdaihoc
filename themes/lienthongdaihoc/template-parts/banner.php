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

if ( preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)(?:/page/\d+)?/?$#i', $request_path ) ) {
	$banner_title    = 'Hình thức đào tạo';
	$banner_subtitle = 'Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm';
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
	$raw_title       = get_the_title();
	$banner_title    = ( 0 === stripos( trim( $raw_title ), 'ngành' ) ) ? $raw_title : 'Ngành ' . $raw_title;
	$major_code      = get_field( 'major_code', get_the_ID() );
	$code_suffix     = $major_code ? ' • Mã ngành: ' . $major_code : '';
	$banner_subtitle = 'Tìm hiểu chương trình đào tạo, cơ hội nghề nghiệp và thông tin tuyển sinh' . $code_suffix;
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
			$banner_subtitle = 'Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm';
		}
	} else {
		$banner_title    = 'Chương Trình Tuyển Sinh Liên Thông Đại Học';
		$banner_subtitle = 'Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm';
	}
} elseif ( is_tax( 'training_type' ) ) {
	$term            = get_queried_object();
	$clean_term_name = ( $term && ! empty( $term->name ) ) ? preg_replace( '/^hệ\s+/iu', '', $term->name ) : '';
	$banner_title    = 'Hình thức đào tạo: ' . $clean_term_name;
	$banner_subtitle = ( $term && ! empty( $term->description ) ) ? $term->description : ( 'Danh sách chương trình thuộc hình thức đào tạo ' . $clean_term_name );
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
	if ( preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)(?:/page/\d+)?/?$#i', $request_path ) ) {
		$banner_title    = 'Hình thức đào tạo';
		$banner_subtitle = 'Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm';
	} elseif ( preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $m ) && 'page' !== $m[1] ) {
		$slug_lookup     = ( 'tu-xa' === $m[1] ) ? 'dao-tao-tu-xa' : $m[1];
		$he_term         = get_term_by( 'slug', $slug_lookup, 'training_type' ) ?: get_term_by( 'slug', $m[1], 'training_type' );
		$clean_he_name   = $he_term ? preg_replace( '/^hệ\s+/iu', '', $he_term->name ) : '';
		$banner_title    = $he_term ? 'Hình thức đào tạo: ' . $clean_he_name : 'Chương Trình Tuyển Sinh Liên Thông Đại Học';
		$banner_subtitle = $he_term ? ( $he_term->description ?: 'Danh sách chương trình thuộc hình thức đào tạo ' . $clean_he_name ) : 'Tổng hợp các chương trình tuyển sinh liên thông đại học hình thức từ xa và vừa học vừa làm';
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
?>

	<section class="relative w-full bg-gradient-to-tr from-[#0F172A] via-[#1E293B] to-brand-primary text-white py-12 md:py-16 overflow-hidden">
	<?php if ( ! empty( $banner_image ) ) : ?>
		<!-- Banner Background Image with Gradient Overlay -->
		<div class="absolute inset-0 z-0">
			<img src="<?php echo esc_url( $banner_image ); ?>" class="w-full h-full object-cover object-center scale-105 opacity-30 blur-[1px]" alt="<?php echo esc_attr( $banner_title ); ?>" width="1920" height="400" loading="eager" fetchpriority="high" decoding="async">
			<div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-900/80 to-brand-primary/80"></div>
		</div>
	<?php endif; ?>

	<!-- Ambient Grid Pattern & Glow -->
	<div class="absolute inset-0 opacity-15 pointer-events-none z-0" style="background-image: radial-gradient(rgba(255,255,255,0.4) 1px, transparent 1px); background-size: 24px 24px;"></div>
	<div class="absolute -right-32 -bottom-32 w-96 h-96 bg-brand-primary/20 rounded-full blur-3xl pointer-events-none"></div>

	<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
		<!-- Breadcrumbs -->
		<nav class="flex items-center gap-2 text-xs text-slate-300 font-semibold mb-3">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-white transition-colors">Trang chủ</a>
			<span class="text-slate-500">/</span>
			<span class="text-brand-accent font-bold truncate max-w-xs"><?php echo esc_html( $banner_title ); ?></span>
		</nav>

		<h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black tracking-tight leading-tight text-white drop-shadow-sm">
			<?php echo esc_html( $banner_title ); ?>
		</h1>
		<?php if ( ! empty( $banner_subtitle ) ) : ?>
			<p class="text-slate-200/90 text-sm md:text-base font-medium max-w-2xl mt-2.5 leading-relaxed">
				<?php echo esc_html( $banner_subtitle ); ?>
			</p>
		<?php endif; ?>
	</div>
</section>
