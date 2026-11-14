<?php
/**
 * Card Post Partial - UI/UX Pro Max Edition
 *
 * Displays an editorial article card in news and category grids.
 * High-performance, zero layout shift, crisp aspect ratio, semantic microdata.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
if ( ! $post_id ) {
	return;
}

$permalink = get_permalink( $post_id );
$title     = get_the_title( $post_id );
$thumb_url = function_exists( 'ltdh_get_post_thumbnail_url' ) 
	? ltdh_get_post_thumbnail_url( $post_id, 'medium_large' ) 
	: ( get_the_post_thumbnail_url( $post_id, 'medium_large' ) ?: ( function_exists( 'ltdh_get_fallback_image' ) ? ltdh_get_fallback_image( 'post' ) : '' ) );

// Resolve Category
$post_cats = get_the_category( $post_id );
$post_cat  = null;
if ( ! empty( $post_cats ) ) {
	foreach ( $post_cats as $pc ) {
		if ( 'uncategorized' !== $pc->slug ) {
			$post_cat = $pc;
			break;
		}
	}
	if ( ! $post_cat && ! empty( $post_cats[0] ) ) {
		$post_cat = $post_cats[0];
	}
}

// Related school entity
$school_id = function_exists( 'get_field' ) ? get_field( 'school_relationship', $post_id ) : get_post_meta( $post_id, 'school_relationship', true );
if ( is_array( $school_id ) && ! empty( $school_id ) ) {
	$school_id = is_object( $school_id[0] ) ? $school_id[0]->ID : (int) $school_id[0];
} elseif ( is_object( $school_id ) ) {
	$school_id = $school_id->ID;
}
$school_id   = (int) $school_id;
$school_name = $school_id ? get_the_title( $school_id ) : '';
$school_code = $school_id ? ( get_post_meta( $school_id, 'school_code', true ) ?: '' ) : '';

// Reading time calculation
$content_raw       = get_post_field( 'post_content', $post_id );
$word_count        = str_word_count( strip_tags( (string) $content_raw ) );
$reading_time_mins = max( 2, (int) ceil( $word_count / 200 ) );

// Excerpt
$raw_excerpt = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_trim_words( (string) $content_raw, 24, '...' );
$clean_excerpt = wp_strip_all_tags( (string) $raw_excerpt );

$grid_class = $args['class'] ?? '';
?>
<article class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:-translate-y-1 hover:border-blue-300/80 transition-all duration-300 flex flex-col justify-between group cursor-pointer <?php echo esc_attr( $grid_class ); ?>">
	<!-- Card Thumbnail & Context Badges -->
	<a href="<?php echo esc_url( $permalink ); ?>" class="relative block aspect-[16/10] w-full bg-slate-100 overflow-hidden shrink-0" tabindex="-1" aria-hidden="true">
		<img src="<?php echo esc_url( $thumb_url ); ?>" 
		     alt="<?php echo esc_attr( $title ); ?>" 
		     loading="lazy" 
		     decoding="async"
		     class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500 pointer-events-none">
		
		<div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent pointer-events-none"></div>

		<!-- Partner University Badge (if tagged) -->
		<?php if ( ! empty( $school_name ) ) : ?>
			<span class="absolute top-3 left-3 z-10 bg-white/95 backdrop-blur-md text-[#00308b] text-[11px] font-extrabold px-3 py-1 rounded-full shadow-sm border border-white/60 line-clamp-1 max-w-[85%] flex items-center gap-1.5" title="<?php echo esc_attr( $school_name ); ?>">
				<svg class="w-3 h-3 text-[#00308b] shrink-0" fill="currentColor" viewBox="0 0 20 20">
					<path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a10.99 10.99 0 015.02-1.378 11.002 11.002 0 015.228 1.405l2.108-.903a1 1 0 000-1.84l-7-3zM4 9.5a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1z" />
				</svg>
				<span class="truncate"><?php echo esc_html( $school_code ? $school_code . ' • ' . $school_name : $school_name ); ?></span>
			</span>
		<?php endif; ?>

		<!-- Reading Time Mini Pill -->
		<span class="absolute bottom-2.5 right-2.5 z-10 bg-slate-900/75 backdrop-blur-xs text-white text-[11px] font-medium px-2 py-0.5 rounded-md flex items-center gap-1 border border-white/10 shadow-xs">
			<svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<span><?php echo esc_html( $reading_time_mins ); ?> phút</span>
		</span>
	</a>

	<!-- Card Body -->
	<div class="p-4 sm:p-5 flex-1 flex flex-col justify-between text-left">
		<div>
			<!-- Category & Published Date Row -->
			<div class="flex items-center justify-between gap-2 text-xs mb-2">
				<?php if ( $post_cat ) : ?>
					<a href="<?php echo esc_url( get_category_link( $post_cat->term_id ) ); ?>" class="font-bold text-[#00308b] hover:text-blue-700 uppercase tracking-wider text-[11px] truncate" title="<?php echo esc_attr( $post_cat->name ); ?>">
						<?php echo esc_html( $post_cat->name ); ?>
					</a>
				<?php else : ?>
					<span class="font-bold text-slate-400 uppercase tracking-wider text-[11px]">Tin tức</span>
				<?php endif; ?>

				<div class="inline-flex items-center gap-1 text-slate-400 font-medium shrink-0">
					<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
					</svg>
					<time datetime="<?php echo esc_attr( get_the_date( 'c', $post_id ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y', $post_id ) ); ?></time>
				</div>
			</div>

			<!-- Article Title -->
			<h3 class="font-extrabold text-slate-900 text-sm sm:text-base leading-snug tracking-tight line-clamp-2 min-h-[38px] sm:min-h-[44px] group-hover:text-[#00308b] transition-colors duration-200">
				<a href="<?php echo esc_url( $permalink ); ?>" title="<?php echo esc_attr( $title ); ?>">
					<?php echo esc_html( $title ); ?>
				</a>
			</h3>

			<!-- Excerpt -->
			<?php if ( ! empty( $clean_excerpt ) ) : ?>
				<p class="text-xs sm:text-sm text-slate-600 line-clamp-2 leading-relaxed mt-2">
					<?php echo esc_html( $clean_excerpt ); ?>
				</p>
			<?php endif; ?>
		</div>

		<!-- Card Action Footer -->
		<div class="border-t border-slate-100 pt-3 mt-4 flex items-center justify-between text-xs">
			<span class="inline-flex items-center gap-1.5 text-slate-500 font-medium truncate max-w-[65%]">
				<span class="w-5 h-5 rounded-full bg-blue-100 text-[#00308b] flex items-center justify-center font-bold text-[10px] shrink-0">
					<?php echo esc_html( mb_substr( get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ) ?: 'A', 0, 1 ) ); ?>
				</span>
				<span class="truncate"><?php echo esc_html( get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ) ); ?></span>
			</span>

			<a href="<?php echo esc_url( $permalink ); ?>" class="text-[#00308b] font-bold inline-flex items-center gap-1 group-hover:gap-2 transition-all duration-200">
				<span>Đọc tiếp</span>
				<svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
				</svg>
			</a>
		</div>
	</div>
</article>
