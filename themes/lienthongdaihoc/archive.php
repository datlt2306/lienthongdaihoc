<?php
/**
 * Generic Archive Template - UI/UX Pro Max Edition
 *
 * Handles tag archives, author archives, date archives, and generic taxonomies.
 * Ensures consistent design with category and news hub templates.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Clean title: strips "Category: ", "Tag: ", "Author: "
$archive_title = get_the_archive_title();
$clean_title   = preg_replace( '/^(?:Chuyên mục|Danh mục|Category|Thẻ|Tag|Tác giả|Author|Năm|Tháng|Lưu trữ):\s*/iu', '', $archive_title );
$archive_desc  = get_the_archive_description();
$total_posts   = $wp_query->found_posts ?? 0;
$paged         = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );

// Categories for navigation pills
$blog_cats = get_categories( [
	'hide_empty' => true,
	'exclude'    => [ 1 ],
] );
?>

<div class="site-general-archive bg-slate-50/60 min-h-screen">
	<!-- Hero Banner -->
	<?php get_template_part( 'template-parts/banner' ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

		<!-- Category Navigation Pills -->
		<nav class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 mb-6 border-b border-slate-200/90" aria-label="<?php esc_attr_e( 'Danh mục tin tức', 'ltdh' ); ?>">
			<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" 
			   class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:border-slate-300 hover:bg-slate-50 min-h-[38px] flex items-center gap-1.5 shrink-0 shadow-2xs">
				<span>Tất cả tin tức</span>
			</a>
			<?php foreach ( $blog_cats as $bcat ) : ?>
				<a href="<?php echo esc_url( get_category_link( $bcat->term_id ) ); ?>" 
				   class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 min-h-[38px] flex items-center gap-2 shrink-0 bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:border-slate-300 hover:bg-slate-50 shadow-2xs">
					<span><?php echo esc_html( $bcat->name ); ?></span>
					<span class="text-[11px] px-1.5 py-0.2 rounded-full bg-slate-100 text-slate-500">
						<?php echo esc_html( $bcat->count ); ?>
					</span>
				</a>
			<?php endforeach; ?>
		</nav>

		<!-- Archive Context Bar -->
		<div class="mb-8 bg-blue-50/70 border border-blue-100 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
			<div class="flex items-center gap-3">
				<div class="w-10 h-10 rounded-xl bg-[#00308b] text-white flex items-center justify-center shrink-0 shadow-sm">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
					</svg>
				</div>
				<div>
					<div class="flex items-center gap-2">
						<span class="text-xs uppercase tracking-wider font-extrabold text-[#00308b]">Đang xem danh mục lưu trữ</span>
						<span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
							<?php echo esc_html( $total_posts ); ?> bài viết
						</span>
					</div>
					<h2 class="text-base sm:text-lg font-black text-slate-900 leading-snug mt-0.5">
						<?php echo esc_html( $clean_title ); ?>
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
							Chưa có bài viết nào trong danh mục này. Vui lòng quay lại sau!
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
