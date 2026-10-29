<?php
/**
 * BlankSlate Entry Footer (Categories, Tags, Comment Anchors)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer class="entry-footer mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500 flex flex-wrap items-center gap-3">
	<span class="cat-links">
		<strong class="font-bold text-slate-600"><?php esc_html_e( 'Chuyên mục: ', 'ltdh' ); ?></strong>
		<?php the_category( ', ' ); ?>
	</span>
	<?php $tags = get_the_tags(); if ( $tags ) : ?>
		<span class="tag-links">
			<strong class="font-bold text-slate-600"><?php esc_html_e( 'Thẻ: ', 'ltdh' ); ?></strong>
			<?php the_tags( '', ', ', '' ); ?>
		</span>
	<?php endif; ?>
	<?php if ( comments_open() ) : ?>
		<span class="comments-link ml-auto">
			<a href="<?php echo esc_url( get_comments_link() ); ?>" class="hover:underline">
				<?php comments_number( __( 'Chưa có bình luận', 'ltdh' ), __( '1 bình luận', 'ltdh' ), __( '% bình luận', 'ltdh' ) ); ?>
			</a>
		</span>
	<?php endif; ?>
</footer>
