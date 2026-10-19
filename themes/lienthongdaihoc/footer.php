<?php
/**
 * Theme footer template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hotline   = ltdh_get_hotline();
$zalo      = ltdh_get_zalo_url();
$messenger = ltdh_get_messenger_url();
$address   = function_exists( 'ltdh_get_address' ) ? ltdh_get_address() : 'Số 71 ngõ 32/84 Đỗ Đức Dục, Nam Từ Liêm, HN';
$email     = function_exists( 'ltdh_get_email' ) ? ltdh_get_email() : 'tuvan@lienthongdaihoc.com';
?>

	<footer id="colophon" class="site-footer text-slate-400 pt-10 pb-28 md:py-16 border-t border-slate-800/80" style="background-color: #0d192d;">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 md:grid-cols-4 gap-8 lg:gap-10">
				
				<!-- Column 1: Brand Info & Social -->
				<div class="space-y-4">
					<div class="flex items-center justify-between md:block">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-block">
							<span class="font-display font-black text-2xl text-white tracking-tight">lienthongdaihoc<span class="text-orange-500">.com</span></span>
						</a>
					</div>
					
					<p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-sm">
    Kênh thông tin tuyển sinh Liên thông Đại học, Vừa học vừa làm và Đào tạo từ xa. Hỗ trợ bạn tìm hiểu ngành học, điều kiện tuyển sinh và lựa chọn chương trình phù hợp với công việc, thời gian của mình.
					</p>

					<!-- Social Links Circles (Thumb-friendly 40x40px touch targets) -->
					<div>
						<span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2.5">Kênh thông tin chính thức</span>
						<div class="flex items-center gap-2.5">
							<a href="https://www.facebook.com/groups/lienthongdh" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-xl bg-slate-800/80 border border-slate-700/60 hover:bg-[#1877f2] hover:border-[#1877f2] hover:text-white active:scale-95 transition-all flex items-center justify-center text-slate-300 shadow-xs" aria-label="Facebook">
								<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
							</a>
							<a href="<?php echo esc_url( $zalo ); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-xl bg-slate-800/80 border border-slate-700/60 hover:bg-[#0068ff] hover:border-[#0068ff] hover:text-white active:scale-95 transition-all flex items-center justify-center text-slate-300 shadow-xs" aria-label="Zalo">
								<span class="text-xs font-black">Za</span>
							</a>
							
						</div>
					</div>
				</div>

				<!-- Column 2: Contact Info -->
				<div>
					<h3 class="font-display font-extrabold text-white mb-4 md:mb-6 text-xs uppercase tracking-wider flex items-center gap-2">
						<span class="w-1.5 h-3.5 rounded-full bg-[#00a2f4]"></span>Thông tin liên hệ
					</h3>
					
					<!-- 1-Tap Hotline Call Card (Mobile Optimized) -->
					<div class="space-y-3">
						<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex items-center justify-between p-3 rounded-xl bg-gradient-to-r from-blue-900/40 to-slate-800/60 border border-blue-500/30 hover:border-[#00a2f4] active:scale-[0.99] transition-all group">
							<div class="flex items-center gap-3">
								<div class="w-9 h-9 rounded-lg bg-[#00a2f4]/20 border border-[#00a2f4]/40 flex items-center justify-center text-[#00a2f4] shrink-0 group-hover:scale-110 transition-transform">
									<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
								</div>
								<div>
									<span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Hotline Tư Vấn 24/7</span>
									<span class="text-sm sm:text-base font-black text-white group-hover:text-[#00a2f4] transition-colors"><?php echo esc_html( $hotline ?: '0988 991 496' ); ?></span>
								</div>
							</div>
							<span class="px-2.5 py-1 rounded-md bg-[#00a2f4] text-white text-[11px] font-extrabold shadow-xs shrink-0">Gọi ngay</span>
						</a>

						<ul class="space-y-2.5 text-xs">
							<li class="flex items-start gap-2.5 p-2 rounded-lg bg-slate-800/20 border border-slate-800/40">
								<svg class="w-4 h-4 text-[#00a2f4] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
								<div class="min-w-0">
									<strong class="text-white block font-bold text-[11px] uppercase tracking-wider">Trụ sở Hà Nội</strong>
									<span class="text-slate-400"><?php echo esc_html( $address ); ?></span>
								</div>
							</li>
							<li class="flex items-start gap-2.5 p-2 rounded-lg bg-slate-800/20 border border-slate-800/40">
								<svg class="w-4 h-4 text-[#00a2f4] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
								<div class="min-w-0">
									<strong class="text-white block font-bold text-[11px] uppercase tracking-wider">Email Tuyển sinh</strong>
									<!--email_off--><a href="mailto:<?php echo esc_attr( $email ); ?>" class="text-slate-400 hover:text-white transition-colors truncate block"><?php echo esc_html( $email ); ?></a><!--/email_off-->
								</div>
							</li>
						</ul>
					</div>
				</div>

				<!-- Column 3: Training Programs (2-Column Mobile Touch Grid) -->
				<div>
					<h3 class="font-display font-extrabold text-white mb-4 md:mb-6 text-xs uppercase tracking-wider flex items-center gap-2">
						<span class="w-1.5 h-3.5 rounded-full bg-[#00a2f4]"></span>Chương trình đào tạo
					</h3>
					<!-- Mobile: 2-column touch tiles | Desktop: vertical list -->
					<div class="grid grid-cols-2 gap-2 md:flex md:flex-col md:space-y-2.5">
						<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/chinh-quy/' ) ); ?>" 
						   class="flex items-center justify-between p-2.5 md:p-0 rounded-xl md:rounded-none bg-slate-800/40 md:bg-transparent border border-slate-800/70 md:border-none hover:bg-slate-800 md:hover:bg-transparent text-slate-300 hover:text-white active:scale-[0.98] md:active:scale-100 transition-all min-h-[42px] md:min-h-0 group">
							<span class="text-xs font-bold md:font-semibold line-clamp-1">Liên thông Chính quy</span>
							<span class="text-[#00a2f4] text-xs font-bold md:hidden">›</span>
						</a>
						<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/dao-tao-tu-xa/' ) ); ?>" 
						   class="flex items-center justify-between p-2.5 md:p-0 rounded-xl md:rounded-none bg-slate-800/40 md:bg-transparent border border-slate-800/70 md:border-none hover:bg-slate-800 md:hover:bg-transparent text-slate-300 hover:text-white active:scale-[0.98] md:active:scale-100 transition-all min-h-[42px] md:min-h-0 group">
							<span class="text-xs font-bold md:font-semibold line-clamp-1">Đại học Từ xa</span>
							<span class="text-[#00a2f4] text-xs font-bold md:hidden">›</span>
						</a>
						<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/vua-hoc-vua-lam/' ) ); ?>" 
						   class="flex items-center justify-between p-2.5 md:p-0 rounded-xl md:rounded-none bg-slate-800/40 md:bg-transparent border border-slate-800/70 md:border-none hover:bg-slate-800 md:hover:bg-transparent text-slate-300 hover:text-white active:scale-[0.98] md:active:scale-100 transition-all min-h-[42px] md:min-h-0 group">
							<span class="text-xs font-bold md:font-semibold line-clamp-1">Vừa học vừa làm</span>
							<span class="text-[#00a2f4] text-xs font-bold md:hidden">›</span>
						</a>
						<a href="<?php echo esc_url( home_url( '/truong-doi-tac/' ) ); ?>" 
						   class="flex items-center justify-between p-2.5 md:p-0 rounded-xl md:rounded-none bg-slate-800/40 md:bg-transparent border border-slate-800/70 md:border-none hover:bg-slate-800 md:hover:bg-transparent text-slate-300 hover:text-white active:scale-[0.98] md:active:scale-100 transition-all min-h-[42px] md:min-h-0 group">
							<span class="text-xs font-bold md:font-semibold line-clamp-1">Trường đối tác</span>
							<span class="text-[#00a2f4] text-xs font-bold md:hidden">›</span>
						</a>
						<a href="<?php echo esc_url( home_url( '/nhom-nganh/' ) ); ?>" 
						   class="flex items-center justify-between p-2.5 md:p-0 rounded-xl md:rounded-none bg-slate-800/40 md:bg-transparent border border-slate-800/70 md:border-none hover:bg-slate-800 md:hover:bg-transparent text-slate-300 hover:text-white active:scale-[0.98] md:active:scale-100 transition-all min-h-[42px] md:min-h-0 group">
							<span class="text-xs font-bold md:font-semibold line-clamp-1">Ngành liên thông</span>
							<span class="text-[#00a2f4] text-xs font-bold md:hidden">›</span>
						</a>
						<a href="<?php echo esc_url( home_url( '/kiem-tra-dieu-kien/' ) ); ?>" 
						   class="flex items-center justify-between p-2.5 md:p-0 rounded-xl md:rounded-none bg-slate-800/40 md:bg-transparent border border-slate-800/70 md:border-none hover:bg-slate-800 md:hover:bg-transparent text-slate-300 hover:text-white active:scale-[0.98] md:active:scale-100 transition-all min-h-[42px] md:min-h-0 group">
							<span class="text-xs font-bold md:font-semibold line-clamp-1 text-orange-400">Kiểm tra điều kiện</span>
							<span class="text-orange-400 text-xs font-bold md:hidden">›</span>
						</a>
					</div>
				</div>

				<!-- Column 4: Community & Fanpage -->
				<div>
					<h3 class="font-display font-extrabold text-white mb-4 md:mb-6 text-xs uppercase tracking-wider flex items-center gap-2">
						<span class="w-1.5 h-3.5 rounded-full bg-[#00a2f4]"></span>Kết nối cộng đồng
					</h3>
					<!-- Compact Horizontal Community Card -->
					<div class="border border-slate-800/80 p-3.5 sm:p-4 rounded-xl bg-gradient-to-br from-slate-900/90 to-blue-950/40 flex flex-col gap-3 shadow-xs">
						<div class="flex items-center gap-3">
							<div class="w-10 h-10 rounded-xl bg-[#1877f2] flex items-center justify-center text-white shrink-0 shadow-sm p-2">
								<svg class="w-full h-full fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
							</div>
							<div class="min-w-0">
								<p class="font-extrabold text-white text-xs sm:text-sm truncate">Group Hỏi - Đáp Tuyển Sinh</p>
							</div>
						</div>
						<a href="https://www.facebook.com/groups/lienthongdh" target="_blank" rel="noopener noreferrer" 
						   class="bg-[#1877f2] hover:bg-blue-600 active:scale-[0.98] text-white text-xs font-bold py-2.5 rounded-lg transition-all text-center w-full shadow-sm flex items-center justify-center gap-1.5 min-h-[40px]">
							<span>Tham gia nhóm ngay</span>
							<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
						</a>
					</div>
				</div>
			</div>

			<!-- Footer Bottom -->
			<div class="mt-8 md:mt-12 pt-6 md:pt-8 border-t border-slate-800/80 flex flex-col md:flex-row items-center justify-between text-xs text-slate-500 font-medium gap-3.5 text-center md:text-left">
				<p>© 2026 lienthongdaihoc.com. Tất cả các quyền được bảo hộ.</p>
				<div class="flex items-center justify-center gap-4 sm:gap-6 text-xs">
					<a href="<?php echo esc_url( home_url( '/chinh-sach-bao-mat/' ) ); ?>" class="text-slate-400 hover:text-white transition-colors py-1">Chính sách bảo mật</a>
					<span class="text-slate-700">•</span>
					<a href="<?php echo esc_url( home_url( '/dieu-khoan/' ) ); ?>" class="text-slate-400 hover:text-white transition-colors py-1">Điều khoản dịch vụ</a>
				</div>
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
<div id="ltdh-global-mobile-bar" class="fixed bottom-0 left-0 right-0 z-40 bg-white/98 backdrop-blur-md border-t border-slate-200/90 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] px-3 sm:px-4 pt-2.5 pb-[calc(0.625rem+env(safe-area-inset-bottom,0px))] flex items-center justify-between gap-2.5 sm:gap-3 md:hidden">
	<!-- Messenger Button -->
	<a href="<?php echo esc_url( $messenger ); ?>" target="_blank" rel="noopener" class="flex items-center justify-center h-12 w-12 bg-orange-500 hover:bg-orange-600 active:scale-95 rounded-2xl shrink-0 transition-all shadow-sm shadow-orange-500/20" aria-label="Messenger">
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
	<a href="<?php echo esc_url( $register_link ); ?>" class="flex-1 flex items-center justify-center gap-2 bg-[#00a2f4] hover:bg-[#0091db] active:scale-[0.98] text-white font-extrabold text-sm sm:text-base h-12 px-3 rounded-2xl shadow-md shadow-[#00a2f4]/30 transition-all">
		<svg class="w-5 h-5 fill-current text-white shrink-0" viewBox="0 0 20 20">
			<path fill-rule="evenodd" d="M6.672 1.911a1 1 0 10-1.932.518l.259.966a1 1 0 001.932-.518l-.259-.966zM2.429 4.74a1 1 0 10-.518 1.932l.966.259a1 1 0 00.518-1.932l-.966-.259zm8.839 2.517a1 1 0 00-1.042-.018l-7 4a1 1 0 00-.086 1.707l3.183 2.122 2.122 3.183a1 1 0 001.707-.086l4-7a1 1 0 00-.018-1.042l-3.866-3.866zM7.17 11.232l4.896-2.797 2.797 4.896-4.896 2.797-2.797-4.896z" clip-rule="evenodd" />
		</svg>
		<span class="truncate">Đăng ký tư vấn ngay</span>
	</a>
	<!-- Hotline Button -->
	<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex items-center justify-center h-12 w-12 bg-orange-500 hover:bg-orange-600 active:scale-95 rounded-2xl shrink-0 transition-all shadow-sm shadow-orange-500/20" aria-label="Hotline">
		<svg class="w-6 h-6 fill-none stroke-current text-white" stroke-width="2.2" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
		</svg>
	</a>
</div>

<style>
@media (max-width: 767px) {
  body {
    padding-bottom: calc(4.75rem + env(safe-area-inset-bottom, 0px)) !important;
  }
}
</style>



<?php wp_footer(); ?>
</body>
</html>
