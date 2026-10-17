<?php
/**
 * Widget Registration, Sidebars, and Custom LTDH Widgets.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Widget Areas (Sidebars).
 */
function ltdh_widgets_init() {
	// 1. Sidebar Tin tức & Blog (Primary Sidebar)
	register_sidebar( [
		'name'          => __( 'Thanh bên Tin tức & Blog (Blog Sidebar)', 'ltdh' ),
		'id'            => 'sidebar-blog',
		'description'   => __( 'Khu vực widget hiển thị ở thanh bên trang tin tức, bài viết chi tiết, danh mục tin tức và tìm kiếm.', 'ltdh' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">',
		'after_title'   => '</h3>',
	] );

	// 2. Sidebar Cẩm nang & Hướng dẫn Tuyển sinh
	register_sidebar( [
		'name'          => __( 'Thanh bên Cẩm nang Tuyển sinh (Guide Sidebar)', 'ltdh' ),
		'id'            => 'sidebar-guide',
		'description'   => __( 'Khu vực widget hiển thị ở thanh bên trang chi tiết cẩm nang & hướng dẫn tuyển sinh.', 'ltdh' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">',
		'after_title'   => '</h3>',
	] );

	// 3. Sidebar Chương trình Đào tạo
	register_sidebar( [
		'name'          => __( 'Thanh bên Chương trình Đào tạo (Program Sidebar)', 'ltdh' ),
		'id'            => 'sidebar-program',
		'description'   => __( 'Khu vực widget hiển thị ở thanh bên trang chi tiết chương trình đào tạo.', 'ltdh' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">',
		'after_title'   => '</h3>',
	] );

	// 4. Sidebar Trường Đào tạo
	register_sidebar( [
		'name'          => __( 'Thanh bên Trường Đào tạo (School Sidebar)', 'ltdh' ),
		'id'            => 'sidebar-school',
		'description'   => __( 'Khu vực widget hiển thị ở thanh bên trang chi tiết trường đại học.', 'ltdh' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">',
		'after_title'   => '</h3>',
	] );

	// 5. Sidebar Ngành Đào tạo
	register_sidebar( [
		'name'          => __( 'Thanh bên Ngành Đào tạo (Major Sidebar)', 'ltdh' ),
		'id'            => 'sidebar-major',
		'description'   => __( 'Khu vực widget hiển thị ở thanh bên trang chi tiết ngành đào tạo.', 'ltdh' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">',
		'after_title'   => '</h3>',
	] );

	// 6. Sidebar Trang tĩnh (Page Sidebar)
	register_sidebar( [
		'name'          => __( 'Thanh bên Trang tĩnh (Page Sidebar)', 'ltdh' ),
		'id'            => 'sidebar-page',
		'description'   => __( 'Khu vực widget hiển thị ở thanh bên cho các trang tĩnh (khi bật giao diện có thanh bên).', 'ltdh' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s bg-white border border-slate-200 rounded-xl p-5 md:p-6 shadow-xs">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">',
		'after_title'   => '</h3>',
	] );

	// Register Custom LTDH Widgets
	register_widget( 'LTDH_Widget_Categories' );
	register_widget( 'LTDH_Widget_Recent_Posts' );
	register_widget( 'LTDH_Widget_CTA' );
	register_widget( 'LTDH_Widget_Hotline' );
	register_widget( 'LTDH_Widget_Consultation_Form' );
	register_widget( 'LTDH_Widget_Zalo_Community' );
	register_widget( 'LTDH_Widget_School_Info' );
}
add_action( 'widgets_init', 'ltdh_widgets_init' );


// =========================================================================
// CUSTOM WIDGET 1: DANH MỤC TIN TỨC / CẨM NANG
// =========================================================================
class LTDH_Widget_Categories extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'ltdh_categories',
			__( '[LTDH] Danh mục tin tức', 'ltdh' ),
			[ 'description' => __( 'Hiển thị danh sách chuyên mục cẩm nang & tin tức kèm số lượng bài viết và trạng thái đang chọn.', 'ltdh' ) ]
		);
	}

	public function widget( $args, $instance ) {
		$title      = ! empty( $instance['title'] ) ? $instance['title'] : '📂 Chuyên mục cẩm nang';
		$show_all   = isset( $instance['show_all'] ) ? (bool) $instance['show_all'] : true;
		$show_count = isset( $instance['show_count'] ) ? (bool) $instance['show_count'] : true;
		$hide_empty = isset( $instance['hide_empty'] ) ? (bool) $instance['hide_empty'] : true;

		$blog_cats = get_categories( [
			'hide_empty' => $hide_empty,
			'exclude'    => [ 1 ], // exclude uncategorized
		] );

		$active_cat_slug = '';
		if ( is_category() ) {
			$cat_obj = get_queried_object();
			if ( $cat_obj && ! empty( $cat_obj->slug ) ) {
				$active_cat_slug = $cat_obj->slug;
			}
		} elseif ( isset( $_GET['danh-muc'] ) ) {
			$active_cat_slug = sanitize_text_field( wp_unslash( $_GET['danh-muc'] ) );
		} elseif ( is_single() ) {
			$post_cats = get_the_category( get_the_ID() );
			foreach ( $post_cats as $pc ) {
				if ( $pc->slug !== 'uncategorized' ) {
					$active_cat_slug = $pc->slug;
					break;
				}
			}
		}

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		?>
		<ul class="space-y-1">
			<?php if ( $show_all ) :
				$total_posts = wp_count_posts( 'post' )->publish;
				$is_all_active = empty( $active_cat_slug );
			?>
				<li>
					<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" 
					   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-bold transition-all <?php echo $is_all_active ? 'bg-slate-50 text-brand-primary' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[40px]">
						<span><?php esc_html_e( 'Tất cả tin tức', 'ltdh' ); ?></span>
						<?php if ( $show_count ) : ?>
							<span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500"><?php echo esc_html( $total_posts ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endif; ?>

			<?php foreach ( $blog_cats as $bcat ) :
				$is_active = ( $active_cat_slug === $bcat->slug );
			?>
				<li>
					<a href="<?php echo esc_url( home_url( '/tin-tuc/?danh-muc=' . $bcat->slug ) ); ?>" 
					   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-bold transition-all <?php echo $is_active ? 'bg-slate-50 text-brand-primary' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[40px]">
						<span><?php echo esc_html( $bcat->name ); ?></span>
						<?php if ( $show_count ) : ?>
							<span class="text-xs px-2 py-0.5 rounded-full <?php echo $is_active ? 'bg-blue-50 text-brand-primary' : 'bg-slate-100 text-slate-500'; ?>"><?php echo esc_html( $bcat->count ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title      = ! empty( $instance['title'] ) ? $instance['title'] : '📂 Chuyên mục cẩm nang';
		$show_all   = isset( $instance['show_all'] ) ? (bool) $instance['show_all'] : true;
		$show_count = isset( $instance['show_count'] ) ? (bool) $instance['show_count'] : true;
		$hide_empty = isset( $instance['hide_empty'] ) ? (bool) $instance['hide_empty'] : true;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Tiêu đề:', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_all ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_all' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_all' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_all' ) ); ?>"><?php esc_html_e( 'Hiển thị mục "Tất cả tin tức"', 'ltdh' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_count ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"><?php esc_html_e( 'Hiển thị số lượng bài viết', 'ltdh' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $hide_empty ); ?> id="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_empty' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>"><?php esc_html_e( 'Ẩn chuyên mục trống', 'ltdh' ); ?></label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance               = [];
		$instance['title']      = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['show_all']   = ! empty( $new_instance['show_all'] );
		$instance['show_count'] = ! empty( $new_instance['show_count'] );
		$instance['hide_empty'] = ! empty( $new_instance['hide_empty'] );
		return $instance;
	}
}


// =========================================================================
// CUSTOM WIDGET 2: BÀI VIẾT MỚI NHẤT / XEM NHIỀU
// =========================================================================
class LTDH_Widget_Recent_Posts extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'ltdh_recent_posts',
			__( '[LTDH] Bài viết mới nhất / Xem nhiều', 'ltdh' ),
			[ 'description' => __( 'Hiển thị danh sách bài viết mới nhất có ảnh thumbnail và ngày đăng.', 'ltdh' ) ]
		);
	}

	public function widget( $args, $instance ) {
		$title        = ! empty( $instance['title'] ) ? $instance['title'] : '🔥 Tin xem nhiều nhất';
		$number       = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$category     = ! empty( $instance['category'] ) ? absint( $instance['category'] ) : 0;
		$show_thumb   = isset( $instance['show_thumb'] ) ? (bool) $instance['show_thumb'] : true;
		$show_date    = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : true;

		$query_args = [
			'post_type'           => 'post',
			'posts_per_page'      => $number,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		];

		if ( is_single() ) {
			$query_args['post__not_in'] = [ get_the_ID() ];
		}
		if ( $category > 0 ) {
			$query_args['cat'] = $category;
		}

		$recent_posts = get_posts( $query_args );
		if ( empty( $recent_posts ) ) {
			return;
		}

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		?>
		<ul class="space-y-4">
			<?php foreach ( $recent_posts as $rp ) :
				$rp_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $rp->ID, 'thumbnail' ) : ( get_the_post_thumbnail_url( $rp->ID, 'thumbnail' ) ?: ltdh_get_fallback_image( 'post' ) );
			?>
				<li class="flex items-start gap-3">
					<?php if ( $show_thumb ) : ?>
						<a href="<?php echo esc_url( get_permalink( $rp->ID ) ); ?>" class="w-12 h-12 rounded-lg bg-slate-100 overflow-hidden shrink-0 border border-slate-100 block">
							<img src="<?php echo esc_url( $rp_thumb ); ?>" alt="<?php echo esc_attr( $rp->post_title ); ?>" class="w-full h-full object-cover" loading="lazy">
						</a>
					<?php endif; ?>
					<div class="min-w-0 flex-1">
						<a href="<?php echo esc_url( get_permalink( $rp->ID ) ); ?>" class="text-sm font-bold text-slate-800 hover:text-brand-primary leading-snug block line-clamp-2 transition-all">
							<?php echo esc_html( $rp->post_title ); ?>
						</a>
						<?php if ( $show_date ) : ?>
							<time class="text-xs text-slate-400 mt-0.5 block"><?php echo get_the_date( 'd/m/Y', $rp->ID ); ?></time>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title      = ! empty( $instance['title'] ) ? $instance['title'] : '🔥 Tin xem nhiều nhất';
		$number     = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$category   = ! empty( $instance['category'] ) ? absint( $instance['category'] ) : 0;
		$show_thumb = isset( $instance['show_thumb'] ) ? (bool) $instance['show_thumb'] : true;
		$show_date  = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : true;

		$categories = get_categories( [ 'hide_empty' => false ] );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Tiêu đề:', 'ltdh' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Số lượng bài viết:', 'ltdh' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" max="20" value="<?php echo esc_attr( $number ); ?>" size="3">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>"><?php esc_html_e( 'Chuyên mục:', 'ltdh' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>">
				<option value="0"><?php esc_html_e( '— Tất cả chuyên mục —', 'ltdh' ); ?></option>
				<?php foreach ( $categories as $cat ) : ?>
					<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( $category, $cat->term_id ); ?>>
						<?php echo esc_html( $cat->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_thumb ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_thumb' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_thumb' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_thumb' ) ); ?>"><?php esc_html_e( 'Hiển thị ảnh thumbnail', 'ltdh' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>"><?php esc_html_e( 'Hiển thị ngày đăng', 'ltdh' ); ?></label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance               = [];
		$instance['title']      = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['number']     = absint( $new_instance['number'] ?? 5 );
		$instance['category']   = absint( $new_instance['category'] ?? 0 );
		$instance['show_thumb'] = ! empty( $new_instance['show_thumb'] );
		$instance['show_date']  = ! empty( $new_instance['show_date'] );
		return $instance;
	}
}


// =========================================================================
// CUSTOM WIDGET 3: BANNER CTA KÊU GỌI HÀNH ĐỘNG
// =========================================================================
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
				$hotline       = function_exists( 'ltdh_get_school_hotline' ) ? ltdh_get_school_hotline() : ( function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496' );
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


// =========================================================================
// CUSTOM WIDGET 4: HOTLINE & ZALO TUYỂN SINH
// =========================================================================
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
				$target_school_id = function_exists( 'ltdh_get_current_context_school_id' ) ? ltdh_get_current_context_school_id() : 0;
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

		// Hotline: if set manually in widget, use it; else resolve from school context; else fallback to global
		$manual_hotline = ! empty( $instance['hotline'] ) ? trim( $instance['hotline'] ) : '';
		if ( ! empty( $manual_hotline ) ) {
			$hotline = $manual_hotline;
		} elseif ( $target_school_id > 0 && function_exists( 'ltdh_get_school_hotline' ) ) {
			$hotline = ltdh_get_school_hotline( $target_school_id );
		} else {
			$hotline = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496';
		}

		// Zalo URL: if set manually in widget, use it; else resolve from school context; else fallback to global
		$manual_zalo = ! empty( $instance['zalo_url'] ) ? trim( $instance['zalo_url'] ) : '';
		if ( ! empty( $manual_zalo ) ) {
			$zalo_url = $manual_zalo;
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


// =========================================================================
// CUSTOM WIDGET 5: FORM ĐĂNG KÝ TƯ VẤN
// =========================================================================
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
		<div class="ltdh-widget-form">
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
					$hidden['current_program_id'] = get_the_ID();
					$school_id = get_field( 'school_relationship', get_the_ID() );
					if ( $school_id ) {
						$hidden['current_school_id'] = is_object( $school_id ) ? $school_id->ID : (int) $school_id;
					}
				} elseif ( is_singular( 'school' ) ) {
					$hidden['current_school_id'] = get_the_ID();
				} elseif ( is_singular( 'major' ) ) {
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


// =========================================================================
// CUSTOM WIDGET 6: NHÓM ZALO CỘNG ĐỒNG
// =========================================================================
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
				$target_school_id = function_exists( 'ltdh_get_current_context_school_id' ) ? ltdh_get_current_context_school_id() : 0;
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

		// Resolve Zalo URL
		$manual_url = ! empty( $instance['zalo_url'] ) ? trim( $instance['zalo_url'] ) : '';
		if ( ! empty( $manual_url ) ) {
			$zalo_url = $manual_url;
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


// =========================================================================
// CUSTOM WIDGET 7: THÔNG TIN TRƯỜNG ĐÀO TẠO (TỰ ĐỘNG THEO NGỮ CẢNH)
// =========================================================================
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


// =========================================================================
// DEFAULT SIDEBAR FALLBACK RENDERERS
// =========================================================================

/**
 * Render default blog sidebar when no widgets are active in 'sidebar-blog'.
 */
function ltdh_render_default_blog_sidebar() {
	$blog_cats = get_categories( [ 'hide_empty' => true, 'exclude' => [ 1 ] ] );
	$recent_posts = get_posts( [
		'numberposts' => 5,
		'post__not_in' => is_single() ? [ get_the_ID() ] : [],
	] );

	$active_cat_slug = '';
	if ( is_category() ) {
		$cat_obj = get_queried_object();
		if ( $cat_obj && ! empty( $cat_obj->slug ) ) {
			$active_cat_slug = $cat_obj->slug;
		}
	} elseif ( isset( $_GET['danh-muc'] ) ) {
		$active_cat_slug = sanitize_text_field( wp_unslash( $_GET['danh-muc'] ) );
	} elseif ( is_single() ) {
		$post_cats = get_the_category( get_the_ID() );
		foreach ( $post_cats as $pc ) {
			if ( $pc->slug !== 'uncategorized' ) {
				$active_cat_slug = $pc->slug;
				break;
			}
		}
	}
	?>
	<!-- Sidebar Category Card -->
	<div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
		<h3 class="font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100">📂 Chuyên mục cẩm nang</h3>
		<ul class="space-y-1">
			<li>
				<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" 
				   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-bold transition-all <?php echo empty( $active_cat_slug ) ? 'bg-slate-50 text-brand-primary' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[40px]">
					<span>Tất cả tin tức</span>
					<span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500"><?php echo esc_html( wp_count_posts( 'post' )->publish ); ?></span>
				</a>
			</li>
			<?php foreach ( $blog_cats as $bcat ) :
				$is_active_cat = ( $active_cat_slug === $bcat->slug );
			?>
				<li>
					<a href="<?php echo esc_url( home_url( '/tin-tuc/?danh-muc=' . $bcat->slug ) ); ?>" 
					   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-bold transition-all <?php echo $is_active_cat ? 'bg-slate-50 text-brand-primary' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[40px]">
						<span><?php echo esc_html( $bcat->name ); ?></span>
						<span class="text-xs px-2 py-0.5 rounded-full <?php echo $is_active_cat ? 'bg-blue-50 text-brand-primary' : 'bg-slate-100 text-slate-500'; ?>"><?php echo esc_html( $bcat->count ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<!-- Featured Posts / Recent Widget -->
	<div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
		<h3 class="font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100">🔥 Tin xem nhiều nhất</h3>
		<ul class="space-y-4">
			<?php foreach ( $recent_posts as $rp ) :
				$rp_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $rp->ID, 'thumbnail' ) : ( get_the_post_thumbnail_url( $rp->ID, 'thumbnail' ) ?: ltdh_get_fallback_image( 'post' ) );
			?>
				<li class="flex items-start gap-3">
					<a href="<?php echo esc_url( get_permalink( $rp->ID ) ); ?>" class="w-12 h-12 rounded-lg bg-slate-100 overflow-hidden shrink-0 border border-slate-100 block">
						<img src="<?php echo esc_url( $rp_thumb ); ?>" alt="<?php echo esc_attr( $rp->post_title ); ?>" class="w-full h-full object-cover" loading="lazy">
					</a>
					<div class="min-w-0">
						<a href="<?php echo esc_url( get_permalink( $rp->ID ) ); ?>" class="text-sm font-bold text-slate-800 hover:text-brand-primary leading-snug block line-clamp-2 transition-all">
							<?php echo esc_html( $rp->post_title ); ?>
						</a>
						<time class="text-xs text-slate-400 mt-0.5 block"><?php echo get_the_date( 'd/m/Y', $rp->ID ); ?></time>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<!-- Premium CTA Consultation Sidebar Card -->
	<div class="relative bg-gradient-to-tr from-[#0E2038] to-brand-primary rounded-xl p-6 text-center text-white shadow-lg overflow-hidden">
		<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 16px 16px;"></div>
		<div class="text-3xl mb-2">💡</div>
		<h3 class="font-extrabold text-base mb-1.5 leading-tight">Bạn chưa chọn được lộ trình học?</h3>
		<p class="text-blue-100 text-sm mb-4 leading-relaxed">Để lại thông tin, ban tư vấn sẽ giải đáp lộ trình liên thông hoàn toàn miễn phí cho bạn.</p>
		<a href="<?php echo esc_url( home_url( '/kiem-tra-dieu-kien/' ) ); ?>" 
		   class="block w-full bg-brand-accent text-white font-extrabold text-sm py-3 rounded-lg hover:bg-[#e06e00] transition-all shadow-md shadow-brand-accent/20 min-h-[44px] flex items-center justify-center">
			KIỂM TRA ĐIỀU KIỆN NGAY
		</a>
	</div>
	<?php
}

/**
 * Render default guide sidebar when no widgets are active in 'sidebar-guide'.
 */
function ltdh_render_default_guide_sidebar() {
	$hotline      = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496';
	$global_zalo  = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
	?>
	<!-- Sidebar register widget -->
	<section class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
		<h3 class="text-lg font-bold text-slate-900 mb-2">Nhận thêm thông tin chi tiết</h3>
		<p class="text-sm text-slate-500 mb-4">Để lại câu hỏi của bạn. Ban tuyển sinh đại học đối tác sẽ giải đáp nhanh chóng sau 15 phút.</p>
		
		<?php if ( isset( $_GET['submit_success'] ) && '1' === $_GET['submit_success'] ) : ?>
			<div class="p-3 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
				<span class="font-bold">Gửi thông tin thành công!</span> Chúng tôi sẽ liên hệ lại sớm nhất.
			</div>
		<?php endif; ?>
		<?php 
		if ( function_exists( 'wpcf7_contact_form_html' ) ) :
			echo do_shortcode( '[contact-form-7 id="consultation-form" title="Form Tư vấn"]' );
		else :
		?>
			<form action="" method="POST" class="space-y-4">
				<?php wp_nonce_field( 'ltdh_native_lead_submit_action', 'ltdh_native_lead_nonce' ); ?>
				<input type="hidden" name="referral_source" value="<?php echo esc_attr( get_permalink() ); ?>">
				<div class="hidden" style="display:none !important;" aria-hidden="true">
					<label for="hp_website_guide">Website</label>
					<input type="text" name="hp_website" id="hp_website_guide" tabindex="-1" autocomplete="off" value="">
				</div>

				<div>
					<label class="block text-sm font-semibold text-slate-600 mb-1">Họ và tên *</label>
					<input type="text" name="your-name" required class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Họ và tên của bạn">
				</div>
				<div>
					<label class="block text-sm font-semibold text-slate-600 mb-1">Số điện thoại *</label>
					<input type="tel" name="your-phone" required class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Số điện thoại liên hệ">
				</div>

				<div class="pt-0.5">
					<label class="flex items-start gap-2 p-2 rounded-lg bg-amber-50/90 border border-amber-200 cursor-pointer hover:bg-amber-100/60 transition-colors select-none">
						<input type="checkbox" name="is_group_reg" value="1" class="mt-0.5 w-4 h-4 rounded border-amber-300 text-brand-primary focus:ring-brand-primary">
						<span class="text-xs text-slate-700 leading-snug">
							🎁 <strong class="font-bold text-amber-900">Đăng ký nhóm:</strong> Nhận học bổng đồng hành (từ 2 bạn)
						</span>
					</label>
				</div>

				<?php if ( function_exists( 'ltdh_render_recaptcha_field' ) ) : ?>
					<?php ltdh_render_recaptcha_field( 'sidebar_lead' ); ?>
				<?php endif; ?>
				
				<button type="submit" class="w-full bg-brand-accent text-white py-3 rounded-lg text-sm font-bold shadow-md hover:bg-[#e06e00] transition-all">
					Gửi Thông Tin Yêu Cầu
				</button>
			</form>
		<?php endif; ?>
	</section>

	<!-- Zalo / Call Card -->
	<?php
	get_template_part( 'template-parts/sidebar-hotline', null, [
		'hotline'  => $hotline,
		'zalo_url' => $global_zalo,
		'title'    => 'Văn phòng tuyển sinh',
	] );
}

if ( ! function_exists( 'ltdh_render_school_info_card' ) ) {
	/**
	 * Render School Info Card (for sidebars).
	 *
	 * @param int    $school_id
	 * @param string $wrapper_class
	 */
	function ltdh_render_school_info_card( $school_id, $wrapper_class = 'bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden' ) {
		if ( ! $school_id ) {
			return;
		}
		$school_title     = get_the_title( $school_id );
		$school_logo_url  = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'thumbnail' ) : get_stylesheet_directory_uri() . '/assets/images/cropped-logo-scaled-2.webp';
		$school_cover_url = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'medium' ) : ( function_exists( 'ltdh_get_fallback_image' ) ? ltdh_get_fallback_image( 'school' ) : '' );
		$school_address   = get_post_meta( $school_id, 'address', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'address', $school_id ) : '' ) ?: 'Việt Nam';
		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>">
			<div class="h-28 bg-cover bg-center relative" style="background-image: url('<?php echo esc_url( $school_cover_url ); ?>');">
				<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
			</div>
			<div class="p-4 sm:p-5 text-center relative -mt-12">
				<div class="w-20 h-20 rounded-2xl bg-white p-2 shadow-md mx-auto mb-3 border border-slate-100 flex items-center justify-center overflow-hidden">
					<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_title ); ?>" class="max-w-full max-h-full object-contain">
				</div>
				<h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug mb-1">
					<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="hover:text-brand-primary transition-colors">
						<?php echo esc_html( $school_title ); ?>
					</a>
				</h3>
				<p class="text-xs text-slate-500 mb-3 line-clamp-2">📍 <?php echo esc_html( $school_address ); ?></p>

				<div class="border-t border-slate-100 pt-3">
					<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="text-brand-primary font-bold text-xs sm:text-sm hover:underline flex items-center justify-center gap-1">
						<span>Xem chi tiết trường</span> <span>→</span>
					</a>
				</div>
			</div>
		</div>
		<?php
	}
}

/**
 * Render default program sidebar when no widgets are active in 'sidebar-program'.
 *
 * @param int $program_id
 * @param int $school_id
 */
function ltdh_render_default_program_sidebar( $program_id, $school_id = 0 ) {
	$global_zalo     = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
	$program_hotline = ( $school_id ? ( get_post_meta( $school_id, 'hotline', true ) ?: get_field( 'hotline', $school_id ) ) : '' ) ?: ( function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496' );
	$major_id        = 0;
	$major_rel       = get_field( 'major_relationship', $program_id );
	if ( ! empty( $major_rel ) ) {
		$major_id = is_object( $major_rel ) ? $major_rel->ID : ( is_array( $major_rel ) && ! empty( $major_rel[0] ) ? ( is_object( $major_rel[0] ) ? $major_rel[0]->ID : (int) $major_rel[0] ) : (int) $major_rel );
	}
	$school_title = $school_id ? get_the_title( $school_id ) : '';
	?>
	<!-- SCHOOL INFO CARD (Desktop Only >= 1024px) -->
	<?php 
	if ( $school_id ) {
		ltdh_render_school_info_card( $school_id, 'hidden lg:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden' );
	}
	?>

	<!-- CONSULTATION FORM -->
	<section id="register" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6">
		<h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2">Đăng ký tư vấn miễn phí</h3>
		<p class="text-sm text-slate-500 mb-4">Hãy để lại thông tin, ban tư vấn tuyển sinh sẽ liên hệ và giải đáp lộ trình cụ thể cho bạn trong vòng 15 phút.</p>
		
		<?php 
		if ( function_exists( 'ltdh_render_consultation_form' ) ) {
			ltdh_render_consultation_form( [
				'current_program_id' => $program_id,
				'current_school_id'  => $school_id,
				'current_major_id'   => $major_id,
				'referral_source'    => get_permalink(),
			] );
		}
		?>
	</section>

	<!-- ZALO GROUP COMMUNITY WIDGET -->
	<?php
	$program_school_zalo = ( $school_id ? get_field( 'zalo_group_url', $school_id ) : '' ) ?: $global_zalo;
	?>
	<div class="hidden lg:block bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-2xl p-5 shadow-md relative overflow-hidden">
		<div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

		<div class="flex items-center gap-3 mb-3 relative z-10">
			<div class="w-10 h-10 rounded-xl bg-white text-blue-600 flex items-center justify-center font-black text-xl shadow-xs shrink-0">
				💬
			</div>
			<div>
				<span class="text-[11px] uppercase font-bold tracking-wider text-blue-200 block">Cộng đồng sinh viên</span>
				<h3 class="font-extrabold text-base text-white leading-tight">Nhóm Zalo Trao Đổi</h3>
			</div>
		</div>

		<p class="text-xs text-blue-100 leading-relaxed mb-4 relative z-10">
			Tham gia nhóm Zalo trao đổi thông tin tuyển sinh, lịch học và đề án cùng sinh viên <?php echo esc_html( $school_title ?: 'chương trình' ); ?>.
		</p>

		<ul class="space-y-1.5 text-xs text-blue-50 font-medium mb-4 relative z-10">
			<li class="flex items-center gap-2">
				<span class="text-emerald-300 font-bold">✓</span>
				<span>Cập nhật lịch thi & đề án mới nhất</span>
			</li>
			<li class="flex items-center gap-2">
				<span class="text-emerald-300 font-bold">✓</span>
				<span>Giải đáp thắc mắc hồ sơ 24/7</span>
			</li>
		</ul>

		<a href="<?php echo esc_url( $program_school_zalo ); ?>"
		   target="_blank"
		   rel="noopener noreferrer"
		   class="w-full bg-white hover:bg-blue-50 text-blue-700 font-extrabold text-sm py-3 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 min-h-[44px] relative z-10 hover:scale-[1.02]">
			<span>💬 Tham gia Nhóm Zalo</span>
			<span class="text-xs">→</span>
		</a>
	</div>

	<!-- RELATED NEWS & ANNOUNCEMENTS (Sidebar - Desktop Only) -->
	<?php
	$program_news_meta = [ 'relation' => 'OR' ];
	$program_news_meta[] = [
		'key'     => 'related_programs',
		'value'   => '"' . $program_id . '"',
		'compare' => 'LIKE',
	];
	if ( $school_id ) {
		$program_news_meta[] = [
			'key'     => 'related_schools',
			'value'   => '"' . $school_id . '"',
			'compare' => 'LIKE',
		];
	}

	$related_news_query = new WP_Query( [
		'post_type'      => [ 'post', 'guide' ],
		'posts_per_page' => 6,
		'post_status'    => 'publish',
		'meta_query'     => $program_news_meta,
	] );

	if ( $related_news_query->have_posts() ) :
		$has_more = ( $related_news_query->post_count > 5 );
	?>
		<section class="hidden lg:block bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-5">
			<h3 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-3">Tin tức & Thông báo liên quan</h3>
			<div class="space-y-3.5">
				<?php
				$news_counter = 0;
				while ( $related_news_query->have_posts() ) :
					$related_news_query->the_post();
					if ( $news_counter >= 5 ) {
						continue;
					}
					$news_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( get_the_ID(), 'thumbnail' ) : ( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ?: ltdh_get_fallback_image( 'post' ) );
					$news_counter++;
				?>
					<div class="flex gap-3 items-start pb-3 border-b border-slate-100 last:border-b-0 last:pb-0">
						<a href="<?php the_permalink(); ?>" class="shrink-0">
							<img src="<?php echo esc_url( $news_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-12 h-12 object-cover rounded-lg" loading="lazy">
						</a>
						
						<div class="flex-1 min-w-0">
							<h4 class="font-bold text-slate-800 text-sm hover:text-brand-primary transition-colors line-clamp-2 leading-snug">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h4>
							<p class="text-xs text-slate-400 mt-0.5">📅 <?php echo get_the_date(); ?></p>
						</div>
					</div>
				<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php if ( $has_more ) : ?>
				<div class="mt-4 pt-3 border-t border-slate-100">
					<a href="<?php echo esc_url( home_url( '/tin-tuc/?chuong-trinh=' . $program_id ) ); ?>" class="w-full text-center bg-slate-100 text-slate-700 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-200/80 transition-all flex items-center justify-center gap-1.5 min-h-[38px]">
						<span>Xem thêm tin tức</span>
						<span>→</span>
					</a>
				</div>
			<?php endif; ?>
		</section>
	<?php
	endif;
	?>

	<!-- HOTLINE CONTACT CARD -->
	<?php
	get_template_part( 'template-parts/sidebar-hotline', null, [
		'hotline'     => $program_hotline,
		'zalo_url'    => $global_zalo,
		'title'       => 'Văn phòng tuyển sinh',
		'extra_class' => 'hidden lg:block',
	] );
	?>

	<!-- COMPARE BUTTON ONLY (Desktop Only) -->
	<?php
	$types = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
	$type_slug = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->slug : '';
	$major_rel_id_raw = get_field( LTDH_META_MAJOR_REL, $program_id );
	$major_rel_id = 0;
	if ( is_array( $major_rel_id_raw ) ) {
		$major_rel_id = ! empty( $major_rel_id_raw ) ? ( is_object( $major_rel_id_raw[0] ) ? $major_rel_id_raw[0]->ID : $major_rel_id_raw[0] ) : 0;
	} elseif ( is_object( $major_rel_id_raw ) ) {
		$major_rel_id = $major_rel_id_raw->ID;
	} elseif ( $major_rel_id_raw ) {
		$major_rel_id = intval( $major_rel_id_raw );
	}
	$major_slug = $major_rel_id ? get_post_field( 'post_name', $major_rel_id ) : '';
	$major_name = $major_rel_id ? get_the_title( $major_rel_id ) : '';
	?>
	<button type="button"
			class="hidden lg:flex w-full text-center bg-slate-100 hover:bg-slate-200/80 text-slate-700 py-3.5 rounded-xl font-bold transition-all ltdh-compare-single-btn text-sm flex items-center justify-center gap-2 mt-4 min-h-[44px] cursor-pointer"
			data-compare-type="program" data-compare-id="<?php echo esc_attr( $program_id ); ?>"
			data-compare-title="<?php echo esc_attr( get_the_title( $program_id ) ); ?>"
			data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $program_id ) ); ?>"
			data-compare-he="<?php echo esc_attr( $type_slug ); ?>"
			data-compare-nganh="<?php echo esc_attr( $major_slug ); ?>"
			data-compare-major-name="<?php echo esc_attr( preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) ) ); ?>"
			data-compare-school-name="<?php echo esc_attr( $school_title ); ?>">
		<span>📊</span> <span>Thêm vào so sánh</span>
	</button>
<?php
}

/**
 * Render default school sidebar when no widgets are active in 'sidebar-school'.
 *
 * @param int $school_id
 */
function ltdh_render_default_school_sidebar( $school_id ) {
	$global_zalo = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
	$hotline     = ( get_post_meta( $school_id, 'hotline', true ) ?: get_field( 'hotline', $school_id ) ) ?: ( function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496' );
	$school_title = get_the_title( $school_id );
	$website     = get_field( 'website', $school_id );
	$address     = get_post_meta( $school_id, 'address', true ) ?: get_field( 'address', $school_id );
	$map_url     = get_field( 'google_maps_url', $school_id );
	$zalo_group  = get_field( 'zalo_group_url', $school_id ) ?: $global_zalo;
	?>
	<!-- School Mini Card -->
	<?php
	$school_logo_url  = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'medium' ) : '';
	$school_cover_url = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'medium_large' ) : ( get_field( 'school_banner', $school_id ) ?: get_template_directory_uri() . '/assets/images/banner-school.jpg' );
	$map_url          = get_field( 'google_map_url', $school_id ) ?: get_field( 'map_url', $school_id );
	if ( ! $map_url ) {
		$map_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $school_title . ' ' . ( $address ?: '' ) );
	}
	$words = explode( ' ', $school_title );
	$initials = '';
	foreach ( array_slice( $words, -3 ) as $w ) {
		$initials .= mb_substr( $w, 0, 1 );
	}
	$initials = mb_strtoupper( $initials );
	?>


	<!-- Consultation Form -->
	<section id="register" class="bg-white rounded-2xl lg:rounded-lg shadow-sm border border-slate-100 lg:border-slate-200 p-4.5 lg:p-6 relative overflow-hidden">
		<div class="inline-flex lg:hidden items-center gap-1.5 px-2.5 py-1 rounded-full bg-brand-primary/5 text-brand-primary text-[11px] font-bold tracking-wide uppercase mb-3">
			<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
			<span>Tư vấn tuyển sinh</span>
		</div>
		<h3 class="text-base lg:text-lg font-bold text-slate-900 mb-1.5 lg:mb-2 leading-snug">Đăng ký vào <?php echo esc_html( $school_title ); ?></h3>
		<p class="text-xs lg:text-sm text-slate-500 mb-4 leading-relaxed">Nhận tư vấn hồ sơ miễn phí, hỗ trợ xử lý thủ tục nhập học nhanh chóng.</p>
		
		<?php 
		if ( function_exists( 'ltdh_render_consultation_form' ) ) {
			ltdh_render_consultation_form( [
				'current_school_id' => $school_id,
				'referral_source'   => get_permalink(),
			] );
		}
		?>
	</section>

	<!-- ADMISSION FORMS DOWNLOAD (Sidebar) -->
	<?php
	$school_admission_forms = function_exists( 'ltdh_get_school_admission_forms' ) ? ltdh_get_school_admission_forms( $school_id ) : [];
	if ( ! empty( $school_admission_forms ) ) :
	?>
		<section class="bg-white rounded-2xl lg:rounded-lg shadow-sm border border-slate-100 lg:border-slate-200 p-4.5 lg:p-5">
			<div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-3">
				<span class="w-7 h-7 rounded-lg bg-blue-50 text-[#00308b] flex items-center justify-center text-sm font-bold shrink-0">📄</span>
				<h3 class="text-base font-extrabold text-slate-900 leading-snug">Mẫu phiếu tuyển sinh</h3>
			</div>
			<p class="text-xs text-slate-500 mb-3.5 leading-relaxed">Tải mẫu phiếu đăng ký chính thức để hoàn thiện hồ sơ xét tuyển vào trường.</p>
			<div class="space-y-2">
				<?php foreach ( $school_admission_forms as $form_item ) : ?>
					<a href="<?php echo esc_url( $form_item['url'] ); ?>" download target="_blank" rel="noopener noreferrer" class="flex items-center justify-between gap-2 p-2.5 bg-blue-50/50 hover:bg-blue-50 rounded-lg border border-blue-100/70 text-xs font-semibold text-[#00308b] transition-all cursor-pointer group">
						<span class="truncate"><?php echo esc_html( $form_item['label'] ); ?></span>
						<svg class="w-4 h-4 text-[#00308b] shrink-0 transition-transform group-hover:translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- RELATED NEWS & ANNOUNCEMENTS (Sidebar) -->
	<?php
	$related_news_query = new WP_Query( [
		'post_type'      => [ 'post', 'guide' ],
		'posts_per_page' => 6,
		'post_status'    => 'publish',
		'meta_query'     => [
			[
				'key'     => 'related_schools',
				'value'   => '"' . $school_id . '"',
				'compare' => 'LIKE',
			],
		],
	] );

	if ( $related_news_query->have_posts() ) :
		$has_more = ( $related_news_query->post_count > 5 );
	?>
		<section class="bg-white rounded-2xl lg:rounded-lg shadow-sm border border-slate-100 lg:border-slate-200 p-4.5 lg:p-5">
			<h3 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-3">Tin tức & Thông báo mới</h3>
			<div class="space-y-3.5">
				<?php
				$news_counter = 0;
				while ( $related_news_query->have_posts() ) :
					$related_news_query->the_post();
					if ( $news_counter >= 5 ) {
						continue;
					}
					$news_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( get_the_ID(), 'thumbnail' ) : ( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ?: ltdh_get_fallback_image( 'post' ) );
					$news_counter++;
				?>
					<div class="flex gap-3 items-start pb-3 border-b border-slate-100 last:border-b-0 last:pb-0">
						<a href="<?php the_permalink(); ?>" class="shrink-0">
							<img src="<?php echo esc_url( $news_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-12 h-12 object-cover rounded-xl lg:rounded border border-slate-100" loading="lazy">
						</a>
						
						<div class="flex-1 min-w-0">
							<h4 class="font-bold text-slate-800 text-sm hover:text-brand-primary transition-colors line-clamp-2 leading-snug">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h4>
							<p class="text-xs text-slate-400 mt-0.5">📅 <?php echo get_the_date(); ?></p>
						</div>
					</div>
				<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php if ( $has_more ) : ?>
				<div class="mt-4 pt-3 border-t border-slate-100">
					<a href="<?php echo esc_url( home_url( '/tin-tuc/?truong=' . $school_id ) ); ?>" class="w-full text-center bg-slate-50 border border-slate-200 text-slate-700 py-2.5 rounded-xl lg:rounded-lg font-bold text-sm hover:bg-slate-100 transition-all flex items-center justify-center gap-1.5 min-h-[38px]">
						<span>Xem thêm tin tức</span>
						<span>→</span>
					</a>
				</div>
			<?php endif; ?>
		</section>
	<?php
	endif;
	?>

	<!-- DESKTOP ONLY: Full Zalo Community Card -->
	<div class="hidden lg:block bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-xl p-5 shadow-md relative overflow-hidden border border-blue-500/30">
		<div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

		<div class="flex items-center gap-3 mb-3 relative z-10">
			<div class="w-10 h-10 rounded-xl bg-white text-blue-600 flex items-center justify-center font-black text-xl shadow-xs shrink-0">
				💬
			</div>
			<div>
				<span class="text-[11px] uppercase font-bold tracking-wider text-blue-200 block">Cộng đồng sinh viên</span>
				<h3 class="font-extrabold text-base text-white leading-tight">Nhóm Zalo Trao Đổi</h3>
			</div>
		</div>

		<p class="text-xs text-blue-100 leading-relaxed mb-4 relative z-10">
			Tham gia nhóm Zalo trao đổi thông tin tuyển sinh, lịch học và chia sẻ kinh nghiệm cùng cựu sinh viên <?php echo esc_html( $school_title ); ?>.
		</p>

		<a href="<?php echo esc_url( $zalo_group ); ?>"
		   target="_blank"
		   rel="noopener noreferrer"
		   class="w-full bg-white hover:bg-blue-50 text-blue-700 font-extrabold text-sm py-3 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 min-h-[44px] relative z-10 hover:scale-[1.02]">
			<span>💬 Tham gia Nhóm Zalo</span>
			<span class="text-xs">→</span>
		</a>
	</div>

	<!-- DESKTOP ONLY: Full Hotline Contact Card -->
	<div class="hidden lg:block">
		<?php
		get_template_part( 'template-parts/sidebar-hotline', null, [
			'hotline'  => $hotline,
			'zalo_url' => $global_zalo,
			'title'    => 'Văn phòng tuyển sinh',
		] );
		?>
	</div>

	<!-- MOBILE ONLY: Compact Bento Contact & Community Tiles -->
	<div class="block lg:hidden">
		<?php
		get_template_part( 'template-parts/sidebar-bento-contact', null, [
			'hotline'       => $hotline,
			'zalo_url'      => $zalo_group,
			'zalo_title'    => 'Nhóm SV ' . ( $school_title ?: 'Trường' ),
			'hotline_title' => 'Tuyển sinh 24/7',
		] );
		?>
	</div>
<?php
}

/**
 * Render default major sidebar when no widgets are active in 'sidebar-major'.
 *
 * @param int $major_id
 * @param int $school_id
 */
function ltdh_render_default_major_sidebar( $major_id, $school_id = 0 ) {
	$global_zalo = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
	$hotline     = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496';
	$major_title = get_the_title( $major_id );

	if ( ! $school_id ) {
		$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );
		if ( ! empty( $school_param ) ) {
			if ( is_numeric( $school_param ) ) {
				$s_post = get_post( intval( $school_param ) );
			} else {
				$cpt_school = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
				$s_post     = get_page_by_path( sanitize_title( $school_param ), OBJECT, $cpt_school );
			}
			if ( $s_post && 'publish' === $s_post->post_status && ( defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' ) === $s_post->post_type ) {
				$school_id = $s_post->ID;
			}
		}
	}

	$context_school  = $school_id && ( defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' ) === get_post_type( $school_id );
	$c_school_title  = $context_school ? get_the_title( $school_id ) : '';
	$program_hotline = ( $context_school ? ( get_post_meta( $school_id, 'hotline', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'hotline', $school_id ) : '' ) ) : '' ) ?: $hotline;
	?>

	<!-- SCHOOL INFO CARD (When navigating from school) -->
	<?php if ( $context_school ) : ?>
		<?php ltdh_render_school_info_card( $school_id ); ?>
	<?php endif; ?>

	<!-- Consultation Form -->
	<section id="register" class="bg-white rounded-2xl lg:rounded-lg shadow-sm border border-slate-100 lg:border-slate-200 p-4.5 lg:p-6 relative overflow-hidden">
		<div class="inline-flex lg:hidden items-center gap-1.5 px-2.5 py-1 rounded-full bg-brand-primary/5 text-brand-primary text-[11px] font-bold tracking-wide uppercase mb-3">
			<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
			<span>Tư vấn tuyển sinh</span>
		</div>
		<h3 id="ltdh-sidebar-form-title" class="text-base lg:text-lg font-bold text-slate-900 mb-1.5 lg:mb-2 leading-snug">
			<?php if ( $context_school ) : ?>
				Đăng ký tư vấn ngành <?php echo esc_html( $major_title ); ?> — <?php echo esc_html( $c_school_title ); ?>
			<?php else : ?>
				Đăng ký tư vấn ngành <?php echo esc_html( $major_title ); ?>
			<?php endif; ?>
		</h3>
		<p id="ltdh-sidebar-form-desc" class="text-xs lg:text-sm text-slate-500 mb-4 leading-relaxed">
			<?php if ( $context_school ) : ?>
				Để lại thông tin, ban tuyển sinh sẽ gửi thông tin chi tiết về ngành <?php echo esc_html( $major_title ); ?> tại <strong><?php echo esc_html( $c_school_title ); ?></strong> và lộ trình học phù hợp nhất với bạn.
			<?php else : ?>
				Để lại thông tin, ban tuyển sinh sẽ gửi danh sách trường đào tạo phù hợp nhất với học lực và thời gian của bạn.
			<?php endif; ?>
		</p>
		
		<?php 
		if ( function_exists( 'ltdh_render_consultation_form' ) ) {
			$consultation_hidden = [
				'current_major_id' => $major_id,
				'referral_source'  => get_permalink(),
			];
			if ( $context_school ) {
				$consultation_hidden['current_school_id'] = $school_id;
			}
			ltdh_render_consultation_form( $consultation_hidden );
		}
		?>
	</section>

	<!-- RELATED NEWS & ANNOUNCEMENTS (Sidebar) -->
	<?php
	$related_news_query = new WP_Query( [
		'post_type'      => [ 'post', 'guide' ],
		'posts_per_page' => 6,
		'post_status'    => 'publish',
		'meta_query'     => [
			[
				'key'     => 'related_majors',
				'value'   => '"' . $major_id . '"',
				'compare' => 'LIKE',
			],
		],
	] );

	if ( $related_news_query->have_posts() ) :
		$has_more = ( $related_news_query->post_count > 5 );
	?>
		<section class="bg-white rounded-2xl lg:rounded-lg shadow-sm border border-slate-100 lg:border-slate-200 p-4.5 lg:p-5">
			<h3 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-3">Tin tức & Hướng dẫn</h3>
			<div class="space-y-3.5">
				<?php
				$news_counter = 0;
				while ( $related_news_query->have_posts() ) :
					$related_news_query->the_post();
					if ( $news_counter >= 5 ) {
						continue;
					}
					$news_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( get_the_ID(), 'thumbnail' ) : ( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ?: ltdh_get_fallback_image( 'post' ) );
					$news_counter++;
				?>
					<div class="flex gap-3 items-start pb-3 border-b border-slate-100 last:border-b-0 last:pb-0">
						<a href="<?php the_permalink(); ?>" class="shrink-0">
							<img src="<?php echo esc_url( $news_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-12 h-12 object-cover rounded-xl lg:rounded border border-slate-100" loading="lazy">
						</a>
						
						<div class="flex-1 min-w-0">
							<h4 class="font-bold text-slate-800 text-sm hover:text-brand-primary transition-colors line-clamp-2 leading-snug">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h4>
							<p class="text-xs text-slate-400 mt-0.5">📅 <?php echo get_the_date(); ?></p>
						</div>
					</div>
				<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php if ( $has_more ) : ?>
				<div class="mt-4 pt-3 border-t border-slate-100">
					<a href="<?php echo esc_url( home_url( '/tin-tuc/?nganh=' . $major_id ) ); ?>" class="w-full text-center bg-slate-50 border border-slate-200 text-slate-700 py-2.5 rounded-xl lg:rounded-lg font-bold text-sm hover:bg-slate-100 transition-all flex items-center justify-center gap-1.5 min-h-[38px]">
						<span>Xem thêm tin tức</span>
						<span>→</span>
					</a>
				</div>
			<?php endif; ?>
		</section>
	<?php
	endif;
	?>

	<!-- DESKTOP ONLY: Full Zalo Community Card -->
	<div class="hidden lg:block bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-xl p-5 shadow-md relative overflow-hidden border border-blue-500/30">
		<div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

		<div class="flex items-center gap-3 mb-3 relative z-10">
			<div class="w-10 h-10 rounded-xl bg-white text-blue-600 flex items-center justify-center font-black text-xl shadow-xs shrink-0">
				💬
			</div>
			<div>
				<span class="text-[11px] uppercase font-bold tracking-wider text-blue-200 block">Cộng đồng sinh viên</span>
				<h3 class="font-extrabold text-base text-white leading-tight">Nhóm Zalo Trao Đổi</h3>
			</div>
		</div>

		<p class="text-xs text-blue-100 leading-relaxed mb-4 relative z-10">
			Tham gia nhóm Zalo trao đổi thông tin tuyển sinh, định hướng nghề nghiệp ngành <?php echo esc_html( $major_title ); ?>.
		</p>

		<a href="<?php echo esc_url( $global_zalo ); ?>"
		   target="_blank"
		   rel="noopener noreferrer"
		   class="w-full bg-white hover:bg-blue-50 text-blue-700 font-extrabold text-sm py-3 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 min-h-[44px] relative z-10 hover:scale-[1.02]">
			<span>💬 Tham gia Nhóm Zalo</span>
			<span class="text-xs">→</span>
		</a>
	</div>

	<!-- DESKTOP ONLY: Full Hotline Contact Card -->
	<div class="hidden lg:block">
		<?php
		get_template_part( 'template-parts/sidebar-hotline', null, [
			'hotline'  => $program_hotline,
			'zalo_url' => $global_zalo,
			'title'    => 'Văn phòng tuyển sinh',
		] );
		?>
	</div>

	<!-- MOBILE ONLY: Compact Bento Contact & Community Tiles -->
	<div class="block lg:hidden">
		<?php
		get_template_part( 'template-parts/sidebar-bento-contact', null, [
			'hotline'       => $program_hotline,
			'zalo_url'      => $global_zalo,
			'zalo_title'    => 'Nhóm Zalo ' . ( $major_title ?: 'Học viên' ),
			'hotline_title' => 'Tuyển sinh 24/7',
		] );
		?>
	</div>
<?php
}


