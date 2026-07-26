<?php
/**
 * Eligibility Checker — Unified Single-Page Form (Quick Check Only)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Load majors for dropdown
$majors = get_posts( [ 'post_type' => 'major', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
?>

<div class="elig-form-container bg-white border border-slate-200 rounded-2xl p-6 md:p-8 shadow-sm">

	<form id="elig-unified-form" class="space-y-8" autocomplete="off">
		
		<!-- Section 1: Hồ sơ hiện tại của bạn -->
		<div class="elig-section space-y-4">
			<h3 class="text-lg font-extrabold text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
				<span class="text-xl">🎓</span> 1. Hồ sơ học vấn hiện tại của bạn
			</h3>
			
			<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
				<!-- Trình độ học vấn -->
				<div class="space-y-2">
					<label class="block text-sm font-bold text-slate-700">Trình độ học vấn hiện tại *</label>
					<select name="education" class="elig-select select-education">
						<option value="">-- Chọn trình độ hiện tại --</option>
						<option value="thap-phan">THPT (Tốt nghiệp Phổ thông)</option>
						<option value="trung-cap">Trung cấp (Bằng Trung cấp)</option>
						<option value="cao-dang">Cao đẳng (Bằng Cao đẳng)</option>
						<option value="dai-hoc">Đại học (Bằng Cử nhân)</option>
					</select>
				</div>

				<!-- Chuyên ngành đã học -->
				<div class="space-y-2 relative" data-search-select>
					<label class="block text-sm font-bold text-slate-700">Chuyên ngành đã tốt nghiệp / đang học</label>
					<input type="text" class="elig-search-input" placeholder="Gõ để tìm chuyên ngành..." autocomplete="off">
					<input type="hidden" name="major_id" class="elig-select elig-search-value">
					<div class="elig-search-dropdown absolute w-full max-h-48 overflow-y-auto bg-white border border-slate-200 rounded-lg shadow-lg hidden z-50 mt-1">
						<div class="p-2 text-xs font-bold text-slate-400 border-b border-slate-100 uppercase tracking-wider">Danh sách chuyên ngành</div>
						<div class="elig-search-options">
							<div class="elig-search-option-item p-2.5 text-sm cursor-pointer hover:bg-slate-50 font-semibold text-slate-500 border-b border-slate-50" data-value="">-- Chọn chuyên ngành --</div>
							<?php foreach ( $majors as $m ) : ?>
								<div class="elig-search-option-item p-2.5 text-sm cursor-pointer hover:bg-slate-50 font-semibold text-slate-700 border-b border-slate-50 last:border-0" data-value="<?php echo esc_attr( $m->ID ); ?>"><?php echo esc_html( $m->post_title ); ?></div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Section 2: Nhu cầu học tập mong muốn -->
		<div class="elig-section space-y-4 pt-4 border-t border-slate-100">
			<h3 class="text-lg font-extrabold text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
				<span class="text-xl">🎯</span> 2. Nhu cầu học tập mong muốn
			</h3>
			
			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				<!-- Ngành mong muốn -->
				<div class="space-y-2 relative" data-search-select>
					<label class="block text-sm font-bold text-slate-700">Chuyên ngành mong muốn *</label>
					<input type="text" class="elig-search-input" placeholder="Gõ để tìm chuyên ngành..." autocomplete="off">
					<input type="hidden" name="desired_major" class="elig-select elig-search-value">
					<div class="elig-search-dropdown absolute w-full max-h-48 overflow-y-auto bg-white border border-slate-200 rounded-lg shadow-lg hidden z-50 mt-1">
						<div class="p-2 text-xs font-bold text-slate-400 border-b border-slate-100 uppercase tracking-wider">Danh sách chuyên ngành</div>
						<div class="elig-search-options">
							<div class="elig-search-option-item p-2.5 text-sm cursor-pointer hover:bg-slate-50 font-semibold text-slate-500 border-b border-slate-50" data-value="">-- Chọn chuyên ngành --</div>
							<?php foreach ( $majors as $m ) : ?>
								<div class="elig-search-option-item p-2.5 text-sm cursor-pointer hover:bg-slate-50 font-semibold text-slate-700 border-b border-slate-50 last:border-0" data-value="<?php echo esc_attr( $m->ID ); ?>"><?php echo esc_html( $m->post_title ); ?></div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- Hệ đào tạo mong muốn -->
				<div class="space-y-2">
					<label class="block text-sm font-bold text-slate-700">Hệ đào tạo mong muốn</label>
					<select name="training_type" class="elig-select">
						<option value="">Chưa xác định / Gợi ý cho tôi</option>
						<?php 
						$training_types = get_terms( [ 'taxonomy' => 'training_type', 'hide_empty' => false ] );
						if ( ! is_wp_error( $training_types ) && ! empty( $training_types ) ) {
							foreach ( $training_types as $tt ) {
								echo '<option value="' . esc_attr( $tt->slug ) . '">' . esc_html( $tt->name ) . '</option>';
							}
						}
						?>
					</select>
				</div>

				<!-- Cơ sở học mong muốn -->
				<div class="space-y-2">
					<label class="block text-sm font-bold text-slate-700">Khu vực / Cơ sở muốn học</label>
					<select name="campus" class="elig-select">
						<option value="">Không quan trọng / Bất kỳ đâu</option>
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
				</div>
			</div>
		</div>

		<!-- Submit Button -->
		<div class="pt-6 border-t border-slate-100 flex justify-end">
			<button type="button" id="elig-unified-submit" class="elig-btn elig-btn-primary px-8 py-3.5 text-base w-full md:w-auto" style="box-shadow: 0 4px 14px rgba(217, 119, 6, 0.25);">
				<span class="elig-btn-text flex items-center gap-2 justify-center">🔎 XEM CHƯƠNG TRÌNH PHÙ HỢP</span>
				<span class="elig-btn-loading hidden">Đang tìm kiếm chương trình...</span>
			</button>
		</div>

	</form>

</div>
