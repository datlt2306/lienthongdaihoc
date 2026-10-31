<?php
/**
 * Widget: Sidebar Consultation Form.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTDH_Widget_Consultation_Form extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'ltdh_consultation_form',
			__( '[LTDH] Form Đăng ký tư vấn', 'ltdh' ),
			[ 'description' => __( 'Form thu thập thông tin đăng ký tư vấn tuyển sinh ngay trên thanh bên.', 'ltdh' ) ]
		);
	}

	public function widget( $args, $instance ) {
		$title         = ! empty( $instance['title'] ) ? $instance['title'] : 'Đăng ký tư vấn miễn phí';
		$desc          = ! empty( $instance['desc'] ) ? $instance['desc'] : 'Hãy để lại thông tin, ban tư vấn tuyển sinh sẽ liên hệ và giải đáp lộ trình cụ thể cho bạn trong vòng 15 phút.';
		$cf7_shortcode = ! empty( $instance['cf7_shortcode'] ) ? $instance['cf7_shortcode'] : '';

		echo $args['before_widget'];
		?>
		<div class="ltdh-widget-form ltdh-sidebar-form hidden lg:block">
			<?php if ( ! empty( $title ) ) : ?>
				<h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<?php if ( ! empty( $desc ) ) : ?>
				<p class="text-sm text-slate-500 mb-4"><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>

			<?php
			if ( ! empty( $cf7_shortcode ) ) {
				echo do_shortcode( $cf7_shortcode );
			} elseif ( function_exists( 'ltdh_render_consultation_form' ) ) {
				$hidden = [
					'referral_source' => get_permalink(),
				];
				if ( is_singular( 'program' ) ) {
					$hidden['program_id']         = get_the_ID();
					$hidden['current_program_id'] = get_the_ID();
					$school_id = get_field( 'school_relationship', get_the_ID() );
					if ( $school_id ) {
						$resolved_sid = is_object( $school_id ) ? $school_id->ID : (int) $school_id;
						$hidden['school_id']         = $resolved_sid;
						$hidden['current_school_id'] = $resolved_sid;
					}
				} elseif ( is_singular( 'school' ) ) {
					$hidden['school_id']         = get_the_ID();
					$hidden['current_school_id'] = get_the_ID();
				} elseif ( is_singular( 'major' ) ) {
					$hidden['major_id']         = get_the_ID();
					$hidden['current_major_id'] = get_the_ID();
					$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );
					if ( ! empty( $school_param ) ) {
						if ( is_numeric( $school_param ) ) {
							$s_post = get_post( intval( $school_param ) );
						} else {
							$cpt_school = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
							$s_post     = get_page_by_path( sanitize_title( $school_param ), OBJECT, $cpt_school );
						}
						if ( $s_post && 'publish' === $s_post->post_status && ( defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' ) === $s_post->post_type ) {
							$hidden['school_id']         = $s_post->ID;
							$hidden['current_school_id'] = $s_post->ID;
						}
					}
				}
				ltdh_render_consultation_form( $hidden );
			}
			?>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title         = ! empty( $instance['title'] ) ? $instance['title'] : 'Đăng ký tư vấn miễn phí';
		$desc          = ! empty( $instance['desc'] ) ? $instance['desc'] : 'Hãy để lại thông tin, ban tư vấn tuyển sinh sẽ liên hệ và giải đáp lộ trình cụ thể cho bạn trong vòng 15 phút.';
		$cf7_shortcode = ! empty( $instance['cf7_shortcode'] ) ? $instance['cf7_shortcode'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Tiêu đề:', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'desc' ) ); ?>"><?php esc_html_e( 'Mô tả:', 'ltdh' ); ?></label>
			<textarea class="widefat" rows="3" id="<?php echo esc_attr( $this->get_field_id( 'desc' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'desc' ) ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'cf7_shortcode' ) ); ?>"><?php esc_html_e( 'Shortcode CF7 (tùy chọn, để trống sẽ dùng form chuẩn hệ thống):', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'cf7_shortcode' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'cf7_shortcode' ) ); ?>" type="text" value="<?php echo esc_attr( $cf7_shortcode ); ?>" placeholder="[contact-form-7 id=...]">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance                  = [];
		$instance['title']         = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['desc']          = sanitize_textarea_field( $new_instance['desc'] ?? '' );
		$instance['cf7_shortcode'] = sanitize_text_field( $new_instance['cf7_shortcode'] ?? '' );
		return $instance;
	}
}
