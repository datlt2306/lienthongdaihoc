<?php
/**
 * BlankSlate Singular Content Template Part
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="entry-content">
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="entry-thumbnail mb-6">
			<?php the_post_thumbnail( 'full', [ 'class' => 'w-full h-auto rounded-xl shadow-sm' ] ); ?>
		</div>
	<?php endif; ?>
	<div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-[15px]">
		<?php the_content(); ?>
	</div>
	<div class="entry-links mt-6 pt-4 border-t border-slate-100">
		<?php wp_link_pages( [ 'before' => '<div class="page-links"><span class="page-links-title">' . __( 'Trang:', 'ltdh' ) . '</span>', 'after' => '</div>' ] ); ?>
	</div>
</div>
