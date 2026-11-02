<?php
/**
 * Program Admission Batches Section (Spacious Horizontal Rows)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id   = isset( $args['program_id'] ) ? (int) $args['program_id'] : get_the_ID();
$batches_data = isset( $args['batches_data'] ) ? $args['batches_data'] : ( function_exists( 'ltdh_get_program_admission_batches' ) ? ltdh_get_program_admission_batches( $program_id ) : get_field( 'admission_batches', $program_id ) );
$enrollment   = isset( $args['enrollment'] ) ? $args['enrollment'] : ( function_exists( 'get_field' ) ? get_field( 'enrollment', $program_id ) : '' );

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

	$parsed_batches[ $b_idx ] = [
		'title'          => $clean_title,
		'status'         => $status,
		'release_period' => ( $release_period !== '-' ) ? $release_period : '',
		'app_period'     => ( $app_period !== '-' ) ? $app_period : '',
		'review_time'    => ( $review_time !== '-' ) ? $review_time : '',
		'eval_val'       => $eval_val,
		'enrol_val'      => $enrol_val,
	];
}

// Find the active batch to display on mobile (default to first 'dang-nhan', or first 'sap-mo', or latest batch)
$active_mobile_batch_idx = null;
foreach ( $parsed_batches as $idx => $batch ) {
	if ( 'dang-nhan' === ( $batch['status'] ?? '' ) ) {
		$active_mobile_batch_idx = $idx;
		break;
	}
}
if ( null === $active_mobile_batch_idx ) {
	foreach ( $parsed_batches as $idx => $batch ) {
		if ( 'sap-mo' === ( $batch['status'] ?? '' ) ) {
			$active_mobile_batch_idx = $idx;
			break;
		}
	}
}
if ( null === $active_mobile_batch_idx && ! empty( $parsed_batches ) ) {
	$active_mobile_batch_idx = array_key_last( $parsed_batches );
}
?>

<!-- SECTION: ADMISSION BATCHES -->
<section id="lich-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-6 mb-8">
	<!-- Header -->
	<div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4 sm:mb-5">
		<div>
			<h2 class="text-lg md:text-2xl font-bold text-slate-900">Kế hoạch tuyển sinh các đợt trong năm</h2>
			<p class="text-xs sm:text-sm text-slate-500 mt-0.5">Lịch tiếp nhận hồ sơ, <?php echo esc_html( mb_strtolower( $eval_label, 'UTF-8' ) ); ?> và khai giảng chi tiết</p>
		</div>
		<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-800 text-xs font-bold rounded-full border border-amber-200/80">
			<svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
			Lịch chính thức
		</span>
	</div>

	<!-- Batches List (Spacious Horizontal Rows) -->
	<div class="space-y-3.5">
		<?php foreach ( $parsed_batches as $b_idx => $b_data ) : 
			$status      = $b_data['status'];
			$is_open     = ( $status === 'dang-nhan' );
			$is_upcoming = ( $status === 'sap-mo' );
			$is_closed   = ( $status === 'da-dong' || $status === 'het-han' );

			if ( $is_open ) {
				$row_class = 'bg-emerald-50/40 border-2 border-emerald-500 shadow-md shadow-emerald-500/10 rounded-2xl p-3 sm:p-4';
			} elseif ( $is_upcoming ) {
				$row_class = 'bg-blue-50/25 border border-blue-200/70 rounded-2xl p-3 sm:p-5';
			} else {
				$row_class = 'bg-slate-50/60 border border-slate-200/60 rounded-2xl p-3 sm:p-4 opacity-80 hover:opacity-100 transition-opacity';
			}

			$is_active_for_mobile = ( $b_idx === $active_mobile_batch_idx );
			$mobile_vis_class     = $is_active_for_mobile ? '' : 'hidden md:block';
		?>
			<div class="transition-all <?php echo esc_attr( trim( $row_class . ' ' . $mobile_vis_class ) ); ?>">
				
				<!-- MOBILE ONLY: Thanh trạng thái đợt tuyển sinh ở đầu (khi ẩn chữ "Đợt X" trên mobile) -->
				<div class="flex items-center justify-between pb-2.5 mb-2.5 border-b border-emerald-200/60 md:hidden">
					<div class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
						<span class="w-2 h-2 rounded-full <?php echo $is_open ? 'bg-emerald-500' : ( $is_upcoming ? 'bg-blue-500' : 'bg-slate-400' ); ?>"></span>
						<span><?php echo $is_open ? 'Đợt tuyển sinh đang mở' : ( $is_upcoming ? 'Đợt tuyển sinh chuẩn bị mở' : 'Đợt tuyển sinh gần nhất' ); ?></span>
					</div>
					<?php if ( $is_open ) : ?>
						<div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-600 text-white font-extrabold text-[11px] shadow-2xs whitespace-nowrap">
							<span class="relative flex h-1.5 w-1.5">
								<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
								<span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-white"></span>
							</span>
							<span>Đang nhận hồ sơ</span>
						</div>
					<?php elseif ( $is_upcoming ) : ?>
						<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 whitespace-nowrap">
							Sắp mở nhận
						</span>
					<?php else : ?>
						<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-200 text-slate-600 whitespace-nowrap">
							Đã kết thúc
						</span>
					<?php endif; ?>
				</div>

				<!-- MAIN ROW: Desktop [Title] [3 Cột mốc] [Badge]; Mobile [3 Cột mốc dàn 1 hàng ngang] -->
				<div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
					
					<!-- 1. Tên đợt (Ẩn trên mobile theo yêu cầu, hiển thị từ tablet/desktop md:flex) -->
					<div class="hidden md:flex w-24 shrink-0 flex-col items-start justify-center gap-1">
						<h3 class="<?php echo $is_open ? 'text-sm font-bold text-slate-900' : 'text-sm font-bold text-slate-700'; ?>">
							<?php echo esc_html( $b_data['title'] ); ?>
						</h3>
					</div>

					<!-- 2. CẢ 3 MỐC THỜI GIAN: Dàn thành 1 hàng ngang (3 cột) trên CẢ MOBILE và DESKTOP -->
					<?php 
					$batch_app_text = $b_data['app_period'];
					if ( empty( $batch_app_text ) && $is_open && ! empty( $enrollment ) ) {
						$batch_app_text = $enrollment;
					}
					?>
					<div class="flex-1 grid grid-cols-3 gap-1.5 sm:gap-6 lg:gap-8 divide-x divide-emerald-200/60 md:divide-none text-center md:text-left -ml-1.5 md:ml-0">
						
						<!-- Cột 1: Hạn nộp hồ sơ -->
						<div class="pl-1.5 md:pl-0 min-w-0">
							<span class="text-[10px] sm:text-xs font-bold sm:font-semibold text-slate-400 sm:text-slate-500 block leading-tight mb-1 uppercase tracking-tight sm:normal-case">Hạn nộp</span>
							<span class="<?php echo $is_open ? 'text-xs sm:text-sm font-black sm:font-bold text-amber-950' : 'text-xs font-medium text-slate-800'; ?> block leading-tight sm:leading-snug">
								<?php echo esc_html( $batch_app_text ?: 'Liên tục' ); ?>
							</span>
						</div>

						<!-- Cột 2: Xét tuyển / Thi tuyển -->
						<div class="pl-1.5 md:pl-0 min-w-0">
							<span class="text-[10px] sm:text-xs font-bold sm:font-semibold text-slate-400 sm:text-slate-500 block leading-tight mb-1 uppercase tracking-tight sm:normal-case"><?php echo esc_html( $eval_label ); ?></span>
							<span class="<?php echo $is_open ? 'text-xs sm:text-sm font-bold text-slate-900' : 'text-xs font-medium text-slate-700'; ?> block leading-tight sm:leading-snug">
								<?php echo esc_html( $b_data['eval_val'] ?: ( $is_chinh_quy ? 'Thi tuyển theo đợt' : 'Xét tuyển hồ sơ' ) ); ?>
							</span>
						</div>

						<!-- Cột 3: Khai giảng -->
						<div class="pl-1.5 md:pl-0 min-w-0">
							<span class="text-[10px] sm:text-xs font-bold sm:font-semibold text-slate-400 sm:text-slate-500 block leading-tight mb-1 uppercase tracking-tight sm:normal-case">Khai giảng</span>
							<span class="<?php echo $is_open ? 'text-xs sm:text-sm font-bold text-[#00308b]' : 'text-xs font-medium text-slate-700'; ?> block leading-tight sm:leading-snug">
								<?php echo esc_html( $b_data['enrol_val'] ?: 'Theo kế hoạch' ); ?>
							</span>
						</div>

					</div>

					<!-- 3. DESKTOP ONLY: Badge bên phải (Trên mobile đã hiển thị gọn ở thanh đầu card) -->
					<div class="hidden md:flex w-auto md:w-44 lg:w-48 shrink-0 items-center justify-end">
						<?php if ( $is_open ) : ?>
							<div class="inline-flex items-center gap-2 px-3 py-2 sm:px-3 sm:py-2.5 rounded-full bg-emerald-600 text-white font-black text-xs sm:text-sm shadow-md ring-4 ring-emerald-500/20 whitespace-nowrap">
								<span class="relative flex h-2.5 w-2.5">
									<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
									<span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
								</span>
								<span>Đang nhận hồ sơ</span>
							</div>
						<?php elseif ( $is_upcoming ) : ?>
							<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 whitespace-nowrap">
								Sắp mở nhận
							</span>
						<?php else : ?>
							<?php if ( $is_closed ) : ?>
								<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-200 text-slate-600 whitespace-nowrap">
									Đã kết thúc
								</span>
							<?php endif; ?>
						<?php endif; ?>
					</div>

				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
