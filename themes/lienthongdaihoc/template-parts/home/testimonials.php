<?php
/**
 * Home: Student Testimonials Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$testimonials = [];
if ( function_exists( 'get_field' ) ) {
	$testimonials = get_field( 'testimonials', 'options' ) ?: [];
}
if ( empty( $testimonials ) ) {
	// 3 học viên tiêu biểu có phản hồi và hình ảnh thực tế
	$testimonials = [
		[
			'name'         => 'Hoàng Thị Độ',
			'workplace'    => 'Product Owner',
			'role'         => 'Liên thông Công nghệ thông tin',
			'graduation'   => 'Khóa 29.1 - UTC',
			'status'       => 'graduated',
			'status_label' => 'Đã tốt nghiệp',
			'initials'     => 'HĐ',
			'image'        => get_template_directory_uri() . '/assets/images/student-do.jpg',
			'content'      => 'Mình liên thông Cao Đẳng lên đại học. Lịch học được sắp xếp ngoài giờ hành chính, tạo điều kiện thuận lợi cho những người đang đi làm như mình có thể vừa hoàn thành công việc, vừa tiếp tục học tập. Bên cạnh đó, thầy cô luôn nhiệt tình hỗ trợ và tạo điều kiện để sinh viên cân bằng giữa công việc và việc học.',
		],
		[
			'name'         => 'Phạm Văn Dũng',
			'workplace'    => 'Leader SEO tại CAR BOSS Việt Nam',
			'role'         => 'Liên thông ngành TMDT',
			'graduation'   => 'Khóa K10 - TMU',
			'status'       => 'studying',
			'status_label' => 'Sắp tốt nghiệp',
			'initials'     => 'PD',
			'image'        => get_template_directory_uri() . '/assets/images/student-dung.jpg',
			'content'      => 'Nhờ thầy Đạt hướng dẫn tận tình từ khâu tìm hiểu chương trình, chuẩn bị hồ sơ đến hoàn tất thủ tục nhập học, em đã đăng ký học liên thông lên Đại học TMU thuận lợi và nhanh chóng. Thầy luôn giải đáp thắc mắc kịp thời, giúp em yên tâm hơn trong suốt quá trình đăng ký.',
		],
		[
			'name'         => 'Nguyễn Quý Duy',
			'workplace'    => 'Front-End Developer tại VC Corp',
			'role'         => 'Liên thông Công nghệ thông tin',
			'graduation'   => 'Khóa K27 - UTC',
			'status'       => 'graduated',
			'status_label' => 'Đã tốt nghiệp',
			'initials'     => 'QD',
			'image'        => get_template_directory_uri() . '/assets/images/student-duy.jpg',
			'content'      => 'Điều em hài lòng khi học liên thông là lịch học linh hoạt, phù hợp với người vừa học vừa làm. Thời gian đào tạo chỉ khoảng 1 năm 7 tháng, giúp em sớm có bằng Đại học để đáp ứng yêu cầu công việc. Bên cạnh đó, học phí hợp lý cũng là một lợi thế, đặc biệt với các bạn vừa tốt nghiệp Cao đẳng và muốn học tiếp lên Đại học.',
		],
	];
}
$testimonials = array_slice( $testimonials, 0, 3 );
?>

<!-- 7. STUDENT TESTIMONIALS (Đã kiểm chứng thực tế) -->
<section class="py-10 sm:py-16 bg-white relative overflow-hidden">
	<div class="absolute -right-16 -bottom-16 w-64 h-64 bg-brand-accent/10 rounded-full blur-3xl pointer-events-none z-0"></div>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
		<div class="text-center max-w-2xl mx-auto mb-6 sm:mb-8 space-y-2 sm:space-y-3">
			<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">CẢM NHẬN HỌC VIÊN</span>
			<h2 class="text-xl md:text-3xl font-black text-slate-900">Đánh giá của học viên</h2>
			<p class="text-slate-500 text-sm">Chia sẻ thực tế từ người học đã được tư vấn và hỗ trợ chuẩn bị hồ sơ xét tuyển liên thông đại học.</p>
		</div>

		<div class="flex md:grid overflow-x-auto md:overflow-x-visible snap-x snap-mandatory md:snap-none gap-6 pb-4 md:pb-0 no-scrollbar md:grid-cols-3">
			<?php
			foreach ( $testimonials as $t ) :
				$initials = strtoupper( substr( $t['initials'] ?? $t['name'] ?? 'U', 0, 2 ) );
				$avatar_url = '';
				if ( ! empty( $t['image'] ) ) {
					if ( is_array( $t['image'] ) ) {
						$avatar_url = $t['image']['sizes']['medium'] ?? $t['image']['sizes']['thumbnail'] ?? $t['image']['url'] ?? '';
					} elseif ( is_numeric( $t['image'] ) && function_exists( 'wp_get_attachment_image_url' ) ) {
						$avatar_url = wp_get_attachment_image_url( $t['image'], 'medium' ) ?: wp_get_attachment_image_url( $t['image'], 'thumbnail' );
					} else {
						$avatar_url = $t['image'];
					}
				}
				$workplace    = $t['workplace'] ?? $t['company'] ?? $t['job'] ?? '';
				$graduation   = $t['graduation'] ?? $t['school'] ?? $t['batch'] ?? 'Bằng chuẩn Bộ GD&ĐT';
				$status       = $t['status'] ?? 'graduated';
				$is_studying  = in_array( $status, [ 'studying', 'new', 'enrolled' ], true );
				$status_label = $t['status_label'] ?? ( $is_studying ? 'Đang theo học' : 'Đã tốt nghiệp' );
			?>
				<div class="shrink-0 w-[85vw] sm:w-[360px] md:w-auto snap-center bg-white border border-slate-200/90 hover:border-blue-200 rounded-2xl p-5 sm:p-6 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between group">
					<div>
						<!-- Student Profile Header with Large Photo (96px) -->
						<div class="flex items-start gap-4 mb-4">
							<div class="relative shrink-0">
								<?php if ( $avatar_url ) : ?>
									<img class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-2 ring-slate-100 shadow-sm group-hover:scale-105 transition-all duration-200" src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $t['name'] ?? '' ); ?>" loading="lazy" width="96" height="96">
								<?php else : ?>
									<div class="w-20 h-20 sm:w-24 sm:h-24 bg-brand-primary text-white rounded-2xl flex items-center justify-center font-bold text-xl shadow-sm"><?php echo esc_html( $initials ); ?></div>
								<?php endif; ?>

								<?php if ( $is_studying ) : ?>
									<span class="absolute -bottom-1 -right-1 bg-blue-600 text-white p-1 rounded-full ring-2 ring-white shadow-2xs" title="<?php echo esc_attr( $status_label ); ?>">
										<svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
											<path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a10.993 10.993 0 00-.25 2.302c0 1.109.2 2.16.564 3.125a1 1 0 00.916.622h7.04a1 1 0 00.916-.622c.364-.965.564-2.016.564-3.125 0-.796-.09-1.57-.25-2.302l2.644-1.131a1 1 0 000-1.84l-7-3zM10 4.218l4.157 1.782L10 7.782 5.843 6 10 4.218z"/>
										</svg>
									</span>
								<?php else : ?>
									<span class="absolute -bottom-1 -right-1 bg-emerald-500 text-white p-1 rounded-full ring-2 ring-white shadow-2xs" title="Học viên đã tốt nghiệp">
										<svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
											<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
										</svg>
									</span>
								<?php endif; ?>
							</div>
							<div class="min-w-0 flex-1">
								<div class="flex items-center gap-0.5 text-amber-400 mb-1" aria-label="5 trên 5 sao">
									<?php for ( $s = 0; $s < 5; $s++ ) : ?>
										<?php if ( function_exists( 'ltdh_icon' ) ) { ltdh_icon( 'star-fill', 'w-4 h-4 fill-amber-400 text-amber-400 shrink-0' ); } ?>
									<?php endfor; ?>
									<span class="text-xs font-bold text-amber-600 ml-1">5.0</span>
								</div>
								<h3 class="font-bold text-base sm:text-lg text-slate-900 group-hover:text-brand-primary transition-colors leading-snug truncate"><?php echo esc_html( $t['name'] ?? '' ); ?></h3>
								<?php if ( ! empty( $workplace ) ) : ?>
									<p class="text-xs sm:text-[13px] font-semibold text-slate-700 flex items-center gap-1 my-1.5 truncate" title="<?php echo esc_attr( $workplace ); ?>">
										<svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
										</svg>
										<span class="truncate"><?php echo esc_html( $workplace ); ?></span>
									</p>
								<?php endif; ?>
								<p class="text-xs text-brand-primary font-medium mt-0.5 line-clamp-1"><?php echo esc_html( $t['role'] ?? '' ); ?></p>
							</div>
						</div>

						<!-- Quote Body with decorative quote mark -->
						<div class="relative pt-2">
							<svg class="w-8 h-8 text-blue-100 mb-2 -mt-1" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
							<p class="text-sm sm:text-[15px] text-slate-600 leading-relaxed italic">"<?php echo esc_html( $t['content'] ?? '' ); ?>"</p>
						</div>
					</div>

					<!-- Card Bottom / Trust signal with Graduation Batch & University (Mục 2) -->
					<div class="mt-5 pt-3.5 border-t border-slate-200/80 flex items-center justify-between text-xs gap-2">
						<?php if ( $is_studying ) : ?>
							<span class="inline-flex items-center gap-1.5 font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md shrink-0 border border-blue-100/80">
								<span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
								<?php echo esc_html( $status_label ); ?>
							</span>
						<?php else : ?>
							<span class="inline-flex items-center gap-1.5 font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md shrink-0">
								<?php if ( function_exists( 'ltdh_icon' ) ) { ltdh_icon( 'checkmark-circle', 'w-3.5 h-3.5 text-emerald-600 shrink-0' ); } ?>
								Đã tốt nghiệp
							</span>
						<?php endif; ?>
						<span class="text-slate-600 font-medium flex items-center gap-1 truncate" title="<?php echo esc_attr( $graduation ); ?>">
							<?php if ( function_exists( 'ltdh_icon' ) ) { ltdh_icon( 'academic-cap', 'w-3.5 h-3.5 text-brand-primary shrink-0' ); } ?>
							<span class="truncate"><?php echo esc_html( $graduation ); ?></span>
						</span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
