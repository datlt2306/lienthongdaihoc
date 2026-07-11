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
	'posts_per_page' => 9,
	'post_status'    => 'publish',
	'paged'          => $paged,
], $cat_args ) );

// Recent posts for sidebar
$recent_posts = get_posts( [ 'numberposts' => 5 ] );

// Total post count per cat (for sidebar badges)
?>

<main id="primary" class="site-main bg-slate-50">
	<!-- Hero Banner -->
	<div class="bg-gradient-to-br from-[#1E3A8A] to-[#2563EB] py-14">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<nav class="text-sm text-blue-200 mb-4 flex items-center gap-2">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-white transition-colors">Trang chủ</a>
				<span>›</span>
				<span class="text-white">Tin tức tuyển sinh</span>
			</nav>
			<h1 class="text-3xl md:text-4xl font-black text-white mb-3 leading-tight">Tin tức & Thông báo tuyển sinh</h1>
			<p class="text-blue-200 text-base max-w-2xl">Cập nhật nhanh nhất thông tin xét tuyển, chính sách học phí và lịch khai giảng từ các trường đại học liên kết.</p>
		</div>
	</div>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
		<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

			<!-- ====== LEFT: Featured + Listing ====== -->
			<div class="lg:col-span-3 space-y-8">

				<!-- Featured Post -->
				<?php if ( $featured_post && empty( $active_cat_slug ) ) :
					$feat_thumb = get_the_post_thumbnail_url( $featured_post->ID, 'large' );
					$feat_cats  = get_the_category( $featured_post->ID );
					$feat_cat   = null;
					foreach ( $feat_cats as $fc ) {
						if ( $fc->slug !== 'uncategorized' ) { $feat_cat = $fc; break; }
					}
				?>
				<div class="rounded-2xl overflow-hidden shadow-lg group relative bg-slate-900" style="min-height:280px;">
					<?php if ( $feat_thumb ) : ?>
						<div class="absolute inset-0 bg-cover bg-center opacity-40 group-hover:opacity-50 transition-opacity" style="background-image: url('<?php echo esc_url( $feat_thumb ); ?>');"></div>
					<?php else : ?>
						<div class="absolute inset-0 bg-gradient-to-br from-[#1E3A8A] to-[#7C3AED] opacity-80"></div>
					<?php endif; ?>
					<div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent"></div>

					<div class="relative z-10 p-8 md:p-10 flex flex-col justify-end h-full" style="min-height:280px;">
						<?php if ( $feat_cat ) : ?>
							<span class="inline-block self-start bg-[#2563EB] text-white text-[11px] font-extrabold px-3 py-1.5 rounded-full uppercase tracking-wider mb-3">
								⭐ Tin nổi bật &nbsp;·&nbsp; <?php echo esc_html( $feat_cat->name ); ?>
							</span>
						<?php endif; ?>
						<h2 class="text-2xl md:text-3xl font-black text-white leading-tight mb-3">
							<a href="<?php echo esc_url( get_permalink( $featured_post->ID ) ); ?>" class="hover:underline">
								<?php echo esc_html( $featured_post->post_title ); ?>
							</a>
						</h2>
						<p class="text-blue-100 text-sm mb-5 line-clamp-2 leading-relaxed max-w-2xl">
							<?php echo esc_html( wp_trim_words( $featured_post->post_excerpt ?: $featured_post->post_content, 25 ) ); ?>
						</p>
						<div class="flex items-center gap-4">
							<a href="<?php echo esc_url( get_permalink( $featured_post->ID ) ); ?>"
							   class="bg-white text-[#2563EB] font-extrabold text-sm px-6 py-2.5 rounded-xl hover:bg-blue-50 transition-all shadow">
								Đọc ngay →
							</a>
							<span class="text-blue-300 text-sm"><?php echo get_the_date( 'd/m/Y', $featured_post->ID ); ?></span>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- Posts Listing -->
				<div>
					<div class="flex items-center justify-between mb-5">
						<h2 class="text-lg font-extrabold text-slate-900">
							<?php echo $active_cat ? esc_html( $active_cat->name ) : 'Bài viết mới nhất'; ?>
						</h2>
						<span class="text-sm text-slate-400"><?php echo esc_html( $blog_query->found_posts ); ?> bài viết</span>
					</div>

					<div class="grid grid-cols-2 md:grid-cols-1 gap-3 md:gap-0 md:divide-y md:divide-slate-100 bg-transparent md:bg-white rounded-lg md:border md:border-slate-200 md:shadow-sm overflow-hidden">
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
						?>
							<article class="flex flex-col md:flex-row items-stretch md:items-start gap-2.5 md:gap-5 p-3 md:px-6 md:py-4 bg-white md:bg-transparent border border-slate-100 md:border-none rounded-xl md:rounded-none shadow-sm md:shadow-none hover:bg-slate-50 transition-colors">
								<!-- Thumb -->
								<a href="<?php the_permalink(); ?>" class="shrink-0 w-full md:w-28 aspect-video md:aspect-square rounded-lg md:rounded-xl overflow-hidden bg-blue-50 block">
									<?php if ( $post_thumb ) : ?>
										<div class="w-full h-full bg-cover bg-center" style="background-image: url('<?php echo esc_url( $post_thumb ); ?>');"></div>
									<?php else : ?>
										<div class="w-full h-full flex items-center justify-center text-xl md:text-3xl">📰</div>
									<?php endif; ?>
								</a>

								<!-- Info -->
								<div class="flex-1 min-w-0 flex flex-col justify-between">
									<div>
										<?php if ( $post_cat ) : ?>
											<span class="text-[9px] md:text-[10px] font-extrabold text-[#2563EB] uppercase tracking-wider block mb-0.5"><?php echo esc_html( $post_cat->name ); ?></span>
										<?php endif; ?>
										<h3 class="font-extrabold text-slate-800 text-xs md:text-sm leading-snug hover:text-[#2563EB] transition-colors line-clamp-2">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h3>
										<div class="hidden md:block text-xs text-slate-500 line-clamp-2 leading-relaxed mt-1.5 mb-2">
											<?php
											the_excerpt();
											if (strpos(get_the_content(), 'more-link') === false && strpos(get_the_excerpt(), 'more-link') === false) {
												echo ' <a href="' . esc_url(get_permalink()) . '" class="text-[#2563EB] font-bold hover:underline">Đọc thêm →</a>';
											}
											?>
										</div>
									</div>
									<div class="flex items-center justify-between text-[9px] md:text-[11px] text-slate-400 mt-2 md:mt-0">
										<time><?php echo get_the_date( 'd/m/Y' ); ?></time>
										<span class="text-[#2563EB] font-bold md:inline-block">Chi tiết →</span>
									</div>
								</div>
							</article>
						<?php
							endwhile;
							wp_reset_postdata();
						else :
							echo '<div class="p-12 text-center text-slate-400 col-span-2">Chưa có bài viết nào trong mục này.</div>';
						endif;
						?>
					</div>

					<!-- Pagination -->
					<?php if ( $blog_query->max_num_pages > 1 ) : ?>
					<div class="mt-8 flex justify-center theme-pagination">
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

			<!-- ====== SIDEBAR ====== -->
			<aside class="lg:col-span-1 space-y-6">

				<!-- Danh mục -->
				<div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
					<h3 class="font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100">📂 Danh mục</h3>
					<ul class="space-y-0.5">
						<?php foreach ( $blog_cats as $bcat ) :
							$is_active_cat = ( $active_cat_slug === $bcat->slug );
						?>
							<li>
								<a href="<?php echo esc_url( home_url( '/tin-tuc/?danh-muc=' . $bcat->slug ) ); ?>"
								   class="flex items-center px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors <?php echo $is_active_cat ? 'text-[#2563EB]' : 'text-slate-600 hover:text-[#2563EB]'; ?>">
									<span class="w-1.5 h-1.5 rounded-full <?php echo $is_active_cat ? 'bg-[#2563EB]' : 'bg-slate-300'; ?> mr-2.5 shrink-0"></span>
									<?php echo esc_html( $bcat->name ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<!-- Bài viết mới nhất -->
				<div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
					<h3 class="font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100">🔥 Bài viết nổi bật</h3>
					<ul class="space-y-4">
						<?php foreach ( $recent_posts as $rp ) :
							$rp_thumb = get_the_post_thumbnail_url( $rp->ID, 'thumbnail' );
						?>
							<li class="flex items-start gap-3">
								<?php if ( $rp_thumb ) : ?>
									<img src="<?php echo esc_url( $rp_thumb ); ?>" alt="" class="w-14 h-14 rounded-lg object-cover shrink-0 border border-slate-100">
								<?php else : ?>
									<div class="w-14 h-14 rounded-lg bg-blue-50 flex items-center justify-center shrink-0 text-xl">📰</div>
								<?php endif; ?>
								<div class="min-w-0">
									<a href="<?php echo esc_url( get_permalink( $rp->ID ) ); ?>" class="text-xs font-bold text-slate-800 hover:text-[#2563EB] leading-snug block line-clamp-2 transition-colors">
										<?php echo esc_html( $rp->post_title ); ?>
									</a>
									<time class="text-[10px] text-slate-400 mt-1 block"><?php echo get_the_date( 'd/m/Y', $rp->ID ); ?></time>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<!-- CTA Box -->
				<div class="bg-gradient-to-br from-[#1E3A8A] to-[#2563EB] rounded-xl p-6 text-center text-white shadow-lg">
					<div class="text-3xl mb-3">🎓</div>
					<h3 class="font-extrabold text-base mb-2 leading-tight">Cần tư vấn tuyển sinh?</h3>
					<p class="text-blue-100 text-xs mb-4 leading-relaxed">Đội ngũ tư vấn viên sẵn sàng hỗ trợ bạn 24/7</p>
					<a href="<?php echo esc_url( home_url( '/dang-ky-tu-van/' ) ); ?>"
					   class="block w-full bg-white text-[#2563EB] font-extrabold text-sm py-2.5 rounded-xl hover:bg-blue-50 transition-all">
						Đăng ký ngay
					</a>
					<a href="tel:0389198653" class="block mt-2 text-blue-200 text-xs font-semibold hover:text-white transition-colors">
						📞 0389 198 653
					</a>
				</div>

			</aside>
		</div>
	</div>
</main>
	
<?php
get_footer();

