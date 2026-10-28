<?php

/**
 * Helper Functions — Logo, breadcrumb, thumbnails, contact info, form shortcodes.
 *
 * All functions prefixed with ltdh_ are globally available.
 * Every function that previously hardcoded fallbacks now uses ltdh_get_defaults().
 *
 * @package lienthongdaihoc
 */

if (! defined('ABSPATH')) {
	exit;
}

// ----------------------------------------------------
// 1. Contact Information Helpers
// ----------------------------------------------------

/**
 * Get the global hotline number.
 */
function ltdh_get_hotline(): string {
	$hotline = '';
	if (function_exists('get_field')) {
		$hotline = get_field('global_hotline', 'options');
	}
	return $hotline ?: ltdh_default('contact', 'hotline');
}

/**
 * Get the global Zalo URL.
 */
function ltdh_get_zalo_url(): string {
	$url = '';
	if (function_exists('get_field')) {
		$url = get_field('global_zalo_url', 'options');
	}
	return $url ?: ltdh_default('contact', 'zalo_url');
}

/**
 * Detect the current school ID based on the viewed page/context.
 *
 * @return int School ID or 0 if not found.
 */
function ltdh_get_current_context_school_id(): int {
	// 1. Direct school singular
	if ( is_singular( 'school' ) ) {
		return (int) get_the_ID();
	}

	// 2. Query parameters (?truong=..., ?school=..., ?from_school=...)
	$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );
	if ( ! empty( $school_param ) ) {
		if ( is_numeric( $school_param ) ) {
			$sid = (int) $school_param;
			if ( $sid && 'school' === get_post_type( $sid ) && 'publish' === get_post_status( $sid ) ) {
				return $sid;
			}
		} else {
			$cpt_school = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
			$s_post     = get_page_by_path( sanitize_title( $school_param ), OBJECT, $cpt_school );
			if ( ! $s_post ) {
				$found_by_code = get_posts( [
					'post_type'   => $cpt_school,
					'post_status' => 'publish',
					'numberposts' => 1,
					'meta_query'  => [
						[
							'key'     => 'school_code',
							'value'   => sanitize_text_field( $school_param ),
							'compare' => '=',
						],
					],
				] );
				if ( ! empty( $found_by_code ) ) {
					$s_post = $found_by_code[0];
				}
			}
			if ( $s_post && 'publish' === $s_post->post_status && $cpt_school === $s_post->post_type ) {
				return (int) $s_post->ID;
			}
		}
	}

	// 3. Program singular
	if ( is_singular( 'program' ) ) {
		$prog_id = get_the_ID();
		if ( function_exists( 'ltdh_get_program_school_id' ) ) {
			$sid = ltdh_get_program_school_id( $prog_id );
			if ( $sid ) {
				return (int) $sid;
			}
		}
		$school_rel = function_exists( 'get_field' ) ? get_field( 'school_relationship', $prog_id ) : get_post_meta( $prog_id, 'school_relationship', true );
		if ( ! empty( $school_rel ) ) {
			if ( is_array( $school_rel ) ) {
				$first = reset( $school_rel );
				return is_object( $first ) ? (int) $first->ID : (int) $first;
			}
			return is_object( $school_rel ) ? (int) $school_rel->ID : (int) $school_rel;
		}
	}

	// 4. Post or guide singular
	if ( is_singular( [ 'post', 'guide' ] ) ) {
		$post_id    = get_the_ID();
		$school_rel = function_exists( 'get_field' ) ? get_field( 'school_relationship', $post_id ) : get_post_meta( $post_id, 'school_relationship', true );
		if ( ! empty( $school_rel ) ) {
			if ( is_array( $school_rel ) ) {
				$first = reset( $school_rel );
				return is_object( $first ) ? (int) $first->ID : (int) $first;
			}
			return is_object( $school_rel ) ? (int) $school_rel->ID : (int) $school_rel;
		}
	}

	return 0;
}

/**
 * Get the Zalo group URL for a specific school or the current context school.
 *
 * @param int $school_id School ID (0 to auto-detect from context).
 * @return string Zalo URL.
 */
function ltdh_get_school_zalo_group_url( int $school_id = 0 ): string {
	if ( ! $school_id ) {
		$school_id = ltdh_get_current_context_school_id();
	}
	if ( $school_id ) {
		$url = function_exists( 'get_field' ) ? get_field( 'zalo_group_url', $school_id ) : '';
		if ( empty( $url ) ) {
			$url = get_post_meta( $school_id, 'zalo_group_url', true );
		}
		if ( ! empty( $url ) && is_string( $url ) ) {
			return trim( $url );
		}
	}
	return ltdh_get_zalo_url();
}

/**
 * Get the hotline for a specific school or the current context school.
 *
 * @param int $school_id School ID (0 to auto-detect from context).
 * @return string Hotline string.
 */
function ltdh_get_school_hotline( int $school_id = 0 ): string {
	if ( ! $school_id ) {
		$school_id = ltdh_get_current_context_school_id();
	}
	if ( $school_id ) {
		$phone = get_post_meta( $school_id, 'hotline', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'hotline', $school_id ) : '' );
		if ( ! empty( $phone ) && is_string( $phone ) ) {
			return trim( $phone );
		}
	}
	return ltdh_get_hotline();
}


/**
 * Get the global Messenger URL.
 */
function ltdh_get_messenger_url(): string {
	$url = '';
	if (function_exists('get_field')) {
		$url = get_field('global_messenger_url', 'options');
	}
	return $url ?: ltdh_default('contact', 'messenger_url');
}

/**
 * Get the global contact email.
 */
function ltdh_get_email(): string {
	$email = '';
	if ( function_exists( 'get_field' ) ) {
		$email = (string) get_field( 'global_email', 'options' );
	}
	return trim( $email ) ?: ltdh_default( 'contact', 'email' );
}

/**
 * Get the global address.
 */
function ltdh_get_address(): string {
	$address = '';
	if ( function_exists( 'get_field' ) ) {
		$address = (string) get_field( 'global_address', 'options' );
	}
	return trim( $address ) ?: ltdh_default( 'contact', 'address' );
}

/**
 * Get the company name.
 */
function ltdh_get_company_name(): string {
	$name = '';
	if ( function_exists( 'get_field' ) ) {
		$name = (string) get_field( 'global_company_name', 'options' );
	}
	return trim( $name ) ?: ltdh_default( 'contact', 'company_name' );
}

/**
 * Format and sanitize contact information HTML for display.
 * Replaces legacy "Địa điểm khai giảng & thi" headings and addresses with the modern admission office reception address.
 *
 * @param string $contact_html Raw contact HTML.
 * @param int    $school_id    Optional school ID context.
 * @return string Formatted HTML.
 */
function ltdh_format_contact_info( string $contact_html, int $school_id = 0 ): string {
	$new_address_html = '<p class="text-slate-800 leading-relaxed font-normal"><strong>✅ Địa chỉ nộp hồ sơ (trực tiếp hoặc chuyển phát nhanh):</strong> 📍 Tầng 2 – Khu Hiệu bộ, 306B Kim Mã, phường Giảng Võ, Hà Nội (Trong khuôn viên Trường Lê Duẩn, đối diện Trường ĐH GTVT).</p>';

	if ( empty( trim( strip_tags( $contact_html ) ) ) ) {
		return $new_address_html;
	}

	// Pattern matches any legacy "Địa điểm khai giảng & thi" / "Địa điểm khai giảng" heading and its immediately following address paragraph or list
	$pattern = '/<h[1-6][^>]*>[\s\S]*?(?:địa\s*điểm\s*khai\s*giảng|khai\s*giảng\s*&(?:amp;)?\s*thi)[\s\S]*?<\/h[1-6]>\s*(?:<p[^>]*>[\s\S]*?<\/p>|<ul[^>]*>[\s\S]*?<\/ul>)?/ui';

	if ( preg_match( $pattern, $contact_html ) ) {
		$contact_html = preg_replace( $pattern, $new_address_html, $contact_html, 1 );
	}

	// Also check if any standalone legacy heading remains
	$heading_only_pattern = '/<h[1-6][^>]*>[\s\S]*?(?:địa\s*điểm\s*khai\s*giảng|khai\s*giảng\s*&(?:amp;)?\s*thi)[\s\S]*?<\/h[1-6]>/ui';
	$contact_html = preg_replace( $heading_only_pattern, '', $contact_html );

	// Remove legacy Cầu Giấy address text if still present
	$legacy_address_pattern = '/<p[^>]*>[\s\S]*?Phòng\s+Khảo\s+thí[\s\S]*?Cầu\s+Giấy[\s\S]*?<\/p>/ui';
	if ( preg_match( $legacy_address_pattern, $contact_html ) ) {
		$contact_html = preg_replace( $legacy_address_pattern, $new_address_html, $contact_html, 1 );
	}

	return trim( (string) $contact_html );
}

add_filter( 'acf/format_value/name=contact_info', function( $value, $post_id, $field ) {
	if ( ! empty( $value ) && is_string( $value ) ) {
		return ltdh_format_contact_info( $value, (int) $post_id );
	}
	return $value;
}, 20, 3 );

/**
 * Get a program's effective hotline: program override → school → global.
 */
function ltdh_get_program_hotline(int $program_id): string {
	$override = '';
	if (function_exists('get_field')) {
		$override = get_field('hotline_override', $program_id);
	}
	if (! empty($override)) {
		return $override;
	}

	$school_id = 0;
	if (function_exists('get_field')) {
		$school_id = intval(get_field(LTDH_META_SCHOOL_REL, $program_id) ?: 0);
	}
	if ($school_id) {
		$school_hotline = '';
		if (function_exists('get_field')) {
			$school_hotline = get_field('hotline', $school_id);
		}
		if (! empty($school_hotline)) {
			return $school_hotline;
		}
	}

	return ltdh_get_hotline();
}

/**
 * Resolves the valid, published school ID for a given program.
 * Falls back to matching slug/code if relation was broken or orphaned.
 *
 * @param int $program_id
 * @return int
 */
function ltdh_get_program_school_id( int $program_id ): int {
	if ( ! $program_id ) {
		return 0;
	}

	$school_id = function_exists( 'get_field' ) ? get_field( defined( 'LTDH_META_SCHOOL_REL' ) ? LTDH_META_SCHOOL_REL : 'school_relationship', $program_id ) : 0;
	if ( empty( $school_id ) ) {
		$school_id = get_post_meta( $program_id, defined( 'LTDH_META_SCHOOL_REL' ) ? LTDH_META_SCHOOL_REL : 'school_relationship', true );
	}
	if ( is_array( $school_id ) ) {
		$first     = reset( $school_id );
		$school_id = is_object( $first ) ? $first->ID : intval( $first );
	} elseif ( is_object( $school_id ) ) {
		$school_id = $school_id->ID;
	}
	$school_id = intval( $school_id );

	if ( $school_id > 0 && 'publish' === get_post_status( $school_id ) ) {
		return $school_id;
	}

	// Healing fallback: Attempt to match existing published schools by slug/code
	$slug = get_post_field( 'post_name', $program_id );
	if ( empty( $slug ) ) {
		return 0;
	}

	$school_cpt = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
	$all_schools = get_posts( [
		'post_type'      => $school_cpt,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	] );

	foreach ( $all_schools as $school ) {
		$clean_school_slug = str_replace( 'truong-dai-hoc-', '', $school->post_name );
		$clean_school_slug = str_replace( 'hoc-vien-', '', $clean_school_slug );
		$clean_school_slug = str_replace( 'dai-hoc-', '', $clean_school_slug );
		$school_code       = strtolower( (string) get_post_meta( $school->ID, 'school_code', true ) );

		// Strict suffix matching: program slug ends with -$school_code, -$post_name, or -$clean_school_slug
		// E.g. "...-tmu", "...-tvu", "...-dai-hoc-thai-nguyen", "...-intracom", "...-utc"
		$matched = false;
		if ( ! empty( $school_code ) && preg_match( '/-' . preg_quote( $school_code, '/' ) . '$/i', $slug ) ) {
			$matched = true;
		} elseif ( preg_match( '/-' . preg_quote( $school->post_name, '/' ) . '$/i', $slug ) ) {
			$matched = true;
		} elseif ( ! empty( $clean_school_slug ) && preg_match( '/-' . preg_quote( $clean_school_slug, '/' ) . '$/i', $slug ) ) {
			$matched = true;
		}

		if ( $matched ) {
			$meta_key = defined( 'LTDH_META_SCHOOL_REL' ) ? LTDH_META_SCHOOL_REL : 'school_relationship';
			update_post_meta( $program_id, $meta_key, $school->ID );
			return $school->ID;
		}
	}

	return 0;
}

/**
 * Chuẩn hóa tiêu đề thẻ card chương trình đào tạo theo quy tắc:
 * "Liên thông ngành [Tiêu đề trong quản trị / Tên ngành] - [Mã trường]"
 *
 * @param string $raw_prog_title Tiêu đề bài viết chương trình trong quản trị.
 * @param string $school_code Mã trường (ví dụ: TMU, UTC, PTIT).
 * @param string $school_name Tên trường đào tạo (dùng khi không có mã trường).
 * @param string $major_title Tên ngành liên kết (fallback nếu tiêu đề quản trị rỗng).
 * @return string Tiêu đề thẻ card đã được định dạng chuẩn.
 */
function ltdh_format_program_card_headline( ?string $raw_prog_title = '', ?string $school_code = '', ?string $school_name = '', ?string $major_title = '' ): string {
	$school_code   = trim( html_entity_decode( (string) $school_code, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	$school_name   = trim( html_entity_decode( (string) $school_name, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	$school_suffix = '';

	if ( ! empty( $school_code ) ) {
		$school_suffix = ' - ' . $school_code;
	} elseif ( ! empty( $school_name ) ) {
		$clean_school_fallback = preg_replace( '/^trường\s+đại\s+học\s+/iu', 'ĐH ', $school_name );
		$school_suffix = $clean_school_fallback ? ' - ' . $clean_school_fallback : '';
	}

	$clean_prog = trim( (string) $raw_prog_title );
	$clean_prog = html_entity_decode( $clean_prog, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$clean_prog = preg_replace( '/^(?:tuyển sinh\s+)?(?:liên thông\s+)?(?:ngành\s+)?/iu', '', $clean_prog );
	$clean_prog = preg_replace( '/^(?:cử nhân|kỹ sư|đại học)\s+/iu', '', $clean_prog );

	// Lặp lược bỏ các hậu tố tên trường, mã trường, ngoặc hệ đào tạo cho đến khi chuỗi ổn định
	$prev = '';
	while ( $prev !== $clean_prog ) {
		$prev = $clean_prog;
		if ( ! empty( $school_code ) ) {
			$clean_prog = preg_replace( '/\s*[-–—]\s*(?:(?:đh|trường|học viện|đại học)\s+)?' . preg_quote( $school_code, '/' ) . '$/iu', '', $clean_prog );
		}
		if ( ! empty( $school_name ) ) {
			$clean_prog = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_name, '/' ) . '$/iu', '', $clean_prog );
		}
		// Lược bỏ hậu tố tên trường dạng - ĐH ..., - Trường ..., - Học viện ...
		$clean_prog = preg_replace( '/\s*[-–—]\s*(?:đh|trường|học viện|đại học)\s+[^\-–—()]+$/iu', '', $clean_prog );
		// Lược bỏ phần ngoặc hệ đào tạo ở đuôi (ví dụ: (Chính quy), (Từ xa)...)
		$clean_prog = preg_replace( '/\s*\([^)]*\)$/u', '', $clean_prog );
		// Lược bỏ hệ đào tạo sau dấu gạch ngang nếu có (ví dụ: - Từ xa, - Đào tạo từ xa...)
		$clean_prog = preg_replace( '/\s*[-–—]\s*(?:hệ\s+|đào tạo\s+)?(?:từ xa|chính quy|vừa học vừa làm|vhvl|vừa làm vừa học|online|văn bằng 2|vb2)$/iu', '', $clean_prog );
		$clean_prog = trim( $clean_prog, " -–—\t\n\r\0\x0B" );
	}

	if ( empty( $clean_prog ) && ! empty( $major_title ) ) {
		$clean_prog = preg_replace( '/^(?:ngành|cử nhân|kỹ sư)\s+/iu', '', trim( $major_title ) );
	}

	if ( empty( $clean_prog ) ) {
		$clean_prog = trim( $raw_prog_title );
	}

	return 'Liên thông ngành ' . $clean_prog . $school_suffix;
}

/**
 * Lấy giá trị custom field (ưu tiên get_field của ACF, nếu rỗng thì fallback sang get_post_meta)
 *
 * @param string $key
 * @param int $post_id
 * @return mixed
 */
function ltdh_get_field_or_meta( string $key, int $post_id ) {
	$val = function_exists( 'get_field' ) ? get_field( $key, $post_id ) : null;
	if ( empty( $val ) ) {
		$val = get_post_meta( $post_id, $key, true );
	}
	return $val;
}

/**
 * Kiểm tra xem một URL có phải là liên kết Google Drive / Google Docs hay không.
 *
 * @param string $url
 * @return bool
 */
function ltdh_is_google_drive_url( string $url ): bool {
	if ( empty( $url ) ) {
		return false;
	}
	return (bool) preg_match( '#^https?://(drive|docs)\.google\.com/#i', trim( $url ) );
}

/**
 * Chuẩn hóa giá trị trường file của ACF (Attachment ID, Array, hoặc URL string)
 *
 * @param mixed $raw
 * @return array [ 'url' => string, 'title' => string, 'filename' => string, 'ext' => string, 'is_drive' => bool ]
 */
function ltdh_normalize_admission_file_info( $raw ): array {
	$url           = '';
	$title         = '';
	$attachment_id = 0;
	$local_path    = '';

	if ( is_array( $raw ) && ! empty( $raw['url'] ) ) {
		$url   = (string) $raw['url'];
		$title = ! empty( $raw['title'] ) ? (string) $raw['title'] : ( ! empty( $raw['filename'] ) ? (string) $raw['filename'] : '' );
		if ( ! empty( $raw['ID'] ) ) {
			$attachment_id = (int) $raw['ID'];
		}
	} elseif ( is_numeric( $raw ) && (int) $raw > 0 ) {
		$attachment_id = (int) $raw;
		$url           = wp_get_attachment_url( $attachment_id ) ?: '';
		$title         = get_the_title( $attachment_id );
	} elseif ( is_string( $raw ) && ! empty( $raw ) ) {
		$url = trim( $raw );
	}

	if ( $attachment_id > 0 && function_exists( 'get_attached_file' ) ) {
		$local_path = (string) get_attached_file( $attachment_id );
	}

	$is_drive = ltdh_is_google_drive_url( $url );
	$filename = ! empty( $url ) ? basename( parse_url( $url, PHP_URL_PATH ) ?: '' ) : '';
	$ext      = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

	if ( $is_drive && empty( $ext ) ) {
		$ext = str_contains( $url, '/folders/' ) ? 'drive_folder' : 'drive';
	}

	if ( empty( $local_path ) && ! empty( $url ) && ! $is_drive && function_exists( 'wp_upload_dir' ) ) {
		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['baseurl'] ) && str_starts_with( $url, $upload_dir['baseurl'] ) ) {
			$rel        = substr( $url, strlen( $upload_dir['baseurl'] ) );
			$check_path = $upload_dir['basedir'] . $rel;
			if ( file_exists( $check_path ) ) {
				$local_path = $check_path;
			}
		} elseif ( function_exists( 'content_url' ) && defined( 'WP_CONTENT_DIR' ) && str_starts_with( $url, content_url() ) ) {
			$rel        = substr( $url, strlen( content_url() ) );
			$check_path = WP_CONTENT_DIR . $rel;
			if ( file_exists( $check_path ) ) {
				$local_path = $check_path;
			}
		}
	}

	return [
		'url'           => $url,
		'title'         => $title ?: $filename,
		'filename'      => $filename,
		'ext'           => $ext,
		'attachment_id' => $attachment_id,
		'local_path'    => $local_path,
		'is_drive'      => $is_drive,
	];
}

/**
 * Tự động tạo và cache file ZIP gộp toàn bộ biểu mẫu tuyển sinh (Lead Magnet Auto-ZIP)
 *
 * @param array  $documents Danh sách documents đã chuẩn hóa
 * @param string $slug_prefix Tiền tố tên file ZIP (ví dụ: ho-so-cntt-tmu)
 * @return string URL file ZIP (hoặc rỗng nếu không tạo được)
 */
function ltdh_get_or_create_admission_zip( array $documents, string $slug_prefix = 'ho-so-tuyen-sinh' ): string {
	if ( ! class_exists( 'ZipArchive' ) || count( $documents ) < 2 || ! function_exists( 'wp_upload_dir' ) ) {
		return '';
	}

	$upload_dir = wp_upload_dir();
	if ( empty( $upload_dir['basedir'] ) || empty( $upload_dir['baseurl'] ) ) {
		return '';
	}

	$zip_dir = trailingslashit( $upload_dir['basedir'] ) . 'admission-zips';
	if ( ! file_exists( $zip_dir ) && function_exists( 'wp_mkdir_p' ) ) {
		wp_mkdir_p( $zip_dir );
	}

	// Tạo hash nhận diện dựa trên nội dung các documents (đổi URL hoặc nhãn sẽ tự động tạo ZIP mới)
	$hash_content = '';
	foreach ( $documents as $doc ) {
		$hash_content .= ( $doc['url'] ?? '' ) . '|' . ( $doc['label'] ?? '' ) . ';';
	}
	$content_hash = substr( md5( $hash_content ), 0, 10 );

	$clean_prefix = sanitize_title( $slug_prefix ) ?: 'ho-so-tuyen-sinh';
	$zip_filename = "{$clean_prefix}-{$content_hash}.zip";
	$zip_path     = $zip_dir . '/' . $zip_filename;
	$zip_url      = trailingslashit( $upload_dir['baseurl'] ) . 'admission-zips/' . $zip_filename;

	// Nếu file cache đã tồn tại và hợp lệ (> 0 bytes), trả về URL luôn
	if ( file_exists( $zip_path ) && filesize( $zip_path ) > 0 ) {
		return $zip_url;
	}

	$zip = new ZipArchive();
	if ( $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
		return '';
	}

	$used_names  = [];
	$added_count = 0;

	foreach ( $documents as $doc ) {
		$doc_url    = $doc['url'] ?? '';
		$doc_label  = $doc['label'] ?? '';
		$local_path = $doc['local_path'] ?? '';
		$ext        = ! empty( $doc['ext'] ) ? $doc['ext'] : 'pdf';

		if ( empty( $doc_url ) ) {
			continue;
		}

		// Tạo tên file an toàn bên trong file ZIP từ tiêu đề biểu mẫu
		$sanitized_base = sanitize_file_name( remove_accents( $doc_label ) );
		if ( empty( $sanitized_base ) ) {
			$sanitized_base = pathinfo( $doc['filename'] ?: 'bieu-mau', PATHINFO_FILENAME ) ?: 'bieu-mau';
		}
		$entry_name = $sanitized_base . '.' . $ext;

		// Tránh trùng tên file bên trong ZIP
		$counter = 1;
		while ( isset( $used_names[ strtolower( $entry_name ) ] ) ) {
			$entry_name = $sanitized_base . '-' . $counter . '.' . $ext;
			$counter++;
		}
		$used_names[ strtolower( $entry_name ) ] = true;

		// 1. Nếu có local_path từ attachment/wp-content
		if ( ! empty( $local_path ) && file_exists( $local_path ) ) {
			if ( $zip->addFile( $local_path, $entry_name ) ) {
				$added_count++;
				continue;
			}
		}

		// 2. Thử dò đường dẫn cục bộ nếu URL thuộc site này
		$check_path = '';
		if ( str_starts_with( $doc_url, $upload_dir['baseurl'] ) ) {
			$rel       = substr( $doc_url, strlen( $upload_dir['baseurl'] ) );
			$candidate = $upload_dir['basedir'] . $rel;
			if ( file_exists( $candidate ) ) {
				$check_path = $candidate;
			}
		} elseif ( function_exists( 'content_url' ) && defined( 'WP_CONTENT_DIR' ) && str_starts_with( $doc_url, content_url() ) ) {
			$rel       = substr( $doc_url, strlen( content_url() ) );
			$candidate = WP_CONTENT_DIR . $rel;
			if ( file_exists( $candidate ) ) {
				$check_path = $candidate;
			}
		}

		if ( ! empty( $check_path ) && file_exists( $check_path ) ) {
			if ( $zip->addFile( $check_path, $entry_name ) ) {
				$added_count++;
				continue;
			}
		}

		// 3. Nếu là file remote bên ngoài
		if ( function_exists( 'wp_remote_get' ) ) {
			$response = wp_remote_get( $doc_url, [ 'timeout' => 5, 'sslverify' => false ] );
			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
				$body = wp_remote_retrieve_body( $response );
				if ( ! empty( $body ) ) {
					$zip->addFromString( $entry_name, $body );
					$added_count++;
					continue;
				}
			}
		}

		// 4. Fallback an toàn (mock/offline URLs): tạo text stub
		$stub_content = "Biểu mẫu: " . $doc_label . "\nĐường dẫn gốc: " . $doc_url . "\nNgày khởi tạo: " . current_time( 'mysql' );
		$zip->addFromString( $sanitized_base . '.txt', $stub_content );
		$added_count++;
	}

	$zip->close();

	if ( $added_count > 0 && file_exists( $zip_path ) && filesize( $zip_path ) > 0 ) {
		return $zip_url;
	}

	return '';
}

/**
 * Lấy toàn bộ gói hồ sơ & bộ biểu mẫu tuyển sinh của chương trình theo cơ chế kế thừa phân tầng (Cách 3):
 * 1. Chương trình (Override riêng tại bài viết Program: bundle_file, admission_documents, admission_form_file)
 * 2. Hệ đào tạo của Trường (admission_forms_by_type: bundle_file, documents, file)
 * 3. Mặc định của Trường (default_bundle_file, default_admission_documents, default_admission_form)
 * 4. File tĩnh quy ước (/uploads/forms/phieu-dang-ky-{code}-2026.pdf)
 *
 * @param int $program_id
 * @return array
 */
function ltdh_get_program_admission_package( int $program_id ): array {
	$empty_package = [
		'has_package'      => false,
		'source'           => '',
		'source_label'     => '',
		'drive_url'        => '',
		'bundle_url'       => '',
		'bundle_title'     => '',
		'bundle_ext'       => '',
		'drive_folder_url' => '',
		'school_drive_url' => '',
		'school_title'     => '',
		'documents'        => [],
		'is_single'        => true,
		'primary_url'      => '',
		'primary_title'    => '',
	];

	if ( ! $program_id ) {
		return $empty_package;
	}

	$school_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $program_id ) : 0;
	$drive_url = '';
	$source    = '';
	$source_label = '';

	// ----------------------------------------------------
	// CẤP 1: KIỂM TRA LINK DRIVE RIÊNG CỦA CHƯƠNG TRÌNH (PROGRAM OVERRIDE)
	// ----------------------------------------------------
	$prog_drive = ltdh_get_field_or_meta( 'program_admission_drive_url', $program_id );
	if ( empty( $prog_drive ) ) {
		$prog_drive = ltdh_get_field_or_meta( 'admission_drive_folder_url', $program_id );
	}
	if ( ! empty( $prog_drive ) ) {
		$drive_url     = trim( (string) $prog_drive );
		$source        = 'program';
		$source_label  = 'Mẫu riêng theo ngành';
	}

	// ----------------------------------------------------
	// CẤP 2: KIỂM TRA LINK DRIVE THEO HỆ ĐÀO TẠO CỦA TRƯỜNG (TRAINING TYPE)
	// ----------------------------------------------------
	if ( empty( $drive_url ) && $school_id > 0 ) {
		$training_terms = wp_get_post_terms( $program_id, 'training_type' );
		if ( ! is_wp_error( $training_terms ) && ! empty( $training_terms ) ) {
			$by_type_rows = ltdh_get_field_or_meta( 'admission_forms_by_type', $school_id );
			if ( ! empty( $by_type_rows ) && is_array( $by_type_rows ) ) {
				$prog_term_ids   = wp_list_pluck( $training_terms, 'term_id' );
				$prog_term_slugs = wp_list_pluck( $training_terms, 'slug' );

				foreach ( $by_type_rows as $row ) {
					$row_type = $row['training_type'] ?? null;
					$row_type_id   = 0;
					$row_type_slug = '';
					if ( is_numeric( $row_type ) ) {
						$row_type_id = (int) $row_type;
					} elseif ( is_array( $row_type ) ) {
						$row_type_id   = (int) ( $row_type['term_id'] ?? $row_type['ID'] ?? 0 );
						$row_type_slug = (string) ( $row_type['slug'] ?? '' );
					} elseif ( is_object( $row_type ) ) {
						$row_type_id   = (int) ( $row_type->term_id ?? $row_type->ID ?? 0 );
						$row_type_slug = (string) ( $row_type->slug ?? '' );
					} elseif ( is_string( $row_type ) ) {
						$row_type_slug = $row_type;
					}

					$is_matched = false;
					if ( $row_type_id > 0 && in_array( $row_type_id, $prog_term_ids, true ) ) {
						$is_matched = true;
					} elseif ( ! empty( $row_type_slug ) && in_array( $row_type_slug, $prog_term_slugs, true ) ) {
						$is_matched = true;
					}

					if ( $is_matched && ! empty( $row['drive_folder_url'] ) ) {
						$drive_url     = trim( (string) $row['drive_folder_url'] );
						$term_name     = $training_terms[0]->name ?? 'Hệ đào tạo';
						$source        = 'school_type';
						$source_label  = 'Mẫu theo hệ: ' . $term_name;
						break;
					}
				}
			}
		}
	}

	// ----------------------------------------------------
	// CẤP 3: KIỂM TRA LINK DRIVE CHUNG CỦA TRƯỜNG (SCHOOL DEFAULT)
	// ----------------------------------------------------
	$school_drive = '';
	if ( $school_id > 0 ) {
		$school_drive_raw = ltdh_get_field_or_meta( 'admission_drive_folder_url', $school_id );
		if ( ! empty( $school_drive_raw ) ) {
			$school_drive = trim( (string) $school_drive_raw );
		}
	}

	if ( empty( $drive_url ) && ! empty( $school_drive ) ) {
		$drive_url     = $school_drive;
		$source        = 'school_default';
		$source_label  = 'Mẫu chuẩn của trường';
	}

	// ----------------------------------------------------
	// CẤP 4: FALLBACK CHO DỮ LIỆU FILE CŨ (NẾU CHƯA NHẬP DRIVE LINK)
	// ----------------------------------------------------
	$fallback_url = '';
	if ( empty( $drive_url ) ) {
		// Program legacy single file
		$prog_single = ltdh_get_field_or_meta( 'admission_form_file', $program_id );
		$s_info      = ltdh_normalize_admission_file_info( $prog_single );
		if ( ! empty( $s_info['url'] ) ) {
			$fallback_url = $s_info['url'];
			$source       = 'program';
			$source_label = 'Mẫu riêng theo ngành';
		} elseif ( $school_id > 0 ) {
			// School bundle or single default
			$def_bundle = ltdh_get_field_or_meta( 'default_bundle_file', $school_id );
			$b_info     = ltdh_normalize_admission_file_info( $def_bundle );
			if ( ! empty( $b_info['url'] ) ) {
				$fallback_url = $b_info['url'];
				$source       = 'school_default';
				$source_label = 'Mẫu chuẩn của trường';
			} else {
				$def_single = ltdh_get_field_or_meta( 'default_admission_form', $school_id );
				$df_info    = ltdh_normalize_admission_file_info( $def_single );
				if ( ! empty( $df_info['url'] ) ) {
					$fallback_url = $df_info['url'];
					$source       = 'school_default';
					$source_label = 'Mẫu chuẩn của trường';
				}
			}
		}
	}

	$target_url = ! empty( $drive_url ) ? $drive_url : $fallback_url;
	if ( empty( $target_url ) ) {
		return $empty_package;
	}

	$is_drive = ! empty( $drive_url ) || ( function_exists( 'ltdh_is_google_drive_url' ) && ltdh_is_google_drive_url( $target_url ) );
	$title    = $is_drive ? 'Mở trọn bộ hồ sơ trên Google Drive' : ( 'Phiếu tuyển sinh: ' . get_the_title( $program_id ) );

	return [
		'has_package'      => true,
		'source'           => $source,
		'source_label'     => $source_label,
		'drive_url'        => $is_drive ? $target_url : '',
		'bundle_url'       => $target_url,
		'bundle_title'     => $title,
		'bundle_ext'       => $is_drive ? 'drive' : 'pdf',
		'drive_folder_url' => $is_drive ? $target_url : '',
		'school_drive_url' => $school_drive,
		'school_title'     => $school_id > 0 ? get_the_title( $school_id ) : '',
		'documents'        => [
			[
				'label'       => $title,
				'url'         => $target_url,
				'is_drive'    => $is_drive,
				'ext'         => $is_drive ? 'drive' : 'pdf',
				'badge'       => 'required',
				'badge_label' => 'Bắt buộc',
			],
		],
		'is_single'        => true,
		'primary_url'      => $target_url,
		'primary_title'    => $title,
	];
}

/**
 * Lấy dữ liệu phiếu tuyển sinh chính (Backward compatibility wrapper)
 *
 * @param int $program_id
 * @return array|null
 */
function ltdh_get_program_admission_form_data( int $program_id ): ?array {
	$pkg = ltdh_get_program_admission_package( $program_id );
	if ( empty( $pkg['has_package'] ) || empty( $pkg['primary_url'] ) ) {
		return null;
	}

	return [
		'url'          => $pkg['primary_url'],
		'title'        => $pkg['primary_title'] ?: ( 'Phiếu tuyển sinh: ' . get_the_title( $program_id ) ),
		'source'       => $pkg['source'],
		'source_label' => $pkg['source_label'],
		'filename'     => basename( parse_url( $pkg['primary_url'], PHP_URL_PATH ) ?: '' ),
	];
}

/**
 * Lấy nhanh URL phiếu tuyển sinh của chương trình
 *
 * @param int $program_id
 * @return string
 */
function ltdh_get_program_admission_form_url( int $program_id ): string {
	$data = ltdh_get_program_admission_form_data( $program_id );
	return $data['url'] ?? '';
}

/**
 * Tìm kiếm biểu mẫu miễn giảm môn học phù hợp với tên môn học
 *
 * @param string $subject
 * @param array $documents
 * @return array|null
 */
function ltdh_find_matching_exemption_doc( string $subject, array $documents ): ?array {
	if ( empty( $documents ) || empty( $subject ) ) {
		return null;
	}

	$sub_clean = mb_strtolower( remove_accents( $subject ) );

	$topic_keywords = [
		'quoc phong' => [ 'quoc phong', 'an ninh', 'gdqp', 'quan su' ],
		'an ninh'    => [ 'quoc phong', 'an ninh', 'gdqp', 'quan su' ],
		'gdqp'       => [ 'quoc phong', 'an ninh', 'gdqp', 'quan su' ],
		'tieng anh'  => [ 'tieng anh', 'anh van', 'english', 'ngoai ngu' ],
		'ngoai ngu'  => [ 'tieng anh', 'anh van', 'english', 'ngoai ngu' ],
		'tin hoc'    => [ 'tin hoc', 'cntt', 'ic3', 'mos' ],
		'triet'      => [ 'triet hoc', 'mac lenin', 'ly luan' ],
	];

	$active_keywords = [];
	foreach ( $topic_keywords as $key => $kws ) {
		foreach ( $kws as $kw ) {
			if ( str_contains( $sub_clean, $kw ) ) {
				$active_keywords = array_merge( $active_keywords, $kws );
				break;
			}
		}
	}

	// 1. Kiểm tra đối chiếu theo chủ đề nhận diện được
	if ( ! empty( $active_keywords ) ) {
		foreach ( $documents as $doc ) {
			$doc_clean = mb_strtolower( remove_accents( $doc['label'] ?? '' ) );
			foreach ( $active_keywords as $kw ) {
				if ( str_contains( $doc_clean, $kw ) ) {
					return $doc;
				}
			}
		}
	}

	// 2. Đối chiếu từ khóa ngữ nghĩa nếu tài liệu có gắn badge 'exemption'
	$words = preg_split( '/[\s,\.-]+/', $sub_clean );
	$meaningful = array_filter( $words, fn( $w ) => mb_strlen( $w ) >= 4 && ! in_array( $w, [ 'mon', 'hoc', 'phan', 'khoa', 'tin', 'chi' ], true ) );
	if ( ! empty( $meaningful ) ) {
		foreach ( $documents as $doc ) {
			if ( ( $doc['badge'] ?? '' ) !== 'exemption' ) {
				continue;
			}
			$doc_clean = mb_strtolower( remove_accents( $doc['label'] ?? '' ) );
			foreach ( $meaningful as $w ) {
				if ( str_contains( $doc_clean, $w ) ) {
					return $doc;
				}
			}
		}
	}

	return null;
}

/**
 * Lấy thông tin Khung chương trình đào tạo của một Ngành học
 * (Hỗ trợ phân biệt giữa Ngành chung và Ngành dành cho Trường cụ thể)
 *
 * @param int   $major_id
 * @param mixed $school_param ID, slug, school_code hoặc post object của trường
 * @return array|null
 */
function ltdh_get_major_curriculum_info( int $major_id, $school_param = null ): ?array {
	if ( ! $major_id ) {
		return null;
	}

	$target_school_id = 0;
	if ( ! empty( $school_param ) ) {
		if ( is_numeric( $school_param ) ) {
			$target_school_id = (int) $school_param;
		} elseif ( is_object( $school_param ) && ! empty( $school_param->ID ) ) {
			$target_school_id = (int) $school_param->ID;
		} elseif ( is_string( $school_param ) ) {
			$found_school = get_page_by_path( sanitize_title( $school_param ), OBJECT, 'school' );
			if ( $found_school ) {
				$target_school_id = $found_school->ID;
			} else {
				$by_code = get_posts( [
					'post_type'   => 'school',
					'post_status' => 'publish',
					'numberposts' => 1,
					'meta_query'  => [
						[
							'key'     => 'school_code',
							'value'   => sanitize_text_field( $school_param ),
							'compare' => '=',
						],
					],
				] );
				if ( ! empty( $by_code ) ) {
					$target_school_id = $by_code[0]->ID;
				}
			}
		}
	}

	$raw_file = null;
	$title    = '';
	$source   = '';

	// 1. Nếu có ngữ cảnh Trường cụ thể, tìm trong danh sách khung chương trình theo trường
	if ( $target_school_id > 0 ) {
		$school_rows = ltdh_get_field_or_meta( 'major_school_curriculums', $major_id );
		if ( ! empty( $school_rows ) && is_array( $school_rows ) ) {
			foreach ( $school_rows as $row ) {
				$r_school = $row['school'] ?? 0;
				$r_sid    = 0;
				if ( is_numeric( $r_school ) ) {
					$r_sid = (int) $r_school;
				} elseif ( is_array( $r_school ) && ! empty( $r_school['ID'] ) ) {
					$r_sid = (int) $r_school['ID'];
				} elseif ( is_object( $r_school ) && ! empty( $r_school->ID ) ) {
					$r_sid = (int) $r_school->ID;
				}

				if ( $r_sid === $target_school_id && ! empty( $row['curriculum_file'] ) ) {
					$raw_file = $row['curriculum_file'];
					$title    = ! empty( $row['notes'] ) ? (string) $row['notes'] : ( 'Khung chương trình: ' . get_the_title( $major_id ) . ' - ' . get_the_title( $target_school_id ) );
					$source   = 'major_school';
					break;
				}
			}
		}
	}

	// 2. Nếu không tìm thấy khung riêng của trường hoặc đang xem Ngành chung: Lấy khung mặc định của ngành
	if ( empty( $raw_file ) ) {
		$def_file = ltdh_get_field_or_meta( 'major_default_curriculum', $major_id );
		if ( ! empty( $def_file ) ) {
			$raw_file = $def_file;
			$title    = 'Khung chương trình chuẩn: ' . get_the_title( $major_id );
			$source   = 'major_default';
		}
	}

	if ( empty( $raw_file ) ) {
		return null;
	}

	$f_info = ltdh_normalize_admission_file_info( $raw_file );
	if ( empty( $f_info['url'] ) ) {
		return null;
	}

	$ext  = $f_info['ext'] ?: 'pdf';
	$type = in_array( $ext, [ 'png', 'jpg', 'jpeg', 'webp', 'gif' ], true ) ? 'image' : 'pdf';

	return [
		'url'      => $f_info['url'],
		'type'     => $type,
		'title'    => $title ?: ( 'Khung chương trình: ' . get_the_title( $major_id ) ),
		'filename' => $f_info['filename'],
		'ext'      => $ext,
		'source'   => $source,
	];
}

/**
 * Lấy thông tin Khung chương trình đào tạo của Chương trình tuyển sinh
 * Kế thừa tập trung từ Ngành học (Major):
 * 1. Khung chương trình của Ngành dành riêng cho Trường đó (và Hệ đào tạo đó nếu có)
 * 2. Khung chương trình chuẩn mặc định của Ngành chung
 * Nếu Ngành chưa cấu hình khung chương trình nào, hàm trả về null (không hiển thị ra ngoài frontend).
 *
 * @param int $program_id
 * @return array|null
 */
function ltdh_get_program_curriculum_info( int $program_id ): ?array {
	if ( ! $program_id ) {
		return null;
	}

	$school_id     = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $program_id ) : 0;
	$major_rel_key = defined( 'LTDH_META_MAJOR_REL' ) ? LTDH_META_MAJOR_REL : 'major_relationship';
	$major_raw     = get_post_meta( $program_id, $major_rel_key, true );
	if ( empty( $major_raw ) && function_exists( 'get_field' ) ) {
		$major_raw = get_field( $major_rel_key, $program_id );
	}
	$major_id = is_numeric( $major_raw ) ? (int) $major_raw : ( is_array( $major_raw ) && ! empty( $major_raw['ID'] ) ? (int) $major_raw['ID'] : ( is_object( $major_raw ) && ! empty( $major_raw->ID ) ? (int) $major_raw->ID : 0 ) );

	$raw_file = null;
	$title    = '';
	$source   = '';

	// ----------------------------------------------------
	// CẤP 1 & 2: KẾ THỪA TỪ BÀI VIẾT NGÀNH (MAJOR)
	// ----------------------------------------------------
	if ( $major_id > 0 ) {
		// Tìm trong danh sách khung chương trình theo trường
		$school_curriculums = ltdh_get_field_or_meta( 'major_school_curriculums', $major_id );
		if ( ! empty( $school_curriculums ) && is_array( $school_curriculums ) && $school_id > 0 ) {
			$training_terms = wp_get_post_terms( $program_id, 'training_type' );
			$prog_term_ids  = ! is_wp_error( $training_terms ) ? wp_list_pluck( $training_terms, 'term_id' ) : [];

			$fallback_file = null;
			$fallback_note = '';

			foreach ( $school_curriculums as $row ) {
				$r_school = $row['school'] ?? 0;
				$r_sid    = 0;
				if ( is_numeric( $r_school ) ) {
					$r_sid = (int) $r_school;
				} elseif ( is_array( $r_school ) && ! empty( $r_school['ID'] ) ) {
					$r_sid = (int) $r_school['ID'];
				} elseif ( is_object( $r_school ) && ! empty( $r_school->ID ) ) {
					$r_sid = (int) $r_school->ID;
				}

				if ( $r_sid === $school_id && ! empty( $row['curriculum_file'] ) ) {
					$row_type    = $row['training_type'] ?? null;
					$row_type_id = is_numeric( $row_type ) ? (int) $row_type : ( is_array( $row_type ) ? (int) ( $row_type['term_id'] ?? 0 ) : 0 );

					// Khớp chính xác cả Hệ đào tạo
					if ( $row_type_id > 0 && in_array( $row_type_id, $prog_term_ids, true ) ) {
						$raw_file = $row['curriculum_file'];
						$title    = ! empty( $row['notes'] ) ? (string) $row['notes'] : '';
						$source   = 'major_school_type';
						break;
					}

					// Khung chung cho mọi hệ của trường đó
					if ( empty( $row_type_id ) && empty( $fallback_file ) ) {
						$fallback_file = $row['curriculum_file'];
						$fallback_note = ! empty( $row['notes'] ) ? (string) $row['notes'] : '';
					}
				}
			}

			if ( empty( $raw_file ) && ! empty( $fallback_file ) ) {
				$raw_file = $fallback_file;
				$title    = $fallback_note;
				$source   = 'major_school';
			}
		}

		// Nếu trường chưa có khung riêng, lấy khung chuẩn mặc định của ngành chung
		if ( empty( $raw_file ) ) {
			$def_file = ltdh_get_field_or_meta( 'major_default_curriculum', $major_id );
			if ( ! empty( $def_file ) ) {
				$raw_file = $def_file;
				$source   = 'major_default';
			}
		}
	}

	if ( empty( $raw_file ) ) {
		return null;
	}

	$f_info = ltdh_normalize_admission_file_info( $raw_file );
	if ( empty( $f_info['url'] ) ) {
		return null;
	}

	$ext  = $f_info['ext'] ?: 'pdf';
	$type = in_array( $ext, [ 'png', 'jpg', 'jpeg', 'webp', 'gif' ], true ) ? 'image' : 'pdf';

	return [
		'url'      => $f_info['url'],
		'type'     => $type,
		'title'    => $title ?: ( 'Khung chương trình: ' . get_the_title( $program_id ) ),
		'filename' => $f_info['filename'],
		'ext'      => $ext,
		'source'   => $source,
	];
}


/**
 * Lấy danh sách toàn bộ các phiếu tuyển sinh đang có của một trường
 * (gồm phiếu mặc định chung, bundle file và các phiếu theo hệ đào tạo)
 *
 * @param int $school_id
 * @return array
 */
function ltdh_get_school_admission_forms( int $school_id ): array {
	if ( ! $school_id ) {
		return [];
	}

	$forms = [];

	// 1. Link Google Drive chung của trường
	$school_drive = ltdh_get_field_or_meta( 'admission_drive_folder_url', $school_id );
	if ( ! empty( $school_drive ) ) {
		$forms[] = [
			'type'     => 'drive_folder',
			'label'    => 'Thư mục Google Drive hồ sơ trường',
			'url'      => trim( (string) $school_drive ),
			'title'    => 'Thư mục Google Drive: ' . get_the_title( $school_id ),
			'filename' => 'Google Drive',
			'is_drive' => true,
		];
	}

	// 2. Link Google Drive theo từng hệ đào tạo (nếu có riêng)
	$by_type_rows = ltdh_get_field_or_meta( 'admission_forms_by_type', $school_id );
	if ( ! empty( $by_type_rows ) && is_array( $by_type_rows ) ) {
		foreach ( $by_type_rows as $row ) {
			if ( empty( $row['drive_folder_url'] ) ) {
				continue;
			}
			$row_type  = $row['training_type'] ?? null;
			$term_name = 'Hệ đào tạo';
			if ( is_numeric( $row_type ) ) {
				$term = get_term( (int) $row_type, 'training_type' );
				if ( $term && ! is_wp_error( $term ) ) {
					$term_name = $term->name;
				}
			} elseif ( is_array( $row_type ) && ! empty( $row_type['name'] ) ) {
				$term_name = $row_type['name'];
			} elseif ( is_object( $row_type ) && ! empty( $row_type->name ) ) {
				$term_name = $row_type->name;
			}

			$label = ! empty( $row['notes'] ) ? (string) $row['notes'] : ( 'Hồ sơ Google Drive hệ ' . $term_name );
			$forms[] = [
				'type'     => 'type_drive_folder',
				'label'    => $label,
				'url'      => trim( (string) $row['drive_folder_url'] ),
				'title'    => $label . ' - ' . get_the_title( $school_id ),
				'filename' => 'Google Drive',
				'is_drive' => true,
			];
		}
	}

	// 3. Fallback cho dữ liệu file cũ nếu trường chưa kịp nhập link Drive
	if ( empty( $forms ) ) {
		$def_bundle = ltdh_get_field_or_meta( 'default_bundle_file', $school_id );
		$b_info     = ltdh_normalize_admission_file_info( $def_bundle );
		if ( ! empty( $b_info['url'] ) ) {
			$forms[] = [
				'type'     => 'bundle',
				'label'    => 'Trọn bộ hồ sơ tuyển sinh (ZIP)',
				'url'      => $b_info['url'],
				'title'    => 'Trọn bộ hồ sơ: ' . get_the_title( $school_id ),
				'filename' => $b_info['filename'],
				'is_drive' => false,
			];
		} else {
			$def_single = ltdh_get_field_or_meta( 'default_admission_form', $school_id );
			$s_info     = ltdh_normalize_admission_file_info( $def_single );
			if ( ! empty( $s_info['url'] ) ) {
				$forms[] = [
					'type'     => 'default',
					'label'    => 'Phiếu tuyển sinh chung toàn trường',
					'url'      => $s_info['url'],
					'title'    => 'Phiếu tuyển sinh: ' . get_the_title( $school_id ),
					'filename' => $s_info['filename'],
					'is_drive' => false,
				];
			}
		}
	}

	return $forms;
}

// ----------------------------------------------------
// 2. Form Shortcode Helper
// ----------------------------------------------------

// Disable Contact Form 7 automatic paragraph formatting for pixel-perfect Tailwind grid layouts
add_filter( 'wpcf7_autop_or_not', '__return_false' );

// Bridge contextual hidden fields (school_id, program_id, major_id, referral_source, etc.) into CF7 hidden container
add_filter( 'wpcf7_form_hidden_fields', function( $hidden_fields ) {
	if ( ! empty( $GLOBALS['ltdh_cf7_context_hidden_fields'] ) && is_array( $GLOBALS['ltdh_cf7_context_hidden_fields'] ) ) {
		$hidden_fields = array_merge( $hidden_fields, $GLOBALS['ltdh_cf7_context_hidden_fields'] );
	}
	return $hidden_fields;
} );

/**
 * Get the CF7 shortcode for a given context.
 *
 * Contexts: 'consultation', 'contact', 'program'.
 * Falls back to default options, centralized defaults, or auto-detects active CF7 form.
 *
 * @param string $context
 * @return string Raw shortcode string, or empty if no form configured.
 */
function ltdh_get_form_shortcode( string $context = 'consultation' ): string {
	$option_map = [
		'consultation' => 'cf7_consultation_form_id',
		'contact'      => 'cf7_contact_form_id',
		'program'      => 'cf7_program_form_id',
		'evaluation'   => 'cf7_evaluation_form_id',
		'eligibility'  => 'cf7_evaluation_form_id',
	];

	$form_id    = '';
	$field_name = $option_map[ $context ] ?? '';

	// 1. Check ACF Options Page
	if ( $field_name && function_exists( 'get_field' ) ) {
		$form_id = get_field( $field_name, 'options' );
	}
	// Fallback to direct get_option if ACF function is unavailable
	if ( empty( $form_id ) && $field_name ) {
		$form_id = get_option( 'options_' . $field_name ) ?: get_option( $field_name );
	}

	// 2. Check defaults configuration
	if ( empty( $form_id ) && function_exists( 'ltdh_default' ) ) {
		$form_id = ltdh_default( 'forms', $context . '_form_id' );
	}

	// 3. Auto-detect published CF7 form by slug/title if still empty
	if ( empty( $form_id ) && post_type_exists( 'wpcf7_contact_form' ) ) {
		$slugs = [
			'contact'      => [ 'form-lien-he', 'lien-he', 'contact-form-1' ],
			'consultation' => [ 'form-tu-van', 'tu-van', 'consultation-form' ],
			'program'      => [ 'form-tu-van', 'form-chuong-trinh', 'consultation-form' ],
			'evaluation'   => [ 'form-tham-dinh-ho-so', 'form-tham-dinh', 'tham-dinh', 'evaluation-form' ],
			'eligibility'  => [ 'form-tham-dinh-ho-so', 'form-tham-dinh', 'tham-dinh', 'evaluation-form' ],
		];
		$candidates = $slugs[ $context ] ?? [ 'form-tu-van', 'form-lien-he' ];
		foreach ( $candidates as $candidate_slug ) {
			$found = get_page_by_path( $candidate_slug, OBJECT, 'wpcf7_contact_form' );
			if ( $found && 'publish' === $found->post_status ) {
				$form_id = $found->ID;
				break;
			}
		}
	}

	// 4. Fallback to cf7_default_form_id if not evaluation/contact
	if ( empty( $form_id ) && ! in_array( $context, [ 'evaluation', 'eligibility' ], true ) ) {
		if ( function_exists( 'get_field' ) ) {
			$form_id = get_field( 'cf7_default_form_id', 'options' );
		}
		if ( empty( $form_id ) ) {
			$form_id = get_option( 'options_cf7_default_form_id' ) ?: get_option( 'cf7_default_form_id' );
		}
	}

	if ( ! empty( $form_id ) ) {
		$form_post = get_post( $form_id );
		if ( $form_post && 'wpcf7_contact_form' === $form_post->post_type && 'publish' === $form_post->post_status ) {
			return do_shortcode( '[contact-form-7 id="' . esc_attr( $form_id ) . '"]' );
		}
	}

	return '';
}

/**
 * Render an evaluation / eligibility form with automatic fallback.
 *
 * @param array $context_hidden_fields Optional hidden fields to inject.
 */
function ltdh_render_evaluation_form( array $context_hidden_fields = [] ): void {
	$GLOBALS['ltdh_cf7_context_hidden_fields'] = $context_hidden_fields;

	$shortcode = ltdh_get_form_shortcode( 'evaluation' );
	if ( ! empty( $shortcode ) && strpos( $shortcode, 'wpcf7-contact-form-not-found' ) === false ) {
		echo $shortcode; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];
		return;
	}
	$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];
	ltdh_render_native_form( 'evaluation', $context_hidden_fields );
}

/**
 * Render a consultation form with automatic fallback to native HTML form.
 *
 * @param array $context_hidden_fields Optional hidden fields to inject.
 */
function ltdh_render_consultation_form( array $context_hidden_fields = [] ): void {
	$GLOBALS['ltdh_cf7_context_hidden_fields'] = $context_hidden_fields;

	$shortcode = ltdh_get_form_shortcode( 'consultation' );
	if ( ! empty( $shortcode ) ) {
		echo $shortcode; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];
		return;
	}
	$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];
	ltdh_render_native_form( 'consultation', $context_hidden_fields );
}

/**
 * Render a contact form with automatic fallback.
 *
 * @param array $context_hidden_fields Optional hidden fields to inject.
 */
function ltdh_render_contact_form( array $context_hidden_fields = [] ): void {
	$GLOBALS['ltdh_cf7_context_hidden_fields'] = $context_hidden_fields;

	$shortcode = ltdh_get_form_shortcode( 'contact' );
	if ( ! empty( $shortcode ) ) {
		echo $shortcode; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];
		return;
	}
	$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];
	ltdh_render_native_form( 'contact', $context_hidden_fields );
}

/**
 * Render a native HTML form fallback.
 *
 * @param string $type 'consultation' or 'contact'.
 * @param array  $hidden_fields
 */
function ltdh_render_native_form(string $type = 'consultation', array $hidden_fields = []): void {
	static $form_instance = 0;
	$form_instance++;
	$prefix = "ltdh-lead-{$type}-{$form_instance}";
?>
	<form action="" method="POST" class="space-y-3 lg:space-y-4">
		<?php wp_nonce_field( 'ltdh_native_lead_submit_action', 'ltdh_native_lead_nonce' ); ?>
		<?php if ( isset( $_GET['submit_success'] ) && '1' === $_GET['submit_success'] ) : ?>
			<div class="p-3 lg:p-4 mb-3 lg:mb-4 text-xs lg:text-sm text-green-800 rounded-xl lg:rounded-lg bg-green-50 border border-green-200" role="alert">
				<span class="font-bold">Gửi thông tin thành công!</span> Đội ngũ tư vấn tuyển sinh sẽ liên hệ với bạn trong thời gian sớm nhất.
			</div>
		<?php endif; ?>

		<?php foreach ($hidden_fields as $name => $value) : ?>
			<input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>">
		<?php endforeach; ?>

		<div class="hidden" style="display:none !important;" aria-hidden="true">
			<label for="<?php echo esc_attr( $prefix . '-hp' ); ?>">Website URL</label>
			<input type="text" name="hp_website" id="<?php echo esc_attr( $prefix . '-hp' ); ?>" tabindex="-1" autocomplete="off" value="">
		</div>

		<div>
			<label for="<?php echo esc_attr( $prefix . '-name' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600 mb-1"><?php esc_html_e('Họ và tên *', 'ltdh'); ?></label>
			<input type="text" id="<?php echo esc_attr( $prefix . '-name' ); ?>" name="your-name" required autocomplete="name" aria-required="true" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2.5 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all" placeholder="<?php esc_attr_e('Họ và tên của bạn', 'ltdh'); ?>">
		</div>
		<div>
			<label for="<?php echo esc_attr( $prefix . '-phone' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600 mb-1"><?php esc_html_e('Số điện thoại *', 'ltdh'); ?></label>
			<input type="tel" id="<?php echo esc_attr( $prefix . '-phone' ); ?>" name="your-phone" required autocomplete="tel" aria-required="true" pattern="^(0|\+84)(3|5|7|8|9)[0-9]{8}$" title="Vui lòng nhập số điện thoại Việt Nam hợp lệ (10 chữ số)" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2.5 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all" placeholder="<?php esc_attr_e('Số điện thoại liên hệ (10 số)'); ?>">
		</div>

		<?php if ( 'consultation' === $type || 'contact' === $type ) : ?>
			<div>
				<div class="flex items-center justify-between mb-1">
					<label for="<?php echo esc_attr( $prefix . '-email' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600"><?php esc_html_e('Địa chỉ Email', 'ltdh'); ?></label>
					<span class="text-[10px] text-slate-400"><?php esc_html_e('Nhận lộ trình & xác nhận', 'ltdh'); ?></span>
				</div>
				<input type="email" id="<?php echo esc_attr( $prefix . '-email' ); ?>" name="your-email" autocomplete="email" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2.5 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all" placeholder="Ví dụ: hoten@gmail.com">
			</div>
		<?php endif; ?>

		<?php if ( 'evaluation' === $type ) : ?>
			<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
				<div>
					<label for="<?php echo esc_attr( $prefix . '-email' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600 mb-1"><?php esc_html_e('Email nhận kết quả', 'ltdh'); ?></label>
					<input type="email" id="<?php echo esc_attr( $prefix . '-email' ); ?>" name="your-email" autocomplete="email" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2.5 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all" placeholder="Ví dụ: name@example.com">
				</div>
				<div>
					<label for="<?php echo esc_attr( $prefix . '-school' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600 mb-1"><?php esc_html_e('Trường đã tốt nghiệp', 'ltdh'); ?></label>
					<input type="text" id="<?php echo esc_attr( $prefix . '-school' ); ?>" name="previous_school" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2.5 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all" placeholder="Ví dụ: CĐ Kinh Tế...">
				</div>
				<div>
					<label for="<?php echo esc_attr( $prefix . '-grad' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600 mb-1"><?php esc_html_e('Năm tốt nghiệp', 'ltdh'); ?></label>
					<select id="<?php echo esc_attr( $prefix . '-grad' ); ?>" name="graduation" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2.5 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all bg-white">
						<option value="">-- Chọn năm --</option>
						<?php
						$current_y = (int) date( 'Y' );
						for ( $y = $current_y; $y >= $current_y - 25; $y-- ) :
						?>
							<option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
						<?php endfor; ?>
					</select>
				</div>
			</div>
		<?php endif; ?>

		<div>
			<div class="flex items-center justify-between mb-1">
				<label for="<?php echo esc_attr( $prefix . '-message' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600"><?php esc_html_e('Nội dung cần tư vấn', 'ltdh'); ?></label>
				<span class="text-[10px] text-slate-400 lg:hidden">Không bắt buộc</span>
			</div>
			<textarea id="<?php echo esc_attr( $prefix . '-message' ); ?>" name="your-message" rows="2" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all lg:h-24 resize-none" placeholder="<?php esc_attr_e('Nhập câu hỏi hoặc yêu cầu cụ thể...', 'ltdh'); ?>"></textarea>
		</div>

		<?php if ( 'contact' !== $type ) : ?>
			<div class="pt-0.5">
				<label for="<?php echo esc_attr( $prefix . '-group' ); ?>" class="flex items-start gap-2.5 p-2.5 sm:p-3 rounded-xl bg-amber-50/80 border border-amber-200/90 cursor-pointer hover:bg-amber-100/60 transition-colors select-none">
					<input type="checkbox" id="<?php echo esc_attr( $prefix . '-group' ); ?>" name="is_group_reg" value="1" class="mt-0.5 w-4 h-4 rounded border-amber-300 text-brand-primary focus:ring-brand-primary">
					<span class="text-xs sm:text-sm text-slate-700 leading-snug">
						🎁 <strong class="font-bold text-amber-900">Đăng ký theo nhóm:</strong> Tư vấn cho tôi suất học bổng đồng hành (khi cùng bạn bè tìm hiểu từ 2 người trở lên)
					</span>
				</label>
			</div>
		<?php endif; ?>

		<?php if ( function_exists( 'ltdh_render_recaptcha_field' ) ) : ?>
			<?php ltdh_render_recaptcha_field( 'native_lead' ); ?>
		<?php endif; ?>

		<button type="submit" class="w-full bg-[#0071E3] hover:bg-[#0077ED] text-white py-3 lg:py-3.5 rounded-full text-sm font-semibold tracking-tight shadow-none hover:shadow-xs active:scale-[0.98] transition-all mt-1 lg:mt-2 min-h-[44px] flex items-center justify-center gap-2 cursor-pointer">
			<span><?php echo 'contact' === $type ? esc_html__('Gửi Liên Hệ') : ( 'evaluation' === $type ? esc_html__('GỬI YÊU CẦU ĐỐI CHIẾU HỒ SƠ & TƯ VẤN') : esc_html__('Gửi Thông Tin Ngay') ); ?></span>
		</button>

		<div class="flex flex-col sm:flex-row items-center justify-center gap-1.5 sm:gap-3 text-[11px] text-slate-400 pt-1 text-center">
			<span class="inline-flex items-center gap-1 font-medium">
				<svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
				Bảo mật theo Nghị định 13/2023/NĐ-CP
			</span>
			<span class="hidden sm:inline text-slate-300">•</span>
			<span class="inline-flex items-center gap-1 font-medium">
				<svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
				Phản hồi trong 15 phút
			</span>
		</div>
	</form>
	<?php
}

// ----------------------------------------------------
// 3. Logo Helpers
// ----------------------------------------------------

/**
 * Automatically resolve optimized image URL (e.g. serving optimized WebP replacements from assets/images/).
 *
 * @param string $url       Original image URL.
 * @param string|bool $variant Variant name ('mobile', '1400w') or bool for mobile.
 * @return string
 */
function ltdh_get_optimized_image_url( string $url, $variant = false ): string {
	if ( empty( $url ) ) {
		return '';
	}

	// Preserve offloaded ImageKit / Cloudflare R2 / S3 / external CDN URLs
	if ( strpos( $url, 'ik.imagekit.io' ) !== false || strpos( $url, 'r2.dev' ) !== false || strpos( $url, 'cloudflarestorage.com' ) !== false || ( function_exists( 'advmo_get_provider_credential' ) && ( $cdn_domain = advmo_get_provider_credential( 'cloudflare_r2', 'domain' ) ) && strpos( $url, parse_url( $cdn_domain, PHP_URL_HOST ) ?: $cdn_domain ) !== false ) ) {
		return $url;
	}

	$parsed_path = parse_url( $url, PHP_URL_PATH );
	if ( empty( $parsed_path ) ) {
		return $url;
	}

	$filename         = basename( $parsed_path );
	$name_without_ext = pathinfo( $filename, PATHINFO_FILENAME );
	$ext              = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

	$is_mobile = ( true === $variant || 'mobile' === $variant );
	$is_1400w  = ( '1400w' === $variant );

	$theme_dir   = get_template_directory();
	$theme_uri   = 'https://ik.imagekit.io/cdnplus/assets/images';

	// 1. Check for specific variant in theme assets/images/
	if ( $is_1400w && file_exists( $theme_dir . '/assets/images/' . $name_without_ext . '-1400w.webp' ) ) {
		return $theme_uri . '/' . $name_without_ext . '-1400w.webp';
	}

	if ( $is_mobile && file_exists( $theme_dir . '/assets/images/' . $name_without_ext . '-mobile.webp' ) ) {
		return $theme_uri . '/' . $name_without_ext . '-mobile.webp';
	}

	// 2. Check for standard .webp in theme assets/images/
	if ( file_exists( $theme_dir . '/assets/images/' . $name_without_ext . '.webp' ) ) {
		return $theme_uri . '/' . $name_without_ext . '.webp';
	}

	// 3. If image is in wp-content/uploads/, check for sibling .webp
	if ( in_array( $ext, [ 'jpg', 'jpeg', 'png' ], true ) && strpos( $parsed_path, '/wp-content/uploads/' ) !== false ) {
		$upload_dir = wp_upload_dir();
		$rel_path   = ltrim( strstr( $parsed_path, '/wp-content/uploads/' ), '/' );
		$rel_path   = preg_replace( '#^wp-content/uploads/#', '', $rel_path );
		$dir_part   = dirname( $rel_path );
		$webp_rel   = ( '.' === $dir_part ? '' : $dir_part . '/' ) . $name_without_ext . '.webp';
		$webp_file  = $upload_dir['basedir'] . '/' . $webp_rel;

		if ( file_exists( $webp_file ) ) {
			return $upload_dir['baseurl'] . '/' . $webp_rel;
		}
	}

	return $url;
}

/**
 * Get site logo data (URL, width, height) with Customizer → ACF fallback.
 */
function ltdh_get_logo_data(): array {
	$data = [ 'url' => '', 'width' => 0, 'height' => 0 ];
	$logo_id = get_theme_mod('custom_logo');
	if ($logo_id) {
		$img_data = wp_get_attachment_image_src($logo_id, 'full');
		if ($img_data && ! empty($img_data[0])) {
			$data = [
				'url'    => $img_data[0],
				'width'  => (int) $img_data[1],
				'height' => (int) $img_data[2],
			];
		}
	}

	if ( empty( $data['url'] ) && function_exists('get_field') ) {
		$acf_logo = get_field('global_logo', 'options');
		if ($acf_logo) {
			if (is_numeric($acf_logo)) {
				$img_data = wp_get_attachment_image_src((int) $acf_logo, 'full');
				if ($img_data && ! empty($img_data[0])) {
					$data = [
						'url'    => $img_data[0],
						'width'  => (int) $img_data[1],
						'height' => (int) $img_data[2],
					];
				}
			} elseif (is_array($acf_logo) && isset($acf_logo['url'])) {
				$data = [
					'url'    => $acf_logo['url'],
					'width'  => (int) ($acf_logo['width'] ?? 200),
					'height' => (int) ($acf_logo['height'] ?? 60),
				];
			} elseif (is_string($acf_logo)) {
				$data = [
					'url'    => $acf_logo,
					'width'  => 200,
					'height' => 60,
				];
			}
		}
	}

	if ( ! empty( $data['url'] ) ) {
		$optimized = ltdh_get_optimized_image_url( $data['url'] );
		if ( ! empty( $optimized ) && $optimized !== $data['url'] ) {
			$data['url'] = $optimized;
		}
	}

	return $data;
}


/**
 * Get site logo URL with Customizer → ACF fallback.
 */
function ltdh_get_logo_url(): string {
	$data = ltdh_get_logo_data();
	return $data['url'] ?? '';
}

/**
 * Output site logo HTML with explicit dimensions & aspect-ratio (Zero CLS).
 */
function ltdh_site_logo(int $max_height = 48): void {
	$logo_data = ltdh_get_logo_data();
	if (! empty($logo_data['url'])) :
		$site_name = get_bloginfo('name');
		$orig_w = $logo_data['width'] ?: 200;
		$orig_h = $logo_data['height'] ?: 60;
		$calc_w = $orig_h > 0 ? round(($orig_w / $orig_h) * $max_height) : $orig_w;
		$logo_url = $logo_data['url'];
		if ( strpos( $logo_url, 'ik.imagekit.io' ) !== false || strpos( $logo_url, 'r2.dev' ) !== false ) {
			$clean_logo = preg_replace( '#\?tr=.*$#', '', $logo_url );
			$logo_url   = $clean_logo . '?tr=w-' . round( $calc_w * 2 ) . ',h-' . round( $max_height * 2 );
		}
	?>
		<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center" aria-label="<?php echo esc_attr($site_name); ?>">
			<img src="<?php echo esc_url($logo_url); ?>"
				alt="<?php echo esc_attr($site_name); ?>"
				width="<?php echo esc_attr($calc_w); ?>"
				height="<?php echo esc_attr($max_height); ?>"
				class="site-logo custom-logo skip-lazy no-lazy h-auto object-contain"
				loading="eager"
				fetchpriority="high"
				decoding="async"
				data-no-lazy="1"
				style="max-height: <?php echo esc_attr($max_height); ?>px; width: auto; aspect-ratio: <?php echo esc_attr($calc_w); ?> / <?php echo esc_attr($max_height); ?>;">
		</a>
	<?php else : ?>
		<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2 font-display font-black text-2xl text-brand-primary" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
			<div class="flex flex-col leading-none">
				<span class="text-sm font-semibold text-slate-400 tracking-wider">LIÊN THÔNG</span>
				<span class="text-xl font-extrabold text-brand-primary">ĐẠI HỌC</span>
			</div>
		</a>
	<?php endif;
}

/**
 * Output site logo for mobile with explicit dimensions & aspect-ratio (Zero CLS).
 */
function ltdh_site_logo_mobile(int $max_height = 36): void {
	$logo_data = ltdh_get_logo_data();
	if (! empty($logo_data['url'])) :
		$site_name = get_bloginfo('name');
		$orig_w = $logo_data['width'] ?: 200;
		$orig_h = $logo_data['height'] ?: 60;
		$calc_w = $orig_h > 0 ? round(($orig_w / $orig_h) * $max_height) : $orig_w;
		$logo_url = $logo_data['url'];
		if ( strpos( $logo_url, 'ik.imagekit.io' ) !== false || strpos( $logo_url, 'r2.dev' ) !== false ) {
			$clean_logo = preg_replace( '#\?tr=.*$#', '', $logo_url );
			$logo_url   = $clean_logo . '?tr=w-' . round( $calc_w * 2 ) . ',h-' . round( $max_height * 2 );
		}
	?>
		<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center" aria-label="<?php echo esc_attr($site_name); ?>">
			<img src="<?php echo esc_url($logo_url); ?>"
				alt="<?php echo esc_attr($site_name); ?>"
				width="<?php echo esc_attr($calc_w); ?>"
				height="<?php echo esc_attr($max_height); ?>"
				class="site-logo custom-logo skip-lazy no-lazy h-auto object-contain"
				loading="eager"
				decoding="async"
				data-no-lazy="1"
				style="max-height: <?php echo esc_attr($max_height); ?>px; width: auto; aspect-ratio: <?php echo esc_attr($calc_w); ?> / <?php echo esc_attr($max_height); ?>;">
		</a>
	<?php else : ?>
		<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2 font-display font-black text-2xl text-brand-primary" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
			<div class="flex flex-col leading-none">
				<span class="text-xs font-semibold text-slate-400 tracking-wider">LIÊN THÔNG</span>
				<span class="text-lg font-extrabold text-brand-primary">ĐẠI HỌC</span>
			</div>
		</a>
	<?php endif;
}

// ----------------------------------------------------
// 4. Breadcrumb
// ----------------------------------------------------

function ltdh_breadcrumb(): void {
	// 1. Primary: Rank Math breadcrumb engine
	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		// Respect admin setting: if breadcrumbs are toggled off in Rank Math settings, output nothing.
		if ( class_exists( '\RankMath\Helper' ) && ! \RankMath\Helper::is_breadcrumbs_enabled() ) {
			return;
		}

		ob_start();
		rank_math_the_breadcrumbs();
		$html = trim( (string) ob_get_clean() );

		if ( ! empty( $html ) ) {
			echo '<div class="ltdh-breadcrumb bg-slate-50/60 border-b border-slate-100">';
			echo '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 text-xs sm:text-sm text-slate-500 overflow-x-auto whitespace-nowrap scrollbar-none flex items-center gap-1.5">';
			echo $html;
			echo '</div>';
			echo '</div>';
			return;
		}
	}

	// 2. Fallback: Minimal native breadcrumbs (only active if Rank Math is deactivated)
	if ( is_front_page() ) {
		return;
	}

	$crumbs = [ [ 'label' => 'Trang chủ', 'url' => home_url( '/' ) ] ];

	if ( is_singular( 'program' ) ) {
		$program_id   = (int) get_the_ID();
		$program_name = function_exists( 'ltdh_get_program_banner_title' ) ? ltdh_get_program_banner_title( $program_id ) : get_the_title( $program_id );
		$major_id     = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $program_id ) : 0;
		if ( $major_id > 0 ) {
			$terms = get_the_terms( $major_id, 'major_cat' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$cat_link = get_term_link( $terms[0] );
				if ( ! is_wp_error( $cat_link ) ) {
					$crumbs[] = [ 'label' => $terms[0]->name, 'url' => $cat_link ];
				}
			}
			$crumbs[] = [ 'label' => get_the_title( $major_id ), 'url' => get_permalink( $major_id ) ];
		} else {
			$crumbs[] = [ 'label' => 'Liên thông Đại học', 'url' => home_url( '/hinh-thuc-dao-tao/' ) ];
		}
		$crumbs[] = [ 'label' => $program_name, 'url' => '' ];
	} elseif ( is_singular( 'school' ) ) {
		$crumbs[] = [ 'label' => 'Trường đối tác', 'url' => home_url( '/truong-doi-tac/' ) ];
		$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
	} elseif ( is_singular( 'major' ) ) {
		$terms = get_the_terms( get_the_ID(), 'major_cat' );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$crumbs[] = [ 'label' => $terms[0]->name, 'url' => get_term_link( $terms[0] ) ];
		}
		$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
	} elseif ( is_singular( 'post' ) || is_home() || is_category() ) {
		$crumbs[] = [ 'label' => 'Tin tức', 'url' => home_url( '/tin-tuc/' ) ];
		if ( is_category() ) {
			$cat = get_queried_object();
			if ( $cat ) {
				$crumbs[] = [ 'label' => $cat->name, 'url' => '' ];
			}
		} elseif ( is_singular( 'post' ) ) {
			$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
		}
	} elseif ( is_tax( 'training_type' ) ) {
		$crumbs[] = [ 'label' => 'Liên thông Đại học', 'url' => home_url( '/hinh-thuc-dao-tao/' ) ];
		$term     = get_queried_object();
		if ( $term ) {
			$crumbs[] = [ 'label' => $term->name, 'url' => '' ];
		}
	} else {
		$crumbs[] = [ 'label' => wp_title( '', false ) ?: 'Nội dung', 'url' => '' ];
	}

	echo '<div class="ltdh-breadcrumb bg-slate-50/60 border-b border-slate-100">';
	echo '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 text-xs sm:text-sm text-slate-500 overflow-x-auto whitespace-nowrap scrollbar-none flex items-center gap-1.5">';
	echo '<nav aria-label="breadcrumbs" class="rank-math-breadcrumb"><p>';
	$count = count( $crumbs );
	foreach ( $crumbs as $idx => $c ) {
		if ( ! empty( $c['url'] ) && $idx < $count - 1 ) {
			echo '<a href="' . esc_url( $c['url'] ) . '">' . esc_html( $c['label'] ) . '</a>';
			echo '<span class="separator"> - </span>';
		} else {
			echo '<span class="last">' . esc_html( $c['label'] ) . '</span>';
		}
	}
	echo '</p></nav>';
	echo '</div>';
	echo '</div>';
}

// ----------------------------------------------------
// 5. Performance / Query Caching
// ----------------------------------------------------

function ltdh_get_cached_query(string $transient_key, array $query_args, int $expiration = HOUR_IN_SECONDS) {
	$cached_results = get_transient($transient_key);
	if (false !== $cached_results) {
		return $cached_results;
	}
	$query = new WP_Query($query_args);
	set_transient($transient_key, $query, $expiration);
	return $query;
}

/**
 * Retrieve the display order for a school.
 * Checks ACF 'order', then postmeta 'order', then native 'menu_order'.
 *
 * @param int $school_id
 * @return int
 */
function ltdh_get_school_order( int $school_id ): int {
	if ( ! $school_id ) {
		return 0;
	}
	$order = get_field( 'order', $school_id );
	if ( null !== $order && false !== $order && '' !== $order ) {
		return intval( $order );
	}
	$meta_order = get_post_meta( $school_id, 'order', true );
	if ( '' !== $meta_order && false !== $meta_order && null !== $meta_order ) {
		return intval( $meta_order );
	}
	$post = get_post( $school_id );
	return $post ? intval( $post->menu_order ) : 0;
}

/**
 * Sort an array of school posts by display order.
 * Priority 1: Custom order > 0 (ASC: 1, 2, 3...)
 * Priority 2: is_featured = 1
 * Priority 3: Post date DESC
 *
 * @param array $posts Array of WP_Post objects
 * @return array
 */
function ltdh_sort_schools_by_order( array $posts ): array {
	if ( empty( $posts ) ) {
		return [];
	}

	usort( $posts, function( $a, $b ) {
		$id_a = is_object( $a ) ? $a->ID : intval( $a );
		$id_b = is_object( $b ) ? $b->ID : intval( $b );

		$order_a = ltdh_get_school_order( $id_a );
		$order_b = ltdh_get_school_order( $id_b );

		// Priority 1: custom order > 0
		if ( $order_a > 0 && $order_b > 0 ) {
			if ( $order_a !== $order_b ) {
				return $order_a <=> $order_b;
			}
		} elseif ( $order_a > 0 ) {
			return -1;
		} elseif ( $order_b > 0 ) {
			return 1;
		}

		// Priority 2: is_featured
		$feat_a = get_field( 'is_featured', $id_a ) ? 1 : 0;
		$feat_b = get_field( 'is_featured', $id_b ) ? 1 : 0;
		if ( $feat_a !== $feat_b ) {
			return $feat_b <=> $feat_a;
		}

		// Priority 3: date DESC
		$post_a = is_object( $a ) ? $a : get_post( $id_a );
		$post_b = is_object( $b ) ? $b : get_post( $id_b );
		$time_a = $post_a ? strtotime( $post_a->post_date ) : 0;
		$time_b = $post_b ? strtotime( $post_b->post_date ) : 0;

		return $time_b <=> $time_a;
	} );

	return $posts;
}

/**
 * Synchronize ACF field 'order' with native post menu_order for School CPT.
 */
add_action( 'save_post_school', 'ltdh_sync_school_order', 25, 3 );
function ltdh_sync_school_order( $post_id, $post = null, $update = null ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$order_val = null;
	if ( isset( $_POST['acf']['field_school_order'] ) ) {
		$order_val = intval( $_POST['acf']['field_school_order'] );
	} elseif ( isset( $_POST['order'] ) ) {
		$order_val = intval( $_POST['order'] );
	} elseif ( isset( $_POST['menu_order'] ) ) {
		$order_val = intval( $_POST['menu_order'] );
	}

	if ( null !== $order_val ) {
		update_post_meta( $post_id, 'order', $order_val );

		global $wpdb;
		$wpdb->update(
			$wpdb->posts,
			[ 'menu_order' => $order_val ],
			[ 'ID' => $post_id ],
			[ '%d' ],
			[ '%d' ]
		);
		clean_post_cache( $post_id );
	}

	ltdh_flush_all_content_transients();
}

add_action( 'acf/save_post', function( $post_id ) {
	if ( 'school' === get_post_type( $post_id ) ) {
		$order_val = get_field( 'order', $post_id );
		if ( null !== $order_val && false !== $order_val && '' !== $order_val ) {
			$order_num = intval( $order_val );
			global $wpdb;
			$wpdb->update(
				$wpdb->posts,
				[ 'menu_order' => $order_num ],
				[ 'ID' => $post_id ],
				[ '%d' ],
				[ '%d' ]
			);
			clean_post_cache( $post_id );
		}
	}
	ltdh_flush_all_content_transients();
}, 20 );

add_filter( 'acf/load_value/name=order', function( $value, $post_id, $field ) {
	if ( ( empty( $value ) || 0 === intval( $value ) ) && $post_id ) {
		$post = get_post( $post_id );
		if ( $post && $post->menu_order > 0 ) {
			return $post->menu_order;
		}
	}
	return $value;
}, 10, 3 );

/**
 * Get unified location display label for a school (Campus or Region).
 *
 * Prioritizes physical campus (e.g. 'Hà Nội', 'TP. Hồ Chí Minh'),
 * then city from address, then region ('Miền Bắc').
 *
 * @param int $school_id
 * @return string
 */
function ltdh_get_school_location_label( int $school_id ): string {
	if ( ! $school_id ) {
		return 'Chưa cập nhật';
	}

	// 1. Campus taxonomy term (Cơ sở đào tạo)
	$campus_terms = wp_get_post_terms( $school_id, LTDH_TAX_CAMPUS );
	if ( ! is_wp_error( $campus_terms ) && ! empty( $campus_terms ) ) {
		foreach ( $campus_terms as $ct ) {
			if ( 'online' !== $ct->slug && ! empty( $ct->name ) ) {
				return $ct->name;
			}
		}
		if ( ! empty( $campus_terms[0]->name ) ) {
			return $campus_terms[0]->name;
		}
	}

	// 2. Extract city / province from address ACF meta
	$address = get_field( 'address', $school_id );
	if ( $address ) {
		if ( preg_match( '/(Hà Nội|Hồ Chí Minh|TP\.?\s*HCM|Đà Nẵng|Hải Phòng|Cần Thơ|Thái Nguyên|Hưng Yên|Nghệ An|Nam Định|Bắc Ninh|Thanh Hóa|Huế|Thừa Thiên Huế|Bình Dương|Đồng Nai|Quảng Ninh|Vĩnh Phúc|Hải Dương|Bắc Giang|Phú Thọ|Thái Bình|Hà Nam|Ninh Bình|Quảng Nam|Khánh Hòa|Bà Rịa|Vũng Tàu|An Giang|Kiên Giang|Tiền Giang|Vĩnh Long|Trà Vinh|Đắk Lắk|Lâm Đồng|Gia Lai)/iu', $address, $matches ) ) {
			$matched_city = $matches[1];
			if ( preg_match( '/TP\.?\s*HCM/i', $matched_city ) ) {
				return 'Hồ Chí Minh';
			}
			return $matched_city;
		}
	}

	// 3. Region taxonomy term (Khu vực)
	$region_terms = wp_get_post_terms( $school_id, LTDH_TAX_REGION );
	if ( ! is_wp_error( $region_terms ) && ! empty( $region_terms ) ) {
		return $region_terms[0]->name;
	}

	return 'Hà Nội';
}

/**
 * Get school region label ('Miền Bắc', 'Miền Trung', 'Miền Nam').
 *
 * @param int $school_id
 * @return string
 */
function ltdh_get_school_region_label( int $school_id ): string {
	if ( ! $school_id ) {
		return '';
	}

	// 1. Region taxonomy term (Khu vực)
	$region_terms = wp_get_post_terms( $school_id, LTDH_TAX_REGION );
	if ( ! is_wp_error( $region_terms ) && ! empty( $region_terms ) ) {
		return $region_terms[0]->name;
	}

	// 2. Fallback: Infer region from address
	$address = (string) get_field( 'address', $school_id );
	if ( $address ) {
		if ( preg_match( '/(Hà Nội|Thái Nguyên|Hải Phòng|Hưng Yên|Nam Định|Bắc Ninh|Vĩnh Phúc|Hải Dương|Bắc Giang|Phú Thọ|Thái Bình|Hà Nam|Ninh Bình|Quảng Ninh)/iu', $address ) ) {
			return 'Miền Bắc';
		}
		if ( preg_match( '/(Hồ Chí Minh|TP\.?\s*HCM|Cần Thơ|Bình Dương|Đồng Nai|Bà Rịa|Vũng Tàu|An Giang|Kiên Giang|Tiền Giang|Vĩnh Long|Trà Vinh)/iu', $address ) ) {
			return 'Miền Nam';
		}
		if ( preg_match( '/(Đà Nẵng|Nghệ An|Thanh Hóa|Huế|Thừa Thiên Huế|Quảng Nam|Khánh Hòa|Đắk Lắk|Lâm Đồng|Gia Lai)/iu', $address ) ) {
			return 'Miền Trung';
		}
	}

	return 'Miền Bắc';
}

function ltdh_get_cached_featured_schools() {
	$cache_key = 'ltdh_featured_schools_data_v18';
	$data      = get_transient( $cache_key );
	if ( false !== $data && is_array( $data ) ) {
		return $data;
	}

	$schools_candidates = get_posts([
		'post_type'      => 'school',
		'numberposts'    => 100,
		'post_status'    => 'publish',
	]);

	$schools_candidates = ltdh_sort_schools_by_order( $schools_candidates );
	$top_schools        = array_slice( $schools_candidates, 0, 10 );

	$data = [];
	if ( ! empty( $top_schools ) ) {
		$index = 0;
		$fallback_images = ltdh_default('images', 'fallback_school_covers', []);
		foreach ( $top_schools as $school_post ) {
			$school_id   = $school_post->ID;
			$address     = get_field( 'address', $school_id );
			$hotline     = ltdh_get_school_hotline( $school_id );
			$thumb_url   = ltdh_get_school_cover_url( $school_id, 'medium' );
			$logo_id     = ltdh_get_school_image_id( $school_id );
			$school_code = get_post_meta( $school_id, 'school_code', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'school_code', $school_id ) : '' );
			$en_name     = get_post_meta( $school_id, 'english_name', true );
			if ( empty( $en_name ) && function_exists( 'get_field' ) ) {
				$en_name = get_field( 'english_name', $school_id );
			}
			if ( ( empty( $en_name ) || 'University' === $en_name ) && ! empty( $school_code ) ) {
				$en_name = $school_code . ' University';
			}

			$region = ltdh_get_school_region_label( $school_id );

			$school_progs = get_posts([
				'post_type' => 'program',
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_query' => [
					[
						'key' => 'school_relationship',
						'value' => $school_id,
						'compare' => '='
					]
				]
			]);

			$systems = [];
			if ( ! empty( $school_progs ) ) {
				foreach ($school_progs as $sp_id) {
					$terms = wp_get_post_terms($sp_id, 'training_type');
					if (! is_wp_error($terms)) {
						foreach ($terms as $t) {
							$systems[$t->slug] = $t->name;
						}
					}
				}
			}

			$systems_label = '';
			if (! empty($systems)) {
				if (count($systems) === 1 && (isset($systems['tu-xa']) || isset($systems['dao-tao-tu-xa']))) {
					$systems_label = '';
				} else {
					$systems_label = implode(' · ', $systems);
				}
			}

			$data[] = [
				'id'            => $school_id,
				'title'         => get_the_title($school_id),
				'permalink'     => get_permalink($school_id),
				'code'          => $school_code,
				'address'       => $address,
				'location'      => ltdh_get_school_location_label($school_id),
				'region'        => $region,
				'hotline'       => $hotline,
				'thumb_url'     => $thumb_url,
				'cover_id'      => ltdh_get_school_cover_id( $school_id ),
				'logo_id'       => $logo_id,
				'en_name'       => $en_name,
				'systems_label' => $systems_label,
				'school_types'  => ltdh_get_school_training_types( $school_id ),
				'prog_count'    => ltdh_get_school_unique_majors_count($school_id),
			];
			$index++;
		}
		wp_reset_postdata();
	}

	set_transient( $cache_key, $data, HOUR_IN_SECONDS );
	return $data;
}

/**
 * Cached filter dropdown options for Homepage & Search Bar.
 *
 * Avoids executing get_posts( -1 ) on every page request.
 *
 * @return array
 */
function ltdh_get_cached_filter_options(): array {
	$cache_key = 'ltdh_filter_options';
	$cached    = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	$schools = get_posts([
		'post_type'      => 'school',
		'posts_per_page' => 100,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	]);

	$school_list = [];
	foreach ( $schools as $sid ) {
		$school_list[] = [
			'slug'  => get_post_field( 'post_name', $sid ),
			'title' => get_the_title( $sid ),
		];
	}

	$majors = get_posts([
		'post_type'      => 'major',
		'posts_per_page' => 100,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	]);

	$major_list = [];
	foreach ( $majors as $mid ) {
		$major_list[] = [
			'slug'  => get_post_field( 'post_name', $mid ),
			'title' => get_the_title( $mid ),
		];
	}

	$types_terms = get_terms([
		'taxonomy'   => LTDH_TAX_TRAINING_TYPE,
		'hide_empty' => false,
	]);

	$type_list = [];
	if ( ! is_wp_error( $types_terms ) && ! empty( $types_terms ) ) {
		foreach ( $types_terms as $tt ) {
			$type_list[] = [
				'slug' => $tt->slug,
				'name' => $tt->name,
			];
		}
	}

	$data = [
		'schools' => $school_list,
		'majors'  => $major_list,
		'types'   => $type_list,
	];

	set_transient( $cache_key, $data, 12 * HOUR_IN_SECONDS );
	return $data;
}

/**
 * Flush all content transients across the theme.
 * Invalidates school cards, mega menus, search indices, and filter options.
 */
function ltdh_flush_all_content_transients() {
	delete_transient( LTDH_TRANSIENT_FEATURED_SCHOOLS );
	delete_transient( 'ltdh_featured_schools_data_v18' );
	delete_transient( 'ltdh_featured_schools_data_v11' );
	delete_transient( 'ltdh_featured_schools_data_v10' );
	delete_transient( 'ltdh_featured_schools_data' );
	delete_transient( 'ltdh_archive_school_featured_v2' );
	delete_transient( 'ltdh_archive_school_featured' );
	delete_transient( 'ltdh_mega_menu_majors_data' );
	delete_transient( 'ltdh_hot_majors_data' );
	delete_transient( 'ltdh_combinations_data' );
	delete_transient( 'ltdh_filter_options' );
	delete_transient( 'ltdh_training_type_counts' );
	delete_transient( 'ltdh_homepage_news' );

	global $wpdb;
	if ( $wpdb && ! empty( $wpdb->options ) ) {
		$wpdb->query(
			"DELETE FROM {$wpdb->options} 
			 WHERE option_name LIKE '_transient_ltdh_featured_%' 
			    OR option_name LIKE '_transient_timeout_ltdh_featured_%'
			    OR option_name LIKE '_transient_ltdh_archive_school_%'
			    OR option_name LIKE '_transient_timeout_ltdh_archive_school_%'
			    OR option_name LIKE '_transient_ltdh_major_s_%'
			    OR option_name LIKE '_transient_timeout_ltdh_major_s_%'
			    OR option_name LIKE '_transient_ltdh_search_%'
			    OR option_name LIKE '_transient_timeout_ltdh_search_%'"
		);
	}
}

function ltdh_clear_transients_on_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	$post_type = get_post_type( $post_id );
	if ( in_array( $post_type, [ LTDH_CPT_PROGRAM, LTDH_CPT_SCHOOL, LTDH_CPT_MAJOR, 'post' ], true ) ) {
		ltdh_flush_all_content_transients();
	}
}
add_action( 'save_post', 'ltdh_clear_transients_on_save', 20 );
add_action( 'deleted_post', 'ltdh_flush_all_content_transients', 20 );

// Auto flush theme transients whenever LiteSpeed Cache is purged
add_action( 'litespeed_purged_all', 'ltdh_flush_all_content_transients' );
add_action( 'litespeed_purged_all_lscache', 'ltdh_flush_all_content_transients' );

// ----------------------------------------------------

// 6. School Thumbnail Helpers
// ----------------------------------------------------

function ltdh_get_school_image_id(int $school_id): int {
	if (function_exists('get_field')) {
		$logo_id = get_field('logo', $school_id);
		if ($logo_id) {
			if (is_numeric($logo_id)) {
				return (int) $logo_id;
			}
			if (is_array($logo_id) && !empty($logo_id['ID'])) {
				return (int) $logo_id['ID'];
			}
		}
	}
	return 0;
}

function ltdh_generate_school_svg_logo( string $code, string $title = '' ): string {
	$code = strtoupper( trim( $code ) );
	$color_map = [
		'UTC'  => [ 'bg' => '#00308b', 'text' => '#ffffff', 'accent' => '#f97316' ],
		'NEU'  => [ 'bg' => '#b91c1c', 'text' => '#ffffff', 'accent' => '#fbbf24' ],
		'TNU'  => [ 'bg' => '#047857', 'text' => '#ffffff', 'accent' => '#34d399' ],
		'PTIT' => [ 'bg' => '#c2410c', 'text' => '#ffffff', 'accent' => '#fca5a5' ],
		'TMU'  => [ 'bg' => '#1d4ed8', 'text' => '#ffffff', 'accent' => '#93c5fd' ],
		'TVU'  => [ 'bg' => '#0f766e', 'text' => '#ffffff', 'accent' => '#5eead4' ],
		'DNU'  => [ 'bg' => '#ea580c', 'text' => '#ffffff', 'accent' => '#fed7aa' ],
		'BAV'  => [ 'bg' => '#1e3a8a', 'text' => '#ffffff', 'accent' => '#93c5fd' ],
		'AOF'  => [ 'bg' => '#4338ca', 'text' => '#ffffff', 'accent' => '#c7d2fe' ],
		'HAUI' => [ 'bg' => '#0369a1', 'text' => '#ffffff', 'accent' => '#7dd3fc' ],
	];

	$c = $color_map[ $code ] ?? [ 'bg' => '#0f172a', 'text' => '#ffffff', 'accent' => '#3b82f6' ];
	$font_size = strlen( $code ) <= 3 ? '40' : ( strlen( $code ) <= 5 ? '30' : '22' );

	$svg = sprintf(
		'<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">' .
		'<rect width="120" height="120" rx="24" fill="%s"/>' .
		'<circle cx="60" cy="60" r="50" fill="none" stroke="%s" stroke-width="3" stroke-opacity="0.3"/>' .
		'<text x="60" y="67" font-family="-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif" font-weight="900" font-size="%s" fill="%s" text-anchor="middle" dominant-baseline="middle">%s</text>' .
		'</svg>',
		$c['bg'],
		$c['accent'],
		$font_size,
		$c['text'],
		esc_html( $code )
	);

	return 'data:image/svg+xml;utf8,' . rawurlencode( $svg );
}

function ltdh_get_school_logo_url( $school_id, string $size = 'thumbnail' ): string {
	if ( is_array( $school_id ) ) {
		$school_id = ! empty( $school_id ) ? ( is_object( $school_id[0] ) ? $school_id[0]->ID : $school_id[0] ) : 0;
	} elseif ( is_object( $school_id ) ) {
		$school_id = $school_id->ID;
	}
	$school_id = intval( $school_id );

	if ( $school_id > 0 && function_exists( 'get_field' ) ) {
		$logo = get_field( 'logo', $school_id );
		if ( ! empty( $logo ) ) {
			$url = '';
			if ( is_numeric( $logo ) ) {
				$url = wp_get_attachment_image_url( (int) $logo, $size );
			} elseif ( is_array( $logo ) ) {
				$url = $logo['sizes'][ $size ] ?? ( $logo['url'] ?? '' );
			} elseif ( is_string( $logo ) && 0 === strpos( $logo, 'http' ) ) {
				$url = $logo;
			}
			if ( ! empty( $url ) && false === strpos( $url, 'cropped-logo' ) ) {
				return $url;
			}
		}

		$image_id = ltdh_get_school_image_id( $school_id );
		if ( $image_id ) {
			$url = wp_get_attachment_image_url( $image_id, $size );
			if ( ! empty( $url ) && false === strpos( $url, 'cropped-logo' ) ) {
				return $url;
			}
		}
	}

	$school_code = '';
	$title       = '';
	if ( $school_id > 0 ) {
		$school_code = get_post_meta( $school_id, 'school_code', true );
		$title       = get_the_title( $school_id );
	}

	if ( empty( $school_code ) && ! empty( $title ) ) {
		if ( false !== mb_stripos( $title, 'Giao thông' ) ) {
			$school_code = 'UTC';
		} elseif ( false !== mb_stripos( $title, 'Thái Nguyên' ) ) {
			$school_code = 'TNU';
		} elseif ( false !== mb_stripos( $title, 'Kinh tế Quốc dân' ) ) {
			$school_code = 'NEU';
		} elseif ( false !== mb_stripos( $title, 'Bưu chính' ) ) {
			$school_code = 'PTIT';
		} elseif ( false !== mb_stripos( $title, 'Thương mại' ) ) {
			$school_code = 'TMU';
		} elseif ( false !== mb_stripos( $title, 'Trà Vinh' ) ) {
			$school_code = 'TVU';
		} elseif ( false !== mb_stripos( $title, 'Đại Nam' ) ) {
			$school_code = 'DNU';
		} elseif ( false !== mb_stripos( $title, 'Ngân hàng' ) ) {
			$school_code = 'BAV';
		} elseif ( false !== mb_stripos( $title, 'Tài chính' ) ) {
			$school_code = 'AOF';
		} elseif ( false !== mb_stripos( $title, 'Công nghiệp' ) ) {
			$school_code = 'HAUI';
		} else {
			$words       = explode( ' ', preg_replace( '/^(Trường|Đại học|Học viện)\s+/iu', '', $title ) );
			$school_code = '';
			foreach ( array_slice( $words, 0, 4 ) as $w ) {
				if ( ! empty( $w ) ) {
					$school_code .= mb_substr( $w, 0, 1 );
				}
			}
			$school_code = mb_strtoupper( $school_code );
		}
	}

	if ( ! empty( $school_code ) ) {
		return ltdh_generate_school_svg_logo( $school_code, $title );
	}

	return ltdh_generate_school_svg_logo( 'UNI', 'Đại học' );
}

/**
 * Lấy Attachment ID ảnh bìa trường học (bảo đảm không nhầm lẫn với Logo).
 *
 * @param int|array|object $school_id School post ID, array, or post object.
 * @return int Attachment ID hoặc 0.
 */
function ltdh_get_school_cover_id( $school_id ): int {
	if ( is_array( $school_id ) ) {
		$school_id = ! empty( $school_id ) ? ( is_object( $school_id[0] ) ? $school_id[0]->ID : $school_id[0] ) : 0;
	} elseif ( is_object( $school_id ) ) {
		$school_id = $school_id->ID;
	}
	$school_id = intval( $school_id );
	if ( $school_id <= 0 ) {
		return 0;
	}

	static $runtime_cache = [];
	if ( isset( $runtime_cache[ $school_id ] ) ) {
		return $runtime_cache[ $school_id ];
	}

	// 0. Tra cứu bộ nhớ đệm postmeta đã phân giải trước đó (triệt tiêu N+1 Query Storm)
	$cached_cover_id = get_post_meta( $school_id, '_ltdh_cover_id_cache', true );
	if ( '' !== $cached_cover_id && false !== $cached_cover_id && is_numeric( $cached_cover_id ) ) {
		$runtime_cache[ $school_id ] = (int) $cached_cover_id;
		return (int) $cached_cover_id;
	}

	$logo_id = function_exists( 'ltdh_get_school_image_id' ) ? ltdh_get_school_image_id( $school_id ) : 0;
	$cover_id = 0;

	// 1. Kiểm tra ACF trường school_banner (không format để lấy Attachment ID)
	if ( function_exists( 'get_field' ) ) {
		$banner = get_field( 'school_banner', $school_id, false )
			?: get_field( 'cover_image', $school_id, false )
			?: get_field( 'page_banner', $school_id, false )
			?: get_field( 'banner', $school_id, false );

		if ( ! empty( $banner ) ) {
			if ( is_numeric( $banner ) && (int) $banner !== (int) $logo_id ) {
				$cover_id = (int) $banner;
			} elseif ( is_array( $banner ) && ! empty( $banner['ID'] ) && (int) $banner['ID'] !== (int) $logo_id ) {
				$cover_id = (int) $banner['ID'];
			}
		}
	}

	// 2. Kiểm tra postmeta trực tiếp
	if ( ! $cover_id ) {
		$meta_banner = get_post_meta( $school_id, 'school_banner', true );
		if ( ! empty( $meta_banner ) && is_numeric( $meta_banner ) && (int) $meta_banner !== (int) $logo_id ) {
			$cover_id = (int) $meta_banner;
		}
	}

	// 3. Featured Image (ảnh đại diện post)
	if ( ! $cover_id ) {
		$thumb_id = get_post_thumbnail_id( $school_id );
		if ( $thumb_id && (int) $thumb_id !== (int) $logo_id ) {
			$cover_id = (int) $thumb_id;
		}
	}

	// 4. Nếu lưu chuỗi URL, tra cứu ngược ID qua attachment_url_to_postid
	if ( ! $cover_id && function_exists( 'get_field' ) ) {
		$banner_url = get_field( 'school_banner', $school_id, true );
		if ( is_string( $banner_url ) && ! empty( $banner_url ) ) {
			$att_id = attachment_url_to_postid( $banner_url );
			if ( $att_id && (int) $att_id !== (int) $logo_id ) {
				$cover_id = (int) $att_id;
			}
		}
	}

	// Lưu cache vào postmeta và runtime cache
	update_post_meta( $school_id, '_ltdh_cover_id_cache', $cover_id );
	$runtime_cache[ $school_id ] = $cover_id;

	return $cover_id;
}

// Xóa cache cover ID khi bài viết trường được cập nhật
add_action( 'save_post_school', function( $post_id ) {
	delete_post_meta( $post_id, '_ltdh_cover_id_cache' );
} );

/**
 * Get school cover / banner photo URL (e.g. for card top backgrounds or school page banners).
 * Never uses the school logo attachment as a banner background.
 * Trả về ảnh kích thước 'medium' (300x180 WebP) thay vì ảnh gốc 1920px.
 *
 * @param int|array|object $school_id School post ID, array, or post object.
 * @param string           $size      Image size name (default 'medium').
 * @return string
 */
function ltdh_get_school_cover_url( $school_id, string $size = 'medium' ): string {
	if ( is_array( $school_id ) ) {
		$school_id = ! empty( $school_id ) ? ( is_object( $school_id[0] ) ? $school_id[0]->ID : $school_id[0] ) : 0;
	} elseif ( is_object( $school_id ) ) {
		$school_id = $school_id->ID;
	}
	$school_id = intval( $school_id );

	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();

	if ( $school_id > 0 ) {
		$cover_id = ltdh_get_school_cover_id( $school_id );
		if ( $cover_id > 0 ) {
			$url = wp_get_attachment_image_url( $cover_id, $size );
			if ( ! empty( $url ) ) {
				return $url;
			}
		}

		// Fallback kiểm tra chuỗi URL hợp lệ không phải logo
		if ( function_exists( 'get_field' ) ) {
			$banner = get_field( 'school_banner', $school_id ) ?: get_field( 'cover_image', $school_id ) ?: get_field( 'page_banner', $school_id ) ?: get_field( 'banner', $school_id );
			if ( ! empty( $banner ) ) {
				$logo_id  = function_exists( 'ltdh_get_school_image_id' ) ? ltdh_get_school_image_id( $school_id ) : 0;
				$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
				$raw_url  = is_array( $banner ) ? ( $banner['sizes'][ $size ] ?? ( $banner['url'] ?? '' ) ) : ( is_string( $banner ) ? $banner : '' );
				if ( ! empty( $raw_url ) && $raw_url !== $logo_url && false === strpos( $raw_url, 'cropped-logo' ) && false === strpos( strtolower( $raw_url ), 'logo' ) ) {
					return $raw_url;
				}
			}
		}
	}

	// Fallback campus cover photos: ưu tiên WebP chuẩn kích thước thẻ card (< 30 KiB)
	if ( ( $size === 'medium' || $size === 'thumbnail' ) && file_exists( $theme_dir . '/assets/images/banner-fallback-300x180.webp' ) ) {
		return $theme_uri . '/assets/images/banner-fallback-300x180.webp';
	}

	if ( file_exists( $theme_dir . '/assets/images/banner-fallback.webp' ) ) {
		return $theme_uri . '/assets/images/banner-fallback.webp';
	}

	if ( file_exists( $theme_dir . '/assets/images/banner-school.webp' ) ) {
		return $theme_uri . '/assets/images/banner-school.webp';
	}

	return $theme_uri . '/assets/images/banner-school.jpg';
}

function ltdh_render_school_thumbnail(int $school_id, string $size = 'thumbnail', string $classes = 'h-14 w-14 object-cover border border-slate-100 bg-white rounded-lg'): void {
	$image_id = ltdh_get_school_image_id($school_id);
	if ($image_id) {
		echo wp_get_attachment_image($image_id, $size, false, [
			'class'   => $classes,
			'loading' => 'lazy',
			'alt'     => sprintf('Logo %s', get_the_title($school_id)),
		]);
		return;
	}

	$fallback_classes = preg_replace('/\bobject-(cover|contain)\b/', '', $classes);
	$fallback_classes = trim(preg_replace('/\s+/', ' ', $fallback_classes));
	printf(
		'<div class="%s bg-blue-50 text-brand-primary font-display font-black text-sm flex items-center justify-center" aria-hidden="true">UNI</div>',
		esc_attr($fallback_classes)
	);
}

// ----------------------------------------------------
// 7. Program Learning Details
// ----------------------------------------------------

function ltdh_get_program_learning_details(int $program_id): array {
	$campuses          = wp_get_post_terms($program_id, LTDH_TAX_CAMPUS);
	$physical_campuses = [];

	if ( ! empty($campuses) && ! is_wp_error($campuses) ) {
		foreach ($campuses as $campus) {
			$slug = is_object($campus) ? $campus->slug : '';
			$name = is_object($campus) ? trim($campus->name) : '';
			if ('online' === strtolower($slug) || 'online' === strtolower($name)) {
				continue;
			}
			if ($name && ! in_array($name, $physical_campuses, true)) {
				$physical_campuses[] = $name;
			}
		}
	}

	$types     = wp_get_post_terms($program_id, LTDH_TAX_TRAINING_TYPE);
	$type_slug = ! empty($types) && ! is_wp_error($types) ? $types[0]->slug : '';

	// Resolve physical campus location:
	if ( ! empty( $physical_campuses ) ) {
		$campus_name = implode( ', ', $physical_campuses );
	} elseif ( in_array( $type_slug, [ 'tu-xa', 'dao-tao-tu-xa' ], true ) ) {
		$campus_name = 'Toàn quốc';
	} else {
		// Non-tu-xa without explicit physical campus: check school region
		$school_id = get_post_meta( $program_id, 'school_relationship', true );
		if ( is_array( $school_id ) ) {
			$school_id = ! empty( $school_id ) ? ( is_object( $school_id[0] ) ? $school_id[0]->ID : $school_id[0] ) : 0;
		} elseif ( is_object( $school_id ) ) {
			$school_id = $school_id->ID;
		}
		$school_region = '';
		if ( $school_id ) {
			$region_terms = wp_get_post_terms( (int) $school_id, LTDH_TAX_REGION );
			if ( ! empty( $region_terms ) && ! is_wp_error( $region_terms ) ) {
				$school_region = $region_terms[0]->name;
			}
		}
		$campus_name = $school_region ?: 'Toàn quốc';
	}

	// Resolve learning mode
	$custom_mode = get_post_meta( $program_id, 'learning_mode', true );
	if ( ! empty( $custom_mode ) && is_string( $custom_mode ) && trim( $custom_mode ) !== '' ) {
		$learning_mode = trim( $custom_mode );
	} elseif ( in_array( $type_slug, [ 'tu-xa', 'dao-tao-tu-xa' ], true ) ) {
		$learning_mode = 'Học online 100%';
	} elseif ( in_array( $type_slug, [ 'vua-hoc-vua-lam', 'vhvl', 'vua-lam-vua-hoc' ], true ) ) {
		$learning_mode = 'Học tập trung / Cuối tuần';
	} elseif ( in_array( $type_slug, [ 'chinh-quy', 'chinh-quy-tap-trung' ], true ) ) {
		$learning_mode = 'Học tập trung';
	} else {
		$learning_mode = 'Học tập trung / Cuối tuần';
	}

	return [
		'campus' => $campus_name,
		'mode'   => $learning_mode,
	];
}

// ----------------------------------------------------
// 12. Dynamic CF7 Program Selector & Option Label Formatter
// ----------------------------------------------------

/**
 * Format standard label for program select dropdowns in consultation and registration forms.
 * Format: "Tên chương trình - Hệ - Mã trường" (VD: "Công nghệ thông tin - Đào tạo từ xa - UTC")
 *
 * @param int|WP_Post $program Program ID or post object.
 * @return string Formatted program label.
 */
function ltdh_format_program_select_label( $program ): string {
	$prog_id = is_object( $program ) ? $program->ID : absint( $program );
	if ( ! $prog_id ) {
		return '';
	}

	$prog_name = '';

	// 1. Chuyên ngành đào tạo cụ thể (nếu có)
	$spec_title = function_exists( 'get_field' ) ? get_field( 'specialization_title', $prog_id ) : '';
	if ( empty( $spec_title ) ) {
		$spec_title = get_post_meta( $prog_id, 'specialization_title', true );
	}
	if ( empty( $spec_title ) ) {
		$spec_title = function_exists( 'get_field' ) ? get_field( 'chuyen_nganh', $prog_id ) : '';
		if ( empty( $spec_title ) ) {
			$spec_title = get_post_meta( $prog_id, 'chuyen_nganh', true );
		}
	}
	if ( ! empty( $spec_title ) && is_string( $spec_title ) ) {
		$prog_name = $spec_title;
	}

	// 2. Tên ngành đào tạo liên kết (major_relationship)
	if ( empty( $prog_name ) ) {
		$major_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $prog_id ) : 0;
		if ( $major_id ) {
			$prog_name = get_the_title( $major_id );
		}
	}

	// 3. Fallback: Tiêu đề chương trình đã làm sạch qua helper chuẩn
	if ( empty( $prog_name ) ) {
		$prog_name = function_exists( 'ltdh_get_program_discipline_name' ) ? ltdh_get_program_discipline_name( $prog_id ) : get_the_title( $prog_id );
	}

	$prog_name = preg_replace( '/^(?:ngành|chuyên ngành|cử nhân|kỹ sư)\s+/iu', '', trim( (string) $prog_name ) );
	$prog_name = preg_replace( '/\s*\([^)]*\)$/u', '', $prog_name );
	$prog_name = trim( $prog_name, " -–—\t\n\r\0\x0B" );
	if ( ! empty( $prog_name ) ) {
		$prog_name = mb_strtoupper( mb_substr( $prog_name, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $prog_name, 1, null, 'UTF-8' );
	}

	$terms      = wp_get_post_terms( $prog_id, 'training_type' );
	$type_name  = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? trim( (string) $terms[0]->name ) : '';
	$clean_type = preg_replace( '/^(?:hệ\s+|đào\s+tạo\s+)/iu', '', $type_name );
	if ( ! empty( $clean_type ) ) {
		$clean_type = mb_strtoupper( mb_substr( $clean_type, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $clean_type, 1, null, 'UTF-8' );
	}

	$school_id   = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $prog_id ) : 0;
	$school_code = $school_id ? ( function_exists( 'ltdh_get_school_code' ) ? ltdh_get_school_code( $school_id ) : get_post_meta( $school_id, 'school_code', true ) ) : '';
	if ( empty( $school_code ) && $school_id ) {
		$school_title = get_the_title( $school_id );
		$school_code  = preg_replace( '/^(?:trường\s+)?đại\s+học\s+/iu', 'ĐH ', trim( (string) $school_title ) );
	}

	$parts = array_filter( [ $prog_name, $clean_type, $school_code ] );
	return ! empty( $parts ) ? implode( ' - ', $parts ) : get_the_title( $prog_id );
}

function ltdh_cf7_dynamic_programs($tag, $replace) {
	if ('current_program_id' === $tag['name'] || 'program_id' === $tag['name'] || 'current_major_id' === $tag['name'] || 'major_id' === $tag['name']) {
		$majors = get_posts([
			'post_type'   => defined('LTDH_CPT_MAJOR') ? LTDH_CPT_MAJOR : 'major',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'title',
			'order'       => 'ASC',
		]);
		$tag['raw_values'] = [];
		$tag['values']     = [];
		$tag['labels']     = [];

		$tag['raw_values'][] = '';
		$tag['values'][]     = '';
		$tag['labels'][]     = '-- Chọn ngành học --';

		$curr_id = 0;
		if ( ! empty( $GLOBALS['ltdh_cf7_context_hidden_fields']['current_major_id'] ) ) {
			$curr_id = (int) $GLOBALS['ltdh_cf7_context_hidden_fields']['current_major_id'];
		} elseif ( ! empty( $GLOBALS['ltdh_cf7_context_hidden_fields']['major_id'] ) ) {
			$curr_id = (int) $GLOBALS['ltdh_cf7_context_hidden_fields']['major_id'];
		} elseif ( ! empty( $GLOBALS['ltdh_cf7_context_hidden_fields']['current_program_id'] ) ) {
			$prog_id = (int) $GLOBALS['ltdh_cf7_context_hidden_fields']['current_program_id'];
			$curr_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $prog_id ) : (int) get_post_meta( $prog_id, 'major_relationship', true );
		} elseif ( ! empty( $GLOBALS['ltdh_cf7_context_hidden_fields']['program_id'] ) ) {
			$prog_id = (int) $GLOBALS['ltdh_cf7_context_hidden_fields']['program_id'];
			$curr_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $prog_id ) : (int) get_post_meta( $prog_id, 'major_relationship', true );
		} elseif ( function_exists( 'is_singular' ) && is_singular( 'major' ) ) {
			$curr_id = get_the_ID();
		} elseif ( function_exists( 'is_singular' ) && is_singular( 'program' ) ) {
			$prog_id = get_the_ID();
			$curr_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $prog_id ) : (int) get_post_meta( $prog_id, 'major_relationship', true );
		}

		$items = [];
		foreach ($majors as $m) {
			$items[] = [
				'id'    => (string) $m->ID,
				'label' => $m->post_title,
			];
		}

		// Sắp xếp danh sách ngành theo thứ tự chữ cái của tên hiển thị
		usort( $items, function( $a, $b ) {
			return strcmp( mb_strtolower( $a['label'], 'UTF-8' ), mb_strtolower( $b['label'], 'UTF-8' ) );
		} );

		$selected_idx = 0;
		$idx = 1;
		foreach ($items as $item) {
			$idx++;
			$tag['raw_values'][] = $item['id'];
			$tag['values'][]     = $item['id'];
			$tag['labels'][]     = $item['label'];

			if ( $curr_id && (int) $item['id'] === $curr_id ) {
				$selected_idx = $idx;
			}
		}

		if ( $selected_idx > 0 ) {
			if ( ! isset( $tag['options'] ) || ! is_array( $tag['options'] ) ) {
				$tag['options'] = [];
			}
			$tag['options'][] = 'default:' . $selected_idx;
		}
	}
	return $tag;
}
add_filter('wpcf7_form_tag', 'ltdh_cf7_dynamic_programs', 10, 2);

/**
 * Ensure CF7 consultation form displays "Ngành học quan tâm" label consistently.
 */
add_filter( 'wpcf7_form_elements', function( $content ) {
	if ( false !== strpos( $content, 'consult-program' ) ) {
		$content = str_replace( 'Chương trình quan tâm', 'Ngành học quan tâm', $content );
	}
	return $content;
} );

// ----------------------------------------------------
// 13. Fallback Image Helper
// ----------------------------------------------------

function ltdh_get_fallback_image( string $context = 'program', string $size = 'medium' ): string {
	$theme_uri = get_template_directory_uri();
	$theme_dir = get_template_directory();

	if ( $context === 'school' ) {
		if ( ( $size === 'medium' || $size === 'thumbnail' ) && file_exists( $theme_dir . '/assets/images/banner-fallback-300x180.webp' ) ) {
			return $theme_uri . '/assets/images/banner-fallback-300x180.webp';
		}
		if ( file_exists( $theme_dir . '/assets/images/banner-school.webp' ) ) {
			return $theme_uri . '/assets/images/banner-school.webp';
		}
		return $theme_uri . '/assets/images/banner-school.jpg';
	}

	if ( $context === 'contact' ) {
		$contact_img = home_url( '/wp-content/uploads/2026/07/banner-contact.png' );
		return function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $contact_img ) : $contact_img;
	}

	$custom_share_image = null;
	if ( function_exists( 'get_field' ) ) {
		$custom_share_image = get_field( 'global_share_image', 'options' ) ?: get_field( 'global_share_image', 'option' );
	}
	if ( empty( $custom_share_image ) ) {
		$raw_opt = get_option( 'options_global_share_image' ) ?: get_option( 'global_share_image' );
		if ( ! empty( $raw_opt ) ) {
			if ( is_numeric( $raw_opt ) ) {
				$custom_share_image = wp_get_attachment_image_url( (int) $raw_opt, 'full' );
			} elseif ( is_string( $raw_opt ) && filter_var( $raw_opt, FILTER_VALIDATE_URL ) ) {
				$custom_share_image = $raw_opt;
			}
		}
	}
	if ( ! empty( $custom_share_image ) ) {
		if ( is_array( $custom_share_image ) && ! empty( $custom_share_image['url'] ) ) {
			$custom_share_image = $custom_share_image['url'];
		} elseif ( is_numeric( $custom_share_image ) ) {
			$custom_share_image = wp_get_attachment_image_url( (int) $custom_share_image, 'full' );
		}
		if ( ! empty( $custom_share_image ) ) {
			return function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $custom_share_image ) : $custom_share_image;
		}
	}

	if ( ( $size === 'medium' || $size === 'thumbnail' ) && file_exists( $theme_dir . '/assets/images/banner-fallback-300x180.webp' ) ) {
		return $theme_uri . '/assets/images/banner-fallback-300x180.webp';
	}
	if ( file_exists( $theme_dir . '/assets/images/banner-fallback.webp' ) ) {
		return $theme_uri . '/assets/images/banner-fallback.webp';
	}
	if ( file_exists( $theme_dir . '/assets/images/banner-default.jpg' ) ) {
		return $theme_uri . '/assets/images/banner-default.jpg';
	}
	if ( file_exists( $theme_dir . '/assets/images/banner-fallback.png' ) ) {
		return $theme_uri . '/assets/images/banner-fallback.png';
	}
	return $theme_uri . '/assets/images/banner-program.jpg';
}

/**
 * Lấy URL ảnh đại diện của bài viết, tự động fallback về ảnh mặc định nếu bài viết không có ảnh đại diện.
 *
 * @param int|WP_Post|null $post Post ID or post object. Default current post.
 * @param string           $size Kích thước ảnh (thumbnail, medium, large, full).
 * @return string URL ảnh đại diện hoặc ảnh mặc định.
 */
function ltdh_get_post_thumbnail_url( $post = null, string $size = 'medium' ): string {
	$post_id = $post ? ( is_object( $post ) ? $post->ID : (int) $post ) : get_the_ID();
	if ( $post_id && has_post_thumbnail( $post_id ) ) {
		$thumb = get_the_post_thumbnail_url( $post_id, $size );
		if ( ! empty( $thumb ) ) {
			return function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $thumb ) : $thumb;
		}
	}

	// Nếu là chương trình đào tạo mà không có ảnh, mặc định lấy ảnh của ngành thuộc chương trình đó
	if ( $post_id && 'program' === get_post_type( $post_id ) ) {
		$major_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $post_id ) : 0;
		if ( $major_id ) {
			$major_thumb = get_the_post_thumbnail_url( $major_id, $size );
			if ( ! empty( $major_thumb ) ) {
				return function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $major_thumb ) : $major_thumb;
			}
		}
	}

	$fallback = ltdh_get_fallback_image( 'post' );
	return function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $fallback ) : $fallback;
}

/**
 * Lấy URL ảnh banner của Ngành đào tạo (Major).
 * Nguyên tắc: Ưu tiên ảnh upload riêng (major_banner / page_banner).
 * Nếu không có thì mới lấy từ ảnh đại diện (featured image / thumbnail) của ngành.
 *
 * @param int    $major_id ID của ngành đào tạo (post_type = major).
 * @param string $size     Kích thước ảnh (full, large,...). Default 'full'.
 * @return string URL ảnh banner hoặc chuỗi rỗng nếu không có.
 */
function ltdh_get_major_banner_url( int $major_id, string $size = 'full' ): string {
	if ( ! $major_id ) {
		return '';
	}

	$banner_url = '';

	// 1. Upload riêng: ACF major_banner (hoặc page_banner)
	if ( function_exists( 'get_field' ) ) {
		$raw = get_field( 'major_banner', $major_id ) ?: get_field( 'page_banner', $major_id );
		if ( is_array( $raw ) ) {
			$banner_url = $raw['url'] ?? '';
		} elseif ( is_numeric( $raw ) && (int) $raw > 0 ) {
			$banner_url = wp_get_attachment_image_url( (int) $raw, $size ) ?: '';
		} elseif ( is_string( $raw ) && ! empty( $raw ) ) {
			$banner_url = $raw;
		}
	}

	if ( empty( $banner_url ) ) {
		$raw_meta = get_post_meta( $major_id, 'major_banner', true ) ?: get_post_meta( $major_id, 'page_banner', true );
		if ( is_numeric( $raw_meta ) && (int) $raw_meta > 0 ) {
			$banner_url = wp_get_attachment_image_url( (int) $raw_meta, $size ) ?: '';
		} elseif ( is_string( $raw_meta ) && ! empty( $raw_meta ) ) {
			$banner_url = $raw_meta;
		}
	}

	// 2. Fallback: Ảnh đại diện (Featured Image / Thumbnail) của ngành
	if ( empty( $banner_url ) && has_post_thumbnail( $major_id ) ) {
		$banner_url = get_the_post_thumbnail_url( $major_id, $size ) ?: '';
	}

	if ( ! empty( $banner_url ) && function_exists( 'ltdh_get_optimized_image_url' ) ) {
		$banner_url = ltdh_get_optimized_image_url( $banner_url );
	}

	return $banner_url ?: '';
}

/**
 * Lấy URL ảnh đại diện / ảnh banner của chương trình tuyển sinh.
 * Nguyên tắc: Nếu chương trình không có ảnh thì mặc định sẽ lấy ảnh ngành của chương trình đó.
 *
 * @param int    $program_id ID chương trình (post_type = program).
 * @param string $size       Kích thước ảnh (full, medium, large,...). Default 'full'.
 * @return string URL ảnh hoặc chuỗi rỗng nếu không có.
 */
function ltdh_get_program_image_url( int $program_id, string $size = 'full' ): string {
	if ( ! $program_id ) {
		return '';
	}

	$prog_img = '';

	// 1. Upload riêng: ACF program_banner (hoặc page_banner, banner_image)
	if ( function_exists( 'get_field' ) ) {
		$raw = get_field( 'program_banner', $program_id )
			?: get_field( 'page_banner', $program_id )
			?: get_field( 'banner_image', $program_id );
		if ( is_array( $raw ) ) {
			$prog_img = $raw['url'] ?? '';
		} elseif ( is_numeric( $raw ) && (int) $raw > 0 ) {
			$prog_img = wp_get_attachment_image_url( (int) $raw, $size ) ?: '';
		} elseif ( is_string( $raw ) && ! empty( $raw ) ) {
			$prog_img = $raw;
		}
	}

	if ( empty( $prog_img ) ) {
		$raw_meta = get_post_meta( $program_id, 'program_banner', true )
			?: get_post_meta( $program_id, 'page_banner', true )
			?: get_post_meta( $program_id, 'banner_image', true );
		if ( is_numeric( $raw_meta ) && (int) $raw_meta > 0 ) {
			$prog_img = wp_get_attachment_image_url( (int) $raw_meta, $size ) ?: '';
		} elseif ( is_string( $raw_meta ) && ! empty( $raw_meta ) ) {
			$prog_img = $raw_meta;
		}
	}

	// 2. Fallback: Ảnh đại diện (Featured Image / Thumbnail) của chương trình
	if ( empty( $prog_img ) && has_post_thumbnail( $program_id ) ) {
		$prog_img = get_the_post_thumbnail_url( $program_id, $size ) ?: '';
	}

	if ( ! empty( $prog_img ) ) {
		return function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $prog_img ) : $prog_img;
	}

	// 3. Fallback: Kế thừa ảnh ngành của chương trình đấy
	$major_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $program_id ) : 0;
	if ( $major_id ) {
		$major_img = function_exists( 'ltdh_get_major_banner_url' )
			? ltdh_get_major_banner_url( $major_id, $size )
			: '';
		if ( ! empty( $major_img ) ) {
			return $major_img;
		}
	}

	// 4. Fallback: Banner của trường đối tác (nếu có)
	$school_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $program_id ) : 0;
	if ( $school_id ) {
		$school_img = '';
		if ( function_exists( 'get_field' ) ) {
			$raw_school = get_field( 'school_banner', $school_id );
			if ( is_array( $raw_school ) ) {
				$school_img = $raw_school['url'] ?? '';
			} elseif ( is_numeric( $raw_school ) && (int) $raw_school > 0 ) {
				$school_img = wp_get_attachment_image_url( (int) $raw_school, $size ) ?: '';
			} elseif ( is_string( $raw_school ) && ! empty( $raw_school ) ) {
				$school_img = $raw_school;
			}
		}
		if ( empty( $school_img ) && has_post_thumbnail( $school_id ) ) {
			$school_img = get_the_post_thumbnail_url( $school_id, $size ) ?: '';
		}
		if ( ! empty( $school_img ) ) {
			return function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $school_img ) : $school_img;
		}
	}

	return '';
}

/**
 * Filter default fallback HTML for post thumbnails when no featured image is set.
 */
function ltdh_filter_default_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	if ( empty( $html ) && ! is_admin() ) {
		$fallback_url = ltdh_get_fallback_image( 'post' );
		$alt = esc_attr( get_the_title( $post_id ) );
		$class = 'wp-post-image';
		if ( is_array( $attr ) && ! empty( $attr['class'] ) ) {
			$class .= ' ' . esc_attr( $attr['class'] );
		} elseif ( is_string( $attr ) && ! empty( $attr ) ) {
			$class .= ' ' . esc_attr( $attr );
		}
		return sprintf( '<img src="%s" alt="%s" class="%s" loading="lazy" />', esc_url( $fallback_url ), $alt, $class );
	}
	return $html;
}
add_filter( 'post_thumbnail_html', 'ltdh_filter_default_post_thumbnail_html', 20, 5 );

/**
 * Lấy URL ảnh thumbnail của Nhóm ngành (major_cat taxonomy).
 * Hỗ trợ ACF field (category_thumbnail) và term meta (thumbnail_id / category_thumbnail).
 *
 * @param int|WP_Term|null $term Term ID or object.
 * @param string           $size Image size.
 * @return string
 */
function ltdh_get_major_cat_thumbnail_url( $term = null, string $size = 'medium_large' ): string {
	if ( empty( $term ) ) {
		return '';
	}
	$term_id = is_object( $term ) ? $term->term_id : (int) $term;
	if ( ! $term_id ) {
		return '';
	}

	// 1. Check ACF field
	if ( function_exists( 'get_field' ) ) {
		$thumb = get_field( 'category_thumbnail', 'term_' . $term_id )
			?: get_field( 'category_thumbnail', 'major_cat_' . $term_id );
		if ( ! empty( $thumb ) ) {
			if ( is_numeric( $thumb ) ) {
				$url = wp_get_attachment_image_url( (int) $thumb, $size );
				if ( ! empty( $url ) ) {
					return $url;
				}
			} elseif ( is_array( $thumb ) && ! empty( $thumb['url'] ) ) {
				return $thumb['url'];
			} elseif ( is_string( $thumb ) ) {
				return $thumb;
			}
		}
	}

	// 2. Check WordPress term meta
	$meta_thumb_id = get_term_meta( $term_id, 'category_thumbnail', true )
		?: get_term_meta( $term_id, 'thumbnail_id', true );
	if ( ! empty( $meta_thumb_id ) && is_numeric( $meta_thumb_id ) ) {
		$url = wp_get_attachment_image_url( (int) $meta_thumb_id, $size );
		if ( ! empty( $url ) ) {
			return $url;
		}
	}

	return '';
}

/**
 * Lấy ảnh thumbnail hiển thị cho Card Ngành (Major).
 * Ưu tiên:
 * 1. Post featured image (thumbnail của bài viết)
 * 2. Category thumbnail (ảnh đại diện của Nhóm ngành major_cat)
 * 3. Fallback theme banner
 *
 * @param int|WP_Post|null $post_id Post ID or object.
 * @param string           $size Image size.
 * @return string
 */
function ltdh_get_major_card_thumbnail_url( $post_id = null, string $size = 'medium_large' ): string {
	$pid = $post_id ? ( is_object( $post_id ) ? $post_id->ID : (int) $post_id ) : get_the_ID();
	if ( ! $pid ) {
		return get_stylesheet_directory_uri() . '/assets/images/banner-fallback.webp';
	}

	// 1. Featured image of the post
	if ( has_post_thumbnail( $pid ) ) {
		$thumb = get_the_post_thumbnail_url( $pid, $size );
		if ( ! empty( $thumb ) ) {
			return $thumb;
		}
	}

	// 2. Category thumbnail of major_cat
	$terms = get_the_terms( $pid, 'major_cat' );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			$cat_thumb = ltdh_get_major_cat_thumbnail_url( $t->term_id, $size );
			if ( ! empty( $cat_thumb ) ) {
				return $cat_thumb;
			}
		}
	}

	// 3. Fallback theme banner
	if ( file_exists( get_template_directory() . '/assets/images/banner-hero-02.webp' ) ) {
		return get_template_directory_uri() . '/assets/images/banner-hero-02.webp';
	}
	return get_template_directory_uri() . '/assets/images/banner-fallback.webp';
}

function ltdh_get_school_unique_majors_count( int $school_id ): int {
	$cache_key = 'ltdh_school_majors_count_' . $school_id;
	$cached    = wp_cache_get( $cache_key, 'ltdh' );
	if ( false !== $cached ) {
		return (int) $cached;
	}

	$allowed_slugs = [ 'chinh-quy', 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ];
	$programs      = get_posts( [
		'post_type'      => 'program',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [
			[
				'key'     => 'school_relationship',
				'value'   => $school_id,
				'compare' => '=',
			],
		],
		'tax_query'      => [
			[
				'taxonomy' => LTDH_TAX_TRAINING_TYPE,
				'field'    => 'slug',
				'terms'    => $allowed_slugs,
				'operator' => 'IN',
			],
		],
	] );

	if ( empty( $programs ) ) {
		$offered_ids = get_post_meta( $school_id, '_offered_programs', true );
		if ( ! empty( $offered_ids ) && is_array( $offered_ids ) ) {
			$programs = get_posts( [
				'post_type'      => 'program',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'post__in'       => $offered_ids,
				'tax_query'      => [
					[
						'taxonomy' => LTDH_TAX_TRAINING_TYPE,
						'field'    => 'slug',
						'terms'    => $allowed_slugs,
						'operator' => 'IN',
					],
				],
			] );
		}
	}

	if ( empty( $programs ) ) {
		wp_cache_set( $cache_key, 0, 'ltdh', HOUR_IN_SECONDS );
		return 0;
	}

	$major_ids = [];
	foreach ( $programs as $prog_id ) {
		$major_rel = get_post_meta( $prog_id, 'major_relationship', true );
		if ( is_array( $major_rel ) ) {
			$major_rel = ! empty( $major_rel ) ? ( is_object( $major_rel[0] ) ? $major_rel[0]->ID : $major_rel[0] ) : 0;
		} elseif ( is_object( $major_rel ) ) {
			$major_rel = $major_rel->ID;
		}
		$major_id = intval( $major_rel );
		if ( $major_id && ! in_array( $major_id, $major_ids, true ) ) {
			$major_ids[] = $major_id;
		}
	}

	$count = count( $major_ids );
	wp_cache_set( $cache_key, $count, 'ltdh', HOUR_IN_SECONDS );
	return $count;
}

/**
 * Get count of valid, actively offered programs linked to a major.
 * Only counts published programs linked to published schools and not marked as 'tam-ngung'.
 *
 * @param int $major_id
 * @return int
 */
function ltdh_get_major_valid_programs_count( int $major_id ): int {
	if ( ! $major_id ) {
		return 0;
	}

	$cache_key = 'ltdh_major_programs_count_' . $major_id;
	$cached    = wp_cache_get( $cache_key, 'ltdh' );
	if ( false !== $cached ) {
		return (int) $cached;
	}

	$offered_program_ids = get_post_meta( $major_id, defined( 'LTDH_META_OFFERED_PROGRAMS' ) ? LTDH_META_OFFERED_PROGRAMS : '_offered_programs', true );
	$major_rel_key       = defined( 'LTDH_META_MAJOR_REL' ) ? LTDH_META_MAJOR_REL : 'major_relationship';
	$program_cpt         = defined( 'LTDH_CPT_PROGRAM' ) ? LTDH_CPT_PROGRAM : 'program';
	$school_rel_key      = defined( 'LTDH_META_SCHOOL_REL' ) ? LTDH_META_SCHOOL_REL : 'school_relationship';
	$status_key          = defined( 'LTDH_META_ADMISSION_STATUS' ) ? LTDH_META_ADMISSION_STATUS : 'admission_status';

	$direct_prog_ids = get_posts( [
		'post_type'     => $program_cpt,
		'post_status'   => 'publish',
		'numberposts'   => -1,
		'fields'        => 'ids',
		'no_found_rows' => true,
		'meta_query'    => [
			'relation' => 'OR',
			[
				'key'     => $major_rel_key,
				'value'   => $major_id,
				'compare' => '=',
			],
			[
				'key'     => $major_rel_key,
				'value'   => '"' . $major_id . '"',
				'compare' => 'LIKE',
			],
			[
				'key'     => $major_rel_key,
				'value'   => 's:' . strlen( (string) $major_id ) . ':"' . $major_id . '"',
				'compare' => 'LIKE',
			],
			[
				'key'     => $major_rel_key,
				'value'   => 'i:' . $major_id . ';',
				'compare' => 'LIKE',
			],
		],
	] );

	$all_candidate_ids = array_unique( array_filter( array_merge(
		is_array( $offered_program_ids ) ? $offered_program_ids : [],
		is_array( $direct_prog_ids ) ? $direct_prog_ids : []
	) ) );

	if ( empty( $all_candidate_ids ) ) {
		wp_cache_set( $cache_key, 0, 'ltdh', HOUR_IN_SECONDS );
		return 0;
	}

	$meta_query = [
		'relation' => 'AND',
		[
			'relation' => 'OR',
			[
				'key'     => $status_key,
				'value'   => 'tam-ngung',
				'compare' => '!=',
			],
			[
				'key'     => $status_key,
				'compare' => 'NOT EXISTS',
			],
		],
	];

	$programs = get_posts( [
		'post_type'      => $program_cpt,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'post__in'       => $all_candidate_ids,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => $meta_query,
	] );

	$count = 0;
	foreach ( $programs as $p_id ) {
		$school_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $p_id ) : intval( get_post_meta( $p_id, $school_rel_key, true ) );
		if ( $school_id > 0 && 'publish' === get_post_status( $school_id ) ) {
			$count++;
		}
	}

	wp_cache_set( $cache_key, $count, 'ltdh', HOUR_IN_SECONDS );
	return $count;
}

/**
 * Get count of distinct schools actively offering/admitting this major.
 * Only counts published schools with active (non tam-ngung) programs.
 *
 * @param int $major_id
 * @return int
 */
function ltdh_get_major_unique_schools_count( int $major_id ): int {
	if ( ! $major_id ) {
		return 0;
	}

	$cache_key = 'ltdh_major_schools_count_' . $major_id;
	$cached    = wp_cache_get( $cache_key, 'ltdh' );
	if ( false !== $cached ) {
		return (int) $cached;
	}

	$offered_program_ids = get_post_meta( $major_id, defined( 'LTDH_META_OFFERED_PROGRAMS' ) ? LTDH_META_OFFERED_PROGRAMS : '_offered_programs', true );
	$major_rel_key       = defined( 'LTDH_META_MAJOR_REL' ) ? LTDH_META_MAJOR_REL : 'major_relationship';
	$program_cpt         = defined( 'LTDH_CPT_PROGRAM' ) ? LTDH_CPT_PROGRAM : 'program';
	$school_rel_key      = defined( 'LTDH_META_SCHOOL_REL' ) ? LTDH_META_SCHOOL_REL : 'school_relationship';
	$status_key          = defined( 'LTDH_META_ADMISSION_STATUS' ) ? LTDH_META_ADMISSION_STATUS : 'admission_status';

	$direct_prog_ids = get_posts( [
		'post_type'     => $program_cpt,
		'post_status'   => 'publish',
		'numberposts'   => -1,
		'fields'        => 'ids',
		'no_found_rows' => true,
		'meta_query'    => [
			'relation' => 'OR',
			[
				'key'     => $major_rel_key,
				'value'   => $major_id,
				'compare' => '=',
			],
			[
				'key'     => $major_rel_key,
				'value'   => '"' . $major_id . '"',
				'compare' => 'LIKE',
			],
			[
				'key'     => $major_rel_key,
				'value'   => 's:' . strlen( (string) $major_id ) . ':"' . $major_id . '"',
				'compare' => 'LIKE',
			],
			[
				'key'     => $major_rel_key,
				'value'   => 'i:' . $major_id . ';',
				'compare' => 'LIKE',
			],
		],
	] );

	$all_candidate_ids = array_unique( array_filter( array_merge(
		is_array( $offered_program_ids ) ? $offered_program_ids : [],
		is_array( $direct_prog_ids ) ? $direct_prog_ids : []
	) ) );

	if ( empty( $all_candidate_ids ) ) {
		wp_cache_set( $cache_key, 0, 'ltdh', HOUR_IN_SECONDS );
		return 0;
	}

	$meta_query = [
		'relation' => 'AND',
		[
			'relation' => 'OR',
			[
				'key'     => $status_key,
				'value'   => 'tam-ngung',
				'compare' => '!=',
			],
			[
				'key'     => $status_key,
				'compare' => 'NOT EXISTS',
			],
		],
	];

	$programs = get_posts( [
		'post_type'      => $program_cpt,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'post__in'       => $all_candidate_ids,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => $meta_query,
	] );

	$school_ids = [];
	foreach ( $programs as $p_id ) {
		$school_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $p_id ) : intval( get_post_meta( $p_id, $school_rel_key, true ) );
		if ( $school_id > 0 && 'publish' === get_post_status( $school_id ) && ! in_array( $school_id, $school_ids, true ) ) {
			$school_ids[] = $school_id;
		}
	}

	$count = count( $school_ids );
	wp_cache_set( $cache_key, $count, 'ltdh', HOUR_IN_SECONDS );
	return $count;
}

function ltdh_get_training_type_badge_html( string $type_name, string $type_slug = '' ): string {
	if ( ! $type_name ) {
		return '';
	}
	$name_lower = mb_strtolower( trim( $type_name ) );
	$slug_lower = sanitize_title( $type_slug ?: $type_name );

	if ( in_array( $slug_lower, [ 'tu-xa', 'dao-tao-tu-xa', 'online' ], true ) || false !== strpos( $name_lower, 'từ xa' ) || false !== strpos( $name_lower, 'online' ) ) {
		$badge_class = 'bg-sky-50 text-sky-700 border-sky-200';
	} elseif ( in_array( $slug_lower, [ 'vua-hoc-vua-lam', 'vhvl', 'vua-lam-vua-hoc' ], true ) || false !== strpos( $name_lower, 'vừa học vừa làm' ) || false !== strpos( $name_lower, 'vừa làm vừa học' ) ) {
		$badge_class = 'bg-amber-50 text-amber-700 border-amber-200';
	} elseif ( in_array( $slug_lower, [ 'chinh-quy', 'chinh-quy-tap-trung' ], true ) || false !== strpos( $name_lower, 'chính quy' ) ) {
		$badge_class = 'bg-emerald-50 text-emerald-700 border-emerald-200';
	} else {
		$badge_class = 'bg-blue-50 text-[#00308b] border-blue-200';
	}

	return sprintf(
		'<span class="%s text-xs font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wide border shadow-xs inline-block leading-normal">%s</span>',
		esc_attr( $badge_class ),
		esc_html( $type_name )
	);
}

/**
 * Get dynamic program tuition display with fallback.
 */
function ltdh_get_program_tuition_display(int $program_id, bool $include_year = false): string {
	$amount        = get_field('tuition_amount', $program_id);
	$unit          = get_field('tuition_unit', $program_id);
	$academic_year = get_field('tuition_academic_year', $program_id);

	if (! empty($amount)) {
		$unit_label = 'đ';
		if ($unit === 'tin-chi') {
			$unit_label = 'đ/tín chỉ';
		} elseif ($unit === 'hoc-ky') {
			$unit_label = 'đ/học kỳ';
		} elseif ($unit === 'nam') {
			$unit_label = 'đ/năm';
		}
		
		$year_suffix = ($include_year && ! empty($academic_year)) ? ' · ' . $academic_year : '';
		return number_format((float) $amount, 0, ',', '.') . ' ' . $unit_label . $year_suffix;
	}

	// Fallback to original tuition_fee
	$legacy_tuition = get_field('tuition_fee', $program_id);
	return ! empty($legacy_tuition) ? $legacy_tuition : 'Liên hệ';
}

/**
 * Lấy trạng thái tuyển sinh thực tế của chương trình đào tạo.
 * Tự động tính toán (derived) trực tiếp từ các đợt tuyển sinh (admission_batches):
 * - Nếu tất cả các đợt đã đóng -> 'tam-ngung' (Đã hết chỉ tiêu)
 * - Nếu có ít nhất 1 đợt đang nhận hồ sơ -> 'tuyen-sinh' (Đang tuyển sinh / Còn chỉ tiêu)
 * - Nếu có đợt sắp mở (và không có đợt mở) -> 'sap-mo' (Sắp mở tuyển sinh)
 * - Nếu chưa cấu hình đợt tuyển sinh -> fallback về postmeta 'admission_status' hoặc mặc định 'tuyen-sinh'
 *
 * @param int $program_id ID của bài viết program.
 * @return string 'tuyen-sinh' | 'tam-ngung' | 'sap-mo'
 */
function ltdh_get_program_admission_status( int $program_id ): string {
	$batches = function_exists( 'ltdh_get_program_admission_batches' )
		? ltdh_get_program_admission_batches( $program_id )
		: ( get_field( 'admission_batches', $program_id ) ?: [] );

	if ( is_array( $batches ) && ! empty( $batches ) ) {
		$has_open     = false;
		$has_upcoming = false;
		$all_closed   = true;

		foreach ( $batches as $b ) {
			$b_status = $b['batch_status'] ?? 'dang-nhan';
			if ( 'dang-nhan' === $b_status ) {
				$has_open   = true;
				$all_closed = false;
			} elseif ( 'sap-mo' === $b_status ) {
				$has_upcoming = true;
				$all_closed   = false;
			}
		}

		// Nếu TẤT CẢ các đợt đã đóng -> Bắt buộc là 'tam-ngung' (Đã hết chỉ tiêu)
		if ( $all_closed ) {
			return 'tam-ngung';
		}

		// Nếu có ít nhất 1 đợt đang nhận hồ sơ -> 'tuyen-sinh' (Đang tuyển sinh)
		if ( $has_open ) {
			return 'tuyen-sinh';
		}

		// Nếu có đợt sắp mở -> 'sap-mo' (Sắp mở tuyển sinh)
		if ( $has_upcoming ) {
			return 'sap-mo';
		}
	}

	return get_post_meta( $program_id, 'admission_status', true ) ?: 'tuyen-sinh';
}

/**
 * Get dynamic admission deadline display based on batches or enrollment_period.
 */
function ltdh_get_program_admission_deadline_display( int $program_id ): string {
	$legacy_period = trim( (string) ( get_field( 'enrollment_period', $program_id ) ?: get_post_meta( $program_id, 'enrollment_period', true ) ) );

	$batches = function_exists( 'ltdh_get_program_admission_batches' )
		? ltdh_get_program_admission_batches( $program_id )
		: ( get_field( 'admission_batches', $program_id ) ?: [] );

	if ( is_array( $batches ) && ! empty( $batches ) ) {
		$sap_mo_batches = [];
		$all_closed     = true;

		foreach ( $batches as $b ) {
			$status             = $b['batch_status'] ?? '';
			$batch_name         = trim( (string) ( $b['batch_name'] ?? '' ) );
			$clean_name         = trim( preg_replace( '/^Tuyển sinh\s*/ui', '', $batch_name ) );
			$application_period = trim( (string) ( $b['application_period'] ?? '' ) );

			if ( 'dang-nhan' === $status ) {
				// 1. Nếu có ngày nhận hồ sơ cụ thể
				if ( ! empty( $application_period ) && '-' !== $application_period ) {
					return ! empty( $clean_name ) 
						? esc_html( $clean_name ) . ': ' . esc_html( $application_period )
						: esc_html( $application_period );
				}

				// 2. Nếu ngày nhận hồ sơ trong đợt để trống: ưu tiên lấy ô "Hạn hồ sơ" (Tab Tổng quan) nếu có nội dung có nghĩa
				if ( ! empty( $legacy_period ) && ! in_array( $legacy_period, [ 'Đang nhận hồ sơ', 'Tuyển sinh theo đợt' ], true ) ) {
					return esc_html( $legacy_period );
				}

				// 3. Fallback an toàn (tránh vỡ chuỗi "Đợt 1:")
				return ! empty( $clean_name ) 
					? 'Đang nhận hồ sơ (' . esc_html( $clean_name ) . ')'
					: 'Đang nhận hồ sơ';
			}

			if ( 'da-dong' !== $status ) {
				$all_closed = false;
			}

			$sap_mo_batches[] = [
				'status' => $status,
				'name'   => $clean_name,
				'period' => $application_period,
			];
		}

		// Nếu không có đợt đang nhận hồ sơ, kiểm tra đợt sắp mở
		foreach ( $sap_mo_batches as $b ) {
			if ( 'sap-mo' === $b['status'] ) {
				$period = trim( (string) ( $b['period'] ?? '' ) );
				if ( ! empty( $period ) && '-' !== $period ) {
					return ( ! empty( $b['name'] ) ? esc_html( $b['name'] ) . ' (Sắp mở): ' : 'Sắp mở: ' ) . esc_html( $period );
				}
				return ! empty( $b['name'] ) 
					? esc_html( $b['name'] ) . ' (Sắp mở nhận hồ sơ)'
					: 'Sắp mở nhận hồ sơ';
			}
		}

		// Nếu tất cả các đợt đã đóng ('da-dong')
		if ( $all_closed ) {
			return 'Đã hết chỉ tiêu tuyển sinh';
		}
	}

	// Fallback to legacy enrollment_period
	$status = ltdh_get_program_admission_status( $program_id );
	if ( 'tam-ngung' === $status ) {
		return ( ! empty( $legacy_period ) && 'Tuyển sinh theo đợt' !== $legacy_period ) ? esc_html( $legacy_period ) : 'Đã hết chỉ tiêu tuyển sinh';
	}
	return ! empty( $legacy_period ) ? esc_html( $legacy_period ) : 'Đang nhận hồ sơ';
}


// Hot majors helper has been moved to inc/core/class-menus.php

/**
 * Get active training types for a school (with caching and automatic rollup from programs).
 *
 * Rolls up exclusively from published, in-scope programs where
 * school_relationship = $school_id and training type term slug is in ['tu-xa', 'vua-hoc-vua-lam'].
 * Step 1 (checking terms directly on school post) is eliminated.
 *
 * @param int    $school_id      School post ID.
 * @param string $output_format  'names', 'slugs', or 'terms'/'objects'.
 * @return array
 */
function ltdh_get_school_training_types( int $school_id, string $output_format = 'names' ): array {
	if ( ! $school_id ) {
		return [];
	}

	$cache_key = 'ltdh_school_tt_' . $school_id . '_' . $output_format;
	$cached    = wp_cache_get( $cache_key, 'ltdh' );
	if ( false !== $cached ) {
		return (array) $cached;
	}

	$allowed_slugs = [ 'chinh-quy', 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ];

	// Rollup exclusively from published in-scope programs linked to this school
	$query_args = [
		'post_type'      => 'program',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [
			[
				'key'     => 'school_relationship',
				'value'   => $school_id,
				'compare' => '=',
			],
		],
		'tax_query'      => [
			[
				'taxonomy' => LTDH_TAX_TRAINING_TYPE,
				'field'    => 'slug',
				'terms'    => $allowed_slugs,
				'operator' => 'IN',
			],
		],
	];

	$programs = get_posts( $query_args );

	// Fallback to _offered_programs if meta_query returned no programs
	if ( empty( $programs ) ) {
		$offered_ids = get_post_meta( $school_id, '_offered_programs', true );
		if ( ! empty( $offered_ids ) && is_array( $offered_ids ) ) {
			$programs = get_posts( [
				'post_type'      => 'program',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'post__in'       => $offered_ids,
				'tax_query'      => [
					[
						'taxonomy' => LTDH_TAX_TRAINING_TYPE,
						'field'    => 'slug',
						'terms'    => $allowed_slugs,
						'operator' => 'IN',
					],
				],
			] );
		}
	}

	if ( empty( $programs ) ) {
		// Fallback: check training_type terms directly assigned to school post
		$direct_terms = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE );
		if ( ! is_wp_error( $direct_terms ) && ! empty( $direct_terms ) ) {
			$direct_types = [];
			foreach ( $direct_terms as $dt ) {
				if ( ! is_object( $dt ) || ! in_array( $dt->slug, $allowed_slugs, true ) ) {
					continue;
				}
				if ( 'slugs' === $output_format ) {
					$val = $dt->slug;
				} elseif ( 'terms' === $output_format || 'objects' === $output_format || 'all' === $output_format ) {
					$val = $dt;
				} else {
					$val = $dt->name;
				}
				if ( is_object( $val ) ) {
					$direct_types[ $val->term_id ] = $val;
				} else {
					$direct_types[ $val ] = $val;
				}
			}
			if ( ! empty( $direct_types ) ) {
				$res = array_values( $direct_types );
				wp_cache_set( $cache_key, $res, 'ltdh', HOUR_IN_SECONDS );
				return $res;
			}
		}

		wp_cache_set( $cache_key, [], 'ltdh', HOUR_IN_SECONDS );
		return [];
	}

	$school_types = [];
	foreach ( $programs as $prog_id ) {
		$prog_terms = wp_get_post_terms( $prog_id, LTDH_TAX_TRAINING_TYPE );
		if ( ! is_wp_error( $prog_terms ) && ! empty( $prog_terms ) ) {
			foreach ( $prog_terms as $t ) {
				if ( ! is_object( $t ) || ! in_array( $t->slug, $allowed_slugs, true ) ) {
					continue;
				}
				if ( 'slugs' === $output_format ) {
					$val = $t->slug;
				} elseif ( 'terms' === $output_format || 'objects' === $output_format || 'all' === $output_format ) {
					$val = $t;
				} else {
					$val = $t->name;
				}

				if ( is_object( $val ) ) {
					$school_types[ $t->slug ] = $val;
				} elseif ( ! in_array( $val, $school_types, true ) ) {
					$school_types[] = $val;
				}
			}
		}
	}

	$result = ( 'terms' === $output_format || 'objects' === $output_format || 'all' === $output_format )
		? array_values( $school_types )
		: $school_types;

	wp_cache_set( $cache_key, $result, 'ltdh', HOUR_IN_SECONDS );
	return $result;
}

/**
 * Format admission fee display with '/ hồ sơ' unit and currency formatting.
 *
 * @param mixed $fee Raw admission fee value from meta/ACF.
 * @return array Array with keys 'display' and 'is_free'.
 */
function ltdh_format_admission_fee( $fee ): array {
	if ( '' === $fee || null === $fee || false === $fee ) {
		return [ 'display' => '', 'is_free' => false ];
	}
	$fee_str = trim( (string) $fee );
	if ( '' === $fee_str ) {
		return [ 'display' => '', 'is_free' => false ];
	}

	$fee_lower = mb_strtolower( $fee_str, 'UTF-8' );
	$is_free   = in_array( $fee_lower, [ '0', '0đ', '0 đ', '0d', '0 d', 'miễn phí', 'mien phi', 'không thu', 'khong thu', 'miễn lệ phí' ], true );

	if ( $is_free ) {
		return [
			'display' => 'Miễn phí / hồ sơ',
			'is_free' => true,
		];
	}

	// If already contains "hồ sơ" or "ho so"
	if ( preg_match( '/\b(hồ sơ|ho so|hs)\b/iu', $fee_str ) ) {
		return [
			'display' => $fee_str,
			'is_free' => false,
		];
	}

	// Check if it's purely digits (e.g. "200000")
	if ( ctype_digit( $fee_str ) ) {
		return [
			'display' => number_format( (float) $fee_str, 0, ',', '.' ) . ' đ / hồ sơ',
			'is_free' => false,
		];
	}

	// Check if string contains numbers with optional separators (e.g. "200.000" or "200.000đ" or "200,000")
	$clean_num = preg_replace( '/[^\d]/', '', $fee_str );
	if ( is_numeric( $clean_num ) && (float) $clean_num > 0 ) {
		if ( preg_match( '/^[\d.,\s]+(?:đ|vnđ|vnd)?$/iu', $fee_str ) ) {
			return [
				'display' => number_format( (float) $clean_num, 0, ',', '.' ) . ' đ / hồ sơ',
				'is_free' => false,
			];
		}
	}

	// Fallback: append " / hồ sơ"
	return [
		'display' => rtrim( $fee_str, ' /' ) . ' / hồ sơ',
		'is_free' => false,
	];
}

/**
 * Lấy mã viết tắt của trường (school_code) như UTC, TNU, NEU...
 *
 * @param int $school_id
 * @return string
 */
function ltdh_get_school_code( int $school_id ): string {
	if ( ! $school_id ) {
		return '';
	}
	$code = get_post_meta( $school_id, 'school_code', true );
	if ( ! empty( $code ) ) {
		return strtoupper( trim( (string) $code ) );
	}
	if ( function_exists( 'get_field' ) ) {
		$acf_code = get_field( 'school_code', $school_id );
		if ( ! empty( $acf_code ) ) {
			return strtoupper( trim( (string) $acf_code ) );
		}
	}
	return '';
}

/**
 * Lấy danh sách quy định đối tượng tuyển sinh (Ngành đúng / Ngành gần) theo từng trường của ngành đào tạo
 *
 * @param int $major_id
 * @return array Danh sách các trường có quy định ngành gần
 */
function ltdh_get_major_target_admissions( int $major_id ): array {
	$parse_items = function( $val ) {
		if ( is_array( $val ) ) {
			return array_values( array_filter( array_map( 'trim', $val ) ) );
		}
		if ( empty( $val ) ) {
			return [];
		}
		$lines = preg_split( '/[\r\n;]+/', (string) $val );
		return array_values( array_filter( array_map( 'trim', $lines ) ) );
	};

	$results = [];

	// 1. Kiểm tra repeater trong Major CPT: major_school_admission_targets
	$repeater = function_exists( 'get_field' ) ? get_field( 'major_school_admission_targets', $major_id ) : get_post_meta( $major_id, 'major_school_admission_targets', true );

	if ( ! empty( $repeater ) && is_array( $repeater ) ) {
		foreach ( $repeater as $row ) {
			$school_raw   = $row['school'] ?? ( $row['field_target_school'] ?? 0 );
			$school_id    = is_object( $school_raw ) ? $school_raw->ID : (int) $school_raw;
			$school_title = $school_id ? get_the_title( $school_id ) : '';
			$school_slug  = $school_id ? get_post_field( 'post_name', $school_id ) : '';
			$school_badge = $school_id && function_exists( 'ltdh_get_school_code' ) ? ltdh_get_school_code( $school_id ) : '';

			$proper    = $parse_items( $row['proper_majors'] ?? ( $row['field_target_proper_majors'] ?? '' ) );
			$near      = $parse_items( $row['near_majors'] ?? ( $row['field_target_near_majors'] ?? '' ) );
			$note      = trim( (string) ( $row['bridge_note'] ?? ( $row['field_target_bridge_note'] ?? '' ) ) );
			$count     = $row['bridge_count'] ?? ( $row['field_target_bridge_count'] ?? 3 );
			$reg_title = trim( (string) ( $row['regulation_title'] ?? ( $row['field_target_regulation_title'] ?? '' ) ) );

			if ( ! empty( $proper ) || ! empty( $near ) ) {
				$results[] = [
					'school_id'        => $school_id,
					'school_title'     => $school_title ?: ( $reg_title ?: 'Nhà trường' ),
					'school_slug'      => $school_slug,
					'school_badge'     => $school_badge,
					'regulation_title' => $reg_title ?: ( $school_title ? 'Theo Đề án tuyển sinh ' . $school_title : 'Quy chế tuyển sinh' ),
					'proper_majors'    => $proper,
					'near_majors'      => $near,
					'bridge_note'      => $note ?: 'Thí sinh các ngành này phải học bổ sung 03 học phần trong quá trình đào tạo theo yêu cầu của chương trình đào tạo.',
					'bridge_count'     => is_numeric( $count ) ? (int) $count : 3,
				];
			}
		}
	}

	return $results;
}

/**
 * Lấy quy định đối tượng tuyển sinh (ngành đúng & ngành gần) cho một chương trình đào tạo.
 * Kế thừa trực tiếp từ cấu hình của ngành (CPT major) tương ứng với trường đào tạo của chương trình.
 *
 * @param int $program_id ID của chương trình đào tạo.
 * @return array|null Mảng thông tin quy định nếu có, null nếu không có.
 */
function ltdh_get_program_target_admission( int $program_id ): ?array {
	if ( ! $program_id ) {
		return null;
	}

	$school_raw = function_exists( 'get_field' ) ? get_field( LTDH_META_SCHOOL_REL, $program_id ) : get_post_meta( $program_id, LTDH_META_SCHOOL_REL, true );
	$major_raw  = function_exists( 'get_field' ) ? get_field( LTDH_META_MAJOR_REL, $program_id ) : get_post_meta( $program_id, LTDH_META_MAJOR_REL, true );

	$school_id = is_object( $school_raw ) ? (int) $school_raw->ID : ( (int) $school_raw ?: (int) get_post_meta( $program_id, LTDH_META_SCHOOL_REL, true ) );
	$major_id  = is_object( $major_raw ) ? (int) $major_raw->ID : ( (int) $major_raw ?: (int) get_post_meta( $program_id, LTDH_META_MAJOR_REL, true ) );

	if ( ! $major_id ) {
		return null;
	}

	$target_list = function_exists( 'ltdh_get_major_target_admissions' ) ? ltdh_get_major_target_admissions( $major_id ) : [];
	if ( empty( $target_list ) ) {
		return null;
	}

	$target_school_matcher = function( array $target_item, int $target_sch_id, int $p_id ): bool {
		if ( empty( $target_item ) ) {
			return false;
		}

		$row_school_id    = (int) ( $target_item['school_id'] ?? 0 );
		$row_school_slug  = sanitize_title( (string) ( $target_item['school_slug'] ?? '' ) );
		$row_school_badge = strtoupper( trim( (string) ( $target_item['school_badge'] ?? '' ) ) );

		// 1. Khớp trực tiếp School ID
		if ( $target_sch_id > 0 && $row_school_id > 0 && $row_school_id === $target_sch_id ) {
			return true;
		}

		// 2. Khớp School Code / Badge
		if ( $target_sch_id > 0 ) {
			$school_code = strtoupper( trim( (string) ( function_exists( 'ltdh_get_school_code' ) ? ltdh_get_school_code( $target_sch_id ) : get_post_meta( $target_sch_id, 'school_code', true ) ) ) );
			if ( ! empty( $school_code ) && ! empty( $row_school_badge ) && $school_code === $row_school_badge ) {
				return true;
			}

			// 3. Khớp School Slug
			$school_slug = sanitize_title( (string) get_post_field( 'post_name', $target_sch_id ) );
			if ( ! empty( $school_slug ) && ! empty( $row_school_slug ) && ( $school_slug === $row_school_slug || str_contains( $school_slug, $row_school_slug ) || str_contains( $row_school_slug, $school_slug ) ) ) {
				return true;
			}
		}

		// 4. Khớp theo tên chương trình có chứa badge trường (VD: "UTC")
		if ( ! empty( $row_school_badge ) ) {
			$prog_title = strtoupper( get_the_title( $p_id ) );
			if ( str_contains( $prog_title, $row_school_badge ) ) {
				return true;
			}
		}

		return false;
	};

	foreach ( $target_list as $t_candidate ) {
		if ( $target_school_matcher( $t_candidate, $school_id, $program_id ) ) {
			return $t_candidate;
		}
	}

	return null;
}

/**
 * Lấy ID ngành học (CPT major) liên kết với chương trình.
 *
 * @param int $program_id ID của chương trình (CPT program).
 * @return int ID của ngành học hoặc 0 nếu không tìm thấy.
 */
function ltdh_get_program_major_id( int $program_id ): int {
	if ( ! $program_id ) {
		return 0;
	}

	$key      = defined( 'LTDH_META_MAJOR_REL' ) ? LTDH_META_MAJOR_REL : 'major_relationship';
	$major_id = function_exists( 'get_field' ) ? get_field( $key, $program_id ) : 0;
	if ( empty( $major_id ) ) {
		$major_id = get_post_meta( $program_id, $key, true );
	}
	if ( is_array( $major_id ) ) {
		$first    = reset( $major_id );
		$major_id = is_object( $first ) ? $first->ID : intval( $first );
	} elseif ( is_object( $major_id ) ) {
		$major_id = $major_id->ID;
	}

	return intval( $major_id );
}

/**
 * Lấy điều kiện xét tuyển của chương trình với cơ chế DRY fallback về trường.
 *
 * @param int $program_id ID của chương trình (CPT program).
 * @return string HTML/text điều kiện xét tuyển.
 */
function ltdh_get_program_admission_requirements( int $program_id ): string {
	if ( ! $program_id ) {
		return '';
	}

	$prog_req = function_exists( 'get_field' ) ? get_field( 'admission_requirements', $program_id ) : '';
	if ( empty( $prog_req ) ) {
		$prog_req = get_post_meta( $program_id, 'admission_requirements', true );
	}

	if ( ! empty( $prog_req ) && ! empty( trim( strip_tags( (string) $prog_req ) ) ) ) {
		return (string) $prog_req;
	}

	// Fallback to school default
	$school_id = ltdh_get_program_school_id( $program_id );
	if ( $school_id > 0 ) {
		$school_req = function_exists( 'get_field' ) ? get_field( 'default_admission_requirements', $school_id ) : '';
		if ( empty( $school_req ) ) {
			$school_req = get_post_meta( $school_id, 'default_admission_requirements', true );
		}
		if ( ! empty( $school_req ) && ! empty( trim( strip_tags( (string) $school_req ) ) ) ) {
			return (string) $school_req;
		}
	}

	return '';
}

/**
 * Lấy danh mục hồ sơ cần nộp của chương trình với cơ chế DRY fallback về trường.
 *
 * @param int $program_id ID của chương trình (CPT program).
 * @return string HTML/text danh mục hồ sơ.
 */
function ltdh_get_program_required_documents( int $program_id ): string {
	if ( ! $program_id ) {
		return '';
	}

	$prog_docs = function_exists( 'get_field' ) ? get_field( 'required_documents', $program_id ) : '';
	if ( empty( $prog_docs ) ) {
		$prog_docs = get_post_meta( $program_id, 'required_documents', true );
	}

	if ( ! empty( $prog_docs ) && ! empty( trim( strip_tags( (string) $prog_docs ) ) ) ) {
		return (string) $prog_docs;
	}

	// Fallback to school required documents (DRY)
	$school_id = ltdh_get_program_school_id( $program_id );
	if ( $school_id > 0 ) {
		$school_docs = function_exists( 'get_field' ) ? ( get_field( 'required_documents', $school_id ) ?: get_field( 'default_required_documents', $school_id ) ) : '';
		if ( empty( $school_docs ) ) {
			$school_docs = get_post_meta( $school_id, 'required_documents', true ) ?: get_post_meta( $school_id, 'default_required_documents', true );
		}
		if ( ! empty( $school_docs ) && ! empty( trim( strip_tags( (string) $school_docs ) ) ) ) {
			return (string) $school_docs;
		}
	}

	return '';
}

/**
 * Lấy điều kiện xét tuyển chung của trường (CPT school).
 *
 * @param int $school_id ID của trường.
 * @return string HTML/text điều kiện xét tuyển.
 */
function ltdh_get_school_admission_requirements( int $school_id ): string {
	if ( ! $school_id ) {
		return '';
	}

	$req = function_exists( 'get_field' ) ? ( get_field( 'default_admission_requirements', $school_id ) ?: get_field( 'admission_requirements', $school_id ) ) : '';
	if ( empty( $req ) ) {
		$req = get_post_meta( $school_id, 'default_admission_requirements', true ) ?: get_post_meta( $school_id, 'admission_requirements', true );
	}

	if ( ! empty( $req ) && ! empty( trim( strip_tags( (string) $req ) ) ) ) {
		return (string) $req;
	}

	return '<p>Thí sinh đã tốt nghiệp <strong>Trung cấp, Cao đẳng hoặc Đại học</strong> các chuyên ngành đúng hoặc ngành gần theo quy định của Bộ Giáo dục & Đào tạo.</p><p><strong>Hình thức tuyển sinh:</strong> Xét tuyển hồ sơ văn bằng và kết quả học tập (không phải thi tuyển các môn văn hóa).</p>';
}

/**
 * Lấy danh mục hồ sơ xét tuyển cần chuẩn bị của trường (CPT school).
 *
 * @param int $school_id ID của trường.
 * @return string HTML/text danh mục hồ sơ.
 */
function ltdh_get_school_required_documents( int $school_id ): string {
	if ( ! $school_id ) {
		return '';
	}

	$docs = function_exists( 'get_field' ) ? ( get_field( 'default_required_documents', $school_id ) ?: get_field( 'required_documents', $school_id ) ) : '';
	if ( empty( $docs ) ) {
		$docs = get_post_meta( $school_id, 'default_required_documents', true ) ?: get_post_meta( $school_id, 'required_documents', true );
	}

	if ( ! empty( $docs ) && ! empty( trim( strip_tags( (string) $docs ) ) ) ) {
		return (string) $docs;
	}

	return '<ul>
		<li>01 Phiếu đăng ký dự tuyển theo mẫu của nhà trường.</li>
		<li>02 bản sao công chứng Bằng tốt nghiệp Trung cấp / Cao đẳng / Đại học (hoặc Giấy chứng nhận tốt nghiệp tạm thời).</li>
		<li>02 bản sao công chứng Bảng điểm toàn khóa học tương ứng.</li>
		<li>01 bản sao công chứng Căn cước công dân hoặc Hộ chiếu (còn hiệu lực).</li>
		<li>01 bản sao Giấy khai sinh (bản sao trích lục hoặc có công chứng).</li>
		<li>04 ảnh thẻ kích thước 3x4 (chụp trong vòng 6 tháng, ghi rõ họ tên và ngày sinh ở mặt sau).</li>
		<li>Đơn đề nghị xét miễn giảm học phần / chuyển đổi tín chỉ (nếu có).</li>
	</ul>';
}

/**
 * Lấy điều kiện xét tuyển chung của ngành đào tạo (CPT major).
 *
 * @param int $major_id ID của ngành.
 * @param int|null $school_id ID của trường context (nếu có).
 * @return string HTML/text điều kiện xét tuyển.
 */
function ltdh_get_major_admission_requirements( int $major_id, ?int $school_id = null ): string {
	if ( $school_id && $school_id > 0 ) {
		return ltdh_get_school_admission_requirements( $school_id );
	}

	$major_title = $major_id ? get_the_title( $major_id ) : 'ngành đào tạo';

	return '<p>Thí sinh đã tốt nghiệp <strong>Trung cấp, Cao đẳng</strong> khối ngành đúng hoặc ngành gần với ngành <strong>' . esc_html( $major_title ) . '</strong> theo quy định tuyển sinh liên thông của Bộ Giáo dục & Đào tạo.</p><p>Thí sinh đã có bằng <strong>Đại học</strong> các chuyên ngành khác có thể đăng ký học theo diện <strong>Văn bằng 2</strong> để được xét miễn giảm các học phần đại cương tương đương.</p><p><strong>Phương thức tuyển sinh:</strong> Xét tuyển thẳng dựa trên văn bằng và kết quả học tập (bảng điểm toàn khóa).</p>';
}

/**
 * Lấy danh mục hồ sơ cần chuẩn bị của ngành đào tạo (CPT major).
 *
 * @param int $major_id ID của ngành.
 * @param int|null $school_id ID của trường context (nếu có).
 * @return string HTML/text danh mục hồ sơ.
 */
function ltdh_get_major_required_documents( int $major_id, ?int $school_id = null ): string {
	if ( $school_id && $school_id > 0 ) {
		return ltdh_get_school_required_documents( $school_id );
	}

	return '<ul>
		<li>01 Phiếu đăng ký xét tuyển liên thông theo mẫu của trường đào tạo.</li>
		<li>02 bản sao công chứng Bằng tốt nghiệp TC/CĐ/ĐH (hoặc Giấy chứng nhận tốt nghiệp tạm thời).</li>
		<li>02 bản sao công chứng Bảng điểm / Kết quả học tập toàn khóa.</li>
		<li>01 bản sao công chứng Căn cước công dân hoặc Hộ chiếu.</li>
		<li>01 bản sao Giấy khai sinh (bản sao trích lục hoặc công chứng).</li>
		<li>04 ảnh thẻ 3x4 (chụp trong 6 tháng gần nhất).</li>
		<li>Giấy xác nhận ưu tiên hoặc đơn xét miễn giảm môn học (nếu có).</li>
	</ul>';
}

/**
 * Lấy thời gian học của chương trình với cơ chế DRY fallback về trường.
 *
 * @param int $program_id ID của chương trình (CPT program).
 * @return string Khoảng thời gian đào tạo (ví dụ: '1.5 - 2 năm').
 */
function ltdh_get_program_duration( int $program_id ): string {
	if ( ! $program_id ) {
		return '1.5 - 2 năm';
	}

	$dur = function_exists( 'get_field' ) ? get_field( 'duration', $program_id ) : '';
	if ( empty( $dur ) ) {
		$dur = get_post_meta( $program_id, 'duration', true );
	}

	if ( ! empty( $dur ) && ! empty( trim( (string) $dur ) ) ) {
		return trim( (string) $dur );
	}

	// Fallback to school duration (DRY)
	$school_id = ltdh_get_program_school_id( $program_id );
	if ( $school_id > 0 ) {
		$school_dur = function_exists( 'get_field' ) ? get_field( 'duration', $school_id ) : '';
		if ( empty( $school_dur ) ) {
			$school_dur = get_post_meta( $school_id, 'duration', true );
		}
		if ( ! empty( $school_dur ) && ! empty( trim( (string) $school_dur ) ) ) {
			return trim( (string) $school_dur );
		}
	}

	return '1.5 - 2 năm';
}

/**
 * Lấy lộ trình tăng học phí / ghi chú học phí của chương trình với cơ chế DRY fallback về trường.
 *
 * @param int $program_id ID của chương trình (CPT program).
 * @return string Lộ trình hoặc ghi chú học phí.
 */
function ltdh_get_program_tuition_roadmap( int $program_id ): string {
	if ( ! $program_id ) {
		return '';
	}

	$roadmap = function_exists( 'get_field' ) ? get_field( 'tuition_increase_roadmap', $program_id ) : '';
	if ( empty( $roadmap ) ) {
		$roadmap = get_post_meta( $program_id, 'tuition_increase_roadmap', true );
	}

	if ( ! empty( $roadmap ) && ! empty( trim( (string) $roadmap ) ) ) {
		return trim( (string) $roadmap );
	}

	// Fallback to school tuition increase roadmap (DRY)
	$school_id = ltdh_get_program_school_id( $program_id );
	if ( $school_id > 0 ) {
		$school_roadmap = function_exists( 'get_field' ) ? get_field( 'tuition_increase_roadmap', $school_id ) : '';
		if ( empty( $school_roadmap ) ) {
			$school_roadmap = get_post_meta( $school_id, 'tuition_increase_roadmap', true );
		}
		if ( ! empty( $school_roadmap ) && ! empty( trim( (string) $school_roadmap ) ) ) {
			return trim( (string) $school_roadmap );
		}
	}

	return '';
}

/**
 * Lấy lệ phí xét tuyển của chương trình với cơ chế DRY fallback về trường.
 *
 * @param int $program_id ID của chương trình (CPT program).
 * @return mixed Chuỗi lệ phí hoặc giá trị số, rỗng nếu không có.
 */
function ltdh_get_program_admission_fee( int $program_id ) {
	if ( ! $program_id ) {
		return '';
	}

	$fee = function_exists( 'get_field' ) ? get_field( 'admission_fee', $program_id ) : '';
	if ( '' === $fee || null === $fee || false === $fee ) {
		$fee = get_post_meta( $program_id, 'admission_fee', true );
	}

	if ( ! empty( $fee ) || ( is_numeric( $fee ) && (int) $fee === 0 ) ) {
		return $fee;
	}

	// Fallback to school default
	$school_id = ltdh_get_program_school_id( $program_id );
	if ( $school_id > 0 ) {
		$default_fee = function_exists( 'get_field' ) ? get_field( 'default_admission_fee', $school_id ) : '';
		if ( '' === $default_fee || null === $default_fee || false === $default_fee ) {
			$default_fee = get_post_meta( $school_id, 'default_admission_fee', true );
		}
		if ( ! empty( $default_fee ) || ( is_numeric( $default_fee ) && (int) $default_fee === 0 ) ) {
			return $default_fee;
		}
	}

	return '';
}

/**
 * Lấy loại danh hiệu văn bằng tốt nghiệp của chương trình với cơ chế DRY kế thừa từ Ngành học (Major).
 *
 * Thứ tự ưu tiên:
 * 1. Chương trình (CPT program) có giá trị ghi đè riêng (nếu có).
 * 2. Ngành học (CPT major) có cấu hình degree_type chuẩn.
 * 3. Fallback: "Cử nhân [Tên ngành]" hoặc "Cử nhân / Kỹ sư".
 *
 * @param int $program_id ID của chương trình (CPT program).
 * @return string Danh hiệu văn bằng tốt nghiệp.
 */
function ltdh_get_program_degree_type( int $program_id ): string {
	if ( ! $program_id ) {
		return 'Cử nhân / Kỹ sư';
	}

	// 1. Cấp Program: kiểm tra xem có giá trị ghi đè không
	$prog_degree = function_exists( 'get_field' ) ? get_field( 'degree_type', $program_id ) : '';
	if ( empty( $prog_degree ) ) {
		$prog_degree = get_post_meta( $program_id, 'degree_type', true );
	}

	if ( ! empty( $prog_degree ) && ! empty( trim( (string) $prog_degree ) ) ) {
		return trim( (string) $prog_degree );
	}

	// 2. Cấp Major (DRY): Kế thừa từ bài viết Ngành đào tạo
	$major_id = ltdh_get_program_major_id( $program_id );
	if ( $major_id > 0 ) {
		$major_degree = function_exists( 'get_field' ) ? get_field( 'degree_type', $major_id ) : '';
		if ( empty( $major_degree ) ) {
			$major_degree = get_post_meta( $major_id, 'degree_type', true );
		}
		if ( ! empty( $major_degree ) && ! empty( trim( (string) $major_degree ) ) ) {
			return trim( (string) $major_degree );
		}

		// Fallback tạo từ tên ngành chuẩn
		$major_title = get_the_title( $major_id );
		$clean_major = preg_replace( '/^ngành\s+/iu', '', trim( (string) $major_title ) );
		if ( ! empty( $clean_major ) ) {
			return 'Cử nhân ' . $clean_major;
		}
	}

	return 'Cử nhân / Kỹ sư';
}

/**
 * Lấy quy định và giá trị pháp lý văn bằng của chương trình với cơ chế DRY kế thừa từ Trường đào tạo (School).
 *
 * Thứ tự ưu tiên:
 * 1. Chương trình (CPT program) có giá trị ghi đè riêng (nếu có).
 * 2. Trường đào tạo (CPT school) có cấu hình diploma_value chuẩn.
 * 3. Fallback: Văn bản pháp lý chuẩn Thông tư 27/2019/TT-BGDĐT ráp tên trường tự động.
 *
 * @param int $program_id ID của chương trình (CPT program).
 * @return string Quy định & giá trị pháp lý văn bằng.
 */
function ltdh_get_program_diploma_value( int $program_id ): string {
	if ( ! $program_id ) {
		return '';
	}

	// 1. Cấp Program: kiểm tra xem có giá trị ghi đè không
	$prog_diploma = function_exists( 'get_field' ) ? get_field( 'diploma_value', $program_id ) : '';
	if ( empty( $prog_diploma ) ) {
		$prog_diploma = get_post_meta( $program_id, 'diploma_value', true );
	}

	if ( ! empty( $prog_diploma ) && ! empty( trim( (string) $prog_diploma ) ) ) {
		return trim( (string) $prog_diploma );
	}

	// 2. Cấp School (DRY): Kế thừa từ bài viết Trường đào tạo
	$school_id = ltdh_get_program_school_id( $program_id );
	if ( $school_id > 0 ) {
		$school_diploma = function_exists( 'get_field' ) ? get_field( 'diploma_value', $school_id ) : '';
		if ( empty( $school_diploma ) ) {
			$school_diploma = get_post_meta( $school_id, 'diploma_value', true );
		}
		if ( ! empty( $school_diploma ) && ! empty( trim( (string) $school_diploma ) ) ) {
			return trim( (string) $school_diploma );
		}

		$school_title = get_the_title( $school_id );
		return sprintf(
			'Bằng tốt nghiệp do %s cấp, phôi bằng chuẩn Bộ Giáo dục & Đào tạo. Theo Thông tư 27/2019/TT-BGDĐT, trên văn bằng không ghi hình thức đào tạo, có giá trị pháp lý tương đương bằng chính quy, đủ điều kiện học lên Thạc sĩ/Tiến sĩ hoặc thi tuyển công chức, viên chức nhà nước.',
			esc_html( $school_title )
		);
	}

	return 'Bằng tốt nghiệp chuẩn Bộ Giáo dục & Đào tạo. Theo Thông tư 27/2019/TT-BGDĐT, trên văn bằng không ghi hình thức đào tạo, có giá trị pháp lý tương đương bằng chính quy, đủ điều kiện học lên Thạc sĩ/Tiến sĩ hoặc thi tuyển công chức nhà nước.';
}

/**
 * Lấy tên chuyên ngành hoặc ngành đào tạo chuẩn của chương trình (đã làm sạch tiền tố).
 * Ưu tiên:
 * 1. Chuyên ngành đào tạo hẹp (specialization_title / chuyen_nganh).
 * 2. Ngành đào tạo liên kết (major_relationship).
 * 3. Fallback: Tiêu đề bài viết chương trình đã làm sạch.
 *
 * @param int $program_id ID bài viết chương trình.
 * @return string Tên chuyên ngành / ngành sạch (VD: "Luật thương mại", "Công nghệ thông tin").
 */
function ltdh_get_program_discipline_name( int $program_id = 0 ): string {
	if ( ! $program_id ) {
		$program_id = (int) get_the_ID();
	}
	if ( ! $program_id ) {
		return '';
	}

	// 1. Chuyên ngành đào tạo hẹp (specialization_title / chuyen_nganh) nếu được khai báo riêng
	$spec_title = function_exists( 'get_field' ) ? get_field( 'specialization_title', $program_id ) : '';
	if ( empty( $spec_title ) ) {
		$spec_title = get_post_meta( $program_id, 'specialization_title', true );
	}
	if ( empty( $spec_title ) ) {
		$spec_title = function_exists( 'get_field' ) ? get_field( 'chuyen_nganh', $program_id ) : '';
		if ( empty( $spec_title ) ) {
			$spec_title = get_post_meta( $program_id, 'chuyen_nganh', true );
		}
	}
	if ( ! empty( $spec_title ) && is_string( $spec_title ) ) {
		$clean_spec = preg_replace( '/^(?:chuyên ngành|ngành|cử nhân|kỹ sư)\s+/iu', '', trim( $spec_title ) );
		$clean_spec = preg_replace( '/\s*\([^)]*\)$/u', '', $clean_spec );
		if ( ! empty( $clean_spec ) ) {
			return $clean_spec;
		}
	}

	// 2. Mặc định ăn theo tiêu đề bài viết (post_title) của chương trình
	$raw_title = get_the_title( $program_id );
	if ( ! empty( $raw_title ) ) {
		$clean_title = html_entity_decode( wp_strip_all_tags( (string) $raw_title ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		// Bỏ tên trường hoặc mã trường ở đuôi nếu có (VD: " - ĐH GTVT", " - TMU")
		$school_id   = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $program_id ) : 0;
		$school_name = $school_id ? get_the_title( $school_id ) : '';
		$school_code = $school_id ? ( ( function_exists( 'get_field' ) ? get_field( 'school_code', $school_id ) : '' ) ?: get_post_meta( $school_id, 'school_code', true ) ) : '';

		if ( $school_name ) {
			$school_name = html_entity_decode( wp_strip_all_tags( (string) $school_name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}

		for ( $i = 0; $i < 3; $i++ ) {
			if ( $school_name ) {
				$clean_title = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_name, '/' ) . '$/iu', '', $clean_title );
			}
			if ( ! empty( $school_code ) ) {
				$clean_title = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_code, '/' ) . '$/iu', '', $clean_title );
			}
			$clean_title = preg_replace( '/\s*[-–—]\s*(?:ĐH|Trường ĐH|Đại học|Viện ĐH)[^)]*$/iu', '', $clean_title );

			// Bỏ hệ đào tạo trong ngoặc đơn ở cuối nếu có (VD: "(Từ xa)", "(Chính quy)", "(Đào tạo từ xa)")
			$clean_title = preg_replace( '/\s*\((?:hệ\s+)?(?:từ xa|đào tạo từ xa|vừa học vừa làm|vhvl|chính quy|online|tập trung|liên thông)[^)]*\)$/iu', '', $clean_title );
			$clean_title = trim( $clean_title, " -–—\t\n\r\0\x0B" );
		}

		// Bỏ tiền tố tuyển sinh, liên thông, cử nhân, kỹ sư
		$clean_title = preg_replace( '/^(?:tuyển sinh\s+)+/iu', '', $clean_title );
		$clean_title = preg_replace( '/^(?:liên thông\s+(?:đại học\s+)?(?:ngành\s+)?)/iu', '', $clean_title );
		$clean_title = preg_replace( '/^(?:cử nhân|kỹ sư|ngành)\s+/iu', '', $clean_title );

		$clean_title = trim( $clean_title, " -–—\t\n\r\0\x0B" );

		if ( ! empty( $clean_title ) ) {
			return $clean_title;
		}
	}

	// 3. Fallback: Ngành đào tạo liên kết (major_relationship) nếu tiêu đề rỗng
	$major_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $program_id ) : 0;
	if ( $major_id > 0 ) {
		$major_name  = get_the_title( $major_id );
		$clean_major = preg_replace( '/^(?:ngành|cử nhân|kỹ sư)\s+/iu', '', trim( (string) $major_name ) );
		$clean_major = preg_replace( '/\s*\([^)]*\)$/u', '', $clean_major );
		if ( ! empty( $clean_major ) ) {
			return $clean_major;
		}
	}

	return '';
}

/**
 * Lấy tiêu đề chuẩn cho banner và breadcrumb của chương trình.
 * Công thức: 'Liên thông ' . [Discipline] . ' (' . [Clean Training Type] . ')'
 *
 * @param int $program_id ID bài viết chương trình.
 * @return string Tiêu đề đầy đủ chuẩn (VD: "Liên thông Luật thương mại (Đào tạo từ xa)").
 */
function ltdh_get_program_banner_title( int $program_id = 0 ): string {
	if ( ! $program_id ) {
		$program_id = (int) get_the_ID();
	}
	if ( ! $program_id ) {
		return '';
	}

	$discipline = ltdh_get_program_discipline_name( $program_id );

	$training_types = get_the_terms( $program_id, 'training_type' );
	$training_type  = ( ! empty( $training_types ) && ! is_wp_error( $training_types ) ) ? $training_types[0]->name : '';
	$clean_type     = preg_replace( '/^hệ\s+/iu', '', trim( (string) $training_type ) );
	$type_part      = ! empty( $clean_type ) ? ' (' . $clean_type . ')' : '';

	if ( ! empty( $discipline ) ) {
		return 'Liên thông ' . $discipline . $type_part;
	}

	$school_id   = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $program_id ) : 0;
	$school_name = $school_id ? get_the_title( $school_id ) : '';
	$school_code = $school_id ? ( ( function_exists( 'get_field' ) ? get_field( 'school_code', $school_id ) : '' ) ?: get_post_meta( $school_id, 'school_code', true ) ) : '';

	$raw_prog_title = get_the_title( $program_id );
	$clean_prog     = trim( (string) $raw_prog_title );
	$clean_prog     = preg_replace( '/^(?:tuyển sinh\s+)+/iu', '', $clean_prog );
	$clean_prog     = preg_replace( '/^(?:cử nhân|kỹ sư)\s+/iu', '', $clean_prog );
	if ( $school_name ) {
		$clean_prog = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_name, '/' ) . '$/iu', '', $clean_prog );
	}
	if ( ! empty( $school_code ) ) {
		$clean_prog = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_code, '/' ) . '$/iu', '', $clean_prog );
	}
	if ( ! preg_match( '/^liên thông/iu', $clean_prog ) ) {
		$clean_prog = 'Liên thông ' . $clean_prog;
	}

	return $clean_prog;
}

/**
 * Lấy danh sách các đợt tuyển sinh của chương trình với cơ chế DRY fallback về trường.
 *
 * @param int $program_id ID của chương trình (CPT program).
 * @return array Danh sách các đợt tuyển sinh.
 */
function ltdh_get_program_admission_batches( int $program_id ): array {
	if ( ! $program_id ) {
		return [];
	}

	$batches = function_exists( 'get_field' ) ? get_field( 'admission_batches', $program_id ) : [];
	if ( ! empty( $batches ) && is_array( $batches ) ) {
		return $batches;
	}

	// Fallback to school default admission batches
	$school_id = ltdh_get_program_school_id( $program_id );
	if ( $school_id > 0 ) {
		$school_batches = function_exists( 'get_field' ) ? get_field( 'default_admission_batches', $school_id ) : [];
		if ( ! empty( $school_batches ) && is_array( $school_batches ) ) {
			return $school_batches;
		}
	}

	return [];
}

/**
 * Lấy thông tin quy định miễn môn & chuyển đổi tín chỉ với cơ chế DRY fallback về trường.
 *
 * @param int $program_id ID của chương trình (CPT program).
 * @return array Mảng gồm ['title', 'intro', 'items', 'inherited']
 */
function ltdh_get_program_exemptions( int $program_id ): array {
	$result = [
		'title'     => '',
		'intro'     => '',
		'items'     => [],
		'inherited' => false,
	];

	if ( ! $program_id ) {
		return $result;
	}

	$items = function_exists( 'ltdh_get_field_or_meta' ) 
		? ltdh_get_field_or_meta( 'exemption_items', $program_id ) 
		: ( function_exists( 'get_field' ) ? get_field( 'exemption_items', $program_id ) : get_post_meta( $program_id, 'exemption_items', true ) );

	if ( ! empty( $items ) && is_array( $items ) ) {
		$title = function_exists( 'ltdh_get_field_or_meta' ) 
			? ltdh_get_field_or_meta( 'exemption_title', $program_id ) 
			: ( function_exists( 'get_field' ) ? get_field( 'exemption_title', $program_id ) : get_post_meta( $program_id, 'exemption_title', true ) );
		$intro = function_exists( 'ltdh_get_field_or_meta' ) 
			? ltdh_get_field_or_meta( 'exemption_intro', $program_id ) 
			: ( function_exists( 'get_field' ) ? get_field( 'exemption_intro', $program_id ) : get_post_meta( $program_id, 'exemption_intro', true ) );

		$result['title']     = ! empty( $title ) ? (string) $title : 'Quy định miễn môn & Chuyển đổi tín chỉ';
		$result['intro']     = ! empty( $intro ) ? (string) $intro : '';
		$result['items']     = $items;
		$result['inherited'] = false;
		return $result;
	}

	// Fallback to school default exemptions
	$school_id = ltdh_get_program_school_id( $program_id );
	if ( $school_id > 0 ) {
		$default_items = function_exists( 'ltdh_get_field_or_meta' ) 
			? ( ltdh_get_field_or_meta( 'exemption_items', $school_id ) ?: ltdh_get_field_or_meta( 'default_exemption_items', $school_id ) )
			: ( ( function_exists( 'get_field' ) ? ( get_field( 'exemption_items', $school_id ) ?: get_field( 'default_exemption_items', $school_id ) ) : null ) ?: ( get_post_meta( $school_id, 'exemption_items', true ) ?: get_post_meta( $school_id, 'default_exemption_items', true ) ) );

		if ( ! empty( $default_items ) && is_array( $default_items ) ) {
			$def_title = function_exists( 'ltdh_get_field_or_meta' ) 
				? ( ltdh_get_field_or_meta( 'exemption_title', $school_id ) ?: ltdh_get_field_or_meta( 'default_exemption_title', $school_id ) )
				: ( ( function_exists( 'get_field' ) ? ( get_field( 'exemption_title', $school_id ) ?: get_field( 'default_exemption_title', $school_id ) ) : null ) ?: ( get_post_meta( $school_id, 'exemption_title', true ) ?: get_post_meta( $school_id, 'default_exemption_title', true ) ) );
			$def_intro = function_exists( 'ltdh_get_field_or_meta' ) 
				? ( ltdh_get_field_or_meta( 'exemption_intro', $school_id ) ?: ltdh_get_field_or_meta( 'default_exemption_intro', $school_id ) )
				: ( ( function_exists( 'get_field' ) ? ( get_field( 'exemption_intro', $school_id ) ?: get_field( 'default_exemption_intro', $school_id ) ) : null ) ?: ( get_post_meta( $school_id, 'exemption_intro', true ) ?: get_post_meta( $school_id, 'default_exemption_intro', true ) ) );

			$result['title']     = ! empty( $def_title ) ? (string) $def_title : 'Quy định miễn môn & Chuyển đổi tín chỉ';
			$result['intro']     = ! empty( $def_intro ) ? (string) $def_intro : '';
			$result['items']     = $default_items;
			$result['inherited'] = true;
			return $result;
		}
	}

	return $result;
}

/**
 * Lấy danh sách câu hỏi thường gặp (FAQ) của chương trình với cơ chế DRY thông minh:
 * 1. Nếu chương trình có FAQ riêng -> Ưu tiên dùng FAQ riêng.
 * 2. Nếu chương trình để trống -> Tự động kết hợp FAQ của ngành (major_faq) và FAQ của trường (school_faq / synthesized).
 *
 * @param int $program_id ID của chương trình (CPT program).
 * @return array Mảng các câu hỏi [ ['question' => ..., 'answer' => ...] ]
 */
function ltdh_get_program_faqs( int $program_id ): array {
	if ( ! $program_id ) {
		return [];
	}

	// 1. Program-specific FAQs
	$prog_faqs = function_exists( 'get_field' ) ? get_field( 'faq', $program_id ) : [];
	if ( empty( $prog_faqs ) ) {
		$prog_faqs = get_post_meta( $program_id, 'faq', true );
	}

	if ( ! empty( $prog_faqs ) && is_array( $prog_faqs ) ) {
		$valid = [];
		foreach ( $prog_faqs as $item ) {
			$q = trim( $item['question'] ?? '' );
			$a = trim( $item['answer'] ?? '' );
			if ( ! empty( $q ) && ! empty( $a ) ) {
				$valid[] = [
					'question' => $q,
					'answer'   => $a,
				];
			}
		}
		if ( ! empty( $valid ) ) {
			return $valid;
		}
	}

	// 2. Intelligent DRY Merge: Major FAQs + School FAQs
	$combined       = [];
	$seen_questions = [];

	$major_id  = ltdh_get_program_major_id( $program_id );
	$school_id = ltdh_get_program_school_id( $program_id );

	// 2.1 Lấy FAQ ngành (đặc thù chuyên môn, việc làm, cơ hội nghề nghiệp)
	if ( $major_id > 0 ) {
		$major_raw_faqs = function_exists( 'get_field' ) ? get_field( 'major_faq', $major_id ) : get_post_meta( $major_id, 'major_faq', true );
		if ( ! empty( $major_raw_faqs ) && is_array( $major_raw_faqs ) ) {
			foreach ( $major_raw_faqs as $item ) {
				$q = trim( $item['question'] ?? '' );
				$a = trim( $item['answer'] ?? '' );
				if ( ! empty( $q ) && ! empty( $a ) ) {
					$key = mb_strtolower( preg_replace( '/[^\p{L}\p{N}]/u', '', $q ), 'UTF-8' );
					if ( ! isset( $seen_questions[ $key ] ) ) {
						$seen_questions[ $key ] = true;
						$combined[] = [
							'question' => $q,
							'answer'   => $a,
						];
					}
				}
			}
		}
	}

	// 2.2 Lấy FAQ trường (đặc thù bằng cấp, học phí, địa điểm thi, điều kiện tốt nghiệp)
	if ( $school_id > 0 ) {
		$school_faqs = ltdh_get_school_faqs( $school_id );
		if ( ! empty( $school_faqs ) && is_array( $school_faqs ) ) {
			foreach ( $school_faqs as $item ) {
				$q = trim( $item['question'] ?? '' );
				$a = trim( $item['answer'] ?? '' );
				if ( ! empty( $q ) && ! empty( $a ) ) {
					$key = mb_strtolower( preg_replace( '/[^\p{L}\p{N}]/u', '', $q ), 'UTF-8' );
					if ( ! isset( $seen_questions[ $key ] ) ) {
						$seen_questions[ $key ] = true;
						$combined[] = [
							'question' => $q,
							'answer'   => $a,
						];
					}
				}
			}
		}
	}

	return $combined;
}


/**
 * Lấy danh sách các hình thức đào tạo thực tế có chương trình đang tuyển sinh của một ngành.
 *
 * @param int $major_id ID của ngành (CPT major).
 * @return array Mảng các hình thức: [ 'tu-xa' => [ 'slug' => 'tu-xa', 'name' => 'Đào tạo từ xa', 'dot' => 'bg-emerald-500' ], ... ]
 */
function ltdh_get_major_training_types( int $major_id ): array {
	if ( ! $major_id ) {
		return [];
	}

	$cache_key = 'ltdh_major_training_types_' . $major_id;
	$cached    = wp_cache_get( $cache_key, 'ltdh' );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	$offered_program_ids = get_post_meta( $major_id, LTDH_META_OFFERED_PROGRAMS, true );
	$direct_prog_ids     = get_posts( [
		'post_type'   => LTDH_CPT_PROGRAM,
		'post_status' => 'publish',
		'numberposts' => -1,
		'fields'      => 'ids',
		'meta_query'  => [
			'relation' => 'OR',
			[
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => $major_id,
				'compare' => '=',
			],
			[
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => '"' . $major_id . '"',
				'compare' => 'LIKE',
			],
			[
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => 's:' . strlen( (string) $major_id ) . ':"' . $major_id . '"',
				'compare' => 'LIKE',
			],
			[
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => 'i:' . $major_id . ';',
				'compare' => 'LIKE',
			],
		],
	] );

	$candidate_ids = array_unique( array_filter( array_merge(
		is_array( $offered_program_ids ) ? $offered_program_ids : [],
		is_array( $direct_prog_ids ) ? $direct_prog_ids : []
	) ) );

	$meta_query = [
		'relation' => 'AND',
		[
			'relation' => 'OR',
			[
				'key'     => LTDH_META_ADMISSION_STATUS,
				'value'   => 'tam-ngung',
				'compare' => '!=',
			],
			[
				'key'     => LTDH_META_ADMISSION_STATUS,
				'compare' => 'NOT EXISTS',
			],
		],
	];

	$args = [
		'post_type'      => LTDH_CPT_PROGRAM,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => $meta_query,
	];

	if ( ! empty( $candidate_ids ) ) {
		$args['post__in'] = $candidate_ids;
	} else {
		$meta_query[] = [
			'relation' => 'OR',
			[
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => $major_id,
				'compare' => '=',
			],
			[
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => '"' . $major_id . '"',
				'compare' => 'LIKE',
			],
		];
		$args['meta_query'] = $meta_query;
	}

	$prog_ids = get_posts( $args );
	if ( empty( $prog_ids ) ) {
		wp_cache_set( $cache_key, [], 'ltdh', HOUR_IN_SECONDS );
		return [];
	}

	$available_types = [];

	foreach ( $prog_ids as $p_id ) {
		$school_rel_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $p_id ) : intval( get_post_meta( $p_id, LTDH_META_SCHOOL_REL, true ) );
		if ( ! $school_rel_id || 'publish' !== get_post_status( $school_rel_id ) ) {
			continue;
		}

		$terms = wp_get_post_terms( $p_id, LTDH_TAX_TRAINING_TYPE );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			continue;
		}

		foreach ( $terms as $term ) {
			$raw_slug       = $term->slug;
			$canonical_slug = $raw_slug;
			$dot_class      = 'bg-slate-400';
			$name           = $term->name;

			if ( in_array( $raw_slug, [ 'tu-xa', 'dao-tao-tu-xa' ], true ) ) {
				$canonical_slug = 'tu-xa';
				$name           = 'Đào tạo từ xa';
				$dot_class      = 'bg-blue-600';
			} elseif ( in_array( $raw_slug, [ 'vua-hoc-vua-lam', 'vhvl', 'vua-lam-vua-hoc' ], true ) ) {
				$canonical_slug = 'vua-hoc-vua-lam';
				$name           = 'Vừa học vừa làm';
				$dot_class      = 'bg-amber-500';
			} elseif ( in_array( $raw_slug, [ 'chinh-quy', 'chinh-quy-tap-trung' ], true ) ) {
				$canonical_slug = 'chinh-quy';
				$name           = 'Chính quy';
				$dot_class      = 'bg-indigo-600';
			}

			if ( ! isset( $available_types[ $canonical_slug ] ) ) {
				$available_types[ $canonical_slug ] = [
					'slug' => $canonical_slug,
					'name' => $name,
					'dot'  => $dot_class,
				];
			}
		}
	}

	wp_cache_set( $cache_key, $available_types, 'ltdh', HOUR_IN_SECONDS );
	return $available_types;
}

// ----------------------------------------------------
// 21. Program Verification Helpers
// ----------------------------------------------------

/**
 * Format verification date into Vietnamese standard DD/MM/YYYY.
 *
 * @param mixed $date_raw Date string or timestamp.
 * @return string Formatted date (DD/MM/YYYY) or empty string.
 */
function ltdh_format_verification_date( $date_raw ): string {
	if ( empty( $date_raw ) || ! is_string( $date_raw ) ) {
		return '';
	}
	$trimmed = trim( $date_raw );
	if ( '' === $trimmed ) {
		return '';
	}

	// 1. Format: DD/MM/YYYY
	if ( preg_match( '#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $trimmed, $m ) ) {
		return sprintf( '%02d/%02d/%04d', (int) $m[1], (int) $m[2], (int) $m[3] );
	}

	// 2. Format: YYYYMMDD (ACF datepicker raw)
	if ( preg_match( '#^(\d{4})(\d{2})(\d{2})$#', $trimmed, $m ) ) {
		return sprintf( '%02d/%02d/%04d', (int) $m[3], (int) $m[2], (int) $m[1] );
	}

	// 3. Format: YYYY-MM-DD
	if ( preg_match( '#^(\d{4})-(\d{1,2})-(\d{1,2})$#', $trimmed, $m ) ) {
		return sprintf( '%02d/%02d/%04d', (int) $m[3], (int) $m[2], (int) $m[1] );
	}

	// 4. Try strtotime for standard ISO formats
	$ts = strtotime( $trimmed );
	if ( false !== $ts && $ts > 0 ) {
		return date_i18n( 'd/m/Y', $ts );
	}

	return '';
}

/**
 * Retrieve verified status, date, source and internal notes for a program.
 *
 * @param int $program_id Program post ID.
 * @return array Verification data.
 */
function ltdh_get_program_verification( int $program_id ): array {
	if ( ! $program_id || 'program' !== get_post_type( $program_id ) ) {
		return [
			'status'             => LTDH_VERIF_STATUS_UNVERIFIED,
			'status_label'       => 'Chưa xác minh',
			'is_verified'        => false,
			'is_partial'         => false,
			'is_unverified'      => true,
			'last_verified_at'   => '',
			'last_verified_raw'  => '',
			'source_url'         => '',
			'source_title'       => '',
			'notes'              => '',
		];
	}

	$status = get_post_meta( $program_id, LTDH_META_VERIFICATION_STATUS, true );
	if ( empty( $status ) && function_exists( 'get_field' ) ) {
		$status = get_field( LTDH_META_VERIFICATION_STATUS, $program_id );
	}
	$allowed_statuses = [
		LTDH_VERIF_STATUS_VERIFIED,
		LTDH_VERIF_STATUS_PARTIAL,
		LTDH_VERIF_STATUS_UNVERIFIED,
	];
	if ( ! in_array( $status, $allowed_statuses, true ) ) {
		$status = LTDH_VERIF_STATUS_UNVERIFIED;
	}

	$status_labels = [
		LTDH_VERIF_STATUS_VERIFIED   => 'Đã xác minh',
		LTDH_VERIF_STATUS_PARTIAL    => 'Đã xác minh một phần',
		LTDH_VERIF_STATUS_UNVERIFIED => 'Chưa xác minh',
	];

	// Date: Only use recorded verification date, never fall back to publish or modified date
	$raw_date = get_post_meta( $program_id, LTDH_META_LAST_VERIFIED_AT, true );
	if ( empty( $raw_date ) && function_exists( 'get_field' ) ) {
		$raw_date = get_field( LTDH_META_LAST_VERIFIED_AT, $program_id, false );
	}
	$formatted_date = ltdh_format_verification_date( $raw_date );

	// Source URL: Must be validated HTTP/HTTPS URL
	$raw_url = get_post_meta( $program_id, LTDH_META_VERIFICATION_SOURCE, true );
	if ( empty( $raw_url ) && function_exists( 'get_field' ) ) {
		$raw_url = get_field( LTDH_META_VERIFICATION_SOURCE, $program_id );
	}
	$clean_url = '';
	if ( ! empty( $raw_url ) && is_string( $raw_url ) ) {
		$candidate_url = esc_url_raw( trim( $raw_url ) );
		if ( ! empty( $candidate_url ) && filter_var( $candidate_url, FILTER_VALIDATE_URL ) && preg_match( '#^https?://#i', $candidate_url ) ) {
			$clean_url = $candidate_url;
		}
	}

	// Source Title
	$source_title = get_post_meta( $program_id, LTDH_META_VERIFICATION_SOURCE_TITLE, true );
	if ( empty( $source_title ) && function_exists( 'get_field' ) ) {
		$source_title = get_field( LTDH_META_VERIFICATION_SOURCE_TITLE, $program_id );
	}
	$clean_title = ! empty( $source_title ) && is_string( $source_title ) ? trim( sanitize_text_field( $source_title ) ) : '';

	// Notes (internal only)
	$notes = get_post_meta( $program_id, LTDH_META_VERIFICATION_NOTES, true );
	if ( empty( $notes ) && function_exists( 'get_field' ) ) {
		$notes = get_field( LTDH_META_VERIFICATION_NOTES, $program_id );
	}
	$clean_notes = ! empty( $notes ) && is_string( $notes ) ? trim( sanitize_textarea_field( $notes ) ) : '';

	return [
		'status'            => $status,
		'status_label'      => $status_labels[ $status ] ?? 'Chưa xác minh',
		'is_verified'       => ( LTDH_VERIF_STATUS_VERIFIED === $status ),
		'is_partial'        => ( LTDH_VERIF_STATUS_PARTIAL === $status ),
		'is_unverified'     => ( LTDH_VERIF_STATUS_UNVERIFIED === $status ),
		'last_verified_at'  => $formatted_date,
		'last_verified_raw' => is_string( $raw_date ) ? trim( $raw_date ) : '',
		'source_url'        => $clean_url,
		'source_title'      => $clean_title,
		'notes'             => $clean_notes,
	];
}

/**
 * Get verification badge HTML for a program.
 *
 * @param int    $program_id Program post ID.
 * @param string $context    'detail' for single page, 'card' for listing cards.
 * @return string HTML output or empty string.
 */
function ltdh_get_program_verification_badge_html( int $program_id, string $context = 'detail' ): string {
	$data = ltdh_get_program_verification( $program_id );

	if ( 'card' === $context ) {
		if ( ! empty( $data['is_verified'] ) ) {
			return '<span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-md shadow-2xs select-none" title="Thông tin chương trình đã được xác minh">'
				. '<svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>'
				. '<span>Đã xác minh</span>'
				. '</span>';
		}
		return '';
	}

	// Single Program Detail Page
	if ( ! empty( $data['is_verified'] ) ) {
		$source_label = ! empty( $data['source_title'] ) ? $data['source_title'] : 'Xem nguồn chính thức';

		$meta_items = [];
		if ( ! empty( $data['last_verified_at'] ) ) {
			$meta_items[] = '<span class="inline-flex items-center gap-1.5">'
				. '<span class="text-emerald-700/80">Cập nhật xác minh:</span>'
				. '<strong class="font-bold text-emerald-950">' . esc_html( $data['last_verified_at'] ) . '</strong>'
				. '</span>';
		}
		if ( ! empty( $data['source_url'] ) ) {
			$meta_items[] = '<span class="inline-flex items-center gap-1.5">'
				. '<span class="text-emerald-700/80">Nguồn thông tin:</span>'
				. '<a href="' . esc_url( $data['source_url'] ) . '" target="_blank" rel="noopener noreferrer nofollow" class="font-bold text-emerald-800 hover:text-emerald-950 underline underline-offset-2 transition-colors inline-flex items-center gap-0.5">'
				. '<span>' . esc_html( $source_label ) . '</span>'
				. '<svg class="w-3 h-3 ml-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>'
				. '</a>'
				. '</span>';
		}

		$meta_html = '';
		if ( ! empty( $meta_items ) ) {
			$meta_html = '<div class="mt-2.5 pt-2.5 border-t border-emerald-200/70 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-emerald-800">'
				. implode( '<span class="text-emerald-300 hidden sm:inline select-none" aria-hidden="true">•</span>', $meta_items )
				. '</div>';
		}

		return '<div class="ltdh-verification-badge mb-5 rounded-2xl border border-emerald-200/90 bg-emerald-50/70 p-3.5 sm:p-4 text-emerald-950 shadow-2xs">'
			. '<div class="flex items-center gap-2.5 min-w-0">'
			. '<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-black shrink-0 shadow-xs" aria-hidden="true">✓</span>'
			. '<span class="font-extrabold text-sm sm:text-base text-emerald-950 tracking-tight leading-snug">Thông tin chương trình đã được xác minh</span>'
			. '</div>'
			. $meta_html
			. '</div>';
	}

	if ( ! empty( $data['is_partial'] ) ) {
		return '<div class="ltdh-verification-partial mb-5 rounded-2xl border border-amber-200/90 bg-amber-50/70 p-3.5 sm:p-4 text-amber-950 shadow-2xs">'
			. '<div class="flex items-center gap-2.5">'
			. '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-500 text-white text-xs font-bold shrink-0" aria-hidden="true">!</span>'
			. '<span class="font-semibold text-xs sm:text-sm text-amber-900 leading-snug">Một số thông tin chương trình chưa được xác minh đầy đủ.</span>'
			. '</div>'
			. '</div>';
	}

	// Unverified programs output nothing public, per specification.
	return '';
}

/**
 * Render verification information badge on frontend.
 *
 * @param int    $program_id Program post ID.
 * @param string $context    'detail' for single page, 'card' for listing cards.
 */
function ltdh_render_program_verification_badge( int $program_id, string $context = 'detail' ): void {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo ltdh_get_program_verification_badge_html( $program_id, $context );
}

/**
 * Retrieve FAQ items for a School.
 *
 * Checks for custom FAQs entered in ACF ('school_faq' or 'faq').
 * If not set or empty, dynamically synthesizes high-value, school-specific
 * FAQ items contextualized with school name, training types, degree legal validity,
 * entrance requirements, and tuition policies.
 *
 * @param int $school_id School post ID.
 * @return array Array of associative arrays with 'question' and 'answer' keys.
 */
function ltdh_get_school_faqs( int $school_id ): array {
	if ( ! $school_id ) {
		return [];
	}

	// 1. Check custom ACF FAQs on school post
	$custom_faqs = function_exists( 'get_field' ) 
		? ( get_field( 'school_faq', $school_id ) ?: get_field( 'faq', $school_id ) )
		: get_post_meta( $school_id, 'school_faq', true );

	if ( ! empty( $custom_faqs ) && is_array( $custom_faqs ) ) {
		$valid_faqs = [];
		foreach ( $custom_faqs as $item ) {
			$q = trim( $item['question'] ?? '' );
			$a = trim( $item['answer'] ?? '' );
			if ( ! empty( $q ) && ! empty( $a ) ) {
				$valid_faqs[] = [
					'question' => $q,
					'answer'   => $a,
				];
			}
		}
		if ( ! empty( $valid_faqs ) ) {
			return $valid_faqs;
		}
	}

	// 2. Synthesize school-specific intelligent fallback FAQs
	$school_name = get_the_title( $school_id );
	if ( empty( $school_name ) ) {
		return [];
	}

	$school_code  = get_post_meta( $school_id, 'school_code', true ) ?: '';
	$school_label = $school_code ? sprintf( '%s (%s)', $school_name, $school_code ) : $school_name;

	$training_types = function_exists( 'ltdh_get_school_training_types' ) 
		? ltdh_get_school_training_types( $school_id ) 
		: [];

	$types_str = ! empty( $training_types ) 
		? implode( ', ', array_map( function( $t ) { return preg_replace( '/^hệ\s+/iu', '', trim( $t ) ); }, $training_types ) )
		: 'Đào tạo từ xa, Vừa học vừa làm';

	return [
		[
			'question' => sprintf( 'Bằng tốt nghiệp Liên thông Đại học của %s có ghi hình thức đào tạo không?', $school_name ),
			'answer'   => sprintf(
				'<p class="mb-2"><strong>Không.</strong> Căn cứ theo Thông tư số 27/2019/TT-BGDĐT của Bộ Giáo dục & Đào tạo, từ ngày 01/03/2020 trên văn bằng tốt nghiệp Đại học của <strong>%s</strong> hoàn toàn KHÔNG ghi hình thức đào tạo. Giá trị pháp lý văn bằng được quy định như sau:</p>' .
				'<ul class="space-y-2 my-2.5 list-none pl-0">' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">✓</span><span><strong>Giá trị tương đương:</strong> Bằng tốt nghiệp hệ Từ xa hoặc Vừa học vừa làm có giá trị công nhận tương đương bằng Đại học chính quy.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">✓</span><span><strong>Đầy đủ quyền lợi pháp lý:</strong> Được sử dụng để thi công chức, viên chức, xét nâng lương, nâng bậc ngạch theo đúng quy định Nhà nước.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">✓</span><span><strong>Học lên cao học:</strong> Đủ điều kiện đăng ký thi tuyển và theo học các chương trình Thạc sĩ, Tiến sĩ tại mọi cơ sở giáo dục đại học trong và ngoài nước.</span></li>' .
				'</ul>',
				$school_name
			),
		],
		[
			'question' => sprintf( 'Tốt nghiệp Cao đẳng nghề hoặc tốt nghiệp trái ngành có được học Liên thông tại %s không?', $school_name ),
			'answer'   => sprintf(
				'<p class="mb-2"><strong>Có.</strong> <strong>%s</strong> tiếp nhận xét tuyển rộng rãi các đối tượng người học theo các tiêu chí:</p>' .
				'<ul class="space-y-2 my-2.5 list-none pl-0">' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold shrink-0 mt-0.5">1</span><span><strong>Cao đẳng chính quy & Cao đẳng nghề:</strong> Thí sinh tốt nghiệp Cao đẳng ở mọi hình thức đào tạo đều được đăng ký xét tuyển liên thông.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold shrink-0 mt-0.5">2</span><span><strong>Trường hợp trái ngành / ngành gần:</strong> Nhà trường hỗ trợ tổ chức học bổ sung kiến thức (chuyển đổi tín chỉ) một số học phần cơ sở trước khi vào học chuyên ngành chính thức.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold shrink-0 mt-0.5">3</span><span><strong>Phương thức xét tuyển:</strong> Xét tuyển thẳng dựa trên văn bằng và bảng điểm đã có, không phải thi tuyển đầu vào áp lực.</span></li>' .
				'</ul>',
				$school_name
			),
		],
		[
			'question' => sprintf( 'Hình thức học và thi kết thúc học phần tại %s diễn ra như thế nào?', $school_name ),
			'answer'   => sprintf(
				'<p class="mb-2">Lịch học và hình thức kiểm tra đánh giá tại <strong>%s</strong> được thiết kế linh hoạt theo từng hình thức đào tạo (<strong>%s</strong>):</p>' .
				'<ul class="space-y-2 my-2.5 list-none pl-0">' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-800 text-[11px] font-bold shrink-0 mt-0.5">1</span><span><strong>Hệ Đào tạo từ xa:</strong> Học 100%% online qua hệ thống E-learning của trường, sinh viên chủ động tự học mọi lúc mọi nơi theo tiến độ cá nhân.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-800 text-[11px] font-bold shrink-0 mt-0.5">2</span><span><strong>Hệ Vừa học vừa làm:</strong> Lịch học bố trí linh hoạt vào các buổi tối trong tuần hoặc Thứ 7 & Chủ nhật, phù hợp người đang đi làm.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-800 text-[11px] font-bold shrink-0 mt-0.5">3</span><span><strong>Thi học phần & tốt nghiệp:</strong> Tổ chức thi tập trung định kỳ tại trụ sở chính của trường hoặc các trạm thi vệ tinh liên kết trên toàn quốc.</span></li>' .
				'</ul>',
				$school_name,
				$types_str
			),
		],
		[
			'question' => sprintf( 'Học phí đào tạo Liên thông tại %s được tính và thu như thế nào?', $school_name ),
			'answer'   => sprintf(
				'<p class="mb-2">Chính sách thu học phí đào tạo Liên thông tại <strong>%s</strong> được thực hiện minh bạch theo các nguyên tắc:</p>' .
				'<ul class="space-y-2 my-2.5 list-none pl-0">' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold shrink-0 mt-0.5">1</span><span><strong>Tính theo tín chỉ thực tế:</strong> Học phí thu theo số tín chỉ sinh viên đăng ký học trong từng học kỳ theo đúng quy định của Nhà nước.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold shrink-0 mt-0.5">2</span><span><strong>Chia đợt đóng linh hoạt:</strong> Đóng học phí theo từng học kỳ, giúp giảm tải tối đa áp lực tài chính cho người học.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold shrink-0 mt-0.5">3</span><span><strong>Không phát sinh phụ phí:</strong> Mọi chi phí đào tạo được thông báo công khai ngay từ đầu khóa và cam kết không phát sinh các khoản phụ phí bất hợp lý.</span></li>' .
				'</ul>',
				$school_name
			),
		],
		[
			'question' => sprintf( 'Hồ sơ đăng ký xét tuyển Liên thông vào %s gồm những giấy tờ gì và nộp ở đâu?', $school_name ),
			'answer'   => sprintf(
				'<p class="mb-2">Hồ sơ đăng ký xét tuyển Liên thông vào <strong>%s</strong> gồm các giấy tờ cơ bản sau:</p>' .
				'<ul class="space-y-2 my-2.5 list-none pl-0">' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">1</span><span><strong>01 Phiếu đăng ký xét tuyển:</strong> Điền đầy đủ thông tin theo mẫu chuẩn của %s.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">2</span><span><strong>02 Bản sao công chứng:</strong> Bằng tốt nghiệp và Bảng điểm Cao đẳng hoặc Trung cấp.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">3</span><span><strong>01 Bản sao công chứng:</strong> Căn cước công dân (CCCD) và 01 Bản sao Giấy khai sinh.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">4</span><span><strong>04 Ảnh thẻ cỡ 3x4:</strong> Chụp rõ mặt trong vòng 6 tháng gần nhất (ghi rõ họ tên, ngày sinh ở mặt sau).</span></li>' .
				'</ul>' .
				'<p class="mt-2 text-xs text-slate-500 italic">Thí sinh có thể gửi trước bản scan / ảnh chụp hồ sơ trực tuyến để được xét duyệt điều kiện, sau đó nộp hồ sơ bản cứng khi nhập học chính thức.</p>',
				$school_name,
				$school_name
			),
		],
	];
}

/**
 * Retrieve FAQ items for a Major.
 *
 * Handles 2 distinct user contexts:
 * 1. Contextualized under a specific School ($school_id provided):
 *    - Looks up program linking this major and school for specific FAQs.
 *    - Falls back to school FAQs if program has no custom FAQs.
 * 2. General Major view ($school_id not provided / null):
 *    - Checks custom ACF FAQs on the major post ('major_faq' or 'faq').
 *    - Synthesizes intelligent orientation & comparative buying-guide FAQs
 *      (Degree type, credit bridge policy, duration, online practice, school comparison).
 *
 * @param int      $major_id  Major post ID.
 * @param int|null $school_id Optional school post ID context.
 * @return array Array of associative arrays with 'question' and 'answer' keys.
 */
function ltdh_get_major_faqs( int $major_id, ?int $school_id = null ): array {
	if ( ! $major_id ) {
		return [];
	}

	// ----------------------------------------------------
	// CASE 1: Contextualized under a specific school
	// ----------------------------------------------------
	if ( ! empty( $school_id ) ) {
		// 1.1 Check if a specific program exists for this school & major
		$progs = get_posts( [
			'post_type'      => 'program',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => 'school_relationship',
					'value'   => $school_id,
					'compare' => '=',
				],
				[
					'key'     => 'major_relationship',
					'value'   => $major_id,
					'compare' => '=',
				],
			],
		] );

		if ( ! empty( $progs ) ) {
			$prog_id   = $progs[0]->ID;
			$prog_faqs = function_exists( 'get_field' ) ? get_field( 'faq', $prog_id ) : get_post_meta( $prog_id, 'faq', true );
			if ( ! empty( $prog_faqs ) && is_array( $prog_faqs ) ) {
				$valid = [];
				foreach ( $prog_faqs as $item ) {
					$q = trim( $item['question'] ?? '' );
					$a = trim( $item['answer'] ?? '' );
					if ( ! empty( $q ) && ! empty( $a ) ) {
						$valid[] = [
							'question' => $q,
							'answer'   => $a,
						];
					}
				}
				if ( ! empty( $valid ) ) {
					return $valid;
				}
			}
		}

		// 1.2 Fallback to school FAQs if no program-specific FAQs
		if ( function_exists( 'ltdh_get_school_faqs' ) ) {
			$sch_faqs = ltdh_get_school_faqs( $school_id );
			if ( ! empty( $sch_faqs ) ) {
				return $sch_faqs;
			}
		}
	}

	// ----------------------------------------------------
	// CASE 2: General Major view (No school context)
	// ----------------------------------------------------
	// 2.1 Check custom ACF FAQs on the major post
	$custom_faqs = function_exists( 'get_field' ) 
		? ( get_field( 'major_faq', $major_id ) ?: get_field( 'faq', $major_id ) )
		: get_post_meta( $major_id, 'major_faq', true );

	if ( ! empty( $custom_faqs ) && is_array( $custom_faqs ) ) {
		$valid_faqs = [];
		foreach ( $custom_faqs as $item ) {
			$q = trim( $item['question'] ?? '' );
			$a = trim( $item['answer'] ?? '' );
			if ( ! empty( $q ) && ! empty( $a ) ) {
				$valid_faqs[] = [
					'question' => $q,
					'answer'   => $a,
				];
			}
		}
		if ( ! empty( $valid_faqs ) ) {
			return $valid_faqs;
		}
	}

	// 2.2 Synthesize high-value orientation & comparative FAQs for this major
	$raw_major_title = get_the_title( $major_id );
	if ( empty( $raw_major_title ) ) {
		return [];
	}

	$clean_major = preg_replace( '/^ngành\s+/iu', '', trim( $raw_major_title ) );
	$major_lower = mb_strtolower( $clean_major, 'UTF-8' );

	// Determine degree credential wording based on field of study
	$is_engineering = (
		false !== strpos( $major_lower, 'công nghệ' ) ||
		false !== strpos( $major_lower, 'kỹ thuật' ) ||
		false !== strpos( $major_lower, 'xây dựng' ) ||
		false !== strpos( $major_lower, 'điện' ) ||
		false !== strpos( $major_lower, 'phần mềm' )
	);

	if ( $is_engineering ) {
		$degree_answer = sprintf(
			'<p class="mb-2">Tùy thuộc vào cơ sở đào tạo và định hướng chương trình của từng trường đại học:</p>' .
			'<ul class="space-y-2 my-2.5 list-none pl-0">' .
			'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">1</span><span><strong>Bằng Kỹ sư:</strong> Các trường khối kỹ thuật chuyên sâu (như Học viện Bưu chính Viễn thông PTIT, ĐH Bách Khoa, ĐH Giao thông Vận tải) thường cấp bằng Kỹ sư ngành %s với khối lượng kiến thức chuyên sâu.</span></li>' .
			'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">2</span><span><strong>Bằng Cử nhân:</strong> Các trường đại học đa ngành (như Viện ĐH Mở, ĐH Thái Nguyên) thường cấp bằng Cử nhân ngành %s tập trung vào ứng dụng thực tiễn.</span></li>' .
			'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">✓</span><span><strong>Giá trị pháp lý:</strong> Cả bằng Cử nhân và Kỹ sư đều chuẩn trình độ Đại học theo khung văn bằng quốc gia, đủ điều kiện thi công chức, xét nâng bậc lương và học lên Thạc sĩ.</span></li>' .
			'</ul>',
			$clean_major,
			$clean_major
		);
	} else {
		$degree_answer = sprintf(
			'<p class="mb-2">Sinh viên tốt nghiệp chương trình Liên thông ngành <strong>%s</strong> được cấp <strong>Bằng Cử nhân</strong> đại học chuẩn quy chế của Bộ Giáo dục & Đào tạo. Giá trị văn bằng cụ thể:</p>' .
			'<ul class="space-y-2 my-2.5 list-none pl-0">' .
			'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">✓</span><span><strong>Không ghi hình thức đào tạo:</strong> Theo Thông tư 27/2019/TT-BGDĐT, bằng không phân biệt Chính quy, Vừa học vừa làm hay Từ xa.</span></li>' .
			'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">✓</span><span><strong>Quyền lợi pháp lý đầy đủ:</strong> Được công nhận toàn quốc để thi tuyển công chức, viên chức, xét nâng ngạch bổ nhiệm cán bộ.</span></li>' .
			'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">✓</span><span><strong>Học lên cao học:</strong> Đủ điều kiện đăng ký học tiếp lên trình độ Thạc sĩ, Tiến sĩ tại mọi trường đại học trong nước và quốc tế.</span></li>' .
			'</ul>',
			$clean_major
		);
	}

	return [
		[
			'question' => sprintf( 'Bằng tốt nghiệp Liên thông ngành %s là bằng Cử nhân hay Kỹ sư?', $clean_major ),
			'answer'   => $degree_answer,
		],
		[
			'question' => sprintf( 'Tốt nghiệp Cao đẳng nghề hoặc trái ngành có được học Liên thông ngành %s không?', $clean_major ),
			'answer'   => sprintf(
				'<p class="mb-2"><strong>Có.</strong> Quy chế tuyển sinh hiện hành của Bộ GD&ĐT cho phép người học đăng ký liên thông vào ngành <strong>%s</strong> theo các diện sau:</p>' .
				'<ul class="space-y-2 my-2.5 list-none pl-0">' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold shrink-0 mt-0.5">1</span><span><strong>Tốt nghiệp Cao đẳng đúng ngành:</strong> Xét tuyển thẳng vào chương trình chính khóa, được công nhận tối đa số tín chỉ đã tích lũy ở bậc Cao đẳng.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold shrink-0 mt-0.5">2</span><span><strong>Tốt nghiệp Cao đẳng nghề / Ngành gần / Trái ngành:</strong> Đều đủ điều kiện xét tuyển. Nhà trường sẽ tổ chức học bổ sung chuyển đổi tín chỉ từ 2 - 4 học phần cơ sở trước khi vào học chuyên ngành.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold shrink-0 mt-0.5">3</span><span><strong>Phương thức xét tuyển:</strong> Xét tuyển hoàn toàn theo hồ sơ văn bằng đã có, không phải thi tuyển đầu vào áp lực.</span></li>' .
				'</ul>',
				$clean_major
			),
		],
		[
			'question' => sprintf( 'Thời gian đào tạo Liên thông ngành %s mất bao lâu? Có được rút ngắn không?', $clean_major ),
			'answer'   => sprintf(
				'<p class="mb-2">Thời gian đào tạo ngành <strong>%s</strong> phụ thuộc vào văn bằng đầu vào và tiến độ tích lũy tín chỉ của bạn:</p>' .
				'<ul class="space-y-2 my-2.5 list-none pl-0">' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-800 text-[11px] font-bold shrink-0 mt-0.5">1</span><span><strong>Từ trình độ Cao đẳng đúng ngành:</strong> Thời gian học từ <strong>1.5 - 2 năm</strong> (tương đương 3 - 4 học kỳ chính).</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-800 text-[11px] font-bold shrink-0 mt-0.5">2</span><span><strong>Từ trình độ Cao đẳng trái ngành:</strong> Thời gian học từ <strong>2 - 2.5 năm</strong> (do cần thêm 1 học kỳ bổ sung kiến thức chuyển đổi).</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-800 text-[11px] font-bold shrink-0 mt-0.5">3</span><span><strong>Cơ chế rút ngắn thời gian:</strong> Với hình thức đào tạo theo hệ thống tín chỉ, sinh viên có thể đăng ký học vượt môn trong các kỳ học hè để tốt nghiệp sớm trước hạn.</span></li>' .
				'</ul>',
				$clean_major
			),
		],
		[
			'question' => sprintf( 'Học trực tuyến (Từ xa) ngành %s thì học tập, thực hành và thi cử như thế nào?', $clean_major ),
			'answer'   => sprintf(
				'<p class="mb-2">Chương trình đào tạo từ xa ngành <strong>%s</strong> được thiết kế tối ưu cho người đi làm với quy trình học hiện đại:</p>' .
				'<ul class="space-y-2 my-2.5 list-none pl-0">' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold shrink-0 mt-0.5">1</span><span><strong>Học lý thuyết trực tuyến 100%%:</strong> Xem video bài giảng, tài liệu điện tử và làm bài tập trắc nghiệm trên cổng E-learning mọi lúc mọi nơi.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold shrink-0 mt-0.5">2</span><span><strong>Thực hành & Làm bài tập lớn:</strong> Làm bài tập tình huống, đồ án môn học hoặc thực hành qua phần mềm chuyên ngành có giảng viên hướng dẫn trực tiếp qua lớp học trực tuyến (Google Meet / Zoom).</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold shrink-0 mt-0.5">3</span><span><strong>Thi kết thúc học phần:</strong> Thi tập trung theo lịch thông báo trước tại trụ sở chính của trường hoặc các trạm thi vệ tinh liên kết gần nơi bạn sinh sống.</span></li>' .
				'</ul>',
				$clean_major
			),
		],
		[
			'question' => sprintf( 'Nên chọn trường đại học nào để học Liên thông ngành %s phù hợp nhất?', $clean_major ),
			'answer'   => sprintf(
				'<p class="mb-2">Mỗi trường đại học có thế mạnh đào tạo riêng về ngành <strong>%s</strong>. Bạn nên cân nhắc dựa trên 3 tiêu chí cốt lõi:</p>' .
				'<ul class="space-y-2 my-2.5 list-none pl-0">' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">1</span><span><strong>Ưu tiên thương hiệu & chuyên môn sâu:</strong> Chọn các trường đại học chuyên ngành đầu ngành để có nền tảng kiến thức vững chắc và giá trị thương hiệu bằng cấp cao.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">2</span><span><strong>Ưu tiên học phí & lộ trình linh hoạt:</strong> Chọn các trường có mức học phí phù hợp với ngân sách cá nhân và chính sách hỗ trợ đóng chia kỳ linh hoạt.</span></li>' .
				'<li class="flex items-start gap-2.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-[#00308b] text-[11px] font-bold shrink-0 mt-0.5">3</span><span><strong>Ưu tiên trạm thi gần nơi ở:</strong> Chọn trường có mạng lưới trạm thi vệ tinh tại khu vực bạn sinh sống để thuận tiện di chuyển trong các kỳ thi tập trung.</span></li>' .
				'</ul>' .
				'<p class="mt-2 text-xs text-slate-500 italic">Quý học viên có thể tham khảo bảng so sánh các trường đang tuyển sinh ngành này ở mục phía trên hoặc để lại thông tin để được cán bộ tư vấn phân tích trường phù hợp nhất.</p>',
				$clean_major
			),
		],
	];
}

/**
 * Prevent wpautop from injecting <br> or unwanted empty <p> inside links/buttons and custom callouts in post content.
 */
add_filter( 'the_content', function( $content ) {
	if ( empty( $content ) || ! is_string( $content ) ) {
		return $content;
	}

	// Remove <br /> directly inside <a> tags
	$content = preg_replace_callback( '/<a\b[^>]*>(.*?)<\/a>/is', function( $matches ) {
		$inner = preg_replace( '/<br\s*\/?>/i', ' ', $matches[1] );
		return str_replace( $matches[1], $inner, $matches[0] );
	}, $content );

	// Remove <br /> between sibling <a> tags (e.g. inside flex button groups)
	$content = preg_replace( '/(<\/a>)\s*<br\s*\/?>\s*(<a\b)/i', '$1 $2', $content );

	// Unwrap inline-flex buttons from accidental <p> tags created by wpautop
	$content = preg_replace( '/<p>\s*(<a\b[^>]*class="[^"]*inline-flex[^"]*"[^>]*>.*?<\/a>)\s*<\/p>/is', '$1', $content );

	// Clean up empty <p></p> or <p><br /></p>
	$content = preg_replace( '/<p>\s*(?:<br\s*\/?>)?\s*<\/p>/i', '', $content );

	return $content;
}, 99 );

/**
 * Retrieve FAQ items for a Training Type taxonomy term.
 * Checks for custom FAQs via ACF or term meta, then falls back to expertly authored default FAQs.
 *
 * @param WP_Term|int|string|null $term
 * @return array
 */
function ltdh_get_training_type_faqs( $term = null ): array {
	if ( empty( $term ) ) {
		return [];
	}

	if ( is_numeric( $term ) ) {
		$term = get_term( (int) $term, 'training_type' );
	} elseif ( is_string( $term ) ) {
		$term = get_term_by( 'slug', $term, 'training_type' );
	}

	if ( ! ( $term instanceof WP_Term ) ) {
		return [];
	}

	// 1. Check custom ACF or term meta
	$custom_faqs = function_exists( 'get_field' ) ? get_field( 'faqs', $term ) : [];
	if ( empty( $custom_faqs ) ) {
		$custom_faqs = get_term_meta( $term->term_id, 'faqs', true );
	}
	if ( ! empty( $custom_faqs ) && is_array( $custom_faqs ) ) {
		$valid = [];
		foreach ( $custom_faqs as $item ) {
			if ( ! empty( $item['question'] ) && ! empty( $item['answer'] ) ) {
				$valid[] = [
					'question' => trim( (string) $item['question'] ),
					'answer'   => trim( (string) $item['answer'] ),
				];
			}
		}
		if ( ! empty( $valid ) ) {
			return $valid;
		}
	}

	// 2. Default expert FAQs based on slug
	$slug = $term->slug;
	if ( 'tu-xa' === $slug || 'dao-tao-tu-xa' === $slug ) {
		return [
			[
				'question' => 'Bằng tốt nghiệp Đại học từ xa có ghi chữ "Từ xa" trên văn bằng không?',
				'answer'   => '<p><strong>Không.</strong> Theo <strong>Thông tư 27/2019/TT-BGDĐT</strong> của Bộ Giáo dục và Đào tạo có hiệu lực từ ngày 01/03/2020, mẫu bằng tốt nghiệp đại học <strong>không còn ghi hình thức đào tạo</strong> (chính quy, vừa làm vừa học hay từ xa). Bằng chỉ ghi danh hiệu Cử nhân/Kỹ sư, ngành học, xếp loại tốt nghiệp và tên cơ sở giáo dục đại học cấp bằng.</p>',
			],
			[
				'question' => 'Học đại học từ xa có được thi tuyển công chức, viên chức và học lên Thạc sĩ không?',
				'answer'   => '<p><strong>Hoàn toàn được.</strong> Luật Giáo dục Đại học sửa đổi quy định văn bằng tốt nghiệp các hình thức đào tạo có giá trị pháp lý tương đương nhau. Học viên tốt nghiệp đại học từ xa hoàn toàn đủ điều kiện dự thi công chức, xét nâng ngạch, chuyển bậc lương và nộp hồ sơ xét tuyển Thạc sĩ, Tiến sĩ tại các trường đại học trong và ngoài nước.</p>',
			],
			[
				'question' => 'Thời gian đào tạo liên thông từ xa mất bao lâu?',
				'answer'   => '<p>Thời gian học liên thông thường kéo dài từ <strong>1.5 đến 2 năm</strong> tùy theo số lượng tín chỉ bạn được xét công nhận và miễn trừ từ bằng Trung cấp hoặc Cao đẳng trước đó. Học viên học vượt hoặc tích lũy tín chỉ nhanh có thể hoàn thành chương trình và tốt nghiệp sớm.</p>',
			],
			[
				'question' => 'Học trực tuyến 100% thì thi kết thúc học phần ở đâu?',
				'answer'   => '<p>Quá trình nghe giảng, làm bài tập và trao đổi với giảng viên diễn ra 100% online trên hệ thống LMS. Đối với các kỳ thi kết thúc học phần, các trường đại học tổ chức thi tập trung vào dịp Thứ 7, Chủ nhật tại các trạm đào tạo ở Hà Nội, Đà Nẵng, TP.HCM... hoặc hỗ trợ thi online có camera giám sát theo quy chế đào tạo của từng trường.</p>',
			],
			[
				'question' => 'Đăng ký xét tuyển đại học từ xa cần những hồ sơ gì?',
				'answer'   => '<p>Hình thức tuyển sinh là <strong>xét tuyển hồ sơ</strong> (không cần thi tuyển). Hồ sơ gồm: Bản sao công chứng bằng và bảng điểm tốt nghiệp Trung cấp/Cao đẳng, bản sao CCCD, giấy khai sinh và phiếu đăng ký tuyển sinh theo mẫu. Ban tư vấn của chúng tôi sẽ hướng dẫn bạn chuẩn bị hồ sơ nhanh gọn và hoàn toàn miễn phí.</p>',
			],
		];
	}

	if ( 'vua-hoc-vua-lam' === $slug ) {
		return [
			[
				'question' => 'Lịch học hệ Vừa học vừa làm (Tại chức) được sắp xếp vào thời gian nào?',
				'answer'   => '<p>Lịch học được thiết kế linh hoạt ngoài giờ hành chính: học vào các <strong>buổi tối trong tuần</strong> hoặc <strong>trọn vẹn Thứ 7 và Chủ nhật</strong>. Khung giờ này giúp người đi làm hoàn toàn yên tâm duy trì công việc cơ quan mà vẫn nâng cao được trình độ chuyên môn.</p>',
			],
			[
				'question' => 'Bằng đại học Vừa học vừa làm có giá trị như thế nào so với chính quy?',
				'answer'   => '<p>Theo quy định hiện hành của Bộ GD&ĐT (Thông tư 27/2019/TT-BGDĐT), văn bằng đại học không còn ghi hình thức đào tạo. Bằng hệ Vừa học vừa làm có <strong>giá trị pháp lý bình đẳng</strong> với bằng chính quy, được công nhận để thi công chức, xét nâng lương và học tiếp lên bậc Thạc sĩ, Tiến sĩ.</p>',
			],
			[
				'question' => 'Thời gian đào tạo liên thông hệ Vừa học vừa làm là bao lâu?',
				'answer'   => '<p>Thời gian đào tạo liên thông từ Cao đẳng lên Đại học thường từ <strong>1.5 đến 2 năm</strong> (từ Trung cấp lên Đại học từ 2.5 đến 3 năm). Thời gian cụ thể phụ thuộc vào số tín chỉ đại cương và cơ sở ngành được đối chiếu miễn giảm theo bảng điểm của học viên.</p>',
			],
			[
				'question' => 'Điều kiện đăng ký xét tuyển liên thông vừa học vừa làm?',
				'answer'   => '<p>Học viên chỉ cần có bằng tốt nghiệp Trung cấp hoặc Cao đẳng đúng ngành hoặc ngành gần. Trường hợp tốt nghiệp trái ngành, học viên chỉ cần học bổ sung một số học phần chuyển đổi theo quy định của trường trước khi vào học chuyên ngành.</p>',
			],
		];
	}

	if ( 'chinh-quy' === $slug ) {
		return [
			[
				'question' => 'Thời gian học liên thông chính quy diễn ra vào khung giờ nào?',
				'answer'   => '<p>Hệ liên thông chính quy học <strong>tập trung ban ngày</strong> theo thời khóa biểu tại khuôn viên chính của trường đại học. Hình thức này rất phù hợp với các bạn trẻ vừa tốt nghiệp CĐ/TC có thể dành toàn thời gian học tập và nghiên cứu tại trường.</p>',
			],
			[
				'question' => 'Bằng tốt nghiệp liên thông chính quy có khác bằng chính quy 4 năm không?',
				'answer'   => '<p><strong>Không khác nhau.</strong> Sau khi hoàn thành đủ số tín chỉ theo khung chương trình đào tạo, sinh viên được cấp bằng Cử nhân hoặc Kỹ sư đại học <strong>Chính quy</strong> chuẩn quốc gia do Hiệu trưởng nhà trường ký duyệt.</p>',
			],
			[
				'question' => 'Sinh viên liên thông chính quy có được hưởng học bổng và vay vốn chính sách không?',
				'answer'   => '<p><strong>Có.</strong> Sinh viên liên thông chính quy được hưởng đầy đủ các chính sách của Nhà nước và nhà trường: xét học bổng khuyến khích học tập theo kỳ, hỗ trợ vay vốn sinh viên tại Ngân hàng Chính sách xã hội, đăng ký ký túc xá và sinh hoạt Đoàn - Hội.</p>',
			],
			[
				'question' => 'Phương thức xét tuyển liên thông chính quy gồm những gì?',
				'answer'   => '<p>Hầu hết các trường đại học hiện nay áp dụng phương thức xét tuyển học bạ hoặc điểm tốt nghiệp Cao đẳng/Trung cấp; một số trường kết hợp điểm thi đánh giá năng lực hoặc điểm thi tốt nghiệp THPT theo đề án tuyển sinh cụ thể hàng năm.</p>',
			],
		];
	}

	// Generic fallback
	return [
		[
			'question' => 'Giá trị pháp lý của bằng Đại học liên thông?',
			'answer'   => '<p>Bằng Đại học liên thông do các trường Đại học công lập và ngoài công lập cấp đều nằm trong hệ thống văn bằng quốc gia, được Bộ Giáo dục và Đào tạo công nhận có giá trị pháp lý để thi tuyển công chức, xét nâng lương và học tiếp lên Thạc sĩ, Tiến sĩ.</p>',
		],
		[
			'question' => 'Làm sao để biết tôi được miễn giảm bao nhiêu tín chỉ?',
			'answer'   => '<p>Hội đồng tuyển sinh nhà trường sẽ căn cứ vào bảng điểm tốt nghiệp Cao đẳng hoặc Trung cấp của bạn để đối chiếu với khung chương trình đào tạo đại học, từ đó ra quyết định miễn giảm các học phần tương đương nhằm rút ngắn thời gian học tối đa.</p>',
		],
	];
}
