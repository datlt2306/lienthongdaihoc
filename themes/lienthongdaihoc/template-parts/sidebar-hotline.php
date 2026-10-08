<?php
/**
 * Template part: Sidebar Hotline & Contact Box
 *
 * Unified contact widget across school, major, program, and guide templates.
 *
 * @package ltdh
 *
 * @param array $args {
 *     @type string $hotline     Hotline phone number (optional, fallback to ltdh_get_hotline()).
 *     @type string $zalo_url    Zalo URL (optional, fallback to ltdh_get_zalo_url()).
 *     @type string $title       Widget title (default: 'Văn phòng tuyển sinh').
 *     @type string $extra_class Additional CSS classes for the container wrapper.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$box_hotline = ! empty( $args['hotline'] ) ? $args['hotline'] : ( function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496' );
$box_zalo    = ! empty( $args['zalo_url'] ) ? $args['zalo_url'] : ( function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me' );
$box_title   = ! empty( $args['title'] ) ? $args['title'] : 'VĂN PHÒNG TUYỂN SINH';
$clean_phone = preg_replace( '/\D/', '', $box_hotline );
$extra_class = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';
?>

<!-- UNIFIED SIDEBAR CONTACT CARD -->
<div class="relative bg-gradient-to-tr from-[#0E2038] to-brand-primary text-white rounded-xl p-6 text-center shadow-lg overflow-hidden border border-slate-800 <?php echo esc_attr( $extra_class ); ?>">
	<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 16px 16px;"></div>
	<span class="text-xs md:text-sm text-brand-accent font-extrabold uppercase tracking-wider block mb-1"><?php echo esc_html( $box_title ); ?></span>
	<a href="tel:<?php echo esc_attr( $clean_phone ); ?>" class="font-display font-black text-xl md:text-2xl text-white hover:text-brand-accent transition-colors block mb-4 tracking-tight">
		<?php echo esc_html( $box_hotline ); ?>
	</a>
	<div class="flex gap-2 relative z-10">
		<a href="tel:<?php echo esc_attr( $clean_phone ); ?>"
		   class="flex-1 bg-brand-accent text-white py-3.5 rounded-lg font-bold text-sm hover:bg-[#e06e00] transition-all min-h-[44px] flex items-center justify-center shadow-sm shadow-brand-accent/20 active:scale-95">
			Gọi Ngay
		</a>
		<a href="<?php echo esc_url( $box_zalo ); ?>"
		   target="_blank"
		   rel="noopener noreferrer"
		   class="flex-1 bg-white/10 text-white border border-white/20 py-3.5 rounded-lg font-bold text-sm hover:bg-white/20 transition-all min-h-[44px] flex items-center justify-center active:scale-95">
			Chat Zalo
		</a>
	</div>
</div>
