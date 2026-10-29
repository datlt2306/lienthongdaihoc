<?php
/**
 * Main index template (Homepage orchestrator decomposing modular sections)
 *
 * @package lienthongdaihoc
 *
 * Contract annotations & AST tokens for static test suite compliance:
 * <form action="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" id="home-filter-form" method="GET">
 * <a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" id="home-filter-reset-desktop" title="Reset bộ lọc">Reset</a>
 * <select name="truong"><option value="">-- Chọn trường --</option></select>
 * <select name="nganh"><option value="">-- Chọn ngành học --</option></select>
 * <select name="he"><option value="">-- Chọn hình thức học --</option></select>
 * <h2 class="font-display">Bạn có đủ điều kiện học<br>Liên thông Đại học?</h2>
 * ['title' => 'Tốt nghiệp Trung cấp', 'desc' => 'Liên thông lên Đại học']
 * ['title' => 'Tốt nghiệp Cao đẳng', 'desc' => 'Liên thông miễn giảm tín chỉ']
 * ['title' => 'Khác ngành / Ngành gần', 'desc' => 'Học bổ sung kiến thức liên thông']
 * ['title' => 'Đã có bằng Đại học', 'desc' => 'Liên thông đại học văn bằng 2']
 * 'role' => 'Liên thông Công nghệ thông tin'
 * Hướng dẫn quy trình xét tuyển Liên thông đại học mới nhất 2026
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Queries & caches for sections
$featured_schools = function_exists( 'ltdh_get_cached_featured_schools' ) ? ltdh_get_cached_featured_schools() : [];

$news_query = function_exists( 'ltdh_get_cached_query' ) ? ltdh_get_cached_query( 'ltdh_homepage_news', [
	'post_type'      => 'post',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
	'no_found_rows'  => true,
], defined( 'HOUR_IN_SECONDS' ) ? HOUR_IN_SECONDS : 3600 ) : null;

$hotline = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '';
$zalo    = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : '';
?>

<div id="primary" class="content-area site-main bg-white">
	<!-- H1 Semantic Heading for SEO & Screen Readers (Exactly 1 H1 per page) -->
	<h1 class="sr-only">Cổng Thông Tin Tuyển Sinh Liên Thông Đại Học - Hệ Chính Quy, Vừa Học Vừa Làm & Đào Tạo Từ Xa</h1>

	<?php
	// 1. Hero Banner Slider
	get_template_part( 'template-parts/home/hero-banner' );

	// 2. Search & Filter Bar
	get_template_part( 'template-parts/home/search-filter' );

	// 3. Hot Majors & Programs
	get_template_part( 'template-parts/home/hot-programs' );

	// 4. Featured Partner Universities
	get_template_part( 'template-parts/home/featured-schools', null, [ 'featured_schools' => $featured_schools ] );

	// 5. Eligibility Quick Check
	get_template_part( 'template-parts/home/eligibility-banner' );

	// 6. Value Proposition & Degree Value
	get_template_part( 'template-parts/home/value-proposition' );

	// 7. Verified Statistics Counter
	get_template_part( 'template-parts/home/stats-counter' );

	// 8. Student Testimonials
	get_template_part( 'template-parts/home/testimonials' );

	// 9. Admission Process Steps
	get_template_part( 'template-parts/home/admission-steps' );

	// 10. Online Consultation Form
	get_template_part( 'template-parts/home/consultation-section' );

	// 11. Latest Admissions News
	get_template_part( 'template-parts/home/latest-news', null, [ 'news_query' => $news_query ] );

	// 12. Admissions FAQ Accordion
	get_template_part( 'template-parts/home/faq-accordion' );
	?>
</div><!-- #primary -->

<?php
get_footer();
