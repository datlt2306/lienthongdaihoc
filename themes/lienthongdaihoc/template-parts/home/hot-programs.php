<?php
/**
 * Homepage Hot Programs Section (Apple Cupertino Bento Tiles)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hot_majors = function_exists( 'ltdh_get_hot_majors' ) ? ltdh_get_hot_majors() : [];
$card_colors = [
	['bg' => 'bg-white', 'icon_bg' => 'bg-[#0071E3]/10', 'icon_text' => 'text-[#0071E3]', 'border' => 'border-black/[0.06]', 'hover' => 'hover:border-[#0071E3]/30'],
	['bg' => 'bg-white', 'icon_bg' => 'bg-violet-50',     'icon_text' => 'text-violet-600', 'border' => 'border-black/[0.06]', 'hover' => 'hover:border-violet-300'],
	['bg' => 'bg-white', 'icon_bg' => 'bg-emerald-50',    'icon_text' => 'text-emerald-600', 'border' => 'border-black/[0.06]', 'hover' => 'hover:border-emerald-300'],
	['bg' => 'bg-white', 'icon_bg' => 'bg-amber-50',      'icon_text' => 'text-amber-600',  'border' => 'border-black/[0.06]', 'hover' => 'hover:border-amber-300'],
	['bg' => 'bg-white', 'icon_bg' => 'bg-rose-50',       'icon_text' => 'text-rose-600',   'border' => 'border-black/[0.06]', 'hover' => 'hover:border-rose-300'],
];

$major_icons = [
	'cong-nghe-thong-tin' => '💻',
	'quan-tri-kinh-doanh' => '📈',
	'ke-toan' => '💰',
	'thuong-mai-dien-tu' => '🏪',
	'logistics' => '🚚',
];
?>

<!-- 2. HOT PROGRAMS SECTION (Apple Cupertino Bento Tiles) -->
<section class="py-10 sm:py-14 bg-[#F5F5F7] border-y border-black/[0.06] relative overflow-hidden">
	<div class="max-w-7xl mx-auto relative z-10 px-4 sm:px-6 lg:px-8">
		<div class="text-center max-w-2xl mx-auto mb-6 sm:mb-8 space-y-2">
			<span class="inline-block bg-[#0071E3]/10 text-[#0071E3] text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">KHÁM PHÁ NGÀNH HỌC</span>
			<h2 class="text-2xl md:text-3xl font-semibold tracking-tight text-[#1D1D1F]">5 ngành đào tạo hot nhất</h2>
			<p class="text-[#86868B] text-sm leading-relaxed">Top ngành được quan tâm nhất, đón đầu xu hướng thị trường lao động số.</p>
		</div>

		<!-- Swiper Carousel Container (Mobile swipeable, Desktop 5 columns) -->
		<div class="relative -mx-4 sm:mx-0">
			<div class="swiper hot-majors-swiper overflow-hidden">
				<div class="swiper-wrapper py-2">
					<?php
					if ( ! empty( $hot_majors ) ) :
						$i = 0;
						foreach ( $hot_majors as $maj ) :
							$slug = $maj->post_name;
							$icon = $major_icons[ $slug ] ?? '🎓';
							$c    = $card_colors[ $i % count( $card_colors ) ];
							$i++;
					?>
							<div class="swiper-slide h-auto">
								<a href="<?php echo esc_url( get_permalink( $maj->ID ) ); ?>"
									class="group flex flex-col items-center text-center p-5 sm:p-6 bg-white border border-black/[0.06] rounded-2xl sm:rounded-3xl hover:border-black/[0.12] hover:shadow-[0_12px_32px_rgba(0,0,0,0.06)] hover:-translate-y-1 active:scale-[0.98] transition-all duration-300 h-full">
									<div class="h-12 w-12 <?php echo $c['icon_bg']; ?> <?php echo $c['icon_text']; ?> rounded-2xl flex items-center justify-center mb-3 text-xl group-hover:scale-110 transition-transform">
										<?php echo $icon; ?>
									</div>
									<h3 class="font-semibold text-[#1D1D1F] text-sm sm:text-base leading-snug line-clamp-2"><?php echo esc_html( $maj->post_title ); ?></h3>
									<span class="mt-2 text-xs text-[#0071E3] font-normal inline-flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
										<span>Khám phá</span>
										<span class="transition-transform group-hover:translate-x-0.5">›</span>
									</span>
								</a>
							</div>
					<?php
						endforeach;
					else :
						echo '<p class="text-sm text-slate-400 col-span-5 text-center">Chưa cấu hình ngành hot.</p>';
					endif;
					?>
				</div>
				<!-- Mobile Pagination Dots -->
				<div class="swiper-pagination hot-majors-pagination !relative !bottom-0 mt-4 sm:hidden"></div>
			</div>
		</div>

		<div class="text-center mt-6 sm:mt-8">
			<a href="<?php echo esc_url( home_url( '/nganh-hoc/' ) ); ?>"
				class="apple-link text-[#0071E3] font-normal text-sm hover:underline inline-flex items-center gap-1 group">
				<span>Xem tất cả ngành học</span>
				<span class="transition-transform group-hover:translate-x-0.5">›</span>
			</a>
		</div>
	</div>
</section>
