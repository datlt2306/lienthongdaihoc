<?php
/**
 * Search Results Template (BlankSlate Architecture)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
	<?php if ( have_posts() ) : ?>
		<header class="header mb-8 pb-4 border-b border-slate-200">
			<h1 class="entry-title text-xl sm:text-2xl font-extrabold text-slate-900" itemprop="name">
				<?php printf( esc_html__( 'Kết quả tìm kiếm cho: %s', 'ltdh' ), '<span class="text-[#00308b]">' . esc_html( get_search_query() ) . '</span>' ); ?>
			</h1>
		</header>

		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'entry' ); ?>
			<?php endwhile; ?>
		</div>

		<div class="mt-10">
			<?php get_template_part( 'nav', 'below' ); ?>
		</div>

	<?php else : ?>
		<article id="post-0" class="post no-results not-found max-w-2xl mx-auto text-center py-12">
			<header class="header mb-4">
				<h1 class="entry-title text-2xl font-bold text-slate-800" itemprop="name">
					<?php esc_html_e( 'Không tìm thấy kết quả phù hợp', 'ltdh' ); ?>
				</h1>
			</header>
			<div class="entry-content text-slate-500 mb-8" itemprop="mainContentOfPage">
				<p><?php esc_html_e( 'Rất tiếc, không tìm thấy thông tin nào phù hợp với từ khóa của bạn. Vui lòng thử tìm kiếm bằng từ khóa khác hoặc tra cứu theo danh mục bên dưới.', 'ltdh' ); ?></p>
			</div>
			<div class="max-w-md mx-auto">
				<?php get_search_form(); ?>
			</div>
		</article>
	<?php endif; ?>
</div>

<?php
get_footer();
