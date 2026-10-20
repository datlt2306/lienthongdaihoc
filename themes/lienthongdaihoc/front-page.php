<?php

/**
 * Main index template (Homepage fully optimized to match Mockup UI/UX)
 *
 * @package lienthongdaihoc
 */

if (! defined('ABSPATH')) {
	exit;
}

get_header();

// Cache queries for schools
$featured_schools = ltdh_get_cached_featured_schools();

$news_query = ltdh_get_cached_query('ltdh_homepage_news', [
	'post_type'      => 'post',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
	'no_found_rows'  => true,
], HOUR_IN_SECONDS);

$hotline = ltdh_get_hotline();
$zalo    = ltdh_get_zalo_url();
?>

<main id="primary" class="site-main bg-white">
	<!-- H1 Semantic Heading for SEO & Screen Readers -->
	<h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hệ Chính Quy, Vừa Học Vừa Làm & Đào Tạo Từ Xa</h1>

	<!-- 1. HERO SECTION (Swiper Banner Slider) -->
	<?php
	$hero_slides = [];
	if (function_exists('get_field')) {
		$hero_slides = get_field('hero_slides', 'options') ?: [];
	}
	// Fallback if empty
	if (empty($hero_slides)) {
		$hero_slides = [
			[
				'image' => ltdh_get_fallback_image('hero'),
				'link'  => '',
			]
		];
	}
	?>
	<section class="relative w-full overflow-hidden bg-slate-50 border-b border-slate-100">
		<div class="swiper hero-swiper w-full min-h-[220px] sm:min-h-[350px] md:min-h-[450px]">
			<div class="swiper-wrapper">
				<?php 
				$slide_idx = 0;
				foreach ($hero_slides as $slide) : 
					if (empty($slide['image'])) continue;
					$slide_img_desktop = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide['image'] ) : $slide['image'];
					$slide_img_1400    = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide['image'], '1400w' ) : '';
					$slide_img_mobile  = ! empty( $slide['image_mobile'] ) ? ( function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide['image_mobile'], true ) : $slide['image_mobile'] ) : ( function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide['image'], true ) : '' );
					$is_lcp            = ( 0 === $slide_idx );
					$loading_attr      = $is_lcp ? 'loading="eager" fetchpriority="high" data-no-lazy="1"' : 'loading="lazy" fetchpriority="low"';
					$hero_cls          = $is_lcp ? 'hero-banner hero-img skip-lazy no-lazy ' : '';
					?>

					<div class="swiper-slide w-full">
						<?php if (!empty($slide['link'])) : ?>
							<a href="<?php echo esc_url($slide['link']); ?>" class="block w-full h-full">
						<?php endif; ?>
						
						<div class="relative w-full h-auto min-h-[220px] sm:min-h-[350px] md:min-h-[450px] max-h-[800px] overflow-hidden bg-[#f8fafc]">
							<!-- Main Banner Image -->
							<picture class="relative z-10 block w-full h-auto max-h-[800px] aspect-[1942/809]" style="aspect-ratio: 1942/809;">
								<?php if ( ! empty( $slide_img_mobile ) && $slide_img_mobile !== $slide_img_desktop ) : ?>
									<source media="(max-width: 768px)" srcset="<?php echo esc_url( $slide_img_mobile ); ?>" width="768" height="320">
								<?php endif; ?>
								<?php if ( ! empty( $slide_img_1400 ) && $slide_img_1400 !== $slide_img_desktop ) : ?>
									<source media="(max-width: 1440px)" srcset="<?php echo esc_url( $slide_img_1400 ); ?>" width="1400" height="584">
								<?php endif; ?>
								<img src="<?php echo esc_url( $slide_img_desktop ); ?>" width="1942" height="809" alt="Banner Hero" class="<?php echo $hero_cls; ?>w-full h-auto max-h-[800px] aspect-[1942/809] object-cover object-center pointer-events-none" style="aspect-ratio: 1942/809;" <?php echo $loading_attr; ?> decoding="async">
							</picture>
						</div>


						<?php if (!empty($slide['link'])) : ?>
							</a>
						<?php endif; ?>
					</div>
				<?php 
					$slide_idx++;
				endforeach; 
				?>
			</div>
			<!-- Add Pagination -->
			<div class="swiper-pagination"></div>
			<!-- Add Navigation Arrows -->
			<div class="swiper-button-next !text-white after:!text-base bg-black/30 hover:bg-black/50 !w-8 md:!w-10 !h-8 md:!h-10 rounded-full transition-all flex items-center justify-center"></div>
			<div class="swiper-button-prev !text-white after:!text-base bg-black/30 hover:bg-black/50 !w-8 md:!w-10 !h-8 md:!h-10 rounded-full transition-all flex items-center justify-center"></div>
		</div>
	</section>


	<!-- Styling specifically for pagination position, arrows and critical layout -->
	<style>
		.hero-swiper {
			position: relative;
			overflow: hidden;
			list-style: none;
			padding: 0;
			z-index: 1;
			display: block;
		}
		.hero-swiper .swiper-wrapper {
			position: relative;
			width: 100%;
			height: 100%;
			z-index: 1;
			display: flex;
			transition-property: transform;
			box-sizing: content-box;
		}
		.hero-swiper .swiper-slide {
			flex-shrink: 0;
			width: 100%;
			height: 100%;
			position: relative;
			transition-property: transform;
			display: block;
		}
		.hero-swiper .swiper-pagination-bullet-active {
			background: #f5bf23 !important;
			width: 24px;
			border-radius: 4px;
		}
		.hero-swiper .swiper-pagination-bullet {
			transition: all 0.3s ease;
		}
		.hero-swiper .swiper-button-next, .hero-swiper .swiper-button-prev {
			opacity: 0;
			margin-top: -16px;
			transition: all 0.3s ease;
		}
		.hero-swiper:hover .swiper-button-next, .hero-swiper:hover .swiper-button-prev {
			opacity: 1;
		}
		.hero-swiper .swiper-button-next {
			right: 20px;
		}
		.hero-swiper .swiper-button-prev {
			left: 20px;
		}
	</style>


	<!-- Search & Filters Container (Compact Mobile Trigger + Desktop Bar) -->
	<section class="bg-slate-50">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 sm:-mt-8 relative z-30">
			
			<!-- MOBILE COMPACT SEARCH TRIGGER BAR (Visible only on < md) -->
			<div class="md:hidden pt-3 sm:pt-0">
				<button type="button" 
				        id="ltdh-mobile-home-filter-btn"
				        aria-expanded="false"
				        aria-controls="ltdh-home-filter-drawer"
				        class="w-full bg-white rounded-2xl shadow-md hover:shadow-lg border border-slate-200/90 p-3 flex items-center justify-between gap-3 text-left transition-all active:scale-[0.99] cursor-pointer">
					<div class="flex items-center gap-2.5 min-w-0">
						<div class="w-9 h-9 rounded-xl bg-blue-50 text-brand-primary flex items-center justify-center shrink-0">
							<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
							</svg>
						</div>
						<div class="min-w-0">
							<span class="block text-xs sm:text-sm font-bold text-slate-800 truncate">Tìm kiếm ngành &amp; trường học</span>
							<span class="block text-[11px] text-slate-400 truncate">Chọn trường, ngành, hình thức đào tạo...</span>
						</div>
					</div>
					<div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-primary text-white text-xs font-bold shrink-0 shadow-xs">
						<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
						</svg>
						<span>Bộ lọc</span>
					</div>
				</button>
			</div>

			<!-- DESKTOP FILTER BAR (Visible only on >= md) -->
			<div id="home-desktop-filter-wrapper" class="hidden md:block bg-white rounded-2xl shadow-xl border border-slate-100 p-4 sm:p-5">
				<?php
				$filter_opts = ltdh_get_cached_filter_options();
				$schools     = $filter_opts['schools'] ?? [];
				$majors      = $filter_opts['majors'] ?? [];
				$types       = $filter_opts['types'] ?? [];
				?>
				<form id="home-filter-form" action="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" method="GET" class="space-y-4 md:space-y-0 md:grid md:grid-cols-5 md:gap-3 md:items-center">
					<!-- Keyword -->
					<div class="w-full">
						<label for="home-search-keyword" class="block md:sr-only text-xs font-extrabold uppercase text-slate-500 mb-1.5"><?php esc_html_e( 'Từ khóa tìm kiếm', 'ltdh' ); ?></label>
						<div class="relative">
							<span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
								<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
							</span>
							<input type="text" id="home-search-keyword" name="s" placeholder="Từ khóa tìm kiếm..." class="w-full pl-10 pr-4 py-2.5 border border-slate-200/80 rounded-xl text-sm focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none placeholder-slate-400 min-h-[44px] transition-all bg-slate-50/50 focus:bg-white" />
						</div>
					</div>

					<!-- School Select -->
					<div class="w-full">
						<label for="home-filter-truong" class="block md:sr-only text-xs font-extrabold uppercase text-slate-500 mb-1.5"><?php esc_html_e( 'Chọn trường', 'ltdh' ); ?></label>
						<select id="home-filter-truong" name="truong" class="w-full py-2.5 px-3.5 border border-slate-200/80 rounded-xl text-sm font-medium bg-slate-50/50 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
							<option value="">-- Chọn trường --</option>
							<?php foreach ($schools as $sc) : ?>
								<option value="<?php echo esc_attr($sc['slug']); ?>"><?php echo esc_html($sc['title']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Major Select -->
					<div class="w-full">
						<label for="home-filter-nganh" class="block md:sr-only text-xs font-extrabold uppercase text-slate-500 mb-1.5"><?php esc_html_e( 'Chọn ngành học', 'ltdh' ); ?></label>
						<select id="home-filter-nganh" name="nganh" class="w-full py-2.5 px-3.5 border border-slate-200/80 rounded-xl text-sm font-medium bg-slate-50/50 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
							<option value="">-- Chọn ngành học --</option>
							<?php foreach ($majors as $mj) : ?>
								<option value="<?php echo esc_attr($mj['slug']); ?>"><?php echo esc_html($mj['title']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Training Mode Select -->
					<div class="w-full">
						<label for="home-filter-he" class="block md:sr-only text-xs font-extrabold uppercase text-slate-500 mb-1.5"><?php esc_html_e( 'Hình thức đào tạo', 'ltdh' ); ?></label>
						<select name="he" id="home-filter-he" class="w-full py-2.5 px-3.5 border border-slate-200/80 rounded-xl text-sm font-medium bg-slate-50/50 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
							<option value="">-- Chọn hình thức đào tạo --</option>
							<?php foreach ($types as $tp) : ?>
								<option value="<?php echo esc_attr($tp['slug']); ?>"><?php echo esc_html($tp['name']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Submit Buttons (Visible on desktop) -->
					<div class="hidden md:flex gap-2 w-full">
						<button type="submit" class="flex-1 bg-brand-primary hover:bg-brand-darkBlue active:scale-95 text-white font-extrabold text-sm px-5 py-2.5 rounded-xl transition-all uppercase tracking-wider min-h-[44px] shadow-sm cursor-pointer">
							Tìm kiếm
						</button>
						<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" class="p-2.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-500 rounded-xl transition-all flex items-center justify-center min-h-[44px] w-11 shrink-0" title="Reset bộ lọc">
							🔄
						</a>
					</div>
				</form>
			</div>
		</div>

		<!-- OFF-CANVAS SEARCH DRAWER (SLIDES IN FROM LEFT ON MOBILE) -->
		<div id="ltdh-home-filter-drawer-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[70] transition-opacity duration-300 opacity-0 pointer-events-none md:hidden touch-none" style="touch-action: none;" aria-hidden="true"></div>

		<aside id="ltdh-home-filter-drawer" 
		       class="fixed inset-y-0 left-0 w-[86%] max-w-[340px] bg-white shadow-2xl z-[80] transform -translate-x-full transition-transform duration-300 ease-out flex flex-col md:hidden"
		       role="dialog" 
		       aria-modal="true" 
		       aria-label="Bộ lọc tìm kiếm chương trình tuyển sinh">

			<!-- Drawer Top Header -->
			<div class="px-4 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/90 shrink-0 touch-none" style="touch-action: none;">
				<div class="flex items-center gap-2">
					<svg class="w-5 h-5 text-brand-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
					</svg>
					<h3 class="font-extrabold text-slate-900 text-sm sm:text-base">Bộ lọc tìm kiếm</h3>
				</div>
				<button type="button" 
						id="ltdh-home-filter-drawer-close" 
						class="p-2 -mr-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 active:scale-95 transition-all cursor-pointer"
						aria-label="Đóng bộ lọc">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
				</button>
			</div>

			<!-- Drawer Scrollable Content Wrapper -->
			<div id="home-drawer-filter-wrapper" class="flex-1 overflow-y-auto overscroll-contain p-4 space-y-4" style="overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">
				<!-- #home-filter-form relocated here on mobile via JS -->
			</div>

			<!-- Drawer Sticky Footer -->
			<div class="p-3.5 border-t border-slate-100 bg-white flex items-center gap-2.5 shrink-0 shadow-lg touch-none" style="touch-action: none;">
				<button type="button" 
						id="ltdh-home-drawer-reset-btn"
						class="flex-1 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 font-bold text-xs text-center transition-all cursor-pointer">
					Xóa lọc
				</button>
				<button type="button" 
						id="ltdh-home-drawer-submit-btn" 
						class="flex-2 py-2.5 px-4 rounded-xl bg-[#00308b] hover:bg-blue-900 active:scale-95 text-white font-extrabold text-xs text-center shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
					<svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
					</svg>
					<span>Tìm kiếm ngay</span>
				</button>
			</div>
		</aside>

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

				if (resetBtn && filterForm) {
					resetBtn.onclick = function() {
						var keyword = document.getElementById('home-search-keyword');
						var truong = document.getElementById('home-filter-truong');
						var nganh = document.getElementById('home-filter-nganh');
						var he = document.getElementById('home-filter-he');
						if (keyword) keyword.value = '';
						if (truong) truong.value = '';
						if (nganh) nganh.value = '';
						if (he) he.value = '';
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
	

	<!-- 2. HOT PROGRAMS SECTION -->
	<section class="py-8 sm:py-12 bg-slate-50 border-y border-slate-100 relative overflow-hidden">
		<div class="max-w-7xl mx-auto relative z-10 px-4 sm:px-6 lg:px-8">
			<div class="text-center max-w-2xl mx-auto mb-6 sm:mb-8 space-y-2">
				<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">KHÁM PHÁ NGÀNH HỌC</span>
				<h2 class="text-xl md:text-4xl font-black text-slate-900">5 ngành đào tạo hot nhất</h2>
				<p class="text-slate-500 text-sm">Top ngành được yêu thích nhất, phù hợp xu hướng thị trường lao động.</p>
			</div>

			<!-- Swiper Carousel Container (Mobile swipeable, Desktop 5 columns) -->
			<div class="relative -mx-4 sm:mx-0">
				<div class="swiper hot-majors-swiper overflow-hidden">
					<div class="swiper-wrapper py-2">
						<?php
						$hot_majors = ltdh_get_hot_majors();
						$card_colors = [
							['bg' => 'bg-blue-50',   'icon_bg' => 'bg-blue-100',   'icon_text' => 'text-blue-600',   'border' => 'border-blue-100',   'hover' => 'hover:border-blue-300'],
							['bg' => 'bg-violet-50', 'icon_bg' => 'bg-violet-100', 'icon_text' => 'text-violet-600', 'border' => 'border-violet-100', 'hover' => 'hover:border-violet-300'],
							['bg' => 'bg-emerald-50', 'icon_bg' => 'bg-emerald-100', 'icon_text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'hover' => 'hover:border-emerald-300'],
							['bg' => 'bg-amber-50',  'icon_bg' => 'bg-amber-100',  'icon_text' => 'text-amber-600',  'border' => 'border-amber-100',  'hover' => 'hover:border-amber-300'],
							['bg' => 'bg-rose-50',   'icon_bg' => 'bg-rose-100',   'icon_text' => 'text-rose-600',   'border' => 'border-rose-100',   'hover' => 'hover:border-rose-300'],
						];

						$major_icons = [
							'cong-nghe-thong-tin' => '💻',
							'quan-tri-kinh-doanh' => '📈',
							'ke-toan' => '💰',
							'thuong-mai-dien-tu' => '🏪',
							'logistics' => '🚚',
						];

						if (! empty($hot_majors)) :
							$i = 0;
							foreach ($hot_majors as $maj) :
								$slug = $maj->post_name;
								$icon = $major_icons[$slug] ?? '🎓';
								$c = $card_colors[$i % count($card_colors)];
								$i++;
						?>
								<div class="swiper-slide h-auto">
									<a href="<?php echo esc_url(get_permalink($maj->ID)); ?>"
										class="group flex flex-col items-center text-center p-4 sm:p-5 bg-white border <?php echo $c['border']; ?> rounded-2xl <?php echo $c['hover']; ?> hover:shadow-md active:scale-95 transition-all h-full">
										<div class="h-12 w-12 <?php echo $c['icon_bg']; ?> <?php echo $c['icon_text']; ?> rounded-xl flex items-center justify-center mb-3 text-xl group-hover:scale-110 transition-transform">
											<?php echo $icon; ?>
										</div>
										<h3 class="font-bold text-slate-800 text-sm sm:text-base leading-snug line-clamp-2"><?php echo esc_html($maj->post_title); ?></h3>
									</a>
								</div>
						<?php
							endforeach;
						else :
							echo '<p class="text-sm text-slate-400 col-span-5 text-center">Chưa cấu hình ngành hot.</p>';
						endif;
						?>
					</div>
					<!-- Mobile Pagination Dots -->
					<div class="swiper-pagination hot-majors-pagination !relative !bottom-0 mt-4 sm:hidden"></div>
				</div>
			</div>

			<div class="text-center mt-6 sm:mt-8">
				<a href="<?php echo esc_url(home_url('/nhom-nganh/')); ?>"
					class="inline-flex items-center gap-2 text-[#00308b] font-bold text-sm hover:underline">
					Xem tất cả ngành học <span>→</span>
				</a>
			</div>
		</div>
	</section>

	<!-- 3. NATIONAL SCHOOLS SECTION (CAROUSEL SLIDER) -->
	<section id="program-section" class="py-8 sm:py-12 md:py-16 bg-white overflow-hidden">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 sm:mb-8">
				<div class="space-y-2">
					<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">ĐỐI TÁC ĐẠI HỌC</span>
					<h2 class="text-xl md:text-3xl font-black text-slate-900">Trường đối tác đào tạo</h2>
				</div>
				<div class="flex items-center gap-3 mt-4 sm:mt-0">
					<a href="<?php echo esc_url(home_url('/truong-doi-tac/')); ?>" class="text-sm text-brand-primary font-bold hover:underline flex items-center gap-1">
						Xem tất cả
						<span>→</span>
					</a>
					<div class="hidden sm:flex items-center gap-2 pl-2 border-l border-slate-200">
						<button type="button" class="school-slider-prev w-9 h-9 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 flex items-center justify-center transition-all hover:scale-105 active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed shadow-xs" aria-label="Trường trước đó">
							<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
								<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
							</svg>
						</button>
						<button type="button" class="school-slider-next w-9 h-9 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 flex items-center justify-center transition-all hover:scale-105 active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed shadow-xs" aria-label="Trường tiếp theo">
							<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
								<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
							</svg>
						</button>
					</div>
				</div>
			</div>

			<!-- Swiper Carousel Container -->
			<div class="relative -mx-4 px-4 sm:mx-0 sm:px-0">
				<div class="swiper school-partner-swiper !overflow-visible sm:!overflow-hidden">
					<div class="swiper-wrapper py-3">
						<?php
						if ( ! empty( $featured_schools ) ) {
							foreach ( $featured_schools as $school ) :
								$school_id       = $school['id'];
								$address         = $school['address'];
								$hotline         = $school['hotline'];
								$thumb_url       = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'medium' ) : $school['thumb_url'];
								if ( function_exists( 'ltdh_get_optimized_image_url' ) ) {
									$thumb_url = ltdh_get_optimized_image_url( $thumb_url );
								}
								$logo_id         = $school['logo_id'];
								$school_code     = ! empty( $school['code'] ) ? $school['code'] : ( get_post_meta( $school_id, 'school_code', true ) ?: '' );
								$en_name         = ! empty( $school['en_name'] ) ? $school['en_name'] : get_post_meta( $school_id, 'english_name', true );
								if ( ( empty( $en_name ) || 'University' === $en_name ) && ! empty( $school_code ) ) {
									$en_name = $school_code . ' University';
								}
								$school_types    = ! empty( $school['school_types'] ) ? $school['school_types'] : ltdh_get_school_training_types( $school_id );
								$school_location = ! empty( $school['location'] ) ? $school['location'] : ltdh_get_school_location_label( $school_id );
								$school_region   = ! empty( $school['region'] ) ? $school['region'] : ltdh_get_school_region_label( $school_id );

								// Clean short address for footer info row
								$short_address = $school_location;
								if ( ! empty( $address ) && 'Việt Nam' !== $address ) {
									$addr_parts = array_map( 'trim', explode( ',', $address ) );
									if ( count( $addr_parts ) >= 2 ) {
										$short_address = implode( ', ', array_slice( $addr_parts, -2 ) );
									} else {
										$short_address = $address;
									}
								}
						?>
								<div class="swiper-slide h-auto flex">
									<div class="w-full bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
										<!-- Ảnh trường & Vị trí -->
										<div class="relative h-36 sm:h-40 bg-slate-200 overflow-hidden">
											<img src="<?php echo esc_url( $thumb_url ); ?>"
												 alt="<?php echo esc_attr( $school['title'] ?? 'Ảnh trường' ); ?>"
												 loading="lazy"
												 decoding="async"
												 width="300"
												 height="180"
												 class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
											<div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-slate-900/10 to-transparent pointer-events-none"></div>
											<?php if ( ! empty( $school_region ) ) : ?>
												<span class="absolute top-3 right-3 bg-slate-900/60 backdrop-blur-md text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full z-10 flex items-center gap-1 border border-white/20 shadow-xs">
													<svg class="w-3 h-3 text-white/80 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
														<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
														<path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
													</svg>
													<span><?php echo esc_html( $school_region ); ?></span>
												</span>
											<?php endif; ?>
										</div>

										<!-- Logo trường -->
										<div class="h-14 w-14 sm:h-16 sm:w-16 bg-white rounded-2xl border-2 border-white shadow-[0_4px_16px_rgba(0,0,0,0.12)] -mt-7 sm:-mt-8 ml-4 sm:ml-5 z-10 relative flex items-center justify-center overflow-hidden p-1.5 transition-transform duration-300 group-hover:scale-105">
											<?php if ($logo_id) : 
												$logo_alt = get_post_meta( $logo_id, '_wp_attachment_image_alt', true ) ?: ( 'Logo ' . ( $school['title'] ?? '' ) );
											?>
												<?php echo wp_get_attachment_image($logo_id, 'thumbnail', false, [
													'class'    => 'h-full w-full object-contain',
													'alt'      => $logo_alt,
													'width'    => 96,
													'height'   => 96,
													'sizes'    => '48px',
													'loading'  => 'lazy',
													'decoding' => 'async',
												]); ?>
											<?php else : ?>
												<span class="font-display font-extrabold text-[#00308b] text-sm">UNI</span>
											<?php endif; ?>
										</div>

										<!-- Nội dung thẻ -->
										<div class="p-4 sm:p-5 pt-2 flex-1 flex flex-col justify-between text-left">
											<div>
												<!-- Mã trường & Địa điểm -->
												<div class="flex items-center gap-2 mb-1.5 min-h-[20px]">
													<?php if ( ! empty( $school_code ) ) : ?>
														<span class="font-bold text-[#00308b] bg-blue-50 px-2 py-0.5 rounded text-[11px] border border-blue-100">Mã: <?php echo esc_html( $school_code ); ?></span>
													<?php endif; ?>
													<?php if ( ! empty( $school_location ) ) : ?>
														<span class="text-xs text-slate-500 font-medium truncate"><?php echo esc_html( $school_location ); ?></span>
													<?php endif; ?>
												</div>

												<!-- Tên trường -->
												<h3 class="font-extrabold text-slate-900 text-sm md:text-[15px] tracking-tight leading-snug line-clamp-2 mt-0.5 group-hover:text-[#00308b] transition-colors">
													<a href="<?php echo esc_url($school['permalink']); ?>">
														<?php echo esc_html($school['title']); ?>
													</a>
												</h3>

												<!-- Tên tiếng Anh -->
												<?php if ( ! empty( $en_name ) ) : ?>
													<p class="text-xs text-slate-400 font-medium line-clamp-1 italic mt-1" title="<?php echo esc_attr( $en_name ); ?>">
														<?php echo esc_html( $en_name ); ?>
													</p>
												<?php endif; ?>

												<!-- Địa chỉ trường -->
												<?php
												$display_address = ! empty( $address ) && 'Việt Nam' !== $address ? $address : ( ! empty( $school_location ) ? 'Cơ sở: ' . $school_location : '' );
												if ( ! empty( $display_address ) ) :
												?>
													<div class="mt-3 pt-2.5 border-t border-slate-100 flex items-start gap-1.5 text-xs text-slate-500 min-h-[36px]">
														<svg class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
															<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
															<path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
														</svg>
														<span class="line-clamp-2 leading-relaxed" title="<?php echo esc_attr( $display_address ); ?>">
															<?php echo esc_html( $display_address ); ?>
														</span>
													</div>
												<?php endif; ?>
											</div>

											<!-- Nút Tìm hiểu chi tiết -->
											<div class="mt-3.5 pt-3 border-t border-slate-100">
												<a href="<?php echo esc_url($school['permalink']); ?>" 
												   class="w-full text-center py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold text-[#00308b] bg-blue-50/90 hover:bg-[#00308b] hover:text-white border border-blue-200/80 hover:border-[#00308b] transition-all duration-200 flex items-center justify-center gap-1.5 shadow-2xs group-hover:shadow-sm">
													<span>Tìm hiểu chi tiết</span>
													<svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
														<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
													</svg>
												</a>
											</div>
										</div>
									</div>
								</div>
						<?php
							endforeach;
						} else {
							echo '<div class="w-full text-center text-slate-500 py-6">Chưa có trường đối tác nào được gieo dữ liệu.</div>';
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<style>
	.school-partner-swiper:not(.swiper-initialized) .swiper-wrapper {
		display: flex;
		overflow-x: auto;
		scroll-snap-type: x mandatory;
		gap: 1.25rem;
		scrollbar-width: none;
		-ms-overflow-style: none;
	}
	.school-partner-swiper:not(.swiper-initialized) .swiper-wrapper::-webkit-scrollbar {
		display: none;
	}
	.school-partner-swiper:not(.swiper-initialized) .swiper-slide {
		flex: 0 0 280px;
		scroll-snap-align: start;
	}
	@media (min-width: 1280px) {
		.school-partner-swiper:not(.swiper-initialized) .swiper-slide {
			flex: 0 0 calc((100% - 3 * 1.5rem) / 4);
		}
	}
	.school-slider-prev.swiper-button-disabled,
	.school-slider-next.swiper-button-disabled {
		opacity: 0.35;
		pointer-events: none;
		cursor: not-allowed;
	}
	</style>

	<!-- 4. ELIGIBILITY QUICK CHECK SECTION -->
	<section class="py-8 sm:py-12 md:py-16 bg-slate-50 border-t border-slate-100">
		<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
			<div class="space-y-4">
				<?php
				$e_badge = '';
				$e_heading = '';
				$e_desc = '';
				$e_items = [];
				$e_cta_text = '';
				$e_cta_url = '';
				if (function_exists('get_field')) {
					$e_badge = get_field('hp_elig_badge', 'options') ?: '';
					$e_heading = get_field('hp_elig_heading', 'options') ?: '';
					$e_desc = get_field('hp_elig_desc', 'options') ?: '';
					$e_items = get_field('hp_elig_items', 'options') ?: [];
					$e_cta_text = get_field('hp_elig_cta_text', 'options') ?: '';
					$e_cta_raw  = get_field('hp_elig_cta_url', 'options') ?: '/kiem-tra-dieu-kien/';
				} else {
					$e_cta_raw = '/kiem-tra-dieu-kien/';
				}
				$e_cta_url = ( preg_match( '#^(https?:)?//#i', $e_cta_raw ) ) ? $e_cta_raw : home_url( $e_cta_raw );
				$e_heading = str_replace('\n', '<br>', $e_heading);
				?>
				<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">
					<?php echo esc_html($e_badge ?: 'Điều kiện tuyển sinh'); ?>
				</span>
				<h2 class="text-2xl sm:text-3xl lg:text-4xl font-black font-display text-slate-900 leading-tight">
					<?php echo $e_heading ?: 'Bạn có đủ điều kiện học<br>Liên thông Đại học?'; ?>
				</h2>
				<p class="text-slate-500 text-sm leading-relaxed max-w-2xl mx-auto">
					<?php echo esc_html($e_desc ?: 'Chương trình tuyển sinh mở rộng cho người tốt nghiệp Trung cấp hoặc Cao đẳng. Chỉ mất 1 phút để kiểm tra tự động.'); ?>
				</p>
				<?php
				$e_items_default = [
					['title' => 'Tốt nghiệp Trung cấp', 'desc' => 'Liên thông lên Đại học'],
					['title' => 'Tốt nghiệp Cao đẳng', 'desc' => 'Liên thông miễn giảm tín chỉ'],
					['title' => 'Khác ngành / Ngành gần', 'desc' => 'Học bổ sung kiến thức liên thông'],
				];
				$e_items_render = !empty($e_items) ? $e_items : $e_items_default;
				?>
				<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4 max-w-3xl mx-auto text-left">
					<?php foreach ($e_items_render as $item) : ?>
						<div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
							<span class="block text-sm font-bold text-slate-800 mb-0.5"><?php echo esc_html($item['title'] ?? ''); ?></span>
							<span class="text-sm text-slate-500"><?php echo esc_html($item['desc'] ?? ''); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="flex flex-wrap justify-center gap-3 pt-6">
					<a href="<?php echo esc_url($e_cta_url); ?>" class="bg-brand-accent text-white px-6 py-3.5 rounded-lg font-bold text-sm hover:bg-[#e06e00] transition-all shadow-md shadow-brand-primary/10">
						<?php echo esc_html($e_cta_text ?: 'Bắt đầu kiểm tra ngay ➔'); ?>
					</a>
					<a href="#register-section" class="bg-white border border-slate-200 text-slate-700 px-6 py-3.5 rounded-lg font-bold text-sm hover:bg-slate-50 transition-all">
						Tư vấn tiêu chuẩn tuyển sinh
					</a>
				</div>
			</div>
		</div>
	</section>

	<!-- 8. DOUBLE CERTIFICATE VALUE PROPOSITION -->
	<?php
	$c_left_badge = '';
	$c_left_heading = '';
	$c_left_items = '';
	$c_left_cta = '';
	$c_right_badge = '';
	$c_right_heading = '';
	$c_right_items = '';
	$c_right_cta = '';
	$c_left_badge = '';
	$c_left_heading = '';
	$c_slider_items = [];
	$c_left_image = '';
	$c_left_youtube = '';
	$c_right_image = '';
	$c_right_content = '';
	if (function_exists('get_field')) {
		$c_left_badge = get_field('hp_cert_left_badge', 'options') ?: '';
		$c_left_heading = get_field('hp_cert_left_heading', 'options') ?: '';
		$c_slider_items = get_field('hp_cert_slider', 'options') ?: [];
		$c_left_image = get_field('hp_cert_left_image', 'options') ?: '';
		$c_left_youtube = get_field('hp_cert_left_youtube', 'options') ?: '';
		$c_right_image = get_field('hp_cert_right_image', 'options') ?: '';
		$c_right_content = get_field('hp_cert_right_content', 'options') ?: '';
	}
	?>
	<section class="py-8 sm:py-12 md:py-16 bg-white overflow-hidden">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 sm:space-y-12 lg:space-y-20">
			
			<!-- Row 1: [Text Content] [Slider] -->
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 lg:gap-12 lg:items-stretch items-center">
				<!-- Left column: Text Content -->
				<div class="space-y-4 sm:space-y-6 flex flex-col justify-center">
					<span class="inline-block bg-emerald-50 text-emerald-600 text-xs font-extrabold px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg uppercase tracking-wider self-start"><?php echo esc_html($c_left_badge ?: 'BẰNG CẤP TƯƠNG ĐƯƠNG'); ?></span>
					
					<h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-900 leading-tight">
						<?php 
						$heading = $c_left_heading ?: 'Không ghi hình thức đào tạo';
						$words = explode(' ', $heading);
						if ( count($words) > 2 ) {
							$last_words = implode(' ', array_slice($words, -3));
							$first_words = implode(' ', array_slice($words, 0, count($words) - 3));
							echo esc_html($first_words) . ' <span class="text-brand-primary inline">' . esc_html($last_words) . '</span>';
						} else {
							echo esc_html($heading);
						}
						?>
					</h2>

					<div class="space-y-2 sm:space-y-3 text-slate-600 text-xs sm:text-sm md:text-base leading-relaxed">
						<p>Từ ngày <strong>01/03/2020</strong>, bằng tốt nghiệp đại học sẽ không còn phân biệt hình thức đào tạo như chính quy, tại chức, từ xa...</p>
						<p>Theo <strong>Thông tư 27/2019/TT-BGDĐT</strong>, trên văn bằng sẽ vẫn ghi các nội dung cũ nhưng không ghi thông tin về hình thức đào tạo.</p>
					</div>

					<div class="pt-3 sm:pt-4 border-t border-slate-100">
						<h3 class="font-bold text-slate-800 text-sm sm:text-base md:text-lg mb-2.5 sm:mb-4">Có Giá Trị Sử Dụng Suốt Đời Trên Toàn Quốc</h3>
						
						<div class="space-y-2.5 sm:space-y-3.5">
							<div class="flex items-start gap-2.5 sm:gap-3.5">
								<div class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
									<svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="3">
										<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
									</svg>
								</div>
								<div>
									<h4 class="font-bold text-slate-900 text-xs sm:text-sm md:text-base leading-snug">Bằng Cử nhân / Kỹ sư chính thức</h4>
									<p class="text-slate-500 text-[11px] sm:text-xs md:text-sm leading-relaxed mt-0.5">Sau khi hoàn thành chương trình, học viên sẽ được trường Đại học cấp bằng Cử nhân/Kỹ sư theo quy định của Bộ GD&ĐT, có giá trị pháp lý trên toàn quốc.</p>
								</div>
							</div>

							<div class="flex items-start gap-2.5 sm:gap-3.5">
								<div class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
									<svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="3">
										<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
									</svg>
								</div>
								<div>
									<h4 class="font-bold text-slate-900 text-xs sm:text-sm md:text-base leading-snug">Có Thể Dùng Để Học Lên Các Bậc Cao Hơn</h4>
									<p class="text-slate-500 text-[11px] sm:text-xs md:text-sm leading-relaxed mt-0.5">Thi công chức nhà nước, xét nâng bậc lương, đi du học.</p>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Right column: Slider Wrapper -->
				<div class="relative flex justify-center w-full lg:h-full">
					<style>
						.cert-slider-wrapper {
							position: relative;
							width: 100%;
							max-width: 32rem; /* max-w-lg */
							aspect-ratio: 1.4; /* Perfect aspect ratio for certificate documents */
							border-radius: 1.5rem; /* rounded-3xl */
							overflow: hidden;
							box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.15);
							background-color: #ffffff; /* match white degree background */
						}
						@media (max-width: 640px) {
							.cert-slider-wrapper {
								border-radius: 1rem;
								box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
							}
						}
						@media (min-width: 1024px) {
							.cert-slider-wrapper {
								max-width: 100%;
								width: 100%;
								height: 100%;
								min-height: 100%;
								aspect-ratio: auto;
							}
						}
						.cert-swiper {
							width: 100%;
							height: 100%;
						}
						.cert-swiper .swiper-wrapper {
							height: 100%;
						}
						.cert-swiper .swiper-slide {
							height: 100%;
							width: 100%;
						}
						.cert-swiper .swiper-slide img {
							width: 100%;
							height: 100%;
							object-fit: cover;
							object-position: center;
							background-color: #ffffff;
						}
						.cert-swiper .swiper-pagination-bullet {
							background: rgba(0, 48, 139, 0.3) !important;
							opacity: 1 !important;
							width: 8px;
							height: 8px;
							transition: all 0.3s ease;
						}
						.cert-swiper .swiper-pagination-bullet-active {
							background: #0284c7 !important;
							transform: scale(1.2);
						}
					</style>
					<div class="cert-slider-wrapper border border-slate-100">
						<!-- Slider Container -->
						<div class="swiper cert-swiper">
							<div class="swiper-wrapper">
								<?php 
								$slides = [];
								if ( ! empty( $c_slider_items ) ) {
									foreach ( $c_slider_items as $item ) {
										if ( ! empty( $item['image'] ) ) {
											$img_val = $item['image'];
											if ( is_numeric( $img_val ) ) {
												$slides[] = wp_get_attachment_url( $img_val );
											} elseif ( is_array( $img_val ) && isset( $img_val['url'] ) ) {
												$slides[] = $img_val['url'];
											} else {
												$slides[] = $img_val;
											}
										}
									}
								}
								
								if ( empty( $slides ) ) {
									$slides[] = home_url('wp-content/uploads/2026/07/students-graduation.jpg');
								}
								
								foreach ( $slides as $index => $slide_url ) :
									$opt_slide_url = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide_url ) : $slide_url;
								?>
									<div class="swiper-slide w-full h-full">
										<img src="<?php echo esc_url($opt_slide_url); ?>" alt="Students Graduation Slide" width="640" height="468" loading="lazy" decoding="async" class="w-full h-full object-cover">
									</div>
								<?php endforeach; ?>
							</div>

							<!-- Swiper Pagination -->
							<?php if ( count($slides) > 1 ) : ?>
								<div class="swiper-pagination !bottom-2.5 !right-2.5 sm:!bottom-5 sm:!right-5 !left-auto !w-auto flex gap-1.5 sm:gap-2"></div>
							<?php endif; ?>
						</div>

						<!-- Orange badge -->
						<div class="absolute bottom-2.5 left-2.5 sm:bottom-5 sm:left-5 bg-[#f97316] text-white p-2.5 sm:p-4 md:p-5 rounded-xl sm:rounded-2xl shadow-lg flex flex-col justify-center max-w-[115px] sm:max-w-[160px] z-20 hover:scale-105 transition-transform duration-300 pointer-events-none">
							<span class="text-lg sm:text-2xl md:text-3xl font-black leading-none">100%</span>
							<span class="text-[9px] sm:text-xs font-extrabold tracking-wider uppercase mt-1 sm:mt-2 leading-tight">VĂN BẰNG CHUẨN<br>BỘ GD&ĐT</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Row 2: [Video Card] [Text Content] -->
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 lg:gap-12 lg:items-stretch items-center">
				<!-- Video Card (Mobile: 2nd after Text; Desktop: Left col) -->
				<div class="w-full rounded-2xl sm:rounded-3xl relative overflow-hidden shadow-xl aspect-video lg:aspect-auto lg:h-full lg:min-h-full group bg-slate-900 flex items-center justify-center order-2 lg:order-1 <?php echo !empty($c_left_youtube) ? 'cursor-pointer' : ''; ?>" <?php echo !empty($c_left_youtube) ? 'data-youtube="' . esc_url($c_left_youtube) . '"' : ''; ?>>
					<?php
					$video_raw_thumb = $c_left_image ?: home_url('wp-content/uploads/2026/07/vtv-news-thumb.png');
					$video_opt_thumb = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $video_raw_thumb ) : $video_raw_thumb;
					?>
					<img src="<?php echo esc_url($video_opt_thumb); ?>" alt="VTV24 Video Thumbnail" loading="lazy" decoding="async" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
					
					<?php if (!empty($c_left_youtube)): ?>
						<!-- Play button overlay -->
						<div class="absolute inset-0 bg-slate-950/20 flex items-center justify-center">
							<div class="w-12 h-12 sm:w-16 sm:h-16 bg-[#e11d48] text-white rounded-full flex items-center justify-center shadow-2xl group-hover:scale-110 transition-transform duration-300">
								<svg class="w-6 h-6 sm:w-8 sm:h-8 fill-current translate-x-0.5" viewBox="0 0 24 24">
									<path d="M8 5v14l11-7z"/>
								</svg>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<!-- Text Content (Mobile: 1st before Video; Desktop: Right col) -->
				<div class="space-y-4 sm:space-y-6 order-1 lg:order-2 flex flex-col justify-center">
					<?php if (!empty($c_right_content)): ?>
						<div class="prose max-w-none text-slate-700 text-xs sm:text-sm md:text-base leading-relaxed">
							<?php echo wp_kses_post($c_right_content); ?>
						</div>
					<?php else: ?>
						<span class="inline-block bg-emerald-50 text-emerald-600 text-xs font-extrabold px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg uppercase tracking-wider">
							Bạn có thể sử dụng văn bằng để làm gì?
						</span>
						
						<h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-900 leading-tight">
							<?php 
							$right_heading = 'Văn bằng sau tốt nghiệp';
							$words_right = explode(' ', $right_heading);
							if ( count($words_right) > 2 ) {
								$last_words_right = implode(' ', array_slice($words_right, -3));
								$first_words_right = implode(' ', array_slice($words_right, 0, count($words_right) - 3));
								echo esc_html($first_words_right) . ' <span class="text-brand-primary inline">' . esc_html($last_words_right) . '</span>';
							} else {
								echo esc_html($right_heading);
							}
							?>
						</h2>

						<div class="space-y-2 sm:space-y-3 text-slate-600 text-xs sm:text-sm md:text-base leading-relaxed">
							<p>Sau khi tốt nghiệp, người học được cấp văn bằng theo quy định hiện hành và có thể sử dụng để phục vụ các mục tiêu học tập, nghề nghiệp theo điều kiện của từng đơn vị tiếp nhận.</p>
						</div>

						<div class="pt-3 sm:pt-4 border-t border-slate-100">
							<h3 class="font-bold text-slate-800 text-sm sm:text-base md:text-lg mb-2.5 sm:mb-4">Giá trị sử dụng của văn bằng</h3>
							
							<div class="space-y-2.5 sm:space-y-3.5">
								<div class="flex items-start gap-2.5 sm:gap-3.5">
									<div class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
										<svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="3">
											<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
										</svg>
									</div>
									<div>
										<h4 class="font-bold text-slate-900 text-xs sm:text-sm md:text-base leading-snug">Học tiếp lên trình độ cao hơn</h4>
										<p class="text-slate-500 text-[11px] sm:text-xs md:text-sm leading-relaxed mt-0.5">Có thể đăng ký dự tuyển chương trình sau đại học khi đáp ứng điều kiện tuyển sinh.</p>
									</div>
								</div>

								<div class="flex items-start gap-2.5 sm:gap-3.5">
									<div class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
										<svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="3">
											<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
										</svg>
									</div>
									<div>
										<h4 class="font-bold text-slate-900 text-xs sm:text-sm md:text-base leading-snug">Bổ sung và hoàn thiện hồ sơ nghề nghiệp</h4>
										<p class="text-slate-500 text-[11px] sm:text-xs md:text-sm leading-relaxed mt-0.5">Phục vụ yêu cầu về trình độ chuyên môn đối với vị trí việc làm phù hợp.</p>
									</div>
								</div>

								<div class="flex items-start gap-2.5 sm:gap-3.5">
									<div class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
										<svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="3">
											<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
										</svg>
									</div>
									<div>
										<h4 class="font-bold text-slate-900 text-xs sm:text-sm md:text-base leading-snug">Tham gia tuyển dụng, thi tuyển</h4>
										<p class="text-slate-500 text-[11px] sm:text-xs md:text-sm leading-relaxed mt-0.5">Sử dụng văn bằng trong hồ sơ dự tuyển theo yêu cầu cụ thể của cơ quan, đơn vị tuyển dụng.</p>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
			
		</div>
	</section>

	<!-- YouTube Video Popup Modal -->
	<div id="youtube-video-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/80 p-4 transition-all duration-300 opacity-0">
		<div class="relative w-full max-w-4xl bg-black rounded-lg overflow-hidden shadow-2xl">
			<!-- Close Button -->
			<button id="close-video-modal" class="absolute top-3 right-3 text-white/80 hover:text-white bg-slate-800/50 hover:bg-slate-700 rounded-full p-2 focus:outline-none transition-colors z-50">
				<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
				</svg>
			</button>
			<!-- Video Container -->
			<div class="aspect-video w-full">
				<iframe id="modal-video-iframe" class="w-full h-full" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		</div>
	</div>

	<script>
		function initFrontPageInteractive() {
			const modal = document.getElementById('youtube-video-modal');
			const iframe = document.getElementById('modal-video-iframe');
			const closeBtn = document.getElementById('close-video-modal');
			const triggerElements = document.querySelectorAll('[data-youtube]');

			function getYoutubeId(url) {
				const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
				const match = url.match(regExp);
				return (match && match[2].length === 11) ? match[2] : null;
			}

			triggerElements.forEach(elem => {
				elem.addEventListener('click', function() {
					const url = this.getAttribute('data-youtube');
					const videoId = getYoutubeId(url);
					if (videoId) {
						iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
						modal.classList.remove('hidden');
						modal.classList.add('flex');
						// Force reflow
						setTimeout(() => {
							modal.classList.remove('opacity-0');
							modal.classList.add('opacity-100');
						}, 50);
					}
				});
			});

			function closeModal() {
				modal.classList.remove('opacity-100');
				modal.classList.add('opacity-0');
				setTimeout(() => {
					modal.classList.remove('flex');
					modal.classList.add('hidden');
					iframe.src = '';
				}, 300);
			}

			if (closeBtn) {
				closeBtn.addEventListener('click', closeModal);
			}
			if (modal) {
				modal.addEventListener('click', function(e) {
					if (e.target === modal) {
						closeModal();
					}
				});
			}

			// Certificate Swiper Slider Logic (Lazy Hydration with Deferred Swiper support)
			let certAttempts = 0;
			const initCertSwiper = () => {
				if (typeof Swiper === 'undefined') {
					if (++certAttempts < 100) {
						setTimeout(initCertSwiper, certAttempts > 20 ? 250 : 100);
					}
					return;
				}
				if (document.querySelector('.cert-swiper')) {
					new Swiper('.cert-swiper', {
						loop: true,
						autoplay: {
							delay: 4000,
							disableOnInteraction: false,
						},
						pagination: {
							el: '.cert-swiper .swiper-pagination',
							clickable: true,
						},
						speed: 600,
					});
				}
			};

			window.addEventListener('ltdh:swiper:loaded', initCertSwiper, { once: true });

			if ('requestIdleCallback' in window) {
				requestIdleCallback(initCertSwiper, { timeout: 3000 });
			} else {
				setTimeout(initCertSwiper, 1000);
			}
		}
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', initFrontPageInteractive);
		} else {
			initFrontPageInteractive();
		}
	</script>

	<!-- 6. EXPERIENCE & ACCURATE STATISTICS -->
	<?php
	$w_heading = '';
	$w_desc    = '';
	if ( function_exists( 'get_field' ) ) {
		$w_heading = get_field( 'hp_whyus_heading', 'options' ) ?: '';
		$w_desc    = get_field( 'hp_whyus_desc', 'options' ) ?: '';
	}
	if ( empty( $w_heading ) ) {
		$w_heading = 'Kinh nghiệm & Thực tế';
	}
	if ( empty( $w_desc ) || str_contains( $w_desc, 'hàng đầu' ) ) {
		$w_desc = 'Hỗ trợ người học tiếp cận thông tin tuyển sinh chính xác và chuẩn bị hồ sơ xét tuyển.';
	}

	$kpi_metrics = [];
	if ( function_exists( 'get_field' ) ) {
		$kpi_metrics = get_field( 'kpi_metrics', 'options' ) ?: [];
	}
	if ( empty( $kpi_metrics ) ) {
		$kpi_metrics = ltdh_default( 'homepage', 'kpi_metrics' );
	}

	// Sanitize and filter out legacy unverified claims (98%, 30+ đối tác, 1.200+ học viên, 10+ năm)
	$sanitized_metrics = [];
	if ( is_array( $kpi_metrics ) ) {
		foreach ( $kpi_metrics as $m ) {
			$val = trim( (string) ( $m['value'] ?? '' ) );
			$lbl = trim( (string) ( $m['label'] ?? '' ) );

			// Strip unverified satisfaction percentages and unverified partner counts
			if ( str_contains( $val, '98%' ) || str_contains( mb_strtolower( $lbl, 'UTF-8' ), 'hài lòng' ) ) {
				continue;
			}
			if ( ( str_contains( $val, '30+' ) || str_contains( $val, '30' ) ) && str_contains( mb_strtolower( $lbl, 'UTF-8' ), 'đối tác' ) ) {
				continue;
			}
			// Replace legacy 200+ / 1.200+ student enrollment claims with verified 500+ registration support
			if ( str_contains( $val, '1.200' ) || str_contains( $val, '200+' ) || str_contains( $val, '1200' ) ) {
				$sanitized_metrics[] = [
					'value'   => '500+',
					'label'   => 'Người học được hỗ trợ đăng ký liên thông',
					'subtext' => 'Trong 4 năm qua',
				];
				continue;
			}
			// Replace legacy unverified 10+ years with actual 4 years experience
			if ( str_contains( $val, '10+' ) || str_contains( $val, '10' ) ) {
				$sanitized_metrics[] = [
					'value'   => '4 năm',
					'label'   => 'Kinh nghiệm tư vấn tuyển sinh liên thông',
					'subtext' => 'Tư vấn lựa chọn chương trình và hỗ trợ hồ sơ',
				];
				continue;
			}
			$sanitized_metrics[] = $m;
		}
	}

	if ( empty( $sanitized_metrics ) ) {
		$sanitized_metrics = ltdh_default( 'homepage', 'kpi_metrics' );
	}
	?>
	<section class="py-8 sm:py-10 md:py-12 bg-gradient-to-br from-[#0c192c] via-[#0E2038] to-[#002b7a] text-white relative overflow-hidden">
		<div class="absolute -right-20 -top-20 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
		<div class="absolute -left-20 -bottom-20 w-80 h-80 bg-[#f5bf23]/10 rounded-full blur-3xl pointer-events-none"></div>
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
			<div class="flex flex-col lg:flex-row items-center justify-between gap-6 lg:gap-12">
				<div class="w-full lg:w-1/3 text-center lg:text-left">
					<div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-xs text-[#f5bf23] text-[11px] font-extrabold uppercase tracking-wider mb-2.5 border border-white/10">
						<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
						<span>Thông tin xác thực</span>
					</div>
					<h2 class="text-xl md:text-2xl font-black font-display tracking-tight text-white uppercase leading-tight">
						<?php echo esc_html( $w_heading ); ?>
					</h2>
					<p class="text-slate-300 text-xs sm:text-sm mt-2 leading-relaxed">
						<?php echo esc_html( $w_desc ); ?>
					</p>
				</div>
				<div id="ltdh-kpi-metrics-grid" class="w-full lg:w-2/3 grid grid-cols-2 gap-3 sm:gap-6 text-center">
					<?php foreach ( $sanitized_metrics as $idx => $metric ) : 
						$is_first = ( 0 === $idx );
					?>
						<div class="group bg-white/[0.07] hover:bg-white/[0.12] border border-white/10 hover:border-white/20 rounded-2xl p-3.5 sm:p-6 flex flex-col items-center text-center backdrop-blur-xs transition-all duration-300 shadow-sm hover:shadow-lg h-full">
							<div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-[#f5bf23] mb-2 sm:mb-3 group-hover:bg-[#f5bf23]/20 group-hover:border-[#f5bf23]/30 transition-colors shrink-0">
								<?php if ( $is_first ) : ?>
									<svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
										<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
									</svg>
								<?php else : ?>
									<svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
										<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
									</svg>
								<?php endif; ?>
							</div>

							<span class="ltdh-counter tabular-nums block font-display font-black text-2xl sm:text-4xl lg:text-5xl text-[#f5bf23] leading-none tracking-tight mb-1.5 sm:mb-2" data-target="<?php echo esc_attr( $metric['value'] ?? '' ); ?>">
								<?php echo esc_html( $metric['value'] ?? '' ); ?>
							</span>
							<span class="text-xs sm:text-base text-white font-bold block leading-snug sm:leading-snug mb-auto">
								<?php echo esc_html( $metric['label'] ?? '' ); ?>
							</span>
							<?php if ( ! empty( $metric['subtext'] ) ) : ?>
								<span class="text-[11px] sm:text-xs text-slate-300 font-medium block mt-1.5 sm:mt-2 leading-tight sm:leading-relaxed pt-1.5 border-t border-white/5 w-full">
									<?php echo esc_html( $metric['subtext'] ); ?>
								</span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>

	<!-- 7. STUDENT TESTIMONIALS -->
	<section class="py-8 sm:py-12 md:py-16 bg-white relative overflow-hidden">
		<div class="absolute -left-16 -top-16 w-64 h-64 bg-brand-accent/10 rounded-full blur-3xl pointer-events-none z-0"></div>
		<div class="absolute -right-16 -bottom-16 w-64 h-64 bg-brand-accent/10 rounded-full blur-3xl pointer-events-none z-0"></div>
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
			<div class="text-center max-w-2xl mx-auto mb-6 sm:mb-8 space-y-2 sm:space-y-3">
				<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">CẢM NHẬN HỌC VIÊN</span>
				<h2 class="text-xl md:text-3xl font-black text-slate-900">Đánh giá của học viên</h2>
				<p class="text-slate-500 text-sm">Chia sẻ thực tế từ người học đã được tư vấn và hỗ trợ chuẩn bị hồ sơ xét tuyển liên thông đại học.</p>
			</div>

			<div class="flex md:grid overflow-x-auto md:overflow-x-visible snap-x snap-mandatory md:snap-none gap-6 pb-4 md:pb-0 no-scrollbar md:grid-cols-3">
				<?php
				$testimonials = [];
				if (function_exists('get_field')) {
					$testimonials = get_field('testimonials', 'options') ?: [];
				}
				if (empty($testimonials)) {
					// 3 học viên tiêu biểu có phản hồi và hình ảnh thực tế
					$testimonials = [
						[
							'name'         => 'Hoàng Thị Độ',
							'workplace'    => 'Product Owner',
							'role' => 'Liên thông Công nghệ thông tin',
							'graduation'   => 'Khóa 29.1 - ĐH Giao thông Vận tải',
							'status'       => 'graduated',
							'status_label' => 'Đã tốt nghiệp',
							'initials'     => 'HĐ',
							'image'        => get_template_directory_uri() . '/assets/images/student-do.jpg',
							'content'      => 'Mình liên thông Cao Đẳng lên đại học. Lịch học được sắp xếp ngoài giờ hành chính, tạo điều kiện thuận lợi cho những người đang đi làm như mình có thể vừa hoàn thành công việc, vừa tiếp tục học tập. Bên cạnh đó, thầy cô luôn nhiệt tình hỗ trợ và tạo điều kiện để sinh viên cân bằng giữa công việc và việc học.'
						],
						[
							'name'         => 'Phạm Văn Dũng',
							'workplace'    => 'Leader SEO tại CAR BOSS Việt Nam',
							'role'         => 'Liên thông ngành TMDT',
							'graduation'   => 'Khóa K10 - ĐH Thương Mại',
							'status'       => 'studying',
							'status_label' => 'Sắp tốt nghiệp',
							'initials'     => 'PD',
							'image'        => get_template_directory_uri() . '/assets/images/student-dung.jpg',
							'content'      => 'Nhờ thầy Đạt hướng dẫn tận tình từ khâu tìm hiểu chương trình, chuẩn bị hồ sơ đến hoàn tất thủ tục nhập học, em đã đăng ký học liên thông lên Đại học TMU thuận lợi và nhanh chóng. Thầy luôn giải đáp thắc mắc kịp thời, giúp em yên tâm hơn trong suốt quá trình đăng ký.'
						],
						[
							'name'         => 'Nguyễn Quý Duy',
							'workplace'    => 'Front-End Developer tại VC Corp',
							'role'         => 'Liên thông ngành CNTT',
							'graduation'   => 'Khóa K27 - ĐH Giao thông Vận tải',
							'status'       => 'graduated',
							'status_label' => 'Đã tốt nghiệp',
							'initials'     => 'QD',
							'image'        => get_template_directory_uri() . '/assets/images/student-duy.jpg',
							'content'      => 'Điều em hài lòng khi học liên thông là lịch học linh hoạt, phù hợp với người vừa học vừa làm. Thời gian đào tạo chỉ khoảng 1 năm 7 tháng, giúp em sớm có bằng Đại học để đáp ứng yêu cầu công việc. Bên cạnh đó, học phí hợp lý cũng là một lợi thế, đặc biệt với các bạn vừa tốt nghiệp Cao đẳng và muốn học tiếp lên Đại học.'
						],
					];
				}
				$testimonials = array_slice($testimonials, 0, 3);

				foreach ($testimonials as $t) :
					$initials = strtoupper(substr($t['initials'] ?? $t['name'] ?? 'U', 0, 2));
					$avatar_url = '';
					if (!empty($t['image'])) {
						if (is_array($t['image'])) {
							$avatar_url = $t['image']['sizes']['medium'] ?? $t['image']['sizes']['thumbnail'] ?? $t['image']['url'] ?? '';
						} elseif (is_numeric($t['image'])) {
							$avatar_url = wp_get_attachment_image_url($t['image'], 'medium') ?: wp_get_attachment_image_url($t['image'], 'thumbnail');
						} else {
							$avatar_url = $t['image'];
						}
					}
					$workplace    = $t['workplace'] ?? $t['company'] ?? $t['job'] ?? '';
					$graduation   = $t['graduation'] ?? $t['school'] ?? $t['batch'] ?? 'Bằng chuẩn Bộ GD&ĐT';
					$status       = $t['status'] ?? 'graduated';
					$is_studying  = in_array($status, ['studying', 'new', 'enrolled'], true);
					$status_label = $t['status_label'] ?? ($is_studying ? 'Đang theo học' : 'Đã tốt nghiệp');
				?>
					<div class="shrink-0 w-[85vw] sm:w-[360px] md:w-auto snap-center bg-slate-50/80 hover:bg-white border border-slate-200/80 hover:border-blue-300 rounded-2xl p-6 sm:p-7 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
						<div>
							<!-- Student Profile Header with Large Photo (96px) -->
							<div class="flex items-start gap-4 mb-4">
								<div class="relative shrink-0">
									<?php if ($avatar_url) : ?>
										<img class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-2 ring-slate-100 shadow-md group-hover:ring-brand-primary/20 transition-all duration-300" src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($t['name'] ?? ''); ?>" loading="lazy" width="96" height="96">
									<?php else : ?>
										<div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-brand-primary to-blue-700 text-white rounded-2xl flex items-center justify-center font-bold text-xl shadow-md ring-2 ring-slate-100"><?php echo esc_html($initials); ?></div>
									<?php endif; ?>

									<?php if ($is_studying) : ?>
										<span class="absolute -bottom-1 -right-1 bg-blue-600 text-white p-1 rounded-full ring-2 ring-white shadow-xs" title="<?php echo esc_attr($status_label); ?>">
											<svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a10.993 10.993 0 00-.25 2.302c0 1.109.2 2.16.564 3.125a1 1 0 00.916.622h7.04a1 1 0 00.916-.622c.364-.965.564-2.016.564-3.125 0-.796-.09-1.57-.25-2.302l2.644-1.131a1 1 0 000-1.84l-7-3zM10 4.218l4.157 1.782L10 7.782 5.843 6 10 4.218z"/>
											</svg>
										</span>
									<?php else : ?>
										<span class="absolute -bottom-1 -right-1 bg-emerald-500 text-white p-1 rounded-full ring-2 ring-white shadow-xs" title="Học viên đã tốt nghiệp">
											<svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
											</svg>
										</span>
									<?php endif; ?>
								</div>
								<div class="min-w-0 flex-1">
									<div class="flex items-center gap-0.5 text-amber-400 mb-1" aria-label="5 trên 5 sao">
										<svg class="w-4 h-4 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
										<svg class="w-4 h-4 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
										<svg class="w-4 h-4 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
										<svg class="w-4 h-4 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
										<svg class="w-4 h-4 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
										<span class="text-xs font-bold text-amber-600 ml-1">5.0</span>
									</div>
									<h3 class="font-bold text-base sm:text-lg text-slate-900 group-hover:text-brand-primary transition-colors leading-snug truncate"><?php echo esc_html($t['name'] ?? ''); ?></h3>
									<?php if (!empty($workplace)) : ?>
										<p class="text-xs sm:text-[13px] font-semibold text-slate-700 flex items-center gap-1 my-1.5 truncate" title="<?php echo esc_attr($workplace); ?>">
											<svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
											</svg>
											<span class="truncate"><?php echo esc_html($workplace); ?></span>
										</p>
									<?php endif; ?>
									<p class="text-xs text-brand-primary font-medium mt-0.5 line-clamp-1"><?php echo esc_html($t['role'] ?? ''); ?></p>
								</div>
							</div>

							<!-- Quote Body with decorative quote mark -->
							<div class="relative pt-2">
								<svg class="w-8 h-8 text-blue-100 mb-2 -mt-1" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
								<p class="text-sm sm:text-[15px] text-slate-600 leading-relaxed italic">"<?php echo esc_html($t['content'] ?? ''); ?>"</p>
							</div>
						</div>

						<!-- Card Bottom / Trust signal with Graduation Batch & University (Mục 2) -->
						<div class="mt-5 pt-3.5 border-t border-slate-200/80 flex items-center justify-between text-xs gap-2">
							<?php if ($is_studying) : ?>
								<span class="inline-flex items-center gap-1.5 font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md shrink-0 border border-blue-100/80">
									<span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
									<?php echo esc_html($status_label); ?>
								</span>
							<?php else : ?>
								<span class="inline-flex items-center gap-1.5 font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md shrink-0">
									<svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
										<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
									</svg>
									Đã tốt nghiệp
								</span>
							<?php endif; ?>
							<span class="text-slate-600 font-medium flex items-center gap-1 truncate" title="<?php echo esc_attr($graduation); ?>">
								<svg class="w-3.5 h-3.5 text-brand-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
								</svg>
								<span class="truncate"><?php echo esc_html($graduation); ?></span>
							</span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			
		</div>
	</section>

	<!-- 8.5. ADMISSION PROCESS SECTION -->
	<section class="py-8 sm:py-12 md:py-16 bg-slate-50 border-t border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<?php
			get_template_part(
				'template-parts/admission-process',
				null,
				[
					'title'      => 'Quy trình xét tuyển Liên thông & Đại học từ xa',
					'subtitle'   => 'Chỉ với 4 bước đơn giản, người đi làm dễ dàng sở hữu bằng Đại học chính quy / chuẩn Bộ GD&ĐT',
					'section_id' => 'quy-trinh-xet-tuyen-home',
					'cta_text'   => 'Kiểm tra điều kiện & Nhận tư vấn ngay',
					'cta_link'   => '/kiem-tra-dieu-kien',
					'card_bg'    => 'bg-white',
				]
			);
			?>
		</div>
	</section>

	<!-- 9. DYNAMIC CONSULTATION FORM SECTION (Hidden on mobile per UX specification) -->
	<section id="register-section" class="hidden md:block scroll-mt-20 md:scroll-mt-24 py-8 sm:py-12 md:py-16 bg-white">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-20">
			<div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden grid grid-cols-1 lg:grid-cols-12 mb-12">
			
			<!-- Left Side Graphic (Uses the banner-contact image from homepage) -->
			<div class="lg:col-span-5 relative min-h-[350px] md:min-h-[450px] lg:min-h-[550px] bg-slate-50">
					<?php
					$raw_contact_banner = home_url( '/wp-content/uploads/2026/07/banner-contact.png' );
					$opt_contact_banner = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $raw_contact_banner ) : $raw_contact_banner;
					?>
					<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $opt_contact_banner ); ?>');"></div>
			</div>

			<!-- Right Side Form (Matches Mockup exact copy and style) -->
			<div class="lg:col-span-7 p-6 md:p-10 flex flex-col justify-center">
				<div class="mb-6">
					<span class="inline-block bg-blue-50 text-[#00308b] text-sm font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-3">
						ĐĂNG KÝ TRỰC TUYẾN
					</span>
					<h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-2">
						Đăng ký nhận tư vấn tuyển sinh miễn phí
					</h2>
					<p class="text-sm text-slate-500 leading-relaxed">
						Vui lòng cung cấp thông tin liên hệ, đội ngũ tuyển sinh của chúng tôi sẽ chủ động gọi lại hỗ trợ giải đáp lộ trình cho bạn sớm nhất.
					</p>
				</div>

				<div class="ltdh-contact-form-wrapper">
					<?php ltdh_render_consultation_form(); ?>
				</div>
			</div>
		</div>
		</div>
	</section>

	<!-- 10. NEWS RECENT SECTION -->
	<section class="py-8 sm:py-12 md:py-16 bg-slate-50 border-t border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex justify-between items-end mb-6 sm:mb-8 lg:mb-10">
				<div class="space-y-2">
					<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">THÔNG TIN TUYỂN SINH</span>
					<h2 class="text-2xl md:text-3xl font-black text-slate-900">Tin tức mới nhất</h2>
				</div>
				<a href="<?php echo esc_url(home_url('/tin-tuc/')); ?>" class="text-sm text-[#00308b] font-bold hover:underline flex items-center gap-1">
					Xem tất cả
					<span>→</span>
				</a>
			</div>

			<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-6">
				<?php
				$mock_news = [
					['title' => 'Tuyển sinh Đại học Từ xa khóa mới nhất', 'date' => '10/07/2026', 'desc' => 'Thông tin chi tiết các ngành đào tạo từ xa hệ Đại học được bộ GD&ĐT công nhận tốt nghiệp chính quy.'],
					['title' => 'Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026', 'date' => '08/07/2026', 'desc' => 'Quy trình và hồ sơ xét tuyển liên thông đại học từ trung cấp, cao đẳng lên đại học theo hình thức từ xa và vừa học vừa làm.'],
					['title' => 'Quy chế tuyển sinh Liên thông Cao đẳng lên Đại học', 'date' => '05/07/2026', 'desc' => 'Quy định rút ngắn chương trình đào tạo khi thi liên thông và các hồ sơ chuẩn bị nhập học.'],
					['title' => 'Học đại học vừa học vừa làm có giá trị như thế nào?', 'date' => '02/07/2026', 'desc' => 'Giá trị pháp lý của tấm bằng đại học vừa học vừa làm đối với cơ hội thăng tiến nghề nghiệp.'],
				];

				$news_has_posts = false;
				if ($news_query->have_posts()) {
					$news_has_posts = true;
					$index = 0;
					while ($news_query->have_posts()) : $news_query->the_post();
						$post_id = get_the_ID();
						$raw_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $post_id, 'medium' ) : ( get_the_post_thumbnail_url( $post_id, 'medium' ) ?: ltdh_get_fallback_image( 'news' ) );
						$thumb_url = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $raw_thumb ) : $raw_thumb;
						$categories = get_the_category($post_id);
						$category_name = ! empty($categories) ? $categories[0]->name : 'Tin tuyển sinh';
				?>
						<div class="bg-white border border-slate-100 rounded-lg overflow-hidden hover:shadow-md transition-all flex flex-col justify-between group">
							<a href="<?php the_permalink(); ?>" class="aspect-[16/9] w-full bg-slate-100 overflow-hidden block shrink-0">
								<img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" class="w-full h-full object-cover object-left group-hover:scale-105 transition-transform duration-500" />
							</a>
							<div class="p-4 flex-1 flex flex-col justify-between">
								<div>
									<span class="text-xs font-bold text-brand-primary uppercase tracking-wider block mb-1"><?php echo esc_html($category_name); ?></span>
									<h3 class="font-extrabold text-slate-800 text-sm line-clamp-2 hover:text-[#00308b] transition-colors leading-snug">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h3>
								</div>
								<time class="text-xs text-slate-400 mt-3 block"><?php echo get_the_date('d/m/Y'); ?></time>
							</div>
						</div>
					<?php
						$index++;
					endwhile;
					wp_reset_postdata();
				}

				if (! $news_has_posts) {
					foreach ($mock_news as $mn) {
					?>
						<div class="bg-white border border-slate-100 rounded-lg p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
							<div>
								<span class="text-xs font-bold text-brand-primary uppercase tracking-wider block mb-1">Hướng dẫn</span>
								<h3 class="font-bold text-slate-800 text-sm line-clamp-2 leading-snug"><?php echo esc_html($mn['title']); ?></h3>
								<p class="text-sm text-slate-400 leading-normal line-clamp-3 mt-2"><?php echo esc_html($mn['desc']); ?></p>
							</div>
							<time class="text-xs text-slate-400 mt-3 block"><?php echo esc_html($mn['date']); ?></time>
						</div>
				<?php
					}
				}
				?>
			</div>
		</div>
	</section>

	<!-- 11. FAQ SECTION (SECTION CUỐI TRANG CHỦ) -->
	<?php
	$homepage_faqs = [];
	if ( function_exists( 'get_field' ) ) {
		$homepage_faqs = get_field( 'faq_items', 'options' ) ?: [];
	}

	if ( ! empty( $homepage_faqs ) && is_array( $homepage_faqs ) ) :
	?>
	<section id="faq-section" class="py-8 sm:py-12 md:py-16 bg-white border-t border-slate-100">
		<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
			<!-- Section Header -->
			<div class="text-center max-w-3xl mx-auto mb-6 sm:mb-8 space-y-2 sm:space-y-3">
				<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">HỎI ĐÁP TUYỂN SINH</span>
				<h2 class="text-2xl md:text-4xl font-black text-slate-900 tracking-tight">Câu hỏi thường gặp</h2>
				<p class="text-slate-500 text-sm md:text-base">Giải đáp thắc mắc về điều kiện tuyển sinh liên thông, hình thức học trực tuyến, bằng cấp và học phí.</p>
			</div>

			<!-- FAQ Accordion List -->
			<div class="space-y-3 md:space-y-4">
				<?php
				foreach ( $homepage_faqs as $faq ) :
					$q_text = $faq['question'] ?? '';
					$a_text = $faq['answer'] ?? '';
					if ( empty( $q_text ) || empty( $a_text ) ) continue;
				?>
					<details class="bg-slate-50/70 border border-slate-100/90 rounded-2xl overflow-hidden group transition-all hover:bg-slate-50 hover:border-slate-200/60 shadow-2xs">
						<summary class="flex justify-between items-center font-bold text-slate-900 p-4 sm:p-5 md:p-6 cursor-pointer list-none select-none text-sm sm:text-base md:text-lg [&::-webkit-details-marker]:hidden gap-3 sm:gap-4">
							<span class="flex items-center gap-2.5 sm:gap-3">
								<span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-blue-50 text-brand-primary font-black text-xs flex items-center justify-center shrink-0">?</span>
								<span><?php echo esc_html( $q_text ); ?></span>
							</span>
							<span class="text-slate-400 group-open:rotate-180 transition-transform duration-300 shrink-0">
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
							</span>
						</summary>
						<div class="px-4 sm:px-5 md:px-6 pb-4 sm:pb-6 pt-2 text-slate-600 text-xs sm:text-sm md:text-base leading-relaxed border-t border-slate-100/60 bg-white">
							<p><?php echo esc_html( $a_text ); ?></p>
						</div>
					</details>
				<?php endforeach; ?>
			</div>

			<!-- View All FAQs Button -->
			<div class="mt-6 sm:mt-8 text-center">
				<a href="<?php echo esc_url( home_url( '/cau-hoi-thuong-gap/' ) ); ?>" class="inline-flex items-center gap-2 text-sm font-bold text-brand-primary hover:text-brand-darkBlue bg-blue-50 hover:bg-blue-100 px-5 py-3 rounded-xl transition-colors min-h-[44px]">
					<span>Xem tất cả câu hỏi thường gặp</span>
					<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
				</a>
			</div>
		</div>
	</section>
	<?php endif; ?>

</main>

<?php
get_footer();
