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
		$valid_limits = [ 10, 12, 20, 24, 30, 36, 48, 50, 100, -1 ];
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
		$type_terms = ( 'tu-xa' === $type_slug || 'dao-tao-tu-xa' === $type_slug ) ? [ 'dao-tao-tu-xa', 'tu-xa' ] : $type_slug;
		$args['tax_query'] = [
			[
				'taxonomy' => LTDH_TAX_TRAINING_TYPE,
				'field'    => 'slug',
				'terms'    => $type_terms,
			],
		];
	} else {
		$args['tax_query'] = [
			[
				'taxonomy' => LTDH_TAX_TRAINING_TYPE,
				'field'    => 'slug',
				'terms'    => [ 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ],
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
			if ( is_array( $school_rel_id ) ) {
				$school_rel_id = ! empty( $school_rel_id ) ? ( is_object( $school_rel_id[0] ) ? $school_rel_id[0]->ID : $school_rel_id[0] ) : 0;
			} elseif ( is_object( $school_rel_id ) ) {
				$school_rel_id = $school_rel_id->ID;
			}
			$school_rel_id = intval( $school_rel_id );

			$school_name   = $school_rel_id ? get_the_title( $school_rel_id ) : 'Đại học đối tác';
			$major_rel_id  = get_field( 'major_relationship', $prog_id );
			if ( is_array( $major_rel_id ) ) {
				$major_rel_id = ! empty( $major_rel_id ) ? ( is_object( $major_rel_id[0] ) ? $major_rel_id[0]->ID : $major_rel_id[0] ) : 0;
			} elseif ( is_object( $major_rel_id ) ) {
				$major_rel_id = $major_rel_id->ID;
			}
			$major_rel_id = intval( $major_rel_id );

			$major_name = $major_rel_id ? get_the_title( $major_rel_id ) : '';
			if ( empty( $major_name ) ) {
				$raw_prog_title = get_the_title( $prog_id );
				$major_name = preg_replace( '/^(Cử nhân|Kỹ sư|Đại học)\s+/iu', '', $raw_prog_title );
				$major_name = preg_replace( '/\s*\([^)]*\)$/u', '', $major_name );
			}
			$clean_major_name = preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) );

			$thumb = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium' ) : '';
			if ( ! $thumb ) {
				$thumb = function_exists( 'ltdh_get_fallback_image' ) ? ltdh_get_fallback_image( 'program' ) : get_template_directory_uri() . '/assets/images/banner-program.jpg';
			}

			$prog_types       = wp_get_post_terms( $prog_id, 'training_type' );
			$t_name           = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->name : '';
			$t_slug           = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->slug : '';
			$clean_type_name  = preg_replace( '/^hệ\s+/iu', '', trim( $t_name ) );
			$card_headline    = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );
			$admission_status = get_post_meta( $prog_id, 'admission_status', true ) ?: 'tuyen-sinh';

			$badge_class = 'bg-orange-50 text-orange-600 border border-orange-200';
			if ( $clean_type_name ) {
				$t_lower = mb_strtolower( trim( $clean_type_name ), 'UTF-8' );
				if ( false !== strpos( $t_lower, 'chính quy' ) ) {
					$badge_class = 'bg-blue-50 text-blue-700 border border-blue-200';
				} elseif ( false !== strpos( $t_lower, 'từ xa' ) ) {
					$badge_class = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
				} elseif ( false !== strpos( $t_lower, 'vừa học vừa làm' ) || false !== strpos( $t_lower, 'vừa làm vừa học' ) || false !== strpos( $t_lower, 'liên thông' ) || false !== strpos( $t_lower, 'văn bằng 2' ) ) {
					$badge_class = 'bg-amber-50 text-amber-700 border border-amber-200';
				}
			}

			$show_type_badge = ! empty( $clean_type_name ) && ( empty( $type_slug ) || $type_slug !== $t_slug );
			$school_thumb    = $school_rel_id && function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_rel_id, 'medium_large' ) : get_template_directory_uri() . '/assets/images/banner-school.jpg';
			$learning_details = function_exists( 'ltdh_get_program_learning_details' ) ? ltdh_get_program_learning_details( $prog_id ) : [ 'mode' => 'Học online 100%' ];
			$school_logo_id   = $school_rel_id && function_exists( 'ltdh_get_school_image_id' ) ? ltdh_get_school_image_id( $school_rel_id ) : 0;
			$school_logo_url  = $school_rel_id && function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_rel_id, 'thumbnail' ) : get_template_directory_uri() . '/assets/images/cropped-logo-scaled-2.webp';
			$tuition          = get_field( 'tuition_fee', $prog_id ) ?: 'Liên hệ';
			$duration         = get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm';
			?>
			<div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between group"
				 data-compare-btn
				 data-compare-type="program"
				 data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
				 data-compare-title="<?php echo esc_attr( $card_headline ); ?>"
				 data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
				 data-compare-thumb="<?php echo esc_url( $school_logo_url ); ?>"
				 data-compare-major-name="<?php echo esc_attr( $clean_major_name ); ?>"
				 data-compare-school-name="<?php echo esc_attr( $school_name ); ?>">

				<div>
					<!-- School Cover Image with Top Badges -->
					<div class="h-28 sm:h-32 w-full bg-slate-100 bg-cover bg-center relative overflow-hidden" style="background-image: url('<?php echo esc_url( $school_thumb ); ?>');">
						<div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-slate-900/20 to-transparent"></div>
						
						<!-- Status Badge (Left) -->
						<?php if ( 'tam-ngung' === $admission_status ) : ?>
							<span class="absolute top-3 left-3 bg-red-600 text-white text-[10px] md:text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-md z-10 flex items-center gap-1.5">
								<span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
								Đã hết chỉ tiêu
							</span>
						<?php elseif ( 'sap-mo' === $admission_status ) : ?>
							<span class="absolute top-3 left-3 bg-amber-500 text-white text-[10px] md:text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-md z-10">
								Sắp mở
							</span>
						<?php endif; ?>

						<!-- Training Type Badge (Right) -->
						<?php if ( $show_type_badge ) : ?>
							<span class="absolute top-3 right-3 <?php echo esc_attr( $badge_class ); ?> text-xs font-black px-3 py-1 rounded-full uppercase tracking-wide border shadow-sm z-10">
								<?php echo esc_html( $clean_type_name ); ?>
							</span>
						<?php endif; ?>
					</div>

					<!-- Card Body -->
					<div class="p-5 pb-0">
						<!-- School Header with Logo -->
						<div class="flex items-center gap-3 mb-3.5 -mt-8 relative z-10">
							<div class="w-12 h-12 bg-white border border-slate-200/90 rounded-xl flex items-center justify-center p-1.5 shrink-0 shadow-sm group-hover:border-brand-primary/40 transition-colors">
								<?php if ( ! empty( $school_logo_url ) ) : ?>
									<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_name ); ?>" class="h-full w-full object-contain p-0.5">
								<?php elseif ( $school_logo_id ) : ?>
									<?php echo wp_get_attachment_image( $school_logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
								<?php else : ?>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-brand-primary"><path d="M11.7 2.805a.75.75 0 0 1 .6 0l9.3 4.25a.75.75 0 0 1 0 1.39l-9.3 4.25a.75.75 0 0 1-.6 0L2.4 8.445a.75.75 0 0 1 0-1.39l9.3-4.25ZM2.84 10.74l6.735 3.08a2.25 2.25 0 0 0 1.85 0l6.735-3.08v3.42c0 .532-.244 1.026-.642 1.378L12.5 19.544a1.25 1.25 0 0 1-1.6 0l-5.023-3.97a1.75 1.75 0 0 1-.642-1.378v-3.456Z" /><path d="M20.25 10.32v5.43a3.25 3.25 0 0 1-3.25 3.25h-.5a.75.75 0 0 0 0 1.5h.5a4.75 4.75 0 0 0 4.75-4.75v-5.43a.75.75 0 0 0-1.5 0Z" /></svg>
								<?php endif; ?>
							</div>
							<div class="min-w-0">
								<span class="text-xs font-bold text-slate-500 uppercase tracking-wider block truncate">
									<?php echo esc_html( $school_name ); ?>
								</span>
							</div>
						</div>

						<!-- Program Name (Primary Hero Headline) -->
						<div class="mb-4">
							<h2 class="font-black text-slate-900 text-base md:text-lg hover:text-brand-primary leading-snug line-clamp-2 min-h-[48px] transition-colors">
								<a href="<?php the_permalink(); ?>"><?php echo esc_html( $card_headline ); ?></a>
							</h2>
							<?php if ( 'tam-ngung' === $admission_status ) : ?>
								<div class="mt-2">
									<span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-50 border border-red-200/80 px-2.5 py-1 rounded-md">
										<span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span> Tạm dừng tuyển sinh năm nay
									</span>
								</div>
							<?php endif; ?>
						</div>

						<!-- Key Information List -->
						<div class="space-y-1.5 text-xs md:text-sm text-slate-600 py-3.5 border-t border-slate-100">
							<div class="flex items-center justify-between">
								<span class="text-slate-400">Học phí:</span>
								<span class="font-extrabold text-brand-primary text-sm md:text-base"><?php echo esc_html( $tuition ); ?></span>
							</div>
							<div class="flex items-center justify-between">
								<span class="text-slate-400">Thời gian:</span>
								<span class="font-bold text-slate-700"><?php echo esc_html( $duration ); ?></span>
							</div>
							<div class="flex items-center justify-between">
								<span class="text-slate-400">Hình thức:</span>
								<span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded text-xs"><?php echo esc_html( $learning_details['mode'] ); ?></span>
							</div>
						</div>
					</div>
				</div>

				<!-- Card Footer Action Buttons -->
				<div class="p-5 pt-0">
					<div class="pt-3 border-t border-slate-100 flex items-center gap-2">
						<a href="<?php the_permalink(); ?>" class="text-xs md:text-sm py-2.5 px-4 rounded-xl uppercase font-bold ltdh-btn-details min-h-[44px] flex items-center justify-center flex-1 shadow-2xs hover:shadow-md transition-all">
							Tìm hiểu
						</a>
						<button type="button"
								class="ltdh-compare-toggle text-xs md:text-sm text-slate-600 hover:text-brand-primary font-bold border border-slate-200 hover:border-brand-primary rounded-xl py-2.5 px-3 transition-all min-h-[44px] flex items-center justify-center flex-1 bg-white hover:bg-slate-50"
								data-compare-type="program"
								data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
								data-compare-title="<?php echo esc_attr( $card_headline ); ?>"
								data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
								data-compare-he="<?php echo esc_attr( $t_slug ); ?>"
								data-compare-nganh="<?php echo esc_attr( $major_rel_id ? get_post_field( 'post_name', $major_rel_id ) : '' ); ?>"
								data-compare-major-name="<?php echo esc_attr( $clean_major_name ); ?>"
								data-compare-school-name="<?php echo esc_attr( $school_name ); ?>">
							So sánh
						</button>
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

/**
 * Prioritize open/active programs over paused/tam-ngung programs in catalog queries.
 */
function ltdh_order_by_admission_status_filter( $clauses, $wp_query ) {
	if ( $wp_query->get( 'sort_by_admission_priority' ) ) {
		global $wpdb;
		$clauses['join']    .= " LEFT JOIN {$wpdb->postmeta} AS pm_adm_order ON ({$wpdb->posts}.ID = pm_adm_order.post_id AND pm_adm_order.meta_key = 'admission_status') ";
		$status_order        = " (CASE WHEN pm_adm_order.meta_value = 'tam-ngung' THEN 1 ELSE 0 END) ASC ";
		$clauses['orderby']  = $clauses['orderby'] ? $status_order . ", " . $clauses['orderby'] : $status_order;
	}
	return $clauses;
}
add_filter( 'posts_clauses', 'ltdh_order_by_admission_status_filter', 10, 2 );
