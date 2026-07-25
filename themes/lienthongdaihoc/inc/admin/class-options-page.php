<?php
/**
 * ACF Options Page — Tabs for Page Mapping, FAQ, Hot Majors, and Homepage Sections.
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
	// Group 2: FAQ Page Content
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
			'menu_order'            => 2,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		] );
	}

	// ---------------------------------------------------
	// Group 3: Hot Majors
	// ---------------------------------------------------
	if ( ! in_array( 'group_ltdh_hot_majors', $existing_keys, true ) ) {
		acf_add_local_field_group( [
			'key'       => 'group_ltdh_hot_majors',
			'title'     => 'Ngành học nổi bật',
			'fields'    => [
				[
					'key'          => 'field_hot_major_slugs',
					'label'        => 'Slug ngành hot (5 ngành)',
					'name'         => 'hot_major_slugs',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Thêm ngành',
					'max'          => 5,
					'instructions' => 'Nhap slug của ngành (ví dụ: cong-nghe-thong-tin). Hiển thị ở menu dropdown và trang chủ.',
					'sub_fields'   => [
						[
							'key'   => 'field_hot_major_slug',
							'label' => 'Slug',
							'name'  => 'slug',
							'type'  => 'text',
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
	// Group 4: Homepage Sections Config (Hero Banner + Content Sections)
	// ---------------------------------------------------
	if ( ! in_array( 'group_ltdh_homepage_sections', $existing_keys, true ) ) {
		acf_add_local_field_group( [
			'key'       => 'group_ltdh_homepage_sections',
			'title'     => 'Cấu hình Sections Homepage',
			'fields'    => [
				// ---- Section: Hero Banner ----
				[
					'key'       => 'field_hp_tab_hero',
					'label'     => 'Hero Banner',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				],
				[
					'key'          => 'field_hero_heading',
					'label'        => 'Heading chính',
					'name'         => 'hero_heading',
					'type'         => 'text',
					'default_value' => 'Tìm ngành học liên thông',
				],
				[
					'key'          => 'field_hero_heading_highlight',
					'label'        => 'Heading highlight',
					'name'         => 'hero_heading_highlight',
					'type'         => 'text',
					'default_value' => 'phù hợp cho bạn',
				],
				[
					'key'          => 'field_hero_subtitle',
					'label'        => 'Mô tả ngắn',
					'name'         => 'hero_subtitle',
					'type'         => 'textarea',
					'rows'         => 2,
					'default_value' => 'Học Liên thông Đại học chính quy, Đại học từ xa uy tín trên toàn quốc',
				],
				[
					'key'          => 'field_hero_image',
					'label'        => 'Ảnh Hero',
					'name'         => 'hero_image',
					'type'         => 'image',
					'return_format' => 'url',
					'preview_size'  => 'large',
				],
				[
					'key'       => 'field_hero_tab_cta',
					'label'     => 'CTA Buttons',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				],
				[
					'key'          => 'field_hero_cta_primary_text',
					'label'        => 'CTA chính - Text',
					'name'         => 'hero_cta_primary_text',
					'type'         => 'text',
					'default_value' => 'Tra cứu tuyển sinh',
				],
				[
					'key'          => 'field_hero_cta_primary_url',
					'label'        => 'CTA chính - Link',
					'name'         => 'hero_cta_primary_url',
					'type'         => 'url',
					'default_value' => '/he-dao-tao/tu-xa/',
				],
				[
					'key'          => 'field_hero_cta_secondary_text',
					'label'        => 'CTA phụ - Text',
					'name'         => 'hero_cta_secondary_text',
					'type'         => 'text',
					'default_value' => 'Hotline tư vấn',
				],

				// ---- Section: Kiểm tra điều kiện ----
				[
					'key'       => 'field_hp_tab_eligibility',
					'label'     => 'Kiểm tra điều kiện',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				],
				[
					'key'          => 'field_hp_elig_badge',
					'label'        => 'Badge label',
					'name'         => 'hp_elig_badge',
					'type'         => 'text',
					'default_value' => 'Điều kiện tuyển sinh',
				],
				[
					'key'          => 'field_hp_elig_heading',
					'label'        => 'Heading',
					'name'         => 'hp_elig_heading',
					'type'         => 'text',
					'default_value' => 'Bạn có đủ điều kiện học\nLiên thông & Đại học từ xa?',
				],
				[
					'key'          => 'field_hp_elig_desc',
					'label'        => 'Mô tả',
					'name'         => 'hp_elig_desc',
					'type'         => 'textarea',
					'rows'         => 3,
					'default_value' => 'Chương trình tuyển sinh mở rộng cho nhiều đối tượng. Chỉ mất 1 phút để kiểm tra tự động.',
				],
				[
					'key'          => 'field_hp_elig_items',
					'label'        => '3 ô thông tin',
					'name'         => 'hp_elig_items',
					'type'         => 'repeater',
					'max'          => 3,
					'layout'       => 'table',
					'button_label' => 'Thêm mục',
					'sub_fields'   => [
						[ 'key' => 'field_hp_elig_item_title', 'label' => 'Tiêu đề', 'name' => 'title', 'type' => 'text' ],
						[ 'key' => 'field_hp_elig_item_desc', 'label' => 'Mô tả', 'name' => 'desc', 'type' => 'text' ],
					],
				],
				[
					'key'          => 'field_hp_elig_cta_text',
					'label'        => 'CTA Text',
					'name'         => 'hp_elig_cta_text',
					'type'         => 'text',
					'default_value' => 'Bắt đầu kiểm tra ngay ➔',
				],
				[
					'key'          => 'field_hp_elig_cta_url',
					'label'        => 'CTA URL',
					'name'         => 'hp_elig_cta_url',
					'type'         => 'url',
					'default_value' => '/kiem-tra-dieu-kien/',
				],

				// ---- Section: Bằng cấp ----
				[
					'key'       => 'field_hp_tab_certificate',
					'label'     => 'Bằng cấp & Giá trị',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				],
				[
					'key'   => 'field_hp_cert_left_badge',
					'label' => 'Badge tiêu đề',
					'name'  => 'hp_cert_left_badge',
					'type'  => 'text',
					'default_value' => 'BẰNG CẤP CHÍNH QUY',
				],
				[
					'key'   => 'field_hp_cert_left_heading',
					'label' => 'Heading chính',
					'name'  => 'hp_cert_left_heading',
					'type'  => 'text',
					'default_value' => 'KHÔNG GHI HÌNH THỨC ĐÀO TẠO',
				],
				[
					'key'          => 'field_hp_cert_slider',
					'label'        => 'Slider ảnh bên phải',
					'name'         => 'hp_cert_slider',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Thêm ảnh slider',
					'instructions' => 'Thêm các hình ảnh hiển thị ở slider bên phải. Nếu không có ảnh nào, hệ thống sẽ sử dụng ảnh tốt nghiệp mặc định.',
					'sub_fields'   => [
						[
							'key'           => 'field_hp_cert_slide_img',
							'label'         => 'Hình ảnh',
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'url',
							'preview_size'  => 'medium',
						],
					],
				],
				[
					'key'   => 'field_hp_cert_left_image',
					'label' => 'Ảnh Video trái (Dưới - Có nút Play)',
					'name'  => 'hp_cert_left_image',
					'type'  => 'image',
					'return_format' => 'url',
					'preview_size'  => 'medium',
					'instructions' => 'Chọn hoặc upload ảnh đại diện cho video.',
				],
				[
					'key'   => 'field_hp_cert_left_youtube',
					'label' => 'Link Video YouTube trái',
					'name'  => 'hp_cert_left_youtube',
					'type'  => 'text',
					'instructions' => 'Gắn link YouTube (ví dụ: https://www.youtube.com/watch?v=...). Khi click sẽ mở video dạng popup.',
					'placeholder'  => 'https://www.youtube.com/watch?v=...',
				],
				[
					'key'   => 'field_hp_cert_right_content',
					'label' => 'Nội dung bên phải (Dưới)',
					'name'  => 'hp_cert_right_content',
					'type'  => 'wysiwyg',
					'instructions' => 'Nhập nội dung văn bản hiển thị ở ô dưới bên phải.',
					'tabs'  => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
					'default_value' => '
<p><span style="color: #0284c7; font-weight: bold; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Bạn có thể sử dụng văn bằng để làm gì?</span></p>
<h2>VĂN BẰNG SAU TỐT NGHIỆP</h2>
<h3>Giá trị sử dụng của văn bằng</h3>
<p>Sau khi tốt nghiệp, người học được cấp văn bằng theo quy định hiện hành và có thể sử dụng để phục vụ các mục tiêu học tập, nghề nghiệp theo điều kiện của từng đơn vị tiếp nhận.</p>
<ul>
<li><strong>🎓 Học tiếp lên trình độ cao hơn</strong><br>Có thể đăng ký dự tuyển chương trình sau đại học khi đáp ứng điều kiện tuyển sinh.</li>
<li><strong>💼 Bổ sung và hoàn thiện hồ sơ nghề nghiệp</strong><br>Phục vụ yêu cầu về trình độ chuyên môn đối với vị trí việc làm phù hợp.</li>
<li><strong>📋 Tham gia tuyển dụng, thi tuyển</strong><br>Sử dụng văn bằng trong hồ sơ dự tuyển theo yêu cầu cụ thể của cơ quan, đơn vị tuyển dụng.</li>
</ul>
',
				],

				// ---- Section: Lợi ích ----
				[
					'key'       => 'field_hp_tab_benefits',
					'label'     => 'Lợi ích học viên',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				],
				[
					'key'   => 'field_hp_benefits_heading',
					'label' => 'Heading',
					'name'  => 'hp_benefits_heading',
					'type'  => 'text',
					'default_value' => 'Lợi ích dành cho bạn',
				],
				[
					'key'   => 'field_hp_benefits_desc',
					'label' => 'Mô tả',
					'name'  => 'hp_benefits_desc',
					'type'  => 'text',
					'default_value' => 'Chương trình tối ưu giúp người đi làm nâng cao bằng cấp dễ dàng.',
				],
				[
					'key'          => 'field_hp_benefits_items',
					'label'        => '4 lợi ích',
					'name'         => 'hp_benefits_items',
					'type'         => 'repeater',
					'max'          => 4,
					'layout'       => 'table',
					'button_label' => 'Thêm lợi ích',
					'sub_fields'   => [
						[ 'key' => 'field_hp_ben_icon', 'label' => 'Icon (emoji)', 'name' => 'icon', 'type' => 'text', 'default_value' => '📜' ],
						[ 'key' => 'field_hp_ben_title', 'label' => 'Tiêu đề', 'name' => 'title', 'type' => 'text' ],
						[ 'key' => 'field_hp_ben_desc', 'label' => 'Mô tả', 'name' => 'desc', 'type' => 'textarea', 'rows' => 2 ],
					],
				],

				// ---- Section: Vì sao chọn chúng tôi ----
				[
					'key'       => 'field_hp_tab_whyus',
					'label'     => 'Vì sao chọn chúng tôi',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				],
				[
					'key'   => 'field_hp_whyus_heading',
					'label' => 'Heading',
					'name'  => 'hp_whyus_heading',
					'type'  => 'text',
					'default_value' => 'Vì sao chọn chúng tôi?',
				],
				[
					'key'   => 'field_hp_whyus_desc',
					'label' => 'Mô tả',
					'name'  => 'hp_whyus_desc',
					'type'  => 'text',
					'default_value' => 'Đơn vị tư vấn và liên kết tuyển sinh hàng đầu.',
				],

				// ---- Section: Segments ----
				[
					'key'       => 'field_hp_tab_segments',
					'label'     => 'Đối tượng học viên',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				],
				[
					'key'   => 'field_hp_seg_heading',
					'label' => 'Heading',
					'name'  => 'hp_seg_heading',
					'type'  => 'text',
					'default_value' => 'Chương trình phù hợp với bạn?',
				],
				[
					'key'   => 'field_hp_seg_desc',
					'label' => 'Mô tả',
					'name'  => 'hp_seg_desc',
					'type'  => 'text',
					'default_value' => 'Chúng tôi thiết kế các lộ trình học tối ưu riêng cho từng nhóm đối tượng cụ thể.',
				],
				[
					'key'          => 'field_hp_seg_items',
					'label'        => '5 đối tượng',
					'name'         => 'hp_seg_items',
					'type'         => 'repeater',
					'max'          => 5,
					'layout'       => 'table',
					'button_label' => 'Thêm đối tượng',
					'sub_fields'   => [
						[ 'key' => 'field_hp_seg_img', 'label' => 'Ảnh', 'name' => 'image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'thumbnail' ],
						[ 'key' => 'field_hp_seg_title', 'label' => 'Tiêu đề', 'name' => 'title', 'type' => 'text' ],
						[ 'key' => 'field_hp_seg_desc', 'label' => 'Mô tả', 'name' => 'desc', 'type' => 'textarea', 'rows' => 2 ],
					],
				],
			],
			'location'  => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => LTDH_OPTIONS_PAGE ] ] ],
			'menu_order' => 4,
			'active'     => true,
		] );
	}
}
add_action( 'acf/init', 'ltdh_register_options_fields' );
