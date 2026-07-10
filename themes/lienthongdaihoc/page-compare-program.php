<?php
/**
 * Program Comparison Page Template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$type = ltdh_compare_get_type();
$items = ltdh_compare_get_items( $type );
$highlights = ltdh_compare_get_highlights( $type, $items );
$ids = ltdh_compare_get_ids();
$hotline = ltdh_compare_get_global_hotline();
$zalo = ltdh_compare_get_zalo_url();

// Build SEO title
$titles = array_map( function( $item ) { return $item['title']; }, $items );
$seo_title = 'So sánh ' . implode( ' vs ', $titles );
$seo_desc = 'So sánh chi tiết ' . implode( ', ', $titles ) . ' — học phí, thời gian, điều kiện tuyển sinh.';
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

		<!-- SEO H1 -->
		<h1 class="text-2xl md:text-3xl font-black text-slate-900 mb-2">
			<?php echo esc_html( $seo_title ); ?>
		</h1>
		<p class="text-slate-500 text-sm mb-8"><?php echo esc_html( $seo_desc ); ?></p>

		<?php if ( count( $items ) < 2 ) : ?>
			<div class="bg-white rounded-lg p-12 text-center shadow-sm border border-slate-100">
				<p class="text-slate-500 text-lg mb-4">Vui lòng chọn ít nhất 2 chương trình để so sánh.</p>
				<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" class="inline-flex items-center gap-2 bg-brand-primary text-white font-bold px-6 py-3 rounded-lg hover:bg-teal-700 transition-all">
					Xem danh sách chương trình →
				</a>
			</div>
		<?php else : ?>

			<!-- Desktop Table -->
			<div class="hidden lg:block overflow-x-auto">
				<?php include get_template_directory() . '/template-parts/compare/program-table.php'; ?>
			</div>

			<!-- Mobile Cards -->
			<div class="lg:hidden space-y-6">
				<?php include get_template_directory() . '/template-parts/compare/program-cards.php'; ?>
			</div>

			<!-- CTA Section -->
			<div class="mt-12">
				<?php get_template_part( 'template-parts/compare/cta-bar' ); ?>
			</div>

			<!-- SEO Content Section -->
			<section class="mt-12 bg-white rounded-lg shadow-sm border border-slate-100 p-6">
				<h2 class="text-xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">
					Thông tin so sánh chi tiết
				</h2>
				<div class="prose prose-slate max-w-none text-slate-600 text-sm">
					<?php
					foreach ( $items as $item ) :
						$school_name = $item['school'] ? $item['school']['title'] : '';
					?>
						<h3><?php echo esc_html( $item['title'] ); ?> <?php echo esc_html( $school_name ? '(' . $school_name . ')' : '' ); ?></h3>
						<p><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
						<?php if ( ! empty( $item['advantages'] ) ) : ?>
							<p><strong>Ưu điểm:</strong> <?php echo wp_kses_post( $item['advantages'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $item['disadvantages'] ) ) : ?>
							<p><strong>Nhược điểm:</strong> <?php echo wp_kses_post( $item['disadvantages'] ); ?></p>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</section>

			<!-- FAQ Section -->
			<section class="mt-8 bg-white rounded-lg shadow-sm border border-slate-100 p-6">
				<h2 class="text-xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">
					Câu hỏi thường gặp khi so sánh chương trình
				</h2>
				<div class="space-y-4">
					<div class="border-b border-slate-100 pb-4">
						<h4 class="font-semibold text-slate-800 text-sm mb-1.5 flex items-start gap-2">
							<span class="bg-teal-100 text-teal-800 text-sm px-1.5 py-0.5 rounded-lg font-black">Q</span>
							<span>Làm sao để chọn chương trình phù hợp?</span>
						</h4>
						<p class="text-slate-600 text-sm pl-7 leading-relaxed">
							Hãy xem xét học phí, thời gian đào tạo, lịch học và cơ hội việc làm. Nếu bạn đang đi làm, ưu tiên chương trình có lịch học linh hoạt.
						</p>
					</div>
					<div class="border-b border-slate-100 pb-4">
						<h4 class="font-semibold text-slate-800 text-sm mb-1.5 flex items-start gap-2">
							<span class="bg-teal-100 text-teal-800 text-sm px-1.5 py-0.5 rounded-lg font-black">Q</span>
							<span>Bằng cấp từ các chương trình này có giá trị không?</span>
						</h4>
						<p class="text-slate-600 text-sm pl-7 leading-relaxed">
							Các chương trình liên thông và văn bằng 2 đều được Bộ Giáo dục & Đào tạo công nhận. Bằng cấp có giá trị toàn quốc.
						</p>
					</div>
					<div class="border-b border-slate-100 pb-4">
						<h4 class="font-semibold text-slate-800 text-sm mb-1.5 flex items-start gap-2">
							<span class="bg-teal-100 text-teal-800 text-sm px-1.5 py-0.5 rounded-lg font-black">Q</span>
							<span>Tôi có thể vừa học vừa làm được không?</span>
						</h4>
						<p class="text-slate-600 text-sm pl-7 leading-relaxed">
							Có. Hầu hết các chương trình liên thông và từ xa đều thiết kế cho người đi làm với lịch học cuối tuần hoặc học trực tuyến linh hoạt.
						</p>
					</div>
				</div>
			</section>

			<!-- Related Comparisons -->
			<section class="mt-8 text-center">
				<h3 class="text-lg font-bold text-slate-800 mb-4">So sánh tương tự</h3>
				<div class="flex flex-wrap justify-center gap-2">
					<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" class="inline-flex items-center gap-1.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold px-4 py-2 rounded-lg hover:border-brand-primary hover:text-brand-primary transition-all">
						Xem tất cả chương trình →
					</a>
				</div>
			</section>

		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
