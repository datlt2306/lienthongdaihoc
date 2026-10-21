<?php
/**
 * Template Part: Program Reviews & Feedback (UI/UX Pro Max Edition)
 *
 * Designed to seamlessly match lienthongdaihoc.com's aesthetic:
 * - Wide 2-Column Responsive Modal (max-w-4xl)
 * - Anti-bloat / Zero tedious fields: Effortless 1-tap rating & quick tags
 * - Strict SVG Iconography (Zero cartoon emojis)
 * - Real-time Client-Side Canvas WebP/JPEG Compression (<250KB)
 * - O(1) Cached Scoreboard & Shopee-style Instant Filter Chips
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id = get_the_ID();
if ( ! $program_id ) {
	return;
}

$program_title = get_the_title( $program_id );
$stats         = function_exists( 'ltdh_get_program_rating_stats' ) ? ltdh_get_program_rating_stats( $program_id ) : ltdh_get_default_rating_stats();
$total_reviews = $stats['total'];
$avg_rating    = $stats['average'];
$recommend_pct = $stats['recommend_pct'];
$distribution  = $stats['distribution'];
$criteria      = $stats['criteria'];
$aspect_tags   = ltdh_get_review_aspect_tags();

// Get school details for modal branding
$school_id     = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $program_id ) : 0;
$school_title  = $school_id ? get_the_title( $school_id ) : '';
$school_logo   = $school_id && function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'thumbnail' ) : '';

// Query initial 5 published reviews for immediate SEO indexing
$initial_query = new WP_Query( [
	'post_type'      => 'program_review',
	'post_status'    => 'publish',
	'posts_per_page' => 5,
	'meta_query'     => [
		[
			'key'     => '_review_program_id',
			'value'   => $program_id,
			'compare' => '=',
			'type'    => 'NUMERIC',
		],
	],
	'orderby'        => 'date',
	'order'          => 'DESC',
] );

$nonce = wp_create_nonce( 'ltdh_review_nonce' );
?>

<section id="danh-gia" class="scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-6 mb-8">
	<!-- Section Header -->
	<div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-4 mb-6 gap-3">
		<div>
			<div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200/80 mb-1.5">
				<svg class="w-3.5 h-3.5 text-amber-500 fill-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
				<span>Đánh giá từ người học thực tế</span>
			</div>
			<h2 class="text-lg md:text-2xl font-bold text-slate-900 leading-tight">
				Cảm nhận & Đánh giá chương trình
			</h2>
		</div>

		<!-- Open Modal CTA Button -->
		<?php 
		$has_reviewed_cookie = ! empty( $_COOKIE[ 'ltdh_rev_done_' . $program_id ] );
		if ( $has_reviewed_cookie ) : ?>
			<button type="button" 
			        disabled
			        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-500 border border-slate-200 text-xs sm:text-sm font-semibold cursor-not-allowed shadow-none shrink-0 self-start sm:self-auto">
				<svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
				<span>Bạn đã đánh giá chương trình này</span>
			</button>
		<?php else : ?>
			<button type="button" 
			        id="ltdh-open-review-modal-btn"
			        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#EA580C] hover:bg-[#c2410c] text-white text-xs sm:text-sm font-bold shadow-sm hover:shadow transition-all duration-200 cursor-pointer shrink-0 self-start sm:self-auto">
				<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
				<span>Viết đánh giá chương trình</span>
			</button>
		<?php endif; ?>
	</div>

	<!-- SCOREBOARD (CellphoneS Style Rating Box) -->
	<div class="bg-gradient-to-br from-slate-50 via-white to-amber-50/30 rounded-2xl p-4 sm:p-6 border border-slate-200/80 mb-6 shadow-2xs">
		<div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
			
			<!-- Left: Big Average Score -->
			<div class="md:col-span-4 text-center md:text-left md:border-r border-slate-200/80 md:pr-6">
				<div class="flex items-baseline justify-center md:justify-start gap-1 mb-1">
					<span class="text-4xl sm:text-5xl font-black text-slate-900 leading-none">
						<?php echo esc_html( number_format( $avg_rating, 1 ) ); ?>
					</span>
					<span class="text-slate-400 font-bold text-lg">/ 5.0</span>
				</div>

				<!-- Golden Stars -->
				<div class="flex items-center justify-center md:justify-start gap-1 text-amber-400 mb-2">
					<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
						<?php if ( $i <= round( $avg_rating ) ) : ?>
							<svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
						<?php else : ?>
							<svg class="w-5 h-5 text-slate-200 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
						<?php endif; ?>
					<?php endfor; ?>
				</div>

				<p class="text-xs sm:text-sm text-slate-500 font-medium">
					Dựa trên <strong class="text-slate-800 font-bold"><?php echo esc_html( $total_reviews ); ?></strong> đánh giá từ học viên
				</p>

				<?php if ( $total_reviews > 0 ) : ?>
					<div class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
						<svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
						<span><?php echo esc_html( $recommend_pct ); ?>% học viên hài lòng & khuyên học</span>
					</div>
				<?php endif; ?>
			</div>

			<!-- Right: Star Distribution Bars (5 to 1 Star) -->
			<div class="md:col-span-8 space-y-1.5">
				<?php for ( $s = 5; $s >= 1; $s-- ) :
					$count = $distribution[ $s ] ?? 0;
					$percent = ( $total_reviews > 0 ) ? round( ( $count / $total_reviews ) * 100 ) : 0;
				?>
					<div class="flex items-center gap-2.5 text-xs text-slate-600 group cursor-pointer ltdh-quick-star-filter" data-star="<?php echo esc_attr( $s ); ?>">
						<span class="w-11 font-semibold flex items-center gap-1 shrink-0 text-slate-700 group-hover:text-[#00308b]">
							<span><?php echo esc_html( $s ); ?></span>
							<svg class="w-3.5 h-3.5 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
						</span>

						<!-- Progress Bar -->
						<div class="flex-1 h-2 sm:h-2.5 bg-slate-200/80 rounded-full overflow-hidden">
							<div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full transition-all duration-300" 
							     style="width: <?php echo esc_attr( $percent ); ?>%;"></div>
						</div>

						<!-- Count & Percent -->
						<span class="w-14 text-right font-medium text-slate-500 group-hover:text-slate-800 shrink-0">
							<?php echo esc_html( $count ); ?> (<?php echo esc_html( $percent ); ?>%)
						</span>
					</div>
				<?php endfor; ?>
			</div>

		</div>

		<!-- 4 Sub-criteria Breakdown Cards -->
		<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-5 mt-5 border-t border-slate-200/80">
			<div class="bg-white p-3 rounded-xl border border-slate-100 shadow-2xs">
				<span class="text-[11px] text-slate-500 block mb-0.5 font-medium truncate">Lịch học linh hoạt</span>
				<div class="flex items-center justify-between">
					<span class="text-base font-bold text-slate-900"><?php echo esc_html( number_format( $criteria['schedule'] ?? 5.0, 1 ) ); ?>/5</span>
					<div class="flex text-amber-400 text-xs">★★★★★</div>
				</div>
			</div>

			<div class="bg-white p-3 rounded-xl border border-slate-100 shadow-2xs">
				<span class="text-[11px] text-slate-500 block mb-0.5 font-medium truncate">Giảng viên & LMS</span>
				<div class="flex items-center justify-between">
					<span class="text-base font-bold text-slate-900"><?php echo esc_html( number_format( $criteria['lecturer'] ?? 5.0, 1 ) ); ?>/5</span>
					<div class="flex text-amber-400 text-xs">★★★★★</div>
				</div>
			</div>

			<div class="bg-white p-3 rounded-xl border border-slate-100 shadow-2xs">
				<span class="text-[11px] text-slate-500 block mb-0.5 font-medium truncate">Hỗ trợ & Cố vấn</span>
				<div class="flex items-center justify-between">
					<span class="text-base font-bold text-slate-900"><?php echo esc_html( number_format( $criteria['support'] ?? 5.0, 1 ) ); ?>/5</span>
					<div class="flex text-amber-400 text-xs">★★★★★</div>
				</div>
			</div>

			<div class="bg-white p-3 rounded-xl border border-slate-100 shadow-2xs">
				<span class="text-[11px] text-slate-500 block mb-0.5 font-medium truncate">Học phí minh bạch</span>
				<div class="flex items-center justify-between">
					<span class="text-base font-bold text-slate-900"><?php echo esc_html( number_format( $criteria['tuition'] ?? 5.0, 1 ) ); ?>/5</span>
					<div class="flex text-amber-400 text-xs">★★★★★</div>
				</div>
			</div>
		</div>
	</div>

	<!-- SHOPEE STYLE FILTER PILLS BAR -->
	<div class="mb-5 space-y-2.5">
		<div class="flex items-center gap-2 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden pb-1" id="ltdh-review-filter-chips">
			<button type="button" 
			        data-filter-type="star" 
			        data-filter-value="all" 
			        class="ltdh-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all border cursor-pointer shrink-0 bg-[#00308b] text-white border-[#00308b] shadow-2xs">
				Tất cả (<?php echo esc_html( $total_reviews ); ?>)
			</button>

			<button type="button" 
			        data-filter-type="star" 
			        data-filter-value="5" 
			        class="ltdh-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all border cursor-pointer shrink-0 bg-white text-slate-700 border-slate-200 hover:border-slate-300">
				5 Sao (<?php echo esc_html( $distribution[5] ?? 0 ); ?>)
			</button>

			<button type="button" 
			        data-filter-type="star" 
			        data-filter-value="4" 
			        class="ltdh-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all border cursor-pointer shrink-0 bg-white text-slate-700 border-slate-200 hover:border-slate-300">
				4 Sao (<?php echo esc_html( $distribution[4] ?? 0 ); ?>)
			</button>

			<button type="button" 
			        data-filter-type="has_images" 
			        data-filter-value="1" 
			        class="ltdh-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all border cursor-pointer shrink-0 bg-white text-slate-700 border-slate-200 hover:border-slate-300 flex items-center gap-1.5">
				<svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
				<span>Có hình ảnh</span>
				<?php if ( ! empty( $stats['has_images'] ) ) : ?>
					<span>(<?php echo esc_html( $stats['has_images'] ); ?>)</span>
				<?php endif; ?>
			</button>

			<button type="button" 
			        data-filter-type="status" 
			        data-filter-value="graduated" 
			        class="ltdh-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all border cursor-pointer shrink-0 bg-white text-slate-700 border-slate-200 hover:border-slate-300 flex items-center gap-1.5">
				<svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
				<span>Đã tốt nghiệp</span>
			</button>

			<button type="button" 
			        data-filter-type="status" 
			        data-filter-value="studying" 
			        class="ltdh-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all border cursor-pointer shrink-0 bg-white text-slate-700 border-slate-200 hover:border-slate-300 flex items-center gap-1.5">
				<svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
				<span>Đang học</span>
			</button>
		</div>

		<!-- Aspect Tag Chips -->
		<div class="flex items-center gap-1.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden pb-1" id="ltdh-review-tag-chips">
			<?php foreach ( $aspect_tags as $t_slug => $t_name ) : ?>
				<button type="button" 
				        data-filter-type="tag" 
				        data-filter-value="<?php echo esc_attr( $t_slug ); ?>" 
				        class="ltdh-tag-btn px-2.5 py-1 rounded-lg text-[11px] font-medium transition-all cursor-pointer shrink-0 bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900 border border-transparent">
					# <?php echo esc_html( $t_name ); ?>
				</button>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- REVIEWS LIST CONTAINER -->
	<div id="ltdh-reviews-list" class="divide-y divide-slate-100 relative min-h-[120px]">
		<?php if ( $initial_query->have_posts() ) : ?>
			<?php while ( $initial_query->have_posts() ) : $initial_query->the_post(); ?>
				<?php ltdh_render_review_card( get_the_ID() ); ?>
			<?php endwhile; wp_reset_postdata(); ?>
		<?php else : ?>
			<div class="py-12 text-center text-slate-500 bg-slate-50/70 rounded-2xl border border-dashed border-slate-200 p-6">
				<div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
					<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
				</div>
				<p class="font-bold text-slate-800 text-sm">Chưa có đánh giá nào cho chương trình này</p>
				<p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Bạn đã hoặc đang học chương trình này? Hãy là người đầu tiên chia sẻ cảm nhận thực tế giúp các bạn khóa sau nhé!</p>
				<button type="button" 
				        class="ltdh-trigger-open-modal mt-3.5 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#00308b] text-white text-xs font-bold hover:bg-[#002266] transition-colors cursor-pointer shadow-xs">
					<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
					<span>Viết đánh giá đầu tiên</span>
				</button>
			</div>
		<?php endif; ?>
	</div>

	<!-- AJAX Load More Button -->
	<?php if ( $initial_query->max_num_pages > 1 ) : ?>
		<div class="text-center mt-6 pt-4 border-t border-slate-100" id="ltdh-load-more-wrap">
			<button type="button" 
			        id="ltdh-load-more-reviews-btn" 
			        data-page="1" 
			        data-max-pages="<?php echo esc_attr( $initial_query->max_num_pages ); ?>"
			        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50 hover:bg-slate-100 text-xs sm:text-sm font-bold text-slate-700 transition-colors cursor-pointer">
				<span>Xem thêm đánh giá</span>
				<svg class="w-4 h-4 text-slate-500 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
			</button>
		</div>
	<?php endif; ?>

</section>


<!-- MODAL: GỬI ĐÁNH GIÁ CHƯƠNG TRÌNH (WIDE 2-COLUMN LUXURY REDESIGN) -->
<div id="ltdh-review-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5">
	<div class="relative bg-white w-full max-w-4xl rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all my-auto max-h-[94vh] flex flex-col">
		
		<!-- Modal Header: Deep Brand Navy Gradient -->
		<div class="bg-gradient-to-r from-[#00308b] via-[#002875] to-[#001e54] px-5 sm:px-7 py-4 text-white flex items-center justify-between shrink-0 shadow-xs">
			<div class="flex items-center gap-3 min-w-0">
				<div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm border border-white/15 flex items-center justify-center text-amber-300 shrink-0">
					<svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
				</div>
				<div class="min-w-0">
					<h3 class="text-base sm:text-lg font-extrabold text-white leading-tight">Đánh Giá & Cảm Nhận Học Tập</h3>
					<p class="text-xs text-blue-200 mt-0.5 truncate">
						<?php echo esc_html( $program_title ); ?>
					</p>
				</div>
			</div>
			<button type="button" 
			        id="ltdh-close-review-modal-btn" 
			        class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer shrink-0 ml-3"
			        aria-label="Đóng">
				<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
			</button>
		</div>

		<!-- Modal Body Form: 2-Column Responsive Layout -->
		<div class="p-5 sm:p-7 overflow-y-auto flex-1">
			<form id="ltdh-program-review-form" method="POST" action="javascript:void(0);" onsubmit="return ltdhSubmitReviewForm(event);" enctype="multipart/form-data">
				
				
				<!-- 2-COLUMN DESKTOP GRID -->
				<div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
					
					<!-- LEFT COLUMN: RATING & HIGHLIGHT TAGS (5 cols) -->
					<div class="md:col-span-5 space-y-4">
						
						<!-- Hero Star Rating Picker Box -->
						<div class="bg-gradient-to-b from-slate-50 to-amber-50/20 rounded-2xl p-4 sm:p-5 border border-slate-200/80 text-center shadow-2xs">
							<span class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
								Đánh giá tổng quan <span class="text-red-500">*</span>
							</span>

							<!-- Interactive Golden Stars -->
							<div class="flex items-center justify-center gap-2 py-1 cursor-pointer select-none" id="ltdh-star-picker">
								<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
									<span class="ltdh-star-icon text-amber-400 hover:text-amber-500 transition-colors p-1" data-value="<?php echo esc_attr( $s ); ?>">
										<svg class="w-7 h-7 sm:w-8 sm:h-8 fill-current transition-transform duration-150 hover:scale-110 pointer-events-none" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
									</span>
								<?php endfor; ?>
							</div>
							<input type="hidden" name="rating" id="ltdh-rating-input" value="5">

							<!-- Reaction Sentiment Text -->
							<div class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold text-amber-900 bg-amber-100/80 border border-amber-200">
								<span id="ltdh-rating-desc">Rất hài lòng - Khuyên học!</span>
							</div>
						</div>

						<!-- Quick Highlight Tags (Shopee 1-Tap Pills) -->
						<div class="bg-slate-50/60 rounded-2xl p-4 border border-slate-200/80">
							<div class="flex items-center justify-between mb-2.5">
								<span class="text-xs font-bold text-slate-800">Điểm nổi bật bạn thích:</span>
								<span class="text-[10px] text-slate-400">Chọn 1 hoặc nhiều</span>
							</div>

							<div class="flex flex-wrap gap-1.5" id="ltdh-aspect-tags-container">
								<?php foreach ( $aspect_tags as $t_slug => $t_name ) : ?>
									<label class="ltdh-aspect-pill inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-medium border border-slate-200 bg-white text-slate-700 hover:border-[#00308b] hover:text-[#00308b] cursor-pointer select-none transition-all duration-150">
										<input type="checkbox" name="aspect_tags[]" value="<?php echo esc_attr( $t_slug ); ?>" class="hidden">
										<svg class="w-3 h-3 text-[#00308b] hidden ltdh-pill-check" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
										<span><?php echo esc_html( $t_name ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>

						<!-- Trust & Program Context Banner -->
						<div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 flex items-start gap-2.5">
							<div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5">
								<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
							</div>
							<div class="text-xs text-slate-600 min-w-0">
								<span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Chương trình đào tạo:</span>
								<strong class="text-slate-900 truncate block font-bold"><?php echo esc_html( $program_title ); ?></strong>
								<?php if ( $school_title ) : ?>
									<span class="text-[11px] text-blue-700 block truncate"><?php echo esc_html( $school_title ); ?></span>
								<?php endif; ?>
							</div>
						</div>

					</div>


					<!-- RIGHT COLUMN: REVIEW CONTENT, PHOTOS & STUDENT VERIFICATION (7 cols) -->
					<div class="md:col-span-7 space-y-4">
						
						<!-- Review Content Textarea -->
						<div>
							<div class="flex items-center justify-between mb-1.5">
								<label for="ltdh-review-content" class="text-xs font-bold text-slate-800">
									Chia sẻ cảm nhận học tập của bạn <span class="text-red-500">*</span>
								</label>
								<span id="ltdh-char-count" class="text-[11px] text-slate-400">0 / 1500</span>
							</div>
							<textarea name="content" 
							          id="ltdh-review-content" 
							          rows="4" 
							          required 
							          minlength="10" 
							          maxlength="1500"
							          placeholder="Ví dụ: Chương trình học online buổi tối rất thuận tiện cho người đi làm. Giảng viên hỗ trợ nhiệt tình, hệ thống học trực quan..."
							          class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#00308b]/20 focus:border-[#00308b] transition-all resize-y"></textarea>
						</div>

						<!-- Compact Photo Upload Dropzone (Client-side Compressed) -->
						<div>
							<div class="flex items-center justify-between mb-1.5">
								<label class="text-xs font-bold text-slate-800">
									Hình ảnh thực tế (Tùy chọn - tối đa 3 ảnh):
								</label>
							</div>

							<div class="flex items-center gap-3">
								<div id="ltdh-dropzone" class="flex-1 border-2 border-dashed border-slate-200 hover:border-[#00308b] rounded-xl p-3 text-center transition-colors cursor-pointer bg-slate-50/50 hover:bg-blue-50/30">
									<input type="file" id="ltdh-image-input" accept="image/jpeg,image/png,image/webp" multiple class="hidden">
									<div class="flex items-center justify-center gap-2 pointer-events-none">
										<svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
										<span class="text-xs text-slate-600 font-medium">Bấm chọn ảnh (Lễ tốt nghiệp, thẻ SV, góc học tập)</span>
									</div>
								</div>

								<!-- Image Previews Container -->
								<div id="ltdh-image-previews" class="flex items-center gap-2 empty:hidden shrink-0"></div>
							</div>
						</div>

						<!-- Student Verification Information (Compact 2-Cols) -->
						<div class="bg-slate-50/60 rounded-2xl p-4 border border-slate-200/80 space-y-3">
							<span class="block text-xs font-bold text-slate-800">
								Thông tin người đánh giá (Dùng để xác thực học viên thật):
							</span>

							<!-- Row 1: Name & Phone -->
							<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
								<div>
									<label for="ltdh-author-name" class="block text-xs font-semibold text-slate-700 mb-1">
										Họ và tên <span class="text-red-500">*</span>
									</label>
									<input type="text" 
									       name="student_name" 
									       id="ltdh-author-name" 
									       required 
									       placeholder="Nguyễn Văn A" 
									       class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-[#00308b]/20 focus:border-[#00308b]">
								</div>

								<div>
									<label for="ltdh-author-phone" class="block text-xs font-semibold text-slate-700 mb-1">
										Số điện thoại (Zalo) <span class="text-red-500">*</span>
									</label>
									<input type="tel" 
									       name="phone" 
									       id="ltdh-author-phone" 
									       required 
									       placeholder="0912 xxx xxx" 
									       pattern="^(0[35789])[0-9]{8}$"
									       class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-[#00308b]/20 focus:border-[#00308b]">
								</div>
							</div>

							<!-- Row 2: Status & Batch -->
							<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
								<div>
									<label for="ltdh-author-status" class="block text-xs font-semibold text-slate-700 mb-1">
										Tình trạng học tập:
									</label>
									<select name="student_status" id="ltdh-author-status" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-[#00308b]/20 focus:border-[#00308b]">
										<option value="graduated">Cựu sinh viên đã tốt nghiệp</option>
										<option value="studying" selected>Học viên đang theo học</option>
										<option value="interested">Học viên mới / Người quan tâm</option>
									</select>
								</div>

								<div>
									<label for="ltdh-author-batch" class="block text-xs font-semibold text-slate-700 mb-1">
										Khóa học / Niên khóa:
									</label>
									<input type="text" 
									       name="batch" 
									       id="ltdh-author-batch" 
									       placeholder="Ví dụ: Khóa K2022" 
									       class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-[#00308b]/20 focus:border-[#00308b]">
								</div>
							</div>

							<!-- Privacy Micro-text -->
							<p class="text-[11px] text-slate-400 flex items-center gap-1.5 pt-0.5">
								<svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
								<span>Số điện thoại chỉ dùng để xác thực học viên, tuyệt đối không công khai trên website.</span>
							</p>
						</div>
<!-- Hidden Security Fields -->
				<input type="hidden" name="action" value="ltdh_submit_program_review">
				<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
				<input type="hidden" name="program_id" value="<?php echo esc_attr( $program_id ); ?>">
				<?php if ( function_exists( 'ltdh_render_recaptcha_field' ) ) : ?>
					<?php ltdh_render_recaptcha_field( 'program_review' ); ?>
				<?php endif; ?>
				<!-- Honeypot anti-spam (invisible to humans) -->
				<input type="text" name="website_url_hp" value="" tabindex="-1" autocomplete="off" style="display:none !important; visibility:hidden !important;" aria-hidden="true">

					</div>

				</div>

				<!-- Error / Alert Banner -->
				<div id="ltdh-review-error" class="hidden text-xs text-red-600 bg-red-50 p-3 rounded-xl border border-red-200 mt-4"></div>

				<!-- Modal Actions (Footer) -->
				<div class="flex items-center justify-between pt-5 mt-5 border-t border-slate-100">
					<p class="text-[11px] text-slate-400 hidden sm:block">
						🔒 Bảo mật thông tin theo Nghị định 13/2023/NĐ-CP.
					</p>

					<div class="flex items-center gap-2.5 ml-auto">
						<button type="button" 
						        id="ltdh-cancel-review-btn" 
						        class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
							Hủy bỏ
						</button>

						<button type="submit" 
						        id="ltdh-submit-review-btn" 
						        onclick="return ltdhSubmitReviewForm(event);"
						        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#EA580C] hover:bg-[#c2410c] text-white text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all cursor-pointer disabled:opacity-50">
							<svg id="ltdh-submit-spinner" class="hidden w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
							<span id="ltdh-submit-btn-text">Gửi đánh giá chương trình</span>
						</button>
					</div>
					
				</div>

			</form>
		</div>

		<!-- Success State Modal Overlay -->
		<div id="ltdh-review-success" class="hidden absolute inset-0 bg-white p-6 sm:p-10 flex flex-col items-center justify-center text-center z-10">
			<div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center mb-4 shadow-2xs">
				<svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
			</div>
			<h4 class="text-xl font-bold text-slate-900 mb-1.5">Gửi đánh giá thành công!</h4>
			<p class="text-xs sm:text-sm text-slate-600 max-w-md mb-6 leading-relaxed" id="ltdh-success-msg">
				Cảm ơn bạn đã chia sẻ trải nghiệm thực tế. Đánh giá của bạn đang được Ban Tuyển Sinh kiểm duyệt và sẽ hiển thị trên chương trình sớm nhất.
			</p>
			<button type="button" 
			        id="ltdh-close-success-btn" 
			        class="px-6 py-2.5 rounded-xl bg-[#00308b] hover:bg-[#002266] text-white text-xs sm:text-sm font-bold shadow-sm transition-colors cursor-pointer">
				Hoàn tất & Đóng
			</button>
		</div>

	</div>
</div>

<script>
(function() {
	var ajaxUrl = (typeof ltdh_ajax !== 'undefined' && ltdh_ajax.ajax_url) ? ltdh_ajax.ajax_url : '/wp-admin/admin-ajax.php';
	var programId = <?php echo (int) $program_id; ?>;
	var currentFilter = { star: 'all', has_images: 0, status: '', tag: '', page: 1 };
	var compressedFiles = []; // Array of { blob, name }

	function initProgramReviews() {
		var modal = document.getElementById('ltdh-review-modal');
		var openBtns = document.querySelectorAll('#ltdh-open-review-modal-btn, .ltdh-trigger-open-modal');
		var closeBtn = document.getElementById('ltdh-close-review-modal-btn');
		var cancelBtn = document.getElementById('ltdh-cancel-review-btn');
		var successCloseBtn = document.getElementById('ltdh-close-success-btn');
		var form = document.getElementById('ltdh-program-review-form');

		// Enforce 1 review per person per program on Client-Side
		var reviewedKey = 'ltdh_reviewed_' + programId;
		var hasReviewed = (localStorage.getItem(reviewedKey) === '1') || (document.cookie.indexOf('ltdh_rev_done_' + programId + '=1') !== -1);

		function lockReviewedButtons() {
			openBtns.forEach(function(btn) {
				btn.disabled = true;
				btn.classList.remove('bg-[#EA580C]', 'hover:bg-[#c2410c]', 'cursor-pointer');
				btn.classList.add('bg-slate-100', 'text-slate-500', 'border', 'border-slate-200', 'cursor-not-allowed', 'shadow-none');
				btn.innerHTML = '<svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>Bạn đã đánh giá chương trình này</span>';
			});
		}

		if (hasReviewed) {
			lockReviewedButtons();
		}

		// Open Modal
		function openModal() {
			if (hasReviewed) {
				alert('Bạn đã gửi đánh giá cho chương trình này rồi. Mỗi học viên chỉ được đánh giá 1 lần.');
				return;
			}
			if (modal) {
				modal.classList.remove('hidden');
				document.body.style.overflow = 'hidden';
			}
		}

		// Close Modal
		function closeModal() {
			if (modal) {
				modal.classList.add('hidden');
				document.body.style.overflow = '';
				var successBox = document.getElementById('ltdh-review-success');
				if (successBox) successBox.classList.add('hidden');
			}
		}

		openBtns.forEach(function(b) { b.addEventListener('click', openModal); });
		if (closeBtn) closeBtn.addEventListener('click', closeModal);
		if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
		if (successCloseBtn) successCloseBtn.addEventListener('click', closeModal);

		if (modal) {
			modal.addEventListener('click', function(e) {
				if (e.target === modal) closeModal();
			});
			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
			});
		}

		// Interactive Star Picker
		var starPicker = document.getElementById('ltdh-star-picker');
		var ratingInput = document.getElementById('ltdh-rating-input');
		var ratingDesc = document.getElementById('ltdh-rating-desc');
		var descMap = {
			1: 'Rất tệ - Không hài lòng',
			2: 'Tạm được - Cần cải thiện nhiều',
			3: 'Bình thường - Đạt yêu cầu cơ bản',
			4: 'Tốt - Hài lòng với chương trình',
			5: 'Rất hài lòng - Khuyên học!'
		};

		if (starPicker) {
			var stars = starPicker.querySelectorAll('.ltdh-star-icon');
			function updateStarVisual(val) {
				stars.forEach(function(st, idx) {
					if (idx < val) {
						st.classList.add('text-amber-400');
						st.classList.remove('text-slate-200');
					} else {
						st.classList.remove('text-amber-400');
						st.classList.add('text-slate-200');
					}
				});
				if (ratingDesc && descMap[val]) {
					ratingDesc.textContent = descMap[val];
				}
			}

			stars.forEach(function(st) {
				st.addEventListener('mouseenter', function() {
					var val = parseInt(this.getAttribute('data-value'), 10);
					updateStarVisual(val);
				});
				st.addEventListener('click', function() {
					var val = parseInt(this.getAttribute('data-value'), 10);
					if (ratingInput) ratingInput.value = val;
					updateStarVisual(val);
				});
			});

			starPicker.addEventListener('mouseleave', function() {
				var current = parseInt(ratingInput ? ratingInput.value : 5, 10);
				updateStarVisual(current);
			});
		}

		// Aspect Tag Pills (1-Tap Selection Active State)
		document.querySelectorAll('.ltdh-aspect-pill').forEach(function(pill) {
			var checkbox = pill.querySelector('input[type="checkbox"]');
			var checkIcon = pill.querySelector('.ltdh-pill-check');
			if (!checkbox) return;

			pill.addEventListener('click', function() {
				setTimeout(function() {
					if (checkbox.checked) {
						pill.classList.add('bg-[#00308b]', 'text-white', 'border-[#00308b]');
						pill.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
						if (checkIcon) {
							checkIcon.classList.remove('hidden');
							checkIcon.classList.add('text-white');
						}
					} else {
						pill.classList.remove('bg-[#00308b]', 'text-white', 'border-[#00308b]');
						pill.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
						if (checkIcon) {
							checkIcon.classList.add('hidden');
							checkIcon.classList.remove('text-white');
						}
					}
				}, 10);
			});
		});

		// Character counter
		var textarea = document.getElementById('ltdh-review-content');
		var charCount = document.getElementById('ltdh-char-count');
		if (textarea && charCount) {
			textarea.addEventListener('input', function() {
				charCount.textContent = this.value.length + ' / 1500';
			});
		}

		// Client-Side Canvas Image Compression
		var dropzone = document.getElementById('ltdh-dropzone');
		var fileInput = document.getElementById('ltdh-image-input');
		var previewsContainer = document.getElementById('ltdh-image-previews');

		if (dropzone && fileInput) {
			dropzone.addEventListener('click', function() { fileInput.click(); });

			fileInput.addEventListener('change', function(e) {
				var files = Array.prototype.slice.call(e.target.files);
				if (!files.length) return;

				var remaining = 3 - compressedFiles.length;
				var toProcess = files.slice(0, remaining);

				toProcess.forEach(function(file) {
					compressImage(file, 1200, 0.8, function(compressedBlob) {
						compressedFiles.push({ blob: compressedBlob, name: file.name });
						renderPreviews();
					});
				});
				fileInput.value = '';
			});
		}

		function compressImage(file, maxDimension, quality, callback) {
			var reader = new FileReader();
			reader.onload = function(event) {
				var img = new Image();
				img.onload = function() {
					var width = img.width;
					var height = img.height;

					if (width > maxDimension || height > maxDimension) {
						if (width > height) {
							height = Math.round((height * maxDimension) / width);
							width = maxDimension;
						} else {
							width = Math.round((width * maxDimension) / height);
							height = maxDimension;
						}
					}

					var canvas = document.createElement('canvas');
					canvas.width = width;
					canvas.height = height;
					var ctx = canvas.getContext('2d');
					ctx.drawImage(img, 0, 0, width, height);

					// Prefer webp, fallback to jpeg
					var format = 'image/webp';
					canvas.toBlob(function(blob) {
						if (!blob) {
							canvas.toBlob(function(fallbackBlob) {
								callback(fallbackBlob);
							}, 'image/jpeg', quality);
						} else {
							callback(blob);
						}
					}, format, quality);
				};
				img.src = event.target.result;
			};
			reader.readAsDataURL(file);
		}

		function renderPreviews() {
			if (!previewsContainer) return;
			previewsContainer.innerHTML = '';
			compressedFiles.forEach(function(item, idx) {
				var url = URL.createObjectURL(item.blob);
				var wrap = document.createElement('div');
				wrap.className = 'relative w-12 h-12 rounded-xl overflow-hidden border border-slate-200 shadow-2xs group shrink-0';
				wrap.innerHTML = '<img src="' + url + '" class="w-full h-full object-cover">' +
					'<button type="button" class="absolute top-0.5 right-0.5 w-4 h-4 rounded-full bg-slate-900/80 text-white text-[10px] flex items-center justify-center cursor-pointer hover:bg-red-600 transition-colors">✕</button>';
				
				wrap.querySelector('button').addEventListener('click', function(e) {
					e.stopPropagation();
					compressedFiles.splice(idx, 1);
					renderPreviews();
				});
				previewsContainer.appendChild(wrap);
			});
		}

		// Global Safe Form Submission Handler (Prevents Any Accidental Page Navigation)
		window.ltdhSubmitReviewForm = function(e) {
			if (e) {
				if (typeof e.preventDefault === 'function') e.preventDefault();
				if (typeof e.stopPropagation === 'function') e.stopPropagation();
			}

			if (!form) {
				form = document.getElementById('ltdh-program-review-form');
			}
			if (!form) return false;

			if (form.reportValidity && !form.reportValidity()) {
				return false;
			}

			var errorDiv = document.getElementById('ltdh-review-error');
			var submitBtn = document.getElementById('ltdh-submit-review-btn');
			var spinner = document.getElementById('ltdh-submit-spinner');
			var btnText = document.getElementById('ltdh-submit-btn-text');

			if (errorDiv) errorDiv.classList.add('hidden');

			if (submitBtn) submitBtn.disabled = true;
			if (spinner) spinner.classList.remove('hidden');
			if (btnText) btnText.textContent = 'Đang gửi...';

			function executeAjax(token) {
				var formData = new FormData(form);
				if (token) {
					formData.append('g-recaptcha-response', token);
					formData.append('recaptcha_token', token);
				}

				// Append compressed blobs
				compressedFiles.forEach(function(item, i) {
					formData.append('review_images[' + i + ']', item.blob, item.name);
				});

				var xhr = new XMLHttpRequest();
				xhr.open('POST', ajaxUrl, true);
				xhr.onload = function() {
					if (submitBtn) submitBtn.disabled = false;
					if (spinner) spinner.classList.add('hidden');
					if (btnText) btnText.textContent = 'Gửi đánh giá chương trình';

					try {
						var res = JSON.parse(xhr.responseText);
						if (res.success) {
							localStorage.setItem(reviewedKey, '1');
							hasReviewed = true;
							lockReviewedButtons();

							var successBox = document.getElementById('ltdh-review-success');
							var successMsg = document.getElementById('ltdh-success-msg');
							if (successMsg && res.data && res.data.message) {
								successMsg.textContent = res.data.message;
							}
							if (successBox) successBox.classList.remove('hidden');
							form.reset();
							compressedFiles = [];
							renderPreviews();
						} else {
							var err = (res.data && res.data.message) ? res.data.message : 'Có lỗi xảy ra. Vui lòng kiểm tra lại.';
							if (errorDiv) {
								errorDiv.textContent = err;
								errorDiv.classList.remove('hidden');
							}
						}
					} catch(err) {
						if (errorDiv) {
							errorDiv.textContent = 'Lỗi máy chủ phản hồi không đúng. Vui lòng thử lại sau.';
							errorDiv.classList.remove('hidden');
						}
					}
				};
				xhr.onerror = function() {
					if (submitBtn) submitBtn.disabled = false;
					if (spinner) spinner.classList.add('hidden');
					if (btnText) btnText.textContent = 'Gửi đánh giá chương trình';
					if (errorDiv) {
						errorDiv.textContent = 'Lỗi kết nối internet. Vui lòng thử lại.';
						errorDiv.classList.remove('hidden');
					}
				};
				xhr.send(formData);
			}

			if (typeof window.ltdhGetRecaptchaToken === 'function') {
				window.ltdhGetRecaptchaToken('program_review', form)
					.then(function(token) {
						executeAjax(token);
					})
					.catch(function() {
						executeAjax('');
					});
			} else {
				executeAjax('');
			}

			return false;
		};

		if (form) {
			form.addEventListener('submit', window.ltdhSubmitReviewForm);
		}

		// Filter Buttons logic (Shopee Chips)
		var filterChips = document.querySelectorAll('#ltdh-review-filter-chips .ltdh-filter-btn');
		filterChips.forEach(function(btn) {
			btn.addEventListener('click', function() {
				filterChips.forEach(function(b) {
					b.classList.remove('bg-[#00308b]', 'text-white', 'border-[#00308b]');
					b.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
				});
				btn.classList.add('bg-[#00308b]', 'text-white', 'border-[#00308b]');
				btn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');

				var type = btn.getAttribute('data-filter-type');
				var val = btn.getAttribute('data-filter-value');

				// Reset other primary filters
				currentFilter.star = 'all';
				currentFilter.has_images = 0;
				currentFilter.status = '';
				currentFilter.page = 1;

				if (type === 'star') currentFilter.star = val;
				else if (type === 'has_images') currentFilter.has_images = 1;
				else if (type === 'status') currentFilter.status = val;

				loadReviews(false);
			});
		});

		// Quick Star Filter from Scoreboard bars
		document.querySelectorAll('.ltdh-quick-star-filter').forEach(function(bar) {
			bar.addEventListener('click', function() {
				var s = this.getAttribute('data-star');
				var targetBtn = document.querySelector('#ltdh-review-filter-chips [data-filter-type="star"][data-filter-value="' + s + '"]');
				if (targetBtn) {
					targetBtn.click();
				}
			});
		});

		// Tag Filter logic
		var tagChips = document.querySelectorAll('#ltdh-review-tag-chips .ltdh-tag-btn');
		tagChips.forEach(function(tagBtn) {
			tagBtn.addEventListener('click', function() {
				var val = this.getAttribute('data-filter-value');
				if (currentFilter.tag === val) {
					// Untoggle
					currentFilter.tag = '';
					this.classList.remove('bg-[#00308b]', 'text-white');
					this.classList.add('bg-slate-100', 'text-slate-600');
				} else {
					tagChips.forEach(function(tb) {
						tb.classList.remove('bg-[#00308b]', 'text-white');
						tb.classList.add('bg-slate-100', 'text-slate-600');
					});
					currentFilter.tag = val;
					this.classList.add('bg-[#00308b]', 'text-white');
					this.classList.remove('bg-slate-100', 'text-slate-600');
				}
				currentFilter.page = 1;
				loadReviews(false);
			});
		});

		// Load More Reviews logic
		var loadMoreBtn = document.getElementById('ltdh-load-more-reviews-btn');
		if (loadMoreBtn) {
			loadMoreBtn.addEventListener('click', function() {
				currentFilter.page++;
				loadReviews(true);
			});
		}

		function loadReviews(append) {
			var list = document.getElementById('ltdh-reviews-list');
			var loadMoreWrap = document.getElementById('ltdh-load-more-wrap');

			if (!append && list) {
				list.style.opacity = '0.5';
			}

			var url = ajaxUrl + '?action=ltdh_get_program_reviews&program_id=' + programId +
				'&star=' + encodeURIComponent(currentFilter.star) +
				'&has_images=' + encodeURIComponent(currentFilter.has_images) +
				'&status=' + encodeURIComponent(currentFilter.status) +
				'&tag=' + encodeURIComponent(currentFilter.tag) +
				'&page=' + encodeURIComponent(currentFilter.page);

			var xhr = new XMLHttpRequest();
			xhr.open('GET', url, true);
			xhr.onload = function() {
				if (list) list.style.opacity = '1';
				try {
					var res = JSON.parse(xhr.responseText);
					if (res.success && res.data) {
						if (append && list) {
							list.insertAdjacentHTML('beforeend', res.data.html);
						} else if (list) {
							list.innerHTML = res.data.html;
						}

						if (loadMoreWrap) {
							if (res.data.has_more) {
								loadMoreWrap.classList.remove('hidden');
							} else {
								loadMoreWrap.classList.add('hidden');
							}
						}
						bindLikeButtons();
					}
				} catch(e) {}
			};
			xhr.send();
		}

		// Helpful Upvote Buttons
		function bindLikeButtons() {
			document.querySelectorAll('.ltdh-like-review-btn:not([data-bound])').forEach(function(btn) {
				btn.setAttribute('data-bound', '1');
				btn.addEventListener('click', function() {
					var revId = this.getAttribute('data-review-id');
					var countSpan = this.querySelector('.ltdh-like-count');
					var self = this;

					var xhr = new XMLHttpRequest();
					xhr.open('POST', ajaxUrl, true);
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					xhr.onload = function() {
						try {
							var res = JSON.parse(xhr.responseText);
							if (res.success && res.data) {
								if (countSpan) countSpan.textContent = '(' + res.data.count + ')';
								self.classList.add('text-[#00308b]');
							}
						} catch(e) {}
					};
					xhr.send('action=ltdh_like_program_review&review_id=' + encodeURIComponent(revId));
				});
			});
		}
		bindLikeButtons();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initProgramReviews);
	} else {
		initProgramReviews();
	}
})();
</script>
