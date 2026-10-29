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

		</main><!-- #content -->
	</div><!-- #container -->

	<footer id="colophon" class="site-footer text-slate-400 pt-10 pb-28 md:py-16 border-t border-slate-800/80" role="contentinfo" style="background-color: #0d192d;">
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
									<?php ltdh_icon( 'phone.fill', 'w-4 h-4' ); ?>
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
								<?php ltdh_icon( 'location', 'w-4 h-4 text-[#00a2f4] shrink-0 mt-0.5' ); ?>
								<div class="min-w-0">
									<strong class="text-white block font-bold text-[11px] uppercase tracking-wider">Trụ sở Hà Nội</strong>
									<span class="text-slate-400"><?php echo esc_html( $address ); ?></span>
								</div>
							</li>
							<li class="flex items-start gap-2.5 p-2 rounded-lg bg-slate-800/20 border border-slate-800/40">
								<?php ltdh_icon( 'envelope', 'w-4 h-4 text-[#00a2f4] shrink-0 mt-0.5' ); ?>
								<div class="min-w-0">
									<strong class="text-white block font-bold text-[11px] uppercase tracking-wider">Email Tuyển sinh</strong>
									<!--email_off--><a href="mailto:<?php echo esc_attr( $email ); ?>" class="text-slate-400 hover:text-[#00a2f4] transition-colors truncate block"><?php echo esc_html( $email ); ?></a><!--/email_off-->
								</div>
							</li>
						</ul>
					</div>
				</div>

				<!-- Column 3: Training Programs -->
				<div>
					<h3 class="font-display font-extrabold text-white mb-4 md:mb-6 text-xs uppercase tracking-wider flex items-center gap-2">
						<span class="w-1.5 h-3.5 rounded-full bg-[#00a2f4]"></span>Chương trình đào tạo
					</h3>
					<ul class="grid grid-cols-2 gap-2 md:flex md:flex-col md:space-y-2.5">
						<li>
							<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/chinh-quy/' ) ); ?>" 
							   class="flex items-center justify-between p-2.5 md:p-0 rounded-xl md:rounded-none bg-slate-800/40 md:bg-transparent border border-slate-800/70 md:border-none hover:bg-slate-800 md:hover:bg-transparent text-slate-300 hover:text-white active:scale-[0.98] md:active:scale-100 transition-all min-h-[42px] md:min-h-0 group">
								<span class="text-xs font-bold md:font-semibold line-clamp-1">Liên thông Chính quy</span>
								<span class="text-[#00a2f4] text-xs font-bold md:hidden">›</span>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/dao-tao-tu-xa/' ) ); ?>" 
							   class="flex items-center justify-between p-2.5 md:p-0 rounded-xl md:rounded-none bg-slate-800/40 md:bg-transparent border border-slate-800/70 md:border-none hover:bg-slate-800 md:hover:bg-transparent text-slate-300 hover:text-white active:scale-[0.98] md:active:scale-100 transition-all min-h-[42px] md:min-h-0 group">
								<span class="text-xs font-bold md:font-semibold line-clamp-1">Liên thông Đại học Từ xa</span>
								<span class="text-[#00a2f4] text-xs font-bold md:hidden">›</span>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/vua-hoc-vua-lam/' ) ); ?>" 
							   class="flex items-center justify-between p-2.5 md:p-0 rounded-xl md:rounded-none bg-slate-800/40 md:bg-transparent border border-slate-800/70 md:border-none hover:bg-slate-800 md:hover:bg-transparent text-slate-300 hover:text-white active:scale-[0.98] md:active:scale-100 transition-all min-h-[42px] md:min-h-0 group">
								<span class="text-xs font-bold md:font-semibold line-clamp-1">Liên thông Vừa học vừa làm</span>
								<span class="text-[#00a2f4] text-xs font-bold md:hidden">›</span>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( home_url( '/truong-doi-tac/' ) ); ?>" 
							   class="flex items-center justify-between p-2.5 md:p-0 rounded-xl md:rounded-none bg-slate-800/40 md:bg-transparent border border-slate-800/70 md:border-none hover:bg-slate-800 md:hover:bg-transparent text-slate-300 hover:text-white active:scale-[0.98] md:active:scale-100 transition-all min-h-[42px] md:min-h-0 group">
								<span class="text-xs font-bold md:font-semibold line-clamp-1">Trường đối tác</span>
								<span class="text-[#00a2f4] text-xs font-bold md:hidden">›</span>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( home_url( '/nganh-hoc/' ) ); ?>" 
							   class="flex items-center justify-between p-2.5 md:p-0 rounded-xl md:rounded-none bg-slate-800/40 md:bg-transparent border border-slate-800/70 md:border-none hover:bg-slate-800 md:hover:bg-transparent text-slate-300 hover:text-white active:scale-[0.98] md:active:scale-100 transition-all min-h-[42px] md:min-h-0 group">
								<span class="text-xs font-bold md:font-semibold line-clamp-1">Ngành học liên thông</span>
								<span class="text-[#00a2f4] text-xs font-bold md:hidden">›</span>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( home_url( '/kiem-tra-dieu-kien/' ) ); ?>" 
							   class="flex items-center justify-between p-2.5 md:p-0 rounded-xl md:rounded-none bg-slate-800/40 md:bg-transparent border border-slate-800/70 md:border-none hover:bg-slate-800 md:hover:bg-transparent text-slate-300 hover:text-white active:scale-[0.98] md:active:scale-100 transition-all min-h-[42px] md:min-h-0 group">
								<span class="text-xs font-bold md:font-semibold line-clamp-1 text-orange-400">Kiểm tra điều kiện</span>
								<span class="text-orange-400 text-xs font-bold md:hidden">›</span>
							</a>
						</li>
					</ul>
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
							<?php ltdh_icon( 'arrow-right', 'w-3.5 h-3.5' ); ?>
						</a>
					</div>
					<div class="mt-3 flex justify-center">
						<a href="//www.dmca.com/Protection/Status.aspx?ID=3639cda6-dc5f-4f9a-ada9-7f14ca3a390d" title="DMCA.com Protection Status" class="dmca-badge" rel="noopener noreferrer" target="_blank"><img src="https://images.dmca.com/Badges/dmca_protected_sml_120d.png?ID=3639cda6-dc5f-4f9a-ada9-7f14ca3a390d" alt="DMCA.com Protection Status" width="120" height="28" loading="lazy" decoding="async" /></a>
					</div>
					<script src="https://images.dmca.com/Badges/DMCABadgeHelper.min.js"></script>

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
</div><!-- #wrapper -->

<!-- Floating/Sticky CTAs for Desktop/Tablet (Apple Squircle Style) -->
<div class="hidden md:flex fixed bottom-4 left-4 z-40 flex-col gap-2.5 md:bottom-6 md:left-6">
	<!-- Phone Hotline Float -->
	<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex h-11 w-11 items-center justify-center rounded-full bg-[#FF9500] hover:bg-[#E08500] text-white shadow-[0_4px_16px_rgba(255,149,0,0.3)] transition-all hover:scale-105 active:scale-95" aria-label="Gọi ngay">
		<?php ltdh_icon( 'phone.fill', 'h-5 w-5' ); ?>
	</a>

	<!-- Zalo OA Float -->
	<a href="<?php echo esc_url( $zalo ); ?>" target="_blank" rel="noopener noreferrer" class="flex h-11 w-11 items-center justify-center rounded-full bg-[#0071E3] hover:bg-[#0077ED] text-white shadow-[0_4px_16px_rgba(0,113,227,0.3)] transition-all hover:scale-105 active:scale-95" aria-label="Chat Zalo">
		<span class="font-display font-bold text-xs">Za</span>
	</a>
</div>

<!-- FLOATING DYNAMIC ISLAND MOBILE CTA BAR (CUPERTINO FROSTED GLASS) -->
<nav id="ltdh-global-mobile-bar" 
     aria-label="Thanh liên hệ nhanh"
     class="fixed inset-x-3.5 max-w-md mx-auto z-40 bg-[#1D1D1F]/90 backdrop-blur-2xl border border-white/15 shadow-[0_8px_32px_rgba(0,0,0,0.35)] p-1.5 rounded-full flex items-center justify-between gap-2 md:hidden transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] transform translate-y-0 opacity-100 will-change-transform"
     style="bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px));">
	
	<!-- Zalo Button (Left Circle) -->
	<a href="<?php echo esc_url( $zalo ); ?>" 
	   target="_blank" 
	   rel="noopener noreferrer" 
	   class="flex items-center justify-center h-10 w-10 bg-[#0071E3] hover:bg-[#0077ED] active:scale-90 rounded-full shrink-0 transition-all text-white" 
	   aria-label="Nhắn tin Zalo"
	   title="Nhắn tin Zalo">
		<span class="font-display font-bold text-xs tracking-tight">Za</span>
	</a>

	<!-- Register Consultation Button (Center Pill) -->
	<?php
	$register_link = '#register';
	if ( is_front_page() || is_home() ) {
		$register_link = '#register-section';
	} elseif ( ! is_singular( [ 'major', 'program', 'school' ] ) ) {
		$register_link = home_url( '/lien-he/' );
	}
	?>
	<a href="<?php echo esc_url( $register_link ); ?>" 
	   class="js-open-consultation-modal flex-1 flex items-center justify-center gap-2 bg-[#0071E3] hover:bg-[#0077ED] active:scale-[0.97] text-white font-medium text-xs sm:text-sm h-10 px-4 rounded-full shadow-none transition-all min-w-0"
	   aria-haspopup="dialog">
		<span class="truncate font-display font-medium">Đăng ký tư vấn ngay</span>
	</a>

	<!-- Hotline Call Button (Right Circle) -->
	<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" 
	   class="flex items-center justify-center h-10 w-10 bg-[#FF9500] hover:bg-[#E08500] active:scale-90 rounded-full shrink-0 transition-all text-white" 
	   aria-label="Gọi hotline"
	   title="Gọi hotline">
		<?php ltdh_icon( 'phone.fill', 'w-4.5 h-4.5' ); ?>
	</a>
</nav>

<style>
@media (max-width: 767px) {
  body {
    padding-bottom: calc(3.5rem + env(safe-area-inset-bottom, 0px)) !important;
  }
}
</style>

<script>
(function() {
	var bar = document.getElementById('ltdh-global-mobile-bar');
	if (!bar || bar._scrollInit) return;
	bar._scrollInit = true;

	var lastScrollY = window.pageYOffset || document.documentElement.scrollTop;
	var ticking = false;
	var threshold = 8;
	var isHidden = false;

	function updateBar() {
		var currentScrollY = window.pageYOffset || document.documentElement.scrollTop;
		var windowHeight = window.innerHeight;
		var docHeight = document.documentElement.scrollHeight;
		var diff = currentScrollY - lastScrollY;

		if (currentScrollY < 60) {
			if (isHidden) showBar();
		} else if (currentScrollY + windowHeight >= docHeight - 80) {
			if (isHidden) showBar();
		} else if (diff > threshold && !isHidden) {
			hideBar();
		} else if (diff < -threshold && isHidden) {
			showBar();
		}

		lastScrollY = currentScrollY <= 0 ? 0 : currentScrollY;
		ticking = false;
	}

	function hideBar() {
		bar.classList.add('translate-y-[150%]', 'opacity-0', 'pointer-events-none');
		bar.classList.remove('translate-y-0', 'opacity-100');
		isHidden = true;
	}

	function showBar() {
		bar.classList.remove('translate-y-[150%]', 'opacity-0', 'pointer-events-none');
		bar.classList.add('translate-y-0', 'opacity-100');
		isHidden = false;
	}

	window.addEventListener('scroll', function() {
		if (!ticking) {
			window.requestAnimationFrame(updateBar);
			ticking = true;
		}
	}, { passive: true });

	document.addEventListener('touchstart', function(e) {
		if (isHidden && e.touches && e.touches[0].clientY > window.innerHeight - 70) {
			showBar();
		}
	}, { passive: true });
})();
</script>


<?php
get_template_part( 'template-parts/modal-admissions' );
get_template_part( 'template-parts/modal-compare' );
?>
<?php wp_footer(); ?>
</body>
</html>
