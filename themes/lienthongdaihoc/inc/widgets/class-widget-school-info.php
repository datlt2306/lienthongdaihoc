<?php
/**
 * Widget: School Profile & Info Card.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTDH_Widget_School_Info extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'ltdh_school_info',
			__( '[LTDH] Thông tin trường đào tạo', 'ltdh' ),
			[
				'description'                 => __( 'Tự động hiển thị thẻ thông tin trường đối tác (ảnh bìa, logo, tên trường, địa chỉ, website, nút xem chi tiết) theo ngữ cảnh trang hoặc URL (?truong=...).', 'ltdh' ),
				'customize_selective_refresh' => true,
			]
		);
	}

	/**
	 * Detect school ID from context (URL parameter, single program, single school, or manual override).
	 *
	 * @param array $instance
	 * @return int
	 */
	public function get_school_id( $instance ) {
		// 1. If a manual school is selected in settings (not 'auto' and numeric)
		if ( ! empty( $instance['school_id'] ) && 'auto' !== $instance['school_id'] && is_numeric( $instance['school_id'] ) ) {
			return (int) $instance['school_id'];
		}

		// 2. Check URL query parameters (?truong=..., ?school=..., ?from_school=...)
		$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );
		if ( ! empty( $school_param ) ) {
			if ( is_numeric( $school_param ) ) {
				$s_post = get_post( intval( $school_param ) );
			} else {
				$cpt_school = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
				$s_post     = get_page_by_path( sanitize_title( $school_param ), OBJECT, $cpt_school );
			}
			if ( $s_post && 'publish' === $s_post->post_status && ( defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' ) === $s_post->post_type ) {
				return $s_post->ID;
			}
		}

		// 3. Check Single Program
		if ( is_singular( 'program' ) ) {
			$prog_id    = get_the_ID();
			$school_rel = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $prog_id ) : ( get_field( 'school_relationship', $prog_id ) ?: get_post_meta( $prog_id, 'school_relationship', true ) );
			if ( is_array( $school_rel ) ) {
				$school_rel = ! empty( $school_rel ) ? ( is_object( $school_rel[0] ) ? $school_rel[0]->ID : $school_rel[0] ) : 0;
			} elseif ( is_object( $school_rel ) ) {
				$school_rel = $school_rel->ID;
			}
			$school_rel = (int) $school_rel;
			if ( $school_rel && 'publish' === get_post_status( $school_rel ) ) {
				return $school_rel;
			}
		}

		// 4. Check Single School
		if ( is_singular( 'school' ) ) {
			return get_the_ID();
		}

		// 5. Shared context helper fallback (e.g. on single major with context school)
		if ( function_exists( 'ltdh_detect_current_school_id' ) ) {
			$detected_sid = ltdh_detect_current_school_id();
			if ( $detected_sid > 0 && 'publish' === get_post_status( $detected_sid ) ) {
				return $detected_sid;
			}
		}

		return 0;
	}

	public function widget( $args, $instance ) {
		$school_id     = $this->get_school_id( $instance );
		$hide_if_empty = isset( $instance['hide_if_empty'] ) ? (bool) $instance['hide_if_empty'] : true;

		if ( ! $school_id ) {
			if ( $hide_if_empty ) {
				return;
			}
			return;
		}

		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';

		// Strip default padding/borders from the widget wrapper to let the card render edge-to-edge
		$custom_before = preg_replace( '/\b(p-[0-9]+|md:p-[0-9]+|bg-white|border|border-slate-200|shadow-xs)\b/', '', $args['before_widget'] );
		echo $custom_before;

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		if ( function_exists( 'ltdh_render_school_info_card' ) ) {
			ltdh_render_school_info_card( $school_id, 'bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden' );
		}

		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title         = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$selected_id   = ! empty( $instance['school_id'] ) ? $instance['school_id'] : 'auto';
		$hide_if_empty = isset( $instance['hide_if_empty'] ) ? (bool) $instance['hide_if_empty'] : true;

		$schools = get_posts( [
			'post_type'      => defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Tiêu đề widget (tùy chọn, để trống sẽ hiển thị thẻ tràn viền đẹp mắt):', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'school_id' ) ); ?>"><?php esc_html_e( 'Chế độ lấy thông tin trường:', 'ltdh' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'school_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'school_id' ) ); ?>">
				<option value="auto" <?php selected( $selected_id, 'auto' ); ?>>⚡ Tự động nhận diện (URL ?truong=... hoặc Trang trường / Chương trình)</option>
				<?php foreach ( $schools as $sch ) : ?>
					<option value="<?php echo esc_attr( $sch->ID ); ?>" <?php selected( $selected_id, (string) $sch->ID ); ?>>
						<?php echo esc_html( $sch->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $hide_if_empty ); ?> id="<?php echo esc_attr( $this->get_field_id( 'hide_if_empty' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_if_empty' ) ); ?>" value="1">
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_if_empty' ) ); ?>"><?php esc_html_e( 'Tự động ẩn widget nếu không có thông tin trường', 'ltdh' ); ?></label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance                  = [];
		$instance['title']         = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['school_id']     = sanitize_text_field( $new_instance['school_id'] ?? 'auto' );
		$instance['hide_if_empty'] = ! empty( $new_instance['hide_if_empty'] );
		return $instance;
	}
}
