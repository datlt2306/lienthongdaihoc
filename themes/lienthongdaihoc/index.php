<?php
/**
 * Blog Archive Template - Tin tức tuyển sinh
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Active category filter
$active_cat_slug = isset( $_GET['danh-muc'] ) ? sanitize_text_field( $_GET['danh-muc'] ) : '';
$active_cat      = $active_cat_slug ? get_term_by( 'slug', $active_cat_slug, 'category' ) : null;

// Blog categories for sidebar/tabs
$blog_cats = get_categories( [ 'hide_empty' => true, 'exclude' => [ 1 ] ] );

// Featured post (most recent, optionally sticky)
$featured_post = null;
$sticky_ids    = get_option( 'sticky_posts' );
if ( ! empty( $sticky_ids ) ) {
	$featured_posts = get_posts( [ 'post__in' => $sticky_ids, 'numberposts' => 1 ] );
	$featured_post  = $featured_posts[0] ?? null;
}
if ( ! $featured_post ) {
	$featured_posts = get_posts( [
		'numberposts'    => 1,
		'category__not_in' => [ 1 ],
	] );
	$featured_post = $featured_posts[0] ?? null;
}

// Main query args
$paged = max( 1, get_query_var( 'paged' ) );
$cat_args = [];
if ( $active_cat ) {
	$cat_args['cat'] = $active_cat->term_id;
}
if ( $featured_post ) {
	$cat_args['post__not_in'] = [ $featured_post->ID ];
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

<main id="primary" class="site-main bg-slate-50/50 min-h-screen">
	<!-- Hero Header Section (Premium Minimalist Design) -->
	<section class="relative bg-gradient-to-tr from-[#0E2038] to-brand-primary text-white py-14 md:py-20 overflow-hidden">
		<!-- Dot Grid Pattern -->
		<div class="absolute inset-0 opacity-10 pointer-events-none z-0" style="background-image: radial-gradient(white 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
		<div class="absolute -right-32 -bottom-32 w-96 h-96 bg-brand-accent/20 rounded-full blur-3xl"></div>
		
		<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
			<nav class="text-xs text-blue-200 mb-3 flex items-center gap-2">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-white transition-colors">Trang chủ</a>
				<span>›</span>
				<span class="text-white">Tin tức tuyển sinh</span>
			</nav>
			<h1 class="text-2xl sm:text-3xl md:text-4xl font-black font-display tracking-tight leading-tight uppercase">CẨM NANG TUYỂN SINH & TIN TỨC</h1>
			<p class="text-blue-100 text-sm md:text-base font-semibold max-w-2xl mt-2">Cập nhật nhanh nhất và chính xác các quy chế thi, đề án tuyển sinh, học bổng từ các trường đại học liên kết.</p>
		</div>
	</section>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
		
		<!-- Category Pills Navigation (Horizontal scroll on mobile) -->
		<div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-4 mb-8 border-b border-slate-200">
			<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" 
			   class="px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all <?php echo empty( $active_cat_slug ) ? 'bg-brand-primary text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?> min-h-[38px] flex items-center justify-center">
				Tất cả tin tức
			</a>
			<?php foreach ( $blog_cats as $bcat ) :
				$is_active_cat = ( $active_cat_slug === $bcat->slug );
			?>
				<a href="<?php echo esc_url( home_url( '/tin-tuc/?danh-muc=' . $bcat->slug ) ); ?>" 
				   class="px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all <?php echo $is_active_cat ? 'bg-brand-primary text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?> min-h-[38px] flex items-center justify-center">
					<?php echo esc_html( $bcat->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

			<!-- ====== LEFT: CONTENT ====== -->
			<div class="lg:col-span-8 space-y-10">

				<!-- Featured Post (Split card design) -->
				<?php if ( $featured_post && empty( $active_cat_slug ) && $paged === 1 ) :
					$feat_thumb = get_the_post_thumbnail_url( $featured_post->ID, 'large' );
					$feat_cats  = get_the_category( $featured_post->ID );
					$feat_cat   = null;
					foreach ( $feat_cats as $fc ) {
						if ( $fc->slug !== 'uncategorized' ) { $feat_cat = $fc; break; }
					}
					$feat_school_id = get_field( 'school_relationship', $featured_post->ID );
					$feat_school_name = $feat_school_id ? get_the_title( $feat_school_id ) : '';
				?>
				<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs hover:shadow-md transition-all group grid grid-cols-1 md:grid-cols-12">
					<!-- Thumbnail Left -->
					<div class="md:col-span-7 relative min-h-[220px] md:min-h-[340px] bg-slate-100 overflow-hidden">
						<?php if ( $feat_thumb ) : ?>
							<div class="absolute inset-0 bg-cover bg-center group-hover:scale-105 transition-transform duration-500" style="background-image: url('<?php echo esc_url( $feat_thumb ); ?>');"></div>
						<?php else : ?>
							<div class="absolute inset-0 bg-gradient-to-tr from-[#0E2038] to-brand-primary flex items-center justify-center text-4xl">📰</div>
						<?php endif; ?>
						<?php if ( $feat_school_name ) : ?>
							<span class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-[#00308b] text-[10px] font-extrabold px-3 py-1 rounded-full shadow-sm">
								<?php echo esc_html( $feat_school_name ); ?>
							</span>
						<?php endif; ?>
					</div>
					
					<!-- Content Right -->
					<div class="md:col-span-5 p-6 md:p-8 flex flex-col justify-between space-y-4">
						<div class="space-y-3">
							<div class="flex items-center gap-2">
								<span class="bg-brand-primary text-white text-[9px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
									NỔI BẬT
								</span>
								<?php if ( $feat_cat ) : ?>
									<span class="text-[10px] text-slate-400 font-bold uppercase"><?php echo esc_html( $feat_cat->name ); ?></span>
								<?php endif; ?>
							</div>
							<h2 class="text-base md:text-xl font-extrabold text-slate-900 leading-snug hover:text-brand-primary transition-colors">
								<a href="<?php echo esc_url( get_permalink( $featured_post->ID ) ); ?>">
									<?php echo esc_html( $featured_post->post_title ); ?>
								</a>
							</h2>
							<p class="text-xs md:text-sm text-slate-500 line-clamp-3 leading-relaxed">
								<?php echo esc_html( wp_trim_words( $featured_post->post_excerpt ?: $featured_post->post_content, 25 ) ); ?>
							</p>
						</div>
						<div class="flex items-center justify-between pt-4 border-t border-slate-100 text-[11px] text-slate-400">
							<span><?php echo get_the_date( 'd/m/Y', $featured_post->ID ); ?></span>
							<a href="<?php echo esc_url( get_permalink( $featured_post->ID ) ); ?>" class="text-brand-primary font-bold hover:underline">Chi tiết →</a>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- Articles Section -->
				<div class="space-y-6">
					<div class="flex items-center justify-between pb-3 border-b border-slate-200">
						<h3 class="font-extrabold text-slate-900 text-sm uppercase tracking-wider">
							<?php echo $active_cat ? esc_html( $active_cat->name ) : 'Bài viết mới nhất'; ?>
						</h3>
						<span class="text-xs text-slate-400 font-bold bg-slate-100 px-2.5 py-1 rounded-full"><?php echo esc_html( $blog_query->found_posts ); ?> Bài viết</span>
					</div>

					<!-- 2-Column Responsive Card Grid (Consistent with school/major page) -->
					<div class="grid grid-cols-2 gap-3 md:gap-6">
						<?php
						if ( $blog_query->have_posts() ) :
							while ( $blog_query->have_posts() ) : $blog_query->the_post();
								$post_id    = get_the_ID();
								$post_thumb = get_the_post_thumbnail_url( $post_id, 'medium' );
								$post_cats  = get_the_category( $post_id );
								$post_cat   = null;
								foreach ( $post_cats as $pc ) {
									if ( $pc->slug !== 'uncategorized' ) { $post_cat = $pc; break; }
								}
								// Get school relationship
								$school_id = get_field( 'school_relationship', $post_id );
								$school_name = $school_id ? get_the_title( $school_id ) : '';
						?>
							<article class="bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-md transition-all flex flex-col justify-between">
								<a href="<?php the_permalink(); ?>" class="relative block h-28 md:h-40 bg-slate-100 overflow-hidden shrink-0">
									<?php if ( $post_thumb ) : ?>
										<div class="w-full h-full bg-cover bg-center hover:scale-105 transition-transform duration-500" style="background-image: url('<?php echo esc_url( $post_thumb ); ?>');"></div>
									<?php else : ?>
										<div class="w-full h-full flex items-center justify-center text-3xl">📰</div>
									<?php endif; ?>
									<?php if ( $school_name ) : ?>
										<span class="absolute top-2 left-2 bg-white/90 backdrop-blur-sm text-[#00308b] text-[8px] md:text-[9px] font-extrabold px-2 py-0.5 rounded-full shadow-sm line-clamp-1">
											<?php echo esc_html( $school_name ); ?>
										</span>
									<?php endif; ?>
								</a>

								<div class="p-3 md:p-5 flex-1 flex flex-col justify-between">
									<div>
										<div class="flex items-center justify-between text-[9px] md:text-[10px] font-bold text-slate-400 mb-1">
											<?php if ( $post_cat ) : ?>
												<span class="text-brand-primary uppercase"><?php echo esc_html( $post_cat->name ); ?></span>
											<?php endif; ?>
											<time class="font-normal"><?php echo get_the_date( 'd/m/Y' ); ?></time>
										</div>
										<h4 class="font-extrabold text-slate-800 text-xs md:text-sm leading-snug hover:text-brand-primary transition-colors line-clamp-2 min-h-[32px] md:min-h-[40px]">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h4>
										<div class="hidden md:block text-xs text-slate-500 line-clamp-2 leading-relaxed mt-2">
											<?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?>
										</div>
									</div>

									<div class="border-t border-slate-100 pt-3 mt-3 md:mt-4 flex items-center justify-between text-[11px] font-bold text-brand-primary">
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
						<?php echo paginate_links( [
							'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
							'format'    => '?paged=%#%',
							'current'   => $paged,
							'total'     => $blog_query->max_num_pages,
							'prev_text' => '← Trước',
							'next_text' => 'Sau →',
						] ); ?>
					</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- ====== RIGHT: SIDEBAR ====== -->
			<aside class="lg:col-span-4 space-y-6">

				<!-- Sidebar Category Card -->
				<div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
					<h3 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider mb-4 pb-3 border-b border-slate-100">📂 Chuyên mục cẩm nang</h3>
					<ul class="space-y-1">
						<li>
							<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" 
							   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold transition-all <?php echo empty( $active_cat_slug ) ? 'bg-slate-50 text-brand-primary' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[40px]">
								<span>Tất cả tin tức</span>
								<span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500"><?php echo esc_html( wp_count_posts( 'post' )->publish ); ?></span>
							</a>
						</li>
						<?php foreach ( $blog_cats as $bcat ) :
							$is_active_cat = ( $active_cat_slug === $bcat->slug );
						?>
							<li>
								<a href="<?php echo esc_url( home_url( '/tin-tuc/?danh-muc=' . $bcat->slug ) ); ?>" 
								   class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold transition-all <?php echo $is_active_cat ? 'bg-slate-50 text-brand-primary' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[40px]">
									<span><?php echo esc_html( $bcat->name ); ?></span>
									<span class="text-[10px] px-2 py-0.5 rounded-full <?php echo $is_active_cat ? 'bg-blue-50 text-brand-primary' : 'bg-slate-100 text-slate-500'; ?>"><?php echo esc_html( $bcat->count ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<!-- Featured Posts / Recent Widget -->
				<div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
					<h3 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider mb-4 pb-3 border-b border-slate-100">🔥 Tin xem nhiều nhất</h3>
					<ul class="space-y-4">
						<?php foreach ( $recent_posts as $rp ) :
							$rp_thumb = get_the_post_thumbnail_url( $rp->ID, 'thumbnail' );
						?>
							<li class="flex items-start gap-3">
								<a href="<?php echo esc_url( get_permalink( $rp->ID ) ); ?>" class="w-12 h-12 rounded-lg bg-slate-100 overflow-hidden shrink-0 border border-slate-100 block">
									<?php if ( $rp_thumb ) : ?>
										<img src="<?php echo esc_url( $rp_thumb ); ?>" alt="" class="w-full h-full object-cover" loading="lazy">
									<?php else : ?>
										<div class="w-full h-full flex items-center justify-center text-base">📰</div>
									<?php endif; ?>
								</a>
								<div class="min-w-0">
									<a href="<?php echo esc_url( get_permalink( $rp->ID ) ); ?>" class="text-[11px] font-bold text-slate-800 hover:text-brand-primary leading-snug block line-clamp-2 transition-all">
										<?php echo esc_html( $rp->post_title ); ?>
									</a>
									<time class="text-[9px] text-slate-400 mt-0.5 block"><?php echo get_the_date( 'd/m/Y', $rp->ID ); ?></time>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<!-- Premium CTA Consultation Sidebar Card -->
				<div class="relative bg-gradient-to-tr from-[#0E2038] to-brand-primary rounded-xl p-6 text-center text-white shadow-lg overflow-hidden">
					<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 16px 16px;"></div>
					<div class="text-3xl mb-2">💡</div>
					<h3 class="font-extrabold text-base mb-1.5 leading-tight">Bạn chưa chọn được lộ trình học?</h3>
					<p class="text-blue-100 text-xs mb-4 leading-relaxed">Để lại thông tin, ban tư vấn sẽ giải đáp lộ trình liên thông hoàn toàn miễn phí cho bạn.</p>
					<a href="<?php echo esc_url( home_url( '/kiem-tra-dieu-kien/' ) ); ?>" 
					   class="block w-full bg-brand-accent text-white font-extrabold text-xs py-3 rounded-lg hover:bg-[#e06e00] transition-all shadow-md shadow-brand-accent/20 min-h-[44px] flex items-center justify-center">
						KIỂM TRA ĐIỀU KIỆN NGAY
					</a>
				</div>

			</aside>
		</div>
	</div>
</main>

<?php
get_footer();

