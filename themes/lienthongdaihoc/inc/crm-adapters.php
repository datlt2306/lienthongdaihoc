<?php
/**
 * CRM Integration Sync Queue and Adapters
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Cron Setup
// ----------------------------------------------------
add_filter( 'cron_schedules', 'ltdh_add_cron_intervals' );
function ltdh_add_cron_intervals( $schedules ) {
	$schedules['every_five_minutes'] = [
		'interval' => 300,
		'display'  => esc_html__( 'Mỗi 5 phút' ),
	];
	return $schedules;
}

add_action( 'admin_init', 'ltdh_schedule_crm_sync' );
function ltdh_schedule_crm_sync() {
	if ( ! wp_next_scheduled( 'ltdh_cron_sync_leads' ) ) {
		wp_schedule_event( time(), 'every_five_minutes', 'ltdh_cron_sync_leads' );
	}
}

// Clean up cron on theme deactivation
add_action( 'switch_theme', 'ltdh_clear_crm_sync_schedule' );
function ltdh_clear_crm_sync_schedule() {
	wp_clear_scheduled_hook( 'ltdh_cron_sync_leads' );
}

add_action( 'ltdh_cron_sync_leads', 'ltdh_process_lead_queue' );

// ----------------------------------------------------
// 2. Lead Queue Processor
// ----------------------------------------------------
function ltdh_process_lead_queue() {
	global $wpdb;
	$table_name = $wpdb->prefix . LTDH_TABLE_LEADS;

	// Get pending or retry-eligible failed leads (limit to 10 per execution)
	$leads = $wpdb->get_results( "
		SELECT * FROM $table_name 
		WHERE sync_status = 'pending' 
		   OR (sync_status = 'failed' AND retry_count < 5)
		LIMIT 10
	" );

	if ( empty( $leads ) ) {
		return;
	}

	foreach ( $leads as $lead ) {
		// Mark as processing to avoid duplicate runs
		$wpdb->update( $table_name, [ 'sync_status' => 'processing' ], [ 'id' => $lead->id ] );

		$result = ltdh_sync_lead_to_crm( $lead );

		if ( is_wp_error( $result ) ) {
			$wpdb->update(
				$table_name,
				[
					'sync_status'   => 'failed',
					'retry_count'   => $lead->retry_count + 1,
					'error_message' => $result->get_error_message(),
				],
				[ 'id' => $lead->id ]
			);
		} else {
			$wpdb->update(
				$table_name,
				[
					'sync_status' => 'synced',
					'synced_at'   => current_time( 'mysql' ),
					'error_message' => '',
				],
				[ 'id' => $lead->id ]
			);
		}
	}
}

// ----------------------------------------------------
// 3. CRM Sync Router & Adapters
// ----------------------------------------------------
function ltdh_sync_lead_to_crm( $lead ) {
	$crm_type = get_field( 'default_crm_type', 'options' );

	if ( ! $crm_type || $crm_type === 'internal' ) {
		return true; // No third-party CRM sync needed
	}

	// Fetch related entity titles for richer payload context
	$program_name = $lead->program_id ? get_the_title( $lead->program_id ) : 'Không xác định';
	$school_name  = $lead->school_id ? get_the_title( $lead->school_id ) : 'Không xác định';
	$major_name   = $lead->major_id ? get_the_title( $lead->major_id ) : 'Không xác định';

	$payload = [
		'name'          => $lead->name,
		'phone'         => $lead->phone,
		'email'         => $lead->email,
		'program'       => $program_name,
		'school'        => $school_name,
		'major'         => $major_name,
		'training_type' => $lead->training_type,
		'campus'        => $lead->campus,
		'referrer'      => $lead->referral_source,
	];

	if ( $crm_type === 'onschool' ) {
		return ltdh_sync_onschool_adapter( $payload );
	} elseif ( $crm_type === 'aum' ) {
		return ltdh_sync_aum_adapter( $payload );
	} elseif ( $crm_type === 'erpnext' ) {
		return ltdh_sync_erpnext_adapter( $payload );
	}

	return new WP_Error( 'invalid_crm', 'Hệ thống CRM cấu hình không hợp lệ.' );
}

/**
 * OnSchool API Adapter
 */
function ltdh_sync_onschool_adapter( $payload ) {
	$endpoint = get_field( 'onschool_endpoint', 'options' );
	$token    = get_field( 'onschool_token', 'options' );

	if ( empty( $endpoint ) ) {
		return new WP_Error( 'missing_config', 'Thiếu OnSchool API Endpoint.' );
	}

	$response = wp_safe_remote_post( $endpoint, [
		'headers' => [
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $token,
		],
		'body'    => wp_json_encode( [
			'lead_name'    => $payload['name'],
			'lead_phone'   => $payload['phone'],
			'lead_email'   => $payload['email'],
			'target_class' => $payload['program'],
			'university'   => $payload['school'],
			'major'        => $payload['major'],
			'course_type'  => $payload['training_type'],
			'site_location'=> $payload['campus'],
			'source_url'   => $payload['referrer'],
		] ),
		'timeout' => 15,
	] );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return new WP_Error( 'api_error', 'OnSchool API returned HTTP code ' . $code );
	}

	return true;
}

/**
 * AUM CRM API Adapter
 */
function ltdh_sync_aum_adapter( $payload ) {
	$endpoint = get_field( 'aum_endpoint', 'options' );
	$token    = get_field( 'aum_token', 'options' );

	if ( empty( $endpoint ) ) {
		return new WP_Error( 'missing_config', 'Thiếu AUM API Endpoint.' );
	}

	$response = wp_safe_remote_post( $endpoint, [
		'headers' => [
			'Content-Type'  => 'application/json',
			'X-API-Key'     => $token,
		],
		'body'    => wp_json_encode( [
			'fullname'      => $payload['name'],
			'telephone'     => $payload['phone'],
			'email_address' => $payload['email'],
			'program_code'  => $payload['program'],
			'school_code'   => $payload['school'],
			'major_code'    => $payload['major'],
			'training'      => $payload['training_type'],
			'location'      => $payload['campus'],
			'utm_source'    => 'lienthongdaihoc.com',
			'url_referer'   => $payload['referrer'],
		] ),
		'timeout' => 15,
	] );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return new WP_Error( 'api_error', 'AUM API returned HTTP code ' . $code );
	}

	return true;
}

/**
 * ERPNext Webhook API Adapter (Prepared for future integration)
 */
function ltdh_sync_erpnext_adapter( $payload ) {
	$endpoint = get_field( 'erpnext_endpoint', 'options' );
	$token    = get_field( 'erpnext_token', 'options' ); // Token key:secret format

	if ( empty( $endpoint ) ) {
		return new WP_Error( 'missing_config', 'Thiếu ERPNext Webhook Endpoint.' );
	}

	$response = wp_safe_remote_post( $endpoint, [
		'headers' => [
			'Content-Type'  => 'application/json',
			'Authorization' => 'token ' . $token,
		],
		'body'    => wp_json_encode( [
			'naming_series'   => 'CRM-LEAD-.YYYY.-',
			'lead_name'       => $payload['name'],
			'phone'           => $payload['phone'],
			'email_id'        => $payload['email'],
			'custom_program'  => $payload['program'],
			'custom_school'   => $payload['school'],
			'custom_major'    => $payload['major'],
			'custom_training' => $payload['training_type'],
			'custom_campus'   => $payload['campus'],
			'source'          => 'lienthongdaihoc.com',
			'custom_referrer' => $payload['referrer'],
		] ),
		'timeout' => 15,
	] );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return new WP_Error( 'api_error', 'ERPNext Webhook returned HTTP code ' . $code );
	}

	return true;
}
