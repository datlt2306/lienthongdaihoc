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
$offered_program_ids = get_post_meta( $major_id, '_offered_programs', true );

$global_zalo = get_field( 'global_zalo_url', 'options' ) ?: 'https://zalo.me';
$hotline = get_field( 'global_hotline', 'options' ) ?: '0389198653';
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
		
		<!-- HERO SECTION -->
		<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8 mb-8">
			<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
				<div class="space-y-2">
					<span class="inline-block bg-teal-50 text-brand-primary text-sm font-bold px-3 py-1 rounded-full uppercase tracking-wider">
						Thông tin Ngành học
					</span>
					<h1 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight">
						Ngành <?php the_title(); ?>
					</h1>
					<p class="text-slate-500 text-sm font-medium">Mã ngành: <?php echo esc_html( $major_code ?: 'Đang cập nhật' ); ?></p>
				</div>
				<a href="#register" class="w-full md:w-auto bg-brand-primary text-white text-center px-6 py-2.5 rounded-lg font-bold shadow-md hover:bg-teal-700 transition-all text-sm">
					Tư Vấn Hướng Nghiệp
				</a>
			</div>
		</section>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-8">
				
				<!-- OVERVIEW -->
				<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Tổng quan về ngành</h2>
					<div class="prose prose-slate max-w-none text-slate-600 text-sm md:text-base">
						<?php the_content(); ?>
					</div>
				</section>

				<!-- CAREER OPPORTUNITIES -->
				<?php if ( $career ) : ?>
					<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Cơ hội nghề nghiệp & Định hướng</h2>
						<div class="prose prose-slate max-w-none text-slate-600 text-sm">
							<?php echo wp_kses_post( $career ); ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- PROGRAMS FOR THIS MAJOR -->
				<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Chương trình tuyển sinh ngành <?php the_title(); ?></h2>
					
					<?php
					$meta_status_filter = [
						'relation' => 'OR',
						[
							'key'     => 'admission_status',
							'value'   => 'tam-ngung',
							'compare' => '!=',
						],
						[
							'key'     => 'admission_status',
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
									'key' => 'major_relationship',
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
							$school_rel_id = get_field( 'school_relationship', $prog_id );
							$school_name = $school_rel_id ? get_the_title( $school_rel_id ) : 'Mời tư vấn';
							$status = get_post_meta( $prog_id, 'admission_status', true ) ?: 'tuyen-sinh';
							$types = wp_get_post_terms( $prog_id, 'training_type' );
							$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';
							?>
							<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 border border-slate-100 rounded-xl hover:border-[#2563EB] transition-all bg-white">
								<div class="space-y-1">
									<h4 class="font-bold text-slate-800 text-base hover:text-[#2563EB] transition-colors flex items-center gap-2 flex-wrap">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										<?php if ( $type_name ) : ?>
											<span class="bg-blue-50 text-brand-primary text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider"><?php echo esc_html( $type_name ); ?></span>
										<?php endif; ?>
									</h4>
									<p class="text-sm text-slate-500">Trường: 
										<?php if ( $school_rel_id ) : ?>
											<a href="<?php echo esc_url( get_permalink( $school_rel_id ) ); ?>" class="font-bold text-[#2563EB] hover:underline"><?php echo esc_html( $school_name ); ?></a>
										<?php else : ?>
											<span class="font-semibold text-slate-700"><?php echo esc_html( $school_name ); ?></span>
										<?php endif; ?>
										| Hệ đào tạo: <span class="font-semibold text-slate-700"><?php echo esc_html( $type_name ?: 'Liên hệ' ); ?></span>
										| Học phí: <span class="font-semibold text-slate-700"><?php echo esc_html( get_field( 'tuition_fee' ) ?: 'Liên hệ' ); ?></span>
									</p>
								</div>
								<?php if ( $status === 'tam-ngung' ) : ?>
									<a href="<?php the_permalink(); ?>" class="mt-3 sm:mt-0 bg-slate-100 text-slate-500 px-5 py-2 rounded-lg text-sm font-bold hover:bg-slate-200 transition-all">
										Chi tiết
									</a>
								<?php else : ?>
									<a href="<?php the_permalink(); ?>" class="mt-3 sm:mt-0 bg-[#2563EB] text-white px-5 py-2 rounded-lg text-sm font-bold hover:bg-[#1E40AF] transition-all">
										Chi tiết
									</a>
								<?php endif; ?>
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

				<!-- SCHOOLS OFFERING THIS MAJOR -->
				<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Các trường đào tạo ngành này</h2>
					<?php
					$distinct_school_ids = [];
					if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
						foreach ( $offered_program_ids as $p_id ) {
							$s_id = get_field( 'school_relationship', $p_id );
							if ( $s_id && ! in_array( $s_id, $distinct_school_ids ) ) {
								$distinct_school_ids[] = $s_id;
							}
						}
					}
					
					if ( empty( $distinct_school_ids ) ) {
						$linked_programs = get_posts( [
							'post_type'      => 'program',
							'posts_per_page' => -1,
							'meta_query'     => [
								[
									'key'     => 'major_relationship',
									'value'   => $major_id,
									'compare' => '=',
								],
							],
							'fields'         => 'ids',
						] );
						foreach ( $linked_programs as $p_id ) {
							$s_id = get_field( 'school_relationship', $p_id );
							if ( $s_id && ! in_array( $s_id, $distinct_school_ids ) ) {
								$distinct_school_ids[] = $s_id;
							}
						}
					}
					
					if ( ! empty( $distinct_school_ids ) ) {
						$schools_query = new WP_Query( [
							'post_type' => 'school',
							'post__in'  => $distinct_school_ids,
							'post_status' => 'publish'
						] );
					} else {
						$schools_query = false;
					}

					if ( $schools_query && $schools_query->have_posts() ) :
						echo '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">';
						while ( $schools_query->have_posts() ) : $schools_query->the_post();
						?>
							<a href="<?php the_permalink(); ?>" class="block p-4 border border-slate-100 rounded-xl hover:border-brand-primary hover:shadow-sm transition-all bg-white flex items-center gap-3">
								<?php ltdh_render_school_thumbnail( get_the_ID(), 'thumbnail', 'h-10 w-10 object-cover shrink-0 rounded-lg border border-slate-100 bg-white' ); ?>
								<div>
									<h4 class="font-bold text-slate-800 text-sm"><?php the_title(); ?></h4>
									<span class="text-[10px] text-slate-400 block truncate max-w-[200px]"><?php echo esc_html( get_field( 'address' ) ?: 'Xem bản đồ' ); ?></span>
								</div>
							</a>
						<?php
						endwhile;
						echo '</div>';
						wp_reset_postdata();
					else :
						echo '<p class="text-sm text-slate-500">Danh sách các trường tuyển sinh ngành này đang được bổ sung.</p>';
					endif;
					?>
				</section>

			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<div class="sticky top-24 space-y-6">
					
					<!-- CONSULTATION FORM -->
					<section id="register" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
						<h3 class="text-lg font-bold text-slate-900 mb-2">Đăng ký tư vấn ngành <?php the_title(); ?></h3>
						<p class="text-sm text-slate-500 mb-4">Để lại thông tin, ban tuyển sinh sẽ gửi danh sách trường đào tạo phù hợp nhất với học lực và thời gian của bạn.</p>
						
						<?php 
						if ( function_exists( 'wpcf7_contact_form_html' ) ) :
							echo do_shortcode( '[contact-form-7 id="consultation-form" title="Form Tư vấn"]' );
						else :
						?>
							<form action="#" method="POST" class="space-y-4">
								<input type="hidden" name="current_major_id" value="<?php echo esc_attr( $major_id ); ?>">
								<input type="hidden" name="referral_source" value="<?php echo esc_attr( get_permalink() ); ?>">

								<div>
									<label class="block text-sm font-semibold text-slate-600 mb-1">Họ và tên *</label>
									<input type="text" name="your-name" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-brand-primary focus:outline-none" placeholder="Họ và tên của bạn">
								</div>
								<div>
									<label class="block text-sm font-semibold text-slate-600 mb-1">Số điện thoại *</label>
									<input type="tel" name="your-phone" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-brand-primary focus:outline-none" placeholder="Số điện thoại liên hệ">
								</div>
								
								<button type="submit" class="w-full bg-brand-primary text-white py-2.5 rounded-lg text-sm font-bold shadow-md shadow-brand-primary/10 hover:bg-teal-700 transition-all mt-2">
									Gửi Yêu Cầu Hướng Nghiệp
								</button>
							</form>
						<?php endif; ?>
					</section>

					<!-- CONTACT INFO CARD -->
					<div class="bg-brand-primary/5 border border-brand-primary/10 rounded-2xl p-6 text-center">
						<span class="text-sm text-brand-primary font-bold uppercase tracking-wider block mb-1">Ban hướng nghiệp</span>
						<h4 class="font-display font-black text-2xl text-slate-800 mb-4"><?php echo esc_html( $hotline ); ?></h4>
						<div class="flex gap-2">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex-1 bg-brand-primary text-white py-2 rounded-lg font-semibold text-sm hover:bg-teal-700 transition-all">Gọi Ngay</a>
							<a href="<?php echo esc_url( $global_zalo ); ?>" class="flex-1 bg-white border border-brand-primary text-brand-primary py-2 rounded-lg font-semibold text-sm hover:bg-brand-primary/5 transition-all">Zalo OA</a>
						</div>
					</div>

				</div>
			</div>
		</div>

	</div>
</main>

<?php
get_footer();
