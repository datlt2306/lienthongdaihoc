<?php
/**
 * Eligibility Checker — Multi-step Wizard
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Load majors for dropdown
$majors = get_posts( [ 'post_type' => 'major', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );

// Graduation years (now Birth Years)
$current_year = (int) date( 'Y' );
$years = range( $current_year - 18, $current_year - 70 );
?>

<div class="elig-wizard-container">

	<!-- Progress Bar -->
	<div class="elig-progress">
		<div class="elig-progress-bar">
			<div id="elig-progress-fill" class="elig-progress-fill" style="width: 11%"></div>
		</div>
		<span id="elig-progress-text" class="elig-progress-text">Bước 1 / 8</span>
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
			<select name="major_id" class="elig-select">
				<option value="">-- Chọn chuyên ngành --</option>
				<?php foreach ( $majors as $m ) : ?>
					<option value="<?php echo esc_attr( $m->ID ); ?>"><?php echo esc_html( $m->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="elig-step-hint">Bỏ qua nếu chưa có chuyên ngành</p>
		</div>

		<!-- Step 3: Birth Year -->
		<div class="elig-step" data-step="3">
			<h2 class="elig-step-title">Năm sinh</h2>
			<p class="elig-step-desc">Bạn sinh năm nào?</p>
			<select name="graduation" class="elig-select">
				<option value="">-- Chọn năm --</option>
				<?php foreach ( $years as $y ) : ?>
					<option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<!-- Step 4: Desired Major -->
		<div class="elig-step" data-step="4">
			<h2 class="elig-step-title">Ngành mong muốn</h2>
			<p class="elig-step-desc">Bạn muốn học ngành gì?</p>
			<select name="desired_major" class="elig-select">
				<option value="">-- Chọn ngành muốn học --</option>
				<?php foreach ( $majors as $m ) : ?>
					<option value="<?php echo esc_attr( $m->ID ); ?>"><?php echo esc_html( $m->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<!-- Step 5: Training Type -->
		<div class="elig-step" data-step="5">
			<h2 class="elig-step-title">Hệ đào tạo</h2>
			<p class="elig-step-desc">Bạn muốn học hệ nào?</p>
			<div class="elig-options elig-options-grid">
				<?php 
				$training_types = get_terms( [
					'taxonomy'   => 'training_type',
					'hide_empty' => false,
				] );
				if ( ! is_wp_error( $training_types ) && ! empty( $training_types ) ) :
					$tt_icons = [
						'tu-xa' => '💻',
						'van-bang-2' => '📋',
						'vua-hoc-vua-lam' => '⏰',
						'lien-thong' => '🔗',
						'chinh-quy' => '🎓',
					];
					$tt_subs = [
						'tu-xa' => 'Học trực tuyến',
						'van-bang-2' => 'Học thêm ngành mới',
						'vua-hoc-vua-lam' => 'Lịch học linh hoạt',
						'lien-thong' => 'Nâng cấp bằng cấp',
						'chinh-quy' => 'Học tập trung',
					];
					foreach ( $training_types as $tt ) : 
						$icon = $tt_icons[ $tt->slug ] ?? '🎓';
						$sub = $tt_subs[ $tt->slug ] ?? '';
						?>
						<label class="elig-option">
							<input type="radio" name="training_type" value="<?php echo esc_attr( $tt->slug ); ?>">
							<span class="elig-option-box">
								<span class="elig-option-icon"><?php echo esc_html( $icon ); ?></span>
								<span class="elig-option-text"><?php echo esc_html( $tt->name ); ?></span>
								<?php if ( $sub ) : ?>
									<span class="elig-option-sub"><?php echo esc_html( $sub ); ?></span>
								<?php endif; ?>
							</span>
						</label>
					<?php 
					endforeach;
				endif;
				?>
			</div>
		</div>

		<!-- Step 6: Campus -->
		<div class="elig-step" data-step="6">
			<h2 class="elig-step-title">Cơ sở học</h2>
			<p class="elig-step-desc">Bạn muốn học ở đâu?</p>
			<div class="elig-options elig-options-grid">
				<?php 
				$campuses = get_terms( [
					'taxonomy'   => 'campus',
					'hide_empty' => false,
				] );
				if ( ! is_wp_error( $campuses ) && ! empty( $campuses ) ) :
					$campus_icons = [
						'ha-noi' => '🏛️',
						'ho-chi-minh' => '🏙️',
						'da-nang' => '🌉',
						'thai-nguyen' => '⛰️',
						'online' => '💻',
					];
					foreach ( $campuses as $cp ) : 
						$icon = $campus_icons[ $cp->slug ] ?? '📍';
						?>
						<label class="elig-option">
							<input type="radio" name="campus" value="<?php echo esc_attr( $cp->slug ); ?>">
							<span class="elig-option-box">
								<span class="elig-option-icon"><?php echo esc_html( $icon ); ?></span>
								<span class="elig-option-text"><?php echo esc_html( $cp->name ); ?></span>
							</span>
						</label>
					<?php 
					endforeach;
				endif;
				?>
			</div>
		</div>

		<!-- Step 7: Phone -->
		<div class="elig-step" data-step="7">
			<h2 class="elig-step-title">Số điện thoại</h2>
			<p class="elig-step-desc">Để tư vấn viên liên hệ hỗ trợ (không bắt buộc)</p>
			<input type="tel" name="phone" class="elig-input" placeholder="Ví dụ: 0912 345 678">
			<p class="elig-step-hint">Bỏ qua nếu không muốn nhận tư vấn</p>
		</div>

		<!-- Step 8: Email -->
		<div class="elig-step" data-step="8">
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
