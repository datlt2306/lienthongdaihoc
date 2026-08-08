<?php
/**
 * Custom WP-CLI Commands
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * CLI tools for lienthongdaihoc.com multi-university portal
 */
class LTDH_CLI_Commands {

	/**
	 * Setup required core pages, settings, and navigation menus.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ltdh setup-system
	 */
	public function setup_system( $args, $assoc_args ) {
		WP_CLI::log( 'Đang thiết lập các trang cốt lõi...' );

		$required_pages = [
			'gioi-thieu'            => [ 'title' => 'Giới thiệu', 'template' => 'page-about.php' ],
			'lien-he'               => [ 'title' => 'Liên hệ', 'template' => 'page-contact.php' ],
			'dang-ky-tu-van'        => [ 'title' => 'Đăng ký tư vấn', 'template' => 'page-register.php' ],
			'faq'                   => [ 'title' => 'Câu hỏi thường gặp', 'template' => 'page-faq.php' ],
			'kiem-tra-dieu-kien'    => [ 'title' => 'Kiểm tra điều kiện', 'template' => 'page-eligible.php' ],
			'dieu-khoan'            => [ 'title' => 'Điều khoản dịch vụ', 'template' => '' ],
			'chinh-sach-bao-mat'    => [ 'title' => 'Chính sách bảo mật', 'template' => '' ],
		];

		foreach ( $required_pages as $slug => $meta ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				$post_id = wp_insert_post( [
					'post_title'   => $meta['title'],
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_type'    => 'page',
				] );
				if ( ! is_wp_error( $post_id ) ) {
					if ( ! empty( $meta['template'] ) ) {
						update_post_meta( $post_id, '_wp_page_template', $meta['template'] );
					}
					WP_CLI::success( sprintf( 'Đã tạo trang: %s', $meta['title'] ) );
				}
			} else {
				WP_CLI::line( sprintf( 'Trang đã tồn tại: %s', $meta['title'] ) );
			}
		}

		// Ensure primary and footer menus are created
		$primary_menu = wp_get_nav_menu_object( 'Header Navigation Menu' );
		if ( ! $primary_menu ) {
			$menu_id = wp_create_nav_menu( 'Header Navigation Menu' );
			$locations = get_theme_mod( 'nav_menu_locations' );
			$locations['primary-menu'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
			WP_CLI::success( 'Đã khởi tạo Header Navigation Menu.' );
		}

		$footer_menu = wp_get_nav_menu_object( 'Footer Navigation Menu' );
		if ( ! $footer_menu ) {
			$menu_id = wp_create_nav_menu( 'Footer Navigation Menu' );
			$locations = get_theme_mod( 'nav_menu_locations' );
			$locations['footer-menu'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
			WP_CLI::success( 'Đã khởi tạo Footer Navigation Menu.' );
		}

		// Manually invoke leads table creation
		if ( function_exists( 'ltdh_create_leads_table' ) ) {
			ltdh_create_leads_table();
			WP_CLI::success( 'Đã thiết lập bảng lead cơ sở.' );
		}

		WP_CLI::success( 'Thiết lập hệ thống hoàn tất!' );
	}

	/**
	 * Seed custom taxonomy terms (campuses, regions, training types).
	 *
	 * ## EXAMPLES
	 *
	 *     wp ltdh create-taxonomies
	 */
	public function create_taxonomies( $args, $assoc_args ) {
		WP_CLI::log( 'Đang tạo danh mục mẫu...' );

		// 1. Training Types
		$types = [
			'Văn bằng 2'       => 'van-bang-2',
			'Từ xa'            => 'tu-xa',
			'Vừa học vừa làm'  => 'vua-hoc-vua-lam',
			'Chính quy'        => 'chinh-quy',
		];
		foreach ( $types as $name => $slug ) {
			if ( ! term_exists( $slug, 'training_type' ) ) {
				wp_insert_term( $name, 'training_type', [ 'slug' => $slug ] );
			}
		}
		WP_CLI::success( 'Đã gieo mẫu taxonomy: Hệ đào tạo' );

		// 2. Campuses
		$campuses = [
			'Hà Nội'     => 'ha-noi',
			'Hồ Chí Minh'=> 'ho-chi-minh',
			'Đà Nẵng'    => 'da-nang',
			'Thái Nguyên'=> 'thai-nguyen',
			'Online'     => 'online',
		];
		foreach ( $campuses as $name => $slug ) {
			if ( ! term_exists( $slug, 'campus' ) ) {
				wp_insert_term( $name, 'campus', [ 'slug' => $slug ] );
			}
		}
		WP_CLI::success( 'Đã gieo mẫu taxonomy: Cơ sở' );

		// 3. Regions
		$regions = [
			'Miền Bắc' => 'mien-bac',
			'Miền Trung'=> 'mien-trung',
			'Miền Nam' => 'mien-nam',
		];
		foreach ( $regions as $name => $slug ) {
			if ( ! term_exists( $slug, 'region' ) ) {
				wp_insert_term( $name, 'region', [ 'slug' => $slug ] );
			}
		}
		WP_CLI::success( 'Đã gieo mẫu taxonomy: Khu vực' );

		// 4. Nhóm ngành (major_cat)
		$major_cats = [
			'Kinh tế - Quản lý'    => 'kinh-te-quan-ly',
			'Kỹ thuật - Công nghệ' => 'ky-thuat-cong-nghe',
			'Ngôn ngữ - Nhân văn'  => 'ngon-ngu-nhan-van',
			'Nông lâm - Môi trường'=> 'nong-lam-moi-truong',
			'Xã hội - Dịch vụ'     => 'xa-hoi-dich-vu',
		];
		foreach ( $major_cats as $name => $slug ) {
			if ( ! term_exists( $slug, 'major_cat' ) ) {
				wp_insert_term( $name, 'major_cat', [ 'slug' => $slug ] );
			}
		}
		WP_CLI::success( 'Đã gieo mẫu taxonomy: Nhóm ngành' );

		WP_CLI::success( 'Hoàn thành việc tạo danh mục.' );
	}

	/**
	 * Seed sample database for schools, majors, programs, and guides.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ltdh seed-data
	 */
	public function seed_data( $args, $assoc_args ) {
		WP_CLI::log( 'Đang xóa các dữ liệu cũ...' );

		// 0. Clear existing seed data first
		$existing_schools = get_posts( [ 'post_type' => 'school', 'numberposts' => -1, 'post_status' => 'any' ] );
		foreach ( $existing_schools as $p ) {
			wp_delete_post( $p->ID, true );
		}

		$existing_majors = get_posts( [ 'post_type' => 'major', 'numberposts' => -1, 'post_status' => 'any' ] );
		foreach ( $existing_majors as $p ) {
			wp_delete_post( $p->ID, true );
		}

		$existing_programs = get_posts( [ 'post_type' => 'program', 'numberposts' => -1, 'post_status' => 'any' ] );
		foreach ( $existing_programs as $p ) {
			wp_delete_post( $p->ID, true );
		}

		WP_CLI::log( 'Bắt đầu gieo dữ liệu thực tế...' );

		// Make sure taxonomies exist
		$this->create_taxonomies([], []);

		// 1. Seed Real Schools from schools_import.json
		$schools_import_file = get_template_directory() . '/schools_import.json';
		$schools_data = [];
		if ( file_exists( $schools_import_file ) ) {
			$json_content = file_get_contents( $schools_import_file );
			$parsed_schools = json_decode( $json_content, true );
			if ( is_array( $parsed_schools ) ) {
				foreach ( $parsed_schools as $s ) {
				$schools_data[ $s['title'] ] = [
					'code'           => $s['code'],
					'web'            => $s['web'],
					'addr'           => $s['addr'],
					'phone'          => $s['phone'],
					'region'         => $s['region'],
					'img'            => ltdh_get_fallback_image( 'school' ),
					'logo'           => ltdh_get_fallback_image( 'school_logo' ),
					'en'             => $s['code'] . ' University',
					'rating'         => '4.8',
					'reviews'        => '120',
					'target'         => '2.000',
					'admission_info' => $s['admission_info'],
					'contact_info'   => $s['contact_info'],
					'majors'         => isset( $s['majors'] ) ? $s['majors'] : [],
				];
				}
			}
		}

		if ( empty( $schools_data ) ) {
			WP_CLI::error( 'Không tìm thấy file schools_import.json hoặc file trống!' );
			return;
		}

		$school_ids = [];
		foreach ( $schools_data as $title => $meta ) {
			$school = get_page_by_path( sanitize_title( $title ), OBJECT, 'school' );
			if ( ! $school ) {
				$post_id = wp_insert_post( [
					'post_title'   => $title,
					'post_name'    => sanitize_title( $title ),
					'post_status'  => 'publish',
					'post_type'    => 'school',
					'post_content' => 'Giới thiệu tổng quan về trường đại học liên kết tuyển sinh chính quy chất lượng cao.',
				] );
				if ( ! is_wp_error( $post_id ) ) {
					update_post_meta( $post_id, 'school_code', $meta['code'] );
					update_post_meta( $post_id, 'website', $meta['web'] );
					update_post_meta( $post_id, 'address', $meta['addr'] );
					update_post_meta( $post_id, 'hotline', '0338 615 497' );
					update_post_meta( $post_id, 'english_name', $meta['en'] );
					update_post_meta( $post_id, 'rating', $meta['rating'] );
					update_post_meta( $post_id, 'reviews_count', $meta['reviews'] );
					update_post_meta( $post_id, 'admission_target', $meta['target'] );
					update_post_meta( $post_id, 'admission_info', isset( $meta['admission_info'] ) ? $meta['admission_info'] : '' );
					update_post_meta( $post_id, 'contact_info', isset( $meta['contact_info'] ) ? $meta['contact_info'] : '' );
					wp_set_object_terms( $post_id, $meta['region'], 'region' );
					$this->maybe_set_school_thumbnail( $post_id, $title, $meta['img'], $meta['logo'] );
					$school_ids[] = $post_id;
					WP_CLI::success( "Đã tạo Trường: $title" );
				}
			} else {
				update_post_meta( $school->ID, 'school_code', $meta['code'] );
				update_post_meta( $school->ID, 'hotline', '0338 615 497' );
				update_post_meta( $school->ID, 'english_name', $meta['en'] );
				update_post_meta( $school->ID, 'rating', $meta['rating'] );
				update_post_meta( $school->ID, 'reviews_count', $meta['reviews'] );
				update_post_meta( $school->ID, 'admission_target', $meta['target'] );
				update_post_meta( $school->ID, 'admission_info', isset( $meta['admission_info'] ) ? $meta['admission_info'] : '' );
				update_post_meta( $school->ID, 'contact_info', isset( $meta['contact_info'] ) ? $meta['contact_info'] : '' );
				$this->maybe_set_school_thumbnail( $school->ID, $title, $meta['img'], $meta['logo'] );
				$school_ids[] = $school->ID;
				WP_CLI::line( "Trường đã tồn tại: $title" );
			}
		}

		// 2. Loop through schools and dynamically create/update majors and programs
		$variants = [
			// Ngôn ngữ Anh variants
			7220201 => [
				'Bách Khoa' => [
					'tuition' => '650.000đ / tín chỉ', 'duration' => '2.5 năm',
					'schedule' => 'Thứ 7 & CN hàng tuần', 'target' => 'Sinh viên năm 3, người đi làm muốn nâng cao trình độ',
					'degree' => 'Cử nhân Ngôn ngữ Anh', 'diploma' => 'Đào tạo theo chuẩn B2 CEFR, có giá trị quốc tế',
					'advantages' => 'Chương trình tích hợp chứng chỉ IELTS, có thực tập tại doanh nghiệp nước ngoài',
					'disadvantages' => 'Học phí cao hơn so với các trường khác, lịch học cố định cuối tuần',
				],
				'Kinh tế' => [
					'tuition' => '580.000đ / tín chỉ', 'duration' => '2 năm',
					'schedule' => 'Tối thứ 2, 4, 6', 'target' => 'Người đi làm, văn bằng 2',
					'degree' => 'Cử nhân Ngôn ngữ Anh', 'diploma' => 'Bằng chính quy, có giá trị toàn quốc',
					'advantages' => 'Học phí hợp lý, lịch học linh hoạt cho người đi làm',
					'disadvantages' => 'Không có chứng chỉ quốc tế đi kèm, số lượng lớp hạn chế',
				],
			],
			// Kế toán variants
			7340301 => [
				'Bách Khoa' => [
					'tuition' => '550.000đ / tín chỉ', 'duration' => '2 năm',
					'schedule' => 'Thứ 7 hàng tuần', 'target' => 'Người có bằng Cao đẳng muốn liên thông',
					'degree' => 'Cử nhân Kế toán', 'diploma' => 'Bằng chính quy, được Bộ GD&ĐT công nhận',
					'advantages' => 'Miễn giảm 30% tín chỉ cho người liên thông, học phí thấp',
					'disadvantages' => 'Chỉ học 1 ngày/tuần, tiến độ chậm',
				],
				'Tài chính' => [
					'tuition' => '620.000đ / tín chỉ', 'duration' => '2.5 năm',
					'schedule' => 'Tối thứ 2, 4', 'target' => 'Kế toán viên muốn nâng cao chứng chỉ',
					'degree' => 'Cử nhân Kế toán', 'diploma' => 'Bằng có giá trị quốc tế, được ACCA công nhận',
					'advantages' => 'Được miễn 4 môn thi chứng chỉ ACCA, networking với chuyên gia tài chính',
					'disadvantages' => 'Học phí cao hơn, lịch học yêu cầu cam kết đều đặn',
				],
			],
		];

		$campus_slugs   = ['ha-noi', 'ho-chi-minh', 'online'];
		$training_slugs = ['van-bang-2', 'tu-xa', 'chinh-quy'];
		$program_index = 0;

		foreach ( $school_ids as $school_id ) {
			$school_title = get_the_title( $school_id );
			$school_code = get_post_meta( $school_id, 'school_code', true );

			// Find matching school metadata to get its majors
			$school_meta = null;
			foreach ( $schools_data as $title => $meta ) {
				if ( $meta['code'] === $school_code ) {
					$school_meta = $meta;
					break;
				}
			}

			$school_majors = isset( $school_meta['majors'] ) ? $school_meta['majors'] : [ 'Kế toán', 'Quản trị kinh doanh', 'Ngôn ngữ Anh' ];

			foreach ( $school_majors as $m_name ) {
				$m_name = trim( $m_name );
				if ( empty( $m_name ) ) {
					continue;
				}

				// Find or create major
				$major = get_page_by_path( sanitize_title( $m_name ), OBJECT, 'major' );
				if ( ! $major ) {
				$major_id = wp_insert_post( [
					'post_title'   => $m_name,
					'post_name'    => sanitize_title( $m_name ),
					'post_status'  => 'publish',
					'post_type'    => LTDH_CPT_MAJOR,
					'post_content' => 'Ngành đào tạo tiềm năng đón đầu xu hướng việc làm công nghệ cao.',
				] );
				if ( ! is_wp_error( $major_id ) ) {
					update_post_meta( $major_id, 'major_code', '7' . str_pad( rand( 100000, 999999 ), 6, '0', STR_PAD_LEFT ) );
					update_post_meta( $major_id, 'career_opportunities', 'Cơ hội nghề nghiệp rộng mở tại các doanh nghiệp lớn.' );
					update_post_meta( $major_id, 'admission_groups', 'A00, A01, D01' );
					update_post_meta( $major_id, LTDH_META_ADMISSION_STATUS, LTDH_STATUS_OPEN );
						WP_CLI::success( "Đã tạo Ngành học: $m_name" );
					}
				} else {
					$major_id = $major->ID;
				}

				if ( ! $major_id ) {
					continue;
				}

				// Determine Nhóm ngành category
				$cat_slug = 'xa-hoi-dich-vu'; // Fallback
				$m_lower = mb_strtolower( $m_name, 'UTF-8' );
				if ( str_contains( $m_lower, 'kinh tế' ) || str_contains( $m_lower, 'quản trị' ) || str_contains( $m_lower, 'kế toán' ) || str_contains( $m_lower, 'thương mại' ) || str_contains( $m_lower, 'marketing' ) || str_contains( $m_lower, 'tài chính' ) || str_contains( $m_lower, 'bất động sản' ) || str_contains( $m_lower, 'quản lý công nghiệp' ) ) {
					$cat_slug = 'kinh-te-quan-ly';
				} elseif ( str_contains( $m_lower, 'công nghệ thông tin' ) || str_contains( $m_lower, 'điện tử' ) || str_contains( $m_lower, 'xây dựng' ) || str_contains( $m_lower, 'máy tính' ) || str_contains( $m_lower, 'tin học' ) || str_contains( $m_lower, 'viễn thông' ) || str_contains( $m_lower, 'cntt' ) ) {
					$cat_slug = 'ky-thuat-cong-nghe';
				} elseif ( str_contains( $m_lower, 'ngôn ngữ' ) || str_contains( $m_lower, 'tiếng anh' ) || str_contains( $m_lower, 'tiếng trung' ) || str_contains( $m_lower, 'nhân văn' ) ) {
					$cat_slug = 'ngon-ngu-nhan-van';
				} elseif ( str_contains( $m_lower, 'nông nghiệp' ) || str_contains( $m_lower, 'thực phẩm' ) || str_contains( $m_lower, 'môi trường' ) || str_contains( $m_lower, 'đất đai' ) || str_contains( $m_lower, 'thú y' ) || str_contains( $m_lower, 'sinh học' ) || str_contains( $m_lower, 'nông lâm' ) ) {
					$cat_slug = 'nong-lam-moi-truong';
				}

				wp_set_object_terms( $major_id, $cat_slug, 'major_cat' );

				$major_title = get_the_title( $major_id );
				$major_code = get_post_meta( $major_id, 'major_code', true );

				// Get variant tuition/details or fallback
				$base_tuition = 400000 + ( $program_index * 5000 );
				$variant = [
					'tuition' => number_format( $base_tuition ) . 'đ / tín chỉ',
					'duration' => '1.5 - 3 năm',
					'schedule' => 'Lịch học linh hoạt',
					'target' => 'Người đi làm, văn bằng 2',
					'degree' => 'Cử nhân ' . $major_title,
					'diploma' => 'Bằng chính quy, được Bộ GD&ĐT công nhận',
					'advantages' => 'Học phí hợp lý, thời gian học linh hoạt',
					'disadvantages' => 'Chưa có đánh giá chi tiết',
				];

				// If we have custom variant maps based on codes, we can use them:
				if ( isset( $variants[ $major_code ] ) ) {
					foreach ( $variants[ $major_code ] as $key => $v ) {
						if ( mb_stripos( $school_title, $key ) !== false ) {
							$variant = $v;
							break;
						}
					}
				}

				if ( mb_stripos( $school_title, 'Giao thông' ) !== false ) {
					$t_slug = $training_slugs[ $program_index % count( $training_slugs ) ];
				} else {
					$t_slug = 'tu-xa';
				}
				$c_slug = $campus_slugs[ $program_index % count( $campus_slugs ) ];
				$t_names = [ 'van-bang-2' => 'Văn bằng 2', 'tu-xa' => 'Từ xa', 'chinh-quy' => 'Chính quy' ];
				$t_name = isset( $t_names[ $t_slug ] ) ? $t_names[ $t_slug ] : 'Từ xa';

				$program_title = "Cử nhân $major_title";
				$enrollment = 'Tuyển sinh quanh năm';

				$existing = get_posts( [
					'post_type'  => 'program',
					'title'      => $program_title,
					'meta_query' => [
						[ 'key' => 'school_relationship', 'value' => $school_id, 'compare' => '=' ],
					],
					'tax_query'  => [
						[ 'taxonomy' => 'training_type', 'field' => 'slug', 'terms' => $t_slug ],
					],
					'posts_per_page' => 1,
				] );

				if ( ! empty( $existing ) ) {
					$program_index++;
					continue;
				}

				$program_id = wp_insert_post( [
					'post_title'   => $program_title,
					'post_name'    => sanitize_title( $program_title . '-' . $t_slug . '-' . $school_title ),
					'post_status'  => 'publish',
					'post_type'    => LTDH_CPT_PROGRAM,
					'post_content' => "Chương trình đào tạo Cử nhân ngành $major_title hệ $t_name của $school_title. " . $variant['advantages'] . ".",
				] );

				if ( is_wp_error( $program_id ) ) {
					$program_index++;
					continue;
				}

				update_post_meta( $program_id, LTDH_META_SCHOOL_REL, $school_id );
				update_post_meta( $program_id, LTDH_META_MAJOR_REL, $major_id );
				update_post_meta( $program_id, LTDH_META_TUITION, $variant['tuition'] );
				update_post_meta( $program_id, LTDH_META_DURATION, $variant['duration'] );
				update_post_meta( $program_id, 'campus_info', $c_slug === 'ha-noi' ? 'Hà Nội' : ( $c_slug === 'ho-chi-minh' ? 'TP. Hồ Chí Minh' : 'Online' ) );
				update_post_meta( $program_id, 'admission_requirements', 'Xét tuyển hồ sơ văn bằng đã có (THPT, Trung cấp, Cao đẳng).' );
				update_post_meta( $program_id, 'required_documents', 'CCCD, Ảnh 3x4, Phiếu tuyển sinh, Bản sao công chứng Bằng tốt nghiệp.' );
				update_post_meta( $program_id, 'admission_form_file', 'https://lienthongdaihoc.vn/phieu-dang-ky-tuyen-sinh-utc-2026.pdf' );
				update_post_meta( $program_id, 'enrollment_period', $enrollment );
				update_post_meta( $program_id, 'program_benefits', $variant['advantages'] );
				update_post_meta( $program_id, LTDH_META_SCHEDULE, $variant['schedule'] );
				update_post_meta( $program_id, 'target_students', $variant['target'] );
				update_post_meta( $program_id, 'degree_type', $variant['degree'] );
				update_post_meta( $program_id, 'diploma_value', $variant['diploma'] );
				update_post_meta( $program_id, 'disadvantages', $variant['disadvantages'] );

				$parent_status = get_post_meta( $major_id, LTDH_META_ADMISSION_STATUS, true ) ?: LTDH_STATUS_OPEN;
				$parent_groups = get_post_meta( $major_id, LTDH_META_AD_GROUPS, true ) ?: 'A00, A01, D01';
				update_post_meta( $program_id, LTDH_META_ADMISSION_STATUS, $parent_status );
				update_post_meta( $program_id, LTDH_META_AD_GROUPS, $parent_groups );

				$faqs = [
					[ 'question' => 'Có cần phải đến trường học trực tiếp không?', 'answer' => $t_slug === 'tu-xa' ? 'Đối với hệ Từ xa, bạn học 100% qua E-Learning và không cần đến trường học hay điểm danh.' : 'Bạn cần đến học trực tiếp theo lịch học.' ],
					[ 'question' => 'Bằng tốt nghiệp có giá trị chính quy không?', 'answer' => 'Có. Bằng do Hiệu trưởng trường Đại học cấp và được bộ Giáo dục công nhận, có giá trị học lên Thạc sĩ/Tiến sĩ.' ]
				];
				update_post_meta( $program_id, 'faq', $faqs );

				// Set eligibility criteria.
				$elig_min_edu = '';
				if ( $t_slug === 'lien-thong' ) {
					$elig_min_edu = 'trung-cap';
				} elseif ( $t_slug === 'van-bang-2' ) {
					$elig_min_edu = 'dai-hoc';
				} else {
					$elig_min_edu = 'thap-phan';
				}
				update_post_meta( $program_id, 'elig_min_education', $elig_min_edu );
				update_post_meta( $program_id, 'elig_training_types', [ $t_slug ] );
				update_post_meta( $program_id, 'elig_campuses', [ $c_slug ] );
				update_post_meta( $program_id, 'elig_max_grad_years', $t_slug === 'lien-thong' ? 10 : 99 );
				update_post_meta( $program_id, 'elig_notes', '' );

				wp_set_object_terms( $program_id, $t_slug, LTDH_TAX_TRAINING_TYPE );
				wp_set_object_terms( $program_id, $c_slug, LTDH_TAX_CAMPUS );

				if ( function_exists( 'ltdh_sync_program_relationships' ) ) {
					ltdh_sync_program_relationships( $program_id );
				}

				$program_index++;
			}
		}

		// 4. Seed 4 Tin tức / Hướng dẫn tuyển sinh (as regular posts)
		$cat_guide = wp_create_category( 'Hướng dẫn tuyển sinh' );
		$cat_news  = wp_create_category( 'Tin tuyển sinh' );
		$cat_ann   = wp_create_category( 'Thông báo' );

		// Get schools for assignment
		$all_schools = get_posts( [ 'post_type' => 'school', 'numberposts' => -1, 'post_status' => 'publish' ] );
		$school_ids = wp_list_pluck( $all_schools, 'ID' );

				$guides_data = [
					[
						'title' => 'Học Đại học từ xa là gì?',
						'desc'  => 'Tìm hiểu hình thức đào tạo E-learning trực tuyến, xu hướng phát triển giáo dục đại học cho người đi làm.',
						'cat'   => $cat_guide,
						'img'   => ltdh_get_fallback_image( 'post' ),
						'school' => ! empty( $school_ids ) ? $school_ids[ array_rand( $school_ids ) ] : 0,
					],
					[
						'title' => 'Tuyển sinh Liên thông Cao đẳng lên Đại học năm 2026',
						'desc'  => 'Xét tuyển hồ sơ và miễn giảm tín chỉ đối với sinh viên có bằng tốt nghiệp Trung cấp/Cao đẳng chuyển tiếp.',
						'cat'   => $cat_news,
						'img'   => ltdh_get_fallback_image( 'post' ),
						'school' => ! empty( $school_ids ) ? $school_ids[ array_rand( $school_ids ) ] : 0,
					],
					[
						'title' => 'Thông báo tuyển sinh Đại học từ xa đợt 1 năm 2026',
						'desc'  => 'Thông tin chi tiết các ngành đào tạo tuyển sinh đại học trực tuyến và văn bằng 2 đợt đầu năm.',
						'cat'   => $cat_ann,
						'img'   => ltdh_get_fallback_image( 'post' ),
						'school' => ! empty( $school_ids ) ? $school_ids[ array_rand( $school_ids ) ] : 0,
					],
					[
						'title' => 'Quy định miễn giảm tín chỉ khi học văn bằng 2',
						'desc'  => 'Học viên có thể rút ngắn đến 50% thời gian học nếu đối chiếu bảng điểm môn học tương đồng.',
						'cat'   => $cat_guide,
						'img'   => ltdh_get_fallback_image( 'post' ),
						'school' => ! empty( $school_ids ) ? $school_ids[ array_rand( $school_ids ) ] : 0,
					],
				];

		foreach ( $guides_data as $g ) {
			$existing = get_page_by_path( sanitize_title( $g['title'] ), OBJECT, 'post' );
			if ( ! $existing ) {
				$post_id = wp_insert_post( [
					'post_title'   => $g['title'],
					'post_name'    => sanitize_title( $g['title'] ),
					'post_status'  => 'publish',
					'post_type'    => 'post',
					'post_content' => $g['desc'],
				] );
				if ( ! is_wp_error( $post_id ) ) {
					wp_set_post_categories( $post_id, [ $g['cat'] ] );
					
					// Assign school relationship
					if ( ! empty( $g['school'] ) ) {
						update_field( 'school_relationship', $g['school'], $post_id );
					}
					
					// Sideload thumbnail for post
					require_once ABSPATH . 'wp-admin/includes/media.php';
					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';
					$attach_id = $this->sideload_image_from_url( $g['img'], $post_id, $g['title'] );
					if ( ! is_wp_error( $attach_id ) ) {
						set_post_thumbnail( $post_id, (int) $attach_id );
					}
					
					WP_CLI::success( "Đã tạo Bài viết: " . $g['title'] );
				}
			}
		}

		WP_CLI::success( 'Đồng bộ hóa gieo seeder hoàn tất!' );
	}

	/**
	 * Attach cover photo and logo crest to a school CPT.
	 */
	private function maybe_set_school_thumbnail( $school_id, $title, $image_url, $logo_url ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Set Cover thumbnail
		if ( ! has_post_thumbnail( $school_id ) ) {
			$attachment_id = $this->sideload_image_from_url( $image_url, $school_id, $title . ' Cover' );
			if ( ! is_wp_error( $attachment_id ) ) {
				set_post_thumbnail( $school_id, (int) $attachment_id );
				WP_CLI::line( sprintf( 'Đã gán campus cover cho: %s', $title ) );
			}
		}

		// Set Logo crest
		if ( ! get_field( 'logo', $school_id ) ) {
			$attachment_id = $this->sideload_image_from_url( $logo_url, $school_id, $title . ' Logo' );
			if ( ! is_wp_error( $attachment_id ) ) {
				update_field( 'logo', (int) $attachment_id, $school_id );
				WP_CLI::line( sprintf( 'Đã gán logo crest cho: %s', $title ) );
			}
		}
	}

	/**
	 * Sideload remote image when URL has no file extension (e.g. Unsplash).
	 */
	private function sideload_image_from_url( $image_url, $post_id, $title ) {
		$tmp_file = download_url( esc_url_raw( $image_url ) );
		if ( is_wp_error( $tmp_file ) ) {
			return $tmp_file;
		}

		$file_array = [
			'name'     => sanitize_file_name( sanitize_title( $title ) ) . '.jpg',
			'tmp_name' => $tmp_file,
		];

		$attachment_id = media_handle_sideload( $file_array, $post_id, $title );

		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp_file );
		}

		return $attachment_id;
	}

	/**
	 * Run lead queue synchronization manually.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ltdh sync-leads
	 */
	public function sync_leads( $args, $assoc_args ) {
		WP_CLI::log( 'Đang bắt đầu đồng bộ hóa lead queue sang CRM...' );
		if ( function_exists( 'ltdh_process_lead_queue' ) ) {
			ltdh_process_lead_queue();
			WP_CLI::success( 'Đồng bộ hóa hoàn thành!' );
		} else {
			WP_CLI::error( 'Không tìm thấy hàm ltdh_process_lead_queue. Kiểm tra xem crm-adapters đã được kích hoạt chưa.' );
		}
	}

	/**
	 * Seed UTC (Đại học Giao thông Vận tải) with Information Technology major for 3 training types.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ltdh seed-utc
	 */
	public function seed_utc( $args, $assoc_args ) {
		WP_CLI::log( 'Bắt đầu gieo mẫu dữ liệu cho Trường Đại học Giao thông Vận tải (UTC)...' );

		// Make sure taxonomies exist
		$this->create_taxonomies([], []);

		// 1. Seed/Get UTC School
		$school_title = 'Trường Đại học Giao thông Vận tải';
		$school_slug = sanitize_title( $school_title );
		$school = get_page_by_path( $school_slug, OBJECT, 'school' );
		
		if ( ! $school ) {
			$school_id = wp_insert_post( [
				'post_title'   => $school_title,
				'post_name'    => $school_slug,
				'post_status'  => 'publish',
				'post_type'    => 'school',
				'post_content' => 'Trường Đại học Giao thông Vận tải (UTC) là một trong những trường đại học kỹ thuật đa ngành hàng đầu Việt Nam, có bề dày truyền thống đào tạo cán bộ kỹ thuật cho ngành giao thông vận tải và các ngành kinh tế khác.',
			] );
			if ( is_wp_error( $school_id ) ) {
				WP_CLI::error( 'Lỗi khi tạo Trường Đại học Giao thông Vận tải: ' . $school_id->get_error_message() );
				return;
			}
			WP_CLI::success( "Đã tạo Trường: $school_title" );
		} else {
			$school_id = $school->ID;
			WP_CLI::line( "Trường đã tồn tại: $school_title" );
		}

		// Update School Meta
		update_post_meta( $school_id, 'school_code', 'UTC' );
		update_post_meta( $school_id, 'website', 'https://www.utc.edu.vn' );
		update_post_meta( $school_id, 'address', 'Số 3 Cầu Giấy, Láng Thượng, Đống Đa, Hà Nội' );
		update_post_meta( $school_id, 'hotline', '0338 615 497' );
		update_post_meta( $school_id, 'english_name', 'University of Transport and Communications' );
		update_post_meta( $school_id, 'rating', '4.7' );
		update_post_meta( $school_id, 'reviews_count', '98' );
		update_post_meta( $school_id, 'admission_target', '1.500' );
		update_post_meta( $school_id, 'admission_info', '<h4>Học phí & Lệ phí</h4><ul><li><strong>Mức học phí tham khảo:</strong> 420.000 - 684.000 đ/tín chỉ (tùy ngành & hệ)</li><li><strong>Lệ phí xét tuyển:</strong> 200.000đ/hồ sơ</li></ul>' );
		update_post_meta( $school_id, 'contact_info', '<h4>Địa điểm khai giảng & thi</h4><p>Phòng Khảo thí và Đảm bảo chất lượng đào tạo - Trường Đại học Giao thông vận tải, Số 3 Cầu Giấy, Láng Thượng, Đống Đa, Hà Nội.</p>' );
		wp_set_object_terms( $school_id, 'mien-bac', 'region' );

		// 2. Seed/Get IT Major
		$major_title = 'Công nghệ thông tin';
		$major_slug = sanitize_title( $major_title );
		$major = get_page_by_path( $major_slug, OBJECT, LTDH_CPT_MAJOR );

		if ( ! $major ) {
			$major_id = wp_insert_post( [
				'post_title'   => $major_title,
				'post_name'    => $major_slug,
				'post_status'  => 'publish',
				'post_type'    => LTDH_CPT_MAJOR,
				'post_content' => 'Ngành đào tạo mũi nhọn đón đầu cuộc cách mạng công nghiệp 4.0 và chuyển đổi số.',
			] );
			if ( is_wp_error( $major_id ) ) {
				WP_CLI::error( 'Lỗi khi tạo Ngành Công nghệ thông tin: ' . $major_id->get_error_message() );
				return;
			}
			WP_CLI::success( "Đã tạo Ngành học: $major_title" );
		} else {
			$major_id = $major->ID;
			WP_CLI::line( "Ngành học đã tồn tại: $major_title" );
		}

		// Update Major Meta
		update_post_meta( $major_id, 'major_code', '7480201' );
		update_post_meta( $major_id, 'career_opportunities', 'Cơ hội làm việc tại các tập đoàn công nghệ lớn: Viettel, FPT, VNPT, CMC với vai trò Kỹ sư Phần mềm, Quản trị Hệ thống, Kiểm thử Phần mềm, DevOps, AI Engineer.' );
		update_post_meta( $major_id, 'admission_groups', 'A00, A01, D01, D07' );
		update_post_meta( $major_id, LTDH_META_ADMISSION_STATUS, LTDH_STATUS_OPEN );
		wp_set_object_terms( $major_id, 'ky-thuat-cong-nghe', 'major_cat' );

		// Cleanup legacy/duplicate UTC IT programs if any exist
		$valid_slugs = [
			'cu-nhan-cong-nghe-thong-tin-vua-hoc-vua-lam-vua-hoc-vua-lam-utc',
			'cu-nhan-cong-nghe-thong-tin-lien-thong-chinh-quy-chinh-quy-utc',
			'cu-nhan-cong-nghe-thong-tin-dao-tao-tu-xa-tu-xa-utc',
		];
		$all_utc_programs = get_posts( [
			'post_type'      => LTDH_CPT_PROGRAM,
			'posts_per_page' => -1,
			'meta_query'     => [
				[
					'key'   => LTDH_META_SCHOOL_REL,
					'value' => $school_id,
				],
			],
		] );
		foreach ( $all_utc_programs as $old_p ) {
			if ( ! in_array( $old_p->post_name, $valid_slugs, true ) ) {
				wp_delete_post( $old_p->ID, true );
			}
		}

		// 3. Seed Programs for the 3 training types
		$programs_data = [
			'vua-hoc-vua-lam' => [
				'title' => 'Cử nhân Công nghệ thông tin (Vừa học vừa làm)',
				'tuition' => '684.026đ / tín chỉ',
				'tuition_amount' => 684026,
				'tuition_unit' => 'tin-chi',
				'tuition_year' => '2025 - 2026',
				'total_credits' => 136,
				'increase_roadmap' => 'Học phí có thể được điều chỉnh hàng năm theo quy định của nhà nước và lộ trình của nhà trường (tăng không quá 10%/năm).',
				'duration' => '2.0 năm',
				'quota' => 200,
				'schedule' => 'Tối thứ 2 - thứ 6 hoặc Thứ 7 & Chủ nhật',
				'target' => 'Người đi làm, người muốn học liên thông từ Trung cấp/Cao đẳng lên Đại học',
				'advantages' => 'Vừa học vừa đi làm tích lũy kinh nghiệm thực tế, lịch học linh hoạt ngoài giờ hành chính.',
				'disadvantages' => 'Yêu cầu thời gian cam kết học tập đều đặn vào buổi tối hoặc cuối tuần.',
				'batches' => [
					[ 'batch_name' => 'Tuyển sinh Đợt 1', 'release_period' => '20/04/2026 - 15/06/2026', 'application_period' => '20/04/2026 - 15/06/2026', 'review_time' => '-', 'evaluation_time' => 'Tháng 6/2026', 'enrollment_time' => 'Tháng 7/2026', 'batch_status' => 'dang-nhan' ],
					[ 'batch_name' => 'Tuyển sinh Đợt 2', 'release_period' => '20/08/2026 - 15/09/2026', 'application_period' => '20/08/2026 - 15/09/2026', 'review_time' => '-', 'evaluation_time' => 'Tháng 9/2026', 'enrollment_time' => 'Tháng 10/2026', 'batch_status' => 'sap-mo' ]
				]
			],
			'chinh-quy' => [
				'title' => 'Cử nhân Công nghệ thông tin (Liên thông Chính quy)',
				'tuition' => '526.174đ / tín chỉ',
				'tuition_amount' => 526174,
				'tuition_unit' => 'tin-chi',
				'tuition_year' => '2025 - 2026',
				'total_credits' => 136,
				'increase_roadmap' => 'Học phí có thể được điều chỉnh hàng năm theo quy định của nhà nước và lộ trình của nhà trường (tăng không quá 10%/năm).',
				'duration' => '2.0 năm',
				'quota' => 700,
				'schedule' => 'Học ban ngày tại giảng đường',
				'target' => 'Sinh viên tốt nghiệp Cao đẳng muốn liên thông chính quy lên Đại học',
				'advantages' => 'Bằng đại học chính quy danh giá, môi trường học tập tập trung chuyên sâu.',
				'disadvantages' => 'Lịch học ban ngày cố định, khó sắp xếp đi làm thêm.',
				'batches' => [
					[ 
						'batch_name' => 'Tuyển sinh Đợt 1',
						'release_period' => '19/12/2025 - 05/01/2026',
						'application_period' => '22/12/2025 - 07/01/2026',
						'review_time' => 'Dự kiến từ 25/12/2025',
						'evaluation_time' => 'Xét tuyển 09-14/01/2026',
						'enrollment_time' => 'Thi tuyển 17-18/01/2026',
						'batch_status' => 'da-dong'
					],
					[
						'batch_name' => 'Tuyển sinh Đợt 2',
						'release_period' => '24/03/2026 - 13/05/2026',
						'application_period' => '25/03/2026 - 18/05/2026',
						'review_time' => 'Dự kiến từ 12/04/2026',
						'evaluation_time' => 'Xét tuyển 01-04/06/2026',
						'enrollment_time' => 'Thi tuyển 06-07/06/2026',
						'batch_status' => 'dang-nhan'
					],
					[
						'batch_name' => 'Tuyển sinh Đợt 3 (Bổ sung)',
						'release_period' => '06/07/2026 - 13/08/2026',
						'application_period' => '07/07/2026 - 14/08/2026',
						'review_time' => 'Dự kiến từ 12/07/2026',
						'evaluation_time' => 'Xét tuyển 07-09/09/2026',
						'enrollment_time' => 'Thi tuyển 12-13/09/2026',
						'batch_status' => 'sap-mo'
					]
				]
			],
			'tu-xa' => [
				'title' => 'Cử nhân Công nghệ thông tin (Đào tạo từ xa)',
				'tuition' => '606.369đ / tín chỉ',
				'tuition_amount' => 606369,
				'tuition_unit' => 'tin-chi',
				'tuition_year' => '2025 - 2026',
				'total_credits' => 136,
				'increase_roadmap' => 'Học phí có thể được điều chỉnh hàng năm theo quy định của nhà nước và lộ trình của nhà trường (tăng không quá 10%/năm).',
				'duration' => 'Tối thiểu 1.5 năm',
				'quota' => 800,
				'schedule' => 'Học trực tuyến (E-learning) 100% linh hoạt',
				'target' => 'Người đi làm bận rộn, người muốn học văn bằng 2 hoặc liên thông từ xa',
				'advantages' => 'Tự chủ thời gian và không gian học tập, phôi bằng tốt nghiệp không ghi hình thức đào tạo.',
				'disadvantages' => 'Đòi hỏi tính tự kỷ luật và chủ động cao trong tự học.',
				'batches' => [
					[ 'batch_name' => 'Tuyển sinh Đợt 1', 'release_period' => '-', 'application_period' => 'Liên tục trong năm', 'review_time' => '-', 'evaluation_time' => 'Xét tuyển tháng 3/2026', 'enrollment_time' => 'Khai giảng tháng 4/2026', 'batch_status' => 'da-dong' ],
					[ 'batch_name' => 'Tuyển sinh Đợt 2', 'release_period' => '-', 'application_period' => 'Liên tục trong năm', 'review_time' => '-', 'evaluation_time' => 'Xét tuyển tháng 5/2026', 'enrollment_time' => 'Khai giảng tháng 6/2026', 'batch_status' => 'da-dong' ],
					[ 'batch_name' => 'Tuyển sinh Đợt 3', 'release_period' => '-', 'application_period' => 'Liên tục trong năm', 'review_time' => '-', 'evaluation_time' => 'Xét tuyển tháng 8/2026', 'enrollment_time' => 'Khai giảng tháng 9/2026', 'batch_status' => 'dang-nhan' ],
					[ 'batch_name' => 'Tuyển sinh Đợt 4', 'release_period' => '-', 'application_period' => 'Liên tục trong năm', 'review_time' => '-', 'evaluation_time' => 'Xét tuyển tháng 11/2026', 'enrollment_time' => 'Khai giảng tháng 12/2026', 'batch_status' => 'sap-mo' ]
				]
			]
		];

		foreach ( $programs_data as $t_slug => $p_info ) {
			$program_title = $p_info['title'];
			$program_name_slug = sanitize_title( $program_title . '-' . $t_slug . '-utc' );

			$existing_program = get_posts( [
				'post_type'  => LTDH_CPT_PROGRAM,
				'name'       => $program_name_slug,
				'posts_per_page' => 1,
			] );

			if ( ! empty( $existing_program ) ) {
				$program_id = $existing_program[0]->ID;
				WP_CLI::line( "Chương trình đã tồn tại: $program_title" );
			} else {
				$program_id = wp_insert_post( [
					'post_title'   => $program_title,
					'post_name'    => $program_name_slug,
					'post_status'  => 'publish',
					'post_type'    => LTDH_CPT_PROGRAM,
					'post_content' => "Chương trình đào tạo Cử nhân ngành $major_title hệ " . ( $t_slug === 'tu-xa' ? 'Đào tạo từ xa' : ( $t_slug === 'chinh-quy' ? 'Chính quy' : 'Vừa học vừa làm' ) ) . " của $school_title.",
				] );

				if ( is_wp_error( $program_id ) ) {
					WP_CLI::line( "Lỗi khi tạo chương trình $program_title: " . $program_id->get_error_message() );
					continue;
				}
				WP_CLI::success( "Đã tạo Chương trình: $program_title" );
			}

			// Update Program Meta
			update_post_meta( $program_id, LTDH_META_SCHOOL_REL, $school_id );
			update_post_meta( $program_id, LTDH_META_MAJOR_REL, $major_id );
			update_post_meta( $program_id, LTDH_META_TUITION, $p_info['tuition'] );
			update_post_meta( $program_id, 'tuition_amount', $p_info['tuition_amount'] );
			update_post_meta( $program_id, 'tuition_unit', $p_info['tuition_unit'] );
			update_post_meta( $program_id, 'tuition_academic_year', $p_info['tuition_year'] );
			update_post_meta( $program_id, 'tuition_total_credits', $p_info['total_credits'] );
			update_post_meta( $program_id, 'tuition_increase_roadmap', $p_info['increase_roadmap'] );
			update_post_meta( $program_id, 'quota', $p_info['quota'] );
			update_post_meta( $program_id, LTDH_META_DURATION, $p_info['duration'] );
			update_post_meta( $program_id, 'campus_info', 'Hà Nội' );
			update_post_meta( $program_id, 'admission_requirements', 'Xét tuyển học bạ hoặc hồ sơ văn bằng (THPT, Trung cấp, Cao đẳng, Đại học).' );
			update_post_meta( $program_id, 'required_documents', 'Bản sao công chứng CCCD, Ảnh chân dung, Bằng tốt nghiệp cao nhất, Học bạ/Bảng điểm tương ứng.' );
			update_post_meta( $program_id, 'enrollment_period', 'Tuyển sinh liên tục trong năm' );
			update_post_meta( $program_id, 'program_benefits', $p_info['advantages'] );
			update_post_meta( $program_id, LTDH_META_SCHEDULE, $p_info['schedule'] );
			update_post_meta( $program_id, 'target_students', $p_info['target'] );
			update_post_meta( $program_id, 'degree_type', 'Cử nhân Công nghệ thông tin' );
			update_post_meta( $program_id, 'diploma_value', 'Phôi bằng chuẩn Bộ GD&ĐT, đủ điều kiện học tiếp lên Thạc sĩ/Tiến sĩ hoặc thi công chức.' );
			update_post_meta( $program_id, 'disadvantages', $p_info['disadvantages'] );

			update_post_meta( $program_id, LTDH_META_ADMISSION_STATUS, LTDH_STATUS_OPEN );
			update_post_meta( $program_id, LTDH_META_AD_GROUPS, 'A00, A01, D01, D07' );

			// Save Repeater field for batches
			update_post_meta( $program_id, 'admission_batches', count( $p_info['batches'] ) );
			foreach ( $p_info['batches'] as $index => $batch ) {
				update_post_meta( $program_id, "admission_batches_{$index}_batch_name", $batch['batch_name'] );
				update_post_meta( $program_id, "admission_batches_{$index}_release_period", $batch['release_period'] ?? '' );
				update_post_meta( $program_id, "admission_batches_{$index}_application_period", $batch['application_period'] );
				update_post_meta( $program_id, "admission_batches_{$index}_review_time", $batch['review_time'] ?? '' );
				update_post_meta( $program_id, "admission_batches_{$index}_evaluation_time", $batch['evaluation_time'] );
				update_post_meta( $program_id, "admission_batches_{$index}_enrollment_time", $batch['enrollment_time'] );
				update_post_meta( $program_id, "admission_batches_{$index}_batch_status", $batch['batch_status'] );
				
				// ACF subfields mapping keys (optional but helpful)
				update_post_meta( $program_id, "_admission_batches_{$index}_batch_name", 'field_program_batch_name' );
				update_post_meta( $program_id, "_admission_batches_{$index}_release_period", 'field_program_batch_release_period' );
				update_post_meta( $program_id, "_admission_batches_{$index}_application_period", 'field_program_batch_application_period' );
				update_post_meta( $program_id, "_admission_batches_{$index}_review_time", 'field_program_batch_review_time' );
				update_post_meta( $program_id, "_admission_batches_{$index}_evaluation_time", 'field_program_batch_evaluation_time' );
				update_post_meta( $program_id, "_admission_batches_{$index}_enrollment_time", 'field_program_batch_enrollment_time' );
				update_post_meta( $program_id, "_admission_batches_{$index}_batch_status", 'field_program_batch_status' );
			}
			update_post_meta( $program_id, '_admission_batches', 'field_program_admission_batches' );

			$faq_question = 'Học hệ từ xa có giá trị tương đương hệ chính quy không?';
			if ( $t_slug === 'vua-hoc-vua-lam' ) {
				$faq_question = 'Học hệ vừa học vừa làm có giá trị tương đương hệ chính quy không?';
			} elseif ( $t_slug === 'chinh-quy' ) {
				$faq_question = 'Bằng liên thông chính quy khác gì bằng đại học chính quy 4 năm?';
			}

			$faqs = [
				[ 'question' => $faq_question, 'answer' => 'Có. Theo Thông tư của Bộ GD&ĐT, từ ngày 01/03/2020 trên văn bằng tốt nghiệp Đại học không ghi hình thức đào tạo (Chính quy, Từ xa, hay Vừa học vừa làm), giá trị pháp lý là hoàn toàn như nhau.' ],
				[ 'question' => 'Thời gian đào tạo tối đa là bao lâu?', 'answer' => 'Thời gian đào tạo tiêu chuẩn là từ 1.5 đến 4 năm tùy thuộc văn bằng đầu vào của học viên (Liên thông, văn bằng 2 hoặc tốt nghiệp THPT).' ]
			];
			update_post_meta( $program_id, 'faq', $faqs );

			// Set eligibility criteria
			$elig_min_edu = ( $t_slug === 'tu-xa' || $t_slug === 'vua-hoc-vua-lam' ) ? 'thap-phan' : 'cao-dang';
			update_post_meta( $program_id, 'elig_min_education', $elig_min_edu );
			update_post_meta( $program_id, 'elig_training_types', [ $t_slug ] );
			update_post_meta( $program_id, 'elig_campuses', [ 'ha-noi' ] );
			update_post_meta( $program_id, 'elig_max_grad_years', 99 );
			update_post_meta( $program_id, 'elig_notes', '' );

			wp_set_object_terms( $program_id, $t_slug, LTDH_TAX_TRAINING_TYPE );
			wp_set_object_terms( $program_id, 'ha-noi', LTDH_TAX_CAMPUS );

			if ( function_exists( 'ltdh_sync_program_relationships' ) ) {
				ltdh_sync_program_relationships( $program_id );
			}
		}

		WP_CLI::success( 'Đã gieo mẫu thành công dữ liệu Trường UTC, Ngành CNTT và 3 Hệ đào tạo!' );
	}

	/**
	 * Seed 20 news posts across 3 categories.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ltdh seed-posts
	 */
	public function seed_posts( $args, $assoc_args ) {
		WP_CLI::log( 'Đang chuẩn bị danh mục tin tức...' );

		$cat_guide = wp_create_category( 'Hướng dẫn tuyển sinh' );
		$cat_news  = wp_create_category( 'Tin tuyển sinh' );
		$cat_ann   = wp_create_category( 'Thông báo' );

		$titles_guides = [
			'Lộ trình học liên thông đại học cho người đi làm bận rộn',
			'Cách chuyển đổi tín chỉ khi học Văn bằng 2 đại học từ xa',
			'Những lưu ý quan trọng khi chuẩn bị hồ sơ tuyển sinh đại học trực tuyến',
			'Bằng Đại học từ xa có được thi công chức, cao học không?',
			'Kinh nghiệm tự học trực tuyến E-Learning đạt kết quả xuất sắc',
			'So sánh ưu nhược điểm giữa Đại học từ xa và Vừa học vừa làm',
			'Học phí đại học từ xa 2026 của các trường tốp đầu'
		];

		$titles_news = [
			'Nhu cầu nhân lực ngành Công nghệ thông tin tăng mạnh năm 2026',
			'Các trường đại học công bố chỉ tiêu tuyển sinh liên thông đợt mới',
			'Xu hướng chọn ngành Quản trị kinh doanh trong kỷ nguyên số',
			'Đại học Giao thông Vận tải mở rộng đào tạo hệ từ xa chất lượng cao',
			'Thị trường lao động ưu tiên nhân sự sở hữu từ hai văn bằng',
			'Phương thức tuyển sinh bằng học bạ tiếp tục giữ ưu thế',
			'Ngành Kế toán doanh nghiệp chuyển mình với công nghệ AI'
		];

		$titles_ann = [
			'Thông báo tuyển sinh đợt 2 hệ Đại học từ xa năm 2026',
			'Lịch khai giảng các lớp liên thông và văn bằng 2 tháng tới',
			'Danh sách học viên trúng tuyển đợt tuyển sinh vừa qua',
			'Thông báo nhận hồ sơ xét tuyển học bạ đợt 1',
			'Lịch thi tốt nghiệp và hướng dẫn thủ tục làm khóa luận tốt nghiệp',
			'Chương trình học bổng khuyến học cho tân sinh viên E-Learning'
		];

		$posts_seeded = 0;

		foreach ( $titles_guides as $title ) {
			$existing = get_page_by_path( sanitize_title( $title ), OBJECT, 'post' );
			if ( ! $existing ) {
				$post_id = wp_insert_post([
					'post_title'   => $title,
					'post_name'    => sanitize_title( $title ),
					'post_status'  => 'publish',
					'post_type'    => 'post',
					'post_content' => 'Nội dung chi tiết của bài viết hướng dẫn tuyển sinh về chủ đề: "' . $title . '". Bài viết cung cấp các thông tin hữu ích, phân tích chuyên sâu giúp học viên dễ dàng định hướng con đường học tập nâng cao bằng cấp phù hợp với công việc hiện tại.',
				]);
				if ( ! is_wp_error( $post_id ) ) {
					wp_set_post_categories( $post_id, [ $cat_guide ] );
					$posts_seeded++;
				}
			}
		}

		foreach ( $titles_news as $title ) {
			$existing = get_page_by_path( sanitize_title( $title ), OBJECT, 'post' );
			if ( ! $existing ) {
				$post_id = wp_insert_post([
					'post_title'   => $title,
					'post_name'    => sanitize_title( $title ),
					'post_status'  => 'publish',
					'post_type'    => 'post',
					'post_content' => 'Cập nhật tin tức tuyển sinh mới nhất về chủ đề: "' . $title . '". Phản ánh thực trạng nhu cầu học tập, xu hướng tuyển dụng của doanh nghiệp và những thay đổi trong phương thức tuyển sinh của các trường đại học đối tác.',
				]);
				if ( ! is_wp_error( $post_id ) ) {
					wp_set_post_categories( $post_id, [ $cat_news ] );
					$posts_seeded++;
				}
			}
		}

		foreach ( $titles_ann as $title ) {
			$existing = get_page_by_path( sanitize_title( $title ), OBJECT, 'post' );
			if ( ! $existing ) {
				$post_id = wp_insert_post([
					'post_title'   => $title,
					'post_name'    => sanitize_title( $title ),
					'post_status'  => 'publish',
					'post_type'    => 'post',
					'post_content' => 'Bản tin thông báo chính thức từ ban tuyển sinh: "' . $title . '". Yêu cầu tất cả học viên và quý thí sinh quan tâm chú ý theo dõi để chuẩn bị hồ sơ và hoàn thành các thủ tục đúng thời hạn quy định.',
				]);
				if ( ! is_wp_error( $post_id ) ) {
					wp_set_post_categories( $post_id, [ $cat_ann ] );
					$posts_seeded++;
				}
			}
		}

		WP_CLI::success( "Đã gieo thành công $posts_seeded bài viết mới vào 3 danh mục tin tức!" );
	}
}

WP_CLI::add_command( 'ltdh', 'LTDH_CLI_Commands' );
