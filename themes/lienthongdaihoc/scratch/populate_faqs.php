<?php
require_once __DIR__ . '/../../../../wp-load.php';

if ( function_exists( 'update_field' ) ) {
	$faqs = [
		[
			'question' => 'Bằng tốt nghiệp Đại học Liên thông / Từ xa có ghi hình thức đào tạo không?',
			'answer'   => 'Theo Thông tư 27/2019/TT-BGDĐT của Bộ Giáo dục và Đào tạo, từ ngày 01/03/2020 trên bằng tốt nghiệp Đại học không còn ghi hình thức đào tạo (như Từ xa hay Vừa học vừa làm). Tấm bằng do các trường đại học đối tác cấp có giá trị pháp lý tương đương bằng cử nhân/kỹ sư chính quy, được thi cao học, công chức và nâng bậc lương.'
		],
		[
			'question' => 'Thời gian đào tạo Liên thông Đại học mất bao lâu?',
			'answer'   => 'Thời gian đào tạo trung bình từ 1.5 đến 2 năm tùy theo khối ngành. Đặc biệt, học viên đã có bằng Trung cấp hoặc Cao đẳng đúng ngành sẽ được xét miễn giảm các học phần tương đương để rút ngắn thời gian hoàn thành chương trình.'
		],
		[
			'question' => 'Hình thức học trực tuyến (Online/Từ xa) diễn ra như thế nào?',
			'answer'   => 'Học viên học hoàn toàn qua hệ thống E-Learning trực tuyến của nhà trường. Bạn có thể tự chủ thời gian học mọi lúc mọi nơi qua bài giảng video, slide và tham gia trao đổi trực tiếp với giảng viên qua các buổi ôn tập online cuối tuần.'
		],
		[
			'question' => 'Hồ sơ xét tuyển Liên thông Đại học bao gồm những gì?',
			'answer'   => 'Hồ sơ xét tuyển cơ bản gồm: 01 Phiếu đăng ký theo mẫu của trường, 02 bản sao công chứng Bằng + Bảng điểm tốt nghiệp Cao đẳng/Trung cấp, 01 bản sao CCCD và 02 ảnh 3x4. Chuyên viên tuyển sinh sẽ hỗ trợ gửi mẫu và hướng dẫn chuẩn bị chi tiết.'
		],
		[
			'question' => 'Bằng Đại học Liên thông có đủ điều kiện đăng ký học Cao học / Thạc sĩ không?',
			'answer'   => 'Hoàn toàn đủ điều kiện. Tấm bằng đại học sau khi tốt nghiệp có đầy đủ tư cách pháp lý để bạn tiếp tục đăng ký thi tuyển Thạc sĩ, Cao học tại tất cả các trường Đại học trên toàn quốc hoặc du học nước ngoài.'
		]
	];
	update_field( 'faq_items', $faqs, 'option' );
	echo "SUCCESSfully pre-populated 5 FAQ items in WP Admin options!";
} else {
	echo "ACF not active.";
}
