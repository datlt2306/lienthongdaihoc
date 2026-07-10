<?php
/**
 * Taxonomy Archive Template (Handles Training Types, Campuses, and Regions)
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term = get_queried_object();
$taxonomy = $term->taxonomy;
?>

<main id="primary" class="site-main py-12 bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		
		<!-- Breadcrumbs -->
		<nav class="text-sm text-slate-500 mb-6">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-brand-primary">Trang chủ</a> / 
			<span><?php echo esc_html( $term->name ); ?></span>
		</nav>

		<header class="mb-10">
			<span class="inline-block bg-blue-50 text-[#2563EB] text-sm font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-2">
				<?php 
				if ( $taxonomy === 'training_type' ) {
					echo 'Hệ đào tạo';
				} elseif ( $taxonomy === 'campus' ) {
					echo 'Cơ sở đào tạo';
				} else {
					echo 'Khu vực tuyển sinh';
				}
				?>
			</span>
			<h1 class="text-3xl font-black text-slate-900">
				Danh sách: <?php echo esc_html( $term->name ); ?>
			</h1>
			<?php if ( $term->description ) : ?>
				<p class="text-slate-500 text-sm mt-2 max-w-2xl"><?php echo esc_html( $term->description ); ?></p>
			<?php endif; ?>
		</header>

		<!-- Dynamic Grid Output based on Taxonomy target -->
		<?php if ( $taxonomy === 'region' ) : ?>
			<!-- Region lists schools -->
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
									</div>
								</div>
								<p class="text-sm text-slate-500 line-clamp-3 mb-6"><?php the_excerpt(); ?></p>
							</div>

							<div class="border-t border-slate-100 pt-4 flex justify-between items-center text-sm">
								<span class="text-slate-400 truncate max-w-[150px]"><?php echo esc_html( $address ); ?></span>
								<a href="<?php the_permalink(); ?>" class="text-[#2563EB] font-bold hover:underline">Chi tiết →</a>
							</div>
						</div>
				<?php
					endwhile;
				else :
					echo '<div class="col-span-3 text-center py-12"><p class="text-slate-500 text-base">Chưa có trường liên kết nào thuộc khu vực này.</p></div>';
				endif;
				?>
			</div>

		<?php else : ?>
			<!-- campus or training_type lists programs -->
			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				<?php
				if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						$prog_id = get_the_ID();
						$school_rel_id = get_field( 'school_relationship', $prog_id );
						$school_name = $school_rel_id ? get_the_title( $school_rel_id ) : 'Đại học liên kết';
				?>
						<div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
							<?php
							$status = get_post_meta( $prog_id, 'admission_status', true ) ?: 'tuyen-sinh';
							$groups = get_post_meta( $prog_id, 'admission_groups', true );
							?>
							<div>
								<div class="flex items-center flex-wrap gap-2 mb-1.5">
									<span class="text-sm text-slate-400 font-semibold uppercase"><?php echo esc_html( $school_name ); ?></span>
									<?php if ( $status === 'tam-ngung' ) : ?>
										<span class="bg-rose-50 text-rose-700 border border-rose-100 text-[10px] font-bold px-2 py-0.5 rounded">Tạm ngưng tuyển</span>
									<?php else : ?>
										<span class="bg-blue-50 text-brand-primary border border-blue-100 text-[10px] font-bold px-2 py-0.5 rounded">Đang tuyển</span>
									<?php endif; ?>
								</div>
								<h3 class="font-extrabold text-slate-800 text-lg hover:text-brand-primary mb-3">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
								
								<div class="space-y-1.5 text-sm text-slate-500 py-3 border-t border-slate-100">
									<p>Học phí: <span class="font-bold text-brand-primary"><?php echo esc_html( get_field( 'tuition_fee' ) ?: 'Liên hệ' ); ?></span></p>
									<p>Thời gian: <span class="font-bold text-slate-700"><?php echo esc_html( get_field( 'duration' ) ?: '1.5 - 2 năm' ); ?></span></p>
									<p>Cơ sở: <span class="font-bold text-slate-700"><?php echo esc_html( get_field( 'campus_info' ) ?: 'Cơ sở / Online' ); ?></span></p>
									<?php if ( ! empty( $groups ) ) : ?>
										<p>Tổ hợp: <span class="font-bold text-slate-700"><?php echo esc_html( $groups ); ?></span></p>
									<?php endif; ?>
								</div>
							</div>

							<div class="mt-6 pt-4 border-t border-slate-100 flex justify-between items-center">
								<a href="<?php the_permalink(); ?>" class="text-sm text-brand-primary font-bold hover:underline">Chi tiết</a>
								<a href="<?php the_permalink(); ?>#register" class="bg-[#2563EB] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-[#1E40AF] transition-all">Đăng ký học</a>
							</div>
						</div>
				<?php
					endwhile;
				else :
					echo '<div class="col-span-3 text-center py-12"><p class="text-slate-500 text-base">Hiện chưa có chương trình nào tuyển sinh thuộc hệ/cơ sở đào tạo này.</p></div>';
				endif;
				?>
			</div>
		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
