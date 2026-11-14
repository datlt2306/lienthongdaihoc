<?php
/**
 * Blog Archive Template - Tin tức tuyển sinh & cẩm nang đào tạo
 *
 * UI/UX Pro Max Edition
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Active category filter — read from URL routing (/tin-tuc/slug/) via WordPress query vars
$active_cat      = is_category() ? get_queried_object() : null;
$active_cat_slug = $active_cat ? $active_cat->slug : '';

// Blog categories for sidebar/tabs
$blog_cats = get_categories( [ 'hide_empty' => true, 'exclude' => [ 1 ] ] );

// Filter parameters
$school_filter   = isset( $_GET['truong'] ) ? intval( $_GET['truong'] ) : 0;
$major_filter    = isset( $_GET['nganh'] ) ? intval( $_GET['nganh'] ) : 0;
$program_filter  = isset( $_GET['chuong-trinh'] ) ? intval( $_GET['chuong-trinh'] ) : 0;
$is_filtered     = ( $school_filter || $major_filter || $program_filter );

$filter_title = '';
if ( $school_filter ) {
	$filter_title = get_the_title( $school_filter );
} elseif ( $major_filter ) {
	$filter_title = get_the_title( $major_filter );
} elseif ( $program_filter ) {
	$filter_title = get_the_title( $program_filter );
}

// Featured post (most recent, optionally sticky) - Only show if not filtering
$featured_post = null;
if ( ! $is_filtered ) {
	$sticky_ids = get_option( 'sticky_posts' );
	if ( ! empty( $sticky_ids ) ) {
		$featured_posts = get_posts( [ 'post__in' => $sticky_ids, 'numberposts' => 1 ] );
		$featured_post  = $featured_posts[0] ?? null;
	}
	if ( ! $featured_post ) {
		$featured_posts = get_posts( [
			'numberposts'      => 1,
			'category__not_in' => [ 1 ],
		] );
		$featured_post = $featured_posts[0] ?? null;
	}
}

// Main query args
$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
$cat_args = [];
if ( $active_cat ) {
	$cat_args['cat'] = $active_cat->term_id;
}
if ( $featured_post ) {
	$cat_args['post__not_in'] = [ $featured_post->ID ];
}

// Apply metadata filtering for related entities
if ( $is_filtered ) {
	$cat_args['meta_query'] = [ 'relation' => 'AND' ];
	if ( $school_filter ) {
		$cat_args['meta_query'][] = [
			'key'     => 'related_schools',
			'value'   => '"' . $school_filter . '"',
			'compare' => 'LIKE',
		];
	}
	if ( $major_filter ) {
		$cat_args['meta_query'][] = [
			'key'     => 'related_majors',
			'value'   => '"' . $major_filter . '"',
			'compare' => 'LIKE',
		];
	}
	if ( $program_filter ) {
		$cat_args['meta_query'][] = [
			'key'     => 'related_programs',
			'value'   => '"' . $program_filter . '"',
			'compare' => 'LIKE',
		];
	}
}

$blog_query = new WP_Query( array_merge( [
	'post_type'      => 'post',
	'posts_per_page' => 8,
	'post_status'    => 'publish',
	'paged'          => $paged,
], $cat_args ) );

// Total post counts for badge
$total_published = wp_count_posts( 'post' )->publish ?? 0;
?>

<div class="site-blog-archive bg-slate-50/60 min-h-screen">
	<!-- Hero Banner -->
	<?php get_template_part( 'template-parts/banner' ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
		
		<?php if ( $is_filtered && $filter_title ) : ?>
			<!-- Active Filter Banner (Zero emojis, crisp SVG) -->
			<div class="mb-8 bg-blue-50/80 border border-blue-200/90 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
				<div class="flex items-center gap-3">
					<div class="w-10 h-10 rounded-xl bg-[#00308b] text-white flex items-center justify-center shrink-0 shadow-sm">
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
						</svg>
					</div>
					<div>
						<h3 class="font-extrabold text-slate-900 text-sm sm:text-base leading-snug">Đang lọc tin tức tuyển sinh</h3>
						<p class="text-xs sm:text-sm text-slate-600 mt-0.5">Hiển thị tin tức liên quan đến: <strong class="text-[#00308b] font-bold"><?php echo esc_html( $filter_title ); ?></strong></p>
					</div>
				</div>
				<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" 
				   class="inline-flex items-center justify-center gap-1.5 bg-white border border-slate-200 text-slate-700 hover:text-rose-600 hover:border-rose-200 px-4 py-2 rounded-xl font-bold text-xs sm:text-sm hover:bg-slate-50 transition-all duration-200 min-h-[38px] shrink-0 shadow-2xs">
					<svg class="w-4 h-4 text-slate-400 group-hover:text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
					</svg>
					<span>Tắt bộ lọc</span>
				</a>
			</div>
		<?php endif; ?>

		<!-- Category Pills Navigation (Horizontal scroll on mobile, zero emojis) -->
		<nav class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 mb-8 border-b border-slate-200/90" aria-label="<?php esc_attr_e( 'Danh mục tin tức', 'ltdh' ); ?>">
			<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" 
			   class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 min-h-[38px] flex items-center gap-1.5 shrink-0 <?php echo empty( $active_cat_slug ) ? 'bg-[#00308b] text-white shadow-sm ring-2 ring-[#00308b]/20' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:border-slate-300 hover:bg-slate-50 shadow-2xs'; ?>">
				<span>Tất cả tin tức</span>
				<span class="text-[11px] px-1.5 py-0.2 rounded-full <?php echo empty( $active_cat_slug ) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'; ?>">
					<?php echo esc_html( $total_published ); ?>
				</span>
			</a>
			<?php foreach ( $blog_cats as $bcat ) :
				$is_active_cat = ( $active_cat_slug === $bcat->slug );
			?>
				<a href="<?php echo esc_url( get_category_link( $bcat->term_id ) ); ?>" 
				   class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 min-h-[38px] flex items-center gap-2 shrink-0 <?php echo $is_active_cat ? 'bg-[#00308b] text-white shadow-sm ring-2 ring-[#00308b]/20' : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:border-slate-300 hover:bg-slate-50 shadow-2xs'; ?>">
					<span><?php echo esc_html( $bcat->name ); ?></span>
					<span class="text-[11px] px-1.5 py-0.2 rounded-full <?php echo $is_active_cat ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'; ?>">
						<?php echo esc_html( $bcat->count ); ?>
					</span>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

			<!-- ====== LEFT: CONTENT ====== -->
			<div class="lg:col-span-8 space-y-10">

				<!-- Featured Post (Split card editorial design) -->
				<?php if ( $featured_post && empty( $active_cat_slug ) && $paged === 1 ) :
					$feat_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $featured_post->ID, 'large' ) : ( get_the_post_thumbnail_url( $featured_post->ID, 'large' ) ?: ltdh_get_fallback_image( 'post' ) );
					$feat_cats  = get_the_category( $featured_post->ID );
					$feat_cat   = null;
					foreach ( $feat_cats as $fc ) {
						if ( $fc->slug !== 'uncategorized' ) { $feat_cat = $fc; break; }
					}
					$feat_school_id = get_field( 'school_relationship', $featured_post->ID );
					$feat_school_name = $feat_school_id ? get_the_title( $feat_school_id ) : '';
					$feat_content_raw = get_post_field( 'post_content', $featured_post->ID );
					$feat_reading_time = max( 2, (int) ceil( str_word_count( strip_tags( (string) $feat_content_raw ) ) / 200 ) );
				?>
				<div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:border-blue-300 transition-all duration-300 group grid grid-cols-1 md:grid-cols-12 items-stretch cursor-pointer">
					<!-- Thumbnail Left -->
					<a href="<?php echo esc_url( get_permalink( $featured_post->ID ) ); ?>" class="md:col-span-7 relative block aspect-[16/9] md:aspect-auto md:min-h-[280px] md:h-full w-full bg-slate-100 overflow-hidden shrink-0">
						<img src="<?php echo esc_url( $feat_thumb ); ?>" alt="<?php echo esc_attr( $featured_post->post_title ); ?>" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" loading="eager" />
						<div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent pointer-events-none"></div>

						<?php if ( $feat_school_name ) : ?>
							<span class="absolute top-3 left-3 z-10 bg-white/95 backdrop-blur-md text-[#00308b] text-xs font-extrabold px-3 py-1 rounded-full shadow-sm border border-white/60">
								<?php echo esc_html( $feat_school_name ); ?>
							</span>
						<?php endif; ?>

						<span class="absolute bottom-3 right-3 z-10 bg-slate-900/80 backdrop-blur-xs text-white text-xs font-medium px-2.5 py-0.5 rounded-md flex items-center gap-1">
							<svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
							<span><?php echo esc_html( $feat_reading_time ); ?> phút đọc</span>
						</span>
					</a>
					
					<!-- Content Right -->
					<div class="md:col-span-5 p-5 lg:p-6 flex flex-col justify-between">
						<div>
							<div class="flex items-center gap-2 mb-3">
								<span class="bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-2xs">
									Nổi bật
								</span>
								<?php if ( $feat_cat ) : ?>
									<span class="text-xs text-[#00308b] font-bold uppercase tracking-wider"><?php echo esc_html( $feat_cat->name ); ?></span>
								<?php endif; ?>
							</div>
							<h2 class="text-base sm:text-lg lg:text-xl font-black text-slate-900 leading-snug hover:text-[#00308b] transition-colors line-clamp-3">
								<a href="<?php echo esc_url( get_permalink( $featured_post->ID ) ); ?>">
									<?php echo esc_html( $featured_post->post_title ); ?>
								</a>
							</h2>
							<p class="text-xs sm:text-sm text-slate-600 line-clamp-2 sm:line-clamp-3 leading-relaxed mt-2.5">
								<?php echo esc_html( wp_trim_words( $featured_post->post_excerpt ?: $featured_post->post_content, 26 ) ); ?>
							</p>
						</div>

						<div class="flex items-center justify-between pt-3.5 border-t border-slate-100 text-xs text-slate-400 mt-4">
							<div class="inline-flex items-center gap-1.5">
								<svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
								</svg>
								<span><?php echo get_the_date( 'd/m/Y', $featured_post->ID ); ?></span>
							</div>

							<a href="<?php echo esc_url( get_permalink( $featured_post->ID ) ); ?>" class="text-[#00308b] font-bold inline-flex items-center gap-1 group-hover:gap-1.5 transition-all">
								<span>Chi tiết</span>
								<svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
								</svg>
							</a>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- Articles Section -->
				<div class="space-y-6">
					<div class="flex items-center justify-between pb-3 border-b border-slate-200">
						<h2 class="font-black text-slate-900 text-sm uppercase tracking-wider flex items-center gap-2">
							<span class="w-2.5 h-2.5 rounded-full bg-[#00308b]"></span>
							<span><?php echo $active_cat ? esc_html( $active_cat->name ) : 'Bài viết mới nhất'; ?></span>
						</h2>
						<span class="text-xs text-slate-500 font-bold bg-slate-100 px-3 py-1 rounded-full"><?php echo esc_html( $blog_query->found_posts ); ?> Bài viết</span>
					</div>

					<!-- 2-Column Responsive Card Grid -->
					<div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
						<?php
						if ( $blog_query->have_posts() ) :
							while ( $blog_query->have_posts() ) : $blog_query->the_post();
								get_template_part( 'template-parts/cards/card', 'post', [
									'post_id' => get_the_ID(),
								] );
							endwhile;
							wp_reset_postdata();
						else :
							echo '<div class="col-span-2 text-center text-slate-500 py-12 bg-white rounded-2xl border border-slate-200">Chưa có bài viết nào thuộc danh mục này.</div>';
						endif;
						?>
					</div>

					<!-- Pagination -->
					<?php if ( $blog_query->max_num_pages > 1 ) : ?>
					<nav class="pt-8 flex justify-center theme-pagination" aria-label="<?php esc_attr_e( 'Phân trang tin tức', 'ltdh' ); ?>">
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
							'total'     => $blog_query->max_num_pages,
							'prev_text' => '<span class="inline-flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>Trước</span>',
							'next_text' => '<span class="inline-flex items-center gap-1">Sau<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>',
							'add_args'  => $query_args,
						] );
						?>
					</nav>
					<?php endif; ?>
				</div>
			</div>

			<!-- ====== RIGHT: SIDEBAR ====== -->
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
