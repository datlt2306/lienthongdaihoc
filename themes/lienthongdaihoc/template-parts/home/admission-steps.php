<?php
/**
 * Home: Admission Process Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- 8.5. ADMISSION PROCESS SECTION -->
<section class="py-8 sm:py-12 md:py-16 bg-slate-50 border-t border-slate-100">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<?php
		get_template_part(
			'template-parts/admission-process',
			null,
			[
				'title'      => 'Quy trình xét tuyển Liên thông & Đại học từ xa',
				'subtitle'   => 'Chỉ với 4 bước đơn giản, người đi làm dễ dàng sở hữu bằng Đại học chính quy / chuẩn Bộ GD&ĐT',
				'section_id' => 'quy-trinh-xet-tuyen-home',
				'cta_text'   => 'Kiểm tra điều kiện & Nhận tư vấn ngay',
				'cta_link'   => '/kiem-tra-dieu-kien',
				'card_bg'    => 'bg-white',
			]
		);
		?>
	</div>
</section>
