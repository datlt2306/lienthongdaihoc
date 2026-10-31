<?php
/**
 * Widget: Admissions Hotline & Zalo.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTDH_Widget_Hotline extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'ltdh_hotline',
			__( '[LTDH] Hotline & Zalo Tuyển sinh', 'ltdh' ),
			[ 'description' => __( 'Hộp liên hệ trực tiếp với hotline và nút bấm Gọi ngay / Chat Zalo. Tự động nhận diện Hotline & Zalo theo từng trường.', 'ltdh' ) ]
		);
	}

	public function widget( $args, $instance ) {
		// Resolve school ID
		$school_setting   = ! empty( $instance['school_id'] ) ? $instance['school_id'] : 'auto';
		$target_school_id = 0;
		if ( 'global' !== $school_setting ) {
			if ( 'auto' === $school_setting ) {
				$target_school_id = function_exists( 'ltdh_detect_current_school_id' ) ? ltdh_detect_current_school_id() : ( function_exists( 'ltdh_get_current_context_school_id' ) ? ltdh_get_current_context_school_id() : 0 );
			} elseif ( is_numeric( $school_setting ) && (int) $school_setting > 0 ) {
				$target_school_id = (int) $school_setting;
			}
		}

		$title = ! empty( $instance['title'] ) ? $instance['title'] : 'VĂN PHÒNG TUYỂN SINH';
		if ( $target_school_id > 0 ) {
			$s_title = get_the_title( $target_school_id );
			$s_code  = get_post_meta( $target_school_id, 'school_code', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'school_code', $target_school_id ) : '' );
			$s_code  = $s_code ? $s_code : $s_title;
			$replace = [
				'{school_name}' => $s_title,
				'{ten_truong}'  => $s_title,
				'{school_code}' => $s_code,
				'{ma_truong}'   => $s_code,
			];
		} else {
			$replace = [
				'{school_name}' => '',
				'{ten_truong}'  => '',
				'{school_code}' => '',
				'{ma_truong}'   => '',
			];
		}
		$title = trim( preg_replace( '/\s+/', ' ', strtr( $title, $replace ) ) );

		// Context Program & Major IDs
		$current_program_id = function_exists( 'ltdh_detect_current_program_id' ) ? ltdh_detect_current_program_id() : 0;
		$current_major_id   = function_exists( 'ltdh_detect_current_major_id' ) ? ltdh_detect_current_major_id() : 0;

		// Hotline: if set manually in widget, use it; else if on program, use program hotline (with school cascade); else resolve from school context; else fallback to global
		$manual_hotline = ! empty( $instance['hotline'] ) ? trim( $instance['hotline'] ) : '';
		if ( ! empty( $manual_hotline ) ) {
			$hotline = $manual_hotline;
		} elseif ( $current_program_id > 0 && function_exists( 'ltdh_get_program_hotline' ) ) {
			$hotline = ltdh_get_program_hotline( $current_program_id );
		} elseif ( $current_major_id > 0 && ( get_post_meta( $current_major_id, 'hotline', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'hotline', $current_major_id ) : '' ) ) ) {
			$hotline = get_post_meta( $current_major_id, 'hotline', true ) ?: get_field( 'hotline', $current_major_id );
		} elseif ( $target_school_id > 0 && function_exists( 'ltdh_get_school_hotline' ) ) {
			$hotline = ltdh_get_school_hotline( $target_school_id );
		} else {
			$hotline = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496';
		}

		// Zalo URL: if set manually in widget, use it; else resolve from program override; else from school context; else fallback to global
		$manual_zalo = ! empty( $instance['zalo_url'] ) ? trim( $instance['zalo_url'] ) : '';
		if ( ! empty( $manual_zalo ) ) {
			$zalo_url = $manual_zalo;
		} elseif ( $current_program_id > 0 && ( get_post_meta( $current_program_id, 'zalo_url', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'zalo_url', $current_program_id ) : '' ) ) ) {
			$zalo_url = get_post_meta( $current_program_id, 'zalo_url', true ) ?: get_field( 'zalo_url', $current_program_id );
		} elseif ( $current_major_id > 0 && ( get_post_meta( $current_major_id, 'zalo_url', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'zalo_url', $current_major_id ) : '' ) ) ) {
			$zalo_url = get_post_meta( $current_major_id, 'zalo_url', true ) ?: get_field( 'zalo_url', $current_major_id );
		} elseif ( $target_school_id > 0 && function_exists( 'ltdh_get_school_zalo_group_url' ) ) {
			$zalo_url = ltdh_get_school_zalo_group_url( $target_school_id );
		} else {
			$zalo_url = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
		}

		echo $args['before_widget'];
		get_template_part( 'template-parts/sidebar-hotline', null, [
			'title'    => $title,
			'hotline'  => $hotline,
			'zalo_url' => $zalo_url,
		] );
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title          = ! empty( $instance['title'] ) ? $instance['title'] : 'VĂN PHÒNG TUYỂN SINH';
		$hotline        = ! empty( $instance['hotline'] ) ? $instance['hotline'] : '';
		$zalo_url       = ! empty( $instance['zalo_url'] ) ? $instance['zalo_url'] : '';
		$school_setting = ! empty( $instance['school_id'] ) ? $instance['school_id'] : 'auto';

		$schools = get_posts( [
			'post_type'      => defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );
		?>
		<div style="background: #eff6ff; border-left: 4px solid #2563eb; padding: 10px 12px; margin-bottom: 14px; font-size: 12px; line-height: 1.5; color: #1e3a8a; border-radius: 4px;">
			<strong>💡 Tự động theo từng trường:</strong><br>
			Để trống ô <em>Hotline</em> và <em>Đường dẫn Zalo</em> để widget <strong>tự động lấy theo trường đang xem</strong> (hoặc lấy cài đặt chung nếu trang không thuộc trường nào). Hỗ trợ thẻ <code>{school_name}</code> hoặc <code>{school_code}</code> trong Tiêu đề.
		</div>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'school_id' ) ); ?>"><strong><?php esc_html_e( 'Chế độ nhận diện trường:', 'ltdh' ); ?></strong></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'school_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'school_id' ) ); ?>">
				<option value="auto" <?php selected( $school_setting, 'auto' ); ?>>⚡ Tự động nhận diện theo trang đang xem (Khuyên dùng)</option>
				<option value="global" <?php selected( $school_setting, 'global' ); ?>>🌐 Dùng cài đặt chung toàn trang (không theo trường)</option>
				<?php foreach ( $schools as $sch ) : ?>
					<option value="<?php echo esc_attr( $sch->ID ); ?>" <?php selected( $school_setting, (string) $sch->ID ); ?>>
						<?php echo esc_html( 'Cố định: ' . $sch->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Tiêu đề:', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			<small style="color:#64748b;"><?php esc_html_e( 'Có thể chèn {school_name} hoặc {school_code}', 'ltdh' ); ?></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'hotline' ) ); ?>"><?php esc_html_e( 'Số điện thoại Hotline:', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'hotline' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hotline' ) ); ?>" type="text" value="<?php echo esc_attr( $hotline ); ?>" placeholder="<?php esc_attr_e( 'Để trống để tự động lấy theo trường / cài đặt chung', 'ltdh' ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'zalo_url' ) ); ?>"><?php esc_html_e( 'Đường dẫn Zalo:', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'zalo_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'zalo_url' ) ); ?>" type="text" value="<?php echo esc_attr( $zalo_url ); ?>" placeholder="<?php esc_attr_e( 'Để trống để tự động lấy theo trường / cài đặt chung', 'ltdh' ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance              = [];
		$instance['school_id'] = sanitize_text_field( $new_instance['school_id'] ?? 'auto' );
		$instance['title']     = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['hotline']   = sanitize_text_field( $new_instance['hotline'] ?? '' );
		$instance['zalo_url']  = esc_url_raw( $new_instance['zalo_url'] ?? '' );
		return $instance;
	}
}
