<?php
/**
 * Theme footer template
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hotline = get_field( 'global_hotline', 'options' ) ?: '0901234567';
$zalo    = get_field( 'global_zalo_url', 'options' ) ?: 'https://zalo.me';
$messenger = get_field( 'global_messenger_url', 'options' ) ?: 'https://m.me';
?>

	<footer id="colophon" class="site-footer bg-slate-900 text-slate-400 pt-16 pb-28 border-t border-slate-800">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
			<!-- About / Info -->
			<div class="md:col-span-2">
				<?php
				$footer_logo = ltdh_get_logo_url();
				if ( $footer_logo ) :
				?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-block mb-4">
						<img src="<?php echo esc_url( $footer_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="h-auto object-contain" style="max-height: 48px; width: auto; filter: brightness(0) invert(1);">
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-2 font-display font-extrabold text-2xl text-white mb-4">
						<div class="flex flex-col leading-none">
							<span class="text-sm font-semibold text-slate-400 tracking-wider">LIÊN THÔNG</span>
							<span class="text-xl font-extrabold text-brand-primary">ĐẠI HỌC</span>
						</div>
					</a>
				<?php endif; ?>
				<p class="text-sm text-slate-400 mb-4 max-w-sm">
					Cổng thông tin tuyển sinh đại học trực tuyến, liên thông, văn bằng 2 và vừa học vừa làm hàng đầu Việt Nam. Kết nối người học với các chương trình chất lượng từ hơn 50 trường đại học top đầu.
				</p>
				<p class="text-sm font-semibold text-white">Hotline hỗ trợ: <?php echo esc_html( $hotline ); ?></p>
			</div>

			<!-- Quick Links -->
			<div>
				<h3 class="font-display font-semibold text-white mb-4 text-lg">Hệ đào tạo</h3>
				<?php
				$training_types = get_terms( [ 'taxonomy' => 'training_type', 'hide_empty' => false ] );
				if ( ! is_wp_error( $training_types ) && ! empty( $training_types ) ) :
					echo '<ul class="space-y-2 text-sm">';
					foreach ( $training_types as $term ) :
						echo '<li><a href="' . esc_url( get_term_link( $term ) ) . '" class="hover:text-brand-primary transition-colors">' . esc_html( $term->name ) . '</a></li>';
					endforeach;
					echo '</ul>';
				endif;
				?>
			</div>

			<!-- Pages -->
			<div>
				<h3 class="font-display font-semibold text-white mb-4 text-lg">Liên kết nhanh</h3>
				<?php
				wp_nav_menu( [
					'theme_location' => 'footer-menu',
					'container'      => false,
					'menu_class'     => 'space-y-2 text-sm flex flex-col',
					'fallback_cb'    => 'ltdh_default_footer_menu',
				] );
				?>
			</div>
		</div>

		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-slate-800 text-center text-sm text-slate-500">
			<p>© <?php echo date( 'Y' ); ?> lienthongdaihoc.com. Tất cả quyền được bảo lưu. Bản quyền thuộc về Cổng thông tin Tuyển sinh liên kết Đại học.</p>
		</div>
	</footer>
</div><!-- #page -->

<!-- Floating/Sticky CTAs for Mobile Conversion -->
<div class="fixed bottom-4 left-4 z-40 flex flex-col gap-2.5 md:bottom-6 md:left-6">
	<!-- Phone Hotline Float -->
	<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="flex h-12 w-12 items-center justify-center rounded-lg bg-brand-primary text-white shadow-lg hover:bg-teal-700 transition-all hover:scale-105" aria-label="Gọi ngay">
		<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
		</svg>
	</a>

	<!-- Zalo OA Float -->
	<a href="<?php echo esc_url( $zalo ); ?>" target="_blank" rel="noopener noreferrer" class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-500 text-white shadow-lg hover:bg-blue-600 transition-all hover:scale-105" aria-label="Chat Zalo">
		<span class="font-display font-black text-sm">Zalo</span>
	</a>
</div>

<script>
	<?php
	$combos_query = new WP_Query( [
		'post_type'      => 'program',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	] );
	$combos = [];
	if ( $combos_query->have_posts() ) {
		while ( $combos_query->have_posts() ) {
			$combos_query->the_post();
			$pid = get_the_ID();
			$s_rel = get_field( 'school_relationship', $pid );
			$m_rel = get_field( 'major_relationship', $pid );
			
			$s_id = 0;
			if ( $s_rel ) {
				if ( is_numeric( $s_rel ) ) {
					$s_id = intval( $s_rel );
				} elseif ( is_object( $s_rel ) && isset( $s_rel->ID ) ) {
					$s_id = $s_rel->ID;
				} elseif ( is_array( $s_rel ) && ! empty( $s_rel ) ) {
					$first = reset( $s_rel );
					$s_id = is_object( $first ) ? $first->ID : intval( $first );
				}
			}

			$m_id = 0;
			if ( $m_rel ) {
				if ( is_numeric( $m_rel ) ) {
					$m_id = intval( $m_rel );
				} elseif ( is_object( $m_rel ) && isset( $m_rel->ID ) ) {
					$m_id = $m_rel->ID;
				} elseif ( is_array( $m_rel ) && ! empty( $m_rel ) ) {
					$first = reset( $m_rel );
					$m_id = is_object( $first ) ? $first->ID : intval( $first );
				}
			}

			$t_terms = wp_get_post_terms( $pid, 'training_type' );
			$t_slug = ( ! is_wp_error( $t_terms ) && ! empty( $t_terms ) ) ? $t_terms[0]->slug : '';
			if ( $s_id && $m_id ) {
				$combos[] = [
					'school' => (string) $s_id,
					'major'  => (string) $m_id,
					'type'   => (string) $t_slug,
				];
			}
		}
		wp_reset_postdata();
	}
	?>
	window.ltdh_combinations = <?php echo json_encode( $combos ); ?>;
</script>

<?php wp_footer(); ?>
</body>
</html>
