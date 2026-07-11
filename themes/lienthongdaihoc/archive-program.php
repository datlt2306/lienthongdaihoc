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
$selected_major    = isset( $_GET['nganh'] ) ? sanitize_text_field( $_GET['nganh'] ) : '';
$selected_nhom     = isset( $_GET['nhom_nganh'] ) ? sanitize_text_field( $_GET['nhom_nganh'] ) : '';
$selected_type     = isset( $_GET['he'] ) ? sanitize_text_field( $_GET['he'] ) : '';
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
} elseif ( ! empty( $selected_major ) ) {
	if ( ! is_numeric( $selected_major ) ) {
		$major_post = get_page_by_path( $selected_major, OBJECT, 'major' );
		$major_id   = $major_post ? $major_post->ID : 0;
	} else {
		$major_id = intval( $selected_major );
	}
	$args['meta_query'][] = [
		'key'     => 'major_relationship',
		'value'   => $major_id,
		'compare' => '=',
	];
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
			<div class="lg:col-span-1">
				<div class="sticky top-24 space-y-6">

					<!-- Search Box -->
					<div class="bg-white border border-slate-200 rounded-lg p-4 shadow-sm">
						<form action="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" method="GET">
							<?php if ( $selected_type ) : ?>
								<input type="hidden" name="he" value="<?php echo esc_attr( $selected_type ); ?>">
							<?php endif; ?>
							<input type="text" name="s" value="<?php echo esc_attr( $selected_search ); ?>" placeholder="Tìm kiếm chương trình..." class="w-full border border-slate-200 rounded-lg px-3 py-3 text-sm focus:border-brand-primary focus:outline-none placeholder-slate-400 min-h-[44px]">
						</form>
					</div>

					<!-- Nhóm ngành Filter -->
					<div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
						<h3 class="font-extrabold text-slate-900 text-base mb-4 border-b border-slate-100 pb-3 uppercase tracking-wider">Nhóm ngành</h3>
						<ul class="space-y-1">
							<li>
								<a href="<?php echo esc_url( remove_query_arg( 'nhom_nganh' ) ); ?>"
								   class="flex items-center justify-between px-3 py-3 rounded-lg text-sm font-semibold transition-all <?php echo empty( $selected_nhom ) ? 'bg-brand-primary text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[44px]">
									<span>Tất cả các ngành</span>
									<span class="text-xs px-2 py-0.5 rounded-full font-bold <?php echo empty( $selected_nhom ) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'; ?>">
										<?php echo esc_html( $query->found_posts ); ?>
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
										<a href="<?php echo esc_url( add_query_arg( 'nhom_nganh', $cat->slug, remove_query_arg( 'nhom_nganh' ) ) ); ?>"
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
			</div>

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
					<?php if ( $selected_type || $selected_school || $selected_search ) : ?>
						<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" class="text-xs font-bold text-brand-primary hover:underline shrink-0">✕ Xóa tất cả bộ lọc</a>
					<?php endif; ?>
				</div>

				<!-- 3-Column Grid -->
				<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
							<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col"
								 data-compare-btn
								 data-compare-type="program"
								 data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
								 data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
								 data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
								 data-compare-thumb="<?php echo esc_url( $thumb ); ?>">

								<!-- Thumbnail with optional badge -->
								<div class="relative h-44 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $thumb ); ?>');">
									<?php if ( $show_type_badge ) : ?>
										<span class="absolute top-2.5 left-2.5 bg-brand-primary text-white text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase tracking-wide shadow">
											<?php echo esc_html( $type_name ); ?>
										</span>
									<?php endif; ?>
								</div>

								<!-- Card Body -->
								<div class="p-5 flex-1 flex flex-col justify-between">
									<div>
										<span class="text-xs text-slate-400 font-semibold uppercase block mb-1"><?php echo esc_html( $school_name ); ?></span>
										<h3 class="font-extrabold text-slate-800 text-base hover:text-brand-primary mb-3 leading-snug">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h3>

										<?php
										$learning_details = ltdh_get_program_learning_details( $prog_id );
										?>
										<div class="space-y-1 text-sm text-slate-500 py-3 border-t border-slate-100">
											<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( get_field( 'tuition_fee', $prog_id ) ?: 'Liên hệ' ); ?></span></p>
											<p>Thời gian: <span class="font-bold text-slate-700"><?php echo esc_html( get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm' ); ?></span></p>
											<p>Cơ sở: <span class="font-bold text-slate-700"><?php echo esc_html( $learning_details['campus'] ); ?></span></p>
											<p>Hình thức: <span class="font-bold text-slate-700 text-xs"><?php echo esc_html( $learning_details['mode'] ); ?></span></p>
										</div>
									</div>

									<div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
										<div class="flex items-center gap-2 w-full">
											<a href="<?php the_permalink(); ?>#register" class="bg-brand-accent text-white text-xs font-bold px-4 py-2.5 rounded-lg hover:bg-amber-700 transition-all min-h-[44px] flex items-center justify-center shadow-sm shadow-brand-accent/10">Tìm hiểu</a>
											<button type="button"
													class="ltdh-compare-toggle text-xs text-slate-400 hover:text-brand-primary font-semibold border border-slate-200 hover:border-brand-primary px-4 py-2.5 rounded-lg transition-all min-h-[44px] flex items-center justify-center"
													data-compare-type="program"
													data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
													data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
													data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>">
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
						echo '<div class="col-span-3 text-center py-16"><p class="text-slate-500 text-base">Không tìm thấy chương trình học nào khớp với bộ lọc.</p></div>';
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

<?php
get_footer();
