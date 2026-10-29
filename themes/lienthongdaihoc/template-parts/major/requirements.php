<?php
/**
 * Major Requirements & Target Applicants Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$major_id       = isset( $args['major_id'] ) ? (int) $args['major_id'] : get_the_ID();
$context_school = isset( $args['context_school'] ) ? $args['context_school'] : null;
$clean_m_name   = isset( $args['clean_m_name'] ) ? $args['clean_m_name'] : preg_replace( '/^ngành\s+/iu', '', trim( (string) get_the_title( $major_id ) ) );

if ( ! $context_school ) {
	return;
}

$major_target_admissions = function_exists( 'ltdh_get_major_target_admissions' ) ? ltdh_get_major_target_admissions( (int) $major_id ) : [];
if ( ! empty( $major_target_admissions ) ) {
	$school_matched_targets = array_filter( $major_target_admissions, function( $item ) use ( $context_school ) {
		return (int) ( $item['school_id'] ?? 0 ) === (int) $context_school->ID;
	} );
	if ( ! empty( $school_matched_targets ) ) {
		$major_target_admissions = array_values( $school_matched_targets );
	}
}
$major_req  = function_exists( 'ltdh_get_major_admission_requirements' ) ? ltdh_get_major_admission_requirements( (int) $major_id, $context_school ? $context_school->ID : null ) : '';
$major_docs = function_exists( 'ltdh_get_major_required_documents' ) ? ltdh_get_major_required_documents( (int) $major_id, $context_school ? $context_school->ID : null ) : '';
?>

<!-- SECTION: ADMISSION REQUIREMENTS & TARGET APPLICANTS (ĐÚNG NGÀNH & NGÀNH GẦN) -->
<section id="dieu-kien-doi-tuong" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6 mb-6">
	<!-- Hidden anchor targets for backward compatibility -->
	<span id="dieu-kien-xet-tuyen" class="scroll-mt-36 md:scroll-mt-40 block invisible pointer-events-none h-0"></span>
	<span id="doi-tuong-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 block invisible pointer-events-none h-0"></span>
	<span id="nganh-dung-nganh-gan" class="scroll-mt-36 md:scroll-mt-40 block invisible pointer-events-none h-0"></span>
	<span id="ho-so-can-nop" class="scroll-mt-36 md:scroll-mt-40 block invisible pointer-events-none h-0"></span>

	<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3 mb-2">
		<div>
			<h2 class="text-lg md:text-2xl font-bold text-slate-900">Điều kiện &amp; Đối tượng tuyển sinh</h2>
			<p class="text-xs md:text-sm text-slate-500 mt-0.5">Quy định nhóm Đúng ngành &amp; Ngành gần, tiêu chuẩn đầu vào và hồ sơ cần chuẩn bị ngành <?php echo esc_html( $clean_m_name ); ?><?php echo $context_school ? ' tại ' . esc_html( get_the_title( $context_school->ID ) ) : ''; ?></p>
		</div>
		<div class="flex items-center gap-2 shrink-0 self-start sm:self-center">
			<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-[#00308b] text-xs font-bold rounded-full border border-blue-100/80">
				<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
				Quy định tuyển sinh
			</span>
			<button type="button" 
					id="ltdh-toggle-major-req-docs-btn" 
					class="inline-flex items-center gap-1 text-xs font-bold text-[#00308b] hover:text-blue-900 hover:bg-blue-50/80 px-2.5 py-1 rounded-lg transition-all cursor-pointer select-none"
					title="Mở hoặc thu gọn toàn bộ nội dung">
				<span id="ltdh-toggle-major-req-docs-text">Mở tất cả</span>
				<svg id="ltdh-toggle-major-req-docs-icon" class="w-3.5 h-3.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
					<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
				</svg>
			</button>
		</div>
	</div>

	<!-- Collapsible Accordion Group (UI/UX Pro Max) -->
	<div class="divide-y divide-slate-100" id="ltdh-major-req-docs-accordion">
		
		<!-- BLOCK 1: ĐỐI TƯỢNG TUYỂN SINH (ĐÚNG NGÀNH & NGÀNH GẦN) - HIGHLIGHTED -->
		<details id="details-doi-tuong" class="group py-3.5 sm:py-4 transition-colors">
			<summary class="flex items-center justify-between gap-3 cursor-pointer select-none font-bold text-slate-800 text-sm md:text-[15px] list-none hover:text-[#0071E3] transition-colors">
				<div class="flex items-center gap-3 min-w-0">
					<span class="w-7 h-7 rounded-full bg-[#0071E3]/10 text-[#0071E3] group-open:bg-[#0071E3] group-open:text-white flex items-center justify-center text-xs font-semibold shrink-0 transition-colors">
						1
					</span>
					<span class="leading-snug text-slate-800 group-open:text-[#0071E3] transition-colors font-bold">
						Quy định đối tượng tuyển sinh: Đúng ngành &amp; Ngành gần
					</span>
				</div>
				<div class="flex items-center gap-2 shrink-0">
					<span class="text-xs text-slate-400 group-open:hidden hidden sm:inline font-medium">Bấm xem</span>
					<svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 group-open:rotate-180 group-open:text-[#0071E3] transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
						<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
					</svg>
				</div>
			</summary>
			<div class="mt-3 pl-0 sm:pl-10 text-sm md:text-base text-slate-700 leading-relaxed space-y-4">
				
				<?php if ( ! empty( $major_target_admissions ) ) : ?>
					<div class="space-y-4">
						<?php foreach ( $major_target_admissions as $target_group ) : 
							$group_label = $target_group['group_label'] ?? 'Nhóm đối tượng tuyển sinh';
							$target_type = $target_group['target_type'] ?? '';
							$is_dung_nganh = ( $target_type === 'dung_nganh' || false !== stripos( $group_label, 'đúng ngành' ) );
						?>
							<div class="p-4 rounded-xl border <?php echo $is_dung_nganh ? 'bg-emerald-50/40 border-emerald-200/80' : 'bg-blue-50/40 border-blue-200/80'; ?>">
								<div class="flex items-center justify-between mb-2">
									<h4 class="font-extrabold text-sm md:text-base <?php echo $is_dung_nganh ? 'text-emerald-900' : 'text-blue-900'; ?>">
										<?php echo esc_html( $group_label ); ?>
									</h4>
									<span class="text-[11px] font-bold px-2 py-0.5 rounded-full <?php echo $is_dung_nganh ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'; ?>">
										<?php echo $is_dung_nganh ? 'Miễn giảm tối đa' : 'Học bổ sung kiến thức'; ?>
									</span>
								</div>

								<?php if ( ! empty( $target_group['qualifying_majors'] ) ) : ?>
									<div class="text-xs md:text-sm text-slate-700 leading-relaxed mb-2">
										<strong>Các ngành phù hợp:</strong> <?php echo esc_html( $target_group['qualifying_majors'] ); ?>
									</div>
								<?php endif; ?>

								<?php if ( ! empty( $target_group['conversion_note'] ) ) : ?>
									<div class="text-xs text-slate-600 bg-white/80 p-2.5 rounded-lg border border-slate-200/60 leading-relaxed">
										💡 <strong>Lộ trình học bổ sung &amp; Miễn môn:</strong> <?php echo esc_html( $target_group['conversion_note'] ); ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $major_req ) ) : ?>
					<div class="prose prose-slate max-w-none text-slate-700 text-sm md:text-base leading-relaxed bg-slate-50/70 p-4 md:p-5 rounded-xl border border-slate-100/80">
						<?php echo wp_kses_post( $major_req ); ?>
					</div>
				<?php endif; ?>
			</div>
		</details>

		<!-- BLOCK 2: HỒ SƠ TUYỂN SINH CẦN NỘP -->
		<?php if ( ! empty( $major_docs ) ) : ?>
			<details id="details-ho-so" class="group py-3.5 sm:py-4 transition-colors">
				<summary class="flex items-center justify-between gap-3 cursor-pointer select-none font-bold text-slate-800 text-sm md:text-[15px] list-none hover:text-[#0071E3] transition-colors">
					<div class="flex items-center gap-3 min-w-0">
						<span class="w-7 h-7 rounded-full bg-[#0071E3]/10 text-[#0071E3] group-open:bg-[#0071E3] group-open:text-white flex items-center justify-center text-xs font-semibold shrink-0 transition-colors">
							2
						</span>
						<span class="leading-snug text-slate-800 group-open:text-[#0071E3] transition-colors font-bold">
							Hồ sơ xét tuyển cần chuẩn bị
						</span>
					</div>
					<div class="flex items-center gap-2 shrink-0">
						<span class="text-xs text-slate-400 group-open:hidden hidden sm:inline font-medium">Bấm xem</span>
						<svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 group-open:rotate-180 group-open:text-[#0071E3] transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
							<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
						</svg>
					</div>
				</summary>
				<div class="mt-3 pl-0 sm:pl-10 text-sm md:text-base text-slate-700 leading-relaxed">
					<div class="prose prose-slate max-w-none text-slate-700 text-sm md:text-base leading-relaxed bg-slate-50/70 p-4 md:p-5 rounded-xl border border-slate-100/80">
						<?php echo wp_kses_post( $major_docs ); ?>
					</div>
				</div>
			</details>
		<?php endif; ?>
	</div>
</section>
