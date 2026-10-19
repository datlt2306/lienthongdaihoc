<?php
/**
 * Taxonomy Training Type Archive Template (handles /he-dao-tao/)
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Fetch filter values from URL GET params
$selected_school = isset( $_GET['truong'] ) ? sanitize_text_field( $_GET['truong'] ) : '';
$selected_nhom   = isset( $_GET['nganh'] ) ? sanitize_text_field( $_GET['nganh'] ) : '';
if ( empty( $selected_nhom ) ) {
	$selected_nhom = isset( $_GET['nhom_nganh'] ) ? sanitize_text_field( $_GET['nhom_nganh'] ) : '';
}
$selected_type = '';
if ( is_tax( 'training_type' ) ) {
	$queried_term = get_queried_object();
	if ( $queried_term && ! empty( $queried_term->slug ) ) {
		$selected_type = $queried_term->slug;
	}
}
$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
if ( empty( $selected_type ) && preg_match( '#^/(?:hinh-thuc-dao-tao|he-dao-tao)/([^/]+)(?:/page/\d+)?/?$#i', $request_path, $m ) && 'page' !== $m[1] ) {
	$selected_type = sanitize_text_field( $m[1] );
}
if ( empty( $selected_type ) ) {
	$selected_type = isset( $_GET['he'] ) ? sanitize_text_field( $_GET['he'] ) : ( isset( $_GET['training_type'] ) ? sanitize_text_field( $_GET['training_type'] ) : '' );
}

$valid_limits   = [ 10, 12, 20, 24, 30, 36, 48, 50, 100, -1 ];
$selected_limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 12;
if ( ! in_array( $selected_limit, $valid_limits, true ) ) {
	$selected_limit = 12;
}

$selected_search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$selected_sort   = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : '';
$selected_region = isset( $_GET['khu_vuc'] ) ? sanitize_text_field( $_GET['khu_vuc'] ) : ( isset( $_GET['khu-vuc'] ) ? sanitize_text_field( $_GET['khu-vuc'] ) : '' );
$paged           = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );

$args = [
	'post_type'                   => 'program',
	'posts_per_page'              => $selected_limit,
	'paged'                       => $paged,
	'post_status'                 => 'publish',
	'meta_query'                  => [ 'relation' => 'AND' ],
	'tax_query'                   => [ 'relation' => 'AND' ],
	'sort_by_admission_priority'  => true, // Prioritize open programs before tam-ngung
];

if ( $selected_sort === 'title_asc' ) {
	$args['orderby'] = 'title';
	$args['order']   = 'ASC';
} elseif ( $selected_sort === 'title_desc' ) {
	$args['orderby'] = 'title';
	$args['order']   = 'DESC';
} elseif ( $selected_sort === 'date_desc' ) {
	$args['orderby'] = 'date';
	$args['order']   = 'DESC';
}

if ( ! empty( $selected_search ) ) {
	$args['s'] = $selected_search;
}

// Filter by region / campus
if ( ! empty( $selected_region ) ) {
	$loc_program_ids  = ltdh_get_program_ids_by_location( $selected_region );
	$args['post__in'] = $loc_program_ids;
}

if ( ! empty( $selected_school ) ) {
	if ( ! is_numeric( $selected_school ) ) {
		$school_post = get_page_by_path( $selected_school, OBJECT, 'school' );
		$school_id   = $school_post ? $school_post->ID : 0;
	} else {
		$school_id = intval( $selected_school );
	}
	if ( $school_id ) {
		$args['meta_query'][] = [
			'key'     => 'school_relationship',
			'value'   => $school_id,
			'compare' => '=',
		];
	}
}

// Filter by chuyên ngành / nhóm ngành
if ( ! empty( $selected_nhom ) ) {
	$major_post = get_page_by_path( $selected_nhom, OBJECT, 'major' );
	if ( $major_post ) {
		$args['meta_query'][] = [
			'key'     => 'major_relationship',
			'value'   => $major_post->ID,
			'compare' => '=',
		];
	} else {
		$majors_in_cat = get_posts( [
			'post_type'   => 'major',
			'numberposts' => -1,
			'fields'      => 'ids',
			'tax_query'   => [ [ 'taxonomy' => 'major_cat', 'field' => 'slug', 'terms' => $selected_nhom ] ],
		] );
		if ( ! empty( $majors_in_cat ) ) {
			$args['meta_query'][] = [
				'key'     => 'major_relationship',
				'value'   => $majors_in_cat,
				'compare' => 'IN',
			];
		}
	}
}

if ( ! empty( $selected_type ) ) {
	$type_terms = ( 'tu-xa' === $selected_type || 'dao-tao-tu-xa' === $selected_type ) ? [ 'dao-tao-tu-xa', 'tu-xa' ] : $selected_type;
	$args['tax_query'][] = [
		'taxonomy' => 'training_type',
		'field'    => 'slug',
		'terms'    => $type_terms,
	];
} else {
	$args['tax_query'][] = [
		'taxonomy' => 'training_type',
		'field'    => 'slug',
		'terms'    => [ 'chinh-quy', 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ],
	];
}

$args  = apply_filters( 'pre_get_posts_args_ltdh', $args );
$query = new WP_Query( $args );

// Training types counts from DB
global $wpdb;
$t_results = $wpdb->get_results( "
	SELECT t.slug, COUNT(p.ID) as count
	FROM {$wpdb->posts} p
	INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
	INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
	INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
	WHERE p.post_type = 'program' AND p.post_status = 'publish' AND tt.taxonomy = 'training_type'
	GROUP BY t.slug
" );
$t_counts = [];
if ( ! is_wp_error( $t_results ) && ! empty( $t_results ) ) {
	foreach ( $t_results as $row ) {
		$t_counts[ $row->slug ] = intval( $row->count );
	}
}
if ( isset( $t_counts['dao-tao-tu-xa'] ) && ! isset( $t_counts['tu-xa'] ) ) {
	$t_counts['tu-xa'] = $t_counts['dao-tao-tu-xa'];
}

$allowed_training_types = [ 'chinh-quy', 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ];
$all_types             = get_terms( [
	'taxonomy'   => 'training_type',
	'slug'       => $allowed_training_types,
	'hide_empty' => false,
] );
if ( ! is_wp_error( $all_types ) && is_array( $all_types ) ) {
	$all_types = array_values( array_filter( $all_types, function( $t ) use ( $allowed_training_types ) {
		return in_array( $t->slug, $allowed_training_types, true );
	} ) );
}
$total_programs   = wp_count_posts( 'program' )->publish;
$active_type_term = $selected_type ? get_term_by( 'slug', $selected_type, 'training_type' ) : null;

// School list for dropdown
$schools_list = get_posts( [
	'post_type'      => 'school',
	'numberposts'    => -1,
	'post_status'    => 'publish',
	'orderby'        => 'title',
	'order'          => 'ASC',
] );

// Major list for dropdown
$majors_list = get_posts( [
	'post_type'      => 'major',
	'numberposts'    => -1,
	'post_status'    => 'publish',
	'orderby'        => 'title',
	'order'          => 'ASC',
] );

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
$all_region_terms = get_terms( [
	'taxonomy'   => [ 'campus', 'region' ],
	'hide_empty' => false,
] );
if ( ! is_wp_error( $all_region_terms ) && ! empty( $all_region_terms ) ) {
	foreach ( $all_region_terms as $r_term ) {
		if ( ! isset( $default_regions[ $r_term->slug ] ) ) {
			$default_regions[ $r_term->slug ] = $r_term->name;
		}
	}
}

// Compute active filter items for badge counter and mobile chips
$active_filter_items = [];
if ( ! empty( $selected_search ) ) {
	$active_filter_items[] = [
		'label' => 'Từ khóa: "' . $selected_search . '"',
		'key'   => 's',
	];
}
if ( ! empty( $selected_region ) ) {
	$r_label = $default_regions[ $selected_region ] ?? $selected_region;
	$active_filter_items[] = [
		'label' => 'Khu vực: ' . $r_label,
		'key'   => 'khu_vuc',
	];
}
if ( ! empty( $selected_school ) ) {
	$sch_name = '';
	if ( ! empty( $schools_list ) ) {
		foreach ( $schools_list as $sch ) {
			if ( $selected_school === $sch->post_name || $selected_school === (string) $sch->ID ) {
				$sch_name = $sch->post_title;
				break;
			}
		}
	}
	$active_filter_items[] = [
		'label' => $sch_name ? 'Trường: ' . $sch_name : 'Trường: ' . $selected_school,
		'key'   => 'truong',
	];
}
if ( ! empty( $selected_nhom ) ) {
	$maj_name = '';
	if ( ! empty( $majors_list ) ) {
		foreach ( $majors_list as $maj ) {
			if ( $selected_nhom === $maj->post_name ) {
				$maj_name = $maj->post_title;
				break;
			}
		}
	}
	$active_filter_items[] = [
		'label' => $maj_name ? 'Ngành: ' . $maj_name : 'Ngành: ' . $selected_nhom,
		'key'   => 'nganh',
	];
}
if ( ! empty( $selected_type ) ) {
	$t_name = $active_type_term ? $active_type_term->name : $selected_type;
	$active_filter_items[] = [
		'label' => 'Hệ: ' . $t_name,
		'key'   => 'he',
	];
}
if ( ! empty( $selected_sort ) ) {
	$sort_labels = [
		'title_asc'  => 'A-Z',
		'title_desc' => 'Z-A',
		'date_desc'  => 'Mới nhất',
	];
	$active_filter_items[] = [
		'label' => 'Xếp: ' . ( $sort_labels[ $selected_sort ] ?? $selected_sort ),
		'key'   => 'sort',
	];
}
$active_filter_count = count( $active_filter_items );
$has_active_filters  = $active_filter_count > 0;
?>

<main id="primary" class="site-main bg-slate-50 min-h-screen">
	<?php get_template_part( 'template-parts/banner' ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

		<!-- ============ TOP FILTER BAR (FULL-WIDTH) ============ -->
		<div class="bg-white border border-slate-100 rounded-2xl p-4 sm:p-5 md:p-6 shadow-sm mb-8 transition-all">
			
			<!-- Header inside Filter: Page Title, Found Counter & Desktop Reset Action -->
			<div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 sm:pb-5 border-b border-slate-100 gap-3">
				<div>
					<h2 id="catalog-heading-title" class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
						<?php echo $active_type_term ? 'Liên thông ' . esc_html( preg_replace( '/^(hệ|liên\s+thông)\s+/iu', '', $active_type_term->name ) ) : 'Chương trình tuyển sinh Liên thông Đại học'; ?>
					</h2>
					<p class="text-xs md:text-sm text-slate-500 mt-1">
						Tìm thấy <strong id="catalog-count-display" class="text-brand-primary font-extrabold text-sm md:text-base"><?php echo esc_html( $query->found_posts ); ?></strong> chương trình phù hợp.
					</p>
				</div>
				<div id="catalog-desktop-reset" class="hidden sm:block">
					<?php if ( $has_active_filters ) : ?>
						<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" class="js-ltdh-reset-filter inline-flex items-center gap-1.5 text-xs md:text-sm font-bold text-rose-600 hover:text-rose-700 bg-rose-50/80 hover:bg-rose-100/80 border border-rose-100 px-3.5 py-2 rounded-lg transition-colors min-h-[40px]">
							<span>✕</span> <span>Xóa tất cả bộ lọc</span>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- Mobile Filter Bar: Toggle Trigger & Active Filter Chips (Visible only on < md) -->
			<div class="md:hidden mt-3">
				<div class="flex items-center gap-2">
					<button type="button" 
							id="ltdh-mobile-filter-btn" 
							aria-expanded="false" 
							aria-controls="catalog-filter-body"
							class="flex-1 flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl border font-bold text-xs sm:text-sm transition-all duration-200 min-h-[44px] cursor-pointer shadow-2xs <?php echo $has_active_filters ? 'bg-blue-50/90 border-blue-200/90 text-[#00308b]' : 'bg-slate-50 hover:bg-slate-100 border-slate-200/80 text-slate-700'; ?>">
						<div class="flex items-center gap-2 min-w-0">
							<svg class="w-4 h-4 text-current shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
							</svg>
							<span id="ltdh-mobile-filter-btn-text">Bộ lọc &amp; Tìm kiếm</span>
							<span id="catalog-mobile-filter-badge">
								<?php if ( $active_filter_count > 0 ) : ?>
									<span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[11px] font-black rounded-full bg-[#EA580C] text-white shrink-0 min-w-[20px] shadow-2xs">
										<?php echo esc_html( $active_filter_count ); ?>
									</span>
								<?php endif; ?>
							</span>
						</div>
						<svg id="ltdh-mobile-filter-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
						</svg>
					</button>

					<div id="catalog-mobile-reset">
						<?php if ( $has_active_filters ) : ?>
							<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" 
							   class="js-ltdh-reset-filter flex items-center justify-center gap-1 px-3.5 py-2.5 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100 min-h-[44px] transition-colors shrink-0"
							   title="Xóa tất cả bộ lọc">
								<span>✕ Xóa lọc</span>
							</a>
						<?php endif; ?>
					</div>
				</div>

				<!-- Active Filter Chips (Scrollable row) -->
				<div id="catalog-mobile-chips">
					<?php if ( ! empty( $active_filter_items ) ) : ?>
						<div class="flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden pt-2.5 pb-0.5">
							<?php foreach ( $active_filter_items as $item ) : 
								$remove_url = remove_query_arg( $item['key'] );
								if ( 'he' === $item['key'] ) {
									$remove_url = home_url( '/hinh-thuc-dao-tao/' );
									if ( ! empty( $preserved_args ) ) {
										$remove_url = add_query_arg( $preserved_args, $remove_url );
									}
								}
							?>
								<a href="<?php echo esc_url( $remove_url ); ?>" data-filter-key="<?php echo esc_attr( $item['key'] ); ?>" class="js-ltdh-chip-remove inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-semibold bg-blue-50 text-blue-900 border border-blue-200/60 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition-colors shrink-0 select-none">
									<span class="truncate max-w-[140px]"><?php echo esc_html( $item['label'] ); ?></span>
									<span class="text-blue-400 hover:text-rose-600 font-bold ml-0.5">✕</span>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Filter Form Controls (Collapsible on mobile, fully visible on desktop) -->
			<div id="catalog-filter-body" class="hidden md:block pt-4 md:pt-0">
				<?php
				$form_action = home_url( '/hinh-thuc-dao-tao/' );
				if ( $active_type_term ) {
					$t_link = get_term_link( $active_type_term );
					if ( ! is_wp_error( $t_link ) && ! empty( $t_link ) ) {
						$form_action = $t_link;
					} else {
						$form_action = home_url( '/hinh-thuc-dao-tao/' . $active_type_term->slug . '/' );
					}
				}
				?>
				<form id="catalog-filter-form" action="<?php echo esc_url( $form_action ); ?>" method="GET" class="space-y-4">
					<input type="hidden" name="he" value="<?php echo esc_attr( $selected_type ); ?>">
				
				<!-- Row 1: Search keyword + Region dropdown + School dropdown + Major dropdown + Sorting -->
				<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4">
					
					<!-- 1. Search Box -->
					<div class="relative sm:col-span-2 md:col-span-1">
						<span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
						</span>
						<input type="text" 
							   name="s" 
							   value="<?php echo esc_attr( $selected_search ); ?>" 
							   placeholder="Tìm tên ngành, trường..." 
							   class="w-full pl-10 pr-4 py-2.5 border border-slate-200/60 rounded-xl text-sm focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none placeholder-slate-400 min-h-[44px] transition-all bg-slate-50/30 focus:bg-white">
					</div>

					<!-- 2. Region / Location Select -->
					<div>
						<select name="khu_vuc" class="w-full py-2.5 px-3.5 border border-slate-200/60 rounded-xl text-sm font-medium bg-slate-50/30 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
							<option value="">Tất cả khu vực</option>
							<?php
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
							$all_region_terms = get_terms( [
								'taxonomy'   => [ 'campus', 'region' ],
								'hide_empty' => false,
							] );
							if ( ! is_wp_error( $all_region_terms ) && ! empty( $all_region_terms ) ) {
								foreach ( $all_region_terms as $r_term ) {
									if ( ! isset( $default_regions[ $r_term->slug ] ) ) {
										$default_regions[ $r_term->slug ] = $r_term->name;
									}
								}
							}
							foreach ( $default_regions as $r_slug => $r_name ) :
							?>
								<option value="<?php echo esc_attr( $r_slug ); ?>" <?php selected( $selected_region, $r_slug ); ?>>
									<?php echo esc_html( $r_name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- 2. School Select -->
					<div>
						<select name="truong" class="w-full py-2.5 px-3.5 border border-slate-200/60 rounded-xl text-sm font-medium bg-slate-50/30 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
							<option value="">Tất cả trường đối tác</option>
							<?php if ( ! empty( $schools_list ) ) : ?>
								<?php foreach ( $schools_list as $sch ) : ?>
									<?php 
									$is_sch_selected = ( $selected_school === $sch->post_name || $selected_school === (string) $sch->ID );
									?>
									<option value="<?php echo esc_attr( $sch->post_name ); ?>" <?php selected( $is_sch_selected, true ); ?>>
										<?php echo esc_html( $sch->post_title ); ?>
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>

					<!-- 3. Major Select -->
					<div>
						<select name="nganh" class="w-full py-2.5 px-3.5 border border-slate-200/60 rounded-xl text-sm font-medium bg-slate-50/30 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
							<option value="">Tất cả chuyên ngành</option>
							<?php if ( ! empty( $majors_list ) ) : ?>
								<?php foreach ( $majors_list as $maj ) : ?>
									<option value="<?php echo esc_attr( $maj->post_name ); ?>" <?php selected( $selected_nhom, $maj->post_name ); ?>>
										<?php echo esc_html( $maj->post_title ); ?>
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>

					<!-- 4. Sorting Select -->
					<div>
						<select name="sort" class="w-full py-2.5 px-3.5 border border-slate-200/60 rounded-xl text-sm font-medium bg-slate-50/30 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 focus:outline-none cursor-pointer min-h-[44px] shadow-2xs">
							<option value="" <?php selected( $selected_sort, '' ); ?>>Sắp xếp: Mặc định</option>
							<option value="title_asc" <?php selected( $selected_sort, 'title_asc' ); ?>>Tên chương trình (A-Z)</option>
							<option value="title_desc" <?php selected( $selected_sort, 'title_desc' ); ?>>Tên chương trình (Z-A)</option>
							<option value="date_desc" <?php selected( $selected_sort, 'date_desc' ); ?>>Mới nhất</option>
						</select>
					</div>

				</div>

				<!-- Row 2: Quick Pill Tabs for Training Types -->
				<div id="catalog-pills-container" class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2">
					<span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 shrink-0">Hình thức đào tạo:</span>
					
					<!-- All Systems Tab -->
					<?php
					$is_all_active = empty( $selected_type );
					$all_url = home_url( '/hinh-thuc-dao-tao/' );
					$preserved_args = [];
					if ( $selected_school ) $preserved_args['truong'] = $selected_school;
					if ( $selected_nhom )   $preserved_args['nganh']  = $selected_nhom;
					if ( $selected_search ) $preserved_args['s']      = $selected_search;
					if ( $selected_sort )   $preserved_args['sort']   = $selected_sort;
					if ( ! empty( $preserved_args ) ) {
						$all_url = add_query_arg( $preserved_args, $all_url );
					}
					?>
					<a href="<?php echo esc_url( $all_url ); ?>"
					   data-type=""
					   class="js-ltdh-type-pill inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs md:text-sm font-bold transition-all min-h-[34px] <?php echo $is_all_active ? 'bg-brand-primary text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'; ?>">
						<span>Tất cả</span>
						<span class="pill-badge text-[11px] px-1.5 py-0.2 rounded-full font-black <?php echo $is_all_active ? 'bg-white/20 text-white' : 'bg-white text-slate-600 border border-slate-200/60'; ?>">
							<?php echo esc_html( $total_programs ); ?>
						</span>
					</a>

					<!-- Dynamic Training Types Tabs (Hides systems with 0 count) -->
					<?php if ( ! is_wp_error( $all_types ) && ! empty( $all_types ) ) : ?>
						<?php foreach ( $all_types as $t_term ) : ?>
							<?php
							$t_count = $t_counts[ $t_term->slug ] ?? ( $t_counts['tu-xa'] ?? 0 );
							$is_active = ( $selected_type === $t_term->slug || ( ( 'tu-xa' === $selected_type || 'dao-tao-tu-xa' === $selected_type ) && ( 'tu-xa' === $t_term->slug || 'dao-tao-tu-xa' === $t_term->slug ) ) );

							// Hide training types with 0 programs unless currently active
							if ( $t_count === 0 && ! $is_active ) {
								continue;
							}

							$t_link   = get_term_link( $t_term );
							$type_url = ( ! is_wp_error( $t_link ) && ! empty( $t_link ) ) ? $t_link : home_url( '/hinh-thuc-dao-tao/' . $t_term->slug . '/' );
							if ( ! empty( $preserved_args ) ) {
								$type_url = add_query_arg( $preserved_args, $type_url );
							}
							?>
							<a href="<?php echo esc_url( $type_url ); ?>"
							   data-type="<?php echo esc_attr( $t_term->slug ); ?>"
							   class="js-ltdh-type-pill inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs md:text-sm font-bold transition-all min-h-[34px] <?php echo $is_active ? 'bg-brand-primary text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'; ?>">
								<span><?php echo esc_html( $t_term->name ); ?></span>
								<span class="pill-badge text-[11px] px-1.5 py-0.2 rounded-full font-black <?php echo $is_active ? 'bg-white/20 text-white' : 'bg-white text-slate-600 border border-slate-200/60'; ?>">
									<?php echo esc_html( $t_count ); ?>
								</span>
							</a>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<!-- Mobile Action Buttons (Visible inside open filter on < md) -->
				<div class="pt-3 border-t border-slate-100 flex items-center gap-2 md:hidden">
					<button type="submit" class="flex-1 py-3 px-4 rounded-xl bg-[#00308b] hover:bg-blue-900 text-white font-extrabold text-sm shadow-sm transition-all flex items-center justify-center gap-2 min-h-[46px] cursor-pointer">
						<svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
						<span>Áp dụng bộ lọc (<?php echo esc_html( $query->found_posts ); ?>)</span>
					</button>
					<button type="button" id="ltdh-mobile-filter-close" class="py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm min-h-[46px] transition-colors cursor-pointer">
						Đóng
					</button>
				</div>

			</form>
		</div><!-- #catalog-filter-body -->

		<script>
		(function() {
			function initMobileFilter() {
				var btn = document.getElementById('ltdh-mobile-filter-btn');
				var body = document.getElementById('catalog-filter-body');
				var chevron = document.getElementById('ltdh-mobile-filter-chevron');
				var btnText = document.getElementById('ltdh-mobile-filter-btn-text');
				var closeBtn = document.getElementById('ltdh-mobile-filter-close');
				if (!btn || !body || btn._bound) return;
				btn._bound = true;

				function toggleFilter() {
					var isHidden = body.classList.contains('hidden');
					if (isHidden) {
						body.classList.remove('hidden');
						btn.setAttribute('aria-expanded', 'true');
						if (chevron) chevron.classList.add('rotate-180');
						if (btnText) btnText.textContent = 'Thu gọn bộ lọc';
					} else {
						body.classList.add('hidden');
						btn.setAttribute('aria-expanded', 'false');
						if (chevron) chevron.classList.remove('rotate-180');
						if (btnText) btnText.textContent = 'Bộ lọc & Tìm kiếm';
					}
				}

				btn.addEventListener('click', toggleFilter);
				if (closeBtn) {
					closeBtn.addEventListener('click', toggleFilter);
				}
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', initMobileFilter);
			} else {
				initMobileFilter();
			}
		})();
		</script>
	</div>

		<!-- ============ PROGRAM RESULTS GRID (2 COLUMNS ON MOBILE, 3 ON DESKTOP) ============ -->
		<div id="program-results-wrapper" class="relative">
			<div id="program-results-container" class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
			<?php
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
					$type_slug        = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->slug : '';
					$clean_type_name     = preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) );
					$card_headline       = function_exists( 'ltdh_format_program_card_headline' )
						? ltdh_format_program_card_headline( get_the_title( $prog_id ), $school_code, $school_name, $clean_major_name )
						: ( 'Liên thông ngành ' . $clean_major_name . ( $school_code ? ' - ' . $school_code : ( $school_name ? ' - ' . $school_name : '' ) ) );
					$admission_status    = function_exists( 'ltdh_get_program_admission_status' ) 
						? ltdh_get_program_admission_status( $prog_id ) 
						: ( get_post_meta( $prog_id, 'admission_status', true ) ?: 'tuyen-sinh' );

					// Badge classes based on training type (Unified brand navy badge across all training types)
					$badge_class = 'bg-[#00308b] text-white shadow-md border border-white/20';

					$school_thumb    = $school_rel_id && function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_rel_id, 'medium' ) : get_stylesheet_directory_uri() . '/assets/images/banner-school.jpg';
					$learning_details = ltdh_get_program_learning_details( $prog_id );
					$school_logo_id   = $school_rel_id ? ltdh_get_school_image_id( $school_rel_id ) : 0;
					$school_logo_url  = $school_rel_id && function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_rel_id, 'thumbnail' ) : get_stylesheet_directory_uri() . '/assets/images/cropped-logo-scaled-2.webp';
					$tuition          = get_field( 'tuition_fee', $prog_id ) ?: 'Liên hệ';
					$duration         = function_exists( 'ltdh_get_program_duration' ) ? ltdh_get_program_duration( $prog_id ) : ( get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm' );
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
				<!-- Empty State -->
				<div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-16 bg-white border border-slate-200 rounded-2xl p-8 shadow-xs w-full">
					<span class="text-5xl block mb-4">🔍</span>
					<h3 class="font-extrabold text-slate-900 text-lg mb-2">Không tìm thấy chương trình phù hợp</h3>
					<p class="text-slate-500 text-sm max-w-md mx-auto mb-6">Không có chương trình nào khớp với các tiêu chí lọc bạn đang chọn. Hãy thử chọn lại hoặc đặt lại bộ lọc.</p>
					<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
						<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" class="bg-brand-primary text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-brand-darkBlue shadow-md min-h-[44px] flex items-center justify-center">
							✕ Đặt lại tất cả bộ lọc
						</a>
					</div>
				</div>
			<?php
			endif;
			?>
			</div><!-- #program-results-container -->
		</div><!-- #program-results-wrapper -->

		<!-- Pagination -->
		<div id="catalog-pagination-container" class="mt-12 flex justify-center theme-pagination">
			<?php if ( $query->max_num_pages > 1 ) : ?>
				<?php
				echo paginate_links( [
					'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
					'format'    => '?paged=%#%',
					'current'   => $paged,
					'total'     => $query->max_num_pages,
					'prev_text' => '← Trước',
					'next_text' => 'Sau →',
				] );
				?>
			<?php endif; ?>
		</div>

	</div>
</main>

<?php
get_footer();
