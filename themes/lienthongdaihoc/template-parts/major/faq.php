<?php
/**
 * Major FAQ Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$major_id       = isset( $args['major_id'] ) ? (int) $args['major_id'] : get_the_ID();
$context_school = isset( $args['context_school'] ) ? $args['context_school'] : null;
$clean_m_name   = isset( $args['clean_m_name'] ) ? $args['clean_m_name'] : ( function_exists( 'ltdh_normalize_major_name' ) ? ltdh_normalize_major_name( get_the_title( $major_id ) ) : get_the_title( $major_id ) );
$major_faqs     = isset( $args['major_faqs'] ) ? $args['major_faqs'] : ( function_exists( 'ltdh_get_major_faqs' ) ? ltdh_get_major_faqs( (int) $major_id, $context_school ? $context_school->ID : null ) : [] );

if ( empty( $major_faqs ) ) {
	return;
}

$faq_context_school_name = $context_school ? get_the_title( $context_school->ID ) : '';
?>

<!-- SECTION: MAJOR FAQ (UI/UX Pro Max - Borderless Accordion & Structured Bullets) -->
<section id="hoi-dap-nganh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-xl shadow-xs border border-slate-100/80 p-5 md:p-6 mb-8">
	<div class="border-b border-slate-200/80 pb-4 mb-4">
		<div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#00308b] border border-blue-100/80 mb-2">
			<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
				<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
			</svg>
			<span><?php echo $context_school ? 'Hỏi đáp tuyển sinh' : 'Cẩm nang định hướng'; ?></span>
		</div>
		<h2 class="text-xl md:text-2xl font-black text-slate-900 leading-tight">
			Câu hỏi thường gặp về Liên thông ngành <?php echo esc_html( $clean_m_name ); ?><?php echo $context_school ? ' tại ' . esc_html( $faq_context_school_name ) : ''; ?>
		</h2>
		<p class="text-xs sm:text-sm text-slate-500 mt-1.5">
			<?php if ( $context_school ) : ?>
				Giải đáp các thắc mắc thường gặp về điều kiện xét tuyển, đối tượng ngành đúng - ngành gần và chương trình học tại <?php echo esc_html( $faq_context_school_name ); ?>.
			<?php else : ?>
				Giải đáp thắc mắc về giá trị bằng tốt nghiệp, điều kiện miễn giảm môn, tiêu chí lựa chọn trường và cơ hội nghề nghiệp sau khi tốt nghiệp.
			<?php endif; ?>
		</p>
	</div>

	<div class="divide-y divide-slate-100">
		<?php foreach ( $major_faqs as $f_idx => $faq_item ) : 
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
				<h3 class="font-bold text-xs sm:text-sm text-slate-900">
					<?php echo $context_school ? 'Cần hỗ trợ hồ sơ xét tuyển tại ' . esc_html( $faq_context_school_name ) . '?' : 'Chưa rõ bằng cấp của bạn có đủ điều kiện xét tuyển?'; ?>
				</h3>
				<p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">
					Chuyên viên tư vấn sẽ rà soát bảng điểm Cao đẳng và hỗ trợ kiểm tra môn miễn giảm hoàn toàn miễn phí.
				</p>
			</div>
		</div>
		<a href="/kiem-tra-dieu-kien?nganh=<?php echo esc_attr( get_post_field( 'post_name', $major_id ) ); ?><?php echo $context_school ? '&truong=' . esc_attr( get_post_field( 'post_name', $context_school->ID ) ) : ''; ?>" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-full bg-[#0071E3] hover:bg-[#0077ED] text-white text-xs font-medium tracking-tight shrink-0 transition-all shadow-none hover:shadow-xs active:scale-98 no-underline">
			<span>Kiểm tra hồ sơ ngay</span>
			<span>&rarr;</span>
		</a>
	</div>
</section>
