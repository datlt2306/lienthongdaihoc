<?php
/**
 * Default Values — Centralized fallbacks for all configurable data.
 *
 * Every helper that reads from ACF options or wp_options MUST fall back
 * to the values defined here so that the site works even before an admin
 * populates the settings.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return default values grouped by context.
 *
 * @param string $group One of: contact, navigation, homepage, forms, seo, images.
 * @return array<string,mixed>
 */
function ltdh_get_defaults( string $group ): array {
	$defaults = [
		'contact' => [
			'hotline'    => '0389 198 653',
			'zalo_url'   => 'https://zalo.me',
			'messenger_url' => 'https://m.me',
			'email'      => 'tuyensinh@lienthongdaihoc.com',
			'address'    => '123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội',
			'company_name' => 'Cổng thông tin Tuyển sinh liên kết Đại học',
		],
		'navigation' => [
			'primary' => [
				[ 'url' => '/',                   'label' => 'Trang chủ' ],
				[ 'url' => '/truong-lien-ket/',   'label' => 'Trường liên kết' ],
				[ 'url' => '/nganh-hoc/',         'label' => 'Ngành học' ],
				[ 'url' => '/he-dao-tao/',        'label' => 'Hệ đào tạo' ],
				[ 'url' => '/tin-tuyen-sinh/',    'label' => 'Tin tức' ],
				[ 'url' => '/lien-he/',           'label' => 'Liên hệ' ],
			],
			'mobile' => [
				[ 'url' => '/',                   'label' => 'Trang chủ' ],
				[ 'url' => '/truong-lien-ket/',   'label' => 'Trường liên kết' ],
				[ 'url' => '/nganh-hoc/',         'label' => 'Ngành học' ],
				[ 'url' => '/he-dao-tao/tu-xa/',  'label' => 'Chương trình' ],
				[ 'url' => '/tin-tuyen-sinh/',    'label' => 'Tin tức' ],
				[ 'url' => '/kiem-tra-dieu-kien/', 'label' => 'Kiểm tra điều kiện' ],
				[ 'url' => '/lien-he/',           'label' => 'Liên hệ' ],
			],
			'footer' => [
				[ 'url' => '/gioi-thieu/',            'label' => 'Giới thiệu' ],
				[ 'url' => '/cau-hoi-thuong-gap/',   'label' => 'Câu hỏi thường gặp' ],
				[ 'url' => '/chinh-sach-bao-mat/',   'label' => 'Chính sách bảo mật' ],
				[ 'url' => '/lien-he/',              'label' => 'Liên hệ' ],
			],
		],
		'forms' => [
			'consultation_shortcode' => '',
			'contact_shortcode'      => '',
			'program_shortcode'      => '',
		],
		'homepage' => [
			'hero_year_label' => 'TUYỂN SINH ' . date( 'Y' ),
			'hero_badges'     => [
				[ 'text' => '50+ chương trình',          'subtext' => 'Liên thông, VB2, Từ xa' ],
				[ 'text' => '30+ trường ĐH',              'subtext' => 'Đối tác uy tín toàn quốc' ],
				[ 'text' => 'Miễn giảm tín chỉ',          'subtext' => 'Rút ngắn thời gian học' ],
			],
			'kpi_metrics' => [
				[ 'value' => '1.200+', 'label' => 'Học viên' ],
				[ 'value' => '98%',    'label' => 'Hài lòng' ],
				[ 'value' => '30+',    'label' => 'Đối tác ĐH' ],
				[ 'value' => '10+',    'label' => 'Năm uy tín' ],
			],
		],
		'seo' => [
			'year_pattern' => '%s | Tuyển sinh %d',
		],
		'images' => [
			'fallback_school'      => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=600',
			'fallback_school_logo' => 'https://images.unsplash.com/photo-1594788094620-4579ad50c7fe?auto=format&fit=crop&q=80&w=150',
			'fallback_program'     => 'https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=300',
			'fallback_post'        => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=400',
			'fallback_news'        => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=250',
			'fallback_hero'        => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=600',
			'fallback_consult'     => 'https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=400',
			'fallback_segment_1'   => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&q=80&w=200',
			'fallback_segment_2'   => 'https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?auto=format&fit=crop&q=80&w=200',
			'fallback_segment_3'   => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&q=80&w=200',
			'fallback_segment_4'   => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&q=80&w=200',
			'fallback_segment_5'   => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=200',
			'fallback_school_covers' => [
				'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&q=80&w=300',
				'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=300',
				'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&q=80&w=300',
				'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&q=80&w=300',
				'https://images.unsplash.com/photo-1519452635265-7b1fbfd1e4e0?auto=format&fit=crop&q=80&w=300',
			],
		],
		'synonyms' => [
			'cntt' => 'công nghệ thông tin',
			'qtkd' => 'quản trị kinh doanh',
			'kdtm' => 'kinh doanh thương mại',
			'nna'  => 'ngôn ngữ anh',
		],
	];

	return $defaults[ $group ] ?? [];
}

/**
 * Get a single default value.
 *
 * @param string $group
 * @param string $key
 * @param mixed  $fallback
 * @return mixed
 */
function ltdh_default( string $group, string $key, $fallback = '' ) {
	$defaults = ltdh_get_defaults( $group );
	return $defaults[ $key ] ?? $fallback;
}
