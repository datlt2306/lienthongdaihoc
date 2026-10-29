<?php
/**
 * Program Overview Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id   = isset( $args['program_id'] ) ? (int) $args['program_id'] : get_the_ID();
$school_id    = isset( $args['school_id'] ) ? (int) $args['school_id'] : (int) get_field( LTDH_META_SCHOOL_REL, $program_id );
$major_id     = isset( $args['major_id'] ) ? (int) $args['major_id'] : (int) get_field( LTDH_META_MAJOR_REL, $program_id );
$school_title = isset( $args['school_title'] ) ? $args['school_title'] : ( $school_id ? get_the_title( $school_id ) : '' );
$major_title  = isset( $args['major_title'] ) ? $args['major_title'] : ( $major_id ? get_the_title( $major_id ) : '' );
$tuition      = isset( $args['tuition'] ) ? $args['tuition'] : ltdh_get_program_tuition_display( $program_id );
$duration     = isset( $args['duration'] ) ? $args['duration'] : ( function_exists( 'ltdh_get_program_duration' ) ? ltdh_get_program_duration( $program_id ) : ( get_field( LTDH_META_DURATION, $program_id ) ?: '1.5 - 2 năm' ) );
$quota        = isset( $args['quota'] ) ? $args['quota'] : get_field( 'quota', $program_id );
$enrollment   = isset( $args['enrollment'] ) ? $args['enrollment'] : ltdh_get_program_admission_deadline_display( $program_id );
$benefits     = isset( $args['benefits'] ) ? $args['benefits'] : get_field( 'program_benefits', $program_id );
if ( ! empty( $benefits ) ) {
	$benefits = str_replace( 'Tự chủ thời gian và không gian học tập, phôi bằng tốt nghiệp không ghi hình thức đào tạo.', '', $benefits );
	$benefits = str_replace( 'phôi bằng tốt nghiệp không ghi hình thức đào tạo.', '', $benefits );
	if ( empty( trim( strip_tags( $benefits ) ) ) ) {
		$benefits = '';
	}
}

$learning_details = ltdh_get_program_learning_details( $program_id );
$tuition_year     = get_field( 'tuition_academic_year', $program_id );
$degree_val       = function_exists( 'ltdh_get_program_degree_type' ) 
	? ltdh_get_program_degree_type( $program_id ) 
	: ( get_field( 'degree_type', $program_id ) ?: get_post_meta( $program_id, 'degree_type', true ) ?: 'Cử nhân / Kỹ sư' );
$diploma_val      = function_exists( 'ltdh_get_program_diploma_value' )
	? ltdh_get_program_diploma_value( $program_id )
	: ( get_field( 'diploma_value', $program_id ) ?: '' );
$prog_clean_name   = get_the_title( $program_id );
$prog_school_name  = $school_title ?: 'trường đối tác';
$prog_mode_name    = ! empty( $learning_details['mode'] ) ? $learning_details['mode'] : 'Liên thông Đại học';
$prog_dur_name     = $duration ?: '1.5 – 2.0 năm';
$prog_fee_name     = $tuition ?: 'Theo thông báo tuyển sinh';
?>

<!-- SECTION 2: PROGRAM OVERVIEW -->
<section id="tong-quan" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6">
	<h2 class="text-lg md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Tổng quan chương trình</h2>

	<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
		<?php the_content(); ?>
	</div>
	<?php if ( $benefits ) : ?>
		<div class="mt-6 bg-teal-50/40 p-4 rounded-xl mb-6">
			<h3 class="text-teal-800 font-bold text-base mb-2">Quyền lợi nổi bật</h3>
			<div class="prose prose-slate max-w-none text-slate-900 text-sm md:text-base">
				<?php echo wp_kses_post( $benefits ); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-3.5 py-4 border-t border-slate-100">
		<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
			<span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Học phí</span>
			<span class="font-bold text-[#00308b] text-sm sm:text-base leading-snug">
				<?php 
				echo esc_html( $tuition ?: 'Liên hệ' ); 
				if ( $tuition_year ) {
					echo ' <span class="text-[10px] font-normal text-slate-400 block sm:inline">(' . esc_html( $tuition_year ) . ')</span>';
				}
				?>
			</span>
		</div>
		<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
			<span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Thời gian học</span>
			<span class="font-bold text-slate-800 text-sm sm:text-base leading-snug"><?php echo esc_html( $duration ?: '1.5 - 2 năm' ); ?></span>
		</div>
		<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
			<span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Danh hiệu văn bằng</span>
			<span class="font-bold text-slate-800 text-sm sm:text-base leading-snug"><?php echo esc_html( $degree_val ); ?></span>
		</div>
		<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
			<span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Căn cứ pháp lý</span>
			<span class="font-bold text-emerald-700 text-sm sm:text-base leading-snug">Thông tư 27/2019/TT-BGDĐT</span>
		</div>
		<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
			<span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Hình thức học</span>
			<span class="font-bold text-slate-800 text-sm sm:text-base leading-snug"><?php echo esc_html( $learning_details['mode'] ); ?></span>
		</div>
		<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
			<span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Cơ sở học</span>
			<span class="font-bold text-slate-800 text-sm sm:text-base leading-snug"><?php 
				$display_campus = $learning_details['campus'] ?? '';
				if ( empty( $display_campus ) || 'online' === strtolower( trim( $display_campus ) ) ) {
					$display_campus = 'Toàn quốc';
				}
				echo esc_html( $display_campus ); 
			?></span>
		</div>
		<?php if ( $quota ) : ?>
			<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
				<span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Chỉ tiêu</span>
				<span class="font-bold text-slate-800 text-sm sm:text-base leading-snug"><?php echo esc_html( $quota ); ?> chỉ tiêu</span>
			</div>
		<?php endif; ?>
		<div class="bg-slate-50/80 rounded-xl p-3 sm:p-3.5 flex flex-col justify-center">
			<span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Hạn hồ sơ</span>
			<span class="font-bold text-[#EA580C] text-sm sm:text-base leading-snug"><?php echo esc_html( $enrollment ?: 'Đang nhận hồ sơ' ); ?></span>
		</div>
	</div>

	<!-- Semantic HTML Table for Search Engine Snippets & AI LLM Crawlers -->
	<div class="sr-only">
		<table aria-label="Bảng tóm tắt thông số chương trình tuyển sinh">
			<caption>Thông số kỹ thuật chương trình tuyển sinh Liên thông</caption>
			<tbody>
				<tr><th>Trường đào tạo</th><td><?php echo esc_html( $prog_school_name ); ?></td></tr>
				<tr><th>Chuyên ngành</th><td><?php echo esc_html( ! empty( $clean_major ) ? $clean_major : ( $major_title ?: $prog_clean_name ) ); ?></td></tr>
				<tr><th>Hình thức đào tạo</th><td><?php echo esc_html( $prog_mode_name ); ?></td></tr>
				<tr><th>Đối tượng đầu vào</th><td>Trung cấp / Cao đẳng</td></tr>
				<tr><th>Thời gian đào tạo</th><td><?php echo esc_html( $prog_dur_name ); ?></td></tr>
				<tr><th>Mức học phí</th><td><?php echo esc_html( $prog_fee_name ); ?></td></tr>
				<tr><th>Danh hiệu văn bằng</th><td><?php echo esc_html( $degree_val ); ?></td></tr>
				<tr><th>Hạn nộp hồ sơ</th><td><?php echo esc_html( $enrollment ?: 'Đang nhận hồ sơ' ); ?></td></tr>
			</tbody>
		</table>
	</div>

	<!-- Thông tin pháp lý văn bằng & Giá trị toàn quốc (Thông tư 27/2019/TT-BGDĐT) -->
	<div class="mt-4 bg-emerald-50/50 rounded-xl p-4 text-sm md:text-base text-slate-700 space-y-2 leading-relaxed border border-emerald-100/50">
		<?php if ( ! empty( $diploma_val ) ) : ?>
			<p class="flex items-start gap-2">
				<span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
				<span><strong>Quy định cấp bằng & Giá trị pháp lý:</strong> <?php echo nl2br( esc_html( $diploma_val ) ); ?></span>
			</p>
		<?php else : ?>
			<p class="flex items-start gap-2">
				<span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
				<span><strong>Không ghi hình thức đào tạo:</strong> Theo quy định tại Thông tư 27/2019/TT-BGDĐT của Bộ GD&ĐT, văn bằng đại học hiện hành không ghi hình thức đào tạo (không phân biệt Chính quy, Vừa làm vừa học hay Đào tạo từ xa).</span>
			</p>
		<?php endif; ?>
		<p class="flex items-start gap-2">
			<span class="text-emerald-600 font-bold shrink-0 mt-0.5">✓</span>
			<span><strong>Giá trị sử dụng toàn quốc:</strong> Bằng có đầy đủ giá trị pháp lý để thi tuyển công chức, viên chức, xét nâng lương ngạch bậc và học tiếp lên Thạc sĩ, Tiến sĩ tại tất cả các cơ sở đào tạo trong và ngoài nước.</span>
		</p>
	</div>
</section>
