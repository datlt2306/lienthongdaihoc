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
$selected_school = isset( $_GET['truong_filter'] ) ? sanitize_text_field( $_GET['truong_filter'] ) : '';
$selected_major  = isset( $_GET['nganh_filter'] ) ? sanitize_text_field( $_GET['nganh_filter'] ) : '';
$selected_type   = isset( $_GET['he_filter'] ) ? sanitize_text_field( $_GET['he_filter'] ) : '';

$args = [
	'post_type'      => 'program',
	'posts_per_page' => 12,
	'post_status'    => 'publish',
	'meta_query'     => [ 'relation' => 'AND' ],
	'tax_query'      => [ 'relation' => 'AND' ],
];

if ( ! empty( $selected_school ) ) {
	$args['meta_query'][] = [
		'key'     => 'school_relationship',
		'value'   => $selected_school,
		'compare' => '=',
	];
}

if ( ! empty( $selected_major ) ) {
	$args['meta_query'][] = [
		'key'     => 'major_relationship',
		'value'   => $selected_major,
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

$query = new WP_Query( $args );
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

		<!-- Filter Panel -->
		<section class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-8">
			<form action="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
				<div>
					<label class="block text-sm font-bold text-slate-700 mb-1">Trường đại học</label>
					<select name="truong_filter" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-base focus:border-brand-primary focus:outline-none">
						<option value="">-- Chọn trường học --</option>
						<?php
						$schools = get_posts( [ 'post_type' => 'school', 'numberposts' => -1 ] );
						foreach ( $schools as $s ) {
							$selected = ( $selected_school == $s->ID ) ? 'selected' : '';
							echo '<option value="' . esc_attr( $s->ID ) . '" ' . $selected . '>' . esc_html( $s->post_title ) . '</option>';
						}
						?>
					</select>
				</div>
				
				<div>
					<label class="block text-sm font-bold text-slate-700 mb-1">Ngành đào tạo</label>
					<select name="nganh_filter" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-base focus:border-brand-primary focus:outline-none">
						<option value="">-- Chọn ngành học --</option>
						<?php
						$majors = get_posts( [ 'post_type' => 'major', 'numberposts' => -1 ] );
						foreach ( $majors as $m ) {
							$selected = ( $selected_major == $m->ID ) ? 'selected' : '';
							echo '<option value="' . esc_attr( $m->ID ) . '" ' . $selected . '>' . esc_html( $m->post_title ) . '</option>';
						}
						?>
					</select>
				</div>

				<div>
					<label class="block text-sm font-bold text-slate-700 mb-1">Hệ đào tạo</label>
					<select name="he_filter" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-base focus:border-brand-primary focus:outline-none">
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

				<button type="submit" class="w-full bg-[#2563EB] text-white py-3 rounded-xl font-bold hover:bg-[#1E40AF] transition-all text-base">
					LỌC KẾT QUẢ
				</button>
			</form>
		</section>

		<!-- Programs Grid Output -->
		<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
			<?php
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) : $query->the_post();
					$prog_id = get_the_ID();
					$school_rel_id = get_field( 'school_relationship', $prog_id );
					$school_name = $school_rel_id ? get_the_title( $school_rel_id ) : 'Đại học liên kết';
			?>
					<div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
						<?php
						$status = get_post_meta( $prog_id, 'admission_status', true ) ?: 'tuyen-sinh';
						$groups = get_post_meta( $prog_id, 'admission_groups', true );
						?>
						<div>
							<div class="flex items-center flex-wrap gap-2 mb-1.5">
								<span class="text-sm text-slate-400 font-semibold uppercase"><?php echo esc_html( $school_name ); ?></span>
								<?php if ( $status === 'tam-ngung' ) : ?>
									<span class="bg-rose-50 text-rose-700 border border-rose-100 text-[10px] font-bold px-2 py-0.5 rounded">Tạm ngưng tuyển</span>
								<?php else : ?>
									<span class="bg-blue-50 text-brand-primary border border-blue-100 text-[10px] font-bold px-2 py-0.5 rounded">Đang tuyển</span>
								<?php endif; ?>
							</div>
							<h3 class="font-extrabold text-slate-800 text-lg hover:text-brand-primary mb-3">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							
							<div class="space-y-1.5 text-sm text-slate-500 py-3 border-t border-slate-100">
								<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( get_field( 'tuition_fee' ) ?: 'Liên hệ' ); ?></span></p>
								<p>Thời gian: <span class="font-bold text-slate-700"><?php echo esc_html( get_field( 'duration' ) ?: '1.5 - 2 năm' ); ?></span></p>
								<p>Cơ sở: <span class="font-bold text-slate-700"><?php echo esc_html( get_field( 'campus_info' ) ?: 'Cơ sở / Online' ); ?></span></p>
								<?php if ( ! empty( $groups ) ) : ?>
									<p>Tổ hợp: <span class="font-bold text-slate-700"><?php echo esc_html( $groups ); ?></span></p>
								<?php endif; ?>
							</div>
						</div>

						<div class="mt-6 pt-4 border-t border-slate-100 flex justify-between items-center">
							<a href="<?php the_permalink(); ?>" class="text-sm text-brand-primary font-bold hover:underline">Chi tiết</a>
							<a href="<?php the_permalink(); ?>#register" class="bg-[#2563EB] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-[#1E40AF] transition-all">Đăng ký học</a>
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
