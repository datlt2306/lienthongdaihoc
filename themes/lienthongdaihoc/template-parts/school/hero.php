<?php
/**
 * School Hero Banner Template Part
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$school_id       = isset( $args['school_id'] ) ? (int) $args['school_id'] : get_the_ID();
$school_title    = isset( $args['school_title'] ) ? $args['school_title'] : get_the_title( $school_id );
$school_logo_url = isset( $args['school_logo_url'] ) ? $args['school_logo_url'] : ( function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'medium' ) : '' );
$initials        = isset( $args['initials'] ) ? $args['initials'] : 'ĐH';
$address         = isset( $args['address'] ) ? $args['address'] : get_field( 'address', $school_id );
$website         = isset( $args['website'] ) ? $args['website'] : get_field( 'website', $school_id );
$map_url         = isset( $args['map_url'] ) ? $args['map_url'] : ( get_field( 'google_map_url', $school_id ) ?: get_field( 'map_url', $school_id ) );
$banner_image    = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'full' ) : get_field( 'school_banner', $school_id );
?>

<!-- SCHOOL BRAND HERO BANNER (Redesigned with UI/UX Pro Max) -->
<div class="relative bg-slate-100 border border-slate-200/90 rounded-2xl sm:rounded-3xl overflow-hidden mb-6 shadow-lg min-h-[320px] sm:min-h-[380px] md:min-h-[440px] lg:min-h-[480px] flex flex-col justify-end">
	<?php if ( $banner_image ) : ?>
		<!-- Background School Banner Image (Bright, natural display) -->
		<img src="<?php echo esc_url( $banner_image ); ?>" class="hero-banner skip-lazy no-lazy absolute inset-0 w-full h-full object-cover object-center" alt="<?php echo esc_attr( $school_title ); ?>" loading="eager" fetchpriority="high" decoding="async" data-no-lazy="1">
		
		<!-- Ultra-Soft Scrim Gradient: Upper 65% is 100% clear & bright; bottom has gentle fade only for text contrast -->
		<div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 via-35% to-transparent pointer-events-none"></div>
	<?php else : ?>
		<!-- Ambient Fallback Background -->
		<div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-[#00308b] to-slate-800"></div>
	<?php endif; ?>

	<!-- Subtle Ambient Top Accent Line -->
	<div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#00308b] via-blue-500 to-amber-500 z-10"></div>

	<!-- Main Brand Info Content (Anchored to Bottom of Grand Banner) -->
	<div class="relative z-10 p-5 sm:p-7 md:p-9 lg:p-10 flex flex-col sm:flex-row sm:items-end gap-4 sm:gap-6">
		<!-- School Logo Container -->
		<div class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 lg:w-32 lg:h-32 rounded-2xl sm:rounded-3xl bg-white p-2 sm:p-2.5 shadow-2xl ring-4 ring-white/15 flex items-center justify-center shrink-0 overflow-hidden">
			<?php if ( $school_logo_url ) : ?>
				<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_title ); ?>" class="max-w-full max-h-full object-contain">
			<?php else : ?>
				<div class="w-full h-full bg-[#00308b] text-white flex items-center justify-center font-black text-xl sm:text-2xl rounded-xl uppercase tracking-wider">
					<?php echo esc_html( $initials ?: 'ĐH' ); ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- Title & Meta Details -->
		<div class="min-w-0 flex-1">
			<!-- Badges Row -->
			<div class="flex flex-wrap items-center gap-2 mb-2 sm:mb-2.5">
				<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/25 text-emerald-300 border border-emerald-400/35 text-[11px] sm:text-xs font-bold backdrop-blur-md shadow-xs">
					<span class="relative flex h-2 w-2">
						<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
						<span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
					</span>
					Đang nhận hồ sơ 2025 - 2026
				</span>
			</div>

			<!-- School Title -->
			<h1 class="font-black text-white text-xl sm:text-2xl md:text-3xl lg:text-4xl leading-tight tracking-tight drop-shadow-md">
				Tuyển sinh Liên thông <?php echo esc_html( $school_title ); ?>
			</h1>

			<!-- Address & Website Metadata -->
			<div class="mt-2.5 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs sm:text-sm text-slate-100 drop-shadow-[0_1px_2px_rgba(0,0,0,0.85)]">
				<?php if ( $address ) : ?>
					<div class="inline-flex items-center gap-1.5">
						<svg class="w-4 h-4 text-blue-300 shrink-0 drop-shadow-[0_1px_2px_rgba(0,0,0,0.85)]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
							<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
						</svg>
						<span><?php echo esc_html( $address ); ?></span>
						<?php if ( $map_url ) : ?>
							<a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer" class="text-blue-200 font-bold hover:text-white hover:underline cursor-pointer transition-colors">(Bản đồ)</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $website ) : ?>
					<div class="inline-flex items-center gap-1.5">
						<svg class="w-4 h-4 text-blue-300 shrink-0 drop-shadow-[0_1px_2px_rgba(0,0,0,0.85)]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
						</svg>
						<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" class="group inline-flex items-center text-blue-200 font-semibold hover:text-white hover:underline cursor-pointer transition-colors">
							<span><?php echo esc_html( preg_replace( '#^https?://#i', '', rtrim( $website, '/' ) ) ); ?></span>
							<svg class="w-3.5 h-3.5 text-blue-300 group-hover:text-white transition-colors ml-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
							</svg>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
