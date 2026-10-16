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
	<?php get_template_part( 'template-parts/banner' ); ?>

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
