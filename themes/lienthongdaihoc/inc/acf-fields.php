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
