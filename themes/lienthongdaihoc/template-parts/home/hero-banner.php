<?php
/**
 * Homepage Hero Banner Section (Swiper Banner Slider)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hero_slides = [];
if ( function_exists( 'get_field' ) ) {
	$hero_slides = get_field( 'hero_slides', 'options' ) ?: [];
}
if ( empty( $hero_slides ) ) {
	$hero_slides = [
		[
			'image' => function_exists( 'ltdh_get_fallback_image' ) ? ltdh_get_fallback_image( 'hero' ) : '',
			'link'  => '',
		],
	];
}
?>

<!-- 1. HERO SECTION (Swiper Banner Slider) -->
<section class="relative w-full overflow-hidden bg-slate-50 border-b border-slate-100">
	<div class="swiper hero-swiper w-full min-h-[220px] sm:min-h-[350px] md:min-h-[450px]">
		<div class="swiper-wrapper">
			<?php 
			$slide_idx = 0;
			foreach ( $hero_slides as $slide ) : 
				if ( empty( $slide['image'] ) ) continue;
				$slide_img_desktop = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide['image'] ) : $slide['image'];
				$clean_slide_base  = preg_replace( '#\?tr=.*$#', '', $slide_img_desktop );
				$slide_img_1400    = ! empty( $slide['image_1400'] ) ? $slide['image_1400'] : ( ( strpos( $slide_img_desktop, 'ik.imagekit.io' ) !== false || strpos( $slide_img_desktop, 'r2.dev' ) !== false ) ? $clean_slide_base . '?tr=w-1400,h-584' : ( function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide['image'], '1400w' ) : '' ) );
				$slide_img_mobile  = ! empty( $slide['image_mobile'] ) ? ( function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide['image_mobile'], true ) : $slide['image_mobile'] ) : ( ( strpos( $slide_img_desktop, 'ik.imagekit.io' ) !== false || strpos( $slide_img_desktop, 'r2.dev' ) !== false ) ? $clean_slide_base . '?tr=w-768,h-320' : ( function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide['image'], true ) : '' ) );
				$is_lcp            = ( 0 === $slide_idx );
				$loading_attr      = $is_lcp ? 'loading="eager" fetchpriority="high" data-no-lazy="1"' : 'loading="lazy" fetchpriority="low"';
				$hero_cls          = $is_lcp ? 'hero-banner hero-img skip-lazy no-lazy ' : '';
				?>

				<div class="swiper-slide w-full">
					<?php if ( ! empty( $slide['link'] ) ) : ?>
						<a href="<?php echo esc_url( $slide['link'] ); ?>" class="block w-full h-full">
					<?php endif; ?>
					
					<div class="relative w-full h-auto min-h-[220px] sm:min-h-[350px] md:min-h-[450px] max-h-[800px] overflow-hidden bg-[#f8fafc]">
						<!-- Main Banner Image -->
						<picture class="relative z-10 block w-full h-auto max-h-[800px] aspect-[1942/809]" style="aspect-ratio: 1942/809;">
							<?php if ( ! empty( $slide_img_mobile ) && $slide_img_mobile !== $slide_img_desktop ) : ?>
								<source media="(max-width: 768px)" srcset="<?php echo esc_url( $slide_img_mobile ); ?>" width="768" height="320">
							<?php endif; ?>
							<?php if ( ! empty( $slide_img_1400 ) && $slide_img_1400 !== $slide_img_desktop ) : ?>
								<source media="(max-width: 1440px)" srcset="<?php echo esc_url( $slide_img_1400 ); ?>" width="1400" height="584">
							<?php endif; ?>
							<img src="<?php echo esc_url( $slide_img_desktop ); ?>" width="1942" height="809" alt="Banner Hero" class="<?php echo $hero_cls; ?>w-full h-auto max-h-[800px] aspect-[1942/809] object-cover object-center pointer-events-none" style="aspect-ratio: 1942/809;" <?php echo $loading_attr; ?> decoding="async">
						</picture>
					</div>

					<?php if ( ! empty( $slide['link'] ) ) : ?>
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

<!-- Styling specifically for pagination position, arrows and critical layout -->
<style>
	.hero-swiper {
		position: relative;
		overflow: hidden;
		list-style: none;
		padding: 0;
		z-index: 1;
		display: block;
	}
	.hero-swiper .swiper-wrapper {
		position: relative;
		width: 100%;
		height: 100%;
		z-index: 1;
		display: flex;
		transition-property: transform;
		box-sizing: content-box;
	}
	.hero-swiper .swiper-slide {
		flex-shrink: 0;
		width: 100%;
		height: 100%;
		position: relative;
		transition-property: transform;
		display: block;
	}
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
