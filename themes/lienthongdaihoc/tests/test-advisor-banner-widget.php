<?php
/**
 * Test Suite: LTDH Advisor Banner Widget & Multi-Counselor Switching
 *
 * @package lienthongdaihoc
 */

// Load WordPress environment
require_once dirname( __DIR__, 4 ) . '/wp-load.php';

class Test_Advisor_Banner_Widget {

	public static function run() {
		echo "\n==================================================\n";
		echo "STARTING TEST SUITE: ADVISOR BANNER WIDGET\n";
		echo "==================================================\n\n";

		$passed = 0;
		$total  = 0;

		// TC-01: Zalo URL Formatting
		$total++;
		$phone1 = '0988 991 496';
		$zalo1  = ltdh_format_zalo_url( $phone1 );
		if ( 'https://zalo.me/0988991496' === $zalo1 ) {
			echo "✅ TC-01: Formats spaced phone number to valid Zalo URL\n";
			$passed++;
		} else {
			echo "❌ TC-01 FAILED: Expected https://zalo.me/0988991496, got $zalo1\n";
		}

		// TC-02: Existing Zalo link preserved
		$total++;
		$link_custom = 'https://zalo.me/g/community123';
		$zalo2       = ltdh_format_zalo_url( $link_custom );
		if ( $link_custom === $zalo2 ) {
			echo "✅ TC-02: Preserves custom Zalo community link\n";
			$passed++;
		} else {
			echo "❌ TC-02 FAILED: Expected $link_custom, got $zalo2\n";
		}

		// TC-03: Empty fallback
		$total++;
		$zalo3 = ltdh_format_zalo_url( '' );
		if ( ! empty( $zalo3 ) && str_starts_with( $zalo3, 'https://zalo.me' ) ) {
			echo "✅ TC-03: Falls back to default Zalo link when empty\n";
			$passed++;
		} else {
			echo "❌ TC-03 FAILED: Expected valid fallback, got $zalo3\n";
		}

		// TC-04: Widget class registered
		$total++;
		global $wp_widget_factory;
		if ( isset( $wp_widget_factory->widgets['LTDH_Widget_Advisor_Banner'] ) ) {
			echo "✅ TC-04: LTDH_Widget_Advisor_Banner registered in WP Widget Factory\n";
			$passed++;
		} else {
			echo "❌ TC-04 FAILED: LTDH_Widget_Advisor_Banner not found in WP Widget Factory\n";
		}

		// TC-05: Query param major detection
		$total++;
		$_GET['nganh'] = '1694';
		$detected_id = ltdh_detect_current_major_id();
		unset( $_GET['nganh'] );
		if ( 1694 === $detected_id ) {
			echo "✅ TC-05: Accurately detects major ID from query parameters\n";
			$passed++;
		} else {
			echo "❌ TC-05 FAILED: Expected 1694, got $detected_id\n";
		}

		// TC-06: Dynamic Sidebar Params filter strips card wrapper
		$total++;
		$dummy_params = [
			[
				'widget_id'     => 'ltdh_advisor_banner-2',
				'before_widget' => '<div id="ltdh_advisor_banner-2" class="widget widget_ltdh_advisor_banner bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
				'after_widget'  => '</div>',
			],
		];
		$filtered_params = ltdh_filter_widget_display_classes( $dummy_params );
		$new_before = $filtered_params[0]['before_widget'];
		if ( ! str_contains( $new_before, 'p-5' ) && ! str_contains( $new_before, 'bg-white' ) && str_contains( $new_before, 'bg-transparent' ) ) {
			echo "✅ TC-06: dynamic_sidebar_params filter cleanly strips card padding & border\n";
			$passed++;
		} else {
			echo "❌ TC-06 FAILED: Failed to strip card classes: $new_before\n";
		}

		// TC-07: Widget Render Default Advisor (Advisor 1 - Thầy Đạt)
		$total++;
		$widget_obj = new LTDH_Widget_Advisor_Banner();
		$instance = [
			'advisor_1_name'   => 'Thầy Đạt',
			'advisor_1_phone'  => '0988 991 496',
			'advisor_1_image'  => 'http://example.com/thay-dat.jpg',
			'advisor_1_alt'    => 'Thầy Đạt tư vấn trực tiếp',
			'advisor_2_name'   => 'Cô Mai',
			'advisor_2_phone'  => '0912 345 678',
			'advisor_2_image'  => 'http://example.com/co-mai.jpg',
			'advisor_2_majors' => [ 9999 ], // major 9999 belongs to Advisor 2
			'display_mode'     => 'auto_by_major',
			'open_new_tab'     => true,
		];
		$args = [
			'before_widget' => '<div class="widget bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		];

		ob_start();
		$widget_obj->widget( $args, $instance );
		$html_default = ob_get_clean();

		if ( str_contains( $html_default, 'thay-dat.jpg' ) && str_contains( $html_default, 'https://zalo.me/0988991496' ) && ! str_contains( $html_default, 'p-5' ) ) {
			echo "✅ TC-07: Renders Advisor 1 (Thầy Đạt) and phone 0988991496 with seamless wrapper\n";
			$passed++;
		} else {
			echo "❌ TC-07 FAILED: Output did not match Advisor 1:\n$html_default\n";
		}

		// TC-08: Widget Render Switch to Advisor 2 when matching Major is viewed
		$total++;
		$_GET['nganh'] = '9999';
		ob_start();
		$widget_obj->widget( $args, $instance );
		$html_advisor2 = ob_get_clean();
		unset( $_GET['nganh'] );

		if ( str_contains( $html_advisor2, 'co-mai.jpg' ) && str_contains( $html_advisor2, 'https://zalo.me/0912345678' ) && str_contains( $html_advisor2, 'data-advisor="2"' ) ) {
			echo "✅ TC-08: Automatically switches to Advisor 2 (Cô Mai) & link 0912345678 for assigned major\n";
			$passed++;
		} else {
			echo "❌ TC-08 FAILED: Output did not switch to Advisor 2:\n$html_advisor2\n";
		}

		// TC-09: Post Meta Override takes precedence
		$total++;
		// Create temporary test major post
		$test_major_id = wp_insert_post( [
			'post_title'  => 'Test Ngành Tư Vấn',
			'post_type'   => 'major',
			'post_status' => 'publish',
		] );
		update_post_meta( $test_major_id, '_ltdh_assigned_advisor', 'advisor_2' );

		$_GET['nganh'] = (string) $test_major_id;
		ob_start();
		$widget_obj->widget( $args, $instance );
		$html_override = ob_get_clean();
		unset( $_GET['nganh'] );
		wp_delete_post( $test_major_id, true );

		if ( str_contains( $html_override, 'co-mai.jpg' ) && str_contains( $html_override, 'https://zalo.me/0912345678' ) ) {
			echo "✅ TC-09: Per-major post meta override successfully switches to Advisor 2\n";
			$passed++;
		} else {
			echo "❌ TC-09 FAILED: Override failed:\n$html_override\n";
		}

		// TC-10: CSS Verification in style.css
		$total++;
		$style_css = file_get_contents( dirname( __DIR__ ) . '/style.css' );
		if ( str_contains( $style_css, '.widget.widget_ltdh_advisor_banner' ) &&
		     str_contains( $style_css, 'line-height: 0 !important' ) &&
		     str_contains( $style_css, 'padding: 0 !important' ) ) {
			echo "✅ TC-10: style.css contains zero-margin, zero-padding rules for banner widgets\n";
			$passed++;
		} else {
			echo "❌ TC-10 FAILED: CSS rules missing in style.css\n";
		}

		echo "\n==================================================\n";
		echo "TEST RESULT: $passed / $total TESTS PASSED! 🎉\n";
		echo "==================================================\n\n";

		return ( $passed === $total ) ? 0 : 1;
	}
}

exit( Test_Advisor_Banner_Widget::run() );
