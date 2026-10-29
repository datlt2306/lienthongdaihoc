<?php
/**
 * Homepage Search & Filter Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filter_opts = function_exists( 'ltdh_get_cached_filter_options' ) ? ltdh_get_cached_filter_options() : [];
$schools     = $filter_opts['schools'] ?? [];
$majors      = $filter_opts['majors'] ?? [];
$types       = $filter_opts['types'] ?? [];
?>

<!-- Search & Filters Container (Apple Cupertino Search Pod & Compact Mobile Trigger) -->
<section class="bg-[#F5F5F7] py-4 sm:py-0">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 sm:-mt-8 relative z-30">
		
		<!-- MOBILE COMPACT SEARCH TRIGGER BAR (Visible only on < md) -->
		<div class="md:hidden pt-1 sm:pt-0">
			<button type="button" 
			        id="ltdh-mobile-home-filter-btn"
			        aria-expanded="false" 
			        aria-controls="ltdh-home-filter-drawer"
			        class="w-full bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] hover:shadow-md border border-black/[0.08] p-3 flex items-center justify-between gap-3 text-left transition-all active:scale-[0.99] cursor-pointer">
				<div class="flex items-center gap-2.5 min-w-0">
					<div class="w-9 h-9 rounded-full bg-[#0071E3]/10 text-[#0071E3] flex items-center justify-center shrink-0">
						<?php ltdh_icon( 'magnifyingglass', 'w-4.5 h-4.5' ); ?>
					</div>
					<div class="min-w-0">
						<span class="block text-xs sm:text-sm font-semibold text-[#1D1D1F] truncate">Tìm kiếm ngành &amp; trường học</span>
						<span class="block text-[11px] text-[#86868B] truncate">Chọn trường, ngành, hình thức đào tạo...</span>
					</div>
				</div>
				<div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#0071E3] text-white text-xs font-medium shrink-0">
					<?php ltdh_icon( 'slider.horizontal.3', 'w-3.5 h-3.5' ); ?>
					<span>Bộ lọc</span>
				</div>
			</button>
		</div>

		<!-- DESKTOP FILTER BAR (Apple Cupertino Pill Pod) -->
		<div id="home-desktop-filter-wrapper" class="hidden md:block bg-white rounded-3xl shadow-[0_12px_40px_rgba(0,0,0,0.06)] border border-black/[0.08] p-4 sm:p-5">
			<form action="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" id="home-filter-form" method="GET" class="space-y-4 md:space-y-0 md:grid md:grid-cols-5 md:gap-3 md:items-center">
				<!-- Keyword -->
				<div class="w-full">
					<label for="home-search-keyword" class="block md:sr-only text-xs font-semibold uppercase text-[#86868B] mb-1.5"><?php esc_html_e( 'Từ khóa tìm kiếm', 'ltdh' ); ?></label>
					<div class="relative">
						<span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#86868B]">
							<?php ltdh_icon( 'magnifyingglass', 'w-4 h-4' ); ?>
						</span>
						<input type="text" id="home-search-keyword" name="s" placeholder="Từ khóa tìm kiếm..." class="w-full pl-10 pr-4 py-2.5 border border-black/[0.08] rounded-full text-sm focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 focus:outline-none placeholder-[#86868B] min-h-[44px] transition-all bg-[#F5F5F7] focus:bg-white text-[#1D1D1F]" />
					</div>
				</div>

				<!-- School Select -->
				<div class="w-full">
					<label for="home-filter-truong" class="block md:sr-only text-xs font-semibold uppercase text-[#86868B] mb-1.5"><?php esc_html_e( 'Chọn trường', 'ltdh' ); ?></label>
					<select id="home-filter-truong" name="truong" class="w-full py-2.5 px-3.5 border border-black/[0.08] rounded-full text-sm font-medium bg-[#F5F5F7] focus:bg-white text-[#1D1D1F] focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 focus:outline-none cursor-pointer min-h-[44px]">
						<option value="">-- Chọn trường --</option>
						<?php foreach ( $schools as $sc ) : ?>
							<option value="<?php echo esc_attr( $sc['slug'] ); ?>"><?php echo esc_html( $sc['title'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- Major Select -->
				<div class="w-full">
					<label for="home-filter-nganh" class="block md:sr-only text-xs font-semibold uppercase text-[#86868B] mb-1.5"><?php esc_html_e( 'Chọn ngành học', 'ltdh' ); ?></label>
					<select id="home-filter-nganh" name="nganh" class="w-full py-2.5 px-3.5 border border-black/[0.08] rounded-full text-sm font-medium bg-[#F5F5F7] focus:bg-white text-[#1D1D1F] focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 focus:outline-none cursor-pointer min-h-[44px]">
						<option value="">-- Chọn ngành học --</option>
						<?php foreach ( $majors as $mj ) : ?>
							<option value="<?php echo esc_attr( $mj['slug'] ); ?>"><?php echo esc_html( $mj['title'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- Training Mode Select -->
				<div class="w-full">
					<label for="home-filter-he" class="block md:sr-only text-xs font-semibold uppercase text-[#86868B] mb-1.5"><?php esc_html_e( 'Hình thức đào tạo', 'ltdh' ); ?></label>
					<select name="he" id="home-filter-he" class="w-full py-2.5 px-3.5 border border-black/[0.08] rounded-full text-sm font-medium bg-[#F5F5F7] focus:bg-white text-[#1D1D1F] focus:border-[#0071E3] focus:ring-2 focus:ring-[#0071E3]/20 focus:outline-none cursor-pointer min-h-[44px]">
						<option value="">-- Chọn hình thức học --</option>
						<?php foreach ( $types as $tp ) : ?>
							<option value="<?php echo esc_attr( $tp['slug'] ); ?>"><?php echo esc_html( $tp['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- Submit Buttons (Apple Pill Action) -->
				<div class="hidden md:flex gap-2 w-full">
					<button type="submit" class="flex-1 bg-[#0071E3] hover:bg-[#0077ED] active:scale-98 text-white font-medium text-sm px-5 py-2.5 rounded-full transition-all tracking-tight min-h-[44px] shadow-none hover:shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
						<span>Tìm kiếm</span>
					</button>
					<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" id="home-filter-reset-desktop" class="p-2.5 bg-[#F5F5F7] hover:bg-[#E8E8ED] active:scale-95 text-[#6E6E73] hover:text-[#1D1D1F] rounded-full transition-all flex items-center justify-center min-h-[44px] w-11 shrink-0 cursor-pointer group" title="Reset bộ lọc" aria-label="Reset bộ lọc">
						<svg class="w-4 h-4 text-[#86868B] group-hover:text-[#1D1D1F] transition-transform duration-300 group-hover:-rotate-90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 0 0 4.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 0 1-15.357-2m15.357 2H15" />
						</svg>
					</a>
				</div>
			</form>
		</div>
	</div>

	<!-- OFF-CANVAS SEARCH DRAWER (SLIDES IN FROM LEFT ON MOBILE) -->
	<div id="ltdh-home-filter-drawer-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[70] transition-opacity duration-300 opacity-0 pointer-events-none md:hidden touch-none" style="touch-action: none;" aria-hidden="true"></div>

	<div id="ltdh-home-filter-drawer" 
	       class="fixed inset-y-0 left-0 w-[86%] max-w-[340px] bg-white shadow-2xl z-[80] transform -translate-x-full transition-transform duration-300 ease-out flex flex-col md:hidden"
	       role="dialog" 
	       aria-modal="true" 
	       aria-label="Bộ lọc tìm kiếm chương trình tuyển sinh">

		<!-- Drawer Top Header -->
		<div class="px-4 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/90 shrink-0 touch-none" style="touch-action: none;">
			<div class="flex items-center gap-2">
				<?php ltdh_icon( 'slider.horizontal.3', 'w-5 h-5 text-brand-primary shrink-0' ); ?>
				<h3 class="font-extrabold text-slate-900 text-sm sm:text-base">Bộ lọc tìm kiếm</h3>
			</div>
			<button type="button" 
					id="ltdh-home-filter-drawer-close" 
					class="p-2 -mr-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 active:scale-95 transition-all cursor-pointer"
					aria-label="Đóng bộ lọc">
				<?php ltdh_icon( 'xmark', 'w-5 h-5' ); ?>
			</button>
		</div>

		<!-- Drawer Scrollable Content Wrapper -->
		<div id="home-drawer-filter-wrapper" class="flex-1 overflow-y-auto overscroll-contain p-4 space-y-4" style="overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">
			<!-- #home-filter-form relocated here on mobile via JS -->
		</div>

		<!-- Drawer Sticky Footer -->
		<div class="p-3.5 border-t border-slate-100 bg-white flex items-center gap-2.5 shrink-0 shadow-lg touch-none" style="touch-action: none;">
			<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" 
			   id="ltdh-home-drawer-reset-btn"
			   class="flex-1 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 font-bold text-xs text-center transition-all cursor-pointer no-underline block"
			   title="Reset bộ lọc">
				Xóa lọc
			</a>
			<button type="button" 
					id="ltdh-home-drawer-submit-btn" 
					class="flex-2 py-2.5 px-4 rounded-xl bg-[#00308b] hover:bg-blue-900 active:scale-95 text-white font-extrabold text-xs text-center shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
				<?php ltdh_icon( 'magnifyingglass', 'w-4 h-4 text-white shrink-0' ); ?>
				<span>Tìm kiếm ngay</span>
			</button>
		</div>
	</div>

	<script>
	(function() {
		function setupHomeFilterDrawer() {
			var openBtn = document.getElementById('ltdh-mobile-home-filter-btn');
			var closeBtn = document.getElementById('ltdh-home-filter-drawer-close');
			var drawer = document.getElementById('ltdh-home-filter-drawer');
			var overlay = document.getElementById('ltdh-home-filter-drawer-overlay');
			var submitBtn = document.getElementById('ltdh-home-drawer-submit-btn');
			var resetBtn = document.getElementById('ltdh-home-drawer-reset-btn');
			var desktopWrapper = document.getElementById('home-desktop-filter-wrapper');
			var drawerWrapper = document.getElementById('home-drawer-filter-wrapper');
			var filterForm = document.getElementById('home-filter-form');

			var touchStartY = 0;
			function onTouchStart(e) {
				if (e.touches && e.touches.length === 1) {
					touchStartY = e.touches[0].clientY;
				}
			}

			function onTouchMoveLock(e) {
				var scrollable = e.target.closest('#home-drawer-filter-wrapper');
				if (!scrollable) {
					if (e.cancelable) e.preventDefault();
					return;
				}
				if (e.touches && e.touches.length === 1) {
					var currentY = e.touches[0].clientY;
					var deltaY = currentY - touchStartY;
					if (scrollable.scrollTop <= 0 && deltaY > 0) {
						if (e.cancelable) e.preventDefault();
						return;
					}
					var maxScroll = scrollable.scrollHeight - scrollable.clientHeight;
					if (scrollable.scrollTop >= maxScroll && deltaY < 0) {
						if (e.cancelable) e.preventDefault();
						return;
					}
				}
			}

			function relocate() {
				if (!filterForm) return;
				if (window.innerWidth < 768) {
					if (drawerWrapper && !drawerWrapper.contains(filterForm)) {
						drawerWrapper.appendChild(filterForm);
					}
				} else {
					if (desktopWrapper && !desktopWrapper.contains(filterForm)) {
						desktopWrapper.appendChild(filterForm);
					}
					closeDrawer();
				}
			}

			function openDrawer() {
				if (!drawer || !overlay) return;
				relocate();
				drawer.classList.remove('-translate-x-full');
				drawer.classList.add('translate-x-0');
				overlay.classList.remove('opacity-0', 'pointer-events-none');
				overlay.classList.add('opacity-100', 'pointer-events-auto');
				document.documentElement.classList.add('overflow-hidden');
				document.body.classList.add('overflow-hidden');
				document.addEventListener('touchstart', onTouchStart, { passive: true });
				document.addEventListener('touchmove', onTouchMoveLock, { passive: false });
				if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
			}

			function closeDrawer() {
				if (!drawer || !overlay) return;
				drawer.classList.remove('translate-x-0');
				drawer.classList.add('-translate-x-full');
				overlay.classList.remove('opacity-100', 'pointer-events-auto');
				overlay.classList.add('opacity-0', 'pointer-events-none');
				document.documentElement.classList.remove('overflow-hidden');
				document.body.classList.remove('overflow-hidden');
				document.removeEventListener('touchstart', onTouchStart);
				document.removeEventListener('touchmove', onTouchMoveLock);
				if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
			}

			if (overlay) {
				overlay.addEventListener('touchmove', function(e) {
					if (e.cancelable) e.preventDefault();
				}, { passive: false });
				overlay.addEventListener('wheel', function(e) {
					if (e.cancelable) e.preventDefault();
				}, { passive: false });
			}

			if (openBtn) openBtn.onclick = openDrawer;
			if (closeBtn) closeBtn.onclick = closeDrawer;
			if (overlay) overlay.onclick = closeDrawer;

			if (submitBtn && filterForm) {
				submitBtn.onclick = function() {
					filterForm.submit();
				};
			}

			var desktopResetBtn = document.getElementById('home-filter-reset-desktop');
			function clearHomeFilters() {
				var keyword = document.getElementById('home-search-keyword');
				var truong = document.getElementById('home-filter-truong');
				var nganh = document.getElementById('home-filter-nganh');
				var he = document.getElementById('home-filter-he');
				if (keyword) keyword.value = '';
				if (truong) truong.value = '';
				if (nganh) nganh.value = '';
				if (he) he.value = '';
			}

			if (desktopResetBtn) {
				desktopResetBtn.onclick = function(e) {
					e.preventDefault();
					clearHomeFilters();
				};
			}

			if (resetBtn) {
				resetBtn.onclick = function() {
					clearHomeFilters();
				};
			}

			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape') closeDrawer();
			});

			relocate();
			window.addEventListener('resize', relocate);
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', setupHomeFilterDrawer);
		} else {
			setupHomeFilterDrawer();
		}
	})();
	</script>
</section>
