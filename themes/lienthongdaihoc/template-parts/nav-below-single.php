<?php
/**
 * Single post adjacent navigation
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$prev_post = get_previous_post();
$next_post = get_next_post();

if ( ! $prev_post && ! $next_post ) {
	return;
}
?>
<nav class="navigation post-navigation mt-8 pt-8 border-t border-slate-200/80" aria-label="<?php esc_attr_e( 'Điều hướng bài viết', 'ltdh' ); ?>">
	<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
		<?php if ( $prev_post ) :
			$prev_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $prev_post->ID, 'thumbnail' ) : ( get_the_post_thumbnail_url( $prev_post->ID, 'thumbnail' ) ?: ltdh_get_fallback_image( 'post' ) );
		?>
			<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="group p-4 rounded-xl bg-white border border-slate-200/80 hover:border-[#0071E3] hover:shadow-sm transition-all flex items-center gap-3.5 min-h-[72px]">
				<span class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 group-hover:bg-blue-50 group-hover:text-[#0071E3] flex items-center justify-center shrink-0 transition-colors font-bold text-sm">
					←
				</span>
				<div class="min-w-0 flex-1">
					<span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Bài trước</span>
					<h4 class="text-xs sm:text-sm font-bold text-slate-800 group-hover:text-[#0071E3] transition-colors line-clamp-2 leading-snug">
						<?php echo esc_html( get_the_title( $prev_post->ID ) ); ?>
					</h4>
				</div>
			</a>
		<?php else : ?>
			<div class="hidden sm:block"></div>
		<?php endif; ?>

		<?php if ( $next_post ) :
			$next_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $next_post->ID, 'thumbnail' ) : ( get_the_post_thumbnail_url( $next_post->ID, 'thumbnail' ) ?: ltdh_get_fallback_image( 'post' ) );
		?>
			<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="group p-4 rounded-xl bg-white border border-slate-200/80 hover:border-[#0071E3] hover:shadow-sm transition-all flex items-center justify-between gap-3.5 min-h-[72px] sm:text-right">
				<div class="min-w-0 flex-1">
					<span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Bài tiếp theo</span>
					<h4 class="text-xs sm:text-sm font-bold text-slate-800 group-hover:text-[#0071E3] transition-colors line-clamp-2 leading-snug">
						<?php echo esc_html( get_the_title( $next_post->ID ) ); ?>
					</h4>
				</div>
				<span class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 group-hover:bg-blue-50 group-hover:text-[#0071E3] flex items-center justify-center shrink-0 transition-colors font-bold text-sm">
					→
				</span>
			</a>
		<?php endif; ?>
	</div>
</nav>
