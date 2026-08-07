<?php
/**
 * Single Major Template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$major_id   = get_the_ID();
$major_code = get_field( 'major_code', $major_id );
$career     = get_field( 'career_opportunities', $major_id );

// Retrieve pre-calculated list of programs matching this major
$offered_program_ids = get_post_meta( $major_id, LTDH_META_OFFERED_PROGRAMS, true );

$global_zalo = ltdh_get_zalo_url();
$hotline = ltdh_get_hotline();
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
		
		<!-- HERO SECTION -->
		<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-6 md:p-8 mb-8">
			<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
				<div class="space-y-2">
					<span class="inline-block bg-teal-50 text-brand-primary text-sm font-bold px-3 py-1 rounded-lg uppercase tracking-wider">
						Thông tin Ngành học
					</span>
					<h1 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight">
						Ngành <?php the_title(); ?>
					</h1>
					<p class="text-slate-500 text-sm font-medium">Mã ngành: <?php echo esc_html( $major_code ?: 'Đang cập nhật' ); ?></p>
				</div>
				<div class="flex flex-col gap-2 w-full md:w-auto">
					<a href="#register" class="w-full md:w-auto bg-brand-accent text-white text-center px-6 py-3 rounded-lg font-bold shadow-md hover:bg-[#e06e00] transition-all text-sm min-h-[44px] flex items-center justify-center">
						Tư Vấn Hướng Nghiệp
					</a>
				</div>
			</div>
		</section>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-6 md:space-y-8">
				
				<!-- OVERVIEW -->
				<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Tổng quan về ngành</h2>
					<div class="relative">
						<div id="major-intro-content" class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base overflow-hidden transition-all duration-500 max-h-[350px] relative">
							<?php the_content(); ?>
							<!-- Gradient Overlay for fade-out effect -->
							<div id="major-intro-overlay" class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white to-transparent pointer-events-none transition-opacity duration-300"></div>
						</div>
						
						<div id="major-intro-btn-container" class="hidden justify-center mt-4 border-t border-slate-100 pt-4">
							<button id="major-intro-toggle" class="flex items-center gap-2 px-5 py-2 rounded-full border border-slate-200 bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 hover:text-brand-accent shadow-sm transition-all focus:outline-none">
								<span id="major-intro-btn-text">Xem thêm tổng quan</span>
								<svg id="major-intro-btn-icon" class="w-4 h-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
								</svg>
							</button>
						</div>
					</div>

					<script>
					(function() {
						var content = document.getElementById('major-intro-content');
						var overlay = document.getElementById('major-intro-overlay');
						var container = document.getElementById('major-intro-btn-container');
						var button = document.getElementById('major-intro-toggle');
						var btnText = document.getElementById('major-intro-btn-text');
						var btnIcon = document.getElementById('major-intro-btn-icon');
						
						if (!content || !overlay || !container || !button) return;
						
						var limit = 350;
						var isExpanded = false;
						
						function checkHeight() {
							if (isExpanded) return;
							if (content.scrollHeight > limit + 30) {
								container.classList.remove('hidden');
								container.classList.add('flex');
								content.style.maxHeight = limit + 'px';
								overlay.style.display = 'block';
							} else {
								container.classList.remove('flex');
								container.classList.add('hidden');
								content.style.maxHeight = 'none';
								overlay.style.display = 'none';
							}
						}
						
						checkHeight();
						window.addEventListener('load', checkHeight);
						
						button.addEventListener('click', function() {
							isExpanded = !isExpanded;
							if (isExpanded) {
								content.style.maxHeight = content.scrollHeight + 'px';
								overlay.classList.add('opacity-0');
								setTimeout(function() {
									if (isExpanded) {
										content.style.maxHeight = 'none';
									}
								}, 500);
								btnText.textContent = 'Thu gọn';
								btnIcon.classList.add('rotate-180');
							} else {
								content.style.maxHeight = content.scrollHeight + 'px';
								content.offsetHeight; // Force reflow
								content.style.maxHeight = limit + 'px';
								overlay.classList.remove('opacity-0');
								btnText.textContent = 'Xem thêm tổng quan';
								btnIcon.classList.remove('rotate-180');
								
								var rect = content.getBoundingClientRect();
								if (rect.top < 0) {
									window.scrollTo({
										top: window.pageYOffset + rect.top - 20,
										behavior: 'smooth'
									});
								}
							}
						});
					})();
					</script>
				</section>

				<!-- CAREER OPPORTUNITIES -->
				<?php if ( $career ) : ?>
					<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 md:pb-4 mb-4">Cơ hội nghề nghiệp & Định hướng</h2>
						<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
							<?php echo wp_kses_post( $career ); ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- PROGRAMS FOR THIS MAJOR -->
				<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 md:pb-4 mb-4">Các trường tuyển sinh ngành <?php the_title(); ?></h2>
					
					<?php
					$meta_status_filter = [
						'relation' => 'OR',
						[
							'key'     => LTDH_META_ADMISSION_STATUS,
							'value'   => 'tam-ngung',
							'compare' => '!=',
						],
						[
							'key'     => LTDH_META_ADMISSION_STATUS,
							'compare' => 'NOT EXISTS',
						],
					];

					if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
						$programs_query = new WP_Query( [
							'post_type' => 'program',
							'post__in'  => $offered_program_ids,
							'post_status' => 'publish',
							'meta_query' => [
								'relation' => 'AND',
								$meta_status_filter,
							],
						] );
					} else {
						// Fallback logic
						$programs_query = new WP_Query( [
							'post_type' => 'program',
							'meta_query' => [
								'relation' => 'AND',
								[
									'key' => LTDH_META_MAJOR_REL,
									'value' => $major_id,
									'compare' => '='
								],
								$meta_status_filter,
							],
							'posts_per_page' => 10
						] );
					}

					$schools_data = [];
					if ( $programs_query->have_posts() ) {
						while ( $programs_query->have_posts() ) {
							$programs_query->the_post();
							$prog_id = get_the_ID();
							$school_rel_id = get_field( LTDH_META_SCHOOL_REL, $prog_id );
							$school_key = $school_rel_id ? $school_rel_id : 'no_school_' . $prog_id;

							if ( ! isset( $schools_data[ $school_key ] ) ) {
								$school_name = $school_rel_id ? get_the_title( $school_rel_id ) : 'Mời tư vấn';
								$school_thumb = $school_rel_id ? get_the_post_thumbnail_url( $school_rel_id, 'medium' ) : '';
								if ( ! $school_thumb ) {
									$school_thumb = ltdh_get_fallback_image( 'school' );
								}
								$school_code = $school_rel_id ? get_field( 'school_code', $school_rel_id ) : '';
								$school_address = $school_rel_id ? get_field( 'address', $school_rel_id ) : '';

								$schools_data[ $school_key ] = [
									'id'       => $school_rel_id,
									'name'     => $school_name,
									'thumb'    => $school_thumb,
									'code'     => $school_code,
									'address'  => $school_address,
									'programs' => [],
								];
							}

							$status = get_post_meta( $prog_id, LTDH_META_ADMISSION_STATUS, true ) ?: 'tuyen-sinh';
							$types = wp_get_post_terms( $prog_id, LTDH_TAX_TRAINING_TYPE );
							$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';
							$tuition_fee = ltdh_get_program_tuition_display( $prog_id );
							$duration = get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm';
							$permalink = get_permalink( $prog_id );

							// Determine badge classes based on training type name
							$badge_class = 'bg-orange-50 text-orange-600 border border-orange-100';
							if ( $type_name ) {
								$type_name_lower = mb_strtolower( trim( $type_name ), 'UTF-8' );
								if ( false !== strpos( $type_name_lower, 'chính quy' ) ) {
									$badge_class = 'bg-blue-50 text-blue-600 border border-blue-100';
								} elseif ( false !== strpos( $type_name_lower, 'từ xa' ) ) {
									$badge_class = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
								} elseif ( false !== strpos( $type_name_lower, 'vừa học vừa làm' ) || false !== strpos( $type_name_lower, 'vừa làm vừa học' ) || false !== strpos( $type_name_lower, 'liên thông' ) || false !== strpos( $type_name_lower, 'văn bằng 2' ) ) {
									$badge_class = 'bg-amber-50 text-amber-600 border border-amber-100';
								}
							}

							$academic_year = get_field( 'tuition_academic_year', $prog_id ) ?: '2025 - 2026';

							$schools_data[ $school_key ]['programs'][] = [
								'id'            => $prog_id,
								'status'        => $status,
								'type_name'     => $type_name,
								'badge_class'   => $badge_class,
								'tuition_fee'   => $tuition_fee,
								'academic_year' => $academic_year,
								'duration'      => $duration,
								'permalink'     => $permalink,
							];
						}
						wp_reset_postdata();
					}

					if ( ! empty( $schools_data ) ) :
						echo '<div class="space-y-6">';
						foreach ( $schools_data as $school ) :
							if ( count( $school['programs'] ) > 1 ) :
								// Grouped nested layout for schools with 2+ programs
								?>
								<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all p-4 md:p-5 flex flex-col gap-4">
									<!-- School Primary Info Header -->
									<div class="flex items-start sm:items-center gap-4">
										<div class="h-16 w-16 bg-slate-200 bg-cover bg-center rounded-lg shrink-0 border border-slate-100" style="background-image: url('<?php echo esc_url( $school['thumb'] ); ?>'); font-size: 0;"></div>
										<div class="space-y-1.5 flex-1 min-w-0">
											<h4 class="font-extrabold text-slate-800 text-lg hover:text-[#00308b] transition-colors leading-snug">
												<?php if ( $school['id'] ) : ?>
													<a href="<?php echo esc_url( get_permalink( $school['id'] ) ); ?>">
														<?php echo esc_html( $school['name'] ); ?><?php if ( $school['code'] ) { echo ' - ' . esc_html( $school['code'] ); } ?>
													</a>
												<?php else : ?>
													<span class="font-semibold text-slate-700"><?php echo esc_html( $school['name'] ); ?></span>
												<?php endif; ?>
											</h4>
											<?php if ( $school['address'] ) : ?>
												<p class="text-xs text-slate-400 flex items-center gap-1">
													<span>📍</span>
													<span class="truncate"><?php echo esc_html( $school['address'] ); ?></span>
												</p>
											<?php endif; ?>
										</div>
									</div>

									<!-- Programs Offered by this School -->
									<div class="border-t border-slate-100 pt-3">
										<div class="flex items-center justify-between mb-2">
											<div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Chương trình đào tạo:</div>
											<?php 
											$header_year = ! empty( $school['programs'][0]['academic_year'] ) ? $school['programs'][0]['academic_year'] : '2025 - 2026';
											?>
											<span class="text-[11px] font-semibold text-slate-500 bg-slate-100/90 px-2.5 py-0.5 rounded border border-slate-200/50">
												Biểu phí Năm học <?php echo esc_html( $header_year ); ?>
											</span>
										</div>
										<div class="divide-y divide-slate-100">
											<?php foreach ( $school['programs'] as $prog ) : ?>
												<div class="flex items-center justify-between gap-3 py-2.5 sm:py-3 rounded-xl hover:bg-slate-50/90 transition-all text-xs sm:text-sm border-b border-slate-100/60 last:border-0 my-0.5">
													<div class="flex items-center gap-4 sm:gap-6 min-w-0 flex-1">
														<?php if ( $prog['type_name'] ) : ?>
															<div class="w-[115px] sm:w-[125px] shrink-0">
																<span class="<?php echo esc_attr( $prog['badge_class'] ); ?> inline-block w-full text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider text-center">
																	<?php echo esc_html( $prog['type_name'] ); ?>
																</span>
															</div>
														<?php endif; ?>
														<div class="flex items-center gap-x-4 sm:gap-x-6 gap-y-1 flex-wrap text-slate-600 min-w-0">
															<span class="inline-flex items-center gap-1.5 shrink-0 whitespace-nowrap">
																<span class="text-slate-400">⏱</span>
																<span>Thời gian: <strong class="font-semibold text-slate-800"><?php echo esc_html( $prog['duration'] ); ?></strong></span>
															</span>
															<span class="hidden sm:inline text-slate-200">|</span>
															<span class="inline-flex items-center gap-1.5 min-w-0 whitespace-nowrap">
																<span>Học phí: <strong class="font-bold text-brand-primary"><?php echo esc_html( $prog['tuition_fee'] ); ?></strong></span>
															</span>
														</div>
													</div>
													<div class="shrink-0 ml-3">
														<?php if ( $prog['status'] === 'tam-ngung' ) : ?>
															<span class="text-xs text-slate-400 font-medium">Tạm ngưng</span>
														<?php else : ?>
															<a href="<?php echo esc_url( $prog['permalink'] ); ?>" class="text-xs font-bold text-[#00308b] hover:text-blue-700 hover:underline inline-flex items-center gap-1 py-1.5 px-2.5 rounded-lg hover:bg-blue-50 transition-colors whitespace-nowrap">
																<span>Tìm hiểu</span>
																<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
																	<path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
																</svg>
															</a>
														<?php endif; ?>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
								<?php
							else :
								// Single-row layout for schools with only 1 program
								$prog = $school['programs'][0];
								?>
								<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all p-4 md:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
									<div class="flex items-center gap-4 flex-1 min-w-0">
										<div class="h-20 w-20 bg-slate-200 bg-cover bg-center rounded-xl shrink-0 border border-slate-100 shadow-sm" style="background-image: url('<?php echo esc_url( $school['thumb'] ); ?>'); font-size: 0;"></div>
										<div class="space-y-1 flex-1 min-w-0">
											<div class="flex items-center gap-2 flex-wrap">
												<h4 class="font-extrabold text-slate-800 text-base sm:text-lg hover:text-[#00308b] transition-colors leading-snug">
													<?php if ( $school['id'] ) : ?>
														<a href="<?php echo esc_url( get_permalink( $school['id'] ) ); ?>">
															<?php echo esc_html( $school['name'] ); ?><?php if ( $school['code'] ) { echo ' - ' . esc_html( $school['code'] ); } ?>
														</a>
													<?php else : ?>
														<span class="font-semibold text-slate-700"><?php echo esc_html( $school['name'] ); ?></span>
													<?php endif; ?>
												</h4>
												<?php if ( $prog['type_name'] ) : ?>
													<span class="<?php echo esc_attr( $prog['badge_class'] ); ?> text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider">
														<?php echo esc_html( $prog['type_name'] ); ?>
													</span>
												<?php endif; ?>
											</div>
											<?php if ( $school['address'] ) : ?>
												<p class="text-xs sm:text-sm text-slate-400 flex items-center gap-1">
													<span>📍</span>
													<span class="truncate"><?php echo esc_html( $school['address'] ); ?></span>
												</p>
											<?php endif; ?>
											
											<div class="flex flex-wrap gap-x-4 gap-y-1 text-xs sm:text-sm text-slate-500 pt-0.5">
												<p>Thời gian: <span class="font-semibold text-slate-700"><?php echo esc_html( $prog['duration'] ); ?></span></p>
												<p class="hidden sm:inline text-slate-300">|</p>
												<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( $prog['tuition_fee'] ); ?></span></p>
											</div>
										</div>
									</div>

									<div class="shrink-0 w-full sm:w-auto flex items-center justify-end">
										<?php if ( $prog['status'] === 'tam-ngung' ) : ?>
											<span class="text-xs sm:text-sm text-slate-400 bg-slate-150 px-4 py-2 rounded-lg font-bold">Tạm ngưng</span>
										<?php else : ?>
											<a href="<?php echo esc_url( $prog['permalink'] ); ?>" class="w-full sm:w-auto text-xs sm:text-sm px-5 py-2.5 rounded-lg uppercase ltdh-btn-details min-h-[36px] flex items-center justify-center">Tìm hiểu</a>
										<?php endif; ?>
									</div>
								</div>
								<?php
							endif;
						endforeach;
						echo '</div>';
					else :
						echo '<p class="text-sm text-slate-500">Hiện tại chưa có lớp học nào mở cho ngành này.</p>';
					endif;
					?>
				</section>

			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<div class="sticky top-24 space-y-6">
					
					<!-- CONSULTATION FORM -->
					<section id="register" class="bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h3 class="text-lg font-bold text-slate-900 mb-2">Đăng ký tư vấn ngành <?php the_title(); ?></h3>
						<p class="text-sm text-slate-500 mb-4">Để lại thông tin, ban tuyển sinh sẽ gửi danh sách trường đào tạo phù hợp nhất với học lực và thời gian của bạn.</p>
						
						<?php 
						ltdh_render_consultation_form( [
							'current_major_id' => $major_id,
							'referral_source'  => get_permalink(),
						] );
					?>
					</section>

					<!-- RELATED NEWS & ANNOUNCEMENTS (Sidebar) -->
					<?php
					$related_news_query = new WP_Query( [
						'post_type'      => [ 'post', 'guide' ],
						'posts_per_page' => 6,
						'post_status'    => 'publish',
						'meta_query'     => [
							[
								'key'     => 'related_majors',
								'value'   => '"' . $major_id . '"',
								'compare' => 'LIKE',
							],
						],
					] );

					if ( $related_news_query->have_posts() ) :
						$has_more = ( $related_news_query->post_count > 5 );
					?>
						<section class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
							<h3 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-3">Tin tức & Hướng dẫn</h3>
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
									<a href="<?php echo esc_url( home_url( '/tin-tuc/?nganh=' . $major_id ) ); ?>" class="w-full text-center bg-slate-50 border border-slate-200 text-slate-700 py-2.5 rounded-lg font-bold text-sm hover:bg-slate-100 transition-all flex items-center justify-center gap-1.5 min-h-[38px]">
										<span>Xem thêm tin tức</span>
										<span>→</span>
									</a>
								</div>
							<?php endif; ?>
						</section>
					<?php
					endif;
					?>
 
					<!-- CONTACT INFO CARD -->
					<div class="bg-brand-accent/5 border border-brand-primary/10 rounded-lg p-6 text-center">
						<span class="text-sm text-brand-primary font-bold uppercase tracking-wider block mb-1">Ban hướng nghiệp</span>
						<h4 class="font-display font-black text-2xl text-slate-800 mb-4"><?php echo esc_html( $hotline ); ?></h4>
						<div class="flex gap-2">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex-1 bg-brand-accent text-white py-3.5 rounded-lg font-semibold text-sm hover:bg-[#e06e00] transition-all min-h-[44px] flex items-center justify-center">Gọi Ngay</a>
							<a href="<?php echo esc_url( $global_zalo ); ?>" class="flex-1 bg-white border border-brand-primary text-brand-primary py-3.5 rounded-lg font-semibold text-sm hover:bg-brand-accent/5 transition-all min-h-[44px] flex items-center justify-center">Zalo OA</a>
						</div>
					</div>

				</div>
			</div>
		</div>

	</div>
</main>

<?php
get_footer();
