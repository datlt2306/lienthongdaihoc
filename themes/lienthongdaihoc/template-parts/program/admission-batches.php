<?php
/**
 * Program Admission Batches Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id   = isset( $args['program_id'] ) ? (int) $args['program_id'] : get_the_ID();
$batches_data = function_exists( 'ltdh_get_program_admission_batches' ) ? ltdh_get_program_admission_batches( $program_id ) : get_field( 'admission_batches', $program_id );

if ( empty( $batches_data ) || ! is_array( $batches_data ) ) {
	return;
}

// Determine training type: 'chinh-quy' -> 'Thi tuyển', others ('tu-xa', 'vua-hoc-vua-lam') -> 'Xét tuyển'
$training_terms = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
$t_slug         = ! empty( $training_terms ) && ! is_wp_error( $training_terms ) ? $training_terms[0]->slug : '';
$t_name         = ! empty( $training_terms ) && ! is_wp_error( $training_terms ) ? mb_strtolower( $training_terms[0]->name, 'UTF-8' ) : '';
$is_chinh_quy   = ( in_array( $t_slug, [ 'chinh-quy', 'chinh-quy-tap-trung' ], true ) || strpos( $t_name, 'chính quy' ) !== false );
$eval_label     = $is_chinh_quy ? 'Thi tuyển' : 'Xét tuyển';

$parsed_batches = [];

foreach ( $batches_data as $b_idx => $b_item ) {
	$raw_name    = trim( $b_item['batch_name'] ?? '' );
	$clean_title = preg_replace( '/^Tuyển sinh\s*/ui', '', $raw_name );
	if ( empty( $clean_title ) ) {
		$clean_title = 'Đợt ' . ( $b_idx + 1 );
	}

	$status         = $b_item['batch_status'] ?? 'dang-nhan';
	$release_period = trim( $b_item['release_period'] ?? '' );
	$app_period     = trim( $b_item['application_period'] ?? '' );
	$review_time    = trim( $b_item['review_time'] ?? '' );
	$eval_time      = trim( $b_item['evaluation_time'] ?? '' );
	$enrol_time     = trim( $b_item['enrollment_time'] ?? '' );

	// Parse evaluation and enrollment if grouped with pipe '|'
	$eval_val  = ( $eval_time !== '-' ) ? $eval_time : '';
	$enrol_val = ( $enrol_time !== '-' ) ? $enrol_time : '';
	if ( empty( $enrol_val ) && strpos( $eval_val, '|' ) !== false ) {
		$parts     = explode( '|', $eval_val );
		$eval_val  = trim( $parts[0] );
		$enrol_val = trim( $parts[1] ?? '' );
	}

	// Standardize evaluation text based on training type
	if ( ! empty( $eval_val ) ) {
		if ( ! $is_chinh_quy ) {
			$eval_val = preg_replace( '/^Thi tuyển\s*/ui', 'Xét tuyển ', $eval_val );
		} else {
			$eval_val = preg_replace( '/^Xét tuyển\s*/ui', 'Thi tuyển ', $eval_val );
		}
	}

	$parsed_batches[] = [
		'name'           => $clean_title,
		'status'         => $status,
		'release_period' => $release_period,
		'app_period'     => $app_period,
		'review_time'    => $review_time,
		'eval_val'       => $eval_val,
		'enrol_val'      => $enrol_val,
	];
}
?>

<!-- SECTION: ADMISSION BATCHES -->
<section id="lich-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6 mb-6">
	<div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
		<div>
			<h2 class="text-lg md:text-2xl font-bold text-slate-900">Kế hoạch tuyển sinh các đợt trong năm</h2>
			<p class="text-xs md:text-sm text-slate-500 mt-0.5">Lịch tiếp nhận hồ sơ, xét tuyển và khai giảng chi tiết</p>
		</div>
		<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-800 text-xs font-bold rounded-full border border-amber-200/80">
			<svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
			Lịch chính thức
		</span>
	</div>

	<!-- Responsive Grid of Batches -->
	<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
		<?php foreach ( $parsed_batches as $batch ) : 
			$status = $batch['status'];
			$is_active = ( $status === 'dang-nhan' );
			$is_upcoming = ( $status === 'sap-mo' );
			$is_closed = ( $status === 'da-dong' || $status === 'het-han' );
		?>
			<div class="rounded-xl border <?php echo $is_active ? 'border-[#00308b]/40 bg-blue-50/20' : 'border-slate-200/70 bg-white'; ?> p-4 flex flex-col justify-between">
				<div>
					<div class="flex items-center justify-between gap-2 mb-3">
						<h3 class="font-bold text-slate-900 text-sm sm:text-base"><?php echo esc_html( $batch['name'] ); ?></h3>
						<?php if ( $is_active ) : ?>
							<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
								<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
								Đang nhận hồ sơ
							</span>
						<?php elseif ( $is_upcoming ) : ?>
							<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
								Sắp mở
							</span>
						<?php else : ?>
							<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
								Đã đóng
							</span>
						<?php endif; ?>
					</div>

					<div class="space-y-2 text-xs sm:text-sm text-slate-600">
						<?php if ( ! empty( $batch['app_period'] ) ) : ?>
							<div class="flex items-start gap-2">
								<span class="font-medium text-slate-400 shrink-0">Hạn hồ sơ:</span>
								<span class="font-semibold text-slate-800"><?php echo esc_html( $batch['app_period'] ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $batch['eval_val'] ) ) : ?>
							<div class="flex items-start gap-2">
								<span class="font-medium text-slate-400 shrink-0"><?php echo esc_html( $eval_label ); ?>:</span>
								<span class="font-semibold text-slate-800"><?php echo esc_html( $batch['eval_val'] ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $batch['enrol_val'] ) ) : ?>
							<div class="flex items-start gap-2">
								<span class="font-medium text-slate-400 shrink-0">Khai giảng:</span>
								<span class="font-semibold text-slate-800"><?php echo esc_html( $batch['enrol_val'] ); ?></span>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
					<a href="#dieu-kien-ho-so" class="text-xs font-bold text-[#00308b] hover:underline inline-flex items-center gap-1">
						<span>Xem hồ sơ cần nộp</span>
						<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
					</a>
					<a href="#register" class="text-xs font-bold px-3 py-1.5 rounded-lg <?php echo $is_active ? 'bg-[#00308b] text-white hover:bg-[#002266]' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?> transition-colors">
						Đăng ký đợt này
					</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
