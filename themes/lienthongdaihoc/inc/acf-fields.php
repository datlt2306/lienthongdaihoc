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
						$field_group['fields'] = $item['fields'];
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
						return $item['fields'];
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

// Remove/hide unwanted program fields in admin area dynamically
add_filter( 'acf/prepare_field/key=field_program_why_choose', '__return_false' );
add_filter( 'acf/prepare_field/key=field_program_schedule', '__return_false' );
add_filter( 'acf/prepare_field/key=field_program_target_students', '__return_false' );
add_filter( 'acf/prepare_field/key=field_program_degree_type', '__return_false' );
add_filter( 'acf/prepare_field/key=field_program_diploma_value', '__return_false' );
add_filter( 'acf/prepare_field/key=field_program_disadvantages', '__return_false' );

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




