<?php
/**
 * Card Major Partial
 *
 * Displays a major card in directory listings and taxonomy archives.
 * Conforms to clean minimalist card layout (Image #2 specification):
 * - Clean aspect-[16/10] thumbnail
 * - Prominent Category badge (e.g. XÃ HỘI - DỊCH VỤ)
 * - Title "Ngành [Title]"
 * - Rich excerpt / overview description
 * - Action button "Khám phá ngành học →"
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$major_id = get_the_ID();

// 1. Resolve thumbnail: Post thumbnail -> Category thumbnail -> Fallback banner
$thumb = function_exists( 'ltdh_get_major_card_thumbnail_url' )
	? ltdh_get_major_card_thumbnail_url( $major_id, 'medium_large' )
	: ( get_the_post_thumbnail_url( $major_id, 'medium_large' ) ?: get_stylesheet_directory_uri() . '/assets/images/banner-fallback.webp' );

// 2. Resolve Category Term
$terms    = get_the_terms( $major_id, 'major_cat' );
$cat_name = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->name : 'Ngành đào tạo';

// 3. Format Title with "Ngành" prefix if not already present
$raw_title     = get_the_title( $major_id );
$display_title = preg_match( '/^ngành\s+/iu', $raw_title ) ? $raw_title : 'Ngành ' . $raw_title;

// 4. Resolve Excerpt / Overview description
$excerpt = get_the_excerpt( $major_id );
if ( empty( trim( $excerpt ) ) ) {
	$overview = get_post_meta( $major_id, 'major_overview', true );
	if ( ! empty( $overview ) ) {
		$excerpt = wp_strip_all_tags( $overview );
	} else {
		$excerpt = wp_strip_all_tags( get_post_field( 'post_content', $major_id ) );
	}
}
$excerpt = wp_trim_words( $excerpt, 40, ' [...]' );
?>

<div class="bg-white border border-slate-200/80 rounded-2xl md:rounded-3xl overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between group">
	
	<!-- Header Thumbnail Area -->
	<div class="relative aspect-[16/10] bg-slate-100 overflow-hidden">
		<a href="<?php the_permalink(); ?>" class="block w-full h-full" tabindex="-1" aria-hidden="true">
			<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $display_title ); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
		</a>
	</div>

	<!-- Card Body -->
	<div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
		<div>
			<div class="text-xs font-extrabold text-[#00308b] tracking-wider uppercase mb-1.5 line-clamp-1">
				<?php echo esc_html( $cat_name ); ?>
			</div>
			<h3 class="font-bold text-slate-900 text-lg sm:text-xl md:text-2xl hover:text-brand-primary transition-colors mb-2.5 sm:mb-3 line-clamp-2 leading-snug">
				<a href="<?php the_permalink(); ?>"><?php echo esc_html( $display_title ); ?></a>
			</h3>
			<p class="text-xs sm:text-sm text-slate-600 line-clamp-4 leading-relaxed mb-5 font-normal">
				<?php echo esc_html( $excerpt ); ?>
			</p>
		</div>

		<!-- Action Footer -->
		<div class="pt-5 border-t border-slate-100/90 mt-auto">
			<a href="<?php the_permalink(); ?>" class="w-full inline-flex items-center justify-center gap-2 bg-[#f0f4f9] hover:bg-[#e2e8f0] text-[#324158] hover:text-[#0f172a] font-bold py-3.5 sm:py-4 px-5 rounded-2xl text-sm sm:text-[15px] transition-all duration-200 group/btn">
				<span>Khám phá ngành học</span>
				<svg class="w-4 h-4 transition-transform duration-200 group-hover/btn:translate-x-1.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M5 12h14M12 5l7 7-7 7" />
				</svg>
			</a>
		</div>
	</div>

</div>
