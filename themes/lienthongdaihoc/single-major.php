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

// Query parameters for filter & context tracking
$selected_he        = isset( $_GET['he'] ) ? sanitize_text_field( wp_unslash( $_GET['he'] ) ) : '';
$available_he_types = function_exists( 'ltdh_get_major_training_types' ) ? ltdh_get_major_training_types( (int) $major_id ) : [];

// If requested filter he does not exist in available training types, reset to empty
if ( ! empty( $selected_he ) && ! empty( $available_he_types ) ) {
	$matched_he = false;
	foreach ( $available_he_types as $he_slug => $he_info ) {
		if ( $he_slug === $selected_he || ( 'tu-xa' === $he_slug && 'dao-tao-tu-xa' === $selected_he ) ) {
			$matched_he = true;
			break;
		}
	}
	if ( ! $matched_he ) {
		$selected_he = '';
	}
}

$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );

$context_school       = null;
$selected_school_slug = '';

if ( ! empty( $school_param ) ) {
	if ( is_numeric( $school_param ) ) {
		$context_school = get_post( intval( $school_param ) );
	} else {
		$context_school = get_page_by_path( sanitize_title( $school_param ), OBJECT, LTDH_CPT_SCHOOL );
		if ( ! $context_school ) {
			$school_by_code = get_posts( [
				'post_type'   => LTDH_CPT_SCHOOL,
				'post_status' => 'publish',
				'numberposts' => 1,
				'meta_query'  => [
					[
						'key'     => 'school_code',
						'value'   => sanitize_text_field( $school_param ),
						'compare' => '=',
					],
				],
			] );
			if ( ! empty( $school_by_code ) ) {
				$context_school = $school_by_code[0];
			}
		}
	}
	if ( $context_school && 'publish' === $context_school->post_status ) {
		$selected_school_slug = $context_school->post_name;
	} else {
		$selected_school_slug = sanitize_title( $school_param );
	}
}

// Retrieve pre-calculated list of programs matching this major (Formula: 'Liên thông ngành ' . $clean_major_name, $prog['opportunity_title'], preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) ))
$offered_program_ids = get_post_meta( $major_id, LTDH_META_OFFERED_PROGRAMS, true );

// Retrieve all distinct schools offering programs for this major
$direct_major_programs = get_posts( [
	'post_type'   => LTDH_CPT_PROGRAM,
	'post_status' => 'publish',
	'numberposts' => -1,
	'meta_query'  => [
		'relation' => 'OR',
		[
			'key'     => LTDH_META_MAJOR_REL,
			'value'   => $major_id,
			'compare' => '=',
		],
		[
			'key'     => LTDH_META_MAJOR_REL,
			'value'   => '"' . $major_id . '"',
			'compare' => 'LIKE',
		],
		[
			'key'     => LTDH_META_MAJOR_REL,
			'value'   => 's:' . strlen( (string) $major_id ) . ':"' . $major_id . '"',
			'compare' => 'LIKE',
		],
		[
			'key'     => LTDH_META_MAJOR_REL,
			'value'   => 'i:' . $major_id . ';',
			'compare' => 'LIKE',
		],
	],
	'fields'      => 'ids',
] );

$all_major_programs = array_unique( array_filter( array_merge(
	is_array( $offered_program_ids ) ? $offered_program_ids : [],
	is_array( $direct_major_programs ) ? $direct_major_programs : []
) ) );

$global_zalo = ltdh_get_zalo_url();
$hotline     = ltdh_get_hotline();

// Chỉ hiển thị khối đối tượng tuyển sinh (ngành đúng & ngành gần) khi truy cập ngành đúng với trường có quy định đó.
// Bình thường (không có tham số trường hoặc trường không có quy định ngành gần) thì không hiển thị.
$active_target_admission = null;

if ( ! empty( $school_param ) ) {
	$target_admissions_list = function_exists( 'ltdh_get_major_target_admissions' ) ? ltdh_get_major_target_admissions( (int) $major_id ) : [];

	if ( ! empty( $target_admissions_list ) ) {
		$target_school_matcher = function( array $target_item, $school, string $param, string $selected_slug ): bool {
			if ( empty( $target_item ) ) {
				return false;
			}

			$target_school_id    = (int) ( $target_item['school_id'] ?? 0 );
			$target_school_slug  = sanitize_title( (string) ( $target_item['school_slug'] ?? '' ) );
			$target_school_badge = strtoupper( trim( (string) ( $target_item['school_badge'] ?? '' ) ) );

			$param_clean = sanitize_title( $param );
			$param_code  = strtoupper( trim( $param ) );

			// 1. By School Post ID
			if ( $school && $target_school_id > 0 && (int) $school->ID === $target_school_id ) {
				return true;
			}
			if ( is_numeric( $param ) && $target_school_id > 0 && (int) $param === $target_school_id ) {
				return true;
			}

			// 2. By School Code (e.g. UTC, TNU, NEU)
			if ( $school ) {
				$school_code = strtoupper( trim( (string) get_post_meta( $school->ID, 'school_code', true ) ) );
				if ( ! empty( $school_code ) && ! empty( $target_school_badge ) && $school_code === $target_school_badge ) {
					return true;
				}
			}
			if ( ! empty( $target_school_badge ) && ( $param_code === $target_school_badge || str_contains( $param_code, $target_school_badge ) ) ) {
				return true;
			}

			// 3. By School Slug
			$slugs_to_check = array_filter( [
				$selected_slug ? sanitize_title( $selected_slug ) : '',
				$school ? sanitize_title( $school->post_name ) : '',
				$param_clean,
			] );

			foreach ( $slugs_to_check as $chk_slug ) {
				if ( ! empty( $target_school_slug ) && ( $chk_slug === $target_school_slug || str_contains( $chk_slug, $target_school_slug ) || str_contains( $target_school_slug, $chk_slug ) ) ) {
					return true;
				}
			}

			return false;
		};

		foreach ( $target_admissions_list as $t_candidate ) {
			if ( $target_school_matcher( $t_candidate, $context_school, $school_param, $selected_school_slug ) ) {
				$active_target_admission = $t_candidate;
				break;
			}
		}
	}
}

$major_tabs = [];

$major_tabs[] = [
	'id'       => 'tong-quan',
	'title'    => 'Tổng quan',
	'subtitle' => 'Giới thiệu ngành',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>',
];

$major_tabs[] = [
	'id'       => 'truong-tuyen-sinh',
	'title'    => 'Trường tuyển sinh',
	'subtitle' => 'Bộ lọc & Các trường',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>',
];

if ( ! empty( $active_target_admission ) ) {
	$major_tabs[] = [
		'id'       => 'doi-tuong-tuyen-sinh',
		'title'    => 'Đối tượng tuyển sinh',
		'subtitle' => 'Ngành đúng & ngành gần',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>',
	];
}

$major_tabs[] = [
	'id'       => 'quy-trinh-tuyen-sinh',
	'title'    => 'Quy trình',
	'subtitle' => '4 bước xét tuyển',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
$major_curriculum = function_exists( 'ltdh_get_major_curriculum_info' ) ? ltdh_get_major_curriculum_info( (int) $major_id, $context_school ?: $school_param ) : null;

if ( ! empty( $major_curriculum['url'] ) ) {
	$major_tabs[] = [
		'id'       => 'khung-chuong-trinh',
		'title'    => 'Khung đào tạo',
		'subtitle' => 'Môn học & Lộ trình',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>',
	];
}

$major_faqs = function_exists( 'ltdh_get_major_faqs' ) ? ltdh_get_major_faqs( (int) $major_id, $context_school ? $context_school->ID : null ) : [];

if ( ! empty( $major_faqs ) ) {
	$major_tabs[] = [
		'id'       => 'hoi-dap-nganh',
		'title'    => 'Hỏi đáp (FAQ)',
		'subtitle' => $context_school ? 'Hỏi đáp tuyển sinh' : 'Cẩm nang định hướng',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>',
	];
}

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
	<?php 
	get_template_part( 
		'template-parts/banner', 
		null, 
		[ 
			'school'      => $context_school,
			'selected_he' => $selected_he,
		] 
	); 
	?>

	<!-- STICKY SECTION TAB NAVIGATION (Redesigned with UI/UX Pro Max) -->
	<div id="ltdh-major-sticky-nav" class="ltdh-sticky-nav sticky top-16 sm:top-20 z-40 bg-white/98 backdrop-blur-md border-b border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.06)] transition-all duration-200">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<nav class="flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden py-2" aria-label="Điều hướng các mục ngành đào tạo">
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
					<?php 
					$clean_m_name = preg_replace( '/^ngành\s+/iu', '', trim( (string) get_the_title( $major_id ) ) );
					$major_overview_raw = get_field( 'major_overview', $major_id );
					$overview_markup    = ( ! empty( $major_overview_raw ) && is_string( $major_overview_raw ) && '' !== trim( $major_overview_raw ) ) 
						? $major_overview_raw 
						: get_the_content( null, false, $major_id );
					?>
					<div class="relative">
						<div id="major-intro-content" class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base overflow-hidden transition-all duration-500 max-h-[350px] relative">
							<?php echo apply_filters( 'the_content', $overview_markup ); ?>
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

						<!-- FLOATING STICKY COLLAPSE PILL (Active when expanded & scrolling content) -->
						<div id="major-intro-sticky-bar" class="fixed bottom-[calc(4.75rem+env(safe-area-inset-bottom,0px))] md:bottom-8 z-40 transition-all duration-300 opacity-0 pointer-events-none translate-y-4 -translate-x-1/2">
							<button type="button" id="major-intro-sticky-btn" class="group flex items-center gap-2.5 px-6 py-2.5 rounded-full bg-[#00308b] text-white hover:bg-[#002266] text-sm font-bold shadow-[0_12px_30px_-4px_rgba(0,48,139,0.45),0_6px_12px_-4px_rgba(0,0,0,0.15)] ring-4 ring-white transition-all duration-200 focus:outline-none active:scale-95 cursor-pointer">
								<span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center shrink-0 transition-transform group-hover:-translate-y-0.5">
									<svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
									</svg>
								</span>
								<span class="tracking-wide">Thu gọn</span>
							</button>
						</div>
					</div>

					<script>
					(function() {
						var section = document.getElementById('tong-quan');
						var content = document.getElementById('major-intro-content');
						var overlay = document.getElementById('major-intro-overlay');
						var container = document.getElementById('major-intro-btn-container');
						var button = document.getElementById('major-intro-toggle');
						var btnText = document.getElementById('major-intro-btn-text');
						var btnIcon = document.getElementById('major-intro-btn-icon');
						var stickyBar = document.getElementById('major-intro-sticky-bar');
						var stickyBtn = document.getElementById('major-intro-sticky-btn');
						
						if (!content || !overlay || !container || !button) return;
						
						var limit = 350;
						var storageKey = 'ltdh_major_intro_expanded_<?php echo $major_id; ?>';
						var isExpanded = false;
						
						try {
							isExpanded = localStorage.getItem(storageKey) === 'true';
						} catch (e) {}

						function updateStickyPosition() {
							if (!stickyBar || !section) return;
							var sectionRect = section.getBoundingClientRect();
							var contentCenterX = Math.round(sectionRect.left + (sectionRect.width / 2));
							stickyBar.style.left = contentCenterX + 'px';
						}

						function updateStickyVisibility() {
							if (!stickyBar) return;
							if (!isExpanded) {
								stickyBar.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
								stickyBar.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
								return;
							}
							if (!section) return;

							updateStickyPosition();

							var sectionRect = section.getBoundingClientRect();
							var containerRect = container.getBoundingClientRect();
							var winH = window.innerHeight || document.documentElement.clientHeight;

							// Check if user is actively reading within the section
							var inSection = (sectionRect.top < winH - 80) && (sectionRect.bottom > 120);
							// Check if in-place button at bottom is already visible on screen
							var isBottomBtnVisible = (containerRect.top < winH - 30) && (containerRect.bottom > 0);

							if (inSection && !isBottomBtnVisible) {
								stickyBar.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
								stickyBar.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
							} else {
								stickyBar.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
								stickyBar.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
							}
						}

						function scrollToSectionTop() {
							if (!section) return;
							var header = document.getElementById('masthead');
							var nav = document.getElementById('ltdh-major-sticky-nav');
							var offset = (header ? header.offsetHeight : 64) + (nav ? nav.offsetHeight : 54) + 16;
							var sectionTop = section.getBoundingClientRect().top + window.pageYOffset;
							var targetY = Math.max(0, sectionTop - offset);
							
							if (window.pageYOffset > targetY) {
								window.scrollTo({
									top: targetY,
									behavior: 'smooth'
								});
							}
						}

						function collapse() {
							isExpanded = false;
							try {
								localStorage.setItem(storageKey, 'false');
							} catch (e) {}

							updateStickyVisibility();

							overlay.style.display = 'block';
							content.style.maxHeight = content.scrollHeight + 'px';
							content.offsetHeight; // Force reflow
							content.style.maxHeight = limit + 'px';
							overlay.classList.remove('opacity-0');
							btnText.textContent = 'Xem thêm tổng quan';
							btnIcon.classList.remove('rotate-180');

							scrollToSectionTop();
						}

						function expand() {
							isExpanded = true;
							try {
								localStorage.setItem(storageKey, 'true');
							} catch (e) {}

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

							updateStickyVisibility();
						}

						function applyState() {
							var needsToggle = content.scrollHeight > limit + 30;
							if (!needsToggle) {
								container.classList.remove('flex');
								container.classList.add('hidden');
								content.style.maxHeight = 'none';
								overlay.style.display = 'none';
								if (stickyBar) stickyBar.classList.add('hidden');
								return;
							}
							
							container.classList.remove('hidden');
							container.classList.add('flex');
							if (stickyBar) stickyBar.classList.remove('hidden');
							
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

							updateStickyVisibility();
						}
						
						applyState();
						window.addEventListener('load', applyState);
						
						button.addEventListener('click', function() {
							if (isExpanded) {
								collapse();
							} else {
								expand();
							}
						});

						if (stickyBtn) {
							stickyBtn.addEventListener('click', function() {
								collapse();
							});
						}

						// Auto expand if user clicks any link in EZ-TOC
						if (content) {
							content.addEventListener('click', function(e) {
								var a = e.target.closest('a');
								if (a && a.getAttribute('href') && a.getAttribute('href').startsWith('#')) {
									if (!isExpanded) {
										expand();
									}
								}
							});
						}

						var ticking = false;
						function handleScroll() {
							if (!isExpanded) return;
							if (!ticking) {
								window.requestAnimationFrame(function() {
									updateStickyVisibility();
									ticking = false;
								});
								ticking = true;
							}
						}

						window.addEventListener('scroll', handleScroll, { passive: true });
						window.addEventListener('resize', handleScroll, { passive: true });
					})();
					</script>
				</section>

				<!-- PROGRAMS FOR THIS MAJOR (PRIMARY FOCUS & FILTER) -->
				<section id="truong-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6 mb-6">
					<!-- Header -->
					<div class="mb-5 sm:mb-6">
						<h2 id="ltdh-major-programs-title" class="text-xl md:text-2xl font-black text-slate-900 leading-tight">
							Các trường đang tuyển sinh Liên thông ngành <?php echo esc_html( $clean_m_name ); ?>
						</h2>
						<p class="text-xs sm:text-sm text-slate-500 mt-1.5 flex items-center gap-1.5">
							<svg class="w-4 h-4 text-[#00308b] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
							<span>Nhấp vào chương trình để xem thời gian, học phí và lộ trình đào tạo cụ thể của từng trường.</span>
						</p>
					</div>

					<!-- Filter Bar (Only show if there are 2 or more training types available) -->
					<?php if ( count( $available_he_types ) >= 2 ) : ?>
						<div class="bg-slate-50/90 rounded-2xl p-1.5 sm:p-2 mb-6 flex items-center overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
							<!-- Training Type Filter Pills -->
							<div class="flex items-center gap-1 sm:gap-1.5 shrink-0">
								<button type="button" data-he="" class="ltdh-major-he-pill px-3.5 py-2 rounded-xl text-xs font-bold transition-all shrink-0 <?php echo empty( $selected_he ) ? 'bg-[#00308b] text-white shadow-xs is-active' : 'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-white/60'; ?>">
									Tất cả hình thức
								</button>
								<?php foreach ( $available_he_types as $he_key => $he_item ) : 
									$is_pill_active = ( $he_key === $selected_he || ( 'tu-xa' === $he_key && 'dao-tao-tu-xa' === $selected_he ) );
								?>
									<button type="button" data-he="<?php echo esc_attr( $he_item['slug'] ); ?>" class="ltdh-major-he-pill inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all shrink-0 <?php echo $is_pill_active ? 'bg-[#00308b] text-white shadow-xs is-active' : 'bg-transparent text-slate-600  hover:bg-white/60'; ?>">
										<span class="w-2 h-2 rounded-full <?php echo esc_attr( $he_item['dot'] ); ?>"></span>
										<span><?php echo esc_html( $he_item['name'] ); ?></span>
									</button>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Dynamic Programs List Container -->
					<div id="ltdh-major-programs-wrapper" data-major-id="<?php echo esc_attr( $major_id ); ?>" data-school-slug="<?php echo esc_attr( $selected_school_slug ); ?>" class="transition-opacity duration-300">
						<div id="ltdh-major-programs-list">
							<?php 
							$initial_count = ltdh_render_major_programs_list( $major_id, $selected_he, $selected_school_slug ); 
							?>
						</div>
					</div>
				</section>

				<!-- SECTION: ĐỐI TƯỢNG TUYỂN SINH (NGÀNH ĐÚNG & NGÀNH GẦN THEO TRƯỜNG ĐANG TRUY CẬP) -->
				<?php if ( ! empty( $active_target_admission ) ) : 
					$t_item = $active_target_admission;
				?>
					<section id="doi-tuong-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6 mb-6">
						<div class="border-b border-slate-100 pb-3 mb-5">
							<h2 class="text-xl md:text-2xl font-black text-slate-900 leading-tight">
								Đối tượng tuyển sinh: Ngành đúng & Ngành gần
							</h2>
							<p class="text-xs md:text-sm text-slate-500 mt-1">
								Quy định phân loại văn bằng tốt nghiệp Cao đẳng và điều kiện học phần bổ sung của <?php echo esc_html( $t_item['school_title'] ); ?>
							</p>
						</div>

						<div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-2xs">
							<!-- Header thẻ quy định của trường -->
							<div class="bg-slate-50/90 px-4 py-3 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
								<div class="flex items-center gap-2">
									<span class="w-6 h-6 rounded-lg bg-[#00308b] text-white flex items-center justify-center font-bold text-xs shrink-0">
										🏛️
									</span>
									<h3 class="font-bold text-slate-900 text-sm md:text-base">
										<?php echo esc_html( $t_item['school_title'] ); ?>
									</h3>
								</div>
								<span class="text-xs font-semibold text-slate-600 bg-white px-2.5 py-1 rounded-md border border-slate-200">
									<?php echo esc_html( $t_item['regulation_title'] ); ?>
								</span>
							</div>

							<!-- BẢNG 2 CỘT CHUẨN: CHỈ CÓ NGÀNH ĐÚNG VS NGÀNH GẦN (KHÔNG STT, KHÔNG CỘT NGÀNH DỰ TUYỂN) -->
							<div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-200">
								
								<!-- CỘT 1: NHÓM NGÀNH ĐÚNG -->
								<div class="p-4 sm:p-5 bg-emerald-50/15 flex flex-col justify-between">
									<div>
										<div class="flex items-center justify-between gap-2 border-b border-emerald-100 pb-3 mb-3.5">
											<div class="flex items-center gap-2">
												<span class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs shrink-0">✓</span>
												<h4 class="font-bold text-slate-900 text-sm md:text-base">Nhóm Ngành Đúng</h4>
											</div>
											<span class="px-2.5 py-1 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-lg border border-emerald-200">
												Không học bổ sung
											</span>
										</div>

										<p class="text-xs text-slate-600 mb-2.5 font-medium">Thí sinh tốt nghiệp Cao đẳng các chuyên ngành:</p>
										<ul class="space-y-2 pl-4 list-disc text-slate-800 text-xs sm:text-sm mb-4">
											<?php foreach ( $t_item['proper_majors'] as $p_major ) : ?>
												<li><span class="font-semibold text-slate-900"><?php echo esc_html( $p_major ); ?></span></li>
											<?php endforeach; ?>
										</ul>
									</div>

									<div class="mt-4 pt-3 border-t border-emerald-200/60 text-xs text-emerald-800 font-semibold bg-emerald-50/70 p-2.5 rounded-lg leading-relaxed">
										🎉 Vào học thẳng môn chuyên ngành. Thời gian đào tạo tiêu chuẩn (1.5 - 2 năm), không phải học bổ sung môn chuyển đổi.
									</div>
								</div>

								<!-- CỘT 2: NHÓM NGÀNH GẦN -->
								<div class="p-4 sm:p-5 bg-amber-50/15 flex flex-col justify-between">
									<div>
										<div class="flex items-center justify-between gap-2 border-b border-amber-100 pb-3 mb-3.5">
											<div class="flex items-center gap-2">
												<span class="w-6 h-6 rounded-md bg-amber-100 text-amber-900 flex items-center justify-center font-bold text-xs shrink-0">⚡</span>
												<h4 class="font-bold text-slate-900 text-sm md:text-base">Nhóm Ngành Gần</h4>
											</div>
											<span class="px-2.5 py-1 bg-amber-50 text-amber-900 text-xs font-bold rounded-lg border border-amber-200">
												+ <?php echo sprintf( '%02d', (int) ( $t_item['bridge_count'] ?: 3 ) ); ?> Học phần bổ sung
											</span>
										</div>

										<p class="text-xs text-slate-600 mb-2.5 font-medium">Thí sinh tốt nghiệp Cao đẳng các ngành:</p>
										<p class="text-xs sm:text-sm text-slate-800 leading-relaxed mb-4">
											<?php echo esc_html( implode( '; ', $t_item['near_majors'] ) ); ?>.
										</p>
									</div>

									<div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-950 leading-relaxed font-medium">
										⚠️ <strong>Quy định bắt buộc:</strong> <?php echo esc_html( $t_item['bridge_note'] ); ?>
									</div>
								</div>

							</div>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION: ADMISSION PROCESS -->
				<?php 
				get_template_part(
					'template-parts/admission-process',
					null,
					[
						'title'      => 'Quy trình tuyển sinh & Nhập học',
						'subtitle'   => 'Lộ trình 4 bước nộp hồ sơ xét tuyển và làm thủ tục nhập học chính thức ngành ' . get_the_title( $major_id ),
						'section_id' => 'quy-trinh-tuyen-sinh',
						'cta_text'   => 'Đăng ký tư vấn chọn trường ngay',
						'cta_link'   => '/kiem-tra-dieu-kien',
						'card_bg'    => 'bg-white',
					]
				<!-- SECTION: MAJOR CURRICULUM ROADMAP (UI/UX Pro Max) -->
				<?php if ( ! empty( $major_curriculum['url'] ) ) : ?>
					<section id="khung-chuong-trinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6 mb-6">
						<div class="border-b border-slate-100 pb-3 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
							<div>
								<h2 class="text-lg md:text-2xl font-bold text-slate-900">Khung chương trình đào tạo</h2>
								<p class="text-xs md:text-sm text-slate-500 mt-0.5">
									<?php if ( $context_school && ! empty( $context_school->post_title ) ) : ?>
										Lộ trình môn học & khung kiến thức chính thức áp dụng tại <?php echo esc_html( $context_school->post_title ); ?>
									<?php else : ?>
										Khung kiến thức chuẩn và lộ trình đào tạo các học kỳ của ngành <?php echo esc_html( get_the_title( $major_id ) ); ?>
									<?php endif; ?>
								</p>
							</div>
							<?php if ( ! empty( $major_curriculum['source'] ) && 'major_school' === $major_curriculum['source'] && $context_school ) : ?>
								<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-[#00308b] text-xs font-bold rounded-full self-start sm:self-auto border border-blue-100">
									Mẫu chuẩn <?php echo esc_html( get_post_meta( $context_school->ID, 'school_code', true ) ?: $context_school->post_title ); ?>
								</span>
							<?php endif; ?>
						</div>

						<?php if ( 'image' === $major_curriculum['type'] ) : ?>
							<a href="<?php echo esc_url( $major_curriculum['url'] ); ?>" target="_blank" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 p-3.5 sm:p-4 bg-slate-50/90 hover:bg-blue-50/60 rounded-xl sm:rounded-2xl border border-slate-100 hover:border-blue-200/80 transition-all duration-200 group shadow-2xs hover:shadow-md block">
								<div class="flex items-start sm:items-center gap-3 sm:gap-3.5 min-w-0">
									<div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-blue-100/90 text-[#00308b] flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform mt-0.5 sm:mt-0">
										<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
									</div>
									<div class="min-w-0 flex-1">
										<h3 class="font-bold text-slate-900 text-xs sm:text-sm md:text-base group-hover:text-[#00308b] transition-colors leading-snug">
											<?php echo esc_html( $major_curriculum['title'] ); ?>
										</h3>
										<p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 sm:mt-1 leading-relaxed">Click để mở xem ảnh lộ trình các học kỳ & danh sách môn học chi tiết</p>
									</div>
								</div>
								<span class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 text-xs font-bold text-white bg-[#00308b] sm:bg-white sm:text-[#00308b] sm:border sm:border-blue-200/60 group-hover:bg-[#00308b] group-hover:text-white px-4 py-2 sm:py-2.5 rounded-lg shadow-2xs transition-all shrink-0 min-h-[38px] whitespace-nowrap">
									<span>Xem chi tiết</span>
									<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
								</span>
							</a>
						<?php else : ?>
							<div class="bg-slate-50/80 p-4 sm:p-5 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
								<div class="flex items-center gap-3.5 min-w-0">
									<div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center font-black text-xl shrink-0 shadow-2xs">
										PDF
									</div>
									<div class="min-w-0">
										<h3 class="font-bold text-slate-900 text-sm sm:text-base mb-1 truncate">
											<?php echo esc_html( $major_curriculum['title'] ); ?>
										</h3>
										<p class="text-xs text-slate-500">Tệp PDF chính thức liệt kê lộ trình các học kỳ, khối lượng tín chỉ và môn học</p>
									</div>
								</div>
								<a href="<?php echo esc_url( $major_curriculum['url'] ); ?>" download target="_blank" rel="noopener noreferrer" class="ltdh-lead-magnet-btn w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#00308b] hover:bg-[#002266] text-white text-xs font-bold rounded-lg shadow-xs hover:shadow-sm transition-all shrink-0 cursor-pointer" data-file-url="<?php echo esc_url( $major_curriculum['url'] ); ?>" data-file-title="<?php echo esc_attr( $major_curriculum['title'] ); ?>">
									<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
									<span>Tải Khung Chương Trình (PDF)</span>
								</a>
							</div>
						<?php endif; ?>
					</section>
				<?php endif; ?>

				<!-- SECTION: MAJOR FAQ (UI/UX Pro Max - Borderless Accordion & Structured Bullets) -->
				<?php if ( ! empty( $major_faqs ) ) : 
					$clean_m_name = function_exists( 'ltdh_normalize_major_name' ) ? ltdh_normalize_major_name( get_the_title( $major_id ) ) : get_the_title( $major_id );
					$faq_context_school_name = $context_school ? get_the_title( $context_school->ID ) : '';
				?>
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
							?>
								<details <?php echo $is_first_open; ?> class="group py-3.5 sm:py-4 transition-colors">
									<summary class="flex items-center justify-between gap-3 cursor-pointer font-bold text-slate-800 text-sm sm:text-[15px] list-none select-none hover:text-[#00308b] transition-colors">
										<div class="flex items-start gap-2.5 min-w-0">
											<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-50 text-[#00308b] text-xs font-black shrink-0 mt-0.5 group-open:bg-[#00308b] group-open:text-white transition-colors">
												?
											</span>
											<span class="leading-snug text-slate-800 group-open:text-[#00308b] transition-colors">
												<?php echo esc_html( $faq_item['question'] ); ?>
											</span>
										</div>
										<svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 group-open:rotate-180 group-open:text-[#00308b] transition-transform shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
											<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
										</svg>
									</summary>
									<div class="mt-2.5 sm:mt-3 pl-8 sm:pl-8 text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
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
							<a href="/kiem-tra-dieu-kien?nganh=<?php echo esc_attr( get_post_field( 'post_name', $major_id ) ); ?><?php echo $context_school ? '&truong=' . esc_attr( get_post_field( 'post_name', $context_school->ID ) ) : ''; ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#00308b] hover:bg-blue-900 text-white text-xs font-bold shrink-0 transition-all shadow-2xs hover:shadow-xs active:scale-95 no-underline">
								<span>Kiểm tra hồ sơ ngay</span>
								<span>&rarr;</span>
							</a>
						</div>
					</section>
				<?php endif; ?>

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
				<?php get_sidebar( 'major', [ 'school_id' => $context_school ? $context_school->ID : 0 ] ); ?>
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
	function initMajorStickyNav() {
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

		var currentActiveId = null;

		function setActiveTab(targetId, isFromUserClick) {
			if (targetId === currentActiveId && !isFromUserClick) return;
			currentActiveId = targetId;

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

					if (navContainer && navContainer.scrollWidth > navContainer.clientWidth) {
						var tabLeft = link.offsetLeft;
						var tabWidth = link.offsetWidth;
						var containerWidth = navContainer.clientWidth;
						navContainer.scrollTo({
							left: tabLeft - (containerWidth / 2) + (tabWidth / 2),
							behavior: isFromUserClick ? 'smooth' : 'auto'
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
				setActiveTab(currentId, false);
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
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initMajorStickyNav);
	} else {
		initMajorStickyNav();
	}
})();
</script>

<?php
get_footer();
