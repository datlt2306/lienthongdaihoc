<?php
/**
 * Default Page Template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) : the_post();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-entry max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8' ); ?>>
	<header class="header mb-6">
		<h1 class="entry-title text-2xl sm:text-3xl font-extrabold text-slate-900" itemprop="headline"><?php the_title(); ?></h1>
		<?php edit_post_link( __( 'Chỉnh sửa trang', 'ltdh' ), '<span class="edit-link text-xs text-blue-600 hover:underline">', '</span>' ); ?>
	</header>
	<div class="entry-content prose prose-slate max-w-none text-slate-900 text-sm md:text-base bg-white rounded-xl shadow-xs border border-slate-100 p-6 md:p-8" itemprop="mainContentOfPage">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="mb-6">
				<?php the_post_thumbnail( 'full', [ 'itemprop' => 'image', 'class' => 'rounded-xl w-full h-auto' ] ); ?>
			</div>
		<?php endif; ?>
		<?php the_content(); ?>
		<div class="entry-links mt-6 pt-4 border-t border-slate-100">
			<?php wp_link_pages(); ?>
		</div>
	</div>
</article>
<?php if ( comments_open() ) { comments_template( '', true ); } ?>
<?php endwhile; endif; ?>

<?php
get_footer();
