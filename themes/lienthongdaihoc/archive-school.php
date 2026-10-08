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

$view_mode = isset( $_GET['view'] ) && in_array( $_GET['view'], [ 'list', 'card' ], true ) ? $_GET['view'] : ( wp_is_mobile() ? 'card' : 'list' );
?>

<main id="primary" class="site-main bg-slate-50">
	<?php get_template_part( 'template-parts/banner' ); ?>
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

		<?php
		// Query featured schools (Cached for performance)
		$cache_key = 'ltdh_archive_school_featured';
		$featured_posts = get_transient( $cache_key );
		if ( false === $featured_posts || ! is_array( $featured_posts ) ) {
			$all_school_candidates = get_posts( [
				'post_type'        => 'school',
				'numberposts'      => 100,
				'post_status'      => 'publish',
				'suppress_filters' => false,
			] );

			$all_school_candidates = function_exists( 'ltdh_sort_schools_by_order' )
				? ltdh_sort_schools_by_order( $all_school_candidates )
				: $all_school_candidates;

			$featured_posts = array_slice( $all_school_candidates, 0, 4 );
			set_transient( $cache_key, $featured_posts, 2 * HOUR_IN_SECONDS );
		}


		$featured_school_ids = wp_list_pluck( $featured_posts, 'ID' );

		if ( ! empty( $featured_posts ) ) :
		?>
			<!-- Section: Trường nổi bật (4-column grid) -->
			<div class="mb-16">
				<div class="flex items-center gap-3 mb-6 border-b border-slate-200 pb-4">
					<span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-primary text-white">
						<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.371 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.97-2.883a1 1 0 00-1.176 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.3c-.783-.57-.373-1.81.588-1.81h4.906a1 1 0 00.95-.69l1.519-4.674z"/>
						</svg>
					</span>
					<div>
						<h2 class="text-xl md:text-2xl font-extrabold text-slate-900">Trường đối tác nổi bật</h2>
						<p class="text-sm text-slate-500 mt-0.5">Các trường đại học đối tác tuyển sinh hàng đầu với chất lượng đào tạo vượt trội.</p>
					</div>
				</div>

				<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
					<?php
					$featured_count = count( $featured_posts );
					$featured_index = 0;
					foreach ( $featured_posts as $featured_post ) :
						$school_id   = $featured_post->ID;
						$logo_id     = get_field( 'logo', $school_id );
						$en_name     = get_post_meta( $school_id, 'english_name', true ) ?: 'University';
						$school_code = get_post_meta( $school_id, 'school_code', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'school_code', $school_id ) : '' );

						$prog_count = ltdh_get_school_unique_majors_count( $school_id );

						$school_types = ltdh_get_school_training_types( $school_id );
						$systems_label = ( ! empty( $school_types ) ) ? implode( ' · ', $school_types ) : '';
						
						$address = get_field( 'address', $school_id ) ?: 'Việt Nam';
						$region_terms = wp_get_post_terms( $school_id, LTDH_TAX_REGION );
						$region = ( ! is_wp_error( $region_terms ) && ! empty( $region_terms ) ) ? $region_terms[0]->name : '';

						$grid_span_class = '';
						if ( $featured_count % 2 !== 0 && $featured_index === $featured_count - 1 ) {
							$grid_span_class = 'col-span-2 lg:col-span-1';
						}
					?>
						<div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group<?php echo $grid_span_class ? ' ' . esc_attr( $grid_span_class ) : ''; ?>">
							<!-- Card Cover & Badges -->
							<div class="relative h-36 sm:h-40 bg-slate-200 bg-cover bg-center overflow-hidden" style="background-image: url('<?php echo esc_url( function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'large' ) : ( get_the_post_thumbnail_url( $school_id, 'large' ) ?: ltdh_get_fallback_image( 'school' ) ) ); ?>');">
								<div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-slate-900/20 to-transparent"></div>
								
								<!-- Featured Pill -->
								<span class="absolute top-3 left-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[11px] font-black uppercase px-2.5 py-1 rounded-full tracking-wider shadow-sm z-10 flex items-center gap-1.5">
									<svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
										<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
									</svg>
									<span>Nổi bật</span>
								</span>

								<!-- Location Pill on Cover (if available) -->
								<?php if ( $region ) : ?>
									<span class="absolute top-3 right-3 bg-slate-900/60 backdrop-blur-md text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full z-10 flex items-center gap-1 border border-white/20 shadow-xs">
										<svg class="w-3 h-3 text-white/80 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
											<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
											<path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
										</svg>
										<span><?php echo esc_html( $region ); ?></span>
									</span>
								<?php endif; ?>
							</div>

							<!-- Floating Logo (Left-anchored for clean architectural balance) -->
							<div class="h-14 w-14 sm:h-16 sm:w-16 bg-white rounded-2xl border-2 border-white shadow-[0_4px_16px_rgba(0,0,0,0.12)] -mt-7 sm:-mt-8 ml-4 sm:ml-5 z-10 relative flex items-center justify-center overflow-hidden p-1.5 transition-transform duration-300 group-hover:scale-105">
								<?php
								$school_logo_url = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'thumbnail' ) : '';
								if ( ! empty( $school_logo_url ) ) :
								?>
									<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( get_the_title( $school_id ) ); ?>" class="h-full w-full object-contain p-0.5" loading="lazy">
								<?php elseif ( $logo_id ) : ?>
									<?php echo wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
								<?php else : ?>
									<span class="font-display font-extrabold text-[#00308b] text-sm">UNI</span>
								<?php endif; ?>
							</div>

							<!-- Card Body (Left-aligned & Structured Hierarchy) -->
							<div class="p-4 sm:p-5 pt-2.5 flex-1 flex flex-col justify-between text-left">
								<div>
									<!-- Code & Location Mini Row -->
									<div class="flex items-center gap-2 mb-1 min-h-[20px]">
										<?php if ( ! empty( $school_code ) ) : ?>
											<span class="font-bold text-[#00308b] bg-blue-50 px-1.5 py-0.5 rounded text-[10px] border border-blue-100/80">Mã: <?php echo esc_html( $school_code ); ?></span>
										<?php endif; ?>
										<?php if ( ! empty( $region ) ) : ?>
											<span class="text-xs text-slate-500 font-medium truncate"><?php echo esc_html( $region ); ?></span>
										<?php endif; ?>
									</div>

									<!-- School Name -->
									<h3 class="font-extrabold text-slate-900 text-sm md:text-[15px] tracking-tight leading-snug min-h-[44px] line-clamp-2 mt-0.5 group-hover:text-[#00308b] transition-colors">
										<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>">
											<?php echo esc_html( get_the_title( $school_id ) ); ?>
										</a>
									</h3>
									
									<!-- Training Types Badges Container -->
									<div class="mt-3 min-h-[38px] flex items-center">
										<?php if ( ! empty( $school_types ) && ! is_wp_error( $school_types ) ) : ?>
											<div class="flex flex-wrap gap-1.5">
												<?php
												foreach ( $school_types as $st_term ) {
													echo ltdh_get_training_type_badge_html( $st_term );
												}
												?>
											</div>
										<?php else : ?>
											<div class="flex flex-wrap gap-1.5">
												<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500">
													<span>Đang cập nhật hệ đào tạo</span>
												</span>
											</div>
										<?php endif; ?>
									</div>

									<!-- Meta Row (Khu vực / Cơ sở & Ngành đào tạo) -->
									<div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600 gap-2">
										<div class="flex items-center gap-1.5 text-slate-500 truncate" title="<?php echo esc_attr( $address ); ?>">
											<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
												<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
												<path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
											</svg>
											<span class="truncate"><?php echo esc_html( ltdh_get_school_location_label( $school_id ) ); ?></span>
										</div>

										<div class="flex items-center gap-1 shrink-0">
											<?php if ( $prog_count > 0 ) : ?>
												<span class="inline-flex items-center gap-1 text-slate-700 font-semibold">
													<svg class="w-3.5 h-3.5 text-[#00308b] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
														<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
													</svg>
													<span><strong class="text-[#00308b] font-black"><?php echo esc_html( $prog_count ); ?></strong> ngành</span>
												</span>
											<?php else : ?>
												<span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/60">
													<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
													Đang cập nhật chỉ tiêu
												</span>
											<?php endif; ?>
										</div>
									</div>
								</div>
								
								<!-- Action CTA Button -->
								<div class="mt-3.5 pt-3 border-t border-slate-100">
									<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" 
									   class="w-full text-center py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold text-[#00308b] bg-blue-50/90 hover:bg-[#00308b] hover:text-white border border-blue-200/80 hover:border-[#00308b] transition-all duration-200 flex items-center justify-center gap-1.5 shadow-2xs group-hover:shadow-sm">
										<span>Tìm hiểu chi tiết</span>
										<svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
											<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
										</svg>
									</a>
								</div>
							</div>
						</div>
					<?php
						$featured_index++;
					endforeach;
					?>
				</div>
			</div>
		<?php endif; ?>

		<?php
		// Count non-featured schools in the query
		global $wp_query;
		$non_featured_count = 0;
		if ( have_posts() && ! empty( $wp_query->posts ) ) {
			foreach ( $wp_query->posts as $p ) {
				if ( empty( $featured_school_ids ) || ! in_array( $p->ID, $featured_school_ids, true ) ) {
					$non_featured_count++;
				}
			}
		}
		$total_schools_count = count( $featured_posts ) + $non_featured_count;

		if ( $total_schools_count === 0 ) :
		?>
			<div class="text-center py-12">
				<p class="text-slate-500 text-base">Chưa có trường đối tác nào.</p>
			</div>
		<?php
		endif;

		if ( $non_featured_count > 0 ) :
		?>
			<!-- Header with view toggle -->
			<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 pb-4 border-b border-slate-200 gap-4">
				<p class="text-sm font-medium text-slate-500">Hiển thị tất cả các trường đại học đối tác tuyển sinh chính thức.</p>
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
			<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
				<?php
				if ( have_posts() ) :
					$regular_index = 0;
					while ( have_posts() ) : the_post();
						$school_id = get_the_ID();
						if ( ! empty( $featured_school_ids ) && in_array( $school_id, $featured_school_ids, true ) ) {
							continue;
						}
						$logo_id     = get_field( 'logo', $school_id );
						$en_name     = get_post_meta( $school_id, 'english_name', true ) ?: 'University';
						$school_code = get_post_meta( $school_id, 'school_code', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'school_code', $school_id ) : '' );

						$prog_count = ltdh_get_school_unique_majors_count( $school_id );

						$school_types = ltdh_get_school_training_types( $school_id );
						$systems_label = ( ! empty( $school_types ) ) ? implode( ' · ', $school_types ) : '';
						
						$address = get_field( 'address', $school_id ) ?: 'Việt Nam';
						$region_terms = wp_get_post_terms( $school_id, LTDH_TAX_REGION );
						$region = ( ! is_wp_error( $region_terms ) && ! empty( $region_terms ) ) ? $region_terms[0]->name : '';

						$grid_span_class = '';
						if ( $non_featured_count % 2 !== 0 && $regular_index === $non_featured_count - 1 ) {
							$grid_span_class = 'col-span-2 lg:col-span-1';
						}
				?>
					<div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group<?php echo $grid_span_class ? ' ' . esc_attr( $grid_span_class ) : ''; ?>">
						<!-- Card Cover & Location -->
						<div class="relative h-32 sm:h-36 bg-slate-200 bg-cover bg-center overflow-hidden" style="background-image: url('<?php echo esc_url( function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'medium' ) : ( get_the_post_thumbnail_url( $school_id, 'medium' ) ?: ltdh_get_fallback_image( 'school' ) ) ); ?>');">
							<div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-slate-900/20 to-transparent"></div>
							<?php if ( $region ) : ?>
								<span class="absolute top-2.5 right-2.5 bg-slate-900/60 backdrop-blur-md text-white text-[10px] font-semibold px-2 py-0.5 rounded-full z-10 flex items-center gap-1 border border-white/20 shadow-xs">
									<svg class="w-3 h-3 text-white/80 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
										<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
										<path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
									</svg>
									<span><?php echo esc_html( $region ); ?></span>
								</span>
							<?php endif; ?>
						</div>

						<!-- Floating Logo (Left-anchored for clean architectural balance) -->
						<div class="h-14 w-14 sm:h-16 sm:w-16 bg-white rounded-2xl border-2 border-white shadow-[0_4px_16px_rgba(0,0,0,0.12)] -mt-7 sm:-mt-8 ml-4 sm:ml-5 z-10 relative flex items-center justify-center overflow-hidden p-1.5 transition-transform duration-300 group-hover:scale-105">
							<?php
							$school_logo_url = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'thumbnail' ) : '';
							if ( ! empty( $school_logo_url ) ) :
							?>
								<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( get_the_title( $school_id ) ); ?>" class="h-full w-full object-contain p-0.5" loading="lazy">
							<?php elseif ( $logo_id ) : ?>
								<?php echo wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
							<?php else : ?>
								<span class="font-display font-extrabold text-[#00308b] text-xs">UNI</span>
							<?php endif; ?>
						</div>

						<!-- Card Body (Left-aligned & Structured Hierarchy) -->
						<div class="p-4 sm:p-5 pt-2.5 flex-1 flex flex-col justify-between text-left">
							<div>
								<!-- Code & Location Mini Row -->
								<div class="flex items-center gap-2 mb-1 min-h-[20px]">
									<?php if ( ! empty( $school_code ) ) : ?>
										<span class="font-bold text-[#00308b] bg-blue-50 px-1.5 py-0.5 rounded text-[10px] border border-blue-100/80">Mã: <?php echo esc_html( $school_code ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $region ) ) : ?>
										<span class="text-xs text-slate-500 font-medium truncate"><?php echo esc_html( $region ); ?></span>
									<?php endif; ?>
								</div>

								<!-- School Name -->
								<h3 class="font-extrabold text-slate-900 text-sm tracking-tight leading-snug min-h-[40px] line-clamp-2 mt-0.5 group-hover:text-[#00308b] transition-colors">
									<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>">
										<?php the_title(); ?>
									</a>
								</h3>
								
								<!-- Training Types / Status Badges Container -->
								<div class="mt-3 min-h-[38px] flex items-center">
									<?php if ( ! empty( $school_types ) && ! is_wp_error( $school_types ) ) : ?>
										<div class="flex flex-wrap gap-1.5">
											<?php
											foreach ( $school_types as $st_term ) {
												echo ltdh_get_training_type_badge_html( $st_term );
											}
											?>
										</div>
									<?php else : ?>
										<div class="flex flex-wrap gap-1.5">
											<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500">
												<span>Đang cập nhật hệ đào tạo</span>
											</span>
										</div>
									<?php endif; ?>
								</div>

								<!-- Meta Row (Khu vực / Cơ sở & Ngành đào tạo) -->
								<div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600 gap-2">
									<div class="flex items-center gap-1 text-slate-500 truncate" title="<?php echo esc_attr( $address ); ?>">
										<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
											<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
											<path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
										</svg>
										<span class="truncate"><?php echo esc_html( ltdh_get_school_location_label( $school_id ) ); ?></span>
									</div>

									<div class="flex items-center gap-1 shrink-0">
										<?php if ( $prog_count > 0 ) : ?>
											<span class="inline-flex items-center gap-1 text-slate-700 font-semibold">
												<svg class="w-3.5 h-3.5 text-[#00308b] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
												</svg>
												<span><strong class="text-[#00308b] font-black"><?php echo esc_html( $prog_count ); ?></strong> ngành</span>
											</span>
										<?php else : ?>
											<span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/60">
												<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
												Đang cập nhật chỉ tiêu
											</span>
										<?php endif; ?>
									</div>
								</div>
							</div>
							
							<!-- Action CTA Button -->
							<div class="mt-3 pt-2.5 border-t border-slate-100">
								<a href="<?php the_permalink(); ?>" 
								   class="w-full text-center py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold text-[#00308b] bg-blue-50/90 hover:bg-[#00308b] hover:text-white border border-blue-200/80 hover:border-[#00308b] transition-all duration-200 flex items-center justify-center gap-1.5 shadow-2xs group-hover:shadow-sm">
									<span>Tìm hiểu chi tiết</span>
									<svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
										<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
									</svg>
								</a>
							</div>
					</div>
				<?php
						$regular_index++;
					endwhile;
				else :
					echo '<div class="col-span-4 text-center py-12"><p class="text-slate-500 text-base">Chưa có trường đối tác nào.</p></div>';
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
						if ( ! empty( $featured_school_ids ) && in_array( $school_id, $featured_school_ids, true ) ) {
							continue;
						}
						$address   = get_field( 'address', $school_id ) ?: 'Việt Nam';
						$hotline   = ltdh_get_school_hotline( $school_id );
						$logo_id   = get_field( 'logo', $school_id );
						$en_name   = get_post_meta( $school_id, 'english_name', true ) ?: '';

						$prog_count = ltdh_get_school_unique_majors_count( $school_id );
						$offered_program_ids = get_posts( [
							'post_type'      => 'program',
							'posts_per_page' => 5,
							'post_status'    => 'publish',
							'fields'         => 'ids',
							'no_found_rows'  => true,
							'meta_query'     => [
								[
									'key'     => 'school_relationship',
									'value'   => $school_id,
									'compare' => '=',
								],
							],
						] );

						$prog_tags = [];
						if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
							foreach ( $offered_program_ids as $tid ) {
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

						$training_modes = ltdh_get_school_training_types( $school_id );
				?>
				<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all">
					<div class="flex flex-col sm:flex-row items-stretch">
						<div class="sm:w-36 h-32 sm:h-auto bg-cover bg-center shrink-0 border-b sm:border-b-0 sm:border-r border-slate-100" style="background-image: url('<?php echo esc_url( function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'medium' ) : ltdh_get_fallback_image( 'school' ) ); ?>');"></div>
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
									<p class="text-sm text-slate-400 italic mt-0.5"><?php echo esc_html( $en_name ); ?></p>
								<?php endif; ?>
								<div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm text-slate-500">
									<span class="flex items-center gap-1"><span class="text-brand-primary">📍</span> <?php echo esc_html( $address ); ?></span>
									<?php if ( $prog_count > 0 ) : ?>
										<span class="flex items-center gap-1"><span class="text-brand-primary">📊</span> <?php echo esc_html( $prog_count ); ?> chương trình</span>
									<?php endif; ?>
									<?php if ( ! empty( $training_modes ) ) : ?>
										<span class="flex items-center gap-1.5">
											<span class="text-brand-primary">🎓</span>
											<span class="flex flex-wrap gap-1">
												<?php
												foreach ( $training_modes as $mode ) {
													echo ltdh_get_training_type_badge_html( $mode );
												}
												?>
											</span>
										</span>
									<?php endif; ?>
								</div>
								<?php if ( ! empty( $prog_tags ) ) : ?>
									<div class="flex flex-wrap gap-1.5 mt-2">
										<?php foreach ( $prog_tags as $tag ) : ?>
											<a href="<?php echo esc_url( $tag['link'] ); ?>" class="inline-block bg-blue-50 text-brand-primary text-xs font-bold px-2 py-0.5 rounded-full hover:bg-blue-100 transition-colors"><?php echo esc_html( $tag['title'] ); ?></a>
										<?php endforeach; ?>
										<?php if ( $prog_count > 5 ) : ?>
											<a href="<?php the_permalink(); ?>" class="inline-block bg-slate-100 text-slate-500 text-xs font-bold px-2 py-0.5 rounded-full hover:bg-slate-200 transition-colors">+<?php echo esc_html( $prog_count - 5 ); ?> nữa</a>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div>
							<div class="flex items-center gap-2 shrink-0 w-full sm:w-auto mt-3 sm:mt-0">
								<a href="<?php the_permalink(); ?>" class="w-full sm:w-auto text-center justify-center gap-1.5 px-6 py-2.5 rounded-lg text-sm ltdh-btn-details min-h-[40px] flex items-center">Tìm hiểu chi tiết</a>
							</div>
						</div>
					</div>
				</div>
				<?php
					endwhile;
				else :
					echo '<div class="text-center py-12"><p class="text-slate-500 text-base">Chưa có trường đối tác nào.</p></div>';
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
		<?php
		endif;
		?>


	</div>
</main>

<?php get_footer(); ?>
