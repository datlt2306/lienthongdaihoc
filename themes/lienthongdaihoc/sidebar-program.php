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
?>

<aside id="program-sidebar" class="widget-area space-y-4 lg:space-y-6" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên chương trình đào tạo', 'ltdh' ); ?>">
	<?php
	if ( is_active_sidebar( 'sidebar-program' ) ) :
		dynamic_sidebar( 'sidebar-program' );
	elseif ( is_active_sidebar( 'sidebar-school' ) ) :
		// Kế thừa widget từ Trường nếu Chương trình chưa setup
		dynamic_sidebar( 'sidebar-school' );
	else :
		ltdh_render_default_program_sidebar( $program_id, $school_id );
	endif;
	?>
</aside>
