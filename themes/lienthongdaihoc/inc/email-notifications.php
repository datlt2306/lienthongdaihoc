<?php
/**
 * Automated Email Notifications for Candidate and Admissions Admin
 *
 * Dispatches confirmation emails to students upon form submission and
 * real-time notification alerts to admissions admins, while preserving
 * Telegram bot alerts and ERPNext CRM synchronization.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Configuration & Helper Functions
// ----------------------------------------------------

/**
 * Check if automated email notifications are globally enabled.
 */
function ltdh_is_email_notifications_enabled(): bool {
	if ( defined( 'LTDH_EMAIL_NOTIFICATIONS_ENABLED' ) && ! LTDH_EMAIL_NOTIFICATIONS_ENABLED ) {
		return false;
	}

	if ( function_exists( 'get_field' ) ) {
		$enabled = get_field( 'email_notifications_enabled', 'options' );
		if ( null !== $enabled && false === (bool) $enabled ) {
			return false;
		}
	}

	return true;
}

/**
 * Check if student confirmation email is enabled.
 */
function ltdh_is_student_email_enabled(): bool {
	if ( ! ltdh_is_email_notifications_enabled() ) {
		return false;
	}

	if ( defined( 'LTDH_EMAIL_STUDENT_CONFIRMATION' ) && ! LTDH_EMAIL_STUDENT_CONFIRMATION ) {
		return false;
	}

	if ( function_exists( 'get_field' ) ) {
		$enabled = get_field( 'email_student_enabled', 'options' );
		if ( null !== $enabled && false === (bool) $enabled ) {
			return false;
		}
	}

	return true;
}

/**
 * Check if admin notification email is enabled.
 */
function ltdh_is_admin_email_enabled(): bool {
	if ( ! ltdh_is_email_notifications_enabled() ) {
		return false;
	}

	if ( defined( 'LTDH_EMAIL_ADMIN_NOTIFICATION' ) && ! LTDH_EMAIL_ADMIN_NOTIFICATION ) {
		return false;
	}

	if ( function_exists( 'get_field' ) ) {
		$enabled = get_field( 'email_admin_enabled', 'options' );
		if ( null !== $enabled && false === (bool) $enabled ) {
			return false;
		}
	}

	return true;
}

/**
 * Get the list of recipient email addresses for admin notifications.
 *
 * @return string[]
 */
function ltdh_get_admin_notification_emails(): array {
	$recipients_str = '';

	if ( defined( 'LTDH_EMAIL_ADMIN_RECIPIENT' ) && ! empty( LTDH_EMAIL_ADMIN_RECIPIENT ) ) {
		$recipients_str = LTDH_EMAIL_ADMIN_RECIPIENT;
	} elseif ( function_exists( 'get_field' ) ) {
		$recipients_str = (string) get_field( 'email_admin_recipients', 'options' );
	}

	$emails = [];
	if ( ! empty( $recipients_str ) ) {
		$parts = preg_split( '/[\s,;]+/', $recipients_str );
		if ( is_array( $parts ) ) {
			foreach ( $parts as $part ) {
				$clean = sanitize_email( trim( $part ) );
				if ( is_email( $clean ) ) {
					$emails[] = $clean;
				}
			}
		}
	}

	// Fallback to WordPress admin email if no custom recipients are configured
	if ( empty( $emails ) ) {
		$wp_admin = sanitize_email( get_option( 'admin_email' ) );
		if ( is_email( $wp_admin ) ) {
			$emails[] = $wp_admin;
		}
	}

	return array_values( array_unique( $emails ) );
}

/**
 * Get sender display name for outgoing emails.
 */
function ltdh_get_mailer_from_name(): string {
	if ( defined( 'LTDH_EMAIL_FROM_NAME' ) && ! empty( LTDH_EMAIL_FROM_NAME ) ) {
		return LTDH_EMAIL_FROM_NAME;
	}

	if ( function_exists( 'get_field' ) ) {
		$custom_name = get_field( 'email_from_name', 'options' );
		if ( ! empty( $custom_name ) ) {
			return sanitize_text_field( $custom_name );
		}
	}

	// Respect WP Mail SMTP settings if configured
	if ( function_exists( 'wp_mail_smtp' ) ) {
		$smtp_name = wp_mail_smtp()->get_options()->get( 'mail', 'from_name' );
		if ( ! empty( $smtp_name ) ) {
			return sanitize_text_field( $smtp_name );
		}
	}
	$smtp_opts = get_option( 'wp_mail_smtp' );
	if ( is_array( $smtp_opts ) && ! empty( $smtp_opts['mail']['from_name'] ) ) {
		return sanitize_text_field( $smtp_opts['mail']['from_name'] );
	}

	$site_title = get_bloginfo( 'name' );
	return ! empty( $site_title ) ? $site_title : 'Liên Thông Đại Học';
}

/**
 * Get sender email address for outgoing emails.
 */
function ltdh_get_mailer_from_address(): string {
	if ( defined( 'LTDH_EMAIL_FROM_ADDRESS' ) && is_email( LTDH_EMAIL_FROM_ADDRESS ) ) {
		return LTDH_EMAIL_FROM_ADDRESS;
	}

	if ( function_exists( 'get_field' ) ) {
		$custom_email = get_field( 'email_from_address', 'options' );
		if ( ! empty( $custom_email ) && is_email( $custom_email ) ) {
			return sanitize_email( $custom_email );
		}
	}

	// Respect WP Mail SMTP settings if configured
	if ( function_exists( 'wp_mail_smtp' ) ) {
		$smtp_email = wp_mail_smtp()->get_options()->get( 'mail', 'from_email' );
		if ( ! empty( $smtp_email ) && is_email( $smtp_email ) ) {
			return sanitize_email( $smtp_email );
		}
	}
	$smtp_opts = get_option( 'wp_mail_smtp' );
	if ( is_array( $smtp_opts ) && ! empty( $smtp_opts['mail']['from_email'] ) && is_email( $smtp_opts['mail']['from_email'] ) ) {
		return sanitize_email( $smtp_opts['mail']['from_email'] );
	}

	// Fallback to domain email (e.g. hotro@lienthongdaihoc.com)
	$host = parse_url( home_url(), PHP_URL_HOST );
	if ( ! empty( $host ) && false === strpos( $host, 'localhost' ) && false === strpos( $host, '.local' ) ) {
		return 'hotro@' . $host;
	}

	$admin_email = get_option( 'admin_email' );
	if ( is_email( $admin_email ) && false === strpos( $admin_email, 'localhost' ) && false === strpos( $admin_email, '.local' ) ) {
		return sanitize_email( $admin_email );
	}

	return 'hotro@lienthongdaihoc.com';
}

/**
 * Get reply-to email address.
 */
function ltdh_get_mailer_reply_to(): string {
	if ( function_exists( 'get_field' ) ) {
		$custom_reply = get_field( 'email_reply_to', 'options' );
		if ( ! empty( $custom_reply ) && is_email( $custom_reply ) ) {
			return sanitize_email( $custom_reply );
		}
	}

	return ltdh_get_mailer_from_address();
}

// ----------------------------------------------------
// 2. Custom SMTP Hook Integration
// ----------------------------------------------------

add_action( 'phpmailer_init', 'ltdh_configure_custom_smtp', 999 );

/**
 * Configure custom SMTP server if enabled in theme settings and not handled by external plugin.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer
 */
function ltdh_configure_custom_smtp( $phpmailer ) {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}

	$smtp_enabled = (bool) get_field( 'email_smtp_custom_enabled', 'options' );
	if ( ! $smtp_enabled ) {
		return;
	}

	$host = (string) get_field( 'email_smtp_host', 'options' );
	$port = (int) get_field( 'email_smtp_port', 'options' );
	$enc  = (string) get_field( 'email_smtp_encryption', 'options' );
	$user = (string) get_field( 'email_smtp_username', 'options' );
	$pass = (string) get_field( 'email_smtp_password', 'options' );

	if ( empty( $host ) ) {
		return;
	}

	$phpmailer->isSMTP();
	$phpmailer->Host = sanitize_text_field( $host );
	$phpmailer->Port = $port > 0 ? $port : 465;

	if ( in_array( $enc, [ 'ssl', 'tls' ], true ) ) {
		$phpmailer->SMTPSecure = $enc;
	} else {
		$phpmailer->SMTPSecure = '';
		$phpmailer->SMTPAutoTLS = false;
	}

	if ( ! empty( $user ) && ! empty( $pass ) ) {
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = sanitize_text_field( $user );
		$phpmailer->Password = $pass;
	} else {
		$phpmailer->SMTPAuth = false;
	}
}

// ----------------------------------------------------
// 3. Centralized Email Dispatcher Helper
// ----------------------------------------------------

/**
 * Send an HTML email with proper headers and error resilience.
 *
 * @param string|string[] $to
 * @param string          $subject
 * @param string          $message_html
 * @param string[]        $extra_headers
 * @return bool
 */
function ltdh_send_html_mail( $to, string $subject, string $message_html, array $extra_headers = [] ): bool {
	$from_name    = ltdh_get_mailer_from_name();
	$from_address = ltdh_get_mailer_from_address();
	$reply_to     = ltdh_get_mailer_reply_to();

	$headers = [
		'Content-Type: text/html; charset=UTF-8',
		"From: {$from_name} <{$from_address}>",
		"Reply-To: {$from_name} <{$reply_to}>",
	];

	if ( ! empty( $extra_headers ) ) {
		$headers = array_merge( $headers, $extra_headers );
	}

	// Filter from name & email temporarily to ensure strict compliance
	$name_filter = static function () use ( $from_name ) {
		return $from_name;
	};
	$email_filter = static function () use ( $from_address ) {
		return $from_address;
	};

	add_filter( 'wp_mail_from_name', $name_filter, 999 );
	add_filter( 'wp_mail_from', $email_filter, 999 );

	try {
		$sent = wp_mail( $to, $subject, $message_html, $headers );
	} catch ( \Throwable $e ) {
		error_log( '[LTDH wp_mail Exception] ' . $e->getMessage() );
		$sent = false;
	}

	remove_filter( 'wp_mail_from_name', $name_filter, 999 );
	remove_filter( 'wp_mail_from', $email_filter, 999 );

	return (bool) $sent;
}

// ----------------------------------------------------
// 4. HTML Email Templates Builder
// ----------------------------------------------------

/**
 * Build responsive HTML confirmation email for candidate.
 */
function ltdh_build_student_email_html( array $data, int $lead_id ): string {
	$name          = ! empty( $data['name'] ) ? esc_html( $data['name'] ) : 'Bạn';
	$phone         = ! empty( $data['phone'] ) ? esc_html( $data['phone'] ) : 'N/A';
	$email         = ! empty( $data['email'] ) ? esc_html( $data['email'] ) : 'N/A';
	$message       = ! empty( $data['message'] ) ? esc_html( $data['message'] ) : '';
	$is_group_reg  = ! empty( $data['is_group_reg'] );
	$training_type = ! empty( $data['training_type'] ) && 'N/A' !== $data['training_type'] ? esc_html( $data['training_type'] ) : 'Liên thông Đại học';
	$campus        = ! empty( $data['campus'] ) && 'N/A' !== $data['campus'] ? esc_html( $data['campus'] ) : 'Trực tuyến / Linh hoạt';

	$school_title = 'Chưa chọn';
	if ( ! empty( $data['school_id'] ) ) {
		$school_title = get_the_title( (int) $data['school_id'] );
	}
	$major_title = 'Chưa chọn';
	if ( ! empty( $data['major_id'] ) ) {
		$major_title = get_the_title( (int) $data['major_id'] );
	}

	$hotline = function_exists( 'ltdh_get_school_hotline' ) && ! empty( $data['school_id'] )
		? ltdh_get_school_hotline( (int) $data['school_id'] )
		: ( function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496' );

	$clean_hotline = preg_replace( '/\D/', '', $hotline );
	$site_name     = ltdh_get_mailer_from_name();
	$site_url      = home_url( '/' );
	$lead_code     = '#LTDH-' . str_pad( (string) $lead_id, 5, '0', STR_PAD_LEFT );
	$created_time  = current_time( 'd/m/Y - H:i' );

	ob_start();
	?>
	<!DOCTYPE html>
	<html lang="vi">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Xác nhận đăng ký tư vấn tuyển sinh</title>
	</head>
	<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1e293b;line-height:1.6;">
		<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f1f5f9;padding:24px 12px;">
			<tr>
				<td align="center">
					<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.06);border:1px solid #e2e8f0;">
						
						<!-- Header Banner -->
						<tr>
							<td style="background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 100%);padding:36px 32px 30px;text-align:center;color:#ffffff;">
								<div style="display:inline-block;background:rgba(255,255,255,0.15);padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;color:#93c5fd;margin-bottom:12px;">
									Cổng Tuyển Sinh Liên Thông & Từ Xa Đại Học
								</div>
								<h1 style="margin:0 0 8px;font-size:24px;font-weight:700;line-height:1.3;color:#ffffff;">
									XÁC NHẬN ĐĂNG KÝ TƯ VẤN
								</h1>
								<p style="margin:0;font-size:14px;color:#cbd5e1;">
									Mã hồ sơ tiếp nhận: <strong style="color:#fbbf24;"><?php echo esc_html( $lead_code ); ?></strong>
								</p>
							</td>
						</tr>

						<!-- Body Content -->
						<tr>
							<td style="padding:32px 32px 24px;">
								<p style="margin:0 0 16px;font-size:16px;line-height:1.6;">
									Chào bạn <strong><?php echo $name; ?></strong>,
								</p>
								<p style="margin:0 0 24px;font-size:15px;color:#475569;line-height:1.6;">
									Cảm ơn bạn đã tin tưởng và đăng ký nhận tư vấn tuyển sinh tại <strong><?php echo esc_html( $site_name ); ?></strong>. Hệ thống đã tiếp nhận thông tin của bạn vào lúc <strong><?php echo esc_html( $created_time ); ?></strong>.
								</p>

								<?php if ( $is_group_reg ) : ?>
								<!-- Group Registration Badge -->
								<div style="background-color:#fef3c7;border-left:4px solid:#f59e0b;padding:14px 16px;border-radius:8px;margin-bottom:24px;color:#92400e;">
									<strong style="font-size:14px;">👥 GÓI ĐĂNG KÝ THEO NHÓM - HỌC BỔNG ĐỒNG HÀNH</strong>
									<p style="margin:4px 0 0;font-size:13px;color:#78350f;">
										Bạn đã đăng ký theo chương trình nhóm bạn cùng tiến. Chuyên viên sẽ tư vấn cụ thể mức chiết khấu học phí và ưu đãi học bổng đồng hành dành cho bạn và bạn bè!
									</p>
								</div>
								<?php endif; ?>

								<!-- Information Card Table -->
								<div style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:28px;">
									<h2 style="margin:0 0 16px;font-size:15px;text-transform:uppercase;color:#0f172a;letter-spacing:0.5px;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">
										📋 Thông tin hồ sơ đăng ký
									</h2>
									<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:14px;color:#334155;">
										<tr>
											<td width="38%" style="padding:8px 0;color:#64748b;">Họ và tên:</td>
											<td width="62%" style="padding:8px 0;font-weight:600;color:#0f172a;"><?php echo $name; ?></td>
										</tr>
										<tr>
											<td style="padding:8px 0;color:#64748b;">Số điện thoại:</td>
											<td style="padding:8px 0;font-weight:600;color:#0f172a;"><?php echo $phone; ?></td>
										</tr>
										<?php if ( ! empty( $email ) && 'N/A' !== $email ) : ?>
										<tr>
											<td style="padding:8px 0;color:#64748b;">Email:</td>
											<td style="padding:8px 0;color:#0f172a;"><?php echo $email; ?></td>
										</tr>
										<?php endif; ?>
										<tr>
											<td style="padding:8px 0;color:#64748b;">Ngành học quan tâm:</td>
											<td style="padding:8px 0;font-weight:600;color:#1e3a8a;"><?php echo esc_html( $major_title ); ?></td>
										</tr>
										<tr>
											<td style="padding:8px 0;color:#64748b;">Trường đại học:</td>
											<td style="padding:8px 0;font-weight:600;color:#1e3a8a;"><?php echo esc_html( $school_title ); ?></td>
										</tr>
										<tr>
											<td style="padding:8px 0;color:#64748b;">Hệ đào tạo:</td>
											<td style="padding:8px 0;color:#0f172a;"><?php echo $training_type; ?></td>
										</tr>
										<tr>
											<td style="padding:8px 0;color:#64748b;">Cơ sở / Khu vực:</td>
											<td style="padding:8px 0;color:#0f172a;"><?php echo $campus; ?></td>
										</tr>
										<?php if ( ! empty( $message ) ) : ?>
										<tr>
											<td style="padding:8px 0;color:#64748b;vertical-align:top;">Lời nhắn / Yêu cầu:</td>
											<td style="padding:8px 0;color:#0f172a;line-height:1.5;"><?php echo nl2br( $message ); ?></td>
										</tr>
										<?php endif; ?>
									</table>
								</div>

								<!-- Next Steps Section -->
								<h2 style="margin:0 0 16px;font-size:16px;font-weight:700;color:#0f172a;">
									🚀 Quy trình hỗ trợ tiếp theo dành cho bạn:
								</h2>
								<div style="margin-bottom:28px;">
									<div style="display:flex;margin-bottom:12px;">
										<div style="background-color:#eff6ff;color:#2563eb;font-weight:700;width:28px;height:28px;border-radius:50%;text-align:center;line-height:28px;font-size:14px;display:inline-block;margin-right:12px;vertical-align:top;">1</div>
										<div style="display:inline-block;width:calc(100% - 44px);vertical-align:top;font-size:14px;color:#475569;">
											<strong style="color:#0f172a;">Tiếp nhận & đối chiếu hồ sơ:</strong> Chuyên viên tuyển sinh sẽ rà soát điều kiện đầu vào và phương án miễn giảm môn học/tín chỉ theo văn bằng hiện tại của bạn.
										</div>
									</div>
									<div style="display:flex;margin-bottom:12px;">
										<div style="background-color:#eff6ff;color:#2563eb;font-weight:700;width:28px;height:28px;border-radius:50%;text-align:center;line-height:28px;font-size:14px;display:inline-block;margin-right:12px;vertical-align:top;">2</div>
										<div style="display:inline-block;width:calc(100% - 44px);vertical-align:top;font-size:14px;color:#475569;">
											<strong style="color:#0f172a;">Tư vấn trực tiếp 1-1:</strong> Trong vòng <strong>15 - 30 phút</strong> (trong giờ làm việc), chuyên viên sẽ liên hệ với bạn qua số điện thoại/Zalo <strong><?php echo $phone; ?></strong> để cung cấp đầy đủ thông tin học phí, lịch khai giảng và hình thức học.
										</div>
									</div>
									<div style="display:flex;">
										<div style="background-color:#eff6ff;color:#2563eb;font-weight:700;width:28px;height:28px;border-radius:50%;text-align:center;line-height:28px;font-size:14px;display:inline-block;margin-right:12px;vertical-align:top;">3</div>
										<div style="display:inline-block;width:calc(100% - 44px);vertical-align:top;font-size:14px;color:#475569;">
											<strong style="color:#0f172a;">Hoàn thiện thủ tục nhập học:</strong> Hướng dẫn đăng ký xét tuyển trực tuyến tiện lợi, an toàn, chuẩn quy chế của Bộ Giáo dục và Đào tạo.
										</div>
									</div>
								</div>

								<!-- Support Hotline Box -->
								<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%);border-radius:12px;padding:20px;text-align:center;border:1px solid #bfdbfe;">
									<tr>
										<td>
											<p style="margin:0 0 6px;font-size:13px;color:#1e40af;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">
												Cần giải đáp thắc mắc khẩn cấp?
											</p>
											<p style="margin:0 0 14px;font-size:14px;color:#1e293b;">
												Hotline Ban Tuyển Sinh hỗ trợ 24/7:
											</p>
											<a href="tel:<?php echo esc_attr( $clean_hotline ); ?>" style="display:inline-block;background-color:#2563eb;color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;padding:10px 24px;border-radius:8px;box-shadow:0 2px 8px rgba(37,99,235,0.3);">
												📞 Gọi Hotline: <?php echo esc_html( $hotline ); ?>
											</a>
										</td>
									</tr>
								</table>

							</td>
						</tr>

						<!-- Footer -->
						<tr>
							<td style="background-color:#f8fafc;padding:24px 32px;text-align:center;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.6;">
								<p style="margin:0 0 6px;">
									© <?php echo esc_html( date( 'Y' ) ); ?> <strong><?php echo esc_html( $site_name ); ?></strong>. Toàn bộ quyền được bảo lưu.
								</p>
								<p style="margin:0 0 8px;">
									Website: <a href="<?php echo esc_url( $site_url ); ?>" style="color:#2563eb;text-decoration:none;"><?php echo esc_html( $site_url ); ?></a>
								</p>
								<p style="margin:0;font-size:11px;color:#94a3b8;">
									🛡 Cam kết bảo mật thông tin cá nhân của ứng viên theo Nghị định 13/2023/NĐ-CP. Thư điện tử này được tạo tự động từ hệ thống tiếp nhận hồ sơ.
								</p>
							</td>
						</tr>

					</table>
				</td>
			</tr>
		</table>
	</body>
	</html>
	<?php
	return ob_get_clean();
}

/**
 * Build responsive HTML alert email for Admissions Admin.
 */
function ltdh_build_admin_email_html( array $data, int $lead_id ): string {
	$name          = ! empty( $data['name'] ) ? esc_html( $data['name'] ) : 'N/A';
	$phone         = ! empty( $data['phone'] ) ? esc_html( $data['phone'] ) : 'N/A';
	$email         = ! empty( $data['email'] ) ? esc_html( $data['email'] ) : 'N/A';
	$message       = ! empty( $data['message'] ) ? esc_html( $data['message'] ) : '';
	$is_group_reg  = ! empty( $data['is_group_reg'] );
	$training_type = ! empty( $data['training_type'] ) && 'N/A' !== $data['training_type'] ? esc_html( $data['training_type'] ) : 'Chưa xác định';
	$campus        = ! empty( $data['campus'] ) && 'N/A' !== $data['campus'] ? esc_html( $data['campus'] ) : 'Chưa xác định';
	$referral      = ! empty( $data['referral_source'] ) ? esc_html( $data['referral_source'] ) : 'Website trực tiếp';

	$degree_link     = ! empty( $data['degree_link'] ) ? esc_url( $data['degree_link'] ) : '';
	$transcript_link = ! empty( $data['transcript_link'] ) ? esc_url( $data['transcript_link'] ) : '';

	$school_title = 'Chưa chọn';
	if ( ! empty( $data['school_id'] ) ) {
		$school_title = get_the_title( (int) $data['school_id'] );
	}
	$major_title = 'Chưa chọn';
	if ( ! empty( $data['major_id'] ) ) {
		$major_title = get_the_title( (int) $data['major_id'] );
	}

	$clean_phone   = preg_replace( '/\D/', '', $phone );
	$lead_code     = '#LTDH-' . str_pad( (string) $lead_id, 5, '0', STR_PAD_LEFT );
	$created_time  = current_time( 'd/m/Y H:i:s' );
	$admin_leads_url = admin_url( 'admin.php?page=ltdh_elig_leads_menu' );

	ob_start();
	?>
	<!DOCTYPE html>
	<html lang="vi">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Thông báo Lead Tuyển Sinh Mới</title>
	</head>
	<body style="margin:0;padding:0;background-color:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1e293b;line-height:1.5;">
		<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f8fafc;padding:24px 12px;">
			<tr>
				<td align="center">
					<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background-color:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);border:1px solid #e2e8f0;">
						
						<!-- Alert Header Banner -->
						<tr>
							<td style="background:<?php echo $is_group_reg ? 'linear-gradient(135deg,#b45309 0%,#d97706 100%)' : 'linear-gradient(135deg,#047857 0%,#059669 100%)'; ?>;padding:28px 24px;text-align:center;color:#ffffff;">
								<span style="display:inline-block;background:rgba(0,0,0,0.2);padding:4px 12px;border-radius:16px;font-size:12px;font-weight:700;letter-spacing:0.5px;margin-bottom:8px;">
									<?php echo $is_group_reg ? '👥 HỌC BỔNG ĐỒNG HÀNH - ĐĂNG KÝ THEO NHÓM' : '🔔 HỆ THỐNG LEAD TƯ VẤN MỚI'; ?>
								</span>
								<h1 style="margin:0 0 6px;font-size:22px;font-weight:700;color:#ffffff;">
									CÓ LEAD MỚI CẦN LIÊN HỆ
								</h1>
								<div style="font-size:14px;color:#ecfdf5;">
									Mã Lead: <strong style="color:#fef08a;"><?php echo esc_html( $lead_code ); ?></strong> | Thời gian: <?php echo esc_html( $created_time ); ?>
								</div>
							</td>
						</tr>

						<!-- Quick Action Buttons -->
						<tr>
							<td style="background-color:#f1f5f9;padding:14px 24px;border-bottom:1px solid #e2e8f0;text-align:center;">
								<a href="tel:<?php echo esc_attr( $clean_phone ); ?>" style="display:inline-block;background-color:#059669;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;padding:8px 18px;border-radius:6px;margin:3px 4px;">
									📞 Gọi ngay: <?php echo $phone; ?>
								</a>
								<?php if ( ! empty( $clean_phone ) ) : ?>
								<a href="https://zalo.me/<?php echo esc_attr( $clean_phone ); ?>" target="_blank" style="display:inline-block;background-color:#0284c7;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;padding:8px 18px;border-radius:6px;margin:3px 4px;">
									💬 Nhắn tin Zalo
								</a>
								<?php endif; ?>
							</td>
						</tr>

						<!-- Candidate Details Table -->
						<tr>
							<td style="padding:28px 24px;">
								<h2 style="margin:0 0 14px;font-size:15px;color:#0f172a;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #e2e8f0;padding-bottom:8px;">
									👤 Chi tiết ứng viên
								</h2>
								<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:14px;color:#334155;">
									<tr>
										<td width="36%" style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Họ và tên:</td>
										<td width="64%" style="padding:9px 0;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f5f9;font-size:15px;">
											<?php echo $name; ?>
										</td>
									</tr>
									<tr>
										<td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Số điện thoại:</td>
										<td style="padding:9px 0;font-weight:700;color:#059669;border-bottom:1px solid #f1f5f9;">
											<a href="tel:<?php echo esc_attr( $clean_phone ); ?>" style="color:#059669;text-decoration:none;"><?php echo $phone; ?></a>
										</td>
									</tr>
									<tr>
										<td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Địa chỉ Email:</td>
										<td style="padding:9px 0;color:#0f172a;border-bottom:1px solid #f1f5f9;">
											<?php if ( ! empty( $email ) && 'N/A' !== $email ) : ?>
												<a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:#2563eb;text-decoration:none;"><?php echo $email; ?></a>
											<?php else : ?>
												<span style="color:#94a3b8;">(Không cung cấp)</span>
											<?php endif; ?>
										</td>
									</tr>
									<tr>
										<td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Ngành quan tâm:</td>
										<td style="padding:9px 0;font-weight:600;color:#1e3a8a;border-bottom:1px solid #f1f5f9;"><?php echo esc_html( $major_title ); ?></td>
									</tr>
									<tr>
										<td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Trường đăng ký:</td>
										<td style="padding:9px 0;font-weight:600;color:#1e3a8a;border-bottom:1px solid #f1f5f9;"><?php echo esc_html( $school_title ); ?></td>
									</tr>
									<tr>
										<td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Hệ đào tạo:</td>
										<td style="padding:9px 0;color:#0f172a;border-bottom:1px solid #f1f5f9;"><?php echo $training_type; ?></td>
									</tr>
									<tr>
										<td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Cơ sở / Tỉnh thành:</td>
										<td style="padding:9px 0;color:#0f172a;border-bottom:1px solid #f1f5f9;"><?php echo $campus; ?></td>
									</tr>
									<tr>
										<td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Hình thức tuyển sinh:</td>
										<td style="padding:9px 0;color:#0f172a;border-bottom:1px solid #f1f5f9;">
											<?php if ( $is_group_reg ) : ?>
												<span style="display:inline-block;background-color:#fef3c7;color:#92400e;font-weight:700;padding:2px 8px;border-radius:4px;font-size:12px;">👥 ĐĂNG KÝ NHÓM (Học bổng đồng hành)</span>
											<?php else : ?>
												<span>👤 Cá nhân</span>
											<?php endif; ?>
										</td>
									</tr>
									<?php if ( ! empty( $message ) ) : ?>
									<tr>
										<td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;vertical-align:top;">Lời nhắn / Ghi chú:</td>
										<td style="padding:9px 0;color:#0f172a;border-bottom:1px solid #f1f5f9;line-height:1.5;">
											<div style="background-color:#f8fafc;padding:10px 12px;border-radius:6px;border:1px solid #e2e8f0;">
												<?php echo nl2br( $message ); ?>
											</div>
										</td>
									</tr>
									<?php endif; ?>
									<?php if ( ! empty( $degree_link ) ) : ?>
									<tr>
										<td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Ảnh bằng cấp đính kèm:</td>
										<td style="padding:9px 0;border-bottom:1px solid #f1f5f9;">
											<a href="<?php echo $degree_link; ?>" target="_blank" style="color:#2563eb;text-decoration:none;font-weight:600;">📎 Mở xem tệp bằng cấp</a>
										</td>
									</tr>
									<?php endif; ?>
									<?php if ( ! empty( $transcript_link ) ) : ?>
									<tr>
										<td style="padding:9px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Bảng điểm đính kèm:</td>
										<td style="padding:9px 0;border-bottom:1px solid #f1f5f9;">
											<a href="<?php echo $transcript_link; ?>" target="_blank" style="color:#2563eb;text-decoration:none;font-weight:600;">📊 Mở xem bảng điểm</a>
										</td>
									</tr>
									<?php endif; ?>
									<tr>
										<td style="padding:9px 0;color:#64748b;">Nguồn đăng ký:</td>
										<td style="padding:9px 0;color:#64748b;font-size:12px;word-break:break-all;"><?php echo $referral; ?></td>
									</tr>
								</table>

								<!-- Integration Status Summary -->
								<div style="margin-top:24px;padding:14px 16px;background-color:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;font-size:12px;color:#475569;">
									<div style="font-weight:600;margin-bottom:6px;color:#0f172a;">Trạng thái hệ thống:</div>
									<div>✓ <strong>Telegram Alert:</strong> Đã chuyển tiếp tin nhắn tức thời tới nhóm tiếp nhận</div>
									<div>✓ <strong>ERPNext CRM:</strong> Đã gửi dữ liệu sang hàng đợi đồng bộ CRM</div>
									<div>✓ <strong>CSDL wp_ltdh_leads:</strong> Đã lưu trữ an toàn với ID #<?php echo (int) $lead_id; ?></div>
								</div>

								<!-- Link to Admin Portal -->
								<div style="margin-top:20px;text-align:center;">
									<a href="<?php echo esc_url( $admin_leads_url ); ?>" style="display:inline-block;background-color:#0f172a;color:#ffffff;text-decoration:none;font-size:13px;font-weight:600;padding:10px 20px;border-radius:6px;">
										Quản lý toàn bộ danh sách Lead trong WordPress
									</a>
								</div>
							</td>
						</tr>

						<!-- Footer -->
						<tr>
							<td style="background-color:#f8fafc;padding:16px 24px;text-align:center;border-top:1px solid #e2e8f0;font-size:11px;color:#94a3b8;">
								Email thông báo tự động từ Hệ thống Tiếp nhận Tuyển sinh Liên Thông Đại Học. Vui lòng xử lý liên hệ ứng viên sớm nhất để đạt tỷ lệ chuyển đổi cao.
							</td>
						</tr>

					</table>
				</td>
			</tr>
		</table>
	</body>
	</html>
	<?php
	return ob_get_clean();
}

// ----------------------------------------------------
// 5. Main Notification Coordinator
// ----------------------------------------------------

/**
 * Trigger both student confirmation and admin alert emails.
 *
 * Designed to be entirely fail-safe: failures never throw exceptions,
 * never block the caller, and never impede Telegram or ERPNext sync.
 *
 * @param array $data    Lead data payload (name, phone, email, program_id, school_id, major_id, etc.)
 * @param int   $lead_id Database primary key ID of the captured lead.
 * @return array         Status report of dispatched emails.
 */
function ltdh_trigger_email_notifications( array $data, int $lead_id = 0 ): array {
	$result = [
		'student_sent'  => false,
		'admin_sent'    => false,
		'student_error' => '',
		'admin_error'   => '',
	];

	if ( ! ltdh_is_email_notifications_enabled() ) {
		return $result;
	}

	$name         = ! empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : 'Học viên';
	$student_mail = ! empty( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
	$is_group_reg = ! empty( $data['is_group_reg'] );

	$school_title = '';
	if ( ! empty( $data['school_id'] ) ) {
		$school_title = get_the_title( (int) $data['school_id'] );
	}
	$major_title = '';
	if ( ! empty( $data['major_id'] ) ) {
		$major_title = get_the_title( (int) $data['major_id'] );
	}

	// 1. Dispatch Student Confirmation Email (if student provided a valid email)
	if ( ltdh_is_student_email_enabled() && is_email( $student_mail ) ) {
		$student_subject = '';
		if ( function_exists( 'get_field' ) ) {
			$student_subject = (string) get_field( 'email_student_subject', 'options' );
		}
		if ( empty( $student_subject ) ) {
			$student_subject = sprintf( '[Liên Thông Đại Học] Xác nhận đăng ký tư vấn tuyển sinh thành công - %s', $name );
		}
		$student_subject = apply_filters( 'ltdh_student_email_subject', $student_subject, $data, $lead_id );

		$student_body = ltdh_build_student_email_html( $data, $lead_id );
		$student_body = apply_filters( 'ltdh_student_email_content', $student_body, $data, $lead_id );

		$student_sent = ltdh_send_html_mail( $student_mail, $student_subject, $student_body );
		$result['student_sent'] = $student_sent;

		if ( ! $student_sent ) {
			$result['student_error'] = 'Failed to deliver confirmation email to student (' . $student_mail . ').';
			error_log( '[LTDH Email] ' . $result['student_error'] );
		}
	}

	// 2. Dispatch Admissions Admin Alert Email
	if ( ltdh_is_admin_email_enabled() ) {
		$admin_recipients = ltdh_get_admin_notification_emails();
		$admin_recipients = apply_filters( 'ltdh_admin_email_recipients', $admin_recipients, $data, $lead_id );

		if ( ! empty( $admin_recipients ) ) {
			$prefix = $is_group_reg ? '👥 [Học Bổng Nhóm]' : '🔔 [Lead Mới]';
			$target_info = trim( ($major_title ? $major_title : '') . ($school_title ? ' - ' . $school_title : '') );
			if ( empty( $target_info ) ) {
				$target_info = 'Tư vấn tuyển sinh chung';
			}

			$admin_subject = sprintf( '%s #%d: %s (%s)', $prefix, $lead_id, $name, $target_info );
			$admin_subject = apply_filters( 'ltdh_admin_email_subject', $admin_subject, $data, $lead_id );

			$admin_body = ltdh_build_admin_email_html( $data, $lead_id );
			$admin_body = apply_filters( 'ltdh_admin_email_content', $admin_body, $data, $lead_id );

			$admin_sent = ltdh_send_html_mail( $admin_recipients, $admin_subject, $admin_body );
			$result['admin_sent'] = $admin_sent;

			if ( ! $admin_sent ) {
				$result['admin_error'] = 'Failed to deliver notification email to admin recipients.';
				error_log( '[LTDH Email] ' . $result['admin_error'] );
			}
		}
	}

	return $result;
}

/**
 * Trigger supplementary document upload email notification to admin.
 *
 * @param array $lead_row    Database row data of the lead.
 * @param array $update_info Additional uploaded document metadata.
 * @return bool
 */
function ltdh_trigger_admin_supplement_email( array $lead_row, array $update_info ): bool {
	if ( ! ltdh_is_admin_email_enabled() ) {
		return false;
	}

	$admin_recipients = ltdh_get_admin_notification_emails();
	if ( empty( $admin_recipients ) ) {
		return false;
	}

	$lead_id = (int) ( $lead_row['id'] ?? 0 );
	$name    = ! empty( $lead_row['name'] ) ? esc_html( $lead_row['name'] ) : 'N/A';
	$phone   = ! empty( $lead_row['phone'] ) ? esc_html( $lead_row['phone'] ) : 'N/A';
	$clean_phone = preg_replace( '/\D/', '', $phone );

	$degree_link     = ! empty( $update_info['degree_link'] ) ? esc_url( $update_info['degree_link'] ) : '';
	$transcript_link = ! empty( $update_info['transcript_link'] ) ? esc_url( $update_info['transcript_link'] ) : '';
	$prev_school     = ! empty( $update_info['previous_school'] ) ? esc_html( $update_info['previous_school'] ) : '';
	$graduation      = ! empty( $update_info['graduation'] ) ? esc_html( $update_info['graduation'] ) : '';

	$subject = sprintf( '📎 [Bổ sung hồ sơ Lead #%d] %s đã gửi bằng cấp/bảng điểm', $lead_id, $name );

	ob_start();
	?>
	<!DOCTYPE html>
	<html lang="vi">
	<head><meta charset="UTF-8"><title>Hồ sơ bổ sung</title></head>
	<body style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1e293b;padding:20px;background-color:#f8fafc;">
		<div style="max-width:580px;margin:0 auto;background:#ffffff;border-radius:12px;padding:24px;border:1px solid #e2e8f0;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
			<h2 style="color:#0284c7;margin:0 0 12px;font-size:18px;">
				📎 ỨNG VIÊN BỔ SUNG HỒ SƠ XÁC MINH ĐIỀU KIỆN
			</h2>
			<p style="font-size:14px;color:#475569;margin-bottom:16px;">
				Ứng viên <strong><?php echo $name; ?></strong> (Mã Lead: <strong>#LTDH-<?php echo $lead_id; ?></strong>) vừa tải lên giấy tờ xác minh bổ sung:
			</p>
			<table width="100%" cellspacing="0" cellpadding="6" style="font-size:14px;border-collapse:collapse;margin-bottom:20px;">
				<tr>
					<td width="35%" style="color:#64748b;border-bottom:1px solid #f1f5f9;">Số điện thoại:</td>
					<td width="65%" style="font-weight:700;color:#059669;border-bottom:1px solid #f1f5f9;">
						<a href="tel:<?php echo esc_attr( $clean_phone ); ?>" style="color:#059669;text-decoration:none;"><?php echo $phone; ?></a>
					</td>
				</tr>
				<?php if ( ! empty( $prev_school ) ) : ?>
				<tr>
					<td style="color:#64748b;border-bottom:1px solid #f1f5f9;">Trường cũ / Tốt nghiệp:</td>
					<td style="border-bottom:1px solid #f1f5f9;color:#0f172a;"><?php echo $prev_school; ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( ! empty( $graduation ) ) : ?>
				<tr>
					<td style="color:#64748b;border-bottom:1px solid #f1f5f9;">Năm tốt nghiệp / sinh:</td>
					<td style="border-bottom:1px solid #f1f5f9;color:#0f172a;"><?php echo $graduation; ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( ! empty( $degree_link ) ) : ?>
				<tr>
					<td style="color:#64748b;border-bottom:1px solid #f1f5f9;">Ảnh bằng cấp:</td>
					<td style="border-bottom:1px solid #f1f5f9;">
						<a href="<?php echo $degree_link; ?>" target="_blank" style="color:#2563eb;font-weight:600;">Xem ảnh bằng tốt nghiệp ↗</a>
					</td>
				</tr>
				<?php endif; ?>
				<?php if ( ! empty( $transcript_link ) ) : ?>
				<tr>
					<td style="color:#64748b;border-bottom:1px solid #f1f5f9;">Ảnh/link bảng điểm:</td>
					<td style="border-bottom:1px solid #f1f5f9;">
						<a href="<?php echo $transcript_link; ?>" target="_blank" style="color:#2563eb;font-weight:600;">Xem bảng điểm chi tiết ↗</a>
					</td>
				</tr>
				<?php endif; ?>
			</table>
			<div style="text-align:center;margin-top:16px;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ltdh_elig_leads_menu' ) ); ?>" style="display:inline-block;background-color:#0284c7;color:#ffffff;text-decoration:none;font-size:13px;font-weight:600;padding:8px 18px;border-radius:6px;">
					Mở quản trị Lead trong WordPress
				</a>
			</div>
		</div>
	</body>
	</html>
	<?php
	$body = ob_get_clean();

	return ltdh_send_html_mail( $admin_recipients, $subject, $body );
}
