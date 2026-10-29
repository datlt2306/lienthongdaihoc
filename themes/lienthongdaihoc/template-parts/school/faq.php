<?php
/**
 * School FAQ Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$school_id   = isset( $args['school_id'] ) ? (int) $args['school_id'] : get_the_ID();
$school_title = isset( $args['school_title'] ) ? $args['school_title'] : get_the_title( $school_id );
$school_faqs = isset( $args['school_faqs'] ) ? $args['school_faqs'] : ( function_exists( 'ltdh_get_school_faqs' ) ? ltdh_get_school_faqs( $school_id ) : [] );

if ( empty( $school_faqs ) ) {
	return;
}
?>

<!-- SCHOOL SPECIFIC FAQ SECTION -->
<section id="hoi-dap-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6 mb-6">
	<div class="border-b border-slate-100 pb-4 mb-5">
		<div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-[#00308b] text-[11px] font-extrabold uppercase tracking-wider mb-2">
			<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
				<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
			</svg>
			<span>Hỏi đáp tuyển sinh</span>
		</div>
		<h2 class="text-xl md:text-2xl font-black text-slate-900 leading-tight">
			Câu hỏi thường gặp về Liên thông tại <?php echo esc_html( $school_title ); ?>
		</h2>
		<p class="text-xs sm:text-sm text-slate-500 mt-1.5">
			Giải đáp chi tiết các thắc mắc phổ biến về giá trị văn bằng, điều kiện xét tuyển, hình thức học tập và học phí tại trường.
		</p>
	</div>

	<div class="divide-y divide-slate-100">
		<?php foreach ( $school_faqs as $f_idx => $faq_item ) : 
			$is_first_open = ( 0 === $f_idx ) ? '' : '';
			$faq_num       = $f_idx + 1;
		?>
			<details <?php echo $is_first_open; ?> class="group py-3.5 sm:py-4 transition-colors" id="faq-<?php echo esc_attr( $faq_num ); ?>">
				<summary class="flex items-center justify-between gap-3 cursor-pointer font-bold text-slate-800 text-sm sm:text-[15px] list-none select-none hover:text-[#0071E3] transition-colors">
					<div class="flex items-start gap-2.5 min-w-0">
						<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#0071E3]/10 text-[#0071E3] text-xs font-semibold shrink-0 mt-0.5 group-open:bg-[#0071E3] group-open:text-white transition-colors">
							?
						</span>
						<span class="leading-snug text-slate-800 group-open:text-[#0071E3] transition-colors">
							<?php echo esc_html( $faq_item['question'] ); ?>
						</span>
					</div>
					<svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 group-open:rotate-180 group-open:text-[#0071E3] transition-transform shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
						<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
					</svg>
				</summary>
				<div id="faq-<?php echo esc_attr( $faq_num ); ?>-answer" class="mt-2.5 sm:mt-3 pl-8 sm:pl-8 text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
					<?php echo wp_kses_post( $faq_item['answer'] ); ?>
				</div>
			</details>
		<?php endforeach; ?>
	</div>

	<!-- Mini FAQ CTA box (Border-free soft surface) -->
	<div class="mt-6 p-4 sm:p-5 rounded-2xl bg-slate-50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
		<div class="flex items-center gap-2.5">
			<span class="text-xl">💡</span>
			<div>
				<h3 class="font-bold text-xs sm:text-sm text-slate-900">Bạn còn câu hỏi nào khác về tuyển sinh của trường?</h3>
				<p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Đội ngũ cán bộ tuyển sinh luôn sẵn sàng hỗ trợ kiểm tra bằng cấp và tư vấn lộ trình học phù hợp nhất.</p>
			</div>
		</div>
		<a href="#register" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-full bg-[#0071E3] hover:bg-[#0077ED] text-white text-xs font-medium tracking-tight shrink-0 transition-all shadow-none hover:shadow-xs active:scale-98 no-underline">
			<span>Hỏi tư vấn viên</span>
			<span>&rarr;</span>
		</a>
	</div>
</section>
