<?php
/**
 * Program Mobile School Minibar
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$school_id       = isset( $args['school_id'] ) ? (int) $args['school_id'] : 0;
$school_title    = isset( $args['school_title'] ) ? $args['school_title'] : '';
$school_logo_url = isset( $args['school_logo_url'] ) ? $args['school_logo_url'] : '';
$initials        = isset( $args['initials'] ) ? $args['initials'] : 'ĐH';

if ( ! $school_id ) {
	return;
}
?>
<!-- MOBILE ONLY SCHOOL MINI BAR (< 1024px) -->
<div class="block lg:hidden bg-white border border-slate-100 rounded-2xl p-3 shadow-xs">
	<div class="flex items-center justify-between gap-3">
		<div class="flex items-center gap-2.5 min-w-0">
			<div class="w-9 h-9 rounded-xl bg-slate-50 p-0.5 shrink-0 flex items-center justify-center overflow-hidden">
				<?php if ( $school_logo_url ) : ?>
					<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_title ); ?>" class="max-w-full max-h-full object-contain">
				<?php else : ?>
					<span class="text-xs font-black text-[#00308b]"><?php echo esc_html( $initials ?: 'ĐH' ); ?></span>
				<?php endif; ?>
			</div>
			<div class="min-w-0">
				<span class="text-xs font-bold text-slate-400 uppercase tracking-wider block leading-none mb-1">Trường đào tạo</span>
				<p class="font-extrabold text-slate-800 text-sm truncate leading-tight"><?php echo esc_html( $school_title ); ?></p>
			</div>
		</div>
		<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="px-3 py-1.5 bg-[#F5F5F7] hover:bg-[#E8E8ED] text-[#1D1D1F] text-xs font-medium tracking-tight rounded-full transition-all shrink-0">
			Chi tiết →
		</a>
	</div>
</div>
