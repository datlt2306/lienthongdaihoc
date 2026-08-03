<?php
/**
 * Default Page Template
 *
 * Fallback template for WordPress pages that don't have
 * a specific page-{slug}.php or page-{id}.php template.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
		<?php
		while ( have_posts() ) :
			the_post();
		?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'bg-white rounded-lg shadow-sm border border-slate-100 p-6 md:p-8' ); ?>>
				<h1 class="text-2xl md:text-3xl font-black text-slate-900 mb-6 border-b border-slate-100 pb-4">
					<?php the_title(); ?>
				</h1>
				<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
					<?php the_content(); ?>
				</div>
			</article>
		<?php
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
