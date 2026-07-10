<?php
/**
 * Archive School Directory Template
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

		<!-- Limit Dropdown Selector -->
		<div class="flex flex-col sm:flex-row items-center justify-between mb-8 pb-4 border-b border-slate-200 gap-4">
			<p class="text-sm font-medium text-slate-500">Hiển thị tất cả các trường đại học liên kết tuyển sinh chính thức.</p>
			<div class="flex items-center gap-3">
				<label for="limit-select" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Số lượng hiển thị:</label>
				<select id="limit-select" class="rounded-lg border-slate-300 text-xs py-1.5 px-3 bg-white text-slate-700 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary cursor-pointer shadow-sm" onchange="location = this.value;">
					<option value="<?php echo esc_url( add_query_arg( 'limit', -1 ) ); ?>" <?php selected( isset($_GET['limit']) ? intval($_GET['limit']) : -1, -1 ); ?>>Tất cả</option>
					<option value="<?php echo esc_url( add_query_arg( 'limit', 10 ) ); ?>" <?php selected( isset($_GET['limit']) ? intval($_GET['limit']) : -1, 10 ); ?>>10</option>
					<option value="<?php echo esc_url( add_query_arg( 'limit', 20 ) ); ?>" <?php selected( isset($_GET['limit']) ? intval($_GET['limit']) : -1, 20 ); ?>>20</option>
					<option value="<?php echo esc_url( add_query_arg( 'limit', 30 ) ); ?>" <?php selected( isset($_GET['limit']) ? intval($_GET['limit']) : -1, 30 ); ?>>30</option>
					<option value="<?php echo esc_url( add_query_arg( 'limit', 50 ) ); ?>" <?php selected( isset($_GET['limit']) ? intval($_GET['limit']) : -1, 50 ); ?>>50</option>
					<option value="<?php echo esc_url( add_query_arg( 'limit', 100 ) ); ?>" <?php selected( isset($_GET['limit']) ? intval($_GET['limit']) : -1, 100 ); ?>>100</option>
				</select>
			</div>
		</div>

		<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
			<?php
			if ( have_posts() ) :
				$index = 0;
				$fallback_images = [
					'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&q=80&w=300',
					'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=300',
					'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&q=80&w=300',
					'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&q=80&w=300',
					'https://images.unsplash.com/photo-1519452635265-7b1fbfd1e4e0?auto=format&fit=crop&q=80&w=300'
				];
				while ( have_posts() ) : the_post();
					$school_id = get_the_ID();
					$address = get_field( 'address', $school_id ) ?: 'Việt Nam';
					$hotline = get_field( 'hotline', $school_id ) ?: get_field( 'global_hotline', 'options' );
					$thumb_url = get_the_post_thumbnail_url( $school_id, 'medium' ) ?: $fallback_images[$index % 5];
					$logo_id = get_field( 'logo', $school_id );
					$en_name = get_post_meta( $school_id, 'english_name', true ) ?: 'University';
					$rating  = get_post_meta( $school_id, 'rating', true ) ?: '4.8';
					$reviews = get_post_meta( $school_id, 'reviews_count', true ) ?: '256';
			?>
			<div class="bg-white border border-slate-100 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
						<div class="h-28 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $thumb_url ); ?>');"></div>
						
						<!-- Logo Overlay -->
						<div class="h-16 w-16 bg-white rounded-lg border-4 border-white shadow-md bg-white -mt-8 mx-auto z-10 relative flex items-center justify-center overflow-hidden">
							<?php if ( $logo_id ) : ?>
								<?php echo wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
							<?php else : ?>
								<span class="font-display font-extrabold text-brand-primary text-xs">UNI</span>
							<?php endif; ?>
						</div>

						<div class="p-4 pt-2 flex-1 flex flex-col justify-between">
							<div class="text-center">
								<h4 class="font-extrabold text-slate-800 text-xs md:text-sm tracking-tight leading-snug uppercase min-h-[36px] line-clamp-2 mt-1"><?php the_title(); ?></h4>
								<p class="text-[11px] text-slate-400 mt-0.5 font-medium line-clamp-1 italic"><?php echo esc_html( $en_name ); ?></p>
								
								<div class="flex items-center justify-center gap-1 mt-2.5 text-[11px] text-slate-500 font-bold">
									<span class="text-yellow-400">★</span>
									<span><?php echo esc_html( $rating ); ?> (<?php echo esc_html( $reviews ); ?> đánh giá)</span>
								</div>
							</div>
							
						<div class="mt-4 pt-3 border-t border-slate-100">
							<a href="<?php the_permalink(); ?>" class="block w-full text-center bg-slate-50 hover:bg-[#2563EB] hover:text-white py-2 rounded-lg font-bold transition-all text-xs uppercase text-[#2563EB]">Chi tiết trường</a>
						</div>
						</div>
					</div>
			<?php 
					$index++;
				endwhile;
			else :
				echo '<div class="col-span-4 text-center py-12"><p class="text-slate-500 text-base">Chưa có trường liên kết nào được nhập hệ thống.</p></div>';
			endif;
			?>
		</div>

		<!-- Pagination -->
		<div class="mt-12 flex justify-center theme-pagination">
			<?php
			the_posts_pagination( [
				'mid_size'  => 2,
				'prev_text' => '← Trước',
				'next_text' => 'Sau →',
			] );
			?>
		</div>

	</div>
</main>

<?php
get_footer();
