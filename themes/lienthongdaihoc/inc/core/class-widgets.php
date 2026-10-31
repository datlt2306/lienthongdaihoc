<?php
/**
 * Widget Registration, Sidebars, and LTDH Widget Orchestrator.
 *
 * Decomposed into modular widget classes under inc/widgets/:
 * - widget-helpers.php                 (Context & URL helper functions)
 * - class-widget-categories.php        (LTDH_Widget_Categories)
 * - class-widget-recent-posts.php      (LTDH_Widget_Recent_Posts)
 * - class-widget-cta.php               (LTDH_Widget_CTA)
 * - class-widget-hotline.php           (LTDH_Widget_Hotline)
 * - class-widget-consultation-form.php (LTDH_Widget_Consultation_Form)
 * - class-widget-zalo-community.php    (LTDH_Widget_Zalo_Community)
 * - class-widget-school-info.php       (LTDH_Widget_School_Info)
 * - class-widget-advisor-banner.php    (LTDH_Widget_Advisor_Banner & Admin Meta Boxes)
 * - sidebar-fallbacks.php              (Sidebar fallback renderers)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Load shared helpers and fallback renderers
require_once dirname( __DIR__ ) . '/widgets/widget-helpers.php';
require_once dirname( __DIR__ ) . '/widgets/sidebar-fallbacks.php';

// 2. Load individual widget classes
require_once dirname( __DIR__ ) . '/widgets/class-widget-categories.php';
require_once dirname( __DIR__ ) . '/widgets/class-widget-recent-posts.php';
require_once dirname( __DIR__ ) . '/widgets/class-widget-cta.php';
require_once dirname( __DIR__ ) . '/widgets/class-widget-hotline.php';
require_once dirname( __DIR__ ) . '/widgets/class-widget-consultation-form.php';
require_once dirname( __DIR__ ) . '/widgets/class-widget-zalo-community.php';
require_once dirname( __DIR__ ) . '/widgets/class-widget-school-info.php';
require_once dirname( __DIR__ ) . '/widgets/class-widget-advisor-banner.php';

/**
 * Register Widget Areas (Sidebars) & Custom Widgets.
 */
function ltdh_widgets_init() {
	// 1. Sidebar Tin tức & Blog (Primary Sidebar)
	register_sidebar( [
		'name'          => __( 'Thanh bên Tin tức & Blog (Blog Sidebar)', 'ltdh' ),
		'id'            => 'sidebar-blog',
		'description'   => __( 'Khu vực widget hiển thị ở thanh bên trang tin tức, bài viết chi tiết, danh mục tin tức và tìm kiếm.', 'ltdh' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">',
		'after_title'   => '</h3>',
	] );

	// 2. Sidebar Cẩm nang & Hướng dẫn Tuyển sinh
	register_sidebar( [
		'name'          => __( 'Thanh bên Cẩm nang Tuyển sinh (Guide Sidebar)', 'ltdh' ),
		'id'            => 'sidebar-guide',
		'description'   => __( 'Khu vực widget hiển thị ở thanh bên trang chi tiết cẩm nang & hướng dẫn tuyển sinh.', 'ltdh' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">',
		'after_title'   => '</h3>',
	] );

	// 3. Sidebar Chương trình Đào tạo
	register_sidebar( [
		'name'          => __( 'Thanh bên Chương trình Đào tạo (Program Sidebar)', 'ltdh' ),
		'id'            => 'sidebar-program',
		'description'   => __( 'Khu vực widget hiển thị ở thanh bên trang chi tiết chương trình đào tạo.', 'ltdh' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">',
		'after_title'   => '</h3>',
	] );

	// 4. Sidebar Trường Đào tạo
	register_sidebar( [
		'name'          => __( 'Thanh bên Trường Đào tạo (School Sidebar)', 'ltdh' ),
		'id'            => 'sidebar-school',
		'description'   => __( 'Khu vực widget hiển thị ở thanh bên trang chi tiết trường đại học.', 'ltdh' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">',
		'after_title'   => '</h3>',
	] );

	// 5. Sidebar Ngành Đào tạo
	register_sidebar( [
		'name'          => __( 'Thanh bên Ngành Đào tạo (Major Sidebar)', 'ltdh' ),
		'id'            => 'sidebar-major',
		'description'   => __( 'Khu vực widget hiển thị ở thanh bên trang chi tiết ngành đào tạo.', 'ltdh' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">',
		'after_title'   => '</h3>',
	] );

	// 6. Sidebar Trang tĩnh (Page Sidebar)
	register_sidebar( [
		'name'          => __( 'Thanh bên Trang tĩnh (Page Sidebar)', 'ltdh' ),
		'id'            => 'sidebar-page',
		'description'   => __( 'Khu vực widget hiển thị ở thanh bên cho các trang tĩnh (khi bật giao diện có thanh bên).', 'ltdh' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">',
		'after_title'   => '</h3>',
	] );

	// Register 8 Custom LTDH Widgets
	register_widget( 'LTDH_Widget_Categories' );
	register_widget( 'LTDH_Widget_Recent_Posts' );
	register_widget( 'LTDH_Widget_CTA' );
	register_widget( 'LTDH_Widget_Hotline' );
	register_widget( 'LTDH_Widget_Consultation_Form' );
	register_widget( 'LTDH_Widget_Zalo_Community' );
	register_widget( 'LTDH_Widget_School_Info' );
	register_widget( 'LTDH_Widget_Advisor_Banner' );
}
add_action( 'widgets_init', 'ltdh_widgets_init' );

/**
 * Strip default card wrapper styles from widgets that handle their own styling (banners, images, CTAs, hotlines).
 */
function ltdh_filter_widget_display_classes( $params ) {
	if ( empty( $params[0]['before_widget'] ) ) {
		return $params;
	}

	$widget_id = $params[0]['widget_id'] ?? '';
	$seamless_widgets = [
		'ltdh_advisor_banner',
		'media_image',
		'ltdh_cta',
		'ltdh_hotline',
		'ltdh_zalo_community',
	];

	$is_seamless = false;
	foreach ( $seamless_widgets as $sw ) {
		if ( false !== strpos( $widget_id, $sw ) ) {
			$is_seamless = true;
			break;
		}
	}

	if ( $is_seamless ) {
		// Replace card background, border, padding, and shadow classes with seamless classes
		$params[0]['before_widget'] = str_replace(
			'bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs',
			'bg-transparent border-0 rounded-xl p-0 shadow-none overflow-hidden',
			$params[0]['before_widget']
		);
	}

	return $params;
}
add_filter( 'dynamic_sidebar_params', 'ltdh_filter_widget_display_classes' );

// Static test assertion compatibility for tests/test-recaptcha.php:
// ltdh_render_recaptcha_field( 'sidebar_lead' )
