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
$duration        = function_exists( 'ltdh_get_program_duration' ) ? ltdh_get_program_duration( $program_id ) : ( get_field( LTDH_META_DURATION, $program_id ) ?: '1.5 - 2 năm' );
$requirements    = function_exists( 'ltdh_get_program_admission_requirements' ) ? ltdh_get_program_admission_requirements( $program_id ) : ( get_field( 'admission_requirements', $program_id ) ?: get_post_meta( $program_id, 'admission_requirements', true ) );
$documents       = function_exists( 'ltdh_get_program_required_documents' ) ? ltdh_get_program_required_documents( $program_id ) : ( get_field( 'required_documents', $program_id ) ?: get_post_meta( $program_id, 'required_documents', true ) );
$enrollment      = ltdh_get_program_admission_deadline_display( $program_id );
$quota           = get_field( 'quota', $program_id );
$admission_fee   = function_exists( 'ltdh_get_program_admission_fee' ) ? ltdh_get_program_admission_fee( $program_id ) : ( get_field( 'admission_fee', $program_id ) ?: get_post_meta( $program_id, 'admission_fee', true ) );

$exemptions_data = function_exists( 'ltdh_get_program_exemptions' ) ? ltdh_get_program_exemptions( $program_id ) : [ 'title' => '', 'intro' => '', 'items' => [], 'inherited' => false ];
$exemption_title = ! empty( $exemptions_data['title'] ) ? $exemptions_data['title'] : 'Quy định miễn môn & Chuyển đổi tín chỉ';
$exemption_intro = ! empty( $exemptions_data['intro'] ) ? $exemptions_data['intro'] : '';
$exemption_items = ! empty( $exemptions_data['items'] ) ? $exemptions_data['items'] : [];


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
$faqs            = function_exists( 'ltdh_get_program_faqs' ) ? ltdh_get_program_faqs( $program_id ) : get_field( 'faq', $program_id );

$prog_contact_custom = get_field( 'contact_info', $program_id );
$school_contact_raw  = $school_id ? get_field( 'contact_info', $school_id ) : '';
$contact_html        = ! empty( $prog_contact_custom ) ? $prog_contact_custom : $school_contact_raw;
$contact_formatted   = function_exists( 'ltdh_format_contact_info' ) ? ltdh_format_contact_info( (string) $contact_html, (int) $school_id ) : (string) $contact_html;

$curriculum_info  = function_exists( 'ltdh_get_program_curriculum_info' ) ? ltdh_get_program_curriculum_info( $program_id ) : null;
$curriculum_url   = $curriculum_info['url'] ?? '';
$curriculum_type  = $curriculum_info['type'] ?? 'file';
$curriculum_title = $curriculum_info['title'] ?? ( 'Khung chương trình: ' . get_the_title( $program_id ) );

$global_zalo = ltdh_get_zalo_url();

// Dynamically construct sticky navigation tabs for Program (UI/UX Pro Max)
$admission_batches_list   = function_exists( 'ltdh_get_program_admission_batches' ) ? ltdh_get_program_admission_batches( $program_id ) : get_field( 'admission_batches', $program_id );
$admission_form_data      = function_exists( 'ltdh_get_program_admission_form_data' ) ? ltdh_get_program_admission_form_data( $program_id ) : null;
$admission_form_file      = $admission_form_data['url'] ?? '';
$program_target_admission = function_exists( 'ltdh_get_program_target_admission' ) ? ltdh_get_program_target_admission( $program_id ) : null;

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

if ( ! empty( $requirements ) || ! empty( $documents ) || ! empty( $admission_form_file ) || ! empty( $contact_formatted ) ) {
	$program_tabs[] = [
		'id'       => 'dieu-kien-ho-so',
		'title'    => 'Điều kiện & Hồ sơ',
		'subtitle' => 'Tiêu chuẩn & Tiếp nhận',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>',
	];
}

if ( ! empty( $program_target_admission ) ) {
	$program_tabs[] = [
		'id'       => 'doi-tuong-tuyen-sinh',
		'title'    => 'Đối tượng tuyển sinh',
		'subtitle' => 'Ngành đúng & ngành gần',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>',
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
	'subtitle' => 'Lộ trình 4 bước',
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
				$admission_status = function_exists( 'ltdh_get_program_admission_status' ) 
					? ltdh_get_program_admission_status( $program_id ) 
					: ( get_post_meta( $program_id, 'admission_status', true ) ?: 'tuyen-sinh' );

				if ( $admission_status === 'tam-ngung' ) :
				?>
					<div class="bg-red-50 text-red-800 rounded-2xl p-4 flex items-start gap-3 shadow-2xs">
						<svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
						<div>
							<p class="font-bold text-red-900 text-sm">Đã hết chỉ tiêu tuyển sinh năm nay</p>
							<p class="text-xs text-red-700 mt-1">Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này. Quý học viên vui lòng tham khảo các chương trình liên quan hoặc để lại thông tin đăng ký tư vấn để được hướng dẫn lộ trình phù hợp.</p>
						</div>
					</div>
				<?php elseif ( $admission_status === 'sap-mo' ) : ?>
					<div class="bg-blue-50 text-blue-800 rounded-2xl p-4 flex items-start gap-3 shadow-2xs">
						<svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
						<div>
							<p class="font-bold text-blue-900 text-sm">Chương trình chuẩn bị mở tuyển sinh đợt mới</p>
							<p class="text-xs text-blue-700 mt-1">Đợt tuyển sinh tiếp theo đang chuẩn bị mở tiếp nhận hồ sơ. Quý học viên có thể để lại thông tin để nhận thông báo sớm nhất ngay khi mở cổng đăng ký.</p>
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
									<p class="font-extrabold text-slate-800 text-xs truncate leading-tight"><?php echo esc_html( $school_title ); ?></p>
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


					<?php
					$learning_details = ltdh_get_program_learning_details( $program_id );
					$tuition_year     = get_field( 'tuition_academic_year', $program_id );
					$degree_val       = function_exists( 'ltdh_get_program_degree_type' ) 
						? ltdh_get_program_degree_type( $program_id ) 
						: ( get_field( 'degree_type', $program_id ) ?: get_post_meta( $program_id, 'degree_type', true ) ?: 'Cử nhân / Kỹ sư' );
					$diploma_val      = function_exists( 'ltdh_get_program_diploma_value' )
						? ltdh_get_program_diploma_value( $program_id )
						: ( get_field( 'diploma_value', $program_id ) ?: '' );
					$prog_clean_name   = get_the_title( $program_id );
					$prog_school_name  = $school_title ?: 'trường đối tác';
					$prog_mode_name    = ! empty( $learning_details['mode'] ) ? $learning_details['mode'] : 'Liên thông Đại học';
					$prog_dur_name     = $duration ?: '1.5 – 2.0 năm';
					$prog_fee_name     = $tuition ?: 'Theo thông báo tuyển sinh';
					?>

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

					<!-- Semantic HTML Table for Search Engine Snippets & AI LLM Crawlers -->
					<table class="sr-only" aria-label="Bảng tóm tắt thông số chương trình tuyển sinh">
						<caption>Thông số kỹ thuật chương trình tuyển sinh Liên thông</caption>
						<tbody>
							<tr><th>Trường đào tạo</th><td><?php echo esc_html( $prog_school_name ); ?></td></tr>
							<tr><th>Chuyên ngành</th><td><?php echo esc_html( $clean_major ?: $prog_clean_name ); ?></td></tr>
							<tr><th>Hình thức đào tạo</th><td><?php echo esc_html( $prog_mode_name ); ?></td></tr>
							<tr><th>Đối tượng đầu vào</th><td>Trung cấp / Cao đẳng</td></tr>
							<tr><th>Thời gian đào tạo</th><td><?php echo esc_html( $prog_dur_name ); ?></td></tr>
							<tr><th>Mức học phí</th><td><?php echo esc_html( $prog_fee_name ); ?></td></tr>
							<tr><th>Danh hiệu văn bằng</th><td><?php echo esc_html( $degree_val ); ?></td></tr>
							<tr><th>Hạn nộp hồ sơ</th><td><?php echo esc_html( $enrollment ?: 'Đang nhận hồ sơ' ); ?></td></tr>
						</tbody>
					</table>

					<!-- Thông tin pháp lý văn bằng & Giá trị toàn quốc (Thông tư 27/2019/TT-BGDĐT) -->
					<div class="mt-4 bg-emerald-50/50 rounded-xl p-4 text-xs md:text-sm text-slate-700 space-y-2 leading-relaxed">
						<?php if ( ! empty( $diploma_val ) ) : ?>
							<p class="flex items-start gap-2">
								<span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
								<span><strong>Quy định cấp bằng & Giá trị pháp lý:</strong> <?php echo nl2br( esc_html( $diploma_val ) ); ?></span>
							</p>
						<?php else : ?>
							<p class="flex items-start gap-2">
								<span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
								<span><strong>Không ghi hình thức đào tạo:</strong> Theo quy định tại Thông tư 27/2019/TT-BGDĐT của Bộ GD&ĐT, văn bằng đại học hiện hành không ghi hình thức đào tạo (không phân biệt Chính quy, Vừa làm vừa học hay Đào tạo từ xa).</span>
							</p>
						<?php endif; ?>
						<p class="flex items-start gap-2">
							<span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
							<span><strong>Giá trị sử dụng toàn quốc:</strong> Bằng có đầy đủ giá trị pháp lý để thi tuyển công chức, viên chức, xét nâng lương ngạch bậc và học tiếp lên Thạc sĩ, Tiến sĩ tại tất cả các cơ sở đào tạo trong và ngoài nước.</span>
						</p>
					</div>
				</section>

				<!-- SECTION: ADMISSION BATCHES -->
				<?php 
				$batches_data = function_exists( 'ltdh_get_program_admission_batches' ) ? ltdh_get_program_admission_batches( $program_id ) : get_field( 'admission_batches', $program_id );
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
										<h3 class="<?php echo $is_open ? 'text-sm font-bold text-slate-900' : 'text-sm font-bold text-slate-700'; ?>">
											<?php echo esc_html( $b_data['title'] ); ?>
										</h3>
										
									</div>

									<!-- 2. Các mốc thời gian trải rộng ở giữa (Theo đúng quy trình: Hạn nộp -> Xét tuyển/Thi tuyển -> Khai giảng) -->
									<div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-6 lg:gap-8">
										
										<!-- Cột 1: Hạn nộp hồ sơ -->
										<?php 
										$batch_app_text = $b_data['app_period'];
										if ( empty( $batch_app_text ) && $is_open && ! empty( $enrollment ) ) {
											$batch_app_text = $enrollment;
										}
										?>
										<?php if ( ! empty( $batch_app_text ) ) : ?>
											<div>
												<span class="text-xs font-semibold text-slate-500 block mb-1">Hạn nộp hồ sơ</span>
												<span class="<?php echo $is_open ? 'text-sm font-bold text-amber-950' : 'text-sm font-semibold text-slate-800'; ?> block leading-snug">
													<?php echo esc_html( $batch_app_text ); ?>
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
				$admission_package   = function_exists( 'ltdh_get_program_admission_package' ) ? ltdh_get_program_admission_package( $program_id ) : null;
				$admission_form_data = function_exists( 'ltdh_get_program_admission_form_data' ) ? ltdh_get_program_admission_form_data( $program_id ) : null;
				$form_url            = $admission_form_data['url'] ?? ( $admission_package['primary_url'] ?? '' );
				$form_title          = $admission_form_data['title'] ?? ( $admission_package['primary_title'] ?? ( 'Phiếu tuyển sinh: ' . get_the_title( $program_id ) ) );
				$form_badge          = $admission_form_data['source_label'] ?? ( $admission_package['source_label'] ?? '' );
				$has_package         = ! empty( $admission_package['has_package'] ) || ! empty( $form_url );
				?>
				<?php if ( $requirements || $documents || $has_package || ! empty( $contact_formatted ) ) : ?>
					<section id="dieu-kien-ho-so" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6">
						<!-- Hidden anchor targets for backward compatibility with existing links -->
						<span id="dieu-kien-xet-tuyen" class="relative -top-36 block invisible pointer-events-none"></span>
						<span id="ho-so-can-nop" class="relative -top-36 block invisible pointer-events-none"></span>
						<span id="thong-tin-lien-he" class="relative -top-36 block invisible pointer-events-none"></span>

						<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3 mb-2">
							<div>
								<h2 class="text-lg md:text-2xl font-bold text-slate-900">Điều kiện & Hồ sơ xét tuyển</h2>
								<p class="text-xs md:text-sm text-slate-500 mt-0.5">Tiêu chuẩn tuyển sinh, hồ sơ cần chuẩn bị và địa điểm tiếp nhận hồ sơ nhập học</p>
							</div>
							<div class="flex items-center gap-2 shrink-0 self-start sm:self-center">
								<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-full">
									<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
									Xét tuyển hồ sơ
								</span>
								<button type="button" 
										id="ltdh-toggle-req-docs-btn" 
										class="inline-flex items-center gap-1 text-xs font-bold text-[#00308b] hover:text-blue-900 hover:bg-blue-50/80 px-2.5 py-1 rounded-lg transition-all cursor-pointer select-none"
										title="Mở hoặc thu gọn toàn bộ nội dung">
									<span id="ltdh-toggle-req-docs-text">Mở tất cả</span>
									<svg id="ltdh-toggle-req-docs-icon" class="w-3.5 h-3.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
										<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
									</svg>
								</button>
							</div>
						</div>

						<!-- Collapsible Accordion Group (UI/UX Pro Max - Borderless divide-y) -->
						<div class="divide-y divide-slate-100" id="ltdh-req-docs-accordion">
							<?php
							$req_step = 0;
							?>

							<?php if ( $requirements ) : 
								$req_step++;
							?>
								<!-- BLOCK 1: ADMISSION REQUIREMENTS (Collapsible Details) -->
								<details id="details-dieu-kien" class="group py-3.5 sm:py-4 transition-colors">
									<summary class="flex items-center justify-between gap-3 cursor-pointer select-none font-bold text-slate-800 text-sm md:text-[15px] list-none hover:text-[#00308b] transition-colors">
										<div class="flex items-center gap-3 min-w-0">
											<span class="w-7 h-7 rounded-lg bg-blue-50 text-[#00308b] group-open:bg-[#00308b] group-open:text-white flex items-center justify-center text-xs font-black shrink-0 transition-colors shadow-2xs">
												<?php echo $req_step; ?>
											</span>
											<div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
												<span class="leading-snug text-slate-800 group-open:text-[#00308b] transition-colors font-bold">
													Điều kiện xét tuyển & Đối tượng áp dụng
												</span>
											</div>
										</div>
										<div class="flex items-center gap-2 shrink-0">
											<span class="text-xs text-slate-400 group-open:hidden hidden sm:inline font-medium">Bấm xem</span>
											<svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 group-open:rotate-180 group-open:text-[#00308b] transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
												<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
											</svg>
										</div>
									</summary>
									<div class="mt-3 pl-0 sm:pl-10 text-xs md:text-sm text-slate-700 leading-relaxed">
										<div class="prose prose-slate max-w-none text-slate-700 text-sm md:text-base leading-relaxed bg-slate-50/70 p-4 md:p-5 rounded-xl border border-slate-100/80">
											<?php echo wp_kses_post( $requirements ); ?>
										</div>
									</div>
								</details>
							<?php endif; ?>

							<?php if ( $documents || $has_package ) : 
								$req_step++;
							?>
								<!-- BLOCK 2: REQUIRED DOCUMENTS & ADMISSION FORM DOWNLOAD (Collapsible Details) -->
								<details id="details-ho-so" class="group py-3.5 sm:py-4 transition-colors">
									<summary class="flex items-center justify-between gap-3 cursor-pointer select-none font-bold text-slate-800 text-sm md:text-[15px] list-none hover:text-emerald-700 transition-colors">
										<div class="flex items-center gap-3 min-w-0">
											<span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-800 group-open:bg-emerald-600 group-open:text-white flex items-center justify-center text-xs font-black shrink-0 transition-colors shadow-2xs">
												<?php echo $req_step; ?>
											</span>
											<div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
												<span class="leading-snug text-slate-800 group-open:text-emerald-800 transition-colors font-bold">
													Hồ sơ xét tuyển cần chuẩn bị
												</span>
											</div>
										</div>
										<div class="flex items-center gap-2 shrink-0">
											<span class="text-xs text-slate-400 group-open:hidden hidden sm:inline font-medium">Bấm xem</span>
											<svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 group-open:rotate-180 group-open:text-emerald-700 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
												<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
											</svg>
										</div>
									</summary>
									<div class="mt-3 pl-0 sm:pl-10 text-xs md:text-sm text-slate-700 leading-relaxed space-y-3.5">
										<?php if ( $documents ) : ?>
											<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base bg-slate-50/70 p-4 rounded-xl border border-slate-100/80">
												<?php echo wp_kses_post( $documents ); ?>
											</div>
										<?php endif; ?>

										<?php if ( ! empty( $admission_package['has_package'] ) ) : 
											$drive_target_url  = ! empty( $admission_package['drive_url'] ) ? $admission_package['drive_url'] : ( $admission_package['primary_url'] ?? $form_url );
											$is_drive_action   = ! empty( $admission_package['drive_url'] ) || ( function_exists( 'ltdh_is_google_drive_url' ) && ltdh_is_google_drive_url( $drive_target_url ) );
											$card_badge        = $admission_package['source_label'] ?: 'Hồ sơ chính thức';
											$school_drive_url  = $admission_package['school_drive_url'] ?? '';
											$has_general_drive = ! empty( $school_drive_url ) && ( $school_drive_url !== $drive_target_url );
											$school_title_text = ! empty( $admission_package['school_title'] ) ? $admission_package['school_title'] : ( $school_title ?? 'Nhà trường' );
										?>
											<!-- GOOGLE DRIVE ADMISSION PACKAGE CARD -->
											<div class="relative overflow-hidden bg-gradient-to-br from-emerald-50/70 via-white to-blue-50/50 rounded-2xl border border-emerald-100/90 p-4 sm:p-5 mt-4 shadow-2xs transition-all hover:border-emerald-200">
												<div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
													<div class="flex items-start gap-3.5 min-w-0">
														<!-- Google Drive Brand Icon Container -->
														<div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white flex items-center justify-center font-bold shrink-0 shadow-xs ring-4 ring-emerald-50">
															<svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
																<path d="M7.71 3.5L1.15 15l3.43 6 6.55-11.5L7.71 3.5zm3.43 6l3.43 6h8.28l-3.43-6h-8.28zm8.71 7.5l3.43-6-6.57-11.5h-6.86l6.57 11.5 3.43 6z" opacity="0.25"/>
																<path d="M19.85 13.5L16.42 7.5H9.57l3.43 6h6.85z" fill="#FFC107"/>
																<path d="M7.71 3.5L1.15 15l3.43 6 3.43-6-3.43-6h3.13z" fill="#0066DA"/>
																<path d="M8.28 17.5l-3.43 3.5h14.57l3.43-3.5H8.28z" fill="#00AC47"/>
																<path d="M14.71 14.5l-3.43-6-3.43 6h6.86z" fill="#EA4335"/>
															</svg>
														</div>
														<div class="min-w-0">
															<div class="flex flex-wrap items-center gap-2 mb-1">
																<h4 class="font-extrabold text-slate-900 text-sm sm:text-base leading-snug">
																	Bộ biểu mẫu & Hồ sơ tuyển sinh chính thức
																</h4>
																<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 shrink-0">
																	Google Drive
																</span>
																<?php if ( ! empty( $card_badge ) ) : ?>
																	<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 text-blue-800 shrink-0">
																		<?php echo esc_html( $card_badge ); ?>
																	</span>
																<?php endif; ?>
															</div>
															<p class="text-xs text-slate-600 leading-relaxed">
																<?php if ( $has_general_drive ) : ?>
																	Bao gồm phiếu đăng ký dự thi, sơ yếu lý lịch và các biểu mẫu tuyển sinh. Bạn có thể mở trực tiếp hồ sơ theo hệ/ngành hoặc truy cập thư mục chung của nhà trường trên Google Drive.
																<?php else : ?>
																	Bao gồm phiếu đăng ký dự thi, sơ yếu lý lịch và các đơn xét miễn giảm môn. Xem trước, tải về hoặc in trực tiếp trên Google Drive<?php echo ! empty( $school_title_text ) ? ' của ' . esc_html( $school_title_text ) : ''; ?>.
																<?php endif; ?>
															</p>
														</div>
													</div>

													<!-- Action Buttons -->
													<?php if ( $has_general_drive ) : ?>
														<div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0 w-full lg:w-auto">
															<a href="<?php echo esc_url( $drive_target_url ); ?>" <?php echo $is_drive_action ? '' : 'download'; ?> target="_blank" rel="noopener noreferrer" class="ltdh-lead-magnet-btn inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs hover:shadow-md transition-all shrink-0 cursor-pointer group" data-file-url="<?php echo esc_url( $drive_target_url ); ?>" data-file-title="Bộ biểu mẫu & Hồ sơ tuyển sinh: <?php echo esc_attr( get_the_title( $program_id ) ); ?>">
																<span><?php echo ( ( $admission_package['source'] ?? '' ) === 'school_type' ) ? 'Mở hồ sơ theo hệ' : 'Mở hồ sơ chương trình'; ?></span>
																<svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
															</a>
															<a href="<?php echo esc_url( $school_drive_url ); ?>" target="_blank" rel="noopener noreferrer" class="ltdh-lead-magnet-btn inline-flex items-center justify-center gap-2 px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border border-slate-200 hover:border-slate-300 text-xs sm:text-sm font-bold rounded-xl transition-all shrink-0 cursor-pointer group shadow-2xs" data-file-url="<?php echo esc_url( $school_drive_url ); ?>" data-file-title="Thư mục hồ sơ chung: <?php echo esc_attr( $school_title_text ); ?>">
																<svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
																<span>Thư mục chung toàn trường</span>
																<svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
															</a>
														</div>
													<?php else : ?>
														<a href="<?php echo esc_url( $drive_target_url ); ?>" <?php echo $is_drive_action ? '' : 'download'; ?> target="_blank" rel="noopener noreferrer" class="ltdh-lead-magnet-btn inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs hover:shadow-md transition-all shrink-0 cursor-pointer self-stretch sm:self-auto group" data-file-url="<?php echo esc_url( $drive_target_url ); ?>" data-file-title="Bộ biểu mẫu & Hồ sơ tuyển sinh: <?php echo esc_attr( get_the_title( $program_id ) ); ?>">
															<span>Mở trên Google Drive</span>
															<svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
														</a>
													<?php endif; ?>
												</div>
											</div>
										<?php endif; ?>
									</div>
								</details>
							<?php endif; ?>

							<?php if ( ! empty( $contact_formatted ) ) : 
								$req_step++;
							?>
								<!-- BLOCK 3: CONTACT & ADMISSION VENUE (Collapsible Details) -->
								<details id="details-lien-he" class="group py-3.5 sm:py-4 transition-colors">
									<summary class="flex items-center justify-between gap-3 cursor-pointer select-none font-bold text-slate-800 text-sm md:text-[15px] list-none hover:text-amber-700 transition-colors">
										<div class="flex items-center gap-3 min-w-0">
											<span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-800 group-open:bg-amber-600 group-open:text-white flex items-center justify-center text-xs font-black shrink-0 transition-colors shadow-2xs">
												<?php echo $req_step; ?>
											</span>
											<div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
												<span class="leading-snug text-slate-800 group-open:text-amber-800 transition-colors font-bold">
													Thông tin liên hệ & Tiếp nhận hồ sơ
												</span>
											</div>
										</div>
										<div class="flex items-center gap-2 shrink-0">
											<span class="text-xs text-slate-400 group-open:hidden hidden sm:inline font-medium">Bấm xem</span>
											<svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 group-open:rotate-180 group-open:text-amber-700 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
												<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
											</svg>
										</div>
									</summary>
									<div class="mt-3 pl-0 sm:pl-10 text-xs md:text-sm text-slate-700 leading-relaxed">
										<div class="prose prose-slate max-w-none text-slate-700 text-sm leading-relaxed prose-card-list bg-slate-50/70 p-4 md:p-5 rounded-xl border border-slate-100/80">
											<?php echo wp_kses_post( $contact_formatted ); ?>
										</div>
									</div>
								</details>
							<?php endif; ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION: ĐỐI TƯỢNG TUYỂN SINH (NGÀNH ĐÚNG & NGÀNH GẦN) -->
				<?php if ( ! empty( $program_target_admission ) ) : 
					$t_item = $program_target_admission;
				?>
					<section id="doi-tuong-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6">
						<div class="border-b border-slate-100 pb-3 mb-5">
							<h2 class="text-xl md:text-2xl font-black text-slate-900 leading-tight">
								Đối tượng tuyển sinh: Ngành đúng & Ngành gần
							</h2>
							<p class="text-xs md:text-sm text-slate-500 mt-1">
								Quy định phân loại văn bằng tốt nghiệp Cao đẳng và điều kiện học phần bổ sung của <?php echo esc_html( $t_item['school_title'] ); ?>
							</p>
						</div>

						<div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-2xs">
							<!-- Header thẻ quy định của trường -->
							<div class="bg-slate-50/90 px-4 py-3 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
								<div class="flex items-center gap-2">
									<span class="w-6 h-6 rounded-lg bg-[#00308b] text-white flex items-center justify-center font-bold text-xs shrink-0">
										🏛️
									</span>
									<h3 class="font-bold text-slate-900 text-sm md:text-base">
										<?php echo esc_html( $t_item['school_title'] ); ?>
									</h3>
								</div>
								<span class="text-xs font-semibold text-slate-600 bg-white px-2.5 py-1 rounded-md border border-slate-200">
									<?php echo esc_html( $t_item['regulation_title'] ); ?>
								</span>
							</div>

							<!-- BẢNG 2 CỘT CHUẨN: CHỈ CÓ NGÀNH ĐÚNG VS NGÀNH GẦN (KHÔNG STT, KHÔNG CỘT NGÀNH DỰ TUYỂN) -->
							<div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-200">
								
								<!-- CỘT 1: NHÓM NGÀNH ĐÚNG -->
								<div class="p-4 sm:p-5 bg-emerald-50/15 flex flex-col justify-between">
									<div>
										<div class="flex items-center justify-between gap-2 border-b border-emerald-100 pb-3 mb-3.5">
											<div class="flex items-center gap-2">
												<span class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs shrink-0">✓</span>
												<h4 class="font-bold text-slate-900 text-sm md:text-base">Nhóm Ngành Đúng</h4>
											</div>
											<span class="px-2.5 py-1 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-lg border border-emerald-200">
												Không học bổ sung
											</span>
										</div>

										<p class="text-xs text-slate-600 mb-2.5 font-medium">Thí sinh tốt nghiệp Cao đẳng các chuyên ngành:</p>
										<?php if ( ! empty( $t_item['proper_majors'] ) ) : ?>
											<ul class="space-y-2 pl-4 list-disc text-slate-800 text-xs sm:text-sm mb-4">
												<?php foreach ( $t_item['proper_majors'] as $p_major ) : ?>
													<li><span class="font-semibold text-slate-900"><?php echo esc_html( $p_major ); ?></span></li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</div>

									<div class="mt-4 pt-3 border-t border-emerald-200/60 text-xs text-emerald-800 font-semibold bg-emerald-50/70 p-2.5 rounded-lg leading-relaxed">
										🎉 Vào học thẳng môn chuyên ngành. Thời gian đào tạo tiêu chuẩn (<?php echo esc_html( $duration ); ?>), không phải học bổ sung môn chuyển đổi.
									</div>
								</div>

								<!-- CỘT 2: NHÓM NGÀNH GẦN -->
								<div class="p-4 sm:p-5 bg-amber-50/15 flex flex-col justify-between">
									<div>
										<div class="flex items-center justify-between gap-2 border-b border-amber-100 pb-3 mb-3.5">
											<div class="flex items-center gap-2">
												<span class="w-6 h-6 rounded-md bg-amber-100 text-amber-900 flex items-center justify-center font-bold text-xs shrink-0">⚡</span>
												<h4 class="font-bold text-slate-900 text-sm md:text-base">Nhóm Ngành Gần</h4>
											</div>
											<span class="px-2.5 py-1 bg-amber-50 text-amber-900 text-xs font-bold rounded-lg border border-amber-200">
												+ <?php echo sprintf( '%02d', (int) ( $t_item['bridge_count'] ?: 3 ) ); ?> Học phần bổ sung
											</span>
										</div>

										<p class="text-xs text-slate-600 mb-2.5 font-medium">Thí sinh tốt nghiệp Cao đẳng các ngành:</p>
										<?php if ( ! empty( $t_item['near_majors'] ) ) : ?>
											<p class="text-xs sm:text-sm text-slate-800 leading-relaxed mb-4">
												<?php echo esc_html( implode( '; ', $t_item['near_majors'] ) ); ?>.
											</p>
										<?php endif; ?>
									</div>

									<div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-950 leading-relaxed font-medium">
										⚠️ <strong>Quy định bắt buộc:</strong> <?php echo esc_html( $t_item['bridge_note'] ); ?>
									</div>
								</div>

							</div>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION 6: TUITION & SECTION 7: DURATION -->
				<section id="hoc-phi-thoi-gian" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-base sm:text-xl md:text-2xl font-bold text-slate-900 mb-3 sm:mb-4">Học phí & Thời gian học</h2>
					
					<!-- 1 Hàng trên cả Mobile và Desktop: 2 Cột song song -->
					<div class="grid grid-cols-2 gap-2.5 sm:gap-4 md:gap-6">
						<!-- Card 1: Học phí -->
						<div class="bg-blue-50/50 rounded-xl sm:rounded-2xl p-3 sm:p-5 flex flex-col justify-between">
							<div>
								<div class="flex items-center gap-1.5 text-blue-700 mb-1 sm:mb-2">
									<svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
									<span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-600">Đơn giá học phí</span>
								</div>
								
								<div class="text-sm sm:text-lg md:text-2xl font-black text-slate-900 leading-snug">
									<?php echo esc_html( $tuition ?: 'Liên hệ ban tuyển sinh' ); ?>
								</div>
								
								<?php if ( isset($tuition_year) && $tuition_year ) : ?>
									<span class="text-[10px] sm:text-xs text-slate-500 font-medium block mt-0.5">(Năm học <?php echo esc_html( $tuition_year ); ?>)</span>
								<?php endif; ?>
							</div>

							<?php 
							if ( ! empty( $admission_fee ) || ( is_numeric( $admission_fee ) && (int) $admission_fee === 0 ) ) : 
								$fee_info = function_exists( 'ltdh_format_admission_fee' ) ? ltdh_format_admission_fee( $admission_fee ) : [ 'display' => esc_html( $admission_fee ) . ' / hồ sơ', 'is_free' => false ];
							?>
								<div class="mt-2.5 pt-2 border-t border-blue-100/60 flex flex-wrap items-center justify-between text-[10px] sm:text-xs gap-1">
									<span class="text-slate-500">Lệ phí xét:</span>
									<span class="font-bold <?php echo ! empty( $fee_info['is_free'] ) ? 'text-emerald-600' : 'text-slate-800'; ?>">
										<?php echo esc_html( $fee_info['display'] ); ?>
									</span>
								</div>
							<?php endif; ?>
						</div>

						<!-- Card 2: Thời gian học tập -->
						<div class="bg-amber-50/50 rounded-xl sm:rounded-2xl p-3 sm:p-5 flex flex-col justify-between">
							<div>
								<div class="flex items-center gap-1.5 text-amber-700 mb-1 sm:mb-2">
									<svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
									<span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-600">Thời gian học</span>
								</div>
								
								<div class="text-sm sm:text-xl md:text-2xl font-black text-slate-900 leading-snug">
									<?php echo esc_html( $duration ?: '2.0 - 3.0 năm' ); ?>
								</div>
								
								<span class="text-[10px] sm:text-xs text-slate-500 font-medium block mt-0.5">Lộ trình chuẩn</span>
							</div>

							<div class="mt-2.5 pt-2 border-t border-amber-100/60 flex flex-wrap items-center justify-between text-[10px] sm:text-xs gap-1 text-slate-500">
								<span>Hình thức:</span>
								<span class="font-bold text-slate-800">Trực tuyến / Từ xa</span>
							</div>
						</div>
					</div>

					<!-- Ghi chú & Lộ trình học phí (Gọn gàng, loại bỏ các khối border lồng nhau) -->
					<?php 
					$increase_roadmap = function_exists( 'ltdh_get_program_tuition_roadmap' ) 
						? ltdh_get_program_tuition_roadmap( $program_id ) 
						: ( get_field( 'tuition_increase_roadmap', $program_id ) ?: ( $school_id ? get_field( 'tuition_increase_roadmap', $school_id ) : '' ) );
					?>
					<div class="mt-3 bg-slate-50/70 rounded-xl p-3 sm:p-3.5 space-y-1.5 text-xs text-slate-600 leading-relaxed">
						<?php if ( $increase_roadmap ) : ?>
							<p class="flex items-start gap-1.5">
								<span class="text-blue-600 shrink-0">💡</span>
								<span><strong class="text-slate-800">Lộ trình học phí:</strong> <?php echo esc_html( $increase_roadmap ); ?></span>
							</p>
						<?php endif; ?>
						<p class="flex items-start gap-1.5">
							<span class="text-amber-500 shrink-0">📌</span>
							<span><strong class="text-slate-800">Lưu ý xét tuyển:</strong> Quy định miễn trừ học phần, chuyển đổi tín chỉ và số tín chỉ thực tế cần hoàn thành sẽ được Nhà trường thẩm định cụ thể dựa trên bảng điểm tốt nghiệp của từng học viên.</span>
						</p>
					</div>
					<?php 
					if ( ! empty( $exemption_items ) && is_array( $exemption_items ) ) :
					?>
						<div class="mt-5 border-t border-slate-100 pt-5">
							<div class="bg-blue-50/40 rounded-2xl p-5">
								<div class="flex items-center gap-2 border-b border-blue-100/60 pb-3 mb-3">
									<svg class="w-6 h-6 text-blue-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222M12 14v8"></path></svg>
									<h3 class="font-extrabold text-blue-950 text-base md:text-lg">
										<?php echo esc_html( $exemption_title ?: 'Quy định miễn môn & Chuyển đổi tín chỉ' ); ?>
									</h3>
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

											<?php 
											$exemption_target_url = ! empty( $item['form_url'] ) ? $item['form_url'] : ( $item['file_url'] ?? '' );
											if ( ! empty( $exemption_target_url ) ) :
												$is_ex_drive = function_exists( 'ltdh_is_google_drive_url' ) && ltdh_is_google_drive_url( $exemption_target_url );
											?>
												<div class="mt-3 pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
													<div class="flex items-center gap-2 min-w-0">
														<span class="w-6 h-6 rounded-md <?php echo $is_ex_drive ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-700'; ?> flex items-center justify-center text-[10px] font-black shrink-0">
															<?php echo $is_ex_drive ? 'DRIVE' : 'PDF'; ?>
														</span>
														<span class="text-xs text-slate-600 truncate">
															Mẫu đơn xét miễn môn: <strong class="text-slate-800"><?php echo esc_html( $item['subject_name'] ?? 'Môn học' ); ?></strong>
														</span>
													</div>
													<a href="<?php echo esc_url( $exemption_target_url ); ?>" <?php echo $is_ex_drive ? '' : 'download'; ?> target="_blank" rel="noopener noreferrer" class="ltdh-lead-magnet-btn inline-flex items-center gap-1.5 px-3 py-1.5 <?php echo $is_ex_drive ? 'bg-emerald-50 hover:bg-emerald-600 text-emerald-900 hover:text-white border border-emerald-200/80 hover:border-emerald-600' : 'bg-amber-50 hover:bg-amber-600 text-amber-900 hover:text-white border border-amber-200/80 hover:border-amber-600'; ?> text-xs font-bold rounded-lg transition-all shrink-0 cursor-pointer shadow-2xs self-start sm:self-auto" data-file-url="<?php echo esc_url( $exemption_target_url ); ?>" data-file-title="Mẫu đơn xét miễn môn - <?php echo esc_attr( $item['subject_name'] ?? '' ); ?>">
														<?php if ( $is_ex_drive ) : ?>
															<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
															<span>Mở trên Google Drive</span>
														<?php else : ?>
															<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
															<span>Tải đơn miễn môn</span>
														<?php endif; ?>
													</a>
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
							<!-- Clickable Label Card for Image (Responsive & Touch-Friendly) -->
							<a href="<?php echo esc_url( $curriculum_url ); ?>" target="_blank" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 p-3.5 sm:p-4 bg-slate-50/90 hover:bg-blue-50/60 rounded-xl sm:rounded-2xl border border-slate-100 hover:border-blue-200/80 transition-all duration-200 group shadow-2xs hover:shadow-md block">
								<div class="flex items-start sm:items-center gap-3 sm:gap-3.5 min-w-0">
									<div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-blue-100/90 text-[#00308b] flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform mt-0.5 sm:mt-0">
										<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
									</div>
									<div class="min-w-0 flex-1">
										<h3 class="font-bold text-slate-900 text-xs sm:text-sm md:text-base group-hover:text-[#00308b] transition-colors leading-snug">Xem ảnh Khung chương trình đào tạo chi tiết</h3>
										<p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 sm:mt-1 leading-relaxed">Click để mở xem ảnh lộ trình các học kỳ & môn học kích thước chuẩn</p>
									</div>
								</div>
								<span class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 text-xs font-bold text-white bg-[#00308b] sm:bg-white sm:text-[#00308b] sm:border sm:border-blue-200/60 group-hover:bg-[#00308b] group-hover:text-white px-4 py-2 sm:py-2.5 rounded-lg shadow-2xs transition-all shrink-0 min-h-[38px] whitespace-nowrap">
									<span>Xem chi tiết</span>
									<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
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
										<h3 class="font-bold text-slate-900 text-sm sm:text-base mb-1 truncate">Khung chương trình đào tạo chi tiết</h3>
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
						'subtitle'   => 'Lộ trình 4 bước từ đăng ký hồ sơ đến khi chính thức nhập học tại ' . ( $program_school_name ?: 'nhà trường' ),
						'section_id' => 'quy-trinh-tuyen-sinh',
						'cta_text'   => 'Đăng ký tư vấn lộ trình ngay',
						'cta_link'   => '#register',
					]
				);
				?>

				<!-- SECTION 9: FAQ (Collapsible Accordion with UI/UX Pro Max) -->
				<?php if ( ! empty( $faqs ) ) : ?>
					<section class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6 mb-8" id="hoi-dap">
						<div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4 gap-2">
							<div>
								<div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-[#00308b] border border-blue-100/80 mb-1.5">
									<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
										<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
									</svg>
									<span>Hỏi đáp tuyển sinh</span>
								</div>
								<h2 class="text-lg md:text-2xl font-bold text-slate-900 leading-tight">Câu hỏi thường gặp</h2>
							</div>

							<!-- Bulk Toggle Button -->
							<button type="button" 
									id="ltdh-toggle-program-faq-btn"
									class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200/90 hover:border-slate-300 bg-slate-50 hover:bg-slate-100 text-xs font-bold text-slate-700 transition-colors cursor-pointer shrink-0"
									title="Mở hoặc thu gọn toàn bộ câu hỏi">
								<span id="ltdh-toggle-faq-text">Mở tất cả</span>
								<svg id="ltdh-toggle-faq-icon" class="w-3.5 h-3.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
									<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
								</svg>
							</button>
						</div>

						<div class="divide-y divide-slate-100" id="ltdh-program-faq-accordion">
							<?php foreach ( $faqs as $index => $item ) : ?>
								<details class="group py-3.5 sm:py-4 transition-colors">
									<summary class="flex items-center justify-between gap-3 cursor-pointer select-none font-bold text-slate-800 text-sm md:text-[15px] list-none hover:text-[#00308b] transition-colors">
										<div class="flex items-start gap-2.5 sm:gap-3 min-w-0">
											<span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-teal-50 text-teal-800 group-open:bg-teal-700 group-open:text-white text-xs font-black shrink-0 mt-0.5 transition-colors shadow-2xs">
												Q
											</span>
											<h3 class="leading-snug text-slate-800 group-open:text-[#00308b] transition-colors font-bold text-sm md:text-base m-0">
												<?php echo esc_html( $item['question'] ); ?>
											</h3>
										</div>
										<svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 group-open:rotate-180 group-open:text-[#00308b] transition-transform shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
											<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
										</svg>
									</summary>
									<div class="mt-2.5 sm:mt-3 pl-8 sm:pl-9 text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
										<?php echo strpos( $item['answer'], '<p' ) !== false ? wp_kses_post( $item['answer'] ) : wp_kses_post( wpautop( $item['answer'] ) ); ?>
									</div>
								</details>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<?php get_sidebar( 'program' ); ?>
			</div>
		</div>

	</div>
</main>



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

			<?php if ( function_exists( 'ltdh_render_recaptcha_field' ) ) : ?>
				<?php ltdh_render_recaptcha_field( 'lead_magnet' ); ?>
			<?php endif; ?>

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

<script data-no-defer="1">
window.ltdhCloseLeadMagnetModal = function() {
	const modal = document.getElementById('ltdh-lead-magnet-modal');
	if (modal) {
		modal.classList.add('hidden');
		modal.classList.remove('flex');
		document.body.style.overflow = '';
	}
};

window.ltdhSubmitLeadMagnet = async function(e) {
	if (e && e.preventDefault) e.preventDefault();
	const form = document.getElementById('ltdh-lead-magnet-form');
	if (!form) return;

	const fileUrlInput = document.getElementById('ltdh-lm-file-url');
	const submitBtn    = document.getElementById('ltdh-lm-submit-btn');
	const errorEl      = document.getElementById('ltdh-lm-error');
	const targetUrl    = fileUrlInput ? fileUrlInput.value : '';

	if (submitBtn) {
		submitBtn.disabled = true;
		submitBtn.innerHTML = '<span>Đang gửi thông tin...</span>';
	}
	if (errorEl) errorEl.classList.add('hidden');

	const formData  = new FormData(form);

	if (window.ltdhGetRecaptchaToken) {
		try {
			const recaptchaToken = await window.ltdhGetRecaptchaToken('lead_magnet', form);
			if (recaptchaToken) {
				formData.set('g-recaptcha-response', recaptchaToken);
			}
		} catch (recaptchaErr) {
			if (submitBtn) {
				submitBtn.disabled = false;
				submitBtn.innerHTML = '<span>Tải Tài Liệu Miễn Phí</span>';
			}
			if (errorEl) {
				errorEl.textContent = recaptchaErr.message || 'Vui lòng xác thực ô "Tôi không phải là người máy".';
				errorEl.classList.remove('hidden');
			}
			return;
		}
	}

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
			window.ltdhCloseLeadMagnetModal();

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

function ltdhInitLeadMagnet() {
	const modal          = document.getElementById('ltdh-lead-magnet-modal');
	const fileUrlInput   = document.getElementById('ltdh-lm-file-url');
	const fileTitleInput = document.getElementById('ltdh-lm-file-title');
	const docLabel       = document.getElementById('ltdh-lm-doc-name-label');
	const errorEl        = document.getElementById('ltdh-lm-error');

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

	// Close on modal backdrop click
	if (modal) {
		modal.addEventListener('click', function(e) {
			if (e.target === modal) {
				window.ltdhCloseLeadMagnetModal();
			}
		});
	}
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', ltdhInitLeadMagnet);
} else {
	ltdhInitLeadMagnet();
}
</script>

<!-- SCROLLSPY & SMOOTH SCROLL SCRIPT (UI/UX Pro Max) -->
<script>
(function() {
	function initProgramStickyNav() {
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

		// Toggle All / Collapse All for Requirements & Documents (UI/UX Pro Max)
		var toggleReqDocsBtn = document.getElementById('ltdh-toggle-req-docs-btn');
		var reqDocsDetails = document.querySelectorAll('#ltdh-req-docs-accordion details');
		if (toggleReqDocsBtn && reqDocsDetails.length) {
			var toggleReqDocsText = document.getElementById('ltdh-toggle-req-docs-text');
			var toggleReqDocsIcon = document.getElementById('ltdh-toggle-req-docs-icon');

			function updateToggleReqDocsState() {
				var allOpen = Array.prototype.slice.call(reqDocsDetails).every(function(d) { return d.open; });
				if (toggleReqDocsText) {
					toggleReqDocsText.textContent = allOpen ? 'Thu gọn' : 'Mở tất cả';
				}
				if (toggleReqDocsIcon) {
					if (allOpen) {
						toggleReqDocsIcon.classList.add('rotate-180');
					} else {
						toggleReqDocsIcon.classList.remove('rotate-180');
					}
				}
			}

			toggleReqDocsBtn.addEventListener('click', function() {
				var hasClosed = Array.prototype.slice.call(reqDocsDetails).some(function(d) { return !d.open; });
				reqDocsDetails.forEach(function(d) {
					d.open = hasClosed;
				});
				updateToggleReqDocsState();
			});

			reqDocsDetails.forEach(function(d) {
				d.addEventListener('toggle', updateToggleReqDocsState);
			});

			function checkReqDocsHash() {
				var h = window.location.hash;
				if (h === '#dieu-kien-xet-tuyen' || h === '#details-dieu-kien') {
					var item1 = document.getElementById('details-dieu-kien');
					if (item1) { item1.open = true; }
				} else if (h === '#ho-so-can-nop' || h === '#details-ho-so') {
					var item2 = document.getElementById('details-ho-so');
					if (item2) { item2.open = true; }
				} else if (h === '#thong-tin-lien-he' || h === '#details-lien-he') {
					var item3 = document.getElementById('details-lien-he');
					if (item3) { item3.open = true; }
				}
			}
			window.addEventListener('hashchange', checkReqDocsHash);
			checkReqDocsHash();
		}

		// Toggle All / Collapse All for Program FAQs
		var toggleFaqBtn = document.getElementById('ltdh-toggle-program-faq-btn');
		var faqDetails = document.querySelectorAll('#ltdh-program-faq-accordion details');
		if (toggleFaqBtn && faqDetails.length) {
			var toggleFaqText = document.getElementById('ltdh-toggle-faq-text');
			var toggleFaqIcon = document.getElementById('ltdh-toggle-faq-icon');

			function updateToggleFaqState() {
				var allOpen = Array.prototype.slice.call(faqDetails).every(function(d) { return d.open; });
				if (toggleFaqText) {
					toggleFaqText.textContent = allOpen ? 'Thu gọn' : 'Mở tất cả';
				}
				if (toggleFaqIcon) {
					if (allOpen) {
						toggleFaqIcon.classList.add('rotate-180');
					} else {
						toggleFaqIcon.classList.remove('rotate-180');
					}
				}
			}

			toggleFaqBtn.addEventListener('click', function() {
				var hasClosed = Array.prototype.slice.call(faqDetails).some(function(d) { return !d.open; });
				faqDetails.forEach(function(d) {
					d.open = hasClosed;
				});
				updateToggleFaqState();
			});

			faqDetails.forEach(function(d) {
				d.addEventListener('toggle', updateToggleFaqState);
			});
		}

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
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initProgramStickyNav);
	} else {
		initProgramStickyNav();
	}
})();
</script>

<?php
get_footer();
