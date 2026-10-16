<?php
/**
 * Eligibility Checker — Unified Single-Page Form (Quick Check Only)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Load majors for dropdown
$majors = get_posts( [ 'post_type' => 'major', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
?>

<div class="elig-form-container bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-7 md:p-8 shadow-xs">

	<form id="elig-unified-form" class="space-y-7" autocomplete="off">
		
		<!-- Section 1: Hồ sơ hiện tại của bạn -->
		<div class="elig-section space-y-4">
			<div class="flex items-center gap-2.5 pb-2.5 border-b border-slate-100">
				<span class="w-6 h-6 rounded-full bg-blue-50 text-[#00308b] text-xs font-black inline-flex items-center justify-center border border-blue-200/60 shrink-0">1</span>
				<h2 class="text-base sm:text-lg font-black text-slate-800 tracking-tight">Hồ sơ học vấn hiện tại của bạn</h2>
			</div>
			
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
				<!-- Trình độ học vấn -->
				<div class="space-y-1.5">
					<label class="block text-xs sm:text-sm font-bold text-slate-700">Trình độ học vấn hiện tại *</label>
					<select name="education" class="elig-select select-education">
						<option value="trung-cap">Trung cấp / Trung cấp nghề</option>
						<option value="cao-dang" selected>Cao đẳng / Cao đẳng nghề</option>
					</select>
				</div>

				<!-- Chuyên ngành đã học -->
				<div class="space-y-1.5 relative" data-search-select>
					<label class="block text-xs sm:text-sm font-bold text-slate-700">Chuyên ngành đã tốt nghiệp / đang học</label>
					<input type="text" class="elig-search-input" placeholder="Gõ để tìm chuyên ngành..." autocomplete="off">
					<input type="hidden" name="major_id" class="elig-select elig-search-value">
					<div class="elig-search-dropdown absolute w-full max-h-48 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-lg hidden z-50 mt-1">
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
			<div class="flex items-center gap-2.5 pb-2.5 border-b border-slate-100">
				<span class="w-6 h-6 rounded-full bg-blue-50 text-[#00308b] text-xs font-black inline-flex items-center justify-center border border-blue-200/60 shrink-0">2</span>
				<h2 class="text-base sm:text-lg font-black text-slate-800 tracking-tight">Nhu cầu học tập mong muốn</h2>
			</div>
			
			<div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
				<!-- Ngành mong muốn -->
				<div class="space-y-1.5 relative" data-search-select>
					<label class="block text-xs sm:text-sm font-bold text-slate-700">Chuyên ngành mong muốn *</label>
					<input type="text" class="elig-search-input" placeholder="Gõ để tìm chuyên ngành..." autocomplete="off">
					<input type="hidden" name="desired_major" class="elig-select elig-search-value">
					<div class="elig-search-dropdown absolute w-full max-h-48 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-lg hidden z-50 mt-1">
						<div class="p-2 text-xs font-bold text-slate-400 border-b border-slate-100 uppercase tracking-wider">Danh sách chuyên ngành</div>
						<div class="elig-search-options">
							<div class="elig-search-option-item p-2.5 text-sm cursor-pointer hover:bg-slate-50 font-semibold text-slate-500 border-b border-slate-50" data-value="">-- Chọn chuyên ngành --</div>
							<?php foreach ( $majors as $m ) : ?>
								<div class="elig-search-option-item p-2.5 text-sm cursor-pointer hover:bg-slate-50 font-semibold text-slate-700 border-b border-slate-50 last:border-0" data-value="<?php echo esc_attr( $m->ID ); ?>"><?php echo esc_html( $m->post_title ); ?></div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- Hình thức đào tạo mong muốn -->
				<div class="space-y-1.5">
					<label class="block text-xs sm:text-sm font-bold text-slate-700">Hình thức đào tạo mong muốn</label>
					<select name="training_type" class="elig-select">
						<option value="">Chưa xác định / Gợi ý cho tôi</option>
						<option value="tu-xa">Đào tạo từ xa (Trực tuyến 100%)</option>
						<option value="vua-hoc-vua-lam">Vừa học vừa làm (Tối & Cuối tuần)</option>
						<option value="chinh-quy">Chính quy (Tập trung)</option>
					</select>
				</div>

				<!-- Cơ sở học mong muốn -->
				<div class="space-y-1.5">
					<label class="block text-xs sm:text-sm font-bold text-slate-700">Khu vực / Cơ sở muốn học</label>
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
		<div class="pt-5 border-t border-slate-100 flex justify-end">
			<button type="button" id="elig-unified-submit" class="elig-btn elig-btn-primary px-8 py-3.5 text-sm sm:text-base font-black w-full sm:w-auto rounded-xl shadow-xs hover:shadow-md transition-all">
				<span class="elig-btn-text flex items-center gap-2 justify-center">
					<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
					</svg>
					<span>XEM CHƯƠNG TRÌNH PHÙ HỢP</span>
				</span>
				<span class="elig-btn-loading hidden">Đang tìm kiếm chương trình...</span>
			</button>
		</div>

	</form>

</div>
