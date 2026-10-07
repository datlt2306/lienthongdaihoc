<?php
/**
 * Template Name: Điều khoản dịch vụ
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hotline      = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496';
$email        = function_exists( 'ltdh_get_email' ) ? ltdh_get_email() : 'tuvan@lienthongdaihoc.com';
$address      = function_exists( 'ltdh_get_address' ) ? ltdh_get_address() : 'Số 71 ngõ 32/84 Đỗ Đức Dục, Mễ Trì, Nam Từ Liêm, Hà Nội';
$zalo         = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
$company_name = function_exists( 'ltdh_get_company_name' ) ? ltdh_get_company_name() : 'Cổng thông tin Tuyển sinh lienthongdaihoc.com';
?>

<!-- BANNER HERO -->
<section class="relative bg-gradient-to-tr from-[#0E2038] via-[#002B66] to-[#001838] text-white py-16 md:py-20 overflow-hidden border-b border-slate-800">
	<div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(white 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
	<div class="absolute -right-24 -bottom-24 w-80 h-80 bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>

	<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<!-- Breadcrumb -->
		<nav class="flex items-center gap-2 text-xs text-slate-400 mb-4 font-semibold uppercase tracking-wider">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-white transition-colors">Trang chủ</a>
			<span>/</span>
			<span class="text-blue-300">Điều khoản dịch vụ</span>
		</nav>

		<div class="max-w-3xl space-y-3">
			<span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-500/20 text-orange-300 border border-orange-400/30">
				<span class="w-2 h-2 rounded-full bg-orange-400"></span>
				Quy chế & Điều khoản áp dụng năm 2026
			</span>
			<h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold font-display tracking-tight text-white leading-tight">
				ĐIỀU KHOẢN VÀ ĐIỀU KIỆN DỊCH VỤ
			</h1>
			<p class="text-slate-300 text-sm md:text-base leading-relaxed">
				Quy định quyền lợi, nghĩa vụ và trách nhiệm pháp lý giữa người sử dụng và Ban quản trị hệ thống <span class="text-white font-bold">lienthongdaihoc.com</span>.
			</p>
			<div class="pt-2 flex flex-wrap items-center gap-4 text-xs text-slate-400">
				<span>Cập nhật lần cuối: <strong>Tháng 10/2026</strong></span>
				<span>•</span>
				<span>Phạm vi: Cổng thông tin Tuyển sinh liên thông đại học</span>
			</div>
		</div>
	</div>
</section>

<!-- MAIN CONTENT WRAPPER -->
<main id="primary" class="site-main py-12 bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

			<!-- MAIN CONTENT BODY (8/12) -->
			<div class="lg:col-span-8">
				<article class="bg-white rounded-2xl shadow-sm border border-slate-200/90 p-6 sm:p-10 space-y-8">

					<?php
					$has_custom_content = false;
					if ( have_posts() ) {
						while ( have_posts() ) {
							the_post();
							$content = get_the_content();
							if ( ! empty( trim( $content ) ) ) {
								$has_custom_content = true;
								?>
								<div class="prose prose-slate max-w-none text-slate-700 leading-relaxed">
									<?php the_content(); ?>
								</div>
								<?php
							}
						}
					}

					// Render complete legal terms if no custom content in DB
					if ( ! $has_custom_content ) :
					?>

					<!-- Alert Box -->
					<div class="p-4 rounded-xl bg-amber-50/80 border border-amber-200 text-amber-900 text-sm leading-relaxed flex items-start gap-3">
						<svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
						</svg>
						<div>
							<strong>Lưu ý trước khi sử dụng:</strong> Việc quý khách tiếp tục truy cập, tra cứu thông tin, sử dụng công cụ kiểm tra điều kiện tuyển sinh hoặc gửi form đăng ký tư vấn trên <strong>lienthongdaihoc.com</strong> đồng nghĩa với việc quý khách xác nhận đã đủ năng lực hành vi dân sự, đã đọc kỹ, hiểu rõ và tự nguyện chấp thuận ràng buộc bởi các Điều khoản dịch vụ này.
						</div>
					</div>

					<!-- Section 1 -->
					<section id="dieu-1" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">1</span>
							Giới thiệu và Chấp thuận điều khoản
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Chào mừng quý học viên và bạn đọc đến với <strong>lienthongdaihoc.com</strong>. Bản "Điều khoản và Điều kiện Dịch vụ" này (sau đây gọi tắt là "Điều khoản") cấu thành một hợp đồng thỏa thuận pháp lý giữa bạn (sau đây gọi là "Người dùng" hoặc "Học viên") và Ban quản trị website <strong>lienthongdaihoc.com</strong>.
						</p>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Nếu bạn không đồng ý với bất kỳ phần nào trong các điều khoản này, vui lòng ngừng sử dụng các tính năng và dịch vụ trên hệ thống của chúng tôi ngay lập tức.
						</p>
					</section>

					<!-- Section 2 -->
					<section id="dieu-2" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">2</span>
							Bản chất dịch vụ và Vai trò của Cổng thông tin
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							<strong>lienthongdaihoc.com</strong> là cổng thông tin điện tử chuyên sâu chuyên cung cấp:
						</p>
						<ul class="list-disc list-inside space-y-1.5 text-slate-600 text-sm pl-2">
							<li>Thông tin tra cứu, tổng hợp và so sánh các chương trình tuyển sinh Liên thông Đại học, Đại học từ xa (Trực tuyến), Đại học vừa học vừa làm và Văn bằng 2;</li>
							<li>Công cụ hỗ trợ học viên kiểm tra điều kiện xét tuyển, ước tính thời gian đào tạo và dự toán học phí tương ứng với văn bằng hiện có;</li>
							<li>Dịch vụ kết nối và tư vấn lộ trình học tập hoàn toàn <strong>miễn phí</strong> từ các chuyên viên giáo dục giàu kinh nghiệm;</li>
							<li>Hỗ trợ hướng dẫn chuẩn bị hồ sơ xét tuyển và chuyển tiếp nguyện vọng học tập đến các trường Đại học đối tác.</li>
						</ul>
						<div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-950 text-xs sm:text-sm leading-relaxed mt-2 font-medium">
							<strong>Tuyên bố rõ ràng về tư cách pháp lý:</strong> lienthongdaihoc.com là đơn vị hỗ trợ thông tin và tư vấn tuyển sinh độc lập. Chúng tôi <u>KHÔNG</u> phải là cơ sở giáo dục đại học trực tiếp tổ chức giảng dạy hay cấp văn bằng tốt nghiệp. Toàn bộ chương trình đào tạo, hội đồng thi, học liệu và bằng tốt nghiệp Đại học (cử nhân/kỹ sư) do trực tiếp các <strong>Trường Đại học đối tác được Bộ Giáo dục và Đào tạo cấp phép</strong> chịu trách nhiệm ban hành theo quy chế đào tạo đại học hiện hành.
						</div>
					</section>

					<!-- Section 3 -->
					<section id="dieu-3" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">3</span>
							Quyền và nghĩa vụ của Người dùng
						</h2>
						<div class="space-y-3">
							<h3 class="font-bold text-slate-900 text-base">3.1. Quyền của Người dùng</h3>
							<ul class="list-disc list-inside space-y-1 text-slate-600 text-sm pl-2">
								<li>Được tra cứu thông tin tuyển sinh, học phí, ngành học miễn phí trên website;</li>
								<li>Được chuyên viên tuyển sinh tư vấn, giải đáp thắc mắc về lộ trình học tập tận tình và bảo mật;</li>
								<li>Được hỗ trợ hướng dẫn chuẩn bị hồ sơ đúng quy định để nộp vào các trường Đại học đối tác.</li>
							</ul>

							<h3 class="font-bold text-slate-900 text-base pt-2">3.2. Nghĩa vụ và Trách nhiệm của Người dùng</h3>
							<ul class="list-disc list-inside space-y-1 text-slate-600 text-sm pl-2">
								<li><strong>Tính chính xác của thông tin:</strong> Cam kết cung cấp thông tin trung thực, chính xác về họ tên, số điện thoại, văn bằng đã tốt nghiệp khi gửi yêu cầu tư vấn hoặc hồ sơ xét tuyển;</li>
								<li><strong>Trách nhiệm về tính pháp lý văn bằng:</strong> Người học tự chịu trách nhiệm trước pháp luật về tính hợp pháp và hợp lệ của các văn bằng đầu vào (Bằng tốt nghiệp THPT, Trung cấp, Cao đẳng...) khi đăng ký xét tuyển liên thông;</li>
								<li><strong>Sử dụng dịch vụ văn minh:</strong> Tôn trọng cán bộ tư vấn, không sử dụng ngôn từ xúc phạm, quấy rối hoặc khiêu khích trong quá trình trao đổi hỗ trợ.</li>
							</ul>

							<h3 class="font-bold text-slate-900 text-base pt-2">3.3. Các hành vi bị nghiêm cấm tuyệt đối</h3>
							<div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-950 text-xs sm:text-sm space-y-1.5 leading-relaxed">
								<p>❌ Sử dụng thông tin cá nhân hoặc mạo danh người khác để gửi thông tin rác (spam form) lên hệ thống;</p>
								<p>❌ Tự động hóa sử dụng robot, spider, crawler hoặc các công cụ kỹ thuật để cào bới dữ liệu, sao chép nội dung trái phép gây tắc nghẽn máy chủ;</p>
								<p>❌ Thực hiện các hành vi tấn công mạng, chèn mã độc, can thiệp hoặc thay đổi cấu trúc cơ sở dữ liệu và mã nguồn website.</p>
							</div>
						</div>
					</section>

					<!-- Section 4 -->
					<section id="dieu-4" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">4</span>
							Quyền và trách nhiệm của lienthongdaihoc.com
						</h2>
						<div class="space-y-3">
							<h3 class="font-bold text-slate-900 text-base">4.1. Trách nhiệm của chúng tôi</h3>
							<ul class="list-disc list-inside space-y-1 text-slate-600 text-sm pl-2">
								<li>Cung cấp thông tin tuyển sinh trung thực, khách quan dựa trên thông báo tuyển sinh chính thức của các trường Đại học đối tác;</li>
								<li>Bảo mật tuyệt đối dữ liệu thông tin cá nhân của người học theo đúng quy định tại Chính sách bảo mật và pháp luật Việt Nam;</li>
								<li>Hỗ trợ tận tình, kịp thời giải đáp các vướng mắc của học viên trong suốt quá trình đăng ký xét tuyển.</li>
							</ul>

							<h3 class="font-bold text-slate-900 text-base pt-2">4.2. Quyền hạn của chúng tôi</h3>
							<ul class="list-disc list-inside space-y-1 text-slate-600 text-sm pl-2">
								<li>Có quyền từ chối cung cấp dịch vụ tư vấn đối với các cá nhân có hành vi spam, quấy rối hoặc cung cấp thông tin sai lệch có chủ đích;</li>
								<li>Có quyền cập nhật, chỉnh lý hoặc gỡ bỏ thông tin các chương trình đào tạo khi có sự thay đổi từ Hội đồng tuyển sinh các trường;</li>
								<li>Có quyền tiến hành nâng cấp, bảo trì hệ thống website mà không cần báo trước trong các tình huống khẩn cấp vì lý do an ninh.</li>
							</ul>
						</div>
					</section>

					<!-- Section 5 -->
					<section id="dieu-5" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">5</span>
							Quyền sở hữu trí tuệ
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Toàn bộ nội dung hiển thị trên website <strong>lienthongdaihoc.com</strong> bao gồm nhưng không giới hạn ở: bài viết nghiên cứu, cẩm nang hướng dẫn, bảng so sánh chương trình, thuật toán kiểm tra điều kiện, giao diện thiết kế, đồ họa, biểu tượng, hình ảnh và mã nguồn đều thuộc quyền sở hữu trí tuệ của Ban quản trị <strong>lienthongdaihoc.com</strong> hoặc được cấp phép hợp pháp từ đối tác.
						</p>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Mọi hành vi sao chép, trích dẫn nội dung phục vụ mục đích thương mại mà không có sự đồng ý bằng văn bản từ Ban quản trị hoặc không dẫn nguồn đầy đủ đều bị coi là hành vi xâm phạm quyền tác giả và sẽ bị xử lý theo Luật Sở hữu trí tuệ Việt Nam.
						</p>
					</section>

					<!-- Section 6 -->
					<section id="dieu-6" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">6</span>
							Tuyên bố miễn trừ trách nhiệm (Disclaimer)
						</h2>
						<div class="space-y-3 text-slate-600 text-sm sm:text-base leading-relaxed">
							<p>
								<strong>1. Về tính cập nhật của thông tin tuyển sinh:</strong> Chúng tôi nỗ lực tối đa để đảm bảo mọi thông tin về học phí, chỉ tiêu, thời gian đào tạo và chính sách xét tuyển luôn được cập nhật chính xác nhất. Tuy nhiên, thông tin tuyển sinh có thể được các Trường Đại học chủ động điều chỉnh tùy theo từng đợt tuyển sinh. Vì vậy, người học nên liên hệ trực tiếp tư vấn viên để được xác nhận thông tin cập nhật tại thời điểm nộp hồ sơ.
							</p>
							<p>
								<strong>2. Về quyết định xét tuyển:</strong> Quyền quyết định công nhận trúng tuyển, miễn giảm tín chỉ học phần và cấp văn bằng tốt nghiệp hoàn toàn thuộc thẩm quyền độc quyền của Hội đồng tuyển sinh các trường Đại học đối tác. lienthongdaihoc.com không cam kết hoặc bảo đảm thay mặt nhà trường việc chắc chắn trúng tuyển nếu học viên không đáp ứng đủ các tiêu chuẩn theo quy chế đào tạo.
							</p>
							<p>
								<strong>3. Về sự cố kỹ thuật:</strong> Chúng tôi không chịu trách nhiệm đối với bất kỳ sự gián đoạn kết nối, lỗi đường truyền Internet, máy chủ viễn thông của bên thứ ba nằm ngoài khả năng kiểm soát của chúng tôi.
							</p>
						</div>
					</section>

					<!-- Section 7 -->
					<section id="dieu-7" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">7</span>
							Liên kết đến trang web của bên thứ ba
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Website có thể chứa các siêu liên kết dẫn tới trang thông tin chính thức của các trường Đại học đối tác, cổng dịch vụ công của Bộ Giáo dục và Đào tạo hoặc các nền tảng mạng xã hội (Facebook, Zalo, YouTube). Các liên kết này chỉ nhằm mục đích hỗ trợ và tạo thuận tiện cho việc tra cứu của người dùng.
						</p>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Chúng tôi không kiểm soát và không chịu trách nhiệm về nội dung, chính sách bảo mật hoặc bất kỳ thiệt hại nào phát sinh từ việc bạn sử dụng các trang web của bên thứ ba đó.
						</p>
					</section>

					<!-- Section 8 -->
					<section id="dieu-8" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">8</span>
							Giải quyết tranh chấp và Luật điều chỉnh
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Các Điều khoản dịch vụ này được điều chỉnh và giải thích hoàn toàn theo quy định của pháp luật nước Cộng hòa Xã hội Chủ nghĩa Việt Nam.
						</p>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Mọi tranh chấp, khiếu nại phát sinh trong quá trình sử dụng website sẽ được ưu tiên giải quyết thông qua con đường đàm phán, hòa giải nhằm đảm bảo tối đa quyền lợi chính đáng của học viên. Trong trường hợp không thể tự hòa giải trong vòng 30 ngày kể từ ngày phát sinh tranh chấp, vụ việc sẽ được đưa ra phân xử tại Tòa án nhân dân có thẩm quyền tại Thành phố Hà Nội.
						</p>
					</section>

					<!-- Section 9 -->
					<section id="dieu-9" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">9</span>
							Sửa đổi, bổ sung điều khoản
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Chúng tôi có quyền đơn phương sửa đổi, bổ sung hoặc thay thế bất kỳ điều khoản nào trong văn bản này vào bất kỳ lúc nào để phản ánh đúng thực tiễn hoạt động và quy định pháp luật mới nhất.
						</p>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Phiên bản sửa đổi sẽ có hiệu lực ngay khi được đăng tải công khai trên website. Việc bạn tiếp tục sử dụng website sau khi các thay đổi được công bố sẽ đồng nghĩa với việc bạn chấp thuận các nội dung sửa đổi đó.
						</p>
					</section>

					<!-- Section 10 -->
					<section id="dieu-10" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">10</span>
							Thông tin liên hệ & Hỗ trợ pháp lý
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Nếu có bất kỳ câu hỏi nào về Điều khoản dịch vụ này, xin vui lòng liên hệ với Ban quản trị theo các kênh thông tin chính thức sau:
						</p>
						<div class="p-5 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5 text-sm text-slate-700">
							<p><strong>Cơ quan chủ quản:</strong> <?php echo esc_html( $company_name ); ?></p>
							<p><strong>Cổng thông tin tuyển sinh:</strong> <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-blue-600 hover:underline font-semibold">lienthongdaihoc.com</a></p>
							<p><strong>Địa chỉ tiếp nhận hồ sơ:</strong> <?php echo esc_html( $address ); ?></p>
							<p><strong>Hotline tư vấn tuyển sinh:</strong> <a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="text-blue-600 font-bold hover:underline"><?php echo esc_html( $hotline ); ?></a></p>
							<p><strong>Email tiếp nhận góp ý:</strong> <a href="mailto:<?php echo esc_attr( $email ); ?>" class="text-blue-600 font-bold hover:underline"><?php echo esc_html( $email ); ?></a></p>
							<p><strong>Thời gian trực hỗ trợ:</strong> 08:00 – 21:00 hàng ngày</p>
						</div>
					</section>

					<?php endif; ?>

				</article>
			</div>

			<!-- SIDEBAR NAVIGATION & QUICK ACTIONS (4/12) -->
			<aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">

				<!-- Quick TOC Card -->
				<div class="bg-white rounded-2xl shadow-sm border border-slate-200/90 p-5 sm:p-6 space-y-4">
					<h3 class="font-extrabold text-slate-900 text-sm uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-slate-100">
						<span class="w-1.5 h-4 bg-orange-500 rounded-full inline-block"></span>
						Mục lục điều khoản
					</h3>
					<nav class="space-y-1.5 text-xs sm:text-sm">
						<a href="#dieu-1" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition-colors font-medium">1. Giới thiệu & chấp thuận</a>
						<a href="#dieu-2" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition-colors font-medium">2. Bản chất dịch vụ cổng thông tin</a>
						<a href="#dieu-3" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition-colors font-medium">3. Quyền & nghĩa vụ người dùng</a>
						<a href="#dieu-4" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition-colors font-medium">4. Quyền & trách nhiệm của chúng tôi</a>
						<a href="#dieu-5" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition-colors font-medium">5. Quyền sở hữu trí tuệ</a>
						<a href="#dieu-6" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition-colors font-medium">6. Miễn trừ trách nhiệm</a>
						<a href="#dieu-7" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition-colors font-medium">7. Liên kết bên thứ ba</a>
						<a href="#dieu-8" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition-colors font-medium">8. Giải quyết tranh chấp</a>
						<a href="#dieu-9" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition-colors font-medium">9. Sửa đổi, bổ sung điều khoản</a>
						<a href="#dieu-10" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-orange-600 hover:bg-orange-50 transition-colors font-medium">10. Thông tin liên hệ pháp lý</a>
					</nav>
				</div>

				<!-- Legal Switch Card -->
				<div class="bg-gradient-to-br from-slate-900 to-[#002B66] text-white rounded-2xl p-6 shadow-sm space-y-4">
					<span class="text-xs font-bold text-blue-400 uppercase tracking-wider block">Văn bản liên quan</span>
					<h4 class="font-extrabold text-base leading-snug">Chính sách bảo mật thông tin</h4>
					<p class="text-xs text-slate-300 leading-relaxed">
						Tìm hiểu về cam kết bảo vệ dữ liệu cá nhân theo Nghị định 13/2023/NĐ-CP và quyền của chủ thể dữ liệu.
					</p>
					<a href="<?php echo esc_url( home_url( '/chinh-sach-bao-mat/' ) ); ?>" class="inline-flex items-center justify-center w-full py-2.5 px-4 rounded-xl bg-white text-slate-900 font-bold text-xs uppercase tracking-wider hover:bg-slate-100 transition-colors shadow-sm">
						Xem Chính sách bảo mật →
					</a>
				</div>

				<!-- Support Box -->
				<div class="bg-white rounded-2xl shadow-sm border border-slate-200/90 p-6 space-y-4 text-center">
					<div class="w-12 h-12 rounded-full bg-orange-50 text-orange-600 mx-auto flex items-center justify-center">
						<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
						</svg>
					</div>
					<div>
						<h4 class="font-black text-slate-900 text-sm">Cần tư vấn chương trình học?</h4>
						<p class="text-xs text-slate-500 mt-1">Liên hệ ngay để được hướng dẫn chọn trường và ngành học phù hợp nhất.</p>
					</div>
					<div class="pt-2 space-y-2">
						<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="block w-full py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-xs uppercase tracking-wider transition-colors shadow-sm">
							Gọi Hotline: <?php echo esc_html( $hotline ); ?>
						</a>
						<a href="<?php echo esc_url( $zalo ); ?>" target="_blank" rel="noopener noreferrer" class="block w-full py-2.5 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-extrabold text-xs uppercase tracking-wider transition-colors shadow-sm">
							Nhắn tin qua Zalo
						</a>
					</div>
				</div>

			</aside>

		</div>
	</div>
</main>

<?php
get_footer();
