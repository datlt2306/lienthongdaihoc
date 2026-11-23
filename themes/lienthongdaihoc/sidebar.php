<?php
/**
 * The sidebar template containing the main blog widget area.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_dynamic_checker = ( is_active_sidebar( 'sidebar-blog' ) && function_exists( 'ltdh_sidebar_has_condition_checker' ) && ltdh_sidebar_has_condition_checker( 'sidebar-blog' ) );
?>

<aside id="secondary" class="widget-area space-y-6" role="complementary" aria-label="<?php esc_attr_e( 'Thanh bên tin tức & bài viết', 'ltdh' ); ?>">
	<?php
	// 1. Luôn gắn mặc định Widget Kiểm tra điều kiện ở đầu thanh bên tin tức
	if ( ! $has_dynamic_checker ) {
		get_template_part( 'template-parts/widgets/condition-checker', null, [
			'mode'  => 'compact',
			'title' => 'Kiểm tra nhanh điều kiện xét tuyển',
		] );
	}

	if ( is_active_sidebar( 'sidebar-blog' ) ) :
		dynamic_sidebar( 'sidebar-blog' );
	else :
		ltdh_render_default_blog_sidebar();
	endif;
	?>
</aside>
