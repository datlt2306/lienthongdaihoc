<?php
/**
 * Template Part: Quy Trình Tuyển Sinh & Nhập Học (Brand Color & Responsive Adaptable)
 * Optimized for Brand Consistency & Narrow Container Layouts (Single Program / School)
 *
 * @package LTDH
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = [
	'title'      => 'Quy trình tuyển sinh & Nhập học',
	'subtitle'   => 'Lộ trình 4 bước xét tuyển từ đăng ký đến khi chính thức nhập học tại nhà trường',
	'section_id' => 'quy-trinh-tuyen-sinh',
	'cta_text'   => 'Đăng ký tư vấn lộ trình ngay',
	'cta_link'   => '#register',
	'show_cta'   => true,
	'card_bg'    => 'bg-white',
	'columns'    => null, // Auto-detects 2 cols for single-program/school, 4 cols for full-width
];

$args = isset( $args ) && is_array( $args ) ? wp_parse_args( $args, $defaults ) : $defaults;

// Determine grid column layout based on page context or explicit argument
$columns = ! empty( $args['columns'] ) ? intval( $args['columns'] ) : ( is_singular( [ 'program', 'school', 'major' ] ) ? 2 : 4 );
$grid_class = ( 2 === $columns ) ? 'grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5' : 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5';

$steps = [
	[
		'step'        => '01',
		'title'       => 'Đăng ký & Kiểm tra điều kiện',
		'desc'        => 'Điền thông tin bằng cấp hiện tại (Trung cấp / Cao đẳng / VB1) để Ban tuyển sinh thẩm định điều kiện và dự kiến số tín chỉ được miễn giảm.',
		'tag'         => '1 phút online',
		'detail_meta' => 'Miễn phí 100%',
		'icon_svg'    => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
	],
	[
		'step'        => '02',
		'title'       => 'Tư vấn 1:1 & Chọn lộ trình',
		'desc'        => 'Chuyên viên tuyển sinh gọi điện/Zalo hỗ trợ chọn ngành phù hợp, giải đáp học phí, thời gian học và gửi danh mục hồ sơ mẫu.',
		'tag'         => 'Tư vấn miễn phí',
		'detail_meta' => 'Phản hồi trong 15p',
		'icon_svg'    => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>',
	],
	[
		'step'        => '03',
		'title'       => 'Nộp hồ sơ xét tuyển',
		'desc'        => 'Hoàn thiện bộ hồ sơ (bản photo công chứng bằng & bảng điểm). Nộp linh hoạt qua Bưu điện, nộp trực tiếp hoặc gửi bản scan trước.',
		'tag'         => 'Thủ tục đơn giản',
		'detail_meta' => 'Linh hoạt hình thức',
		'icon_svg'    => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
	],
	[
		'step'        => '04',
		'title'       => 'Nhận thông báo & Nhập học',
		'desc'        => 'Nhận Giấy báo trúng tuyển chính thức từ nhà trường, cấp tài khoản hệ thống E-Learning (LMS) và bắt đầu học tập ngay.',
		'tag'         => 'Bằng chuẩn Bộ GD&ĐT',
		'detail_meta' => 'Học online linh hoạt',
		'icon_svg'    => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>',
	],
];
?>

<section id="<?php echo esc_attr( $args['section_id'] ); ?>" class="scroll-mt-36 md:scroll-mt-40 <?php echo esc_attr( $args['card_bg'] ); ?> rounded-xl shadow-2xs border border-slate-200/80 p-4 md:p-6 transition-all">
	<!-- Section Header -->
	<div class="border-b border-slate-100 pb-4 mb-5 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
		<div>
			<div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-blue-50 text-[#00308b] text-[11px] font-extrabold uppercase tracking-wider rounded-md mb-2">
				<span>Quy trình tuyển sinh</span>
			</div>
			<h2 class="text-xl md:text-2xl font-bold text-slate-900 leading-tight">
				<?php echo esc_html( $args['title'] ); ?>
			</h2>
			<p class="text-xs md:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
				<?php echo esc_html( $args['subtitle'] ); ?>
			</p>
		</div>

		<!-- Step Count Badge (Brand Color) -->
		<div class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 rounded-lg text-xs font-bold text-slate-700 border border-slate-200 shrink-0">
			<span class="w-2 h-2 rounded-full bg-[#00308b]"></span>
			<span>4 Bước xét tuyển</span>
		</div>
	</div>

	<!-- STEPS GRID CONTAINER -->
	<div class="grid <?php echo esc_attr( $grid_class ); ?> relative">
		<?php foreach ( $steps as $index => $step_item ) : 
			$is_last = ( $index === count( $steps ) - 1 );
		?>
			<div class="group relative flex flex-col justify-between bg-slate-50/60 hover:bg-white border border-slate-200/80 hover:border-blue-300 rounded-xl p-4 md:p-5 transition-all duration-200 shadow-2xs hover:shadow-sm">
				
				<!-- Top Content -->
				<div>
					<div class="flex items-center justify-between gap-2 mb-3">
						<!-- Brand Primary Step Badge -->
						<div class="w-9 h-9 rounded-lg bg-[#00308b] text-white font-extrabold text-xs flex items-center justify-center shadow-xs shrink-0">
							<?php echo esc_html( $step_item['step'] ); ?>
						</div>

						<!-- Clean Neutral Tag Pill -->
						<span class="inline-block text-[11px] font-bold text-slate-600 bg-white border border-slate-200/80 px-2.5 py-0.5 rounded-md shadow-2xs shrink-0">
							<?php echo esc_html( $step_item['tag'] ); ?>
						</span>
					</div>

					<!-- Step Title -->
					<h3 class="font-bold text-slate-900 text-base md:text-lg mb-1.5 group-hover:text-[#00308b] transition-colors leading-snug">
						<?php echo esc_html( $step_item['title'] ); ?>
					</h3>

					<!-- Step Description -->
					<p class="text-sm md:text-base text-slate-600 leading-relaxed font-normal">
						<?php echo esc_html( $step_item['desc'] ); ?>
					</p>
				</div>

				<!-- Step Card Footer Meta -->
				<div class="mt-4 pt-3 border-t border-slate-200/70 flex items-center justify-between text-xs font-medium text-slate-500">
					<span class="inline-flex items-center gap-1.5 text-[11px] text-slate-600">
						<span class="text-[#00308b]"><?php echo $step_item['icon_svg']; ?></span>
						<span><?php echo esc_html( $step_item['detail_meta'] ); ?></span>
					</span>

					<?php if ( ! $is_last ) : ?>
						<span class="text-slate-300 group-hover:text-[#00308b] transition-colors font-bold text-xs">
							➔
						</span>
					<?php else : ?>
						<span class="text-[11px] font-bold text-[#00308b] bg-blue-50 px-2 py-0.5 rounded">
							Hoàn tất
						</span>
					<?php endif; ?>
				</div>

				<!-- Top Accent Line (Brand Primary) -->
				<div class="absolute top-0 left-4 right-4 h-[2px] rounded-t-full bg-[#00308b] opacity-0 group-hover:opacity-100 transition-opacity"></div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Bottom Action Bar -->
	<?php if ( $args['show_cta'] ) : ?>
		<div class="mt-5 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-blue-50/50 p-3.5 md:p-4 rounded-xl border border-blue-100/80">
			<div class="flex items-center gap-3 text-center sm:text-left">
				<div class="w-9 h-9 rounded-lg bg-[#00308b] text-white flex items-center justify-center shrink-0 shadow-2xs hidden sm:flex">
					<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
				</div>
				<div>
					<h4 class="font-bold text-slate-900 text-sm mb-0.5">Bạn muốn kiểm tra điều kiện & dự kiến số tín chỉ được miễn giảm?</h4>
					<p class="text-xs text-slate-500">Đội ngũ chuyên viên tư vấn sẽ liên hệ hỗ trợ hướng dẫn thông tin chi tiết hoàn toàn miễn phí.</p>
				</div>
			</div>
			
			<a href="<?php echo esc_url( $args['cta_link'] ); ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4.5 py-2.5 bg-[#00308b] hover:bg-[#002266] text-white text-xs font-bold rounded-lg shadow-xs hover:shadow-sm transition-all shrink-0 cursor-pointer">
				<span><?php echo esc_html( $args['cta_text'] ); ?></span>
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
			</a>
		</div>
	<?php endif; ?>
</section>
