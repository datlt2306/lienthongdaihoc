<?php
/**
 * Template part: Bento Contact & Community Tiles (Apple Minimal / Bento Grid)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hotline       = ! empty( $args['hotline'] ) ? $args['hotline'] : ( function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496' );
$zalo_url      = ! empty( $args['zalo_url'] ) ? $args['zalo_url'] : ( function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me' );
$zalo_title    = ! empty( $args['zalo_title'] ) ? $args['zalo_title'] : 'Nhóm Zalo Trao Đổi';
$hotline_title = ! empty( $args['hotline_title'] ) ? $args['hotline_title'] : 'Tuyển sinh 24/7';
$clean_phone   = preg_replace( '/\D/', '', $hotline );
?>

<!-- BENTO CONTACT & COMMUNITY TILES -->
<div class="grid grid-cols-2 gap-2.5 sm:gap-3">
	<!-- Tile 1: Hotline 24/7 (Apple Dark Bento) -->
	<div class="bg-gradient-to-br from-[#0c192c] to-[#0E2038] text-white rounded-2xl p-3.5 sm:p-4 flex flex-col justify-between shadow-xs border border-slate-800/80 relative overflow-hidden group">
		<div class="absolute -right-4 -bottom-4 w-16 h-16 bg-[#f5bf23]/10 rounded-full blur-lg pointer-events-none"></div>
		<div>
			<div class="flex items-center justify-between gap-1 mb-1.5">
				<span class="text-[9px] sm:text-[10px] text-[#f5bf23] font-extrabold uppercase tracking-wider truncate"><?php echo esc_html( $hotline_title ); ?></span>
				<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse shrink-0" title="Đang online"></span>
			</div>
			<a href="tel:<?php echo esc_attr( $clean_phone ); ?>" class="block font-black text-xs sm:text-sm md:text-base text-white hover:text-[#f5bf23] transition-colors tracking-tight leading-tight mb-2 truncate">
				<?php echo esc_html( $hotline ); ?>
			</a>
		</div>
		<a href="tel:<?php echo esc_attr( $clean_phone ); ?>"
		   class="w-full bg-[#f97316] hover:bg-[#ea580c] active:scale-95 text-white py-2 px-3 rounded-full font-medium text-[11px] sm:text-xs tracking-tight flex items-center justify-center gap-1.5 shadow-none hover:shadow-xs transition-all text-center">
			<?php ltdh_icon( 'phone.fill', 'w-3.5 h-3.5 shrink-0' ); ?>
			<span class="truncate">Gọi Ngay</span>
		</a>
	</div>

	<!-- Tile 2: Nhóm Zalo Trao Đổi (Apple Vibrant Blue Bento) -->
	<div class="bg-gradient-to-br from-[#0052cc] to-[#003d99] text-white rounded-2xl p-3.5 sm:p-4 flex flex-col justify-between shadow-xs border border-blue-400/20 relative overflow-hidden group">
		<div class="absolute -right-4 -bottom-4 w-16 h-16 bg-white/10 rounded-full blur-lg pointer-events-none"></div>
		<div>
			<div class="flex items-center justify-between gap-1 mb-1.5">
				<span class="text-[9px] sm:text-[10px] text-blue-200 font-extrabold uppercase tracking-wider truncate">Cộng đồng SV</span>
				<span class="text-xs">💬</span>
			</div>
			<div class="font-extrabold text-xs sm:text-sm text-white leading-tight mb-2 line-clamp-2">
				<?php echo esc_html( $zalo_title ); ?>
			</div>
		</div>
		<a href="<?php echo esc_url( $zalo_url ); ?>"
		   target="_blank"
		   rel="noopener noreferrer"
		   class="w-full bg-white hover:bg-blue-50 active:scale-95 text-[#003d99] py-2 px-3 rounded-full font-medium text-[11px] sm:text-xs tracking-tight flex items-center justify-center gap-1 shadow-none hover:shadow-xs transition-all text-center">
			<span class="truncate">Vào Nhóm</span>
			<span class="text-[10px]">➔</span>
		</a>
	</div>
</div>
