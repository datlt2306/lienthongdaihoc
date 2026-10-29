<?php
/**
 * Program Tuition & Duration Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id = isset( $args['program_id'] ) ? (int) $args['program_id'] : get_the_ID();
$school_id  = isset( $args['school_id'] ) ? (int) $args['school_id'] : (int) get_field( LTDH_META_SCHOOL_REL, $program_id );

$tuition         = isset( $args['tuition'] ) ? $args['tuition'] : ltdh_get_program_tuition_display( $program_id );
$duration        = isset( $args['duration'] ) ? $args['duration'] : ( function_exists( 'ltdh_get_program_duration' ) ? ltdh_get_program_duration( $program_id ) : ( get_field( LTDH_META_DURATION, $program_id ) ?: '1.5 - 2 năm' ) );
$tuition_year    = get_field( 'tuition_academic_year', $program_id );
$admission_fee   = function_exists( 'ltdh_get_program_admission_fee' ) ? ltdh_get_program_admission_fee( $program_id ) : ( get_field( 'admission_fee', $program_id ) ?: get_post_meta( $program_id, 'admission_fee', true ) );

$exemptions_data = function_exists( 'ltdh_get_program_exemptions' ) ? ltdh_get_program_exemptions( $program_id ) : [ 'title' => '', 'intro' => '', 'items' => [], 'inherited' => false ];
$exemption_title = ! empty( $exemptions_data['title'] ) ? $exemptions_data['title'] : 'Quy định miễn môn & Chuyển đổi tín chỉ';
$exemption_intro = ! empty( $exemptions_data['intro'] ) ? $exemptions_data['intro'] : '';
$exemption_items = ! empty( $exemptions_data['items'] ) ? $exemptions_data['items'] : [];

$increase_roadmap = function_exists( 'ltdh_get_program_tuition_roadmap' ) 
	? ltdh_get_program_tuition_roadmap( $program_id ) 
	: ( get_field( 'tuition_increase_roadmap', $program_id ) ?: ( $school_id ? get_field( 'tuition_increase_roadmap', $school_id ) : '' ) );
?>

<!-- SECTION 6: TUITION & SECTION 7: DURATION -->
<section id="hoc-phi-thoi-gian" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6">
	<h2 class="text-base sm:text-xl md:text-2xl font-bold text-slate-900 mb-3 sm:mb-4">Học phí & Thời gian học</h2>
	
	<!-- 1 Hàng trên cả Mobile và Desktop: 2 Cột song song -->
	<div class="grid grid-cols-2 gap-2.5 sm:gap-4 md:gap-6">
		<!-- Card 1: Học phí -->
		<div class="bg-blue-50/50 rounded-xl sm:rounded-2xl p-3 sm:p-5 flex flex-col justify-between">
			<div>
				<div class="flex items-center gap-1.5 text-blue-700 mb-1 sm:mb-2">
					<svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
					<span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-600">Đơn giá học phí</span>
				</div>
				
				<div class="text-sm sm:text-lg md:text-2xl font-black text-slate-900 leading-snug">
					<?php echo esc_html( $tuition ?: 'Liên hệ ban tuyển sinh' ); ?>
				</div>
				
				<?php if ( isset($tuition_year) && $tuition_year ) : ?>
					<span class="text-[10px] sm:text-xs text-slate-500 font-medium block mt-0.5">(Năm học <?php echo esc_html( $tuition_year ); ?>)</span>
				<?php endif; ?>
			</div>

			<?php 
			if ( ! empty( $admission_fee ) || ( is_numeric( $admission_fee ) && (int) $admission_fee === 0 ) ) : 
				$fee_info = function_exists( 'ltdh_format_admission_fee' ) ? ltdh_format_admission_fee( $admission_fee ) : [ 'display' => esc_html( $admission_fee ) . ' / hồ sơ', 'is_free' => false ];
			?>
				<div class="mt-2.5 pt-2 border-t border-blue-100/60 flex flex-wrap items-center justify-between text-[10px] sm:text-xs gap-1">
					<span class="text-slate-500">Lệ phí xét:</span>
					<span class="font-bold <?php echo ! empty( $fee_info['is_free'] ) ? 'text-emerald-600' : 'text-slate-800'; ?>">
						<?php echo esc_html( $fee_info['display'] ); ?>
					</span>
				</div>
			<?php endif; ?>
		</div>

		<!-- Card 2: Thời gian học tập -->
		<div class="bg-amber-50/50 rounded-xl sm:rounded-2xl p-3 sm:p-5 flex flex-col justify-between">
			<div>
				<div class="flex items-center gap-1.5 text-amber-700 mb-1 sm:mb-2">
					<svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
					<span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-600">Thời gian học</span>
				</div>
				
				<div class="text-sm sm:text-xl md:text-2xl font-black text-slate-900 leading-snug">
					<?php echo esc_html( $duration ?: '2.0 - 3.0 năm' ); ?>
				</div>
				
				<span class="text-[10px] sm:text-xs text-slate-500 font-medium block mt-0.5">Lộ trình chuẩn</span>
			</div>
		</div>
	</div>

	<!-- Ghi chú & Lộ trình học phí (Gọn gàng, loại bỏ các khối border lồng nhau) -->
	<div class="mt-3 bg-slate-50/70 rounded-xl p-3 sm:p-3.5 space-y-1.5 text-xs text-slate-600 leading-relaxed">
		<?php if ( $increase_roadmap ) : ?>
			<p class="flex items-start gap-1.5">
				<span class="text-blue-600 shrink-0">💡</span>
				<span><strong class="text-slate-800">Lộ trình học phí:</strong> <?php echo esc_html( $increase_roadmap ); ?></span>
			</p>
		<?php endif; ?>
		<p class="flex items-start gap-1.5">
			<span class="text-amber-500 shrink-0">📌</span>
			<span><strong class="text-slate-800">Lưu ý xét tuyển:</strong> Quy định miễn trừ học phần, chuyển đổi tín chỉ và số tín chỉ thực tế cần hoàn thành sẽ được Nhà trường thẩm định cụ thể dựa trên bảng điểm tốt nghiệp của từng học viên.</span>
		</p>
	</div>
	<?php 
	if ( ! empty( $exemption_items ) && is_array( $exemption_items ) ) :
	?>
		<div class="mt-5 border-t border-slate-100 pt-5">
			<div class="bg-blue-50/40 rounded-2xl p-5">
				<div class="flex items-center gap-2 border-b border-blue-100/60 pb-3 mb-3">
					<svg class="w-6 h-6 text-blue-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222M12 14v8"></path></svg>
					<h3 class="font-extrabold text-blue-950 text-base md:text-lg">
						<?php echo esc_html( $exemption_title ?: 'Quy định miễn môn & Chuyển đổi tín chỉ' ); ?>
					</h3>
				</div>
				<?php if ( $exemption_intro ) : ?>
					<p class="text-xs md:text-sm text-blue-950 font-medium mb-4 leading-relaxed">
						<?php echo esc_html( $exemption_intro ); ?>
					</p>
				<?php endif; ?>

				<div class="space-y-4">
					<?php foreach ( $exemption_items as $index => $item ) : ?>
						<div class="bg-white rounded-xl p-4 sm:p-4.5 shadow-xs transition-all duration-300">
							<div class="flex items-center gap-2.5 mb-2">
								<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-black shrink-0"><?php echo esc_html( $index + 1 ); ?></span>
								<h4 class="font-extrabold text-slate-900 text-sm md:text-base"><?php echo esc_html( $item['subject_name'] ?? '' ); ?></h4>
							</div>
							<?php if ( ! empty( $item['condition_note'] ) ) : ?>
								<p class="text-slate-700 text-xs md:text-sm leading-relaxed pl-8">
									<?php echo nl2br( esc_html( $item['condition_note'] ) ); ?>
								</p>
							<?php endif; ?>

							<?php if ( ! empty( $item['cert_scores'] ) && is_array( $item['cert_scores'] ) ) : ?>
								<div class="pl-8 mt-3">
									<div class="grid grid-cols-3 gap-2.5 sm:gap-3 mb-3">
										<?php foreach ( $item['cert_scores'] as $cert ) : ?>
											<div class="bg-slate-50 rounded-xl p-2.5 sm:p-3 text-center transition-all duration-200 cursor-pointer">
												<span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1"><?php echo esc_html( $cert['cert_name'] ?? '' ); ?></span>
												<span class="text-sm md:text-base font-black text-blue-900"><?php echo esc_html( $cert['min_score'] ?? '' ); ?></span>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $item['cert_note'] ) ) : ?>
								<div class="pl-8 mt-2 flex items-start gap-2 text-xs md:text-sm text-slate-600 leading-relaxed">
									<svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
									<p><?php echo esc_html( $item['cert_note'] ); ?></p>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $item['assessment_process'] ) ) : ?>
								<div class="mt-3 bg-amber-50/80 rounded-xl p-3.5 text-xs md:text-sm text-amber-900 leading-relaxed flex items-start gap-2.5">
									<svg class="w-4.5 h-4.5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
									<p><?php echo esc_html( $item['assessment_process'] ); ?></p>
								</div>
							<?php endif; ?>

							<?php 
							$exemption_target_url = ! empty( $item['form_url'] ) ? $item['form_url'] : ( $item['file_url'] ?? '' );
							if ( ! empty( $exemption_target_url ) ) :
								$is_ex_drive = function_exists( 'ltdh_is_google_drive_url' ) && ltdh_is_google_drive_url( $exemption_target_url );
							?>
								<div class="mt-3 pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
									<div class="flex items-center gap-2 min-w-0">
										<span class="w-6 h-6 rounded-md <?php echo $is_ex_drive ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-700'; ?> flex items-center justify-center text-[10px] font-black shrink-0">
											<?php echo $is_ex_drive ? 'DRIVE' : 'PDF'; ?>
										</span>
										<span class="text-xs text-slate-600 truncate">
											Mẫu đơn xét miễn môn: <strong class="text-slate-800"><?php echo esc_html( $item['subject_name'] ?? 'Môn học' ); ?></strong>
										</span>
									</div>
									<a href="<?php echo esc_url( $exemption_target_url ); ?>" target="_blank" rel="nofollow noopener noreferrer" class="ltdh-lead-magnet-btn inline-flex items-center gap-1.5 px-3 py-1.5 <?php echo $is_ex_drive ? 'bg-emerald-50 hover:bg-emerald-600 text-emerald-900 hover:text-white border border-emerald-200/80 hover:border-emerald-600' : 'bg-amber-50 hover:bg-amber-600 text-amber-900 hover:text-white border border-amber-200/80 hover:border-amber-600'; ?> text-xs font-bold rounded-lg transition-all shrink-0 cursor-pointer shadow-2xs self-start sm:self-auto" data-file-url="<?php echo esc_url( $exemption_target_url ); ?>" data-file-title="Mẫu đơn xét miễn môn - <?php echo esc_attr( $item['subject_name'] ?? '' ); ?>">
										<?php if ( $is_ex_drive ) : ?>
											<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
											<span>Mở trên Google Drive</span>
										<?php else : ?>
											<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
											<span>Tải đơn miễn môn</span>
										<?php endif; ?>
									</a>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</section>
