<?php
/**
 * Single School Template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$school_id  = get_the_ID();
$school_title = get_the_title( $school_id );
$website    = get_field( 'website', $school_id );
$address    = get_field( 'address', $school_id );
$hotline    = ltdh_get_school_hotline( $school_id );
$adm_info   = get_field( 'admission_info', $school_id );
$contact    = get_field( 'contact_info', $school_id );

// Retrieve pre-calculated list of programs offered by this school
$offered_program_ids = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
	update_meta_cache( 'post', $offered_program_ids );
	update_object_term_cache( $offered_program_ids, 'program' );
}

$global_zalo = ltdh_get_zalo_url();
$zalo_group  = get_field( 'zalo_group_url', $school_id ) ?: get_field( 'school_zalo_group', $school_id ) ?: $global_zalo;

// Dynamically construct sticky navigation tabs for School
$school_tabs = [];
$school_tabs[] = [
	'id'       => 'gioi-thieu',
	'title'    => 'Giới thiệu',
	'subtitle' => 'Thông tin về trường',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>',
];

$school_tabs[] = [
	'id'       => 'chuong-trinh-tuyen-sinh',
	'title'    => 'Ngành & Chương trình',
	'subtitle' => 'Hệ đào tạo tuyển sinh',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>',
];

$school_tabs[] = [
	'id'       => 'quy-trinh-tuyen-sinh',
	'title'    => 'Quy trình',
	'subtitle' => '4 bước xét tuyển',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
];

if ( ! empty( $contact ) ) {
	$school_tabs[] = [
		'id'       => 'thong-tin-lien-he',
		'title'    => 'Liên hệ',
		'subtitle' => 'Văn phòng tuyển sinh',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>',
	];
}
?>


<main id="primary" class="site-main bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-12">
		<?php 
		$banner_image = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'full' ) : ( get_field( 'school_banner', $school_id ) ?: get_template_directory_uri() . '/assets/images/banner-school.jpg' );
		if ( $banner_image ) : 
		?>
			<!-- School Cover/Banner Image inside container -->
			<div class="w-full h-48 sm:h-64 md:h-80 lg:h-96 rounded-2xl overflow-hidden mb-6 shadow-sm border border-slate-100 bg-slate-200">
				<img src="<?php echo esc_url( $banner_image ); ?>" class="w-full h-full object-cover object-center" alt="<?php the_title_attribute(); ?>">
			</div>
		<?php endif; ?>

		<!-- STICKY SECTION TAB NAVIGATION (Inside Container, Floating Rounded Card) -->
		<div id="ltdh-school-sticky-nav" class="ltdh-sticky-nav sticky top-20 z-40 bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.06)] mb-6 transition-all duration-200">
			<nav class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden p-1.5 sm:p-2" aria-label="Điều hướng các mục của trường">
				<?php foreach ( $school_tabs as $tab_idx => $tab ) : 
					$is_active = ( 0 === $tab_idx );
				?>
					<a href="#<?php echo esc_attr( $tab['id'] ); ?>" 
					   data-tab-target="<?php echo esc_attr( $tab['id'] ); ?>"
					   class="ltdh-section-tab-link group relative inline-flex items-center gap-2.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all duration-200 shrink-0 select-none cursor-pointer no-underline <?php echo $is_active ? 'bg-blue-50/90 text-[#00308b] font-bold shadow-2xs is-active' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'; ?>">
						
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

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-6 md:space-y-8">
				
				<!-- OVERVIEW -->
				<section id="gioi-thieu" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Giới thiệu về trường</h2>
					<div class="relative">
						<div id="school-intro-content" class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base overflow-hidden transition-all duration-500 max-h-[350px] relative">
							<?php the_content(); ?>
							<!-- Gradient Overlay for fade-out effect -->
							<div id="school-intro-overlay" class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white to-transparent pointer-events-none transition-opacity duration-300"></div>
						</div>
						
						<div id="school-intro-btn-container" class="hidden justify-center mt-4 border-t border-slate-100 pt-4">
							<button id="school-intro-toggle" class="flex items-center gap-2 px-5 py-2 rounded-full border border-slate-200 bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 hover:text-brand-accent shadow-sm transition-all focus:outline-none">
								<span id="school-intro-btn-text">Xem thêm giới thiệu</span>
								<svg id="school-intro-btn-icon" class="w-4 h-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
								</svg>
							</button>
						</div>
					</div>

					<script>
					(function() {
						var content = document.getElementById('school-intro-content');
						var overlay = document.getElementById('school-intro-overlay');
						var container = document.getElementById('school-intro-btn-container');
						var button = document.getElementById('school-intro-toggle');
						var btnText = document.getElementById('school-intro-btn-text');
						var btnIcon = document.getElementById('school-intro-btn-icon');
						
						if (!content || !overlay || !container || !button) return;
						
						var limit = 350;
						var storageKey = 'ltdh_school_intro_expanded_<?php echo $school_id; ?>';
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
								btnText.textContent = 'Xem thêm giới thiệu';
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
								btnText.textContent = 'Xem thêm giới thiệu';
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




				

				<!-- PROGRAMS OFFERED -->
				<section id="chuong-trinh-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
					<!-- Hidden anchor target for backward compatibility -->
					<span id="nganh-dao-tao" class="relative -top-36 block invisible pointer-events-none"></span>

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

					$tax_training_type_filter = [
						'taxonomy' => LTDH_TAX_TRAINING_TYPE,
						'field'    => 'slug',
						'terms'    => [ 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ],
						'operator' => 'IN',
					];

					if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
						$programs_query = new WP_Query( [
							'post_type'      => 'program',
							'post__in'       => $offered_program_ids,
							'post_status'    => 'publish',
							'posts_per_page' => -1,
							'no_found_rows'  => true,
							'tax_query'      => [
								$tax_training_type_filter,
							],
							'meta_query'     => [
								'relation' => 'AND',
								$meta_status_filter,
							],
						] );
					}

					if ( empty( $programs_query ) || ! $programs_query->have_posts() ) {
						// Fallback query if meta relationships don't exist yet or yielded no published programs
						$programs_query = new WP_Query( [
							'post_type'      => 'program',
							'post_status'    => 'publish',
							'posts_per_page' => -1,
							'no_found_rows'  => true,
							'tax_query'      => [
								$tax_training_type_filter,
							],
							'meta_query'     => [
								'relation' => 'AND',
								[
									'key'     => LTDH_META_SCHOOL_REL,
									'value'   => $school_id,
									'compare' => '=',
								],
								$meta_status_filter,
							],
						] );
					}

					$majors_data = [];
					if ( $programs_query->have_posts() ) {
						$queried_ids = wp_list_pluck( $programs_query->posts, 'ID' );
						update_meta_cache( 'post', $queried_ids );
						update_object_term_cache( $queried_ids, 'program' );

						$school_thumb_url = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'thumbnail' ) : get_template_directory_uri() . '/assets/images/cropped-logo-scaled-2.webp';

						while ( $programs_query->have_posts() ) {
							$programs_query->the_post();
							$prog_id = get_the_ID();
							$major_rel_id = get_field( LTDH_META_MAJOR_REL, $prog_id );
							$major_key = $major_rel_id ? $major_rel_id : 'no_major_' . $prog_id;

							if ( ! isset( $majors_data[ $major_key ] ) ) {
								$major_name = $major_rel_id ? get_the_title( $major_rel_id ) : 'Mời tư vấn';
								$major_thumb = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium_large' ) : '';
								if ( ! $major_thumb ) {
									$major_thumb = get_the_post_thumbnail_url( $prog_id, 'medium_large' );
								}
								if ( ! $major_thumb ) {
									$major_fallbacks = [
										get_stylesheet_directory_uri() . '/assets/images/banner-program.jpg',
										get_stylesheet_directory_uri() . '/assets/images/banner-hero-02.webp',
										get_stylesheet_directory_uri() . '/assets/images/banner-default.jpg',
									];
									$idx = absint( $prog_id ) % count( $major_fallbacks );
									$major_thumb = $major_fallbacks[ $idx ];
								}
								$major_code = $major_rel_id ? ( get_field( 'major_code', $major_rel_id ) ?: get_post_meta( $major_rel_id, 'major_code', true ) ) : '';
								$major_terms = $major_rel_id ? get_the_terms( $major_rel_id, 'major_cat' ) : [];
								$major_cat_name = ( ! empty( $major_terms ) && ! is_wp_error( $major_terms ) ) ? $major_terms[0]->name : '';

								$majors_data[ $major_key ] = [
									'id'       => $major_rel_id,
									'name'     => $major_name,
									'code'     => $major_code,
									'cat_name' => $major_cat_name,
									'thumb'    => $major_thumb,
									'programs' => [],
								];
							}

							$status = get_post_meta( $prog_id, LTDH_META_ADMISSION_STATUS, true ) ?: 'tuyen-sinh';
							$clean_title = get_the_title();
							if ( $school_title ) {
								$clean_title = str_replace( ' - ' . $school_title, '', $clean_title );
								$clean_title = str_replace( ' – ' . $school_title, '', $clean_title );
							}
							$types = wp_get_post_terms( $prog_id, 'training_type' );
							$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';
							$type_slug = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->slug : '';
							$clean_type_name = preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) );
							$clean_major_name = preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) );
							$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );
							$tuition_fee = ltdh_get_program_tuition_display( $prog_id );
							$duration = get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm';
							$permalink = get_permalink( $prog_id );
							$prog_slug = get_post_field( 'post_name', $prog_id );
							$major_slug = $major_rel_id ? get_post_field( 'post_name', $major_rel_id ) : '';

							// Determine badge classes based on training type name
							$badge_class = 'bg-orange-50 text-orange-700';
							if ( $clean_type_name ) {
								$type_name_lower = mb_strtolower( trim( $clean_type_name ), 'UTF-8' );
								if ( false !== strpos( $type_name_lower, 'chính quy' ) ) {
									$badge_class = 'bg-blue-50 text-[#00308b]';
								} elseif ( false !== strpos( $type_name_lower, 'từ xa' ) ) {
									$badge_class = 'bg-emerald-50 text-emerald-700';
								} elseif ( false !== strpos( $type_name_lower, 'vừa học vừa làm' ) || false !== strpos( $type_name_lower, 'vừa làm vừa học' ) || false !== strpos( $type_name_lower, 'liên thông' ) || false !== strpos( $type_name_lower, 'văn bằng 2' ) ) {
									$badge_class = 'bg-amber-50 text-amber-800';
								}
							}

							$academic_year = get_field( 'tuition_academic_year', $prog_id ) ?: '2025 - 2026';

							$majors_data[ $major_key ]['programs'][] = [
								'id'                => $prog_id,
								'title'             => $clean_title,
								'opportunity_title' => $opportunity_title,
								'status'            => $status,
								'type_name'         => $clean_type_name,
								'badge_class'       => $badge_class,
								'tuition_fee'       => $tuition_fee,
								'academic_year'     => $academic_year,
								'duration'          => $duration,
								'permalink'         => $permalink,
								'slug'              => $prog_slug,
								'he'                => $type_slug,
								'nganh'             => $major_slug,
								'major_name'        => $clean_major_name,
								'school_name'       => $school_title,
								'thumb'             => $school_thumb_url,
							];
						}
						wp_reset_postdata();
					}

					$available_he = [];
					$total_programs_count = 0;
					if ( ! empty( $majors_data ) ) {
						foreach ( $majors_data as $major_item ) {
							foreach ( $major_item['programs'] as $prog_item ) {
								$total_programs_count++;
								if ( ! empty( $prog_item['he'] ) ) {
									$he_slug = $prog_item['he'];
									if ( ! isset( $available_he[ $he_slug ] ) ) {
										$available_he[ $he_slug ] = [
											'name'  => $prog_item['type_name'] ?: $he_slug,
											'count' => 0,
										];
									}
									$available_he[ $he_slug ]['count']++;
								}
							}
						}
					}
					?>

					<!-- Header with Live Stats & Quick Search/Filter -->
					<div class="border-b border-slate-200/80 pb-5 mb-6">
						<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
							<div>
								<h2 class="text-xl md:text-2xl font-black text-slate-900 leading-tight">Ngành và chương trình trường đào tạo</h2>
								<p class="text-xs md:text-sm text-slate-500 mt-1">Danh sách các ngành và hệ đào tạo tuyển sinh chính thức tại trường</p>
							</div>
							<div class="flex items-center gap-2 self-start sm:self-center shrink-0">
								<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-[#00308b] border border-blue-100/80">
									<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
										<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
									</svg>
									<span id="ltdh-majors-total-badge"><?php echo count( $majors_data ); ?> ngành tuyển sinh</span>
								</span>
							</div>
						</div>

						<!-- Quick Filter Controls: Instant Search & Training System Tabs -->
						<div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 pt-3 border-t border-slate-100">
							<!-- Search Input -->
							<div class="relative flex-1">
								<span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
									<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
										<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
									</svg>
								</span>
								<input type="text"
									   id="ltdh-major-search-input"
									   placeholder="Tìm kiếm tên ngành, mã ngành..."
									   class="w-full pl-9 pr-8 py-2 rounded-xl text-xs sm:text-sm border border-slate-200 bg-slate-50/70 focus:bg-white focus:border-[#00308b] focus:ring-2 focus:ring-blue-100 outline-none transition-all placeholder:text-slate-400">
								<button type="button"
										id="ltdh-major-search-clear"
										class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
										title="Xóa tìm kiếm">
									<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
										<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
									</svg>
								</button>
							</div>

							<!-- Training Types Tabs -->
							<?php if ( ! empty( $available_he ) && count( $available_he ) > 1 ) : ?>
								<div class="flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden shrink-0" id="ltdh-he-filter-tabs">
									<button type="button"
											data-he-filter="all"
											class="ltdh-he-tab-btn active px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-[#00308b] text-white shadow-2xs cursor-pointer whitespace-nowrap">
										Tất cả (<?php echo count( $majors_data ); ?>)
									</button>
									<?php foreach ( $available_he as $he_key => $he_info ) : ?>
										<button type="button"
												data-he-filter="<?php echo esc_attr( $he_key ); ?>"
												class="ltdh-he-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-slate-100 hover:bg-slate-200/80 text-slate-700 cursor-pointer whitespace-nowrap">
											<?php echo esc_html( $he_info['name'] ); ?> (<?php echo esc_html( $he_info['count'] ); ?>)
										</button>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<?php if ( ! empty( $majors_data ) ) : ?>
						<!-- Majors Grid (Visual Cards with Thumbnails) -->
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-5" id="ltdh-majors-container">
							<?php
							foreach ( $majors_data as $major ) :
								$m_id           = $major['id'];
								$major_link     = $m_id ? add_query_arg( 'truong', get_post_field( 'post_name', $school_id ), get_permalink( $m_id ) ) : '';
								$major_he_slugs = array_unique( array_column( $major['programs'], 'he' ) );
								$programs_count = count( $major['programs'] );
								$first_prog     = ! empty( $major['programs'] ) ? $major['programs'][0] : null;
								$primary_link   = $major_link ?: ( $first_prog ? $first_prog['permalink'] : '#' );
								?>
								<div class="ltdh-major-card group bg-white border border-slate-200/90 hover:border-blue-300 rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
									 data-major-name="<?php echo esc_attr( mb_strtolower( $major['name'], 'UTF-8' ) ); ?>"
									 data-major-code="<?php echo esc_attr( mb_strtolower( $major['code'] ?? '', 'UTF-8' ) ); ?>"
									 data-major-he="<?php echo esc_attr( implode( ' ', $major_he_slugs ) ); ?>">
									
									<!-- Thumbnail Area (16:9 ratio, smooth zoom on hover) -->
									<div class="relative h-40 sm:h-44 bg-slate-100 overflow-hidden">
										<a href="<?php echo esc_url( $primary_link ); ?>" class="block w-full h-full">
											<img src="<?php echo esc_url( $major['thumb'] ); ?>" 
												 alt="Ngành <?php echo esc_attr( $major['name'] ); ?>" 
												 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
												 loading="lazy">
										</a>
										<div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-900/25 to-transparent pointer-events-none"></div>

										<!-- Top Badges on Thumbnail -->
										<div class="absolute top-2.5 left-2.5 right-2.5 flex items-center justify-between gap-1.5 z-10 pointer-events-none">
											<?php if ( ! empty( $major['code'] ) ) : ?>
												<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-900/80 text-white backdrop-blur-md shadow-xs border border-white/10">
													Mã: <?php echo esc_html( $major['code'] ); ?>
												</span>
											<?php else : ?>
												<span></span>
											<?php endif; ?>

											<?php if ( $programs_count > 1 ) : ?>
												<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-white/95 text-slate-800 backdrop-blur-md shadow-xs">
													<?php echo esc_html( $programs_count ); ?> hệ đào tạo
												</span>
											<?php endif; ?>
										</div>

										<!-- Bottom Badges on Thumbnail (Training Systems) -->
										<div class="absolute bottom-2.5 left-2.5 right-2.5 flex flex-wrap gap-1.5 z-10">
											<?php foreach ( $major['programs'] as $prog ) : ?>
												<?php if ( $prog['type_name'] ) : ?>
													<a href="<?php echo esc_url( $prog['permalink'] ); ?>" 
													   class="<?php echo esc_attr( $prog['badge_class'] ); ?> text-[11px] font-bold px-2 py-0.5 rounded-md shadow-xs hover:opacity-90 transition-opacity" 
													   title="Xem chi tiết <?php echo esc_attr( $prog['type_name'] ); ?>">
														<?php echo esc_html( $prog['type_name'] ); ?>
													</a>
												<?php endif; ?>
											<?php endforeach; ?>
										</div>
									</div>

									<!-- Card Body -->
									<div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
										<div>
											<?php if ( ! empty( $major['cat_name'] ) ) : ?>
												<span class="text-[11px] font-bold uppercase tracking-wider text-[#00308b] mb-1 block">
													<?php echo esc_html( $major['cat_name'] ); ?>
												</span>
											<?php endif; ?>

											<!-- Major Name (Display full title, clamp 2 lines nicely, no truncation) -->
											<h3 class="font-extrabold text-slate-900 text-sm sm:text-base group-hover:text-[#00308b] transition-colors leading-snug mb-2.5">
												<a href="<?php echo esc_url( $primary_link ); ?>">
													Ngành <?php echo esc_html( $major['name'] ); ?>
												</a>
											</h3>

											<!-- Highlights Info Row -->
											<?php if ( $programs_count === 1 && $first_prog ) : ?>
												<div class="flex items-center gap-2.5 text-xs text-slate-500 mb-3">
													<span class="inline-flex items-center gap-1 font-medium">
														<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
															<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
														</svg>
														<span><?php echo esc_html( $first_prog['duration'] ?: '1.5 - 2 năm' ); ?></span>
													</span>
													<span class="text-slate-300">•</span>
													<span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
														<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
														<span>Tuyển sinh 2025</span>
													</span>
												</div>
											<?php elseif ( $programs_count > 1 ) : ?>
												
											<?php endif; ?>
										</div>

										<!-- Action CTA Buttons Footer -->
										<div class="pt-3 border-t border-slate-100 flex items-center gap-2 mt-auto">
											<a href="<?php echo esc_url( $primary_link ); ?>" 
											   class="flex-1 py-2 px-3 rounded-xl text-xs sm:text-sm font-bold text-center text-[#00308b] bg-blue-50/90 hover:bg-[#00308b] hover:text-white border border-blue-200/80 hover:border-[#00308b] transition-all flex items-center justify-center gap-1 shadow-2xs group-hover/btn:shadow-sm whitespace-nowrap">
												<span>Xem chi tiết ngành</span>
												<svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
													<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
												</svg>
											</a>
										</div>
									</div>
								</div>
								<?php
							endforeach;
							?>

							<!-- Empty Search Results State -->
							<div id="ltdh-majors-empty-state" class="hidden col-span-full text-center py-12 bg-white border border-slate-200/80 rounded-2xl p-6">
								<div class="w-12 h-12 bg-blue-50 text-[#00308b] rounded-full flex items-center justify-center mx-auto mb-3 text-xl">🔍</div>
								<h3 class="text-base font-bold text-slate-800 mb-1">Không tìm thấy ngành đào tạo phù hợp</h3>
								<p class="text-xs text-slate-500 mb-4">Vui lòng thử tìm với từ khóa khác hoặc xóa bộ lọc.</p>
								<button type="button" id="ltdh-major-reset-search" class="px-4 py-2 bg-[#00308b] text-white rounded-xl text-xs font-bold hover:bg-blue-800 transition-colors cursor-pointer">
									Xem tất cả ngành
								</button>
							</div>
						</div>

						<!-- Instant Search & Filter Script -->
						<script>
						(function() {
							var searchInput = document.getElementById('ltdh-major-search-input');
							var searchClear = document.getElementById('ltdh-major-search-clear');
							var tabButtons = document.querySelectorAll('.ltdh-he-tab-btn');
							var cards = document.querySelectorAll('.ltdh-major-card');
							var emptyState = document.getElementById('ltdh-majors-empty-state');
							var resetBtn = document.getElementById('ltdh-major-reset-search');
							var badge = document.getElementById('ltdh-majors-total-badge');

							if (!cards.length) return;

							var activeHe = 'all';
							var searchQuery = '';

							function filterCards() {
								var visibleCount = 0;
								var q = searchQuery.trim().toLowerCase();

								cards.forEach(function(card) {
									var name = card.getAttribute('data-major-name') || '';
									var code = card.getAttribute('data-major-code') || '';
									var heList = (card.getAttribute('data-major-he') || '').split(' ');

									var matchesSearch = !q || name.indexOf(q) !== -1 || code.indexOf(q) !== -1;
									var matchesHe = (activeHe === 'all') || heList.indexOf(activeHe) !== -1;

									if (matchesSearch && matchesHe) {
										card.style.display = '';
										visibleCount++;
									} else {
										card.style.display = 'none';
									}
								});

								if (emptyState) {
									emptyState.classList.toggle('hidden', visibleCount > 0);
								}

								if (badge) {
									badge.textContent = visibleCount + ' ngành tuyển sinh';
								}

								if (searchClear) {
									searchClear.classList.toggle('hidden', !searchQuery);
								}
							}

							if (searchInput) {
								searchInput.addEventListener('input', function() {
									searchQuery = this.value;
									filterCards();
								});
							}

							if (searchClear) {
								searchClear.addEventListener('click', function() {
									if (searchInput) {
										searchInput.value = '';
										searchQuery = '';
										searchInput.focus();
										filterCards();
									}
								});
							}

							tabButtons.forEach(function(btn) {
								btn.addEventListener('click', function() {
									tabButtons.forEach(function(b) {
										b.classList.remove('active', 'bg-[#00308b]', 'text-white', 'shadow-2xs');
										b.classList.add('bg-slate-100', 'text-slate-700');
									});
									this.classList.remove('bg-slate-100', 'text-slate-700');
									this.classList.add('active', 'bg-[#00308b]', 'text-white', 'shadow-2xs');

									activeHe = this.getAttribute('data-he-filter') || 'all';
									filterCards();
								});
							});

							if (resetBtn) {
								resetBtn.addEventListener('click', function() {
									if (searchInput) searchInput.value = '';
									searchQuery = '';
									activeHe = 'all';
									tabButtons.forEach(function(b) {
										var isAll = (b.getAttribute('data-he-filter') === 'all');
										b.classList.toggle('active', isAll);
										b.classList.toggle('bg-[#00308b]', isAll);
										b.classList.toggle('text-white', isAll);
										b.classList.toggle('shadow-2xs', isAll);
										b.classList.toggle('bg-slate-100', !isAll);
										b.classList.toggle('text-slate-700', !isAll);
									});
									filterCards();
								});
							}
						})();
						</script>
					<?php
					else :
						echo '<p class="text-sm text-slate-500 py-4">Hiện tại chưa có chương trình nào được cập nhật cho trường này.</p>';
					endif;
					?>
				</section>

				
				<!-- ADMISSION PROCESS -->
				<?php
				get_template_part(
					'template-parts/admission-process',
					null,
					[
						'title'      => 'Quy trình tuyển sinh & Nhập học',
						'subtitle'   => 'Lộ trình 4 bước nộp hồ sơ xét tuyển và làm thủ tục nhập học chính thức',
						'section_id' => 'quy-trinh-tuyen-sinh',
						'cta_text'   => 'Đăng ký tư vấn chọn ngành ngay',
						'cta_link'   => '/kiem-tra-dieu-kien',
					]
				);
				?>
				<!-- CONTACT INFO -->
				<?php if ( $contact ) : ?>
					<section id="thong-tin-lien-he" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<!-- Hidden anchor targets for backward compatibility -->
						<span id="phuong-thuc-tuyen-sinh" class="relative -top-36 block invisible pointer-events-none"></span>
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 md:pb-4 mb-4">Thông tin liên hệ tuyển sinh</h2>
						<div class="prose prose-slate max-w-none text-slate-700 text-sm leading-relaxed prose-card-list">
							<?php echo wp_kses_post( ltdh_format_contact_info( (string) $contact, (int) $school_id ) ); ?>
						</div>
					</section>
				<?php endif; ?>
			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<div class="sticky top-36 md:top-40 space-y-6">
					
					<!-- SCHOOL INFO CARD (Sidebar Card matching Image 2) -->
					<?php 
					$school_logo_url  = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'medium' ) : '';
					$school_cover_url = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'medium_large' ) : ( get_field( 'school_banner', $school_id ) ?: get_template_directory_uri() . '/assets/images/banner-school.jpg' );
					$map_url          = get_field( 'google_map_url', $school_id ) ?: get_field( 'map_url', $school_id );
					if ( ! $map_url ) {
						$map_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $school_title . ' ' . ( $address ?: '' ) );
					}
					
					// Typographic initials fallback
					$words = explode( ' ', $school_title );
					$initials = '';
					foreach ( array_slice( $words, -3 ) as $w ) {
						$initials .= mb_substr( $w, 0, 1 );
					}
					$initials = mb_strtoupper( $initials );
					?>
					<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs hover:shadow-sm transition-all">
						<!-- Banner Cover -->
						<div class="h-28 sm:h-32 bg-gradient-to-r from-[#00308b] to-[#001a4d] bg-cover bg-center relative" <?php echo $school_cover_url ? 'style="background-image: url(\'' . esc_url( $school_cover_url ) . '\');"' : ''; ?>>
							<div class="absolute inset-0 bg-blue-950/20"></div>
						</div>
						
						<!-- Overlapping Logo Wrapper -->
						<div class="relative flex justify-center -mt-10 mb-3">
							<div class="w-20 h-20 bg-white p-1 rounded-2xl shadow-md border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
								<?php if ( $school_logo_url ) : ?>
									<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_title ); ?>" class="max-w-full max-h-full object-contain" loading="lazy">
								<?php else : ?>
									<div class="w-full h-full bg-[#00308b] text-white flex items-center justify-center font-black text-base rounded-xl uppercase tracking-wider">
										<?php echo esc_html( $initials ?: 'ĐH' ); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
						
						<!-- School Info details -->
						<div class="px-5 pb-5 text-center">
							<h1 class="font-extrabold text-slate-800 text-sm sm:text-base leading-snug uppercase tracking-tight mb-2">
								<?php echo esc_html( $school_title ); ?>
							</h1>
							
							<?php if ( $website ) : ?>
								<p class="text-xs text-slate-500 mb-2 font-medium">
									Website: <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" class="text-[#00308b] hover:underline font-semibold"><?php echo esc_html( $website ); ?></a>
								</p>
							<?php endif; ?>

							<div class="flex items-start justify-center gap-1.5 text-xs text-slate-500 font-medium max-w-xs mx-auto mb-3.5">
								<span class="text-[#00308b] shrink-0 mt-0.5">📍</span>
								<span class="text-left leading-relaxed">
									Địa chỉ: <?php echo esc_html( $address ?: 'Chưa cập nhật' ); ?>
									<?php if ( $map_url ) : ?>
										<a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer" class="text-[#00308b] font-bold hover:underline ml-0.5 inline-block">(Xem bản đồ)</a>
									<?php endif; ?>
								</span>
							</div>

							<div class="border-t border-slate-100 pt-3">
								<a href="#gioi-thieu" class="text-[#00308b] font-bold text-xs sm:text-sm hover:underline flex items-center justify-center gap-1">
									<span>Xem chi tiết trường</span> <span>→</span>
								</a>
							</div>
						</div>
					</div>

					<!-- CONSULTATION FORM -->
					<section id="register" class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
						<h3 class="text-base font-extrabold text-slate-900 mb-1">Đăng ký vào <?php the_title(); ?></h3>
						<p class="text-sm text-slate-500 mb-4">Nhận tư vấn hồ sơ miễn phí, hỗ trợ xử lý thủ tục nhập học nhanh chóng.</p>
						
						<?php 
						ltdh_render_consultation_form( [
							'current_school_id' => $school_id,
							'referral_source'   => get_permalink(),
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
								'key'     => 'related_schools',
								'value'   => '"' . $school_id . '"',
								'compare' => 'LIKE',
							],
						],
					] );

					if ( $related_news_query->have_posts() ) :
						$has_more = ( $related_news_query->post_count > 5 );
					?>
						<section class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
							<h3 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-3">Tin tức & Thông báo mới</h3>
							<div class="space-y-3.5">
								<?php
								$news_counter = 0;
								while ( $related_news_query->have_posts() ) :
									$related_news_query->the_post();
									if ( $news_counter >= 5 ) {
										continue;
									}
									$news_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( get_the_ID(), 'thumbnail' ) : ( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ?: ltdh_get_fallback_image( 'post' ) );
									$news_counter++;
								?>
									<div class="flex gap-3 items-start pb-3 border-b border-slate-100 last:border-b-0 last:pb-0">
										<a href="<?php the_permalink(); ?>" class="shrink-0">
											<img src="<?php echo esc_url( $news_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-12 h-12 object-cover rounded border border-slate-100" loading="lazy">
										</a>
										
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
									<a href="<?php echo esc_url( home_url( '/tin-tuc/?truong=' . $school_id ) ); ?>" class="w-full text-center bg-slate-50 border border-slate-200 text-slate-700 py-2.5 rounded-lg font-bold text-sm hover:bg-slate-100 transition-all flex items-center justify-center gap-1.5 min-h-[38px]">
										<span>Xem thêm tin tức</span>
										<span>→</span>
									</a>
								</div>
							<?php endif; ?>
						</section>
					<?php
					endif;
					?>
 
					<!-- ZALO GROUP DISCUSSION COMMUNITY CARD -->
					<div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-xl p-5 shadow-md relative overflow-hidden border border-blue-500/30">
						<div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

						<div class="flex items-center gap-3 mb-3 relative z-10">
							<div class="w-10 h-10 rounded-xl bg-white text-blue-600 flex items-center justify-center font-black text-xl shadow-xs shrink-0">
								💬
							</div>
							<div>
								<span class="text-[11px] uppercase font-bold tracking-wider text-blue-200 block">Cộng đồng sinh viên</span>
								<h3 class="font-extrabold text-base text-white leading-tight">Nhóm Zalo Trao Đổi</h3>
							</div>
						</div>

						<p class="text-xs text-blue-100 leading-relaxed mb-4 relative z-10">
							Tham gia nhóm Zalo trao đổi thông tin tuyển sinh, lịch học và chia sẻ kinh nghiệm cùng cựu sinh viên <?php echo esc_html( $school_title ); ?>.
						</p>

						<ul class="space-y-1.5 text-xs text-blue-50 font-medium mb-4 relative z-10">
							<li class="flex items-center gap-2">
								<span class="text-emerald-300 font-bold">✓</span>
								<span>Cập nhật thông báo tuyển sinh mới nhất</span>
							</li>
							<li class="flex items-center gap-2">
								<span class="text-emerald-300 font-bold">✓</span>
								<span>Giải đáp thắc mắc hồ sơ 24/7</span>
							</li>
						</ul>

						<a href="<?php echo esc_url( $zalo_group ); ?>"
						   target="_blank"
						   rel="noopener noreferrer"
						   class="w-full bg-white hover:bg-blue-50 text-blue-700 font-extrabold text-sm py-3 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 min-h-[44px] relative z-10 hover:scale-[1.02]">
							<span>💬 Tham gia Nhóm Zalo</span>
							<span class="text-xs">→</span>
						</a>
					</div>

					<!-- CONTACT INFO CARD -->
					<?php
					get_template_part( 'template-parts/sidebar-hotline', null, [
						'hotline'  => $hotline,
						'zalo_url' => $global_zalo,
						'title'    => 'Văn phòng tuyển sinh',
					] );
					?>

				</div>
			</div>
		</div>
	</div>
</main>

<!-- SCROLLSPY & SMOOTH SCROLL SCRIPT -->
<script>
(function() {
	document.addEventListener('DOMContentLoaded', function() {
		var stickyNav = document.getElementById('ltdh-school-sticky-nav');
		if (!stickyNav) return;

		var navContainer = stickyNav.querySelector('nav');
		var tabLinks = Array.prototype.slice.call(stickyNav.querySelectorAll('.ltdh-section-tab-link'));
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
				var offsetPosition = elementPosition - totalOffset + 8; // gentle breathing room

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
