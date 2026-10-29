<?php
/**
 * Blog Archive Template - Tin tức tuyển sinh
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

// Recent posts for sidebar
$recent_posts = get_posts( [ 'numberposts' => 5 ] );
?>

<div class="site-blog-archive bg-slate-50/50 min-h-screen">
	<?php get_template_part( 'template-parts/banner' ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
		
		<?php if ( $is_filtered && $filter_title ) : ?>
			<div class="mb-8 bg-blue-50 border border-blue-100 rounded-xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
				<div class="flex items-center gap-3">
					<span class="text-2xl">🔍</span>
					<div>
						<h3 class="font-bold text-slate-800 text-sm md:text-base">Đang lọc tin tức tuyển sinh</h3>
						<p class="text-sm text-slate-500 mt-0.5">Hiển thị tin tức liên quan đến: <strong class="text-[#00308b] font-bold"><?php echo esc_html( $filter_title ); ?></strong></p>
					</div>
				</div>
				<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-lg font-bold text-sm hover:bg-slate-50 transition-all min-h-[38px] flex items-center justify-center">
					❌ Tắt bộ lọc
				</a>
			</div>
		<?php endif; ?>

		<!-- Category Pills Navigation (Horizontal scroll on mobile) -->
		<div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-4 mb-8 border-b border-slate-200">
			<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" 
			   class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all <?php echo empty( $active_cat_slug ) ? 'bg-brand-primary text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?> min-h-[38px] flex items-center justify-center">
				Tất cả tin tức
			</a>
			<?php foreach ( $blog_cats as $bcat ) :
				$is_active_cat = ( $active_cat_slug === $bcat->slug );
			?>
				<a href="<?php echo esc_url( get_category_link( $bcat->term_id ) ); ?>" 
				   class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all <?php echo $is_active_cat ? 'bg-brand-primary text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?> min-h-[38px] flex items-center justify-center">
					<?php echo esc_html( $bcat->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

			<!-- ====== LEFT: CONTENT ====== -->
			<div class="lg:col-span-8 space-y-10">

				<!-- Featured Post (Split card design) -->
				<?php if ( $featured_post && empty( $active_cat_slug ) && $paged === 1 ) :
					$feat_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $featured_post->ID, 'large' ) : ( get_the_post_thumbnail_url( $featured_post->ID, 'large' ) ?: ltdh_get_fallback_image( 'post' ) );
					$feat_cats  = get_the_category( $featured_post->ID );
					$feat_cat   = null;
					foreach ( $feat_cats as $fc ) {
						if ( $fc->slug !== 'uncategorized' ) { $feat_cat = $fc; break; }
					}
					$feat_school_id = get_field( 'school_relationship', $featured_post->ID );
					$feat_school_name = $feat_school_id ? get_the_title( $feat_school_id ) : '';
				?>
				<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs hover:shadow-md transition-all group grid grid-cols-1 md:grid-cols-12 items-stretch">
					<!-- Thumbnail Left -->
					<a href="<?php echo esc_url( get_permalink( $featured_post->ID ) ); ?>" class="md:col-span-7 relative block aspect-[16/9] md:aspect-auto md:min-h-[250px] md:h-full w-full bg-slate-100 overflow-hidden shrink-0">
						<img src="<?php echo esc_url( $feat_thumb ); ?>" alt="<?php echo esc_attr( $featured_post->post_title ); ?>" class="w-full h-full object-cover object-left group-hover:scale-105 transition-transform duration-500" />
						<?php if ( $feat_school_name ) : ?>
							<span class="absolute top-3 left-3 z-10 bg-white/90 backdrop-blur-sm text-[#00308b] text-xs font-extrabold px-3 py-1 rounded-full shadow-sm">
								<?php echo esc_html( $feat_school_name ); ?>
							</span>
						<?php endif; ?>
					</a>
					
					<!-- Content Right -->
					<div class="md:col-span-5 p-5 lg:p-6 flex flex-col justify-between">
						<div>
							<div class="flex items-center gap-2 mb-2.5">
								<span class="bg-brand-primary text-white text-xs font-extrabold px-2.5 py-1 rounded-full uppercase tracking-wider">
									NỔI BẬT
								</span>
								<?php if ( $feat_cat ) : ?>
									<span class="text-xs text-slate-400 font-bold uppercase"><?php echo esc_html( $feat_cat->name ); ?></span>
								<?php endif; ?>
							</div>
							<h2 class="text-base md:text-lg lg:text-xl font-extrabold text-slate-900 leading-snug hover:text-brand-primary transition-colors line-clamp-2">
								<a href="<?php echo esc_url( get_permalink( $featured_post->ID ) ); ?>">
									<?php echo esc_html( $featured_post->post_title ); ?>
								</a>
							</h2>
							<p class="text-xs md:text-sm text-slate-500 line-clamp-2 leading-relaxed mt-2.5">
								<?php echo esc_html( wp_trim_words( $featured_post->post_excerpt ?: $featured_post->post_content, 22 ) ); ?>
							</p>
						</div>
						<div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs text-slate-400 mt-4">
							<span><?php echo get_the_date( 'd/m/Y', $featured_post->ID ); ?></span>
							<a href="<?php echo esc_url( get_permalink( $featured_post->ID ) ); ?>" class="text-brand-primary font-bold hover:underline">Chi tiết →</a>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- Articles Section -->
				<div class="space-y-6">
					<div class="flex items-center justify-between pb-3 border-b border-slate-200">
						<h2 class="font-extrabold text-slate-900 text-sm uppercase tracking-wider">
							<?php echo $active_cat ? esc_html( $active_cat->name ) : 'Bài viết mới nhất'; ?>
						</h2>
						<span class="text-sm text-slate-400 font-bold bg-slate-100 px-2.5 py-1 rounded-full"><?php echo esc_html( $blog_query->found_posts ); ?> Bài viết</span>
					</div>

					<!-- 2-Column Responsive Card Grid (Consistent with school/major page) -->
					<div class="grid grid-cols-2 gap-3 md:gap-6">
						<?php
						if ( $blog_query->have_posts() ) :
							while ( $blog_query->have_posts() ) : $blog_query->the_post();
								$post_id    = get_the_ID();
								$post_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $post_id, 'medium' ) : ( get_the_post_thumbnail_url( $post_id, 'medium' ) ?: ltdh_get_fallback_image( 'post' ) );
								$post_cats  = get_the_category( $post_id );
								$post_cat   = null;
								foreach ( $post_cats as $pc ) {
									if ( $pc->slug !== 'uncategorized' ) { $post_cat = $pc; break; }
								}
								// Get school relationship
								$school_id = get_field( 'school_relationship', $post_id );
								$school_name = $school_id ? get_the_title( $school_id ) : '';
						?>
							<article class="bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-md transition-all flex flex-col justify-between group">
								<a href="<?php the_permalink(); ?>" class="relative block aspect-[16/9] w-full bg-slate-100 overflow-hidden shrink-0">
									<img src="<?php echo esc_url( $post_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" class="w-full h-full object-cover object-left group-hover:scale-105 transition-transform duration-500" />
									<?php if ( $school_name ) : ?>
										<span class="absolute top-2 left-2 z-10 bg-white/90 backdrop-blur-sm text-[#00308b] text-[10px] md:text-xs font-extrabold px-2 py-0.5 rounded-full shadow-sm line-clamp-1 max-w-[85%]">
											<?php echo esc_html( $school_name ); ?>
										</span>
									<?php endif; ?>
								</a>

								<div class="p-3 md:p-5 flex-1 flex flex-col justify-between">
									<div>
										<div class="flex items-center justify-between text-xs font-bold text-slate-400 mb-1">
											<?php if ( $post_cat ) : ?>
												<span class="text-brand-primary uppercase"><?php echo esc_html( $post_cat->name ); ?></span>
											<?php endif; ?>
											<time class="font-normal"><?php echo get_the_date( 'd/m/Y' ); ?></time>
										</div>
										<h4 class="font-extrabold text-slate-800 text-sm leading-snug hover:text-brand-primary transition-colors line-clamp-2 min-h-[32px] md:min-h-[40px]">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h4>
										<div class="hidden md:block text-sm text-slate-500 line-clamp-2 leading-relaxed mt-2">
											<?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?>
										</div>
									</div>

									<div class="border-t border-slate-100 pt-3 mt-3 md:mt-4 flex items-center justify-between text-sm font-bold text-brand-primary">
										<a href="<?php the_permalink(); ?>" class="hover:underline">Chi tiết bài viết</a>
										<span>→</span>
									</div>
								</div>
							</article>
						<?php
							endwhile;
							wp_reset_postdata();
						else :
							echo '<div class="col-span-2 text-center text-slate-500 py-12">Chưa có bài viết nào thuộc danh mục này.</div>';
						endif;
						?>
					</div>

					<!-- Pagination -->
					<?php if ( $blog_query->max_num_pages > 1 ) : ?>
					<div class="pt-8 flex justify-center theme-pagination">
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
							'prev_text' => '← Trước',
							'next_text' => 'Sau →',
							'add_args'  => $query_args,
						] );
						?>
					</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- ====== RIGHT: SIDEBAR ====== -->
			<div class="lg:col-span-4">
				<?php get_sidebar(); ?>
			</div>

		</div>
	</div>
</div>

<?php
get_footer();
