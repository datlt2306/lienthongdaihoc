<?php
/**
 * Archive Major Directory Template
 *
 * @package ltdh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Get active category slug
$active_cat = isset( $_GET['nhom_nganh'] ) ? sanitize_text_field( $_GET['nhom_nganh'] ) : '';

// Get all Nhóm ngành terms
$major_cats = get_terms( [
	'taxonomy'   => 'major_cat',
	'hide_empty' => false,
] );
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

		<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
			
			<!-- Sidebar -->
			<details id="sidebar-filters" class="lg:col-span-1 lg:block bg-white lg:bg-transparent border border-slate-200 lg:border-0 rounded-xl lg:rounded-none shadow-xs lg:shadow-none mb-6 lg:mb-0 group overflow-hidden" open>
				<summary class="flex lg:hidden items-center justify-between p-4 cursor-pointer font-bold text-slate-800 list-none [&::-webkit-details-marker]:hidden select-none min-h-[44px]">
					<span class="flex items-center gap-2">
						<span>⚙️</span> <span>Bộ lọc tìm kiếm</span>
					</span>
					<span class="text-slate-400 group-open:rotate-180 transition-transform duration-200">▾</span>
				</summary>
				<div class="p-4 lg:p-0 pt-0 lg:pt-0 border-t lg:border-0 border-slate-100 space-y-5 sticky top-24">

					<!-- Search Box -->
					<form action="<?php echo esc_url( get_post_type_archive_link( 'major' ) ); ?>" method="GET" class="relative">
						<?php if ( $active_cat ) : ?>
							<input type="hidden" name="nhom_nganh" value="<?php echo esc_attr( $active_cat ); ?>">
						<?php endif; ?>
						<input type="text" name="s" value="<?php echo esc_attr( isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '' ); ?>" placeholder="Tìm kiếm ngành học..." class="w-full border border-slate-200 rounded-lg px-3 py-3 text-sm focus:border-brand-primary focus:outline-none placeholder-slate-400 min-h-[44px]">
					</form>

					<h3 class="font-extrabold text-slate-900 text-base border-t border-slate-100 pt-4 uppercase tracking-wider">Nhóm ngành</h3>
					<ul class="space-y-1">
						<li>
							<a href="<?php echo esc_url( remove_query_arg( 'nhom_nganh' ) ); ?>" class="flex items-center justify-between px-3 py-3 rounded-lg text-sm font-semibold transition-all <?php echo empty( $active_cat ) ? 'bg-brand-primary text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[44px]">
								<span>Tất cả các ngành</span>
								<?php
								$total_majors = wp_count_posts( 'major' )->publish;
								?>
								<span class="text-xs px-2 py-0.5 rounded-full font-bold <?php echo empty( $active_cat ) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'; ?>"><?php echo esc_html( $total_majors ); ?></span>
							</a>
						</li>
						<?php if ( ! is_wp_error( $major_cats ) && ! empty( $major_cats ) ) : ?>
							<?php foreach ( $major_cats as $cat ) : ?>
								<?php
								$is_active = ( $active_cat === $cat->slug );
								$term_count = $cat->count;
								?>
								<li>
									<a href="<?php echo esc_url( add_query_arg( 'nhom_nganh', $cat->slug ) ); ?>" class="flex items-center justify-between px-3 py-3 rounded-lg text-sm font-semibold transition-all <?php echo $is_active ? 'bg-brand-primary text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[44px]">
										<span><?php echo esc_html( $cat->name ); ?></span>
										<span class="text-xs px-2 py-0.5 rounded-full font-bold <?php echo $is_active ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'; ?>"><?php echo esc_html( $term_count ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</div>
			</details>

			<!-- Main Content -->
			<div class="lg:col-span-3">
				<!-- Limit Dropdown Selector -->
				<div class="flex flex-col sm:flex-row items-center justify-between mb-8 pb-4 border-b border-slate-200 gap-4">
					<p class="text-sm font-medium text-slate-500">Danh sách các ngành đào tạo tuyển sinh đại học trực tuyến và liên thông.</p>
					<div class="flex items-center gap-3">
						<label for="limit-select" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Số lượng hiển thị:</label>
						<select id="limit-select" class="rounded-lg border-slate-300 text-sm py-2.5 px-3 bg-white text-slate-700 focus:border-brand-primary focus:ring-1 focus:ring-brand-primary cursor-pointer shadow-sm min-h-[44px]" onchange="location = this.value;">
							<option value="<?php echo esc_url( add_query_arg( 'limit', -1 ) ); ?>" <?php selected( isset($_GET['limit']) ? intval($_GET['limit']) : -1, -1 ); ?>>Tất cả</option>
							<option value="<?php echo esc_url( add_query_arg( 'limit', 10 ) ); ?>" <?php selected( isset($_GET['limit']) ? intval($_GET['limit']) : -1, 10 ); ?>>10</option>
							<option value="<?php echo esc_url( add_query_arg( 'limit', 20 ) ); ?>" <?php selected( isset($_GET['limit']) ? intval($_GET['limit']) : -1, 20 ); ?>>20</option>
							<option value="<?php echo esc_url( add_query_arg( 'limit', 30 ) ); ?>" <?php selected( isset($_GET['limit']) ? intval($_GET['limit']) : -1, 30 ); ?>>30</option>
							<option value="<?php echo esc_url( add_query_arg( 'limit', 50 ) ); ?>" <?php selected( isset($_GET['limit']) ? intval($_GET['limit']) : -1, 50 ); ?>>50</option>
							<option value="<?php echo esc_url( add_query_arg( 'limit', 100 ) ); ?>" <?php selected( isset($_GET['limit']) ? intval($_GET['limit']) : -1, 100 ); ?>>100</option>
						</select>
					</div>
				</div>

				<div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">
					<?php
					if ( have_posts() ) :
						while ( have_posts() ) : the_post();
							$major_id = get_the_ID();
							$code = get_field( 'major_code', $major_id ) ?: 'Đang cập nhật';
							$thumb = get_the_post_thumbnail_url( $major_id, 'medium' );
							if ( ! $thumb ) {
								$thumb = get_stylesheet_directory_uri() . '/assets/images/banner-default.jpg';
							}
					?>
							<div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
								<div class="h-28 md:h-44 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $thumb ); ?>');"></div>
								<div class="p-3 md:p-6 flex-1 flex flex-col justify-between">
									<div>
										<h3 class="font-extrabold text-slate-800 text-xs md:text-lg hover:text-brand-primary mb-1 line-clamp-2 min-h-[32px] md:min-h-[48px] leading-tight">
											<a href="<?php the_permalink(); ?>">Ngành <?php the_title(); ?></a>
										</h3>
										<span class="text-[10px] md:text-sm text-slate-400 block mb-2 md:mb-3 font-semibold uppercase">Mã: <?php echo esc_html( $code ); ?></span>
										<div class="text-sm text-slate-500 line-clamp-3 mb-6 hidden md:block"><?php the_excerpt(); ?></div>
									</div>

									<div class="border-t border-slate-100 pt-3 md:pt-4 mt-auto">
										<a href="<?php the_permalink(); ?>" class="block w-full text-center bg-slate-50 hover:bg-brand-primary hover:text-white py-2.5 md:py-3 rounded-lg font-bold transition-all text-[11px] md:text-sm uppercase text-brand-primary min-h-[44px] flex items-center justify-center">Tìm hiểu thêm</a>
									</div>
								</div>
							</div>
					<?php
						endwhile;
					else :
						echo '<div class="col-span-2 text-center py-12"><p class="text-slate-500 text-base">Chưa có ngành học nào thuộc nhóm ngành này.</p></div>';
					endif;
					?>
				</div>

				<!-- Pagination -->
				<div class="mt-12 flex justify-center theme-pagination">
					<?php
					the_posts_pagination( [
						'mid_size'  => 2,
						'prev_text' => '← Trước',
						'next_text' => 'Sau →',
					] );
					?>
				</div>
			</div>

		</div>

	</div>
</main>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		const sidebar = document.getElementById('sidebar-filters');
		if (sidebar && window.innerWidth < 1024) {
			sidebar.removeAttribute('open');
		}
	});
</script>

<?php
get_footer();
