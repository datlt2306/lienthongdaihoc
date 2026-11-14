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
<section class="relative bg-gradient-to-tr from-[#0E2038] via-[#0F284E] to-brand-primary text-white py-20 md:py-28 overflow-hidden shadow-sm">
	<!-- Ambient Modern Background: Soft Aura Glow & Subtle Fine Grid (Clean, Eye-friendly, High-legibility) -->
	<div class="absolute inset-0 pointer-events-none z-0 overflow-hidden select-none" aria-hidden="true">
		<!-- Fine Blueprint Grid Pattern (Subtle 4% opacity, zero glare) -->
		<svg class="absolute inset-0 w-full h-full opacity-[0.04]" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
			<defs>
				<pattern id="ltdh-contact-fine-grid" width="36" height="36" patternUnits="userSpaceOnUse">
					<path d="M 36 0 L 0 0 0 36" fill="none" stroke="#ffffff" stroke-width="1"/>
				</pattern>
				<radialGradient id="ltdh-contact-grid-mask-grad" cx="50%" cy="50%" r="55%">
					<stop offset="0%" stop-color="#ffffff" stop-opacity="0.2"/>
					<stop offset="60%" stop-color="#ffffff" stop-opacity="0.8"/>
					<stop offset="100%" stop-color="#ffffff" stop-opacity="1"/>
				</radialGradient>
				<mask id="ltdh-contact-grid-mask">
					<rect width="100%" height="100%" fill="url(#ltdh-contact-grid-mask-grad)"/>
				</mask>
			</defs>
			<rect width="100%" height="100%" fill="url(#ltdh-contact-fine-grid)" mask="url(#ltdh-contact-grid-mask)"/>
		</svg>

		<!-- Elegant Ambient Glow Orbs (Soft Diffusion, Pure Depth) -->
		<div class="absolute -right-24 -top-24 w-96 h-96 bg-sky-400/15 rounded-full blur-3xl pointer-events-none"></div>
		<div class="absolute right-1/4 -bottom-32 w-80 h-80 bg-brand-primary/25 rounded-full blur-3xl pointer-events-none"></div>
		<div class="absolute -left-20 -bottom-20 w-72 h-72 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>

		<!-- Subtle Right-side Academic Geometric Concentric Rings (Only 5% opacity, non-intrusive) -->
		<svg class="absolute -right-16 top-1/2 -translate-y-1/2 w-[480px] h-[480px] opacity-[0.05] pointer-events-none hidden md:block" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
			<circle cx="200" cy="200" r="185" stroke="#ffffff" stroke-width="1" stroke-dasharray="4 6" />
			<circle cx="200" cy="200" r="145" stroke="#ffffff" stroke-width="1" />
			<circle cx="200" cy="200" r="105" stroke="#ffffff" stroke-width="1" stroke-dasharray="2 4" />
			<circle cx="200" cy="200" r="65" stroke="#ffffff" stroke-width="1" />
		</svg>
	</div>

	<!-- Subtle Top Accent Line -->
	<div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#00308b] via-blue-400 to-amber-400 z-10"></div>
	
	<div class="relative max-w-4xl mx-auto px-4 text-center z-10 space-y-2 animate-fade-in">
		<h1 class="text-2xl sm:text-3xl md:text-4xl font-black font-display tracking-tight leading-tight uppercase drop-shadow-sm">LIÊN HỆ VỚI CHÚNG TÔI</h1>
		<p class="text-blue-100/95 text-sm md:text-base font-normal max-w-xl mx-auto leading-relaxed">Hỗ trợ giải đáp các thắc mắc về điều kiện tuyển sinh, chương trình đào tạo Liên thông Đại học (Hệ chính quy, Vừa học vừa làm, Đào tạo từ xa).</p>
	</div>
</section>

<div id="primary" class="content-area site-main py-12 bg-slate-50">
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
						LIÊN HỆ TRỰC TUYẾN
					</span>
					<h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-2">
						Gửi thông tin liên hệ & tư vấn
					</h2>
					<p class="text-sm text-slate-500 leading-relaxed">
						Vui lòng để lại thông tin hoặc câu hỏi cần giải đáp, đội ngũ ban tuyển sinh sẽ phản hồi và chủ động gọi lại hỗ trợ bạn sớm nhất.
					</p>
				</div>

				<div class="ltdh-contact-form-wrapper">
					<?php ltdh_render_contact_form(); ?>
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
</div><!-- #primary -->

<?php
get_footer();
