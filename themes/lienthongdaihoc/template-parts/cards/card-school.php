<?php
/**
 * Card School Partial
 *
 * Displays a single school card in directory listings and grids.
 * Features cover image, region pill, institution logo, school code,
 * English title, address, and link to school detail page.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$school_id = isset( $args['school_id'] ) ? (int) $args['school_id'] : get_the_ID();
if ( ! $school_id ) {
	return;
}

$logo_id     = get_field( 'logo', $school_id );
$en_name     = get_post_meta( $school_id, 'english_name', true );
if ( empty( $en_name ) && function_exists( 'get_field' ) ) {
	$en_name = get_field( 'english_name', $school_id );
}
$school_code = get_post_meta( $school_id, 'school_code', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'school_code', $school_id ) : '' );
if ( ( empty( $en_name ) || 'University' === $en_name ) && ! empty( $school_code ) ) {
	$en_name = $school_code . ' University';
}

$address         = get_field( 'address', $school_id ) ?: 'Việt Nam';
$school_location = function_exists( 'ltdh_get_school_location_label' ) ? ltdh_get_school_location_label( $school_id ) : '';
$school_region   = function_exists( 'ltdh_get_school_region_label' ) ? ltdh_get_school_region_label( $school_id ) : '';
$cover_url       = function_exists( 'ltdh_get_school_cover_url' )
	? ltdh_get_school_cover_url( $school_id, 'medium' )
	: ( get_the_post_thumbnail_url( $school_id, 'medium' ) ?: ( function_exists( 'ltdh_get_fallback_image' ) ? ltdh_get_fallback_image( 'school', 'medium' ) : '' ) );
$school_logo_url = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'thumbnail' ) : '';
$permalink       = get_permalink( $school_id );
$title           = get_the_title( $school_id );

$is_featured     = ! empty( $args['is_featured'] );
$grid_span_class = isset( $args['grid_span_class'] ) ? $args['grid_span_class'] : '';
?>

<div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group<?php echo $grid_span_class ? ' ' . esc_attr( $grid_span_class ) : ''; ?>">
	<!-- Card Cover & Badges -->
	<div class="relative h-36 sm:h-40 bg-slate-200 overflow-hidden">
		<a href="<?php echo esc_url( $permalink ); ?>" class="block w-full h-full" tabindex="-1" aria-hidden="true">
			<img src="<?php echo esc_url( $cover_url ); ?>"
				 alt="<?php echo esc_attr( $title ); ?>"
				 loading="lazy"
				 decoding="async"
				 width="300"
				 height="180"
				 class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
		</a>
		<div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-slate-900/20 to-transparent pointer-events-none"></div>
		
		<?php if ( $is_featured ) : ?>
			<!-- Featured Pill -->
			<span class="absolute top-2.5 left-2.5 sm:top-3 sm:left-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[10px] sm:text-[11px] font-black uppercase p-1 sm:px-2.5 sm:py-1 rounded-full tracking-wider shadow-sm z-10 flex items-center gap-1 sm:gap-1.5 shrink-0" title="Trường nổi bật">
				<?php ltdh_icon( 'star-fill', 'w-3.5 h-3.5 fill-current' ); ?>
				<span class="hidden sm:inline">Nổi bật</span>
			</span>
		<?php endif; ?>

		<!-- Region Pill on Cover -->
		<?php if ( ! empty( $school_region ) ) : ?>
			<span class="absolute top-2.5 right-2.5 sm:top-3 sm:right-3 bg-slate-900/60 backdrop-blur-md text-white text-[10px] sm:text-[11px] font-semibold px-2 py-0.5 sm:px-2.5 sm:py-0.5 rounded-full z-10 flex items-center gap-1 border border-white/20 shadow-xs">
				<?php ltdh_icon( 'location', 'w-2.5 h-2.5 sm:w-3 sm:h-3 text-white/80 shrink-0' ); ?>
				<span><?php echo esc_html( $school_region ); ?></span>
			</span>
		<?php endif; ?>
	</div>

	<!-- Floating Logo -->
	<div class="h-14 w-14 sm:h-16 sm:w-16 bg-white rounded-2xl border-2 border-white shadow-[0_4px_16px_rgba(0,0,0,0.12)] -mt-7 sm:-mt-8 ml-3.5 sm:ml-5 z-10 relative flex items-center justify-center overflow-hidden p-1.5 transition-transform duration-300 group-hover:scale-105">
		<?php if ( ! empty( $school_logo_url ) ) : ?>
			<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="h-full w-full object-contain p-0.5" loading="lazy">
		<?php elseif ( $logo_id ) : ?>
			<?php echo wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
		<?php else : ?>
			<span class="font-display font-extrabold text-[#00308b] text-sm">UNI</span>
		<?php endif; ?>
	</div>

	<!-- Card Body -->
	<div class="p-3.5 sm:p-5 pt-2 sm:pt-2.5 flex-1 flex flex-col justify-between text-left">
		<div>
			<!-- Code & Location Mini Row -->
			<div class="flex items-center gap-1.5 sm:gap-2 mb-1 min-h-[20px]">
				<?php if ( ! empty( $school_code ) ) : ?>
					<span class="font-bold text-[#00308b] bg-blue-50 px-1.5 py-0.5 rounded text-[10px] border border-blue-100/80">Mã: <?php echo esc_html( $school_code ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $school_location ) ) : ?>
					<span class="text-xs text-slate-500 font-medium truncate"><?php echo esc_html( $school_location ); ?></span>
				<?php endif; ?>
			</div>

			<!-- School Name -->
			<h3 class="font-extrabold text-slate-900 text-sm md:text-[15px] tracking-tight leading-snug min-h-[38px] sm:min-h-[40px] line-clamp-2 mt-0.5 group-hover:text-[#00308b] transition-colors">
				<a href="<?php echo esc_url( $permalink ); ?>">
					<?php echo esc_html( $title ); ?>
				</a>
			</h3>

			<!-- English Name -->
			<?php if ( ! empty( $en_name ) ) : ?>
				<p class="hidden sm:block text-xs text-slate-400 font-medium line-clamp-1 italic mt-0.5" title="<?php echo esc_attr( $en_name ); ?>">
					<?php echo esc_html( $en_name ); ?>
				</p>
			<?php endif; ?>
			
			<!-- Address -->
			<?php
			$display_address = ! empty( $address ) && 'Việt Nam' !== $address ? $address : ( ! empty( $school_location ) ? 'Cơ sở: ' . $school_location : '' );
			if ( ! empty( $display_address ) ) :
			?>
				<div class="hidden sm:flex mt-3 pt-2.5 border-t border-slate-100 items-start gap-1.5 text-xs text-slate-500 min-h-[36px]">
					<?php ltdh_icon( 'location', 'w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5' ); ?>
					<span class="line-clamp-2 leading-relaxed" title="<?php echo esc_attr( $display_address ); ?>">
						<?php echo esc_html( $display_address ); ?>
					</span>
				</div>
			<?php endif; ?>
		</div>

		<!-- Action Footer -->
		<div class="mt-2.5 pt-2 sm:mt-3.5 sm:pt-3 border-t border-slate-100">
			<a href="<?php echo esc_url( $permalink ); ?>" 
			   class="w-full text-center py-2 px-3 rounded-full text-xs sm:text-sm font-medium text-white bg-[#0071E3] hover:bg-[#0077ED] active:scale-98 transition-all duration-200 flex items-center justify-center gap-1.5 shadow-none">
				<span class="sm:hidden">Xem trường</span>
				<span class="hidden sm:inline">Tìm hiểu chi tiết</span>
				<?php ltdh_icon( 'arrow-right', 'w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1 shrink-0' ); ?>
			</a>
		</div>
	</div>
</div>
