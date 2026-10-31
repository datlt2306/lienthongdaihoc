<?php
/**
 * Widget: Recent Posts / Popular News.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		$recent_posts = [];
		// When no fixed category is selected, attempt to cascade by Program -> School -> Major context
		if ( 0 === $category ) {
			$cur_prog_id = function_exists( 'ltdh_detect_current_program_id' ) ? ltdh_detect_current_program_id() : 0;
			$cur_sch_id  = function_exists( 'ltdh_detect_current_school_id' ) ? ltdh_detect_current_school_id() : 0;
			$cur_maj_id  = function_exists( 'ltdh_detect_current_major_id' ) ? ltdh_detect_current_major_id() : 0;

			$meta_conditions = [];
			if ( $cur_prog_id > 0 ) {
				$meta_conditions[] = [
					'key'     => 'related_programs',
					'value'   => '"' . $cur_prog_id . '"',
					'compare' => 'LIKE',
				];
				if ( $cur_sch_id > 0 ) {
					$meta_conditions[] = [
						'key'     => 'related_schools',
						'value'   => '"' . $cur_sch_id . '"',
						'compare' => 'LIKE',
					];
				}
			} elseif ( $cur_sch_id > 0 ) {
				$meta_conditions[] = [
					'key'     => 'related_schools',
					'value'   => '"' . $cur_sch_id . '"',
					'compare' => 'LIKE',
				];
			} elseif ( $cur_maj_id > 0 ) {
				$meta_conditions[] = [
					'key'     => 'related_majors',
					'value'   => '"' . $cur_maj_id . '"',
					'compare' => 'LIKE',
				];
			}

			if ( ! empty( $meta_conditions ) ) {
				$context_args = $query_args;
				$context_args['post_type'] = [ 'post', 'guide' ];
				if ( count( $meta_conditions ) > 1 ) {
					$context_args['meta_query'] = array_merge( [ 'relation' => 'OR' ], $meta_conditions );
				} else {
					$context_args['meta_query'] = $meta_conditions;
				}
				$recent_posts = get_posts( $context_args );
			}
		}

		if ( empty( $recent_posts ) ) {
			$recent_posts = get_posts( $query_args );
		}

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
