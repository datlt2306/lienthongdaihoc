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
	$table_name = $wpdb->prefix . LTDH_TABLE_LEADS;
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

/**
 * Check if the submission contains indicators of spam.
 */
function ltdh_is_spam_submission( array $data ): bool {
	$name    = isset( $data['name'] ) ? $data['name'] : '';
	$phone   = isset( $data['phone'] ) ? $data['phone'] : '';
	$email   = isset( $data['email'] ) ? $data['email'] : '';
	$message = isset( $data['message'] ) ? $data['message'] : '';

	// 1. Check for Cyrillic (Russian/Ukrainian/etc.) characters in any field
	if ( preg_match( '/[\p{Cyrillic}]/u', $name ) || 
	     preg_match( '/[\p{Cyrillic}]/u', $phone ) || 
	     preg_match( '/[\p{Cyrillic}]/u', $message ) ) {
		return true;
	}

	// 2. Check for links/URLs in the message or name
	if ( preg_match( '/https?:\/\//i', $message ) || preg_match( '/www\./i', $message ) ||
	     preg_match( '/https?:\/\//i', $name ) || preg_match( '/www\./i', $name ) ) {
		return true;
	}

	// 3. Validate Phone Number length and format (must start with 0, 84, or +84)
	$clean_phone = preg_replace( '/[^\d+]/', '', $phone );
	if ( ! empty( $phone ) ) {
		if ( strlen( $clean_phone ) < 8 || strlen( $clean_phone ) > 15 ) {
			return true;
		}
		if ( ! preg_match( '/^(0|\+84|84)/', $clean_phone ) ) {
			return true;
		}
	}

	return false;
}

// ----------------------------------------------------
// 2. Centralized Lead Insertion Function
// ----------------------------------------------------
function ltdh_insert_lead( array $data ): int {
	if ( ltdh_is_spam_submission( $data ) ) {
		return 0;
	}
	global $wpdb;
	$table_name = $wpdb->prefix . LTDH_TABLE_LEADS;

	$name            = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
	$phone           = isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '';
	$email           = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
	$program_id      = isset( $data['program_id'] ) ? intval( $data['program_id'] ) : 0;
	$school_id       = isset( $data['school_id'] ) ? intval( $data['school_id'] ) : 0;
	$major_id        = isset( $data['major_id'] ) ? intval( $data['major_id'] ) : 0;
	$training_type   = isset( $data['training_type'] ) ? sanitize_text_field( $data['training_type'] ) : '';
	$campus          = isset( $data['campus'] ) ? sanitize_text_field( $data['campus'] ) : '';
	$referral_source = isset( $data['referral_source'] ) ? sanitize_text_field( $data['referral_source'] ) : '';
	$message         = isset( $data['message'] ) ? sanitize_textarea_field( $data['message'] ) : '';

	// Resolve missing metadata based on program
	if ( $program_id > 0 ) {
		if ( ! $school_id ) {
			$school_id = intval( get_post_meta( $program_id, 'school_relationship', true ) );
		}
		if ( ! $major_id ) {
			$major_id = intval( get_post_meta( $program_id, 'major_relationship', true ) );
		}
		if ( empty( $training_type ) ) {
			$terms = wp_get_post_terms( $program_id, 'training_type' );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$training_type = $terms[0]->name;
			}
		}
		if ( empty( $campus ) ) {
			$campus_terms = wp_get_post_terms( $program_id, 'campus' );
			if ( ! is_wp_error( $campus_terms ) && ! empty( $campus_terms ) ) {
				$campus = $campus_terms[0]->name;
			}
		}
	}

	$inserted = $wpdb->insert(
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
			'error_message'   => $message,
			'created_at'      => current_time( 'mysql' ),
		],
		[ '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s' ]
	);

	if ( $inserted ) {
		$lead_id = $wpdb->insert_id;

		// Format and send to Telegram
		$notification_data = [
			'name'            => $name,
			'phone'           => $phone,
			'email'           => $email,
			'program_id'      => $program_id,
			'school_id'       => $school_id,
			'major_id'        => $major_id,
			'training_type'   => $training_type,
			'campus'          => $campus,
			'referral_source' => $referral_source,
			'message'         => $message,
		];
		ltdh_trigger_telegram_notification( $notification_data );

		return $lead_id;
	}

	return 0;
}

// ----------------------------------------------------
// 3. Telegram Notification Handler
// ----------------------------------------------------
function ltdh_trigger_telegram_notification( array $data ): void {
	$bot_token = defined( 'LTDH_TELEGRAM_BOT_TOKEN' ) ? LTDH_TELEGRAM_BOT_TOKEN : '';
	$chat_id   = defined( 'LTDH_TELEGRAM_CHAT_ID' ) ? LTDH_TELEGRAM_CHAT_ID : '';

	// Fallback to option fields
	if ( empty( $bot_token ) && function_exists( 'get_field' ) ) {
		$bot_token = get_field( 'telegram_bot_token', 'options' );
	}
	if ( empty( $chat_id ) && function_exists( 'get_field' ) ) {
		$chat_id = get_field( 'telegram_chat_id', 'options' );
	}

	if ( empty( $bot_token ) || empty( $chat_id ) ) {
		return; // Credentials not configured
	}

	$is_eligibility = ( isset( $data['referral_source'] ) && strpos( $data['referral_source'], 'eligibility_checker' ) !== false );

	$degree_link = '';
	if ( ! empty( $data['referral_source'] ) ) {
		$query_str = parse_url( $data['referral_source'], PHP_URL_QUERY );
		if ( $query_str ) {
			parse_str( $query_str, $query_params );
			if ( ! empty( $query_params['degree_link'] ) ) {
				$degree_link = $query_params['degree_link'];
			}
		}
	}
	if ( empty( $degree_link ) && ! empty( $data['degree_link'] ) ) {
		$degree_link = $data['degree_link'];
	}

	$name    = ! empty( $data['name'] ) ? $data['name'] : 'N/A';
	$phone   = ! empty( $data['phone'] ) ? $data['phone'] : 'N/A';
	$email   = ! empty( $data['email'] ) ? $data['email'] : 'N/A';
	$message = ! empty( $data['message'] ) ? $data['message'] : '';

	if ( $is_eligibility ) {
		// Full Detail notification for Eligibility Checker
		$training_type = ! empty( $data['training_type'] ) ? $data['training_type'] : 'N/A';
		$campus        = ! empty( $data['campus'] ) ? $data['campus'] : 'N/A';
		
		$school_title  = 'N/A';
		$major_title   = 'N/A';

		if ( ! empty( $data['school_id'] ) ) {
			$school_title = get_the_title( $data['school_id'] );
		}
		if ( ! empty( $data['major_id'] ) ) {
			$major_title = get_the_title( $data['major_id'] );
		}
		$referral = $data['referral_source'];

		$msg_text  = "🔔 <b>ĐÁNH GIÁ ĐIỀU KIỆN TUYỂN SINH MỚI</b> 🔔\n\n";
		$msg_text .= "👤 <b>Họ và tên:</b> " . esc_html( $name ) . "\n";
		$msg_text .= "📞 <b>Số điện thoại:</b> " . esc_html( $phone ) . "\n";
		$msg_text .= "✉ <b>Email:</b> " . esc_html( $email ) . "\n";
		$msg_text .= "🏫 <b>Trường đối tác:</b> " . esc_html( $school_title ) . "\n";
		$msg_text .= "🎓 <b>Chuyên ngành:</b> " . esc_html( $major_title ) . "\n";
		if ( $training_type !== 'N/A' ) {
			$msg_text .= "🏷 <b>Hệ học:</b> " . esc_html( $training_type ) . "\n";
		}
		if ( $campus !== 'N/A' ) {
			$msg_text .= "📍 <b>Cơ sở:</b> " . esc_html( $campus ) . "\n";
		}
		if ( ! empty( $message ) ) {
			$msg_text .= "💬 <b>Nội dung yêu cầu:</b> " . esc_html( $message ) . "\n";
		}
		if ( ! empty( $degree_link ) ) {
			$msg_text .= "📎 <b>Ảnh bằng cấp:</b> " . esc_url( $degree_link ) . "\n";
		}
	} else {
		// Simple notification for Free Consultation forms
		$msg_text  = "🔔 <b>YÊU CẦU TƯ VẤN MIỄN PHÍ MỚI</b> 🔔\n\n";
		$msg_text .= "👤 <b>Họ và tên:</b> " . esc_html( $name ) . "\n";
		$msg_text .= "📞 <b>Số điện thoại:</b> " . esc_html( $phone ) . "\n";
		$msg_text .= "✉ <b>Email:</b> " . esc_html( $email ) . "\n";
		if ( ! empty( $message ) ) {
			$msg_text .= "💬 <b>Nội dung yêu cầu:</b> " . esc_html( $message ) . "\n";
		}
		if ( ! empty( $degree_link ) ) {
			$msg_text .= "📎 <b>Ảnh bằng cấp:</b> " . esc_url( $degree_link ) . "\n";
		}
	}
	
	$msg_text .= "📅 <b>Thời gian:</b> " . current_time( 'd/m/Y H:i:s' ) . "\n";

	// Split chat IDs by comma, semicolon, or space to support multiple recipients
	$chat_ids = preg_split( '/[\s,;]+/', $chat_id );
	$chat_ids = array_filter( array_map( 'trim', $chat_ids ) );

	if ( empty( $chat_ids ) ) {
		return;
	}

	$api_url = "https://api.telegram.org/bot" . urlencode( $bot_token ) . "/sendMessage";

	foreach ( $chat_ids as $single_chat_id ) {
		// Use non-blocking wp_remote_post
		wp_remote_post( $api_url, [
			'body' => [
				'chat_id'    => $single_chat_id,
				'text'       => $msg_text,
				'parse_mode' => 'HTML',
			],
			'timeout'  => 10,
			'blocking' => false,
		] );
	}
}

// ----------------------------------------------------
// 4. CF7 Lead Interceptor
// ----------------------------------------------------
add_action( 'wpcf7_before_send_mail', 'ltdh_capture_cf7_lead', 10, 3 );


function ltdh_capture_cf7_lead( $contact_form, &$abort, $submission ) {
	$posted_data = $submission->get_posted_data();

	$name    = isset( $posted_data['your-name'] ) ? sanitize_text_field( $posted_data['your-name'] ) : '';
	$phone   = isset( $posted_data['your-phone'] ) ? sanitize_text_field( $posted_data['your-phone'] ) : '';
	$email   = isset( $posted_data['your-email'] ) ? sanitize_email( $posted_data['your-email'] ) : '';
	$message = isset( $posted_data['your-message'] ) ? sanitize_textarea_field( $posted_data['your-message'] ) : '';

	// Perform spam check
	if ( ltdh_is_spam_submission( [ 'name' => $name, 'phone' => $phone, 'email' => $email, 'message' => $message ] ) ) {
		$abort = true;
		return;
	}

	if ( empty( $name ) || empty( $phone ) ) {
		return;
	}

	$program_id      = isset( $posted_data['current_program_id'] ) ? intval( $posted_data['current_program_id'] ) : 0;
	$school_id       = isset( $posted_data['current_school_id'] ) ? intval( $posted_data['current_school_id'] ) : 0;
	$major_id        = isset( $posted_data['current_major_id'] ) ? intval( $posted_data['current_major_id'] ) : 0;

	$referral_source = isset( $posted_data['referral_source'] ) ? esc_url_raw( $posted_data['referral_source'] ) : '';
	if ( empty( $referral_source ) ) {
		$referral_source = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '';
	}

	ltdh_insert_lead( [
		'name'            => $name,
		'phone'           => $phone,
		'email'           => $email,
		'program_id'      => $program_id,
		'school_id'       => $school_id,
		'major_id'        => $major_id,
		'referral_source' => $referral_source,
		'message'         => $message,
	] );
}

// ----------------------------------------------------
// 5. Native Form Submission Handler
// ----------------------------------------------------
add_action( 'template_redirect', 'ltdh_handle_native_form_submit' );

function ltdh_handle_native_form_submit() {
	if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return;
	}

	if ( ! isset( $_POST['your-name'] ) || ! isset( $_POST['your-phone'] ) ) {
		return;
	}

	$name    = sanitize_text_field( wp_unslash( $_POST['your-name'] ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['your-phone'] ) );
	$email   = isset( $_POST['your-email'] ) ? sanitize_email( wp_unslash( $_POST['your-email'] ) ) : '';
	$message = isset( $_POST['your-message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['your-message'] ) ) : '';

	// Perform spam check
	if ( ltdh_is_spam_submission( [ 'name' => $name, 'phone' => $phone, 'email' => $email, 'message' => $message ] ) ) {
		wp_die( 'Yêu cầu của bạn bị chặn do nghi ngờ spam. Vui lòng liên hệ hotline.', 'Spam Blocked', [ 'response' => 403 ] );
	}

	if ( empty( $name ) || empty( $phone ) ) {
		return;
	}

	$program_id    = isset( $_POST['current_program_id'] ) ? intval( $_POST['current_program_id'] ) : 0;
	$school_id     = isset( $_POST['current_school_id'] ) ? intval( $_POST['current_school_id'] ) : 0;
	$major_id      = isset( $_POST['current_major_id'] ) ? intval( $_POST['current_major_id'] ) : 0;

	$referral_source = isset( $_POST['referral_source'] ) ? esc_url_raw( wp_unslash( $_POST['referral_source'] ) ) : '';
	if ( empty( $referral_source ) ) {
		$referral_source = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '';
	}

	$inserted_id = ltdh_insert_lead( [
		'name'            => $name,
		'phone'           => $phone,
		'email'           => $email,
		'program_id'      => $program_id,
		'school_id'       => $school_id,
		'major_id'        => $major_id,
		'referral_source' => $referral_source,
		'message'         => $message,
	] );

	if ( $inserted_id ) {
		$redirect_url = add_query_arg( 'submit_success', '1', wp_get_referer() ?: home_url( '/' ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}
}
