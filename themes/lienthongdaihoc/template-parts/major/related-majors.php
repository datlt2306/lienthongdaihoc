<?php
/**
 * Related Majors Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$related_majors = isset( $args['related_majors'] ) ? $args['related_majors'] : [];

if ( empty( $related_majors ) || ! is_array( $related_majors ) ) {
	return;
}
?>

<!-- RELATED MAJORS -->
<section id="nganh-lien-quan" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
	<h2 class="text-xl md:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-3 md:pb-4 mb-4">Ngành đào tạo liên quan</h2>
	<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 sm:gap-4">
		<?php foreach ( $related_majors as $rel_major ) : 
			$rel_id    = is_object( $rel_major ) ? $rel_major->ID : (int) $rel_major;
			$rel_title = get_the_title( $rel_id );
			$rel_url   = get_permalink( $rel_id );
			$rel_terms = get_the_terms( $rel_id, 'major_cat' );
			$rel_cat   = ( ! empty( $rel_terms ) && ! is_wp_error( $rel_terms ) ) ? $rel_terms[0]->name : 'Chương trình liên thông';
		?>
			<a href="<?php echo esc_url( $rel_url ); ?>" class="group p-3.5 rounded-xl border border-slate-200/80 hover:border-[#00308b] hover:shadow-xs transition-all bg-slate-50/50 flex items-center gap-3">
				<div class="w-10 h-10 rounded-lg bg-blue-50 text-[#00308b] flex items-center justify-center font-bold text-base shrink-0 group-hover:bg-[#00308b] group-hover:text-white transition-colors">
					🎓
				</div>
				<div class="min-w-0 flex-1">
					<h3 class="font-bold text-slate-800 text-xs sm:text-sm group-hover:text-[#00308b] transition-colors truncate"><?php echo esc_html( $rel_title ); ?></h3>
					<span class="text-[11px] text-slate-400 font-medium"><?php echo esc_html( $rel_cat ); ?></span>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
</section>
