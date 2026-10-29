<?php
/**
 * Template Name: Kiểm tra điều kiện
 *
 * @package lienthongdaihoc
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Check if a shared check ID or token is requested via URL (?kq= or ?check_id= or ?res=)
$shared_check = null;
if ( function_exists( 'ltdh_elig_get_check_results' ) ) {
	$param = sanitize_text_field( $_GET['kq'] ?? ( $_GET['check_id'] ?? ( $_GET['res'] ?? ( $_GET['token'] ?? '' ) ) ) );
	if ( ! empty( $param ) ) {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();
		do_action( 'litespeed_control_set_nocache', 'shared_check_result' );
		$shared_check = ltdh_elig_get_check_results( $param );
	}
}

get_header();
?>

<div id="primary" tabindex="-1" class="content-area site-main min-h-screen bg-slate-50/50">
	<?php get_template_part( 'template-parts/banner' ); ?>

	<!-- Checker Container -->
	<div id="eligibility-app" class="max-w-4xl mx-auto px-4 py-8 md:py-12" data-elig-root>

		<!-- Wizard Form -->
		<div id="elig-wizard" class="elig-wizard<?php echo $shared_check ? ' hidden' : ''; ?>">
			<?php get_template_part( 'template-parts/eligibility/wizard' ); ?>
		</div>

		<!-- Results (shown immediately if shared link, else hidden initially) -->
		<div id="elig-results" class="elig-results<?php echo $shared_check ? '' : ' hidden'; ?>">
			<?php get_template_part( 'template-parts/eligibility/results' ); ?>
		</div>

	</div>

	<?php if ( $shared_check ) : ?>
		<script>
			window.ltdh_initial_results = <?php echo wp_json_encode( $shared_check ); ?>;
		</script>
	<?php endif; ?>


</div><!-- #primary -->

<?php
get_footer();
