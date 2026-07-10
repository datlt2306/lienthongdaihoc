<?php
/**
 * Lead Capture Custom DB Table and CF7 Integration
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Database Table Creation (Theme Activation)
// ----------------------------------------------------
add_action( 'after_switch_theme', 'ltdh_create_leads_table' );

function ltdh_create_leads_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'ltdh_leads';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		name varchar(255) NOT NULL,
		phone varchar(50) NOT NULL,
		email varchar(100) DEFAULT '',
		program_id bigint(20) DEFAULT 0,
		school_id bigint(20) DEFAULT 0,
		major_id bigint(20) DEFAULT 0,
		training_type varchar(100) DEFAULT '',
		campus varchar(100) DEFAULT '',
		referral_source text DEFAULT '',
		sync_status varchar(50) DEFAULT 'pending',
		retry_count int(11) DEFAULT 0,
		error_message text DEFAULT '',
		created_at datetime NOT NULL,
		synced_at datetime DEFAULT NULL,
		PRIMARY KEY  (id),
		KEY sync_status (sync_status)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

// ----------------------------------------------------
// 2. CF7 Lead Interceptor
// ----------------------------------------------------
add_action( 'wpcf7_before_send_mail', 'ltdh_capture_cf7_lead', 10, 3 );

function ltdh_capture_cf7_lead( $contact_form, &$abort, $submission ) {
	$posted_data = $submission->get_posted_data();

	// Read Name and Phone (Required fields for leads)
	$name  = isset( $posted_data['your-name'] ) ? sanitize_text_field( $posted_data['your-name'] ) : '';
	$phone = isset( $posted_data['your-phone'] ) ? sanitize_text_field( $posted_data['your-phone'] ) : '';
	$email = isset( $posted_data['your-email'] ) ? sanitize_email( $posted_data['your-email'] ) : '';

	if ( empty( $name ) || empty( $phone ) ) {
		return; // Not a standard lead submit or missing info
	}

	// Capture Context Fields
	$program_id      = isset( $posted_data['current_program_id'] ) ? intval( $posted_data['current_program_id'] ) : 0;
	$school_id       = isset( $posted_data['current_school_id'] ) ? intval( $posted_data['current_school_id'] ) : 0;
	$major_id        = isset( $posted_data['current_major_id'] ) ? intval( $posted_data['current_major_id'] ) : 0;
	$training_type   = '';
	$campus          = '';

	// If Program context is loaded, pre-resolve fields if not filled manually
	if ( $program_id > 0 ) {
		if ( ! $school_id ) {
			$school_id = get_field( 'school_relationship', $program_id );
		}
		if ( ! $major_id ) {
			$major_id = get_field( 'major_relationship', $program_id );
		}
		
		// Fetch training types taxonomy from program
		$terms = wp_get_post_terms( $program_id, 'training_type' );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$training_type = $terms[0]->name;
		}

		// Fetch campus from program
		$campus_terms = wp_get_post_terms( $program_id, 'campus' );
		if ( ! is_wp_error( $campus_terms ) && ! empty( $campus_terms ) ) {
			$campus = $campus_terms[0]->name;
		}
	}

	// Referral URL
	$referral_source = isset( $posted_data['referral_source'] ) ? esc_url_raw( $posted_data['referral_source'] ) : '';
	if ( empty( $referral_source ) ) {
		$referral_source = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '';
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'ltdh_leads';

	$wpdb->insert(
		$table_name,
		[
			'name'            => $name,
			'phone'           => $phone,
			'email'           => $email,
			'program_id'      => $program_id,
			'school_id'       => $school_id,
			'major_id'        => $major_id,
			'training_type'   => $training_type,
			'campus'          => $campus,
			'referral_source' => $referral_source,
			'sync_status'     => 'pending',
			'retry_count'     => 0,
			'created_at'      => current_time( 'mysql' ),
		],
		[ '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s' ]
	);
}
