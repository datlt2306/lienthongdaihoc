<?php
/**
 * Template Name: Câu hỏi thường gặp
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hotline = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496';
$zalo    = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';

// Get FAQs from ACF Options
$faq_items = [];
if ( function_exists( 'get_field' ) ) {
	$faq_items = get_field( 'faq_items', 'options' ) ?: get_field( 'faq_items', 'option' ) ?: [];
}

// Fallback default FAQs if not yet configured in ACF
if ( empty( $faq_items ) || ! is_array( $faq_items ) ) {
	$faq_items = [
		[
			'question' => 'Bằng tốt nghiệp Đại học Liên thông / Từ xa có ghi hình thức đào tạo không?',
			'answer'   => 'Theo Thông tư 27/2019/TT-BGDĐT của Bộ Giáo dục và Đào tạo, từ ngày 01/03/2020 trên bằng tốt nghiệp Đại học không còn ghi hình thức đào tạo (như Từ xa hay Vừa học vừa làm). Tấm bằng do các trường đại học đối tác cấp có giá trị pháp lý tương đương bằng cử nhân/kỹ sư chính quy, được thi cao học, công chức và nâng bậc lương.',
		],
		[
			'question' => 'Thời gian đào tạo Liên thông Đại học mất bao lâu?',
			'answer'   => 'Thời gian đào tạo trung bình từ 1.5 đến 2 năm tùy theo khối ngành. Đặc biệt, học viên đã có bằng Trung cấp hoặc Cao đẳng đúng ngành sẽ được xét miễn giảm các học phần tương đương để rút ngắn thời gian hoàn thành chương trình.',
		],
		[
			'question' => 'Hình thức học trực tuyến (Online/Từ xa) diễn ra như thế nào?',
			'answer'   => 'Học viên học hoàn toàn qua hệ thống E-Learning trực tuyến của nhà trường. Bạn có thể tự chủ thời gian học mọi lúc mọi nơi qua bài giảng video, slide và tham gia trao đổi trực tiếp với giảng viên qua các buổi ôn tập online cuối tuần.',
		],
		[
			'question' => 'Hồ sơ xét tuyển Liên thông Đại học bao gồm những gì?',
			'answer'   => 'Hồ sơ xét tuyển cơ bản gồm: 01 Phiếu đăng ký theo mẫu của trường, 02 bản sao công chứng Bằng + Bảng điểm tốt nghiệp Cao đẳng/Trung cấp, 01 bản sao CCCD và 02 ảnh 3x4. Chuyên viên tuyển sinh sẽ hỗ trợ gửi mẫu và hướng dẫn chuẩn bị chi tiết.',
		],
		[
			'question' => 'Bằng Đại học Liên thông có đủ điều kiện đăng ký học Cao học / Thạc sĩ không?',
			'answer'   => 'Hoàn toàn đủ điều kiện. Tấm bằng đại học sau khi tốt nghiệp có đầy đủ tư cách pháp lý để bạn tiếp tục đăng ký thi tuyển Thạc sĩ, Cao học tại tất cả các trường Đại học trên toàn quốc hoặc du học nước ngoài.',
		],
	];
}
?>

<?php get_template_part( 'template-parts/banner' ); ?>

<div id="primary" class="content-area site-main py-12 md:py-16 bg-slate-50">
	<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

		<!-- FAQ ACCORDION LIST -->
		<div class="space-y-4">
			<?php
			$idx = 0;
			foreach ( $faq_items as $faq ) :
				$q_text = $faq['question'] ?? '';
				$a_text = $faq['answer'] ?? '';
				if ( empty( $q_text ) || empty( $a_text ) ) continue;
				$idx++;
			?>
				<details class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden group transition-all hover:border-slate-300 shadow-xs" <?php echo ( 1 === $idx ) ? 'open' : ''; ?> id="faq-<?php echo esc_attr( $idx ); ?>">
					<summary class="flex justify-between items-center font-bold text-slate-900 p-5 md:p-6 cursor-pointer list-none select-none text-base md:text-lg [&::-webkit-details-marker]:hidden gap-4 hover:bg-slate-50/60 transition-colors">
						<span class="flex items-center gap-3 md:gap-4">
							<span class="w-8 h-8 rounded-xl bg-blue-50 text-brand-primary font-black text-xs flex items-center justify-center shrink-0 border border-blue-100">
								Q<?php echo esc_html( $idx ); ?>
							</span>
							<span class="text-left font-display font-bold leading-snug text-slate-900"><?php echo esc_html( $q_text ); ?></span>
						</span>
						<span class="text-slate-400 group-open:rotate-180 transition-transform duration-300 shrink-0">
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
						</span>
					</summary>
					<div id="faq-<?php echo esc_attr( $idx ); ?>-answer" class="px-5 md:px-6 pb-6 pt-2 text-slate-600 text-sm md:text-base leading-relaxed border-t border-slate-100 bg-white">
						<div class="prose prose-slate max-w-none text-slate-600">
							<?php echo wp_kses_post( wpautop( $a_text ) ); ?>
						</div>
					</div>
				</details>
			<?php endforeach; ?>
		</div>

		<!-- CONTACT & ADVISORY CTA BLOCK -->
		<div class="mt-12 md:mt-16 bg-gradient-to-br from-brand-primary to-brand-darkBlue rounded-2xl p-6 md:p-10 text-white shadow-xl text-center relative overflow-hidden">
			<div class="absolute -right-16 -top-16 w-60 h-60 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
			<div class="relative z-10 max-w-2xl mx-auto space-y-4">
				<h2 class="text-xl md:text-2xl font-black font-display tracking-tight text-white">
					Bạn vẫn còn câu hỏi cần giải đáp chi tiết?
				</h2>
				<p class="text-blue-100 text-sm md:text-base leading-relaxed">
					Đội ngũ ban tuyển sinh của chúng tôi sẵn sàng hỗ trợ tư vấn miễn phí về hồ sơ, quy trình xét miễn giảm tín chỉ và mức học phí phù hợp cho bạn.
				</p>
				<div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
					<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-brand-accent hover:bg-[#e06e00] text-white font-extrabold text-sm px-6 py-3.5 rounded-xl shadow-lg transition-transform hover:-translate-y-0.5 min-h-[48px]">
						<span>Đăng ký nhận tư vấn ngay</span>
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
					</a>
					<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-extrabold text-sm px-6 py-3.5 rounded-xl transition-colors min-h-[48px]">
						<svg class="w-4 h-4 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
						<span>Hotline: <?php echo esc_html( $hotline ); ?></span>
					</a>
				</div>
			</div>
		</div>

	</div>
</div><!-- #primary -->

<?php
get_footer();
