<?php
/**
 * The sidebar template containing the main blog widget area.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<aside id="secondary" class="widget-area space-y-6" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên tin tức & bài viết', 'ltdh' ); ?>">
	<?php
	if ( is_active_sidebar( 'sidebar-blog' ) ) :
		dynamic_sidebar( 'sidebar-blog' );
	else :
		ltdh_render_default_blog_sidebar();
	endif;
	?>
</aside>
