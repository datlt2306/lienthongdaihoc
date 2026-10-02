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
$school_title = $school_id ? get_the_title( $school_id ) : '';
$major_title  = $major_id ? get_the_title( $major_id ) : '';

$tuition         = ltdh_get_program_tuition_display( $program_id );
$duration        = get_field( LTDH_META_DURATION, $program_id );
$requirements    = get_field( 'admission_requirements', $program_id );
$documents       = get_field( 'required_documents', $program_id );
$enrollment      = ltdh_get_program_admission_deadline_display( $program_id );
$quota           = get_field( 'quota', $program_id );

$program_hotline = ltdh_get_program_hotline( $program_id );
$benefits        = get_field( 'program_benefits', $program_id );
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

if ( ! empty( $requirements ) ) {
	$program_tabs[] = [
		'id'       => 'dieu-kien-xet-tuyen',
		'title'    => 'Điều kiện',
		'subtitle' => 'Đối tượng & tiêu chuẩn',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
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

if ( ! empty( $documents ) || ! empty( $admission_form_file ) ) {
	$program_tabs[] = [
		'id'       => 'ho-so-can-nop',
		'title'    => 'Hồ sơ',
		'subtitle' => 'Thủ tục đăng ký',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>',
	];
}

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
	<div id="ltdh-program-sticky-nav" class="ltdh-sticky-nav sticky top-20 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.06)] transition-all duration-200">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<nav class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden py-2" aria-label="Điều hướng các mục chương trình đào tạo">
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
					<div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 flex items-start gap-3 shadow-2xs">
						<svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
						<div>
							<h4 class="font-bold text-red-900 text-sm">Đã hết chỉ tiêu tuyển sinh năm nay</h4>
							<p class="text-xs text-red-700 mt-1">Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này. Quý học viên vui lòng tham khảo các chương trình liên quan hoặc để lại thông tin đăng ký tư vấn để được hướng dẫn lộ trình phù hợp.</p>
						</div>
					</div>
				<?php endif; ?>
				
				<!-- MOBILE ONLY SCHOOL MINI BAR (< 1024px) -->
				<?php if ( $school_id ) : ?>
					<div class="block lg:hidden bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
						<div class="flex items-center justify-between gap-3">
							<div class="flex items-center gap-2.5 min-w-0">
								<div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 p-0.5 shrink-0 flex items-center justify-center overflow-hidden">
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
				<section id="tong-quan" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Tổng quan chương trình</h2>
					<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
						<?php the_content(); ?>
					</div>
					<?php if ( $benefits ) : ?>
						<div class="mt-6 bg-teal-50/50 p-4 rounded-lg border border-teal-100/50 mb-6">
							<h3 class="text-teal-800 font-bold text-base mb-2">Quyền lợi nổi bật</h3>
							<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
								<?php echo wp_kses_post( $benefits ); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php
					$learning_details = ltdh_get_program_learning_details( $program_id );
					$tuition_year = get_field( 'tuition_academic_year', $program_id );
					?>
					<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-3.5 py-4 border-t border-slate-100">
						<div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center shadow-2xs">
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
						<div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center shadow-2xs">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Thời gian học</span>
							<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $duration ?: '1.5 - 2 năm' ); ?></span>
						</div>
						<?php if ( $quota ) : ?>
							<div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center shadow-2xs">
								<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Chỉ tiêu</span>
								<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $quota ); ?> chỉ tiêu</span>
							</div>
						<?php endif; ?>
						<div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center shadow-2xs">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Cơ sở học</span>
							<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php 
								$display_campus = $learning_details['campus'] ?? '';
								if ( empty( $display_campus ) || 'online' === strtolower( trim( $display_campus ) ) ) {
									$display_campus = 'Toàn quốc';
								}
								echo esc_html( $display_campus ); 
							?></span>
						</div>
						<div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center shadow-2xs">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Hình thức đào tạo</span>
							<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $learning_details['mode'] ); ?></span>
						</div>
						<div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center shadow-2xs">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Hạn hồ sơ</span>
							<span class="font-bold text-[#EA580C] text-xs sm:text-sm leading-snug"><?php echo esc_html( $enrollment ?: 'Đang nhận hồ sơ' ); ?></span>
						</div>
					</div>
				</section>

				<!-- SECTION: ADMISSION BATCHES -->
				<?php 
				$batches_data = get_field( 'admission_batches', $program_id );
				if ( ! empty( $batches_data ) && is_array( $batches_data ) ) : 
					$has_release = false;
					$has_app     = false;
					$has_review  = false;
					$has_eval    = false;

					foreach ( $batches_data as $b_item ) {
						$rel = trim( $b_item['release_period'] ?? '' );
						$app = trim( $b_item['application_period'] ?? '' );
						$rev = trim( $b_item['review_time'] ?? '' );
						$ev1 = trim( $b_item['evaluation_time'] ?? '' );
						$ev2 = trim( $b_item['enrollment_time'] ?? '' );

						if ( $rel !== '' && $rel !== '-' ) {
							$has_release = true;
						}
						if ( $app !== '' && $app !== '-' ) {
							$has_app = true;
						}
						if ( $rev !== '' && $rev !== '-' ) {
							$has_review = true;
						}
						if ( ( $ev1 !== '' && $ev1 !== '-' ) || ( $ev2 !== '' && $ev2 !== '-' ) ) {
							$has_eval = true;
						}
					}
				?>
				<section id="lich-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6 mb-8">
					<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Lịch trình các đợt tuyển sinh</h2>

							<!-- MOBILE TABBED CARD VIEW (< 768px) -->
							<div class="block md:hidden admission-batches-mobile">
								<!-- iOS Segmented Control Tab Navigation -->
								<div class="bg-slate-100 p-1 rounded-xl flex items-center justify-between gap-1 mb-3 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
									<?php foreach ( $batches_data as $b_idx => $b_item ) :
										$raw_name = $b_item['batch_name'] ?? '';
										// Shorten batch name for tabs: "Tuyển sinh Đợt 1" -> "Đợt 1", "Tuyển sinh Đợt 3 (Bổ sung)" -> "Đợt 3"
										$short_name = $raw_name;
										if ( preg_match( '/Đợt\s*\d+/i', $raw_name, $matches ) ) {
											$short_name = $matches[0];
										} elseif ( empty( $short_name ) ) {
											$short_name = 'Đợt ' . ( $b_idx + 1 );
										}

										$b_status  = $b_item['batch_status'] ?? '';
										$is_active = ( $b_idx === 0 );
										$tab_badge = '';
										if ( 'dang-nhan' === $b_status ) {
											$tab_badge = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse ml-0.5 inline-block"></span>';
										}
									?>
										<button type="button" 
											onclick="ltdhSwitchBatchTab(this, <?php echo (int) $b_idx; ?>)"
											class="ltdh-batch-tab-btn flex-1 py-2 px-3 text-xs rounded-lg transition-all text-center flex items-center justify-center gap-1 shrink-0 whitespace-nowrap outline-none focus:outline-none focus:ring-0 focus-visible:outline-none border-0 <?php echo $is_active ? 'bg-[#00308b] text-white font-bold shadow-xs' : 'bg-transparent text-slate-600 font-medium hover:text-slate-900'; ?>">
											<span><?php echo esc_html( $short_name ); ?></span>
											<?php echo $tab_badge; ?>
										</button>
									<?php endforeach; ?>
								</div>

								<!-- Tab Content Cards -->
								<?php foreach ( $batches_data as $b_idx => $b_item ) :
									$batch_name     = $b_item['batch_name'] ?? '';
									$clean_title    = preg_replace( '/^Tuyển sinh\s*/ui', '', $batch_name );
									$release_period = $b_item['release_period'] ?? '';
									$app_period     = $b_item['application_period'] ?? '';
									$review_time    = $b_item['review_time'] ?? '';
									$eval_time      = $b_item['evaluation_time'] ?? '';
									$enrol_time     = $b_item['enrollment_time'] ?? '';
									$status         = $b_item['batch_status'] ?? 'dang-nhan';
									
									$status_label = 'Đang nhận hồ sơ';
									$status_class = 'bg-emerald-50 text-emerald-700 border-0';
									if ( $status === 'sap-mo' ) {
										$status_label = 'Sắp mở';
										$status_class = 'bg-amber-50 text-amber-700 border-0';
									} elseif ( $status === 'da-dong' ) {
										$status_label = 'Đã đóng';
										$status_class = 'bg-slate-100 text-slate-600 border-0';
									}

									$eval_dates = array_filter([ $eval_time, $enrol_time ], function( $v ) {
										$v_clean = trim( $v );
										return $v_clean !== '' && $v_clean !== '-';
									});
									$eval_display = ! empty( $eval_dates ) ? implode(' | ', $eval_dates) : '';
									$is_hidden = ( $b_idx !== 0 );
								?>
									<div class="ltdh-batch-tab-card bg-white border border-slate-200/80 hover:border-slate-400 rounded-xl p-3.5 shadow-2xs hover:shadow-xs transition-all space-y-3 <?php echo $is_hidden ? 'hidden' : ''; ?>" data-batch-card="<?php echo (int) $b_idx; ?>">
										<div class="flex items-center justify-between border-b border-slate-100 pb-2.5 gap-2">
											<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $clean_title ); ?></span>
											<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border-0 shrink-0 <?php echo esc_attr( $status_class ); ?>">
												<?php echo esc_html( $status_label ); ?>
											</span>
										</div>

										<div class="space-y-3 pt-0.5">
											<?php if ( ! empty( $release_period ) && $release_period !== '-' ) : ?>
												<div class="flex items-start gap-2.5">
													<div class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Phát hành hồ sơ</div>
														<div class="text-xs font-semibold text-slate-800 mt-0.5"><?php echo esc_html( $release_period ); ?></div>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $app_period ) && $app_period !== '-' ) : ?>
												<div class="flex items-start gap-2.5">
													<div class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hạn nhận hồ sơ</div>
														<div class="text-xs font-semibold text-slate-800 mt-0.5"><?php echo esc_html( $app_period ); ?></div>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $review_time ) && $review_time !== '-' ) : ?>
												<div class="flex items-start gap-2.5">
													<div class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Thời gian ôn tập</div>
														<div class="text-xs font-semibold text-slate-800 mt-0.5"><?php echo esc_html( $review_time ); ?></div>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $eval_display ) ) : ?>
												<div class="flex items-start gap-2.5 pt-1.5 border-t border-slate-100">
													<div class="w-1.5 h-1.5 rounded-full bg-indigo-600 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Xét tuyển / Thi tuyển</div>
														<div class="text-xs font-bold text-[#00308b] mt-0.5"><?php echo esc_html( $eval_display ); ?></div>
													</div>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>

							<!-- DESKTOP BENTO GRID CARDS VIEW (>= 768px) -->
							<div class="hidden md:grid grid-cols-1 md:grid-cols-3 gap-3.5 sm:gap-4">
								<?php foreach ( $batches_data as $b_idx => $b_item ) :
									$batch_name     = $b_item['batch_name'] ?? '';
									$clean_title    = preg_replace( '/^Tuyển sinh\s*/ui', '', $batch_name );
									$release_period = $b_item['release_period'] ?? '';
									$app_period     = $b_item['application_period'] ?? '';
									$review_time    = $b_item['review_time'] ?? '';
									$eval_time      = $b_item['evaluation_time'] ?? '';
									$enrol_time     = $b_item['enrollment_time'] ?? '';
									$status         = $b_item['batch_status'] ?? 'dang-nhan';
									
									$status_label = 'Đang nhận hồ sơ';
									$status_class = 'bg-emerald-50 text-emerald-700 border-0';
									$card_style   = 'bg-white border border-slate-200/80 hover:border-slate-400 shadow-2xs hover:shadow-xs';
									
									if ( $status === 'dang-nhan' ) {
										$status_label = 'Đang nhận hồ sơ';
										$status_class = 'bg-emerald-600 text-white border-0 shadow-2xs';
										$card_style   = 'bg-emerald-50/15 border border-emerald-400/80 hover:border-emerald-500 shadow-xs';
									} elseif ( $status === 'sap-mo' ) {
										$status_label = 'Sắp mở';
										$status_class = 'bg-amber-50 text-amber-700 border-0';
									} elseif ( $status === 'da-dong' ) {
										$status_label = 'Đã đóng';
										$status_class = 'bg-slate-100 text-slate-500 border-0';
									}

									$eval_dates = array_filter([ $eval_time, $enrol_time ], function( $v ) {
										$v_clean = trim( $v );
										return $v_clean !== '' && $v_clean !== '-';
									});
									$eval_display = ! empty( $eval_dates ) ? implode(' | ', $eval_dates) : '';
								?>
									<div class="rounded-xl p-3.5 sm:p-4 transition-all duration-200 flex flex-col justify-between space-y-3 <?php echo esc_attr( $card_style ); ?>">
										<!-- Card Header -->
										<div class="flex items-center justify-between pb-2.5 gap-2 border-b border-slate-100/80">
											<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $clean_title ); ?></span>
											<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 <?php echo esc_attr( $status_class ); ?>">
												<?php echo esc_html( $status_label ); ?>
											</span>
										</div>

										<!-- Card Content Timeline -->
										<div class="space-y-3 flex-1">
											<?php if ( ! empty( $release_period ) && $release_period !== '-' ) : ?>
												<div class="flex items-start gap-x-2.5">
													<div class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Phát hành hồ sơ</div>
														<div class="text-xs font-semibold text-slate-800 mt-0.5"><?php echo esc_html( $release_period ); ?></div>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $app_period ) && $app_period !== '-' ) : ?>
												<div class="flex items-start gap-x-2.5">
													<div class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hạn nhận hồ sơ</div>
														<div class="text-xs font-semibold text-slate-800 mt-0.5"><?php echo esc_html( $app_period ); ?></div>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $review_time ) && $review_time !== '-' ) : ?>
												<div class="flex items-start gap-x-2.5">
													<div class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Thời gian ôn tập</div>
														<div class="text-xs font-semibold text-slate-800 mt-0.5"><?php echo esc_html( $review_time ); ?></div>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $eval_display ) ) : ?>
												<div class="flex items-start gap-x-2.5 pt-1.5">
													<div class="w-1.5 h-1.5 rounded-full bg-indigo-600 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Xét tuyển / Thi tuyển</div>
														<div class="text-xs font-bold text-[#00308b] mt-0.5"><?php echo esc_html( $eval_display ); ?></div>
													</div>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>

						<script>
						function ltdhSwitchBatchTab(btn, idx) {
							const container = btn.closest('.admission-batches-mobile');
							if (!container) return;
							const btns = container.querySelectorAll('.ltdh-batch-tab-btn');
							const cards = container.querySelectorAll('.ltdh-batch-tab-card');
							
							const activeCls   = ['bg-[#00308b]', 'text-white', 'font-bold', 'shadow-xs'];
							const inactiveCls = ['bg-transparent', 'text-slate-600', 'font-medium'];

							btns.forEach((b, i) => {
								if (i === idx) {
									inactiveCls.forEach(c => b.classList.remove(c));
									activeCls.forEach(c => b.classList.add(c));
								} else {
									activeCls.forEach(c => b.classList.remove(c));
									inactiveCls.forEach(c => b.classList.add(c));
								}
							});

							cards.forEach((c, i) => {
								if (i === idx) {
									c.classList.remove('hidden');
								} else {
									c.classList.add('hidden');
								}
							});
						}
						</script>
					</section>
				<?php endif; ?>


				<!-- SECTION 5: ADMISSION REQUIREMENTS -->
				<?php if ( $requirements ) : ?>
					<section id="dieu-kien-xet-tuyen" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
							<h2 class="text-lg md:text-2xl font-bold text-slate-900">Điều kiện xét tuyển</h2>
							<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-[#00308b] border border-blue-100 text-xs font-bold rounded-full">
								<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
								Phương thức: <?php echo ( strpos( mb_strtolower( $requirements ), 'thi tuyển' ) !== false ) ? 'Thi tuyển' : 'Xét tuyển'; ?>
							</span>
						</div>

						<?php if ( strpos( strtolower($requirements), 'thi tuyển 3 môn' ) !== false || strpos( strtolower($requirements), 'thi tuyển' ) !== false ) : ?>
							<!-- Structured High-End Admission Cards View -->
							<div class="space-y-4">
								<!-- Intro Box -->
								<div class="bg-slate-50/80 border border-slate-200/80 rounded-xl p-4 flex items-center gap-3">
									<div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
										<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
									</div>
									<div>
										<h4 class="font-extrabold text-slate-900 text-sm md:text-base">Phương thức tuyển sinh duy nhất: Thi tuyển 3 môn</h4>
										<p class="text-xs md:text-sm text-slate-600 mt-0.5">Áp dụng chính thức cho thí sinh đăng ký chương trình Liên thông ngành Công nghệ thông tin.</p>
									</div>
								</div>

								<!-- 3 Exam Subject Cards Grid -->
								<div>
									<span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2.5">Danh mục 3 môn thi tuyển:</span>
									<div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
										<!-- Môn 1 -->
										<div class="bg-white border border-slate-200/80 rounded-xl p-4 shadow-2xs hover:border-blue-300 transition-all">
											<div class="flex items-center justify-between mb-2">
												<span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-black rounded-md">Môn 1</span>
												<span class="text-[11px] font-semibold text-slate-400">Cơ bản</span>
											</div>
											<h5 class="font-black text-slate-900 text-base md:text-lg mb-1">Toán</h5>
											<p class="text-xs text-slate-500 leading-relaxed">Phần thi kiến thức Toán học cơ bản.</p>
										</div>

										<!-- Môn 2 -->
										<div class="bg-white border border-slate-200/80 rounded-xl p-4 shadow-2xs hover:border-blue-300 transition-all">
											<div class="flex items-center justify-between mb-2">
												<span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded-md">Môn 2</span>
												<span class="text-[11px] font-semibold text-slate-400">Cơ sở ngành</span>
											</div>
											<h5 class="font-black text-slate-900 text-base md:text-lg mb-1">Toán rời rạc</h5>
											<p class="text-xs text-slate-500 leading-relaxed">Phần thi kiến thức Cơ sở ngành CNTT.</p>
										</div>

										<!-- Môn 3 -->
										<div class="bg-white border border-slate-200/80 rounded-xl p-4 shadow-2xs hover:border-blue-300 transition-all">
											<div class="flex items-center justify-between mb-2">
												<span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-md">Môn 3</span>
												<span class="text-[11px] font-semibold text-slate-400">Chuyên môn</span>
											</div>
											<h5 class="font-black text-slate-900 text-base md:text-lg mb-1">Cấu trúc dữ liệu & Giải thuật</h5>
											<p class="text-xs text-slate-500 leading-relaxed">Phần thi kiến thức Lập trình chuyên ngành.</p>
										</div>
									</div>
								</div>

								<!-- Quality Threshold Alert Box -->
								<div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-4 flex items-start gap-3 text-xs md:text-sm text-amber-950 leading-relaxed">
									<svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
									<div>
										<strong class="font-extrabold text-amber-950 block mb-0.5">Ngưỡng đảm bảo chất lượng đầu vào:</strong>
										<p class="text-amber-900">Tổng điểm thi của 3 môn phải thỏa mãn ngưỡng đảm bảo chất lượng theo Quy chế tuyển sinh hiện hành của Trường Đại học Giao thông Vận tải và Bộ Giáo dục & Đào tạo.</p>
									</div>
								</div>
							</div>
						<?php else : ?>
							<!-- Fallback formatted view -->
							<div class="bg-slate-50/60 border border-slate-200/80 rounded-xl p-4 sm:p-5 text-slate-800 text-sm md:text-base leading-relaxed">
								<?php echo wp_kses_post( $requirements ); ?>
							</div>
						<?php endif; ?>
					</section>
				<?php endif; ?>

				<!-- SECTION 6: TUITION & SECTION 7: DURATION -->
				<section id="hoc-phi-thoi-gian" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Học phí & Thời gian học</h2>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
						<!-- Column 1: Tuition Details -->
						<div class="bg-white border border-slate-200/80 p-5 rounded-2xl flex flex-col justify-between shadow-2xs hover:shadow-xs transition-all duration-300">
							<div class="space-y-3.5">
								<div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-1">
									<svg class="w-5.5 h-5.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
									<h3 class="font-extrabold text-base md:text-lg text-slate-900">Thông tin học phí</h3>
								</div>
								
								<!-- Học phí per credit -->
								<div class="bg-slate-50 border border-slate-100/80 rounded-xl p-3.5">
									<span class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Đơn giá học phí</span>
									<div class="flex items-baseline flex-wrap gap-1">
										<span class="text-base md:text-lg font-black text-slate-900">
											<?php echo esc_html( $tuition ?: 'Liên hệ ban tuyển sinh' ); ?>
										</span>
										<?php if ( isset($tuition_year) && $tuition_year ) : ?>
											<span class="text-xs font-semibold text-slate-600">(Năm học <?php echo esc_html( $tuition_year ); ?>)</span>
										<?php endif; ?>
									</div>
								</div>

								<!-- Total credits -->
								<?php 
								$total_credits = get_field( 'tuition_total_credits', $program_id );
								if ( $total_credits ) : 
								?>
									<div class="bg-slate-50 border border-slate-100/80 rounded-xl p-3.5">
										<span class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Tổng số tín chỉ toàn khóa</span>
										<div class="flex items-baseline flex-wrap gap-1">
											<span class="text-base font-black text-slate-900">
												<?php echo esc_html( $total_credits ); ?> tín chỉ
											</span>
											<?php if ( isset($tuition_year) && $tuition_year ) : ?>
												<span class="text-xs font-semibold text-slate-600">(Năm học <?php echo esc_html( $tuition_year ); ?>)</span>
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
						<div class="bg-white border border-slate-200/80 p-5 rounded-2xl flex flex-col justify-between shadow-2xs hover:shadow-xs transition-all duration-300">
							<div class="space-y-3.5">
								<div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-1">
									<svg class="w-5.5 h-5.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
									<h3 class="font-extrabold text-base md:text-lg text-slate-900">Thời gian học tập</h3>
								</div>
								
								<!-- Card 1: Standard Duration -->
								<div class="bg-slate-50 border border-slate-100/80 rounded-xl p-3.5">
									<span class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Lộ trình chuẩn</span>
									<div class="flex items-baseline flex-wrap gap-1">
										<span class="text-base font-black text-slate-900"><?php echo esc_html( $duration ?: '2.0 - 3.0 năm' ); ?></span>
									</div>
								</div>

								<!-- Card 2: Exemption Scope -->
								<?php
								$exemption_title = get_field( 'exemption_title', $program_id );
								$exemption_intro = get_field( 'exemption_intro', $program_id );
								$exemption_items = get_field( 'exemption_items', $program_id );

								$school_slug = $school_id ? get_post_field( 'post_name', $school_id ) : '';
								$is_utc      = ( false !== strpos( $school_slug, 'giao-thong-van-tai' ) );

								if ( empty( $exemption_items ) && $is_utc ) {
									$exemption_title = 'Quy định miễn môn đối với Đại học GTVT (UTC)';
									$exemption_intro = 'Chương trình đào tạo hệ liên thông của UTC chỉ xem xét miễn trừ tối đa đối với 2 môn học dưới đây nếu học viên đáp ứng đủ điều kiện:';
									$exemption_items = [
										[
											'subject_name'       => 'Giáo dục quốc phòng an ninh',
											'condition_note'     => 'Chỉ được xét miễn giảm khi học viên nộp chứng chỉ do Bộ Giáo dục và Đào tạo cấp theo phôi mẫu chuẩn (màu đỏ). Các loại phôi khác (kể cả phôi của các trường tự cấp) đều không được chấp nhận.',
											'cert_scores'        => [],
											'cert_note'          => '',
											'assessment_process' => '',
										],
										[
											'subject_name'       => 'Tiếng Anh B1',
											'condition_note'     => 'Được xem xét quy đổi điểm khi sở hữu một trong các chứng chỉ quốc tế/quốc gia còn hiệu lực:',
											'cert_scores'        => [
												[ 'cert_name' => 'IELTS', 'min_score' => '≥ 4.5' ],
												[ 'cert_name' => 'TOEIC', 'min_score' => '≥ 450' ],
												[ 'cert_name' => 'VSTEP', 'min_score' => '≥ 5.0' ],
											],
											'cert_note'          => '* Riêng VSTEP: Chỉ nhận chứng chỉ do 1 trong 3 cơ sở đào tạo cấp: ĐH Quốc gia HN, ĐH Sư phạm HN, và ĐH Hà Nội.',
											'assessment_process' => 'Quy trình thẩm định & Quy đổi điểm: Sinh viên bắt buộc phải tham gia và vượt qua bài kiểm tra năng lực do bộ môn tổ chức. Nếu đạt yêu cầu, điểm số sẽ được quy đổi sang điểm 5 trên hệ thống.',
										],
									];
								}

								$scope_text = ! empty( $exemption_items ) ? 'Theo quy định nhà trường' : ( $is_utc ? 'Tối đa 2 môn (GDQP & Tiếng Anh)' : 'Xét theo bảng điểm cũ' );
								?>
								<div class="bg-slate-50 border border-slate-100/80 rounded-xl p-3.5">
									<span class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Phạm vi miễn giảm môn</span>
									<div class="flex items-baseline flex-wrap gap-1">
										<span class="text-base font-black text-slate-900"><?php echo esc_html( $scope_text ); ?></span>
									</div>
								</div>
							</div>
							
							<div class="mt-4 pt-3.5 border-t border-slate-100 text-xs md:text-sm text-slate-600 leading-relaxed">
								<span class="font-bold text-slate-700 block mb-1">Lưu ý quan trọng:</span>
								<?php if ( ! empty( $exemption_items ) ) : ?>
									<p class="text-slate-600">Quy định miễn trừ học phần và quy đổi tín chỉ được thực hiện theo đúng hướng dẫn của Nhà trường.</p>
								<?php elseif ( $is_utc ) : ?>
									<p class="text-slate-600">Nhà trường chỉ xem xét miễn trừ 2 môn (GDQP-AN và Tiếng Anh B1) nếu đủ điều kiện chứng chỉ. Tất cả các môn học khác học viên bắt buộc phải hoàn thành theo khung chương trình.</p>
								<?php else : ?>
									<p class="text-slate-600">Học viên được xem xét miễn giảm các môn đại cương và môn chuyên ngành dựa trên bảng điểm tốt nghiệp trung cấp, cao đẳng hoặc văn bằng 1 đã có.</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<?php 
					if ( ! empty( $exemption_items ) && is_array( $exemption_items ) ) :
					?>
						<div class="mt-5 border-t border-slate-100 pt-5">
							<div class="bg-blue-50/40 border border-blue-100 rounded-2xl p-5 shadow-3xs">
								<div class="flex items-center gap-2 border-b border-blue-100 pb-3 mb-3">
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
										<div class="bg-white border border-slate-200/80 rounded-xl p-4 sm:p-4.5 shadow-3xs hover:border-blue-300 transition-all duration-300">
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
															<div class="bg-slate-50 border border-slate-200/80 rounded-xl p-2.5 sm:p-3 text-center hover:border-blue-400 hover:shadow-xs transition-all duration-200 cursor-pointer">
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
												<div class="mt-3 bg-amber-50 border border-amber-200/70 rounded-xl p-3.5 text-xs md:text-sm text-amber-900 leading-relaxed flex items-start gap-2.5">
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
					<section class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6" id="lo-trinh-hoc">
						<div class="border-b border-slate-100 pb-3 mb-4">
							<h2 class="text-lg md:text-2xl font-bold text-slate-900">Lộ trình học & Khung chương trình</h2>
							<p class="text-xs md:text-sm text-slate-500 mt-0.5">Khung chương trình đào tạo chính thức áp dụng cho khóa học này</p>
						</div>

						<?php if ( $curriculum_type === 'image' ) : ?>
							<!-- Clickable Label Card for Image -->
							<a href="<?php echo esc_url( $curriculum_url ); ?>" target="_blank" class="flex items-center justify-between p-4 bg-slate-50 hover:bg-blue-50/60 border border-slate-200/80 hover:border-blue-300 rounded-xl transition-all duration-200 group shadow-2xs">
								<div class="flex items-center gap-3.5">
									<div class="w-10 h-10 rounded-xl bg-blue-100/80 text-blue-700 flex items-center justify-center shrink-0">
										<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
									</div>
									<div>
										<h4 class="font-extrabold text-slate-900 text-sm group-hover:text-blue-900 transition-colors">Xem ảnh Khung chương trình đào tạo chi tiết</h4>
										<p class="text-xs text-slate-500 mt-0.5">Click để mở xem ảnh lộ trình các học kỳ & môn học kích thước chuẩn</p>
									</div>
								</div>
								<span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-white border border-slate-200 group-hover:border-blue-300 px-3.5 py-2 rounded-lg shadow-2xs group-hover:bg-blue-600 group-hover:text-white transition-all shrink-0">
									<span>Xem chi tiết</span>
									<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
								</span>
							</a>
						<?php else : ?>
							<!-- PDF or File Download Box -->
							<div class="bg-slate-50 p-4 sm:p-5 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
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

				<!-- SECTION 8: DOCUMENTS REQUIRED -->
				<?php 
				$admission_form = get_field( 'admission_form_file', $program_id );
				$form_url = '';
				if ( is_array( $admission_form ) && ! empty( $admission_form['url'] ) ) {
					$form_url = $admission_form['url'];
				} elseif ( is_string( $admission_form ) && ! empty( $admission_form ) ) {
					$form_url = $admission_form;
				}
				?>
				<?php if ( $documents || $form_url ) : ?>
					<section class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6" id="ho-so-can-nop">
						<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Hồ sơ xét tuyển cần thiết</h2>
						<?php if ( $documents ) : ?>
							<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
								<?php echo wp_kses_post( $documents ); ?>
							</div>
						<?php endif; ?>

						<?php if ( $form_url ) : ?>
							<div class="mt-5 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-blue-50/60 p-4 rounded-xl border border-blue-100/80">
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
					</section>
				<?php endif; ?>

				<!-- SECTION 9: FAQ -->
				<?php if ( ! empty( $faqs ) ) : ?>
					<section class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6" id="hoi-dap">
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

				<!-- SECTION 10: RELATED PROGRAMS -->
				<?php
				$related_query = new WP_Query( [
					'post_type'      => 'program',
					'posts_per_page' => 3,
					'post__not_in'   => [ $program_id ],
					'meta_query'     => [
						'relation' => 'OR',
						[
							'key'     => LTDH_META_MAJOR_REL,
							'value'   => $major_id,
							'compare' => '=',
						],
						[
							'key'     => LTDH_META_SCHOOL_REL,
							'value'   => $school_id,
							'compare' => '=',
						]
					]
				] );

				if ( $related_query->have_posts() ) :
				?>
					<section class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6" id="chuong-trinh-lien-quan">
						<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Chương trình liên quan</h2>
						<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
							<?php 
							while ( $related_query->have_posts() ) : 
								$related_query->the_post();
								$rel_school_id = get_field( LTDH_META_SCHOOL_REL );
								$rel_school = $rel_school_id ? get_the_title( $rel_school_id ) : '';
							?>
								<a href="<?php the_permalink(); ?>" class="group block border border-slate-100 rounded-lg p-4 hover:border-brand-primary hover:shadow-md transition-all bg-white">
									<span class="text-sm text-slate-400 block mb-1 font-medium"><?php echo esc_html( $rel_school ); ?></span>
									<h4 class="font-bold text-slate-800 text-sm group-hover:text-brand-primary transition-colors line-clamp-2"><?php the_title(); ?></h4>
									<div class="mt-3 flex justify-between items-center text-sm text-slate-500 border-t border-slate-50 pt-2">
										<span>Học phí: <?php echo esc_html( ltdh_get_program_tuition_display( get_the_ID() ) ); ?></span>
									</div>
								</a>
							<?php 
							endwhile; 
							wp_reset_postdata();
							?>
						</div>
					</section>
				<?php endif; ?>

			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<div class="sticky top-36 md:top-40 space-y-6">
					
					<!-- SCHOOL INFO CARD (Desktop Only >= 1024px) -->
					<?php if ( $school_id ) : 
						$school_logo_id  = function_exists( 'ltdh_get_school_image_id' ) ? ltdh_get_school_image_id( $school_id ) : 0;
						$school_logo_url = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'thumbnail' ) : get_stylesheet_directory_uri() . '/assets/images/cropped-logo-scaled-2.webp';
						$school_cover_url = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'medium' ) : ltdh_get_fallback_image( 'school' );
						$school_address = get_post_meta( $school_id, 'address', true ) ?: get_field( 'address', $school_id ) ?: 'Việt Nam';
						$school_web = get_field( 'website', $school_id );
						
						// Get initials for typographic logo fallback (e.g. UTC, ĐHGTVT)
						$words = explode( ' ', $school_title );
						$initials = '';
						foreach ( array_slice( $words, -3 ) as $w ) {
							$initials .= mb_substr( $w, 0, 1 );
						}
						$initials = mb_strtoupper( $initials );
					?>
						<div class="hidden lg:block bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs hover:shadow-sm transition-all">
							<!-- Banner Cover -->
							<div class="h-24 bg-gradient-to-r from-[#00308b] to-[#001a4d] bg-cover bg-center relative" <?php echo $school_cover_url ? 'style="background-image: url(\'' . esc_url( $school_cover_url ) . '\');"' : ''; ?>>
								<div class="absolute inset-0 bg-blue-950/20"></div>
							</div>
							
							<!-- Overlapping Logo Wrapper -->
							<div class="relative flex justify-center -mt-9 mb-3">
								<div class="w-18 h-18 bg-white p-1 rounded-xl shadow-md border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
									<?php if ( $school_logo_url ) : ?>
										<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_title ); ?>" class="max-w-full max-h-full object-contain" loading="lazy">
									<?php else : ?>
										<div class="w-full h-full bg-[#00308b] text-white flex items-center justify-center font-black text-base rounded-lg uppercase tracking-wider">
											<?php echo esc_html( $initials ?: 'ĐH' ); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
							
							<!-- School Info details -->
							<div class="px-5 pb-5 text-center">
								<h4 class="font-extrabold text-slate-800 text-sm leading-snug uppercase tracking-tight mb-2">
									<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="hover:text-[#00308b] transition-colors"><?php echo esc_html( $school_title ); ?></a>
								</h4>
								
								<?php if ( $school_web ) : ?>
									<p class="text-xs text-slate-500 mb-2 font-medium">
										Website: <a href="<?php echo esc_url( $school_web ); ?>" target="_blank" rel="noopener noreferrer" class="text-[#00308b] hover:underline"><?php echo esc_html( $school_web ); ?></a>
									</p>
								<?php endif; ?>

								<div class="flex items-start justify-center gap-1.5 text-xs text-slate-500 font-medium max-w-xs mx-auto mb-3.5">
									<span class="text-[#00308b] shrink-0 mt-0.5">📍</span>
									<span class="text-left leading-relaxed">
										Địa chỉ: <?php echo esc_html( $school_address ); ?>
										<a href="<?php echo esc_url( 'https://www.google.com/maps/search/?api=1&query=' . urlencode( $school_title . ' ' . $school_address ) ); ?>" target="_blank" rel="noopener noreferrer" class="text-[#00308b] font-bold hover:underline ml-0.5 inline-block">(Xem bản đồ)</a>
									</span>
								</div>
								<div class="border-t border-slate-100 pt-3">
									<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="text-[#00308b] font-bold text-xs sm:text-sm hover:underline flex items-center justify-center gap-1">
										<span>Xem chi tiết trường</span> <span>→</span>
									</a>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<!-- SECTION 11: CONSULTATION FORM (Sidebar Form - Available on Mobile & Desktop) -->
					<section id="register" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2">Đăng ký tư vấn miễn phí</h3>
						<p class="text-sm text-slate-500 mb-4">Hãy để lại thông tin, ban tư vấn tuyển sinh sẽ liên hệ và giải đáp lộ trình cụ thể cho bạn trong vòng 15 phút.</p>
						
						<?php 
						$form_context = [
							'current_program_id' => $program_id,
							'current_school_id'  => $school_id,
							'current_major_id'   => $major_id,
							'referral_source'    => get_permalink(),
						];
						ltdh_render_consultation_form( $form_context );
						?>
					</section>
					


					<!-- ZALO GROUP COMMUNITY WIDGET (Sidebar - Desktop Only) -->
					<?php
					$program_school_zalo = ( $school_id ? ( get_field( 'zalo_group_url', $school_id ) ?: get_field( 'school_zalo_group', $school_id ) ) : '' ) ?: $global_zalo;
					$program_school_name = $school_title ?: 'chương trình';
					?>
					<div class="hidden lg:block bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-xl p-5 shadow-md relative overflow-hidden border border-blue-500/30">
						<div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

						<div class="flex items-center gap-3 mb-3 relative z-10">
							<div class="w-10 h-10 rounded-xl bg-white text-blue-600 flex items-center justify-center font-black text-xl shadow-xs shrink-0">
								💬
							</div>
							<div>
								<span class="text-[11px] uppercase font-bold tracking-wider text-blue-200 block">Cộng đồng sinh viên</span>
								<h3 class="font-extrabold text-base text-white leading-tight">Nhóm Zalo Trao Đổi</h3>
							</div>
						</div>

						<p class="text-xs text-blue-100 leading-relaxed mb-4 relative z-10">
							Tham gia nhóm Zalo trao đổi thông tin tuyển sinh, lịch học và đề án cùng sinh viên <?php echo esc_html( $program_school_name ); ?>.
						</p>

						<ul class="space-y-1.5 text-xs text-blue-50 font-medium mb-4 relative z-10">
							<li class="flex items-center gap-2">
								<span class="text-emerald-300 font-bold">✓</span>
								<span>Cập nhật lịch thi & đề án mới nhất</span>
							</li>
							<li class="flex items-center gap-2">
								<span class="text-emerald-300 font-bold">✓</span>
								<span>Giải đáp thắc mắc hồ sơ 24/7</span>
							</li>
						</ul>

						<a href="<?php echo esc_url( $program_school_zalo ); ?>"
						   target="_blank"
						   rel="noopener noreferrer"
						   class="w-full bg-white hover:bg-blue-50 text-blue-700 font-extrabold text-sm py-3 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 min-h-[44px] relative z-10 hover:scale-[1.02]">
							<span>💬 Tham gia Nhóm Zalo</span>
							<span class="text-xs">→</span>
						</a>
					</div>

					<!-- RELATED NEWS & ANNOUNCEMENTS (Sidebar - Desktop Only) -->
					<?php
					$program_news_meta = [ 'relation' => 'OR' ];
					$program_news_meta[] = [
						'key'     => 'related_programs',
						'value'   => '"' . $program_id . '"',
						'compare' => 'LIKE',
					];
					if ( $school_id ) {
						$program_news_meta[] = [
							'key'     => 'related_schools',
							'value'   => '"' . $school_id . '"',
							'compare' => 'LIKE',
						];
					}

					$related_news_query = new WP_Query( [
						'post_type'      => [ 'post', 'guide' ],
						'posts_per_page' => 6,
						'post_status'    => 'publish',
						'meta_query'     => $program_news_meta,
					] );

					if ( $related_news_query->have_posts() ) :
						$has_more = ( $related_news_query->post_count > 5 );
					?>
						<section class="hidden lg:block bg-white rounded-lg shadow-sm border border-slate-200 p-4 md:p-5">
							<h3 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-3">Tin tức & Thông báo liên quan</h3>
							<div class="space-y-3.5">
								<?php
								$news_counter = 0;
								while ( $related_news_query->have_posts() ) :
									$related_news_query->the_post();
									if ( $news_counter >= 5 ) {
										continue;
									}
									$news_thumb = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
									$news_counter++;
								?>
									<div class="flex gap-3 items-start pb-3 border-b border-slate-100 last:border-b-0 last:pb-0">
										<?php if ( $news_thumb ) : ?>
											<a href="<?php the_permalink(); ?>" class="shrink-0">
												<img src="<?php echo esc_url( $news_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-12 h-12 object-cover rounded border border-slate-100" loading="lazy">
											</a>
										<?php else : ?>
											<div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded flex items-center justify-center shrink-0">
												<span class="text-lg">📰</span>
											</div>
										<?php endif; ?>
										
										<div class="flex-1 min-w-0">
											<h4 class="font-bold text-slate-800 text-sm hover:text-brand-primary transition-colors line-clamp-2 leading-snug">
												<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
											</h4>
											<p class="text-xs text-slate-400 mt-0.5">📅 <?php echo get_the_date(); ?></p>
										</div>
									</div>
								<?php
								endwhile;
								wp_reset_postdata();
								?>
							</div>

							<?php if ( $has_more ) : ?>
								<div class="mt-4 pt-3 border-t border-slate-100">
									<a href="<?php echo esc_url( home_url( '/tin-tuc/?chuong-trinh=' . $program_id ) ); ?>" class="w-full text-center bg-slate-50 border border-slate-200 text-slate-700 py-2.5 rounded-lg font-bold text-sm hover:bg-slate-100 transition-all flex items-center justify-center gap-1.5 min-h-[38px]">
										<span>Xem thêm tin tức</span>
										<span>→</span>
									</a>
								</div>
							<?php endif; ?>
						</section>
					<?php
					endif;
					?>

					<!-- SECTION 13: PHONE CTA & SECTION 14: ZALO CTA sidebar cards (Desktop Only) -->
					<div class="hidden lg:block bg-brand-accent/5 border border-brand-primary/10 rounded-lg p-6 text-center">
						<span class="text-sm text-brand-primary font-bold uppercase tracking-wider block mb-1">Cần hỗ trợ trực tiếp?</span>
						<h4 class="font-display font-black text-2xl text-slate-800 mb-4"><?php echo esc_html( $program_hotline ); ?></h4>
						<div class="flex gap-2">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $program_hotline ) ); ?>" class="flex-1 bg-brand-accent text-white py-3.5 rounded-lg font-semibold text-sm hover:bg-[#e06e00] transition-all min-h-[44px] flex items-center justify-center">Gọi Điện</a>
							<a href="<?php echo esc_url( $global_zalo ); ?>" class="flex-1 bg-white border border-brand-primary text-brand-primary py-3.5 rounded-lg font-semibold text-sm hover:bg-brand-accent/5 transition-all min-h-[44px] flex items-center justify-center">Chat Zalo</a>
						</div>
					</div>

					<!-- COMPARE BUTTON ONLY (Desktop Only) -->
					<?php
					$types = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
					$type_slug = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->slug : '';
					$major_rel_id_raw = get_field( LTDH_META_MAJOR_REL, $program_id );
					$major_rel_id = 0;
					if ( is_array( $major_rel_id_raw ) ) {
						$major_rel_id = ! empty( $major_rel_id_raw ) ? ( is_object( $major_rel_id_raw[0] ) ? $major_rel_id_raw[0]->ID : $major_rel_id_raw[0] ) : 0;
					} elseif ( is_object( $major_rel_id_raw ) ) {
						$major_rel_id = $major_rel_id_raw->ID;
					} elseif ( $major_rel_id_raw ) {
						$major_rel_id = intval( $major_rel_id_raw );
					}
					$major_slug = $major_rel_id ? get_post_field( 'post_name', $major_rel_id ) : '';
					?>
					<button type="button"
							class="hidden lg:flex w-full text-center bg-white border border-slate-200 text-slate-700 py-3.5 rounded-xl font-bold shadow-xs hover:bg-slate-50 transition-all ltdh-compare-single-btn text-sm flex items-center justify-center gap-2 mt-4 min-h-[44px]"
							data-compare-type="program" data-compare-id="<?php echo esc_attr( $program_id ); ?>"
							data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
							data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $program_id ) ); ?>"
							data-compare-he="<?php echo esc_attr( $type_slug ); ?>"
							data-compare-nganh="<?php echo esc_attr( $major_slug ); ?>"
							data-compare-major-name="<?php echo esc_attr( preg_replace( '/^ngành\s+/iu', '', trim( $major_title ) ) ); ?>"
							data-compare-school-name="<?php echo esc_attr( $school_title ); ?>">
						<span>📊</span> <span>Thêm vào so sánh</span>
					</button>

				</div>
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
			<a href="<?php echo esc_url( $form_url ); ?>" download target="_blank" rel="noopener noreferrer" class="ltdh-lead-magnet-btn bg-slate-100 hover:bg-slate-200 text-slate-700 p-2.5 rounded-lg text-xs font-bold transition-all shrink-0 flex items-center justify-center border border-slate-200/80 min-h-[40px] cursor-pointer" title="Tải phiếu tuyển sinh" data-file-url="<?php echo esc_url( $form_url ); ?>" data-file-title="Phiếu tuyển sinh: <?php echo esc_attr( get_the_title() ); ?>">
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

		function setActiveTab(targetId, shouldScrollNav) {
			sectionMap.forEach(function(item) {
				var link = item.link;
				var iconEl = link.querySelector('.ltdh-tab-icon');
				var titleEl = link.querySelector('.ltdh-tab-title');
				var subEl = link.querySelector('.ltdh-tab-sub');
				var indicatorEl = link.querySelector('.ltdh-tab-indicator');

				if (item.id === targetId) {
					link.classList.add('bg-blue-50/90', 'border-blue-200/80', 'text-[#00308b]', 'font-bold', 'shadow-2xs', 'is-active');
					link.classList.remove('text-slate-600', 'hover:bg-slate-100/70', 'border-transparent');
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

					if (shouldScrollNav && navContainer && navContainer.scrollWidth > navContainer.clientWidth) {
						var tabLeft = link.offsetLeft;
						var tabWidth = link.offsetWidth;
						var containerWidth = navContainer.clientWidth;
						navContainer.scrollTo({
							left: tabLeft - (containerWidth / 2) + (tabWidth / 2),
							behavior: 'smooth'
						});
					}
				} else {
					link.classList.remove('bg-blue-50/90', 'border-blue-200/80', 'text-[#00308b]', 'font-bold', 'shadow-2xs', 'is-active');
					link.classList.add('text-slate-600', 'hover:bg-slate-100/70', 'border-transparent');
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
				setActiveTab(currentId, true);
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
