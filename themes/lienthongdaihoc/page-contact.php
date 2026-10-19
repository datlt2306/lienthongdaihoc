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
$email = ltdh_get_email();
$address = ltdh_get_address();
?>

<!-- BANNER SECTION -->
<section class="relative bg-gradient-to-tr from-[#0E2038] to-brand-primary text-white py-20 md:py-28 overflow-hidden">
	<!-- Dot Grid Pattern -->
	<div class="absolute inset-0 opacity-10 pointer-events-none z-0" style="background-image: radial-gradient(white 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
	<div class="absolute -right-32 -bottom-32 w-96 h-96 bg-brand-accent/20 rounded-full blur-3xl"></div>
	
	<div class="relative max-w-4xl mx-auto px-4 text-center z-10 space-y-2 animate-fade-in">
		<h1 class="text-2xl sm:text-3xl md:text-4xl font-black font-display tracking-tight leading-tight uppercase">LIÊN HỆ VỚI CHÚNG TÔI</h1>
		<p class="text-blue-100 text-sm md:text-base font-semibold max-w-xl mx-auto">Hỗ trợ giải đáp các thắc mắc về điều kiện tuyển sinh, chương trình đào tạo Liên thông Đại học (Hệ chính quy, Vừa học vừa làm, Đào tạo từ xa).</p>
	</div>
</section>

<main id="primary" class="site-main py-12 bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-20">
		
		<!-- MAIN CONTACT CARD (Matches Image Layout) -->
		<div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden grid grid-cols-1 lg:grid-cols-12 mb-12">
			
			<!-- Left Side Graphic (Uses the banner-contact image from homepage) -->
			<div class="lg:col-span-5 relative min-h-[350px] md:min-h-[450px] lg:min-h-[550px] bg-slate-50">
				<!-- The custom contact banner image containing the counselor illustration and pathway -->
				<?php
				$custom_banner      = get_the_post_thumbnail_url( get_the_ID(), 'full' );
				$raw_contact_banner = $custom_banner ?: home_url( '/wp-content/uploads/2026/07/banner-contact.png' );
				$opt_contact_banner = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $raw_contact_banner ) : $raw_contact_banner;
				?>
				<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $opt_contact_banner ); ?>');"></div>
			</div>

			<!-- Right Side Form (Matches Mockup exact copy and style) -->
			<div class="lg:col-span-7 p-6 md:p-10 flex flex-col justify-center">
				<div class="mb-6">
					<span class="inline-block bg-blue-50 text-[#00308b] text-sm font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-3">
						ĐĂNG KÝ TRỰC TUYẾN
					</span>
					<h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-2">
						Đăng ký nhận tư vấn tuyển sinh miễn phí
					</h2>
					<p class="text-sm text-slate-500 leading-relaxed">
						Vui lòng cung cấp thông tin liên hệ, đội ngũ tuyển sinh của chúng tôi sẽ chủ động gọi lại hỗ trợ giải đáp lộ trình cho bạn sớm nhất.
					</p>
				</div>

				<div class="ltdh-contact-form-wrapper">
					<?php ltdh_render_consultation_form(); ?>
				</div>
			</div>
		</div>

		<!-- SECONDARY SECTIONS: OFFICE CARDS -->
		<div class="mt-8">
			<h2 class="sr-only">Thông tin liên hệ & Văn phòng hỗ trợ</h2>
			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				<!-- Office Card -->
				<div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 flex gap-4">
					<div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-xl shrink-0">
						📍
					</div>
					<div>
						<h3 class="font-bold text-slate-950 text-sm mb-1">Địa chỉ văn phòng</h3>
						<p class="text-sm text-slate-500 leading-normal"><?php echo esc_html( $address ); ?></p>
					</div>
				</div>

				<!-- Phone Card -->
				<div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 flex gap-4">
					<div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-xl shrink-0">
						📞
					</div>
					<div>
						<h3 class="font-bold text-slate-950 text-sm mb-1">Hotline tư vấn</h3>
						<p class="text-sm text-[#00308b] font-bold leading-normal"><?php echo esc_html( $hotline ); ?></p>
						<p class="text-sm text-slate-400 mt-0.5">Hỗ trợ 24/7 toàn quốc</p>
					</div>
				</div>

				<!-- Email Card -->
				<div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 flex gap-4">
					<div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-xl shrink-0">
						✉
					</div>
					<div>
						<h3 class="font-bold text-slate-950 text-sm mb-1">Địa chỉ Email</h3>
						<p class="text-sm text-slate-500 leading-normal break-all"><?php echo esc_html( $email ); ?></p>
						<p class="text-sm text-slate-400 mt-0.5">Phản hồi trong vòng 24h làm việc</p>
					</div>
				</div>
			</div>
		</div>

	</div>
</main>

<?php
get_footer();
