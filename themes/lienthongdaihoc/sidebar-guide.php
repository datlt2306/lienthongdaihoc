<?php
/**
 * The sidebar template for single guide posts.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<aside id="guide-sidebar" class="widget-area space-y-6 sticky top-24" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên cẩm nang', 'ltdh' ); ?>">
	<?php
	if ( is_active_sidebar( 'sidebar-guide' ) ) :
		dynamic_sidebar( 'sidebar-guide' );
	else :
		ltdh_render_default_guide_sidebar();
	endif;
	?>
</aside>
