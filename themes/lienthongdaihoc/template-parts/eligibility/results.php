<?php
/**
 * Eligibility Checker — Results Display (Progressive Flow)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Years range for Advanced Verification (Graduation year: current down to 25 years ago)
$current_year = (int) date( 'Y' );
$years = range( $current_year, $current_year - 25 );
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
<div id="elig-alternatives-section" class="elig-alternatives hidden mt-8">
	<h3 class="font-bold text-slate-800 text-lg mb-4">💡 Gợi ý chương trình liên quan</h3>
	<div id="elig-alternatives-list" class="elig-program-list"></div>
</div>

<!-- Contextual Comprehensive Lead Form Section -->
<div id="elig-lead-section" class="elig-lead-form mt-10 border border-slate-200 bg-gradient-to-tr from-[#EFF6FF]/40 to-white p-6 md:p-8 rounded-3xl shadow-sm">
	<div class="elig-lead-context mb-6 space-y-2">
		<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-2.5 py-1 rounded-lg uppercase tracking-wider">
			Kiểm tra chính xác hồ sơ của bạn
		</span>
		<h3 class="text-xl font-black text-slate-900 leading-tight">Yêu cầu đối chiếu điều kiện nhập học thực tế</h3>
		<p class="text-slate-500 text-sm leading-relaxed">
			Cung cấp thông tin và tài liệu (bằng tốt nghiệp, bảng điểm nếu có) để chuyên viên đối chiếu số tín chỉ miễn giảm và thẩm định điều kiện trúng tuyển chính xác nhất.
		</p>
	</div>

	<!-- Unified Lead Capture & Profile Verification Form -->
	<form id="elig-consultation-form" class="space-y-5" autocomplete="off" enctype="multipart/form-data">
		<input type="hidden" name="elig_check_id" id="elig-check-id" value="">
		<input type="hidden" name="elig_program_id" id="elig-program-id" value="">
		
		<!-- Block 1: Contact Info -->
		<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
			<div class="space-y-1.5">
				<label class="block text-sm font-bold text-slate-700">Họ và tên *</label>
				<input type="text" name="cf_name" id="elig-lead-name" class="elig-input" placeholder="Ví dụ: Nguyễn Văn A" required>
			</div>
			<div class="space-y-1.5">
				<label class="block text-sm font-bold text-slate-700">Số điện thoại liên hệ *</label>
				<input type="tel" name="cf_phone" id="elig-lead-phone" class="elig-input" placeholder="Ví dụ: 0912345678" required>
			</div>
		</div>

		<!-- Block 2: Email & Previous School & Graduation Year -->
		<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
			<div class="space-y-1.5">
				<label class="block text-sm font-bold text-slate-700">Email nhận kết quả</label>
				<input type="email" name="cf_email" id="elig-lead-email" class="elig-input" placeholder="Ví dụ: name@example.com">
			</div>
			<div class="space-y-1.5">
				<label class="block text-sm font-bold text-slate-700">Trường đã tốt nghiệp</label>
				<input type="text" name="previous_school" id="elig-lead-school" class="elig-input" placeholder="Ví dụ: CĐ Kinh Tế, CĐ Công Thương...">
			</div>
			<div class="space-y-1.5">
				<label class="block text-sm font-bold text-slate-700">Năm tốt nghiệp</label>
				<select name="graduation" id="elig-lead-graduation" class="elig-select">
					<option value="">-- Chọn năm --</option>
					<?php foreach ( $years as $y ) : ?>
						<option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<!-- Block 3: Degree & Transcript Upload / Links -->
		<div class="border-t border-slate-200/80 pt-4 space-y-3">
			<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
				<label class="block text-sm font-bold text-slate-800">Tài liệu đính kèm đối chiếu (Bằng tốt nghiệp & Bảng điểm)</label>
				<span class="text-xs text-slate-500 font-medium">Không bắt buộc • Ảnh chụp hoặc PDF dưới 5MB</span>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<!-- Degree upload -->
				<div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
					<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
						<span>🎓</span> 1. Bằng tốt nghiệp (TC / CĐ)
					</label>
					<div class="relative border-2 border-dashed border-slate-300 hover:border-brand-primary rounded-xl p-3 flex flex-col items-center justify-center cursor-pointer transition-all bg-white hover:bg-blue-50/30 group" onclick="document.getElementById('degree-file-input').click()">
						<input type="file" id="degree-file-input" name="degree_file" accept="image/*,application/pdf" class="hidden">
						<svg class="w-6 h-6 text-slate-400 group-hover:text-brand-primary transition-colors mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
						</svg>
						<span id="degree-file-label" class="text-xs text-slate-600 font-bold text-center break-all">Tải ảnh hoặc PDF bằng cấp lên</span>
					</div>
					<input type="text" name="degree_link" class="elig-input text-xs" placeholder="Hoặc dán link Google Drive / ảnh bằng cấp...">
				</div>

				<!-- Transcript upload -->
				<div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
					<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
						<span>📊</span> 2. Bảng điểm tốt nghiệp
					</label>
					<div class="relative border-2 border-dashed border-slate-300 hover:border-brand-primary rounded-xl p-3 flex flex-col items-center justify-center cursor-pointer transition-all bg-white hover:bg-blue-50/30 group" onclick="document.getElementById('transcript-file-input').click()">
						<input type="file" id="transcript-file-input" name="transcript_file" accept="image/*,application/pdf" class="hidden">
						<svg class="w-6 h-6 text-slate-400 group-hover:text-brand-primary transition-colors mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
						</svg>
						<span id="transcript-file-label" class="text-xs text-slate-600 font-bold text-center break-all">Tải ảnh hoặc PDF bảng điểm lên</span>
					</div>
					<input type="text" name="transcript_link" class="elig-input text-xs" placeholder="Hoặc dán link Google Drive / ảnh bảng điểm...">
				</div>
			</div>
		</div>

		<!-- Block 4: Message -->
		<div class="space-y-1.5">
			<label class="block text-sm font-bold text-slate-700">Ghi chú hoặc câu hỏi thêm (Nếu có)</label>
			<textarea name="cf_message" id="elig-lead-message" rows="2" class="elig-input" placeholder="Ví dụ: Mong muốn học lớp tối thứ 7 & CN, thắc mắc về miễn giảm môn học..."></textarea>
		</div>

		<!-- Block 5: Consent -->
		<div class="flex items-start gap-2 pt-1 text-xs text-slate-500">
			<input type="checkbox" name="consent_data" id="elig-consent-data" checked required class="mt-0.5 rounded border-slate-300 text-brand-primary focus:ring-brand-primary">
			<label for="elig-consent-data">Tôi đồng ý cho phép xử lý dữ liệu cá nhân theo Nghị định 13/2023/NĐ-CP để nhận thông tin tư vấn tuyển sinh.</label>
		</div>

		<!-- Block 6: reCAPTCHA -->
		<?php if ( function_exists( 'ltdh_render_recaptcha_field' ) ) : ?>
			<?php ltdh_render_recaptcha_field( 'elig_lead' ); ?>
		<?php endif; ?>

		<!-- Submit button -->
		<button type="submit" id="elig-lead-submit-btn" class="elig-btn elig-btn-primary elig-btn-full w-full py-3.5 text-sm md:text-base font-bold flex justify-center items-center gap-2 shadow-md hover:shadow-lg transition-all">
			<span class="elig-btn-text">GỬI YÊU CẦU ĐỐI CHIẾU HỒ SƠ & TƯ VẤN</span>
			<span class="elig-btn-loading hidden">Đang gửi hồ sơ...</span>
		</button>
	</form>
</div>

<!-- Disclaimer Box -->
<div class="elig-disclaimer mt-8 p-4 bg-slate-100 border-l-4 border-amber-500 rounded-xl text-xs text-slate-600 leading-relaxed shadow-sm">
	<p><strong>* Lưu ý quan trọng:</strong> Kết quả trên được đối chiếu tự động dựa trên thông tin bạn cung cấp và dữ liệu chương trình hiện có trên hệ thống. Đây là kết quả đánh giá sơ bộ và không thay thế cho quyết định tuyển sinh chính thức của các trường. Điều kiện nhập học thực tế có thể thay đổi tùy theo quy chế tuyển sinh cụ thể, hồ sơ văn bằng, bảng điểm thực tế của bạn tại thời điểm nộp hồ sơ.</p>
</div>

<!-- CTA Bar -->
<div class="elig-cta-bar">
	<div class="elig-cta-channels">
		<?php $elig_hotline = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : ( ( function_exists( 'get_field' ) ? get_field( 'global_hotline', 'options' ) : '' ) ?: '0988 991 496' ); ?>
		<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $elig_hotline ) ); ?>" class="elig-cta-card elig-cta-phone">
			<span class="elig-cta-icon">📞</span>
			<span class="elig-cta-label">Gọi hotline</span>
			<span class="elig-cta-value"><?php echo esc_html( $elig_hotline ); ?></span>
		</a>
		<?php $elig_zalo = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : ( function_exists( 'get_field' ) ? get_field( 'global_zalo_url', 'options' ) : '' ); if ( $elig_zalo ) : ?>
		<a href="<?php echo esc_url( $elig_zalo ); ?>" target="_blank" class="elig-cta-card elig-cta-zalo" rel="noopener">
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
