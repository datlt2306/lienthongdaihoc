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
			'gioi-thieu'        => [ 'title' => 'Giới thiệu', 'template' => 'page-about.php' ],
			'lien-he'           => [ 'title' => 'Liên hệ', 'template' => 'page-contact.php' ],
			'dang-ky-tu-van'    => [ 'title' => 'Đăng ký tư vấn', 'template' => 'page-register.php' ],
			'faq'               => [ 'title' => 'Câu hỏi thường gặp', 'template' => 'page-faq.php' ],
			'dieu-khoan'        => [ 'title' => 'Điều khoản dịch vụ', 'template' => '' ],
			'chinh-sach-bao-mat'=> [ 'title' => 'Chính sách bảo mật', 'template' => '' ],
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
						'img'            => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=600',
						'logo'           => 'https://images.unsplash.com/photo-1594788094620-4579ad50c7fe?auto=format&fit=crop&q=80&w=150',
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
					update_post_meta( $post_id, 'hotline', $meta['phone'] );
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
						'post_type'    => 'major',
						'post_content' => 'Ngành đào tạo tiềm năng đón đầu xu hướng việc làm công nghệ cao.',
					] );
					if ( ! is_wp_error( $major_id ) ) {
						update_post_meta( $major_id, 'major_code', '7' . str_pad( rand( 100000, 999999 ), 6, '0', STR_PAD_LEFT ) );
						update_post_meta( $major_id, 'career_opportunities', 'Cơ hội nghề nghiệp rộng mở tại các doanh nghiệp lớn.' );
						update_post_meta( $major_id, 'salary_info', 'Mức lương từ 10 - 30 triệu tùy năng lực.' );
						update_post_meta( $major_id, 'job_market', 'Nhu cầu tuyển dụng cao trên toàn quốc.' );
						update_post_meta( $major_id, 'admission_groups', 'A00, A01, D01' );
						update_post_meta( $major_id, 'admission_status', 'tuyen-sinh' );
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

				$post_id = wp_insert_post( [
					'post_title'   => $program_title,
					'post_name'    => sanitize_title( $program_title . '-' . $t_slug . '-' . $school_title ),
					'post_status'  => 'publish',
					'post_type'    => 'program',
					'post_content' => "Chương trình đào tạo Cử nhân ngành $major_title hệ $t_name của $school_title. " . $variant['advantages'] . ".",
				] );

				if ( is_wp_error( $post_id ) ) {
					$program_index++;
					continue;
				}

				update_post_meta( $post_id, 'school_relationship', $school_id );
				update_post_meta( $post_id, 'major_relationship', $major_id );
				update_post_meta( $post_id, 'tuition_fee', $variant['tuition'] );
				update_post_meta( $post_id, 'duration', $variant['duration'] );
				update_post_meta( $post_id, 'campus_info', $c_slug === 'ha-noi' ? 'Hà Nội' : ( $c_slug === 'ho-chi-minh' ? 'TP. Hồ Chí Minh' : 'Online' ) );
				update_post_meta( $post_id, 'admission_requirements', 'Xét tuyển hồ sơ văn bằng đã có (THPT, Trung cấp, Cao đẳng).' );
				update_post_meta( $post_id, 'required_documents', 'CCCD, Ảnh 3x4, Phiếu tuyển sinh, Bản sao công chứng Bằng tốt nghiệp.' );
				update_post_meta( $post_id, 'enrollment_period', $enrollment );
				update_post_meta( $post_id, 'program_benefits', $variant['advantages'] );
				update_post_meta( $post_id, 'schedule', $variant['schedule'] );
				update_post_meta( $post_id, 'target_students', $variant['target'] );
				update_post_meta( $post_id, 'degree_type', $variant['degree'] );
				update_post_meta( $post_id, 'diploma_value', $variant['diploma'] );
				update_post_meta( $post_id, 'disadvantages', $variant['disadvantages'] );

				$parent_status = get_post_meta( $major_id, 'admission_status', true ) ?: 'tuyen-sinh';
				$parent_groups = get_post_meta( $major_id, 'admission_groups', true ) ?: 'A00, A01, D01';
				update_post_meta( $post_id, 'admission_status', $parent_status );
				update_post_meta( $post_id, 'admission_groups', $parent_groups );

				$faqs = [
					[ 'question' => 'Có cần phải đến trường học trực tiếp không?', 'answer' => $t_slug === 'tu-xa' ? 'Đối với hệ Từ xa, bạn học 100% qua E-Learning và không cần đến trường học hay điểm danh.' : 'Bạn cần đến học trực tiếp theo lịch học.' ],
					[ 'question' => 'Bằng tốt nghiệp có giá trị chính quy không?', 'answer' => 'Có. Bằng do Hiệu trưởng trường Đại học cấp và được bộ Giáo dục công nhận, có giá trị học lên Thạc sĩ/Tiến sĩ.' ]
				];
				update_post_meta( $post_id, 'faq', $faqs );

				// Set eligibility criteria
				$elig_min_edu = '';
				if ( $t_slug === 'lien-thong' ) {
					$elig_min_edu = 'trung-cap';
				} elseif ( $t_slug === 'van-bang-2' ) {
					$elig_min_edu = 'dai-hoc';
				} else {
					$elig_min_edu = 'thap-phan';
				}
				update_post_meta( $post_id, 'elig_min_education', $elig_min_edu );
				update_post_meta( $post_id, 'elig_training_types', [ $t_slug ] );
				update_post_meta( $post_id, 'elig_campuses', [ $c_slug ] );
				update_post_meta( $post_id, 'elig_max_grad_years', $t_slug === 'lien-thong' ? 10 : 99 );
				update_post_meta( $post_id, 'elig_notes', '' );

				wp_set_object_terms( $post_id, $t_slug, 'training_type' );
				wp_set_object_terms( $post_id, $c_slug, 'campus' );

				if ( function_exists( 'ltdh_sync_program_relationships' ) ) {
					ltdh_sync_program_relationships( $post_id );
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
				'img'   => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=400',
				'school' => ! empty( $school_ids ) ? $school_ids[ array_rand( $school_ids ) ] : 0,
			],
			[
				'title' => 'Tuyển sinh Liên thông Cao đẳng lên Đại học năm 2026',
				'desc'  => 'Xét tuyển hồ sơ và miễn giảm tín chỉ đối với sinh viên có bằng tốt nghiệp Trung cấp/Cao đẳng chuyển tiếp.',
				'cat'   => $cat_news,
				'img'   => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=400',
				'school' => ! empty( $school_ids ) ? $school_ids[ array_rand( $school_ids ) ] : 0,
			],
			[
				'title' => 'Thông báo tuyển sinh Đại học từ xa đợt 1 năm 2026',
				'desc'  => 'Thông tin chi tiết các ngành đào tạo tuyển sinh đại học trực tuyến và văn bằng 2 đợt đầu năm.',
				'cat'   => $cat_ann,
				'img'   => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=400',
				'school' => ! empty( $school_ids ) ? $school_ids[ array_rand( $school_ids ) ] : 0,
			],
			[
				'title' => 'Quy định miễn giảm tín chỉ khi học văn bằng 2',
				'desc'  => 'Học viên có thể rút ngắn đến 50% thời gian học nếu đối chiếu bảng điểm môn học tương đồng.',
				'cat'   => $cat_guide,
				'img'   => 'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&q=80&w=400',
				'school' => ! empty( $school_ids ) ? $school_ids[ array_rand( $school_ids ) ] : 0,
			]
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
}

WP_CLI::add_command( 'ltdh', 'LTDH_CLI_Commands' );
