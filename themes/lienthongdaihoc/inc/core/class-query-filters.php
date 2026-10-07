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
 * Helper to get program IDs matching a location or region slug.
 *
 * @param string $location_slug Slug of campus or region (e.g. mien-bac, mien-trung, mien-nam, ha-noi, ho-chi-minh, da-nang, thai-nguyen, online)
 * @return array Array of program IDs or [0] if no programs match.
 */
function ltdh_get_program_ids_by_location( $location_slug ) {
	if ( empty( $location_slug ) ) {
		return [];
	}

	$region_map = [
		'mien-bac'   => [ 'campuses' => [ 'ha-noi', 'thai-nguyen' ], 'regions' => [ 'mien-bac' ] ],
		'mien-trung' => [ 'campuses' => [ 'da-nang' ], 'regions' => [ 'mien-trung' ] ],
		'mien-nam'   => [ 'campuses' => [ 'ho-chi-minh' ], 'regions' => [ 'mien-nam' ] ],
		'ha-noi'     => [ 'campuses' => [ 'ha-noi' ], 'regions' => [] ],
		'ho-chi-minh'=> [ 'campuses' => [ 'ho-chi-minh' ], 'regions' => [] ],
		'da-nang'    => [ 'campuses' => [ 'da-nang' ], 'regions' => [] ],
		'thai-nguyen'=> [ 'campuses' => [ 'thai-nguyen' ], 'regions' => [] ],
		'online'     => [ 'campuses' => [ 'online' ], 'regions' => [] ],
	];

	$target_campuses = isset( $region_map[ $location_slug ] ) ? $region_map[ $location_slug ]['campuses'] : [ $location_slug ];
	$target_regions  = isset( $region_map[ $location_slug ] ) ? $region_map[ $location_slug ]['regions'] : [ $location_slug ];

	// 1. Find school IDs matching region taxonomy
	$school_ids = [];
	if ( ! empty( $target_regions ) ) {
		$school_ids = get_posts( [
			'post_type'   => 'school',
			'numberposts' => -1,
			'fields'      => 'ids',
			'tax_query'   => [
				[
					'taxonomy' => 'region',
					'field'    => 'slug',
					'terms'    => $target_regions,
				],
			],
		] );
	}

	// 2. Build tax query for programs
	$tax_or_query = [ 'relation' => 'OR' ];
	if ( ! empty( $target_campuses ) ) {
		$tax_or_query[] = [
			'taxonomy' => 'campus',
			'field'    => 'slug',
			'terms'    => $target_campuses,
		];
	}
	if ( ! empty( $target_regions ) ) {
		$tax_or_query[] = [
			'taxonomy' => 'region',
			'field'    => 'slug',
			'terms'    => $target_regions,
		];
	}

	// 3. Build meta query for school relationship or elig_campuses
	$meta_or_query = [ 'relation' => 'OR' ];
	if ( ! empty( $school_ids ) ) {
		$meta_or_query[] = [
			'key'     => 'school_relationship',
			'value'   => $school_ids,
			'compare' => 'IN',
		];
	}

	foreach ( $target_campuses as $c_slug ) {
		$meta_or_query[] = [
			'key'     => 'elig_campuses',
			'value'   => '"' . $c_slug . '"',
			'compare' => 'LIKE',
		];
	}

	$prog_args = [
		'post_type'   => 'program',
		'numberposts' => -1,
		'fields'      => 'ids',
		'post_status' => 'publish',
		'tax_query'   => $tax_or_query,
	];

	if ( count( $meta_or_query ) > 1 ) {
		$prog_args['meta_query'] = $meta_or_query;
	}

	$matched_ids = get_posts( $prog_args );

	return ! empty( $matched_ids ) ? array_values( array_unique( array_map( 'intval', $matched_ids ) ) ) : [ 0 ];
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

	// Filter by region / campus (Khu vực)
	$region_slug = ! empty( $_POST['khu_vuc'] ) ? sanitize_text_field( wp_unslash( $_POST['khu_vuc'] ) ) : ( ! empty( $_POST['khu-vuc'] ) ? sanitize_text_field( wp_unslash( $_POST['khu-vuc'] ) ) : '' );
	if ( ! empty( $region_slug ) ) {
		$loc_program_ids = ltdh_get_program_ids_by_location( $region_slug );
		$args['post__in'] = $loc_program_ids;
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
						<div class="flex items-end gap-3 mb-3.5 relative z-10">
							<div class="w-12 h-12 bg-white border border-slate-200/90 rounded-xl flex items-center justify-center p-1.5 shrink-0 shadow-sm group-hover:border-brand-primary/40 group-hover:shadow-md transition-all -mt-7">
								<?php if ( ! empty( $school_logo_url ) ) : ?>
									<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_name ); ?>" class="h-full w-full object-contain p-0.5">
								<?php elseif ( $school_logo_id ) : ?>
									<?php echo wp_get_attachment_image( $school_logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
								<?php else : ?>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-brand-primary"><path d="M11.7 2.805a.75.75 0 0 1 .6 0l9.3 4.25a.75.75 0 0 1 0 1.39l-9.3 4.25a.75.75 0 0 1-.6 0L2.4 8.445a.75.75 0 0 1 0-1.39l9.3-4.25ZM2.84 10.74l6.735 3.08a2.25 2.25 0 0 0 1.85 0l6.735-3.08v3.42c0 .532-.244 1.026-.642 1.378L12.5 19.544a1.25 1.25 0 0 1-1.6 0l-5.023-3.97a1.75 1.75 0 0 1-.642-1.378v-3.456Z" /><path d="M20.25 10.32v5.43a3.25 3.25 0 0 1 3.25 3.25h-.5a.75.75 0 0 0 0 1.5h.5a4.75 4.75 0 0 0 4.75-4.75v-5.43a.75.75 0 0 0-1.5 0Z" /></svg>
								<?php endif; ?>
							</div>
							<div class="min-w-0 flex-1 pb-0.5">
								<?php if ( ! empty( $school_rel_id ) ) : ?>
									<a href="<?php echo esc_url( get_permalink( $school_rel_id ) ); ?>" class="text-xs sm:text-sm font-extrabold text-slate-800 hover:text-brand-primary transition-colors uppercase tracking-wide block truncate" title="<?php echo esc_attr( $school_name ); ?>">
										<?php echo esc_html( $school_name ); ?>
									</a>
								<?php else : ?>
									<span class="text-xs sm:text-sm font-extrabold text-slate-800 group-hover:text-brand-primary transition-colors uppercase tracking-wide block truncate">
										<?php echo esc_html( $school_name ); ?>
									</span>
								<?php endif; ?>
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

/**
 * Render major programs list grouped by school.
 */
function ltdh_render_major_programs_list( $major_id, $selected_he = '', $selected_school = '' ) {
	$school_filter_id = 0;
	if ( ! empty( $selected_school ) ) {
		if ( is_numeric( $selected_school ) ) {
			$school_filter_id = intval( $selected_school );
		} else {
			$s_post = get_page_by_path( sanitize_title( $selected_school ), OBJECT, LTDH_CPT_SCHOOL );
			if ( $s_post ) {
				$school_filter_id = $s_post->ID;
			}
		}
	}

	$meta_status_filter = [
		'relation' => 'OR',
		[
			'key'     => LTDH_META_ADMISSION_STATUS,
			'value'   => 'tam-ngung',
			'compare' => '!=',
		],
		[
			'key'     => LTDH_META_ADMISSION_STATUS,
			'compare' => 'NOT EXISTS',
		],
	];

	$tax_query = [];
	if ( ! empty( $selected_he ) ) {
		$terms = ( 'tu-xa' === $selected_he || 'dao-tao-tu-xa' === $selected_he ) ? [ 'dao-tao-tu-xa', 'tu-xa' ] : [ $selected_he ];
		$tax_query[] = [
			'taxonomy' => LTDH_TAX_TRAINING_TYPE,
			'field'    => 'slug',
			'terms'    => $terms,
			'operator' => 'IN',
		];
	}

	$meta_query = [
		'relation' => 'AND',
		[
			'key'     => LTDH_META_MAJOR_REL,
			'value'   => $major_id,
			'compare' => '=',
		],
		$meta_status_filter,
	];

	if ( $school_filter_id ) {
		$meta_query[] = [
			'key'     => LTDH_META_SCHOOL_REL,
			'value'   => $school_filter_id,
			'compare' => '=',
		];
	}

	$query_args = [
		'post_type'      => LTDH_CPT_PROGRAM,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'meta_query'     => $meta_query,
	];

	if ( ! empty( $tax_query ) ) {
		$query_args['tax_query'] = $tax_query;
	}

	$programs_query = new WP_Query( $query_args );

	$schools_data = [];
	if ( $programs_query->have_posts() ) {
		while ( $programs_query->have_posts() ) {
			$programs_query->the_post();
			$prog_id       = get_the_ID();
			$school_rel_id = get_field( LTDH_META_SCHOOL_REL, $prog_id );
			if ( is_array( $school_rel_id ) ) {
				$school_rel_id = ! empty( $school_rel_id ) ? ( is_object( $school_rel_id[0] ) ? $school_rel_id[0]->ID : $school_rel_id[0] ) : 0;
			} elseif ( is_object( $school_rel_id ) ) {
				$school_rel_id = $school_rel_id->ID;
			}
			$school_rel_id = intval( $school_rel_id );
			$school_key    = $school_rel_id ? $school_rel_id : 'no_school_' . $prog_id;

			if ( ! isset( $schools_data[ $school_key ] ) ) {
				$school_name    = $school_rel_id ? get_the_title( $school_rel_id ) : 'Mời tư vấn';
				$school_thumb   = $school_rel_id ? get_the_post_thumbnail_url( $school_rel_id, 'medium' ) : '';
				if ( ! $school_thumb ) {
					$school_thumb = ltdh_get_fallback_image( 'school' );
				}
				$school_code    = $school_rel_id ? get_field( 'school_code', $school_rel_id ) : '';
				$school_address = $school_rel_id ? get_field( 'address', $school_rel_id ) : '';

				$schools_data[ $school_key ] = [
					'id'       => $school_rel_id,
					'name'     => $school_name,
					'thumb'    => $school_thumb,
					'code'     => $school_code,
					'address'  => $school_address,
					'programs' => [],
				];
			}

			$status            = get_post_meta( $prog_id, LTDH_META_ADMISSION_STATUS, true ) ?: 'tuyen-sinh';
			$types             = wp_get_post_terms( $prog_id, LTDH_TAX_TRAINING_TYPE );
			$type_name         = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';
			$clean_type_name   = preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) );
			$clean_major_name  = preg_replace( '/^ngành\s+/iu', '', trim( get_the_title( $major_id ) ) );
			$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );
			$tuition_fee       = ltdh_get_program_tuition_display( $prog_id );
			$duration          = get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm';
			$permalink         = get_permalink( $prog_id );

			$badge_class = 'bg-orange-50 text-orange-600 border border-orange-100';
			if ( $clean_type_name ) {
				$type_name_lower = mb_strtolower( trim( $clean_type_name ), 'UTF-8' );
				if ( false !== strpos( $type_name_lower, 'chính quy' ) ) {
					$badge_class = 'bg-blue-50 text-blue-600 border border-blue-100';
				} elseif ( false !== strpos( $type_name_lower, 'từ xa' ) ) {
					$badge_class = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
				} elseif ( false !== strpos( $type_name_lower, 'vừa học vừa làm' ) || false !== strpos( $type_name_lower, 'vừa làm vừa học' ) || false !== strpos( $type_name_lower, 'liên thông' ) || false !== strpos( $type_name_lower, 'văn bằng 2' ) ) {
					$badge_class = 'bg-amber-50 text-amber-600 border border-amber-100';
				}
			}

			$academic_year = get_field( 'tuition_academic_year', $prog_id ) ?: '2025 - 2026';

			$type_slug         = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->slug : '';
			$school_logo_url   = $school_rel_id && function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_rel_id, 'thumbnail' ) : get_template_directory_uri() . '/assets/images/cropped-logo-scaled-2.webp';
			$nganh_slug        = get_post_field( 'post_name', $major_id );

			$schools_data[ $school_key ]['programs'][] = [
				'id'                => $prog_id,
				'status'            => $status,
				'type_name'         => $clean_type_name,
				'opportunity_title' => $opportunity_title,
				'badge_class'       => $badge_class,
				'tuition_fee'       => $tuition_fee,
				'academic_year'     => $academic_year,
				'duration'          => $duration,
				'permalink'         => $permalink,
				'slug'              => get_post_field( 'post_name', $prog_id ),
				'he'                => $type_slug,
				'nganh'             => $nganh_slug,
				'major_name'        => $clean_major_name,
				'school_name'       => $school_name,
				'thumb'             => $school_logo_url,
			];
		}
		wp_reset_postdata();
	}

	$is_single_school_mode = ! empty( $selected_school );

	if ( ! empty( $schools_data ) ) :
		echo '<div class="space-y-4">';
		foreach ( $schools_data as $school ) :
			if ( $is_single_school_mode ) :
				?>
				<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
					<?php foreach ( $school['programs'] as $prog ) : ?>
						<div class="bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-5 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between group"
							 data-compare-btn
							 data-compare-type="program"
							 data-compare-id="<?php echo esc_attr( $prog['id'] ); ?>"
							 data-compare-title="<?php echo esc_attr( $prog['opportunity_title'] ); ?>"
							 data-compare-slug="<?php echo esc_attr( $prog['slug'] ); ?>"
							 data-compare-thumb="<?php echo esc_url( $prog['thumb'] ); ?>"
							 data-compare-he="<?php echo esc_attr( $prog['he'] ); ?>"
							 data-compare-nganh="<?php echo esc_attr( $prog['nganh'] ); ?>"
							 data-compare-major-name="<?php echo esc_attr( $prog['major_name'] ); ?>"
							 data-compare-school-name="<?php echo esc_attr( $prog['school_name'] ); ?>">
							<div>
								<!-- Header Badges -->
								<div class="flex items-center justify-between gap-2 mb-3.5">
									<?php if ( $prog['type_name'] ) : ?>
										<span class="<?php echo esc_attr( $prog['badge_class'] ); ?> text-xs font-black px-3 py-1 rounded-full uppercase tracking-wide border shadow-2xs">
											<?php echo esc_html( $prog['type_name'] ); ?>
										</span>
									<?php endif; ?>
									<span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200/60 shrink-0">
										Biểu phí <?php echo esc_html( $prog['academic_year'] ); ?>
									</span>
								</div>

								<!-- Program Title -->
								<h4 class="font-black text-slate-900 text-base sm:text-lg group-hover:text-brand-primary transition-colors leading-snug mb-3">
									<a href="<?php echo esc_url( $prog['permalink'] ); ?>">
										<?php echo esc_html( $prog['opportunity_title'] ); ?>
									</a>
								</h4>

								<!-- Key Info List -->
								<div class="space-y-2 text-xs sm:text-sm text-slate-600 py-3.5 border-t border-b border-slate-100 mb-4">
									<div class="flex items-center justify-between">
										<span class="text-slate-400">Thời gian đào tạo:</span>
										<span class="font-bold text-slate-800"><?php echo esc_html( $prog['duration'] ); ?></span>
									</div>
									<div class="flex items-center justify-between">
										<span class="text-slate-400">Học phí:</span>
										<span class="font-extrabold text-brand-primary text-sm sm:text-base"><?php echo esc_html( $prog['tuition_fee'] ); ?></span>
									</div>
								</div>
							</div>

							<!-- Card Footer Action Buttons -->
							<div class="flex items-center gap-2 pt-1">
								<?php if ( $prog['status'] === 'tam-ngung' ) : ?>
									<span class="w-full text-center text-xs text-slate-400 bg-slate-100 py-2.5 rounded-xl font-bold">Tạm ngưng tuyển sinh</span>
								<?php else : ?>
									<a href="<?php echo esc_url( $prog['permalink'] ); ?>" class="text-xs sm:text-sm py-2.5 px-4 rounded-xl font-extrabold ltdh-btn-details flex-1 text-center shadow-2xs hover:shadow-md transition-all">
										Tìm hiểu
									</a>
									<button type="button"
											class="ltdh-compare-toggle text-xs md:text-sm text-slate-600 hover:text-brand-primary font-bold border border-slate-200 hover:border-brand-primary rounded-xl py-2.5 px-3 transition-all min-h-[44px] flex items-center justify-center flex-1 bg-white hover:bg-slate-50"
											data-compare-type="program"
											data-compare-id="<?php echo esc_attr( $prog['id'] ); ?>"
											data-compare-title="<?php echo esc_attr( $prog['opportunity_title'] ); ?>"
											data-compare-slug="<?php echo esc_attr( $prog['slug'] ); ?>"
											data-compare-he="<?php echo esc_attr( $prog['he'] ); ?>"
											data-compare-nganh="<?php echo esc_attr( $prog['nganh'] ); ?>"
											data-compare-major-name="<?php echo esc_attr( $prog['major_name'] ); ?>"
											data-compare-school-name="<?php echo esc_attr( $prog['school_name'] ); ?>"
											data-compare-thumb="<?php echo esc_url( $prog['thumb'] ); ?>">
										So sánh
									</button>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<?php
			elseif ( count( $school['programs'] ) > 1 ) :
				?>
				<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all p-4 md:p-5 flex flex-col gap-4">
					<div class="flex items-center gap-3">
						<div class="h-9 w-9 sm:h-11 sm:w-11 bg-slate-200 bg-cover bg-center rounded-lg shrink-0 border border-slate-100" style="background-image: url('<?php echo esc_url( $school['thumb'] ); ?>'); font-size: 0;"></div>
						<div class="space-y-0.5 flex-1 min-w-0">
							<h4 class="font-bold text-slate-800 text-xs sm:text-sm hover:text-[#00308b] transition-colors leading-snug">
								<?php if ( $school['id'] ) : ?>
									<a href="<?php echo esc_url( get_permalink( $school['id'] ) ); ?>">
										<?php echo esc_html( $school['name'] ); ?><?php if ( $school['code'] ) { echo ' - ' . esc_html( $school['code'] ); } ?>
									</a>
								<?php else : ?>
									<span class="font-semibold text-slate-700"><?php echo esc_html( $school['name'] ); ?></span>
								<?php endif; ?>
							</h4>
							<?php if ( $school['address'] ) : ?>
								<p class="text-xs text-slate-400 flex items-center gap-1">
									<span>📍</span>
									<span class="truncate"><?php echo esc_html( $school['address'] ); ?></span>
								</p>
							<?php endif; ?>
						</div>
					</div>

					<div class="border-t border-slate-100 pt-3">
						<div class="flex items-center justify-between gap-2 mb-2.5">
							<div class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Hình thức đào tạo:</div>
							<?php $header_year = ! empty( $school['programs'][0]['academic_year'] ) ? $school['programs'][0]['academic_year'] : '2025 - 2026'; ?>
							<span class="text-[11px] font-semibold text-slate-500 bg-slate-100/90 px-2 py-0.5 rounded border border-slate-200/50 whitespace-nowrap shrink-0">
								Biểu phí <?php echo esc_html( $header_year ); ?>
							</span>
						</div>
						<div class="divide-y divide-slate-100/80 space-y-1 sm:space-y-0">
							<?php foreach ( $school['programs'] as $prog ) : ?>
								<div class="flex items-center justify-between gap-2.5 py-2.5 sm:py-3 rounded-xl hover:bg-slate-50/90 transition-all text-xs sm:text-sm my-0.5">
									<div class="flex items-center gap-2.5 sm:gap-5 min-w-0 flex-1">
										<?php if ( $prog['type_name'] ) : ?>
											<div class="w-[105px] sm:w-[125px] shrink-0">
												<span class="<?php echo esc_attr( $prog['badge_class'] ); ?> inline-block w-full text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider text-center">
													<?php echo esc_html( $prog['type_name'] ); ?>
												</span>
											</div>
										<?php endif; ?>
										<div class="min-w-0 flex-1">
											<h5 class="font-bold text-slate-800 text-xs sm:text-sm hover:text-[#00308b] truncate leading-snug">
												<a href="<?php echo esc_url( $prog['permalink'] ); ?>">
													<?php echo esc_html( $prog['opportunity_title'] ); ?>
												</a>
											</h5>
											<div class="flex flex-col sm:flex-row sm:items-center gap-x-5 gap-y-0.5 text-slate-600 min-w-0 flex-1 mt-0.5">
												<span class="inline-flex items-center gap-1 shrink-0">
													<span class="text-slate-400">⏱</span>
													<span>Thời gian: <strong class="font-semibold text-slate-800"><?php echo esc_html( $prog['duration'] ); ?></strong></span>
												</span>
												<span class="hidden sm:inline text-slate-200">|</span>
												<span class="inline-flex items-center gap-1 min-w-0">
													<span>Học phí: <strong class="font-bold text-brand-primary"><?php echo esc_html( $prog['tuition_fee'] ); ?></strong></span>
												</span>
											</div>
										</div>
									</div>
									<div class="shrink-0 ml-1 sm:ml-3">
										<?php if ( $prog['status'] === 'tam-ngung' ) : ?>
											<span class="text-xs text-slate-400 font-medium">Tạm ngưng</span>
										<?php else : ?>
											<a href="<?php echo esc_url( $prog['permalink'] ); ?>" class="text-xs font-bold text-[#00308b] hover:text-blue-700 hover:underline inline-flex items-center gap-0.5 py-1 px-1.5 sm:px-2.5 rounded-lg hover:bg-blue-50 transition-colors whitespace-nowrap">
												<span>Tìm hiểu</span>
												<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
													<path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
												</svg>
											</a>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php
			else :
				$prog = $school['programs'][0];
				?>
				<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all p-4 md:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
					<div class="flex items-center gap-4 flex-1 min-w-0">
						<div class="h-20 w-20 bg-slate-200 bg-cover bg-center rounded-xl shrink-0 border border-slate-100 shadow-sm" style="background-image: url('<?php echo esc_url( $school['thumb'] ); ?>'); font-size: 0;"></div>
						<div class="space-y-1 flex-1 min-w-0">
							<div class="flex items-center gap-2 flex-wrap">
								<h4 class="font-extrabold text-slate-800 text-base sm:text-lg hover:text-[#00308b] transition-colors leading-snug">
									<a href="<?php echo esc_url( $prog['permalink'] ); ?>">
										<?php echo esc_html( $prog['opportunity_title'] ); ?>
									</a>
								</h4>
								<?php if ( $prog['type_name'] ) : ?>
									<span class="<?php echo esc_attr( $prog['badge_class'] ); ?> text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider">
										<?php echo esc_html( $prog['type_name'] ); ?>
									</span>
								<?php endif; ?>
							</div>
							<div class="text-xs sm:text-sm text-slate-600 font-medium">
								<?php if ( $school['id'] ) : ?>
									<a href="<?php echo esc_url( get_permalink( $school['id'] ) ); ?>" class="hover:text-[#00308b] transition-colors">
										<?php echo esc_html( $school['name'] ); ?><?php if ( $school['code'] ) { echo ' - ' . esc_html( $school['code'] ); } ?>
									</a>
								<?php else : ?>
									<span><?php echo esc_html( $school['name'] ); ?></span>
								<?php endif; ?>
							</div>
							<?php if ( $school['address'] ) : ?>
								<p class="text-xs sm:text-sm text-slate-400 flex items-center gap-1">
									<span>📍</span>
									<span class="truncate"><?php echo esc_html( $school['address'] ); ?></span>
								</p>
							<?php endif; ?>
							
							<div class="flex flex-wrap gap-x-4 gap-y-1 text-xs sm:text-sm text-slate-500 pt-0.5">
								<p>Thời gian: <span class="font-semibold text-slate-700"><?php echo esc_html( $prog['duration'] ); ?></span></p>
								<p class="hidden sm:inline text-slate-300">|</p>
								<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( $prog['tuition_fee'] ); ?></span></p>
							</div>
						</div>
					</div>

					<div class="shrink-0 w-full sm:w-auto flex items-center justify-end">
						<?php if ( $prog['status'] === 'tam-ngung' ) : ?>
							<span class="text-xs sm:text-sm text-slate-400 bg-slate-150 px-4 py-2 rounded-lg font-bold">Tạm ngưng</span>
						<?php else : ?>
							<a href="<?php echo esc_url( $prog['permalink'] ); ?>" class="w-full sm:w-auto text-xs sm:text-sm px-5 py-2.5 rounded-lg uppercase ltdh-btn-details min-h-[36px] flex items-center justify-center">Tìm hiểu</a>
						<?php endif; ?>
					</div>
				</div>
				<?php
			endif;
		endforeach;
		echo '</div>';
	else :
		?>
		<div class="text-center py-12 bg-white border border-slate-200/80 rounded-2xl p-8 shadow-2xs w-full">
			<span class="text-4xl block mb-3">🔍</span>
			<h3 class="font-bold text-slate-800 text-base mb-1">Không tìm thấy chương trình phù hợp</h3>
			<p class="text-slate-500 text-xs sm:text-sm max-w-md mx-auto">Vui lòng thử chọn hình thức đào tạo khác hoặc thay đổi trường tuyển sinh.</p>
		</div>
		<?php
	endif;

	$total_programs_count = 0;
	if ( ! empty( $schools_data ) ) {
		foreach ( $schools_data as $s_item ) {
			$total_programs_count += count( $s_item['programs'] );
		}
	}

	return $is_single_school_mode ? $total_programs_count : count( $schools_data );
}

/**
 * AJAX handler for major page programs filtering.
 */
add_action( 'wp_ajax_ltdh_filter_major_programs', 'ltdh_ajax_filter_major_programs' );
add_action( 'wp_ajax_nopriv_ltdh_filter_major_programs', 'ltdh_ajax_filter_major_programs' );

function ltdh_ajax_filter_major_programs() {
	$major_id = isset( $_POST['major_id'] ) ? intval( $_POST['major_id'] ) : 0;
	$he       = isset( $_POST['he'] ) ? sanitize_text_field( wp_unslash( $_POST['he'] ) ) : '';
	$school   = isset( $_POST['school'] ) ? sanitize_text_field( wp_unslash( $_POST['school'] ) ) : '';

	if ( ! $major_id ) {
		wp_send_json_error( [ 'message' => 'Missing major_id' ] );
	}

	ob_start();
	$count = ltdh_render_major_programs_list( $major_id, $he, $school );
	$html  = ob_get_clean();

	wp_send_json_success( [
		'html'  => $html,
		'count' => $count,
	] );
}

