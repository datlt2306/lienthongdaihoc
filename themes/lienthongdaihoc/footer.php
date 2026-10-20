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

<!-- FLOATING DYNAMIC ISLAND MOBILE CTA BAR (APPLE iOS STYLE) -->
<nav id="ltdh-global-mobile-bar" 
     aria-label="Thanh liên hệ nhanh"
     class="fixed inset-x-3.5 max-w-md mx-auto z-40 bg-white/92 backdrop-blur-xl border border-white/80 shadow-[0_10px_35px_rgba(15,23,42,0.16),0_2px_8px_rgba(15,23,42,0.08)] p-1.5 rounded-full flex items-center justify-between gap-2 md:hidden transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] transform translate-y-0 opacity-100 will-change-transform"
     style="bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px));">
	
	<!-- Zalo Button (Left Circle) -->
	<a href="<?php echo esc_url( $zalo ); ?>" 
	   target="_blank" 
	   rel="noopener noreferrer" 
	   class="flex items-center justify-center h-10 w-10 bg-gradient-to-br from-[#0080ff] to-[#0052cc] hover:opacity-90 active:scale-90 rounded-full shrink-0 transition-all shadow-xs text-white" 
	   aria-label="Nhắn tin Zalo"
	   title="Nhắn tin Zalo">
		<span class="font-display font-black text-xs tracking-tight">Zalo</span>
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
	   class="js-open-consultation-modal flex-1 flex items-center justify-center gap-2 bg-gradient-to-r from-[#00308b] via-[#004bb5] to-[#00a2f4] hover:brightness-110 active:scale-[0.97] text-white font-bold text-xs sm:text-sm h-10 px-3.5 rounded-full shadow-md shadow-[#00308b]/20 transition-all min-w-0"
	   aria-haspopup="dialog">
		<span class="truncate font-display font-bold">Đăng ký tư vấn ngay</span>
	</a>

	<!-- Hotline Call Button (Right Circle) -->
	<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" 
	   class="flex items-center justify-center h-10 w-10 bg-gradient-to-br from-[#fe8100] to-[#ea580c] hover:opacity-90 active:scale-90 rounded-full shrink-0 transition-all shadow-xs" 
	   aria-label="Gọi hotline"
	   title="Gọi hotline">
		<svg class="w-5 h-5 fill-none stroke-current text-white" stroke-width="2.2" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
		</svg>
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


<!-- CONSULTATION POPUP MODAL (MOBILE & DESKTOP) -->
<?php
$modal_hidden = [
	'referral_source' => home_url( add_query_arg( [], $GLOBALS['wp']->request ?? '' ) ),
];
$modal_title    = 'Đăng ký nhận tư vấn tuyển sinh';
$modal_subtitle = 'Để lại thông tin, ban tư vấn sẽ liên hệ và giải đáp lộ trình cụ thể cho bạn trong 15 phút.';

if ( is_singular( 'program' ) ) {
	$cur_pid = get_the_ID();
	$modal_hidden['program_id']         = $cur_pid;
	$modal_hidden['current_program_id'] = $cur_pid;
	$modal_school_id = get_field( 'school_relationship', $cur_pid );
	if ( $modal_school_id ) {
		$sid = is_object( $modal_school_id ) ? $modal_school_id->ID : (int) $modal_school_id;
		$modal_hidden['school_id']         = $sid;
		$modal_hidden['current_school_id'] = $sid;
	}
	$modal_major_id = get_field( 'major_relationship', $cur_pid );
	if ( ! empty( $modal_major_id ) ) {
		$mid = is_object( $modal_major_id ) ? $modal_major_id->ID : ( is_array( $modal_major_id ) && ! empty( $modal_major_id[0] ) ? ( is_object( $modal_major_id[0] ) ? $modal_major_id[0]->ID : (int) $modal_major_id[0] ) : (int) $modal_major_id );
		$modal_hidden['major_id']         = $mid;
		$modal_hidden['current_major_id'] = $mid;
	}
	$modal_title    = 'Đăng ký tư vấn chương trình';
	$modal_subtitle = get_the_title( $cur_pid );
} elseif ( is_singular( 'school' ) ) {
	$cur_sid = get_the_ID();
	$modal_hidden['school_id']         = $cur_sid;
	$modal_hidden['current_school_id'] = $cur_sid;
	$modal_title    = 'Đăng ký vào ' . get_the_title( $cur_sid );
	$modal_subtitle = 'Nhận tư vấn hồ sơ và hỗ trợ xử lý thủ tục nhập học nhanh chóng.';
} elseif ( is_singular( 'major' ) ) {
	$cur_mid = get_the_ID();
	$modal_hidden['major_id']         = $cur_mid;
	$modal_hidden['current_major_id'] = $cur_mid;
	$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );
	if ( ! empty( $school_param ) ) {
		if ( is_numeric( $school_param ) ) {
			$s_post = get_post( intval( $school_param ) );
		} else {
			$cpt_school = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
			$s_post     = get_page_by_path( sanitize_title( $school_param ), OBJECT, $cpt_school );
		}
		if ( $s_post && 'publish' === $s_post->post_status && ( defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' ) === $s_post->post_type ) {
			$modal_hidden['school_id']         = $s_post->ID;
			$modal_hidden['current_school_id'] = $s_post->ID;
		}
	}
	$modal_title    = 'Đăng ký tư vấn ngành ' . get_the_title( $cur_mid );
	$modal_subtitle = 'Ban tư vấn sẽ gửi danh sách trường và lộ trình học tối ưu cho bạn.';
}
?>
<div id="ltdh-consultation-modal" 
     class="fixed inset-0 z-[999] hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs items-center justify-center p-3 sm:p-5"
     role="dialog"
     aria-modal="true"
     aria-labelledby="ltdh-consultation-modal-title">
	<div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all my-auto">
		<!-- Modal Header -->
		<div class="bg-gradient-to-r from-[#00308b] via-[#004bb5] to-[#002266] px-5 py-4 text-white flex items-center justify-between">
			<div class="flex items-center gap-3 min-w-0">
				<div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-lg shrink-0 border border-white/20">
					🎓
				</div>
				<div class="min-w-0">
					<h3 id="ltdh-consultation-modal-title" class="font-extrabold text-sm sm:text-base leading-tight truncate">
						<?php echo esc_html( $modal_title ); ?>
					</h3>
					<p class="text-[11px] sm:text-xs text-blue-200 mt-0.5 truncate">
						<?php echo esc_html( $modal_subtitle ); ?>
					</p>
				</div>
			</div>
			<button type="button" 
			        onclick="window.ltdhCloseConsultationModal && window.ltdhCloseConsultationModal()" 
			        class="p-2 -mr-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 active:scale-95 transition-all cursor-pointer shrink-0" 
			        aria-label="Đóng popup">
				<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"></path></svg>
			</button>
		</div>

		<!-- Modal Body -->
		<div class="p-5 sm:p-6 max-h-[calc(90vh-80px)] overflow-y-auto">
			<?php
			if ( function_exists( 'ltdh_render_consultation_form' ) ) {
				ltdh_render_consultation_form( $modal_hidden );
			}
			?>
		</div>
	</div>
</div>

<script>
(function() {
	function openConsultationModal(e) {
		if (e && e.preventDefault) e.preventDefault();
		var modal = document.getElementById('ltdh-consultation-modal');
		if (modal) {
			modal.classList.remove('hidden');
			modal.classList.add('flex');
			document.body.style.overflow = 'hidden';
			var firstInput = modal.querySelector('input[name="your-name"], input[type="text"]:not([tabindex="-1"]), input[type="tel"]');
			if (firstInput) {
				setTimeout(function() { firstInput.focus(); }, 100);
			}
		}
	}

	function closeConsultationModal() {
		var modal = document.getElementById('ltdh-consultation-modal');
		if (modal) {
			modal.classList.add('hidden');
			modal.classList.remove('flex');
			document.body.style.overflow = '';
		}
	}

	window.ltdhOpenConsultationModal = openConsultationModal;
	window.ltdhCloseConsultationModal = closeConsultationModal;

	document.addEventListener('DOMContentLoaded', function() {
		// Attach to all elements marked with js-open-consultation-modal
		document.querySelectorAll('.js-open-consultation-modal, .ltdh-open-consultation-modal, [data-target="#ltdh-consultation-modal"]').forEach(function(el) {
			el.addEventListener('click', openConsultationModal);
		});

		// On mobile (< 1024px), intercept any clicks to #register anchors
		document.querySelectorAll('a[href="#register"], a[href="#register-section"]').forEach(function(el) {
			el.addEventListener('click', function(e) {
				if (window.innerWidth < 1024) {
					openConsultationModal(e);
				}
			});
		});

		// Close modal on backdrop click
		var modal = document.getElementById('ltdh-consultation-modal');
		if (modal) {
			modal.addEventListener('click', function(e) {
				if (e.target === modal) {
					closeConsultationModal();
				}
			});
		}

		// Close modal on Escape
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape') {
				closeConsultationModal();
			}
		});

		// If returned from successful form submission and on mobile, open modal to show success alert
		if (window.location.search.indexOf('submit_success=1') !== -1 && window.innerWidth < 1024) {
			var successBox = modal ? modal.querySelector('.text-green-800') : null;
			if (successBox) {
				openConsultationModal();
			}
		}
	});
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
