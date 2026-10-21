<?php
/**
 * Program Reviews & Feedback Module
 *
 * Implements high-converting, verified student review system inspired by CellphoneS & Shopee:
 * - Specific to individual training programs (single-program.php)
 * - Anti-spam & Multi-layered Security (Honeypot, reCAPTCHA, Nonce, Binary MIME validation)
 * - Optimized Media Storage (Client-side pre-compression, secure directory, execution block)
 * - High Performance O(1) Rating Cache stored in post meta
 * - Google Schema.org AggregateRating Rich Snippet
 * - Full WordPress Admin Moderation & Official Board Responses
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Register Custom Post Type: program_review
// ----------------------------------------------------
add_action( 'init', 'ltdh_register_program_review_cpt' );

function ltdh_register_program_review_cpt() {
	$labels = [
		'name'               => 'Đánh giá chương trình',
		'singular_name'      => 'Đánh giá',
		'menu_name'          => 'Đánh giá học viên',
		'name_admin_bar'     => 'Đánh giá chương trình',
		'add_new'            => 'Thêm đánh giá mới',
		'add_new_item'       => 'Thêm đánh giá mới',
		'new_item'           => 'Đánh giá mới',
		'edit_item'          => 'Chỉnh sửa đánh giá',
		'view_item'          => 'Xem đánh giá',
		'all_items'          => 'Tất cả đánh giá',
		'search_items'       => 'Tìm kiếm đánh giá',
		'parent_item_colon'  => 'Chương trình:',
		'not_found'          => 'Không có đánh giá nào.',
		'not_found_in_trash' => 'Không có đánh giá nào trong thùng rác.',
	];

	$args = [
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true, // Independent Top-Level Admin Menu
		'menu_icon'          => 'dashicons-star-filled',
		'query_var'          => false,
		'rewrite'            => false,
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 23, // Right below Programs (22)
		'supports'           => [ 'title', 'editor' ],
		'show_in_rest'       => false,
	];

	register_post_type( 'program_review', $args );
}

/**
 * Add pending review count badge to the admin sidebar menu
 */
add_action( 'admin_menu', 'ltdh_add_pending_review_bubble_to_menu', 999 );
function ltdh_add_pending_review_bubble_to_menu() {
	global $menu;
	$counts = wp_count_posts( 'program_review' );
	$pending = ! empty( $counts->pending ) ? (int) $counts->pending : 0;
	if ( $pending > 0 && is_array( $menu ) ) {
		foreach ( $menu as $key => $item ) {
			if ( isset( $item[2] ) && $item[2] === 'edit.php?post_type=program_review' ) {
				$menu[ $key ][0] .= sprintf(
					' <span class="awaiting-mod update-plugins count-%1$d"><span class="pending-count">%1$d</span></span>',
					$pending
				);
				break;
			}
		}
	}
}

// ----------------------------------------------------
// 2. Rating Aspect Tags & Criteria Definitions
// ----------------------------------------------------
function ltdh_get_review_criteria_labels(): array {
	return [
		'schedule' => 'Lịch học linh hoạt (Online/Cuối tuần)',
		'lecturer' => 'Chất lượng giảng viên & LMS',
		'support'  => 'Hỗ trợ thủ tục & Cố vấn học tập',
		'tuition'  => 'Học phí minh bạch, đúng cam kết',
	];
}

function ltdh_get_review_aspect_tags(): array {
	return [
		'hoc-online-tien'       => 'Học online tiện lợi',
		'phu-hop-nguoi-di-lam'  => 'Phù hợp người đi làm',
		'bang-chuan-bo-gddt'    => 'Bằng chuẩn Bộ GD&ĐT',
		'thay-co-nhiet-tinh'    => 'Thầy cô hỗ trợ nhiệt tình',
		'xet-mien-mon-nhanh'    => 'Xét miễn môn nhanh',
		'hoc-phi-ro-rang'       => 'Học phí rõ ràng, không phát sinh',
		'thi-cu-thuan-tien'     => 'Lịch thi cuối tuần tiện lợi',
		'lms-de-su-dung'        => 'Phần mềm học trực quan, dễ dùng',
	];
}

// ----------------------------------------------------
// 3. Stats & Cache Engine (O(1) Performance)
// ----------------------------------------------------

/**
 * Retrieve cached rating summary for a program. Recalculates on cache miss.
 *
 * @param int $program_id
 * @return array
 */
function ltdh_get_program_rating_stats( int $program_id ): array {
	if ( ! $program_id ) {
		return ltdh_get_default_rating_stats();
	}

	$cached = get_post_meta( $program_id, '_program_rating_summary', true );
	if ( is_array( $cached ) && isset( $cached['total'] ) ) {
		return $cached;
	}

	return ltdh_recalculate_program_rating_stats( $program_id );
}

/**
 * Default empty rating statistics structure.
 *
 * @return array
 */
function ltdh_get_default_rating_stats(): array {
	return [
		'total'         => 0,
		'average'       => 5.0,
		'recommend_pct' => 100,
		'distribution'  => [ 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 ],
		'criteria'      => [
			'schedule' => 5.0,
			'lecturer' => 5.0,
			'support'  => 5.0,
			'tuition'  => 5.0,
		],
		'has_images'    => 0,
		'verified'      => 0,
		'tags_count'    => [],
	];
}

/**
 * Recalculate rating summary from published reviews and update program post meta.
 *
 * @param int $program_id
 * @return array
 */
function ltdh_recalculate_program_rating_stats( int $program_id ): array {
	if ( ! $program_id ) {
		return ltdh_get_default_rating_stats();
	}

	$reviews = get_posts( [
		'post_type'      => 'program_review',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'     => [
			[
				'key'     => '_review_program_id',
				'value'   => $program_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			],
		],
		'fields'         => 'ids',
	] );

	if ( empty( $reviews ) ) {
		$stats = ltdh_get_default_rating_stats();
		update_post_meta( $program_id, '_program_rating_summary', $stats );
		return $stats;
	}

	$total           = count( $reviews );
	$sum_rating      = 0;
	$distribution    = [ 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 ];
	$criteria_sums   = [ 'schedule' => 0, 'lecturer' => 0, 'support' => 0, 'tuition' => 0 ];
	$criteria_counts = [ 'schedule' => 0, 'lecturer' => 0, 'support' => 0, 'tuition' => 0 ];
	$has_images_cnt  = 0;
	$verified_cnt    = 0;
	$tags_count      = [];
	$high_rating_cnt = 0;

	foreach ( $reviews as $rev_id ) {
		$rating = (int) get_post_meta( $rev_id, '_review_rating', true );
		if ( $rating < 1 || $rating > 5 ) {
			$rating = 5;
		}

		$sum_rating += $rating;
		if ( isset( $distribution[ $rating ] ) ) {
			$distribution[ $rating ]++;
		}
		if ( $rating >= 4 ) {
			$high_rating_cnt++;
		}

		// Criteria
		$crit = get_post_meta( $rev_id, '_review_criteria', true );
		if ( is_array( $crit ) ) {
			foreach ( [ 'schedule', 'lecturer', 'support', 'tuition' ] as $ckey ) {
				if ( isset( $crit[ $ckey ] ) && (int) $crit[ $ckey ] >= 1 ) {
					$criteria_sums[ $ckey ] += (int) $crit[ $ckey ];
					$criteria_counts[ $ckey ]++;
				}
			}
		}

		// Images
		$imgs = get_post_meta( $rev_id, '_review_images', true );
		if ( ! empty( $imgs ) && is_array( $imgs ) ) {
			$has_images_cnt++;
		}

		// Verified
		if ( (int) get_post_meta( $rev_id, '_review_is_verified', true ) === 1 ) {
			$verified_cnt++;
		}

		// Tags
		$tags = get_post_meta( $rev_id, '_review_aspect_tags', true );
		if ( is_array( $tags ) ) {
			foreach ( $tags as $t ) {
				$t = sanitize_key( $t );
				if ( ! empty( $t ) ) {
					$tags_count[ $t ] = ( $tags_count[ $t ] ?? 0 ) + 1;
				}
			}
		}
	}

	$average       = round( $sum_rating / $total, 1 );
	$recommend_pct = round( ( $high_rating_cnt / $total ) * 100 );

	$final_criteria = [];
	foreach ( [ 'schedule', 'lecturer', 'support', 'tuition' ] as $ckey ) {
		$cnt = $criteria_counts[ $ckey ];
		$final_criteria[ $ckey ] = $cnt > 0 ? round( $criteria_sums[ $ckey ] / $cnt, 1 ) : 5.0;
	}

	$stats = [
		'total'         => $total,
		'average'       => $average,
		'recommend_pct' => $recommend_pct,
		'distribution'  => $distribution,
		'criteria'      => $final_criteria,
		'has_images'    => $has_images_cnt,
		'verified'      => $verified_cnt,
		'tags_count'    => $tags_count,
	];

	update_post_meta( $program_id, '_program_rating_summary', $stats );
	return $stats;
}

// Automatically sync cache when reviews are modified, approved, or deleted
add_action( 'save_post_program_review', 'ltdh_on_review_save', 10, 3 );
function ltdh_on_review_save( $post_id, $post, $update ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	$program_id = (int) get_post_meta( $post_id, '_review_program_id', true );
	if ( $program_id ) {
		ltdh_recalculate_program_rating_stats( $program_id );
	}
}

add_action( 'transition_post_status', 'ltdh_on_review_status_change', 10, 3 );
function ltdh_on_review_status_change( $new_status, $old_status, $post ) {
	if ( $post->post_type !== 'program_review' || $new_status === $old_status ) {
		return;
	}
	$program_id = (int) get_post_meta( $post->ID, '_review_program_id', true );
	if ( $program_id ) {
		ltdh_recalculate_program_rating_stats( $program_id );
	}
}

add_action( 'deleted_post', 'ltdh_on_review_deleted' );
function ltdh_on_review_deleted( $post_id ) {
	$post = get_post( $post_id );
	if ( $post && $post->post_type === 'program_review' ) {
		$program_id = (int) get_post_meta( $post_id, '_review_program_id', true );
		if ( $program_id ) {
			ltdh_recalculate_program_rating_stats( $program_id );
		}
	}
}

// ----------------------------------------------------
// 4. Secure Media Upload & Processing
// ----------------------------------------------------

/**
 * Upload and optimize review images securely.
 * Standardized via WordPress Media Attachment System to seamlessly support:
 * - Direct offloading to Cloudflare R2 / S3
 * - Automatic local file deletion via Offloader ("Full Cloud Migration")
 * - Maximum 3 images per review, max 2.5MB per image
 * - Strict binary MIME validation (JPEG, PNG, WebP)
 * - Safe randomized filename hash
 *
 * @param array $files_array The $_FILES['review_images'] structure
 * @param int   $program_id  Target Program ID
 * @param int   $review_id   Attached Review Post ID
 * @return array Array of image items: [ ['id' => ..., 'url' => ..., 'thumb' => ...] ]
 */
function ltdh_handle_review_images_upload( array $files_array, int $program_id, int $review_id = 0 ): array {
	if ( empty( $files_array['name'] ) || ! is_array( $files_array['name'] ) ) {
		return [];
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$allowed_mimes = [
		'image/jpeg' => 'jpg',
		'image/png'  => 'png',
		'image/webp' => 'webp',
	];

	$uploaded_results = [];
	$count            = count( $files_array['name'] );
	$max_allowed      = 3; // Maximum 3 images per review

	for ( $i = 0; $i < $count && count( $uploaded_results ) < $max_allowed; $i++ ) {
		if ( empty( $files_array['name'][ $i ] ) ) {
			continue;
		}

		$error     = $files_array['error'][ $i ];
		$tmp_name  = $files_array['tmp_name'][ $i ];
		$file_size = $files_array['size'][ $i ];

		if ( $error !== UPLOAD_ERR_OK || ! is_uploaded_file( $tmp_name ) ) {
			continue;
		}

		// Max size 2.5MB (2,621,440 bytes)
		if ( $file_size > 2621440 ) {
			continue;
		}

		// Binary MIME detection
		$finfo     = finfo_open( FILEINFO_MIME_TYPE );
		$real_mime = finfo_file( $finfo, $tmp_name );
		finfo_close( $finfo );

		if ( ! array_key_exists( $real_mime, $allowed_mimes ) ) {
			continue; // Reject dangerous or unallowed mime
		}

		$ext         = $allowed_mimes[ $real_mime ];
		$random_hash = wp_generate_password( 8, false, false );
		$clean_name  = sprintf( 'review_%d_%s_%s.%s', $program_id, gmdate( 'Ymd_His' ), $random_hash, $ext );

		$file_entry = [
			'name'     => $clean_name,
			'type'     => $real_mime,
			'tmp_name' => $tmp_name,
			'error'    => 0,
			'size'     => $file_size,
		];

		// Sideload into WordPress Media library attached to review post.
		// Triggers standard hooks ('wp_generate_attachment_metadata') which
		// auto-offloads to Cloudflare R2 and deletes the local file from server.
		$attachment_id = media_handle_sideload(
			$file_entry,
			$review_id,
			sprintf( 'Ảnh phản hồi thực tế chương trình #%d', $program_id )
		);

		if ( ! is_wp_error( $attachment_id ) ) {
			$full_url  = wp_get_attachment_image_url( $attachment_id, 'full' );
			$thumb_url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' ) ?: $full_url;

			$uploaded_results[] = [
				'id'    => (int) $attachment_id,
				'url'   => esc_url_raw( $full_url ),
				'thumb' => esc_url_raw( $thumb_url ),
			];
		}
	}

	return $uploaded_results;
}

// ----------------------------------------------------
// 5. AJAX Endpoints
// ----------------------------------------------------

// Endpoint: Submit Program Review
add_action( 'wp_ajax_ltdh_submit_program_review', 'ltdh_handle_submit_program_review' );
add_action( 'wp_ajax_nopriv_ltdh_submit_program_review', 'ltdh_handle_submit_program_review' );

function ltdh_handle_submit_program_review() {
	// 1. Nonce check
	$nonce = $_POST['nonce'] ?? '';
	if ( ! wp_verify_nonce( $nonce, 'ltdh_review_nonce' ) ) {
		wp_send_json_error( [ 'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.' ] );
	}

	// 2. Honeypot check (Bots fill this invisible field, humans do not)
	if ( ! empty( $_POST['website_url_hp'] ) ) {
		// Silent exit for bots
		wp_send_json_success( [
			'message' => 'Cảm ơn bạn đã gửi đánh giá! Đánh giá đang được tiếp nhận.',
		] );
	}

	// 3. reCAPTCHA check if enabled in theme
	if ( function_exists( 'ltdh_is_recaptcha_enabled' ) && ltdh_is_recaptcha_enabled() ) {
		$recaptcha_token = sanitize_text_field( $_POST['g-recaptcha-response'] ?? $_POST['recaptcha_token'] ?? '' );
		if ( ! empty( $recaptcha_token ) && function_exists( 'ltdh_verify_recaptcha' ) ) {
			if ( ! ltdh_verify_recaptcha( $recaptcha_token, 'program_review' ) ) {
				wp_send_json_error( [ 'message' => 'Xác thực an toàn không hợp lệ. Vui lòng thử lại.' ] );
			}
		}
	}

	// 4. Rate limiting (1 review per IP per program per hour)
	$program_id = isset( $_POST['program_id'] ) ? (int) $_POST['program_id'] : 0;
	if ( ! $program_id || get_post_type( $program_id ) !== 'program' ) {
		wp_send_json_error( [ 'message' => 'Chương trình đào tạo không hợp lệ.' ] );
	}

	// 4.1 Check browser submission cookie
	$cookie_key = 'ltdh_rev_done_' . $program_id;
	if ( isset( $_COOKIE[ $cookie_key ] ) ) {
		wp_send_json_error( [ 'message' => 'Bạn đã gửi đánh giá cho chương trình này rồi. Mỗi học viên chỉ được đánh giá 1 lần.' ] );
	}

	$user_ip      = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );
	$rate_key     = 'ltdh_rev_rate_' . md5( $user_ip . '_' . $program_id );
	if ( get_transient( $rate_key ) ) {
		wp_send_json_error( [ 'message' => 'Bạn vừa gửi đánh giá cho chương trình này. Vui lòng đợi thêm trước khi gửi tiếp.' ] );
	}

	// 5. Sanitize Form Inputs (Accept student_name to prevent WordPress author_name query conflict)
	$author_name = sanitize_text_field( wp_unslash( $_POST['student_name'] ?? $_POST['author_name'] ?? '' ) );
	$phone_raw   = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$phone       = preg_replace( '/[^0-9]/', '', $phone_raw );
	$rating      = isset( $_POST['rating'] ) ? (int) $_POST['rating'] : 5;
	$content     = wp_strip_all_tags( wp_unslash( $_POST['content'] ?? '' ) );
	$status_type = sanitize_key( $_POST['student_status'] ?? 'studying' ); // 'graduated', 'studying', 'interested'
	$batch       = sanitize_text_field( wp_unslash( $_POST['batch'] ?? '' ) );

	if ( empty( $author_name ) || mb_strlen( $author_name ) < 2 ) {
		wp_send_json_error( [ 'message' => 'Vui lòng nhập họ và tên của bạn.' ] );
	}

	// Validate Vietnamese Phone: 10 digits starting with 03, 05, 07, 08, 09
	if ( ! preg_match( '/^(0[3|5|7|8|9])[0-9]{8}$/', $phone ) ) {
		wp_send_json_error( [ 'message' => 'Số điện thoại không đúng định dạng. Vui lòng nhập số điện thoại 10 chữ số.' ] );
	}

	// 5.1 Enforce strict 1 review per person per program (Database lookup by phone & program)
	global $wpdb;
	$existing_review_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT p.ID FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm_prog ON (p.ID = pm_prog.post_id AND pm_prog.meta_key = '_review_program_id')
		 INNER JOIN {$wpdb->postmeta} pm_phone ON (p.ID = pm_phone.post_id AND pm_phone.meta_key = '_review_phone')
		 WHERE p.post_type = 'program_review'
		   AND p.post_status IN ('publish', 'pending', 'draft')
		   AND pm_prog.meta_value = %d
		   AND pm_phone.meta_value = %s
		 LIMIT 1",
		$program_id,
		$phone
	) );

	if ( $existing_review_id ) {
		wp_send_json_error( [
			'message' => 'Số điện thoại này đã gửi đánh giá cho chương trình này rồi. Mỗi học viên chỉ được gửi đánh giá 1 lần.',
		] );
	}

	if ( $rating < 1 || $rating > 5 ) {
		$rating = 5;
	}

	if ( empty( $content ) || mb_strlen( $content ) < 10 ) {
		wp_send_json_error( [ 'message' => 'Vui lòng chia sẻ cảm nhận tối thiểu 10 ký tự.' ] );
	}

	// Sub-criteria ratings
	$criteria_input = $_POST['criteria'] ?? [];
	$criteria_data  = [
		'schedule' => isset( $criteria_input['schedule'] ) ? max( 1, min( 5, (int) $criteria_input['schedule'] ) ) : 5,
		'lecturer' => isset( $criteria_input['lecturer'] ) ? max( 1, min( 5, (int) $criteria_input['lecturer'] ) ) : 5,
		'support'  => isset( $criteria_input['support'] ) ? max( 1, min( 5, (int) $criteria_input['support'] ) ) : 5,
		'tuition'  => isset( $criteria_input['tuition'] ) ? max( 1, min( 5, (int) $criteria_input['tuition'] ) ) : 5,
	];

	// Aspect Tags
	$tags_input = (array) ( $_POST['aspect_tags'] ?? [] );
	$aspect_tags = [];
	$valid_tags = ltdh_get_review_aspect_tags();
	foreach ( $tags_input as $tag_key ) {
		$tag_key = sanitize_key( $tag_key );
		if ( isset( $valid_tags[ $tag_key ] ) ) {
			$aspect_tags[] = $tag_key;
		}
	}

	// Check if this student phone matches an enrolled lead in database (Auto-verify)
	global $wpdb;
	$leads_table = $wpdb->prefix . LTDH_TABLE_LEADS;
	$is_verified = 0;
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$leads_table'" ) === $leads_table ) {
		$lead_match = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $leads_table WHERE phone LIKE %s LIMIT 1",
			'%' . $wpdb->esc_like( substr( $phone, -9 ) )
		) );
		if ( $lead_match ) {
			$is_verified = 1;
		}
	}

	// Create Review Post (Status: pending for editorial safety)
	$program_title = get_the_title( $program_id );
	$post_title    = sprintf( '%s đánh giá: %s (%d★)', $author_name, $program_title, $rating );

	$post_data = [
		'post_title'   => $post_title,
		'post_content' => $content,
		'post_status'  => 'pending',
		'post_type'    => 'program_review',
		'post_author'  => 1,
	];

	$review_id = wp_insert_post( $post_data );

	if ( is_wp_error( $review_id ) || ! $review_id ) {
		wp_send_json_error( [ 'message' => 'Có lỗi xảy ra khi lưu đánh giá. Vui lòng thử lại.' ] );
	}

	// Handle Image Uploads securely and offload to Cloudflare R2
	$uploaded_images = [];
	if ( ! empty( $_FILES['review_images'] ) ) {
		$uploaded_images = ltdh_handle_review_images_upload( $_FILES['review_images'], $program_id, $review_id );
	}

	// Save Post Meta
	update_post_meta( $review_id, '_review_program_id', $program_id );
	update_post_meta( $review_id, '_review_rating', $rating );
	update_post_meta( $review_id, '_review_phone', $phone ); // Admin only, never returned in public API
	update_post_meta( $review_id, '_review_author_name', $author_name );
	update_post_meta( $review_id, '_review_student_status', $status_type );
	update_post_meta( $review_id, '_review_batch', $batch );
	update_post_meta( $review_id, '_review_criteria', $criteria_data );
	update_post_meta( $review_id, '_review_aspect_tags', $aspect_tags );
	update_post_meta( $review_id, '_review_images', $uploaded_images );
	update_post_meta( $review_id, '_review_is_verified', $is_verified );
	update_post_meta( $review_id, '_review_helpful_count', 0 );
	update_post_meta( $review_id, '_review_ip', $user_ip );

	// Set rate limit (1 hour)
	set_transient( $rate_key, 1, HOUR_IN_SECONDS );

	// Set 1-year cookie to remember user already submitted for this program
	setcookie( $cookie_key, '1', time() + ( 86400 * 365 ), COOKIEPATH ?: '/', COOKIE_DOMAIN );

	// Trigger rating stats and schema recalculation
	ltdh_recalculate_program_rating_stats( $program_id );

	wp_send_json_success( [
		'message'     => 'Cảm ơn bạn! Đánh giá đã được gửi thành công. Ban Tuyển Sinh sẽ kiểm duyệt và hiển thị lên chương trình sớm nhất.',
		'is_verified' => $is_verified,
	] );
}

// Endpoint: Fetch Program Reviews with Filters (AJAX Pagination & Tabs)
add_action( 'wp_ajax_ltdh_get_program_reviews', 'ltdh_handle_get_program_reviews' );
add_action( 'wp_ajax_nopriv_ltdh_get_program_reviews', 'ltdh_handle_get_program_reviews' );

function ltdh_handle_get_program_reviews() {
	$program_id = isset( $_GET['program_id'] ) ? (int) $_GET['program_id'] : 0;
	if ( ! $program_id ) {
		wp_send_json_error( [ 'message' => 'Chương trình không hợp lệ.' ] );
	}

	$star       = isset( $_GET['star'] ) ? sanitize_text_field( $_GET['star'] ) : 'all';
	$has_images = ! empty( $_GET['has_images'] );
	$status     = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
	$tag        = isset( $_GET['tag'] ) ? sanitize_key( $_GET['tag'] ) : '';
	$page       = max( 1, (int) ( $_GET['page'] ?? 1 ) );
	$per_page   = 5;

	$meta_query = [
		'relation' => 'AND',
		[
			'key'     => '_review_program_id',
			'value'   => $program_id,
			'compare' => '=',
			'type'    => 'NUMERIC',
		],
	];

	if ( $star !== 'all' && in_array( (int) $star, [ 1, 2, 3, 4, 5 ], true ) ) {
		$meta_query[] = [
			'key'     => '_review_rating',
			'value'   => (int) $star,
			'compare' => '=',
			'type'    => 'NUMERIC',
		];
	}

	if ( ! empty( $status ) && in_array( $status, [ 'graduated', 'studying' ], true ) ) {
		$meta_query[] = [
			'key'     => '_review_student_status',
			'value'   => $status,
			'compare' => '=',
		];
	}

	$args = [
		'post_type'      => 'program_review',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'meta_query'     => $meta_query,
		'orderby'        => 'date',
		'order'          => 'DESC',
	];

	$query = new WP_Query( $args );

	ob_start();
	$rendered_count = 0;

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$rev_id = get_the_ID();

			// Apply client-side tag/images filter if needed
			$imgs = (array) get_post_meta( $rev_id, '_review_images', true );
			if ( $has_images && empty( $imgs ) ) {
				continue;
			}

			$tags = (array) get_post_meta( $rev_id, '_review_aspect_tags', true );
			if ( ! empty( $tag ) && ! in_array( $tag, $tags, true ) ) {
				continue;
			}

			$rendered_count++;
			ltdh_render_review_card( $rev_id );
		}
		wp_reset_postdata();
	}

	$html = ob_get_clean();

	if ( $rendered_count === 0 && $page === 1 ) {
		$html = '<div class="py-12 text-center text-slate-500 bg-slate-50/70 rounded-2xl border border-dashed border-slate-200 p-6">
			<div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
				<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
			</div>
			<p class="font-bold text-slate-700 text-sm">Chưa có đánh giá nào phù hợp với bộ lọc</p>
			<p class="text-xs text-slate-500 mt-1">Hãy thử chọn bộ lọc khác hoặc chia sẻ đánh giá đầu tiên của bạn!</p>
		</div>';
	}

	wp_send_json_success( [
		'html'      => $html,
		'has_more'  => $page < $query->max_num_pages,
		'page'      => $page,
		'max_pages' => $query->max_num_pages,
	] );
}

// Endpoint: Helpful Upvote
add_action( 'wp_ajax_ltdh_like_program_review', 'ltdh_handle_like_program_review' );
add_action( 'wp_ajax_nopriv_ltdh_like_program_review', 'ltdh_handle_like_program_review' );

function ltdh_handle_like_program_review() {
	$review_id = isset( $_POST['review_id'] ) ? (int) $_POST['review_id'] : 0;
	if ( ! $review_id || get_post_type( $review_id ) !== 'program_review' ) {
		wp_send_json_error( [ 'message' => 'Đánh giá không hợp lệ.' ] );
	}

	$cookie_name = 'ltdh_liked_rev_' . $review_id;
	if ( isset( $_COOKIE[ $cookie_name ] ) ) {
		wp_send_json_error( [ 'message' => 'Bạn đã thích đánh giá này rồi.' ] );
	}

	$count = (int) get_post_meta( $review_id, '_review_helpful_count', true );
	$count++;
	update_post_meta( $review_id, '_review_helpful_count', $count );

	// Set cookie for 30 days
	setcookie( $cookie_name, '1', time() + ( 86400 * 30 ), COOKIEPATH ?: '/', COOKIE_DOMAIN );

	wp_send_json_success( [ 'count' => $count ] );
}

// ----------------------------------------------------
// 6. Review Card HTML Renderer
// ----------------------------------------------------
function ltdh_render_review_card( int $review_id ) {
	$author_name = get_post_meta( $review_id, '_review_author_name', true ) ?: 'Học viên ẩn danh';
	$rating      = (int) get_post_meta( $review_id, '_review_rating', true ) ?: 5;
	$status_type = get_post_meta( $review_id, '_review_student_status', true ) ?: 'studying';
	$batch       = get_post_meta( $review_id, '_review_batch', true ) ?: '';
	$is_verified = (int) get_post_meta( $review_id, '_review_is_verified', true ) === 1;
	$aspect_tags = (array) get_post_meta( $review_id, '_review_aspect_tags', true );
	$images      = (array) get_post_meta( $review_id, '_review_images', true );
	$reply       = get_post_meta( $review_id, '_review_official_reply', true );
	$likes       = (int) get_post_meta( $review_id, '_review_helpful_count', true );
	$date        = get_the_date( 'd/m/Y', $review_id );
	$time_ago    = human_time_diff( get_the_time( 'U', $review_id ), current_time( 'timestamp' ) ) . ' trước';

	// Mask author name for privacy (e.g. "Nguyễn Văn Tuấn" -> "Nguyễn V*** Tuấn")
	$masked_name = ltdh_mask_student_name( $author_name );
	$initial     = mb_substr( trim( $author_name ), 0, 1, 'UTF-8' );

	$status_label = 'Học viên';
	if ( $status_type === 'graduated' ) {
		$status_label = 'Cựu sinh viên đã tốt nghiệp';
	} elseif ( $status_type === 'studying' ) {
		$status_label = 'Học viên đang theo học';
	}

	$all_aspect_labels = ltdh_get_review_aspect_tags();
	?>
	<div class="ltdh-review-item py-5 border-b border-slate-100 last:border-b-0" data-review-id="<?php echo esc_attr( $review_id ); ?>">
		<div class="flex items-start gap-3 sm:gap-4">
			<!-- Avatar with Initial & Gradient -->
			<div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-bold text-sm sm:text-base flex items-center justify-center shrink-0 shadow-xs uppercase select-none">
				<?php echo esc_html( $initial ); ?>
			</div>

			<div class="min-w-0 flex-1">
				<!-- Author Header & Verified Badge -->
				<div class="flex flex-wrap items-center gap-x-2 gap-y-1 mb-1">
					<span class="font-bold text-slate-900 text-sm sm:text-base leading-tight">
						<?php echo esc_html( $masked_name ); ?>
					</span>

					<?php if ( $is_verified ) : ?>
						<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
							<svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
							<span>Đã xác thực học viên</span>
						</span>
					<?php endif; ?>

					<span class="text-xs text-slate-400">• <?php echo esc_html( $time_ago ); ?></span>
				</div>

				<!-- Student Sub-info (Khóa / Tình trạng) -->
				<div class="text-[12px] text-slate-500 mb-2 flex items-center gap-1.5 flex-wrap">
					<span class="font-medium text-slate-600"><?php echo esc_html( $status_label ); ?></span>
					<?php if ( ! empty( $batch ) ) : ?>
						<span>•</span>
						<span class="text-slate-500 font-medium"><?php echo esc_html( $batch ); ?></span>
					<?php endif; ?>
				</div>

				<!-- Stars Rating -->
				<div class="flex items-center gap-1 mb-2">
					<div class="flex items-center text-amber-400">
						<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
							<?php if ( $s <= $rating ) : ?>
								<svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
							<?php else : ?>
								<svg class="w-4 h-4 text-slate-200 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
							<?php endif; ?>
						<?php endfor; ?>
					</div>
					<span class="text-xs font-bold text-slate-700 ml-1"><?php echo esc_html( $rating ); ?>.0</span>
				</div>

				<!-- Aspect Tags (Pills) -->
				<?php if ( ! empty( $aspect_tags ) ) : ?>
					<div class="flex flex-wrap gap-1.5 mb-2.5">
						<?php foreach ( $aspect_tags as $t_slug ) :
							if ( isset( $all_aspect_labels[ $t_slug ] ) ) : ?>
								<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
									# <?php echo esc_html( $all_aspect_labels[ $t_slug ] ); ?>
								</span>
							<?php endif;
						endforeach; ?>
					</div>
				<?php endif; ?>

				<!-- Review Content Text -->
				<div class="text-sm text-slate-700 leading-relaxed font-normal mb-3">
					<?php echo nl2br( esc_html( get_the_content( null, false, $review_id ) ) ); ?>
				</div>

				<!-- Uploaded Proof Media Gallery -->
				<?php if ( ! empty( $images ) ) : ?>
					<div class="flex items-center gap-2 mb-3 overflow-x-auto py-1">
						<?php foreach ( $images as $img ) :
							$img_url   = '';
							$img_thumb = '';
							if ( is_array( $img ) ) {
								if ( ! empty( $img['id'] ) ) {
									$img_url   = wp_get_attachment_image_url( (int) $img['id'], 'full' ) ?: ( $img['url'] ?? '' );
									$img_thumb = wp_get_attachment_image_url( (int) $img['id'], 'thumbnail' ) ?: ( $img['thumb'] ?? $img_url );
								} else {
									$img_url   = $img['url'] ?? '';
									$img_thumb = $img['thumb'] ?? $img_url;
								}
							} elseif ( is_string( $img ) ) {
								$img_url   = $img;
								$img_thumb = $img;
							}
							if ( empty( $img_url ) ) {
								continue;
							}
						?>
							<a href="<?php echo esc_url( $img_url ); ?>" 
							   target="_blank" 
							   rel="noopener" 
							   class="ltdh-review-img-link block w-18 h-18 sm:w-20 sm:h-20 rounded-xl overflow-hidden border border-slate-200/80 shadow-2xs hover:opacity-90 transition-opacity shrink-0 bg-slate-100"
							   title="Xem ảnh cỡ lớn">
								<img src="<?php echo esc_url( $img_thumb ); ?>" 
								     alt="Hình ảnh đánh giá thực tế" 
								     loading="lazy" 
								     width="80" 
								     height="80" 
								     class="w-full h-full object-cover">
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<!-- Official Board Response (Shop reply like Shopee) -->
				<?php if ( ! empty( $reply ) ) : ?>
					<div class="mt-3 p-3 sm:p-3.5 bg-blue-50/70 border border-blue-100 rounded-xl text-xs sm:text-sm text-slate-700">
						<div class="flex items-center gap-1.5 font-bold text-[#00308b] mb-1">
							<svg class="w-4 h-4 text-[#00308b]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
							<span>Phản hồi từ Ban Tuyển Sinh:</span>
						</div>
						<div class="leading-relaxed text-slate-600">
							<?php echo nl2br( esc_html( $reply ) ); ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Helpful Upvote Button -->
				<div class="flex items-center justify-between mt-3 text-xs text-slate-400">
					<button type="button" 
					        class="ltdh-like-review-btn inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-[#00308b] font-medium transition-colors cursor-pointer"
					        data-review-id="<?php echo esc_attr( $review_id ); ?>">
						<svg class="w-4 h-4 text-slate-400 group-hover:text-[#00308b]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
						<span>Hữu ích</span>
						<span class="ltdh-like-count font-bold text-slate-700">(<?php echo esc_html( $likes ); ?>)</span>
					</button>

					<span class="text-[11px] text-slate-400">Ngày đăng: <?php echo esc_html( $date ); ?></span>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Mask student full name for public privacy protection.
 * e.g., "Nguyễn Văn Tuấn" -> "Nguyễn V*** Tuấn"
 *
 * @param string $name
 * @return string
 */
function ltdh_mask_student_name( string $name ): string {
	$parts = explode( ' ', trim( $name ) );
	$count = count( $parts );
	if ( $count <= 1 ) {
		return $name;
	}
	if ( $count === 2 ) {
		return $parts[0] . ' ' . mb_substr( $parts[1], 0, 1, 'UTF-8' ) . '***';
	}
	// 3 or more words: mask middle word(s)
	$masked = $parts[0] . ' ';
	for ( $i = 1; $i < $count - 1; $i++ ) {
		$masked .= mb_substr( $parts[ $i ], 0, 1, 'UTF-8' ) . '*** ';
	}
	$masked .= $parts[ $count - 1 ];
	return $masked;
}

// ----------------------------------------------------
// 7. Google Schema JSON-LD Generator
// ----------------------------------------------------
function ltdh_get_program_review_schema_ld( int $program_id ): ?array {
	$stats = ltdh_get_program_rating_stats( $program_id );
	if ( empty( $stats['total'] ) || (int) $stats['total'] === 0 ) {
		return null;
	}

	// Explicit program name formula for SEO & Schema (e.g. "Liên thông ngành Logistics hệ Đào tạo từ xa")
	$discipline = function_exists( 'ltdh_get_program_discipline_name' ) ? ltdh_get_program_discipline_name( $program_id ) : '';
	if ( empty( $discipline ) ) {
		$raw_title  = get_the_title( $program_id );
		$discipline = preg_replace( '/^(?:cử nhân|kỹ sư|ngành|chuyên ngành|liên thông(?:\s+đại học)?)\s+/iu', '', trim( (string) $raw_title ) );
	}
	$discipline = preg_replace( '/^(?:ngành|chuyên ngành)\s+/iu', '', trim( $discipline ) );

	$training_types = get_the_terms( $program_id, 'training_type' );
	$training_type  = ( ! empty( $training_types ) && ! is_wp_error( $training_types ) ) ? $training_types[0]->name : '';
	$clean_type     = preg_replace( '/^hệ\s+/iu', '', trim( (string) $training_type ) );

	$program_name = 'Liên thông ngành ' . ( $discipline ?: get_the_title( $program_id ) );
	if ( ! empty( $clean_type ) ) {
		$program_name .= ' hệ ' . $clean_type;
	}

	$school_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $program_id ) : 0;
	if ( ! $school_id ) {
		$school_rel = get_field( 'school_relationship', $program_id ) ?: get_post_meta( $program_id, 'school_relationship', true );
		if ( is_array( $school_rel ) ) {
			$school_id = ! empty( $school_rel[0] ) ? ( is_object( $school_rel[0] ) ? $school_rel[0]->ID : (int) $school_rel[0] ) : 0;
		} elseif ( is_numeric( $school_rel ) ) {
			$school_id = (int) $school_rel;
		}
	}

	$school_name = $school_id ? get_the_title( $school_id ) : '';

	$schema = [
		'@context'        => 'https://schema.org',
		'@type'           => 'Course',
		'name'            => $program_name,
		'description'     => wp_strip_all_tags( get_the_excerpt( $program_id ) ?: ( $program_name . ( $school_name ? ' tại ' . $school_name : '' ) ) ),
		'url'             => get_permalink( $program_id ),
		'aggregateRating' => [
			'@type'       => 'AggregateRating',
			'ratingValue' => (string) $stats['average'],
			'reviewCount' => (string) $stats['total'],
			'bestRating'  => '5',
			'worstRating' => '1',
		],
	];

	if ( ! empty( $school_name ) ) {
		$schema['provider'] = [
			'@type'  => 'CollegeOrUniversity',
			'name'   => $school_name,
			'sameAs' => get_permalink( $school_id ),
		];
	}

	// Top published reviews for Google Rich Snippets
	$recent_reviews = get_posts( [
		'post_type'      => 'program_review',
		'post_status'    => 'publish',
		'posts_per_page' => 5,
		'meta_query'     => [
			[
				'key'     => '_review_program_id',
				'value'   => $program_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			],
		],
		'orderby'        => 'date',
		'order'          => 'DESC',
	] );

	if ( ! empty( $recent_reviews ) ) {
		$schema['review'] = [];
		foreach ( $recent_reviews as $r_post ) {
			$author_name = get_post_meta( $r_post->ID, '_review_author_name', true ) ?: 'Học viên ẩn danh';
			$r_rating    = (int) get_post_meta( $r_post->ID, '_review_rating', true ) ?: 5;
			$r_date      = get_the_date( 'Y-m-d', $r_post->ID );
			$r_content   = wp_strip_all_tags( $r_post->post_content );

			$schema['review'][] = [
				'@type'         => 'Review',
				'author'        => [
					'@type' => 'Person',
					'name'  => ltdh_mask_student_name( $author_name ),
				],
				'datePublished' => $r_date,
				'reviewBody'    => $r_content,
				'reviewRating'  => [
					'@type'       => 'Rating',
					'ratingValue' => (string) $r_rating,
					'bestRating'  => '5',
					'worstRating' => '1',
				],
			];
		}
	}

	return $schema;
}

// ----------------------------------------------------
// 8. Custom Admin Columns, Quick Actions & Meta Boxes
// ----------------------------------------------------
add_filter( 'manage_program_review_posts_columns', 'ltdh_program_review_custom_columns' );
function ltdh_program_review_custom_columns( $columns ) {
	$new = [];
	$new['cb']           = $columns['cb'];
	$new['title']        = 'Tiêu đề';
	$new['author_info']  = 'Học viên & SĐT';
	$new['program_ref']  = 'Chương trình đào tạo';
	$new['rating']       = 'Điểm số';
	$new['quick_action'] = 'Duyệt nhanh';
	$new['verified']     = 'Xác thực';
	$new['date']         = 'Ngày gửi';
	return $new;
}

add_action( 'manage_program_review_posts_custom_column', 'ltdh_program_review_column_content', 10, 2 );
function ltdh_program_review_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'author_info':
			$name   = get_post_meta( $post_id, '_review_author_name', true );
			$phone  = get_post_meta( $post_id, '_review_phone', true );
			$status = get_post_meta( $post_id, '_review_student_status', true );
			echo '<strong>' . esc_html( $name ) . '</strong><br>';
			if ( $phone ) {
				echo '<span style="color:#00308b; font-family:monospace;">' . esc_html( $phone ) . '</span> ';
				echo '<a href="https://zalo.me/' . esc_attr( $phone ) . '" target="_blank" style="color:#0084ff; text-decoration:none;">[Zalo]</a><br>';
			}
			$status_text = $status === 'graduated' ? '🎓 Đã tốt nghiệp' : ( $status === 'studying' ? '📚 Đang học' : 'Người quan tâm' );
			echo '<span style="font-size:11px; color:#666;">' . esc_html( $status_text ) . '</span>';
			break;

		case 'program_ref':
			$pid = (int) get_post_meta( $post_id, '_review_program_id', true );
			if ( $pid && get_post( $pid ) ) {
				$edit_link = get_edit_post_link( $pid );
				$view_link = get_permalink( $pid );
				echo '<div style="margin-bottom: 5px;">';
				echo '<a href="' . esc_url( $edit_link ) . '" target="_blank" style="font-weight: 600; color: #1e293b; text-decoration: none;">' . esc_html( get_the_title( $pid ) ) . '</a>';
				echo '</div>';
				if ( $view_link ) {
					echo '<a href="' . esc_url( $view_link . '#danh-gia' ) . '" target="_blank" class="button button-small" style="display: inline-flex; align-items: center; gap: 3px; font-size: 11px; padding: 0 8px; height: 24px; line-height: 22px; color: #00308b; border-color: #cbd5e1; background: #f8fafc;" title="Xem phần đánh giá của chương trình trên website">';
					echo '<span style="display:inline-block; font-size:12px;">↗</span> Xem bài viết';
					echo '</a>';
				}
			} else {
				echo '<span style="color:#999;">Không xác định</span>';
			}
			break;

		case 'quick_action':
			$post_status = get_post_status( $post_id );
			if ( $post_status === 'pending' ) {
				$approve_url = wp_nonce_url(
					admin_url( 'admin-post.php?action=ltdh_quick_approve_review&post_id=' . $post_id ),
					'ltdh_quick_approve_' . $post_id
				);
				echo '<a href="' . esc_url( $approve_url ) . '" class="button button-small button-primary" style="background: #059669; border-color: #047857; color: #fff; font-weight: 600; font-size: 11px; height: 26px; line-height: 24px; padding: 0 10px;" title="Duyệt xuất bản ngay lập tức">';
				echo '✓ Duyệt ngay';
				echo '</a>';
			} elseif ( $post_status === 'publish' ) {
				$unapprove_url = wp_nonce_url(
					admin_url( 'admin-post.php?action=ltdh_quick_unapprove_review&post_id=' . $post_id ),
					'ltdh_quick_unapprove_' . $post_id
				);
				echo '<span style="display: inline-block; color: #059669; font-weight: 600; font-size: 12px; margin-bottom: 2px;">✓ Đã xuất bản</span><br>';
				echo '<a href="' . esc_url( $unapprove_url ) . '" style="color: #94a3b8; font-size: 11px; text-decoration: none;" title="Chuyển về trạng thái chờ duyệt">(Bỏ duyệt)</a>';
			} else {
				echo '<span style="color: #64748b; font-size: 11px;">' . esc_html( ucfirst( $post_status ) ) . '</span>';
			}
			break;

		case 'rating':
			$r = (int) get_post_meta( $post_id, '_review_rating', true );
			echo '<span style="color:#f59e0b; font-weight:bold; font-size:14px;">' . str_repeat( '★', $r ) . '</span> (' . esc_html( $r ) . '/5)';
			break;

		case 'verified':
			$v = (int) get_post_meta( $post_id, '_review_is_verified', true );
			if ( $v === 1 ) {
				echo '<span style="color:#059669; font-weight:bold;">✓ Đã xác thực</span>';
			} else {
				echo '<span style="color:#999;">Chưa</span>';
			}
			break;
	}
}

// Add Quick Approve and View Program action links in list table row actions
add_filter( 'post_row_actions', 'ltdh_program_review_row_actions', 10, 2 );
function ltdh_program_review_row_actions( $actions, $post ) {
	if ( $post->post_type !== 'program_review' ) {
		return $actions;
	}

	$pid = (int) get_post_meta( $post->ID, '_review_program_id', true );

	// 1. Quick Approve / Unapprove row action
	if ( $post->post_status === 'pending' && current_user_can( 'edit_post', $post->ID ) ) {
		$approve_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=ltdh_quick_approve_review&post_id=' . $post->ID ),
			'ltdh_quick_approve_' . $post->ID
		);
		$actions['quick_approve'] = sprintf(
			'<a href="%s" style="color: #059669; font-weight: bold;">✓ Duyệt nhanh</a>',
			esc_url( $approve_url )
		);
	} elseif ( $post->post_status === 'publish' && current_user_can( 'edit_post', $post->ID ) ) {
		$unapprove_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=ltdh_quick_unapprove_review&post_id=' . $post->ID ),
			'ltdh_quick_unapprove_' . $post->ID
		);
		$actions['quick_unapprove'] = sprintf(
			'<a href="%s" style="color: #d97706;">Bỏ duyệt</a>',
			esc_url( $unapprove_url )
		);
	}

	// 2. Direct link to view program review section on website
	if ( $pid && get_post( $pid ) ) {
		$view_url = get_permalink( $pid );
		if ( $view_url ) {
			$actions['view_program'] = sprintf(
				'<a href="%s" target="_blank" style="color: #00308b; font-weight: 600;">Xem bài viết ↗</a>',
				esc_url( $view_url . '#danh-gia' )
			);
		}
	}

	return $actions;
}

// Handlers for Admin Quick Approve & Unapprove
add_action( 'admin_post_ltdh_quick_approve_review', 'ltdh_handle_quick_approve_review' );
function ltdh_handle_quick_approve_review() {
	$post_id = isset( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 0;
	if ( ! $post_id || get_post_type( $post_id ) !== 'program_review' ) {
		wp_die( 'Đánh giá không hợp lệ.' );
	}

	check_admin_referer( 'ltdh_quick_approve_' . $post_id );

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( 'Bạn không có quyền duyệt đánh giá này.' );
	}

	wp_update_post( [
		'ID'          => $post_id,
		'post_status' => 'publish',
	] );

	$program_id = (int) get_post_meta( $post_id, '_review_program_id', true );
	if ( $program_id ) {
		ltdh_recalculate_program_rating_stats( $program_id );
	}

	$redirect_to = isset( $_GET['redirect_to'] ) && $_GET['redirect_to'] === 'edit'
		? get_edit_post_link( $post_id, 'url' )
		: admin_url( 'edit.php?post_type=program_review' );

	$redirect_to = add_query_arg( 'ltdh_approved', '1', $redirect_to );
	wp_safe_redirect( $redirect_to );
	exit;
}

add_action( 'admin_post_ltdh_quick_unapprove_review', 'ltdh_handle_quick_unapprove_review' );
function ltdh_handle_quick_unapprove_review() {
	$post_id = isset( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 0;
	if ( ! $post_id || get_post_type( $post_id ) !== 'program_review' ) {
		wp_die( 'Đánh giá không hợp lệ.' );
	}

	check_admin_referer( 'ltdh_quick_unapprove_' . $post_id );

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( 'Bạn không có quyền thao tác đánh giá này.' );
	}

	wp_update_post( [
		'ID'          => $post_id,
		'post_status' => 'pending',
	] );

	$program_id = (int) get_post_meta( $post_id, '_review_program_id', true );
	if ( $program_id ) {
		ltdh_recalculate_program_rating_stats( $program_id );
	}

	$redirect_to = add_query_arg( 'ltdh_unapproved', '1', admin_url( 'edit.php?post_type=program_review' ) );
	wp_safe_redirect( $redirect_to );
	exit;
}

// Admin Flash Notices
add_action( 'admin_notices', 'ltdh_review_admin_notices' );
function ltdh_review_admin_notices() {
	global $pagenow, $typenow;
	if ( $typenow === 'program_review' ) {
		if ( ! empty( $_GET['ltdh_approved'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p><strong>✓ Đã duyệt xuất bản đánh giá thành công!</strong> Điểm số và mã Google Schema trên trang chương trình đã được cập nhật.</p></div>';
		}
		if ( ! empty( $_GET['ltdh_unapproved'] ) ) {
			echo '<div class="notice notice-warning is-dismissible"><p>Đã chuyển đánh giá về trạng thái Chờ duyệt (Pending).</p></div>';
		}
	}
}

// Meta Box for Review Details & Official Reply in WP Admin
add_action( 'add_meta_boxes', 'ltdh_add_program_review_metaboxes' );
function ltdh_add_program_review_metaboxes() {
	add_meta_box(
		'ltdh_review_details_box',
		'Chi tiết Đánh giá & Phản hồi Nhà trường',
		'ltdh_render_review_metabox_html',
		'program_review',
		'normal',
		'high'
	);
}

function ltdh_render_review_metabox_html( $post ) {
	wp_nonce_field( 'ltdh_save_review_meta', 'ltdh_review_admin_nonce' );

	$program_id  = (int) get_post_meta( $post->ID, '_review_program_id', true );
	$rating      = (int) get_post_meta( $post->ID, '_review_rating', true ) ?: 5;
	$author_name = get_post_meta( $post->ID, '_review_author_name', true );
	$phone       = get_post_meta( $post->ID, '_review_phone', true );
	$status_type = get_post_meta( $post->ID, '_review_student_status', true ) ?: 'studying';
	$batch       = get_post_meta( $post->ID, '_review_batch', true );
	$is_verified = (int) get_post_meta( $post->ID, '_review_is_verified', true );
	$reply       = get_post_meta( $post->ID, '_review_official_reply', true );
	$images      = (array) get_post_meta( $post->ID, '_review_images', true );

	// Programs list for dropdown
	$programs = get_posts( [
		'post_type'      => 'program',
		'posts_per_page' => 100,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
	] );
	?>
	<?php if ( $post->post_status === 'pending' ) : 
		$approve_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=ltdh_quick_approve_review&post_id=' . $post->ID . '&redirect_to=edit' ),
			'ltdh_quick_approve_' . $post->ID
		);
	?>
		<div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
			<div style="color: #065f46; font-size: 13px;">
				<strong style="display: block; font-size: 14px; margin-bottom: 2px;">⏳ Đánh giá này đang ở trạng thái Chờ duyệt</strong>
				Đánh giá chưa hiển thị trên trang web và chưa được tính vào Google Schema.
			</div>
			<a href="<?php echo esc_url( $approve_url ); ?>" class="button button-primary" style="background: #059669; border-color: #047857; font-weight: bold; font-size: 13px; padding: 6px 16px; height: auto; text-decoration: none;">
				✓ Duyệt & Xuất bản ngay
			</a>
		</div>
	<?php endif; ?>

	<table class="form-table" style="max-width: 800px;">
		<tr>
			<th scope="row"><label for="ltdh_admin_program_id">Chương trình đào tạo:</label></th>
			<td>
				<div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
					<select name="ltdh_admin_program_id" id="ltdh_admin_program_id" style="width: 100%; max-width: 450px;">
						<option value="">-- Chọn chương trình --</option>
						<?php foreach ( $programs as $p ) : ?>
							<option value="<?php echo esc_attr( $p->ID ); ?>" <?php selected( $program_id, $p->ID ); ?>>
								<?php echo esc_html( function_exists( 'ltdh_format_program_select_label' ) ? ltdh_format_program_select_label( $p->ID ) : $p->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<?php if ( $program_id && get_post( $program_id ) ) : 
						$view_url = get_permalink( $program_id );
					?>
						<a href="<?php echo esc_url( $view_url . '#danh-gia' ); ?>" target="_blank" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 4px; font-weight: 600; color: #00308b;" title="Mở trang chi tiết chương trình trên website và cuộn tới mục Đánh giá">
							<span style="font-size: 13px;">↗</span> Xem bài viết trên web
						</a>
					<?php endif; ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="ltdh_admin_rating">Số sao đánh giá:</label></th>
			<td>
				<select name="ltdh_admin_rating" id="ltdh_admin_rating">
					<option value="5" <?php selected( $rating, 5 ); ?>>5 Sao (⭐⭐⭐⭐⭐)</option>
					<option value="4" <?php selected( $rating, 4 ); ?>>4 Sao (⭐⭐⭐⭐)</option>
					<option value="3" <?php selected( $rating, 3 ); ?>>3 Sao (⭐⭐⭐)</option>
					<option value="2" <?php selected( $rating, 2 ); ?>>2 Sao (⭐⭐)</option>
					<option value="1" <?php selected( $rating, 1 ); ?>>1 Sao (⭐)</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="ltdh_admin_author_name">Họ và tên học viên:</label></th>
			<td><input type="text" name="ltdh_admin_author_name" id="ltdh_admin_author_name" value="<?php echo esc_attr( $author_name ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th scope="row"><label for="ltdh_admin_phone">Số điện thoại:</label></th>
			<td>
				<input type="text" name="ltdh_admin_phone" id="ltdh_admin_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text">
				<?php if ( $phone ) : ?>
					<a href="https://zalo.me/<?php echo esc_attr( $phone ); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">Nhắn tin Zalo</a>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="ltdh_admin_student_status">Tình trạng học viên:</label></th>
			<td>
				<select name="ltdh_admin_student_status" id="ltdh_admin_student_status">
					<option value="graduated" <?php selected( $status_type, 'graduated' ); ?>>Cựu sinh viên đã tốt nghiệp</option>
					<option value="studying" <?php selected( $status_type, 'studying' ); ?>>Học viên đang theo học</option>
					<option value="interested" <?php selected( $status_type, 'interested' ); ?>>Học viên mới / Người quan tâm</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="ltdh_admin_batch">Khóa học / Niên khóa:</label></th>
			<td><input type="text" name="ltdh_admin_batch" id="ltdh_admin_batch" value="<?php echo esc_attr( $batch ); ?>" placeholder="VD: Khóa K2022" class="regular-text"></td>
		</tr>
		<tr>
			<th scope="row">Xác thực sinh viên:</th>
			<td>
				<label>
					<input type="checkbox" name="ltdh_admin_is_verified" value="1" <?php checked( $is_verified, 1 ); ?>>
					<strong>Gắn huy hiệu [✓ Đã xác thực sinh viên]</strong> (Tăng uy tín trên website)
				</label>
			</td>
		</tr>
		<?php if ( ! empty( $images ) ) : ?>
			<tr>
				<th scope="row">Hình ảnh đính kèm:</th>
				<td>
					<div style="display: flex; gap: 10px; flex-wrap: wrap;">
						<?php foreach ( $images as $img ) :
							$url = '';
							if ( is_array( $img ) ) {
								if ( ! empty( $img['id'] ) ) {
									$url = wp_get_attachment_image_url( (int) $img['id'], 'full' ) ?: ( $img['url'] ?? '' );
								} else {
									$url = $img['url'] ?? '';
								}
							} else {
								$url = (string) $img;
							}
							if ( $url ) : ?>
								<a href="<?php echo esc_url( $url ); ?>" target="_blank">
									<img src="<?php echo esc_url( $url ); ?>" style="width: 90px; height: 90px; object-fit: cover; border-radius: 8px; border: 1px solid #ccc;">
								</a>
							<?php endif;
						endforeach; ?>
					</div>
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<th scope="row"><label for="ltdh_admin_official_reply">Phản hồi từ Ban Tuyển Sinh:</label></th>
			<td>
				<textarea name="ltdh_admin_official_reply" id="ltdh_admin_official_reply" rows="4" style="width: 100%; max-width: 500px;" placeholder="Ví dụ: Cảm ơn bạn Tuấn đã tin tưởng đồng hành cùng chương trình. Chúc bạn thành công!"><?php echo esc_textarea( $reply ); ?></textarea>
				<p class="description">Phản hồi này sẽ hiển thị bên dưới đánh giá của học viên trên website (như phản hồi của Shop trên Shopee).</p>
			</td>
		</tr>
	</table>
	<?php
}

add_action( 'save_post_program_review', 'ltdh_save_program_review_metabox', 15 );
function ltdh_save_program_review_metabox( $post_id ) {
	if ( ! isset( $_POST['ltdh_review_admin_nonce'] ) || ! wp_verify_nonce( $_POST['ltdh_review_admin_nonce'], 'ltdh_save_review_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['ltdh_admin_program_id'] ) ) {
		update_post_meta( $post_id, '_review_program_id', (int) $_POST['ltdh_admin_program_id'] );
	}
	if ( isset( $_POST['ltdh_admin_rating'] ) ) {
		update_post_meta( $post_id, '_review_rating', max( 1, min( 5, (int) $_POST['ltdh_admin_rating'] ) ) );
	}
	if ( isset( $_POST['ltdh_admin_author_name'] ) ) {
		update_post_meta( $post_id, '_review_author_name', sanitize_text_field( $_POST['ltdh_admin_author_name'] ) );
	}
	if ( isset( $_POST['ltdh_admin_phone'] ) ) {
		update_post_meta( $post_id, '_review_phone', sanitize_text_field( $_POST['ltdh_admin_phone'] ) );
	}
	if ( isset( $_POST['ltdh_admin_student_status'] ) ) {
		update_post_meta( $post_id, '_review_student_status', sanitize_key( $_POST['ltdh_admin_student_status'] ) );
	}
	if ( isset( $_POST['ltdh_admin_batch'] ) ) {
		update_post_meta( $post_id, '_review_batch', sanitize_text_field( $_POST['ltdh_admin_batch'] ) );
	}

	$is_verified = ! empty( $_POST['ltdh_admin_is_verified'] ) ? 1 : 0;
	update_post_meta( $post_id, '_review_is_verified', $is_verified );

	if ( isset( $_POST['ltdh_admin_official_reply'] ) ) {
		update_post_meta( $post_id, '_review_official_reply', sanitize_textarea_field( $_POST['ltdh_admin_official_reply'] ) );
	}
}

/**
 * Clean up attached images when a review is deleted (also purges from Cloudflare R2 via offloader)
 */
add_action( 'before_delete_post', 'ltdh_cleanup_review_attachments', 10, 2 );
function ltdh_cleanup_review_attachments( $post_id, $post ) {
	if ( $post && $post->post_type === 'program_review' ) {
		$images = get_post_meta( $post_id, '_review_images', true );
		if ( is_array( $images ) ) {
			foreach ( $images as $img ) {
				if ( is_array( $img ) && ! empty( $img['id'] ) ) {
					wp_delete_attachment( (int) $img['id'], true );
				}
			}
		}
	}
}
