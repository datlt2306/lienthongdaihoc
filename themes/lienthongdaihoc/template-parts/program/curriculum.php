<?php
/**
 * Program Curriculum Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id       = isset( $args['program_id'] ) ? (int) $args['program_id'] : get_the_ID();
$curriculum_info  = function_exists( 'ltdh_get_program_curriculum_info' ) ? ltdh_get_program_curriculum_info( $program_id ) : null;
$curriculum_url   = $curriculum_info['url'] ?? '';
$curriculum_type  = $curriculum_info['type'] ?? 'file';
$curriculum_title = $curriculum_info['title'] ?? ( 'Khung chương trình: ' . get_the_title( $program_id ) );

if ( empty( $curriculum_url ) ) {
	return;
}
?>

<!-- SECTION 7.5: CURRICULUM ROADMAP FILE/IMAGE -->
<section class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6" id="lo-trinh-hoc">
	<div class="border-b border-slate-100 pb-3 mb-4">
		<h2 class="text-lg md:text-2xl font-bold text-slate-900">Khung chương trình</h2>
		<p class="text-xs md:text-sm text-slate-500 mt-0.5">Khung chương trình đào tạo chính thức áp dụng cho khóa học này</p>
	</div>

	<?php if ( $curriculum_type === 'image' ) : ?>
		<!-- Clickable Label Card for Image (Responsive & Touch-Friendly) -->
		<a href="<?php echo esc_url( $curriculum_url ); ?>" target="_blank" rel="nofollow noopener noreferrer" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 p-3.5 sm:p-4 bg-slate-50/90 hover:bg-blue-50/60 rounded-xl sm:rounded-2xl border border-slate-100 hover:border-blue-200/80 transition-all duration-200 group shadow-2xs hover:shadow-md block">
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
			<a href="<?php echo esc_url( $curriculum_url ); ?>" target="_blank" rel="nofollow noopener noreferrer" class="ltdh-lead-magnet-btn w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#0071E3] hover:bg-[#0077ED] text-white text-xs font-medium tracking-tight rounded-full shadow-none hover:shadow-xs active:scale-98 transition-all shrink-0 cursor-pointer" data-file-url="<?php echo esc_url( $curriculum_url ); ?>" data-file-title="Khung chương trình: <?php echo esc_attr( get_the_title( $program_id ) ); ?>">
				<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
				<span>Tải Khung Chương Trình (PDF)</span>
			</a>
		</div>
	<?php endif; ?>
</section>
