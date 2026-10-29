<?php
/**
 * Home: Dynamic Consultation Form Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- 9. DYNAMIC CONSULTATION FORM SECTION (Apple Cupertino Bento Form) -->
<section id="register-section" class="hidden md:block scroll-mt-20 md:scroll-mt-24 py-8 sm:py-12 md:py-16 bg-white">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-20">
		<div class="bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.06)] border border-black/[0.08] overflow-hidden grid grid-cols-1 lg:grid-cols-12 mb-12">
		
			<!-- Left Side Graphic -->
			<div class="lg:col-span-5 relative min-h-[350px] md:min-h-[450px] lg:min-h-[550px] bg-slate-50">
				<?php
				$raw_contact_banner = home_url( '/wp-content/uploads/2026/07/banner-contact.png' );
				$opt_contact_banner = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $raw_contact_banner ) : $raw_contact_banner;
				?>
				<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $opt_contact_banner ); ?>');"></div>
			</div>

			<!-- Right Side Form -->
			<div class="lg:col-span-7 p-6 md:p-10 flex flex-col justify-center">
				<div class="mb-6">
					<span class="inline-block bg-[#0071E3]/10 text-[#0071E3] text-xs font-semibold px-3.5 py-1 rounded-full uppercase tracking-wider mb-3">
						ĐĂNG KÝ TRỰC TUYẾN
					</span>
					<h2 class="text-2xl md:text-3xl font-semibold text-[#1D1D1F] tracking-tight mb-2">
						Đăng ký nhận tư vấn tuyển sinh miễn phí
					</h2>
					<p class="text-sm text-[#86868B] leading-relaxed">
						Vui lòng cung cấp thông tin liên hệ, đội ngũ tuyển sinh của chúng tôi sẽ chủ động gọi lại hỗ trợ giải đáp lộ trình cho bạn sớm nhất.
					</p>
				</div>

				<div class="ltdh-contact-form-wrapper">
					<?php 
					if ( function_exists( 'ltdh_render_consultation_form' ) ) {
						ltdh_render_consultation_form();
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>
