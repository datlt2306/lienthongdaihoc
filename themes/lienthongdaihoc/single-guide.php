<?php
/**
 * Single Guide Template
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$guide_id = get_the_ID();
$hotline = get_field( 'global_hotline', 'options' ) ?: '0389198653';
$global_zalo = get_field( 'global_zalo_url', 'options' ) ?: 'https://zalo.me';
?>

<main id="primary" class="site-main py-12 bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		
		<!-- Breadcrumbs -->
		<nav class="text-sm text-slate-500 mb-6">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-brand-primary">Trang chủ</a> / 
			<a href="<?php echo esc_url( home_url( '/huong-dan/' ) ); ?>" class="hover:text-brand-primary">Hướng dẫn tuyển sinh</a> / 
			<span><?php the_title(); ?></span>
		</nav>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-6">
				<article class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 md:p-8">
					<span class="inline-block bg-blue-50 text-brand-primary text-sm font-bold px-3 py-1 rounded-lg uppercase tracking-wider mb-4">Cẩm nang tuyển sinh</span>
					<h1 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight mb-4"><?php the_title(); ?></h1>
					
					<div class="flex items-center gap-4 text-sm text-slate-400 pb-4 border-b border-slate-100 mb-6">
						<span>Ngày đăng: <?php echo get_the_date( 'd/m/Y' ); ?></span>
					</div>

					<div class="prose prose-slate max-w-none text-slate-700 text-base leading-relaxed">
						<?php the_content(); ?>
					</div>
				</article>
			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<div class="sticky top-24 space-y-6">
					
					<!-- Sidebar register widget -->
					<section class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
						<h3 class="text-lg font-bold text-slate-900 mb-2">Nhận thêm thông tin chi tiết</h3>
						<p class="text-sm text-slate-500 mb-4">Để lại câu hỏi của bạn. Ban tuyển sinh đại học liên kết sẽ giải đáp nhanh chóng sau 15 phút.</p>
						
						<?php 
						if ( function_exists( 'wpcf7_contact_form_html' ) ) :
							echo do_shortcode( '[contact-form-7 id="consultation-form" title="Form Tư vấn"]' );
						else :
						?>
							<form action="#" method="POST" class="space-y-4">
								<input type="hidden" name="referral_source" value="<?php echo esc_attr( get_permalink() ); ?>">

								<div>
									<label class="block text-sm font-semibold text-slate-600 mb-1">Họ và tên *</label>
									<input type="text" name="your-name" required class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Họ và tên của bạn">
								</div>
								<div>
									<label class="block text-sm font-semibold text-slate-600 mb-1">Số điện thoại *</label>
									<input type="tel" name="your-phone" required class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Số điện thoại liên hệ">
								</div>
								
								<button type="submit" class="w-full bg-brand-accent text-white py-3 rounded-lg text-sm font-bold shadow-md hover:bg-amber-700 shadow-brand-accent/20 transition-all">
									Gửi Thông Tin Yêu Cầu
								</button>
							</form>
						<?php endif; ?>
					</section>

					<!-- Zalo / Call Card -->
					<div class="bg-blue-50 border border-blue-100 rounded-lg p-6 text-center">
						<span class="text-sm text-brand-primary font-bold uppercase tracking-wider block mb-1">Tổng đài hỗ trợ</span>
						<h4 class="font-display font-black text-2xl text-slate-800 mb-4"><?php echo esc_html( $hotline ); ?></h4>
						<div class="flex gap-2">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex-1 bg-brand-primary text-white py-2 rounded-lg font-semibold text-xs hover:bg-brand-darkBlue transition-all flex items-center justify-center min-h-[44px]">Gọi Điện</a>
							<a href="<?php echo esc_url( $global_zalo ); ?>" class="flex-1 bg-white border border-brand-primary text-brand-primary py-2 rounded-lg font-semibold text-xs hover:bg-blue-50 transition-all flex items-center justify-center min-h-[44px]">Zalo OA</a>
						</div>
					</div>

				</div>
			</div>
		</div>

	</div>
</main>

<?php
get_footer();
