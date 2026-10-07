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
$selected_he  = isset( $_GET['he'] ) ? sanitize_text_field( wp_unslash( $_GET['he'] ) ) : '';
$school_param = isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );

$context_school       = null;
$selected_school_slug = '';

if ( ! empty( $school_param ) ) {
	if ( is_numeric( $school_param ) ) {
		$context_school = get_post( intval( $school_param ) );
	} else {
		$context_school = get_page_by_path( sanitize_title( $school_param ), OBJECT, LTDH_CPT_SCHOOL );
	}
	if ( $context_school && 'publish' === $context_school->post_status ) {
		$selected_school_slug = $context_school->post_name;
	} else {
		$selected_school_slug = sanitize_title( $school_param );
	}
}

// Retrieve all distinct schools offering programs for this major
$all_major_programs = get_posts( [
	'post_type'   => LTDH_CPT_PROGRAM,
	'post_status' => 'publish',
	'numberposts' => -1,
	'meta_query'  => [
		[
			'key'     => LTDH_META_MAJOR_REL,
			'value'   => $major_id,
			'compare' => '=',
		],
	],
	'fields'      => 'ids',
] );

$distinct_school_objs = [];
if ( ! empty( $all_major_programs ) ) {
	foreach ( $all_major_programs as $p_id ) {
		$s_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $p_id ) : intval( get_post_meta( $p_id, LTDH_META_SCHOOL_REL, true ) );
		if ( $s_id && ! isset( $distinct_school_objs[ $s_id ] ) ) {
			$s_post = get_post( $s_id );
			if ( $s_post && 'publish' === $s_post->post_status ) {
				$distinct_school_objs[ $s_id ] = [
					'id'    => $s_id,
					'title' => get_the_title( $s_id ),
					'slug'  => $s_post->post_name,
				];
			}
		}
	}
}

// Retrieve pre-calculated list of programs matching this major
$offered_program_ids = get_post_meta( $major_id, LTDH_META_OFFERED_PROGRAMS, true );

$global_zalo = ltdh_get_zalo_url();
$hotline     = ltdh_get_hotline();

// Dynamically construct sticky navigation tabs matching reference mockup
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
				
				<?php if ( $context_school ) : 
					$c_school_id = $context_school->ID;
					$c_school_title = get_the_title( $c_school_id );
					$c_school_thumb = get_the_post_thumbnail_url( $c_school_id, 'thumbnail' ) ?: ltdh_get_fallback_image( 'school' );
					$c_school_permalink = get_permalink( $c_school_id );
				?>
					<!-- CONTEXT AWARENESS BANNER -->
					<div class="bg-gradient-to-r from-blue-950 via-slate-900 to-indigo-950 text-white rounded-2xl p-5 md:p-6 shadow-md border border-blue-800/40 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
						<div class="flex items-center gap-4">
							<div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-white p-1.5 flex items-center justify-center shrink-0 border border-white/20 shadow-xs">
								<img src="<?php echo esc_url( $c_school_thumb ); ?>" alt="<?php echo esc_attr( $c_school_title ); ?>" class="w-full h-full object-contain">
							</div>
							<div>
								<div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-500/20 text-blue-200 text-xs font-bold border border-blue-400/30 mb-1">
									<span>🎓</span> <span>Trường tuyển sinh chọn sẵn</span>
								</div>
								<h2 class="text-base md:text-xl font-black text-white leading-snug">
									Ngành <?php the_title(); ?> — <?php echo esc_html( $c_school_title ); ?>
								</h2>
								<p class="text-xs md:text-sm text-blue-100/80 mt-0.5">
									Đang ưu tiên hiển thị thông tin đào tạo và các lớp tuyển sinh thuộc <strong><?php echo esc_html( $c_school_title ); ?></strong>
								</p>
							</div>
						</div>
						<div class="flex items-center gap-2 w-full md:w-auto">
							<a href="<?php echo esc_url( $c_school_permalink ); ?>" class="w-full md:w-auto text-xs md:text-sm font-extrabold bg-white text-blue-900 px-4 py-2.5 rounded-xl hover:bg-blue-50 transition-all text-center whitespace-nowrap shadow-sm">
								Trang trường ➔
							</a>
						</div>
					</div>
				<?php endif; ?>


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

				<!-- PROGRAMS FOR THIS MAJOR (PRIMARY FOCUS & FILTER) -->
				<section id="truong-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6 mb-6">
					<!-- Header & Count -->
					<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4 mb-5">
						<div>
							<h2 class="text-xl md:text-2xl font-black text-slate-900 leading-tight">
								<?php echo ! empty( $context_school ) ? 'Chương trình đào tạo tại trường' : 'Chương trình & Trường tuyển sinh'; ?>
							</h2>
							<p class="text-xs md:text-sm text-slate-500 mt-0.5">
								<?php echo ! empty( $context_school ) ? 'Danh sách các hình thức tuyển sinh đang mở cho ngành này' : 'Lựa chọn hình thức đào tạo và trường phù hợp với nguyện vọng của bạn'; ?>
							</p>
						</div>
						<span id="ltdh-major-programs-count" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#00308b] shrink-0 self-start sm:self-center">
							Đang tải...
						</span>
					</div>

					<!-- Filter Bar (Minimalist Segmented Track) -->
					<div class="bg-slate-50/90 rounded-2xl p-1.5 sm:p-2 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-2.5 sm:gap-3">
						<!-- Training Type Filter Pills -->
						<div class="flex items-center gap-1 sm:gap-1.5 overflow-x-auto pb-1 md:pb-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
							<button type="button" data-he="" class="ltdh-major-he-pill px-3.5 py-2 rounded-xl text-xs font-bold transition-all shrink-0 <?php echo empty( $selected_he ) ? 'bg-[#00308b] text-white shadow-xs is-active' : 'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-white/60'; ?>">
								Tất cả hình thức
							</button>
							<button type="button" data-he="tu-xa" class="ltdh-major-he-pill inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all shrink-0 <?php echo ( 'tu-xa' === $selected_he || 'dao-tao-tu-xa' === $selected_he ) ? 'bg-[#00308b] text-white shadow-xs is-active' : 'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-white/60'; ?>">
								<span class="w-2 h-2 rounded-full bg-emerald-500"></span>
								<span>Đào tạo từ xa</span>
							</button>
							<button type="button" data-he="vua-hoc-vua-lam" class="ltdh-major-he-pill inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all shrink-0 <?php echo 'vua-hoc-vua-lam' === $selected_he ? 'bg-[#00308b] text-white shadow-xs is-active' : 'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-white/60'; ?>">
								<span class="w-2 h-2 rounded-full bg-amber-500"></span>
								<span>Vừa học vừa làm</span>
							</button>
							<button type="button" data-he="chinh-quy" class="ltdh-major-he-pill inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all shrink-0 <?php echo 'chinh-quy' === $selected_he ? 'bg-[#00308b] text-white shadow-xs is-active' : 'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-white/60'; ?>">
								<span class="w-2 h-2 rounded-full bg-blue-500"></span>
								<span>Chính quy</span>
							</button>
						</div>

						<!-- School Select Dropdown -->
						<div class="flex items-center gap-2 shrink-0 md:w-[164px]">
							<div class="relative w-full">
								<select id="ltdh-major-school-select" class="w-full bg-white text-slate-800 text-xs sm:text-sm font-semibold rounded-xl pl-3 pr-8 py-2 shadow-2xs focus:ring-2 focus:ring-[#00308b] focus:outline-none transition-all cursor-pointer appearance-none">
									<option value="">Tất cả các trường</option>
									<?php foreach ( $distinct_school_objs as $s ) : ?>
										<option value="<?php echo esc_attr( $s['slug'] ); ?>" <?php selected( $s['slug'], $selected_school_slug ); ?>>
											<?php echo esc_html( $s['title'] ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
									<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
								</div>
							</div>
						</div>
					</div>

					<!-- Dynamic Programs List Container -->
					<div id="ltdh-major-programs-wrapper" data-major-id="<?php echo esc_attr( $major_id ); ?>" class="transition-opacity duration-300">
						<div id="ltdh-major-programs-list">
							<?php 
							$initial_count = ltdh_render_major_programs_list( $major_id, $selected_he, $selected_school_slug ); 
							?>
						</div>
					</div>

					<script>
					document.addEventListener('DOMContentLoaded', function() {
						var countBadge = document.getElementById('ltdh-major-programs-count');
						if (countBadge) {
							<?php if ( ! empty( $context_school ) ) : ?>
								countBadge.textContent = '<?php echo $initial_count; ?> chương trình';
							<?php else : ?>
								countBadge.textContent = '<?php echo $initial_count; ?> trường tuyển sinh';
							<?php endif; ?>
						}
					});
					</script>
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
 
					<!-- ZALO GROUP COMMUNITY WIDGET -->
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
							Tham gia nhóm Zalo trao đổi thông tin tuyển sinh, định hướng nghề nghiệp ngành <?php echo esc_html( get_the_title( $major_id ) ); ?>.
						</p>

						<ul class="space-y-1.5 text-xs text-blue-50 font-medium mb-4 relative z-10">
							<li class="flex items-center gap-2">
								<span class="text-emerald-300 font-bold">✓</span>
								<span>Cập nhật thông tin tuyển sinh mới nhất</span>
							</li>
							<li class="flex items-center gap-2">
								<span class="text-emerald-300 font-bold">✓</span>
								<span>Chia sẻ kinh nghiệm & đề án học tập</span>
							</li>
						</ul>

						<a href="<?php echo esc_url( $global_zalo ); ?>"
						   target="_blank"
						   rel="noopener noreferrer"
						   class="w-full bg-white hover:bg-blue-50 text-blue-700 font-extrabold text-sm py-3 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 min-h-[44px] relative z-10 hover:scale-[1.02]">
							<span>💬 Tham gia Nhóm Zalo</span>
							<span class="text-xs">→</span>
						</a>
					</div>

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
