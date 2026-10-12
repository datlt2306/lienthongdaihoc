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

<aside id="program-sidebar" class="widget-area space-y-6 sticky top-36 md:top-40" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên chương trình đào tạo', 'ltdh' ); ?>">
	<?php
	if ( is_active_sidebar( 'sidebar-program' ) ) :
		dynamic_sidebar( 'sidebar-program' );
	else :
		ltdh_render_default_program_sidebar( $program_id, $school_id );
	endif;
	?>
</aside>
