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
						<a href="<?php echo esc_url($cta_url ? home_url($cta_url) : home_url('/chuong-trinh/')); ?>" class="bg-brand-accent text-white text-center px-8 py-4 rounded-xl font-bold hover:bg-[#e06e00] transition-all flex items-center justify-center gap-2 shadow-md shadow-emerald-600/20">
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
			<form action="<?php echo esc_url(home_url('/chuong-trinh/')); ?>" method="GET" class="space-y-3 md:space-y-0">
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
						<a href="<?php echo esc_url(home_url('/chuong-trinh/')); ?>" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-lg transition-all flex items-center justify-center min-h-[40px] md:min-h-[38px]" title="Reset bộ lọc">
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

	<!-- 4. ELIGIBILITY QUICK CHECK SECTION -->
	<section class="py-12 bg-slate-50 border-t border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
				<div class="lg:col-span-7 space-y-4">
					<?php
					$e_badge = '';
					$e_heading = '';
					$e_desc = '';
					$e_items = [];
					$e_cta_text = '';
					$e_cta_url = '';
					if (function_exists('get_field')) {
						$e_badge = get_field('hp_elig_badge', 'options') ?: '';
						$e_heading = get_field('hp_elig_heading', 'options') ?: '';
						$e_desc = get_field('hp_elig_desc', 'options') ?: '';
						$e_items = get_field('hp_elig_items', 'options') ?: [];
						$e_cta_text = get_field('hp_elig_cta_text', 'options') ?: '';
						$e_cta_url = get_field('hp_elig_cta_url', 'options') ?: '';
					}
					$e_heading = str_replace('\n', '<br>', $e_heading);
					?>
					<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">
						<?php echo esc_html($e_badge ?: 'Điều kiện tuyển sinh'); ?>
					</span>
					<h2 class="text-2xl sm:text-3xl lg:text-4xl font-black font-display text-slate-900 leading-tight">
						<?php echo $e_heading ?: 'Bạn có đủ điều kiện học<br>Liên thông & Đại học từ xa?'; ?>
					</h2>
					<p class="text-slate-500 text-sm leading-relaxed max-w-2xl">
						<?php echo esc_html($e_desc ?: 'Chương trình tuyển sinh mở rộng cho nhiều đối tượng. Chỉ mất 1 phút để kiểm tra tự động.'); ?>
					</p>
					<?php
					$e_items_default = [
						['title' => 'Người đi làm', 'desc' => 'Học trực tuyến linh hoạt'],
						['title' => 'Đã tốt nghiệp TC/CĐ', 'desc' => 'Liên thông miễn giảm tín'],
						['title' => 'Học sinh tốt nghiệp THPT', 'desc' => 'Xét học bạ tuyển thẳng'],
					];
					$e_items_render = !empty($e_items) ? $e_items : $e_items_default;
					?>
					<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
						<?php foreach ($e_items_render as $item) : ?>
							<div class="bg-white p-3 rounded-lg border border-slate-200">
								<span class="block text-sm font-bold text-slate-800 mb-0.5"><?php echo esc_html($item['title'] ?? ''); ?></span>
								<span class="text-xs text-slate-500"><?php echo esc_html($item['desc'] ?? ''); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="flex flex-wrap gap-3 pt-4">
						<a href="<?php echo esc_url($e_cta_url ? home_url($e_cta_url) : home_url('/kiem-tra-dieu-kien/')); ?>" class="bg-brand-accent text-white px-6 py-3 rounded-lg font-bold text-sm hover:bg-[#e06e00] transition-all shadow-md shadow-brand-primary/10">
							<?php echo esc_html($e_cta_text ?: 'Bắt đầu kiểm tra ngay ➔'); ?>
						</a>
						<a href="#register-section" class="bg-white border border-slate-200 text-slate-700 px-6 py-3 rounded-lg font-bold text-sm hover:bg-slate-50 transition-all">
							Tư vấn tiêu chuẩn tuyển sinh
						</a>
					</div>
				</div>
				<div class="lg:col-span-5 flex justify-center">
					<div class="relative w-full max-w-sm aspect-video sm:aspect-square bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
						<div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100/50 space-y-2">
							<?php foreach ($e_items_render as $item) : ?>
								<div class="flex items-center gap-2">
									<span class="text-green-500 text-lg">✔</span>
									<span class="text-xs font-bold text-slate-700"><?php echo esc_html($item['title'] ?? ''); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
						<div class="text-center pt-2">
							<span class="text-[11px] text-slate-400 block font-medium">Bằng đại học được Bộ GD&ĐT cấp phép và công nhận</span>
						</div>
					</div>
				</div>
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
	if (function_exists('get_field')) {
		$c_left_badge = get_field('hp_cert_left_badge', 'options') ?: '';
		$c_left_heading = get_field('hp_cert_left_heading', 'options') ?: '';
		$c_left_items = get_field('hp_cert_left_items', 'options') ?: '';
		$c_left_cta = get_field('hp_cert_left_cta', 'options') ?: '';
		$c_right_badge = get_field('hp_cert_right_badge', 'options') ?: '';
		$c_right_heading = get_field('hp_cert_right_heading', 'options') ?: '';
		$c_right_items = get_field('hp_cert_right_items', 'options') ?: '';
		$c_right_cta = get_field('hp_cert_right_cta', 'options') ?: '';
	}
	?>
	<section class="py-12 bg-white">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
			<div class="bg-slate-50 border border-slate-100 rounded-lg p-6 md:p-8 flex flex-col md:flex-row justify-between gap-6">
				<div class="flex-1 space-y-4">
					<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider"><?php echo esc_html($c_left_badge ?: 'BẰNG CẤP TƯƠNG ĐƯƠNG'); ?></span>
					<h3 class="font-extrabold text-xl text-slate-900 leading-tight"><?php echo esc_html($c_left_heading ?: 'BẰNG ĐẠO TẠO CHÍNH QUY'); ?></h3>
					<div class="space-y-2 text-sm text-slate-600">
						<?php echo nl2br(esc_html($c_left_items ?: "✔ Học chương trình chuẩn theo quy định của bộ GD&ĐT.\n✔ Đảm bảo giá trị pháp lý, sử dụng toàn quốc.\n✔ Phục vụ học tập, thi công chức, nâng lương nâng bậc.\n✔ Hồ sơ nhanh gọn - Quy trình rõ ràng.")); ?>
					</div>
				</div>
				<div class="w-full md:w-36 shrink-0 aspect-[3/4] bg-white border border-slate-100 rounded-lg p-2 flex items-center justify-center shadow-inner">
					<div class="text-center leading-none">
						<span class="text-4xl block mb-2">📜</span>
						<span class="text-sm text-slate-400 block uppercase font-bold">BẰNG ĐẠI HỌC</span>
					</div>
				</div>
			</div>
			<div class="bg-slate-50 border border-slate-100 rounded-lg p-6 md:p-8 flex flex-col md:flex-row justify-between gap-6">
				<div class="flex-1 space-y-4">
					<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider"><?php echo esc_html($c_right_badge ?: 'BẰNG CÓ GIÁ TRỊ SỬ DỤNG'); ?></span>
					<h3 class="font-extrabold text-xl text-slate-900 leading-tight"><?php echo esc_html($c_right_heading ?: 'SUỐT ĐỜI TRÊN TOÀN QUỐC'); ?></h3>
					<div class="space-y-2 text-sm text-slate-600">
						<?php echo nl2br(esc_html($c_right_items ?: "✔ Bằng đại học sử dụng lâu dài, không giới hạn thời gian.\n✔ Cơ hội tiếp tục học lên cao học, thạc sĩ, tiến sĩ.\n✔ Hỗ trợ kết nối việc làm sau khi hoàn thành khóa học.")); ?>
					</div>
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

	<!-- 8. SEGMENTS SECTION -->
	<?php
	$s_heading = '';
	$s_desc = '';
	$s_items = [];
	if (function_exists('get_field')) {
		$s_heading = get_field('hp_seg_heading', 'options') ?: '';
		$s_desc = get_field('hp_seg_desc', 'options') ?: '';
		$s_items = get_field('hp_seg_items', 'options') ?: [];
	}
	$s_items_default = [
		['image' => ltdh_get_fallback_image('segment_1'), 'title' => 'Cho người đi làm', 'desc' => 'Lịch học linh hoạt, học trực tuyến 100% không làm gián đoạn công việc bận rộn hàng ngày.'],
		['image' => ltdh_get_fallback_image('segment_2'), 'title' => 'Cho học sinh lớp 12', 'desc' => 'Định hướng nghề nghiệp sớm, đăng ký lộ trình học liên kết đại học - mở ra tương lai rộng mở.'],
		['image' => ltdh_get_fallback_image('segment_3'), 'title' => 'Cho người muốn liên thông', 'desc' => 'Từ Trung cấp/Cao đẳng lên Đại học. Rút ngắn thời gian đào tạo và tối ưu hóa chi phí học tập.'],
		['image' => ltdh_get_fallback_image('segment_4'), 'title' => 'Cho người muốn chuyển ngành', 'desc' => 'Đón đầu xu hướng chuyển dịch lao động sang các ngành công nghệ, dịch vụ hot nhất hiện nay.'],
		['image' => ltdh_get_fallback_image('segment_5'), 'title' => 'Cho người học văn bằng 2', 'desc' => 'Mở rộng kiến thức đa lĩnh vực, gấp đôi cơ hội ứng tuyển và nâng cao giá trị thương hiệu cá nhân.'],
	];
	$s_items_render = !empty($s_items) ? $s_items : $s_items_default;
	?>
	<section class="py-16 bg-slate-50 border-y border-slate-100">
		<div class="max-w-7xl mx-auto relative">
			<div class="text-center max-w-2xl mx-auto mb-12 px-4">
				<h2 class="text-xl md:text-3xl font-black text-slate-900 mb-2"><?php echo esc_html($s_heading ?: 'Chương trình phù hợp với bạn?'); ?></h2>
				<p class="text-slate-500 text-sm"><?php echo esc_html($s_desc ?: 'Chúng tôi thiết kế các lộ trình học tối ưu riêng cho từng nhóm đối tượng cụ thể.'); ?></p>
			</div>
			<div class="flex md:grid md:grid-cols-5 gap-4 overflow-x-auto snap-x snap-mandatory px-4 pb-2 md:px-0 md:pb-0 scrollbar-hide" style="-webkit-overflow-scrolling: touch;">
				<?php foreach ($s_items_render as $i => $seg) : ?>
					<div class="bg-white border border-slate-100 rounded-lg p-5 hover:border-brand-primary transition-all flex flex-col justify-between snap-start shrink-0 w-[200px] md:w-auto">
						<div class="space-y-2">
							<div class="h-32 bg-slate-100 rounded-lg overflow-hidden bg-cover bg-center mb-3" style="background-image: url('<?php echo esc_url($seg['image'] ?? ltdh_get_fallback_image('segment_' . ($i + 1))); ?>');"></div>
							<h4 class="font-bold text-slate-800 text-xs"><?php echo esc_html($seg['title'] ?? ''); ?></h4>
							<p class="text-[11px] text-slate-500 leading-normal"><?php echo esc_html($seg['desc'] ?? ''); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
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
