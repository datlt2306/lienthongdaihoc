<?php

/**
 * Main index template (Homepage fully optimized to match Mockup UI/UX)
 *
 * @package lienthongdaihoc
 */

if (! defined('ABSPATH')) {
	exit;
}

get_header();

// Cache queries for schools
$featured_schools = ltdh_get_cached_featured_schools();

$news_query = ltdh_get_cached_query('ltdh_homepage_news', [
	'post_type'      => 'post',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
	'no_found_rows'  => true,
], HOUR_IN_SECONDS);

$hotline = ltdh_get_hotline();
$zalo    = ltdh_get_zalo_url();
?>

<main id="primary" class="site-main bg-white">

	<!-- 1. HERO SECTION -->
	<section class="relative bg-gradient-to-br from-[#EFF6FF] via-[#F8FAFC] to-[#FFFFFF] overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-32">
		<div class="absolute right-0 top-1/2 -translate-y-1/2 w-1/2 h-full opacity-10 bg-[radial-gradient(var(--tw-gradient-to-r,#00308b)_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
				<!-- Hero Left Text Column -->
				<div class="lg:col-span-6 space-y-6 text-center lg:text-left z-10">
					<div class="pt-2">
						<span class="bg-[#F5BF23] text-[#0F172A] px-6 py-2 rounded-full inline-block text-base font-extrabold shadow-md">
							<?php
							$hero_year = '';
							if (function_exists('get_field')) {
								$hero_year = get_field('hero_year_label', 'options');
							}
							echo esc_html($hero_year ?: ltdh_default('homepage', 'hero_year_label'));
							?>
						</span>
					</div>

					<?php
					$hero_heading = '';
					$hero_heading_hl = '';
					$hero_subtitle = '';
					if (function_exists('get_field')) {
						$hero_heading = get_field('hero_heading', 'options') ?: '';
						$hero_heading_hl = get_field('hero_heading_highlight', 'options') ?: '';
						$hero_subtitle = get_field('hero_subtitle', 'options') ?: '';
					}
					?>
					<h1 class="text-4xl font-black text-[#0B2545] leading-[1.1] tracking-tight font-display uppercase">
						<?php echo esc_html($hero_heading ?: 'Tìm ngành học liên thông'); ?><br>
						<?php if ($hero_heading_hl) : ?>
							<span class="text-brand-primary"><?php echo esc_html($hero_heading_hl); ?></span>
						<?php else : ?>
							<span class="text-brand-primary">phù hợp cho bạn</span>
						<?php endif; ?>
					</h1>
					<p class="text-lg text-slate-600 leading-relaxed max-w-lg">
						<?php echo esc_html($hero_subtitle ?: 'Học Liên thông Đại học chính quy, Đại học từ xa uy tín trên toàn quốc'); ?>
					</p>


					<!-- Badges Grid (3 items row layout) -->
					<div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 w-full text-left">
						<?php
						$hero_badges = [];
						if (function_exists('get_field')) {
							$hero_badges = get_field('hero_badges', 'options') ?: [];
						}
						if (empty($hero_badges)) {
							$hero_badges = ltdh_default('homepage', 'hero_badges');
						}
						foreach ($hero_badges as $badge) :
						?>
							<div class="flex items-center gap-3 bg-white px-4 py-3.5 rounded-xl border border-slate-100 shadow-sm">
								<div class="leading-tight">
									<span class="block text-sm font-bold text-slate-800"><?php echo esc_html($badge['text'] ?? ''); ?></span>
									<span class="text-sm text-slate-500"><?php echo esc_html($badge['subtext'] ?? ''); ?></span>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<!-- CTAs -->
					<div class="flex flex-col sm:flex-row gap-4 pt-2 justify-center lg:justify-start">
						<?php
						$cta_text = '';
						$cta_url = '';
						$cta_sec_text = '';
						if (function_exists('get_field')) {
							$cta_text = get_field('hero_cta_primary_text', 'options') ?: '';
							$cta_url = get_field('hero_cta_primary_url', 'options') ?: '';
							$cta_sec_text = get_field('hero_cta_secondary_text', 'options') ?: '';
						}
						?>
						<a href="<?php echo esc_url($cta_url ? $cta_url : home_url('/he-dao-tao/tu-xa/')); ?>" class="bg-brand-accent text-white text-center px-8 py-4 rounded-xl font-bold hover:bg-[#e06e00] transition-all flex items-center justify-center gap-2 shadow-md shadow-emerald-600/20">
							<?php echo esc_html($cta_text ?: 'Tra cứu tuyển sinh'); ?>
							<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
							</svg>
						</a>
						<a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $hotline)); ?>" class="flex items-center justify-center gap-3 bg-white border border-slate-200 px-6 py-4 rounded-xl font-bold text-slate-800 hover:bg-slate-50 transition-all">
							<span class="h-10 w-10 bg-emerald-50 text-emerald-600 flex items-center justify-center rounded-full shrink-0">📞</span>
							<div class="text-left leading-tight">
								<span class="block text-xs text-slate-400 font-medium"><?php echo esc_html($cta_sec_text ?: 'Hotline tư vấn'); ?></span>
								<span class="text-sm font-black text-brand-primary"><?php echo esc_html($hotline); ?></span>
							</div>
						</a>
					</div>
				</div>

				<!-- Hero Right Image Column -->
				<div class="lg:col-span-6 relative hidden lg:flex justify-center items-center">
					<?php
					$hero_img = '';
					if (function_exists('get_field')) {
						$hero_img = get_field('hero_image', 'options') ?: '';
					}
					?>
					<div class="relative w-full max-w-md md:max-w-lg aspect-square">
						<div class="absolute inset-0 bg-gradient-to-tr from-brand-primary/20 to-[#EFF6FF]/50 rounded-lg blur-2xl -z-10"></div>
						<div class="w-full h-full  overflow-hidden relative bg-slate-100 flex items-center justify-center">
							<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo esc_url($hero_img ?: ltdh_get_fallback_image('hero')); ?>');"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Search & Filters Container (Compact Single-Row Bar) -->
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-30 hidden">
		<div class="bg-white rounded-xl shadow-xl border border-slate-100 p-4 md:p-5">
			<form action="<?php echo esc_url(home_url('/he-dao-tao/tu-xa/')); ?>" method="GET" class="space-y-3 md:space-y-0">
				<!-- Keyword (always visible) -->
				<div class="flex-1 min-w-[20%]">
					<input type="text" name="s" placeholder="Từ khóa tìm kiếm..." class="w-full border border-slate-200 rounded-lg px-3 py-2.5 md:py-2 text-sm md:text-xs focus:border-brand-primary focus:outline-none placeholder-slate-400 font-medium min-h-[40px] md:min-h-[38px]" />
				</div>

				<?php
				$schools = get_posts(['post_type' => 'school', 'numberposts' => -1]);
				$majors  = get_posts(['post_type' => 'major', 'numberposts' => -1]);
				$types   = get_terms(['taxonomy' => LTDH_TAX_TRAINING_TYPE, 'hide_empty' => false]);
				?>

				<!-- Filters: stacked on mobile, inline on desktop -->
				<div class="flex flex-col md:flex-row md:items-center md:gap-3 gap-2">
					<div class="flex-1">
						<select name="truong" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 md:py-2 text-sm md:text-xs focus:border-brand-primary focus:outline-none bg-white md:bg-transparent min-h-[40px] md:min-h-[38px]">
							<option value="">-- Chọn trường --</option>
							<?php foreach ($schools as $sc) : ?>
								<option value="<?php echo esc_attr($sc->post_name); ?>"><?php echo esc_html($sc->post_title); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="flex-1">
						<select name="nganh" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 md:py-2 text-sm md:text-xs focus:border-brand-primary focus:outline-none bg-white md:bg-transparent min-h-[40px] md:min-h-[38px]">
							<option value="">-- Chọn ngành học --</option>
							<?php foreach ($majors as $mj) : ?>
								<option value="<?php echo esc_attr($mj->post_name); ?>"><?php echo esc_html($mj->post_title); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="flex-1">
						<select name="he" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 md:py-2 text-sm md:text-xs focus:border-brand-primary focus:outline-none bg-white md:bg-transparent min-h-[40px] md:min-h-[38px]">
							<option value="">-- Chọn hệ học --</option>
							<?php foreach ($types as $tp) : ?>
								<option value="<?php echo esc_attr($tp->slug); ?>"><?php echo esc_html($tp->name); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="flex gap-2 md:shrink-0">
						<button type="submit" class="flex-1 md:flex-none bg-brand-primary hover:bg-brand-darkBlue text-white font-extrabold text-xs px-5 py-2.5 rounded-lg transition-all uppercase tracking-wider min-h-[40px] md:min-h-[38px]">
							Tìm kiếm
						</button>
						<a href="<?php echo esc_url(home_url('/he-dao-tao/tu-xa/')); ?>" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-lg transition-all flex items-center justify-center min-h-[40px] md:min-h-[38px]" title="Reset bộ lọc">
							🔄
						</a>
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- 2. HOT PROGRAMS SECTION -->
	<section class="py-12 bg-slate-50 border-y border-slate-100 relative overflow-hidden">
		<div class="max-w-7xl mx-auto relative z-10">
			<div class="text-center max-w-2xl mx-auto mb-8 space-y-2 px-4">
				<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">KHÁM PHÁ NGÀNH HỌC</span>
				<h2 class="text-xl md:text-3xl font-black text-slate-900">5 ngành đào tạo hot nhất</h2>
				<p class="text-slate-500 text-sm">Top ngành được yêu thích nhất, phù hợp xu hướng thị trường lao động.</p>
			</div>

			<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 px-4 md:px-0">
				<?php
				$hot_majors = ltdh_get_hot_majors();
				$card_colors = [
					['bg' => 'bg-blue-50',   'icon_bg' => 'bg-blue-100',   'icon_text' => 'text-blue-600',   'border' => 'border-blue-100',   'hover' => 'hover:border-blue-300'],
					['bg' => 'bg-violet-50', 'icon_bg' => 'bg-violet-100', 'icon_text' => 'text-violet-600', 'border' => 'border-violet-100', 'hover' => 'hover:border-violet-300'],
					['bg' => 'bg-emerald-50', 'icon_bg' => 'bg-emerald-100', 'icon_text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'hover' => 'hover:border-emerald-300'],
					['bg' => 'bg-amber-50',  'icon_bg' => 'bg-amber-100',  'icon_text' => 'text-amber-600',  'border' => 'border-amber-100',  'hover' => 'hover:border-amber-300'],
					['bg' => 'bg-rose-50',   'icon_bg' => 'bg-rose-100',   'icon_text' => 'text-rose-600',   'border' => 'border-rose-100',   'hover' => 'hover:border-rose-300'],
				];

				$major_icons = [
					'cong-nghe-thong-tin' => '💻',
					'quan-tri-kinh-doanh' => '📈',
					'ke-toan' => '💰',
					'thuong-mai-dien-tu' => '🏪',
					'logistics' => '🚚',
				];

				if (! empty($hot_majors)) :
					$i = 0;
					foreach ($hot_majors as $maj) :
						$slug = $maj->post_name;
						$icon = $major_icons[$slug] ?? '🎓';
						$c = $card_colors[$i % count($card_colors)];
						$i++;
				?>
						<a href="<?php echo esc_url(get_permalink($maj->ID)); ?>"
							class="group flex flex-col items-center text-center p-4 bg-white border <?php echo $c['border']; ?> rounded-xl <?php echo $c['hover']; ?> hover:shadow-md transition-all">
							<div class="h-12 w-12 <?php echo $c['icon_bg']; ?> <?php echo $c['icon_text']; ?> rounded-xl flex items-center justify-center mb-3 text-xl group-hover:scale-110 transition-transform">
								<?php echo $icon; ?>
							</div>
							<h4 class="font-bold text-xs text-slate-700 leading-snug line-clamp-2"><?php echo esc_html($maj->post_title); ?></h4>
						</a>
				<?php
					endforeach;
				else :
					echo '<p class="text-sm text-slate-400 col-span-5 text-center">Chưa cấu hình ngành hot.</p>';
				endif;
				?>
			</div>

			<div class="text-center mt-8">
				<a href="<?php echo esc_url(home_url('/nganh-hoc/')); ?>"
					class="inline-flex items-center gap-2 text-[#00308b] font-bold text-sm hover:underline">
					Xem tất cả ngành học <span>→</span>
				</a>
			</div>
		</div>
	</section>

	<!-- 3. NATIONAL SCHOOLS SECTION -->
	<section id="program-section" class="py-12 md:py-16 bg-white">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8">
				<div class="space-y-2">
					<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">ĐỐI TÁC ĐẠI HỌC</span>
					<h2 class="text-xl md:text-3xl font-black text-slate-900">Trường liên kết đào tạo</h2>
				</div>
				<a href="<?php echo esc_url(home_url('/truong-lien-ket/')); ?>" class="text-sm text-brand-primary font-bold hover:underline mt-4 sm:mt-0 flex items-center gap-1">
					Xem tất cả
					<span>→</span>
				</a>
			</div>

			<div class="flex lg:grid overflow-x-auto lg:overflow-x-visible snap-x snap-mandatory lg:snap-none gap-4 pb-4 lg:pb-0 no-scrollbar lg:grid-cols-5">
				<?php
				if ( ! empty( $featured_schools ) ) {
					foreach ( $featured_schools as $school ) :
						$school_id = $school['id'];
						$address = $school['address'];
						$hotline = $school['hotline'];
						$thumb_url = $school['thumb_url'];
						$logo_id = $school['logo_id'];
						$en_name = $school['en_name'];
						$systems_label = $school['systems_label'];
						$prog_count = $school['prog_count'];
				?>
						<div class="bg-white border border-slate-100 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between shrink-0 w-[45vw] sm:w-[250px] lg:w-auto snap-center">
							<div class="h-20 md:h-28 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url($thumb_url); ?>');"></div>
							<div class="h-12 w-12 md:h-16 md:w-16 bg-white rounded-lg border-2 md:border-4 border-white shadow-md bg-white -mt-6 md:-mt-8 mx-auto z-10 relative flex items-center justify-center overflow-hidden">
								<?php if ($logo_id) : ?>
									<?php echo wp_get_attachment_image($logo_id, 'thumbnail', false, ['class' => 'h-full w-full object-contain']); ?>
								<?php else : ?>
									<span class="font-display font-extrabold text-brand-primary text-xs">UNI</span>
								<?php endif; ?>
							</div>

							<div class="p-4 pt-2 flex-1 flex flex-col justify-between">
								<div class="text-center">
									<h4 class="font-extrabold text-slate-800 text-xs md:text-sm tracking-tight leading-snug uppercase min-h-[36px] line-clamp-2 mt-1"><?php echo esc_html($school['title']); ?></h4>
									<p class="text-[11px] text-slate-400 mt-0.5 font-medium line-clamp-1 italic"><?php echo esc_html($en_name); ?></p>
									<div class="mt-3 space-y-1 text-center text-[10px] md:text-xs">
										<?php if ( ! empty($systems_label) ) : ?>
											<span class="font-bold text-brand-primary bg-blue-50 px-2.5 py-1 rounded-full inline-block leading-none mb-1.5"><?php echo esc_html($systems_label); ?></span>
										<?php endif; ?>
										<p class="text-slate-500 font-semibold">📊 <?php echo esc_html($prog_count); ?> ngành tuyển sinh</p>
									</div>
								</div>
								<div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-sm text-slate-600">
									<a href="<?php echo esc_url($school['permalink']); ?>" class="w-full text-center bg-slate-50 hover:bg-brand-accent hover:text-white py-2 rounded-lg font-bold transition-all text-xs uppercase text-brand-primary">Tìm hiểu thêm</a>
								</div>
							</div>
						</div>
				<?php
					endforeach;
				} else {
					echo '<div class="col-span-5 text-center text-slate-500 py-6">Chưa có trường liên kết nào được gieo dữ liệu.</div>';
				}
				?>
			</div>
		</div>
	</section>


	<!-- 8. DOUBLE CERTIFICATE VALUE PROPOSITION -->
	<?php
	$c_left_badge = '';
	$c_left_heading = '';
	$c_left_items = '';
	$c_left_cta = '';
	$c_right_badge = '';
	$c_right_heading = '';
	$c_right_items = '';
	$c_right_cta = '';
	$c_left_badge = '';
	$c_left_heading = '';
	$c_slider_items = [];
	$c_left_image = '';
	$c_left_youtube = '';
	$c_right_image = '';
	$c_right_content = '';
	if (function_exists('get_field')) {
		$c_left_badge = get_field('hp_cert_left_badge', 'options') ?: '';
		$c_left_heading = get_field('hp_cert_left_heading', 'options') ?: '';
		$c_slider_items = get_field('hp_cert_slider', 'options') ?: [];
		$c_left_image = get_field('hp_cert_left_image', 'options') ?: '';
		$c_left_youtube = get_field('hp_cert_left_youtube', 'options') ?: '';
		$c_right_image = get_field('hp_cert_right_image', 'options') ?: '';
		$c_right_content = get_field('hp_cert_right_content', 'options') ?: '';
	}
	?>
	<section class="py-16 bg-white overflow-hidden">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 lg:space-y-24">
			
			<!-- Row 1: [Text Content] [Slider] -->
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
				<!-- Left column: Text Content -->
				<div class="space-y-6">
					<span class="inline-block bg-emerald-50 text-emerald-600 text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider"><?php echo esc_html($c_left_badge ?: 'BẰNG CẤP TƯƠNG ĐƯƠNG'); ?></span>
					
					<h2 class="text-3xl md:text-4xl font-black text-slate-900 leading-tight">
						<?php 
						$heading = $c_left_heading ?: 'Không ghi hình thức đào tạo';
						$words = explode(' ', $heading);
						if ( count($words) > 2 ) {
							$last_words = implode(' ', array_slice($words, -3));
							$first_words = implode(' ', array_slice($words, 0, count($words) - 3));
							echo esc_html($first_words) . ' <span class="text-brand-primary inline">' . esc_html($last_words) . '</span>';
						} else {
							echo esc_html($heading);
						}
						?>
					</h2>

					<div class="space-y-4 text-slate-600 text-sm md:text-base leading-relaxed">
						<p>Từ ngày <strong>01/03/2020</strong>, bằng tốt nghiệp đại học sẽ không còn phân biệt hình thức đào tạo như chính quy, tại chức, từ xa...</p>
						<p>Theo <strong>Thông tư 27/2019/TT-BGDĐT</strong>, trên văn bằng sẽ vẫn ghi các nội dung cũ nhưng không ghi thông tin về hình thức đào tạo.</p>
					</div>

					<div class="pt-4 border-t border-slate-100">
						<h3 class="font-bold text-slate-800 text-lg mb-4">Có Giá Trị Sử Dụng Suốt Đời Trên Toàn Quốc</h3>
						
						<div class="space-y-4">
							<div class="flex gap-4">
								<div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
									<svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="3">
										<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
									</svg>
								</div>
								<div>
									<h4 class="font-bold text-slate-900 text-base">Bằng đỏ</h4>
									<p class="text-slate-500 text-sm leading-relaxed">Sau khi hoàn thành chương trình, học viên sẽ được trường Đại học cấp bằng Cử nhân (Bằng đỏ), được Bộ GD&ĐT công nhận.</p>
								</div>
							</div>

							<div class="flex gap-4">
								<div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
									<svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="3">
										<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
									</svg>
								</div>
								<div>
									<h4 class="font-bold text-slate-900 text-base">Có Thể Dùng Để Học Lên Các Bậc Cao Hơn</h4>
									<p class="text-slate-500 text-sm leading-relaxed">Thi công chức nhà nước, xét nâng bậc lương, đi du học.</p>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Right column: Slider Wrapper -->
				<div class="relative flex justify-center w-full">
					<style>
						.cert-slider-wrapper {
							position: relative;
							width: 100%;
							max-width: 32rem; /* max-w-lg */
							aspect-ratio: 1.4; /* Perfect aspect ratio for certificate documents */
							border-radius: 1.5rem; /* rounded-3xl */
							overflow: hidden;
							box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); /* shadow-2xl */
							background-color: #ffffff; /* match white degree background */
						}
						.cert-slider-container {
							position: relative;
							width: 100%;
							height: 100%;
							overflow: hidden;
						}
						.cert-slide {
							position: absolute;
							top: 0;
							left: 0;
							width: 100%;
							height: 100%;
							opacity: 0;
							z-index: 1;
							transition: opacity 1s ease-in-out;
						}
						.cert-slide.active {
							opacity: 1;
							z-index: 10;
						}
						.cert-slide img {
							width: 100%;
							height: 100%;
							object-fit: contain;
							background-color: #ffffff;
						}
						.cert-slider-dots {
							position: absolute;
							bottom: 24px;
							right: 24px;
							z-index: 20;
							display: flex;
							gap: 8px;
						}
						.cert-slider-dot {
							width: 10px;
							height: 10px;
							border-radius: 50%;
							border: none;
							background-color: rgba(255, 255, 255, 0.5);
							cursor: pointer;
							transition: all 0.3s ease;
						}
						.cert-slider-dot.active {
							background-color: #ffffff;
							transform: scale(1.2);
						}
					</style>
					<div class="cert-slider-wrapper">
						<!-- Slider Container -->
						<div class="cert-slider-container">
							<?php 
							$slides = [];
							if ( ! empty( $c_slider_items ) ) {
								foreach ( $c_slider_items as $item ) {
									if ( ! empty( $item['image'] ) ) {
										$img_val = $item['image'];
										if ( is_numeric( $img_val ) ) {
											$slides[] = wp_get_attachment_url( $img_val );
										} elseif ( is_array( $img_val ) && isset( $img_val['url'] ) ) {
											$slides[] = $img_val['url'];
										} else {
											$slides[] = $img_val;
										}
									}
								}
							}
							
							if ( empty( $slides ) ) {
								$slides[] = home_url('wp-content/uploads/2026/07/students-graduation.jpg');
							}
							
							foreach ( $slides as $index => $slide_url ) :
							?>
								<div class="cert-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-slide-index="<?php echo $index; ?>">
									<img src="<?php echo esc_url($slide_url); ?>" alt="Students Graduation Slide">
								</div>
							<?php endforeach; ?>
						</div>

						<!-- Slider Dots -->
						<?php if ( count($slides) > 1 ) : ?>
							<div class="cert-slider-dots">
								<?php foreach ( $slides as $index => $slide ) : ?>
									<button class="cert-slider-dot <?php echo $index === 0 ? 'active' : ''; ?>" data-dot-index="<?php echo $index; ?>" aria-label="Go to slide <?php echo $index + 1; ?>"></button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<!-- Orange badge -->
						<div class="absolute bottom-6 left-6 bg-[#f97316] text-white p-5 rounded-2xl shadow-xl flex flex-col justify-center max-w-[150px] z-20 hover:scale-105 transition-transform duration-300 pointer-events-none">
							<span class="text-3xl font-black leading-none">100%</span>
							<span class="text-[9px] font-extrabold tracking-wider uppercase mt-2 leading-tight">BẰNG CỬ NHÂN<br>CHÍNH QUY</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Row 2: [Video Card] [Text Content] -->
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
				<!-- Left column: Video Card -->
				<div class="relative overflow-hidden rounded-3xl shadow-lg aspect-video group bg-slate-800 flex items-center justify-center <?php echo !empty($c_left_youtube) ? 'cursor-pointer' : ''; ?>" <?php echo !empty($c_left_youtube) ? 'data-youtube="' . esc_url($c_left_youtube) . '"' : ''; ?>>
					<img src="<?php echo esc_url($c_left_image ?: home_url('wp-content/uploads/2026/07/vtv-news-thumb.png')); ?>" alt="VTV24 Video Thumbnail" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
					
					<?php if (!empty($c_left_youtube)): ?>
						<!-- Play button overlay -->
						<div class="absolute inset-0 bg-slate-950/20 flex items-center justify-center">
							<div class="w-16 h-16 bg-[#e11d48] text-white rounded-full flex items-center justify-center shadow-2xl group-hover:scale-110 transition-transform duration-300">
								<svg class="w-8 h-8 fill-current translate-x-0.5" viewBox="0 0 24 24">
									<path d="M8 5v14l11-7z"/>
								</svg>
							</div>
						</div>
					<?php endif; ?>

					<!-- Bottom title bar -->
					<div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent p-5 pt-10">
						<p class="text-white font-extrabold text-sm md:text-base leading-snug">Văn bằng đại học mới sẽ không còn ghi hình thức đào tạo - VTV24 News</p>
					</div>
				</div>

				<!-- Right column: Text Content -->
				<div class="space-y-6">
					<?php if (!empty($c_right_content)): ?>
						<div class="prose max-w-none text-slate-700 text-sm md:text-base leading-relaxed">
							<?php echo wp_kses_post($c_right_content); ?>
						</div>
					<?php else: ?>
						<span class="inline-block bg-emerald-50 text-emerald-600 text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">
							Bạn có thể sử dụng văn bằng để làm gì?
						</span>
						
						<h2 class="text-3xl md:text-4xl font-black text-slate-900 leading-tight">
							<?php 
							$right_heading = 'Văn bằng sau tốt nghiệp';
							$words_right = explode(' ', $right_heading);
							if ( count($words_right) > 2 ) {
								$last_words_right = implode(' ', array_slice($words_right, -3));
								$first_words_right = implode(' ', array_slice($words_right, 0, count($words_right) - 3));
								echo esc_html($first_words_right) . ' <span class="text-brand-primary inline">' . esc_html($last_words_right) . '</span>';
							} else {
								echo esc_html($right_heading);
							}
							?>
						</h2>

						<div class="space-y-4 text-slate-600 text-sm md:text-base leading-relaxed">
							<p>Sau khi tốt nghiệp, người học được cấp văn bằng theo quy định hiện hành và có thể sử dụng để phục vụ các mục tiêu học tập, nghề nghiệp theo điều kiện của từng đơn vị tiếp nhận.</p>
						</div>

						<div class="pt-4 border-t border-slate-100">
							<h3 class="font-bold text-slate-800 text-lg mb-4">Giá trị sử dụng của văn bằng</h3>
							
							<div class="space-y-4">
								<div class="flex gap-4">
									<div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
										<svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="3">
											<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
										</svg>
									</div>
									<div>
										<h4 class="font-bold text-slate-900 text-base">Học tiếp lên trình độ cao hơn</h4>
										<p class="text-slate-500 text-sm leading-relaxed">Có thể đăng ký dự tuyển chương trình sau đại học khi đáp ứng điều kiện tuyển sinh.</p>
									</div>
								</div>

								<div class="flex gap-4">
									<div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
										<svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="3">
											<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
										</svg>
									</div>
									<div>
										<h4 class="font-bold text-slate-900 text-base">Bổ sung và hoàn thiện hồ sơ nghề nghiệp</h4>
										<p class="text-slate-500 text-sm leading-relaxed">Phục vụ yêu cầu về trình độ chuyên môn đối với vị trí việc làm phù hợp.</p>
									</div>
								</div>

								<div class="flex gap-4">
									<div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
										<svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="3">
											<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
										</svg>
									</div>
									<div>
										<h4 class="font-bold text-slate-900 text-base">Tham gia tuyển dụng, thi tuyển</h4>
										<p class="text-slate-500 text-sm leading-relaxed">Sử dụng văn bằng trong hồ sơ dự tuyển theo yêu cầu cụ thể của cơ quan, đơn vị tuyển dụng.</p>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
			
		</div>
	</section>

	<!-- YouTube Video Popup Modal -->
	<div id="youtube-video-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/80 p-4 transition-all duration-300 opacity-0">
		<div class="relative w-full max-w-4xl bg-black rounded-lg overflow-hidden shadow-2xl">
			<!-- Close Button -->
			<button id="close-video-modal" class="absolute top-3 right-3 text-white/80 hover:text-white bg-slate-800/50 hover:bg-slate-700 rounded-full p-2 focus:outline-none transition-colors z-50">
				<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
				</svg>
			</button>
			<!-- Video Container -->
			<div class="aspect-video w-full">
				<iframe id="modal-video-iframe" class="w-full h-full" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const modal = document.getElementById('youtube-video-modal');
			const iframe = document.getElementById('modal-video-iframe');
			const closeBtn = document.getElementById('close-video-modal');
			const triggerElements = document.querySelectorAll('[data-youtube]');

			function getYoutubeId(url) {
				const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
				const match = url.match(regExp);
				return (match && match[2].length === 11) ? match[2] : null;
			}

			triggerElements.forEach(elem => {
				elem.addEventListener('click', function() {
					const url = this.getAttribute('data-youtube');
					const videoId = getYoutubeId(url);
					if (videoId) {
						iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
						modal.classList.remove('hidden');
						modal.classList.add('flex');
						// Force reflow
						setTimeout(() => {
							modal.classList.remove('opacity-0');
							modal.classList.add('opacity-100');
						}, 50);
					}
				});
			});

			function closeModal() {
				modal.classList.remove('opacity-100');
				modal.classList.add('opacity-0');
				setTimeout(() => {
					modal.classList.remove('flex');
					modal.classList.add('hidden');
					iframe.src = '';
				}, 300);
			}

			if (closeBtn) {
				closeBtn.addEventListener('click', closeModal);
			}
			if (modal) {
				modal.addEventListener('click', function(e) {
					if (e.target === modal) {
						closeModal();
					}
				});
			}

			// Image Slider Logic
			const slides = document.querySelectorAll('.cert-slide');
			const dots = document.querySelectorAll('.cert-slider-dot');
			if (slides.length > 1) {
				let currentSlide = 0;
				let slideInterval;

				function showSlide(index) {
					slides.forEach((slide, i) => {
						if (i === index) {
							slide.classList.remove('opacity-0', 'z-0');
							slide.classList.add('opacity-100', 'z-10');
						} else {
							slide.classList.remove('opacity-100', 'z-10');
							slide.classList.add('opacity-0', 'z-0');
						}
					});
					dots.forEach((dot, i) => {
						if (i === index) {
							dot.classList.remove('bg-white/50', 'scale-100');
							dot.classList.add('bg-white', 'scale-125');
						} else {
							dot.classList.remove('bg-white', 'scale-125');
							dot.classList.add('bg-white/50', 'scale-100');
						}
					});
					currentSlide = index;
				}

				function nextSlide() {
					let next = (currentSlide + 1) % slides.length;
					showSlide(next);
				}

				function startSlider() {
					slideInterval = setInterval(nextSlide, 4000);
				}

				function stopSlider() {
					clearInterval(slideInterval);
				}

				dots.forEach((dot, i) => {
					dot.addEventListener('click', () => {
						stopSlider();
						showSlide(i);
						startSlider();
					});
				});

				startSlider();
			}
		});
	</script>
	<!-- 5. STUDENT BENEFITS -->
	<?php
	$b_heading = '';
	$b_desc = '';
	$b_items = [];
	if (function_exists('get_field')) {
		$b_heading = get_field('hp_benefits_heading', 'options') ?: '';
		$b_desc = get_field('hp_benefits_desc', 'options') ?: '';
		$b_items = get_field('hp_benefits_items', 'options') ?: [];
	}
	$b_items_default = [
		['icon' => '📜', 'title' => 'Bằng chuẩn Bộ GD&ĐT', 'desc' => 'Phôi bằng tương đương chính quy, đủ điều kiện xét bậc lương và học lên Cao học.'],
		['icon' => '🔗', 'title' => 'Miễn giảm tín chỉ tối đa', 'desc' => 'Xét duyệt bảng điểm cũ để lược bỏ các môn đã học, rút ngắn thời gian ra trường.'],
		['icon' => '💻', 'title' => 'Học Online linh hoạt', 'desc' => 'Học trực tuyến mọi lúc mọi nơi qua E-learning. Thi tập trung vào cuối tuần.'],
		['icon' => '📝', 'title' => 'Hỗ trợ hồ sơ từ A-Z', 'desc' => 'Tiếp nhận hồ sơ online nhanh gọn, hoàn thiện thủ tục nhập học trọn gói.'],
	];
	$b_items_render = !empty($b_items) ? $b_items : $b_items_default;
	?>
	<section class="py-12 bg-white relative overflow-hidden">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
			<div class="text-center max-w-2xl mx-auto mb-8">
				<h2 class="text-xl md:text-3xl font-black text-slate-900 mb-2"><?php echo esc_html($b_heading ?: 'Lợi ích dành cho bạn'); ?></h2>
				<p class="text-slate-500 text-sm"><?php echo esc_html($b_desc ?: 'Chương trình tối ưu giúp người đi làm nâng cao bằng cấp dễ dàng.'); ?></p>
			</div>
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
				<?php foreach ($b_items_render as $item) : ?>
					<div class="bg-slate-50/70 border border-slate-100 rounded-xl p-5 text-left shadow-sm">
						<div class="h-10 w-10 bg-blue-50 text-[#00308b] flex items-center justify-center rounded-lg mb-3 text-lg"><?php echo esc_html($item['icon'] ?? '📜'); ?></div>
						<h4 class="font-bold text-sm text-slate-800 mb-1"><?php echo esc_html($item['title'] ?? ''); ?></h4>
						<p class="text-xs text-slate-500 leading-relaxed"><?php echo esc_html($item['desc'] ?? ''); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- 6. WHY CHOOSE US & METRICS -->
	<?php
	$w_heading = '';
	$w_desc = '';
	if (function_exists('get_field')) {
		$w_heading = get_field('hp_whyus_heading', 'options') ?: '';
		$w_desc = get_field('hp_whyus_desc', 'options') ?: '';
	}
	?>
	<section class="py-8 bg-gradient-to-r from-[#0E2038] to-[#00308b] text-white relative">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex flex-col lg:flex-row items-center justify-between gap-6">
				<div class="w-full lg:w-1/3 text-center lg:text-left">
					<h3 class="text-lg md:text-xl font-extrabold font-display tracking-tight text-white uppercase">
						<?php echo esc_html($w_heading ?: 'Vì sao chọn chúng tôi?'); ?>
					</h3>
					<p class="text-slate-400 text-xs mt-1"><?php echo esc_html($w_desc ?: 'Đơn vị tư vấn và liên kết tuyển sinh hàng đầu.'); ?></p>
				</div>
				<div class="w-full lg:w-2/3 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
					<?php
					$kpi_metrics = [];
					if (function_exists('get_field')) {
						$kpi_metrics = get_field('kpi_metrics', 'options') ?: [];
					}
					if (empty($kpi_metrics)) {
						$kpi_metrics = ltdh_default('homepage', 'kpi_metrics');
					}
					foreach ($kpi_metrics as $metric) :
					?>
						<div>
							<span class="block font-display font-black text-2xl md:text-3xl text-brand-accent leading-none mb-1"><?php echo esc_html($metric['value'] ?? ''); ?></span>
							<span class="text-xs text-slate-300 font-bold block"><?php echo esc_html($metric['label'] ?? ''); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>

	<!-- 7. STUDENT TESTIMONIALS -->
	<section class="py-12 bg-white relative overflow-hidden">
		<div class="absolute -left-16 -top-16 w-64 h-64 bg-brand-accent/10 rounded-full blur-3xl pointer-events-none z-0"></div>
		<div class="absolute -right-16 -bottom-16 w-64 h-64 bg-brand-accent/10 rounded-full blur-3xl pointer-events-none z-0"></div>
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
			<div class="text-center max-w-2xl mx-auto mb-8 space-y-3">
				<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">CẢM NHẬN HỌC VIÊN</span>
				<h2 class="text-xl md:text-3xl font-black text-slate-900">Đánh giá của học viên</h2>
				<p class="text-slate-500 text-sm">Hàng ngàn học viên đã tin tưởng và đồng hành cùng chúng tôi trên hành trình chinh phục tấm bằng đại học.</p>
			</div>

			<div class="flex md:grid overflow-x-auto md:overflow-x-visible snap-x snap-mandatory md:snap-none gap-6 pb-4 md:pb-0 no-scrollbar md:grid-cols-3">
				<?php
				$testimonials = [];
				if (function_exists('get_field')) {
					$testimonials = get_field('testimonials', 'options') ?: [];
				}
				if (! empty($testimonials)) :
					foreach ($testimonials as $t) :
						$initials = strtoupper(substr($t['initials'] ?? 'U', 0, 2));
				?>
						<div class="shrink-0 w-[85vw] md:w-auto snap-center bg-slate-50 border border-slate-100 rounded-xl p-6 relative">
							<div class="text-yellow-400 text-lg mb-3">★★★★★</div>
							<p class="text-sm text-slate-600 leading-relaxed mb-4 italic">"<?php echo esc_html($t['content'] ?? ''); ?>"</p>
							<div class="flex items-center gap-3 pt-4 border-t border-slate-200">
								<div class="h-10 w-10 bg-brand-accent text-white rounded-full flex items-center justify-center font-bold text-sm"><?php echo esc_html($initials); ?></div>
								<div>
									<p class="font-bold text-sm text-slate-800"><?php echo esc_html($t['name'] ?? ''); ?></p>
									<p class="text-xs text-slate-400"><?php echo esc_html($t['role'] ?? ''); ?></p>
								</div>
							</div>
						</div>
					<?php
					endforeach;
				else :
					// Fallback hardcoded testimonials when ACF not populated.
					$fallback_testimonials = [
						['name' => 'Nguyễn Hương', 'role' => 'VB2 Công nghệ thông tin', 'initials' => 'NH', 'content' => 'Mình đã học Văn bằng 2 CNTT tại đây. Lịch học trực tuyến rất linh hoạt, giảng viên nhiệt tình và kiến thức thực tế. Sau khi tốt nghiệp mình đã được thăng chức đúng như mong đợi.'],
						['name' => 'Trần Minh', 'role' => 'Đại học Từ xa - Quản trị kinh doanh', 'initials' => 'TM', 'content' => 'Từ xa giúp mình vừa đi làm vừa học đại học. Chương trình đào tạo bài bản, hồ sơ thủ tục nhanh gọn. Mình rất hài lòng với chất lượng giảng dạy.'],
						['name' => 'Lê Phương', 'role' => 'Liên thông Đại học - Kế toán', 'initials' => 'LP', 'content' => 'Liên thông từ Cao đẳng lên Đại học nhanh hơn mình nghĩ. Nhờ tư vấn viên hướng dẫn tận tình mà mình hoàn thành hồ sơ chỉ trong 2 tuần. Bằng cấp được bộ GD&ĐT công nhận.'],
					];
					foreach ($fallback_testimonials as $t) :
					?>
						<div class="shrink-0 w-[85vw] md:w-auto snap-center bg-slate-50 border border-slate-100 rounded-xl p-6 relative">
							<div class="text-yellow-400 text-lg mb-3">★★★★★</div>
							<p class="text-sm text-slate-600 leading-relaxed mb-4 italic">"<?php echo esc_html($t['content']); ?>"</p>
							<div class="flex items-center gap-3 pt-4 border-t border-slate-200">
								<div class="h-10 w-10 bg-brand-accent text-white rounded-full flex items-center justify-center font-bold text-sm"><?php echo esc_html($t['initials']); ?></div>
								<div>
									<p class="font-bold text-sm text-slate-800"><?php echo esc_html($t['name']); ?></p>
									<p class="text-xs text-slate-400"><?php echo esc_html($t['role']); ?></p>
								</div>
							</div>
						</div>
				<?php
					endforeach;
				endif;
				?>
			</div>
		</div>
	</section>



	<!-- 9. DYNAMIC CONSULTATION FORM SECTION -->
	<section id="register-section" class="py-16 bg-white">
		<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 border border-slate-100 shadow-xl rounded-xl p-6 md:p-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center bg-gradient-to-tr from-[#EFF6FF]/35 to-white">
			<!-- Graduate photo column -->
			<div class="lg:col-span-5 hidden lg:block">
				<div class="relative w-full aspect-[4/5] rounded-xl overflow-hidden shadow-md">
					<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('http://localhost:10028/wp-content/uploads/2026/07/banner-contact.png');"></div>
				</div>
			</div>

			<!-- Dynamic contact form column -->
			<div class="lg:col-span-7 space-y-6">
				<div class="text-left space-y-2">
					<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">Đăng ký trực tuyến</span>
					<h3 class="text-2xl md:text-3xl font-black text-slate-900">Nhận tư vấn miễn phí</h3>
					<p class="text-slate-500 text-sm">Vui lòng cung cấp thông tin liên hệ, đội ngũ tuyển sinh của chúng tôi sẽ chủ động gọi lại hỗ trợ giải đáp lộ trình cho bạn sớm nhất.</p>
				</div>

				<?php
				ltdh_render_consultation_form();
				?>
			</div>
		</div>
	</section>

	<!-- 10. NEWS RECENT SECTION -->
	<section class="py-16 bg-slate-50 border-t border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex justify-between items-end mb-12">
				<div class="space-y-2">
					<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">THÔNG TIN TUYỂN SINH</span>
					<h2 class="text-2xl md:text-3xl font-black text-slate-900">Tin tức mới nhất</h2>
				</div>
				<a href="<?php echo esc_url(home_url('/tin-tuc/')); ?>" class="text-sm text-[#00308b] font-bold hover:underline flex items-center gap-1">
					Xem tất cả
					<span>→</span>
				</a>
			</div>

			<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-6">
				<?php
				$mock_news = [
					['title' => 'Tuyển sinh Đại học Từ xa khóa mới nhất', 'date' => '10/07/2026', 'desc' => 'Thông tin chi tiết các ngành đào tạo từ xa hệ Đại học được bộ GD&ĐT công nhận tốt nghiệp chính quy.'],
					['title' => 'Điều kiện học Văn bằng 2 đại học năm 2026', 'date' => '08/07/2026', 'desc' => 'Giải đáp những thắc mắc thường gặp về điều kiện tuyển sinh học văn bằng 2 cho học viên tốt nghiệp các ngành.'],
					['title' => 'Quy chế tuyển sinh Liên thông Cao đẳng lên Đại học', 'date' => '05/07/2026', 'desc' => 'Quy định rút ngắn chương trình đào tạo khi thi liên thông và các hồ sơ chuẩn bị nhập học.'],
					['title' => 'Học đại học vừa học vừa làm có giá trị như thế nào?', 'date' => '02/07/2026', 'desc' => 'Giá trị pháp lý của tấm bằng đại học vừa học vừa làm đối với cơ hội thăng tiến nghề nghiệp.'],
				];

				$news_has_posts = false;
				if ($news_query->have_posts()) {
					$news_has_posts = true;
					$index = 0;
					while ($news_query->have_posts()) : $news_query->the_post();
						$post_id = get_the_ID();
						$thumb_url = get_the_post_thumbnail_url($post_id, 'medium') ?: ltdh_get_fallback_image('news');
						$categories = get_the_category($post_id);
						$category_name = ! empty($categories) ? $categories[0]->name : 'Tin tuyển sinh';
				?>
						<div class="bg-white border border-slate-100 rounded-lg overflow-hidden hover:shadow-md transition-all flex flex-col justify-between">
							<div class="h-36 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url($thumb_url); ?>');"></div>
							<div class="p-4 flex-1 flex flex-col justify-between">
								<div>
									<span class="text-[10px] font-bold text-brand-primary uppercase tracking-wider block mb-1"><?php echo esc_html($category_name); ?></span>
									<h4 class="font-extrabold text-slate-800 text-xs sm:text-sm line-clamp-2 hover:text-[#00308b] transition-colors leading-snug">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h4>
								</div>
								<time class="text-[10px] text-slate-400 mt-3 block"><?php echo get_the_date('d/m/Y'); ?></time>
							</div>
						</div>
					<?php
						$index++;
					endwhile;
					wp_reset_postdata();
				}

				if (! $news_has_posts) {
					foreach ($mock_news as $mn) {
					?>
						<div class="bg-white border border-slate-100 rounded-lg p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
							<div>
								<span class="text-[10px] font-bold text-brand-primary uppercase tracking-wider block mb-1">Hướng dẫn</span>
								<h4 class="font-bold text-slate-800 text-xs sm:text-sm line-clamp-2 leading-snug"><?php echo esc_html($mn['title']); ?></h4>
								<p class="text-[11px] text-slate-400 leading-normal line-clamp-3 mt-2"><?php echo esc_html($mn['desc']); ?></p>
							</div>
							<time class="text-[10px] text-slate-400 mt-3 block"><?php echo esc_html($mn['date']); ?></time>
						</div>
				<?php
					}
				}
				?>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
