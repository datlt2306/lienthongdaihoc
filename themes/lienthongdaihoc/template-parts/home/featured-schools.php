<?php
/**
 * Home: Featured Partner Schools Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$featured_schools = $args['featured_schools'] ?? ( isset( $featured_schools ) ? $featured_schools : ( function_exists( 'ltdh_get_cached_featured_schools' ) ? ltdh_get_cached_featured_schools() : [] ) );
?>

<!-- 3. NATIONAL SCHOOLS SECTION (Apple Cupertino Carousel Slider) -->
<section id="program-section" class="py-10 sm:py-16 bg-white overflow-hidden">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 sm:mb-8">
			<div class="space-y-2">
				<span class="inline-block bg-[#0071E3]/10 text-[#0071E3] text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">ĐỐI TÁC ĐẠI HỌC</span>
				<h2 class="text-2xl md:text-3xl font-semibold text-[#1D1D1F] tracking-tight">Trường đối tác đào tạo</h2>
			</div>
			<div class="flex items-center gap-3 mt-4 sm:mt-0">
				<a href="<?php echo esc_url( home_url( '/truong-doi-tac/' ) ); ?>" class="apple-link text-sm text-[#0071E3] font-normal hover:underline inline-flex items-center gap-1 group">
					<span>Xem tất cả</span>
					<span class="transition-transform group-hover:translate-x-0.5">›</span>
				</a>
				<div class="hidden sm:flex items-center gap-2 pl-3 border-l border-black/[0.08]">
					<button type="button" class="school-slider-prev w-9 h-9 rounded-full border border-black/[0.08] bg-[#F5F5F7] hover:bg-[#E8E8ED] text-[#1D1D1F] flex items-center justify-center transition-all hover:scale-105 active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed shadow-none" aria-label="Trường trước đó">
						<?php if ( function_exists( 'ltdh_icon' ) ) { ltdh_icon( 'chevron-left', 'w-4 h-4' ); } ?>
					</button>
					<button type="button" class="school-slider-next w-9 h-9 rounded-full border border-black/[0.08] bg-[#F5F5F7] hover:bg-[#E8E8ED] text-[#1D1D1F] flex items-center justify-center transition-all hover:scale-105 active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed shadow-none" aria-label="Trường tiếp theo">
						<?php if ( function_exists( 'ltdh_icon' ) ) { ltdh_icon( 'chevron-right', 'w-4 h-4' ); } ?>
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
							$school_id       = $school['id'] ?? 0;
							$address         = $school['address'] ?? '';
							$hotline         = $school['hotline'] ?? '';
							$thumb_url       = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'medium' ) : ( $school['thumb_url'] ?? '' );
							if ( function_exists( 'ltdh_get_optimized_image_url' ) ) {
								$thumb_url = ltdh_get_optimized_image_url( $thumb_url );
							}
							$logo_id         = $school['logo_id'] ?? 0;
							$school_code     = ! empty( $school['code'] ) ? $school['code'] : ( get_post_meta( $school_id, 'school_code', true ) ?: '' );
							$en_name         = ! empty( $school['en_name'] ) ? $school['en_name'] : get_post_meta( $school_id, 'english_name', true );
							if ( ( empty( $en_name ) || 'University' === $en_name ) && ! empty( $school_code ) ) {
								$en_name = $school_code . ' University';
							}
							$school_types    = ! empty( $school['school_types'] ) ? $school['school_types'] : ( function_exists( 'ltdh_get_school_training_types' ) ? ltdh_get_school_training_types( $school_id ) : [] );
							$school_location = ! empty( $school['location'] ) ? $school['location'] : ( function_exists( 'ltdh_get_school_location_label' ) ? ltdh_get_school_location_label( $school_id ) : '' );
							$school_region   = ! empty( $school['region'] ) ? $school['region'] : ( function_exists( 'ltdh_get_school_region_label' ) ? ltdh_get_school_region_label( $school_id ) : '' );

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
								<div class="w-full bg-white border border-black/[0.07] rounded-3xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_16px_36px_rgba(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
									<!-- Ảnh trường & Vị trí -->
									<div class="relative h-36 sm:h-40 bg-slate-100 overflow-hidden">
										<img src="<?php echo esc_url( $thumb_url ); ?>"
											 alt="<?php echo esc_attr( $school['title'] ?? 'Ảnh trường' ); ?>"
											 loading="lazy"
											 decoding="async"
											 width="300"
											 height="180"
											 class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
										<div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-slate-900/10 to-transparent pointer-events-none"></div>
										<?php if ( ! empty( $school_region ) ) : ?>
											<span class="absolute top-3 right-3 bg-white/80 backdrop-blur-md text-[#1D1D1F] text-[11px] font-medium px-2.5 py-0.5 rounded-full z-10 flex items-center gap-1 border border-black/[0.08] shadow-2xs">
												<?php if ( function_exists( 'ltdh_icon' ) ) { ltdh_icon( 'location', 'w-3 h-3 text-[#0071E3] shrink-0' ); } ?>
												<span><?php echo esc_html( $school_region ); ?></span>
											</span>
										<?php endif; ?>
									</div>

									<!-- Logo trường (Apple Squircle) -->
									<div class="h-14 w-14 sm:h-16 sm:w-16 bg-white rounded-2xl border border-black/[0.08] shadow-[0_4px_16px_rgba(0,0,0,0.08)] -mt-7 sm:-mt-8 ml-4 sm:ml-5 z-10 relative flex items-center justify-center overflow-hidden p-1.5 transition-transform duration-300 group-hover:scale-105">
										<?php if ( ! empty( $logo_id ) && function_exists( 'wp_get_attachment_image' ) ) : 
											$logo_alt = get_post_meta( $logo_id, '_wp_attachment_image_alt', true ) ?: ( 'Logo ' . ( $school['title'] ?? '' ) );
										?>
											<?php echo wp_get_attachment_image( $logo_id, 'thumbnail', false, [
												'class'    => 'h-full w-full object-contain',
												'alt'      => $logo_alt,
												'width'    => 96,
												'height'   => 96,
												'sizes'    => '48px',
												'loading'  => 'lazy',
												'decoding' => 'async',
											] ); ?>
										<?php else : ?>
											<span class="font-display font-semibold text-[#0071E3] text-sm">UNI</span>
										<?php endif; ?>
									</div>

									<!-- Nội dung thẻ -->
									<div class="p-4 sm:p-5 pt-2 flex-1 flex flex-col justify-between text-left">
										<div>
											<!-- Mã trường & Địa điểm -->
											<div class="flex items-center gap-2 mb-1.5 min-h-[20px]">
												<?php if ( ! empty( $school_code ) ) : ?>
													<span class="font-medium text-[#0071E3] bg-[#0071E3]/10 px-2.5 py-0.5 rounded-full text-[11px]">Mã: <?php echo esc_html( $school_code ); ?></span>
												<?php endif; ?>
												<?php if ( ! empty( $school_location ) ) : ?>
													<span class="text-xs text-slate-500 font-medium truncate"><?php echo esc_html( $school_location ); ?></span>
												<?php endif; ?>
											</div>

											<!-- Tên trường -->
											<h3 class="font-extrabold text-slate-900 text-sm md:text-[15px] tracking-tight leading-snug line-clamp-1 mt-0.5 group-hover:text-[#00308b] transition-colors">
												<a href="<?php echo esc_url( $school['permalink'] ?? '#' ); ?>">
													<?php echo esc_html( $school['title'] ?? '' ); ?>
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
													<?php if ( function_exists( 'ltdh_icon' ) ) { ltdh_icon( 'location', 'w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5' ); } ?>
													<span class="line-clamp-2 leading-relaxed" title="<?php echo esc_attr( $display_address ); ?>">
														<?php echo esc_html( $display_address ); ?>
													</span>
												</div>
											<?php endif; ?>
										</div>

										<!-- Nút Tìm hiểu chi tiết (Cupertino Pill) -->
										<div class="mt-3.5 pt-3 border-t border-black/[0.06]">
											<a href="<?php echo esc_url( $school['permalink'] ?? '#' ); ?>" 
											   class="w-full text-center py-2 px-3 rounded-full text-xs sm:text-sm font-medium text-white bg-[#0071E3] hover:bg-[#0077ED] active:scale-98 transition-all duration-200 flex items-center justify-center gap-1.5 shadow-none">
												<span class="sm:hidden">Xem trường</span>
												<span class="hidden sm:inline">Tìm hiểu chi tiết</span>
												<?php if ( function_exists( 'ltdh_icon' ) ) { ltdh_icon( 'arrow-right', 'w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1 shrink-0' ); } ?>
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
