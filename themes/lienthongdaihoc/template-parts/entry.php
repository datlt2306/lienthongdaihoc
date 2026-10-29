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
<article id="post-<?php the_ID(); ?>" <?php post_class( is_singular() ? 'entry-singular' : 'entry-summary-card' ); ?>>
	<header class="entry-header">
		<?php if ( is_singular() ) : ?>
			<h1 class="entry-title" itemprop="headline"><?php the_title(); ?></h1>
		<?php else : ?>
			<h2 class="entry-title">
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h2>
		<?php endif; ?>
		<?php edit_post_link( __( 'Chỉnh sửa', 'ltdh' ), '<span class="edit-link">', '</span>' ); ?>
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
