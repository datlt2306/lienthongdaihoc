<?php
/**
 * Single Major Template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$major_id   = get_the_ID();
$major_code = get_field( 'major_code', $major_id );
$career     = get_field( 'career_opportunities', $major_id );
$salary     = get_field( 'salary_info', $major_id );
$market     = get_field( 'job_market', $major_id );

// Retrieve pre-calculated list of programs matching this major
$offered_program_ids = get_post_meta( $major_id, LTDH_META_OFFERED_PROGRAMS, true );

$global_zalo = ltdh_get_zalo_url();
$hotline = ltdh_get_hotline();
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
		
		<!-- HERO SECTION -->
		<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-6 md:p-8 mb-8">
			<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
				<div class="space-y-2">
					<span class="inline-block bg-teal-50 text-brand-primary text-sm font-bold px-3 py-1 rounded-lg uppercase tracking-wider">
						Thông tin Ngành học
					</span>
					<h1 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight">
						Ngành <?php the_title(); ?>
					</h1>
					<p class="text-slate-500 text-sm font-medium">Mã ngành: <?php echo esc_html( $major_code ?: 'Đang cập nhật' ); ?></p>
				</div>
				<div class="flex flex-col gap-2 w-full md:w-auto">
					<a href="#register" class="w-full md:w-auto bg-brand-accent text-white text-center px-6 py-3 rounded-lg font-bold shadow-md hover:bg-[#e06e00] transition-all text-sm min-h-[44px] flex items-center justify-center">
						Tư Vấn Hướng Nghiệp
					</a>
				</div>
			</div>
		</section>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-8">
				
				<!-- OVERVIEW -->
				<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Tổng quan về ngành</h2>
					<div class="prose prose-slate max-w-none text-slate-600 text-sm md:text-base">
						<?php the_content(); ?>
					</div>
				</section>

				<!-- CAREER OPPORTUNITIES -->
				<?php if ( $career ) : ?>
					<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Cơ hội nghề nghiệp & Định hướng</h2>
						<div class="prose prose-slate max-w-none text-slate-600 text-sm">
							<?php echo wp_kses_post( $career ); ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- PROGRAMS FOR THIS MAJOR -->
				<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Các trường tuyển sinh ngành <?php the_title(); ?></h2>
					
					<?php
					$meta_status_filter = [
						'relation' => 'OR',
						[
							'key'     => LTDH_META_ADMISSION_STATUS,
							'value'   => 'tam-ngung',
							'compare' => '!=',
						],
						[
							'key'     => LTDH_META_ADMISSION_STATUS,
							'compare' => 'NOT EXISTS',
						],
					];

					if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
						$programs_query = new WP_Query( [
							'post_type' => 'program',
							'post__in'  => $offered_program_ids,
							'post_status' => 'publish',
							'meta_query' => [
								'relation' => 'AND',
								$meta_status_filter,
							],
						] );
					} else {
						// Fallback logic
						$programs_query = new WP_Query( [
							'post_type' => 'program',
							'meta_query' => [
								'relation' => 'AND',
								[
									'key' => LTDH_META_MAJOR_REL,
									'value' => $major_id,
									'compare' => '='
								],
								$meta_status_filter,
							],
							'posts_per_page' => 10
						] );
					}

					if ( $programs_query->have_posts() ) :
						echo '<div class="space-y-4">';
						while ( $programs_query->have_posts() ) : $programs_query->the_post();
							$prog_id = get_the_ID();
							$school_rel_id = get_field( LTDH_META_SCHOOL_REL, $prog_id );
							$school_name = $school_rel_id ? get_the_title( $school_rel_id ) : 'Mời tư vấn';
							$school_thumb = $school_rel_id ? get_the_post_thumbnail_url( $school_rel_id, 'medium' ) : '';
						if ( ! $school_thumb ) {
							$school_thumb = ltdh_get_fallback_image( 'school' );
						}
							$status = get_post_meta( $prog_id, LTDH_META_ADMISSION_STATUS, true ) ?: 'tuyen-sinh';
							$types = wp_get_post_terms( $prog_id, LTDH_TAX_TRAINING_TYPE );
							$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';
							$tuition_fee = get_field( LTDH_META_TUITION, $prog_id ) ?: 'Liên hệ';
							$duration = get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm';
							$school_code = $school_rel_id ? get_field( 'school_code', $school_rel_id ) : '';
							$school_address = $school_rel_id ? get_field( 'address', $school_rel_id ) : '';
							?>
							<div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
								<div class="flex items-center gap-4 flex-1">
									<div class="h-16 w-16 bg-slate-200 bg-cover bg-center rounded-lg shrink-0" style="background-image: url('<?php echo esc_url( $school_thumb ); ?>'); font-size: 0;"></div>
									<div class="space-y-1 flex-1 min-w-0">
										<div class="flex items-center gap-2 flex-wrap">
											<h4 class="font-extrabold text-slate-800 text-base hover:text-[#00308b] transition-colors leading-snug">
												<?php if ( $school_rel_id ) : ?>
													<a href="<?php echo esc_url( get_permalink( $school_rel_id ) ); ?>"><?php echo esc_html( $school_name ); ?><?php if ( $school_code ) { echo ' - ' . esc_html( $school_code ); } ?></a>
												<?php else : ?>
													<span class="font-semibold text-slate-700"><?php echo esc_html( $school_name ); ?></span>
												<?php endif; ?>
											</h4>
											<?php if ( $type_name ) : ?>
												<a href="<?php the_permalink(); ?>" class="bg-orange-50 text-orange-600 text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-wider hover:bg-orange-100 transition-all"><?php echo esc_html( $type_name ); ?></a>
											<?php endif; ?>
										</div>
										<?php if ( $school_address ) : ?>
											<p class="text-xs text-slate-400 flex items-center gap-1">
												<span>📍</span>
												<span class="truncate"><?php echo esc_html( $school_address ); ?></span>
											</p>
										<?php endif; ?>
										
										<div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-500 pt-1">
											<p>Thời gian: <span class="font-semibold text-slate-700"><?php echo esc_html( $duration ); ?></span></p>
											<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( $tuition_fee ); ?></span></p>
										</div>
									</div>
								</div>

								<div class="shrink-0 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 flex items-center justify-end">
									<?php if ( $status === 'tam-ngung' ) : ?>
										<span class="text-xs text-slate-400 bg-slate-100 px-4 py-2 rounded-lg font-bold">Tạm ngưng</span>
									<?php else : ?>
										<a href="<?php the_permalink(); ?>" class="w-full sm:w-auto bg-brand-primary text-white text-xs font-bold px-6 py-2.5 rounded-lg hover:bg-brand-darkBlue transition-all min-h-[40px] flex items-center justify-center">Tìm hiểu</a>
									<?php endif; ?>
								</div>
							</div>
							<?php
						endwhile;
						echo '</div>';
						wp_reset_postdata();
					else :
						echo '<p class="text-sm text-slate-500">Hiện tại chưa có lớp học nào mở cho ngành này.</p>';
					endif;
					?>
				</section>
			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<div class="sticky top-24 space-y-6">
					
					<!-- CONSULTATION FORM -->
					<section id="register" class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
						<h3 class="text-lg font-bold text-slate-900 mb-2">Đăng ký tư vấn ngành <?php the_title(); ?></h3>
						<p class="text-sm text-slate-500 mb-4">Để lại thông tin, ban tuyển sinh sẽ gửi danh sách trường đào tạo phù hợp nhất với học lực và thời gian của bạn.</p>
						
						<?php 
						ltdh_render_consultation_form( [
							'current_major_id' => $major_id,
							'referral_source'  => get_permalink(),
						] );
					?>
					</section>
 
					<!-- CONTACT INFO CARD -->
					<div class="bg-brand-accent/5 border border-brand-primary/10 rounded-lg p-6 text-center">
						<span class="text-sm text-brand-primary font-bold uppercase tracking-wider block mb-1">Ban hướng nghiệp</span>
						<h4 class="font-display font-black text-2xl text-slate-800 mb-4"><?php echo esc_html( $hotline ); ?></h4>
						<div class="flex gap-2">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex-1 bg-brand-accent text-white py-3.5 rounded-lg font-semibold text-sm hover:bg-[#e06e00] transition-all min-h-[44px] flex items-center justify-center">Gọi Ngay</a>
							<a href="<?php echo esc_url( $global_zalo ); ?>" class="flex-1 bg-white border border-brand-primary text-brand-primary py-3.5 rounded-lg font-semibold text-sm hover:bg-brand-accent/5 transition-all min-h-[44px] flex items-center justify-center">Zalo OA</a>
						</div>
					</div>

				</div>
			</div>
		</div>

	</div>
</main>

<?php
get_footer();
