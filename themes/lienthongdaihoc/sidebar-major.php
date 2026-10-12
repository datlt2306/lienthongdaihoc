<?php
/**
 * The sidebar template for single major posts.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$major_id = get_the_ID();
?>

<aside id="major-sidebar" class="widget-area space-y-6 sticky top-36 md:top-40" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên ngành đào tạo', 'ltdh' ); ?>">
	<?php
	if ( is_active_sidebar( 'sidebar-major' ) ) :
		dynamic_sidebar( 'sidebar-major' );
	else :
		ltdh_render_default_major_sidebar( $major_id );
	endif;
	?>
</aside>
