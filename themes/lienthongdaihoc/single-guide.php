<?php
/**
 * Single Guide Template
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$guide_id = get_the_ID();
$hotline = ltdh_get_hotline();
$global_zalo = get_field( 'global_zalo_url', 'options' ) ?: 'https://zalo.me';
?>

<div id="primary" class="content-area site-main py-12 bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		
		<!-- Breadcrumbs -->
		<nav class="text-sm text-slate-500 mb-6">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-brand-primary">Trang chủ</a> / 
			<a href="<?php echo esc_url( get_post_type_archive_link( 'guide' ) ?: home_url( '/cam-nang/' ) ); ?>" class="hover:text-brand-primary">Cẩm nang tuyển sinh</a> / 
			<span><?php the_title(); ?></span>
		</nav>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-6">
				<article class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 md:p-8">
					<span class="inline-block bg-blue-50 text-brand-primary text-sm font-bold px-3 py-1 rounded-lg uppercase tracking-wider mb-4">Cẩm nang tuyển sinh</span>
					<h1 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight mb-4"><?php the_title(); ?></h1>
					
					<div class="flex items-center gap-4 text-sm text-slate-400 pb-4 border-b border-slate-100 mb-6">
						<span>Ngày đăng: <?php echo get_the_date( 'd/m/Y' ); ?></span>
					</div>

					<div class="prose prose-slate max-w-none text-slate-700 text-base leading-relaxed">
						<?php the_content(); ?>
					</div>

					<!-- Share Component -->
					<?php get_template_part( 'template-parts/post-share' ); ?>
				</article>
			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<?php get_sidebar( 'guide' ); ?>
			</div>

		</div>

	</div>
</div><!-- #primary -->

<?php
get_footer();
