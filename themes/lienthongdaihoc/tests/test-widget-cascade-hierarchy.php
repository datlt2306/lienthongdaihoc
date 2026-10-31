<?php
/**
 * Test Suite: All Widgets Cascade Hierarchy (Program / Major -> School -> Global Fallback)
 *
 * @package lienthongdaihoc
 */

// Load WordPress environment
require_once dirname( __DIR__, 4 ) . '/wp-load.php';

class Test_Widget_Cascade_Hierarchy {

	public static function run() {
		echo "\n==================================================\n";
		echo "STARTING TEST SUITE: WIDGET CASCADE HIERARCHY\n";
		echo "==================================================\n\n";

		$passed = 0;
		$total  = 0;

		// TC-01: Helper ltdh_detect_current_program_id detects single program or query param
		$total++;
		$_GET['chuong-trinh'] = '9999';
		$det_prog = ltdh_detect_current_program_id();
		unset( $_GET['chuong-trinh'] );
		if ( 9999 === $det_prog ) {
			echo "✅ TC-01: ltdh_detect_current_program_id detects program from query param\n";
			$passed++;
		} else {
			echo "❌ TC-01 FAILED: Expected 9999, got $det_prog\n";
		}

		// Setup Test Fixtures: 1 School, 1 Program under that School
		$test_school_id = wp_insert_post( [
			'post_title'  => 'Trường ĐH Test Widget',
			'post_type'   => 'school',
			'post_status' => 'publish',
		] );
		update_post_meta( $test_school_id, 'hotline', '0911 222 333' );
		update_post_meta( $test_school_id, 'zalo_group_url', 'https://zalo.me/g/school-group-test' );
		update_post_meta( $test_school_id, '_ltdh_assigned_advisor', 'advisor_2' );

		$test_program_id = wp_insert_post( [
			'post_title'  => 'Chương trình Test Widget',
			'post_type'   => 'program',
			'post_status' => 'publish',
		] );
		update_post_meta( $test_program_id, 'school_relationship', $test_school_id );

		// TC-02: Program inherits Advisor 2 from School when Program has no override
		$total++;
		$banner_widget = new LTDH_Widget_Advisor_Banner();
		$banner_instance = [
			'advisor_1_name'  => 'Thầy Đạt',
			'advisor_1_phone' => '0988 991 496',
			'advisor_1_image' => 'http://example.com/dat.jpg',
			'advisor_2_name'  => 'Cô Mai',
			'advisor_2_phone' => '0912 345 678',
			'advisor_2_image' => 'http://example.com/mai.jpg',
			'display_mode'    => 'auto_by_school',
		];
		$dummy_args = [
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		];

		// Simulate Program post context
		global $post;
		$orig_post = $post;
		$post = get_post( $test_program_id );
		setup_postdata( $post );

		ob_start();
		$banner_widget->widget( $dummy_args, $banner_instance );
		$out_banner_prog = ob_get_clean();

		if ( str_contains( $out_banner_prog, 'mai.jpg' ) && str_contains( $out_banner_prog, 'data-advisor="2"' ) ) {
			echo "✅ TC-02: Program without advisor setup automatically inherits Advisor 2 from School\n";
			$passed++;
		} else {
			echo "❌ TC-02 FAILED: Program did not inherit Advisor 2:\n$out_banner_prog\n";
		}

		// TC-03: Program override to Advisor 1 takes precedence over School's Advisor 2
		$total++;
		update_post_meta( $test_program_id, '_ltdh_assigned_advisor', 'advisor_1' );
		ob_start();
		$banner_widget->widget( $dummy_args, $banner_instance );
		$out_banner_override = ob_get_clean();

		if ( str_contains( $out_banner_override, 'dat.jpg' ) && str_contains( $out_banner_override, 'data-advisor="1"' ) ) {
			echo "✅ TC-03: Program with explicit override (Advisor 1) overrides School setting\n";
			$passed++;
		} else {
			echo "❌ TC-03 FAILED: Program override did not work:\n$out_banner_override\n";
		}
		delete_post_meta( $test_program_id, '_ltdh_assigned_advisor' ); // reset to default

		// TC-04: Hotline Widget inherits Hotline from School when Program has no override
		$total++;
		$hotline_widget = new LTDH_Widget_Hotline();
		$hotline_instance = [
			'title'     => 'Tư vấn {school_name}',
			'hotline'   => '', // empty to trigger auto cascade
			'zalo_url'  => '', // empty to trigger auto cascade
			'school_id' => 'auto',
		];
		ob_start();
		$hotline_widget->widget( $dummy_args, $hotline_instance );
		$out_hotline_prog = ob_get_clean();

		if ( str_contains( $out_hotline_prog, '0911 222 333' ) || str_contains( $out_hotline_prog, '0911222333' ) ) {
			echo "✅ TC-04: Hotline widget inherits School hotline when Program hotline is empty\n";
			$passed++;
		} else {
			echo "❌ TC-04 FAILED: Hotline did not inherit from school:\n$out_hotline_prog\n";
		}

		// TC-05: Hotline Widget uses Program override when specified on Program
		$total++;
		update_post_meta( $test_program_id, 'hotline_override', '0999 888 777' );
		ob_start();
		$hotline_widget->widget( $dummy_args, $hotline_instance );
		$out_hotline_prog_override = ob_get_clean();

		if ( str_contains( $out_hotline_prog_override, '0999 888 777' ) || str_contains( $out_hotline_prog_override, '0999888777' ) ) {
			echo "✅ TC-05: Hotline widget uses Program hotline override when set\n";
			$passed++;
		} else {
			echo "❌ TC-05 FAILED: Hotline override failed:\n$out_hotline_prog_override\n";
		}
		delete_post_meta( $test_program_id, 'hotline_override' );
		if ( function_exists( 'acf_get_store' ) ) {
			acf_get_store( 'values' )->reset();
		}
		clean_post_cache( $test_program_id );

		// TC-06: Zalo Community Widget inherits School Zalo Group URL when Program has none
		$total++;
		$zalo_widget = new LTDH_Widget_Zalo_Community();
		$zalo_instance = [
			'title'     => 'Nhóm Zalo {school_name}',
			'zalo_url'  => '', // empty for auto
			'school_id' => 'auto',
		];
		ob_start();
		$zalo_widget->widget( $dummy_args, $zalo_instance );
		$out_zalo_prog = ob_get_clean();

		if ( str_contains( $out_zalo_prog, 'https://zalo.me/g/school-group-test' ) ) {
			echo "✅ TC-06: Zalo community widget inherits School Zalo Group link on Program\n";
			$passed++;
		} else {
			echo "❌ TC-06 FAILED: Zalo link did not inherit from school:\n$out_zalo_prog\n";
		}

		// TC-07: Zalo Community Widget uses Program Zalo Group override when set
		$total++;
		update_post_meta( $test_program_id, 'zalo_group_url', 'https://zalo.me/g/program-specific-group' );
		ob_start();
		$zalo_widget->widget( $dummy_args, $zalo_instance );
		$out_zalo_prog_override = ob_get_clean();

		if ( str_contains( $out_zalo_prog_override, 'https://zalo.me/g/program-specific-group' ) ) {
			echo "✅ TC-07: Zalo community widget uses Program specific Zalo Group link when set\n";
			$passed++;
		} else {
			echo "❌ TC-07 FAILED: Program Zalo override failed:\n$out_zalo_prog_override\n";
		}
		delete_post_meta( $test_program_id, 'zalo_group_url' );

		// TC-08: School Info Widget resolves School from Program context
		$total++;
		$school_info_widget = new LTDH_Widget_School_Info();
		$detected_sid = $school_info_widget->get_school_id( [ 'school_id' => 'auto' ] );
		if ( $test_school_id === $detected_sid ) {
			echo "✅ TC-08: School info widget automatically detects School ID ($test_school_id) from Program context\n";
			$passed++;
		} else {
			echo "❌ TC-08 FAILED: Expected $test_school_id, got $detected_sid\n";
		}

		// TC-09: CTA Widget inherits School hotline on Program
		$total++;
		$cta_widget = new LTDH_Widget_CTA();
		ob_start();
		$cta_widget->widget( $dummy_args, [ 'show_hotline' => true ] );
		$out_cta = ob_get_clean();

		if ( str_contains( $out_cta, '0911 222 333' ) || str_contains( $out_cta, '0911222333' ) ) {
			echo "✅ TC-09: CTA widget inherits School hotline in Program context\n";
			$passed++;
		} else {
			echo "❌ TC-09 FAILED: CTA hotline did not inherit:\n$out_cta\n";
		}

		// TC-10: Template files verification for sidebar fallback cascade
		$total++;
		$sidebar_program_code = file_get_contents( dirname( __DIR__ ) . '/sidebar-program.php' );
		$sidebar_major_code   = file_get_contents( dirname( __DIR__ ) . '/sidebar-major.php' );

		if ( str_contains( $sidebar_program_code, "elseif ( is_active_sidebar( 'sidebar-school' ) )" ) &&
		     str_contains( $sidebar_major_code, "elseif ( \$school_id && is_active_sidebar( 'sidebar-school' ) )" ) ) {
			echo "✅ TC-10: sidebar-program.php and sidebar-major.php implement sidebar-school fallback\n";
			$passed++;
		} else {
			echo "❌ TC-10 FAILED: Sidebar fallback logic missing in templates\n";
		}

		// TC-11: Program meta box registration includes 'program' CPT
		$total++;
		$advisor_file = file_get_contents( dirname( __DIR__ ) . '/inc/widgets/class-widget-advisor-banner.php' );
		if ( str_contains( $advisor_file, '$cpt_program = defined( \'LTDH_CPT_PROGRAM\' ) ? LTDH_CPT_PROGRAM : \'program\';' ) &&
		     str_contains( $advisor_file, '[ $cpt_school, $cpt_major, $cpt_program ]' ) &&
		     has_action( 'add_meta_boxes', 'ltdh_register_advisor_meta_boxes' ) ) {
			echo "✅ TC-11: ltdh_advisor_override meta box registered for 'program' post type\n";
			$passed++;
		} else {
			echo "❌ TC-11 FAILED: ltdh_advisor_override not found for program post type\n";
		}

		// Clean up fixture posts
		$post = $orig_post;
		if ( $post ) setup_postdata( $post );
		wp_delete_post( $test_program_id, true );
		wp_delete_post( $test_school_id, true );

		echo "\n==================================================\n";
		echo "TEST RESULT: $passed / $total TESTS PASSED! 🎉\n";
		echo "==================================================\n\n";

		return ( $passed === $total ) ? 0 : 1;
	}
}

exit( Test_Widget_Cascade_Hierarchy::run() );
