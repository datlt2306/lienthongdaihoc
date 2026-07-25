<?php
/**
 * Template Name: Kiểm tra điều kiện
 *
 * @package lienthongdaihoc
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<main class="min-h-screen bg-slate-50/50">
	<!-- Hero Section -->
	<section class="relative bg-gradient-to-tr from-[#0E2038] to-brand-primary text-white py-14 md:py-20 overflow-hidden">
		<!-- Dot Grid Pattern -->
		<div class="absolute inset-0 opacity-10 pointer-events-none z-0" style="background-image: radial-gradient(white 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
		<div class="absolute -right-32 -bottom-32 w-96 h-96 bg-brand-accent/20 rounded-full blur-3xl"></div>
		
		<div class="relative max-w-4xl mx-auto px-4 text-center z-10 space-y-2 animate-fade-in">
			<h1 class="text-2xl sm:text-3xl md:text-4xl font-black font-display tracking-tight leading-tight">KIỂM TRA ĐIỀU KIỆN TUYỂN SINH</h1>
			<p class="text-blue-100 text-sm md:text-base font-semibold max-w-md mx-auto">Chỉ với 60 giây trả lời câu hỏi để tìm đúng lộ trình và trường học phù hợp nhất cho bạn.</p>
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


</main>

<?php
get_footer();
