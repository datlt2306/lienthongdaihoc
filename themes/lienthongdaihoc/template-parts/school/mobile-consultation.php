<?php
/**
 * School Mobile Quick Consultation Callout
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$school_title = isset( $args['school_title'] ) ? $args['school_title'] : get_the_title();
$zalo_group   = isset( $args['zalo_group'] ) ? $args['zalo_group'] : ( get_field( 'zalo_group_url' ) ?: ltdh_get_zalo_url() );
?>

<!-- MOBILE MID-PAGE QUICK CONSULTATION CALLOUT (< 1024px) -->
<div class="block lg:hidden bg-gradient-to-r from-[#00308b] via-blue-900 to-indigo-950 text-white rounded-2xl p-4 sm:p-5 my-6 shadow-md border border-blue-700/30">
	<div class="flex items-center gap-3 mb-2">
		<div class="w-9 h-9 rounded-xl bg-[#EA580C] text-white flex items-center justify-center font-bold text-lg shrink-0 shadow-xs">
			⚡
		</div>
		<div>
			<span class="text-[10px] uppercase font-extrabold tracking-wider text-blue-200 block">Tư vấn tuyển sinh 2026</span>
			<h3 class="font-extrabold text-sm sm:text-base text-white leading-tight">
				Nhận lộ trình Liên thông tại <?php echo esc_html( $school_title ); ?>
			</h3>
		</div>
	</div>
	<p class="text-xs text-blue-100/90 leading-relaxed mb-3.5">
		Hỗ trợ tư vấn chọn ngành, miễn giảm học phần và thông tin tuyển sinh mới nhất hoàn toàn miễn phí.
	</p>
	<div class="flex items-center gap-2">
		<a href="#register" class="flex-1 bg-[#0071E3] hover:bg-[#0077ED] text-white font-medium text-xs py-2.5 px-4 rounded-full shadow-none hover:shadow-xs text-center transition-all active:scale-98 tracking-tight">
			📝 Đăng ký tư vấn ngay
		</a>
		<a href="<?php echo esc_url( $zalo_group ); ?>" target="_blank" rel="noopener noreferrer" class="bg-white/15 hover:bg-white/25 text-white font-medium text-xs py-2.5 px-4 rounded-full text-center transition-all border border-white/20 shrink-0 active:scale-98 tracking-tight">
			💬 Group Zalo
		</a>
	</div>
</div>
