<?php
/**
 * Main index template (Homepage fully optimized to match Mockup UI/UX)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Cache queries for schools
$schools_query = ltdh_get_cached_query( 'ltdh_featured_schools', [
	'post_type'      => 'school',
	'posts_per_page' => 5,
	'post_status'    => 'publish',
], HOUR_IN_SECONDS );

$news_query = new WP_Query( [
	'post_type'      => 'post',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
] );

$hotline = get_field( 'global_hotline', 'options' ) ?: '0389198653';
$zalo    = get_field( 'global_zalo_url', 'options' ) ?: 'https://zalo.me';
?>

<main id="primary" class="site-main bg-white">

	<!-- 1. HERO SECTION -->
	<section class="relative bg-gradient-to-br from-[#EFF6FF] via-[#F8FAFC] to-[#FFFFFF] overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-32">
		<div class="absolute right-0 top-1/2 -translate-y-1/2 w-1/2 h-full opacity-10 bg-[radial-gradient(var(--tw-gradient-to-r,#1E3A8A)_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
				<!-- Hero Left Text Column -->
				<div class="lg:col-span-6 space-y-6 text-center lg:text-left z-10">
					<div class="relative inline-block">
						<span class="font-playfair italic text-3xl md:text-4xl text-brand-primary block -mb-2 font-normal">Tư vấn</span>
					</div>
					
					<h1 class="text-4xl sm:text-5xl lg:text-[56px] font-black text-[#0B2545] leading-[1.1] tracking-tight font-display uppercase">
						Lựa chọn đại học<br>
						<span class="text-brand-primary">Đúng đắn</span>
					</h1>

					<div class="pt-2">
						<span class="bg-[#F5BF23] text-[#0F172A] px-6 py-2 rounded-full inline-block text-lg sm:text-xl font-extrabold -rotate-2 transform shadow-md">
							ĐỊNH HƯỚNG TƯƠNG LAI
						</span>
					</div>

					<!-- Badges Grid (3 items row layout) -->
					<div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 w-full text-left">
						<div class="flex items-center gap-3 bg-white px-4 py-3.5 rounded-xl border border-slate-100 shadow-sm">
							<span class="text-brand-primary bg-emerald-50 p-2.5 rounded-lg shrink-0 font-bold">✓</span>
							<div class="leading-tight">
								<span class="block text-base font-bold text-slate-800">Tư vấn 1:1</span>
								<span class="text-sm text-slate-500">Hoàn toàn miễn phí</span>
							</div>
						</div>
						<div class="flex items-center gap-3 bg-white px-4 py-3.5 rounded-xl border border-slate-100 shadow-sm">
							<span class="text-brand-primary bg-emerald-50 p-2.5 rounded-lg shrink-0 font-bold">✓</span>
							<div class="leading-tight">
								<span class="block text-base font-bold text-slate-800">Rút ngắn lộ trình</span>
								<span class="text-sm text-slate-500">Miễn giảm tín chỉ tối đa</span>
							</div>
						</div>
						<div class="flex items-center gap-3 bg-white px-4 py-3.5 rounded-xl border border-slate-100 shadow-sm">
							<span class="text-brand-primary bg-emerald-50 p-2.5 rounded-lg shrink-0 font-bold">✓</span>
							<div class="leading-tight">
								<span class="block text-base font-bold text-slate-800">Bằng chuẩn Bộ GD&ĐT</span>
								<span class="text-sm text-slate-500">Tương đương bằng chính quy</span>
							</div>
						</div>
					</div>
					
					<!-- CTAs -->
					<div class="flex flex-col sm:flex-row gap-4 pt-2 justify-center lg:justify-start">
						<a href="#register-section" class="bg-brand-primary text-white text-center px-8 py-4 rounded-xl font-bold hover:bg-emerald-700 transition-all flex items-center justify-center gap-2 shadow-md shadow-emerald-600/20">
							LIÊN HỆ NGAY
							<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
							</svg>
						</a>
						<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex items-center justify-center gap-3 bg-white border border-slate-200 px-6 py-4 rounded-xl font-bold text-slate-800 hover:bg-slate-50 transition-all">
							<span class="h-10 w-10 bg-emerald-50 text-emerald-600 flex items-center justify-center rounded-full shrink-0">📞</span>
							<div class="text-left leading-tight">
								<span class="block text-xs text-slate-400 font-medium">Hotline tư vấn</span>
								<span class="text-sm font-black text-brand-primary"><?php echo esc_html( $hotline ); ?></span>
							</div>
						</a>
					</div>
				</div>
				
				<!-- Hero Right Image Column -->
				<div class="lg:col-span-6 relative hidden lg:flex justify-center items-center">
					<div class="relative w-full max-w-md md:max-w-lg aspect-square">
						<div class="absolute inset-0 bg-gradient-to-tr from-brand-primary/20 to-[#EFF6FF]/50 rounded-lg blur-2xl -z-10"></div>
						<div class="w-full h-full rounded-lg border-4 border-white shadow-xl overflow-hidden relative bg-slate-100 flex items-center justify-center">
							<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=600');"></div>
							<div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 via-transparent to-transparent"></div>
						</div>

						<div class="absolute bottom-10 left-0 bg-white p-3 rounded-lg shadow-lg border border-slate-100 flex items-center gap-3 animate-bounce">
							<span class="text-2xl">🎓</span>
							<div class="leading-none text-left">
								<span class="block text-sm font-black text-slate-800">50+ Đối tác</span>
								<span class="text-sm text-slate-400">Trường đại học uy tín</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Search & Filters Container (Compact Single-Row Bar) -->
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-30">
		<div class="bg-white rounded-xl shadow-xl border border-slate-100 p-4 md:p-5">
			<form action="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" method="GET" class="space-y-0">
				<!-- Mobile layout toggler -->
				<div class="md:hidden flex flex-col gap-2">
					<input type="text" name="s" placeholder="Tìm tên chương trình học..." class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none placeholder-slate-400 font-medium min-h-[40px]" />
					<button type="button" onclick="document.getElementById('mobile-advanced-filters').toggleAttribute('open')" class="w-full text-center border border-slate-200 text-slate-700 py-2.5 rounded-lg font-bold text-xs uppercase tracking-wider">
						⚙ Bộ lọc chi tiết
					</button>
					<button type="submit" class="w-full bg-brand-accent text-white py-2.5 rounded-lg font-bold hover:bg-amber-700 transition-all text-xs uppercase tracking-wider">
						TÌM KIẾM
					</button>
				</div>

				<?php
				$schools = get_posts( [ 'post_type' => 'school', 'numberposts' => -1 ] );
				$majors  = get_posts( [ 'post_type' => 'major', 'numberposts' => -1 ] );
				$types   = get_terms( [ 'taxonomy' => 'training_type', 'hide_empty' => false ] );
				?>

				<!-- Mobile Expandable Filters (Only active on mobile) -->
				<details id="mobile-advanced-filters" class="md:hidden group border-t border-slate-100 mt-3 pt-3">
					<summary class="list-none hidden"></summary>
					<div class="space-y-3">
						<div>
							<select name="truong_filter" class="w-full border border-slate-200 rounded-lg px-4 py-2 text-sm focus:border-brand-primary focus:outline-none bg-white">
								<option value="">-- Chọn trường học --</option>
								<?php
								foreach ( $schools as $s ) {
									echo '<option value="' . esc_attr( $s->ID ) . '">' . esc_html( $s->post_title ) . '</option>';
								}
								?>
							</select>
						</div>
						<div>
							<select name="nganh_filter" class="w-full border border-slate-200 rounded-lg px-4 py-2 text-sm focus:border-brand-primary focus:outline-none bg-white">
								<option value="">-- Chọn ngành học --</option>
								<?php
								foreach ( $majors as $m ) {
									echo '<option value="' . esc_attr( $m->ID ) . '">' . esc_html( $m->post_title ) . '</option>';
								}
								?>
							</select>
						</div>
						<div>
							<select name="he_filter" class="w-full border border-slate-200 rounded-lg px-4 py-2 text-sm focus:border-brand-primary focus:outline-none bg-white">
								<option value="">-- Chọn hệ học --</option>
								<?php
								foreach ( $types as $t ) {
									echo '<option value="' . esc_attr( $t->slug ) . '">' . esc_html( $t->name ) . '</option>';
								}
								?>
							</select>
						</div>
					</div>
				</details>

				<!-- Desktop Compact Filter Bar (1 Single Row) -->
				<div class="hidden md:flex md:items-center md:gap-3">
					<!-- Keyword -->
					<div class="flex-1 min-w-[20%]">
						<input type="text" name="s" placeholder="Từ khóa tìm kiếm..." class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:border-brand-primary focus:outline-none placeholder-slate-400 font-medium min-h-[38px]" />
					</div>
					
					<!-- School Dropdown -->
					<div class="flex-1">
						<select name="truong_filter" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:border-brand-primary focus:outline-none bg-transparent min-h-[38px]">
							<option value="">-- Chọn trường --</option>
							<?php
							foreach ( $schools as $s ) {
								echo '<option value="' . esc_attr( $s->ID ) . '">' . esc_html( $s->post_title ) . '</option>';
							}
							?>
						</select>
					</div>
					
					<!-- Major Dropdown -->
					<div class="flex-1">
						<select name="nganh_filter" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:border-brand-primary focus:outline-none bg-transparent min-h-[38px]">
							<option value="">-- Chọn ngành học --</option>
							<?php
							foreach ( $majors as $m ) {
								echo '<option value="' . esc_attr( $m->ID ) . '">' . esc_html( $m->post_title ) . '</option>';
							}
							?>
						</select>
					</div>

					<!-- System Dropdown -->
					<div class="flex-1">
						<select name="he_filter" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:border-brand-primary focus:outline-none bg-transparent min-h-[38px]">
							<option value="">-- Chọn hệ học --</option>
							<?php
							foreach ( $types as $t ) {
								echo '<option value="' . esc_attr( $t->slug ) . '">' . esc_html( $t->name ) . '</option>';
							}
							?>
						</select>
					</div>

					<!-- Search Action -->
					<div class="shrink-0 flex gap-1.5">
						<button type="submit" class="bg-brand-accent hover:bg-amber-700 text-white font-extrabold text-xs px-5 py-2.5 rounded-lg transition-all uppercase tracking-wider min-h-[38px]">
							Tìm kiếm
						</button>
						<a href="<?php echo esc_url( home_url( '/chuong-trinh/' ) ); ?>" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-lg transition-all flex items-center justify-center min-h-[38px]" title="Reset bộ lọc">
							🔄
						</a>
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- 2. HOT MAJORS SECTION -->
	<section class="py-12 bg-slate-50 border-y border-slate-100 relative overflow-hidden">
		<div class="max-w-7xl mx-auto relative z-10">
			<div class="text-center max-w-2xl mx-auto mb-8 space-y-2 px-4">
				<span class="text-[#2563EB] text-xs font-extrabold uppercase tracking-widest block">KHÁM PHÁ NGÀNH HỌC</span>
				<h2 class="text-xl md:text-3xl font-black text-slate-900">Các ngành đào tạo hot nhất</h2>
				<p class="text-slate-500 text-sm">Hàng trăm ngành đào tạo đa dạng, phù hợp mọi đối tượng và xu hướng thị trường lao động.</p>
			</div>

			<!-- Mobile: Horizontal scroll | Desktop: Grid -->
			<div class="flex overflow-x-auto lg:grid lg:grid-cols-6 gap-3 snap-x snap-mandatory px-4 pb-4 md:px-0 md:pb-0 no-scrollbar" style="-webkit-overflow-scrolling: touch;">
				<?php
				$majors_query = new WP_Query( [
					'post_type'      => 'major',
					'posts_per_page' => 12,
					'post_status'    => 'publish',
				] );

				// Color palette cycling for cards
				$card_colors = [
					[ 'bg' => 'bg-blue-50',   'icon_bg' => 'bg-blue-100',   'icon_text' => 'text-blue-600',   'border' => 'border-blue-100',   'hover' => 'hover:border-blue-300' ],
					[ 'bg' => 'bg-violet-50', 'icon_bg' => 'bg-violet-100', 'icon_text' => 'text-violet-600', 'border' => 'border-violet-100', 'hover' => 'hover:border-violet-300' ],
					[ 'bg' => 'bg-emerald-50','icon_bg' => 'bg-emerald-100','icon_text' => 'text-emerald-600','border' => 'border-emerald-100','hover' => 'hover:border-emerald-300' ],
					[ 'bg' => 'bg-amber-50',  'icon_bg' => 'bg-amber-100',  'icon_text' => 'text-amber-600',  'border' => 'border-amber-100',  'hover' => 'hover:border-amber-300' ],
					[ 'bg' => 'bg-rose-50',   'icon_bg' => 'bg-rose-100',   'icon_text' => 'text-rose-600',   'border' => 'border-rose-100',   'hover' => 'hover:border-rose-300' ],
					[ 'bg' => 'bg-cyan-50',   'icon_bg' => 'bg-cyan-100',   'icon_text' => 'text-cyan-600',   'border' => 'border-cyan-100',   'hover' => 'hover:border-cyan-300' ],
				];

				if ( $majors_query->have_posts() ) :
					$i = 0;
					while ( $majors_query->have_posts() ) : $majors_query->the_post();
						$title       = get_the_title();
						$clean_title = trim( preg_replace( '/\s*[\(\-][\s\S]*/', '', $title ) );
						$lower_title = mb_strtolower( $clean_title, 'UTF-8' );
						$icon        = '🎓';
						if ( strpos( $lower_title, 'công nghệ thông tin' ) !== false || strpos( $lower_title, 'cntt' ) !== false ) {
							$icon = '💻';
						} elseif ( strpos( $lower_title, 'quản trị' ) !== false ) {
							$icon = '📈';
						} elseif ( strpos( $lower_title, 'kinh tế' ) !== false || strpos( $lower_title, 'tài chính' ) !== false || strpos( $lower_title, 'kế toán' ) !== false ) {
							$icon = '💰';
						} elseif ( strpos( $lower_title, 'marketing' ) !== false ) {
							$icon = '🎯';
						} elseif ( strpos( $lower_title, 'ngôn ngữ' ) !== false || strpos( $lower_title, 'tiếng anh' ) !== false ) {
							$icon = '🌐';
						} elseif ( strpos( $lower_title, 'thương mại' ) !== false ) {
							$icon = '🏪';
						} elseif ( strpos( $lower_title, 'thiết kế' ) !== false || strpos( $lower_title, 'đồ họa' ) !== false ) {
							$icon = '🎨';
						} elseif ( strpos( $lower_title, 'luật' ) !== false ) {
							$icon = '⚖️';
						} elseif ( strpos( $lower_title, 'xây dựng' ) !== false || strpos( $lower_title, 'kiến trúc' ) !== false ) {
							$icon = '🏗️';
						} elseif ( strpos( $lower_title, 'y' ) !== false || strpos( $lower_title, 'dược' ) !== false ) {
							$icon = '🏥';
						}
						$c = $card_colors[ $i % count( $card_colors ) ];
						$i++;
					?>
						<a href="<?php the_permalink(); ?>"
						   class="group flex flex-col items-center text-center p-4 bg-white border <?php echo $c['border']; ?> rounded-xl <?php echo $c['hover']; ?> hover:shadow-md transition-all snap-start shrink-0 w-[140px] md:w-auto">
							<div class="h-12 w-12 <?php echo $c['icon_bg']; ?> <?php echo $c['icon_text']; ?> rounded-xl flex items-center justify-center mb-3 text-xl group-hover:scale-110 transition-transform">
								<?php echo $icon; ?>
							</div>
							<h4 class="font-bold text-xs text-slate-700 leading-snug line-clamp-2"><?php echo esc_html( $clean_title ); ?></h4>
						</a>
					<?php
					endwhile;
					wp_reset_postdata();
				else :
					echo '<p class="text-sm text-slate-400 col-span-6 text-center">Chưa có ngành học nào.</p>';
				endif;
				?>
			</div>

			<div class="text-center mt-8">
				<a href="<?php echo esc_url( home_url( '/nganh-hoc/' ) ); ?>"
				   class="inline-flex items-center gap-2 text-[#2563EB] font-bold text-sm hover:underline">
					Xem tất cả ngành học <span>→</span>
				</a>
			</div>
		</div>
	</section>

	<!-- 3. NATIONAL SCHOOLS SECTION -->
	<section id="program-section" class="py-12 md:py-16 bg-white">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8">
				<div class="space-y-2">
					<span class="text-brand-primary text-sm font-bold uppercase tracking-wider block">THÔNG TIN TUYỂN SINH</span>
					<h2 class="text-xl md:text-3xl font-black text-slate-900">Các trường đào tạo trên toàn quốc</h2>
				</div>
				<a href="<?php echo esc_url( home_url( '/truong-lien-ket/' ) ); ?>" class="text-sm text-brand-primary font-bold hover:underline mt-4 sm:mt-0 flex items-center gap-1">
					Xem tất cả
					<span>→</span>
				</a>
			</div>
			
			<div class="flex lg:grid overflow-x-auto lg:overflow-x-visible snap-x snap-mandatory lg:snap-none gap-4 pb-4 lg:pb-0 no-scrollbar lg:grid-cols-5">
				<?php 
				if ( $schools_query->have_posts() ) {
					$index = 0;
					$fallback_images = [
						'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&q=80&w=300',
						'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=300',
						'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&q=80&w=300',
						'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&q=80&w=300',
						'https://images.unsplash.com/photo-1519452635265-7b1fbfd1e4e0?auto=format&fit=crop&q=80&w=300'
					];
					while ( $schools_query->have_posts() ) : $schools_query->the_post();
						$school_id = get_the_ID();
						$address = get_field( 'address', $school_id );
						$hotline = get_field( 'hotline', $school_id ) ?: get_field( 'global_hotline', 'options' );
						$thumb_url = get_the_post_thumbnail_url( $school_id, 'medium' ) ?: $fallback_images[$index % 5];
						$logo_id = get_field( 'logo', $school_id );
						$en_name = get_post_meta( $school_id, 'english_name', true ) ?: 'University';
						
						$school_progs = get_posts( [
							'post_type' => 'program',
							'numberposts' => -1,
							'meta_query' => [
								[
									'key' => 'school_relationship',
									'value' => $school_id,
									'compare' => '='
								]
							]
						] );
						
						$systems = [];
						foreach ( $school_progs as $sp ) {
							$terms = wp_get_post_terms( $sp->ID, 'training_type' );
							if ( ! is_wp_error( $terms ) ) {
								foreach ( $terms as $t ) {
									$systems[$t->slug] = $t->name;
								}
							}
						}
						$systems_label = '';
						if ( ! empty( $systems ) ) {
							if ( count( $systems ) === 1 && isset( $systems['tu-xa'] ) ) {
								$systems_label = '';
							} else {
								$systems_label = implode( ' · ', $systems );
							}
						}
						$prog_count = count( $school_progs );
				?>
						<div class="bg-white border border-slate-100 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between shrink-0 w-[45vw] sm:w-[250px] lg:w-auto snap-center">
							<div class="h-20 md:h-28 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $thumb_url ); ?>');"></div>
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
						$index++;
					endwhile;
					wp_reset_postdata();
				} else {
					echo '<div class="col-span-5 text-center text-slate-500 py-6">Chưa có trường liên kết nào được gieo dữ liệu.</div>';
				}
				?>
			</div>
		</div>
	</section>

	<!-- 4. ELIGIBILITY QUICK CHECK SECTION -->
	<section class="py-12 bg-slate-50 border-t border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
				<div class="lg:col-span-7 space-y-4">
					<span class="inline-block bg-blue-50 text-brand-primary text-xs font-extrabold px-3 py-1.5 rounded-lg uppercase tracking-wider">
						Điều kiện tuyển sinh
					</span>
					<h2 class="text-2xl sm:text-3xl lg:text-4xl font-black font-display text-slate-900 leading-tight">
						Bạn có đủ điều kiện học<br>Liên thông & Đại học từ xa?
					</h2>
					<p class="text-slate-500 text-sm leading-relaxed max-w-2xl">
						Chương trình tuyển sinh mở rộng cho nhiều đối tượng. Chỉ mất 1 phút để kiểm tra tự động xem bạn có được miễn giảm tín chỉ học tập hay không.
					</p>

					<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
						<div class="bg-white p-3 rounded-lg border border-slate-200">
							<span class="block text-sm font-bold text-slate-800 mb-0.5">Người đi làm</span>
							<span class="text-xs text-slate-500">Học trực tuyến linh hoạt</span>
						</div>
						<div class="bg-white p-3 rounded-lg border border-slate-200">
							<span class="block text-sm font-bold text-slate-800 mb-0.5">Đã tốt nghiệp TC/CĐ</span>
							<span class="text-xs text-slate-500">Liên thông miễn giảm tín</span>
						</div>
						<div class="bg-white p-3 rounded-lg border border-slate-200">
							<span class="block text-sm font-bold text-slate-800 mb-0.5">Học sinh tốt nghiệp THPT</span>
							<span class="text-xs text-slate-500">Xét học bạ tuyển thẳng</span>
						</div>
					</div>

					<div class="flex flex-wrap gap-3 pt-4">
						<a href="<?php echo esc_url( home_url( '/kiem-tra-dieu-kien/' ) ); ?>" class="bg-brand-primary text-white px-6 py-3 rounded-lg font-bold text-sm hover:bg-brand-darkBlue transition-all shadow-md shadow-brand-primary/10">
							Bắt đầu kiểm tra ngay ➔
						</a>
						<a href="#register-section" class="bg-white border border-slate-200 text-slate-700 px-6 py-3 rounded-lg font-bold text-sm hover:bg-slate-50 transition-all">
							Tư vấn tiêu chuẩn tuyển sinh
						</a>
					</div>
				</div>

				<div class="lg:col-span-5 flex justify-center">
					<div class="relative w-full max-w-sm aspect-video sm:aspect-square bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
						<div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100/50 space-y-2">
							<div class="flex items-center gap-2">
								<span class="text-green-500 text-lg">✔</span>
								<span class="text-xs font-bold text-slate-700">Miễn thi đầu vào</span>
							</div>
							<div class="flex items-center gap-2">
								<span class="text-green-500 text-lg">✔</span>
								<span class="text-xs font-bold text-slate-700">Chấp nhận bằng Trung cấp/Cao đẳng nghề</span>
							</div>
							<div class="flex items-center gap-2">
								<span class="text-green-500 text-lg">✔</span>
								<span class="text-xs font-bold text-slate-700">Xét tuyển học bạ THPT nhanh gọn</span>
							</div>
						</div>
						<div class="text-center pt-2">
							<span class="text-[11px] text-slate-400 block font-medium">Bằng đại học được Bộ GD&ĐT cấp phép và công nhận</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 5. SECTION 1: STUDENT BENEFITS -->
	<section class="py-12 bg-white relative overflow-hidden">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
			<div class="text-center max-w-2xl mx-auto mb-8">
				<h2 class="text-xl md:text-3xl font-black text-slate-900 mb-2">Lợi ích dành cho bạn</h2>
				<p class="text-slate-500 text-sm">Chương trình tối ưu giúp người đi làm nâng cao bằng cấp dễ dàng.</p>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
				<div class="bg-slate-50/70 border border-slate-100 rounded-xl p-5 text-left shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-lg mb-3 text-lg">📜</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Bằng chuẩn Bộ GD&ĐT</h4>
					<p class="text-xs text-slate-500 leading-relaxed">Phôi bằng tương đương chính quy, đủ điều kiện xét bậc lương và học lên Cao học.</p>
				</div>
				<div class="bg-slate-50/70 border border-slate-100 rounded-xl p-5 text-left shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-lg mb-3 text-lg">🔗</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Miễn giảm tín chỉ tối đa</h4>
					<p class="text-xs text-slate-500 leading-relaxed">Xét duyệt bảng điểm cũ để lược bỏ các môn đã học, rút ngắn thời gian ra trường.</p>
				</div>
				<div class="bg-slate-50/70 border border-slate-100 rounded-xl p-5 text-left shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-lg mb-3 text-lg">💻</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Học Online linh hoạt</h4>
					<p class="text-xs text-slate-500 leading-relaxed">Học trực tuyến mọi lúc mọi nơi qua E-learning. Thi tập trung vào cuối tuần.</p>
				</div>
				<div class="bg-slate-50/70 border border-slate-100 rounded-xl p-5 text-left shadow-sm">
					<div class="h-10 w-10 bg-blue-50 text-[#2563EB] flex items-center justify-center rounded-lg mb-3 text-lg">📝</div>
					<h4 class="font-bold text-sm text-slate-800 mb-1">Hỗ trợ hồ sơ từ A-Z</h4>
					<p class="text-xs text-slate-500 leading-relaxed">Tiếp nhận hồ sơ online nhanh gọn, hoàn thiện thủ tục nhập học trọn gói.</p>
				</div>
			</div>
		</div>
	</section>

	<!-- 6. SECTION 2: WHY CHOOSE US & METRICS -->
	<section class="py-8 bg-gradient-to-r from-[#0E2038] to-[#1E3A8A] text-white relative">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex flex-col lg:flex-row items-center justify-between gap-6">
				<!-- Title -->
				<div class="w-full lg:w-1/3 text-center lg:text-left">
					<h3 class="text-lg md:text-xl font-extrabold font-display tracking-tight text-white uppercase">
						Vì sao chọn chúng tôi?
					</h3>
					<p class="text-slate-400 text-xs mt-1">Đơn vị tư vấn và liên kết tuyển sinh hàng đầu.</p>
				</div>
				
				<!-- KPIs -->
				<div class="w-full lg:w-2/3 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
					<div>
						<span class="block font-display font-black text-2xl md:text-3xl text-brand-accent leading-none mb-1">1.200+</span>
						<span class="text-xs text-slate-300 font-bold block">Học viên</span>
					</div>
					<div>
						<span class="block font-display font-black text-2xl md:text-3xl text-brand-accent leading-none mb-1">98%</span>
						<span class="text-xs text-slate-300 font-bold block">Hài lòng</span>
					</div>
					<div>
						<span class="block font-display font-black text-2xl md:text-3xl text-brand-accent leading-none mb-1">30+</span>
						<span class="text-xs text-slate-300 font-bold block">Đối tác ĐH</span>
					</div>
					<div>
						<span class="block font-display font-black text-2xl md:text-3xl text-brand-accent leading-none mb-1">10+</span>
						<span class="text-xs text-slate-300 font-bold block">Năm uy tín</span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 7. STUDENT TESTIMONIALS -->
	<section class="py-12 bg-white relative overflow-hidden">
		<div class="absolute -left-16 -top-16 w-64 h-64 bg-brand-primary/10 rounded-full blur-3xl pointer-events-none z-0"></div>
		<div class="absolute -right-16 -bottom-16 w-64 h-64 bg-brand-accent/10 rounded-full blur-3xl pointer-events-none z-0"></div>
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
			<div class="text-center max-w-2xl mx-auto mb-8 space-y-3">
				<span class="text-brand-primary text-sm font-bold uppercase tracking-wider block">CẢM NHẬN HỌC VIÊN</span>
				<h2 class="text-xl md:text-3xl font-black text-slate-900">Đánh giá của học viên</h2>
				<p class="text-slate-500 text-sm">Hàng ngàn học viên đã tin tưởng và đồng hành cùng chúng tôi trên hành trình chinh phục tấm bằng đại học.</p>
			</div>

			<div class="flex md:grid overflow-x-auto md:overflow-x-visible snap-x snap-mandatory md:snap-none gap-6 pb-4 md:pb-0 no-scrollbar md:grid-cols-3">
				<!-- Testimonial 1 -->
				<div class="shrink-0 w-[85vw] md:w-auto snap-center bg-slate-50 border border-slate-100 rounded-xl p-6 relative">
					<div class="text-yellow-400 text-lg mb-3">★★★★★</div>
					<p class="text-sm text-slate-600 leading-relaxed mb-4 italic">"Mình đã học Văn bằng 2 CNTT tại đây. Lịch học trực tuyến rất linh hoạt, giảng viên nhiệt tình và kiến thức thực tế. Sau khi tốt nghiệp mình đã được thăng chức đúng như mong đợi."</p>
					<div class="flex items-center gap-3 pt-4 border-t border-slate-200">
						<div class="h-10 w-10 bg-brand-primary text-white rounded-full flex items-center justify-center font-bold text-sm">NH</div>
						<div>
							<p class="font-bold text-sm text-slate-800">Nguyễn Hương</p>
							<p class="text-xs text-slate-400">VB2 Công nghệ thông tin</p>
						</div>
					</div>
				</div>

				<!-- Testimonial 2 -->
				<div class="shrink-0 w-[85vw] md:w-auto snap-center bg-slate-50 border border-slate-100 rounded-xl p-6 relative">
					<div class="text-yellow-400 text-lg mb-3">★★★★★</div>
					<p class="text-sm text-slate-600 leading-relaxed mb-4 italic">"Từ xa giúp mình vừa đi làm vừa học đại học. Chương trình đào tạo bài bản, hồ sơ thủ tục nhanh gọn. Mình rất hài lòng với chất lượng giảng dạy."</p>
					<div class="flex items-center gap-3 pt-4 border-t border-slate-200">
						<div class="h-10 w-10 bg-brand-primary text-white rounded-full flex items-center justify-center font-bold text-sm">TM</div>
						<div>
							<p class="font-bold text-sm text-slate-800">Trần Minh</p>
							<p class="text-xs text-slate-400">Đại học Từ xa - Quản trị kinh doanh</p>
						</div>
					</div>
				</div>

				<!-- Testimonial 3 -->
				<div class="shrink-0 w-[85vw] md:w-auto snap-center bg-slate-50 border border-slate-100 rounded-xl p-6 relative">
					<div class="text-yellow-400 text-lg mb-3">★★★★★</div>
					<p class="text-sm text-slate-600 leading-relaxed mb-4 italic">"Liên thông từ Cao đẳng lên Đại học nhanh hơn mình nghĩ. Nhờ tư vấn viên hướng dẫn tận tình mà mình hoàn thành hồ sơ chỉ trong 2 tuần. Bằng cấp được bộ GD&ĐT công nhận."</p>
					<div class="flex items-center gap-3 pt-4 border-t border-slate-200">
						<div class="h-10 w-10 bg-brand-primary text-white rounded-full flex items-center justify-center font-bold text-sm">LP</div>
						<div>
							<p class="font-bold text-sm text-slate-800">Lê Phương</p>
							<p class="text-xs text-slate-400">Liên thông Đại học - Kế toán</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 8. DOUBLE CERTIFICATE VALUE PROPOSITION -->
	<section class="py-12 bg-white">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
			<!-- Box Left: BẰNG ĐẠO TẠO CHÍNH QUY -->
			<div class="bg-slate-50 border border-slate-100 rounded-lg p-6 md:p-8 flex flex-col md:flex-row justify-between gap-6">
				<div class="flex-1 space-y-4">
					<span class="bg-[#EFF6FF] text-[#2563EB] text-sm font-black px-3 py-1 rounded-lg uppercase tracking-wider inline-block">BẰNG CẤP TƯƠNG ĐƯƠNG</span>
					<h3 class="font-extrabold text-xl text-slate-900 leading-tight">BẰNG ĐẠO TẠO CHÍNH QUY</h3>
					<ul class="space-y-2 text-sm text-slate-600">
						<li>✔ Học chương trình chuẩn theo quy định của bộ GD&ĐT.</li>
						<li>✔ Đảm bảo giá trị pháp lý, sử dụng toàn quốc.</li>
						<li>✔ Phục vụ học tập, thi công chức, nâng lương nâng bậc.</li>
						<li>✔ Hồ sơ nhanh gọn - Quy trình rõ ràng.</li>
					</ul>
					<a href="#register-section" class="inline-block bg-[#2563EB] text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-[#1E40AF] transition-all pt-2">Tìm hiểu thêm</a>
				</div>
				<div class="w-full md:w-36 shrink-0 aspect-[3/4] bg-white border border-slate-100 rounded-lg p-2 flex items-center justify-center shadow-inner">
					<div class="text-center leading-none">
						<span class="text-4xl block mb-2">📜</span>
						<span class="text-sm text-slate-400 block uppercase font-bold">BẰNG ĐẠI HỌC</span>
					</div>
				</div>
			</div>

			<!-- Box Right: SUỐT ĐỜI TRÊN TOÀN QUỐC -->
			<div class="bg-slate-50 border border-slate-100 rounded-lg p-6 md:p-8 flex flex-col md:flex-row justify-between gap-6">
				<div class="flex-1 space-y-4">
					<span class="bg-blue-50 text-blue-800 text-sm font-black px-3 py-1 rounded-lg uppercase tracking-wider inline-block">BẰNG CẤP TƯƠNG ĐƯƠNG</span>
					<h3 class="font-extrabold text-xl text-slate-900 leading-tight">SUỐT ĐỜI TRÊN TOÀN QUỐC</h3>
					<ul class="space-y-2 text-sm text-slate-600">
						<li>✔ Bằng đại học sử dụng lâu dài, không giới hạn thời gian.</li>
						<li>✔ Cơ hội tiếp tục học lên cao học, thạc sĩ, tiến sĩ.</li>
						<li>✔ Hỗ trợ kết nối việc làm sau khi hoàn thành khóa học.</li>
					</ul>
					<a href="#register-section" class="inline-block bg-slate-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-slate-900 transition-all pt-2">Xem chi tiết</a>
				</div>
				<div class="w-full md:w-36 shrink-0 aspect-[3/4] bg-white border border-slate-100 rounded-lg p-2 flex items-center justify-center shadow-inner">
					<div class="text-center leading-none">
						<span class="text-4xl block mb-2">🛡</span>
						<span class="text-sm text-slate-400 block uppercase font-bold">CHỨNG CHỈ</span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 9. SEGMENTS SECTION -->
	<section class="py-16 bg-slate-50 border-y border-slate-100">
		<div class="max-w-7xl mx-auto relative">
			<div class="text-center max-w-2xl mx-auto mb-12 px-4">
				<h2 class="text-xl md:text-3xl font-black text-slate-900 mb-2">Chương trình phù hợp với bạn?</h2>
				<p class="text-slate-500 text-sm">Chúng tôi thiết kế các lộ trình học tối ưu riêng cho từng nhóm đối tượng cụ thể.</p>
			</div>

			<!-- Mobile: Horizontal scroll | Desktop: Grid -->
			<div class="flex md:grid md:grid-cols-5 gap-4 overflow-x-auto snap-x snap-mandatory px-4 pb-2 md:px-0 md:pb-0 scrollbar-hide" style="-webkit-overflow-scrolling: touch;">
				<!-- Segment 1 -->
				<div class="bg-white border border-slate-100 rounded-lg p-5 hover:border-brand-primary transition-all flex flex-col justify-between snap-start shrink-0 w-[200px] md:w-auto">
					<div class="space-y-2">
						<div class="h-32 bg-slate-100 rounded-lg overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&q=80&w=200');"></div>
						<h4 class="font-bold text-slate-800 text-xs">Cho người đi làm</h4>
						<p class="text-[11px] text-slate-500 leading-normal">Lịch học linh hoạt, học trực tuyến 100% không làm gián đoạn công việc bận rộn hàng ngày.</p>
					</div>
				</div>

				<!-- Segment 2 -->
				<div class="bg-white border border-slate-100 rounded-lg p-5 hover:border-brand-primary transition-all flex flex-col justify-between snap-start shrink-0 w-[200px] md:w-auto">
					<div class="space-y-2">
						<div class="h-32 bg-slate-100 rounded-lg overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?auto=format&fit=crop&q=80&w=200');"></div>
						<h4 class="font-bold text-slate-800 text-xs">Cho học sinh lớp 12</h4>
						<p class="text-[11px] text-slate-500 leading-normal">Định hướng nghề nghiệp sớm, đăng ký lộ trình học liên kết đại học - mở ra tương lai rộng mở.</p>
					</div>
				</div>

				<!-- Segment 3 -->
				<div class="bg-white border border-slate-100 rounded-lg p-5 hover:border-brand-primary transition-all flex flex-col justify-between snap-start shrink-0 w-[200px] md:w-auto">
					<div class="space-y-2">
						<div class="h-32 bg-slate-100 rounded-lg overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&q=80&w=200');"></div>
						<h4 class="font-bold text-slate-800 text-xs">Cho người muốn liên thông</h4>
						<p class="text-[11px] text-slate-500 leading-normal">Từ Trung cấp/Cao đẳng lên Đại học. Rút ngắn thời gian đào tạo và tối ưu hóa chi phí học tập.</p>
					</div>
				</div>

				<!-- Segment 4 -->
				<div class="bg-white border border-slate-100 rounded-lg p-5 hover:border-brand-primary transition-all flex flex-col justify-between snap-start shrink-0 w-[200px] md:w-auto">
					<div class="space-y-2">
						<div class="h-32 bg-slate-100 rounded-lg overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&q=80&w=200');"></div>
						<h4 class="font-bold text-slate-800 text-xs">Cho người muốn chuyển ngành</h4>
						<p class="text-[11px] text-slate-500 leading-normal">Đón đầu xu hướng chuyển dịch lao động sang các ngành công nghệ, dịch vụ hot nhất hiện nay.</p>
					</div>
				</div>

				<!-- Segment 5 -->
				<div class="bg-white border border-slate-100 rounded-lg p-5 hover:border-brand-primary transition-all flex flex-col justify-between snap-start shrink-0 w-[200px] md:w-auto">
					<div class="space-y-2">
						<div class="h-32 bg-slate-100 rounded-lg overflow-hidden bg-cover bg-center mb-3" style="background-image: url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=200');"></div>
						<h4 class="font-bold text-slate-800 text-xs">Cho người học văn bằng 2</h4>
						<p class="text-[11px] text-slate-500 leading-normal">Mở rộng kiến thức đa lĩnh vực, gấp đôi cơ hội ứng tuyển và nâng cao giá trị thương hiệu cá nhân.</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 10. DYNAMIC CONSULTATION FORM SECTION -->
	<section id="register-section" class="py-16 bg-white">
		<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 border border-slate-100 shadow-xl rounded-xl p-6 md:p-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center bg-gradient-to-tr from-[#EFF6FF]/35 to-white">
			<!-- Graduate photo column -->
			<div class="lg:col-span-5 hidden lg:block">
				<div class="relative w-full aspect-[4/5] rounded-xl overflow-hidden shadow-md">
					<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c476?auto=format&fit=crop&q=80&w=400');"></div>
					<div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
					<div class="absolute bottom-6 left-6 right-6 text-white text-left">
						<p class="font-display font-black text-lg leading-tight">Mở cánh cửa tương lai</p>
						<p class="text-xs text-slate-200 mt-1">Đăng ký ngay hôm nay để nhận thông tin tư vấn chính xác nhất.</p>
					</div>
				</div>
			</div>

			<!-- Dynamic contact form column -->
			<div class="lg:col-span-7 space-y-6">
				<div class="text-left space-y-2">
					<span class="text-brand-primary text-xs font-extrabold uppercase tracking-widest block">Đăng ký trực tuyến</span>
					<h3 class="text-2xl md:text-3xl font-black text-slate-900">Nhận tư vấn miễn phí</h3>
					<p class="text-slate-500 text-sm">Vui lòng cung cấp thông tin liên hệ, đội ngũ tuyển sinh của chúng tôi sẽ chủ động gọi lại hỗ trợ giải đáp lộ trình cho bạn sớm nhất.</p>
				</div>
				
				<?php 
				if ( class_exists( 'WPCF7_ContactForm' ) ) :
					echo do_shortcode( '[contact-form-7 id="f3902eb" title="Form Tư vấn"]' );
				else :
				?>
					<form action="#" method="POST" class="space-y-4">
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<label class="block text-sm font-bold text-slate-600 mb-1">Họ và tên *</label>
								<input type="text" name="your-name" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Họ và tên của bạn">
							</div>
							<div>
								<label class="block text-sm font-bold text-slate-600 mb-1">Số điện thoại *</label>
								<input type="tel" name="your-phone" required class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Số điện thoại liên hệ">
							</div>
						</div>
						<div>
							<label class="block text-sm font-bold text-slate-600 mb-1">Địa chỉ email</label>
							<input type="email" name="your-email" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Địa chỉ email của bạn">
						</div>
						<div>
							<label class="block text-sm font-bold text-slate-600 mb-1">Chương trình quan tâm</label>
							<select name="current_program_id" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none">
								<option value="">-- Chọn ngành hoặc hệ học --</option>
								<?php
								$programs = get_posts( [ 'post_type' => 'program', 'numberposts' => -1 ] );
								foreach ( $programs as $p ) {
									echo '<option value="' . esc_attr( $p->ID ) . '">' . esc_html( $p->post_title ) . '</option>';
								}
								?>
							</select>
						</div>
						<div>
							<label class="block text-sm font-bold text-slate-600 mb-1">Nội dung cần tư vấn</label>
							<textarea name="your-message" rows="3" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:border-brand-primary focus:outline-none" placeholder="Nhập câu hỏi hoặc yêu cầu cụ thể của bạn..."></textarea>
						</div>
						
						<div class="flex flex-col sm:flex-row items-center gap-4 pt-2">
							<button type="submit" class="w-full sm:w-auto bg-[#2563EB] text-white px-8 py-3.5 rounded-lg font-bold text-sm hover:bg-[#1E40AF] transition-all shadow-md shadow-brand-primary/20">
								GỬI THÔNG TIN
							</button>
							<span class="text-sm text-slate-400 font-medium text-center sm:text-left">Cam kết bảo mật thông tin tuyệt đối và chỉ sử dụng cho mục đích tư vấn tuyển sinh.</span>
						</div>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- 11. NEWS RECENT SECTION -->
	<section class="py-16 bg-slate-50 border-t border-slate-100">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex justify-between items-end mb-12">
				<div class="space-y-2">
					<span class="text-[#2563EB] text-sm font-bold uppercase tracking-wider block">THÔNG TIN TUYỂN SINH</span>
					<h2 class="text-2xl md:text-3xl font-black text-slate-900">Tin tức mới nhất</h2>
				</div>
				<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" class="text-sm text-[#2563EB] font-bold hover:underline flex items-center gap-1">
					Xem tất cả
					<span>→</span>
				</a>
			</div>

			<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-6">
				<?php 
				$mock_news = [
					[ 'title' => 'Tuyển sinh Đại học Từ xa khóa mới nhất', 'date' => '10/07/2026', 'desc' => 'Thông tin chi tiết các ngành đào tạo từ xa hệ Đại học được bộ GD&ĐT công nhận tốt nghiệp chính quy.' ],
					[ 'title' => 'Điều kiện học Văn bằng 2 đại học năm 2026', 'date' => '08/07/2026', 'desc' => 'Giải đáp những thắc mắc thường gặp về điều kiện tuyển sinh học văn bằng 2 cho học viên tốt nghiệp các ngành.' ],
					[ 'title' => 'Quy chế tuyển sinh Liên thông Cao đẳng lên Đại học', 'date' => '05/07/2026', 'desc' => 'Quy định rút ngắn chương trình đào tạo khi thi liên thông và các hồ sơ chuẩn bị nhập học.' ],
					[ 'title' => 'Học đại học vừa học vừa làm có giá trị như thế nào?', 'date' => '02/07/2026', 'desc' => 'Giá trị pháp lý của tấm bằng đại học vừa học vừa làm đối với cơ hội thăng tiến nghề nghiệp.' ],
				];

				$news_has_posts = false;
				if ( $news_query->have_posts() ) {
					$news_has_posts = true;
					$index = 0;
					while ( $news_query->have_posts() ) : $news_query->the_post();
						$post_id = get_the_ID();
						$thumb_url = get_the_post_thumbnail_url( $post_id, 'medium' ) ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=250';
						$categories = get_the_category( $post_id );
						$category_name = ! empty( $categories ) ? $categories[0]->name : 'Tin tuyển sinh';
				?>
						<div class="bg-white border border-slate-100 rounded-lg overflow-hidden hover:shadow-md transition-all flex flex-col justify-between">
							<div class="h-36 bg-slate-200 bg-cover bg-center" style="background-image: url('<?php echo esc_url( $thumb_url ); ?>');"></div>
							<div class="p-4 flex-1 flex flex-col justify-between">
								<div>
									<span class="text-[10px] font-bold text-brand-primary uppercase tracking-wider block mb-1"><?php echo esc_html( $category_name ); ?></span>
									<h4 class="font-extrabold text-slate-800 text-xs sm:text-sm line-clamp-2 hover:text-[#2563EB] transition-colors leading-snug">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h4>
								</div>
								<time class="text-[10px] text-slate-400 mt-3 block"><?php echo get_the_date( 'd/m/Y' ); ?></time>
							</div>
						</div>
				<?php 
						$index++;
					endwhile;
					wp_reset_postdata();
				}
				
				if ( ! $news_has_posts ) {
					foreach ( $mock_news as $mn ) {
				?>
						<div class="bg-white border border-slate-100 rounded-lg p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
							<div>
								<span class="text-[10px] font-bold text-brand-primary uppercase tracking-wider block mb-1">Hướng dẫn</span>
								<h4 class="font-bold text-slate-800 text-xs sm:text-sm line-clamp-2 leading-snug"><?php echo esc_html( $mn['title'] ); ?></h4>
								<p class="text-[11px] text-slate-400 leading-normal line-clamp-3 mt-2"><?php echo esc_html( $mn['desc'] ); ?></p>
							</div>
							<time class="text-[10px] text-slate-400 mt-3 block"><?php echo esc_html( $mn['date'] ); ?></time>
						</div>
				<?php
					}
				}
				?>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
