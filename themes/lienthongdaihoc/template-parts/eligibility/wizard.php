<?php
/**
 * Eligibility Checker — Unified Single-Page Form (2026–2027 Regulations)
 *
 * Implements 6 core input fields with smart conditional branching:
 * 1. Trình độ / Văn bằng hiện có (Cao đẳng, Trung cấp)
 * 2. Tình trạng bằng THPT (Đã có bằng THPT, Giấy chứng nhận văn hóa THPT, Chưa có bằng & giấy)
 * 3. Thời điểm công nhận tốt nghiệp (Tháng/Năm — bắt buộc khi CĐ chưa có THPT)
 * 4. Xếp loại tốt nghiệp / Học lực (Xuất sắc/Giỏi, Khá, Trung bình khá/Trung bình)
 * 5. Mức độ liên quan ngành (Đúng ngành/Cùng nhóm, Ngành gần, Trái ngành) + Ngành dự tuyển
 * 6. Quỹ thời gian học tập (Hành chính, Ngoài giờ/Cuối tuần, Online 100%)
 *
 * Fully compliant with Circulars 06/2026, 95/2026, 56/2026, 52/2026/TT-BGDĐT.
 *
 * @package lienthongdaihoc
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Load majors for dropdown search
$majors = get_posts( [
	'post_type'      => 'major',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
] );

$current_year = (int) date( 'Y' );
$years = range( $current_year, $current_year - 25 );
$months = range( 1, 12 );
?>

<div class="elig-form-container bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-7 md:p-8 shadow-xs">

	<!-- Header Banner: New 2026-2027 Regulations Notice -->
	<div class="mb-6 p-4 rounded-xl bg-blue-50/70 border border-blue-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
		<div class="flex items-center gap-2.5">
			<span class="w-8 h-8 rounded-lg bg-[#00308b] text-white text-sm font-black inline-flex items-center justify-center shrink-0 shadow-2xs">⚖️</span>
			<div>
				<h3 class="text-xs sm:text-sm font-extrabold text-[#00308b] uppercase tracking-wider m-0">Hệ thống đối chiếu quy chế tuyển sinh 2026–2027</h3>
				<p class="text-[11px] sm:text-xs text-slate-600 mt-0.5 m-0 leading-tight">Cập nhật chuẩn Thông tư 06/2026, Thông tư 95/2026, Thông tư 56/2026 và Thông tư 52/2026/TT-BGDĐT</p>
			</div>
		</div>
		<span class="text-[11px] font-bold text-emerald-800 bg-emerald-100/80 border border-emerald-200 px-2.5 py-1 rounded-full shrink-0 flex items-center gap-1">
			<span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
			Quy chế mới nhất Bộ GD&ĐT
		</span>
	</div>

	<form id="elig-unified-form" class="space-y-7" autocomplete="off">
		
		<!-- Section 1: Hồ sơ văn bằng hiện tại của bạn -->
		<div class="elig-section space-y-4">
			<div class="flex items-center gap-2.5 pb-2.5 border-b border-slate-100">
				<span class="w-6 h-6 rounded-full bg-blue-50 text-[#00308b] text-xs font-black inline-flex items-center justify-center border border-blue-200/60 shrink-0">1</span>
				<h2 class="text-base sm:text-lg font-black text-slate-800 tracking-tight">Hồ sơ học vấn & văn bằng hiện có</h2>
			</div>
			
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
				<!-- Trường 1: Trình độ văn bằng hiện có -->
				<div class="space-y-1.5">
					<label class="block text-xs sm:text-sm font-bold text-slate-700">Trình độ / Văn bằng hiện có *</label>
					<select name="education" id="field-education" class="elig-select select-education" required>
						<option value="cao-dang" selected>Cao đẳng</option>
						<option value="trung-cap">Trung cấp</option>
					</select>
					<span class="text-[11px] text-slate-400 block">Chọn văn bằng bạn đã tốt nghiệp hoặc sắp nhận bằng.</span>
				</div>

				<!-- Trường 4: Xếp loại tốt nghiệp / Học lực -->
				<div class="space-y-1.5">
					<label class="block text-xs sm:text-sm font-bold text-slate-700">Xếp loại tốt nghiệp / Học lực *</label>
					<select name="graduation_grade" id="field-graduation-grade" class="elig-select" required>
						<option value="kha" selected>Khá</option>
						<option value="xuat-sac-gioi">Xuất sắc / Giỏi</option>
						<option value="trung-binh">Trung bình khá / Trung bình</option>
					</select>
					<span class="text-[11px] text-slate-400 block">Dùng để đối chiếu ngưỡng tuyển sinh chuẩn Thông tư 95/2026 và xét miễn giảm môn.</span>
				</div>
			</div>

			<!-- Trường 2: Tình trạng bằng THPT (Chỉ hiện khi chọn Trung cấp hoặc Cao đẳng) -->
			<div id="field-thpt-status-wrap" class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/70 space-y-2 transition-all duration-300">
				<div class="flex items-center justify-between">
					<label class="block text-xs sm:text-sm font-bold text-slate-800">Tình trạng bằng tốt nghiệp THPT *</label>
					<span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-200/60">Yếu tố quyết định 2026–2027</span>
				</div>
				<select name="thpt_status" id="field-thpt-status" class="elig-select">
					<option value="co-bang-thpt" selected>1. Đã có bằng tốt nghiệp THPT</option>
					<option value="co-giay-van-hoa-thpt">2. Chưa có bằng THPT, nhưng đã có Giấy chứng nhận hoàn thành đủ khối lượng kiến thức văn hóa THPT</option>
					<option value="chua-co-giay-van-hoa-thpt">3. Chưa có bằng THPT và chưa có Giấy chứng nhận hoàn thành kiến thức văn hóa THPT</option>
				</select>
				<p id="thpt-status-hint" class="text-[11px] text-slate-500 m-0 leading-normal">
					💡 <em>Lưu ý pháp lý:</em> Theo Thông tư 95/2026/TT-BGDĐT, từ 01/01/2027 quy định về khối lượng văn hóa THPT sẽ được siết chặt đối với người học liên thông.
				</p>
			</div>

			<!-- Trường 3: Thời điểm được công nhận tốt nghiệp (Tháng/Năm) -->
			<div id="field-graduation-date-wrap" class="p-4 sm:p-5 rounded-xl bg-slate-50/80 border border-slate-200/70 space-y-3 transition-all duration-300">
				<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
					<label class="text-xs sm:text-sm font-bold text-slate-800 leading-snug">
						Thời điểm cấp bằng / công nhận tốt nghiệp (Cao đẳng / Trung cấp)
						<span id="grad-required-indicator" class="inline-block text-rose-500 font-bold text-xs whitespace-nowrap ml-1 hidden">* (Bắt buộc)</span>
					</label>
					<div class="shrink-0 flex items-center">
						<span id="grad-months-badge" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full border shadow-2xs whitespace-nowrap hidden"></span>
					</div>
				</div>
				<div class="grid grid-cols-2 gap-3 sm:gap-4 max-w-md">
					<div>
						<label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tháng tốt nghiệp</label>
						<select name="grad_month" id="field-grad-month" class="elig-select">
							<?php foreach ( $months as $m ) : ?>
								<option value="<?php echo esc_attr( $m ); ?>" <?php selected( $m, 6 ); ?>><?php echo esc_html( 'Tháng ' . $m ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div>
						<label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Năm tốt nghiệp *</label>
						<select name="grad_year" id="field-grad-year" class="elig-select" required>
							<?php foreach ( $years as $y ) : ?>
								<option value="<?php echo esc_attr( $y ); ?>" <?php selected( $y, 2024 ); ?>><?php echo esc_html( $y ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<p id="grad-date-hint" class="text-[11px] text-slate-500 m-0 leading-normal">
					📅 Dùng để tính chính xác số tháng tốt nghiệp (đặc biệt đối với trường hợp Cao đẳng chưa có bằng THPT cần đạt mốc ≥ 24 tháng).
				</p>
			</div>

		</div>

		<!-- Section 2: Nguyện vọng đào tạo & Quỹ thời gian -->
		<div class="elig-section space-y-4 pt-4 border-t border-slate-100">
			<div class="flex items-center gap-2.5 pb-2.5 border-b border-slate-100">
				<span class="w-6 h-6 rounded-full bg-blue-50 text-[#00308b] text-xs font-black inline-flex items-center justify-center border border-blue-200/60 shrink-0">2</span>
				<h2 class="text-base sm:text-lg font-black text-slate-800 tracking-tight">Nguyện vọng đào tạo & Quỹ thời gian học tập</h2>
			</div>
			
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
				<!-- Chuyên ngành đại học mong muốn -->
				<div class="space-y-1.5 relative" data-search-select>
					<label class="block text-xs sm:text-sm font-bold text-slate-700">Chuyên ngành đại học mong muốn *</label>
					<input type="text" class="elig-search-input" placeholder="Gõ để tìm ngành (VD: Công nghệ thông tin, Kế toán...)" autocomplete="off">
					<input type="hidden" name="desired_major" class="elig-select elig-search-value" required>
					<div class="elig-search-dropdown absolute w-full max-h-48 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-lg hidden z-50 mt-1">
						<div class="p-2 text-xs font-bold text-slate-400 border-b border-slate-100 uppercase tracking-wider">Danh sách chuyên ngành đào tạo</div>
						<div class="elig-search-options">
							<div class="elig-search-option-item p-2.5 text-sm cursor-pointer hover:bg-slate-50 font-semibold text-slate-500 border-b border-slate-50" data-value="">-- Chọn chuyên ngành --</div>
							<?php foreach ( $majors as $m ) : ?>
								<div class="elig-search-option-item p-2.5 text-sm cursor-pointer hover:bg-slate-50 font-semibold text-slate-700 border-b border-slate-50 last:border-0" data-value="<?php echo esc_attr( $m->ID ); ?>"><?php echo esc_html( $m->post_title ); ?></div>
							<?php endforeach; ?>
						</div>
					</div>
					<span class="text-[11px] text-slate-400 block">Tìm và chọn ngành bạn dự định học lấy bằng cử nhân / kỹ sư.</span>
				</div>

				<!-- Trường 5: Mức độ liên quan giữa ngành đã tốt nghiệp và ngành dự tuyển -->
				<div class="space-y-1.5">
					<label class="block text-xs sm:text-sm font-bold text-slate-700">Mức độ liên quan ngành đã tốt nghiệp & ngành dự tuyển *</label>
					<select name="major_relation" id="field-major-relation" class="elig-select" required>
						<option value="dung-nganh" selected>Đúng ngành / Cùng nhóm ngành đào tạo</option>
						<option value="nganh-gan">Ngành gần (Liên quan một phần chương trình)</option>
						<option value="trai-nganh">Khác nhóm ngành (Trái ngành hoàn toàn)</option>
					</select>
					<span class="text-[11px] text-slate-400 block">Căn cứ theo Thông tư 95/2026/TT-BGDĐT để xác định điều kiện chuyển đổi và môn bổ sung.</span>
				</div>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 pt-2">
				<!-- Trường 6: Quỹ thời gian học tập mong muốn (Thông tư 56/2026) -->
				<div class="space-y-1.5">
					<label class="block text-xs sm:text-sm font-bold text-slate-700">Quỹ thời gian học tập mong muốn *</label>
					<select name="study_time_preference" id="field-time-preference" class="elig-select" required>
						<option value="online-100" selected>Học trực tuyến (Online 100% qua LMS, thi tập trung)</option>
						<option value="ngoai-gio-cuoi-tuan">Chỉ học được ngoài giờ hành chính / Cuối tuần (Tối, T7 & CN)</option>
						<option value="gio-hanh-chinh">Học tập trung giờ hành chính ban ngày tại trường</option>
					</select>
					<p class="text-[11px] text-slate-500 m-0 leading-normal">
						⚖️ <em>Quy định Điều 5 Thông tư 56/2026/TT-BGDĐT:</em> Hệ Chính quy liên thông bắt buộc học trực tiếp trong giờ hành chính &gt;50% chương trình.
					</p>
				</div>

				<!-- Cơ sở / Khu vực học mong muốn (Tùy chọn) -->
				<div class="space-y-1.5">
					<label class="block text-xs sm:text-sm font-bold text-slate-700">Khu vực / Cơ sở học tập mong muốn</label>
					<select name="campus" class="elig-select">
						<option value="">Toàn quốc / Học trực tuyến (Online)</option>
						<?php 
						$campuses = get_terms( [ 'taxonomy' => 'campus', 'hide_empty' => false ] );
						if ( ! is_wp_error( $campuses ) && ! empty( $campuses ) ) {
							foreach ( $campuses as $cp ) {
								if ( $cp->slug === 'online' ) {
									continue;
								}
								echo '<option value="' . esc_attr( $cp->slug ) . '">' . esc_html( $cp->name ) . '</option>';
							}
						}
						?>
					</select>
					<span class="text-[11px] text-slate-400 block">Áp dụng khi chọn hình thức học có lịch học trực tiếp tại trạm/cơ sở đào tạo.</span>
				</div>
			</div>
		</div>

		<!-- Submit Button -->
		<div class="pt-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
			<div class="text-xs text-slate-500 flex items-center gap-1.5">
				<svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
				</svg>
				<span>Đối chiếu thuật toán 100% bảo mật & miễn phí</span>
			</div>

			<button type="button" id="elig-unified-submit" class="elig-btn elig-btn-primary px-8 py-3.5 text-sm sm:text-base font-semibold tracking-tight w-full sm:w-auto rounded-full bg-[#0071E3] hover:bg-[#0077ED] text-white shadow-none hover:shadow-xs active:scale-98 transition-all cursor-pointer">
				<span class="elig-btn-text flex items-center gap-2 justify-center">
					<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
					</svg>
					<span>Kiểm tra điều kiện & xem chương trình phù hợp</span>
				</span>
				<span class="elig-btn-loading hidden">Đang đối chiếu quy chế tuyển sinh...</span>
			</button>
		</div>

	</form>

</div>
