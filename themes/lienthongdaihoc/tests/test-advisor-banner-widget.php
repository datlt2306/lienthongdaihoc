<?php
/**
 * Test Suite: LTDH Advisor Banner Widget & Multi-Counselor Switching by School & Major
 *
 * @package lienthongdaihoc
 */

// Load WordPress environment
require_once dirname( __DIR__, 4 ) . '/wp-load.php';

class Test_Advisor_Banner_Widget {

	public static function run() {
		echo "\n==================================================\n";
		echo "STARTING TEST SUITE: ADVISOR BANNER WIDGET (BY SCHOOL & MAJOR)\n";
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

		// TC-05: Query param school detection
		$total++;
		$_GET['truong'] = '1853';
		$detected_school_id = ltdh_detect_current_school_id();
		unset( $_GET['truong'] );
		if ( 1853 === $detected_school_id ) {
			echo "✅ TC-05: Accurately detects school ID from query parameters (?truong=1853)\n";
			$passed++;
		} else {
			echo "❌ TC-05 FAILED: Expected 1853, got $detected_school_id\n";
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

		// Setup widget fixture
		$widget_obj = new LTDH_Widget_Advisor_Banner();
		$instance = [
			'advisor_1_name'    => 'Thầy Đạt',
			'advisor_1_phone'   => '0988 991 496',
			'advisor_1_image'   => 'http://example.com/thay-dat.jpg',
			'advisor_1_alt'     => 'Thầy Đạt tư vấn trực tiếp',
			'advisor_2_name'    => 'Cô Mai',
			'advisor_2_phone'   => '0912 345 678',
			'advisor_2_image'   => 'http://example.com/co-mai.jpg',
			'advisor_2_schools' => [ 1853 ], // GTVT belongs to Advisor 2!
			'advisor_2_majors'  => [],
			'display_mode'      => 'auto_by_school',
			'open_new_tab'      => true,
		];
		$args = [
			'before_widget' => '<div class="widget bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		];

		// TC-07: Widget Render Default Advisor (Advisor 1 - Thầy Đạt) when no school context or non-assigned school
		$total++;
		ob_start();
		$widget_obj->widget( $args, $instance );
		$html_default = ob_get_clean();

		if ( str_contains( $html_default, 'thay-dat.jpg' ) && str_contains( $html_default, 'https://zalo.me/0988991496' ) && ! str_contains( $html_default, 'p-5' ) ) {
			echo "✅ TC-07: Renders Advisor 1 (Thầy Đạt) and phone 0988991496 when school is unassigned\n";
			$passed++;
		} else {
			echo "❌ TC-07 FAILED: Output did not match Advisor 1:\n$html_default\n";
		}

		// TC-08: All programs under assigned school AUTOMATICALLY switch to Advisor 2!
		$total++;
		$gtvt_programs = get_posts( [
			'post_type'   => 'program',
			'meta_key'    => 'school_relationship',
			'meta_value'  => 1853,
			'numberposts' => 1,
			'fields'      => 'ids',
		] );

		if ( ! empty( $gtvt_programs ) ) {
			$gtvt_program_id = $gtvt_programs[0];
			global $post;
			$original_post = $post;
			$post = get_post( $gtvt_program_id );
			setup_postdata( $post );

			ob_start();
			$widget_obj->widget( $args, $instance );
			$html_prog = ob_get_clean();

			$post = $original_post;
			if ( $post ) setup_postdata( $post );

			if ( str_contains( $html_prog, 'co-mai.jpg' ) && str_contains( $html_prog, 'https://zalo.me/0912345678' ) && str_contains( $html_prog, 'data-advisor="2"' ) ) {
				echo "✅ TC-08: Program under assigned school (ĐH GTVT) AUTOMATICALLY switches to Advisor 2\n";
				$passed++;
			} else {
				echo "❌ TC-08 FAILED: Program did not switch to Advisor 2:\n$html_prog\n";
			}
		} else {
			echo "⚠️ TC-08 SKIPPED: No program found for school 1853\n";
		}

		// TC-09: Major page with school query param (?truong=1853) switches to Advisor 2!
		$total++;
		$_GET['truong'] = '1853';
		ob_start();
		$widget_obj->widget( $args, $instance );
		$html_major_school = ob_get_clean();
		unset( $_GET['truong'] );

		if ( str_contains( $html_major_school, 'co-mai.jpg' ) && str_contains( $html_major_school, 'https://zalo.me/0912345678' ) && str_contains( $html_major_school, 'data-advisor="2"' ) ) {
			echo "✅ TC-09: Major page viewed in context of assigned school (?truong=1853) displays Advisor 2\n";
			$passed++;
		} else {
			echo "❌ TC-09 FAILED: Major page with school query failed:\n$html_major_school\n";
		}

		// TC-10: Non-assigned school (e.g. school 2364 - ĐH Thương mại) retains Advisor 1 (Thầy Đạt)!
		$total++;
		$_GET['truong'] = '2364';
		ob_start();
		$widget_obj->widget( $args, $instance );
		$html_other_school = ob_get_clean();
		unset( $_GET['truong'] );

		if ( str_contains( $html_other_school, 'thay-dat.jpg' ) && str_contains( $html_other_school, 'data-advisor="1"' ) ) {
			echo "✅ TC-10: Other school (ĐH Thương mại) correctly retains Advisor 1 (Thầy Đạt)\n";
			$passed++;
		} else {
			echo "❌ TC-10 FAILED: Other school output failed:\n$html_other_school\n";
		}

		// TC-11: School Post Meta Override takes precedence
		$total++;
		$test_school_id = wp_insert_post( [
			'post_title'  => 'Test Trường Tư Vấn',
			'post_type'   => 'school',
			'post_status' => 'publish',
		] );
		update_post_meta( $test_school_id, '_ltdh_assigned_advisor', 'advisor_2' );

		$_GET['truong'] = (string) $test_school_id;
		ob_start();
		$widget_obj->widget( $args, $instance );
		$html_override = ob_get_clean();
		unset( $_GET['truong'] );
		wp_delete_post( $test_school_id, true );

		if ( str_contains( $html_override, 'co-mai.jpg' ) && str_contains( $html_override, 'https://zalo.me/0912345678' ) ) {
			echo "✅ TC-11: Per-school post meta override successfully switches to Advisor 2\n";
			$passed++;
		} else {
			echo "❌ TC-11 FAILED: Per-school override failed:\n$html_override\n";
		}

		// TC-12: CSS Verification in style.css
		$total++;
		$style_css = file_get_contents( dirname( __DIR__ ) . '/style.css' );
		if ( str_contains( $style_css, '.widget.widget_ltdh_advisor_banner' ) &&
		     str_contains( $style_css, 'line-height: 0 !important' ) &&
		     str_contains( $style_css, 'padding: 0 !important' ) ) {
			echo "✅ TC-12: style.css contains zero-margin, zero-padding rules for banner widgets\n";
			$passed++;
		} else {
			echo "❌ TC-12 FAILED: CSS rules missing in style.css\n";
		}

		echo "\n==================================================\n";
		echo "TEST RESULT: $passed / $total TESTS PASSED! 🎉\n";
		echo "==================================================\n\n";

		return ( $passed === $total ) ? 0 : 1;
	}
}

exit( Test_Advisor_Banner_Widget::run() );
