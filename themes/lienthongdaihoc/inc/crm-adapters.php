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
		   OR (sync_status = 'failed' AND (
		        (retry_count < 5) OR 
		        (retry_count >= 5 AND retry_count < 15 AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR))
		      ))
		   OR (sync_status = 'processing' AND created_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE))
		ORDER BY id ASC
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
			$new_retry_count = (int) $lead->retry_count + 1;
			$new_status      = ( $new_retry_count >= 15 ) ? 'failed_permanent' : 'failed';
			$wpdb->update(
				$table_name,
				[
					'sync_status'   => $new_status,
					'retry_count'   => $new_retry_count,
					'error_message' => $result->get_error_message(),
				],
				[ 'id' => $lead->id ]
			);
			if ( 'failed_permanent' === $new_status ) {
				error_log( sprintf( '[LTDH CRM Alert] Lead #%d (%s - %s) reached max retries. Error: %s', $lead->id, $lead->name, $lead->phone, $result->get_error_message() ) );
			}
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
	// Determine default CRM type from ACF options or constants
	$default_crm_type = function_exists( 'get_field' ) ? get_field( 'default_crm_type', 'options' ) : '';
	if ( empty( $default_crm_type ) && defined( 'LTDH_DEFAULT_CRM_TYPE' ) ) {
		$default_crm_type = LTDH_DEFAULT_CRM_TYPE;
	}

	// [CBR-02]: Handle general leads without a designated school.
	// Only our master internal CRM (ERPNext) receives general leads.
	// Third-party partner CRMs (OnSchool, AUM) must NEVER receive general leads.
	if ( empty( $lead->school_id ) ) {
		if ( 'erpnext' === $default_crm_type ) {
			$program_name = $lead->program_id ? get_the_title( $lead->program_id ) : 'Tư vấn tuyển sinh chung';
			$school_name  = 'Cổng tuyển sinh Liên thông Đại học';
			$major_name   = $lead->major_id ? get_the_title( $lead->major_id ) : 'Chưa chọn';

			$payload = [
				'name'          => $lead->name,
				'phone'         => $lead->phone,
				'email'         => $lead->email,
				'program'       => $program_name,
				'school'        => $school_name,
				'major'         => $major_name,
				'training_type' => $lead->training_type,
				'campus'        => $lead->campus,
				'sales_partner' => $lead->sales_partner ?? '',
				'referrer'      => $lead->referral_source,
				'message'       => $lead->message ?? '',
			];

			return ltdh_sync_erpnext_adapter( $payload );
		}

		return true; // Handled internally
	}

	$school_id  = intval( $lead->school_id );
	$school_crm = function_exists( 'get_field' ) ? get_field( 'crm_provider', $school_id ) : '';

	// Determine CRM provider: school override or global default
	$crm_type = ( ! empty( $school_crm ) && $school_crm !== 'default' ) ? $school_crm : $default_crm_type;
	$endpoint = '';
	$token    = '';

	if ( ! $crm_type || $crm_type === 'internal' ) {
		return true; // No third-party CRM sync needed
	}

	// Fetch related entity titles for richer payload context
	$program_name = $lead->program_id ? get_the_title( $lead->program_id ) : 'Không xác định';
	$school_name  = $school_id ? get_the_title( $school_id ) : 'Không xác định';
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
		'sales_partner' => $lead->sales_partner ?? '',
		'referrer'      => $lead->referral_source,
		'message'       => $lead->message ?? '',
	];

	if ( $crm_type === 'onschool' ) {
		return ltdh_sync_onschool_adapter( $payload, $endpoint, $token );
	} elseif ( $crm_type === 'aum' ) {
		return ltdh_sync_aum_adapter( $payload, $endpoint, $token );
	} elseif ( $crm_type === 'erpnext' ) {
		return ltdh_sync_erpnext_adapter( $payload, $endpoint, $token );
	} elseif ( $crm_type === 'custom_webhook' ) {
		return ltdh_sync_custom_webhook_adapter( $payload, $endpoint, $token );
	}

	return new WP_Error( 'invalid_crm', 'Hệ thống CRM cấu hình không hợp lệ.' );
}

/**
 * OnSchool API Adapter
 */
function ltdh_sync_onschool_adapter( $payload, $endpoint = '', $token = '' ) {
	if ( empty( $endpoint ) && function_exists( 'get_field' ) ) {
		$endpoint = get_field( 'onschool_endpoint', 'options' );
	}
	if ( empty( $token ) && function_exists( 'get_field' ) ) {
		$token = get_field( 'onschool_token', 'options' );
	}

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
			'note'         => $payload['message'],
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
function ltdh_sync_aum_adapter( $payload, $endpoint = '', $token = '' ) {
	if ( empty( $endpoint ) && function_exists( 'get_field' ) ) {
		$endpoint = get_field( 'aum_endpoint', 'options' );
	}
	if ( empty( $token ) && function_exists( 'get_field' ) ) {
		$token = get_field( 'aum_token', 'options' );
	}

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
			'note'          => $payload['message'],
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
 * ERPNext Webhook / REST API Adapter
 */
function ltdh_sync_erpnext_adapter( $payload, $endpoint = '', $token = '' ) {
	if ( empty( $endpoint ) && function_exists( 'get_field' ) ) {
		$endpoint = get_field( 'erpnext_endpoint', 'options' );
	}
	if ( empty( $endpoint ) && defined( 'LTDH_ERPNEXT_ENDPOINT' ) ) {
		$endpoint = LTDH_ERPNEXT_ENDPOINT;
	}

	if ( empty( $token ) && function_exists( 'get_field' ) ) {
		$token = get_field( 'erpnext_token', 'options' );
	}
	if ( empty( $token ) && defined( 'LTDH_ERPNEXT_TOKEN' ) ) {
		$token = LTDH_ERPNEXT_TOKEN;
	}

	if ( empty( $endpoint ) ) {
		return new WP_Error( 'missing_config', 'Thiếu ERPNext Webhook / REST Endpoint.' );
	}
	if ( empty( $token ) ) {
		return new WP_Error( 'missing_config', 'Thiếu ERPNext Auth Token.' );
	}

	// Construct comprehensive notes for ERPNext CRM Note child table
	$note_lines = [];
	if ( ! empty( $payload['program'] ) && 'Không xác định' !== $payload['program'] ) {
		$note_lines[] = 'Chương trình: ' . $payload['program'];
	}
	if ( ! empty( $payload['school'] ) && 'Không xác định' !== $payload['school'] ) {
		$note_lines[] = 'Trường đào tạo: ' . $payload['school'];
	}
	if ( ! empty( $payload['major'] ) && 'Không xác định' !== $payload['major'] ) {
		$note_lines[] = 'Ngành đào tạo: ' . $payload['major'];
	}
	if ( ! empty( $payload['training_type'] ) ) {
		$note_lines[] = 'Hình thức đào tạo: ' . $payload['training_type'];
	}
	if ( ! empty( $payload['campus'] ) ) {
		$note_lines[] = 'Cơ sở / Khu vực: ' . $payload['campus'];
	}
	if ( ! empty( $payload['sales_partner'] ) ) {
		$note_lines[] = 'Người giới thiệu (Ref): ' . $payload['sales_partner'];
	}
	if ( ! empty( $payload['referrer'] ) ) {
		$note_lines[] = 'Nguồn giới thiệu: ' . $payload['referrer'];
	}
	if ( ! empty( $payload['message'] ) ) {
		$note_lines[] = 'Ghi chú / Lời nhắn: ' . $payload['message'];
	}

	$notes = [];
	if ( ! empty( $note_lines ) ) {
		$notes[] = [
			'doctype' => 'CRM Note',
			'note'    => implode( "\n", $note_lines ),
		];
	}

	$company = defined( 'LTDH_ERPNEXT_COMPANY' ) ? LTDH_ERPNEXT_COMPANY : 'Liên thông giao thông vận tải';

	// Tự động phân loại trường đẩy vào ERPNext
	$school_name  = ! empty( $payload['school'] ) ? $payload['school'] : '';
	$program_name = ! empty( $payload['program'] ) ? $payload['program'] : '';

	$target_school = 'Nội bộ (Các ngành khác)';
	if ( stripos( $school_name, 'Giao thông' ) !== false || 
	     stripos( $school_name, 'GTVT' ) !== false || 
	     stripos( $program_name, 'Giao thông' ) !== false || 
	     stripos( $program_name, 'GTVT' ) !== false ) {
		$target_school = 'ĐH Giao thông Vận tải';
	}

	$body_data = [
		'naming_series'         => 'CRM-LEAD-.YYYY.-',
		'lead_name'             => $payload['name'],
		'mobile_no'             => $payload['phone'],
		'phone'                 => $payload['phone'],
		'email_id'              => ! empty( $payload['email'] ) ? $payload['email'] : '',
		'company'               => $company,
		'territory'             => 'Vietnam',
		'utm_source'            => 'Website',
		'status'                => 'Lead',
		'qualification_status'  => 'Unqualified',
		'custom_trường_đăng_ký' => $target_school,
		'custom_người_giới_thiệu' => ! empty( $payload['sales_partner'] ) ? $payload['sales_partner'] : '',
	];

	if ( ! empty( $notes ) ) {
		$body_data['notes'] = $notes;
	}

	$response = wp_remote_post( $endpoint, [
		'headers'            => [
			'Content-Type'  => 'application/json',
			'Authorization' => 'token ' . $token,
		],
		'body'               => wp_json_encode( $body_data ),
		'timeout'            => 15,
		'reject_unsafe_urls' => false,
	] );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		$body = wp_remote_retrieve_body( $response );
		return new WP_Error( 'api_error', 'ERPNext API returned HTTP code ' . $code . ': ' . wp_strip_all_tags( $body ) );
	}

	return true;
}

/**
 * Custom Webhook Adapter
 */
function ltdh_sync_custom_webhook_adapter( $payload, $endpoint = '', $token = '' ) {
	if ( empty( $endpoint ) ) {
		return new WP_Error( 'missing_config', 'Thiếu Custom Webhook Endpoint.' );
	}

	$headers = [ 'Content-Type' => 'application/json' ];
	if ( ! empty( $token ) ) {
		$headers['Authorization'] = 'Bearer ' . $token;
		$headers['X-API-Key']     = $token;
	}

	$response = wp_safe_remote_post( $endpoint, [
		'headers' => $headers,
		'body'    => wp_json_encode( $payload ),
		'timeout' => 15,
	] );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return new WP_Error( 'api_error', 'Custom Webhook returned HTTP code ' . $code );
	}

	return true;
}
