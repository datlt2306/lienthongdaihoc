<?php
/**
 * ACF Field Groups — loaded from theme JSON.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'ltdh_load_acf_field_groups_from_json' );

function ltdh_load_acf_field_groups_from_json() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$json_path = get_template_directory() . '/inc/acf-import-fields.json';
	if ( ! file_exists( $json_path ) ) {
		return;
	}

	$data = json_decode( file_get_contents( $json_path ), true );
	if ( ! is_array( $data ) ) {
		return;
	}

	// Get existing field groups to avoid duplicates
	$existing_groups = acf_get_field_groups();
	$existing_keys = [];
	foreach ( $existing_groups as $eg ) {
		$existing_keys[] = $eg['key'] ?? '';
	}

	foreach ( $data as $item ) {
		// Only load field groups (items with "fields" key)
		if ( ! isset( $item['fields'] ) || ! is_array( $item['fields'] ) ) {
			continue;
		}

		if ( empty( $item['active'] ) ) {
			continue;
		}

		// Build the field group array for acf_add_local_field_group
		$field_group = [
			'key'                  => $item['key'],
			'title'                => $item['title'] ?? '',
			'fields'               => $item['fields'],
			'location'             => $item['location'] ?? [],
			'menu_order'           => $item['menu_order'] ?? 0,
			'position'             => $item['position'] ?? 'normal',
			'style'                => $item['style'] ?? 'default',
			'label_placement'      => $item['label_placement'] ?? 'top',
			'instruction_placement'=> $item['instruction_placement'] ?? 'label',
			'active'               => true,
			'description'          => $item['description'] ?? '',
			'show_in_rest'         => $item['show_in_rest'] ?? 0,
		];

		acf_add_local_field_group( $field_group );
	}
}

// Ensure ACF local JSON load points include inc directory
add_filter( 'acf/settings/load_json', 'ltdh_acf_json_load_point' );
function ltdh_acf_json_load_point( $paths ) {
	$paths[] = get_template_directory() . '/inc';
	$paths[] = get_template_directory() . '/acf-json';
	return $paths;
}

function ltdh_normalize_acf_fields_prefix( $fields, $prefix = 'acf' ) {
	if ( ! is_array( $fields ) ) {
		return $fields;
	}
	foreach ( $fields as &$field ) {
		if ( ! is_array( $field ) ) {
			continue;
		}
		if ( empty( $field['prefix'] ) ) {
			$field['prefix'] = $prefix;
		}
		if ( ! empty( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ) {
			$field['sub_fields'] = ltdh_normalize_acf_fields_prefix( $field['sub_fields'], $prefix );
		}
	}
	return $fields;
}

// Global guarantee: Every ACF field rendered in WP Admin has prefix 'acf' so inputs are named acf[...]
add_filter( 'acf/prepare_field', 'ltdh_force_field_prefix_acf', 1 );
function ltdh_force_field_prefix_acf( $field ) {
	if ( is_array( $field ) && empty( $field['prefix'] ) ) {
		$field['prefix'] = 'acf';
	}
	return $field;
}

// Ensure Program & Major field groups always use the structured layout from JSON in WP Admin
add_filter( 'acf/load_field_group', 'ltdh_filter_json_field_groups' );
function ltdh_filter_json_field_groups( $field_group ) {
	$target_groups = [ 'group_program_details', 'group_major_details' ];
	if ( isset( $field_group['key'] ) && in_array( $field_group['key'], $target_groups, true ) ) {
		$json_path = get_template_directory() . '/inc/acf-import-fields.json';
		if ( file_exists( $json_path ) ) {
			$data = json_decode( file_get_contents( $json_path ), true );
			if ( is_array( $data ) ) {
				foreach ( $data as $item ) {
					if ( isset( $item['key'] ) && $item['key'] === $field_group['key'] && ! empty( $item['fields'] ) ) {
						$field_group['fields'] = ltdh_normalize_acf_fields_prefix( $item['fields'] );
						break;
					}
				}
			}
		}
	}
	return $field_group;
}

add_filter( 'acf/load_fields', 'ltdh_filter_json_fields', 10, 2 );
function ltdh_filter_json_fields( $fields, $parent ) {
	$target_groups = [ 'group_program_details', 'group_major_details' ];
	if ( isset( $parent['key'] ) && in_array( $parent['key'], $target_groups, true ) ) {
		$json_path = get_template_directory() . '/inc/acf-import-fields.json';
		if ( file_exists( $json_path ) ) {
			$data = json_decode( file_get_contents( $json_path ), true );
			if ( is_array( $data ) ) {
				foreach ( $data as $item ) {
					if ( isset( $item['key'] ) && $item['key'] === $parent['key'] && ! empty( $item['fields'] ) ) {
						return ltdh_normalize_acf_fields_prefix( $item['fields'] );
					}
				}
			}
		}
	}
	return $fields;
}

// Ensure ACF labels in WordPress admin match frontend labels exactly
add_filter( 'acf/load_field/key=field_program_tuition', 'ltdh_override_field_program_tuition_label' );
function ltdh_override_field_program_tuition_label( $field ) {
	$field['label'] = 'Học phí chỉ từ';
	return $field;
}

add_filter( 'acf/load_field/key=field_program_duration', 'ltdh_override_field_program_duration_label' );
function ltdh_override_field_program_duration_label( $field ) {
	$field['label'] = 'Thời gian học';
	return $field;
}

add_filter( 'acf/load_field/key=field_program_period', 'ltdh_override_field_program_period_label' );
function ltdh_override_field_program_period_label( $field ) {
	$field['label'] = 'Hạn hồ sơ';
	return $field;
}

add_filter( 'acf/load_field/key=field_program_benefits', 'ltdh_override_field_program_benefits_label' );
function ltdh_override_field_program_benefits_label( $field ) {
	$field['label'] = 'Quyền lợi nổi bật';
	return $field;
}

add_filter( 'acf/load_field/key=field_program_faq', 'ltdh_override_field_program_faq_label' );
function ltdh_override_field_program_faq_label( $field ) {
	$field['label'] = 'Câu hỏi thường gặp';
	return $field;
}

// Ensure Program admin tab 'Điều kiện & Hồ sơ xét tuyển' is properly labeled
add_filter( 'acf/load_field/key=field_tab_prog_requirements', 'ltdh_override_field_program_requirements_tab_label' );
function ltdh_override_field_program_requirements_tab_label( $field ) {
	$field['label'] = 'Điều kiện & Hồ sơ xét tuyển';
	return $field;
}

// Remove/hide unwanted program & major fields in admin area dynamically
add_filter( 'acf/prepare_field/key=field_program_why_choose', '__return_false' );
add_filter( 'acf/prepare_field/key=field_program_schedule', '__return_false' );
// Keep degree_type and diploma_value visible for TT 27/2019/TT-BGDĐT compliance
// add_filter( 'acf/prepare_field/key=field_program_degree_type', '__return_false' );
// add_filter( 'acf/prepare_field/key=field_program_diploma_value', '__return_false' );
add_filter( 'acf/prepare_field/key=field_program_disadvantages', '__return_false' );

// Remove/hide 'Các mảng đào tạo chuyên sâu' and 'Cơ hội nghề nghiệp & Định hướng' from Major CPT in admin
add_filter( 'acf/prepare_field/key=field_tab_major_specializations', '__return_false' );
add_filter( 'acf/prepare_field/key=field_major_specializations', '__return_false' );
add_filter( 'acf/prepare_field/name=major_specializations', '__return_false' );
add_filter( 'acf/prepare_field/key=field_tab_major_career', '__return_false' );
add_filter( 'acf/prepare_field/key=field_major_opportunities', '__return_false' );
add_filter( 'acf/prepare_field/name=career_opportunities', '__return_false' );

// Remove duplicate and unused eligibility meta fields (use standard Taxonomies & pure admission requirements instead)
add_filter( 'acf/prepare_field/key=field_elig_campuses', '__return_false' );
add_filter( 'acf/prepare_field/name=elig_campuses', '__return_false' );
add_filter( 'acf/prepare_field/key=field_elig_training_types', '__return_false' );
add_filter( 'acf/prepare_field/name=elig_training_types', '__return_false' );
add_filter( 'acf/prepare_field/key=field_elig_min_education', '__return_false' );
add_filter( 'acf/prepare_field/name=elig_min_education', '__return_false' );
add_filter( 'acf/prepare_field/key=field_elig_max_grad_years', '__return_false' );
add_filter( 'acf/prepare_field/name=elig_max_grad_years', '__return_false' );
add_filter( 'acf/prepare_field/key=field_elig_notes', '__return_false' );
add_filter( 'acf/prepare_field/name=elig_notes', '__return_false' );

// Hide duplicate standalone 'group_program_eligibility' metabox since all its fields are now in Tab 2 of Program Details
add_filter( 'acf/prepare_field_group', 'ltdh_hide_duplicate_eligibility_group' );
function ltdh_hide_duplicate_eligibility_group( $group ) {
	if ( isset( $group['key'] ) && $group['key'] === 'group_program_eligibility' ) {
		return false;
	}
	return $group;
}

// Remove/hide 'Dịch vụ hỗ trợ' (Support Services) field in school details in admin area
add_filter( 'acf/prepare_field/name=support_services', '__return_false' );
add_filter( 'acf/prepare_field/name=dich_vu_ho_tro', '__return_false' );
add_filter( 'acf/prepare_field/name=services_support', '__return_false' );
add_filter( 'acf/prepare_field/key=field_school_support_services', '__return_false' );

add_filter( 'acf/prepare_field', 'ltdh_remove_support_services_field' );
function ltdh_remove_support_services_field( $field ) {
	if ( ! empty( $field['label'] ) && mb_strtolower( trim( $field['label'] ), 'UTF-8' ) === 'dịch vụ hỗ trợ' ) {
		return false;
	}
	return $field;
}

// Provide default pre-filled values in WP Admin for UTC exemption rules if fields are empty
add_filter( 'acf/load_value/name=exemption_title', 'ltdh_default_utc_exemption_title', 10, 3 );
function ltdh_default_utc_exemption_title( $value, $post_id, $field ) {
	if ( empty( $value ) && is_numeric( $post_id ) ) {
		$school_id = get_field( 'school_relationship', $post_id );
		$school_slug = $school_id ? get_post_field( 'post_name', $school_id ) : '';
		if ( false !== strpos( $school_slug, 'giao-thong-van-tai' ) ) {
			return 'Quy định miễn môn đối với Đại học GTVT (UTC)';
		}
	}
	return $value;
}

add_filter( 'acf/load_value/name=exemption_intro', 'ltdh_default_utc_exemption_intro', 10, 3 );
function ltdh_default_utc_exemption_intro( $value, $post_id, $field ) {
	if ( empty( $value ) && is_numeric( $post_id ) ) {
		$school_id = get_field( 'school_relationship', $post_id );
		$school_slug = $school_id ? get_post_field( 'post_name', $school_id ) : '';
		if ( false !== strpos( $school_slug, 'giao-thong-van-tai' ) ) {
			return 'Chương trình đào tạo hệ liên thông của UTC chỉ xem xét miễn trừ tối đa đối với 2 môn học dưới đây nếu học viên đáp ứng đủ điều kiện:';
		}
	}
	return $value;
}

add_filter( 'acf/load_value/name=exemption_items', 'ltdh_default_utc_exemption_items', 10, 3 );
function ltdh_default_utc_exemption_items( $value, $post_id, $field ) {
	if ( ( empty( $value ) || ! is_array( $value ) ) && is_numeric( $post_id ) ) {
		$school_id = get_field( 'school_relationship', $post_id );
		$school_slug = $school_id ? get_post_field( 'post_name', $school_id ) : '';
		if ( false !== strpos( $school_slug, 'giao-thong-van-tai' ) ) {
			return [
				[
					'field_exemption_subject_name'       => 'Giáo dục quốc phòng an ninh',
					'field_exemption_condition_note'     => 'Chỉ được xét miễn giảm khi học viên nộp chứng chỉ do Bộ Giáo dục và Đào tạo cấp theo phôi mẫu chuẩn (màu đỏ). Các loại phôi khác (kể cả phôi của các trường tự cấp) đều không được chấp nhận.',
					'field_exemption_cert_scores'        => [],
					'field_exemption_cert_note'          => '',
					'field_exemption_assessment_process' => '',
					'subject_name'       => 'Giáo dục quốc phòng an ninh',
					'condition_note'     => 'Chỉ được xét miễn giảm khi học viên nộp chứng chỉ do Bộ Giáo dục và Đào tạo cấp theo phôi mẫu chuẩn (màu đỏ). Các loại phôi khác (kể cả phôi của các trường tự cấp) đều không được chấp nhận.',
					'cert_scores'        => [],
					'cert_note'          => '',
					'assessment_process' => '',
				],
				[
					'field_exemption_subject_name'       => 'Tiếng Anh B1',
					'field_exemption_condition_note'     => 'Được xem xét quy đổi điểm khi sở hữu một trong các chứng chỉ quốc tế/quốc gia còn hiệu lực:',
					'field_exemption_cert_scores'        => [
						[ 'field_cert_name' => 'IELTS', 'field_cert_score' => '≥ 4.5', 'cert_name' => 'IELTS', 'min_score' => '≥ 4.5' ],
						[ 'field_cert_name' => 'TOEIC', 'field_cert_score' => '≥ 450', 'cert_name' => 'TOEIC', 'min_score' => '≥ 450' ],
						[ 'field_cert_name' => 'VSTEP', 'field_cert_score' => '≥ 5.0', 'cert_name' => 'VSTEP', 'min_score' => '≥ 5.0' ],
					],
					'field_exemption_cert_note'          => '* Riêng VSTEP: Chỉ nhận chứng chỉ do 1 trong 3 cơ sở đào tạo cấp: ĐH Quốc gia HN, ĐH Sư phạm HN, và ĐH Hà Nội.',
					'field_exemption_assessment_process' => 'Quy trình thẩm định & Quy đổi điểm: Sinh viên bắt buộc phải tham gia và vượt qua bài kiểm tra năng lực do bộ môn tổ chức. Nếu đạt yêu cầu, điểm số sẽ được quy đổi sang điểm 5 trên hệ thống.',
					'subject_name'       => 'Tiếng Anh B1',
					'condition_note'     => 'Được xem xét quy đổi điểm khi sở hữu một trong các chứng chỉ quốc tế/quốc gia còn hiệu lực:',
					'cert_scores'        => [
						[ 'cert_name' => 'IELTS', 'min_score' => '≥ 4.5' ],
						[ 'cert_name' => 'TOEIC', 'min_score' => '≥ 450' ],
						[ 'cert_name' => 'VSTEP', 'min_score' => '≥ 5.0' ],
					],
					'cert_note'          => '* Riêng VSTEP: Chỉ nhận chứng chỉ do 1 trong 3 cơ sở đào tạo cấp: ĐH Quốc gia HN, ĐH Sư phạm HN, và ĐH Hà Nội.',
					'assessment_process' => 'Quy trình thẩm định & Quy đổi điểm: Sinh viên bắt buộc phải tham gia và vượt qua bài kiểm tra năng lực do bộ môn tổ chức. Nếu đạt yêu cầu, điểm số sẽ được quy đổi sang điểm 5 trên hệ thống.',
				],
			];
		}
	}
	return $value;
}

// Provide default pre-filled values in WP Admin for CNTT Major if fields are empty
add_filter( 'acf/load_value/name=major_entry_roadmaps', 'ltdh_default_cntt_entry_roadmaps', 10, 3 );
function ltdh_default_cntt_entry_roadmaps( $value, $post_id, $field ) {
	if ( ( empty( $value ) || ! is_array( $value ) ) && is_numeric( $post_id ) ) {
		$slug = get_post_field( 'post_name', $post_id );
		if ( false !== strpos( $slug, 'cong-nghe-thong-tin' ) ) {
			return [
				[
					'field_roadmap_entry_level'   => 'Từ Cao đẳng CNTT',
					'field_roadmap_study_time'    => '1.5 - 2 năm',
					'field_roadmap_study_mode'    => 'Online từ xa / Tối & Cuối tuần',
					'field_roadmap_degree_output' => 'Cử nhân / Kỹ sư CNTT',
					'entry_level'                 => 'Từ Cao đẳng CNTT',
					'study_time'                  => '1.5 - 2 năm',
					'study_mode'                  => 'Online từ xa / Tối & Cuối tuần',
					'degree_output'               => 'Cử nhân / Kỹ sư CNTT',
				],
				[
					'field_roadmap_entry_level'   => 'Từ Trung cấp CNTT',
					'field_roadmap_study_time'    => '2.5 - 3 năm',
					'field_roadmap_study_mode'    => 'Online từ xa / Tối & Cuối tuần',
					'field_roadmap_degree_output' => 'Cử nhân / Kỹ sư CNTT',
					'entry_level'                 => 'Từ Trung cấp CNTT',
					'study_time'                  => '2.5 - 3 năm',
					'study_mode'                  => 'Online từ xa / Tối & Cuối tuần',
					'degree_output'               => 'Cử nhân / Kỹ sư CNTT',
				],
				[
					'field_roadmap_entry_level'   => 'Văn bằng 2 (Đã có 1 bằng ĐH/CĐ khác)',
					'field_roadmap_study_time'    => '1.5 - 2 năm',
					'field_roadmap_study_mode'    => 'Văn bằng 2 CNTT',
					'field_roadmap_degree_output' => 'Bằng Đại học thứ 2 CNTT',
					'entry_level'                 => 'Văn bằng 2 (Đã có 1 bằng ĐH/CĐ khác)',
					'study_time'                  => '1.5 - 2 năm',
					'study_mode'                  => 'Văn bằng 2 CNTT',
					'degree_output'               => 'Bằng Đại học thứ 2 CNTT',
				],
			];
		}
	}
	return $value;
}

add_filter( 'acf/load_value/name=major_specializations', 'ltdh_default_cntt_specializations', 10, 3 );
function ltdh_default_cntt_specializations( $value, $post_id, $field ) {
	if ( ( empty( $value ) || ! is_array( $value ) ) && is_numeric( $post_id ) ) {
		$slug = get_post_field( 'post_name', $post_id );
		if ( false !== strpos( $slug, 'cong-nghe-thong-tin' ) ) {
			return [
				[
					'field_spec_icon' => '💻',
					'field_spec_name' => 'Phát triển Phần mềm',
					'field_spec_desc' => 'Đào tạo lập trình Web, App di động (iOS/Android), hệ thống phần mềm quản trị doanh nghiệp (ERP, CRM).',
					'spec_icon'       => '💻',
					'spec_name'       => 'Phát triển Phần mềm',
					'spec_desc'       => 'Đào tạo lập trình Web, App di động (iOS/Android), hệ thống phần mềm quản trị doanh nghiệp (ERP, CRM).',
				],
				[
					'field_spec_icon' => '📊',
					'field_spec_name' => 'Khoa học Dữ liệu & AI',
					'field_spec_desc' => 'Xử lý dữ liệu lớn (Big Data), phân tích dữ liệu kinh doanh và ứng dụng Trí tuệ nhân tạo (Machine Learning/AI).',
					'spec_icon'       => '📊',
					'spec_name'       => 'Khoa học Dữ liệu & AI',
					'spec_desc'       => 'Xử lý dữ liệu lớn (Big Data), phân tích dữ liệu kinh doanh và ứng dụng Trí tuệ nhân tạo (Machine Learning/AI).',
				],
				[
					'field_spec_icon' => '🔒',
					'field_spec_name' => 'An toàn thông tin & Bảo mật',
					'field_spec_desc' => 'Bảo vệ hệ thống máy tính, an ninh mạng doanh nghiệp, phòng chống xâm nhập và bảo mật dữ liệu.',
					'spec_icon'       => '🔒',
					'spec_name'       => 'An toàn thông tin & Bảo mật',
					'spec_desc'       => 'Bảo vệ hệ thống máy tính, an ninh mạng doanh nghiệp, phòng chống xâm nhập và bảo mật dữ liệu.',
				],
				[
					'field_spec_icon' => '☁️',
					'field_spec_name' => 'Hạ tầng & Điện toán đám mây',
					'field_spec_desc' => 'Quản trị hạ tầng máy chủ, triển khai dịch vụ đám mây (AWS, Azure) và vận hành hệ thống DevOps.',
					'spec_icon'       => '☁️',
					'spec_name'       => 'Hạ tầng & Điện toán đám mây',
					'spec_desc'       => 'Quản trị hạ tầng máy chủ, triển khai dịch vụ đám mây (AWS, Azure) và vận hành hệ thống DevOps.',
				],
			];
		}
	}
	return $value;
}

// Automatically clean up legacy duplicate blocks from Major 1677 editor (table & bullets now in structured fields)
add_action( 'admin_init', 'ltdh_cleanup_major_legacy_content' );
function ltdh_cleanup_major_legacy_content() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$major_post = get_post( 1677 );
	if ( $major_post && ( false !== strpos( $major_post->post_content, 'Các mảng đào tạo chuyên sâu' ) || false !== strpos( $major_post->post_content, 'Tóm tắt thông tin' ) ) ) {
		$clean_content = "<p>Ngành <strong>Công nghệ thông tin (CNTT)</strong> là ngành học đào tạo chuyên sâu về việc thiết kế, xây dựng, vận hành và tối ưu hóa hệ thống phần mềm, cơ sở dữ liệu và hạ tầng mạng máy tính trong kỷ nguyên chuyển đổi số.</p>\n\n<p>Chương trình <strong>Liên thông Đại học ngành Công nghệ thông tin</strong> được thiết kế linh hoạt, tạo điều kiện thuận lợi nhất cho người đã tốt nghiệp Trung cấp, Cao đẳng hoặc đã có một văn bằng Đại học khác nhanh chóng hoàn thiện văn bằng Cử nhân / Kỹ sư chính quy chuẩn Bộ GD&ĐT, nâng bậc lương và mở rộng lộ trình thăng tiến sự nghiệp.</p>";
		wp_update_post( [
			'ID'           => 1677,
			'post_content' => $clean_content,
		] );
	}
}

// Automatically correct legacy exam text in VHVL/Tu-xa programs to pure admission by records (one-time migration)
add_action( 'init', 'ltdh_cleanup_vhvl_admission_requirements' );
function ltdh_cleanup_vhvl_admission_requirements() {
	if ( get_transient( 'ltdh_vhvl_admission_cleanup_done_v1' ) ) {
		return;
	}
	if ( ! function_exists( 'get_posts' ) ) {
		return;
	}
	$programs = get_posts( [
		'post_type'      => 'program',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
	] );

	if ( empty( $programs ) ) {
		set_transient( 'ltdh_vhvl_admission_cleanup_done_v1', 1, DAY_IN_SECONDS * 30 );
		return;
	}

	foreach ( $programs as $pid ) {
		$slug = get_post_field( 'post_name', $pid );
		if ( str_contains( $slug, 'vua-hoc-vua-lam' ) || str_contains( $slug, 'tu-xa' ) ) {
			$req = get_post_meta( $pid, 'admission_requirements', true );
			if ( ! empty( $req ) && ( str_contains( $req, 'Thi tuyển 3 môn' ) || str_contains( $req, 'Phương thức 2: Thi tuyển' ) ) ) {
				$clean_req = '<p>Chương trình tuyển sinh Liên thông Đại học hệ Vừa học vừa làm (VHVL) áp dụng phương thức <strong>Xét tuyển hồ sơ văn bằng</strong> (không phải thi tuyển):</p><ul><li><strong>Đối tượng tuyển sinh:</strong> Người đã tốt nghiệp Cao đẳng hoặc Trung cấp đúng ngành/ngành gần Công nghệ thông tin; hoặc người đã có bằng Đại học khác có nguyện vọng học văn bằng 2.</li><li><strong>Tiêu chí xét tuyển:</strong> Xét duyệt dựa trên kết quả học tập ghi trên văn bằng và bảng điểm bậc tốt nghiệp trước đó. Điểm trung bình tích lũy toàn khóa đạt yêu cầu theo Quy chế tuyển sinh của nhà trường.</li><li><strong>Ngưỡng đảm bảo chất lượng:</strong> Thí sinh hoàn thiện đầy đủ hồ sơ hợp lệ và nộp đúng thời hạn quy định của các đợt tuyển sinh.</li></ul>';
				update_post_meta( $pid, 'admission_requirements', $clean_req );
			}
		}
	}
	set_transient( 'ltdh_vhvl_admission_cleanup_done_v1', 1, DAY_IN_SECONDS * 30 );
}

// Clean up deprecated "Tự chủ thời gian và không gian học tập, phôi bằng tốt nghiệp không ghi hình thức đào tạo." placeholder in program benefits (one-time migration)
add_action( 'init', 'ltdh_cleanup_program_benefits_placeholder' );
function ltdh_cleanup_program_benefits_placeholder() {
	if ( get_transient( 'ltdh_benefits_cleanup_done_v1' ) ) {
		return;
	}
	$programs = get_posts( [
		'post_type'      => 'program',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'post_status'    => 'any',
	] );

	if ( empty( $programs ) ) {
		set_transient( 'ltdh_benefits_cleanup_done_v1', 1, DAY_IN_SECONDS * 30 );
		return;
	}

	foreach ( $programs as $pid ) {
		$benefits = get_post_meta( $pid, 'program_benefits', true );
		if ( ! empty( $benefits ) && ( str_contains( $benefits, 'phôi bằng tốt nghiệp không ghi hình thức đào tạo' ) || str_contains( $benefits, 'Tự chủ thời gian' ) ) ) {
			$new_benefits = str_replace( 'Tự chủ thời gian và không gian học tập, phôi bằng tốt nghiệp không ghi hình thức đào tạo.', '', $benefits );
			$new_benefits = str_replace( 'phôi bằng tốt nghiệp không ghi hình thức đào tạo.', '', $new_benefits );
			$new_benefits = trim( strip_tags( $new_benefits ) ) ? trim( $new_benefits ) : '';
			update_post_meta( $pid, 'program_benefits', $new_benefits );
		}
	}
	set_transient( 'ltdh_benefits_cleanup_done_v1', 1, DAY_IN_SECONDS * 30 );
}
// Register ACF Theme Options Page
add_action( 'acf/init', 'ltdh_register_acf_options_page' );
function ltdh_register_acf_options_page() {
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page( [
			'page_title' => 'Cấu Hình Chung Website',
			'menu_title' => 'Cấu Hình Chung',
			'menu_slug'  => 'ltdh-settings',
			'capability' => 'edit_posts',
			'redirect'   => false,
			'icon_url'   => 'dashicons-admin-generic',
			'position'   => 59,
		] );
	}
}

add_action( 'admin_init', 'ltdh_prepopulate_faq_items_if_empty' );
function ltdh_prepopulate_faq_items_if_empty() {
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$existing = get_field( 'faq_items', 'option' );
	if ( empty( $existing ) || ! is_array( $existing ) ) {
		$default_faqs = [
			[
				'question' => 'Bằng tốt nghiệp Đại học Liên thông / Từ xa có ghi hình thức đào tạo không?',
				'answer'   => 'Theo Thông tư 27/2019/TT-BGDĐT của Bộ Giáo dục và Đào tạo, từ ngày 01/03/2020 trên bằng tốt nghiệp Đại học không còn ghi hình thức đào tạo (như Từ xa hay Vừa học vừa làm). Tấm bằng do các trường đại học đối tác cấp có giá trị pháp lý tương đương bằng cử nhân/kỹ sư chính quy, được thi cao học, công chức và nâng bậc lương.',
			],
			[
				'question' => 'Thời gian đào tạo Liên thông Đại học mất bao lâu?',
				'answer'   => 'Thời gian đào tạo trung bình từ 1.5 đến 2 năm tùy theo khối ngành. Đặc biệt, học viên đã có bằng Trung cấp hoặc Cao đẳng đúng ngành sẽ được xét miễn giảm các học phần tương đương để rút ngắn thời gian hoàn thành chương trình.',
			],
			[
				'question' => 'Hình thức học trực tuyến (Online/Từ xa) diễn ra như thế nào?',
				'answer'   => 'Học viên học hoàn toàn qua hệ thống E-Learning trực tuyến của nhà trường. Bạn có thể tự chủ thời gian học mọi lúc mọi nơi qua bài giảng video, slide và tham gia trao đổi trực tiếp với giảng viên qua các buổi ôn tập online cuối tuần.',
			],
			[
				'question' => 'Hồ sơ xét tuyển Liên thông Đại học bao gồm những gì?',
				'answer'   => 'Hồ sơ xét tuyển cơ bản gồm: 01 Phiếu đăng ký theo mẫu của trường, 02 bản sao công chứng Bằng + Bảng điểm tốt nghiệp Cao đẳng/Trung cấp, 01 bản sao CCCD và 02 ảnh 3x4. Chuyên viên tuyển sinh sẽ hỗ trợ gửi mẫu và hướng dẫn chuẩn bị chi tiết.',
			],
			[
				'question' => 'Bằng Đại học Liên thông có đủ điều kiện đăng ký học Cao học / Thạc sĩ không?',
				'answer'   => 'Hoàn toàn đủ điều kiện. Tấm bằng đại học sau khi tốt nghiệp có đầy đủ tư cách pháp lý để bạn tiếp tục đăng ký thi tuyển Thạc sĩ, Cao học tại tất cả các trường Đại học trên toàn quốc hoặc du học nước ngoài.',
			],
		];
		update_field( 'faq_items', $default_faqs, 'option' );
	}
}

// Use classic editor for Programs, Schools, and Majors to ensure reliable native ACF metabox editing and saving
add_filter( 'use_block_editor_for_post_type', 'ltdh_disable_block_editor_for_structured_cpts', 99, 2 );
function ltdh_disable_block_editor_for_structured_cpts( $use_block_editor, $post_type ) {
	if ( in_array( $post_type, [ 'program', 'school', 'major' ], true ) ) {
		return false;
	}
	return $use_block_editor;
}

add_filter( 'use_block_editor_for_post', 'ltdh_disable_block_editor_for_structured_posts', 99, 2 );
function ltdh_disable_block_editor_for_structured_posts( $use_block_editor, $post ) {
	if ( $post && in_array( get_post_type( $post ), [ 'program', 'school', 'major' ], true ) ) {
		return false;
	}
	return $use_block_editor;
}

// Ensure TinyMCE editors in hidden tabs always sync content to textareas prior to form submission
add_action( 'admin_footer-post.php', 'ltdh_admin_tinymce_sync_on_submit' );
add_action( 'admin_footer-post-new.php', 'ltdh_admin_tinymce_sync_on_submit' );
function ltdh_admin_tinymce_sync_on_submit() {
	global $post;
	if ( ! $post || ! in_array( $post->post_type, [ 'program', 'school', 'major' ], true ) ) {
		return;
	}
	?>
	<script>
	jQuery(document).ready(function($) {
		function syncMceEditors() {
			if (typeof tinyMCE !== 'undefined') {
				tinyMCE.triggerSave();
			}
		}
		$('form#post').on('submit', syncMceEditors);
		$('#publish, #save-post').on('click', syncMceEditors);
	});
	</script>
	<?php
}

// Ensure standard ACF reference meta keys (_field_name) exist for all programs
add_action( 'init', 'ltdh_ensure_program_acf_meta_keys' );
function ltdh_ensure_program_acf_meta_keys() {
	if ( get_transient( 'ltdh_program_meta_keys_synced_v1' ) ) {
		return;
	}
	if ( ! function_exists( 'get_posts' ) ) {
		return;
	}
	$programs = get_posts( [
		'post_type'      => 'program',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
	] );
	if ( ! empty( $programs ) ) {
		foreach ( $programs as $pid ) {
			if ( ! get_post_meta( $pid, '_admission_requirements', true ) ) {
				update_post_meta( $pid, '_admission_requirements', 'field_program_requirements' );
			}
			if ( ! get_post_meta( $pid, '_required_documents', true ) ) {
				update_post_meta( $pid, '_required_documents', 'field_program_documents' );
			}
		}
	}
	set_transient( 'ltdh_program_meta_keys_synced_v1', 1, DAY_IN_SECONDS * 30 );
}

/**
 * Returns a mapping of ACF field keys to meta keys from the theme JSON configuration.
 */
function ltdh_get_theme_acf_key_to_name_map() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	$map = [];
	$json_path = get_template_directory() . '/inc/acf-import-fields.json';
	if ( file_exists( $json_path ) ) {
		$data = json_decode( file_get_contents( $json_path ), true );
		if ( is_array( $data ) ) {
			foreach ( $data as $group ) {
				if ( ! empty( $group['fields'] ) && is_array( $group['fields'] ) ) {
					foreach ( $group['fields'] as $f ) {
						if ( ! empty( $f['key'] ) && ! empty( $f['name'] ) ) {
							$map[ $f['key'] ] = $f['name'];
						}
					}
				}
			}
		}
	}
	return $map;
}

/**
 * Comprehensive fail-safe save handler for Program, School, and Major custom fields.
 * Handles $_POST['acf'], top-level $_POST['field_*'], and top-level $_POST['meta_key'].
 */
add_action( 'save_post', 'ltdh_save_custom_fields_fail_safe', 25, 2 );
function ltdh_save_custom_fields_fail_safe( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! $post || ! in_array( $post->post_type, [ 'program', 'school', 'major' ], true ) ) {
		return;
	}

	$field_map = ltdh_get_theme_acf_key_to_name_map();

	// 1. Process standard $_POST['acf'] if populated
	if ( ! empty( $_POST['acf'] ) && is_array( $_POST['acf'] ) ) {
		foreach ( $_POST['acf'] as $key => $value ) {
			if ( str_starts_with( $key, 'field_' ) ) {
				$field = function_exists( 'acf_get_field' ) ? acf_get_field( $key ) : null;
				$meta_name = ( $field && ! empty( $field['name'] ) ) ? $field['name'] : ( $field_map[ $key ] ?? '' );
				if ( $meta_name ) {
					update_post_meta( $post_id, $meta_name, $value );
					update_post_meta( $post_id, '_' . $meta_name, $key );
				}
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	// 2. Process flat POST variables if submitted without acf[...] container
	foreach ( $field_map as $field_key => $meta_name ) {
		if ( isset( $_POST[ $field_key ] ) ) {
			$val = $_POST[ $field_key ];
			update_post_meta( $post_id, $meta_name, $val );
			update_post_meta( $post_id, '_' . $meta_name, $field_key );
		} elseif ( isset( $_POST[ $meta_name ] ) ) {
			$val = $_POST[ $meta_name ];
			update_post_meta( $post_id, $meta_name, $val );
			update_post_meta( $post_id, '_' . $meta_name, $field_key );
		}
	}
}





