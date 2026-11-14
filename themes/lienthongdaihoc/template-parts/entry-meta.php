<?php
/**
 * BlankSlate Entry Metadata (Author & Schema.org Dates)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="entry-meta flex items-center gap-2 text-xs text-slate-400 py-1">
	<span class="author vcard">
		<span class="font-medium text-slate-600"><?php the_author_posts_link(); ?></span>
	</span>
	<span class="meta-sep text-slate-300">•</span>
	<time class="entry-date font-normal" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time>
</div>
