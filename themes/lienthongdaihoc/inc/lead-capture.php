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
add_action( 'admin_init', 'ltdh_check_leads_table_schema' );

/**
 * Whitelist Contact Form 7 and theme REST API endpoints in AIOS (All-In-One Security)
 * to prevent 403 Forbidden errors on visitor form submissions and AJAX tools.
 */
add_filter( 'aios_whitelisted_rest_routes', 'ltdh_whitelist_security_rest_routes' );
function ltdh_whitelist_security_rest_routes( $routes ) {
	if ( ! is_array( $routes ) ) {
		$routes = [];
	}
	$essential = [ 'contact-form-7', 'ltdh' ];
	foreach ( $essential as $route ) {
		if ( ! in_array( $route, $routes, true ) ) {
			$routes[] = $route;
		}
	}
	return $routes;
}

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
		sales_partner varchar(100) DEFAULT '',
		referral_source text DEFAULT '',
		message text DEFAULT NULL,
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

function ltdh_check_leads_table_schema() {
	global $wpdb;
	$table_name = $wpdb->prefix . LTDH_TABLE_LEADS;
	$col_check  = $wpdb->get_results( $wpdb->prepare(
		"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'message'",
		DB_NAME,
		$table_name
	) );

	if ( empty( $col_check ) ) {
		$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN message text DEFAULT NULL AFTER referral_source" );
	}

	$col_partner = $wpdb->get_results( $wpdb->prepare(
		"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'sales_partner'",
		DB_NAME,
		$table_name
	) );

	if ( empty( $col_partner ) ) {
		$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN sales_partner varchar(100) DEFAULT '' AFTER campus" );
	}
}

/**
 * Determine whether an IP belongs to official Cloudflare IPv4/IPv6 proxy ranges.
 */
if ( ! function_exists( 'ltdh_ip_in_cidr' ) ) {
	function ltdh_ip_in_cidr( string $ip, string $cidr ): bool {
		if ( false === strpos( $cidr, '/' ) ) {
			return $ip === $cidr;
		}
		list( $subnet, $bits ) = explode( '/', $cidr, 2 );
		$bits    = (int) $bits;
		$ip_bin  = @inet_pton( $ip );
		$sub_bin = @inet_pton( $subnet );
		if ( false === $ip_bin || false === $sub_bin || strlen( $ip_bin ) !== strlen( $sub_bin ) ) {
			return false;
		}
		$max_bits = strlen( $ip_bin ) * 8;
		if ( $bits < 0 || $bits > $max_bits ) {
			return false;
		}
		$bytes = intdiv( $bits, 8 );
		$rem   = $bits % 8;
		if ( $bytes > 0 && substr( $ip_bin, 0, $bytes ) !== substr( $sub_bin, 0, $bytes ) ) {
			return false;
		}
		if ( $rem > 0 ) {
			$mask = ( 0xFF << ( 8 - $rem ) ) & 0xFF;
			if ( ( ord( $ip_bin[ $bytes ] ) & $mask ) !== ( ord( $sub_bin[ $bytes ] ) & $mask ) ) {
				return false;
			}
		}
		return true;
	}
}

if ( ! function_exists( 'ltdh_is_cloudflare_ip' ) ) {
	function ltdh_is_cloudflare_ip( string $ip ): bool {
		$cf_cidrs = [
			'173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
			'141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
			'197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
			'104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
			'2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
			'2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
		];
		foreach ( $cf_cidrs as $cidr ) {
			if ( ltdh_ip_in_cidr( $ip, $cidr ) ) {
				return true;
			}
		}
		return false;
	}
}

if ( ! function_exists( 'ltdh_get_client_ip' ) ) {
	function ltdh_get_client_ip(): string {
		$remote_addr = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		if ( ! empty( $remote_addr ) && ltdh_is_cloudflare_ip( $remote_addr ) && ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$cf_ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
			if ( filter_var( $cf_ip, FILTER_VALIDATE_IP ) ) {
				return $cf_ip;
			}
		}
		return filter_var( $remote_addr, FILTER_VALIDATE_IP ) ? $remote_addr : '';
	}
}

if ( ! function_exists( 'ltdh_is_local_dev_site' ) ) {
	function ltdh_is_local_dev_site(): bool {
		if ( function_exists( 'wp_get_environment_type' ) && 'local' === wp_get_environment_type() ) {
			return true;
		}
		$host = strtolower( (string) parse_url( home_url(), PHP_URL_HOST ) );
		return in_array( $host, [ 'localhost', '127.0.0.1' ], true ) || str_ends_with( $host, '.local' );
	}
}

if ( ! function_exists( 'ltdh_cleanup_ephemeral_files' ) ) {
	function ltdh_cleanup_ephemeral_files( array $paths ): void {
		foreach ( $paths as $path ) {
			if ( is_string( $path ) && '' !== $path && file_exists( $path ) && is_file( $path ) ) {
				@unlink( $path );
			}
		}
	}
}

if ( ! function_exists( 'ltdh_handle_ephemeral_document_upload' ) ) {
	/**
	 * Store an uploaded verification document in a protected non-public directory
	 * after strict binary signature validation, for immediate ephemeral Telegram forwarding.
	 *
	 * @param array $file $_FILES entry.
	 * @return array|\WP_Error ['file' => string] on success, WP_Error on failure.
	 */
	function ltdh_handle_ephemeral_document_upload( array $file ) {
		$max_file_size = 5 * 1024 * 1024;
		if ( ! empty( $file['error'] ) && UPLOAD_ERR_OK !== (int) $file['error'] ) {
			return new \WP_Error( 'upload_err', 'Tải tệp lên thất bại.' );
		}
		if ( empty( $file['tmp_name'] ) || ! is_readable( $file['tmp_name'] ) ) {
			return new \WP_Error( 'invalid_tmp', 'Tệp tải lên không hợp lệ.' );
		}
		if ( PHP_SAPI !== 'cli' && ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new \WP_Error( 'not_uploaded', 'Nguồn tệp tải lên không hợp lệ.' );
		}
		$size = (int) ( $file['size'] ?? filesize( $file['tmp_name'] ) );
		if ( $size <= 0 || $size > $max_file_size ) {
			return new \WP_Error( 'file_too_large', 'Dung lượng tệp vượt quá giới hạn 5MB cho phép.' );
		}

		$fp = @fopen( $file['tmp_name'], 'rb' );
		if ( ! $fp ) {
			return new \WP_Error( 'read_err', 'Không thể đọc tệp tải lên.' );
		}
		$header = fread( $fp, 12 );
		fclose( $fp );

		$orig_ext  = strtolower( pathinfo( (string) ( $file['name'] ?? '' ), PATHINFO_EXTENSION ) );
		$valid_ext = '';
		$expected_mime = '';

		if ( 'pdf' === $orig_ext && str_starts_with( $header, '%PDF-' ) ) {
			$valid_ext     = 'pdf';
			$expected_mime = 'application/pdf';
		} elseif ( 'png' === $orig_ext && str_starts_with( $header, "\x89PNG\r\n\x1a\n" ) ) {
			$valid_ext     = 'png';
			$expected_mime = 'image/png';
		} elseif ( in_array( $orig_ext, [ 'jpg', 'jpeg', 'jpe' ], true ) && str_starts_with( $header, "\xFF\xD8\xFF" ) ) {
			$valid_ext     = 'jpg';
			$expected_mime = 'image/jpeg';
		} elseif ( 'webp' === $orig_ext && strlen( $header ) >= 12 && 'RIFF' === substr( $header, 0, 4 ) && 'WEBP' === substr( $header, 8, 4 ) ) {
			$valid_ext     = 'webp';
			$expected_mime = 'image/webp';
		} else {
			return new \WP_Error( 'bad_signature', 'Định dạng tệp không hợp lệ (chỉ hỗ trợ JPG, PNG, WEBP, PDF chuẩn).' );
		}

		if ( function_exists( 'finfo_open' ) ) {
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			if ( $finfo ) {
				$detected_mime = finfo_file( $finfo, $file['tmp_name'] );
				finfo_close( $finfo );
				if ( $detected_mime !== $expected_mime ) {
					return new \WP_Error( 'mime_mismatch', 'Nội dung tệp không khớp với định dạng khai báo.' );
				}
			}
		}

		$upload_dir = wp_upload_dir();
		if ( empty( $upload_dir['basedir'] ) ) {
			return new \WP_Error( 'upload_dir_err', 'Không thể khởi tạo thư mục lưu tạm.' );
		}
		$ephemeral_dir = trailingslashit( $upload_dir['basedir'] ) . 'ltdh-ephemeral';
		if ( ! file_exists( $ephemeral_dir ) ) {
			wp_mkdir_p( $ephemeral_dir );
		}
		$htaccess_path = $ephemeral_dir . '/.htaccess';
		if ( ! file_exists( $htaccess_path ) ) {
			@file_put_contents( $htaccess_path, "<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tOrder deny,allow\n\tDeny from all\n</IfModule>\n" );
		}
		$index_path = $ephemeral_dir . '/index.php';
		if ( ! file_exists( $index_path ) ) {
			@file_put_contents( $index_path, "<?php\n// Silence is golden.\n" );
		}

		$dest_filename = 'doc-' . bin2hex( random_bytes( 16 ) ) . '.' . $valid_ext;
		$dest_path     = $ephemeral_dir . '/' . $dest_filename;

		$moved = ( PHP_SAPI === 'cli' )
			? @copy( $file['tmp_name'], $dest_path )
			: @move_uploaded_file( $file['tmp_name'], $dest_path );

		if ( ! $moved || ! file_exists( $dest_path ) ) {
			return new \WP_Error( 'move_failed', 'Không thể xử lý tệp tải lên.' );
		}
		@chmod( $dest_path, 0600 );

		return [ 'file' => $dest_path ];
	}
}

/**
 * Check if current IP address has exceeded the rate limit (10 submissions per 10 minutes).
 */
function ltdh_is_ip_rate_limited(): bool {
	if ( PHP_SAPI === 'cli' || defined( 'WP_CLI' ) ) {
		return false;
	}
	// Whitelist administrators and authenticated editors
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return false;
	}

	$ip = ltdh_get_client_ip();
	if ( empty( $ip ) ) {
		return false;
	}
	if ( ltdh_is_local_dev_site() && ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
		return false;
	}
	$transient_key = 'ltdh_rl_' . md5( $ip );
	$count         = (int) get_transient( $transient_key );
	return ( $count >= 10 );
}

/**
 * Increment the submission counter for current IP.
 */
function ltdh_increment_ip_rate_limit(): void {
	if ( PHP_SAPI === 'cli' || defined( 'WP_CLI' ) ) {
		return;
	}
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return;
	}

	$ip = ltdh_get_client_ip();
	if ( empty( $ip ) ) {
		return;
	}
	if ( ltdh_is_local_dev_site() && ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
		return;
	}
	$transient_key = 'ltdh_rl_' . md5( $ip );
	$count         = (int) get_transient( $transient_key );
	set_transient( $transient_key, $count + 1, 600 ); // 10 minutes window
}

/**
 * Check if the submission contains indicators of spam.
 */
function ltdh_is_spam_submission( array $data ): bool {
	// 1. Invisible Honeypot check
	$hp_val = $data['hp_website'] ?? $data['website_url_hp'] ?? $data['fax_hp'] ?? '';
	if ( ! empty( $hp_val ) ) {
		return true; // Bot filled the invisible honeypot field
	}

	// 2. IP Rate Limit check (max 3 submissions / 10 mins)
	if ( ltdh_is_ip_rate_limited() ) {
		return true;
	}

	$name    = isset( $data['name'] ) ? $data['name'] : '';
	$phone   = isset( $data['phone'] ) ? $data['phone'] : '';
	$email   = isset( $data['email'] ) ? $data['email'] : '';
	$message = isset( $data['message'] ) ? $data['message'] : '';

	// 3. Check for Cyrillic (Russian/Ukrainian/etc.) characters in any field
	if ( preg_match( '/[\p{Cyrillic}]/u', $name ) || 
	     preg_match( '/[\p{Cyrillic}]/u', $phone ) || 
	     preg_match( '/[\p{Cyrillic}]/u', $message ) ) {
		return true;
	}

	// 4. Check for links/URLs in the message or name
	if ( preg_match( '/https?:\/\//i', $message ) || preg_match( '/www\./i', $message ) ||
	     preg_match( '/https?:\/\//i', $name ) || preg_match( '/www\./i', $name ) ) {
		return true;
	}

	// 5. Validate Phone Number format (Chuẩn số điện thoại di động & cố định Việt Nam)
	$clean_phone = preg_replace( '/[^\d+]/', '', (string) $phone );
	if ( ! empty( $clean_phone ) ) {
		$normalized_phone = preg_replace( '/^(\+84|84)/', '0', $clean_phone );
		if ( ! preg_match( '/^(0(3[2-9]|5[25689]|7[06-9]|8[1-9]|9[0-9])[0-9]{7}|02[0-9]{8,9})$/', $normalized_phone ) ) {
			return true;
		}
	}

	return false;
}

// ----------------------------------------------------
// 2. Centralized Lead Insertion Function
// ----------------------------------------------------
function ltdh_insert_lead( array $data ): int {
	$ephemeral_files = [
		$data['degree_file_path'] ?? '',
		$data['transcript_file_path'] ?? '',
	];
	try {
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
	$sales_partner   = ! empty( $data['sales_partner'] ) ? sanitize_text_field( $data['sales_partner'] ) : ( function_exists( 'ltdh_get_active_referral' ) ? ltdh_get_active_referral() : '' );
	$referral_source = isset( $data['referral_source'] ) ? sanitize_text_field( $data['referral_source'] ) : '';
	$message         = isset( $data['message'] ) ? sanitize_textarea_field( $data['message'] ) : '';
	$is_group_reg    = ! empty( $data['is_group_reg'] );

	if ( ! empty( $sales_partner ) ) {
		$ref_tag = '[🤝 Người giới thiệu (Ref): ' . $sales_partner . ']';
		if ( false === strpos( $message, $ref_tag ) ) {
			$message = trim( $ref_tag . ( $message ? "\n" . $message : '' ) );
		}
	}

	if ( $is_group_reg ) {
		$group_tag = '[👥 ĐĂNG KÝ THEO NHÓM - HỌC BỔNG ĐỒNG HÀNH]';
		if ( false === strpos( $message, $group_tag ) ) {
			$message = trim( $group_tag . ( $message ? "\n" . $message : '' ) );
		}
	}

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
			'sales_partner'   => $sales_partner,
			'referral_source' => $referral_source,
			'message'         => $message,
			'sync_status'     => 'pending',
			'retry_count'     => 0,
			'error_message'   => '',
			'created_at'      => current_time( 'mysql' ),
		],
		[ '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' ]
	);

	if ( $inserted ) {
		$lead_id = $wpdb->insert_id;

		// Format and send to Telegram
		$notification_data = [
			'name'                 => $name,
			'phone'                => $phone,
			'email'                => $email,
			'program_id'           => $program_id,
			'school_id'            => $school_id,
			'major_id'             => $major_id,
			'training_type'        => $training_type,
			'campus'               => $campus,
			'sales_partner'        => $sales_partner,
			'referral_source'      => $referral_source,
			'message'              => $message,
			'is_group_reg'         => $is_group_reg,
			'degree_file_path'     => $data['degree_file_path'] ?? '',
			'degree_link'          => $data['degree_link'] ?? '',
			'transcript_file_path' => $data['transcript_file_path'] ?? '',
			'transcript_link'      => $data['transcript_link'] ?? '',
		];
		ltdh_trigger_telegram_notification( $notification_data );
		ltdh_increment_ip_rate_limit();

		// Real-time CRM Sync: Attempt immediate sync to CRM (ERPNext / Partner CRM)
		if ( function_exists( 'ltdh_sync_lead_to_crm' ) ) {
			$lead_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $lead_id ) );
			if ( $lead_row ) {
				$sync_res = ltdh_sync_lead_to_crm( $lead_row );
				if ( is_wp_error( $sync_res ) ) {
					$wpdb->update(
						$table_name,
						[
							'sync_status'   => 'failed',
							'retry_count'   => 1,
							'error_message' => $sync_res->get_error_message(),
						],
						[ 'id' => $lead_id ]
					);
				} elseif ( true === $sync_res ) {
					$wpdb->update(
						$table_name,
						[
							'sync_status'   => 'synced',
							'synced_at'     => current_time( 'mysql' ),
							'error_message' => '',
						],
						[ 'id' => $lead_id ]
					);
				}
			}
		}

		// Automated Email Dispatch: Confirmation to Student and Alert to Admin
		if ( function_exists( 'ltdh_trigger_email_notifications' ) ) {
			try {
				ltdh_trigger_email_notifications( $notification_data, $lead_id );
			} catch ( \Throwable $e ) {
				error_log( '[LTDH Email Error] ' . $e->getMessage() );
			}
		}

		return $lead_id;
	}

	return 0;
	} finally {
		ltdh_cleanup_ephemeral_files( $ephemeral_files );
	}
}

// ----------------------------------------------------
// 3. Telegram Notification Handler
// ----------------------------------------------------
function ltdh_trigger_telegram_notification( array $data ): void {
	$ephemeral_files = [
		$data['degree_file_path'] ?? '',
		$data['transcript_file_path'] ?? '',
	];
	try {
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
	$transcript_link = '';
	if ( ! empty( $data['referral_source'] ) ) {
		$query_str = parse_url( $data['referral_source'], PHP_URL_QUERY );
		if ( $query_str ) {
			parse_str( $query_str, $query_params );
			if ( ! empty( $query_params['degree_link'] ) ) {
				$degree_link = $query_params['degree_link'];
			}
			if ( ! empty( $query_params['transcript_link'] ) ) {
				$transcript_link = $query_params['transcript_link'];
			}
		}
	}
	if ( empty( $degree_link ) && ! empty( $data['degree_link'] ) ) {
		$degree_link = $data['degree_link'];
	}
	if ( empty( $transcript_link ) && ! empty( $data['transcript_link'] ) ) {
		$transcript_link = $data['transcript_link'];
	}

	$name    = ! empty( $data['name'] ) ? $data['name'] : 'N/A';
	$phone   = ! empty( $data['phone'] ) ? $data['phone'] : 'N/A';
	$email   = ! empty( $data['email'] ) ? $data['email'] : 'N/A';
	$message = ! empty( $data['message'] ) ? $data['message'] : '';

	$is_group = ! empty( $data['is_group_reg'] );

	if ( $is_eligibility ) {
		// Full Detail notification for Eligibility Checker (2026–2027 Regulations)
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
		$referral = $data['referral_source'] ?? '';

		// Parse decision parameters from referral source
		$ref_query = parse_url( $referral, PHP_URL_QUERY );
		$ref_params = [];
		if ( $ref_query ) {
			parse_str( $ref_query, $ref_params );
		}
		$decision_status = $ref_params['decision_status'] ?? '';
		$education_level = $ref_params['education_level'] ?? '';
		$thpt_status     = $ref_params['thpt_status'] ?? '';
		$months_grad     = $ref_params['months_since_grad'] ?? '';
		$grad_grade      = $ref_params['grad_grade'] ?? '';
		$major_relation  = $ref_params['major_relation'] ?? '';
		$time_pref       = $ref_params['time_preference'] ?? '';
		$suit_types      = $ref_params['suit_types'] ?? '';

		if ( 'warning_urgent' === $decision_status ) {
			$msg_text = "🟡 <b>[ĐỦ ĐIỀU KIỆN CÓ LƯU Ý] HỒ SƠ CẦN HƯỚNG DẪN</b> ⏳\n\n";
		} elseif ( in_array( $decision_status, [ 'not_eligible', 'not_eligible_2027' ], true ) ) {
			$msg_text = "🔴 <b>[CHƯA ĐỦ ĐIỀU KIỆN] HỒ SƠ THEO QUY CHẾ MỚI</b> ⚠️\n\n";
		} elseif ( $is_group ) {
			$msg_text = "🚨 <b>ĐÁNH GIÁ ĐIỀU KIỆN - ĐĂNG KÝ THEO NHÓM!</b> 👥\n\n";
		} else {
			$msg_text = "🟢 <b>[ĐỦ ĐIỀU KIỆN] HỒ SƠ ĐÁNH GIÁ LIÊN THÔNG MỚI</b> 🎓\n\n";
		}

		if ( 'warning_urgent' === $decision_status ) {
			$msg_text .= "⚡ <b>Trạng thái:</b> 🟡 <b>ĐỦ ĐIỀU KIỆN CÓ LƯU Ý</b> (Cần thẩm định điểm sàn / môn bổ sung)\n";
		} elseif ( in_array( $decision_status, [ 'not_eligible', 'not_eligible_2027' ], true ) ) {
			$msg_text .= "⚡ <b>Trạng thái:</b> 🔴 <b>CHƯA ĐỦ ĐIỀU KIỆN</b> (Theo quy chế mới của Bộ GD&ĐT)\n";
		} elseif ( 'eligible' === $decision_status ) {
			$msg_text .= "⚡ <b>Trạng thái:</b> 🟢 <b>ĐỦ ĐIỀU KIỆN XÉT TUYỂN</b>\n";
		}

		$msg_text .= "👤 <b>Họ và tên:</b> " . esc_html( $name ) . "\n";
		$msg_text .= "📞 <b>Số điện thoại:</b> " . esc_html( $phone ) . "\n";
		if ( $is_group ) {
			$msg_text .= "🎁 <b>Hình thức:</b> 👥 <b>ĐĂNG KÝ THEO NHÓM (Học bổng đồng hành)</b>\n";
		} else {
			$msg_text .= "🎁 <b>Hình thức:</b> 👤 Đăng ký cá nhân\n";
		}
		if ( ! empty( $data['sales_partner'] ) ) {
			$msg_text .= "🤝 <b>Người giới thiệu (Ref):</b> <code>" . esc_html( $data['sales_partner'] ) . "</code>\n";
		}
		$msg_text .= "✉ <b>Email:</b> " . esc_html( $email ) . "\n";

		// Detailed Profile Breakdown
		if ( $education_level && function_exists( 'ltdh_elig_get_education_label' ) ) {
			$msg_text .= "🎓 <b>Trình độ hiện có:</b> " . esc_html( ltdh_elig_get_education_label( $education_level ) ) . "\n";
		}
		if ( $thpt_status && function_exists( 'ltdh_elig_get_thpt_status_label' ) ) {
			$msg_text .= "📜 <b>Tình trạng THPT:</b> " . esc_html( ltdh_elig_get_thpt_status_label( $thpt_status ) ) . "\n";
		}
		if ( $months_grad !== '' && is_numeric( $months_grad ) ) {
			$msg_text .= "⏱️ <b>Thời gian tốt nghiệp:</b> " . intval( $months_grad ) . " tháng\n";
		}
		if ( $grad_grade && function_exists( 'ltdh_elig_get_graduation_grade_label' ) ) {
			$msg_text .= "📊 <b>Học lực tốt nghiệp:</b> " . esc_html( ltdh_elig_get_graduation_grade_label( $grad_grade ) ) . "\n";
		}
		if ( $major_relation && function_exists( 'ltdh_elig_get_major_relation_label' ) ) {
			$msg_text .= "🔄 <b>Liên quan ngành:</b> " . esc_html( ltdh_elig_get_major_relation_label( $major_relation ) ) . "\n";
		}
		if ( $time_pref && function_exists( 'ltdh_elig_get_time_preference_label' ) ) {
			$msg_text .= "⏰ <b>Quỹ thời gian:</b> " . esc_html( ltdh_elig_get_time_preference_label( $time_pref ) ) . "\n";
		}
		if ( $suit_types ) {
			$msg_text .= "💡 <b>Hệ đề xuất (TT 56):</b> <code>" . esc_html( $suit_types ) . "</code>\n";
		}

		$msg_text .= "🏫 <b>Trường quan tâm:</b> " . esc_html( $school_title ) . "\n";
		$msg_text .= "🎯 <b>Chuyên ngành:</b> " . esc_html( $major_title ) . "\n";
		if ( $training_type !== 'N/A' ) {
			$msg_text .= "🏷 <b>Hệ học:</b> " . esc_html( $training_type ) . "\n";
		}
		if ( $campus !== 'N/A' ) {
			$msg_text .= "📍 <b>Cơ sở:</b> " . esc_html( $campus ) . "\n";
		}
		if ( ! empty( $message ) ) {
			$msg_text .= "💬 <b>Ghi chú hồ sơ:</b> " . esc_html( $message ) . "\n";
		}
		if ( ! empty( $degree_link ) ) {
			$msg_text .= "📎 <b>Ảnh bằng cấp:</b> " . esc_url( $degree_link ) . "\n";
		}
		if ( ! empty( $transcript_link ) ) {
			$msg_text .= "📊 <b>Ảnh/link bảng điểm:</b> " . esc_url( $transcript_link ) . "\n";
		}
		if ( 'warning_urgent' === $decision_status ) {
			$msg_text .= "👉 <i>Telesale Action: Liên hệ hướng dẫn thủ tục thẩm định điểm sàn và môn bổ sung!</i>\n";
		} elseif ( in_array( $decision_status, [ 'not_eligible', 'not_eligible_2027' ], true ) ) {
			$msg_text .= "👉 <i>Telesale Action: Hướng dẫn lộ trình hoàn thiện văn hóa THPT hoặc ngành học phù hợp!</i>\n";
		}
	} else {
		// Notification for Free & Program Consultation forms
		$school_title = ! empty( $data['school_id'] ) ? get_the_title( $data['school_id'] ) : '';
		$major_title  = ! empty( $data['major_id'] ) ? get_the_title( $data['major_id'] ) : '';
		$training_val = ! empty( $data['training_type'] ) && $data['training_type'] !== 'N/A' ? $data['training_type'] : '';
		$campus_val   = ! empty( $data['campus'] ) && $data['campus'] !== 'N/A' ? $data['campus'] : '';

		if ( $is_group ) {
			$msg_text  = "🚨 <b>CÓ LEAD MỚI - ĐĂNG KÝ THEO NHÓM!</b> 👥\n\n";
		} else {
			$msg_text  = "🔔 <b>YÊU CẦU TƯ VẤN TUYỂN SINH MỚI</b> 🔔\n\n";
		}

		$msg_text .= "👤 <b>Họ và tên:</b> " . esc_html( $name ) . "\n";
		$msg_text .= "📞 <b>Số điện thoại:</b> " . esc_html( $phone ) . "\n";
		if ( $is_group ) {
			$msg_text .= "🎁 <b>Hình thức:</b> 👥 <b>ĐĂNG KÝ THEO NHÓM (Học bổng đồng hành)</b>\n";
		} else {
			$msg_text .= "🎁 <b>Hình thức:</b> 👤 Đăng ký cá nhân\n";
		}
		if ( ! empty( $data['sales_partner'] ) ) {
			$msg_text .= "🤝 <b>Người giới thiệu (Ref):</b> <code>" . esc_html( $data['sales_partner'] ) . "</code>\n";
		}
		if ( ! empty( $email ) && $email !== 'N/A' ) {
			$msg_text .= "✉ <b>Email:</b> " . esc_html( $email ) . "\n";
		}
		if ( ! empty( $school_title ) && $school_title !== 'N/A' ) {
			$msg_text .= "🏫 <b>Trường đăng ký:</b> " . esc_html( $school_title ) . "\n";
		}
		if ( ! empty( $major_title ) && $major_title !== 'N/A' ) {
			$msg_text .= "🎓 <b>Ngành quan tâm:</b> " . esc_html( $major_title ) . "\n";
		}
		if ( ! empty( $training_val ) ) {
			$msg_text .= "🏷 <b>Hệ học:</b> " . esc_html( $training_val ) . "\n";
		}
		if ( ! empty( $campus_val ) ) {
			$msg_text .= "📍 <b>Cơ sở:</b> " . esc_html( $campus_val ) . "\n";
		}
		if ( ! empty( $message ) ) {
			$msg_text .= "💬 <b>Nội dung yêu cầu:</b> " . esc_html( $message ) . "\n";
		}
		if ( ! empty( $degree_link ) ) {
			$msg_text .= "📎 <b>Ảnh bằng cấp:</b> " . esc_url( $degree_link ) . "\n";
		}
		if ( ! empty( $transcript_link ) ) {
			$msg_text .= "📊 <b>Ảnh/link bảng điểm:</b> " . esc_url( $transcript_link ) . "\n";
		}
	}
	
	if ( $is_group ) {
		$msg_text .= "👉 <i>Gợi ý Telesale: Khách có nhu cầu học cùng bạn bè, mở lời theo gói học bổng nhóm để chốt đôi!</i>\n";
	}
	$msg_text .= "📅 <b>Thời gian:</b> " . wp_date( 'd/m/Y H:i:s', null, new DateTimeZone( 'Asia/Ho_Chi_Minh' ) ) . "\n";

	// Split chat IDs by comma, semicolon, or space to support multiple recipients
	$chat_ids = preg_split( '/[\s,;]+/', $chat_id );
	$chat_ids = array_filter( array_map( 'trim', $chat_ids ) );

	if ( empty( $chat_ids ) ) {
		return;
	}

	$clean_token = trim( $bot_token );
	$api_url     = "https://api.telegram.org/bot{$clean_token}/sendMessage";

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

	// [CBR-10]: Ephemeral Degree File forwarding & cleanup (Decree 13/2023/ND-CP)
	if ( ! empty( $data['degree_file_path'] ) && file_exists( $data['degree_file_path'] ) ) {
		$caption = '📎 Hồ sơ văn bằng đính kèm: ' . esc_html( $name ) . ' (' . esc_html( $phone ) . ')';
		foreach ( $chat_ids as $m_chat_id ) {
			ltdh_telegram_send_document( $clean_token, $m_chat_id, $data['degree_file_path'], $caption );
		}
		if ( ! empty( $school_chat_id ) && ! empty( $dispatch_token ) && ! empty( $school_chat_ids ) ) {
			foreach ( $school_chat_ids as $s_chat_id ) {
				if ( in_array( $s_chat_id, $chat_ids, true ) && $dispatch_token === $clean_token ) {
					continue;
				}
				ltdh_telegram_send_document( $dispatch_token, $s_chat_id, $data['degree_file_path'], $caption );
			}
		}
		// Unlink file from local disk immediately after transmission
		@unlink( $data['degree_file_path'] );
	}

	// [CBR-10]: Ephemeral Transcript File forwarding & cleanup (Decree 13/2023/ND-CP)
	if ( ! empty( $data['transcript_file_path'] ) && file_exists( $data['transcript_file_path'] ) ) {
		$caption = '📊 Bảng điểm đính kèm: ' . esc_html( $name ) . ' (' . esc_html( $phone ) . ')';
		foreach ( $chat_ids as $m_chat_id ) {
			ltdh_telegram_send_document( $clean_token, $m_chat_id, $data['transcript_file_path'], $caption );
		}
		if ( ! empty( $school_chat_id ) && ! empty( $dispatch_token ) && ! empty( $school_chat_ids ) ) {
			foreach ( $school_chat_ids as $s_chat_id ) {
				if ( in_array( $s_chat_id, $chat_ids, true ) && $dispatch_token === $clean_token ) {
					continue;
				}
				ltdh_telegram_send_document( $dispatch_token, $s_chat_id, $data['transcript_file_path'], $caption );
			}
		}
		// Unlink file from local disk immediately after transmission
		@unlink( $data['transcript_file_path'] );
	}
	} finally {
		ltdh_cleanup_ephemeral_files( $ephemeral_files );
	}
}

/**
 * Send document attachment to Telegram via multipart/form-data.
 */
function ltdh_telegram_send_document( string $bot_token, string $chat_id, string $file_path, string $caption = '' ): bool {
	if ( empty( $bot_token ) || empty( $chat_id ) || ! file_exists( $file_path ) ) {
		return false;
	}

	$url      = "https://api.telegram.org/bot" . trim( $bot_token ) . "/sendDocument";
	$boundary = wp_generate_password( 24, false );
	$headers  = [
		'content-type' => 'multipart/form-data; boundary=' . $boundary,
	];
	$filename = basename( $file_path );
	$file_data = file_get_contents( $file_path );
	if ( false === $file_data ) {
		return false;
	}

	$payload  = '';

	// chat_id field
	$payload .= '--' . $boundary . "\r\n";
	$payload .= 'Content-Disposition: form-data; name="chat_id"' . "\r\n\r\n";
	$payload .= $chat_id . "\r\n";

	// caption field
	if ( ! empty( $caption ) ) {
		$payload .= '--' . $boundary . "\r\n";
		$payload .= 'Content-Disposition: form-data; name="caption"' . "\r\n\r\n";
		$payload .= $caption . "\r\n";
	}

	// document field
	$mime_type = function_exists( 'mime_content_type' ) ? mime_content_type( $file_path ) : 'application/octet-stream';
	$payload .= '--' . $boundary . "\r\n";
	$payload .= 'Content-Disposition: form-data; name="document"; filename="' . $filename . '"' . "\r\n";
	$payload .= 'Content-Type: ' . $mime_type . "\r\n\r\n";
	$payload .= $file_data . "\r\n";
	$payload .= '--' . $boundary . '--' . "\r\n";

	$response = wp_remote_post( $url, [
		'headers' => $headers,
		'body'    => $payload,
		'timeout' => 20,
	] );

	return ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 );
}

// ----------------------------------------------------
// 4. CF7 Lead Interceptor
// ----------------------------------------------------
add_action( 'wpcf7_before_send_mail', 'ltdh_capture_cf7_lead', 10, 3 );


function ltdh_capture_cf7_lead( $contact_form, &$abort, $submission ) {
	$posted_data = $submission->get_posted_data();

	// Bỏ qua nếu đây là form bình luận bài viết (xử lý riêng qua ltdh_capture_cf7_comment)
	if ( ! empty( $posted_data['is_comment_form'] ) || ! empty( $posted_data['cf7_is_comment_form'] ) ) {
		return;
	}

	// 1. Invisible Honeypot check
	$hp_val = $posted_data['hp_website'] ?? $posted_data['website_url_hp'] ?? '';
	if ( ! empty( $hp_val ) ) {
		$abort = true;
		return;
	}

	$name_val  = is_array( $posted_data['your-name'] ?? '' ) ? reset( $posted_data['your-name'] ) : ( $posted_data['your-name'] ?? '' );
	if ( empty( $name_val ) ) {
		$name_val = is_array( $posted_data['cf_name'] ?? '' ) ? reset( $posted_data['cf_name'] ) : ( $posted_data['cf_name'] ?? '' );
	}
	$phone_val = is_array( $posted_data['your-phone'] ?? '' ) ? reset( $posted_data['your-phone'] ) : ( $posted_data['your-phone'] ?? '' );
	if ( empty( $phone_val ) ) {
		$phone_val = is_array( $posted_data['cf_phone'] ?? '' ) ? reset( $posted_data['cf_phone'] ) : ( $posted_data['cf_phone'] ?? '' );
	}
	$email_val = is_array( $posted_data['your-email'] ?? '' ) ? reset( $posted_data['your-email'] ) : ( $posted_data['your-email'] ?? '' );
	if ( empty( $email_val ) ) {
		$email_val = is_array( $posted_data['cf_email'] ?? '' ) ? reset( $posted_data['cf_email'] ) : ( $posted_data['cf_email'] ?? '' );
	}
	$subj_val  = is_array( $posted_data['your-subject'] ?? '' ) ? reset( $posted_data['your-subject'] ) : ( $posted_data['your-subject'] ?? '' );
	$msg_val   = is_array( $posted_data['your-message'] ?? '' ) ? reset( $posted_data['your-message'] ) : ( $posted_data['your-message'] ?? '' );
	if ( empty( $msg_val ) ) {
		$msg_val = is_array( $posted_data['cf_message'] ?? '' ) ? reset( $posted_data['cf_message'] ) : ( $posted_data['cf_message'] ?? '' );
	}

	$name    = sanitize_text_field( $name_val );
	$phone   = sanitize_text_field( $phone_val );
	$email   = sanitize_email( $email_val );
	$subject = sanitize_text_field( $subj_val );
	$message = sanitize_textarea_field( $msg_val );

	if ( ! empty( $subject ) && '-- Chọn chủ đề liên hệ --' !== $subject ) {
		$message = "[Chủ đề: " . $subject . "]\n" . $message;
	}

	// Capture previous school & graduation year if present (e.g. from evaluation form)
	$previous_school = isset( $posted_data['previous_school'] ) ? sanitize_text_field( is_array( $posted_data['previous_school'] ) ? reset( $posted_data['previous_school'] ) : $posted_data['previous_school'] ) : '';
	$graduation      = isset( $posted_data['graduation'] ) ? sanitize_text_field( is_array( $posted_data['graduation'] ) ? reset( $posted_data['graduation'] ) : $posted_data['graduation'] ) : '';
	if ( '-- Chọn năm --' === $graduation ) {
		$graduation = '';
	}

	$eval_notes = [];
	if ( ! empty( $previous_school ) ) {
		$eval_notes[] = "Trường đã tốt nghiệp: " . $previous_school;
	}
	if ( ! empty( $graduation ) ) {
		$eval_notes[] = "Năm tốt nghiệp: " . $graduation;
	}
	if ( ! empty( $eval_notes ) ) {
		$message = "[" . implode( " | ", $eval_notes ) . "]" . ( $message ? "\n" . $message : "" );
	}

	// Perform spam check
	if ( ltdh_is_spam_submission( [ 'name' => $name, 'phone' => $phone, 'email' => $email, 'message' => $message, 'hp_website' => $hp_val ] ) ) {
		$abort = true;
		return;
	}

	// Verify Google reCAPTCHA (Fail-Closed when enabled)
	if ( function_exists( 'ltdh_is_recaptcha_enabled' ) && ltdh_is_recaptcha_enabled() ) {
		$recaptcha_token = isset( $posted_data['g-recaptcha-response'] ) ? sanitize_text_field( $posted_data['g-recaptcha-response'] ) : '';
		if ( ! ltdh_verify_recaptcha( $recaptcha_token, 'cf7' ) ) {
			$abort = true;
			return;
		}
	}

	if ( empty( $name ) || empty( $phone ) ) {
		return;
	}

	// Resolve program ID and major ID (accepts numeric ID or title string, supports array from CF7 select)
	$program_id = 0;
	$raw_prog   = '';
	if ( ! empty( $posted_data['current_program_id'] ) ) {
		$raw_prog = is_array( $posted_data['current_program_id'] ) ? reset( $posted_data['current_program_id'] ) : $posted_data['current_program_id'];
	}

	$ignored_placeholders = [
		'-- Chọn ngành hoặc hệ học --',
		'-- Chọn ngành học --',
		'-- Chọn ngành học quan tâm --',
		'-- Chọn ngành quan tâm --',
	];

	if ( ( empty( $raw_prog ) || in_array( $raw_prog, $ignored_placeholders, true ) ) && ! empty( $posted_data['program_id'] ) ) {
		$raw_prog = is_array( $posted_data['program_id'] ) ? reset( $posted_data['program_id'] ) : $posted_data['program_id'];
	}
	if ( ( empty( $raw_prog ) || in_array( $raw_prog, $ignored_placeholders, true ) ) && ! empty( $posted_data['elig_program_id'] ) ) {
		$raw_prog = is_array( $posted_data['elig_program_id'] ) ? reset( $posted_data['elig_program_id'] ) : $posted_data['elig_program_id'];
	}

	$school_raw    = is_array( $posted_data['current_school_id'] ?? ( $posted_data['school_id'] ?? 0 ) ) ? reset( $posted_data['current_school_id'] ?? ( $posted_data['school_id'] ?? 0 ) ) : ( $posted_data['current_school_id'] ?? ( $posted_data['school_id'] ?? 0 ) );
	$major_raw     = is_array( $posted_data['current_major_id'] ?? ( $posted_data['major_id'] ?? 0 ) ) ? reset( $posted_data['current_major_id'] ?? ( $posted_data['major_id'] ?? 0 ) ) : ( $posted_data['current_major_id'] ?? ( $posted_data['major_id'] ?? 0 ) );
	$school_id     = absint( $school_raw );
	$major_id      = absint( $major_raw );

	if ( ! empty( $raw_prog ) && ! in_array( $raw_prog, $ignored_placeholders, true ) ) {
		if ( is_numeric( $raw_prog ) && (int) $raw_prog > 0 ) {
			$raw_id = intval( $raw_prog );
			$ptype  = get_post_type( $raw_id );
			if ( 'major' === $ptype ) {
				if ( ! $major_id ) {
					$major_id = $raw_id;
				}
			} elseif ( 'program' === $ptype ) {
				$program_id = $raw_id;
				if ( ! $major_id ) {
					$major_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $program_id ) : (int) get_post_meta( $program_id, 'major_relationship', true );
				}
			} else {
				if ( ! $major_id ) {
					$major_id = $raw_id;
				}
			}
		} elseif ( is_string( $raw_prog ) ) {
			$cpt_major  = defined( 'LTDH_CPT_MAJOR' ) ? LTDH_CPT_MAJOR : 'major';
			$found_maj  = get_page_by_title( $raw_prog, OBJECT, $cpt_major );
			if ( $found_maj ) {
				if ( ! $major_id ) {
					$major_id = $found_maj->ID;
				}
			} else {
				$cpt_prog   = defined( 'LTDH_CPT_PROGRAM' ) ? LTDH_CPT_PROGRAM : 'program';
				$found_prog = get_page_by_title( $raw_prog, OBJECT, $cpt_prog );
				if ( $found_prog ) {
					$program_id = $found_prog->ID;
					if ( ! $major_id ) {
						$major_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $program_id ) : (int) get_post_meta( $program_id, 'major_relationship', true );
					}
				} else {
					$message = "[Nguyện vọng/Ngành: " . sanitize_text_field( $raw_prog ) . "]\n" . $message;
				}
			}
		}
	}
	$training_type = isset( $posted_data['training_type'] ) ? sanitize_text_field( is_array( $posted_data['training_type'] ) ? reset( $posted_data['training_type'] ) : $posted_data['training_type'] ) : '';
	$campus        = isset( $posted_data['campus'] ) ? sanitize_text_field( is_array( $posted_data['campus'] ) ? reset( $posted_data['campus'] ) : $posted_data['campus'] ) : '';

	$referral_source = isset( $posted_data['referral_source'] ) ? esc_url_raw( $posted_data['referral_source'] ) : '';

	// Resolve check_id or share_token and enrich referral source if coming from eligibility check
	$raw_check_ref = isset( $posted_data['elig_check_id'] ) ? sanitize_text_field( is_array( $posted_data['elig_check_id'] ) ? reset( $posted_data['elig_check_id'] ) : $posted_data['elig_check_id'] ) : '';
	$check_row     = null;
	if ( '' !== $raw_check_ref ) {
		global $wpdb;
		if ( ctype_digit( $raw_check_ref ) && (int) $raw_check_ref > 0 ) {
			$check_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ltdh_eligibility_checks WHERE id = %d", (int) $raw_check_ref ) );
		} elseif ( preg_match( '/^[a-zA-Z0-9]{24,64}$/', $raw_check_ref ) ) {
			$check_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ltdh_eligibility_checks WHERE session_token = %s", $raw_check_ref ) );
		}
	}
	if ( $check_row ) {
			if ( empty( $campus ) && ! empty( $check_row->input_campus ) ) {
				$campus = $check_row->input_campus;
			}
			if ( empty( $training_type ) && ! empty( $check_row->input_training_type ) ) {
				$training_type = $check_row->input_training_type;
			}
			$is_verification_required = ( $program_id && function_exists( 'get_field' ) && get_field( 'elig_min_education', $program_id ) ) ? '1' : '0';
			$referral_source = 'eligibility_checker?education_level=' . urlencode( (string) $check_row->input_education ) . 
				'&current_major=' . urlencode( (string) $check_row->input_major_id ) . 
				'&previous_school=' . urlencode( (string) ( $previous_school ?: $check_row->input_previous_school ) ) .
				'&desired_major=' . urlencode( (string) $check_row->input_desired_major ) . 
				'&birth_year=' . urlencode( (string) ( $graduation ?: $check_row->input_graduation ) ) . 
				'&verification_required=' . $is_verification_required;
	}

	if ( empty( $referral_source ) ) {
		$form_ctx = isset( $posted_data['form_context'] ) ? sanitize_text_field( is_array( $posted_data['form_context'] ) ? reset( $posted_data['form_context'] ) : $posted_data['form_context'] ) : '';
		if ( 'eligibility_evaluation' === $form_ctx ) {
			$referral_source = 'eligibility_evaluation_form';
		} else {
			$referral_source = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '';
		}
	}

	$is_group_reg = ! empty( $posted_data['is_group_reg'] ) || ! empty( $posted_data['group_reg'] ) || ! empty( $posted_data['dang-ky-nhom'] );

	$lead_id = ltdh_insert_lead( [
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
		'is_group_reg'    => $is_group_reg,
	] );

	// Suppress duplicate plain-text emails from Contact Form 7 ONLY when DB insert succeeds.
	// The theme's HTML notification system (ltdh_trigger_email_notifications)
	// handles sending branded HTML confirmation to the student and lead alert to the admin.
	if ( ! empty( $lead_id ) && $lead_id > 0 ) {
		add_filter( 'wpcf7_skip_mail', '__return_true' );
	} else {
		error_log( '[LTDH Lead Loss Alert] Failed to insert CF7 lead into DB for phone: ' . $phone );
	}
}

// ----------------------------------------------------
// 4b. CF7 Post Comment Interceptor
// ----------------------------------------------------
add_action( 'wpcf7_before_send_mail', 'ltdh_capture_cf7_comment', 10, 3 );

/**
 * Handle Contact Form 7 submissions designated as post comments.
 * Automatically inserts submitted comment into WordPress core comments (pending moderation).
 *
 * @param WPCF7_ContactForm $contact_form
 * @param bool              $abort
 * @param WPCF7_Submission  $submission
 */
function ltdh_capture_cf7_comment( $contact_form, &$abort, $submission ) {
	$posted_data = $submission->get_posted_data();

	// Chỉ xử lý form có cờ is_comment_form
	if ( empty( $posted_data['is_comment_form'] ) && empty( $posted_data['cf7_is_comment_form'] ) ) {
		return;
	}

	// 1. Kiểm tra Honeypot, Rate Limit & reCAPTCHA
	$hp_val = $posted_data['hp_website'] ?? $posted_data['website_url_hp'] ?? '';
	if ( ! empty( $hp_val ) || ltdh_is_ip_rate_limited() ) {
		$abort = true;
		return;
	}
	if ( function_exists( 'ltdh_is_recaptcha_enabled' ) && ltdh_is_recaptcha_enabled() ) {
		$recaptcha_token = isset( $posted_data['g-recaptcha-response'] ) ? sanitize_text_field( $posted_data['g-recaptcha-response'] ) : '';
		if ( ! ltdh_verify_recaptcha( $recaptcha_token, 'cf7' ) ) {
			$abort = true;
			return;
		}
	}

	// 2. Trích xuất thông tin người gửi
	$name_val = is_array( $posted_data['your-name'] ?? '' ) ? reset( $posted_data['your-name'] ) : ( $posted_data['your-name'] ?? '' );
	if ( empty( $name_val ) ) {
		$name_val = is_array( $posted_data['cf_name'] ?? '' ) ? reset( $posted_data['cf_name'] ) : ( $posted_data['cf_name'] ?? '' );
	}
	$email_val = is_array( $posted_data['your-email'] ?? '' ) ? reset( $posted_data['your-email'] ) : ( $posted_data['your-email'] ?? '' );
	if ( empty( $email_val ) ) {
		$email_val = is_array( $posted_data['cf_email'] ?? '' ) ? reset( $posted_data['cf_email'] ) : ( $posted_data['cf_email'] ?? '' );
	}
	$comment_val = is_array( $posted_data['your-comment'] ?? '' ) ? reset( $posted_data['your-comment'] ) : ( $posted_data['your-comment'] ?? '' );
	if ( empty( $comment_val ) ) {
		$comment_val = is_array( $posted_data['your-message'] ?? '' ) ? reset( $posted_data['your-message'] ) : ( $posted_data['your-message'] ?? '' );
	}

	$author_name    = sanitize_text_field( $name_val );
	$author_email   = sanitize_email( $email_val );
	$comment_text   = sanitize_textarea_field( $comment_val );

	if ( empty( $author_name ) || empty( $comment_text ) ) {
		$abort = true;
		return;
	}

	// 3. Chống spam bình luận (chặn Cyrillic và link spam nếu muốn bảo mật cao)
	if ( preg_match( '/[\p{Cyrillic}]/u', $author_name ) || preg_match( '/[\p{Cyrillic}]/u', $comment_text ) ) {
		$abort = true;
		return;
	}

	// 4. Xác định Post ID của bài viết được bình luận
	$post_id = 0;
	if ( ! empty( $posted_data['comment_post_id'] ) ) {
		$post_id = absint( is_array( $posted_data['comment_post_id'] ) ? reset( $posted_data['comment_post_id'] ) : $posted_data['comment_post_id'] );
	}
	if ( ! $post_id ) {
		$ref_url = wp_get_referer();
		if ( $ref_url ) {
			$post_id = url_to_postid( $ref_url );
		}
	}
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$parent_id = 0;
	if ( ! empty( $posted_data['comment_parent'] ) ) {
		$parent_id = absint( is_array( $posted_data['comment_parent'] ) ? reset( $posted_data['comment_parent'] ) : $posted_data['comment_parent'] );
	}

	$user_ip    = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );

	// 5. Thêm bình luận vào hệ thống WordPress core (Bảng wp_comments)
	$commentdata = [
		'comment_post_ID'      => $post_id,
		'comment_author'       => $author_name,
		'comment_author_email' => $author_email,
		'comment_content'      => $comment_text,
		'comment_parent'       => $parent_id,
		'comment_author_IP'    => $user_ip,
		'comment_agent'        => $user_agent,
		'comment_type'         => 'comment',
		'comment_date'         => current_time( 'mysql' ),
		'comment_approved'     => 0, // Trạng thái: Chờ duyệt (Pending) trong WP Admin > Bình luận
	];

	$inserted_comment_id = wp_insert_comment( $commentdata );

	if ( ! is_wp_error( $inserted_comment_id ) && $inserted_comment_id > 0 ) {
		ltdh_increment_ip_rate_limit();
		// Bỏ qua gửi email của CF7 vì đây là bình luận bài viết
		add_filter( 'wpcf7_skip_mail', '__return_true' );
	} else {
		error_log( '[LTDH CF7 Comment Alert] Failed to insert comment into WP Comments for post: ' . $post_id );
	}
}

// Tùy chỉnh thông điệp phản hồi cho form comment trong CF7
add_filter( 'wpcf7_feedback_response', function( $response, $result ) {
	if ( isset( $_POST['is_comment_form'] ) || isset( $_POST['cf7_is_comment_form'] ) ) {
		if ( 'mail_sent' === ( $result['status'] ?? '' ) ) {
			$response['message'] = esc_html__( 'Cảm ơn bạn! Bình luận của bạn đã được gửi thành công và đang chờ ban quản trị phê duyệt trước khi hiển thị.', 'ltdh' );
		}
	}
	return $response;
}, 10, 2 );

// ----------------------------------------------------
// 5. Native Form Submission Handler
// ----------------------------------------------------
add_action( 'template_redirect', 'ltdh_handle_native_form_submit' );

function ltdh_handle_native_form_submit() {
	if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return;
	}

	// Bỏ qua nếu đây là Contact Form 7 (CF7 xử lý độc lập qua hook riêng của CF7)
	if ( isset( $_POST['_wpcf7'] ) || isset( $_POST['_wpcf7_version'] ) || isset( $_POST['_wpcf7_unit_tag'] ) ) {
		return;
	}

	// Chỉ xử lý native form khi có token nonce của native form
	if ( ! isset( $_POST['ltdh_native_lead_nonce'] ) ) {
		return;
	}

	if ( ! isset( $_POST['your-name'] ) || ! isset( $_POST['your-phone'] ) ) {
		return;
	}

	// 1. Kiểm tra xác thực WordPress CSRF Nonce Token bắt buộc
	$nonce = sanitize_text_field( wp_unslash( $_POST['ltdh_native_lead_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'ltdh_native_lead_submit_action' ) ) {
		wp_die(
			esc_html__( 'Phiên làm việc bảo mật đã hết hạn hoặc yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.', 'lienthongdaihoc' ),
			esc_html__( 'Lỗi Bảo Mật CSRF', 'lienthongdaihoc' ),
			[ 'response' => 403 ]
		);
	}

	// 2. Kiểm tra xác thực Google reCAPTCHA nếu kích hoạt
	if ( function_exists( 'ltdh_is_recaptcha_enabled' ) && ltdh_is_recaptcha_enabled() ) {
		$recaptcha_token = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
		if ( ! ltdh_verify_recaptcha( $recaptcha_token, 'native_form' ) ) {
			wp_die(
				esc_html__( 'Xác thực bảo mật reCAPTCHA không thành công hoặc nghi ngờ tự động. Vui lòng tải lại trang và thử lại.', 'lienthongdaihoc' ),
				esc_html__( 'Lỗi Bảo Mật reCAPTCHA', 'lienthongdaihoc' ),
				[ 'response' => 403 ]
			);
		}
	}

	$name       = sanitize_text_field( wp_unslash( $_POST['your-name'] ) );
	$phone      = sanitize_text_field( wp_unslash( $_POST['your-phone'] ) );
	$email      = isset( $_POST['your-email'] ) ? sanitize_email( wp_unslash( $_POST['your-email'] ) ) : '';
	$message    = isset( $_POST['your-message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['your-message'] ) ) : '';
	$hp_website = isset( $_POST['hp_website'] ) ? sanitize_text_field( wp_unslash( $_POST['hp_website'] ) ) : '';

	// 3. Kiểm tra spam & honeypot
	if ( function_exists( 'ltdh_is_spam_submission' ) && ltdh_is_spam_submission( [ 'name' => $name, 'phone' => $phone, 'email' => $email, 'message' => $message, 'hp_website' => $hp_website ] ) ) {
		wp_die(
			esc_html__( 'Yêu cầu của bạn bị chặn do nghi ngờ spam. Vui lòng liên hệ hotline.', 'lienthongdaihoc' ),
			esc_html__( 'Spam Blocked', 'lienthongdaihoc' ),
			[ 'response' => 403 ]
		);
	}

	if ( empty( $name ) || empty( $phone ) ) {
		return;
	}

	// 4. Trích xuất tham số ID an toàn hỗ trợ tương thích ngược
	$program_id    = isset( $_POST['program_id'] ) ? absint( $_POST['program_id'] ) : ( isset( $_POST['current_program_id'] ) ? absint( $_POST['current_program_id'] ) : 0 );
	$school_id     = isset( $_POST['school_id'] ) ? absint( $_POST['school_id'] ) : ( isset( $_POST['current_school_id'] ) ? absint( $_POST['current_school_id'] ) : 0 );
	$major_id      = isset( $_POST['major_id'] ) ? absint( $_POST['major_id'] ) : ( isset( $_POST['current_major_id'] ) ? absint( $_POST['current_major_id'] ) : 0 );
	$training_type = isset( $_POST['training_type'] ) ? sanitize_text_field( wp_unslash( $_POST['training_type'] ) ) : '';
	$campus        = isset( $_POST['campus'] ) ? sanitize_text_field( wp_unslash( $_POST['campus'] ) ) : '';

	$referral_source = isset( $_POST['referral_source'] ) ? esc_url_raw( wp_unslash( $_POST['referral_source'] ) ) : '';
	if ( empty( $referral_source ) ) {
		$referral_source = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	}

	$is_group_reg = ! empty( $_POST['is_group_reg'] );

	$inserted_id = ltdh_insert_lead( [
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
		'is_group_reg'    => $is_group_reg,
	] );

	if ( $inserted_id ) {
		$redirect_url = add_query_arg( 'submit_success', '1', wp_get_referer() ?: home_url( '/' ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}
}

// ----------------------------------------------------
// 6. Lead Magnet Download AJAX Handler
// ----------------------------------------------------
add_action( 'wp_ajax_ltdh_lead_magnet_download', 'ltdh_handle_lead_magnet_download' );
add_action( 'wp_ajax_nopriv_ltdh_lead_magnet_download', 'ltdh_handle_lead_magnet_download' );

function ltdh_handle_lead_magnet_download() {
	check_ajax_referer( 'ltdh_lead_magnet_nonce', 'security' );

	$name       = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$phone      = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$doc_title  = sanitize_text_field( wp_unslash( $_POST['doc_title'] ?? 'Tài liệu tuyển sinh' ) );
	$doc_url    = esc_url_raw( wp_unslash( $_POST['doc_url'] ?? '' ) );
	$program_id = intval( $_POST['program_id'] ?? 0 );
	$school_id  = intval( $_POST['school_id'] ?? 0 );
	$major_id   = intval( $_POST['major_id'] ?? 0 );
	$hp_val     = sanitize_text_field( wp_unslash( $_POST['hp_website'] ?? '' ) );

	if ( ! empty( $hp_val ) || ltdh_is_spam_submission( [ 'name' => $name, 'phone' => $phone, 'hp_website' => $hp_val ] ) ) {
		wp_send_json_error( [ 'message' => 'Yêu cầu không hợp lệ hoặc bị nghi ngờ là spam.' ] );
	}

	// Verify Google reCAPTCHA
	if ( function_exists( 'ltdh_is_recaptcha_enabled' ) && ltdh_is_recaptcha_enabled() ) {
		$recaptcha_token = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
		if ( ! ltdh_verify_recaptcha( $recaptcha_token, 'lead_magnet' ) ) {
			wp_send_json_error( [ 'message' => 'Xác thực bảo mật reCAPTCHA thất bại. Vui lòng tải lại trang và thử lại.' ] );
		}
	}

	if ( empty( $name ) || empty( $phone ) ) {
		wp_send_json_error( [ 'message' => 'Vui lòng cung cấp đầy đủ họ tên và số điện thoại.' ] );
	}

	// Validate download URL against trusted hosts (prevents arbitrary Open Redirect / Phishing URL injection)
	$safe_doc_url = '';
	if ( ! empty( $doc_url ) ) {
		$scheme   = strtolower( (string) parse_url( $doc_url, PHP_URL_SCHEME ) );
		$doc_host = strtolower( (string) parse_url( $doc_url, PHP_URL_HOST ) );
		$own_host = strtolower( (string) parse_url( home_url(), PHP_URL_HOST ) );
		$trusted  = array_filter( [ $own_host, 'drive.google.com', 'docs.google.com', 'ik.imagekit.io' ] );
		if ( in_array( $scheme, [ 'http', 'https' ], true ) && '' !== $doc_host ) {
			if ( in_array( $doc_host, $trusted, true ) || str_ends_with( $doc_host, '.r2.dev' ) ) {
				$safe_doc_url = $doc_url;
			}
		}
	}
	if ( '' === $safe_doc_url && $program_id > 0 ) {
		$meta_form_url = esc_url_raw( (string) get_post_meta( $program_id, 'admission_form_file', true ) );
		if ( ! empty( $meta_form_url ) ) {
			$safe_doc_url = $meta_form_url;
		}
	}

	$referral_source = 'lead_magnet: ' . $doc_title;
	if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
		$referral_source .= ' (' . esc_url_raw( $_SERVER['HTTP_REFERER'] ) . ')';
	}

	$lead_id = ltdh_insert_lead( [
		'name'            => $name,
		'phone'           => $phone,
		'email'           => '',
		'program_id'      => $program_id,
		'school_id'       => $school_id,
		'major_id'        => $major_id,
		'referral_source' => $referral_source,
		'message'         => 'Ứng viên yêu cầu tải tài liệu: ' . $doc_title,
	] );

	if ( $lead_id ) {
		wp_send_json_success( [
			'message'      => 'Cảm ơn bạn! Đang mở tài liệu tuyển sinh.',
			'file_url'     => $safe_doc_url,
			'download_url' => $safe_doc_url,
		] );
	}

	wp_send_json_error( [ 'message' => 'Không thể ghi nhận thông tin. Vui lòng liên hệ hotline hỗ trợ.' ] );
}
