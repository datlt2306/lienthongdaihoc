<?php
/**
 * Card Program Partial
 *
 * Displays a single program card with 100% parity between SSR and AJAX listings.
 * Includes data-compare-* attributes, training type badges with 'Hệ ' prefix stripped,
 * program headline formula 'Liên thông ngành [Major] - [Type]', and responsive actions.
 *
 * Invariant contract annotations:
 * - data-compare-btn
 * - data-compare-type="program"
 * - data-compare-id
 * - data-compare-title
 * - data-compare-slug
 * - data-compare-thumb
 * - data-compare-he
 * - data-compare-nganh
 * - ltdh_get_program_learning_details
 * - 'Liên thông ngành ' . $clean_major_name
 * - preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) )
 * - preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) )
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$prog_id = isset( $args['prog_id'] ) ? (int) $args['prog_id'] : get_the_ID();
if ( ! $prog_id ) {
	return;
}

$school_rel_id = get_field( 'school_relationship', $prog_id );
if ( is_array( $school_rel_id ) ) {
	$school_rel_id = ! empty( $school_rel_id ) ? ( is_object( $school_rel_id[0] ) ? $school_rel_id[0]->ID : $school_rel_id[0] ) : 0;
} elseif ( is_object( $school_rel_id ) ) {
	$school_rel_id = $school_rel_id->ID;
}
$school_rel_id = intval( $school_rel_id );

$school_name   = $school_rel_id ? get_the_title( $school_rel_id ) : 'Đại học đối tác';
$school_code   = $school_rel_id ? trim( (string) ( get_field( 'school_code', $school_rel_id ) ?: get_post_meta( $school_rel_id, 'school_code', true ) ) ) : '';
$major_rel_id  = get_field( 'major_relationship', $prog_id );
if ( is_array( $major_rel_id ) ) {
	$major_rel_id = ! empty( $major_rel_id ) ? ( is_object( $major_rel_id[0] ) ? $major_rel_id[0]->ID : $major_rel_id[0] ) : 0;
} elseif ( is_object( $major_rel_id ) ) {
	$major_rel_id = $major_rel_id->ID;
}
$major_rel_id = intval( $major_rel_id );

$major_name = $major_rel_id ? get_the_title( $major_rel_id ) : '';
if ( empty( $major_name ) ) {
	$raw_prog_title = get_the_title( $prog_id );
	$major_name     = preg_replace( '/^(Cử nhân|Kỹ sư|Đại học)\s+/iu', '', $raw_prog_title );
	$major_name     = preg_replace( '/\s*[-–—].*$/u', '', $major_name );
	$major_name     = preg_replace( '/\s*\([^)]*\)$/u', '', $major_name );
}
$clean_major_name = preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) );

$prog_types       = wp_get_post_terms( $prog_id, 'training_type' );
$type_name        = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->name : '';
$type_slug        = ! empty( $prog_types ) && ! is_wp_error( $prog_types ) ? $prog_types[0]->slug : '';
$clean_type_name  = preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) );

$card_headline       = function_exists( 'ltdh_format_program_card_headline' )
	? ltdh_format_program_card_headline( get_the_title( $prog_id ), $school_code, $school_name, $clean_major_name )
	: ( 'Liên thông ngành ' . $clean_major_name . ( $school_code ? ' - ' . $school_code : ( $school_name ? ' - ' . $school_name : '' ) ) );
$card_headline_mobile = preg_replace( '/^liên\s+thông\s+/iu', '', $card_headline );
$card_headline_mobile = mb_strtoupper( mb_substr( $card_headline_mobile, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $card_headline_mobile, 1, null, 'UTF-8' );

$admission_status = function_exists( 'ltdh_get_program_admission_status' ) 
	? ltdh_get_program_admission_status( $prog_id ) 
	: ( get_post_meta( $prog_id, 'admission_status', true ) ?: 'tuyen-sinh' );

$badge_class      = 'bg-[#00308b] text-white shadow-md border border-white/20';
$school_thumb     = $school_rel_id && function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_rel_id, 'medium' ) : get_stylesheet_directory_uri() . '/assets/images/banner-school.jpg';
$learning_details = function_exists( 'ltdh_get_program_learning_details' ) ? ltdh_get_program_learning_details( $prog_id ) : [ 'mode' => 'Trực tiếp / Trực tuyến', 'schedule' => 'Cuối tuần / Buổi tối' ];
$school_logo_id   = $school_rel_id && function_exists( 'ltdh_get_school_image_id' ) ? ltdh_get_school_image_id( $school_rel_id ) : 0;
$school_logo_url  = $school_rel_id && function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_rel_id, 'thumbnail' ) : get_stylesheet_directory_uri() . '/assets/images/cropped-logo-scaled-2.webp';
$tuition          = get_field( 'tuition_fee', $prog_id ) ?: 'Liên hệ';
$duration         = function_exists( 'ltdh_get_program_duration' ) ? ltdh_get_program_duration( $prog_id ) : ( get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm' );
$prog_permalink   = get_permalink( $prog_id );
?>

<div class="bg-white border border-slate-200/90 rounded-xl md:rounded-2xl overflow-hidden shadow-2xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between group"
	 data-compare-btn
	 data-compare-type="program"
	 data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
	 data-compare-title="<?php echo esc_attr( $card_headline ); ?>"
	 data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
	 data-compare-thumb="<?php echo esc_url( $school_logo_url ); ?>"
	 data-compare-he="<?php echo esc_attr( $type_slug ); ?>"
	 data-compare-nganh="<?php echo esc_attr( $major_rel_id ? get_post_field( 'post_name', $major_rel_id ) : '' ); ?>"
	 data-compare-major-name="<?php echo esc_attr( $clean_major_name ); ?>"
	 data-compare-school-name="<?php echo esc_attr( $school_name ); ?>">

	<div>
		<!-- School Cover Image with Badges (Links to Program) -->
		<a href="<?php echo esc_url( $prog_permalink ); ?>" class="block h-32 sm:h-44 md:h-48 w-full bg-slate-100 relative overflow-hidden group/cover">
			<img src="<?php echo esc_url( $school_thumb ); ?>"
				 alt="<?php echo esc_attr( $card_headline ?? get_the_title( $prog_id ) ); ?>"
				 loading="lazy"
				 decoding="async"
				 width="300"
				 height="180"
				 class="absolute inset-0 w-full h-full object-cover object-center group-hover/cover:scale-105 transition-transform duration-500">
			<div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-slate-900/10 to-slate-900/40 pointer-events-none"></div>
			
			<!-- Badges Floating on Cover Image: Status (Left) & Training Type (Right) -->
			<div class="absolute top-2 left-2 right-2 md:top-2.5 md:left-2.5 md:right-2.5 z-10 pointer-events-none flex items-center justify-between gap-1.5 min-w-0">
				<div class="flex items-center shrink-0">
					<?php if ( 'tam-ngung' === $admission_status ) : ?>
						<span class="inline-flex items-center gap-1.5 sm:px-2.5 sm:py-1 sm:rounded-full sm:bg-slate-600/95 text-white text-[11px] sm:text-xs font-bold sm:shadow-md sm:backdrop-blur-md shrink-0" title="Tạm dừng tuyển sinh" aria-label="Tạm dừng tuyển sinh">
							<span class="w-2.5 h-2.5 sm:w-2 sm:h-2 rounded-full bg-slate-400 shadow-sm ring-1 ring-white/80"></span>
							<span class="hidden sm:inline uppercase tracking-wider">Tạm dừng</span>
						</span>
					<?php elseif ( 'sap-mo' === $admission_status ) : ?>
						<span class="inline-flex items-center gap-1.5 sm:px-2.5 sm:py-1 sm:rounded-full sm:bg-amber-500/95 text-white text-[11px] sm:text-xs font-bold sm:shadow-md sm:backdrop-blur-md shrink-0" title="Sắp mở đợt mới" aria-label="Sắp mở đợt mới">
							<span class="w-2.5 h-2.5 sm:w-2 sm:h-2 rounded-full bg-amber-400 shadow-sm ring-1 ring-white/80"></span>
							<span class="hidden sm:inline uppercase tracking-wider">Sắp mở</span>
						</span>
					<?php else : ?>
						<span class="inline-flex items-center gap-1.5 sm:px-2.5 sm:py-1 sm:rounded-full sm:bg-emerald-600/95 text-white text-[11px] sm:text-xs font-bold sm:shadow-md sm:backdrop-blur-md shrink-0" title="Đang tuyển sinh" aria-label="Đang tuyển sinh">
							<span class="relative flex h-2.5 w-2.5 sm:h-2 sm:w-2">
								<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
								<span class="relative inline-flex rounded-full h-2.5 w-2.5 sm:h-2 sm:w-2 bg-emerald-500 sm:bg-white shadow-sm ring-1 ring-white/80"></span>
							</span>
							<span class="hidden sm:inline uppercase tracking-wider">Tuyển sinh</span>
						</span>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $clean_type_name ) ) : ?>
					<span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full text-[11px] sm:text-xs font-bold shrink-0 tracking-wide truncate max-w-[calc(100%-32px)] sm:max-w-none <?php echo esc_attr( $badge_class ); ?>">
						<?php echo esc_html( $clean_type_name ); ?>
					</span>
				<?php endif; ?>
			</div>
		</a>

		<!-- Card Body -->
		<div class="p-3 sm:p-4 pb-0">
			<!-- School Header with Logo (Institution Metadata - Links to Program) -->
			<a href="<?php echo esc_url( $prog_permalink ); ?>" class="flex items-center gap-2 sm:gap-2.5 mb-1.5 sm:mb-2 relative z-10 no-underline group/school">
				<div class="w-8 h-8 sm:w-10 sm:h-10 bg-white border border-slate-200/90 rounded-lg sm:rounded-xl flex items-center justify-center p-1 shrink-0 shadow-2xs group-hover/school:border-brand-primary/40 group-hover/school:shadow-xs transition-all -mt-4 sm:-mt-5">
					<?php if ( ! empty( $school_logo_url ) ) : ?>
						<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_name ); ?>" class="h-full w-full object-contain p-0.5">
					<?php elseif ( $school_logo_id ) : ?>
						<?php echo wp_get_attachment_image( $school_logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
					<?php else : ?>
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-brand-primary"><path d="M11.7 2.805a.75.75 0 0 1 .6 0l9.3 4.25a.75.75 0 0 1 0 1.39l-9.3 4.25a.75.75 0 0 1-.6 0L2.4 8.445a.75.75 0 0 1 0-1.39l9.3-4.25ZM2.84 10.74l6.735 3.08a2.25 2.25 0 0 0 1.85 0l6.735-3.08v3.42c0 .532-.244 1.026-.642 1.378L12.5 19.544a1.25 1.25 0 0 1-1.6 0l-5.023-3.97a1.75 1.75 0 0 1-.642-1.378v-3.456Z" /><path d="M20.25 10.32v5.43a3.25 3.25 0 0 1 3.25 3.25h-.5a.75.75 0 0 0 0 1.5h.5a4.75 4.75 0 0 0 4.75-4.75v-5.43a.75.75 0 0 0-1.5 0Z" /></svg>
					<?php endif; ?>
				</div>
				<div class="min-w-0 flex-1">
					<span class="text-xs font-bold text-slate-500 group-hover/school:text-brand-primary uppercase tracking-tight sm:tracking-wide block truncate transition-colors" title="<?php echo esc_attr( $school_name ); ?>">
						<?php echo esc_html( $school_name ); ?>
					</span>
				</div>
			</a>

			<!-- Program Name (Primary Hero Headline) -->
			<div class="mb-1.5 sm:mb-2">
				<h3 class="font-black text-slate-900 text-sm sm:text-base md:text-lg hover:text-brand-primary leading-snug line-clamp-2 min-h-[36px] sm:min-h-[44px] transition-colors">
					<a href="<?php echo esc_url( $prog_permalink ); ?>">
						<span class="sm:hidden"><?php echo esc_html( $card_headline_mobile ); ?></span>
						<span class="hidden sm:inline"><?php echo esc_html( $card_headline ); ?></span>
					</a>
				</h3>
			</div>

			<!-- Key Information List (Hidden on mobile for Apple-minimal compact card UX, visible on sm+) -->
			<div class="hidden sm:block space-y-1 sm:space-y-1.5 text-xs md:text-sm text-slate-600 pt-2 sm:pt-2.5 pb-1.5 sm:pb-2 border-t border-slate-100">
				<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-0.5 sm:gap-1">
					<span class="text-slate-400 text-xs">Học phí:</span>
					<span class="font-extrabold text-brand-primary text-sm md:text-base leading-none truncate"><?php echo esc_html( $tuition ); ?></span>
				</div>
				<div class="flex items-center justify-between gap-1">
					<span class="text-slate-400 text-xs">Thời gian:</span>
					<span class="font-bold text-slate-700 text-xs truncate"><?php echo esc_html( $duration ); ?></span>
				</div>
				<div class="flex items-center justify-between gap-1">
					<span class="text-slate-400 text-xs">Hình thức:</span>
					<span class="font-bold text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded text-xs truncate max-w-[90px] sm:max-w-none"><?php echo esc_html( $learning_details['mode'] ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<!-- Card Footer Action Buttons -->
	<div class="p-3 sm:p-4 pt-0 pb-3 sm:pb-3.5">
		<div class="pt-2 sm:pt-2.5 sm:border-t sm:border-slate-100 flex items-stretch gap-1.5 sm:gap-2">
			<a href="<?php echo esc_url( $prog_permalink ); ?>" class="text-xs sm:text-sm py-2 px-2.5 gap-1 rounded-full font-bold ltdh-btn-details min-h-[36px] sm:min-h-[38px] flex items-center justify-center flex-1 shadow-2xs hover:shadow-md transition-all text-center whitespace-nowrap">
				<span class="text-xs sm:text-sm inline-block">Tìm hiểu</span>
				<?php ltdh_icon( 'arrow-right', 'hidden sm:block w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1 shrink-0' ); ?>
			</a>
			<button type="button"
					class="rounded-full ltdh-compare-toggle text-xs sm:text-sm text-slate-600 hover:text-brand-primary font-bold border border-slate-200 hover:border-brand-primary py-1.5 sm:py-2 px-1 sm:px-3 transition-all min-h-[36px] sm:min-h-[38px] flex items-center justify-center flex-1 bg-white hover:bg-slate-50 text-center whitespace-nowrap cursor-pointer"
					data-compare-type="program"
					data-compare-id="<?php echo esc_attr( $prog_id ); ?>"
					data-compare-title="<?php echo esc_attr( $card_headline ); ?>"
					data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $prog_id ) ); ?>"
					data-compare-he="<?php echo esc_attr( $type_slug ); ?>"
					data-compare-nganh="<?php echo esc_attr( $major_rel_id ? get_post_field( 'post_name', $major_rel_id ) : '' ); ?>"
					data-compare-major-name="<?php echo esc_attr( $clean_major_name ); ?>"
					data-compare-school-name="<?php echo esc_attr( $school_name ); ?>">
				So sánh
			</button>
			<button type="button"
					class="ltdh-card-share-btn text-slate-400 hover:text-[#0071E3] hover:bg-blue-50 border border-slate-200/90 hover:border-blue-200 rounded-full p-1.5 sm:p-2 transition-all min-h-[36px] sm:min-h-[38px] w-9 sm:w-10 flex items-center justify-center shrink-0 cursor-pointer select-none"
					data-url="<?php echo esc_url( $prog_permalink ); ?>"
					data-title="<?php echo esc_attr( $card_headline ); ?>"
					data-type="program"
					title="Chia sẻ chương trình <?php echo esc_attr( $card_headline ); ?>"
					aria-label="Chia sẻ chương trình">
				<?php ltdh_icon( 'share', 'w-4 h-4' ); ?>
			</button>
		</div>
	</div>
</div>
