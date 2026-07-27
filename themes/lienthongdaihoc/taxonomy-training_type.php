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
$selected_school   = isset( $_GET['truong'] ) ? sanitize_text_field( $_GET['truong'] ) : '';
$selected_nhom     = isset( $_GET['nganh'] ) ? sanitize_text_field( $_GET['nganh'] ) : '';
if ( empty( $selected_nhom ) ) {
	$selected_nhom = isset( $_GET['nhom_nganh'] ) ? sanitize_text_field( $_GET['nhom_nganh'] ) : '';
}
$selected_type     = '';
$request_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
if ( preg_match( '#^/he-dao-tao/([^/]+)/?$#i', $request_path, $m ) ) {
	$selected_type = sanitize_text_field( $m[1] );
}
if ( empty( $selected_type ) ) {
	$selected_type = isset( $_GET['he'] ) ? sanitize_text_field( $_GET['he'] ) : '';
}

$valid_limits = [ 10, 12, 20, 24, 30, 36, 48, 50, 100, -1 ];
$selected_limit    = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 12;
if ( ! in_array( $selected_limit, $valid_limits, true ) ) {
	$selected_limit = 12;
}

$selected_search   = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$selected_sort     = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : '';

$args = [
	'post_type'      => 'program',
	'posts_per_page' => $selected_limit,
	'post_status'    => 'publish',
	'meta_query'     => [
		'relation' => 'AND',
		[
			'relation' => 'OR',
			[
				'key'     => 'admission_status',
				'value'   => 'tam-ngung',
				'compare' => '!=',
			],
			[
				'key'     => 'admission_status',
				'compare' => 'NOT EXISTS',
			],
		]
	],
	'tax_query' => [ 'relation' => 'AND' ],
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

if ( ! empty( $selected_school ) ) {
	if ( ! is_numeric( $selected_school ) ) {
		$school_post = get_page_by_path( $selected_school, OBJECT, 'school' );
		$school_id   = $school_post ? $school_post->ID : 0;
	} else {
		$school_id = intval( $selected_school );
	}
	$args['meta_query'][] = [
		'key'     => 'school_relationship',
		'value'   => $school_id,
		'compare' => '=',
	];
}

// Filter by nhóm ngành: get all major IDs in that category, then filter programs
if ( ! empty( $selected_nhom ) ) {
	// Check if this is a major slug (filter by major_relationship)
	$major_post = get_page_by_path( $selected_nhom, OBJECT, 'major' );
	if ( $major_post ) {
		$args['meta_query'][] = [
			'key'     => 'major_relationship',
			'value'   => $major_post->ID,
			'compare' => '=',
		];
	} else {
		// Fallback: treat as major_cat taxonomy slug
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
	$args['tax_query'][] = [
		'taxonomy' => 'training_type',
		'field'    => 'slug',
		'terms'    => $selected_type,
	];
}

$args  = apply_filters( 'pre_get_posts_args_ltdh', $args );
$query = new WP_Query( $args );

// Count for "Tất cả các ngành"
$all_majors_args              = $args;
$all_majors_args['posts_per_page'] = -1;
$all_majors_args['fields']         = 'ids';
$all_majors_args['meta_query']     = array_filter( $all_majors_args['meta_query'], function ( $clause ) {
	return ! ( isset( $clause['key'] ) && 'major_relationship' === $clause['key'] );
} );
$all_majors_query = new WP_Query( $all_majors_args );
$all_majors_count = $all_majors_query->found_posts;
wp_reset_postdata();

// Sidebar data
$major_cats       = get_terms( [ 'taxonomy' => 'major_cat', 'hide_empty' => false ] );
$total_programs   = wp_count_posts( 'program' )->publish;
$active_type_term = $selected_type ? get_term_by( 'slug', $selected_type, 'training_type' ) : null;
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

		<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

			<!-- ============ SIDEBAR ============ -->
			<details id="sidebar-filters" class="lg:col-span-1 lg:block bg-white lg:bg-transparent border border-slate-200 lg:border-0 rounded-xl lg:rounded-none shadow-xs lg:shadow-none mb-6 lg:mb-0 group overflow-hidden" open>
				<summary class="flex lg:hidden items-center justify-between p-4 cursor-pointer font-bold text-slate-800 list-none [&::-webkit-details-marker]:hidden select-none min-h-[44px]">
					<span class="flex items-center gap-2">
						<span>⚙️</span> <span>Bộ lọc tìm kiếm</span>
					</span>
					<span class="text-slate-400 group-open:rotate-180 transition-transform duration-200">▾</span>
				</summary>
				<div class="p-4 lg:p-0 pt-0 lg:pt-0 border-t lg:border-0 border-slate-100 space-y-6 sticky top-24">
					<?php
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

					$m_counts = [];

					// Pre-filter programs for current parameters
					$major_filter_args = [
						'post_type'      => 'program',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
					];
					if ( ! empty( $selected_type ) ) {
						$major_filter_args['tax_query'] = [
							[
								'taxonomy' => 'training_type',
								'field'    => 'slug',
								'terms'    => $selected_type,
							]
						];
					}
					if ( ! empty( $selected_search ) ) {
						$major_filter_args['s'] = $selected_search;
					}
					$filtered_sidebar_programs = get_posts( $major_filter_args );
					if ( ! empty( $filtered_sidebar_programs ) ) {
						// Prime meta cache to avoid N+1 queries in the loop
						update_meta_cache( 'post', $filtered_sidebar_programs );
						foreach ( $filtered_sidebar_programs as $p_id ) {
							$major_rel = get_post_meta( $p_id, 'major_relationship', true );
							if ( is_array( $major_rel ) ) {
								$major_rel = ! empty( $major_rel ) ? $major_rel[0] : 0;
							}
							$major_rel = intval( $major_rel );
							if ( $major_rel ) {
								$m_counts[ $major_rel ] = ( $m_counts[ $major_rel ] ?? 0 ) + 1;
							}
						}
					}
					?>

					<!-- Search Box -->
					<div class="bg-white border border-slate-200 rounded-lg p-4 shadow-sm">
						<form action="<?php echo esc_url( $selected_type ? home_url( '/he-dao-tao/' . $selected_type . '/' ) : home_url( '/he-dao-tao/tu-xa/' ) ); ?>" method="GET">
							<input type="text" name="s" value="<?php echo esc_attr( $selected_search ); ?>" placeholder="Tìm kiếm chương trình..." class="w-full border border-slate-200 rounded-lg px-3 py-3 text-sm focus:border-brand-primary focus:outline-none placeholder-slate-400 min-h-[44px]">
						</form>
					</div>

					<!-- Hệ đào tạo Filter -->
					<div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
						<h3 class="font-extrabold text-slate-900 text-base mb-4 border-b border-slate-100 pb-3 uppercase tracking-wider">Hệ đào tạo</h3>
						<ul class="space-y-1">
							<li>
								<a href="<?php echo esc_url( home_url( '/he-dao-tao/' ) ); ?>"
								   class="flex items-center justify-between px-3 py-3 rounded-lg text-sm font-semibold transition-all <?php echo empty( $selected_type ) ? 'bg-brand-primary text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[44px]">
									<span>Tất cả hệ học</span>
									<span class="text-xs px-2 py-0.5 rounded-full font-bold <?php echo empty( $selected_type ) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'; ?>">
										<?php echo esc_html( $total_programs ); ?>
									</span>
								</a>
							</li>
							<?php
							$all_types = get_terms( [ 'taxonomy' => 'training_type', 'hide_empty' => false ] );
							if ( ! is_wp_error( $all_types ) && ! empty( $all_types ) ) :
								foreach ( $all_types as $t_term ) :
									$is_active = ( $selected_type === $t_term->slug );
									$t_count = $t_counts[ $t_term->slug ] ?? 0;
							?>
									<li>
										<a href="<?php echo esc_url( home_url( '/he-dao-tao/' . $t_term->slug . '/' ) ); ?>"
										   class="flex items-center justify-between px-3 py-3 rounded-lg text-sm font-semibold transition-all <?php echo $is_active ? 'bg-brand-primary text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[44px]">
											<span><?php echo esc_html( $t_term->name ); ?></span>
											<span class="text-xs px-2 py-0.5 rounded-full font-bold <?php echo $is_active ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'; ?>">
												<?php echo esc_html( $t_count ); ?>
											</span>
										</a>
									</li>
								<?php
								endforeach;
							endif;
							?>
						</ul>
					</div>

					<!-- Ngành học Filter -->
					<div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
						<h3 class="font-extrabold text-slate-950 text-base mb-4 border-b border-slate-100 pb-3 uppercase tracking-wider">Chuyên ngành</h3>
						
						<!-- Instant Search Filter -->
						<div class="mb-3 relative">
							<input type="text" id="major-search-filter" placeholder="Tìm nhanh chuyên ngành..." class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:border-brand-primary focus:outline-none placeholder-slate-400 min-h-[36px]">
						</div>

						<div class="pr-1 space-y-1" id="major-list-container" style="max-height: 480px; overflow-y: auto; scrollbar-width: thin;">
							<ul class="space-y-1">
								<li data-search="tất cả các ngành">
									<a href="<?php echo esc_url( remove_query_arg( 'nganh' ) ); ?>"
									   class="flex items-center justify-between px-3 py-3 rounded-lg text-sm font-semibold transition-all <?php echo empty( $selected_nhom ) ? 'bg-brand-primary text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[44px]">
										<span>Tất cả các ngành</span>
										<span class="text-xs px-2 py-0.5 rounded-full font-bold <?php echo empty( $selected_nhom ) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'; ?>">
											<?php echo esc_html( $all_majors_count ); ?>
										</span>
									</a>
								</li>
								<?php
								$all_majors_list = get_posts( [ 'post_type' => 'major', 'numberposts' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ] );
								if ( ! empty( $all_majors_list ) ) :
									foreach ( $all_majors_list as $maj ) :
										$is_active = ( $selected_nhom === $maj->post_name );
										$prog_count = $m_counts[ $maj->ID ] ?? 0;
								?>
										<li data-search="<?php echo esc_attr( mb_strtolower( $maj->post_title, 'UTF-8' ) ); ?>">
											<a href="<?php echo esc_url( add_query_arg( 'nganh', $maj->post_name, remove_query_arg( 'nganh' ) ) ); ?>"
											   class="flex items-center justify-between px-3 py-3 rounded-lg text-sm font-semibold transition-all <?php echo $is_active ? 'bg-brand-primary text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[44px]">
												<span><?php echo esc_html( $maj->post_title ); ?></span>
												<span class="text-xs px-2 py-0.5 rounded-full font-bold <?php echo $is_active ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'; ?>">
													<?php echo esc_html( $prog_count ); ?>
												</span>
											</a>
										</li>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
					</div>

				</div>
			</details>

			<!-- ============ MAIN CONTENT ============ -->
			<div class="lg:col-span-3">

				<!-- Header bar -->
				<div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6 pb-4 border-b border-slate-200 gap-4">
					<div>
						<?php if ( $active_type_term ) : ?>
							<p class="text-base font-extrabold text-slate-800 mb-0.5">
								Hệ <?php echo esc_html( $active_type_term->name ); ?>
							</p>
						<?php endif; ?>
						<p class="text-sm text-slate-500">
							Tìm thấy <strong><?php echo esc_html( $query->found_posts ); ?></strong> chương trình học phù hợp.
						</p>
					</div>
					
					<div class="flex flex-row flex-wrap items-center gap-3 justify-start lg:justify-end w-full lg:w-auto">
						<div class="flex items-center gap-2">
							<label for="sort-select" class="text-[11px] font-bold text-slate-500 uppercase tracking-wider shrink-0">Sắp xếp:</label>
							<select id="sort-select" class="rounded-lg border-slate-300 text-sm py-2 px-3 bg-white text-slate-700 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary cursor-pointer shadow-sm min-h-[44px]" onchange="location = this.value;">
								<option value="<?php echo esc_url( remove_query_arg( 'sort' ) ); ?>" <?php selected( $selected_sort, '' ); ?>>Mặc định</option>
								<option value="<?php echo esc_url( add_query_arg( 'sort', 'title_asc' ) ); ?>" <?php selected( $selected_sort, 'title_asc' ); ?>>Tên chương trình (A-Z)</option>
								<option value="<?php echo esc_url( add_query_arg( 'sort', 'title_desc' ) ); ?>" <?php selected( $selected_sort, 'title_desc' ); ?>>Tên chương trình (Z-A)</option>
								<option value="<?php echo esc_url( add_query_arg( 'sort', 'date_desc' ) ); ?>" <?php selected( $selected_sort, 'date_desc' ); ?>>Mới nhất</option>
							</select>
						</div>
						
						<div class="flex items-center gap-2">
							<label for="limit-select" class="text-[11px] font-bold text-slate-500 uppercase tracking-wider shrink-0">Hiển thị:</label>
							<select id="limit-select" class="rounded-lg border-slate-300 text-sm py-2 px-3 bg-white text-slate-700 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary cursor-pointer shadow-sm min-h-[44px]" onchange="location = this.value;">
								<option value="<?php echo esc_url( add_query_arg( 'limit', 12 ) ); ?>" <?php selected( $selected_limit, 12 ); ?>>12</option>
								<option value="<?php echo esc_url( add_query_arg( 'limit', 24 ) ); ?>" <?php selected( $selected_limit, 24 ); ?>>24</option>
								<option value="<?php echo esc_url( add_query_arg( 'limit', 36 ) ); ?>" <?php selected( $selected_limit, 36 ); ?>>36</option>
								<option value="<?php echo esc_url( add_query_arg( 'limit', 48 ) ); ?>" <?php selected( $selected_limit, 48 ); ?>>48</option>
								<option value="<?php echo esc_url( add_query_arg( 'limit', -1 ) ); ?>" <?php selected( $selected_limit, -1 ); ?>>Tất cả</option>
							</select>
						</div>
						
						<?php if ( $selected_type || $selected_nhom || $selected_school || $selected_search || $selected_sort || $selected_limit != 12 ) : ?>
							<a href="<?php echo esc_url( home_url( '/he-dao-tao/' ) ); ?>" class="text-xs font-bold text-brand-primary hover:underline shrink-0">✕ Xóa bộ lọc</a>
						<?php endif; ?>
					</div>
				</div>

				<!-- 3-Column Grid -->
				<div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">
					<?php
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

							$thumb         = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium' ) : '';
							if ( ! $thumb ) {
								$thumb = 'https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=300';
							}

							$prog_types = wp_get_post_terms( $prog_id, 'training_type' );
							$type_name  = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->name : '';
							$type_slug  = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->slug : '';

							// Determine badge classes based on training type name
							$badge_class = 'bg-orange-50 text-orange-600 border border-orange-100';
							if ( $type_name ) {
								$type_name_lower = mb_strtolower( trim( $type_name ), 'UTF-8' );
								if ( false !== strpos( $type_name_lower, 'chính quy' ) ) {
									$badge_class = 'bg-blue-50 text-blue-600 border border-blue-100';
								} elseif ( false !== strpos( $type_name_lower, 'từ xa' ) ) {
									$badge_class = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
								} elseif ( false !== strpos( $type_name_lower, 'vừa học vừa làm' ) || false !== strpos( $type_name_lower, 'vừa làm vừa học' ) || false !== strpos( $type_name_lower, 'liên thông' ) || false !== strpos( $type_name_lower, 'văn bằng 2' ) ) {
									$badge_class = 'bg-amber-50 text-amber-600 border border-amber-100';
								}
							}

							$show_type_badge = ! empty( $type_name ) && ( empty( $selected_type ) || $selected_type !== $type_slug );
					?>
							<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
								 data-compare-btn
								 data-compare-type="program"
								 data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
								 data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
								 data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
								 data-compare-thumb="<?php echo esc_url( $thumb ); ?>">

								<div>
									<!-- School Cover Image -->
									<?php
									$school_thumb = $school_rel_id ? get_the_post_thumbnail_url( $school_rel_id, 'medium' ) : '';
									if ( ! $school_thumb ) {
										$school_thumb = 'https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=300';
									}
									?>
									<div class="h-24 w-full bg-slate-200 bg-cover bg-center relative" style="background-image: url('<?php echo esc_url( $school_thumb ); ?>');">
										<div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent"></div>
										<?php if ( $show_type_badge ) : ?>
											<span class="absolute top-2.5 right-2.5 <?php echo esc_attr( $badge_class ); ?> text-[9px] md:text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wide border shadow-sm z-10">
												Hệ <?php echo esc_html( $type_name ); ?>
											</span>
										<?php endif; ?>
									</div>

									<div class="p-3 md:p-4 pb-0">
										<!-- Top Header: Logo & System Badge -->
										<div class="flex items-center justify-between gap-2 mb-3 -mt-8 md:-mt-10 relative z-10">
											<div class="w-10 h-10 md:w-12 md:h-12 bg-white border border-slate-100 rounded-lg flex items-center justify-center p-1 shrink-0 shadow-xs">
												<?php 
												$school_logo_id = $school_rel_id ? ltdh_get_school_image_id( $school_rel_id ) : 0;
												if ( $school_logo_id ) : 
												?>
													<?php echo wp_get_attachment_image( $school_logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
												<?php else : ?>
													<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-brand-primary/80"><path d="M11.7 2.805a.75.75 0 0 1 .6 0l9.3 4.25a.75.75 0 0 1 0 1.39l-9.3 4.25a.75.75 0 0 1-.6 0L2.4 8.445a.75.75 0 0 1 0-1.39l9.3-4.25ZM2.84 10.74l6.735 3.08a2.25 2.25 0 0 0 1.85 0l6.735-3.08v3.42c0 .532-.244 1.026-.642 1.378L12.5 19.544a1.25 1.25 0 0 1-1.6 0l-5.023-3.97a1.75 1.75 0 0 1-.642-1.378v-3.456Z" /><path d="M20.25 10.32v5.43a3.25 3.25 0 0 1-3.25 3.25h-.5a.75.75 0 0 0 0 1.5h.5a4.75 4.75 0 0 0 4.75-4.75v-5.43a.75.75 0 0 0-1.5 0Z" /></svg>
												<?php endif; ?>
											</div>
										</div>

										<!-- Title Info -->
										<div>
											<h3 class="font-extrabold text-slate-950 text-sm md:text-base hover:text-brand-primary mb-1 leading-snug line-clamp-2 min-h-[40px]">
												<a href="<?php the_permalink(); ?>"><?php echo esc_html( $school_name ); ?></a>
											</h3>
											<span class="text-[11px] text-slate-500 font-semibold bg-slate-50 px-1.5 py-0.5 rounded inline-block mb-3 border border-slate-200/50"><?php the_title(); ?></span>
										</div>

										<?php
										$learning_details = ltdh_get_program_learning_details( $prog_id );
										?>
										<div class="space-y-0.5 md:space-y-1 text-[11px] md:text-sm text-slate-500 py-2 md:py-3 border-t border-slate-100">
											<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( get_field( 'tuition_fee', $prog_id ) ?: 'Liên hệ' ); ?></span></p>
											<p class="hidden sm:block">Thời gian: <span class="font-bold text-slate-700"><?php echo esc_html( get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm' ); ?></span></p>
											<p>Hình thức: <span class="font-bold text-slate-700 text-[10px] md:text-xs"><?php echo esc_html( $learning_details['mode'] ); ?></span></p>
										</div>
									</div>
								</div>

								<div class="p-3 md:p-4 pt-0">
									<div class="pt-2 md:pt-3 border-t border-slate-100 flex items-center justify-between">
										<div class="flex items-center gap-1.5 w-full">
											<a href="<?php the_permalink(); ?>" class="text-[10px] md:text-xs py-2.5 rounded-lg uppercase ltdh-btn-details min-h-[44px] flex items-center justify-center flex-1">Tìm hiểu</a>
											<button type="button"
													class="ltdh-compare-toggle text-[10px] md:text-xs text-slate-400 hover:text-brand-primary font-semibold border border-slate-200 hover:border-brand-primary rounded-lg py-2.5 transition-all min-h-[44px] flex items-center justify-center flex-1"
													data-compare-type="program"
													data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
													data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
													data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
													data-compare-he="<?php echo esc_attr( $type_slug ); ?>"
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
						<div class="col-span-3 text-center py-16 bg-white border border-slate-200 rounded-xl p-8 shadow-xs w-full">
							<span class="text-5xl block mb-4">🔍</span>
							<h3 class="font-extrabold text-slate-800 text-lg mb-2">Không tìm thấy chương trình phù hợp</h3>
							<p class="text-slate-500 text-sm max-w-md mx-auto mb-6">Hệ học này hiện chưa có chương trình hoặc không khớp với các bộ lọc khác. Hãy thử chọn hệ học khác hoặc đặt lại bộ lọc.</p>
							<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
								<a href="<?php echo esc_url( $selected_type ? home_url( '/he-dao-tao/' . $selected_type . '/' ) : home_url( '/he-dao-tao/tu-xa/' ) ); ?>" class="bg-brand-primary text-white px-6 py-3 rounded-lg font-bold text-sm hover:bg-brand-darkBlue shadow-md min-h-[44px] flex items-center justify-center">✕ Đặt lại bộ lọc</a>
								<a href="<?php echo esc_url( home_url( '/he-dao-tao/lien-thong/' ) ); ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3 rounded-lg font-bold text-sm transition-all min-h-[44px] flex items-center justify-center">Xem hệ Liên thông</a>
							</div>
						</div>
					<?php
					endif;
					?>
				</div>

				<!-- Pagination -->
				<?php if ( $query->max_num_pages > 1 ) : ?>
				<div class="mt-12 flex justify-center theme-pagination">
					<?php
					echo paginate_links( [
						'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
						'format'    => '?paged=%#%',
						'current'   => max( 1, get_query_var( 'paged' ) ),
						'total'     => $query->max_num_pages,
						'prev_text' => '← Trước',
						'next_text' => 'Sau →',
					] );
					?>
				</div>
				<?php endif; ?>

			</div>
		</div>

	</div>
</main>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Toggle sidebar open state on mobile
		const sidebar = document.getElementById('sidebar-filters');
		if (sidebar && window.innerWidth < 1024) {
			sidebar.removeAttribute('open');
		}

		// Instant Search Filter for Majors List
		const searchInput = document.getElementById('major-search-filter');
		if (searchInput) {
			searchInput.addEventListener('input', function(e) {
				const val = e.target.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
				const items = document.querySelectorAll('#major-list-container li');
				items.forEach(function(item) {
					const text = (item.getAttribute('data-search') || '').normalize("NFD").replace(/[\u0300-\u036f]/g, "");
					if (text.includes(val)) {
						item.style.display = '';
					} else {
						item.style.display = 'none';
					}
				});
			});
		}
	});
</script>

<?php
get_footer();
