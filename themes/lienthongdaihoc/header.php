<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;850;900&family=Montserrat:wght@600;700;800;900&family=Playfair+Display:ital,wght@1,400;1,600&display=swap" rel="stylesheet">
	
	<!-- Tailwind CDN for rich interactive styles -->
	<script src="https://cdn.tailwindcss.com"></script>
	<script>
		tailwind.config = {
			theme: {
				extend: {
					colors: {
						brand: {
							primary: '#00308b', // Deep Blue (default buttons, links)
							secondary: '#0F172A', // Slate 900
							accent: '#fe8100', // Orange CTA buttons only
							darkBlue: '#002266', // Darker blue hover
							light: '#F8FAFC', // Light Background
						}
					},
					fontFamily: {
						sans: ['"Be Vietnam Pro"', 'sans-serif'],
						display: ['Montserrat', '"Be Vietnam Pro"', 'sans-serif'],
						playfair: ['"Playfair Display"', 'serif'],
					}
				}
			}
		}
	</script>
	<style type="text/tailwindcss">
		@layer base {
			body {
				@apply font-sans text-brand-secondary bg-slate-50 antialiased;
			}
			h1, h2, h3, h4, h5, h6 {
				@apply font-display font-bold;
			}
		}
	</style>
	<style>
		/* Prevent Montserrat font weights from being too heavy */
		h1, h2, h3, h4, h5, h6,
		.font-black,
		.font-extrabold {
			font-weight: 700 !important;
		}

		/* Dropdown animation */
		.dropdown-panel {
			opacity: 0;
			transform: translateY(-8px);
			pointer-events: none;
			transition: opacity 0.2s ease, transform 0.2s ease;
		}
		.group:hover .dropdown-panel {
			opacity: 1;
			transform: translateY(0);
			pointer-events: auto;
		}

		/* Breadcrumb styles */
		.ltdh-breadcrumb { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
		.ltdh-breadcrumb a { color: #00308b; text-decoration: none; font-weight: 500; }
		.ltdh-breadcrumb a:hover { text-decoration: underline; }
		.ltdh-breadcrumb .separator { margin: 0 0.375rem; color: #94a3b8; }
		.ltdh-breadcrumb .last { color: #64748b; font-weight: 600; }

		/* Dynamic WP Menus styling */
		.nav-primary-menu {
			display: flex;
			align-items: center;
			gap: 2rem;
			list-style: none;
			margin: 0;
			padding: 0;
		}
		.nav-primary-menu li {
			position: relative;
		}
		.nav-primary-menu a {
			color: #475569;
			transition: all 0.2s;
			padding: 0.5rem 0;
			border-bottom: 2px solid transparent;
			font-weight: 600;
			font-size: 0.875rem;
			display: inline-block;
		}
		.nav-primary-menu a:hover,
		.nav-primary-menu .current-menu-item > a,
		.nav-primary-menu .current_page_item > a,
		.nav-primary-menu .current-menu-ancestor > a {
			color: #00308b;
			border-bottom-color: #00308b;
		}
		/* Submenu hover panel */
		.nav-primary-menu ul.sub-menu {
			position: absolute;
			left: 0;
			top: 100%;
			background: #ffffff;
			border: 1px solid #f1f5f9;
			box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
			border-radius: 0.75rem;
			min-width: 14rem;
			padding: 0.5rem 0;
			margin: 0;
			opacity: 0;
			visibility: hidden;
			transform: translateY(10px);
			transition: all 0.2s ease;
			z-index: 50;
		}
		.nav-primary-menu li:hover > ul.sub-menu {
			opacity: 1;
			visibility: visible;
			transform: translateY(0);
		}
		.nav-primary-menu ul.sub-menu li {
			display: block;
		}
		.nav-primary-menu ul.sub-menu a {
			display: block;
			padding: 0.5rem 1rem;
			color: #475569;
			font-weight: 500;
			border: none;
			font-size: 0.875rem;
		}
		.nav-primary-menu ul.sub-menu a:hover,
		.nav-primary-menu ul.sub-menu .current-menu-item > a {
			background: #f8fafc;
			color: #00308b;
		}

		/* Mobile Nav styling */
		.nav-mobile-menu {
			display: flex;
			flex-direction: column;
			gap: 1rem;
			list-style: none;
			padding: 0;
			margin: 0 0 1.5rem 0;
		}
		.nav-mobile-menu a {
			display: block;
			color: #334155;
			font-weight: 600;
			transition: all 0.2s;
		}
		.nav-mobile-menu a:hover,
		.nav-mobile-menu .current-menu-item > a,
		.nav-mobile-menu .current_page_item > a,
		.nav-mobile-menu .current-menu-ancestor > a {
			color: #00308b;
		}
		.nav-mobile-menu .menu-item-has-children > a::after {
			content: ' ▾';
			font-size: 0.75rem;
			margin-left: 0.25rem;
		}
		.nav-mobile-menu ul.sub-menu {
			list-style: none;
			padding-left: 1rem;
			margin-top: 0.5rem;
			display: flex;
			flex-direction: column;
			gap: 0.5rem;
		}
		.nav-mobile-menu ul.sub-menu a {
			font-weight: 500;
			color: #64748b;
		}
		
		/* Arrow styling for desktop */
		.nav-primary-menu .menu-item-has-children > a::after {
			content: ' ▾';
			font-size: 0.75rem;
			margin-left: 0.25rem;
			display: inline-block;
			transition: transform 0.2s;
		}
		.nav-primary-menu .menu-item-has-children:hover > a::after {
			transform: rotate(180deg);
		}

		/* Custom Contact Form 7 form styling */
		.ltdh-cf7-form input[type="text"],
		.ltdh-cf7-form input[type="tel"],
		.ltdh-cf7-form input[type="email"],
		.ltdh-cf7-form select,
		.ltdh-cf7-form textarea {
			width: 100% !important;
			border: 1px solid #cbd5e1 !important;
			border-radius: 0.5rem !important;
			padding: 0.625rem 1rem !important;
			font-size: 0.875rem !important;
			background-color: #ffffff !important;
			color: #1e293b !important;
			transition: border-color 0.2s, box-shadow 0.2s;
			box-sizing: border-box !important;
		}
		.ltdh-cf7-form input[type="text"]:focus,
		.ltdh-cf7-form input[type="tel"]:focus,
		.ltdh-cf7-form input[type="email"]:focus,
		.ltdh-cf7-form select:focus,
		.ltdh-cf7-form textarea:focus {
			border-color: #00308b !important;
			outline: none !important;
			box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.15) !important;
		}
		.ltdh-cf7-form input[type="submit"] {
			width: 100% !important;
			background-color: #00308b !important;
			color: #ffffff !important;
			padding: 0.875rem 2rem !important;
			border-radius: 0.5rem !important;
			font-weight: 700 !important;
			font-size: 0.875rem !important;
			cursor: pointer !important;
			transition: background-color 0.2s, box-shadow 0.2s !important;
			border: none !important;
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
			min-height: 44px !important;
		}
		@media (min-width: 640px) {
			.ltdh-cf7-form input[type="submit"] {
				width: auto !important;
			}
		}
		.ltdh-cf7-form input[type="submit"]:hover {
			background-color: #B45309 !important;
		}
		.ltdh-cf7-form .wpcf7-form-control-wrap {
			display: block !important;
			width: 100% !important;
		}
		.ltdh-cf7-form br {
			display: none !important;
		}
		.ltdh-cf7-form p {
			margin-bottom: 0 !important;
		}
	</style>
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
