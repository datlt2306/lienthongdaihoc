<?php
/**
 * School Programs List Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$school_id           = isset( $args['school_id'] ) ? (int) $args['school_id'] : get_the_ID();
$school_title        = isset( $args['school_title'] ) ? $args['school_title'] : get_the_title( $school_id );
$offered_program_ids = isset( $args['offered_program_ids'] ) ? $args['offered_program_ids'] : get_post_meta( $school_id, LTDH_META_OFFERED_PROGRAMS, true );
?>

<!-- PROGRAMS OFFERED -->
<section id="chuong-trinh-tuyen-sinh" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-lg shadow-sm border border-slate-100 p-4 md:p-6">
	<!-- Hidden anchor target for backward compatibility -->
	<span id="nganh-dao-tao" class="scroll-mt-36 md:scroll-mt-40 block invisible pointer-events-none h-0"></span>

	<?php
	$meta_status_filter = [
		'relation' => 'OR',
		[
			'key'     => LTDH_META_ADMISSION_STATUS,
			'value'   => 'tam-ngung',
			'compare' => '!=',
		],
		[
			'key'     => LTDH_META_ADMISSION_STATUS,
			'compare' => 'NOT EXISTS',
		],
	];

	$tax_training_type_filter = [
		'taxonomy' => LTDH_TAX_TRAINING_TYPE,
		'field'    => 'slug',
		'terms'    => [ 'chinh-quy', 'dao-tao-tu-xa', 'tu-xa', 'vua-hoc-vua-lam' ],
		'operator' => 'IN',
	];

	if ( ! empty( $offered_program_ids ) && is_array( $offered_program_ids ) ) {
		$programs_query = new WP_Query( [
			'post_type'      => 'program',
			'post__in'       => $offered_program_ids,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'tax_query'      => [
				$tax_training_type_filter,
			],
			'meta_query'     => [
				'relation' => 'AND',
				$meta_status_filter,
			],
		] );
	}

	if ( empty( $programs_query ) || ! $programs_query->have_posts() ) {
		// Fallback query if meta relationships don't exist yet or yielded no published programs
		$programs_query = new WP_Query( [
			'post_type'      => 'program',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'tax_query'      => [
				$tax_training_type_filter,
			],
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => LTDH_META_SCHOOL_REL,
					'value'   => $school_id,
					'compare' => '=',
				],
				$meta_status_filter,
			],
		] );
	}

	$majors_data = [];
	if ( $programs_query->have_posts() ) {
		$queried_ids = wp_list_pluck( $programs_query->posts, 'ID' );
		update_meta_cache( 'post', $queried_ids );
		update_object_term_cache( $queried_ids, 'program' );

		$school_thumb_url = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'thumbnail' ) : get_template_directory_uri() . '/assets/images/cropped-logo-scaled-2.webp';

		while ( $programs_query->have_posts() ) {
			$programs_query->the_post();
			$prog_id = get_the_ID();
			$major_rel_id = get_field( LTDH_META_MAJOR_REL, $prog_id );
			$major_key = $major_rel_id ? $major_rel_id : 'no_major_' . $prog_id;

			if ( ! isset( $majors_data[ $major_key ] ) ) {
				$major_name = $major_rel_id ? get_the_title( $major_rel_id ) : 'Mời tư vấn';
				$major_thumb = $major_rel_id ? get_the_post_thumbnail_url( $major_rel_id, 'medium_large' ) : '';
				if ( ! $major_thumb ) {
					$major_thumb = get_the_post_thumbnail_url( $prog_id, 'medium_large' );
				}
				if ( ! $major_thumb ) {
					$major_fallbacks = [
						get_stylesheet_directory_uri() . '/assets/images/banner-program.jpg',
						get_stylesheet_directory_uri() . '/assets/images/banner-hero-02.webp',
						get_stylesheet_directory_uri() . '/assets/images/banner-default.jpg',
					];
					$idx = absint( $prog_id ) % count( $major_fallbacks );
					$major_thumb = $major_fallbacks[ $idx ];
				}
				$major_code = $major_rel_id ? ( get_field( 'major_code', $major_rel_id ) ?: get_post_meta( $major_rel_id, 'major_code', true ) ) : '';
				$major_terms = $major_rel_id ? get_the_terms( $major_rel_id, 'major_cat' ) : [];
				$major_cat_name = ( ! empty( $major_terms ) && ! is_wp_error( $major_terms ) ) ? $major_terms[0]->name : '';

				$majors_data[ $major_key ] = [
					'id'       => $major_rel_id,
					'name'     => $major_name,
					'code'     => $major_code,
					'cat_name' => $major_cat_name,
					'thumb'    => $major_thumb,
					'programs' => [],
				];
			}

			$status = get_post_meta( $prog_id, LTDH_META_ADMISSION_STATUS, true ) ?: 'tuyen-sinh';
			$school_code = (string) ( get_field( 'school_code', $school_id ) ?: get_post_meta( $school_id, 'school_code', true ) );
			$school_short_name = (string) ( get_field( 'school_short_name', $school_id ) ?: ( get_field( 'badge', $school_id ) ?: '' ) );
			$clean_title = (string) get_the_title();
			if ( $school_title ) {
				$clean_title = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_title, '/' ) . '$/iu', '', $clean_title );
			}
			if ( $school_short_name ) {
				$clean_title = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_short_name, '/' ) . '$/iu', '', $clean_title );
			}
			if ( $school_code ) {
				$clean_title = preg_replace( '/\s*[-–—]\s*' . preg_quote( $school_code, '/' ) . '$/iu', '', $clean_title );
			}
			// Strip common school suffix abbreviations like " - ĐH GTVT", " - TMU", " - ĐH...", " - Trường...", " - Học viện..."
			$clean_title = preg_replace( '/\s*[-–—]\s*(?:ĐH|Trường ĐH|Đại học|Viện ĐH|Học viện|[A-Z0-9]{2,}).*$/iu', '', $clean_title );

			$types = wp_get_post_terms( $prog_id, 'training_type' );
			$type_name = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->name : '';
			$type_slug = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->slug : '';
			$clean_type_name = preg_replace( '/^hệ\s+/iu', '', trim( $type_name ) );
			$clean_major_name = preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) );
			$opportunity_title = 'Liên thông ngành ' . $clean_major_name . ( $clean_type_name ? ' - ' . $clean_type_name : '' );
			$tuition_fee = ltdh_get_program_tuition_display( $prog_id );
			$duration = function_exists( 'ltdh_get_program_duration' ) ? ltdh_get_program_duration( $prog_id ) : ( get_field( 'duration', $prog_id ) ?: '1.5 - 2 năm' );
			$permalink = get_permalink( $prog_id );
			$prog_slug = get_post_field( 'post_name', $prog_id );
			$major_slug = $major_rel_id ? get_post_field( 'post_name', $major_rel_id ) : '';

			// Check specialization (Chuyên ngành hẹp)
			$spec_title = get_field( 'specialization_title', $prog_id ) ?: get_post_meta( $prog_id, 'specialization_title', true );
			if ( empty( $spec_title ) ) {
				$spec_title = get_field( 'chuyen_nganh', $prog_id ) ?: get_post_meta( $prog_id, 'chuyen_nganh', true );
			}
			if ( empty( $spec_title ) ) {
				$candidate = (string) $clean_title;
				$candidate = preg_replace( '/\s*\([^)]*\)/iu', '', $candidate );
				$candidate = preg_replace( '/\s*[-–—]\s*(?:hệ\s+)?(?:từ xa|đào tạo từ xa|vừa học vừa làm|vừa làm vừa học|vhvl|chính quy|online|tập trung|liên thông).*$/iu', '', $candidate );
				$candidate = preg_replace( '/^(?:tuyển sinh\s+)?(?:liên\s+thông\s+(?:đại\s+học\s+)?)?(?:cử\s+nhân|kỹ\s+sư|ngành)\s+/iu', '', $candidate );
				$candidate = trim( $candidate, " -–—\t\n\r\0\x0B" );
				if ( ! empty( $candidate ) ) {
					$c_lower      = mb_strtolower( $candidate, 'UTF-8' );
					$m_lower      = mb_strtolower( $clean_major_name, 'UTF-8' );
					$orig_m_lower = mb_strtolower( $major_name, 'UTF-8' );
					if ( $c_lower !== $m_lower && $c_lower !== $orig_m_lower ) {
						$spec_title = $candidate;
					}
				}
			}

			// Determine badge classes based on training type name
			$badge_class = 'bg-blue-50 text-[#00308b]';

			$academic_year = get_field( 'tuition_academic_year', $prog_id ) ?: '2025 - 2026';

			$prog_data = [
				'id'                => $prog_id,
				'title'             => $clean_title,
				'specialization'    => $spec_title,
				'opportunity_title' => $opportunity_title,
				'status'            => $status,
				'type_name'         => $clean_type_name,
				'badge_class'       => $badge_class,
				'tuition_fee'       => $tuition_fee,
				'academic_year'     => $academic_year,
				'duration'          => $duration,
				'permalink'         => $permalink,
				'slug'              => $prog_slug,
				'he'                => $type_slug,
				'nganh'             => $major_slug,
				'major_name'        => $clean_major_name,
				'school_name'       => $school_title,
				'thumb'             => $school_thumb_url,
			];

			// Retain all programs without overwriting
			$majors_data[ $major_key ]['programs'][ $prog_id ] = $prog_data;
		}
		wp_reset_postdata();

		foreach ( $majors_data as $m_k => $m_v ) {
			$majors_data[ $m_k ]['programs'] = array_values( $m_v['programs'] );
		}
	}

	$available_he = [];
	$total_programs_count = 0;
	if ( ! empty( $majors_data ) ) {
		foreach ( $majors_data as $major_item ) {
			foreach ( $major_item['programs'] as $prog_item ) {
				$total_programs_count++;
				if ( ! empty( $prog_item['he'] ) ) {
					$he_slug = $prog_item['he'];
					if ( ! isset( $available_he[ $he_slug ] ) ) {
						$available_he[ $he_slug ] = [
							'name'  => $prog_item['type_name'] ?: $he_slug,
							'count' => 0,
						];
					}
					$available_he[ $he_slug ]['count']++;
				}
			}
		}
	}
	?>

	<!-- Header with Live Stats & Quick Search/Filter -->
	<div class="border-b border-slate-200/80 pb-5 mb-6">
		<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 <?php echo ( count( $majors_data ) > 1 ) ? 'mb-4' : ''; ?>">
			<div>
				<h2 class="text-xl md:text-2xl font-black text-slate-900 leading-tight">Ngành và chương trình trường đào tạo</h2>
				<p class="text-xs md:text-sm text-slate-500 mt-1">Danh sách các ngành và hình thức học tuyển sinh chính thức tại trường</p>
			</div>
			<div class="flex items-center gap-2 self-start sm:self-center shrink-0">
				<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-[#00308b] border border-blue-100/80">
					<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
					</svg>
					<span id="ltdh-majors-total-badge"><?php echo count( $majors_data ); ?> ngành tuyển sinh</span>
				</span>
			</div>
		</div>

		<?php if ( count( $majors_data ) > 1 ) : ?>
		<!-- Quick Filter Controls: Instant Search & Training System Tabs -->
		<div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 pt-3 border-t border-slate-100">
			<!-- Search Input -->
			<div class="relative flex-1">
				<span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
					<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
					</svg>
				</span>
				<label for="ltdh-major-search-input" class="sr-only">Tìm kiếm ngành học</label>
				<input type="text"
					   id="ltdh-major-search-input"
					   placeholder="Tìm kiếm ngành học..."
					   class="w-full pl-9 pr-8 py-2 rounded-xl text-xs sm:text-sm border border-slate-200 bg-slate-50/70 focus:bg-white focus:border-[#00308b] focus:ring-2 focus:ring-blue-100 outline-none transition-all placeholder:text-slate-400">
				<button type="button"
						id="ltdh-major-search-clear"
						class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
						title="Xóa tìm kiếm">
					<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
					</svg>
				</button>
			</div>

			<!-- Training Types Tabs -->
			<?php if ( ! empty( $available_he ) && count( $available_he ) > 1 ) : ?>
				<div class="flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden shrink-0" id="ltdh-he-filter-tabs">
					<button type="button"
							data-he-filter="all"
							class="ltdh-he-tab-btn active px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-[#00308b] text-white shadow-2xs cursor-pointer whitespace-nowrap">
						Tất cả (<?php echo count( $majors_data ); ?>)
					</button>
					<?php foreach ( $available_he as $he_key => $he_info ) : ?>
						<button type="button"
								data-he-filter="<?php echo esc_attr( $he_key ); ?>"
								class="ltdh-he-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-slate-100 hover:bg-slate-200/80 text-slate-700 cursor-pointer whitespace-nowrap">
							<?php echo esc_html( $he_info['name'] ); ?> (<?php echo esc_html( $he_info['count'] ); ?>)
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $majors_data ) ) : ?>
		<!-- Majors Grid (Visual Cards with Thumbnails) -->
		<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-5" id="ltdh-majors-container">
			<?php
			foreach ( $majors_data as $major ) :
				$m_id           = $major['id'];
				$major_link     = $m_id ? add_query_arg( 'truong', get_post_field( 'post_name', $school_id ), get_permalink( $m_id ) ) : '';
				$major_he_slugs = array_unique( array_column( $major['programs'], 'he' ) );
				$programs_count   = count( $major['programs'] );
				$first_prog       = ! empty( $major['programs'] ) ? $major['programs'][0] : null;
				$primary_link     = ( $first_prog && ! empty( $first_prog['permalink'] ) ) ? $first_prog['permalink'] : ( $major_link ?: '#' );
				$major_spec_names = array_unique( array_filter( array_column( $major['programs'], 'specialization' ) ) );
				?>
				<div class="ltdh-major-card group bg-white border border-slate-200/90 hover:border-blue-300 rounded-2xl overflow-hidden shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
					 data-major-name="<?php echo esc_attr( mb_strtolower( $major['name'], 'UTF-8' ) ); ?>"
					 data-major-code="<?php echo esc_attr( mb_strtolower( $major['code'] ?? '', 'UTF-8' ) ); ?>"
					 data-major-specs="<?php echo esc_attr( mb_strtolower( implode( ' ', $major_spec_names ), 'UTF-8' ) ); ?>"
					 data-major-he="<?php echo esc_attr( implode( ' ', $major_he_slugs ) ); ?>">
					
					<!-- Thumbnail Area (16:9 ratio, smooth zoom on hover) -->
					<div class="relative h-44 sm:h-48 bg-slate-100 overflow-hidden">
						<a href="<?php echo esc_url( $major_link ?: $primary_link ); ?>" class="block w-full h-full" title="Xem chi tiết ngành <?php echo esc_attr( $major['name'] ); ?>">
							<img src="<?php echo esc_url( $major['thumb'] ); ?>" 
								 alt="Ngành <?php echo esc_attr( $major['name'] ); ?>" 
								 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
								 loading="lazy">
						</a>
						<div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/20 to-transparent pointer-events-none"></div>

						<!-- Top Badges on Thumbnail: Đang tuyển sinh (Right) -->
						<div class="absolute top-2.5 right-2.5 flex items-center justify-end z-10 pointer-events-none">
							<?php 
							$major_has_active   = false;
							$major_has_upcoming = false;
							foreach ( $major['programs'] as $p_c ) {
								$p_stat = $p_c['status'] ?? 'tuyen-sinh';
								if ( 'sap-mo' === $p_stat ) {
									$major_has_upcoming = true;
								} elseif ( 'tam-ngung' !== $p_stat ) {
									$major_has_active = true;
								}
							}

							if ( $major_has_active ) : ?>
								<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white/95 text-emerald-700 shadow-sm backdrop-blur-md">
									<span class="relative flex h-2 w-2">
										<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
										<span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
									</span>
									<span>Đang tuyển sinh</span>
								</span>
							<?php elseif ( $major_has_upcoming ) : ?>
								<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white/95 text-amber-700 shadow-sm backdrop-blur-md">
									<span class="w-2 h-2 rounded-full bg-amber-500"></span>
									<span>Sắp mở đợt mới</span>
								</span>
							<?php endif; ?>
						</div>
					</div>

					<!-- Card Body -->
					<div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
						<div>
							<?php if ( ! empty( $major['cat_name'] ) ) : ?>
								<span class="text-[11px] font-bold uppercase tracking-wider text-[#00308b] mb-1 block">
									<?php echo esc_html( $major['cat_name'] ); ?>
								</span>
							<?php endif; ?>

							<!-- Major Name -->
							<h3 class="font-extrabold text-slate-900 text-base sm:text-lg group-hover:text-[#00308b] transition-colors leading-snug mb-1">
								<a href="<?php echo esc_url( $major_link ?: $primary_link ); ?>">
									Ngành <?php echo esc_html( $major['name'] ); ?>
								</a>
							</h3>

							<?php 
							$is_real_spec = ! empty( $first_prog['specialization'] )
								&& mb_strtolower( trim( $first_prog['specialization'] ), 'UTF-8' ) !== mb_strtolower( trim( $major['name'] ), 'UTF-8' )
								&& mb_strtolower( trim( $first_prog['specialization'] ), 'UTF-8' ) !== mb_strtolower( trim( preg_replace( '/^ngành\s+/iu', '', $major['name'] ) ), 'UTF-8' );
							if ( $is_real_spec ) : ?>
								<p class="text-xs sm:text-sm text-slate-500 font-medium mb-2 flex items-center gap-1.5">
									<span class="w-1.5 h-1.5 rounded-full bg-[#00308b]"></span>
									<span>Chuyên ngành: <strong class="text-slate-800 font-semibold"><?php echo esc_html( $first_prog['specialization'] ); ?></strong></span>
								</p>
							<?php endif; ?>

							<!-- Highlights Info Row -->
							<div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-sm text-slate-500 mb-3.5">
								<span class="inline-flex items-center gap-1 font-medium">
									<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
									</svg>
									<span><?php echo esc_html( ( $first_prog && ! empty( $first_prog['duration'] ) ) ? $first_prog['duration'] : '1.5 - 2 năm' ); ?></span>
								</span>
								<span class="text-slate-300">•</span>
								<span class="inline-flex items-center gap-1 text-slate-600 font-medium">
									<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z" />
									</svg>
									<span>Bằng chuẩn Bộ GD&ĐT</span>
								</span>
							</div>
						</div>

						<!-- Programs Selector Tray (Dedicated Action Area) -->
						<div class="pt-3 border-t border-slate-100 mt-auto">
							<div class="text-xs font-semibold text-slate-500 flex items-center gap-1.5 mb-2">
								<svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
								</svg>
								<span>Hệ đào tạo tuyển sinh:</span>
							</div>

							<?php 
							$he_list          = array_filter( array_column( $major['programs'], 'he' ) );
							$he_counts        = array_count_values( $he_list );
							$has_duplicate_he = ! empty( $he_counts ) && max( $he_counts ) > 1;
							?>
							<?php if ( $programs_count === 1 && $first_prog ) : 
								$prog      = $first_prog;
								$raw_type  = $prog['type_name'] ?: 'Đào tạo từ xa';
								$label     = ( 0 !== stripos( $raw_type, 'hệ' ) ) ? 'Hệ ' . $raw_type : $raw_type;
								$t_lower   = mb_strtolower( $raw_type, 'UTF-8' );

								$icon_svg  = '<svg class="w-4 h-4 shrink-0 text-[#00308b] group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" /></svg>';
								$btn_style = 'bg-blue-50 hover:bg-[#00308b] text-[#00308b] hover:text-white border border-blue-200/80 hover:border-transparent rounded-full';

								if ( false !== strpos( $t_lower, 'từ xa' ) ) {
									$icon_svg  = '<svg class="w-4 h-4 shrink-0 text-[#00308b] group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>';
									$btn_style = 'bg-[#0071E3]/10 hover:bg-[#0071E3] text-[#0071E3] hover:text-white border border-[#0071E3]/20 hover:border-transparent rounded-full';
								} elseif ( false !== strpos( $t_lower, 'vừa học vừa làm' ) || false !== strpos( $t_lower, 'vừa làm vừa học' ) || false !== strpos( $t_lower, 'vhvl' ) ) {
									$icon_svg  = '<svg class="w-4 h-4 shrink-0 text-[#00308b] group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
									$btn_style = 'bg-[#FF9500]/10 hover:bg-[#FF9500] text-[#D97706] hover:text-white border border-[#FF9500]/25 hover:border-transparent rounded-full';
								}
							?>
								<a href="<?php echo esc_url( $prog['permalink'] ); ?>" 
								   class="group/btn w-full py-2.5 px-3.5 rounded-full text-xs sm:text-sm font-semibold border transition-all flex items-center justify-between no-underline shadow-none hover:shadow-xs active:scale-[0.99] <?php echo esc_attr( $btn_style ); ?>"
								   title="<?php echo esc_attr( $prog['opportunity_title'] ); ?>">
									<span class="flex items-center gap-2 min-w-0 mr-2">
										<?php echo $icon_svg; ?>
										<span class="truncate"><?php echo esc_html( $label ); ?></span>
									</span>
									<span class="inline-flex items-center gap-1 text-xs opacity-80 group-hover/btn:opacity-100 shrink-0 ml-auto whitespace-nowrap">
										<span>Xem tuyển sinh</span>
										<svg class="w-3.5 h-3.5 transition-transform group-hover/btn:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
											<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
										</svg>
									</span>
								</a>
							<?php else : ?>
								<!-- 2 or more systems/programs: Grid of action tiles -->
								<div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
									<?php foreach ( $major['programs'] as $prog ) : 
										$raw_type  = $prog['type_name'] ?: 'Xem chương trình';
										$label     = ( 0 !== stripos( $raw_type, 'hệ' ) ) ? 'Hệ ' . $raw_type : $raw_type;
										$t_lower   = mb_strtolower( $raw_type, 'UTF-8' );
										$spec_name = ! empty( $prog['specialization'] ) ? $prog['specialization'] : '';

										$icon_svg  = '<svg class="w-4 h-4 shrink-0 text-[#00308b] group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" /></svg>';
										$btn_style = 'bg-slate-100 hover:bg-[#00308b] text-slate-700 hover:text-white border border-slate-200/80 hover:border-transparent rounded-full';

										if ( false !== strpos( $t_lower, 'từ xa' ) ) {
											$icon_svg  = '<svg class="w-4 h-4 shrink-0 text-[#00308b] group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>';
											$btn_style = 'bg-[#0071E3]/10 hover:bg-[#0071E3] text-[#0071E3] hover:text-white border border-[#0071E3]/20 hover:border-transparent rounded-full';
										} elseif ( false !== strpos( $t_lower, 'vừa học vừa làm' ) || false !== strpos( $t_lower, 'vừa làm vừa học' ) || false !== strpos( $t_lower, 'vhvl' ) ) {
											$icon_svg  = '<svg class="w-4 h-4 shrink-0 text-[#00308b] group-hover/btn:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
											$btn_style = 'bg-[#FF9500]/10 hover:bg-[#FF9500] text-[#D97706] hover:text-white border border-[#FF9500]/25 hover:border-transparent rounded-full';
										}

										$prog_real_spec = ! empty( $spec_name )
											&& mb_strtolower( trim( $spec_name ), 'UTF-8' ) !== mb_strtolower( trim( $major['name'] ), 'UTF-8' )
											&& mb_strtolower( trim( $spec_name ), 'UTF-8' ) !== mb_strtolower( trim( preg_replace( '/^ngành\s+/iu', '', $major['name'] ) ), 'UTF-8' );
									?>
										<a href="<?php echo esc_url( $prog['permalink'] ); ?>" 
										   class="group/btn py-2 px-3.5 rounded-full text-xs font-semibold border transition-all flex items-center justify-between no-underline shadow-none hover:shadow-xs active:scale-[0.98] <?php echo esc_attr( $btn_style ); ?>"
										   title="<?php echo esc_attr( $prog['opportunity_title'] ); ?>">
											<span class="flex items-center gap-1.5 min-w-0 mr-1.5">
												<?php echo $icon_svg; ?>
												<?php if ( $prog_real_spec && ( $has_duplicate_he || count( $major['programs'] ) > 1 ) ) : ?>
													<span class="truncate font-extrabold"><?php echo esc_html( $spec_name ); ?></span>
													<span class="text-[10px] font-medium opacity-80 shrink-0 px-1.5 py-0.5 rounded bg-black/5"><?php echo esc_html( $prog['type_name'] ?: 'Từ xa' ); ?></span>
												<?php else : ?>
													<span class="truncate"><?php echo esc_html( $label ); ?></span>
												<?php endif; ?>
											</span>
											<svg class="w-3.5 h-3.5 opacity-60 group-hover/btn:opacity-100 group-hover/btn:translate-x-0.5 transition-all shrink-0 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
												<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
											</svg>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php
			endforeach;
			?>

			<!-- Empty Search Results State -->
			<div id="ltdh-majors-empty-state" class="hidden col-span-full text-center py-12 bg-white border border-slate-200/80 rounded-2xl p-6">
				<div class="w-12 h-12 bg-blue-50 text-[#00308b] rounded-full flex items-center justify-center mx-auto mb-3 text-xl">🔍</div>
				<h3 class="text-base font-bold text-slate-800 mb-1">Không tìm thấy ngành đào tạo phù hợp</h3>
				<p class="text-xs text-slate-500 mb-4">Vui lòng thử tìm với từ khóa khác hoặc xóa bộ lọc.</p>
				<button type="button" id="ltdh-major-reset-search" class="px-5 py-2 bg-[#0071E3] hover:bg-[#0077ED] text-white rounded-full text-xs font-medium tracking-tight shadow-none hover:shadow-xs active:scale-98 transition-all cursor-pointer">
					Xem tất cả ngành
				</button>
			</div>
		</div>

		<!-- Instant Search & Filter Script -->
		<script>
		(function() {
			var searchInput = document.getElementById('ltdh-major-search-input');
			var searchClear = document.getElementById('ltdh-major-search-clear');
			var tabButtons = document.querySelectorAll('.ltdh-he-tab-btn');
			var cards = document.querySelectorAll('.ltdh-major-card');
			var emptyState = document.getElementById('ltdh-majors-empty-state');
			var resetBtn = document.getElementById('ltdh-major-reset-search');
			var badge = document.getElementById('ltdh-majors-total-badge');

			if (!cards.length) return;

			var activeHe = 'all';
			var searchQuery = '';

			function filterCards() {
				var visibleCount = 0;
				var q = searchQuery.trim().toLowerCase();

				cards.forEach(function(card) {
					var name = card.getAttribute('data-major-name') || '';
					var code = card.getAttribute('data-major-code') || '';
					var specs = card.getAttribute('data-major-specs') || '';
					var heList = (card.getAttribute('data-major-he') || '').split(' ');

					var matchesSearch = !q || name.indexOf(q) !== -1 || code.indexOf(q) !== -1 || specs.indexOf(q) !== -1;
					var matchesHe = (activeHe === 'all') || heList.indexOf(activeHe) !== -1;

					if (matchesSearch && matchesHe) {
						card.style.display = '';
						visibleCount++;
					} else {
						card.style.display = 'none';
					}
				});

				if (emptyState) {
					emptyState.classList.toggle('hidden', visibleCount > 0);
				}

				if (badge) {
					badge.textContent = visibleCount + ' ngành tuyển sinh';
				}

				if (searchClear) {
					searchClear.classList.toggle('hidden', !searchQuery);
				}
			}

			if (searchInput) {
				searchInput.addEventListener('input', function() {
					searchQuery = this.value;
					filterCards();
				});
			}

			if (searchClear) {
				searchClear.addEventListener('click', function() {
					if (searchInput) {
						searchInput.value = '';
						searchQuery = '';
						searchInput.focus();
						filterCards();
					}
				});
			}

			tabButtons.forEach(function(btn) {
				btn.addEventListener('click', function() {
					tabButtons.forEach(function(b) {
						b.classList.remove('active', 'bg-[#00308b]', 'text-white', 'shadow-2xs');
						b.classList.add('bg-slate-100', 'text-slate-700');
					});
					this.classList.remove('bg-slate-100', 'text-slate-700');
					this.classList.add('active', 'bg-[#00308b]', 'text-white', 'shadow-2xs');

					activeHe = this.getAttribute('data-he-filter') || 'all';
					filterCards();
				});
			});

			if (resetBtn) {
				resetBtn.addEventListener('click', function() {
					if (searchInput) searchInput.value = '';
					searchQuery = '';
					activeHe = 'all';
					tabButtons.forEach(function(b) {
						var isAll = (b.getAttribute('data-he-filter') === 'all');
						b.classList.toggle('active', isAll);
						b.classList.toggle('bg-[#00308b]', isAll);
						b.classList.toggle('text-white', isAll);
						b.classList.toggle('shadow-2xs', isAll);
						b.classList.toggle('bg-slate-100', !isAll);
						b.classList.toggle('text-slate-700', !isAll);
					});
					filterCards();
				});
			}
		})();
		</script>
	<?php
	else :
		echo '<p class="text-sm text-slate-500 py-4">Hiện tại chưa có chương trình nào được cập nhật cho trường này.</p>';
	endif;
	?>
</section>
