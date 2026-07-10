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
			'Đại học Bách Khoa Hà Nội' => [
				'web'     => 'https://hust.edu.vn',
				'addr'    => 'Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội',
				'phone'   => '024 3869 6056',
				'region'  => 'mien-bac',
				'img'     => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&q=80&w=600',
				'logo'    => 'https://images.unsplash.com/photo-1594788094620-4579ad50c7fe?auto=format&fit=crop&q=80&w=150',
				'en'      => 'Hanoi University of Science and Technology',
				'rating'  => '4.8',
				'reviews' => '256',
				'target'  => '3.200',
			],
			'Đại học Kinh tế Quốc dân' => [
				'web'     => 'https://neu.edu.vn',
				'addr'    => '207 Giải Phóng, Đồng Tâm, Hai Bà Trưng, Hà Nội',
				'phone'   => '024 3628 0280',
				'region'  => 'mien-bac',
				'img'     => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=600',
				'logo'    => 'https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=150',
				'en'      => 'National Economics University',
				'rating'  => '4.7',
				'reviews' => '210',
				'target'  => '2.500',
			],
			'Đại học Ngoại thương' => [
				'web'     => 'https://ftu.edu.vn',
				'addr'    => '91 Chùa Láng, Láng Thượng, Đống Đa, Hà Nội',
				'phone'   => '024 3259 5158',
				'region'  => 'mien-bac',
				'img'     => 'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&q=80&w=600',
				'logo'    => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&q=80&w=150',
				'en'      => 'Foreign Trade University',
				'rating'  => '4.9',
				'reviews' => '189',
				'target'  => '3.800',
			],
			'Học viện Tài chính' => [
				'web'     => 'https://hvtc.edu.vn',
				'addr'    => 'Số 58 Lê Văn Hiến, Đức Thắng, Bắc Từ Liêm, Hà Nội',
				'phone'   => '024 3838 9326',
				'region'  => 'mien-bac',
				'img'     => 'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&q=80&w=600',
				'logo'    => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&q=80&w=150',
				'en'      => 'Academy of Finance',
				'rating'  => '4.8',
				'reviews' => '150',
				'target'  => '2.800',
			],
			'Đại học Công nghệ - ĐHQGHN' => [
				'web'     => 'https://uet.vnu.edu.vn',
				'addr'    => '144 Xuân Thủy, Dịch Vọng Hậu, Cầu Giấy, Hà Nội',
				'phone'   => '024 3754 7461',
				'region'  => 'mien-bac',
				'img'     => 'https://images.unsplash.com/photo-1519452635265-7b1fbfd1e4e0?auto=format&fit=crop&q=80&w=600',
				'logo'    => 'https://images.unsplash.com/photo-1488590528505-98d2b5aba04b?auto=format&fit=crop&q=80&w=150',
				'en'      => 'VNU University of Engineering and Technology',
				'rating'  => '4.7',
				'reviews' => '240',
				'target'  => '2.400',
			],
			'Đại học Giao thông vận tải' => [
				'web'     => 'https://utc.edu.vn',
				'addr'    => 'Số 3 Phố Cầu Giấy, Láng Thượng, Đống Đa, Hà Nội',
				'phone'   => '024 3766 3311',
				'region'  => 'mien-bac',
				'img'     => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=600',
				'logo'    => 'https://images.unsplash.com/photo-1594788094620-4579ad50c7fe?auto=format&fit=crop&q=80&w=150',
				'en'      => 'University of Transport and Communications',
				'rating'  => '4.7',
				'reviews' => '174',
				'target'  => '2.900',
			],
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
					update_post_meta( $post_id, 'english_name', $meta['en'] );
					update_post_meta( $post_id, 'rating', $meta['rating'] );
					update_post_meta( $post_id, 'reviews_count', $meta['reviews'] );
					update_post_meta( $post_id, 'admission_target', $meta['target'] );
					wp_set_object_terms( $post_id, $meta['region'], 'region' );
					$this->maybe_set_school_thumbnail( $post_id, $title, $meta['img'], $meta['logo'] );
					$school_ids[] = $post_id;
					WP_CLI::success( "Đã tạo Trường: $title" );
				}
			} else {
				update_post_meta( $school->ID, 'english_name', $meta['en'] );
				update_post_meta( $school->ID, 'rating', $meta['rating'] );
				update_post_meta( $school->ID, 'reviews_count', $meta['reviews'] );
				update_post_meta( $school->ID, 'admission_target', $meta['target'] );
				$this->maybe_set_school_thumbnail( $school->ID, $title, $meta['img'], $meta['logo'] );
				$school_ids[] = $school->ID;
				WP_CLI::line( "Trường đã tồn tại: $title" );
			}
		}

		// 2. Seed 5 Target Majors with Admission Groups
		$majors_data = [
			'Ngôn ngữ Anh'          => [ 'code' => '7220201', 'career' => 'Giáo viên Tiếng Anh, Biên dịch viên, Chuyên viên đối ngoại.', 'groups' => 'A01, D01, D09, D14', 'status' => 'tuyen-sinh', 'img' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&q=80&w=400' ],
			'Kế toán'                => [ 'code' => '7340301', 'career' => 'Kế toán trưởng, Kiểm toán viên, Kiểm soát nội bộ.', 'groups' => 'A00, A01, C01, D01', 'status' => 'tuyen-sinh', 'img' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&q=80&w=400' ],
			'Quản trị kinh doanh'    => [ 'code' => '7340101', 'career' => 'Quản trị viên dự án, Giám đốc điều hành, Startup founder.', 'groups' => 'A00, A01, C01, D01', 'status' => 'tuyen-sinh', 'img' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&q=80&w=400' ],
			'Marketing'              => [ 'code' => '7340115', 'career' => 'Chuyên viên SEO, Sáng tạo nội dung, Quản lý thương hiệu.', 'groups' => 'A00, A01, C01, D01', 'status' => 'tuyen-sinh', 'img' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&q=80&w=400' ],
			'Kinh doanh thương mại' => [ 'code' => '7340121', 'career' => 'Quản trị chuỗi cung ứng, Quản trị bán lẻ.', 'groups' => 'A00, A01, C01, D01', 'status' => 'tam-ngung', 'img' => 'https://images.unsplash.com/photo-1472851294608-062f824d296e?auto=format&fit=crop&q=80&w=400' ]
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
					
					// Sideload thumbnail for major
					require_once ABSPATH . 'wp-admin/includes/media.php';
					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';
					$attach_id = $this->sideload_image_from_url( $meta['img'], $post_id, $title );
					if ( ! is_wp_error( $attach_id ) ) {
						set_post_thumbnail( $post_id, (int) $attach_id );
					}
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

		// 3. Seed Programs with differentiated data for meaningful comparison
		$program_templates = [
			// [school_offset => [programs with varied data]]
			// Each school gets unique program characteristics
		];

		// Define unique program variants for each major x school combination
		// Keys must be substrings of the school title for matching
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
				'Ngoại thương' => [
					'tuition' => '720.000đ / tín chỉ', 'duration' => '3 năm',
					'schedule' => 'Buổi tối thứ 3, 5, 7', 'target' => 'Sinh viên mới tốt nghiệp THPT',
					'degree' => 'Cử nhân Ngôn ngữ Anh Thương mại', 'diploma' => 'Bằng có giá trị quốc tế, được công nhận bởi British Council',
					'advantages' => 'Chương trình song ngữ, cơ hội du học semester tại Anh',
					'disadvantages' => 'Học phí cao, yêu cầu đầu vào điểm chuẩn cao',
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
				'Công nghệ' => [
					'tuition' => '480.000đ / tín chỉ', 'duration' => '1.5 năm',
					'schedule' => 'Online + thực hành cuối tháng', 'target' => 'Người đi làm bận rộn',
					'degree' => 'Cử nhân Kế toán', 'diploma' => 'Bằng chính quy, được Bộ GD&ĐT công nhận',
					'advantages' => 'Hình thức học linh hoạt nhất, học phí thấp nhất thị trường',
					'disadvantages' => 'Ít cơ hội giao lưu, thực hành hạn chế',
				],
			],
			// Quản trị kinh doanh variants
			7340101 => [
				'Kinh tế' => [
					'tuition' => '680.000đ / tín chỉ', 'duration' => '3 năm',
					'schedule' => 'Buổi tối thứ 2, 4, 6', 'target' => 'Quản lý cấp trung, giám đốc bộ phận',
					'degree' => 'Cử nhân Quản trị Kinh doanh', 'diploma' => 'Bằng quốc tế, được AACSB công nhận',
					'advantages' => 'Chương trình MBA pathway, networking CEO级别',
					'disadvantages' => 'Học phí cao, yêu cầu kinh nghiệm làm việc',
				],
				'Ngoại thương' => [
					'tuition' => '750.000đ / tín chỉ', 'duration' => '2.5 năm',
					'schedule' => 'Thứ 7 & CN sáng', 'target' => 'Sinh viên mới tốt nghiệp, người muốn chuyển ngành',
					'degree' => 'Cử nhân Quản trị Kinh doanh Quốc tế', 'diploma' => 'Double degree với đối tác quốc tế',
					'advantages' => 'Cơ hội thực tập tại tập đoàn đa quốc gia, bằng double degree',
					'disadvantages' => 'Học phí rất cao, chương trình nặng',
				],
				'Giao thông' => [
					'tuition' => '420.000đ / tín chỉ', 'duration' => '2 năm',
					'schedule' => 'Online + practical workshop cuối tháng', 'target' => 'Người đi làm muốn có bằng đại học',
					'degree' => 'Cử nhân Quản trị Kinh doanh', 'diploma' => 'Bằng chính quy, được Bộ GD&ĐT công nhận',
					'advantages' => 'Học phí thấp nhất, hình thức học linh hoạt nhất',
					'disadvantages' => 'Không có networking cao cấp, brand知名度 thấp hơn',
				],
			],
			// Marketing variants
			7340115 => [
				'Công nghệ' => [
					'tuition' => '600.000đ / tín chỉ', 'duration' => '2 năm',
					'schedule' => 'Tối thứ 3, 5', 'target' => 'Content Creator, Social Media Manager',
					'degree' => 'Cử nhân Marketing Số', 'diploma' => 'Bảng bổ sung kỹ năng số hóa',
					'advantages' => 'Được đào tạo thực chiến với công cụ AI, Google Ads certified',
					'disadvantages' => 'Chương trình mới, chưa có nhiều cựu sinh viên',
				],
				'Ngoại thương' => [
					'tuition' => '700.000đ / tín chỉ', 'duration' => '2.5 năm',
					'schedule' => 'Buổi tối thứ 2, 4, 6', 'target' => 'Marketing Manager, Brand Manager',
					'degree' => 'Cử nhân Marketing Quốc tế', 'diploma' => 'Bằng có giá trị quốc tế, được CIM công nhận',
					'advantages' => 'Case study quốc tế, cơ hội du học ngắn hạn',
					'disadvantages' => 'Học phí cao, yêu cầu ngoại ngữ tốt',
				],
				'Tài chính' => [
					'tuition' => '520.000đ / tín chỉ', 'duration' => '2 năm',
					'schedule' => 'Thứ 7 cả ngày', 'target' => 'Người mới bắt đầu chuyển sang Marketing',
					'degree' => 'Cử nhân Marketing', 'diploma' => 'Bằng chính quy, được Bộ GD&ĐT công nhận',
					'advantages' => 'Học phí hợp lý, chương trình thực tế',
					'disadvantages' => 'Ít cơ hội thực tập tại agency lớn',
				],
			],
			// Kinh doanh thương mại variants
			7340121 => [
				'Kinh tế' => [
					'tuition' => '580.000đ / tín chỉ', 'duration' => '2.5 năm',
					'schedule' => 'Tối thứ 2, 4', 'target' => 'Nhân viên kinh doanh muốn thăng tiến',
					'degree' => 'Cử nhân Kinh doanh Thương mại', 'diploma' => 'Bằng chính quy, được Bộ GD&ĐT công nhận',
					'advantages' => 'Chương trình cập nhật xu hướng thương mại điện tử',
					'disadvantages' => 'Hình thức học truyền thống, ít ứng dụng công nghệ',
				],
				'Giao thông' => [
					'tuition' => '450.000đ / tín chỉ', 'duration' => '2 năm',
					'schedule' => 'Online + thực hành', 'target' => 'Người đi làm, chủ doanh nghiệp nhỏ',
					'degree' => 'Cử nhân Kinh doanh Thương mại', 'diploma' => 'Bằng chính quy, được Bộ GD&ĐT công nhận',
					'advantages' => 'Học phí thấp, áp dụng ngay vào công việc',
					'disadvantages' => 'Ít cơ hội giao lưu, networking hạn chế',
				],
			],
		];

		// Seed programs with unique data per major x school
		$campus_slugs   = ['ha-noi', 'ho-chi-minh', 'online'];
		$training_slugs = ['van-bang-2', 'tu-xa', 'chinh-quy'];

		$program_index = 0;
		foreach ( $school_ids as $school_id ) {
			$school_title = get_the_title( $school_id );
			$school_short = str_replace( ['Đại học ', 'Học viện '], '', $school_title );

			// Each school gets 3 programs with different majors
			for ( $j = 0; $j < 3; $j++ ) {
				$major_id = $major_ids[ $program_index % count( $major_ids ) ];
				$major_title = get_the_title( $major_id );
				$major_code = get_post_meta( $major_id, 'major_code', true );

				// Get variant data for this major x school combination
				// Match by checking if school title contains the variant key
				$variant = null;
				if ( isset( $variants[ $major_code ] ) ) {
					foreach ( $variants[ $major_code ] as $key => $v ) {
						if ( mb_stripos( $school_title, $key ) !== false ) {
							$variant = $v;
							break;
						}
					}
				}

				if ( ! $variant ) {
					// Fallback: random-ish but still unique
					$base_tuition = 400000 + ( $program_index * 50000 );
					$variant = [
						'tuition' => number_format( $base_tuition ) . 'đ / tín chỉ',
						'duration' => ( 1.5 + ( $j * 0.5 ) ) . ' năm',
						'schedule' => 'Lịch học linh hoạt',
						'target' => 'Người đi làm, văn bằng 2',
						'degree' => 'Cử nhân',
						'diploma' => 'Bằng chính quy, được Bộ GD&ĐT công nhận',
						'advantages' => 'Chi phí hợp lý, lịch học linh hoạt',
						'disadvantages' => 'Chưa có đánh giá chi tiết',
					];
				}

				$t_slug = $training_slugs[ $program_index % count( $training_slugs ) ];
				$c_slug = $campus_slugs[ $program_index % count( $campus_slugs ) ];
				$t_names = [ 'van-bang-2' => 'Văn bằng 2', 'tu-xa' => 'Từ xa', 'chinh-quy' => 'Chính quy' ];
				$t_name = $t_names[ $t_slug ];

				$program_title = "Cử nhân $major_title";
				$enrollment = $j === 0 ? 'Đợt 1 đến hết 30/06/2026' : ( $j === 1 ? 'Đợt 2 đến hết 30/09/2026' : 'Tuyển sinh quanh năm' );

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

		$guides_data = [
			[
				'title' => 'Học Đại học từ xa là gì?',
				'desc'  => 'Tìm hiểu hình thức đào tạo E-learning trực tuyến, xu hướng phát triển giáo dục đại học cho người đi làm.',
				'cat'   => $cat_guide,
				'img'   => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=400'
			],
			[
				'title' => 'Tuyển sinh Liên thông Cao đẳng lên Đại học năm 2026',
				'desc'  => 'Xét tuyển hồ sơ và miễn giảm tín chỉ đối với sinh viên có bằng tốt nghiệp Trung cấp/Cao đẳng chuyển tiếp.',
				'cat'   => $cat_news,
				'img'   => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=400'
			],
			[
				'title' => 'Thông báo tuyển sinh Đại học từ xa đợt 1 năm 2026',
				'desc'  => 'Thông tin chi tiết các ngành đào tạo tuyển sinh đại học trực tuyến và văn bằng 2 đợt đầu năm.',
				'cat'   => $cat_ann,
				'img'   => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=400'
			],
			[
				'title' => 'Quy định miễn giảm tín chỉ khi học văn bằng 2',
				'desc'  => 'Học viên có thể rút ngắn đến 50% thời gian học nếu đối chiếu bảng điểm môn học tương đồng.',
				'cat'   => $cat_guide,
				'img'   => 'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&q=80&w=400'
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
