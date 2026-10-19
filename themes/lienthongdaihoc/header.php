<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&family=Montserrat:wght@600;700;800&display=swap">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&family=Montserrat:wght@600;700;800&display=swap" media="print" onload="this.media='all'">
	<noscript>
		<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&family=Montserrat:wght@600;700;800&display=swap">
	</noscript>

	<style id="ltdh-critical-typography">
		html { scroll-behavior: smooth; }
		@media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }
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
	<link rel="preload" as="style" href="<?php echo esc_url( get_template_directory_uri() . '/assets/css/main.min.css?ver=' . filemtime( $ltdh_css_file ) ); ?>">
	<?php endif; ?>

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2.5 focus:bg-[#00308b] focus:text-white focus:font-bold focus:rounded-md focus:shadow-2xl focus:ring-2 focus:ring-white focus:outline-none transition-all duration-150" href="#primary">
	<?php esc_html_e( 'Chuyển đến nội dung chính', 'ltdh' ); ?>
</a>

<div id="page" class="site">
	<!-- HEADER NAVIGATION (Matches mockup layout and brand colors) -->
	<header id="masthead" class="site-header bg-white border-b border-slate-100 sticky top-0 z-[60] shadow-xs">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-3 relative">
			<!-- Logo Section -->
			<div class="site-branding flex items-center shrink-0">
				<?php ltdh_site_logo( 42 ); ?>
			</div>

			<!-- Menu System (Matches exact structural requirements) -->
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

			<!-- Mobile Actions (Optimized layout for phone screens) -->
			<div class="flex lg:hidden items-center gap-2 shrink-0">
				<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="flex items-center gap-1.5 bg-brand-accent text-white px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-md shadow-brand-accent/20 hover:bg-[#e06e00] active:scale-95 transition-all tracking-wide shrink-0">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 shrink-0">
						<path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd" />
					</svg>
					<span class="sm:hidden">TƯ VẤN</span>
					<span class="hidden sm:inline">TƯ VẤN NGAY</span>
				</a>
				<button id="mobile-menu-toggle" aria-label="Mở menu" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 text-slate-700 hover:bg-brand-primary/10 hover:text-brand-primary active:scale-95 transition-all shrink-0 border border-slate-200/80">
					<svg class="h-6 w-6 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
					</svg>
				</button>
			</div>
		</div>

		<!-- Mobile Navigation Drawer (Offcanvas style) -->
		<div id="mobile-menu-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[99] opacity-0 pointer-events-none transition-opacity duration-300"></div>
		
		<div id="mobile-menu" class="fixed top-0 right-0 bottom-0 h-full h-[100dvh] w-80 max-w-[85vw] bg-white z-[100] shadow-2xl translate-x-full transition-transform duration-300 ease-out flex flex-col overflow-hidden">
			<!-- Pinned Top Bar -->
			<div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0 bg-white">
				<div class="site-branding">
					<?php ltdh_site_logo_mobile( 36 ); ?>
				</div>
				<button id="mobile-menu-close" aria-label="Đóng menu" class="w-9 h-9 min-w-[36px] min-h-[36px] flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 hover:bg-slate-200 active:scale-95 transition-all">
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
				<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="flex items-center justify-center w-full min-h-[44px] bg-brand-accent text-white py-3.5 px-4 rounded-xl font-bold text-sm tracking-wide shadow-lg shadow-brand-accent/20 hover:bg-[#e06e00] active:scale-98 transition-all">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 mr-2 shrink-0">
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
		</script>
	</header>

	<?php if ( ! is_front_page() ) : ?>
		<?php ltdh_breadcrumb(); ?>
	<?php endif; ?>
