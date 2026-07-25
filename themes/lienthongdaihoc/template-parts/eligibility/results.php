<?php
/**
 * Eligibility Checker — Results Display (Progressive Flow)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Years range for Advanced Verification
$current_year = (int) date( 'Y' );
$years = range( $current_year - 18, $current_year - 70 );
?>

<!-- Summary Header -->
<div id="elig-results-header" class="elig-results-header hidden">
	<div class="elig-results-icon" id="elig-results-icon">✅</div>
	<h2 class="elig-results-title" id="elig-results-title">Kết quả đối chiếu sơ bộ</h2>
	<p class="elig-results-subtitle" id="elig-results-subtitle"></p>
</div>

<!-- Input Summary -->
<div id="elig-input-summary" class="elig-input-summary hidden">
	<div class="elig-summary-row">
		<span class="elig-summary-label">Hồ sơ đã nhập:</span>
		<div id="elig-summary-tags" class="elig-summary-tags"></div>
		<button type="button" id="elig-retry" class="elig-link-btn flex items-center gap-1">✏️ Chỉnh sửa</button>
	</div>
</div>

<!-- Eligible Programs -->
<div id="elig-program-list" class="elig-program-list"></div>

<!-- No Results -->
<div id="elig-no-results" class="elig-no-results hidden">
	<h3>Chưa tìm thấy chương trình phù hợp 100%</h3>
	<p>Tuy nhiên, các chuyên viên tuyển sinh của chúng tôi vẫn có thể hỗ trợ đối chiếu điều kiện đặc cách của từng trường dành riêng cho bạn.</p>
	<div class="elig-no-results-actions">
		<button type="button" id="elig-no-results-retry" class="elig-btn elig-btn-secondary">Thử lại với điều kiện khác</button>
	</div>
</div>

<!-- Alternatives -->
<div id="elig-alternatives" class="elig-alternatives hidden mt-8">
	<h3 class="font-bold text-slate-800 text-lg mb-4">💡 Gợi ý chương trình liên quan</h3>
	<div id="elig-alternatives-list" class="elig-program-list"></div>
</div>

<!-- Contextual Progressive Lead Form Section -->
<div id="elig-lead-section" class="elig-lead-form mt-10 border border-slate-200 bg-gradient-to-tr from-[#EFF6FF]/40 to-white p-6 md:p-8 rounded-3xl shadow-sm">
	<div class="elig-lead-context mb-6 space-y-2">
		<span class="inline-block bg-blue-50 text-brand-primary text-[10px] font-extrabold px-2.5 py-1 rounded-lg uppercase tracking-wider">
			Kiểm tra chính xác hồ sơ của bạn
		</span>
		<h3 class="text-xl font-black text-slate-900 leading-tight">Yêu cầu đối chiếu điều kiện nhập học thực tế</h3>
		<p class="text-slate-500 text-xs md:text-sm leading-relaxed">
			Một số điều kiện tuyển sinh nâng cao và chính sách đặc cách cần được xác minh trực tiếp. Vui lòng để lại thông tin nhận kết quả tư vấn chi tiết từ trường.
		</p>
	</div>

	<!-- Step 1: Lead Capture Form -->
	<form id="elig-consultation-form" class="space-y-4" autocomplete="off">
		<input type="hidden" name="elig_check_id" id="elig-check-id" value="">
		<input type="hidden" name="elig_program_id" id="elig-program-id" value="">
		
		<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
			<div class="space-y-2">
				<label class="block text-xs font-bold text-slate-700">Họ và tên *</label>
				<input type="text" name="cf_name" id="elig-lead-name" class="elig-input" placeholder="Ví dụ: Nguyễn Văn A" required>
			</div>
			<div class="space-y-2">
				<label class="block text-xs font-bold text-slate-700">Số điện thoại liên hệ *</label>
				<input type="tel" name="cf_phone" id="elig-lead-phone" class="elig-input" placeholder="Ví dụ: 0912345678" required>
			</div>
		</div>
		<div class="space-y-2">
			<label class="block text-xs font-bold text-slate-700">Email nhận thông báo (Không bắt buộc)</label>
			<input type="email" name="cf_email" id="elig-lead-email" class="elig-input" placeholder="Ví dụ: name@example.com">
		</div>

		<button type="submit" id="elig-lead-submit-btn" class="elig-btn elig-btn-primary elig-btn-full w-full py-3.5 text-sm font-bold flex justify-center items-center gap-2">
			<span>GỬI YÊU CẦU KIỂM TRA HỒ SƠ</span>
		</button>
	</form>

	<!-- Step 2: Advanced Verification Section (Hidden initially, shown after lead success) -->
	<div id="elig-advanced-verification-section" class="hidden space-y-6 pt-6 border-t border-slate-200 animate-fade-in">
		<div class="space-y-2">
			<span class="inline-block bg-emerald-50 text-emerald-600 text-[10px] font-extrabold px-2.5 py-1 rounded-lg uppercase tracking-wider">
				Bước nâng cao
			</span>
			<h4 class="text-lg font-black text-slate-900 leading-tight">Gửi kèm bằng cấp để đối chiếu chính xác hơn</h4>
			<p class="text-slate-500 text-xs leading-relaxed">
				Cung cấp thêm thông tin bằng cấp của bạn sẽ giúp tư vấn viên thẩm định hồ sơ tuyển sinh nhanh và chính xác hơn 100%. (Không bắt buộc)
			</p>
		</div>

		<form id="elig-advanced-verify-form" class="space-y-4" autocomplete="off">
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<!-- Trường học trước đây -->
				<div class="space-y-2">
					<label class="block text-xs font-bold text-slate-700">Trường học trước đây (THPT / Cao đẳng / Đại học cũ)</label>
					<input type="text" name="previous_school" class="elig-input" placeholder="Ví dụ: Cao đẳng Kinh tế, THPT Chu Văn An...">
				</div>

				<!-- Năm sinh / Năm tốt nghiệp -->
				<div class="space-y-2">
					<label class="block text-xs font-bold text-slate-700">Năm sinh</label>
					<select name="graduation" class="elig-select">
						<option value="">-- Chọn năm sinh --</option>
						<?php foreach ( $years as $y ) : ?>
							<option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<!-- Document upload field layout -->
			<div class="space-y-2 border-t border-slate-100 pt-4 mt-2">
				<label class="block text-xs font-bold text-slate-700">Hình ảnh bằng cấp hiện tại (Nếu có)</label>
				<div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
					<!-- File Upload Container -->
					<div class="relative border-2 border-dashed border-slate-200 hover:border-brand-primary rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer transition-all bg-slate-50/50 hover:bg-slate-50" onclick="document.getElementById('degree-file-input').click()">
						<input type="file" id="degree-file-input" name="degree_file" accept="image/*,application/pdf" class="hidden">
						<svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
						</svg>
						<span id="degree-file-label" class="text-xs text-slate-500 font-bold text-center">Tải ảnh hoặc PDF bằng cấp lên</span>
					</div>
					<!-- Link paste -->
					<div class="space-y-2">
						<input type="text" name="degree_link" class="elig-input" placeholder="Hoặc dán đường dẫn (link) ảnh bằng cấp...">
						<p class="text-[10px] text-slate-400 font-medium leading-tight">Chấp nhận ảnh chụp bằng cấp dạng PNG, JPG, JPEG hoặc tài liệu PDF dưới 10MB.</p>
					</div>
				</div>
			</div>

			<button type="submit" id="elig-advanced-submit-btn" class="elig-btn elig-btn-secondary elig-btn-full w-full py-3.5 text-sm font-bold flex justify-center items-center gap-2">
				<span>GỬI HỒ SƠ XÁC MINH</span>
			</button>
		</form>
	</div>
</div>

<!-- Disclaimer Box -->
<div class="elig-disclaimer mt-8 p-4 bg-slate-100 border-l-4 border-amber-500 rounded-xl text-xs text-slate-600 leading-relaxed shadow-sm">
	<p><strong>* Lưu ý quan trọng:</strong> Kết quả trên được đối chiếu tự động dựa trên thông tin bạn cung cấp và dữ liệu chương trình hiện có trên hệ thống. Đây là kết quả đánh giá sơ bộ và không thay thế cho quyết định tuyển sinh chính thức của các trường. Điều kiện nhập học thực tế có thể thay đổi tùy theo quy chế tuyển sinh cụ thể, hồ sơ văn bằng, bảng điểm thực tế của bạn tại thời điểm nộp hồ sơ.</p>
</div>

<!-- CTA Bar -->
<div class="elig-cta-bar">
	<div class="elig-cta-channels">
		<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', get_field( 'global_hotline', 'options' ) ?: '' ) ); ?>" class="elig-cta-card elig-cta-phone">
			<span class="elig-cta-icon">📞</span>
			<span class="elig-cta-label">Gọi hotline</span>
			<span class="elig-cta-value"><?php echo esc_html( get_field( 'global_hotline', 'options' ) ?: '1900 xxxx' ); ?></span>
		</a>
		<?php $zalo = get_field( 'global_zalo_url', 'options' ); if ( $zalo ) : ?>
		<a href="<?php echo esc_url( $zalo ); ?>" target="_blank" class="elig-cta-card elig-cta-zalo" rel="noopener">
			<span class="elig-cta-icon">💬</span>
			<span class="elig-cta-label">Nhắn Zalo</span>
			<span class="elig-cta-value">Chat ngay</span>
		</a>
		<?php endif; ?>
		<a href="#elig-lead-section" class="elig-cta-card elig-cta-form">
			<span class="elig-cta-icon">📋</span>
			<span class="elig-cta-label">Đăng ký tư vấn</span>
			<span class="elig-cta-value">Miễn phí</span>
		</a>
	</div>
</div>
