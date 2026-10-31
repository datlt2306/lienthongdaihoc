<?php
/**
 * Program & Academic Major Domain Helpers.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTDH_Program_Helpers {

	/**
	 * Get a program's effective hotline: program override → school → global.
	 *
	 * @param int $program_id
	 * @return string
	 */
	public static function get_hotline( int $program_id ): string {
		$override = '';
		if ( function_exists( 'get_field' ) ) {
			$override = get_field( 'hotline_override', $program_id );
		}
		if ( ! empty( $override ) ) {
			return $override;
		}

		$school_id = 0;
		if ( function_exists( 'get_field' ) ) {
			$rel_key   = defined( 'LTDH_META_SCHOOL_REL' ) ? LTDH_META_SCHOOL_REL : 'school_relationship';
			$school_id = intval( get_field( $rel_key, $program_id ) ?: 0 );
		}
		if ( ! $school_id ) {
			$school_id = self::get_school_id( $program_id );
		}

		if ( $school_id ) {
			$school_hotline = '';
			if ( function_exists( 'get_field' ) ) {
				$school_hotline = get_field( 'hotline', $school_id );
			}
			if ( ! empty( $school_hotline ) ) {
				return $school_hotline;
			}
			if ( class_exists( 'LTDH_School_Helpers' ) ) {
				$sch_h = LTDH_School_Helpers::get_hotline( $school_id );
				if ( ! empty( $sch_h ) ) {
					return $sch_h;
				}
			}
		}

		if ( class_exists( 'LTDH_UI_Helpers' ) ) {
			return LTDH_UI_Helpers::get_hotline();
		}
		return function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '';
	}

	public static function get_program_hotline( int $program_id ): string {
		return self::get_hotline( $program_id );
	}

	/**
	 * Resolves the valid, published school ID for a given program.
	 * Falls back to matching slug/code if relation was broken or orphaned.
	 *
	 * @param int $program_id
	 * @return int
	 */
	public static function get_school_id( int $program_id ): int {
		if ( ! $program_id ) {
			return 0;
		}

		$rel_key   = defined( 'LTDH_META_SCHOOL_REL' ) ? LTDH_META_SCHOOL_REL : 'school_relationship';
		$school_id = function_exists( 'get_field' ) ? get_field( $rel_key, $program_id ) : 0;
		if ( empty( $school_id ) ) {
			$school_id = get_post_meta( $program_id, $rel_key, true );
		}
		if ( is_array( $school_id ) ) {
			$first     = reset( $school_id );
			$school_id = is_object( $first ) ? $first->ID : intval( $first );
		} elseif ( is_object( $school_id ) ) {
			$school_id = $school_id->ID;
		}
		$school_id = intval( $school_id );

		if ( $school_id > 0 && ( ! function_exists( 'get_post_status' ) || 'publish' === get_post_status( $school_id ) ) ) {
			return $school_id;
		}

		// Healing fallback: Attempt to match existing published schools by slug/code
		$slug = get_post_field( 'post_name', $program_id );
		if ( empty( $slug ) ) {
			return 0;
		}

		$school_cpt  = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
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

	public static function get_program_school_id( int $program_id ): int {
		return self::get_school_id( $program_id );
	}

	/**
	 * Chuẩn hóa tiêu đề thẻ card chương trình đào tạo theo quy tắc:
	 * "Liên thông ngành [Tiêu đề trong quản trị / Tên ngành] - [Mã trường]"
	 *
	 * @param string|null $raw_prog_title Tiêu đề bài viết chương trình trong quản trị.
	 * @param string|null $school_code Mã trường (ví dụ: TMU, UTC, PTIT).
	 * @param string|null $school_name Tên trường đào tạo (dùng khi không có mã trường).
	 * @param string|null $major_title Tên ngành liên kết (fallback nếu tiêu đề quản trị rỗng).
	 * @return string Tiêu đề thẻ card đã được định dạng chuẩn.
	 */
	public static function format_card_headline( ?string $raw_prog_title = '', ?string $school_code = '', ?string $school_name = '', ?string $major_title = '' ): string {
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
			$clean_prog = trim( (string) $raw_prog_title );
		}

		return 'Liên thông ngành ' . $clean_prog . $school_suffix;
	}

	public static function format_program_card_headline( ?string $raw_prog_title = '', ?string $school_code = '', ?string $school_name = '', ?string $major_title = '' ): string {
		return self::format_card_headline( $raw_prog_title, $school_code, $school_name, $major_title );
	}

	/**
	 * Lấy giá trị custom field (ưu tiên get_field của ACF, nếu rỗng thì fallback sang get_post_meta)
	 *
	 * @param string $key
	 * @param int $post_id
	 * @return mixed
	 */
	public static function get_field_or_meta( string $key, int $post_id ) {
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
	public static function is_google_drive_url( string $url ): bool {
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
	public static function normalize_admission_file_info( $raw ): array {
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

		$is_drive = self::is_google_drive_url( $url );
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
	public static function get_or_create_admission_zip( array $documents, string $slug_prefix = 'ho-so-tuyen-sinh' ): string {
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
	 * Lấy toàn bộ gói hồ sơ & bộ biểu mẫu tuyển sinh của chương trình theo cơ chế kế thừa phân tầng
	 *
	 * @param int $program_id
	 * @return array
	 */
	public static function get_admission_package( int $program_id ): array {
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

		$school_id = self::get_school_id( $program_id );
		$drive_url = '';
		$source    = '';
		$source_label = '';

		// CẤP 1: KIỂM TRA LINK DRIVE RIÊNG CỦA CHƯƠNG TRÌNH
		$prog_drive = self::get_field_or_meta( 'program_admission_drive_url', $program_id );
		if ( empty( $prog_drive ) ) {
			$prog_drive = self::get_field_or_meta( 'admission_drive_folder_url', $program_id );
		}
		if ( ! empty( $prog_drive ) ) {
			$drive_url     = trim( (string) $prog_drive );
			$source        = 'program';
			$source_label  = 'Mẫu riêng theo ngành';
		}

		// CẤP 2: KIỂM TRA LINK DRIVE THEO HỆ ĐÀO TẠO CỦA TRƯỜNG
		if ( empty( $drive_url ) && $school_id > 0 ) {
			$training_terms = wp_get_post_terms( $program_id, defined( 'LTDH_TAX_TRAINING_TYPE' ) ? LTDH_TAX_TRAINING_TYPE : 'training_type' );
			if ( ! is_wp_error( $training_terms ) && ! empty( $training_terms ) ) {
				$by_type_rows = self::get_field_or_meta( 'admission_forms_by_type', $school_id );
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

		// CẤP 3: KIỂM TRA LINK DRIVE CHUNG CỦA TRƯỜNG
		$school_drive = '';
		if ( $school_id > 0 ) {
			$school_drive_raw = self::get_field_or_meta( 'admission_drive_folder_url', $school_id );
			if ( ! empty( $school_drive_raw ) ) {
				$school_drive = trim( (string) $school_drive_raw );
			}
		}

		if ( empty( $drive_url ) && ! empty( $school_drive ) ) {
			$drive_url     = $school_drive;
			$source        = 'school_default';
			$source_label  = 'Mẫu chuẩn của trường';
		}

		// CẤP 4: FALLBACK CHO DỮ LIỆU FILE CŨ
		$fallback_url = '';
		if ( empty( $drive_url ) ) {
			$prog_single = self::get_field_or_meta( 'admission_form_file', $program_id );
			$s_info      = self::normalize_admission_file_info( $prog_single );
			if ( ! empty( $s_info['url'] ) ) {
				$fallback_url = $s_info['url'];
				$source       = 'program';
				$source_label = 'Mẫu riêng theo ngành';
			} elseif ( $school_id > 0 ) {
				$def_bundle = self::get_field_or_meta( 'default_bundle_file', $school_id );
				$b_info     = self::normalize_admission_file_info( $def_bundle );
				if ( ! empty( $b_info['url'] ) ) {
					$fallback_url = $b_info['url'];
					$source       = 'school_default';
					$source_label = 'Mẫu chuẩn của trường';
				} else {
					$def_single = self::get_field_or_meta( 'default_admission_form', $school_id );
					$df_info    = self::normalize_admission_file_info( $def_single );
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

		$is_drive = ! empty( $drive_url ) || self::is_google_drive_url( $target_url );
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

	public static function get_program_admission_package( int $program_id ): array {
		return self::get_admission_package( $program_id );
	}

	/**
	 * Lấy dữ liệu phiếu tuyển sinh chính (Backward compatibility wrapper)
	 *
	 * @param int $program_id
	 * @return array|null
	 */
	public static function get_admission_form_data( int $program_id ): ?array {
		$pkg = self::get_admission_package( $program_id );
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

	public static function get_program_admission_form_data( int $program_id ): ?array {
		return self::get_admission_form_data( $program_id );
	}

	/**
	 * Lấy nhanh URL phiếu tuyển sinh của chương trình
	 *
	 * @param int $program_id
	 * @return string
	 */
	public static function get_admission_form_url( int $program_id ): string {
		$data = self::get_admission_form_data( $program_id );
		return $data['url'] ?? '';
	}

	public static function get_program_admission_form_url( int $program_id ): string {
		return self::get_admission_form_url( $program_id );
	}

	/**
	 * Tìm kiếm biểu mẫu miễn giảm môn học phù hợp với tên môn học
	 *
	 * @param string $subject
	 * @param array $documents
	 * @return array|null
	 */
	public static function find_matching_exemption_doc( string $subject, array $documents ): ?array {
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

		$words      = preg_split( '/[\s,\.-]+/', $sub_clean );
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
	 *
	 * @param int   $major_id
	 * @param mixed $school_param ID, slug, school_code hoặc post object của trường
	 * @return array|null
	 */
	public static function get_major_curriculum_info( int $major_id, $school_param = null ): ?array {
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
				$cpt_school   = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
				$found_school = get_page_by_path( sanitize_title( $school_param ), OBJECT, $cpt_school );
				if ( $found_school ) {
					$target_school_id = $found_school->ID;
				} else {
					$by_code = get_posts( [
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
					if ( ! empty( $by_code ) ) {
						$target_school_id = $by_code[0]->ID;
					}
				}
			}
		}

		$raw_file = null;
		$title    = '';
		$source   = '';

		if ( $target_school_id > 0 ) {
			$school_rows = self::get_field_or_meta( 'major_school_curriculums', $major_id );
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

		if ( empty( $raw_file ) ) {
			$def_file = self::get_field_or_meta( 'major_default_curriculum', $major_id );
			if ( ! empty( $def_file ) ) {
				$raw_file = $def_file;
				$title    = 'Khung chương trình chuẩn: ' . get_the_title( $major_id );
				$source   = 'major_default';
			}
		}

		if ( empty( $raw_file ) ) {
			return null;
		}

		$f_info = self::normalize_admission_file_info( $raw_file );
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
	 *
	 * @param int $program_id
	 * @return array|null
	 */
	public static function get_curriculum_info( int $program_id ): ?array {
		if ( ! $program_id ) {
			return null;
		}

		$school_id     = self::get_school_id( $program_id );
		$major_rel_key = defined( 'LTDH_META_MAJOR_REL' ) ? LTDH_META_MAJOR_REL : 'major_relationship';
		$major_raw     = get_post_meta( $program_id, $major_rel_key, true );
		if ( empty( $major_raw ) && function_exists( 'get_field' ) ) {
			$major_raw = get_field( $major_rel_key, $program_id );
		}
		$major_id = is_numeric( $major_raw ) ? (int) $major_raw : ( is_array( $major_raw ) && ! empty( $major_raw['ID'] ) ? (int) $major_raw['ID'] : ( is_object( $major_raw ) && ! empty( $major_raw->ID ) ? (int) $major_raw->ID : 0 ) );

		$raw_file = null;
		$title    = '';
		$source   = '';

		if ( $major_id > 0 ) {
			$school_curriculums = self::get_field_or_meta( 'major_school_curriculums', $major_id );
			if ( ! empty( $school_curriculums ) && is_array( $school_curriculums ) && $school_id > 0 ) {
				$training_terms = wp_get_post_terms( $program_id, defined( 'LTDH_TAX_TRAINING_TYPE' ) ? LTDH_TAX_TRAINING_TYPE : 'training_type' );
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

						if ( $row_type_id > 0 && in_array( $row_type_id, $prog_term_ids, true ) ) {
							$raw_file = $row['curriculum_file'];
							$title    = ! empty( $row['notes'] ) ? (string) $row['notes'] : '';
							$source   = 'major_school_type';
							break;
						}

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

			if ( empty( $raw_file ) ) {
				$def_file = self::get_field_or_meta( 'major_default_curriculum', $major_id );
				if ( ! empty( $def_file ) ) {
					$raw_file = $def_file;
					$source   = 'major_default';
				}
			}
		}

		if ( empty( $raw_file ) ) {
			return null;
		}

		$f_info = self::normalize_admission_file_info( $raw_file );
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

	public static function get_program_curriculum_info( int $program_id ): ?array {
		return self::get_curriculum_info( $program_id );
	}

	/**
	 * Program Learning Details (Campus & Learning Mode).
	 *
	 * @param int $program_id
	 * @return array ['campus' => string, 'mode' => string]
	 */
	public static function get_learning_details( int $program_id ): array {
		$tax_campus        = defined( 'LTDH_TAX_CAMPUS' ) ? LTDH_TAX_CAMPUS : 'campus';
		$tax_training_type = defined( 'LTDH_TAX_TRAINING_TYPE' ) ? LTDH_TAX_TRAINING_TYPE : 'training_type';
		$tax_region        = defined( 'LTDH_TAX_REGION' ) ? LTDH_TAX_REGION : 'region';

		$campuses          = wp_get_post_terms( $program_id, $tax_campus );
		$physical_campuses = [];

		if ( ! empty( $campuses ) && ! is_wp_error( $campuses ) ) {
			foreach ( $campuses as $campus ) {
				$slug = is_object( $campus ) ? $campus->slug : '';
				$name = is_object( $campus ) ? trim( $campus->name ) : '';
				if ( 'online' === strtolower( $slug ) || 'online' === strtolower( $name ) ) {
					continue;
				}
				if ( $name && ! in_array( $name, $physical_campuses, true ) ) {
					$physical_campuses[] = $name;
				}
			}
		}

		$types     = wp_get_post_terms( $program_id, $tax_training_type );
		$type_slug = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->slug : '';

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
				$region_terms = wp_get_post_terms( (int) $school_id, $tax_region );
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

	public static function get_program_learning_details( int $program_id ): array {
		return self::get_learning_details( $program_id );
	}

	/**
	 * Lấy URL ảnh banner hoặc ảnh đại diện của chương trình đào tạo.
	 *
	 * @param int    $program_id
	 * @param string $size
	 * @return string
	 */
	public static function get_image_url( int $program_id, string $size = 'full' ): string {
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

		// 2. Fallback: Ảnh đại diện (Featured Image) của chương trình
		if ( empty( $prog_img ) && has_post_thumbnail( $program_id ) ) {
			$prog_img = get_the_post_thumbnail_url( $program_id, $size ) ?: '';
		}

		if ( ! empty( $prog_img ) ) {
			return function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $prog_img ) : ( class_exists( 'LTDH_UI_Helpers' ) ? LTDH_UI_Helpers::get_optimized_image_url( $prog_img ) : $prog_img );
		}

		// 3. Fallback: Kế thừa ảnh ngành của chương trình đấy
		$major_id = self::get_major_id( $program_id );
		if ( $major_id ) {
			$major_img = function_exists( 'ltdh_get_major_banner_url' ) ? ltdh_get_major_banner_url( $major_id, $size ) : ( class_exists( 'LTDH_UI_Helpers' ) ? LTDH_UI_Helpers::get_major_banner_url( $major_id, $size ) : '' );
			if ( ! empty( $major_img ) ) {
				return $major_img;
			}
		}

		// 4. Fallback: Banner của trường đối tác (nếu có)
		$school_id = self::get_school_id( $program_id );
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
				return function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $school_img ) : ( class_exists( 'LTDH_UI_Helpers' ) ? LTDH_UI_Helpers::get_optimized_image_url( $school_img ) : $school_img );
			}
		}

		return '';
	}

	public static function get_program_image_url( int $program_id, string $size = 'full' ): string {
		return self::get_image_url( $program_id, $size );
	}

	/**
	 * Get count of valid, actively offered programs linked to a major.
	 *
	 * @param int $major_id
	 * @return int
	 */
	public static function get_major_valid_programs_count( int $major_id ): int {
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
			$school_id = self::get_school_id( $p_id );
			if ( $school_id > 0 && 'publish' === get_post_status( $school_id ) ) {
				$count++;
			}
		}

		wp_cache_set( $cache_key, $count, 'ltdh', HOUR_IN_SECONDS );
		return $count;
	}

	/**
	 * Get count of distinct schools actively offering/admitting this major.
	 *
	 * @param int $major_id
	 * @return int
	 */
	public static function get_major_unique_schools_count( int $major_id ): int {
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
			$school_id = self::get_school_id( $p_id );
			if ( $school_id > 0 && 'publish' === get_post_status( $school_id ) && ! in_array( $school_id, $school_ids, true ) ) {
				$school_ids[] = $school_id;
			}
		}

		$count = count( $school_ids );
		wp_cache_set( $cache_key, $count, 'ltdh', HOUR_IN_SECONDS );
		return $count;
	}

	/**
	 * Lấy trạng thái tuyển sinh thực tế của chương trình đào tạo.
	 *
	 * @param int $program_id
	 * @return string 'tuyen-sinh' | 'tam-ngung' | 'sap-mo'
	 */
	public static function get_admission_status( int $program_id ): string {
		$batches = self::get_admission_batches( $program_id );

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

			if ( $all_closed ) {
				return 'tam-ngung';
			}

			if ( $has_open ) {
				return 'tuyen-sinh';
			}

			if ( $has_upcoming ) {
				return 'sap-mo';
			}
		}

		return get_post_meta( $program_id, 'admission_status', true ) ?: 'tuyen-sinh';
	}

	public static function get_program_admission_status( int $program_id ): string {
		return self::get_admission_status( $program_id );
	}

	/**
	 * Get dynamic admission deadline display based on batches or enrollment_period.
	 *
	 * @param int $program_id
	 * @return string
	 */
	public static function get_admission_deadline_display( int $program_id ): string {
		$legacy_period = trim( (string) ( ( function_exists( 'get_field' ) ? get_field( 'enrollment_period', $program_id ) : null ) ?: get_post_meta( $program_id, 'enrollment_period', true ) ) );

		$batches = self::get_admission_batches( $program_id );

		if ( is_array( $batches ) && ! empty( $batches ) ) {
			$sap_mo_batches = [];
			$all_closed     = true;

			foreach ( $batches as $b ) {
				$status             = $b['batch_status'] ?? '';
				$batch_name         = trim( (string) ( $b['batch_name'] ?? '' ) );
				$clean_name         = trim( preg_replace( '/^Tuyển sinh\s*/ui', '', $batch_name ) );
				$application_period = trim( (string) ( $b['application_period'] ?? '' ) );

				if ( 'dang-nhan' === $status ) {
					if ( ! empty( $application_period ) && '-' !== $application_period ) {
						return ! empty( $clean_name ) 
							? esc_html( $clean_name ) . ': ' . esc_html( $application_period )
							: esc_html( $application_period );
					}

					if ( ! empty( $legacy_period ) && ! in_array( $legacy_period, [ 'Đang nhận hồ sơ', 'Tuyển sinh theo đợt' ], true ) ) {
						return esc_html( $legacy_period );
					}

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

			if ( $all_closed ) {
				return 'Đã hết chỉ tiêu tuyển sinh';
			}
		}

		$status = self::get_admission_status( $program_id );
		if ( 'tam-ngung' === $status ) {
			return ( ! empty( $legacy_period ) && 'Tuyển sinh theo đợt' !== $legacy_period ) ? esc_html( $legacy_period ) : 'Đã hết chỉ tiêu tuyển sinh';
		}
		return ! empty( $legacy_period ) ? esc_html( $legacy_period ) : 'Đang nhận hồ sơ';
	}

	public static function get_program_admission_deadline_display( int $program_id ): string {
		return self::get_admission_deadline_display( $program_id );
	}

	/**
	 * Lấy danh sách quy định đối tượng tuyển sinh theo từng trường của ngành đào tạo
	 *
	 * @param int $major_id
	 * @return array
	 */
	public static function get_major_target_admissions( int $major_id ): array {
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

		$repeater = function_exists( 'get_field' ) ? get_field( 'major_school_admission_targets', $major_id ) : get_post_meta( $major_id, 'major_school_admission_targets', true );

		if ( ! empty( $repeater ) && is_array( $repeater ) ) {
			foreach ( $repeater as $row ) {
				$school_raw   = $row['school'] ?? ( $row['field_target_school'] ?? 0 );
				$school_id    = is_object( $school_raw ) ? $school_raw->ID : (int) $school_raw;
				$school_title = $school_id ? get_the_title( $school_id ) : '';
				$school_slug  = $school_id ? get_post_field( 'post_name', $school_id ) : '';
				$school_badge = $school_id && function_exists( 'ltdh_get_school_code' ) ? ltdh_get_school_code( $school_id ) : ( class_exists( 'LTDH_School_Helpers' ) ? LTDH_School_Helpers::get_code( $school_id ) : '' );

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
	 * Lấy quy định đối tượng tuyển sinh cho một chương trình đào tạo.
	 *
	 * @param int $program_id
	 * @return array|null
	 */
	public static function get_target_admission( int $program_id ): ?array {
		if ( ! $program_id ) {
			return null;
		}

		$school_rel_key = defined( 'LTDH_META_SCHOOL_REL' ) ? LTDH_META_SCHOOL_REL : 'school_relationship';
		$major_rel_key  = defined( 'LTDH_META_MAJOR_REL' ) ? LTDH_META_MAJOR_REL : 'major_relationship';

		$school_raw = function_exists( 'get_field' ) ? get_field( $school_rel_key, $program_id ) : get_post_meta( $program_id, $school_rel_key, true );
		$major_raw  = function_exists( 'get_field' ) ? get_field( $major_rel_key, $program_id ) : get_post_meta( $program_id, $major_rel_key, true );

		$school_id = is_object( $school_raw ) ? (int) $school_raw->ID : ( (int) $school_raw ?: (int) get_post_meta( $program_id, $school_rel_key, true ) );
		$major_id  = is_object( $major_raw ) ? (int) $major_raw->ID : ( (int) $major_raw ?: (int) get_post_meta( $program_id, $major_rel_key, true ) );

		if ( ! $major_id ) {
			return null;
		}

		$target_list = self::get_major_target_admissions( $major_id );
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

			if ( $target_sch_id > 0 && $row_school_id > 0 && $row_school_id === $target_sch_id ) {
				return true;
			}

			if ( $target_sch_id > 0 ) {
				$school_code = strtoupper( trim( (string) ( function_exists( 'ltdh_get_school_code' ) ? ltdh_get_school_code( $target_sch_id ) : get_post_meta( $target_sch_id, 'school_code', true ) ) ) );
				if ( ! empty( $school_code ) && ! empty( $row_school_badge ) && $school_code === $row_school_badge ) {
					return true;
				}

				$school_slug = sanitize_title( (string) get_post_field( 'post_name', $target_sch_id ) );
				if ( ! empty( $school_slug ) && ! empty( $row_school_slug ) && ( $school_slug === $row_school_slug || str_contains( $school_slug, $row_school_slug ) || str_contains( $row_school_slug, $school_slug ) ) ) {
					return true;
				}
			}

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

	public static function get_program_target_admission( int $program_id ): ?array {
		return self::get_target_admission( $program_id );
	}

	/**
	 * Lấy ID ngành học liên kết với chương trình.
	 *
	 * @param int $program_id
	 * @return int
	 */
	public static function get_major_id( int $program_id ): int {
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

	public static function get_program_major_id( int $program_id ): int {
		return self::get_major_id( $program_id );
	}

	/**
	 * Lấy điều kiện xét tuyển của chương trình với cơ chế DRY fallback về trường.
	 *
	 * @param int $program_id
	 * @return string
	 */
	public static function get_admission_requirements( int $program_id ): string {
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
		$school_id = self::get_school_id( $program_id );
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

	public static function get_program_admission_requirements( int $program_id ): string {
		return self::get_admission_requirements( $program_id );
	}

	/**
	 * Lấy danh mục hồ sơ cần nộp của chương trình với cơ chế DRY fallback về trường.
	 *
	 * @param int $program_id
	 * @return string
	 */
	public static function get_required_documents( int $program_id ): string {
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
		$school_id = self::get_school_id( $program_id );
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

	public static function get_program_required_documents( int $program_id ): string {
		return self::get_required_documents( $program_id );
	}

	/**
	 * Lấy điều kiện xét tuyển chung của ngành đào tạo (CPT major).
	 *
	 * @param int $major_id
	 * @param int|null $school_id
	 * @return string
	 */
	public static function get_major_admission_requirements( int $major_id, ?int $school_id = null ): string {
		if ( $school_id && $school_id > 0 ) {
			if ( class_exists( 'LTDH_School_Helpers' ) ) {
				return LTDH_School_Helpers::get_admission_requirements( $school_id );
			}
			return function_exists( 'ltdh_get_school_admission_requirements' ) ? ltdh_get_school_admission_requirements( $school_id ) : '';
		}

		$major_title = $major_id ? get_the_title( $major_id ) : 'ngành đào tạo';

		return '<p>Thí sinh đã tốt nghiệp <strong>Trung cấp, Cao đẳng</strong> khối ngành đúng hoặc ngành gần với ngành <strong>' . esc_html( $major_title ) . '</strong> theo quy định tuyển sinh liên thông của Bộ Giáo dục & Đào tạo.</p><p>Thí sinh đã có bằng <strong>Đại học</strong> các chuyên ngành khác có thể đăng ký học theo diện <strong>Văn bằng 2</strong> để được xét miễn giảm các học phần đại cương tương đương.</p><p><strong>Phương thức tuyển sinh:</strong> Xét tuyển thẳng dựa trên văn bằng và kết quả học tập (bảng điểm toàn khóa).</p>';
	}

	/**
	 * Lấy danh mục hồ sơ cần chuẩn bị của ngành đào tạo (CPT major).
	 *
	 * @param int $major_id
	 * @param int|null $school_id
	 * @return string
	 */
	public static function get_major_required_documents( int $major_id, ?int $school_id = null ): string {
		if ( $school_id && $school_id > 0 ) {
			if ( class_exists( 'LTDH_School_Helpers' ) ) {
				return LTDH_School_Helpers::get_required_documents( $school_id );
			}
			return function_exists( 'ltdh_get_school_required_documents' ) ? ltdh_get_school_required_documents( $school_id ) : '';
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
	 * @param int $program_id
	 * @return string
	 */
	public static function get_duration( int $program_id ): string {
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
		$school_id = self::get_school_id( $program_id );
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

	public static function get_program_duration( int $program_id ): string {
		return self::get_duration( $program_id );
	}

	/**
	 * Lấy danh hiệu văn bằng tốt nghiệp (Degree Type) của chương trình.
	 *
	 * @param int $program_id
	 * @return string
	 */
	public static function get_degree_type( int $program_id ): string {
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
		$major_id = self::get_major_id( $program_id );
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

	public static function get_program_degree_type( int $program_id ): string {
		return self::get_degree_type( $program_id );
	}

	/**
	 * Lấy quy định và giá trị pháp lý văn bằng của chương trình với cơ chế DRY kế thừa từ Trường đào tạo.
	 *
	 * @param int $program_id
	 * @return string
	 */
	public static function get_diploma_value( int $program_id ): string {
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
		$school_id = self::get_school_id( $program_id );
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

	public static function get_program_diploma_value( int $program_id ): string {
		return self::get_diploma_value( $program_id );
	}

	/**
	 * Lấy tên chuyên ngành hoặc ngành đào tạo chuẩn của chương trình (đã làm sạch tiền tố).
	 *
	 * @param int $program_id
	 * @return string
	 */
	public static function get_discipline_name( int $program_id = 0 ): string {
		if ( ! $program_id ) {
			$program_id = (int) get_the_ID();
		}
		if ( ! $program_id ) {
			return '';
		}

		// 1. Chuyên ngành đào tạo hẹp nếu được khai báo riêng
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

			$school_id   = self::get_school_id( $program_id );
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

				// Bỏ hệ đào tạo trong ngoặc đơn ở cuối nếu có
				$clean_title = preg_replace( '/\s*\((?:hệ\s+)?(?:từ xa|đào tạo từ xa|vừa học vừa làm|vhvl|chính quy|online|tập trung|liên thông)[^)]*\)$/iu', '', $clean_title );
				$clean_title = trim( $clean_title, " -–—\t\n\r\0\x0B" );
			}

			$clean_title = preg_replace( '/^(?:tuyển sinh\s+)+/iu', '', $clean_title );
			$clean_title = preg_replace( '/^(?:liên thông\s+(?:đại học\s+)?(?:ngành\s+)?)/iu', '', $clean_title );
			$clean_title = preg_replace( '/^(?:cử nhân|kỹ sư|ngành)\s+/iu', '', $clean_title );
			$clean_title = trim( $clean_title, " -–—\t\n\r\0\x0B" );

			if ( ! empty( $clean_title ) ) {
				return $clean_title;
			}
		}

		// 3. Fallback: Ngành đào tạo liên kết nếu tiêu đề rỗng
		$major_id = self::get_major_id( $program_id );
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

	public static function get_program_discipline_name( int $program_id = 0 ): string {
		return self::get_discipline_name( $program_id );
	}

	/**
	 * Lấy tiêu đề chuẩn cho banner và breadcrumb của chương trình.
	 *
	 * @param int $program_id
	 * @return string
	 */
	public static function get_banner_title( int $program_id = 0 ): string {
		if ( ! $program_id ) {
			$program_id = (int) get_the_ID();
		}
		if ( ! $program_id ) {
			return '';
		}

		$discipline = self::get_discipline_name( $program_id );

		$training_types = get_the_terms( $program_id, defined( 'LTDH_TAX_TRAINING_TYPE' ) ? LTDH_TAX_TRAINING_TYPE : 'training_type' );
		$training_type  = ( ! empty( $training_types ) && ! is_wp_error( $training_types ) ) ? $training_types[0]->name : '';
		$clean_type     = preg_replace( '/^hệ\s+/iu', '', trim( (string) $training_type ) );
		$type_part      = ! empty( $clean_type ) ? ' (' . $clean_type . ')' : '';

		if ( ! empty( $discipline ) ) {
			return 'Liên thông ' . $discipline . $type_part;
		}

		$school_id   = self::get_school_id( $program_id );
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

	public static function get_program_banner_title( int $program_id = 0 ): string {
		return self::get_banner_title( $program_id );
	}

	/**
	 * Lấy danh sách các đợt tuyển sinh của chương trình với cơ chế DRY fallback về trường.
	 *
	 * @param int $program_id
	 * @return array
	 */
	public static function get_admission_batches( int $program_id ): array {
		if ( ! $program_id ) {
			return [];
		}

		$batches = function_exists( 'get_field' ) ? get_field( 'admission_batches', $program_id ) : [];
		if ( ! empty( $batches ) && is_array( $batches ) ) {
			return $batches;
		}

		// Fallback to school default admission batches
		$school_id = self::get_school_id( $program_id );
		if ( $school_id > 0 ) {
			$school_batches = function_exists( 'get_field' ) ? get_field( 'default_admission_batches', $school_id ) : [];
			if ( ! empty( $school_batches ) && is_array( $school_batches ) ) {
				return $school_batches;
			}
		}

		return [];
	}

	public static function get_program_admission_batches( int $program_id ): array {
		return self::get_admission_batches( $program_id );
	}

	/**
	 * Lấy thông tin quy định miễn môn & chuyển đổi tín chỉ với cơ chế DRY fallback về trường.
	 *
	 * @param int $program_id
	 * @return array
	 */
	public static function get_exemptions( int $program_id ): array {
		$result = [
			'title'     => '',
			'intro'     => '',
			'items'     => [],
			'inherited' => false,
		];

		if ( ! $program_id ) {
			return $result;
		}

		$items = self::get_field_or_meta( 'exemption_items', $program_id );

		if ( ! empty( $items ) && is_array( $items ) ) {
			$title = self::get_field_or_meta( 'exemption_title', $program_id );
			$intro = self::get_field_or_meta( 'exemption_intro', $program_id );

			$result['title']     = ! empty( $title ) ? (string) $title : 'Quy định miễn môn & Chuyển đổi tín chỉ';
			$result['intro']     = ! empty( $intro ) ? (string) $intro : '';
			$result['items']     = $items;
			$result['inherited'] = false;
			return $result;
		}

		// Fallback to school default exemptions
		$school_id = self::get_school_id( $program_id );
		if ( $school_id > 0 ) {
			$default_items = self::get_field_or_meta( 'exemption_items', $school_id ) ?: self::get_field_or_meta( 'default_exemption_items', $school_id );

			if ( ! empty( $default_items ) && is_array( $default_items ) ) {
				$def_title = self::get_field_or_meta( 'exemption_title', $school_id ) ?: self::get_field_or_meta( 'default_exemption_title', $school_id );
				$def_intro = self::get_field_or_meta( 'exemption_intro', $school_id ) ?: self::get_field_or_meta( 'default_exemption_intro', $school_id );

				$result['title']     = ! empty( $def_title ) ? (string) $def_title : 'Quy định miễn môn & Chuyển đổi tín chỉ';
				$result['intro']     = ! empty( $def_intro ) ? (string) $def_intro : '';
				$result['items']     = $default_items;
				$result['inherited'] = true;
				return $result;
			}
		}

		return $result;
	}

	public static function get_program_exemptions( int $program_id ): array {
		return self::get_exemptions( $program_id );
	}

	/**
	 * Lấy danh sách câu hỏi thường gặp (FAQ) của chương trình với cơ chế DRY thông minh
	 *
	 * @param int $program_id
	 * @return array
	 */
	public static function get_faqs( int $program_id ): array {
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

		$major_id  = self::get_major_id( $program_id );
		$school_id = self::get_school_id( $program_id );

		// 2.1 Lấy FAQ ngành
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

		// 2.2 Lấy FAQ trường
		if ( $school_id > 0 ) {
			$school_faqs = class_exists( 'LTDH_School_Helpers' ) ? LTDH_School_Helpers::get_faqs( $school_id ) : ( function_exists( 'ltdh_get_school_faqs' ) ? ltdh_get_school_faqs( $school_id ) : [] );
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

	public static function get_program_faqs( int $program_id ): array {
		return self::get_faqs( $program_id );
	}

	/**
	 * Lấy danh sách các hình thức đào tạo thực tế có chương trình đang tuyển sinh của một ngành.
	 *
	 * @param int $major_id
	 * @return array
	 */
	public static function get_major_training_types( int $major_id ): array {
		if ( ! $major_id ) {
			return [];
		}

		$cache_key = 'ltdh_major_training_types_' . $major_id;
		$cached    = wp_cache_get( $cache_key, 'ltdh' );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$meta_offered_programs = defined( 'LTDH_META_OFFERED_PROGRAMS' ) ? LTDH_META_OFFERED_PROGRAMS : '_offered_programs';
		$cpt_program           = defined( 'LTDH_CPT_PROGRAM' ) ? LTDH_CPT_PROGRAM : 'program';
		$meta_major_rel        = defined( 'LTDH_META_MAJOR_REL' ) ? LTDH_META_MAJOR_REL : 'major_relationship';
		$meta_admission_status = defined( 'LTDH_META_ADMISSION_STATUS' ) ? LTDH_META_ADMISSION_STATUS : 'admission_status';
		$meta_school_rel       = defined( 'LTDH_META_SCHOOL_REL' ) ? LTDH_META_SCHOOL_REL : 'school_relationship';
		$tax_training_type     = defined( 'LTDH_TAX_TRAINING_TYPE' ) ? LTDH_TAX_TRAINING_TYPE : 'training_type';

		$offered_program_ids = get_post_meta( $major_id, $meta_offered_programs, true );
		$direct_prog_ids     = get_posts( [
			'post_type'   => $cpt_program,
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_query'  => [
				'relation' => 'OR',
				[
					'key'     => $meta_major_rel,
					'value'   => $major_id,
					'compare' => '=',
				],
				[
					'key'     => $meta_major_rel,
					'value'   => '"' . $major_id . '"',
					'compare' => 'LIKE',
				],
				[
					'key'     => $meta_major_rel,
					'value'   => 's:' . strlen( (string) $major_id ) . ':"' . $major_id . '"',
					'compare' => 'LIKE',
				],
				[
					'key'     => $meta_major_rel,
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
					'key'     => $meta_admission_status,
					'value'   => 'tam-ngung',
					'compare' => '!=',
				],
				[
					'key'     => $meta_admission_status,
					'compare' => 'NOT EXISTS',
				],
			],
		];

		$args = [
			'post_type'      => $cpt_program,
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
					'key'     => $meta_major_rel,
					'value'   => $major_id,
					'compare' => '=',
				],
				[
					'key'     => $meta_major_rel,
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
			$school_rel_id = self::get_school_id( $p_id );
			if ( ! $school_rel_id || 'publish' !== get_post_status( $school_rel_id ) ) {
				continue;
			}

			$terms = wp_get_post_terms( $p_id, $tax_training_type );
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

	/**
	 * Retrieve verified status, date, source and internal notes for a program.
	 *
	 * @param int $program_id
	 * @return array
	 */
	public static function get_verification( int $program_id ): array {
		if ( class_exists( 'LTDH_UI_Helpers' ) ) {
			return LTDH_UI_Helpers::get_program_verification( $program_id );
		}
		if ( function_exists( 'ltdh_get_program_verification' ) ) {
			return ltdh_get_program_verification( $program_id );
		}

		$status_verified   = defined( 'LTDH_VERIF_STATUS_VERIFIED' ) ? LTDH_VERIF_STATUS_VERIFIED : 'verified';
		$status_partial    = defined( 'LTDH_VERIF_STATUS_PARTIAL' ) ? LTDH_VERIF_STATUS_PARTIAL : 'partial';
		$status_unverified = defined( 'LTDH_VERIF_STATUS_UNVERIFIED' ) ? LTDH_VERIF_STATUS_UNVERIFIED : 'unverified';

		if ( ! $program_id || 'program' !== get_post_type( $program_id ) ) {
			return [
				'status'            => $status_unverified,
				'status_label'      => 'Chưa xác minh',
				'is_verified'       => false,
				'is_partial'        => false,
				'is_unverified'     => true,
				'last_verified_at'  => '',
				'last_verified_raw' => '',
				'source_url'        => '',
				'source_title'      => '',
				'notes'             => '',
			];
		}

		$meta_verif_status = defined( 'LTDH_META_VERIFICATION_STATUS' ) ? LTDH_META_VERIFICATION_STATUS : '_ltdh_verification_status';
		$status = get_post_meta( $program_id, $meta_verif_status, true );
		if ( empty( $status ) && function_exists( 'get_field' ) ) {
			$status = get_field( $meta_verif_status, $program_id );
		}
		$allowed_statuses = [ $status_verified, $status_partial, $status_unverified ];
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			$status = $status_unverified;
		}

		$status_labels = [
			$status_verified   => 'Đã xác minh',
			$status_partial    => 'Đã xác minh một phần',
			$status_unverified => 'Chưa xác minh',
		];

		$meta_last_verif = defined( 'LTDH_META_LAST_VERIFIED_AT' ) ? LTDH_META_LAST_VERIFIED_AT : '_ltdh_last_verified_at';
		$raw_date = get_post_meta( $program_id, $meta_last_verif, true );
		if ( empty( $raw_date ) && function_exists( 'get_field' ) ) {
			$raw_date = get_field( $meta_last_verif, $program_id, false );
		}
		$formatted_date = class_exists( 'LTDH_UI_Helpers' ) ? LTDH_UI_Helpers::format_verification_date( $raw_date ) : ( function_exists( 'ltdh_format_verification_date' ) ? ltdh_format_verification_date( $raw_date ) : '' );

		$meta_source = defined( 'LTDH_META_VERIFICATION_SOURCE' ) ? LTDH_META_VERIFICATION_SOURCE : '_ltdh_verification_source';
		$raw_url = get_post_meta( $program_id, $meta_source, true );
		if ( empty( $raw_url ) && function_exists( 'get_field' ) ) {
			$raw_url = get_field( $meta_source, $program_id );
		}
		$clean_url = '';
		if ( ! empty( $raw_url ) && is_string( $raw_url ) ) {
			$candidate_url = esc_url_raw( trim( $raw_url ) );
			if ( ! empty( $candidate_url ) && filter_var( $candidate_url, FILTER_VALIDATE_URL ) && preg_match( '#^https?://#i', $candidate_url ) ) {
				$clean_url = $candidate_url;
			}
		}

		$meta_title = defined( 'LTDH_META_VERIFICATION_SOURCE_TITLE' ) ? LTDH_META_VERIFICATION_SOURCE_TITLE : '_ltdh_verification_source_title';
		$source_title = get_post_meta( $program_id, $meta_title, true );
		if ( empty( $source_title ) && function_exists( 'get_field' ) ) {
			$source_title = get_field( $meta_title, $program_id );
		}
		$clean_title = ! empty( $source_title ) && is_string( $source_title ) ? trim( sanitize_text_field( $source_title ) ) : '';

		$meta_notes = defined( 'LTDH_META_VERIFICATION_NOTES' ) ? LTDH_META_VERIFICATION_NOTES : '_ltdh_verification_notes';
		$notes = get_post_meta( $program_id, $meta_notes, true );
		if ( empty( $notes ) && function_exists( 'get_field' ) ) {
			$notes = get_field( $meta_notes, $program_id );
		}
		$clean_notes = ! empty( $notes ) && is_string( $notes ) ? trim( sanitize_textarea_field( $notes ) ) : '';

		return [
			'status'            => $status,
			'status_label'      => $status_labels[ $status ] ?? 'Chưa xác minh',
			'is_verified'       => ( $status_verified === $status ),
			'is_partial'        => ( $status_partial === $status ),
			'is_unverified'     => ( $status_unverified === $status ),
			'last_verified_at'  => $formatted_date,
			'last_verified_raw' => is_string( $raw_date ) ? trim( $raw_date ) : '',
			'source_url'        => $clean_url,
			'source_title'      => $clean_title,
			'notes'             => $clean_notes,
		];
	}

	public static function get_program_verification( int $program_id ): array {
		return self::get_verification( $program_id );
	}

	/**
	 * Retrieve FAQ items for a Major.
	 *
	 * @param int      $major_id
	 * @param int|null $school_id
	 * @return array
	 */
	public static function get_major_faqs( int $major_id, ?int $school_id = null ): array {
		if ( ! $major_id ) {
			return [];
		}

		// CASE 1: Contextualized under a specific school
		if ( ! empty( $school_id ) ) {
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

			if ( class_exists( 'LTDH_School_Helpers' ) ) {
				$sch_faqs = LTDH_School_Helpers::get_faqs( $school_id );
				if ( ! empty( $sch_faqs ) ) {
					return $sch_faqs;
				}
			} elseif ( function_exists( 'ltdh_get_school_faqs' ) ) {
				$sch_faqs = ltdh_get_school_faqs( $school_id );
				if ( ! empty( $sch_faqs ) ) {
					return $sch_faqs;
				}
			}
		}

		// CASE 2: General Major view
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

		$raw_major_title = get_the_title( $major_id );
		if ( empty( $raw_major_title ) ) {
			return [];
		}

		$clean_major = preg_replace( '/^ngành\s+/iu', '', trim( $raw_major_title ) );
		$major_lower = mb_strtolower( $clean_major, 'UTF-8' );

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
	 * Retrieve FAQ items for a Training Type taxonomy term.
	 *
	 * @param WP_Term|int|string|null $term
	 * @return array
	 */
	public static function get_training_type_faqs( $term = null ): array {
		if ( empty( $term ) ) {
			return [];
		}

		$tax_training_type = defined( 'LTDH_TAX_TRAINING_TYPE' ) ? LTDH_TAX_TRAINING_TYPE : 'training_type';

		if ( is_numeric( $term ) ) {
			$term = get_term( (int) $term, $tax_training_type );
		} elseif ( is_string( $term ) ) {
			$term = get_term_by( 'slug', $term, $tax_training_type );
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
}
