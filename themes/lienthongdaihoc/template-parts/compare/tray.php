<?php
/**
 * Floating Compare Tray — appears on listing and detail pages.
 *
 * @package lienthongdaihoc
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="ltdh-compare-tray" class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-200/90 shadow-[0_-4px_25px_rgba(0,0,0,0.12)] transition-all duration-300 hidden">
	<div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 pt-2.5 sm:pt-3 pb-[calc(0.625rem+env(safe-area-inset-bottom,0px))] sm:pb-3">
		
		<!-- Mobile Top Meta Bar (Visible only on < md) -->
		<div class="flex md:hidden items-center justify-between gap-2 pb-2 mb-2 border-b border-slate-100">
			<div class="flex items-center gap-1.5 min-w-0 flex-1">
				<span class="text-base shrink-0">📊</span>
				<span class="text-xs font-bold text-slate-800 shrink-0">So sánh</span>
				<span class="ltdh-tray-count text-[11px] font-extrabold bg-[#00308b] text-white px-1.5 py-0.5 rounded-full shrink-0">0/4</span>
				<span class="ltdh-tray-major hidden items-center text-[11px] font-bold bg-blue-50 text-[#00308b] border border-blue-200/60 px-2 py-0.5 rounded-md truncate max-w-[140px] sm:max-w-[200px]" title=""></span>
			</div>
			<button type="button" onclick="window.ltdhCompare && window.ltdhCompare.clearAll();"
				class="text-[11px] text-slate-400 hover:text-red-500 font-semibold whitespace-nowrap px-1.5 py-1 min-h-[32px] flex items-center gap-1 shrink-0 transition-colors">
				<svg class="w-3.5 h-3.5 text-slate-400 hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
				<span>Xóa tất cả</span>
			</button>
		</div>

		<!-- Main Comparison Row -->
		<div class="flex items-center justify-between gap-2 sm:gap-4">
			
			<!-- Desktop Title & Meta (Hidden on mobile, visible on md+) -->
			<div class="hidden md:flex items-center gap-2.5 shrink-0">
				<span class="text-lg">📊</span>
				<div class="flex items-center gap-1.5">
					<span class="text-sm font-bold text-slate-800">So sánh</span>
					<span class="ltdh-tray-major hidden items-center text-xs font-bold bg-brand-primary/10 text-brand-primary px-2 py-0.5 rounded-md max-w-[200px] truncate" title=""></span>
				</div>
				<span class="ltdh-tray-count text-xs font-bold bg-brand-primary text-white px-2 py-0.5 rounded-lg">0/4</span>
			</div>

			<!-- Comparison Items (Scrollable chips) -->
			<div class="ltdh-tray-items flex items-center gap-2 overflow-x-auto min-w-0 flex-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden py-0.5"></div>

			<!-- Actions (Clear + CTA Button) -->
			<div class="flex items-center gap-2 sm:gap-3 shrink-0">
				<button type="button" onclick="window.ltdhCompare && window.ltdhCompare.clearAll();"
					class="hidden md:flex text-xs text-slate-400 hover:text-red-500 font-semibold whitespace-nowrap min-h-[44px] px-3 items-center justify-center transition-colors">
					Xóa tất cả
				</button>
				<a href="#"
				   class="ltdh-tray-link inline-flex items-center justify-center gap-1.5 bg-brand-accent text-white text-xs sm:text-sm font-bold px-3.5 sm:px-5 py-2.5 sm:py-3 min-h-[40px] sm:min-h-[44px] rounded-xl shadow-md shadow-brand-accent/25 hover:bg-[#e06e00] transition-all opacity-50 pointer-events-none whitespace-nowrap">
					<span>So sánh ngay</span>
					<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
				</a>
			</div>

		</div>
	</div>
</div>
