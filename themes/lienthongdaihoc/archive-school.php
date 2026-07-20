<?php
/**
 * Archive School Directory Template — List/Card Toggle
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$view_mode = isset( $_GET['view'] ) && in_array( $_GET['view'], [ 'list', 'card' ], true ) ? $_GET['view'] : 'list';
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

		<?php
		// Query featured schools
		$featured_args = [
			'post_type'      => 'school',
			'posts_per_page' => 6,
			'post_status'    => 'publish',
			'meta_query'     => [
				[
					'key'     => 'is_featured',
					'value'   => '1',
					'compare' => '='
				]
			]
		];
		$featured_query = new WP_Query( $featured_args );
		$featured_posts = $featured_query->posts;

		// If less than 6, query latest schools to fill the remaining slots
		if ( count( $featured_posts ) < 6 ) {
			$exclude_ids = wp_list_pluck( $featured_posts, 'ID' );
			$fallback_args = [
				'post_type'      => 'school',
				'posts_per_page' => 6 - count( $featured_posts ),
				'post_status'    => 'publish',
				'post__not_in'   => ! empty( $exclude_ids ) ? $exclude_ids : [],
			];
			$fallback_query = new WP_Query( $fallback_args );
			$featured_posts = array_merge( $featured_posts, $fallback_query->posts );
		}

		if ( ! empty( $featured_posts ) ) :
		?>
			<!-- Section: Trường nổi bật (3-column grid) -->
			<div class="mb-16">
				<div class="flex items-center gap-3 mb-6 border-b border-slate-200 pb-4">
					<span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-primary text-white">
						<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.371 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.97-2.883a1 1 0 00-1.176 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.3c-.783-.57-.373-1.81.588-1.81h4.906a1 1 0 00.95-.69l1.519-4.674z"/>
						</svg>
					</span>
					<div>
						<h2 class="text-xl md:text-2xl font-extrabold text-slate-900">Trường đại học nổi bật</h2>
						<p class="text-xs text-slate-500 mt-0.5">Các trường đại học liên kết tuyển sinh hàng đầu với chất lượng đào tạo vượt trội.</p>
					</div>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
					<?php
					foreach ( $featured_posts as $featured_post ) :
						$school_id = $featured_post->ID;
						$logo_id   = get_field( 'logo', $school_id );
						$en_name   = get_post_meta( $school_id, 'english_name', true ) ?: 'University';

						$offered_program_ids = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
						$prog_count = is_array( $offered_program_ids ) ? count( $offered_program_ids ) : 0;

						$school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );
						$systems_label = ( ! is_wp_error( $school_types ) && ! empty( $school_types ) ) ? implode( ' · ', $school_types ) : '';
						
						$address = get_field( 'address', $school_id ) ?: 'Việt Nam';
						$region_terms = wp_get_post_terms( $school_id, LTDH_TAX_REGION );
						$region = ( ! is_wp_error( $region_terms ) && ! empty( $region_terms ) ) ? $region_terms[0]->name : '';
					?>
						<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
							<div class="relative h-40 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( get_the_post_thumbnail_url( $school_id, 'large' ) ?: ltdh_get_fallback_image( 'school' ) ); ?>');">
								<div class="absolute inset-0 bg-gradient-to-t from-slate-900/50 via-slate-900/20 to-transparent"></div>
								<span class="absolute top-3 left-3 bg-brand-accent text-white text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full tracking-wider shadow-sm z-10 flex items-center gap-1">
									⭐️ Nổi bật
								</span>
							</div>

							<!-- Floating Logo -->
							<div class="h-16 w-16 bg-white rounded-xl border-4 border-white shadow-md -mt-8 mx-auto z-10 relative flex items-center justify-center overflow-hidden p-1 transition-transform group-hover:scale-105 duration-300">
								<?php if ( $logo_id ) : ?>
									<?php echo wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
								<?php else : ?>
									<span class="font-display font-extrabold text-brand-primary text-sm">UNI</span>
								<?php endif; ?>
							</div>

							<div class="p-6 pt-3 flex-1 flex flex-col justify-between">
								<div class="text-center">
									<h4 class="font-extrabold text-slate-800 text-sm md:text-base tracking-tight leading-snug uppercase min-h-[48px] line-clamp-2 mt-1 group-hover:text-brand-primary transition-colors">
										<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>"><?php echo esc_html( get_the_title( $school_id ) ); ?></a>
									</h4>
									<p class="text-xs text-slate-400 mt-1 font-medium line-clamp-1 italic"><?php echo esc_html( $en_name ); ?></p>
									
									<div class="mt-4 space-y-2 text-center text-xs">
										<?php if ( ! empty( $systems_label ) ) : ?>
											<span class="font-bold text-brand-primary bg-blue-50 px-3 py-1 rounded-full inline-block leading-none"><?php echo esc_html( $systems_label ); ?></span>
										<?php endif; ?>
										
										<div class="flex items-center justify-center gap-x-4 gap-y-1 flex-wrap text-slate-500 font-medium pt-2">
											<span class="flex items-center gap-1">📍 <?php echo esc_html( $region ?: 'Việt Nam' ); ?></span>
											<span class="flex items-center gap-1">📊 <?php echo esc_html( $prog_count ); ?> ngành</span>
										</div>
									</div>
								</div>
								
								<div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
									<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="w-full text-center bg-brand-primary hover:bg-[#002263] text-white py-2.5 rounded-lg font-bold transition-all text-xs uppercase tracking-wider">Tìm hiểu chi tiết</a>
								</div>
							</div>
						</div>
					<?php
					endforeach;
					?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Header with view toggle -->
		<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 pb-4 border-b border-slate-200 gap-4">
			<p class="text-sm font-medium text-slate-500">Hiển thị tất cả các trường đại học liên kết tuyển sinh chính thức.</p>
			<a href="<?php echo esc_url( add_query_arg( 'view', 'list' === $view_mode ? 'card' : 'list' ) ); ?>"
			   class="flex items-center justify-center w-10 h-10 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-brand-accent transition-all"
			   title="<?php echo 'list' === $view_mode ? 'Chuyển sang Card' : 'Chuyển sang List'; ?>">
				<?php if ( 'list' === $view_mode ) : ?>
					<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
				<?php else : ?>
					<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
				<?php endif; ?>
			</a>
		</div>

		<?php if ( 'card' === $view_mode ) : ?>
		<!-- ============ CARD VIEW ============ -->
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					$school_id = get_the_ID();
					$logo_id   = get_field( 'logo', $school_id );
					$en_name   = get_post_meta( $school_id, 'english_name', true ) ?: 'University';

					$offered_program_ids = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
					$prog_count = is_array( $offered_program_ids ) ? count( $offered_program_ids ) : 0;

					$school_types = wp_get_post_terms( $school_id, LTDH_TAX_TRAINING_TYPE, [ 'fields' => 'names' ] );
					$systems_label = ( ! is_wp_error( $school_types ) && ! empty( $school_types ) ) ? implode( ' · ', $school_types ) : '';
			?>
				<div class="bg-white border border-slate-100 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
					<div class="h-20 md:h-28 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( get_the_post_thumbnail_url( $school_id, 'medium' ) ?: ltdh_get_fallback_image( 'school' ) ); ?>');"></div>

					<div class="h-12 w-12 md:h-16 md:w-16 bg-white rounded-lg border-2 md:border-4 border-white shadow-md bg-white -mt-6 md:-mt-8 mx-auto z-10 relative flex items-center justify-center overflow-hidden">
						<?php if ( $logo_id ) : ?>
							<?php echo wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
						<?php else : ?>
							<span class="font-display font-extrabold text-brand-primary text-xs">UNI</span>
						<?php endif; ?>
					</div>

					<div class="p-4 pt-2 flex-1 flex flex-col justify-between">
						<div class="text-center">
							<h4 class="font-extrabold text-slate-800 text-xs md:text-sm tracking-tight leading-snug uppercase min-h-[36px] line-clamp-2 mt-1"><?php the_title(); ?></h4>
							<p class="text-[11px] text-slate-400 mt-0.5 font-medium line-clamp-1 italic"><?php echo esc_html( $en_name ); ?></p>
							<div class="mt-3 space-y-1 text-center text-[10px] md:text-xs">
								<?php if ( ! empty( $systems_label ) ) : ?>
									<span class="font-bold text-brand-primary bg-blue-50 px-2.5 py-1 rounded-full inline-block leading-none mb-1.5"><?php echo esc_html( $systems_label ); ?></span>
								<?php endif; ?>
								<p class="text-slate-500 font-semibold">📊 <?php echo esc_html( $prog_count ); ?> ngành đào tạo</p>
							</div>
						</div>
						<div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-sm text-slate-600">
							<a href="<?php the_permalink(); ?>" class="w-full text-center bg-slate-50 hover:bg-brand-primary hover:text-white py-2 rounded-lg font-bold transition-all text-xs uppercase text-brand-primary">Tìm hiểu thêm</a>
						</div>
					</div>
				</div>
			<?php
				endwhile;
			else :
				echo '<div class="col-span-4 text-center py-12"><p class="text-slate-500 text-base">Chưa có trường liên kết nào.</p></div>';
			endif;
			?>
		</div>

		<?php else : ?>
		<!-- ============ LIST VIEW ============ -->
		<div class="space-y-4">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					$school_id = get_the_ID();
					$address   = get_field( 'address', $school_id ) ?: 'Việt Nam';
					$hotline   = ltdh_get_school_hotline( $school_id );
					$logo_id   = get_field( 'logo', $school_id );
					$en_name   = get_post_meta( $school_id, 'english_name', true ) ?: '';

					$offered_program_ids = get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
					$prog_count = is_array( $offered_program_ids ) ? count( $offered_program_ids ) : 0;

					$prog_tags = [];
					if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
						$tag_ids = array_slice( $offered_program_ids, 0, 5 );
						foreach ( $tag_ids as $tid ) {
							$title = get_the_title( $tid );
							if ( $title ) {
								$prog_tags[] = [
									'title' => $title,
									'link'  => get_permalink( $tid ),
								];
							}
						}
					}

					$region_terms = wp_get_post_terms( $school_id, LTDH_TAX_REGION );
					$region = ( ! is_wp_error( $region_terms ) && ! empty( $region_terms ) ) ? $region_terms[0]->name : '';

					$training_modes = [];
					if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
						foreach ( $offered_program_ids as $pid ) {
							$terms = wp_get_post_terms( $pid, LTDH_TAX_TRAINING_TYPE );
							if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
								foreach ( $terms as $term ) {
									if ( ! in_array( $term->name, $training_modes ) ) {
										$training_modes[] = $term->name;
									}
								}
							}
						}
					}
			?>
			<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all">
				<div class="flex flex-col sm:flex-row items-stretch">
					<div class="sm:w-36 h-32 sm:h-auto bg-cover bg-center shrink-0 border-b sm:border-b-0 sm:border-r border-slate-100" style="background-image: url('<?php echo esc_url( get_the_post_thumbnail_url( $school_id, 'medium' ) ?: ltdh_get_fallback_image( 'school' ) ); ?>');"></div>
					<div class="flex-1 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6">
						<div class="flex-1 min-w-0">
							<div class="flex items-center gap-2">
								<?php if ( $logo_id ) : ?>
									<div class="h-8 w-8 bg-white border border-slate-100 rounded shrink-0 flex items-center justify-center overflow-hidden p-0.5">
										<?php echo wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
									</div>
								<?php endif; ?>
								<h3 class="font-extrabold text-slate-900 text-base sm:text-lg leading-tight">
									<a href="<?php the_permalink(); ?>" class="hover:text-brand-accent transition-colors"><?php the_title(); ?></a>
								</h3>
							</div>
							<?php if ( $en_name ) : ?>
								<p class="text-xs text-slate-400 italic mt-0.5"><?php echo esc_html( $en_name ); ?></p>
							<?php endif; ?>
							<div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs text-slate-500">
								<span class="flex items-center gap-1"><span class="text-brand-primary">📍</span> <?php echo esc_html( $address ); ?></span>
								<?php if ( $region ) : ?>
									<span class="flex items-center gap-1"><span class="text-brand-primary">🗺</span> <?php echo esc_html( $region ); ?></span>
								<?php endif; ?>
								<?php if ( $prog_count > 0 ) : ?>
									<span class="flex items-center gap-1"><span class="text-brand-primary">📊</span> <?php echo esc_html( $prog_count ); ?> chương trình</span>
								<?php endif; ?>
								<?php if ( ! empty( $training_modes ) ) : ?>
									<span class="flex items-center gap-1"><span class="text-brand-primary">🎓</span> <?php echo esc_html( implode( ', ', $training_modes ) ); ?></span>
								<?php endif; ?>
							</div>
							<?php if ( ! empty( $prog_tags ) ) : ?>
								<div class="flex flex-wrap gap-1.5 mt-2">
									<?php foreach ( $prog_tags as $tag ) : ?>
										<a href="<?php echo esc_url( $tag['link'] ); ?>" class="inline-block bg-blue-50 text-brand-primary text-[10px] font-bold px-2 py-0.5 rounded-full hover:bg-blue-100 transition-colors"><?php echo esc_html( $tag['title'] ); ?></a>
									<?php endforeach; ?>
									<?php if ( $prog_count > 5 ) : ?>
										<a href="<?php the_permalink(); ?>" class="inline-block bg-slate-100 text-slate-500 text-[10px] font-bold px-2 py-0.5 rounded-full hover:bg-slate-200 transition-colors">+<?php echo esc_html( $prog_count - 5 ); ?> nữa</a>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
						<div class="flex items-center gap-2 shrink-0">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex items-center gap-1.5 bg-brand-accent text-white px-4 py-2.5 rounded-lg font-bold text-xs hover:bg-[#e06e00] transition-all min-h-[40px]">📞 Gọi ngay</a>
							<a href="<?php the_permalink(); ?>" class="flex items-center gap-1.5 bg-slate-100 text-slate-700 px-4 py-2.5 rounded-lg font-bold text-xs hover:bg-slate-200 transition-all min-h-[40px]">Chi tiết →</a>
						</div>
					</div>
				</div>
			</div>
			<?php
				endwhile;
			else :
				echo '<div class="text-center py-12"><p class="text-slate-500 text-base">Chưa có trường liên kết nào.</p></div>';
			endif;
			?>
		</div>
		<?php endif; ?>

		<!-- Pagination -->
		<?php if ( have_posts() && $wp_query->max_num_pages > 1 ) : ?>
		<div class="mt-12 flex justify-center">
			<?php the_posts_pagination( [ 'mid_size' => 2, 'prev_text' => '← Trước', 'next_text' => 'Sau →' ] ); ?>
		</div>
		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
