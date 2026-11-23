<?php
/**
 * Widget: Sidebar Condition Checker (Kiểm tra điều kiện tuyển sinh).
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTDH_Widget_Condition_Checker extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'ltdh_condition_checker',
			__( '[LTDH] Kiểm tra điều kiện xét tuyển', 'ltdh' ),
			[
				'description' => __( 'Widget kiểm tra nhanh điều kiện xét tuyển liên thông (chuẩn Thông tư 06, 95, 56, 52/2026/TT-BGDĐT), tự động nhận diện Trường, Ngành, Hệ đào tạo.', 'ltdh' ),
			]
		);
	}

	public function widget( $args, $instance ) {
		// Do not render compact widget on the full /kiem-tra-dieu-kien/ page
		if ( function_exists( 'is_page' ) && is_page( 'kiem-tra-dieu-kien' ) ) {
			return;
		}

		$title    = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$subtitle = ! empty( $instance['subtitle'] ) ? $instance['subtitle'] : '';

		$params = [
			'mode'      => 'compact',
			'widget_id' => $args['widget_id'] ?? '',
		];

		if ( ! empty( $title ) ) {
			$params['title'] = $title;
		}
		if ( ! empty( $subtitle ) ) {
			$params['subtitle'] = $subtitle;
		}

		if ( is_singular( 'program' ) ) {
			$params['program_id'] = get_the_ID();
		} elseif ( is_singular( 'school' ) ) {
			$params['school_id'] = get_the_ID();
		} elseif ( is_singular( 'major' ) ) {
			$params['major_id'] = get_the_ID();
			$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );
			if ( ! empty( $school_param ) ) {
				if ( is_numeric( $school_param ) ) {
					$s_post = get_post( intval( $school_param ) );
				} else {
					$cpt_school = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
					$s_post     = get_page_by_path( sanitize_title( $school_param ), OBJECT, $cpt_school );
				}
				if ( $s_post && 'publish' === $s_post->post_status && ( defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' ) === $s_post->post_type ) {
					$params['school_id'] = $s_post->ID;
				}
			}
		}

		echo $args['before_widget'];
		get_template_part( 'template-parts/widgets/condition-checker', null, $params );
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title    = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$subtitle = ! empty( $instance['subtitle'] ) ? $instance['subtitle'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Tiêu đề tùy chỉnh (để trống để tự nhận diện theo ngành/trường):', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>"><?php esc_html_e( 'Phụ đề (để trống để dùng mặc định Thông tư Bộ GD&ĐT):', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'subtitle' ) ); ?>" type="text" value="<?php echo esc_attr( $subtitle ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance             = [];
		$instance['title']    = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['subtitle'] = sanitize_text_field( $new_instance['subtitle'] ?? '' );
		return $instance;
	}
}
