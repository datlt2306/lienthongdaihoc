<?php
/**
 * Template part: Post Social & Direct Sharing Component
 *
 * Provides Facebook, Zalo, Native Mobile Web Share API,
 * and robust Copy Link with dual-mode clipboard support and toast feedback.
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id    = get_the_ID();
$post_url   = get_permalink( $post_id );
$post_title = get_the_title( $post_id );

// Share the exact canonical URL of this post
$social_share_url = apply_filters( 'ltdh_social_share_url', $post_url, $post_id );

$fb_share_url   = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $social_share_url );
?>

<div class="mt-8 pt-6 border-t border-slate-100 ltdh-post-share-section" data-post-id="<?php echo esc_attr( $post_id ); ?>">
	<div class="flex items-center justify-between mb-3.5">
		<p class="text-xs md:text-sm font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
			<svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
			</svg>
			<span>Chia sẻ bài viết</span>
		</p>
		<span class="text-[11px] font-semibold text-slate-400 hidden sm:inline-block">Lan tỏa kiến thức đến bạn bè</span>
	</div>

	<div class="flex flex-wrap items-center gap-2.5">
		<!-- Facebook Share Button -->
		<a href="<?php echo esc_url( $fb_share_url ); ?>"
		   target="_blank"
		   rel="noopener noreferrer"
		   data-share-type="facebook"
		   data-url="<?php echo esc_url( $post_url ); ?>"
		   onclick="if(window.ltdhOpenFacebookShare){return window.ltdhOpenFacebookShare(event, this);}else if(window.ltdhOpenSharePopup){return window.ltdhOpenSharePopup(event, this.href, 'Facebook');}"
		   class="ltdh-share-btn inline-flex items-center gap-2 px-3.5 py-2 bg-[#1877F2] hover:bg-[#166FE5] text-white text-xs md:text-sm font-bold rounded-xl shadow-2xs hover:shadow-xs active:scale-95 transition-all select-none cursor-pointer"
		   title="Chia sẻ lên Facebook">
			<svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
				<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
			</svg>
			<span>Facebook</span>
		</a>

		<!-- Copy Link Button with Dual Fallback & Visual Feedback -->
		<button type="button"
		        id="ltdh-copy-post-btn"
		        data-url="<?php echo esc_url( $post_url ); ?>"
		        onclick="if(window.ltdhCopyPostLink){window.ltdhCopyPostLink(this);}"
		        class="ltdh-share-btn inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs md:text-sm font-bold rounded-xl border border-slate-200/90 hover:border-slate-300 shadow-2xs active:scale-95 transition-all cursor-pointer select-none"
		        title="Sao chép liên kết bài viết">
			<svg class="w-4 h-4 text-slate-500 shrink-0 ltdh-copy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
			</svg>
			<span class="ltdh-copy-text">Sao chép link</span>
		</button>

		<!-- Native Share (Web Share API for iOS / Android / macOS Safari) -->
		<button type="button"
		        id="ltdh-native-share-btn"
		        data-title="<?php echo esc_attr( $post_title ); ?>"
		        data-url="<?php echo esc_url( $post_url ); ?>"
		        class="ltdh-share-btn hidden inline-flex items-center gap-2 px-3.5 py-2 bg-blue-50/90 hover:bg-blue-100 text-[#00308b] text-xs md:text-sm font-bold rounded-xl border border-blue-200/80 active:scale-95 transition-all cursor-pointer select-none"
		        title="Mở menu chia sẻ hệ thống">
			<svg class="w-4 h-4 text-[#00308b] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
			</svg>
			<span>Chia sẻ khác</span>
		</button>
	</div>
</div>

<!-- Floating Toast Notification for Feedback -->
<div id="ltdh-share-toast"
     class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 bg-slate-900/95 text-white text-xs md:text-sm font-bold rounded-2xl shadow-2xl border border-slate-700/80 backdrop-blur-md transition-all duration-300 opacity-0 translate-y-4 pointer-events-none flex items-center gap-2"
     role="status"
     aria-live="polite">
	<svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
	</svg>
	<span id="ltdh-share-toast-msg">Đã sao chép liên kết vào bộ nhớ tạm!</span>
</div>
