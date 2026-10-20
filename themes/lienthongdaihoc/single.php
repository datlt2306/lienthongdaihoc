<?php
/**
 * Single Post Template - Chi tiết bài viết
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$post_id    = get_the_ID();
$post_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $post_id, 'full' ) : ( get_the_post_thumbnail_url( $post_id, 'full' ) ?: ltdh_get_fallback_image( 'post' ) );
$post_cats  = get_the_category( $post_id );
$post_cat   = null;
foreach ( $post_cats as $pc ) {
	if ( $pc->slug !== 'uncategorized' ) {
		$post_cat = $pc;
		break;
	}
}

// Related posts (same category)
$related = [];
if ( $post_cat ) {
	$related = get_posts( [
		'numberposts'    => 4,
		'category__in'   => [ $post_cat->term_id ],
		'post__not_in'   => [ $post_id ],
	] );
}
if ( empty( $related ) ) {
	$related = get_posts( [ 'numberposts' => 4, 'post__not_in' => [ $post_id ] ] );
}

// Estimate reading time
$word_count   = str_word_count( strip_tags( get_the_content() ) );
$reading_time = max( 1, ceil( $word_count / 200 ) );
?>

<main id="primary" class="site-main bg-slate-50">

	<?php get_template_part( 'template-parts/banner' ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
		<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

			<!-- Article Content -->
			<article class="lg:col-span-3">
				<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

					<!-- Full Thumbnail -->
					<?php if ( $post_thumb ) : ?>
						<div class="h-72 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $post_thumb ); ?>');"></div>
					<?php endif; ?>

					<div class="p-8 md:p-10">
						<!-- Excerpt / Lead -->
						<?php if ( get_the_excerpt() ) : ?>
							<div class="text-base text-slate-600 font-medium leading-relaxed mb-6 pb-6 border-b border-slate-100 bg-blue-50/50 rounded-xl p-5 border-l-4 border-l-[#00308b]">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>

						<!-- Main content -->
						<div class="prose prose-slate max-w-none
							prose-headings:font-extrabold prose-headings:text-slate-900
							prose-h2:text-xl prose-h2:mt-8 prose-h2:mb-4 prose-h2:border-b prose-h2:border-slate-100 prose-h2:pb-3
							prose-a:text-[#00308b] prose-a:no-underline hover:prose-a:underline
							prose-img:rounded-xl prose-img:shadow-md
							prose-blockquote:border-l-4 prose-blockquote:border-[#00308b] prose-blockquote:bg-blue-50 prose-blockquote:rounded-r-lg prose-blockquote:py-1
							text-slate-700 leading-relaxed text-[15px]">
							<?php the_content(); ?>
						</div>

						<!-- Tags -->
						<?php $tags = get_the_tags(); if ( $tags ) : ?>
							<div class="mt-8 pt-6 border-t border-slate-100 flex flex-wrap gap-2">
								<span class="text-sm font-bold text-slate-400 uppercase mr-2 self-center">Tags:</span>
								<?php foreach ( $tags as $tag ) : ?>
									<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>"
									   class="px-3 py-1 bg-slate-100 hover:bg-[#00308b] hover:text-white text-slate-600 text-sm font-semibold rounded-full transition-all">
										#<?php echo esc_html( $tag->name ); ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<!-- Share Component -->
						<?php get_template_part( 'template-parts/post-share' ); ?>
					</div>
				</div>

				<!-- Related Posts -->
				<?php if ( ! empty( $related ) ) : ?>
				<div class="mt-10">
					<h2 class="text-xl font-extrabold text-slate-900 mb-6">📚 Bài viết liên quan</h2>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
						<?php foreach ( $related as $rp ) :
							$rp_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $rp->ID, 'medium' ) : ( get_the_post_thumbnail_url( $rp->ID, 'medium' ) ?: ltdh_get_fallback_image( 'post' ) );
							$rp_cats  = get_the_category( $rp->ID );
							$rp_cat   = null;
							foreach ( $rp_cats as $rc ) {
								if ( $rc->slug !== 'uncategorized' ) { $rp_cat = $rc; break; }
							}
						?>
							<a href="<?php echo esc_url( get_permalink( $rp->ID ) ); ?>"
							   class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all flex gap-0">
								<!-- Thumbnail -->
								<div class="w-28 shrink-0">
									<div class="h-full bg-cover bg-center" style="background-image: url('<?php echo esc_url( $rp_thumb ); ?>');"></div>
								</div>
								<div class="p-4 flex flex-col justify-between min-w-0">
									<?php if ( $rp_cat ) : ?>
										<span class="text-xs font-extrabold text-[#00308b] uppercase tracking-wide"><?php echo esc_html( $rp_cat->name ); ?></span>
									<?php endif; ?>
									<h3 class="font-bold text-slate-800 text-sm leading-snug line-clamp-2 mt-1"><?php echo esc_html( $rp->post_title ); ?></h3>
									<time class="text-xs text-slate-400 mt-2 block"><?php echo get_the_date( 'd/m/Y', $rp->ID ); ?></time>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>
			</article>

			<!-- Sidebar -->
			<div class="lg:col-span-1">
				<?php get_sidebar(); ?>
			</div>

		</div>
	</div>
</main>

<?php
get_footer();
