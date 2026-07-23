<?php
/**
 * Theme footer template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hotline  = ltdh_get_hotline();
$zalo     = ltdh_get_zalo_url();
$messenger = ltdh_get_messenger_url();
?>

	<footer id="colophon" class="site-footer bg-slate-900 text-slate-400 pt-16 pb-28 border-t border-slate-800">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
			<!-- About / Info -->
			<div class="md:col-span-2">
				<?php
				$footer_logo = ltdh_get_logo_url();
				if ( $footer_logo ) :
				?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-block mb-4">
						<img src="<?php echo esc_url( $footer_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="h-auto object-contain" style="max-height: 48px; width: auto; filter: brightness(0) invert(1);">
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-2 font-display font-extrabold text-2xl text-white mb-4">
						<div class="flex flex-col leading-none">
							<span class="text-sm font-semibold text-slate-400 tracking-wider">LIÊN THÔNG</span>
							<span class="text-xl font-extrabold text-brand-primary">ĐẠI HỌC</span>
						</div>
					</a>
				<?php endif; ?>
				<p class="text-sm text-slate-400 mb-4 max-w-sm">
					Cổng thông tin tuyển sinh đại học trực tuyến, liên thông, văn bằng 2 và vừa học vừa làm hàng đầu Việt Nam. Kết nối người học với các chương trình chất lượng từ hơn 50 trường đại học top đầu.
				</p>
				<p class="text-sm font-semibold text-white">Hotline hỗ trợ: <?php echo esc_html( $hotline ); ?></p>
			</div>

			<!-- Quick Links -->
			<div>
				<h3 class="font-display font-semibold text-white mb-4 text-lg">Hệ đào tạo</h3>
				<?php
				$training_types = get_terms( [ 'taxonomy' => 'training_type', 'hide_empty' => false ] );
				if ( ! is_wp_error( $training_types ) && ! empty( $training_types ) ) :
					echo '<ul class="space-y-2 text-sm">';
					foreach ( $training_types as $term ) :
						echo '<li><a href="' . esc_url( get_term_link( $term ) ) . '" class="hover:text-brand-primary transition-colors">' . esc_html( $term->name ) . '</a></li>';
					endforeach;
					echo '</ul>';
				endif;
				?>
			</div>

			<!-- Pages -->
			<div>
				<h3 class="font-display font-semibold text-white mb-4 text-lg">Liên kết nhanh</h3>
				<?php
				wp_nav_menu( [
					'theme_location' => 'footer-menu',
					'container'      => false,
					'menu_class'     => 'space-y-2 text-sm flex flex-col',
					'fallback_cb'    => 'ltdh_default_footer_menu',
				] );
				?>
			</div>
		</div>

		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-slate-800 text-center text-sm text-slate-500">
			<p>© <?php echo date( 'Y' ); ?> lienthongdaihoc.com. Tất cả quyền được bảo lưu. Bản quyền thuộc về Cổng thông tin Tuyển sinh liên kết Đại học.</p>
		</div>
	</footer>
</div><!-- #page -->

<!-- Floating/Sticky CTAs for Mobile Conversion (Desktop/Tablet only) -->
<div class="hidden md:flex fixed bottom-4 left-4 z-40 flex-col gap-2.5 md:bottom-6 md:left-6">
	<!-- Phone Hotline Float -->
	<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex h-12 w-12 items-center justify-center rounded-lg bg-brand-accent text-white shadow-lg hover:bg-[#e06e00] transition-all hover:scale-105" aria-label="Gọi ngay">
		<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
		</svg>
	</a>

	<!-- Zalo OA Float -->
	<a href="<?php echo esc_url( $zalo ); ?>" target="_blank" rel="noopener noreferrer" class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-500 text-white shadow-lg hover:bg-blue-600 transition-all hover:scale-105" aria-label="Chat Zalo">
		<span class="font-display font-black text-sm">Zalo</span>
	</a>
</div>

<!-- FIXED MOBILE CTA BAR (ALL PAGES) -->
<div class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-100 p-2.5 flex items-center justify-between gap-3 shadow-[0_-4px_10px_rgba(0,0,0,0.05)] md:hidden">
	<!-- Messenger Button -->
	<a href="<?php echo esc_url( $messenger ); ?>" target="_blank" rel="noopener" class="flex items-center justify-center h-12 w-12 bg-orange-500 hover:bg-orange-600 rounded-xl shrink-0 transition-colors" aria-label="Messenger">
		<svg class="w-6 h-6 fill-current text-white" viewBox="0 0 24 24">
			<path d="M12 2C6.477 2 2 6.145 2 11.258c0 2.914 1.448 5.518 3.7 7.205V22l3.39-1.859c.91.253 1.87.392 2.87.392 5.523 0 10-4.146 10-9.258C22 6.145 17.523 2 12 2zm1.26 12.18l-2.45-2.61-4.78 2.61 5.26-5.59 2.5 2.61 4.73-2.61-5.26 5.59z"/>
		</svg>
	</a>
	<!-- Register Button -->
	<?php
	$register_link = '#register';
	if ( ! is_singular( [ 'major', 'program', 'school' ] ) ) {
		$register_link = home_url( '/lien-he/' );
	}
	?>
	<a href="<?php echo esc_url( $register_link ); ?>" class="flex-1 flex items-center justify-center gap-1.5 bg-[#00a2f4] text-white font-extrabold text-sm h-12 rounded-xl hover:bg-[#0091db] transition-colors">
		<svg class="w-5 h-5 fill-current text-white shrink-0" viewBox="0 0 20 20">
			<path fill-rule="evenodd" d="M6.672 1.911a1 1 0 10-1.932.518l.259.966a1 1 0 001.932-.518l-.259-.966zM2.429 4.74a1 1 0 10-.518 1.932l.966.259a1 1 0 00.518-1.932l-.966-.259zm8.839 2.517a1 1 0 00-1.042-.018l-7 4a1 1 0 00-.086 1.707l3.183 2.122 2.122 3.183a1 1 0 001.707-.086l4-7a1 1 0 00-.018-1.042l-3.866-3.866zM7.17 11.232l4.896-2.797 2.797 4.896-4.896 2.797-2.797-4.896z" clip-rule="evenodd" />
		</svg>
		Đăng ký tư vấn ngay
	</a>
	<!-- Hotline Button -->
	<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex items-center justify-center h-12 w-12 bg-orange-500 hover:bg-orange-600 rounded-xl shrink-0 transition-colors" aria-label="Hotline">
		<svg class="w-6 h-6 fill-none stroke-current text-white" stroke-width="2" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
		</svg>
	</a>
</div>

<style>
@media (max-w: 767px) {
  body {
    padding-bottom: 72px !important;
  }
}
</style>



<?php wp_footer(); ?>
</body>
</html>
