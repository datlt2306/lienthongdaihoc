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

	<!-- Hero Banner with Post Thumbnail (Aligned with standard layout) -->
	<section class="relative bg-gradient-to-tr from-[#0E2038] to-brand-primary text-white py-14 md:py-20 overflow-hidden">
		<!-- Dot Grid Pattern -->
		<div class="absolute inset-0 opacity-10 pointer-events-none z-0" style="background-image: radial-gradient(white 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
		<div class="absolute -right-32 -bottom-32 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
		<?php if ( $post_thumb ) : ?>
			<div class="absolute inset-0 bg-cover bg-center opacity-15 pointer-events-none mix-blend-overlay" style="background-image: url('<?php echo esc_url( $post_thumb ); ?>');"></div>
		<?php endif; ?>

		<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<!-- Category & School Badges -->
			<div class="flex flex-wrap items-center gap-2 mb-4">
				<?php if ( $post_cat ) : ?>
					<span class="inline-block bg-white/20 backdrop-blur-sm text-white text-sm font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
						<?php echo esc_html( $post_cat->name ); ?>
					</span>
				<?php endif; ?>
				<?php
				$school_id = get_field( 'school_relationship', $post_id );
				if ( $school_id ) :
					$school_name = get_the_title( $school_id );
				?>
					<span class="inline-block bg-white/10 backdrop-blur-sm text-white text-sm font-extrabold px-3 py-1 rounded-full tracking-wider">
						<?php echo esc_html( $school_name ); ?>
					</span>
				<?php endif; ?>
			</div>

			<h1 class="text-2xl sm:text-3xl md:text-4xl font-black font-display tracking-tight leading-tight mb-5">
				<?php the_title(); ?>
			</h1>

			<!-- Meta -->
			<div class="flex flex-wrap items-center gap-4 text-sm text-blue-100/90 font-medium">
				<span class="flex items-center gap-1.5">
					<svg class="w-4 h-4 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
					<?php echo get_the_date( 'd/m/Y' ); ?>
				</span>
				<span class="flex items-center gap-1.5">
					<svg class="w-4 h-4 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
					<?php echo esc_html( $reading_time ); ?> phút đọc
				</span>
				<span class="flex items-center gap-1.5">
					<svg class="w-4 h-4 text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
					<?php the_author(); ?>
				</span>
			</div>
		</div>
	</section>

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

						<!-- Share -->
						<div class="mt-8 pt-6 border-t border-slate-100">
							<p class="text-sm font-extrabold text-slate-400 uppercase tracking-wider mb-3">Chia sẻ bài viết</p>
							<div class="flex gap-2">
								<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>" target="_blank" rel="noopener"
								   class="flex items-center gap-2 px-4 py-2 bg-[#1877F2] text-white text-sm font-bold rounded-lg hover:bg-[#166FE5] transition-all">
									<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
									Facebook
								</a>
								<a href="https://zalo.me/share/url?url=<?php echo urlencode( get_permalink() ); ?>" target="_blank" rel="noopener"
								   class="flex items-center gap-2 px-4 py-2 bg-[#0068FF] text-white text-sm font-bold rounded-lg hover:opacity-90 transition-all">
									💬 Zalo
								</a>
								<button onclick="navigator.clipboard.writeText('<?php echo esc_js( get_permalink() ); ?>'); this.textContent='✓ Đã sao chép';"
								        class="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-lg hover:bg-slate-200 transition-all">
									🔗 Sao chép link
								</button>
							</div>
						</div>
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
