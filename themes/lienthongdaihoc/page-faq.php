<?php
/**
 * Template Name: Câu hỏi thường gặp
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main py-12 bg-slate-50">
	<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
		
		<h1 class="text-3xl font-black text-slate-900 text-center mb-4">CÂU HỎI THƯỜNG GẶP</h1>
		<p class="text-slate-500 text-sm text-center mb-8 max-w-lg mx-auto">Giải đáp tất cả thắc mắc thường gặp về quy trình tuyển sinh, học phí, hình thức học tập trực tuyến (Online) và bằng cấp.</p>

		<div class="space-y-4">
			<?php
			$mock_faqs = [
				[ 'q' => 'Bằng tốt nghiệp đại học từ xa có ghi chữ "Từ xa" không?', 'a' => 'Theo Thông tư 27/2019/TT-BGDĐT của Bộ Giáo dục và Đào tạo từ ngày 1/3/2020, bằng đại học sẽ không còn ghi hình thức đào tạo (như Từ xa, Vừa học vừa làm, Chính quy) trên văn bằng tốt nghiệp. Tất cả phôi bằng đều có giá trị tương đương tốt nghiệp chính quy.' ],
				[ 'q' => 'Thời gian hoàn thành chương trình liên thông/văn bằng 2 là bao lâu?', 'a' => 'Thời gian đào tạo dao động từ 1.5 đến 2 năm. Thời gian cụ thể tùy thuộc vào số lượng tín chỉ bạn được miễn giảm dựa trên bảng điểm tốt nghiệp trung cấp, cao đẳng hoặc văn bằng 1 đã có.' ],
				[ 'q' => 'Hình thức học trực tuyến (Online) diễn ra như thế nào?', 'a' => 'Học viên sẽ học qua hệ thống quản lý học tập E-Learning của nhà trường. Bạn có thể tự học qua video bài giảng, tài liệu slide mọi lúc mọi nơi và tham gia ôn tập trực tuyến với giảng viên vào cuối tuần.' ],
				[ 'q' => 'Bằng đại học liên thông/văn bằng 2 có đủ điều kiện thi cao học không?', 'a' => 'Hoàn toàn đủ điều kiện. Tấm bằng tốt nghiệp do các đại học liên kết cấp có đầy đủ giá trị pháp lý để bạn đăng ký thi thạc sĩ, cao học, thi công chức nhà nước hoặc nâng bậc lương.' ],
				[ 'q' => 'Hồ sơ tuyển sinh gồm những giấy tờ gì?', 'a' => 'Hồ sơ cơ bản bao gồm: Phiếu đăng ký theo mẫu của trường, Bản sao công chứng Bằng tốt nghiệp + Bảng điểm cấp cao nhất, Bản sao CCCD, Ảnh 3x4. Bạn sẽ được chuyên viên tư vấn gửi mẫu và hướng dẫn chi tiết.' ]
			];

			foreach ( $mock_faqs as $faq ) :
			?>
				<details class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden group">
					<summary class="flex justify-between items-center font-bold text-slate-800 p-5 cursor-pointer list-none hover:bg-slate-50 select-none text-base">
						<span><?php echo esc_html( $faq['q'] ); ?></span>
						<span class="text-slate-400 group-open:rotate-180 transition-transform">▼</span>
					</summary>
					<div class="p-5 border-t border-slate-100 text-slate-600 text-sm leading-relaxed bg-slate-50/50">
						<?php echo esc_html( $faq['a'] ); ?>
					</div>
				</details>
			<?php endforeach; ?>
		</div>

	</div>
</main>

<?php
get_footer();
