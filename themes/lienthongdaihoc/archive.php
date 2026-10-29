<?php
/**
 * Generic Archive Template
 *
 * Conforms strictly to BlankSlate reference specification.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
	<header class="header mb-8 pb-4 border-b border-slate-200">
		<h1 class="entry-title text-xl sm:text-2xl font-extrabold text-slate-900" itemprop="name"><?php the_archive_title(); ?></h1>
		<?php if ( get_the_archive_description() ) : ?>
			<div class="archive-description text-slate-500 text-sm mt-2" itemprop="description"><?php the_archive_description(); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'entry' ); ?>
			<?php endwhile; ?>
		</div>
		<div class="mt-10">
			<?php get_template_part( 'nav', 'below' ); ?>
		</div>
	<?php endif; ?>
</div>

<?php
get_footer();
