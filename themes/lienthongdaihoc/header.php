<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="overflow-x-clip">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Montserrat:wght@600;700&display=swap" media="print" onload="this.media='all'">
	<noscript>
		<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Montserrat:wght@600;700&display=swap">
	</noscript>

	<style id="ltdh-critical-typography">
		html { scroll-behavior: smooth; }
		@media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }
		html, body {
			font-family: "Be Vietnam Pro", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			overflow-x: clip;
		}
		#mobile-menu.translate-x-full {
			visibility: hidden;
			pointer-events: none;
		}
		@media (max-width: 1023px) {
			#masthead {
				transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
				will-change: transform, opacity;
			}
			#masthead.is-hidden {
				transform: translateY(-150%) !important;
				opacity: 0 !important;
				pointer-events: none !important;
			}
			html.ltdh-header-hidden .ltdh-sticky-nav {
				top: 0.5rem !important;
			}
		}
		@media (min-width: 1024px) {
			#masthead {
				transform: none !important;
				opacity: 1 !important;
				pointer-events: auto !important;
			}
		}
		h1, h2, h3, h4, h5, h6,
		h1 a, h2 a, h3 a, h4 a, h5 a, h6 a,
		h1 strong, h2 strong, h3 strong, h4 strong, h5 strong, h6 strong,
		h1 b, h2 b, h3 b, h4 b, h5 b, h6 b,
		h1 .font-bold, h2 .font-bold, h3 .font-bold, h4 .font-bold, h5 .font-bold, h6 .font-bold,
		h1 .font-extrabold, h2 .font-extrabold, h3 .font-extrabold, h4 .font-extrabold, h5 .font-extrabold, h6 .font-extrabold,
		h1 .font-black, h2 .font-black, h3 .font-black, h4 .font-black, h5 .font-black, h6 .font-black {
			font-weight: 600 !important;
		}
		.hero-banner { aspect-ratio: 1942 / 809; }
	</style>

	<?php
	$ltdh_css_file = get_template_directory() . '/assets/css/main.min.css';
	if ( file_exists( $ltdh_css_file ) ) :
	?>
	<link rel="preload" as="style" href="<?php echo esc_url( get_template_directory_uri() . '/assets/css/main.min.css?ver=' . filemtime( $ltdh_css_file ) ); ?>" fetchpriority="high">
	<?php endif; ?>

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2.5 focus:bg-[#00308b] focus:text-white focus:font-bold focus:rounded-md focus:shadow-2xl focus:ring-2 focus:ring-white focus:outline-none transition-all duration-150" href="#primary">
	<?php esc_html_e( 'Chuyển đến nội dung chính', 'ltdh' ); ?>
</a>

<div id="page" class="site">
	<!-- HEADER NAVIGATION (Mobile: Floating Glass Island with Auto-Hide | Desktop: Classic Sticky Navbar) -->
	<header id="masthead" class="site-header sticky top-0 z-[60] w-full pt-2 px-2.5 lg:pt-0 lg:px-0 lg:bg-white lg:border-b lg:border-slate-100 lg:shadow-xs pointer-events-none lg:pointer-events-auto transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] lg:transform-none lg:opacity-100 translate-y-0 opacity-100">
		<div class="ltdh-header-island pointer-events-auto relative bg-white/90 backdrop-blur-xl border border-white/80 shadow-[0_8px_30px_rgba(15,23,42,0.08),0_1px_3px_rgba(15,23,42,0.04)] rounded-2xl px-3.5 h-14 flex items-center justify-between gap-2.5 transition-all duration-300 lg:bg-transparent lg:backdrop-blur-none lg:border-0 lg:shadow-none lg:rounded-none lg:h-20 lg:max-w-7xl lg:mx-auto lg:px-4 sm:lg:px-6 lg:px-8 lg:gap-3">
			<!-- Logo Section -->
			<div class="site-branding flex items-center shrink-0">
				<div class="hidden lg:block">
					<?php ltdh_site_logo( 42 ); ?>
				</div>
				<div class="lg:hidden">
					<?php ltdh_site_logo_mobile( 34 ); ?>
				</div>
			</div>

			<!-- Desktop Menu System (Preserved classic layout & button) -->
			<nav id="site-navigation" class="hidden lg:flex items-center gap-8">
				<?php
				wp_nav_menu( [
					'theme_location' => 'primary-menu',
					'container'      => false,
					'menu_class'     => 'nav-primary-menu',
					'fallback_cb'    => 'ltdh_default_primary_menu',
				] );
				?>
				<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="flex items-center bg-brand-accent text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-md shadow-brand-accent/20 hover:bg-[#e06e00] hover:shadow-lg active:scale-95 transition-all tracking-wide">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 mr-2">
						<path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd" />
					</svg>
					Tư vấn ngay
				</a>
			</nav>

			<!-- Mobile Actions (Ergonomic Touch Targets & Visual Hierarchy) -->
			<div class="flex lg:hidden items-center gap-2 shrink-0">
				<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="flex items-center gap-1.5 bg-gradient-to-r from-[#fe8100] to-[#ea580c] text-white px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-full font-bold text-xs sm:text-sm shadow-sm shadow-orange-500/25 hover:shadow-md hover:brightness-105 active:scale-95 transition-all tracking-wide shrink-0">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 shrink-0">
						<path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd" />
					</svg>
					<span class="sm:hidden">Tư vấn</span>
					<span class="hidden sm:inline">Tư vấn ngay</span>
				</a>
				<button id="mobile-menu-toggle" aria-label="Mở menu điều hướng" class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-slate-100/90 text-slate-700 hover:bg-slate-200 hover:text-slate-900 active:scale-90 transition-all shrink-0 border border-slate-200/70 focus:outline-none focus:ring-2 focus:ring-brand-primary/30">
					<svg class="h-5 w-5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
					</svg>
				</button>
			</div>
		</div>
	</header>

	<!-- Mobile Navigation Drawer (Offcanvas style: detached from header transform) -->
	<div id="mobile-menu-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[99] opacity-0 pointer-events-none transition-opacity duration-300 touch-none"></div>
	
	<div id="mobile-menu" class="fixed top-0 right-0 bottom-0 h-full h-[100dvh] w-80 max-w-[85vw] bg-white z-[100] shadow-2xl translate-x-full transition-transform duration-300 ease-out flex flex-col overflow-hidden">
		<!-- Pinned Top Bar -->
		<div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0 bg-white">
			<div class="site-branding">
				<?php ltdh_site_logo_mobile( 36 ); ?>
			</div>
			<button id="mobile-menu-close" aria-label="Đóng menu" class="w-9 h-9 min-w-[36px] min-h-[36px] flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:text-slate-900 hover:bg-slate-200 active:scale-90 transition-all">
				<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12" />
				</svg>
			</button>
		</div>

		<!-- Scrollable Menu Content -->
		<div class="flex-1 min-h-0 overflow-y-auto overscroll-contain px-5 py-4 [scrollbar-width:thin]">
			<?php
			wp_nav_menu( [
				'theme_location' => 'primary-menu',
				'container'      => false,
				'menu_class'     => 'nav-mobile-menu',
				'fallback_cb'    => 'ltdh_default_mobile_menu',
			] );
			?>
		</div>
		
		<!-- Pinned Bottom CTA Bar -->
		<div class="p-4 sm:p-5 border-t border-slate-100 bg-white shrink-0 pb-[calc(1rem+env(safe-area-inset-bottom,0px))]">
			<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="flex items-center justify-center w-full min-h-[44px] bg-gradient-to-r from-[#fe8100] to-[#ea580c] text-white py-3 px-4 rounded-full font-bold text-sm tracking-wide shadow-lg shadow-orange-500/20 hover:brightness-105 active:scale-98 transition-all">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2 shrink-0">
					<path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd" />
				</svg>
				TƯ VẤN NGAY
			</a>
		</div>
	</div>

	<script data-no-defer="1">
	function ltdhToggleSubmenu(btn, e) {
		if (e) {
			e.preventDefault();
			e.stopPropagation();
		}
		var li = btn.closest('li.menu-item-has-children');
		if (!li) return;
		var isOpen = li.classList.toggle('is-open');
		btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		btn.setAttribute('aria-label', isOpen ? 'Thu gọn menu con' : 'Mở rộng menu con');
	}
	(function() {
		var h = document.getElementById('masthead');
		if (!h) return;
		var lastY = window.pageYOffset || 0;
		var ticking = false;
		function onScroll() {
			if (window.innerWidth >= 1024) {
				if (h.classList.contains('is-hidden')) {
					h.classList.remove('is-hidden');
					document.documentElement.classList.remove('ltdh-header-hidden');
				}
				lastY = window.pageYOffset || 0;
				ticking = false;
				return;
			}
			var y = window.pageYOffset || 0;
			var diff = y - lastY;
			var m = document.getElementById('mobile-menu');
			if (m && !m.classList.contains('translate-x-full')) {
				lastY = y <= 0 ? 0 : y;
				ticking = false;
				return;
			}
			if (y < 60) {
				h.classList.remove('is-hidden');
				document.documentElement.classList.remove('ltdh-header-hidden');
			} else if (diff > 8) {
				h.classList.add('is-hidden');
				document.documentElement.classList.add('ltdh-header-hidden');
			} else if (diff < -8) {
				h.classList.remove('is-hidden');
				document.documentElement.classList.remove('ltdh-header-hidden');
			}
			lastY = y <= 0 ? 0 : y;
			ticking = false;
		}
		window.addEventListener('scroll', function() {
			if (!ticking) {
				window.requestAnimationFrame(onScroll);
				ticking = true;
			}
		}, { passive: true });
		window.addEventListener('resize', function() {
			if (window.innerWidth >= 1024 && h.classList.contains('is-hidden')) {
				h.classList.remove('is-hidden');
				document.documentElement.classList.remove('ltdh-header-hidden');
			}
		}, { passive: true });
	})();
	</script>

	<?php if ( ! is_front_page() ) : ?>
		<?php ltdh_breadcrumb(); ?>
	<?php endif; ?>
