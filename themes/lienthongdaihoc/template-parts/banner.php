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
	$clean_major     = preg_replace( '/^(?:ngành|cử nhân|kỹ sư)\s+/iu', '', trim( (string) $raw_title ) );
	$year            = (int) date( 'Y' );
	$banner_title    = 'Liên thông ' . $clean_major;
	$major_code      = get_field( 'major_code', get_the_ID() ) ?: get_post_meta( get_the_ID(), 'major_code', true );
	$code_suffix     = $major_code ? ' • Mã ngành: ' . $major_code : '';
	$banner_subtitle = 'Danh sách trường đào tạo, lộ trình công nhận tín chỉ và học phí năm ' . $year . $code_suffix;
	$banner_image    = get_the_post_thumbnail_url( get_the_ID(), 'full' ) ?: '';
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

	// Concise H1 formula for single program
	if ( ! empty( $clean_major ) ) {
		$banner_title = 'Liên thông ' . $clean_major . $type_part;
	} else {
		$raw_prog_title = get_the_title( $program_id );
		$clean_prog     = trim( (string) $raw_prog_title );
		$clean_prog     = preg_replace( '/^(?:tuyển sinh\s+)+/iu', '', $clean_prog );
		if ( $school_name ) {
			$clean_prog = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_name, '/' ) . '$/iu', '', $clean_prog );
		}
		if ( ! empty( $school_code ) ) {
			$clean_prog = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_code, '/' ) . '$/iu', '', $clean_prog );
		}
		if ( ! preg_match( '/^liên thông/iu', $clean_prog ) ) {
			$clean_prog = 'Liên thông ' . $clean_prog;
		}
		$banner_title = $clean_prog;
	}

	// Clean subtitle without repeating school name
	$banner_subtitle = sprintf(
		'Chương trình đào tạo Liên thông Đại học (%s). Chuẩn quy chế tuyển sinh & công nhận tín chỉ của Bộ GD&ĐT.',
		$clean_type ?: 'Chính quy & Từ xa'
	);
	$banner_image    = get_the_post_thumbnail_url( $program_id, 'full' ) ?: ( $school_id ? get_field( 'school_banner', $school_id ) : '' ) ?: '';
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
			$banner_subtitle = 'Tổng hợp các chương trình tuyển sinh Liên thông Đại học hệ Chính quy, Vừa học vừa làm và Đào tạo từ xa';
		}
	} else {
		$banner_title    = 'Chương Trình Tuyển Sinh Liên Thông Đại Học';
		$banner_subtitle = 'Tổng hợp các chương trình tuyển sinh Liên thông Đại học hệ Chính quy, Vừa học vừa làm và Đào tạo từ xa';
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
		<?php if ( ! empty( $banner_eyebrow ) ) : ?>
			<div class="mb-3 sm:mb-4">
				<?php if ( ! empty( $banner_eyebrow['url'] ) ) : ?>
					<a href="<?php echo esc_url( $banner_eyebrow['url'] ); ?>" 
					   title="<?php echo esc_attr( $banner_eyebrow['text'] ); ?>"
					   class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 hover:bg-white/20 text-white/95 border border-white/15 text-xs sm:text-sm font-semibold backdrop-blur-md transition-all duration-200 no-underline shadow-xs hover:border-white/30 group">
						<svg class="w-4 h-4 text-amber-400 shrink-0 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM3.45 9L12 4.34 20.55 9 12 13.66 3.45 9zM5 12.2v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1z"/></svg>
						<span class="truncate max-w-[280px] sm:max-w-md"><?php echo esc_html( $banner_eyebrow['text'] ); ?></span>
						<span class="text-white/60 group-hover:text-white transition-colors text-xs font-normal shrink-0">→</span>
					</a>
				<?php else : ?>
					<span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 text-white/95 border border-white/15 text-xs sm:text-sm font-semibold backdrop-blur-md shadow-xs">
						<svg class="w-4 h-4 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM3.45 9L12 4.34 20.55 9 12 13.66 3.45 9zM5 12.2v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1zm4 0v5.8c0 .28.22.5.5.5h1c.28 0 .5-.22.5-.5v-5.8l-2 1z"/></svg>
						<span class="truncate max-w-[280px] sm:max-w-md"><?php echo esc_html( $banner_eyebrow['text'] ); ?></span>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black tracking-tight leading-tight text-white drop-shadow-sm">
			<?php echo esc_html( $banner_title ); ?>
		</h1>
		<?php if ( ! empty( $banner_subtitle ) ) : ?>
			<p class="text-slate-200/90 text-sm md:text-base font-medium max-w-2xl mt-2.5 leading-relaxed">
				<?php echo esc_html( $banner_subtitle ); ?>
			</p>
		<?php endif; ?>
		<?php if ( is_singular( 'program' ) ) :
			$banner_verif = function_exists( 'ltdh_get_program_verification' ) ? ltdh_get_program_verification( get_the_ID() ) : null;
			if ( ! empty( $banner_verif['is_verified'] ) ) :
				$source_label = ! empty( $banner_verif['source_title'] ) ? $banner_verif['source_title'] : 'Nguồn chính thức';
		?>
			<div class="mt-3.5 inline-flex flex-wrap items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 text-xs font-semibold backdrop-blur-xs select-none">
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
		<?php endif; endif; ?>
	</div>
</section>
