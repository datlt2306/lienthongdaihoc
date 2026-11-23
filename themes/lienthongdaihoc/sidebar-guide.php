<?php
/**
 * The sidebar template for single guide posts.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_dynamic_checker = ( is_active_sidebar( 'sidebar-guide' ) && function_exists( 'ltdh_sidebar_has_condition_checker' ) && ltdh_sidebar_has_condition_checker( 'sidebar-guide' ) );
?>

<aside id="guide-sidebar" class="widget-area space-y-6" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên cẩm nang', 'ltdh' ); ?>">
	<?php
	// 1. Luôn gắn mặc định Widget Kiểm tra điều kiện ở đầu thanh bên cẩm nang tuyển sinh
	if ( ! $has_dynamic_checker ) {
		get_template_part( 'template-parts/widgets/condition-checker', null, [
			'mode'  => 'compact',
			'title' => 'Kiểm tra điều kiện tuyển sinh',
		] );
	}

	if ( is_active_sidebar( 'sidebar-guide' ) ) :
		dynamic_sidebar( 'sidebar-guide' );
	else :
		ltdh_render_default_guide_sidebar();
	endif;
	?>
</aside>
