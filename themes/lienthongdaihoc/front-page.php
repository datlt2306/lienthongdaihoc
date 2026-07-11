<?php
/**
 * Main index template (Homepage fully optimized to match Mockup UI/UX)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Cache queries for schools
$schools_query = ltdh_get_cached_query( 'ltdh_featured_schools', [
	'post_type'      => 'school',
	'posts_per_page' => 5,
	'post_status'    => 'publish',
], HOUR_IN_SECONDS );

$news_query = new WP_Query( [
	'post_type'      => 'post',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
] );

$hotline = get_field( 'global_hotline', 'options' ) ?: '0338615497';
$zalo    = get_field( 'global_zalo_url', 'options' ) ?: 'https://zalo.me';
?>

<main id="primary" class="site-main bg-white">

	<!-- 1. HERO SECTION (Matches mockup structure, student photo background, and key badges) -->
	<section class="relative bg-gradient-to-br from-[#EFF6FF] via-[#F8FAFC] to-[#FFFFFF] overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-32">
		<!-- Decorative Background SVGs -->
		<div class="absolute right-0 top-1/2 -translate-y-1/2 w-1/2 h-full opacity-10 bg-[radial-gradient(var(--tw-gradient-to-r,#1E3A8A)_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
				<!-- Hero Left Text Column -->
				<div class="lg:col-span-6 space-y-6 text-center lg:text-left z-10">
					<div class="inline-flex items-center gap-1 bg-white border border-slate-100 px-3.5 py-2 rounded-lg shadow-sm">
						<span class="text-sm text-brand-primary font-bold">Tư vấn tuyển sinh</span>
					</div>
					
					<h1 class="text-3xl sm:text-5xl lg:text-[54px] font-black text-slate-900 leading-tight tracking-tight font-display">
						LỰA CHỌN ĐẠI HỌC<br>
						<span class="text-brand-primary">ĐÚNG ĐẮN</span><br>
						<span class="bg-brand-accent text-white px-4 py-1.5 rounded-lg inline-block text-2xl sm:text-3xl font-extrabold mt-2">
							ĐỊNH HƯỚNG TƯƠNG LAI
						</span>
					</h1>

					<!-- Mockup Badges Grid (Prevent text wrapping and skewing) -->
					<div class="hidden md:flex flex-col md:flex-row gap-4 pt-4 w-full">
						<div class="flex-1 flex items-center gap-3 bg-white px-4 py-3.5 rounded-lg border border-slate-100 shadow-sm">
							<span class="text-brand-primary bg-blue-50 p-2 rounded-lg shrink-0">✔</span>
							<div class="text-left leading-tight">
								<span class="block text-base font-bold text-slate-800">Tư vấn 1:1</span>
								<span class="text-sm text-slate-500">Hoàn toàn miễn phí</span>
							</div>
						</div>
						<div class="flex-1 flex items-center gap-3 bg-white px-4 py-3.5 rounded-lg border border-slate-100 shadow-sm">
							<span class="text-brand-primary bg-blue-50 p-2 rounded-lg shrink-0">✔</span>
							<div class="text-left leading-tight">
								<span class="block text-base font-bold text-slate-800">Rút ngắn lộ trình</span>
								<span class="text-sm text-slate-500">Miễn giảm tín chỉ tối đa</span>
							</div>
						</div>
						<div class="flex-1 flex items-center gap-3 bg-white px-4 py-3.5 rounded-lg border border-slate-100 shadow-sm">
							<span class="text-brand-primary bg-blue-50 p-2 rounded-lg shrink-0">✔</span>
							<div class="text-left leading-tight">
								<span class="block text-base font-bold text-slate-800">Bằng chuẩn Bộ GD&ĐT</span>
								<span class="text-sm text-slate-500">Tương đương bằng chính quy</span>
							</div>
						</div>
					</div>
					
					<!-- Mockup CTAs -->
					<div class="flex flex-col sm:flex-row gap-4 pt-4 justify-center lg:justify-start">
						<a href="#register-section" class="bg-brand-accent text-white text-center px-8 py-4 rounded-lg font-bold hover:bg-amber-700 transition-all flex items-center justify-center gap-2 shadow-md shadow-brand-accent/20">
							LIÊN HỆ NGAY
							<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
							</svg>
						</a>
						<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex items-center justify-center gap-3 bg-white border border-slate-200 px-6 py-4 rounded-lg font-bold text-slate-800 hover:bg-slate-50 transition-all">
							<span class="h-10 w-10 bg-blue-50 text-brand-primary flex items-center justify-center rounded-lg shrink-0">📞</span>
							<div class="text-left leading-tight">
								<span class="block text-sm text-slate-500 font-medium">Hotline tư vấn</span>
								<span class="text-base font-extrabold text-brand-primary"><?php echo esc_html( $hotline ); ?></span>
							</div>
						</a>
					</div>
				</div>
				
				<!-- Hero Right Image Column (Stunning mockup layout replica) -->
				<div class="lg:col-span-6 relative hidden lg:flex justify-center items-center">
					<div class="relative w-full max-w-md md:max-w-lg aspect-square">
						<!-- Mockup Blob Graphic / Frame decoration -->
						<div class="absolute inset-0 bg-gradient-to-tr from-brand-primary/20 to-[#EFF6FF]/50 rounded-lg blur-2xl -z-10"></div>
						
						<!-- Circle Photo representation -->
						<div class="w-full h-full rounded-lg border-4 border-white shadow-xl overflow-hidden relative bg-slate-100 flex items-center justify-center">
							<!-- Standard CSS/SVG representation of modern student learning background -->
							<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=600');"></div>
							<div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 via-transparent to-transparent"></div>
						</div>

						<!-- Floating Badges like in Image -->
						<div class="absolute bottom-10 left-0 bg-white p-3 rounded-lg shadow-lg border border-slate-100 flex items-center gap-3 animate-bounce">
							<span class="text-2xl">🎓</span>
							<div class="leading-none text-left">
								<span class="block text-sm font-black text-slate-800">50+ Đối tác</span>
								<span class="text-sm text-slate-400">Trường đại học uy tín</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Wave Separator -->
		<div class="absolute bottom-0 left-0 right-0 w-full overflow-hidden leading-none z-0 pointer-events-none">
			<svg class="relative block w-full h-[40px] md:h-[80px]" viewBox="0 0 1200 120" preserveAspectRatio="none">
				<path d="M0,0 C300,100 900,0 1200,100 L1200,120 L0,120 Z" class="fill-white"></path>
			</svg>
		</div>
	</section>

	<!-- Floating Filter Panel Section -->
	<?php
	$schools = get_posts( [ 'post_type' => 'school', 'numberposts' => -1 ] );
	$majors  = get_posts( [ 'post_type' => 'major', 'numberposts' => -1 ] );
	$types   = get_terms( [ 'taxonomy' => 'training_type', 'hide_empty' => false ] );
	?>
	<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-20 -mt-12 mb-8">
		<div class="bg-white p-6 md:p-8 rounded-xl shadow-xl border border-slate-100">
			<form action="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" method="GET" class="flex flex-col md:grid md:grid-cols-5 gap-4 md:items-end animate-fade-in">
				<!-- 1. Keyword search (Always visible) -->
				<div class="w-full">
					<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Từ khóa tìm kiếm</label>
					<input type="text" name="s" placeholder="Tìm tên chương trình..." class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:border-brand-primary focus:outline-none placeholder-slate-400 font-medium min-h-[44px]" />
				</div>
				
				<!-- 2. Mobile Collapsible Advanced Filters -->
				<details class="md:hidden w-full group">
					<summary class="text-xs font-bold text-brand-primary cursor-pointer select-none py-2.5 flex items-center justify-between min-h-[44px] list-none [&::-webkit-details-marker]:hidden">
						<span class="flex items-center gap-2">
							<span>⚙️</span> <span>Tìm kiếm nâng cao (Trường, Ngành, Hệ)</span>
						</span>
						<span class="text-slate-400 group-open:rotate-180 transition-transform">▾</span>
					</summary>
					<div class="space-y-4 pt-3 border-t border-slate-100 bg-slate-50/50 p-4 rounded-lg">
						<div>
							<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Trường đại học</label>
							<select name="truong_filter" class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:border-brand-primary focus:outline-none bg-white min-h-[44px]">
								<option value="">-- Chọn trường học --</option>
								<?php
								foreach ( $schools as $s ) {
									echo '<option value="' . esc_attr( $s->ID ) . '">' . esc_html( $s->post_title ) . '</option>';
								}
								?>
							</select>
						</div>
						
						<div>
							<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Ngành đào tạo</label>
							<select name="nganh_filter" class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:border-brand-primary focus:outline-none bg-white min-h-[44px]">
								<option value="">-- Chọn ngành học --</option>
								<?php
								foreach ( $majors as $m ) {
									echo '<option value="' . esc_attr( $m->ID ) . '">' . esc_html( $m->post_title ) . '</option>';
								}
								?>
							</select>
						</div>

						<div>
							<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Hệ đào tạo</label>
							<select name="he_filter" class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:border-brand-primary focus:outline-none bg-white min-h-[44px]">
								<option value="">-- Chọn hệ học --</option>
								<?php
								foreach ( $types as $t ) {
									echo '<option value="' . esc_attr( $t->slug ) . '">' . esc_html( $t->name ) . '</option>';
								}
								?>
							</select>
						</div>
					</div>
				</details>

				<!-- 3. Desktop Advanced Filters (Hidden on Mobile) -->
				<div class="hidden md:grid md:grid-cols-3 md:gap-4 md:col-span-3 items-end">
					<div>
						<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Trường đại học</label>
						<select name="truong_filter" class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:border-brand-primary focus:outline-none bg-transparent min-h-[44px]">
							<option value="">-- Chọn trường học --</option>
							<?php
							foreach ( $schools as $s ) {
								echo '<option value="' . esc_attr( $s->ID ) . '">' . esc_html( $s->post_title ) . '</option>';
							}
							?>
						</select>
					</div>
					
					<div>
						<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Ngành đào tạo</label>
						<select name="nganh_filter" class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:border-brand-primary focus:outline-none bg-transparent min-h-[44px]">
							<option value="">-- Chọn ngành học --</option>
							<?php
							foreach ( $majors as $m ) {
								echo '<option value="' . esc_attr( $m->ID ) . '">' . esc_html( $m->post_title ) . '</option>';
							}
							?>
						</select>
					</div>

					<div>
						<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Hệ đào tạo</label>
						<select name="he_filter" class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm focus:border-brand-primary focus:outline-none bg-transparent min-h-[44px]">
							<option value="">-- Chọn hệ học --</option>
							<?php
							foreach ( $types as $t ) {
								echo '<option value="' . esc_attr( $t->slug ) . '">' . esc_html( $t->name ) . '</option>';
							}
							?>
						</select>
					</div>
				</div>

				<!-- 4. Action Buttons (Always visible) -->
				<div class="flex gap-2 w-full">
					<button type="submit" class="flex-1 bg-brand-accent text-white py-3 rounded-lg font-bold hover:bg-amber-700 transition-all text-xs uppercase tracking-wider min-h-[44px]">
						TÌM KIẾM
					</button>
					<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg font-bold transition-all text-xs uppercase text-center flex items-center justify-center shrink-0 min-h-[44px]" title="Reset bộ lọc">
						🔄
					</a>
				</div>
			</form>
		</div>
	</div>

	<!-- 2. SUB-HERO PROMO SECTION (Liên thông Đại học - Nâng tầm nghề nghiệp ngay) -->
	<section class="py-16 bg-white">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
				<!-- Laptop student illustration representation -->
				<div class="lg:col-span-5 flex justify-center">
					<div class="relative w-full max-w-sm aspect-video sm:aspect-square bg-slate-50 border border-slate-100 rounded-xl p-4 shadow-inner overflow-hidden">
						<div class="w-full h-full bg-cover bg-center rounded-lg" style="background-image: url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=500');"></div>
					</div>
				</div>

				<!-- Right Side Promotion Texts -->
				<div class="lg:col-span-7 space-y-4">
					<span class="inline-block bg-[#EFF6FF] text-[#2563EB] text-sm font-bold px-3.5 py-1 rounded-lg uppercase tracking-wider">
						LIÊN THÔNG ĐẠI HỌC
					</span>
					<h1 class="text-xl sm:text-2xl md:text-5xl lg:text-6xl font-black font-display text-slate-900 leading-tight">
						LỰA CHỌN ĐẠI HỌC ĐÚNG ĐẮN<br>NÂNG TẦM NGHỀ NGHIỆP NGAY
					</h1>
					<p class="text-slate-500 text-sm md:text-base leading-relaxed">
						Giúp bạn rút ngắn thời gian - Tiết kiệm chi phí - Mở rộng cơ hội việc làm với chương trình Liên thông Đại học uy tín, chất lượng. Lớp học trực tuyến linh động và bằng cấp giá trị được bộ GD&ĐT công nhận.
					</p>

					<div class="flex flex-wrap gap-3 pt-2">
						<a href="#program-section" class="bg-[#2563EB] text-white px-6 py-3 rounded-lg font-bold text-sm hover:bg-[#1E40AF] transition-all">
							Tìm hiểu ngay →
						</a>
						<a href="#register-section" class="bg-white border border-slate-200 text-slate-700 px-6 py-3 rounded-lg font-bold text-sm hover:bg-slate-50 transition-all">
							Nhận tư vấn miễn phí
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 3. HOT MAJORS SECTION -->
	<section class="py-12 bg-slate-50 border-y border-slate-100 relative overflow-hidden">
		<!-- Decorative Background Dots -->
		<div class="absolute inset-0 opacity-40 pointer-events-none z-0" style="background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
			<div class="text-center max-w-2xl mx-auto mb-8 space-y-2">
				<span class="text-[#2563EB] text-xs font-extrabold uppercase tracking-widest block">KHÁM PHÁ NGÀNH HỌC</span>
				<h2 class="text-xl md:text-3xl font-black text-slate-900">Các ngành đào tạo hot nhất</h2>
				<p class="text-slate-500 text-sm">Hàng trăm ngành đào tạo đa dạng, phù hợp mọi đối tượng và xu hướng thị trường lao động.</p>
			</div>

			<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
				<?php
				$majors_query = new WP_Query( [
					'post_type'      => 'major',
					'posts_per_page' => 12,
					'post_status'    => 'publish',
				] );

				// Color palette cycling for cards
				$card_colors = [
					[ 'bg' => 'bg-blue-50',   'icon_bg' => 'bg-blue-100',   'icon_text' => 'text-blue-600',   'border' => 'border-blue-100',   'hover' => 'hover:border-blue-300' ],
					[ 'bg' => 'bg-violet-50', 'icon_bg' => 'bg-violet-100', 'icon_text' => 'text-violet-600', 'border' => 'border-violet-100', 'hover' => 'hover:border-violet-300' ],
					[ 'bg' => 'bg-emerald-50','icon_bg' => 'bg-emerald-100','icon_text' => 'text-emerald-600','border' => 'border-emerald-100','hover' => 'hover:border-emerald-300' ],
					[ 'bg' => 'bg-amber-50',  'icon_bg' => 'bg-amber-100',  'icon_text' => 'text-amber-600',  'border' => 'border-amber-100',  'hover' => 'hover:border-amber-300' ],
					[ 'bg' => 'bg-rose-50',   'icon_bg' => 'bg-rose-100',   'icon_text' => 'text-rose-600',   'border' => 'border-rose-100',   'hover' => 'hover:border-rose-300' ],
					[ 'bg' => 'bg-cyan-50',   'icon_bg' => 'bg-cyan-100',   'icon_text' => 'text-cyan-600',   'border' => 'border-cyan-100',   'hover' => 'hover:border-cyan-300' ],
				];

				if ( $majors_query->have_posts() ) :
					$i = 0;
					while ( $majors_query->have_posts() ) : $majors_query->the_post();
						$title       = get_the_title();
						$clean_title = trim( preg_replace( '/\s*[\(\-][\s\S]*/', '', $title ) );
						$lower_title = mb_strtolower( $clean_title, 'UTF-8' );
						$icon        = '🎓';
						if ( strpos( $lower_title, 'công nghệ thông tin' ) !== false || strpos( $lower_title, 'cntt' ) !== false ) {
							$icon = '💻';
						} elseif ( strpos( $lower_title, 'quản trị' ) !== false ) {
							$icon = '📈';
						} elseif ( strpos( $lower_title, 'kinh tế' ) !== false || strpos( $lower_title, 'tài chính' ) !== false || strpos( $lower_title, 'kế toán' ) !== false ) {
							$icon = '💰';
						} elseif ( strpos( $lower_title, 'marketing' ) !== false ) {
							$icon = '🎯';
						} elseif ( strpos( $lower_title, 'ngôn ngữ' ) !== false || strpos( $lower_title, 'tiếng anh' ) !== false ) {
							$icon = '🌐';
						} elseif ( strpos( $lower_title, 'thương mại' ) !== false ) {
							$icon = '🏪';
						} elseif ( strpos( $lower_title, 'thiết kế' ) !== false || strpos( $lower_title, 'đồ họa' ) !== false ) {
							$icon = '🎨';
						} elseif ( strpos( $lower_title, 'luật' ) !== false ) {
							$icon = '⚖️';
						} elseif ( strpos( $lower_title, 'xây dựng' ) !== false || strpos( $lower_title, 'kiến trúc' ) !== false ) {
							$icon = '🏗️';
						} elseif ( strpos( $lower_title, 'y' ) !== false || strpos( $lower_title, 'dược' ) !== false ) {
							$icon = '🏥';
						}
						$c = $card_colors[ $i % count( $card_colors ) ];
						$i++;
					?>
						<a href="<?php the_permalink(); ?>"
						   class="group flex flex-col items-center text-center p-4 bg-white border <?php echo $c['border']; ?> rounded-xl <?php echo $c['hover']; ?> hover:shadow-md transition-all">
							<div class="h-12 w-12 <?php echo $c['icon_bg']; ?> <?php echo $c['icon_text']; ?> rounded-xl flex items-center justify-center mb-3 text-xl group-hover:scale-110 transition-transform">
								<?php echo $icon; ?>
							</div>
							<h4 class="font-bold text-xs text-slate-700 leading-snug line-clamp-2"><?php echo esc_html( $clean_title ); ?></h4>
						</a>
					<?php
					endwhile;
					wp_reset_postdata();
				else :
					echo '<p class="text-sm text-slate-400 col-span-6 text-center">Chưa có ngành học nào.</p>';
				endif;
				?>
			</div>

			<div class="text-center mt-8">
				<a href="<?php echo esc_url( home_url( '/nganh-hoc/' ) ); ?>"
				   class="inline-flex items-center gap-2 text-[#2563EB] font-bold text-sm hover:underline">
					Xem tất cả ngành học <span>→</span>
				</a>
			</div>
		</div>
	</section>



	<!-- 4. NATIONAL SCHOOLS SECTION ("CÁC TRƯỜNG ĐÀO TẠO TRÊN TOÀN QUỐC") -->
	<section id="program-section" class="py-12 md:py-16 bg-slate-50">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8">
				<div class="space-y-2">
					<span class="text-brand-primary text-sm font-bold uppercase tracking-wider block">THÔNG TIN TUYỂN SINH</span>
					<h2 class="text-xl md:text-3xl font-black text-slate-900">CÁC TRƯỜNG ĐÀO TẠO TRÊN TOÀN QUỐC</h2>
				</div>
				<a href="<?php echo esc_url( home_url( '/truong-lien-ket/' ) ); ?>" class="text-sm text-brand-primary font-bold hover:underline mt-4 sm:mt-0 flex items-center gap-1">
					Xem tất cả
					<span>→</span>
				</a>
			</div>
			
			<div class="flex lg:grid overflow-x-auto lg:overflow-x-visible snap-x snap-mandatory lg:snap-none gap-4 pb-4 lg:pb-0 no-scrollbar lg:grid-cols-5">
				<?php 
				if ( $schools_query->have_posts() ) {
					$index = 0;
					$fallback_images = [
						'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&q=80&w=300',
						'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=300',
						'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&q=80&w=300',
						'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&q=80&w=300',
						'https://images.unsplash.com/photo-1519452635265-7b1fbfd1e4e0?auto=format&fit=crop&q=80&w=300'
					];
					while ( $schools_query->have_posts() ) : $schools_query->the_post();
						$school_id = get_the_ID();
						$address = get_field( 'address', $school_id );
						$hotline = get_field( 'hotline', $school_id ) ?: get_field( 'global_hotline', 'options' );
						$thumb_url = get_the_post_thumbnail_url( $school_id, 'medium' ) ?: $fallback_images[$index % 5];
						$logo_id = get_field( 'logo', $school_id );
						$en_name = get_post_meta( $school_id, 'english_name', true ) ?: 'University';
						$rating  = get_post_meta( $school_id, 'rating', true ) ?: '4.8';
						$reviews = get_post_meta( $school_id, 'reviews_count', true ) ?: '256';
						$target  = get_post_meta( $school_id, 'admission_target', true ) ?: '3.000';
				?>
						<div class="bg-white border border-slate-100 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between shrink-0 w-[45vw] sm:w-[250px] lg:w-auto snap-center">
							<div class="h-20 md:h-28 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $thumb_url ); ?>');"></div>
							
							<!-- Logo Overlay -->
							<div class="h-12 w-12 md:h-16 md:w-16 bg-white rounded-lg border-2 md:border-4 border-white shadow-md bg-white -mt-6 md:-mt-8 mx-auto z-10 relative flex items-center justify-center overflow-hidden">
								<?php if ( $logo_id ) : ?>
									<?php echo wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
								<?php else : ?>
									<span class="font-display font-extrabold text-brand-primary text-xs">UNI</span>
								<?php endif; ?>
							</div>

							<div class="p-4 pt-2 flex-1 flex flex-col justify-between">
								<div class="text-center">
									<h4 class="font-extrabold text-slate-800 text-xs md:text-sm tracking-tight leading-snug uppercase min-h-[36px] line-clamp-2 mt-1"><?php the_title(); ?></h4>
									<p class="text-[11px] text-slate-400 mt-0.5 font-medium line-clamp-1 italic"><?php echo esc_html( $en_name ); ?></p>
									
									<div class="flex items-center justify-center gap-1 mt-2.5 text-[11px] text-slate-500 font-bold">
										<span class="text-yellow-400">★</span>
										<span><?php echo esc_html( $rating ); ?> (<?php echo esc_html( $reviews ); ?> đánh giá)</span>
									</div>
								</div>
								
								<div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-sm text-slate-600">
									<a href="<?php the_permalink(); ?>" class="w-full text-center bg-slate-50 hover:bg-brand-primary hover:text-white py-2 rounded-lg font-bold transition-all text-xs uppercase text-brand-primary">Tìm hiểu thêm</a>
								</div>
							</div>
						</div>
				<?php 
						$index++;
					endwhile;
					wp_reset_postdata();
				} else {
					echo '<div class="col-span-5 text-center text-slate-500 py-6">Chưa có trường liên kết nào được gieo dữ liệu.</div>';
				}
				?>
			</div>
		</div>
	</section>

	<!-- 5. WHY CHOOSE US ("VÌ SAO CHỌN NEXT PATH / LIÊN THÔNG ĐẠI HỌC?") -->
	<section class="py-12 bg-white border-t border-slate-100 relative overflow-hidden">
		<!-- Decorative Background Dots -->
		<div class="absolute inset-0 opacity-[0.03] pointer-events-none z-0" style="background-image: radial-gradient(#2563eb 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
			<div class="text-center max-w-2xl mx-auto mb-8">
				<h2 class="text-xl md:text-3xl font-black text-slate-900 mb-2">VÌ SAO CHỌN LIÊN THÔNG ĐẠI HỌC?</h2>
				<p class="text-slate-500 text-sm md:text-sm">Chúng tôi đồng hành cùng bạn trên hành trình chinh phục tri thức.</p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
				<div class="bg-slate-50/50 border border-slate-100 rounded-lg p-6 text-center shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-lg mx-auto mb-3 text-lg">📜</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Bằng chuẩn Bộ GD&ĐT</h4>
					<p class="text-sm text-slate-500">Phôi bằng tương đương chính quy, có giá trị lâu dài và đủ điều kiện thi cao học, xét bậc lương.</p>
				</div>
				<div class="bg-slate-50/50 border border-slate-100 rounded-lg p-6 text-center shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-lg mx-auto mb-3 text-lg">🔗</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Miễn giảm tín chỉ tối đa</h4>
					<p class="text-sm text-slate-500">Đối chiếu bảng điểm Trung cấp/Cao đẳng cũ để lược bỏ các môn đã học, tiết kiệm thời gian ra trường.</p>
				</div>
				<div class="bg-slate-50/50 border border-slate-100 rounded-lg p-6 text-center shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-lg mx-auto mb-3 text-lg">💻</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Học Online linh hoạt</h4>
					<p class="text-sm text-slate-500">Học trực tuyến hoàn toàn qua hệ thống E-learning của trường, thi cử tập trung cuối tuần tiện lợi.</p>
				</div>
				<div class="bg-slate-50/50 border border-slate-100 rounded-lg p-6 text-center shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-lg mx-auto mb-3 text-lg">📝</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Hỗ trợ hồ sơ từ A-Z</h4>
					<p class="text-sm text-slate-500">Tiếp nhận hồ sơ online, công chứng và làm thủ tục nhập học trọn gói nhanh chóng trong 7 ngày.</p>
				</div>
			</div>
		</div>
	</section>

	<!-- 6. STATS NUMBERS BANNER (Blue stats widget from mockup image) -->
	<section class="py-12 bg-gradient-to-r from-[#0E2038] to-[#1E3A8A] text-white overflow-hidden relative">
		<div class="absolute right-0 bottom-0 w-96 h-96 bg-[#2563EB]/10 rounded-lg blur-3xl"></div>
		
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
				<div class="lg:col-span-7 space-y-4">
					<span class="inline-block bg-[#2563EB] text-white text-sm font-black px-3.5 py-1 rounded-lg uppercase tracking-wider">
						LÝ DO NÊN CHỌN CHÚNG TÔI
					</span>
					<h3 class="text-xl sm:text-2xl md:text-4xl font-black font-display tracking-tight leading-tight text-white">
						LIÊN THÔNG ĐẠI HỌC - NÂNG TẦM NGHỀ NGHIỆP NGAY<br>TỐT NHẤT CHO BẠN
					</h3>
					
					<div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-6">
						<div>
							<span class="block font-display font-black text-3xl md:text-[40px] text-[#2563EB] leading-none mb-1">1.200+</span>
							<span class="text-sm text-slate-300 font-semibold block leading-tight">Học viên đã và đang đồng hành</span>
						</div>
						<div>
							<span class="block font-display font-black text-3xl md:text-[40px] text-[#2563EB] leading-none mb-1">98%</span>
							<span class="text-sm text-slate-300 font-semibold block leading-tight">Tỷ lệ hài lòng của học viên</span>
						</div>
						<div>
							<span class="block font-display font-black text-3xl md:text-[40px] text-[#2563EB] leading-none mb-1">30+</span>
							<span class="text-sm text-slate-300 font-semibold block leading-tight">Đối tác trường đại học uy tín</span>
						</div>
						<div>
							<span class="block font-display font-black text-3xl md:text-[40px] text-[#2563EB] leading-none mb-1">10+</span>
							<span class="text-sm text-slate-300 font-semibold block leading-tight">Năm kinh nghiệm tư vấn</span>
						</div>
					</div>
				</div>
				
				<!-- Graduate image representation placeholder -->
				<div class="lg:col-span-5 hidden lg:block">
					<div class="relative w-full max-w-sm aspect-[4/3] rounded-lg overflow-hidden border-4 border-white/10 shadow-2xl">
						<div class="w-full h-full bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=400');"></div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 7. STUDENT TESTIMONIALS ("ĐÁNH GIÁ CỦA HỌC VIÊN") -->
	<section class="py-12 bg-white relative overflow-hidden">
		<!-- Decorative Background Blurred Blobs -->
		<div class="absolute -left-16 -top-16 w-64 h-64 bg-brand-primary/10 rounded-full blur-3xl pointer-events-none z-0"></div>
		<div class="absolute -right-16 -bottom-16 w-64 h-64 bg-brand-accent/10 rounded-full blur-3xl pointer-events-none z-0"></div>
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
			<div class="text-center max-w-2xl mx-auto mb-8 space-y-3">
				<span class="text-brand-primary text-sm font-bold uppercase tracking-wider block">CẢM NHẬN HỌC VIÊN</span>
				<h2 class="text-xl md:text-3xl font-black text-slate-900">ĐÁNH GIÁ CỦA HỌC VIÊN</h2>
				<p class="text-slate-500 text-sm">Hàng ngàn học viên đã tin tưởng và đồng hành cùng chúng tôi trên hành trình chinh phục tấm bằng đại học.</p>
			</div>

			<div class="flex md:grid overflow-x-auto md:overflow-x-visible snap-x snap-mandatory md:snap-none gap-6 pb-4 md:pb-0 no-scrollbar md:grid-cols-3">
				<!-- Testimonial 1 -->
				<div class="shrink-0 w-[85vw] md:w-auto snap-center bg-slate-50 border border-slate-100 rounded-xl p-6 relative">
					<div class="text-yellow-400 text-lg mb-3">★★★★★</div>
					<p class="text-sm text-slate-600 leading-relaxed mb-4 italic">"Mình đã học Văn bằng 2 CNTT tại đây. Lịch học trực tuyến rất linh hoạt, giảng viên nhiệt tình và kiến thức thực tế. Sau khi tốt nghiệp mình đã được thăng chức đúng như mong đợi."</p>
					<div class="flex items-center gap-3 pt-4 border-t border-slate-200">
						<div class="h-10 w-10 bg-brand-primary text-white rounded-full flex items-center justify-center font-bold text-sm">NH</div>
						<div>
							<p class="font-bold text-sm text-slate-800">Nguyễn Hương</p>
							<p class="text-xs text-slate-400">VB2 Công nghệ thông tin</p>
						</div>
					</div>
				</div>

				<!-- Testimonial 2 -->
				<div class="shrink-0 w-[85vw] md:w-auto snap-center bg-slate-50 border border-slate-100 rounded-xl p-6 relative">
					<div class="text-yellow-400 text-lg mb-3">★★★★★</div>
					<p class="text-sm text-slate-600 leading-relaxed mb-4 italic">"Từ xa giúp mình vừa đi làm vừa học đại học. Chương trình đào tạo bài bản, hồ sơ thủ tục nhanh gọn. Mình rất hài lòng với chất lượng giảng dạy."</p>
					<div class="flex items-center gap-3 pt-4 border-t border-slate-200">
						<div class="h-10 w-10 bg-brand-primary text-white rounded-full flex items-center justify-center font-bold text-sm">TM</div>
						<div>
							<p class="font-bold text-sm text-slate-800">Trần Minh</p>
							<p class="text-xs text-slate-400">Đại học Từ xa - Quản trị kinh doanh</p>
						</div>
					</div>
				</div>

				<!-- Testimonial 3 -->
				<div class="shrink-0 w-[85vw] md:w-auto snap-center bg-slate-50 border border-slate-100 rounded-xl p-6 relative">
					<div class="text-yellow-400 text-lg mb-3">★★★★★</div>
					<p class="text-sm text-slate-600 leading-relaxed mb-4 italic">"Liên thông từ Cao đẳng lên Đại học nhanh hơn mình nghĩ. Nhờ tư vấn viên hướng dẫn tận tình mà mình hoàn thành hồ sơ chỉ trong 2 tuần. Bằng cấp được bộ GD&ĐT công nhận."</p>
					<div class="flex items-center gap-3 pt-4 border-t border-slate-200">
						<div class="h-10 w-10 bg-brand-primary text-white rounded-full flex items-center justify-center font-bold text-sm">LP</div>
						<div>
							<p class="font-bold text-sm text-slate-800">Lê Phương</p>
							<p class="text-xs text-slate-400">Liên thông Đại học - Kế toán</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Stats Row -->
			<div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-4">
				<div class="text-center p-4 bg-blue-50 rounded-lg">
					<span class="block font-display font-black text-2xl md:text-3xl text-brand-primary">1.200+</span>
					<span class="text-sm text-slate-600 font-semibold">Học viên tin tưởng</span>
				</div>
				<div class="text-center p-4 bg-blue-50 rounded-lg">
					<span class="block font-display font-black text-2xl md:text-3xl text-brand-primary">98%</span>
					<span class="text-sm text-slate-600 font-semibold">Tỷ lệ hài lòng</span>
				</div>
				<div class="text-center p-4 bg-blue-50 rounded-lg">
					<span class="block font-display font-black text-2xl md:text-3xl text-brand-primary">4.9/5</span>
					<span class="text-sm text-slate-600 font-semibold">Đánh giá trung bình</span>
				</div>
				<div class="text-center p-4 bg-blue-50 rounded-lg">
					<span class="block font-display font-black text-2xl md:text-3xl text-brand-primary">500+</span>
					<span class="text-sm text-slate-600 font-semibold">Lượt đánh giá 5 sao</span>
				</div>
			</div>
		</div>
	</section>

	<!-- 8. DOUBLE CERTIFICATE VALUE PROPOSITION -->
	<section class="py-12 bg-white">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
			<!-- Box Left: BẰNG ĐẠO TẠO CHÍNH QUY -->
			<div class="bg-slate-50 border border-slate-100 rounded-lg p-6 md:p-8 flex flex-col md:flex-row justify-between gap-6">
				<div class="flex-1 space-y-4">
					<span class="bg-[#EFF6FF] text-[#2563EB] text-sm font-black px-3 py-1 rounded-lg uppercase tracking-wider inline-block">BẰNG CẤP TƯƠNG ĐƯƠNG</span>
					<h3 class="font-extrabold text-xl text-slate-900 leading-tight">BẰNG ĐẠO TẠO CHÍNH QUY</h3>
					<ul class="space-y-2 text-sm text-slate-600">
						<li>✔ Học chương trình chuẩn theo quy định của bộ GD&ĐT.</li>
						<li>✔ Đảm bảo giá trị pháp lý, sử dụng toàn quốc.</li>
						<li>✔ Phục vụ học tập, thi công chức, nâng lương nâng bậc.</li>
						<li>✔ Hồ sơ nhanh gọn - Quy trình rõ ràng.</li>
					</ul>
					<a href="#register-section" class="inline-block bg-[#2563EB] text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-[#1E40AF] transition-all pt-2">Tìm hiểu thêm</a>
				</div>
				<div class="w-full md:w-36 shrink-0 aspect-[3/4] bg-white border border-slate-100 rounded-lg p-2 flex items-center justify-center shadow-inner">
					<div class="text-center leading-none">
						<span class="text-4xl block mb-2">📜</span>
						<span class="text-sm text-slate-400 block uppercase font-bold">BẰNG ĐẠI HỌC</span>
					</div>
				</div>
			</div>

			<!-- Box Right: SUỐT ĐỜI TRÊN TOÀN QUỐC -->
			<div class="bg-slate-50 border border-slate-100 rounded-lg p-6 md:p-8 flex flex-col md:flex-row justify-between gap-6">
				<div class="flex-1 space-y-4">
					<span class="bg-blue-50 text-blue-800 text-sm font-black px-3 py-1 rounded-lg uppercase tracking-wider inline-block">BẰNG CÓ GIÁ TRỊ SỬ DỤNG</span>
					<h3 class="font-extrabold text-xl text-slate-900 leading-tight">SUỐT ĐỜI TRÊN TOÀN QUỐC</h3>
					<ul class="space-y-2 text-sm text-slate-600">
						<li>✔ Bằng đại học sử dụng lâu dài, không giới hạn thời gian.</li>
						<li>✔ Được công nhận rộng rãi bởi doanh nghiệp và nhà nước.</li>
						<li>✔ Cơ hội tiếp tục học lên cao học, thạc sĩ, tiến sĩ.</li>
						<li>✔ Hỗ trợ kết nối việc làm sau khi hoàn thành khóa học.</li>
					</ul>
					<a href="#register-section" class="inline-block bg-slate-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-slate-900 transition-all pt-2">Xem chi tiết</a>
				</div>
				<div class="w-full md:w-36 shrink-0 aspect-[3/4] bg-white border border-slate-100 rounded-lg p-2 flex items-center justify-center shadow-inner">
					<div class="text-center leading-none">
						<span class="text-4xl block mb-2">🛡</span>
						<span class="text-sm text-slate-400 block uppercase font-bold">CHỨNG CHỈ</span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 9. SEGMENTS SECTION ("CHƯƠNG TRÌNH PHÙ HỢP VỚI BẠN") -->
	<section class="py-12 bg-slate-50 border-y border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="text-center max-w-2xl mx-auto mb-8">
				<h2 class="text-xl md:text-3xl font-black text-slate-900 mb-2">NGÀNH HỌC HOT & DỄ XIN VIỆC</h2>
				<p class="text-slate-500 text-sm md:text-sm">Chúng tôi thiết kế các lộ trình học tối ưu riêng cho từng nhóm đối tượng học viên.</p>
			</div>

			<div class="flex md:grid overflow-x-auto md:overflow-x-visible snap-x snap-mandatory md:snap-none gap-6 pb-4 md:pb-0 no-scrollbar md:grid-cols-3">
				<!-- Segment 1 -->
				<div class="bg-white border border-slate-100 rounded-lg p-6 hover:border-brand-primary hover:shadow-md transition-all flex flex-col justify-between shrink-0 w-[85vw] md:w-auto snap-center">
					<div class="space-y-3">
						<div class="h-36 bg-slate-100 rounded-lg overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&q=80&w=350');"></div>
						<h4 class="font-extrabold text-slate-800 text-base">Tốt nghiệp Trung cấp / Cao đẳng</h4>
						<p class="text-sm text-slate-500 leading-relaxed">Rút ngắn tối đa thời gian đào tạo nhờ chuyển đổi, miễn giảm tín chỉ. Nhận bằng Đại học nhanh chóng để nâng lương, thăng chức hoặc thi cao học.</p>
					</div>
				</div>
				<!-- Segment 2 -->
				<div class="bg-white border border-slate-100 rounded-lg p-6 hover:border-brand-primary hover:shadow-md transition-all flex flex-col justify-between shrink-0 w-[85vw] md:w-auto snap-center">
					<div class="space-y-3">
						<div class="h-36 bg-slate-100 rounded-lg overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&q=80&w=350');"></div>
						<h4 class="font-extrabold text-slate-800 text-base">Người đi làm bận rộn</h4>
						<p class="text-sm text-slate-500 leading-relaxed">Chương trình liên thông trực tuyến (Online E-learning) học mọi lúc mọi nơi. Lịch thi linh hoạt cuối tuần, giúp cân bằng công việc và học tập dễ dàng.</p>
					</div>
				</div>
				<!-- Segment 3 -->
				<div class="bg-white border border-slate-100 rounded-lg p-6 hover:border-brand-primary hover:shadow-md transition-all flex flex-col justify-between shrink-0 w-[85vw] md:w-auto snap-center">
					<div class="space-y-3">
						<div class="h-36 bg-slate-100 rounded-lg overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&q=80&w=350');"></div>
						<h4 class="font-extrabold text-slate-800 text-base">Liên thông trái ngành / Chuyển ngành</h4>
						<p class="text-sm text-slate-500 leading-relaxed">Đón đầu xu hướng lao động bằng cách liên thông sang ngành nghề mới (CNTT, Kinh tế, Ngoại ngữ). Tối ưu lộ trình học tập, mở rộng cơ hội chuyển đổi việc làm.</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 10. DYNAMIC CONSULTATION FORM SECTION ("ĐĂNG KÝ NHẬN TƯ VẤN MIỄN PHÍ") -->
	<section id="register-section" class="py-16 bg-white">
		<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 border border-slate-100 shadow-xl rounded-xl p-6 md:p-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center bg-gradient-to-tr from-[#EFF6FF]/35 to-white">
			<!-- Graduate photo column -->
			<div class="lg:col-span-5 hidden lg:block">
				<div class="h-[400px] w-full rounded-lg overflow-hidden bg-slate-200 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=500');"></div>
			</div>

			<!-- Registration form column -->
			<div class="lg:col-span-7 space-y-6">
				<div>
					<h2 class="text-xl md:text-3xl font-black text-slate-900 leading-tight">ĐĂNG KÝ NHẬN TƯ VẤN MIỄN PHÍ</h2>
					<p class="text-slate-500 text-sm mt-1">Để lại thông tin, chuyên gia tư vấn tuyển sinh đại học sẽ liên hệ cho bạn trong vòng 15 phút.</p>
				</div>
				
				<?php 
				if ( function_exists( 'wpcf7_contact_form_html' ) ) :
					echo do_shortcode( '[contact-form-7 id="consultation-form" title="Form Tư vấn"]' );
				else :
				?>
					<form action="#" method="POST" class="space-y-4">
						<input type="hidden" name="referral_source" value="<?php echo esc_attr( home_url( '/' ) ); ?>">
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<label class="block text-sm font-bold text-slate-600 mb-1">Họ và tên *</label>
								<input type="text" name="your-name" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Họ và tên của bạn">
							</div>
							<div>
								<label class="block text-sm font-bold text-slate-600 mb-1">Số điện thoại *</label>
								<input type="tel" name="your-phone" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Số điện thoại liên hệ">
							</div>
						</div>
						<div>
							<label class="block text-sm font-bold text-slate-600 mb-1">Địa chỉ Email (Tùy chọn)</label>
							<input type="email" name="your-email" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Địa chỉ email của bạn">
						</div>
						<div>
							<label class="block text-sm font-bold text-slate-600 mb-1">Chương trình quan tâm</label>
							<select name="current_program_id" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none">
								<option value="">-- Chọn ngành hoặc hệ học --</option>
								<?php
								$programs = get_posts( [ 'post_type' => 'program', 'numberposts' => -1 ] );
								foreach ( $programs as $p ) {
									echo '<option value="' . esc_attr( $p->ID ) . '">' . esc_html( $p->post_title ) . '</option>';
								}
								?>
							</select>
						</div>
						<div>
							<label class="block text-sm font-bold text-slate-600 mb-1">Nội dung cần tư vấn</label>
							<textarea name="your-message" rows="3" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Nhập câu hỏi hoặc yêu cầu cụ thể của bạn..."></textarea>
						</div>
						
						<div class="flex flex-col sm:flex-row items-center gap-4 pt-2">
							<button type="submit" class="w-full sm:w-auto bg-brand-accent text-white px-8 py-3.5 rounded-lg font-bold text-sm hover:bg-amber-700 transition-all shadow-md shadow-brand-accent/20">
								GỬI THÔNG TIN
							</button>
							<span class="text-sm text-slate-400 font-medium text-center sm:text-left">Cam kết bảo mật thông tin tuyệt đối và chỉ sử dụng cho mục đích tư vấn tuyển sinh.</span>
						</div>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- 11. NEWS RECENT SECTION ("TIN TỨC MỚI NHẤT") -->
	<section class="py-16 bg-slate-50 border-t border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex justify-between items-end mb-12">
				<div class="space-y-2">
					<span class="text-brand-primary text-sm font-bold uppercase tracking-wider block">THÔNG TIN TUYỂN SINH</span>
					<h2 class="text-xl md:text-3xl font-black text-slate-900">TIN TỨC MỚI NHẤT</h2>
				</div>
				<a href="<?php echo esc_url( home_url( '/tin-tuyen-sinh/' ) ); ?>" class="text-sm text-brand-primary font-bold hover:underline flex items-center gap-1">
					Xem tất cả
					<span>→</span>
				</a>
			</div>

			<div class="flex sm:grid overflow-x-auto sm:overflow-x-visible snap-x snap-mandatory sm:snap-none gap-3 sm:gap-6 pb-4 sm:pb-0 no-scrollbar sm:grid-cols-4">
				<?php 
				$mock_news = [
					[ 'title' => 'Tuyển sinh Đại học Từ xa khóa mới nhất', 'date' => '10/07/2026', 'desc' => 'Thông tin chi tiết các ngành đào tạo từ xa hệ Đại học được bộ GD&ĐT công nhận tốt nghiệp chính quy.' ],
					[ 'title' => 'Điều kiện học Văn bằng 2 đại học năm 2026', 'date' => '08/07/2026', 'desc' => 'Giải đáp những thắc mắc thường gặp về điều kiện tuyển sinh học văn bằng 2 cho học viên tốt nghiệp các ngành.' ],
					[ 'title' => 'Quy chế tuyển sinh Liên thông Cao đẳng lên Đại học', 'date' => '05/07/2026', 'desc' => 'Quy định rút ngắn chương trình đào tạo khi thi liên thông và các hồ sơ chuẩn bị nhập học.' ],
					[ 'title' => 'Học đại học vừa học vừa làm có giá trị như thế nào?', 'date' => '02/07/2026', 'desc' => 'Giá trị pháp lý của tấm bằng đại học vừa học vừa làm đối với cơ hội thăng tiến nghề nghiệp.' ],
				];

				$news_has_posts = false;
				if ( $news_query->have_posts() ) {
					$news_has_posts = true;
					$index = 0;
					while ( $news_query->have_posts() ) : $news_query->the_post();
						$post_id = get_the_ID();
						$thumb_url = get_the_post_thumbnail_url( $post_id, 'medium' ) ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=250';
						$categories = get_the_category( $post_id );
						$category_name = ! empty( $categories ) ? $categories[0]->name : 'Tin tuyển sinh';
				?>
						<div class="bg-white border border-slate-100 rounded-lg overflow-hidden hover:shadow-md transition-all flex flex-col justify-between shrink-0 w-[45vw] sm:w-auto snap-center">
							<div class="h-36 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $thumb_url ); ?>');"></div>
							<div class="p-4 flex-1 flex flex-col justify-between">
								<div class="space-y-2">
									<div class="flex items-center justify-between text-xs font-semibold">
										<span class="bg-blue-50 text-brand-primary px-2 py-0.5 rounded-lg"><?php echo esc_html( $category_name ); ?></span>
										<span class="text-slate-400"><?php echo get_the_date( 'd/m/Y' ); ?></span>
									</div>
									<h4 class="font-extrabold text-slate-800 text-sm md:text-sm tracking-tight leading-snug line-clamp-2 hover:text-brand-primary pt-1">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h4>
									<p class="text-sm text-slate-400 leading-normal line-clamp-2"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
								</div>
								<a href="<?php the_permalink(); ?>" class="mt-4 text-sm text-brand-primary font-bold hover:underline block">Chi tiết bài viết</a>
							</div>
						</div>
				<?php 
						$index++;
					endwhile;
					wp_reset_postdata();
				}

				if ( ! $news_has_posts ) {
					foreach ( $mock_news as $n ) :
				?>
						<div class="bg-white border border-slate-100 rounded-lg overflow-hidden hover:shadow-md transition-all flex flex-col justify-between shrink-0 w-[45vw] sm:w-auto snap-center">
							<div class="h-32 bg-slate-200 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=250');"></div>
							<div class="p-4 flex-1 flex flex-col justify-between">
								<div class="space-y-2">
									<span class="text-sm text-slate-400 font-semibold block"><?php echo esc_html( $n['date'] ); ?></span>
									<h4 class="font-extrabold text-slate-800 text-sm md:text-sm tracking-tight leading-snug line-clamp-2"><?php echo esc_html( $n['title'] ); ?></h4>
									<p class="text-sm text-slate-400 leading-normal line-clamp-2"><?php echo esc_html( $n['desc'] ); ?></p>
								</div>
								<span class="mt-4 text-sm text-[#2563EB] font-bold hover:underline block">Chi tiết bài viết</span>
							</div>
						</div>
				<?php
					endforeach;
				}
				?>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
