<?php
/**
 * The sidebar template for pages.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_dynamic_checker = ( is_active_sidebar( 'sidebar-page' ) && function_exists( 'ltdh_sidebar_has_condition_checker' ) && ltdh_sidebar_has_condition_checker( 'sidebar-page' ) );
?>

<aside id="page-sidebar" class="widget-area space-y-6" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên trang tĩnh', 'ltdh' ); ?>">
	<?php
	// 1. Luôn gắn mặc định Widget Kiểm tra điều kiện ở đầu thanh bên trang tĩnh (ngoại trừ chính trang /kiem-tra-dieu-kien/)
	if ( ! $has_dynamic_checker && ! ( function_exists( 'is_page' ) && is_page( 'kiem-tra-dieu-kien' ) ) ) {
		get_template_part( 'template-parts/widgets/condition-checker', null, [
			'mode' => 'compact',
		] );
	}

	if ( is_active_sidebar( 'sidebar-page' ) ) :
		dynamic_sidebar( 'sidebar-page' );
	else :
		ltdh_render_default_blog_sidebar();
	endif;
	?>
</aside>
