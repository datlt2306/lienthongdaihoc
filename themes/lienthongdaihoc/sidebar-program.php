<?php
/**
 * The sidebar template for single program posts.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id = get_the_ID();
$school_id  = (int) get_field( 'school_relationship', $program_id );

// Check if active dynamic sidebar already contains condition checker
$has_dynamic_checker = (
	( is_active_sidebar( 'sidebar-program' ) && function_exists( 'ltdh_sidebar_has_condition_checker' ) && ltdh_sidebar_has_condition_checker( 'sidebar-program' ) ) ||
	( ! is_active_sidebar( 'sidebar-program' ) && is_active_sidebar( 'sidebar-school' ) && function_exists( 'ltdh_sidebar_has_condition_checker' ) && ltdh_sidebar_has_condition_checker( 'sidebar-school' ) )
);
?>

<aside id="program-sidebar" class="widget-area space-y-4 lg:space-y-6" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên chương trình đào tạo', 'ltdh' ); ?>">
	<?php
	// 1. Luôn gắn mặc định Widget Kiểm tra điều kiện ở đầu thanh bên chương trình
	if ( ! $has_dynamic_checker ) {
		get_template_part( 'template-parts/widgets/condition-checker', null, [
			'mode'       => 'compact',
			'program_id' => $program_id,
		] );
	}

	// 2. Dynamic widgets hoặc Fallback
	if ( is_active_sidebar( 'sidebar-program' ) ) :
		dynamic_sidebar( 'sidebar-program' );
	elseif ( is_active_sidebar( 'sidebar-school' ) ) :
		// Kế thừa widget từ Trường nếu Chương trình chưa setup
		dynamic_sidebar( 'sidebar-school' );
	else :
		ltdh_render_default_program_sidebar( $program_id, $school_id, true );
	endif;
	?>
</aside>
