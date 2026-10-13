<?php
/**
 * Single Program Template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$program_id = get_the_ID();
$school_id  = get_field( LTDH_META_SCHOOL_REL, $program_id );
$major_id   = get_field( LTDH_META_MAJOR_REL, $program_id );

// Retrieve school, major and global fields
$school_title        = $school_id ? get_the_title( $school_id ) : '';
$program_school_name = $school_title;
$major_title         = $major_id ? get_the_title( $major_id ) : '';
$school_logo_id      = $school_id ? ( get_field( 'logo', $school_id ) ?: get_post_thumbnail_id( $school_id ) ) : 0;
$school_logo_url     = $school_logo_id ? ( is_numeric( $school_logo_id ) ? wp_get_attachment_image_url( (int) $school_logo_id, 'thumbnail' ) : ( is_array( $school_logo_id ) ? ( $school_logo_id['url'] ?? '' ) : (string) $school_logo_id ) ) : '';
$initials            = $school_title ? ( function_exists( 'ltdh_get_initials' ) ? ltdh_get_initials( $school_title ) : 'ĐH' ) : 'ĐH';

$tuition         = ltdh_get_program_tuition_display( $program_id );
$duration        = get_field( LTDH_META_DURATION, $program_id );
$requirements    = get_field( 'admission_requirements', $program_id ) ?: get_post_meta( $program_id, 'admission_requirements', true );
$documents       = get_field( 'required_documents', $program_id ) ?: get_post_meta( $program_id, 'required_documents', true );
$enrollment      = ltdh_get_program_admission_deadline_display( $program_id );
$quota           = get_field( 'quota', $program_id );
$admission_fee   = get_field( 'admission_fee', $program_id ) ?: get_post_meta( $program_id, 'admission_fee', true );

$program_hotline = ltdh_get_program_hotline( $program_id );
$benefits        = get_field( 'program_benefits', $program_id );
if ( ! empty( $benefits ) ) {
	$benefits = str_replace( 'Tự chủ thời gian và không gian học tập, phôi bằng tốt nghiệp không ghi hình thức đào tạo.', '', $benefits );
	$benefits = str_replace( 'phôi bằng tốt nghiệp không ghi hình thức đào tạo.', '', $benefits );
	if ( empty( trim( strip_tags( $benefits ) ) ) ) {
		$benefits = '';
	}
}
$opportunities   = get_field( 'career_opportunities', $program_id );
$why_choose      = get_field( 'why_choose_us', $program_id );
$faqs            = get_field( 'faq', $program_id );

$curriculum_raw  = get_field( 'curriculum_file', $program_id );
$curriculum_url  = '';
$curriculum_type = 'file';

if ( is_numeric( $curriculum_raw ) ) {
	$attachment_id = intval( $curriculum_raw );
	$curriculum_url = wp_get_attachment_url( $attachment_id );
	$mime = get_post_mime_type( $attachment_id );
	if ( strpos( $mime, 'image' ) !== false ) {
		$curriculum_type = 'image';
	} elseif ( strpos( $mime, 'pdf' ) !== false ) {
		$curriculum_type = 'pdf';
	} else {
		$ext = strtolower( pathinfo( $curriculum_url, PATHINFO_EXTENSION ) );
		if ( in_array( $ext, array( 'png', 'jpg', 'jpeg', 'webp', 'gif' ), true ) ) {
			$curriculum_type = 'image';
		} elseif ( $ext === 'pdf' ) {
			$curriculum_type = 'pdf';
		}
	}
} elseif ( is_array( $curriculum_raw ) && ! empty( $curriculum_raw['url'] ) ) {
	$curriculum_url = $curriculum_raw['url'];
	$mime    = strtolower( $curriculum_raw['mime_type'] ?? '' );
	$type    = strtolower( $curriculum_raw['type'] ?? '' );
	$subtype = strtolower( $curriculum_raw['subtype'] ?? '' );
	if ( strpos( $mime, 'image' ) !== false || in_array( $type, array( 'image', 'png', 'jpg', 'jpeg', 'webp' ), true ) || in_array( $subtype, array( 'png', 'jpg', 'jpeg', 'webp' ), true ) ) {
		$curriculum_type = 'image';
	} elseif ( strpos( $mime, 'pdf' ) !== false || $subtype === 'pdf' || strtolower( pathinfo( $curriculum_url, PATHINFO_EXTENSION ) ) === 'pdf' ) {
		$curriculum_type = 'pdf';
	}
} elseif ( is_string( $curriculum_raw ) && ! empty( $curriculum_raw ) ) {
	$curriculum_url = $curriculum_raw;
	$ext = strtolower( pathinfo( $curriculum_url, PATHINFO_EXTENSION ) );
	if ( in_array( $ext, array( 'png', 'jpg', 'jpeg', 'webp', 'gif' ), true ) ) {
		$curriculum_type = 'image';
	} elseif ( $ext === 'pdf' ) {
		$curriculum_type = 'pdf';
	}
}

$global_zalo = ltdh_get_zalo_url();

// Dynamically construct sticky navigation tabs for Program (UI/UX Pro Max)
$admission_batches_list = get_field( 'admission_batches', $program_id );
$admission_form_file    = get_field( 'admission_form_file', $program_id );

$program_tabs = [];
$program_tabs[] = [
	'id'       => 'tong-quan',
	'title'    => 'Tổng quan',
	'subtitle' => 'Thông tin chương trình',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>',
];

if ( ! empty( $admission_batches_list ) && is_array( $admission_batches_list ) ) {
	$program_tabs[] = [
		'id'       => 'lich-tuyen-sinh',
		'title'    => 'Lịch tuyển sinh',
		'subtitle' => 'Các đợt nhận hồ sơ',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.253M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" /></svg>',
	];
}

if ( ! empty( $requirements ) || ! empty( $documents ) || ! empty( $admission_form_file ) ) {
	$program_tabs[] = [
		'id'       => 'dieu-kien-ho-so',
		'title'    => 'Điều kiện & Hồ sơ',
		'subtitle' => 'Tiêu chuẩn & Hồ sơ nộp',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>',
	];
}

$program_tabs[] = [
	'id'       => 'hoc-phi-thoi-gian',
	'title'    => 'Học phí',
	'subtitle' => 'Chi phí & thời gian',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
];

if ( ! empty( $curriculum_url ) ) {
	$program_tabs[] = [
		'id'       => 'lo-trinh-hoc',
		'title'    => 'Lộ trình học',
		'subtitle' => 'Khung chương trình',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" /></svg>',
	];
}

$program_tabs[] = [
	'id'       => 'quy-trinh-tuyen-sinh',
	'title'    => 'Quy trình',
	'subtitle' => '4 bước xét tuyển',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
];

if ( ! empty( $faqs ) ) {
	$program_tabs[] = [
		'id'       => 'hoi-dap',
		'title'    => 'Hỏi đáp',
		'subtitle' => 'Thắc mắc thường gặp',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>',
	];
}

$program_tabs[] = [
	'id'       => 'thong-tin-lien-he',
	'title'    => 'Liên hệ',
	'subtitle' => 'Đầu mối tuyển sinh',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>',
];

?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>

	<!-- STICKY SECTION TAB NAVIGATION (Redesigned with UI/UX Pro Max) -->
	<div id="ltdh-program-sticky-nav" class="ltdh-sticky-nav sticky top-16 sm:top-20 z-40 bg-white/98 backdrop-blur-md border-b border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.06)] transition-all duration-200">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<nav class="flex items-center gap-1.5  overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden py-2" aria-label="Điều hướng các mục chương trình đào tạo">
				<?php foreach ( $program_tabs as $tab_idx => $tab ) : 
					$is_active = ( 0 === $tab_idx );
				?>
					<a href="#<?php echo esc_attr( $tab['id'] ); ?>" 
					   data-tab-target="<?php echo esc_attr( $tab['id'] ); ?>"
					   class="ltdh-section-tab-link group relative inline-flex items-center gap-2.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all duration-200 shrink-0 select-none cursor-pointer no-underline <?php echo $is_active ? 'bg-blue-50/90 text-[#00308b] font-bold shadow-2xs is-active' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'; ?>">
						
						<!-- Icon Box -->
						<div class="ltdh-tab-icon w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 <?php echo $is_active ? 'bg-[#00308b] text-white shadow-xs shadow-blue-900/20' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200 group-hover:text-slate-800'; ?>">
							<?php echo $tab['icon']; ?>
						</div>

						<!-- Text Column -->
						<div class="flex flex-col text-left">
							<span class="ltdh-tab-title text-xs sm:text-[13px] font-bold leading-tight transition-colors <?php echo $is_active ? 'text-[#00308b]' : 'text-slate-700 group-hover:text-slate-900'; ?>">
								<?php echo esc_html( $tab['title'] ); ?>
							</span>
							<span class="ltdh-tab-sub text-[10px] leading-tight mt-0.5 hidden md:block transition-colors <?php echo $is_active ? 'text-blue-700/80 font-medium' : 'text-slate-400 group-hover:text-slate-500'; ?>">
								<?php echo esc_html( $tab['subtitle'] ); ?>
							</span>
						</div>

						<!-- Subtle Active Accent Indicator (Bottom pill) -->
						<span class="ltdh-tab-indicator absolute -bottom-0.5 left-3 right-3 h-[2px] rounded-full transition-all duration-200 <?php echo $is_active ? 'bg-[#EA580C] opacity-100' : 'bg-transparent opacity-0'; ?>"></span>
					</a>
				<?php endforeach; ?>
			</nav>
		</div>
	</div>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">
		

		<!-- CONTENT GRID -->
		<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-6 md:space-y-8">
				
				<?php 
				$admission_status = get_post_meta( $program_id, 'admission_status', true ) ?: 'tuyen-sinh';
				if ( $admission_status === 'tam-ngung' ) :
				?>
					<div class="bg-red-50 text-red-800 rounded-2xl p-4 flex items-start gap-3 shadow-2xs">
						<svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
						<div>
							<h4 class="font-bold text-red-900 text-sm">Đã hết chỉ tiêu tuyển sinh năm nay</h4>
							<p class="text-xs text-red-700 mt-1">Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này. Quý học viên vui lòng tham khảo các chương trình liên quan hoặc để lại thông tin đăng ký tư vấn để được hướng dẫn lộ trình phù hợp.</p>
						</div>
					</div>
				<?php endif; ?>
				
				<!-- MOBILE ONLY SCHOOL MINI BAR (< 1024px) -->
				<?php if ( $school_id ) : ?>
					<div class="block lg:hidden bg-white border border-slate-100 rounded-2xl p-3 shadow-xs">
						<div class="flex items-center justify-between gap-3">
							<div class="flex items-center gap-2.5 min-w-0">
								<div class="w-9 h-9 rounded-xl bg-slate-50 p-0.5 shrink-0 flex items-center justify-center overflow-hidden">
									<?php if ( $school_logo_url ) : ?>
										<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_title ); ?>" class="max-w-full max-h-full object-contain">
									<?php else : ?>
										<span class="text-xs font-black text-[#00308b]"><?php echo esc_html( $initials ?: 'ĐH' ); ?></span>
									<?php endif; ?>
								</div>
								<div class="min-w-0">
									<span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block leading-none mb-0.5">Trường đào tạo</span>
									<h4 class="font-extrabold text-slate-800 text-xs truncate leading-tight"><?php echo esc_html( $school_title ); ?></h4>
								</div>
							</div>
							<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition-all shrink-0">
								Chi tiết →
							</a>
						</div>
					</div>
				<?php endif; ?>

				<!-- SECTION 2: PROGRAM OVERVIEW -->
				<section id="tong-quan" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Tổng quan chương trình</h2>
					<?php ltdh_render_program_verification_badge( $program_id, 'detail' ); ?>
					<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
						<?php the_content(); ?>
					</div>
					<?php if ( $benefits ) : ?>
						<div class="mt-6 bg-teal-50/40 p-4 rounded-xl mb-6">
							<h3 class="text-teal-800 font-bold text-base mb-2">Quyền lợi nổi bật</h3>
							<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
								<?php echo wp_kses_post( $benefits ); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php
					$learning_details = ltdh_get_program_learning_details( $program_id );
					$tuition_year     = get_field( 'tuition_academic_year', $program_id );
					$degree_val       = get_field( 'degree_type', $program_id ) ?: get_post_meta( $program_id, 'degree_type', true );
					if ( empty( $degree_val ) ) {
						$clean_major = preg_replace( '/^ngành\s+/iu', '', trim( (string) $major_title ) );
						$degree_val  = ! empty( $clean_major ) ? 'Cử nhân ' . $clean_major : 'Cử nhân / Kỹ sư';
					}
					?>
					<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-3.5 py-4 border-t border-slate-100">
						<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Học phí</span>
							<span class="font-bold text-[#00308b] text-xs sm:text-sm leading-snug">
								<?php 
								echo esc_html( $tuition ?: 'Liên hệ' ); 
								if ( $tuition_year ) {
									echo ' <span class="text-[9px] font-normal text-slate-400 block sm:inline">(' . esc_html( $tuition_year ) . ')</span>';
								}
								?>
							</span>
						</div>
						<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Thời gian học</span>
							<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $duration ?: '1.5 - 2 năm' ); ?></span>
						</div>
						<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Danh hiệu văn bằng</span>
							<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $degree_val ); ?></span>
						</div>
						<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Căn cứ pháp lý</span>
							<span class="font-bold text-emerald-700 text-xs sm:text-sm leading-snug">Thông tư 27/2019/TT-BGDĐT</span>
						</div>
						<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Hình thức học</span>
							<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $learning_details['mode'] ); ?></span>
						</div>
						<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Cơ sở học</span>
							<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php 
								$display_campus = $learning_details['campus'] ?? '';
								if ( empty( $display_campus ) || 'online' === strtolower( trim( $display_campus ) ) ) {
									$display_campus = 'Toàn quốc';
								}
								echo esc_html( $display_campus ); 
							?></span>
						</div>
						<?php if ( $quota ) : ?>
							<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
								<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Chỉ tiêu</span>
								<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $quota ); ?> chỉ tiêu</span>
							</div>
						<?php endif; ?>
						<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Hạn hồ sơ</span>
							<span class="font-bold text-[#EA580C] text-xs sm:text-sm leading-snug"><?php echo esc_html( $enrollment ?: 'Đang nhận hồ sơ' ); ?></span>
						</div>
					</div>

					<!-- Thông tin pháp lý văn bằng & Giá trị toàn quốc (Thông tư 27/2019/TT-BGDĐT) -->
					<div class="mt-4 bg-emerald-50/50 rounded-xl p-4 text-xs md:text-sm text-slate-700 space-y-2 leading-relaxed">
						<p class="flex items-start gap-2">
							<span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
							<span><strong>Không ghi hình thức đào tạo:</strong> Theo quy định tại Thông tư 27/2019/TT-BGDĐT của Bộ GD&ĐT, văn bằng đại học hiện hành không ghi hình thức đào tạo (không phân biệt Chính quy, Vừa làm vừa học hay Đào tạo từ xa).</span>
						</p>
						<p class="flex items-start gap-2">
							<span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
							<span><strong>Giá trị sử dụng toàn quốc:</strong> Bằng có đầy đủ giá trị pháp lý để thi tuyển công chức, viên chức, xét nâng lương ngạch bậc và học tiếp lên Thạc sĩ, Tiến sĩ tại tất cả các cơ sở đào tạo trong và ngoài nước.</span>
						</p>
					</div>
				</section>

				<!-- SECTION: ADMISSION BATCHES -->
				<?php 
				$batches_data = get_field( 'admission_batches', $program_id );
				if ( ! empty( $batches_data ) && is_array( $batches_data ) ) : 
					// Determine training type: 'chinh-quy' -> 'Thi tuyển', others ('tu-xa', 'vua-hoc-vua-lam') -> 'Xét tuyển'
					$training_terms = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
					$t_slug         = ! empty( $training_terms ) && ! is_wp_error( $training_terms ) ? $training_terms[0]->slug : '';
					$t_name         = ! empty( $training_terms ) && ! is_wp_error( $training_terms ) ? mb_strtolower( $training_terms[0]->name, 'UTF-8' ) : '';
					$is_chinh_quy   = ( in_array( $t_slug, [ 'chinh-quy', 'chinh-quy-tap-trung' ], true ) || strpos( $t_name, 'chính quy' ) !== false );
					$eval_label     = $is_chinh_quy ? 'Thi tuyển' : 'Xét tuyển';

					$parsed_batches = [];

					foreach ( $batches_data as $b_idx => $b_item ) {
						$raw_name    = trim( $b_item['batch_name'] ?? '' );
						$clean_title = preg_replace( '/^Tuyển sinh\s*/ui', '', $raw_name );
						if ( empty( $clean_title ) ) {
							$clean_title = 'Đợt ' . ( $b_idx + 1 );
						}

						$status         = $b_item['batch_status'] ?? 'dang-nhan';
						$release_period = trim( $b_item['release_period'] ?? '' );
						$app_period     = trim( $b_item['application_period'] ?? '' );
						$review_time    = trim( $b_item['review_time'] ?? '' );
						$eval_time      = trim( $b_item['evaluation_time'] ?? '' );
						$enrol_time     = trim( $b_item['enrollment_time'] ?? '' );

						// Parse evaluation and enrollment if grouped with pipe '|'
						$eval_val  = ( $eval_time !== '-' ) ? $eval_time : '';
						$enrol_val = ( $enrol_time !== '-' ) ? $enrol_time : '';
						if ( empty( $enrol_val ) && strpos( $eval_val, '|' ) !== false ) {
							$parts     = explode( '|', $eval_val );
							$eval_val  = trim( $parts[0] );
							$enrol_val = trim( $parts[1] ?? '' );
						}

						// Standardize evaluation text based on training type
						if ( ! empty( $eval_val ) ) {
							if ( ! $is_chinh_quy ) {
								$eval_val = preg_replace( '/^Thi tuyển\s*/ui', 'Xét tuyển ', $eval_val );
							} else {
								$eval_val = preg_replace( '/^Xét tuyển\s*/ui', 'Thi tuyển ', $eval_val );
							}
						}

						$parsed_batches[ $b_idx ] = [
							'title'          => $clean_title,
							'status'         => $status,
							'release_period' => ( $release_period !== '-' ) ? $release_period : '',
							'app_period'     => ( $app_period !== '-' ) ? $app_period : '',
							'review_time'    => ( $review_time !== '-' ) ? $review_time : '',
							'eval_val'       => $eval_val,
							'enrol_val'      => $enrol_val,
						];
					}
				?>
				<section id="lich-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-6 mb-8">
					<!-- Header -->
					<div class="border-b border-slate-100 pb-3 mb-4 sm:mb-5">
						<h2 class="text-lg md:text-xl font-bold text-slate-900">Các đợt tuyển sinh</h2>
						<p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kế hoạch nhận hồ sơ, <?php echo esc_html( mb_strtolower( $eval_label, 'UTF-8' ) ); ?> và khai giảng trong năm</p>
					</div>

					<!-- Batches List (Spacious Horizontal Rows) -->
					<div class="space-y-3.5">
						<?php foreach ( $parsed_batches as $b_idx => $b_data ) : 
							$status      = $b_data['status'];
							$is_open     = ( $status === 'dang-nhan' );
							$is_upcoming = ( $status === 'sap-mo' );
							$is_closed   = ( $status === 'da-dong' );

							if ( $is_open ) {
								$row_class = 'bg-emerald-50/40 border-2 border-emerald-500 shadow-md shadow-emerald-500/10 rounded-2xl p-3';
							} elseif ( $is_upcoming ) {
								$row_class = 'bg-blue-50/25 border border-blue-200/70 rounded-2xl p-4 sm:p-5';
							} else {
								$row_class = 'bg-slate-50/60 border border-slate-200/60 rounded-2xl p-3 opacity-80 hover:opacity-100 transition-opacity';
							}
						?>
							<div class="transition-all <?php echo esc_attr( $row_class ); ?>">
								<div class="flex flex-col md:flex-row md:items-center justify-between sm:gap-3">
									
									<!-- 1. Tên đợt (Thu gọn vừa vặn, không chiếm khoảng trống thừa) -->
									<div class="w-auto md:w-24 shrink-0 flex items-center justify-between md:flex-col md:items-start md:justify-center gap-1">
										<h3 class="<?php echo $is_open ? 'text-base font-bold text-slate-900' : 'text-base font-bold text-slate-700'; ?>">
											<?php echo esc_html( $b_data['title'] ); ?>
										</h3>
										
									</div>

									<!-- 2. Các mốc thời gian trải rộng ở giữa (Theo đúng quy trình: Hạn nộp -> Xét tuyển/Thi tuyển -> Khai giảng) -->
									<div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-6 lg:gap-8">
										
										<!-- Cột 1: Hạn nộp hồ sơ -->
										<?php if ( ! empty( $b_data['app_period'] ) ) : ?>
											<div>
												<span class="text-xs font-semibold text-slate-500 block mb-1">Hạn nộp hồ sơ</span>
												<span class="<?php echo $is_open ? 'text-sm font-bold text-amber-950' : 'text-sm font-semibold text-slate-800'; ?> block leading-snug">
													<?php echo esc_html( $b_data['app_period'] ); ?>
												</span>
											</div>
										<?php endif; ?>

										<!-- Cột 2: Xét tuyển / Thi tuyển (Đặt ở giữa theo tiến trình) -->
										<div>
											<span class="text-xs font-semibold text-slate-500 block mb-1"><?php echo esc_html( $eval_label ); ?></span>
											<span class="<?php echo $is_open ? 'text-sm font-bold text-slate-900' : 'text-sm font-medium text-slate-700'; ?> block leading-snug">
												<?php echo esc_html( $b_data['eval_val'] ?: ( $is_chinh_quy ? 'Thi tuyển theo đợt' : 'Xét tuyển hồ sơ' ) ); ?>
											</span>
										</div>

										<!-- Cột 3: Khai giảng (Mốc cuối cùng) -->
										<div>
											<span class="text-xs font-semibold text-slate-500 block mb-1">Khai giảng dự kiến</span>
											<span class="<?php echo $is_open ? 'text-sm font-bold text-[#00308b]' : 'text-sm font-medium text-slate-700'; ?> block leading-snug">
												<?php echo esc_html( $b_data['enrol_val'] ?: 'Theo kế hoạch' ); ?>
											</span>
										</div>

									</div>

									<!-- 3. Trạng thái bên phải: Thay nút bằng chữ "Đang nhận hồ sơ" siêu nổi bật -->
									<div class="w-auto md:w-44 lg:w-48 shrink-0 flex items-center justify-start md:justify-end pt-1 md:pt-0">
										
										<?php if ( $is_open ) : ?>
											<div class="inline-flex items-center gap-2 px-4 py-2 sm:px-3 sm:py-2.5 rounded-full bg-emerald-600 text-white font-black text-xs sm:text-sm shadow-md ring-4 ring-emerald-500/20 whitespace-nowrap">
												<span class="relative flex h-2.5 w-2.5">
													<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
													<span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
												</span>
												<span>Đang nhận hồ sơ</span>
											</div>
										<?php elseif ( $is_upcoming ) : ?>
											<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
												Sắp mở nhận
											</span>
										<?php else : ?>
											<?php if ( $is_closed ) : ?>
												<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-200 text-slate-600">
													Đã kết thúc
												</span>
											<?php endif; ?>
										<?php endif; ?>
									</div>

								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
				<?php endif; ?>


				<!-- SECTION 3: ADMISSION REQUIREMENTS & DOCUMENTS -->
				<?php 
				$admission_form = get_field( 'admission_form_file', $program_id );
				$form_url = '';
				if ( is_array( $admission_form ) && ! empty( $admission_form['url'] ) ) {
					$form_url = $admission_form['url'];
				} elseif ( is_string( $admission_form ) && ! empty( $admission_form ) ) {
					$form_url = $admission_form;
				}

				// Nếu file URL trỏ nhầm về UTC nhưng trường không phải UTC, ánh xạ lại chính xác
				if ( ! empty( $form_url ) && str_contains( $form_url, 'utc-2026.pdf' ) ) {
					$s_code_val = get_post_meta( $school_id, 'school_code', true );
					$school_code_clean = strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', (string) $s_code_val ) );
					if ( $school_code_clean !== 'utc' ) {
						$custom_form_path = WP_CONTENT_DIR . "/uploads/forms/phieu-dang-ky-{$school_code_clean}-2026.pdf";
						if ( file_exists( $custom_form_path ) ) {
							$form_url = content_url( "/uploads/forms/phieu-dang-ky-{$school_code_clean}-2026.pdf" );
						} else {
							$form_url = home_url( "/lien-he/?school={$school_code_clean}&program_id={$program_id}" );
						}
					}
				}
				?>
				<?php if ( $requirements || $documents || $form_url ) : ?>
					<section id="dieu-kien-ho-so" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6">
						<!-- Hidden anchor targets for backward compatibility with existing links -->
						<span id="dieu-kien-xet-tuyen" class="relative -top-36 block invisible pointer-events-none"></span>
						<span id="ho-so-can-nop" class="relative -top-36 block invisible pointer-events-none"></span>

						<div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
							<div>
								<h2 class="text-lg md:text-2xl font-bold text-slate-900">Điều kiện & Hồ sơ xét tuyển</h2>
								<p class="text-xs md:text-sm text-slate-500 mt-0.5">Tiêu chuẩn tuyển sinh và danh mục hồ sơ thủ tục cần thiết để hoàn tất đăng ký nhập học</p>
							</div>
							<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-full shrink-0 self-start sm:self-auto">
								<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
								Phương thức: Xét tuyển hồ sơ
							</span>
						</div>

						<div class="space-y-6">
							<?php if ( $requirements ) : ?>
								<!-- BLOCK 1: ADMISSION REQUIREMENTS (Dynamic from WP Admin) -->
								<div>
									<h3 class="text-sm md:text-base font-bold text-slate-900 mb-3 flex items-center gap-2">
										<span class="w-6 h-6 rounded-md bg-blue-100 text-[#00308b] flex items-center justify-center text-xs font-bold shrink-0">1</span>
										<span>Điều kiện xét tuyển & Đối tượng áp dụng</span>
									</h3>
									<div class="prose prose-slate max-w-none text-slate-700 text-sm md:text-base leading-relaxed bg-slate-50/70 p-4 md:p-5 rounded-xl">
										<?php echo wp_kses_post( $requirements ); ?>
									</div>
								</div>
							<?php endif; ?>

							<?php if ( $documents || $form_url ) : ?>
								<!-- BLOCK 2: REQUIRED DOCUMENTS & ADMISSION FORM DOWNLOAD -->
								<div class="<?php echo $requirements ? 'pt-2' : ''; ?>">
									<h3 class="text-sm md:text-base font-bold text-slate-900 mb-3 flex items-center gap-2">
										<span class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold shrink-0"><?php echo $requirements ? '2' : '1'; ?></span>
										<span>Hồ sơ xét tuyển cần chuẩn bị</span>
									</h3>

									<?php if ( $documents ) : ?>
										<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base bg-slate-50/70 p-4 rounded-xl">
											<?php echo wp_kses_post( $documents ); ?>
										</div>
									<?php endif; ?>

									<?php if ( $form_url ) : ?>
										<div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-blue-50/60 p-4 rounded-xl">
											<div class="flex items-center gap-3">
												<div class="w-10 h-10 rounded-xl bg-[#00308b] text-white flex items-center justify-center font-bold text-lg shrink-0 shadow-xs">
													📄
												</div>
												<div>
													<h4 class="font-bold text-slate-900 text-sm mb-0.5">Tải mẫu phiếu đăng ký tuyển sinh</h4>
													<p class="text-xs text-slate-500">Mẫu phiếu đăng ký tuyển sinh chính thức để in và làm hồ sơ</p>
												</div>
											</div>
											<a href="<?php echo esc_url( $form_url ); ?>" download target="_blank" rel="noopener noreferrer" class="ltdh-lead-magnet-btn inline-flex items-center gap-2 px-4.5 py-2.5 bg-[#00308b] hover:bg-[#002266] text-white text-xs font-bold rounded-lg shadow-xs hover:shadow-sm transition-all shrink-0 cursor-pointer" data-file-url="<?php echo esc_url( $form_url ); ?>" data-file-title="Phiếu tuyển sinh: <?php echo esc_attr( get_the_title() ); ?>">
												<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
												<span>Tải Phiếu Tuyển Sinh</span>
											</a>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION 6: TUITION & SECTION 7: DURATION -->
				<section id="hoc-phi-thoi-gian" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Học phí & Thời gian học</h2>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
						<!-- Column 1: Tuition Details -->
						<div class="bg-white border border-slate-100 p-5 rounded-2xl flex flex-col justify-between shadow-xs transition-all duration-300">
							<div class="space-y-3.5">
								<div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-1">
									<svg class="w-5.5 h-5.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
									<h3 class="font-extrabold text-base md:text-lg text-slate-900">Thông tin học phí</h3>
								</div>
								
								<!-- Học phí per credit -->
								<div class="bg-slate-50 rounded-xl p-3.5">
									<span class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Đơn giá học phí</span>
									<div class="flex items-baseline flex-wrap gap-1">
										<span class="text-base md:text-lg font-bold text-slate-900">
											<?php echo esc_html( $tuition ?: 'Liên hệ ban tuyển sinh' ); ?>
										</span>
										<?php if ( isset($tuition_year) && $tuition_year ) : ?>
											<span class="text-xs font-semibold text-slate-600">(Năm học <?php echo esc_html( $tuition_year ); ?>)</span>
										<?php endif; ?>
									</div>
								</div>

								<!-- Admission fee (Lệ phí xét tuyển trên 1 hồ sơ) -->
								<?php 
								if ( ! empty( $admission_fee ) || ( is_numeric( $admission_fee ) && (int) $admission_fee === 0 ) ) : 
									$fee_info = function_exists( 'ltdh_format_admission_fee' ) ? ltdh_format_admission_fee( $admission_fee ) : [ 'display' => esc_html( $admission_fee ) . ' / hồ sơ', 'is_free' => false ];
								?>
									<div class="bg-slate-50 rounded-xl p-3.5">
										<span class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Lệ phí xét tuyển</span>
										<div class="flex items-baseline flex-wrap gap-1">
											<?php if ( ! empty( $fee_info['is_free'] ) ) : ?>
												<span class="text-base md:text-lg font-bold text-emerald-600">
													<?php echo esc_html( $fee_info['display'] ); ?>
												</span>
											<?php else : ?>
												<span class="text-base md:text-lg font-bold text-slate-900">
													<?php echo esc_html( $fee_info['display'] ); ?>
												</span>
											<?php endif; ?>
										</div>
									</div>
								<?php endif; ?>
							</div>

							<?php 
							$increase_roadmap = get_field( 'tuition_increase_roadmap', $program_id );
							if ( $increase_roadmap ) : 
							?>
								<div class="mt-4 pt-3.5 border-t border-slate-100 text-xs md:text-sm text-slate-600 leading-relaxed">
									<span class="font-bold text-slate-700 block mb-1">Lộ trình học phí / Ghi chú:</span>
									<p class="text-slate-600"><?php echo esc_html( $increase_roadmap ); ?></p>
								</div>
							<?php endif; ?>
						</div>

						<!-- Column 2: Study Duration -->
						<div class="bg-white border border-slate-100 p-5 rounded-2xl flex flex-col justify-between shadow-xs transition-all duration-300">
							<div class="space-y-3.5">
								<div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-1">
									<svg class="w-5.5 h-5.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
									<h3 class="font-extrabold text-base md:text-lg text-slate-900">Thời gian học tập</h3>
								</div>
								
								<!-- Card 1: Standard Duration -->
								<div class="bg-slate-50 rounded-xl p-3.5">
									<span class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Lộ trình chuẩn</span>
									<div class="flex items-baseline flex-wrap gap-1">
										<span class="text-base font-bold text-slate-900"><?php echo esc_html( $duration ?: '2.0 - 3.0 năm' ); ?></span>
									</div>
								</div>
							</div>
							
							<div class="mt-4 pt-3.5 border-t border-slate-100 text-xs md:text-sm text-slate-600 leading-relaxed">
								<span class="font-bold text-slate-700 block mb-1">Lưu ý quan trọng:</span>
								<p class="text-slate-600">Quy định miễn trừ học phần, chuyển đổi tín chỉ và số tín chỉ thực tế cần hoàn thành sẽ được Nhà trường thẩm định cụ thể dựa trên bảng điểm tốt nghiệp của từng học viên.</p>
							</div>
						</div>
					</div>
					<?php 
					if ( ! empty( $exemption_items ) && is_array( $exemption_items ) ) :
					?>
						<div class="mt-5 border-t border-slate-100 pt-5">
							<div class="bg-blue-50/40 rounded-2xl p-5">
								<div class="flex items-center gap-2 border-b border-blue-100/60 pb-3 mb-3">
									<svg class="w-6 h-6 text-blue-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222M12 14v8"></path></svg>
									<h4 class="font-extrabold text-blue-950 text-base md:text-lg">
										<?php echo esc_html( $exemption_title ?: 'Quy định miễn môn & Chuyển đổi tín chỉ' ); ?>
									</h4>
								</div>
								<?php if ( $exemption_intro ) : ?>
									<p class="text-xs md:text-sm text-blue-950 font-medium mb-4 leading-relaxed">
										<?php echo esc_html( $exemption_intro ); ?>
									</p>
								<?php endif; ?>

								<div class="space-y-4">
									<?php foreach ( $exemption_items as $index => $item ) : ?>
										<div class="bg-white rounded-xl p-4 sm:p-4.5 shadow-xs transition-all duration-300">
											<div class="flex items-center gap-2.5 mb-2">
												<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-black shrink-0"><?php echo esc_html( $index + 1 ); ?></span>
												<h5 class="font-extrabold text-slate-900 text-sm md:text-base"><?php echo esc_html( $item['subject_name'] ?? '' ); ?></h5>
											</div>
											<?php if ( ! empty( $item['condition_note'] ) ) : ?>
												<p class="text-slate-700 text-xs md:text-sm leading-relaxed pl-8">
													<?php echo nl2br( esc_html( $item['condition_note'] ) ); ?>
												</p>
											<?php endif; ?>

											<?php if ( ! empty( $item['cert_scores'] ) && is_array( $item['cert_scores'] ) ) : ?>
												<div class="pl-8 mt-3">
													<div class="grid grid-cols-3 gap-2.5 sm:gap-3 mb-3">
														<?php foreach ( $item['cert_scores'] as $cert ) : ?>
															<div class="bg-slate-50 rounded-xl p-2.5 sm:p-3 text-center transition-all duration-200 cursor-pointer">
																<span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1"><?php echo esc_html( $cert['cert_name'] ?? '' ); ?></span>
																<span class="text-sm md:text-base font-black text-blue-900"><?php echo esc_html( $cert['min_score'] ?? '' ); ?></span>
															</div>
														<?php endforeach; ?>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $item['cert_note'] ) ) : ?>
												<div class="pl-8 mt-2 flex items-start gap-2 text-xs md:text-sm text-slate-600 leading-relaxed">
													<svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
													<p><?php echo esc_html( $item['cert_note'] ); ?></p>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $item['assessment_process'] ) ) : ?>
												<div class="mt-3 bg-amber-50/80 rounded-xl p-3.5 text-xs md:text-sm text-amber-900 leading-relaxed flex items-start gap-2.5">
													<svg class="w-4.5 h-4.5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
													<p><?php echo esc_html( $item['assessment_process'] ); ?></p>
												</div>
											<?php endif; ?>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</section>

				<!-- SECTION 7.5: CURRICULUM ROADMAP FILE/IMAGE -->
				<?php if ( $curriculum_url ) : ?>
					<section class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6" id="lo-trinh-hoc">
						<div class="border-b border-slate-100 pb-3 mb-4">
							<h2 class="text-lg md:text-2xl font-bold text-slate-900">Khung chương trình</h2>
							<p class="text-xs md:text-sm text-slate-500 mt-0.5">Khung chương trình đào tạo chính thức áp dụng cho khóa học này</p>
						</div>

						<?php if ( $curriculum_type === 'image' ) : ?>
							<!-- Clickable Label Card for Image -->
							<a href="<?php echo esc_url( $curriculum_url ); ?>" target="_blank" class="flex items-center justify-between p-4 bg-slate-50/80 hover:bg-blue-50/50 rounded-xl transition-all duration-200 group shadow-xs">
								<div class="flex items-center gap-3.5">
									<div class="w-10 h-10 rounded-xl bg-blue-100/80 text-blue-700 flex items-center justify-center shrink-0">
										<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
									</div>
									<div>
										<h4 class="font-extrabold text-slate-900 text-sm group-hover:text-blue-900 transition-colors">Xem ảnh Khung chương trình đào tạo chi tiết</h4>
										<p class="text-xs text-slate-500 mt-0.5">Click để mở xem ảnh lộ trình các học kỳ & môn học kích thước chuẩn</p>
									</div>
								</div>
								<span class="inline-flex items-center gap-1.5 text-xs font-bold text-[#00308b] bg-white group-hover:bg-[#00308b] group-hover:text-white px-3.5 py-2 rounded-lg shadow-2xs transition-all shrink-0">
									<span>Xem chi tiết</span>
									<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
								</span>
							</a>
						<?php else : ?>
							<!-- PDF or File Download Box -->
							<div class="bg-slate-50/80 p-4 sm:p-5 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
								<div class="flex items-center gap-3.5 min-w-0">
									<div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center font-black text-xl shrink-0 shadow-2xs">
										PDF
									</div>
									<div class="min-w-0">
										<h4 class="font-bold text-slate-900 text-sm sm:text-base mb-1 truncate">Khung chương trình đào tạo chi tiết</h4>
										<p class="text-xs text-slate-500">Bản PDF chính thức từ nhà trường liệt kê lộ trình các học kỳ và danh sách môn học</p>
									</div>
								</div>
								<a href="<?php echo esc_url( $curriculum_url ); ?>" download target="_blank" rel="noopener noreferrer" class="ltdh-lead-magnet-btn w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#00308b] hover:bg-[#002266] text-white text-xs font-bold rounded-lg shadow-xs hover:shadow-sm transition-all shrink-0 cursor-pointer" data-file-url="<?php echo esc_url( $curriculum_url ); ?>" data-file-title="Khung chương trình: <?php echo esc_attr( get_the_title() ); ?>">
									<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
									<span>Tải Khung Chương Trình (PDF)</span>
								</a>
							</div>
						<?php endif; ?>
					</section>
				<?php endif; ?>


				<!-- SECTION 8.5: ADMISSION PROCESS -->
				<?php
				get_template_part(
					'template-parts/admission-process',
					null,
					[
						'title'      => 'Quy trình tuyển sinh & Nhập học',
						'subtitle'   => 'Lộ trình 4 bước xét tuyển từ đăng ký đến khi chính thức nhập học tại ' . ( $program_school_name ?: 'nhà trường' ),
						'section_id' => 'quy-trinh-tuyen-sinh',
						'cta_text'   => 'Đăng ký tư vấn lộ trình ngay',
						'cta_link'   => '#register',
					]
				);
				?>

				<!-- SECTION 9: FAQ -->
				<?php if ( ! empty( $faqs ) ) : ?>
					<section class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6" id="hoi-dap">
						<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Câu hỏi thường gặp</h2>
						<div class="space-y-4">
							<?php foreach ( $faqs as $index => $item ) : ?>
								<div class="border-b border-slate-100 pb-4 last:border-0 last:pb-0">
									<h4 class="font-semibold text-slate-800 text-base mb-1.5 flex items-start gap-2">
										<span class="bg-teal-100 text-teal-800 text-sm px-1.5 py-0.5 rounded-lg font-black">Q</span>
										<span><?php echo esc_html( $item['question'] ); ?></span>
									</h4>
									<p class="text-slate-600 text-sm pl-7 leading-relaxed"><?php echo esc_html( $item['answer'] ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION 9.5: CONTACT & ADMISSION OFFICE -->
				<?php
				$prog_contact_custom = get_field( 'contact_info', $program_id );
				$school_contact_raw  = $school_id ? get_field( 'contact_info', $school_id ) : '';
				$contact_html        = ! empty( $prog_contact_custom ) ? $prog_contact_custom : $school_contact_raw;
				$contact_formatted   = ltdh_format_contact_info( (string) $contact_html, (int) $school_id );
				$school_address      = $school_id ? ( get_post_meta( $school_id, 'address', true ) ?: get_field( 'address', $school_id ) ) : '';
				$school_website      = $school_id ? get_field( 'website', $school_id ) : '';
				?>
				<section id="thong-tin-lien-he" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6">
					<div class="border-b border-slate-100 pb-3 md:pb-4 mb-5">
						<h2 class="text-lg md:text-2xl font-bold text-slate-900">Thông tin liên hệ & Tiếp nhận hồ sơ</h2>
					</div>
					<!-- Detailed Venue, Office & Bank Info -->
					<?php if ( ! empty( $contact_formatted ) ) : ?>
						<div class="prose prose-slate max-w-none text-slate-700 text-sm leading-relaxed prose-card-list">
							<?php echo wp_kses_post( $contact_formatted ); ?>
						</div>
					<?php endif; ?>
				</section>


			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<?php get_sidebar( 'program' ); ?>
			</div>
		</div>

	</div>
</main>

<!-- SECTION 12: STICKY CTA (Mobile Bottom Sticky Bar) -->
<div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/80 shadow-[0_-4px_16px_rgba(0,0,0,0.06)] py-2.5 px-4 flex items-center justify-between lg:hidden">
	<div class="flex-1 mr-3 min-w-0">
		<span class="text-[10px] text-slate-400 block font-bold uppercase tracking-wider truncate mb-0.5"><?php echo esc_html( $school_title ); ?></span>
		<h4 class="font-bold text-slate-800 text-xs sm:text-sm truncate leading-tight"><?php the_title(); ?></h4>
	</div>
	<div class="flex items-center gap-2 shrink-0">
		<?php if ( ! empty( $form_url ) ) : ?>
			<a href="<?php echo esc_url( $form_url ); ?>" download target="_blank" rel="noopener noreferrer" class="ltdh-lead-magnet-btn bg-slate-100 hover:bg-slate-200 text-slate-700 p-2.5 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center justify-center min-h-[40px] cursor-pointer" title="Tải phiếu tuyển sinh" data-file-url="<?php echo esc_url( $form_url ); ?>" data-file-title="Phiếu tuyển sinh: <?php echo esc_attr( get_the_title() ); ?>">
				📄 <span class="hidden sm:inline ml-1">Tải phiếu</span>
			</a>
		<?php endif; ?>
		<a href="#register" class="bg-[#00308b] text-white px-4 py-2.5 rounded-lg text-xs font-extrabold shadow-sm hover:bg-[#002266] transition-all flex items-center justify-center shrink-0 min-h-[40px]">
			Đăng Ký Học
		</a>
	</div>
</div>

<!-- LEAD MAGNET MODAL COMPONENT -->
<div id="ltdh-lead-magnet-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
	<div class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
		<!-- Header -->
		<div class="bg-gradient-to-r from-[#00308b] to-[#002266] px-5 py-4 text-white flex items-center justify-between">
			<div class="flex items-center gap-2.5">
				<span class="text-xl">📥</span>
				<div>
					<h3 class="font-extrabold text-sm sm:text-base leading-tight">Tải Tài Liệu Tuyển Sinh</h3>
					<p class="text-[11px] text-blue-200 mt-0.5">Mẫu đơn đăng ký & Khung chương trình đào tạo</p>
				</div>
			</div>
			<button type="button" onclick="ltdhCloseLeadMagnetModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors cursor-pointer" aria-label="Đóng">
				<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
			</button>
		</div>

		<!-- Form Body -->
		<form id="ltdh-lead-magnet-form" class="p-5 sm:p-6 space-y-4" onsubmit="ltdhSubmitLeadMagnet(event)">
			<input type="hidden" name="action" value="ltdh_lead_magnet_download">
			<input type="hidden" name="security" value="<?php echo esc_attr( wp_create_nonce( 'ltdh_lead_magnet_nonce' ) ); ?>">
			<input type="hidden" name="program_id" value="<?php echo (int) $program_id; ?>">
			<input type="hidden" name="school_id" value="<?php echo (int) $school_id; ?>">
			<input type="hidden" name="major_id" value="<?php echo (int) $major_id; ?>">
			<input type="hidden" id="ltdh-lm-file-url" name="doc_url" value="">
			<input type="hidden" id="ltdh-lm-file-title" name="doc_title" value="">

			<!-- Honeypot -->
			<div class="hidden" style="display:none !important;" aria-hidden="true">
				<label for="lm_hp_website">Website URL</label>
				<input type="text" name="hp_website" id="lm_hp_website" tabindex="-1" autocomplete="off" value="">
			</div>

			<div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 flex items-start gap-2.5">
				<span class="text-base text-blue-600 shrink-0">📄</span>
				<div class="text-xs text-slate-700 min-w-0">
					<span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Tài liệu bạn chọn:</span>
					<strong id="ltdh-lm-doc-name-label" class="text-slate-900 truncate block">Khung chương trình đào tạo</strong>
				</div>
			</div>

			<div>
				<label class="block text-xs font-bold text-slate-700 mb-1">Họ và tên của bạn <span class="text-red-500">*</span></label>
				<input type="text" name="name" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#00308b] focus:ring-1 focus:ring-[#00308b] focus:outline-none" placeholder="Nguyễn Văn A">
			</div>

			<div>
				<label class="block text-xs font-bold text-slate-700 mb-1">Số điện thoại nhận tài liệu (Zalo) <span class="text-red-500">*</span></label>
				<input type="tel" name="phone" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-[#00308b] focus:ring-1 focus:ring-[#00308b] focus:outline-none" placeholder="0912 345 678">
				<p class="text-[11px] text-slate-400 mt-1">Hệ thống sẽ mở file trực tiếp và gửi bản dự phòng qua Zalo cho bạn.</p>
			</div>

			<div id="ltdh-lm-error" class="hidden text-xs text-red-600 bg-red-50 p-2.5 rounded-lg border border-red-200"></div>

			<button type="submit" id="ltdh-lm-submit-btn" class="w-full bg-[#00308b] hover:bg-[#002266] text-white py-3 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
				<span>Tải Tài Liệu Miễn Phí</span>
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
			</button>

			<p class="text-[10px] text-slate-400 text-center">
				🔒 Cam kết bảo mật thông tin 100% theo Nghị định 13/2023/NĐ-CP.
			</p>
		</form>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const modal          = document.getElementById('ltdh-lead-magnet-modal');
	const fileUrlInput   = document.getElementById('ltdh-lm-file-url');
	const fileTitleInput = document.getElementById('ltdh-lm-file-title');
	const docLabel       = document.getElementById('ltdh-lm-doc-name-label');
	const errorEl        = document.getElementById('ltdh-lm-error');
	const submitBtn      = document.getElementById('ltdh-lm-submit-btn');

	// Attach click handlers to all .ltdh-lead-magnet-btn elements
	document.querySelectorAll('.ltdh-lead-magnet-btn').forEach(btn => {
		btn.addEventListener('click', function(e) {
			const isUnlocked  = sessionStorage.getItem('ltdh_lead_magnet_unlocked');
			const targetUrl   = this.getAttribute('data-file-url') || this.getAttribute('href');
			const targetTitle = this.getAttribute('data-file-title') || 'Tài liệu tuyển sinh';

			if (isUnlocked === '1') {
				// Already unlocked in this session, allow default download
				return true;
			}

			// Intercept and open modal
			e.preventDefault();
			if (fileUrlInput) fileUrlInput.value = targetUrl;
			if (fileTitleInput) fileTitleInput.value = targetTitle;
			if (docLabel) docLabel.textContent = targetTitle;
			if (errorEl) {
				errorEl.textContent = '';
				errorEl.classList.add('hidden');
			}
			if (modal) {
				modal.classList.remove('hidden');
				modal.classList.add('flex');
				document.body.style.overflow = 'hidden';
			}
		});
	});

	window.ltdhCloseLeadMagnetModal = function() {
		if (modal) {
			modal.classList.add('hidden');
			modal.classList.remove('flex');
			document.body.style.overflow = '';
		}
	};

	window.ltdhSubmitLeadMagnet = function(e) {
		e.preventDefault();
		const form = document.getElementById('ltdh-lead-magnet-form');
		if (!form) return;

		const formData  = new FormData(form);
		const targetUrl = fileUrlInput ? fileUrlInput.value : '';

		if (submitBtn) {
			submitBtn.disabled = true;
			submitBtn.innerHTML = '<span>Đang gửi thông tin...</span>';
		}
		if (errorEl) errorEl.classList.add('hidden');

		fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
			method: 'POST',
			body: formData
		})
		.then(res => res.json())
		.then(data => {
			if (submitBtn) {
				submitBtn.disabled = false;
				submitBtn.innerHTML = '<span>Tải Tài Liệu Miễn Phí</span>';
			}

			if (data.success) {
				sessionStorage.setItem('ltdh_lead_magnet_unlocked', '1');
				ltdhCloseLeadMagnetModal();

				// Automatically trigger download or open file in new window
				if (targetUrl) {
					const win = window.open(targetUrl, '_blank');
					if (!win || win.closed || typeof win.closed === 'undefined') {
						window.location.href = targetUrl;
					}
				}
			} else {
				if (errorEl) {
					errorEl.textContent = data.data && data.data.message ? data.data.message : 'Có lỗi xảy ra, vui lòng thử lại.';
					errorEl.classList.remove('hidden');
				}
			}
		})
		.catch(err => {
			if (submitBtn) {
				submitBtn.disabled = false;
				submitBtn.innerHTML = '<span>Tải Tài Liệu Miễn Phí</span>';
			}
			if (errorEl) {
				errorEl.textContent = 'Lỗi kết nối mạng. Vui lòng thử lại sau.';
				errorEl.classList.remove('hidden');
			}
		});
	};
});
</script>

<!-- SCROLLSPY & SMOOTH SCROLL SCRIPT (UI/UX Pro Max) -->
<script>
(function() {
	document.addEventListener('DOMContentLoaded', function() {
		var stickyNav = document.getElementById('ltdh-program-sticky-nav');
		if (!stickyNav) return;

		var navContainer = stickyNav.querySelector('nav');
		var tabLinks = Array.prototype.slice.call(stickyNav.querySelectorAll('.ltdh-section-tab-link'));
		if (!tabLinks.length) return;

		var sectionMap = [];
		tabLinks.forEach(function(link) {
			var id = link.getAttribute('data-tab-target');
			var sec = document.getElementById(id);
			if (sec) {
				sectionMap.push({
					id: id,
					link: link,
					section: sec
				});
			}
		});

		if (!sectionMap.length) return;

		var isClickScrolling = false;
		var scrollTimeout = null;

		function getStickyOffset() {
			var header = document.getElementById('masthead');
			var headerHeight = header ? header.offsetHeight : 80;
			var navHeight = stickyNav.offsetHeight || 54;
			var adminBar = document.getElementById('wpadminbar');
			var adminBarHeight = (adminBar && window.getComputedStyle(adminBar).position === 'fixed') ? adminBar.offsetHeight : 0;
			return headerHeight + navHeight + adminBarHeight;
		}

		var currentActiveId = null;

		function setActiveTab(targetId, isFromUserClick) {
			if (targetId === currentActiveId && !isFromUserClick) return;
			currentActiveId = targetId;

			sectionMap.forEach(function(item) {
				var link = item.link;
				var iconEl = link.querySelector('.ltdh-tab-icon');
				var titleEl = link.querySelector('.ltdh-tab-title');
				var subEl = link.querySelector('.ltdh-tab-sub');
				var indicatorEl = link.querySelector('.ltdh-tab-indicator');

				if (item.id === targetId) {
					link.classList.add('bg-blue-50/90', 'text-[#00308b]', 'font-bold', 'shadow-2xs', 'is-active');
					link.classList.remove('text-slate-600', 'hover:bg-slate-100/70');
					if (iconEl) {
						iconEl.classList.add('bg-[#00308b]', 'text-white', 'shadow-xs', 'shadow-blue-900/20');
						iconEl.classList.remove('bg-slate-100', 'text-slate-500');
					}
					if (titleEl) {
						titleEl.classList.add('text-[#00308b]', 'font-extrabold');
						titleEl.classList.remove('text-slate-700');
					}
					if (subEl) {
						subEl.classList.add('text-blue-700/80', 'font-medium');
						subEl.classList.remove('text-slate-400');
					}
					if (indicatorEl) {
						indicatorEl.classList.add('bg-[#EA580C]', 'opacity-100');
						indicatorEl.classList.remove('bg-transparent', 'opacity-0');
					}

					if (navContainer && navContainer.scrollWidth > navContainer.clientWidth) {
						var tabLeft = link.offsetLeft;
						var tabWidth = link.offsetWidth;
						var containerWidth = navContainer.clientWidth;
						navContainer.scrollTo({
							left: tabLeft - (containerWidth / 2) + (tabWidth / 2),
							behavior: isFromUserClick ? 'smooth' : 'auto'
						});
					}
				} else {
					link.classList.remove('bg-blue-50/90', 'text-[#00308b]', 'font-bold', 'shadow-2xs', 'is-active');
					link.classList.add('text-slate-600', 'hover:bg-slate-100/70');
					if (iconEl) {
						iconEl.classList.remove('bg-[#00308b]', 'text-white', 'shadow-xs', 'shadow-blue-900/20');
						iconEl.classList.add('bg-slate-100', 'text-slate-500');
					}
					if (titleEl) {
						titleEl.classList.remove('text-[#00308b]', 'font-extrabold');
						titleEl.classList.add('text-slate-700');
					}
					if (subEl) {
						subEl.classList.remove('text-blue-700/80', 'font-medium');
						subEl.classList.add('text-slate-400');
					}
					if (indicatorEl) {
						indicatorEl.classList.remove('bg-[#EA580C]', 'opacity-100');
						indicatorEl.classList.add('bg-transparent', 'opacity-0');
					}
				}
			});
		}

		// Handle Click on Tabs
		tabLinks.forEach(function(link) {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				var targetId = this.getAttribute('data-tab-target');
				var targetItem = sectionMap.find(function(item) { return item.id === targetId; });
				if (!targetItem) return;

				isClickScrolling = true;
				clearTimeout(scrollTimeout);
				setActiveTab(targetId, true);

				var totalOffset = getStickyOffset();
				var elementPosition = targetItem.section.getBoundingClientRect().top + window.pageYOffset;
				var offsetPosition = elementPosition - totalOffset + 8; // gentle breathing room

				window.scrollTo({
					top: offsetPosition,
					behavior: 'smooth'
				});

				if (history.replaceState) {
					history.replaceState(null, '', '#' + targetId);
				}

				scrollTimeout = setTimeout(function() {
					isClickScrolling = false;
				}, 750);
			});
		});

		// Scrollspy with requestAnimationFrame
		var ticking = false;
		function onScroll() {
			if (isClickScrolling) return;

			var totalOffset = getStickyOffset();
			var scrollPos = window.pageYOffset + totalOffset + 50;
			var currentId = sectionMap[0].id;

			var atPageBottom = (window.innerHeight + window.pageYOffset) >= (document.documentElement.scrollHeight - 60);

			if (atPageBottom) {
				currentId = sectionMap[sectionMap.length - 1].id;
			} else {
				for (var i = 0; i < sectionMap.length; i++) {
					var item = sectionMap[i];
					var secTop = item.section.offsetTop;
					var secHeight = item.section.offsetHeight;
					if (scrollPos >= secTop && scrollPos < secTop + secHeight) {
						currentId = item.id;
						break;
					} else if (scrollPos >= secTop) {
						currentId = item.id;
					}
				}
			}

			if (currentId) {
				setActiveTab(currentId, false);
			}
		}

		window.addEventListener('scroll', function() {
			if (!ticking) {
				window.requestAnimationFrame(function() {
					onScroll();
					ticking = false;
				});
				ticking = true;
			}
		}, { passive: true });

		// Handle initial hash in URL
		if (window.location.hash) {
			var initialHash = window.location.hash.replace('#', '');
			var match = sectionMap.find(function(item) { return item.id === initialHash; });
			if (match) {
				setTimeout(function() {
					match.link.click();
				}, 250);
			} else {
				setTimeout(onScroll, 100);
			}
		} else {
			setTimeout(onScroll, 100);
		}
	});
})();
</script>

<?php
get_footer();
