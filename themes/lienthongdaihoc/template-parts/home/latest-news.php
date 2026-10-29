<?php
/**
 * Home: Latest News Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$news_query = $args['news_query'] ?? ( isset( $news_query ) ? $news_query : ( function_exists( 'ltdh_get_cached_query' ) ? ltdh_get_cached_query( 'ltdh_homepage_news', [
	'post_type'      => 'post',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
	'no_found_rows'  => true,
], HOUR_IN_SECONDS ) : null ) );
?>

<!-- 10. NEWS RECENT SECTION (Apple Cupertino Editorial Grid) -->
<section class="py-10 sm:py-16 md:py-20 bg-[#F5F5F7] border-t border-black/[0.06]">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="flex justify-between items-end mb-6 sm:mb-8 lg:mb-10">
			<div class="space-y-2">
				<span class="inline-block bg-[#0071E3]/10 text-[#0071E3] text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">THÔNG TIN TUYỂN SINH</span>
				<h2 class="text-2xl md:text-3xl font-semibold text-[#1D1D1F] tracking-tight">Tin tức mới nhất</h2>
			</div>
			<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" class="apple-link text-sm text-[#0071E3] font-normal hover:underline inline-flex items-center gap-1 group">
				<span>Xem tất cả</span>
				<span class="transition-transform group-hover:translate-x-0.5">›</span>
			</a>
		</div>

		<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-6">
			<?php
			$mock_news = [
				[ 'title' => 'Tuyển sinh Đại học Từ xa khóa mới nhất', 'date' => '10/07/2026', 'desc' => 'Thông tin chi tiết các ngành đào tạo từ xa hệ Đại học được bộ GD&ĐT công nhận tốt nghiệp chính quy.' ],
				[ 'title' => 'Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026', 'date' => '08/07/2026', 'desc' => 'Quy trình và hồ sơ xét tuyển liên thông đại học từ trung cấp, cao đẳng lên đại học theo hình thức từ xa và vừa học vừa làm.' ],
				[ 'title' => 'Quy chế tuyển sinh Liên thông Cao đẳng lên Đại học', 'date' => '05/07/2026', 'desc' => 'Quy định rút ngắn chương trình đào tạo khi thi liên thông và các hồ sơ chuẩn bị nhập học.' ],
				[ 'title' => 'Học đại học vừa học vừa làm có giá trị như thế nào?', 'date' => '02/07/2026', 'desc' => 'Giá trị pháp lý của tấm bằng đại học vừa học vừa làm đối với cơ hội thăng tiến nghề nghiệp.' ],
			];

			$news_has_posts = false;
			if ( $news_query && method_exists( $news_query, 'have_posts' ) && $news_query->have_posts() ) {
				$news_has_posts = true;
				$index          = 0;
				while ( $news_query->have_posts() ) : $news_query->the_post();
					$post_id       = get_the_ID();
					$raw_thumb     = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $post_id, 'medium' ) : ( get_the_post_thumbnail_url( $post_id, 'medium' ) ?: ( function_exists( 'ltdh_get_fallback_image' ) ? ltdh_get_fallback_image( 'news' ) : '' ) );
					$thumb_url     = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $raw_thumb ) : $raw_thumb;
					$categories    = function_exists( 'get_the_category' ) ? get_the_category( $post_id ) : [];
					$category_name = ! empty( $categories ) ? $categories[0]->name : 'Tin tuyển sinh';
			?>
					<div class="bg-white border border-slate-100 rounded-lg overflow-hidden hover:shadow-md transition-all flex flex-col justify-between group">
						<a href="<?php the_permalink(); ?>" class="aspect-[16/9] w-full bg-slate-100 overflow-hidden block shrink-0">
							<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" class="w-full h-full object-cover object-left group-hover:scale-105 transition-transform duration-500" />
						</a>
						<div class="p-4 flex-1 flex flex-col justify-between">
							<div>
								<span class="text-xs font-bold text-brand-primary uppercase tracking-wider block mb-1"><?php echo esc_html( $category_name ); ?></span>
								<h3 class="font-extrabold text-slate-800 text-sm line-clamp-2 hover:text-[#00308b] transition-colors leading-snug">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
							</div>
							<time class="text-xs text-slate-400 mt-3 block"><?php echo get_the_date( 'd/m/Y' ); ?></time>
						</div>
					</div>
				<?php
					$index++;
				endwhile;
				wp_reset_postdata();
			}

			if ( ! $news_has_posts ) {
				foreach ( $mock_news as $mn ) {
				?>
					<div class="bg-white border border-slate-100 rounded-lg p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
						<div>
							<span class="text-xs font-bold text-brand-primary uppercase tracking-wider block mb-1">Hướng dẫn</span>
							<h3 class="font-bold text-slate-800 text-sm line-clamp-2 leading-snug"><?php echo esc_html( $mn['title'] ); ?></h3>
							<p class="text-sm text-slate-400 leading-normal line-clamp-3 mt-2"><?php echo esc_html( $mn['desc'] ); ?></p>
						</div>
						<time class="text-xs text-slate-400 mt-3 block"><?php echo esc_html( $mn['date'] ); ?></time>
					</div>
			<?php
				}
			}
			?>
		</div>
	</div>
</section>
