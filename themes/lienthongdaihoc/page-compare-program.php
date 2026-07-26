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
				<a href="<?php echo esc_url( home_url( '/he-dao-tao/tu-xa/' ) ); ?>" class="inline-flex items-center gap-2 bg-brand-primary text-white font-bold px-6 py-3 rounded-lg hover:bg-brand-darkBlue transition-all">
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

			<!-- FAQ Section -->
			<?php
			$faqs = get_field( 'faq_compare_items', 'option' );
			if ( empty( $faqs ) ) {
				// Fallback to defaults
				$faqs = [
					[
						'question' => 'Làm sao để chọn chương trình phù hợp?',
						'answer'   => 'Hãy xem xét học phí, thời gian đào tạo, lịch học và cơ hội việc làm. Nếu bạn đang đi làm, ưu tiên chương trình có lịch học linh hoạt.'
					],
					[
						'question' => 'Bằng cấp từ các chương trình này có giá trị không?',
						'answer'   => 'Các chương trình liên thông và văn bằng 2 đều được Bộ Giáo dục & Đào tạo công nhận. Bằng cấp có giá trị toàn quốc.'
					],
					[
						'question' => 'Tôi có thể vừa học vừa làm được không?',
						'answer'   => 'Có. Hầu hết các chương trình liên thông và từ xa đều thiết kế cho người đi làm với lịch học cuối tuần hoặc học trực tuyến linh hoạt.'
					]
				];
			}
			?>
			<section class="mt-8 bg-white rounded-lg shadow-sm border border-slate-100 p-6">
				<h2 class="text-xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">
					Câu hỏi thường gặp khi so sánh chương trình
				</h2>
				<div class="space-y-4">
					<?php foreach ( $faqs as $faq ) : ?>
						<div class="border-b border-slate-100 pb-4">
							<h4 class="font-semibold text-slate-800 text-sm mb-1.5 flex items-start gap-2">
								<span class="bg-teal-100 text-teal-800 text-sm px-1.5 py-0.5 rounded-lg font-black">Q</span>
								<span><?php echo esc_html( $faq['question'] ); ?></span>
							</h4>
							<p class="text-slate-600 text-sm pl-7 leading-relaxed">
								<?php echo esc_html( $faq['answer'] ); ?>
							</p>
						</div>
					<?php endforeach; ?>
				</div>
			</section>

			<!-- Related Comparisons -->
			<section class="mt-8 text-center">
				<h3 class="text-lg font-bold text-slate-800 mb-4">So sánh tương tự</h3>
				<div class="flex flex-wrap justify-center gap-2">
					<a href="<?php echo esc_url( home_url( '/he-dao-tao/tu-xa/' ) ); ?>" class="inline-flex items-center gap-1.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold px-4 py-2 rounded-lg hover:border-brand-primary hover:text-brand-primary transition-all">
						Xem tất cả chương trình →
					</a>
				</div>
			</section>

		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
