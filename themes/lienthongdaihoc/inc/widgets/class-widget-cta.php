<?php
/**
 * Widget: Call to Action (CTA) Banner.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTDH_Widget_CTA extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'ltdh_cta',
			__( '[LTDH] Banner Kêu gọi hành động (CTA)', 'ltdh' ),
			[ 'description' => __( 'Hộp banner kêu gọi hành động tư vấn / kiểm tra điều kiện tuyển sinh nổi bật.', 'ltdh' ) ]
		);
	}

	public function widget( $args, $instance ) {
		$title        = ! empty( $instance['title'] ) ? $instance['title'] : 'Bạn chưa chọn được lộ trình học?';
		$desc         = ! empty( $instance['desc'] ) ? $instance['desc'] : 'Để lại thông tin, ban tư vấn sẽ giải đáp lộ trình liên thông hoàn toàn miễn phí cho bạn.';
		$btn_text     = ! empty( $instance['btn_text'] ) ? $instance['btn_text'] : 'KIỂM TRA ĐIỀU KIỆN NGAY';
		$btn_url      = ! empty( $instance['btn_url'] ) ? $instance['btn_url'] : home_url( '/kiem-tra-dieu-kien/' );
		$icon         = ! empty( $instance['icon'] ) ? $instance['icon'] : '💡';
		$style        = ! empty( $instance['style'] ) ? $instance['style'] : 'navy';
		$show_hotline = isset( $instance['show_hotline'] ) ? (bool) $instance['show_hotline'] : true;

		$bg_class = 'bg-gradient-to-tr from-[#0E2038] to-brand-primary text-white';
		$btn_class = 'bg-brand-accent text-white hover:bg-[#e06e00] shadow-md shadow-brand-accent/20';

		if ( 'blue' === $style ) {
			$bg_class  = 'bg-gradient-to-br from-[#00308b] to-[#001d57] text-white';
			$btn_class = 'bg-white text-[#00308b] hover:bg-blue-50 shadow-md';
		} elseif ( 'orange' === $style ) {
			$bg_class  = 'bg-gradient-to-tr from-[#c2410c] to-[#ea580c] text-white';
			$btn_class = 'bg-white text-[#c2410c] hover:bg-orange-50 shadow-md';
		}

		echo $args['before_widget'];
		?>
		<div class="relative rounded-xl p-6 text-center shadow-lg overflow-hidden <?php echo esc_attr( $bg_class ); ?>">
			<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 16px 16px;"></div>
			<?php if ( ! empty( $icon ) ) : ?>
				<div class="text-3xl mb-2"><?php echo esc_html( $icon ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $title ) ) : ?>
				<h3 class="font-extrabold text-base mb-1.5 leading-tight"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<?php if ( ! empty( $desc ) ) : ?>
				<p class="text-blue-100 text-sm mb-4 leading-relaxed"><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>
			<a href="<?php echo esc_url( $btn_url ); ?>" 
			   class="block w-full font-extrabold text-sm py-3 rounded-lg transition-all min-h-[44px] flex items-center justify-center <?php echo esc_attr( $btn_class ); ?>">
				<?php echo esc_html( $btn_text ); ?>
			</a>
			<?php if ( $show_hotline ) :
				$cur_prog_id = function_exists( 'ltdh_detect_current_program_id' ) ? ltdh_detect_current_program_id() : 0;
				$cur_sch_id  = function_exists( 'ltdh_detect_current_school_id' ) ? ltdh_detect_current_school_id() : 0;
				if ( $cur_prog_id > 0 && function_exists( 'ltdh_get_program_hotline' ) ) {
					$hotline = ltdh_get_program_hotline( $cur_prog_id );
				} elseif ( $cur_sch_id > 0 && function_exists( 'ltdh_get_school_hotline' ) ) {
					$hotline = ltdh_get_school_hotline( $cur_sch_id );
				} elseif ( function_exists( 'ltdh_get_hotline' ) ) {
					$hotline = ltdh_get_hotline();
				} else {
					$hotline = '0988 991 496';
				}
				$clean_hotline = preg_replace( '/\D/', '', $hotline );
			?>
				<a href="tel:<?php echo esc_attr( $clean_hotline ); ?>" class="block text-blue-200 text-xs font-semibold hover:text-white transition-colors mt-3">
					📞 <?php echo esc_html( $hotline ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title        = ! empty( $instance['title'] ) ? $instance['title'] : 'Bạn chưa chọn được lộ trình học?';
		$desc         = ! empty( $instance['desc'] ) ? $instance['desc'] : 'Để lại thông tin, ban tư vấn sẽ giải đáp lộ trình liên thông hoàn toàn miễn phí cho bạn.';
		$btn_text     = ! empty( $instance['btn_text'] ) ? $instance['btn_text'] : 'KIỂM TRA ĐIỀU KIỆN NGAY';
		$btn_url      = ! empty( $instance['btn_url'] ) ? $instance['btn_url'] : '/kiem-tra-dieu-kien/';
		$icon         = ! empty( $instance['icon'] ) ? $instance['icon'] : '💡';
		$style        = ! empty( $instance['style'] ) ? $instance['style'] : 'navy';
		$show_hotline = isset( $instance['show_hotline'] ) ? (bool) $instance['show_hotline'] : true;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Tiêu đề:', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'desc' ) ); ?>"><?php esc_html_e( 'Mô tả ngắn:', 'ltdh' ); ?></label>
			<textarea class="widefat" rows="3" id="<?php echo esc_attr( $this->get_field_id( 'desc' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'desc' ) ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'icon' ) ); ?>"><?php esc_html_e( 'Biểu tượng / Emoji:', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'icon' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'icon' ) ); ?>" type="text" value="<?php echo esc_attr( $icon ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'btn_text' ) ); ?>"><?php esc_html_e( 'Nhãn nút bấm:', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'btn_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'btn_text' ) ); ?>" type="text" value="<?php echo esc_attr( $btn_text ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'btn_url' ) ); ?>"><?php esc_html_e( 'Đường dẫn liên kết nút bấm:', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'btn_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'btn_url' ) ); ?>" type="text" value="<?php echo esc_attr( $btn_url ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>"><?php esc_html_e( 'Kiểu giao diện:', 'ltdh' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'style' ) ); ?>">
				<option value="navy" <?php selected( $style, 'navy' ); ?>><?php esc_html_e( 'Xanh Đậm Navy (Mặc định)', 'ltdh' ); ?></option>
				<option value="blue" <?php selected( $style, 'blue' ); ?>><?php esc_html_e( 'Xanh Dương Royal', 'ltdh' ); ?></option>
				<option value="orange" <?php selected( $style, 'orange' ); ?>><?php esc_html_e( 'Cam Nổi Bật', 'ltdh' ); ?></option>
			</select>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_hotline ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_hotline' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_hotline' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_hotline' ) ); ?>"><?php esc_html_e( 'Hiển thị số Hotline bên dưới', 'ltdh' ); ?></label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance                 = [];
		$instance['title']        = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['desc']         = sanitize_textarea_field( $new_instance['desc'] ?? '' );
		$instance['btn_text']     = sanitize_text_field( $new_instance['btn_text'] ?? '' );
		$instance['btn_url']      = esc_url_raw( $new_instance['btn_url'] ?? '' );
		$instance['icon']         = sanitize_text_field( $new_instance['icon'] ?? '' );
		$instance['style']        = in_array( $new_instance['style'] ?? '', [ 'navy', 'blue', 'orange' ], true ) ? $new_instance['style'] : 'navy';
		$instance['show_hotline'] = ! empty( $new_instance['show_hotline'] );
		return $instance;
	}
}
