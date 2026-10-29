<?php
/**
 * Taxonomy Archive: Region (Khu vực)
 *
 * Lists schools belonging to a specific geographic region.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term = get_queried_object();
$region_name = $term && isset( $term->name ) ? $term->name : 'Khu vực';
?>

<div id="primary" class="content-area site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
		<div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10 space-y-2">
			<span class="inline-block bg-[#0071E3]/10 text-[#0071E3] text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">KHU VỰC ĐÀO TẠO</span>
			<h1 class="text-2xl sm:text-3xl font-semibold text-[#1D1D1F] tracking-tight"><?php echo esc_html( $region_name ); ?></h1>
			<p class="text-slate-500 text-sm">Danh sách các trường đại học đối tác đào tạo liên thông tại <?php echo esc_html( $region_name ); ?>.</p>
		</div>

		<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					get_template_part( 'template-parts/cards/card-school' );
				endwhile;
				wp_reset_postdata();
			else :
				echo '<div class="col-span-full text-center py-12"><p class="text-slate-500 text-base">Chưa có trường đối tác nào thuộc khu vực này.</p></div>';
			endif;
			?>
		</div>

		<?php get_template_part( 'template-parts/nav-below' ); ?>
	</div>
</div><!-- #primary -->

<?php
get_footer();
