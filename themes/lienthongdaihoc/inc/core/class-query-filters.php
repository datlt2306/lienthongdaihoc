<?php
/**
 * Query Filters — Customize main queries for archives and search.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customize archive queries for school, major, and program post types.
 */
function ltdh_customize_archive_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_tax( LTDH_TAX_TRAINING_TYPE ) ) {
		$query->set( 'post_type', LTDH_CPT_PROGRAM );
	}

	if ( $query->is_post_type_archive( LTDH_CPT_SCHOOL ) || $query->is_post_type_archive( LTDH_CPT_MAJOR ) ) {
		$limit        = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 12;
		$valid_limits = [ 10, 12, 20, 30, 50 ];
		if ( in_array( $limit, $valid_limits, true ) ) {
			$query->set( 'posts_per_page', $limit );
		} else {
			$query->set( 'posts_per_page', 12 );
		}
	}

	if ( $query->is_post_type_archive( LTDH_CPT_MAJOR ) ) {
		if ( ! empty( $_GET['nhom_nganh'] ) ) {
			$query->set( 'tax_query', [
				[
					'taxonomy' => LTDH_TAX_MAJOR_CAT,
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $_GET['nhom_nganh'] ),
				],
			] );
		}

		$sort = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : '';
		if ( $sort === 'title_asc' ) {
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
		} elseif ( $sort === 'title_desc' ) {
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'DESC' );
		} elseif ( $sort === 'date_desc' ) {
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
		}
	}
}
add_action( 'pre_get_posts', 'ltdh_customize_archive_queries' );

/**
 * AJAX Handler for filtering programs without page reload.
 */
add_action( 'wp_ajax_ltdh_filter_programs', 'ltdh_ajax_filter_programs' );
add_action( 'wp_ajax_nopriv_ltdh_filter_programs', 'ltdh_ajax_filter_programs' );

function ltdh_ajax_filter_programs() {
	$paged = isset( $_POST['paged'] ) ? max( 1, intval( $_POST['paged'] ) ) : 1;
	$limit = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 12;
	if ( ! in_array( $limit, [ 10, 12, 20, 24, 30, 36, 48, 50, 100, -1 ], true ) ) {
		$limit = 12;
	}

	$args = [
		'post_type'      => LTDH_CPT_PROGRAM,
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'paged'          => $paged,
	];

	// Meta query
	$meta_query = [ 'relation' => 'AND' ];

	// Filter by school
	if ( ! empty( $_POST['truong'] ) ) {
		$school_raw = sanitize_text_field( wp_unslash( $_POST['truong'] ) );
		$school_id  = is_numeric( $school_raw ) ? intval( $school_raw ) : 0;
		if ( ! $school_id ) {
			$school_post = get_page_by_path( $school_raw, OBJECT, LTDH_CPT_SCHOOL );
			$school_id   = $school_post ? $school_post->ID : 0;
		}
		if ( $school_id ) {
			$meta_query[] = [
				'key'     => 'school_relationship',
				'value'   => $school_id,
				'compare' => '=',
			];
		}
	}

	// Filter by major / major category
	$nhom_slug = ! empty( $_POST['nhom_nganh'] ) ? sanitize_text_field( wp_unslash( $_POST['nhom_nganh'] ) ) : ( ! empty( $_POST['nganh'] ) ? sanitize_text_field( wp_unslash( $_POST['nganh'] ) ) : '' );
	if ( ! empty( $nhom_slug ) ) {
		$major_post = get_page_by_path( $nhom_slug, OBJECT, LTDH_CPT_MAJOR );
		if ( $major_post ) {
			$meta_query[] = [
				'key'     => 'major_relationship',
				'value'   => $major_post->ID,
				'compare' => '=',
			];
		} else {
			$majors_in_cat = get_posts( [
				'post_type'   => LTDH_CPT_MAJOR,
				'numberposts' => -1,
				'fields'      => 'ids',
				'tax_query'   => [
					[
						'taxonomy' => LTDH_TAX_MAJOR_CAT,
						'field'    => 'slug',
						'terms'    => $nhom_slug,
					],
				],
			] );
			if ( ! empty( $majors_in_cat ) ) {
				$meta_query[] = [
					'key'     => 'major_relationship',
					'value'   => $majors_in_cat,
					'compare' => 'IN',
				];
			}
		}
	}

	if ( count( $meta_query ) > 1 ) {
		$args['meta_query'] = $meta_query;
	}

	// Tax query (training_type)
	$type_slug = ! empty( $_POST['he'] ) ? sanitize_text_field( wp_unslash( $_POST['he'] ) ) : ( ! empty( $_POST['training_type'] ) ? sanitize_text_field( wp_unslash( $_POST['training_type'] ) ) : '' );
	if ( ! empty( $type_slug ) ) {
		$args['tax_query'] = [
			[
				'taxonomy' => LTDH_TAX_TRAINING_TYPE,
				'field'    => 'slug',
				'terms'    => $type_slug,
			],
		];
	}

	// Search
	if ( ! empty( $_POST['s'] ) ) {
		$args['s'] = sanitize_text_field( wp_unslash( $_POST['s'] ) );
	}

	// Sort
	$sort = ! empty( $_POST['sort'] ) ? sanitize_text_field( wp_unslash( $_POST['sort'] ) ) : '';
	if ( $sort === 'title_asc' ) {
		$args['orderby'] = 'title';
		$args['order']   = 'ASC';
	} elseif ( $sort === 'title_desc' ) {
		$args['orderby'] = 'title';
		$args['order']   = 'DESC';
	} elseif ( $sort === 'date_desc' ) {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	}

	$query = new WP_Query( $args );

	ob_start();
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) : $query->the_post();
			$prog_id       = get_the_ID();
			$school_rel_id = get_field( 'school_relationship', $prog_id );
			$school_name   = $school_rel_id ? get_the_title( $school_rel_id ) : 'Đại học đối tác';
			$major_rel_id  = get_field( 'major_relationship', $prog_id );
			if ( is_array( $major_rel_id ) ) {
				$major_rel_id = ! empty( $major_rel_id ) ? ( is_object( $major_rel_id[0] ) ? $major_rel_id[0]->ID : $major_rel_id[0] ) : 0;
			} elseif ( is_object( $major_rel_id ) ) {
				$major_rel_id = $major_rel_id->ID;
			}
			$major_rel_id = intval( $major_rel_id );

			$thumb = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium' ) : '';
			if ( ! $thumb ) {
				$thumb = 'https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=300';
			}

			$prog_types = wp_get_post_terms( $prog_id, 'training_type' );
			$t_name     = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->name : '';
			$t_slug     = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->slug : '';

			$badge_class = 'bg-orange-50 text-orange-600 border border-orange-100';
			if ( $t_name ) {
				$t_lower = mb_strtolower( trim( $t_name ), 'UTF-8' );
				if ( false !== strpos( $t_lower, 'chính quy' ) ) {
					$badge_class = 'bg-blue-50 text-blue-600 border border-blue-100';
				} elseif ( false !== strpos( $t_lower, 'từ xa' ) ) {
					$badge_class = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
				} elseif ( false !== strpos( $t_lower, 'vừa học vừa làm' ) || false !== strpos( $t_lower, 'liên thông' ) || false !== strpos( $t_lower, 'văn bằng 2' ) ) {
					$badge_class = 'bg-amber-50 text-amber-600 border border-amber-100';
				}
			}

			$school_thumb = $school_rel_id ? get_the_post_thumbnail_url( $school_rel_id, 'medium' ) : '';
			if ( ! $school_thumb ) {
				$school_thumb = 'https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=300';
			}
			$learning_details = function_exists( 'ltdh_get_program_learning_details' ) ? ltdh_get_program_learning_details( $prog_id ) : [ 'mode' => 'Học Online' ];
			?>
			<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
				 data-compare-btn
				 data-compare-type="program"
				 data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
				 data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
				 data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
				 data-compare-thumb="<?php echo esc_url( $thumb ); ?>">

				<div>
					<div class="h-24 w-full bg-slate-200 bg-cover bg-center relative" style="background-image: url('<?php echo esc_url( $school_thumb ); ?>');">
						<div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent"></div>
						<?php if ( $t_name ) : ?>
							<span class="absolute top-2.5 right-2.5 <?php echo esc_attr( $badge_class ); ?> text-xs font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wide border shadow-sm z-10">
								Hệ <?php echo esc_html( $t_name ); ?>
							</span>
						<?php endif; ?>
					</div>

					<div class="p-3 md:p-4 pb-0">
						<div class="flex items-center justify-between gap-2 mb-3 -mt-8 md:-mt-10 relative z-10">
							<div class="w-10 h-10 md:w-12 md:h-12 bg-white border border-slate-100 rounded-lg flex items-center justify-center p-1 shrink-0 shadow-xs">
								<?php 
								$school_logo_id = $school_rel_id && function_exists( 'ltdh_get_school_image_id' ) ? ltdh_get_school_image_id( $school_rel_id ) : 0;
								if ( $school_logo_id ) : 
								?>
									<?php echo wp_get_attachment_image( $school_logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
								<?php else : ?>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-brand-primary/80"><path d="M11.7 2.805a.75.75 0 0 1 .6 0l9.3 4.25a.75.75 0 0 1 0 1.39l-9.3 4.25a.75.75 0 0 1-.6 0L2.4 8.445a.75.75 0 0 1 0-1.39l9.3-4.25ZM2.84 10.74l6.735 3.08a2.25 2.25 0 0 0 1.85 0l6.735-3.08v3.42c0 .532-.244 1.026-.642 1.378L12.5 19.544a1.25 1.25 0 0 1-1.6 0l-5.023-3.97a1.75 1.75 0 0 1-.642-1.378v-3.456Z" /><path d="M20.25 10.32v5.43a3.25 3.25 0 0 1-3.25 3.25h-.5a.75.75 0 0 0 0 1.5h.5a4.75 4.75 0 0 0 4.75-4.75v-5.43a.75.75 0 0 0-1.5 0Z" /></svg>
								<?php endif; ?>
							</div>
						</div>

						<div>
							<h3 class="font-extrabold text-slate-950 text-sm md:text-base hover:text-brand-primary mb-1 leading-snug line-clamp-2 min-h-[40px]">
								<a href="<?php the_permalink(); ?>"><?php echo esc_html( $school_name ); ?></a>
							</h3>
							<span class="text-xs text-slate-500 font-semibold bg-slate-50 px-1.5 py-0.5 rounded inline-block mb-3 border border-slate-200/50"><?php the_title(); ?></span>
						</div>

						<div class="space-y-0.5 md:space-y-1 text-sm text-slate-500 py-2 md:py-3 border-t border-slate-100">
							<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( get_field( 'tuition_fee', $prog_id ) ?: 'Liên hệ' ); ?></span></p>
							<p class="hidden sm:block">Thời gian: <span class="font-bold text-slate-700"><?php echo esc_html( get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm' ); ?></span></p>
							<p>Hình thức: <span class="font-bold text-slate-700 text-xs md:text-sm"><?php echo esc_html( $learning_details['mode'] ); ?></span></p>
						</div>
					</div>
				</div>

				<div class="p-3 md:p-4 pt-0">
					<div class="pt-2 md:pt-3 border-t border-slate-100 flex items-center justify-between">
						<div class="flex items-center gap-1.5 w-full">
							<a href="<?php the_permalink(); ?>" class="text-sm py-2.5 rounded-lg uppercase ltdh-btn-details min-h-[44px] flex items-center justify-center flex-1">Tìm hiểu</a>
							<button type="button"
									class="ltdh-compare-toggle text-sm text-slate-400 hover:text-brand-primary font-semibold border border-slate-200 hover:border-brand-primary rounded-lg py-2.5 transition-all min-h-[44px] flex items-center justify-center flex-1"
									data-compare-type="program"
									data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
									data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
									data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
									data-compare-he="<?php echo esc_attr( $t_slug ); ?>"
									data-compare-nganh="<?php echo esc_attr( $major_rel_id ? get_post_field( 'post_name', $major_rel_id ) : '' ); ?>">
								So sánh
							</button>
						</div>
					</div>
				</div>
			</div>
			<?php
		endwhile;
		wp_reset_postdata();
	else :
		?>
		<div class="col-span-2 md:col-span-3 text-center py-12 bg-white border border-slate-200 rounded-xl p-8 shadow-xs w-full">
			<span class="text-5xl block mb-4">🔍</span>
			<h3 class="font-extrabold text-slate-800 text-lg mb-2">Không tìm thấy chương trình phù hợp</h3>
			<p class="text-slate-500 text-sm max-w-md mx-auto mb-6">Hãy thử thay đổi điều kiện lọc hoặc đặt lại bộ lọc để tìm kiếm các chương trình khác.</p>
		</div>
		<?php
	endif;
	$html = ob_get_clean();

	wp_send_json_success( [
		'html'        => $html,
		'found_posts' => $query->found_posts,
	] );
}
