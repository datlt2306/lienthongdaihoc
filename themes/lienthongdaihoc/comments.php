<?php
/**
 * The template for displaying comments
 *
 * Provides an anti-slop, Cupertino-inspired comment section with:
 * 1. Borderless, breathable top-level discussion items.
 * 2. Visual hierarchy for replies with left connector line and official feedback styling.
 * 3. Smart dynamic avatar system (LTDH verified badge for Admin, vibrant initials for users).
 * 4. In-place Frontend Quick Reply controller for Admins and visitors.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}

$comments_number = get_comments_number();
?>

<div id="comments" class="comments-area mt-8 pt-6 border-t border-slate-100">

	<?php if ( have_comments() ) : ?>
		<div class="comments-header flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
			<h3 class="comments-title text-base sm:text-lg font-display font-extrabold text-slate-900 flex items-center gap-2">
				<span>💬 Bình luận & Thảo luận</span>
				<span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-50 text-brand-primary font-bold">
					<?php echo esc_html( number_format_i18n( $comments_number ) ); ?>
				</span>
			</h3>
		</div>

		<ol class="comment-list list-none pl-0 space-y-4 mb-8">
			<?php
			wp_list_comments( [
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 40,
				'callback'    => function( $comment, $args, $depth ) {
					$GLOBALS['comment'] = $comment;

					$is_reply = $depth > 1 || (int) $comment->comment_parent > 0;

					// Check if author is Administrator or Editor (Ban Quản Trị)
					$is_author_admin = false;
					if ( ! empty( $comment->user_id ) ) {
						$comment_user = get_userdata( $comment->user_id );
						if ( $comment_user && ( in_array( 'administrator', (array) $comment_user->roles, true ) || in_array( 'editor', (array) $comment_user->roles, true ) ) ) {
							$is_author_admin = true;
						}
					}
					if ( ! $is_author_admin && ( stripos( $comment->comment_author, 'Ban Quản Trị' ) !== false || stripos( $comment->comment_author, 'Admin' ) !== false ) ) {
						$is_author_admin = true;
					}

					$is_current_user_admin = current_user_can( 'moderate_comments' ) || current_user_can( 'manage_options' );
					$comment_id            = (int) get_comment_ID();
					$author_name           = get_comment_author();
					$author_js             = esc_js( $author_name );

					// Classes for top-level vs child/reply comment
					$item_classes = 'comment-item transition-all ';
					if ( $is_reply ) {
						if ( $is_author_admin ) {
							$item_classes .= 'p-4 sm:p-5 rounded-2xl bg-blue-50/60 border border-blue-200/70 shadow-2xs mt-3';
						} else {
							$item_classes .= 'p-3.5 sm:p-4 rounded-2xl bg-slate-50/80 border border-slate-100 shadow-2xs mt-3';
						}
					} else {
						$item_classes .= 'pb-5 border-b border-slate-100/90 last:border-b-0';
					}
					?>
					<li <?php comment_class( $item_classes ); ?> id="comment-<?php echo esc_attr( $comment_id ); ?>">
						<article class="comment-body">

							<?php if ( $is_author_admin && $is_reply ) : ?>
								<!-- Official Feedback Tag -->
								<div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#0071E3]/10 text-[#0071E3] border border-[#0071E3]/20 mb-2.5">
									<span>🛡️ Phản hồi từ Ban Quản Trị</span>
								</div>
							<?php endif; ?>

							<footer class="comment-meta flex items-start gap-3">
								<div class="comment-author-avatar shrink-0">
									<?php
									if ( function_exists( 'ltdh_get_comment_avatar_html' ) ) {
										echo ltdh_get_comment_avatar_html( $comment, 40 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									} else {
										echo get_avatar( $comment, 40, '', '', [ 'class' => 'rounded-full' ] );
									}
									?>
								</div>

								<div class="comment-metadata min-w-0 flex-1 leading-tight">
									<div class="flex items-center gap-1.5 flex-wrap">
										<strong class="comment-author-name text-xs sm:text-sm font-bold text-slate-900 truncate">
											<?php echo esc_html( $author_name ); ?>
										</strong>

										<?php if ( $is_author_admin ) : ?>
											<span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#0071E3] text-white select-none">BQT</span>
											<svg class="w-3.5 h-3.5 text-[#0071E3] shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
											</svg>
										<?php endif; ?>
									</div>

									<time class="comment-date text-[11px] text-slate-400 block mt-1" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>">
										<?php echo esc_html( get_comment_date( 'd/m/Y \l\ú\c H:i' ) ); ?>
									</time>
								</div>
							</footer>

							<div class="comment-content text-xs sm:text-sm text-slate-700 leading-relaxed mt-2.5 <?php echo $is_reply ? '' : 'pl-13 sm:pl-13'; ?>">
								<?php if ( '0' === $comment->comment_approved ) : ?>
									<p class="text-xs text-amber-700 italic bg-amber-50 border border-amber-200/70 p-2.5 rounded-xl mb-2">
										⏳ Bình luận của bạn đang được ban quản trị xét duyệt trước khi hiển thị công khai.
									</p>
								<?php endif; ?>

								<div class="prose prose-xs sm:prose-sm max-w-none text-slate-700 break-words">
									<?php comment_text(); ?>
								</div>

								<!-- Comment Action Buttons (Quick Reply) -->
								<?php if ( comments_open() ) : ?>
									<div class="comment-actions mt-2.5 flex items-center gap-2">
										<?php if ( $is_current_user_admin ) : ?>
											<button type="button"
													onclick="window.ltdhQuickReply(<?php echo (int) $comment_id; ?>, '<?php echo $author_js; ?>', true)"
													class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0071E3] text-xs font-bold transition-all cursor-pointer">
												<span>⚡ Trả lời nhanh</span>
											</button>
										<?php else : ?>
											<button type="button"
													onclick="window.ltdhQuickReply(<?php echo (int) $comment_id; ?>, '<?php echo $author_js; ?>', false)"
													class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 text-xs font-semibold transition-all cursor-pointer">
												<span>💬 Trả lời</span>
											</button>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<!-- Slot for moving the reply form directly here -->
								<div id="ltdh-reply-slot-<?php echo (int) $comment_id; ?>" class="ltdh-reply-slot mt-3"></div>
							</div>
						</article>
					<?php
					// Note: Do NOT output </li> here; WordPress walker handles closing after children.
				},
			] );
			?>
		</ol>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<nav class="comment-navigation flex items-center justify-between py-3 mb-6 border-t border-slate-100 text-xs sm:text-sm font-semibold" role="navigation" aria-label="<?php esc_attr_e( 'Điều hướng bình luận', 'ltdh' ); ?>">
				<div class="nav-previous text-slate-600 hover:text-brand-primary transition-colors">
					<?php previous_comments_link( __( '← Bình luận cũ hơn', 'ltdh' ) ); ?>
				</div>
				<div class="nav-next text-slate-600 hover:text-brand-primary transition-colors">
					<?php next_comments_link( __( 'Bình luận mới hơn →', 'ltdh' ) ); ?>
				</div>
			</nav>
		<?php endif; ?>

	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="no-comments p-4 bg-slate-50 text-slate-500 text-xs sm:text-sm rounded-xl text-center border border-slate-200/60">
			<?php esc_html_e( 'Mục bình luận cho bài viết này đã được đóng.', 'ltdh' ); ?>
		</p>
	<?php endif; ?>

	<?php if ( comments_open() ) : ?>
		<div id="ltdh-default-respond-slot" class="comment-form-wrapper <?php echo have_comments() ? 'mt-8 pt-6 border-t border-slate-100' : ''; ?>">
			<?php
			if ( function_exists( 'ltdh_render_comment_form' ) ) {
				ltdh_render_comment_form();
			} else {
				comment_form();
			}
			?>
		</div>
	<?php endif; ?>

</div><!-- #comments -->

<script>
(function() {
	window.ltdhQuickReply = function(commentId, authorName, isAdmin) {
		var respond = document.getElementById('respond');
		var slot = document.getElementById('ltdh-reply-slot-' + commentId);
		var parentInput = document.getElementById('comment_parent');
		var commentField = document.getElementById('comment');

		if (!respond || !slot) {
			return;
		}

		// Move #respond into target comment slot
		slot.appendChild(respond);

		// Set comment parent hidden field
		if (parentInput) {
			parentInput.value = commentId;
		}

		// Remove existing notice if any
		var oldNotice = document.getElementById('ltdh-reply-notice');
		if (oldNotice) {
			oldNotice.remove();
		}

		// Create contextual reply notice banner
		var notice = document.createElement('div');
		notice.id = 'ltdh-reply-notice';
		notice.className = 'flex items-center justify-between p-3 mb-3 bg-blue-50 border border-blue-200/80 rounded-xl text-xs text-blue-900 animate-in fade-in duration-200';
		notice.innerHTML = '<span class="flex items-center gap-1.5 font-semibold">' +
			'<span>' + (isAdmin ? '⚡ Phản hồi nhanh cho:' : '💬 Đang trả lời:') + '</span>' +
			'<strong class="text-brand-primary">@' + authorName + '</strong>' +
			'</span>' +
			'<button type="button" onclick="window.ltdhCancelReply()" class="text-xs text-slate-500 hover:text-rose-600 font-bold px-2 py-0.5 rounded hover:bg-white transition-all cursor-pointer">✕ Hủy</button>';

		var form = respond.querySelector('form');
		if (form) {
			form.insertBefore(notice, form.firstChild);
		}

		// Focus and update placeholder
		if (commentField) {
			commentField.placeholder = isAdmin
				? 'Nhập phản hồi chính thức từ Ban Quản Trị LTDH cho @' + authorName + '...'
				: 'Nhập câu trả lời của bạn cho @' + authorName + '...';
			commentField.focus();
		}

		respond.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
	};

	window.ltdhCancelReply = function() {
		var respond = document.getElementById('respond');
		var defaultSlot = document.getElementById('ltdh-default-respond-slot');
		var parentInput = document.getElementById('comment_parent');
		var commentField = document.getElementById('comment');
		var notice = document.getElementById('ltdh-reply-notice');

		if (notice) {
			notice.remove();
		}

		if (parentInput) {
			parentInput.value = '0';
		}

		if (commentField) {
			commentField.placeholder = 'Nhập câu hỏi, thắc mắc hoặc thảo luận về bài viết...';
		}

		if (respond && defaultSlot) {
			defaultSlot.appendChild(respond);
			respond.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}
	};
})();
</script>
