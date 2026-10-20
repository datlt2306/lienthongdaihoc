<?php
/**
 * Archive Major Directory Template
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wp_query;

// Active filter state
$selected_cat = '';
if ( ! empty( $_GET['nhom_nganh'] ) ) {
	$selected_cat = sanitize_text_field( wp_unslash( $_GET['nhom_nganh'] ) );
} elseif ( is_tax( 'major_cat' ) ) {
	$queried_tax = get_queried_object();
	$selected_cat = ( $queried_tax && ! empty( $queried_tax->slug ) ) ? $queried_tax->slug : '';
} elseif ( ! empty( get_query_var( 'major_cat' ) ) ) {
	$selected_cat = sanitize_text_field( get_query_var( 'major_cat' ) );
} else {
	$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
	if ( preg_match( '#^/nhom-nganh/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $path_matches ) ) {
		if ( 'page' !== $path_matches[1] ) {
			$selected_cat = sanitize_text_field( $path_matches[1] );
		}
	}
}

$selected_search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$selected_sort   = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : '';
$selected_limit  = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 12;

$has_active_filters = ( ! empty( $selected_cat ) || ! empty( $selected_search ) || ! empty( $selected_sort ) || ( isset( $_GET['limit'] ) && 12 !== $selected_limit ) );

// Fetch all major_cat taxonomy terms
$major_cats = get_terms( [
	'taxonomy'   => 'major_cat',
	'hide_empty' => false,
] );
if ( is_wp_error( $major_cats ) || ! is_array( $major_cats ) ) {
	$major_cats = [];
}

$total_majors    = wp_count_posts( 'major' )->publish;
$active_cat_term = $selected_cat ? get_term_by( 'slug', $selected_cat, 'major_cat' ) : null;
$archive_link    = home_url( '/nhom-nganh/' );

// Preserved args for tabs
$preserved_args = [];
if ( $selected_search ) {
	$preserved_args['s'] = $selected_search;
}
if ( $selected_sort ) {
	$preserved_args['sort'] = $selected_sort;
}
if ( 12 !== $selected_limit ) {
	$preserved_args['limit'] = $selected_limit;
}

$all_tab_url = ! empty( $preserved_args ) ? add_query_arg( $preserved_args, $archive_link ) : $archive_link;

// Compute active filter items for badge counter and mobile chips
$active_filter_items = [];
if ( ! empty( $selected_search ) ) {
	$active_filter_items[] = [
		'label' => 'Từ khóa: "' . $selected_search . '"',
		'key'   => 's',
	];
}
if ( ! empty( $selected_cat ) ) {
	$cat_label = $active_cat_term ? $active_cat_term->name : $selected_cat;
	$active_filter_items[] = [
		'label' => 'Nhóm: ' . $cat_label,
		'key'   => 'nhom_nganh',
	];
}
if ( ! empty( $selected_sort ) ) {
	$sort_labels = [
		'title_asc'  => 'A-Z',
		'title_desc' => 'Z-A',
		'date_desc'  => 'Mới nhất',
	];
	$active_filter_items[] = [
		'label' => 'Xếp: ' . ( $sort_labels[ $selected_sort ] ?? $selected_sort ),
		'key'   => 'sort',
	];
}
if ( 12 !== $selected_limit ) {
	$active_filter_items[] = [
		'label' => $selected_limit . ' ngành/trang',
		'key'   => 'limit',
	];
}
$active_filter_count = count( $active_filter_items );
$has_active_filters  = $active_filter_count > 0;
?>

<main id="primary" class="site-main bg-slate-50 min-h-screen">
	<?php get_template_part( 'template-parts/banner' ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-3 pb-8 sm:py-8 md:py-12">

		<!-- ============ DESKTOP TOP FILTER BAR (FULL-WIDTH, VISIBLE ONLY ON >= MD) ============ -->
		<div id="catalog-desktop-major-filter-wrapper" class="hidden md:block bg-white border border-slate-100 rounded-2xl p-5 md:p-6 shadow-sm mb-8 transition-all">
			
			<!-- Header inside Filter: Title, Found Counter & Desktop Reset Action -->
			<div class="flex flex-row items-center justify-between pb-4 border-b border-slate-100 gap-3">
				<div>
					<h2 id="major-heading-title" class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
						<?php echo $active_cat_term ? 'Nhóm ngành: ' . esc_html( $active_cat_term->name ) : 'Danh sách Ngành đào tạo Liên thông Đại học'; ?>
					</h2>
					<p class="text-xs md:text-sm text-slate-500 mt-1">
						Tìm thấy <strong id="major-count-display" class="text-brand-primary font-extrabold text-sm md:text-base"><?php echo esc_html( $wp_query->found_posts ); ?></strong> ngành học phù hợp.
					</p>
				</div>
				<div id="major-desktop-reset">
					<?php if ( $has_active_filters ) : ?>
						<a href="<?php echo esc_url( $archive_link ); ?>" class="js-ltdh-reset-filter inline-flex items-center gap-1.5 text-xs md:text-sm font-bold text-rose-600 hover:text-rose-700 bg-rose-50/80 hover:bg-rose-100/80 border border-rose-100 px-3.5 py-2 rounded-lg transition-colors min-h-[40px]">
							<span>✕</span> <span>Xóa tất cả bộ lọc</span>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- Filter Form Controls (Rendered here initially on desktop SSR) -->
			<div id="major-filter-body" class="pt-4 md:pt-0">
				<form id="major-filter-form" action="<?php echo esc_url( $archive_link ); ?>" method="GET" class="space-y-4">
					
					<!-- Row 1: Search + Nhóm ngành Dropdown + Sort Dropdown + Limit Dropdown -->
					<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
					
					<!-- 1. Search Box -->
					<div class="relative">
						<label for="major-search-keyword" class="block md:sr-only text-xs font-extrabold uppercase text-slate-500 mb-1.5">Tìm kiếm</label>
						<div class="relative">
							<span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
								<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
							</span>
							<input type="text" 
								   id="major-search-keyword"
								   name="s" 
								   value="<?php echo esc_attr( $selected_search ); ?>" 
								   placeholder="Tìm tên ngành học..." 
								   class="w-full pl-10 pr-4 py-2.5 border border-slate-200/80 rounded-xl text-sm focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none placeholder-slate-400 min-h-[44px] transition-all bg-slate-50/50 focus:bg-white">
						</div>
					</div>

					<!-- 2. Nhóm ngành Select -->
					<div>
						<label for="major-cat-select" class="block md:sr-only text-xs font-extrabold uppercase text-slate-500 mb-1.5">Nhóm ngành</label>
						<select id="major-cat-select" name="nhom_nganh" class="w-full py-2.5 px-3.5 border border-slate-200/80 rounded-xl text-sm font-medium bg-slate-50/50 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
							<option value="">Tất cả nhóm ngành</option>
							<?php if ( ! empty( $major_cats ) ) : ?>
								<?php foreach ( $major_cats as $cat ) : ?>
									<option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $selected_cat, $cat->slug ); ?>>
										<?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>

					<!-- 3. Sorting Select -->
					<div>
						<label for="major-sort-select" class="block md:sr-only text-xs font-extrabold uppercase text-slate-500 mb-1.5">Sắp xếp</label>
						<select id="major-sort-select" name="sort" class="w-full py-2.5 px-3.5 border border-slate-200/80 rounded-xl text-sm font-medium bg-slate-50/50 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
							<option value="" <?php selected( $selected_sort, '' ); ?>>Sắp xếp: Mặc định</option>
							<option value="title_asc" <?php selected( $selected_sort, 'title_asc' ); ?>>Tên ngành (A-Z)</option>
							<option value="title_desc" <?php selected( $selected_sort, 'title_desc' ); ?>>Tên ngành (Z-A)</option>
							<option value="date_desc" <?php selected( $selected_sort, 'date_desc' ); ?>>Mới nhất</option>
						</select>
					</div>

					<!-- 4. Limit Select -->
					<div>
						<label for="major-limit-select" class="block md:sr-only text-xs font-extrabold uppercase text-slate-500 mb-1.5">Hiển thị</label>
						<select id="major-limit-select" name="limit" class="w-full py-2.5 px-3.5 border border-slate-200/80 rounded-xl text-sm font-medium bg-slate-50/50 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
							<option value="12" <?php selected( $selected_limit, 12 ); ?>>Hiển thị: 12 ngành</option>
							<option value="24" <?php selected( $selected_limit, 24 ); ?>>Hiển thị: 24 ngành</option>
							<option value="36" <?php selected( $selected_limit, 36 ); ?>>Hiển thị: 36 ngành</option>
							<option value="50" <?php selected( $selected_limit, 50 ); ?>>Hiển thị: 50 ngành</option>
						</select>
					</div>

				</div>

				<!-- Row 2: Quick Pill Tabs for Nhóm ngành -->
				<div id="major-pills-container" class="pt-3 border-t border-slate-100">
					<span class="block md:inline-block text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-2 md:mb-0 md:mr-2 shrink-0">Nhóm ngành:</span>
					
					<div class="inline-flex flex-wrap items-center gap-1.5 sm:gap-2">
						<!-- All Majors Tab -->
						<a href="<?php echo esc_url( $all_tab_url ); ?>"
						   data-cat=""
						   class="js-ltdh-major-pill inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs md:text-sm font-bold transition-all min-h-[34px] <?php echo empty( $selected_cat ) ? 'is-active bg-brand-primary text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'; ?>">
							<span>Tất cả</span>
							<span class="pill-badge text-[11px] px-1.5 py-0.2 rounded-full font-black <?php echo empty( $selected_cat ) ? 'bg-white/20 text-white' : 'bg-white text-slate-600 border border-slate-200/60'; ?>">
								<?php echo esc_html( $total_majors ); ?>
							</span>
						</a>

						<!-- Dynamic Nhóm ngành Tabs -->
						<?php if ( ! empty( $major_cats ) ) : ?>
							<?php foreach ( $major_cats as $cat ) : ?>
								<?php
								$is_cat_active = ( $selected_cat === $cat->slug );
								$cat_base_url  = home_url( '/nhom-nganh/' . $cat->slug . '/' );
								$cat_tab_url   = ! empty( $preserved_args ) ? add_query_arg( $preserved_args, $cat_base_url ) : $cat_base_url;
								?>
								<a href="<?php echo esc_url( $cat_tab_url ); ?>"
								   data-cat="<?php echo esc_attr( $cat->slug ); ?>"
								   class="js-ltdh-major-pill inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs md:text-sm font-bold transition-all min-h-[34px] <?php echo $is_cat_active ? 'is-active bg-brand-primary text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'; ?>">
									<span><?php echo esc_html( $cat->name ); ?></span>
									<span class="pill-badge text-[11px] px-1.5 py-0.2 rounded-full font-black <?php echo $is_cat_active ? 'bg-white/20 text-white' : 'bg-white text-slate-600 border border-slate-200/60'; ?>">
										<?php echo esc_html( $cat->count ); ?>
									</span>
								</a>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>

				</form>
			</div><!-- #major-filter-body -->

		</div><!-- #catalog-desktop-major-filter-wrapper -->

		<!-- ============ MOBILE COMPACT BAR (ULTRA COMPACT, VISIBLE ONLY ON < MD) ============ -->
		<div class="md:hidden flex items-center justify-between gap-3 mb-3 px-0.5">
			<div class="min-w-0">
				<p class="text-xs font-bold text-slate-500 truncate">
					Tìm thấy <strong id="catalog-major-count-display-mobile" class="text-brand-primary font-black text-sm"><?php echo esc_html( $wp_query->found_posts ); ?></strong> ngành học
				</p>
			</div>

			<div class="flex items-center gap-2 shrink-0">
				<div id="major-mobile-reset">
					<?php if ( $has_active_filters ) : ?>
						<a href="<?php echo esc_url( $archive_link ); ?>" 
						   class="js-ltdh-reset-filter inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 hover:bg-rose-100 min-h-[38px] transition-colors"
						   title="Xóa tất cả bộ lọc">
							<span>✕ Xóa lọc</span>
						</a>
					<?php endif; ?>
				</div>

				<button type="button" 
						id="ltdh-mobile-major-filter-btn" 
						aria-expanded="false" 
						aria-controls="ltdh-major-filter-drawer"
						class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border font-bold text-xs transition-all duration-200 min-h-[38px] cursor-pointer shadow-2xs <?php echo $has_active_filters ? 'bg-blue-50 border-blue-200 text-[#00308b]' : 'bg-white hover:bg-slate-50 border-slate-200 text-slate-700'; ?>">
					<svg class="w-4 h-4 text-current shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
					</svg>
					<span id="ltdh-mobile-major-filter-btn-text">Bộ lọc</span>
					<span id="major-mobile-filter-badge">
						<?php if ( $active_filter_count > 0 ) : ?>
							<span class="inline-flex items-center justify-center px-1.5 py-0.2 text-[10px] font-black rounded-full bg-[#EA580C] text-white shrink-0 min-w-[18px]">
								<?php echo esc_html( $active_filter_count ); ?>
							</span>
						<?php endif; ?>
					</span>
				</button>
			</div>
		</div>

		<!-- Mobile Active Filter Chips (Scrollable row) -->
		<div id="major-mobile-chips" class="md:hidden -mt-1 mb-3">
			<?php if ( ! empty( $active_filter_items ) ) : ?>
				<div class="flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden py-1">
					<?php foreach ( $active_filter_items as $item ) : 
						$remove_args = $preserved_args;
						unset( $remove_args[ $item['key'] ] );
						$chip_base   = ( $item['key'] === 'nhom_nganh' || empty( $selected_cat ) ) ? home_url( '/nhom-nganh/' ) : home_url( '/nhom-nganh/' . $selected_cat . '/' );
						$remove_url  = ! empty( $remove_args ) ? add_query_arg( $remove_args, $chip_base ) : $chip_base;
					?>
						<a href="<?php echo esc_url( $remove_url ); ?>" data-filter-key="<?php echo esc_attr( $item['key'] ); ?>" class="js-ltdh-chip-remove inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-blue-50 text-blue-900 border border-blue-200/60 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition-colors shrink-0 select-none">
							<span class="truncate max-w-[140px]"><?php echo esc_html( $item['label'] ); ?></span>
							<span class="text-blue-400 hover:text-rose-600 font-bold ml-0.5">✕</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- ============ OFF-CANVAS FILTER DRAWER (SLIDES IN FROM LEFT ON MOBILE) ============ -->
		<div id="ltdh-major-filter-drawer-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[70] transition-opacity duration-300 opacity-0 pointer-events-none md:hidden touch-none" style="touch-action: none;" aria-hidden="true"></div>

		<aside id="ltdh-major-filter-drawer" 
		       class="fixed inset-y-0 left-0 w-[86%] max-w-[340px] bg-white shadow-2xl z-[80] transform -translate-x-full transition-transform duration-300 ease-out flex flex-col md:hidden"
		       role="dialog" 
		       aria-modal="true" 
		       aria-label="Bộ lọc tìm kiếm ngành học">

			<!-- Drawer Top Header -->
			<div class="px-4 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/90 shrink-0 touch-none" style="touch-action: none;">
				<div class="flex items-center gap-2">
					<svg class="w-5 h-5 text-brand-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
					</svg>
					<h3 class="font-extrabold text-slate-900 text-sm sm:text-base">Bộ lọc ngành học</h3>
				</div>
				<button type="button" 
						id="ltdh-major-filter-drawer-close" 
						class="p-2 -mr-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 active:scale-95 transition-all cursor-pointer"
						aria-label="Đóng bộ lọc">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
				</button>
			</div>

			<!-- Drawer Scrollable Content Wrapper -->
			<div id="catalog-drawer-major-filter-wrapper" class="flex-1 overflow-y-auto overscroll-contain p-4 space-y-4" style="overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">
				<!-- #major-filter-body relocated here on mobile via JS -->
			</div>

			<!-- Drawer Sticky Footer -->
			<div class="p-3.5 border-t border-slate-100 bg-white flex items-center gap-2.5 shrink-0 shadow-lg touch-none" style="touch-action: none;">
				<button type="button" 
						id="ltdh-major-drawer-reset-btn"
						class="js-ltdh-reset-filter flex-1 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 font-bold text-xs text-center transition-all cursor-pointer">
					Xóa lọc
				</button>
				<button type="button" 
						id="ltdh-major-drawer-apply-btn" 
						class="flex-2 py-2.5 px-4 rounded-xl bg-[#00308b] hover:bg-blue-900 active:scale-95 text-white font-extrabold text-xs text-center shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
					<span>Áp dụng</span>
					<span class="js-major-filter-count-badge font-bold">(<?php echo esc_html( $wp_query->found_posts ); ?>)</span>
				</button>
			</div>
		</aside>

		<script>
		(function() {
			function setupMajorFilterDrawer() {
				var openBtn = document.getElementById('ltdh-mobile-major-filter-btn');
				var closeBtn = document.getElementById('ltdh-major-filter-drawer-close');
				var drawer = document.getElementById('ltdh-major-filter-drawer');
				var overlay = document.getElementById('ltdh-major-filter-drawer-overlay');
				var applyBtn = document.getElementById('ltdh-major-drawer-apply-btn');
				var desktopWrapper = document.getElementById('catalog-desktop-major-filter-wrapper');
				var drawerWrapper = document.getElementById('catalog-drawer-major-filter-wrapper');
				var filterBody = document.getElementById('major-filter-body');

				var touchStartY = 0;
				function onTouchStart(e) {
					if (e.touches && e.touches.length === 1) {
						touchStartY = e.touches[0].clientY;
					}
				}

				function onTouchMoveLock(e) {
					var scrollable = e.target.closest('#catalog-drawer-major-filter-wrapper');
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
					if (!filterBody) return;
					if (window.innerWidth < 768) {
						if (drawerWrapper && !drawerWrapper.contains(filterBody)) {
							drawerWrapper.appendChild(filterBody);
						}
					} else {
						if (desktopWrapper && !desktopWrapper.contains(filterBody)) {
							desktopWrapper.appendChild(filterBody);
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
				if (applyBtn) applyBtn.onclick = closeDrawer;

				relocate();
				window.addEventListener('resize', relocate);
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', setupMajorFilterDrawer);
			} else {
				setupMajorFilterDrawer();
			}
		})();
		</script>

		<!-- ============ MAJORS RESULTS GRID (2 COLUMNS ON MOBILE, 3 ON DESKTOP) ============ -->
		<div id="major-results-wrapper" class="relative">
			<div id="major-results-container" class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					get_template_part( 'template-parts/cards/card-major' );
				endwhile;
			else :
			?>
				<div class="col-span-full text-center py-16 bg-white border border-slate-200 rounded-2xl p-8 shadow-xs">
					<div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
						<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z" />
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
						</svg>
					</div>
					<h3 class="text-lg font-bold text-slate-800 mb-1">Không tìm thấy ngành học phù hợp</h3>
					<p class="text-slate-500 text-sm max-w-md mx-auto mb-6">Vui lòng thử thay đổi từ khóa tìm kiếm hoặc chọn lại nhóm ngành khác.</p>
					<a href="<?php echo esc_url( $archive_link ); ?>" class="js-ltdh-reset-filter inline-flex items-center gap-2 bg-brand-primary text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-brand-primary-dark transition-colors cursor-pointer">
						Xóa tất cả bộ lọc
					</a>
				</div>
			<?php
			endif;
			?>
			</div><!-- #major-results-container -->
		</div><!-- #major-results-wrapper -->

		<!-- ============ PAGINATION ============ -->
		<div id="major-pagination-container" class="mt-12 flex justify-center theme-pagination">
			<?php
			the_posts_pagination( [
				'mid_size'  => 2,
				'prev_text' => '← Trước',
				'next_text' => 'Sau →',
			] );
			?>
		</div>

	</div>
</main>

<?php
get_footer();
