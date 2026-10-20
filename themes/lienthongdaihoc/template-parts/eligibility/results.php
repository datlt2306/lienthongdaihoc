<?php
/**
 * Eligibility Checker — Results Display (Progressive Flow)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Years range for Advanced Verification (Graduation year: current down to 25 years ago)
$current_year = (int) date( 'Y' );
$years = range( $current_year, $current_year - 25 );
?>

<!-- Admissions Assessment Header Card (Clean Website Concept) -->
<div id="elig-results-header" class="elig-results-hero hidden bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-7 shadow-xs mb-6">
	<!-- Top Bar: Badge & Action Group -->
	<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
		<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#00308b] text-xs font-bold border border-blue-100 w-fit">
			<span class="w-2 h-2 rounded-full bg-[#00308b]"></span>
			<span class="uppercase tracking-wider">Kết quả đối chiếu hồ sơ</span>
		</div>

		<!-- Action Group: Share Link & Re-check -->
		<div class="flex items-center gap-2">
			<!-- Share / Copy URL button -->
			<button type="button" id="elig-copy-share-btn" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 active:scale-95 border border-slate-200 transition-all cursor-pointer shadow-2xs" title="Sao chép link kết quả này để chia sẻ">
				<svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
				</svg>
				<span id="elig-copy-btn-text">Sao chép link kết quả</span>
			</button>

			<!-- Re-check / Edit button -->
			<button type="button" id="elig-retry" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-[#00308b] hover:text-[#002266] bg-blue-50 hover:bg-blue-100 transition-all cursor-pointer border border-blue-100 shadow-2xs">
				<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
				</svg>
				<span>Đối chiếu lại</span>
			</button>
		</div>
	</div>

	<!-- Main Assessment Title & Subtitle -->
	<div class="my-4">
		<h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug" id="elig-results-title">
			Tìm thấy chương trình phù hợp với hồ sơ của bạn!
		</h2>
		<p class="text-slate-600 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed" id="elig-results-subtitle">
			Dưới đây là các chương trình đào tạo tương thích nhất với trình độ văn bằng và nguyện vọng của bạn.
		</p>
	</div>

	<!-- Candidate Profile Tags Integrated -->
	<div id="elig-input-summary" class="pt-3 border-t border-slate-100">
		<div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Hồ sơ đã đối chiếu:</div>
		<div id="elig-summary-tags" class="flex flex-wrap items-center gap-2"></div>
	</div>
</div>

<!-- Floating Toast Notification for Link Copied -->
<div id="elig-share-toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 bg-slate-900/95 text-white text-xs font-bold rounded-2xl shadow-2xl border border-slate-700/80 backdrop-blur-md transition-all duration-300 opacity-0 translate-y-4 pointer-events-none flex items-center gap-2">
	<svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
		<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
	</svg>
	<span>Đã sao chép link kết quả vào bộ nhớ tạm! Bạn có thể gửi cho người khác.</span>
</div>

<!-- Section 1 Header: Program Results -->
<div id="elig-programs-heading" class="flex items-center justify-between gap-3 mb-3 px-1">
	<div class="flex items-center gap-2">
		<span class="w-6 h-6 rounded-lg bg-[#00308b] text-white text-xs font-black inline-flex items-center justify-center shrink-0 shadow-2xs">1</span>
		<h3 class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-slate-700 m-0">
			Danh sách chương trình đối chiếu phù hợp
		</h3>
	</div>
	<span id="elig-programs-count-badge" class="text-xs text-slate-500 font-semibold"></span>
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

<!-- Alternatives (Smart Recommendations) -->
<div id="elig-alternatives-section" class="elig-alternatives hidden mt-8 bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-6 shadow-xs">
	<div class="mb-4">
		<div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-50 text-[#00308b] text-[11px] font-extrabold uppercase tracking-wider mb-1.5 border border-blue-100">
			<span>Phương án đề xuất bổ sung</span>
		</div>
		<h3 class="font-black text-slate-900 text-base sm:text-lg flex items-center gap-2 m-0">
			Gợi ý chương trình liên quan & phương án dự phòng
		</h3>
		<p class="text-xs sm:text-sm text-slate-500 mt-1">
			Các chương trình cùng ngành hoặc hệ đào tạo từ xa linh hoạt, tối ưu thời gian và chi phí bạn có thể cân nhắc thêm.
		</p>
	</div>
	<div id="elig-alternatives-list" class="elig-program-list space-y-4"></div>
</div>

<!-- Clear Visual Divider: Transition from Results to Application Form -->
<div class="my-8 sm:my-10 relative">
	<div class="absolute inset-0 flex items-center" aria-hidden="true">
		<div class="w-full border-t border-slate-200"></div>
	</div>
	<div class="relative flex justify-center">
		<div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider border border-slate-200/80 shadow-2xs">
			<span class="w-5 h-5 rounded-full bg-[#00308b] text-white text-[11px] font-bold flex items-center justify-center shrink-0">2</span>
			<span>Bước tiếp theo: Đăng ký thẩm định chi tiết & miễn giảm môn</span>
			<svg class="w-4 h-4 text-[#00308b]" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
			</svg>
		</div>
	</div>
</div>

<!-- Dedicated Consultation / Verification Form Section -->
<div id="elig-lead-section" class="elig-lead-form mt-8 rounded-2xl border border-slate-200/80 overflow-hidden bg-white shadow-xs scroll-mt-6">

	<!-- Distinct Form Header: Solid Navy Blue with pure white text -->
	<div class="bg-[#00308b] text-white p-5 sm:p-6 md:p-7 relative">
		<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
			<div>
				<div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/15 backdrop-blur-xs text-white text-[11px] font-extrabold uppercase tracking-wider mb-2 border border-white/20">
					<span>✍️ Form đăng ký xét tuyển 1:1</span>
				</div>
				<h2 class="text-xl sm:text-2xl font-black leading-tight m-0" style="color: #ffffff !important;">
					Đăng ký thẩm định hồ sơ & xét miễn giảm tín chỉ
				</h2>
				<p class="text-xs sm:text-sm mt-1.5 max-w-2xl leading-relaxed m-0" style="color: #dbeafe !important;">
					Điền thông tin và đính kèm bằng/bảng điểm (nếu có). Ban tuyển sinh sẽ thẩm định điều kiện trúng tuyển thực tế và gửi bảng đối chiếu tín chỉ miễn giảm hoàn toàn miễn phí.
				</p>
			</div>
			<div class="hidden md:flex w-14 h-14 rounded-2xl bg-white/10 border border-white/15 items-center justify-center text-3xl shrink-0 text-white shadow-inner">
				📋
			</div>
		</div>
	</div>

	<!-- Form Body -->
	<div class="p-5 sm:p-7 md:p-8 bg-white">

		<!-- Target Program Context Bar: Sleek Left-accent without full box border -->
		<div id="elig-selected-program-banner" class="mb-6 p-3.5 sm:p-4 rounded-xl bg-blue-50/70 border-l-4 border-l-[#00308b] flex items-center justify-between gap-3 transition-all duration-300">
			<div class="flex items-center gap-3 min-w-0">
				<div class="w-9 h-9 rounded-lg bg-blue-100/80 flex items-center justify-center text-base shrink-0 text-[#00308b]">
					🎓
				</div>
				<div class="min-w-0">
					<span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 block">Bạn đang nộp thẩm định cho chương trình:</span>
					<strong id="elig-selected-program-title" class="text-sm sm:text-base font-extrabold text-slate-900 block truncate">
						Đang chọn chương trình...
					</strong>
				</div>
			</div>
			<span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 rounded-full shrink-0 hidden sm:inline-flex items-center gap-1">
				<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
				Thẩm định miễn phí
			</span>
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

		<!-- Block 3: Degree & Transcript Upload / Links (Clean, No Nested Outer Borders) -->
		<div class="border-t border-slate-100 pt-5 space-y-3">
			<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
				<label class="block text-sm font-bold text-slate-800">Tài liệu đính kèm đối chiếu (Bằng tốt nghiệp & Bảng điểm)</label>
				<span class="text-xs text-slate-400 font-medium">Không bắt buộc • Ảnh chụp hoặc PDF dưới 5MB</span>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<!-- Degree upload -->
				<div class="space-y-1.5">
					<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
						<span>🎓</span> 1. Bằng tốt nghiệp (TC / CĐ)
					</label>
					<div class="border border-dashed border-slate-300 hover:border-[#00308b] rounded-xl p-3.5 flex flex-col items-center justify-center cursor-pointer transition-colors bg-slate-50/50 hover:bg-blue-50/30 group" onclick="document.getElementById('degree-file-input').click()">
						<input type="file" id="degree-file-input" name="degree_file" accept="image/*,application/pdf" class="hidden">
						<svg class="w-6 h-6 text-slate-400 group-hover:text-[#00308b] transition-colors mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
						</svg>
						<span id="degree-file-label" class="text-xs text-slate-600 font-bold text-center break-all">Tải ảnh hoặc PDF bằng cấp lên</span>
					</div>
					<input type="text" name="degree_link" class="elig-input text-xs" placeholder="Hoặc dán link Google Drive / ảnh bằng cấp...">
				</div>

				<!-- Transcript upload -->
				<div class="space-y-1.5">
					<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
						<span>📊</span> 2. Bảng điểm tốt nghiệp
					</label>
					<div class="border border-dashed border-slate-300 hover:border-[#00308b] rounded-xl p-3.5 flex flex-col items-center justify-center cursor-pointer transition-colors bg-slate-50/50 hover:bg-blue-50/30 group" onclick="document.getElementById('transcript-file-input').click()">
						<input type="file" id="transcript-file-input" name="transcript_file" accept="image/*,application/pdf" class="hidden">
						<svg class="w-6 h-6 text-slate-400 group-hover:text-[#00308b] transition-colors mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

		<!-- Group Registration Option: Left-accent bar instead of full bounding box -->
		<div class="p-3 bg-amber-50/60 border-l-4 border-l-amber-400 rounded-r-xl">
			<label class="flex items-start gap-2.5 cursor-pointer select-none">
				<input type="checkbox" name="is_group_reg" id="elig-group-reg" value="1" class="mt-0.5 rounded border-amber-300 text-brand-primary focus:ring-brand-primary">
				<span class="text-xs text-slate-700 font-medium leading-snug">
					🎁 <strong class="font-bold text-amber-900">Đăng ký theo nhóm:</strong> Tư vấn cho tôi suất học bổng đồng hành khi nộp hồ sơ cùng bạn bè (từ 2 người trở lên).
				</span>
			</label>
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
</div>

<!-- Disclaimer Box -->
<div class="elig-disclaimer mt-8 p-4 bg-slate-50 border border-slate-200/80 rounded-xl text-xs text-slate-500 leading-relaxed">
	<p><strong class="text-slate-700">* Lưu ý:</strong> Kết quả trên được đối chiếu tự động dựa trên thông tin bạn cung cấp và cơ sở dữ liệu chương trình. Đây là kết quả đánh giá sơ bộ tham khảo. Điều kiện nhập học chính thức phụ thuộc vào hồ sơ văn bằng, bảng điểm cụ thể và quy chế tuyển sinh của từng trường.</p>
</div>

<!-- CTA Bar -->
<div class="elig-cta-bar mt-8">
	<div class="elig-cta-channels grid grid-cols-1 sm:grid-cols-3 gap-3">
		<?php $elig_hotline = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : ( ( function_exists( 'get_field' ) ? get_field( 'global_hotline', 'options' ) : '' ) ?: '0988 991 496' ); ?>
		<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $elig_hotline ) ); ?>" class="elig-cta-card hidden sm:flex flex-col items-center justify-center p-4 bg-white border border-slate-200/90 rounded-2xl hover:border-blue-400 hover:shadow-xs transition-all text-center group no-underline">
			<div class="w-10 h-10 rounded-xl bg-blue-50 text-[#00308b] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
				<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
				</svg>
			</div>
			<span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Gọi hotline</span>
			<span class="text-sm font-black text-slate-900 mt-0.5"><?php echo esc_html( $elig_hotline ); ?></span>
		</a>

		<?php $elig_zalo = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : ( function_exists( 'get_field' ) ? get_field( 'global_zalo_url', 'options' ) : '' ); if ( $elig_zalo ) : ?>
		<a href="<?php echo esc_url( $elig_zalo ); ?>" target="_blank" class="elig-cta-card hidden sm:flex flex-col items-center justify-center p-4 bg-white border border-slate-200/90 rounded-2xl hover:border-emerald-400 hover:shadow-xs transition-all text-center group no-underline" rel="noopener">
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
