<?php
/**
 * Template Name: Liên hệ
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hotline = get_field( 'global_hotline', 'options' ) ?: '0389198653';
?>

<main id="primary" class="site-main py-12 bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		
		<h1 class="text-3xl font-black text-slate-900 mb-8">THÔNG TIN LIÊN HỆ</h1>

		<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
			<!-- Contact Information Columns -->
			<div class="lg:col-span-5 space-y-6">
				<div class="bg-white rounded-lg p-6 shadow-sm border border-slate-200 space-y-6">
					<h3 class="font-bold text-xl text-slate-800 pb-3 border-b border-slate-100">VĂN PHÒNG TUYỂN SINH</h3>
					
					<div class="flex items-start gap-4">
						<span class="text-xl">📍</span>
						<div>
							<h4 class="font-bold text-slate-700 text-sm">Địa chỉ trụ sở chính</h4>
							<p class="text-sm text-slate-500 mt-1">123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội</p>
						</div>
					</div>

					<div class="flex items-start gap-4">
						<span class="text-xl">📞</span>
						<div>
							<h4 class="font-bold text-slate-700 text-sm">Hotline liên hệ 24/7</h4>
							<p class="text-sm text-brand-primary mt-1 font-bold"><?php echo esc_html( $hotline ); ?></p>
						</div>
					</div>

					<div class="flex items-start gap-4">
						<span class="text-xl">✉</span>
						<div>
							<h4 class="font-bold text-slate-700 text-sm">Địa chỉ Email</h4>
							<p class="text-sm text-slate-500 mt-1">tuyensinh@lienthongdaihoc.com</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Contact Form Column -->
			<div class="lg:col-span-7">
				<div class="bg-white rounded-lg p-6 md:p-8 shadow-sm border border-slate-200">
					<h3 class="font-bold text-xl text-slate-800 mb-4">GỬI YÊU CẦU CHO CHÚNG TÔI</h3>
					
					<?php 
					if ( function_exists( 'wpcf7_contact_form_html' ) ) :
						echo do_shortcode( '[contact-form-7 id="contact-form" title="Form Liên hệ"]' );
					else :
					?>
						<form action="#" method="POST" class="space-y-4">
							<div>
								<label class="block text-sm font-semibold text-slate-600 mb-1">Họ và tên *</label>
								<input type="text" name="your-name" required class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Họ và tên của bạn">
							</div>
							<div>
								<label class="block text-sm font-semibold text-slate-600 mb-1">Số điện thoại *</label>
								<input type="tel" name="your-phone" required class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Số điện thoại liên lạc">
							</div>
							<div>
								<label class="block text-sm font-semibold text-slate-600 mb-1">Tin nhắn của bạn</label>
								<textarea name="your-message" rows="4" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Nhập tin nhắn..."></textarea>
							</div>
							
							<button type="submit" class="w-full bg-[#2563EB] text-white py-3 rounded-lg text-sm font-bold shadow-md hover:bg-[#1E40AF] transition-all">
								Gửi Liên Hệ
							</button>
						</form>
					<?php endif; ?>
				</div>
			</div>
		</div>

	</div>
</main>

<?php
get_footer();
