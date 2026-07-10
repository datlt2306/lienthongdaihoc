<?php
/**
 * Single School Template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$school_id  = get_the_ID();
$school_title = get_the_title( $school_id );
$website    = get_field( 'website', $school_id );
$address    = get_field( 'address', $school_id );
$hotline    = get_field( 'hotline', $school_id ) ?: get_field( 'global_hotline', 'options' );
$adm_info   = get_field( 'admission_info', $school_id );
$contact    = get_field( 'contact_info', $school_id );

// Retrieve pre-calculated list of programs offered by this school
$offered_program_ids = get_post_meta( $school_id, '_offered_programs', true );

$global_zalo = get_field( 'global_zalo_url', 'options' ) ?: 'https://zalo.me';
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
		
		<!-- HERO SECTION -->
		<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-6 md:p-8 mb-8">
			<div class="flex flex-col md:flex-row gap-6 items-center md:items-start text-center md:text-left">
				<?php ltdh_render_school_thumbnail( $school_id, 'medium', 'h-24 w-24 object-cover shrink-0 rounded-lg border border-slate-100 bg-white' ); ?>
				
				<div class="flex-1 space-y-2">
					<h1 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight"><?php the_title(); ?></h1>
					<p class="text-slate-500 text-sm">Địa chỉ: <?php echo esc_html( $address ?: 'Chưa cập nhật' ); ?></p>
					<?php if ( $website ) : ?>
						<p class="text-sm text-slate-400">Website chính thức: <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" class="text-brand-primary hover:underline"><?php echo esc_html( $website ); ?></a></p>
					<?php endif; ?>
				</div>

				<div class="flex flex-col gap-2 w-full md:w-auto">
					<a href="#register" class="bg-brand-primary text-white text-center px-6 py-2.5 rounded-lg font-bold shadow-md hover:bg-teal-700 transition-all text-sm">Đăng Ký Nhận Tư Vấn</a>
					<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="border border-slate-200 text-slate-700 text-center px-6 py-2.5 rounded-lg font-semibold hover:bg-slate-50 transition-all text-sm">Hotline: <?php echo esc_html( $hotline ); ?></a>
				</div>
			</div>
		</section>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-8">
				
				<!-- OVERVIEW -->
				<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Giới thiệu về trường</h2>
					<div class="prose prose-slate max-w-none text-slate-600 text-sm md:text-base">
						<?php the_content(); ?>
					</div>
				</section>

				<!-- PROGRAMS OFFERED -->
				<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Chương trình tuyển sinh đang mở</h2>
					
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
						// Fallback query if meta relationships don't exist yet
						$programs_query = new WP_Query( [
							'post_type' => 'program',
							'meta_query' => [
								'relation' => 'AND',
								[
									'key' => 'school_relationship',
									'value' => $school_id,
									'compare' => '='
								],
								$meta_status_filter,
							],
							'posts_per_page' => 10
						] );
					}

					if ( $programs_query->have_posts() ) :
						echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
						while ( $programs_query->have_posts() ) : $programs_query->the_post();
							$prog_id = get_the_ID();
							$major_rel_id = get_field( 'major_relationship', $prog_id );
							$major_name = $major_rel_id ? get_the_title( $major_rel_id ) : 'Mời tư vấn';
							$major_thumb = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium' ) : '';
							if ( ! $major_thumb ) {
								$major_thumb = 'https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=300';
							}
							?>
							<?php
							$status = get_post_meta( $prog_id, 'admission_status', true ) ?: 'tuyen-sinh';
							$clean_title = get_the_title();
							if ( $school_title ) {
								$clean_title = str_replace( ' - ' . $school_title, '', $clean_title );
								$clean_title = str_replace( ' – ' . $school_title, '', $clean_title );
							}
							$types = wp_get_post_terms( $prog_id, 'training_type' );
							$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';
							$tuition_fee = get_field( 'tuition_fee', $prog_id ) ?: 'Liên hệ';
							$duration = get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm';
							?>
							<div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
								<div class="h-40 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $major_thumb ); ?>');"></div>
								<div class="p-5 flex-1 flex flex-col justify-between space-y-4">
									<div>
										<div class="flex items-center flex-wrap gap-2 mb-2">
											<?php if ( $type_name ) : ?>
												<span class="bg-orange-50 text-orange-600 text-[10px] font-black px-2.5 py-0.5 rounded-lg uppercase tracking-wider"><?php echo esc_html( $type_name ); ?></span>
											<?php endif; ?>
										</div>
										<h4 class="font-extrabold text-slate-800 text-sm hover:text-[#2563EB] transition-colors leading-snug">
											<a href="<?php the_permalink(); ?>"><?php echo esc_html( $clean_title ); ?></a>
										</h4>
										
										<div class="mt-3 pt-3 border-t border-slate-100 space-y-1.5 text-xs text-slate-500">
											<p>Ngành học: 
												<?php if ( $major_rel_id ) : ?>
													<a href="<?php echo esc_url( get_permalink( $major_rel_id ) ); ?>" class="font-bold text-[#2563EB] hover:underline"><?php echo esc_html( $major_name ); ?></a>
												<?php else : ?>
													<span class="font-semibold text-slate-700"><?php echo esc_html( $major_name ); ?></span>
												<?php endif; ?>
											</p>
											<p>Thời gian: <span class="font-semibold text-slate-700"><?php echo esc_html( $duration ); ?></span></p>
											<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( $tuition_fee ); ?></span></p>
										</div>
									</div>

									<div class="pt-2 border-t border-slate-100 flex items-center justify-between">
										<a href="<?php the_permalink(); ?>" class="text-xs text-brand-primary font-bold hover:underline">Chi tiết</a>
										<?php if ( $status === 'tam-ngung' ) : ?>
											<span class="text-xs text-slate-400 bg-slate-100 px-3 py-1.5 rounded-lg font-bold">Tạm ngưng</span>
										<?php else : ?>
											<a href="<?php the_permalink(); ?>#register" class="bg-[#2563EB] text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-[#1E40AF] transition-all">Đăng ký học</a>
										<?php endif; ?>
									</div>
								</div>
							</div>
							<?php
						endwhile;
						echo '</div>';
						wp_reset_postdata();
					else :
						echo '<p class="text-sm text-slate-500">Hiện tại chưa có chương trình nào được cập nhật cho trường này.</p>';
					endif;
					?>
				</section>

				<!-- MAJORS OFFERED -->
				<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Các ngành đào tạo phổ biến</h2>
					<?php
					// Query distinct majors via the programs offered by this school
					$distinct_major_ids = [];
					if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
						foreach ( $offered_program_ids as $p_id ) {
							$m_id = get_field( 'major_relationship', $p_id );
							if ( $m_id && ! in_array( $m_id, $distinct_major_ids ) ) {
								$distinct_major_ids[] = $m_id;
							}
						}
					}
					
					if ( empty( $distinct_major_ids ) ) {
						$linked_programs = get_posts( [
							'post_type'      => 'program',
							'posts_per_page' => -1,
							'meta_query'     => [
								[
									'key'     => 'school_relationship',
									'value'   => $school_id,
									'compare' => '=',
								],
							],
							'fields'         => 'ids',
						] );
						foreach ( $linked_programs as $p_id ) {
							$m_id = get_field( 'major_relationship', $p_id );
							if ( $m_id && ! in_array( $m_id, $distinct_major_ids ) ) {
								$distinct_major_ids[] = $m_id;
							}
						}
					}
					
					if ( ! empty( $distinct_major_ids ) ) {
						$majors_query = new WP_Query( [
							'post_type' => 'major',
							'post__in'  => $distinct_major_ids,
							'post_status' => 'publish'
						] );
					} else {
						$majors_query = false;
					}

					if ( $majors_query && $majors_query->have_posts() ) :
						echo '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">';
						while ( $majors_query->have_posts() ) : $majors_query->the_post();
						?>
							<a href="<?php the_permalink(); ?>" class="block p-4 border border-slate-100 rounded-lg hover:border-brand-primary hover:shadow-sm transition-all bg-white">
								<h4 class="font-bold text-slate-800 text-sm mb-1"><?php the_title(); ?></h4>
								<span class="text-sm text-slate-400">Mã ngành: <?php echo esc_html( get_field( 'major_code' ) ?: 'Đang cập nhật' ); ?></span>
							</a>
						<?php
						endwhile;
						echo '</div>';
						wp_reset_postdata();
					else :
						echo '<p class="text-sm text-slate-500">Các ngành học chính của trường đang được cập nhật.</p>';
					endif;
					?>
				</section>

				<!-- ADMISSION INFORMATION -->
				<?php if ( $adm_info ) : ?>
					<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Phương thức tuyển sinh</h2>
						<div class="prose prose-slate max-w-none text-slate-600 text-sm">
							<?php echo wp_kses_post( $adm_info ); ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- CONTACT INFO -->
				<?php if ( $contact ) : ?>
					<section class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Thông tin liên hệ tuyển sinh</h2>
						<div class="prose prose-slate max-w-none text-slate-600 text-sm">
							<?php echo wp_kses_post( $contact ); ?>
						</div>
					</section>
				<?php endif; ?>

			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<div class="sticky top-24 space-y-6">
					
					<!-- CONSULTATION FORM -->
					<section id="register" class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
						<h3 class="text-lg font-bold text-slate-900 mb-2">Đăng ký vào <?php the_title(); ?></h3>
						<p class="text-sm text-slate-500 mb-4">Nhận tư vấn hồ sơ miễn phí, hỗ trợ xử lý thủ tục nhập học nhanh chóng.</p>
						
						<?php 
						if ( function_exists( 'wpcf7_contact_form_html' ) ) :
							echo do_shortcode( '[contact-form-7 id="consultation-form" title="Form Tư vấn"]' );
						else :
						?>
							<form action="#" method="POST" class="space-y-4">
								<input type="hidden" name="current_school_id" value="<?php echo esc_attr( $school_id ); ?>">
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
									Gửi Thông Tin Đăng Ký
								</button>
							</form>
						<?php endif; ?>
					</section>

					<!-- CONTACT INFO CARD -->
					<div class="bg-slate-950 text-white rounded-lg p-6 text-center shadow-lg">
						<span class="text-sm text-teal-400 font-bold uppercase tracking-wider block mb-1">Văn phòng tuyển sinh</span>
						<h4 class="font-display font-black text-2xl mb-4"><?php echo esc_html( $hotline ); ?></h4>
						<div class="flex gap-2">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex-1 bg-brand-primary text-white py-2 rounded-lg font-semibold text-sm hover:bg-teal-700 transition-all">Gọi Ngay</a>
							<a href="<?php echo esc_url( $global_zalo ); ?>" class="flex-1 bg-white/10 text-white border border-white/20 py-2 rounded-lg font-semibold text-sm hover:bg-white/20 transition-all">Nhận Trò Chuyện</a>
						</div>
					</div>

				</div>
			</div>
		</div>

	</div>
</main>

<?php
get_footer();
