<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<!-- HEADER NAVIGATION (Matches mockup layout and brand colors) -->
	<header id="masthead" class="site-header bg-white border-b border-slate-100 sticky top-0 z-50 shadow-xs">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-3">
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
		
		<div id="mobile-menu" class="fixed top-0 right-0 bottom-0 w-80 max-w-[85vw] bg-white z-[100] shadow-2xl translate-x-full transition-transform duration-300 ease-out flex flex-col justify-between p-6">
			<div>
				<!-- Header Offcanvas -->
				<div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
					<div class="site-branding">
						<?php ltdh_site_logo_mobile( 36 ); ?>
					</div>
					<button id="mobile-menu-close" aria-label="Đóng menu" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 hover:bg-slate-200 active:scale-95 transition-all">
						<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12" />
						</svg>
					</button>
				</div>

				<?php
				wp_nav_menu( [
					'theme_location' => 'primary-menu',
					'container'      => false,
					'menu_class'     => 'nav-mobile-menu',
					'fallback_cb'    => 'ltdh_default_mobile_menu',
				] );
				?>
			</div>
			
			<div class="pt-6 border-t border-slate-100">
				<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="flex items-center justify-center w-full bg-brand-accent text-white py-3.5 rounded-xl font-bold text-sm tracking-wide shadow-lg shadow-brand-accent/20 hover:bg-[#e06e00] active:scale-98 transition-all">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 mr-2">
						<path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd" />
					</svg>
					TƯ VẤN NGAY
				</a>
			</div>
		</div>
	</header>

	<?php if ( ! is_front_page() ) : ?>

		<?php ltdh_breadcrumb(); ?>
	<?php endif; ?>
