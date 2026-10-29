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
	<span class="author vcard" itemprop="author" itemscope itemtype="https://schema.org/Person">
		<span itemprop="name" class="font-medium text-slate-600"><?php the_author_posts_link(); ?></span>
	</span>
	<span class="meta-sep text-slate-300">•</span>
	<time class="entry-date font-normal" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" itemprop="datePublished"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time>
	<?php if ( get_the_date() !== get_the_modified_date() ) : ?>
		<meta itemprop="dateModified" content="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>">
	<?php endif; ?>
</div>
