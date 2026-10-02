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
?>

<main id="primary" class="site-main bg-slate-50 min-h-screen">
	<?php get_template_part( 'template-parts/banner' ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

		<!-- ============ TOP FILTER BAR (FULL-WIDTH) ============ -->
		<div class="bg-white border border-slate-100 rounded-2xl p-5 md:p-6 shadow-sm mb-8 transition-all">
			
			<!-- Header inside Filter: Title, Found Counter & Reset Action -->
			<div class="flex flex-col sm:flex-row sm:items-center justify-between pb-5 mb-5 border-b border-slate-100 gap-3">
				<div>
					<h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
						<?php echo $active_cat_term ? 'Nhóm ngành: ' . esc_html( $active_cat_term->name ) : 'Danh sách Ngành đào tạo Liên thông Đại học'; ?>
					</h1>
					<p class="text-xs md:text-sm text-slate-500 mt-1">
						Tìm thấy <strong class="text-brand-primary font-extrabold text-sm md:text-base"><?php echo esc_html( $wp_query->found_posts ); ?></strong> ngành học phù hợp.
					</p>
				</div>
				<?php if ( $has_active_filters ) : ?>
					<div>
						<a href="<?php echo esc_url( $archive_link ); ?>" class="js-ltdh-reset-filter inline-flex items-center gap-1.5 text-xs md:text-sm font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 px-3.5 py-2 rounded-lg transition-colors min-h-[40px]">
							<span>✕</span> <span>Xóa tất cả bộ lọc</span>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<!-- Filter Form Controls -->
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

			</form>
		</div>

		<!-- ============ MAJORS RESULTS GRID (FULL-WIDTH 3 COLUMNS) ============ -->
		<div id="major-results-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
					
					// Get count of programs linked to this major
					$prog_query = new WP_Query( [
						'post_type'      => 'program',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'meta_query'     => [
							[
								'key'     => 'major_relationship',
								'value'   => $major_id,
								'compare' => '=',
							],
						],
					] );
					$prog_count = $prog_query->post_count;
					wp_reset_postdata();

					// Get category term
					$terms    = get_the_terms( $major_id, 'major_cat' );
					$cat_name = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->name : 'Ngành đào tạo';
			?>
					<div class="bg-white border border-slate-100/60 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:border-brand-primary/20 transition-all duration-300 flex flex-col justify-between group">
						
						<!-- Header Thumbnail Area -->
						<div class="relative h-44 bg-slate-100 overflow-hidden">
							<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
							<div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-900/20 to-transparent"></div>

							<!-- Badges -->
							<div class="absolute top-3 left-3 flex flex-wrap gap-2">
								<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-black bg-slate-900/80 text-white backdrop-blur-md shadow-xs">
									Mã: <?php echo esc_html( $code ?: 'Liên thông' ); ?>
								</span>
							</div>

							<?php if ( $prog_count > 0 ) : ?>
								<div class="absolute bottom-3 right-3">
									<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-500 text-white shadow-sm backdrop-blur-md">
										<span><?php echo esc_html( $prog_count ); ?></span> chương trình
									</span>
								</div>
							<?php endif; ?>
						</div>

						<!-- Card Body -->
						<div class="p-5 md:p-6 flex-1 flex flex-col justify-between">
							<div>
								<div class="text-xs font-bold text-brand-primary uppercase tracking-wider mb-1.5">
									<?php echo esc_html( $cat_name ); ?>
								</div>
								<h3 class="font-black text-slate-900 text-lg md:text-xl hover:text-brand-primary transition-colors mb-2.5 line-clamp-2 leading-snug">
									<a href="<?php the_permalink(); ?>">Ngành <?php the_title(); ?></a>
								</h3>
								<p class="text-sm text-slate-500 line-clamp-2 leading-relaxed mb-4">
									<?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?>
								</p>
							</div>

							<!-- Action Footer -->
							<div class="pt-4 border-t border-slate-100 mt-auto">
								<a href="<?php the_permalink(); ?>" class="w-full inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-brand-primary text-slate-700 hover:text-white py-3 px-4 rounded-xl font-bold transition-all text-sm group/btn min-h-[44px]">
									<span>Khám phá ngành học</span>
									<svg class="w-4 h-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
								</a>
							</div>
						</div>

					</div>
			<?php
				endwhile;
			else :
			?>
				<div class="col-span-full text-center py-16 bg-white border border-slate-200 rounded-2xl p-8 shadow-xs">
					<div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">🎓</div>
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
