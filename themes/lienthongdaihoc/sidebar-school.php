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
?>

<aside id="school-sidebar" class="widget-area space-y-6 sticky top-36 md:top-40" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên trường đào tạo', 'ltdh' ); ?>">
	<?php
	if ( is_active_sidebar( 'sidebar-school' ) ) :
		dynamic_sidebar( 'sidebar-school' );
	else :
		ltdh_render_default_school_sidebar( $school_id );
	endif;
	?>
</aside>
