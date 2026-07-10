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
		<div class="absolute right-0 top-1/2 -translate-y-1/2 w-1/2 h-full opacity-10 bg-[radial-gradient(#2563EB_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
				<!-- Hero Left Text Column -->
				<div class="lg:col-span-6 space-y-6 text-center lg:text-left z-10">
					<div class="inline-flex items-center gap-1 bg-white border border-slate-100 px-3.5 py-2 rounded-full shadow-sm">
						<span class="text-sm font-serif italic text-[#2563EB] font-bold">Tư vấn</span>
					</div>
					
					<h1 class="text-3xl sm:text-5xl lg:text-[54px] font-black text-slate-900 leading-tight tracking-tight">
						LỰA CHỌN ĐẠI HỌC<br>
						<span class="text-[#2563EB]">ĐÚNG ĐẮN</span><br>
						<span class="bg-[#F5BF23] text-slate-950 px-4 py-1.5 rounded-lg inline-block text-2xl sm:text-3xl font-extrabold mt-2">
							ĐỊNH HƯỚNG TƯƠNG LAI
						</span>
					</h1>

					<!-- Mockup Badges Grid (Prevent text wrapping and skewing) -->
					<div class="flex flex-col md:flex-row gap-4 pt-4 w-full">
						<div class="flex-1 flex items-center gap-3 bg-white px-4 py-3.5 rounded-xl border border-slate-100 shadow-sm">
							<span class="text-[#2563EB] bg-blue-50 p-2 rounded-lg shrink-0">✔</span>
							<div class="text-left leading-tight">
								<span class="block text-base font-bold text-slate-800 whitespace-nowrap">Tư vấn 1:1</span>
								<span class="text-sm text-slate-500 whitespace-nowrap">Hoàn toàn miễn phí</span>
							</div>
						</div>
						<div class="flex-1 flex items-center gap-3 bg-white px-4 py-3.5 rounded-xl border border-slate-100 shadow-sm">
							<span class="text-[#2563EB] bg-blue-50 p-2 rounded-lg shrink-0">✔</span>
							<div class="text-left leading-tight">
								<span class="block text-base font-bold text-slate-800 whitespace-nowrap">Lộ trình cá nhân</span>
								<span class="text-sm text-slate-500 whitespace-nowrap">Phù hợp năng lực</span>
							</div>
						</div>
						<div class="flex-1 flex items-center gap-3 bg-white px-4 py-3.5 rounded-xl border border-slate-100 shadow-sm">
							<span class="text-[#2563EB] bg-blue-50 p-2 rounded-lg shrink-0">✔</span>
							<div class="text-left leading-tight">
								<span class="block text-base font-bold text-slate-800 whitespace-nowrap">Hỗ trợ toàn diện</span>
								<span class="text-sm text-slate-500 whitespace-nowrap">Hồ sơ - Học bổng - Visa</span>
							</div>
						</div>
					</div>
					
					<!-- Mockup CTAs -->
					<div class="flex flex-col sm:flex-row gap-4 pt-4 justify-center lg:justify-start">
						<a href="#register-section" class="bg-[#2563EB] text-white text-center px-8 py-4 rounded-xl font-bold hover:bg-[#1E40AF] transition-all flex items-center justify-center gap-2 shadow-md shadow-brand-primary/10">
							LIÊN HỆ NGAY
							<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
							</svg>
						</a>
						<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex items-center justify-center gap-3 bg-white border border-slate-200 px-6 py-4 rounded-xl font-bold text-slate-800 hover:bg-slate-50 transition-all">
							<span class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-full shrink-0">📞</span>
							<div class="text-left leading-tight">
								<span class="block text-sm text-slate-500 font-medium">Hotline tư vấn</span>
								<span class="text-base font-extrabold text-[#2563EB]"><?php echo esc_html( $hotline ); ?></span>
							</div>
						</a>
					</div>
				</div>
				
				<!-- Hero Right Image Column (Stunning mockup layout replica) -->
				<div class="lg:col-span-6 relative flex justify-center items-center">
					<div class="relative w-full max-w-md md:max-w-lg aspect-square">
						<!-- Mockup Blob Graphic / Frame decoration -->
						<div class="absolute inset-0 bg-gradient-to-tr from-[#2563EB]/20 to-[#EFF6FF]/50 rounded-full blur-2xl -z-10"></div>
						
						<!-- Circle Photo representation -->
						<div class="w-full h-full rounded-full border-4 border-white shadow-xl overflow-hidden relative bg-slate-100 flex items-center justify-center">
							<!-- Standard CSS/SVG representation of modern student learning background -->
							<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=600');"></div>
							<div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 via-transparent to-transparent"></div>
						</div>

						<!-- Floating Badges like in Image -->
						<div class="absolute bottom-10 left-0 bg-white p-3 rounded-2xl shadow-lg border border-slate-100 flex items-center gap-3 animate-bounce">
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
	</section>

	<!-- 2. SUB-HERO PROMO SECTION (Liên thông Đại học - Nâng tầm nghề nghiệp ngay) -->
	<section class="py-16 bg-white border-t border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
				<!-- Laptop student illustration representation -->
				<div class="lg:col-span-5 flex justify-center">
					<div class="relative w-full max-w-sm aspect-video sm:aspect-square bg-slate-50 border border-slate-100 rounded-3xl p-4 shadow-inner overflow-hidden">
						<div class="w-full h-full bg-cover bg-center rounded-2xl" style="background-image: url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=500');"></div>
					</div>
				</div>

				<!-- Right Side Promotion Texts -->
				<div class="lg:col-span-7 space-y-5">
					<span class="inline-block bg-[#EFF6FF] text-[#2563EB] text-sm font-bold px-3.5 py-1 rounded-full uppercase tracking-wider">
						LIÊN THÔNG ĐẠI HỌC
					</span>
					<h2 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight">
						NÂNG TẦM NGHỀ NGHIỆP NGAY
					</h2>
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

	<!-- 3. HOT MAJORS SECTION ("CÁC NGÀNH ĐÀO TẠO HOT NHẤT" matching the slider elements in mockup) -->
	<section class="py-16 bg-[#0B2545] text-white relative">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="text-center max-w-2xl mx-auto mb-12 space-y-3">
				<h2 class="text-2xl md:text-3xl font-black font-display tracking-tight text-white">
					CÁC NGÀNH ĐÀO TẠO HOT NHẤT
				</h2>
				<p class="text-slate-300 text-sm md:text-sm">
					Đội ngũ chuyên gia giàu kinh nghiệm sẽ giúp bạn chọn ngành học phù hợp với đam mê và xu hướng thị trường lao động.
				</p>
			</div>

			<!-- Grid representing the slider components -->
			<div class="grid grid-cols-2 md:grid-cols-6 gap-4">
				<!-- Industry Card 1 -->
				<div class="bg-white/5 border border-white/10 rounded-xl p-4 text-center hover:border-brand-primary hover:bg-white/10 transition-all cursor-pointer group">
					<div class="h-12 w-12 bg-white/10 text-white rounded-full flex items-center justify-center mx-auto mb-3 text-xl group-hover:scale-110 transition-transform">💻</div>
					<h4 class="font-bold text-sm md:text-sm text-slate-100">Công nghệ Thông Tin</h4>
				</div>
				<!-- Industry Card 2 -->
				<div class="bg-white/5 border border-white/10 rounded-xl p-4 text-center hover:border-brand-primary hover:bg-white/10 transition-all cursor-pointer group">
					<div class="h-12 w-12 bg-white/10 text-white rounded-full flex items-center justify-center mx-auto mb-3 text-xl group-hover:scale-110 transition-transform">📊</div>
					<h4 class="font-bold text-sm md:text-sm text-slate-100">Quản Trị Kinh Doanh</h4>
				</div>
				<!-- Industry Card 3 -->
				<div class="bg-white/5 border border-white/10 rounded-xl p-4 text-center hover:border-brand-primary hover:bg-white/10 transition-all cursor-pointer group">
					<div class="h-12 w-12 bg-white/10 text-white rounded-full flex items-center justify-center mx-auto mb-3 text-xl group-hover:scale-110 transition-transform">💵</div>
					<h4 class="font-bold text-sm md:text-sm text-slate-100">Kinh Tế - Tài Chính</h4>
				</div>
				<!-- Industry Card 4 -->
				<div class="bg-white/5 border border-white/10 rounded-xl p-4 text-center hover:border-brand-primary hover:bg-white/10 transition-all cursor-pointer group">
					<div class="h-12 w-12 bg-white/10 text-white rounded-full flex items-center justify-center mx-auto mb-3 text-xl group-hover:scale-110 transition-transform">🎯</div>
					<h4 class="font-bold text-sm md:text-sm text-slate-100">Marketing</h4>
				</div>
				<!-- Industry Card 5 -->
				<div class="bg-white/5 border border-white/10 rounded-xl p-4 text-center hover:border-brand-primary hover:bg-white/10 transition-all cursor-pointer group">
					<div class="h-12 w-12 bg-white/10 text-white rounded-full flex items-center justify-center mx-auto mb-3 text-xl group-hover:scale-110 transition-transform">🔤</div>
					<h4 class="font-bold text-sm md:text-sm text-slate-100">Ngôn Ngữ Anh</h4>
				</div>
				<!-- Industry Card 6 -->
				<div class="bg-white/5 border border-white/10 rounded-xl p-4 text-center hover:border-brand-primary hover:bg-white/10 transition-all cursor-pointer group">
					<div class="h-12 w-12 bg-white/10 text-white rounded-full flex items-center justify-center mx-auto mb-3 text-xl group-hover:scale-110 transition-transform">🎨</div>
					<h4 class="font-bold text-sm md:text-sm text-slate-100">Thiết Kế Đồ Họa</h4>
				</div>
			</div>
		</div>
	</section>

	<!-- 4. NATIONAL SCHOOLS SECTION ("CÁC TRƯỜNG ĐÀO TẠO TRÊN TOÀN QUỐC") -->
	<section id="program-section" class="py-16 md:py-24 bg-slate-50">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-12">
				<div class="space-y-2">
					<span class="text-[#2563EB] text-sm font-bold uppercase tracking-wider block">THÔNG TIN TUYỂN SINH</span>
					<h2 class="text-2xl md:text-3xl font-black text-slate-900">CÁC TRƯỜNG ĐÀO TẠO TRÊN TOÀN QUỐC</h2>
				</div>
				<a href="<?php echo esc_url( home_url( '/truong/' ) ); ?>" class="text-sm text-[#2563EB] font-bold hover:underline mt-4 sm:mt-0 flex items-center gap-1">
					Xem tất cả
					<span>→</span>
				</a>
			</div>
			
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
				<?php 
				$mock_schools = [
					[ 'title' => 'ĐẠI HỌC BÁCH KHOA HÀ NỘI', 'en' => 'Hanoi University of Science and Technology', 'rating' => '4.8', 'reviews' => '288', 'target' => '3.000', 'img' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&q=80&w=250' ],
					[ 'title' => 'ĐẠI HỌC KINH TẾ QUỐC DÂN', 'en' => 'National Economics University', 'rating' => '4.7', 'reviews' => '210', 'target' => '2.500', 'img' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=250' ],
					[ 'title' => 'ĐẠI HỌC NGOẠI THƯƠNG', 'en' => 'Foreign Trade University', 'rating' => '4.9', 'reviews' => '189', 'target' => '3.800', 'img' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&q=80&w=250' ],
					[ 'title' => 'HỌC VIỆN TÀI CHÍNH', 'en' => 'Academy of Finance', 'rating' => '4.8', 'reviews' => '150', 'target' => '2.800', 'img' => 'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&q=80&w=250' ],
					[ 'title' => 'ĐẠI HỌC CÔNG NGHỆ - ĐHQGHN', 'en' => 'VNU University of Engineering and Technology', 'rating' => '4.7', 'reviews' => '240', 'target' => '2.400', 'img' => 'https://images.unsplash.com/photo-1519452635265-7b1fbfd1e4e0?auto=format&fit=crop&q=80&w=250' ],
				];

				// Loop to output database query, or fall back to mock schools if empty
				$query_has_posts = false;
				if ( $schools_query->have_posts() ) {
					$query_has_posts = true;
					$index = 0;
					while ( $schools_query->have_posts() ) : $schools_query->the_post();
						$school_id = get_the_ID();
						$address = get_field( 'address', $school_id );
						$logo_id = get_field( 'logo', $school_id );
						$m_school = $mock_schools[$index % 5];
				?>
						<div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
							<div class="h-28 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $m_school['img'] ); ?>');"></div>
							<div class="p-4 flex-1 flex flex-col justify-between">
								<div>
									<h4 class="font-extrabold text-slate-800 text-sm md:text-sm tracking-tight leading-snug uppercase min-h-[36px] line-clamp-2"><?php the_title(); ?></h4>
									<p class="text-sm text-slate-400 mt-1 font-medium italic line-clamp-1"><?php echo esc_html( $m_school['en'] ); ?></p>
									<div class="flex items-center gap-1 mt-2 text-sm text-slate-500 font-semibold">
										<span class="text-yellow-400">★</span>
										<span><?php echo esc_html( $m_school['rating'] ); ?> (<?php echo esc_html( $m_school['reviews'] ); ?> đánh giá)</span>
									</div>
								</div>
								<div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-sm text-slate-600">
									<span>Chỉ tiêu: <span class="font-bold text-[#2563EB]"><?php echo esc_html( $m_school['target'] ); ?></span></span>
									<a href="<?php the_permalink(); ?>" class="bg-slate-50 hover:bg-[#2563EB]/10 text-[#2563EB] px-2.5 py-1 rounded font-bold transition-all text-sm uppercase">Chi tiết</a>
								</div>
							</div>
						</div>
				<?php 
						$index++;
					endwhile;
					wp_reset_postdata();
				}

				if ( ! $query_has_posts ) {
					// Fallback loops to draw mock schools if not seeded
					foreach ( $mock_schools as $s ) :
				?>
						<div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
							<div class="h-28 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $s['img'] ); ?>');"></div>
							<div class="p-4 flex-1 flex flex-col justify-between">
								<div>
									<h4 class="font-extrabold text-slate-800 text-sm md:text-sm tracking-tight leading-snug uppercase min-h-[36px] line-clamp-2"><?php echo esc_html( $s['title'] ); ?></h4>
									<p class="text-sm text-slate-400 mt-1 font-medium italic line-clamp-1"><?php echo esc_html( $s['en'] ); ?></p>
									<div class="flex items-center gap-1 mt-2 text-sm text-slate-500 font-semibold">
										<span class="text-yellow-400">★</span>
										<span><?php echo esc_html( $s['rating'] ); ?> (<?php echo esc_html( $s['reviews'] ); ?> đánh giá)</span>
									</div>
								</div>
								<div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-sm text-slate-600">
									<span>Chỉ tiêu: <span class="font-bold text-[#2563EB]"><?php echo esc_html( $s['target'] ); ?></span></span>
									<span class="bg-slate-50 text-[#2563EB] px-2.5 py-1 rounded font-bold text-sm uppercase">Chi tiết</span>
								</div>
							</div>
						</div>
				<?php
					endforeach;
				}
				?>
			</div>
		</div>
	</section>

	<!-- 5. WHY CHOOSE US ("VÌ SAO CHỌN NEXT PATH / LIÊN THÔNG ĐẠI HỌC?") -->
	<section class="py-16 bg-white border-t border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="text-center max-w-2xl mx-auto mb-12">
				<h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-2">VÌ SAO CHỌN LIÊN THÔNG ĐẠI HỌC?</h2>
				<p class="text-slate-500 text-sm md:text-sm">Chúng tôi đồng hành cùng bạn trên hành trình chinh phục tri thức.</p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-5 gap-4">
				<div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-6 text-center shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-full mx-auto mb-3 text-lg">👥</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Đội ngũ chuyên gia</h4>
					<p class="text-sm text-slate-500">Giàu kinh nghiệm, nhiệt tình và tận tâm tư vấn.</p>
				</div>
				<div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-6 text-center shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-full mx-auto mb-3 text-lg">📞</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Tư vấn 1:1 miễn phí</h4>
					<p class="text-sm text-slate-500">Lắng nghe và thấu hiểu nhu cầu của từng học viên.</p>
				</div>
				<div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-6 text-center shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-full mx-auto mb-3 text-lg">📈</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Lộ trình cá nhân hóa</h4>
					<p class="text-sm text-slate-500">Phù hợp tối đa với năng lực và mục tiêu sự nghiệp.</p>
				</div>
				<div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-6 text-center shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-full mx-auto mb-3 text-lg">🛡</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Hỗ trợ toàn diện</h4>
					<p class="text-sm text-slate-500">Từ định hướng chọn trường đến hoàn thiện hồ sơ.</p>
				</div>
				<div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-6 text-center shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-full mx-auto mb-3 text-lg">🎯</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Tỷ lệ thành công cao</h4>
					<p class="text-sm text-slate-500">Hàng ngàn học viên đã và đang đạt được tấm bằng mơ ước.</p>
				</div>
			</div>
		</div>
	</section>

	<!-- 6. STATS NUMBERS BANNER (Blue stats widget from mockup image) -->
	<section class="py-16 bg-gradient-to-r from-[#0E2038] to-[#1E3A8A] text-white overflow-hidden relative">
		<div class="absolute right-0 bottom-0 w-96 h-96 bg-[#2563EB]/10 rounded-full blur-3xl"></div>
		
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
				<div class="lg:col-span-7 space-y-4">
					<span class="inline-block bg-[#2563EB] text-white text-sm font-black px-3.5 py-1 rounded-full uppercase tracking-wider">
						LÝ DO NÊN CHỌN CHÚNG TÔI
					</span>
					<h3 class="text-2xl md:text-4xl font-black font-display tracking-tight leading-tight text-white">
						MỞ RỘNG CƠ HỘI HỌC TẬP & NGHỀ NGHIỆP<br>TỐT NHẤT CHO BẠN
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
							<span class="block font-display font-black text-3xl md:text-[40px] text-[#2563EB] leading-none mb-1">150+</span>
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
					<div class="relative w-full max-w-sm aspect-[4/3] rounded-2xl overflow-hidden border-4 border-white/10 shadow-2xl">
						<div class="w-full h-full bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=400');"></div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 7. DOUBLE CERTIFICATE VALUE PROPOSITION -->
	<section class="py-16 bg-white">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
			<!-- Box Left: BẰNG ĐẠO TẠO CHÍNH QUY -->
			<div class="bg-slate-50 border border-slate-100 rounded-2xl p-6 md:p-8 flex flex-col md:flex-row justify-between gap-6">
				<div class="flex-1 space-y-4">
					<span class="bg-[#EFF6FF] text-[#2563EB] text-sm font-black px-3 py-1 rounded-full uppercase tracking-wider inline-block">RẰNG CẤP TƯƠNG ĐƯƠNG</span>
					<h3 class="font-extrabold text-xl text-slate-900 leading-tight">BẰNG ĐẠO TẠO CHÍNH QUY</h3>
					<ul class="space-y-2 text-sm text-slate-600">
						<li>✔ Học chương trình chuẩn theo quy định của bộ GD&ĐT.</li>
						<li>✔ Đảm bảo giá trị pháp lý, sử dụng toàn quốc.</li>
						<li>✔ Phục vụ học tập, thi công chức, nâng lương nâng bậc.</li>
						<li>✔ Hồ sơ nhanh gọn - Quy trình rõ ràng.</li>
					</ul>
					<a href="#register-section" class="inline-block bg-[#2563EB] text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-[#1E40AF] transition-all pt-2">Tìm hiểu thêm</a>
				</div>
				<div class="w-full md:w-36 shrink-0 aspect-[3/4] bg-white border border-slate-100 rounded-xl p-2 flex items-center justify-center shadow-inner">
					<div class="text-center leading-none">
						<span class="text-4xl block mb-2">📜</span>
						<span class="text-sm text-slate-400 block uppercase font-bold">BẰNG ĐẠI HỌC</span>
					</div>
				</div>
			</div>

			<!-- Box Right: SUỐT ĐỜI TRÊN TOÀN QUỐC -->
			<div class="bg-slate-50 border border-slate-100 rounded-2xl p-6 md:p-8 flex flex-col md:flex-row justify-between gap-6">
				<div class="flex-1 space-y-4">
					<span class="bg-blue-50 text-blue-800 text-sm font-black px-3 py-1 rounded-full uppercase tracking-wider inline-block">RẰNG CÓ GIÁ TRỊ SỬ DỤNG</span>
					<h3 class="font-extrabold text-xl text-slate-900 leading-tight">SUỐT ĐỜI TRÊN TOÀN QUỐC</h3>
					<ul class="space-y-2 text-sm text-slate-600">
						<li>✔ Bằng đại học sử dụng lâu dài, không giới hạn thời gian.</li>
						<li>✔ Được công nhận rộng rãi bởi doanh nghiệp và nhà nước.</li>
						<li>✔ Cơ hội tiếp tục học lên cao học, thạc sĩ, tiến sĩ.</li>
						<li>✔ Hỗ trợ kết nối việc làm sau khi hoàn thành khóa học.</li>
					</ul>
					<a href="#register-section" class="inline-block bg-slate-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-slate-900 transition-all pt-2">Xem chi tiết</a>
				</div>
				<div class="w-full md:w-36 shrink-0 aspect-[3/4] bg-white border border-slate-100 rounded-xl p-2 flex items-center justify-center shadow-inner">
					<div class="text-center leading-none">
						<span class="text-4xl block mb-2">🛡</span>
						<span class="text-sm text-slate-400 block uppercase font-bold">CHỨNG CHỈ</span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 8. SEGMENTS SECTION ("CHƯƠNG TRÌNH PHÙ HỢP VỚI BẠN") -->
	<section class="py-16 bg-slate-50 border-y border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="text-center max-w-2xl mx-auto mb-12">
				<h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-2">CHƯƠNG TRÌNH PHÙ HỢP VỚI BẠN</h2>
				<p class="text-slate-500 text-sm md:text-sm">Chúng tôi thiết kế các lộ trình học tối ưu riêng cho từng nhóm đối tượng học viên.</p>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
				<!-- Segment 1 -->
				<div class="bg-white border border-slate-100 rounded-2xl p-5 hover:border-brand-primary transition-all flex flex-col justify-between">
					<div class="space-y-2">
						<div class="h-32 bg-slate-100 rounded-xl overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&q=80&w=200');"></div>
						<h4 class="font-extrabold text-slate-800 text-sm">Cho người đi làm</h4>
						<p class="text-sm text-slate-500 leading-normal">Lịch học linh hoạt, học trực tuyến 100% không làm gián đoạn công việc bận rộn hàng ngày.</p>
					</div>
				</div>
				<!-- Segment 2 -->
				<div class="bg-white border border-slate-100 rounded-2xl p-5 hover:border-brand-primary transition-all flex flex-col justify-between">
					<div class="space-y-2">
						<div class="h-32 bg-slate-100 rounded-xl overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?auto=format&fit=crop&q=80&w=200');"></div>
						<h4 class="font-extrabold text-slate-800 text-sm">Cho học sinh lớp 12</h4>
						<p class="text-sm text-slate-500 leading-normal">Định hướng nghề nghiệp sớm, đăng ký lộ trình học liên kết đại học - mở ra tương lai rộng mở.</p>
					</div>
				</div>
				<!-- Segment 3 -->
				<div class="bg-white border border-slate-100 rounded-2xl p-5 hover:border-brand-primary transition-all flex flex-col justify-between">
					<div class="space-y-2">
						<div class="h-32 bg-slate-100 rounded-xl overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&q=80&w=200');"></div>
						<h4 class="font-extrabold text-slate-800 text-sm">Cho người muốn liên thông</h4>
						<p class="text-sm text-slate-500 leading-normal">Từ Trung cấp/Cao đẳng lên Đại học. Rút ngắn thời gian đào tạo và tối ưu hóa chi phí học tập.</p>
					</div>
				</div>
				<!-- Segment 4 -->
				<div class="bg-white border border-slate-100 rounded-2xl p-5 hover:border-brand-primary transition-all flex flex-col justify-between">
					<div class="space-y-2">
						<div class="h-32 bg-slate-100 rounded-xl overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&q=80&w=200');"></div>
						<h4 class="font-extrabold text-slate-800 text-sm">Cho người muốn chuyển ngành</h4>
						<p class="text-sm text-slate-500 leading-normal">Đón đầu xu hướng chuyển dịch lao động sang các ngành công nghệ, dịch vụ hot nhất hiện nay.</p>
					</div>
				</div>
				<!-- Segment 5 -->
				<div class="bg-white border border-slate-100 rounded-2xl p-5 hover:border-brand-primary transition-all flex flex-col justify-between">
					<div class="space-y-2">
						<div class="h-32 bg-slate-100 rounded-xl overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=200');"></div>
						<h4 class="font-extrabold text-slate-800 text-sm">Cho người học văn bằng 2</h4>
						<p class="text-sm text-slate-500 leading-normal">Mở rộng kiến thức đa lĩnh vực, gấp đôi cơ hội ứng tuyển và nâng cao giá trị thương hiệu cá nhân.</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 9. DYNAMIC CONSULTATION FORM SECTION ("ĐĂNG KÝ NHẬN TƯ VẤN MIỄN PHÍ") -->
	<section id="register-section" class="py-16 bg-white">
		<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 border border-slate-100 shadow-xl rounded-3xl p-6 md:p-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center bg-gradient-to-tr from-[#EFF6FF]/35 to-white">
			<!-- Graduate photo column -->
			<div class="lg:col-span-5 hidden lg:block">
				<div class="h-[400px] w-full rounded-2xl overflow-hidden bg-slate-200 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=500');"></div>
			</div>

			<!-- Registration form column -->
			<div class="lg:col-span-7 space-y-6">
				<div>
					<h2 class="text-2xl md:text-3xl font-black text-slate-900 leading-tight">ĐĂNG KÝ NHẬN TƯ VẤN MIỄN PHÍ</h2>
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
								<input type="text" name="your-name" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Họ và tên của bạn">
							</div>
							<div>
								<label class="block text-sm font-bold text-slate-600 mb-1">Số điện thoại *</label>
								<input type="tel" name="your-phone" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Số điện thoại liên hệ">
							</div>
						</div>
						<div>
							<label class="block text-sm font-bold text-slate-600 mb-1">Địa chỉ Email (Tùy chọn)</label>
							<input type="email" name="your-email" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Địa chỉ email của bạn">
						</div>
						<div>
							<label class="block text-sm font-bold text-slate-600 mb-1">Chương trình quan tâm</label>
							<select name="current_program_id" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none">
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
							<textarea name="your-message" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Nhập câu hỏi hoặc yêu cầu cụ thể của bạn..."></textarea>
						</div>
						
						<div class="flex flex-col sm:flex-row items-center gap-4 pt-2">
							<button type="submit" class="w-full sm:w-auto bg-[#2563EB] text-white px-8 py-3.5 rounded-xl font-bold text-sm hover:bg-[#1E40AF] transition-all shadow-md shadow-brand-primary/20">
								GỬI THÔNG TIN
							</button>
							<span class="text-sm text-slate-400 font-medium text-center sm:text-left">Cam kết bảo mật thông tin tuyệt đối và chỉ sử dụng cho mục đích tư vấn tuyển sinh.</span>
						</div>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- 10. NEWS RECENT SECTION ("TIN TỨC MỚI NHẤT") -->
	<section class="py-16 bg-slate-50 border-t border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex justify-between items-end mb-12">
				<div class="space-y-2">
					<span class="text-[#2563EB] text-sm font-bold uppercase tracking-wider block">THÔNG TIN TUYỂN SINH</span>
					<h2 class="text-2xl md:text-3xl font-black text-slate-900">TIN TỨC MỚI NHẤT</h2>
				</div>
				<a href="<?php echo esc_url( home_url( '/tin-tuyen-sinh/' ) ); ?>" class="text-sm text-[#2563EB] font-bold hover:underline flex items-center gap-1">
					Xem tất cả
					<span>→</span>
				</a>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
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
						$m_news = $mock_news[$index % 4];
				?>
						<div class="bg-white border border-slate-100 rounded-2xl overflow-hidden hover:shadow-md transition-all flex flex-col justify-between">
							<div class="h-32 bg-slate-200 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=250');"></div>
							<div class="p-4 flex-1 flex flex-col justify-between">
								<div class="space-y-2">
									<span class="text-sm text-slate-400 font-semibold block"><?php echo get_the_date( 'd/m/Y' ); ?></span>
									<h4 class="font-extrabold text-slate-800 text-sm md:text-sm tracking-tight leading-snug line-clamp-2 hover:text-[#2563EB]">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h4>
									<p class="text-sm text-slate-400 leading-normal line-clamp-2"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
								</div>
								<a href="<?php the_permalink(); ?>" class="mt-4 text-sm text-[#2563EB] font-bold hover:underline block">Chi tiết bài viết</a>
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
						<div class="bg-white border border-slate-100 rounded-2xl overflow-hidden hover:shadow-md transition-all flex flex-col justify-between">
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
