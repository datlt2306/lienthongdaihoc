<?php
/**
 * Template Name: Giới thiệu
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main py-12 bg-slate-50">
	<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
		
		<article class="bg-white rounded-xl p-8 md:p-12 shadow-sm border border-slate-200 space-y-6">
			<span class="inline-block bg-blue-50 text-[#00308b] text-sm font-bold px-3.5 py-1 rounded-lg uppercase tracking-wider">
				Về chúng tôi
			</span>
			<h1 class="text-3xl md:text-4xl font-black text-slate-900 leading-tight">CỔNG THÔNG TIN TUYỂN SINH LIÊN THÔNG ĐẠI HỌC</h1>
			
			<?php
			$about_intro     = '';
			$about_mission   = '';
			$about_vision    = '';
			$about_cv_title  = 'Giá trị cốt lõi';
			$about_cv        = '';
			$about_vs_title  = 'Tầm nhìn chiến lược';
			if ( function_exists( 'get_field' ) ) {
				$about_intro    = get_field( 'about_intro', 'options' ) ?: '';
				$about_mission  = get_field( 'about_mission', 'options' ) ?: '';
				$about_vision   = get_field( 'about_vision', 'options' ) ?: '';
				$about_cv_title = get_field( 'about_core_values_title', 'options' ) ?: $about_cv_title;
				$about_cv       = get_field( 'about_core_values', 'options' ) ?: '';
				$about_vs_title = get_field( 'about_vision_title', 'options' ) ?: $about_vs_title;
			}

			// Fallback if ACF not populated.
			if ( empty( $about_intro ) ) {
				$about_intro = 'lienthongdaihoc.com là cổng thông tin tìm kiếm, đối chiếu và tư vấn tuyển sinh đại học trực tuyến liên thông, văn bằng 2 và đào tạo từ xa hàng đầu tại Việt Nam.';
			}
			if ( empty( $about_mission ) ) {
				$about_mission = 'Sứ mệnh của chúng tôi là trở thành cầu nối tin cậy giữa người học và các trường Đại học danh tiếng, mang lại cơ hội tiếp cận tri thức đại học dễ dàng, linh hoạt và tối ưu hóa chi phí cho người đi làm.';
			}
			if ( empty( $about_vision ) ) {
				$about_vision = 'Hệ thống hỗ trợ tổng hợp thông tin học phí, điều kiện xét tuyển, hồ sơ chuẩn bị của hàng trăm lớp học trực tuyến chính quy, hỗ trợ sinh viên nhanh chóng tìm được lớp học thích hợp nhất.';
			}
			if ( empty( $about_cv ) ) {
				$about_cv = 'Đặt chất lượng thông tin lên hàng đầu. Đảm bảo tính chính quy, minh bạch của dữ liệu tuyển sinh từ các trường.';
			}
			?>

			<div class="prose prose-slate max-w-none text-slate-600 text-base leading-relaxed space-y-4">
				<p class="font-semibold text-slate-800"><?php echo esc_html( $about_intro ); ?></p>
				<p><?php echo esc_html( $about_mission ); ?></p>
				<p><?php echo esc_html( $about_vision ); ?></p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-slate-100">
				<div class="bg-slate-50 p-6 rounded-lg border border-slate-100">
					<h3 class="font-bold text-slate-800 text-lg mb-2"><?php echo esc_html( $about_cv_title ); ?></h3>
					<p class="text-sm text-slate-500"><?php echo esc_html( $about_cv ); ?></p>
				</div>
				<div class="bg-slate-50 p-6 rounded-lg border border-slate-100">
					<h3 class="font-bold text-slate-800 text-lg mb-2"><?php echo esc_html( $about_vs_title ); ?></h3>
					<p class="text-sm text-slate-500">Mở rộng đối tác với hơn 50+ trường đại học chính quy, hỗ trợ giải đáp lộ trình học cho hàng chục ngàn học viên mỗi năm.</p>
				</div>
			</div>
		</article>

	</div>
</main>

<?php
get_footer();
