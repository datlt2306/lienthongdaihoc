<?php
/**
 * ACF Options Page — Extended tabs for Page Mapping, Homepage, and Navigation.
 *
 * This file registers additional ACF field groups via acf_add_local_field_group()
 * that attach to the existing ltdh-settings options page.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register additional Options Page field groups after ACF loads.
 */
function ltdh_register_options_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// Check if already imported (avoid duplicates).
	$existing = acf_get_field_groups();
	$existing_keys = array_column( $existing, 'key' );

	// ---------------------------------------------------
	// Group 1: Page Mapping (CF7 form IDs)
	// ---------------------------------------------------
	if ( ! in_array( 'group_ltdh_page_mapping', $existing_keys, true ) ) {
		acf_add_local_field_group( [
			'key'       => 'group_ltdh_page_mapping',
			'title'     => 'Ánh xạ Form & Trang',
			'fields'    => [
				[
					'key'          => 'field_ltdh_tab_forms',
					'label'        => 'Contact Form 7 Mapping',
					'type'         => 'tab',
					'placement'    => 'top',
					'endpoint'     => 0,
				],
				[
					'key'          => 'field_cf7_default_id',
					'label'        => 'Form mặc định (ID)',
					'name'         => 'cf7_default_form_id',
					'type'         => 'text',
					'instructions' => 'Nhập ID của CF7 form mặc định dùng làm fallback. Xem tại WP Admin → Contact → Contact Forms.',
					'placeholder'  => 'Ví dụ: f3902eb',
				],
				[
					'key'          => 'field_cf7_consultation_id',
					'label'        => 'Form Tư vấn (Consultation)',
					'name'         => 'cf7_consultation_form_id',
					'type'         => 'text',
					'instructions' => 'ID CF7 form dùng cho các trang đăng ký tư vấn.',
					'placeholder'  => 'Ví dụ: f3902eb',
				],
				[
					'key'          => 'field_cf7_contact_id',
					'label'        => 'Form Liên hệ (Contact)',
					'name'         => 'cf7_contact_form_id',
					'type'         => 'text',
					'instructions' => 'ID CF7 form dùng cho trang Liên hệ.',
					'placeholder'  => 'Ví dụ: contact-form',
				],
				[
					'key'          => 'field_cf7_program_id',
					'label'        => 'Form Chương trình (Program Sidebar)',
					'name'         => 'cf7_program_form_id',
					'type'         => 'text',
					'instructions' => 'ID CF7 form dùng trong sidebar trang chương trình.',
					'placeholder'  => 'Ví dụ: consultation-form',
				],
			],
			'location'  => [
				[
					[
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => LTDH_OPTIONS_PAGE,
					],
				],
			],
			'menu_order'            => 1,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		] );
	}

	// ---------------------------------------------------
	// Group 2: Homepage Content (Hero, KPIs, Testimonials)
	// ---------------------------------------------------
	if ( ! in_array( 'group_ltdh_homepage', $existing_keys, true ) ) {
		acf_add_local_field_group( [
			'key'       => 'group_ltdh_homepage',
			'title'     => 'Nội dung Trang chủ',
			'fields'    => [
				[
					'key'       => 'field_ltdh_tab_hero',
					'label'     => 'Hero Section',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				],
				[
					'key'          => 'field_hero_year_label',
					'label'        => 'Năm tuyển sinh',
					'name'         => 'hero_year_label',
					'type'         => 'text',
					'default_value' => 'TUYỂN SINH ' . date( 'Y' ),
					'placeholder'  => 'Ví dụ: TUYỂN SINH 2026',
				],
				[
					'key'          => 'field_hero_badges',
					'label'        => 'Badges (3 mục)',
					'name'         => 'hero_badges',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Thêm badge',
					'max'          => 3,
					'sub_fields'   => [
						[
							'key'   => 'field_badge_text',
							'label' => 'Dòng chính',
							'name'  => 'text',
							'type'  => 'text',
						],
						[
							'key'   => 'field_badge_subtext',
							'label' => 'Dòng phụ',
							'name'  => 'subtext',
							'type'  => 'text',
						],
					],
				],
				[
					'key'       => 'field_ltdh_tab_kpi',
					'label'     => 'KPI Section',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				],
				[
					'key'          => 'field_kpi_metrics',
					'label'        => 'KPI Metrics (4 mục)',
					'name'         => 'kpi_metrics',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Thêm metric',
					'max'          => 4,
					'sub_fields'   => [
						[
							'key'   => 'field_kpi_value',
							'label' => 'Giá trị',
							'name'  => 'value',
							'type'  => 'text',
						],
						[
							'key'   => 'field_kpi_label',
							'label' => 'Nhãn',
							'name'  => 'label',
							'type'  => 'text',
						],
					],
				],
				[
					'key'       => 'field_ltdh_tab_testimonials',
					'label'     => 'Đánh giá Học viên',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				],
				[
					'key'          => 'field_testimonials',
					'label'        => 'Testimonials',
					'name'         => 'testimonials',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Thêm đánh giá',
					'max'          => 6,
					'sub_fields'   => [
						[
							'key'   => 'field_testimonial_name',
							'label' => 'Họ tên',
							'name'  => 'name',
							'type'  => 'text',
						],
						[
							'key'   => 'field_testimonial_role',
							'label' => 'Chương trình đã học',
							'name'  => 'role',
							'type'  => 'text',
						],
						[
							'key'   => 'field_testimonial_content',
							'label' => 'Nội dung đánh giá',
							'name'  => 'content',
							'type'  => 'textarea',
							'rows'  => 3,
						],
						[
							'key'          => 'field_testimonial_initials',
							'label'        => 'Chữ cái hiển thị (avatar)',
							'name'         => 'initials',
							'type'         => 'text',
							'maxlength'    => 2,
							'placeholder'  => 'Ví dụ: NH',
						],
					],
				],
			],
			'location'  => [
				[
					[
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => LTDH_OPTIONS_PAGE,
					],
				],
			],
			'menu_order'            => 2,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		] );
	}

	// ---------------------------------------------------
	// Group 3: Navigation Links
	// ---------------------------------------------------
	if ( ! in_array( 'group_ltdh_navigation', $existing_keys, true ) ) {
		acf_add_local_field_group( [
			'key'       => 'group_ltdh_navigation',
			'title'     => 'Điều hướng Fallback',
			'fields'    => [
				[
					'key'          => 'field_menu_primary_items',
					'label'        => 'Menu Desktop (Fallback)',
					'name'         => 'menu_primary_items',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Thêm mục',
					'instructions' => 'Dùng khi WordPress menu chưa được cấu hình.',
					'sub_fields'   => [
						[
							'key'   => 'field_menu_item_label',
							'label' => 'Nhãn',
							'name'  => 'label',
							'type'  => 'text',
						],
						[
							'key'   => 'field_menu_item_url',
							'label' => 'Đường dẫn',
							'name'  => 'url',
							'type'  => 'url',
						],
					],
				],
				[
					'key'          => 'field_menu_mobile_items',
					'label'        => 'Menu Mobile (Fallback)',
					'name'         => 'menu_mobile_items',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Thêm mục',
					'sub_fields'   => [
						[
							'key'   => 'field_menu_mobile_label',
							'label' => 'Nhãn',
							'name'  => 'label',
							'type'  => 'text',
						],
						[
							'key'   => 'field_menu_mobile_url',
							'label' => 'Đường dẫn',
							'name'  => 'url',
							'type'  => 'url',
						],
					],
				],
				[
					'key'          => 'field_menu_footer_items',
					'label'        => 'Menu Footer (Fallback)',
					'name'         => 'menu_footer_items',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Thêm mục',
					'sub_fields'   => [
						[
							'key'   => 'field_menu_footer_label',
							'label' => 'Nhãn',
							'name'  => 'label',
							'type'  => 'text',
						],
						[
							'key'   => 'field_menu_footer_url',
							'label' => 'Đường dẫn',
							'name'  => 'url',
							'type'  => 'url',
						],
					],
				],
			],
			'location'  => [
				[
					[
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => LTDH_OPTIONS_PAGE,
					],
				],
			],
			'menu_order'            => 3,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		] );
	}

	// ---------------------------------------------------
	// Group 4: FAQ Page Content
	// ---------------------------------------------------
	if ( ! in_array( 'group_ltdh_faq', $existing_keys, true ) ) {
		acf_add_local_field_group( [
			'key'       => 'group_ltdh_faq',
			'title'     => 'Nội dung Trang FAQ',
			'fields'    => [
				[
					'key'          => 'field_faq_items',
					'label'        => 'Câu hỏi thường gặp',
					'name'         => 'faq_items',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Thêm câu hỏi',
					'sub_fields'   => [
						[
							'key'   => 'field_faq_question',
							'label' => 'Câu hỏi',
							'name'  => 'question',
							'type'  => 'text',
						],
						[
							'key'   => 'field_faq_answer',
							'label' => 'Câu trả lời',
							'name'  => 'answer',
							'type'  => 'textarea',
							'rows'  => 4,
						],
					],
				],
			],
			'location'  => [
				[
					[
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => LTDH_OPTIONS_PAGE,
					],
				],
			],
			'menu_order'            => 4,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		] );
	}

	// ---------------------------------------------------
	// Group 5: About Page Content
	// ---------------------------------------------------
	if ( ! in_array( 'group_ltdh_about', $existing_keys, true ) ) {
		acf_add_local_field_group( [
			'key'       => 'group_ltdh_about',
			'title'     => 'Nội dung Trang Giới thiệu',
			'fields'    => [
				[
					'key'          => 'field_about_intro',
					'label'        => 'Mô tả giới thiệu',
					'name'         => 'about_intro',
					'type'         => 'textarea',
					'rows'         => 4,
					'instructions' => 'Đoạn giới thiệu ngắn về trang web.',
				],
				[
					'key'          => 'field_about_mission',
					'label'        => 'Sứ mệnh',
					'name'         => 'about_mission',
					'type'         => 'textarea',
					'rows'         => 3,
				],
				[
					'key'          => 'field_about_vision',
					'label'        => 'Tầm nhìn',
					'name'         => 'about_vision',
					'type'         => 'textarea',
					'rows'         => 3,
				],
				[
					'key'          => 'field_about_core_values_title',
					'label'        => 'Tiêu đề Giá trị cốt lõi',
					'name'         => 'about_core_values_title',
					'type'         => 'text',
					'default_value' => 'Giá trị cốt lõi',
				],
				[
					'key'          => 'field_about_core_values',
					'label'        => 'Giá trị cốt lõi',
					'name'         => 'about_core_values',
					'type'         => 'textarea',
					'rows'         => 3,
				],
				[
					'key'          => 'field_about_vision_title',
					'label'        => 'Tiêu đề Tầm nhìn',
					'name'         => 'about_vision_title',
					'type'         => 'text',
					'default_value' => 'Tầm nhìn chiến lược',
				],
			],
			'location'  => [
				[
					[
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => LTDH_OPTIONS_PAGE,
					],
				],
			],
			'menu_order'            => 5,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		] );
	}
}
add_action( 'acf/init', 'ltdh_register_options_fields' );
