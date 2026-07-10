<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;850;900&display=swap" rel="stylesheet">
	
	<!-- Tailwind CDN for rich interactive styles -->
	<script src="https://cdn.tailwindcss.com"></script>
	<script>
		tailwind.config = {
			theme: {
				extend: {
					colors: {
						brand: {
							primary: '#2563EB', // Royal Blue primary
							secondary: '#0F172A', // Text Slate 900
							accent: '#60A5FA', // Accent Sky Blue
							darkBlue: '#1E40AF', // Secondary Deep Indigo
							light: '#F8FAFC', // Light Background
						}
					},
					fontFamily: {
						sans: ['"Be Vietnam Pro"', 'sans-serif'],
						display: ['"Be Vietnam Pro"', 'sans-serif'],
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
		.ltdh-breadcrumb a { color: #2563EB; text-decoration: none; font-weight: 500; }
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
			color: #2563EB;
			border-bottom-color: #2563EB;
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
			color: #2563EB;
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
		.nav-mobile-menu .current_page_item > a {
			color: #2563EB;
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
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-2 font-display font-black text-2xl text-brand-primary">
					<div class="flex flex-col leading-none">
						<span class="text-sm font-semibold text-slate-400 tracking-wider">LIÊN THÔNG</span>
						<span class="text-xl font-extrabold text-[#2563EB]">ĐẠI HỌC</span>
					</div>
				</a>
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
				<a href="<?php echo esc_url( home_url( '#register-section' ) ); ?>" class="bg-brand-primary text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-md shadow-brand-primary/20 hover:bg-[#1E40AF] hover:shadow-lg transition-all tracking-wide">
					TƯ VẤN NGAY
				</a>
			</nav>

			<!-- Mobile Toggle -->
			<div class="flex lg:hidden items-center">
				<a href="<?php echo esc_url( home_url( '#register-section' ) ); ?>" class="bg-brand-primary text-white px-4 py-2 rounded-lg text-sm font-bold mr-3">Tư vấn</a>
				<button id="mobile-menu-toggle" class="text-slate-600 hover:text-brand-primary focus:outline-none">
					<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
					</svg>
				</button>
			</div>
		</div>

		<!-- Mobile Navigation Drawer -->
		<div id="mobile-menu" class="hidden border-t border-slate-100 bg-white px-6 py-6 lg:hidden shadow-lg">
			<?php
			wp_nav_menu( [
				'theme_location' => 'primary-menu',
				'container'      => false,
				'menu_class'     => 'nav-mobile-menu',
				'fallback_cb'    => 'ltdh_default_mobile_menu',
			] );
			?>
			<a href="<?php echo esc_url( home_url( '#register-section' ) ); ?>" class="block w-full text-center bg-brand-primary text-white py-3 rounded-lg font-bold text-sm">
				TƯ VẤN NGAY
			</a>
		</div>
	</header>

	<?php if ( ! is_front_page() ) : ?>
		<?php ltdh_breadcrumb(); ?>
	<?php endif; ?>
