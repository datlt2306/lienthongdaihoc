<?php
/**
 * Archive Program Template (with Sidebar + 3-Column Grid)
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
$selected_search   = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

$args = [
	'post_type'      => 'program',
	'posts_per_page' => 12,
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

if ( ! empty( $selected_type ) ) {
	$args['tax_query'][] = [
		'taxonomy' => 'training_type',
		'field'    => 'slug',
		'terms'    => $selected_type,
	];
}

$args  = apply_filters( 'pre_get_posts_args_ltdh', $args );
$query = new WP_Query( $args );

// Count for "Tất cả các ngành": same filters as the main query (hệ, school, search,
// admission_status) but WITHOUT the nhóm ngành / major filter, so selecting a
// nhóm ngành does not change the "all majors" number.
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

					<!-- Search Box -->
					<div class="bg-white border border-slate-200 rounded-lg p-4 shadow-sm">
						<form action="<?php echo esc_url( $selected_type ? home_url( '/he-dao-tao/' . $selected_type . '/' ) : home_url( '/chuong-trinh/' ) ); ?>" method="GET">
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
									// Calculate count of programs in this training type
									$count_args = [
										'post_type'      => 'program',
										'posts_per_page' => -1,
										'post_status'    => 'publish',
										'tax_query'      => [ [ 'taxonomy' => 'training_type', 'field' => 'slug', 'terms' => $t_term->slug ] ]
									];
									$t_query = new WP_Query( $count_args );
									$t_count = $t_query->found_posts;
									wp_reset_postdata();
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

					<!-- Nhóm ngành Filter -->
					<div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
						<h3 class="font-extrabold text-slate-900 text-base mb-4 border-b border-slate-100 pb-3 uppercase tracking-wider">Nhóm ngành</h3>
						<ul class="space-y-1">
							<li>
								<a href="<?php echo esc_url( remove_query_arg( 'nganh' ) ); ?>"
								   class="flex items-center justify-between px-3 py-3 rounded-lg text-sm font-semibold transition-all <?php echo empty( $selected_nhom ) ? 'bg-brand-primary text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[44px]">
									<span>Tất cả các ngành</span>
									<span class="text-xs px-2 py-0.5 rounded-full font-bold <?php echo empty( $selected_nhom ) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'; ?>">
										<?php echo esc_html( $all_majors_count ); ?>
									</span>
								</a>
							</li>
							<?php if ( ! is_wp_error( $major_cats ) && ! empty( $major_cats ) ) : ?>
								<?php foreach ( $major_cats as $cat ) :
									$is_active = ( $selected_nhom === $cat->slug );
									// Get major IDs in this category
									$cat_major_ids = get_posts( [
										'post_type'   => 'major',
										'numberposts' => -1,
										'fields'      => 'ids',
										'tax_query'   => [ [ 'taxonomy' => 'major_cat', 'field' => 'slug', 'terms' => $cat->slug ] ],
									] );
									$prog_count = 0;
									if ( ! empty( $cat_major_ids ) ) {
										// Build count args that mirror the main query's active filters
										$count_args = [
											'post_type'      => 'program',
											'post_status'    => 'publish',
											'posts_per_page' => -1,
											'fields'         => 'ids',
											'meta_query'     => [
												'relation' => 'AND',
												[ 'key' => 'major_relationship', 'value' => $cat_major_ids, 'compare' => 'IN' ],
											],
										];
										// Apply training type filter if set
										if ( ! empty( $selected_type ) ) {
											$count_args['tax_query'] = [ [ 'taxonomy' => 'training_type', 'field' => 'slug', 'terms' => $selected_type ] ];
										}
										// Apply search filter if set
										if ( ! empty( $selected_search ) ) {
											$count_args['s'] = $selected_search;
										}
										$prog_count_q = new WP_Query( $count_args );
										$prog_count   = $prog_count_q->found_posts;
										wp_reset_postdata();
									}
								?>
									<li>
										<a href="<?php echo esc_url( add_query_arg( 'nganh', $cat->slug, remove_query_arg( 'nganh' ) ) ); ?>"
										   class="flex items-center justify-between px-3 py-3 rounded-lg text-sm font-semibold transition-all <?php echo $is_active ? 'bg-brand-primary text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[44px]">
											<span><?php echo esc_html( $cat->name ); ?></span>
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
			</details>

			<!-- ============ MAIN CONTENT ============ -->
			<div class="lg:col-span-3">

				<!-- Header bar -->
				<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-slate-200 gap-3">
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
					<?php if ( $selected_type || $selected_nhom || $selected_school || $selected_search ) : ?>
						<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" class="text-xs font-bold text-brand-primary hover:underline shrink-0">✕ Xóa tất cả bộ lọc</a>
					<?php endif; ?>
				</div>

				<!-- 3-Column Grid -->
				<div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">
					<?php
					if ( $query->have_posts() ) :
						while ( $query->have_posts() ) : $query->the_post();
							$prog_id       = get_the_ID();
							$school_rel_id = get_field( 'school_relationship', $prog_id );
							$school_name   = $school_rel_id ? get_the_title( $school_rel_id ) : 'Đại học liên kết';

							$major_rel_id = get_field( 'major_relationship', $prog_id );
							$thumb        = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium' ) : '';
							if ( ! $thumb ) {
								$thumb = 'https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=300';
							}

							$prog_types = wp_get_post_terms( $prog_id, 'training_type' );
							$type_name  = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->name : '';
							$type_slug  = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->slug : '';

							// Only show badge if NOT already filtered by this type
							$show_type_badge = ! empty( $type_name ) && ( empty( $selected_type ) || $selected_type !== $type_slug );
					?>
							<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
								 data-compare-btn
								 data-compare-type="program"
								 data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
								 data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
								 data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
								 data-compare-thumb="<?php echo esc_url( $thumb ); ?>">

								<!-- Thumbnail with optional badge -->
								<div class="relative h-28 md:h-44 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $thumb ); ?>');">
									<?php if ( $show_type_badge ) : ?>
										<span class="absolute top-2 left-2 bg-brand-accent text-white text-[9px] md:text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wide shadow">
											<?php echo esc_html( $type_name ); ?>
										</span>
									<?php endif; ?>
								</div>

								<!-- Card Body -->
								<div class="p-3 md:p-5 flex-1 flex flex-col justify-between">
									<div>
										<span class="text-[10px] md:text-xs text-slate-400 font-semibold uppercase block mb-0.5 md:mb-1 truncate"><?php echo esc_html( $school_name ); ?></span>
										<h3 class="font-extrabold text-slate-800 text-xs md:text-base hover:text-brand-primary mb-2 md:mb-3 leading-snug line-clamp-2 min-h-[32px] md:min-h-[48px]">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h3>

										<?php
										$learning_details = ltdh_get_program_learning_details( $prog_id );
										?>
										<div class="space-y-0.5 md:space-y-1 text-[11px] md:text-sm text-slate-500 py-2 md:py-3 border-t border-slate-100">
											<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( get_field( 'tuition_fee', $prog_id ) ?: 'Liên hệ' ); ?></span></p>
											<p class="hidden sm:block">Thời gian: <span class="font-bold text-slate-700"><?php echo esc_html( get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm' ); ?></span></p>
											<p>Hình thức: <span class="font-bold text-slate-700 text-[10px] md:text-xs"><?php echo esc_html( $learning_details['mode'] ); ?></span></p>
										</div>
									</div>

									<div class="mt-3 pt-2 md:mt-4 md:pt-3 border-t border-slate-100 flex items-center justify-between">
										<div class="flex items-center gap-1.5 w-full">
											<a href="<?php the_permalink(); ?>" class="bg-brand-primary text-white text-[10px] md:text-xs font-bold py-2.5 rounded-lg hover:bg-brand-darkBlue transition-all min-h-[44px] flex items-center justify-center shadow-sm flex-1">Tìm hiểu</a>
											<button type="button"
													class="ltdh-compare-toggle text-[10px] md:text-xs text-slate-400 hover:text-brand-primary font-semibold border border-slate-200 hover:border-brand-primary rounded-lg py-2.5 transition-all min-h-[44px] flex items-center justify-center flex-1"
													data-compare-type="program"
													data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
													data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
													data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
													data-compare-he="<?php echo esc_attr( $type_slug ); ?>"
													data-compare-nganh="<?php echo esc_attr( $major_rel_id ); ?>">
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
						<div class="col-span-3 text-center py-16 bg-white border border-slate-200 rounded-xl p-8 shadow-xs">
							<span class="text-5xl block mb-4">🔍</span>
							<h3 class="font-extrabold text-slate-800 text-lg mb-2">Không tìm thấy chương trình phù hợp</h3>
							<p class="text-slate-500 text-sm max-w-md mx-auto mb-6">Hệ học này hiện chưa có chương trình hoặc không khớp với các bộ lọc khác. Hãy thử chọn hệ học khác hoặc đặt lại bộ lọc.</p>
							<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
								<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" class="bg-brand-primary text-white px-6 py-3 rounded-lg font-bold text-sm hover:bg-brand-darkBlue shadow-md min-h-[44px] flex items-center justify-center">✕ Đặt lại bộ lọc</a>
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
		const sidebar = document.getElementById('sidebar-filters');
		if (sidebar && window.innerWidth < 1024) {
			sidebar.removeAttribute('open');
		}
	});
</script>

<?php
get_footer();
