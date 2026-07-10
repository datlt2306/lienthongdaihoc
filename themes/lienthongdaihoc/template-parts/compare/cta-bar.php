<?php
/**
 * Comparison CTA Bar — shown on all comparison pages.
 *
 * @package lienthongdaihoc
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hotline = ltdh_compare_get_global_hotline();
$zalo    = ltdh_compare_get_zalo_url();
?>
<section class="bg-white rounded shadow-sm border border-slate-100 p-6">
	<div class="text-center mb-6">
		<h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-2">Đăng ký tư vấn miễn phí</h2>
		<p class="text-slate-500 text-sm">Bạn cần hỗ trợ thêm? Liên hệ ngay với ban tư vấn tuyển sinh.</p>
	</div>

	<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
		<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>"
		   class="flex flex-col items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl p-4 transition-all group">
			<span class="text-2xl">📞</span>
			<span class="text-xs font-bold text-emerald-700 uppercase">Gọi Hotline</span>
			<span class="text-sm font-bold text-slate-800"><?php echo esc_html( $hotline ); ?></span>
		</a>
		<a href="<?php echo esc_url( $zalo ); ?>" target="_blank" rel="noopener"
		   class="flex flex-col items-center gap-1.5 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl p-4 transition-all group">
			<span class="text-2xl">💬</span>
			<span class="text-xs font-bold text-blue-700 uppercase">Chat Zalo</span>
			<span class="text-sm font-bold text-slate-800">Trao đổi ngay</span>
		</a>
		<a href="<?php echo esc_url( home_url( '/dang-ky-tu-van/' ) ); ?>"
		   class="flex flex-col items-center gap-1.5 bg-brand-primary/5 hover:bg-brand-primary/10 border border-brand-primary/20 rounded-xl p-4 transition-all group">
			<span class="text-2xl">📝</span>
			<span class="text-xs font-bold text-brand-primary uppercase">Đăng ký tư vấn</span>
			<span class="text-sm font-bold text-slate-800">Form online</span>
		</a>
		<?php
		$messenger = get_field( 'global_messenger_url', 'options' );
		if ( $messenger ) :
		?>
		<a href="<?php echo esc_url( $messenger ); ?>" target="_blank" rel="noopener"
		   class="flex flex-col items-center gap-1.5 bg-sky-50 hover:bg-sky-100 border border-sky-200 rounded-xl p-4 transition-all group">
			<span class="text-2xl">💌</span>
			<span class="text-xs font-bold text-sky-700 uppercase">Messenger</span>
			<span class="text-sm font-bold text-slate-800">Nhắn tin FB</span>
		</a>
		<?php endif; ?>
	</div>

	<div class="bg-slate-50 rounded-xl p-4 text-center">
		<p class="text-sm text-slate-500">
			<strong class="text-slate-700">Liên hệ hotline <?php echo esc_html( $hotline ); ?></strong> để được tư vấn miễn phí về lộ trình học, điều kiện tuyển sinh và chính sách hỗ trợ học phí.
		</p>
	</div>
</section>
