<?php
/**
 * School Domain Helpers.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTDH_School_Helpers {

	/**
	 * Detect the active school context ID from current page or query parameter.
	 *
	 * @return int School ID or 0 if not found.
	 */
	public static function get_current_context_school_id(): int {
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
	 * Get the Zalo group URL for a specific school or current context school.
	 *
	 * @param int $school_id School ID (0 to auto-detect from context).
	 * @return string Zalo URL.
	 */
	public static function get_zalo_group_url( int $school_id = 0 ): string {
		if ( ! $school_id ) {
			$school_id = self::get_current_context_school_id();
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
		return function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : '';
	}

	public static function get_school_zalo_group_url( int $school_id = 0 ): string {
		return self::get_zalo_group_url( $school_id );
	}

	/**
	 * Get the hotline for a specific school or the current context school.
	 *
	 * @param int $school_id School ID (0 to auto-detect from context).
	 * @return string Hotline string.
	 */
	public static function get_hotline( int $school_id = 0 ): string {
		if ( ! $school_id ) {
			$school_id = self::get_current_context_school_id();
		}
		if ( $school_id ) {
			$phone = get_post_meta( $school_id, 'hotline', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'hotline', $school_id ) : '' );
			if ( ! empty( $phone ) && is_string( $phone ) ) {
				return trim( $phone );
			}
		}
		return function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '';
	}

	public static function get_school_hotline( int $school_id = 0 ): string {
		return self::get_hotline( $school_id );
	}

	/**
	 * Get admission forms list for a school.
	 *
	 * @param int $school_id
	 * @return array
	 */
	public static function get_admission_forms( int $school_id ): array {
		if ( ! $school_id ) {
			return [];
		}

		$forms = [];

		// 1. Link Google Drive chung của trường
		$school_drive = function_exists( 'ltdh_get_field_or_meta' ) ? ltdh_get_field_or_meta( 'admission_drive_folder_url', $school_id ) : ( get_field( 'admission_drive_folder_url', $school_id ) ?: get_post_meta( $school_id, 'admission_drive_folder_url', true ) );
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
		$by_type_rows = function_exists( 'ltdh_get_field_or_meta' ) ? ltdh_get_field_or_meta( 'admission_forms_by_type', $school_id ) : ( get_field( 'admission_forms_by_type', $school_id ) ?: get_post_meta( $school_id, 'admission_forms_by_type', true ) );
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
			$def_bundle = function_exists( 'ltdh_get_field_or_meta' ) ? ltdh_get_field_or_meta( 'default_bundle_file', $school_id ) : ( get_field( 'default_bundle_file', $school_id ) ?: get_post_meta( $school_id, 'default_bundle_file', true ) );
			$b_info     = function_exists( 'ltdh_normalize_admission_file_info' ) ? ltdh_normalize_admission_file_info( $def_bundle ) : [ 'url' => '', 'filename' => '' ];
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
				$def_single = function_exists( 'ltdh_get_field_or_meta' ) ? ltdh_get_field_or_meta( 'default_admission_form', $school_id ) : ( get_field( 'default_admission_form', $school_id ) ?: get_post_meta( $school_id, 'default_admission_form', true ) );
				$s_info     = function_exists( 'ltdh_normalize_admission_file_info' ) ? ltdh_normalize_admission_file_info( $def_single ) : [ 'url' => '', 'filename' => '' ];
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

	public static function get_school_admission_forms( int $school_id ): array {
		return self::get_admission_forms( $school_id );
	}

	/**
	 * Get school order priority.
	 * Checks ACF 'order', then postmeta 'order', then native 'menu_order'.
	 *
	 * @param int $school_id
	 * @return int
	 */
	public static function get_order( int $school_id ): int {
		if ( ! $school_id ) {
			return 0;
		}
		$order = function_exists( 'get_field' ) ? get_field( 'order', $school_id ) : null;
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

	public static function get_school_order( int $school_id ): int {
		return self::get_order( $school_id );
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
	public static function sort_by_order( array $posts ): array {
		if ( empty( $posts ) ) {
			return [];
		}

		usort( $posts, function( $a, $b ) {
			$id_a = is_object( $a ) ? $a->ID : intval( $a );
			$id_b = is_object( $b ) ? $b->ID : intval( $b );

			$order_a = self::get_order( $id_a );
			$order_b = self::get_order( $id_b );

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
			$feat_a = ( function_exists( 'get_field' ) && get_field( 'is_featured', $id_a ) ) ? 1 : 0;
			$feat_b = ( function_exists( 'get_field' ) && get_field( 'is_featured', $id_b ) ) ? 1 : 0;
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

	public static function sort_schools_by_order( array $posts ): array {
		return self::sort_by_order( $posts );
	}

	/**
	 * Synchronize ACF field 'order' with native post menu_order for School CPT.
	 */
	public static function sync_order( $post_id, $post = null, $update = null ) {
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

		if ( function_exists( 'ltdh_flush_all_content_transients' ) ) {
			ltdh_flush_all_content_transients();
		}
	}

	public static function sync_school_order( $post_id, $post = null, $update = null ) {
		return self::sync_order( $post_id, $post, $update );
	}

	/**
	 * Get unified location display label for a school (Campus or Region).
	 *
	 * Prioritizes physical campus (e.g. 'Hà Nội', 'TP. Hồ Chí Minh'),
	 * then city from address, then region ('Miền Bắc').
	 *
	 * @param int $school_id
	 * @return string
	 */
	public static function get_location_label( int $school_id ): string {
		if ( ! $school_id ) {
			return 'Chưa cập nhật';
		}

		$tax_campus = defined( 'LTDH_TAX_CAMPUS' ) ? LTDH_TAX_CAMPUS : 'campus';
		// 1. Campus taxonomy term (Cơ sở đào tạo)
		$campus_terms = wp_get_post_terms( $school_id, $tax_campus );
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
		$address = function_exists( 'get_field' ) ? get_field( 'address', $school_id ) : get_post_meta( $school_id, 'address', true );
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
		$tax_region   = defined( 'LTDH_TAX_REGION' ) ? LTDH_TAX_REGION : 'region';
		$region_terms = wp_get_post_terms( $school_id, $tax_region );
		if ( ! is_wp_error( $region_terms ) && ! empty( $region_terms ) ) {
			return $region_terms[0]->name;
		}

		return 'Hà Nội';
	}

	public static function get_school_location_label( int $school_id ): string {
		return self::get_location_label( $school_id );
	}

	/**
	 * Get school region label ('Miền Bắc', 'Miền Trung', 'Miền Nam').
	 *
	 * @param int $school_id
	 * @return string
	 */
	public static function get_region_label( int $school_id ): string {
		if ( ! $school_id ) {
			return '';
		}

		// 1. Region taxonomy term (Khu vực)
		$tax_region   = defined( 'LTDH_TAX_REGION' ) ? LTDH_TAX_REGION : 'region';
		$region_terms = wp_get_post_terms( $school_id, $tax_region );
		if ( ! is_wp_error( $region_terms ) && ! empty( $region_terms ) ) {
			return $region_terms[0]->name;
		}

		// 2. Fallback: Infer region from address
		$address = (string) ( function_exists( 'get_field' ) ? get_field( 'address', $school_id ) : get_post_meta( $school_id, 'address', true ) );
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

	public static function get_school_region_label( int $school_id ): string {
		return self::get_region_label( $school_id );
	}

	/**
	 * Cached featured schools dataset for homepage.
	 *
	 * @return array
	 */
	public static function get_cached_featured(): array {
		$cache_key = 'ltdh_featured_schools_data_v18';
		$data      = get_transient( $cache_key );
		if ( false !== $data && is_array( $data ) ) {
			return $data;
		}

		$schools_candidates = get_posts( [
			'post_type'   => 'school',
			'numberposts' => 100,
			'post_status' => 'publish',
		] );

		$schools_candidates = self::sort_by_order( $schools_candidates );
		$top_schools        = array_slice( $schools_candidates, 0, 10 );

		$data = [];
		if ( ! empty( $top_schools ) ) {
			foreach ( $top_schools as $school_post ) {
				$school_id   = $school_post->ID;
				$address     = function_exists( 'get_field' ) ? get_field( 'address', $school_id ) : get_post_meta( $school_id, 'address', true );
				$hotline     = self::get_hotline( $school_id );
				$thumb_url   = self::get_cover_url( $school_id, 'medium' );
				$logo_id     = self::get_image_id( $school_id );
				$school_code = get_post_meta( $school_id, 'school_code', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'school_code', $school_id ) : '' );
				$en_name     = get_post_meta( $school_id, 'english_name', true );
				if ( empty( $en_name ) && function_exists( 'get_field' ) ) {
					$en_name = get_field( 'english_name', $school_id );
				}
				if ( ( empty( $en_name ) || 'University' === $en_name ) && ! empty( $school_code ) ) {
					$en_name = $school_code . ' University';
				}

				$region = self::get_region_label( $school_id );

				$school_progs = get_posts( [
					'post_type'   => 'program',
					'numberposts' => -1,
					'fields'      => 'ids',
					'meta_query'  => [
						[
							'key'     => 'school_relationship',
							'value'   => $school_id,
							'compare' => '=',
						],
					],
				] );

				$systems = [];
				if ( ! empty( $school_progs ) ) {
					foreach ( $school_progs as $sp_id ) {
						$terms = wp_get_post_terms( $sp_id, 'training_type' );
						if ( ! is_wp_error( $terms ) ) {
							foreach ( $terms as $t ) {
								$systems[ $t->slug ] = $t->name;
							}
						}
					}
				}

				$systems_label = '';
				if ( ! empty( $systems ) ) {
					if ( 1 === count( $systems ) && ( isset( $systems['tu-xa'] ) || isset( $systems['dao-tao-tu-xa'] ) ) ) {
						$systems_label = '';
					} else {
						$systems_label = implode( ' · ', $systems );
					}
				}

				$data[] = [
					'id'            => $school_id,
					'title'         => get_the_title( $school_id ),
					'permalink'     => get_permalink( $school_id ),
					'code'          => $school_code,
					'address'       => $address,
					'location'      => self::get_location_label( $school_id ),
					'region'        => $region,
					'hotline'       => $hotline,
					'thumb_url'     => $thumb_url,
					'cover_id'      => self::get_cover_id( $school_id ),
					'logo_id'       => $logo_id,
					'en_name'       => $en_name,
					'systems_label' => $systems_label,
					'school_types'  => self::get_training_types( $school_id ),
					'prog_count'    => self::get_unique_majors_count( $school_id ),
				];
			}
			wp_reset_postdata();
		}

		set_transient( $cache_key, $data, HOUR_IN_SECONDS );
		return $data;
	}

	public static function get_cached_featured_schools(): array {
		return self::get_cached_featured();
	}

	/**
	 * Get school logo attachment ID.
	 *
	 * @param int $school_id
	 * @return int
	 */
	public static function get_image_id( int $school_id ): int {
		if ( function_exists( 'get_field' ) ) {
			$logo_id = get_field( 'logo', $school_id );
			if ( $logo_id ) {
				if ( is_numeric( $logo_id ) ) {
					return (int) $logo_id;
				}
				if ( is_array( $logo_id ) && ! empty( $logo_id['ID'] ) ) {
					return (int) $logo_id['ID'];
				}
			}
		}
		return 0;
	}

	public static function get_school_image_id( int $school_id ): int {
		return self::get_image_id( $school_id );
	}

	/**
	 * Generate SVG placeholder logo with school code.
	 *
	 * @param string $code
	 * @param string $title
	 * @return string Data URI SVG
	 */
	public static function generate_svg_logo( string $code, string $title = '' ): string {
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

	public static function generate_school_svg_logo( string $code, string $title = '' ): string {
		return self::generate_svg_logo( $code, $title );
	}

	/**
	 * Get resolved school logo URL.
	 *
	 * @param mixed  $school_id
	 * @param string $size
	 * @return string
	 */
	public static function get_logo_url( $school_id, string $size = 'thumbnail' ): string {
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

			$image_id = self::get_image_id( $school_id );
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
			return self::generate_svg_logo( $school_code, $title );
		}

		return self::generate_svg_logo( 'UNI', 'Đại học' );
	}

	public static function get_school_logo_url( $school_id, string $size = 'thumbnail' ): string {
		return self::get_logo_url( $school_id, $size );
	}

	/**
	 * Get school cover image attachment ID (isolated from logo).
	 * Traverses hierarchy and caches resolved cover ID in postmeta to prevent N+1 queries.
	 *
	 * @param mixed $school_id
	 * @return int Attachment ID or 0.
	 */
	public static function get_cover_id( $school_id ): int {
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

		$logo_id  = self::get_image_id( $school_id );
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

	public static function get_school_cover_id( $school_id ): int {
		return self::get_cover_id( $school_id );
	}

	/**
	 * Get school cover / banner photo URL.
	 *
	 * @param mixed  $school_id
	 * @param string $size
	 * @return string
	 */
	public static function get_cover_url( $school_id, string $size = 'medium' ): string {
		if ( is_array( $school_id ) ) {
			$school_id = ! empty( $school_id ) ? ( is_object( $school_id[0] ) ? $school_id[0]->ID : $school_id[0] ) : 0;
		} elseif ( is_object( $school_id ) ) {
			$school_id = $school_id->ID;
		}
		$school_id = intval( $school_id );

		$theme_dir = get_template_directory();
		$theme_uri = get_template_directory_uri();

		if ( $school_id > 0 ) {
			$cover_id = self::get_cover_id( $school_id );
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
					$logo_id  = self::get_image_id( $school_id );
					$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
					$raw_url  = is_array( $banner ) ? ( $banner['sizes'][ $size ] ?? ( $banner['url'] ?? '' ) ) : ( is_string( $banner ) ? $banner : '' );
					if ( ! empty( $raw_url ) && $raw_url !== $logo_url && false === strpos( $raw_url, 'cropped-logo' ) && false === strpos( strtolower( $raw_url ), 'logo' ) ) {
						return $raw_url;
					}
				}
			}
		}

		// Fallback campus cover photos: ưu tiên WebP chuẩn kích thước thẻ card (< 30 KiB)
		if ( ( 'medium' === $size || 'thumbnail' === $size ) && file_exists( $theme_dir . '/assets/images/banner-fallback-300x180.webp' ) ) {
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

	public static function get_school_cover_url( $school_id, string $size = 'medium' ): string {
		return self::get_cover_url( $school_id, $size );
	}

	/**
	 * Render school thumbnail markup.
	 *
	 * @param int    $school_id
	 * @param string $size
	 * @param string $classes
	 * @return void
	 */
	public static function render_thumbnail( int $school_id, string $size = 'thumbnail', string $classes = 'h-14 w-14 object-cover border border-slate-100 bg-white rounded-lg' ): void {
		$image_id = self::get_image_id( $school_id );
		if ( $image_id ) {
			echo wp_get_attachment_image( $image_id, $size, false, [
				'class'   => $classes,
				'loading' => 'lazy',
				'alt'     => sprintf( 'Logo %s', get_the_title( $school_id ) ),
			] );
			return;
		}

		$fallback_classes = preg_replace( '/\bobject-(cover|contain)\b/', '', $classes );
		$fallback_classes = trim( preg_replace( '/\s+/', ' ', $fallback_classes ) );
		printf(
			'<div class="%s bg-blue-50 text-brand-primary font-display font-black text-sm flex items-center justify-center" aria-hidden="true">UNI</div>',
			esc_attr( $fallback_classes )
		);
	}

	public static function render_school_thumbnail( int $school_id, string $size = 'thumbnail', string $classes = 'h-14 w-14 object-cover border border-slate-100 bg-white rounded-lg' ): void {
		self::render_thumbnail( $school_id, $size, $classes );
	}

	/**
	 * Get count of unique admitting majors under a school.
	 *
	 * @param int $school_id
	 * @return int
	 */
	public static function get_unique_majors_count( int $school_id ): int {
		$cache_key = 'ltdh_school_majors_count_' . $school_id;
		$cached    = wp_cache_get( $cache_key, 'ltdh' );
		if ( false !== $cached ) {
			return (int) $cached;
		}

		$allowed_slugs = [ 'chinh-quy', 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ];
		$tax_tt        = defined( 'LTDH_TAX_TRAINING_TYPE' ) ? LTDH_TAX_TRAINING_TYPE : 'training_type';
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
					'taxonomy' => $tax_tt,
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
							'taxonomy' => $tax_tt,
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

	public static function get_school_unique_majors_count( int $school_id ): int {
		return self::get_unique_majors_count( $school_id );
	}

	/**
	 * Get active training types rollup for a school.
	 *
	 * @param int    $school_id      School post ID.
	 * @param string $output_format  'names', 'slugs', or 'terms'/'objects'.
	 * @return array
	 */
	public static function get_training_types( int $school_id, string $output_format = 'names' ): array {
		if ( ! $school_id ) {
			return [];
		}

		$cache_key = 'ltdh_school_tt_' . $school_id . '_' . $output_format;
		$cached    = wp_cache_get( $cache_key, 'ltdh' );
		if ( false !== $cached ) {
			return (array) $cached;
		}

		$allowed_slugs = [ 'chinh-quy', 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ];
		$tax_tt        = defined( 'LTDH_TAX_TRAINING_TYPE' ) ? LTDH_TAX_TRAINING_TYPE : 'training_type';

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
					'taxonomy' => $tax_tt,
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
							'taxonomy' => $tax_tt,
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
			$direct_terms = wp_get_post_terms( $school_id, $tax_tt );
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
			$prog_terms = wp_get_post_terms( $prog_id, $tax_tt );
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

	public static function get_school_training_types( int $school_id, string $output_format = 'names' ): array {
		return self::get_training_types( $school_id, $output_format );
	}

	/**
	 * Get school code abbreviation (e.g. PTIT, NEU).
	 *
	 * @param int $school_id
	 * @return string
	 */
	public static function get_code( int $school_id ): string {
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

	public static function get_school_code( int $school_id ): string {
		return self::get_code( $school_id );
	}

	/**
	 * Get school admission requirements HTML.
	 *
	 * @param int $school_id
	 * @return string
	 */
	public static function get_admission_requirements( int $school_id ): string {
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

	public static function get_school_admission_requirements( int $school_id ): string {
		return self::get_admission_requirements( $school_id );
	}

	/**
	 * Get school required documents HTML list.
	 *
	 * @param int $school_id
	 * @return string
	 */
	public static function get_required_documents( int $school_id ): string {
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

	public static function get_school_required_documents( int $school_id ): string {
		return self::get_required_documents( $school_id );
	}

	/**
	 * Get school FAQs with structured items.
	 *
	 * @param int $school_id
	 * @return array
	 */
	public static function get_faqs( int $school_id ): array {
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

		$training_types = self::get_training_types( $school_id );

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

	public static function get_school_faqs( int $school_id ): array {
		return self::get_faqs( $school_id );
	}
}

// ----------------------------------------------------------------------------
// Action & Filter Hooks for School Domain
// ----------------------------------------------------------------------------

add_action( 'save_post_school', [ 'LTDH_School_Helpers', 'sync_order' ], 25, 3 );

add_action( 'acf/save_post', function( $post_id ) {
	if ( 'school' === get_post_type( $post_id ) ) {
		$order_val = function_exists( 'get_field' ) ? get_field( 'order', $post_id ) : null;
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
	if ( function_exists( 'ltdh_flush_all_content_transients' ) ) {
		ltdh_flush_all_content_transients();
	}
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

add_action( 'save_post_school', function( $post_id ) {
	delete_post_meta( $post_id, '_ltdh_cover_id_cache' );
} );
