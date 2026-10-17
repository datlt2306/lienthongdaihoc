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
			'hide_on_screen'       => $item['hide_on_screen'] ?? [],
			'active'               => true,
			'description'          => $item['description'] ?? '',
			'show_in_rest'         => $item['show_in_rest'] ?? 0,
		];

		acf_add_local_field_group( $field_group );
	}
}

/**
 * Ensure School FAQ Tab and Repeater are registered directly to group_school_details.
 */
add_action( 'acf/init', 'ltdh_register_school_faq_acf_fields', 20 );
function ltdh_register_school_faq_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field' ) ) {
		return;
	}

	// Register Tab
	acf_add_local_field( [
		'key'       => 'field_tab_school_faq',
		'label'     => 'Hỏi đáp (FAQ)',
		'name'      => '',
		'type'      => 'tab',
		'parent'    => 'group_school_details',
		'placement' => 'top',
		'endpoint'  => 0,
	] );

	// Register Repeater
	acf_add_local_field( [
		'key'          => 'field_school_faq',
		'label'        => 'Câu hỏi thường gặp của trường',
		'name'         => 'school_faq',
		'type'         => 'repeater',
		'parent'       => 'group_school_details',
		'instructions' => 'Thêm các câu hỏi & giải đáp đặc thù về tuyển sinh liên thông riêng của trường này. Nếu để trống, hệ thống sẽ tự động hiển thị bộ câu hỏi chuẩn hóa theo thông tin của trường.',
		'layout'       => 'row',
		'button_label' => 'Thêm câu hỏi',
		'sub_fields'   => [
			[
				'key'   => 'field_school_faq_question',
				'label' => 'Câu hỏi',
				'name'  => 'question',
				'type'  => 'text',
			],
			[
				'key'   => 'field_school_faq_answer',
				'label' => 'Câu trả lời',
				'name'  => 'answer',
				'type'  => 'textarea',
				'rows'  => 3,
			],
		],
	] );
}

/**
 * Ensure School Admission Forms Tab and Fields are registered directly to group_school_details.
 */
add_action( 'acf/init', 'ltdh_register_school_admission_forms_acf_fields', 20 );
function ltdh_register_school_admission_forms_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field' ) ) {
		return;
	}

	// Register Tab
	acf_add_local_field( [
		'key'       => 'field_tab_school_admission_forms',
		'label'     => 'Phiếu tuyển sinh',
		'name'      => '',
		'type'      => 'tab',
		'parent'    => 'group_school_details',
		'placement' => 'top',
		'endpoint'  => 0,
	] );

	// Register Default Admission Form Field
	acf_add_local_field( [
		'key'           => 'field_school_default_admission_form',
		'label'         => 'Mẫu phiếu tuyển sinh mặc định của trường',
		'name'          => 'default_admission_form',
		'type'          => 'file',
		'parent'        => 'group_school_details',
		'instructions'  => 'Mẫu phiếu đăng ký xét tuyển dùng chung cho tất cả các hệ đào tạo và ngành học của trường nếu không có mẫu riêng. Nếu trường chỉ dùng đúng 1 biểu mẫu duy nhất cho mọi hệ, hãy tải lên tại đây.',
		'return_format' => 'array',
		'library'       => 'all',
	] );

	// Register Forms by Training Type Repeater
	acf_add_local_field( [
		'key'          => 'field_school_admission_forms_by_type',
		'label'        => 'Mẫu phiếu tuyển sinh theo từng Hệ đào tạo',
		'name'         => 'admission_forms_by_type',
		'type'         => 'repeater',
		'parent'       => 'group_school_details',
		'instructions' => 'Dành cho trường hợp mỗi Hệ đào tạo (Đại học từ xa, Liên thông chính quy, Văn bằng 2...) có mẫu phiếu riêng. Các chương trình thuộc hệ tương ứng sẽ tự động dùng mẫu này.',
		'layout'       => 'table',
		'button_label' => 'Thêm phiếu theo hệ',
		'sub_fields'   => [
			[
				'key'           => 'field_school_form_training_type',
				'label'         => 'Hệ đào tạo',
				'name'          => 'training_type',
				'type'          => 'taxonomy',
				'taxonomy'      => 'training_type',
				'field_type'    => 'select',
				'allow_null'    => 0,
				'add_term'      => 0,
				'save_terms'    => 0,
				'load_terms'    => 0,
				'return_format' => 'id',
			],
			[
				'key'           => 'field_school_form_file',
				'label'         => 'File mẫu phiếu (PDF/DOCX)',
				'name'          => 'file',
				'type'          => 'file',
				'return_format' => 'array',
				'library'       => 'all',
			],
			[
				'key'         => 'field_school_form_notes',
				'label'       => 'Ghi chú / Tên hiển thị (Tùy chọn)',
				'name'        => 'notes',
				'type'        => 'text',
				'placeholder' => 'Ví dụ: Mẫu phiếu ĐH Từ xa 2026',
			],
		],
	] );
}

/**
 * Ensure Major FAQ Tab and Repeater are registered directly to group_major_details.
 */
add_action( 'acf/init', 'ltdh_register_major_faq_acf_fields', 20 );
function ltdh_register_major_faq_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field' ) ) {
		return;
	}

	// Register Tab
	acf_add_local_field( [
		'key'       => 'field_tab_major_faq',
		'label'     => 'Hỏi đáp (FAQ)',
		'name'      => '',
		'type'      => 'tab',
		'parent'    => 'group_major_details',
		'placement' => 'top',
		'endpoint'  => 0,
	] );

	// Register Repeater
	acf_add_local_field( [
		'key'          => 'field_major_faq',
		'label'        => 'Câu hỏi thường gặp về ngành',
		'name'         => 'major_faq',
		'type'         => 'repeater',
		'parent'       => 'group_major_details',
		'instructions' => 'Thêm các câu hỏi & giải đáp chuyên sâu về ngành này. Nếu để trống, hệ thống sẽ tự động hiển thị cẩm nang định hướng và so sánh chuẩn hóa cho ngành.',
		'layout'       => 'row',
		'button_label' => '+ Thêm câu hỏi',
		'sub_fields'   => [
			[
				'key'   => 'field_major_faq_question',
				'label' => 'Câu hỏi',
				'name'  => 'question',
				'type'  => 'text',
			],
			[
				'key'   => 'field_major_faq_answer',
				'label' => 'Câu trả lời',
				'name'  => 'answer',
				'type'  => 'textarea',
				'rows'  => 3,
			],
		],
	] );
}

/**
 * Ensure Major Overview WYSIWYG Field is registered directly to group_major_details.
 */
add_action( 'acf/init', 'ltdh_register_major_overview_acf_field', 20 );
function ltdh_register_major_overview_acf_field() {
	if ( ! function_exists( 'acf_add_local_field' ) ) {
		return;
	}

	acf_add_local_field( [
		'key'          => 'field_major_overview',
		'label'        => 'Nội dung Tổng quan về ngành (Chi tiết chuẩn SEO)',
		'name'         => 'major_overview',
		'type'         => 'wysiwyg',
		'parent'       => 'group_major_details',
		'instructions' => 'Nhập nội dung bài viết Tổng quan chi tiết về ngành học (Hỗ trợ định dạng Tiêu đề H2, H3, Bảng biểu, danh sách, shortcode [ez-toc] mục lục tự động). Nội dung này hiển thị trực tiếp tại mục "Tổng quan về ngành" ở trang chi tiết.',
		'required'     => 0,
		'tabs'         => 'all',
		'toolbar'      => 'full',
		'media_upload' => 1,
		'delay'        => 0,
	] );
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

add_filter( 'acf/load_field/key=field_program_admission_fee', 'ltdh_override_field_program_admission_fee_label' );
function ltdh_override_field_program_admission_fee_label( $field ) {
	$field['label'] = 'Lệ phí xét tuyển (trên 1 hồ sơ)';
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

// Ensure Program contact info field is properly labeled
add_filter( 'acf/load_field/key=field_program_contact_info', 'ltdh_override_field_program_contact_info_label' );
function ltdh_override_field_program_contact_info_label( $field ) {
	$field['label'] = 'Thông tin liên hệ & Tiếp nhận hồ sơ';
	return $field;
}

// Provide intelligent defaults for Target Admissions by School in Major Admin if empty
add_filter( 'acf/load_value/name=major_school_admission_targets', 'ltdh_default_major_target_admissions', 10, 3 );
function ltdh_default_major_target_admissions( $value, $post_id, $field ) {
	if ( ! empty( $value ) && is_array( $value ) ) {
		return $value;
	}
	if ( 'major' !== get_post_type( $post_id ) ) {
		return $value;
	}
	$major_slug = get_post_field( 'post_name', $post_id );
	$title      = get_the_title( $post_id );

	// Mặc định nạp dữ liệu Đề án tuyển sinh UTC cho ngành Công nghệ thông tin
	if ( ( $major_slug && ( str_contains( $major_slug, 'thong-tin' ) || str_contains( $major_slug, 'cntt' ) ) ) || str_contains( mb_strtolower( (string) $title, 'UTF-8' ), 'thông tin' ) ) {
		$utc_school = get_page_by_path( 'truong-dai-hoc-giao-thong-van-tai', OBJECT, 'school' );
		if ( ! $utc_school ) {
			$utc_schools = get_posts( [
				'post_type'   => 'school',
				'title'       => 'Trường Đại học Giao thông Vận tải',
				'numberposts' => 1,
			] );
			$utc_school = $utc_schools[0] ?? null;
		}
		$school_id = $utc_school ? $utc_school->ID : 0;

		return [
			[
				'field_target_school'           => $school_id,
				'field_target_regulation_title' => 'Theo Đề án tuyển sinh Trường Đại học Giao thông Vận tải (UTC)',
				'field_target_proper_majors'    => "Khoa học máy tính\nCông nghệ thông tin\nỨng dụng phần mềm\nCông nghệ kỹ thuật máy tính\nLập trình máy tính",
				'field_target_near_majors'      => "Kỹ thuật sửa chữa lắp ráp máy tính\nThiết kế mạch điện tử trên máy tính\nTruyền thông và mạng máy tính\nCông nghệ kỹ thuật máy tính\nĐiện tử máy tính\nĐồ họa đa phương tiện\nTin học văn phòng\nTin học viễn thông ứng dụng\nTin học ứng dụng\nXử lý dữ liệu\nQuản trị cơ sở dữ liệu\nQuản trị mạng máy tính\nvà các ngành tương đương",
				'field_target_bridge_note'      => "Thí sinh các ngành này phải học bổ sung 03 học phần trong quá trình đào tạo theo yêu cầu của chương trình đào tạo.",
				'field_target_bridge_count'     => 3,
				'school'           => $school_id,
				'regulation_title' => 'Theo Đề án tuyển sinh Trường Đại học Giao thông Vận tải (UTC)',
				'proper_majors'    => "Khoa học máy tính\nCông nghệ thông tin\nỨng dụng phần mềm\nCông nghệ kỹ thuật máy tính\nLập trình máy tính",
				'near_majors'      => "Kỹ thuật sửa chữa lắp ráp máy tính\nThiết kế mạch điện tử trên máy tính\nTruyền thông và mạng máy tính\nCông nghệ kỹ thuật máy tính\nĐiện tử máy tính\nĐồ họa đa phương tiện\nTin học văn phòng\nTin học viễn thông ứng dụng\nTin học ứng dụng\nXử lý dữ liệu\nQuản trị cơ sở dữ liệu\nQuản trị mạng máy tính\nvà các ngành tương đương",
				'bridge_note'      => "Thí sinh các ngành này phải học bổ sung 03 học phần trong quá trình đào tạo theo yêu cầu của chương trình đào tạo.",
				'bridge_count'     => 3,
			]
		];
	}
	return $value;
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
		$clean_content = "<p>Ngành <strong>Công nghệ thông tin (CNTT)</strong> là ngành học đào tạo chuyên sâu về việc thiết kế, xây dựng, vận hành và tối ưu hóa hệ thống phần mềm, cơ sở dữ liệu và hạ tầng mạng máy tính trong kỷ nguyên chuyển đổi số.</p>\n\n<p>Chương trình <strong>Liên thông Đại học ngành Công nghệ thông tin</strong> được thiết kế linh hoạt, tạo điều kiện thuận lợi nhất cho người đã tốt nghiệp Trung cấp hoặc Cao đẳng nhanh chóng hoàn thiện văn bằng Cử nhân / Kỹ sư chuẩn Bộ GD&ĐT, nâng bậc lương và mở rộng lộ trình thăng tiến sự nghiệp.</p>";
		wp_update_post( [
			'ID'           => 1677,
			'post_content' => $clean_content,
		] );
	}
}

// Automatically correct legacy exam text in VHVL/Tu-xa programs to pure admission by records (one-time migration)
add_action( 'init', 'ltdh_cleanup_vhvl_admission_requirements' );
function ltdh_cleanup_vhvl_admission_requirements() {
	if ( get_transient( 'ltdh_vhvl_admission_cleanup_done_v2' ) ) {
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
		set_transient( 'ltdh_vhvl_admission_cleanup_done_v2', 1, DAY_IN_SECONDS * 30 );
		return;
	}

	foreach ( $programs as $pid ) {
		$slug = get_post_field( 'post_name', $pid );
		if ( str_contains( $slug, 'vua-hoc-vua-lam' ) || str_contains( $slug, 'tu-xa' ) ) {
			$req = get_post_meta( $pid, 'admission_requirements', true );
			if ( ! empty( $req ) && ( str_contains( $req, 'Thi tuyển 3 môn' ) || str_contains( $req, 'Phương thức 2: Thi tuyển' ) ) ) {
				$clean_req = '<p>Chương trình tuyển sinh Liên thông Đại học hệ Vừa học vừa làm (VHVL) áp dụng phương thức <strong>Xét tuyển hồ sơ văn bằng</strong> (không phải thi tuyển):</p><ul><li><strong>Đối tượng tuyển sinh:</strong> Người đã tốt nghiệp Cao đẳng hoặc Trung cấp đúng ngành/ngành gần theo quy định tuyển sinh liên thông.</li><li><strong>Tiêu chí xét tuyển:</strong> Xét duyệt dựa trên kết quả học tập ghi trên văn bằng và bảng điểm bậc tốt nghiệp trước đó. Điểm trung bình tích lũy toàn khóa đạt yêu cầu theo Quy chế tuyển sinh của nhà trường.</li><li><strong>Ngưỡng đảm bảo chất lượng:</strong> Thí sinh hoàn thiện đầy đủ hồ sơ hợp lệ và nộp đúng thời hạn quy định của các đợt tuyển sinh.</li></ul>';
				update_post_meta( $pid, 'admission_requirements', $clean_req );
			}
		}
	}
	set_transient( 'ltdh_vhvl_admission_cleanup_done_v2', 1, DAY_IN_SECONDS * 30 );
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
	if ( get_transient( 'ltdh_program_meta_keys_synced_v2' ) ) {
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
			if ( ! get_post_meta( $pid, '_admission_fee', true ) ) {
				update_post_meta( $pid, '_admission_fee', 'field_program_admission_fee' );
			}
			if ( ! get_post_meta( $pid, '_contact_info', true ) ) {
				update_post_meta( $pid, '_contact_info', 'field_program_contact_info' );
			}
		}
	}
	set_transient( 'ltdh_program_meta_keys_synced_v3', 1, DAY_IN_SECONDS * 30 );
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
 * Check if a given field key or name is a repeater or complex ACF field.
 */
function ltdh_is_repeater_or_complex_field( $key_or_name ) {
	static $repeaters = [
		'field_program_admission_batches',
		'admission_batches',
		'field_program_exemption_items',
		'exemption_items',
		'field_program_faq',
		'faq',
		'field_school_faq',
		'school_faq',
		'field_major_faq',
		'major_faq',
		'field_major_entry_roadmaps',
		'major_entry_roadmaps',
		'field_major_specializations',
		'major_specializations',
		'field_exemption_cert_scores',
		'cert_scores',
		'training_variants',
		'field_program_training_variants',
		'field_variant_admission_batches',
	];
	if ( in_array( $key_or_name, $repeaters, true ) ) {
		return true;
	}
	if ( function_exists( 'acf_get_field' ) ) {
		$f = acf_get_field( $key_or_name );
		if ( $f && in_array( $f['type'] ?? '', [ 'repeater', 'flexible_content', 'clone', 'group' ], true ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Automatically handle and repair repeater values if stored as raw array instead of integer count in DB.
 * Runs at priority 5 (before ACF's repeater load_value at priority 10).
 */
add_filter( 'acf/load_value/type=repeater', 'ltdh_fix_repeater_load_value', 5, 3 );
function ltdh_fix_repeater_load_value( $value, $post_id, $field ) {
	if ( ! is_array( $value ) || empty( $value ) ) {
		return $value;
	}

	$field_name = $field['name'] ?? '';
	$first_subfield = ! empty( $field['sub_fields'][0]['name'] ) ? $field['sub_fields'][0]['name'] : '';
	$has_flat_meta = false;
	if ( $field_name && $first_subfield ) {
		$flat_check = get_post_meta( $post_id, "{$field_name}_0_{$first_subfield}", true );
		if ( '' !== $flat_check && false !== $flat_check ) {
			$has_flat_meta = true;
		}
	}

	if ( $has_flat_meta ) {
		return count( $value );
	}

	// Data is stored directly inside array rows: unpack to flat postmeta
	$count = 0;
	$sub_name_to_key = [];
	if ( ! empty( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ) {
		foreach ( $field['sub_fields'] as $sf ) {
			if ( ! empty( $sf['name'] ) && ! empty( $sf['key'] ) ) {
				$sub_name_to_key[ $sf['name'] ] = $sf['key'];
			}
		}
	}

	foreach ( array_values( $value ) as $i => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$count++;
		foreach ( $row as $k => $v ) {
			$sub_name = $k;
			$sub_key = $sub_name_to_key[ $k ] ?? '';
			if ( str_starts_with( $k, 'field_' ) ) {
				$sub_key = $k;
				$sub_name = array_search( $k, $sub_name_to_key, true ) ?: $k;
			}
			if ( $sub_name && $field_name ) {
				update_post_meta( $post_id, "{$field_name}_{$i}_{$sub_name}", $v );
				if ( $sub_key ) {
					update_post_meta( $post_id, "_{$field_name}_{$i}_{$sub_name}", $sub_key );
				}
			}
		}
	}

	if ( $count > 0 && $field_name ) {
		update_post_meta( $post_id, $field_name, $count );
		if ( ! empty( $field['key'] ) ) {
			update_post_meta( $post_id, '_' . $field_name, $field['key'] );
		}
		return $count;
	}

	return count( $value );
}

/**
 * Comprehensive fail-safe save handler for Program, School, and Major custom fields.
 * Handles $_POST['acf'], top-level $_POST['field_*'], and top-level $_POST['meta_key'].
 * Explicitly skips repeaters/complex fields so ACF PRO's native handling is never corrupted.
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

	// 1. Process standard $_POST['acf'] if populated (skip repeaters/complex fields so ACF's native save is preserved)
	if ( ! empty( $_POST['acf'] ) && is_array( $_POST['acf'] ) ) {
		foreach ( $_POST['acf'] as $key => $value ) {
			if ( ltdh_is_repeater_or_complex_field( $key ) ) {
				continue;
			}
			if ( str_starts_with( $key, 'field_' ) ) {
				$field = function_exists( 'acf_get_field' ) ? acf_get_field( $key ) : null;
				$meta_name = ( $field && ! empty( $field['name'] ) ) ? $field['name'] : ( $field_map[ $key ] ?? '' );
				if ( $meta_name ) {
					if ( ltdh_is_repeater_or_complex_field( $meta_name ) ) {
						continue;
					}
					if ( ! is_array( $value ) ) {
						if ( in_array( $meta_name, [ 'verification_status', 'verification_source', 'verification_source_title', 'last_verified_at', 'verification_notes' ], true ) ) {
							$value = ltdh_sanitize_verification_meta( $meta_name, $value );
						}
						update_post_meta( $post_id, $meta_name, $value );
					}
					update_post_meta( $post_id, '_' . $meta_name, $key );
				}
			} else {
				if ( ! ltdh_is_repeater_or_complex_field( $key ) && ! is_array( $value ) ) {
					if ( in_array( $key, [ 'verification_status', 'verification_source', 'verification_source_title', 'last_verified_at', 'verification_notes' ], true ) ) {
						$value = ltdh_sanitize_verification_meta( $key, $value );
					}
					update_post_meta( $post_id, $key, $value );
				}
			}
		}
	}

	// 2. Process flat POST variables if submitted without acf[...] container
	foreach ( $field_map as $field_key => $meta_name ) {
		if ( ltdh_is_repeater_or_complex_field( $field_key ) || ltdh_is_repeater_or_complex_field( $meta_name ) ) {
			continue;
		}
		if ( isset( $_POST[ $field_key ] ) ) {
			$val = $_POST[ $field_key ];
			if ( ! is_array( $val ) ) {
				if ( in_array( $meta_name, [ 'verification_status', 'verification_source', 'verification_source_title', 'last_verified_at', 'verification_notes' ], true ) ) {
					$val = ltdh_sanitize_verification_meta( $meta_name, $val );
				}
				update_post_meta( $post_id, $meta_name, $val );
			}
			update_post_meta( $post_id, '_' . $meta_name, $field_key );
		} elseif ( isset( $_POST[ $meta_name ] ) ) {
			$val = $_POST[ $meta_name ];
			if ( ! is_array( $val ) ) {
				if ( in_array( $meta_name, [ 'verification_status', 'verification_source', 'verification_source_title', 'last_verified_at', 'verification_notes' ], true ) ) {
					$val = ltdh_sanitize_verification_meta( $meta_name, $val );
				}
				update_post_meta( $post_id, $meta_name, $val );
			}
			update_post_meta( $post_id, '_' . $meta_name, $field_key );
		}
	}

	// 3. Auto-promote verification_status to 'verified' if verification_source is present
	// and status was left as default 'unverified' (unless explicitly set to 'partially_verified').
	if ( 'program' === $post->post_type ) {
		$source = get_post_meta( $post_id, 'verification_source', true );
		$status = get_post_meta( $post_id, 'verification_status', true );
		if ( ! empty( $source ) && ( empty( $status ) || 'unverified' === $status ) ) {
			update_post_meta( $post_id, 'verification_status', 'verified' );
		}
	}
}

// Auto-promote verification status on ACF save if verification source URL is provided
add_action( 'acf/save_post', 'ltdh_acf_auto_promote_verification_status', 20 );
function ltdh_acf_auto_promote_verification_status( $post_id ) {
	if ( ! is_numeric( $post_id ) || 'program' !== get_post_type( $post_id ) ) {
		return;
	}
	$source = get_post_meta( $post_id, 'verification_source', true );
	$status = get_post_meta( $post_id, 'verification_status', true );
	if ( ! empty( $source ) && ( empty( $status ) || 'unverified' === $status ) ) {
		update_post_meta( $post_id, 'verification_status', 'verified' );
	}
}

/**
 * Synchronize major_overview ACF WYSIWYG field to wp_posts.post_content
 * and ensure both sources remain 100% in sync for SEO, feed, search, and frontend rendering.
 */
add_action( 'acf/save_post', 'ltdh_sync_major_overview_content', 30 );
function ltdh_sync_major_overview_content( $post_id ) {
	if ( ! is_numeric( $post_id ) || (int) $post_id <= 0 ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'major' ) {
		return;
	}

	$overview = get_field( 'major_overview', $post_id, false );
	if ( false !== $overview && null !== $overview && '' !== trim( (string) $overview ) ) {
		remove_action( 'acf/save_post', 'ltdh_sync_major_overview_content', 30 );
		wp_update_post( [
			'ID'           => (int) $post_id,
			'post_content' => $overview,
		] );
		add_action( 'acf/save_post', 'ltdh_sync_major_overview_content', 30 );
	}
}

/**
 * Fallback sync: When a major post is saved outside ACF,
 * preserve existing content bidirectionally so content is never lost.
 */
add_action( 'save_post_major', 'ltdh_fallback_sync_major_content', 35, 2 );
function ltdh_fallback_sync_major_content( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$major_overview = get_post_meta( $post_id, 'major_overview', true );
	if ( empty( $major_overview ) && ! empty( $post->post_content ) ) {
		update_post_meta( $post_id, 'major_overview', $post->post_content );
		update_post_meta( $post_id, '_major_overview', 'field_major_overview' );
	}
}

/**
 * Sanitize and validate verification metadata.
 *
 * @param string $meta_name Field or meta name.
 * @param mixed  $value     Input value.
 * @return mixed Sanitized value.
 */
function ltdh_sanitize_verification_meta( $meta_name, $value ) {
	switch ( $meta_name ) {
		case 'verification_status':
			$val = is_string( $value ) ? trim( $value ) : '';
			return in_array( $val, [ 'verified', 'partially_verified', 'unverified' ], true ) ? $val : 'unverified';

		case 'verification_source':
			if ( empty( $value ) || ! is_string( $value ) ) {
				return '';
			}
			$url = esc_url_raw( trim( $value ) );
			return ( filter_var( $url, FILTER_VALIDATE_URL ) && preg_match( '#^https?://#i', $url ) ) ? $url : '';

		case 'verification_source_title':
			return is_string( $value ) ? sanitize_text_field( trim( $value ) ) : '';

		case 'last_verified_at':
			if ( empty( $value ) || ! is_string( $value ) ) {
				return '';
			}
			$val = trim( $value );
			// Check standard date formats (Y-m-d, d/m/Y, Ymd)
			if ( preg_match( '#^(\d{4})-(\d{1,2})-(\d{1,2})$#', $val ) ||
			     preg_match( '#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $val ) ||
			     preg_match( '#^(\d{4})(\d{2})(\d{2})$#', $val ) ) {
				return sanitize_text_field( $val );
			}
			$ts = strtotime( $val );
			return ( false !== $ts && $ts > 0 ) ? date( 'Y-m-d', $ts ) : '';

		case 'verification_notes':
			return is_string( $value ) ? sanitize_textarea_field( trim( $value ) ) : '';

		default:
			return $value;
	}
}

// Ensure ACF field updates pass through sanitization
add_filter( 'acf/update_value/name=verification_status', function( $value ) {
	return ltdh_sanitize_verification_meta( 'verification_status', $value );
} );
add_filter( 'acf/update_value/name=verification_source', function( $value ) {
	return ltdh_sanitize_verification_meta( 'verification_source', $value );
} );
add_filter( 'acf/update_value/name=verification_source_title', function( $value ) {
	return ltdh_sanitize_verification_meta( 'verification_source_title', $value );
} );
add_filter( 'acf/update_value/name=last_verified_at', function( $value ) {
	return ltdh_sanitize_verification_meta( 'last_verified_at', $value );
} );
add_filter( 'acf/update_value/name=verification_notes', function( $value ) {
	return ltdh_sanitize_verification_meta( 'verification_notes', $value );
} );

// ----------------------------------------------------
// Admin UI: Verification Workflow & Quick Actions
// ----------------------------------------------------

add_action( 'admin_footer-post.php', 'ltdh_admin_verification_quick_actions' );
add_action( 'admin_footer-post-new.php', 'ltdh_admin_verification_quick_actions' );
function ltdh_admin_verification_quick_actions() {
	global $post;
	if ( ! $post || 'program' !== $post->post_type ) {
		return;
	}
	$today_iso = current_time( 'Y-m-d' );
	$today_vn  = current_time( 'd/m/Y' );
	?>
	<script>
	jQuery(document).ready(function($) {
		// Wait for ACF fields to be rendered
		setTimeout(function() {
			var $verifTab = $('.acf-tab-wrap a[data-key="field_tab_prog_verification"]');
			var $statusField = $('[data-name="verification_status"], [data-key="field_program_verification_status"]');
			if (!$statusField.length) return;

			// Inject Quick Action toolbar above the verification status field
			if (!$('#ltdh-verif-quick-bar').length) {
				var barHtml = '<div id="ltdh-verif-quick-bar" style="margin-bottom:15px;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #10b981;border-radius:6px;display:flex;align-items:center;flex-wrap:wrap;gap:8px;">' +
					'<span style="font-weight:600;font-size:12px;color:#1e293b;">⚡ Thao tác nhanh:</span>' +
					'<button type="button" id="ltdh-act-verify-today" class="button button-secondary" style="font-size:12px;display:inline-flex;align-items:center;gap:4px;border-color:#10b981;color:#047857;">' +
						'<span>✓ Xác nhận đã kiểm tra hôm nay</span>' +
					'</button>' +
					'<button type="button" id="ltdh-act-clear-verif" class="button button-link-delete" style="font-size:12px;color:#b91c1c;margin-left:auto;">' +
						'Xóa ngày xác minh' +
					'</button>' +
					'<div id="ltdh-verif-quick-msg" style="width:100%;font-size:11px;color:#047857;display:none;margin-top:4px;"></div>' +
				'</div>';
				$statusField.before(barHtml);
			}

			// Handle "Xác nhận đã kiểm tra hôm nay"
			$('#ltdh-act-verify-today').on('click', function(e) {
				e.preventDefault();
				// 1. Set status to 'verified'
				var $statusSelect = $('select[name="acf[field_program_verification_status]"], select[name*="verification_status"]');
				if ($statusSelect.length) {
					$statusSelect.val('verified').trigger('change');
				}

				// 2. Set date to today
				var todayIso = <?php echo json_encode( $today_iso ); ?>;
				var todayVn  = <?php echo json_encode( $today_vn ); ?>;

				var $dateHidden = $('input[type="hidden"][name="acf[field_program_last_verified_at]"], input[type="hidden"][name*="last_verified_at"]');
				var $dateField  = $('[data-name="last_verified_at"], [data-key="field_program_last_verified_at"]');
				var $dateVisible = $dateField.find('.input-alt, input.hasDatepicker, input[type="text"]');

				if ($dateHidden.length) {
					$dateHidden.val(todayIso).trigger('change');
				}
				if ($dateVisible.length) {
					$dateVisible.val(todayVn).trigger('change');
				}

				$('#ltdh-verif-quick-msg')
					.html('✓ Đã chọn "Đã xác minh" và điền ngày hôm nay (' + todayVn + '). Hãy nhấn <strong>Cập nhật</strong> để lưu.')
					.css('color', '#047857')
					.show();
			});

			// Handle "Xóa ngày xác minh"
			$('#ltdh-act-clear-verif').on('click', function(e) {
				e.preventDefault();
				var $dateHidden = $('input[type="hidden"][name="acf[field_program_last_verified_at]"], input[type="hidden"][name*="last_verified_at"]');
				var $dateField  = $('[data-name="last_verified_at"], [data-key="field_program_last_verified_at"]');
				var $dateVisible = $dateField.find('.input-alt, input.hasDatepicker, input[type="text"]');

				if ($dateHidden.length) {
					$dateHidden.val('').trigger('change');
				}
				if ($dateVisible.length) {
					$dateVisible.val('').trigger('change');
				}

				$('#ltdh-verif-quick-msg')
					.html('Đã xóa ngày xác minh. Nhấn <strong>Cập nhật</strong> để lưu.')
					.css('color', '#64748b')
					.show();
			});

			// Auto-sync status to "verified" when a source URL is typed or pasted
			$(document).on('input paste change', '[data-name="verification_source"] input, [data-key="field_program_verification_source"] input, input[name*="verification_source"]', function() {
				var val = $.trim($(this).val());
				var $statusSelect = $('select[name="acf[field_program_verification_status]"], select[name*="verification_status"]');
				if (val.length > 0 && (/^https?:\/\//i.test(val) || val.indexOf('.') !== -1)) {
					if ($statusSelect.length && ($statusSelect.val() === 'unverified' || !$statusSelect.val())) {
						$statusSelect.val('verified').trigger('change');
						$('#ltdh-verif-quick-msg')
							.html('✓ Đã tự động chọn "Đã xác minh" khi nhập nguồn xác minh. Nhấn <strong>Cập nhật</strong> để lưu ra ngoài website.')
							.css('color', '#047857')
							.show();
					}
				}
			});

			// On load check: if source exists but status is unverified, auto-select verified
			var $sourceInput = $('[data-name="verification_source"] input, [data-key="field_program_verification_source"] input, input[name*="verification_source"]');
			var $statusSelect = $('select[name="acf[field_program_verification_status]"], select[name*="verification_status"]');
			if ($sourceInput.length && $statusSelect.length) {
				var curUrl = $.trim($sourceInput.val());
				if (curUrl.length > 0 && $statusSelect.val() === 'unverified') {
					$statusSelect.val('verified').trigger('change');
					$('#ltdh-verif-quick-msg')
						.html('✓ Đã tự động chuyển sang "Đã xác minh" do có nguồn URL. Nhấn <strong>Cập nhật</strong> để lưu.')
						.css('color', '#047857')
						.show();
				}
			}

			// -------------------------------------------------------------------
			// 2-Way Sync & Ràng buộc tự động giữa Tab 1 (Trạng thái tuyển sinh) & Tab 2 (Các đợt tuyển sinh)
			// -------------------------------------------------------------------
			function ltdhEvaluateAdmissionBatches(triggerSource) {
				var $batchWrapper = $('[data-name="admission_batches"], [data-key="field_program_admission_batches"]');
				if (!$batchWrapper.length) return;

				var $rows = $batchWrapper.find('tbody tr.acf-row:not(.acf-clone)');
				var total = $rows.length;
				if (total === 0) return;

				var countOpen = 0;
				var countUpcoming = 0;
				var countClosed = 0;

				$rows.each(function() {
					var val = $(this).find('[data-name="batch_status"] select, select[name*="batch_status"]').val();
					if (val === 'dang-nhan') {
						countOpen++;
					} else if (val === 'sap-mo') {
						countUpcoming++;
					} else if (val === 'da-dong') {
						countClosed++;
					}
				});

				var $statusField = $('[data-name="admission_status"], [data-key="field_program_admission_status"]');
				var $statusSelect = $statusField.find('select');
				if (!$statusSelect.length) return;
				var curStatus = $statusSelect.val();

				var $notice = $('#ltdh-batch-constraint-notice');
				if (!$notice.length) {
					$notice = $('<div id="ltdh-batch-constraint-notice" style="margin-top:8px;padding:8px 12px;border-radius:6px;font-size:12px;line-height:1.5;display:none;"></div>');
					$statusField.find('.acf-input').append($notice);
				}

				if (countClosed === total) {
					// TẤT CẢ CÁC ĐỢT ĐÃ ĐÓNG
					if (curStatus !== 'tam-ngung') {
						$statusSelect.val('tam-ngung').trigger('change');
						$notice.html('⚡ <strong>Ràng buộc tự động:</strong> Tất cả ' + total + ' đợt tuyển sinh ở Tab "Các đợt tuyển sinh" đều đã đóng. Hệ thống đã tự động chuyển sang <strong>"Tạm ngưng tuyển sinh (Đã hết chỉ tiêu)"</strong>.')
							   .css({'background': '#fef2f2', 'color': '#991b1b', 'border': '1px solid #fecaca', 'display': 'block'});
					} else {
						$notice.html('ℹ️ Tất cả ' + total + ' đợt tuyển sinh đều đã đóng. Trạng thái chương trình đang là <strong>Tạm ngưng tuyển sinh</strong>.')
							   .css({'background': '#f8fafc', 'color': '#475569', 'border': '1px solid #e2e8f0', 'display': 'block'});
					}
				} else if (countOpen > 0) {
					// CÓ ĐỢT ĐANG NHẬN HỒ SƠ
					if (curStatus === 'tam-ngung') {
						$statusSelect.val('tuyen-sinh').trigger('change');
						$notice.html('⚡ <strong>Ràng buộc tự động:</strong> Có ' + countOpen + ' đợt đang mở nhận hồ sơ. Hệ thống đã tự động chuyển sang <strong>"Đang tuyển sinh (Còn chỉ tiêu)"</strong>.')
							   .css({'background': '#ecfdf5', 'color': '#065f46', 'border': '1px solid #a7f3d0', 'display': 'block'});
					} else {
						$notice.hide();
					}
				} else if (countUpcoming > 0 && countOpen === 0) {
					// CÓ ĐỢT SẮP MỞ
					if (curStatus === 'tam-ngung') {
						$statusSelect.val('sap-mo').trigger('change');
						$notice.html('⚡ <strong>Ràng buộc tự động:</strong> Có đợt sắp mở nhận hồ sơ. Hệ thống đã tự động chuyển sang <strong>"Sắp mở tuyển sinh"</strong>.')
							   .css({'background': '#eff6ff', 'color': '#1e40af', 'border': '1px solid #bfdbfe', 'display': 'block'});
					} else {
						$notice.hide();
					}
				}
			}

			// Lắng nghe khi thay đổi trạng thái đợt
			$(document).on('change', '[data-name="admission_batches"] [data-name="batch_status"] select, [data-key="field_program_admission_batches"] select[name*="batch_status"]', function() {
				ltdhEvaluateAdmissionBatches('batch_status_changed');
			});

			// Lắng nghe khi thêm/xóa hàng trong bảng đợt
			if (window.acf) {
				window.acf.addAction('remove', function($el) {
					setTimeout(function() { ltdhEvaluateAdmissionBatches('batch_row_removed'); }, 150);
				});
				window.acf.addAction('append', function($el) {
					setTimeout(function() { ltdhEvaluateAdmissionBatches('batch_row_added'); }, 150);
				});
			}

			// Lắng nghe khi người dùng thủ công đổi Trạng thái tuyển sinh ở Tab 1
			$(document).on('change', '[data-name="admission_status"] select, [data-key="field_program_admission_status"] select', function() {
				var val = $(this).val();
				var $batchWrapper = $('[data-name="admission_batches"], [data-key="field_program_admission_batches"]');
				if (!$batchWrapper.length) return;

				var $rows = $batchWrapper.find('tbody tr.acf-row:not(.acf-clone)');
				if (!$rows.length) return;

				var countOpen = 0;
				var countClosed = 0;
				$rows.each(function() {
					var st = $(this).find('[data-name="batch_status"] select, select[name*="batch_status"]').val();
					if (st === 'dang-nhan') countOpen++;
					if (st === 'da-dong') countClosed++;
				});

				var $notice = $('#ltdh-batch-constraint-notice');
				if (!$notice.length) {
					$notice = $('<div id="ltdh-batch-constraint-notice" style="margin-top:8px;padding:8px 12px;border-radius:6px;font-size:12px;line-height:1.5;display:none;"></div>');
					$(this).closest('.acf-input').append($notice);
				}

				if (val === 'tam-ngung' && countOpen > 0) {
					$notice.html('⚠️ <strong>Chú ý:</strong> Bạn đang chọn "Tạm ngưng tuyển sinh" nhưng ở Tab "Các đợt tuyển sinh" vẫn còn ' + countOpen + ' đợt đang mở. <button type="button" id="ltdh-btn-close-all-batches" class="button button-small" style="margin-left:6px;color:#b91c1c;border-color:#fca5a5;">Đóng tất cả các đợt ngay</button>')
						   .css({'background': '#fffbeb', 'color': '#92400e', 'border': '1px solid #fde68a', 'display': 'block'});
				} else if (val === 'tuyen-sinh' && countClosed === $rows.length) {
					$notice.html('⚠️ <strong>Chú ý:</strong> Bạn đang chọn "Đang tuyển sinh" nhưng toàn bộ ' + countClosed + ' đợt tuyển sinh ở Tab 2 đều đang "Đã đóng". Vui lòng mở lại ít nhất 1 đợt hoặc tạo đợt mới.')
						   .css({'background': '#fffbeb', 'color': '#92400e', 'border': '1px solid #fde68a', 'display': 'block'});
				} else {
					$notice.hide();
				}
			});

			// Xử lý nút bấm đóng tất cả các đợt ngay
			$(document).on('click', '#ltdh-btn-close-all-batches', function(e) {
				e.preventDefault();
				var $batchWrapper = $('[data-name="admission_batches"], [data-key="field_program_admission_batches"]');
				$batchWrapper.find('tbody tr.acf-row:not(.acf-clone)').each(function() {
					$(this).find('[data-name="batch_status"] select, select[name*="batch_status"]').val('da-dong').trigger('change');
				});
				$('#ltdh-batch-constraint-notice')
					.html('✓ Đã đồng bộ đóng tất cả các đợt tuyển sinh.')
					.css({'background': '#ecfdf5', 'color': '#065f46', 'border': '1px solid #a7f3d0'});
			});

			// Kiểm tra ban đầu khi load trang
			setTimeout(function() {
				ltdhEvaluateAdmissionBatches('initial_load');
			}, 600);
		}, 300);
	});
	</script>
	<?php
}

/**
 * Tự động đồng bộ ràng buộc giữa Trạng thái tuyển sinh (Tab 1) và Các đợt tuyển sinh (Tab 2) khi lưu bài.
 */
add_action( 'acf/save_post', 'ltdh_sync_program_admission_status_batches', 20 );
function ltdh_sync_program_admission_status_batches( $post_id ) {
	if ( 'program' !== get_post_type( $post_id ) ) {
		return;
	}

	$batches = get_field( 'admission_batches', $post_id );
	if ( ! is_array( $batches ) || empty( $batches ) ) {
		return;
	}

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

	$current_status = get_post_meta( $post_id, 'admission_status', true ) ?: 'tuyen-sinh';

	if ( $all_closed ) {
		if ( 'tam-ngung' !== $current_status ) {
			update_post_meta( $post_id, 'admission_status', 'tam-ngung' );
		}
		$cur_period = get_post_meta( $post_id, 'enrollment_period', true );
		if ( empty( $cur_period ) || in_array( trim( $cur_period ), [ 'Đang nhận hồ sơ', 'Tuyển sinh theo đợt' ], true ) ) {
			update_post_meta( $post_id, 'enrollment_period', 'Đã hết chỉ tiêu tuyển sinh' );
		}
	} elseif ( $has_open && 'tam-ngung' === $current_status ) {
		update_post_meta( $post_id, 'admission_status', 'tuyen-sinh' );
	} elseif ( ! $has_open && $has_upcoming && 'tam-ngung' === $current_status ) {
		update_post_meta( $post_id, 'admission_status', 'sap-mo' );
	}
}

// ----------------------------------------------------
// Admin Columns & Filter for Program CPT
// ----------------------------------------------------

// Admin Columns & Filter for Program CPT
// ----------------------------------------------------

add_filter( 'manage_edit-program_columns', 'ltdh_add_program_admin_columns', 20 );
function ltdh_add_program_admin_columns( $columns ) {
	$new_columns = [];
	$author_label = isset( $columns['author'] ) ? $columns['author'] : __( 'Author' );

	foreach ( $columns as $key => $title ) {
		if ( 'date' === $key ) {
			$new_columns['author'] = $author_label;
		}

		if ( 'author' === $key || 'taxonomy-campus' === $key || 'program_tuition' === $key || 'taxonomy-training_type' === $key ) {
			continue;
		}

		$new_columns[ $key ] = $title;
		if ( 'title' === $key ) {
			$new_columns['program_school']        = 'Trường đối tác';
			$new_columns['program_major']         = 'Chuyên ngành';
			$new_columns['program_training_type'] = 'Hình thức học';
			$new_columns['verification_status']   = 'Xác minh';
			$new_columns['data_completeness']     = 'Chuẩn P0';
		}
	}

	if ( ! isset( $new_columns['author'] ) ) {
		$new_columns['author'] = $author_label;
	}

	unset( $new_columns['program_tuition'], $new_columns['taxonomy-campus'], $new_columns['taxonomy-training_type'] );

	return $new_columns;
}

add_filter( 'manage_edit-program_sortable_columns', 'ltdh_program_admin_sortable_columns' );
function ltdh_program_admin_sortable_columns( $columns ) {
	$columns['author'] = 'author';
	return $columns;
}

add_action( 'manage_program_posts_custom_column', 'ltdh_render_program_admin_columns', 10, 2 );
function ltdh_render_program_admin_columns( $column, $post_id ) {
	if ( 'program_school' === $column ) {
		$school_id = (int) ( get_field( 'school_relationship', $post_id ) ?: get_post_meta( $post_id, 'school_relationship', true ) );
		if ( is_array( $school_id ) ) {
			$school_id = ! empty( $school_id ) ? (int) $school_id[0] : 0;
		}
		if ( $school_id ) {
			$school_title = get_the_title( $school_id );
			$edit_url     = get_edit_post_link( $school_id );
			echo '<a href="' . esc_url( $edit_url ) . '" style="font-weight:600; color:#00308b; text-decoration:none;">' . esc_html( $school_title ) . '</a>';
		} else {
			echo '<span style="color:#94a3b8;">Chưa chọn trường</span>';
		}
	} elseif ( 'program_major' === $column ) {
		$major_id = (int) ( get_field( 'major_relationship', $post_id ) ?: get_post_meta( $post_id, 'major_relationship', true ) );
		if ( is_array( $major_id ) ) {
			$major_id = ! empty( $major_id ) ? (int) $major_id[0] : 0;
		}
		if ( $major_id ) {
			$major_title = get_the_title( $major_id );
			$clean_m     = preg_replace( '/^ngành\s+/iu', '', $major_title );
			$edit_url    = get_edit_post_link( $major_id );
			echo '<a href="' . esc_url( $edit_url ) . '" style="color:#334155; text-decoration:none; font-weight:500;">' . esc_html( $clean_m ) . '</a>';
		} else {
			echo '<span style="color:#94a3b8;">Chưa chọn ngành</span>';
		}
	} elseif ( 'program_training_type' === $column ) {
		$terms = wp_get_post_terms( $post_id, 'training_type' );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$slug = $t->slug;
				$name = preg_replace( '/^hệ\s+/iu', '', $t->name );
				$filter_url = add_query_arg( [
					'post_type'     => 'program',
					'training_type' => $slug,
				], admin_url( 'edit.php' ) );

				if ( 'tu-xa' === $slug || 'dao-tao-tu-xa' === $slug ) {
					echo '<a href="' . esc_url( $filter_url ) . '" style="text-decoration:none;"><span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:600;font-size:11px;margin:2px 0;">' . esc_html( $name ) . '</span></a>';
				} elseif ( 'vua-hoc-vua-lam' === $slug ) {
					echo '<a href="' . esc_url( $filter_url ) . '" style="text-decoration:none;"><span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-weight:600;font-size:11px;margin:2px 0;">' . esc_html( $name ) . '</span></a>';
				} elseif ( 'chinh-quy' === $slug ) {
					echo '<a href="' . esc_url( $filter_url ) . '" style="text-decoration:none;"><span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-weight:600;font-size:11px;margin:2px 0;">' . esc_html( $name ) . '</span></a>';
				} else {
					echo '<a href="' . esc_url( $filter_url ) . '" style="text-decoration:none;"><span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-weight:600;font-size:11px;margin:2px 0;">' . esc_html( $name ) . '</span></a>';
				}
			}
		} else {
			echo '<span style="color:#94a3b8;">—</span>';
		}
	} elseif ( 'program_tuition' === $column ) {
		$fee = function_exists( 'ltdh_get_program_tuition_display' ) ? ltdh_get_program_tuition_display( $post_id ) : ( get_field( 'tuition_fee', $post_id ) ?: '' );
		echo ! empty( $fee ) ? '<strong style="color:#00308b;font-size:12px;">' . esc_html( $fee ) . '</strong>' : '<span style="color:#94a3b8;">Chưa cập nhật</span>';
	} elseif ( 'verification_status' === $column ) {
		$verif = ltdh_get_program_verification( $post_id );

		if ( $verif['is_verified'] ) {
			echo '<span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:600;font-size:11px;">✓ Đã xác minh</span>';
			if ( ! empty( $verif['last_verified_at'] ) ) {
				echo '<div style="font-size:11px;color:#64748b;margin-top:2px;">' . esc_html( $verif['last_verified_at'] ) . '</div>';
			}
			if ( ! empty( $verif['source_url'] ) ) {
				$label = ! empty( $verif['source_title'] ) ? $verif['source_title'] : 'Nguồn chính thức';
				echo '<div style="font-size:10px;margin-top:2px;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><a href="' . esc_url( $verif['source_url'] ) . '" target="_blank" rel="noopener noreferrer nofollow" style="color:#0284c7;text-decoration:none;" title="' . esc_attr( $label ) . '">' . esc_html( $label ) . ' ↗</a></div>';
			}
		} elseif ( $verif['is_partial'] ) {
			echo '<span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-weight:600;font-size:11px;">⚠ Một phần</span>';
		} else {
			echo '<span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-weight:500;font-size:11px;">Chưa xác minh</span>';
		}
	} elseif ( 'data_completeness' === $column ) {
		$completeness = ltdh_get_program_data_completeness( $post_id );
		if ( $completeness['is_p0_valid'] ) {
			echo '<span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:600;font-size:11px;" title="Đầy đủ 100% các trường thông tin bắt buộc">✓ Đủ điều kiện</span>';
			if ( ! empty( $completeness['warnings'] ) ) {
				echo '<div style="font-size:10px;color:#d97706;margin-top:2px;" title="' . esc_attr( implode( '; ', $completeness['warnings'] ) ) . '">⚠ Cảnh báo dữ liệu</div>';
			}
		} else {
			$count = count( $completeness['p0_missing'] );
			$title = implode( "\n• ", $completeness['p0_missing'] );
			echo '<span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-weight:600;font-size:11px;cursor:help;" title="Chưa đủ thông tin để xuất bản:&#10;• ' . esc_attr( $title ) . '">⚠ Thiếu ' . $count . ' mục</span>';
		}
	} elseif ( 'author' === $column ) {
		$author_id   = (int) get_post_field( 'post_author', $post_id );
		$author_name = get_the_author_meta( 'display_name', $author_id );
		if ( ! empty( $author_name ) ) {
			$filter_url = add_query_arg( [
				'post_type' => 'program',
				'author'    => $author_id,
			], admin_url( 'edit.php' ) );
			echo '<a href="' . esc_url( $filter_url ) . '">' . esc_html( $author_name ) . '</a>';
		} else {
			echo '<span style="color:#94a3b8;">—</span>';
		}
	}
}

add_action( 'restrict_manage_posts', 'ltdh_filter_programs_dropdowns' );
function ltdh_filter_programs_dropdowns( $post_type ) {
	if ( 'program' !== $post_type ) {
		return;
	}
	$selected_verif = isset( $_GET['filter_verification_status'] ) ? sanitize_text_field( $_GET['filter_verification_status'] ) : '';
	?>
	<select name="filter_verification_status">
		<option value="">Tất cả trạng thái xác minh</option>
		<option value="verified" <?php selected( $selected_verif, 'verified' ); ?>>Đã xác minh</option>
		<option value="partially_verified" <?php selected( $selected_verif, 'partially_verified' ); ?>>Đã xác minh một phần</option>
		<option value="unverified" <?php selected( $selected_verif, 'unverified' ); ?>>Chưa xác minh</option>
	</select>
	<?php
	$selected_comp = isset( $_GET['filter_data_completeness'] ) ? sanitize_text_field( $_GET['filter_data_completeness'] ) : '';
	?>
	<select name="filter_data_completeness">
		<option value="">Tất cả điều kiện xuất bản</option>
		<option value="complete" <?php selected( $selected_comp, 'complete' ); ?>>Đủ điều kiện xuất bản</option>
		<option value="incomplete" <?php selected( $selected_comp, 'incomplete' ); ?>>Thiếu thông tin bắt buộc</option>
	</select>
	<?php
}

add_action( 'pre_get_posts', 'ltdh_apply_program_admin_filters' );
function ltdh_apply_program_admin_filters( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'program' !== $query->get( 'post_type' ) ) {
		return;
	}

	$meta_query = $query->get( 'meta_query' ) ?: [];

	$verif_filter = isset( $_GET['filter_verification_status'] ) ? sanitize_text_field( $_GET['filter_verification_status'] ) : '';
	if ( ! empty( $verif_filter ) && in_array( $verif_filter, [ 'verified', 'partially_verified', 'unverified' ], true ) ) {
		if ( 'unverified' === $verif_filter ) {
			$meta_query[] = [
				'relation' => 'OR',
				[
					'key'     => 'verification_status',
					'value'   => 'unverified',
					'compare' => '=',
				],
				[
					'key'     => 'verification_status',
					'compare' => 'NOT EXISTS',
				],
				[
					'key'     => 'verification_status',
					'value'   => '',
					'compare' => '=',
				],
			];
		} else {
			$meta_query[] = [
				'key'     => 'verification_status',
				'value'   => $verif_filter,
				'compare' => '=',
			];
		}
	}

	$comp_filter = isset( $_GET['filter_data_completeness'] ) ? sanitize_text_field( $_GET['filter_data_completeness'] ) : '';
	if ( 'complete' === $comp_filter ) {
		$meta_query[] = [
			'key'     => 'tuition_amount',
			'value'   => 0,
			'compare' => '>',
			'type'    => 'NUMERIC',
		];
	} elseif ( 'incomplete' === $comp_filter ) {
		$meta_query[] = [
			'relation' => 'OR',
			[
				'key'     => 'tuition_amount',
				'compare' => 'NOT EXISTS',
			],
			[
				'key'     => 'tuition_amount',
				'value'   => '',
				'compare' => '=',
			],
			[
				'key'     => 'tuition_amount',
				'value'   => '0',
				'compare' => '<=',
				'type'    => 'NUMERIC',
			],
		];
	}

	if ( ! empty( $meta_query ) ) {
		$query->set( 'meta_query', $meta_query );
	}
}

/**
 * Kiểm tra và trả về mức độ hoàn thiện các trường dữ liệu bắt buộc (P0/P1) của một chương trình.
 *
 * @param int $post_id Program post ID.
 * @return array
 */
function ltdh_get_program_data_completeness( $post_id ) {
	$p0_missing = [];
	$p1_missing = [];
	$warnings   = [];

	if ( ! $post_id || 'program' !== get_post_type( $post_id ) ) {
		return [
			'is_p0_valid' => false,
			'p0_missing'  => [ 'Bản ghi không tồn tại hoặc không phải là chương trình đào tạo' ],
			'p1_missing'  => [],
			'warnings'    => [],
			'score'       => 0,
		];
	}

	// 1. Kiểm tra: Thực thể liên kết (Trường và Ngành bắt buộc phải chọn và ở trạng thái công khai)
	$school_id = get_post_meta( $post_id, 'school_relationship', true );
	if ( empty( $school_id ) ) {
		$p0_missing[] = 'Trường "Trường đào tạo (Liên kết)" (Tab Tổng quan chương trình): Chưa chọn trường đại học liên kết.';
	} else {
		$school_post = get_post( $school_id );
		if ( ! $school_post || 'publish' !== $school_post->post_status || 'school' !== $school_post->post_type ) {
			$p0_missing[] = 'Trường "Trường đào tạo (Liên kết)" (Tab Tổng quan chương trình): Trường được chọn không ở trạng thái công khai hoặc đã bị xóa.';
		}
	}

	$major_id = get_post_meta( $post_id, 'major_relationship', true );
	if ( empty( $major_id ) ) {
		$p0_missing[] = 'Trường "Ngành đào tạo (Liên kết)" (Tab Tổng quan chương trình): Chưa chọn ngành đào tạo.';
	} else {
		$major_post = get_post( $major_id );
		if ( ! $major_post || 'publish' !== $major_post->post_status || 'major' !== $major_post->post_type ) {
			$p0_missing[] = 'Trường "Ngành đào tạo (Liên kết)" (Tab Tổng quan chương trình): Ngành được chọn không hợp lệ hoặc đã bị xóa vào thùng rác.';
		}
	}

	// 2. Kiểm tra: Phân loại Taxonomies
	$tt = get_the_terms( $post_id, 'training_type' );
	if ( empty( $tt ) || is_wp_error( $tt ) ) {
		$p0_missing[] = 'Mục "Hình thức đào tạo" (ở cột bên phải): Vui lòng tick chọn ít nhất một hình thức (ví dụ: Liên thông chính quy, Vừa học vừa làm, Từ xa...).';
	}

	$cp = get_the_terms( $post_id, 'campus' );
	if ( empty( $cp ) || is_wp_error( $cp ) ) {
		$p0_missing[] = 'Mục "Cơ sở đào tạo" (ở cột bên phải): Vui lòng tick chọn cơ sở đào tạo (ví dụ: Hà Nội, TP.HCM...).';
	}

	// 3. Kiểm tra: Học phí
	$tuition_fee = get_post_meta( $post_id, 'tuition_fee', true );
	if ( empty( $tuition_fee ) ) {
		$p0_missing[] = 'Trường "Học phí chỉ từ" (Tab Học phí & Thời gian học): Đang để trống (ví dụ: "450.000đ/tín chỉ" hoặc "15 triệu/học kỳ").';
	}

	$tuition_amount = get_post_meta( $post_id, 'tuition_amount', true );
	if ( '' === $tuition_amount || null === $tuition_amount || ! is_numeric( $tuition_amount ) || intval( $tuition_amount ) < 1000 ) {
		$p0_missing[] = 'Trường "Mức học phí định mức (số tiền)" (Tab Học phí & Thời gian học): Vui lòng nhập số tiền VNĐ đầy đủ (tối thiểu 1.000đ, chỉ nhập số nguyên).';
	}

	$tuition_unit = get_post_meta( $post_id, 'tuition_unit', true );
	if ( empty( $tuition_unit ) ) {
		$p0_missing[] = 'Trường "Đơn vị tính học phí" (Tab Học phí & Thời gian học): Vui lòng chọn đơn vị tính (ví dụ: /tín chỉ, /học kỳ...).';
	}

	// 4. Kiểm tra: Thời gian học & Trạng thái tuyển sinh
	$duration = get_post_meta( $post_id, 'duration', true );
	if ( empty( $duration ) ) {
		$p0_missing[] = 'Trường "Thời gian học" (Tab Học phí & Thời gian học): Đang để trống (ví dụ: "1.5 - 2 năm").';
	}

	$status = get_post_meta( $post_id, 'admission_status', true );
	if ( empty( $status ) ) {
		$p0_missing[] = 'Trường "Trạng thái tuyển sinh" (Tab Tổng quan chương trình): Vui lòng chọn trạng thái (ví dụ: Đang nhận hồ sơ, Sắp khai giảng...).';
	}

	// 5. Kiểm tra: Yêu cầu & Hồ sơ
	$req = get_post_meta( $post_id, 'admission_requirements', true );
	$req_clean = trim( strip_tags( (string) $req ) );
	if ( empty( $req_clean ) ) {
		$p0_missing[] = 'Trường "Điều kiện xét tuyển" (Tab Điều kiện & Hồ sơ xét tuyển): Đang để trống.';
	} elseif ( strlen( $req_clean ) < 80 ) {
		$p0_missing[] = 'Trường "Điều kiện xét tuyển" (Tab Điều kiện & Hồ sơ xét tuyển): Nội dung quá ngắn (cần tối thiểu 80 ký tự để mô tả chi tiết đối tượng tuyển sinh).';
	} elseif ( str_contains( $req_clean, 'THPT' ) ) {
		$p0_missing[] = 'Trường "Điều kiện xét tuyển" (Tab Điều kiện & Hồ sơ xét tuyển): Còn chứa từ khóa "THPT". Hệ liên thông chỉ tuyển sinh thí sinh đã tốt nghiệp Trung cấp hoặc Cao đẳng, vui lòng điều chỉnh lại.';
	}

	$docs = get_post_meta( $post_id, 'required_documents', true );
	$docs_clean = trim( strip_tags( (string) $docs ) );
	if ( empty( $docs_clean ) ) {
		$p0_missing[] = 'Trường "Hồ sơ xét tuyển cần thiết" (Tab Điều kiện & Hồ sơ xét tuyển): Đang để trống.';
	} elseif ( strlen( $docs_clean ) < 80 ) {
		$p0_missing[] = 'Trường "Hồ sơ xét tuyển cần thiết" (Tab Điều kiện & Hồ sơ xét tuyển): Nội dung quá ngắn (cần tối thiểu 80 ký tự để hướng dẫn hồ sơ giấy tờ cần nộp).';
	}

	// 6. Kiểm tra: Đợt tuyển sinh
	$batches = get_field( 'admission_batches', $post_id );
	if ( empty( $batches ) || ! is_array( $batches ) ) {
		$p0_missing[] = 'Trường "Các đợt tuyển sinh" (Tab Các đợt tuyển sinh): Chưa thêm đợt tuyển sinh nào (bấm "Thêm dòng" để khai báo).';
	}

	$verif_status = get_post_meta( $post_id, 'verification_status', true );
	if ( 'verified' === $verif_status ) {
		$source = get_post_meta( $post_id, 'verification_source', true );
		$date   = get_post_meta( $post_id, 'last_verified_at', true );
		if ( empty( $source ) ) {
			$p1_missing[] = 'Trường "Nguồn xác minh (URL)" (Tab Xác minh thông tin): Đã chọn Đã xác minh nhưng chưa nhập link tài liệu/thông báo.';
		}
		if ( empty( $date ) ) {
			$p1_missing[] = 'Trường "Ngày xác minh gần nhất" (Tab Xác minh thông tin): Đang để trống ngày xác minh.';
		}
	}

	// 7. Cảnh báo dữ liệu bất thường (Sanity Warnings)
	if ( $duration && ( preg_match( '/\d{4}-\d{2}-\d{2}/', (string) $duration ) || str_contains( (string) $duration, '00:00:00' ) ) ) {
		$warnings[] = 'Thời gian học dính lỗi định dạng ngày tháng Excel: ' . esc_html( $duration );
	}
	if ( $tuition_fee && preg_match( '/\b\d+\.0\b/', (string) $tuition_fee ) ) {
		$warnings[] = 'Học phí bị dính định dạng số thực lẻ (.0): ' . esc_html( $tuition_fee );
	}

	$form_file = function_exists( 'ltdh_get_program_admission_form_url' ) ? ltdh_get_program_admission_form_url( $post_id ) : get_post_meta( $post_id, 'admission_form_file', true );
	if ( ! empty( $form_file ) && is_string( $form_file ) && str_contains( $form_file, 'utc' ) ) {
		$school_slug = $school_id ? get_post_field( 'post_name', $school_id ) : '';
		if ( ! str_contains( (string) $school_slug, 'giao-thong-van-tai' ) ) {
			$warnings[] = 'Phiếu đăng ký tuyển sinh đang trỏ nhầm về biểu mẫu UTC';
		}
	}

	$total_checks = 11;
	$passed       = $total_checks - count( $p0_missing );
	$score        = max( 0, min( 100, intval( round( ( $passed / $total_checks ) * 100 ) ) ) );

	return [
		'is_p0_valid' => empty( $p0_missing ),
		'p0_missing'  => $p0_missing,
		'p1_missing'  => $p1_missing,
		'warnings'    => $warnings,
		'score'       => $score,
	];
}

/**
 * Hiển thị thông báo hướng dẫn hoàn thiện trường dữ liệu bắt buộc trên màn hình sửa bài viết.
 */
add_action( 'admin_notices', 'ltdh_program_admin_edit_completeness_notice' );
function ltdh_program_admin_edit_completeness_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'program' !== $screen->post_type || 'post' !== $screen->base ) {
		return;
	}
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return;
	}
	$completeness = ltdh_get_program_data_completeness( $post_id );
	if ( ! $completeness['is_p0_valid'] ) {
		?>
		<div class="notice notice-warning is-dismissible" style="border-left-color: #f59e0b; padding: 14px 18px; background: #fffdf5; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
			<p style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700; color: #92400e; display: flex; align-items: center; gap: 6px;">
				<span>⚠️</span> THÔNG TIN CẦN BỔ SUNG TRƯỚC KHI XUẤT BẢN:
			</p>
			<p style="margin: 0 0 8px 0; color: #1e293b; font-size: 13px;">
				Chương trình này đang còn <strong><?php echo count( $completeness['p0_missing'] ); ?> mục thông tin bắt buộc</strong> chưa hoàn thiện (cần bổ sung để đủ điều kiện Xuất bản):
			</p>
			<ul style="margin: 0 0 10px 20px; list-style-type: disc; color: #dc2626; font-size: 13px; line-height: 1.6;">
				<?php foreach ( $completeness['p0_missing'] as $miss ) : ?>
					<li style="margin-bottom: 4px;"><?php echo esc_html( $miss ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php if ( ! empty( $completeness['warnings'] ) ) : ?>
				<p style="margin: 6px 0; color: #b45309; font-size: 12px; background: #fef3c7; padding: 6px 10px; border-radius: 4px;">
					<strong>⚠️ Lưu ý chất lượng dữ liệu:</strong> <?php echo esc_html( implode( ' | ', $completeness['warnings'] ) ); ?>
				</p>
			<?php endif; ?>
			<p style="margin: 6px 0 0 0; font-size: 12px; color: #64748b; line-height: 1.5;">
				💡 <em>Ghi chú: Bạn vẫn có thể bấm <strong>"Lưu bản nháp" (Save Draft)</strong> để lưu tiến độ. Hệ thống chỉ yêu cầu hoàn thiện đầy đủ các mục trên khi bạn bấm <strong>"Xuất bản" (Publish)</strong>.</em>
			</p>
		</div>
		<?php
	}
}

/**
 * Kiểm thực chặn xuất bản bài viết nếu thiếu các trường bắt buộc trên giao diện ACF Admin.
 */
add_action( 'acf/validate_save_post', 'ltdh_validate_program_acf_publish_fields', 10 );
function ltdh_validate_program_acf_publish_fields() {
	$post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';
	if ( 'program' !== $post_type ) {
		return;
	}

	// Chỉ bắt buộc kiểm tra chặt chẽ khi có hành động Xuất bản (Publish)
	$is_publishing = false;
	if ( isset( $_POST['post_status'] ) && 'publish' === $_POST['post_status'] ) {
		$is_publishing = true;
	} elseif ( isset( $_POST['publish'] ) ) {
		$is_publishing = true;
	}

	if ( ! $is_publishing ) {
		return;
	}

	$acf = isset( $_POST['acf'] ) && is_array( $_POST['acf'] ) ? $_POST['acf'] : [];

	// 1. School relationship
	if ( empty( $acf['field_program_school'] ) ) {
		acf_add_validation_error( 'acf[field_program_school]', 'Vui lòng chọn trường "Trường đào tạo (Liên kết)" ở Tab "Tổng quan chương trình" trước khi Xuất bản.' );
	}

	// 2. Major relationship
	if ( empty( $acf['field_program_major'] ) ) {
		acf_add_validation_error( 'acf[field_program_major]', 'Vui lòng chọn trường "Ngành đào tạo (Liên kết)" ở Tab "Tổng quan chương trình" trước khi Xuất bản.' );
	}

	// 3. Tuition Fee
	if ( empty( $acf['field_program_tuition'] ) ) {
		acf_add_validation_error( 'acf[field_program_tuition]', 'Vui lòng nhập trường "Học phí chỉ từ" ở Tab "Học phí & Thời gian học" trước khi Xuất bản.' );
	}

	// 4. Tuition Amount
	if ( empty( $acf['field_program_tuition_amount'] ) || intval( $acf['field_program_tuition_amount'] ) < 1000 ) {
		acf_add_validation_error( 'acf[field_program_tuition_amount]', 'Vui lòng nhập trường "Mức học phí định mức (số tiền)" bằng số tiền VNĐ đầy đủ (tối thiểu 1.000đ) ở Tab "Học phí & Thời gian học".' );
	}

	// 5. Tuition Unit
	if ( empty( $acf['field_program_tuition_unit'] ) ) {
		acf_add_validation_error( 'acf[field_program_tuition_unit]', 'Vui lòng chọn trường "Đơn vị tính học phí" ở Tab "Học phí & Thời gian học" trước khi Xuất bản.' );
	}

	// 6. Duration
	if ( empty( $acf['field_program_duration'] ) ) {
		acf_add_validation_error( 'acf[field_program_duration]', 'Vui lòng nhập trường "Thời gian học" ở Tab "Học phí & Thời gian học" trước khi Xuất bản.' );
	} elseif ( preg_match( '/\d{4}-\d{2}-\d{2}/', (string) $acf['field_program_duration'] ) ) {
		acf_add_validation_error( 'acf[field_program_duration]', 'Thời gian học bị dính lỗi định dạng ngày tháng Excel. Vui lòng nhập khoảng thời gian dạng "1.5 - 2 năm".' );
	}

	// 7. Admission status
	if ( empty( $acf['field_program_admission_status'] ) ) {
		acf_add_validation_error( 'acf[field_program_admission_status]', 'Vui lòng chọn trường "Trạng thái tuyển sinh" ở Tab "Tổng quan chương trình" trước khi Xuất bản.' );
	}

	// 8. Requirements
	$req_text = isset( $acf['field_program_requirements'] ) ? trim( strip_tags( (string) $acf['field_program_requirements'] ) ) : '';
	if ( empty( $req_text ) ) {
		acf_add_validation_error( 'acf[field_program_requirements]', 'Vui lòng nhập nội dung trường "Điều kiện xét tuyển" ở Tab "Điều kiện & Hồ sơ xét tuyển" trước khi Xuất bản.' );
	}

	// 9. Required documents
	$doc_text = isset( $acf['field_program_documents'] ) ? trim( strip_tags( (string) $acf['field_program_documents'] ) ) : '';
	if ( empty( $doc_text ) ) {
		acf_add_validation_error( 'acf[field_program_documents]', 'Vui lòng nhập danh mục trường "Hồ sơ xét tuyển cần thiết" ở Tab "Điều kiện & Hồ sơ xét tuyển" trước khi Xuất bản.' );
	}
}






