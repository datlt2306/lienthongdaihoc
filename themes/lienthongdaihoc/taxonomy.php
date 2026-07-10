<?php
/**
 * Taxonomy Archive Template (Handles Training Types, Campuses, and Regions)
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term = get_queried_object();
$taxonomy = $term->taxonomy;
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

		<!-- Dynamic Grid Output based on Taxonomy target -->
		<?php if ( $taxonomy === 'region' ) : ?>
			<!-- Region lists schools -->
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
						$target  = get_post_meta( $school_id, 'admission_target', true ) ?: '3.000';
				?>
						<div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
							<div class="h-28 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $thumb_url ); ?>');"></div>
							
							<!-- Logo Overlay -->
							<div class="h-16 w-16 bg-white rounded-full border-4 border-white shadow-md bg-white -mt-8 mx-auto z-10 relative flex items-center justify-center overflow-hidden">
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
								
								<div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-sm text-slate-600">
									<a href="<?php the_permalink(); ?>" class="w-full text-center bg-slate-50 hover:bg-[#2563EB] hover:text-white py-2 rounded-xl font-bold transition-all text-xs uppercase text-[#2563EB]">Chi tiết trường</a>
								</div>
							</div>
						</div>
				<?php 
						$index++;
					endwhile;
				else :
					echo '<div class="col-span-4 text-center py-12"><p class="text-slate-500 text-base">Chưa có trường liên kết nào thuộc khu vực này.</p></div>';
				endif;
				?>
			</div>

		<?php else : ?>
			<!-- campus or training_type lists programs -->
			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				<?php
				if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						$prog_id = get_the_ID();
						$school_rel_id = get_field( 'school_relationship', $prog_id );
						$school_name = $school_rel_id ? get_the_title( $school_rel_id ) : 'Đại học liên kết';
				?>
					<?php
					$major_rel_id = get_field( 'major_relationship', $prog_id );
					$major_thumb = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium' ) : '';
					if ( ! $major_thumb ) {
						$major_thumb = 'https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=300';
					}
					$types = wp_get_post_terms( $prog_id, 'training_type' );
					$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : 'Chưa xác định';
					?>
					<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
						<div class="h-44 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $major_thumb ); ?>');"></div>
						<div class="p-6 flex-1 flex flex-col justify-between">
							<?php
							$status = get_post_meta( $prog_id, 'admission_status', true ) ?: 'tuyen-sinh';
							$groups = get_post_meta( $prog_id, 'admission_groups', true );
							?>
							<div>
								<div class="flex items-center flex-wrap gap-2 mb-1.5">
									<span class="text-sm text-slate-400 font-semibold uppercase"><?php echo esc_html( $school_name ); ?></span>
								</div>
								<h3 class="font-extrabold text-slate-800 text-lg hover:text-brand-primary mb-3">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
								
								<?php
								$learning_details = ltdh_get_program_learning_details( get_the_ID() );
								?>
								<div class="space-y-1.5 text-sm text-slate-500 py-3 border-t border-slate-100">
									<p>Hệ đào tạo: <span class="font-bold text-slate-700"><?php echo esc_html( $type_name ); ?></span></p>
									<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( get_field( 'tuition_fee' ) ?: 'Liên hệ' ); ?></span></p>
									<p>Thời gian: <span class="font-bold text-slate-700"><?php echo esc_html( get_field( 'duration' ) ?: '1.5 - 2 năm' ); ?></span></p>
									<p>Cơ sở: <span class="font-bold text-slate-700"><?php echo esc_html( $learning_details['campus'] ); ?></span></p>
									<p>Hình thức: <span class="font-bold text-slate-700 text-xs"><?php echo esc_html( $learning_details['mode'] ); ?></span></p>
									<?php if ( ! empty( $groups ) ) : ?>
										<p>Tổ hợp: <span class="font-bold text-slate-700"><?php echo esc_html( $groups ); ?></span></p>
									<?php endif; ?>
								</div>
							</div>

							<div class="mt-6 pt-4 border-t border-slate-100 flex justify-between items-center">
								<a href="<?php the_permalink(); ?>" class="text-sm text-brand-primary font-bold hover:underline">Chi tiết</a>
								<a href="<?php the_permalink(); ?>#register" class="bg-[#2563EB] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-[#1E40AF] transition-all">Đăng ký học</a>
							</div>
						</div>
					</div>
				<?php
					endwhile;
				else :
					echo '<div class="col-span-3 text-center py-12"><p class="text-slate-500 text-base">Hiện chưa có chương trình nào tuyển sinh thuộc hệ/cơ sở đào tạo này.</p></div>';
				endif;
				?>
			</div>
		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
