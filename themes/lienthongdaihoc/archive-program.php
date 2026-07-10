<?php
/**
 * Archive Program Template (Filterable Search Engine)
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Fetch filter values from URL GET params
$selected_school = isset( $_GET['truong'] ) ? sanitize_text_field( $_GET['truong'] ) : '';
$selected_major  = isset( $_GET['nganh'] ) ? sanitize_text_field( $_GET['nganh'] ) : '';
$selected_type   = isset( $_GET['he'] ) ? sanitize_text_field( $_GET['he'] ) : '';
$selected_search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

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
	'tax_query'      => [ 'relation' => 'AND' ],
];

if ( ! empty( $selected_search ) ) {
	$args['s'] = $selected_search;
}

if ( ! empty( $selected_school ) ) {
	if ( ! is_numeric( $selected_school ) ) {
		$school_post = get_page_by_path( $selected_school, OBJECT, 'school' );
		$school_id = $school_post ? $school_post->ID : 0;
	} else {
		$school_id = intval( $selected_school );
	}
	$args['meta_query'][] = [
		'key'     => 'school_relationship',
		'value'   => $school_id,
		'compare' => '=',
	];
}

if ( ! empty( $selected_major ) ) {
	if ( ! is_numeric( $selected_major ) ) {
		$major_post = get_page_by_path( $selected_major, OBJECT, 'major' );
		$major_id = $major_post ? $major_post->ID : 0;
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

$args = apply_filters( 'pre_get_posts_args_ltdh', $args );
$query = new WP_Query( $args );
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

		<!-- Filter Panel -->
		<section class="bg-white p-6 rounded shadow-sm border border-slate-200 mb-8">
			<form action="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
				<div>
					<label class="block text-sm font-bold text-slate-700 mb-1">Từ khóa tìm kiếm</label>
					<input type="text" name="s" value="<?php echo esc_attr( $selected_search ); ?>" placeholder="Tìm tên chương trình..." class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-base focus:border-brand-primary focus:outline-none placeholder-slate-400 font-medium" />
				</div>

				<div>
					<label class="block text-sm font-bold text-slate-700 mb-1">Trường đại học</label>
					<select name="truong" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-base focus:border-brand-primary focus:outline-none">
						<option value="">-- Chọn trường học --</option>
						<?php
						$schools = get_posts( [ 'post_type' => 'school', 'numberposts' => -1 ] );
						foreach ( $schools as $s ) {
							$selected = ( $selected_school == $s->post_name ) ? 'selected' : '';
							echo '<option value="' . esc_attr( $s->post_name ) . '" ' . $selected . '>' . esc_html( $s->post_title ) . '</option>';
						}
						?>
					</select>
				</div>
				
				<div>
					<label class="block text-sm font-bold text-slate-700 mb-1">Ngành đào tạo</label>
					<select name="nganh" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-base focus:border-brand-primary focus:outline-none">
						<option value="">-- Chọn ngành học --</option>
						<?php
						$majors = get_posts( [ 'post_type' => 'major', 'numberposts' => -1 ] );
						foreach ( $majors as $m ) {
							$selected = ( $selected_major == $m->post_name ) ? 'selected' : '';
							echo '<option value="' . esc_attr( $m->post_name ) . '" ' . $selected . '>' . esc_html( $m->post_title ) . '</option>';
						}
						?>
					</select>
				</div>

				<div>
					<label class="block text-sm font-bold text-slate-700 mb-1">Hệ đào tạo</label>
					<select name="he" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-base focus:border-brand-primary focus:outline-none">
						<option value="">-- Chọn hệ học --</option>
						<?php
						$types = get_terms( [ 'taxonomy' => 'training_type', 'hide_empty' => false ] );
						foreach ( $types as $t ) {
							$selected = ( $selected_type == $t->slug ) ? 'selected' : '';
							echo '<option value="' . esc_attr( $t->slug ) . '" ' . $selected . '>' . esc_html( $t->name ) . '</option>';
						}
						?>
					</select>
				</div>
			</form>
		</section>

		<!-- Programs Grid Output -->
		<div id="program-results-container" class="grid grid-cols-1 md:grid-cols-3 gap-6">
			<?php
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) : $query->the_post();
					$prog_id = get_the_ID();
					$school_rel_id = get_field( 'school_relationship', $prog_id );
					$school_name = $school_rel_id ? get_the_title( $school_rel_id ) : 'Đại học liên kết';
			?>
					<?php
					$major_rel_id = get_field( 'major_relationship', $prog_id );
					$major_thumb = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium' ) : '';
					if ( ! $major_thumb ) {
						$major_thumb = 'https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=300';
					}
					$types = wp_get_post_terms( $prog_id, 'training_type' );
					$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : 'Chưa xác định';
					?>
					<div class="bg-white border border-slate-200 rounded overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
						 data-compare-btn data-compare-type="program" data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
						 data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
						 data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
						 data-compare-thumb="<?php echo esc_url( $major_thumb ); ?>">
						<div class="h-44 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $major_thumb ); ?>');"></div>
						<div class="p-6 flex-1 flex flex-col justify-between">
							<?php
							$status = get_post_meta( $prog_id, 'admission_status', true ) ?: 'tuyen-sinh';
							$groups = get_post_meta( $prog_id, 'admission_groups', true );
							?>
							<div>
								<div class="flex items-center flex-wrap gap-2 mb-1.5">
									<span class="text-sm text-slate-400 font-semibold uppercase"><?php echo esc_html( $school_name ); ?></span>
								</div>
								<h3 class="font-extrabold text-slate-800 text-lg hover:text-brand-primary mb-3">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
								
								<?php
								$learning_details = ltdh_get_program_learning_details( get_the_ID() );
								?>
								<div class="space-y-1.5 text-sm text-slate-500 py-3 border-t border-slate-100">
									<p>Hệ đào tạo: <span class="font-bold text-slate-700"><?php echo esc_html( $type_name ); ?></span></p>
									<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( get_field( 'tuition_fee' ) ?: 'Liên hệ' ); ?></span></p>
									<p>Thời gian: <span class="font-bold text-slate-700"><?php echo esc_html( get_field( 'duration' ) ?: '1.5 - 2 năm' ); ?></span></p>
									<p>Cơ sở: <span class="font-bold text-slate-700"><?php echo esc_html( $learning_details['campus'] ); ?></span></p>
									<p>Hình thức: <span class="font-bold text-slate-700 text-xs"><?php echo esc_html( $learning_details['mode'] ); ?></span></p>
									<?php if ( ! empty( $groups ) ) : ?>
										<p>Tổ hợp: <span class="font-bold text-slate-700"><?php echo esc_html( $groups ); ?></span></p>
									<?php endif; ?>
								</div>
							</div>

							<div class="mt-6 pt-4 border-t border-slate-100 flex justify-between items-center">
								<div class="flex items-center gap-2">
									<a href="<?php the_permalink(); ?>" class="text-sm text-brand-primary font-bold hover:underline">Chi tiết</a>
									<button type="button" class="ltdh-compare-toggle text-xs text-slate-400 hover:text-brand-primary font-semibold border border-slate-200 hover:border-brand-primary rounded-lg px-2.5 py-1 transition-all"
											data-compare-type="program" data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
											data-compare-title="<?php echo esc_attr( get_the_title() ); ?>"
											data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>">
										So sánh
									</button>
								</div>
								<a href="<?php the_permalink(); ?>#register" class="bg-[#2563EB] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-[#1E40AF] transition-all">Đăng ký học</a>
							</div>
						</div>
					</div>
			<?php
				endwhile;
				wp_reset_postdata();
			else :
				echo '<div class="col-span-3 text-center py-12"><p class="text-slate-500 text-base">Không tìm thấy chương trình học nào khớp với bộ lọc.</p></div>';
			endif;
			?>
		</div>

	</div>
</main>

<?php
get_footer();
