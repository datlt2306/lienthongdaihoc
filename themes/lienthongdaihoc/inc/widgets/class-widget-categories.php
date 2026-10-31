<?php
/**
 * Widget: News & Guide Categories.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
