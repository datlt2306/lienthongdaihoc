<?php
/**
 * The sidebar template for single school posts.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$school_id = get_the_ID();
$has_dynamic_checker = ( is_active_sidebar( 'sidebar-school' ) && function_exists( 'ltdh_sidebar_has_condition_checker' ) && ltdh_sidebar_has_condition_checker( 'sidebar-school' ) );
?>

<aside id="school-sidebar" class="widget-area space-y-4 lg:space-y-6" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên trường đào tạo', 'ltdh' ); ?>">
	<?php
	// 1. Luôn gắn mặc định Widget Kiểm tra điều kiện ở đầu thanh bên trường đào tạo
	if ( ! $has_dynamic_checker ) {
		get_template_part( 'template-parts/widgets/condition-checker', null, [
			'mode'      => 'compact',
			'school_id' => $school_id,
		] );
	}

	if ( is_active_sidebar( 'sidebar-school' ) ) :
		dynamic_sidebar( 'sidebar-school' );
	else :
		ltdh_render_default_school_sidebar( $school_id, true );
	endif;
	?>
</aside>
