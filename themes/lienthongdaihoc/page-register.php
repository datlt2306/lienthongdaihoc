<?php
/**
 * Template Name: Đăng ký tư vấn
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main py-16 bg-slate-50">
	<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
		
		<div class="bg-white rounded-xl p-8 md:p-12 shadow-md border border-slate-200">
			<div class="text-center max-w-lg mx-auto mb-8">
				<h1 class="text-3xl font-black text-slate-900 mb-3">ĐĂNG KÝ NHẬN TƯ VẤN TUYỂN SINH</h1>
				<p class="text-slate-500 text-sm">Hãy điền đầy đủ thông tin của bạn dưới đây. Các chuyên gia sẽ liên hệ tư vấn lộ trình học phù hợp nhất với bạn hoàn toàn miễn phí.</p>
			</div>

			<?php 
				ltdh_render_consultation_form( [
					'referral_source' => get_permalink(),
				] );
			?>
		</div>

	</div>
</main>

<?php
get_footer();
