<?php
/**
 * Home: Experience & Statistics Counter Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$w_heading = '';
$w_desc    = '';
if ( function_exists( 'get_field' ) ) {
	$w_heading = get_field( 'hp_whyus_heading', 'options' ) ?: '';
	$w_desc    = get_field( 'hp_whyus_desc', 'options' ) ?: '';
}
if ( empty( $w_heading ) ) {
	$w_heading = 'Kinh nghiệm & Thực tế';
}
if ( empty( $w_desc ) || str_contains( $w_desc, 'hàng đầu' ) ) {
	$w_desc = 'Hỗ trợ người học tiếp cận thông tin tuyển sinh chính xác và chuẩn bị hồ sơ xét tuyển.';
}

$kpi_metrics = [];
if ( function_exists( 'get_field' ) ) {
	$kpi_metrics = get_field( 'kpi_metrics', 'options' ) ?: [];
}
if ( empty( $kpi_metrics ) && function_exists( 'ltdh_default' ) ) {
	$kpi_metrics = ltdh_default( 'homepage', 'kpi_metrics' );
}

// Sanitize and filter out legacy unverified claims
$sanitized_metrics = [];
if ( is_array( $kpi_metrics ) ) {
	foreach ( $kpi_metrics as $m ) {
		$val = trim( (string) ( $m['value'] ?? '' ) );
		$lbl = trim( (string) ( $m['label'] ?? '' ) );

		if ( str_contains( $val, '98%' ) || str_contains( mb_strtolower( $lbl, 'UTF-8' ), 'hài lòng' ) ) {
			continue;
		}
		if ( ( str_contains( $val, '30+' ) || str_contains( $val, '30' ) ) && str_contains( mb_strtolower( $lbl, 'UTF-8' ), 'đối tác' ) ) {
			continue;
		}
		if ( str_contains( $val, '1.200' ) || str_contains( $val, '200+' ) || str_contains( $val, '1200' ) ) {
			$sanitized_metrics[] = [
				'value'   => '1500+',
				'label'   => 'Người học được hỗ trợ đăng ký liên thông',
				'subtext' => 'Trong 4 năm qua',
			];
			continue;
		}
		if ( str_contains( $val, '10+' ) || str_contains( $val, '10' ) ) {
			$sanitized_metrics[] = [
				'value'   => '4 năm',
				'label'   => 'Kinh nghiệm tư vấn tuyển sinh liên thông',
				'subtext' => 'Tư vấn lựa chọn chương trình và hỗ trợ hồ sơ',
			];
			continue;
		}
		$sanitized_metrics[] = $m;
	}
}

if ( empty( $sanitized_metrics ) && function_exists( 'ltdh_default' ) ) {
	$sanitized_metrics = ltdh_default( 'homepage', 'kpi_metrics' );
}
?>

<!-- 6. EXPERIENCE & ACCURATE STATISTICS -->
<section class="py-8 sm:py-10 md:py-14 bg-gradient-to-br from-[#0c192c] via-[#0E2038] to-[#002b7a] text-white relative overflow-hidden">
	<div class="absolute -right-20 -top-20 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
	<div class="absolute -left-20 -bottom-20 w-80 h-80 bg-[#f5bf23]/10 rounded-full blur-3xl pointer-events-none"></div>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
		<div class="flex flex-col lg:flex-row items-center justify-between gap-6 lg:gap-12">
			<div class="w-full lg:w-1/3 text-center lg:text-left">
				<div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-xs text-[#f5bf23] text-[11px] font-extrabold uppercase tracking-wider mb-2.5 border border-white/10">
					<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
					<span>Thông tin xác thực</span>
				</div>
				<h2 class="text-xl md:text-2xl font-black font-display tracking-tight text-white uppercase leading-tight">
					<?php echo esc_html( $w_heading ); ?>
				</h2>
				<p class="text-slate-300 text-sm sm:text-base mt-2 leading-relaxed">
					<?php echo esc_html( $w_desc ); ?>
				</p>
			</div>
			<div id="ltdh-kpi-metrics-grid" class="w-full lg:w-2/3 grid grid-cols-2 gap-3.5 sm:gap-6 text-center">
				<?php foreach ( $sanitized_metrics as $idx => $metric ) : 
					$is_first = ( 0 === $idx );
				?>
					<div class="group bg-white/[0.07] hover:bg-white/[0.12] border border-white/10 hover:border-white/20 rounded-2xl p-4 sm:p-7 flex flex-col items-center text-center backdrop-blur-xs transition-all duration-300 shadow-sm hover:shadow-lg h-full">
						<div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-[#f5bf23] mb-2 sm:mb-3 group-hover:bg-[#f5bf23]/20 group-hover:border-[#f5bf23]/30 transition-colors shrink-0">
							<?php if ( $is_first ) : ?>
								<svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
								</svg>
							<?php else : ?>
								<svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
								</svg>
							<?php endif; ?>
						</div>

						<span class="ltdh-counter tabular-nums block font-display font-black text-2xl sm:text-4xl lg:text-5xl text-[#f5bf23] leading-none tracking-tight mb-1.5 sm:mb-2" data-target="<?php echo esc_attr( $metric['value'] ?? '' ); ?>">
							<?php echo esc_html( $metric['value'] ?? '' ); ?>
						</span>
						<span class="text-sm sm:text-base text-white font-bold block leading-snug sm:leading-snug mb-auto">
							<?php echo esc_html( $metric['label'] ?? '' ); ?>
						</span>
						<?php if ( ! empty( $metric['subtext'] ) ) : ?>
							<span class="text-xs sm:text-sm text-slate-300 font-medium block mt-1.5 sm:mt-2 leading-tight sm:leading-relaxed pt-1.5 border-t border-white/5 w-full">
								<?php echo esc_html( $metric['subtext'] ); ?>
							</span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
