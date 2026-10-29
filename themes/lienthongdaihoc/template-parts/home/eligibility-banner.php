<?php
/**
 * Home: Eligibility Quick Check Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$e_badge    = '';
$e_heading  = '';
$e_desc     = '';
$e_items    = [];
$e_cta_text = '';
$e_cta_url  = '';

if ( function_exists( 'get_field' ) ) {
	$e_badge    = get_field( 'hp_elig_badge', 'options' ) ?: '';
	$e_heading  = get_field( 'hp_elig_heading', 'options' ) ?: '';
	$e_desc     = get_field( 'hp_elig_desc', 'options' ) ?: '';
	$e_items    = get_field( 'hp_elig_items', 'options' ) ?: [];
	$e_cta_text = get_field( 'hp_elig_cta_text', 'options' ) ?: '';
	$e_cta_raw  = get_field( 'hp_elig_cta_url', 'options' ) ?: '/kiem-tra-dieu-kien/';
} else {
	$e_cta_raw = '/kiem-tra-dieu-kien/';
}

$e_cta_url = ( preg_match( '#^(https?:)?//#i', $e_cta_raw ) ) ? $e_cta_raw : home_url( $e_cta_raw );
$e_heading = str_replace( '\n', '<br>', $e_heading );

$e_items_default = [
	[ 'title' => 'Tốt nghiệp Trung cấp', 'desc' => 'Liên thông lên Đại học' ],
	[ 'title' => 'Tốt nghiệp Cao đẳng', 'desc' => 'Liên thông miễn giảm tín chỉ' ],
	[ 'title' => 'Khác ngành / Ngành gần', 'desc' => 'Học bổ sung kiến thức liên thông' ],
];
$e_items_render = ! empty( $e_items ) ? $e_items : $e_items_default;
?>

<!-- 4. ELIGIBILITY QUICK CHECK SECTION (Apple Cupertino Bento) -->
<section class="py-10 sm:py-16 bg-[#F5F5F7] border-t border-black/[0.06]">
	<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
		<div class="space-y-4">
			<span class="inline-block bg-[#0071E3]/10 text-[#0071E3] text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">
				<?php echo esc_html( $e_badge ?: 'Điều kiện tuyển sinh' ); ?>
			</span>
			<h2 class="font-display text-2xl sm:text-3xl lg:text-4xl font-semibold text-[#1D1D1F] tracking-tight leading-tight">
				<?php echo $e_heading ?: 'Bạn có đủ điều kiện học<br>Liên thông Đại học?'; ?>
			</h2>
			<p class="text-[#86868B] text-sm leading-relaxed max-w-2xl mx-auto">
				<?php echo esc_html( $e_desc ?: 'Chương trình tuyển sinh mở rộng cho người tốt nghiệp Trung cấp hoặc Cao đẳng. Chỉ mất 1 phút để kiểm tra tự động.' ); ?>
			</p>

			<div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-4 max-w-3xl mx-auto text-left">
				<?php foreach ( $e_items_render as $item ) : ?>
					<div class="bg-white p-5 rounded-2xl sm:rounded-3xl border border-black/[0.06] shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-md transition-all">
						<span class="block text-sm font-semibold text-[#1D1D1F] mb-1"><?php echo esc_html( $item['title'] ?? '' ); ?></span>
						<span class="text-sm text-[#86868B] leading-relaxed block"><?php echo esc_html( $item['desc'] ?? '' ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="flex flex-wrap justify-center gap-3 pt-6">
				<a href="<?php echo esc_url( $e_cta_url ); ?>" class="bg-[#0071E3] hover:bg-[#0077ED] active:scale-98 text-white px-7 py-3 rounded-full font-medium text-sm tracking-tight transition-all shadow-none hover:shadow-xs inline-flex items-center gap-1.5">
					<span><?php echo esc_html( $e_cta_text ?: 'Bắt đầu kiểm tra ngay' ); ?></span>
					<span>›</span>
				</a>
				<a href="#register-section" class="bg-white hover:bg-slate-50 active:scale-98 border border-black/[0.08] text-[#1D1D1F] px-7 py-3 rounded-full font-medium text-sm tracking-tight transition-all">
					Tư vấn tiêu chuẩn tuyển sinh
				</a>
			</div>
		</div>
	</div>
</section>
