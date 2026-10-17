<?php
/**
 * Card Major Partial
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$major_id = get_the_ID();
$thumb    = get_the_post_thumbnail_url( $major_id, 'medium_large' );
if ( empty( $thumb ) ) {
	$major_fallbacks = [
		get_stylesheet_directory_uri() . '/assets/images/banner-program.jpg',
		get_stylesheet_directory_uri() . '/assets/images/banner-hero-02.webp',
		get_stylesheet_directory_uri() . '/assets/images/banner-default.jpg',
	];
	$idx   = $major_id % count( $major_fallbacks );
	$thumb = $major_fallbacks[ $idx ];
}

// Get count of distinct schools actively offering this major
$schools_count = function_exists( 'ltdh_get_major_unique_schools_count' )
	? ltdh_get_major_unique_schools_count( $major_id )
	: 0;

// Get category term
$terms    = get_the_terms( $major_id, 'major_cat' );
$cat_name = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->name : 'Ngành đào tạo';
?>
<div class="bg-white border border-slate-100/80 rounded-xl md:rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:border-brand-primary/20 transition-all duration-300 flex flex-col justify-between group">
	
	<!-- Header Thumbnail Area -->
	<div class="relative aspect-[4/3] sm:aspect-[16/10] md:aspect-auto md:h-60 lg:h-64 bg-slate-100 overflow-hidden">
		<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
		<div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-900/20 to-transparent"></div>

		<?php if ( $schools_count > 0 ) : ?>
			<div class="absolute bottom-2 right-2 md:bottom-3 md:right-3">
				<span class="inline-flex items-center gap-1 px-2 py-0.5 md:px-2.5 md:py-1 rounded-full text-[10px] md:text-xs font-bold bg-emerald-600 text-white shadow-xs backdrop-blur-md">
					<span><?php echo esc_html( $schools_count ); ?></span> trường tuyển sinh
				</span>
			</div>
		<?php endif; ?>
	</div>

	<!-- Card Body -->
	<div class="p-3 sm:p-4 md:p-6 flex-1 flex flex-col justify-between">
		<div>
			<div class="text-[10px] sm:text-xs font-bold text-brand-primary uppercase tracking-wider mb-1 line-clamp-1">
				<?php echo esc_html( $cat_name ); ?>
			</div>
			<h3 class="font-black text-slate-900 text-xs sm:text-base md:text-xl hover:text-brand-primary transition-colors mb-1.5 sm:mb-2.5 line-clamp-2 leading-tight sm:leading-snug">
				<a href="<?php the_permalink(); ?>">Ngành <?php the_title(); ?></a>
			</h3>
			<p class="hidden md:block text-sm text-slate-500 line-clamp-2 leading-relaxed mb-4">
				<?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?>
			</p>
		</div>

		<!-- Action Footer -->
		<div class="pt-2.5 sm:pt-3 md:pt-4 border-t border-slate-100 mt-auto">
			<a href="<?php the_permalink(); ?>" class="w-full inline-flex items-center justify-center gap-1.5 sm:gap-2 bg-slate-100 hover:bg-brand-primary text-slate-700 hover:text-white py-2 sm:py-2.5 md:py-3 px-2 sm:px-3 md:px-4 rounded-lg md:rounded-xl font-bold transition-all text-xs sm:text-sm group/btn min-h-[38px] md:min-h-[44px]">
				<span>Khám phá</span><span class="hidden sm:inline">ngành học</span>
				<svg class="w-3.5 h-3.5 md:w-4 md:h-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
			</a>
		</div>
	</div>

</div>
