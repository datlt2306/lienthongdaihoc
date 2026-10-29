<?php
/**
 * Home: FAQ Accordion Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$homepage_faqs = [];
if ( function_exists( 'get_field' ) ) {
	$homepage_faqs = get_field( 'faq_items', 'options' ) ?: [];
}

if ( ! empty( $homepage_faqs ) && is_array( $homepage_faqs ) ) :
?>
<!-- 11. FAQ SECTION (Apple Cupertino Accordion) -->
<section id="faq-section" class="py-10 sm:py-16 md:py-20 bg-white border-t border-black/[0.06]">
	<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
		<!-- Section Header -->
		<div class="text-center max-w-3xl mx-auto mb-8 sm:mb-10 space-y-2">
			<span class="inline-block bg-[#0071E3]/10 text-[#0071E3] text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">HỎI ĐÁP TUYỂN SINH</span>
			<h2 class="text-2xl md:text-3xl font-semibold text-[#1D1D1F] tracking-tight">Câu hỏi thường gặp</h2>
			<p class="text-[#86868B] text-sm md:text-base leading-relaxed">Giải đáp thắc mắc về điều kiện tuyển sinh liên thông, hình thức học trực tuyến, bằng cấp và học phí.</p>
		</div>

		<!-- FAQ Accordion List -->
		<div class="space-y-3">
			<?php
			foreach ( $homepage_faqs as $faq ) :
				$q_text = $faq['question'] ?? '';
				$a_text = $faq['answer'] ?? '';
				if ( empty( $q_text ) || empty( $a_text ) ) continue;
			?>
				<details class="bg-[#F5F5F7] border border-black/[0.04] rounded-2xl overflow-hidden group transition-all hover:bg-[#EEEEF0]">
					<summary class="flex justify-between items-center font-semibold text-[#1D1D1F] p-4 sm:p-5 md:p-6 cursor-pointer list-none select-none text-sm sm:text-base [&::-webkit-details-marker]:hidden gap-3 sm:gap-4">
						<span class="flex items-center gap-3">
							<span class="w-6 h-6 rounded-full bg-[#0071E3]/10 text-[#0071E3] font-semibold text-xs flex items-center justify-center shrink-0">?</span>
							<span><?php echo esc_html( $q_text ); ?></span>
						</span>
						<span class="text-[#86868B] group-open:rotate-180 transition-transform duration-300 shrink-0">
							<?php if ( function_exists( 'ltdh_icon' ) ) { ltdh_icon( 'chevron-down', 'w-4.5 h-4.5' ); } ?>
						</span>
					</summary>
					<div class="px-4 sm:px-5 md:px-6 pb-5 pt-3 text-[#6E6E73] text-xs sm:text-sm md:text-base leading-relaxed border-t border-black/[0.04] bg-white">
						<p><?php echo esc_html( $a_text ); ?></p>
					</div>
				</details>
			<?php endforeach; ?>
		</div>

		<!-- View All FAQs Button -->
		<div class="mt-8 text-center">
			<a href="<?php echo esc_url( home_url( '/cau-hoi-thuong-gap/' ) ); ?>" class="inline-flex items-center gap-1.5 text-sm font-medium text-white bg-[#0071E3] hover:bg-[#0077ED] px-6 py-2.5 rounded-full transition-all active:scale-98 shadow-none">
				<span>Xem tất cả câu hỏi thường gặp</span>
				<span class="text-xs">›</span>
			</a>
		</div>
	</div>
</section>
<?php endif; ?>
