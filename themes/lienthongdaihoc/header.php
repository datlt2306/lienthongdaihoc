<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&family=Montserrat:wght@600;700;800&display=swap">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&family=Montserrat:wght@600;700;800&display=swap" media="print" onload="this.media='all'">
	<noscript>
		<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&family=Montserrat:wght@600;700;800&display=swap">
	</noscript>

	<style id="ltdh-critical-above-fold">
		*, ::before, ::after { box-sizing: border-box; border-width: 0; border-style: solid; border-color: #e2e8f0; }
		html { line-height: 1.5; -webkit-text-size-adjust: 100%; font-family: 'Be Vietnam Pro', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; scroll-behavior: smooth; }
		@media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }
		body { margin: 0; line-height: inherit; color: #1e293b; background-color: #fff; }
		h1, h2, h3, h4, h5, h6,
		h1 a, h2 a, h3 a, h4 a, h5 a, h6 a,
		h1 strong, h2 strong, h3 strong, h4 strong, h5 strong, h6 strong,
		h1 b, h2 b, h3 b, h4 b, h5 b, h6 b,
		h1 .font-bold, h2 .font-bold, h3 .font-bold, h4 .font-bold, h5 .font-bold, h6 .font-bold,
		h1 .font-extrabold, h2 .font-extrabold, h3 .font-extrabold, h4 .font-extrabold, h5 .font-extrabold, h6 .font-extrabold,
		h1 .font-black, h2 .font-black, h3 .font-black, h4 .font-black, h5 .font-black, h6 .font-black {
			font-weight: 600 !important;
		}
		.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0; }
		.site-header { background-color: #fff; border-bottom: 1px solid #f1f5f9; position: sticky; top: 0; z-index: 60; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
		.max-w-7xl { max-width: 80rem; }
		.mx-auto { margin-left: auto; margin-right: auto; }
		.px-4 { padding-left: 1rem; padding-right: 1rem; }
		@media (min-width: 640px) { .sm\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; } }
		@media (min-width: 1024px) { .lg\:px-8 { padding-left: 2rem; padding-right: 2rem; } }
		.h-16 { height: 4rem; }
		@media (min-width: 640px) { .sm\:h-20 { height: 5rem; } }
		.flex { display: flex; }
		.items-center { align-items: center; }
		.justify-between { justify-content: space-between; }
		.gap-2 { gap: 0.5rem; }
		.gap-3 { gap: 0.75rem; }
		.gap-8 { gap: 2rem; }
		.relative { position: relative; }
		.shrink-0 { flex-shrink: 0; }
		.hidden { display: none; }
		@media (min-width: 1024px) {
			.lg\:flex { display: flex; }
			.lg\:hidden { display: none; }
		}
		.site-branding { display: flex; align-items: center; flex-shrink: 0; }
		.site-branding img, .site-branding svg { display: block; max-height: 48px; width: auto; }
		.bg-brand-accent { background-color: #ff7b00; }
		.text-white { color: #fff; }
		.rounded-lg { border-radius: 0.5rem; }
		.rounded-xl { border-radius: 0.75rem; }
		.rounded-2xl { border-radius: 1rem; }
		.font-bold { font-weight: 700; }
		.font-medium { font-weight: 500; }
		.text-xs { font-size: 0.75rem; line-height: 1rem; }
		.text-sm { font-size: 0.875rem; line-height: 1.25rem; }
		.shadow-md { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1); }
		.shadow-xl { box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); }
		.bg-slate-100 { background-color: #f1f5f9; }
		.text-slate-700 { color: #334155; }
		.w-10 { width: 2.5rem; }
		.h-10 { height: 2.5rem; }
		.w-full { width: 100%; }
		.block { display: block; }
		.hero-banner { aspect-ratio: 1942 / 809; width: 100%; height: auto; max-height: 800px; object-fit: cover; object-position: center; display: block; }
		.hero-banner-container { background: linear-gradient(135deg, #002e8a 0%, #00184a 100%); min-height: 220px; position: relative; overflow: hidden; width: 100%; }
		@media (min-width: 640px) { .hero-banner-container { min-height: 350px; } }
		@media (min-width: 768px) { .hero-banner-container { min-height: 450px; } }
		.hero-swiper { position: relative; overflow: hidden; list-style: none; padding: 0; z-index: 1; display: block; width: 100%; min-height: 220px; }
		@media (min-width: 640px) { .hero-swiper { min-height: 350px; } }
		@media (min-width: 768px) { .hero-swiper { min-height: 450px; } }
		.hero-swiper .swiper-wrapper { position: relative; width: 100%; height: 100%; z-index: 1; display: flex; box-sizing: content-box; }
		.hero-swiper .swiper-slide { flex-shrink: 0; width: 100%; height: 100%; position: relative; display: block; }
		.search-filters-bar { margin-top: -1.5rem; position: relative; z-index: 30; }
		@media (min-width: 640px) { .search-filters-bar { margin-top: -2rem; } }
		.bg-white { background-color: #fff; }
		.border-slate-100 { border-color: #f1f5f9; }
		.border-slate-200 { border-color: #e2e8f0; }
		.p-4 { padding: 1rem; }
		@media (min-width: 640px) { .p-5 { padding: 1.25rem; } }
		.grid { display: grid; }
		.grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
		@media (min-width: 640px) { .sm\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
		@media (min-width: 1024px) { .lg\:grid-cols-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
	</style>



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
