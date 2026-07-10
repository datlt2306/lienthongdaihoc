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
<div id="ltdh-compare-tray" class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-200 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] transition-all hidden">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
		<div class="flex items-center justify-between gap-4">
			<div class="flex items-center gap-3 min-w-0 flex-1">
				<div class="flex items-center gap-1.5 shrink-0">
					<span class="text-lg">📊</span>
					<span class="text-sm font-bold text-slate-800">So sánh</span>
					<span class="ltdh-tray-count text-xs font-bold bg-brand-primary text-white px-1.5 py-0.5 rounded-lg">0/4</span>
				</div>
				<div class="ltdh-tray-items flex items-center gap-2 overflow-x-auto min-w-0"></div>
			</div>
			<div class="flex items-center gap-2 shrink-0">
				<button onclick="document.getElementById('ltdh-compare-tray').classList.add('hidden'); sessionStorage.removeItem('<?php echo esc_js( 'ltdh_compare_items' ); ?>');"
					class="text-xs text-slate-400 hover:text-red-500 font-semibold whitespace-nowrap">
					Xóa tất cả
				</button>
				<a href="#"
				   class="ltdh-tray-link inline-flex items-center gap-1.5 bg-brand-primary text-white text-sm font-bold px-5 py-2.5 rounded-lg shadow-md shadow-brand-primary/20 hover:bg-teal-700 transition-all opacity-50 pointer-events-none">
					So sánh ngay →
				</a>
			</div>
		</div>
	</div>
</div>
