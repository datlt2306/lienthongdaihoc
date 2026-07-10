<?php
/**
 * Eligibility Checker — Multi-step Wizard
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Load majors for dropdown
$majors = get_posts( [ 'post_type' => 'major', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );

// Graduation years
$current_year = (int) date( 'Y' );
$years = range( $current_year, $current_year - 20 );
?>

<div class="elig-wizard-container">

	<!-- Progress Bar -->
	<div class="elig-progress">
		<div class="elig-progress-bar">
			<div id="elig-progress-fill" class="elig-progress-fill" style="width: 11%"></div>
		</div>
		<span id="elig-progress-text" class="elig-progress-text">Bước 1 / 9</span>
	</div>

	<!-- Steps Container -->
	<div id="elig-steps" class="elig-steps">

		<!-- Step 1: Education Level -->
		<div class="elig-step active" data-step="1">
			<h2 class="elig-step-title">Trình độ học vấn hiện tại</h2>
			<p class="elig-step-desc">Bạn hiện có bằng cấp nào?</p>
			<div class="elig-options">
				<label class="elig-option">
					<input type="radio" name="education" value="thap-phan">
					<span class="elig-option-box">
						<span class="elig-option-icon">🎓</span>
						<span class="elig-option-text">THPT</span>
						<span class="elig-option-sub">Tốt nghiệp Phổ thông</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="education" value="trung-cap">
					<span class="elig-option-box">
						<span class="elig-option-icon">📘</span>
						<span class="elig-option-text">Trung cấp</span>
						<span class="elig-option-sub">Bằng Trung cấp</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="education" value="cao-dang">
					<span class="elig-option-box">
						<span class="elig-option-icon">📗</span>
						<span class="elig-option-text">Cao đẳng</span>
						<span class="elig-option-sub">Bằng Cao đẳng</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="education" value="dai-hoc">
					<span class="elig-option-box">
						<span class="elig-option-icon">📕</span>
						<span class="elig-option-text">Đại học</span>
						<span class="elig-option-sub">Bằng Cử nhân</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="education" value="thac-si">
					<span class="elig-option-box">
						<span class="elig-option-icon">🏆</span>
						<span class="elig-option-text">Thạc sĩ / Tiến sĩ</span>
						<span class="elig-option-sub">Bằng sau đại học</span>
					</span>
				</label>
			</div>
		</div>

		<!-- Step 2: Current Major -->
		<div class="elig-step" data-step="2">
			<h2 class="elig-step-title">Chuyên ngành hiện tại</h2>
			<p class="elig-step-desc">Bạn đang học hoặc đã tốt nghiệp ngành gì?</p>
			<div class="elig-search-wrap">
				<input type="text" id="elig-major-search" class="elig-search-input" placeholder="Tìm ngành học..." autocomplete="off">
				<input type="hidden" name="major_id" value="">
				<div id="elig-major-dropdown" class="elig-dropdown">
					<?php foreach ( $majors as $m ) : ?>
						<div class="elig-dropdown-item" data-id="<?php echo esc_attr( $m->ID ); ?>" data-title="<?php echo esc_attr( $m->post_title ); ?>">
							<?php echo esc_html( $m->post_title ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<p class="elig-step-hint">Bỏ qua nếu chưa có chuyên ngành</p>
		</div>

		<!-- Step 3: Graduation Year -->
		<div class="elig-step" data-step="3">
			<h2 class="elig-step-title">Năm tốt nghiệp</h2>
			<p class="elig-step-desc">Bạn tốt nghiệp năm nào?</p>
			<select name="graduation" class="elig-select">
				<option value="">-- Chọn năm --</option>
				<?php foreach ( $years as $y ) : ?>
					<option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="elig-step-hint">Bỏ qua nếu chưa tốt nghiệp</p>
		</div>

		<!-- Step 4: Desired Major -->
		<div class="elig-step" data-step="4">
			<h2 class="elig-step-title">Ngành mong muốn</h2>
			<p class="elig-step-desc">Bạn muốn học ngành gì?</p>
			<div class="elig-search-wrap">
				<input type="text" id="elig-desired-search" class="elig-search-input" placeholder="Tìm ngành muốn học..." autocomplete="off">
				<input type="hidden" name="desired_major" value="">
				<div id="elig-desired-dropdown" class="elig-dropdown">
					<?php foreach ( $majors as $m ) : ?>
						<div class="elig-dropdown-item" data-id="<?php echo esc_attr( $m->ID ); ?>" data-title="<?php echo esc_attr( $m->post_title ); ?>">
							<?php echo esc_html( $m->post_title ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- Step 5: Training Type -->
		<div class="elig-step" data-step="5">
			<h2 class="elig-step-title">Hệ đào tạo</h2>
			<p class="elig-step-desc">Bạn muốn học hệ nào?</p>
			<div class="elig-options elig-options-grid">
				<label class="elig-option">
					<input type="radio" name="training_type" value="lien-thong">
					<span class="elig-option-box">
						<span class="elig-option-icon">🔗</span>
						<span class="elig-option-text">Liên thông</span>
						<span class="elig-option-sub">Nâng cấp bằng cấp</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="training_type" value="van-bang-2">
					<span class="elig-option-box">
						<span class="elig-option-icon">📋</span>
						<span class="elig-option-text">Văn bằng 2</span>
						<span class="elig-option-sub">Học thêm ngành mới</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="training_type" value="tu-xa">
					<span class="elig-option-box">
						<span class="elig-option-icon">💻</span>
						<span class="elig-option-text">Từ xa</span>
						<span class="elig-option-sub">Học trực tuyến</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="training_type" value="vua-hoc-vua-lam">
					<span class="elig-option-box">
						<span class="elig-option-icon">⏰</span>
						<span class="elig-option-text">Vừa học vừa làm</span>
						<span class="elig-option-sub">Lịch học linh hoạt</span>
					</span>
				</label>
			</div>
		</div>

		<!-- Step 6: Campus -->
		<div class="elig-step" data-step="6">
			<h2 class="elig-step-title">Cơ sở học</h2>
			<p class="elig-step-desc">Bạn muốn học ở đâu?</p>
			<div class="elig-options elig-options-grid">
				<label class="elig-option">
					<input type="radio" name="campus" value="ha-noi">
					<span class="elig-option-box">
						<span class="elig-option-text">Hà Nội</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="campus" value="ho-chi-minh">
					<span class="elig-option-box">
						<span class="elig-option-text">TP. Hồ Chí Minh</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="campus" value="da-nang">
					<span class="elig-option-box">
						<span class="elig-option-text">Đà Nẵng</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="campus" value="thai-nguyen">
					<span class="elig-option-box">
						<span class="elig-option-text">Thái Nguyên</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="campus" value="online">
					<span class="elig-option-box">
						<span class="elig-option-text">Online</span>
						<span class="elig-option-sub">Học trực tuyến</span>
					</span>
				</label>
			</div>
		</div>

		<!-- Step 7: Budget -->
		<div class="elig-step" data-step="7">
			<h2 class="elig-step-title">Ngân sách</h2>
			<p class="elig-step-desc">Ngân sách của bạn cho toàn bộ khóa học?</p>
			<div class="elig-options">
				<label class="elig-option">
					<input type="radio" name="budget" value="duoi-20-trieu">
					<span class="elig-option-box">
						<span class="elig-option-text">Dưới 20 triệu</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="budget" value="20-30-trieu">
					<span class="elig-option-box">
						<span class="elig-option-text">20 - 30 triệu</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="budget" value="30-50-trieu">
					<span class="elig-option-box">
						<span class="elig-option-text">30 - 50 triệu</span>
					</span>
				</label>
				<label class="elig-option">
					<input type="radio" name="budget" value="tren-50-trieu">
					<span class="elig-option-box">
						<span class="elig-option-text">Trên 50 triệu</span>
					</span>
				</label>
			</div>
		</div>

		<!-- Step 8: Phone -->
		<div class="elig-step" data-step="8">
			<h2 class="elig-step-title">Số điện thoại</h2>
			<p class="elig-step-desc">Để tư vấn viên liên hệ hỗ trợ (không bắt buộc)</p>
			<input type="tel" name="phone" class="elig-input" placeholder="Ví dụ: 0912 345 678">
			<p class="elig-step-hint">Bỏ qua nếu không muốn nhận tư vấn</p>
		</div>

		<!-- Step 9: Email -->
		<div class="elig-step" data-step="9">
			<h2 class="elig-step-title">Email</h2>
			<p class="elig-step-desc">Nhận kết quả qua email (không bắt buộc)</p>
			<input type="email" name="email" class="elig-input" placeholder="Ví dụ: email@example.com">
			<p class="elig-step-hint">Bỏ qua nếu không muốn nhận email</p>
		</div>

	</div>

	<!-- Navigation -->
	<div class="elig-nav">
		<button type="button" id="elig-prev" class="elig-btn elig-btn-secondary hidden">← Quay lại</button>
		<button type="button" id="elig-next" class="elig-btn elig-btn-primary">Tiếp theo →</button>
		<button type="button" id="elig-submit" class="elig-btn elig-btn-primary hidden">
			<span class="elig-btn-text">Kiểm tra ngay</span>
			<span class="elig-btn-loading hidden">Đang kiểm tra...</span>
		</button>
	</div>

</div>
