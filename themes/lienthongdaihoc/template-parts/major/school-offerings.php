<?php
/**
 * Major School Offerings Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$major_id             = isset( $args['major_id'] ) ? (int) $args['major_id'] : get_the_ID();
$clean_m_name         = isset( $args['clean_m_name'] ) ? $args['clean_m_name'] : preg_replace( '/^ngành\s+/iu', '', trim( (string) get_the_title( $major_id ) ) );
$available_he_types   = isset( $args['available_he_types'] ) ? $args['available_he_types'] : ( function_exists( 'ltdh_get_major_training_types' ) ? ltdh_get_major_training_types( (int) $major_id ) : [] );
$selected_he          = isset( $args['selected_he'] ) ? $args['selected_he'] : '';
$selected_school_slug = isset( $args['selected_school_slug'] ) ? $args['selected_school_slug'] : '';
?>

<!-- PROGRAMS FOR THIS MAJOR (PRIMARY FOCUS & FILTER) -->
<section id="truong-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6 mb-6">
	<!-- Header -->
	<div class="mb-5 sm:mb-6">
		<h2 id="ltdh-major-programs-title" class="text-xl md:text-2xl font-black text-slate-900 leading-tight">
			Các trường đang tuyển sinh Liên thông ngành <?php echo esc_html( $clean_m_name ); ?>
		</h2>
		<p class="text-xs sm:text-sm text-slate-500 mt-1.5 flex items-center gap-1.5">
			<svg class="w-4 h-4 text-[#00308b] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<span>Nhấp vào chương trình để xem thời gian, học phí và lộ trình đào tạo cụ thể của từng trường.</span>
		</p>
	</div>

	<!-- Filter Bar (Only show if there are 2 or more training types available) -->
	<?php if ( count( $available_he_types ) >= 2 ) : ?>
		<div class="bg-slate-50/90 rounded-2xl p-1.5 sm:p-2 mb-6 flex items-center overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
			<!-- Training Type Filter Pills -->
			<div class="flex items-center gap-1 sm:gap-1.5 shrink-0">
				<button type="button" data-he="" class="ltdh-major-he-pill px-3.5 py-2 rounded-xl text-xs font-bold transition-all shrink-0 <?php echo empty( $selected_he ) ? 'bg-[#00308b] text-white shadow-xs is-active' : 'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-white/60'; ?>">
					Tất cả hình thức
				</button>
				<?php foreach ( $available_he_types as $he_key => $he_item ) : 
					$is_pill_active = ( $he_key === $selected_he || ( 'tu-xa' === $he_key && 'dao-tao-tu-xa' === $selected_he ) );
				?>
					<button type="button" data-he="<?php echo esc_attr( $he_item['slug'] ); ?>" class="ltdh-major-he-pill inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all shrink-0 <?php echo $is_pill_active ? 'bg-[#00308b] text-white shadow-xs is-active' : 'bg-transparent text-slate-600  hover:bg-white/60'; ?>">
						<span class="w-2 h-2 rounded-full <?php echo esc_attr( $he_item['dot'] ); ?>"></span>
						<span><?php echo esc_html( $he_item['name'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- Dynamic Programs List Container -->
	<div id="ltdh-major-programs-wrapper" data-major-id="<?php echo esc_attr( $major_id ); ?>" data-school-slug="<?php echo esc_attr( $selected_school_slug ); ?>" class="transition-opacity duration-300">
		<div id="ltdh-major-programs-list">
			<?php 
			$initial_count = ltdh_render_major_programs_list( $major_id, $selected_he, $selected_school_slug ); 
			?>
		</div>
	</div>
</section>
