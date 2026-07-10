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
			if ( function_exists( 'wpcf7_contact_form_html' ) ) :
				echo do_shortcode( '[contact-form-7 id="consultation-form" title="Form Tư vấn"]' );
			else :
			?>
				<form action="#" method="POST" class="space-y-4">
					<input type="hidden" name="referral_source" value="<?php echo esc_attr( get_permalink() ); ?>">
					
					<div>
						<label class="block text-sm font-bold text-slate-700 mb-1">Họ và tên *</label>
						<input type="text" name="your-name" required class="w-full border border-slate-200 rounded-lg px-4 py-3 text-base focus:border-brand-primary focus:outline-none" placeholder="Họ và tên của bạn">
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-bold text-slate-700 mb-1">Số điện thoại *</label>
							<input type="tel" name="your-phone" required class="w-full border border-slate-200 rounded-lg px-4 py-3 text-base focus:border-brand-primary focus:outline-none" placeholder="Số điện thoại liên hệ">
						</div>
						<div>
							<label class="block text-sm font-bold text-slate-700 mb-1">Email (Không bắt buộc)</label>
							<input type="email" name="your-email" class="w-full border border-slate-200 rounded-lg px-4 py-3 text-base focus:border-brand-primary focus:outline-none" placeholder="Địa chỉ email">
						</div>
					</div>

					<div>
						<label class="block text-sm font-bold text-slate-700 mb-1">Chương trình bạn quan tâm</label>
						<select name="current_program_id" class="w-full border border-slate-200 rounded-lg px-4 py-3 text-base focus:border-brand-primary focus:outline-none">
							<option value="">-- Chọn khối ngành hoặc hệ đào tạo --</option>
							<?php
							$programs = get_posts( [ 'post_type' => 'program', 'numberposts' => -1 ] );
							foreach ( $programs as $p ) {
								echo '<option value="' . esc_attr( $p->ID ) . '">' . esc_html( $p->post_title ) . '</option>';
							}
							?>
						</select>
					</div>

					<div>
						<label class="block text-sm font-bold text-slate-700 mb-1">Nội dung yêu cầu tư vấn</label>
						<textarea name="your-message" rows="4" class="w-full border border-slate-200 rounded-lg px-4 py-3 text-base focus:border-brand-primary focus:outline-none" placeholder="Nhập thắc mắc hoặc câu hỏi cụ thể..."></textarea>
					</div>

					<div class="pt-4 flex flex-col items-center gap-4">
						<button type="submit" class="w-full bg-[#2563EB] text-white py-3.5 rounded-lg font-bold hover:bg-[#1E40AF] transition-all text-base shadow-md">
							ĐĂNG KÝ TƯ VẤN NGAY
						</button>
						<span class="text-xs text-slate-400 text-center">Cam kết thông tin đăng ký của bạn được bảo mật hoàn toàn 100%.</span>
					</div>
				</form>
			<?php endif; ?>
		</div>

	</div>
</main>

<?php
get_footer();
