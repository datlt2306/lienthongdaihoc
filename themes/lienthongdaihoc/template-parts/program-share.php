<?php
/**
 * Template part: Program Social & Direct Sharing Component (Dropdown Popover)
 *
 * Provides a prominent Share Trigger button in the sticky navigation bar
 * with a high-performance, accessible dropdown popover containing Facebook,
 * Zalo, Copy Link, and Native Device Share API options.
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id    = ! empty( $args['program_id'] ) ? (int) $args['program_id'] : get_the_ID();
$program_url   = get_permalink( $program_id );
$program_title = get_the_title( $program_id );

// Retrieve school and major for enhanced contextual sharing
$school_id   = (int) ( get_field( 'school_relationship', $program_id ) ?: get_post_meta( $program_id, 'school_relationship', true ) );
$major_id    = (int) ( get_field( 'major_relationship', $program_id ) ?: get_post_meta( $program_id, 'major_relationship', true ) );
$school_name = $school_id ? get_the_title( $school_id ) : '';
$major_name  = $major_id ? get_the_title( $major_id ) : '';

$clean_title = function_exists( 'ltdh_get_program_banner_title' )
	? ltdh_get_program_banner_title( $program_id )
	: ( $program_title ?: 'Chương trình Liên thông Đại học' );

// Share the exact canonical URL of this program
$social_share_url = apply_filters( 'ltdh_social_share_url', $program_url, $program_id );
$fb_share_url     = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $social_share_url );
$zalo_share_url   = 'https://sp.zalo.me/plugins/share?url=' . rawurlencode( $social_share_url );
?>

<div id="share-chuong-trinh" class="relative inline-block text-left ltdh-program-share-section" data-program-id="<?php echo esc_attr( $program_id ); ?>" aria-labelledby="share-program-heading">
	
	<!-- PROMINENT SHARE BUTTON (Eye-catching brand styling with gradient & shadow) -->
	<button type="button"
	        id="ltdh-quick-share-btn"
	        class="ltdh-quick-share-btn ltdh-program-share-toggle-btn group inline-flex items-center gap-1.5 sm:gap-2 px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-xl bg-gradient-to-r from-[#00308b] via-[#003ca6] to-[#004dc7] hover:from-[#002266] hover:to-[#00308b] text-white text-xs sm:text-[13px] font-bold shadow-md shadow-blue-900/25 hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 active:scale-95 transition-all duration-200 border border-blue-400/30 cursor-pointer select-none"
	        data-has-dropdown="true"
	        data-url="<?php echo esc_url( $program_url ); ?>"
	        data-title="<?php echo esc_attr( $clean_title ); ?>"
	        data-type="program"
	        aria-expanded="false"
	        aria-haspopup="true"
	        aria-controls="ltdh-share-dropdown-menu"
	        onclick="if(window.ltdhToggleProgramShareDropdown){window.ltdhToggleProgramShareDropdown(this, event);}"
	        title="Chia sẻ chương trình đào tạo này">
		<span class="w-5 h-5 rounded-md bg-white/20 flex items-center justify-center shrink-0 text-white shadow-2xs">
			<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
			</svg>
		</span>
		<span class="tracking-wide">Chia sẻ</span>
		<svg class="w-3.5 h-3.5 text-white/80 transition-transform duration-200 group-aria-expanded:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
		</svg>
	</button>

	<!-- DROPDOWN POPOVER MENU -->
	<div id="ltdh-share-dropdown-menu"
	     class="hidden absolute right-0 top-full mt-2.5 w-72 sm:w-80 bg-white rounded-2xl shadow-2xl border border-slate-200/90 p-2 z-50 transition-all duration-150 origin-top-right focus:outline-none"
	     role="menu"
	     aria-orientation="vertical"
	     aria-labelledby="ltdh-quick-share-btn">
		
		<!-- Dropdown Header -->
		<div class="px-3 py-2 border-b border-slate-100 mb-1">
			<h3 id="share-program-heading" class="text-xs font-bold text-slate-900 leading-tight">Chia sẻ chương trình đào tạo</h3>
			<p class="text-[11px] text-slate-500 mt-0.5 truncate"><?php echo esc_html( $clean_title ); ?></p>
		</div>

		<!-- Share Actions List -->
		<div class="space-y-1">
			
			<!-- 1. Copy Link Option -->
			<button type="button"
			        id="ltdh-copy-program-btn"
			        data-url="<?php echo esc_url( $program_url ); ?>"
			        data-type="program"
			        onclick="if(window.ltdhCopyProgramLink){window.ltdhCopyProgramLink(this);}else if(window.ltdhCopyPostLink){window.ltdhCopyPostLink(this);}"
			        class="ltdh-share-btn ltdh-copy-program-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 text-left transition-colors cursor-pointer group"
			        role="menuitem"
			        title="Sao chép liên kết chương trình">
				<span class="w-9 h-9 rounded-xl bg-blue-50 text-[#00308b] flex items-center justify-center shrink-0 border border-blue-100/60 group-hover:scale-105 transition-transform shadow-2xs">
					<svg class="w-4 h-4 text-[#00308b] ltdh-copy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
					</svg>
				</span>
				<div class="flex-1 min-w-0">
					<span class="block text-xs sm:text-sm font-bold text-slate-800 ltdh-copy-text">Sao chép liên kết</span>
					<span class="block text-[11px] text-slate-500">Copy URL gửi qua Zalo, Messenger</span>
				</div>
			</button>

			<!-- 2. Facebook Share Option -->
			<a href="<?php echo esc_url( $fb_share_url ); ?>"
			   target="_blank"
			   rel="noopener noreferrer"
			   data-share-type="facebook"
			   data-url="<?php echo esc_url( $program_url ); ?>"
			   onclick="if(window.ltdhOpenFacebookShare){return window.ltdhOpenFacebookShare(event, this);}else if(window.ltdhOpenSharePopup){return window.ltdhOpenSharePopup(event, this.href, 'Facebook');}"
			   class="ltdh-share-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 text-left transition-colors cursor-pointer group no-underline"
			   role="menuitem"
			   title="Chia sẻ lên Facebook">
				<span class="w-9 h-9 rounded-xl bg-[#1877F2]/10 text-[#1877F2] flex items-center justify-center shrink-0 border border-[#1877F2]/20 group-hover:scale-105 transition-transform shadow-2xs">
					<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
					</svg>
				</span>
				<div class="flex-1 min-w-0">
					<span class="block text-xs sm:text-sm font-bold text-slate-800">Chia sẻ Facebook</span>
					<span class="block text-[11px] text-slate-500">Đăng lên trang cá nhân hoặc nhóm</span>
				</div>
			</a>

			<!-- 3. Zalo Share Option -->
			<a href="<?php echo esc_url( $zalo_share_url ); ?>"
			   target="_blank"
			   rel="noopener noreferrer"
			   data-share-type="zalo"
			   onclick="if(window.ltdhOpenSharePopup){return window.ltdhOpenSharePopup(event, this.href, 'Zalo');}"
			   class="ltdh-share-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 text-left transition-colors cursor-pointer group no-underline"
			   role="menuitem"
			   title="Chia sẻ qua Zalo">
				<span class="w-9 h-9 rounded-xl bg-[#0068FF]/10 text-[#0068FF] flex items-center justify-center shrink-0 border border-[#0068FF]/20 group-hover:scale-105 transition-transform shadow-2xs font-black text-xs">
					Zalo
				</span>
				<div class="flex-1 min-w-0">
					<span class="block text-xs sm:text-sm font-bold text-slate-800">Chia sẻ qua Zalo</span>
					<span class="block text-[11px] text-slate-500">Gửi trực tiếp cho bạn bè, nhóm Zalo</span>
				</div>
			</a>

			<!-- 4. Native Device Share (Web Share API for iOS / Android / macOS Safari) -->
			<button type="button"
			        id="ltdh-native-program-share-btn"
			        data-title="<?php echo esc_attr( $clean_title ); ?>"
			        data-url="<?php echo esc_url( $program_url ); ?>"
			        data-type="program"
			        class="ltdh-share-btn ltdh-native-share-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 text-left transition-colors cursor-pointer group"
			        role="menuitem"
			        title="Mở menu chia sẻ hệ thống">
				<span class="w-9 h-9 rounded-xl bg-orange-50 text-brand-accent flex items-center justify-center shrink-0 border border-orange-100 group-hover:scale-105 transition-transform shadow-2xs">
					<svg class="w-4 h-4 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
					</svg>
				</span>
				<div class="flex-1 min-w-0">
					<span class="block text-xs sm:text-sm font-bold text-slate-800">Chia sẻ qua ứng dụng khác</span>
					<span class="block text-[11px] text-slate-500">AirDrop, Tin nhắn, Menu hệ thống</span>
				</div>
			</button>

		</div>

	</div>

</div>

<!-- Floating Toast Notification for Feedback -->
<?php if ( ! did_action( 'ltdh_share_toast_rendered' ) ) : 
	do_action( 'ltdh_share_toast_rendered' );
?>
<div id="ltdh-share-toast"
     class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 bg-slate-900/95 text-white text-xs md:text-sm font-bold rounded-2xl shadow-2xl border border-slate-700/80 backdrop-blur-md transition-all duration-300 opacity-0 translate-y-4 pointer-events-none flex items-center gap-2"
     role="status"
     aria-live="polite">
	<svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
	</svg>
	<span id="ltdh-share-toast-msg">Đã sao chép liên kết vào bộ nhớ tạm!</span>
</div>
<?php endif; ?>

<script data-no-defer="1">
function ltdhToggleProgramShareDropdown(btn, e) {
	if (e) {
		e.preventDefault();
		e.stopPropagation();
	}
	var menu = document.getElementById('ltdh-share-dropdown-menu');
	if (!menu || !btn) return;
	var isHidden = menu.classList.contains('hidden');
	if (isHidden) {
		menu.classList.remove('hidden');
		btn.setAttribute('aria-expanded', 'true');
	} else {
		menu.classList.add('hidden');
		btn.setAttribute('aria-expanded', 'false');
	}
}

document.addEventListener('click', function(e) {
	var menu = document.getElementById('ltdh-share-dropdown-menu');
	var btn = document.getElementById('ltdh-quick-share-btn');
	if (!menu || !btn) return;
	if (!menu.contains(e.target) && !btn.contains(e.target)) {
		menu.classList.add('hidden');
		btn.setAttribute('aria-expanded', 'false');
	}
});

document.addEventListener('keydown', function(e) {
	if (e.key === 'Escape') {
		var menu = document.getElementById('ltdh-share-dropdown-menu');
		var btn = document.getElementById('ltdh-quick-share-btn');
		if (menu && !menu.classList.contains('hidden')) {
			menu.classList.add('hidden');
			if (btn) {
				btn.setAttribute('aria-expanded', 'false');
				btn.focus();
			}
		}
	}
});
</script>
