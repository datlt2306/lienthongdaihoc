<?php
/**
 * Home: Value Proposition & Certificate Information Section
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$c_left_badge    = '';
$c_left_heading  = '';
$c_slider_items  = [];
$c_left_image    = '';
$c_left_youtube  = '';
$c_right_image   = '';
$c_right_content = '';

if ( function_exists( 'get_field' ) ) {
	$c_left_badge    = get_field( 'hp_cert_left_badge', 'options' ) ?: '';
	$c_left_heading  = get_field( 'hp_cert_left_heading', 'options' ) ?: '';
	$c_slider_items  = get_field( 'hp_cert_slider', 'options' ) ?: [];
	$c_left_image    = get_field( 'hp_cert_left_image', 'options' ) ?: '';
	$c_left_youtube  = get_field( 'hp_cert_left_youtube', 'options' ) ?: '';
	$c_right_image   = get_field( 'hp_cert_right_image', 'options' ) ?: '';
	$c_right_content = get_field( 'hp_cert_right_content', 'options' ) ?: '';
}
?>

<!-- 8. DOUBLE CERTIFICATE VALUE PROPOSITION -->
<section class="py-10 sm:py-16 md:py-20 bg-white overflow-hidden">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 sm:space-y-16 lg:space-y-24">
		
		<!-- Row 1: [Text Content] [Slider] -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 lg:gap-14 lg:items-stretch items-center">
			<!-- Left column: Text Content -->
			<div class="space-y-4 sm:space-y-6 flex flex-col justify-center">
				<span class="inline-block bg-[#0071E3]/10 text-[#0071E3] text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider self-start"><?php echo esc_html( $c_left_badge ?: 'BẰNG CẤP TƯƠNG ĐƯƠNG' ); ?></span>
				
				<h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-[#1D1D1F] tracking-tight leading-tight">
					<?php 
					$heading = $c_left_heading ?: 'Không ghi hình thức đào tạo';
					$words   = explode( ' ', $heading );
					if ( count( $words ) > 2 ) {
						$last_words  = implode( ' ', array_slice( $words, -3 ) );
						$first_words = implode( ' ', array_slice( $words, 0, count( $words ) - 3 ) );
						echo esc_html( $first_words ) . ' <span class="text-[#0071E3] inline">' . esc_html( $last_words ) . '</span>';
					} else {
						echo esc_html( $heading );
					}
					?>
				</h2>

				<div class="space-y-2 sm:space-y-3 text-sm sm:text-base leading-relaxed">
					<p>Từ ngày <strong>01/03/2020</strong>, bằng tốt nghiệp đại học sẽ không còn phân biệt hình thức đào tạo như chính quy, tại chức, từ xa...</p>
					<p>Theo <strong>Thông tư 27/2019/TT-BGDĐT</strong>, trên văn bằng sẽ vẫn ghi các nội dung cũ nhưng không ghi thông tin về hình thức đào tạo.</p>
				</div>

				<div class="pt-4 border-t border-black/[0.08]">
					<h3 class="font-semibold text-[#1D1D1F] text-base sm:text-lg mb-3">Có Giá Trị Sử Dụng Suốt Đời Trên Toàn Quốc</h3>
					
					<div class="space-y-3">
						<div class="flex items-start gap-3">
							<div class="w-5 h-5 rounded-full bg-[#0071E3]/10 text-[#0071E3] flex items-center justify-center shrink-0 mt-0.5">
								<svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.5">
									<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
								</svg>
							</div>
							<div>
								<h4 class="font-semibold text-[#1D1D1F] text-sm sm:text-base leading-snug">Bằng Cử nhân / Kỹ sư chính thức</h4>
								<p class="text-[#86868B] text-sm sm:text-base leading-relaxed mt-0.5">Sau khi hoàn thành chương trình, học viên sẽ được trường Đại học cấp bằng Cử nhân/Kỹ sư theo quy định của Bộ GD&ĐT, có giá trị pháp lý trên toàn quốc.</p>
							</div>
						</div>

						<div class="flex items-start gap-3">
							<div class="w-5 h-5 rounded-full bg-[#0071E3]/10 text-[#0071E3] flex items-center justify-center shrink-0 mt-0.5">
								<svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.5">
									<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
								</svg>
							</div>
							<div>
								<h4 class="font-semibold text-[#1D1D1F] text-sm sm:text-base leading-snug">Có Thể Dùng Để Học Lên Các Bậc Cao Hơn</h4>
								<p class="text-[#86868B] text-sm sm:text-base leading-relaxed mt-0.5">Thi công chức nhà nước, xét nâng bậc lương, đi du học.</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Right column: Slider Wrapper -->
			<div class="relative flex justify-center w-full lg:h-full">
				<style>
					.cert-slider-wrapper {
						position: relative;
						width: 100%;
						max-width: 32rem; /* max-w-lg */
						aspect-ratio: 1.4; /* Perfect aspect ratio for certificate documents */
						border-radius: 1.75rem; /* rounded-3xl */
						overflow: hidden;
						box-shadow: 0 20px 48px -15px rgba(0, 0, 0, 0.08);
						background-color: #ffffff;
						border: 1px solid rgba(0, 0, 0, 0.08);
					}
					@media (max-width: 640px) {
						.cert-slider-wrapper {
							border-radius: 1.25rem;
							box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06);
						}
					}
					@media (min-width: 1024px) {
						.cert-slider-wrapper {
							max-width: 100%;
							width: 100%;
							height: 100%;
							min-height: 100%;
							aspect-ratio: auto;
						}
					}
					.cert-swiper {
						width: 100%;
						height: 100%;
					}
					.cert-swiper .swiper-wrapper {
						height: 100%;
					}
					.cert-swiper .swiper-slide {
						height: 100%;
						width: 100%;
					}
					.cert-swiper .swiper-slide img {
						width: 100%;
						height: 100%;
						object-fit: cover;
						object-position: center;
						background-color: #ffffff;
					}
					.cert-swiper .swiper-pagination-bullet {
						background: rgba(0, 0, 0, 0.2) !important;
						opacity: 1 !important;
						width: 8px;
						height: 8px;
						transition: all 0.3s ease;
					}
					.cert-swiper .swiper-pagination-bullet-active {
						background: #0071E3 !important;
						width: 20px;
						border-radius: 9999px;
					}
				</style>
				<div class="cert-slider-wrapper">
					<!-- Slider Container -->
					<div class="swiper cert-swiper">
						<div class="swiper-wrapper">
							<?php 
							$slides = [];
							if ( ! empty( $c_slider_items ) ) {
								foreach ( $c_slider_items as $item ) {
									if ( ! empty( $item['image'] ) ) {
										$img_val = $item['image'];
										if ( is_numeric( $img_val ) && function_exists( 'wp_get_attachment_url' ) ) {
											$slides[] = wp_get_attachment_url( $img_val );
										} elseif ( is_array( $img_val ) && isset( $img_val['url'] ) ) {
											$slides[] = $img_val['url'];
										} else {
											$slides[] = $img_val;
										}
									}
								}
							}
							
							if ( empty( $slides ) ) {
								$slides[] = home_url( 'wp-content/uploads/2026/07/students-graduation.jpg' );
							}
							
							foreach ( $slides as $index => $slide_url ) :
								$opt_slide_url = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $slide_url ) : $slide_url;
							?>
								<div class="swiper-slide w-full h-full">
									<img src="<?php echo esc_url( $opt_slide_url ); ?>" alt="Students Graduation Slide" width="640" height="468" loading="lazy" decoding="async" class="w-full h-full object-cover">
								</div>
							<?php endforeach; ?>
						</div>

						<!-- Swiper Pagination -->
						<?php if ( count( $slides ) > 1 ) : ?>
							<div class="swiper-pagination !bottom-2.5 !right-2.5 sm:!bottom-5 sm:!right-5 !left-auto !w-auto flex gap-1.5 sm:gap-2"></div>
						<?php endif; ?>
					</div>

					<!-- Orange badge (Apple Ember Pill Pod) -->
					<div class="absolute bottom-2.5 left-2.5 sm:bottom-5 sm:left-5 bg-[#FF9500] text-white p-3 sm:p-4 rounded-2xl shadow-lg flex flex-col justify-center max-w-[125px] sm:max-w-[160px] z-20 pointer-events-none">
						<span class="text-xl sm:text-2xl md:text-3xl font-bold leading-none">100%</span>
						<span class="text-[9px] sm:text-xs font-semibold tracking-wider uppercase mt-1 leading-tight">VĂN BẰNG CHUẨN<br>BỘ GD&ĐT</span>
					</div>
				</div>
			</div>
		</div>

		<!-- Row 2: [Video Card] [Text Content] -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 lg:gap-14 lg:items-stretch items-center">
			<!-- Video Card -->
			<div class="w-full rounded-2xl sm:rounded-3xl relative overflow-hidden border border-black/[0.08] shadow-[0_16px_40px_rgba(0,0,0,0.06)] aspect-video lg:aspect-auto lg:h-full lg:min-h-full group bg-[#161617] flex items-center justify-center order-2 lg:order-1 <?php echo ! empty( $c_left_youtube ) ? 'cursor-pointer' : ''; ?>" <?php echo ! empty( $c_left_youtube ) ? 'data-youtube="' . esc_url( $c_left_youtube ) . '"' : ''; ?>>
				<?php
				$video_raw_thumb = $c_left_image ?: home_url( 'wp-content/uploads/2026/07/vtv-news-thumb.png' );
				$video_opt_thumb = function_exists( 'ltdh_get_optimized_image_url' ) ? ltdh_get_optimized_image_url( $video_raw_thumb ) : $video_raw_thumb;
				?>
				<img src="<?php echo esc_url( $video_opt_thumb ); ?>" alt="VTV24 Video Thumbnail" loading="lazy" decoding="async" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
				
				<?php if ( ! empty( $c_left_youtube ) ) : ?>
					<!-- Play button overlay (Cupertino Frosted Pill) -->
					<div class="absolute inset-0 bg-black/20 flex items-center justify-center">
						<div class="w-14 h-14 sm:w-16 sm:h-16 bg-white/90 backdrop-blur-md text-[#1D1D1F] rounded-full flex items-center justify-center shadow-2xl group-hover:scale-110 active:scale-95 transition-transform duration-300">
							<svg class="w-6 h-6 sm:w-7 sm:h-7 fill-current translate-x-0.5 text-[#0071E3]" viewBox="0 0 24 24">
								<path d="M8 5v14l11-7z"/>
							</svg>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<!-- Text Content -->
			<div class="space-y-4 sm:space-y-6 order-1 lg:order-2 flex flex-col justify-center">
				<?php if ( ! empty( $c_right_content ) && function_exists( 'wp_kses_post' ) ) : ?>
					<div class="prose max-w-none text-[#6E6E73] text-sm sm:text-base leading-relaxed">
						<?php echo wp_kses_post( $c_right_content ); ?>
					</div>
				<?php else : ?>
					<span class="inline-block bg-[#0071E3]/10 text-[#0071E3] text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider self-start">
						Bạn có thể sử dụng văn bằng để làm gì?
					</span>
					
					<h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-[#1D1D1F] tracking-tight leading-tight">
						<?php 
						$right_heading     = 'Văn bằng sau tốt nghiệp';
						$words_right       = explode( ' ', $right_heading );
						if ( count( $words_right ) > 2 ) {
							$last_words_right  = implode( ' ', array_slice( $words_right, -3 ) );
							$first_words_right = implode( ' ', array_slice( $words_right, 0, count( $words_right ) - 3 ) );
							echo esc_html( $first_words_right ) . ' <span class="text-[#0071E3] inline">' . esc_html( $last_words_right ) . '</span>';
						} else {
							echo esc_html( $right_heading );
						}
						?>
					</h2>

					<div class="space-y-2 sm:space-y-3 text-sm sm:text-base leading-relaxed">
						<p>Sau khi tốt nghiệp, người học được cấp văn bằng theo quy định hiện hành và có thể sử dụng để phục vụ các mục tiêu học tập, nghề nghiệp theo điều kiện của từng đơn vị tiếp nhận.</p>
					</div>

					<div class="pt-4 border-t border-black/[0.08]">
						<h3 class="font-semibold text-[#1D1D1F] text-base sm:text-lg mb-3">Giá trị sử dụng của văn bằng</h3>
						
						<div class="space-y-3">
							<div class="flex items-start gap-3">
								<div class="w-5 h-5 rounded-full bg-[#0071E3]/10 text-[#0071E3] flex items-center justify-center shrink-0 mt-0.5">
									<svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.5">
										<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
									</svg>
								</div>
								<div>
									<h4 class="font-semibold text-[#1D1D1F] text-sm sm:text-base leading-snug">Học tiếp lên trình độ cao hơn</h4>
									<p class="text-[#86868B] text-sm sm:text-base leading-relaxed mt-0.5">Có thể đăng ký dự tuyển chương trình sau đại học khi đáp ứng điều kiện tuyển sinh.</p>
								</div>
							</div>

							<div class="flex items-start gap-3">
								<div class="w-5 h-5 rounded-full bg-[#0071E3]/10 text-[#0071E3] flex items-center justify-center shrink-0 mt-0.5">
									<svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.5">
										<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
									</svg>
								</div>
								<div>
									<h4 class="font-semibold text-[#1D1D1F] text-sm sm:text-base leading-snug">Bổ sung và hoàn thiện hồ sơ nghề nghiệp</h4>
									<p class="text-[#86868B] text-sm sm:text-base leading-relaxed mt-0.5">Phục vụ yêu cầu về trình độ chuyên môn đối với vị trí việc làm phù hợp.</p>
								</div>
							</div>

							<div class="flex items-start gap-3">
								<div class="w-5 h-5 rounded-full bg-[#0071E3]/10 text-[#0071E3] flex items-center justify-center shrink-0 mt-0.5">
									<svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.5">
										<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
									</svg>
								</div>
								<div>
									<h4 class="font-semibold text-[#1D1D1F] text-sm sm:text-base leading-snug">Tham gia tuyển dụng, thi tuyển</h4>
									<p class="text-[#86868B] text-sm sm:text-base leading-relaxed mt-0.5">Sử dụng văn bằng trong hồ sơ dự tuyển theo yêu cầu cụ thể của cơ quan, đơn vị tuyển dụng.</p>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
		
	</div>
</section>

<!-- YouTube Video Popup Modal -->
<div id="youtube-video-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/80 p-4 transition-all duration-300 opacity-0">
	<div class="relative w-full max-w-4xl bg-black rounded-lg overflow-hidden shadow-2xl">
		<!-- Close Button -->
		<button id="close-video-modal" class="absolute top-3 right-3 text-white/80 hover:text-white bg-slate-800/50 hover:bg-slate-700 rounded-full p-2 focus:outline-none transition-colors z-50">
			<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
			</svg>
		</button>
		<!-- Video Container -->
		<div class="aspect-video w-full">
			<iframe id="modal-video-iframe" class="w-full h-full" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</div>
	</div>
</div>

<script>
	function initFrontPageInteractive() {
		const modal = document.getElementById('youtube-video-modal');
		const iframe = document.getElementById('modal-video-iframe');
		const closeBtn = document.getElementById('close-video-modal');
		const triggerElements = document.querySelectorAll('[data-youtube]');

		function getYoutubeId(url) {
			const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
			const match = url.match(regExp);
			return (match && match[2].length === 11) ? match[2] : null;
		}

		triggerElements.forEach(elem => {
			elem.addEventListener('click', function() {
				const url = this.getAttribute('data-youtube');
				const videoId = getYoutubeId(url);
				if (videoId) {
					iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
					modal.classList.remove('hidden');
					modal.classList.add('flex');
					setTimeout(() => {
						modal.classList.remove('opacity-0');
						modal.classList.add('opacity-100');
					}, 50);
				}
			});
		});

		function closeModal() {
			modal.classList.remove('opacity-100');
			modal.classList.add('opacity-0');
			setTimeout(() => {
				modal.classList.remove('flex');
				modal.classList.add('hidden');
				iframe.src = '';
			}, 300);
		}

		if (closeBtn) {
			closeBtn.addEventListener('click', closeModal);
		}
		if (modal) {
			modal.addEventListener('click', function(e) {
				if (e.target === modal) {
					closeModal();
				}
			});
		}

		// Certificate Swiper Slider Logic
		let certAttempts = 0;
		const initCertSwiper = () => {
			if (typeof Swiper === 'undefined') {
				if (++certAttempts < 100) {
					setTimeout(initCertSwiper, certAttempts > 20 ? 250 : 100);
				}
				return;
			}
			if (document.querySelector('.cert-swiper')) {
				new Swiper('.cert-swiper', {
					loop: true,
					autoplay: {
						delay: 4000,
						disableOnInteraction: false,
					},
					pagination: {
						el: '.cert-swiper .swiper-pagination',
						clickable: true,
					},
					speed: 600,
				});
			}
		};

		window.addEventListener('ltdh:swiper:loaded', initCertSwiper, { once: true });

		if ('requestIdleCallback' in window) {
			requestIdleCallback(initCertSwiper, { timeout: 3000 });
		} else {
			setTimeout(initCertSwiper, 1000);
		}
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initFrontPageInteractive);
	} else {
		initFrontPageInteractive();
	}
</script>
