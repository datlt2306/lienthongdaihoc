<?php
/**
 * Major Overview Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$major_id           = isset( $args['major_id'] ) ? (int) $args['major_id'] : get_the_ID();
$clean_m_name       = preg_replace( '/^ngành\s+/iu', '', trim( (string) get_the_title( $major_id ) ) );
$major_overview_raw = get_field( 'major_overview', $major_id );
$overview_markup    = ( ! empty( $major_overview_raw ) && is_string( $major_overview_raw ) && '' !== trim( $major_overview_raw ) ) 
	? $major_overview_raw 
	: get_the_content( null, false, $major_id );
?>

<!-- OVERVIEW -->
<section id="tong-quan" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
	<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Tổng quan về ngành</h2>
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
		var storageKey = 'ltdh_major_intro_expanded_<?php echo (int) $major_id; ?>';
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

			var inSection = (sectionRect.top < winH - 80) && (sectionRect.bottom > 120);
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
			content.offsetHeight;
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
