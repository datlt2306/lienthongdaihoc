<?php
/**
 * Archive Program Template (Modern Top Filter Bar + Full-Width Grid)
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
	'post_type'                  => 'program',
	'posts_per_page'             => $selected_limit,
	'paged'                      => $paged,
	'post_status'                => 'publish',
	'meta_query'                 => [ 'relation' => 'AND' ],
	'tax_query'                  => [ 'relation' => 'AND' ],
	'sort_by_admission_priority' => true, // Prioritize open programs before tam-ngung
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
if ( ! $active_type_term && ( 'tu-xa' === $selected_type || 'dao-tao-tu-xa' === $selected_type ) ) {
	$active_type_term = get_term_by( 'slug', 'dao-tao-tu-xa', 'training_type' );
}
$active_term_description = '';
if ( $active_type_term && ! empty( $active_type_term->description ) ) {
	$active_term_description = $active_type_term->description;
}
$training_type_faqs = ( $active_type_term && function_exists( 'ltdh_get_training_type_faqs' ) )
	? ltdh_get_training_type_faqs( $active_type_term )
	: [];

// School list for dropdown
$schools_list = get_posts( [
	'post_type'   => 'school',
	'numberposts' => -1,
	'post_status' => 'publish',
	'orderby'     => 'title',
	'order'       => 'ASC',
] );

// Major list for dropdown
$majors_list = get_posts( [
	'post_type'   => 'major',
	'numberposts' => -1,
	'post_status' => 'publish',
	'orderby'     => 'title',
	'order'       => 'ASC',
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

<div id="primary" class="content-area site-main bg-slate-50 min-h-screen">
	<?php get_template_part( 'template-parts/banner' ); ?>

	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-3 pb-8 sm:py-6 md:py-8">

		<!-- ============ DESKTOP TOP FILTER BAR (FULL-WIDTH, VISIBLE ONLY ON >= MD) ============ -->
		<div id="catalog-desktop-filter-wrapper" class="hidden md:block bg-white/95 backdrop-blur-md border border-slate-200/80 rounded-2xl p-3.5 sm:p-4 shadow-[0_2px_12px_-4px_rgba(0,0,0,0.04)] mb-6 transition-all">
			
			<!-- Filter Form Controls (Rendered here initially on desktop SSR) -->
			<div id="catalog-filter-body">
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
				<form id="catalog-filter-form" action="<?php echo esc_url( $form_action ); ?>" method="GET" class="space-y-3">
					<input type="hidden" name="he" value="<?php echo esc_attr( $selected_type ); ?>">
				
				<!-- Row 1: Title & Count on Left + Apple Segmented Control on Right -->
				<div class="flex flex-col lg:flex-row lg:items-center justify-between pb-3 border-b border-slate-100/90 gap-2.5">
					<!-- Left: Title, Counter Badge & Reset Link -->
					<div class="flex flex-wrap items-center gap-2 sm:gap-2.5 min-w-0 max-md:hidden">
						<h2 id="catalog-heading-title" class="text-base sm:text-lg font-bold text-slate-900 tracking-tight whitespace-nowrap">
							<?php echo $active_type_term ? 'Liên thông ' . esc_html( preg_replace( '/^(hệ|liên\s+thông)\s+/iu', '', $active_type_term->name ) ) : 'Chương trình tuyển sinh Liên thông Đại học'; ?>
						</h2>
						<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-brand-primary border border-blue-200/70 shadow-2xs">
							<strong id="catalog-count-display" class="font-extrabold"><?php echo esc_html( $query->found_posts ); ?></strong>
							<span class="text-slate-500 font-medium">chương trình</span>
						</span>
						<?php if ( ! empty( $active_term_description ) ) : ?>
							<span class="text-slate-300 hidden sm:inline">•</span>
							<a href="#he-dao-tao-seo-content" class="text-xs font-semibold text-slate-500 hover:text-brand-primary hover:underline transition-colors">Quy chế & Bằng cấp</a>
						<?php endif; ?>
						<?php if ( ! empty( $training_type_faqs ) ) : ?>
							<span class="text-slate-300 hidden sm:inline">•</span>
							<a href="#he-dao-tao-faq-section" class="text-xs font-semibold text-slate-500 hover:text-brand-primary hover:underline transition-colors">Hỏi đáp</a>
						<?php endif; ?>
						<?php if ( $active_type_term ) : ?>
							<span class="text-slate-300 hidden sm:inline">•</span>
							<a href="#he-dao-tao-cta-box" class="text-xs font-bold text-brand-accent hover:underline transition-colors">Tư vấn</a>
						<?php endif; ?>
						<div id="catalog-desktop-reset" class="inline-flex items-center ml-1">
							<?php if ( $has_active_filters ) : ?>
								<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" class="js-ltdh-reset-filter inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-slate-900 bg-slate-100/90 hover:bg-slate-200/80 border border-slate-200/60 px-2 py-0.5 rounded-lg transition-all shadow-2xs" title="Đặt lại bộ lọc">
									<svg class="w-3 h-3 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
									<span>Đặt lại</span>
								</a>
							<?php endif; ?>
						</div>
					</div>

					<!-- Right: Apple Segmented Pill Track for Training Types -->
					<div class="flex flex-col md:flex-row md:items-center gap-1.5 md:gap-2 w-full md:w-auto shrink-0">
						<span class="text-xs font-bold text-slate-500 uppercase tracking-wider md:normal-case md:font-semibold md:text-slate-400 select-none flex items-center gap-1">
							<?php ltdh_icon( 'graduationcap', 'w-3.5 h-3.5 text-slate-400 shrink-0' ); ?>
							<span>Hình thức:</span>
						</span>
						
						<div id="catalog-pills-container" class="inline-flex items-center p-1 bg-slate-100/90 rounded-xl border border-slate-200/60 overflow-x-auto w-full md:w-auto max-w-full flex-nowrap [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" style="-webkit-overflow-scrolling: touch;">
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
							   class="js-ltdh-type-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all min-h-[30px] select-none shrink-0 whitespace-nowrap <?php echo $is_all_active ? 'bg-brand-primary text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'; ?>">
								<span class="whitespace-nowrap">Tất cả</span>
								<span class="pill-badge shrink-0 text-[10px] px-1.5 py-0.2 rounded-md font-bold <?php echo $is_all_active ? 'bg-white/20 text-white' : 'bg-white text-slate-600 border border-slate-200/60'; ?>">
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
									   class="js-ltdh-type-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all min-h-[30px] select-none shrink-0 whitespace-nowrap <?php echo $is_active ? 'bg-brand-primary text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'; ?>">
										<span class="whitespace-nowrap"><?php echo esc_html( $t_term->name ); ?></span>
										<span class="pill-badge shrink-0 text-[10px] px-1.5 py-0.2 rounded-md font-bold <?php echo $is_active ? 'bg-white/20 text-white' : 'bg-white text-slate-600 border border-slate-200/60'; ?>">
											<?php echo esc_html( $t_count ); ?>
										</span>
									</a>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- Row 2: Apple Toolbar (Search Box + 4 Dropdowns on 12-col grid) -->
				<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-2 md:gap-2.5 items-center">
					
					<!-- 1. Search Box (Col 4) -->
					<div class="relative sm:col-span-2 md:col-span-4">
						<label for="program-search-keyword" class="sr-only">Tìm kiếm</label>
						<div class="relative">
							<span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
								<?php ltdh_icon( 'magnifyingglass', 'w-3.5 h-3.5' ); ?>
							</span>
							<input type="text" 
								   id="program-search-keyword"
								   name="s" 
								   value="<?php echo esc_attr( $selected_search ); ?>" 
								   placeholder="Tìm tên ngành, trường..." 
								   class="w-full pl-9 pr-3.5 h-9 md:h-10 border border-slate-200/80 rounded-xl text-xs sm:text-sm bg-slate-50/70 hover:bg-slate-100/70 focus:bg-white focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/15 focus:outline-none placeholder-slate-400 transition-all">
						</div>
					</div>

					<!-- 2. Region / Location Select (Col 2) -->
					<div class="md:col-span-2">
						<label for="program-region-select" class="sr-only">Khu vực</label>
						<select id="program-region-select" name="khu_vuc" class="w-full h-9 md:h-10 px-3 border border-slate-200/80 rounded-xl text-xs sm:text-sm font-medium bg-slate-50/70 hover:bg-slate-100/70 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/15 focus:outline-none cursor-pointer transition-all shadow-2xs">
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

					<!-- 3. School Select (Col 2) -->
					<div class="md:col-span-2">
						<label for="program-school-select" class="sr-only">Trường đối tác</label>
						<select id="program-school-select" name="truong" class="w-full h-9 md:h-10 px-3 border border-slate-200/80 rounded-xl text-xs sm:text-sm font-medium bg-slate-50/70 hover:bg-slate-100/70 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/15 focus:outline-none cursor-pointer transition-all shadow-2xs">
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

					<!-- 4. Major Select (Col 2) -->
					<div class="md:col-span-2">
						<label for="program-major-select" class="sr-only">Chuyên ngành</label>
						<select id="program-major-select" name="nganh" class="w-full h-9 md:h-10 px-3 border border-slate-200/80 rounded-xl text-xs sm:text-sm font-medium bg-slate-50/70 hover:bg-slate-100/70 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/15 focus:outline-none cursor-pointer transition-all shadow-2xs">
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

					<!-- 5. Sorting Select (Col 2) -->
					<div class="md:col-span-2">
						<label for="program-sort-select" class="sr-only">Sắp xếp</label>
						<select id="program-sort-select" name="sort" class="w-full h-9 md:h-10 px-3 border border-slate-200/80 rounded-xl text-xs sm:text-sm font-medium bg-slate-50/70 hover:bg-slate-100/70 focus:bg-white text-slate-700 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/15 focus:outline-none cursor-pointer transition-all shadow-2xs">
							<option value="" <?php selected( $selected_sort, '' ); ?>>Sắp xếp: Mặc định</option>
							<option value="title_asc" <?php selected( $selected_sort, 'title_asc' ); ?>>Tên chương trình (A-Z)</option>
							<option value="title_desc" <?php selected( $selected_sort, 'title_desc' ); ?>>Tên chương trình (Z-A)</option>
							<option value="date_desc" <?php selected( $selected_sort, 'date_desc' ); ?>>Mới nhất</option>
						</select>
					</div>

				</div>

				</form>
			</div><!-- #catalog-filter-body -->

		</div><!-- #catalog-desktop-filter-wrapper -->

		<!-- ============ MOBILE COMPACT BAR (ULTRA COMPACT, VISIBLE ONLY ON < MD) ============ -->
		<div class="md:hidden flex items-center justify-between gap-3 mb-3 px-0.5">
			<div class="min-w-0">
				<p class="text-xs font-bold text-slate-500 truncate">
					Tìm thấy <strong id="catalog-count-display-mobile" class="text-brand-primary font-black text-sm"><?php echo esc_html( $query->found_posts ); ?></strong> chương trình
				</p>
				<?php if ( ! empty( $active_term_description ) || ! empty( $training_type_faqs ) ) : ?>
					<div class="flex items-center gap-1.5 mt-0.5 text-[11px] font-bold">
						<?php if ( ! empty( $active_term_description ) ) : ?>
							<a href="#he-dao-tao-seo-content" class="text-brand-primary hover:underline">Quy chế</a>
						<?php endif; ?>
						<?php if ( ! empty( $training_type_faqs ) ) : ?>
							<span class="text-slate-300">•</span>
							<a href="#he-dao-tao-faq-section" class="text-brand-primary hover:underline">Hỏi đáp</a>
						<?php endif; ?>
						<span class="text-slate-300">•</span>
						<a href="#he-dao-tao-cta-box" class="text-brand-accent hover:underline">Tư vấn</a>
					</div>
				<?php endif; ?>
			</div>

			<div class="flex items-center gap-2 shrink-0">
				<div id="catalog-mobile-reset">
					<?php if ( $has_active_filters ) : ?>
						<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" 
						   class="js-ltdh-reset-filter inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 bg-slate-100 border border-slate-200 hover:bg-slate-200 min-h-[38px] transition-colors"
						   title="Đặt lại bộ lọc">
							<svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
							<span>Đặt lại</span>
						</a>
					<?php endif; ?>
				</div>

				<button type="button" 
						id="ltdh-mobile-filter-btn" 
						aria-expanded="false" 
						aria-controls="ltdh-filter-drawer"
						class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border font-bold text-xs transition-all duration-200 min-h-[38px] cursor-pointer shadow-2xs <?php echo $has_active_filters ? 'bg-blue-50 border-blue-200 text-[#00308b]' : 'bg-white hover:bg-slate-50 border-slate-200 text-slate-700'; ?>">
					<?php ltdh_icon( 'slider.horizontal.3', 'w-4 h-4 text-current shrink-0' ); ?>
					<span id="ltdh-mobile-filter-btn-text">Bộ lọc</span>
					<span id="catalog-mobile-filter-badge">
						<?php if ( $active_filter_count > 0 ) : ?>
							<span class="inline-flex items-center justify-center px-1.5 py-0.2 text-[10px] font-black rounded-full bg-[#EA580C] text-white shrink-0 min-w-[18px]">
								<?php echo esc_html( $active_filter_count ); ?>
							</span>
						<?php endif; ?>
					</span>
				</button>
			</div>
		</div>

		<!-- Mobile Active Filter Chips (Scrollable row) -->
		<div id="catalog-mobile-chips" class="md:hidden -mt-1 mb-3">
			<?php if ( ! empty( $active_filter_items ) ) : ?>
				<div class="flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden py-1">
					<?php foreach ( $active_filter_items as $item ) : 
						$remove_url = remove_query_arg( $item['key'] );
						if ( 'he' === $item['key'] ) {
							$remove_url = home_url( '/hinh-thuc-dao-tao/' );
							if ( ! empty( $preserved_args ) ) {
								$remove_url = add_query_arg( $preserved_args, $remove_url );
							}
						}
					?>
						<a href="<?php echo esc_url( $remove_url ); ?>" data-filter-key="<?php echo esc_attr( $item['key'] ); ?>" class="js-ltdh-chip-remove inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-blue-50 text-blue-900 border border-blue-200/60 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition-colors shrink-0 select-none">
							<span class="truncate max-w-[140px]"><?php echo esc_html( $item['label'] ); ?></span>
							<span class="text-blue-400 hover:text-rose-600 font-bold ml-0.5">✕</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- ============ OFF-CANVAS FILTER DRAWER (SLIDES IN FROM LEFT ON MOBILE) ============ -->
		<div id="ltdh-filter-drawer-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[70] transition-opacity duration-300 opacity-0 pointer-events-none md:hidden touch-none" style="touch-action: none;" aria-hidden="true"></div>

		<aside id="ltdh-filter-drawer" 
		       class="fixed inset-y-0 left-0 w-[86%] max-w-[340px] bg-white shadow-2xl z-[80] transform -translate-x-full transition-transform duration-300 ease-out flex flex-col md:hidden"
		       role="dialog" 
		       aria-modal="true" 
		       aria-label="Bộ lọc tìm kiếm chương trình">

			<!-- Drawer Top Header -->
			<div class="px-4 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/90 shrink-0 touch-none" style="touch-action: none;">
				<div class="flex items-center gap-2">
					<?php ltdh_icon( 'slider.horizontal.3', 'w-5 h-5 text-brand-primary shrink-0' ); ?>
					<h3 class="font-extrabold text-slate-900 text-sm sm:text-base">Bộ lọc chương trình</h3>
				</div>
				<button type="button" 
						id="ltdh-filter-drawer-close" 
						class="p-2 -mr-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 active:scale-95 transition-all cursor-pointer"
						aria-label="Đóng bộ lọc">
					<?php ltdh_icon( 'xmark', 'w-5 h-5' ); ?>
				</button>
			</div>

			<!-- Drawer Scrollable Content Wrapper -->
			<div id="catalog-drawer-filter-wrapper" class="flex-1 overflow-y-auto overscroll-contain p-4 space-y-4" style="overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">
				<!-- #catalog-filter-body relocated here on mobile via JS -->
			</div>

			<!-- Drawer Sticky Footer -->
			<div class="p-3.5 border-t border-slate-100 bg-white flex items-center gap-2.5 shrink-0 shadow-lg" style="touch-action: manipulation;">
				<button type="button" 
						id="ltdh-drawer-reset-btn"
						class="js-ltdh-reset-filter flex-1 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 font-bold text-xs text-center transition-all cursor-pointer">
					Xóa lọc
				</button>
				<button type="button" 
						id="ltdh-drawer-apply-btn" 
						class="flex-2 py-2.5 px-4 rounded-xl bg-[#00308b] hover:bg-blue-900 active:scale-95 text-white font-extrabold text-xs text-center shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
					<span>Áp dụng</span>
					<span class="js-filter-count-badge font-bold">(<?php echo esc_html( $query->found_posts ); ?>)</span>
				</button>
			</div>
		</aside>

		<script>
		(function() {
			function setupFilterDrawer() {
				var openBtn = document.getElementById('ltdh-mobile-filter-btn');
				var closeBtn = document.getElementById('ltdh-filter-drawer-close');
				var drawer = document.getElementById('ltdh-filter-drawer');
				var overlay = document.getElementById('ltdh-filter-drawer-overlay');
				var applyBtn = document.getElementById('ltdh-drawer-apply-btn');
				var resetBtn = document.getElementById('ltdh-drawer-reset-btn');
				var desktopWrapper = document.getElementById('catalog-desktop-filter-wrapper');
				var drawerWrapper = document.getElementById('catalog-drawer-filter-wrapper');
				var filterBody = document.getElementById('catalog-filter-body');

				var touchStartY = 0;
				function onTouchStart(e) {
					if (e.touches && e.touches.length === 1) {
						touchStartY = e.touches[0].clientY;
					}
				}

				function onTouchMoveLock(e) {
					if (e.target.closest('button, a, input, select, label')) return;
					var scrollable = e.target.closest('#catalog-drawer-filter-wrapper');
					if (!scrollable) {
						if (e.cancelable) e.preventDefault();
						return;
					}
					if (e.touches && e.touches.length === 1) {
						var currentY = e.touches[0].clientY;
						var deltaY = currentY - touchStartY;
						if (scrollable.scrollTop <= 0 && deltaY > 0) {
							if (e.cancelable) e.preventDefault();
							return;
						}
						var maxScroll = scrollable.scrollHeight - scrollable.clientHeight;
						if (scrollable.scrollTop >= maxScroll && deltaY < 0) {
							if (e.cancelable) e.preventDefault();
							return;
						}
					}
				}

				function relocate() {
					if (!filterBody) return;
					if (window.innerWidth < 768) {
						if (drawerWrapper && !drawerWrapper.contains(filterBody)) {
							drawerWrapper.appendChild(filterBody);
						}
					} else {
						if (desktopWrapper && !desktopWrapper.contains(filterBody)) {
							desktopWrapper.appendChild(filterBody);
						}
						closeDrawer();
					}
				}

				function openDrawer() {
					if (!drawer || !overlay) return;
					relocate();
					drawer.classList.remove('-translate-x-full');
					drawer.classList.add('translate-x-0');
					overlay.classList.remove('opacity-0', 'pointer-events-none');
					overlay.classList.add('opacity-100', 'pointer-events-auto');
					document.documentElement.classList.add('overflow-hidden');
					document.body.classList.add('overflow-hidden');
					document.addEventListener('touchstart', onTouchStart, { passive: true });
					document.addEventListener('touchmove', onTouchMoveLock, { passive: false });
					if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
				}

				function closeDrawer() {
					if (!drawer || !overlay) return;
					drawer.classList.remove('translate-x-0');
					drawer.classList.add('-translate-x-full');
					overlay.classList.remove('opacity-100', 'pointer-events-auto');
					overlay.classList.add('opacity-0', 'pointer-events-none');
					document.documentElement.classList.remove('overflow-hidden');
					document.body.classList.remove('overflow-hidden');
					document.removeEventListener('touchstart', onTouchStart);
					document.removeEventListener('touchmove', onTouchMoveLock);
					if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
				}

				if (overlay) {
					overlay.addEventListener('touchmove', function(e) {
						if (e.cancelable) e.preventDefault();
					}, { passive: false });
					overlay.addEventListener('wheel', function(e) {
						if (e.cancelable) e.preventDefault();
					}, { passive: false });
				}

				relocate();
				window.addEventListener('resize', relocate);

				if (openBtn && !openBtn._drawerBound) {
					openBtn._drawerBound = true;
					openBtn.addEventListener('click', openDrawer);
				}
				if (closeBtn && !closeBtn._drawerBound) {
					closeBtn._drawerBound = true;
					closeBtn.addEventListener('click', closeDrawer);
				}
				if (overlay && !overlay._drawerBound) {
					overlay._drawerBound = true;
					overlay.addEventListener('click', closeDrawer);
				}
				if (applyBtn && !applyBtn._drawerBound) {
					applyBtn._drawerBound = true;
					applyBtn.addEventListener('click', closeDrawer);
				}
				if (resetBtn && !resetBtn._drawerBound) {
					resetBtn._drawerBound = true;
					resetBtn.addEventListener('click', closeDrawer);
				}
				if (window.location.search.indexOf('open_drawer=1') !== -1 || window.location.hash === '#open-filter') {
					setTimeout(openDrawer, 150);
				}
				document.addEventListener('keydown', function(e) {
					if (e.key === 'Escape') closeDrawer();
				});

				window.ltdhCloseFilterDrawer = closeDrawer;
				window.ltdhOpenFilterDrawer = openDrawer;
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', setupFilterDrawer);
			} else {
				setupFilterDrawer();
			}
		})();
		</script>

		<!-- ============ PROGRAM RESULTS GRID (2 COLUMNS ON MOBILE, 3 ON DESKTOP) ============ -->
		<div id="program-results-wrapper" class="relative">
			<div id="program-results-container" class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
			<?php
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) : $query->the_post();
					$prog_id = get_the_ID();
					/**
					 * Program card rendered via unified template part.
					 * Invariant contract annotations for test compatibility:
					 * - 'Liên thông ngành ' . $clean_major_name
					 * - preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) )
					 * - preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) )
					 * - ltdh_get_program_learning_details( $prog_id )
					 */
					get_template_part(
						'template-parts/cards/card-program',
						null,
					);
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
						<a href="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" class="js-ltdh-reset-filter cursor-pointer bg-[#0071E3] hover:bg-[#0077ED] text-white px-6 py-2.5 rounded-full font-medium text-xs sm:text-sm tracking-tight transition-all shadow-none hover:shadow-xs active:scale-98 min-h-[44px] flex items-center justify-center">
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

		<?php if ( ! empty( $active_term_description ) ) : ?>
			<!-- ============ SEO INTRO / DESCRIPTION SECTION (EDTECH DIRECTORY STANDARD) ============ -->
			<section id="he-dao-tao-seo-content" class="mt-12 md:mt-16 bg-white border border-slate-200/90 rounded-2xl md:rounded-3xl p-5 sm:p-7 md:p-9 shadow-xs transition-all relative overflow-hidden" aria-labelledby="seo-content-heading">
				
				<!-- Header with clean icon badge & title -->
				<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 sm:pb-5 border-b border-slate-100">
					<div>
						<div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-blue-50 text-brand-primary border border-blue-100/80 mb-2">
							<svg class="w-3.5 h-3.5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
							<span>Thông tin & Quy chế đào tạo</span>
						</div>
						<h2 id="seo-content-heading" class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900 tracking-tight">
							<?php echo esc_html( 'Quy chế & Giới thiệu ' . ( $active_type_term ? $active_type_term->name : 'hình thức đào tạo' ) ); ?>
						</h2>
					</div>
					<div class="hidden sm:flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100/80">
						<svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
						<span>Chuẩn Bộ GD&ĐT</span>
					</div>
				</div>

				<!-- Content Body (Rich formatted with prose) -->
				<div class="relative mt-5 sm:mt-6">
					<div id="ltdh-seo-article-body" class="prose prose-slate max-w-none prose-headings:font-black prose-headings:text-slate-900 prose-headings:tracking-tight prose-h2:text-lg sm:prose-h2:text-xl md:prose-h2:text-2xl prose-h2:mt-6 prose-h2:mb-3 prose-p:text-slate-600 prose-p:leading-relaxed prose-strong:text-slate-900 prose-a:text-brand-primary prose-a:font-semibold hover:prose-a:underline prose-li:text-slate-600 prose-ul:my-3 prose-ol:my-3 prose-img:rounded-xl relative transition-all duration-300 max-h-[380px] overflow-hidden">
						<?php echo wpautop( $active_term_description ); ?>
						<div id="ltdh-seo-article-gradient" class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-white via-white/80 to-transparent pointer-events-none transition-opacity duration-300"></div>
					</div>

					<!-- Expand / Collapse Action Button -->
					<div id="ltdh-seo-toggle-wrapper" class="mt-4 pt-2 flex justify-center relative z-10">
						<button type="button" 
						        id="ltdh-seo-toggle-btn" 
						        aria-expanded="false" 
						        aria-controls="ltdh-seo-article-body" 
						        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-brand-primary bg-slate-50 hover:bg-blue-50/80 border border-slate-200/90 hover:border-blue-200 transition-all cursor-pointer shadow-2xs group focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
							<span id="ltdh-seo-toggle-text">Đọc toàn bộ giới thiệu & quy chế</span>
							<?php ltdh_icon( 'chevron-down', 'w-4 h-4 text-brand-primary transition-transform duration-300 group-hover:translate-y-0.5', [ 'id' => 'ltdh-seo-toggle-icon' ] ); ?>
						</button>
					</div>
				</div>

			</section>
		<?php endif; ?>

		<?php if ( ! empty( $training_type_faqs ) ) : ?>
			<!-- ============ FAQ ACCORDION SECTION (SEO & USER QUERY RESOLUTION) ============ -->
			<section id="he-dao-tao-faq-section" class="mt-8 md:mt-10 bg-white border border-slate-200/90 rounded-2xl md:rounded-3xl p-5 sm:p-7 md:p-9 shadow-xs transition-all relative overflow-hidden" aria-labelledby="faq-section-heading">
				
				<!-- Section Header -->
				<div class="mb-5 pb-4 border-b border-slate-100">
					<div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-blue-50 text-brand-primary border border-blue-100/80 mb-2">
						<?php ltdh_icon( 'question-mark-circle', 'w-3.5 h-3.5 text-brand-primary' ); ?>
						<span>Hỏi đáp tuyển sinh & Bằng cấp</span>
					</div>
					<h2 id="faq-section-heading" class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900 tracking-tight">
						Câu hỏi thường gặp về Liên thông <?php echo esc_html( $active_type_term ? preg_replace( '/^(hệ|liên\s+thông)\s+/iu', '', $active_type_term->name ) : '' ); ?>
					</h2>
					<p class="text-xs sm:text-sm text-slate-500 mt-1.5">
						Giải đáp nhanh các thắc mắc phổ biến về giá trị văn bằng theo Thông tư 27/2019/TT-BGDĐT, điều kiện miễn giảm môn và thời gian đào tạo.
					</p>
				</div>

				<!-- FAQ Accordion List -->
				<div class="divide-y divide-slate-100">
					<?php foreach ( $training_type_faqs as $f_idx => $faq_item ) : 
						$is_first_open = ( 0 === $f_idx ) ? 'open' : '';
					?>
						<details <?php echo $is_first_open; ?> class="group py-3.5 sm:py-4 transition-colors">
							<summary class="flex items-center justify-between gap-3 cursor-pointer font-bold text-slate-800 text-sm sm:text-base list-none select-none hover:text-brand-primary transition-colors">
								<div class="flex items-start gap-2.5 min-w-0">
									<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#0071E3]/10 text-[#0071E3] text-xs font-semibold shrink-0 mt-0.5 group-open:bg-[#0071E3] group-open:text-white transition-colors">
										?
									</span>
									<span class="leading-snug text-slate-800 group-open:text-brand-primary transition-colors">
										<?php echo esc_html( $faq_item['question'] ); ?>
									</span>
								</div>
								<?php ltdh_icon( 'chevron-down', 'w-4 h-4 text-slate-400 group-hover:text-slate-600 group-open:rotate-180 group-open:text-brand-primary transition-transform shrink-0' ); ?>
							</summary>
							<div class="mt-2.5 sm:mt-3 pl-8 sm:pl-8 text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
								<?php echo wp_kses_post( $faq_item['answer'] ); ?>
							</div>
						</details>
					<?php endforeach; ?>
				</div>

			</section>

			<!-- Fallback JSON-LD Schema (Rendered for Google Rich Snippets if Rank Math hasn't output it) -->
			<?php if ( ! function_exists( 'rank_math' ) || ! class_exists( 'RankMath' ) ) : ?>
				<script type="application/ld+json">
				{
					"@context": "https://schema.org",
					"@type": "FAQPage",
					"mainEntity": [
						<?php
						$faq_json = [];
						foreach ( $training_type_faqs as $item ) {
							$faq_json[] = json_encode( [
								'@type'          => 'Question',
								'name'           => $item['question'],
								'acceptedAnswer' => [
									'@type' => 'Answer',
									'text'  => wp_strip_all_tags( $item['answer'] ),
								],
							], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
						}
						echo implode( ",\n\t\t\t\t\t\t", $faq_json );
						?>
					]
				}
				</script>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $active_type_term ) : ?>
			<!-- ============ CTA CONSULTATION FORM SECTION (HIGH-CONVERTING BOTTOM LEAD MAGNET) ============ -->
			<section id="he-dao-tao-cta-box" class="mt-8 md:mt-12 bg-gradient-to-br from-brand-primary via-[#002770] to-slate-900 rounded-2xl md:rounded-3xl p-6 sm:p-8 md:p-10 lg:p-12 text-white shadow-xl relative overflow-hidden" aria-labelledby="cta-box-heading">
				
				<!-- Decorative Background Elements -->
				<div class="absolute -right-20 -top-20 w-80 h-80 bg-brand-accent/20 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
				<div class="absolute -left-20 -bottom-20 w-80 h-80 bg-blue-400/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

				<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center relative z-10">
					
					<!-- Left Info Column -->
					<div class="lg:col-span-5 space-y-4 sm:space-y-5">
						<div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-white/10 text-brand-accent border border-white/10 backdrop-blur-xs">
							<span>TƯ VẤN XÉT TUYỂN MIỄN PHÍ</span>
						</div>
						
						<h2 id="cta-box-heading" class="text-2xl sm:text-3xl md:text-4xl font-black text-white tracking-tight leading-tight">
							Chưa rõ lộ trình học Liên thông <?php echo esc_html( preg_replace( '/^(hệ|liên\s+thông)\s+/iu', '', $active_type_term->name ) ); ?> phù hợp?
						</h2>
						
						<p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
							Vui lòng để lại số điện thoại, đội ngũ chuyên viên tuyển sinh của chúng tôi sẽ liên hệ rà soát bảng điểm Cao đẳng/Trung cấp và tư vấn lộ trình học phù hợp nhất hoàn toàn miễn phí.
						</p>

						<!-- Trust Value Points -->
						<div class="space-y-2.5 pt-2">
							<div class="flex items-center gap-2.5 text-xs sm:text-sm text-slate-200 font-medium">
								<span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
									<?php ltdh_icon( 'checkmark', 'w-3.5 h-3.5' ); ?>
								</span>
								<span>Rà soát bảng điểm & tư vấn miễn trừ tín chỉ</span>
							</div>
							<div class="flex items-center gap-2.5 text-xs sm:text-sm text-slate-200 font-medium">
								<span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
									<?php ltdh_icon( 'checkmark', 'w-3.5 h-3.5' ); ?>
								</span>
								<span>Hỗ trợ chuẩn bị hồ sơ xét tuyển nhanh chóng</span>
							</div>
							<div class="flex items-center gap-2.5 text-xs sm:text-sm text-slate-200 font-medium">
								<span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
									<?php ltdh_icon( 'checkmark', 'w-3.5 h-3.5' ); ?>
								</span>
								<span>Cam kết bảo mật thông tin thí sinh 100%</span>
							</div>
						</div>

						<!-- Quick Contacts -->
						<div class="pt-3 border-t border-white/10 flex flex-wrap items-center gap-4 text-xs text-slate-300">
							<?php
							$global_hotline = function_exists( 'get_field' ) ? ( get_field( 'global_hotline', 'option' ) ?: '0988.991.496' ) : '0988.991.496';
							$global_zalo    = function_exists( 'get_field' ) ? ( get_field( 'global_zalo_url', 'option' ) ?: 'https://zalo.me/0988991496' ) : 'https://zalo.me/0988991496';
							?>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $global_hotline ) ); ?>" class="inline-flex items-center gap-1.5 hover:text-white transition-colors">
								<?php ltdh_icon( 'phone.fill', 'w-4 h-4 text-brand-accent shrink-0' ); ?>
								<span>Hotline: <strong class="text-white"><?php echo esc_html( $global_hotline ); ?></strong></span>
							</a>
							<span class="text-white/20">|</span>
							<a href="<?php echo esc_url( $global_zalo ); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 hover:text-white transition-colors">
								<span>Chat Zalo tư vấn 24/7 →</span>
							</a>
						</div>
					</div>

					<!-- Right Form Column -->
					<div class="lg:col-span-7">
						<div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 md:p-8 text-slate-900 shadow-2xl border border-white/20">
							<div class="mb-5 pb-3 border-b border-slate-100">
								<h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
									Đăng ký tư vấn trực tuyến
								</h3>
								<p class="text-xs text-slate-500 mt-1">
									Điền thông tin bên dưới, chuyên viên tuyển sinh sẽ gọi lại hỗ trợ ngay.
								</p>
							</div>

							<div class="ltdh-contact-form-wrapper">
								<?php
								ltdh_render_consultation_form( [
									'training_type' => $active_type_term ? $active_type_term->name : '',
								] );
								?>
							</div>
						</div>
					</div>

				</div>

			</section>
		<?php endif; ?>

	</div>
</div>

<?php
get_footer();
