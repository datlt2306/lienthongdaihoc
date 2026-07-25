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

	<footer id="colophon" class="site-footer text-slate-400 pt-16 pb-28 border-t border-slate-800/80" style="background-color: #0d192d;">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
			<!-- Column 1: Brand Info -->
			<div>
				<h2 class="font-display font-extrabold text-2xl text-white mb-4 tracking-tight">lienthongdaihoc<span style="color: #f97316;">.com</span></h2>
				<p class="text-sm text-slate-400 mb-6 leading-relaxed">
					lienthongdaihoc.com – Đơn vị tư vấn chuyên sâu về liên thông Đại học chính quy. Chúng tôi đồng hành cùng bạn trên hành trình chinh phục tấm bằng đại học và kiến tạo sự nghiệp tương lai.
				</p>
				<!-- Social links circles -->
				<div class="flex items-center gap-3">
					<a href="#" class="w-9 h-9 rounded-full bg-slate-800/50 border border-slate-800 hover:bg-blue-600 hover:border-blue-600 hover:text-white transition-all flex items-center justify-center text-slate-400" aria-label="Facebook">
						<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
					</a>
					<a href="#" class="w-9 h-9 rounded-full bg-slate-800/50 border border-slate-800 hover:bg-blue-500 hover:border-blue-500 hover:text-white transition-all flex items-center justify-center text-slate-400" aria-label="Zalo">
						<span class="text-xs font-black">Za</span>
					</a>
					<a href="#" class="w-9 h-9 rounded-full bg-slate-800/50 border border-slate-800 hover:bg-red-600 hover:border-red-600 hover:text-white transition-all flex items-center justify-center text-slate-400" aria-label="Youtube">
						<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.163a3.003 3.003 0 00-2.11-2.11C19.517 3.545 12 3.545 12 3.545s-7.517 0-9.388.508a3.003 3.003 0 00-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 002.11 2.11c1.871.508 9.388.508 9.388.508s7.517 0 9.388-.508a3.002 3.002 0 002.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
					</a>
				</div>
			</div>

			<!-- Column 2: Contact Info -->
			<div>
				<h3 class="font-display font-extrabold text-white mb-6 text-xs uppercase tracking-wider flex items-center gap-2">
					<span class="w-[3px] h-3 inline-block" style="background-color: #00a2f4;"></span>Thông tin liên hệ
				</h3>
				<ul class="space-y-4 text-sm">
					<li class="flex items-start gap-3">
						<span class="text-[#00a2f4] shrink-0 mt-0.5" style="color: #00a2f4;">
							<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
						</span>
						<div>
							<strong class="text-white block font-extrabold text-xs uppercase tracking-wider">Trụ sở Hà Nội</strong>
							<span class="text-slate-400 text-xs">Số 71 ngõ 32/84 Đỗ Đức Dục, Nam Từ Liêm, Hà Nội</span>
						</div>
					</li>
					<li class="flex items-start gap-3">
						<span class="text-[#00a2f4] shrink-0 mt-0.5" style="color: #00a2f4;">
							<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
						</span>
						<div>
							<strong class="text-white block font-extrabold text-xs uppercase tracking-wider">Hotline / Zalo</strong>
							<span class="font-extrabold text-base block mt-0.5" style="color: #00a2f4;">0338 615 497</span>
						</div>
					</li>
					<li class="flex items-start gap-3">
						<span class="text-[#00a2f4] shrink-0 mt-0.5" style="color: #00a2f4;">
							<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
						</span>
						<div>
							<strong class="text-white block font-extrabold text-xs uppercase tracking-wider">Email Tư vấn</strong>
							<span class="text-slate-400 text-xs">tuvan@lienthongdaihoc.com</span>
						</div>
					</li>
				</ul>
			</div>

			<!-- Column 3: Training Programs -->
			<div>
				<h3 class="font-display font-extrabold text-white mb-6 text-xs uppercase tracking-wider flex items-center gap-2">
					<span class="w-[3px] h-3 inline-block" style="background-color: #00a2f4;"></span>Chương trình đào tạo
				</h3>
				<ul class="space-y-2.5 text-sm">
					<li class="flex items-center">
						<span style="display: inline-block; width: 6px; height: 6px; background-color: #00a2f4; border-radius: 50%; margin-right: 8px; flex-shrink: 0;"></span>
						<a href="<?php echo esc_url( home_url('/he-dao-tao/tu-xa/') ); ?>" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Học đại học từ xa</a>
					</li>
					<li class="flex items-center">
						<span style="display: inline-block; width: 6px; height: 6px; background-color: #00a2f4; border-radius: 50%; margin-right: 8px; flex-shrink: 0;"></span>
						<a href="#" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Cao đẳng online / VB2</a>
					</li>
					<li class="flex items-center">
						<span style="display: inline-block; width: 6px; height: 6px; background-color: #00a2f4; border-radius: 50%; margin-right: 8px; flex-shrink: 0;"></span>
						<a href="#" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Liên thông Đại Học chính quy</a>
					</li>
					<li class="flex items-center">
						<span style="display: inline-block; width: 6px; height: 6px; background-color: #00a2f4; border-radius: 50%; margin-right: 8px; flex-shrink: 0;"></span>
						<a href="#" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Trung Cấp lên Đại học</a>
					</li>
					<li class="flex items-center">
						<span style="display: inline-block; width: 6px; height: 6px; background-color: #00a2f4; border-radius: 50%; margin-right: 8px; flex-shrink: 0;"></span>
						<a href="#" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Đại học tại chức / VLVH</a>
					</li>
					<li class="flex items-center">
						<span style="display: inline-block; width: 6px; height: 6px; background-color: #00a2f4; border-radius: 50%; margin-right: 8px; flex-shrink: 0;"></span>
						<a href="#" class="text-slate-400 hover:text-white transition-colors text-xs font-semibold">Liên thông Đại Học Offline</a>
					</li>
				</ul>
			</div>

			<!-- Column 4: Community Fanpage -->
			<div>
				<h3 class="font-display font-extrabold text-white mb-6 text-xs uppercase tracking-wider flex items-center gap-2">
					<span class="w-[3px] h-3 inline-block" style="background-color: #00a2f4;"></span>Kết nối cộng đồng
				</h3>
				<div class="border border-slate-800 p-4 rounded-xl flex flex-col gap-4" style="background-color: rgba(10, 21, 38, 0.5); border-color: rgba(255, 255, 255, 0.1);">
					<div class="flex items-center gap-3">
						<div class="w-11 h-11 rounded-lg bg-blue-600 flex items-center justify-center text-white shrink-0 shadow-sm p-2" style="background-color: #1877f2;">
							<svg class="w-full h-full fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
						</div>
						<div class="min-w-0">
							<h4 class="font-extrabold text-white text-sm truncate">Fan Page</h4>
							<span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider block mt-0.5">Cập nhật tin tức mỗi ngày</span>
						</div>
					</div>
					<a href="#" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 rounded-lg transition-colors text-center w-full shadow-sm" style="background-color: #1877f2;">Tham gia ngay</a>
				</div>
			</div>
		</div>

		<!-- Footer Bottom -->
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 font-semibold uppercase tracking-wider gap-4">
			<p>© 2026 lienthongdaihoc.com. Tất cả các quyền được bảo hộ.</p>
			<div class="flex items-center gap-6">
				<a href="#" class="hover:text-white transition-colors">Chính sách bảo mật</a>
				<a href="#" class="hover:text-white transition-colors">Điều khoản dịch vụ</a>
			</div>
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
