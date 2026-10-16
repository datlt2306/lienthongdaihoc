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
	<!-- H1 Semantic Heading for SEO & Screen Readers -->
	<h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hệ Chính Quy, Vừa Học Vừa Làm & Đào Tạo Từ Xa</h1>

	<!-- 1. HERO SECTION (Swiper Banner Slider) -->
	<?php
	$hero_slides = [];
	if (function_exists('get_field')) {
		$hero_slides = get_field('hero_slides', 'options') ?: [];
	}
	// Fallback if empty
	if (empty($hero_slides)) {
		$hero_slides = [
			[
				'image' => ltdh_get_fallback_image('hero'),
				'link'  => '',
			]
		];
	}
	?>
	<section class="relative w-full overflow-hidden bg-slate-50 border-b border-slate-100">
		<div class="swiper hero-swiper w-full min-h-[220px] sm:min-h-[350px] md:min-h-[450px]">
			<div class="swiper-wrapper">
				<?php 
				$slide_idx = 0;
				foreach ($hero_slides as $slide) : 
					if (empty($slide['image'])) continue;
					$slide_img_desktop = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide['image'] ) : $slide['image'];
					$slide_img_mobile  = ! empty( $slide['image_mobile'] ) ? ( function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide['image_mobile'], true ) : $slide['image_mobile'] ) : ( function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide['image'], true ) : '' );
					$is_lcp            = ( 0 === $slide_idx );
					$loading_attr      = $is_lcp ? 'loading="eager" fetchpriority="high"' : 'loading="lazy" fetchpriority="low"';
					?>

					<div class="swiper-slide w-full">
						<?php if (!empty($slide['link'])) : ?>
							<a href="<?php echo esc_url($slide['link']); ?>" class="block w-full h-full">
						<?php endif; ?>
						
						<div class="relative w-full h-auto min-h-[220px] sm:min-h-[350px] md:min-h-[450px] max-h-[800px] overflow-hidden bg-[#f8fafc]">
							<!-- Main Banner Image -->
							<picture class="relative z-10 block w-full h-auto max-h-[800px]">
								<?php if ( ! empty( $slide_img_mobile ) && $slide_img_mobile !== $slide_img_desktop ) : ?>
									<source media="(max-width: 768px)" srcset="<?php echo esc_url( $slide_img_mobile ); ?>">
								<?php endif; ?>
								<img src="<?php echo esc_url( $slide_img_desktop ); ?>" width="1942" height="809" alt="Banner Hero" class="w-full h-auto max-h-[800px] object-cover object-center pointer-events-none" <?php echo $loading_attr; ?> decoding="async">
							</picture>
						</div>


						<?php if (!empty($slide['link'])) : ?>
							</a>
						<?php endif; ?>
					</div>
				<?php 
					$slide_idx++;
				endforeach; 
				?>
			</div>
			<!-- Add Pagination -->
			<div class="swiper-pagination"></div>
			<!-- Add Navigation Arrows -->
			<div class="swiper-button-next !text-white after:!text-base bg-black/30 hover:bg-black/50 !w-8 md:!w-10 !h-8 md:!h-10 rounded-full transition-all flex items-center justify-center"></div>
			<div class="swiper-button-prev !text-white after:!text-base bg-black/30 hover:bg-black/50 !w-8 md:!w-10 !h-8 md:!h-10 rounded-full transition-all flex items-center justify-center"></div>
		</div>
	</section>


	<!-- Styling specifically for pagination position and arrows -->
	<style>
		.hero-swiper .swiper-pagination-bullet-active {
			background: #f5bf23 !important;
			width: 24px;
			border-radius: 4px;
		}
		.hero-swiper .swiper-pagination-bullet {
			transition: all 0.3s ease;
		}
		.hero-swiper .swiper-button-next, .hero-swiper .swiper-button-prev {
			opacity: 0;
			margin-top: -16px;
			transition: all 0.3s ease;
		}
		.hero-swiper:hover .swiper-button-next, .hero-swiper:hover .swiper-button-prev {
			opacity: 1;
		}
		.hero-swiper .swiper-button-next {
			right: 20px;
		}
		.hero-swiper .swiper-button-prev {
			left: 20px;
		}
	</style>


	<!-- Search & Filters Container (Compact Single-Row Bar) -->
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-30 hidden">
		<div class="bg-white rounded-xl shadow-xl border border-slate-100 p-4 md:p-5">
			<form action="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" method="GET" class="space-y-3 md:space-y-0">
				<!-- Keyword (always visible) -->
				<div class="flex-grow min-w-[20%]">
					<input type="text" name="s" placeholder="Từ khóa tìm kiếm..." class="w-full border border-slate-200 rounded-lg px-3 py-2.5 md:py-2 text-sm focus:border-brand-primary focus:outline-none placeholder-slate-400 font-medium min-h-[40px] md:min-h-[38px]" />
				</div>

				<?php
				$filter_opts = ltdh_get_cached_filter_options();
				$schools     = $filter_opts['schools'] ?? [];
				$majors      = $filter_opts['majors'] ?? [];
				$types       = $filter_opts['types'] ?? [];
				?>

				<!-- Filters: stacked on mobile, inline on desktop -->
				<div class="flex flex-col md:flex-row md:items-center md:gap-3 gap-2">
					<div class="flex-1">
						<select name="truong" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 md:py-2 text-sm focus:border-brand-primary focus:outline-none bg-white md:bg-transparent min-h-[40px] md:min-h-[38px]">
							<option value="">-- Chọn trường --</option>
							<?php foreach ($schools as $sc) : ?>
								<option value="<?php echo esc_attr($sc['slug']); ?>"><?php echo esc_html($sc['title']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="flex-1">
						<select name="nganh" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 md:py-2 text-sm focus:border-brand-primary focus:outline-none bg-white md:bg-transparent min-h-[40px] md:min-h-[38px]">
							<option value="">-- Chọn ngành học --</option>
							<?php foreach ($majors as $mj) : ?>
								<option value="<?php echo esc_attr($mj['slug']); ?>"><?php echo esc_html($mj['title']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="flex-1">
						<select name="he" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 md:py-2 text-sm focus:border-brand-primary focus:outline-none bg-white md:bg-transparent min-h-[40px] md:min-h-[38px]">
							<option value="">-- Chọn hình thức đào tạo --</option>
							<?php foreach ($types as $tp) : ?>
								<option value="<?php echo esc_attr($tp['slug']); ?>"><?php echo esc_html($tp['name']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="flex gap-2 md:shrink-0">
						<button type="submit" class="flex-1 md:flex-none bg-brand-primary hover:bg-brand-darkBlue text-white font-extrabold text-sm px-5 py-2.5 rounded-lg transition-all uppercase tracking-wider min-h-[40px] md:min-h-[38px]">
							Tìm kiếm
						</button>
						<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-lg transition-all flex items-center justify-center min-h-[40px] md:min-h-[38px]" title="Reset bộ lọc">
							🔄
						</a>
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- 2. HOT PROGRAMS SECTION -->
	<section class="py-12 bg-slate-50 border-y border-slate-100 relative overflow-hidden">
		<div class="max-w-7xl mx-auto relative z-10 sm:px-6 lg:px-8">
			<div class="text-center max-w-2xl mx-auto mb-8 space-y-2 px-4">
				<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">KHÁM PHÁ NGÀNH HỌC</span>
				<h2 class="text-xl md:text-4xl font-black text-slate-900">5 ngành đào tạo hot nhất</h2>
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
							<h3 class="font-semibold text-slate-700 leading-snug line-clamp-2"><?php echo esc_html($maj->post_title); ?></h3>
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

	<!-- 3. NATIONAL SCHOOLS SECTION (CAROUSEL SLIDER) -->
	<section id="program-section" class="py-12 md:py-16 bg-white overflow-hidden">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8">
				<div class="space-y-2">
					<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">ĐỐI TÁC ĐẠI HỌC</span>
					<h2 class="text-xl md:text-3xl font-black text-slate-900">Trường đối tác đào tạo</h2>
				</div>
				<div class="flex items-center gap-3 mt-4 sm:mt-0">
					<a href="<?php echo esc_url(home_url('/truong-doi-tac/')); ?>" class="text-sm text-brand-primary font-bold hover:underline flex items-center gap-1">
						Xem tất cả
						<span>→</span>
					</a>
					<div class="hidden sm:flex items-center gap-2 pl-2 border-l border-slate-200">
						<button type="button" class="school-slider-prev w-9 h-9 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 flex items-center justify-center transition-all hover:scale-105 active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed shadow-xs" aria-label="Trường trước đó">
							<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
								<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
							</svg>
						</button>
						<button type="button" class="school-slider-next w-9 h-9 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 flex items-center justify-center transition-all hover:scale-105 active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed shadow-xs" aria-label="Trường tiếp theo">
							<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
								<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
							</svg>
						</button>
					</div>
				</div>
			</div>

			<!-- Swiper Carousel Container -->
			<div class="relative -mx-4 px-4 sm:mx-0 sm:px-0">
				<div class="swiper school-partner-swiper !overflow-visible sm:!overflow-hidden">
					<div class="swiper-wrapper py-3">
						<?php
						if ( ! empty( $featured_schools ) ) {
							foreach ( $featured_schools as $school ) :
								$school_id       = $school['id'];
								$address         = $school['address'];
								$hotline         = $school['hotline'];
								$thumb_url       = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'medium' ) : $school['thumb_url'];
								$logo_id         = $school['logo_id'];
								$school_code     = ! empty( $school['code'] ) ? $school['code'] : ( get_post_meta( $school_id, 'school_code', true ) ?: '' );
								$en_name         = ! empty( $school['en_name'] ) ? $school['en_name'] : get_post_meta( $school_id, 'english_name', true );
								if ( ( empty( $en_name ) || 'University' === $en_name ) && ! empty( $school_code ) ) {
									$en_name = $school_code . ' University';
								}
								$school_types    = ! empty( $school['school_types'] ) ? $school['school_types'] : ltdh_get_school_training_types( $school_id );
								$school_location = ! empty( $school['location'] ) ? $school['location'] : ltdh_get_school_location_label( $school_id );
								$school_region   = ! empty( $school['region'] ) ? $school['region'] : ltdh_get_school_region_label( $school_id );

								// Clean short address for footer info row
								$short_address = $school_location;
								if ( ! empty( $address ) && 'Việt Nam' !== $address ) {
									$addr_parts = array_map( 'trim', explode( ',', $address ) );
									if ( count( $addr_parts ) >= 2 ) {
										$short_address = implode( ', ', array_slice( $addr_parts, -2 ) );
									} else {
										$short_address = $address;
									}
								}
						?>
								<div class="swiper-slide h-auto flex">
									<div class="w-full bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
										<!-- Ảnh trường & Vị trí -->
										<div class="relative h-36 sm:h-40 bg-slate-200 bg-cover bg-center overflow-hidden" style="background-image: url('<?php echo esc_url($thumb_url); ?>');">
											<div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-slate-900/10 to-transparent"></div>
											<?php if ( ! empty( $school_region ) ) : ?>
												<span class="absolute top-3 right-3 bg-slate-900/60 backdrop-blur-md text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full z-10 flex items-center gap-1 border border-white/20 shadow-xs">
													<svg class="w-3 h-3 text-white/80 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
														<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
														<path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
													</svg>
													<span><?php echo esc_html( $school_region ); ?></span>
												</span>
											<?php endif; ?>
										</div>

										<!-- Logo trường -->
										<div class="h-14 w-14 sm:h-16 sm:w-16 bg-white rounded-2xl border-2 border-white shadow-[0_4px_16px_rgba(0,0,0,0.12)] -mt-7 sm:-mt-8 ml-4 sm:ml-5 z-10 relative flex items-center justify-center overflow-hidden p-1.5 transition-transform duration-300 group-hover:scale-105">
											<?php if ($logo_id) : 
												$logo_alt = get_post_meta( $logo_id, '_wp_attachment_image_alt', true ) ?: ( 'Logo ' . ( $school['title'] ?? '' ) );
											?>
												<?php echo wp_get_attachment_image($logo_id, 'thumbnail', false, ['class' => 'h-full w-full object-contain', 'alt' => $logo_alt]); ?>
											<?php else : ?>
												<span class="font-display font-extrabold text-[#00308b] text-sm">UNI</span>
											<?php endif; ?>
										</div>

										<!-- Nội dung thẻ -->
										<div class="p-4 sm:p-5 pt-2 flex-1 flex flex-col justify-between text-left">
											<div>
												<!-- Mã trường & Địa điểm -->
												<div class="flex items-center gap-2 mb-1.5 min-h-[20px]">
													<?php if ( ! empty( $school_code ) ) : ?>
														<span class="font-bold text-[#00308b] bg-blue-50 px-2 py-0.5 rounded text-[11px] border border-blue-100">Mã: <?php echo esc_html( $school_code ); ?></span>
													<?php endif; ?>
													<?php if ( ! empty( $school_location ) ) : ?>
														<span class="text-xs text-slate-500 font-medium truncate"><?php echo esc_html( $school_location ); ?></span>
													<?php endif; ?>
												</div>

												<!-- Tên trường -->
												<h3 class="font-extrabold text-slate-900 text-sm md:text-[15px] tracking-tight leading-snug line-clamp-2 mt-0.5 group-hover:text-[#00308b] transition-colors">
													<a href="<?php echo esc_url($school['permalink']); ?>">
														<?php echo esc_html($school['title']); ?>
													</a>
												</h3>

												<!-- Tên tiếng Anh -->
												<?php if ( ! empty( $en_name ) ) : ?>
													<p class="text-xs text-slate-400 font-medium line-clamp-1 italic mt-1" title="<?php echo esc_attr( $en_name ); ?>">
														<?php echo esc_html( $en_name ); ?>
													</p>
												<?php endif; ?>

												<!-- Địa chỉ trường -->
												<?php
												$display_address = ! empty( $address ) && 'Việt Nam' !== $address ? $address : ( ! empty( $school_location ) ? 'Cơ sở: ' . $school_location : '' );
												if ( ! empty( $display_address ) ) :
												?>
													<div class="mt-3 pt-2.5 border-t border-slate-100 flex items-start gap-1.5 text-xs text-slate-500 min-h-[36px]">
														<svg class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
															<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
															<path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
														</svg>
														<span class="line-clamp-2 leading-relaxed" title="<?php echo esc_attr( $display_address ); ?>">
															<?php echo esc_html( $display_address ); ?>
														</span>
													</div>
												<?php endif; ?>
											</div>

											<!-- Nút Tìm hiểu chi tiết -->
											<div class="mt-3.5 pt-3 border-t border-slate-100">
												<a href="<?php echo esc_url($school['permalink']); ?>" 
												   class="w-full text-center py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold text-[#00308b] bg-blue-50/90 hover:bg-[#00308b] hover:text-white border border-blue-200/80 hover:border-[#00308b] transition-all duration-200 flex items-center justify-center gap-1.5 shadow-2xs group-hover:shadow-sm">
													<span>Tìm hiểu chi tiết</span>
													<svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
														<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
													</svg>
												</a>
											</div>
										</div>
									</div>
								</div>
						<?php
							endforeach;
						} else {
							echo '<div class="w-full text-center text-slate-500 py-6">Chưa có trường đối tác nào được gieo dữ liệu.</div>';
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<style>
	.school-partner-swiper:not(.swiper-initialized) .swiper-wrapper {
		display: flex;
		overflow-x: auto;
		scroll-snap-type: x mandatory;
		gap: 1.25rem;
		scrollbar-width: none;
		-ms-overflow-style: none;
	}
	.school-partner-swiper:not(.swiper-initialized) .swiper-wrapper::-webkit-scrollbar {
		display: none;
	}
	.school-partner-swiper:not(.swiper-initialized) .swiper-slide {
		flex: 0 0 280px;
		scroll-snap-align: start;
	}
	@media (min-width: 1280px) {
		.school-partner-swiper:not(.swiper-initialized) .swiper-slide {
			flex: 0 0 calc((100% - 3 * 1.5rem) / 4);
		}
	}
	.school-slider-prev.swiper-button-disabled,
	.school-slider-next.swiper-button-disabled {
		opacity: 0.35;
		pointer-events: none;
		cursor: not-allowed;
	}
	</style>

	<!-- 4. ELIGIBILITY QUICK CHECK SECTION -->
	<section class="py-16 bg-slate-50 border-t border-slate-100">
		<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
			<div class="space-y-4">
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
					$e_cta_raw  = get_field('hp_elig_cta_url', 'options') ?: '/kiem-tra-dieu-kien/';
				} else {
					$e_cta_raw = '/kiem-tra-dieu-kien/';
				}
				$e_cta_url = ( preg_match( '#^(https?:)?//#i', $e_cta_raw ) ) ? $e_cta_raw : home_url( $e_cta_raw );
				$e_heading = str_replace('\n', '<br>', $e_heading);
				?>
				<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">
					<?php echo esc_html($e_badge ?: 'Điều kiện tuyển sinh'); ?>
				</span>
				<h2 class="text-2xl sm:text-3xl lg:text-4xl font-black font-display text-slate-900 leading-tight">
					<?php echo $e_heading ?: 'Bạn có đủ điều kiện học<br>Liên thông Đại học?'; ?>
				</h2>
				<p class="text-slate-500 text-sm leading-relaxed max-w-2xl mx-auto">
					<?php echo esc_html($e_desc ?: 'Chương trình tuyển sinh mở rộng cho người tốt nghiệp Trung cấp hoặc Cao đẳng. Chỉ mất 1 phút để kiểm tra tự động.'); ?>
				</p>
				<?php
				$e_items_default = [
					['title' => 'Tốt nghiệp Trung cấp', 'desc' => 'Liên thông lên Đại học'],
					['title' => 'Tốt nghiệp Cao đẳng', 'desc' => 'Liên thông miễn giảm tín chỉ'],
					['title' => 'Khác ngành / Ngành gần', 'desc' => 'Học bổ sung kiến thức liên thông'],
				];
				$e_items_render = !empty($e_items) ? $e_items : $e_items_default;
				?>
				<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4 max-w-3xl mx-auto text-left">
					<?php foreach ($e_items_render as $item) : ?>
						<div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
							<span class="block text-sm font-bold text-slate-800 mb-0.5"><?php echo esc_html($item['title'] ?? ''); ?></span>
							<span class="text-sm text-slate-500"><?php echo esc_html($item['desc'] ?? ''); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="flex flex-wrap justify-center gap-3 pt-6">
					<a href="<?php echo esc_url($e_cta_url); ?>" class="bg-brand-accent text-white px-6 py-3.5 rounded-lg font-bold text-sm hover:bg-[#e06e00] transition-all shadow-md shadow-brand-primary/10">
						<?php echo esc_html($e_cta_text ?: 'Bắt đầu kiểm tra ngay ➔'); ?>
					</a>
					<a href="#register-section" class="bg-white border border-slate-200 text-slate-700 px-6 py-3.5 rounded-lg font-bold text-sm hover:bg-slate-50 transition-all">
						Tư vấn tiêu chuẩn tuyển sinh
					</a>
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
									<h4 class="font-bold text-slate-900 text-base">Bằng Cử nhân / Kỹ sư chính thức</h4>
									<p class="text-slate-500 text-sm leading-relaxed">Sau khi hoàn thành chương trình, học viên sẽ được trường Đại học cấp bằng Cử nhân/Kỹ sư theo quy định của Bộ GD&ĐT, có giá trị pháp lý trên toàn quốc.</p>
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
						.cert-swiper {
							width: 100%;
							height: 100%;
						}
						.cert-swiper .swiper-slide img {
							width: 100%;
							height: 100%;
							object-fit: contain;
							background-color: #ffffff;
						}
						.cert-swiper .swiper-pagination-bullet {
							background: rgba(0, 48, 139, 0.3) !important;
							opacity: 1 !important;
							width: 10px;
							height: 10px;
							transition: all 0.3s ease;
						}
						.cert-swiper .swiper-pagination-bullet-active {
							background: #0284c7 !important;
							transform: scale(1.2);
						}
					</style>
					<div class="cert-slider-wrapper">
						<!-- Slider Container -->
						<div class="swiper cert-swiper">
							<div class="swiper-wrapper">
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
									$opt_slide_url = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide_url ) : $slide_url;
								?>
									<div class="swiper-slide w-full h-full">
										<img src="<?php echo esc_url($opt_slide_url); ?>" alt="Students Graduation Slide" loading="lazy" decoding="async" class="w-full h-full object-cover">
									</div>
								<?php endforeach; ?>
							</div>

							<!-- Swiper Pagination -->
							<?php if ( count($slides) > 1 ) : ?>
								<div class="swiper-pagination !bottom-6 !right-6 !left-auto !w-auto flex gap-2"></div>
							<?php endif; ?>
						</div>

						<!-- Orange badge -->
						<div class="absolute bottom-6 left-6 bg-[#f97316] text-white p-5 rounded-2xl shadow-xl flex flex-col justify-center max-w-[160px] z-20 hover:scale-105 transition-transform duration-300 pointer-events-none">
							<span class="text-3xl font-black leading-none">100%</span>
							<span class="text-xs font-extrabold tracking-wider uppercase mt-2 leading-tight">VĂN BẰNG CHUẨN<br>BỘ GD&ĐT</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Row 2: [Video Card] [Text Content] -->
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
				<!-- Left column: Video Card -->
				<div class="rounded-2xl relative overflow-hidden rounded-3xl shadow-lg aspect-video group bg-slate-800 flex items-center justify-center <?php echo !empty($c_left_youtube) ? 'cursor-pointer' : ''; ?>" <?php echo !empty($c_left_youtube) ? 'data-youtube="' . esc_url($c_left_youtube) . '"' : ''; ?>>
					<?php
					$video_raw_thumb = $c_left_image ?: home_url('wp-content/uploads/2026/07/vtv-news-thumb.png');
					$video_opt_thumb = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $video_raw_thumb ) : $video_raw_thumb;
					?>
					<img src="<?php echo esc_url($video_opt_thumb); ?>" alt="VTV24 Video Thumbnail" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
					
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

			// Certificate Swiper Slider Logic (Lazy Hydration)
			const initCertSwiper = () => {
				if (typeof Swiper !== 'undefined' && document.querySelector('.cert-swiper')) {
					new Swiper('.cert-swiper', {
						loop: true,
						autoplay: {
							delay: 4000,
							disableOnInteraction: false,
						},
						pagination: {
							el: '.cert-swiper .swiper-pagination',
							clickable: true,
						},
						speed: 600,
					});
				}
			};

			if ('requestIdleCallback' in window) {
				requestIdleCallback(initCertSwiper, { timeout: 3000 });
			} else {
				setTimeout(initCertSwiper, 1000);
			}
		});
	</script>

	<!-- 6. EXPERIENCE & ACCURATE STATISTICS -->
	<?php
	$w_heading = '';
	$w_desc    = '';
	if ( function_exists( 'get_field' ) ) {
		$w_heading = get_field( 'hp_whyus_heading', 'options' ) ?: '';
		$w_desc    = get_field( 'hp_whyus_desc', 'options' ) ?: '';
	}
	if ( empty( $w_heading ) ) {
		$w_heading = 'Kinh nghiệm & Thực tế';
	}
	if ( empty( $w_desc ) || str_contains( $w_desc, 'hàng đầu' ) ) {
		$w_desc = 'Hỗ trợ người học tiếp cận thông tin tuyển sinh chính xác và chuẩn bị hồ sơ xét tuyển.';
	}

	$kpi_metrics = [];
	if ( function_exists( 'get_field' ) ) {
		$kpi_metrics = get_field( 'kpi_metrics', 'options' ) ?: [];
	}
	if ( empty( $kpi_metrics ) ) {
		$kpi_metrics = ltdh_default( 'homepage', 'kpi_metrics' );
	}

	// Sanitize and filter out legacy unverified claims (98%, 30+ đối tác, 1.200+ học viên, 10+ năm)
	$sanitized_metrics = [];
	if ( is_array( $kpi_metrics ) ) {
		foreach ( $kpi_metrics as $m ) {
			$val = trim( (string) ( $m['value'] ?? '' ) );
			$lbl = trim( (string) ( $m['label'] ?? '' ) );

			// Strip unverified satisfaction percentages and unverified partner counts
			if ( str_contains( $val, '98%' ) || str_contains( mb_strtolower( $lbl, 'UTF-8' ), 'hài lòng' ) ) {
				continue;
			}
			if ( ( str_contains( $val, '30+' ) || str_contains( $val, '30' ) ) && str_contains( mb_strtolower( $lbl, 'UTF-8' ), 'đối tác' ) ) {
				continue;
			}
			// Replace legacy 200+ / 1.200+ student enrollment claims with verified 500+ registration support
			if ( str_contains( $val, '1.200' ) || str_contains( $val, '200+' ) || str_contains( $val, '1200' ) ) {
				$sanitized_metrics[] = [
					'value'   => '500+',
					'label'   => 'Người học được hỗ trợ đăng ký liên thông',
					'subtext' => 'Trong 4 năm qua',
				];
				continue;
			}
			// Replace legacy unverified 10+ years with actual 4 years experience
			if ( str_contains( $val, '10+' ) || str_contains( $val, '10' ) ) {
				$sanitized_metrics[] = [
					'value'   => '4 năm',
					'label'   => 'Kinh nghiệm tư vấn tuyển sinh liên thông',
					'subtext' => 'Tư vấn lựa chọn chương trình và hỗ trợ hồ sơ',
				];
				continue;
			}
			$sanitized_metrics[] = $m;
		}
	}

	if ( empty( $sanitized_metrics ) ) {
		$sanitized_metrics = ltdh_default( 'homepage', 'kpi_metrics' );
	}
	?>
	<section class="py-10 md:py-12 bg-gradient-to-br from-[#0c192c] via-[#0E2038] to-[#002b7a] text-white relative overflow-hidden">
		<div class="absolute -right-20 -top-20 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
		<div class="absolute -left-20 -bottom-20 w-80 h-80 bg-[#f5bf23]/10 rounded-full blur-3xl pointer-events-none"></div>
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
			<div class="flex flex-col lg:flex-row items-center justify-between gap-6 lg:gap-12">
				<div class="w-full lg:w-1/3 text-center lg:text-left">
					<div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-xs text-[#f5bf23] text-[11px] font-extrabold uppercase tracking-wider mb-2.5 border border-white/10">
						<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
						<span>Thông tin xác thực</span>
					</div>
					<h3 class="text-xl md:text-2xl font-black font-display tracking-tight text-white uppercase leading-tight">
						<?php echo esc_html( $w_heading ); ?>
					</h3>
					<p class="text-slate-300 text-xs sm:text-sm mt-2 leading-relaxed">
						<?php echo esc_html( $w_desc ); ?>
					</p>
				</div>
				<div class="w-full lg:w-2/3 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 text-center">
					<?php foreach ( $sanitized_metrics as $idx => $metric ) : 
						$is_first = ( 0 === $idx );
					?>
						<div class="group bg-white/[0.07] hover:bg-white/[0.12] border border-white/10 hover:border-white/20 rounded-2xl p-5 sm:p-6 flex flex-col justify-between items-center text-center backdrop-blur-xs transition-all duration-300 shadow-sm hover:shadow-lg">
							<div class="w-10 h-10 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-[#f5bf23] mb-3 group-hover:bg-[#f5bf23]/20 group-hover:border-[#f5bf23]/30 transition-colors">
								<?php if ( $is_first ) : ?>
									<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
										<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
									</svg>
								<?php else : ?>
									<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
										<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
									</svg>
								<?php endif; ?>
							</div>

							<span class="block font-display font-black text-3xl sm:text-4xl lg:text-5xl text-[#f5bf23] leading-none tracking-tight mb-2">
								<?php echo esc_html( $metric['value'] ?? '' ); ?>
							</span>
							<span class="text-sm sm:text-base text-white font-bold block leading-snug">
								<?php echo esc_html( $metric['label'] ?? '' ); ?>
							</span>
							<?php if ( ! empty( $metric['subtext'] ) ) : ?>
								<span class="text-xs text-slate-300 font-medium block mt-1.5 leading-relaxed">
									<?php echo esc_html( $metric['subtext'] ); ?>
								</span>
							<?php endif; ?>
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
				<p class="text-slate-500 text-sm">Chia sẻ thực tế từ người học đã được tư vấn và hỗ trợ chuẩn bị hồ sơ xét tuyển liên thông đại học.</p>
			</div>

			<div class="flex md:grid overflow-x-auto md:overflow-x-visible snap-x snap-mandatory md:snap-none gap-6 pb-4 md:pb-0 no-scrollbar md:grid-cols-3">
				<?php
				$testimonials = [];
				if (function_exists('get_field')) {
					$testimonials = get_field('testimonials', 'options') ?: [];
				}
				if (empty($testimonials)) {
					// 6 học viên tiêu biểu: 4 đã tốt nghiệp + 2 tân sinh viên / đang theo học
					$testimonials = [
						[
							'name'         => 'Hoàng Thị Độ',
							'workplace'    => 'Product Owner',
							'role'         => 'Liên thông CĐ lên ĐH - CNTT',
							'graduation'   => 'Khóa 29.1 - ĐH Giao thông Vận tải',
							'status'       => 'graduated',
							'status_label' => 'Đã tốt nghiệp',
							'initials'     => 'HĐ',
							'image'        => get_template_directory_uri() . '/assets/images/student-do.jpg',
							'content'      => 'Mình liên thông Cao Đẳng lên đại học. Lịch học được sắp xếp ngoài giờ hành chính, tạo điều kiện thuận lợi cho những người đang đi làm như mình có thể vừa hoàn thành công việc, vừa tiếp tục học tập. Bên cạnh đó, thầy cô luôn nhiệt tình hỗ trợ và tạo điều kiện để sinh viên cân bằng giữa công việc và việc học.'
						],
						[
							'name'         => 'Trần Minh',
							'workplace'    => 'Trưởng nhóm Kinh doanh tại Viettel Post',
							'role'         => 'Đại học Từ xa - Quản trị kinh doanh',
							'graduation'   => 'Khóa 2021 - ĐH Kinh tế Quốc dân',
							'status'       => 'graduated',
							'status_label' => 'Đã tốt nghiệp',
							'initials'     => 'TM',
							'image'        => get_template_directory_uri() . '/assets/images/student-minh.jpg',
							'content'      => 'Từ xa giúp mình vừa đi làm vừa học đại học. Chương trình đào tạo bài bản, hồ sơ thủ tục nhanh gọn. Mình rất hài lòng với chất lượng giảng dạy.'
						],
						[
							'name'         => 'Lê Phương',
							'workplace'    => 'Kế toán tổng hợp tại VPBank',
							'role'         => 'Liên thông Đại học - Kế toán',
							'graduation'   => 'Khóa 2022 - ĐH Thương Mại',
							'status'       => 'graduated',
							'status_label' => 'Đã tốt nghiệp',
							'initials'     => 'LP',
							'image'        => get_template_directory_uri() . '/assets/images/student-phuong.jpg',
							'content'      => 'Liên thông từ Cao đẳng lên Đại học nhanh hơn mình nghĩ. Nhờ tư vấn viên hướng dẫn tận tình mà mình hoàn thành hồ sơ chỉ trong 2 tuần. Bằng cấp được bộ GD&ĐT công nhận.'
						],
						[
							'name'         => 'Hoàng Dũng',
							'workplace'    => 'Kỹ sư giám sát tại Coteccons',
							'role'         => 'Liên thông Kỹ thuật Xây dựng',
							'graduation'   => 'Khóa 2021 - ĐH Giao thông Vận tải',
							'status'       => 'graduated',
							'status_label' => 'Đã tốt nghiệp',
							'initials'     => 'HD',
							'image'        => get_template_directory_uri() . '/assets/images/student-dung.jpg',
							'content'      => 'Vừa đi công trình vừa học trực tuyến buổi tối rất tiện lợi. Bài giảng thực tế bám sát công việc hàng ngày. Nhận bằng ĐH chính quy giúp mình đủ điều kiện ký hồ sơ năng lực dự án lớn.'
						],
						[
							'name'         => 'Bùi Đức Anh',
							'workplace'    => 'Chuyên viên Kỹ thuật tại VNPT',
							'role'         => 'Liên thông ĐH Điện tử Viễn thông',
							'graduation'   => 'Khóa 2024 - ĐH Giao thông Vận tải',
							'status'       => 'new',
							'status_label' => 'Tân sinh viên K2024',
							'initials'     => 'DA',
							'image'        => get_template_directory_uri() . '/assets/images/student-ducanh.jpg',
							'content'      => 'Mình vừa hoàn thiện hồ sơ xét tuyển đợt tháng 9 vừa rồi. Tư vấn viên hướng dẫn rất chi tiết, chỉ 3 ngày là xong thủ tục và có giấy báo nhập học. Đang học kỳ 1 online buổi tối thấy rất chủ động.'
						],
						[
							'name'         => 'Đặng Hà My',
							'workplace'    => 'Trợ lý Dự án tại Vingroup',
							'role'         => 'Đại học Từ xa - Ngôn ngữ Anh',
							'graduation'   => 'Khóa 2024 - ĐH Thái Nguyên',
							'status'       => 'studying',
							'status_label' => 'Đang học Kỳ 2',
							'initials'     => 'HM',
							'image'        => get_template_directory_uri() . '/assets/images/student-hamy.jpg',
							'content'      => 'Hệ thống học trực tuyến E-learning rất trực quan, bài giảng video rõ ràng và có thể xem lại bất cứ lúc nào. Giảng viên hỗ trợ giải đáp nhanh nên dù vừa đi làm vừa học vẫn theo kịp tiến độ.'
						],
					];
				}

				foreach ($testimonials as $t) :
					$initials = strtoupper(substr($t['initials'] ?? $t['name'] ?? 'U', 0, 2));
					$avatar_url = '';
					if (!empty($t['image'])) {
						if (is_array($t['image'])) {
							$avatar_url = $t['image']['sizes']['medium'] ?? $t['image']['sizes']['thumbnail'] ?? $t['image']['url'] ?? '';
						} elseif (is_numeric($t['image'])) {
							$avatar_url = wp_get_attachment_image_url($t['image'], 'medium') ?: wp_get_attachment_image_url($t['image'], 'thumbnail');
						} else {
							$avatar_url = $t['image'];
						}
					}
					$workplace    = $t['workplace'] ?? $t['company'] ?? $t['job'] ?? '';
					$graduation   = $t['graduation'] ?? $t['school'] ?? $t['batch'] ?? 'Bằng chuẩn Bộ GD&ĐT';
					$status       = $t['status'] ?? 'graduated';
					$is_studying  = in_array($status, ['studying', 'new', 'enrolled'], true);
					$status_label = $t['status_label'] ?? ($is_studying ? 'Đang theo học' : 'Đã tốt nghiệp');
				?>
					<div class="shrink-0 w-[85vw] sm:w-[360px] md:w-auto snap-center bg-slate-50/80 hover:bg-white border border-slate-200/80 hover:border-blue-300 rounded-2xl p-6 sm:p-7 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
						<div>
							<!-- Student Profile Header with Large Photo (96px) -->
							<div class="flex items-start gap-4 mb-4">
								<div class="relative shrink-0">
									<?php if ($avatar_url) : ?>
										<img class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-2 ring-slate-100 shadow-md group-hover:ring-brand-primary/20 transition-all duration-300" src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($t['name'] ?? ''); ?>" loading="lazy" width="96" height="96">
									<?php else : ?>
										<div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-brand-primary to-blue-700 text-white rounded-2xl flex items-center justify-center font-bold text-xl shadow-md ring-2 ring-slate-100"><?php echo esc_html($initials); ?></div>
									<?php endif; ?>

									<?php if ($is_studying) : ?>
										<span class="absolute -bottom-1 -right-1 bg-blue-600 text-white p-1 rounded-full ring-2 ring-white shadow-xs" title="<?php echo esc_attr($status_label); ?>">
											<svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a10.993 10.993 0 00-.25 2.302c0 1.109.2 2.16.564 3.125a1 1 0 00.916.622h7.04a1 1 0 00.916-.622c.364-.965.564-2.016.564-3.125 0-.796-.09-1.57-.25-2.302l2.644-1.131a1 1 0 000-1.84l-7-3zM10 4.218l4.157 1.782L10 7.782 5.843 6 10 4.218z"/>
											</svg>
										</span>
									<?php else : ?>
										<span class="absolute -bottom-1 -right-1 bg-emerald-500 text-white p-1 rounded-full ring-2 ring-white shadow-xs" title="Học viên đã tốt nghiệp">
											<svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
											</svg>
										</span>
									<?php endif; ?>
								</div>
								<div class="min-w-0 flex-1">
									<div class="flex items-center gap-0.5 text-amber-400 mb-1" aria-label="5 trên 5 sao">
										<svg class="w-4 h-4 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
										<svg class="w-4 h-4 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
										<svg class="w-4 h-4 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
										<svg class="w-4 h-4 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
										<svg class="w-4 h-4 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
										<span class="text-xs font-bold text-amber-600 ml-1">5.0</span>
									</div>
									<h3 class="font-bold text-base sm:text-lg text-slate-900 group-hover:text-brand-primary transition-colors leading-snug truncate"><?php echo esc_html($t['name'] ?? ''); ?></h3>
									<?php if (!empty($workplace)) : ?>
										<p class="text-xs sm:text-[13px] font-semibold text-slate-700 flex items-center gap-1 mt-0.5 truncate" title="<?php echo esc_attr($workplace); ?>">
											<svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
											</svg>
											<span class="truncate"><?php echo esc_html($workplace); ?></span>
										</p>
									<?php endif; ?>
									<p class="text-xs text-brand-primary font-medium mt-0.5 line-clamp-1"><?php echo esc_html($t['role'] ?? ''); ?></p>
								</div>
							</div>

							<!-- Quote Body with decorative quote mark -->
							<div class="relative pt-2">
								<svg class="w-8 h-8 text-blue-100 mb-2 -mt-1" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
								<p class="text-sm sm:text-[15px] text-slate-600 leading-relaxed italic">"<?php echo esc_html($t['content'] ?? ''); ?>"</p>
							</div>
						</div>

						<!-- Card Bottom / Trust signal with Graduation Batch & University (Mục 2) -->
						<div class="mt-5 pt-3.5 border-t border-slate-200/80 flex items-center justify-between text-xs gap-2">
							<?php if ($is_studying) : ?>
								<span class="inline-flex items-center gap-1.5 font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md shrink-0 border border-blue-100/80">
									<span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
									<?php echo esc_html($status_label); ?>
								</span>
							<?php else : ?>
								<span class="inline-flex items-center gap-1.5 font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md shrink-0">
									<svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
										<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
									</svg>
									Đã tốt nghiệp
								</span>
							<?php endif; ?>
							<span class="text-slate-600 font-medium flex items-center gap-1 truncate" title="<?php echo esc_attr($graduation); ?>">
								<svg class="w-3.5 h-3.5 text-brand-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
								</svg>
								<span class="truncate"><?php echo esc_html($graduation); ?></span>
							</span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Mobile swipe indicator -->
			<div class="md:hidden flex items-center justify-center gap-2 mt-4 text-xs font-semibold text-slate-500">
				<span>Vuốt xem 6 học viên tiêu biểu</span>
				<svg class="w-4 h-4 text-brand-primary animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
				</svg>
			</div>
		</div>
	</section>

	<!-- 8.5. ADMISSION PROCESS SECTION -->
	<section class="py-12 md:py-16 bg-slate-50 border-t border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<?php
			get_template_part(
				'template-parts/admission-process',
				null,
				[
					'title'      => 'Quy trình xét tuyển Liên thông & Đại học từ xa',
					'subtitle'   => 'Chỉ với 4 bước đơn giản, người đi làm dễ dàng sở hữu bằng Đại học chính quy / chuẩn Bộ GD&ĐT',
					'section_id' => 'quy-trinh-xet-tuyen-home',
					'cta_text'   => 'Kiểm tra điều kiện & Nhận tư vấn ngay',
					'cta_link'   => '/kiem-tra-dieu-kien',
					'card_bg'    => 'bg-white',
				]
			);
			?>
		</div>
	</section>

	<!-- 9. DYNAMIC CONSULTATION FORM SECTION -->
	<section id="register-section" class="py-16 bg-white">
		<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 border border-slate-100 shadow-xl rounded-xl p-6 md:p-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center bg-gradient-to-tr from-[#EFF6FF]/35 to-white">
			<!-- Graduate photo column -->
			<div class="lg:col-span-5 hidden lg:block">
				<div class="relative w-full aspect-[4/5] rounded-xl overflow-hidden shadow-md">
					<?php
					$raw_contact_banner = home_url( '/wp-content/uploads/2026/07/banner-contact.png' );
					$opt_contact_banner = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $raw_contact_banner ) : $raw_contact_banner;
					?>
					<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $opt_contact_banner ); ?>');"></div>
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
					['title' => 'Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026', 'date' => '08/07/2026', 'desc' => 'Quy trình và hồ sơ xét tuyển liên thông đại học từ trung cấp, cao đẳng lên đại học theo hình thức từ xa và vừa học vừa làm.'],
					['title' => 'Quy chế tuyển sinh Liên thông Cao đẳng lên Đại học', 'date' => '05/07/2026', 'desc' => 'Quy định rút ngắn chương trình đào tạo khi thi liên thông và các hồ sơ chuẩn bị nhập học.'],
					['title' => 'Học đại học vừa học vừa làm có giá trị như thế nào?', 'date' => '02/07/2026', 'desc' => 'Giá trị pháp lý của tấm bằng đại học vừa học vừa làm đối với cơ hội thăng tiến nghề nghiệp.'],
				];

				$news_has_posts = false;
				if ($news_query->have_posts()) {
					$news_has_posts = true;
					$index = 0;
					while ($news_query->have_posts()) : $news_query->the_post();
						$post_id = get_the_ID();
						$raw_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $post_id, 'medium' ) : ( get_the_post_thumbnail_url( $post_id, 'medium' ) ?: ltdh_get_fallback_image( 'news' ) );
						$thumb_url = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $raw_thumb ) : $raw_thumb;
						$categories = get_the_category($post_id);
						$category_name = ! empty($categories) ? $categories[0]->name : 'Tin tuyển sinh';
				?>
						<div class="bg-white border border-slate-100 rounded-lg overflow-hidden hover:shadow-md transition-all flex flex-col justify-between">
							<div class="h-36 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url($thumb_url); ?>');"></div>
							<div class="p-4 flex-1 flex flex-col justify-between">
								<div>
									<span class="text-xs font-bold text-brand-primary uppercase tracking-wider block mb-1"><?php echo esc_html($category_name); ?></span>
									<h3 class="font-extrabold text-slate-800 text-sm line-clamp-2 hover:text-[#00308b] transition-colors leading-snug">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h3>
								</div>
								<time class="text-xs text-slate-400 mt-3 block"><?php echo get_the_date('d/m/Y'); ?></time>
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
								<span class="text-xs font-bold text-brand-primary uppercase tracking-wider block mb-1">Hướng dẫn</span>
								<h3 class="font-bold text-slate-800 text-sm line-clamp-2 leading-snug"><?php echo esc_html($mn['title']); ?></h3>
								<p class="text-sm text-slate-400 leading-normal line-clamp-3 mt-2"><?php echo esc_html($mn['desc']); ?></p>
							</div>
							<time class="text-xs text-slate-400 mt-3 block"><?php echo esc_html($mn['date']); ?></time>
						</div>
				<?php
					}
				}
				?>
			</div>
		</div>
	</section>

	<!-- 11. FAQ SECTION (SECTION CUỐI TRANG CHỦ) -->
	<?php
	$homepage_faqs = [];
	if ( function_exists( 'get_field' ) ) {
		$homepage_faqs = get_field( 'faq_items', 'options' ) ?: [];
	}

	if ( ! empty( $homepage_faqs ) && is_array( $homepage_faqs ) ) :
	?>
	<section id="faq-section" class="py-16 md:py-20 bg-white border-t border-slate-100">
		<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
			<!-- Section Header -->
			<div class="text-center max-w-3xl mx-auto mb-10 space-y-3">
				<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">HỎI ĐÁP TUYỂN SINH</span>
				<h2 class="text-2xl md:text-4xl font-black text-slate-900 tracking-tight">Câu hỏi thường gặp</h2>
				<p class="text-slate-500 text-sm md:text-base">Giải đáp thắc mắc về điều kiện tuyển sinh liên thông, hình thức học trực tuyến, bằng cấp và học phí.</p>
			</div>

			<!-- FAQ Accordion List -->
			<div class="space-y-3 md:space-y-4">
				<?php
				foreach ( $homepage_faqs as $faq ) :
					$q_text = $faq['question'] ?? '';
					$a_text = $faq['answer'] ?? '';
					if ( empty( $q_text ) || empty( $a_text ) ) continue;
				?>
					<details class="bg-slate-50/70 border border-slate-100/90 rounded-2xl overflow-hidden group transition-all hover:bg-slate-50 hover:border-slate-200/60 shadow-2xs">
						<summary class="flex justify-between items-center font-bold text-slate-900 p-5 md:p-6 cursor-pointer list-none select-none text-base md:text-lg [&::-webkit-details-marker]:hidden gap-4">
							<span class="flex items-center gap-3">
								<span class="w-7 h-7 rounded-full bg-blue-50 text-brand-primary font-black text-xs flex items-center justify-center shrink-0">?</span>
								<span><?php echo esc_html( $q_text ); ?></span>
							</span>
							<span class="text-slate-400 group-open:rotate-180 transition-transform duration-300 shrink-0">
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
							</span>
						</summary>
						<div class="px-5 md:px-6 pb-6 pt-1 text-slate-600 text-sm md:text-base leading-relaxed border-t border-slate-100/60 bg-white">
							<p><?php echo esc_html( $a_text ); ?></p>
						</div>
					</details>
				<?php endforeach; ?>
			</div>

			<!-- View All FAQs Button -->
			<div class="mt-8 text-center">
				<a href="<?php echo esc_url( home_url( '/cau-hoi-thuong-gap/' ) ); ?>" class="inline-flex items-center gap-2 text-sm font-bold text-brand-primary hover:text-brand-darkBlue bg-blue-50 hover:bg-blue-100 px-5 py-3 rounded-xl transition-colors min-h-[44px]">
					<span>Xem tất cả câu hỏi thường gặp</span>
					<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
				</a>
			</div>
		</div>
	</section>
	<?php endif; ?>

</main>

<?php
get_footer();
