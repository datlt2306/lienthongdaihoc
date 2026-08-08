<?php
/**
 * Single Program Template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$program_id = get_the_ID();
$school_id  = get_field( LTDH_META_SCHOOL_REL, $program_id );
$major_id   = get_field( LTDH_META_MAJOR_REL, $program_id );

// Retrieve school, major and global fields
$school_title = $school_id ? get_the_title( $school_id ) : '';
$major_title  = $major_id ? get_the_title( $major_id ) : '';

$tuition         = ltdh_get_program_tuition_display( $program_id );
$duration        = get_field( LTDH_META_DURATION, $program_id );
$requirements    = get_field( 'admission_requirements', $program_id );
$documents       = get_field( 'required_documents', $program_id );
$enrollment      = ltdh_get_program_admission_deadline_display( $program_id );
$quota           = get_field( 'quota', $program_id );

$program_hotline = ltdh_get_program_hotline( $program_id );
$benefits        = get_field( 'program_benefits', $program_id );
$opportunities   = get_field( 'career_opportunities', $program_id );
$why_choose      = get_field( 'why_choose_us', $program_id );
$faqs            = get_field( 'faq', $program_id );

$curriculum_raw  = get_field( 'curriculum_file', $program_id );
$curriculum_url  = '';
$curriculum_type = 'file';

if ( is_array( $curriculum_raw ) && ! empty( $curriculum_raw['url'] ) ) {
	$curriculum_url = $curriculum_raw['url'];
	$mime    = strtolower( $curriculum_raw['mime_type'] ?? '' );
	$type    = strtolower( $curriculum_raw['type'] ?? '' );
	$subtype = strtolower( $curriculum_raw['subtype'] ?? '' );
	if ( strpos( $mime, 'image' ) !== false || in_array( $type, array( 'image', 'png', 'jpg', 'jpeg', 'webp' ), true ) || in_array( $subtype, array( 'png', 'jpg', 'jpeg', 'webp' ), true ) ) {
		$curriculum_type = 'image';
	} elseif ( strpos( $mime, 'pdf' ) !== false || $subtype === 'pdf' || strtolower( pathinfo( $curriculum_url, PATHINFO_EXTENSION ) ) === 'pdf' ) {
		$curriculum_type = 'pdf';
	}
} elseif ( is_string( $curriculum_raw ) && ! empty( $curriculum_raw ) ) {
	$curriculum_url = $curriculum_raw;
	$ext = strtolower( pathinfo( $curriculum_url, PATHINFO_EXTENSION ) );
	if ( in_array( $ext, array( 'png', 'jpg', 'jpeg', 'webp', 'gif' ), true ) ) {
		$curriculum_type = 'image';
	} elseif ( $ext === 'pdf' ) {
		$curriculum_type = 'pdf';
	}
}

$global_zalo = ltdh_get_zalo_url();
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">
		

		<!-- CONTENT GRID -->
		<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-6 md:space-y-8">
				
				<!-- MOBILE ONLY SCHOOL MINI BAR (< 1024px) -->
				<?php if ( $school_id ) : ?>
					<div class="block lg:hidden bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
						<div class="flex items-center justify-between gap-3">
							<div class="flex items-center gap-2.5 min-w-0">
								<div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 p-0.5 shrink-0 flex items-center justify-center overflow-hidden">
									<?php if ( $school_logo_url ) : ?>
										<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_title ); ?>" class="max-w-full max-h-full object-contain">
									<?php else : ?>
										<span class="text-xs font-black text-[#00308b]"><?php echo esc_html( $initials ?: 'ĐH' ); ?></span>
									<?php endif; ?>
								</div>
								<div class="min-w-0">
									<span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block leading-none mb-0.5">Trường đào tạo</span>
									<h4 class="font-extrabold text-slate-800 text-xs truncate leading-tight"><?php echo esc_html( $school_title ); ?></h4>
								</div>
							</div>
							<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition-all shrink-0">
								Chi tiết →
							</a>
						</div>
					</div>
				<?php endif; ?>

				<!-- SECTION 2: PROGRAM OVERVIEW -->
				<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Tổng quan chương trình</h2>
					<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
						<?php the_content(); ?>
					</div>
					<?php if ( $benefits ) : ?>
						<div class="mt-6 bg-teal-50/50 p-4 rounded-lg border border-teal-100/50 mb-6">
							<h3 class="text-teal-800 font-bold text-base mb-2">Quyền lợi nổi bật</h3>
							<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
								<?php echo wp_kses_post( $benefits ); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php
					$learning_details = ltdh_get_program_learning_details( $program_id );
					?>
					<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-3.5 py-4 border-t border-slate-100">
						<div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center shadow-2xs">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Học phí</span>
							<span class="font-bold text-[#00308b] text-xs sm:text-sm leading-snug"><?php echo esc_html( $tuition ?: 'Liên hệ' ); ?></span>
						</div>
						<div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center shadow-2xs">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Thời gian học</span>
							<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $duration ?: '1.5 - 2 năm' ); ?></span>
						</div>
						<?php if ( $quota ) : ?>
							<div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center shadow-2xs">
								<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Chỉ tiêu</span>
								<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $quota ); ?> chỉ tiêu</span>
							</div>
						<?php endif; ?>
						<div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center shadow-2xs">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Cơ sở học</span>
							<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $learning_details['campus'] ); ?></span>
						</div>
						<div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center shadow-2xs">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Hình thức học</span>
							<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $learning_details['mode'] ); ?></span>
						</div>
						<div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center shadow-2xs">
							<span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Hạn hồ sơ</span>
							<span class="font-bold text-[#EA580C] text-xs sm:text-sm leading-snug"><?php echo esc_html( $enrollment ?: 'Đang nhận hồ sơ' ); ?></span>
						</div>
					</div>

					<?php 
					$batches_data = get_field( 'admission_batches', $program_id );
					if ( ! empty( $batches_data ) && is_array( $batches_data ) ) : 
						$has_release = false;
						$has_app     = false;
						$has_review  = false;
						$has_eval    = false;

						foreach ( $batches_data as $b_item ) {
							$rel = trim( $b_item['release_period'] ?? '' );
							$app = trim( $b_item['application_period'] ?? '' );
							$rev = trim( $b_item['review_time'] ?? '' );
							$ev1 = trim( $b_item['evaluation_time'] ?? '' );
							$ev2 = trim( $b_item['enrollment_time'] ?? '' );

							if ( $rel !== '' && $rel !== '-' ) {
								$has_release = true;
							}
							if ( $app !== '' && $app !== '-' ) {
								$has_app = true;
							}
							if ( $rev !== '' && $rev !== '-' ) {
								$has_review = true;
							}
							if ( ( $ev1 !== '' && $ev1 !== '-' ) || ( $ev2 !== '' && $ev2 !== '-' ) ) {
								$has_eval = true;
							}
						}
					?>
						<div class="mt-6 pt-6 border-t border-slate-100 space-y-3">
							<h3 class="font-bold text-slate-800 text-xs tracking-wider uppercase">Lịch trình các đợt tuyển sinh</h3>

							<!-- MOBILE TABBED CARD VIEW (< 768px) -->
							<div class="block md:hidden admission-batches-mobile">
								<!-- iOS Segmented Control Tab Navigation -->
								<div class="bg-slate-100 p-1 rounded-xl flex items-center justify-between gap-1 mb-3 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
									<?php foreach ( $batches_data as $b_idx => $b_item ) :
										$raw_name = $b_item['batch_name'] ?? '';
										// Shorten batch name for tabs: "Tuyển sinh Đợt 1" -> "Đợt 1", "Tuyển sinh Đợt 3 (Bổ sung)" -> "Đợt 3"
										$short_name = $raw_name;
										if ( preg_match( '/Đợt\s*\d+/i', $raw_name, $matches ) ) {
											$short_name = $matches[0];
										} elseif ( empty( $short_name ) ) {
											$short_name = 'Đợt ' . ( $b_idx + 1 );
										}

										$b_status  = $b_item['batch_status'] ?? '';
										$is_active = ( $b_idx === 0 );
										$tab_badge = '';
										if ( 'dang-nhan' === $b_status ) {
											$tab_badge = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse ml-0.5 inline-block"></span>';
										}
									?>
										<button type="button" 
											onclick="ltdhSwitchBatchTab(this, <?php echo (int) $b_idx; ?>)"
											class="ltdh-batch-tab-btn flex-1 py-2 px-3 text-xs rounded-lg transition-all text-center flex items-center justify-center gap-1 shrink-0 whitespace-nowrap outline-none focus:outline-none focus:ring-0 focus-visible:outline-none border-0 <?php echo $is_active ? 'bg-[#00308b] text-white font-bold shadow-xs' : 'bg-transparent text-slate-600 font-medium hover:text-slate-900'; ?>">
											<span><?php echo esc_html( $short_name ); ?></span>
											<?php echo $tab_badge; ?>
										</button>
									<?php endforeach; ?>
								</div>

								<!-- Tab Content Cards -->
								<?php foreach ( $batches_data as $b_idx => $b_item ) :
									$batch_name     = $b_item['batch_name'] ?? '';
									$clean_title    = preg_replace( '/^Tuyển sinh\s*/ui', '', $batch_name );
									$release_period = $b_item['release_period'] ?? '';
									$app_period     = $b_item['application_period'] ?? '';
									$review_time    = $b_item['review_time'] ?? '';
									$eval_time      = $b_item['evaluation_time'] ?? '';
									$enrol_time     = $b_item['enrollment_time'] ?? '';
									$status         = $b_item['batch_status'] ?? 'dang-nhan';
									
									$status_label = 'Đang nhận hồ sơ';
									$status_class = 'bg-emerald-50 text-emerald-700 border-0';
									if ( $status === 'sap-mo' ) {
										$status_label = 'Sắp mở';
										$status_class = 'bg-amber-50 text-amber-700 border-0';
									} elseif ( $status === 'da-dong' ) {
										$status_label = 'Đã đóng';
										$status_class = 'bg-slate-100 text-slate-600 border-0';
									}

									$eval_dates = array_filter([ $eval_time, $enrol_time ], function( $v ) {
										$v_clean = trim( $v );
										return $v_clean !== '' && $v_clean !== '-';
									});
									$eval_display = ! empty( $eval_dates ) ? implode(' | ', $eval_dates) : '';
									$is_hidden = ( $b_idx !== 0 );
								?>
									<div class="ltdh-batch-tab-card bg-white border border-slate-200/80 hover:border-slate-400 rounded-xl p-3.5 shadow-2xs hover:shadow-xs transition-all space-y-3 <?php echo $is_hidden ? 'hidden' : ''; ?>" data-batch-card="<?php echo (int) $b_idx; ?>">
										<div class="flex items-center justify-between border-b border-slate-100 pb-2.5 gap-2">
											<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $clean_title ); ?></span>
											<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border-0 shrink-0 <?php echo esc_attr( $status_class ); ?>">
												<?php echo esc_html( $status_label ); ?>
											</span>
										</div>

										<div class="space-y-3 pt-0.5">
											<?php if ( ! empty( $release_period ) && $release_period !== '-' ) : ?>
												<div class="flex items-start gap-2.5">
													<div class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Phát hành hồ sơ</div>
														<div class="text-xs font-semibold text-slate-800 mt-0.5"><?php echo esc_html( $release_period ); ?></div>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $app_period ) && $app_period !== '-' ) : ?>
												<div class="flex items-start gap-2.5">
													<div class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hạn nhận hồ sơ</div>
														<div class="text-xs font-semibold text-slate-800 mt-0.5"><?php echo esc_html( $app_period ); ?></div>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $review_time ) && $review_time !== '-' ) : ?>
												<div class="flex items-start gap-2.5">
													<div class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Thời gian ôn tập</div>
														<div class="text-xs font-semibold text-slate-800 mt-0.5"><?php echo esc_html( $review_time ); ?></div>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $eval_display ) ) : ?>
												<div class="flex items-start gap-2.5 pt-1.5 border-t border-slate-100">
													<div class="w-1.5 h-1.5 rounded-full bg-indigo-600 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Xét tuyển / Thi tuyển</div>
														<div class="text-xs font-bold text-[#00308b] mt-0.5"><?php echo esc_html( $eval_display ); ?></div>
													</div>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>

							<!-- DESKTOP BENTO GRID CARDS VIEW (>= 768px) -->
							<div class="hidden md:grid grid-cols-1 md:grid-cols-3 gap-3.5 sm:gap-4">
								<?php foreach ( $batches_data as $b_idx => $b_item ) :
									$batch_name     = $b_item['batch_name'] ?? '';
									$clean_title    = preg_replace( '/^Tuyển sinh\s*/ui', '', $batch_name );
									$release_period = $b_item['release_period'] ?? '';
									$app_period     = $b_item['application_period'] ?? '';
									$review_time    = $b_item['review_time'] ?? '';
									$eval_time      = $b_item['evaluation_time'] ?? '';
									$enrol_time     = $b_item['enrollment_time'] ?? '';
									$status         = $b_item['batch_status'] ?? 'dang-nhan';
									
									$status_label = 'Đang nhận hồ sơ';
									$status_class = 'bg-emerald-50 text-emerald-700 border-0';
									$card_style   = 'bg-white border border-slate-200/80 hover:border-slate-400 shadow-2xs hover:shadow-xs';
									
									if ( $status === 'dang-nhan' ) {
										$status_label = 'Đang nhận hồ sơ';
										$status_class = 'bg-emerald-600 text-white border-0 shadow-2xs';
										$card_style   = 'bg-emerald-50/15 border border-emerald-400/80 hover:border-emerald-500 shadow-xs';
									} elseif ( $status === 'sap-mo' ) {
										$status_label = 'Sắp mở';
										$status_class = 'bg-amber-50 text-amber-700 border-0';
									} elseif ( $status === 'da-dong' ) {
										$status_label = 'Đã đóng';
										$status_class = 'bg-slate-100 text-slate-500 border-0';
									}

									$eval_dates = array_filter([ $eval_time, $enrol_time ], function( $v ) {
										$v_clean = trim( $v );
										return $v_clean !== '' && $v_clean !== '-';
									});
									$eval_display = ! empty( $eval_dates ) ? implode(' | ', $eval_dates) : '';
								?>
									<div class="rounded-xl p-3.5 sm:p-4 transition-all duration-200 flex flex-col justify-between space-y-3 <?php echo esc_attr( $card_style ); ?>">
										<!-- Card Header -->
										<div class="flex items-center justify-between pb-2.5 gap-2 border-b border-slate-100/80">
											<span class="font-bold text-slate-800 text-xs sm:text-sm leading-snug"><?php echo esc_html( $clean_title ); ?></span>
											<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 <?php echo esc_attr( $status_class ); ?>">
												<?php echo esc_html( $status_label ); ?>
											</span>
										</div>

										<!-- Card Content Timeline -->
										<div class="space-y-3 flex-1">
											<?php if ( ! empty( $release_period ) && $release_period !== '-' ) : ?>
												<div class="flex items-start gap-x-2.5">
													<div class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Phát hành hồ sơ</div>
														<div class="text-xs font-semibold text-slate-800 mt-0.5"><?php echo esc_html( $release_period ); ?></div>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $app_period ) && $app_period !== '-' ) : ?>
												<div class="flex items-start gap-x-2.5">
													<div class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hạn nhận hồ sơ</div>
														<div class="text-xs font-semibold text-slate-800 mt-0.5"><?php echo esc_html( $app_period ); ?></div>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $review_time ) && $review_time !== '-' ) : ?>
												<div class="flex items-start gap-x-2.5">
													<div class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Thời gian ôn tập</div>
														<div class="text-xs font-semibold text-slate-800 mt-0.5"><?php echo esc_html( $review_time ); ?></div>
													</div>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $eval_display ) ) : ?>
												<div class="flex items-start gap-x-2.5 pt-1.5">
													<div class="w-1.5 h-1.5 rounded-full bg-indigo-600 mt-1.5 shrink-0"></div>
													<div class="flex-1 min-w-0">
														<div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Xét tuyển / Thi tuyển</div>
														<div class="text-xs font-bold text-[#00308b] mt-0.5"><?php echo esc_html( $eval_display ); ?></div>
													</div>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>

						<script>
						function ltdhSwitchBatchTab(btn, idx) {
							const container = btn.closest('.admission-batches-mobile');
							if (!container) return;
							const btns = container.querySelectorAll('.ltdh-batch-tab-btn');
							const cards = container.querySelectorAll('.ltdh-batch-tab-card');
							
							const activeCls   = ['bg-[#00308b]', 'text-white', 'font-bold', 'shadow-xs'];
							const inactiveCls = ['bg-transparent', 'text-slate-600', 'font-medium'];

							btns.forEach((b, i) => {
								if (i === idx) {
									inactiveCls.forEach(c => b.classList.remove(c));
									activeCls.forEach(c => b.classList.add(c));
								} else {
									activeCls.forEach(c => b.classList.remove(c));
									inactiveCls.forEach(c => b.classList.add(c));
								}
							});

							cards.forEach((c, i) => {
								if (i === idx) {
									c.classList.remove('hidden');
								} else {
									c.classList.add('hidden');
								}
							});
						}
						</script>
					<?php endif; ?>

				</section>



				<!-- SECTION 4: MAJOR INFORMATION -->
				<?php if ( $major_id ) : ?>
					<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Thông tin ngành học</h2>
						<h3 class="font-bold text-base md:text-lg text-slate-900 mb-2"><?php echo esc_html( $major_title ); ?> (Mã ngành: <?php echo esc_html( get_field( 'major_code', $major_id ) ); ?>)</h3>
						<div class="prose prose-slate max-w-none text-sm text-slate-600 mb-4">
							<?php echo wp_kses_post( get_post_field( 'post_content', $major_id ) ); ?>
						</div>
						<?php if ( $opportunities ) : ?>
							<h4 class="font-semibold text-slate-800 text-sm mt-4 mb-1">Cơ hội nghề nghiệp</h4>
							<div class="prose prose-slate max-w-none text-sm text-slate-500">
								<?php echo wp_kses_post( $opportunities ); ?>
							</div>
						<?php endif; ?>
						<div class="mt-4 pt-4 border-t border-slate-100">
							<a href="<?php echo esc_url( get_permalink( $major_id ) ); ?>" class="text-brand-primary font-semibold text-sm hover:underline">
								Tìm hiểu thêm định hướng ngành <?php echo esc_html( $major_title ); ?> →
							</a>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION 5: ADMISSION REQUIREMENTS -->
				<?php if ( $requirements ) : ?>
					<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Điều kiện xét tuyển</h2>
						<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
							<?php echo wp_kses_post( $requirements ); ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION 6: TUITION & SECTION 7: DURATION -->
				<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Học phí & Thời gian học</h2>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
						<div class="bg-slate-50 p-3.5 md:p-4 rounded-lg flex flex-col justify-between">
							<div>
								<h3 class="font-extrabold text-sm md:text-base text-slate-800 mb-2.5">Học phí chi tiết</h3>
								<p class="text-slate-800 text-sm font-semibold mb-3">
									<?php echo esc_html( $tuition ?: 'Liên hệ ban tuyển sinh để nhận biểu phí và chính sách đóng học phí theo đợt.' ); ?>
								</p>
								
								<?php 
								$tuition_amount   = get_field( 'tuition_amount', $program_id );
								$tuition_unit     = get_field( 'tuition_unit', $program_id );
								$total_credits    = get_field( 'tuition_total_credits', $program_id );
								$increase_roadmap = get_field( 'tuition_increase_roadmap', $program_id );
								
								if ( $tuition_amount && $tuition_unit === 'tin-chi' && $total_credits ) : 
									$estimated_total = floatval( $tuition_amount ) * intval( $total_credits );
								?>
									<div class="mt-3 pt-3 border-t border-slate-200/60 text-xs md:text-sm space-y-1.5 text-slate-600">
										<div class="flex justify-between">
											<span>Tổng số tín chỉ toàn khóa:</span>
											<span class="font-bold text-slate-800"><?php echo esc_html( $total_credits ); ?> tín chỉ</span>
										</div>
										<div class="flex justify-between">
											<span>Tổng học phí tạm tính:</span>
											<span class="font-bold text-brand-primary"><?php echo number_format( $estimated_total, 0, ',', '.' ); ?> đ</span>
										</div>
									</div>
								<?php endif; ?>
							</div>

							<?php if ( $increase_roadmap ) : ?>
								<div class="mt-4 pt-3 border-t border-slate-200/60 text-[11px] text-slate-400 leading-normal">
									<span class="font-bold text-slate-500 block mb-0.5">Lộ trình học phí / Ghi chú:</span>
									<?php echo esc_html( $increase_roadmap ); ?>
								</div>
							<?php endif; ?>
						</div>

						<div class="bg-slate-50 p-3.5 md:p-4 rounded-lg flex flex-col justify-between">
							<div>
								<h3 class="font-extrabold text-sm md:text-base text-slate-800 mb-2.5">Thời gian học tập</h3>
								<p class="text-slate-800 text-sm font-semibold mb-2">Lộ trình chuẩn: <?php echo esc_html( $duration ?: '1.5 - 2 năm' ); ?></p>
								<p class="text-slate-500 text-xs md:text-sm leading-relaxed">
									Thời gian đào tạo thực tế có thể được rút ngắn hoặc kéo dài tùy thuộc vào số lượng học phần học viên được miễn giảm (chuyển đổi tín chỉ từ văn bằng trước đó) hoặc tiến độ đăng ký học phần học tập.
								</p>
							</div>
							<div class="mt-4 pt-3 border-t border-slate-200/60 text-[11px] text-slate-400 leading-normal">
								<span class="font-bold text-slate-500 block mb-0.5">Lưu ý:</span>
								Thời gian đào tạo thực tế sẽ do hội đồng xét miễn giảm môn quyết định dựa trên bảng điểm tốt nghiệp bậc học trước đó của học viên.
							</div>
						</div>
					</div>
				</section>

				<!-- SECTION 7.5: CURRICULUM ROADMAP FILE/IMAGE -->
				<?php if ( $curriculum_url ) : ?>
					<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6" id="lo-trinh-hoc">
						<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3 mb-4">
							<div>
								<h2 class="text-lg md:text-2xl font-bold text-slate-900">Lộ trình học & Khung chương trình</h2>
								<p class="text-xs md:text-sm text-slate-500 mt-0.5">Khung chương trình đào tạo chính thức áp dụng cho khóa học này</p>
							</div>
							<?php if ( $curriculum_type === 'image' || $curriculum_type === 'pdf' ) : ?>
								<a href="<?php echo esc_url( $curriculum_url ); ?>" download target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#00308b] hover:text-[#002266] bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-all shrink-0 self-start sm:self-auto">
									<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
									<span>Tải file gốc</span>
								</a>
							<?php endif; ?>
						</div>

						<?php if ( $curriculum_type === 'image' ) : ?>
							<!-- Image Viewer with Lightbox Zoom -->
							<div class="relative group bg-slate-900/5 rounded-xl border border-slate-200/80 overflow-hidden p-2 sm:p-3 text-center">
								<a href="<?php echo esc_url( $curriculum_url ); ?>" target="_blank" class="inline-block relative overflow-hidden rounded-lg cursor-zoom-in group" title="Click để xem ảnh kích thước chuẩn">
									<img src="<?php echo esc_url( $curriculum_url ); ?>" alt="Lộ trình đào tạo <?php echo esc_attr( get_the_title() ); ?>" class="max-w-full h-auto mx-auto rounded-lg shadow-2xs group-hover:scale-[1.01] transition-transform duration-300">
									<div class="absolute inset-0 bg-slate-900/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
										<span class="bg-white/95 text-slate-900 text-xs font-bold px-3 py-2 rounded-lg shadow-lg flex items-center gap-2">
											<svg class="w-4 h-4 text-[#00308b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
											Xem ảnh toàn màn hình
										</span>
									</div>
								</a>
								<p class="text-[11px] text-slate-400 mt-2 flex items-center justify-center gap-1">
									<span>🔍</span> Click vào ảnh để phóng to xem chi tiết mã môn học và số tín chỉ
								</p>
							</div>
						<?php else : ?>
							<!-- PDF or File Download Box -->
							<div class="bg-slate-50 p-4 sm:p-5 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
								<div class="flex items-center gap-3.5 min-w-0">
									<div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center font-black text-xl shrink-0 shadow-2xs">
										PDF
									</div>
									<div class="min-w-0">
										<h4 class="font-bold text-slate-900 text-sm sm:text-base mb-1 truncate">Khung chương trình đào tạo chi tiết</h4>
										<p class="text-xs text-slate-500">Bản PDF chính thức từ nhà trường liệt kê lộ trình các học kỳ và danh sách môn học</p>
									</div>
								</div>
								<a href="<?php echo esc_url( $curriculum_url ); ?>" download target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#00308b] hover:bg-[#002266] text-white text-xs font-bold rounded-lg shadow-xs hover:shadow-sm transition-all shrink-0">
									<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
									<span>Tải Khung Chương Trình (PDF)</span>
								</a>
							</div>
						<?php endif; ?>

						<!-- CTA: Thẩm định miễn môn -->
						<div class="mt-4 p-4 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/70 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
							<div class="flex items-center gap-3">
								<span class="text-2xl shrink-0">💡</span>
								<div>
									<h4 class="font-extrabold text-amber-950 text-xs sm:text-sm">Bạn chưa rõ mình được miễn giảm những môn nào?</h4>
									<p class="text-[11px] sm:text-xs text-amber-800 mt-0.5">Gửi ảnh bảng điểm tốt nghiệp CĐ/ĐH cũ của bạn, ban tuyển sinh sẽ đối chiếu lộ trình và tư vấn miễn môn cho bạn trong 15 phút.</p>
								</div>
							</div>
							<a href="#dang-ky" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg shadow-2xs transition-all shrink-0">
								Thẩm định miễn môn
							</a>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION 8: DOCUMENTS REQUIRED -->
				<?php 
				$admission_form = get_field( 'admission_form_file', $program_id );
				$form_url = '';
				if ( is_array( $admission_form ) && ! empty( $admission_form['url'] ) ) {
					$form_url = $admission_form['url'];
				} elseif ( is_string( $admission_form ) && ! empty( $admission_form ) ) {
					$form_url = $admission_form;
				}
				?>
				<?php if ( $documents || $form_url ) : ?>
					<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Hồ sơ xét tuyển cần thiết</h2>
						<?php if ( $documents ) : ?>
							<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
								<?php echo wp_kses_post( $documents ); ?>
							</div>
						<?php endif; ?>

						<?php if ( $form_url ) : ?>
							<div class="mt-5 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-blue-50/60 p-4 rounded-xl border border-blue-100/80">
								<div class="flex items-center gap-3">
									<div class="w-10 h-10 rounded-xl bg-[#00308b] text-white flex items-center justify-center font-bold text-lg shrink-0 shadow-xs">
										📄
									</div>
									<div>
										<h4 class="font-bold text-slate-900 text-sm mb-0.5">Tải mẫu phiếu đăng ký tuyển sinh</h4>
										<p class="text-xs text-slate-500">Mẫu phiếu đăng ký tuyển sinh chính thức để in và làm hồ sơ</p>
									</div>
								</div>
								<a href="<?php echo esc_url( $form_url ); ?>" download target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4.5 py-2.5 bg-[#00308b] hover:bg-[#002266] text-white text-xs font-bold rounded-lg shadow-xs hover:shadow-sm transition-all shrink-0">
									<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
									<span>Tải Phiếu Tuyển Sinh</span>
								</a>
							</div>
						<?php endif; ?>
					</section>
				<?php endif; ?>

				<!-- SECTION 9: FAQ -->
				<?php if ( ! empty( $faqs ) ) : ?>
					<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Câu hỏi thường gặp</h2>
						<div class="space-y-4">
							<?php foreach ( $faqs as $index => $item ) : ?>
								<div class="border-b border-slate-100 pb-4 last:border-0 last:pb-0">
									<h4 class="font-semibold text-slate-800 text-base mb-1.5 flex items-start gap-2">
										<span class="bg-teal-100 text-teal-800 text-sm px-1.5 py-0.5 rounded-lg font-black">Q</span>
										<span><?php echo esc_html( $item['question'] ); ?></span>
									</h4>
									<p class="text-slate-600 text-sm pl-7 leading-relaxed"><?php echo esc_html( $item['answer'] ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION 10: RELATED PROGRAMS -->
				<?php
				$related_query = new WP_Query( [
					'post_type'      => 'program',
					'posts_per_page' => 3,
					'post__not_in'   => [ $program_id ],
					'meta_query'     => [
						'relation' => 'OR',
						[
							'key'     => LTDH_META_MAJOR_REL,
							'value'   => $major_id,
							'compare' => '=',
						],
						[
							'key'     => LTDH_META_SCHOOL_REL,
							'value'   => $school_id,
							'compare' => '=',
						]
					]
				] );

				if ( $related_query->have_posts() ) :
				?>
					<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Chương trình liên quan</h2>
						<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
							<?php 
							while ( $related_query->have_posts() ) : 
								$related_query->the_post();
								$rel_school_id = get_field( LTDH_META_SCHOOL_REL );
								$rel_school = $rel_school_id ? get_the_title( $rel_school_id ) : '';
							?>
								<a href="<?php the_permalink(); ?>" class="group block border border-slate-100 rounded-lg p-4 hover:border-brand-primary hover:shadow-md transition-all bg-white">
									<span class="text-sm text-slate-400 block mb-1 font-medium"><?php echo esc_html( $rel_school ); ?></span>
									<h4 class="font-bold text-slate-800 text-sm group-hover:text-brand-primary transition-colors line-clamp-2"><?php the_title(); ?></h4>
									<div class="mt-3 flex justify-between items-center text-sm text-slate-500 border-t border-slate-50 pt-2">
										<span>Học phí: <?php echo esc_html( ltdh_get_program_tuition_display( get_the_ID() ) ); ?></span>
									</div>
								</a>
							<?php 
							endwhile; 
							wp_reset_postdata();
							?>
						</div>
					</section>
				<?php endif; ?>

			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<div class="sticky top-24 space-y-6">
					
					<!-- SCHOOL INFO CARD (Desktop Only >= 1024px) -->
					<?php if ( $school_id ) : 
						$school_logo_id = get_field( 'logo', $school_id );
						$school_logo_url = $school_logo_id ? wp_get_attachment_image_url( $school_logo_id, 'thumbnail' ) : '';
						if ( ! $school_logo_url ) {
							$school_logo_url = get_the_post_thumbnail_url( $school_id, 'thumbnail' );
						}
						$school_cover_url = get_the_post_thumbnail_url( $school_id, 'medium' );
						$school_address = get_post_meta( $school_id, 'address', true ) ?: get_field( 'address', $school_id ) ?: 'Việt Nam';
						$school_web = get_field( 'website', $school_id );
						
						// Get initials for typographic logo fallback (e.g. UTC, ĐHGTVT)
						$words = explode( ' ', $school_title );
						$initials = '';
						foreach ( array_slice( $words, -3 ) as $w ) {
							$initials .= mb_substr( $w, 0, 1 );
						}
						$initials = mb_strtoupper( $initials );
					?>
						<div class="hidden lg:block bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs hover:shadow-sm transition-all">
							<!-- Banner Cover -->
							<div class="h-24 bg-gradient-to-r from-[#00308b] to-[#001a4d] bg-cover bg-center relative" <?php echo $school_cover_url ? 'style="background-image: url(\'' . esc_url( $school_cover_url ) . '\');"' : ''; ?>>
								<div class="absolute inset-0 bg-blue-950/20"></div>
							</div>
							
							<!-- Overlapping Logo Wrapper -->
							<div class="relative flex justify-center -mt-9 mb-3">
								<div class="w-18 h-18 bg-white p-1 rounded-xl shadow-md border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
									<?php if ( $school_logo_url ) : ?>
										<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_title ); ?>" class="max-w-full max-h-full object-contain" loading="lazy">
									<?php else : ?>
										<div class="w-full h-full bg-[#00308b] text-white flex items-center justify-center font-black text-base rounded-lg uppercase tracking-wider">
											<?php echo esc_html( $initials ?: 'ĐH' ); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
							
							<!-- School Info details -->
							<div class="px-5 pb-5 text-center">
								<h4 class="font-extrabold text-slate-800 text-sm leading-snug uppercase tracking-tight mb-2">
									<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="hover:text-[#00308b] transition-colors"><?php echo esc_html( $school_title ); ?></a>
								</h4>
								
								<?php if ( $school_web ) : ?>
									<p class="text-xs text-slate-500 mb-2 font-medium">
										Website: <a href="<?php echo esc_url( $school_web ); ?>" target="_blank" rel="noopener noreferrer" class="text-[#00308b] hover:underline"><?php echo esc_html( $school_web ); ?></a>
									</p>
								<?php endif; ?>

								<div class="flex items-start justify-center gap-1.5 text-xs text-slate-500 font-medium max-w-xs mx-auto mb-3.5">
									<span class="text-[#00308b] shrink-0 mt-0.5">📍</span>
									<span class="text-left leading-relaxed">
										Địa chỉ: <?php echo esc_html( $school_address ); ?>
										<a href="<?php echo esc_url( 'https://www.google.com/maps/search/?api=1&query=' . urlencode( $school_title . ' ' . $school_address ) ); ?>" target="_blank" rel="noopener noreferrer" class="text-[#00308b] font-bold hover:underline ml-0.5 inline-block">(Xem bản đồ)</a>
									</span>
								</div>
								<div class="border-t border-slate-100 pt-3">
									<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="text-[#00308b] font-bold text-xs sm:text-sm hover:underline flex items-center justify-center gap-1">
										<span>Xem chi tiết trường</span> <span>→</span>
									</a>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<!-- SECTION 11: CONSULTATION FORM (Sidebar Form - Available on Mobile & Desktop) -->
					<section id="register" class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2">Đăng ký tư vấn miễn phí</h3>
						<p class="text-sm text-slate-500 mb-4">Hãy để lại thông tin, ban tư vấn tuyển sinh sẽ liên hệ và giải đáp lộ trình cụ thể cho bạn trong vòng 15 phút.</p>
						
						<?php 
						$form_context = [
							'current_program_id' => $program_id,
							'current_school_id'  => $school_id,
							'current_major_id'   => $major_id,
							'referral_source'    => get_permalink(),
						];
						ltdh_render_consultation_form( $form_context );
						?>
					</section>
					


					<!-- RELATED NEWS & ANNOUNCEMENTS (Sidebar - Desktop Only) -->
					<?php
					$related_news_query = new WP_Query( [
						'post_type'      => [ 'post', 'guide' ],
						'posts_per_page' => 6,
						'post_status'    => 'publish',
						'meta_query'     => [
							[
								'key'     => 'related_programs',
								'value'   => '"' . $program_id . '"',
								'compare' => 'LIKE',
							],
						],
					] );

					if ( $related_news_query->have_posts() ) :
						$has_more = ( $related_news_query->post_count > 5 );
					?>
						<section class="hidden lg:block bg-white rounded-lg shadow-sm border border-slate-200 p-4 md:p-5">
							<h3 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-3">Tin tức & Thông báo liên quan</h3>
							<div class="space-y-3.5">
								<?php
								$news_counter = 0;
								while ( $related_news_query->have_posts() ) :
									$related_news_query->the_post();
									if ( $news_counter >= 5 ) {
										continue;
									}
									$news_thumb = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
									$news_counter++;
								?>
									<div class="flex gap-3 items-start pb-3 border-b border-slate-100 last:border-b-0 last:pb-0">
										<?php if ( $news_thumb ) : ?>
											<a href="<?php the_permalink(); ?>" class="shrink-0">
												<img src="<?php echo esc_url( $news_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-12 h-12 object-cover rounded border border-slate-100" loading="lazy">
											</a>
										<?php else : ?>
											<div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded flex items-center justify-center shrink-0">
												<span class="text-lg">📰</span>
											</div>
										<?php endif; ?>
										
										<div class="flex-1 min-w-0">
											<h4 class="font-bold text-slate-800 text-sm hover:text-brand-primary transition-colors line-clamp-2 leading-snug">
												<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
											</h4>
											<p class="text-xs text-slate-400 mt-0.5">📅 <?php echo get_the_date(); ?></p>
										</div>
									</div>
								<?php
								endwhile;
								wp_reset_postdata();
								?>
							</div>

							<?php if ( $has_more ) : ?>
								<div class="mt-4 pt-3 border-t border-slate-100">
									<a href="<?php echo esc_url( home_url( '/tin-tuc/?chuong-trinh=' . $program_id ) ); ?>" class="w-full text-center bg-slate-50 border border-slate-200 text-slate-700 py-2.5 rounded-lg font-bold text-sm hover:bg-slate-100 transition-all flex items-center justify-center gap-1.5 min-h-[38px]">
										<span>Xem thêm tin tức</span>
										<span>→</span>
									</a>
								</div>
							<?php endif; ?>
						</section>
					<?php
					endif;
					?>

					<!-- SECTION 13: PHONE CTA & SECTION 14: ZALO CTA sidebar cards (Desktop Only) -->
					<div class="hidden lg:block bg-brand-accent/5 border border-brand-primary/10 rounded-lg p-6 text-center">
						<span class="text-sm text-brand-primary font-bold uppercase tracking-wider block mb-1">Cần hỗ trợ trực tiếp?</span>
						<h4 class="font-display font-black text-2xl text-slate-800 mb-4"><?php echo esc_html( $program_hotline ); ?></h4>
						<div class="flex gap-2">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $program_hotline ) ); ?>" class="flex-1 bg-brand-accent text-white py-3.5 rounded-lg font-semibold text-sm hover:bg-[#e06e00] transition-all min-h-[44px] flex items-center justify-center">Gọi Điện</a>
							<a href="<?php echo esc_url( $global_zalo ); ?>" class="flex-1 bg-white border border-brand-primary text-brand-primary py-3.5 rounded-lg font-semibold text-sm hover:bg-brand-accent/5 transition-all min-h-[44px] flex items-center justify-center">Chat Zalo</a>
						</div>
					</div>

					<!-- COMPARE BUTTON ONLY (Desktop Only) -->
					<?php
					$types = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
					$type_slug = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->slug : '';
					$major_rel_id_raw = get_field( LTDH_META_MAJOR_REL, $program_id );
					$major_rel_id = 0;
					if ( is_array( $major_rel_id_raw ) ) {
						$major_rel_id = ! empty( $major_rel_id_raw ) ? ( is_object( $major_rel_id_raw[0] ) ? $major_rel_id_raw[0]->ID : $major_rel_id_raw[0] ) : 0;
					} elseif ( is_object( $major_rel_id_raw ) ) {
						$major_rel_id = $major_rel_id_raw->ID;
					} elseif ( $major_rel_id_raw ) {
						$major_rel_id = intval( $major_rel_id_raw );
					}
					$major_slug = $major_rel_id ? get_post_field( 'post_name', $major_rel_id ) : '';
					?>
					<button type="button"
							class="hidden lg:flex w-full text-center bg-white border border-slate-200 text-slate-700 py-3.5 rounded-xl font-bold shadow-xs hover:bg-slate-50 transition-all ltdh-compare-single-btn text-sm flex items-center justify-center gap-2 mt-4 min-h-[44px]"
							data-compare-type="program" data-compare-id="<?php echo esc_attr( $program_id ); ?>"
							data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
							data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $program_id ) ); ?>"
							data-compare-he="<?php echo esc_attr( $type_slug ); ?>"
							data-compare-nganh="<?php echo esc_attr( $major_slug ); ?>">
						<span>📊</span> <span>Thêm vào so sánh</span>
					</button>

				</div>
			</div>
		</div>

	</div>
</main>

<!-- SECTION 12: STICKY CTA (Mobile Bottom Sticky Bar) -->
<div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/80 shadow-[0_-4px_16px_rgba(0,0,0,0.06)] py-2.5 px-4 flex items-center justify-between lg:hidden">
	<div class="flex-1 mr-3 min-w-0">
		<span class="text-[10px] text-slate-400 block font-bold uppercase tracking-wider truncate mb-0.5"><?php echo esc_html( $school_title ); ?></span>
		<h4 class="font-bold text-slate-800 text-xs sm:text-sm truncate leading-tight"><?php the_title(); ?></h4>
	</div>
	<div class="flex items-center gap-2 shrink-0">
		<?php if ( ! empty( $form_url ) ) : ?>
			<a href="<?php echo esc_url( $form_url ); ?>" download target="_blank" rel="noopener noreferrer" class="bg-slate-100 hover:bg-slate-200 text-slate-700 p-2.5 rounded-lg text-xs font-bold transition-all shrink-0 flex items-center justify-center border border-slate-200/80 min-h-[40px]" title="Tải phiếu tuyển sinh">
				📄 <span class="hidden sm:inline ml-1">Tải phiếu</span>
			</a>
		<?php endif; ?>
		<a href="#register" class="bg-[#00308b] text-white px-4 py-2.5 rounded-lg text-xs font-extrabold shadow-sm hover:bg-[#002266] transition-all flex items-center justify-center shrink-0 min-h-[40px]">
			Đăng Ký Học
		</a>
	</div>
</div>

<?php
get_footer();
