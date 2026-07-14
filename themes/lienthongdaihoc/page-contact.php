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

$hotline = ltdh_get_hotline();
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
							<p class="text-sm text-slate-500 mt-1"><?php echo esc_html( ltdh_get_address() ); ?></p>
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
							<p class="text-sm text-slate-500 mt-1"><?php echo esc_html( ltdh_get_email() ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<!-- Contact Form Column -->
			<div class="lg:col-span-7">
				<div class="bg-white rounded-lg p-6 md:p-8 shadow-sm border border-slate-200">
					<h3 class="font-bold text-xl text-slate-800 mb-4">GỬI YÊU CẦU CHO CHÚNG TÔI</h3>
					
					<?php 
					ltdh_render_contact_form();
				?>
				</div>
			</div>
		</div>

	</div>
</main>

<?php
get_footer();
