<?php
/**
 * Program FAQ Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id = isset( $args['program_id'] ) ? (int) $args['program_id'] : get_the_ID();
$faqs       = isset( $args['faqs'] ) ? $args['faqs'] : ( function_exists( 'ltdh_get_program_faqs' ) ? ltdh_get_program_faqs( $program_id ) : get_field( 'faq', $program_id ) );

if ( empty( $faqs ) ) {
	return;
}
?>

<!-- SECTION 9: FAQ (Collapsible Accordion with UI/UX Pro Max) -->
<section class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6 mb-8" id="hoi-dap">
	<div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4 gap-2">
		<div>
			<div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-[#00308b] border border-blue-100/80 mb-1.5">
				<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
					<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
				</svg>
				<span>Hỏi đáp tuyển sinh</span>
			</div>
			<h2 class="text-lg md:text-2xl font-bold text-slate-900 leading-tight">Câu hỏi thường gặp</h2>
		</div>

		<!-- Bulk Toggle Button -->
		<button type="button" 
				id="ltdh-toggle-program-faq-btn"
				class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200/90 hover:border-slate-300 bg-slate-50 hover:bg-slate-100 text-xs font-bold text-slate-700 transition-colors cursor-pointer shrink-0"
				title="Mở hoặc thu gọn toàn bộ câu hỏi">
			<span id="ltdh-toggle-faq-text">Mở tất cả</span>
			<svg id="ltdh-toggle-faq-icon" class="w-3.5 h-3.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
				<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
			</svg>
		</button>
	</div>

	<div class="divide-y divide-slate-100" id="ltdh-program-faq-accordion">
		<?php foreach ( $faqs as $index => $item ) : 
			$faq_num = $index + 1;
		?>
			<details class="group py-3.5 sm:py-4 transition-colors">
				<summary class="flex items-center justify-between gap-3 cursor-pointer select-none font-bold text-slate-800 text-sm md:text-[15px] list-none hover:text-[#0071E3] transition-colors">
					<div class="flex items-start gap-2.5 sm:gap-3 min-w-0" id="faq-<?php echo esc_attr( $faq_num ); ?>">
						<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#0071E3]/10 text-[#0071E3] group-open:bg-[#0071E3] group-open:text-white text-xs font-semibold shrink-0 mt-0.5 transition-colors">
							Q
						</span>
						<h3 class="leading-snug text-slate-800 group-open:text-[#0071E3] transition-colors font-bold text-sm md:text-base m-0">
							<?php echo esc_html( $item['question'] ); ?>
						</h3>
					</div>
					<svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 group-open:rotate-180 group-open:text-[#0071E3] transition-transform shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
						<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
					</svg>
				</summary>
				<div id="faq-<?php echo esc_attr( $faq_num ); ?>-answer" class="mt-2.5 sm:mt-3 pl-8 sm:pl-9 text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
					<?php echo strpos( $item['answer'], '<p' ) !== false ? wp_kses_post( $item['answer'] ) : wp_kses_post( wpautop( $item['answer'] ) ); ?>
				</div>
			</details>
		<?php endforeach; ?>
	</div>
</section>
