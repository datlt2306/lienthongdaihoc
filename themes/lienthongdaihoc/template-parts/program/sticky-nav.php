<?php
/**
 * Program Sticky Nav Template Part
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id   = isset( $args['program_id'] ) ? (int) $args['program_id'] : get_the_ID();
$program_tabs = isset( $args['program_tabs'] ) ? $args['program_tabs'] : [];
?>
<!-- STICKY SECTION TAB NAVIGATION (Redesigned with UI/UX Pro Max) -->
<div id="ltdh-program-sticky-nav" class="ltdh-sticky-nav sticky top-16 sm:top-20 z-40 bg-white/98 backdrop-blur-md border-b border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.06)] transition-all duration-200">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-2 sm:gap-4">
		<nav class="flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden py-2 flex-1 min-w-0" aria-label="Điều hướng các mục chương trình đào tạo">
			<?php foreach ( $program_tabs as $tab_idx => $tab ) : 
				$is_active = ( 0 === $tab_idx );
			?>
				<a href="#<?php echo esc_attr( $tab['id'] ); ?>" 
				   data-tab-target="<?php echo esc_attr( $tab['id'] ); ?>"
				   class="ltdh-section-tab-link group relative inline-flex items-center gap-2.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all duration-200 shrink-0 select-none cursor-pointer no-underline <?php echo $is_active ? 'bg-blue-50/90 text-[#00308b] font-bold shadow-2xs is-active' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'; ?>">
					
					<!-- Icon Box -->
					<div class="ltdh-tab-icon w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0 <?php echo $is_active ? 'bg-[#00308b] text-white shadow-xs shadow-blue-900/20' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200 group-hover:text-slate-800'; ?>">
						<?php echo $tab['icon']; ?>
					</div>

					<!-- Text Column -->
					<div class="flex flex-col text-left">
						<span class="ltdh-tab-title text-xs sm:text-[13px] font-bold leading-tight transition-colors <?php echo $is_active ? 'text-[#00308b]' : 'text-slate-700 group-hover:text-slate-900'; ?>">
							<?php echo esc_html( $tab['title'] ); ?>
						</span>
						<span class="ltdh-tab-sub text-[10px] leading-tight mt-0.5 hidden md:block transition-colors <?php echo $is_active ? 'text-blue-700/80 font-medium' : 'text-slate-400 group-hover:text-slate-500'; ?>">
							<?php echo esc_html( $tab['subtitle'] ); ?>
						</span>
					</div>

					<!-- Subtle Active Accent Indicator (Bottom pill) -->
					<span class="ltdh-tab-indicator absolute -bottom-0.5 left-3 right-3 h-[2px] rounded-full transition-all duration-200 <?php echo $is_active ? 'bg-[#EA580C] opacity-100' : 'bg-transparent opacity-0'; ?>"></span>
				</a>
			<?php endforeach; ?>
		</nav>

		<!-- Quick Share Trigger in Sticky Bar -->
		<div class="shrink-0 flex items-center py-2 relative">
			<!-- ltdh-quick-share-btn dropdown trigger loaded via template-parts/program-share -->
			<?php get_template_part( 'template-parts/program-share', null, [ 'program_id' => $program_id ] ); ?>
		</div>
	</div>
</div>
