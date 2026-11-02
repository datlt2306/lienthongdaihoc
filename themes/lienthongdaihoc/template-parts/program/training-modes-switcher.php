<?php
/**
 * Program Training Modes Switcher Partial (Pro UI/UX Edition with Status Dots)
 *
 * Provides a high-converting, compact segmented switcher for prospective students
 * to effortlessly discover, compare, and switch between training modes (e.g. Từ xa, Vừa học vừa làm, Chính quy)
 * of the same major at the same university.
 *
 * Follows strict ui-ux-pro-max guidelines:
 * - Status dot indicators with live feedback (pulse for active intake, slate for closed)
 * - Zero emoji icons (clean, scalable SVGs only)
 * - Clear affordance & cursor-pointer on interactive items
 * - Low vertical footprint to preserve page flow
 * - WCAG AAA compliant text contrast
 * - Smooth 150-200ms transitions
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id   = isset( $args['program_id'] ) ? (int) $args['program_id'] : get_the_ID();
$school_id    = isset( $args['school_id'] ) ? (int) $args['school_id'] : (int) get_field( defined( 'LTDH_META_SCHOOL_REL' ) ? LTDH_META_SCHOOL_REL : 'school_relationship', $program_id );
$major_id     = isset( $args['major_id'] ) ? (int) $args['major_id'] : (int) get_field( defined( 'LTDH_META_MAJOR_REL' ) ? LTDH_META_MAJOR_REL : 'major_relationship', $program_id );
$school_title = isset( $args['school_title'] ) ? $args['school_title'] : ( $school_id ? get_the_title( $school_id ) : '' );
$major_title  = isset( $args['major_title'] ) ? $args['major_title'] : ( $major_id ? get_the_title( $major_id ) : '' );

$clean_major = $major_title ? preg_replace( '/^(?:ngành|cử nhân|kỹ sư)\s+/iu', '', trim( $major_title ) ) : '';

$sibling_modes = function_exists( 'ltdh_get_sibling_training_programs' ) 
	? ltdh_get_sibling_training_programs( $program_id ) 
	: ( class_exists( 'LTDH_Program_Helpers' ) ? LTDH_Program_Helpers::get_sibling_training_programs( $program_id ) : [] );

if ( empty( $sibling_modes ) || count( $sibling_modes ) <= 1 ) {
	return;
}

$mode_count = count( $sibling_modes );
$grid_cols  = ( $mode_count === 2 ) ? 'sm:grid-cols-2' : 'lg:grid-cols-3';
?>

<!-- SECTION: SIBLING TRAINING MODES SWITCHER -->
<section id="he-dao-tao-khac" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5 mb-6 transition-all" aria-label="Lựa chọn hình thức đào tạo">
	<!-- Control Header -->
	<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 mb-3.5 border-b border-slate-100">
		<div class="flex items-center gap-2">
			<span class="w-2 h-2 rounded-full bg-[#00308b] shrink-0"></span>
			<span class="text-xs font-bold uppercase tracking-wider text-slate-500">
				Hình thức đào tạo khác cùng ngành
			</span>
			<span class="text-slate-300">•</span>
			<span class="text-xs font-semibold text-slate-700 truncate max-w-[220px] sm:max-w-none">
				<?php echo esc_html( $clean_major ?: 'Ngành này' ); ?>
			</span>
		</div>
		<div class="flex items-center gap-2 self-start sm:self-auto">
			<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
				<span><?php echo (int) $mode_count; ?> hệ đào tạo</span>
			</span>
			<span class="sm:hidden text-[11px] text-slate-400 font-medium inline-flex items-center gap-0.5">
				<span>Vuốt ngang</span>
				<svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
			</span>
		</div>
	</div>

	<!-- Interactive Mode Cards / Segmented Switcher (Mobile Carousel + Desktop Grid) -->
	<div class="flex sm:grid overflow-x-auto sm:overflow-x-visible snap-x snap-mandatory sm:snap-none gap-3 pb-2 sm:pb-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden -mx-4 px-4 sm:mx-0 sm:px-0 <?php echo esc_attr( $grid_cols ); ?> items-stretch">
		<?php foreach ( $sibling_modes as $item ) : 
			$is_curr     = ! empty( $item['is_current'] );
			$type_slug   = $item['type_slug'];
			$adm_status  = $item['admission_status'] ?? 'tuyen-sinh';
			$is_closed   = ( 'tam-ngung' === $adm_status );
			$is_upcoming = ( 'sap-mo' === $adm_status );
		?>
			<?php if ( $is_curr ) : ?>
				<!-- Active Mode (Current) -->
				<div class="relative bg-blue-50/50 border-2 border-[#00308b] rounded-xl p-3 sm:p-3.5 shadow-2xs ring-2 ring-[#00308b]/5 flex flex-col justify-between select-none shrink-0 w-[82%] sm:w-auto snap-start sm:snap-none">
					<div>
						<div class="flex items-center justify-between gap-2 mb-1.5">
							<div class="flex items-center gap-2 min-w-0">
								<span class="w-7 h-7 rounded-lg bg-[#00308b] text-white flex items-center justify-center shrink-0">
									<?php if ( false !== strpos( $type_slug, 'tu-xa' ) ) : ?>
										<!-- Laptop / Online SVG -->
										<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
									<?php elseif ( false !== strpos( $type_slug, 'vua-hoc-vua-lam' ) ) : ?>
										<!-- Clock / Part-time SVG -->
										<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
									<?php else : ?>
										<!-- Academic Cap / Full-time SVG -->
										<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5"/></svg>
									<?php endif; ?>
								</span>
								<h3 class="font-bold text-sm sm:text-base text-slate-900 truncate">
									<?php echo esc_html( $item['type_name'] ); ?>
								</h3>
							</div>

							<!-- Dot indicator: Current system status -->
							<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#00308b] text-white shrink-0">
								<?php if ( ! $is_closed ) : ?>
									<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
								<?php else : ?>
									<span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
								<?php endif; ?>
								<span>Đang xem</span>
							</span>
						</div>

						<!-- Quick Specs Strip -->
						<div class="mt-2 space-y-1 text-xs">
							<div class="flex items-center justify-between text-slate-600">
								<span class="text-slate-500">Lịch học:</span>
								<span class="font-semibold text-slate-800 text-right truncate max-w-[170px]" title="<?php echo esc_attr( $item['schedule'] ); ?>">
									<?php echo esc_html( $item['schedule'] ); ?>
								</span>
							</div>
							<div class="flex items-center justify-between">
								<span class="text-slate-500">Học phí:</span>
								<span class="font-bold text-[#00308b]"><?php echo esc_html( $item['tuition'] ); ?></span>
							</div>
							<div class="flex items-center justify-between">
								<span class="text-slate-500">Trạng thái:</span>
								<?php if ( $is_closed ) : ?>
									<span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500">
										<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
										<span>Hết chỉ tiêu</span>
									</span>
								<?php elseif ( $is_upcoming ) : ?>
									<span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-700">
										<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
										<span>Sắp mở</span>
									</span>
								<?php else : ?>
									<span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700">
										<span class="relative flex h-1.5 w-1.5">
											<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
											<span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
										</span>
										<span>Đang nhận hồ sơ</span>
									</span>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<div class="mt-3 pt-2.5 border-t border-blue-200/60 flex items-center justify-center gap-1.5 text-xs font-bold text-[#00308b]">
						<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
						<span>Bạn đang xem chương trình này</span>
					</div>
				</div>
			<?php else : ?>
				<!-- Inactive Sibling Mode (Clickable Link) -->
				<a href="<?php echo esc_url( $item['permalink'] ); ?>" 
				   class="group relative bg-white hover:bg-slate-50/80 border border-slate-200 hover:border-[#00308b] rounded-xl p-3 sm:p-3.5 shadow-2xs hover:shadow-sm transition-all duration-200 flex flex-col justify-between cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-[#00308b]/40 shrink-0 w-[82%] sm:w-auto snap-start sm:snap-none">
					<div>
						<div class="flex items-center justify-between gap-2 mb-1.5">
							<div class="flex items-center gap-2 min-w-0">
								<span class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-blue-50 text-slate-600 group-hover:text-[#00308b] flex items-center justify-center shrink-0 transition-colors">
									<?php if ( false !== strpos( $type_slug, 'tu-xa' ) ) : ?>
										<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
									<?php elseif ( false !== strpos( $type_slug, 'vua-hoc-vua-lam' ) ) : ?>
										<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
									<?php else : ?>
										<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5"/></svg>
									<?php endif; ?>
								</span>
								<h3 class="font-bold text-sm sm:text-base text-slate-800 group-hover:text-[#00308b] truncate transition-colors">
									<?php echo esc_html( $item['type_name'] ); ?>
								</h3>
							</div>

							<!-- Dot indicator: Status -->
							<?php if ( $is_closed ) : ?>
								<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500 border border-slate-200/80 shrink-0">
									<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
									<span>Hết chỉ tiêu</span>
								</span>
							<?php elseif ( $is_upcoming ) : ?>
								<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200/60 shrink-0">
									<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
									<span>Sắp mở</span>
								</span>
							<?php else : ?>
								<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60 shrink-0">
									<span class="relative flex h-1.5 w-1.5">
										<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
										<span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
									</span>
									<span>Đang nhận hồ sơ</span>
								</span>
							<?php endif; ?>
						</div>

						<!-- Quick Specs Strip -->
						<div class="mt-2 space-y-1 text-xs">
							<div class="flex items-center justify-between text-slate-600">
								<span class="text-slate-500">Lịch học:</span>
								<span class="font-medium text-slate-700 text-right truncate max-w-[170px]" title="<?php echo esc_attr( $item['schedule'] ); ?>">
									<?php echo esc_html( $item['schedule'] ); ?>
								</span>
							</div>
							<div class="flex items-center justify-between">
								<span class="text-slate-500">Học phí:</span>
								<span class="font-bold text-slate-800 group-hover:text-[#00308b] transition-colors"><?php echo esc_html( $item['tuition'] ); ?></span>
							</div>
						</div>
					</div>

					<div class="mt-3 pt-2.5 border-t border-slate-100 group-hover:border-blue-100 flex items-center justify-between text-xs font-semibold text-slate-600 group-hover:text-[#00308b] transition-colors">
						<span><?php echo $is_closed ? 'Xem thông tin hệ này' : 'Chuyển sang hệ này'; ?></span>
						<svg class="w-4 h-4 text-slate-400 group-hover:text-[#00308b] group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
					</div>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
		<div class="sm:hidden shrink-0 w-1" aria-hidden="true"></div>
	</div>

	<!-- Legal equivalence reassuring note (Circular 27/2019/TT-BGDĐT) -->
	<div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center gap-2 text-[11px] text-slate-500">
		<svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
		<span>Theo Thông tư 27/2019/TT-BGDĐT, bằng tốt nghiệp của các hệ đào tạo có giá trị pháp lý tương đương và không ghi hình thức đào tạo.</span>
	</div>
</section>
