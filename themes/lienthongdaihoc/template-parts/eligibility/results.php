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
<div id="elig-results-header" class="elig-results-header hidden text-center mb-6">
	<div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 mb-2.5 border border-emerald-200/80 shadow-2xs">
		<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
			<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
		</svg>
	</div>
	<h2 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900 tracking-tight" id="elig-results-title">Kết quả đối chiếu sơ bộ</h2>
	<p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xl mx-auto" id="elig-results-subtitle"></p>
</div>

<!-- Input Summary (Mobile-First Responsive Layout) -->
<div id="elig-input-summary" class="elig-input-summary hidden bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 mb-6 shadow-2xs">
	<div class="flex items-center justify-between gap-3 pb-3 mb-3 border-b border-slate-100">
		<div class="flex items-center gap-2">
			<svg class="w-4 h-4 text-[#00308b] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
			</svg>
			<span class="text-xs font-black uppercase tracking-wider text-slate-700">Hồ sơ đã nhập:</span>
		</div>
		<button type="button" id="elig-retry" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-[#00308b] bg-blue-50 hover:bg-blue-100 transition-colors cursor-pointer border border-blue-100/80 shrink-0">
			<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
			</svg>
			<span>Chỉnh sửa</span>
		</button>
	</div>
	<div id="elig-summary-tags" class="elig-summary-tags flex flex-wrap gap-2 w-full"></div>
</div>

<!-- Eligible Programs -->
<div id="elig-program-list" class="elig-program-list space-y-4"></div>

<!-- No Results -->
<div id="elig-no-results" class="elig-no-results hidden bg-white border border-slate-200/80 rounded-2xl p-6 text-center shadow-xs">
	<h3 class="text-base sm:text-lg font-black text-slate-800">Chưa tìm thấy chương trình phù hợp 100%</h3>
	<p class="text-xs sm:text-sm text-slate-500 mt-2 max-w-md mx-auto">Tuy nhiên, các chuyên viên tuyển sinh của chúng tôi vẫn có thể hỗ trợ đối chiếu điều kiện đặc cách của từng trường dành riêng cho bạn.</p>
	<div class="elig-no-results-actions mt-4">
		<button type="button" id="elig-no-results-retry" class="elig-btn elig-btn-secondary text-xs sm:text-sm">Thử lại với điều kiện khác</button>
	</div>
</div>

<!-- Alternatives -->
<div id="elig-alternatives-section" class="elig-alternatives hidden mt-8">
	<h3 class="font-black text-slate-800 text-base sm:text-lg mb-4 flex items-center gap-2">
		<svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
			<path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.516 0c.85.493 1.508 1.333 1.508 2.316V18" />
		</svg>
		<span>Gợi ý chương trình liên quan</span>
	</h3>
	<div id="elig-alternatives-list" class="elig-program-list space-y-4"></div>
</div>

<!-- Contextual Comprehensive Lead Form Section -->
<div id="elig-lead-section" class="elig-lead-form mt-8 border border-slate-200/90 bg-white p-5 sm:p-7 md:p-8 rounded-2xl shadow-xs relative overflow-hidden">
	<div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#00308b] via-blue-500 to-[#EA580C]"></div>

	<div class="elig-lead-context mb-6 space-y-1.5 pt-1">
		<span class="inline-block bg-blue-50 text-[#00308b] text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-blue-100 uppercase tracking-wider">
			Thẩm định hồ sơ 1:1
		</span>
		<h3 class="text-lg sm:text-xl font-black text-slate-900 leading-tight">Yêu cầu đối chiếu điều kiện nhập học thực tế</h3>
		<p class="text-slate-500 text-xs sm:text-sm leading-relaxed">
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
<div class="elig-disclaimer mt-8 p-4 bg-slate-50 border border-slate-200/80 rounded-xl text-xs text-slate-500 leading-relaxed">
	<p><strong class="text-slate-700">* Lưu ý:</strong> Kết quả trên được đối chiếu tự động dựa trên thông tin bạn cung cấp và cơ sở dữ liệu chương trình. Đây là kết quả đánh giá sơ bộ tham khảo. Điều kiện nhập học chính thức phụ thuộc vào hồ sơ văn bằng, bảng điểm cụ thể và quy chế tuyển sinh của từng trường.</p>
</div>

<!-- CTA Bar -->
<div class="elig-cta-bar mt-8">
	<div class="elig-cta-channels grid grid-cols-1 sm:grid-cols-3 gap-3">
		<?php $elig_hotline = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : ( ( function_exists( 'get_field' ) ? get_field( 'global_hotline', 'options' ) : '' ) ?: '0988 991 496' ); ?>
		<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $elig_hotline ) ); ?>" class="elig-cta-card flex flex-col items-center justify-center p-4 bg-white border border-slate-200/90 rounded-2xl hover:border-blue-400 hover:shadow-xs transition-all text-center group no-underline">
			<div class="w-10 h-10 rounded-xl bg-blue-50 text-[#00308b] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
				<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
				</svg>
			</div>
			<span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Gọi hotline</span>
			<span class="text-sm font-black text-slate-900 mt-0.5"><?php echo esc_html( $elig_hotline ); ?></span>
		</a>

		<?php $elig_zalo = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : ( function_exists( 'get_field' ) ? get_field( 'global_zalo_url', 'options' ) : '' ); if ( $elig_zalo ) : ?>
		<a href="<?php echo esc_url( $elig_zalo ); ?>" target="_blank" class="elig-cta-card flex flex-col items-center justify-center p-4 bg-white border border-slate-200/90 rounded-2xl hover:border-emerald-400 hover:shadow-xs transition-all text-center group no-underline" rel="noopener">
			<div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
				<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.974-.94 4.542 4.542 0 00.56-1.572C3.125 16.92 2 14.606 2 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
				</svg>
			</div>
			<span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nhắn Zalo</span>
			<span class="text-sm font-black text-slate-900 mt-0.5">Chat trực tiếp</span>
		</a>
		<?php endif; ?>

		<a href="#elig-lead-section" class="elig-cta-card flex flex-col items-center justify-center p-4 bg-white border border-slate-200/90 rounded-2xl hover:border-blue-400 hover:shadow-xs transition-all text-center group no-underline">
			<div class="w-10 h-10 rounded-xl bg-blue-50 text-[#00308b] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
				<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
				</svg>
			</div>
			<span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Đăng ký tư vấn</span>
			<span class="text-sm font-black text-slate-900 mt-0.5">Miễn phí 100%</span>
		</a>
	</div>
</div>
