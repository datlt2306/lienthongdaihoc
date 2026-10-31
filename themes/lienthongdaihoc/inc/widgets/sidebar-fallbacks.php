<?php
/**
 * Default Sidebar Fallback Renderers.
 *
 * Renders fallback markup when registered widget sidebars have no active widgets.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render default blog sidebar when no widgets are active in 'sidebar-blog'.
 */
function ltdh_render_default_blog_sidebar() {
	$blog_cats = get_categories( [ 'hide_empty' => true, 'exclude' => [ 1 ] ] );
	$recent_posts = get_posts( [
		'numberposts' => 5,
		'post__not_in' => is_single() ? [ get_the_ID() ] : [],
	] );

	$active_cat_slug = '';
	if ( is_category() ) {
		$cat_obj = get_queried_object();
		if ( $cat_obj && ! empty( $cat_obj->slug ) ) {
			$active_cat_slug = $cat_obj->slug;
		}
	} elseif ( isset( $_GET['danh-muc'] ) ) {
		$active_cat_slug = sanitize_text_field( wp_unslash( $_GET['danh-muc'] ) );
	} elseif ( is_single() ) {
		$post_cats = get_the_category( get_the_ID() );
		foreach ( $post_cats as $pc ) {
			if ( $pc->slug !== 'uncategorized' ) {
				$active_cat_slug = $pc->slug;
				break;
			}
		}
	}
	?>
	<!-- Sidebar Category Card -->
	<div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
		<h3 class="font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100">📂 Chuyên mục cẩm nang</h3>
		<ul class="space-y-1">
			<li>
				<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" 
				   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-bold transition-all <?php echo empty( $active_cat_slug ) ? 'bg-slate-50 text-brand-primary' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[40px]">
					<span>Tất cả tin tức</span>
					<span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500"><?php echo esc_html( wp_count_posts( 'post' )->publish ); ?></span>
				</a>
			</li>
			<?php foreach ( $blog_cats as $bcat ) :
				$is_active_cat = ( $active_cat_slug === $bcat->slug );
			?>
				<li>
					<a href="<?php echo esc_url( home_url( '/tin-tuc/?danh-muc=' . $bcat->slug ) ); ?>" 
					   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-bold transition-all <?php echo $is_active_cat ? 'bg-slate-50 text-brand-primary' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?> min-h-[40px]">
						<span><?php echo esc_html( $bcat->name ); ?></span>
						<span class="text-xs px-2 py-0.5 rounded-full <?php echo $is_active_cat ? 'bg-blue-50 text-brand-primary' : 'bg-slate-100 text-slate-500'; ?>"><?php echo esc_html( $bcat->count ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<!-- Featured Posts / Recent Widget -->
	<div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
		<h3 class="font-extrabold text-slate-900 text-sm uppercase tracking-wider mb-4 pb-3 border-b border-slate-100">🔥 Tin xem nhiều nhất</h3>
		<ul class="space-y-4">
			<?php foreach ( $recent_posts as $rp ) :
				$rp_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( $rp->ID, 'thumbnail' ) : ( get_the_post_thumbnail_url( $rp->ID, 'thumbnail' ) ?: ltdh_get_fallback_image( 'post' ) );
			?>
				<li class="flex items-start gap-3">
					<a href="<?php echo esc_url( get_permalink( $rp->ID ) ); ?>" class="w-12 h-12 rounded-lg bg-slate-100 overflow-hidden shrink-0 border border-slate-100 block">
						<img src="<?php echo esc_url( $rp_thumb ); ?>" alt="<?php echo esc_attr( $rp->post_title ); ?>" class="w-full h-full object-cover" loading="lazy">
					</a>
					<div class="min-w-0">
						<a href="<?php echo esc_url( get_permalink( $rp->ID ) ); ?>" class="text-sm font-bold text-slate-800 hover:text-brand-primary leading-snug block line-clamp-2 transition-all">
							<?php echo esc_html( $rp->post_title ); ?>
						</a>
						<time class="text-xs text-slate-400 mt-0.5 block"><?php echo get_the_date( 'd/m/Y', $rp->ID ); ?></time>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<!-- Premium CTA Consultation Sidebar Card -->
	<div class="relative bg-gradient-to-tr from-[#0E2038] to-brand-primary rounded-xl p-6 text-center text-white shadow-lg overflow-hidden">
		<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 16px 16px;"></div>
		<div class="text-3xl mb-2">💡</div>
		<h3 class="font-extrabold text-base mb-1.5 leading-tight">Bạn chưa chọn được lộ trình học?</h3>
		<p class="text-blue-100 text-sm mb-4 leading-relaxed">Để lại thông tin, ban tư vấn sẽ giải đáp lộ trình liên thông hoàn toàn miễn phí cho bạn.</p>
		<a href="<?php echo esc_url( home_url( '/kiem-tra-dieu-kien/' ) ); ?>" 
		   class="block w-full bg-brand-accent text-white font-extrabold text-sm py-3 rounded-lg hover:bg-[#e06e00] transition-all shadow-md shadow-brand-accent/20 min-h-[44px] flex items-center justify-center">
			KIỂM TRA ĐIỀU KIỆN NGAY
		</a>
	</div>
	<?php
}

/**
 * Render default guide sidebar when no widgets are active in 'sidebar-guide'.
 */
function ltdh_render_default_guide_sidebar() {
	$hotline      = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496';
	$global_zalo  = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
	?>
	<!-- Sidebar register widget -->
	<section class="hidden lg:block bg-white rounded-lg shadow-sm border border-slate-200 p-6">
		<h3 class="text-lg font-bold text-slate-900 mb-2">Nhận thêm thông tin chi tiết</h3>
		<p class="text-sm text-slate-500 mb-4">Để lại câu hỏi của bạn. Ban tuyển sinh đại học đối tác sẽ giải đáp nhanh chóng sau 15 phút.</p>
		
		<?php if ( isset( $_GET['submit_success'] ) && '1' === $_GET['submit_success'] ) : ?>
			<div class="p-3 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
				<span class="font-bold">Gửi thông tin thành công!</span> Chúng tôi sẽ liên hệ lại sớm nhất.
			</div>
		<?php endif; ?>
		<div class="ltdh-sidebar-form">
		<?php 
		if ( function_exists( 'ltdh_render_consultation_form' ) ) {
			ltdh_render_consultation_form( [
				'referral_source' => get_permalink(),
			] );
		} elseif ( function_exists( 'ltdh_render_recaptcha_field' ) ) {
			ltdh_render_recaptcha_field( 'sidebar_lead' );
		}
		?>
		</div>
	</section>

	<!-- Zalo / Call Card -->
	<?php
	get_template_part( 'template-parts/sidebar-hotline', null, [
		'hotline'  => $hotline,
		'zalo_url' => $global_zalo,
		'title'    => 'Văn phòng tuyển sinh',
	] );
}

if ( ! function_exists( 'ltdh_render_school_info_card' ) ) {
	/**
	 * Render School Info Card (for sidebars).
	 *
	 * @param int    $school_id
	 * @param string $wrapper_class
	 */
	function ltdh_render_school_info_card( $school_id, $wrapper_class = 'bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden' ) {
		if ( ! $school_id ) {
			return;
		}
		$school_title     = get_the_title( $school_id );
		$school_logo_url  = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'thumbnail' ) : get_stylesheet_directory_uri() . '/assets/images/cropped-logo-scaled-2.webp';
		$school_cover_url = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'medium' ) : ( function_exists( 'ltdh_get_fallback_image' ) ? ltdh_get_fallback_image( 'school' ) : '' );
		$school_address   = get_post_meta( $school_id, 'address', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'address', $school_id ) : '' ) ?: 'Việt Nam';
		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>">
			<div class="h-28 bg-slate-200 relative overflow-hidden">
				<img src="<?php echo esc_url( $school_cover_url ); ?>"
					 alt="<?php echo esc_attr( $school_title ); ?>"
					 loading="lazy"
					 decoding="async"
					 width="300"
					 height="180"
					 class="absolute inset-0 w-full h-full object-cover object-center">
				<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent pointer-events-none"></div>
			</div>
			<div class="p-4 sm:p-5 text-center relative -mt-12">
				<div class="w-20 h-20 rounded-2xl bg-white p-2 shadow-md mx-auto mb-3 border border-slate-100 flex items-center justify-center overflow-hidden">
					<img src="<?php echo esc_url( $school_logo_url ); ?>" alt="<?php echo esc_attr( $school_title ); ?>" class="max-w-full max-h-full object-contain">
				</div>
				<h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug mb-1">
					<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="hover:text-brand-primary transition-colors">
						<?php echo esc_html( $school_title ); ?>
					</a>
				</h3>
				<p class="text-xs text-slate-500 mb-3 line-clamp-2">📍 <?php echo esc_html( $school_address ); ?></p>

				<div class="border-t border-slate-100 pt-3">
					<a href="<?php echo esc_url( get_permalink( $school_id ) ); ?>" class="text-brand-primary font-bold text-xs sm:text-sm hover:underline flex items-center justify-center gap-1">
						<span>Xem chi tiết trường</span> <span>→</span>
					</a>
				</div>
			</div>
		</div>
		<?php
	}
}

/**
 * Render default program sidebar when no widgets are active in 'sidebar-program'.
 *
 * @param int $program_id
 * @param int $school_id
 */
function ltdh_render_default_program_sidebar( $program_id, $school_id = 0 ) {
	$global_zalo     = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
	$program_hotline = ( $school_id ? ( get_post_meta( $school_id, 'hotline', true ) ?: get_field( 'hotline', $school_id ) ) : '' ) ?: ( function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496' );
	$major_id        = 0;
	$major_rel       = get_field( 'major_relationship', $program_id );
	if ( ! empty( $major_rel ) ) {
		$major_id = is_object( $major_rel ) ? $major_rel->ID : ( is_array( $major_rel ) && ! empty( $major_rel[0] ) ? ( is_object( $major_rel[0] ) ? $major_rel[0]->ID : (int) $major_rel[0] ) : (int) $major_rel );
	}
	$school_title = $school_id ? get_the_title( $school_id ) : '';
	?>
	<!-- SCHOOL INFO CARD (Desktop Only >= 1024px) -->
	<?php 
	if ( $school_id ) {
		ltdh_render_school_info_card( $school_id, 'hidden lg:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden' );
	}
	?>

	<!-- CONSULTATION FORM -->
	<section id="register" class="hidden lg:block scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-6">
		<h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2">Đăng ký tư vấn miễn phí</h3>
		<p class="text-sm text-slate-500 mb-4">Hãy để lại thông tin, ban tư vấn tuyển sinh sẽ liên hệ và giải đáp lộ trình cụ thể cho bạn trong vòng 15 phút.</p>
		
		<div class="ltdh-sidebar-form">
		<?php 
		if ( function_exists( 'ltdh_render_consultation_form' ) ) {
			ltdh_render_consultation_form( [
				'program_id'         => $program_id,
				'current_program_id' => $program_id,
				'school_id'          => $school_id,
				'current_school_id'  => $school_id,
				'major_id'           => $major_id,
				'current_major_id'   => $major_id,
				'referral_source'    => get_permalink(),
			] );
		}
		?>
		</div>
	</section>

	<!-- ZALO GROUP COMMUNITY WIDGET -->
	<?php
	$program_school_zalo = ( $school_id ? get_field( 'zalo_group_url', $school_id ) : '' ) ?: $global_zalo;
	?>
	<div class="hidden lg:block bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-2xl p-5 shadow-md relative overflow-hidden">
		<div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

		<div class="flex items-center gap-3 mb-3 relative z-10">
			<div class="w-10 h-10 rounded-xl bg-white text-blue-600 flex items-center justify-center font-black text-xl shadow-xs shrink-0">
				💬
			</div>
			<div>
				<span class="text-[11px] uppercase font-bold tracking-wider text-blue-200 block">Cộng đồng sinh viên</span>
				<h3 class="font-extrabold text-base text-white leading-tight">Nhóm Zalo Trao Đổi</h3>
			</div>
		</div>

		<p class="text-xs text-blue-100 leading-relaxed mb-4 relative z-10">
			Tham gia nhóm Zalo trao đổi thông tin tuyển sinh, lịch học và đề án cùng sinh viên <?php echo esc_html( $school_title ?: 'chương trình' ); ?>.
		</p>

		<ul class="space-y-1.5 text-xs text-blue-50 font-medium mb-4 relative z-10">
			<li class="flex items-center gap-2">
				<span class="text-emerald-300 font-bold">✓</span>
				<span>Cập nhật lịch thi & đề án mới nhất</span>
			</li>
			<li class="flex items-center gap-2">
				<span class="text-emerald-300 font-bold">✓</span>
				<span>Giải đáp thắc mắc hồ sơ 24/7</span>
			</li>
		</ul>

		<a href="<?php echo esc_url( $program_school_zalo ); ?>"
		   target="_blank"
		   rel="noopener noreferrer"
		   class="w-full bg-white hover:bg-blue-50 text-blue-700 font-extrabold text-sm py-3 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 min-h-[44px] relative z-10 hover:scale-[1.02]">
			<span>💬 Tham gia Nhóm Zalo</span>
			<span class="text-xs">→</span>
		</a>
	</div>

	<!-- RELATED NEWS & ANNOUNCEMENTS (Sidebar - Desktop Only) -->
	<?php
	$program_news_meta = [ 'relation' => 'OR' ];
	$program_news_meta[] = [
		'key'     => 'related_programs',
		'value'   => '"' . $program_id . '"',
		'compare' => 'LIKE',
	];
	if ( $school_id ) {
		$program_news_meta[] = [
			'key'     => 'related_schools',
			'value'   => '"' . $school_id . '"',
			'compare' => 'LIKE',
		];
	}

	$related_news_query = new WP_Query( [
		'post_type'      => [ 'post', 'guide' ],
		'posts_per_page' => 6,
		'post_status'    => 'publish',
		'meta_query'     => $program_news_meta,
	] );

	if ( $related_news_query->have_posts() ) :
		$has_more = ( $related_news_query->post_count > 5 );
	?>
		<section class="hidden lg:block bg-white rounded-2xl shadow-sm border border-slate-100 p-4 md:p-5">
			<h3 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-3">Tin tức & Thông báo liên quan</h3>
			<div class="space-y-3.5">
				<?php
				$news_counter = 0;
				while ( $related_news_query->have_posts() ) :
					$related_news_query->the_post();
					if ( $news_counter >= 5 ) {
						continue;
					}
					$news_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( get_the_ID(), 'thumbnail' ) : ( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ?: ltdh_get_fallback_image( 'post' ) );
					$news_counter++;
				?>
					<div class="flex gap-3 items-start pb-3 border-b border-slate-100 last:border-b-0 last:pb-0">
						<a href="<?php the_permalink(); ?>" class="shrink-0">
							<img src="<?php echo esc_url( $news_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-12 h-12 object-cover rounded-lg" loading="lazy">
						</a>
						
						<div class="flex-1 min-w-0">
							<h4 class="font-bold text-slate-800 text-sm hover:text-brand-primary transition-colors line-clamp-2 leading-snug">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h4>
							<p class="text-xs text-slate-400 mt-0.5">📅 <?php echo get_the_date(); ?></p>
						</div>
					</div>
				<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php if ( $has_more ) : ?>
				<div class="mt-4 pt-3 border-t border-slate-100">
					<a href="<?php echo esc_url( home_url( '/tin-tuc/?chuong-trinh=' . $program_id ) ); ?>" class="w-full text-center bg-slate-100 text-slate-700 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-200/80 transition-all flex items-center justify-center gap-1.5 min-h-[38px]">
						<span>Xem thêm tin tức</span>
						<span>→</span>
					</a>
				</div>
			<?php endif; ?>
		</section>
	<?php
	endif;
	?>

	<!-- HOTLINE CONTACT CARD -->
	<?php
	get_template_part( 'template-parts/sidebar-hotline', null, [
		'hotline'     => $program_hotline,
		'zalo_url'    => $global_zalo,
		'title'       => 'Văn phòng tuyển sinh',
		'extra_class' => 'hidden lg:block',
	] );
	?>

	<!-- COMPARE BUTTON ONLY (Desktop Only) -->
	<?php
	$types = wp_get_post_terms( $program_id, LTDH_TAX_TRAINING_TYPE );
	$type_slug = ! empty( $types ) && ! is_wp_error( $types ) ? $types[0]->slug : '';
	$major_rel_id_raw = get_field( LTDH_META_MAJOR_REL, $program_id );
	$major_rel_id = 0;
	if ( is_array( $major_rel_id_raw ) ) {
		$major_rel_id = ! empty( $major_rel_id_raw ) ? ( is_object( $major_rel_id_raw[0] ) ? $major_rel_id_raw[0]->ID : $major_rel_id_raw[0] ) : 0;
	} elseif ( is_object( $major_rel_id_raw ) ) {
		$major_rel_id = $major_rel_id_raw->ID;
	} elseif ( $major_rel_id_raw ) {
		$major_rel_id = intval( $major_rel_id_raw );
	}
	$major_slug = $major_rel_id ? get_post_field( 'post_name', $major_rel_id ) : '';
	$major_name = $major_rel_id ? get_the_title( $major_rel_id ) : '';
	?>
	<button type="button"
			class="hidden lg:flex w-full text-center bg-slate-100 hover:bg-slate-200/80 text-slate-700 py-3.5 rounded-xl font-bold transition-all ltdh-compare-single-btn text-sm flex items-center justify-center gap-2 mt-4 min-h-[44px] cursor-pointer"
			data-compare-type="program" data-compare-id="<?php echo esc_attr( $program_id ); ?>"
			data-compare-title="<?php echo esc_attr( get_the_title( $program_id ) ); ?>"
			data-compare-slug="<?php echo esc_attr( get_post_field( 'post_name', $program_id ) ); ?>"
			data-compare-he="<?php echo esc_attr( $type_slug ); ?>"
			data-compare-nganh="<?php echo esc_attr( $major_slug ); ?>"
			data-compare-major-name="<?php echo esc_attr( preg_replace( '/^ngành\s+/iu', '', trim( $major_name ) ) ); ?>"
			data-compare-school-name="<?php echo esc_attr( $school_title ); ?>">
		<span>📊</span> <span>Thêm vào so sánh</span>
	</button>
<?php
}

/**
 * Render default school sidebar when no widgets are active in 'sidebar-school'.
 *
 * @param int $school_id
 */
function ltdh_render_default_school_sidebar( $school_id ) {
	$global_zalo = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
	$hotline     = ( get_post_meta( $school_id, 'hotline', true ) ?: get_field( 'hotline', $school_id ) ) ?: ( function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496' );
	$school_title = get_the_title( $school_id );
	$website     = get_field( 'website', $school_id );
	$address     = get_post_meta( $school_id, 'address', true ) ?: get_field( 'address', $school_id );
	$map_url     = get_field( 'google_maps_url', $school_id );
	$zalo_group  = get_field( 'zalo_group_url', $school_id ) ?: $global_zalo;
	?>
	<!-- School Mini Card -->
	<?php
	$school_logo_url  = function_exists( 'ltdh_get_school_logo_url' ) ? ltdh_get_school_logo_url( $school_id, 'medium' ) : '';
	$school_cover_url = function_exists( 'ltdh_get_school_cover_url' ) ? ltdh_get_school_cover_url( $school_id, 'medium_large' ) : ( get_field( 'school_banner', $school_id ) ?: get_template_directory_uri() . '/assets/images/banner-school.jpg' );
	$map_url          = get_field( 'google_map_url', $school_id ) ?: get_field( 'map_url', $school_id );
	if ( ! $map_url ) {
		$map_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $school_title . ' ' . ( $address ?: '' ) );
	}
	$words = explode( ' ', $school_title );
	$initials = '';
	foreach ( array_slice( $words, -3 ) as $w ) {
		$initials .= mb_substr( $w, 0, 1 );
	}
	$initials = mb_strtoupper( $initials );
	?>

	<!-- Consultation Form -->
	<section id="register" class="hidden lg:block scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl lg:rounded-lg shadow-sm border border-slate-100 lg:border-slate-200 p-4.5 lg:p-6 relative overflow-hidden">
		<div class="inline-flex lg:hidden items-center gap-1.5 px-2.5 py-1 rounded-full bg-brand-primary/5 text-brand-primary text-[11px] font-bold tracking-wide uppercase mb-3">
			<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
			<span>Tư vấn tuyển sinh</span>
		</div>
		<h3 class="text-base lg:text-lg font-bold text-slate-900 mb-1.5 lg:mb-2 leading-snug">Đăng ký vào <?php echo esc_html( $school_title ); ?></h3>
		<p class="text-xs lg:text-sm text-slate-500 mb-4 leading-relaxed">Nhận tư vấn hồ sơ miễn phí, hỗ trợ xử lý thủ tục nhập học nhanh chóng.</p>
		
		<div class="ltdh-sidebar-form">
		<?php 
		if ( function_exists( 'ltdh_render_consultation_form' ) ) {
			ltdh_render_consultation_form( [
				'school_id'         => $school_id,
				'current_school_id' => $school_id,
				'referral_source'   => get_permalink(),
			] );
		}
		?>
		</div>
	</section>

	<!-- RELATED NEWS & ANNOUNCEMENTS (Sidebar) -->
	<?php
	$related_news_query = new WP_Query( [
		'post_type'      => [ 'post', 'guide' ],
		'posts_per_page' => 6,
		'post_status'    => 'publish',
		'meta_query'     => [
			[
				'key'     => 'related_schools',
				'value'   => '"' . $school_id . '"',
				'compare' => 'LIKE',
			],
		],
	] );

	if ( $related_news_query->have_posts() ) :
		$has_more = ( $related_news_query->post_count > 5 );
	?>
		<section class="bg-white rounded-2xl lg:rounded-lg shadow-sm border border-slate-100 lg:border-slate-200 p-4.5 lg:p-5">
			<h3 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-3">Tin tức & Thông báo mới</h3>
			<div class="space-y-3.5">
				<?php
				$news_counter = 0;
				while ( $related_news_query->have_posts() ) :
					$related_news_query->the_post();
					if ( $news_counter >= 5 ) {
						continue;
					}
					$news_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( get_the_ID(), 'thumbnail' ) : ( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ?: ltdh_get_fallback_image( 'post' ) );
					$news_counter++;
				?>
					<div class="flex gap-3 items-start pb-3 border-b border-slate-100 last:border-b-0 last:pb-0">
						<a href="<?php the_permalink(); ?>" class="shrink-0">
							<img src="<?php echo esc_url( $news_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-12 h-12 object-cover rounded-xl lg:rounded border border-slate-100" loading="lazy">
						</a>
						
						<div class="flex-1 min-w-0">
							<h4 class="font-bold text-slate-800 text-sm hover:text-brand-primary transition-colors line-clamp-2 leading-snug">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h4>
							<p class="text-xs text-slate-400 mt-0.5">📅 <?php echo get_the_date(); ?></p>
						</div>
					</div>
				<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php if ( $has_more ) : ?>
				<div class="mt-4 pt-3 border-t border-slate-100">
					<a href="<?php echo esc_url( home_url( '/tin-tuc/?truong=' . $school_id ) ); ?>" class="w-full text-center bg-slate-50 border border-slate-200 text-slate-700 py-2.5 rounded-xl lg:rounded-lg font-bold text-sm hover:bg-slate-100 transition-all flex items-center justify-center gap-1.5 min-h-[38px]">
						<span>Xem thêm tin tức</span>
						<span>→</span>
					</a>
				</div>
			<?php endif; ?>
		</section>
	<?php
	endif;
	?>

	<!-- DESKTOP ONLY: Full Zalo Community Card -->
	<div class="hidden lg:block bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-xl p-5 shadow-md relative overflow-hidden border border-blue-500/30">
		<div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

		<div class="flex items-center gap-3 mb-3 relative z-10">
			<div class="w-10 h-10 rounded-xl bg-white text-blue-600 flex items-center justify-center font-black text-xl shadow-xs shrink-0">
				💬
			</div>
			<div>
				<span class="text-[11px] uppercase font-bold tracking-wider text-blue-200 block">Cộng đồng sinh viên</span>
				<h3 class="font-extrabold text-base text-white leading-tight">Nhóm Zalo Trao Đổi</h3>
			</div>
		</div>

		<p class="text-xs text-blue-100 leading-relaxed mb-4 relative z-10">
			Tham gia nhóm Zalo trao đổi thông tin tuyển sinh, lịch học và chia sẻ kinh nghiệm cùng cựu sinh viên <?php echo esc_html( $school_title ); ?>.
		</p>

		<a href="<?php echo esc_url( $zalo_group ); ?>"
		   target="_blank"
		   rel="noopener noreferrer"
		   class="w-full bg-white hover:bg-blue-50 text-blue-700 font-extrabold text-sm py-3 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 min-h-[44px] relative z-10 hover:scale-[1.02]">
			<span>💬 Tham gia Nhóm Zalo</span>
			<span class="text-xs">→</span>
		</a>
	</div>

	<!-- DESKTOP ONLY: Full Hotline Contact Card -->
	<div class="hidden lg:block">
		<?php
		get_template_part( 'template-parts/sidebar-hotline', null, [
			'hotline'  => $hotline,
			'zalo_url' => $global_zalo,
			'title'    => 'Văn phòng tuyển sinh',
		] );
		?>
	</div>

	<!-- MOBILE ONLY: Compact Bento Contact & Community Tiles -->
	<div class="block lg:hidden">
		<?php
		get_template_part( 'template-parts/sidebar-bento-contact', null, [
			'hotline'       => $hotline,
			'zalo_url'      => $zalo_group,
			'zalo_title'    => 'Nhóm SV ' . ( $school_title ?: 'Trường' ),
			'hotline_title' => 'Tuyển sinh 24/7',
		] );
		?>
	</div>
<?php
}

/**
 * Render default major sidebar when no widgets are active in 'sidebar-major'.
 *
 * @param int $major_id
 * @param int $school_id
 */
function ltdh_render_default_major_sidebar( $major_id, $school_id = 0 ) {
	$global_zalo = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
	$hotline     = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496';
	$major_title = get_the_title( $major_id );

	if ( ! $school_id ) {
		$school_param = isset( $_GET['truong'] ) ? sanitize_text_field( wp_unslash( $_GET['truong'] ) ) : ( isset( $_GET['from_school'] ) ? sanitize_text_field( wp_unslash( $_GET['from_school'] ) ) : ( isset( $_GET['school'] ) ? sanitize_text_field( wp_unslash( $_GET['school'] ) ) : '' ) );
		if ( ! empty( $school_param ) ) {
			if ( is_numeric( $school_param ) ) {
				$s_post = get_post( intval( $school_param ) );
			} else {
				$cpt_school = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
				$s_post     = get_page_by_path( sanitize_title( $school_param ), OBJECT, $cpt_school );
			}
			if ( $s_post && 'publish' === $s_post->post_status && ( defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' ) === $s_post->post_type ) {
				$school_id = $s_post->ID;
			}
		}
	}

	$context_school  = $school_id && ( defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school' ) === get_post_type( $school_id );
	$c_school_title  = $context_school ? get_the_title( $school_id ) : '';
	$program_hotline = ( $context_school ? ( get_post_meta( $school_id, 'hotline', true ) ?: ( function_exists( 'get_field' ) ? get_field( 'hotline', $school_id ) : '' ) ) : '' ) ?: $hotline;
	?>

	<!-- SCHOOL INFO CARD (When navigating from school) -->
	<?php if ( $context_school ) : ?>
		<?php ltdh_render_school_info_card( $school_id ); ?>
	<?php endif; ?>

	<!-- Consultation Form -->
	<section id="register" class="hidden lg:block scroll-mt-36 md:scroll-mt-40 bg-white rounded-2xl lg:rounded-lg shadow-sm border border-slate-100 lg:border-slate-200 p-4.5 lg:p-6 relative overflow-hidden">
		<div class="inline-flex lg:hidden items-center gap-1.5 px-2.5 py-1 rounded-full bg-brand-primary/5 text-brand-primary text-[11px] font-bold tracking-wide uppercase mb-3">
			<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
			<span>Tư vấn tuyển sinh</span>
		</div>
		<h3 id="ltdh-sidebar-form-title" class="text-base lg:text-lg font-bold text-slate-900 mb-1.5 lg:mb-2 leading-snug">
			<?php if ( $context_school ) : ?>
				Đăng ký tư vấn ngành <?php echo esc_html( $major_title ); ?> — <?php echo esc_html( $c_school_title ); ?>
			<?php else : ?>
				Đăng ký tư vấn ngành <?php echo esc_html( $major_title ); ?>
			<?php endif; ?>
		</h3>
		<p id="ltdh-sidebar-form-desc" class="text-xs lg:text-sm text-slate-500 mb-4 leading-relaxed">
			<?php if ( $context_school ) : ?>
				Để lại thông tin, ban tuyển sinh sẽ gửi thông tin chi tiết về ngành <?php echo esc_html( $major_title ); ?> tại <strong><?php echo esc_html( $c_school_title ); ?></strong> và lộ trình học phù hợp nhất với bạn.
			<?php else : ?>
				Để lại thông tin, ban tuyển sinh sẽ gửi danh sách trường đào tạo phù hợp nhất với học lực và thời gian của bạn.
			<?php endif; ?>
		</p>
		
		<div class="ltdh-sidebar-form">
		<?php 
		if ( function_exists( 'ltdh_render_consultation_form' ) ) {
			$consultation_hidden = [
				'major_id'         => $major_id,
				'current_major_id' => $major_id,
				'referral_source'  => get_permalink(),
			];
			if ( $context_school ) {
				$consultation_hidden['school_id']         = $school_id;
				$consultation_hidden['current_school_id'] = $school_id;
			}
			ltdh_render_consultation_form( $consultation_hidden );
		}
		?>
		</div>
	</section>

	<!-- RELATED NEWS & ANNOUNCEMENTS (Sidebar) -->
	<?php
	$related_news_query = new WP_Query( [
		'post_type'      => [ 'post', 'guide' ],
		'posts_per_page' => 6,
		'post_status'    => 'publish',
		'meta_query'     => [
			[
				'key'     => 'related_majors',
				'value'   => '"' . $major_id . '"',
				'compare' => 'LIKE',
			],
		],
	] );

	if ( $related_news_query->have_posts() ) :
		$has_more = ( $related_news_query->post_count > 5 );
	?>
		<section class="bg-white rounded-2xl lg:rounded-lg shadow-sm border border-slate-100 lg:border-slate-200 p-4.5 lg:p-5">
			<h3 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-3">Tin tức & Hướng dẫn</h3>
			<div class="space-y-3.5">
				<?php
				$news_counter = 0;
				while ( $related_news_query->have_posts() ) :
					$related_news_query->the_post();
					if ( $news_counter >= 5 ) {
						continue;
					}
					$news_thumb = function_exists( 'ltdh_get_post_thumbnail_url' ) ? ltdh_get_post_thumbnail_url( get_the_ID(), 'thumbnail' ) : ( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ?: ltdh_get_fallback_image( 'post' ) );
					$news_counter++;
				?>
					<div class="flex gap-3 items-start pb-3 border-b border-slate-100 last:border-b-0 last:pb-0">
						<a href="<?php the_permalink(); ?>" class="shrink-0">
							<img src="<?php echo esc_url( $news_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="w-12 h-12 object-cover rounded-xl lg:rounded border border-slate-100" loading="lazy">
						</a>
						
						<div class="flex-1 min-w-0">
							<h4 class="font-bold text-slate-800 text-sm hover:text-brand-primary transition-colors line-clamp-2 leading-snug">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h4>
							<p class="text-xs text-slate-400 mt-0.5">📅 <?php echo get_the_date(); ?></p>
						</div>
					</div>
				<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php if ( $has_more ) : ?>
				<div class="mt-4 pt-3 border-t border-slate-100">
					<a href="<?php echo esc_url( home_url( '/tin-tuc/?nganh=' . $major_id ) ); ?>" class="w-full text-center bg-slate-50 border border-slate-200 text-slate-700 py-2.5 rounded-xl lg:rounded-lg font-bold text-sm hover:bg-slate-100 transition-all flex items-center justify-center gap-1.5 min-h-[38px]">
						<span>Xem thêm tin tức</span>
						<span>→</span>
					</a>
				</div>
			<?php endif; ?>
		</section>
	<?php
	endif;
	?>

	<!-- DESKTOP ONLY: Full Zalo Community Card -->
	<div class="hidden lg:block bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-xl p-5 shadow-md relative overflow-hidden border border-blue-500/30">
		<div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

		<div class="flex items-center gap-3 mb-3 relative z-10">
			<div class="w-10 h-10 rounded-xl bg-white text-blue-600 flex items-center justify-center font-black text-xl shadow-xs shrink-0">
				💬
			</div>
			<div>
				<span class="text-[11px] uppercase font-bold tracking-wider text-blue-200 block">Cộng đồng sinh viên</span>
				<h3 class="font-extrabold text-base text-white leading-tight">Nhóm Zalo Trao Đổi</h3>
			</div>
		</div>

		<p class="text-xs text-blue-100 leading-relaxed mb-4 relative z-10">
			Tham gia nhóm Zalo trao đổi thông tin tuyển sinh, định hướng nghề nghiệp ngành <?php echo esc_html( $major_title ); ?>.
		</p>

		<a href="<?php echo esc_url( $global_zalo ); ?>"
		   target="_blank"
		   rel="noopener noreferrer"
		   class="w-full bg-white hover:bg-blue-50 text-blue-700 font-extrabold text-sm py-3 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 min-h-[44px] relative z-10 hover:scale-[1.02]">
			<span>💬 Tham gia Nhóm Zalo</span>
			<span class="text-xs">→</span>
		</a>
	</div>

	<!-- DESKTOP ONLY: Full Hotline Contact Card -->
	<div class="hidden lg:block">
		<?php
		get_template_part( 'template-parts/sidebar-hotline', null, [
			'hotline'  => $program_hotline,
			'zalo_url' => $global_zalo,
			'title'    => 'Văn phòng tuyển sinh',
		] );
		?>
	</div>

	<!-- MOBILE ONLY: Compact Bento Contact & Community Tiles -->
	<div class="block lg:hidden">
		<?php
		get_template_part( 'template-parts/sidebar-bento-contact', null, [
			'hotline'       => $program_hotline,
			'zalo_url'      => $global_zalo,
			'zalo_title'    => 'Nhóm Zalo ' . ( $major_title ?: 'Học viên' ),
			'hotline_title' => 'Tuyển sinh 24/7',
		] );
		?>
	</div>
<?php
}
