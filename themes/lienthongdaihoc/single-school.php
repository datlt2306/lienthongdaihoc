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
	'id'       => 'nganh-dao-tao',
	'title'    => 'Ngành đào tạo',
	'subtitle' => 'Chuyên ngành nổi bật',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" /></svg>',
];

if ( ! empty( $adm_info ) ) {
	$school_tabs[] = [
		'id'       => 'phuong-thuc-tuyen-sinh',
		'title'    => 'Xét tuyển',
		'subtitle' => 'Phương thức tuyển sinh',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
	];
}

$school_tabs[] = [
	'id'       => 'chuong-trinh-tuyen-sinh',
	'title'    => 'Lớp tuyển sinh',
	'subtitle' => 'Chương trình đang mở',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>',
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
		
		<!-- HERO SECTION -->
		<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-6 md:p-8 mb-6">
			<div class="flex flex-col md:flex-row gap-6 items-center md:items-start text-center md:text-left">
				<?php ltdh_render_school_thumbnail( $school_id, 'medium', 'h-24 w-24 object-cover shrink-0 rounded-lg border border-slate-100 bg-white' ); ?>
				
				<div class="flex-1 space-y-2">
					<h1 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight"><?php the_title(); ?></h1>
					<p class="text-slate-500 text-sm">Địa chỉ: <?php echo esc_html( $address ?: 'Chưa cập nhật' ); ?></p>
					<?php if ( $website ) : ?>
						<p class="text-sm text-slate-400">Website chính thức: <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" class="text-brand-primary hover:underline font-bold"><?php echo esc_html( $website ); ?></a></p>
					<?php endif; ?>
					<?php
					$map_url = get_field( 'google_map_url', $school_id ) ?: get_field( 'map_url', $school_id );
					if ( ! $map_url ) {
						$map_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $school_title . ' ' . ( $address ?: '' ) );
					}
					?>
					<p class="text-sm text-slate-400">Bản đồ: <a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer" class="text-brand-accent hover:underline font-bold">📍 Xem đường đi trên Google Maps</a></p>
				</div>

				<div class="flex flex-col gap-2 w-full md:w-auto">
					<a href="#register" class="bg-brand-accent text-white text-center px-6 py-3 rounded-lg font-bold shadow-md hover:bg-[#e06e00] transition-all text-sm min-h-[44px] flex items-center justify-center">Đăng Ký Nhận Tư Vấn</a>
					<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="border border-slate-200 text-slate-700 text-center px-6 py-3 rounded-lg font-semibold hover:bg-slate-50 transition-all text-sm min-h-[44px] flex items-center justify-center">Hotline: <?php echo esc_html( $hotline ); ?></a>
				</div>
			</div>
		</section>

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

				<!-- MAJORS OFFERED -->
				<section id="nganh-dao-tao" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 md:pb-4 mb-4">Các ngành đào tạo phổ biến</h2>
					<?php
					// Query distinct majors via the programs offered by this school
					$distinct_major_ids = [];
					if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
						foreach ( $offered_program_ids as $p_id ) {
							$m_id = get_post_meta( $p_id, 'major_relationship', true );
							if ( is_array( $m_id ) ) {
								$m_id = ! empty( $m_id ) ? $m_id[0] : 0;
							}
							$m_id = intval( $m_id );
							if ( $m_id && ! in_array( $m_id, $distinct_major_ids ) ) {
								$distinct_major_ids[] = $m_id;
							}
						}
					}
					
					if ( empty( $distinct_major_ids ) ) {
						$linked_programs = get_posts( [
							'post_type'      => 'program',
							'posts_per_page' => -1,
							'meta_query'     => [
								[
									'key'     => LTDH_META_SCHOOL_REL,
									'value'   => $school_id,
									'compare' => '=',
								],
							],
							'fields'         => 'ids',
						] );
						if ( ! empty( $linked_programs ) ) {
							update_meta_cache( 'post', $linked_programs );
							foreach ( $linked_programs as $p_id ) {
								$m_id = get_post_meta( $p_id, 'major_relationship', true );
								if ( is_array( $m_id ) ) {
									$m_id = ! empty( $m_id ) ? $m_id[0] : 0;
								}
								$m_id = intval( $m_id );
								if ( $m_id && ! in_array( $m_id, $distinct_major_ids ) ) {
									$distinct_major_ids[] = $m_id;
								}
							}
						}
					}
					
					if ( ! empty( $distinct_major_ids ) ) {
						$majors_query = new WP_Query( [
							'post_type' => 'major',
							'post__in'  => $distinct_major_ids,
							'post_status' => 'publish'
						] );
					} else {
						$majors_query = false;
					}

					if ( $majors_query && $majors_query->have_posts() ) :
						echo '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">';
						while ( $majors_query->have_posts() ) : $majors_query->the_post();
						?>
							<a href="<?php the_permalink(); ?>" class="flex items-center gap-3 p-3 border border-slate-100 rounded-lg hover:border-brand-primary hover:shadow-sm transition-all bg-white">
								<?php 
								$major_thumb = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
								if ( ! $major_thumb ) {
									$major_thumb = ltdh_get_fallback_image( 'program' );
								}
								?>
								<img src="<?php echo esc_url( $major_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="h-12 w-12 rounded-lg object-cover shrink-0 bg-slate-50 border border-slate-100" loading="lazy">
								<div class="min-w-0 flex-1">
									<h4 class="font-bold text-slate-800 text-sm mb-0.5 truncate"><?php the_title(); ?></h4>
									<span class="text-sm text-slate-400 block">Mã ngành: <?php echo esc_html( get_field( 'major_code' ) ?: 'Đang cập nhật' ); ?></span>
								</div>
							</a>
						<?php
						endwhile;
						echo '</div>';
						wp_reset_postdata();
					else :
						echo '<p class="text-sm text-slate-500">Các chuyên ngành chính của trường đang được cập nhật.</p>';
					endif;
					?>
				</section>

				<!-- ADMISSION INFORMATION -->
				<?php if ( $adm_info ) : ?>
					<section id="phuong-thuc-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 md:pb-4 mb-4">Phương thức tuyển sinh</h2>
						<div class="prose prose-slate max-w-none text-slate-600 text-sm prose-card-list">
							<?php echo wp_kses_post( $adm_info ); ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- PROGRAMS OFFERED -->
				<section id="chuong-trinh-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 md:pb-4 mb-4">Chương trình tuyển sinh đang mở</h2>
					
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

						while ( $programs_query->have_posts() ) {
							$programs_query->the_post();
							$prog_id = get_the_ID();
							$major_rel_id = get_field( LTDH_META_MAJOR_REL, $prog_id );
							$major_key = $major_rel_id ? $major_rel_id : 'no_major_' . $prog_id;

							if ( ! isset( $majors_data[ $major_key ] ) ) {
								$major_name = $major_rel_id ? get_the_title( $major_rel_id ) : 'Mời tư vấn';
								$major_thumb = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium' ) : '';
								if ( ! $major_thumb ) {
									$major_thumb = ltdh_get_fallback_image( 'program' );
								}

								$majors_data[ $major_key ] = [
									'id'       => $major_rel_id,
									'name'     => $major_name,
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
							$clean_type_name = preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) );
							$clean_major_name = preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) );
							$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );
							$tuition_fee = ltdh_get_program_tuition_display( $prog_id );
							$duration = get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm';
							$permalink = get_permalink( $prog_id );

							// Determine badge classes based on training type name
							$badge_class = 'bg-orange-50 text-orange-600 border border-orange-100';
							if ( $clean_type_name ) {
								$type_name_lower = mb_strtolower( trim( $clean_type_name ), 'UTF-8' );
								if ( false !== strpos( $type_name_lower, 'chính quy' ) ) {
									$badge_class = 'bg-blue-50 text-blue-600 border border-blue-100';
								} elseif ( false !== strpos( $type_name_lower, 'từ xa' ) ) {
									$badge_class = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
								} elseif ( false !== strpos( $type_name_lower, 'vừa học vừa làm' ) || false !== strpos( $type_name_lower, 'vừa làm vừa học' ) || false !== strpos( $type_name_lower, 'liên thông' ) || false !== strpos( $type_name_lower, 'văn bằng 2' ) ) {
									$badge_class = 'bg-amber-50 text-amber-600 border border-amber-100';
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
							];
						}
						wp_reset_postdata();
					}

					if ( ! empty( $majors_data ) ) :
						echo '<div class="space-y-6">';
						foreach ( $majors_data as $major ) :
							if ( count( $major['programs'] ) > 1 ) :
								// Grouped layout for majors with 2+ programs at this school
								?>
								<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all p-4 md:p-5 flex flex-col gap-4">
									<!-- Major Primary Info Header -->
									<div class="flex items-center gap-3">
										<div class="h-9 w-9 sm:h-11 sm:w-11 bg-slate-200 bg-cover bg-center rounded-lg shrink-0 border border-slate-100 shadow-xs" style="background-image: url('<?php echo esc_url( $major['thumb'] ); ?>'); font-size: 0;"></div>
										<div class="space-y-0.5 flex-1 min-w-0">
											<h4 class="font-bold text-slate-800 text-xs sm:text-sm hover:text-[#00308b] transition-colors leading-snug">
												<?php if ( $major['id'] ) : ?>
													<a href="<?php echo esc_url( get_permalink( $major['id'] ) ); ?>">
														Ngành <?php echo esc_html( $major['name'] ); ?>
													</a>
												<?php else : ?>
													<span class="font-semibold text-slate-700"><?php echo esc_html( $major['name'] ); ?></span>
												<?php endif; ?>
											</h4>
										</div>
									</div>

									<!-- Programs Offered under this Major -->
									<div class="border-t border-slate-100 pt-3">
										<div class="flex items-center justify-between gap-2 mb-2.5">
											<div class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Hình thức đào tạo:</div>
											<?php 
											$header_year = ! empty( $major['programs'][0]['academic_year'] ) ? $major['programs'][0]['academic_year'] : '2025 - 2026';
											?>
											<span class="text-[11px] font-semibold text-slate-500 bg-slate-100/90 px-2 py-0.5 rounded border border-slate-200/50 whitespace-nowrap shrink-0">
												Biểu phí <?php echo esc_html( $header_year ); ?>
											</span>
										</div>
										<div class="divide-y divide-slate-100/80 space-y-1 sm:space-y-0">
											<?php foreach ( $major['programs'] as $prog ) : ?>
												<div class="flex items-center justify-between gap-2.5 py-2.5 sm:py-3 rounded-xl hover:bg-slate-50/90 transition-all text-xs sm:text-sm my-0.5">
													<div class="flex items-center gap-2.5 sm:gap-5 min-w-0 flex-1">
														<?php if ( $prog['type_name'] ) : ?>
															<div class="w-[105px] sm:w-[125px] shrink-0">
																<span class="<?php echo esc_attr( $prog['badge_class'] ); ?> inline-block w-full text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider text-center">
																	<?php echo esc_html( $prog['type_name'] ); ?>
																</span>
															</div>
														<?php endif; ?>
														<div class="min-w-0 flex-1">
															<h5 class="font-bold text-slate-800 text-xs sm:text-sm hover:text-brand-primary truncate leading-snug">
																<a href="<?php echo esc_url( $prog['permalink'] ); ?>">
																	<?php echo esc_html( $prog['opportunity_title'] ); ?>
																</a>
															</h5>
															<div class="flex flex-col sm:flex-row sm:items-center gap-x-5 gap-y-0.5 text-slate-600 min-w-0 flex-1 mt-0.5">
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
								// Single-row layout for majors with only 1 program
								$prog = $major['programs'][0];
								?>
								<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all p-4 md:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
									<div class="flex items-start gap-3 flex-1 min-w-0">
										<div class="h-10 w-10 sm:h-12 sm:w-12 bg-slate-200 bg-cover bg-center rounded-lg shrink-0 border border-slate-100" style="background-image: url('<?php echo esc_url( $major['thumb'] ); ?>'); font-size: 0;"></div>
										<div class="space-y-1 flex-1 min-w-0">
											<div class="flex items-center gap-2 flex-wrap">
												<h4 class="font-bold text-slate-800 text-sm sm:text-base hover:text-[#00308b] transition-colors leading-snug">
													<a href="<?php echo esc_url( $prog['permalink'] ); ?>">
														<?php echo esc_html( $prog['opportunity_title'] ); ?>
													</a>
												</h4>
												<?php if ( $prog['type_name'] ) : ?>
													<span class="<?php echo esc_attr( $prog['badge_class'] ); ?> text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider">
														<?php echo esc_html( $prog['type_name'] ); ?>
													</span>
												<?php endif; ?>
											</div>
											<div class="flex flex-wrap gap-x-4 gap-y-1 text-xs sm:text-sm text-slate-500 pt-0.5">
												<p>Chuyên ngành: 
													<?php if ( $major['id'] ) : ?>
														<a href="<?php echo esc_url( get_permalink( $major['id'] ) ); ?>" class="font-bold text-brand-primary hover:underline"><?php echo esc_html( $major['name'] ); ?></a>
													<?php else : ?>
														<span class="font-semibold text-slate-700"><?php echo esc_html( $major['name'] ); ?></span>
													<?php endif; ?>
												</p>
												<p class="hidden sm:inline text-slate-300">|</p>
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
						echo '<p class="text-sm text-slate-500">Hiện tại chưa có chương trình nào được cập nhật cho trường này.</p>';
					endif;
					?>
				</section>

				<!-- CONTACT INFO -->
				<?php if ( $contact ) : ?>
					<section id="thong-tin-lien-he" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 md:pb-4 mb-4">Thông tin liên hệ tuyển sinh</h2>
						<div class="prose prose-slate max-w-none text-slate-600 text-sm prose-card-list">
							<?php echo wp_kses_post( $contact ); ?>
						</div>
					</section>
				<?php endif; ?>
			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<div class="sticky top-36 md:top-40 space-y-6">
					
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
					<div class="relative bg-gradient-to-tr from-[#0E2038] to-brand-primary text-white rounded-lg p-6 text-center shadow-lg overflow-hidden border border-slate-800">
						<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 16px 16px;"></div>
						<span class="text-sm text-brand-accent font-extrabold uppercase tracking-wider block mb-1">Văn phòng tuyển sinh</span>
						<h4 class="font-display font-black text-xl md:text-2xl mb-4"><?php echo esc_html( $hotline ); ?></h4>
						<div class="flex gap-2 relative z-10">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex-1 bg-brand-accent text-white py-3.5 rounded-lg font-bold text-sm hover:bg-[#e06e00] transition-all min-h-[44px] flex items-center justify-center shadow-sm shadow-brand-accent/20">Gọi Ngay</a>
							<a href="<?php echo esc_url( $global_zalo ); ?>" class="flex-1 bg-white/10 text-white border border-white/20 py-3.5 rounded-lg font-bold text-sm hover:bg-white/20 transition-all min-h-[44px] flex items-center justify-center">Chat Zalo</a>
						</div>
					</div>

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
