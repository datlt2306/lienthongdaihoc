<?php
/**
 * BlankSlate Archive Excerpt Template Part
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="entry-summary">
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="block mb-3 aspect-[16/9] overflow-hidden rounded-lg bg-slate-100 group">
			<?php the_post_thumbnail( 'medium', [ 'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300' ] ); ?>
		</a>
	<?php endif; ?>
	<div itemprop="description" class="text-sm text-slate-600 line-clamp-2 leading-relaxed">
		<?php the_excerpt(); ?>
	</div>
	<?php if ( is_search() ) : ?>
		<div class="entry-links mt-2">
			<?php wp_link_pages(); ?>
		</div>
	<?php endif; ?>
</div>
