<?php
/**
 * 404 Error Page Template (BlankSlate role="alert" Standard)
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<article id="post-0" class="post not-found min-h-[calc(100dvh-15rem)] bg-gradient-to-b from-blue-50/50 via-white to-slate-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" role="alert">
	<div class="w-full max-w-2xl mx-auto text-center space-y-6 sm:space-y-8">
		
		<!-- Status Badge -->
		<div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-100/70 border border-blue-200/60 text-brand-primary text-xs sm:text-sm font-semibold tracking-wide">
			<span class="w-2 h-2 rounded-full bg-brand-accent animate-pulse"></span>
			<span>Lỗi 404: Không tìm thấy lộ trình liên thông</span>
		</div>

		<!-- Degree Motif & Big 404 -->
		<div class="relative inline-block select-none">
			<h1 class="entry-title text-8xl sm:text-9xl font-black font-display tracking-tight text-slate-900 leading-none" itemprop="name">
				4<span class="text-brand-accent inline-block transition-transform hover:rotate-12 duration-300">0</span>4
			</h1>
			<div class="absolute -top-3 right-3 sm:right-6 transform -rotate-12 pointer-events-none text-brand-primary drop-shadow-md">
				<svg class="w-12 h-12 sm:w-16 sm:h-16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
					<path d="M11.7 2.1c.2-.1.4-.1.6 0l10 5a1 1 0 0 1 0 1.8l-10 5a1 1 0 0 1-.6 0l-10-5a1 1 0 0 1 0-1.8l10-5Z"/>
					<path d="M4 11.5v4.8c0 2.4 3.6 4.2 8 4.2s8-1.8 8-4.2v-4.8l-7.4 3.7a1.5 1.5 0 0 1-1.2 0L4 11.5Z"/>
					<path d="M22 10v6a1 1 0 1 1-2 0v-5l2-1Z"/>
				</svg>
			</div>
		</div>

		<!-- Explanatory Messaging -->
		<div class="entry-content space-y-3" itemprop="mainContentOfPage">
			<h2 class="text-2xl sm:text-3xl font-bold font-display text-slate-900 tracking-tight">
				Lạc mất lộ trình liên thông rồi bạn ơi!
			</h2>
			<p class="text-slate-600 text-sm sm:text-base max-w-lg mx-auto leading-relaxed">
				Ngành học hoặc thông báo tuyển sinh này có thể đã được cập nhật hoặc đường link không chính xác. Đừng lo lắng, con đường nâng chuẩn bằng Cử nhân luôn có lối đi rõ ràng ngay bên dưới.
			</p>
		</div>

		<!-- Direct Program / School Search -->
		<div class="max-w-md mx-auto">
			<form action="<?php echo esc_url( home_url( '/hinh-thuc-dao-tao/' ) ); ?>" method="GET" role="search" class="relative flex items-center">
				<label for="campus-search-404" class="sr-only">Tìm ngành học, trường liên thông</label>
				<div class="relative w-full">
					<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
						<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
							<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
						</svg>
					</div>
					<input
						type="search"
						id="campus-search-404"
						name="s"
						placeholder="Tìm ngành học, trường liên thông (VD: CNTT, Luật...)..."
						class="w-full pl-10 pr-24 py-3 rounded-xl border border-slate-300 bg-white text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary shadow-xs transition-all"
					/>
					<button
						type="submit"
						class="absolute right-1.5 top-1.5 bottom-1.5 px-5 bg-[#0071E3] hover:bg-[#0077ED] text-white text-xs sm:text-sm font-medium tracking-tight rounded-full active:scale-98 transition-all flex items-center justify-center cursor-pointer shadow-none hover:shadow-xs"
					>
						Tìm kiếm
					</button>
				</div>
			</form>
		</div>

		<!-- Action Buttons -->
		<div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-brand-primary text-white font-bold text-sm shadow-md hover:bg-brand-darkBlue active:scale-95 transition-all">
				<svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
				</svg>
				Về trang chủ
			</a>
			<a href="<?php echo esc_url( home_url( '/kiem-tra-dieu-kien/' ) ); ?>" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-brand-accent text-white font-bold text-sm shadow-md hover:bg-[#e06e00] active:scale-95 transition-all">
				<svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
				</svg>
				Kiểm tra điều kiện liên thông
			</a>
		</div>

		<!-- Support Footnote -->
		<div class="pt-4 border-t border-slate-200/70 text-xs sm:text-sm text-slate-500 flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
			<a href="<?php echo esc_url( home_url( '/nhom-nganh/' ) ); ?>" class="hover:text-brand-primary hover:underline transition-colors">Nhóm ngành liên thông HOT</a>
			<span class="text-slate-300">•</span>
			<a href="<?php echo esc_url( home_url( '/truong-doi-tac/' ) ); ?>" class="hover:text-brand-primary hover:underline transition-colors">Trường đại học đối tác</a>
			<span class="text-slate-300">•</span>
			<span>Hotline tư vấn: <a href="tel:0988991496" class="font-bold text-brand-accent hover:underline">0988 991 496</a></span>
		</div>
	</div>
</article>

<?php
get_footer();
