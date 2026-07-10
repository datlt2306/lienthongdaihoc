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
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

		<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					$major_id = get_the_ID();
					$code = get_field( 'major_code', $major_id ) ?: 'Mã ngành đang cập nhật';
			?>
					<div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
						<div>
							<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-lg text-lg mb-4">📚</div>
							<h3 class="font-extrabold text-slate-800 text-lg hover:text-brand-primary mb-1">
								<a href="<?php the_permalink(); ?>">Ngành <?php the_title(); ?></a>
							</h3>
							<span class="text-sm text-slate-400 block mb-3 font-semibold uppercase">Mã ngành: <?php echo esc_html( $code ); ?></span>
							<p class="text-sm text-slate-500 line-clamp-3 mb-6"><?php the_excerpt(); ?></p>
						</div>

						<div class="border-t border-slate-100 pt-4 mt-auto">
							<a href="<?php the_permalink(); ?>" class="text-[#2563EB] font-bold text-sm hover:underline block text-right">Tìm hiểu cơ hội việc làm →</a>
						</div>
					</div>
			<?php
				endwhile;
			else :
				echo '<div class="col-span-3 text-center py-12"><p class="text-slate-500 text-base">Chưa có ngành học nào được cấu hình trên hệ thống.</p></div>';
			endif;
			?>
		</div>

	</div>
</main>

<?php
get_footer();
