<?php
/**
 * Category Archive Template - Chuyên mục tin tức & cẩm nang tuyển sinh
 *
 * UI/UX Pro Max Architecture:
 * - Grand Hero Banner with clean Title & Subtitle (Zero raw "Category: " strings)
 * - Horizontal scrollable Category Pills navigation with active state
 * - 2-Column high-performance article grid (card-post partial)
 * - Contextual sidebar with fallback widgets
 * - Smooth accessible pagination
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$current_cat      = get_queried_object();
$current_cat_id   = $current_cat ? $current_cat->term_id : 0;
$current_cat_slug = $current_cat ? $current_cat->slug : '';
$current_cat_name = $current_cat ? $current_cat->name : single_cat_title( '', false );

// All blog categories for tab pills navigation
$blog_cats = get_categories( [
	'hide_empty' => true,
	'exclude'    => [ 1 ], // Exclude "Uncategorized"
] );

// Query pagination
$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
$total_posts = $wp_query->found_posts ?? 0;
?>

<div class="site-category-archive bg-slate-50/60 min-h-screen">
	<!-- Hero Banner (Standardized Grand Hero with Apple Glassmorphism Card) -->
	<?php get_template_part( 'template-parts/banner' ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

		<!-- Category Pills Navigation (Horizontal scroll on mobile, zero emojis) -->
		<nav class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 mb-6 border-b border-slate-200/90" aria-label="<?php esc_attr_e( 'Danh mục tin tức', 'ltdh' ); ?>">
			<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" 
			   class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:border-slate-300 hover:bg-slate-50 min-h-[38px] flex items-center gap-1.5 shrink-0 shadow-2xs">
				<span>Tất cả tin tức</span>
			</a>
			<?php foreach ( $blog_cats as $bcat ) :
				$is_active = ( (int) $bcat->term_id === (int) $current_cat_id );
			?>
				<a href="<?php echo esc_url( get_category_link( $bcat->term_id ) ); ?>" 
				   class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 min-h-[38px] flex items-center gap-2 shrink-0 <?php echo $is_active ? 'bg-[#00308b] text-white shadow-sm ring-2 ring-[#00308b]/20' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:border-slate-300 hover:bg-slate-50 shadow-2xs'; ?>">
					<span><?php echo esc_html( $bcat->name ); ?></span>
					<span class="text-[11px] px-1.5 py-0.2 rounded-full <?php echo $is_active ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'; ?>">
						<?php echo esc_html( $bcat->count ); ?>
					</span>
				</a>
			<?php endforeach; ?>
		</nav>

		<!-- Active Category Summary & Context Bar -->
		<div class="mb-8 bg-blue-50/70 border border-blue-100 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
			<div class="flex items-center gap-3">
				<div class="w-10 h-10 rounded-xl bg-[#00308b] text-white flex items-center justify-center shrink-0 shadow-sm">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
					</svg>
				</div>
				<div>
					<div class="flex items-center gap-2">
						<span class="text-xs uppercase tracking-wider font-extrabold text-[#00308b]">Đang xem chuyên mục</span>
						<span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
							<?php echo esc_html( $total_posts ); ?> bài viết
						</span>
					</div>
					<h2 class="text-base sm:text-lg font-black text-slate-900 leading-snug mt-0.5">
						<?php echo esc_html( $current_cat_name ); ?>
					</h2>
				</div>
			</div>

			<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" 
			   class="inline-flex items-center justify-center gap-1.5 bg-white border border-slate-200 text-slate-700 hover:text-[#00308b] hover:border-blue-200 px-4 py-2 rounded-xl font-bold text-xs sm:text-sm hover:bg-slate-50 transition-all duration-200 min-h-[38px] shrink-0 shadow-2xs">
				<span>Xem tất cả tin tức</span>
				<svg class="w-4 h-4 text-slate-400 group-hover:text-[#00308b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
				</svg>
			</a>
		</div>

		<!-- Main 12-Column Grid -->
		<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

			<!-- ====== LEFT: ARTICLE CONTENT (8 COLS) ====== -->
			<div class="lg:col-span-8 space-y-8">
				<?php if ( have_posts() ) : ?>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
						<?php
						while ( have_posts() ) : the_post();
							get_template_part( 'template-parts/cards/card', 'post', [
								'post_id' => get_the_ID(),
							] );
						endwhile;
						?>
					</div>

					<!-- Pagination -->
					<?php if ( $wp_query->max_num_pages > 1 ) : ?>
						<nav class="pt-8 flex justify-center theme-pagination" aria-label="<?php esc_attr_e( 'Phân trang bài viết', 'ltdh' ); ?>">
							<?php
							$pagenum_link = html_entity_decode( get_pagenum_link() );
							$query_args   = [];
							$url_parts    = explode( '?', $pagenum_link );
							if ( isset( $url_parts[1] ) ) {
								wp_parse_str( $url_parts[1], $query_args );
							}
							$clean_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
							$clean_link = trailingslashit( preg_replace( '%/page/[0-9]+/?%', '/', $clean_link ) );

							echo paginate_links( [
								'base'      => $clean_link . '%_%',
								'format'    => 'page/%#%/',
								'current'   => $paged,
								'total'     => $wp_query->max_num_pages,
								'prev_text' => '<span class="inline-flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>Trước</span>',
								'next_text' => '<span class="inline-flex items-center gap-1">Sau<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>',
								'add_args'  => $query_args,
							] );
							?>
						</nav>
					<?php endif; ?>

				<?php else : ?>
					<!-- Empty State -->
					<div class="bg-white border border-slate-200/90 rounded-2xl p-10 text-center shadow-xs">
						<div class="w-16 h-16 rounded-full bg-blue-50 text-[#00308b] flex items-center justify-center mx-auto mb-4">
							<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
						</div>
						<h3 class="font-extrabold text-slate-800 text-lg mb-2">Chưa có bài viết</h3>
						<p class="text-sm text-slate-500 max-w-md mx-auto mb-6 leading-relaxed">
							Chuyên mục <strong><?php echo esc_html( $current_cat_name ); ?></strong> hiện chưa có bài viết nào được đăng tải. Vui lòng quay lại sau!
						</p>
						<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" 
						   class="inline-flex items-center gap-2 bg-[#00308b] hover:bg-blue-800 text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-xs transition-colors">
							<span>Khám phá tin tức khác</span>
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
							</svg>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<!-- ====== RIGHT: SIDEBAR (4 COLS) ====== -->
			<div class="lg:col-span-4">
				<div class="sticky top-24 space-y-6">
					<?php get_sidebar(); ?>
				</div>
			</div>

		</div>
	</div>
</div>

<?php
get_footer();
