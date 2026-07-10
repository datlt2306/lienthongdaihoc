<?php
/**
 * ACF Pro PHP Field Groups Configuration
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'ltdh_register_acf_field_groups' );

function ltdh_register_acf_field_groups() {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// ----------------------------------------------------
	// 1. School Field Group
	// ----------------------------------------------------
	acf_add_local_field_group( [
		'key'    => 'group_school_details',
		'title'  => 'Thông tin Trường học',
		'fields' => [
			[
				'key'           => 'field_school_logo',
				'label'         => 'Logo Trường',
				'name'          => 'logo',
				'type'          => 'image',
				'return_format' => 'id',
				'preview_size'  => 'thumbnail',
				'library'       => 'all',
			],
			[
				'key'   => 'field_school_website',
				'label' => 'Website chính thức',
				'name'  => 'website',
				'type'  => 'url',
			],
			[
				'key'   => 'field_school_address',
				'label' => 'Địa chỉ trụ sở',
				'name'  => 'address',
				'type'  => 'text',
			],
			[
				'key'   => 'field_school_hotline',
				'label' => 'Hotline tuyển sinh',
				'name'  => 'hotline',
				'type'  => 'text',
			],
			[
				'key'   => 'field_school_admission_info',
				'label' => 'Thông tin tuyển sinh chung',
				'name'  => 'admission_info',
				'type'  => 'wysiwyg',
				'toolbar' => 'full',
				'media_upload' => 1,
			],
			[
				'key'   => 'field_school_contact_info',
				'label' => 'Thông tin liên hệ chi tiết',
				'name'  => 'contact_info',
				'type'  => 'wysiwyg',
				'toolbar' => 'full',
				'media_upload' => 1,
			],
		],
		'location' => [
			[
				[
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'school',
				],
			],
		],
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
	] );

	// ----------------------------------------------------
	// 2. Major Field Group
	// ----------------------------------------------------
	acf_add_local_field_group( [
		'key'    => 'group_major_details',
		'title'  => 'Thông tin Ngành học',
		'fields' => [
			[
				'key'   => 'field_major_code',
				'label' => 'Mã ngành',
				'name'  => 'major_code',
				'type'  => 'text',
			],
			[
				'key'   => 'field_major_opportunities',
				'label' => 'Cơ hội nghề nghiệp',
				'name'  => 'career_opportunities',
				'type'  => 'wysiwyg',
			],
			[
				'key'   => 'field_major_salary',
				'label' => 'Thông tin mức lương',
				'name'  => 'salary_info',
				'type'  => 'wysiwyg',
			],
			[
				'key'   => 'field_major_job_market',
				'label' => 'Nhu cầu thị trường lao động',
				'name'  => 'job_market',
				'type'  => 'wysiwyg',
			],
		],
		'location' => [
			[
				[
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'major',
				],
			],
		],
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
	] );

	// ----------------------------------------------------
	// 3. Program Field Group
	// ----------------------------------------------------
	acf_add_local_field_group( [
		'key'    => 'group_program_details',
		'title'  => 'Thông tin Chương trình tuyển sinh',
		'fields' => [
			[
				'key'           => 'field_program_school',
				'label'         => 'Trường đào tạo (Liên kết)',
				'name'          => 'school_relationship',
				'type'          => 'post_object',
				'post_type'     => [ 'school' ],
				'allow_null'    => 0,
				'multiple'      => 0,
				'return_format' => 'id',
				'ui'            => 1,
			],
			[
				'key'           => 'field_program_major',
				'label'         => 'Ngành đào tạo (Liên kết)',
				'name'          => 'major_relationship',
				'type'          => 'post_object',
				'post_type'     => [ 'major' ],
				'allow_null'    => 0,
				'multiple'      => 0,
				'return_format' => 'id',
				'ui'            => 1,
			],
			[
				'key'   => 'field_program_tuition',
				'label' => 'Học phí',
				'name'  => 'tuition_fee',
				'type'  => 'text',
				'placeholder' => 'Ví dụ: 450,000đ / tín chỉ hoặc 15,000,000đ / học kỳ',
			],
			[
				'key'   => 'field_program_duration',
				'label' => 'Thời gian đào tạo',
				'name'  => 'duration',
				'type'  => 'text',
				'placeholder' => 'Ví dụ: 1.5 - 2 năm',
			],
			[
				'key'   => 'field_program_campus',
				'label' => 'Địa điểm học tập / Cơ sở',
				'name'  => 'campus_info',
				'type'  => 'text',
				'placeholder' => 'Hà Nội, Hồ Chí Minh, Học trực tuyến (Online)...',
			],
			[
				'key'   => 'field_program_requirements',
				'label' => 'Điều kiện xét tuyển',
				'name'  => 'admission_requirements',
				'type'  => 'wysiwyg',
			],
			[
				'key'   => 'field_program_documents',
				'label' => 'Hồ sơ xét tuyển cần thiết',
				'name'  => 'required_documents',
				'type'  => 'wysiwyg',
			],
			[
				'key'   => 'field_program_period',
				'label' => 'Hạn nhận hồ sơ / Thời gian khai giảng',
				'name'  => 'enrollment_period',
				'type'  => 'text',
				'placeholder' => 'Ví dụ: Đợt 1 đến hết 30/11/2026',
			],
			[
				'key'   => 'field_program_hotline',
				'label' => 'Hotline riêng chương trình (Nếu có)',
				'name'  => 'hotline_override',
				'type'  => 'text',
				'placeholder' => 'Bỏ trống để dùng Hotline chung của trường',
			],
			[
				'key'   => 'field_program_benefits',
				'label' => 'Quyền lợi / Ưu điểm chương trình',
				'name'  => 'program_benefits',
				'type'  => 'wysiwyg',
			],
			[
				'key'   => 'field_program_opportunities',
				'label' => 'Cơ hội việc làm sau tốt nghiệp',
				'name'  => 'career_opportunities',
				'type'  => 'wysiwyg',
			],
			[
				'key'   => 'field_program_why_choose',
				'label' => 'Tại sao nên học chương trình này?',
				'name'  => 'why_choose_us',
				'type'  => 'wysiwyg',
			],
			[
				'key'          => 'field_program_faq',
				'label'        => 'Câu hỏi thường gặp (FAQs)',
				'name'         => 'faq',
				'type'         => 'repeater',
				'layout'       => 'row',
				'button_label' => 'Thêm câu hỏi',
				'sub_fields'   => [
					[
						'key'   => 'field_program_faq_question',
						'label' => 'Câu hỏi',
						'name'  => 'question',
						'type'  => 'text',
					],
					[
						'key'   => 'field_program_faq_answer',
						'label' => 'Câu trả lời',
						'name'  => 'answer',
						'type'  => 'textarea',
						'rows'  => 3,
					],
				],
			],
		],
		'location' => [
			[
				[
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'program',
				],
			],
		],
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
	] );

	// ----------------------------------------------------
	// 4. Options Page & Fields
	// ----------------------------------------------------
	acf_add_options_page( [
		'page_title' => 'Cấu hình Hệ thống',
		'menu_title' => 'Cấu hình LTDH',
		'menu_slug'  => 'ltdh-settings',
		'capability' => 'manage_options',
		'redirect'   => false,
	] );

	acf_add_local_field_group( [
		'key'    => 'group_ltdh_global_settings',
		'title'  => 'Thông tin Liên hệ & Tích hợp CRM',
		'fields' => [
			[
				'key'   => 'field_global_hotline',
				'label' => 'Hotline tư vấn chung',
				'name'  => 'global_hotline',
				'type'  => 'text',
			],
			[
				'key'   => 'field_global_zalo',
				'label' => 'Đường dẫn Zalo OA / Zalo cá nhân',
				'name'  => 'global_zalo_url',
				'type'  => 'url',
			],
			[
				'key'   => 'field_global_messenger',
				'label' => 'Đường dẫn Facebook Messenger',
				'name'  => 'global_messenger_url',
				'type'  => 'url',
			],
			[
				'key'          => 'field_tab_crm',
				'label'        => 'Tích hợp CRM',
				'type'         => 'tab',
				'placement'    => 'top',
				'endpoint'     => 0,
			],
			[
				'key'     => 'field_crm_type',
				'label'   => 'Hệ thống CRM mặc định',
				'name'    => 'default_crm_type',
				'type'    => 'select',
				'choices' => [
					'onschool' => 'OnSchool Platform',
					'aum'      => 'AUM CRM',
					'internal' => 'Chỉ lưu trữ nội bộ',
				],
				'default_value' => 'internal',
			],
			[
				'key'               => 'field_onschool_endpoint',
				'label'             => 'OnSchool API Endpoint',
				'name'              => 'onschool_endpoint',
				'type'              => 'url',
				'conditional_logic' => [
					[
						[
							'field'    => 'field_crm_type',
							'operator' => '==',
							'value'    => 'onschool',
						],
					],
				],
			],
			[
				'key'               => 'field_onschool_token',
				'label'             => 'OnSchool Auth Token',
				'name'              => 'onschool_token',
				'type'              => 'password',
				'conditional_logic' => [
					[
						[
							'field'    => 'field_crm_type',
							'operator' => '==',
							'value'    => 'onschool',
						],
					],
				],
			],
			[
				'key'               => 'field_aum_endpoint',
				'label'             => 'AUM API Endpoint',
				'name'              => 'aum_endpoint',
				'type'              => 'url',
				'conditional_logic' => [
					[
						[
							'field'    => 'field_crm_type',
							'operator' => '==',
							'value'    => 'aum',
						],
					],
				],
			],
			[
				'key'               => 'field_aum_token',
				'label'             => 'AUM Auth Token',
				'name'              => 'aum_token',
				'type'              => 'password',
				'conditional_logic' => [
					[
						[
							'field'    => 'field_crm_type',
							'operator' => '==',
							'value'    => 'aum',
						],
					],
				],
			],
		],
		'location' => [
			[
				[
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'ltdh-settings',
				],
			],
		],
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
	] );

	// ----------------------------------------------------
	// Page Banner Field Group
	// ----------------------------------------------------
	acf_add_local_field_group( [
		'key'    => 'group_page_banner',
		'title'  => 'Banner trang',
		'fields' => [
			[
				'key'           => 'field_page_banner_image',
				'label'         => 'Ảnh banner',
				'name'          => 'page_banner',
				'type'          => 'image',
				'return_format' => 'url',
				'preview_size'  => 'large',
				'library'       => 'all',
				'instructions'  => 'Kích thước đề nghị: 1920x400px',
			],
			[
				'key'   => 'field_page_banner_subtitle',
				'label' => 'Mô tả ngắn banner',
				'name'  => 'page_banner_subtitle',
				'type'  => 'text',
			],
		],
		'location' => [
			[
				[
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'page',
				],
			],
		],
		'menu_order' => 0,
		'position'   => 'acf_after_title',
		'style'      => 'default',
		'active'     => true,
	] );
}
