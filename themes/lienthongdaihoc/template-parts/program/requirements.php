<?php
/**
 * Program Requirements & Documents Partial
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id   = isset( $args['program_id'] ) ? (int) $args['program_id'] : get_the_ID();
$school_id    = isset( $args['school_id'] ) ? (int) $args['school_id'] : (int) get_field( LTDH_META_SCHOOL_REL, $program_id );
$major_id     = isset( $args['major_id'] ) ? (int) $args['major_id'] : (int) get_field( LTDH_META_MAJOR_REL, $program_id );
$school_title = isset( $args['school_title'] ) ? $args['school_title'] : ( $school_id ? get_the_title( $school_id ) : '' );
$program_school_name = $school_title;

$requirements      = isset( $args['requirements'] ) ? $args['requirements'] : ( function_exists( 'ltdh_get_program_admission_requirements' ) ? ltdh_get_program_admission_requirements( $program_id ) : ( get_field( 'admission_requirements', $program_id ) ?: get_post_meta( $program_id, 'admission_requirements', true ) ) );
$documents         = isset( $args['documents'] ) ? $args['documents'] : ( function_exists( 'ltdh_get_program_required_documents' ) ? ltdh_get_program_required_documents( $program_id ) : ( get_field( 'required_documents', $program_id ) ?: get_post_meta( $program_id, 'required_documents', true ) ) );
$contact_formatted = isset( $args['contact_formatted'] ) ? $args['contact_formatted'] : '';

if ( empty( $requirements ) && empty( $documents ) && empty( $contact_formatted ) ) {
	return;
}

$prog_admission_forms = ( $school_id > 0 && function_exists( 'ltdh_get_school_admission_forms' ) ) ? ltdh_get_school_admission_forms( $school_id ) : [];
$admission_package    = function_exists( 'ltdh_get_program_admission_package' ) ? ltdh_get_program_admission_package( $program_id ) : null;
$admission_form_data  = function_exists( 'ltdh_get_program_admission_form_data' ) ? ltdh_get_program_admission_form_data( $program_id ) : null;
$primary_drive        = $admission_form_data['url'] ?? ( $admission_package['primary_url'] ?? ( $prog_admission_forms[0]['url'] ?? '' ) );
$major_rel_id         = get_field( LTDH_META_MAJOR_REL, $program_id ) ?: get_post_meta( $program_id, LTDH_META_MAJOR_REL, true );
$major_school_link    = ( $major_rel_id && $school_id ) ? add_query_arg( 'truong', get_post_field( 'post_name', $school_id ), get_permalink( $major_rel_id ) ) : '';
?>

<!-- SECTION: ADMISSION REQUIREMENTS & DOCUMENTS -->
<section id="dieu-kien-ho-so" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6 mb-6">
	<!-- Hidden anchor targets for backward compatibility -->
	<span id="dieu-kien-xet-tuyen" class="scroll-mt-36 md:scroll-mt-40 block invisible pointer-events-none h-0"></span>
	<span id="doi-tuong-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 block invisible pointer-events-none h-0"></span>
	<span id="ho-so-can-nop" class="scroll-mt-36 md:scroll-mt-40 block invisible pointer-events-none h-0"></span>
	<span id="thong-tin-lien-he" class="scroll-mt-36 md:scroll-mt-40 block invisible pointer-events-none h-0"></span>

	<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3 mb-2">
		<div>
			<h2 class="text-lg md:text-2xl font-bold text-slate-900">Điều kiện &amp; Hồ sơ xét tuyển</h2>
			<p class="text-xs md:text-sm text-slate-500 mt-0.5">Tiêu chuẩn tuyển sinh, hồ sơ cần chuẩn bị và biểu mẫu đăng ký chính thức của <?php echo esc_html( $program_school_name ?: get_the_title( $program_id ) ); ?></p>
		</div>
		<div class="flex items-center gap-2 shrink-0 self-start sm:self-center">
			<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-[#00308b] text-xs font-bold rounded-full border border-blue-100/80">
				<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
				Xét tuyển hồ sơ
			</span>
			<button type="button" 
					id="ltdh-toggle-program-req-docs-btn" 
					class="inline-flex items-center gap-1 text-xs font-bold text-[#00308b] hover:text-blue-900 hover:bg-blue-50/80 px-2.5 py-1 rounded-lg transition-all cursor-pointer select-none"
					title="Mở hoặc thu gọn toàn bộ nội dung">
				<span id="ltdh-toggle-program-req-docs-text">Mở tất cả</span>
				<svg id="ltdh-toggle-program-req-docs-icon" class="w-3.5 h-3.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
					<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
				</svg>
			</button>
		</div>
	</div>

	<!-- Collapsible Accordion Group (UI/UX Pro Max) -->
	<div class="divide-y divide-slate-100" id="ltdh-program-req-docs-accordion">
		<!-- BLOCK 1: ADMISSION REQUIREMENTS -->
		<?php if ( ! empty( $requirements ) ) : ?>
			<details id="details-dieu-kien" class="group py-3.5 sm:py-4 transition-colors">
				<summary class="flex items-center justify-between gap-3 cursor-pointer select-none font-bold text-slate-800 text-sm md:text-[15px] list-none hover:text-[#0071E3] transition-colors">
					<div class="flex items-center gap-3 min-w-0">
						<span class="w-7 h-7 rounded-full bg-[#0071E3]/10 text-[#0071E3] group-open:bg-[#0071E3] group-open:text-white flex items-center justify-center text-xs font-semibold shrink-0 transition-colors">
							1
						</span>
						<span class="leading-snug text-slate-800 group-open:text-[#0071E3] transition-colors font-bold">
							Tiêu chuẩn &amp; Điều kiện xét tuyển
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
						<?php echo wp_kses_post( $requirements ); ?>
					</div>

					<?php if ( ! empty( $major_school_link ) ) : ?>
						<div class="mt-3.5 p-3 sm:p-3.5 rounded-xl bg-blue-50/70 border border-blue-100/90 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
							<div class="flex items-center gap-2 text-xs text-slate-700">
								<span class="w-2 h-2 rounded-full bg-[#0071E3] shrink-0"></span>
								<span>Bạn cần tra cứu quy định <strong>Đúng ngành</strong> hoặc <strong>Ngành gần</strong> để biết môn học chuyển đổi?</span>
							</div>
							<a href="<?php echo esc_url( $major_school_link ); ?>#dieu-kien-doi-tuong" class="inline-flex items-center gap-1 text-xs font-bold text-[#0071E3] hover:underline shrink-0">
								<span>Xem quy định ngành</span>
								<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</details>
		<?php endif; ?>

		<!-- BLOCK 2: REQUIRED DOCUMENTS & GOOGLE DRIVE PACKAGE -->
		<?php if ( ! empty( $documents ) || ! empty( $prog_admission_forms ) || ! empty( $primary_drive ) ) : ?>
			<details id="details-ho-so" class="group py-3.5 sm:py-4 transition-colors">
				<summary class="flex items-center justify-between gap-3 cursor-pointer select-none font-bold text-slate-800 text-sm md:text-[15px] list-none hover:text-[#0071E3] transition-colors">
					<div class="flex items-center gap-3 min-w-0">
						<span class="w-7 h-7 rounded-full bg-[#0071E3]/10 text-[#0071E3] group-open:bg-[#0071E3] group-open:text-white flex items-center justify-center text-xs font-semibold shrink-0 transition-colors">
							2
						</span>
						<span class="leading-snug text-slate-800 group-open:text-[#0071E3] transition-colors font-bold">
							Hồ sơ xét tuyển cần chuẩn bị &amp; Biểu mẫu
						</span>
					</div>
					<div class="flex items-center gap-2 shrink-0">
						<span class="text-xs text-slate-400 group-open:hidden hidden sm:inline font-medium">Bấm xem</span>
						<svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 group-open:rotate-180 group-open:text-[#0071E3] transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
							<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
						</svg>
					</div>
				</summary>
				<div class="mt-3 pl-0 sm:pl-10 text-xs md:text-sm text-slate-700 leading-relaxed space-y-3.5">
					<?php if ( ! empty( $documents ) ) : ?>
						<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base bg-slate-50/70 p-4 rounded-xl border border-slate-100/80">
							<?php echo wp_kses_post( $documents ); ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $prog_admission_forms ) || ! empty( $primary_drive ) ) : ?>
						<!-- GOOGLE DRIVE ADMISSION PACKAGE CARD (UI/UX Pro Max) -->
						<div class="bg-slate-50/70 rounded-xl border border-slate-100/90 p-4 sm:p-5 mt-3.5 transition-colors">
							<div class="flex items-start gap-3.5">
								<!-- Google Drive Brand Icon -->
								<div class="w-10 h-10 rounded-xl bg-white border border-slate-200/60 shadow-2xs flex items-center justify-center shrink-0">
									<svg class="w-5 h-5" viewBox="0 0 24 24">
										<path d="M7.71 3.5L1.15 15l3.43 6 6.55-11.5L7.71 3.5zm3.43 6l3.43 6h8.28l-3.43-6h-8.28zm8.71 7.5l3.43-6-6.57-11.5h-6.86l6.57 11.5 3.43 6z" opacity="0.25"/>
										<path d="M19.85 13.5L16.42 7.5H9.57l3.43 6h6.85z" fill="#FFC107"/>
										<path d="M7.71 3.5L1.15 15l3.43 6 3.43-6-3.43-6h3.13z" fill="#0066DA"/>
										<path d="M8.28 17.5l-3.43 3.5h14.57l3.43-3.5H8.28z" fill="#00AC47"/>
										<path d="M14.71 14.5l-3.43-6-3.43 6h6.86z" fill="#EA4335"/>
									</svg>
								</div>

								<!-- Content & Actions Column -->
								<div class="min-w-0 flex-1">
									<div class="flex flex-wrap items-center gap-2">
										<h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">
											Bộ biểu mẫu &amp; Thư mục hồ sơ tuyển sinh chính thức
										</h3>
										<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-white border border-slate-200/70 text-slate-600">
											Hồ sơ nhà trường
										</span>
									</div>

									<p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
										Bao gồm phiếu đăng ký dự thi, sơ yếu lý lịch và các đơn xét miễn giảm môn. Xem trước, tải về hoặc in trực tiếp trên Google Drive của <?php echo esc_html( $program_school_name ?: 'nhà trường' ); ?>.
									</p>

									<!-- Action Buttons Row -->
									<div class="mt-3 flex flex-wrap items-center gap-2">
										<?php if ( ! empty( $prog_admission_forms ) ) : ?>
											<?php foreach ( $prog_admission_forms as $form_idx => $s_form ) : 
												$is_folder = ( $s_form['type'] ?? '' ) === 'drive_folder';
												$clean_label = str_replace( 'Thư mục Google Drive hồ sơ trường', 'Thư mục Drive chung', $s_form['label'] );
												$clean_label = str_replace( 'Hồ sơ Google Drive hệ ', 'Hồ sơ hệ ', $clean_label );
											?>
												<a href="<?php echo esc_url( $s_form['url'] ); ?>" target="_blank" rel="nofollow noopener noreferrer" class="ltdh-lead-magnet-btn inline-flex items-center gap-1.5 px-3 py-1.5 <?php echo 0 === $form_idx ? 'bg-blue-50/80 hover:bg-blue-100/90 text-[#00308b] border border-blue-200/80 font-bold' : 'bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border border-slate-200/90 font-medium'; ?> text-xs rounded-lg transition-all cursor-pointer group shadow-2xs hover:shadow-xs" data-file-url="<?php echo esc_url( $s_form['url'] ); ?>" data-file-title="<?php echo esc_attr( $s_form['title'] ); ?>">
													<?php if ( $is_folder ) : ?>
														<svg class="w-3.5 h-3.5 text-[#00308b] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
															<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776" />
														</svg>
													<?php else : ?>
														<svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#00308b] shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
															<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
														</svg>
													<?php endif; ?>
													<span><?php echo esc_html( $clean_label ); ?></span>
													<svg class="w-3 h-3 text-slate-400 group-hover:text-[#00308b] transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
												</a>
											<?php endforeach; ?>
										<?php elseif ( ! empty( $primary_drive ) ) : ?>
											<a href="<?php echo esc_url( $primary_drive ); ?>" target="_blank" rel="nofollow noopener noreferrer" class="ltdh-lead-magnet-btn inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-blue-50/80 hover:bg-blue-100/90 text-[#00308b] border border-blue-200/80 text-xs font-bold rounded-lg shadow-2xs hover:shadow-xs transition-colors cursor-pointer group" data-file-url="<?php echo esc_url( $primary_drive ); ?>" data-file-title="Thư mục hồ sơ: <?php echo esc_attr( $program_school_name ); ?>">
												<svg class="w-3.5 h-3.5 text-[#00308b] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
													<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776" />
												</svg>
												<span>Mở thư mục trên Google Drive</span>
												<svg class="w-3 h-3 text-[#00308b] transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
											</a>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</details>
		<?php endif; ?>

		<!-- BLOCK 3: CONTACT & ADMISSION VENUE -->
		<?php if ( ! empty( $contact_formatted ) ) : ?>
			<details id="details-lien-he" class="group py-3.5 sm:py-4 transition-colors">
				<summary class="flex items-center justify-between gap-3 cursor-pointer select-none font-bold text-slate-800 text-sm md:text-[15px] list-none hover:text-[#D97706] transition-colors">
					<div class="flex items-center gap-3 min-w-0">
						<span class="w-7 h-7 rounded-full bg-[#FF9500]/15 text-[#D97706] group-open:bg-[#FF9500] group-open:text-white flex items-center justify-center text-xs font-semibold shrink-0 transition-colors">
							3
						</span>
						<span class="leading-snug text-slate-800 group-open:text-[#D97706] transition-colors font-bold">
							Thông tin liên hệ &amp; Tiếp nhận hồ sơ
						</span>
					</div>
					<div class="flex items-center gap-2 shrink-0">
						<span class="text-xs text-slate-400 group-open:hidden hidden sm:inline font-medium">Bấm xem</span>
						<svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 group-open:rotate-180 group-open:text-[#D97706] transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
							<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
						</svg>
					</div>
				</summary>
				<div class="mt-3 pl-0 sm:pl-10 text-sm md:text-base text-slate-700 leading-relaxed">
					<div class="prose prose-slate max-w-none text-slate-700 text-sm leading-relaxed prose-card-list bg-slate-50/70 p-4 md:p-5 rounded-xl border border-slate-100/80">
						<?php echo wp_kses_post( $contact_formatted ); ?>
					</div>
				</div>
			</details>
		<?php endif; ?>
	</div>
</section>
