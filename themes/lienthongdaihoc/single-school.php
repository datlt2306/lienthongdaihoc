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
$hotline    = ltdh_get_school_hotline( $school_id );
$adm_info   = get_field( 'admission_info', $school_id );
$contact    = get_field( 'contact_info', $school_id );

// Retrieve pre-calculated list of programs offered by this school
$offered_program_ids = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );

$global_zalo = ltdh_get_zalo_url();
?>

<style>
/* Custom styled Admission info & Contact info cards */
.prose h4 {
	font-size: 0.95rem;
	font-weight: 800;
	color: #1e293b;
	text-transform: uppercase;
	letter-spacing: 0.05em;
	margin-top: 1.5rem;
	margin-bottom: 0.75rem;
	padding-left: 0.75rem;
	border-left: 4px solid #2563eb;
	display: flex;
	align-items: center;
}
.prose ul {
	list-style-type: none !important;
	padding-left: 0 !important;
	margin-bottom: 1.5rem;
	border: 1px solid #f1f5f9;
	border-radius: 8px;
	overflow: hidden;
	background-color: #ffffff;
}
.prose ul li {
	padding: 0.75rem 1rem !important;
	border-bottom: 1px solid #f1f5f9;
	margin: 0 !important;
	font-size: 0.875rem;
	color: #475569;
	display: flex;
	flex-direction: column;
}
@media (min-width: 640px) {
	.prose ul li {
		flex-direction: row;
		justify-content: space-between;
		align-items: center;
		gap: 1.5rem;
	}
}
.prose ul li:last-child {
	border-bottom: none;
}
.prose ul li:nth-child(even) {
	background-color: #f8fafc;
}
.prose ul li strong {
	color: #0f172a;
	font-weight: 700;
	min-width: 180px;
	flex-shrink: 0;
}
.bank-info-box {
	background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
	border: 1px solid #cbd5e1 !important;
	border-left: 5px solid #2563eb !important;
	padding: 1.25rem !important;
	border-radius: 8px !important;
	color: #334155 !important;
	font-size: 0.875rem !important;
	line-height: 1.6 !important;
	margin-top: 0.75rem !important;
	box-shadow: inset 0 1px 2px rgba(0,0,0,0.02) !important;
}
</style>

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
						<p class="text-sm text-slate-400">Website chính thức: <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" class="text-brand-primary hover:underline font-bold"><?php echo esc_html( $website ); ?></a></p>
					<?php endif; ?>
					<?php
					$map_url = get_field( 'google_map_url', $school_id ) ?: get_field( 'map_url', $school_id );
					if ( ! $map_url ) {
						$map_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $school_title . ' ' . ( $address ?: '' ) );
					}
					?>
					<p class="text-sm text-slate-400">Bản đồ: <a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer" class="text-brand-accent hover:underline font-bold">📍 Xem đường đi trên Google Maps</a></p>
				</div>

				<div class="flex flex-col gap-2 w-full md:w-auto">
					<a href="#register" class="bg-brand-accent text-white text-center px-6 py-3 rounded-lg font-bold shadow-md hover:bg-[#e06e00] transition-all text-sm min-h-[44px] flex items-center justify-center">Đăng Ký Nhận Tư Vấn</a>
					<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="border border-slate-200 text-slate-700 text-center px-6 py-3 rounded-lg font-semibold hover:bg-slate-50 transition-all text-sm min-h-[44px] flex items-center justify-center">Hotline: <?php echo esc_html( $hotline ); ?></a>
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
						// Fallback query if meta relationships don't exist yet
						$programs_query = new WP_Query( [
							'post_type' => 'program',
							'meta_query' => [
								'relation' => 'AND',
								[
									'key' => LTDH_META_SCHOOL_REL,
									'value' => $school_id,
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
							$major_rel_id = get_field( LTDH_META_MAJOR_REL, $prog_id );
							$major_name = $major_rel_id ? get_the_title( $major_rel_id ) : 'Mời tư vấn';
							$major_thumb = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium' ) : '';
						if ( ! $major_thumb ) {
							$major_thumb = ltdh_get_fallback_image( 'program' );
						}
							?>
							<?php
							$status = get_post_meta( $prog_id, LTDH_META_ADMISSION_STATUS, true ) ?: 'tuyen-sinh';
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
							<div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
								<div class="flex items-center gap-4 flex-1">
									<div class="h-16 w-16 bg-slate-200 bg-cover bg-center rounded-lg shrink-0" style="background-image: url('<?php echo esc_url( $major_thumb ); ?>'); font-size: 0;"></div>
									<div class="space-y-1 flex-1 min-w-0">
										<div class="flex items-center gap-2 flex-wrap">
											<h4 class="font-extrabold text-slate-800 text-base hover:text-[#00308b] transition-colors leading-snug">
												<a href="<?php the_permalink(); ?>"><?php echo esc_html( $clean_title ); ?></a>
											</h4>
											<?php if ( $type_name ) : ?>
												<a href="<?php the_permalink(); ?>" class="bg-orange-50 text-orange-600 text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-wider hover:bg-orange-100 transition-all"><?php echo esc_html( $type_name ); ?></a>
											<?php endif; ?>
										</div>
										
										<div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-500 pt-1">
											<p>Ngành học: 
												<?php if ( $major_rel_id ) : ?>
													<a href="<?php echo esc_url( get_permalink( $major_rel_id ) ); ?>" class="font-bold text-brand-primary hover:underline"><?php echo esc_html( $major_name ); ?></a>
												<?php else : ?>
													<span class="font-semibold text-slate-700"><?php echo esc_html( $major_name ); ?></span>
												<?php endif; ?>
											</p>
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
							$m_id = get_field( LTDH_META_MAJOR_REL, $p_id );
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
									'key'     => LTDH_META_SCHOOL_REL,
									'value'   => $school_id,
									'compare' => '=',
								],
							],
							'fields'         => 'ids',
						] );
						foreach ( $linked_programs as $p_id ) {
							$m_id = get_field( LTDH_META_MAJOR_REL, $p_id );
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
							<a href="<?php the_permalink(); ?>" class="flex items-center gap-3 p-3 border border-slate-100 rounded-lg hover:border-brand-primary hover:shadow-sm transition-all bg-white">
								<?php 
								$major_thumb = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
								if ( ! $major_thumb ) {
									$major_thumb = ltdh_get_fallback_image( 'program' );
								}
								?>
								<img src="<?php echo esc_url( $major_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="h-12 w-12 rounded-lg object-cover shrink-0 bg-slate-50 border border-slate-100">
								<div class="min-w-0 flex-1">
									<h4 class="font-bold text-slate-800 text-sm mb-0.5 truncate"><?php the_title(); ?></h4>
									<span class="text-xs text-slate-400 block">Mã ngành: <?php echo esc_html( get_field( 'major_code' ) ?: 'Đang cập nhật' ); ?></span>
								</div>
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
					<section id="register" class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
						<h3 class="text-base font-extrabold text-slate-900 mb-1">Đăng ký vào <?php the_title(); ?></h3>
						<p class="text-xs text-slate-500 mb-4">Nhận tư vấn hồ sơ miễn phí, hỗ trợ xử lý thủ tục nhập học nhanh chóng.</p>
						
						<?php 
						ltdh_render_consultation_form( [
							'current_school_id' => $school_id,
							'referral_source'   => get_permalink(),
						] );
					?>
					</section>
 
					<!-- CONTACT INFO CARD -->
					<div class="relative bg-gradient-to-tr from-[#0E2038] to-brand-primary text-white rounded-lg p-6 text-center shadow-lg overflow-hidden border border-slate-800">
						<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 16px 16px;"></div>
						<span class="text-xs text-brand-accent font-extrabold uppercase tracking-wider block mb-1">Văn phòng tuyển sinh</span>
						<h4 class="font-display font-black text-xl md:text-2xl mb-4"><?php echo esc_html( $hotline ); ?></h4>
						<div class="flex gap-2 relative z-10">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex-1 bg-brand-accent text-white py-3.5 rounded-lg font-bold text-xs hover:bg-[#e06e00] transition-all min-h-[44px] flex items-center justify-center shadow-sm shadow-brand-accent/20">Gọi Ngay</a>
							<a href="<?php echo esc_url( $global_zalo ); ?>" class="flex-1 bg-white/10 text-white border border-white/20 py-3.5 rounded-lg font-bold text-xs hover:bg-white/20 transition-all min-h-[44px] flex items-center justify-center">Chat Zalo</a>
						</div>
					</div>

				</div>
			</div>
		</div>

	</div>
</main>

<?php
get_footer();
