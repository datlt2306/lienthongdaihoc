<?php
/**
 * Template Name: Kiểm tra điều kiện
 *
 * @package lienthongdaihoc
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<main class="min-h-screen bg-gradient-to-b from-slate-50 to-white">
	<!-- Hero Section -->
	<section class="bg-brand-primary text-white py-12 md:py-16">
		<div class="max-w-4xl mx-auto px-4 text-center">
			<h1 class="text-3xl md:text-4xl font-extrabold mb-3">Kiểm tra điều kiện tuyển sinh</h1>
			<p class="text-white/80 text-lg">Tìm chương trình phù hợp trong 60 giây</p>
		</div>
	</section>

	<!-- Checker Container -->
	<div id="eligibility-app" class="max-w-4xl mx-auto px-4 py-8 md:py-12" data-elig-root>

		<!-- Wizard Form -->
		<div id="elig-wizard" class="elig-wizard">
			<?php get_template_part( 'template-parts/eligibility/wizard' ); ?>
		</div>

		<!-- Results (hidden initially) -->
		<div id="elig-results" class="elig-results hidden">
			<?php get_template_part( 'template-parts/eligibility/results' ); ?>
		</div>

	</div>

	<!-- SEO Content -->
	<section class="max-w-4xl mx-auto px-4 py-12">
		<div class="prose prose-slate max-w-none">
			<h2>Kiểm tra điều kiện tuyển sinh đại học</h2>
			<p>Công cụ kiểm tra điều kiện tuyển sinh giúp bạn xác định nhanh chóng mình đủ điều kiện tham gia những chương trình đào tạo nào. Chỉ cần trả lời một vài câu hỏi đơn giản, hệ thống sẽ phân tích và gợi ý những chương trình phù hợp nhất.</p>

			<h3>Các bước kiểm tra</h3>
			<ol>
				<li>Chọn trình độ học vấn hiện tại</li>
				<li>Chọn chuyên ngành đang học hoặc đã tốt nghiệp</li>
				<li>Nhập năm tốt nghiệp</li>
				<li>Chọn ngành mong muốn</li>
				<li>Chọn hệ đào tạo phù hợp</li>
				<li>Chọn cơ sở học</li>
				<li>Xác định ngân sách</li>
			</ol>

			<h3>Hệ đào tạo phù hợp</h3>
			<ul>
				<li><strong>Liên thông:</strong> Dành cho người có bằng Trung cấp/Cao đẳng muốn nâng cấp lên Đại học</li>
				<li><strong>Văn bằng 2:</strong> Dành cho người đã có bằng Đại học muốn học thêm ngành khác</li>
				<li><strong>Từ xa:</strong> Học trực tuyến, phù hợp người đi làm</li>
				<li><strong>Vừa học vừa làm:</strong> Lịch học linh hoạt, phù hợp người đi làm</li>
			</ul>
		</div>
	</section>
</main>

<?php
get_footer();
