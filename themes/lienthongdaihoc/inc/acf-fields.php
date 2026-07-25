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

		// Skip if already exists in database
		if ( in_array( $item['key'], $existing_keys, true ) ) {
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


