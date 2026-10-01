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

$entry_roadmaps   = get_field( 'major_entry_roadmaps', $major_id );
$specializations  = get_field( 'major_specializations', $major_id );
$related_majors   = get_field( 'major_related', $major_id );

// Retrieve pre-calculated list of programs matching this major
$offered_program_ids = get_post_meta( $major_id, LTDH_META_OFFERED_PROGRAMS, true );

$global_zalo = ltdh_get_zalo_url();
$hotline = ltdh_get_hotline();

// Dynamically construct sticky navigation tabs matching reference mockup
$major_tabs = [];
$major_tabs[] = [
	'id'       => 'tong-quan',
	'title'    => 'Tổng quan',
	'subtitle' => 'Giới thiệu ngành',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>',
];

if ( ! empty( $specializations ) && is_array( $specializations ) ) {
	$major_tabs[] = [
		'id'       => 'chuyen-sau',
		'title'    => 'Chuyên ngành',
		'subtitle' => 'Định hướng đào tạo',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" /></svg>',
	];
}

if ( ! empty( $entry_roadmaps ) && is_array( $entry_roadmaps ) ) {
	$major_tabs[] = [
		'id'       => 'lo-trinh-hoc',
		'title'    => 'Lộ trình học',
		'subtitle' => 'Thời gian & hình thức',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
	];
}

if ( ! empty( $career ) ) {
	$major_tabs[] = [
		'id'       => 'co-hoi-nghe-nghiep',
		'title'    => 'Cơ hội việc làm',
		'subtitle' => 'Vị trí & thu nhập',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" /></svg>',
	];
}

$major_tabs[] = [
	'id'       => 'truong-tuyen-sinh',
	'title'    => 'Trường tuyển sinh',
	'subtitle' => 'Điểm chuẩn & học phí',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>',
];

if ( ! empty( $related_majors ) && is_array( $related_majors ) ) {
	$major_tabs[] = [
		'id'       => 'nganh-lien-quan',
		'title'    => 'Ngành liên quan',
		'subtitle' => 'Ngành đào tạo gần',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>',
	];
}
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>

	<!-- STICKY SECTION TAB NAVIGATION (Redesigned with UI/UX Pro Max) -->
	<div id="ltdh-major-sticky-nav" class="ltdh-sticky-nav sticky top-20 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.06)] transition-all duration-200">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<nav class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden py-2" aria-label="Điều hướng các mục ngành đào tạo">
				<?php foreach ( $major_tabs as $tab_idx => $tab ) : 
					$is_active = ( 0 === $tab_idx );
				?>
					<a href="#<?php echo esc_attr( $tab['id'] ); ?>" 
					   data-tab-target="<?php echo esc_attr( $tab['id'] ); ?>"
					   class="ltdh-major-tab-link group relative inline-flex items-center gap-2.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all duration-200 shrink-0 select-none cursor-pointer no-underline <?php echo $is_active ? 'bg-blue-50/90 text-[#00308b] font-bold shadow-2xs is-active' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'; ?>">
						
						<!-- Icon Box -->
						<div class="ltdh-tab-icon w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 <?php echo $is_active ? 'bg-[#00308b] text-white shadow-xs shadow-blue-900/20' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200 group-hover:text-slate-800'; ?>">
							<?php echo $tab['icon']; ?>
						</div>

						<!-- Text Column -->
						<div class="flex flex-col text-left">
							<span class="ltdh-tab-title text-xs sm:text-[13px] font-bold leading-tight transition-colors <?php echo $is_active ? 'text-[#00308b]' : 'text-slate-700 group-hover:text-slate-900'; ?>">
								<?php echo esc_html( $tab['title'] ); ?>
							</span>
							<span class="ltdh-tab-sub text-[10px] leading-tight mt-0.5 hidden md:block transition-colors <?php echo $is_active ? 'text-blue-700/80 font-medium' : 'text-slate-400 group-hover:text-slate-500'; ?>">
								<?php echo esc_html( $tab['subtitle'] ); ?>
							</span>
						</div>

						<!-- Subtle Active Accent Indicator (Bottom pill) -->
						<span class="ltdh-tab-indicator absolute -bottom-0.5 left-3 right-3 h-[2px] rounded-full transition-all duration-200 <?php echo $is_active ? 'bg-[#EA580C] opacity-100' : 'bg-transparent opacity-0'; ?>"></span>
					</a>
				<?php endforeach; ?>
			</nav>
		</div>
	</div>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
		

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-6 md:space-y-8">
				
				<!-- OVERVIEW -->
				<section id="tong-quan" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Tổng quan về ngành</h2>
					<div class="relative">
						<div id="major-intro-content" class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base overflow-hidden transition-all duration-500 max-h-[350px] relative">
							<?php 
							$raw_content = get_the_content();
							if ( ! empty( $specializations ) && ( false !== strpos( $raw_content, 'Các mảng đào tạo chuyên sâu' ) || false !== strpos( $raw_content, 'Tóm tắt thông tin' ) ) ) {
								$parts = preg_split( '/(1\.\s*Các mảng đào tạo chuyên sâu|⚡\s*Tóm tắt thông tin)/ui', $raw_content );
								$cleaned = ! empty( $parts[0] ) ? trim( $parts[0] ) : '';
								if ( empty( $cleaned ) ) {
									$cleaned = '<p>Ngành <strong>Công nghệ thông tin (CNTT)</strong> là ngành học đào tạo chuyên sâu về việc thiết kế, xây dựng, vận hành và tối ưu hóa hệ thống phần mềm, cơ sở dữ liệu và hạ tầng mạng máy tính trong kỷ nguyên chuyển đổi số.</p><p>Chương trình <strong>Liên thông Đại học ngành Công nghệ thông tin</strong> được thiết kế linh hoạt, tạo điều kiện thuận lợi nhất cho người đã tốt nghiệp Trung cấp, Cao đẳng hoặc đã có một văn bằng Đại học khác nhanh chóng hoàn thiện văn bằng Cử nhân / Kỹ sư chính quy chuẩn Bộ GD&ĐT, nâng bậc lương và mở rộng lộ trình thăng tiến sự nghiệp.</p>';
								}
								echo apply_filters( 'the_content', $cleaned );
							} else {
								the_content();
							}
							?>
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
						var storageKey = 'ltdh_major_intro_expanded_<?php echo $major_id; ?>';
						var isExpanded = false;
						
						try {
							isExpanded = localStorage.getItem(storageKey) === 'true';
						} catch (e) {}

						function applyState() {
							var needsToggle = content.scrollHeight > limit + 30;
							if (!needsToggle) {
								container.classList.remove('flex');
								container.classList.add('hidden');
								content.style.maxHeight = 'none';
								overlay.style.display = 'none';
								return;
							}
							
							container.classList.remove('hidden');
							container.classList.add('flex');
							
							if (isExpanded) {
								content.style.maxHeight = 'none';
								overlay.style.display = 'none';
								overlay.classList.add('opacity-0');
								btnText.textContent = 'Thu gọn';
								btnIcon.classList.add('rotate-180');
							} else {
								content.style.maxHeight = limit + 'px';
								overlay.style.display = 'block';
								overlay.classList.remove('opacity-0');
								btnText.textContent = 'Xem thêm tổng quan';
								btnIcon.classList.remove('rotate-180');
							}
						}
						
						applyState();
						window.addEventListener('load', applyState);
						
						button.addEventListener('click', function() {
							isExpanded = !isExpanded;
							try {
								localStorage.setItem(storageKey, isExpanded);
							} catch (e) {}

							if (isExpanded) {
								content.style.maxHeight = content.scrollHeight + 'px';
								overlay.style.display = 'block';
								overlay.classList.add('opacity-0');
								setTimeout(function() {
									if (isExpanded) {
										content.style.maxHeight = 'none';
										overlay.style.display = 'none';
									}
								}, 500);
								btnText.textContent = 'Thu gọn';
								btnIcon.classList.add('rotate-180');
							} else {
								overlay.style.display = 'block';
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

				<!-- SPECIALIZATIONS SECTION -->
				<?php if ( ! empty( $specializations ) && is_array( $specializations ) ) : ?>
					<section id="chuyen-sau" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<div class="border-b border-slate-100 pb-3 md:pb-4 mb-4">
							<h2 class="text-xl md:text-2xl font-bold text-slate-900">Các mảng đào tạo chuyên sâu trong ngành <?php the_title(); ?></h2>
							<p class="text-xs md:text-sm text-slate-500 mt-1">Các định hướng chuyên môn mũi nhọn giúp sinh viên phát huy thế mạnh nghề nghiệp</p>
						</div>
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
							<?php foreach ( $specializations as $spec ) : ?>
								<div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4 sm:p-5 hover:border-[#00308b] hover:shadow-xs transition-all space-y-2">
									<div class="flex items-center gap-2.5">
										<span class="text-2xl shrink-0"><?php echo esc_html( $spec['spec_icon'] ?: '💻' ); ?></span>
										<h4 class="font-extrabold text-slate-900 text-sm sm:text-base"><?php echo esc_html( $spec['spec_name'] ); ?></h4>
									</div>
									<p class="text-xs sm:text-sm text-slate-600 leading-relaxed"><?php echo esc_html( $spec['spec_desc'] ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- ENTRY ROADMAPS SECTION -->
				<?php if ( ! empty( $entry_roadmaps ) && is_array( $entry_roadmaps ) ) : ?>
					<section id="lo-trinh-hoc" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<div class="border-b border-slate-100 pb-3 md:pb-4 mb-4">
							<h2 class="text-xl md:text-2xl font-bold text-slate-900">Lộ trình đào tạo Liên thông ngành <?php the_title(); ?></h2>
							<p class="text-xs md:text-sm text-slate-500 mt-1">Thời gian và hình thức đào tạo được tối ưu linh hoạt theo từng văn bằng đầu vào</p>
						</div>

						<!-- Desktop Table View (>= 768px) -->
						<div class="hidden md:block overflow-x-auto rounded-xl border border-slate-200/80 shadow-2xs">
							<table class="w-full text-left border-collapse text-xs sm:text-sm">
								<thead>
									<tr class="bg-slate-100/90 text-slate-700 font-extrabold border-b border-slate-200">
										<th class="py-3.5 px-4 font-bold">Trình độ đầu vào</th>
										<th class="py-3.5 px-4 font-bold">Thời gian học</th>
										<th class="py-3.5 px-4 font-bold">Hình thức học</th>
										<th class="py-3.5 px-4 font-bold">Bằng cấp nhận được</th>
									</tr>
								</thead>
								<tbody class="divide-y divide-slate-100 bg-white">
									<?php foreach ( $entry_roadmaps as $roadmap ) : ?>
										<tr class="hover:bg-slate-50/80 transition-colors">
											<td class="py-3.5 px-4 font-bold text-slate-900"><?php echo esc_html( $roadmap['entry_level'] ); ?></td>
											<td class="py-3.5 px-4 font-bold text-[#00308b]"><?php echo esc_html( $roadmap['study_time'] ); ?></td>
											<td class="py-3.5 px-4 text-slate-700"><?php echo esc_html( $roadmap['study_mode'] ); ?></td>
											<td class="py-3.5 px-4 font-bold text-emerald-700"><?php echo esc_html( $roadmap['degree_output'] ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

						<!-- Mobile Card View (< 768px) -->
						<div class="grid grid-cols-1 gap-3 md:hidden">
							<?php foreach ( $entry_roadmaps as $roadmap ) : ?>
								<div class="bg-white border border-slate-200/80 rounded-xl p-3.5 shadow-2xs space-y-2">
									<div class="flex items-center justify-between border-b border-slate-100 pb-2">
										<span class="text-xs font-bold text-slate-900"><?php echo esc_html( $roadmap['entry_level'] ); ?></span>
										<span class="px-2 py-0.5 rounded text-[11px] font-black bg-blue-50 text-[#00308b] border border-blue-100">
											<?php echo esc_html( $roadmap['study_time'] ); ?>
										</span>
									</div>
									<div class="text-xs space-y-1 text-slate-600">
										<p><span class="text-slate-400 font-medium">Hình thức:</span> <strong class="text-slate-800"><?php echo esc_html( $roadmap['study_mode'] ); ?></strong></p>
										<p><span class="text-slate-400 font-medium">Bằng cấp:</span> <strong class="text-emerald-700 font-bold"><?php echo esc_html( $roadmap['degree_output'] ); ?></strong></p>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- CAREER OPPORTUNITIES -->
				<?php if ( $career ) : ?>
					<section id="co-hoi-nghe-nghiep" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 md:pb-4 mb-4">Cơ hội nghề nghiệp & Định hướng</h2>
						<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
							<?php echo wp_kses_post( $career ); ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- PROGRAMS FOR THIS MAJOR -->
				<section id="truong-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
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
									<div class="flex items-center gap-3">
										<div class="h-9 w-9 sm:h-11 sm:w-11 bg-slate-200 bg-cover bg-center rounded-lg shrink-0 border border-slate-100" style="background-image: url('<?php echo esc_url( $school['thumb'] ); ?>'); font-size: 0;"></div>
										<div class="space-y-0.5 flex-1 min-w-0">
											<h4 class="font-bold text-slate-800 text-xs sm:text-sm hover:text-[#00308b] transition-colors leading-snug">
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
										<div class="flex items-center justify-between gap-2 mb-2.5">
											<div class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Hệ đào tạo:</div>
											<?php 
											$header_year = ! empty( $school['programs'][0]['academic_year'] ) ? $school['programs'][0]['academic_year'] : '2025 - 2026';
											?>
											<span class="text-[11px] font-semibold text-slate-500 bg-slate-100/90 px-2 py-0.5 rounded border border-slate-200/50 whitespace-nowrap shrink-0">
												Biểu phí <?php echo esc_html( $header_year ); ?>
											</span>
										</div>
										<div class="divide-y divide-slate-100/80 space-y-1 sm:space-y-0">
											<?php foreach ( $school['programs'] as $prog ) : ?>
												<div class="flex items-center justify-between gap-2.5 py-2.5 sm:py-3 rounded-xl hover:bg-slate-50/90 transition-all text-xs sm:text-sm my-0.5">
													<div class="flex items-center gap-2.5 sm:gap-5 min-w-0 flex-1">
														<?php if ( $prog['type_name'] ) : ?>
															<div class="w-[105px] sm:w-[125px] shrink-0">
																<span class="<?php echo esc_attr( $prog['badge_class'] ); ?> inline-block w-full text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider text-center">
																	<?php echo esc_html( $prog['type_name'] ); ?>
																</span>
															</div>
														<?php endif; ?>
														<div class="flex flex-col sm:flex-row sm:items-center gap-x-5 gap-y-0.5 text-slate-600 min-w-0 flex-1">
															<span class="inline-flex items-center gap-1 shrink-0">
																<span class="text-slate-400">⏱</span>
																<span>Thời gian: <strong class="font-semibold text-slate-800"><?php echo esc_html( $prog['duration'] ); ?></strong></span>
															</span>
															<span class="hidden sm:inline text-slate-200">|</span>
															<span class="inline-flex items-center gap-1 min-w-0">
																<span>Học phí: <strong class="font-bold text-brand-primary"><?php echo esc_html( $prog['tuition_fee'] ); ?></strong></span>
															</span>
														</div>
													</div>
													<div class="shrink-0 ml-1 sm:ml-3">
														<?php if ( $prog['status'] === 'tam-ngung' ) : ?>
															<span class="text-xs text-slate-400 font-medium">Tạm ngưng</span>
														<?php else : ?>
															<a href="<?php echo esc_url( $prog['permalink'] ); ?>" class="text-xs font-bold text-[#00308b] hover:text-blue-700 hover:underline inline-flex items-center gap-0.5 py-1 px-1.5 sm:px-2.5 rounded-lg hover:bg-blue-50 transition-colors whitespace-nowrap">
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

				<!-- RELATED MAJORS -->
				<?php 
				if ( ! empty( $related_majors ) && is_array( $related_majors ) ) :
				?>
					<section id="nganh-lien-quan" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 md:pb-4 mb-4">Ngành đào tạo liên quan</h2>
						<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 sm:gap-4">
							<?php foreach ( $related_majors as $rel_major ) : 
								$rel_id    = is_object( $rel_major ) ? $rel_major->ID : (int) $rel_major;
								$rel_title = get_the_title( $rel_id );
								$rel_code  = get_field( 'major_code', $rel_id );
								$rel_url   = get_permalink( $rel_id );
							?>
								<a href="<?php echo esc_url( $rel_url ); ?>" class="group p-3.5 rounded-xl border border-slate-200/80 hover:border-[#00308b] hover:shadow-xs transition-all bg-slate-50/50 flex items-center gap-3">
									<div class="w-10 h-10 rounded-lg bg-blue-50 text-[#00308b] flex items-center justify-center font-bold text-base shrink-0 group-hover:bg-[#00308b] group-hover:text-white transition-colors">
										🎓
									</div>
									<div class="min-w-0 flex-1">
										<h4 class="font-bold text-slate-800 text-xs sm:text-sm group-hover:text-[#00308b] transition-colors truncate"><?php echo esc_html( $rel_title ); ?></h4>
										<?php if ( $rel_code ) : ?>
											<span class="text-[10px] text-slate-400 font-medium">Mã ngành: <?php echo esc_html( $rel_code ); ?></span>
										<?php endif; ?>
									</div>
								</a>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<div class="sticky top-36 md:top-40 space-y-6">
					
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

<!-- STICKY SECTION NAV STYLES & SCROLLSPY -->
<style>
/* Header & Sticky Nav Positioning */
#masthead {
	transition: top 0.2s ease;
}
#ltdh-major-sticky-nav {
	transition: top 0.2s ease, box-shadow 0.2s ease;
}
body.admin-bar #masthead {
	top: 32px !important;
}
body.admin-bar #ltdh-major-sticky-nav {
	top: calc(80px + 32px) !important;
}
@media screen and (max-width: 782px) {
	body.admin-bar #masthead {
		top: 46px !important;
	}
	body.admin-bar #ltdh-major-sticky-nav {
		top: calc(80px + 46px) !important;
	}
}

/* Active tab style matching UI/UX Pro Max standards */
.ltdh-major-tab-link {
	border: 1px solid transparent;
}
.ltdh-major-tab-link.is-active {
	background-color: rgba(239, 246, 255, 0.95) !important;
	border-color: rgba(191, 219, 254, 0.85) !important;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
}
.ltdh-major-tab-link.is-active .ltdh-tab-icon {
	background-color: #00308b !important;
	color: #ffffff !important;
	box-shadow: 0 2px 6px rgba(0, 48, 139, 0.25) !important;
}
.ltdh-major-tab-link.is-active .ltdh-tab-title {
	color: #00308b !important;
	font-weight: 800 !important;
}
.ltdh-major-tab-link.is-active .ltdh-tab-sub {
	color: #1d4ed8 !important;
	font-weight: 600 !important;
}
.ltdh-major-tab-link.is-active .ltdh-tab-indicator {
	background-color: #EA580C !important;
	opacity: 1 !important;
}
</style>

<script>
(function() {
	document.addEventListener('DOMContentLoaded', function() {
		var stickyNav = document.getElementById('ltdh-major-sticky-nav');
		if (!stickyNav) return;

		var navContainer = stickyNav.querySelector('nav');
		var tabLinks = Array.prototype.slice.call(stickyNav.querySelectorAll('.ltdh-major-tab-link'));
		if (!tabLinks.length) return;

		var sectionMap = [];
		tabLinks.forEach(function(link) {
			var id = link.getAttribute('data-tab-target');
			var sec = document.getElementById(id);
			if (sec) {
				sectionMap.push({
					id: id,
					link: link,
					section: sec
				});
			}
		});

		if (!sectionMap.length) return;

		var isClickScrolling = false;
		var scrollTimeout = null;

		function getStickyOffset() {
			var header = document.getElementById('masthead');
			var headerHeight = header ? header.offsetHeight : 80;
			var navHeight = stickyNav.offsetHeight || 54;
			var adminBar = document.getElementById('wpadminbar');
			var adminBarHeight = (adminBar && window.getComputedStyle(adminBar).position === 'fixed') ? adminBar.offsetHeight : 0;
			return headerHeight + navHeight + adminBarHeight;
		}

		function setActiveTab(targetId, shouldScrollNav) {
			sectionMap.forEach(function(item) {
				var link = item.link;
				var iconEl = link.querySelector('.ltdh-tab-icon');
				var titleEl = link.querySelector('.ltdh-tab-title');
				var subEl = link.querySelector('.ltdh-tab-sub');
				var indicatorEl = link.querySelector('.ltdh-tab-indicator');

				if (item.id === targetId) {
					link.classList.add('bg-blue-50/90', 'border-blue-200/80', 'text-[#00308b]', 'font-bold', 'shadow-2xs', 'is-active');
					link.classList.remove('text-slate-600', 'hover:bg-slate-100/70', 'border-transparent');
					if (iconEl) {
						iconEl.classList.add('bg-[#00308b]', 'text-white', 'shadow-xs', 'shadow-blue-900/20');
						iconEl.classList.remove('bg-slate-100', 'text-slate-500');
					}
					if (titleEl) {
						titleEl.classList.add('text-[#00308b]', 'font-extrabold');
						titleEl.classList.remove('text-slate-700');
					}
					if (subEl) {
						subEl.classList.add('text-blue-700/80', 'font-medium');
						subEl.classList.remove('text-slate-400');
					}
					if (indicatorEl) {
						indicatorEl.classList.add('bg-[#EA580C]', 'opacity-100');
						indicatorEl.classList.remove('bg-transparent', 'opacity-0');
					}

					if (shouldScrollNav && navContainer && navContainer.scrollWidth > navContainer.clientWidth) {
						var tabLeft = link.offsetLeft;
						var tabWidth = link.offsetWidth;
						var containerWidth = navContainer.clientWidth;
						navContainer.scrollTo({
							left: tabLeft - (containerWidth / 2) + (tabWidth / 2),
							behavior: 'smooth'
						});
					}
				} else {
					link.classList.remove('bg-blue-50/90', 'border-blue-200/80', 'text-[#00308b]', 'font-bold', 'shadow-2xs', 'is-active');
					link.classList.add('text-slate-600', 'hover:bg-slate-100/70', 'border-transparent');
					if (iconEl) {
						iconEl.classList.remove('bg-[#00308b]', 'text-white', 'shadow-xs', 'shadow-blue-900/20');
						iconEl.classList.add('bg-slate-100', 'text-slate-500');
					}
					if (titleEl) {
						titleEl.classList.remove('text-[#00308b]', 'font-extrabold');
						titleEl.classList.add('text-slate-700');
					}
					if (subEl) {
						subEl.classList.remove('text-blue-700/80', 'font-medium');
						subEl.classList.add('text-slate-400');
					}
					if (indicatorEl) {
						indicatorEl.classList.remove('bg-[#EA580C]', 'opacity-100');
						indicatorEl.classList.add('bg-transparent', 'opacity-0');
					}
				}
			});
		}

		// Handle Click on Tabs
		tabLinks.forEach(function(link) {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				var targetId = this.getAttribute('data-tab-target');
				var targetItem = sectionMap.find(function(item) { return item.id === targetId; });
				if (!targetItem) return;

				isClickScrolling = true;
				clearTimeout(scrollTimeout);
				setActiveTab(targetId, true);

				var totalOffset = getStickyOffset();
				var elementPosition = targetItem.section.getBoundingClientRect().top + window.pageYOffset;
				var offsetPosition = elementPosition - totalOffset + 8; // gentle padding

				window.scrollTo({
					top: offsetPosition,
					behavior: 'smooth'
				});

				if (history.replaceState) {
					history.replaceState(null, '', '#' + targetId);
				}

				scrollTimeout = setTimeout(function() {
					isClickScrolling = false;
				}, 750);
			});
		});

		// Scrollspy with requestAnimationFrame
		var ticking = false;
		function onScroll() {
			if (isClickScrolling) return;

			var totalOffset = getStickyOffset();
			var scrollPos = window.pageYOffset + totalOffset + 50;
			var currentId = sectionMap[0].id;

			var atPageBottom = (window.innerHeight + window.pageYOffset) >= (document.documentElement.scrollHeight - 60);

			if (atPageBottom) {
				currentId = sectionMap[sectionMap.length - 1].id;
			} else {
				for (var i = 0; i < sectionMap.length; i++) {
					var item = sectionMap[i];
					var secTop = item.section.offsetTop;
					var secHeight = item.section.offsetHeight;
					if (scrollPos >= secTop && scrollPos < secTop + secHeight) {
						currentId = item.id;
						break;
					} else if (scrollPos >= secTop) {
						currentId = item.id;
					}
				}
			}

			if (currentId) {
				setActiveTab(currentId, true);
			}
		}

		window.addEventListener('scroll', function() {
			if (!ticking) {
				window.requestAnimationFrame(function() {
					onScroll();
					ticking = false;
				});
				ticking = true;
			}
		}, { passive: true });

		// Handle initial hash in URL
		if (window.location.hash) {
			var initialHash = window.location.hash.replace('#', '');
			var match = sectionMap.find(function(item) { return item.id === initialHash; });
			if (match) {
				setTimeout(function() {
					match.link.click();
				}, 250);
			} else {
				setTimeout(onScroll, 100);
			}
		} else {
			setTimeout(onScroll, 100);
		}
	});
})();
</script>

<?php
get_footer();
