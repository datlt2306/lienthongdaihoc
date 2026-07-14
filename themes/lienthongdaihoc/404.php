<?php
/**
 * 404 Page Template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="min-h-screen bg-slate-50">
	<section class="relative bg-gradient-to-tr from-[#0E2038] to-brand-primary text-white py-20 md:py-32 overflow-hidden">
		<div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(white 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
		<div class="absolute -right-32 -bottom-32 w-96 h-96 bg-brand-accent/20 rounded-full blur-3xl"></div>

		<div class="relative max-w-4xl mx-auto px-4 text-center z-10 space-y-6">
			<h1 class="text-6xl md:text-8xl font-black font-display tracking-tight">404</h1>
			<p class="text-blue-100 text-lg md:text-xl font-semibold max-w-md mx-auto">Trang bạn tìm kiếm không tồn tại hoặc đã bị di chuyển.</p>
			<div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="bg-white text-brand-primary px-8 py-3.5 rounded-lg font-bold hover:bg-slate-100 transition-all shadow-md">
					Về trang chủ
				</a>
				<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" class="border border-white/30 text-white px-8 py-3.5 rounded-lg font-bold hover:bg-white/10 transition-all">
					Xem chương trình đào tạo
				</a>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
