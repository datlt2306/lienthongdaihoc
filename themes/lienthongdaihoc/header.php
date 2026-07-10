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
				// Detect active menu context
				$current_pt   = get_post_type();
				$is_home      = is_front_page();
				$is_school    = is_post_type_archive( 'school' ) || is_singular( 'school' );
				$is_major     = is_post_type_archive( 'major' ) || is_singular( 'major' );
				$is_program   = is_singular( 'program' ) || is_post_type_archive( 'program' );
				$is_training  = is_tax( 'training_type' ) || is_tax( 'campus' );
				$is_guide     = is_post_type_archive( 'guide' ) || is_singular( 'guide' );
				$is_news      = is_post_type_archive( 'post' ) || is_singular( 'post' ) || is_category();

				$active_cls   = 'text-brand-primary border-brand-primary';
				$default_cls  = 'text-slate-600 border-transparent';
				?>
				<ul class="flex items-center gap-8 font-semibold text-sm">
					<li>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-brand-primary transition-all py-2 border-b-2 <?php echo $is_home ? $active_cls : $default_cls; ?>">Trang chủ</a>
					</li>
					<li>
						<a href="<?php echo esc_url( home_url( '/truong/' ) ); ?>" class="hover:text-brand-primary transition-all py-2 border-b-2 <?php echo $is_school ? $active_cls : $default_cls; ?>">Trường liên kết</a>
					</li>
					<li class="relative group">
						<a href="<?php echo esc_url( home_url( '/nganh/' ) ); ?>" class="hover:text-brand-primary flex items-center gap-1 transition-all py-2 border-b-2 <?php echo ( $is_major || $is_program ) ? $active_cls : $default_cls; ?>">
							Ngành học
							<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
							</svg>
						</a>
						<div class="dropdown-panel absolute left-0 top-full pt-2 z-50">
						<div class="bg-white shadow-xl rounded-xl border border-slate-100 py-2 w-56">
							<?php
							$majors = get_posts( [ 'post_type' => 'major', 'numberposts' => 12, 'post_status' => 'publish' ] );
							if ( ! empty( $majors ) ) {
								foreach ( $majors as $m ) {
									$clean_title = trim( preg_replace( '/\s*[\(\-][\s\S]*/', '', $m->post_title ) );
									$item_active = ( is_singular( 'major' ) && get_the_ID() === $m->ID ) ? ' bg-blue-50 text-brand-primary' : '';
									echo '<a href="' . esc_url( get_permalink( $m->ID ) ) . '" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-brand-primary font-medium' . $item_active . '">' . esc_html( $clean_title ) . '</a>';
								}
							} else {
								echo '<a href="#" class="block px-4 py-2 text-sm text-slate-400">Công nghệ thông tin</a>';
								echo '<a href="#" class="block px-4 py-2 text-sm text-slate-400">Quản trị kinh doanh</a>';
							}
							?>
						</div>
						</div>
					</li>
					<li class="relative group">
						<a href="#" class="hover:text-brand-primary flex items-center gap-1 transition-all py-2 border-b-2 <?php echo $is_training ? $active_cls : $default_cls; ?>">
							Hệ đào tạo
							<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
							</svg>
						</a>
						<!-- Dropdown Menu for Training Types -->
						<div class="dropdown-panel absolute left-0 top-full pt-2 z-50">
						<div class="bg-white shadow-xl rounded-xl border border-slate-100 py-2 w-48">
							<?php
							$training_terms = get_terms( [ 'taxonomy' => 'training_type', 'hide_empty' => false ] );
							if ( ! is_wp_error( $training_terms ) && ! empty( $training_terms ) ) {
								$queried = get_queried_object();
								foreach ( $training_terms as $term ) {
									$item_active = ( is_tax( 'training_type' ) && isset( $queried->term_id ) && $queried->term_id === $term->term_id ) ? ' bg-blue-50 text-brand-primary' : '';
									echo '<a href="' . esc_url( get_term_link( $term ) ) . '" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-brand-primary font-medium' . $item_active . '">' . esc_html( $term->name ) . '</a>';
								}
							} else {
								echo '<a href="#" class="block px-4 py-2 text-sm text-slate-400">Từ xa</a>';
								echo '<a href="#" class="block px-4 py-2 text-sm text-slate-400">Văn bằng 2</a>';
								echo '<a href="#" class="block px-4 py-2 text-sm text-slate-400">Liên thông</a>';
							}
							?>
						</div>
						</div>
					</li>
					<li>
						<a href="<?php echo esc_url( home_url( '/huong-dan/' ) ); ?>" class="hover:text-brand-primary transition-all py-2 border-b-2 <?php echo $is_guide ? $active_cls : $default_cls; ?>">Hướng dẫn tuyển sinh</a>
					</li>
					<li>
						<a href="<?php echo esc_url( home_url( '/tin-tuyen-sinh/' ) ); ?>" class="hover:text-brand-primary transition-all py-2 border-b-2 <?php echo $is_news ? $active_cls : $default_cls; ?>">Tin tức</a>
					</li>
				</ul>
				<a href="<?php echo esc_url( home_url( '/dang-ky-tu-van/' ) ); ?>" class="bg-brand-primary text-white px-6 py-2.5 rounded-full font-bold text-sm shadow-md shadow-brand-primary/20 hover:bg-[#1E40AF] hover:shadow-lg transition-all tracking-wide">
					ĐĂNG KÝ TƯ VẤN
				</a>
			</nav>

			<!-- Mobile Toggle -->
			<div class="flex lg:hidden items-center">
				<a href="<?php echo esc_url( home_url( '/dang-ky-tu-van/' ) ); ?>" class="bg-brand-primary text-white px-4 py-2 rounded-full text-sm font-bold mr-3">Tư vấn</a>
				<button id="mobile-menu-toggle" class="text-slate-600 hover:text-brand-primary focus:outline-none">
					<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
					</svg>
				</button>
			</div>
		</div>

		<!-- Mobile Navigation Drawer -->
		<div id="mobile-menu" class="hidden border-t border-slate-100 bg-white px-6 py-6 lg:hidden shadow-lg">
			<ul class="flex flex-col gap-4 font-semibold text-slate-700 mb-6">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-brand-primary block">Trang chủ</a></li>
				<li><a href="<?php echo esc_url( home_url( '/truong/' ) ); ?>" class="hover:text-brand-primary block">Trường liên kết</a></li>
				<li><a href="<?php echo esc_url( home_url( '/nganh/' ) ); ?>" class="hover:text-brand-primary block">Ngành học</a></li>
				<li><a href="<?php echo esc_url( home_url( '/huong-dan/' ) ); ?>" class="hover:text-brand-primary block">Hướng dẫn tuyển sinh</a></li>
				<li><a href="<?php echo esc_url( home_url( '/tin-tuyen-sinh/' ) ); ?>" class="hover:text-brand-primary block">Tin tức</a></li>
			</ul>
			<a href="<?php echo esc_url( home_url( '/dang-ky-tu-van/' ) ); ?>" class="block w-full text-center bg-brand-primary text-white py-3 rounded-full font-bold text-sm">
				ĐĂNG KÝ TƯ VẤN
			</a>
		</div>
	</header>
