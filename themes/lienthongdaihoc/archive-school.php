<?php
/**
 * Archive School Directory Template
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

		<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					$school_id = get_the_ID();
					$logo_id = get_field( 'logo', $school_id );
					$address = get_field( 'address', $school_id ) ?: 'Địa chỉ đang cập nhật';
			?>
					<div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
						<div>
							<div class="flex items-center gap-4 mb-4">
								<?php if ( $logo_id ) : ?>
									<?php echo wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'h-14 w-14 object-contain border border-slate-100 p-1 bg-white rounded-lg' ] ); ?>
								<?php else : ?>
									<div class="h-14 w-14 bg-blue-50 text-[#2563EB] font-display font-black text-sm flex items-center justify-center rounded-lg">UNI</div>
								<?php endif; ?>
								<div>
									<h3 class="font-extrabold text-slate-800 text-base hover:text-brand-primary leading-snug">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h3>
									<span class="text-sm text-slate-400 block font-medium mt-0.5"><?php echo esc_html( get_field( 'hotline', $school_id ) ?: 'Ban tuyển sinh' ); ?></span>
								</div>
							</div>
							<p class="text-sm text-slate-500 line-clamp-3 mb-6"><?php the_excerpt(); ?></p>
						</div>

						<div class="border-t border-slate-100 pt-4 flex justify-between items-center text-sm">
							<span class="text-slate-400 truncate max-w-[150px]"><?php echo esc_html( $address ); ?></span>
							<a href="<?php the_permalink(); ?>" class="text-[#2563EB] font-bold hover:underline">Chi tiết tuyển sinh →</a>
						</div>
					</div>
			<?php
				endwhile;
			else :
				echo '<div class="col-span-3 text-center py-12"><p class="text-slate-500 text-base">Chưa có trường liên kết nào được nhập hệ thống.</p></div>';
			endif;
			?>
		</div>

	</div>
</main>

<?php
get_footer();
