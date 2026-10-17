<?php
/**
 * Card Major Partial (Premium Information-Rich Architecture)
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$major_id = get_the_ID();
$m_data   = function_exists( 'ltdh_get_major_card_metadata' )
	? ltdh_get_major_card_metadata( $major_id )
	: [];

$clean_title    = $m_data['title'] ?? preg_replace( '/^ngành\s+/iu', '', get_the_title( $major_id ) );
$code           = $m_data['code'] ?? get_field( 'major_code', $major_id );
$cat_name       = $m_data['cat_name'] ?? 'Ngành đào tạo';
$thumb          = $m_data['thumb'] ?? get_the_post_thumbnail_url( $major_id, 'medium_large' );
$excerpt        = $m_data['excerpt'] ?? wp_strip_all_tags( get_the_excerpt( $major_id ) );
$study_time     = $m_data['study_time'] ?? '1.5 - 2 năm';
$entry_level    = $m_data['entry_level'] ?? 'Từ Cao đẳng, Trung cấp';
$degree         = $m_data['degree'] ?? 'Bằng Cử nhân / Kỹ sư';
$prog_count     = $m_data['prog_count'] ?? 0;
$schools        = $m_data['schools'] ?? [];
$training_types = $m_data['training_types'] ?? [];
?>
<div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:border-brand-primary/30 transition-all duration-300 flex flex-col justify-between group h-full">
	
	<!-- Header Thumbnail Area -->
	<div class="relative aspect-[16/10] sm:h-52 md:h-56 bg-slate-100 overflow-hidden">
		<a href="<?php the_permalink(); ?>" class="block w-full h-full">
			<img src="<?php echo esc_url( $thumb ); ?>"
				 alt="<?php the_title_attribute(); ?>"
				 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
				 loading="lazy"
				 decoding="async">
			<div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/25 to-transparent pointer-events-none"></div>
		</a>

		<!-- Top Left Badge: Real Major Code or Category -->
		<div class="absolute top-2.5 left-2.5 md:top-3 md:left-3 z-10 pointer-events-none flex items-center gap-1.5">
			<?php if ( ! empty( $code ) ) : ?>
				<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] md:text-xs font-black bg-slate-900/90 text-white backdrop-blur-md shadow-2xs border border-white/10 tracking-wider">
					Mã: <?php echo esc_html( $code ); ?>
				</span>
			<?php else : ?>
				<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] md:text-xs font-bold bg-slate-900/80 text-white backdrop-blur-md shadow-2xs border border-white/10 tracking-wide">
					<?php echo esc_html( $cat_name ); ?>
				</span>
			<?php endif; ?>
		</div>

		<!-- Top Right Badge: Program Count -->
		<div class="absolute top-2.5 right-2.5 md:top-3 md:right-3 z-10 pointer-events-none">
			<?php if ( $prog_count > 0 ) : ?>
				<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] md:text-xs font-extrabold bg-emerald-600/95 text-white shadow-2xs backdrop-blur-md border border-white/10">
					<span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
					<span><?php echo esc_html( $prog_count ); ?></span> <span class="hidden sm:inline">CT tuyển sinh</span><span class="sm:hidden">CT</span>
				</span>
			<?php else : ?>
				<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] md:text-xs font-semibold bg-slate-800/80 text-slate-200 backdrop-blur-md border border-white/10">
					Đang cập nhật
				</span>
			<?php endif; ?>
		</div>
	</div>

	<!-- Card Body Content -->
	<div class="p-3.5 sm:p-5 flex-1 flex flex-col justify-between">
		<div>
			<!-- Eyebrow Category -->
			<span class="text-[10px] sm:text-xs font-bold text-brand-primary uppercase tracking-wider block mb-1">
				<?php echo esc_html( $cat_name ); ?>
			</span>

			<!-- Major Headline Title -->
			<h3 class="font-black text-slate-900 text-sm sm:text-base md:text-lg group-hover:text-brand-primary transition-colors leading-snug line-clamp-2 mb-2 min-h-[38px] sm:min-h-[44px]">
				<a href="<?php the_permalink(); ?>">Ngành <?php echo esc_html( $clean_title ); ?></a>
			</h3>

			<!-- Overview Brief Excerpt -->
			<p class="text-[11px] sm:text-xs md:text-sm text-slate-500 line-clamp-2 leading-relaxed mb-3">
				<?php echo esc_html( $excerpt ); ?>
			</p>

			<!-- Training Modes Pills -->
			<?php if ( ! empty( $training_types ) ) : ?>
				<div class="flex flex-wrap items-center gap-1.5 mb-3.5">
					<?php foreach ( array_slice( $training_types, 0, 3 ) as $type_item ) : ?>
						<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] sm:text-[11px] font-bold border shadow-2xs <?php echo esc_attr( $type_item['badge_class'] ); ?>">
							<span class="w-1.5 h-1.5 rounded-full <?php echo esc_attr( $type_item['dot_class'] ); ?>"></span>
							<?php echo esc_html( $type_item['name'] ); ?>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Structured Specifications Box (Duration, Degree, Schools) -->
			<div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 sm:p-3 space-y-1.5 sm:space-y-2 mb-4 text-[11px] sm:text-xs">
				<div class="flex items-center justify-between gap-2">
					<span class="text-slate-400 flex items-center gap-1.5 shrink-0">
						<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
						Thời gian:
					</span>
					<span class="font-bold text-slate-800 text-right truncate"><?php echo esc_html( $study_time ); ?></span>
				</div>

				<div class="flex items-center justify-between gap-2">
					<span class="text-slate-400 flex items-center gap-1.5 shrink-0">
						<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>
						Bằng cấp:
					</span>
					<span class="font-bold text-slate-800 text-right truncate"><?php echo esc_html( $degree ); ?></span>
				</div>

				<div class="flex items-center justify-between gap-2 pt-1.5 border-t border-slate-200/60">
					<span class="text-slate-400 flex items-center gap-1.5 shrink-0">
						<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
						Trường đào tạo:
					</span>
					<span class="font-bold text-brand-primary text-right truncate max-w-[130px] sm:max-w-[170px]" title="<?php echo esc_attr( ! empty( $schools ) ? implode( ', ', array_column( $schools, 'name' ) ) : 'Trường ĐH đối tác' ); ?>">
						<?php
						if ( ! empty( $schools ) ) {
							$school_codes = array_map( function( $s ) { return $s['code']; }, array_slice( $schools, 0, 2 ) );
							$extra        = count( $schools ) > 2 ? ' (+' . ( count( $schools ) - 2 ) . ')' : '';
							echo esc_html( implode( ', ', $school_codes ) . $extra );
						} else {
							echo 'Trường ĐH đối tác';
						}
						?>
					</span>
				</div>
			</div>
		</div>

		<!-- Action Footer Button -->
		<div class="pt-2.5 sm:pt-3 border-t border-slate-100 mt-auto">
			<a href="<?php the_permalink(); ?>" class="w-full inline-flex items-center justify-between bg-slate-50 hover:bg-[#00308b] text-[#00308b] hover:text-white border border-slate-200/80 hover:border-[#00308b] py-2 sm:py-2.5 px-3 sm:px-4 rounded-xl font-bold transition-all duration-200 shadow-2xs hover:shadow-md text-xs sm:text-sm group/btn min-h-[38px] sm:min-h-[42px]">
				<span><?php echo $prog_count > 0 ? 'Xem ' . $prog_count . ' chương trình' : 'Khám phá ngành học'; ?></span>
				<span class="w-6 h-6 rounded-lg bg-blue-100/70 group-hover/btn:bg-white/20 text-[#00308b] group-hover/btn:text-white flex items-center justify-center transition-colors shrink-0">
					<svg class="w-3.5 h-3.5 transform group-hover/btn:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
				</span>
			</a>
		</div>
	</div>

</div>
