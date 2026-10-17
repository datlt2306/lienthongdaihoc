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

	// Blog and Category Archives: align Main Query posts_per_page with index.php (8 per page)
	if ( $query->is_home() || $query->is_category() ) {
		$query->set( 'posts_per_page', 8 );

		$school_filter   = isset( $_GET['truong'] ) ? intval( $_GET['truong'] ) : 0;
		$major_filter    = isset( $_GET['nganh'] ) ? intval( $_GET['nganh'] ) : 0;
		$program_filter  = isset( $_GET['chuong-trinh'] ) ? intval( $_GET['chuong-trinh'] ) : 0;

		if ( $school_filter || $major_filter || $program_filter ) {
			$meta_query = $query->get( 'meta_query' );
			if ( ! is_array( $meta_query ) ) {
				$meta_query = [];
			}
			$meta_query['relation'] = 'AND';
			if ( $school_filter ) {
				$meta_query[] = [
					'key'     => 'related_schools',
					'value'   => '"' . $school_filter . '"',
					'compare' => 'LIKE',
				];
			}
			if ( $major_filter ) {
				$meta_query[] = [
					'key'     => 'related_majors',
					'value'   => '"' . $major_filter . '"',
					'compare' => 'LIKE',
				];
			}
			if ( $program_filter ) {
				$meta_query[] = [
					'key'     => 'related_programs',
					'value'   => '"' . $program_filter . '"',
					'compare' => 'LIKE',
				];
			}
			$query->set( 'meta_query', $meta_query );
		}
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

	if ( $query->is_post_type_archive( LTDH_CPT_SCHOOL ) ) {
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC' );
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
 * Custom orderby for School queries:
 * 1. Posts with menu_order > 0 come first (ASC: 1, 2, 3...)
 * 2. Posts without order (0) come after, ordered by post_date DESC
 */
add_filter( 'posts_orderby', 'ltdh_filter_school_posts_orderby', 10, 2 );
function ltdh_filter_school_posts_orderby( $orderby, $query ) {
	if ( is_admin() ) {
		return $orderby;
	}

	$post_type = $query->get( 'post_type' );
	$is_school = ( defined( 'LTDH_CPT_SCHOOL' ) && LTDH_CPT_SCHOOL === $post_type )
		|| ( 'school' === $post_type )
		|| ( is_array( $post_type ) && in_array( 'school', $post_type, true ) )
		|| ( function_exists( 'is_post_type_archive' ) && $query->is_post_type_archive( 'school' ) );

	if ( $is_school ) {
		$requested_orderby = $query->get( 'orderby' );
		if ( empty( $requested_orderby ) || 'menu_order' === $requested_orderby || 'date' === $requested_orderby || ( is_array( $requested_orderby ) && isset( $requested_orderby['menu_order'] ) ) ) {
			global $wpdb;
			return "CASE WHEN {$wpdb->posts}.menu_order > 0 THEN 0 ELSE 1 END ASC, CASE WHEN {$wpdb->posts}.menu_order > 0 THEN {$wpdb->posts}.menu_order ELSE 999999 END ASC, {$wpdb->posts}.post_date DESC";
		}
	}

	return $orderby;
}

/**
 * Add Order, Training Type, and Author columns to School post list in WordPress Admin.
 */
add_filter( 'manage_school_posts_columns', 'ltdh_add_school_admin_columns' );
function ltdh_add_school_admin_columns( $columns ) {
	$new_columns  = [];
	$author_label = isset( $columns['author'] ) ? $columns['author'] : __( 'Author' );

	foreach ( $columns as $key => $title ) {
		if ( 'date' === $key ) {
			$new_columns['author'] = $author_label;
		}

		if ( 'author' === $key || 'taxonomy-campus' === $key || 'taxonomy-training_type' === $key ) {
			continue;
		}

		$new_columns[ $key ] = $title;
		if ( 'title' === $key ) {
			$new_columns['school_order']         = __( 'Thứ tự', 'lienthongdaihoc' );
			$new_columns['school_training_type'] = __( 'Hình thức học', 'lienthongdaihoc' );
		}
	}

	if ( ! isset( $new_columns['author'] ) ) {
		$new_columns['author'] = $author_label;
	}

	unset( $new_columns['taxonomy-campus'], $new_columns['taxonomy-training_type'] );

	return $new_columns;
}

add_action( 'manage_school_posts_custom_column', 'ltdh_render_school_admin_columns', 10, 2 );
function ltdh_render_school_admin_columns( $column, $post_id ) {
	if ( 'school_order' === $column ) {
		$order = function_exists( 'ltdh_get_school_order' ) ? ltdh_get_school_order( $post_id ) : intval( get_post_field( 'menu_order', $post_id ) );
		echo $order > 0 ? '<strong style="color:#00308b; font-size:14px;">' . esc_html( $order ) . '</strong>' : '<span style="color:#94a3b8;">—</span>';
	} elseif ( 'school_training_type' === $column ) {
		$terms = wp_get_post_terms( $post_id, 'training_type' );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$slug       = $t->slug;
				$name       = preg_replace( '/^hệ\s+/iu', '', $t->name );
				$filter_url = add_query_arg( [
					'post_type'     => 'school',
					'training_type' => $slug,
				], admin_url( 'edit.php' ) );

				if ( 'tu-xa' === $slug || 'dao-tao-tu-xa' === $slug ) {
					echo '<a href="' . esc_url( $filter_url ) . '" style="text-decoration:none;"><span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:600;font-size:11px;margin:2px 4px 2px 0;">' . esc_html( $name ) . '</span></a>';
				} elseif ( 'vua-hoc-vua-lam' === $slug ) {
					echo '<a href="' . esc_url( $filter_url ) . '" style="text-decoration:none;"><span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-weight:600;font-size:11px;margin:2px 4px 2px 0;">' . esc_html( $name ) . '</span></a>';
				} elseif ( 'chinh-quy' === $slug ) {
					echo '<a href="' . esc_url( $filter_url ) . '" style="text-decoration:none;"><span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-weight:600;font-size:11px;margin:2px 4px 2px 0;">' . esc_html( $name ) . '</span></a>';
				} else {
					echo '<a href="' . esc_url( $filter_url ) . '" style="text-decoration:none;"><span style="display:inline-block;padding:2px 8px;border-radius:12px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-weight:600;font-size:11px;margin:2px 4px 2px 0;">' . esc_html( $name ) . '</span></a>';
				}
			}
		} else {
			echo '<span style="color:#94a3b8;">—</span>';
		}
	} elseif ( 'author' === $column ) {
		$author_id   = (int) get_post_field( 'post_author', $post_id );
		$author_name = get_the_author_meta( 'display_name', $author_id );
		if ( ! empty( $author_name ) ) {
			$filter_url = add_query_arg( [
				'post_type' => 'school',
				'author'    => $author_id,
			], admin_url( 'edit.php' ) );
			echo '<a href="' . esc_url( $filter_url ) . '">' . esc_html( $author_name ) . '</a>';
		} else {
			echo '<span style="color:#94a3b8;">—</span>';
		}
	}
}

add_filter( 'manage_edit-school_sortable_columns', 'ltdh_school_admin_sortable_columns' );
function ltdh_school_admin_sortable_columns( $columns ) {
	$columns['school_order'] = 'menu_order';
	$columns['author']       = 'author';
	return $columns;
}

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
				'terms'    => [ 'chinh-quy', 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ],
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
			$school_code   = $school_rel_id ? trim( (string) ( get_field( 'school_code', $school_rel_id ) ?: get_post_meta( $school_rel_id, 'school_code', true ) ) ) : '';
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
				$major_name = preg_replace( '/\s*[-–—].*$/u', '', $major_name );
				$major_name = preg_replace( '/\s*\([^)]*\)$/u', '', $major_name );
			}
			$clean_major_name = preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) );

			$thumb = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium' ) : '';
			if ( ! $thumb ) {
				$thumb = function_exists( 'ltdh_get_fallback_image' ) ? ltdh_get_fallback_image( 'program' ) : get_template_directory_uri() . '/assets/images/banner-program.jpg';
			}

			$prog_types       = wp_get_post_terms( $prog_id, 'training_type' );
			$type_name        = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->name : '';
			$t_name           = $type_name;
			$type_slug        = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->slug : '';
			$clean_type_name  = preg_replace( '/^hệ\s+/iu', '', trim( $t_name ) );
			$card_headline    = function_exists( 'ltdh_format_program_card_headline' )
				? ltdh_format_program_card_headline( get_the_title( $prog_id ), $school_code, $school_name, $clean_major_name )
				: ( 'Liên thông ngành ' . $clean_major_name . ( $school_code ? ' - ' . $school_code : ( $school_name ? ' - ' . $school_name : '' ) ) );
			$admission_status    = function_exists( 'ltdh_get_program_admission_status' ) 
				? ltdh_get_program_admission_status( $prog_id ) 
				: ( get_post_meta( $prog_id, 'admission_status', true ) ?: 'tuyen-sinh' );

			// Badge classes based on training type (Unified brand navy badge across all training types)
			$badge_class = 'bg-[#00308b] text-white shadow-md border border-white/20';

			$school_thumb    = $school_rel_id && function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_rel_id, 'medium' ) : get_stylesheet_directory_uri() . '/assets/images/banner-school.jpg';
			$learning_details = function_exists( 'ltdh_get_program_learning_details' ) ? ltdh_get_program_learning_details( $prog_id ) : [ 'mode' => 'Học online 100%' ];
			$school_logo_id   = $school_rel_id && function_exists( 'ltdh_get_school_image_id' ) ? ltdh_get_school_image_id( $school_rel_id ) : 0;
			$school_logo_url  = $school_rel_id && function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_rel_id, 'thumbnail' ) : get_stylesheet_directory_uri() . '/assets/images/cropped-logo-scaled-2.webp';
			$tuition          = get_field( 'tuition_fee', $prog_id ) ?: 'Liên hệ';
			$duration         = get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm';
			?>
			<div class="bg-white border border-slate-200/90 rounded-xl md:rounded-2xl overflow-hidden shadow-2xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between group"
				 data-compare-btn
				 data-compare-type="program"
				 data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
				 data-compare-title="<?php echo esc_attr( $card_headline ); ?>"
				 data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
				 data-compare-thumb="<?php echo esc_url( $school_logo_url ); ?>"
				 data-compare-major-name="<?php echo esc_attr( $clean_major_name ); ?>"
				 data-compare-school-name="<?php echo esc_attr( $school_name ); ?>">

				<div>
					<!-- School Cover Image with Badges (Links to Program) -->
					<a href="<?php the_permalink(); ?>" class="block h-32 sm:h-44 md:h-48 w-full bg-slate-100 relative overflow-hidden group/cover">
						<img src="<?php echo esc_url( $school_thumb ); ?>"
							 alt="<?php echo esc_attr( $card_headline ?? get_the_title() ); ?>"
							 loading="lazy"
							 decoding="async"
							 width="300"
							 height="180"
							 class="absolute inset-0 w-full h-full object-cover object-center group-hover/cover:scale-105 transition-transform duration-500">
						<div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-slate-900/10 to-slate-900/40 pointer-events-none"></div>
						
						<!-- Badges Floating on Cover Image: Status (Left) & Training Type (Right) -->
						<div class="absolute top-2 left-2 right-2 md:top-2.5 md:left-2.5 md:right-2.5 z-10 pointer-events-none flex items-center justify-between gap-1.5">
							<div class="flex items-center gap-1">
								<?php if ( 'tam-ngung' === $admission_status ) : ?>
									<span class="bg-slate-600/95 text-white text-[10px] sm:text-[11px] md:text-xs font-bold px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full uppercase tracking-wider shadow-md flex items-center gap-1 backdrop-blur-md shrink-0">
										<span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
										Tạm dừng
									</span>
								<?php elseif ( 'sap-mo' === $admission_status ) : ?>
									<span class="bg-amber-500/95 text-white text-[10px] sm:text-[11px] md:text-xs font-bold px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full uppercase tracking-wider shadow-md backdrop-blur-md shrink-0">
										Sắp mở
									</span>
								<?php else : ?>
									<span class="bg-emerald-600/95 text-white text-[10px] sm:text-[11px] md:text-xs font-bold px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full uppercase tracking-wider shadow-md flex items-center gap-1 backdrop-blur-md shrink-0">
										<span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
										Tuyển sinh
									</span>
								<?php endif; ?>
							</div>

							<?php if ( ! empty( $clean_type_name ) ) : ?>
								<span class="inline-flex items-center px-2.5 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-[11px] md:text-xs font-bold shrink-0 tracking-wide <?php echo esc_attr( $badge_class ); ?>">
									<?php echo esc_html( $clean_type_name ); ?>
								</span>
							<?php endif; ?>
						</div>
					</a>

					<!-- Card Body -->
					<div class="p-3 sm:p-4 pb-0">
						<!-- School Header with Logo (Institution Metadata - Links to Program) -->
						<a href="<?php the_permalink(); ?>" class="flex items-center gap-2 sm:gap-2.5 mb-1.5 sm:mb-2 relative z-10 no-underline group/school">
							<div class="w-8 h-8 sm:w-10 sm:h-10 bg-white border border-slate-200/90 rounded-lg sm:rounded-xl flex items-center justify-center p-1 shrink-0 shadow-2xs group-hover/school:border-brand-primary/40 group-hover/school:shadow-xs transition-all -mt-4 sm:-mt-5">
								<?php if ( ! empty( $school_logo_url ) ) : ?>
									<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_name ); ?>" class="h-full w-full object-contain p-0.5">
								<?php elseif ( $school_logo_id ) : ?>
									<?php echo wp_get_attachment_image( $school_logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
								<?php else : ?>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-brand-primary"><path d="M11.7 2.805a.75.75 0 0 1 .6 0l9.3 4.25a.75.75 0 0 1 0 1.39l-9.3 4.25a.75.75 0 0 1-.6 0L2.4 8.445a.75.75 0 0 1 0-1.39l9.3-4.25ZM2.84 10.74l6.735 3.08a2.25 2.25 0 0 0 1.85 0l6.735-3.08v3.42c0 .532-.244 1.026-.642 1.378L12.5 19.544a1.25 1.25 0 0 1-1.6 0l-5.023-3.97a1.75 1.75 0 0 1-.642-1.378v-3.456Z" /><path d="M20.25 10.32v5.43a3.25 3.25 0 0 1 3.25 3.25h-.5a.75.75 0 0 0 0 1.5h.5a4.75 4.75 0 0 0 4.75-4.75v-5.43a.75.75 0 0 0-1.5 0Z" /></svg>
								<?php endif; ?>
							</div>
							<div class="min-w-0 flex-1">
								<span class="text-[10px] sm:text-xs font-bold text-slate-500 group-hover/school:text-brand-primary uppercase tracking-tight sm:tracking-wide block truncate transition-colors" title="<?php echo esc_attr( $school_name ); ?>">
									<?php echo esc_html( $school_name ); ?>
								</span>
							</div>
						</a>

						<!-- Program Name (Primary Hero Headline) -->
						<div class="mb-1.5 sm:mb-2">
							<h2 class="font-black text-slate-900 text-xs sm:text-base md:text-lg hover:text-brand-primary leading-tight sm:leading-snug line-clamp-2 min-h-[28px] sm:min-h-[40px] transition-colors">
								<a href="<?php the_permalink(); ?>"><?php echo esc_html( $card_headline ); ?></a>
							</h2>
						</div>

						<!-- Key Information List (Hidden on mobile for Apple-minimal compact card UX, visible on sm+) -->
						<div class="hidden sm:block space-y-1 sm:space-y-1.5 text-[11px] sm:text-xs md:text-sm text-slate-600 pt-2 sm:pt-2.5 pb-1.5 sm:pb-2 border-t border-slate-100">
							<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-0.5 sm:gap-1">
								<span class="text-slate-400 text-[10px] sm:text-xs">Học phí:</span>
								<span class="font-extrabold text-brand-primary text-xs sm:text-sm md:text-base leading-none truncate"><?php echo esc_html( $tuition ); ?></span>
							</div>
							<div class="flex items-center justify-between gap-1">
								<span class="text-slate-400 text-[10px] sm:text-xs">Thời gian:</span>
								<span class="font-bold text-slate-700 text-[11px] sm:text-xs truncate"><?php echo esc_html( $duration ); ?></span>
							</div>
							<div class="flex items-center justify-between gap-1">
								<span class="text-slate-400 text-[10px] sm:text-xs">Hình thức:</span>
								<span class="font-bold text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded text-[10px] sm:text-xs truncate max-w-[90px] sm:max-w-none"><?php echo esc_html( $learning_details['mode'] ); ?></span>
							</div>
						</div>
					</div>
				</div>

				<!-- Card Footer Action Buttons -->
				<div class="p-3 sm:p-4 pt-0 pb-3 sm:pb-3.5">
					<div class="pt-2 sm:pt-2.5 sm:border-t sm:border-slate-100 flex items-stretch gap-1.5 sm:gap-2">
						<a href="<?php the_permalink(); ?>" class="text-[10px] sm:text-xs md:text-sm py-1.5 sm:py-2 px-1 sm:px-3 rounded-lg sm:rounded-xl font-bold ltdh-btn-details min-h-[36px] sm:min-h-[38px] flex items-center justify-center flex-1 shadow-2xs hover:shadow-md transition-all text-center whitespace-nowrap">
							Tìm hiểu
						</a>
						<button type="button"
								class="ltdh-compare-toggle text-[10px] sm:text-xs md:text-sm text-slate-600 hover:text-brand-primary font-bold border border-slate-200 hover:border-brand-primary rounded-lg sm:rounded-xl py-1.5 sm:py-2 px-1 sm:px-3 transition-all min-h-[36px] sm:min-h-[38px] flex items-center justify-center flex-1 bg-white hover:bg-slate-50 text-center whitespace-nowrap"
								data-compare-type="program"
								data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
								data-compare-title="<?php echo esc_attr( $card_headline ); ?>"
								data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
								data-compare-he="<?php echo esc_attr( $type_slug ); ?>"
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
		<div class="col-span-full text-center py-16 bg-white border border-slate-200 rounded-2xl p-8 shadow-xs w-full">
			<span class="text-5xl block mb-4">🔍</span>
			<h3 class="font-extrabold text-slate-800 text-lg mb-2">Không tìm thấy chương trình phù hợp</h3>
			<p class="text-slate-500 text-sm max-w-md mx-auto mb-6">Hãy thử thay đổi điều kiện lọc hoặc đặt lại bộ lọc để tìm kiếm các chương trình khác.</p>
			<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" class="js-ltdh-reset-filter inline-flex items-center gap-2 bg-brand-primary text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-brand-primary-dark transition-colors cursor-pointer">
				✕ Xóa tất cả bộ lọc
			</a>
		</div>
		<?php
	endif;
	$html = ob_get_clean();

	// Compute heading title
	$active_type_term = ! empty( $type_slug ) ? ( get_term_by( 'slug', $type_slug, LTDH_TAX_TRAINING_TYPE ) ?: ( 'tu-xa' === $type_slug ? get_term_by( 'slug', 'dao-tao-tu-xa', LTDH_TAX_TRAINING_TYPE ) : null ) ) : null;
	$heading_title = $active_type_term ? 'Liên thông ' . preg_replace( '/^(hệ|liên\s+thông)\s+/iu', '', $active_type_term->name ) : 'Chương trình tuyển sinh Liên thông Đại học';

	// Compute active filter items
	$default_regions = [
		'ha-noi'      => 'Hà Nội',
		'ho-chi-minh' => 'TP. Hồ Chí Minh',
		'da-nang'     => 'Đà Nẵng',
		'thai-nguyen' => 'Thái Nguyên',
		'online'      => 'Học Online',
		'mien-bac'    => 'Miền Bắc',
		'mien-trung'  => 'Miền Trung',
		'mien-nam'    => 'Miền Nam',
	];

	$active_filter_items = [];
	$search_kw = ! empty( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';
	if ( ! empty( $search_kw ) ) {
		$active_filter_items[] = [ 'label' => 'Từ khóa: "' . $search_kw . '"', 'key' => 's' ];
	}
	if ( ! empty( $region_slug ) ) {
		$r_label = $default_regions[ $region_slug ] ?? $region_slug;
		$active_filter_items[] = [ 'label' => 'Khu vực: ' . $r_label, 'key' => 'khu_vuc' ];
	}
	if ( ! empty( $school_raw ) ) {
		$sch_title = $school_id ? get_the_title( $school_id ) : $school_raw;
		$active_filter_items[] = [ 'label' => 'Trường: ' . $sch_title, 'key' => 'truong' ];
	}
	if ( ! empty( $nhom_slug ) ) {
		$maj_title = ! empty( $major_post ) ? $major_post->post_title : $nhom_slug;
		$active_filter_items[] = [ 'label' => 'Ngành: ' . $maj_title, 'key' => 'nganh' ];
	}
	if ( ! empty( $type_slug ) ) {
		$t_title = $active_type_term ? $active_type_term->name : $type_slug;
		$active_filter_items[] = [ 'label' => 'Hệ: ' . $t_title, 'key' => 'he' ];
	}
	if ( ! empty( $sort ) ) {
		$sort_labels = [ 'title_asc' => 'A-Z', 'title_desc' => 'Z-A', 'date_desc' => 'Mới nhất' ];
		$active_filter_items[] = [ 'label' => 'Xếp: ' . ( $sort_labels[ $sort ] ?? $sort ), 'key' => 'sort' ];
	}
	$active_filter_count = count( $active_filter_items );
	$has_active_filters  = $active_filter_count > 0;

	// Preserved args for URLs
	$preserved_args = [];
	if ( ! empty( $search_kw ) ) $preserved_args['s'] = $search_kw;
	if ( ! empty( $region_slug ) ) $preserved_args['khu_vuc'] = $region_slug;
	if ( ! empty( $school_raw ) ) $preserved_args['truong'] = $school_raw;
	if ( ! empty( $nhom_slug ) ) $preserved_args['nganh'] = $nhom_slug;
	if ( ! empty( $type_slug ) ) $preserved_args['he'] = $type_slug;
	if ( ! empty( $sort ) ) $preserved_args['sort'] = $sort;
	if ( 12 !== $limit ) $preserved_args['limit'] = $limit;

	$base_archive_link = home_url( '/hinh-thuc-dao-tao/' );

	// Pagination HTML
	$pagination_html = '';
	if ( $query->max_num_pages > 1 ) {
		$pagination_base = add_query_arg( array_merge( $preserved_args, [ 'paged' => '%#%' ] ), $base_archive_link );
		$page_links = paginate_links( [
			'base'      => $pagination_base,
			'format'    => '',
			'total'     => $query->max_num_pages,
			'current'   => $paged,
			'mid_size'  => 2,
			'prev_text' => '← Trước',
			'next_text' => 'Sau →',
			'type'      => 'plain',
		] );
		if ( $page_links ) {
			$pagination_html = '<nav class="navigation pagination" aria-label="Bài viết"><h2 class="screen-reader-text">Điều hướng bài viết</h2><div class="nav-links">' . $page_links . '</div></nav>';
		}
	}

	// Chips HTML
	ob_start();
	if ( ! empty( $active_filter_items ) ) :
		?>
		<div class="flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden pt-2.5 pb-0.5">
			<?php foreach ( $active_filter_items as $item ) : 
				$remove_args = $preserved_args;
				unset( $remove_args[ $item['key'] ] );
				$remove_url = ! empty( $remove_args ) ? add_query_arg( $remove_args, $base_archive_link ) : $base_archive_link;
			?>
				<a href="<?php echo esc_url( $remove_url ); ?>" data-filter-key="<?php echo esc_attr( $item['key'] ); ?>" class="js-ltdh-chip-remove inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-semibold bg-blue-50 text-blue-900 border border-blue-200/60 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition-colors shrink-0 select-none">
					<span class="truncate max-w-[140px]"><?php echo esc_html( $item['label'] ); ?></span>
					<span class="text-blue-400 hover:text-rose-600 font-bold ml-0.5">✕</span>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	endif;
	$chips_html = ob_get_clean();

	wp_send_json_success( [
		'html'                => $html,
		'found_posts'         => $query->found_posts,
		'heading_title'       => $heading_title,
		'pagination_html'     => $pagination_html,
		'chips_html'          => $chips_html,
		'has_active_filters'  => $has_active_filters,
		'active_filter_count' => $active_filter_count,
		'active_type'         => $type_slug,
		'paged'               => $paged,
		'max_pages'           => $query->max_num_pages,
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

	$offered_program_ids = get_post_meta( $major_id, LTDH_META_OFFERED_PROGRAMS, true );
	$direct_prog_ids = get_posts( [
		'post_type'   => LTDH_CPT_PROGRAM,
		'post_status' => 'publish',
		'numberposts' => -1,
		'fields'      => 'ids',
		'meta_query'  => [
			'relation' => 'OR',
			[
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => $major_id,
				'compare' => '=',
			],
			[
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => '"' . $major_id . '"',
				'compare' => 'LIKE',
			],
			[
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => 's:' . strlen( (string) $major_id ) . ':"' . $major_id . '"',
				'compare' => 'LIKE',
			],
			[
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => 'i:' . $major_id . ';',
				'compare' => 'LIKE',
			],
		],
	] );
	$all_candidate_prog_ids = array_unique( array_filter( array_merge(
		is_array( $offered_program_ids ) ? $offered_program_ids : [],
		is_array( $direct_prog_ids ) ? $direct_prog_ids : []
	) ) );

	$meta_query = [
		'relation' => 'AND',
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

	if ( ! empty( $all_candidate_prog_ids ) ) {
		$query_args['post__in'] = $all_candidate_prog_ids;
	} else {
		$meta_query[] = [
			'relation' => 'OR',
			[
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => $major_id,
				'compare' => '=',
			],
			[
				'key'     => LTDH_META_MAJOR_REL,
				'value'   => '"' . $major_id . '"',
				'compare' => 'LIKE',
			],
		];
		$query_args['meta_query'] = $meta_query;
	}

	if ( ! empty( $tax_query ) ) {
		$query_args['tax_query'] = $tax_query;
	}

	$programs_query = new WP_Query( $query_args );

	$schools_data = [];
	if ( $programs_query->have_posts() ) {
		while ( $programs_query->have_posts() ) {
			$programs_query->the_post();
			$prog_id       = get_the_ID();
			$school_rel_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $prog_id ) : intval( get_post_meta( $prog_id, LTDH_META_SCHOOL_REL, true ) );
			if ( ! $school_rel_id || 'publish' !== get_post_status( $school_rel_id ) ) {
				continue; // Skip programs not linked to a published school
			}

			if ( ! isset( $schools_data[ $school_rel_id ] ) ) {
				$school_name    = get_the_title( $school_rel_id );
				$school_logo    = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_rel_id, 'thumbnail' ) : '';
				$school_code    = get_post_meta( $school_rel_id, 'school_code', true ) ?: ( get_field( 'school_code', $school_rel_id ) ?: '' );
				$school_address = get_post_meta( $school_rel_id, 'address', true ) ?: ( get_field( 'address', $school_rel_id ) ?: '' );

				$schools_data[ $school_rel_id ] = [
					'id'       => $school_rel_id,
					'name'     => $school_name,
					'logo'     => $school_logo,
					'code'     => $school_code,
					'address'  => $school_address,
					'programs' => [],
				];
			}

			$status            = function_exists( 'ltdh_get_program_admission_status' ) 
				? ltdh_get_program_admission_status( $prog_id ) 
				: ( get_post_meta( $prog_id, LTDH_META_ADMISSION_STATUS, true ) ?: 'tuyen-sinh' );
			$types             = wp_get_post_terms( $prog_id, LTDH_TAX_TRAINING_TYPE );
			$type_name         = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';
			$clean_type_name   = preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) );
			$clean_major_name  = preg_replace( '/^ngành\s+/iu', '', trim( get_the_title( $major_id ) ) );
			$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );
			$permalink         = get_permalink( $prog_id );

			$badge_class = 'bg-blue-50 text-[#00308b]';

			$type_slug         = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->slug : '';
			$nganh_slug        = get_post_field( 'post_name', $major_id );

			$schools_data[ $school_rel_id ]['programs'][] = [
				'id'                => $prog_id,
				'status'            => $status,
				'type_name'         => $clean_type_name,
				'opportunity_title' => $opportunity_title,
				'badge_class'       => $badge_class,
				'permalink'         => $permalink,
				'slug'              => get_post_field( 'post_name', $prog_id ),
				'he'                => $type_slug,
				'nganh'             => $nganh_slug,
				'major_name'        => $clean_major_name,
				'school_name'       => $schools_data[ $school_rel_id ]['name'],
				'thumb'             => $schools_data[ $school_rel_id ]['logo'],
			];
		}
		wp_reset_postdata();
	}

	$is_single_school_mode = ! empty( $selected_school );

	if ( ! empty( $schools_data ) ) :
		echo '<div class="space-y-4">';
		foreach ( $schools_data as $school ) :
			$programs_count = count( $school['programs'] );

			$has_active   = false;
			$has_upcoming = false;
			foreach ( $school['programs'] as $p_chk ) {
				$p_status = $p_chk['status'] ?? 'tuyen-sinh';
				if ( 'sap-mo' === $p_status ) {
					$has_upcoming = true;
				} elseif ( 'tam-ngung' !== $p_status ) {
					$has_active = true;
				}
			}

			// Sort programs: active first
			$sorted_programs = $school['programs'];
			usort( $sorted_programs, function( $a, $b ) {
				$a_suspended = ( 'tam-ngung' === ( $a['status'] ?? '' ) );
				$b_suspended = ( 'tam-ngung' === ( $b['status'] ?? '' ) );
				if ( $a_suspended && ! $b_suspended ) {
					return 1;
				}
				if ( ! $a_suspended && $b_suspended ) {
					return -1;
				}
				return 0;
			} );
			?>
			<div class="ltdh-school-group bg-white border border-slate-200/90 hover:border-slate-300 rounded-2xl p-4 sm:p-5 transition-all shadow-xs hover:shadow-md">
				<!-- Top Row: School Info + Status Badge -->
				<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
					<div class="flex items-center gap-3.5 min-w-0 flex-1">
						<!-- School Logo -->
						<a href="<?php echo esc_url( get_permalink( $school['id'] ) ); ?>" class="w-12 h-12 rounded-xl bg-slate-50 p-1.5 flex items-center justify-center shrink-0 border border-slate-100 shadow-2xs hover:opacity-90 transition-opacity" title="<?php echo esc_attr( $school['name'] ); ?>">
							<?php if ( ! empty( $school['logo'] ) ) : ?>
								<img src="<?php echo esc_url( $school['logo'] ); ?>" alt="<?php echo esc_attr( $school['name'] ); ?>" class="w-full h-full object-contain" loading="lazy">
							<?php else : ?>
								<span class="text-xs font-black text-[#00308b]">ĐH</span>
							<?php endif; ?>
						</a>

						<!-- School Name & Address -->
						<div class="min-w-0 flex-1">
							<h3 class="text-sm sm:text-base font-extrabold text-slate-900 leading-snug">
								<a href="<?php echo esc_url( get_permalink( $school['id'] ) ); ?>" class="hover:text-[#00308b] transition-colors no-underline text-inherit">
									<?php echo esc_html( $school['name'] ); ?>
								</a>
								<?php if ( ! empty( $school['code'] ) ) : ?>
									<span class="inline-block ml-1.5 px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200/60 align-middle">
										<?php echo esc_html( $school['code'] ); ?>
									</span>
								<?php endif; ?>
							</h3>
							<?php if ( ! empty( $school['address'] ) ) : ?>
								<p class="text-xs text-slate-500 flex items-center gap-1.5 mt-1">
									<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
									</svg>
									<span class="truncate"><?php echo esc_html( $school['address'] ); ?></span>
								</p>
							<?php endif; ?>
						</div>
					</div>

					<!-- Status Badge (Top Right) -->
					<div class="shrink-0 self-start sm:self-center">
						<?php if ( $has_active ) : ?>
							<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-2xs select-none">
								<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
								<span>Đang tuyển sinh</span>
							</span>
						<?php elseif ( $has_upcoming ) : ?>
							<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/80 shadow-2xs select-none">
								<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
								<span>Sắp mở đợt mới</span>
							</span>
						<?php else : ?>
							<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200 shadow-2xs select-none">
								<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
								<span>Tạm dừng tuyển sinh</span>
							</span>
						<?php endif; ?>
					</div>
				</div>

				<!-- Bottom Row: Programs / Hệ đào tạo -->
				<div class="mt-3.5 pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
					<span class="text-xs font-medium text-slate-500 flex items-center gap-1.5 shrink-0">
						<svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
						</svg>
						Hệ đào tạo tuyển sinh:
					</span>

					<div class="flex items-center flex-wrap gap-2">
						<?php 
						$rendered_training_types = [];
						foreach ( $sorted_programs as $prog ) : 
							$type_key = ! empty( $prog['he'] ) ? $prog['he'] : ( ! empty( $prog['type_name'] ) ? sanitize_title( $prog['type_name'] ) : 'default' );
							if ( isset( $rendered_training_types[ $type_key ] ) ) {
								continue;
							}
							$rendered_training_types[ $type_key ] = true;

							$type_name_lower = mb_strtolower( trim( $prog['type_name'] ), 'UTF-8' );
							
							// Defaults (Chính quy hoặc khác)
							$icon_svg  = '<svg class="w-4 h-4 text-indigo-600 group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" /></svg>';
							$btn_style = 'bg-indigo-50/70 hover:bg-indigo-600 text-indigo-900 hover:text-white border-indigo-200/90 hover:border-indigo-600';

							if ( false !== strpos( $type_name_lower, 'từ xa' ) ) {
								$icon_svg  = '<svg class="w-4 h-4 text-[#00308b] group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>';
								$btn_style = 'bg-blue-50/80 hover:bg-[#00308b] text-[#00308b] hover:text-white border-blue-200/90 hover:border-[#00308b]';
							} elseif ( false !== strpos( $type_name_lower, 'vừa học vừa làm' ) || false !== strpos( $type_name_lower, 'vừa làm vừa học' ) || false !== strpos( $type_name_lower, 'vhvl' ) ) {
								$icon_svg  = '<svg class="w-4 h-4 text-amber-600 group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
								$btn_style = 'bg-amber-50/50 hover:bg-amber-600 text-amber-900 hover:text-white border-amber-200/80 hover:border-amber-600';
							}

							$raw_label     = $prog['type_name'] ? $prog['type_name'] : 'Xem chương trình';
							$display_label = ( 0 !== stripos( $raw_label, 'hệ' ) ) ? 'Hệ ' . $raw_label : $raw_label;
						?>
							<?php if ( 'tam-ngung' === $prog['status'] ) : ?>
								<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed">
									<span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
									<span><?php echo esc_html( $display_label ); ?> (Tạm ngưng)</span>
								</span>
							<?php else : ?>
								<a href="<?php echo esc_url( $prog['permalink'] ); ?>" 
								   class="group/btn inline-flex items-center gap-2 px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-bold transition-all border shadow-2xs hover:shadow-xs no-underline active:scale-[0.98] <?php echo esc_attr( $btn_style ); ?>" 
								   title="<?php echo esc_attr( $prog['opportunity_title'] ); ?>">
									<?php echo $icon_svg; ?>
									<span><?php echo esc_html( $display_label ); ?></span>
									<svg class="w-3.5 h-3.5 opacity-50 group-hover/btn:opacity-100 group-hover/btn:translate-x-0.5 transition-all shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
										<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
									</svg>
								</a>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<?php
		endforeach;
		echo '</div>';
	else :
		?>
		<div class="text-center py-12 bg-white border border-slate-200/80 rounded-2xl p-8 shadow-2xs w-full">
			<div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
				<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
				</svg>
			</div>
			<h3 class="font-bold text-slate-800 text-base mb-1">Không tìm thấy trường phù hợp</h3>
			<p class="text-slate-500 text-xs sm:text-sm max-w-md mx-auto">Vui lòng thử chọn hình thức đào tạo khác.</p>
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

/**
 * AJAX Handler for filtering majors on /nganh-hoc/ without page reload.
 */
add_action( 'wp_ajax_ltdh_filter_majors', 'ltdh_ajax_filter_majors' );
add_action( 'wp_ajax_nopriv_ltdh_filter_majors', 'ltdh_ajax_filter_majors' );

function ltdh_ajax_filter_majors() {
	$paged = isset( $_REQUEST['paged'] ) ? max( 1, intval( $_REQUEST['paged'] ) ) : 1;
	$limit = isset( $_REQUEST['limit'] ) ? intval( $_REQUEST['limit'] ) : 12;
	if ( ! in_array( $limit, [ 10, 12, 20, 24, 30, 36, 48, 50, 100, -1 ], true ) ) {
		$limit = 12;
	}

	$nhom_nganh = isset( $_REQUEST['nhom_nganh'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nhom_nganh'] ) ) : '';
	$search     = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
	$sort       = isset( $_REQUEST['sort'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sort'] ) ) : '';

	$args = [
		'post_type'      => LTDH_CPT_MAJOR,
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'paged'          => $paged,
	];

	if ( ! empty( $nhom_nganh ) ) {
		$args['tax_query'] = [
			[
				'taxonomy' => LTDH_TAX_MAJOR_CAT,
				'field'    => 'slug',
				'terms'    => $nhom_nganh,
			],
		];
	}

	if ( ! empty( $search ) ) {
		$args['s'] = $search;
	}

	if ( 'title_asc' === $sort ) {
		$args['orderby'] = 'title';
		$args['order']   = 'ASC';
	} elseif ( 'title_desc' === $sort ) {
		$args['orderby'] = 'title';
		$args['order']   = 'DESC';
	} elseif ( 'date_desc' === $sort ) {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	} else {
		$args['orderby'] = 'menu_order title';
		$args['order']   = 'ASC';
	}

	$query = new WP_Query( $args );

	$active_cat_term = $nhom_nganh ? get_term_by( 'slug', $nhom_nganh, LTDH_TAX_MAJOR_CAT ) : null;
	$heading_title   = $active_cat_term ? 'Nhóm ngành: ' . $active_cat_term->name : 'Danh sách Ngành đào tạo Liên thông Đại học';

	// Compute active filter items
	$active_filter_items = [];
	if ( ! empty( $search ) ) {
		$active_filter_items[] = [
			'label' => 'Từ khóa: "' . $search . '"',
			'key'   => 's',
		];
	}
	if ( ! empty( $nhom_nganh ) ) {
		$cat_label = $active_cat_term ? $active_cat_term->name : $nhom_nganh;
		$active_filter_items[] = [
			'label' => 'Nhóm: ' . $cat_label,
			'key'   => 'nhom_nganh',
		];
	}
	if ( ! empty( $sort ) ) {
		$sort_labels = [
			'title_asc'  => 'A-Z',
			'title_desc' => 'Z-A',
			'date_desc'  => 'Mới nhất',
		];
		$active_filter_items[] = [
			'label' => 'Xếp: ' . ( $sort_labels[ $sort ] ?? $sort ),
			'key'   => 'sort',
		];
	}
	if ( 12 !== $limit ) {
		$active_filter_items[] = [
			'label' => $limit . ' ngành/trang',
			'key'   => 'limit',
		];
	}
	$active_filter_count = count( $active_filter_items );
	$has_active_filters  = $active_filter_count > 0;
	$archive_link        = get_post_type_archive_link( LTDH_CPT_MAJOR ) ?: home_url( '/nganh-hoc/' );

	// Preserved query parameters for URLs
	$preserved_args = [];
	if ( ! empty( $nhom_nganh ) ) $preserved_args['nhom_nganh'] = $nhom_nganh;
	if ( ! empty( $search ) )     $preserved_args['s'] = $search;
	if ( ! empty( $sort ) )       $preserved_args['sort'] = $sort;
	if ( 12 !== $limit )          $preserved_args['limit'] = $limit;

	// Render Cards HTML
	ob_start();
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) : $query->the_post();
			get_template_part( 'template-parts/cards/card-major' );
		endwhile;
		wp_reset_postdata();
	else :
		?>
		<div class="col-span-full text-center py-16 bg-white border border-slate-200 rounded-2xl p-8 shadow-xs">
			<div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
				<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z" />
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
				</svg>
			</div>
			<h3 class="text-lg font-bold text-slate-800 mb-1">Không tìm thấy ngành học phù hợp</h3>
			<p class="text-slate-500 text-sm max-w-md mx-auto mb-6">Vui lòng thử thay đổi từ khóa tìm kiếm hoặc chọn lại nhóm ngành khác.</p>
			<a href="<?php echo esc_url( $archive_link ); ?>" class="js-ltdh-reset-filter inline-flex items-center gap-2 bg-brand-primary text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-brand-primary-dark transition-colors cursor-pointer">
				Xóa tất cả bộ lọc
			</a>
		</div>
		<?php
	endif;
	$cards_html = ob_get_clean();

	// Render Pagination HTML
	$pagination_html = '';
	if ( $query->max_num_pages > 1 ) {
		$pagination_base = add_query_arg( array_merge( $preserved_args, [ 'paged' => '%#%' ] ), $archive_link );

		$page_links = paginate_links( [
			'base'      => $pagination_base,
			'format'    => '',
			'total'     => $query->max_num_pages,
			'current'   => $paged,
			'mid_size'  => 2,
			'prev_text' => '← Trước',
			'next_text' => 'Sau →',
			'type'      => 'plain',
		] );

		if ( $page_links ) {
			$pagination_html = '<nav class="navigation pagination" aria-label="Bài viết"><h2 class="screen-reader-text">Điều hướng bài viết</h2><div class="nav-links">' . $page_links . '</div></nav>';
		}
	}

	// Render Chips HTML
	ob_start();
	if ( ! empty( $active_filter_items ) ) :
		?>
		<div class="flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden pt-2.5 pb-0.5">
			<?php foreach ( $active_filter_items as $item ) : 
				$remove_args = $preserved_args;
				unset( $remove_args[ $item['key'] ] );
				$remove_url = ! empty( $remove_args ) ? add_query_arg( $remove_args, $archive_link ) : $archive_link;
			?>
				<a href="<?php echo esc_url( $remove_url ); ?>" data-filter-key="<?php echo esc_attr( $item['key'] ); ?>" class="js-ltdh-chip-remove inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-semibold bg-blue-50 text-blue-900 border border-blue-200/60 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition-colors shrink-0 select-none">
					<span class="truncate max-w-[140px]"><?php echo esc_html( $item['label'] ); ?></span>
					<span class="text-blue-400 hover:text-rose-600 font-bold ml-0.5">✕</span>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	endif;
	$chips_html = ob_get_clean();

	wp_send_json_success( [
		'cards_html'          => $cards_html,
		'pagination_html'     => $pagination_html,
		'chips_html'          => $chips_html,
		'found_posts'         => $query->found_posts,
		'heading_title'       => $heading_title,
		'has_active_filters'  => $has_active_filters,
		'active_filter_count' => $active_filter_count,
		'active_cat'          => $nhom_nganh,
		'paged'               => $paged,
		'max_pages'           => $query->max_num_pages,
	] );
}

