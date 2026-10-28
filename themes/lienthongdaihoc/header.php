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
			font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Be Vietnam Pro", system-ui, sans-serif;
			color: #1D1D1F;
			background-color: #FFFFFF;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
			letter-spacing: -0.011em;
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
			letter-spacing: -0.022em;
			color: #1D1D1F;
		}
		h1 { letter-spacing: -0.03em; }
		.hero-banner { aspect-ratio: 1942 / 809; }
	</style>

	<?php
	$ltdh_css_file = get_template_directory() . '/assets/css/main.min.css';
	if ( file_exists( $ltdh_css_file ) ) :
	?>
	<link rel="preload" as="style" href="<?php echo esc_url( get_template_directory_uri() . '/assets/css/main.min.css?ver=' . filemtime( $ltdh_css_file ) ); ?>" fetchpriority="high">
	<?php endif; ?>

	<meta name='dmca-site-verification' content='QWRCRnhPdmkwVTZVK0tJVHhpMUpJaFRKb0NPRG5ZU3JOKzRISTR5Nm5Icz01' />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-[#0071E3] focus:text-white focus:font-medium focus:rounded-full focus:shadow-xl focus:ring-2 focus:ring-white focus:outline-none transition-all duration-150" href="#primary">
	<?php esc_html_e( 'Chuyển đến nội dung chính', 'ltdh' ); ?>
</a>

<div id="page" class="site">
	<?php
	$header_hotline     = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496';
	$header_clean_phone = preg_replace( '/\D/', '', $header_hotline );
	?>
	<!-- HEADER NAVIGATION (Apple Cupertino Aesthetic: Frosted Glass Navbar & Floating Mobile Island) -->
	<header id="masthead" class="site-header sticky top-0 z-[60] w-full pt-2 px-2.5 lg:pt-0 lg:px-0 lg:bg-white/80 lg:backdrop-blur-xl lg:border-b lg:border-black/[0.08] lg:shadow-xs pointer-events-none lg:pointer-events-auto transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] lg:transform-none lg:opacity-100 translate-y-0 opacity-100">
		<div class="ltdh-header-island pointer-events-auto relative bg-white/85 backdrop-blur-xl border border-black/[0.08] shadow-[0_4px_24px_rgba(0,0,0,0.06)] rounded-full px-3.5 h-12 flex items-center justify-between gap-2.5 transition-all duration-300 lg:bg-transparent lg:backdrop-blur-none lg:border-0 lg:shadow-none lg:rounded-none lg:h-20 lg:max-w-7xl lg:mx-auto lg:px-6 lg:gap-6">
			<!-- Logo Section -->
			<div class="site-branding flex items-center shrink-0">
				<div class="hidden lg:block">
					<?php ltdh_site_logo( 65 ); ?>
				</div>
				<div class="lg:hidden">
					<?php ltdh_site_logo_mobile( 32 ); ?>
				</div>
			</div>

			<!-- Desktop Menu System (Apple Cupertino Typography & Pill CTA) -->
			<nav id="site-navigation" class="hidden lg:flex items-center gap-6 xl:gap-8 shrink-0">
				<?php
				wp_nav_menu( [
					'theme_location' => 'primary-menu',
					'container'      => false,
					'menu_class'     => 'nav-primary-menu',
					'fallback_cb'    => 'ltdh_default_primary_menu',
				] );
				?>
				<a href="tel:<?php echo esc_attr( $header_clean_phone ); ?>" class="flex items-center gap-2 bg-[#0071E3] hover:bg-[#0077ED] text-white px-5 py-2.5 rounded-full font-medium text-xs tracking-tight transition-all shadow-none hover:shadow-xs active:scale-98 shrink-0 whitespace-nowrap" aria-label="Gọi hotline <?php echo esc_attr( $header_hotline ); ?>">
					<?php ltdh_icon( 'phone.fill', 'w-3.5 h-3.5' ); ?>
					<span>Tư vấn ngay</span>
				</a>
			</nav>

			<!-- Mobile Actions (Ergonomic Touch Targets & Apple Pill Style) -->
			<div class="flex lg:hidden items-center gap-2 shrink-0">
				<a href="tel:<?php echo esc_attr( $header_clean_phone ); ?>" class="flex items-center gap-1.5 bg-[#0071E3] hover:bg-[#0077ED] text-white px-3.5 py-1.5 rounded-full font-medium text-xs shadow-none active:scale-95 transition-all tracking-tight shrink-0" aria-label="Gọi hotline <?php echo esc_attr( $header_hotline ); ?>">
					<?php ltdh_icon( 'phone.fill', 'w-3.5 h-3.5 shrink-0' ); ?>
					<span>Tư vấn ngay</span>
				</a>
				<button id="mobile-menu-toggle" aria-label="Mở menu điều hướng" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-full bg-black/[0.05] text-[#1D1D1F] hover:bg-black/[0.08] active:scale-90 transition-all shrink-0 border border-black/[0.06] focus:outline-none">
					<?php ltdh_icon( 'menu', 'h-4.5 w-4.5 stroke-[2]' ); ?>
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
				<?php ltdh_icon( 'xmark', 'h-5 w-5' ); ?>
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
			<a href="tel:<?php echo esc_attr( $header_clean_phone ); ?>" class="flex items-center justify-center w-full min-h-[44px] bg-[#0071E3] hover:bg-[#0077ED] text-white py-3 px-4 rounded-full font-medium text-sm tracking-tight transition-all active:scale-98" aria-label="Gọi hotline <?php echo esc_attr( $header_hotline ); ?>">
				<?php ltdh_icon( 'phone.fill', 'w-4 h-4 mr-2 shrink-0' ); ?>
				Tư vấn ngay
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
