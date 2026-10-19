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

// Retrieve pre-calculated list of programs offered by this school
$offered_program_ids = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
	update_meta_cache( 'post', $offered_program_ids );
	update_object_term_cache( $offered_program_ids, 'program' );
}

$global_zalo = ltdh_get_zalo_url();
$zalo_group  = get_field( 'zalo_group_url', $school_id ) ?: $global_zalo;

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

// Retrieve school-specific FAQs (Custom from ACF or synthesized smart defaults)
$school_faqs = function_exists( 'ltdh_get_school_faqs' ) ? ltdh_get_school_faqs( $school_id ) : [];

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
	'subtitle' => 'Hình thức học tuyển sinh',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>',
];

$school_tabs[] = [
	'id'       => 'quy-trinh-tuyen-sinh',
	'title'    => 'Quy trình',
	'subtitle' => 'Lộ trình & Hồ sơ',
	'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
];

if ( ! empty( $school_faqs ) ) {
	$school_tabs[] = [
		'id'       => 'hoi-dap-tuyen-sinh',
		'title'    => 'Hỏi đáp (FAQ)',
		'subtitle' => 'Giải đáp thắc mắc',
		'icon'     => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>',
	];
}
?>


<main id="primary" class="site-main bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-12">
		<?php 
		$banner_image = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'full' ) : ( get_field( 'school_banner', $school_id ) ?: get_template_directory_uri() . '/assets/images/banner-school.jpg' );
		?>

		<!-- SCHOOL BRAND HERO BANNER (Redesigned with UI/UX Pro Max) -->
		<div class="relative bg-slate-100 border border-slate-200/90 rounded-2xl sm:rounded-3xl overflow-hidden mb-6 shadow-lg min-h-[320px] sm:min-h-[380px] md:min-h-[440px] lg:min-h-[480px] flex flex-col justify-end">
			<?php if ( $banner_image ) : ?>
				<!-- Background School Banner Image (Bright, natural display) -->
				<img src="<?php echo esc_url( $banner_image ); ?>" class="hero-banner skip-lazy no-lazy absolute inset-0 w-full h-full object-cover object-center" alt="<?php echo esc_attr( $school_title ); ?>" loading="eager" fetchpriority="high" decoding="async" data-no-lazy="1">
				
				<!-- Ultra-Soft Scrim Gradient: Upper 65% is 100% clear & bright; bottom has gentle fade only for text contrast -->
				<div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 via-35% to-transparent pointer-events-none"></div>
			<?php else : ?>
				<!-- Ambient Fallback Background -->
				<div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-[#00308b] to-slate-800"></div>
			<?php endif; ?>

			<!-- Subtle Ambient Top Accent Line -->
			<div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#00308b] via-blue-500 to-amber-500 z-10"></div>

			<!-- Main Brand Info Content (Anchored to Bottom of Grand Banner) -->
			<div class="relative z-10 p-5 sm:p-7 md:p-9 lg:p-10 flex flex-col sm:flex-row sm:items-end gap-4 sm:gap-6">
				<!-- School Logo Container -->
				<div class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 lg:w-32 lg:h-32 rounded-2xl sm:rounded-3xl bg-white p-2 sm:p-2.5 shadow-2xl ring-4 ring-white/15 flex items-center justify-center shrink-0 overflow-hidden">
					<?php if ( $school_logo_url ) : ?>
						<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_title ); ?>" class="max-w-full max-h-full object-contain">
					<?php else : ?>
						<div class="w-full h-full bg-[#00308b] text-white flex items-center justify-center font-black text-xl sm:text-2xl rounded-xl uppercase tracking-wider">
							<?php echo esc_html( $initials ?: 'ĐH' ); ?>
						</div>
					<?php endif; ?>
				</div>

				<!-- Title & Meta Details -->
				<div class="min-w-0 flex-1">
					<!-- Badges Row -->
					<div class="flex flex-wrap items-center gap-2 mb-2 sm:mb-2.5">
						<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/25 text-emerald-300 border border-emerald-400/35 text-[11px] sm:text-xs font-bold backdrop-blur-md shadow-xs">
							<span class="relative flex h-2 w-2">
								<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
								<span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
							</span>
							Đang nhận hồ sơ 2025 - 2026
						</span>
					</div>

					<!-- School Title -->
					<h1 class="font-black text-white text-xl sm:text-2xl md:text-3xl lg:text-4xl leading-tight tracking-tight drop-shadow-md">
						Tuyển sinh Liên thông <?php echo esc_html( $school_title ); ?>
					</h1>

					<!-- Address & Website Metadata (Responsive inline on both desktop & mobile with drop shadow) -->
					<div class="mt-2.5 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs sm:text-sm text-slate-100 drop-shadow-[0_1px_2px_rgba(0,0,0,0.85)]">
						<?php if ( $address ) : ?>
							<div class="inline-flex items-center gap-1.5">
								<svg class="w-4 h-4 text-blue-300 shrink-0 drop-shadow-[0_1px_2px_rgba(0,0,0,0.85)]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
									<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
								</svg>
								<span><?php echo esc_html( $address ); ?></span>
								<?php if ( $map_url ) : ?>
									<a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer" class="text-blue-200 font-bold hover:text-white hover:underline cursor-pointer transition-colors">(Bản đồ)</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $website ) : ?>
							<div class="inline-flex items-center gap-1.5">
								<svg class="w-4 h-4 text-blue-300 shrink-0 drop-shadow-[0_1px_2px_rgba(0,0,0,0.85)]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
								</svg>
								<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" class="group inline-flex items-center text-blue-200 font-semibold hover:text-white hover:underline cursor-pointer transition-colors">
									<span><?php echo esc_html( preg_replace( '#^https?://#i', '', rtrim( $website, '/' ) ) ); ?></span>
									<svg class="w-3.5 h-3.5 text-blue-300 group-hover:text-white transition-colors ml-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
									</svg>
								</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- STICKY SECTION TAB NAVIGATION (Inside Container, Floating Rounded Card on Desktop, Flush Edge-to-Edge on Mobile) -->
		<div id="ltdh-school-sticky-nav" class="ltdh-sticky-nav sticky top-16 sm:top-20 z-40 bg-white/98 backdrop-blur-md -mx-4 sm:mx-0 px-4 sm:px-2 rounded-none sm:rounded-2xl border-x-0 border-t-0 border-b sm:border border-slate-200/80 shadow-xs sm:shadow-[0_4px_20px_-4px_rgba(0,0,0,0.06)] mb-6 transition-all duration-200">
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

						<!-- FLOATING STICKY COLLAPSE PILL (Active when expanded & scrolling content) -->
						<div id="school-intro-sticky-bar" class="fixed bottom-[calc(4.75rem+env(safe-area-inset-bottom,0px))] md:bottom-8 z-40 transition-all duration-300 opacity-0 pointer-events-none translate-y-4 -translate-x-1/2">
							<button type="button" id="school-intro-sticky-btn" class="group flex items-center gap-2.5 px-6 py-2.5 rounded-full bg-[#00308b] text-white hover:bg-[#002266] text-sm font-bold shadow-[0_12px_30px_-4px_rgba(0,48,139,0.45),0_6px_12px_-4px_rgba(0,0,0,0.15)] ring-4 ring-white transition-all duration-200 focus:outline-none active:scale-95 cursor-pointer">
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
						var section = document.getElementById('gioi-thieu');
						var content = document.getElementById('school-intro-content');
						var overlay = document.getElementById('school-intro-overlay');
						var container = document.getElementById('school-intro-btn-container');
						var button = document.getElementById('school-intro-toggle');
						var btnText = document.getElementById('school-intro-btn-text');
						var btnIcon = document.getElementById('school-intro-btn-icon');
						var stickyBar = document.getElementById('school-intro-sticky-bar');
						var stickyBtn = document.getElementById('school-intro-sticky-btn');
						
						if (!content || !overlay || !container || !button) return;
						
						var limit = 350;
						var storageKey = 'ltdh_school_intro_expanded_<?php echo $school_id; ?>';
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
							var nav = document.getElementById('ltdh-school-sticky-nav');
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
							btnText.textContent = 'Xem thêm giới thiệu';
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
								btnText.textContent = 'Xem thêm giới thiệu';
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

				<!-- MOBILE MID-PAGE QUICK CONSULTATION CALLOUT (< 1024px) -->
				<div class="block lg:hidden bg-gradient-to-r from-[#00308b] via-blue-900 to-indigo-950 text-white rounded-2xl p-4 sm:p-5 my-6 shadow-md border border-blue-700/30">
					<div class="flex items-center gap-3 mb-2">
						<div class="w-9 h-9 rounded-xl bg-[#EA580C] text-white flex items-center justify-center font-bold text-lg shrink-0 shadow-xs">
							⚡
						</div>
						<div>
							<span class="text-[10px] uppercase font-extrabold tracking-wider text-blue-200 block">Tư vấn tuyển sinh 2026</span>
							<h3 class="font-extrabold text-sm sm:text-base text-white leading-tight">
								Nhận lộ trình Liên thông tại <?php echo esc_html( $school_title ); ?>
							</h3>
						</div>
					</div>
					<p class="text-xs text-blue-100/90 leading-relaxed mb-3.5">
						Hỗ trợ tư vấn chọn ngành, miễn giảm học phần và thông tin tuyển sinh mới nhất hoàn toàn miễn phí.
					</p>
					<div class="flex items-center gap-2">
						<a href="#register" class="flex-1 bg-[#EA580C] hover:bg-orange-600 text-white font-extrabold text-xs py-2.5 px-3 rounded-xl shadow-xs text-center transition-all">
							📝 Đăng ký tư vấn ngay
						</a>
						<a href="<?php echo esc_url( $zalo_group ); ?>" target="_blank" rel="noopener noreferrer" class="bg-white/15 hover:bg-white/25 text-white font-bold text-xs py-2.5 px-3 rounded-xl text-center transition-all border border-white/20 shrink-0">
							💬 Group Zalo
						</a>
					</div>
				</div>

				<!-- PROGRAMS OFFERED -->
				<section id="chuong-trinh-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
					<!-- Hidden anchor target for backward compatibility -->
					<span id="nganh-dao-tao" class="scroll-mt-36 md:scroll-mt-40 block invisible pointer-events-none h-0"></span>

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
						'terms'    => [ 'chinh-quy', 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ],
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
							$school_code = (string) ( get_field( 'school_code', $school_id ) ?: get_post_meta( $school_id, 'school_code', true ) );
							$school_short_name = (string) ( get_field( 'school_short_name', $school_id ) ?: ( get_field( 'badge', $school_id ) ?: '' ) );
							$clean_title = (string) get_the_title();
							if ( $school_title ) {
								$clean_title = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_title, '/' ) . '$/iu', '', $clean_title );
							}
							if ( $school_short_name ) {
								$clean_title = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_short_name, '/' ) . '$/iu', '', $clean_title );
							}
							if ( $school_code ) {
								$clean_title = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_code, '/' ) . '$/iu', '', $clean_title );
							}
							// Strip common school suffix abbreviations like " - ĐH GTVT", " - TMU", " - ĐH...", " - Trường...", " - Học viện..."
							$clean_title = preg_replace( '/\s*[-–—]\s*(?:ĐH|Trường ĐH|Đại học|Viện ĐH|Học viện|[A-Z0-9]{2,}).*$/iu', '', $clean_title );

							$types = wp_get_post_terms( $prog_id, 'training_type' );
							$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';
							$type_slug = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->slug : '';
							$clean_type_name = preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) );
							$clean_major_name = preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) );
							$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );
							$tuition_fee = ltdh_get_program_tuition_display( $prog_id );
							$duration = function_exists( 'ltdh_get_program_duration' ) ? ltdh_get_program_duration( $prog_id ) : ( get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm' );
							$permalink = get_permalink( $prog_id );
							$prog_slug = get_post_field( 'post_name', $prog_id );
							$major_slug = $major_rel_id ? get_post_field( 'post_name', $major_rel_id ) : '';

							// Check specialization (Chuyên ngành hẹp)
							$spec_title = get_field( 'specialization_title', $prog_id ) ?: get_post_meta( $prog_id, 'specialization_title', true );
							if ( empty( $spec_title ) ) {
								$spec_title = get_field( 'chuyen_nganh', $prog_id ) ?: get_post_meta( $prog_id, 'chuyen_nganh', true );
							}
							if ( empty( $spec_title ) ) {
								$candidate = (string) $clean_title;
								$candidate = preg_replace( '/\s*\([^)]*\)/iu', '', $candidate );
								$candidate = preg_replace( '/\s*[-–—]\s*(?:hệ\s+)?(?:từ xa|đào tạo từ xa|vừa học vừa làm|vừa làm vừa học|vhvl|chính quy|online|tập trung|liên thông).*$/iu', '', $candidate );
								$candidate = preg_replace( '/^(?:tuyển sinh\s+)?(?:liên\s+thông\s+(?:đại\s+học\s+)?)?(?:cử\s+nhân|kỹ\s+sư|ngành)\s+/iu', '', $candidate );
								$candidate = trim( $candidate, " -–—\t\n\r\0\x0B" );
								if ( ! empty( $candidate ) ) {
									$c_lower      = mb_strtolower( $candidate, 'UTF-8' );
									$m_lower      = mb_strtolower( $clean_major_name, 'UTF-8' );
									$orig_m_lower = mb_strtolower( $major_name, 'UTF-8' );
									if ( $c_lower !== $m_lower && $c_lower !== $orig_m_lower ) {
										$spec_title = $candidate;
									}
								}
							}

							// Determine badge classes based on training type name
							$badge_class = 'bg-blue-50 text-[#00308b]';

							$academic_year = get_field( 'tuition_academic_year', $prog_id ) ?: '2025 - 2026';

							$prog_data = [
								'id'                => $prog_id,
								'title'             => $clean_title,
								'specialization'    => $spec_title,
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

							// Retain all programs without overwriting
							$majors_data[ $major_key ]['programs'][ $prog_id ] = $prog_data;
						}
						wp_reset_postdata();

						foreach ( $majors_data as $m_k => $m_v ) {
							$majors_data[ $m_k ]['programs'] = array_values( $m_v['programs'] );
						}
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
						<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 <?php echo ( count( $majors_data ) > 1 ) ? 'mb-4' : ''; ?>">
							<div>
								<h2 class="text-xl md:text-2xl font-black text-slate-900 leading-tight">Ngành và chương trình trường đào tạo</h2>
								<p class="text-xs md:text-sm text-slate-500 mt-1">Danh sách các ngành và hình thức học tuyển sinh chính thức tại trường</p>
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

						<?php if ( count( $majors_data ) > 1 ) : ?>
						<!-- Quick Filter Controls: Instant Search & Training System Tabs -->
						<div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 pt-3 border-t border-slate-100">
							<!-- Search Input -->
							<div class="relative flex-1">
								<span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
									<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
										<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
									</svg>
								</span>
								<label for="ltdh-major-search-input" class="sr-only">Tìm kiếm tên ngành, mã ngành</label>
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
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $majors_data ) ) : ?>
						<!-- Majors Grid (Visual Cards with Thumbnails) -->
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-5" id="ltdh-majors-container">
							<?php
							foreach ( $majors_data as $major ) :
								$m_id           = $major['id'];
								$major_link     = $m_id ? add_query_arg( 'truong', get_post_field( 'post_name', $school_id ), get_permalink( $m_id ) ) : '';
								$major_he_slugs = array_unique( array_column( $major['programs'], 'he' ) );
								$programs_count   = count( $major['programs'] );
								$first_prog       = ! empty( $major['programs'] ) ? $major['programs'][0] : null;
								$primary_link     = ( $first_prog && ! empty( $first_prog['permalink'] ) ) ? $first_prog['permalink'] : ( $major_link ?: '#' );
								$major_spec_names = array_unique( array_filter( array_column( $major['programs'], 'specialization' ) ) );
								?>
								<div class="ltdh-major-card group bg-white border border-slate-200/90 hover:border-blue-300 rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
									 data-major-name="<?php echo esc_attr( mb_strtolower( $major['name'], 'UTF-8' ) ); ?>"
									 data-major-code="<?php echo esc_attr( mb_strtolower( $major['code'] ?? '', 'UTF-8' ) ); ?>"
									 data-major-specs="<?php echo esc_attr( mb_strtolower( implode( ' ', $major_spec_names ), 'UTF-8' ) ); ?>"
									 data-major-he="<?php echo esc_attr( implode( ' ', $major_he_slugs ) ); ?>">
									
									<!-- Thumbnail Area (16:9 ratio, smooth zoom on hover) -->
									<div class="relative h-44 sm:h-48 bg-slate-100 overflow-hidden">
										<a href="<?php echo esc_url( $primary_link ); ?>" class="block w-full h-full">
											<img src="<?php echo esc_url( $major['thumb'] ); ?>" 
												 alt="Ngành <?php echo esc_attr( $major['name'] ); ?>" 
												 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
												 loading="lazy">
										</a>
										<div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/20 to-transparent pointer-events-none"></div>

										<!-- Top Badges on Thumbnail: Mã ngành (Left) & Đang tuyển sinh (Right) -->
										<div class="absolute top-2.5 left-2.5 right-2.5 flex items-center justify-between gap-1.5 z-10 pointer-events-none">
											<?php if ( ! empty( $major['code'] ) ) : ?>
												<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-900/85 text-white backdrop-blur-md shadow-xs border border-white/10">
													Mã: <?php echo esc_html( $major['code'] ); ?>
												</span>
											<?php else : ?>
												<span></span>
											<?php endif; ?>

											<?php 
											$major_has_active   = false;
											$major_has_upcoming = false;
											foreach ( $major['programs'] as $p_c ) {
												$p_stat = $p_c['status'] ?? 'tuyen-sinh';
												if ( 'sap-mo' === $p_stat ) {
													$major_has_upcoming = true;
												} elseif ( 'tam-ngung' !== $p_stat ) {
													$major_has_active = true;
												}
											}

											if ( $major_has_active ) : ?>
												<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white/95 text-emerald-700 shadow-sm backdrop-blur-md">
													<span class="relative flex h-2 w-2">
														<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
														<span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
													</span>
													<span>Đang tuyển sinh</span>
												</span>
											<?php elseif ( $major_has_upcoming ) : ?>
												<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white/95 text-amber-700 shadow-sm backdrop-blur-md">
													<span class="w-2 h-2 rounded-full bg-amber-500"></span>
													<span>Sắp mở đợt mới</span>
												</span>
											<?php endif; ?>
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

											<!-- Major Name -->
											<h3 class="font-extrabold text-slate-900 text-base sm:text-lg group-hover:text-[#00308b] transition-colors leading-snug mb-1">
												<a href="<?php echo esc_url( $primary_link ); ?>">
													Ngành <?php echo esc_html( $major['name'] ); ?>
												</a>
											</h3>

											<?php 
											$is_real_spec = ! empty( $first_prog['specialization'] )
												&& mb_strtolower( trim( $first_prog['specialization'] ), 'UTF-8' ) !== mb_strtolower( trim( $major['name'] ), 'UTF-8' )
												&& mb_strtolower( trim( $first_prog['specialization'] ), 'UTF-8' ) !== mb_strtolower( trim( preg_replace( '/^ngành\s+/iu', '', $major['name'] ) ), 'UTF-8' );
											if ( $is_real_spec ) : ?>
												<p class="text-xs text-slate-500 font-medium mb-2 flex items-center gap-1.5">
													<span class="w-1.5 h-1.5 rounded-full bg-[#00308b]"></span>
													<span>Chuyên ngành: <strong class="text-slate-800 font-semibold"><?php echo esc_html( $first_prog['specialization'] ); ?></strong></span>
												</p>
											<?php endif; ?>

											<!-- Highlights Info Row -->
											<div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 mb-3.5">
												<span class="inline-flex items-center gap-1 font-medium">
													<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
														<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
													</svg>
													<span><?php echo esc_html( ( $first_prog && ! empty( $first_prog['duration'] ) ) ? $first_prog['duration'] : '1.5 - 2 năm' ); ?></span>
												</span>
												<span class="text-slate-300">•</span>
												<span class="inline-flex items-center gap-1 text-slate-600 font-medium">
													<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
														<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
													</svg>
													<span>Bằng chuẩn Bộ GD&ĐT</span>
												</span>
											</div>
										</div>

										<!-- Programs Selector Tray (Dedicated Action Area) -->
										<div class="pt-3 border-t border-slate-100 mt-auto">
											<div class="text-[11px] font-semibold text-slate-500 flex items-center gap-1.5 mb-2">
												<svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
													<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
												</svg>
												<span>Hệ đào tạo tuyển sinh:</span>
											</div>

											<?php 
											$he_list          = array_filter( array_column( $major['programs'], 'he' ) );
											$he_counts        = array_count_values( $he_list );
											$has_duplicate_he = ! empty( $he_counts ) && max( $he_counts ) > 1;
											?>
											<?php if ( $programs_count === 1 && $first_prog ) : 
												$prog      = $first_prog;
												$raw_type  = $prog['type_name'] ?: 'Đào tạo từ xa';
												$label     = ( 0 !== stripos( $raw_type, 'hệ' ) ) ? 'Hệ ' . $raw_type : $raw_type;
												$t_lower   = mb_strtolower( $raw_type, 'UTF-8' );

												$icon_svg  = '<svg class="w-4 h-4 shrink-0 text-indigo-600 group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" /></svg>';
												$btn_style = 'bg-indigo-50/70 hover:bg-indigo-600 text-indigo-900 hover:text-white border-indigo-200/90 hover:border-indigo-600';

												if ( false !== strpos( $t_lower, 'từ xa' ) ) {
													$icon_svg  = '<svg class="w-4 h-4 shrink-0 text-[#00308b] group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>';
													$btn_style = 'bg-blue-50/80 hover:bg-[#00308b] text-[#00308b] hover:text-white border-blue-200/90 hover:border-[#00308b]';
												} elseif ( false !== strpos( $t_lower, 'vừa học vừa làm' ) || false !== strpos( $t_lower, 'vừa làm vừa học' ) || false !== strpos( $t_lower, 'vhvl' ) ) {
													$icon_svg  = '<svg class="w-4 h-4 shrink-0 text-amber-600 group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
													$btn_style = 'bg-amber-50/70 hover:bg-amber-600 text-amber-900 hover:text-white border-amber-300/80 hover:border-amber-600';
												}
											?>
												<a href="<?php echo esc_url( $prog['permalink'] ); ?>" 
												   class="group/btn w-full py-2.5 px-3.5 rounded-xl text-xs sm:text-sm font-bold border transition-all flex items-center justify-between no-underline shadow-2xs hover:shadow-md active:scale-[0.99] <?php echo esc_attr( $btn_style ); ?>"
												   title="<?php echo esc_attr( $prog['opportunity_title'] ); ?>">
													<span class="flex items-center gap-2 min-w-0 mr-2">
														<?php echo $icon_svg; ?>
														<span class="truncate"><?php echo esc_html( $label ); ?></span>
													</span>
													<span class="inline-flex items-center gap-1 text-xs opacity-80 group-hover/btn:opacity-100 shrink-0 ml-auto whitespace-nowrap">
														<span>Xem tuyển sinh</span>
														<svg class="w-3.5 h-3.5 transition-transform group-hover/btn:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
															<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
														</svg>
													</span>
												</a>
											<?php else : ?>
												<!-- 2 or more systems/programs: Grid of action tiles -->
												<div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
													<?php foreach ( $major['programs'] as $prog ) : 
														$raw_type  = $prog['type_name'] ?: 'Xem chương trình';
														$label     = ( 0 !== stripos( $raw_type, 'hệ' ) ) ? 'Hệ ' . $raw_type : $raw_type;
														$t_lower   = mb_strtolower( $raw_type, 'UTF-8' );
														$spec_name = ! empty( $prog['specialization'] ) ? $prog['specialization'] : '';

														$icon_svg  = '<svg class="w-4 h-4 shrink-0 text-indigo-600 group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" /></svg>';
														$btn_style = 'bg-slate-50 hover:bg-[#00308b] text-slate-800 hover:text-white border-slate-200/90 hover:border-[#00308b]';

														if ( false !== strpos( $t_lower, 'từ xa' ) ) {
															$icon_svg  = '<svg class="w-4 h-4 shrink-0 text-[#00308b] group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>';
															$btn_style = 'bg-blue-50/70 hover:bg-[#00308b] text-[#00308b] hover:text-white border-blue-200/90 hover:border-[#00308b]';
														} elseif ( false !== strpos( $t_lower, 'vừa học vừa làm' ) || false !== strpos( $t_lower, 'vừa làm vừa học' ) || false !== strpos( $t_lower, 'vhvl' ) ) {
															$icon_svg  = '<svg class="w-4 h-4 shrink-0 text-amber-600 group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
															$btn_style = 'bg-amber-50/60 hover:bg-amber-600 text-amber-900 hover:text-white border-amber-200/90 hover:border-amber-600';
														}

														$prog_real_spec = ! empty( $spec_name )
															&& mb_strtolower( trim( $spec_name ), 'UTF-8' ) !== mb_strtolower( trim( $major['name'] ), 'UTF-8' )
															&& mb_strtolower( trim( $spec_name ), 'UTF-8' ) !== mb_strtolower( trim( preg_replace( '/^ngành\s+/iu', '', $major['name'] ) ), 'UTF-8' );
													?>
														<a href="<?php echo esc_url( $prog['permalink'] ); ?>" 
														   class="group/btn py-2 px-3 rounded-xl text-xs font-bold border transition-all flex items-center justify-between no-underline shadow-2xs hover:shadow-xs active:scale-[0.98] <?php echo esc_attr( $btn_style ); ?>"
														   title="<?php echo esc_attr( $prog['opportunity_title'] ); ?>">
															<span class="flex items-center gap-1.5 min-w-0 mr-1.5">
																<?php echo $icon_svg; ?>
																<?php if ( $prog_real_spec && ( $has_duplicate_he || count( $major['programs'] ) > 1 ) ) : ?>
																	<span class="truncate font-extrabold"><?php echo esc_html( $spec_name ); ?></span>
																	<span class="text-[10px] font-medium opacity-80 shrink-0 px-1.5 py-0.5 rounded bg-black/5"><?php echo esc_html( $prog['type_name'] ?: 'Từ xa' ); ?></span>
																<?php else : ?>
																	<span class="truncate"><?php echo esc_html( $label ); ?></span>
																<?php endif; ?>
															</span>
															<svg class="w-3.5 h-3.5 opacity-60 group-hover/btn:opacity-100 group-hover/btn:translate-x-0.5 transition-all shrink-0 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
																<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
															</svg>
														</a>
													<?php endforeach; ?>
												</div>
											<?php endif; ?>
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
									var specs = card.getAttribute('data-major-specs') || '';
									var heList = (card.getAttribute('data-major-he') || '').split(' ');

									var matchesSearch = !q || name.indexOf(q) !== -1 || code.indexOf(q) !== -1 || specs.indexOf(q) !== -1;
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
						'subtitle'   => 'Lộ trình 4 bước từ đăng ký hồ sơ đến khi chính thức nhập học tại trường',
						'section_id' => 'quy-trinh-tuyen-sinh',
						'cta_text'   => 'Đăng ký tư vấn chọn ngành ngay',
						'cta_link'   => '/kiem-tra-dieu-kien',
					]
				);
				?>

				<!-- SCHOOL SPECIFIC FAQ SECTION -->
				<?php if ( ! empty( $school_faqs ) ) : ?>
					<section id="hoi-dap-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6 mb-6">
						<div class="border-b border-slate-100 pb-4 mb-5">
							<div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-[#00308b] text-[11px] font-extrabold uppercase tracking-wider mb-2">
								<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
									<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
								</svg>
								<span>Hỏi đáp tuyển sinh</span>
							</div>
							<h2 class="text-xl md:text-2xl font-black text-slate-900 leading-tight">
								Câu hỏi thường gặp về Liên thông tại <?php echo esc_html( $school_title ); ?>
							</h2>
							<p class="text-xs sm:text-sm text-slate-500 mt-1.5">
								Giải đáp chi tiết các thắc mắc phổ biến về giá trị văn bằng, điều kiện xét tuyển, hình thức học tập và học phí tại trường.
							</p>
						</div>

						<div class="divide-y divide-slate-100">
							<?php foreach ( $school_faqs as $f_idx => $faq_item ) : 
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
									<h3 class="font-bold text-xs sm:text-sm text-slate-900">Bạn còn câu hỏi nào khác về tuyển sinh của trường?</h3>
									<p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Đội ngũ cán bộ tuyển sinh luôn sẵn sàng hỗ trợ kiểm tra bằng cấp và tư vấn lộ trình học phù hợp nhất.</p>
								</div>
							</div>
							<a href="#register" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#00308b] hover:bg-blue-900 text-white text-xs font-bold shrink-0 transition-all shadow-2xs hover:shadow-xs active:scale-95 no-underline">
								<span>Hỏi tư vấn viên</span>
								<span>&rarr;</span>
							</a>
						</div>
					</section>
				<?php endif; ?>
			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<?php get_sidebar( 'school' ); ?>
			</div>
		</div>
	</div>
</main>

<!-- SCROLLSPY & SMOOTH SCROLL SCRIPT -->
<script>
(function() {
	function initSchoolStickyNav() {
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

		// Smooth Scroll for Register Buttons
		document.querySelectorAll('a[href="#register"]').forEach(function(btn) {
			btn.addEventListener('click', function(e) {
				var targetSec = document.getElementById('register');
				if (targetSec) {
					e.preventDefault();
					var totalOffset = getStickyOffset();
					var elementPosition = targetSec.getBoundingClientRect().top + window.pageYOffset;
					var offsetPosition = elementPosition - totalOffset + 8;
					window.scrollTo({
						top: offsetPosition,
						behavior: 'smooth'
					});
					var firstInput = targetSec.querySelector('input[type="text"], input[type="tel"], input:not([type="hidden"])');
					if (firstInput) {
						setTimeout(function() {
							firstInput.focus({ preventScroll: true });
						}, 650);
					}
				}
			});
		});

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
		document.addEventListener('DOMContentLoaded', initSchoolStickyNav);
	} else {
		initSchoolStickyNav();
	}
})();
</script>

<?php
get_footer();
