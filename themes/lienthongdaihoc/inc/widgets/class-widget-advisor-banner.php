<?php
/**
 * Widget: Admissions Advisor Banner (Dynamic Switching by School & Major).
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTDH_Widget_Advisor_Banner extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'ltdh_advisor_banner',
			__( '[LTDH] Banner Thầy/Cô Tư Vấn (Theo Trường & Ngành)', 'ltdh' ),
			[
				'description'                 => __( 'Hiển thị ảnh banner tư vấn (Thầy Đạt hoặc người hợp tác). Chỉ cần chọn Trường là tất cả ngành thuộc trường sẽ tự động theo. Click ảnh nhảy sang Zalo SĐT của từng người.', 'ltdh' ),
				'customize_selective_refresh' => true,
			]
		);
	}

	public function widget( $args, $instance ) {
		$title             = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$display_mode      = ! empty( $instance['display_mode'] ) ? $instance['display_mode'] : 'auto_by_school';
		$open_new_tab      = ! isset( $instance['open_new_tab'] ) || ! empty( $instance['open_new_tab'] );
		$rounded_corners   = ! empty( $instance['rounded_corners'] ) ? $instance['rounded_corners'] : 'rounded-2xl';

		// Advisor 1 (Default: Thầy Đạt)
		$default_image_1   = home_url( '/wp-content/uploads/2026/10/banner-thay-dat-cropped.webp' );
		$advisor_1_name    = ! empty( $instance['advisor_1_name'] ) ? $instance['advisor_1_name'] : 'Thầy Đạt';
		$advisor_1_phone   = ! empty( $instance['advisor_1_phone'] ) ? $instance['advisor_1_phone'] : '0988 991 496';
		$advisor_1_zalo    = ! empty( $instance['advisor_1_zalo'] ) ? $instance['advisor_1_zalo'] : '';
		$advisor_1_image   = ! empty( $instance['advisor_1_image'] ) ? $instance['advisor_1_image'] : $default_image_1;
		$advisor_1_alt     = ! empty( $instance['advisor_1_alt'] ) ? $instance['advisor_1_alt'] : ( 'Tư vấn cùng ' . $advisor_1_name );

		// Advisor 2 (Partner)
		$advisor_2_name    = ! empty( $instance['advisor_2_name'] ) ? $instance['advisor_2_name'] : 'Chuyên viên tư vấn';
		$advisor_2_phone   = ! empty( $instance['advisor_2_phone'] ) ? $instance['advisor_2_phone'] : '';
		$advisor_2_zalo    = ! empty( $instance['advisor_2_zalo'] ) ? $instance['advisor_2_zalo'] : '';
		$advisor_2_image   = ! empty( $instance['advisor_2_image'] ) ? $instance['advisor_2_image'] : '';
		$advisor_2_alt     = ! empty( $instance['advisor_2_alt'] ) ? $instance['advisor_2_alt'] : ( 'Tư vấn cùng ' . $advisor_2_name );
		$advisor_2_schools = ! empty( $instance['advisor_2_schools'] ) && is_array( $instance['advisor_2_schools'] ) ? array_map( 'absint', $instance['advisor_2_schools'] ) : [];
		$advisor_2_majors  = ! empty( $instance['advisor_2_majors'] ) && is_array( $instance['advisor_2_majors'] ) ? array_map( 'absint', $instance['advisor_2_majors'] ) : [];

		// Context Detection
		$current_school_id  = ltdh_detect_current_school_id();
		$current_major_id   = ltdh_detect_current_major_id();
		$current_program_id = function_exists( 'ltdh_detect_current_program_id' ) ? ltdh_detect_current_program_id() : 0;

		// Determine Active Advisor (1 or 2)
		$active_advisor = 1;

		if ( 'always_1' === $display_mode ) {
			$active_advisor = 1;
		} elseif ( 'always_2' === $display_mode && ! empty( $advisor_2_image ) ) {
			$active_advisor = 2;
		} elseif ( 'random' === $display_mode && ! empty( $advisor_2_image ) ) {
			$active_advisor = ( wp_rand( 1, 2 ) === 2 ) ? 2 : 1;
		} else {
			// auto_by_school (Default): School assignment takes precedence, but Program / Major overrides win!
			$is_advisor_2 = false;

			// 0. Program override context (Chương trình đào tạo)
			$program_override = $current_program_id > 0 ? get_post_meta( $current_program_id, '_ltdh_assigned_advisor', true ) : '';
			if ( 'advisor_2' === $program_override ) {
				$is_advisor_2 = true;
			} elseif ( 'advisor_1' === $program_override ) {
				$is_advisor_2 = false;
			} else {
				// Program chưa setup (hoặc 'default') -> ĂN THEO TRƯỜNG!
				// 1. Direct school context (Trang trường, hoặc Chương trình thuộc trường, hoặc URL ?truong=...)
				if ( $current_school_id > 0 ) {
					$school_override = get_post_meta( $current_school_id, '_ltdh_assigned_advisor', true );
					if ( 'advisor_2' === $school_override ) {
						$is_advisor_2 = true;
					} elseif ( 'advisor_1' === $school_override ) {
						$is_advisor_2 = false;
					} elseif ( ! empty( $advisor_2_schools ) && in_array( $current_school_id, $advisor_2_schools, true ) ) {
						$is_advisor_2 = true;
					}
				}

				// 2. Major context: Nếu đang ở trang Ngành học (single-major)
				if ( ! $is_advisor_2 && $current_major_id > 0 && ! $current_program_id ) {
					$major_override = get_post_meta( $current_major_id, '_ltdh_assigned_advisor', true );
					if ( 'advisor_2' === $major_override ) {
						$is_advisor_2 = true;
					} elseif ( 'advisor_1' === $major_override ) {
						$is_advisor_2 = false;
					} elseif ( ! empty( $advisor_2_majors ) && in_array( $current_major_id, $advisor_2_majors, true ) ) {
						$is_advisor_2 = true;
					} else {
						// "Tất cả ngành thuộc trường sẽ phải theo":
						// Kiểm tra xem các trường đào tạo ngành này có trường nào nằm trong danh sách trường của Người 2 không!
						if ( ! empty( $advisor_2_schools ) ) {
							$offering_schools = ltdh_get_schools_for_major( $current_major_id );
							$intersect = array_intersect( $offering_schools, $advisor_2_schools );
							if ( ! empty( $intersect ) ) {
								$is_advisor_2 = true;
							}
						}
					}
				}
			}

			if ( $is_advisor_2 && ! empty( $advisor_2_image ) ) {
				$active_advisor = 2;
			} else {
				$active_advisor = 1;
			}
		}

		// Select active advisor data
		if ( 2 === $active_advisor && ! empty( $advisor_2_image ) ) {
			$name  = $advisor_2_name;
			$phone = $advisor_2_phone;
			$zalo  = ! empty( $advisor_2_zalo ) ? $advisor_2_zalo : $advisor_2_phone;
			$image = $advisor_2_image;
			$alt   = $advisor_2_alt;
		} else {
			$active_advisor = 1;
			$name  = $advisor_1_name;
			$phone = $advisor_1_phone;
			$zalo  = ! empty( $advisor_1_zalo ) ? $advisor_1_zalo : $advisor_1_phone;
			$image = $advisor_1_image;
			$alt   = $advisor_1_alt;
		}

		if ( empty( $image ) ) {
			return; // Don't render empty banner
		}

		$final_zalo_url = ltdh_format_zalo_url( $zalo );
		$target_attr    = $open_new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';

		// Strip default padding/borders from the widget wrapper to let the banner render edge-to-edge
		$custom_before = $args['before_widget'];
		if ( false !== strpos( $custom_before, 'bg-white' ) || false !== strpos( $custom_before, 'border-slate-200' ) ) {
			$custom_before = str_replace(
				[ 'bg-white', 'border-slate-200', 'border ', 'border', 'shadow-xs', 'p-5 md:p-6', 'p-5', 'md:p-6' ],
				'',
				$custom_before
			);
		}
		$custom_before = preg_replace( '/\s+/', ' ', $custom_before );
		echo $custom_before;

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		?>
		<div class="ltdh-advisor-banner-wrap relative w-full overflow-hidden transition-all duration-300">
			<a href="<?php echo esc_url( $final_zalo_url ); ?>"<?php echo $target_attr; ?>
			   class="ltdh-advisor-banner-link group block relative w-full overflow-hidden <?php echo esc_attr( $rounded_corners ); ?> focus:outline-none focus:ring-2 focus:ring-brand-primary cursor-pointer"
			   title="<?php echo esc_attr( 'Chat Zalo tư vấn cùng ' . ( $name ?: 'Ban Tuyển Sinh' ) ); ?>"
			   aria-label="<?php echo esc_attr( 'Chat Zalo tư vấn cùng ' . ( $name ?: 'Ban Tuyển Sinh' ) ); ?>"
			   data-advisor="<?php echo esc_attr( $active_advisor ); ?>"
			   data-advisor-name="<?php echo esc_attr( $name ); ?>"
			   data-school-id="<?php echo esc_attr( $current_school_id ); ?>"
			   data-major-id="<?php echo esc_attr( $current_major_id ); ?>">
				<img src="<?php echo esc_url( $image ); ?>" 
				     alt="<?php echo esc_attr( $alt ?: ( 'Tư vấn tuyển sinh cùng ' . $name ) ); ?>" 
				     class="w-full h-auto block object-cover <?php echo esc_attr( $rounded_corners ); ?> group-hover:scale-[1.01] transition-transform duration-300" 
				     loading="lazy" />
			</a>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title             = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$display_mode      = ! empty( $instance['display_mode'] ) ? $instance['display_mode'] : 'auto_by_school';
		$open_new_tab      = ! isset( $instance['open_new_tab'] ) || ! empty( $instance['open_new_tab'] );
		$rounded_corners   = ! empty( $instance['rounded_corners'] ) ? $instance['rounded_corners'] : 'rounded-2xl';

		$default_image_1   = home_url( '/wp-content/uploads/2026/10/banner-thay-dat-cropped.webp' );
		$advisor_1_name    = ! empty( $instance['advisor_1_name'] ) ? $instance['advisor_1_name'] : 'Thầy Đạt';
		$advisor_1_phone   = ! empty( $instance['advisor_1_phone'] ) ? $instance['advisor_1_phone'] : '0988 991 496';
		$advisor_1_zalo    = ! empty( $instance['advisor_1_zalo'] ) ? $instance['advisor_1_zalo'] : '';
		$advisor_1_image   = ! empty( $instance['advisor_1_image'] ) ? $instance['advisor_1_image'] : $default_image_1;
		$advisor_1_alt     = ! empty( $instance['advisor_1_alt'] ) ? $instance['advisor_1_alt'] : 'Thầy Đạt đồng hành cùng bạn - Tư vấn tuyển sinh trực tiếp';

		$advisor_2_name    = ! empty( $instance['advisor_2_name'] ) ? $instance['advisor_2_name'] : '';
		$advisor_2_phone   = ! empty( $instance['advisor_2_phone'] ) ? $instance['advisor_2_phone'] : '';
		$advisor_2_zalo    = ! empty( $instance['advisor_2_zalo'] ) ? $instance['advisor_2_zalo'] : '';
		$advisor_2_image   = ! empty( $instance['advisor_2_image'] ) ? $instance['advisor_2_image'] : '';
		$advisor_2_alt     = ! empty( $instance['advisor_2_alt'] ) ? $instance['advisor_2_alt'] : '';
		$advisor_2_schools = ! empty( $instance['advisor_2_schools'] ) && is_array( $instance['advisor_2_schools'] ) ? array_map( 'absint', $instance['advisor_2_schools'] ) : [];
		$advisor_2_majors  = ! empty( $instance['advisor_2_majors'] ) && is_array( $instance['advisor_2_majors'] ) ? array_map( 'absint', $instance['advisor_2_majors'] ) : [];

		$schools = get_posts( [
			'post_type'      => defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );

		$majors = get_posts( [
			'post_type'      => defined( 'LTDH_CPT_MAJOR' ) ? LTDH_CPT_MAJOR : 'major',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );

		$img1_id    = $this->get_field_id( 'advisor_1_image' );
		$prev1_id   = $this->get_field_id( 'advisor_1_preview' );
		$img2_id    = $this->get_field_id( 'advisor_2_image' );
		$prev2_id   = $this->get_field_id( 'advisor_2_preview' );
		?>

		<!-- Widget Title (Optional) -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><strong><?php esc_html_e( 'Tiêu đề widget (tùy chọn, để trống để hiển thị banner tràn viền):', 'ltdh' ); ?></strong></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<!-- SECTION 1: Người Tư Vấn 1 (Mặc Định / Thầy Đạt) -->
		<div style="background: #f0f7ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px; margin-bottom: 15px;">
			<div style="font-weight: 700; color: #1e40af; font-size: 13px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
				<span>👨‍🏫 Người tư vấn 1 (Mặc định)</span>
			</div>
			
			<p style="margin: 0 0 8px 0;">
				<label for="<?php echo esc_attr( $this->get_field_id( 'advisor_1_name' ) ); ?>"><?php esc_html_e( 'Tên / Danh xưng:', 'ltdh' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'advisor_1_name' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'advisor_1_name' ) ); ?>" type="text" value="<?php echo esc_attr( $advisor_1_name ); ?>" placeholder="Ví dụ: Thầy Đạt">
			</p>

			<p style="margin: 0 0 8px 0;">
				<label for="<?php echo esc_attr( $this->get_field_id( 'advisor_1_phone' ) ); ?>"><?php esc_html_e( 'Số điện thoại / Hotline:', 'ltdh' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'advisor_1_phone' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'advisor_1_phone' ) ); ?>" type="text" value="<?php echo esc_attr( $advisor_1_phone ); ?>" placeholder="0988 991 496">
			</p>

			<p style="margin: 0 0 8px 0;">
				<label for="<?php echo esc_attr( $this->get_field_id( 'advisor_1_zalo' ) ); ?>"><?php esc_html_e( 'Link Zalo riêng (tùy chọn):', 'ltdh' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'advisor_1_zalo' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'advisor_1_zalo' ) ); ?>" type="text" value="<?php echo esc_attr( $advisor_1_zalo ); ?>" placeholder="Để trống sẽ tự động lấy https://zalo.me/[SĐT]">
			</p>

			<p style="margin: 0 0 8px 0;">
				<label for="<?php echo esc_attr( $img1_id ); ?>"><strong><?php esc_html_e( 'Ảnh Banner Người 1:', 'ltdh' ); ?></strong></label>
				<input class="widefat" id="<?php echo esc_attr( $img1_id ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'advisor_1_image' ) ); ?>" type="text" value="<?php echo esc_attr( $advisor_1_image ); ?>" style="margin-bottom: 6px;">
				<button type="button" class="button button-secondary ltdh-media-upload-btn" data-target-id="<?php echo esc_attr( $img1_id ); ?>" data-preview-id="<?php echo esc_attr( $prev1_id ); ?>">
					📷 Chọn / Tải ảnh lên
				</button>
				<button type="button" class="button-link-delete ltdh-media-remove-btn" data-target-id="<?php echo esc_attr( $img1_id ); ?>" data-preview-id="<?php echo esc_attr( $prev1_id ); ?>" style="<?php echo empty( $advisor_1_image ) ? 'display:none;' : 'margin-left:8px;'; ?>">
					Xóa ảnh
				</button>
			</p>

			<div id="<?php echo esc_attr( $prev1_id ); ?>" class="ltdh-preview-box" style="margin-top: 6px;">
				<?php if ( ! empty( $advisor_1_image ) ) : ?>
					<img src="<?php echo esc_url( $advisor_1_image ); ?>" style="max-width: 100%; height: auto; border-radius: 6px; border: 1px solid #cbd5e1;" />
				<?php endif; ?>
			</div>

			<p style="margin: 8px 0 0 0;">
				<label for="<?php echo esc_attr( $this->get_field_id( 'advisor_1_alt' ) ); ?>"><?php esc_html_e( 'Alt text (Mô tả ảnh):', 'ltdh' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'advisor_1_alt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'advisor_1_alt' ) ); ?>" type="text" value="<?php echo esc_attr( $advisor_1_alt ); ?>">
			</p>
		</div>

		<!-- SECTION 2: Người Tư Vấn 2 (Người Hợp Tác Theo Trường) -->
		<div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px; margin-bottom: 15px;">
			<div style="font-weight: 700; color: #166534; font-size: 13px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
				<span>🤝 Người tư vấn 2 (Người hợp tác)</span>
			</div>
			
			<p style="margin: 0 0 8px 0;">
				<label for="<?php echo esc_attr( $this->get_field_id( 'advisor_2_name' ) ); ?>"><?php esc_html_e( 'Tên / Danh xưng:', 'ltdh' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'advisor_2_name' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'advisor_2_name' ) ); ?>" type="text" value="<?php echo esc_attr( $advisor_2_name ); ?>" placeholder="Ví dụ: Cô Mai / Thầy Hùng">
			</p>

			<p style="margin: 0 0 8px 0;">
				<label for="<?php echo esc_attr( $this->get_field_id( 'advisor_2_phone' ) ); ?>"><?php esc_html_e( 'Số điện thoại / Hotline:', 'ltdh' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'advisor_2_phone' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'advisor_2_phone' ) ); ?>" type="text" value="<?php echo esc_attr( $advisor_2_phone ); ?>" placeholder="0912 345 678">
			</p>

			<p style="margin: 0 0 8px 0;">
				<label for="<?php echo esc_attr( $this->get_field_id( 'advisor_2_zalo' ) ); ?>"><?php esc_html_e( 'Link Zalo riêng (tùy chọn):', 'ltdh' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'advisor_2_zalo' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'advisor_2_zalo' ) ); ?>" type="text" value="<?php echo esc_attr( $advisor_2_zalo ); ?>" placeholder="Để trống sẽ tự động lấy https://zalo.me/[SĐT]">
			</p>

			<p style="margin: 0 0 8px 0;">
				<label for="<?php echo esc_attr( $img2_id ); ?>"><strong><?php esc_html_e( 'Ảnh Banner Người 2:', 'ltdh' ); ?></strong></label>
				<input class="widefat" id="<?php echo esc_attr( $img2_id ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'advisor_2_image' ) ); ?>" type="text" value="<?php echo esc_attr( $advisor_2_image ); ?>" style="margin-bottom: 6px;">
				<button type="button" class="button button-secondary ltdh-media-upload-btn" data-target-id="<?php echo esc_attr( $img2_id ); ?>" data-preview-id="<?php echo esc_attr( $prev2_id ); ?>">
					📷 Chọn / Tải ảnh lên
				</button>
				<button type="button" class="button-link-delete ltdh-media-remove-btn" data-target-id="<?php echo esc_attr( $img2_id ); ?>" data-preview-id="<?php echo esc_attr( $prev2_id ); ?>" style="<?php echo empty( $advisor_2_image ) ? 'display:none;' : 'margin-left:8px;'; ?>">
					Xóa ảnh
				</button>
			</p>

			<div id="<?php echo esc_attr( $prev2_id ); ?>" class="ltdh-preview-box" style="margin-top: 6px;">
				<?php if ( ! empty( $advisor_2_image ) ) : ?>
					<img src="<?php echo esc_url( $advisor_2_image ); ?>" style="max-width: 100%; height: auto; border-radius: 6px; border: 1px solid #cbd5e1;" />
				<?php endif; ?>
			</div>

			<p style="margin: 8px 0 10px 0;">
				<label for="<?php echo esc_attr( $this->get_field_id( 'advisor_2_alt' ) ); ?>"><?php esc_html_e( 'Alt text (Mô tả ảnh):', 'ltdh' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'advisor_2_alt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'advisor_2_alt' ) ); ?>" type="text" value="<?php echo esc_attr( $advisor_2_alt ); ?>">
			</p>

			<!-- PHÂN CÔNG THEO TRƯỜNG ĐÀO TẠO (TRỌNG TÂM) -->
			<div class="ltdh-schools-box" style="border-top: 1px dashed #86efac; padding-top: 10px; margin-top: 12px;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
					<strong style="color: #166534; font-size: 13px;">🏛️ Chọn các TRƯỜNG Người 2 phụ trách:</strong>
					<span style="font-size: 11px;">
						<a href="#" class="ltdh-schools-select-all"><?php esc_html_e( 'Chọn hết', 'ltdh' ); ?></a> | 
						<a href="#" class="ltdh-schools-deselect-all"><?php esc_html_e( 'Bỏ chọn', 'ltdh' ); ?></a>
					</span>
				</div>
				<input type="text" class="widefat ltdh-school-filter-input" placeholder="🔍 Gõ để tìm nhanh tên trường..." style="margin-bottom: 6px; font-size: 12px; padding: 3px 6px;">
				<div class="ltdh-schools-list" style="max-height: 160px; overflow-y: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 8px;">
					<?php if ( ! empty( $schools ) ) : ?>
						<?php foreach ( $schools as $sch ) : 
							$is_sch_checked = in_array( (int) $sch->ID, $advisor_2_schools, true );
						?>
							<label class="ltdh-school-item" style="display: block; margin-bottom: 5px; font-size: 12px; cursor: pointer;">
								<input type="checkbox" class="ltdh-school-checkbox" name="<?php echo esc_attr( $this->get_field_name( 'advisor_2_schools' ) ); ?>[]" value="<?php echo esc_attr( $sch->ID ); ?>" <?php checked( $is_sch_checked ); ?>>
								<span><strong><?php echo esc_html( $sch->post_title ); ?></strong></span>
							</label>
						<?php endforeach; ?>
					<?php else : ?>
						<p style="font-size: 11px; color: #64748b; margin: 4px 0;"><?php esc_html_e( 'Chưa có trường nào được tạo.', 'ltdh' ); ?></p>
					<?php endif; ?>
				</div>
				<div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 8px 10px; margin-top: 8px;">
					<p style="font-size: 11px; margin: 0; color: #065f46; line-height: 1.45;">
						🎯 <strong>Quy tắc tự động:</strong> Chỉ cần tích chọn Trường ở trên, <strong>tất cả các ngành học và chương trình đào tạo thuộc trường đó</strong> sẽ tự động hiển thị banner và link Zalo của <strong>Người 2</strong>! Các trường còn lại sẽ hiển thị Người 1.
					</p>
				</div>
			</div>

			<!-- PHÂN CÔNG THÊM THEO NGÀNH HỌC (TÙY CHỌN PHỤ) -->
			<details style="margin-top: 10px; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
				<summary style="font-size: 12px; font-weight: 600; color: #475569; cursor: pointer;">
					📂 Chọn thêm ngành cụ thể (Tùy chọn phụ nếu cần gán lẻ)
				</summary>
				<div class="ltdh-majors-box" style="margin-top: 8px;">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
						<span style="color: #475569; font-size: 11px;"><?php esc_html_e( 'Ngành lẻ bổ sung:', 'ltdh' ); ?></span>
						<span style="font-size: 11px;">
							<a href="#" class="ltdh-majors-select-all"><?php esc_html_e( 'Chọn hết', 'ltdh' ); ?></a> | 
							<a href="#" class="ltdh-majors-deselect-all"><?php esc_html_e( 'Bỏ chọn', 'ltdh' ); ?></a>
						</span>
					</div>
					<input type="text" class="widefat ltdh-major-filter-input" placeholder="🔍 Gõ để tìm nhanh tên ngành..." style="margin-bottom: 6px; font-size: 12px; padding: 3px 6px;">
					<div class="ltdh-majors-list" style="max-height: 130px; overflow-y: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 8px;">
						<?php if ( ! empty( $majors ) ) : ?>
							<?php foreach ( $majors as $m ) : 
								$is_checked = in_array( (int) $m->ID, $advisor_2_majors, true );
							?>
								<label class="ltdh-major-item" style="display: block; margin-bottom: 4px; font-size: 12px; cursor: pointer;">
									<input type="checkbox" class="ltdh-major-checkbox" name="<?php echo esc_attr( $this->get_field_name( 'advisor_2_majors' ) ); ?>[]" value="<?php echo esc_attr( $m->ID ); ?>" <?php checked( $is_checked ); ?>>
									<span><?php echo esc_html( $m->post_title ); ?></span>
								</label>
							<?php endforeach; ?>
						<?php else : ?>
							<p style="font-size: 11px; color: #64748b; margin: 4px 0;"><?php esc_html_e( 'Chưa có ngành học nào được tạo.', 'ltdh' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</details>
		</div>

		<!-- SECTION 3: Cài Đặt Hiển Thị & Giao Diện -->
		<div style="background: #fafafa; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 10px;">
			<div style="font-weight: 700; color: #334155; font-size: 13px; margin-bottom: 8px;">
				<span>⚙️ Cài đặt hiển thị & Giao diện</span>
			</div>

			<p style="margin: 0 0 8px 0;">
				<label for="<?php echo esc_attr( $this->get_field_id( 'display_mode' ) ); ?>"><?php esc_html_e( 'Chế độ hiển thị:', 'ltdh' ); ?></label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_mode' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_mode' ) ); ?>">
					<option value="auto_by_school" <?php selected( $display_mode, 'auto_by_school' ); ?>>⚡ Tự động theo Trường & Ngành (Khuyên dùng: Chọn trường là tất cả ngành thuộc trường tự động theo)</option>
					<option value="always_1" <?php selected( $display_mode, 'always_1' ); ?>>👨‍🏫 Cố định luôn hiển thị Người 1</option>
					<option value="always_2" <?php selected( $display_mode, 'always_2' ); ?>>🤝 Cố định luôn hiển thị Người 2</option>
					<option value="random" <?php selected( $display_mode, 'random' ); ?>>🎲 Xoay vòng ngẫu nhiên 50/50 (Chia đều khách)</option>
				</select>
			</p>

			<p style="margin: 0 0 8px 0;">
				<label for="<?php echo esc_attr( $this->get_field_id( 'rounded_corners' ) ); ?>"><?php esc_html_e( 'Bo góc ảnh banner:', 'ltdh' ); ?></label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'rounded_corners' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'rounded_corners' ) ); ?>">
					<option value="rounded-2xl" <?php selected( $rounded_corners, 'rounded-2xl' ); ?>>Bo góc mềm mại 16px (Khuyên dùng)</option>
					<option value="rounded-xl" <?php selected( $rounded_corners, 'rounded-xl' ); ?>>Bo góc nhẹ 12px</option>
					<option value="rounded-none" <?php selected( $rounded_corners, 'rounded-none' ); ?>>Không bo góc (Nếu ảnh đã tự bo viền)</option>
				</select>
			</p>

			<p style="margin: 0;">
				<input class="checkbox" type="checkbox" <?php checked( $open_new_tab ); ?> id="<?php echo esc_attr( $this->get_field_id( 'open_new_tab' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'open_new_tab' ) ); ?>" value="1">
				<label for="<?php echo esc_attr( $this->get_field_id( 'open_new_tab' ) ); ?>"><?php esc_html_e( 'Mở liên kết Zalo trong tab mới (target="_blank")', 'ltdh' ); ?></label>
			</p>
		</div>

		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance                      = [];
		$instance['title']             = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['display_mode']      = in_array( $new_instance['display_mode'] ?? '', [ 'auto_by_school', 'always_1', 'always_2', 'random' ], true ) ? $new_instance['display_mode'] : 'auto_by_school';
		$instance['open_new_tab']      = ! empty( $new_instance['open_new_tab'] );
		$instance['rounded_corners']   = in_array( $new_instance['rounded_corners'] ?? '', [ 'rounded-2xl', 'rounded-xl', 'rounded-none' ], true ) ? $new_instance['rounded_corners'] : 'rounded-2xl';

		$instance['advisor_1_name']    = sanitize_text_field( $new_instance['advisor_1_name'] ?? 'Thầy Đạt' );
		$instance['advisor_1_phone']   = sanitize_text_field( $new_instance['advisor_1_phone'] ?? '' );
		$instance['advisor_1_zalo']    = sanitize_text_field( $new_instance['advisor_1_zalo'] ?? '' );
		$instance['advisor_1_image']   = esc_url_raw( $new_instance['advisor_1_image'] ?? '' );
		$instance['advisor_1_alt']     = sanitize_text_field( $new_instance['advisor_1_alt'] ?? '' );

		$instance['advisor_2_name']    = sanitize_text_field( $new_instance['advisor_2_name'] ?? '' );
		$instance['advisor_2_phone']   = sanitize_text_field( $new_instance['advisor_2_phone'] ?? '' );
		$instance['advisor_2_zalo']    = sanitize_text_field( $new_instance['advisor_2_zalo'] ?? '' );
		$instance['advisor_2_image']   = esc_url_raw( $new_instance['advisor_2_image'] ?? '' );
		$instance['advisor_2_alt']     = sanitize_text_field( $new_instance['advisor_2_alt'] ?? '' );
		$instance['advisor_2_schools'] = ! empty( $new_instance['advisor_2_schools'] ) && is_array( $new_instance['advisor_2_schools'] ) ? array_map( 'absint', $new_instance['advisor_2_schools'] ) : [];
		$instance['advisor_2_majors']  = ! empty( $new_instance['advisor_2_majors'] ) && is_array( $new_instance['advisor_2_majors'] ) ? array_map( 'absint', $new_instance['advisor_2_majors'] ) : [];

		return $instance;
	}
}

/**
 * Enqueue Media Uploader scripts and inline handlers for the Advisor Banner Widget.
 */
add_action( 'admin_enqueue_scripts', 'ltdh_advisor_banner_admin_assets' );
function ltdh_advisor_banner_admin_assets( $hook ) {
	if ( 'widgets.php' !== $hook && 'customize.php' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'jquery' );

	$inline_js = <<<JS
jQuery(document).ready(function($) {
	// 1. Upload Banner Image via WP Media Library
	$(document).on('click', '.ltdh-media-upload-btn', function(e) {
		e.preventDefault();
		var btn = $(this);
		var targetId = btn.data('target-id');
		var previewId = btn.data('preview-id');
		var targetInput = $('#' + targetId);
		var previewBox = $('#' + previewId);
		var removeBtn = btn.siblings('.ltdh-media-remove-btn');

		var frame = wp.media({
			title: 'Chọn hoặc Tải lên Ảnh Banner Tư Vấn',
			button: { text: 'Sử dụng ảnh này' },
			multiple: false
		});

		frame.on('select', function() {
			var attachment = frame.state().get('selection').first().toJSON();
			targetInput.val(attachment.url).trigger('change');
			previewBox.html('<img src="' + attachment.url + '" style="max-width:100%; height:auto; border-radius:6px; border:1px solid #cbd5e1; margin-top:6px;" />');
			removeBtn.show();
		});

		frame.open();
	});

	// 2. Remove Banner Image
	$(document).on('click', '.ltdh-media-remove-btn', function(e) {
		e.preventDefault();
		var btn = $(this);
		var targetId = btn.data('target-id');
		var previewId = btn.data('preview-id');

		$('#' + targetId).val('').trigger('change');
		$('#' + previewId).empty();
		btn.hide();
	});

	// 3. Quick Filter Schools in Checklist
	$(document).on('input', '.ltdh-school-filter-input', function() {
		var q = $(this).val().toLowerCase().trim();
		var list = $(this).closest('.ltdh-schools-box').find('.ltdh-school-item');
		list.each(function() {
			var text = $(this).text().toLowerCase();
			$(this).toggle(text.indexOf(q) !== -1);
		});
	});

	// 4. Select All / Deselect All Schools
	$(document).on('click', '.ltdh-schools-select-all', function(e) {
		e.preventDefault();
		var box = $(this).closest('.ltdh-schools-box');
		box.find('.ltdh-school-checkbox:visible').prop('checked', true).trigger('change');
	});

	$(document).on('click', '.ltdh-schools-deselect-all', function(e) {
		e.preventDefault();
		var box = $(this).closest('.ltdh-schools-box');
		box.find('.ltdh-school-checkbox:visible').prop('checked', false).trigger('change');
	});

	// 5. Quick Filter Majors in Checklist
	$(document).on('input', '.ltdh-major-filter-input', function() {
		var q = $(this).val().toLowerCase().trim();
		var list = $(this).closest('.ltdh-majors-box').find('.ltdh-major-item');
		list.each(function() {
			var text = $(this).text().toLowerCase();
			$(this).toggle(text.indexOf(q) !== -1);
		});
	});

	// 6. Select All / Deselect All Majors
	$(document).on('click', '.ltdh-majors-select-all', function(e) {
		e.preventDefault();
		var box = $(this).closest('.ltdh-majors-box');
		box.find('.ltdh-major-checkbox:visible').prop('checked', true).trigger('change');
	});

	$(document).on('click', '.ltdh-majors-deselect-all', function(e) {
		e.preventDefault();
		var box = $(this).closest('.ltdh-majors-box');
		box.find('.ltdh-major-checkbox:visible').prop('checked', false).trigger('change');
	});
});
JS;
	wp_add_inline_script( 'jquery', $inline_js );
}

/**
 * Register meta box on School, Major, and Program post types to select advisor override.
 */
function ltdh_register_advisor_meta_boxes() {
	if ( ! function_exists( 'add_meta_box' ) ) {
		return;
	}

	$cpt_school  = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
	$cpt_major   = defined( 'LTDH_CPT_MAJOR' ) ? LTDH_CPT_MAJOR : 'major';
	$cpt_program = defined( 'LTDH_CPT_PROGRAM' ) ? LTDH_CPT_PROGRAM : 'program';

	add_meta_box(
		'ltdh_advisor_override',
		__( '👤 Chuyên viên tư vấn phụ trách', 'ltdh' ),
		'ltdh_render_advisor_meta_box',
		[ $cpt_school, $cpt_major, $cpt_program ],
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'ltdh_register_advisor_meta_boxes' );

function ltdh_render_advisor_meta_box( $post ) {
	wp_nonce_field( 'ltdh_advisor_nonce', 'ltdh_advisor_nonce_field' );
	$current   = get_post_meta( $post->ID, '_ltdh_assigned_advisor', true ) ?: 'default';
	$post_type = get_post_type( $post );
	if ( 'school' === $post_type ) {
		$label = 'trường này (và toàn bộ ngành/chương trình thuộc trường)';
	} elseif ( 'program' === $post_type ) {
		$label = 'chương trình này (để tự động sẽ ăn theo Trường trực thuộc)';
	} else {
		$label = 'ngành này (để tự động sẽ ăn theo Trường nếu có ngữ cảnh trường)';
	}
	?>
	<p class="description" style="margin-bottom:8px; font-size:12px;">
		<?php printf( esc_html__( 'Chọn chuyên viên xuất hiện trên banner tư vấn khi người xem vào %s:', 'ltdh' ), esc_html( $label ) ); ?>
	</p>
	<select name="ltdh_assigned_advisor" class="widefat" style="margin-bottom:6px;">
		<option value="default" <?php selected( $current, 'default' ); ?>><?php esc_html_e( '⚡ Tự động (Theo phân công trong Widget)', 'ltdh' ); ?></option>
		<option value="advisor_1" <?php selected( $current, 'advisor_1' ); ?>><?php esc_html_e( '👨‍🏫 Người tư vấn 1 (Thầy Đạt)', 'ltdh' ); ?></option>
		<option value="advisor_2" <?php selected( $current, 'advisor_2' ); ?>><?php esc_html_e( '🤝 Người tư vấn 2 (Người hợp tác)', 'ltdh' ); ?></option>
	</select>
	<?php
}

function ltdh_save_advisor_meta_box( $post_id ) {
	if ( ! isset( $_POST['ltdh_advisor_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ltdh_advisor_nonce_field'] ) ), 'ltdh_advisor_nonce' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['ltdh_assigned_advisor'] ) ) {
		$advisor = sanitize_text_field( wp_unslash( $_POST['ltdh_assigned_advisor'] ) );
		if ( in_array( $advisor, [ 'default', 'advisor_1', 'advisor_2' ], true ) ) {
			update_post_meta( $post_id, '_ltdh_assigned_advisor', $advisor );
		}
	}
}
add_action( 'save_post', 'ltdh_save_advisor_meta_box' );
