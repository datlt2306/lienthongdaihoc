<?php
/**
 * Generic Index Fallback Template (Standard Blog Listing)
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main py-12 bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		
		<!-- Breadcrumbs -->
		<nav class="text-sm text-slate-500 mb-6">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-brand-primary">Trang chủ</a> / <span>Tin tức tuyển sinh</span>
		</nav>

		<h1 class="text-3xl font-black text-slate-900 mb-4">TIN TỨC - HƯỚNG DẪN TUYỂN SINH</h1>
		<p class="text-slate-500 text-sm mb-8 max-w-xl">Cập nhật nhanh chóng thông tin quy định xét tuyển, chính sách học phí, thời gian nộp hồ sơ từ Bộ Giáo dục và các trường liên kết.</p>

		<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
			?>
					<article class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
						<div>
							<span class="text-sm text-slate-400 block mb-2"><?php echo get_the_date( 'd/m/Y' ); ?></span>
							<h3 class="font-extrabold text-slate-800 text-lg hover:text-brand-primary mb-3">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							<p class="text-sm text-slate-500 line-clamp-3 mb-6"><?php the_excerpt(); ?></p>
						</div>

						<div class="border-t border-slate-100 pt-4 mt-auto">
							<a href="<?php the_permalink(); ?>" class="text-[#2563EB] font-bold text-sm hover:underline block text-right">Đọc bài viết →</a>
						</div>
					</article>
			<?php
				endwhile;
			else :
				echo '<div class="col-span-3 text-center py-12"><p class="text-slate-500 text-base">Hiện chưa có tin tức nào được đăng tải.</p></div>';
			endif;
			?>
		</div>

	</div>
</main>

<?php
get_footer();
