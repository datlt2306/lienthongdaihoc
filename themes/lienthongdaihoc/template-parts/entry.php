<?php
/**
 * BlankSlate Entry Dispatcher Template Part
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_singular() ) {
	get_template_part( 'template-parts/cards/card', 'post' );
	return;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-singular bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-7 md:p-10' ); ?>>
	<header class="entry-header mb-6">
		<h1 class="entry-title font-display font-black text-2xl sm:text-3xl md:text-4xl text-slate-900 leading-tight tracking-tight mb-4"><?php the_title(); ?></h1>
		<?php edit_post_link( __( 'Chỉnh sửa', 'ltdh' ), '<span class="edit-link text-xs text-blue-600 hover:underline">', '</span>' ); ?>
		<?php get_template_part( 'template-parts/entry', 'meta' ); ?>
	</header>

	<?php get_template_part( 'template-parts/entry', 'content' ); ?>

	<?php get_template_part( 'template-parts/entry', 'footer' ); ?>
</article>
