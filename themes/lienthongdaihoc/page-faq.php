<?php
/**
 * Template Name: Câu hỏi thường gặp
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main py-12 bg-slate-50">
	<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
		
		<h1 class="text-3xl font-black text-slate-900 text-center mb-4">CÂU HỎI THƯỜNG GẶP</h1>
		<p class="text-slate-500 text-sm text-center mb-8 max-w-lg mx-auto">Giải đáp tất cả thắc mắc thường gặp về quy trình tuyển sinh, học phí, hình thức học tập trực tuyến (Online) và bằng cấp.</p>

		<div class="space-y-4">
			<?php
			$faq_items = [];
			if ( function_exists( 'get_field' ) ) {
				$faq_items = get_field( 'faq_items', 'options' ) ?: [];
			}

			if ( ! empty( $faq_items ) && is_array( $faq_items ) ) :
				foreach ( $faq_items as $faq ) :
					$q_text = $faq['question'] ?? '';
					$a_text = $faq['answer'] ?? '';
					if ( empty( $q_text ) || empty( $a_text ) ) continue;
				?>
					<details class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden group">
						<summary class="flex justify-between items-center font-bold text-slate-800 p-5 cursor-pointer list-none hover:bg-slate-50 select-none text-base [&::-webkit-details-marker]:hidden">
							<span><?php echo esc_html( $q_text ); ?></span>
							<span class="text-slate-400 group-open:rotate-180 transition-transform">▼</span>
						</summary>
						<div class="p-5 border-t border-slate-100 text-slate-600 text-sm leading-relaxed bg-slate-50/50">
							<?php echo esc_html( $a_text ); ?>
						</div>
					</details>
				<?php endforeach;
			else : ?>
				<div class="text-center py-12 bg-white rounded-2xl border border-slate-100 p-8">
					<p class="text-slate-500 text-base">Hiện chưa có câu hỏi nào được cập nhật.</p>
				</div>
			<?php endif; ?>
		</div>

	</div>
</main>

<?php
get_footer();
