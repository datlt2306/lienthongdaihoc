<?php
/**
 * Easy Table of Contents (EZ-TOC) Configuration & Automated Setup
 *
 * File cấu hình tích hợp và tự động kích hoạt Easy Table of Contents cho toàn bộ
 * bài viết (post), cẩm nang (guide), ngành học (major) và các loại bài viết khác.
 * 
 * - Mặc định tự động sinh Mục lục khi bài viết có từ 2 tiêu đề (H2, H3) trở lên.
 * - Tác giả không cần phải chèn thủ công shortcode [ez-toc].
 * - Giao diện trong suốt chuẩn theme (Transparent), đồng bộ với thiết kế Tailwind CSS.
 * - Tự động cuộn mượt (Smooth Scroll) có bù trừ chiều cao sticky header (offset 120px).
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trả về danh sách cấu hình chuẩn mặc định của Easy Table of Contents cho theme.
 *
 * @return array<string, mixed>
 */
function ltdh_eztoc_get_default_config(): array {
	return [
		// Các Post Type được hỗ trợ và tự động chèn mục lục
		'enabled_post_types'         => [ 'post', 'major', 'guide', 'page', 'school', 'program' ],
		'auto_insert_post_types'     => [ 'post', 'major', 'guide', 'page', 'school', 'program' ],

		// Vị trí và điều kiện hiển thị
		'position'                   => 'before',     // Tự động chèn ngay trước thẻ Heading đầu tiên
		'start'                      => 2,            // Tự động sinh khi bài viết có từ 2 tiêu đề trở lên
		'heading_levels'             => [ '2', '3' ], // Chỉ quét các đề mục H2 và H3 (tránh loãng mục lục)

		// Tiêu đề khối mục lục
		'show_heading_text'          => 1,
		'heading_text'               => 'Mục lục bài viết',
		'heading_text_tag'           => 'p',
		'show_hierarchy'             => 1,            // Phân cấp thụt lề H3 dưới H2
		'counter'                    => 'none',       // Tắt bộ đếm mặc định của plugin để dùng CSS theme

		// Tính năng tương tác (Thu gọn / Mở rộng / Cuộn mượt)
		'visibility'                 => 1,            // Cho phép người dùng bấm nút Thu gọn / Mở rộng
		'visibility_hide_by_default' => 1,            // Mặc định luôn đóng (thu gọn) khi tải trang
		'visibility_hide_by_device'  => [ 'mobile', 'desktop' ], // Đóng trên cả mobile và desktop
		'css_container_class'        => 'toc_close',  // Khởi tạo container ở trạng thái đóng (toc_close)
		'smooth_scroll'              => 1,            // Bật hiệu ứng cuộn mượt khi click liên kết
		'smooth_scroll_offset'       => 120,          // Bù trừ 120px cho fixed header & navigation trên desktop
		'mobile_smooth_scroll_offset'=> 100,          // Bù trừ 100px trên thiết bị di động

		// Giao diện & Hiển thị
		'theme'                      => 'transparent', // Dùng theme transparent để ăn theo style CSS của theme
		'width'                      => 'auto',
		'wrapping'                   => 'none',
	];
}

/**
 * Tự động đồng bộ cấu hình mặc định vào database (wp_options) nếu plugin EZ-TOC đang kích hoạt.
 * Chạy an toàn với transient cache để không gây tốn tài nguyên truy vấn.
 */
add_action( 'init', 'ltdh_eztoc_sync_database_settings', 10 );
function ltdh_eztoc_sync_database_settings(): void {
	// Chỉ xử lý nếu plugin Easy Table of Contents đang hoạt động
	if ( ! class_exists( 'ezTOC_Option' ) && ! class_exists( 'ezTOC' ) ) {
		return;
	}

	$cache_key = 'ltdh_eztoc_config_synced_v2';
	if ( get_transient( $cache_key ) ) {
		return;
	}

	$defaults    = ltdh_eztoc_get_default_config();
	$current_opt = get_option( 'ez-toc-settings', [] );

	if ( ! is_array( $current_opt ) ) {
		$current_opt = [];
	}

	$updated = false;

	// 1. Đảm bảo toàn bộ post types mục tiêu luôn được bật auto-insert
	foreach ( [ 'enabled_post_types', 'auto_insert_post_types' ] as $pt_key ) {
		$existing_pts = isset( $current_opt[ $pt_key ] ) && is_array( $current_opt[ $pt_key ] )
			? $current_opt[ $pt_key ]
			: [];
		$merged_pts   = array_values( array_unique( array_merge( $existing_pts, $defaults[ $pt_key ] ) ) );
		if ( $merged_pts !== $existing_pts ) {
			$current_opt[ $pt_key ] = $merged_pts;
			$updated                = true;
		}
	}

	// 2. Ép buộc cấu hình mặc định là đóng (thu gọn) theo yêu cầu
	if ( empty( $current_opt['visibility_hide_by_default'] ) ) {
		$current_opt['visibility_hide_by_default'] = 1;
		$updated                                   = true;
	}
	if ( empty( $current_opt['visibility_hide_by_device'] ) || ! is_array( $current_opt['visibility_hide_by_device'] ) ) {
		$current_opt['visibility_hide_by_device'] = [ 'mobile', 'desktop' ];
		$updated                                  = true;
	}
	if ( empty( $current_opt['css_container_class'] ) || false === strpos( (string) $current_opt['css_container_class'], 'toc_close' ) ) {
		$current_opt['css_container_class'] = trim( ( $current_opt['css_container_class'] ?? '' ) . ' toc_close' );
		$updated                            = true;
	}

	// 3. Đảm bảo các thông số trải nghiệm quan trọng luôn chuẩn
	$critical_keys = [
		'position',
		'start',
		'heading_levels',
		'show_heading_text',
		'heading_text',
		'heading_text_tag',
		'show_hierarchy',
		'counter',
		'visibility',
		'smooth_scroll',
		'smooth_scroll_offset',
		'mobile_smooth_scroll_offset',
		'theme',
		'width',
		'wrapping',
	];

	foreach ( $critical_keys as $key ) {
		if ( ! isset( $current_opt[ $key ] ) || empty( $current_opt[ $key ] ) ) {
			$current_opt[ $key ] = $defaults[ $key ];
			$updated             = true;
		}
	}

	if ( $updated ) {
		update_option( 'ez-toc-settings', $current_opt );
	}

	// Lưu transient 7 ngày (hoặc cập nhật ngay khi admin lưu lại)
	set_transient( $cache_key, 1, 7 * DAY_IN_SECONDS );
}

/**
 * Filter Runtime: Bổ sung các post types cốt lõi vào danh sách auto-insert của EZ-TOC
 * ngay cả khi quản trị viên chưa truy cập trang cài đặt plugin.
 */
add_filter( 'eztoc_get_options', 'ltdh_eztoc_filter_runtime_options', 20 );
add_filter( 'ez_toc_get_options', 'ltdh_eztoc_filter_runtime_options', 20 );
function ltdh_eztoc_filter_runtime_options( $options ) {
	if ( ! is_array( $options ) ) {
		$options = [];
	}

	$defaults = ltdh_eztoc_get_default_config();

	foreach ( [ 'enabled_post_types', 'auto_insert_post_types' ] as $pt_key ) {
		$existing = isset( $options[ $pt_key ] ) && is_array( $options[ $pt_key ] ) ? $options[ $pt_key ] : [];
		$options[ $pt_key ] = array_values( array_unique( array_merge( $existing, $defaults[ $pt_key ] ) ) );
	}

	if ( empty( $options['heading_text'] ) ) {
		$options['heading_text'] = $defaults['heading_text'];
	}

	if ( empty( $options['theme'] ) ) {
		$options['theme'] = $defaults['theme'];
	}

	// Luôn đảm bảo mặc định đóng (thu gọn) trên runtime
	$options['visibility_hide_by_default'] = true;
	$options['visibility_hide_by_device']  = [ 'mobile', 'desktop' ];
	if ( empty( $options['css_container_class'] ) || false === strpos( (string) $options['css_container_class'], 'toc_close' ) ) {
		$options['css_container_class'] = trim( ( $options['css_container_class'] ?? '' ) . ' toc_close' );
	}

	return $options;
}

/**
 * Filter Runtime cho từng khóa tùy chọn cụ thể của EZ-TOC.
 */
add_filter( 'eztoc_get_option', 'ltdh_eztoc_filter_individual_option', 20, 3 );
add_filter( 'ez_toc_get_option', 'ltdh_eztoc_filter_individual_option', 20, 3 );
function ltdh_eztoc_filter_individual_option( $value, $key, $default ) {
	$defaults = ltdh_eztoc_get_default_config();

	switch ( $key ) {
		case 'enabled_post_types':
		case 'auto_insert_post_types':
			$existing = is_array( $value ) ? $value : [];
			return array_values( array_unique( array_merge( $existing, $defaults[ $key ] ) ) );

		case 'visibility_hide_by_default':
			return true;

		case 'visibility_hide_by_device':
			return [ 'mobile', 'desktop' ];

		case 'css_container_class':
			return ! empty( $value ) && false !== strpos( (string) $value, 'toc_close' )
				? $value
				: trim( ( (string) $value ) . ' toc_close' );

		case 'smooth_scroll_offset':
			return ! empty( $value ) ? (int) $value : $defaults['smooth_scroll_offset'];

		case 'heading_text':
			return ! empty( $value ) ? $value : $defaults['heading_text'];

		case 'theme':
			return ! empty( $value ) ? $value : $defaults['theme'];

		case 'position':
			return ! empty( $value ) ? $value : $defaults['position'];

		case 'start':
			return ! empty( $value ) ? (int) $value : $defaults['start'];

		case 'heading_levels':
			return ( is_array( $value ) && ! empty( $value ) ) ? $value : $defaults['heading_levels'];
	}

	return $value;
}

/**
 * Tự động xóa transient đồng bộ khi quản trị viên cập nhật cài đặt trong WP Admin
 * để thiết lập mới nhất được nạp ngay lập tức.
 */
add_action( 'update_option_ez-toc-settings', function () {
	delete_transient( 'ltdh_eztoc_config_synced_v1' );
} );
