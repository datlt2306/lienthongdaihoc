<?php
/**
 * Eligibility Checker — Results Display
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<!-- Summary Header -->
<div id="elig-results-header" class="elig-results-header hidden">
	<div class="elig-results-icon" id="elig-results-icon">✅</div>
	<h2 class="elig-results-title" id="elig-results-title">Kết quả kiểm tra</h2>
	<p class="elig-results-subtitle" id="elig-results-subtitle"></p>
</div>

<!-- Input Summary -->
<div id="elig-input-summary" class="elig-input-summary hidden">
	<div class="elig-summary-row">
		<span class="elig-summary-label">Điều kiện của bạn:</span>
		<div id="elig-summary-tags" class="elig-summary-tags"></div>
		<button type="button" id="elig-retry" class="elig-link-btn">Thay đổi</button>
	</div>
</div>

<!-- Eligible Programs -->
<div id="elig-program-list" class="elig-program-list"></div>

<!-- No Results -->
<div id="elig-no-results" class="elig-no-results hidden">
	<div class="elig-no-results-icon">😔</div>
	<h3>Chưa có chương trình phù hợp</h3>
	<p>Hãy thử thay đổi điều kiện hoặc liên hệ tư vấn viên để được hỗ trợ.</p>
	<div class="elig-no-results-actions">
		<button type="button" id="elig-no-results-retry" class="elig-btn elig-btn-primary">Thử lại với điều kiện khác</button>
		<a href="/dang-ky-tu-van/" class="elig-btn elig-btn-secondary">Liên hệ tư vấn</a>
	</div>
</div>

<!-- Alternatives -->
<div id="elig-alternatives" class="elig-alternatives hidden">
	<h3>Gợi ý thay thế</h3>
	<div id="elig-alternative-list"></div>
</div>

<!-- Documents Needed -->
<div id="elig-documents" class="elig-documents hidden">
	<h3>📋 Hồ sơ cần chuẩn bị</h3>
	<ul>
		<li>CCCD photo (2 mặt)</li>
		<li>Bằng tốt nghiệp (bản sao công chứng)</li>
		<li>Bảng điểm (bản sao công chứng)</li>
		<li>Ảnh 3x4 (4 tấm)</li>
		<li>Phiếu đăng ký tuyển sinh</li>
	</ul>
</div>

<!-- Lead Form -->
<div id="elig-lead-form" class="elig-lead-form hidden">
	<h3>📞 Đăng ký tư vấn miễn phí</h3>
	<p>Để lại thông tin, tư vấn viên sẽ liên hệ trong 24 giờ</p>
	<form id="elig-consultation-form">
		<input type="hidden" name="elig_check_id" id="elig-check-id" value="">
		<input type="hidden" name="elig_program_id" id="elig-program-id" value="">
		<div class="elig-form-row">
			<input type="text" name="cf_name" class="elig-input" placeholder="Họ và tên *" required>
		</div>
		<div class="elig-form-row">
			<input type="tel" name="cf_phone" class="elig-input" placeholder="Số điện thoại *" required>
		</div>
		<div class="elig-form-row">
			<input type="email" name="cf_email" class="elig-input" placeholder="Email (không bắt buộc)">
		</div>
		<button type="submit" class="elig-btn elig-btn-primary elig-btn-full">Gửi đăng ký</button>
	</form>
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
		<a href="/dang-ky-tu-van/" class="elig-cta-card elig-cta-form">
			<span class="elig-cta-icon">📋</span>
			<span class="elig-cta-label">Đăng ký tư vấn</span>
			<span class="elig-cta-value">Miễn phí</span>
		</a>
	</div>
</div>
