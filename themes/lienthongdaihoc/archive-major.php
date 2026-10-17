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
$selected_cat    = isset( $_GET['nhom_nganh'] ) ? sanitize_text_field( $_GET['nhom_nganh'] ) : '';
$selected_search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$selected_sort   = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : '';
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
$archive_link    = get_post_type_archive_link( 'major' ) ?: home_url( '/nganh-hoc/' );

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

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

		<!-- ============ TOP FILTER BAR (FULL-WIDTH) ============ -->
		<div class="bg-white border border-slate-100 rounded-2xl p-4 sm:p-5 md:p-6 shadow-sm mb-8 transition-all">
			
			<!-- Header inside Filter: Title, Found Counter & Desktop Reset Action -->
			<div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 sm:pb-5 border-b border-slate-100 gap-3">
				<div>
					<h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
						<?php echo $active_cat_term ? 'Nhóm ngành: ' . esc_html( $active_cat_term->name ) : 'Danh sách Ngành đào tạo Liên thông Đại học'; ?>
					</h2>
					<p class="text-xs md:text-sm text-slate-500 mt-1">
						Tìm thấy <strong class="text-brand-primary font-extrabold text-sm md:text-base"><?php echo esc_html( $wp_query->found_posts ); ?></strong> ngành học phù hợp.
					</p>
				</div>
				<?php if ( $has_active_filters ) : ?>
					<div class="hidden sm:block">
						<a href="<?php echo esc_url( $archive_link ); ?>" class="js-ltdh-reset-filter inline-flex items-center gap-1.5 text-xs md:text-sm font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 px-3.5 py-2 rounded-lg transition-colors min-h-[40px]">
							<span>✕</span> <span>Xóa tất cả bộ lọc</span>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<!-- Mobile Filter Bar: Toggle Trigger & Active Filter Chips (Visible only on < md) -->
			<div class="md:hidden mt-3">
				<div class="flex items-center gap-2">
					<button type="button" 
							id="ltdh-mobile-filter-btn" 
							aria-expanded="false" 
							aria-controls="major-filter-body"
							class="flex-1 flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl border font-bold text-xs sm:text-sm transition-all duration-200 min-h-[44px] cursor-pointer shadow-2xs <?php echo $has_active_filters ? 'bg-blue-50/90 border-blue-200/90 text-[#00308b]' : 'bg-slate-50 hover:bg-slate-100 border-slate-200/80 text-slate-700'; ?>">
						<div class="flex items-center gap-2 min-w-0">
							<svg class="w-4 h-4 text-current shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
							</svg>
							<span id="ltdh-mobile-filter-btn-text">Bộ lọc &amp; Tìm kiếm</span>
							<?php if ( $active_filter_count > 0 ) : ?>
								<span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[11px] font-black rounded-full bg-[#EA580C] text-white shrink-0 min-w-[20px] shadow-2xs">
									<?php echo esc_html( $active_filter_count ); ?>
								</span>
							<?php endif; ?>
						</div>
						<svg id="ltdh-mobile-filter-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
						</svg>
					</button>

					<?php if ( $has_active_filters ) : ?>
						<a href="<?php echo esc_url( $archive_link ); ?>" 
						   class="js-ltdh-reset-filter flex items-center justify-center gap-1 px-3.5 py-2.5 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100 min-h-[44px] transition-colors shrink-0"
						   title="Xóa tất cả bộ lọc">
							<span>✕ Xóa lọc</span>
						</a>
					<?php endif; ?>
				</div>

				<!-- Active Filter Chips (Scrollable row) -->
				<?php if ( ! empty( $active_filter_items ) ) : ?>
					<div class="flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden pt-2.5 pb-0.5">
						<?php foreach ( $active_filter_items as $item ) : 
							$remove_url = remove_query_arg( $item['key'] );
						?>
							<a href="<?php echo esc_url( $remove_url ); ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-semibold bg-blue-50 text-blue-900 border border-blue-200/60 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition-colors shrink-0 select-none">
								<span class="truncate max-w-[140px]"><?php echo esc_html( $item['label'] ); ?></span>
								<span class="text-blue-400 hover:text-rose-600 font-bold ml-0.5">✕</span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Filter Form Controls (Collapsible on mobile, fully visible on desktop) -->
			<div id="major-filter-body" class="hidden md:block pt-4 md:pt-0">
				<form id="major-filter-form" action="<?php echo esc_url( $archive_link ); ?>" method="GET" class="space-y-4">
					
					<!-- Row 1: Search + Nhóm ngành Dropdown + Sort Dropdown + Limit Dropdown -->
					<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
					
					<!-- 1. Search Box -->
					<div class="relative">
						<span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
						</span>
						<input type="text" 
							   name="s" 
							   value="<?php echo esc_attr( $selected_search ); ?>" 
							   placeholder="Tìm tên ngành, mã ngành..." 
							   class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none placeholder-slate-400 min-h-[44px] transition-all">
					</div>

					<!-- 2. Nhóm ngành Select -->
					<div>
						<select name="nhom_nganh" onchange="this.form.submit()" class="w-full py-2.5 px-3.5 border border-slate-200 rounded-xl text-sm font-medium bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
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
						<select name="sort" onchange="this.form.submit()" class="w-full py-2.5 px-3.5 border border-slate-200 rounded-xl text-sm font-medium bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
							<option value="" <?php selected( $selected_sort, '' ); ?>>Sắp xếp: Mặc định</option>
							<option value="title_asc" <?php selected( $selected_sort, 'title_asc' ); ?>>Tên ngành (A-Z)</option>
							<option value="title_desc" <?php selected( $selected_sort, 'title_desc' ); ?>>Tên ngành (Z-A)</option>
							<option value="date_desc" <?php selected( $selected_sort, 'date_desc' ); ?>>Mới nhất</option>
						</select>
					</div>

					<!-- 4. Limit Select -->
					<div>
						<select name="limit" onchange="this.form.submit()" class="w-full py-2.5 px-3.5 border border-slate-200 rounded-xl text-sm font-medium bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
							<option value="12" <?php selected( $selected_limit, 12 ); ?>>Hiển thị: 12 ngành</option>
							<option value="24" <?php selected( $selected_limit, 24 ); ?>>Hiển thị: 24 ngành</option>
							<option value="36" <?php selected( $selected_limit, 36 ); ?>>Hiển thị: 36 ngành</option>
							<option value="50" <?php selected( $selected_limit, 50 ); ?>>Hiển thị: 50 ngành</option>
						</select>
					</div>

				</div>

				<!-- Row 2: Quick Pill Tabs for Nhóm ngành -->
				<div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2">
					<span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 shrink-0">Nhóm ngành:</span>
					
					<!-- All Majors Tab -->
					<a href="<?php echo esc_url( $all_tab_url ); ?>"
					   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs md:text-sm font-bold transition-all min-h-[34px] <?php echo empty( $selected_cat ) ? 'bg-brand-primary text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'; ?>">
						<span>Tất cả</span>
						<span class="text-[11px] px-1.5 py-0.2 rounded-full font-black <?php echo empty( $selected_cat ) ? 'bg-white/20 text-white' : 'bg-white text-slate-600 border border-slate-200/60'; ?>">
							<?php echo esc_html( $total_majors ); ?>
						</span>
					</a>

					<!-- Dynamic Nhóm ngành Tabs -->
					<?php if ( ! empty( $major_cats ) ) : ?>
						<?php foreach ( $major_cats as $cat ) : ?>
							<?php
							$is_cat_active = ( $selected_cat === $cat->slug );
							$cat_args      = array_merge( $preserved_args, [ 'nhom_nganh' => $cat->slug ] );
							$cat_tab_url   = add_query_arg( $cat_args, $archive_link );
							?>
							<a href="<?php echo esc_url( $cat_tab_url ); ?>"
							   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs md:text-sm font-bold transition-all min-h-[34px] <?php echo $is_cat_active ? 'bg-brand-primary text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'; ?>">
								<span><?php echo esc_html( $cat->name ); ?></span>
								<span class="text-[11px] px-1.5 py-0.2 rounded-full font-black <?php echo $is_cat_active ? 'bg-white/20 text-white' : 'bg-white text-slate-600 border border-slate-200/60'; ?>">
									<?php echo esc_html( $cat->count ); ?>
								</span>
							</a>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<!-- Mobile Action Buttons (Visible inside open filter on < md) -->
				<div class="pt-3 border-t border-slate-100 flex items-center gap-2 md:hidden">
					<button type="submit" class="flex-1 py-3 px-4 rounded-xl bg-[#00308b] hover:bg-blue-900 text-white font-extrabold text-sm shadow-sm transition-all flex items-center justify-center gap-2 min-h-[46px] cursor-pointer">
						<svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
						<span>Áp dụng bộ lọc (<?php echo esc_html( $wp_query->found_posts ); ?>)</span>
					</button>
					<button type="button" id="ltdh-mobile-filter-close" class="py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm min-h-[46px] transition-colors cursor-pointer">
						Đóng
					</button>
				</div>

			</form>
		</div><!-- #major-filter-body -->

		<script>
		(function() {
			var btn = document.getElementById('ltdh-mobile-filter-btn');
			var body = document.getElementById('major-filter-body');
			var chevron = document.getElementById('ltdh-mobile-filter-chevron');
			var btnText = document.getElementById('ltdh-mobile-filter-btn-text');
			var closeBtn = document.getElementById('ltdh-mobile-filter-close');
			if (!btn || !body) return;

			function toggleFilter() {
				var isHidden = body.classList.contains('hidden');
				if (isHidden) {
					body.classList.remove('hidden');
					btn.setAttribute('aria-expanded', 'true');
					if (chevron) chevron.classList.add('rotate-180');
					if (btnText) btnText.textContent = 'Thu gọn bộ lọc';
				} else {
					body.classList.add('hidden');
					btn.setAttribute('aria-expanded', 'false');
					if (chevron) chevron.classList.remove('rotate-180');
					if (btnText) btnText.textContent = 'Bộ lọc & Tìm kiếm';
				}
			}

			btn.addEventListener('click', toggleFilter);
			if (closeBtn) {
				closeBtn.addEventListener('click', toggleFilter);
			}
		})();
		</script>
	</div>

		<!-- ============ MAJORS RESULTS GRID (2 COLUMNS ON MOBILE, 3 ON DESKTOP) ============ -->
		<div id="major-results-container" class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					$major_id = get_the_ID();
					$code     = get_field( 'major_code', $major_id ) ?: '';
					$thumb    = get_the_post_thumbnail_url( $major_id, 'medium_large' );
					if ( empty( $thumb ) ) {
						$major_fallbacks = [
							get_stylesheet_directory_uri() . '/assets/images/banner-program.jpg',
							get_stylesheet_directory_uri() . '/assets/images/banner-hero-02.webp',
							get_stylesheet_directory_uri() . '/assets/images/banner-default.jpg',
						];
						$idx   = $major_id % count( $major_fallbacks );
						$thumb = $major_fallbacks[ $idx ];
					}
					
					// Get count of valid, active programs linked to this major
					$prog_count = function_exists( 'ltdh_get_major_valid_programs_count' )
						? ltdh_get_major_valid_programs_count( $major_id )
						: 0;

					// Get category term
					$terms    = get_the_terms( $major_id, 'major_cat' );
					$cat_name = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->name : 'Ngành đào tạo';
			?>
					<div class="bg-white border border-slate-100/80 rounded-xl md:rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:border-brand-primary/20 transition-all duration-300 flex flex-col justify-between group">
						
						<!-- Header Thumbnail Area -->
						<div class="relative aspect-[4/3] sm:aspect-[16/10] md:aspect-auto md:h-60 lg:h-64 bg-slate-100 overflow-hidden">
							<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
							<div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-900/20 to-transparent"></div>

							<!-- Badges -->
							<div class="absolute top-2 left-2 md:top-3 md:left-3 flex flex-wrap gap-1 md:gap-2">
								<span class="inline-flex items-center px-1.5 py-0.5 md:px-2.5 md:py-1 rounded-md text-[10px] md:text-xs font-black bg-slate-900/80 text-white backdrop-blur-md shadow-2xs">
									Mã: <?php echo esc_html( $code ?: 'Liên thông' ); ?>
								</span>
							</div>

							<?php if ( $prog_count > 0 ) : ?>
								<div class="absolute bottom-2 right-2 md:bottom-3 md:right-3">
									<span class="inline-flex items-center gap-1 px-1.5 py-0.5 md:px-2.5 md:py-1 rounded-full text-[10px] md:text-xs font-extrabold bg-emerald-500 text-white shadow-2xs backdrop-blur-md">
										<span><?php echo esc_html( $prog_count ); ?></span> <span class="hidden sm:inline">chương trình</span><span class="sm:hidden">CT</span>
									</span>
								</div>
							<?php endif; ?>
						</div>

						<!-- Card Body -->
						<div class="p-3 sm:p-4 md:p-6 flex-1 flex flex-col justify-between">
							<div>
								<div class="text-[10px] sm:text-xs font-bold text-brand-primary uppercase tracking-wider mb-1 line-clamp-1">
									<?php echo esc_html( $cat_name ); ?>
								</div>
								<h3 class="font-black text-slate-900 text-xs sm:text-base md:text-xl hover:text-brand-primary transition-colors mb-1.5 sm:mb-2.5 line-clamp-2 leading-tight sm:leading-snug">
									<a href="<?php the_permalink(); ?>">Ngành <?php the_title(); ?></a>
								</h3>
								<p class="hidden md:block text-sm text-slate-500 line-clamp-2 leading-relaxed mb-4">
									<?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?>
								</p>
							</div>

							<!-- Action Footer -->
							<div class="pt-2.5 sm:pt-3 md:pt-4 border-t border-slate-100 mt-auto">
								<a href="<?php the_permalink(); ?>" class="w-full inline-flex items-center justify-center gap-1.5 sm:gap-2 bg-slate-100 hover:bg-brand-primary text-slate-700 hover:text-white py-2 sm:py-2.5 md:py-3 px-2 sm:px-3 md:px-4 rounded-lg md:rounded-xl font-bold transition-all text-xs sm:text-sm group/btn min-h-[38px] md:min-h-[44px]">
									<span>Khám phá</span><span class="hidden sm:inline">ngành học</span>
									<svg class="w-3.5 h-3.5 md:w-4 md:h-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
								</a>
							</div>
						</div>

					</div>
			<?php
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
					<a href="<?php echo esc_url( $archive_link ); ?>" class="inline-flex items-center gap-2 bg-brand-primary text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-brand-primary-dark transition-colors">
						Xóa tất cả bộ lọc
					</a>
				</div>
			<?php
			endif;
			?>
		</div>

		<!-- ============ PAGINATION ============ -->
		<div class="mt-12 flex justify-center theme-pagination">
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
