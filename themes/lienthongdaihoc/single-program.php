<?php
/**
 * Single Program Template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$program_id = get_the_ID();
$school_id  = get_field( 'school_relationship', $program_id );
$major_id   = get_field( 'major_relationship', $program_id );

// Retrieve school, major and global fields
$school_title = $school_id ? get_the_title( $school_id ) : '';
$major_title  = $major_id ? get_the_title( $major_id ) : '';

$tuition         = get_field( 'tuition_fee', $program_id );
$duration        = get_field( 'duration', $program_id );
$campus          = get_field( 'campus_info', $program_id );
$requirements    = get_field( 'admission_requirements', $program_id );
$documents       = get_field( 'required_documents', $program_id );
$enrollment      = get_field( 'enrollment_period', $program_id );
$program_hotline = get_field( 'hotline_override', $program_id ) ?: ( $school_id ? get_field( 'hotline', $school_id ) : '' ) ?: get_field( 'global_hotline', 'options' );
$benefits        = get_field( 'program_benefits', $program_id );
$opportunities   = get_field( 'career_opportunities', $program_id );
$why_choose      = get_field( 'why_choose_us', $program_id );
$faqs            = get_field( 'faq', $program_id );

$global_zalo = get_field( 'global_zalo_url', 'options' ) ?: 'https://zalo.me';
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
		
		<!-- SECTION 1: HERO -->
		<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8 mb-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
				<div class="lg:col-span-8">
					<span class="inline-block bg-teal-50 text-brand-primary text-sm font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-3">
						<?php echo esc_html( $school_title ); ?>
					</span>
					<h1 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight mb-4">
						<?php the_title(); ?>
					</h1>
					<p class="text-slate-600 mb-6 text-base max-w-2xl">
						<?php echo esc_html( get_the_excerpt() ?: 'Chương trình đào tạo chất lượng cao liên kết trực tiếp với trường ' . $school_title . ' nhằm mang đến lộ trình tốt nhất cho sinh viên.' ); ?>
					</p>
					
					<div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4 border-t border-slate-100">
						<div>
							<span class="text-sm text-slate-400 block mb-0.5">Học phí chỉ từ</span>
							<span class="font-display font-bold text-brand-primary text-sm md:text-base"><?php echo esc_html( $tuition ?: 'Liên hệ' ); ?></span>
						</div>
						<div>
							<span class="text-sm text-slate-400 block mb-0.5">Thời gian học</span>
							<span class="font-display font-bold text-slate-800 text-sm md:text-base"><?php echo esc_html( $duration ?: '1.5 - 2 năm' ); ?></span>
						</div>
						<div>
							<span class="text-sm text-slate-400 block mb-0.5">Cơ sở học</span>
							<span class="font-display font-bold text-slate-800 text-sm md:text-base"><?php echo esc_html( $campus ?: 'Online / Cơ sở' ); ?></span>
						</div>
						<div>
							<span class="text-sm text-slate-400 block mb-0.5">Hạn hồ sơ</span>
							<span class="font-display font-bold text-brand-accent text-sm md:text-base"><?php echo esc_html( $enrollment ?: 'Đang nhận hồ sơ' ); ?></span>
						</div>
					</div>
				</div>
				<div class="lg:col-span-4 flex flex-col gap-3">
					<a href="#register" class="w-full text-center bg-brand-primary text-white py-3 rounded-xl font-bold shadow-md shadow-brand-primary/20 hover:bg-teal-700 transition-all">
						Đăng Ký Tư Vấn Ngay
					</a>
					<a href="<?php echo esc_url( $global_zalo ); ?>" class="w-full text-center bg-blue-500 text-white py-3 rounded-xl font-bold shadow-md shadow-blue-500/20 hover:bg-blue-600 transition-all">
						Trao đổi qua Zalo
					</a>
				</div>
			</div>
		</section>

		<!-- CONTENT GRID -->
		<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
			<!-- Main Column -->
			<div class="lg:col-span-2 space-y-8">
				
				<!-- SECTION 2: PROGRAM OVERVIEW -->
				<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Tổng quan chương trình</h2>
					<div class="prose prose-slate max-w-none text-slate-600 text-sm md:text-base">
						<?php the_content(); ?>
					</div>
					<?php if ( $benefits ) : ?>
						<div class="mt-6 bg-teal-50/50 p-4 rounded-xl border border-teal-100/50">
							<h3 class="text-teal-800 font-bold text-base mb-2">Quyền lợi nổi bật</h3>
							<div class="prose prose-teal max-w-none text-slate-600 text-sm">
								<?php echo wp_kses_post( $benefits ); ?>
							</div>
						</div>
					<?php endif; ?>
				</section>

				<!-- SECTION 3: SCHOOL INFORMATION -->
				<?php if ( $school_id ) : ?>
					<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Thông tin trường đào tạo</h2>
						<div class="flex flex-col sm:flex-row gap-4 items-start mb-4">
							<?php 
							$logo_id = get_field( 'logo', $school_id );
							if ( $logo_id ) :
								echo wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'h-16 w-16 object-contain rounded-lg border border-slate-100 p-1 bg-white' ] );
							endif;
							?>
							<div>
								<h3 class="font-bold text-lg text-slate-900"><?php echo esc_html( $school_title ); ?></h3>
								<p class="text-sm text-slate-500 mt-1">Website: <a href="<?php echo esc_url( get_field( 'website', $school_id ) ); ?>" target="_blank" class="text-brand-primary"><?php echo esc_html( get_field( 'website', $school_id ) ); ?></a></p>
								<p class="text-sm text-slate-500">Địa chỉ: <?php echo esc_html( get_field( 'address', $school_id ) ); ?></p>
							</div>
						</div>
						<div class="prose prose-slate max-w-none text-sm text-slate-600">
							<?php echo wp_kses_post( get_post_field( 'post_content', $school_id ) ); ?>
						</div>
						<div class="mt-4 pt-4 border-t border-slate-100">
							<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="text-brand-primary font-semibold text-sm hover:underline">
								Xem chi tiết về trường & các chương trình khác →
							</a>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION 4: MAJOR INFORMATION -->
				<?php if ( $major_id ) : ?>
					<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Thông tin ngành học</h2>
						<h3 class="font-bold text-lg text-slate-900 mb-2"><?php echo esc_html( $major_title ); ?> (Mã ngành: <?php echo esc_html( get_field( 'major_code', $major_id ) ); ?>)</h3>
						<div class="prose prose-slate max-w-none text-sm text-slate-600 mb-4">
							<?php echo wp_kses_post( get_post_field( 'post_content', $major_id ) ); ?>
						</div>
						<?php if ( $opportunities ) : ?>
							<h4 class="font-semibold text-slate-800 text-sm mt-4 mb-1">Cơ hội nghề nghiệp</h4>
							<div class="prose prose-slate max-w-none text-sm text-slate-500">
								<?php echo wp_kses_post( $opportunities ); ?>
							</div>
						<?php endif; ?>
						<div class="mt-4 pt-4 border-t border-slate-100">
							<a href="<?php echo esc_url( get_permalink( $major_id ) ); ?>" class="text-brand-primary font-semibold text-sm hover:underline">
								Tìm hiểu thêm định hướng ngành <?php echo esc_html( $major_title ); ?> →
							</a>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION 5: ADMISSION REQUIREMENTS -->
				<?php if ( $requirements ) : ?>
					<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Điều kiện xét tuyển</h2>
						<div class="prose prose-slate max-w-none text-slate-600 text-sm md:text-base">
							<?php echo wp_kses_post( $requirements ); ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION 6: TUITION & SECTION 7: DURATION -->
				<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
					<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Học phí & Thời gian học</h2>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div class="bg-slate-50 p-4 rounded-xl">
							<h3 class="font-bold text-slate-800 mb-2">Học phí chi tiết</h3>
							<p class="text-slate-600 text-sm"><?php echo esc_html( $tuition ?: 'Liên hệ ban tuyển sinh để nhận biểu phí và chính sách đóng học phí theo đợt.' ); ?></p>
						</div>
						<div class="bg-slate-50 p-4 rounded-xl">
							<h3 class="font-bold text-slate-800 mb-2">Thời gian học tập</h3>
							<p class="text-slate-600 text-sm"><?php echo esc_html( $duration ?: 'Lộ trình từ 1.5 - 2 năm tùy thuộc số lượng tín chỉ được miễn giảm khi nhập học.' ); ?></p>
						</div>
					</div>
				</section>

				<!-- SECTION 8: DOCUMENTS REQUIRED -->
				<?php if ( $documents ) : ?>
					<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Hồ sơ xét tuyển cần thiết</h2>
						<div class="prose prose-slate max-w-none text-slate-600 text-sm">
							<?php echo wp_kses_post( $documents ); ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION 9: FAQ -->
				<?php if ( ! empty( $faqs ) ) : ?>
					<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Câu hỏi thường gặp</h2>
						<div class="space-y-4">
							<?php foreach ( $faqs as $index => $item ) : ?>
								<div class="border-b border-slate-100 pb-4 last:border-0 last:pb-0">
									<h4 class="font-semibold text-slate-800 text-base mb-1.5 flex items-start gap-2">
										<span class="bg-teal-100 text-teal-800 text-sm px-1.5 py-0.5 rounded font-black">Q</span>
										<span><?php echo esc_html( $item['question'] ); ?></span>
									</h4>
									<p class="text-slate-600 text-sm pl-7 leading-relaxed"><?php echo esc_html( $item['answer'] ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<!-- SECTION 10: RELATED PROGRAMS -->
				<?php
				$related_query = new WP_Query( [
					'post_type'      => 'program',
					'posts_per_page' => 3,
					'post__not_in'   => [ $program_id ],
					'meta_query'     => [
						'relation' => 'OR',
						[
							'key'     => 'major_relationship',
							'value'   => $major_id,
							'compare' => '=',
						],
						[
							'key'     => 'school_relationship',
							'value'   => $school_id,
							'compare' => '=',
						]
					]
				] );

				if ( $related_query->have_posts() ) :
				?>
					<section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
						<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-4 mb-4">Chương trình liên quan</h2>
						<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
							<?php 
							while ( $related_query->have_posts() ) : 
								$related_query->the_post();
								$rel_school_id = get_field( 'school_relationship' );
								$rel_school = $rel_school_id ? get_the_title( $rel_school_id ) : '';
							?>
								<a href="<?php the_permalink(); ?>" class="group block border border-slate-100 rounded-xl p-4 hover:border-brand-primary hover:shadow-md transition-all bg-white">
									<span class="text-sm text-slate-400 block mb-1 font-medium"><?php echo esc_html( $rel_school ); ?></span>
									<h4 class="font-bold text-slate-800 text-sm group-hover:text-brand-primary transition-colors line-clamp-2"><?php the_title(); ?></h4>
									<div class="mt-3 flex justify-between items-center text-sm text-slate-500 border-t border-slate-50 pt-2">
										<span>Học phí: <?php echo esc_html( get_field( 'tuition_fee' ) ?: 'Liên hệ' ); ?></span>
									</div>
								</a>
							<?php 
							endwhile; 
							wp_reset_postdata();
							?>
						</div>
					</section>
				<?php endif; ?>

			</div>

			<!-- Sidebar Column -->
			<div class="lg:col-span-1">
				<div class="sticky top-24 space-y-6">
					
					<!-- SECTION 11: CONSULTATION FORM -->
					<section id="register" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
						<h3 class="text-lg font-bold text-slate-900 mb-2">Đăng ký tư vấn miễn phí</h3>
						<p class="text-sm text-slate-500 mb-4">Hãy để lại thông tin, ban tư vấn tuyển sinh sẽ liên hệ và giải đáp lộ trình cụ thể cho bạn trong vòng 15 phút.</p>
						
						<!-- Simple Dynamic form utilizing the CF7 dynamic layout if CF7 plugin exists, else native fallback -->
						<?php 
						if ( function_exists( 'wpcf7_contact_form_html' ) ) :
							// Renders Contact Form 7 form named 'Consultation Form'
							// In a real installation we select the form shortcode or ID dynamically
							echo do_shortcode( '[contact-form-7 id="consultation-form" title="Form Tư vấn"]' );
						else :
						?>
							<!-- Fallback Form if Contact Form 7 is not registered yet -->
							<form action="#" method="POST" class="space-y-4">
								<input type="hidden" name="current_program_id" value="<?php echo esc_attr( $program_id ); ?>">
								<input type="hidden" name="current_school_id" value="<?php echo esc_attr( $school_id ); ?>">
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
								<div>
									<label class="block text-sm font-semibold text-slate-600 mb-1">Email (Tùy chọn)</label>
									<input type="email" name="your-email" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-brand-primary focus:outline-none" placeholder="Địa chỉ email">
								</div>
								
								<button type="submit" class="w-full bg-brand-primary text-white py-2.5 rounded-lg text-sm font-bold shadow-md shadow-brand-primary/10 hover:bg-teal-700 transition-all mt-2">
									Gửi Thông Tin Ngay
								</button>
							</form>
						<?php endif; ?>
					</section>

					<!-- SECTION 13: PHONE CTA & SECTION 14: ZALO CTA sidebar cards -->
					<div class="bg-brand-primary/5 border border-brand-primary/10 rounded-2xl p-6 text-center">
						<span class="text-sm text-brand-primary font-bold uppercase tracking-wider block mb-1">Cần hỗ trợ trực tiếp?</span>
						<h4 class="font-display font-black text-2xl text-slate-800 mb-4"><?php echo esc_html( $program_hotline ); ?></h4>
						<div class="flex gap-2">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $program_hotline ) ); ?>" class="flex-1 bg-brand-primary text-white py-2 rounded-lg font-semibold text-sm hover:bg-teal-700 transition-all">Gọi Điện</a>
							<a href="<?php echo esc_url( $global_zalo ); ?>" class="flex-1 bg-white border border-brand-primary text-brand-primary py-2 rounded-lg font-semibold text-sm hover:bg-brand-primary/5 transition-all">Chat Zalo</a>
						</div>
					</div>

				</div>
			</div>
		</div>

	</div>
</main>

<!-- SECTION 12: STICKY CTA (Mobile Bottom Sticky Bar) -->
<div class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-100 shadow-[0_-4px_10px_rgba(0,0,0,0.04)] py-3 px-4 flex items-center justify-between md:hidden">
	<div class="flex-1 mr-3">
		<span class="text-[10px] text-slate-400 block font-medium uppercase leading-none mb-1">Đăng ký lớp khai giảng</span>
		<h4 class="font-bold text-slate-800 text-sm truncate leading-none"><?php the_title(); ?></h4>
	</div>
	<a href="#register" class="bg-brand-primary text-white px-5 py-2 rounded-xl text-sm font-extrabold shadow-md shadow-brand-primary/20 hover:bg-teal-700 transition-all">
		Đăng Ký Học
	</a>
</div>

<?php
get_footer();
