<?php
/**
 * The sidebar template for pages.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<aside id="page-sidebar" class="widget-area space-y-6 sticky top-24" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên trang tĩnh', 'ltdh' ); ?>">
	<?php
	if ( is_active_sidebar( 'sidebar-page' ) ) :
		dynamic_sidebar( 'sidebar-page' );
	else :
		ltdh_render_default_blog_sidebar();
	endif;
	?>
</aside>
