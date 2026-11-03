<?php
/**
 * BlankSlate Entry Dispatcher Template Part
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( is_singular() ? 'entry-singular bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-7 md:p-10' : 'entry-summary-card bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-md transition-all flex flex-col justify-between group' ); ?>>
	<header class="entry-header">
		<?php if ( is_singular() ) : ?>
			<h1 class="entry-title font-display font-black text-2xl sm:text-3xl md:text-4xl text-slate-900 leading-tight tracking-tight mb-4" itemprop="headline"><?php the_title(); ?></h1>
		<?php else : ?>
			<h2 class="entry-title font-bold text-slate-800 text-sm leading-snug hover:text-brand-primary transition-colors line-clamp-2 min-h-[36px]">
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h2>
		<?php endif; ?>
		<?php edit_post_link( __( 'Chỉnh sửa', 'ltdh' ), '<span class="edit-link text-xs text-blue-600 hover:underline">', '</span>' ); ?>
		<?php if ( ! is_search() ) : ?>
			<?php get_template_part( 'template-parts/entry', 'meta' ); ?>
		<?php endif; ?>
	</header>

	<?php
	$content_type = ( is_front_page() || is_home() || is_archive() || is_search() ) ? 'summary' : 'content';
	get_template_part( 'template-parts/entry', $content_type );
	?>

	<?php if ( is_singular() ) : ?>
		<?php get_template_part( 'template-parts/entry', 'footer' ); ?>
	<?php endif; ?>
</article>
