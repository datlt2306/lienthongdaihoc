<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;850;900&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
	

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<!-- HEADER NAVIGATION (Matches mockup layout and brand colors) -->
	<header id="masthead" class="site-header bg-white border-b border-slate-100 sticky top-0 z-50 shadow-sm">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
			<!-- Logo Section -->
			<div class="site-branding">
				<?php ltdh_site_logo( 48 ); ?>
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
				<a href="<?php echo esc_url( home_url( '#register-section' ) ); ?>" class="bg-brand-accent text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-md shadow-brand-primary/20 hover:bg-[#e06e00] hover:shadow-lg transition-all tracking-wide">
					TƯ VẤN NGAY
				</a>
			</nav>

			<!-- Mobile Toggle -->
			<div class="flex lg:hidden items-center">
				<a href="<?php echo esc_url( home_url( '#register-section' ) ); ?>" class="bg-brand-accent text-white px-4 py-2 rounded-lg text-sm font-bold mr-3">Tư vấn</a>
				<button id="mobile-menu-toggle" class="text-slate-600 hover:text-brand-primary focus:outline-none">
					<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
					</svg>
				</button>
			</div>
		</div>

		<!-- Mobile Navigation Drawer (Offcanvas style) -->
		<div id="mobile-menu-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[99] opacity-0 pointer-events-none transition-opacity duration-300"></div>
		
		<div id="mobile-menu" class="fixed top-0 right-0 bottom-0 w-80 max-w-[85vw] bg-white z-[100] shadow-2xl translate-x-full transition-transform duration-300 ease-out flex flex-col justify-between p-6">
			<div>
				<!-- Header Offcanvas -->
				<div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6">
					<div class="site-branding">
						<?php ltdh_site_logo_mobile( 36 ); ?>
					</div>
					<button id="mobile-menu-close" class="text-slate-400 hover:text-slate-900 focus:outline-none p-1">
						<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
				<a href="<?php echo esc_url( home_url( '#register-section' ) ); ?>" class="block w-full text-center bg-brand-accent text-white py-3 rounded-lg font-bold text-sm tracking-wide shadow-md shadow-brand-primary/10 hover:bg-[#e06e00]">
					TƯ VẤN NGAY
				</a>
			</div>
		</div>
	</header>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const toggleBtn = document.getElementById('mobile-menu-toggle');
			const closeBtn = document.getElementById('mobile-menu-close');
			const menu = document.getElementById('mobile-menu');
			const overlay = document.getElementById('mobile-menu-overlay');

			function openMenu() {
				menu.classList.remove('translate-x-full');
				overlay.classList.remove('opacity-0', 'pointer-events-none');
				overlay.classList.add('opacity-100');
				document.body.classList.add('overflow-hidden');
			}

			function closeMenu() {
				menu.classList.add('translate-x-full');
				overlay.classList.remove('opacity-100');
				overlay.classList.add('opacity-0', 'pointer-events-none');
				document.body.classList.remove('overflow-hidden');
			}

			if (toggleBtn && menu && overlay) {
				toggleBtn.addEventListener('click', function(e) {
					e.preventDefault();
					openMenu();
				});
			}

			if (closeBtn) {
				closeBtn.addEventListener('click', closeMenu);
			}

			if (overlay) {
				overlay.addEventListener('click', closeMenu);
			}

			// Smooth scroll to register section from mobile menu
			const mobileLinks = document.querySelectorAll('#mobile-menu a');
			mobileLinks.forEach(link => {
				link.addEventListener('click', function() {
					closeMenu();
				});
			});
		});
	</script>

	<?php if ( ! is_front_page() ) : ?>
		<?php ltdh_breadcrumb(); ?>
	<?php endif; ?>
