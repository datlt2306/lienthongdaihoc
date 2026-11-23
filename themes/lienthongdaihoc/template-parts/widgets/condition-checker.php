<?php
/**
 * Shared Component / Reusable Widget: Condition Checker (Kiểm tra điều kiện)
 * Supports mode="compact" (for single program, sidebars, content) and mode="full" (for /kiem-tra-dieu-kien/).
 *
 * Compliant 100% with Ministry of Education & Training 2026–2027 Admission Regulations:
 * - Circular 06/2026/TT-BGDĐT
 * - Circular 95/2026/TT-BGDĐT
 * - Circular 56/2026/TT-BGDĐT
 * - Circular 52/2026/TT-BGDĐT
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------
// 1. Argument Extraction & Context Resolution
// ----------------------------------------------------
$mode          = ! empty( $args['mode'] ) ? sanitize_key( $args['mode'] ) : 'compact';
$widget_id_raw = ! empty( $args['widget_id'] ) ? sanitize_html_class( $args['widget_id'] ) : '';
$unique_suffix = $widget_id_raw ?: 'ltdh-checker-' . wp_unique_id();
$wrapper_class = ! empty( $args['wrapper_class'] ) ? esc_attr( $args['wrapper_class'] ) : '';
$hide_header   = ! empty( $args['hide_header'] );

$program_id_override = ! empty( $args['program_id'] ) ? intval( $args['program_id'] ) : 0;
$page_context        = function_exists( 'ltdh_elig_get_page_context' ) ? ltdh_elig_get_page_context( $program_id_override ) : [];

// Allow explicit school_id / major_id overrides
if ( ! empty( $args['school_id'] ) ) {
	$s_id = (int) $args['school_id'];
	$page_context['school_id']    = $s_id;
	$page_context['school_title'] = html_entity_decode( get_the_title( $s_id ), ENT_QUOTES, 'UTF-8' );
	$page_context['school_slug']  = get_post_field( 'post_name', $s_id );
}
if ( ! empty( $args['school'] ) ) {
	$page_context['school_slug'] = sanitize_title( $args['school'] );
}
if ( ! empty( $args['major'] ) ) {
	$page_context['major_slug'] = sanitize_title( $args['major'] );
}
if ( ! empty( $args['system'] ) ) {
	$page_context['training_type_slug'] = sanitize_title( $args['system'] );
}

$school_title = $page_context['school_title'] ?? '';
$major_title  = $page_context['major_title'] ?? '';
$system_name  = $page_context['training_type_name'] ?? (
	'tu-xa' === ( $page_context['training_type_slug'] ?? '' ) ? 'Đào tạo từ xa' : (
		'vua-hoc-vua-lam' === ( $page_context['training_type_slug'] ?? '' ) ? 'Vừa làm vừa học' : (
			'chinh-quy' === ( $page_context['training_type_slug'] ?? '' ) ? 'Chính quy' : ''
		)
	)
);

$custom_title    = ! empty( $args['title'] ) ? $args['title'] : '';
$custom_subtitle = ! empty( $args['subtitle'] ) ? $args['subtitle'] : '';

// ----------------------------------------------------
// 2. Mode: FULL (Full-Page Checker on /kiem-tra-dieu-kien/)
// ----------------------------------------------------
if ( 'full' === $mode ) :
	$shared_check = null;
	$param = sanitize_text_field( wp_unslash( $_GET['kq'] ?? ( $_GET['token'] ?? ( $_GET['res'] ?? ( $_GET['check_id'] ?? '' ) ) ) ) );
	if ( ! empty( $param ) && function_exists( 'ltdh_elig_get_check_results' ) ) {
		$shared_check = ltdh_elig_get_check_results( $param );
	}
	?>
	<div id="<?php echo esc_attr( $unique_suffix ); ?>" class="ltdh-condition-checker-full <?php echo esc_attr( $wrapper_class ); ?>" data-checker-widget data-checker-mode="full" data-page-context="<?php echo esc_attr( wp_json_encode( $page_context ) ); ?>">
		<div id="elig-wizard" class="elig-wizard<?php echo $shared_check ? ' hidden' : ''; ?>">
			<?php get_template_part( 'template-parts/eligibility/wizard' ); ?>
		</div>
		<div id="elig-results" class="elig-results<?php echo $shared_check ? '' : ' hidden'; ?>">
			<?php get_template_part( 'template-parts/eligibility/results' ); ?>
		</div>
	</div>
	<?php if ( $shared_check ) : ?>
		<script>
			window.ltdh_initial_results = <?php echo wp_json_encode( $shared_check ); ?>;
		</script>
	<?php endif; ?>
	<?php
	return;
endif;

// ----------------------------------------------------
// 3. Mode: COMPACT (Sidebar / In-Content Shared Widget)
// ----------------------------------------------------
$widget_title    = $custom_title ?: ( $major_title ? 'Kiểm tra điều kiện vào ' . $major_title : ( $school_title ? 'Kiểm tra điều kiện vào ' . $school_title : 'Kiểm tra nhanh điều kiện xét tuyển' ) );
$widget_subtitle = $custom_subtitle ?: 'Đối chiếu 100% Thông tư 06, 95, 56 & 52/2026/TT-BGDĐT';
?>

<div id="<?php echo esc_attr( $unique_suffix ); ?>" 
     class="ltdh-condition-checker-compact not-prose my-6 <?php echo esc_attr( $wrapper_class ); ?>" 
     data-checker-widget 
     data-checker-mode="compact" 
     data-page-context="<?php echo esc_attr( wp_json_encode( $page_context ) ); ?>">
	
	<?php if ( $hide_header ) : ?>
		<!-- STREAMLINED STEP PROGRESS FOR MODAL INSTANCE -->
		<div class="compact-modal-stepper mb-3 px-1">
			<div class="flex items-center justify-between gap-2 mb-2">
				<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-400 text-slate-900 shadow-xs">
					<span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-ping"></span>
					Quy chế mới 2026–2027
				</span>
				<span class="text-xs font-semibold text-slate-500" data-step-indicator>Bước 1 / 2</span>
			</div>
			<div class="compact-step-pills">
				<div class="compact-step-pill is-active" data-pill="1"></div>
				<div class="compact-step-pill" data-pill="2"></div>
				<div class="compact-step-pill" data-pill="3"></div>
			</div>
		</div>
	<?php else : ?>
		<!-- WIDGET HEADER (DEFENSIVE STYLING: GRADIENT NAVY BLUE) -->
		<div class="compact-widget-header bg-gradient-to-br from-[#00308B] to-[#001F5C] text-white p-4 sm:p-5 relative rounded-t-2xl shadow-sm" 
		     style="background: linear-gradient(135deg, #00308B 0%, #001F5C 100%) !important; color: #ffffff !important;">
			<div class="flex items-center justify-between gap-2 mb-1.5">
				<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-400 text-slate-900 shadow-xs">
					<span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-ping"></span>
					Quy chế mới 2026–2027
				</span>
				<span class="text-[11px] font-medium text-blue-200" data-step-indicator>Bước 1 / 2</span>
			</div>

			<h3 class="text-base sm:text-lg font-bold text-white leading-snug">
				<?php echo esc_html( $widget_title ); ?>
			</h3>
			<p class="text-xs text-blue-100/90 mt-0.5">
				<?php echo esc_html( $widget_subtitle ); ?>
			</p>
			<!-- Step Progress Pills -->
			<div class="compact-step-pills">
				<div class="compact-step-pill is-active" data-pill="1"></div>
				<div class="compact-step-pill" data-pill="2"></div>
				<div class="compact-step-pill" data-pill="3"></div>
			</div>
		</div>
	<?php endif; ?>

	<!-- WIDGET BODY (STEPS CONTAINER) -->
	<div class="compact-form-body">

		<!-- ==================== STEP 1 ==================== -->
		<div class="compact-step-pane" data-pane="1">
			<!-- 1. Trình độ hiện có -->
			<div class="compact-field-wrap">
				<label class="compact-input-label">
					1. Trình độ / Văn bằng hiện có <span class="text-rose-500">*</span>
				</label>
				<select class="w-full text-xs sm:text-sm rounded-lg border-slate-300 bg-white py-2 px-3 text-slate-800 shadow-2xs focus:border-[#00308b] focus:ring-1 focus:ring-[#00308b]" 
				        name="compact_education" required>
					<option value="" disabled selected>-- Chọn trình độ hiện có --</option>
					<option value="cao-dang">Cao đẳng</option>
					<option value="trung-cap">Trung cấp</option>
				</select>
			</div>

			<!-- 2. Tình trạng bằng THPT -->
			<div class="compact-field-wrap hidden" data-branch="thpt-status">
				<label class="compact-input-label">
					2. Tình trạng bằng tốt nghiệp THPT <span class="text-rose-500">*</span>
				</label>
				<div class="space-y-1.5">
					<label class="compact-radio-card">
						<input type="radio" name="compact_thpt_status" value="co-bang-thpt" class="text-[#00308b] focus:ring-[#00308b]">
						<span>Đã có bằng tốt nghiệp THPT</span>
					</label>
					<label class="compact-radio-card">
						<input type="radio" name="compact_thpt_status" value="co-giay-van-hoa-thpt" class="text-[#00308b] focus:ring-[#00308b]">
						<span>Có Giấy chứng nhận hoàn thành văn hóa THPT</span>
					</label>
					<label class="compact-radio-card">
						<input type="radio" name="compact_thpt_status" value="chua-co-giay-van-hoa-thpt" class="text-[#00308b] focus:ring-[#00308b]">
						<span>Chưa có bằng &amp; Chưa có giấy chứng nhận văn hóa</span>
					</label>
				</div>
			</div>

			<!-- 3. Thời điểm tốt nghiệp (Tháng/Năm) - Bắt buộc khi Cao đẳng chưa có THPT -->
			<div class="compact-field-wrap hidden" data-branch="grad-date">
				<div class="flex items-center justify-between mb-1">
					<label class="compact-input-label mb-0">
						3. Thời điểm cấp bằng tốt nghiệp <span class="text-rose-500 font-bold">*</span>
					</label>
					<span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded" data-month-calc-badge>
						Tính mốc 24 tháng
					</span>
				</div>
				<div class="grid grid-cols-2 gap-2">
					<div>
						<select name="compact_grad_month" class="w-full text-xs rounded-lg border-slate-300 py-1.5 px-2 bg-white text-slate-800">
							<option value="">Tháng tốt nghiệp</option>
							<?php for ( $m = 1; $m <= 12; $m++ ) : ?>
								<option value="<?php echo $m; ?>" <?php selected( $m, 6 ); ?>>Tháng <?php echo $m; ?></option>
							<?php endfor; ?>
						</select>
					</div>
					<div>
						<input type="number" 
						       name="compact_grad_year" 
						       placeholder="Năm (ví dụ 2024)" 
						       min="1990" 
						       max="<?php echo esc_attr( current_time( 'Y' ) ); ?>" 
						       class="w-full text-xs rounded-lg border-slate-300 py-1.5 px-2 bg-white text-slate-800 focus:border-[#00308b]">
					</div>
				</div>
				<p class="text-[11px] text-slate-500 mt-1">Căn cứ tính mốc ≥ 24 tháng theo quy chế tuyển sinh.</p>
			</div>

			<!-- Next Step Button -->
			<div class="pt-2">
				<button type="button" 
				        class="w-full py-2.5 px-4 rounded-lg bg-[#00308B] hover:bg-blue-800 text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 shadow-sm transition-all cursor-pointer"
				        data-action="next-step">
					<span>Tiếp tục: Học lực &amp; Quỹ thời gian</span>
					<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
				</button>
			</div>
		</div>

		<!-- ==================== STEP 2 ==================== -->
		<div class="compact-step-pane hidden" data-pane="2">
			<!-- 4. Xếp loại tốt nghiệp -->
			<div class="compact-field-wrap">
				<label class="compact-input-label">
					4. Xếp loại tốt nghiệp / Học lực <span class="text-rose-500">*</span>
				</label>
				<select name="compact_grad_grade" class="w-full text-xs sm:text-sm rounded-lg border-slate-300 bg-white py-2 px-3 text-slate-800">
					<option value="kha" selected>Khá</option>
					<option value="xuat-sac-gioi">Xuất sắc / Giỏi</option>
					<option value="trung-binh">Trung bình khá / Trung bình</option>
				</select>
			</div>

			<!-- 5. Mức độ liên quan ngành -->
			<div class="compact-field-wrap">
				<label class="compact-input-label">
					5. Mức độ liên quan ngành đã tốt nghiệp &amp; ngành muốn học <span class="text-rose-500">*</span>
				</label>
				<select name="compact_major_relation" class="w-full text-xs sm:text-sm rounded-lg border-slate-300 bg-white py-2 px-3 text-slate-800">
					<option value="dung-nganh" selected>Đúng ngành / Cùng nhóm ngành</option>
					<option value="nganh-gan">Ngành gần (liên quan)</option>
					<option value="trai-nganh">Khác nhóm ngành (Trái ngành)</option>
				</select>
			</div>

			<!-- 6. Quỹ thời gian học tập -->
			<div class="compact-field-wrap">
				<label class="compact-input-label">
					6. Quỹ thời gian học tập mong muốn <span class="text-rose-500">*</span>
				</label>
				<div class="space-y-1.5">
					<label class="compact-radio-card">
						<input type="radio" name="compact_time_pref" value="online-100" checked class="text-[#00308b] focus:ring-[#00308b]">
						<span>Học trực tuyến (Online 100% qua LMS, thi tập trung)</span>
					</label>
					<label class="compact-radio-card">
						<input type="radio" name="compact_time_pref" value="ngoai-gio-cuoi-tuan" class="text-[#00308b] focus:ring-[#00308b]">
						<span>Chỉ học ngoài giờ hành chính / Cuối tuần</span>
					</label>
					<label class="compact-radio-card">
						<input type="radio" name="compact_time_pref" value="gio-hanh-chinh" class="text-[#00308b] focus:ring-[#00308b]">
						<span>Học tập trung giờ hành chính ban ngày tại trường</span>
					</label>
				</div>
			</div>

			<!-- Navigation Buttons -->
			<div class="flex items-center gap-2 pt-2">
				<button type="button" 
				        class="py-2.5 px-3 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 font-medium text-xs transition-colors cursor-pointer" 
				        data-action="prev-step">
					Quay lại
				</button>
				<button type="button" 
				        class="flex-1 py-2.5 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 shadow-sm transition-all cursor-pointer" 
				        data-action="evaluate-now">
					<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					<span>Kiểm tra điều kiện ngay</span>
				</button>
			</div>
		</div>

		<!-- ==================== STEP 3: RESULTS & LEAD CAPTURE ==================== -->
		<div class="compact-step-pane hidden space-y-3" data-pane="3">
			
			<!-- INSTANT DECISION CARD -->
			<div class="elig-decision-card status-eligible rounded-xl transition-all shadow-2xs p-3.5 sm:p-4" data-result-card>
				<!-- Top Bar: Status Icon + Badges + Title -->
				<div class="flex items-start gap-2.5">
					<div class="decision-icon-badge w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs" data-result-icon>
						<svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
					</div>
					<div class="min-w-0 flex-1">
						<div class="mb-1">
							<span class="elig-badge elig-badge-eligible text-[10px]" data-result-badge>Đủ điều kiện</span>
						</div>
						<h4 class="text-xs sm:text-sm font-bold leading-snug text-slate-900" data-result-title>
							Hồ sơ đủ điều kiện tuyển sinh
						</h4>
					</div>
				</div>

				<!-- Results Content / Regulation Explanation -->
				<div class="mt-2.5 text-xs text-slate-700 leading-relaxed" data-result-summary>
					Đang tính toán kết quả theo Thông tư Bộ GD&ĐT...
				</div>
			</div>

			<!-- CONTEXT CONFLICT ALERT (Cross-School / Modality Recommendation) -->
			<div class="compact-conflict-box hidden rounded-xl p-3.5 transition-all shadow-2xs mb-3" data-conflict-box>
				<div class="flex items-start gap-2.5">
					
					<div class="min-w-0 flex-1 text-xs">
						<strong class="text-amber-950 font-bold block leading-snug" data-conflict-title>Lưu ý quan trọng:</strong>
						<p class="text-amber-900/90 leading-relaxed mt-1 mb-2.5" data-conflict-message></p>
						<a href="#" class="inline-flex items-center justify-center gap-1.5 w-full sm:w-auto px-3.5 py-2 bg-[#00308B] hover:bg-blue-900 active:scale-[0.99] text-white text-xs font-bold rounded-lg shadow-2xs transition-all cursor-pointer group" data-conflict-btn>
							<span data-conflict-btn-text>Xem chương trình phù hợp</span>
							<svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
						</a>
					</div>
				</div>
			</div>

			<!-- COMPACT LEAD FORM -->
			<form class="compact-lead-form bg-slate-50/90 border border-slate-200/90 p-3.5 sm:p-4 rounded-xl shadow-2xs" data-compact-lead-form>
				<div class="flex items-center justify-between gap-2 mb-2.5 pb-2 border-b border-slate-200/60">
					<div class="flex items-center gap-1.5 font-bold text-xs text-slate-800 uppercase tracking-wide">
						<svg class="w-3.5 h-3.5 text-[#00308B]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
						<span>Đăng ký nhận hướng dẫn hồ sơ</span>
					</div>
				</div>

				<!-- Hidden evaluation metadata payload fields -->
				<input type="hidden" name="cf_education" value="">
				<input type="hidden" name="cf_thpt_status" value="">
				<input type="hidden" name="cf_grad_month" value="">
				<input type="hidden" name="cf_grad_year" value="">
				<input type="hidden" name="cf_months_since_grad" value="">
				<input type="hidden" name="cf_grad_grade" value="">
				<input type="hidden" name="cf_major_relation" value="">
				<input type="hidden" name="cf_time_pref" value="">
				<input type="hidden" name="cf_decision_status" value="">
				<input type="hidden" name="cf_priority_level" value="">
				<input type="hidden" name="cf_suitable_training_types" value="">
				<input type="hidden" name="context_program_id" value="<?php echo esc_attr( $page_context['program_id'] ?? 0 ); ?>">
				<input type="hidden" name="context_school_id" value="<?php echo esc_attr( $page_context['school_id'] ?? 0 ); ?>">
				<input type="hidden" name="context_major_id" value="<?php echo esc_attr( $page_context['major_id'] ?? 0 ); ?>">
				<input type="hidden" name="page_url" value="<?php echo esc_url( $page_context['page_url'] ?? '' ); ?>">

				<!-- Anti-spam Honeypot -->
				<div style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">
					<input type="text" name="website_confirm" tabindex="-1" autocomplete="off">
				</div>

				<div class="space-y-2">
					<div>
						<input type="text" 
						       name="cf_name" 
						       placeholder="Họ và tên của bạn *" 
						       required 
						       class="w-full text-xs rounded-lg border-slate-300 py-2 px-3 bg-white text-slate-800 placeholder-slate-400 shadow-2xs focus:border-[#00308b] focus:ring-1 focus:ring-[#00308b]">
					</div>
					<div>
						<input type="tel" 
						       name="cf_phone" 
						       placeholder="Số điện thoại / Zalo *" 
						       required 
						       pattern="[0-9]{10,11}" 
						       class="w-full text-xs rounded-lg border-slate-300 py-2 px-3 bg-white text-slate-800 placeholder-slate-400 shadow-2xs focus:border-[#00308b] focus:ring-1 focus:ring-[#00308b]">
					</div>
					<div>
						<input type="email" 
						       name="cf_email" 
						       placeholder="Email nhận thông báo (tùy chọn)" 
						       class="w-full text-xs rounded-lg border-slate-300 py-2 px-3 bg-white text-slate-800 placeholder-slate-400 shadow-2xs focus:border-[#00308b] focus:ring-1 focus:ring-[#00308b]">
					</div>

					<label class="flex items-center gap-2 text-[11px] text-slate-600 cursor-pointer pt-0.5 select-none">
						<input type="checkbox" name="is_group_reg" value="1" class="rounded text-[#00308b] focus:ring-[#00308b]">
						<span>Đăng ký theo nhóm (Nhận học bổng đồng hành)</span>
					</label>

					<div class="pt-1">
						<button type="submit" 
						        class="w-full py-2.5 px-3 rounded-lg bg-[#00308B] hover:bg-blue-900 active:scale-[0.99] text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 shadow-sm transition-all cursor-pointer" 
						        data-lead-submit-btn>
							<span>Gửi hồ sơ thẩm định ngay</span>
							<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
						</button>
					</div>

					<div class="text-center pt-0.5">
						<button type="button" class="inline-flex items-center gap-1 text-[11px] text-slate-500 hover:text-slate-800 underline cursor-pointer" data-action="reset-check">
							<svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
							<span>Kiểm tra lại với hồ sơ khác</span>
						</button>
					</div>
				</div>

				<div class="flex items-center justify-center gap-1 text-[10px] text-slate-400 mt-2">
					<svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
					<span>Bảo mật thông tin theo Nghị định 13/2023/NĐ-CP.</span>
				</div>
			</form>
		</div>

	</div>
</div>
