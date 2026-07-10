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
		WP_CLI::log( 'Bắt đầu gieo dữ liệu mẫu...' );

		// Make sure taxonomies exist
		$this->create_taxonomies([], []);

		// 1. Seed 5 Schools
		$schools_data = [
			'Đại học Bách Khoa Hà Nội' => [ 'web' => 'https://hust.edu.vn', 'addr' => 'Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội', 'phone' => '024 3869 6056', 'region' => 'mien-bac' ],
			'Đại học Kinh tế Quốc dân' => [ 'web' => 'https://neu.edu.vn', 'addr' => '207 Giải Phóng, Đồng Tâm, Hai Bà Trưng, Hà Nội', 'phone' => '024 3628 0280', 'region' => 'mien-bac' ],
			'Đại học Ngoại thương' => [ 'web' => 'https://ftu.edu.vn', 'addr' => '91 Chùa Láng, Láng Thượng, Đống Đa, Hà Nội', 'phone' => '024 3259 5158', 'region' => 'mien-bac' ],
			'Học viện Tài chính' => [ 'web' => 'https://hvtc.edu.vn', 'addr' => 'Số 58 Lê Văn Hiến, Đức Thắng, Bắc Từ Liêm, Hà Nội', 'phone' => '024 3838 9326', 'region' => 'mien-bac' ],
			'Đại học Công nghệ - ĐHQGHN' => [ 'web' => 'https://uet.vnu.edu.vn', 'addr' => '144 Xuân Thủy, Dịch Vọng Hậu, Cầu Giấy, Hà Nội', 'phone' => '024 3754 7461', 'region' => 'mien-bac' ]
		];

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
					update_post_meta( $post_id, 'website', $meta['web'] );
					update_post_meta( $post_id, 'address', $meta['addr'] );
					update_post_meta( $post_id, 'hotline', $meta['phone'] );
					wp_set_object_terms( $post_id, $meta['region'], 'region' );
					$school_ids[] = $post_id;
					WP_CLI::success( "Đã tạo Trường: $title" );
				}
			} else {
				$school_ids[] = $school->ID;
				WP_CLI::line( "Trường đã tồn tại: $title" );
			}
		}

		// 2. Seed 5 Target Majors with Admission Groups
		$majors_data = [
			'Ngôn ngữ Anh'          => [ 'code' => '7220201', 'career' => 'Giáo viên Tiếng Anh, Biên dịch viên, Chuyên viên đối ngoại.', 'groups' => 'A01, D01, D09, D14', 'status' => 'tuyen-sinh' ],
			'Kế toán'                => [ 'code' => '7340301', 'career' => 'Kế toán trưởng, Kiểm toán viên, Kiểm soát nội bộ.', 'groups' => 'A00, A01, C01, D01', 'status' => 'tuyen-sinh' ],
			'Quản trị kinh doanh'    => [ 'code' => '7340101', 'career' => 'Quản trị viên dự án, Giám đốc điều hành, Startup founder.', 'groups' => 'A00, A01, C01, D01', 'status' => 'tuyen-sinh' ],
			'Marketing'              => [ 'code' => '7340115', 'career' => 'Chuyên viên SEO, Sáng tạo nội dung, Quản lý thương hiệu.', 'groups' => 'A00, A01, C01, D01', 'status' => 'tuyen-sinh' ],
			'Kinh doanh thương mại' => [ 'code' => '7340121', 'career' => 'Quản trị chuỗi cung ứng, Quản trị bán lẻ.', 'groups' => 'A00, A01, C01, D01', 'status' => 'tam-ngung' ]
		];

		$major_ids = [];
		foreach ( $majors_data as $title => $meta ) {
			$major = get_page_by_path( sanitize_title( $title ), OBJECT, 'major' );
			if ( ! $major ) {
				$post_id = wp_insert_post( [
					'post_title'   => $title,
					'post_name'    => sanitize_title( $title ),
					'post_status'  => 'publish',
					'post_type'    => 'major',
					'post_content' => 'Ngành đào tạo tiềm năng đón đầu xu hướng việc làm công nghệ cao.',
				] );
				if ( ! is_wp_error( $post_id ) ) {
					update_post_meta( $post_id, 'major_code', $meta['code'] );
					update_post_meta( $post_id, 'career_opportunities', $meta['career'] );
					update_post_meta( $post_id, 'salary_info', 'Mức lương từ 10 - 30 triệu tùy năng lực và vị trí công tác.' );
					update_post_meta( $post_id, 'job_market', 'Nhu cầu cao trên toàn quốc và khối liên doanh quốc tế.' );
					update_post_meta( $post_id, 'admission_groups', $meta['groups'] );
					update_post_meta( $post_id, 'admission_status', $meta['status'] );
					$major_ids[] = $post_id;
					WP_CLI::success( "Đã tạo Ngành học: $title" );
				}
			} else {
				$major_ids[] = $major->ID;
				WP_CLI::line( "Ngành học đã tồn tại: $title" );
			}
		}

		// 3. Seed 10 Programs
		$training_slugs = ['van-bang-2', 'tu-xa'];
		$campus_slugs   = ['ha-noi', 'ho-chi-minh', 'online'];

		for ( $i = 1; $i <= 10; $i++ ) {
			$school_id = $school_ids[ array_rand( $school_ids ) ];
			$major_id  = $major_ids[ array_rand( $major_ids ) ];
			$school_title = get_the_title( $school_id );
			$major_title  = get_the_title( $major_id );
			
			$t_slug = $training_slugs[ array_rand( $training_slugs ) ];
			$c_slug = $campus_slugs[ array_rand( $campus_slugs ) ];
			
			$t_name = ( $t_slug == 'tu-xa' ) ? 'Từ xa' : 'Văn bằng 2';
			$program_title = "Cử nhân $major_title $t_name";

			$program = get_page_by_path( sanitize_title( $program_title ), OBJECT, 'program' );
			if ( ! $program ) {
				$post_id = wp_insert_post( [
					'post_title'   => $program_title,
					'post_name'    => sanitize_title( $program_title ),
					'post_status'  => 'publish',
					'post_type'    => 'program',
					'post_content' => "Chương trình đào tạo Cử nhân ngành $major_title học trực tuyến linh động hoặc trực tiếp tại các cơ sở, tạo lộ trình tốt nhất cho sinh viên vừa học vừa làm.",
				] );
				if ( ! is_wp_error( $post_id ) ) {
					// Link relationships (Matches ACF keys)
					update_post_meta( $post_id, 'school_relationship', $school_id );
					update_post_meta( $post_id, 'major_relationship', $major_id );
					
					// Set program details
					update_post_meta( $post_id, 'tuition_fee', '450.000đ / tín chỉ' );
					update_post_meta( $post_id, 'duration', '1.5 - 2 năm' );
					update_post_meta( $post_id, 'campus_info', 'Hà Nội & Online' );
					update_post_meta( $post_id, 'admission_requirements', 'Xét tuyển hồ sơ văn bằng đã có (THPT, Trung cấp, Cao đẳng).' );
					update_post_meta( $post_id, 'required_documents', 'CCCD, Ảnh 3x4, Phiếu tuyển sinh, Bản sao công chứng Bằng tốt nghiệp.' );
					update_post_meta( $post_id, 'enrollment_period', 'Đợt 1 đến hết 30/11/2026' );
					update_post_meta( $post_id, 'program_benefits', 'Tự chọn thời gian học, bằng cấp được bộ GD&ĐT công nhận giá trị.' );

					// Inherit status and groups from parent major
					$parent_status = get_post_meta( $major_id, 'admission_status', true ) ?: 'tuyen-sinh';
					$parent_groups = get_post_meta( $major_id, 'admission_groups', true ) ?: 'A00, A01, D01';
					update_post_meta( $post_id, 'admission_status', $parent_status );
					update_post_meta( $post_id, 'admission_groups', $parent_groups );

					// Set FAQs repeater manually
					$faqs = [
						[ 'question' => 'Có cần phải đến trường học trực tiếp không?', 'answer' => 'Đối với hệ Từ xa, bạn học 100% qua E-Learning và không cần đến trường học hay điểm danh.' ],
						[ 'question' => 'Bằng tốt nghiệp có giá trị chính quy không?', 'answer' => 'Có. Bằng do Hiệu trưởng trường Đại học cấp và được bộ Giáo dục công nhận, có giá trị học lên Thạc sĩ/Tiến sĩ.' ]
					];
					update_post_meta( $post_id, 'faq', $faqs );

					// Assign Taxonomies
					wp_set_object_terms( $post_id, $t_slug, 'training_type' );
					wp_set_object_terms( $post_id, $c_slug, 'campus' );

					// Manually trigger bi-directional updates
					if ( function_exists( 'ltdh_sync_program_relationships' ) ) {
						ltdh_sync_program_relationships( $post_id );
					}

					WP_CLI::success( "Đã tạo Chương trình: $program_title" );
				}
			}
		}

		// 4. Seed 4 Guides
		$guides_data = [
			'Học Đại học từ xa là gì?' => 'Tìm hiểu hình thức đào tạo E-learning trực tuyến, xu hướng phát triển giáo dục đại học cho người đi làm.',
			'Liên thông Cao đẳng lên Đại học cần điều kiện gì?' => 'Điều kiện xét tuyển và miễn giảm tín chỉ đối với sinh viên có bằng tốt nghiệp Trung cấp/Cao đẳng chuyển tiếp.',
			'Văn bằng 2 là gì? Ai nên học?' => 'Cách thức mở rộng cơ hội việc làm nhanh nhất bằng việc học thêm một ngành học thứ 2 song song.',
			'Quy định miễn giảm tín chỉ khi học liên thông đại học' => 'Học viên có thể rút ngắn đến 50% thời gian học nếu đối chiếu bảng điểm môn học tương đồng.'
		];

		foreach ( $guides_data as $title => $content ) {
			$guide = get_page_by_path( sanitize_title( $title ), OBJECT, 'guide' );
			if ( ! $guide ) {
				$post_id = wp_insert_post( [
					'post_title'   => $title,
					'post_name'    => sanitize_title( $title ),
					'post_status'  => 'publish',
					'post_type'    => 'guide',
					'post_content' => $content,
				] );
				if ( ! is_wp_error( $post_id ) ) {
					WP_CLI::success( "Đã tạo Hướng dẫn: $title" );
				}
			}
		}

		WP_CLI::success( 'Đồng bộ hóa gieo seeder hoàn tất!' );
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
