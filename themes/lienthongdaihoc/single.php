<?php
/**
 * Single Post Template - Chi tiết bài viết tin tức & cẩm nang tuyển sinh
 *
 * BlankSlate structural skeleton: delegates presentation to modular parts while preserving
 * standard landmarks. Outer <main> tag is omitted as <main id="content" role="main"> is provided by header.php.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$post_id   = get_the_ID();
$hotline   = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496';
$clean_tel = preg_replace( '/\D/', '', $hotline );

// Related school relationship
$school_id = get_field( 'school_relationship', $post_id );
if ( is_array( $school_id ) && ! empty( $school_id ) ) {
	$school_id = is_object( $school_id[0] ) ? $school_id[0]->ID : (int) $school_id[0];
} elseif ( is_object( $school_id ) ) {
	$school_id = $school_id->ID;
}
$school_name = $school_id ? get_the_title( $school_id ) : '';

// Post categories
$categories  = get_the_category( $post_id );
$primary_cat = null;
if ( ! empty( $categories ) ) {
	foreach ( $categories as $cat ) {
		if ( 'uncategorized' !== $cat->slug ) {
			$primary_cat = $cat;
			break;
		}
	}
	if ( ! $primary_cat && ! empty( $categories[0] ) ) {
		$primary_cat = $categories[0];
	}
}

// Estimated reading time
$word_count   = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );
$reading_time = max( 1, (int) ceil( $word_count / 200 ) );

// Author info
$author_id   = get_the_author_meta( 'ID' );
$author_name = get_the_author_meta( 'display_name' ) ?: 'Ban Quản Trị LTDH';
$author_bio  = get_the_author_meta( 'description' ) ?: 'Đội ngũ biên tập viên chuyên trách thông tin tuyển sinh Đại học, Liên thông Đại học và Đào tạo từ xa tại lienthongdaihoc.com.';
?>

<div class="site-single-post bg-slate-50/60 min-h-screen py-8 md:py-12">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

			<!-- ====== LEFT: MAIN ARTICLE COLUMN ====== -->
			<div class="lg:col-span-8 space-y-8 min-w-0">

				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-7 md:p-10 overflow-hidden' ); ?> itemscope itemtype="https://schema.org/Article">
						
						<!-- Article Header -->
						<header class="entry-header mb-6 pb-6 border-b border-slate-100">
							<!-- Badges Row -->
							<div class="flex flex-wrap items-center gap-2 mb-3.5">
								<?php if ( $primary_cat ) : ?>
									<a href="<?php echo esc_url( get_category_link( $primary_cat->term_id ) ); ?>" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 text-brand-primary hover:bg-blue-100 transition-colors">
										<span><?php echo esc_html( $primary_cat->name ); ?></span>
									</a>
								<?php else : ?>
									<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 text-brand-primary">
										<span>Tin tuyển sinh</span>
									</span>
								<?php endif; ?>

								<?php if ( $school_id && $school_name ) : ?>
									<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-900 border border-amber-200/60 hover:bg-amber-100 transition-colors" title="<?php echo esc_attr( $school_name ); ?>">
										<span>🏛️</span>
										<span class="line-clamp-1 max-w-[200px] sm:max-w-xs"><?php echo esc_html( $school_name ); ?></span>
									</a>
								<?php endif; ?>
							</div>

							<!-- Title -->
							<h1 class="entry-title font-display font-black text-2xl sm:text-3xl md:text-4xl text-slate-900 leading-tight tracking-tight mb-4" itemprop="headline">
								<?php the_title(); ?>
							</h1>

							<!-- Meta Bar -->
							<div class="flex flex-wrap items-center gap-y-2 gap-x-4 text-xs sm:text-sm text-slate-500">
								<!-- Author Info -->
								<div class="flex items-center gap-2" itemprop="author" itemscope itemtype="https://schema.org/Person">
									<div class="w-7 h-7 rounded-full bg-blue-100 text-brand-primary flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden select-none">
										<?php echo esc_html( mb_substr( $author_name, 0, 1 ) ); ?>
									</div>
									<span class="font-bold text-slate-700" itemprop="name"><?php echo esc_html( $author_name ); ?></span>
								</div>

								<span class="text-slate-300">•</span>

								<!-- Publish Date -->
								<div class="flex items-center gap-1.5 text-slate-500">
									<?php ltdh_icon( 'calendar', 'w-4 h-4 text-slate-400 shrink-0' ); ?>
									<time class="entry-date font-medium" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" itemprop="datePublished">
										<?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?>
									</time>
									<?php if ( get_the_date() !== get_the_modified_date() ) : ?>
										<meta itemprop="dateModified" content="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>">
									<?php endif; ?>
								</div>

								<span class="text-slate-300">•</span>

								<!-- Reading Time -->
								<div class="flex items-center gap-1.5 text-slate-500">
									<?php ltdh_icon( 'clock', 'w-4 h-4 text-slate-400 shrink-0' ); ?>
									<span><?php echo esc_html( $reading_time ); ?> phút đọc</span>
								</div>

								<?php if ( current_user_can( 'edit_post', $post_id ) ) : ?>
									<span class="text-slate-300">•</span>
									<?php edit_post_link( __( 'Chỉnh sửa', 'ltdh' ), '<span class="edit-link text-xs text-blue-600 hover:underline">', '</span>' ); ?>
								<?php endif; ?>
							</div>
						</header>

						<!-- Article Excerpt / Lead Intro -->
						<?php if ( has_excerpt() ) : ?>
							<div class="p-4 sm:p-5 rounded-xl bg-slate-50/90 border-l-4 border-brand-primary text-slate-700 text-sm sm:text-base font-medium leading-relaxed mb-6" itemprop="description">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>

						<!-- Featured Thumbnail -->
						<?php if ( has_post_thumbnail() ) : ?>
							<figure class="mb-8 rounded-xl overflow-hidden border border-slate-100 shadow-2xs">
								<?php the_post_thumbnail( 'full', [
									'class'    => 'w-full h-auto object-cover max-h-[500px]',
									'itemprop' => 'image',
									'loading'  => 'eager',
								] ); ?>
								<?php if ( get_the_post_thumbnail_caption() ) : ?>
									<figcaption class="text-center text-xs text-slate-400 py-2.5 px-4 bg-slate-50 border-t border-slate-100 italic">
										<?php the_post_thumbnail_caption(); ?>
									</figcaption>
								<?php endif; ?>
							</figure>
						<?php endif; ?>

						<!-- Article Body Content -->
						<div class="entry-content prose prose-slate max-w-none text-slate-700 text-base leading-relaxed prose-headings:font-display prose-headings:font-extrabold prose-headings:text-slate-900 prose-headings:tracking-tight prose-a:text-brand-primary hover:prose-a:underline prose-img:rounded-xl prose-img:shadow-xs prose-strong:text-slate-900 prose-table:overflow-x-auto" itemprop="articleBody">
							<?php the_content(); ?>
						</div>

						<!-- Pagination for multi-page posts -->
						<div class="entry-links mt-6 pt-4 border-t border-slate-100">
							<?php wp_link_pages( [
								'before' => '<div class="page-links flex items-center gap-2"><span class="page-links-title font-bold text-sm text-slate-700">' . __( 'Trang:', 'ltdh' ) . '</span>',
								'after'  => '</div>',
							] ); ?>
						</div>

						<!-- Tags -->
						<?php
						$tags = get_the_tags();
						if ( $tags ) :
						?>
							<div class="mt-8 pt-6 border-t border-slate-100 flex flex-wrap items-center gap-2">
								<span class="text-xs font-bold text-slate-400 mr-1">🏷️ Thẻ:</span>
								<?php foreach ( $tags as $tag ) : ?>
									<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 text-xs font-medium transition-colors">
										#<?php echo esc_html( $tag->name ); ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<!-- Share Component -->
						<?php get_template_part( 'template-parts/post-share' ); ?>

						<!-- Author Box (Ban Quản Trị LTDH - Placed Directly Before Comment Form) -->
						<div class="mt-8 pt-6 border-t border-slate-100 flex items-start gap-4">
							<div class="w-12 h-12 rounded-full bg-blue-100 text-brand-primary flex items-center justify-center font-black text-lg shrink-0 border border-blue-200 select-none">
								<?php echo esc_html( mb_substr( $author_name, 0, 1 ) ); ?>
							</div>
							<div class="min-w-0">
								<div class="flex items-center gap-1.5 mb-1">
									<span class="font-bold text-slate-900 text-sm sm:text-base"><?php echo esc_html( $author_name ); ?></span>
									<?php ltdh_icon( 'verified', 'w-4 h-4 text-[#0071E3] shrink-0' ); ?>
								</div>
								<p class="text-xs text-slate-500 leading-relaxed">
									<?php echo esc_html( $author_bio ); ?>
								</p>
							</div>
						</div>

						<!-- Comments Section (Placed Below Share Section & Author Box) -->
						<?php
						if ( comments_open() || get_comments_number() ) {
							comments_template();
						}
						?>

					</article>
				<?php endwhile; endif; ?>

				<!-- Adjacent Navigation: Previous / Next Post -->
				<?php get_template_part( 'template-parts/nav-below-single' ); ?>

				<!-- Related Posts Section -->
				<?php
				$related_args = [
					'post_type'      => 'post',
					'posts_per_page' => 3,
					'post__not_in'   => [ $post_id ],
					'post_status'    => 'publish',
				];
				if ( $primary_cat ) {
					$related_args['cat'] = $primary_cat->term_id;
				}
				$related_query = new WP_Query( $related_args );
				if ( $related_query->have_posts() ) :
				?>
					<section class="mt-10 pt-8 border-t border-slate-200/80" aria-label="<?php esc_attr_e( 'Bài viết liên quan', 'ltdh' ); ?>">
						<div class="flex items-center justify-between mb-5">
							<h3 class="font-display font-extrabold text-lg sm:text-xl text-slate-900 flex items-center gap-2">
								<span class="w-1.5 h-4 rounded-full bg-brand-primary"></span>
								<span>Bài viết liên quan</span>
							</h3>
							<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" class="text-xs sm:text-sm font-bold text-brand-primary hover:underline flex items-center gap-1">
								<span>Xem tất cả</span>
								<span>→</span>
							</a>
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5">
							<?php
							while ( $related_query->have_posts() ) :
								$related_query->the_post();
								$rel_id    = get_the_ID();
								$rel_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $rel_id, 'medium' ) : ( get_the_post_thumbnail_url( $rel_id, 'medium' ) ?: ltdh_get_fallback_image( 'post' ) );
								$rel_cats  = get_the_category( $rel_id );
								$rel_cat   = ! empty( $rel_cats[0] ) ? $rel_cats[0]->name : '';
							?>
								<article class="bg-white rounded-xl border border-slate-200/80 overflow-hidden hover:shadow-md transition-all flex flex-col justify-between group">
									<a href="<?php the_permalink(); ?>" class="block relative aspect-[16/9] w-full bg-slate-100 overflow-hidden shrink-0">
										<img src="<?php echo esc_url( $rel_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
										<?php if ( $rel_cat ) : ?>
											<span class="absolute top-2 left-2 z-10 bg-white/90 backdrop-blur-sm text-[#00308b] text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-2xs">
												<?php echo esc_html( $rel_cat ); ?>
											</span>
										<?php endif; ?>
									</a>

									<div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between">
										<div>
											<time class="text-[11px] text-slate-400 block mb-1"><?php echo get_the_date( 'd/m/Y' ); ?></time>
											<h4 class="font-bold text-slate-800 text-xs sm:text-sm leading-snug line-clamp-2 group-hover:text-brand-primary transition-colors">
												<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
											</h4>
										</div>
										<div class="pt-2.5 mt-2.5 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-brand-primary">
											<span>Chi tiết</span>
											<span>→</span>
										</div>
									</div>
								</article>
							<?php
							endwhile;
							wp_reset_postdata();
							?>
						</div>
					</section>
				<?php endif; ?>



			</div>

			<!-- ====== RIGHT: SIDEBAR COLUMN ====== -->
			<div class="lg:col-span-4 min-w-0">
				<?php get_sidebar(); ?>
			</div>

		</div>
	</div>
</div>

<?php
get_footer();
