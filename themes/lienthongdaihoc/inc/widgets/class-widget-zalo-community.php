<?php
/**
 * Widget: Zalo Community Group.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTDH_Widget_Zalo_Community extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'ltdh_zalo_community',
			__( '[LTDH] Nhóm Zalo Cộng đồng', 'ltdh' ),
			[ 'description' => __( 'Hộp kêu gọi tham gia nhóm Zalo trao đổi thông tin tuyển sinh với giao diện nổi bật. Tự động liên kết nhóm Zalo của từng trường.', 'ltdh' ) ]
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

		$title       = ! empty( $instance['title'] ) ? $instance['title'] : 'Nhóm Zalo Trao Đổi';
		$subtitle    = ! empty( $instance['subtitle'] ) ? $instance['subtitle'] : 'Cộng đồng sinh viên';
		$desc        = ! empty( $instance['desc'] ) ? $instance['desc'] : 'Tham gia nhóm Zalo trao đổi thông tin tuyển sinh, lịch học và đề án cùng sinh viên.';
		$btn_text    = ! empty( $instance['btn_text'] ) ? $instance['btn_text'] : '💬 Tham gia Nhóm Zalo';
		$raw_bullets = ! empty( $instance['bullets'] ) ? $instance['bullets'] : "Cập nhật lịch thi & đề án mới nhất\nGiải đáp thắc mắc hồ sơ 24/7";

		// Dynamic tag replacement ({school_name}, {ten_truong}, {school_code}, {ma_truong})
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

		$title       = trim( preg_replace( '/\s+/', ' ', strtr( $title, $replace ) ) );
		$subtitle    = trim( preg_replace( '/\s+/', ' ', strtr( $subtitle, $replace ) ) );
		$desc        = trim( preg_replace( '/\s+/', ' ', strtr( $desc, $replace ) ) );
		$btn_text    = trim( preg_replace( '/\s+/', ' ', strtr( $btn_text, $replace ) ) );
		$raw_bullets = strtr( $raw_bullets, $replace );

		// Context Program & Major IDs
		$current_program_id = function_exists( 'ltdh_detect_current_program_id' ) ? ltdh_detect_current_program_id() : 0;
		$current_major_id   = function_exists( 'ltdh_detect_current_major_id' ) ? ltdh_detect_current_major_id() : 0;

		// Resolve Zalo URL (Program/Major override -> School -> Global)
		$manual_url = ! empty( $instance['zalo_url'] ) ? trim( $instance['zalo_url'] ) : '';
		if ( ! empty( $manual_url ) ) {
			$zalo_url = $manual_url;
		} elseif ( $current_program_id > 0 && ( get_post_meta( $current_program_id, 'zalo_group_url', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'zalo_group_url', $current_program_id ) : '' ) ) ) {
			$zalo_url = get_post_meta( $current_program_id, 'zalo_group_url', true ) ?: get_field( 'zalo_group_url', $current_program_id );
		} elseif ( $current_major_id > 0 && ( get_post_meta( $current_major_id, 'zalo_group_url', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'zalo_group_url', $current_major_id ) : '' ) ) ) {
			$zalo_url = get_post_meta( $current_major_id, 'zalo_group_url', true ) ?: get_field( 'zalo_group_url', $current_major_id );
		} elseif ( $target_school_id > 0 && function_exists( 'ltdh_get_school_zalo_group_url' ) ) {
			$zalo_url = ltdh_get_school_zalo_group_url( $target_school_id );
		} else {
			$zalo_url = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
		}

		$bullets = array_filter( array_map( 'trim', explode( "\n", $raw_bullets ) ) );

		echo $args['before_widget'];
		?>
		<div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-2xl p-5 shadow-md relative overflow-hidden">
			<div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

			<div class="flex items-center gap-3 mb-3 relative z-10">
				<div class="w-10 h-10 rounded-xl bg-white text-blue-600 flex items-center justify-center font-black text-xl shadow-xs shrink-0">
					💬
				</div>
				<div>
					<?php if ( ! empty( $subtitle ) ) : ?>
						<span class="text-[11px] uppercase font-bold tracking-wider text-blue-200 block"><?php echo esc_html( $subtitle ); ?></span>
					<?php endif; ?>
					<h3 class="font-extrabold text-base text-white leading-tight"><?php echo esc_html( $title ); ?></h3>
				</div>
			</div>

			<?php if ( ! empty( $desc ) ) : ?>
				<p class="text-xs text-blue-100 leading-relaxed mb-4 relative z-10">
					<?php echo esc_html( $desc ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $bullets ) ) : ?>
				<ul class="space-y-1.5 text-xs text-blue-50 font-medium mb-4 relative z-10">
					<?php foreach ( $bullets as $bullet ) : ?>
						<li class="flex items-center gap-2">
							<span class="text-emerald-300 font-bold">✓</span>
							<span><?php echo esc_html( $bullet ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<a href="<?php echo esc_url( $zalo_url ); ?>"
			   target="_blank"
			   rel="noopener noreferrer"
			   class="w-full bg-white hover:bg-blue-50 text-blue-700 font-extrabold text-sm py-3 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 min-h-[44px] relative z-10 hover:scale-[1.02]">
				<span><?php echo esc_html( $btn_text ); ?></span>
				<span class="text-xs">→</span>
			</a>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title          = ! empty( $instance['title'] ) ? $instance['title'] : 'Nhóm Zalo Trao Đổi';
		$subtitle       = ! empty( $instance['subtitle'] ) ? $instance['subtitle'] : 'Cộng đồng sinh viên';
		$desc           = ! empty( $instance['desc'] ) ? $instance['desc'] : 'Tham gia nhóm Zalo trao đổi thông tin tuyển sinh, lịch học và đề án cùng sinh viên.';
		$zalo_url       = ! empty( $instance['zalo_url'] ) ? $instance['zalo_url'] : '';
		$btn_text       = ! empty( $instance['btn_text'] ) ? $instance['btn_text'] : '💬 Tham gia Nhóm Zalo';
		$raw_bullets    = ! empty( $instance['bullets'] ) ? $instance['bullets'] : "Cập nhật lịch thi & đề án mới nhất\nGiải đáp thắc mắc hồ sơ 24/7";
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
			<strong style="display:block; margin-bottom:4px; color:#1d4ed8; font-size:13px;">💡 Mỗi trường có 1 link Zalo riêng:</strong>
			1. Cài đặt link Zalo riêng từng trường tại: <strong>Trường Đào tạo &gt; Sửa trường &gt; "Link Nhóm Zalo Trao Đổi (Cộng đồng)"</strong>.<br>
			2. Ở widget này, bạn hãy <strong>ĐỂ TRỐNG ô "Link Nhóm Zalo"</strong> bên dưới. Hệ thống sẽ <strong>tự động nhận diện trường</strong> người dùng đang xem để mở đúng nhóm Zalo của trường đó!<br>
			3. Hỗ trợ biến <code>{school_name}</code> (tên trường) hoặc <code>{school_code}</code> (mã viết tắt) trong Tiêu đề, Tiêu đề phụ, Mô tả.
		</div>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'school_id' ) ); ?>"><strong><?php esc_html_e( 'Chế độ nhận diện trường:', 'ltdh' ); ?></strong></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'school_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'school_id' ) ); ?>">
				<option value="auto" <?php selected( $school_setting, 'auto' ); ?>>⚡ Tự động nhận diện theo trang đang xem (Khuyên dùng)</option>
				<option value="global" <?php selected( $school_setting, 'global' ); ?>>🌐 Dùng nhóm Zalo chung toàn trang (không theo trường)</option>
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
			<small style="color:#64748b;"><?php esc_html_e( 'Ví dụ: Nhóm Zalo {school_code} hoặc Nhóm Zalo Trao Đổi', 'ltdh' ); ?></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>"><?php esc_html_e( 'Tiêu đề phụ:', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'subtitle' ) ); ?>" type="text" value="<?php echo esc_attr( $subtitle ); ?>">
			<small style="color:#64748b;"><?php esc_html_e( 'Ví dụ: Cộng đồng sinh viên {school_name}', 'ltdh' ); ?></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'desc' ) ); ?>"><?php esc_html_e( 'Mô tả:', 'ltdh' ); ?></label>
			<textarea class="widefat" rows="3" id="<?php echo esc_attr( $this->get_field_id( 'desc' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'desc' ) ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'bullets' ) ); ?>"><?php esc_html_e( 'Các quyền lợi (mỗi dòng một ý):', 'ltdh' ); ?></label>
			<textarea class="widefat" rows="3" id="<?php echo esc_attr( $this->get_field_id( 'bullets' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'bullets' ) ); ?>"><?php echo esc_textarea( $raw_bullets ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'zalo_url' ) ); ?>"><?php esc_html_e( 'Link Nhóm Zalo (Ghi đè thủ công nếu muốn):', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'zalo_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'zalo_url' ) ); ?>" type="text" value="<?php echo esc_attr( $zalo_url ); ?>" placeholder="<?php esc_attr_e( 'Để trống để TỰ ĐỘNG lấy link Zalo riêng của từng trường', 'ltdh' ); ?>">
			<small style="color:#64748b; display:block; margin-top:3px;"><?php esc_html_e( '👉 Để trống để hệ thống tự lấy link riêng của trường đang xem.', 'ltdh' ); ?></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'btn_text' ) ); ?>"><?php esc_html_e( 'Nhãn nút bấm:', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'btn_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'btn_text' ) ); ?>" type="text" value="<?php echo esc_attr( $btn_text ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance              = [];
		$instance['school_id'] = sanitize_text_field( $new_instance['school_id'] ?? 'auto' );
		$instance['title']     = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['subtitle']  = sanitize_text_field( $new_instance['subtitle'] ?? '' );
		$instance['desc']      = sanitize_textarea_field( $new_instance['desc'] ?? '' );
		$instance['bullets']   = sanitize_textarea_field( $new_instance['bullets'] ?? '' );
		$instance['zalo_url']  = esc_url_raw( $new_instance['zalo_url'] ?? '' );
		$instance['btn_text']  = sanitize_text_field( $new_instance['btn_text'] ?? '' );
		return $instance;
	}
}
